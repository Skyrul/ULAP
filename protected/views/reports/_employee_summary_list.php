<?php 
	$totalScheduledWorkHours = 0;
		
	$startDate = strtotime('monday this week');
	$endDate = strtotime('sunday this week');	
	
	while( $startDate <= $endDate )
	{
		$daySchedules = AccountLoginSchedule::model()->findAll(array(
			'condition' => 'account_id = :account_id AND day_name = :day_name',
			'params' => array(
				':account_id' => $data->id,
				':day_name' => date('l', $startDate),
			),
		));
	
		if( $daySchedules )
		{
			foreach( $daySchedules as $daySchedule )
			{
				$subtractTime = strtotime($daySchedule->end_time) - strtotime($daySchedule->start_time);
				$hours = floor($subtractTime/3600);
				
				$minutes = round(($subtractTime%3600)/60);
				
				if( $minutes >= 30 )
				{
					$hours += .5;
				}
				
				if( $hours > 0 && $daySchedule->type == 1)
				{
					$totalScheduledWorkHours += $hours;
				}
			}
		}
		
		$startDate = strtotime('+1 day', $startDate);
	}
	
	$securityGroups = Account::listAccountType();
?>

<?php if( $index == 0 ): ?>

<thead>
	<th>Employee #</th>
	<th>Badge ID</th>
	<th>First Name</th>
	<th>Last Name</th>
	<th>Employee Classification</th>
	<th>Status</th>
	<th>Start Date</th>
	<th>Term Date</th>
	<th>Phone Extension</th>
	<th>Job Title</th>
	<th>Security Group</th>
	<th>Schedule Hours per Week</th>
</thead>

<?php endif; ?>

<tr>
	<td><?php echo CHtml::link($data->accountUser->employee_number, array('/hr/accountUser/employeeProfile', 'id'=>$data->id)); ?></td>
	
	<td><?php echo $data->accountUser->badge_id; ?></td>
	
	<td><?php echo $data->accountUser->first_name; ?></td>
	
	<td><?php echo $data->accountUser->last_name; ?></td>
	
	<td><?php echo $data->accountUser->full_time_status; ?></td>
	
	<td><?php echo $data->status == 1 ? 'ACTIVE' : 'INACTIVE'; ?></td>
	
	<td><?php echo $data->accountUser->date_hire; ?></td>
	
	<td><?php echo $data->accountUser->date_termination; ?></td>
	
	<td><?php echo $data->accountUser->phone_extension; ?></td>
	
	<td><?php echo $data->accountUser->job_title; ?></td>
	
	<td><?php echo $securityGroups[$data->account_type_id]; ?></td>
	
	<td><?php echo $totalScheduledWorkHours; ?></td>
</tr>