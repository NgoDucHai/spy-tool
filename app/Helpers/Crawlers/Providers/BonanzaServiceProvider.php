<?php
/**
 * Created by PhpStorm.
 * User: anhdt
 * Date: 2/10/2018
 * Time: 7:48 PM
 */

namespace Helpers\Crawlers\Providers;

use Illuminate\Support\ServiceProvider;

class BonanzaServiceProvider extends ServiceProvider {

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('bonanza', function()
        {
            return new \Helpers\Crawlers\Bonanza();
        });
    }

}