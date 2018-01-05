<?php

namespace Statamic\Addons\Pinboard;

use PinboardAPI;
use Illuminate\Http\Request;
use Statamic\Extend\Controller;

class PinboardController extends Controller {
    use Core;

    /** @var PinboardAPI $pinboard */
    private $pinboard;

    public function __construct(PinboardAPI $pinboard) {
        $this->pinboard = $pinboard;
    }

    public function getFetch(Request $request) {
        $from = $request->input('from');
        $url = $request->input('url');

        if (($from == null) && ($url == null)) {
            $this->writeRecentLinks();
        } elseif ($from != null) {
            $this->writeLinks($from);
        } elseif ($url != null) {
            $this->writeLink($url);
        }
    }

    public function getWriteTestBookmark(Request $request) {
        $this->writeEntry(
            $request->input('title'),
            $request->input('url'),
            $request->input('desc'),
            null,
            array('foo'));

        return 'Entry created!';
    }
}