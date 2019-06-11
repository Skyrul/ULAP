<?php
	$startDate = strtotime($data->request_date.' '.$data->start_time);
	$endDate = strtotime($data->request_date_end.' '.$data->end_time);

	$totalScheduledHours = 0;
	
	while( $startDate <= $endDate ) 
	{
		$schedules = AccountLoginSchedule::model()->findAll(array(
			'condition' => 'account_id = :account_id AND day_name = :day_name AND type=1',
			'params' => array(
				':account_id' => $data->account_id,
				':day_name' => date('l', $startDate),
			),
			'group' => 'day_name',
			'order' => 'date_created ASC',
		));
		
		
		
		if( $schedules )
		{
			foreach( $schedules as $schedule )
			{
				$startTime = date('g:i A', strtotime($schedule->start_time));
				$endTime = date('g:i A', strtotime($schedule->end_time));
				
				// echo date('l', $startDate);
				// echo '<br>';
				// echo 'request start date: ' . date('m/d/Y g:i A', strtotime($data->request_date.' '.$schedule->start_time));
				// echo '<br>';
				// echo 'request end date: ' . date('m/d/Y g:i A', strtotime($data->request_date.' '.$schedule->end_time));
				// echo '<br>';
				// echo 'schedule start date: ' . date('m/d/Y g:i A', strtotime($data->request_date.' '.$schedule->start_time));
				// echo '<br>';
				// echo 'schedule end date: ' . date('m/d/Y g:i A', strtotime($data->request_date.' '.$schedule->end_time));
				// echo '<br>';
				// echo '<hr>';
				// echo '<br>';
				
				if( strtotime($data->request_date.' '.$schedule->start_time) >= strtotime($data->request_date.' '.$schedule->start_time) && strtotime($data->request_date.' '.$schedule->end_time) <= strtotime($data->request_date.' '.$schedule->end_time) )
				{
					// $totalScheduledHours += round((strtotime($schedule->end_time) - strtotime($schedule->start_time))/3600, 1);
					$totalScheduledHours += round( (strtotime($data->end_time) - strtotime($data->start_time) )/3600, 1);
				}
			}
		}
		
		$startDate = strtotime('+1 day', $startDate);
	}
	

	if($data->status == 1)
	{
		$trClass = 'success';
		$status = 'Approved';
	}
	elseif($data->status == 2)
	{
		$trClass = 'warning';
		$status = 'For Approval';
	}
	else
	{
		$trClass = 'danger';
		$status = 'Denied';
	}
?>
		
<?php if($index == 0): ?>

<thead>
	<tr>
		<th class="center"></th>
		<th>Name</th>
		<th>Request Date/Time</th>
		<th>PTO Date</th>
		<th class="center">Hours</th>
		<th>Status</th>
	</tr>
</thead>

<?php endif; ?>

<tr class="<?php echo $trClass; ?>">
	<td>
		<input type="checkbox" class="ace pto-checkbox" value="<?php echo $data->id; ?>">
		<span class="lbl"></span>
	</td>
	
	<td><?php echo $data->name; ?></td>
	
	<td>
		<?php 
			echo date('m/d/Y g:i A', strtotime($data->request_date.' '.$data->start_time)); 
			echo ' - ';
			echo date('m/d/Y g:i A', strtotime($data->request_date_end.' '.$data->end_time)); 
		?>
	</td>
	
	<td>
		<?php 
			$ptoDate = new DateTime($data->date_created, new DateTimeZone('America/Chicago'));
			$ptoDate->setTimezone(new DateTimeZone('America/Denver'));
	
			echo $ptoDate->format('m/d/Y'); 
		?>
	</td>
	
	<td class="center">
		<?php 
			// $subtractTime = strtotime($data->request_date_end.' '.$data->end_time) - strtotime($data->request_date.' '.$data->start_time);

			// echo round($subtractTime/3600, 1);
			
			echo $totalScheduledHours;
		?>
	</td>
	
	<td><?php echo $status; ?></td>
</tr>