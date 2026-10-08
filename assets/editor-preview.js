// Measure inside the preview: parent document access can be restricted by the editor.
if ( window.parent !== window ) {
	let lastHeight;
	const measure = ( force = false ) => {
		const dialogOpen = Array.from(
			document.querySelectorAll( '[role="dialog"]' )
		).some(
			( dialog ) =>
				dialog.getAttribute( 'aria-hidden' ) !== 'true' &&
				dialog.getClientRects().length
		);
		const height = Math.ceil(
			Math.max(
				80,
				document.body.getBoundingClientRect().height,
				dialogOpen ? 620 : 0
			)
		);
		if ( force || height !== lastHeight ) {
			lastHeight = height;
			window.parent.postMessage(
				{ type: 'clerk-preview-size', height },
				window.location.origin
			);
		}
	};
	const resizeObserver = new window.ResizeObserver( () => measure() );
	const mutationObserver = new window.MutationObserver( () => measure() );
	resizeObserver.observe( document.body );
	mutationObserver.observe( document.body, {
		childList: true,
		subtree: true,
		attributes: true,
	} );
	window.addEventListener( 'message', ( event ) => {
		if (
			event.source === window.parent &&
			event.origin === window.location.origin &&
			event.data?.type === 'clerk-preview-measure'
		) {
			measure( true );
		}
	} );
	window.addEventListener( 'pagehide', () => {
		resizeObserver.disconnect();
		mutationObserver.disconnect();
	} );
	measure();
}
