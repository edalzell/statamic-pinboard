<?php

namespace Edalzell\Pinboard;

class Bookmark
{
    public $url;
    public $title;
    public $description;
    public $timestamp;

    public function __construct($url, $title, $description = null, $datetime = null)
    {
        $this->url = $url;
        $this->title = $title;
        $this->description = $description;
        $this->timestamp = $datetime ? $datetime->timestamp : now()->timestamp;
    }
}
