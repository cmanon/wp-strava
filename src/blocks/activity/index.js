/* global wp, wpStrava */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Localized Data.
 */
const {
    placeholderActivityImg,
} = wpStrava;


registerBlockType( 'wp-strava/activity', {
    title: 'Strava Activity',
    icon: 'chart-line',
    category: 'embed',
    edit: () => <img className="wp-strava-img" src={placeholderActivityImg} />,
    save: () => <img className="wp-strava-img" src={placeholderActivityImg} />,
} );
