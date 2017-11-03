<?php

namespace Statamic\Addons\Pinboard;

use PinboardAPI;
use Statamic\Extend\ServiceProvider;

class PinboardServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(PinboardAPI::class, function () {
            return new PinboardAPI(null, $this->getConfig('token'));
        });
    }
}
