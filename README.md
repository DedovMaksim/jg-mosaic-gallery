# JG Mosaic Gallery

Adaptive mosaic image gallery for WordPress with modern responsive patterns and a built-in lightbox.

JG Mosaic Gallery renders images in flexible mosaic groups (2–5 images per block), inspired by modern cloud galleries (like Yandex Disk).  
It is lightweight, dependency-free by default, and designed for both developers and content editors.

---

## Features

- Mosaic layout with smart adaptive patterns (2–5 images)
- Responsive behavior with separate mobile logic
- Random or fixed pattern selection
- Gap, row height, and width controls
- Global defaults + per-gallery overrides
- Works with image IDs, URLs, or ACF galleries
- Built-in lightweight lightbox with slider navigation
- Fancybox support if already present on the site
- No external APIs, no tracking, no heavy dependencies

---

## Installation

### From Git

1. Clone or download this repository
2. Upload the `jg-mosaic-gallery` folder to  
   `/wp-content/plugins/`
3. Activate **JG Mosaic Gallery** in the WordPress admin

---

## Usage

### Basic shortcode
```text
[jg_mosaic ids="1,2,3,4"]
```

Full example
```text
[jg_mosaic
  ids="2319,2318,2314,2304,2263,2250"
  layout="mosaic"
  gap="10"
  row_height="260"
  random="1"
  patterns="p2,p3l,p3r"
  mobile_breakpoint="560"
  mobile_patterns="p2,p3l,p3r"
]
```
Available Mosaic Patterns
Key	Description
p2	2 images
p3l	3 images, large image on the left
p3r	3 images, large image on the right
p4	4 images, large left + 3 stacked right
p5	5 images (2–1–2 layout)
p5b	5 images (2 large sides + 3 center)

You can limit which patterns are allowed globally or per gallery.

## Global Settings

Global defaults are available in:

WordPress Admin → Settings → JG Mosaic Gallery

You can configure:

Default layout

Gap size

Base row height

Maximum gallery width

Allowed mosaic patterns

Mobile breakpoint and mobile patterns

Random pattern selection

Lightbox behavior

All global settings can be overridden per gallery via shortcode attributes.

## Lightbox

JG Mosaic Gallery includes a built-in lightweight lightbox with:

Modal overlay

Previous / next navigation

Keyboard support

Image counter

If Fancybox is already loaded by your theme or another plugin, JG Mosaic Gallery will automatically use it.

Lightbox behavior can be controlled via shortcode:

lightbox="fancybox" | "link" | "none"

## Project Structure
jg-mosaic-gallery/

├── assets/

│   └── dist/

│       ├── jg-mosaic-core.js

│       ├── jg-mosaic-core.css

│       ├── jg-mosaic-lightbox.js

│       └── jg-mosaic-lightbox.css

├── includes/

│   ├── Plugin.php

│   ├── Assets.php

│   ├── Settings.php

│   └── Shortcode.php

├── jg-mosaic-gallery.php

├── readme.txt      # WordPress.org readme

├── README.md       # GitHub readme

└── CHANGELOG.md

## Development Notes

Frontend configuration is passed as JSON via data-jg

Layout logic lives entirely in JavaScript

PHP is responsible only for data preparation and configuration

The plugin is safe to use without ACF (ACF is optional)

## Roadmap

Planned improvements:

Stable seeded random (no layout jumping on resize)

Gutenberg block

Pattern preview in admin settings

Optional captions

Swipe support in lightbox

## License

GPLv2 or later
https://www.gnu.org/licenses/gpl-2.0.html

## 🌐 Project Page

Full documentation and development story:
https://maksimdedov.ru/cases/jg-mosaic-gallery/
