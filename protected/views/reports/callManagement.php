<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	$cs = Yii::app()->clientScript;
	
	$cs->registerCss(uniqid(), '
	
		.page-content { padding:0 !important; } 
		
	');
	
	$cs->registerScript(uniqid(), '
	
		// setInterval(function() { 
		
			// $.ajax({
				// url: yii.urls.absoluteUrl + "/reports/callManagement",
				// type: "post",
				// dataType: "json",
				// data: { "ajax":1 },
				// success: function(response){
					
					// $(".call-management-wrapper").html(response.html);
					
				// }
			// });

		// }, 30000);

	', CClientScript::POS_END);
?>

<div class="widget-box widget-color-dark">
	<div class="widget-body">

	
		<div class="call-management-wrapper">
			<div class="widget-main no-padding" style="position:relative; overflow:auto; height:485px; ">
			
				<div class="row">
					<div class="col-sm-12">
						<div class="col-sm-6">
							<h4 style="">Scheduled Hours: <?php echo $totalScheduledHours; ?></h4>
						</div>
						<div class="col-sm-6">
							<h4 style="">
								<?php 
									$today = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('America/Chicago'));
									$today->setTimezone(new DateTimeZone('America/Denver'));
									
									echo $today->format('l, F d');
								?>
							</h4>
						</div>
					</div>
				</div>
				
				<table class="table table-condensed table-bordered table-hover call-management-tbl" style="width:100%;">
					<thead>
						<tr>
							<th>Agent</th>
							<th>Status</th>
							<th>DPH</th>
							<?php 
								date_default_timezone_set('America/Denver');

								foreach (Calendar::createTimeRange('7:00 AM', '10:00 PM', '15 minutes') as $time) 
								{
									$startTime  = $time;
									// $endTime  = $time + 1800; //add 30 minutes
									$endTime  = $time + 900; //add 15 minutes
									
									if( time() >= $startTime && time() <= $endTime )
									{
										$currentTimeBar = '<div class="time-bar" style="border: 1px solid red; height: 95%; margin-left:13px; position: absolute;"></div>';
									}
									else
									{
										$currentTimeBar = '';
									}
									
									echo '<th>';
										
										echo date('g:i A', $time);
										
										echo $currentTimeBar;
										
									echo '</th>';
								}
							?>
						</tr>
					</thead>
					
					<tbody><?php echo $callManagementTableHtml; ?></tbody>
					
				</table>
			</div>
		</div>
		
	</div>
</div>
