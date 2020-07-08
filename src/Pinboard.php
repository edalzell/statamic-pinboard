<?php

namespace Edalzell\Pinboard;

use Log;
use Statamic\API\Str;
use Statamic\API\Site;
use Statamic\API\Entry;
use Statamic\API\Search;
use Illuminate\Support\Arr;
use Statamic\API\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan as Please;

class Pinboard
{
    public function write($bookmarks, $collection = null)
    {
        if ($bookmarks == null || count($bookmarks) == 0) {
            return;
        }

        foreach ($bookmarks as $bookmark) {
            $this->makeEntry($bookmark, $collection)->save();
        }

        // this can throw AlgoliaSearch\AlgoliaException and I don't want to break here
        // so let's just log and continue
        try {
            // update the search index
            Search::update();
            Cache::clear();
            Please::call('clear:static');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    public function makeEntry($bookmark, $collection)
    {
        $collection = Collection::findByHandle($collection ?? config('pinboard.collection'));

        $entry = Entry::make()
            ->collection($collection)
            ->locale(Site::default()->handle())
            ->published(true)
            ->slug(Str::slug($bookmark->title))
            ->data(
                [
                    'title' => $bookmark->title,
                    'content_block' => [
                        [
                            'type' => 'text',
                            'text' => $bookmark->description,
                        ],
                    ],
                ]
            );

        if ($collection->dated()) {
            $entry->date(now());
        }

        if ($bookmark->url) {
            $entry->set('link', $bookmark->url);
        }

        $entry->set('author', config('pinboard.author'));

        $tags = array_values(
            array_diff(
                Arr::get($bookmark, 'tags', []),
                [config('pinboard.pinboard_tag', 'lb')]
            )
        );

        if ($tags) {
            $entry->set(config('pinboard.tag_taxonomy', 'tags'), $tags);
        }

        return $entry;
    }
}
