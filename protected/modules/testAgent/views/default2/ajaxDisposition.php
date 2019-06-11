<?php 
	if( !empty($dispositionDetailOptions) )
	{
	?>
		<br />
		<br />
		
		<div class="form-group">
			<label for="form-field-1" class="col-sm-5 control-label no-padding-right"> 
				DETAIL 
			</label>

			<div class="col-sm-7">
				<?php echo CHtml::dropDownList('Lead[office_phone_dispo_detail_id]', '', $dispositionDetailOptions, array('phone_type'=>'office', 'field_type'=>'dispo_detail', 'prompt'=>'- Select -')); ?>
			</div>
		</div>
	<?php
	}
?>

<?php 
	if( $disposition->is_send_email == 1 )
	{
		$agentNote = $disposition->notes_prefill;
		
		if( $leadCallHistory )
		{
			if( $leadCallHistory->calendar_appointment_id != null )
			{
				$calendarAppointment = CalendarAppointment::model()->findByPk($leadCallHistory->calendar_appointment_id);
				
				if( $calendarAppointment )
				{
					$agentNote = $calendarAppointment->details;
				}
			}
		}
		
		$agentNote = $leadCallHistory->getReplacementCodeValues($agentNote);
	?>	
		<div class="form-group">
			<div class="col-sm-12">
				<br />
				NOTE
				
				<textarea placeholder="" style="width:100%; min-width:100%;"><?php echo $agentNote; ?></textarea>
			</div>
		</div>
	<?php
	}
?>

<?php 
	if( $disposition->is_callback == 1 )
	{
	?>
		<div class="form-group">
			<div class="col-sm-12">
				<br />
				CALLBACK DATE/LOCAL TIME

				<div class="row">
					<div class="col-sm-6">
						<input type="text" class="col-sm-12 datepicker callback-date">
					</div>
					
					<div class="col-sm-6">
						<input type="text" class="col-sm-12 callback-time" placeholder="e.g. 1:30 PM">
					</div>
				</div>
			</div>
		</div>
	<?php
	}
?>

<div class="text-right">	
	<div class="col-sm-12">
	
		<div class="space-6"></div>
	
		<button class="btn btn-minier btn-primary disposition-submit-btn">Submit</button>
	</div>
</div>