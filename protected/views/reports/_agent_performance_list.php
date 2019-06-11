<?php 
	$primaryVoiceContacts = 0;
	$primaryAppointments = 0;
	$primaryConversionRate = 0;
	$primaryTotalDialsPerHour = 0;
	$primaryAppointmentsPerHour = 0;
	
	$primaryCallHours = 0;
	$primaryCallMinutes = 0;
	
	$primaryOutboundDials = 0;
	
	$clockedHours = $data->getTotalLoginHours($dateFilterStart, $dateFilterEnd);

	$teamMember = TeamMember::model()->find(array(
		'condition' => 'account_id',
		'params' => array(
			':account_id' => $data->id,
		),
	));

	if( $teamMember )
	{
		$team = $teamMember->team->name;
	}
	else
	{
		$team = '';	
	}
	
	if( !empty($_POST['skillIds']) )
	{
		$primaryCalls = LeadCallHistory::model()->findAll(array(
			'with' => array('list'),
			'condition' => '
				t.agent_account_id = :agent_account_id 
				AND t.start_call_time >= :dateFilterStart 
				AND t.start_call_time <= :dateFilterEnd 
				AND t.end_call_time > t.start_call_time
				AND list.skill_id IN ('.implode(', ', $_POST['skillIds']).')
				AND t.is_skill_child=0
			',
			'params' => array(
				':agent_account_id' => $data->id,
				':dateFilterStart' => date('Y-m-d 00:00:00', strtotime($dateFilterStart)),
				':dateFilterEnd' => date('Y-m-d 23:59:59', strtotime($dateFilterEnd)),
			),
		));
	
		$primaryOutboundDials = count($primaryCalls);
		
		$primaryVoiceContacts = LeadCallHistory::model()->count(array(
			'with' => array('list','skillDisposition'),
			'condition' => '
				t.agent_account_id = :agent_account_id 
				AND t.start_call_time >= :dateFilterStart 
				AND t.start_call_time <= :dateFilterEnd
				AND t.is_skill_child=0				
				AND skillDisposition.id IS NOT NULL
				AND skillDisposition.is_voice_contact=1
				AND list.skill_id IN ('.implode(', ', $_POST['skillIds']).')
			',
			'params' => array(
				':agent_account_id' => $data->id,
				':dateFilterStart' => date('Y-m-d 00:00:00', strtotime($dateFilterStart)),
				':dateFilterEnd' => date('Y-m-d 23:59:59', strtotime($dateFilterEnd)),
			),
		));
		
		$primaryAppointments = LeadCallHistory::model()->count(array(
			'with' => array('list','skillDisposition'),
			'condition' => '
				t.agent_account_id = :agent_account_id 
				AND t.start_call_time >= :dateFilterStart 
				AND t.start_call_time <= :dateFilterEnd
				AND t.is_skill_child=0
				AND skillDisposition.id IS NOT NULL
				AND skillDisposition.is_appointment_set=1
				AND list.skill_id IN ('.implode(', ', $_POST['skillIds']).')
			',
			'params' => array(
				':agent_account_id' => $data->id,
				':dateFilterStart' => date('Y-m-d 00:00:00', strtotime($dateFilterStart)),
				':dateFilterEnd' => date('Y-m-d 23:59:59', strtotime($dateFilterEnd)),
			),
		));
	}
	
	if( $primaryOutboundDials > 0 && $clockedHours > 0 )
	{
		$primaryTotalDialsPerHour = $primaryOutboundDials / $clockedHours;
	}
	
	if( $primaryAppointments > 0 && $clockedHours > 0 )
	{
		$primaryAppointmentsPerHour = $primaryAppointments / $clockedHours;
	}
	
	if( $primaryAppointments > 0 && $primaryVoiceContacts > 0 )
	{
		$primaryConversionRate = $primaryAppointments / $primaryVoiceContacts;
	}
	
	$primarySkillWrapTimes = LeadCallWrapTime::model()->findAll(array(
		'condition' => '
			agent_account_id = :agent_account_id 
			AND start_time >= :dateFilterStart 
			AND start_time <= :dateFilterEnd
			AND end_time > start_time
			AND DATE(start_time) = DATE(end_time)
			AND call_type NOT IN (3,6) 
			AND main_skill_id IN ('.implode(', ', $_POST['skillIds']).')
		',
		'params' => array(
			':agent_account_id' => $data->id,
			':dateFilterStart' => date('Y-m-d 00:00:00', strtotime($dateFilterStart)),
			':dateFilterEnd' => date('Y-m-d 23:59:59', strtotime($dateFilterEnd)),
		),
	));
	
	$primarySkillWrapTimeMinutes = 0;
	
	if( $primarySkillWrapTimes )
	{
		foreach( $primarySkillWrapTimes as $primarySkillWrapTime )
		{
			$primarySkillWrapTimeMinutes += round( (strtotime($primarySkillWrapTime->end_time) - strtotime($primarySkillWrapTime->start_time)) / 60,2);
		}
	}
	
	$primaryHours = LeadCallWrapTime::model()->findAll(array(
		'condition' => '
			agent_account_id = :agent_account_id 
			AND start_time >= :dateFilterStart 
			AND start_time <= :dateFilterEnd
			AND end_time > start_time
			AND DATE(start_time) = DATE(end_time)
			AND call_type NOT IN (3,6) 
			AND main_skill_id IN ('.implode(', ', $_POST['skillIds']).')
		',
		'params' => array(
			':agent_account_id' => $data->id,
			':dateFilterStart' => date('Y-m-d 00:00:00', strtotime($dateFilterStart)),
			':dateFilterEnd' => date('Y-m-d 23:59:59', strtotime($dateFilterEnd)),
		),
		'group' => 'lead_id',
		'order' => 'start_time ASC',
	));
	
	if( $primaryHours )
	{
		foreach( $primaryHours as $primaryHour )
		{
			$primaryHourEnd = LeadCallWrapTime::model()->find(array(
				'condition' => '
					id != :id
					AND agent_account_id = :agent_account_id 
					AND lead_id = :lead_id 
					AND start_time >= :dateFilterStart 
					AND start_time <= :dateFilterEnd
					AND end_time > start_time
					AND DATE(start_time) = DATE(end_time)
				',
				'params' => array(
					':id' => $primaryHour->id,
					':agent_account_id' => $data->id,
					':lead_id' => $primaryHour->lead_id,
					':dateFilterStart' => date('Y-m-d H:i:s', strtotime($primaryHour->start_time)),
					':dateFilterEnd' => date('Y-m-d H:i:s', strtotime('+1 hour', strtotime($primaryHour->start_time))),
				),
				'order' => 'end_time DESC',
			));
			
			if( $primaryHourEnd )
			{
				$primaryCallMinutes += round( (strtotime($primaryHourEnd->end_time) - strtotime($primaryHour->start_time)) / 60,2);
			}
		}

		$primaryCallHours =  floor($primaryCallMinutes/60);
		$primaryCallMinutes =   $primaryCallMinutes % 60;
	}

	
	//start geting child skill values
	
	$childVoiceContacts = 0;
	$childAppointments = 0;
	$childConversionRate = 0;
	$childTotalDialsPerHour = 0;
	$childAppointmentsPerHour = 0;
	
	$childCallHours = 0;
	$childCallMinutes = 0;
	
	$childOutboundDials = 0;
	
	if( !empty($_POST['skillIds']) )
	{
		$childCalls = LeadCallHistory::model()->findAll(array(
			'with' => array('list'),
			'condition' => '
				t.agent_account_id = :agent_account_id 
				AND t.start_call_time >= :dateFilterStart 
				AND t.start_call_time <= :dateFilterEnd 
				AND t.end_call_time > t.start_call_time
				AND list.skill_id IN ('.implode(', ', $_POST['skillIds']).')
				AND t.is_skill_child=1
			',
			'params' => array(
				':agent_account_id' => $data->id,
				':dateFilterStart' => date('Y-m-d 00:00:00', strtotime($dateFilterStart)),
				':dateFilterEnd' => date('Y-m-d 23:59:59', strtotime($dateFilterEnd)),
			),
		));
		
		$childOutboundDials = count($childCalls);
		
		$childVoiceContacts = LeadCallHistory::model()->count(array(
			'with' => array('list','skillChildDisposition'),
			'condition' => '
				t.agent_account_id = :agent_account_id 
				AND t.start_call_time >= :dateFilterStart 
				AND t.start_call_time <= :dateFilterEnd
				AND t.is_skill_child=1				
				AND skillChildDisposition.id IS NOT NULL
				AND skillChildDisposition.is_voice_contact=1
				AND list.skill_id IN ('.implode(', ', $_POST['skillIds']).')
			',
			'params' => array(
				':agent_account_id' => $data->id,
				':dateFilterStart' => date('Y-m-d 00:00:00', strtotime($dateFilterStart)),
				':dateFilterEnd' => date('Y-m-d 23:59:59', strtotime($dateFilterEnd)),
			),
		));
	}
	
	
	if( $childOutboundDials > 0 && $childCallHours > 0 )
	{
		$childTotalDialsPerHour = $childOutboundDials / $childCallHours;
	}
	
	$childSkilWrapTimes = LeadCallWrapTime::model()->findAll(array(
		'condition' => '
			agent_account_id = :agent_account_id 
			AND start_time >= :dateFilterStart 
			AND start_time <= :dateFilterEnd
			AND end_time > start_time
			AND DATE(start_time) = DATE(end_time)
			AND call_type IN (3,6) 
			AND main_skill_id IN ('.implode(', ', $_POST['skillIds']).')
		',
		'params' => array(
			':agent_account_id' => $data->id,
			':dateFilterStart' => date('Y-m-d 00:00:00', strtotime($dateFilterStart)),
			':dateFilterEnd' => date('Y-m-d 23:59:59', strtotime($dateFilterEnd)),
		),
	));
	

	$childSkillWrapTimeMinutes = 0;
	
	if( $childSkilWrapTimes )
	{
		foreach( $childSkilWrapTimes as $childSkilWrapTime )
		{
			$childSkillWrapTimeMinutes += round( (strtotime($childSkilWrapTime->end_time) - strtotime($childSkilWrapTime->start_time)) / 60,2);
		}
	}
	
	$childHours = LeadCallWrapTime::model()->findAll(array(
		'condition' => '
			agent_account_id = :agent_account_id 
			AND start_time >= :dateFilterStart 
			AND start_time <= :dateFilterEnd
			AND end_time > start_time
			AND DATE(start_time) = DATE(end_time)
			AND call_type IN (3,6) 
			AND main_skill_id IN ('.implode(', ', $_POST['skillIds']).')
		',
		'params' => array(
			':agent_account_id' => $data->id,
			':dateFilterStart' => date('Y-m-d 00:00:00', strtotime($dateFilterStart)),
			':dateFilterEnd' => date('Y-m-d 23:59:59', strtotime($dateFilterEnd)),
		),
		'group' => 'lead_id',
		'order' => 'start_time ASC',
	));
	
	if( $childHours )
	{
		foreach( $childHours as $childHour )
		{
			$childHourEnd = LeadCallWrapTime::model()->find(array(
				'condition' => '
					id != :id
					AND agent_account_id = :agent_account_id 
					AND lead_id = :lead_id 
					AND start_time >= :dateFilterStart 
					AND start_time <= :dateFilterEnd
					AND end_time > start_time
					AND DATE(start_time) = DATE(end_time)
				',
				'params' => array(
					':id' => $childHour->id,
					':agent_account_id' => $data->id,
					':lead_id' => $childHour->lead_id,
					':dateFilterStart' => date('Y-m-d H:i:s', strtotime($childHour->start_time)),
					':dateFilterEnd' => date('Y-m-d H:i:s', strtotime('+1 hour', strtotime($childHour->start_time))),
				),
				'order' => 'end_time DESC',
			));
			
			if( $childHourEnd )
			{
				$childCallMinutes += round( (strtotime($childHourEnd->end_time) - strtotime($childHour->start_time)) / 60,2);
			}
		}

		$childCallHours =  floor($childCallMinutes/60);
		$childCallMinutes =   $childCallMinutes % 60;
	}
?>

<?php if( $index == 0 ): ?>

<thead>
	<th>Team</th>
	<th>Agent First/Last Name</th>
	<th>Total Hours</th>
	<th class="warning">Primary Hours</th>
	<th class="warning">Wrap Time</th>
	<th class="warning">Outbound Dials</th>
	<th class="warning">Voice Contact Dispositions</th>
	<th class="warning">Appointments</th>
	<th class="warning">Total dials per hour</th>
	<th class="warning">Appointments/hour</th>
	<th class="warning">Conversion rate</th>
	<th class="info">Child Skill Hours</th>
	<th class="info">Wrap Time</th>
	<th class="info">Outbound Dials</th>
	<th class="info">Voice Contact Dispositions</th>
	<th class="info">Total dials per hour</th>
</thead>

<?php endif; ?>


<?php if( $primaryOutboundDials > 0 || $childOutboundDials > 0 ): ?>

<tr>
	<td><?php echo $team; ?></td>
	<td><?php echo $data->accountUser->first_name.' '.$data->accountUser->last_name; ?></td>
	<td><?php echo $clockedHours; ?></td>
	<td class="warning">
		<?php 
		
			if( strlen($primaryCallHours) == 1)
			{
				$primaryCallHours = '0'.$primaryCallHours;
			}
			
			if( strlen($primaryCallMinutes) == 1)
			{
				$primaryCallMinutes = '0'.$primaryCallMinutes;
			}
			
			echo $primaryCallHours.':'.$primaryCallMinutes;
		?>
	</td>
	<td class="warning">
		<?php 
			$primarySkillWrapTimeHours =  floor($primarySkillWrapTimeMinutes/60);
			$primarySkillWrapTimeMinutes =   $primarySkillWrapTimeMinutes % 60;
			
			if( strlen($primarySkillWrapTimeHours) == 1)
			{
				$primarySkillWrapTimeHours = '0'.$primarySkillWrapTimeHours;
			}
			
			if( strlen($primarySkillWrapTimeMinutes) == 1)
			{
				$primarySkillWrapTimeMinutes = '0'.$primarySkillWrapTimeMinutes;
			}
			
			echo $primarySkillWrapTimeHours.':'.$primarySkillWrapTimeMinutes;
		?>
	</td>
	<td class="warning"><?php echo $primaryOutboundDials; ?></td>
	<td class="warning"><?php echo $primaryVoiceContacts; ?></td>
	<td class="warning"><?php echo round($primaryAppointments, 2); ?></td>
	<td class="warning"><?php echo round($primaryTotalDialsPerHour, 1); ?></td>
	<td class="warning"><?php echo round($primaryAppointmentsPerHour, 1); ?></td>
	<td class="warning"><?php echo (round($primaryConversionRate, 2) * 100).'%'; ?></td>
	<td class="info">
		<?php 
			if( strlen($childCallHours) == 1)
			{
				$childCallHours = '0'.$childCallHours;
			}
			
			if( strlen($childCallMinutes) == 1)
			{
				$childCallMinutes = '0'.$childCallMinutes;
			}
			
			echo $childCallHours.':'.$childCallMinutes;
		?>
	</td>
	<td class="info">
		<?php 
			$childSkillWrapTimeHours =  floor($childSkillWrapTimeMinutes/60);
			$childSkillWrapTimeMinutes =   $childSkillWrapTimeMinutes % 60;
			
			if( strlen($childSkillWrapTimeHours) == 1)
			{
				$childSkillWrapTimeHours = '0'.$childSkillWrapTimeHours;
			}
			
			if( strlen($childSkillWrapTimeMinutes) == 1)
			{
				$childSkillWrapTimeMinutes = '0'.$childSkillWrapTimeMinutes;
			}
			
			echo $childSkillWrapTimeHours.':'.$childSkillWrapTimeMinutes;
			
		?>
	</td>
	<td class="info"><?php echo $childOutboundDials; ?></td>
	<td class="info"><?php echo $childVoiceContacts; ?></td>
	<td class="info"><?php echo round($childTotalDialsPerHour, 1); ?></td>
</tr>

<?php endif; ?>