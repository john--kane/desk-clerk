<?php
/** Supported browser SDK provider parameters and administrator controls. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function plugin_template_clerk_provider_fields_schema() {
	return array(
		'afterSignOutUrl' => array( 'label' => __( 'After sign-out URL', 'desk-clerk' ), 'help' => __( 'Destination after signing out.', 'desk-clerk' ) ),
		'afterMultiSessionSingleSignOutUrl' => array( 'label' => __( 'After single-session sign-out URL', 'desk-clerk' ), 'help' => __( 'Destination after signing out one account in a multi-session application.', 'desk-clerk' ) ),
		'signInUrl' => array( 'label' => __( 'Sign-in page URL', 'desk-clerk' ), 'help' => __( 'Page containing your Clerk Sign In component.', 'desk-clerk' ) ),
		'signUpUrl' => array( 'label' => __( 'Sign-up page URL', 'desk-clerk' ), 'help' => __( 'Page containing your Clerk Sign Up component.', 'desk-clerk' ) ),
		'signInForceRedirectUrl' => array( 'label' => __( 'Forced sign-in redirect URL', 'desk-clerk' ), 'help' => __( 'Always redirect here after sign-in; takes precedence over the fallback.', 'desk-clerk' ) ),
		'signUpForceRedirectUrl' => array( 'label' => __( 'Forced sign-up redirect URL', 'desk-clerk' ), 'help' => __( 'Always redirect here after sign-up; takes precedence over the fallback.', 'desk-clerk' ) ),
		'signInFallbackRedirectUrl' => array( 'label' => __( 'Fallback sign-in redirect URL', 'desk-clerk' ), 'help' => __( 'Redirect here after sign-in when no explicit redirect is supplied.', 'desk-clerk' ) ),
		'signUpFallbackRedirectUrl' => array( 'label' => __( 'Fallback sign-up redirect URL', 'desk-clerk' ), 'help' => __( 'Redirect here after sign-up when no explicit redirect is supplied.', 'desk-clerk' ) ),
		'waitlistUrl' => array( 'label' => __( 'Waitlist page URL', 'desk-clerk' ), 'help' => __( 'Page containing your Clerk Waitlist component.', 'desk-clerk' ) ),
		'newSubscriptionRedirectUrl' => array( 'label' => __( 'After checkout URL', 'desk-clerk' ), 'help' => __( 'Destination when a visitor clicks Continue after checkout.', 'desk-clerk' ) ),
	);
}

/** Export only supported, validated overrides, including after manual DB edits. */
function plugin_template_clerk_provider() {
	$saved = get_option( 'plugin_template_clerk_provider', array() );
	$provider = array();
	if ( ! is_array( $saved ) ) {
		return $provider;
	}
	foreach ( plugin_template_clerk_provider_fields_schema() as $name => $field ) {
		$value = plugin_template_clerk_validate_url( $saved[ $name ] ?? '', true );
		if ( null !== $value && '' !== $value ) {
			$provider[ $name ] = $value;
		}
	}
	return $provider;
}

function plugin_template_clerk_sanitize_provider( $input ) {
	$previous = plugin_template_clerk_provider();
	if ( ! current_user_can( 'manage_options' ) ) {
		return $previous;
	}
	// options.php checks the Settings API nonce before sanitization.
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Settings API options.php verifies the nonce before these callbacks.
	if ( isset( $_POST['plugin_template_clerk_reset_provider'] ) && '1' === $_POST['plugin_template_clerk_reset_provider'] ) {
		return array();
	}
	if ( ! is_array( $input ) ) {
		add_settings_error( 'plugin_template_clerk_provider', 'invalid_provider', __( 'Provider parameters could not be saved. The previous overrides were preserved.', 'desk-clerk' ) );
		return $previous;
	}
	$provider = array();
	foreach ( plugin_template_clerk_provider_fields_schema() as $name => $field ) {
		$value = plugin_template_clerk_validate_url( array_key_exists( $name, $input ) ? $input[ $name ] : '', true );
		if ( null === $value ) {
			if ( isset( $previous[ $name ] ) ) {
				$provider[ $name ] = $previous[ $name ];
			}
			/* translators: %s: provider setting label. */
			add_settings_error( 'plugin_template_clerk_provider', 'invalid_' . $name, sprintf( __( 'Invalid value for %s. Its previous override was preserved.', 'desk-clerk' ), $field['label'] ) );
		} elseif ( '' !== $value ) {
			$provider[ $name ] = $value;
		}
	}
	return $provider;
}

function plugin_template_clerk_provider_fields() {
	$provider = plugin_template_clerk_provider();
	?>
	<details class="clerk-settings-group" open><summary><strong><?php esc_html_e( 'Provider parameters', 'desk-clerk' ); ?></strong></summary>
	<p class="description"><?php esc_html_e( 'Set site-wide Clerk navigation URLs. Use a site-relative path starting with a single /, or a full HTTP/HTTPS URL without embedded credentials. Blank keeps the existing defaults. Configure permitted redirect origins in Clerk when using another domain. These settings also apply to live previews; redirects may leave the preview.', 'desk-clerk' ); ?></p>
	<table class="form-table" role="presentation">
	<?php foreach ( plugin_template_clerk_provider_fields_schema() as $name => $field ) : ?>
		<tr><th scope="row"><label for="clerk-provider-<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th><td>
		<input id="clerk-provider-<?php echo esc_attr( $name ); ?>" name="plugin_template_clerk_provider[<?php echo esc_attr( $name ); ?>]" type="text" class="regular-text" value="<?php echo esc_attr( $provider[ $name ] ?? '' ); ?>" placeholder="/account/" aria-describedby="clerk-provider-help-<?php echo esc_attr( $name ); ?>" />
		<p class="description" id="clerk-provider-help-<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $field['help'] ); ?> <code><?php echo esc_html( $name ); ?></code></p>
		</td></tr>
	<?php endforeach; ?>
	</table>
	<p><button type="submit" class="button" name="plugin_template_clerk_reset_provider" value="1"><?php esc_html_e( 'Reset provider parameters', 'desk-clerk' ); ?></button></p>
	</details>
	<?php
}
