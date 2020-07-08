<?php

namespace Edalzell\Pinboard;

use Illuminate\Console\Scheduling\Schedule;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    public function register()
    {
        $this->app->bind(BookmarkGateway::class, function () {
            return new PinboardGateway();
        });
    }

    public function boot()
    {
        parent::boot();

        $this->publishes([
            __DIR__.'/../config.php' => config_path('pinboard.php'),
        ]);

        $this->app->booted(function () {
            app(Schedule::class)->call(function () {
                tap(new Pinboard(), function ($bookmarks) {
                    $bookmarks->write(app(BookmarkGateway::class)->recent());
                });
            })->hourly();
        });
    }
}
