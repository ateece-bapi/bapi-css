# Copilot Instructions for bapi-css

## Project Overview
This repository contains CSS files for two environments: `bapi-prod` and `bapi-stage`. Each environment includes four main CSS files:
- `additional.css`
- `bapi-v4.css`
- `storefront.css`
- `woocommerce.css`

These files are used to style different components of the BAPI storefront and WooCommerce integration. There are no build scripts, tests, or automation present in the codebase; all files are static CSS.

## Architecture & Workflow
- **Environments:**
  - `bapi-prod/` is for production-ready CSS.
  - `bapi-stage/` is for staging/testing CSS changes before production.
- **File Structure:**
  - Each environment mirrors the other in file naming and structure.
  - Changes should be tested in `bapi-stage` before being promoted to `bapi-prod`.
- **Update Workflow:**
  - Edit CSS in `bapi-stage` first.
  - Once validated, copy changes to the corresponding file in `bapi-prod`.
  - No automated deployment; manual file management is required.

## Conventions & Patterns
- **CSS Organization:**
  - Each file targets a specific area:
    - `additional.css`: Extra overrides and customizations.
    - `bapi-v4.css`: Core BAPI styles.
    - `storefront.css`: Storefront-specific styles.
    - `woocommerce.css`: WooCommerce integration styles.
  - Use clear comments to mark major sections and customizations.
  - Avoid mixing unrelated styles; keep overrides and new rules grouped logically.
- **Environment Parity:**
  - Maintain identical structure between `bapi-prod` and `bapi-stage`.
  - When adding new rules, update both environments as needed.

## Integration Points
- No external dependencies or build tools are present.
- All integration is via direct CSS file usage in the respective environments.

## Examples
- To add a new storefront rule:
  1. Edit `bapi-stage/storefront.css`.
  2. Test changes in staging.
  3. Copy the rule to `bapi-prod/storefront.css` when ready.
- To override WooCommerce styles:
  - Add custom rules to `woocommerce.css` in the appropriate environment.

## Key Files
- `bapi-prod/` and `bapi-stage/` directories: All main CSS files.

---

**If any conventions or workflows are unclear, please provide feedback so this guide can be improved.**
