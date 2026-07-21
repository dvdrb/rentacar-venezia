# Plugin audit

| Plugin | Version | Classification | Audit note |
| --- | --- | --- | --- |
| Advanced Custom Fields | 6.8.6 | required for existing content | Theme calls ACF functions. |
| ACF Repeater | 2.1.0 | required for existing content | Vehicle `gallery` is a repeater. |
| ACFML | 0.7 | required for translations | Verify field translation preferences. |
| WPML Multilingual CMS | 4.2.4.1 | required for translations/current frontend | Theme invokes WPML language filter. |
| WPML String Translation | 2.10.2 | required for translations | Verify active string registrations. |
| WPML Translation Management | 2.8.3 | required for translations | Verify workflow use. |
| Contact Form 7 | 6.1.6 | unknown and requiring manual verification | No legacy theme integration found. |
| WPForms Lite | 2.0.0.2 | unknown and requiring manual verification | No legacy theme integration found. |
| Classic Editor | 1.7.0 | utility-only | May preserve editor workflow. |
| Classic Widgets | 0.3 | required for current frontend | Legacy header/footer/sidebar widget areas exist. |
| LiteSpeed Cache | 7.8.1 | production-only | Do not treat cached output as source of truth. |
| GTM4WP | 1.22.3 | required for current frontend | Analytics/tagging verification required. |
| Click to Chat for WhatsApp | 4.41 | required for current frontend | Active; no direct legacy-theme API call found. |
| UpdraftPlus | 1.26.5 | utility-only | Backup/restore only; keep all backup data out of Git. |
| All-in-One WP Migration | 7.107 | utility-only | Active; export/import only; keep `.wpress` out of Git. |
| Regenerate Thumbnails | 3.1.6 | utility-only | Do not run during redesign setup. |
| Update Theme and Plugins from ZIP File | 2.0.0 | utility-only | Candidate for later removal after deployment process is agreed. |

**Runtime verified:** all 17 listed third-party plugins are active. `rentacar-core` 0.0.0 is the sole inactive plugin and is intentionally inactive. No persistent must-use plugin exists. Versions were refreshed from the local runtime where they differed from the screenshot. No plugin was activated, deactivated, updated, installed, or removed.
