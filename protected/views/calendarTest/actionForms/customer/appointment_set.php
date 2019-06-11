<div class="tabbable">
	<ul id="myTab4" class="nav nav-tabs padding-12 tab-color-blue background-blue">
		<li class="active">
			<a href="#info" data-toggle="tab">Appointment Info</a>
		</li>

		<li>
			<a href="#history" data-toggle="tab">Lead History</a>
		</li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane in active" id="info">
			<div class="profile-user-info profile-user-info-striped">

				<div class="profile-info-row">
					<div class="profile-info-name"> Action </div>

					<div class="profile-info-value">
						<?php
							
							if( strtotime($model->start_date) < time() )
							{
								if( strtotime($model->start_date) > strtotime(date("Y-m-d").' -2 Weekdays')  )
								{
									$actionOptions = array(
										'NO SHOW RESCHEDULE' => 'NO SHOW RESCHEDULE',
										'RESCHEDULE APPOINTMENT' => 'RESCHEDULE APPOINTMENT',
									);
									
									if( !Yii::app()->user->account->getIsCustomer() && !Yii::app()->user->account->getIsCustomerOfficeStaff() )
									{
										$actionOptions['UPDATE CALENDAR ONLY'] = 'UPDATE CALENDAR ONLY'; 
									}
								}
								else
								{
									$actionOptions = array(
										'RESCHEDULE APPOINTMENT' => 'RESCHEDULE APPOINTMENT',
									);
									
									if( !Yii::app()->user->account->getIsCustomer() && !Yii::app()->user->account->getIsCustomerOfficeStaff() )
									{
										$actionOptions['UPDATE CALENDAR ONLY'] = 'UPDATE CALENDAR ONLY'; 
									}
								}
							}
							else
							{
								$actionOptions = array(
									'CANCEL APPOINTMENT' => 'CANCEL APPOINTMENT', 
									'RESCHEDULE APPOINTMENT' => 'RESCHEDULE APPOINTMENT',
									'CHANGE APPOINTMENT'=>'CHANGE APPOINTMENT',
								);
								
								if( !Yii::app()->user->account->getIsCustomer() && !Yii::app()->user->account->getIsCustomerOfficeStaff() )
								{
									$actionOptions['UPDATE CALENDAR ONLY'] = 'UPDATE CALENDAR ONLY'; 
								}
							}
							
							echo $form->dropDownList($model, 'title', $actionOptions, array('class'=>'form-control', 'prompt'=>' - Select -', 'style'=>'width:auto;')); 
						?>
					</div>
				</div>
				
				<div class="profile-info-row alt-date-field-container">
					<div class="profile-info-name"> Date </div>

					<div class="profile-info-value">
						<?php echo CHtml::textField('CalendarAppointment[alt_date]', date('m/d/Y', strtotime($model->start_date)), array('class' => 'datepicker')); ?>
					</div>
				</div>
				
				<div class="profile-info-row calendar-field-container">
					<div class="profile-info-name"> Calendar </div>

					<div class="profile-info-value">
						<?php
							echo $form->dropDownList($model, 'calendar_id', Calendar::items($model->calendar->customer_id), array('class'=>'form-control', 'style'=>'width:auto;')); 
						?>
					</div>
				</div>
				
				<div class="profile-info-row start-time-field-container">
					<div class="profile-info-name"> Start Time </div>

					<div class="profile-info-value">
						<?php
						
							$timeOptions = $calendar->timeOptions();
							
							if( !in_array($model->start_date_time, $timeOptions) )
							{
								$timeOptions[$model->start_date_time] = date('g:i A', strtotime($model->start_date));
							}
							
							if( !in_array($model->end_date_time, $timeOptions) )
							{
								$timeOptions[$model->end_date_time] = date('g:i A', strtotime($model->end_date));
							}
							

							echo $form->dropDownList($model, 'start_date_time', $timeOptions, array('class'=>'form-control', 'prompt'=>' - Select -', 'style'=>'width:auto;', 'disabled'=>true));
						?>
					</div>
				</div>
				
				<div class="profile-info-row end-time-field-container">
					<div class="profile-info-name"> End Time </div>

					<div class="profile-info-value">
						<?php 
							echo $form->dropDownList($model, 'end_date_time', $timeOptions, array('class'=>'form-control', 'prompt'=>' - Select -', 'style'=>'width:auto;', 'disabled'=>true)); 
						?>
					</div>
				</div>				
				
				<?php /*<div class="profile-info-row details-field-container">
					<div class="profile-info-name"> Details </div>

					<div class="profile-info-value">
						<?php 
							echo $form->textArea($model, 'details', array('class'=>'col-xs-12', 'disabled'=>true)); 
						?>
					</div>
				</div>*/ ?>
				
				<div class="profile-info-row agent-field-container">
					<div class="profile-info-name"> Agent Notes </div>

					<div class="profile-info-value">
						<?php 
							echo $form->textArea($model, 'agent_notes', array('class'=>'col-xs-12', 'disabled'=>$viewer == 'agent' ? false : true)); 
						?>
					</div>
				</div>
				
				<div class="profile-info-row agent-field-container">
					<div class="profile-info-name"> Customer Notes </div>

					<div class="profile-info-value">
						<?php 
							echo $form->textArea($model, 'customer_notes', array('class'=>'col-xs-12', 'disabled'=>$viewer == 'customer' ? false : true)); 
						?>
					</div>
				</div>
				
				<div class="profile-info-row location-field-container">
					<div class="profile-info-name"> <?php echo $model->isNewRecord ? 'Proposed Location' : 'Location'; ?> </div>

					<div class="profile-info-value">								
						<?php 
							echo $form->dropDownList($model, 'location', $calendar->locationOptions('all'), array('class'=>'form-control','prompt'=>' - Select -','style'=>'width:auto;', 'disabled'=>true));
						?>
					</div>
				</div>
			</div>

			<div class="space-12"></div>

			<div class="center">
				<button type="button" class="btn btn-sm btn-info" data-action="save">Save</button>
			</div>
		</div>
	
		<div class="tab-pane" id="history">

			<div class="lead-history-table-wrapper" style="height:400px; overflow-y:auto;">
				<?php 
					$leadHistories = LeadHistory::model()->findAll(array(
						'condition' => 'lead_id = :lead_id',
						'params' => array(
							':lead_id' => $model->lead_id,
						), 
					));
				?>
				
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
							foreach( $leadHistories as $leadHistory )
							{
							?>
								<tr>
									<td>
										<?php 
											$date = new DateTime($leadHistory->date_created, new DateTimeZone('America/Chicago'));

											$date->setTimezone(new DateTimeZone('America/Denver'));

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
									
									<td><?php echo isset($leadHistory->agentAccount) ? $leadHistory->agentAccount->getFullName() : ''; ?></td>
									
									<td>
										<?php
											$dispositionTxt = $leadHistory->disposition; 
											
											if( isset($leadHistory->calendarAppointment) && ($leadHistory->calendarAppointment->title == 'SCHEDULE CONFLICT' || $leadHistory->calendarAppointment->title == 'LOCATION CONFLICT') )
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
											}

											echo $dispositionTxt;
										?>
									</td>
									
									<td><?php echo $leadHistory->dial_number; ?></td>
									
									<td>
										<?php 
											if( $leadHistory->type == 4 )
											{
												echo 'Lead Import';
											}
											else
											{
												echo $leadHistory->note;
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
													if(!Yii::app()->user->account->getIsCustomer() && !Yii::app()->user->account->getIsCustomerOfficeStaff())
														echo CHtml::link('Recording Link', 'http://64.251.13.2/outboundrecordings/'.$channel->unique_id.'.wav', array('target'=>'blank'));
													else
														echo 'Restricted';
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
							}
						}
						else
						{
							echo '<tr><td colspan="6"></td></tr>';
						}
					?>
				</table>
			</div>
			
		</div>
	</div>
</div>