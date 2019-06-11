
	<div id="newCalendarPanel" class="panel panel-default">
		<div class="panel-heading">
			<a class="btn btn-danger btn-minier pull-right cancel-new-calendar"><i class="fa fa-times"></i> Cancel</a>
			
			<h4 class="panel-title">
				<a href="#newCalendar" data-parent="#accordion" data-toggle="collapse" class="accordion-toggle" aria-expanded="false">
					<i data-icon-show="ace-icon fa fa-angle-right" data-icon-hide="ace-icon fa fa-angle-down" class="bigger-110 ace-icon fa fa-angle-down"></i>
					&nbsp;New Calendar
				</a>
			</h4>
		</div>

		<div id="newCalendar" class="panel-collapse collapse in" aria-expanded="true" style="">
			<div class="panel-body">

				<div class="row">
					<div class="col-sm-6">
					
						<div class="page-header">
							<h1 class="blue">Settings</h1>
						</div>
						
						<form id="form-calendar-settings" class="no-border">				
							<input type="hidden" name="Calendar[office_id]" value="<?php echo $calendar->office_id; ?>">
							<input type="hidden" name="Calendar[customer_id]" value="<?php echo $calendar->customer_id; ?>">

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
											'condition' => 'customer_office_id = :customer_office_id',
											'params' => array(
												':customer_office_id' => $calendar->office_id,
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

										echo CHtml::dropDownList('Calendar[staff_id]', $calendar->staff_id, $staffOptions, array('prompt' => '- Select -',));
									?>
								</div>
							</div>

							<div class="row">
								<label class="col-sm-6 control-label no-padding-right">Maximum Appointments Per Day</label>
								<div class="col-sm-5">
									<input type="text" name="Calendar[maximum_appointments_per_day]" class="spinner" value="<?php echo $calendar->maximum_appointments_per_day; ?>"/>
								</div>
							</div>

							<div class="row">
								<label class="col-sm-6 control-label no-padding-right">Maximum Appointments Per Week</label>
								<div class="col-sm-5">
									<input type="text" name="Calendar[maximum_appointments_per_week]" class="spinner" value="<?php echo $calendar->maximum_appointments_per_week; ?>" />
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
									<input type="text" class="spinner2" name="Calendar[minimum_days_appointment_set]" value="<?php echo $calendar->minimum_days_appointment_set; ?>" />
								</div>
							</div>

							<div class="row">
								<label class="col-sm-6 control-label no-padding-right">Maximum days out</label>
								<div class="col-sm-5">
									<input type="text" class="spinner3" name="Calendar[maximum_days_appointment_set]" value="<?php echo $calendar->maximum_days_appointment_set; ?>" />
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
							
							<div class="row">
								<label class="col-sm-6 control-label no-padding-right">Autoload Schedule</label>
								
								<div class="col-sm-5">
									<div class="radio" style="display:inline-block;">
										<label>
											<input type="radio" class="ace" name="Calendar[use_default_schedule]" value="1" <?php echo $calendar->use_default_schedule == 1 ? 'checked' : ''; ?>>
											<span class="lbl"> Default</span>
										</label>
									</div>
									
									<div class="radio" style="display:inline-block;">
										<label>
											<input type="radio" class="ace" name="Calendar[use_default_schedule]" value="0" <?php echo $calendar->use_default_schedule == 0 ? 'checked' : ''; ?>>
											<span class="lbl"> Custom</span>
										</label>
									</div>
								</div>
							</div>
							
							<div class="row form-actions">
								<div class="col-sm-12" style="text-align:center;">
									<button type="button" class="btn btn-xs btn-info save-new-calendar">Save</button>
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
										echo "<tr>";
										
											echo "<td>".$holiday["name"]."</td>";
											echo '<td><input type="checkbox" class="ace" name="holidays[]" value="'.$key.'" disabled><span class="lbl">&nbsp; Enable</span></td>';
										
										echo "</tr>";
									}
								?>
							</table>

							<div class="row form-actions" style="margin-top:35px;">
								<div class="col-sm-12" style="text-align:center;">
									<button type="button" class="btn btn-xs disabled"><i class="ace-icon fa fa-check"></i> Apply</button>
								</div>
							</div>
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
										
										foreach (Calendar::createTimeRange('7:00am', '7:00pm', '1 hour') as $time) 
										{
											$time = date('g:i A', $time);
											
											echo '<tr>';
												foreach( $days as $day )
												{									
													echo '<td>';
														echo '<input type="checkbox" class="ace" name="CalendarAppointmentSchedule['.$day.'][]" value="'.$time.'" disabled><span class="lbl">&nbsp;'.$time.'</span>';
													echo '</td>';
												}
											echo '</tr>';
										}
									?>
								</table>
								
								<div class="row form-actions" style="margin-top:46px;">
									<div class="col-sm-12" style="text-align:center;">
										<button type="button" class="btn btn-xs disabled">Apply Default</button>
										<button type="button" class="btn btn-xs disabled"><i class="ace-icon fa fa-check"></i> Apply</button>
									</div>
								</div>	
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
