<?php
/** Clerk publishable-key and appearance configuration. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/appearance.php';
require_once __DIR__ . '/provider.php';

/** Shared URL validation; relative paths are allowed only for navigation settings. */
function plugin_template_clerk_validate_url( $value, $allow_path = false ) {
	if ( ! is_string( $value ) || strlen( $value ) > 2048 ) {
		return null;
	}
	$value = trim( $value );
	if ( '' === $value ) {
		return '';
	}
	if ( preg_match( '/[\x00-\x20\x7f]/', $value ) || str_contains( $value, '\\' ) ) {
		return null;
	}
	if ( $allow_path && str_starts_with( $value, '/' ) && ! str_starts_with( $value, '//' ) ) {
		return $value;
	}
	$parts = wp_parse_url( $value );
	if ( ! filter_var( $value, FILTER_VALIDATE_URL ) || ! is_array( $parts ) || ! in_array( strtolower( $parts['scheme'] ?? '' ), array( 'http', 'https' ), true ) || empty( $parts['host'] ) || isset( $parts['user'] ) || isset( $parts['pass'] ) ) {
		return null;
	}
	return esc_url_raw( $value, array( 'http', 'https' ) );
}

/** Validate a publishable key and return its Frontend API hostname. */
function plugin_template_clerk_parse_key( $key ) {
	if ( ! is_string( $key ) || ! preg_match( '/^pk_(?:test|live)_([A-Za-z0-9_+\/=\-]+)$/D', $key, $matches ) ) {
		return false;
	}
	$decoded = base64_decode( strtr( $matches[1], '-_', '+/' ), true );
	if ( false === $decoded || ! str_ends_with( $decoded, '$' ) ) {
		return false;
	}
	$host = substr( $decoded, 0, -1 );
	if ( strlen( $host ) > 253 || ! preg_match( '/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z](?:[a-z0-9-]{0,61}[a-z0-9])?$/iD', $host ) ) {
		return false;
	}
	return strtolower( $host );
}

/** Keep invalid or unauthorized submissions from replacing valid configuration. */
function plugin_template_clerk_sanitize_key( $value ) {
	$previous = get_option( 'plugin_template_clerk_key', '' );
	if ( ! current_user_can( 'manage_options' ) ) {
		return $previous;
	}
	$key = is_string( $value ) ? trim( $value ) : null;
	if ( '' === $key || false !== plugin_template_clerk_parse_key( $key ) ) {
		return $key;
	}
	add_settings_error( 'plugin_template_clerk_key', 'invalid_key', __( 'Enter a valid Clerk publishable key (pk_test_ or pk_live_). The previous setting was preserved.', 'visitor-components-for-clerk' ) );
	return $previous;
}

/** Supported preset identifiers, matching Clerk's official theme exports. */
function plugin_template_clerk_themes() {
	return array(
		'default'        => __( 'Default', 'visitor-components-for-clerk' ),
		'simple'         => __( 'Simple', 'visitor-components-for-clerk' ),
		'dark'           => __( 'Dark', 'visitor-components-for-clerk' ),
		'shadesOfPurple' => __( 'Shades of Purple', 'visitor-components-for-clerk' ),
		'neobrutalism'   => __( 'Neobrutalism', 'visitor-components-for-clerk' ),
		'shadcn'         => __( 'shadcn (requires compatible site styles)', 'visitor-components-for-clerk' ),
	);
}

function plugin_template_clerk_theme() {
	$theme = get_option( 'plugin_template_clerk_theme', 'default' );
	return is_string( $theme ) && isset( plugin_template_clerk_themes()[ $theme ] ) ? $theme : 'default';
}

function plugin_template_clerk_sanitize_theme( $value ) {
	$previous = plugin_template_clerk_theme();
	if ( ! current_user_can( 'manage_options' ) ) {
		return $previous;
	}
	if ( is_string( $value ) && isset( plugin_template_clerk_themes()[ $value ] ) ) {
		return $value;
	}
	add_settings_error( 'plugin_template_clerk_theme', 'invalid_theme', __( 'Select a supported Clerk theme. The previous selection was preserved.', 'visitor-components-for-clerk' ) );
	return $previous;
}

function plugin_template_clerk_localization_enabled() {
	return in_array( get_option( 'plugin_template_clerk_localization', false ), array( true, 1, '1' ), true );
}

function plugin_template_clerk_sanitize_localization( $value ) {
	$previous = plugin_template_clerk_localization_enabled();
	if ( ! current_user_can( 'manage_options' ) ) {
		return $previous;
	}
	if ( in_array( $value, array( true, false, 1, 0, '1', '0' ), true ) ) {
		return in_array( $value, array( true, 1, '1' ), true );
	}
	add_settings_error( 'plugin_template_clerk_localization', 'invalid_localization', __( 'Choose whether to enable Clerk localization. The previous setting was preserved.', 'visitor-components-for-clerk' ) );
	return $previous;
}

/** Browser-safe, non-personalized configuration; Clerk secret keys are never used. */
function plugin_template_clerk_config() {
	$key = get_option( 'plugin_template_clerk_key', '' );
	$host = plugin_template_clerk_parse_key( $key );
	if ( false === $host ) {
		return null;
	}
	return array(
		'publishableKey' => $key,
		// get_locale() follows the site/current page language, not an admin's profile language.
		'locale'         => plugin_template_clerk_localization_enabled() ? get_locale() : null,
		'theme'          => plugin_template_clerk_theme(),
		'variables'      => plugin_template_clerk_variables(),
		'options'        => plugin_template_clerk_options(),
		'provider'       => plugin_template_clerk_provider(),
		'uiUrl'          => 'https://' . $host . '/npm/@clerk/ui@1.39.0/dist/ui.browser.js',
		'sdkUrl'         => 'https://' . $host . '/npm/@clerk/clerk-js@6.38.0/dist/clerk.browser.js',
		'messages'       => array(
			'loading'      => __( 'Loading Clerk…', 'visitor-components-for-clerk' ),
			'error'        => __( 'Unable to load this Clerk component. Please try again.', 'visitor-components-for-clerk' ),
			'reload'       => __( 'Reload page', 'visitor-components-for-clerk' ),
			'retry'        => __( 'Retry', 'visitor-components-for-clerk' ),
			'signIn'       => __( 'Sign in', 'visitor-components-for-clerk' ),
			'signedIn'     => __( 'You are signed in.', 'visitor-components-for-clerk' ),
			'organization' => __( 'Select an organization using an Organization Switcher first.', 'visitor-components-for-clerk' ),
			'oneTap'       => __( 'Google One Tap is available when enabled in Clerk and supported by your browser. Your browser may suppress the prompt.', 'visitor-components-for-clerk' ),
			'billing'      => __( 'Pricing plans are not available. Please contact the site administrator.', 'visitor-components-for-clerk' ),
			'billingSetup' => __( 'Enable Clerk Billing and publish plans in your Clerk Dashboard, then refresh this preview.', 'visitor-components-for-clerk' ),
		),
	);
}

function plugin_template_clerk_register_settings() {
	register_setting( 'plugin_template_clerk', 'plugin_template_clerk_provider', array(
		'type'              => 'array',
		'sanitize_callback' => 'plugin_template_clerk_sanitize_provider',
		'show_in_rest'      => false,
		'default'           => array(),
	) );
	register_setting( 'plugin_template_clerk', 'plugin_template_clerk_localization', array(
		'type'              => 'boolean',
		'sanitize_callback' => 'plugin_template_clerk_sanitize_localization',
		'show_in_rest'      => false,
		'default'           => false,
	) );
	register_setting( 'plugin_template_clerk', 'plugin_template_clerk_options', array(
		'type'              => 'array',
		'sanitize_callback' => 'plugin_template_clerk_sanitize_options',
		'show_in_rest'      => false,
		'default'           => array(),
	) );
	register_setting( 'plugin_template_clerk', 'plugin_template_clerk_variables', array(
		'type'              => 'array',
		'sanitize_callback' => 'plugin_template_clerk_sanitize_variables',
		'show_in_rest'      => false,
		'default'           => array(),
	) );
	register_setting( 'plugin_template_clerk', 'plugin_template_clerk_theme', array(
		'type'              => 'string',
		'sanitize_callback' => 'plugin_template_clerk_sanitize_theme',
		'show_in_rest'      => false,
		'default'           => 'default',
	) );
	register_setting( 'plugin_template_clerk', 'plugin_template_clerk_key', array(
		'type'              => 'string',
		'sanitize_callback' => 'plugin_template_clerk_sanitize_key',
		'show_in_rest'      => false,
		'default'           => '',
	) );
}
add_action( 'admin_init', 'plugin_template_clerk_register_settings' );

function plugin_template_clerk_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap clerk-settings">
		<div class="clerk-settings-header">
			<div class="clerk-settings-intro">
				<h1><img class="clerk-settings-logo" src="<?php echo esc_url( plugins_url( 'assets/logo.png', dirname( __DIR__ ) . '/plugin-template.php' ) ); ?>" alt="<?php esc_attr_e( 'Visitor Components for Clerk', 'visitor-components-for-clerk' ); ?>" width="518" height="107" /></h1>
			</div>
			<div class="clerk-settings-coffee">
				<a href="https://buymeacoffee.com/johnkane" target="_blank" rel="noopener noreferrer"><img src="<?php echo esc_url( plugins_url( 'assets/donation-button.svg', dirname( __DIR__ ) . '/plugin-template.php' ) ); ?>" alt="<?php esc_attr_e( 'Buy me a coffee', 'visitor-components-for-clerk' ); ?>" width="227" height="64" /><span class="screen-reader-text"><?php esc_html_e( '(opens in a new tab)', 'visitor-components-for-clerk' ); ?></span></a>
			</div>
		</div>
		<p><?php esc_html_e( 'Connect your Clerk application to visitor-facing blocks and shortcodes. WordPress accounts and login remain separate.', 'visitor-components-for-clerk' ); ?></p>
		<p><?php esc_html_e( 'An independent integration by John Kane. Not affiliated with, endorsed by, or maintained by Clerk.', 'visitor-components-for-clerk' ); ?></p>
		<?php settings_errors( 'plugin_template_clerk_key' ); ?>
		<?php settings_errors( 'plugin_template_clerk_theme' ); ?>
		<?php settings_errors( 'plugin_template_clerk_variables' ); ?>
		<?php settings_errors( 'plugin_template_clerk_options' ); ?>
		<?php settings_errors( 'plugin_template_clerk_provider' ); ?>
		<?php settings_errors( 'plugin_template_clerk_localization' ); ?>
		<form action="options.php" method="post">
			<?php settings_fields( 'plugin_template_clerk' ); ?>
			<section class="clerk-settings-section" aria-labelledby="clerk-general-title">
			<h2 id="clerk-general-title"><?php esc_html_e( 'General', 'visitor-components-for-clerk' ); ?></h2>
			<p><?php esc_html_e( 'Connect your Clerk application and choose the starting appearance for all visitor components.', 'visitor-components-for-clerk' ); ?></p>
			<table class="form-table clerk-settings-general" role="presentation"><tr>
				<th scope="row"><label for="clerk-key"><?php esc_html_e( 'Publishable key', 'visitor-components-for-clerk' ); ?></label></th>
				<td><input id="clerk-key" name="plugin_template_clerk_key" type="text" class="large-text code" value="<?php echo esc_attr( get_option( 'plugin_template_clerk_key', '' ) ); ?>" aria-describedby="clerk-key-help" autocomplete="off" spellcheck="false" />
				<p id="clerk-key-help" class="description"><?php esc_html_e( 'Copy pk_test_ or pk_live_ from your Clerk Dashboard. Never enter a secret key. Configure your production domain in Clerk before going live. Clear this field to disconnect.', 'visitor-components-for-clerk' ); ?></p></td>
			</tr><tr>
				<th scope="row"><label for="clerk-theme"><?php esc_html_e( 'Theme', 'visitor-components-for-clerk' ); ?></label></th>
				<td><select id="clerk-theme" name="plugin_template_clerk_theme" aria-describedby="clerk-theme-help">
					<?php foreach ( plugin_template_clerk_themes() as $theme => $label ) : ?>
						<option value="<?php echo esc_attr( $theme ); ?>" <?php selected( plugin_template_clerk_theme(), $theme ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
				<p id="clerk-theme-help" class="description"><?php esc_html_e( 'Applies to all Clerk components and dialogs. Default uses Clerk’s built-in appearance. The shadcn preset requires your WordPress theme to provide compatible shadcn CSS variables and utility styles.', 'visitor-components-for-clerk' ); ?></p></td>
			</tr></table>
			</section>
			<?php plugin_template_clerk_appearance_fields(); ?>
			<?php plugin_template_clerk_options_fields(); ?>
			<?php plugin_template_clerk_options_fields( true ); ?>
			<section class="clerk-settings-section" aria-labelledby="clerk-experimental-title">
			<h2 id="clerk-experimental-title"><?php esc_html_e( 'Experimental', 'visitor-components-for-clerk' ); ?></h2>
			<table class="form-table" role="presentation"><tr>
				<th scope="row"><?php esc_html_e( 'Localization', 'visitor-components-for-clerk' ); ?></th>
				<td>
					<input type="hidden" name="plugin_template_clerk_localization" value="0" />
					<label for="clerk-localization"><input id="clerk-localization" type="checkbox" name="plugin_template_clerk_localization" value="1" <?php checked( plugin_template_clerk_localization_enabled() ); ?> aria-describedby="clerk-localization-help" /> <?php esc_html_e( 'Use the WordPress language for Clerk components (experimental)', 'visitor-components-for-clerk' ); ?></label>
					<p id="clerk-localization-help" class="description"><?php esc_html_e( 'Uses Site Language under Settings → General, or the current page language supplied by a multilingual plugin. Applies to components, dialogs, and live editor previews. Unsupported languages fall back to English. Clerk’s hosted Account Portal remains in English. Translations are experimental and may change; save and reload to apply.', 'visitor-components-for-clerk' ); ?> <a href="https://clerk.com/docs/guides/customizing-clerk/localization" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Clerk localization documentation', 'visitor-components-for-clerk' ); ?></a></p>
				</td>
			</tr></table>
			</section>
			<?php submit_button(); ?>
		</form>
		<section class="clerk-settings-section" aria-labelledby="clerk-placement-title">
		<h2 id="clerk-placement-title"><?php esc_html_e( 'Place components', 'visitor-components-for-clerk' ); ?></h2>
		<p><?php esc_html_e( 'Search for Clerk in the block inserter and add a control such as Clerk Organization Profile, or use these shortcodes in any shortcode-enabled area:', 'visitor-components-for-clerk' ); ?></p>
		<ul><?php foreach ( plugin_template_clerk_components() as $name ) : ?>
			<li><code><?php echo esc_html( '[clerk component="' . $name . '"]' ); ?></code></li>
		<?php endforeach; ?></ul>
		<p><code>[clerk component="pricing-table" for="organization"]</code></p>
		<p><?php esc_html_e( 'Pricing Table requires Clerk Billing and published plans; Waitlist requires Waitlist mode; Google One Tap requires Google and Google One Tap enabled in Clerk and a supported browser; organization components require Organizations enabled in Clerk.', 'visitor-components-for-clerk' ); ?></p>
		<p><?php esc_html_e( 'Use the Clerk Show block to group content for signed-in or signed-out visitors. Visibility only: nested content remains in public page HTML. Preview published pages to use live components.', 'visitor-components-for-clerk' ); ?></p>
		</section>
		<section class="clerk-settings-section" aria-labelledby="clerk-support-title">
		<h2 id="clerk-support-title"><?php esc_html_e( 'Support development', 'visitor-components-for-clerk' ); ?></h2>
		<p><?php esc_html_e( 'Enjoy using Visitor Components for Clerk? Support its development with a coffee.', 'visitor-components-for-clerk' ); ?></p>
		<a class="button button-secondary" href="https://buymeacoffee.com/johnkane" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Buy me a coffee', 'visitor-components-for-clerk' ); ?> <span class="screen-reader-text"><?php esc_html_e( '(opens in a new tab)', 'visitor-components-for-clerk' ); ?></span></a>
		</section>
	</div>
	<?php
}

function plugin_template_clerk_admin_menu() {
	add_options_page( __( 'Visitor Components for Clerk', 'visitor-components-for-clerk' ), __( 'Visitor Components for Clerk', 'visitor-components-for-clerk' ), 'manage_options', 'plugin-template-clerk', 'plugin_template_clerk_settings_page' );
}
add_action( 'admin_menu', 'plugin_template_clerk_admin_menu' );
