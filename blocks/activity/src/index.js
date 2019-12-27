import { registerBlockType } from '@wordpress/blocks';

registerBlockType( 'wp-strava/activity', {
    title: 'Strava Activity',
    icon: 'smiley',
    category: 'widgets',
    edit: () => <div>Hola, mundo!</div>,
    save: () => <div>Hola, mundo!</div>,
} );
