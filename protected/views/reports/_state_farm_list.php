<?php
	$callDateTime = new DateTime($data->start_call_time, new DateTimeZone('America/New_York'));
	$callDateTime->setTimezone(new DateTimeZone('America/Denver'));	
	
	$appointmentDate = '';
	$appointmentTime = '';
	$appointmentType = '';
	
	if( isset($data->calendarAppointment) )
	{
		$appointmentDateTime = new DateTime($data->calendarAppointment->start_date, new DateTimeZone('America/New_York'));
		$appointmentDateTime->setTimezone(new DateTimeZone('America/Denver'));	
		
		$appointmentDate = $appointmentDateTime->format('m/d/Y');
		
		$appointmentTime = $appointmentDateTime->format('g:i A');
		
		
		if( $data->calendarAppointment->location == 1 )
		{
			$appointmentType = 'Office';
		}
		
		if( $data->calendarAppointment->location == 2 )
		{
			$appointmentType = 'Home';
		}
		
		if( $data->calendarAppointment->location == 3 )
		{
			$appointmentType = 'Phone';
		}
		
		if( $data->calendarAppointment->location == 4 )
		{
			$appointmentType = 'Skype';
		}
	}
?>


<?php if( $index == 0 ): ?>

<thead>
	<th>Customer First Name</th>
	<th>Customer Last Name</th>
	<th>Customer ID</th>
	<th>Customer State</th>
	<th>Lead First Name</th>
	<th>Lead Last Name</th>
	<th>Lead Phone Number</th>
	<th>Call Date</th>
	<th>Call Time</th>
	<th>Appointment Set</th>
	<th>Appointment Date</th>
	<th>Appointment Type</th>
	<th>Disposition</th>
</thead>

<?php endif; ?>

<tr>

	<td><?php echo $data->customer->firstname; ?></td>
	
	<td><?php echo $data->customer->lastname; ?></td>
	
	<td><?php echo $data->customer->account_number; ?></td>
	
	<td><?php echo State::model()->findByPk($data->customer->state)->name; ?></td>
	
	<td><?php echo $data->lead->first_name; ?></td>
	
	<td><?php echo $data->lead->last_name; ?></td>
	
	<td><?php echo $data->lead_phone_number; ?></td>
	
	<td><?php echo $callDateTime->format('m/d/Y'); ?></td>
	
	<td><?php echo $callDateTime->format('g:i A'); ?></td>
	
	<td><?php echo $appointmentDate; ?></td>
	
	<td><?php echo $appointmentTime; ?></td>
	
	<td><?php echo $appointmentType; ?></td>
	
	<td><?php echo $data->disposition; ?></td>
	
</tr>