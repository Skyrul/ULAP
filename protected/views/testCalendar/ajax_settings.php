<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title blue"><i class="fa fa-cog"></i> Settings</h4>
			</div>
			
			<div class="modal-body">
				<form class="no-margin">
				
					<input type="hidden" name="Calendar[id]" value="<?php echo $model->id; ?>">
					
					<div class="row">
						<label class="col-sm-6 control-label no-padding-right">Calendar Name</label>
						<div class="col-sm-5">
							<input type="text" class="middle" name="Calendar[name]" value="<?php echo $model->name; ?>" />
						</div>
					</div>
					
					<div class="row">
						<label class="col-sm-6 control-label no-padding-right">Staff Assignment</label>
						<div class="col-sm-5">
							<?php 
								$staffs = CustomerOfficeStaff::model()->findAll(array(
									'condition' => 'customer_office_id = :customer_office_id',
									'params' => array(
										':customer_office_id' => $model->office_id,
									),
								));
								
								$staffOptions = array();
								
								if( $staffs )
								{
									foreach( $staffs as $staff )
									{
										$staffOptions[$staff->id] = $staff->staff_name;
									}
									
								}

								echo CHtml::dropDownList('CalendarStaffAssignment[staff_id]', $calendarStaffAssignment->staff_id, $staffOptions);
							?>
						</div>
					</div>
					
					<div class="row">
						<label class="col-sm-6 control-label no-padding-right">Maximum Appointments Per Day</label>
						<div class="col-sm-5">
							<input type="text" class="middle spinner" name="Calendar[maximum_appointments_per_day]" value="<?php echo $model->maximum_appointments_per_day; ?>" readonly="" >
						</div>
					</div>
					
					<div class="row">
						<label class="col-sm-6 control-label no-padding-right">Maximum Appointments Per Week</label>
						<div class="col-sm-5">
							<input type="text" class="middle spinner" name="Calendar[maximum_appointments_per_week]" value="<?php echo $model->maximum_appointments_per_week; ?>" readonly="" >
						</div>
					</div>
					
					<div class="row">
						<label class="col-sm-6 control-label no-padding-right">Appointment Start Time</label>
						<div class="col-sm-5">
							<select class="middle" name="Calendar[appointment_start_time]">
								
								<option value=""></option>
							
								<?php 
									for ($i = 25200; $i < 72000; $i += 1800) // 25200 = 7am, 70200 = 7:30pm, 1800 = half hour, 86400 = one day
									{  									
										$selected = (date('g:i A', mktime(0, 0, 0, 1, 1) + $i) == $model->appointment_start_time) ? 'selected' : '';
										
										echo '<option value="'.date('g:i A', mktime(0, 0, 0, 1, 1) + $i).'" '.$selected.'>'.date('g:i A', mktime(0, 0, 0, 1, 1) + $i).'</option>';
									}
								?>
							</select>
						</div>
					</div>
					
					<div class="row">
						<label class="col-sm-6 control-label no-padding-right">Appointment End Time</label>
						<div class="col-sm-5">
							<select class="middle" name="Calendar[appointment_end_time]">
								
								<option value=""></option>
								
								<?php 								
									for ($i = 25200; $i < 72000; $i += 1800) // 25200 = 7am, 70200 = 7:30pm, 1800 = half hour, 86400 = one day
									{  
										$selected = (date('g:i A', mktime(0, 0, 0, 1, 1) + $i) == $model->appointment_end_time) ? 'selected' : '';
										
										echo '<option value="'.date('g:i A', mktime(0, 0, 0, 1, 1) + $i).'" '.$selected.'>'.date('g:i A', mktime(0, 0, 0, 1, 1) + $i).'</option>';
									}
								?>
							</select>
						</div>
					</div>
					
					<div class="row">
						<label class="col-sm-6 control-label no-padding-right">Appointment Length</label>
						<div class="col-sm-5">
							<?php 
								$appointmentLengthOptions = array(
									'30 Minutes' => '30 Minutes',
									'45 Minutes' => '45 Minutes',
									'1 Hour' => '1 Hour',
									'1 Hour 30 Minutes' => '1 Hour 30 Minutes',
									'2 Hours' => '2 Hours',
								);
								
								echo CHtml::dropDownList('Calendar[appointment_length]', $model->appointment_length, $appointmentLengthOptions);
							?>
						</div>
					</div>
					
					<div class="row">
						<label class="col-sm-6 control-label no-padding-right">Minimum days out</label>
						<div class="col-sm-5">
							<input type="text" class="middle spinner2" name="Calendar[minimum_days_appointment_set]" value="<?php echo $model->minimum_days_appointment_set; ?>" readonly="" >
						</div>
					</div>
					
					<div class="row">
						<label class="col-sm-6 control-label no-padding-right">Maximum days out</label>
						<div class="col-sm-5">
							<input type="text" class="middle spinner3" name="Calendar[maximum_days_appointment_set]" value="<?php echo $model->maximum_days_appointment_set; ?>" readonly="" >
						</div>
					</div>
					
					<div class="row">
						<label class="col-sm-6 control-label no-padding-right">Location</label>
						<div class="col-sm-4">
							<div class="checkbox" style="display:inline-block;">
								<label>
									<input type="hidden" name="Calendar[location_office]" value="0">
									<input type="checkbox" class="ace" name="Calendar[location_office]" value="1" <?php echo $model->location_office == 1 ? 'checked' : ''; ?>>
									<span class="lbl"> Office</span>
								</label>
							</div>
							
							<div class="checkbox" style="display:inline-block;">
								<label>
									<input type="hidden" name="Calendar[location_phone]" value="0">
									<input type="checkbox" class="ace" name="Calendar[location_phone]" value="1" <?php echo $model->location_phone == 1 ? 'checked' : ''; ?>>
									<span class="lbl"> Phone</span>
								</label>
							</div>
							
							<div class="checkbox" style="display:inline-block;">
								<label>
									<input type="hidden" name="Calendar[location_home]" value="0">
									<input type="checkbox" class="ace" name="Calendar[location_home]" value="1" <?php echo $model->location_home == 1 ? 'checked' : ''; ?>>
									<span class="lbl"> Home</span>
								</label>
							</div>
							
							<div class="checkbox" style="display:inline-block;">
								<label>
									<input type="hidden" name="Calendar[location_skype]" value="0">
									<input type="checkbox" class="ace" name="Calendar[location_skype]" value="1" <?php echo $model->location_skype == 1 ? 'checked' : ''; ?>>
									<span class="lbl"> Skype</span>
								</label>
							</div>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-xs btn-info" data-action="save"><i class="ace-icon fa fa-trash-o"></i> Save</button>
				<button type="button" class="btn btn-xs" data-dismiss="modal"><i class="ace-icon fa fa-times"></i> Cancel</button>
			</div>
		</div>
	</div>
</div>