<?php 
	if ( $agentAccounts )
	{
		foreach( $agentAccounts as $agentAccount )
		{
			if( isset($agentAccount->accountUser) )
			{
				$stateTime = 0;
				
				$seconds = 0;
				
				$trStyle = '#CCCCCC';

				$currentLoginState = AccountLoginState::model()->find(array(
					'condition' => 'account_id = :account_id',
					'params' => array(
						':account_id' => $agentAccount->id,
					),
					'order' => 'date_created DESC',
				));
				
				if( $currentLoginState )
				{
					if( $currentLoginState->type == AccountLoginState::TYPE_AVAILABLE && $currentLoginState->end_time == null )
					{
						$trStyle = '#B0D877';
					}
					
					if( $currentLoginState->type == AccountLoginState::TYPE_NOT_AVAILABLE )
					{
						$trStyle = '#FF7777';
					}
					
					if( strtotime( $currentLoginState->date_updated ) < strtotime('-2 seconds') )
					{
						$trStyle = '#CCCCCC';
					}
					
					$time_in = strtotime($currentLoginState->start_time);
					$time_out = time();
					
					$seconds += $time_out - $time_in;
					
					if( $seconds > 0 )
					{
						 // extract hours
						$hours = floor($seconds / (60 * 60));
					 
						// extract minutes
						$divisor_for_minutes = $seconds % (60 * 60);
						$minutes = floor($divisor_for_minutes / 60);
					 
						// extract the remaining seconds
						$divisor_for_seconds = $divisor_for_minutes % 60;
						$seconds = ceil($divisor_for_seconds);

						if( strlen($hours) == 1 )
						{
							$hours = '0'.$hours;
						}
						
						if( strlen($minutes) == 1 )
						{
							$minutes = '0'.$minutes;
						}
						
						if( strlen($seconds) == 1 )
						{
							$seconds = '0'.$seconds;
						}

						$stateTime = $hours.':'.$minutes.':'.$seconds;
					}
				}
				
				$skills = array();
				
				$assignedSkills = AccountSkillAssigned::model()->findAll(array(
					'condition' => 'account_id = :account_id',
					'params' => array(
						':account_id' => $agentAccount->id,
					),
				));
				
				if( $assignedSkills )
				{
					foreach( $assignedSkills as $assignedSkill )
					{
						$skills[] = $assignedSkill->skill->skill_name;
					}
				}
				
				$scheduleStart = AccountLoginSchedule::model()->find(array(
					'condition' => 'account_id = :account_id AND day_name = :day_name AND type=1',
					'params' => array(
						':account_id' => $agentAccount->id,
						':day_name' => date('l'),
					),
					'order' => 'date_created ASC',
				));
				
				$scheduleEnd = AccountLoginSchedule::model()->find(array(
					'condition' => 'account_id = :account_id AND day_name = :day_name AND type=1',
					'params' => array(
						':account_id' => $agentAccount->id,
						':day_name' => date('l'),
					),
					'order' => 'date_created DESC',
				));
				
				?>
					<tr style="background:<?php echo $trStyle; ?>;">
							
						<td>
							<?php 
								echo $agentAccount->accountUser->first_name.' '.$agentAccount->accountUser->last_name; 
							?>							
						</td>
						
						<td>
							<?php 
								if( $currentLoginState && $trStyle != '#CCCCCC' )
								{
									if( $currentLoginState->type == AccountLoginState::TYPE_AVAILABLE )
									{
										echo 'Available';
									}
									else
									{
										
										echo 'Not Available';
									}
								}
								else
								{
									echo 'No State';
								}
							?>							
						</td>

						<td>
							<?php 
								if( $trStyle == '#B0D877' )
								{
									echo $stateTime; 
								}
							?>
						</td>
						
						<td>
							<?php 
								if( $currentLoginState && $trStyle != '#CCCCCC' )
								{
									$loginTime = new DateTime($currentLoginState->start_time, new DateTimeZone('America/New_York'));
									$loginTime->setTimezone(new DateTimeZone('America/Denver'));
									
									echo $loginTime->format('m/d/Y g:i A');
								}
							?>
						</td>
						
						<td>
							<?php 
								if( $scheduleStart && $scheduleEnd )
								{
									echo $scheduleStart->start_time.' - '.$scheduleEnd->end_time;
								}
							?>
						</td>
						
						<td><?php echo implode(', ', $skills); ?></td>
						
						<td><?php echo CHtml::link('Force Logout', 'javascript:void(0);', array('id'=>$agentAccount->id, 'class'=>'force-logout')); ?></td>
								
					</tr>
					
				<?php
			}
		}
	}
	else
	{
		echo '<tr><td colspan="2">No results found.<td></tr>';
	}
?>