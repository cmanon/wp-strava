import { registerBlockType } from '@wordpress/blocks';
import ActivityImage from './activity.png';

registerBlockType( 'wp-strava/activity', {
    title: 'Strava Activity',
    icon: 'chart-line',
    category: 'widgets',
    edit: () => <img className="wp-strava-img" src={ActivityImage} />,
    save: () => <div>Hola, mundo!</div>,
} );
