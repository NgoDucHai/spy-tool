<?php
/**
 * Created by PhpStorm.
 * User: anhdt
 * Date: 2/10/2018
 * Time: 5:54 PM
 */

namespace Helpers\Crawlers;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use PHPExcel;
use PHPExcel_IOFactory;
// https://stackoverflow.com/questions/23764375/how-i-can-install-the-phpexcel-library-in-laravel
class Bonanza {
    protected $bonanza_link;

    protected $list_items;

    private $sku;

    private $appendDescription;

    private $priceUpTo;

    private $set_price_based_on;

    private $shipping_cost;


    public function getBonanzaUserFeedbacksLink($booth_id) {
        return "https://www.bonanza.com/users/".$booth_id."/user_feedbacks";
    }

    public function getBonanzaBoothLink($username) {
        return "https://www.bonanza.com/booths/".$username;
    }

    public function validateURL($url) {
        $is_url = Validator::make(
            array('url' => $url),
            array('url' => 'required|url')
        );

        if ($is_url->fails())
        {
            // The given data did not pass url
            return false;
        }
        return true;
    }

    public function htmlSpecialCharactersDecode($text) {
        $text = htmlspecialchars_decode($text, ENT_QUOTES);
        return $text;
    }

    public function setListItems() {
        $this->list_items = array();
    }

    public function __construct(){
        $this->bonanza_link = 'https://www.bonanza.com';
    }

    public function getPageCountOfBoothOnBonanza($startLink) {
        $html = new \Htmldom($startLink);
        $pageCount = $html->find('div[id=booth_page_area_update_container]',0)->getAttribute('data-pagecount');
        return $pageCount;
    }

    public function getItemLinksOfPerPage($startLink, $pageNumber) {
        $listItemLinks = array();
        $pageLink = $startLink . "?item_sort_options[page]=" . $pageNumber;
        $html = new \Htmldom($pageLink);
        foreach($html->find("div[id=page_content-$pageNumber]") as $page)
            foreach($page->find('li[class=item_image]') as $item) {
                $this->list_items[] = array($this->bonanza_link . $item->children(0)->href);
            }

    }
    public function getItemLinksOfBonanza($startLink, $fromPage, $toPage) {
        $allItemLinks = array();
        $pageCount = $this->getPageCountOfBoothOnBonanza($startLink);

        if($toPage > $pageCount) {
            $toPage = $pageCount;
        }

        for($pageNumber = $fromPage; $pageNumber <= $toPage; $pageNumber++) {
            $this->getItemLinksOfPerPage($startLink, $pageNumber);
        }

        return $this->list_items;
    }

    public function getBoothInfo($booth_id) {
        $info_user_feedbacks = $this->getInfoInUserFeedbacks($booth_id);
        $info_booth_page = $this->getInfoInBoothPage($info_user_feedbacks['booth_username']);

        $booth_info = array_merge($info_user_feedbacks, $info_booth_page);

        $timezone = \Config::get('bonanza.set_time_zone');
        $date = new \DateTime("now", new \DateTimeZone($timezone) );
        $updated_at = $created_at = $date->format('Y-m-d H:i:s');

        $booth_info['updated_at'] = $updated_at;
        $booth_info['timezone'] = $timezone;
        return $booth_info;
    }

    public function getInfoInBoothPage($username) {
        $booth_info = array();
        $go_to_booth_link = $this->getBonanzaBoothLink($username);
        $html = new \Htmldom($go_to_booth_link);

        $showing_total_products_str = $html->find('div[class="filter_text"]', 0)->innertext;
        preg_match_all('!\d+!', $showing_total_products_str, $matches);
        $booth_info['total_products'] = $matches[0][0];

        if($html->find('div[class="seller_swatch_membership_flair"]', 0)) {
            $member_level = $html->find('div[class="seller_swatch_membership_flair"]', 0)->find('a', 0)->title;
            $booth_info['member_level'] = $member_level;
        } else {
            $booth_info['member_level'] = '';
        }


        $booth_title = $html->find('h2[class="booth_title"]', 0)->innertext;
        $booth_info['booth_title'] = $booth_title;

        return $booth_info;
    }

    public function getInfoInUserFeedbacks($booth_id) {
        $not_update = 'Not Update';
        $booth_info = array();
        $booth_user_feedbacks_link = $this->getBonanzaUserFeedbacksLink($booth_id);

        $html = new \Htmldom($booth_user_feedbacks_link);

        $booth_info['booth_id'] =  $booth_id;

        $positive_rating = $html->find('span[id="rating_percent"]', 0)->plaintext;
        $positive_rating = trim(str_replace("%","",$positive_rating));
        $booth_info['positive_rating'] = $positive_rating;

        $all_transactions = $html->find('div[id="all_transactions"]', 0)->find('div[class="feedback_value"]', 0)->plaintext;
        $booth_info['transactions'] = $all_transactions;

        $booth_link = $html->find('div[class="booth_link"]', 0)->find('a', 0)->href;
        $booth_username = array_values(array_slice((explode('/', $booth_link)), -1))[0];
        $booth_info['booth_username'] = $booth_username;

        return $booth_info;
    }

    public function getItemDetailHTML($itemLink) {
        // http://php.net/manual/en/function.htmlspecialchars.php
        // http://php.net/manual/en/function.htmlspecialchars-decode.php

        $html = new \Htmldom($itemLink);
        $item = array();

        /*-------------------- start check this link is not correct or item is not exists ---------------------*/
        if(!$html->find('meta[itemprop="url"]', 0)) {
            return $item;
        }
        /*-------------------- end check this link is not correct or item is not exists ---------------------*/


        /*--------------- start get number of item ---------------*/
        $getItemNumber = explode('/', $itemLink);
        $itemID = end($getItemNumber);
        $item['id'] = $itemID;
        /*--------------- end get number of item ---------------*/

        /*--------------- start get title of item ---------------*/
        if($html->find('span[itemprop="name"]', 0)) {
            $title = $html->find('span[itemprop="name"]', 0)->plaintext;
        } else {
            $title = '';
        }
        $item['title'] = $title;
        /*--------------- end get title of item ---------------*/

        /*--------------- start get quantity of item ---------------*/
        if($html->find('meta[itemprop="quantity"]', 0)) {
            $quantity = $html->find('meta[itemprop="quantity"]', 0)->content;
        } else {
            $quantity = 0;
        }
        $item['quantity'] = $quantity;
        /*--------------- end get quantity of item ---------------*/

        /*--------------- start get price of item ---------------*/
        if($html->find('meta[property="product:price:amount"]', 0)) {
            $price = $html->find('meta[property="product:price:amount"]', 0)->content;
        } else {
            $price = 0;
        }
        $item['price'] = $price;
        /*--------------- end get price of item ---------------*/

        /*--------------- start get category of item ---------------*/
        if($html->find('meta[itemprop="category"]', 0)) {
            $category = $html->find('meta[itemprop="category"]', 0)->content;
        } else {
            $category = "";
        }
        $item['category'] = $category;
        /*--------------- end get category of item ---------------*/

        /*--------------- start get images of item ---------------*/
        $images = array();
        $item_image_thumbnails = $html->find('div[class="item_image_thumbnails"]', 0);
        if($item_image_thumbnails) {
            foreach($item_image_thumbnails->find('div[class="image_thumbnail_container"]') as $image_thumbnail_container) {
                $images[] = $image_thumbnail_container->find('a[class="photo_thumb"]',0)->href;
            }
        } else {
            $images[] = $html->find('div[class="item_image_zoom_space"]', 0)->find('div[class="item_image_zoom_inner"]', 0)->find('img', 0)->src;
        }

        $item['images'] = $images;
        /*--------------- end get images of item ---------------*/

        /*--------------- start get description of item ---------------*/
        $description_url = 'https://www.bonanza.com'.$html->find('div[class="item_description_inner"]', 0)->find('div[class="html_description"]', 0)->find('iframe', 0)->src;
        $description_res = new \Htmldom($description_url);
        $description_raw = $description_res->find('body', 0)->innertext;
        $description = str_replace('</body>','', str_replace('<body>', '', $description_raw));


        $item['description'] = $description;
        /*--------------- end get description of item ---------------*/

        /*--------------- start get price multiple of item ---------------*/
        $variation = $html->find('select[id="item_to_add_item_trait_mutation_id"]', 0);
        if($variation) {
            $price_multiple = '';
            if (strpos(strtolower($variation->find('option', 0)->innertext), 'size') !== false) {
                $opt = 'Size';
            } else {
                $opt = 'Type';
            }
            foreach($variation->find('option') as $option) {
                if($option->value != 0) {

                    $price_of_each = $option->attr['data-price'];

                    if(isset($option->attr['data-nondiscount-price'])) {
                        $price_of_each = $option->attr['data-nondiscount-price'];
                    }
//                    $data_quantity = $option->attr['data-quantity'];
                    $data_quantity = 10;
                    if($this->getPriceBasedOn() == 1) {
                        $each = "[[$opt:".$this->htmlSpecialCharactersDecode($option->plaintext)."][quantity:".$data_quantity."]]";
                    } else {
                        $price_of_each = str_replace("$","",$price_of_each) + $this->getPriceUpTo();
                        $price_of_each = "$". $price_of_each;
                        $each = "[[$opt:".$this->htmlSpecialCharactersDecode($option->plaintext)."][quantity:".$data_quantity."][price:".$this->htmlSpecialCharactersDecode(str_replace('$','',$price_of_each))."]]";
                    }

                    $price_multiple .= $each . ' ';
                }
            }
        } else {
            $price_multiple = '';
        }
        $item['price_multiple'] = $price_multiple;
        /*--------------- end get price multiple of item -----------------*/

        /*--------------- start get item traits -----------------*/
        $item_traits = '';
        $traits =  $html->find('div[class="item_traits_table_container"]', 0)->find('table[class="extended_info_table"]', 0);
        if($traits) {
            foreach($traits->find('tr[class="extended_info_row"]') as $tr) {
                $each_trait = '';
                $label = $tr->find('td[class="extended_info_label"]', 0)->plaintext;
                $label = str_replace(':','',$label);
                if($label != 'Reviews' && $label != 'Category' && $label != 'Quantity Available' && $label != 'Sizes' && $label != 'Types') {
                    $value_content = $tr->find('td[class="extended_info_value"]', 0)->find('p[class="extended_info_value_content"]', 0)->plaintext;
                    $each_trait = '[['.$this->htmlSpecialCharactersDecode($label).':'.$this->htmlSpecialCharactersDecode($value_content).']] ';
                }
                $item_traits .= $each_trait;
            }
        }
        $item['item_traits'] = $item_traits;
        /*--------------- end get item traits -------------------*/
//        $this->dataP($item);die;
        return $item;
    }

    private function setSKU($sku) {
        $this->sku = $sku;
    }

    private function getSKU() {
        return $this->sku;
    }

    private function setAppendDescription($appendDescription = "") {
        $this->appendDescription = $appendDescription;
    }

    private function getAppendDescription() {
        return $this->appendDescription;
    }

    private function setPriceUpTo($price_up_to) {
        $this->priceUpTo = $price_up_to;
    }

    private function getPriceUpTo(){
        return $this->priceUpTo;
    }

    private function setPriceBasedOn($price) {
        $this->set_price_based_on = $price;
    }

    private function getPriceBasedOn(){
        return $this->set_price_based_on;
    }

    private function setShippingCost($shipping_cost) {
        $this->shipping_cost = $shipping_cost;
    }

    private function getShippingCost(){
        return $this->shipping_cost;
    }

    public function exportDataToCSV($allItemLinks, $appendDescription = "", $price_up_to = "0.00", $sku = "SKU", $set_price_based_on = 1, $shipping_cost = 0) {

        $fileName = \Config::get('bonanza.excel_file_name');

        /*-------------------- Start Set Basic Params --------------------*/
        $this->setSKU($sku);
        $this->setAppendDescription($appendDescription);
        $this->setPriceUpTo($price_up_to);
        $this->setPriceBasedOn($set_price_based_on);
        $this->setShippingCost($shipping_cost);
        /*-------------------- End Set Basic Params --------------------*/

        Excel::create($fileName, function($excel) use($allItemLinks) {
            $excel->sheet('sheet1', function($sheet) use($allItemLinks) {
                $sheet->row(1, \Config::get('bonanza.excel_title'));
                // Append row as very last
                foreach($allItemLinks as $row) {
                    if(isset($row[0]) && $this->validateURL($row[0])) {
                        $itemLink = $row[0];
                        $this->getDataAndImportToCSV($itemLink, $sheet);
                    }
                }
            });

        })->store(\Config::get('bonanza.excel_type'), \Config::get('bonanza.storage_path'))->download('csv');

        return $fileName . "." . \Config::get('bonanza.excel_type');
    }

    public function setShippingPrice($shipping_cost) {
        if($shipping_cost > 0) {
            return $shipping_cost;
        }

        return "";
    }

    public function setShippingType($shipping_cost) {
        if($shipping_cost > 0) {
            return \Config::get('bonanza.shipping_type');
        }

        return \Config::get('bonanza.shipping_type_free');
    }

    public function getDataAndImportToCSV($itemLink, $sheet) {
        $item = $this->getItemDetailHTML($itemLink);

        if (!empty($item)) {
            // list is not empty.
            $sheet->appendRow(array(
                $this->getSKU().$item['id'],
                $item['title'],
                $item['quantity'],
                $this->calculatePriceUpTo($item['price'], $this->getPriceUpTo()),
                $item['category'],
                implode(' ',$item['images']),
                $item['description'].$this->getAppendDescription(),
                $item['item_traits'] . $item['price_multiple'],
                $this->setShippingPrice($this->getShippingCost()),
                $this->setShippingType($this->getShippingCost()),
                \Config::get('bonanza.shipping_carrier'),
                \Config::get('bonanza.shipping_service'),
                \Config::get('bonanza.shipping_package'),
                \Config::get('bonanza.noindex'),
                \Config::get('bonanza.force_update')
            ));
        }
    }

    private function calculatePriceUpTo($originalPrice, $priceUpTo) {
        return (float) ($originalPrice + $priceUpTo);
    }

    private function getPathToExcelFile($fileName) {
        return \Config::get('bonanza.storage_path') . "/" . $fileName;
    }

    public function arrangeDataSpiedBooth($data) {
        $all_data_of_spied_booths = array();
        foreach($data as $per_feed) {
            if(!array_key_exists($per_feed->booth_id, $all_data_of_spied_booths)) {
                $all_data_of_spied_booths[$per_feed->booth_id] = array(
                    'booth_id' => $per_feed->booth_id,
                    'booth_created_at' => $per_feed->booth_created_at,
                    'booth_updated_at' => $per_feed->booth_updated_at,
                    'current_booth_title' => $per_feed->booth_title,
                    'current_username' => $per_feed->booth_username,
                    'link_go_to_booth' => $this->getBonanzaBoothLink($per_feed->booth_username),
                    'link_go_to_userfeedbacks' => $this->getBonanzaUserFeedbacksLink($per_feed->booth_id),
                    'spied_booth_timezone'  =>  $per_feed->spied_booth_timezone,
                    'feeds' => array(
                        array(
                            'booth_username' => $per_feed->booth_username,
                            'transactions' => $per_feed->transactions,
                            'positive_rating' => $per_feed->positive_rating,
                            'total_products' => $per_feed->total_products,
                            'member_level' => $per_feed->member_level,
                            'feed_last_updated_at' => $per_feed->feed_last_updated_at,
                            'feed_timezone' => $per_feed->feed_timezone,
                        ),
                    ),
                );
            } else {
                $all_data_of_spied_booths[$per_feed->booth_id]['feeds'][] = array(
                    'booth_username' => $per_feed->booth_username,
                    'transactions' => $per_feed->transactions,
                    'positive_rating' => $per_feed->positive_rating,
                    'total_products' => $per_feed->total_products,
                    'member_level' => $per_feed->member_level,
                    'feed_last_updated_at' => $per_feed->feed_last_updated_at,
                    'feed_timezone' => $per_feed->feed_timezone,
                );
            }
        }
        return $all_data_of_spied_booths;
    }

    protected function dataP($data) {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
    }

}