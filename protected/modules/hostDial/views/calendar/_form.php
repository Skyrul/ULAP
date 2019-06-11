<div class="row">
	<div class="col-sm-6">
	
		<div class="page-header">
			<h1 class="blue">Settings</h1>
		</div>
		
		<form id="form-calendar-settings" class="no-border">				
			<input type="hidden" name="Calendar[id]" value="<?php echo $calendar->id; ?>">
			<input type="hidden" name="Calendar[customer_id]" value="<?php echo $calendar->customer_id; ?>">
			<input type="hidden" name="Calendar[office_id]" value="<?php echo $calendar->office_id; ?>">

			<div class="row">
				<label class="col-sm-6 control-label no-padding-right">Calendar Name</label>
				<div class="col-sm-5">
					<input type="text" class="middle" name="Calendar[name]" value="<?php echo $calendar->name; ?>" />
				</div>
			</div>
			
			<div class="row">
				<label class="col-sm-6 control-label no-padding-right">Staff Assignment</label>
				<div class="col-sm-5">
					<?php 
						$staffs = CustomerOfficeStaff::model()->findAll(array(
							'condition' => 'customer_office_id = :customer_office_id AND customer_id = :customer_id and is_deleted=0',
							'params' => array(
								':customer_office_id' => $calendar->office_id,
								':customer_id' => $calendar->customer_id,
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
					<input type="text" name="Calendar[maximum_appointments_per_day]" class="spinner" value="<?php echo $calendar->maximum_appointments_per_day; ?>" readonly="">
				</div>
			</div>

			<div class="row">
				<label class="col-sm-6 control-label no-padding-right">Maximum Appointments Per Week</label>
				<div class="col-sm-5">
					<input type="text" name="Calendar[maximum_appointments_per_week]" class="spinner" value="<?php echo $calendar->maximum_appointments_per_week; ?>" readonly="">
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
								$selected = (date('g:i A', mktime(0, 0, 0, 1, 1) + $i) == $calendar->appointment_start_time) ? 'selected' : '';
								
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
								$selected = (date('g:i A', mktime(0, 0, 0, 1, 1) + $i) == $calendar->appointment_end_time) ? 'selected' : '';
								
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
						
						echo CHtml::dropDownList('Calendar[appointment_length]', $calendar->appointment_length, $appointmentLengthOptions);
					?>
				</div>
			</div>

			<div class="row">
				<label class="col-sm-6 control-label no-padding-right">Minimum days out</label>
				<div class="col-sm-5">
					<input type="text" class="spinner2" name="Calendar[minimum_days_appointment_set]" value="<?php echo $calendar->minimum_days_appointment_set; ?>" readonly="">
				</div>
			</div>

			<div class="row">
				<label class="col-sm-6 control-label no-padding-right">Maximum days out</label>
				<div class="col-sm-5">
					<input type="text" class="spinner3" name="Calendar[maximum_days_appointment_set]" value="<?php echo $calendar->maximum_days_appointment_set; ?>" readonly="">
				</div>
			</div>

			<div class="row">
				<label class="col-sm-5 control-label no-padding-right">Location</label>
				<div class="col-sm-7">
					<div class="checkbox" style="display:inline-block;">
						<label>
							<input type="hidden" name="Calendar[location_office]" value="0">
							<input type="checkbox" class="ace" name="Calendar[location_office]" value="1" <?php echo $calendar->location_office == 1 ? 'checked' : ''; ?>>
							<span class="lbl"> Office</span>
						</label>
					</div>
					
					<div class="checkbox" style="display:inline-block;">
						<label>
							<input type="hidden" name="Calendar[location_phone]" value="0">
							<input type="checkbox" class="ace" name="Calendar[location_phone]" value="1" <?php echo $calendar->location_phone == 1 ? 'checked' : ''; ?>>
							<span class="lbl"> Phone</span>
						</label>
					</div>
					
					<div class="checkbox" style="display:inline-block;">
						<label>
							<input type="hidden" name="Calendar[location_home]" value="0">
							<input type="checkbox" class="ace" name="Calendar[location_home]" value="1" <?php echo $calendar->location_home == 1 ? 'checked' : ''; ?>>
							<span class="lbl"> Home</span>
						</label>
					</div>
					
					<div class="checkbox" style="display:inline-block;">
						<label>
							<input type="hidden" name="Calendar[location_skype]" value="0">
							<input type="checkbox" class="ace" name="Calendar[location_skype]" value="1" <?php echo $calendar->location_skype == 1 ? 'checked' : ''; ?>>
							<span class="lbl"> Skype</span>
						</label>
					</div>
				</div>
			</div>
		</form>
	</div>

	<div class="col-sm-5 col-sm-offset-1">
		<div class="page-header">
			<h1 class="blue">Holidays</h1>
		</div>
		
		<form id="form-holidays-settings" class="no-margin">

			<?php echo CHtml::hiddenField('CalendarCustomSchedule[calendar_id]', $calendar->id); ?>

			<table class="table table-striped table-hover table-bordered compress">
				<?php
					$holidays = new US_Federal_Holidays;
					
					foreach ($holidays->get_list() as $key => $holiday)
					{
						$existingCalendarHolidaySettings = CalendarHoliday::model()->count(array(
							'condition' => 'calendar_id = :calendar_id',
							'params' => array(
								':calendar_id' => $calendar->id,
							),
						));
					
						$existingHolidaySettings = CalendarHoliday::model()->find(array(
							'condition' => 'calendar_id = :calendar_id AND name = :name',
							'params' => array(
								':calendar_id' => $calendar->id,
								':name' => $holiday["name"],
							),
						));
						
						if( $existingCalendarHolidaySettings == 0 )
						{
							$checked = 'checked';
						}
						else
						{
							if($existingHolidaySettings)
							{
								$checked = 'checked';
							}
							else
							{
								$checked = '';
							}
						}
						
						echo "<tr>";
						
							echo "<td>".$holiday["name"]."</td>";
							echo '<td><input type="checkbox" class="ace" name="holidays[]" value="'.$key.'" '.$checked.'><span class="lbl">&nbsp; Enable</span></td>';
						
						echo "</tr>";
					}
				?>
			</table>
		</form>
	</div>
</div>

<div class="hr hr-18 hr-double dotted"></div>

<div class="row">
	<div class="col-sm-12">
		<div class="page-header">
			<h1 class="blue">Schedule</h1>
		</div>
		
		<div class="manage-schedule-wrapper">
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
									if( $calendar->isNewRecord || $calendar->use_default_schedule == 1 )
									{
										if( in_array($day, array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday')) )
										{
											if( in_array($time, array('10:00 AM', '2:00 PM', '4:00 PM')) )
											{
												$checked = 'checked';
											}
											else
											{
												$checked = '';
											}
										}
										else
										{
											$checked = '';
										}
									}
									else
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
									}
									
									
									echo '<td>';
										echo '<input type="checkbox" class="ace" name="CalendarAppointmentSchedule['.$day.'][]" value="'.$time.'" '.$checked.'><span class="lbl">&nbsp;'.$time.'</span>';
									echo '</td>';
								}
							echo '</tr>';
						}
					?>
				</table>
			</form>
		</div>
	</div>
</div>