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
		  directionsDisplay.setPanel(document.getElementById('right-panel'));

		  var control = document.getElementById('floating-panel');
		  control.style.display = 'block';
		  map.controls[google.maps.ControlPosition.TOP_CENTER].push(control);

		  var onChangeHandler = function() {
			calculateAndDisplayRoute(directionsService, directionsDisplay);
		  };
		  document.getElementById('start').addEventListener('change', onChangeHandler);
		  document.getElementById('end').addEventListener('change', onChangeHandler);
		}

		function calculateAndDisplayRoute(directionsService, directionsDisplay) {
		  var start = document.getElementById('start').value;
		  var end = document.getElementById('end').value;
		  directionsService.route({
			origin: start,
			destination: end,
			travelMode: google.maps.TravelMode.DRIVING
		  }, function(response, status) {
			if (status === google.maps.DirectionsStatus.OK) {
				
			  directionsDisplay.setDirections(response);
			 setTimeout(getDirectionHtml, 3000);
			} else {
			  window.alert('Directions request failed due to ' + status);
			}
		  });
		}

	",CClientScript::POS_HEAD);
?>

<?php Yii::app()->clientScript->registerScript('customizeGMAPFunction','
	
	var directionString = "";
	
	function getDirectionHtml()
	{
		directionString = "";
		
		$(".adp-directions tr").each(function( index ) {
			directionString +="<li>";
			
			var tds = $(this).find("td.adp-substep");
			tds.each(function(index){
				if($(this).attr("jsvalues"))
				{
					directionString += $( this ).html();
				}
				else if($(this).find(".adp-distance").length > 0)
				{
					directionString += "<span class=\"adp-distance\">("+$( this ).text()+")</span>";
				}
				else
					directionString += $( this ).text();
			});
		  
			directionString +="</li>";
		});
		
		console.log((directionString == ""));
		if(directionString == "")
		{
			$("#sendEmail").hide();
		}
		else
		{
			$("#sendEmail").show();
		}
		
		return false;
	}

',CClientScript::POS_END); ?>
 
 <?php Yii::app()->clientScript->registerScript('emailingScript','
	$("#sendEmail").on("click",function(e){
		
		e.preventDefault();
		var thisHref = $(this).prop("href");
		
		$.ajax({
			url: thisHref,
			method: "POST",
			dataType: "json",
			data: {
			  "direction" : directionString,
			}
		}).success(function(response) {
			alert("Ajax Success");
		});
		
	});
 ',CClientScript::POS_END); ?>
 
<div id="floating-panel">
  <strong>Start:</strong>
  <select id="start">
	<option value="chicago, il">Chicago</option>
	<option value="st louis, mo">St Louis</option>
	<option value="joplin, mo">Joplin, MO</option>
	<option value="oklahoma city, ok">Oklahoma City</option>
	<option value="amarillo, tx">Amarillo</option>
	<option value="gallup, nm">Gallup, NM</option>
	<option value="flagstaff, az">Flagstaff, AZ</option>
	<option value="winona, az">Winona</option>
	<option value="kingman, az">Kingman</option>
	<option value="barstow, ca">Barstow</option>
	<option value="san bernardino, ca">San Bernardino</option>
	<option value="los angeles, ca">Los Angeles</option>
  </select>
  <br>
  <strong>End:</strong>
  <select id="end">
	<option value="chicago, il">Chicago</option>
	<option value="st louis, mo">St Louis</option>
	<option value="joplin, mo">Joplin, MO</option>
	<option value="oklahoma city, ok">Oklahoma City</option>
	<option value="amarillo, tx">Amarillo</option>
	<option value="gallup, nm">Gallup, NM</option>
	<option value="flagstaff, az">Flagstaff, AZ</option>
	<option value="winona, az">Winona</option>
	<option value="kingman, az">Kingman</option>
	<option value="barstow, ca">Barstow</option>
	<option value="san bernardino, ca">San Bernardino</option>
	<option value="los angeles, ca">Los Angeles</option>
  </select>
</div>

<div class="row">
	<div class="col-md-8"><div id="map" style="height:500px;"></div></div>
	<div class="col-md-4">
		<div id="right-panel"></div>
		<div> <?php echo CHtml::link('Send Email Direction',array('map/email'), array('id'=>'sendEmail', 'style'=>'display:none;')); ?></div>
	</div>
</div>

