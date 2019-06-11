<?php 
	$ifHidden = '';
	$authAccount = Yii::app()->user->account;
	
	if( in_array($model->title, array("LOCATION CONFLICT","SCHEDULE CONFLICT")) && $authAccount->account_type_id == Account::TYPE_AGENT)
	{
		if($model->account_id == $authAccount->id)
			$ifHidden = '';
		else
			$ifHidden = 'hidden';
	}
?>

<div class="profile-user-info profile-user-info-striped">
	
	<div class="profile-info-row">
		<div class="profile-info-name"> Action </div>

		<div class="profile-info-value">
			<?php echo $form->dropDownList($model, 'title', array('APPOINTMENT SET'=>'APPOINTMENT SET', 'LOCATION CONFLICT'=>'LOCATION CONFLICT', 'SCHEDULE CONFLICT'=>'SCHEDULE CONFLICT'), array('class'=>'form-control', 'prompt'=>' - Select -', 'style'=>'width:auto;')); ?>
		</div>
	</div>
	
	<div class="profile-info-row lead-field-container  <?php echo $ifHidden; ?>">
		<div class="profile-info-name"> Lead </div>

		<div class="profile-info-value">
			<?php 
				// $leadOptions = Lead::items($model->calendar_id);
				$leadOptions = array();
				
				if( !in_array($model->lead_id, $leadOptions) )
				{
					$leadOptions[$model->lead_id] = $model->lead->first_name.' '.$model->lead->last_name;
				}
				
				// echo $form->dropDownList($model, 'lead_id', Lead::items($model->calendar_id), array('class'=>'form-control', 'prompt'=>' - Select -', 'style'=>'width:auto;', 'disabled'=>true));
				echo $form->dropDownList($model, 'lead_id', $leadOptions, array('class'=>'form-control', 'prompt'=>' - Select -', 'style'=>'width:auto;', 'disabled'=>true));
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
	
	<?php /*<div class="profile-info-row details-field-container">
		<div class="profile-info-name"> Details </div>

		<div class="profile-info-value">
			<?php 
				echo $form->textArea($model, 'details', array('class'=>'col-xs-12')); 
			?>
		</div>
	</div>*/ ?>
	
	<div class="profile-info-row details-field-container">
		<div class="profile-info-name"> Customer Notes </div>

		<div class="profile-info-value">
			<?php 
				echo $form->textArea($model, 'customer_notes', array('style'=>'height:150px;', 'class'=>'col-xs-12', 'disabled'=>true)); 
			?>
		</div>
	</div>
	
	<div class="profile-info-row location-field-container">
		<div class="profile-info-name"> <?php echo $model->isNewRecord ? 'Proposed Location' : 'Location'; ?> </div>

		<div class="profile-info-value">								
		
			<?php 
				echo CHtml::dropDownList('', '', $calendar->locationOptions('all'), array('id'=>'customerApprovedLocations', 'style'=>'display:none;'));
				echo CHtml::dropDownList('', '', $calendar->locationOptions('all'), array('id'=>'allLocations', 'style'=>'display:none;'));

				echo $form->dropDownList($model, 'location', $calendar->locationOptions('all'), array('class'=>'form-control', 'prompt'=>' - Select -','style'=>'width:auto;',));
			?>
		</div>
	</div>
	
	<div class="profile-info-row">
		<div class="profile-info-name"> Status </div>

		<div class="profile-info-value">
			<?php 
				switch($model->status)
				{
					default: case 1: $status = 'Approved'; break;
					case 2: $status = 'Pending'; break;
					case 3: $status = 'Declined'; break;
					case 4: $status = 'Deleted'; break;
					case 5: $status = 'Suggest Alternative'; break;
				}
				
				echo $status;
			?>
		</div>
	</div>
</div>

<div class="space-12"></div>

<div class="center">
	<button type="button" class="btn btn-sm btn-info" data-action="save" existingEvent="<?php echo $existingEvent; ?>">Save</button>
</div>