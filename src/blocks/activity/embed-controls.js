/**
 * WordPress dependencies
 */
import { BlockControls } from '@wordpress/editor';

const { __ } = wp.i18n;
const {
	Icon,
	Button,
	Toolbar,
} = wp.components;

const EmbedControls = ( props ) => {
	const {
		switchBackToURLInput,
	} = props;

	return (
		<>
			<BlockControls>
				<Toolbar>
					<Button
						label={ __( 'Edit URL' ) }
						onClick={ switchBackToURLInput }
					>
						<Icon icon="edit" />
					</Button>
				</Toolbar>
			</BlockControls>
		</>
	);
};

export default EmbedControls;
