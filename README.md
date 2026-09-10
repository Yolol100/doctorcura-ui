# DoctorCura UI

> **Portfoliostatus:** Experiment · zelfstandige WordPress/WooCommerce-presentatieplugin

DoctorCura UI bevat storefront-, product- en interfacecomponenten voor DoctorCura. De plugin blijft een afzonderlijk pakket van [DoctorCura Core](https://github.com/Yolol100/doctorcura-core), zodat presentatie en domeinlogica onafhankelijk kunnen worden getest, geactiveerd en teruggedraaid.

## Verantwoordelijkheid

- één centraal category-grid-systeem met de shortcodes `[wa_category_grid]` en de backwards-compatible alias `[alle_categorieen]`;
- medical accordion;
- productvariatie- en add-to-cart-interface;
- server-side CHF/EUR currency switching voor WooCommerce-prijzen, checkout/ordervaluta, shipping en vaste coupons;
- prijs-, vertaal- en accessibilitylabels;
- storefrontafbeeldingen en sliders;
- gecontroleerde account-notificatiefilters.

## Relatie met DoctorCura Core

```text
DoctorCura Core → primaire account-, checkout- en orderflows
DoctorCura UI   → storefront/productpresentatie + huidige account-notificatiefilters
WordPress + WooCommerce → gedeeld runtimeplatform
```

DoctorCura UI vereist WooCommerce, maar heeft geen harde runtimeafhankelijkheid van DoctorCura Core. Core en UI hebben afzonderlijke versies en releasepakketten. Test de gebruikte combinatie altijd op staging voordat productie wordt bijgewerkt.

## Compatibiliteit

| Onderdeel | Ondersteuning |
| --- | --- |
| WordPress | 6.4 of nieuwer; getest tot 6.8 |
| PHP | 8.1 of nieuwer |
| WooCommerce | vereist |
| Huidige versie | 1.3.3 |
| Combinatie | Test UI 1.3.3 samen met Core 1.0.6 op staging voordat beide naar productie gaan |

## Belangrijk in 1.3.3

- Geselecteerde variatieknoppen tonen nu een duidelijk wit kruisje rechts naast de tekst, zodat zichtbaar is dat de keuze kan worden verwijderd.
- Een geselecteerde variatie blijft met één klik deselecteerbaar, ook wanneer WooCommerce door een andere keuze combinaties uitschakelt.
- De pluginversie is verhoogd zodat browsers de bijgewerkte CSS en JavaScript direct opnieuw laden.
- De volledige 1.3.1-codebasis is hersteld; de onbedoelde downgrade naar 1.2.6 is teruggedraaid.
- De currency switcher gebruikt weer de server-side WooCommerce-conversie en wordt pas geladen nadat WooCommerce beschikbaar is.
- Login- en lost-passwordlabels zijn e-mailgericht in Duits, Engels en Frans.
- Publieke lost-passwordmeldingen zijn neutraal en bevestigen niet of een e-mailadres aan een account gekoppeld is.
- De bootstrap laadt WooCommerce-afhankelijke modules pas nadat WooCommerce beschikbaar is.
- Legacy Code Snippet/pluginconflicten veroorzaken niet langer stille module-uitval: elke conflictmodule wordt gelogd en zichtbaar gemeld in wp-admin.
- De locale helpers kunnen gedeeltelijk gemigreerde oude snippets opvangen zonder `undefined function`-fatals.
- De twee parallelle category grids zijn samengevoegd tot één renderer en één set assets.
- Account-notificatiefilters respecteren de WordPress filter-contracten.
- Vertaaldata wordt per request gecachet en niet op iedere `gettext`-aanroep opnieuw opgebouwd.
- Uninstall ruimt de wisselkoers, cron en plugin-eigen category-meta op.
- Dubbele actieve pluginmappen worden vóór symboolregistratie gedetecteerd, ook wanneer de nieuwe versie vóór een oude onbeschermde kopie laadt.
- Currency conversion is afgeschermd van wp-admin en niet-Store-API REST-verkeer; shipping caches krijgen valuta/rate-context en WooCommerce-prijspagina’s worden niet als gedeelde full-page cache opgebouwd.
- De custom variation UI gebruikt server-side geconverteerde waarden zonder dubbele JS-conversie en behoudt WooCommerce-prijsnotatie.
- Native button-keyboardgedrag wordt niet meer dubbel afgehandeld; zonder JavaScript blijven de native WooCommerce-variatiecontrols bruikbaar.

## Installatie

1. Zorg dat WooCommerce actief is.
2. Upload de pluginmap naar `/wp-content/plugins/`.
3. Activeer DoctorCura UI.
4. Controleer WooCommerce-archieven, productpagina's, CHF/EUR checkout, vertalingen en keyboardgedrag op staging.
5. Verwijder oude DoctorCura UI Code Snippets of dubbele pluginmappen als de plugin in wp-admin een legacy-codeconflict meldt.

## Releasegrens

Een nieuwe release vereist minimaal PHP- en JavaScript-syntaxcontrole plus stagingtests van storefront, productvariaties, valuta, checkout/ordervaluta, vertalingen, legacy-conflicten en deactivatie/uninstall-cleanup. Plaats geen account-, order- of medische gegevens in publieke issues.

## Licentie

GPL-2.0-or-later, zoals vastgelegd in de pluginheader en `readme.txt`.
