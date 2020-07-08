<?php

namespace Edalzell\Pinboard;

interface BookmarkGateway
{
    public function bookmarks();

    public function recent($from = null);
}
