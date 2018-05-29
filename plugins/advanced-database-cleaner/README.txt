=== Advanced Database Cleaner ===
Contributors: symptote
Donate Link: https://www.sigmaplugin.com/donation
Tags: clean, clean up, cleanup, database, optimize
Requires at least: 3.1.0
Tested up to: 4.8
Stable tag: 2.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Clean database by deleting orphaned data such as 'revisions', 'drafts', optimize database and more...

== Description ==
Clean database by deleting orphaned data such as 'old revisions', 'old drafts', optimize database and more...

If you have been using WordPress for a while, then you should think absolutely about a database clean up. Indeed, your database may be full of garbage that make your site sluggish and bloated such as old revisions, old drafts, spammed comments, etc. You should clean-up this unnecessary data to reduce your database size and improve website speed. In addition, you will have quicker database backup since the file of your backup will be smaller.

'Advanced Database Cleaner' is a must-have plugin that allows you to clean database, optimize database and more.

= Main Features =
* Clean database includes: 
* Delete old revisions of posts and pages
* Delete old drafts of posts and pages
* Delete old auto drafts
* Delete pending comments
* Delete spam comments
* Delete trash comments
* Delete orphan postmeta
* Delete orphan commentmeta
* Delete orphan relationships
* Delete orphan dashboard transient feeds
* You can choose what items to clean-up
* You can schedule your database clean up to run automatically
* Optimize database and improve website speed
* You can schedule your database optimization to run automatically
* View and clean cron tasks (scheduled tasks)
* View and clean tables
* View and clean options
* Supports multisite installation

= Pro Features (<a href="https://sigmaplugin.com/downloads/wordpress-advanced-database-cleaner">Official website</a>) =
Do you know that even after deleting a plugin/theme from your WordPress website, some of its leftovers may remain in your database? Such as orphan options, orphan tables and orphan cron tasks. As you may know, not all plugins/themes care about the housekeeping of your WordPress database. As long as you are removing plugins/themes, leftovers will be accumulated in your database and will influence your website performance. The Pro version of Advanced Database Cleaner will help you remove all those leftovers and perform a deep database clean up. In the pro version you can:

* Display and view unused items before making a database clean up
* Classify database options according to their "creator". They can be either: plugins options, themes options or WP core options
* Detect and delete orphan options
* Classify database tables according to their "creator". They can be either: plugins tables, themes tables or WP core tables
* Detect and delete orphan tables
* Classify all cron tasks (cron jobs) according to their "creator". They can be either: plugins cron tasks, themes cron tasks or WP core tasks
* Detect and delete orphan cron tasks

= Multisite Support =
* Only the main site can view, clean and optimize the whole network. Other sites in the network cannot perform these tasks. We have opted for this philosophy because we are sure that your DB is precious and only the super administrator can perform such actions.

== Installation ==

This section describes how to install the plugin and get it working.

= Single site installation =
* After extraction, upload the Plugin to your `/wp-content/plugins/` directory
* Go to "Dashboard" &raquo; "Plugins" and choose 'Activate'
* The plugin page can be accessed via "Dashboard" &raquo; "Tools" &raquo; "WP DB Cleaner" or via the left menu "WP DB Cleaner"

= Multisite installation =
* Login to your primary site and go to "My Sites" &raquo; "Network Admin" &raquo; "Plugins"
* Install the plugin as usual for multisite
* Network activate the plugin
* Only the main site can have access to the plugin

== Screenshots ==

1. Example of database with items to clean
2. View items before cleaning them (case of auto-draft)
3. Example of clean database
4. Example of tables that should be optimized
5. Example of optimize database
6. View and clean tables. You can also detect orphan tables, plugins tables, themes tables and WP tables (Pro version)
7. View and clean options. You can also detect orphan options, plugins options, themes options and WP options (Pro version)
8. View and clean scheduled tasks. You can also detect orphan tasks, plugins tasks, themes tasks and WP tasks (Pro version)
9. Overview and settings page

== Changelog ==

= 2.0.0 =
* Some changes to readme.txt file
* Changing the way the plugin can be translated
* Correcting __() to some texts
* Correcting some displaying texts
* Big change in styles
* Restructuring the whole code for better performance
* Creation of the plugin main page: https://sigmaplugin.com/downloads/wordpress-advanced-database-cleaner
* Adding language translation support
* Correct the time zone offset for the scheduled tasks
* Skipping InnoDB tables while optimizing
* Change size of lost tables data from 'o' to 'KB'
* Main menu is now under 'Tools' not 'settings'
* Adding separate left menu (can be disabled)
* Adding overview page with some useful information
* Adding settings page
* "Reset database" is now in a separate plugin (please view our plugins page)
* Multisite: now only the main site can clean the network
* New feature: Display/view items before cleaning them (Pro)
* New feature: view and clean options
* New feature: Detect orphan options, plugins options, themes options and WP options (Pro)
* New feature: view and clean cron (scheduled tasks)
* New feature: Detect orphan tasks, plugins tasks, themes tasks and WP tasks (Pro)
* New feature: view and clean database tables
* New feature: Detect orphan tables, plugins tables, themes tables and WP tables (Pro)

= 1.3.7 =
* Adding "clean trash-posts"
* Updating FAQ
* Updating readme file
* Tested up to: 4.4

= 1.3.6 =
* Fixing a problem in donate button
* Using _e() and __() for all texts in the plugin

= 1.3.5 =
* New feature: Adding "Clean Cron". You can now clean unnecessary scheduled tasks.
* Updating FAQ

= 1.3.1 =
* Adding FAQ

= 1.3.0 =
* Some code optimizations
* New feature: Support multisite. You can now clean and optimize your database in multisite installation.

= 1.2.3 =
* Some optimizations and style modifications
* New feature: Adding the scheduler. You can now schedule the clean-up and optimization of your database.

= 1.2.2 =
* Some optimizations and style modifications

= 1.2.1 =
* Some optimizations and style modifications
* "Clean database" tab shows now only elements that should be cleaned instead of listing all elements.
* "Clean database" tab shows now an icon that indicates the state of your database.
* "Optimize database" tab shows now only tables that should be optimized instead of listing all tables.
* "Optimize database" tab shows now an icon that indicates the state of your tables.
* "Reset database" shows now a warning before resetting the database.

= 1.2.0 =
* Some optimizations and style modifications
* New feature: Adding "Reset database"

= 1.1.1 =
* Some optimizations and style modifications
* Adding "Donate link"

= 1.1.0 =
* Some optimizations and style modifications
* New feature: Adding "Optimize Database"

= 1.0.0 =
* First release: Hello world!

== Upgrade Notice ==

= 2.0.0 =
New release.

== Frequently Asked Questions ==

= What does mean "clean my database"? =
As you use WordPress, your database accumulates a lot of extra data such as revisions, spam comments, trashed comments, etc. Removing this unnecessary data will reduce your database size, speeds up your backup process and speeds up your site also.

= Is it safe to clean my database? =
Yes, it is. We do not run any code that can break down your site or delete your posts, pages, comments, etc. However, we advise you to make a database backup before cleaning. It is always better to be safe than sorry.

= What does mean "Optimize my database"? =
Optimizing your database will reclaim unused space in your tables, which will reduce storage space and improve efficiency when accessing tables. Optimizing the database can sometimes significantly improve performance, particularly on sites that receive a lot of traffic or have a large amount of content. Optimizing your database is absolutely safe.

= Is it safe to clean the cron (scheduled tasks)? =
A scheduled task enables plugins to execute some actions at specified times, without having to manually execute code at that time. Wordpress itself uses some scheduled tasks to perform some regular actions. However, some scheduled tasks may not be removed even if the responsible plugins are deleted from your wordpress installation. As you know, not all plugins care about the housekeeping of your wordpress. Hence, deleting these unnecessary tasks may help in cleaning your site. It should be noted that cleaning scheduled tasks is safe as long as you know what tasks to clean. If your are not sure, it is better to not clean any task.

= What does mean "Revision"? What sql code is used to clean it? =
WordPress stores a record (called "revision") of each saved draft or published update. This system allows you to see what changes were made in each post and page over time. However, this can lead to a lot of unnecessary overhead in your WordPress database, which consumes a lot of space. The sql query used by the plugin to clean all revisions is:
`DELETE FROM posts WHERE post_type = 'revision'`

= What does mean "Draft"? What sql code is used to clean it? =
WordPress allows you to save a post or a page without having to publish it immediately. This way you can work on it as much as you want and publish it only when it is ready. This is called a Draft. Over time, you could have multiple drafts that you will never publish and hence you can clean them. The sql query used by the plugin to clean all drafts is:
`DELETE FROM posts WHERE post_status = 'draft'`

= What does mean "Auto-draft"? What sql code is used to clean it? =
Wordpress automatically saves your post/page while you are editing it. This is called an auto-draft. If you don't hit the publish/update button, then the post/page will be saved as auto-draft and any modification to your post/page will not be visible in your public site. Over time, you could have multiple auto-drafts that you will never publish and hence you can clean them. The sql query used by the plugin to clean all auto-drafts is:
`DELETE FROM posts WHERE post_status = 'auto-draft'`

= What does mean "Pending comment"? What sql code is used to clean it? =
Pending comments are comments published by users and which are awaiting for your approval before appearing in your site. In some cases, you will have to clean all these comments. The sql query used by the plugin to clean all pending comments is:
`DELETE FROM comments WHERE comment_approved = '0'`

= What does mean "Spam comment"? What sql code is used to clean it? =
It is a comment that you (or a plugin) have marked as a spam. The sql query used by the plugin to clean all spam comments is:
`DELETE FROM comments WHERE comment_approved = 'spam'`

= What does mean "Trash comment"? What sql code is used to clean it? =
A trash comment is a comment that you have deleted from your Wordpress and have been moved to the trash. A trash comment is not visible in your site and should be deleted forever. The sql query used by the plugin to clean all trash comments is:
`DELETE FROM comments WHERE comment_approved = 'trash'`

= What does mean "Orphan post meta"? What sql code is used to clean it? =
The post meta data is the information you provide to viewers about each post. This information usually includes the author of the post, when it was written (or posted), and how the author categorized that particular post. In some cases, some post meta data information become orphan and do not belong to any post. They are then called "orphan postmeta" and should be cleaned since they are not useful. The sql query used by the plugin to clean all orphan postmeta is:
`DELETE pm FROM postmeta pm LEFT JOIN posts wp ON wp.ID = pm.post_id WHERE wp.ID IS NULL`

= What does mean "Orphan comment meta"? What sql code is used to clean it? =
The same as "Orphan post meta" with the exception that "orphan comment meta" concern comments and not posts. The sql query used by the plugin to clean all orphan comment meta is:
`DELETE FROM commentmeta WHERE comment_id NOT IN (SELECT comment_id FROM comments)`

= What does mean "Orphan relationships"? What sql code is used to clean it? =
Sometimes the wp_term_relationships table becomes bloated with many orphaned relationships. This happens particularly often if you’re using your site not as a blog but as some other type of content site where posts are deleted periodically. Over time, you could get thousands of term relationships for posts that no longer exist which consumes a lot of database space. The sql query used by the plugin to clean all orphan relationships is:
`DELETE FROM term_relationships WHERE term_taxonomy_id=1 AND object_id NOT IN (SELECT id FROM posts)`

= What does mean "Dashboard transient feed"? What sql code is used to clean it? =
Transient are a way for storing cached data temporarily in your database by given it a name and a timeframe after which it will expire and be deleted. The sql query used by the plugin to clean dashboard transient feed is:
`DELETE FROM options WHERE option_name LIKE '_site_transient_browser_%' OR option_name LIKE '_site_transient_timeout_browser_%' OR option_name LIKE '_transient_feed_%' OR option_name LIKE '_transient_timeout_feed_%'`

= Is this plugin compatible with multisite? =
Yes, it is compatible with multisite. It should be noted that only the main site in the network can clean the database and orphan items of all the network. We prevent other sites to clean your DB since we believe that only the super administrator have the right to perform such operation. Your database is precious!

= Is this plugin compatible with SharDB, HyperDB or Multi-DB? =
Actually the plugin is not supposed to be compatible with SharDB, HyperDB or Multi-DB. We will try to make it compatible in coming releases.

= Does this plugin cleans itself after the uninstall? =
We do clean-up of your WordPress site, it will be a shame if the plugin does not clean itself after an uninstall! Of course yes, the plugin cleans itself and removes any data used to store its settings once uninstalled.