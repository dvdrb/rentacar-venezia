# Approved marketing claims

## Rule

The `Rentacar_Core_Marketing_Claim_Registry` is deny-by-default. A template may
only show a claim when its configuration has explicitly enabled it, supplied any
necessary condition, and selected the display location. Templates must never
hard-code a business-policy claim.

## Safe general messages

These are descriptive UI messages rather than policy promises and may be used
where appropriate:

- Local assistance
- WhatsApp contact
- Multilingual support
- Choose your preferred vehicle
- Availability confirmed personally
- Indicative price
- Final price confirmed by our team

## Verified reservation policy

The following owner-approved facts are enabled by default in the registry.
They are rendered only through the registry/component; templates must not
restate or alter them. They apply to the reservation stage, not to every later
rental condition.

| Claim key | Status | Public meaning |
| --- | --- | --- |
| `no_credit_card_to_reserve` | Approved | A credit card is not required to make a reservation. |
| `no_advance_reservation_deposit` | Approved | No advance payment/deposit is required to make a reservation. |
| `security_deposit_at_pickup` | Approved condition | A security deposit is required when the vehicle is collected. |
| `no_deposit` | Disabled | Must never be used as a synonym for the two reservation-stage facts. |

The legacy `no_credit_card` lookup remains a compatibility alias for
`no_credit_card_to_reserve`; new code must use the explicit key.

Exact policy summary:

- IT: “Prenota senza carta di credito e senza deposito anticipato. Il deposito cauzionale viene richiesto al momento del ritiro.”
- EN: “Reserve without a credit card or advance reservation deposit. A security deposit is required at pickup.”
- RO: “Rezervați fără card de credit și fără avans la rezervare. La preluarea mașinii este necesar un depozit de garanție.”
- RU: “Бронируйте без кредитной карты и без предоплаты при бронировании. При получении автомобиля требуется залог.”

## Requires owner approval before enabling

| Claim key | Proposed label | Status |
| --- | --- | --- |
| `free_cancellation` | Free cancellation | Disabled / unresolved |
| `no_hidden_fees` | No hidden fees | Disabled / unresolved |
| `unlimited_mileage` | Unlimited mileage | Disabled / unresolved |
| `free_airport_delivery` | Free airport delivery | Disabled / unresolved |
| `insurance_included` | Insurance included | Disabled / unresolved |
| `pay_on_arrival` | Pay on arrival | Disabled / unresolved |
| `best_price_guarantee` | Best price guarantee | Disabled / unresolved |
| `service_24_7` | 24/7 service | Disabled / unresolved |

The registry can additionally store a translated label, a condition/footnote,
display-location keys and a conditions-page URL. It does not create settings or
change visible content until the inactive plugin is activated and an owner
configures a claim.
