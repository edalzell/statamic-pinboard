<?php
class Hooks_pinboard extends Hooks
{
    public function pinboard__get() {
        $from = Request::get('from');
        $this->tasks->writeRecentLinks($from);
    }
}
