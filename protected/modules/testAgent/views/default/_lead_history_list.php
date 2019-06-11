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
					if( $data->type == 4 ) //lead import time
					{
						$account = Account::model()->findByPk($data->agent_account_id);
						
						if( $account )
						{
							echo $account->getFullName();
						}
						else
						{
							echo 'Agent ID: ' . $data->agent_account_id;
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
				if( $data->type == 3 && isset($data->calendarAppointment) )
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
				}
				else
				{
					if( !empty($data->note) )
					{
						echo '<div class="widget-main">'.$data->note.'</div>'; 
					}
				}
			?>
			
			<?php 
				if(isset($data->calendarAppointment) && !empty($data->calendarAppointment->agent_notes) ){
					echo '<div class="widget-main"><b>Agent Notes:</b> '.$data->calendarAppointment->agent_notes.'</div>'; 
				}
			?>
			
			<?php 
				if(isset($data->calendarAppointment) && !empty($data->calendarAppointment->customer_notes) ){
					echo '<div class="widget-main"><b>Customer Notes:</b> '.$data->calendarAppointment->customer_notes.'</div>'; 
				}
			?>
		</div>
	</div>
</div>