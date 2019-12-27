import { registerBlockType } from '@wordpress/blocks';

registerBlockType( 'wp-strava/activity', {
    title: 'Strava Activity',
    icon: 'chart-line',
    category: 'widgets',
    edit: () => <div>Hola, mundo!</div>,
    save: () => <div>Hola, mundo!</div>,
} );
