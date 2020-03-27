/* global wp, wpStrava */
import EmbedPlaceholder from './embed-placeholder';
import EmbedControls from './embed-controls';

const { Component } = wp.element;
const { isEmpty } = lodash;

/**
 * Localized Data.
 */
const {
	placeholderActivityImg,
} = wpStrava;

class Edit extends Component {

	constructor() {
		super( ...arguments );
		this.setUrl = this.setUrl.bind( this );
		this.switchBackToURLInput = this.switchBackToURLInput.bind( this );

		this.state = {
			url: this.props.attributes.url,
			editingURL: isEmpty( this.props.attributes.url ) ? true : false,
		};
	}

	setUrl( event ) {
		if ( event ) {
			event.preventDefault();
		}
		const { url } = this.state;
		const { setAttributes } = this.props;
		this.setState( { editingURL: false } );
		setAttributes( { url } );
	}

	switchBackToURLInput() {
		this.setState( { editingURL: true } );
	}

	render() {
		const { url, editingURL } = this.state;

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
				<img className="wp-strava-img" src={placeholderActivityImg} />
			</>
		);
	}

};

export default Edit;
