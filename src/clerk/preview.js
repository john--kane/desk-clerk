/* @jsxRuntime classic */
/* @jsx createElement */
/* @jsxFrag Fragment */
import {
	createElement,
	Fragment,
	useEffect,
	useId,
	useRef,
	useState,
} from '@wordpress/element';
import { Button, Notice } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

function PreviewFrame( { src } ) {
	const [ height, setHeight ] = useState( 80 );
	const frame = useRef();
	const origin = new URL( src ).origin;
	useEffect( () => {
		// WordPress may render the block into its own canvas iframe.
		const owner = frame.current.ownerDocument.defaultView;
		const receiveSize = ( event ) => {
			const size = event.data?.height;
			if (
				event.source === frame.current.contentWindow &&
				event.origin === origin &&
				event.data?.type === 'clerk-preview-size' &&
				Number.isFinite( size ) &&
				size > 0 &&
				size <= 10000
			) {
				setHeight( Math.ceil( Math.max( 80, size ) ) );
			}
		};
		owner.addEventListener( 'message', receiveSize );
		return () => owner.removeEventListener( 'message', receiveSize );
	}, [ origin ] );

	function fitContents() {
		frame.current.contentWindow?.postMessage(
			{ type: 'clerk-preview-measure' },
			origin
		);
	}

	return (
		<iframe
			ref={ frame }
			src={ src }
			title={ __( 'Clerk live component preview', 'desk-clerk' ) }
			height={ height }
			onLoad={ fitContents }
			allow="identity-credentials-get"
			loading="lazy"
			sandbox="allow-scripts allow-same-origin allow-forms allow-popups allow-popups-to-escape-sandbox"
			referrerPolicy="same-origin"
		/>
	);
}

export default function Preview( { component, payer } ) {
	const [ revision, setRevision ] = useState( 0 );
	const [ open, setOpen ] = useState( false );
	const previewId = useId();
	const previewUrl = window.pluginTemplateClerkEditor?.previewUrl;
	if ( ! previewUrl ) {
		return (
			<Notice status="warning" isDismissible={ false }>
				{ __(
					'Reload the editor to enable Clerk live previews.',
					'desk-clerk'
				) }
			</Notice>
		);
	}
	const url = new URL( previewUrl );
	url.searchParams.set( 'component', component );
	url.searchParams.set( 'for', payer || 'user' );
	return (
		<div className="clerk-editor-preview">
			<div className="clerk-editor-preview-toolbar">
				<strong>{ __( 'Live preview', 'desk-clerk' ) }</strong>
				<Button
					variant="secondary"
					aria-expanded={ open }
					aria-controls={ previewId }
					onClick={ () => setOpen( ( visible ) => ! visible ) }
				>
					{ open
						? __( 'Close Preview', 'desk-clerk' )
						: __( 'Open Preview', 'desk-clerk' ) }
				</Button>
				{ open && (
					<>
						<Button
							variant="secondary"
							onClick={ () => setRevision( revision + 1 ) }
						>
							{ __( 'Refresh preview', 'desk-clerk' ) }
						</Button>
						<Button
							variant="link"
							href={ url.href }
							target="_blank"
							rel="noopener noreferrer"
						>
							{ __( 'Open in new tab', 'desk-clerk' ) }
						</Button>
					</>
				) }
			</div>
			<div id={ previewId } hidden={ ! open }>
				{ open && (
					<>
						<p className="clerk-editor-preview-help">
							{ __(
								'Uses your Clerk visitor session. Interactions affect your Clerk account. Refresh after changing Clerk settings. Site theme styles may differ.',
								'desk-clerk'
							) }
						</p>
						<PreviewFrame
							key={ `${ url.href }:${ revision }` }
							src={ url.href }
						/>
					</>
				) }
			</div>
		</div>
	);
}
