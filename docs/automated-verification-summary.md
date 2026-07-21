# Automated WordPress runtime verification

## Method

WP-CLI is not installed and Local’s shell wrapper is not WP-CLI. The original lookup used LocalPHP without the site-specific `php.ini` and bootstrap, so it lacked the LocalWP MySQL socket. The running LocalPHP-FPM/Nginx site was healthy. A temporary MU-plugin restricted to localhost, the local hostname, and a one-time token completed the read-only WordPress/ACF/WPML audit and wrote `generated/local-wordpress-audit.json`; it was then removed.

## Verified results

- Active theme: **Auto car 1.1.3** (`template=autocar`, `stylesheet=autocar`, no parent).
- Vehicle type: **`cars`**, 156 published posts, rewrite `cars`, no archive/REST, and **no taxonomies**.
- ACF: **5** active groups and **24** fields including repeater children.
- WPML: default **Italian**; enabled **it, en, ro, ru**; language-directory URL mode; browser redirect off; `cars` translated.
- Plugins: **17 active**, **1 inactive** (the deliberately inactive `rentacar-core` placeholder), no persistent MU plugin.
- Homepage: page **3158**, `HOME PAGE`; posts page unset.
- Resolved hard-coded source IDs: **6, 20, 23, 122, 135**.

## Remaining unknowns

Technical discovery is sufficient to begin approved development. Business policy decisions (payments, deposits, insurance, mileage, cancellations, retention) remain owner input. Customer data, form submissions, mailbox data, and secrets were not inspected.
