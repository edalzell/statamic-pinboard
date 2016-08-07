<?php

namespace Statamic\Addons\Pinboard;

use Statamic\API\Request;
use Statamic\Extend\Listener;

class PinboardListener extends Listener {
    /**
     * The events to be listened for, and the methods to call.
     *
     * @var array
     */
    public $events = [
    	'pinboard.fetch' => 'fetch',
    	'pinboard.test_write' => 'write_test_bookmark'
    ];
    
    // look here for common code: http://docs.talonsbeard.com/addons/best-practices/keeping-dry
    /** @var  Pinboard */
    private $core;
    
    public function init() {
    	$this->core = new Pinboard;
    }
    
    public function fetch() {
        $from = Request::get('from');
        $url = Request::get('url');
        
        if (($from == null) && ($url == null)) {
	        $this->core->writeRecentLinks();
        } else if ($from != null) {
			$this->core->writeLinks($from);
		} else if ($url != null) {
			$this->core->writeLink($url);
		}
    }
    
    public function write_test_bookmark() {
        $title = Request::get('title','No title');
        $url = Request::get('url','No URL');
        $desc = Request::get('desc','No description');

        $this->core->writeEntry($title, $url, $desc, null, array('categories' => array('links'), 'tags' => array('foo')));
    }
}
