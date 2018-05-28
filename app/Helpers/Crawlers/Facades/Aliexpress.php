<?php
/**
 * Created by PhpStorm.
 * User: anhdt
 * Date: 2/10/2018
 * Time: 7:27 PM
 */

namespace Helpers\Crawlers\Facades;

use Illuminate\Support\Facades\Facade;

class Aliexpress extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'aliexpress';
    }
}