<style>.page-content { padding:0 !important; } </style>

<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	$cs = Yii::app()->clientScript;
	
	$cs->registerScript(uniqid(), '
	
		setInterval(function() { 
		
			$.ajax({
				url: yii.urls.absoluteUrl + "/reports/hourlyAttendance",
				type: "post",
				dataType: "json",
				data: { "ajax":1 },
				success: function(response){
					
					$(".hourly-attendance-wrapper").html(response.html);
					
				}
			});

		}, 30000);

	', CClientScript::POS_END);
	
	date_default_timezone_set('America/Denver');
?>

<div class="widget-box widget-color-dark">
	<div class="widget-body">

	
		<div class="hourly-attendance-wrapper">
			<div class="widget-main no-padding" style="position:relative; overflow:auto;">
			
				<div class="row">
					<div class="col-sm-12"> 
						<h4 style=""><?php echo date('l, F d'); ?></h4>
					</div>
				</div>
				
				<table class="table table-condensed table-bordered table-hover call-management-tbl" style="width:100%;">
					<thead>
						<tr>
							<th>Agent</th>
							<th>Scheduled Hours</th>
							<th>Hours Worked</th>
							<?php 
								foreach (Calendar::createTimeRange('7:00 AM', '10:00 PM', '15 minutes') as $time) 
								{
									$startTime  = $time;
									// $endTime  = $time + 1800; //add 30 minutes
									$endTime  = $time + 900; //add 15 minutes
									
									echo '<th>';
										
										echo date('g:i A', $time);
										
									echo '</th>';
								}
							?>
						</tr>
					</thead>
					
					<tbody><?php echo $html; ?></tbody>
					
				</table>
			</div>
		</div>
		
	</div>
</div>
