---
description: 
alwaysApply: true
---

# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a custom WordPress theme for a photography portfolio site (CedricPh). The theme is built from scratch without dependencies on frameworks or build tools.

**Theme Details:**
- Theme Name: CedricPh
- Text Domain: cedricph
- Development Environment: Local by Flywheel (Local Sites)
- WordPress Location: `/Users/lukasvannuffel/Local Sites/portfolio-cdricph/app/public/wp-content/themes/cedricph`

## Development Workflow

This theme uses vanilla PHP, CSS, and JavaScript with no build process. Edit files directly and refresh the browser to see changes.

**Testing changes:**
1. Local WordPress site runs on Local by Flywheel
2. No compilation or build step needed
3. Changes to PHP/CSS/JS are immediately reflected after browser refresh

## Core Architecture

### Template Structure

The theme follows WordPress template hierarchy with custom page templates:

- `front-page.php` - Homepage with sectioned layout
- `page-digital.php` - Digital photography portfolio (currently empty)
- `page-analog.php` - Analog photography portfolio (currently empty)
- `header.php` - Site header with custom navigation walker
- `footer.php` - Site footer with social links
- `template-parts/frontpage-sections/` - Modular front page sections:
  - `hero-section.php` - Hero with background image and CTA
  - `about-section.php` - About section with profile image
  - `featured-section.php` - Featured projects showcase
  - `contact-section.php` - Contact form section

### Advanced Custom Fields (ACF)

All content is managed through ACF fields registered programmatically in `functions.php`. Field groups include:

**Hero Section** (`group_hero_section`):
- Background image
- Title and subtitle
- CTA button text and link
- Scroll indicator toggle

**About Section** (`group_about_section`):
- Profile image
- About text (WYSIWYG)

**Contact Section** (`group_contact_section`):
- Description text (WYSIWYG)

**Note:** ACF Pro plugin is required for this theme to function properly. Fields are registered via `acf_add_local_field_group()`.

### Navigation System

The theme implements a custom navigation system with:

1. **Custom Walker** (`Cedricph_Dropdown_Nav_Walker` in functions.php):
   - Converts parent menu items with `#` URLs to `<span>` elements
   - Used for dropdown-only menu items (not clickable parents)

2. **Hash-based Navigation** (managed in `assets/js/main.js`):
   - Smooth scrolling to sections via hash links (e.g., `/#about`)
   - JavaScript handles active menu states based on URL hash
   - WordPress active classes are removed for hash links via `mytheme_nav_menu_css_class` filter

3. **Menu Filters** (in functions.php):
   - `mytheme_nav_menu_link_attributes` - Adds `.section-link` class to hash links
   - `mytheme_nav_menu_css_class` - Removes default active classes for hash links

### JavaScript Architecture

All JavaScript is in `assets/js/main.js` with no external dependencies. Key functions:

- `initNavigation()` - Smooth scrolling for hash links
- `initMobileMenu()` - Mobile menu toggle with body scroll lock
- `initHeaderScroll()` - Header background on scroll
- `initHeroBackgroundZoom()` - Parallax zoom effect on hero background
- `initActiveMenuState()` - Active menu state management for hash links and portfolio pages
- `initAboutImageHover()` - 3D tilt effect on about profile image

**Important:** Navigation state is entirely JavaScript-driven for front page sections. WordPress only handles active states for actual pages (like Portfolio).

### Styling

Single CSS file at `assets/css/main.css` - no preprocessor or build step.

## Common Patterns

### Adding a New Front Page Section

1. Create new PHP file in `template-parts/frontpage-sections/`
2. Register ACF field group in functions.php using `acf_add_local_field_group()`
3. Add `get_template_part()` call in `front-page.php`
4. Style in `assets/css/main.css`

### Adding ACF Fields

Fields are registered programmatically (not via ACF UI) in `functions.php` within the `if (function_exists('acf_add_local_field_group'))` block. Use the `acf_add_local_field_group()` function with location rules targeting specific page types or templates.

### Working with the Navigation Menu

- The theme expects a menu registered to the `primary` location
- Parent items with URL `#` become dropdown triggers only (via custom walker)
- Child menu items under Portfolio parent should link to actual pages
- Hash links are handled by JavaScript for smooth scrolling

## Known Issues / TODO

See `Todo.md` for current development tasks:
- Featured section needs to display 3 best portfolio projects + Instagram embedding
- Contact section needs email integration
- Portfolio page needs ACF setup for photo collections with carousel
- Analog/Digital pages need iteration over respective collections

## File Organization

```
cedricph/
├── assets/
│   ├── css/
│   │   └── main.css          # All styles
│   └── js/
│       └── main.js            # All JavaScript
├── template-parts/
│   └── frontpage-sections/    # Modular front page sections
├── functions.php              # Theme setup, ACF fields, custom functions
├── style.css                  # Theme header (required by WordPress)
├── header.php                 # Site header with navigation
├── footer.php                 # Site footer
├── front-page.php             # Homepage template
├── page-digital.php           # Digital portfolio template
├── page-analog.php            # Analog portfolio template
└── index.php                  # Fallback template
```

## WordPress Conventions

- Use WordPress escaping functions: `esc_html()`, `esc_url()`, `esc_attr()`
- Follow WordPress coding standards for PHP
- Use translation-ready strings with text domain `'cedricph'`
- Enqueue scripts/styles via `wp_enqueue_scripts` hook
- Register theme features via `after_setup_theme` hook

## Important Notes

- This theme does not use Gutenberg blocks - content is managed entirely through ACF fields
- Navigation menu must be assigned to 'Primary Menu' location in WordPress admin
- The theme assumes ACF Pro is installed and active
- No Node.js, npm, webpack, or build tools are used - keep it simple
- The site is developed locally using Local by Flywheel
