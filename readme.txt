=== DoctorCura UI ===
Contributors: webactueel-team
Requires at least: 6.4
Tested up to: 6.8
Requires PHP: 8.1
Requires Plugins: woocommerce
Stable tag: 1.3.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Tags: woocommerce, storefront, ui, accessibility

DoctorCura UI bevat storefront- en product-UI modules voor DoctorCura.

== Description ==
DoctorCura UI bundelt:
- één category-grid-systeem met twee backwards-compatible shortcodes
- medical accordion
- product variation UI
- server-side CHF/EUR currency switching
- translations
- pricing en archive UI

== Changelog ==
= 1.3.1 =
* Beschermt beide plugin-load-orders wanneer een oude DoctorCura UI-kopie nog actief is
* Meldt gedeeltelijke legacy locale helpers zonder de ontbrekende helper ongedefinieerd te laten
* Category grid wordt niet meer onnodig overgeslagen door losse oude helperfuncties
* Translation map cache is geïntegreerd in de hoofdmodule; aparte optimalisatie-sidecar verwijderd
* Currency conversion uitgesloten van wp-admin en niet-Store-API REST-verkeer
* Shipping cache bevat valuta en wisselkoers zodat oude geconverteerde rates niet blijven hangen
* WooCommerce-prijspagina’s markeren zichzelf als niet geschikt voor gedeelde full-page cache
* Variation UI gebruikt server-side prijzen zonder dubbele browserconversie en met WooCommerce-prijsnotatie
* Keyboard double-toggle in accordion/swatches opgelost en native variation UI blijft beschikbaar als JavaScript uitvalt
* Image slider semantics en alt-output aangescherpt

= 1.3.0 =
* Bootstrap gehard tegen gedeeltelijke Code Snippets, dubbele modules en ontbrekende WooCommerce-dependencies
* Conflicten worden gelogd en zichtbaar gemeld in wp-admin in plaats van stil modules over te slaan
* Currency switcher omgezet van client-side cosmetische prijsweergave naar server-side WooCommerce-conversie
* Oude regex/HTML prijsmanipulatie verwijderd
* Legacy en nieuwe category grid samengevoegd tot één renderer en één assetset; [alle_categorieen] blijft als alias werken
* Translation map wordt per request gecachet en admin gettext-overrides zijn beperkt
* Account-notificatiefilters gecorrigeerd zodat WordPress geen ongeldige email-arrays ontvangt
* Medical accordion adminlayout responsief gemaakt en save-capability/noncecontrole aangescherpt
* Uninstall ruimt wisselkoers, cron, transient en plugin-eigen category-meta op
* Plugin author/release metadata opgeschoond

= 1.2.7 =
* Voorkomt activatiefouten wanneer oudere DoctorCura UI-code of gemigreerde Code Snippets dezelfde functies/classes al hebben geladen
* Pluginconstanten en lifecycle-hooks worden niet dubbel geregistreerd wanneer een oudere UI-kopie actief is
* Het nieuwe neutrale loginlabel blijft afzonderlijk laden

= 1.2.6 =
* Neutraal WooCommerce-loginlabel toegevoegd: Konto, Account en Compte
* Loginlabel beperkt tot het WooCommerce-accountscherm
* Pluginmetadata en versiedocumentatie bijgewerkt

= 1.2.5 =
* Currency switcher wordt pas geladen nadat WooCommerce beschikbaar is
* Voorkomt dat [currency_switcher] als platte tekst zichtbaar blijft door verkeerde plugin-laadvolgorde

= 1.2.4 =
* Duitse storefront-overrides beperkt tot de Duitse locale
* Engelse en Franse product-, prijs-, currency- en accessibilitylabels toegevoegd
* Locale-afhankelijke decimaal- en duizendtalseparatoren toegevoegd
* Medische accordionlabels per taal gecorrigeerd

= 1.2.3 =
* Security hardening for taxonomy save and AJAX/cookie handling
* Added deactivation cleanup for scheduled currency update cron
* Plugin metadata and text domain consistency improvements

= 1.2.0 =
* Add-to-cart UI verplaatst naar assets
* Toegankelijkere swatches
* Minder fragiele frontend vertaalfallback
* Readme opgeschoond
