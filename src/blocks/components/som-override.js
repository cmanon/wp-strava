/**
 * WordPress dependencies
 */
import {
	Button,
	ButtonGroup,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

class SOMOverride extends Component {

	constructor() {
		super( ...arguments );
		this.onChange   = this.onChange.bind( this );
	}

	onChange( event ) {
		console.log(event.target);
		this.props.onChange( event );
	}

	render() {
		return (
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
							value='english'
							onClick={ this.onChange }
						>
							{ __( 'English', 'wp-strava' ) }
						</Button>
						<Button
							key={ 'metric' }
							isSmall
							isPrimary={ som == 'metric' }
							isPressed={ som == 'metric' }
							value='metric'
							onClick={ this.onChange }
						>
							{ __( 'Metric', 'wp-strava' ) }
						</Button>
					</ButtonGroup>
					<Button
						isSmall
						value=''
						onClick={ this.onChange }
					>
						{ __( 'Reset', 'wp-strava' ) }
					</Button>
				</div>
			</div>
		);
	}
}
