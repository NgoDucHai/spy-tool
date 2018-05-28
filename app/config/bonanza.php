<?php
/**
 * Created by PhpStorm.
 * User: anhdt
 * Date: 3/24/2018
 * Time: 11:09 AM
 */

return array(
    'excel_title' => array('SKU', 'Title', 'Quantity', 'Price', 'Category', 'Images', 'Description', 'Traits', 'shipping_price', 'shipping_type', 'shipping_carrier', 'shipping_service','shipping_package', 'noindex', 'force_update'),

    'excel_file_name' => date('Y-m-d', strtotime('now')).'-'.strtotime('now'),

    'excel_type' => 'csv',

    'storage_path' => storage_path('excel/exports'),

    'shipping_price' => '4.90',

    'shipping_type' => 'flat',
    'shipping_type_free' => 'free',

    'shipping_carrier' => 'usps',

    'shipping_service' => 'International2to3weeks',

    'shipping_package' => 'normal',

    'noindex' => 'FALSE',

    'force_update' => 'TRUE',

    'set_time_zone' => 'America/New_York',
);