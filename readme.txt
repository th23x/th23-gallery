=== th23 Gallery ===
Contributors: th23
Donate link: https://th23.net
Tags: gallery, block, shortcode
Requires at least: 4.2
Tested up to: 6.6
Stable tag: 4.0.0
Requires PHP: 7.4
License: GPL-3.0-or-later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Replace standard Wordpress Gallery block and legacy [gallery] shortcode handler, opening galleries using PhotoSwipe


== Description ==

Wordpress has come a long way to be a fully featured content editor system, but the **default gallery does not keep up** with it. And while there are many plugins with various features, I did not find a small and simple one putting the **focus on enlarged images**.

[**PhotoSwipe**](https://photoswipe.com/) is a great basis and thus core component of my **th23 Gallery** plugin, which replaces the Wordpress default to output galleries into a way containing all key information required while maintaining a **simple and easy markup**.

**th23 Gallery** is built with some few goals in mind:

* Unintrusive way to **focus on images**
* Leveraging **PhotoSwipe / Lightbox-like** overlay
* Navigation between images in all galleries in a post via **intuitive swipe, keyboard or mouse** activity
* Compatibile with modern **blocks and legacy shortcodes**
* **Simple markup** and styling for thumbnail preview images (polaroid-style)
* **Plain styling** for medium / large preview images allowing for theme based style
* **Minimized footprint** on page loading


== Usage ==

**th23 Gallery** works out-of-the-box and does not require any special handling - right after activation all embedded galleries will use PhotoSwipe to enlarge pictures.

*Note*: Polaroid-style works only on galleries which have set the preview image size to `Thumbnail`

*Note*: Previously inserted galleries might require a review to ensure link to media file and preview image size are set to work as expected - especially when also using the plugin on legacy `[gallery]` shortcodes.


== Installation ==

Upload extracted files to your `wp-content/plugins` directory

No options required - **install, activate, enjoy** :-)

Only optional setting is the option to define a **default preview image size** for new galleries via the `Settings` / `Media` page in the admin panel

*Note*: There is a [known inconsistency in core WP](https://core.trac.wordpress.org/ticket/40692) not showing this default correctly in new galleries - but it is taken correctly upon saving/including the gallery and upon further edits as well


== Frequently Asked Questions ==

= What is shown as image caption? How can I influence this text? =

Image subtitle in lightbox is image **caption** (defined in gallery), image **description** (as in the media library) or image **title** (as in the media library) - in this order, whatever is given first

= Can you provide options to change x, y or z behaviour in PhotoSwipe? =

Yes, I could, but this plugin is supposed to be simple - in case you want many individual customization options, have a look at this [open source alternative by Arno Welzel](https://wordpress.org/plugins/lightbox-photoswipe/)


== Screenshots ==

1. Gallery embedded in a post with thumbnail preview image size (polaroid-style)
2. Image from gallery opened and given full focus (lightbox-style)


== Changelog ==

= v4.0.0 (first public release) =
* n/a

== Upgrade Notice ==

= v4.0.0 (first public release) =
* n/a
