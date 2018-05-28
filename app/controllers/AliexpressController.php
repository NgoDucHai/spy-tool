<?php
/**
 * Created by PhpStorm.
 * User: anhdt
 * Date: 2/10/2018
 * Time: 12:22 PM
 */
use Illuminate\Http\Request;
use Helpers\Crawlers\Bonanza;
use Helpers\Crawlers\Aliexpress;



class AliexpressController extends BaseController {

    private $_aliexpress;

    /**
     * Show the profile for the given user.
     */

    public function __construct() {
        $this->_aliexpress = new Aliexpress();
        View::share('page_title', 'Page Title');
    }


    public function getExportItemIntoExcel() {
        $links = array(
            "http://localhost/aliexpress/acb.html",
            "http://localhost/aliexpress/acb_1.html",
        );

        foreach($links as $key => $link) {
            echo "<a href='$link' target='_blank'> Item ".$key++."</a><br>";
        }
        echo "<hr>";

        foreach($links as $key => $link) {
            $this->_aliexpress->getItemDetailHTML($link);
        }

    }


}