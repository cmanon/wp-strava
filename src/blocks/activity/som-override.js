/**
 * External dependencies
 */
import mapValues from 'lodash';

/**
 * WordPress dependencies
 */
import {
	Button,
	ButtonGroup,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const SOM_PRESETS = {
	'english': __( 'English', 'wp-strava' ),
	'metric':  __( 'Metric', 'wp-strava' ),
};

export default function SOMOverride( {
	onChange,
} ) {

	function updateSOM( newSOM ) {
		return () => {
			onChange( { som: newSOM } );
		};
	}

	return (
		<>
			<div className="block-editor-som-control">
				<ButtonGroup aria-label={ __( 'System of Measure (override from Settings)' ) }>
					{ mapValues( SOM_PRESETS, ( key, label ) => {
						return (
							<Button
								key={ key }
								isSmall
								isPrimary={ true }
								isPressed={ true }
								onClick={ updateSOM(
									key
								) }
							>
								{ label }
							</Button>
						);
					} ) }
				</ButtonGroup>
				<Button isSmall onClick={ updateSOM() }>
					{ __( 'Reset' ) }
				</Button>
			</div>
		</>
	);
}
