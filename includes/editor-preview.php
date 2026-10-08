<?php
/** Authenticated, isolated live component documents for the block editor. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function plugin_template_clerk_can_preview( $nonce ) {
	return ( current_user_can( 'edit_posts' ) || current_user_can( 'edit_pages' ) ) && is_string( $nonce ) && (bool) wp_verify_nonce( $nonce, 'plugin_template_clerk_preview' );
}

function plugin_template_clerk_preview_attributes( $query ) {
	$component = $query['component'] ?? 'sign-in';
	$payer = $query['for'] ?? 'user';
	if ( ! is_string( $component ) || ! in_array( $component, plugin_template_clerk_components(), true ) || ! in_array( $payer, array( 'user', 'organization' ), true ) ) {
		return new WP_Error( 'invalid_preview', __( 'Select a supported Clerk component and plan type.', 'desk-clerk' ) );
	}
	return array( 'component' => $component, 'for' => $payer );
}

function plugin_template_clerk_editor_assets() {
	wp_add_inline_script( 'clerk-component-editor-script', 'window.pluginTemplateClerkEditor = ' . wp_json_encode( array(
		'previewUrl' => add_query_arg( 'plugin_template_clerk_preview', wp_create_nonce( 'plugin_template_clerk_preview' ), site_url( '/' ) ),
	), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) . ';', 'before' );
}
add_action( 'enqueue_block_editor_assets', 'plugin_template_clerk_editor_assets' );

function plugin_template_clerk_editor_preview() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only detects the route; authorization and nonce verification follow below.
	if ( ! isset( $_GET['plugin_template_clerk_preview'] ) ) {
		return;
	}
	nocache_headers();
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- The permission helper verifies this nonce before rendering.
	$nonce = is_string( $_GET['plugin_template_clerk_preview'] ) ? sanitize_text_field( wp_unslash( $_GET['plugin_template_clerk_preview'] ) ) : '';
	if ( ! plugin_template_clerk_can_preview( $nonce ) ) {
		wp_die( esc_html__( 'This Clerk preview has expired or you do not have permission. Reload the WordPress editor.', 'desk-clerk' ), '', array( 'response' => 403 ) );
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verified above; the helper strictly allowlists component and plan values.
	$attributes = plugin_template_clerk_preview_attributes( wp_unslash( $_GET ) );
	if ( is_wp_error( $attributes ) ) {
		wp_die( esc_html( $attributes->get_error_message() ), '', array( 'response' => 400 ) );
	}
	send_frame_options_header();
	header( 'Referrer-Policy: same-origin' );
	header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
	$html = plugin_template_clerk_render( $attributes );
	wp_add_inline_script( 'clerk-component-view-script', 'if (window.pluginTemplateClerk) { window.pluginTemplateClerk.editorPreview = true; }', 'before' );
	wp_enqueue_script( 'plugin-template-clerk-preview-sizing', plugins_url( 'assets/editor-preview.js', dirname( __DIR__ ) . '/plugin-template.php' ), array(), filemtime( dirname( __DIR__ ) . '/assets/editor-preview.js' ), true );
	?>
	<!doctype html>
	<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php echo esc_attr( get_option( 'blog_charset' ) ); ?>" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<title><?php esc_html_e( 'Clerk live preview', 'desk-clerk' ); ?></title>
		<?php wp_print_styles( array( 'clerk-component-style' ) ); ?>
		<style>body{margin:0;padding:16px;box-sizing:border-box;font-family:system-ui,sans-serif;background:transparent}.clerk-component{display:flex;justify-content:center;flex-wrap:wrap}</style>
	</head>
	<body>
		<?php echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- plugin_template_clerk_render() escapes all dynamic output; retain its data attributes. ?>
		<?php wp_print_scripts( array( 'clerk-component-view-script', 'plugin-template-clerk-preview-sizing' ) ); ?>
	</body>
	</html>
	<?php
	exit;
}
add_action( 'template_redirect', 'plugin_template_clerk_editor_preview', 0 );
