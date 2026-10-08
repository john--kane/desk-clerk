<?php
/** Run inside WordPress with wp eval-file. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
function clerk_check( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}
clerk_check( function_exists( 'plugin_template_clerk_parse_key' ), 'Clerk settings are not implemented.' );
$key = 'pk_test_' . base64_encode( 'example.clerk.accounts.dev$' );
$previous = get_option( 'plugin_template_clerk_key', '' );
$previous_user = get_current_user_id();
$previous_theme = get_option( 'plugin_template_clerk_theme', false );
$previous_variables = get_option( 'plugin_template_clerk_variables', false );
$previous_options = get_option( 'plugin_template_clerk_options', false );
$previous_localization = get_option( 'plugin_template_clerk_localization', false );
$previous_provider = get_option( 'plugin_template_clerk_provider', false );
try {
	$admin = get_users( array( 'role' => 'administrator', 'number' => 1 ) )[0];
	wp_set_current_user( $admin->ID );
	clerk_check( function_exists( 'plugin_template_clerk_preview_attributes' ), 'Editor live preview validation must exist.' );
	clerk_check( plugin_template_clerk_preview_attributes( array( 'component' => 'pricing-table', 'for' => 'organization' ) ) === array( 'component' => 'pricing-table', 'for' => 'organization' ), 'Preview accepts supported component and pricing selection.' );
	foreach ( array( array( 'component' => '<script>' ), array( 'component' => array() ), array( 'component' => 'pricing-table', 'for' => 'bad' ), array( 'for' => array() ) ) as $invalid_preview ) {
		clerk_check( is_wp_error( plugin_template_clerk_preview_attributes( $invalid_preview ) ), 'Preview rejects malformed or unsupported attributes.' );
	}
	$preview_nonce = wp_create_nonce( 'plugin_template_clerk_preview' );
	clerk_check( plugin_template_clerk_can_preview( $preview_nonce ), 'Content editors with a valid nonce can preview.' );
	clerk_check( ! plugin_template_clerk_can_preview( 'bad' ) && ! plugin_template_clerk_can_preview( array() ), 'Preview requires a valid scalar nonce.' );
	wp_set_current_user( 0 );
	clerk_check( ! plugin_template_clerk_can_preview( $preview_nonce ), 'Unauthenticated visitors cannot access editor previews.' );
	wp_set_current_user( $admin->ID );
	clerk_check( plugin_template_clerk_parse_key( $key ) === 'example.clerk.accounts.dev', 'Valid key must resolve host.' );
	foreach ( array( 'sk_test_secret', 'pk_test_bad', 'pk_test_' . base64_encode( 'evil.test/path$' ), 'pk_test_' . base64_encode( '127.0.0.1$' ), 'pk_test_' . base64_encode( 'evil.test:443$' ), 'pk_test_' . base64_encode( 'evil.test' ) ) as $invalid ) {
		clerk_check( false === plugin_template_clerk_parse_key( $invalid ), 'Reject malformed keys and hosts.' );
	}
	clerk_check( function_exists( 'plugin_template_clerk_sanitize_theme' ), 'Clerk theme setting must exist.' );
	foreach ( array( 'default', 'simple', 'dark', 'shadesOfPurple', 'neobrutalism', 'shadcn' ) as $theme ) {
		clerk_check( plugin_template_clerk_sanitize_theme( $theme ) === $theme, 'Accept supported theme ' . $theme );
	}
	update_option( 'plugin_template_clerk_theme', 'dark' );
	foreach ( array( 'unknown', '<script>', array(), null ) as $invalid ) {
		clerk_check( plugin_template_clerk_sanitize_theme( $invalid ) === 'dark', 'Reject invalid themes while preserving selection.' );
	}
	wp_set_current_user( 0 );
	clerk_check( plugin_template_clerk_sanitize_theme( 'simple' ) === 'dark', 'Unauthorized users cannot change the theme.' );
	wp_set_current_user( $admin->ID );
	clerk_check( function_exists( 'plugin_template_clerk_sanitize_variables' ), 'Appearance variable settings must exist.' );
	$variables = array( 'colorPrimary' => '#123abc', 'colorForeground' => '#abc', 'fontFamily' => "'Helvetica Neue', Arial, sans-serif", 'fontSize' => '1rem', 'borderRadius' => '0', 'spacing' => '1.25em' );
	clerk_check( plugin_template_clerk_sanitize_variables( $variables ) === $variables, 'Accept supported appearance variables.' );
	update_option( 'plugin_template_clerk_variables', $variables );
	$result = plugin_template_clerk_sanitize_variables( array( 'colorPrimary' => 'url(https://evil.test)', 'fontSize' => '-1px', 'fontFamily' => 'Arial; background:red', 'unknown' => 'bad', 'spacing' => '' ) );
	$expected = array( 'colorPrimary' => '#123abc', 'fontSize' => '1rem', 'fontFamily' => "'Helvetica Neue', Arial, sans-serif" );
	ksort( $result );
	ksort( $expected );
	clerk_check( $result === $expected, 'Invalid fields preserve their previous values; blank or omitted fields clear and unknown keys are excluded.' );
	clerk_check( plugin_template_clerk_sanitize_variables( 'invalid' ) === $variables, 'Reject malformed submissions without clearing overrides.' );
	wp_set_current_user( 0 );
	clerk_check( plugin_template_clerk_sanitize_variables( array() ) === $variables, 'Only admins can change appearance overrides.' );
	wp_set_current_user( $admin->ID );
	$_POST['plugin_template_clerk_reset_variables'] = '1';
	clerk_check( plugin_template_clerk_sanitize_variables( $variables ) === array(), 'Reset clears all variable overrides.' );
	unset( $_POST['plugin_template_clerk_reset_variables'] );
	update_option( 'plugin_template_clerk_key', $key );
	$provider = array( 'afterSignOutUrl' => '/signed-out/', 'signInUrl' => 'https://example.test/sign-in/', 'signInForceRedirectUrl' => '/account/?from=clerk', 'signUpFallbackRedirectUrl' => '/welcome/' );
	clerk_check( plugin_template_clerk_sanitize_provider( $provider ) === $provider, 'Provider parameters accept full URLs and site-relative paths.' );
	update_option( 'plugin_template_clerk_provider', $provider );
	clerk_check( plugin_template_clerk_config()['provider'] === $provider, 'Shared component and preview configuration exports provider parameters.' );
	foreach ( array( '//evil.test', '/\\evil.test', "https://example.test/\npath", 'javascript:alert(1)', 'data:text/html,bad', 'https://user:pass@example.test/', 'relative-path', array(), null ) as $invalid_provider_url ) {
		$invalid_provider = plugin_template_clerk_sanitize_provider( array( 'afterSignOutUrl' => $invalid_provider_url ) );
		clerk_check( $invalid_provider['afterSignOutUrl'] === '/signed-out/', 'Invalid provider URLs preserve the previous field.' );
	}
	clerk_check( plugin_template_clerk_sanitize_provider( array( 'afterSignOutUrl' => '', 'unknown' => '/ignored/' ) ) === array(), 'Blank provider URLs clear overrides and unknown keys are ignored.' );
	clerk_check( plugin_template_clerk_sanitize_provider( 'bad' ) === $provider, 'Malformed provider submissions preserve all overrides.' );
	wp_set_current_user( 0 );
	clerk_check( plugin_template_clerk_sanitize_provider( array() ) === $provider, 'Only administrators can change provider parameters.' );
	wp_set_current_user( $admin->ID );
	$_POST['plugin_template_clerk_reset_options'] = '1';
	clerk_check( plugin_template_clerk_sanitize_provider( $provider ) === $provider, 'Resetting appearance options preserves provider parameters.' );
	unset( $_POST['plugin_template_clerk_reset_options'] );
	$_POST['plugin_template_clerk_reset_provider'] = '1';
	clerk_check( plugin_template_clerk_sanitize_provider( $provider ) === array(), 'Provider reset clears only provider overrides.' );
	unset( $_POST['plugin_template_clerk_reset_provider'] );
	update_option( 'plugin_template_clerk_provider', array( 'afterSignOutUrl' => 'javascript:alert(1)', 'signInUrl' => '/sign-in/', 'ui' => array() ) );
	clerk_check( plugin_template_clerk_provider() === array( 'signInUrl' => '/sign-in/' ), 'Corrupt provider values and unsupported SDK parameters are excluded from public configuration.' );
	update_option( 'plugin_template_clerk_provider', $provider );
	update_option( 'plugin_template_clerk_localization', false );
	clerk_check( plugin_template_clerk_config()['locale'] === null, 'Disabled localization keeps Clerk defaults.' );
	clerk_check( plugin_template_clerk_sanitize_localization( '1' ) === true && plugin_template_clerk_sanitize_localization( '0' ) === false, 'Checkbox values enable and disable localization.' );
	update_option( 'plugin_template_clerk_localization', true );
	foreach ( array( 'yes', 'false', array(), null ) as $invalid_localization ) {
		clerk_check( plugin_template_clerk_sanitize_localization( $invalid_localization ) === true, 'Malformed localization submissions preserve the setting.' );
	}
	wp_set_current_user( 0 );
	clerk_check( plugin_template_clerk_sanitize_localization( '0' ) === true, 'Unauthorized users cannot disable localization.' );
	wp_set_current_user( $admin->ID );
	$page_locale = static function () { return 'fr_CA'; };
	add_filter( 'locale', $page_locale );
	try {
		clerk_check( plugin_template_clerk_config()['locale'] === 'fr_CA', 'Shared runtime configuration follows the current WordPress page locale.' );
	} finally {
		remove_filter( 'locale', $page_locale );
	}
	clerk_check( plugin_template_clerk_config()['variables'] === $variables, 'Export variables to the shared frontend config.' );
	clerk_check( function_exists( 'plugin_template_clerk_sanitize_options' ), 'Appearance option settings must exist.' );
	foreach ( array( '/terms', 'https://', 'ftp://example.test/logo', 'https://example.test/a b', array(), null ) as $invalid_url ) {
		clerk_check( null === plugin_template_clerk_validate_option( 'termsPageUrl', $invalid_url ), 'Reject malformed or non-HTTP URLs.' );
	}
	$input = array( 'socialButtonsPlacement' => 'bottom', 'socialButtonsVariant' => 'iconButton', 'logoPlacement' => 'outside', 'elevation' => 'flush', 'animations' => '0', 'autoFocus' => '0', 'shimmer' => '1', 'showOptionalFields' => '1', 'termsPageUrl' => 'https://example.test/terms', 'privacyPageUrl' => 'https://example.test/privacy', 'helpPageUrl' => 'http://localhost:8888/help', 'logoImageUrl' => 'https://example.test/logo.png', 'logoLinkUrl' => 'https://example.test/' );
	$options = $input;
	$options['animations'] = false;
	$options['autoFocus'] = false;
	$options['shimmer'] = true;
	$options['showOptionalFields'] = true;
	$actual = plugin_template_clerk_sanitize_options( $input );
	ksort( $actual );
	ksort( $options );
	clerk_check( $actual === $options, 'Accept valid options and convert boolean fields to real booleans.' );
	update_option( 'plugin_template_clerk_options', $options );
	$invalid = plugin_template_clerk_sanitize_options( array( 'animations' => 'maybe', 'socialButtonsPlacement' => 'left', 'termsPageUrl' => 'javascript:alert(1)', 'privacyPageUrl' => 'data:text/html,bad', 'logoImageUrl' => 'https://user:password@example.test/logo', 'autoFocus' => '', 'unknown' => 'bad' ) );
	clerk_check( $invalid['animations'] === false && $invalid['socialButtonsPlacement'] === 'bottom' && $invalid['termsPageUrl'] === 'https://example.test/terms' && $invalid['privacyPageUrl'] === 'https://example.test/privacy' && $invalid['logoImageUrl'] === 'https://example.test/logo.png', 'Invalid fields preserve their prior values, including false.' );
	clerk_check( ! isset( $invalid['unknown'] ) && ! array_key_exists( 'autoFocus', $invalid ), 'Unknown and inherited fields are omitted.' );
	clerk_check( plugin_template_clerk_sanitize_options( 'bad' ) == $options, 'Malformed submissions preserve options.' );
	wp_set_current_user( 0 );
	clerk_check( plugin_template_clerk_sanitize_options( array() ) == $options, 'Only admins can change options.' );
	wp_set_current_user( $admin->ID );
	$_POST['plugin_template_clerk_reset_options'] = '1';
	clerk_check( plugin_template_clerk_sanitize_options( $input ) === array(), 'Reset options clears only option overrides.' );
	unset( $_POST['plugin_template_clerk_reset_options'] );
	clerk_check( plugin_template_clerk_config()['options'] == $options, 'Export options without losing false values.' );
	clerk_check( plugin_template_clerk_config()['theme'] === 'dark', 'Export the saved theme to the shared frontend runtime.' );
	update_option( 'plugin_template_clerk_theme', 'invalid-stored-theme' );
	clerk_check( plugin_template_clerk_config()['theme'] === 'default', 'Unexpected stored values fall back to the default theme.' );

	clerk_check( plugin_template_clerk_sanitize_key( 'invalid' ) === $key, 'Invalid submission must preserve the saved key.' );
	clerk_check( plugin_template_clerk_sanitize_key( '' ) === '', 'Allow deliberate clearing.' );
	wp_set_current_user( 0 );
	clerk_check( plugin_template_clerk_sanitize_key( 'pk_test_' . base64_encode( 'other.clerk.accounts.dev$' ) ) === $key, 'Unauthorized submissions cannot change configuration.' );
	wp_set_current_user( $admin->ID );
	clerk_check( WP_Block_Type_Registry::get_instance()->is_registered( 'plugin-template/message' ), 'Retain the original block.' );
	clerk_check( WP_Block_Type_Registry::get_instance()->is_registered( 'clerk/component' ), 'Register the Clerk block.' );
	foreach ( array( 'sign-in', 'sign-up', 'user-button', 'user-profile', 'organization-switcher', 'organization-profile', 'pricing-table', 'organization-list', 'create-organization', 'waitlist', 'google-one-tap' ) as $component ) {
		$type = WP_Block_Type_Registry::get_instance()->get_registered( 'clerk/' . $component );
		clerk_check( null !== $type && $type->supports['html'] === false, 'Register a dedicated dynamic block for ' . $component );
		$dedicated = render_block( array( 'blockName' => 'clerk/' . $component, 'attrs' => array( 'component' => 'sign-up' ), 'innerBlocks' => array(), 'innerHTML' => '', 'innerContent' => array() ) );
		clerk_check( str_contains( $dedicated, 'data-clerk-component="' . $component . '"' ), 'Dedicated block fixes its control to ' . $component . ' even with conflicting saved attributes.' );
		$html = do_shortcode( '[clerk component="' . $component . '"]' );
		clerk_check( str_contains( $html, 'data-clerk-component="' . $component . '"' ), 'Shortcode renders ' . $component );
		$block = render_block( array( 'blockName' => 'clerk/component', 'attrs' => array( 'component' => $component ), 'innerBlocks' => array(), 'innerHTML' => '', 'innerContent' => array() ) );
		clerk_check( str_contains( $block, 'data-clerk-component="' . $component . '"' ), 'Block renders ' . $component );
	}
	clerk_check( WP_Block_Type_Registry::get_instance()->get_registered( 'clerk/component' )->supports['inserter'] === false, 'Legacy generic blocks remain registered but are hidden from the inserter.' );
	$pricing = render_block( array( 'blockName' => 'clerk/pricing-table', 'attrs' => array( 'for' => 'organization' ), 'innerBlocks' => array(), 'innerHTML' => '', 'innerContent' => array() ) );
	clerk_check( str_contains( $pricing, 'data-clerk-for="organization"' ), 'Dedicated Pricing Table retains the plan type.' );
	clerk_check( WP_Block_Type_Registry::get_instance()->is_registered( 'clerk/show' ), 'Register the nested Show block.' );
	clerk_check( str_contains( do_shortcode( '[clerk component="pricing-table" for="organization"]' ), 'data-clerk-for="organization"' ), 'Pricing shortcode forwards subscriber type.' );
	clerk_check( plugin_template_clerk_render( array( 'component' => 'pricing-table', 'for' => 'bad' ) ) === '', 'Reject unsupported pricing subscriber types.' );
	$show_content = '<p>Member greeting</p>';
	$show = plugin_template_clerk_render_show( array( 'condition' => 'signed-in' ), $show_content );
	clerk_check( str_contains( $show, 'data-clerk-show="signed-in" hidden' ) && str_contains( $show, $show_content ), 'Show retains nested content and starts hidden for cache-safe rendering.' );
	clerk_check( plugin_template_clerk_render_show( array( 'condition' => 'bad' ), $show_content ) === '', 'Reject unsupported Show conditions.' );
	$show_block = render_block( array( 'blockName' => 'clerk/show', 'attrs' => array( 'condition' => 'signed-out' ), 'innerBlocks' => array(), 'innerHTML' => $show_content, 'innerContent' => array( $show_content ) ) );
	clerk_check( str_contains( $show_block, 'data-clerk-show="signed-out" hidden' ) && str_contains( $show_block, $show_content ), 'Registered Show block renders saved nested content.' );
	clerk_check( do_shortcode( '[clerk component="bad"]' ) === '', 'Unknown component must not render.' );
	clerk_check( plugin_template_clerk_render( array( 'component' => '"><script>alert(1)</script>' ) ) === '', 'Reject injected attributes.' );
	clerk_check( plugin_template_clerk_render( array( 'component' => array() ) ) === '', 'Reject non-string component attributes.' );
	clerk_check( wp_script_is( 'clerk-component-view-script', 'enqueued' ), 'Shortcodes enqueue the frontend runtime.' );
	$a = plugin_template_clerk_render( array( 'component' => 'user-button' ) );
	$b = plugin_template_clerk_render( array( 'component' => 'user-button' ) );
	clerk_check( $a !== $b, 'Repeated components have unique container IDs.' );
	delete_option( 'plugin_template_clerk_key' );
	$html = do_shortcode( '[clerk]' );
	clerk_check( str_contains( $html, 'not configured' ), 'Missing key renders accessible guidance.' );
	clerk_check( ! str_contains( $html, 'pk_test_' ), 'No key in missing-configuration markup.' );
	$unconfigured_show = plugin_template_clerk_render_show( array( 'condition' => 'signed-out' ), $show_content );
	clerk_check( str_contains( $unconfigured_show, 'data-clerk-show="signed-out" hidden' ) && str_contains( $unconfigured_show, 'not configured' ), 'Unconfigured Show stays hidden and supplies configuration guidance.' );
	WP_CLI::success( 'Clerk settings and rendering integration assertions passed.' );
} finally {
	if ( false === $previous_provider ) {
		delete_option( 'plugin_template_clerk_provider' );
	} else {
		update_option( 'plugin_template_clerk_provider', $previous_provider );
	}
	if ( false === $previous_localization ) {
		delete_option( 'plugin_template_clerk_localization' );
	} else {
		update_option( 'plugin_template_clerk_localization', $previous_localization );
	}
	update_option( 'plugin_template_clerk_key', $previous );
	if ( false === $previous_theme ) {
		delete_option( 'plugin_template_clerk_theme' );
	} else {
		update_option( 'plugin_template_clerk_theme', $previous_theme );
	}
	if ( false === $previous_variables ) {
		delete_option( 'plugin_template_clerk_variables' );
	} else {
		update_option( 'plugin_template_clerk_variables', $previous_variables );
	}
	if ( false === $previous_options ) {
		delete_option( 'plugin_template_clerk_options' );
	} else {
		update_option( 'plugin_template_clerk_options', $previous_options );
	}
	wp_set_current_user( $previous_user );
}
