# Editorial translation audit

Audited: 2026-07-28  
Locales: Italian (`it`), English (`en`), Romanian (`ro`), Russian (`ru`)

## Result

The code-owned interface strings are covered by the theme translation map. The
translation coverage check passes. The remaining gaps are WordPress-managed
editorial data, which is deliberately not replaced by the theme map. Those
items must be corrected in their locale-specific WordPress records.

## Confirmed public-page defects

| Priority | Locale | Public URL | WordPress page ID | Finding | Required correction |
| --- | --- | --- | ---: | --- | --- |
| P0 | Italian | `/faq/` | 3161 | The page title, H1, FAQ section headings, and question/answer content are in English. | Translate the complete page into Italian. |
| P1 | Italian | `/cookie-policy/` | 5179 | The visible page title/H1 is `Cookie Policy`; the policy body is Italian. | Translate the title to Italian. |
| P1 | Romanian | `/ro/results/` | 3232 | The visible page title/H1 is `Results`. | Translate it to `Rezultate`. |

`/ro/results/` is a legacy public route rather than a primary navigation
destination, but it is still reachable and therefore needs to be translated.

## Translation-quality corrections

These records are in the intended language but should be corrected for
customer-facing copy quality.

| Locale | Public URL | WordPress record ID | Current text | Required correction |
| --- | --- | ---: | --- | --- |
| English | `/en/frequents/` | 3125 | `Frequent Question` | Use `Frequently asked questions`. |
| Italian | `/total/` | 3231 | `Rezultati` | Use `Risultati`. |
| Russian | `/ru/transferul-de-la-aeroport/` | 3842 | `Трансфер из аеропорта` | Correct `аеропорта` to `аэропорта`. |

## Fleet-title defects

The four localized vehicle records currently repeat the same Italian
descriptor in the title. Brand and model names are expected to remain the
same; the Italian descriptor must be localized per language (for example,
`Benzina` is not appropriate for the English, Romanian, or Russian listing).

| Current shared title | Affected vehicle record IDs |
| --- | --- |
| Alfa Romeo Tonale \| 1.6 Benzina | 4096, 4413, 4414, 4415 |
| BMW serie 4 xdrive \| Diesel | 4671, 4680, 4681, 4682 |
| Citroën 2024 C3 \| 1.2 Benzina | 4024, 4395, 4396, 4397 |
| Dacia Duster \| 1.6 benzina | 4062, 4374, 4375, 4376 |
| Fiat panda \| Benzina | 4691, 4696, 4697, 4698 |
| Ford Mondeo \| 1.5 benzina | 4756, 4761, 4762, 4763 |
| NISSAN QASHQAI \| 1.3 Benzina | 4965, 4970, 4971, 4972 |
| Opel Corsa \| 1.2 BENZINA | 4043, 4377, 4378, 4379 |
| Peugeot 3008 \| 1.6 Benzina | 273, 4434, 4435, 4436 |
| RENAULT CAPTUR \| Benzina | 4101, 4425, 4426, 4427 |
| Renault Megane SW \| 1.3 benzina | 2980, 4419, 4420, 4421 |
| Seat Arona \| Benzina | 4991, 5000, 5001, 5002 |
| Skoda Kamiq \| Benzina | 4663, 4668, 4669, 4670 |
| Suzuki Baleno \| 1.4 Benzina | 2874, 4401, 4402, 4403 |
| Suzuki Vitara \| 1.5 Benzina | 314, 4437, 4438, 4439 |
| Tesla model 3 \| elettrica | 4655, 4660, 4661, 4662 |
| Toyota yaris \| ibrida | 4683, 4688, 4689, 4690 |
| VOLKSWAGEN TAIGO \| 1.0 BENZINA | 4961, 4962, 4963, 4964 |

These are a data issue, not an interface-string issue: vehicle titles are
editorial fields and are intentionally separate between Polylang records.

## Content checked and already localized

- Main reservation and contact interface, including the language switcher,
  vehicle-card labels, pricing interface, and validation messages.
- Homepage UI in Italian, Romanian, and Russian (checked in the running local
  site).
- The four language variants of the guides, places, privacy policy, current
  legal pages, and airport pages.
- The four language variants of the transfer article.

## Method and limits

- Inspected the running LocalWP site at the listed public routes.
- Enumerated the public WordPress REST page data and the maintained Polylang
  translation inventory; page and vehicle language relationships are present.
- Audited all vehicle titles in the maintained vehicle inventory for Italian
  descriptors repeated across localized records.
- Ran `tests/php/theme-translation-coverage-test.php`; it passed. This test
  covers code-owned theme strings, not editor-managed WordPress content.

No customer-facing content or database record was changed during this audit.
