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
class Aliexpress {
    protected $aliexpress_link;

    protected $list_items;

    public function testFunction() {
        return "This is Aliexpress Helper";
    }

    public function getItemDetailHTML($itemLink) {
        // http://php.net/manual/en/function.htmlspecialchars.php
        // http://php.net/manual/en/function.htmlspecialchars-decode.php

        $html = new \Htmldom($itemLink);

        $item = array();

        /*--------------- start get product id of item ---------------*/
        $reg  = '/productId="(\d+)"/';
        $productId = $this->getDataByPregMatch($reg, $html);
        echo "Product Id: $productId <br>";
        /*--------------- end get product id of item ---------------*/

        /*--------------- start get Attributes of item ---------------*/
        $reg  = '/skuAttrIds=\[(.+)\];/U';
        $skuAttrIds = $this->getDataByPregMatch($reg, $html);
        echo "SkuAttrIds: $skuAttrIds <br>";
        /*--------------- end get Attributes of item ---------------*/

        /*--------------- start get title of item ---------------*/
        $title = $html->find('h1[class="product-name"]', 0)->innertext;
        $item['title'] = $title;
        echo "Title: $title <br>";
        /*--------------- end get title of item ---------------*/


        /*--------------- start get description of item ---------------*/
        $reg  = '/detailDesc="(.+)"/U';
        $description_url = $this->getDataByPregMatch($reg, $html);
        $description_raw = new \Htmldom($description_url);
        $description = str_replace('</body>','', str_replace('<body>', '', $description_raw));
        $item['description'] = $description;
        /*--------------- end get description of item ---------------*/

        /*--------------- start get price of item ---------------*/
        $product_customer_reviews = $html->find('div[class="product-customer-reviews"]', 0);
        $percent_num = $product_customer_reviews->find('span[class="percent-num"]', 0)->innertext;
        $rantings_num = $product_customer_reviews->find('span[class="rantings-num"]', 0)->innertext;
        /*--------------- end get price of item ---------------*/

        /*--------------- start get images of item ---------------*/
        $reg  = '/imageBigViewURL=\[(.+)\];/U';
        $imageBigViewURL = $this->getDataByPregMatch($reg, $html);
        $imageBigViewURL = str_replace('"', '', $imageBigViewURL);
        /*--------------- end get images of item ---------------*/

        /*--------------- start get price multiple of item ---------------*/
die;

        $variations = $html->find('dl[class="p-property-item"]');
        if($variations) {
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
                    $each = "[[$opt:".$this->htmlSpecialCharactersDecode($option->plaintext)."][quantity:".$option->attr['data-quantity']."][price:".$this->htmlSpecialCharactersDecode(str_replace('$','',$price_of_each))."]]";
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

    public function getDataByPregMatch($reg, $html, $fourthParam = PREG_OFFSET_CAPTURE) {
        $returnValue = "";
        preg_match($reg, $html, $matches, $fourthParam);
        if (isset($matches[1])) {
            $returnValue = $matches[1][0];
        }
        return $returnValue;
    }

    protected function dataP($data) {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
    }

}