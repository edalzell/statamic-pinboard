<?php

namespace Statamic\Addons\Pinboard;

use PinboardAPI;
use Statamic\Extend\Tasks;
use Illuminate\Console\Scheduling\Schedule;

class PinboardTasks extends Tasks
{
    use Core;

    /** @var PinboardAPI $pinboard */
    private $pinboard;

    public function __construct(PinboardAPI $pinboard)
    {
        $this->pinboard = $pinboard;
    }

    public function schedule(Schedule $schedule)    {
		$schedule->call(function () {
			$this->writeRecentLinks();
        })->everyTenMinutes();
    }
}