<?php

namespace Edalzell\Pinboard;

use PinboardAPI;

class PinboardGateway implements BookmarkGateway
{
    /** @var PinboardAPI */
    private $api;

    public function __construct()
    {
        $this->api = new PinboardAPI(null, config('pinboard.token'));
    }

    public function recent($from = null)
    {
        return $this->api->get_all(null, null, config('pinboard.pinboard_tag', 'lb'), $from);
    }
}
