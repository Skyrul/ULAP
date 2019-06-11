<?php 

	$hours = 0;
	$aph = 0;
	$dph = 0;

	$skillCallRecords = LeadCallHistory::model()->findAll(array(
		'with' => array('list'),
		'together' => 'true',
		'condition' => 't.agent_account_id = :agent_account_id AND list.skill_id = :skill_id',
		'params' => array(
			':agent_account_id' => $account->id,
			':skill_id' => $data->list->skill_id,
		),
	));
	
	if( $skillCallRecords )
	{
		$seconds = 0;
		
		foreach( $skillCallRecords as $skillCallRecord )
		{
			$startTime = strtotime($skillCallRecord->start_call_time);
			$endTime = strtotime($skillCallRecord->end_call_time);
			
			$seconds += $endTime - $startTime;
		}
		
		if( $seconds > 0 )
		{
			 // extract hours
			$hours = $seconds / 3600;
		}
	}
	
	$appointments = LeadCallHistory::model()->count(array(
		'with' => array('list'),
		'together' => 'true',
		'condition' => 't.agent_account_id = :agent_account_id AND calendar_appointment_id IS NOT NULL AND list.skill_id = :skill_id',
		'params' => array(
			':agent_account_id' => $account->id,
			':skill_id' => $data->list->skill_id,
		),
	));
	
	if( $appointments > 0 && $hours > 0 )
	{
		$aph = $hours / $appointments;
	}
	
	if( count($skillCallRecords) > 0 && $hours > 0 )
	{
		$dph = $hours / count($skillCallRecords);
	}
?>

<?php if( $index == 0 ): ?>

<thead>
	<th>Skill Name</th>
	<th>Hours</th>
	<th>APH</th>
	<th>DPH</th>
</thead>

<?php endif; ?>

<tr>

	<td><?php echo $data->list->skill->skill_name; ?></td>
	
	<td><?php echo number_format($hours, 2); ?></td>
	
	<td><?php echo number_format($aph, 2); ?></td>
	
	<td><?php echo number_format($dph, 2); ?></td>
	
</tr>