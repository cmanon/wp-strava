/* global wp, wpStrava */
import { registerBlockType, createBlock } from '@wordpress/blocks';
import edit from './edit';
import metadata from './block.json';

metadata.edit = edit;
metadata.save = () => null;

metadata.transforms = {
	from: [
		{
			type: "raw",
			priority: 10,
			isMatch: ( node ) =>
				node.nodeName === "P" &&
				node.innerText.startsWith( "https://www.strava.com/segments/" ),

			transform: function( node ) {
				return createBlock( metadata.name, {
					url: node.innerText,
				} );
			}
		}
	]
};

registerBlockType( metadata.name, metadata );
