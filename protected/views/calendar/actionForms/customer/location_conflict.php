<div class="profile-user-info profile-user-info-striped">
	
	<div class="profile-info-row">
		<div class="profile-info-name"> Calendar </div>

		<div class="profile-info-value">
			<?php
				echo $form->dropDownList($model, 'calendar_id', Calendar::items($model->calendar->customer_id), array('class'=>'form-control', 'style'=>'width:auto;')); 
			?>
		</div>
	</div>
	
	<div class="profile-info-row">
		<div class="profile-info-name"> Date </div>

		<div class="profile-info-value">
			<?php echo CHtml::textField('CalendarAppointment[alt_date]', date('m/d/Y', strtotime($model->start_date)), array('class' => 'datepicker', 'disabled'=>true)); ?>
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
				echo $form->textArea($model, 'details', array('class'=>'col-xs-12', 'disabled'=>true)); 
			?>
		</div>
	</div>*/ ?>
	
	<div class="profile-info-row details-field-container">
		<div class="profile-info-name"> Agent Notes </div>

		<div class="profile-info-value">
			<?php 
				echo $form->textArea($model, 'agent_notes', array('style'=>'height:150px;', 'class'=>'col-xs-12', 'disabled'=>true)); 
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
	
	<div class="profile-info-row customer-field-container">
		<div class="profile-info-name"> Customer Note </div>

		<div class="profile-info-value">
			<?php 
				echo $form->textArea($model, 'customer_notes', array('style'=>'height:150px;', 'class'=>'col-xs-12')); 
			?>
		</div>
	</div>
</div>

<?php if( in_array($model->status, array(1,3,5)) ): ?>

	<div class="space-12"></div>
	
	<div class="row center"> 
		<div class="col-sm-12"> 
			<?php 
				$leadHistory = LeadHistory::model()->find(array(
					'condition' => 'lead_id = :lead_id AND account_id IS NOT NULL',
					'params' => array(
						':lead_id' => $model->lead_id,
					),
					'order' => 'date_created DESC'
				));
				
				if( $leadHistory )
				{
					if( $model->status == $model::STATUS_DECLINED ) 
					{
						echo 'Declined';
					}
					elseif( $model->status == $model::STATUS_SUGGEST )
					{
						echo 'Sugest Alt';
					}				
					else
					{
						echo 'Approved';
					}
					
					if( isset($leadHistory->account) )
					{
						$date = new DateTime($leadHistory->date_created, new DateTimeZone('America/Chicago'));

						$date->setTimezone(new DateTimeZone('America/Denver'));
			
						echo ' - '.$leadHistory->account->getFullName().' - '.$date->format('m/d/Y g:i A');
					}
				}
			?>
		</div>
	</div>

<?php endif; ?>

<div class="space-12"></div>

<div class="center">
	<?php 
		if( $model->status == 2 )
		{
			echo '<button type="button" class="btn btn-sm btn-success" data-action="approved">Approved</button>';
		}
		else
		{
			echo '<button type="button" class="btn btn-sm btn-success disabled">Approved</button>';
		}
	?>
	
	<button type="button" class="btn btn-sm btn-danger" data-action="declined"> Declined</button>
	<button type="button" class="btn btn-sm btn-info" data-action="suggest"> Suggest Alt</button>
</div>