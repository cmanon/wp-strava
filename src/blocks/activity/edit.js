/* global wp, wpStrava */
import EmbedPlaceholder from './embed-placeholder';
import EmbedControls from './embed-controls';
// import SOMOverride from './som-override';

import ServerSideRender from '@wordpress/server-side-render';

const { __ } = wp.i18n;
const { Component } = wp.element;
const { InspectorControls } = wp.editor;
const { PanelBody, ToggleControl, ServerSideRender, Button, ButtonGroup } = wp.components;
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
										aria-pressed={ som == 'english' }
										onClick={ () => this.overrideSOM( 'english' ) }
									>
										{ __( 'English', 'wp-strava' ) }
									</Button>
									<Button
										key={ 'metric' }
										isSmall
										isPrimary={ som == 'metric' }
										aria-pressed={ som == 'metric' }
										onClick={ () => this.overrideSOM( 'metric' ) }
									>
										{ __( 'Metric', 'wp-strava' ) }
									</Button>
								</ButtonGroup>
								<Button isSmall onClick={ () => this.overrideSOM() }>
									{ __( 'Reset', 'wp-strava' ) }
								</Button>
							</div>
						</div>
					</PanelBody>
				</InspectorControls>
			</>
		);
	}
}

export default Edit;
