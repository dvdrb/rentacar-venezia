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

Page-level ACF fields: `text_header`, `text_seo` (`template-homepage.php:12,135`), `coordinates`, `coordinates_2` (`template-homepage.php:94–95`; `page.php:12–13`), and a commented `faq` repeater (`template-faq.php:27–35`). Preserve all keys until an approved migration. ACF field groups, return formats, and translation settings are not knowable from source alone and need ACF/WPML admin verification.
