# Desk Clerk for WordPress

Published package slug: `desk-clerk`. Author: John Kane (`devjkane`).

Drop Clerk's prebuilt visitor components into posts, pages, block-theme templates,
and shortcode-enabled widget/page-builder areas. WordPress accounts and login
remain separate. The original **Template Message** block remains available.

## Install and configure

1. Upload the release ZIP through **Plugins → Add New → Upload Plugin** and activate
   **Desk Clerk**. WordPress 6.3+ and PHP 8.2+ are required.
2. Create a Clerk application and copy its **publishable key** from the Clerk
   Dashboard. Use a `pk_test_…` key for testing and `pk_live_…` for production.
3. Under **Settings → Desk Clerk**, paste the publishable key and save. Never enter a
   Clerk secret key. An invalid submission preserves the previous valid setting;
   clearing the field disconnects the application.
4. Configure your production domain and permitted authentication/redirect origins
   in Clerk before going live. Production Clerk applications need HTTPS. Your
   browser must be allowed to contact the application's Clerk Frontend API domain.
5. Choose a **Theme** under **Settings → Desk Clerk** and save: Default, Simple, Dark,
   Shades of Purple, Neobrutalism, or shadcn. The selection applies to every Clerk
   component and dialog. Default retains Clerk’s built-in appearance; shadcn
   requires compatible shadcn CSS variables and utility styles from your WordPress
   theme. Theme changes appear on subsequent page loads; purge cached pages if needed.
6. Search for **Clerk** in the block inserter and add the control you need, such
   as **Clerk Organization Profile** or **Clerk Sign In**. Save and view the
   published page. Each block also displays a live component preview.

## Live editor previews

Previews start closed. Click **Open Preview** to show the component inline,
and **Close Preview** to hide it. The component loads only while open.

Clerk control blocks render an interactive preview using the saved publishable
key, theme, appearance variables, and options. Changing the Pricing Table
subscriber type reloads the preview. Use **Refresh preview** after updating Clerk
settings in another tab, or **Open in new tab** for a full browser view.
Preview height follows the rendered content and expands when a dialog opens.

Previews use the current browser's Clerk visitor session, independently of
WordPress login. Interactions affect that Clerk account; they are not simulated.
OAuth providers may require **Open in new tab** because they disallow embedded pages.
The isolated preview does not load your WordPress theme's styles or custom fonts,
so those may differ from the published site; the shadcn preset still requires
compatible site styles. Preview access requires permission to edit posts/pages
and an editor nonce. Reload the editor if the preview expires.

## Provider parameters

Under **Settings → Desk Clerk → Advanced → Provider parameters**, set navigation URLs
such as `afterSignOutUrl`, sign-in/sign-up page URLs, forced/fallback authentication
redirects, `waitlistUrl`, and `newSubscriptionRedirectUrl`. Use a site-relative
path such as `/signed-out/`, or a full HTTP/HTTPS URL without credentials.
Configure permitted redirect origins in Clerk for destinations on another domain.

Blank fields keep the existing defaults: authentication and checkout return to
the containing page, while other provider URLs retain Clerk's defaults. A forced
redirect takes precedence over a fallback; setting only a fallback allows Clerk
to honor explicit redirect destinations. Invalid fields preserve their previous
values. **Reset provider parameters** clears only these overrides.

Provider settings apply to blocks, shortcodes, dialogs, and live previews; a
redirect can navigate out of the preview. Function-valued, native-only, and
security configuration props from ClerkProvider are not exposed as text fields.

## Experimental localization

Under **Settings → Desk Clerk → Experimental**, enable **Use the WordPress language
for Clerk components** and save. Localization is off by default. Components,
dialogs, and live editor previews follow **Settings → General → Site Language**,
or the current page locale supplied through WordPress by a multilingual plugin.
Reload pages/previews after changing the setting or language.

The plugin loads only the matching official Clerk translation. Regional variants
use a translation for the same language when available; unsupported languages
and failed translation downloads retain Clerk's default English. Translations
are experimental and may change. Clerk's hosted Account Portal and browser-owned
Google One Tap prompts are outside this setting.

## Appearance overrides

Under **Settings → Desk Clerk → Appearance**, expand **Colors** or
**Typography and sizing**. Overrides apply to every Clerk component and dialog on
top of the selected theme.

- Colors: primary, text on primary, card background, text, secondary text, input
  background/text, borders, and errors. Use the WordPress color picker or a 3- or
  6-digit hex value such as `#6c47ff`.
- Typography: a font family/fallback list (for example `Arial, sans-serif`) and a
  positive font size such as `16px` or `1rem`. Fonts must already be available on
  your site; the plugin does not download fonts.
- Sizing: border radius and spacing in `px`, `rem`, or `em`; `0` is also supported.

Leave a field blank, or use a color picker's **Clear** button, to inherit from the
selected theme. **Reset appearance overrides** clears only the custom variables;
it preserves the theme and publishable key. Invalid values preserve the previous
value for that field. Save changes and reload the published page; purge cached
pages if needed. CSS expressions and arbitrary JSON are not accepted by these
controls.

## Appearance options

The settings page separates connection and theme controls under **General**,
colors and sizing under **Appearance**, visual options under **Layout and links**,
and behavior under the collapsed **Advanced** section. Configure:

- Social buttons: placement above/below the form and automatic, full-width, or
  icon style; logo placement inside/outside the card; raised or flush cards.
- Under **Advanced**, animations, automatic input focus, avatar loading shimmer,
  and optional form fields: choose **Enabled**, **Disabled**, or **Inherit default**.
- Help, terms, privacy, logo image, and logo destination URLs: enter full HTTP or
  HTTPS addresses without embedded credentials.

Inherited or blank values use Clerk's theme and Dashboard defaults. Explicitly
disabled options remain disabled. Flush elevation does not affect profile panels,
popovers, or modals. **Reset appearance options** under **Advanced** clears both
layout/link and behavior options,
preserving appearance variables, theme, and publishable key. Invalid values retain
that field's previous override. Save and reload published pages; purge cached pages
if needed.

## Components and shortcodes

Each component has its own inserter block, prefixed with **Clerk**, and a matching
`[clerk]` shortcode. Blocks have fixed control types; there is no component picker.
Existing **Clerk Component** blocks continue to render with their saved selection,
are hidden from the inserter, and can be converted to the corresponding dedicated
block through WordPress’s block transform menu. Pricing plan type is preserved.
**Clerk Show** remains the nested-content block for signed-in/out visibility.

| Component | Shortcode | Requirements |
| --- | --- | --- |
| Sign In | `[clerk component="sign-in"]` | Sign-in and sign-up flow; signed-in visitors see a status message |
| Sign Up | `[clerk component="sign-up"]` | Clerk application's sign-up settings |
| User Button | `[clerk component="user-button"]` | Signed-in Clerk session |
| User Profile | `[clerk component="user-profile"]` | Signed-in Clerk session |
| Organization Switcher | `[clerk component="organization-switcher"]` | Signed-in session; Organizations enabled in Clerk |
| Organization Profile | `[clerk component="organization-profile"]` | Signed-in session and active organization |
| Organization List | `[clerk component="organization-list"]` | Signed-in session; Organizations enabled in Clerk |
| Create Organization | `[clerk component="create-organization"]` | Signed-in session; organization creation enabled in Clerk |
| Pricing Table | `[clerk component="pricing-table"]` | Clerk Billing enabled with published user plans; available to signed-out visitors |
| Waitlist | `[clerk component="waitlist"]` | Waitlist mode enabled in Clerk |
| Google One Tap | `[clerk component="google-one-tap"]` | Google and Google One Tap enabled in Clerk; signed-out visitor; browser support |

For organization plans, choose **Organizations** in the block's **Plans for**
selector, or use `[clerk component="pricing-table" for="organization"]`. A visitor
must be signed in and select an active organization first. Checkout remains owned
by Clerk. Organization List selection/creation and successful Waitlist completion
return to the current page.

If Pricing Table reports that Clerk Billing is disabled, enable Billing and
publish the appropriate plans in the Clerk Dashboard, then refresh the preview.
This is application configuration; retrying asset loading cannot enable Billing.

Google One Tap requests a browser-controlled, page-level sign-in prompt rather
than mounting an inline panel. Multiple placements share one prompt; it closes
when the visitor signs in or the last visible placement is removed. A browser may
suppress the prompt after dismissal, without a Google session, or because of its
privacy settings. The editor preview allows FedCM; use **Open in new tab** if the
browser restricts the embedded prompt. Successful authentication returns to the
current page. Waitlist remains available in the component picker and live preview.

`[clerk]` defaults to Sign In. Account components offer a Sign In button to
signed-out visitors. Select an organization using an Organization Switcher before
opening Organization Profile. User Button and Organization Switcher use Clerk's
built-in account-management dialogs.

Shortcodes work only where WordPress or the page builder executes shortcodes.
For classic PHP theme templates, use the existing WordPress API:

```php
echo do_shortcode( '[clerk component="user-button"]' );
```

Embedded authentication/profile panels use Clerk's **hash routing**, avoiding
custom WordPress rewrite rules. Place one standalone authentication/profile panel
per page because these panels share the browser's hash; multiple User Buttons and
Organization Switchers can coexist. Successful authentication returns to the
current page. Clerk manages cross-flow links according to its application settings.

## Conditional content with Clerk Show

Insert **Clerk Show**, choose **Visitor is signed in to Clerk** or **Visitor is
signed out of Clerk**, and add any nested WordPress blocks. Both conditions remain
hidden until Clerk loads; visibility then follows the Clerk session, including
sign-in/out changes. Nested Clerk components mount only in visible branches.
The editor shows the nested content for editing, regardless of the author's login.

This controls browser visibility only. All nested content remains in public page
HTML and caches; do not use it for private content or authorization. WordPress
login does not determine the condition. Without a configured key or if Clerk fails
to load, the content stays hidden and a status/error is displayed. Loading errors
offer **Retry**.

## Security, caching, and troubleshooting

Only administrators with `manage_options` can save settings, through WordPress's
nonce-protected Settings API. Only the public publishable key is sent to browsers.
The HTML contains generic placeholders; current visitor identity is resolved by
Clerk after loading, so it can be page-cached without storing account information.

Clerk visitor sessions do not create WordPress users, authorize wp-admin access,
or protect server-rendered content. This plugin does not synchronize roles,
implement webhooks, or provide a WordPress authentication bridge.

The frontend loader runs when components or Show blocks render, including in live
editor previews, and loads Clerk's
browser SDK **6.38.0** and UI bundle **1.39.0** from your application's Frontend API.
These remote assets and Clerk's own network endpoints must be permitted by your
site's Content Security Policy and consent setup. No Clerk network requests are
made by this plugin on pages without configured components.

If Clerk cannot load, the component shows an accessible error and **Retry** button.
Check the key, Clerk domain configuration, network blockers, and SDK response.
For “not configured,” save a valid key. For “assets unavailable,” rebuild the
plugin or reinstall the complete ZIP. Do not put secret keys into shortcodes.

## Development

Use Node.js 20.19+ and npm 10+. Docker is required for local WordPress.

```sh
npm ci
npm run build
npm run env:start
npm start
```

Open `http://localhost:8888/wp-admin` with `admin` / `password` (local only).
wp-env mounts and activates the plugin. Keep `npm start` running while editing
`src/`. Use ignored `.wp-env.override.json` for local overrides. Stop with
`npm run env:stop`; `clean` and `destroy` remove local data.

| Command | Purpose |
| --- | --- |
| `npm run build` | Build both blocks and the Clerk frontend runtime |
| `npm test` | Run focused Clerk lifecycle tests with Jest/jsdom |
| `npm run lint:js` / `npm run lint:css` | Apply WordPress lint rules |
| `npm run format` | Format source using WordPress tooling |
| `npm run env -- run cli wp plugin list` | Check the mounted plugin name and activation |
| `npm run plugin:zip` | Build a distributable ZIP containing runtime assets, readable source, and license notices |

After checking the mounted plugin directory, run the WordPress integration
assertions (the normal repository mount is `clerk-wp`):

```sh
npm run env -- run cli wp eval-file wp-content/plugins/clerk-wp/tests/php/integration.php
```

The assertions restore the saved key and current user. For live acceptance, use a
real Clerk test application to check sign-up, sign-in/out, profile editing,
organization switching, OAuth return navigation, and plain/pretty permalinks.
Mocked lifecycle tests cannot prove those external service flows.

Source lives in `src/`, shared PHP ownership in `includes/`, and generated output
in ignored `build/`. Preserve block names when publishing content; changing names
later requires a content migration. Readable source, JavaScript tests, build configuration, and license notices are included
in release ZIPs. Installed dependencies are omitted; Node.js is unnecessary on
the installed WordPress site. To rebuild a release ZIP without a repository
lockfile, run `npm install`, then `npm run build`. Repository checkouts use
`npm ci` with the committed lockfile. The plugin is GPL-2.0-or-later; bundled
third-party notices are in `THIRD-PARTY-NOTICES.txt`.

## Plugin branding

The settings-page wordmark is in `assets/logo.png`. Block editor headings use
the desk icon in `assets/component-logo.png`. WordPress.org listing icons
are in `directory-assets/icon-128x128.png` and `directory-assets/icon-256x256.png`.
Upload these icons to the directory SVN repository’s top-level `assets/` folder;
listing artwork is separate from the installable plugin ZIP.

The donation button uses original local artwork in `assets/donation-button.svg`,
licensed under GPL-2.0-or-later. It links to the donation page without loading
remote scripts, fonts, or images.
