<?php
class Hooks_pinboard extends Hooks
{
    public function pinboard__get() {
        $from = Request::get('from');
        $url = Request::get('url');
        $this->tasks->writeRecentLinks($from, $url);
    }
}
