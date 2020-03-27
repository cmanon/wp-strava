/**
 * WordPress dependencies
 */
const { __, _x } = wp.i18n;
const { Button, Placeholder, ExternalLink } = wp.components;
const { BlockIcon } = wp.blockEditor;

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
				'Paste a link to the content you want to display on your site.'
			) }
		>
			<form onSubmit={ onSubmit }>
				<input
					type="url"
					value={ value || '' }
					className="components-placeholder__input"
					aria-label={ label }
					placeholder={ __( 'Enter URL to embed here…' ) }
					onChange={ onChange }
				/>
				<Button isPrimary type="submit">
					{ _x( 'Embed', 'button label' ) }
				</Button>
			</form>
			<div className="components-placeholder__learn-more">
				<ExternalLink
					href={ __(
						'https://wordpress.org/support/article/embeds/' // @TODO update for wp-strava
					) }
				>
					{ __( 'Learn more about embeds' ) }
				</ExternalLink>
			</div>
		</Placeholder>
	);
};

export default EmbedPlaceholder;
