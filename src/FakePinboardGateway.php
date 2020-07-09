<?php

namespace Edalzell\Pinboard;

use Illuminate\Support\Facades\Cache;
use Statamic\Support\Arr;

class FakePinboardGateway implements BookmarkGateway
{
    public function add($bookmark)
    {
        $this->bookmarks[] = $bookmark;
    }

    public function bookmarks()
    {
        return $this->bookmarks;
    }

    public function get($url)
    {
        return Arr::first($this->bookmarks, function ($bookmark, $key) use ($url) {
            return $bookmark->url === $url;
        });
    }

    public function recent($from = null)
    {
        // check last time this was run.
        // if never run, start from now
        $timestamp = $from ?: (int) Cache::get('last-check', time());

        $bookmarks = Arr::where($this->bookmarks, function ($bookmark, $key) use ($timestamp) {
            return $bookmark->timestamp >= $timestamp;
        });

        // when done, store the last timestamp so we don't fetch ones we've already retrieved
        Cache::put('last-check', time());

        return $bookmarks;
    }
}
