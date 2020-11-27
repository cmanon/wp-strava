/**
 * WordPress dependencies
 */
import {
	Button,
	ButtonGroup,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function SOMOverride( {
	som,
	onChange,
} ) {

	function updateSOM( som ) {
		return () => {
			onChange( som );
		};
	}

	return (
		<>
			<div className="wp-block-wp-strava-som-control">
				<p className="wp-block-wp-strava-som-control-row">
						{ __( 'System of Measure (override from settings)' ) }
				</p>
				<div className="wp-block-wp-strava-som-control-row">
					<ButtonGroup aria-label={ __( 'System of Measure', 'wp-strava' ) }>
						<Button
							key={ 'english' }
							isSmall
							isPrimary={ som == 'english' }
							isPressed={ som == 'english' }
							onClick={ updateSOM( 'english' ) }
						>
							{ __( 'English', 'wp-strava' ) }
						</Button>
						<Button
							key={ 'metric' }
							isSmall
							isPrimary={ som == 'metric' }
							isPressed={ som == 'metric' }
							onClick={ updateSOM( 'metric' ) }
						>
							{ __( 'Metric', 'wp-strava' ) }
						</Button>
					</ButtonGroup>
					<Button isSmall onClick={ updateSOM() }>
						{ __( 'Reset', 'wp-strava' ) }
					</Button>
				</div>
			</div>
		</>
	);
}
