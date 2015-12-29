statamic-pinboard
=================

A Statamic V2 add-on for Pinboard that creates entries from specific, public bookmarks from your Pinboard account.

## Installing
1. Copy the "addons" folder contents to your Statamic `site` directory;
2. Do the same to the files inside the `settings` directory;

  > Just be careful to respect the exact folder structure, okay?
3. Configure the "pinboard.yaml" file with your custom values:
  * token: your Pinboard username & [token](https://pinboard.in/settings/password);
  * pinboard_tag: which tag to check for on Pinboard. Defaults to 'lb';
  * link_taxonomy: the Taxonomy for the 'link'
  * link_term: the Term for the link post
  * tag_taxonomy: the Taxonomy to put the Pinboard tags in
  * collection: the Collection the entries will go in 
  * author: author for the entries. Leave blank if you don't use it
4. Set up Statamic so tasks are [run](http://docs.talonsbeard.com/addons/anatomy/tasks)

## Usage

Today's tagged bookmarks are automatically pulled and made into entries. The add-on only pulls bookmarks since you last ran the check.

If you want to manually pull older bookmarks:

* by date, run 'http://your-statamic-site/!/pinboard/get?from='date-to-pull-from-in-yyyy-mm-dd-format'
* by URL, run 'http://your-statamic-site/!/pinboard/get?url='complete-url-to-link'

Bookmarks are added with current date/time **not** the bookmark time.

## LICENSE

[MIT License](http://emd.mit-license.org)
