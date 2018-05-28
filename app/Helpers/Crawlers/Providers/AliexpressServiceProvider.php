<?php
/**
 * Created by PhpStorm.
 * User: anhdt
 * Date: 2/10/2018
 * Time: 7:48 PM
 */

namespace Helpers\Crawlers\Providers;

use Illuminate\Support\ServiceProvider;

class AliexpressServiceProvider extends ServiceProvider {

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('aliexpress', function()
        {
            return new \Helpers\Crawlers\Aliexpress();
        });
    }

}