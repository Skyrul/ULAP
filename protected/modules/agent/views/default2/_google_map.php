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
			var start = '".str_replace("'", "",$lead->address)."';
			var end = '".str_replace("'", "",$office->address)."';
			
			if( start == '' )
			{
				start = end;
			}
			
			if( end == '' )
			{
				end = start;
			}
			
			directionsService.route({
				origin: start,
				destination: end,
				travelMode: google.maps.TravelMode.DRIVING
			}, function(response, status) {
				if (status === google.maps.DirectionsStatus.OK) {

					directionsDisplay.setDirections(response);
					setTimeout(getDirectionHtml, 3000);
				} else {
					// window.alert('Directions request failed due to ' + status);
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
			data: { "direction" : directionString },
			success: function(response) {
				alert(response.message);
			}
		});
	});
	
	
	$(document).load( function(){ 
	
		initMap(); 

	});

 ',CClientScript::POS_END); ?>

<div class="row">
	<div class="col-md-8"><div id="map" style="height:500px;"></div></div>
	<div class="col-md-4">
		
		<div id="right-panel"></div>
		
		<div class="space-6"></div>
		
		<div class="text-center"> 
			<?php echo CHtml::link('<i class="fa fa-envelope"></i> Send directions',array('emailDirections', 'lead_id'=>$lead->id, 'office_id'=>$office->id), array('id'=>'sendEmail', 'class'=>'btn btn-primary btn-xs', 'style'=>'display:none;')); ?>
		</div>
	</div>
</div>

