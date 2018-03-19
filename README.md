Pinboard
=================

A Statamic V2 add-on for [Pinboard](https://pinboard.in) that creates entries from specific, public bookmarks from your Pinboard account.

## Installing
1. Copy the addon folder to your Statamic `site` directory
2. Configure addon @ http://yoursite.com/cp/addons/pinboard/settings
3. Set up Statamic so tasks are [run](https://docs.statamic.com/addons/classes/tasks#starting)

## Usage

Today's tagged bookmarks are automatically pulled and made into entries. The add-on only pulls bookmarks since you last ran the check.

If you want to manually pull older bookmarks:

* by date, run 'http://your-statamic-site/!/Pinboard/fetch?from='date-to-pull-from-in-yyyy-mm-dd-format'
* by URL, run 'http://your-statamic-site/!/Pinboard/fetch?url='complete-url-to-link'

Bookmarks are added with current date/time **not** the bookmark time.

## LICENSE

[MIT License](http://emd.mit-license.org)
