=== VimeoRSS ===
Contributors: benmillett
Donate link: 
Tags: vimeo, badge, video
Requires at least: 2.1
Tested up to: 3.0.3
Stable tag: trunk

A non-JavaScript badge generator for videos in one's vimeo account.  This behaves similarly to the flickrrss plugin.

== Description ==
**IMPORTANT:**  Vimeo has changed its API. Versions of this plugin earlier than 2.0 are dependent on the old API and will cease to function in September 2009.  You must upgrade to plugin v2.0 for your video badge to function properly.  v2.0 provides backward compatibility, so you should not need to change your plugin calls to match the new API.

A non-JavaScript badge generator for videos in one's vimeo account.  This behaves similarly to the flickrrss plugin.  Although the name of the plugin is VimeoRSS, it uses the xml feed provided by the simple API of vimeo.  You can call this plugin multiple times for different users and feed types.  This plugin can be used for groups, channels, and albums.

Version 2.1 brings support for [Shadowbox](http://www.shadowbox-js.com/) (lightbox functionality; off by default) and user-defined default width and height of the Shadowbox and Zoombox players (all must be set at the top of the plugin file itself).

If you are having issues with the plugin, PLEASE contact me for assistance.


== Installation ==

1. Upload the vimeorss folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3.  Enter user information in the options menu.
4. Place `<?php if (function_exists("vimeorss")) { vimeorss(); } ?>` in your templates.

You can change the badge by giving values within the plugin call. These are the options: `vimeorss(` User name`, "`Feed type`", `Number of thumbnails`, "`Before image`", "`After image`", `Cache time length in seconds`, "`thumbnail size`", "`XML file cache location`" )`.

Feed types: videos, subscriptions, contacts`_`like, contacts`_`videos, appears`_`in.
Thumbnail sizes: small = 100x75, medium = 150x120, large = 640 by video height.

Defaults: `videos, 4, "<li>", "</li>", 600, "small" /tmp/vimeoxml.`<em>username</em>`.`<em>600</em>`.cache`

Your username is what follows "http://vimeo.com/" on your profile page. Unless you've changed it, it will be "user" plus a number. Thumbnail amount is limited to 20.

Channel, groups, and albums can be accessed now.  In the place of the user name variable, you must use "group/XXX", "channel/XXX", or "album/XXX", where XXX is the group/channel URL name or group/channel/album ID (number).

== Frequently Asked Questions ==

= Do you have an example? =

Visit <a href="http://ben.momillett.org/vimeorss/">the plugin website</a> for additional implementation tips.

== Changelog ==
= 2.1 =
* Brings support for [Shadowbox](http://www.shadowbox-js.com/) and user-defined default width and height of the Shadowbox and Zoombox players. (Thanks to Ben Stewart for the suggestions.) (2011-01-13)
= 2.0.1 =
* Fixes issue where using zoombox and small thumbnails returned invalid links. (Thanks to Seb.)
= 2.0 =
* Brings compatibility with vimeo's new SimpleAPI 2.  Also added is support for [Zoombox](http://grafikart.fr/zoombox) (lightbox functionality; off by default) and the option to choose thumbnail size (small by default, 75x100px). (2009-08-07)
= 1.2 =
* Addressed another caching issue, hopefully making the temporary cache location universal (by determining what OS your server has and selecting the temp. folder accordingly). (2009-03-27)
= 1.1 =
* Addressed a caching issue. (2009-03-19)
= 1.0 =
* Added image caching functionality. (2009-01-16)
= 0.4.1 =
* Fixed the thumbnail alt quotes so apostrophes in video titles don't invalidate xhtml. (2008-11-25)
= 0.4 = 
* Fixed a caching issue.  Previously, the collections would cache to the root folder.  Now they cache to the tmp folder (unless cache location manually set). (2008-07-25)
= 0.3 = 
* Added support for groups, channels, and albums after vimeo's Simple API was changed. (2008-07-23)
= 0.2 =
* Based on XML format, using vimeo's Simple API.  (2007-10)
= 0.1 =
* Initial release, based on RSS format. (2007-07-10)