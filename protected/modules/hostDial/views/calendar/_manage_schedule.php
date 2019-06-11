<form id="form-schedule-settings" class="no-margin">								
	<?php echo CHtml::hiddenField('CalendarCustomSchedule[calendar_id]', $calendar->id); ?>
	
	<table class="table table-bordered table-striped table-condensed">
		<?php 
			$days = array(
				'Monday',
				'Tuesday',
				'Wednesday',
				'Thursday',
				'Friday',
				'Saturday',
				'Sunday',
			);
			
			echo '<tr>';
			
				foreach( $days as $day )
				{
					echo '<th class="text-center">'.$day.'</th>';
				}
			
			echo '</tr>';
			
			foreach (Calendar::createTimeRange($calendar->appointment_start_time, $calendar->appointment_end_time, $calendar->appointment_length) as $time) 
			{
				$time = date('g:i A', $time);
				
				echo '<tr>';
					foreach( $days as $day )
					{									
						$existingCustomSchedule = CalendarCustomSchedule::model()->find(array(
							'condition' => 'calendar_id = :calendar_id AND day = :day AND time = :time',
							'params' => array(
								':calendar_id' => $calendar->id,
								':day' => $day,
								':time' => $time,
							),
						));
						
						if($existingCustomSchedule)
						{
							$checked = 'checked';
						}
						else
						{
							$checked = '';
						}
						
						echo '<td>';
							echo '<input type="checkbox" class="ace" name="CalendarAppointmentSchedule['.$day.'][]" value="'.$time.'" '.$checked.'><span class="lbl">&nbsp;'.$time.'</span>';
						echo '</td>';
					}
				echo '</tr>';
			}
		?>
	</table>
	
	<div class="row form-actions" style="margin-top:46px;">
		<div class="col-sm-12" style="text-align:center;">
			<button type="button" class="btn btn-xs apply-default-schedule">Apply Default</button>
			<button type="button" class="btn btn-xs btn-info apply-custom-schedule"><i class="ace-icon fa fa-check"></i> Apply</button>
		</div>
	</div>	
</form>