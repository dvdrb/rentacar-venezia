import { readFile, writeFile } from 'node:fs/promises';
import { resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = resolve(fileURLToPath(new URL('..', import.meta.url)));
const source = resolve(root, 'node_modules/libphonenumber-js/metadata.max.json');
const destination = resolve(root, 'plugin/rentacar-core/data/phone-metadata.json');
const metadata = JSON.parse(await readFile(source, 'utf8'));
const locales = ['en', 'it', 'ro', 'ru'];

const countryNames = Object.fromEntries(Object.keys(metadata.countries).map((country) => [
  country,
  Object.fromEntries(locales.map((locale) => {
    const displayNames = new Intl.DisplayNames([locale], { type: 'region' });
    return [locale, displayNames.of(country) || country];
  })),
]));

metadata.source = {
  package: 'libphonenumber-js',
  packageVersion: '1.13.9',
  metadata: 'max',
  generatedBy: 'scripts/update-phone-metadata.mjs',
};
metadata.country_names = countryNames;

await writeFile(destination, `${JSON.stringify(metadata)}\n`);
