=== Visitor Components for Clerk ===
Contributors: devjkane
Tags: authentication, clerk, login, blocks, shortcode
Requires at least: 6.3
Tested up to: 7.1
Requires PHP: 8.2
Stable tag: 0.2.3
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Drop-in Clerk visitor components through WordPress blocks and shortcodes.

== Description ==

An independent integration by John Kane. Not affiliated with, endorsed by, or
maintained by Clerk.

Configure a Clerk publishable key under Settings > Visitor Components for Clerk, then place Sign In,
Sign Up, User Button, User Profile, Organization Switcher, Organization Profile,
Organization List, Create Organization, Pricing Table, Waitlist, or Google One Tap
using individual Clerk blocks in the inserter or [clerk component="sign-in"]
shortcodes. Existing generic Clerk Component blocks remain supported and can be
converted to their matching dedicated block.
Choose a site-wide Clerk theme under Settings > Visitor Components for Clerk: Default, Simple, Dark,
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
Nested output is filtered using WordPress's permitted post HTML, retaining the
hidden div attribute for nested Show compatibility. Ordinary blocks
and Clerk components are supported; scripts, iframe embeds, forms, and other
markup not permitted in post HTML may be removed. Place those blocks outside
Clerk Show.

Readable JavaScript/SCSS source and build tools are included in the release ZIP.
See README.md for rebuilding and THIRD-PARTY-NOTICES.txt for bundled licenses.

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

== External services ==

= Clerk =

This plugin requires a Clerk account/application. Clerk provides the visitor
authentication, sessions, account and organization controls, and optional billing
and waitlist features. Some Clerk service features may require a paid plan.

When configured components or Clerk Show blocks render, the visitor's browser
contacts the Frontend API domain of the configured Clerk application to download
the browser SDK/UI and use Clerk's services. Authorized live editor previews also
contact Clerk when opened. The plugin sends the public publishable key in its
browser configuration. Clerk requests carry network information such as IP address
and browser information, session/authentication information, and data visitors
submit in the enabled controls. Depending on the action, this can include email
addresses, profile information, organization changes, or billing/waitlist data.
Not every component collects every type of data.

Clerk manages its own session storage. This plugin stores no Clerk secret keys,
does not create WordPress users, and keeps Clerk login separate from WordPress
login. No Clerk requests are initiated by the plugin without a configured key
and a rendered component/Show block or opened live preview.

Service provider: Clerk, Inc.
Terms of service: https://clerk.com/legal/terms
Privacy policy: https://clerk.com/legal/privacy

= Clerk Billing / Stripe =

If the site owner enables Clerk Billing and a visitor opens checkout from a
Pricing Table or account/organization billing control, Clerk uses Stripe for
payment processing. The payment flow sends payment-method details entered by the
visitor, customer/billing information and transaction information to Stripe.
Browser requests associated with payment collection may also include IP address,
browser information and fraud-prevention signals. This is conditional on using
the billing/payment flow; installing the plugin alone does not submit payment
information. This plugin does not store card details or use a Stripe secret key.

Service provider: Stripe.
Terms of service: https://stripe.com/legal/ssa
Privacy policy: https://stripe.com/privacy
Clerk Billing documentation: https://clerk.com/docs/guides/billing/overview

= Custom logo images =

If the site owner configures a logo image URL, Clerk components may request that
image when displayed, including authorized live previews. For a remote URL, the
image host receives the image request and network information such as IP address
and browser information. Referrer and cookie transmission depends on browser and
host policies. Use an image hosted on your own site where possible. If choosing
an external image service, identify that service and its terms/privacy policy in
your site's privacy notice. The plugin supplies no default external logo host.
Logo destination, help, terms and privacy links navigate when clicked.

= Google One Tap and other identity providers =

The optional Google One Tap component uses Google's identity service through
Clerk. When placed on a page for a signed-out visitor, it asks Clerk to open the
Google prompt; the Clerk application must have Google One Tap enabled and the
browser may suppress the prompt. The browser contacts Google as part of this
flow, sending network/browser information and authentication request information.
If the visitor proceeds, Google supplies the account identity/authentication
credential used by Clerk to sign in or sign up that visitor. Placing the component
does not grant Google Workspace directory access.

Service provider: Google LLC.
Terms of service: https://policies.google.com/terms
Privacy policy: https://policies.google.com/privacy

Other social or enterprise providers enabled by the site owner in Clerk may be
contacted when a visitor chooses those sign-in methods. The chosen provider
receives the authentication request and returns identity information to Clerk
according to that provider's configuration and policies. Site owners should
identify their enabled providers and applicable policies in their site privacy
notice. No additional identity provider is selected or enabled by this plugin.
Examples of conditional providers and their policies are:
- Microsoft personal accounts: Microsoft Services Agreement, https://www.microsoft.com/servicesagreement ; Microsoft Privacy Statement, https://privacy.microsoft.com/privacystatement
- Apple accounts: Apple Account terms, https://www.apple.com/legal/internet-services/icloud/en/terms.html ; Sign in with Apple & Privacy, https://www.apple.com/legal/privacy/data/en/sign-in-with-apple/ ; Apple Privacy Policy, https://www.apple.com/legal/privacy/
- GitHub: GitHub Terms of Service, https://docs.github.com/en/site-policy/github-terms/github-terms-of-service ; GitHub General Privacy Statement, https://docs.github.com/en/site-policy/privacy-policies/github-general-privacy-statement

Microsoft Entra organizational sign-in uses the organization's configured tenant
and applicable Microsoft commercial agreement. The personal-account Services
Agreement above is not a substitute for those organizational terms. Site owners
should review their applicable Microsoft Product Terms at
https://www.microsoft.com/licensing/terms/ and Microsoft's Products and Services
Data Protection Addendum at
https://www.microsoft.com/licensing/docs/view/Microsoft-Products-and-Services-Data-Protection-Addendum-DPA
and explain their organization's handling of identity claims in their privacy
notice. The plugin does not select or configure a Microsoft tenant.

These providers are only used if enabled in the Clerk application and selected
by the visitor. Other configured providers, including enterprise identity
providers, have their own policies; review and disclose those before enabling
them. This plugin does not configure directory synchronization or collect
Google Workspace service-account keys.

Clerk localization assets include upstream instructional text about Google
Workspace and Microsoft Entra setup. Those strings and SAML claim identifiers
are translation data, not requests to the domains mentioned in them.

Donation and documentation links only navigate to external sites when clicked.
No donation-service scripts, fonts, or remote images are loaded by this plugin.

== Installation ==

1. Upload the complete release ZIP and activate Visitor Components for Clerk.
2. Save a publishable key (pk_test_ or pk_live_) under Settings > Visitor Components for Clerk.
3. Configure your production domain in the Clerk Dashboard before going live.
4. Search for Clerk in the inserter, add the individual control block or supported
   shortcode, and view the published page.

== Changelog ==

= 0.2.3 =
Filter Clerk Show nested output using WordPress's permitted post HTML.
Preserve ordinary block markup and nested Clerk component visibility attributes.

= 0.2.2 =
Rename to Visitor Components for Clerk and update the text domain.
Load Clerk UI and SDK asynchronously through WordPress script APIs.
Offer page reload for failed service scripts and retain SDK/component retries.
Document conditional Stripe payment processing and custom-logo requests.


= 0.2.1 =
Rename to Visitor Components for Clerk and clarify independent ownership.
Load preview-only styles through the WordPress stylesheet API.
Expand external-service disclosures for Clerk and Google One Tap.

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
