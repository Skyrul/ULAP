<?php
	$callDateTime = new DateTime($data->start_call_time, new DateTimeZone('America/New_York'));
	$callDateTime->setTimezone(new DateTimeZone('America/Denver'));	
?>


<?php if( $index == 0 ): ?>

<thead>
	<th>Company</th>
	<th>Customer ID</th>
	<th>Customer First Name</th>
	<th>Customer Last Name</th>
	<th>Customer State</th>
	<th>Lead First Name</th>
	<th>Lead Last Name</th>
	<th>Lead Dial Count</th>
	<th>Call Date</th>
	<th>Call Time</th>
	<th>Disposition</th>
	<th>Agent Note</th>
	<th>External Note</th>
</thead>

<?php endif; ?>

<tr>
	<td><?php echo $data->company->company_name; ?></td>
	
	<td><?php echo $data->customer->account_number; ?></td>
	
	<td><?php echo $data->customer->firstname; ?></td>
	
	<td><?php echo $data->customer->lastname; ?></td>
	
	<td><?php echo State::model()->findByPk($data->customer->state)->name; ?></td>
	
	<td><?php echo $data->lead->first_name; ?></td>
	
	<td><?php echo $data->lead->last_name; ?></td>
	
	<td><?php echo $data->dial_number; ?></td>
	
	<td><?php echo $callDateTime->format('m/d/Y'); ?></td>
	
	<td><?php echo $callDateTime->format('g:i A'); ?></td>
	
	<td><?php echo $data->disposition; ?></td>
	
	<td><?php echo $data->agent_note; ?></td>
	
	<td><?php echo $data->external_note; ?></td>
</tr>