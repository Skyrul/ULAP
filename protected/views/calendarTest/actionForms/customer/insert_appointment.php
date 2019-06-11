<div class="profile-user-info profile-user-info-striped">

	<div class="profile-info-row">
		<div class="profile-info-name"> Action </div>

		<div class="profile-info-value">
			<?php 
				if( strtotime($model->start_date) < time() )
				{
					// if( strtotime('+48 hours', strtotime($model->start_date)) >= time() )
					if( strtotime($model->start_date) > strtotime(date("Y-m-d").' -2 Weekdays')  )
					{
						$actionOptions = array('NO SHOW RESCHEDULE' => 'NO SHOW RESCHEDULE', 'RESCHEDULE APPOINTMENT' => 'RESCHEDULE APPOINTMENT');
					}
					else
					{
						$actionOptions = array('RESCHEDULE APPOINTMENT' => 'RESCHEDULE APPOINTMENT');
					}
				}
				else
				{
					$actionOptions = array('CANCEL APPOINTMENT' => 'CANCEL APPOINTMENT', 'RESCHEDULE APPOINTMENT' => 'RESCHEDULE APPOINTMENT', 'CHANGE APPOINTMENT'=>'CHANGE APPOINTMENT');
				}
				
				echo $form->dropDownList($model, 'title', $actionOptions, array('class'=>'form-control', 'prompt'=>' - Select -', 'style'=>'width:auto;')); 
			?>
		</div>
	</div>
	
	<div class="profile-info-row start-time-field-container">
		<div class="profile-info-name"> Start Time </div>

		<div class="profile-info-value">
			<?php
			
				$timeOptions = $calendar->timeOptions();
				
				if( !in_array($model->start_date_time, $timeOptions) )
				{
					$timeOptions[$model->start_date_time] = date('g:i A', strtotime($model->start_date));
				}
				
				if( !in_array($model->end_date_time, $timeOptions) )
				{
					$timeOptions[$model->end_date_time] = date('g:i A', strtotime($model->end_date));
				}
				

				echo $form->dropDownList($model, 'start_date_time', $timeOptions, array('class'=>'form-control', 'prompt'=>' - Select -', 'style'=>'width:auto;', 'disabled'=>true));
			?>
		</div>
	</div>
	
	<div class="profile-info-row end-time-field-container">
		<div class="profile-info-name"> End Time </div>

		<div class="profile-info-value">
			<?php 
				echo $form->dropDownList($model, 'end_date_time', $timeOptions, array('class'=>'form-control', 'prompt'=>' - Select -', 'style'=>'width:auto;', 'disabled'=>true)); 
			?>
		</div>
	</div>				
	
	<?php /*<div class="profile-info-row details-field-container">
		<div class="profile-info-name"> Details </div>

		<div class="profile-info-value">
			<?php 
				echo $form->textArea($model, 'details', array('class'=>'col-xs-12', 'disabled'=>true)); 
			?>
		</div>
	</div>*/ ?>
	
	<div class="profile-info-row agent-field-container">
		<div class="profile-info-name"> Customer Notes </div>

		<div class="profile-info-value">
			<?php 
				echo $form->textArea($model, 'customer_notes', array('class'=>'col-xs-12', 'disabled'=>$viewer == 'customer' ? false : true)); 
			?>
		</div>
	</div>
				
	<div class="profile-info-row location-field-container">
		<div class="profile-info-name"> <?php echo $model->isNewRecord ? 'Proposed Location' : 'Location'; ?> </div>

		<div class="profile-info-value">								
			<?php 
				echo $form->dropDownList($model, 'location', $calendar->locationOptions('all'), array('class'=>'form-control','prompt'=>' - Select -','style'=>'width:auto;', 'disabled'=>true));
			?>
		</div>
	</div>
</div>

<div class="space-12"></div>

<div class="center">
	<button type="button" class="btn btn-sm btn-info" data-action="save">Save</button>
</div>