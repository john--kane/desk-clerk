/* @jsxRuntime classic */
/* @jsx createElement */
import { createElement } from '@wordpress/element';
import { registerBlockType } from '@wordpress/blocks';
import { RichText, useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import EditorTitle from './editor-title';
import './style.scss';

registerBlockType( metadata.name, {
	edit: function Edit( { attributes, setAttributes } ) {
		return (
			<div { ...useBlockProps() }>
				<strong>
					<EditorTitle>
						{ __( 'Template Message', 'desk-clerk' ) }
					</EditorTitle>
				</strong>
				<RichText
					tagName="p"
					value={ attributes.content }
					onChange={ ( content ) => setAttributes( { content } ) }
					placeholder={ __( 'Write a message…', 'desk-clerk' ) }
				/>
			</div>
		);
	},
	save( { attributes } ) {
		return (
			<RichText.Content
				{ ...useBlockProps.save() }
				tagName="p"
				value={ attributes.content }
			/>
		);
	},
} );
