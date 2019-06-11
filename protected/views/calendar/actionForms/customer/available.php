<div class="profile-user-info profile-user-info-striped">

	<?php 
		foreach( $model->attributes as $key => $val )
		{
			echo $form->hiddenField($model, $key, array('id'=>'Model_'.$key));
		}
	?>

	<div class="profile-info-row">
		<div class="profile-info-name"> Action </div>

		<div class="profile-info-value">
			<?php echo $form->dropDownList($model, 'title', array('INSERT APPOINTMENT'=>'INSERT APPOINTMENT'), array('class'=>'form-control', 'prompt'=>' - Select -', 'style'=>'width:auto;')); ?>
		</div>
	</div>
	
	<div class="profile-info-row lead-field-container" style="display:none;">
		<div class="profile-info-name"> Lead </div>

		<div class="profile-info-value">
			<div class="col-sm-6">
				<div class="row">
					<?php echo $form->dropDownList($model, 'lead_id', Lead::items2($model->calendar->customer_id), array('class'=>'form-control', 'prompt'=>' - Select -', 'style'=>'width:auto;')); ?>
				</div>
			</div>
			
			<div class="col-sm-6">
				<?php echo CHtml::link('Add New Lead', array('/customer/leads', 'customer_id'=>$model->calendar->customer_id), array('class'=>'add-new-lead-link', 'target'=>'_blank', 'style'=>'line-height:30px; margin-left:-25px; ')); ?>
			</div>
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
	
	<?php /*<div class="profile-info-row details-field-container"  style="display:none;">
		<div class="profile-info-name"> Details </div>

		<div class="profile-info-value">
			<?php 
				echo $form->textArea($model, 'details', array('class'=>'col-xs-12', 'disabled'=>in_array($model->title, array('LOCATION CONFLICT', 'SCHEDULE CONFLICT')) ? true:false)); 
			?>
		</div>
	</div>*/ ?>
	
	<div class="profile-info-row location-field-container"  style="display:none;">
		<div class="profile-info-name"> <?php echo $model->isNewRecord ? 'Proposed Location' : 'Location'; ?> </div>

		<div class="profile-info-value">								
			<?php 
				echo $form->dropDownList($model, 'location', $calendar->locationOptions('all'), array('class'=>'form-control','prompt'=>' - Select -','style'=>'width:auto;',));
			?>
		</div>
	</div>
</div>

<div class="space-12"></div>

<div class="center">
	<button type="button" class="btn btn-sm btn-info" data-action="save">Save</button>
</div>