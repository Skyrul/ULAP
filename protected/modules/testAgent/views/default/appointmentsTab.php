<div class="row">
	<div class="col-sm-12">
		
		<div id="calendar-details" class="col-sm-4">
		
			<div class="profile-user-info profile-user-info-striped">
				<div class="profile-info-row">
					<div class="profile-info-name"> Lead Name </div>

					<div class="profile-info-value">
						<span><?php echo $lead->first_name.' '.$lead->last_name; ?></span>
					</div>
				</div>
				
				<div class="profile-info-row">
					<div class="profile-info-name"> Customer's Calendar </div>

					<div class="profile-info-value">
						<span>
							<?php echo CHtml::dropDownList('Calendar[id]',$calendar->id, $calendarOptions, array('id'=>'calendar-select', 'prompt'=>'- SELECT -')); ?>
							
							<button class="btn btn-info btn-minier load-calendar-btn" type="button">Load</button>
						</span>
					</div>
				</div>
				
				<div class="profile-info-row">
					<div class="profile-info-name"> Customer Name </div>

					<div class="profile-info-value">
						<span><?php echo $customer->firstname.' '.$customer->lastname ; ?></span>
					</div>
				</div>
				
				<div class="profile-info-row">
					<div class="profile-info-name"> Office Address </div>

					<div class="profile-info-value">
						<span><?php echo $office->address; ?></span>
					</div>
				</div>
				
				<div class="profile-info-row">
					<div class="profile-info-name"> Office Phone # </div>

					<div class="profile-info-value">
						<span><?php echo $office->phone; ?></span>
					</div>
				</div>
				
				<div class="profile-info-row">
					<div class="profile-info-name"> Appointment Locations </div>

					<div class="profile-info-value">
						<span>
							<?php 
							
								$appointmentLocations = '';
							
								if( $calendar->location_office == 1 )
								{
									$appointmentLocations .= 'Office, ';
								}
								
								if( $calendar->location_phone == 1 )
								{
									$appointmentLocations .= 'Phone, ';
								}
								
								if( $calendar->location_home == 1 )
								{
									$appointmentLocations .= 'Home, ';
								}
								
								if( $calendar->location_skype == 1 )
								{
									$appointmentLocations .= 'Skype, ';
								}
								
								echo rtrim($appointmentLocations, ', ');
							?>
						</span>
					</div>
				</div>
				
				<div class="profile-info-row">
					<div class="profile-info-name"> Max appointments per week</div>

					<div class="profile-info-value">
						<span><?php echo $calendar->maximum_appointments_per_week; ?></span>
					</div>
				</div>
				
				<div class="profile-info-row">
					<div class="profile-info-name"> Max appointments per day </div>

					<div class="profile-info-value">
						<span><?php echo $calendar->maximum_appointments_per_day; ?></span>
					</div>
				</div>
				
				<div class="profile-info-row">
					<div class="profile-info-name"> Min days out</div>

					<div class="profile-info-value">
						<span><?php echo $calendar->minimum_days_appointment_set; ?></span>
					</div>
				</div>
				
				<div class="profile-info-row">
					<div class="profile-info-name"> Max days out</div>

					<div class="profile-info-value">
						<span><?php echo $calendar->maximum_days_appointment_set; ?></span>
					</div>
				</div>		

				<div class="profile-info-row">
					<div class="profile-info-name"> Customer Notes</div>

					<div class="profile-info-value">
						<span>
							<?php echo $customer->notes; ?>
						</span>
					</div>
				</div>
				
				<div class="profile-info-row">
					<div class="profile-info-name"> Office Directions</div>

					<div class="profile-info-value">
						<span>
							<?php echo $office->directions; ?>
						</span>
					</div>
				</div>
				
				<div class="profile-info-row">
					<div class="profile-info-name"> Office Landmarks</div>

					<div class="profile-info-value">
						<span>
							<?php echo $office->landmark; ?>
						</span>
					</div>
				</div>
			</div>
			
			<div class="space-12"></div>
			
			<div class="text-center">
				<a href="#" class="btn btn-info btn-xs get-map-directions">Get Directions <i class="fa fa-share"></i></a>
			</div>
		</div>
		
		<div id="calendar-wrapper" class="col-sm-8">
			<div id="calendar"></div>
		</div>
	</div>
</div>