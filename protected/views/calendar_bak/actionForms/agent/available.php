<div class="profile-user-info profile-user-info-striped">

	<div class="profile-info-row">
		<div class="profile-info-name"> Action </div>

		<div class="profile-info-value">
			<?php echo $form->dropDownList($model, 'title', array('APPOINTMENT SET'=>'APPOINTMENT SET', 'LOCATION CONFLICT'=>'LOCATION CONFLICT'), array('class'=>'form-control', 'prompt'=>' - Select -', 'style'=>'width:auto;')); ?>
		</div>
	</div>
	
	<div class="profile-info-row lead-field-container">
		<div class="profile-info-name"> Lead </div>

		<div class="profile-info-value">
			<?php 
				echo $form->dropDownList($model, 'lead_id', Lead::items($model->calendar_id), array('class'=>'form-control', 'prompt'=>' - Select -', 'style'=>'width:auto;', 'disabled'=>true));
			?>
		</div>
	</div>
	
	<div class="profile-info-row">
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
	
	<div class="profile-info-row">
		<div class="profile-info-name"> End Time </div>

		<div class="profile-info-value">
			<?php 
				echo $form->dropDownList($model, 'end_date_time', $timeOptions, array('class'=>'form-control', 'prompt'=>' - Select -', 'style'=>'width:auto;', 'disabled'=>true)); 
			?>
		</div>
	</div>				
	
	<div class="profile-info-row details-field-container"  style="display:none;">
		<div class="profile-info-name"> Details </div>

		<div class="profile-info-value">
			<?php 
				echo $form->textArea($model, 'details', array('class'=>'col-xs-12')); 
			?>
		</div>
	</div>
	
	<div class="profile-info-row location-field-container"  style="display:none;">
		<div class="profile-info-name"> <?php echo $model->isNewRecord ? 'Proposed Location' : 'Location'; ?> </div>

		<div class="profile-info-value">								
		
			<?php 
				echo CHtml::dropDownList('', '', $calendar->locationOptions(), array('id'=>'customerApprovedLocations', 'style'=>'display:none;'));
				echo CHtml::dropDownList('', '', $calendar->locationOptions('all'), array('id'=>'allLocations', 'style'=>'display:none;'));

				echo $form->dropDownList($model, 'location', $calendar->locationOptions(), array('class'=>'form-control', 'prompt'=>' - Select -','style'=>'width:auto;',));
			?>
		</div>
	</div>
	
	<div class="profile-info-row agent-field-container">
		<div class="profile-info-name"> Agent Note </div>

		<div class="profile-info-value">
			<?php 
				echo $form->textArea($model, 'agent_notes', array('class'=>'col-xs-12')); 
			?>
		</div>
	</div>
</div>

<div class="space-12"></div>

<div class="center">
	<button type="button" class="btn btn-sm btn-info" data-action="save" existingEvent="<?php echo $existingEvent; ?>">Save</button>
</div>