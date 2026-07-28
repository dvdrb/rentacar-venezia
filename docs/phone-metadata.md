# International telephone metadata

`plugin/rentacar-core/data/phone-metadata.json` is the sole, version-controlled
source for the public telephone component and PHP validation service. It is
generated from the `libphonenumber-js` `metadata.max.json` dataset, which is
locked in `package.json` and `pnpm-lock.yaml`.

To update it deliberately after upgrading the dependency, run:

```sh
pnpm run update:phone-metadata
```

The generator also embeds country names in English, Italian, Romanian, and
Russian. It makes no network request at page view or form submission.
