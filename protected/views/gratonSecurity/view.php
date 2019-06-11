<div class="wrapper">
	<div class="page-header">
		<h1>
			Graton Credit Monitoring - Used Codes
			<?php echo CHtml::link('<i class="fa fa-arrow-left"></i> Back', array('gratonSecurity/index'), array('class'=>'btn btn-sm btn-info')); ?>
			<?php echo CHtml::link('Export <i class="fa fa-file-excel-o"></i>', array('gratonSecurity/export'), array('class'=>'btn btn-sm btn-yellow')); ?>
		</h1>
	</div>
	
	<div class="space-12"></div>
		
	<div class="row">
		<div class="col-sm-8 col-sm-offset-2">
			<table class="table table-condensed table-bordered table-striped table-hover">
				<thead>
					<th class="center">#</th>
					<th class="center">Code</th>
					<th class="center">Name</th>
					<th class="center">Date/Time</th>
				</thead>
				<tbody>
					<?php 
						if( $models )
						{
							$ctr = 1;
							
							foreach( $models as $model )
							{
								$date = new DateTime($model->date_updated, new DateTimeZone('America/Chicago'));
								$date->setTimezone(new DateTimeZone('America/Denver'));
								
								echo '<tr>';
								
									echo '<td class="center">'.$ctr.'</td>';
									echo '<td class="center">'.$model->number.'</td>';
									echo '<td class="center">'.$model->name.'</td>';
									echo '<td class="center">'.$date->format('m/d/Y g:i A').'</td>';
									
								echo '</tr>';
								
								$ctr++;
							}
						}
						else
						{
							echo '<tr><td colspan="2">No activation codes found.</td></tr>';
						}
					?>
				</tbody>
			</table>
		</div>
	</div>
</div>