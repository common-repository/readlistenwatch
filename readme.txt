=== Read/Listen/Watch ===
Contributors: sahearn
Donate link: http://scott.teamahearn.com/read-listen-watch/
Tags: Amazon, reading, read, listening, listen, listening to, watching, watch
Requires at least: 2.8
Tested up to: 3.0
Stable tag: 2.1.2

A customizable sidebar plugin/widget to display what you're reading, listening to, and watching; with links to Amazon.

== Description ==

This plugin will provide a customizable display along your sidebar for items which you are reading, listening to, and watching.  There is a minimum of one item displayed required, but you are not limited to only one of each "type" (i.e. you can list two items reading, one listening, and none watching - or any combination!).  In addition, this plugin was initially developed for me and my family to use concurrently, so different "owners" can be specified ("Joe is reading...", "Mary is reading...", etc).  Content is created and maintained via the Settings panel.

Other features include:

* If an ASIN is provided, listed items are linked to their respective Amazon detail pages.  In addition, if an Amazon Assoicates ID is provided, it will be appended to the link.
* Display options are configurable, via the Widget panel.
* If desired, an RSS feed is provided of items listed; when enabled the feed is linked from an icon adjascent to the widget title.

== Installation ==

= General =

Download the zip file, unzip it, and copy the "read-listen-watch" folder to your plugins directory. Then activate it from your plugin panel (if you are upgrading, deactivate and then reactivate). After successful activation, "Read/Listen/Watch" will appear in your "Settings" menu and "Widgets" control panel.

Go to the "Widgets" control panel first and take a look at the default options, which for many people will not require any changes.

Now go to the "Settings" menu to add your content.  Add items one at a time; updates and deletes can be performed to multiple items at once.  The date field for each item is used for the optional RSS feed.  It is important that the format of this field is not changed - as is, it conforms to the RSS 2.0 specification for GMT dates.

This plugin stores its data in the WordPress "options" database table.  Two rows will be created: one for each serialized object corresponding to item content and widget configuration.

= FYI =

There is moderate use of the "htmlspecialchars" function throughout this plugin, including the "double_encode" parameter which wasn't introduced until PHP 5.2.3.  I've done my best to run PHP version checks, but if you're running below PHP 5.2.3, be wary of HTML entity usage in any user, title, or author fields.  You might experience some unusual [double] encoding behavior.  If so, do not explicity type out entities - count on plugin code to encode entities for you (e.g. just type "&" - the plugin will convert it appropriately).

== Frequently Asked Questions ==

Please go to [the Read/Listen/Watch page](http://scott.teamahearn.com/read-listen-watch/) for detailed instructions on how to use this plugin.

== Changelog ==

= 2.1.2 =
Updated donate/home URL.  Cleaned up some RSS formatting.

= 2.1.1 =
Cleaner display/formatting on admin settings.  FIRST PUBLIC RELEASE.

= 2.1 =
Handle multiple deletes.

= 2.0.2 =
Better indicators and catches for required and optional fields.

= 2.0.1 =
Minor formatting changes, code cleanup. Lowered content admin permissions from "manage_options" to level 6 (Editor role).

= 2.0 =
Complete rewrite to conform with plugin development standards in preparation for first public release.

= 1.2 =
Added ratings.

= 1.1 =
Various bugfixes.

= 1.0 =
First private-use/unreleased version.

== Upgrade Notice ==

No updates at this time.

== Screenshots ==

1. Widget admin for global plugin settings
2. Admin panel for content maintenance
3. Sample sidebar display
