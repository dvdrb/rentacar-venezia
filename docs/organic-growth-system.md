# Organic growth system

## Implementation assessment

### A. Already implemented correctly

- Multilingual landing-page provisioning, Polylang relation support, Rank Math
  ownership, canonical/noindex safeguards and factual schema boundaries.
- Current-fleet cards, indicative pricing, direct contact actions and a
  reservation **request** flow that does not promise instant availability.
- Physical GBP-backed locations are separated from station, hotel and
  city-access pickup services.

### B. Partially implemented

- Airport, fleet and economy templates already offered useful current-inventory
  paths, but needed one reusable policy proposition and better context-preserving
  availability links.
- Existing lifecycle storage recorded request/delivery state but did not model
  confirmed/completed rentals or review eligibility.

### C. Added in this change

- Verified reservation claims, no-credit-card intent eligibility/provisioning,
  scoped Venice/home metadata, acquisition context, lifecycle/review support
  and focused test coverage.

### D. Incorrect or risky before this change

- The generic no-deposit wording could be confused with the approved policy.
- Frontend request success emitted `reservation_confirmed`, even though no
  business confirmation had occurred.
- Existing intent provisioning could overwrite editor-managed Rank Math page
  metadata during an apply.

### E. Owner decisions still required

- Exact payment-method, document, age, cancellation and flight-delay facts
  before they are added to commercial copy.
- Any vehicle-specific security-deposit amounts not already approved in the
  established rental policy; do not infer them from fleet class or price.
- Editorial approval and factual research for every proposed guide and for
  any SUV/premium category page.

## Positioning and policy

G&D Rent A Car is the public brand. Google Business Profile is the source of
truth for a genuine physical location’s public name, NAP, hours and Google
identity. Stations, hotels and city-access points are service/pickup options,
not physical branches.

The approved reservation wording is centralized in `Rentacar_Core_Marketing_Claim_Registry`: reserve without a credit card or advance reservation deposit; a security deposit is required at pickup. Generic “no deposit” remains disabled.

## Intent ownership and internal links

| Owner | Primary intent | Internal paths |
| --- | --- | --- |
| IT homepage | Broad Italian Treviso/local rental | Fleet, Treviso Airport, locations |
| EN homepage | Broad Venice car rental/car hire | Fleet, Venice Airport, locations |
| Venice Airport EN | Car rental Venice Airport | Fleet preselected to VCE, no-credit-card page |
| Venice Airport IT | Noleggio auto aeroporto Venezia | Fleet preselected to VCE, locations |
| Treviso Airport EN/IT | Treviso Airport rental intent | Fleet preselected to TSF, locations |
| No-credit-card page | Reservation without credit card | Current fleet, Venice/Treviso pickup pages |
| Economy | Economy/affordable rental | Current fleet, relevant pickup pages |

One intent has one multilingual canonical page. Contextual fleet links preserve
only validated trip fields; filtered fleet pages are noindexed/canonicalized.

## Funnel, analytics and attribution

The flow is landing page → availability/fleet → vehicle → reservation request
→ confirmed reservation → completed rental → review request. A form success is
only `reservation_submitted`; the frontend never emits
`reservation_confirmed`. Admin lifecycle controls record confirmed and
completed business states, and emit a server-side lifecycle hook for a future
approved reporting integration.

The request stores only minimal acquisition context: first and last landing
path, referrer origin/path, UTM source/medium/campaign, language, pickup,
return and vehicle. No customer PII is sent with analytics events.

## Review workflow

When an admin marks a genuine rental completed, the request becomes review
eligible. The admin screen selects the location-specific real review URL from
the pickup context, offers a copyable manual message, and can mark it
requested. It never sends a message, asks for a rating, or provides an
incentive.

## Technical SEO and content rules

Rank Math is the sole JSON-LD renderer. Schema describes only verified
Organization, GBP-backed AutoRental locations, pickup Services, pages, fleet
items and cars. Do not add ratings, reviews, availability, invented locations
or pricing to schema.

Create supporting guides only after factual/legal review and link each to a
relevant airport/location page, fleet and availability path. Current editorial
backlog: airport pickup guides, security-deposit explainer, required-documents
guide, Venice driving/ZTL and parking guidance, and Venice/Treviso-to-Dolomites
guides. Do not publish without owner research/approval.

Create a commercial category only when the real fleet has enough inventory,
the intent is distinct, and the page can offer genuinely useful unique content.
SUV and premium/luxury demand require an inventory audit before provisioning.

## Local migration and rollout

Provisioning is deterministic, idempotent and dry-run by default. It does not
replace editor content. Review dry-run output before any local apply:

```sh
wp rentacar seo intents
wp rentacar seo locations
wp rentacar seo noindex-legacy-pages
wp rentacar seo normalize-metadata
```

`noindex-legacy-pages` is intentionally review-gated: its imported-data dry
run can include legal pages. Runtime robots and sitemap exclusions already
enforce the current indexability rule, so do not persist that command's output
until the owner has explicitly reviewed the affected legal URLs.

Apply only in the intended local/staging WordPress environment:

```sh
wp rentacar seo intents --apply
wp rentacar seo locations --apply
wp rentacar seo normalize-metadata --apply
```

Only after owner review of its exact dry-run output:

```sh
wp rentacar seo noindex-legacy-pages --apply
```

Before production rollout: verify GBP NAP against the public site, review each
new translation/page relation and Rank Math metadata, take a database backup,
run the documented test suite, then verify canonical/hreflang, CTA context,
request submission and the review workflow on live. No deployment command is
part of this implementation.

### Manual owner actions

- Reconcile each genuine physical location's public name, address, postal
  code, locality, region, telephone, hours and Maps identity with GBP. Report
  any GBP discrepancy; do not silently change GBP from this project.
- Confirm payment methods, documents, driver ages, cancellation terms,
  flight-delay handling and any future per-vehicle deposit values before they
  appear in commercial copy.
- Review the legal URLs shown by `noindex-legacy-pages` before allowing its
  persistent-metadata apply command.
- Approve factual research and editorial copy before publishing any backlog
  guide or SUV/premium category page.

### Safe production rollout checklist

1. Take a verified production database/files backup and record the current
   Rank Math, Polylang and WordPress versions.
2. Compare the staged public NAP and review URLs against GBP-backed records.
3. Review the no-credit-card translations, intent ownership, canonical,
   hreflang and Rank Math previews on staging.
4. Apply only the reviewed, idempotent content commands; never apply the
   legacy noindex command without the legal-page review above.
5. Verify the homepage, Venice Airport, Treviso Airport, economy, fleet,
   no-credit-card page, selected-vehicle request flow and completed-rental
   review workflow on desktop and 320/390/430px mobile widths.
6. Confirm a request records `reservation_submitted` only; confirm that an
   administrator, not frontend JavaScript, records confirmed/completed state.
7. Purge only normal site caches and re-check canonical, robots, hreflang,
   XML sitemap, schema and console errors. Do not submit URLs to Search
   Console as part of this rollout.

## Measurement

Measure organic landing sessions, CTR/position by owner page, availability
searches, vehicle selections, requests submitted, confirmed reservations,
completed rentals and review-request coverage at 14, 28, 60 and 90 days.

- **Day 14:** validate tracking volume and funnel event ratios; look for
  broken context handoffs or an unusual mobile abandonment point.
- **Day 28:** compare CTR/impressions for Venice Airport EN, Venice EN home,
  Treviso Airport IT, economy and no-credit-card owners against their
  pre-release baseline.
- **Day 60:** compare landing-page-to-submitted-request and
  submitted-to-confirmed ratios by organic source and pickup location.
- **Day 90:** assess completed rentals and neutral review-request coverage;
  decide whether owner-approved guide research or a category evaluation is
  justified by demand and inventory.
