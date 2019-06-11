<?php 
	if($calendars)
	{
		echo '<div class="accordion accordion-style1 panel-group">';
		
		foreach( $calendars as $calendar )
		{
		?>	
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						<span style="z-index: 2; position: absolute; right: 15px; top: 5px;">
							<?php 
								echo CHtml::link('View', array('//calendar/index', 'calendar_id'=>$calendar->id, 'customer_id' => $calendar->customer_id), array('class'=>'btn btn-info btn-minier')); 
							?>
						</span>
																
						<a href="#calendar<?php echo $calendar->id; ?>" data-parent="#accordion" data-toggle="collapse" class="accordion-toggle collapsed" aria-expanded="false">
							<i data-icon-show="ace-icon fa fa-angle-right" data-icon-hide="ace-icon fa fa-angle-down" class="bigger-110 ace-icon fa fa-angle-right"></i>
							&nbsp;<?php echo $calendar->name; ?>
						</a>
					</h4>
				</div>

				<div id="calendar<?php echo $calendar->id; ?>" class="panel-collapse collapse" aria-expanded="true" style="">
					<div class="panel-body">
						<?php 
							$this->renderPartial('_form', array(
								'office' => $office,
								'calendar' => $calendar,
							));
						?>
					</div>
				</div>
			</div>
			
		<?php
		}
		
		echo '</div>';
	}
	else
	{
		echo 'No calendar created.';
	}
?>
