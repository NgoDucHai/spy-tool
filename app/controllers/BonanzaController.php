<?php
/**
 * Created by PhpStorm.
 * User: anhdt
 * Date: 2/10/2018
 * Time: 12:22 PM
 */
use Illuminate\Http\Request;


class BonanzaController extends BaseController {

    private $_bonanza_helper;

    /**
     * Show the profile for the given user.
     */

    public function __construct() {
        ini_set('max_execution_time', 86400);// 24 hours = 86400 s
        $this->_bonanza_helper = new Helpers\Crawlers\Bonanza();
        View::share('page_title', 'Page Title');
    }

    public function getLinkSpiedBooth()
    {
        return View::make('bonanza.getLinkSpiedBooth', array('page_title' => 'Bonanza: Get Link Spied Booth'));
    }

    public function postLinkSpiedBooth()
    {
        $link_of_spied_booth = Input::get('link_of_spied_booth');
        $from_page = Input::get('from_page');
        $to_page = Input::get('to_page');

        $listItems = $this->_bonanza_helper->getItemLinksOfBonanza($link_of_spied_booth, $from_page, $to_page);

        Excel::create(strtotime('now'), function($excel) use($listItems) {

            $excel->sheet('Item Links', function($sheet) use($listItems) {

                $sheet->fromArray($listItems);

            });

        })->export('csv');

        Session::flash('success_message', 'Success | Download Excel File');
        return Redirect::route('bonanza.getLinkSpiedBooth');
    }

    public function getDataAndExportToCSV()
    {
        return View::make('bonanza.getDataAndExportToCSV', array('page_title' => 'Bonanza: Get Data And Export To CSV'));
    }

    public function postDataAndExportToCSV()
    {
        $link_of_spied_booth = Input::get('link_of_spied_booth');
        $from_page = Input::get('from_page');
        $to_page = Input::get('to_page');
        $price_up_to = (float) Input::get('price_up_to');
        $append_description = Input::get('append_description');
        $sku = Input::get('sku');
        $set_price_based_on = Input::get('set_price_based_on');
        $shipping_cost = Input::get('shipping_cost');

        $excel_file_is_vaid = false;

        $validator_data = array(
            'from_page' => $from_page,
            'to_page' => $to_page,
            'price_up_to' => $price_up_to,
            'sku' => $sku,
            'link_of_spied_booth' => $link_of_spied_booth,
            'set_price_based_on' => $set_price_based_on,
            'shipping_cost' => $shipping_cost,
        );

        $validator_rules = array(
            'link_of_spied_booth' => 'required',
            'from_page' => 'required',
            'to_page' => 'required',
            'price_up_to' => 'required',
            'sku' => 'required',
            'set_price_based_on' => 'required',
            'shipping_cost' => 'required',
        );

        if (Input::hasFile('excel_file'))
        {
            $excel_file_is_vaid = true;

            unset($validator_rules['link_of_spied_booth']);
            unset($validator_rules['from_page']);
            unset($validator_rules['to_page']);

            $validator_data['excel_file'] = Input::file('excel_file');
            $validator_rules['excel_file'] = 'required';
        }

        $validator = Validator::make($validator_data,$validator_rules);

        if ($validator->fails())
        {
            // The given data did not pass validation
            $messages = $validator->messages();
            Session::flash('error_messages', $messages);
            return Redirect::route('bonanza.getDataAndExportToCSV');
        }

        if($excel_file_is_vaid) {
            $path = Input::file('excel_file')->getRealPath();

            $allItemLinks = Excel::load($path, function($reader) {
                // Getting all results via array
                return $reader->toArray();
            })->toArray();
        }else {
            $allItemLinks = $this->_bonanza_helper->getItemLinksOfBonanza($link_of_spied_booth, $from_page, $to_page);
        }

        if(is_array($allItemLinks) && !empty($allItemLinks)) {
            $fileName = $this->_bonanza_helper->exportDataToCSV($allItemLinks, $append_description, $price_up_to, $sku, $set_price_based_on, $shipping_cost);
            Session::flash('success_message', 'Success | Download Excel File');
        } else {
            Session::flash('danger_message', 'List item link is empty. Please try again');
        }

        return Redirect::route('bonanza.getDataAndExportToCSV');
    }

    public function getItemByKeyWord()
    {
        return View::make('bonanza.getItemsByKeyWord', array('page_title' => 'Bonanza: Get items by keyword'));
    }

    public function postItemByKeyWord()
    {
        $keywords = Input::get('keywords');
        $entriesPerPage = Input::get('entriesPerPage');
        $pageNumber = Input::get('pageNumber');

        $dev_name = "NjIhzHCob5vuQ9Z";
        $api_url = "https://api.bonanza.com/api_requests/standard_request";
        $headers = array("X-BONANZLE-API-DEV-NAME: " . $dev_name);
        $paginationInput['entriesPerPage'] = $entriesPerPage;
        $paginationInput['pageNumber'] = $pageNumber;
        $args = array("paginationInput"=> $paginationInput, "keywords" => $keywords);
        $post_fields = "findItemsByKeywordsRequest=" .  json_encode($args);
        echo "Request: $post_fields \n";
        $connection = curl_init($api_url);
        $curl_options = array(CURLOPT_HTTPHEADER=>$headers, CURLOPT_POSTFIELDS=>$post_fields,
            CURLOPT_POST=>1, CURLOPT_RETURNTRANSFER=>1);  # data will be returned as a string
        curl_setopt_array($connection, $curl_options);
        $json_response = curl_exec($connection);
        if (curl_errno($connection) > 0) {
            echo curl_error($connection) . "\n";
            exit(2);
        }
        curl_close($connection);
        $response = json_decode($json_response,true);
        dd($response["findItemsByKeywordsResponse"]["item"]);
        return Redirect::route('bonanza.getItemByKeyWord', array('items' => $response["findItemsByKeywordsResponse"]["item"]));

    }

}