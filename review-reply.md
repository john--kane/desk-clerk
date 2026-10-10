# Draft reply to the WordPress Plugin Review Team

This draft reflects your clarification that you built the independent plugin,
while Clerk provides the authentication service. It does not claim ownership of
Clerk or its trademarks. No email has been sent.

---

Thank you for the review. This is an independent integration authored and owned
by John Kane (`devjkane`). Clerk provides the authentication service; this is not
an official Clerk plugin. I do not represent Clerk or claim ownership of its
trademark.

I have renamed the plugin to **Visitor Components for Clerk**. Please change the
slug reservation for this existing submission from `desk-clerk` to
`visitor-components-for-clerk`.

The plugin header, readme, package name, translation domain and artwork now use
the new branding. The readme and settings page explicitly state that the plugin
is not affiliated with, endorsed by, or maintained by Clerk.

The literal preview `<style>` tag in `includes/editor-preview.php` has been
replaced with `assets/editor-preview.css`, loaded through `wp_enqueue_style()`
and printed through WordPress's stylesheet API in the isolated preview document.

The shared renderer now also enqueues the Clerk UI and browser SDK through
WordPress asynchronously, with SDK public-key and crossorigin attributes. The
runtime waits for both service scripts and retains 20-second timeout/error
feedback. Missing service scripts offer a page reload; SDK and
component failures retain in-page retry.

The readme's **External services** section describes Clerk, Google One Tap,
conditional Stripe payment processing, site-configured identity providers and
custom-logo image requests, including purpose, data and timing. It includes
service terms/privacy links for the named services.

The Google Workspace and Microsoft Entra strings cited in `build/4770.js` are
from the upstream `@clerk/localizations` English translation module. The Google
URLs are scope names in instructional text; the Microsoft/XML URLs identify SAML
claims. They are not directory API calls implemented by this plugin. The plugin
does not configure directory synchronization or collect Google Workspace
service-account keys. The separate Google One Tap integration is disclosed.

The updated package is version **0.2.2**. I will upload it through the existing
submission page; I am not creating a duplicate submission or changing accounts.

I checked the exact name in WordPress.org's directory: no matches were returned
for "Visitor Components for Clerk". I also reviewed results for "Visitor
Components" and my public contributor profile. These checks did not identify a
conflicting name or misleading Clerk affiliation. Broader web-search results
were unavailable because the search engine presented a bot challenge. I
understand that similarity/distinctiveness and trademark acceptance remain for
the Plugin Review Team to assess; I am retaining this requested display name.

The readme links Apple's feature-specific Sign in with Apple privacy notice.
It distinguishes Microsoft personal-account policies from the commercial
terms/DPA applicable to site-configured Microsoft Entra tenants. The Microsoft Product Terms portal loaded successfully. The Microsoft consumer
Services Agreement, Privacy Statement and DPA pages returned HTTP 403 to
automated verification; their full content has not been verified. Clerk, Google,
Stripe, Apple and GitHub policy pages loaded successfully with relevant document
titles. Each site owner chooses their own providers and image hosts and must
review the policies applicable to that configuration.

The plugin has no fixed Clerk application: each site owner supplies their own
publishable key and configures identity providers/billing in Clerk. I have not
performed real account/provider/payment flows. The service disclosures are based
on source tracing and official service documentation, not a live traffic capture.

Validation of the exact updated `visitor-components-for-clerk.zip` (version
0.2.2): production build and all 63 JavaScript tests passed. PHP
settings/rendering assertions and 20 HTTP checks passed on isolated WordPress
7.1.3/PHP 8.2.33. All 29 static Plugin Check checks reported 0 errors and
0 warnings against this exact extracted package. All 127 packaged files match
the checkout. Saved block and shortcode contracts remain compatible. The
JavaScript/CSS linters passed during the final rename; subsequent changes affect
only the readme and development documentation. Full runtime Plugin Check and
minimum WordPress 6.3 acceptance were not exercised.

Checked package SHA-256:
`d8064bb3a61be353583182f988e508c4cbd514655557abf62bed9cf8a77558b3`
