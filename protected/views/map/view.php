<?php Yii::app()->clientScript->registerScriptFile('https://maps.googleapis.com/maps/api/js?key=AIzaSyBhgGuFAwyYvpUbYstsiIbSKcdcm4eQZIQ&signed_in=true&callback=initMap', CClientScript::POS_END, array('async'=>true, 'defer' => true)); ?>
<?php 
	Yii::app()->clientScript->registerScript('googleMapsFunctions',"
		function initMap() {
			var directionsDisplay = new google.maps.DirectionsRenderer;
			var directionsService = new google.maps.DirectionsService;
			
			var map = new google.maps.Map(document.getElementById('map'), {
				zoom: 7,
				center: {lat: 41.85, lng: -87.65}
			});

			directionsDisplay.setMap(map);
			
			if( $('#right-panel').is(':empty') )
			{
				directionsDisplay.setPanel(document.getElementById('right-panel'));
			}
			
			calculateAndDisplayRoute(directionsService, directionsDisplay);
		}

		function calculateAndDisplayRoute(directionsService, directionsDisplay) {
			var start = '".$lead->address."';
			var end = '".$office->address."';
			
			directionsService.route({
				origin: start,
				destination: end,
				travelMode: google.maps.TravelMode.DRIVING
			}, function(response, status) {
				if (status === google.maps.DirectionsStatus.OK) {

					directionsDisplay.setDirections(response);
				} else {
					window.alert('Directions request failed due to ' + status);
				}
			});
		}

	",CClientScript::POS_HEAD);
?>
 
 <?php Yii::app()->clientScript->registerScript('emailingScript','
	
	$(document).load( function(){ 
	
		initMap(); 

	});

 ',CClientScript::POS_END); ?>

<div class="row">
	<div class="col-md-8"><div id="map" style="height:500px;"></div></div>
	<div class="col-md-4">
		
		<div id="right-panel"></div>
	</div>
</div>

