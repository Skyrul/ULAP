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
		
		// if( $leadCallHistory )
		// {
			// if( $leadCallHistory->calendar_appointment_id != null )
			// {
				// $calendarAppointment = CalendarAppointment::model()->findByPk($leadCallHistory->calendar_appointment_id);
				
				// if( $calendarAppointment )
				// {
					// if( !empty($agentNote) )
					// {
						// $agentNote .= '<br /><br />';
					// }
					
					// $agentNote .= $calendarAppointment->details;
				// }
			// }
		// }
		
		$agentNote = $leadCallHistory->getReplacementCodeValues( preg_replace('/<br(\s+)?\/?>/i', "\n", $agentNote) );
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
					<div class="col-sm-12">
						<input type="text" class="col-sm-12 datepicker callback-date" style="width:100px;margin-right:10px;">

						<?php /*<input type="text" class="col-sm-12 callback-time" placeholder="e.g. 1:30 PM">*/ ?>

						<select class="callback-date-hour">
							<?php
								foreach( range(1,12) as $hour )
								{
									echo '<option value="'.$hour.'">'.$hour.'</option>';
								}
							?>
						</select>
						
						<select class="callback-date-minute">
							<option value="00">00</option>
							<option value="05">05</option>
							<option value="10">10</option>
							<option value="15">15</option>
							<option value="20">20</option>
							<option value="25">25</option>
							<option value="30">30</option>
							<option value="35">35</option>
							<option value="40">40</option>
							<option value="45">45</option>
							<option value="50">50</option>
							<option value="55">55</option>
						</select>
							
						<select class="callback-date-time">
							<option value="AM">AM</option>
							<option value="PM">PM</option>
						</select>	
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
	
		<button class="btn btn-minier btn-primary disposition-submit-btn" style="margin-right:15px;">Submit</button>
	</div>
</div>