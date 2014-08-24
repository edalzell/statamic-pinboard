Pinboard
========

This task pulls public, tagged bookmarks from your Pinboard account and adds them to your content.

Set the tag and content folder in the add-on settings folder, along with your Pinboard [token](https://pinboard.in/settings/password).
The 'refresh' parameter defaults to 60 mins. The 'link_tag' defaults to 'lb'.

To install, copy the files to your Statamic site and ensure your tasks are [configured](http://learn.statamic.com/learn/creating-add-ons/tasks)

Only today's bookmarks are checked and pulled (if appropriately tagged) and the add-on only pulls bookmarks since you last ran the check.

If you want to manually pull from Pinboard (get older bookmarks), run 'http://your-statamic-site/TRIGGER/pinboard/get?from='date/time-to-pull-from'
