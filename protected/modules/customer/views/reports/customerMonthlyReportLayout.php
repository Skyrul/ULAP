<?php 
	$skills = array();
										
	$customerSkills = CustomerSkill::model()->findAll(array(
		'condition' => 'customer_id = :customer_id AND status=1',
		'params' => array(
			':customer_id' => $customer->id,
		),
	));

	if( $customerSkills )
	{
		foreach( $customerSkills as $customerSkills )
		{
			$skills[] = $customerSkills->skill->skill_name;
		}
	}
?>

<style>
	table.tbl-call-results { width:100%; border:none; }
	table.tbl-call-results td{ border:none; font-size:9px; }
	
	table.tbl-log { width:100%; border:0.3em solid #CCCCCC; }
	table.tbl-log th{ background-color:#FF9833; color:#FFFFFF; }
	table.tbl-log td { border:0.3em solid #CCCCCC; font-size:9px; padding:5px; }
	
	table.tbl-log-blue { width:100%; border:0.3em solid #CCCCCC; }
	table.tbl-log-blue th{ background-color:#0068B1; color:#FFFFFF; }
	table.tbl-log-blue td { border:0.3em solid #CCCCCC; font-size:9px; padding:5px; }
	
	table.customer-info { margin-left:100px; }
	table.customer-info td { width:50%; }
</style>

<table class="customer-info">
	<tr>
		<td align="left">Name: <?php echo strtoupper($customer->getFullName()); ?></td>
	</tr>
	
	<tr>
		<td align="left">Skill: <?php echo !empty( $skills ) ? implode(', ', $skills) : ''; ?></td>
	</tr>
	
	<tr>
		<td align="left">Report Range: <?php echo date('m/d/Y', strtotime($start_date)); ?> - <?php echo date('m/d/Y', strtotime($end_date)); ?></td>
	</tr>
	
	<?php if( date('Y-m-d') >= '2016-07-01' ): ?>
	
	<tr>
		<td align="left">
			Callable leads on the Month 1st: 
			<?php 
				$callableLead = CustomerCallableLeadCount::model()->find(array(
					'condition' => 'customer_id = :customer_id AND YEAR(date_created) = :report_year AND MONTH(date_created) = :report_month',
					'params' => array(
						':customer_id' => $customer->id,
						':report_year' => date('Y', strtotime($start_date)),
						':report_month' => date('m', strtotime($start_date))
					),
				));
				
				if( $callableLead )
				{
					echo $callableLead->callable_leads;
				}
				else
				{
					echo 0;
				}
			?>
		</td>
	</tr>
	
	<?php endif; ?>
	
</table>

<p></p>

<!-- START OF CALL RESULTS -->

<table style="width:100%; border:none;">
	<tr>
		<th width="50%" style="font-size:14px;"><b>Appointments Set</b></th>
		<th align="center" style="font-size:14px;"><b><?php echo count($appointments); ?></b></th>
	</tr>
</table>

<p></p>

<!-- START OF APPOINTMENT LOG -->

<table class="tbl-log">
	<tr>
		<th><b>Appointments</b></th>
		<th></th>
	</tr>

	<?php 
		if( $appointments )
		{
			foreach( $appointments as $appointment )
			{
				$appointmentDateTime = new DateTime($appointment['start_date'], new DateTimeZone('America/Chicago'));
				// $appointmentDateTime->setTimezone(new DateTimeZone('America/Denver'));	
				
				echo '<tr>';
					echo '<td>'.$appointment['last_name'].', '.$appointment['first_name'].'</td>';
					echo '<td>'.$appointmentDateTime->format('m/d/Y g:i A').'</td>';
				echo '</tr>';
			}
		}
		else
		{
			echo '<tr><td colspan="2">No result found.</td></tr>';
		}
	?>
	
	<?php /*<tr>
		<td>Jones, Tom</td>
		<td>12/4/2015 2:00pm</td>
	</tr>
	
	<tr>
		<td>Smith, Mary</td>
		<td>12/6/2015 10:30am</td>
	</tr>
	
	<tr>
		<td>Doe, John</td>
		<td>12/8/2015 9:00am</td>
	</tr>
	
	<tr>
		<td>Docker, Ann</td>
		<td>12/14/2015 3:00pm</td>
	</tr>*/ ?>

</table>

<p></p>

<!-- START OF CONFLICTS -->

<table class="tbl-log">
	<tr>
		<th><b>Conflicts (Unresolved)</b></th>
		<th></th>
	</tr>
	
	<?php 
		if( $conflicts )
		{
			foreach( $conflicts as $conflict )
			{
				if( $conflict->title == 'LOCATION CONFLICT' )
				{
					$conflictType = 'Location';
				}
				else
				{
					$conflictType = 'Schedule';
				}
				
				echo '<tr>';
					
					echo '<td>'.$conflict->lead->last_name.', '.$conflict->lead->first_name.'</td>';
					
					echo '<td>'.$conflictType.'</td>';

				echo '</tr>';
			}
		}
		else
		{
			echo '<tr><td colspan="2">No result found.</td></tr>';
		}
	?>
	
	<?php /*<tr>
		<td>Jones, Tom</td>
		<td align="center">Location</td>
	</tr>
	
	<tr>
		<td>Jane, Mary</td>
		<td align="center">Location</td>
	</tr>
	
	<tr>
		<td>Pitt, Brad</td>
		<td align="center">Schedule</td>
	</tr>*/ ?>

</table>

<p></p>

<table class="tbl-log-blue">
	<tr>
		<th><b>Dispositions</b></th>
		<th></th>
	</tr>
	
	<?php 
		if( $dispositions )
		{
			foreach( $dispositions as $disposition )
			{
				$dispositionCount = LeadCallHistory::model()->count(array(
					'condition' => 'disposition = :disposition AND customer_id = :customer_id AND t.status!=4 AND t.lead_id IS NOT NULL AND t.date_created >= "'.$start_date.'" AND t.date_created <= "'.$end_date.'"',
					'params' => array(
						':disposition' => $disposition,
						':customer_id' => $customer->id,
					),
				));
				
				echo '<tr>';
					echo '<td>'.$disposition.'</td>';
					echo '<td>'.$dispositionCount.'</td>';
				echo '</tr>';
			}
		}
		else
		{
			echo '<tr><td colspan="2">No result found.</td></tr>';
		}
	?>
	
	<?php /*<tr>
		<td>Answering Machine</td>
		<td align="center">81</td>
	</tr>
	<tr>
		<td>Answering Machine-Left Message</td>
		<td align="center">70</td>
	</tr>
	
	<tr>
		<td>Appointment Set**</td>
		<td align="center">14</td>
	</tr>*/ ?>
	
</table>

<p style="font-size:7px; font-style:italic; "> 
	** Appointment count under Disposition listing could be higher than Appointments Set count above. The Appointment Set count removes canceled appointments reported to
Engagex that fall within our cancelation policy.
</p>

<p></p>

<!-- START OF CALL LOG -->

<table class="tbl-log-blue">
	<tr>
		<th><b>Call History</b></th>
		<th></th>
		<th></th>
	</tr>
	
	<?php 
		if( $calls )
		{
			foreach( $calls as $call )
			{
				$valid = true;
				
				if( $call->is_skill_child == 0 )
				{
					if( $call->skillDisposition->is_visible_on_report == 1)
					{
				
						$valid = true;
					}
					else
					{
						$valid = false;
					}
				}
				
				if( $valid )
				{
					echo '<tr>';
					
						$callTime = new DateTime($call->start_call_time, new DateTimeZone('America/Chicago'));
						$callTime->setTimezone(new DateTimeZone('America/Denver'));	

						echo '<td>'.$call->lead->last_name.', '.$call->lead->first_name.'</td>';
						
						echo '<td>';
						
							if( $call->is_skill_child == 0 )
							{
								if( isset( $call->skillDisposition) )
								{
									echo $call->skillDisposition->skill_disposition_name;
								}
							}
							else
							{
								if( $call->skillChildDisposition )
								{
									echo $call->skillChildDisposition->skill_child_disposition_name;
								}
							}
						
						echo '</td>';
						
						echo '<td>'.$callTime->format('m/d/Y g:i A').'</td>';
					
					echo '</tr>';
				}
			}
		}
		else
		{
			echo '<tr><td colspan="3">No result found.</td></tr>';
		}
	?>	
	
	<?php /*<tr>
		<td>Smith, George</td>
		<td>Answering Machine</td>
		<td>11/15/2015 2:32pm</td>
	</tr>
	
	<tr>
		<td>Smith, George</td>
		<td>Appointment</td>
		<td>11/16/2015 10:20am</td>
	</tr>
	
	<tr>
		<td>Mitchell, Brad</td>
		<td>No Answer</td>
		<td>11/17/2015 1:31pm</td>
	</tr>
	
	<tr>
		<td>Lennoex, Annie</td>
		<td>Do Not Call</td>
		<td>11/18/2015 11:47am</td>
	</tr>*/ ?>
	
</table>