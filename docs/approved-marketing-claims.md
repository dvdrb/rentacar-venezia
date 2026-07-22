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

## Requires owner approval before enabling

| Claim key | Proposed label | Status |
| --- | --- | --- |
| `no_credit_card` | No credit card required | Disabled / unresolved |
| `no_deposit` | No deposit required | Disabled / unresolved |
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
