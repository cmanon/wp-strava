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
		instructions,
		placeholder,
		value,
		onSubmit,
		onChange,
	} = props;

	return (
		<Placeholder
			icon={ <BlockIcon icon={ icon } showColors /> }
			label={ label }
			className="wp-block-embed"
			instructions={ instructions }
		>
			<form onSubmit={ onSubmit }>
				<input
					type="url"
					value={ value || '' }
					className="components-placeholder__input"
					aria-label={ label }
					placeholder={ placeholder }
					onChange={ onChange }
				/>
				<Button isPrimary type="submit">
					{ _x( 'Embed', 'button label', 'wp-strava' ) }
				</Button>
			</form>
		</Placeholder>
	);
};

export default EmbedPlaceholder;
