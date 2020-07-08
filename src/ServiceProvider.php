<?php

namespace Edalzell\Pinboard;

use Edalzell\Pinboard\Pinboard;
use Edalzell\Pinboard\BookmarkGateway;
use Edalzell\Pinboard\PinboardGateway;
use Illuminate\Console\Scheduling\Schedule;
use Statamic\Extend\ServiceProvider as BaseProvider;

class ServiceProvider extends BaseProvider
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
            __DIR__ . '/../config.php' => config_path('pinboard.php'),
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
