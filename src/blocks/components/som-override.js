/**
 * WordPress dependencies
 */

const { __ } = wp.i18n;
const { Component } = wp.element;
const { Button, ButtonGroup } = wp.components;

class SOMOverride extends Component {

	constructor() {
		super( ...arguments );
		this.onChange = this.onChange.bind( this );

		this.state = {
			som: '',
		};
	}

	onChange( event ) {
		this.setState( { som: event.target.value } );
		this.props.onChange( event.target.value );
	}

	render() {
		const {
			som
		} = this.state;

		return (
			<div className="wp-block-wp-strava-som-control">
				<p className="wp-block-wp-strava-som-control-row">
						{ __( 'System of Measure (override from settings)' ) }
				</p>
				<div className="wp-block-wp-strava-som-control-row">
					<ButtonGroup aria-label={ __( 'System of Measure', 'wp-strava' ) }>
						<Button
							isSmall
							isPrimary={ som == 'english' }
							isPressed={ som == 'english' }
							value='english'
							onClick={ this.onChange }
						>
							{ __( 'English', 'wp-strava' ) }
						</Button>
						<Button
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

export default SOMOverride;
