<?php
	$existingPtoRequest = AccountPtoRequest::model()->find(array(
		'condition' => 'STR_TO_DATE(request_date, "%m/%d/%Y") = :loginDate AND status=1',
		'params' => array(
			':loginDate' => date('Y-m-d', strtotime($data->date_created)),
		),
	));
				
	$timeIn = new DateTime($data->time_in, new DateTimeZone('America/Chicago'));
	$timeIn->setTimezone(new DateTimeZone('America/Denver'));	

	// if( $data->type == 1)
	// {
		$timeOut = new DateTime($data->time_out, new DateTimeZone('America/Chicago'));
		$timeOut->setTimezone(new DateTimeZone('America/Denver'));	
	// }
	// else
	// {
		// $timeOut = new DateTime($data->time_out);
	// }	
	
	
	$interval = $timeIn->diff($timeOut);
	
	$totalTime = '00:00';
	
	if( $data->time_out != null )
	{
		// $totalTime = $interval->format('%H') .':'. $interval->format('%I') .':'. $interval->format('%S');
		
		$time_in = strtotime($data->time_in);
		$time_out = strtotime($data->time_out);
		
		$diff_seconds = $time_out - $time_in;
		
		$diff_hours = floor($diff_seconds/3600);
		
		if( strlen($diff_hours) == 1 )
		{
			$diff_hours = '0'.$diff_hours;
		}
		
		$diff_minutes = round(($diff_seconds%3600)/60);
		
		if( $diff_minutes == 0 && $diff_seconds > 0 )
		{
			$diff_minutes = 1;
		}
		
		if( strlen($diff_minutes) == 1 )
		{
			$diff_minutes = '0'.$diff_minutes;
		}
		
		$totalTime = $diff_hours.':'.$diff_minutes;
	}
	
	
	
	if($data->status == 1)
	{
		$status = 'Approved';
		$trClass = 'success';
	}
	elseif($data->status == 2)
	{
		$trClass = 'danger';
		
		// if( $data->employee_note != null )
		// {
			$status = 'For Approval';
		// }
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
		<th></th>
		<th>Date</th>
		<th>Time In</th>
		<th>Time Out</th>
		<th>Status</th>
		<th>Employee Note</th>
		<th>Supervisor Note</th>
		<th>Total time</th>
		<th width="25%"></th>
	</tr>
</thead>

<?php endif; ?>

<tr class="<?php echo $trClass; ?>">
	<td>
		<input type="checkbox" class="ace pay-period-checkbox" value="<?php echo $data->id; ?>">
		<span class="lbl"></span>
	</td>
	
	<td><?php echo $timeIn->format('m/d/Y'); ?></td>
	
	<td><?php echo $timeIn->format('g:i A'); ?></td>
	
	<td><?php echo $data->time_out != null ? $timeOut->format('g:i A') : ''; ?></td>
	
	<td><?php echo $status; ?></td>
	
	<td><?php echo $data->employee_note; ?></td>
	
	<td><?php echo $data->note; ?></td>
	
	<td><?php echo $totalTime; ?></td>
	
	<td>
		<?php if( Yii::app()->user->account->checkPermission('employees_time_keeping_edit_button','visible') ){ ?>
			
			<button type="button" id="<?php echo $data->id; ?>" class="btn btn-primary btn-minier edit-variance-btn">Edit <i class="fa fa-pencil"></i></button>
			
		<?php } ?>
		
		<?php if( $data->status != 1 && Yii::app()->user->account->checkPermission('employees_time_keeping_action_button','visible') ): ?>
		
			<button type="button" id="<?php echo $data->id; ?>" class="btn btn-primary btn-minier variance-action-btn">Action <i class="fa fa-arrow-right"></i></button>
			
		<?php endif; ?>
	</td>
</tr>