<?php if($data->note != 'Duplicate allowed to be imported'){ ?>

<div class="timeline-item clearfix">
	<div class="timeline-info">
		<?php 
			if( $data->type == 1 )
			{
				echo '<i class="timeline-indicator ace-icon fa fa-edit btn btn-warning no-hover"></i>';
			}				
			elseif( $data->type == 2 )
			{
				echo '<i class="timeline-indicator ace-icon fa fa-phone btn btn-primary no-hover"></i>';
			}				
			else
			{
				echo '<i class="timeline-indicator ace-icon fa fa-calendar btn btn-danger no-hover"></i>';
			}
		?>
	</div>

	<div class="widget-box transparent">
		<div class="widget-header widget-header-small">
			<h5 class="widget-title smaller">
				<?php 
					if( $data->type == 4 || $data->type == 6 || $data->type == 7) //lead import time
					{
						$account = Account::model()->findByPk($data->agent_account_id);
					
						if( $data->type == 4 )
						{
							
						}
						else
						{
							if( $account )
							{
								echo $account->getFullName();
							}
							else
							{
								echo 'Agent ID: ' . $data->agent_account_id;
							}
						}
					}
					else
					{
						if( $data->type == 3 )
						{
							if( $data->account_id != null )
							{
								if( isset($data->account) )
								{
									echo $data->account->getFullName();
								}
							}
							else
							{
								if( isset($data->agentAccount) )
								{
									echo $data->agentAccount->getFullName();
								}
							}
						}
						else
						{
							if( isset($data->agentAccount) )
							{
								echo $data->agentAccount->getFullName();
							}
							else
							{
								echo 'Agent ID: ' . $data->agent_account_id;
							}
						}
					}
				?>
			</h5>
			<span class="grey">
				<?php 
					if( $data->type == 1 )
					{
						echo ' | Agent Notes';
					}				
					elseif( $data->type == 2 )
					{
						echo ' | Call | ' . $data->disposition;
					}
					elseif( $data->type == 4 )
					{
						echo ' | Lead Import Date/Time ';
					}		
					elseif( $data->type == 6 )
					{
						echo ' | Lead Info Update Date/Time ';
					}	
					elseif( $data->type == 7 )
					{
						echo ' | BLACKOUT ';
					}						
					else
					{
						echo ' | Calendar Appointment | ';
						
						$dispositionTxt = $data->disposition; 
															
						if( isset($data->calendarAppointment) )
						{
							// if( $data->calendarAppointment->status == CalendarAppointment::STATUS_PENDING )
							// {
								// $dispositionTxt .= ' - Pending';
							// }
							
							// if( $data->calendarAppointment->status == CalendarAppointment::STATUS_APPROVED )
							// {
								// $dispositionTxt .= ' - Approved';
							// }
							
							// if( $data->calendarAppointment->status == CalendarAppointment::STATUS_DELETED )
							// {
								// $dispositionTxt .= ' - Denied';
							// }
							
							// if( $data->calendarAppointment->status == CalendarAppointment::STATUS_SUGGEST )
							// {
								// $dispositionTxt .= ' - Alt Suggested';
							// }
							
							if( isset($data->account) )
							{
								$dispositionTxt .= ' by ' . $data->account->getFullName();
							}
						}

						echo $dispositionTxt;
					}
					
					// if( in_array($data->disposition, array('Call Back', 'Call Back - Confirm')) && isset($data->leadCallHistory) )
					if( isset($data->leadCallHistory) && isset($data->leadCallHistory->skillDisposition) && $data->leadCallHistory->skillDisposition->is_callback == 1)
					{
						$timeZone = AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $data->leadCallHistory->customer->phone) );
								
						if( !empty($lead->timezone) )
							$timeZone = $lead->timezone;
						
						if( !empty($timeZone) )
						{
							$leadLocalTime = new DateTime($data->leadCallHistory->callback_time, new DateTimeZone(timezone_name_from_abbr($timeZone)) );
							echo ' | ' . $leadLocalTime->format('m/d/Y g:i A');
						}
						else
						{
							$leadLocalTime = new DateTime($data->leadCallHistory->callback_time, new DateTimeZone(timezone_name_from_abbr($data->leadCallHistory->customer->phone_timezone)) );
							echo ' | ' . $leadLocalTime->format('m/d/Y g:i A');
						}
					}
					
					
					$phoneType = '';
					
					if( $data->lead->home_phone_number == $data->lead_phone_number )
					{
						$phoneType = 'Home';
					}
					
					if( $data->lead->mobile_phone_number == $data->lead_phone_number )
					{
						$phoneType = 'Mobile';
					}
					
					if( $data->lead->office_phone_number == $data->lead_phone_number )
					{
						$phoneType = 'Office';
					}

					if( $phoneType != '' && !empty($data->lead_phone_number) )
					{
						echo ' | ' . $phoneType . " (".substr($data->lead_phone_number, 0, 3).") ".substr($data->lead_phone_number, 3, 3)."-".substr($data->lead_phone_number,6);
					}
				?>
			</span>

			<span class="widget-toolbar no-border">
				<i class="ace-icon fa fa-clock-o bigger-110"></i>
				<?php 
					$date = new DateTime($data->date_created, new DateTimeZone('America/Chicago'));

					$date->setTimezone(new DateTimeZone('America/Denver'));

					echo $date->format('m/d/Y g:i A'); 
				?>
			</span>

			<span class="widget-toolbar">
			</span>
		</div>

		<div class="widget-body">	
			<?php
				if( 
					($data->type == 3 && isset($data->calendarAppointment)) ||
					($data->type == 2 && $data->disposition == 'Appointment Set')
				)
				{
					switch( $data->calendarAppointment->location )
					{
						default: 
						case 1: $location = 'Office'; break;
						case 2: $location = 'Home'; break;
						case 3: $location = 'Phone'; break;
						case 4: $location = 'Skype'; break;
					}
					
					echo '<div class="widget-main">'.date('F, d Y g:i A', strtotime($data->calendarAppointment->start_date)).' - '.$location.' - '.$data->calendarAppointment->calendar->name.'</div>';
				
						
					if( !empty($data->note) && ($data->calendarAppointment->customer_notes != $data->note) )
					{
						echo '<div class="widget-main">'.$data->note.'</div>'; 
						
						if( isset($data->calendarAppointment) && $data->calendarAppointment->status == CalendarAppointment::STATUS_SUGGEST )
						{
							echo '<div class="widget-main"><b>Customer Note:</b> '.$data->calendarAppointment->customer_notes.'</div>'; 
						}
					}
					else
					{
						if(isset($data->calendarAppointment) && !empty($data->calendarAppointment->customer_notes) )
						{
							echo '<div class="widget-main"><b>Customer Note:</b> '.$data->calendarAppointment->customer_notes.'</div>'; 
							// echo '<div class="widget-main"><b>Customer Note:</b> '.$data->note.'</div>'; 
						}
						else
							echo '<div class="widget-main">'.$data->note.'</div>'; 
					}
					
				}
				else if($data->type == 6 || $data->type == 7)
				{
					echo '<div class="widget-main">'.$data->content.' </div>'; 
				}
				else
				{
					if( !empty($data->note) )
					{
						echo '<div class="widget-main">'.$data->note.' --</div>'; 
					}
				}
			?>
			
			<?php 
				// if(isset($data->calendarAppointment) && !empty($data->calendarAppointment->agent_notes) ){
					// echo '<div class="widget-main"><b>Agent Notes:</b> '.$data->calendarAppointment->agent_notes.'</div>'; 
				// }
			?>
			
			<?php 
				// if(isset($data->calendarAppointment) && !empty($data->calendarAppointment->customer_notes) ){
					// echo '<div class="widget-main"><b>Customer Notes:</b> '.$data->calendarAppointment->customer_notes.'</div>'; 
				// }
			?>
		</div>
	</div>
</div>

<?php } ?>