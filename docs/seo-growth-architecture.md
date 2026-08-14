# SEO growth architecture

The new public architecture has one multilingual Pickup Locations hub, seven real pickup-location pages, and a small controlled set of fleet-backed rental-option pages. It does not create city-and-keyword combinations.

## Registries and rendering

`inc/locations.php` remains the source of truth for pickup keys. `inc/landing-pages.php` adds presentation copy, controlled commercial-intent rules, relation helpers, and the safe provisioners. The reusable templates are `template-pickup-locations.php`, `template-location.php`, and `template-rental-option.php`. All fleet cards use `Rentacar_Core_Vehicle_Repository`; no availability is implied.

Intent rules are: automatic = structured transmission value, 7/9-seat = passenger capacity at least 7/9, family = at least 5 seats, and economy = the six lowest valid starting prices. A page requires its configured minimum matching vehicles. `no_credit_card` is deliberately never provisioned without a separately verified business-policy implementation.

## Provisioning and languages

Run `wp rentacar seo locations` or `wp rentacar seo intents` for a dry run. Add `--apply` only after reviewing the output. The commands create missing IT/EN/RO/RU pages, assign Polylang relations, templates, keys, indexability, and Rank Math metadata. Existing location pages are found by `_rentacar_location_key`, so airport pages are preserved. Editor content is not replaced.

## Metadata, schema, and linking

Rank Math remains metadata and JSON-LD renderer. The theme supplies approved G&D Rent A Car site identity, current-language canonical behavior, and enriches the Rank Math graph with CollectionPage/ItemList for hubs and rental options, plus Service for location pages. Organization and WebSite IDs remain global. No availability, branch, review, or reservation schema is fabricated.

Location and rental-option templates surface related pickup locations and live fleet cards. Guides may be queried only when published and explicitly indexable; editors can add `_rentacar_related_keys` to an approved guide for a narrow relation. Empty guide sections are not rendered.

## Quality gate and editor responsibilities

Run `npm run seo:audit` against LocalWP. It writes JSON, CSV, and Markdown reports to `docs/generated/` and reports high-confidence technical errors separately from editorial warnings. Validate visible copy, images, operational details, translations, and approval before enabling indexation.

To add a future location, first add it to the authoritative pickup registry, then add native copy and an approved image or leave imagery absent. To add an intent, add one structured matching rule and a meaningful minimum inventory threshold. Never automate payment-policy claims, location facts, reviews, availability, addresses, schedules, or generic city × keyword pages.
