statamic-pinboard
=================

A Statamic add-on for Pinboard that creates entries from specific, public bookmarks from your Pinboard account.

## Installing
1. Copy the "_add-ons" folder contents to your Statamic root directory;
2. Do the same to the files inside the "_config" directory;

  > Just be careful to respect the exact folder structure, okay?
3. Configure the "pinboard.yaml" file with your custom values:
  * refresh: how often to pull the latest bookmarks in minutes. Defaults to 60 minutes;
  * token: your Pinboard [token](https://pinboard.in/settings/password);
  * link_tag: which tag to check for on Pinboard. Defaults to 'lb';
  * link_page: which folder to put the content in. Defaults to 'blog'. Entries use the date type.
4. Set up Statamic so tasks are [run]((http://learn.statamic.com/learn/creating-add-ons/tasks))
5. If you want to pull Tweets from your Pinboard, install the [statamic-twitter add-on](https://github.com/edalzell/statamic-twitter)
6. Enjoy! :)

## Requirements
* entries must be datetime prefixed (i.e. 2014-09-06-1414-entry-slug.md). This Add-On does **not** detect the entry type.

## Usage


Today's tagged bookmarks are automatically pulled and made into entries. The add-on only pulls bookmarks since you last ran the check.

If you want to manually pull older bookmarks:

* by date, run 'http://your-statamic-site/TRIGGER/pinboard/get?from='date/time-to-pull-from'
* by URL, run 'http://your-statamic-site/TRIGGER/pinboard/get?url='complete-url-to-link'

Bookmarks are added with current date/time **not** the bookmark time.