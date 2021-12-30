window.addEventListener( 'load', function() {
	strava_toggle_map_type(); // Initial load toggle.
	document.getElementById( 'strava_map_type' ).addEventListener( 'input', function( e ) {
		if ( 'SELECT' === e.target.tagName ) {
			strava_toggle_map_type(); // On change toggle.
		}
	}, false );
} );

/**
 * Toggle Mapbox / GMaps fields & instructions.
 */
function strava_toggle_map_type() {
	var select     = document.getElementById( 'strava_map_type' ),
	    map_type   = select.options[select.selectedIndex].value
	    mapbox_id  = 'strava_mapbox_token',
	    gmaps_id   = 'strava_gmaps_key',
	    mapbox_ins = 'strava-maps-mapbox-instructions',
		gmaps_ins  = 'strava-maps-gmaps-instructions';

	// Toggle Instructions.
	var mapbox_text = document.getElementById( mapbox_ins );
	if ( 'mapbox' === map_type ) {
		if ( mapbox_text.classList.contains( 'hidden' ) ) {
			mapbox_text.classList.remove( 'hidden' );
		}
	} else {
		mapbox_text.classList.add( 'hidden' );
	}

	var gmaps_text = document.getElementById( gmaps_ins );
	if ( 'mapbox' !== map_type ) {
		if ( gmaps_text.classList.contains( 'hidden' ) ) {
			gmaps_text.classList.remove( 'hidden' );
		}
	} else {
		gmaps_text.classList.add( 'hidden' );
	}

	// Toggle Inputs.
	var mapbox_row = document.getElementById( mapbox_id ).closest( 'tr' );
	if ( mapbox_row ) {
		mapbox_row.style.display = ( 'mapbox' === map_type ) ? '' : 'none';
	}

	var gmaps_row = document.getElementById( gmaps_id ).closest( 'tr' );
	if ( gmaps_row ) {
		gmaps_row.style.display = ( 'mapbox' !== map_type ) ? '' : 'none';
	}
}
