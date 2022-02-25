/* global wp, wpStrava */
import { registerBlockType, createBlock } from '@wordpress/blocks';
import edit from './edit';
import metadata from './block.json';

metadata.edit = edit;
metadata.save = () => null;

metadata.transforms = {
	from: [
		{
			type: 'raw',
			isMatch: ( node ) => {
				return (
					node.nodeName === 'P' &&
					node.innerText.startsWith( 'https://www.strava.com/activities/' )
				);
			},
			transform: function( node ) {
				return createBlock( metadata.name, {
					url: node.innerText,
				} );
			}
		},
		{
			type: 'block',
			blocks: [ 'core/paragraph' ],
			isMatch: ( node ) => {
				return node.content.startsWith( 'https://www.strava.com/activities/' );
			},
			transform: function( node ) {
				return createBlock( metadata.name, { url: node.content } );
			}
		},
		{
			type: 'shortcode',
			tag: [ 'activity', 'ride' ],
			attributes: {
				url: {
					type: 'string',
					shortcode: ( { named: atts } ) => {
						return 'https://www.strava.com/activities/' + atts.id;
					},
				},
			},
		}
	],
	to: [
		{
			type: 'block',
			blocks: [ 'core/paragraph' ],
			transform: ( attributes ) => {
				return createBlock( 'core/paragraph', { content: attributes.url } );
			}
		},
	],
};

registerBlockType( metadata.name, metadata );
