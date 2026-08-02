import { existsSync, readFileSync, statSync } from 'node:fs';
import { extname, resolve, sep } from 'node:path';

const themeDirectory = resolve('theme/rentacar-venezia-v2');
const distDirectory = resolve(themeDirectory, 'assets/dist');
const manifestPath = resolve(distDirectory, 'manifest.json');
const expectedEntries = {
  main: '.js',
  analytics: '.js',
  style: '.css'
};

function fail(message) {
  process.stderr.write(`Asset manifest check failed: ${message}\n`);
  process.exitCode = 1;
}

if (!existsSync(manifestPath)) {
  fail(`missing ${manifestPath}`);
} else {
  let manifest;
  try {
    manifest = JSON.parse(readFileSync(manifestPath, 'utf8'));
  } catch (error) {
    fail(`invalid JSON in ${manifestPath}: ${error instanceof Error ? error.message : 'unknown error'}`);
  }

  if (!manifest || typeof manifest !== 'object' || Array.isArray(manifest)) {
    fail('manifest must be an object');
  } else {
    const entries = Object.entries(manifest);
    const namedEntries = Object.fromEntries(entries.flatMap(([, entry]) => {
      if (!entry || typeof entry !== 'object') return [];
      if (typeof entry.name === 'string') return [[entry.name, entry]];
      return typeof entry.src === 'string' && entry.src.endsWith('/style.css') ? [['style', entry]] : [];
    }));

    for (const [name, extension] of Object.entries(expectedEntries)) {
      const entry = namedEntries[name];
      if (!entry || typeof entry.file !== 'string' || !entry.file) {
        fail(`missing ${name} entry`);
      } else if (extname(entry.file) !== extension) {
        fail(`${name} target must be a ${extension} file`);
      }
    }

    const referencedFiles = [];
    const referencedEntries = [];

    for (const [entryName, entry] of entries) {
      if (!entry || typeof entry !== 'object' || typeof entry.file !== 'string' || !entry.file) {
        fail(`entry ${entryName} has no compiled file`);
        continue;
      }

      referencedFiles.push(`${entryName}: ${entry.file}`);
      for (const field of ['css', 'assets']) {
        if (entry[field] !== undefined && !Array.isArray(entry[field])) {
          fail(`entry ${entryName} has an invalid ${field} list`);
          continue;
        }
        for (const file of entry[field] || []) referencedFiles.push(`${entryName}: ${file}`);
      }
      for (const field of ['imports', 'dynamicImports']) {
        if (entry[field] !== undefined && !Array.isArray(entry[field])) {
          fail(`entry ${entryName} has an invalid ${field} list`);
          continue;
        }
        for (const reference of entry[field] || []) referencedEntries.push(`${entryName}: ${reference}`);
      }
    }

    for (const reference of referencedEntries) {
      const [, entryName] = reference.split(': ', 2);
      if (!Object.prototype.hasOwnProperty.call(manifest, entryName)) fail(`manifest reference is missing: ${reference}`);
    }
    for (const reference of referencedFiles) {
      const [, file] = reference.split(': ', 2);
      const targetPath = resolve(distDirectory, file);
      if (!targetPath.startsWith(`${distDirectory}${sep}`)) {
        fail(`target escapes assets/dist: ${reference}`);
      } else if (!existsSync(targetPath) || !statSync(targetPath).isFile()) {
        fail(`target is missing: ${reference}`);
      }
    }

    if (!process.exitCode) process.stdout.write(`Asset manifest check passed: ${Object.keys(expectedEntries).join(', ')}\n`);
  }
}
