# Hard-coded ID map

The legacy theme was searched for numeric arguments in WordPress object APIs. Runtime resolution uses the local clone only.

| ID | Source/context | Object | Language | Status | Local URL | Conclusion |
| --- | --- | --- | --- | --- | --- | --- |
| 6 | `single-old.php:176`, terms link | `Terms and Conditions` page | en | publish | `/en/term/` | verified by both |
| 20 | `single.php:67`, popup-text meta | `Home page` page | en | publish | `/en/rent-car-treviso/` | verified by both |
| 23 | `single.php:65`, popup-text meta | Russian page | ru | publish | encoded URL in JSON | verified by both |
| 122 | `sidebar.php:10`, results target | `Results` page | en | publish | `/en/prenotazioni/` | verified by both |
| 135 | `functions.php:1159`, success redirect | `Your request has been sent successfully!` page | en | publish | `/en/success/` | verified by both |

Other numeric literals in the source are prices, time thresholds, date offsets, or loop bounds. Homepage ID 3158 and primary-menu ID 2 are runtime configuration values, not hard-coded theme IDs.
