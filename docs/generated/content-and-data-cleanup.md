# Content and data cleanup findings

No editor-owned content, slugs, vehicle titles, images or daily prices were altered.

## Completed local data work

- The recipient option is configured locally for `robudavid21@gmail.com`; no real email was sent during verification.
- Starting-price metadata was backfilled for all 156 local vehicle records after the dry run.
- The 12 records whose titles explicitly state `HYBRID` were normalized to `hybrid`; ambiguous powertrains remain unset for later editorial review.
- Five invalid price-band configurations, including all four language copies (20 records), had only their day boundaries corrected. The daily prices did not change: Peugeot 3008 and Suzuki Baleno now start at day 3 with no overlap; Suzuki Vitara now covers day 5; Ford Grand C-Max and Mercedes V-Class no longer overlap at the third band.
- The post-change `wp rentacar pricing audit` reports no data issues.
- Optional extras are configured locally as child seat (€5 per rental day), additional driver (€5 per rental day), and authorization for abroad (€80 fixed).

## Owner review records

- Existing audit examples requiring CMS review: `Fiat panda`, `Toyota yaris`, all-caps diesel titles, and the `Hatcback` typo in the Fiat Tipo title.
- Review the remaining unset powertrains manually. The inference routine intentionally leaves titles without an explicit hybrid/electric signal untouched.
- Review translated editor content, address/hours conflicts and marketing claims against `docs/content-quality-findings.md` and `docs/approved-marketing-claims.md`.

## Safe metadata commands

- `wp rentacar vehicles backfill-starting-price --dry-run`
- `wp rentacar vehicles backfill-starting-price --apply`
- `wp rentacar vehicles infer-powertrain --dry-run`
- `wp rentacar vehicles infer-powertrain --apply` (only after owner review)
