# DoctorCura UI

> **Portfoliostatus:** Experiment · zelfstandige WordPress/WooCommerce-presentatieplugin

DoctorCura UI bevat storefront-, product- en interfacecomponenten voor DoctorCura. De plugin blijft een afzonderlijk pakket van [DoctorCura Core](https://github.com/Yolol100/doctorcura-core), zodat presentatie en domeinlogica onafhankelijk kunnen worden getest, geactiveerd en teruggedraaid.

## Verantwoordelijkheid

- category grids en archivepresentatie;
- medical accordion;
- productvariatie- en add-to-cart-interface;
- currency switcher;
- prijs-, vertaal- en accessibilitylabels;
- storefrontafbeeldingen en sliders;
- bestaande hooks die adminmails over wachtwoordwijzigingen en nieuwe gebruikers onderdrukken;
- een bestaand `send_email_change_email`-filter dat de beveiligingsmelding aan het vorige e-mailadres van de gebruiker onderdrukt na een account-e-mailwijziging.

## Relatie met DoctorCura Core

```text
DoctorCura Core → primaire account-, checkout- en orderflows
DoctorCura UI   → storefront/productpresentatie + huidige account-notificatiefilters
WordPress + WooCommerce → gedeeld runtimeplatform
```

De plugins hebben afzonderlijke versies en releasepakketten. UI heeft geen harde runtimeafhankelijkheid van Core, maar beïnvloedt momenteel ook enkele accountgerelateerde adminnotificaties. Deactivatie kan dat notificatiegedrag daarom wijzigen; gezamenlijke productie-uitrol en rollback vereisen een stagingtest van de gebruikte combinatie.

## Compatibiliteit

| Onderdeel | Ondersteuning |
| --- | --- |
| WordPress | 6.4 of nieuwer; getest tot 6.8 |
| PHP | 8.1 of nieuwer |
| Huidige versie | 1.2.5 |
| Combinatie | Test UI 1.2.5 samen met Core 1.0.5 op staging voordat beide naar productie gaan |

## Installatie

1. Upload de pluginmap naar `/wp-content/plugins/`.
2. Activeer DoctorCura UI.
3. Controleer WooCommerce-archieven, productpagina's, valuta, vertalingen en keyboardgedrag op staging.
4. Activeer DoctorCura Core afzonderlijk wanneer de account- en checkoutflows nodig zijn.

## Releasegrens

Een nieuwe release vereist minimaal PHP- en JavaScript-syntaxcontrole plus stagingtests van storefront, productvariaties, valuta, vertalingen en deactivatiecleanup. Plaats geen account-, order- of medische gegevens in publieke issues.

## Licentie

GPL-2.0-or-later, zoals vastgelegd in de pluginheader en `readme.txt`.
