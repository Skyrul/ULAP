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
	
</table>

<p></p>

<!-- START OF CALL RESULTS -->

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
					'condition' => 'disposition = :disposition AND customer_id = :customer_id AND t.status!=4 AND t.lead_id IS NOT NULL',
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
	
</table>

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
	
</table>