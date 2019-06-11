<?php 

class CronHopperController extends Controller
{
	public function accessRules()
	{
		return array(
			array('allow', 
				'users'=>array('*'),
			),
		);
	}
	
	public function actionIndex()
	{
		if( !isset($_GET['debug']) )
		{
			exit;
		}
		
		ini_set('memory_limit', '1024M');
		set_time_limit(0);
		
		date_default_timezone_set('America/Denver');
		
		echo 'Process started at ' . date('H:i:s');
		
		echo '<br><br>';
		
		//remove leads with end call, dispo and done status on hopper
		$leadHopperEntries = LeadHopper::model()->findAll(array(
			'condition' => 'status IN ("DONE", "INCALL")',
		));
		
		if( $leadHopperEntries )
		{	
			foreach( $leadHopperEntries as $leadHopperEntry )
			{
				$lead = $leadHopperEntry->lead;
				$customer = $leadHopperEntry->customer;
				
				if( !empty($lead->timezone) )
				{
					$timeZone = $lead->timezone;
				}
				else
				{
					$timeZone = AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $customer->phone) );
				}
				
				$currentDateTime = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('America/Denver'));
				$currentDateTime->setTimezone(new DateTimeZone('America/Denver'));

				$leadLocalTime = $currentDateTime->setTimezone(new DateTimeZone(timezone_name_from_abbr($timeZone)));
				
				if( $leadHopperEntry->status == 'DONE' && in_array($leadHopperEntry->type, array(LeadHopper::TYPE_CONTACT, LeadHopper::TYPE_LEAD_SEARCH)) )
				{
					$leadHopperEntry->delete();
				}

				if( ($leadHopperEntry->status == 'DONE') && $leadHopperEntry->type == LeadHopper::TYPE_CALLBACK && strtotime($leadLocalTime->format('Y-m-d g:i a')) >= strtotime($leadHopperEntry->callback_date) && $this->checkLeadRetryTime($lead) )
				{					
					$leadHopperEntry->status = 'READY';
					$leadHopperEntry->save(false);
				}
				 
				
				if( ($leadHopperEntry->status == 'DONE') && $leadHopperEntry->type == LeadHopper::TYPE_CONFIRMATION_CALL && date('Y-m-d') >= date('Y-m-d', strtotime($leadHopperEntry->appointment_date)) )
				{
					$leadHopperEntry->status = 'READY';
					$leadHopperEntry->save(false);
				}
			}
		}
		
		$leadAddedToHopper = 0;
		
		$customerWhiteQueues = CustomerQueueViewer::model()->findAll(array(
			'condition' => 'available_leads > 0',
			'order' => 'priority DESC',						
		));
		
		if( $customerWhiteQueues )
		{
			foreach( $customerWhiteQueues as $customerQueue )
			{
				$customerSkill = CustomerSkill::model()->find(array(
					'condition' => 'customer_id = :customer_id AND skill_id = :skill_id AND status=1',
					'params' => array(
						':customer_id' => $customerQueue->customer_id,
						':skill_id' => $customerQueue->skill_id,
					),
				));
				
				$isCallablCustomer = false;	

				if( time() >= strtotime($customerSkill->start_month) )
				{
					$isCallablCustomer = true;
				}
				
				if( $customerSkill->is_contract_hold == 1 )
				{
					if( !empty($customerSkill->is_contract_hold_start_date) && !empty($customerSkill->is_contract_hold_end_date) )
					{
						if( time() >= strtotime($customerSkill->is_contract_hold_start_date) && time() <= strtotime($customerSkill->is_contract_hold_end_date) )
						{
							$isCallablCustomer = false;
						}
					}
				}
				
				if( !empty($customerSkill->end_month) && date('Y', strtotime($customerSkill->end_month)) >= 2015 )
				{
					if( time() >= strtotime($customerSkill->end_month) )
					{
						$isCallablCustomer = false;
					}
				}
							
				if( $customerSkill->is_hold_for_billing == 1 )
				{
					$isCallablCustomer = false;
				}
							
				if( isset($customerSkill->customer) && $customerSkill->customer->status == 1 && $isCallablCustomer )
				{
					$customer = Customer::model()->findByPk($customerSkill->customer_id);
					$skill = Skill::model()->findByPk($customerSkill->skill_id);
					
					$nextAvailableCallingTime = $this->checkTimeZone($customerSkill);
					
					$lists = Lists::model()->findAll(array(
						'together' => true,
						'condition' => 't.customer_id = :customer_id AND skill_id = :skill_id AND t.status=1',
						'params' => array(
							':customer_id' => $customerQueue->customer_id,
							':skill_id' => $customerQueue->skill_id,
						),
					));
					
					if( $nextAvailableCallingTime == 'Now' )
					{
						$customerSkill = CustomerSkill::model()->find(array(
							'condition' => 'customer_id = :customer_id AND skill_id = :skill_id AND status=1',
							'params' => array(
								':customer_id' => $customerQueue->customer_id,
								':skill_id' => $customerQueue->skill_id,
							),
						));
						
						$customer = $customerSkill->customer;
						
						$lists = Lists::model()->findAll(array(
							'together' => true,
							'condition' => 't.customer_id = :customer_id AND skill_id = :skill_id AND t.status=1',
							'params' => array(
								':customer_id' => $customerQueue->customer_id,
								':skill_id' => $customerQueue->skill_id,
							),
						));
						
						if( $customer->status == 1 )
						{
							if( $lists )
							{
								foreach( $lists as $list )
								{
									if( $list->status == 1 )
									{
										if( $list->lead_ordering == 2 )
										{
											$order = 'last_name ASC';
										}
										elseif( $list->lead_ordering == 3 )
										{
											$order = 'custom_date DESC';
										}
										else
										{
											$order = 'RAND()';
										}
										
										//get callable leads
										$leads = Lead::model()->findAll(array(
											'condition' => 'list_id = :list_id AND t.type=1 AND t.status=1 AND t.number_of_dials < :skill_max_dials',
											'params' => array(
												':list_id' => $list->id,
												':skill_max_dials' => $customerSkill->skill->max_dials,
											),
											'order' => $order,
											'limit' => 20,
										));
										
										$leadCounter = LeadHopper::model()->count(array(
											'condition' => 'status="READY" AND customer_id = :customer_id',
											'params' => array(
												':customer_id' => $customerQueue->customer_id,
											),
										));
										
										if( $leads && $leadCounter == 0 )
										{
											foreach( $leads as $lead )
											{

												$existingHopperEntry = LeadHopper::model()->find(array(
													'condition' => 'lead_id = :lead_id',
													'params' => array(
														':lead_id' => $lead->id,
													),
												));

												if( empty($existingHopperEntry) && $this->checkTimeZone($customerSkill, 'lead', $lead) == 'Now' && $this->checkLeadRetryTime($lead) )
												{
													if( !empty($lead->timezone) )
													{
														$timeZone = $lead->timezone;
													}
													else
													{
														$timeZone = AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $customer->phone) );
													}
													
													$hopperEntry = new LeadHopper;
													
													$hopperEntry->setAttributes(array(
														'lead_id' => $lead->id,
														'list_id' => $list->id,
														'skill_id' => $list->skill_id,
														'customer_id' => $list->customer_id,
														'lead_language' => $lead->language,
														'lead_timezone' => $timeZone,
													));
													
													
													$skillChildConfirmation = SkillChild::model()->find(array(
														'condition' => 'skill_id = :skill_id AND type = :type',
														'params' => array(
															':skill_id' => $list->skill_id,
															':type' => SkillChild::TYPE_CONFIRM,
														),
													));
													
													if($skillChildConfirmation !== null)
														$hopperEntry->skill_child_confirmation_id = $skillChildConfirmation->id;
													
													$skillChildReschedule = SkillChild::model()->find(array(
														'condition' => 'skill_id = :skill_id AND type = :type',
														'params' => array(
															':skill_id' => $list->skill_id,
															':type' => SkillChild::TYPE_RESCHEDULE,
														),
													));
													
													if($skillChildReschedule !== null)
														$hopperEntry->skill_child_reschedule_id = $skillChildReschedule->id;
													
													if( $hopperEntry->save(false) )
													{
														$leadAddedToHopper++;
														
														echo 'Added Lead of Customer ID: '.$customerQueue->customer_id.' - '.$customerQueue->customer_name.' => Lead ID: '.$lead->id.' - Name: '.$lead->getFullName() .' => Timezone:'. $lead->timezone;
														echo '<br>';
													}
												}
												else
												{
													// if( $this->checkTimeZone($customerSkill, 'lead', $lead)  != 'Now' )
													// {
														echo 'Deleted Lead of Customer ID: '.$customerQueue->customer_id.' - '.$customerQueue->customer_name. ' => Lead ID: ' . $lead->id.' - '.$lead->getFullName() .' => Timezone:'. $lead->timezone;
														echo '<br>';
											
														LeadHopper::model()->delete(array(
															'condition' => 'customer_id = :customer_id AND status IN ("READY", "INCALL", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
															'params' => array(
																':lead_id' => $lead->id,
															),
														));
													// }
												}
											}
										}						
									}
									else
									{
										echo 'Deleted Leads of Customer ID: ' . $customerQueue->customer_id.' - '.$customerQueue->customer_name.' List ID: ' . $customerQueue->list_id;
										echo '<br>';
										
										LeadHopper::model()->deleteAll(array(
											'condition' => 'customer_id = :customer_id AND status IN ("READY", "INCALL", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7) AND list_id = :list_id',
											'params' => array(
												':customer_id' => $customerQueue->customer_id,
												':list_id' => $customerQueue->list_id,
											),
										));
									}
								}
							}
						}
						else
						{
							echo 'Deleted Leads of Customer ID: ' . $customerQueue->customer_id.' - '.$customerQueue->customer_name;
							echo '<br>';
							
							LeadHopper::model()->deleteAll(array(
								'condition' => 'customer_id = :customer_id AND status IN ("READY", "INCALL", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
								'params' => array(
									':customer_id' => $customerQueue->customer_id,
								),
							));
						}
					}
					else
					{
						echo 'Deleted Leads of Customer ID: ' . $customerQueue->customer_id.' - '.$customerQueue->customer_name;
						echo '<br>';
						
						LeadHopper::model()->deleteAll(array(
							'condition' => 'customer_id = :customer_id AND status IN ("READY", "INCALL", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
							'params' => array(
								':customer_id' => $customerQueue->customer_id,
							),
						));
					}
				}
			}
		}
		
		
		echo '<br><br> Process ended at '.date('H:i:s');
		
		echo '<br><br>Lead Added to Hopper: '.$leadAddedToHopper;
		
		echo '<br><br>Hopper Count: '.LeadHopper::model()->count();
	}
	
	public function checkTimeZone($customerSkill, $type='customer', $lead=null)
	{
		$nextAvailableCallingTime = '';
		
		$customer = $customerSkill->customer;
		
		$skillScheduleHolder = array();
			
		$currentDateTime = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('America/Denver'));
		$currentDateTime->setTimezone(new DateTimeZone('America/Denver')); 

		
		if( $customerSkill->is_custom_call_schedule == 1 )
		{
			$customCallSchedules = CustomerSkillSchedule::model()->findAll(array(
				'condition' => 'customer_skill_id = :customer_skill_id AND schedule_day = :schedule_day',
				'params' => array(
					':customer_skill_id' => $customerSkill->id,
					':schedule_day' => date('N'),
				),
			));
			
			if( $customCallSchedules )
			{
				foreach( $customCallSchedules as $customCallSchedule )
				{
					$skillScheduleHolder[$customer->id][$customCallSchedule->id]['schedule_start'] = date('g:i A', strtotime($customCallSchedule->schedule_start));
					$skillScheduleHolder[$customer->id][$customCallSchedule->id]['schedule_end'] = date('g:i A', strtotime($customCallSchedule->schedule_end));
				}
			}
		}
		else
		{	
			$skillSchedules = SkillSchedule::model()->findAll(array(
				// 'condition' => 'skill_id = :skill_id AND schedule_day = :schedule_day AND status=1 AND is_deleted=0',
				'condition' => 'skill_id = :skill_id AND schedule_day = :schedule_day',
				'params' => array(
					'skill_id' => $customerSkill->skill_id,
					':schedule_day' => date('N'),
				),
			));

			foreach($skillSchedules as $skillSchedule)
			{
				$skillScheduleHolder[$customer->id][$skillSchedule->id]['schedule_start'] = date('g:i A', strtotime($skillSchedule->schedule_start));
				$skillScheduleHolder[$customer->id][$skillSchedule->id]['schedule_end'] = date('g:i A', strtotime($skillSchedule->schedule_end));
			}
		}
	
		
		if( isset($skillScheduleHolder[$customer->id]) )
		{	
			foreach($skillScheduleHolder[$customer->id] as $sched)
			{	
				if( $type == 'customer' )
				{
					$timeZone = AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $customer->phone) );
				}
				else
				{
					if( !empty($lead->timezone) )
					{
						$timeZone = $lead->timezone;
					}
					else
					{
						$timeZone = AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $customer->phone) );
					}
				}
				
				if( !empty($timeZone) )
				{
					if( $type == 'customer' )
					{
						$nextAvailableCallingTimeStart = new DateTime($sched['schedule_start'], new DateTimeZone(timezone_name_from_abbr($timeZone)) );
						$nextAvailableCallingTimeEnd = new DateTime($sched['schedule_end'], new DateTimeZone(timezone_name_from_abbr($timeZone)) );
						
						$nextAvailableCallingTimeStart->setTimezone(new DateTimeZone('America/Denver'));
						$nextAvailableCallingTimeEnd->setTimezone(new DateTimeZone('America/Denver'));
						
						
						
						// for debugging
						// if( $customer->id == 675  )
						// {
							// echo '<br>';
							// echo '<br>';
							// echo 'timeZone: ' . $timeZone;
							// echo '<br>';
							// echo 'schedule_start: ' . $sched['schedule_start'];
							// echo '<br>';
							// echo 'schedule_end: ' . $sched['schedule_end'];
							// echo '<br>';
							// echo 'currentDateTime: ' . $currentDateTime->format('g:i A');
							// echo '<br>';
							// echo 'nextAvailableCallingTimeStart: ' . $nextAvailableCallingTimeStart->format('g:i A');
							// echo '<br>';
							// echo 'nextAvailableCallingTimeEnd: ' . $nextAvailableCallingTimeEnd->format('g:i A');
							// echo '<br>';
							// echo '<br>';
							// exit;
						// }
					
						$nextAvailableCallingTimeStart->setTimezone(new DateTimeZone('America/Denver'));
						$nextAvailableCallingTime = $nextAvailableCallingTimeStart->format('g:i A');

						if( strtotime($currentDateTime->format('g:i A')) >= strtotime($nextAvailableCallingTimeStart->format('g:i A')) && strtotime($currentDateTime->format('g:i A')) <= strtotime($nextAvailableCallingTimeEnd->format('g:i A')) )
						{
							$nextAvailableCallingTime = 'Now';
						}
						
						if( strtotime($currentDateTime->format('g:i A')) >= strtotime($nextAvailableCallingTimeStart->format('g:i A')) && strtotime($currentDateTime->format('g:i A')) >= strtotime($nextAvailableCallingTimeEnd->format('g:i A')) )
						{
							$nextAvailableCallingTime = 'Next Shift';
						}
					}
					else 
					{
						$currentDateTime = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('America/Denver'));
						$leadLocalTime = $currentDateTime->setTimezone(new DateTimeZone(timezone_name_from_abbr($timeZone)));
					
						if( strtotime($leadLocalTime->format('g:i A')) >= strtotime($sched['schedule_start']) && strtotime($leadLocalTime->format('g:i A')) <= strtotime($sched['schedule_end']) )
						{
							$nextAvailableCallingTime = 'Now';
						}
						
						if( strtotime($leadLocalTime->format('g:i A')) >= strtotime($sched['schedule_start']) && strtotime($leadLocalTime->format('g:i A')) >= strtotime($sched['schedule_end']) )
						{
							$nextAvailableCallingTime = 'Next Shift';
						}
					}
				}
			}
		}
		else
		{
			$nextAvailableCallingTime = 'Next Shift';
		}
		
		return $nextAvailableCallingTime;
	}

	public function checkLeadRetryTime($lead)
	{
		$leadIsCallable = false;
		
		$latestCall = LeadCallHistory::model()->find(array(
			'condition' => 'lead_id = :lead_id',
			'params' => array(
				':lead_id' => $lead->id,
			),
			'order' => 'date_created DESC'
		));
		
		
		if( $latestCall )
		{
			if( $latestCall->is_skill_child == 1 )
			{
				if( isset($latestCall->skillChildDisposition) && !empty($latestCall->skillChildDisposition->retry_interval) )
				{
					if( time() >= (strtotime($latestCall->end_call_time) + $latestCall->skillChildDisposition->retry_interval) )
					{
						$leadIsCallable = true;
					}
				}
			}
			else
			{
				if( isset($latestCall->skillDisposition) && !empty($latestCall->skillDisposition->retry_interval) )
				{
					if( time() >= (strtotime($latestCall->end_call_time) + $latestCall->skillDisposition->retry_interval) )
					{
						$leadIsCallable = true;
					}
				}
			}
		}
		else
		{
			$leadIsCallable = true;
		}
		
		return $leadIsCallable;
	}
}

?>