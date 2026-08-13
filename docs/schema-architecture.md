# Structured-data architecture

Rank Math is the sole runtime JSON-LD renderer. The theme enriches its graph through `rank_math/json_ld` in `theme/rentacar-venezia-v2/inc/schema.php`; templates never print independent schema scripts.

## Global graph

`https://rentacarvenezia.it/#organization` is the single G&D Rent A Car entity and has the types `Organization` and `AutoRental`. Its business details come from `rentacar_venezia_v2_business_data()`. `https://rentacarvenezia.it/#website` is the single WebSite and points to that publisher. Every indexable page points to this WebSite and organization and uses its current WordPress/Polylang canonical URL and locale.

The module keeps the path supplied by WordPress/Polylang but normalizes same-site schema URLs and media to `https://rentacarvenezia.it`. This prevents LocalWP URLs from entering rendered JSON-LD while avoiding manual language-route construction.

| Page | Schema |
| --- | --- |
| Homepage | WebPage |
| Contact | ContactPage → Organization |
| Fleet | CollectionPage → ItemList |
| Car | WebPage → Car → Offer/UnitPriceSpecification |
| Airport/station/Piazzale Roma/hotel pickup | WebPage → Service → Airport, TrainStation, Place, or City |
| Guides | CollectionPage → ItemList of approved guides |
| Approved guide | BlogPosting |
| FAQ, How It Works, requirements, terms | ordinary WebPage |
| Transactional/noindex pages | no schema enrichment |

Vehicle prices are a truthful starting daily rental rate: `Offer.priceSpecification` is a `UnitPriceSpecification` in EUR for one `DAY`. Offers intentionally never include availability. Cars do not claim a purchase price, stock status, vehicle listings, ratings, or confirmed reservations.

Location services are applied only to an existing published location page with `_rentacar_location_key`. Airports are service areas, not G&D branches or business addresses. The helper is future-ready for station, Piazzale Roma, and hotel-pickup pages; hotel pickup is a city service area, never a fabricated Hotel entity.

FAQ content remains visible and accessible, but the former custom FAQ JSON-LD emitter was removed. If FAQ schema is configured in Rank Math, Rank Math remains its single owner. Breadcrumbs also remain Rank Math-owned and follow the visible breadcrumb helper.

For local validation, run the PHP schema test and inspect the single Rank Math JSON-LD script on representative IT/EN/RO/RU URLs. Confirm all JSON parses, `@id` values are unique, and canonical URLs/languages follow WordPress and Polylang. To extend a future location page, create the page normally, set its existing `_rentacar_location_key`, and do not add template-specific JSON-LD.

## Contact content parity

Contact page copy is WordPress editor content, not template-owned business data. Before a production release, preview its explicit multilingual repair with:

```sh
wp rentacar contacts migrate-content --path=/path/to/wordpress
```

The command is dry-run by default and reports the IT/EN/RO/RU page IDs, detected stale fields, and changed blocks. Apply only after reviewing that output:

```sh
wp rentacar contacts migrate-content --apply --path=/path/to/wordpress
```

It only targets published pages using `page-templates/template-contact.php`, resolves all four Polylang languages, updates the heading/contact/hours/request blocks from the approved business record, removes known localhost and airport-office claims, and preserves unrelated editor paragraphs. It is idempotent; it does not update schema entities or create airport business branches.
