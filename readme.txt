=== Desk Clerk ===
Contributors: devjkane
Tags: authentication, clerk, login, blocks, shortcode
Requires at least: 6.3
Tested up to: 7.1
Requires PHP: 8.2
Stable tag: 0.2.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Drop-in Clerk visitor components through WordPress blocks and shortcodes.

== Description ==

Configure a Clerk publishable key under Settings > Desk Clerk, then place Sign In,
Sign Up, User Button, User Profile, Organization Switcher, Organization Profile,
Organization List, Create Organization, Pricing Table, Waitlist, or Google One Tap
using individual Clerk blocks in the inserter or [clerk component="sign-in"]
shortcodes. Existing generic Clerk Component blocks remain supported and can be
converted to their matching dedicated block.
Choose a site-wide Clerk theme under Settings > Desk Clerk: Default, Simple, Dark,
Shades of Purple, Neobrutalism, or shadcn. The shadcn preset requires compatible
CSS variables and utility styles supplied by your WordPress theme.
Appearance variables add color pickers and typography/sizing controls. Blank fields
inherit theme defaults; Reset appearance overrides clears only custom variables.
Appearance options configure social buttons, logo placement, card elevation,
animations, automatic focus, avatar shimmer, optional fields, and help/terms/privacy
and logo URLs. Choose Inherit default or leave URLs blank to use Clerk defaults.
URL overrides require full HTTP/HTTPS addresses without embedded credentials.
Reset appearance options preserves variables, theme, and publishable key.
Advanced > Provider parameters configures sign-out, authentication, waitlist,
and post-checkout URLs using site-relative paths or full HTTP/HTTPS URLs.
Forced authentication redirects take precedence over fallbacks. Blank fields
keep the existing defaults; reset clears only provider parameters.
Settings are grouped into General, Appearance, Layout and links, Advanced, and
Experimental. Enable experimental localization to follow WordPress's current
site/page language in components, dialogs, and live previews. Translations load
only when enabled; unsupported languages or download failures use English.
Clerk's hosted Account Portal and browser-controlled prompts are not localized
by this setting.
Organization components require Organizations enabled in your Clerk application.
Pricing Table requires Clerk Billing and published plans. Select user or organization
plans in the editor; organization plans require a signed-in visitor and active
organization. Waitlist requires Waitlist mode enabled in Clerk.
Google One Tap requires Google and Google One Tap enabled in Clerk. It requests
one browser-controlled prompt for signed-out visitors, shared across placements;
the browser may suppress it. Use Open in new tab if the embedded prompt is blocked.

Use Clerk Show to nest blocks shown to signed-in or signed-out Clerk visitors.
Content starts hidden until Clerk loads and follows session changes. This controls
visibility only: all nested content remains in public HTML. Do not use it to protect
private content or authorize access. WordPress login stays separate.

Readable JavaScript/SCSS source and build tools are included in the release ZIP.
See README.md for rebuilding and THIRD-PARTY-NOTICES.txt for bundled licenses.

The plugin contacts your Clerk application's Frontend API to load its browser
SDK/UI and provide authentication. A Clerk account/application is required.
Service terms: https://clerk.com/legal/terms
Privacy policy: https://clerk.com/legal/privacy

When configured components render, visitors’ browsers contact Clerk to load UI
and handle sessions, authentication, account/organization changes, and enabled
billing or waitlist flows. Clerk receives network/request information and the
data visitors submit in those controls, and manages its own session storage.
No Clerk secret keys are stored and this plugin does not create WordPress users.
Clerk requests also occur in administrator-authorized live editor previews.
The donation links are ordinary links; no donation-service scripts are loaded.

Clerk visitor login remains separate from WordPress accounts and wp-admin.
Only publishable keys are used; never enter a secret key. The editor displays
interactive live previews using the saved settings and your Clerk visitor session.
Previews start closed; Open Preview/Close Preview toggles the inline component.
Refresh preview reloads changed settings; Open in new tab provides a full browser
view for authentication providers that disallow embedded pages. Preview interactions
affect your Clerk account. Site theme styles and custom fonts may differ.
Live preview height follows the control and expands for dialogs. Disabled Clerk
Billing displays setup guidance rather than a generic loading error.
Standalone panels use
hash routing; place one authentication/profile panel per page. Multiple User
Buttons and Organization Switchers are supported.

== Installation ==

1. Upload the complete release ZIP and activate Desk Clerk.
2. Save a publishable key (pk_test_ or pk_live_) under Settings > Desk Clerk.
3. Configure your production domain in the Clerk Dashboard before going live.
4. Search for Clerk in the inserter, add the individual control block or supported
   shortcode, and view the published page.

== Changelog ==

= 0.2.0 =
Add administrator settings, eleven Clerk components, blocks and shortcodes,
a shared frontend loader, accessible error recovery, and focused tests.
Add grouped appearance settings, Pricing Table, Organization List, Create
Organization, Waitlist, and a nested Clerk Show block.
Add authenticated live component previews in the block editor.
Add Google One Tap prompt support with shared lifecycle and cleanup.
Add opt-in experimental localization using official Clerk translations.
Preserve the original Template Message block.

= 0.1.0 =
Initial template.
