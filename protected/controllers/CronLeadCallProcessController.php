<?php 

class CronLeadCallProcessController extends Controller
{
	public function accessRules()
	{
		return array(
			array('allow',  // deny all users
				'users'=>array('*'),
			),
		);
	}
	
	public function actionIndex()
	{
		$dbUpdates = 0;
		
		$cronIsOngoing = LeadCallCronProcessSettings::model()->findByPk(1);
		
		$models = LeadCallCronProcess::model()->findAll(array(
			'condition' => 'is_pending=1', 
			// 'condition' => 'lead_id IN (76760, 573121)',
			'limit' => 100,
		));
		
		echo 'count: ' . count($models);
		
		echo '<br><br>';
		
		if( $cronIsOngoing->value == 0 )
		{
			if( $models )
			{
				$transaction = Yii::app()->db->beginTransaction();
				
				try
				{
				
					echo 'started at: ' . date('g:i A');
					echo '<br><br>';
					
					$cronIsOngoing->value = 1;
					$cronIsOngoing->save(false);
					
					foreach( $models as $model )
					{
						$lead = Lead::model()->findByPk($model->lead_id);
						
						$customer = Customer::model()->findByPk($model->customer_id);
						
						$hopperEntry = LeadHopper::model()->findByPk($model->lead_hopper_id);

						$leadCallHistory = LeadCallHistory::model()->findByPk($model->lead_call_history_id);
						
						if( $model->is_skill_child == 0 )
						{
							$disposition = SkillDisposition::model()->findByPk($model->disposition_id);	
						}
						else
						{
							$disposition = SkillChildDisposition::model()->findByPk($model->disposition_id);	
						}
						

						if( empty($hopperEntry) )
						{
							$hopperEntry = new LeadHopper;
							
							$hopperEntry->setAttributes(array(
								'lead_id' => $model->lead_id,
								'list_id' => $model->lead_list_id,
								'skill_id' => $leadCallHistory->list->skill_id,
								'customer_id' => $model->customer_id,
								'calendar_appointment_id' => $model->calendar_appointment_id,
								'lead_language' => $model->lead_language,
								'lead_timezone' => $model->lead_timezone,
								'callback_date' => $model->callback_time,
								'type' => $model->hopper_type,
								'status' => 'DONE'
							));
						}
						
						// if( $disposition && $disposition->is_complete_leads == 1 )
						// {
							// $lead->status = 3;
														
							// recyle module
							// if( !empty($disposition->recycle_interval) && $disposition->is_do_not_call == 0 )
							// {
								// $time = strtotime(date("Y-m-d"));
								// $finalDate = date("Y-m-d", strtotime("+".($disposition->recycle_interval * 30)." day", $time));
								// $lead->recycle_date = $finalDate;
								// $lead->recycle_lead_call_history_id = $leadCallHistory->id;
								// $lead->recycle_lead_call_history_disposition_id = $leadCallHistory->disposition_id;
							// }
							
							// $lead->save(false);
						// }
						
						if( $disposition )
						{
							if( $disposition->is_complete_leads == 1 )
							{
								$lead->status = 3;
							}
							else
							{
								$lead->status = 1;
							}
						}
						
						if( $leadCallHistory->list->skill )
						{
							if( in_array($leadCallHistory->list->skill_id, array(23,36,37,38,39,44,54)) )
							{
								if( $lead->number_of_dials >= $leadCallHistory->list->skill->max_dials ) 
								{
									$lead->status = 3;
								}
							}
							else
							{
								if( $lead->number_of_dials >= ($leadCallHistory->list->skill->max_dials * 3 ) ) 
								{
									$lead->status = 3;
								}
							}
						}
						
						if( $disposition && $hopperEntry && $leadCallHistory )
						{
							### disposition scenarios ####
							$dispositionTxt = $leadCallHistory->disposition;
								
							if(!empty($hopperEntry) && !empty($disposition) && !empty($leadCallHistory))
							{
								if( $disposition->is_appointment_set == 1 )
								{
									//Mountain View Utah
									if( $hopperEntry->customer_id != '1021' )
									{
										CustomerQueueViewer::model()->updateAll(array('dials_until_reset'=> 0), 'customer_id = ' . $hopperEntry->customer_id);
									}
									
									if( isset($leadCallHistory->calendarAppointment) )
									{
										// $hopperEntry->status = LeadHopper::STATUS_CONFIRMATION;
										$hopperEntry->type = LeadHopper::TYPE_CONFIRMATION_CALL;
										
										$confirmationDate = $leadCallHistory->calendarAppointment->start_date;
										
										//if actual appointment date is on monday move it friday last week
										if( date('N', strtotime($confirmationDate)) == 1 )
										{
											$confirmationDate = date('Y-m-d', strtotime('last friday', strtotime($confirmationDate))).' '.date('H:i:s', strtotime($confirmationDate));
										}
										else
										{
											//move it to 1 business day before the actual appointment date
											$confirmationDate = date('Y-m-d', strtotime('-1 day', strtotime($confirmationDate))).' '.date('H:i:s', strtotime($confirmationDate));
										}
										
										$hopperEntry->calendar_appointment_id = $leadCallHistory->calendar_appointment_id;
										$hopperEntry->appointment_date = $confirmationDate;
									}
								}
								
								if( isset($leadCallHistory->calendarAppointment) && ($disposition->is_schedule_conflict == 1 || $disposition->is_location_conflict == 1) )
								{
									// $dispositionTxt .= ' - Pending';
									
									// $hopperEntry->status = LeadHopper::STATUS_CONFLICT;
									$hopperEntry->calendar_appointment_id = $leadCallHistory->calendar_appointment_id;
									$hopperEntry->type = LeadHopper::TYPE_CONFLICT;
									
									
									if($disposition->is_schedule_conflict == 1)
									{
										$calendarAppointment = $leadCallHistory->calendarAppointment;
										// $calendarAppointment->title = "SCHEDULE CONFLICT";
										$calendarAppointment->status= $calendarAppointment::STATUS_PENDING;
										$calendarAppointment->save(false);
									}
									
									if($disposition->is_location_conflict == 1)
									{
										$calendarAppointment = $leadCallHistory->calendarAppointment;
										// $calendarAppointment->title = "LOCATION CONFLICT";
										$calendarAppointment->status= $calendarAppointment::STATUS_PENDING;
										$calendarAppointment->save(false);
									}
									
									$lead->number_of_dials = 0;
								}
								
								
								if($model->hopper_type == LeadHopper::TYPE_CONFIRMATION_CALL)
								{
									$hopperEntry->type = LeadHopper::TYPE_CONTACT;

									# a.)its possible on that confirm call that the lead cancels the appointment in which case it would be removed from the calendar.
									if($disposition->is_appointment_cancelled == 1)
									{
										$lead->status = 3;	

										if( isset($leadCallHistory->calendarAppointment) )
										{
											$calendarAppointment = $leadCallHistory->calendarAppointment;
											$calendarAppointment->status = CalendarAppointment::STATUS_DELETED;
											$calendarAppointment->save(false);
										}
									}
									
									# mark 2016-7-2016: if the confirm call disposition is set to appointment reschedule, make the leadhopper type as reschedule 
									# to make it show in the confirm/reschedule report page as reschedule
									if($disposition->is_appointment_reschedule == 1)
									{
										$hopperEntry->type = LeadHopper::TYPE_RESCHEDULE;
										
										if( $disposition->skill_child_disposition_name == 'Appointment Cancelled - Reschedule Later' )
										{ 
											$hopperEntry->status = 'DONE';
											$hopperEntry->appointment_date = date('Y-m-d H:i:s', strtotime('+1 day'));
										}	
									}	
									
								}
								
								if( $model->hopper_type == LeadHopper::TYPE_RESCHEDULE || $model->hopper_type == LeadHopper::TYPE_NO_SHOW_RESCHEDULE )
								{
									$hopperEntry->type = LeadHopper::TYPE_CONTACT;
								}
								
								
								#CHECK IF the CUSTOMER has their Customer Skill: Skill Child settings are turned on
								$customerSkill = CustomerSkill::model()->find(array(
									'condition' => 'customer_id = :customer_id AND skill_id = :skill_id AND status=1',
									'params' => array(
										':customer_id' => $hopperEntry->customer_id,
										':skill_id' => $hopperEntry->skill_id,
									),
								));
								
								if( $customerSkill )
								{
									if($hopperEntry->type == LeadHopper::TYPE_CONFIRMATION_CALL)
									{
										$customerSkillChildConfirm = CustomerSkillChild::model()->find(array(
											'condition' => 'customer_id = :customer_id AND skill_id = :skill_id AND customer_skill_id = :customer_skill_id AND skill_child_id = :skill_child_id',
											'params' => array(
												':customer_id' => $hopperEntry->customer_id,
												':skill_id' => $hopperEntry->skill_id,
												':customer_skill_id' => $customerSkill->id,
												':skill_child_id' => $hopperEntry->skill_child_confirmation_id
											),
										));

										if( $customerSkillChildConfirm )
										{
											if( $customerSkillChildConfirm->is_enabled == 1 )
											{
												$hopperEntry->type == LeadHopper::TYPE_CONFIRMATION_CALL;
											}
											else
											{
												$hopperEntry->type == LeadHopper::TYPE_CONTACT;
											}
										}
										else
										{
											$hopperEntry->type == LeadHopper::TYPE_CONTACT;
										}
									}
									
									if( $hopperEntry->type == LeadHopper::TYPE_RESCHEDULE || $hopperEntry->type == LeadHopper::TYPE_NO_SHOW_RESCHEDULE )
									{
										if( $hopperEntry->type == LeadHopper::TYPE_RESCHEDULE )
										{
											$hopperEntryType = LeadHopper::TYPE_RESCHEDULE;
										}
										else
										{
											$hopperEntryType = LeadHopper::TYPE_NO_SHOW_RESCHEDULE;
										}
										 
										$customerSkillChildResched = CustomerSkillChild::model()->find(array(
											'condition' => 'customer_id = :customer_id AND skill_id = :skill_id AND customer_skill_id = :customer_skill_id AND skill_child_id = :skill_child_id',
											'params' => array(
												':customer_id' => $hopperEntry->customer_id,
												':skill_id' => $hopperEntry->skill_id,
												':customer_skill_id' => $customerSkill->id,
												':skill_child_id' => $hopperEntry->skill_child_reschedule_id
											),
										));

										if( $customerSkillChildResched )
										{
											if( $customerSkillChildResched->is_enabled == 1 )
											{
												$hopperEntry->type == $hopperEntryType;
											}
											else
											{
												$hopperEntry->type == LeadHopper::TYPE_CONTACT;
											}
										}
										else
										{
											$hopperEntry->type == LeadHopper::TYPE_CONTACT;
										}
									}
								}

								$hopperEntry->save(false);
							}

							
							if( isset($disposition->skill_disposition_name) && strtoupper($disposition->skill_disposition_name) == 'LANGUAGE BARRIER - SPANISH' )
							{
								$lead->language = 'Spanish';
							}
							
							if( !empty($model->lead_phone_type) && !empty($lead) )
							{
								$field = $model->lead_phone_type.'_phone_disposition';
									
								if( $model->lead_phone_type != 'manual' )
								{
									$lead->$field = $leadCallHistory->disposition;

									$field = $model->lead_phone_type.'_phone_disposition_detail';
									$lead->$field = $leadCallHistory->disposition_detail;
								}
							}
							
							if( $lead->save(false) )
							{
								$leadHistory = new LeadHistory;
									
								$leadHistory->setAttributes(array(
									'lead_call_history_id' => $leadCallHistory->id,
									'lead_id' => $leadCallHistory->lead_id,
									'agent_account_id' => $model->agent_account_id,
									'calendar_appointment_id' => $leadCallHistory->calendar_appointment_id,
									'lead_phone_number' => $leadCallHistory->lead_phone_number,
									'dial_number' => 1,
									'call_date' => $leadCallHistory->start_call_time,
									'disposition' => $dispositionTxt,
									'disposition_detail' => $leadCallHistory->disposition_detail,
									'note' => $leadCallHistory->agent_note,
									'type' => 2,
								));

								if( $leadHistory->save(false) )
								{
									// $result['html'] = $this->renderPartial('_lead_history_list', array(
										// 'data' => $leadHistory,
									// ), true); 
								}
							}
							
							//send to email monitor
							if( $disposition->is_send_email == 1 )
							{			
								$emailMonitor = new EmailMonitor;
								
								$emailMonitor->setAttributes(array(
									'lead_id' => $leadCallHistory->lead_id,
									'agent_id' => $model->agent_account_id,
									'customer_id' => $leadCallHistory->customer_id,
									'skill_id' => $leadCallHistory->list->skill_id,
									'disposition_id' => $leadCallHistory->disposition_id,
									'child_disposition_id' => $leadCallHistory->skill_child_disposition_id,
									'is_child_skill' => $model->is_skill_child,
									'disposition' => $leadCallHistory->disposition,
									'calendar_appointment_id' => $leadCallHistory->calendar_appointment_id,
									'lead_call_history_id' => $leadCallHistory->id,
									'html_content' => $leadCallHistory->getReplacementCodeValues(),
									'status' => 0,
								));						

								if( $disposition->is_send_text == 1 )
								{
									$emailMonitor->text_content = $leadCallHistory->getReplacementCodeValues($disposition->text_body);
								}
								
								$emailMonitor->save(false);
							}
						}
						
						$model->is_pending = 0;
						
						if( $model->save(false) )
						{
							$dbUpdates++;
						}
					}
				
				
					echo 'ended at: ' . date('g:i A');
					echo '<br><br>';
					
					$cronIsOngoing->value = 0;
					$cronIsOngoing->save(false);
					
					$transaction->commit();
				}
				catch(Exception $e)
				{
					$transaction->rollback();
				}
			}
			
			echo '<br><br>dbUpdates: ' . $dbUpdates;
		}
	}
	
}

?>