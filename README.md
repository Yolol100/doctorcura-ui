# DoctorCura UI

> **Experiment · WordPress/PHP · WooCommerce · storefront and product interface modules**

**Developer profile:** [Andrew Baeten](https://github.com/Yolol100) · [Portfolio cases](https://andrewbaeten.nl/category/cases)

DoctorCura UI is the presentation plugin for the DoctorCura experiment. It contains storefront, product and interface components while keeping account, checkout, order and request behaviour in the separate [DoctorCura Core](https://github.com/Yolol100/doctorcura-core) plugin.

## What it demonstrates

| Area | Implementation |
| --- | --- |
| Storefront | Category grids, product presentation and image-slider components |
| Variations | WooCommerce variation and add-to-cart interface with native fallbacks |
| Currency | Server-side CHF/EUR switching integrated with WooCommerce pricing paths |
| Accessibility | Keyboard-aware accordion and swatch interactions plus output semantics |
| Compatibility | Duplicate-plugin and legacy-symbol detection with explicit admin notices |
| Separation | UI/presentation code remains independent from DoctorCura Core domain logic |

## Relationship with DoctorCura Core

```text
DoctorCura Core -> account, checkout, order and request behaviour
DoctorCura UI   -> storefront, product and presentation behaviour
WordPress + WooCommerce -> shared runtime platform
```

DoctorCura UI requires WooCommerce but does not have a hard runtime dependency on DoctorCura Core. The two plugins have independent versions and release packages, so the exact deployed combination should be verified on staging.

## Requirements

- WordPress 6.4+
- PHP 8.1+
- WooCommerce
- Current plugin version: **1.3.6**

`readme.txt` remains the source for the current stable tag and full release history.

## Current functionality

- Consolidated category-grid rendering with `[wa_category_grid]` and backwards-compatible `[alle_categorieen]`.
- Product variation and add-to-cart controls with native WooCommerce fallback behaviour.
- Server-side WooCommerce currency conversion instead of display-only browser conversion.
- Storefront translation and pricing helpers.
- Product image-slider and medical-accordion components.
- Duplicate-plugin and migrated-snippet conflict detection.
- Cleanup for plugin-owned currency, cron, transient and category metadata.

## Installation and verification

1. Ensure WooCommerce is active.
2. Upload the plugin folder to `wp-content/plugins/`.
3. Activate **DoctorCura UI**.
4. Verify product archives, product pages, variations, CHF/EUR pricing, checkout/order currency and translations on staging.
5. Remove old DoctorCura UI snippets or duplicate plugin copies when the plugin reports a legacy conflict.

## Safety and QA

Updates should be checked against storefront rendering, product variations, currency handling, checkout/order currency, translations, keyboard interaction, conflict notices and deactivation/uninstall behaviour. Do not place account, order or medical data in public issues or test artifacts.

## Repository structure

```text
doctorcura-ui.php     Plugin bootstrap, dependency and duplicate-copy guards
includes/             Storefront, WooCommerce, currency, translation and UI modules
assets/               Frontend/admin assets
readme.txt            WordPress release information and changelog
uninstall.php         Plugin cleanup
```

## Portfolio context

This repository is intentionally labelled as an experiment rather than a flagship product. It demonstrates modular WooCommerce presentation work, defensive compatibility handling and accessible fallbacks across a fairly broad storefront surface.

## License

GPL-2.0-or-later.
