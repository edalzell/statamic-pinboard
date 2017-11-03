<?php namespace Statamic\Addons\Pinboard\SuggestModes;

use Statamic\API\Taxonomy;
use Statamic\Addons\Suggest\Modes\AbstractMode;

class AllTermsSuggestMode extends AbstractMode
{
    public function suggestions()
    {
        return Taxonomy::all()->flatMap(function($taxonomy, $ignored) {
            return $taxonomy->terms()->map(function ($term, $ignored) {
                return ['value' => $term->id(), 'text' => $term->taxonomy()->get('title') . ' - ' . $term->title()];
            })->values();
        })->values();
    }
}