<?php namespace Statamic\Addons\Pinboard\SuggestModes;

use Statamic\API\Taxonomy;
use Statamic\Addons\Suggest\Modes\AbstractMode;

class TaxonomiesSuggestMode extends AbstractMode
{
    public function suggestions()
    {
        return(Taxonomy::all()->map(function($taxonomy, $slug) {
                return ['value' => $slug, 'text' => $taxonomy->get('title')];
            })->values());
    }
}