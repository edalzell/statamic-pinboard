<?php

namespace Statamic\Addons\Pinboard;

use PinboardAPI;
use Illuminate\Http\Request;
use Statamic\Extend\Controller;

class PinboardController extends Controller
{
    use Core;

    /** @var PinboardAPI $pinboard */
    private $pinboard;

    public function __construct(PinboardAPI $pinboard)
    {
        $this->pinboard = $pinboard;
    }

    public function getFetch(Request $request) {
        $from  = $request->input('from');
        $url = $request->input('url');

        if (($from == null) && ($url == null)) {
            $this->writeRecentLinks();
        } else if ($from != null) {
            $this->writeLinks($from);
        } else if ($url != null)  {
            $this->writeLink($url);
        }
    }

    public function getWriteTestBookmark(Request $request) {
        list($title, $url, $desc) = $request->only(['title', 'url', 'desc']);

        $this->writeEntry($title, $url, $desc, null, array('categories' => array('links'), 'tags' => array('foo')));
    }
}