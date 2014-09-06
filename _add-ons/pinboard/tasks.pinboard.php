<?php 

require_once 'vendor/autoload.php';

class Tasks_pinboard extends Tasks
{
    public function define()
    {
        //get the refresh time from the config
        $refresh = $this->fetchConfig('refresh', 60);
        
        //if Pinboard rate limits the 'all' call, so if lower than the limit reset
        if ($refresh < 5) {
            $this->log->warn("Refresh rate too low, minimum is 5");
            $refresh = 5;
        }
        
        $this->add($refresh, 'writeRecentLinks');
    }

    public function writeRecentLinks($from = null)
    {
        $this->writeBookmarks($this->getBookmarks($from));
        return true;
    }    


    private function getBookmarks($from) {
        //get the token from the config
        $token = $this->fetchConfig('token', null, null, false, false);

        // get the tag used for the links
        $tag = $this->fetchConfig('link_tag', 'lb');
        
        /*
            check last time this was run.
            if never run, just grab todays 
        */
        $timestamp = $from ?: $this->cache->get('last-check');
        $bookmarks = null;
        
        $pinboard = new PinboardAPI(null, $token);
        
        if ($timestamp == null) {
            $bookmarks = $pinboard->get(null, $tag);
        } else {
            $bookmarks = $pinboard->get_all(null, null, $tag, $timestamp);
        }
        
        // when done, store the last timestamp so we don't re fectch old ones
        $this->cache->put('last-check', time());
        
        return $bookmarks;
    }
    
    private function writeBookmarks($bookmarks) {
        // create the file path
        // get the Statamic folder used for the link posts
        $page_path = Path::resolve($this->fetchConfig('link_page', 'links'));
        
        // get the tag used for the links
        $tag = $this->fetchConfig('link_tag', 'lb');
        
        foreach ($bookmarks as $bookmark) {
            // the slug comes from the title in lowercase with '-' as a delimiter
            $slug = Slug::make($bookmark->title, array('lowercase' => true));

            // TODO: check the _entry_timestamps config to determine how to name the file
            $prefix = date('Y-m-d-Hi', $bookmark->timestamp);

            // make the file name
            $filename = $prefix.'-'.$slug;

            $fullpath = $this->getFullPath($page_path, $filename);
        
        	// if the twitter_embed add-on is installed and the bookmark is atwitter link
        	if ($this->addon->hasAPI('twitter') && strpos($bookmark->url, "https://twitter.com") === 0) {
				// get the id
				$url_array = explode('/', $bookmark->url);
				$id = $url_array[count($url_array)-1];
			
				$tweet = $this->addon->api('twitter')->getTweet($id);
				// prepend {{ twitter_embed:tweet id="<id>" omit_script="true"}}\n to the description
				$bookmark->description = '> '.$tweet['text'].PHP_EOL.PHP_EOL.$bookmark->description;
			}
			
            $yaml = array(
                'title' => $bookmark->title,
                'tags' => array_diff($bookmark->tags, array($tag)),
                'link' => $bookmark->url,
                'author' => 'Erin',
                'categories' => array('links'),
                'date' => date("Y-m-d H:i", $bookmark->timestamp));

            File::put($fullpath, File::buildContent($yaml, $bookmark->description));
            
            if ($this->addon->hasAPI('relative_cache_buster')) {
            	$this->addon->api('relative_cache_buster')->bustCache($fullpath);
            }
        }
    }

    // from a page and an entry slug, create the full path
    private function getFullPath($folder, $slug) {
        // create the file path
        $page_path = Path::resolve($folder . '/' . $slug);
        $path = Path::assemble(BASE_PATH, Config::getContentRoot(), $page_path . '.' . Config::getContentType());
        
        return $path;
    }
}