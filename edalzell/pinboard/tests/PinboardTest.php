<?php

use Tests\TestCase;
use Statamic\API\Entry;
use Carbon\CarbonImmutable;
use Statamic\API\Collection;
use Edalzell\Pinboard\Bookmark;
use Edalzell\Pinboard\Pinboard;
use Edalzell\Pinboard\BookmarkGateway;
use Tests\PreventSavingStacheItemsToDisk;
use Edalzell\Pinboard\FakePinboardGateway;

class PinboardTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @var Pinboard */
    private $pinboard;

    /** @var BookmarkGateway */
    private $gateway;

    public function setUp(): void
    {
        parent::setUp();

        $this->app->instance(BookmarkGateway::class, new FakePinboardGateway());

        $this->pinboard = new Pinboard();
        $this->gateway = app(BookmarkGateway::class);
    }

    /** @test */
    public function can_add_bookmark()
    {
        $url = 'http://foo.com/bar';
        $title = 'This Is A Title';
        $description = 'This is a description';

        $bookmark = new Bookmark($url, $title, $description);
        $this->gateway->add($bookmark);

        $bookmark = $this->gateway->get($url);

        $this->assertNotNull($bookmark);
        $this->assertEquals($url, $bookmark->url);
        $this->assertEquals($title, $bookmark->title);
        $this->assertEquals($description, $bookmark->description);
    }

    /** @test */
    public function can_add_entry_from_bookmark()
    {
        $url = 'http://foo.com/bar';
        $title = 'This Is A Title';
        $description = 'This is a description';
        $bookmark = new Bookmark($url, $title, $description);

        $subset = [
            'type' => 'text',
            'text' => $description,
        ];

        Collection::create('blog')->save();

        $this->gateway->add($bookmark);
        $this->pinboard->write($this->gateway->bookmarks(), 'blog');

        /** @var \Statamic\API\Entry */
        $entry = Entry::findBySlug('this-is-a-title', 'blog');
        $content_block = $entry->get('content_block');

        $this->assertNotNull($entry);
        $this->assertEquals($title, $entry->get('title'));
        $this->assertNotNull($content_block);
        $this->assertTrue(in_array($subset, $content_block));
    }

    /** @test */
    public function can_get_recent_from_timestamp()
    {
        for ($x = 0; $x < 5; $x++) {
            $bookmark = new Bookmark(
                'http://url.com/bookmark' . $x,
                'Title ' . $x,
                'Description ' . $x,
                now()->subSeconds($x + 1)
            );
            $this->gateway->add($bookmark);
        }

        $from = now()->subSeconds(3)->timestamp;
        $this->assertNull(Cache::get('last-check'));
        $bookmarks = $this->gateway->recent($from);

        // has the cache been set w/ the right value
        $this->assertNotNull(Cache::get('last-check'));
        $this->assertEquals(now()->timestamp, (int) Cache::get('last-check'));

        // right number of bookmarks?
        $this->assertCount(3, $bookmarks);

        // the right bookmarks?
        for ($x = 0; $x < 3; $x++) {
            $this->assertEquals('http://url.com/bookmark' . $x, $bookmarks[$x]->url);
            $this->assertEquals('Title ' . $x, $bookmarks[$x]->title);
            $this->assertEquals('Description ' . $x, $bookmarks[$x]->description);
            $this->assertGreaterThanOrEqual($from, $bookmarks[$x]->timestamp);
        }
    }

    /** @test */
    public function can_get_recent_from_cache()
    {
        $now = CarbonImmutable::now();
        for ($x = 0; $x < 5; $x++) {
            $bookmark = new Bookmark(
                'http://url.com/bookmark' . $x,
                'Title ' . $x,
                'Description ' . $x,
                $now->subSeconds($x + 1)
            );
            $this->gateway->add($bookmark);
        }

        $from = $now->subSeconds(3)->timestamp;
        Cache::put('last-check', $from);

        $bookmarks = $this->gateway->recent();

        $this->assertCount(3, $bookmarks);

        for ($x = 0; $x < 3; $x++) {
            $this->assertEquals('http://url.com/bookmark' . $x, $bookmarks[$x]->url);
            $this->assertEquals('Title ' . $x, $bookmarks[$x]->title);
            $this->assertEquals('Description ' . $x, $bookmarks[$x]->description);
            $this->assertGreaterThanOrEqual($from, $bookmarks[$x]->timestamp);
        }
    }

}
