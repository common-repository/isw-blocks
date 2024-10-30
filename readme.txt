=== Plugin Name ===
Contributors: isoftware
Tags: admin, shortcode, shortcodes, code, html, javascript, snippet, code snippet, iframe, reuse, reusable, insert, global, content block, block, raw html, formatting, pages, posts, editor, form, forms, modify output, wpml, views
Requires at least: 3.5
Tested up to: 4.1
Stable tag: 1.2.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin lets you create your own reusable blocks of content.

== Description ==

Blocks lets you create your own reusable blocks of content where you can use shortcodes to insert code snippets, HTML including forms, iframes, etc and control formatting.

You can insert Blocks into pages, posts, widgets or directly into php content.

It is ideal for inserting reusable objects into your content that you can use side by side or as an alternative to standard posts.

It is fully compatible with WPML for content translation and WP Views for content filtering.

Configurable Features:

*   The "Output Mode" setting to control whether block content is rendered adding paragraphs and line breaks (wpautop) or not.
*   The "Sort Order" setting to control sort ordering when quering for a list of blocks.
*   The "Block Widget" that allows you put a block in any widget area on your site.
*	The "Multilingual Block Widget" that extends the normal "Block Widget" to work in a multilingual context with WPML (requires WPML plugin and Block Type Translation enabled).
*   The "isw-block" shortcode enables a block to be embedded in any post on your site.

== Installation ==

1. Download the isw-blocks.zip file to your local machine.
2. Either use the automatic plugin installer (Plugins - Add New) or Unzip the file and upload the isw-blocks folder to your /wp-content/plugins/ directory.
3. Activate the plugin through the Plugins menu
4. Visit the Blocks settings page ( Blocks -> Settings ) to configure features and options.
5. Visit the Blocks page ( Blocks ) to add or edit Content Blocks.
6. Insert the Block into pages or posts by inserting the shortcode [isw-block id=xx] where xx is the ID number of the Block.
7. Insert the Block into widget areas by inserting the Block Widget (or Multilingual Block Widget if WPML is installed and configured to translate blocks)

== Screenshots ==

1. The Settings Page
2. The Block Management Page
3. The Block Edit Page
4. The Widgets Page

== Changelog ==

= 1.2.1 =
* Fix activation error due to localization

= 1.2.0 =
* Added localization support
* Added Greek translation

= 1.1.1 =
* Compatible with Wordpress 3.9
* Minor fix in filter block content to execute only in loop

= 1.1.0 =
* Compatible with Wordpress 3.8
* Introduced web fonts instead of images
* Minor fix to css for on/off switches

= 1.0.0 =
* Initial Release
