<?php

namespace Statamic\Addons\Pinboard;

use Log;
use Carbon\Carbon;
use Statamic\API\Str;
use Statamic\API\Term;
use Statamic\API\Cache;
use Statamic\API\Entry;
use Statamic\API\Search;
use Statamic\API\Collection;
use Statamic\Extend\Extensible;
use Illuminate\Support\Facades\Artisan as Please;

// need to include these because they don't use namespaces
use PinboardException;
use PinboardException_ConnectionError;
use PinboardException_InvalidResponse;

trait Core
{
    use Extensible;

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
                    ->with(['title'=> $title])
        			->get();

		if ($url) {
	        $entry->set('link', $url);
	    }
	    
	    // read default author if not passed in

        $entry->set('author', $author ? $author : $this->getConfig('author'));
        
        foreach ($taxonomies as $taxonomy => $terms) {
        	if ($terms != null ) {
		        $entry->set($taxonomy, $terms);
		    }
	    }

		$entry->content($description);

        $entry->save();
    }

    private function getBookmarks($from = null) {
        // get the tag used for the links
        $tag = $this->getConfig('pinboard_tag', 'lb');
        
        // check last time this was run.
        // if never run, start from now
        $timestamp = $from ?: (int)$this->cache->get('last-check', time());
        
        $bookmarks = array();
        
        try {
			$bookmarks = $this->pinboard->get_all(null, null, $tag, $timestamp);
		
			// when done, store the last timestamp so we don't fetch ones we've already retrieved
			$this->cache->put('last-check', time());
        } catch (PinboardException_ConnectionError $ce) {
            // just ignore this
        } catch (PinboardException_InvalidResponse $ir) {
            // just ignore this
		} catch (PinboardException $e) {
			\Log::error($e->getMessage());
        }
        return $bookmarks;
    }
    
    private function getBookmark($url) {
        // get the tag used for the links
        $tag = $this->getConfig('pinboard_tag', 'lb');
        
        $bookmark = array();
        
        try {
			$bookmark = $this->pinboard->get($url, $tag);
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
        
        foreach ($bookmarks as $bookmark) {
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
            Cache::clear();
            Please::call('clear:static');
        } catch (\Exception $e) {
			Log::error($e->getMessage());
		}
    }

    private function getTaxonomies($tags) {
    	$category_terms = array(Term::find($this->getConfig('category_term'))->slug());
    	$tag_terms = null;
    	$category_tags = $this->getConfig('category_tags');
    	
    	foreach ($tags as $tag) {
    		if (in_array($tag, $category_tags)) {
                $category_terms[] = $tag;
    		} else {
    			$tag_terms[] = $tag;
    		}
    	}
    	
    	return array($this->getConfig('category_taxonomy') => $category_terms,
    				 $this->getConfig('tag_taxonomy') => $tag_terms);
    }
    
    private function getOrderPrefix($collection) {
        //get the collection so we can figure out the order
    	return (Collection::whereHandle($collection)->order() == 'date') ? Carbon::now()->format('Y-m-d-Hi') : Entry::whereCollection($collection)->count() + 1;
    }
    
    private function getTermIds($taxonomy, $slugs) {
		return array_map(function($slug) use ($taxonomy) {
			$term = Term::whereSlug($slug, $taxonomy);
			
			if (!$term) {
				$term = $this->createTerm($slug, $taxonomy);
			}

			return $term->id();
		}, $slugs);
    }
}
