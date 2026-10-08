# Repository Guidelines

## Project Structure & Module Organization

This repository is a minimal WordPress plugin with an editable **Template Message** block. `plugin-template.php` registers the compiled block on WordPress’s `init` hook. `src/index.js` implements its editor and saved markup; `src/block.json` defines metadata and attributes; `src/style.scss` supplies styles. Generated `build/` assets are ignored by Git. `readme.txt` contains WordPress plugin information, while `.wp-env.json` configures local WordPress with PHP 8.2. No dedicated test directory currently exists.

## Build, Test, and Development Commands

Use Node.js 20.19+ and npm 10+; local WordPress requires Docker running.

- `npm install`: install dependencies; commit the generated `package-lock.json`. Use `npm ci` once a lockfile exists.
- `npm run build`: compile production block assets before activating the plugin.
- `npm run env:start`: start WordPress at `http://localhost:8888`.
- `npm start`: watch and rebuild changes in `src/`.
- `npm run lint:js` and `npm run lint:css`: check JavaScript and styles using WordPress rules.
- `npm run format`: format source files with WordPress tooling.
- `npm run plugin:zip`: build and package the distributable plugin.
- `npm run env:stop`: stop WordPress while preserving local data.

## Coding Style & Naming Conventions

Follow `.editorconfig`: tabs by default, two spaces for JSON, UTF-8, LF endings, and a final newline. Match existing WordPress-style spacing in PHP and JavaScript. Prefix PHP functions with `plugin_template_`; keep the `plugin-template` text domain consistent across PHP, metadata, and translated strings. Use `@wordpress/i18n` for user-facing editor text. Edit source files rather than generated assets.

## Testing Guidelines

No automated test suite or coverage threshold is configured. For code changes, run the build and both linters. In local WordPress, insert Template Message, edit its text, save, check frontend output, and reopen the editor to confirm persistence. Check activation with `npm run env -- run cli wp plugin list`.

## Commit & Pull Request Guidelines

Git history is unavailable in this checkout, so no existing commit convention can be verified. Use concise, imperative subjects, such as `Fix message block styling`. Pull requests should describe the change, link relevant issues, record validation performed, and include screenshots for visual changes.

## Configuration & Compatibility

Use ignored `.wp-env.override.json` for local overrides. Local credentials `admin` / `password` are development-only. Avoid `wp-env clean` or `destroy` unless intentionally deleting local data. Renaming `plugin-template/message` after publication requires a content migration.
