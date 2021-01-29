/* global wp, wpStrava */
import { registerBlockType } from '@wordpress/blocks';
import edit from './edit';

registerBlockType( 'wp-strava/route', {
	title: 'Strava Route',
	icon: 'location-alt',
	category: 'embed',
	attributes: {
		url: {
		  type: 'string',
		  default: '',
		},
		imageOnly: {
			type: 'boolean',
			default: false,
		},
		displayMarkers: {
			type: 'boolean',
			default: false,
		},
		som: {
			type: 'string',
			default: null,
		},
	},
	edit,
	save: () => null,
} );
