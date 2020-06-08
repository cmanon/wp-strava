/* global wp, wpStrava */
import { registerBlockType } from '@wordpress/blocks';
import edit from './edit';

registerBlockType( 'wp-strava/activity', {
	title: 'Strava Activity',
	icon: 'chart-line',
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
		}
	},
	edit,
	save: () => null,
} );
