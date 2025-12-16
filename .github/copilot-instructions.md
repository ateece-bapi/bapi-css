# Copilot Instructions for bapi-css

## Project Overview
This repository contains styling assets for BAPI (Building Automation Products, Inc.) website built on WordPress with WooCommerce. The project uses the Storefront theme as a base with custom BAPI-v4 child theme styling. Files are organized into two environments:
- `bapi-prod/` - Production-ready files currently deployed (447 lines in additional.css)
- `bapi-stage/` - Staging environment for testing changes before production (78 lines in additional.css)

**Purpose:** Custom CSS overrides and templates for BAPI HVAC sensor product storefront (www.bapihvac.com)

## Architecture

### CSS Layer System
Files load in this order, each building on the previous:
1. **`storefront.css`** (2211 lines) - Base WooCommerce Storefront theme (v2.5.4)
2. **`bapi-v4.css`** (2379 lines) - Child theme with custom BAPI branding
   - Custom fonts: Acumin Pro Extra Condensed & Semi Condensed from Adobe Typekit
   - Indexed sections (use comment markers `/*----` to navigate):
     - 00. Font declarations
     - 01. Global
     - 02. UI (Header/Nav/Footer/Content)
     - 03. Page Specific (Homepage/Category/Product)
     - 04. Misc.
     - 05. Media Queries
   - Example: Product grid uses flexbox with `flex: 1 1 300px` and 24px gap
3. **`additional.css`** - High-priority overrides (stage=78 lines, prod=447 lines)
   - **Critical**: Prod has significantly more rules than stage - stage is minimal subset
   - Removes left sidebar, enforces full-width layouts
   - Specific ID-based rules: `#menu-item-417468` (language selector), `#menu-item-417469` (GTranslate)
   - Product page layouts: Grid for main archive, vertical list for category pages using body classes
4. **`woocommerce.css`** (2215 lines) - WooCommerce component styles

### Template Files
- **`page(bapi-v4).php`** - Custom page template for BAPI-v4 theme pages (identical in both environments)
- **`page(storefront).php`** - Storefront base page template (identical in both environments)
- **HTML source files** (`homePage.html`, `news.html`, `products.html`) - Live page source code for reference when writing CSS selectors

## Critical Workflows

### Making Style Changes
**Always follow this sequence:**
1. Edit CSS in `bapi-stage/` directory first
2. Copy CSS content to WordPress Admin → Appearance → Customize → Additional CSS
3. Preview changes in WordPress Customizer live preview
4. Once validated, copy exact changes to `bapi-prod/` counterpart file for version control
5. Deploy to production by copying CSS to WordPress Customizer on production site

**Deployment Method:** Manual copy-paste to WordPress Customizer (Additional CSS section)
- No automated deployment or build process
- This repo serves as version control for CSS maintained in WordPress admin
- No git hooks or build steps - direct file editing

### CSS Override Strategy
When overriding existing styles:
- Use specificity, not `!important`, unless absolutely necessary
- `additional.css` already contains high-priority overrides - add new ones here
- Comment all overrides with reason: `/* Single product page - make main product image larger */`
- Pattern: Descriptive comment above every major rule block

### Environment Parity
**Critical:** Stage and prod environments are NOT synced and serve different purposes
- **Stage**: Minimal testing environment (78 lines) - focused overrides only
- **Prod**: Complete override suite (447 lines) - includes all product layout variants, blog post styling, navigation adjustments
- Files may drift between environments - **this is intentional** for testing different approaches
- When finalizing changes, determine if stage version should be promoted to prod or if prod additions should be backported
- Template PHP files remain identical between environments
- No formal testing process - changes verified directly in WordPress Customizer preview

## Project-Specific Patterns

### Typography System
- Uses Adobe Typekit fonts via `@font-face` declarations in `bapi-v4.css`
- Font families: `acumin-pro-extra-condensed` (weights 400, 700), `acumin-pro-semi-condensed` (weights 300, 400, 700 + italics)
- Do not add additional font imports; use existing font stack

### Layout Conventions
- Base design uses modular scale: 1em base size with 1.618 ratio
- Standard spacing: 24px padding/gap (see `.site` and `.products` classes)
- Full-width override pattern:
  ```css
  .element {
    max-width: none;
    width: 100%;
    box-sizing: border-box;
    padding-left: 24px;
    padding-right: 24px;
  }
  ```

### WooCommerce Product Layout Strategy
Two distinct layouts controlled by body classes (see [bapi-prod/additional.css](bapi-prod/additional.css#L74-L128)):
1. **Main Products Archive** (`body.post-type-archive-product:not(.tax-product_cat)`):
   - Grid layout: 4 columns with `calc(25% - 18px)` per product
   - Uses `flex: 0 1 calc(25% - 18px)` for responsive columns
2. **Category Pages** (`.tax-product_cat .products:not(.columns-5)`):
   - Vertical list layout: Full width items with horizontal image + content
   - Image max-width: 180px, flex-shrink: 0
   - Mobile breakpoint at 768px switches to column layout

### ID-Based Targeting
Uses WordPress menu item IDs for specific functionality:
- `#menu-item-417468` - Language selector dropdown (show/hide on hover/active)
- `#menu-item-417469` - GTranslate text container (hide anchor, preserve spacing)
- Example: `.gt_switcher` positioning uses absolute positioning at `right: 180px; top: 50px`

### Comment Convention
Every major CSS block in `additional.css` starts with descriptive comment:
```css
/* [Purpose] - [What it does] */
selector { rules }
```

## Key Files Reference
- [bapi-prod/bapi-v4.css](bapi-prod/bapi-v4.css) - Main BAPI theme styles (use INDEX at line 21-32 for navigation)
- [bapi-prod/additional.css](bapi-prod/additional.css) - Complete override suite (input fixes, full-width, product layouts, blog styling)
- [bapi-stage/additional.css](bapi-stage/additional.css) - Minimal testing overrides (78 lines)
- [bapi-prod/page(bapi-v4).php](bapi-prod/page(bapi-v4).php) - Custom page template using Storefront hooks
- Static HTML files - Reference copies of rendered pages (do NOT edit for production changes)

## What This Repo Does NOT Contain
- WordPress theme files (parent Storefront theme lives elsewhere)
- Build tools, webpack, or CSS preprocessors (raw CSS only)
- Automated tests or CI/CD pipelines
- Deployment scripts (manual process)
- JavaScript files (if needed, located in WordPress theme directory)
- Git-based deployment (changes applied manually via WordPress admin)
