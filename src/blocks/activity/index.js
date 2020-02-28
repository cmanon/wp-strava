/* global wp, wpStrava */
const { registerBlockType } = wp.blocks;
import edit from './edit';

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
    attributes: {
        url: {
          type: 'string',
          default: '',
        },
    },
    edit,
    save: () => <img className="wp-strava-img" src={placeholderActivityImg} />,
} );
