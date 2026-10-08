/* @jsxRuntime classic */
/* @jsx createElement */
import { createElement, createRoot, flushSync } from '@wordpress/element';
import Preview from './preview';

jest.mock( '@wordpress/components', () => ( {
	Button: ( { children, variant, ...props } ) =>
		props.href ? (
			<a { ...props }>{ children }</a>
		) : (
			<button { ...props }>{ children }</button>
		),
	Notice: ( { children } ) => <div>{ children }</div>,
} ) );

test( 'live preview starts closed and toggles without opening a new tab', () => {
	window.pluginTemplateClerkEditor = {
		previewUrl: 'https://example.test/?plugin_template_clerk_preview=nonce',
	};
	const container = document.createElement( 'div' );
	document.body.appendChild( container );
	const root = createRoot( container );
	try {
		flushSync( () => root.render( <Preview component="pricing-table" /> ) );
		expect( container.querySelector( 'iframe' ) ).toBeNull();
		const toggle = Array.from(
			container.querySelectorAll( 'button' )
		).find( ( button ) => button.textContent === 'Open Preview' );
		expect( toggle ).toBeDefined();
		expect( toggle.getAttribute( 'aria-expanded' ) ).toBe( 'false' );
		flushSync( () => toggle.click() );
		expect( toggle.getAttribute( 'aria-expanded' ) ).toBe( 'true' );
		expect( toggle.textContent ).toBe( 'Close Preview' );
		expect( container.querySelector( 'iframe' ).src ).toContain(
			'component=pricing-table'
		);
		expect( container.querySelector( 'a' ).target ).toBe( '_blank' );
		flushSync( () => toggle.click() );
		expect( container.querySelector( 'iframe' ) ).toBeNull();
		expect( toggle.getAttribute( 'aria-expanded' ) ).toBe( 'false' );
		flushSync( () => toggle.click() );
		expect( container.querySelector( 'iframe' ) ).not.toBeNull();
	} finally {
		flushSync( () => root.unmount() );
		container.remove();
		delete window.pluginTemplateClerkEditor;
	}
} );
