# Vehicle field map

## `cars` post type fields visible in legacy source

| Field/key | Use | Source |
| --- | --- | --- |
| `gallery` repeater; `image` sub-field | Vehicle slider images | `single.php:73–95` |
| `gearbox` | Manual/automatic label | `single.php:100–105`; fleet templates |
| `air_conditioning` | Yes/no label | `single.php:106` |
| `max_passagers` | Passenger count | `single.php:107` |
| `doors` | Door count | `single.php:108` |
| `price_1_days_1`, `price_1_days_2`, `price` | First daily-rate band | `single.php:42–50`, `117–127` |
| `price_2_days_1`, `price_2_days_2`, `price2` | Second daily-rate band | same |
| `price_3_days_1`, `price_3_days_2`, `price3` | Third daily-rate band | same |
| `price4` | Rate after third band | `single.php:49`, `127` |
| featured image | Vehicle image | `single.php:72`, `985` |

## Runtime ACF inventory

**Verified by WordPress runtime.** Five active groups contain **24 fields including repeater children**. The complete sanitized field key/name/label/type/parent/required/default/return-format/conditional/location inventory is [local-wordpress-audit.json](generated/local-wordpress-audit.json).

- **Car fields** (`group_5c571f7045be6`, `post_type == cars`): gallery repeater `field_5ab2613571fba` with image child `field_5ab261bd4c3e9` (attachment-ID return); gearbox `field_5ab2617071fbd` (select/value return); doors `field_5ab2617e71fbf`; max_passagers `field_5ab2618871fc0`; air_conditioning `field_5ab264d15534e` (true/false, default false).
- **Price list** (`group_5c571f70782a4`, `post_type == cars`): price and threshold keys listed above; all optional numbers with blank defaults.
- **coordinates** (`group_5c571f705a3a6`, `post_type == page`): coordinates and coordinates_2 text fields.
- **FAQ fields** (`group_5c571f706462a`, template `template-faq.php`): faq repeater with question text and answer WYSIWYG children.
- **Home page** (`group_5c571f706f05b`, template `template-homepage.php`): text_header/text_seo WYSIWYG and text_field_car text fields.

All group fields are optional and returned no conditional logic. Groups are database/runtime supplied; no PHP or local-JSON origin marker was detected. The `cars` post type has 156 published posts, rewrite `cars`, no archive/REST, and no taxonomy.
