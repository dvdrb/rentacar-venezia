import tseslint from '@typescript-eslint/eslint-plugin';
import parser from '@typescript-eslint/parser';

export default [{ files: ['theme/rentacar-venezia-v2/assets/src/ts/**/*.ts'], languageOptions: { parser }, plugins: { '@typescript-eslint': tseslint }, rules: { '@typescript-eslint/no-unused-vars': 'error' } }];
