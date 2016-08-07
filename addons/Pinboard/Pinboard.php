<?php

namespace Statamic\Addons\Pinboard;

use Log;
use Carbon\Carbon;
use Statamic\API\Str;
use Statamic\API\User;
use Statamic\API\Entry;
use Statamic\API\Search;
use Statamic\API\Helper;
use Statamic\Extend\Addon;
use Statamic\API\Collection;
use Statamic\API\Term;
use Statamic\Exceptions\ApiNotFoundException;

// need to include these because they don't use namespaces
use PinboardAPI;
use PinboardException;
use PinboardException_ConnectionError;

class Pinboard extends Addon
{
    public function writeRecentLinks($from = null)
    {
        $this->writeBookmarks($this->getBookmarks());
        return true;
    }    

    public function writeLinks($from = null) {
        $this->writeBookmarks($this->getBookmarks($from));
        return true;
    }    

    public function writeLink($url = null) {
        $this->writeBookmarks($this->getBookmark($url));
        return true;
    }    

    public function writeEntry($title, $url, $description, $author=null, $taxonomies=array(), $collection=null) {
		
		if (!$collection) {
			$collection = $this->getConfig('collection');
		}
		
        $entry = Entry::create(Str::slug($title))
        			->collection($collection)
        			->order($this->getOrderPrefix($collection))
        			->get();

		$entry->set('title', $title);

		if ($url) {
	        $entry->set('link', $url);
	    }
	    
	    // read default author if not passed in 
	    if (!$author) {
	    	$author = $this->getConfig('author');
	    }
	    
        $entry->set('author', User::whereUsername($author)->id());
        
        foreach ($taxonomies as $taxonomy => $terms) {
        	if ($terms != null ) {
		        $entry->set($taxonomy, $this->getTermIds($taxonomy, $terms));
		    }
	    }

		$entry->content($description);

        $entry->save();


		// there may be UTF-8 spaces still left and I have no idea how to get rid of them
		// properly so this is an ugly hack
//		$slug = str_replace("%C2%A0","-", urlencode($slug));


    }
    
    private function getBookmarks($from = null) {
        //get the token from the config
        $token = $this->getConfig('token');

        // get the tag used for the links
        $tag = $this->getConfig('pinboard_tag', 'lb');
        
        // check last time this was run.
        // if never run, start from now 
        
        $timestamp = $from ?: (int)$this->cache->get('last-check', time());
        
        $bookmarks = array();
        
        try {
			$pinboard = new PinboardAPI(null, $token);
		
			$bookmarks = $pinboard->get_all(null, null, $tag, $timestamp);
		
			// when done, store the last timestamp so we don't fetch ones we've already retrieved
			$this->cache->put('last-check', time());
		} catch (PinboardException_ConnectionError $ce) {
			// just ignore this
		} catch (PinboardException $e) {
			\Log::error($e->getMessage());
		}        
        return $bookmarks;
    }
    
    private function getBookmark($url) {
        //get the token from the config
        $token = $this->getConfig('token');

        // get the tag used for the links
        $tag = $this->getConfig('pinboard_tag', 'lb');
        
        $bookmark = array();
        
        try {
			$pinboard = new PinboardAPI(null, $token);
		
			$bookmark = $pinboard->get($url, $tag);
		} catch (PinboardException_ConnectionError $ce) {
			// just ignore this
		} catch (PinboardException $e) {
			\Log::error($e->getMessage());
		}        

        return $bookmark;
    }
    
    private function writeBookmarks($bookmarks) {
    
    	if ($bookmarks == null || count($bookmarks) == 0) {
    		return;
    	}
    	
        // get the pinboard tag used for the links
        $pinboard_tag = $this->getConfig('pinboard_tag', 'lb');
        
        // do they have my Twitter Add-on installed?
        $twitterAddon = null;
        
        try {
        	$twitterAddon = $this->api('twitter');
        } catch (ApiNotFoundException $e) {}
        
        foreach ($bookmarks as $bookmark) {
			// if the twitter_embed add-on is installed and the bookmark is a twitter link
			if ($twitterAddon && strpos($bookmark->url, "https://twitter.com") === 0) {
				// get the id
				$url_array = explode('/', $bookmark->url);
				$id = $url_array[count($url_array)-1];
		
				$tweet = $twitterAddon->getTweet($id);
				
				// add the tweet contents to the description as a quote
				$bookmark->description = '> '.$tweet['text'].PHP_EOL.PHP_EOL.$bookmark->description;
			}
			
    		$this->writeEntry($bookmark->title,
    						  $bookmark->url,
    						  $bookmark->description,
    						  $this->getConfig('author'),
    						  $this->getTaxonomies(array_diff($bookmark->tags, array($pinboard_tag))),
    						  $this->getConfig('collection'));
		}
		
		// this can throw AlgoliaSearch\AlgoliaException and I don't want to break here
		// so let's just log and continue
		try {
			// update the search index
			Search::update();
		} catch (Exception $e) {
			Log::error($e->getMessage());
		}
    }
    
    private function getTaxonomies($tags) {
    	$link_taxonomies = array($this->getConfig('link_term'));
    	$tag_taxonomies = null;
    	$not_tags = $this->getConfig('not_tags');
    	
    	foreach ($tags as $tag) {
    		if (in_array($tag, $not_tags)) {
    			$link_taxonomies[] = $tag;
    		} else {
    			$tag_taxonomies[] = $tag;
    		}
    	}
    	
    	return array($this->getConfig('link_taxonomy') => $link_taxonomies,
    				 $this->getConfig('tag_taxonomy') => $tag_taxonomies);
    }
    
    private function getOrderPrefix($collection) {
        //get the collection so we can figure out the order
        $order_type = Collection::whereHandle($collection)->order();
        
        if ($order_type == 'date') {
			$prefix = Carbon::now()->format('Y-m-d-Hi');
		} else {
			$prefix = Entry::whereCollection($collection)->count() + 1;
		}
		
		return $prefix;	
    }
    
    private function getTermIds($taxonomy, $slugs) {
		$ids = array_map(function($slug) use ($taxonomy) {
			$term = Term::whereSlug($slug, $taxonomy);
			
			if (!$term) {
				$term = $this->createTerm($slug, $taxonomy);
			}

			return $term->id();
		}, $slugs);
		
		return $ids;
    }

    private function createTerm($taxonomy, $slug) {
    	$tag = Term::create($slug)
    			->taxonomy($taxonomy)
    			->with(['title' => Str::title($slug), 'id' => Helper::makeUuid()])
    			->get();
    	
    	$tag->save();

    	return $tag;
    }
    
}
