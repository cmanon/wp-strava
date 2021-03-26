/* global wp, wpStrava */
import SOMOverride from '../components/som-override';

const { __ } = wp.i18n;
const { Component } = wp.element;
const { InspectorControls } = wp.editor;
const { PanelBody, ToggleControl, ServerSideRender } = wp.components;

class Edit extends Component {

	constructor() {
		super( ...arguments );
		this.overrideSOM = this.overrideSOM.bind( this );

		this.state = {
			som: this.props.attributes.som,
		};
	}

	overrideSOM( newSOM ) {
		this.setState( { som: newSOM } );
		this.props.setAttributes( { som: newSOM } );
	}

	render() {
		const {
			som
		} = this.state;

		return (
			<>
				<ServerSideRender
					block="wp-strava/activitieslist"
					attributes={ {
						som: som,
					} }
				/>
				<InspectorControls>
					<PanelBody
						title={ __( 'Display Options', 'wp-strava' ) }
					>
						<SOMOverride
							onChange={ this.overrideSOM }
						/>
					</PanelBody>
				</InspectorControls>
			</>
		);
	}
}

export default Edit;
