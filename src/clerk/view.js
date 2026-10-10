/* global pluginTemplateClerk */

import { dark, shadesOfPurple, neobrutalism, shadcn } from '@clerk/ui/themes';
import { loadLocalization } from './localization';

const themes = new Map( [
	[ 'simple', 'simple' ],
	[ 'dark', dark ],
	[ 'shadesOfPurple', shadesOfPurple ],
	[ 'neobrutalism', neobrutalism ],
	[ 'shadcn', shadcn ],
] );

const components = new Map( [
	[ 'sign-in', 'SignIn' ],
	[ 'sign-up', 'SignUp' ],
	[ 'user-button', 'UserButton' ],
	[ 'user-profile', 'UserProfile' ],
	[ 'organization-switcher', 'OrganizationSwitcher' ],
	[ 'organization-profile', 'OrganizationProfile' ],
	[ 'organization-list', 'OrganizationList' ],
	[ 'create-organization', 'CreateOrganization' ],
	[ 'pricing-table', 'PricingTable' ],
	[ 'waitlist', 'Waitlist' ],
	[ 'google-one-tap', 'GoogleOneTap' ],
] );

/**
 * One runtime owns all containers and the Clerk session subscription.
 * WordPress owns script loading.
 *
 * @param {Object} config Validated public configuration from WordPress.
 */
export function createRuntime( config ) {
	const records = new Map();
	let clerk;
	let initialization;
	let unsubscribe;
	let observer;
	let stopped = false;
	const returnUrl = new URL( window.location.href );
	returnUrl.hash = '';
	const provider = config.provider || {};

	function authRedirects( component = 'sign-in' ) {
		const own = component === 'sign-up' ? 'signUp' : 'signIn';
		const other = own === 'signIn' ? 'signUp' : 'signIn';
		const force = ( flow ) =>
			provider[ `${ flow }ForceRedirectUrl` ] ||
			( provider[ `${ flow }FallbackRedirectUrl` ]
				? null
				: returnUrl.href );
		const ownForce = force( own );
		const otherForce = force( other );
		return {
			...( ownForce ? { forceRedirectUrl: ownForce } : {} ),
			fallbackRedirectUrl:
				provider[ `${ own }FallbackRedirectUrl` ] || returnUrl.href,
			...( otherForce
				? { [ `${ other }ForceRedirectUrl` ]: otherForce }
				: {} ),
			[ `${ other }FallbackRedirectUrl` ]:
				provider[ `${ other }FallbackRedirectUrl` ] || returnUrl.href,
		};
	}

	function nodes() {
		return Array.from(
			document.querySelectorAll( '[data-clerk-component]' )
		).filter( ( node ) => components.has( node.dataset.clerkComponent ) );
	}

	function shows() {
		return Array.from( document.querySelectorAll( '[data-clerk-show]' ) );
	}

	function feedbackNodes() {
		return Array.from(
			document.querySelectorAll( '[data-clerk-show-feedback]' )
		);
	}

	function hiddenByShow( node ) {
		for (
			let parent = node.parentElement;
			parent;
			parent = parent.parentElement
		) {
			if ( parent.hasAttribute( 'data-clerk-show' ) && parent.hidden ) {
				return true;
			}
		}
		return false;
	}

	function release( node ) {
		const record = records.get( node );
		records.delete( node );
		if ( record?.mounted ) {
			if ( record.name === 'GoogleOneTap' ) {
				if ( ! hasOneTap() ) {
					clerk.closeGoogleOneTap();
				}
			} else {
				clerk[ `unmount${ record.name }` ]( node );
			}
		}
	}

	function hasOneTap() {
		return Array.from( records.values() ).some(
			( record ) => record.mounted && record.name === 'GoogleOneTap'
		);
	}

	function status( node, key, message, buttonText, action, alert = false ) {
		if ( records.get( node )?.key === key ) {
			return;
		}
		release( node );
		node.replaceChildren();
		const text = document.createElement( 'p' );
		text.setAttribute( 'role', alert ? 'alert' : 'status' );
		text.textContent = message;
		node.appendChild( text );
		if ( buttonText ) {
			const button = document.createElement( 'button' );
			button.type = 'button';
			button.textContent = buttonText;
			button.addEventListener( 'click', action );
			node.appendChild( button );
		}
		records.set( node, { key } );
	}

	function failed( node, key ) {
		release( node );
		status(
			node,
			key,
			config.messages.error,
			! serviceAssetsReady()
				? config.messages.reload
				: config.messages.retry,
			() => {
				if ( ! serviceAssetsReady() ) {
					window.location.reload();
					return;
				}
				release( node );
				if ( ! clerk ) {
					initialization = null;
				}
				start();
			},
			true
		);
	}

	function serviceAssetsReady() {
		return (
			window.Clerk &&
			window.__internal_ClerkUICtor &&
			( ! window.Clerk.publishableKey ||
				window.Clerk.publishableKey === config.publishableKey )
		);
	}

	function waitForServiceScripts() {
		if ( window.Clerk && window.__internal_ClerkUICtor ) {
			return Promise.resolve();
		}
		const scripts = [ 'ui', 'sdk' ].map( ( name ) =>
			document.getElementById( `plugin-template-clerk-${ name }-js` )
		);
		if ( scripts.some( ( script ) => ! script ) ) {
			return Promise.reject(
				new Error( 'Clerk service scripts unavailable' )
			);
		}
		return new Promise( ( resolve, reject ) => {
			const timeout = setTimeout(
				() => finish( new Error( 'Clerk scripts timed out' ) ),
				20000
			);
			function finish( error ) {
				clearTimeout( timeout );
				for ( const script of scripts ) {
					script.removeEventListener( 'load', loaded );
					script.removeEventListener( 'error', failedScript );
				}
				if ( error ) {
					reject( error );
				} else {
					resolve();
				}
			}
			function loaded() {
				if ( window.Clerk && window.__internal_ClerkUICtor ) {
					finish();
				}
			}
			function failedScript() {
				finish( new Error( 'Clerk script failed' ) );
			}
			for ( const script of scripts ) {
				script.addEventListener( 'load', loaded );
				script.addEventListener( 'error', failedScript );
			}
		} );
	}

	async function initialize() {
		// WordPress owns the tags; retain timeout/error handling for async downloads.
		await waitForServiceScripts();
		const sdk = window.Clerk;
		if ( ! serviceAssetsReady() ) {
			throw new Error( 'Clerk SDK configuration mismatch' );
		}
		const theme = themes.get( config.theme );
		const appearance = {};
		if ( theme ) {
			appearance.theme = theme;
		}
		if ( config.variables && Object.keys( config.variables ).length ) {
			appearance.variables = config.variables;
		}
		if ( config.options && Object.keys( config.options ).length ) {
			appearance.options = config.options;
		}
		const localization = await loadLocalization( config.locale );
		await sdk.load( {
			signInFallbackRedirectUrl: returnUrl.href,
			signUpFallbackRedirectUrl: returnUrl.href,
			...provider,
			...( localization ? { localization } : {} ),
			...( Object.keys( appearance ).length ? { appearance } : {} ),
			ui: { ClerkUI: window.__internal_ClerkUICtor },
		} );
		if ( stopped ) {
			return;
		}
		clerk = sdk;
		unsubscribe = clerk.addListener( reconcile );
	}

	function reconcile() {
		if ( stopped || ! clerk ) {
			return;
		}
		for ( const node of shows() ) {
			const condition = node.dataset.clerkShow;
			node.hidden = ! (
				( condition === 'signed-in' && clerk.isSignedIn === true ) ||
				( condition === 'signed-out' && clerk.isSignedIn === false )
			);
		}
		for ( const node of feedbackNodes() ) {
			release( node );
			if ( node.hasChildNodes() ) {
				node.replaceChildren();
			}
		}
		for ( const node of records.keys() ) {
			if (
				! node.isConnected ||
				! components.has( node.dataset.clerkComponent ) ||
				hiddenByShow( node )
			) {
				release( node );
			}
		}
		for ( const node of nodes() ) {
			if ( hiddenByShow( node ) ) {
				continue;
			}
			const component = node.dataset.clerkComponent;
			const name = components.get( component );
			const auth = component === 'sign-in' || component === 'sign-up';
			const oneTap = component === 'google-one-tap';
			const payer =
				node.dataset.clerkFor === 'organization'
					? 'organization'
					: 'user';
			const publicComponent =
				auth ||
				oneTap ||
				component === 'waitlist' ||
				( component === 'pricing-table' && payer === 'user' );
			const key = `${ component }:${ payer }:${ clerk.isSignedIn }:${
				clerk.session?.id || ''
			}:${ clerk.organization?.id || '' }`;
			if ( records.get( node )?.key === key ) {
				continue;
			}
			if ( ( auth || oneTap ) && clerk.isSignedIn ) {
				status( node, key, config.messages.signedIn );
				continue;
			}
			if ( oneTap ) {
				status( node, key, config.messages.oneTap );
				try {
					// The SDK owns one page-level prompt, shared by visible placements.
					if ( ! hasOneTap() ) {
						clerk.openGoogleOneTap( {
							signInForceRedirectUrl: returnUrl.href,
							signUpForceRedirectUrl: returnUrl.href,
						} );
					}
					records.set( node, { key, name, mounted: true } );
				} catch {
					// A failed open may have partially created the prompt.
					if ( ! hasOneTap() ) {
						clerk.closeGoogleOneTap();
					}
					failed( node, key );
				}
				continue;
			}
			if ( ! publicComponent && ! clerk.isSignedIn ) {
				status(
					node,
					key,
					config.messages.signIn,
					config.messages.signIn,
					() => {
						try {
							clerk.openSignIn( {
								...authRedirects(),
								withSignUp: true,
							} );
						} catch {
							failed( node, key );
						}
					}
				);
				continue;
			}
			if (
				( component === 'organization-profile' ||
					( component === 'pricing-table' &&
						payer === 'organization' ) ) &&
				! clerk.organization
			) {
				status( node, key, config.messages.organization );
				continue;
			}
			release( node );
			node.replaceChildren();
			const options = {};
			if (
				auth ||
				component.endsWith( '-profile' ) ||
				component === 'create-organization'
			) {
				options.routing = 'hash';
			}
			if ( auth ) {
				Object.assign( options, authRedirects( component ) );
				if ( component === 'sign-in' ) {
					options.withSignUp = true;
				}
			}
			if (
				component === 'organization-list' ||
				component === 'create-organization'
			) {
				options.afterCreateOrganizationUrl = returnUrl.href;
			}
			if ( component === 'organization-list' ) {
				options.afterSelectOrganizationUrl = returnUrl.href;
				options.afterSelectPersonalUrl = returnUrl.href;
			}
			if ( component === 'pricing-table' ) {
				options.for = payer;
				options.newSubscriptionRedirectUrl =
					provider.newSubscriptionRedirectUrl || returnUrl.href;
			}
			if ( component === 'waitlist' ) {
				options.afterJoinWaitlistUrl = returnUrl.href;
			}
			try {
				clerk[ `mount${ name }` ]( node, options );
				records.set( node, { key, name, mounted: true } );
			} catch ( error ) {
				// A throwing mount may have created a partial instance.
				clerk[ `unmount${ name }` ]( node );
				if (
					component === 'pricing-table' &&
					error?.code === 'cannot_render_billing_disabled'
				) {
					status(
						node,
						key,
						config.editorPreview
							? config.messages.billingSetup
							: config.messages.billing
					);
				} else {
					failed( node, key );
				}
			}
		}
	}

	async function start() {
		if ( stopped || ! ( nodes().length || shows().length ) ) {
			return;
		}
		if ( ! observer ) {
			observer = new window.MutationObserver( () => {
				if ( clerk ) {
					reconcile();
				} else if ( ! initialization ) {
					start();
				}
			} );
			observer.observe( document.body, {
				childList: true,
				subtree: true,
				attributes: true,
				attributeFilter: [
					'data-clerk-component',
					'data-clerk-show',
					'data-clerk-for',
				],
			} );
		}
		if ( ! initialization ) {
			for ( const node of [ ...nodes(), ...feedbackNodes() ] ) {
				if ( hiddenByShow( node ) ) {
					continue;
				}
				status( node, 'loading', config.messages.loading );
			}
			initialization = initialize();
		}
		try {
			await initialization;
			reconcile();
		} catch {
			if ( ! stopped ) {
				for ( const node of [ ...nodes(), ...feedbackNodes() ] ) {
					if ( hiddenByShow( node ) ) {
						continue;
					}
					failed( node, 'load-error' );
				}
			}
		}
	}

	function stop() {
		stopped = true;
		observer?.disconnect();
		unsubscribe?.();
		for ( const node of records.keys() ) {
			release( node );
		}
	}

	return { start, stop };
}

if ( typeof pluginTemplateClerk !== 'undefined' ) {
	const runtime = createRuntime( pluginTemplateClerk );
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', () => runtime.start(), {
			once: true,
		} );
	} else {
		runtime.start();
	}
	window.addEventListener( 'pagehide', ( event ) => {
		if ( ! event.persisted ) {
			runtime.stop();
		}
	} );
}
