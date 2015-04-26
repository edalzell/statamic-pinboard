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
        $this->writeBookmarks($this->getBookmarks());
        return true;
    }    

    public function writeLinks($from = null)
    {
        $this->writeBookmarks($this->getBookmarks($from));
        return true;
    }    

    public function writeLink($url = null)
    {
        $this->writeBookmarks($this->getBookmark($url));
        return true;
    }    

    public function writeEntry($title, $url, $description, $author=null, $categories=array(), $tags=array(), $folder='blog' ) {

        // get the Statamic folder used for the link posts
        $page_path = Path::resolve($folder);
        
		// the slug comes from the title in lowercase with '-' as a delimiter
		$slug = Slug::make($title, array('lowercase' => true));

		// create the appropriate prefix
		$entry_type = Statamic::get_entry_type($page_path);

		$order_prefix = "";
		if ($entry_type == 'date') {
			if (Config::get('_entry_timestamps')) {
				$order_prefix = date('Y-m-d-Hi');
			}
			else {
				$order_prefix = date('Y-m-d');
			}
		} else if ($entry_type == 'number') {
			$order_prefix = Statamic::get_next_numeric($page_path);
		}

		// make the file name
		$filename = $order_prefix.'-'.$slug;

		$fullpath = $this->getFullPath($page_path, $filename);
	
		$yaml = array(
			'title' => $title,
			'link' => $url,
			'author' => $author);
			
		if (count($categories) > 0) {
			$yaml['categories'] = $categories;
		}

		if (count($tags) > 0) {
			$yaml['tags'] = $tags;
		}

		File::put($fullpath, File::buildContent($yaml, $description));
    }
    
    private function getBookmarks($from = null) {
        //get the token from the config
        $token = $this->fetchConfig('token', null, null, false, false);

        // get the tag used for the links
        $tag = $this->fetchConfig('link_tag', 'lb');
        
        /*
            check last time this was run.
            if never run, start from now 
        */
        $timestamp = $from ?: (int)$this->cache->get('last-check', time());
        
        $pinboard = new PinboardAPI(null, $token);
        
        $bookmarks = $pinboard->get_all(null, null, $tag, $timestamp);
        
        // when done, store the last timestamp so we don't fetch ones we've already retrieved
        $this->cache->put('last-check', time());
        
        return $bookmarks;
    }
    
    private function getBookmark($url) {
        //get the token from the config
        $token = $this->fetchConfig('token', null, null, false, false);

        // get the tag used for the links
        $tag = $this->fetchConfig('link_tag', 'lb');
        
        $pinboard = new PinboardAPI(null, $token);
        
        $bookmarks = $pinboard->get($url, $tag);
        
        return $bookmarks;
    }
    
    private function writeBookmarks($bookmarks) {
        // get the tag used for the links
        $tag = $this->fetchConfig('link_tag', 'lb');
        
        // do they have my Twitter Add-on installed?
        $haveTwitterAddon = $this->addon->hasAPI('twitter');
        
        foreach ($bookmarks as $bookmark) {
			// if the twitter_embed add-on is installed and the bookmark is a twitter link
			if ($haveTwitterAddon && strpos($bookmark->url, "https://twitter.com") === 0) {
				// get the id
				$url_array = explode('/', $bookmark->url);
				$id = $url_array[count($url_array)-1];
		
				$tweet = $this->addon->api('twitter')->getTweet($id);
				
				// add the tweet contents to the description as a quote
				$bookmark->description = '> '.$tweet['text'].PHP_EOL.PHP_EOL.$bookmark->description;
			}
			
    		$this->writeEntry($bookmark->title,
    						  $bookmark->url,
    						  $bookmark->description,
    						  $this->fetchConfig('author'),
    						  array('links'),
    						  array_diff($bookmark->tags, array($tag)),
    						  $this->fetchConfig('link_page', 'links'));
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