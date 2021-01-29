/* global wp, wpStrava */
import EmbedPlaceholder from '../components/embed-placeholder';
import EmbedControls from '../components/embed-controls';
import SOMOverride from '../components/som-override';

const { __ } = wp.i18n;
const { Component } = wp.element;
const { InspectorControls } = wp.editor;
const { PanelBody, ToggleControl, ServerSideRender } = wp.components;
const { isEmpty } = lodash;

class Edit extends Component {

	constructor() {
		super( ...arguments );
		this.setUrl               = this.setUrl.bind( this );
		this.switchBackToURLInput = this.switchBackToURLInput.bind( this );
		this.toggleImageOnly      = this.toggleImageOnly.bind( this );
		this.toggleDisplayMarkers = this.toggleDisplayMarkers.bind( this );
		this.overrideSOM          = this.overrideSOM.bind( this );

		this.state = {
			url: this.props.attributes.url,
			imageOnly: this.props.attributes.imageOnly,
			displayMarkers: this.props.attributes.displayMarkers,
			som: this.props.attributes.som,
			editingURL: isEmpty( this.props.attributes.url ) ? true : false,
		};
	}

	setUrl( event ) {
		if ( event ) {
			event.preventDefault();
		}
		this.setState( { editingURL: false } );
		this.props.setAttributes( { url: this.state.url } );
	}

	switchBackToURLInput() {
		this.setState( { editingURL: true } );
	}

	toggleImageOnly( checked ) {
		this.setState( { imageOnly: checked } );
		this.props.setAttributes( { imageOnly: checked } );
	}

	toggleDisplayMarkers( checked ) {
		this.setState( { displayMarkers: checked } );
		this.props.setAttributes( { displayMarkers: checked } );
	}

	overrideSOM( newSOM ) {
		this.setState( { som: newSOM } );
		this.props.setAttributes( { som: newSOM } );
	}

	render() {
		const {
			url,
			editingURL,
			imageOnly,
			displayMarkers,
			som
		} = this.state;

		// Newly inserted block or we've clicked the edit button.
		if ( editingURL ) {
			return (
				<EmbedPlaceholder
					icon="chart-line"
					label="Strava Activity"
					instructions={ __(
						'Paste a link to the Strava Activity you want to display on your site.',
						'wp-strava'
					) }
					placeholder={ __( 'Enter Activity URL to embed here…', 'wp-strava' ) }
					onSubmit={ this.setUrl }
					value={ url }
					onChange={ ( event ) =>
						this.setState( { url: event.target.value } )
					}
				/>
			);
		}

		return (
			<>
				<EmbedControls
					switchBackToURLInput={ this.switchBackToURLInput }
				/>
				<ServerSideRender
					block="wp-strava/activity"
					attributes={ {
						url: url,
						imageOnly: imageOnly,
						displayMarkers: displayMarkers,
						som: som,
					} }
				/>
				<InspectorControls>
					<PanelBody
						title={ __( 'Display Options' ) }
					>
						<ToggleControl
							label={ __( 'Image Only' ) }
							checked={ imageOnly }
							onChange={ ( checked ) => this.toggleImageOnly( checked ) }
						/>
						<ToggleControl
							label={ __( 'Display Markers' ) }
							checked={ displayMarkers }
							onChange={ (checked ) => this.toggleDisplayMarkers( checked ) }
						/>
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
