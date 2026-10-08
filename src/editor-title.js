/* @jsxRuntime classic */
/* @jsx createElement */
import { createElement } from '@wordpress/element';
import logoUrl from '../assets/component-logo.png';

export default function EditorTitle( { children } ) {
	return (
		<span style={ { display: 'flex', alignItems: 'center', gap: '12px' } }>
			<img
				src={ logoUrl }
				alt=""
				width="1096"
				height="752"
				style={ {
					display: 'block',
					flexShrink: 0,
					width: '32px',
					height: 'auto',
				} }
			/>
			{ children }
		</span>
	);
}
