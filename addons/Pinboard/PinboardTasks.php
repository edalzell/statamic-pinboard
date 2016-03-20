<?php

namespace Statamic\Addons\Pinboard;

use Statamic\Extend\Tasks;
use Illuminate\Console\Scheduling\Schedule;
use Log;

class PinboardTasks extends Tasks {

	// look here for common code: http://docs.talonsbeard.com/addons/best-practices/keeping-dry
    private $core;
    
    function init() {
    	$this->core = new Pinboard;
    }
    
	public function schedule(Schedule $schedule)    {
		$schedule->call(function () {
			$this->core->writeRecentLinks();
        })->everyTenMinutes();
    }
}
