/* @jsxRuntime classic */
/* @jsx createElement */
import { createElement } from '@wordpress/element';
import {
	registerBlockType,
	getBlockType,
	createBlock,
} from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import { SelectControl, Placeholder } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import Preview from './preview';
import EditorTitle from '../editor-title';
import componentBlocks from './component-blocks';
import './style.scss';
import '../editor.scss';

function Edit( { attributes, setAttributes, name } ) {
	const component =
		name === metadata.name
			? attributes.component
			: name.slice( 'clerk/'.length );
	const definition =
		componentBlocks.find(
			( block ) => block.name === `clerk/${ component }`
		) || metadata;
	return (
		<div { ...useBlockProps( { className: 'clerk-component-editor' } ) }>
			<Placeholder
				label={
					<EditorTitle>
						{ getBlockType( definition.name ).title }
					</EditorTitle>
				}
			>
				{ component === 'pricing-table' && (
					<SelectControl
						label={ __( 'Plans for', 'desk-clerk' ) }
						value={ attributes.for }
						onChange={ ( payer ) =>
							setAttributes( { for: payer } )
						}
						options={ [
							{
								value: 'user',
								label: __( 'Users', 'desk-clerk' ),
							},
							{
								value: 'organization',
								label: __( 'Organizations', 'desk-clerk' ),
							},
						] }
						help={ __(
							'Requires Clerk Billing and published plans. Organization plans also require a signed-in visitor and an active organization.',
							'desk-clerk'
						) }
					/>
				) }
				{ component === 'waitlist' && (
					<p>
						{ __(
							'Enable Waitlist mode in your Clerk Dashboard before using this component.',
							'desk-clerk'
						) }
					</p>
				) }
				{ component === 'google-one-tap' && (
					<p>
						{ __(
							'Enable Google and Google One Tap in your Clerk Dashboard. This is a browser-controlled prompt for signed-out visitors, not an inline panel. Your browser may suppress it; use Open in new tab to test outside the editor frame.',
							'desk-clerk'
						) }
					</p>
				) }
				{ ( component.startsWith( 'organization-' ) ||
					component === 'create-organization' ) && (
					<p>
						{ __(
							'Requires Organizations enabled in Clerk and a signed-in visitor.',
							'desk-clerk'
						) }
					</p>
				) }
			</Placeholder>
			<Preview component={ component } payer={ attributes.for } />
		</div>
	);
}

for ( const definition of [ metadata, ...componentBlocks ] ) {
	const component = definition.name.slice( 'clerk/'.length );
	registerBlockType( definition.name, {
		edit: Edit,
		save: () => null,
		...( definition.name !== metadata.name
			? {
					transforms: {
						from: [
							{
								type: 'block',
								blocks: [ metadata.name ],
								isMatch: ( attributes ) =>
									attributes.component === component,
								transform: ( attributes ) =>
									createBlock(
										definition.name,
										component === 'pricing-table'
											? { for: attributes.for }
											: {}
									),
							},
						],
					},
			  }
			: {} ),
	} );
}
