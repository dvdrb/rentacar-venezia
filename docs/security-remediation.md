# Security remediation

## Completed locally

- The copied legacy SMTP username and password in `legacy/active-theme/functions.php` were replaced with explicit redaction placeholders.
- Git history was previously rebuilt before this branch so the original credential is not tracked. The current branch must keep it that way.
- A LocalWP-only MU-plugin link installs `scripts/local-mail-safety.php`. It short-circuits `wp_mail()` only when the site hostname is local. This protects the legacy enquiry flow during development without changing production code or settings.

## Required owner action

Treat the legacy SMTP credential as compromised and rotate it through the appropriate mail provider process. Do not copy its replacement into source code, WordPress options, or Git.

## Boundaries

No production service was accessed. No mail was submitted or sent to verify this protection.
