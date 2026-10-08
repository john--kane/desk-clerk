/* @jsxRuntime classic */
/* @jsx createElement */
import { createElement } from '@wordpress/element';
import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';
import { SelectControl, Notice, Placeholder } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import EditorTitle from '../editor-title';
import '../editor.scss';

registerBlockType( metadata.name, {
	edit: function Edit( { attributes, setAttributes } ) {
		return (
			<div
				{ ...useBlockProps( {
					className: 'clerk-component-editor',
				} ) }
			>
				<Placeholder
					label={
						<EditorTitle>
							{ __( 'Clerk Show', 'desk-clerk' ) }
						</EditorTitle>
					}
				>
					<SelectControl
						label={ __( 'Show content when', 'desk-clerk' ) }
						value={ attributes.condition }
						onChange={ ( condition ) =>
							setAttributes( { condition } )
						}
						options={ [
							{
								value: 'signed-in',
								label: __(
									'Visitor is signed in to Clerk',
									'desk-clerk'
								),
							},
							{
								value: 'signed-out',
								label: __(
									'Visitor is signed out of Clerk',
									'desk-clerk'
								),
							},
						] }
					/>
					<Notice status="info" isDismissible={ false }>
						{ __(
							'Visibility only. Content remains in public page HTML. Do not place private information here. WordPress login is separate.',
							'desk-clerk'
						) }
					</Notice>
				</Placeholder>
				<div className="clerk-show-editor-content">
					<InnerBlocks />
				</div>
			</div>
		);
	},
	save: () => <InnerBlocks.Content />,
} );
