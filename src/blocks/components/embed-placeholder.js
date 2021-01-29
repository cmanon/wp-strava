/**
 * WordPress dependencies
 */

import { BlockIcon } from '@wordpress/editor';

const { __, _x } = wp.i18n;
const { Button, Placeholder } = wp.components;

const EmbedPlaceholder = ( props ) => {
	const {
		icon,
		label,
		value,
		onSubmit,
		onChange,
	} = props;

	return (
		<Placeholder
			icon={ <BlockIcon icon={ icon } showColors /> }
			label={ label }
			className="wp-block-embed"
			instructions={ __(
				'Paste a link to the Strava Activity you want to display on your site.'
			) }
		>
			<form onSubmit={ onSubmit }>
				<input
					type="url"
					value={ value || '' }
					className="components-placeholder__input"
					aria-label={ label }
					placeholder={ __( 'Enter Activity URL to embed here…' ) }
					onChange={ onChange }
				/>
				<Button isPrimary type="submit">
					{ _x( 'Embed', 'button label' ) }
				</Button>
			</form>
		</Placeholder>
	);
};

export default EmbedPlaceholder;
