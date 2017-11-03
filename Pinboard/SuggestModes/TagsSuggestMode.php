<?php namespace Statamic\Addons\Pinboard\SuggestModes;

use Statamic\Addons\Pinboard\Core;
use Statamic\Addons\Suggest\Modes\AbstractMode;

class TagsSuggestMode extends AbstractMode
{
    use Core;

    public function suggestions()
    {
        return $this->getTags()->map(function ($tag, $ignored) {
            return ['value' => $tag, 'text' => $tag];
        });
    }
}