import { createRuntime } from './view';

const config = {
	publishableKey: 'pk_test_example',
	uiUrl: 'https://example.clerk.accounts.dev/ui.js',
	sdkUrl: 'https://example.clerk.accounts.dev/sdk.js',
	messages: {
		loading: 'Loading Clerk…',
		error: 'Unable to load Clerk.',
		retry: 'Retry',
		signIn: 'Sign in',
		signedIn: 'You are signed in.',
		organization: 'Select an organization.',
		oneTap: 'Google One Tap prompt depends on browser support.',
		billing: 'Pricing plans are unavailable. Contact the administrator.',
		billingSetup:
			'Enable Clerk Billing and publish plans, then refresh the preview.',
	},
};

function container( component ) {
	const node = document.createElement( 'div' );
	node.dataset.clerkComponent = component;
	document.body.appendChild( node );
	return node;
}

function clerkMock() {
	return {
		load: jest.fn().mockResolvedValue(),
		addListener: jest.fn().mockReturnValue( jest.fn() ),
		isSignedIn: false,
		session: null,
		organization: null,
		mountSignIn: jest.fn(),
		unmountSignIn: jest.fn(),
		mountSignUp: jest.fn(),
		unmountSignUp: jest.fn(),
		mountUserButton: jest.fn(),
		unmountUserButton: jest.fn(),
		mountUserProfile: jest.fn(),
		unmountUserProfile: jest.fn(),
		mountOrganizationSwitcher: jest.fn(),
		unmountOrganizationSwitcher: jest.fn(),
		mountOrganizationProfile: jest.fn(),
		unmountOrganizationProfile: jest.fn(),
		mountOrganizationList: jest.fn(),
		unmountOrganizationList: jest.fn(),
		mountCreateOrganization: jest.fn(),
		unmountCreateOrganization: jest.fn(),
		mountPricingTable: jest.fn(),
		unmountPricingTable: jest.fn(),
		mountWaitlist: jest.fn(),
		unmountWaitlist: jest.fn(),
		openSignIn: jest.fn(),
		openGoogleOneTap: jest.fn(),
		closeGoogleOneTap: jest.fn(),
	};
}

let runtime;
let clerk;
let append;
beforeEach( () => {
	document.body.innerHTML = '';
	document.head.innerHTML = '';
	clerk = clerkMock();
	delete window.Clerk;
	delete window.__internal_ClerkUICtor;
	append = jest
		.spyOn( document.head, 'appendChild' )
		.mockImplementation( ( script ) => {
			if ( script.src === config.uiUrl ) {
				window.__internal_ClerkUICtor = {};
			} else {
				window.Clerk = clerk;
			}
			queueMicrotask( () => script.dispatchEvent( new Event( 'load' ) ) );
			return script;
		} );
} );
afterEach( () => {
	runtime?.stop();
	append.mockRestore();
} );

test( 'multiple containers share one SDK initialization', async () => {
	const a = container( 'sign-in' );
	const b = container( 'sign-up' );
	runtime = createRuntime( config );
	await runtime.start();
	expect( append ).toHaveBeenCalledTimes( 2 );
	expect( clerk.load ).toHaveBeenCalledTimes( 1 );
	expect( clerk.mountSignIn ).toHaveBeenCalledWith(
		a,
		expect.objectContaining( { routing: 'hash', withSignUp: true } )
	);
	expect( clerk.mountSignUp ).toHaveBeenCalledWith(
		b,
		expect.objectContaining( { routing: 'hash' } )
	);
	await runtime.start();
	expect( clerk.mountSignIn ).toHaveBeenCalledTimes( 1 );
} );

test( 'provider redirects apply to SDK, inline authentication, and sign-in dialogs', async () => {
	container( 'sign-in' );
	container( 'sign-up' );
	const button = container( 'user-button' );
	const provider = {
		afterSignOutUrl: '/signed-out/',
		signInUrl: '/sign-in/',
		signUpUrl: '/sign-up/',
		signInForceRedirectUrl: '/account/',
		signUpForceRedirectUrl: '/welcome/',
		signInFallbackRedirectUrl: '/fallback-in/',
		signUpFallbackRedirectUrl: '/fallback-up/',
	};
	runtime = createRuntime( { ...config, provider } );
	await runtime.start();
	expect( clerk.load ).toHaveBeenCalledWith(
		expect.objectContaining( provider )
	);
	expect( clerk.mountSignIn.mock.calls[ 0 ][ 1 ] ).toEqual(
		expect.objectContaining( {
			forceRedirectUrl: '/account/',
			signUpForceRedirectUrl: '/welcome/',
		} )
	);
	expect( clerk.mountSignUp.mock.calls[ 0 ][ 1 ] ).toEqual(
		expect.objectContaining( {
			forceRedirectUrl: '/welcome/',
			signInForceRedirectUrl: '/account/',
		} )
	);
	button.querySelector( 'button' ).click();
	expect( clerk.openSignIn ).toHaveBeenCalledWith(
		expect.objectContaining( { forceRedirectUrl: '/account/' } )
	);
} );

test( 'provider fallback redirects are not shadowed by return-to-page force redirects', async () => {
	container( 'sign-in' );
	container( 'sign-up' );
	runtime = createRuntime( {
		...config,
		provider: {
			signInFallbackRedirectUrl: '/account/',
			signUpFallbackRedirectUrl: '/welcome/',
		},
	} );
	await runtime.start();
	const signIn = clerk.mountSignIn.mock.calls[ 0 ][ 1 ];
	const signUp = clerk.mountSignUp.mock.calls[ 0 ][ 1 ];
	expect( signIn ).not.toHaveProperty( 'forceRedirectUrl' );
	expect( signIn ).not.toHaveProperty( 'signUpForceRedirectUrl' );
	expect( signIn ).toEqual(
		expect.objectContaining( {
			fallbackRedirectUrl: '/account/',
			signUpFallbackRedirectUrl: '/welcome/',
		} )
	);
	expect( signUp ).not.toHaveProperty( 'forceRedirectUrl' );
	expect( signUp ).toEqual(
		expect.objectContaining( { fallbackRedirectUrl: '/welcome/' } )
	);
} );

test( 'provider checkout redirect replaces the Pricing Table return-to-page default', async () => {
	container( 'pricing-table' );
	runtime = createRuntime( {
		...config,
		provider: { newSubscriptionRedirectUrl: '/subscribed/' },
	} );
	await runtime.start();
	expect( clerk.mountPricingTable.mock.calls[ 0 ][ 1 ] ).toEqual(
		expect.objectContaining( {
			newSubscriptionRedirectUrl: '/subscribed/',
		} )
	);
} );

test.each( [
	[ 'fr', 'fr-FR' ],
	[ 'fr_CA', 'fr-FR' ],
	[ 'pt_BR', 'pt-BR' ],
	[ 'pt_PT_ao90', 'pt-PT' ],
	[ 'en_GB', 'en-GB' ],
	[ 'zh_HK', 'zh-TW' ],
	[ 'zh_Hant', 'zh-TW' ],
	[ 'de_DE_formal', 'de-DE' ],
	[ 'bel', 'be-BY' ],
] )( 'localizes Clerk for WordPress locale %s', async ( locale, expected ) => {
	container( 'sign-in' );
	runtime = createRuntime( { ...config, locale } );
	await runtime.start();
	expect( clerk.load ).toHaveBeenCalledWith(
		expect.objectContaining( {
			localization: expect.objectContaining( { locale: expected } ),
		} )
	);
	expect( clerk.mountSignIn ).toHaveBeenCalled();
} );

test.each( [ undefined, '', 'xx_YY', '__proto__', '../fr-FR', {} ] )(
	'keeps default Clerk English for disabled or unsupported locale %s',
	async ( locale ) => {
		container( 'sign-in' );
		runtime = createRuntime( { ...config, locale } );
		await runtime.start();
		expect( clerk.load.mock.calls[ 0 ][ 0 ] ).not.toHaveProperty(
			'localization'
		);
		expect( clerk.mountSignIn ).toHaveBeenCalled();
	}
);

test( 'session changes replace signed-out guidance and unmount signed-in UI', async () => {
	const node = container( 'user-button' );
	runtime = createRuntime( config );
	await runtime.start();
	expect( node.textContent ).toContain( 'Sign in' );
	node.querySelector( 'button' ).click();
	expect( clerk.openSignIn ).toHaveBeenCalled();
	clerk.isSignedIn = true;
	clerk.session = { id: 'session_1' };
	clerk.addListener.mock.calls[ 0 ][ 0 ]();
	expect( clerk.mountUserButton ).toHaveBeenCalledWith(
		node,
		expect.any( Object )
	);
	clerk.isSignedIn = false;
	clerk.session = null;
	clerk.addListener.mock.calls[ 0 ][ 0 ]();
	expect( clerk.unmountUserButton ).toHaveBeenCalledWith( node );
	expect( node.textContent ).toContain( 'Sign in' );
} );

test( 'organization profile waits for an active organization', async () => {
	clerk.isSignedIn = true;
	clerk.session = { id: 'session_1' };
	const node = container( 'organization-profile' );
	runtime = createRuntime( config );
	await runtime.start();
	expect( clerk.mountOrganizationProfile ).not.toHaveBeenCalled();
	expect( node.textContent ).toContain( 'Select an organization.' );
	clerk.organization = { id: 'org_1' };
	clerk.addListener.mock.calls[ 0 ][ 0 ]();
	expect( clerk.mountOrganizationProfile ).toHaveBeenCalledWith( node, {
		routing: 'hash',
	} );
} );

test( 'failed scripts show an accessible retry and recover on demand', async () => {
	append.mockImplementationOnce( ( script ) => {
		queueMicrotask( () => script.dispatchEvent( new Event( 'error' ) ) );
		return script;
	} );
	const node = container( 'sign-in' );
	runtime = createRuntime( config );
	await runtime.start();
	expect( node.querySelector( '[role="alert"]' ).textContent ).toContain(
		'Unable to load Clerk.'
	);
	await runtime.start();
	expect( node.querySelector( 'button' ).textContent ).toBe( 'Retry' );
	node.querySelector( 'button' ).click();
	await new Promise( ( resolve ) => setTimeout( resolve, 0 ) );
	expect( clerk.mountSignIn ).toHaveBeenCalledWith(
		node,
		expect.any( Object )
	);
} );

test( 'unsupported component data cannot call arbitrary SDK methods', async () => {
	container( 'load' );
	runtime = createRuntime( config );
	await runtime.start();
	expect( append ).not.toHaveBeenCalled();
} );

test( 'removed nodes are unmounted and dynamically added nodes mount once', async () => {
	const node = container( 'sign-in' );
	runtime = createRuntime( config );
	await runtime.start();
	node.remove();
	const added = container( 'sign-up' );
	await new Promise( ( resolve ) => setTimeout( resolve, 0 ) );
	expect( clerk.unmountSignIn ).toHaveBeenCalledWith( node );
	expect( clerk.mountSignUp ).toHaveBeenCalledWith(
		added,
		expect.any( Object )
	);
	expect( clerk.load ).toHaveBeenCalledTimes( 1 );
	runtime.stop();
	expect( clerk.unmountSignUp ).toHaveBeenCalledWith( added );
	expect( clerk.addListener.mock.results[ 0 ].value ).toHaveBeenCalled();
} );

test( 'a throwing sign-in modal produces a retry instead of swallowing the error', async () => {
	const node = container( 'user-button' );
	clerk.openSignIn.mockImplementation( () => {
		throw new Error( 'UI unavailable' );
	} );
	runtime = createRuntime( config );
	await runtime.start();
	node.querySelector( 'button' ).click();
	expect( node.querySelector( '[role="alert"]' )?.textContent ).toContain(
		'Unable to load Clerk.'
	);
	expect( node.querySelector( 'button' ).textContent ).toBe( 'Retry' );
} );

test( 'a throwing mount cleans up and recovers through explicit retry', async () => {
	const node = container( 'sign-in' );
	clerk.mountSignIn.mockImplementationOnce( () => {
		throw new Error( 'Mount failed' );
	} );
	runtime = createRuntime( config );
	await runtime.start();
	expect( clerk.unmountSignIn ).toHaveBeenCalledWith( node );
	expect( node.querySelector( '[role="alert"]' ).textContent ).toContain(
		'Unable to load Clerk.'
	);
	node.querySelector( 'button' ).click();
	await new Promise( ( resolve ) => setTimeout( resolve, 0 ) );
	expect( clerk.mountSignIn ).toHaveBeenCalledTimes( 2 );
} );

test.each( [
	[ 'simple', 'simple' ],
	[
		'dark',
		expect.objectContaining( {
			name: 'dark',
			__type: 'prebuilt_appearance',
		} ),
	],
	[
		'shadesOfPurple',
		expect.objectContaining( {
			name: 'shadesOfPurple',
			__type: 'prebuilt_appearance',
		} ),
	],
	[
		'neobrutalism',
		expect.objectContaining( {
			name: 'neobrutalism',
			__type: 'prebuilt_appearance',
		} ),
	],
	[
		'shadcn',
		expect.objectContaining( {
			name: 'shadcn',
			__type: 'prebuilt_appearance',
		} ),
	],
] )(
	'applies the %s theme globally when initializing Clerk',
	async ( theme, expected ) => {
		container( 'sign-in' );
		runtime = createRuntime( { ...config, theme } );
		await runtime.start();
		expect( clerk.load ).toHaveBeenCalledWith(
			expect.objectContaining( {
				appearance: { theme: expected },
			} )
		);
	}
);

test.each( [ undefined, 'default', 'unknown', '__proto__' ] )(
	'keeps Clerk defaults for theme %s',
	async ( theme ) => {
		container( 'sign-in' );
		runtime = createRuntime( { ...config, theme } );
		await runtime.start();
		expect( clerk.load.mock.calls[ 0 ][ 0 ] ).not.toHaveProperty(
			'appearance'
		);
	}
);

test.each( [ 'default', 'dark' ] )(
	'applies appearance variables with the %s theme',
	async ( theme ) => {
		container( 'sign-in' );
		const variables = {
			colorPrimary: '#123abc',
			colorForeground: '#333333',
			fontFamily: 'Arial, sans-serif',
			fontSize: '1rem',
			borderRadius: '0',
			spacing: '1.25rem',
		};
		runtime = createRuntime( { ...config, theme, variables } );
		await runtime.start();
		expect( clerk.load.mock.calls[ 0 ][ 0 ].appearance.variables ).toEqual(
			variables
		);
		expect( clerk.load.mock.calls[ 0 ][ 0 ].appearance.theme?.name ).toBe(
			theme === 'dark' ? 'dark' : undefined
		);
	}
);

test( 'empty appearance overrides preserve the default appearance', async () => {
	container( 'sign-in' );
	runtime = createRuntime( { ...config, variables: {} } );
	await runtime.start();
	expect( clerk.load.mock.calls[ 0 ][ 0 ] ).not.toHaveProperty(
		'appearance'
	);
} );

test( 'combines options, variables, and theme without dropping disabled options', async () => {
	container( 'sign-in' );
	const options = {
		animations: false,
		autoFocus: false,
		shimmer: false,
		showOptionalFields: true,
		socialButtonsPlacement: 'bottom',
		socialButtonsVariant: 'iconButton',
		termsPageUrl: 'https://example.test/terms',
	};
	const variables = { colorPrimary: '#123abc' };
	runtime = createRuntime( { ...config, theme: 'dark', variables, options } );
	await runtime.start();
	expect( clerk.load.mock.calls[ 0 ][ 0 ].appearance ).toEqual( {
		theme: expect.objectContaining( { name: 'dark' } ),
		variables,
		options,
	} );
} );

test( 'appearance options work with the default theme', async () => {
	container( 'sign-in' );
	const options = { elevation: 'flush', autoFocus: false };
	runtime = createRuntime( { ...config, options } );
	await runtime.start();
	expect( clerk.load.mock.calls[ 0 ][ 0 ].appearance ).toEqual( { options } );
} );

test( 'inherited appearance options do not change Clerk defaults', async () => {
	container( 'sign-in' );
	runtime = createRuntime( { ...config, options: {} } );
	await runtime.start();
	expect( clerk.load.mock.calls[ 0 ][ 0 ] ).not.toHaveProperty(
		'appearance'
	);
} );

test( 'pricing and waitlist are available to signed-out visitors', async () => {
	const pricing = container( 'pricing-table' );
	const waitlist = container( 'waitlist' );
	runtime = createRuntime( config );
	await runtime.start();
	expect( clerk.mountPricingTable ).toHaveBeenCalledWith( pricing, {
		for: 'user',
		newSubscriptionRedirectUrl: window.location.href,
	} );
	expect( clerk.mountWaitlist ).toHaveBeenCalledWith( waitlist, {
		afterJoinWaitlistUrl: window.location.href,
	} );
} );

test( 'organization list and creation require sign-in and return to the page', async () => {
	const list = container( 'organization-list' );
	const create = container( 'create-organization' );
	runtime = createRuntime( config );
	await runtime.start();
	expect( list.textContent ).toContain( 'Sign in' );
	expect( create.textContent ).toContain( 'Sign in' );
	clerk.isSignedIn = true;
	clerk.session = { id: 'session_1' };
	clerk.addListener.mock.calls[ 0 ][ 0 ]();
	expect( clerk.mountOrganizationList ).toHaveBeenCalledWith( list, {
		afterCreateOrganizationUrl: window.location.href,
		afterSelectOrganizationUrl: window.location.href,
		afterSelectPersonalUrl: window.location.href,
	} );
	expect( clerk.mountCreateOrganization ).toHaveBeenCalledWith( create, {
		routing: 'hash',
		afterCreateOrganizationUrl: window.location.href,
	} );
} );

test( 'organization pricing waits for sign-in and an active organization', async () => {
	const pricing = container( 'pricing-table' );
	pricing.dataset.clerkFor = 'organization';
	runtime = createRuntime( config );
	await runtime.start();
	expect( pricing.textContent ).toContain( 'Sign in' );
	clerk.isSignedIn = true;
	clerk.session = { id: 'session_1' };
	clerk.addListener.mock.calls[ 0 ][ 0 ]();
	expect( pricing.textContent ).toContain( 'Select an organization' );
	clerk.organization = { id: 'org_1' };
	clerk.addListener.mock.calls[ 0 ][ 0 ]();
	expect( clerk.mountPricingTable ).toHaveBeenCalledWith( pricing, {
		for: 'organization',
		newSubscriptionRedirectUrl: window.location.href,
	} );
} );

function show( condition, content = 'Visitor content' ) {
	const wrapper = document.createElement( 'div' );
	wrapper.innerHTML = `<div data-clerk-show="${ condition }" hidden>${ content }</div><div data-clerk-show-feedback></div>`;
	document.body.appendChild( wrapper );
	return wrapper.firstElementChild;
}

test( 'Show-only pages initialize Clerk and update visibility without replacing content', async () => {
	const signedIn = show( 'signed-in', '<p>Welcome back</p>' );
	const signedOut = show( 'signed-out', '<p>Join us</p>' );
	const paragraph = signedIn.firstElementChild;
	runtime = createRuntime( config );
	const pending = runtime.start();
	expect( signedIn.hidden ).toBe( true );
	expect( signedOut.hidden ).toBe( true );
	await pending;
	expect( signedIn.hidden ).toBe( true );
	expect( signedOut.hidden ).toBe( false );
	clerk.isSignedIn = true;
	clerk.session = { id: 'session_1' };
	clerk.addListener.mock.calls[ 0 ][ 0 ]();
	expect( signedIn.hidden ).toBe( false );
	expect( signedOut.hidden ).toBe( true );
	expect( signedIn.firstElementChild ).toBe( paragraph );
	expect( clerk.load ).toHaveBeenCalledTimes( 1 );
} );

test( 'hidden Show branches do not mount nested Clerk components', async () => {
	const signedIn = show( 'signed-in' );
	const node = container( 'user-button' );
	signedIn.appendChild( node );
	runtime = createRuntime( config );
	await runtime.start();
	expect( clerk.mountUserButton ).not.toHaveBeenCalled();
	clerk.isSignedIn = true;
	clerk.session = { id: 'session_1' };
	clerk.addListener.mock.calls[ 0 ][ 0 ]();
	expect( clerk.mountUserButton ).toHaveBeenCalledTimes( 1 );
	clerk.isSignedIn = false;
	clerk.session = null;
	clerk.addListener.mock.calls[ 0 ][ 0 ]();
	expect( clerk.unmountUserButton ).toHaveBeenCalledWith( node );
	expect( signedIn.hidden ).toBe( true );
} );

test( 'Show fails closed and offers retry when the SDK cannot load', async () => {
	append.mockImplementationOnce( ( script ) => {
		queueMicrotask( () => script.dispatchEvent( new Event( 'error' ) ) );
		return script;
	} );
	const signedOut = show( 'signed-out' );
	runtime = createRuntime( config );
	await runtime.start();
	expect( signedOut.hidden ).toBe( true );
	const feedback = signedOut.nextElementSibling;
	expect( feedback.querySelector( '[role="alert"]' ).textContent ).toContain(
		'Unable to load Clerk'
	);
	feedback.querySelector( 'button' ).click();
	await new Promise( ( resolve ) => setTimeout( resolve, 0 ) );
	expect( signedOut.hidden ).toBe( false );
	expect( feedback.textContent ).toBe( '' );
} );

test( 'Google One Tap placements share one prompt until the last is removed', async () => {
	const a = container( 'google-one-tap' );
	const b = container( 'google-one-tap' );
	runtime = createRuntime( config );
	await runtime.start();
	expect( clerk.openGoogleOneTap ).toHaveBeenCalledTimes( 1 );
	expect( clerk.openGoogleOneTap ).toHaveBeenCalledWith( {
		signInForceRedirectUrl: window.location.href,
		signUpForceRedirectUrl: window.location.href,
	} );
	expect( a.textContent ).toContain( 'browser support' );
	a.remove();
	await new Promise( ( resolve ) => setTimeout( resolve, 0 ) );
	expect( clerk.closeGoogleOneTap ).not.toHaveBeenCalled();
	b.remove();
	await new Promise( ( resolve ) => setTimeout( resolve, 0 ) );
	expect( clerk.closeGoogleOneTap ).toHaveBeenCalledTimes( 1 );
} );

test( 'Google One Tap closes when the visitor signs in and reopens on sign-out', async () => {
	container( 'google-one-tap' );
	container( 'google-one-tap' );
	runtime = createRuntime( config );
	await runtime.start();
	clerk.isSignedIn = true;
	clerk.session = { id: 'session_1' };
	clerk.addListener.mock.calls[ 0 ][ 0 ]();
	expect( clerk.closeGoogleOneTap ).toHaveBeenCalledTimes( 1 );
	clerk.isSignedIn = false;
	clerk.session = null;
	clerk.addListener.mock.calls[ 0 ][ 0 ]();
	expect( clerk.openGoogleOneTap ).toHaveBeenCalledTimes( 2 );
	runtime.stop();
	expect( clerk.closeGoogleOneTap ).toHaveBeenCalledTimes( 2 );
} );

test( 'Google One Tap does not open for an already signed-in visitor', async () => {
	clerk.isSignedIn = true;
	clerk.session = { id: 'session_1' };
	const node = container( 'google-one-tap' );
	runtime = createRuntime( config );
	await runtime.start();
	expect( clerk.openGoogleOneTap ).not.toHaveBeenCalled();
	expect( node.textContent ).toContain( 'You are signed in' );
} );

test( 'Google One Tap opens only while its Show branch is visible', async () => {
	const branch = show( 'signed-out' );
	branch.appendChild( container( 'google-one-tap' ) );
	clerk.isSignedIn = true;
	clerk.session = { id: 'session_1' };
	runtime = createRuntime( config );
	await runtime.start();
	expect( clerk.openGoogleOneTap ).not.toHaveBeenCalled();
	clerk.isSignedIn = false;
	clerk.session = null;
	clerk.addListener.mock.calls[ 0 ][ 0 ]();
	expect( clerk.openGoogleOneTap ).toHaveBeenCalledTimes( 1 );
} );

test( 'a failed Google One Tap prompt offers an explicit retry', async () => {
	const node = container( 'google-one-tap' );
	clerk.openGoogleOneTap.mockImplementationOnce( () => {
		throw new Error( 'One Tap unavailable' );
	} );
	runtime = createRuntime( config );
	await runtime.start();
	await new Promise( ( resolve ) => setTimeout( resolve, 0 ) );
	expect( clerk.openGoogleOneTap ).toHaveBeenCalledTimes( 1 );
	expect( node.querySelector( '[role="alert"]' ).textContent ).toContain(
		'Unable to load Clerk'
	);
	node.querySelector( 'button' ).click();
	await new Promise( ( resolve ) => setTimeout( resolve, 0 ) );
	expect( clerk.openGoogleOneTap ).toHaveBeenCalledTimes( 2 );
	expect( node.querySelector( '[role="alert"]' ) ).toBeNull();
} );

test( 'Waitlist remains available and unmounts when its placement is removed', async () => {
	const node = container( 'waitlist' );
	runtime = createRuntime( config );
	await runtime.start();
	expect( clerk.mountWaitlist ).toHaveBeenCalledWith( node, {
		afterJoinWaitlistUrl: window.location.href,
	} );
	node.remove();
	await new Promise( ( resolve ) => setTimeout( resolve, 0 ) );
	expect( clerk.unmountWaitlist ).toHaveBeenCalledWith( node );
} );

test.each( [
	[ true, 'Enable Clerk Billing and publish plans' ],
	[ false, 'Pricing plans are unavailable' ],
] )(
	'disabled Billing gives appropriate guidance for editorPreview=%s',
	async ( editorPreview, message ) => {
		const node = container( 'pricing-table' );
		clerk.mountPricingTable.mockImplementation( () => {
			throw Object.assign( new Error( 'Billing is disabled' ), {
				code: 'cannot_render_billing_disabled',
			} );
		} );
		runtime = createRuntime( { ...config, editorPreview } );
		await runtime.start();
		expect( node.querySelector( '[role="status"]' ).textContent ).toContain(
			message
		);
		expect( node.querySelector( 'button' ) ).toBeNull();
		expect( clerk.unmountPricingTable ).toHaveBeenCalledWith( node );
	}
);

test( 'other Pricing Table failures retain the accessible retry', async () => {
	const node = container( 'pricing-table' );
	clerk.mountPricingTable.mockImplementation( () => {
		throw new Error( 'Network error' );
	} );
	runtime = createRuntime( { ...config, editorPreview: true } );
	await runtime.start();
	expect( node.querySelector( '[role="alert"]' ).textContent ).toContain(
		'Unable to load Clerk'
	);
	expect( node.querySelector( 'button' ).textContent ).toBe( 'Retry' );
} );
