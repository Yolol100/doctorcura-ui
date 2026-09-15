# DoctorCura UI

> **Portfoliostatus:** Experiment · zelfstandige WordPress/WooCommerce-presentatieplugin

DoctorCura UI bevat de storefront-, product- en interfacecomponenten voor DoctorCura. De plugin blijft bewust gescheiden van [DoctorCura Core](https://github.com/Yolol100/doctorcura-core), zodat presentatie en domeinlogica onafhankelijk kunnen worden geactiveerd, getest en teruggedraaid.

## Verantwoordelijkheid

DoctorCura UI bundelt onder meer:

- één category-grid-systeem met `[wa_category_grid]` en de backwards-compatible alias `[alle_categorieen]`;
- medical accordion-weergave;
- productvariatie- en add-to-cart-interface;
- server-side CHF/EUR currency switching voor WooCommerce-prijzen en relevante checkout/ordercontext;
- prijs-, vertaal- en accessibilitylabels;
- storefrontafbeeldingen en sliders;
- gecontroleerde account-notificatiefilters.

## Relatie met DoctorCura Core

```text
DoctorCura Core → account-, checkout-, order- en aanvraaggedrag
DoctorCura UI   → storefront-, product- en presentatielaag
WordPress + WooCommerce → gedeeld runtimeplatform
```

DoctorCura UI vereist WooCommerce, maar heeft geen harde runtimeafhankelijkheid van DoctorCura Core. De twee plugins hebben afzonderlijke versies en releasepakketten. Test daarom altijd de daadwerkelijk gebruikte Core/UI-combinatie op staging in plaats van één vaste cross-version-combinatie aan te nemen.

## Compatibiliteit

- WordPress 6.4 of nieuwer.
- PHP 8.1 of nieuwer.
- WooCommerce is vereist.
- Huidige pluginversie: `1.3.6`.
- `readme.txt` is leidend voor de actuele stable tag en releasegeschiedenis.

## Huidige functionaliteit

De huidige runtime bevat onder meer:

- server-side WooCommerce-valutaconversie in plaats van alleen cosmetische browserconversie;
- veilige bootstrap van WooCommerce-afhankelijke modules;
- detectie en melding van conflicterende oude DoctorCura UI-kopieën of gemigreerde snippets;
- één geconsolideerde category-grid-renderer;
- per-request gecachete vertaaldata;
- variatiecontrols die ook zonder JavaScript terugvallen op de native WooCommerce-controls;
- toegankelijke accordion-/swatchinteractie zonder dubbele keyboardtoggles;
- uninstall-cleanup voor plugin-eigen wisselkoers-, cron-, transient- en category-meta-data.

Versie `1.3.6` verfijnt daarnaast de deselectie-indicator in de variatie-interface. Zie `readme.txt` voor de volledige changelog.

## Installatie

1. Zorg dat WooCommerce actief is.
2. Upload de pluginmap naar `wp-content/plugins/`.
3. Activeer **DoctorCura UI**.
4. Controleer WooCommerce-archieven, productpagina's, variaties, CHF/EUR-prijzen, checkout/ordervaluta en vertalingen op staging.
5. Verwijder oude DoctorCura UI-snippets of dubbele pluginmappen als de plugin een legacy-conflict meldt.

## Veiligheid en QA

Controleer bij updates minimaal storefrontweergave, productvariaties, valuta, checkout/ordervaluta, vertalingen, keyboardbediening, conflictmeldingen en deactivatie/uninstallgedrag. Plaats geen account-, order- of medische gegevens in publieke issues of testartifacts.

## Repository structure

- `doctorcura-ui.php` — pluginbootstrap en runtime-metadata.
- `includes/` — storefront-, WooCommerce-, currency-, translation- en UI-logica.
- `assets/` — frontend/admin assets.
- `uninstall.php` — plugin-cleanup.
- `readme.txt` — WordPress-format release-informatie en changelog.

## Licentie

GPL-2.0-or-later, zoals vastgelegd in de pluginheader en `readme.txt`.