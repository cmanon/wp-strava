/* global wp, wpStrava */
import EmbedPlaceholder from './embed-placeholder';
import { isEmpty } from 'lodash';

/**
 * Localized Data.
 */
const {
	placeholderActivityImg,
} = wpStrava;

const Edit = (props) => {
	const {
		setAttributes,
		cannotEmbed,
		tryAgain,
		attributes: {
			url
		}
	 } = props;


	const submitUrl = (event) => {
		if ( event ) {
			event.preventDefault();
		}
		console.log( 'onsubmit', event );
		setAttributes( { url: event.target.value } )
	};

	const changeUrl = ( event ) => {
		console.log( 'onchange', event );
		console.log( 'Props', props );
		setAttributes( { url: event.target.value } );
	};

	if ( isEmpty( url ) ) {
		return (
			<EmbedPlaceholder
				icon="chart-line"
				label="BBQ LOL Label"
				value={ url }
				cannotEmbed={ cannotEmbed }
				onChange={ changeUrl }
				fallback={ () => fallback( url, props.onReplace ) }
				tryAgain={ tryAgain }
				onSubmit={ submitUrl }
			/>
		);
	}

	return (
		<img className="wp-strava-img" src={placeholderActivityImg} />
	);
};

export default Edit;
