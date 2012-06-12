
jQuery(document).ready(function($){
    $('.map').each(function(){
        var mapId = $(this).attr('id');
        
        var rideCoordinates = window.coordinates[mapId].latlng;
        var mapCenter = new google.maps.LatLng(23.091860, -102.839356);
        var mapOptions = {
            zoom: 5,
            center: mapCenter,
            //mapTypeControl: true,
            //mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.DROPDOWN_MENU},
            zoomControl: true,
            zoomControlOptions: {style: google.maps.MapTypeControlStyle.SMALL},
            mapTypeId: google.maps.MapTypeId.TERRAIN
        };
        var mapObject = new google.maps.Map($('#' + mapId)[0], mapOptions);
        var mapBounds = new google.maps.LatLngBounds();
        var mapCoordinates = new google.maps.MVCArray();
        var size = rideCoordinates.length;
        for(i = 0; i < size; i++) {
            point = new google.maps.LatLng(parseFloat(rideCoordinates[i][0]), parseFloat(rideCoordinates[i][1]));
            mapBounds.extend(point);
            mapCoordinates.push(point);
        }
        
        var polylineOptions = {
            path: mapCoordinates,
            strokeColor: '#e0642e',
            strokeOpacity: 0.8,
            strokeWeight: 3
        };
        
        var polyline = new google.maps.Polyline(polylineOptions);
        polyline.setMap(mapObject);
        mapObject.fitBounds(mapBounds);
        //google.maps.event.trigger(mapObject, 'resize');
    });
});
