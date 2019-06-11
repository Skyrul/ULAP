<table class="table table-striped table-hover table-bordered compress lead-history-table">
	<thead>
		<tr>
			<th>Date/Time (lead local)</th>
			<th>Appointment Date/Time</th>
			<th>Phone Number</th>
			<th>Agent Name</th>
			<th>Disposition</th>
			<th>Dial #</th>
			<th>Agent Note</th>
			
			<?php if( !in_array(Yii::app()->user->account->account_type_id, array(Account::TYPE_PORTAL, Account::TYPE_CUSTOMER)) ): ?>
			
			<th></th>
			
			<?php endif; ?>
		</tr>
	</thead>
	<?php
		if( $leadHistories )
		{
			$ctr = 1;
			
			foreach( $leadHistories as $leadHistory )
			{
				if( !empty($leadHistory->lead->timezone) )
				{
					$timeZone = $leadHistory->lead->timezone;
				}
				else
				{
					$timeZone = $leadHistory->lead->customer->getTimeZone();
				}
				
				$timeZone = timezone_name_from_abbr($timeZone);
				
			?>
				<tr>
					<td>
						<?php 
							$date = new DateTime($leadHistory->date_created, new DateTimeZone('America/Chicago'));

							$date->setTimezone(new DateTimeZone($timeZone));

							echo $date->format('m/d/Y g:i A'); 
						?>
					</td>
					
					<td>
						<?php
							if( isset($leadHistory->calendarAppointment) )
							{
								echo date('m/d/Y G i:a', strtotime($leadHistory->calendarAppointment->start_date));
							}
						?>
					</td>
					
					<td><?php echo $leadHistory->lead_phone_number != '' ? "(".substr($leadHistory->lead_phone_number, 0, 3).") ".substr($leadHistory->lead_phone_number, 3, 3)."-".substr($leadHistory->lead_phone_number,6) : ''; ?></td>
					
					<td>
						<?php 
						
							// if(
								// $leadHistory->disposition != 'Appointment Confirmed'
								// && $leadHistory->disposition != 'SCHEDULE CONFLICT - Approved'
								// && $leadHistory->disposition != 'SCHEDULE CONFLICT - Denied'
								// && $leadHistory->disposition != 'LOCATION CONFLICT - Approved'
								// && $leadHistory->disposition != 'LOCATION CONFLICT - Denied'
								// && $leadHistory->type != 4
								// && $leadHistory->type != 5
								
							// )
						
							if( $leadHistory->type == 2 || strtoupper($leadHistory->disposition) == 'APPOINTMENT SET' || strtoupper($leadHistory->disposition) == 'APPOINTMENT SET - PENDING' )
							{
								echo isset($leadHistory->agentAccount) ? $leadHistory->agentAccount->getFullName() : '';
							}
							
							if( strtoupper($leadHistory->disposition) == 'NO SHOW RESCHEDULE' || strtoupper($leadHistory->disposition) == 'NO SHOW RESCHEDULE - PENDING' )
							{
								echo isset($leadHistory->account) ? $leadHistory->account->getFullName() : '';
							}
						?>
					</td>
					
					<td>
						<?php
							$dispositionTxt = $leadHistory->disposition; 
							
							/* if( isset($leadHistory->calendarAppointment) && ($leadHistory->calendarAppointment->title == 'SCHEDULE CONFLICT' || $leadHistory->calendarAppointment->title == 'LOCATION CONFLICT') )
							{
								if( isset($leadHistory->calendarAppointment) )
								{
									if( $leadHistory->calendarAppointment->status == CalendarAppointment::STATUS_PENDING )
									{
										$dispositionTxt .= ' - Pending';
									}
									
									if( $leadHistory->calendarAppointment->status == CalendarAppointment::STATUS_APPROVED )
									{
										$dispositionTxt .= ' - Approved';
									}
									
									if( $leadHistory->calendarAppointment->status == CalendarAppointment::STATUS_DELETED )
									{
										$dispositionTxt .= ' - Denied';
									}
									
									if( $leadHistory->calendarAppointment->status == CalendarAppointment::STATUS_SUGGEST )
									{
										$dispositionTxt .= ' - Alt Suggested';
									}
								}
							}if( isset($leadHistory->calendarAppointment) && ($leadHistory->calendarAppointment->title == 'SCHEDULE CONFLICT' || $leadHistory->calendarAppointment->title == 'LOCATION CONFLICT') )
							{
								if( isset($leadHistory->calendarAppointment) )
								{
									if( $leadHistory->calendarAppointment->status == CalendarAppointment::STATUS_PENDING )
									{
										$dispositionTxt .= ' - Pending';
									}
									
									if( $leadHistory->calendarAppointment->status == CalendarAppointment::STATUS_APPROVED )
									{
										$dispositionTxt .= ' - Approved';
									}
									
									if( $leadHistory->calendarAppointment->status == CalendarAppointment::STATUS_DELETED )
									{
										$dispositionTxt .= ' - Denied';
									}
									
									if( $leadHistory->calendarAppointment->status == CalendarAppointment::STATUS_SUGGEST )
									{
										$dispositionTxt .= ' - Alt Suggested';
									}
								}
							} */ 

							echo $dispositionTxt;

							if( !empty($leadHistory->account_id) )
							{
								echo '<br> By: <b><i>'.$leadHistory->account->getFullName().'</i></b>';
							}
						?>
					</td>
					
					<td>
						<?php 
							echo $leadHistory->type == 4 ? '0' : $ctr; 
						?>
					</td>
					
					<td>
						<?php 
							if( $leadHistory->type == 4 )
							{
								echo 'Lead Import';
							}
							
							/*
							if(isset($leadHistory->calendarAppointment))
							{
								if(
									$leadHistory->disposition != 'Schedule Conflict - Pending' 
									&& $leadHistory->disposition != 'SCHEDULE CONFLICT - Approved' 
									&& $leadHistory->disposition != 'SCHEDULE CONFLICT - Denied' 
									&& $leadHistory->disposition != 'LOCATION CONFLICT - Pending' 
									&& $leadHistory->disposition != 'LOCATION CONFLICT - Approved' 
									&& $leadHistory->disposition != 'LOCATION CONFLICT - Denied' 
									&& $leadHistory->disposition != 'RESCHEDULE APPOINTMENT' 
								)
									echo '<p>'.$leadHistory->note.'</p>';
							}
							else
								echo '<p>'.$leadHistory->note.'</p>';
							
							if(isset($leadHistory->calendarAppointment) && !empty($leadHistory->calendarAppointment->agent_notes) )
							{
								
								if($leadHistory->disposition != 'Appointment Confirmed'
									&& $leadHistory->disposition != 'APPOINTMENT SET - Pending'
									&& $leadHistory->disposition != 'SCHEDULE CONFLICT - Approved'
									&& $leadHistory->disposition != 'SCHEDULE CONFLICT - Denied'
									&& $leadHistory->disposition != 'LOCATION CONFLICT - Approved'
									&& $leadHistory->disposition != 'LOCATION CONFLICT - Denied'
									&& $leadHistory->disposition != 'Client Complete - Confirm'
									&& $leadHistory->disposition != 'Client Complete - Denied'
								) //redundacy update 2/17/2017 mark
								
								if($leadHistory->note != $leadHistory->calendarAppointment->agent_notes && $leadHistory->calendarAppointment->title != 'RESCHEDULE APPOINTMENT' )
									echo '<p><b>Agent Note:</b> '.$leadHistory->calendarAppointment->agent_notes.'</p>'; 
							}	
							
							if(isset($leadHistory->calendarAppointment) && !empty($leadHistory->calendarAppointment->customer_notes) )
							{
								if($leadHistory->disposition != 'Schedule Conflict - Pending'
									&& $leadHistory->disposition != 'SCHEDULE CONFLICT'
									&& $leadHistory->disposition != 'Location Conflict - Pending'
								) //redundacy update 2/17/2017 mark
								echo '<p><b>Customer Note:</b> '.$leadHistory->calendarAppointment->customer_notes.'</p>'; 
							}
							*/
							
							if( $leadHistory->type == 2 )
							{
								if( !empty($leadHistory->note) )
								{
									echo '<b>Agent Note:</b> '. $leadHistory->note; 
								}
								// else
								// {
									 // echo '<b>Agent Note:</b> '. $leadHistory->calendarAppointment->agent_notes; 
								// }
							}
							else
							{	
								
								if( $leadHistory->type == 1 ||  $leadHistory->type == 3)
								{
									echo $leadHistory->note;
								}
								else if( $leadHistory->type == 4 )
								{
									echo $leadHistory->note;
								}
								else
								{
									if( !empty($leadHistory->calendarAppointment->customer_notes) )
									{
										echo '<b>Customer Note:</b> '.$leadHistory->calendarAppointment->customer_notes; 
									}
								}
							}
						?>
					</td>
					
					<?php if( !in_array(Yii::app()->user->account->account_type_id, array(Account::TYPE_PORTAL, Account::TYPE_CUSTOMER)) && $leadHistory->type != 4 ): ?>
					
					<td>
						<?php 
							if( $leadHistory->lead_call_history_id == null )
							{
								echo 'Not Available';
							}
							else
							{
								$channel = AsteriskChannel::model()->find(array(
									'condition' => 'call_history_id = :call_history_id',
									'params' => array(
										':call_history_id' => $leadHistory->lead_call_history_id,
									),
								));
								
								if( $channel )
								{
									
									if
									(
										$leadHistory->disposition != 'Appointment Confirmed'
										&& $leadHistory->disposition != 'INSERT APPOINTMENT'
										&& $leadHistory->disposition != 'APPOINTMENT SET - Pending'
										&& $leadHistory->disposition != 'SCHEDULE CONFLICT - Approved'
										&& $leadHistory->disposition != 'SCHEDULE CONFLICT - Denied'
										&& $leadHistory->disposition != 'LOCATION CONFLICT - Approved'
										&& $leadHistory->disposition != 'LOCATION CONFLICT - Denied'
										&& $leadHistory->disposition != 'LOCATION CONFLICT - Denied'
									)
									{
										if(!Yii::app()->user->account->getIsCustomer() && !Yii::app()->user->account->getIsCustomerOfficeStaff())
										{
											if( $this->urlExists('http://64.251.13.2/outboundrecordings/'.$channel->unique_id.'.wav') )
											{
												echo CHtml::link('Recording Link', 'http://64.251.13.2/outboundrecordings/'.$channel->unique_id.'.wav', array('target'=>'blank'));
											}
											
											if( $this->urlExists('http://sip1.engagex.com/outboundrecordings/'.$channel->unique_id.'.wav') )
											{
												echo CHtml::link('Recording Link', 'http://sip1.engagex.com/outboundrecordings/'.$channel->unique_id.'.wav', array('target'=>'blank'));
											}
										}
										else
										{
											echo 'Restricted';
										}
									}
								}
								else
								{
									echo 'Not Available';
								}
							}
						?>
					</td>
					
					<?php endif; ?>
					
				</tr>
			<?php
			
				if( $leadHistory->type != 4 )
				{
					$ctr++;
				}
			
			}
		}
		else
		{
			echo '<tr><td colspan="6"></td></tr>';
		}
	?>
</table>