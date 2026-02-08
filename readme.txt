=== JG Mosaic Gallery ===
Contributors: yourname
Tags: gallery, images, mosaic, grid, layout, photos
Requires at least: 5.8
Tested up to: 6.5
Requires PHP: 7.2
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A flexible mosaic image gallery with adaptive patterns inspired by modern cloud galleries (like Yandex Disk).

== Description ==

**JG Mosaic Gallery** is a lightweight and flexible image gallery plugin for WordPress.

It renders images using adaptive mosaic patterns (2–5 images per group), automatically adjusting layout for desktop and mobile screens.  
The gallery is designed to look modern, clean, and content-focused — similar to popular cloud storage galleries.

### Key features

* Mosaic layout with smart patterns (2, 3, 4, 5 images)
* Responsive behavior with separate mobile patterns
* Gap and row height controls
* Optional random pattern selection
* Works with image IDs, URLs, or ACF galleries
* Lightweight JavaScript engine (no heavy dependencies)
* Built-in lightbox with slider navigation
* Fancybox support if already present on the site
* Shortcode-based usage
* Global defaults + per-gallery overrides

### Designed for developers

JG Mosaic Gallery was built with customization in mind:

* Clean separation of PHP / JS / CSS
* JSON-based configuration passed to the frontend
* Safe fallbacks if images have no predefined sizes
* No external API calls or tracking
* GPL-compatible code only

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/jg-mosaic-gallery/`
2. Activate the plugin through the **Plugins** menu in WordPress
3. (Optional) Configure global defaults in **Settings → JG Mosaic Gallery**
4. Insert a gallery using the shortcode

== Usage ==

### Basic shortcode

[jg_mosaic ids="1,2,3,4"]

### Full example

[jg_mosaic
ids="1,2,3,4,5,6"
layout="mosaic"
gap="10"
row_height="260"
random="1"
patterns="p2,p3l,p3r"
mobile_breakpoint="560"
mobile_patterns="p2,p3l,p3r"
]


### Available pattern keys

* `p2`  — 2 images
* `p3l` — 3 images, large image on the left
* `p3r` — 3 images, large image on the right
* `p4`  — 4 images, large left + 3 stacked right
* `p5`  — 5 images (2–1–2)
* `p5b` — 5 images (2 large sides + 3 center)

== Settings ==

Global defaults are available in:

**Settings → JG Mosaic Gallery**

You can configure:

* Default layout
* Gap size
* Base row height
* Maximum gallery width
* Allowed mosaic patterns
* Mobile breakpoint and mobile patterns
* Random pattern selection

All settings can be overridden per gallery using shortcode attributes.

== Lightbox ==

JG Mosaic Gallery includes a built-in lightweight lightbox with:

* Modal overlay
* Previous / next navigation
* Keyboard support
* Image counter

If Fancybox is already loaded by your theme or another plugin, JG Mosaic Gallery will automatically use it instead.

Lightbox behavior can be controlled via shortcode:

lightbox="fancybox" | "link" | "none"


== Frequently Asked Questions ==

= Does this plugin require ACF? =
No. ACF is optional. You can use image IDs, URLs, or any custom implementation.

= Does it work with Gutenberg? =
Yes. The plugin works via shortcode and can be used inside blocks, classic editor, or custom templates.

= Will layouts change on resize? =
Layouts are recalculated on resize. Random patterns can be stabilized in future versions using a seed.

= Is this plugin free? =
Yes. JG Mosaic Gallery is free and released under the GPL license.

== Screenshots ==

1. Mosaic gallery on desktop
2. Mobile adaptive layout
3. Settings page
4. Lightbox view

== Changelog ==

= 0.1.0 =
* Initial public release
* Mosaic pattern engine
* Mobile adaptive logic
* Shortcode support
* Built-in lightbox
* Global settings page

== Upgrade Notice ==

= 0.1.0 =
Initial release.
