<?php 

error_reporting(E_ALL);
ini_set('display_errors', 1);

ini_set('memory_limit', '20000M');
set_time_limit(0);

class CronQueueViewerController extends Controller
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
		date_default_timezone_set('America/Denver');
		
		echo 'Process started at ' . date('H:i:s');
		
		echo '<br><br>';
		
		$customerQueueViewerEvaluated = 0;
		$customerQueueViewerUpdates = 0;
		$leadAddedToHopper = 0;
		$processCtr = 1;
		
		
		//Queue Viewer Settings
		$customerPriorityFlag = CustomerQueueViewerSettings::model()->findByPk(1);
		
		$customerPriorityCurrentOffset = CustomerQueueViewerSettings::model()->findByPk(2);
		$customerPriorityQueryOngoing = CustomerQueueViewerSettings::model()->findByPk(3);
		
		$leadHopperReordering = CustomerQueueViewerSettings::model()->findByPk(4);
		$leadHopperReorderOffset = CustomerQueueViewerSettings::model()->findByPk(5);
		
		$currentRecordInQueue = CustomerQueueViewerSettings::model()->findByPk(6);
		$currentRecordInQueueChecker = CustomerQueueViewerSettings::model()->findByPk(7);
		
		if( time() > strtotime('today 12:00 am') && time() < strtotime('today 5:00 am') && $customerPriorityCurrentOffset->value == 0 && $customerPriorityQueryOngoing->value == 0 && $leadHopperReordering->value == 0 )
		{
			exit;
		}

		$callableCustomers = CustomerQueueViewer::model()->findAll(array(
			'select' => 'customer_id',
			'condition' => 'available_leads > 0',
		));
		
		$customerQueueViewerIds = array();
		
		foreach( $callableCustomers as $callableCustomer )
		{
			$customerQueueViewerIds[] = $callableCustomer->customer_id;
		}
		
		$customerSkillsCount = CustomerSkill::model()->count(array(
			'with' => array('skill', 'contract'),
			'condition' => 't.status=1 AND skill.id IS NOT NULL AND contract.id IS NOT NULL AND customer_id AND customer_id IN ('.implode(', ', $customerQueueViewerIds).')',
		)); 
		
		$customerSkills = CustomerSkill::model()->findAll(array(
			'with' => array('skill', 'contract'),
			'condition' => 't.status=1 AND skill.id IS NOT NULL AND contract.id IS NOT NULL AND customer_id AND customer_id IN ('.implode(', ', $customerQueueViewerIds).')',
			'limit' => 50,
			'offset' => $customerPriorityCurrentOffset->value
		)); 	
		
		if( $currentRecordInQueue->value == $customerPriorityCurrentOffset->value && $customerPriorityQueryOngoing->value == 1 )
		{
			$currentRecordInQueueChecker->value = $currentRecordInQueueChecker->value + 1;
			$currentRecordInQueueChecker->save(false);
		}
		
		if( $currentRecordInQueueChecker->value == 1 )
		{
			$customerPriorityQueryOngoing->value = 0;
			$customerPriorityQueryOngoing->save(false);
			
			$customerPriorityCurrentOffset->value = $customerPriorityCurrentOffset->value+1;
			$customerPriorityCurrentOffset->save(false);
		}
		
		if( $customerSkills && $customerPriorityQueryOngoing->value == 0 && $leadHopperReordering->value == 0 && $customerPriorityCurrentOffset->value < $customerSkillsCount )
		{
			$customerPriorityQueryOngoing->value = 1;
			$customerPriorityQueryOngoing->save(false);
			
			foreach( $customerSkills as $customerSkill )
			{
				$customerIsCallable = false;	

				if( isset($customerSkill->contract) && isset($customerSkill->customer) && $customerSkill->customer->status == 1 && $customerSkill->customer->is_deleted == 0 && time() >= strtotime($customerSkill->start_month) )
				{
					$customerIsCallable = true;
				}
				
				if( $customerSkill->is_contract_hold == 1 )
				{
					if( !empty($customerSkill->is_contract_hold_start_date) && !empty($customerSkill->is_contract_hold_end_date) )
					{
						if( time() >= strtotime($customerSkill->is_contract_hold_start_date) && time() <= strtotime($customerSkill->is_contract_hold_end_date) )
						{
							$customerIsCallable = false;
						}
					}
				}
				
				if( !empty($customerSkill->end_month) && date('Y', strtotime($customerSkill->end_month)) >= 2015 )
				{
					if( time() >= strtotime($customerSkill->end_month) )
					{
						$customerIsCallable = false;
					}
				}
					
				if( $customerIsCallable )
				{
					$existingQueueViewer = CustomerQueueViewer::model()->find(array(
						'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id',
						'params' => array(
							':customer_id' => $customerSkill->customer_id,
							':contract_id' => $customerSkill->contract_id,
							':skill_id' => $customerSkill->skill_id,
						),
					));
					
					if( $existingQueueViewer )
					{
						if( !empty($existingQueueViewer->removal_start_date) && !empty($existingQueueViewer->removal_end_date) )
						{
							if( time() >= strtotime($existingQueueViewer->removal_start_date) && time() <= strtotime($existingQueueViewer->removal_end_date) )
							{
								$customerIsCallable = false;
							}
						}
						
						if( $customerIsCallable )
						{
							$leadCounter = LeadHopper::model()->count(array(
								'condition' => 'status="READY" AND customer_id = :customer_id',
								'params' => array(
									':customer_id' => $existingQueueViewer->customer_id,
								),
							));
							
							if( $existingQueueViewer->dials_until_reset == 0 || $leadCounter == 0 )
							{
								if( $this->runCustomerEvaluation($customerSkill, $existingQueueViewer) )
								{
									$existingQueueViewer->dials_until_reset = 20;
									$existingQueueViewer->call_agent = null;
									$existingQueueViewer->save(false);
									
									$customerQueueViewerEvaluated++;
									$customerQueueViewerUpdates++;
								}
							}
							else
							{
								$existingQueueViewer->next_available_calling_time = $this->checkTimeZone($customerSkill);
								$existingQueueViewer->call_agent = null;
								
								//get the latest agent that is calling the customer's leads
								$latestCallers = LeadHopper::model()->findAll(array(
									'group' => 'agent_account_id',
									'condition' => 'customer_id = :customer_id AND type != :lead_search AND agent_account_id IS NOT NULL AND status IN ("INCALL")',
									'params' => array(
										':customer_id' => $existingQueueViewer->customer_id,
										':lead_search' => LeadHopper::TYPE_LEAD_SEARCH,
									),
								));
								
								if( $latestCallers )
								{
									$callAgent = '';
									
									foreach( $latestCallers as $latestCaller )
									{
										$callAgent .= $latestCaller->currentAgentAccount->getFullName();
										$callAgent .= ', ';
									}
									
									$existingQueueViewer->call_agent = rtrim($callAgent, ', ');
								}
								
								if( $existingQueueViewer->save(false) )
								{
									$customerQueueViewerUpdates++;
								}
								
							}
						}
						else
						{
							$existingQueueViewer = CustomerQueueViewer::model()->find(array(
								'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id',
								'params' => array(
									':customer_id' => $customerSkill->customer_id,
									':contract_id' => $customerSkill->contract_id,
									':skill_id' => $customerSkill->skill_id,
								),
							));
							
							if( $existingQueueViewer )
							{
								$existingQueueViewer->delete();
							}
							
							LeadHopper::model()->deleteAll(array(
								'condition' => 'customer_id = :customer_id AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
								'params' => array(
									':customer_id' => $customerSkill->customer_id,
								),
							));
						}
					}
					else
					{
						if( $this->runCustomerEvaluation($customerSkill, $existingQueueViewer) )
						{
							$customerQueueViewerEvaluated++;
							$customerQueueViewerUpdates++;
						}
					}
				
					
					
					//start inserting leads
					$customerQueue = CustomerQueueViewer::model()->find(array(
						'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id',
						'params' => array(
							':customer_id' => $customerSkill->customer_id,
							':contract_id' => $customerSkill->contract_id,
							':skill_id' => $customerSkill->skill_id,
						),
					));
					
					if( $customerQueue )
					{
						$lists = Lists::model()->findAll(array(
							'together' => true,
							'condition' => 't.customer_id = :customer_id AND skill_id = :skill_id AND t.status=1',
							'params' => array(
								':customer_id' => $customerQueue->customer_id,
								':skill_id' => $customerQueue->skill_id,
							),
						));
						
						if( $customerQueue->next_available_calling_time == 'Now' )
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
											'with' => array('list', 'list.skill'),
											'condition' => '
												list.status = 1 
												AND t.list_id = :list_id
												AND t.type=1 
												AND t.status=1
												AND t.number_of_dials < (skill.max_dials * 3)
											',
											'params' => array(
												':list_id' => $list->id,
											),
											'order' => $order,
										));
										
										$leadCounter = LeadHopper::model()->count(array(
											'condition' => 'status IN ("READY") AND type=1 AND customer_id = :customer_id',
											'params' => array(
												':customer_id' => $customerQueue->customer_id,
											),
										)); 
										
										if( count($leads) > 0 && $leadCounter == 0 )
										{
											$customerLeadAdded = 1;
											
											foreach( $leads as $lead )
											{
												$existingHopperEntry = LeadHopper::model()->find(array(
													'condition' => 'lead_id = :lead_id',
													'params' => array(
														':lead_id' => $lead->id,
													),
												));

												if( empty($existingHopperEntry) )
												{
													if( $this->checkTimeZone($customerSkill, 'lead', $lead) == 'Now' )
													{
														if( $this->checkLeadRetryTime($lead) )
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
																$customerLeadAdded++;
																
																echo 'Added Lead of Customer ID: '.$customerQueue->customer_id.' - '.$customerQueue->customer_name.' => Lead ID: '.$lead->id.' - Name: '.$lead->getFullName() .' => Timezone:'. $lead->timezone;
																echo '<br>';
															}
														}
														else
														{
															echo 'Not Aded Not yet in Retry Time Lead of Customer ID: '.$customerQueue->customer_id.' - '.$customerQueue->customer_name. ' => Lead ID: ' . $lead->id.' - '.$lead->getFullName() .' => Timezone:'. $lead->timezone.' => '.$this->checkTimeZone($customerSkill, 'lead', $lead);
															echo '<br>';
												
															LeadHopper::model()->delete(array(
																'condition' => 'lead_id = :lead_id AND customer_id = :customer_id AND status IN ("READY", "INCALL", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
																'params' => array(
																	':lead_id' => $lead->id,
																),
															));
														}
													}
													else
													{
														echo 'Not Aded Not in Call Schedule Lead of Customer ID: '.$customerQueue->customer_id.' - '.$customerQueue->customer_name. ' => Lead ID: ' . $lead->id.' - '.$lead->getFullName() .' => Timezone:'. $lead->timezone.' => '.$this->checkTimeZone($customerSkill, 'lead', $lead);
														echo '<br>';
											
														LeadHopper::model()->delete(array(
															'condition' => 'lead_id = :lead_id AND customer_id = :customer_id AND status IN ("READY", "INCALL", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
															'params' => array(
																':lead_id' => $lead->id,
															),
														));
													}
												}
												else
												{
													echo 'Not Added Existing Lead of Customer ID: '.$customerQueue->customer_id.' - '.$customerQueue->customer_name. ' => Lead ID: ' . $lead->id.' - '.$lead->getFullName() .' => Timezone:'. $lead->timezone.' => '.$this->checkTimeZone($customerSkill, 'lead', $lead);
													echo '<br>';
										
													LeadHopper::model()->delete(array(
														'condition' => 'lead_id = :lead_id AND customer_id = :customer_id AND status IN ("READY", "INCALL", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
														'params' => array(
															':lead_id' => $lead->id,
														),
													));
												}
											
												if( $customerLeadAdded == 20 )
												{
													break;
												}
											}
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
				else
				{
					$existingQueueViewer = CustomerQueueViewer::model()->find(array(
						'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id',
						'params' => array(
							':customer_id' => $customerSkill->customer_id,
							':contract_id' => $customerSkill->contract_id,
							':skill_id' => $customerSkill->skill_id,
						),
					));
					
					if( $existingQueueViewer )
					{
						$existingQueueViewer->delete();
					}
					
					LeadHopper::model()->deleteAll(array(
						'condition' => 'customer_id = :customer_id AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
						'params' => array(
							':customer_id' => $customerSkill->customer_id,
						),
					));
				}
			
			
				$customerPriorityCurrentOffset->value = $customerPriorityCurrentOffset->value + 1;
				$customerPriorityCurrentOffset->save(false);
				
				$currentRecordInQueue->value = $customerPriorityCurrentOffset->value;
				$currentRecordInQueue->save(false);
				
				$currentRecordInQueueChecker->value = 0;
				$currentRecordInQueueChecker->save(false);
				
				echo $processCtr++;
				echo '<br>';
			}
			
			$customerPriorityQueryOngoing->value = 0;
			$customerPriorityQueryOngoing->save(false);
		}
		
		if( $customerPriorityCurrentOffset->value >= $customerSkillsCount )
		{
			$customerPriorityCurrentOffset->value = 0;
			$customerPriorityCurrentOffset->save(false);
		}
		
		
		// echo '<br><br>Customer Queue Viewer Table Changes: '.$customerQueueViewerUpdates;
		
		// echo '<br><br>Customer Queue Viewer Evaluated: '.$customerQueueViewerEvaluated;
		
		// echo '<br><br> Process ended at '.date('H:i:s');
		
		// exit;
		
		echo '<br><br> Process ended at '.date('H:i:s');
		
		echo '<br><br>Customer Queue Viewer Table Changes: '.$customerQueueViewerUpdates;
		
		echo '<br><br>Customer Queue Viewer Evaluated: '.$customerQueueViewerEvaluated;
		
		echo '<br><br>Lead Added to Hopper: '.$leadAddedToHopper;
		
		echo '<br><br>Hopper Count: '.LeadHopper::model()->count();
	}
	
	
	
	private function getWorkingDaysForThisMonth($startDate, $endDate, $returnType='array')
	{
		date_default_timezone_set('America/Denver');
		
		$workdays = array();
		
		$holidays = array(
			strtotime(date('Y-01-01')), // New Year's Day
			//strtotime(date('Y-01-18')), // Birthday of Martin Luther King, Jr.
			strtotime(date('Y-02-15')), // Washington’s Birthday
			strtotime(date('Y-05-30')), // Memorial Day
			strtotime(date('Y-07-04')), // Independence Day
			strtotime(date('Y-09-05')), // Labor Day
			strtotime(date('Y-10-10')), // Columbus Day
			strtotime(date('Y-11-11')), // Veterans Day
			strtotime(date('Y-11-24')), // Thanksgiving Day
			strtotime(date('Y-12-26')), // Christmas Day
		);
		
		$type = CAL_GREGORIAN;
		$month = date('n'); // Month ID, 1 through to 12.
		$year = date('Y'); // Year in 4 digit 2009 format.
		$day_count = cal_days_in_month($type, $month, $year); // Get the amount of days
		
		
		$begin = strtotime($startDate);
		$end = strtotime($endDate);

		
		//loop through all days
		while($begin <= $end)
		{
			if( !in_array($begin, array(strtotime($year.'-5-25'), strtotime($year.'-7-4'))) )
			{
				$get_name = date('l', $begin); //get week day
				$day_name = substr($get_name, 0, 3); // Trim day name to 3 chars
				
				if($returnType == 'pastCount')
				{
					//if not a weekend and is past date add day to array
					if( $day_name != 'Sun' && $day_name != 'Sat' )
					{
						if( time() > $begin )
						{
							$workdays[] = $begin;
						}
					}
				}
				else
				{
					//if not a weekend add day to array
					if( $day_name != 'Sun' && $day_name != 'Sat' )
					{
						$workdays[] = $begin;
					}
				}
			}
			
			$begin += 86400; // +1 day
		}

		
		$workdays = array_diff($workdays, $holidays);
		
		if($returnType == 'array')
		{
			return $workdays;
		}
		else
		{
			return count($workdays);
		}
	}

	private function unsetLeadHopperOfIdleAgents()
	{
		$leadHoppers = LeadHopper::model()->findAll(array(
			'condition' => 'agent_account_id IS NOT NULL',
			'group' => 'agent_account_id',
		));
		
		if( $leadHoppers )
		{
			foreach($leadHoppers as $leadHopper)
			{
				$agentIsIdle = false;
				
				$currentLoginState = AccountLoginState::model()->find(array(
					'condition' => 'account_id = :account_id',
					'params' => array(
						':account_id' => $leadHopper->agent_account_id,
					),
					'order' => 'date_created DESC',
				));

				if( $currentLoginState )
				{
					if( strtotime( $currentLoginState->date_updated ) < strtotime('-2 seconds') )
					{  
						$agentIsIdle = true;
					}
					else
					{
						echo $currentLoginState->id.' | ' . $currentLoginState->date_updated.' | '.$currentLoginState->account->getFullName();
						echo '<br>';
					}
				}
				else
				{
					$agentIsIdle = true;
				}
				
				if( $agentIsIdle )
				{
					LeadHopper::model()->updateAll(array('agent_account_id' => null), 'agent_account_id = ' . $leadHopper->agent_account_id);
					CustomerQueueViewer::model()->updateAll(array('dials_until_reset' => 20, 'call_agent'=>null), 'call_agent = ' . $leadHopper->agent_account_id);	
					
					$customerQueueViewer = CustomerQueueViewer::model()->find(array(
						'condition' => 'customer_id = :customer_id',
						'params' => array(
							':customer_id' => $leadHopper->customer_id,
						),
					));
				
					if( $customerQueueViewer )
					{
						$customerQueueViewer->dials_until_reset = 0;
						$customerQueueViewer->save(false);
					}
				}
			}
		}
	}
	
	private function checkTimeZone($customerSkill, $type='customer', $lead=null)
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
					$timeZone = $customer->getTimeZone();
				}
				else
				{
					if( !empty($lead->timezone) )
					{
						$timeZone = $lead->timezone;
					}
					else
					{
						$timeZone = $customer->getTimeZone();
					}
				}
				
				if( !empty($timeZone) )
				{
					$timeZone = timezone_name_from_abbr($timeZone);
													
					// if( strtoupper($lead->timezone) == 'AST' )
					// {
						// $timeZone = 'America/Puerto_Rico';
					// }
					
					// if( strtoupper($lead->timezone) == 'ADT' )
					// {
						// $timeZone = 'America/Halifax';
					// }
					
					if( $type == 'customer' )
					{
						$nextAvailableCallingTimeStart = new DateTime($sched['schedule_start'], new DateTimeZone($timeZone) );
						$nextAvailableCallingTimeEnd = new DateTime($sched['schedule_end'], new DateTimeZone($timeZone) );
						
						$nextAvailableCallingTimeStart->setTimezone(new DateTimeZone('America/Denver'));
						$nextAvailableCallingTimeEnd->setTimezone(new DateTimeZone('America/Denver'));

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
						
						if( in_array($nextAvailableCallingTime, array('7:00 AM', '7:30 AM', '8:00 AM', '8:30 AM', '9:00 AM')) && time() >= strtotime('today 4:00 am') )
						{
							$nextAvailableCallingTime = 'Now';
						}
					}
					else 
					{
						$nextAvailableCallingTimeStart = new DateTime($sched['schedule_start'], new DateTimeZone($timeZone) );
						$nextAvailableCallingTimeEnd = new DateTime($sched['schedule_end'], new DateTimeZone($timeZone) );
						
						$nextAvailableCallingTimeStart->setTimezone(new DateTimeZone('America/Denver'));
						$nextAvailableCallingTimeEnd->setTimezone(new DateTimeZone('America/Denver'));

						$nextAvailableCallingTimeStart->setTimezone(new DateTimeZone('America/Denver'));
						$nextAvailableCallingTime = $nextAvailableCallingTimeStart->format('g:i A');
						
						$currentDateTime = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('America/Denver'));
						$leadLocalTime = $currentDateTime->setTimezone(new DateTimeZone($timeZone));
					
						if( strtotime($leadLocalTime->format('g:i A')) >= strtotime($sched['schedule_start']) && strtotime($leadLocalTime->format('g:i A')) <= strtotime($sched['schedule_end']) )
						{
							$nextAvailableCallingTime = 'Now';
						}
						
						if( strtotime($leadLocalTime->format('g:i A')) >= strtotime($sched['schedule_start']) && strtotime($leadLocalTime->format('g:i A')) >= strtotime($sched['schedule_end']) )
						{
							$nextAvailableCallingTime = 'Next Shift';
						}
						
						if( in_array($nextAvailableCallingTime, array('7:00 AM', '7:30 AM', '8:00 AM', '8:30 AM', '9:00 AM')) && time() >= strtotime('today 4:00 am') )
						{
							$nextAvailableCallingTime = 'Now';
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

	private function checkLeadRetryTime($lead)
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
	
	private function runCustomerEvaluation($customerSkill, $existingQueueViewer)
	{	
		$priority = 0;
		$pace = 0;
		$currentDials = 0;
		$availableLeads = 0;
		$notCompletedLeads = 0;
		$totalLeads = 0;
		$totalPotentialDials = 0;
		$calledLeadCount = 0;
		$dialsNeeded = 0;
		$maxDials = 0;
		$appointmentSetCount = 0;
		
		$availableCallingBlocks = '';
		
		$availableCallingBlock_A = 0;
		$availableCallingBlock_B = 0;
		$availableCallingBlock_C = 0;
		
		$callAgent = '';
		
		$nextAvailableCallingTime = $this->checkTimeZone($customerSkill);
		
		$numberOfWorkingDays = $this->getWorkingDaysForThisMonth(date('Y-m-1'), date('Y-m-t'), 'totalCount');
		$dialingDaysInBillingCycle = $this->getWorkingDaysForThisMonth(date('Y-m-1'), date('Y-m-t'), 'pastCount');	
		
		$customer = Customer::model()->findByPk($customerSkill->customer_id);
		$skill = Skill::model()->findByPk($customerSkill->skill_id);
		$contract = $customerSkill->contract;
		
		$maxDials = $skill->max_dials;
		
		$leads = Lead::model()->findAll(array(
			'with' => array('list'),
			'condition' => 'list.skill_id = :skill_id AND list.customer_id = :customer_id AND t.type=1 AND t.status=1',
			'params' => array(
				':skill_id' => $skill->id,
				':customer_id' => $customer->id,
			),
		));
			
		if($contract->fulfillment_type != null )
		{
			if( $contract->fulfillment_type == Contract::TYPE_FULFILLMENT_GOAL_VOLUME )
			{
				if( !empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME]) )
				{
					foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME] as $key => $subsidyLevel )
					{
						$customerSkillLevelArray = $customerSkill->getCustomerSkillLevelArray();
						$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;

						if( $customerSkillLevelArrayGroup != null )
						{							
							if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
							{
								$totalLeads += ( $subsidyLevel['goal'] * $customerSkillLevelArrayGroup->quantity );
							}
						}
					}
				}
				
				$customerExtras = CustomerExtra::model()->findAll(array(
					'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id AND status=1 AND year = :year AND month = :month',
					'params' => array(
						':customer_id' => $customerSkill->customer_id,
						':contract_id' => $customerSkill->contract_id,
						':skill_id' => $customerSkill->skill_id,
						':year' => date('Y'),
						':month' => date('m'),
					),
				));
				
				if( $customerExtras )
				{
					foreach( $customerExtras as $customerExtra )
					{
						$totalLeads += $customerExtra->quantity;
					}
				}
			}
			else
			{
				if( !empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME]) )
				{
					foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME] as $key => $subsidyLevel )
					{
						$customerSkillLevelArray = $customerSkill->getCustomerSkillLevelArray();
						
						$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;
						
						if( $customerSkillLevelArrayGroup != null )
						{
							if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
							{
								$totalLeads += ( $subsidyLevel['high'] * $customerSkillLevelArrayGroup->quantity );
							}
						}
					}
				}
				
				$customerExtras = CustomerExtra::model()->findAll(array(
					'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id AND status=1 AND year = :year AND month = :month',
					'params' => array(
						':customer_id' => $customerSkill->customer_id,
						':contract_id' => $customerSkill->contract_id,
						':skill_id' => $customerSkill->skill_id,
						':year' => date('Y'),
						':month' => date('m'),
					),
				));
				
				if( $customerExtras )
				{
					foreach( $customerExtras as $customerExtra )
					{
						$totalLeads += $customerExtra->quantity;
					}
				}
			}
		}
		
	
		if( count($leads) > 0 && $totalLeads > 0 )
		{
			foreach( $leads as $lead )
			{
				$leadDialCount = 0;

				if( $lead->type == 1 && $lead->status == 1 && $lead->list->status == 1 && $lead->number_of_dials < ( $maxDials * 3) )
				{
					$leadIsCallable = false;
					
					$latestCall = LeadCallHistory::model()->find(array(
						'condition' => 'lead_id = :lead_id',
						'params' => array(
							':lead_id' => $lead->id,
						),
						'order' => 'date_created DESC'
					));
					
					$leadDialCount = LeadCallHistory::model()->count(array(
						'condition' => 'lead_id = :lead_id AND MONTH(t.date_created) = MONTH(NOW()) AND status !=4',
						'params' => array(
							':lead_id' => $lead->id,
						),
					));
					
					if( $leadDialCount > 0 )
					{
						$currentDials = $currentDials + $leadDialCount;
						
						$calledLeadCount++;
					}
					
					if( $this->checkLeadRetryTime($lead) )
					{
						$leadIsCallable = true;
					}
					
					if( $leadIsCallable )
					{
						$availableLeads++;
					}
					else
					{
						$notCompletedLeads++;
					}
				}
	
				//get total potential dials for lead volume
				if( $customerSkill->contract->fulfillment_type == 2 )
				{
					$totalPotentialDials += ($maxDials - $leadDialCount);
				}
				

				if( $leadDialCount < 1 )
				{
					$availableCallingBlock_A++;
				}
				
				if( $leadDialCount < 2 )
				{
					$availableCallingBlock_B++;
				}
				
				if( $leadDialCount < 3 )
				{
					$availableCallingBlock_C++;
				}
				
				if( $maxDials > 3 )
				{
					if( $leadDialCount < 4 )
					{
						$availableCallingBlock_A++;
					}
					
					if( $leadDialCount < 5 )
					{
						$availableCallingBlock_B++;
					}
					
					if( $leadDialCount < 6 )
					{
						$availableCallingBlock_C++;
					}
				}
			}
			
			//get available calling blocks
			if( $availableCallingBlocks == '' )
			{
				if( $availableCallingBlock_A > 0 )
				{
					$availableCallingBlocks .= 'A';
				}
				
				if( $availableCallingBlock_B > 0 )
				{
					$availableCallingBlocks .= 'B';
				}
				
				if( $availableCallingBlock_C > 0 )
				{
					$availableCallingBlocks .= 'C';
				}
			}
			
			//get total potential dials
			if( $customerSkill->contract->fulfillment_type == 1 )
			{
				$appointmentSetCount = CalendarAppointment::model()->count(array(
					'with' => 'calendar',
					'condition' => 'calendar.customer_id = :customer_id AND t.title IN ("APPOINTMENT SET", "INSERT APPOINTMENT") AND t.status !=4 AND t.lead_id IS NOT NULL AND MONTH(t.date_created) = MONTH(NOW())',
					'params' => array(
						'customer_id' => $customer->id,
					),
				));
				
				$totalPotentialDials = ($totalLeads - $appointmentSetCount);
			}
			
			
			//calculate customer priority
			if( $customerSkill->contract->fulfillment_type == 1 )
			{
				$pace = (($totalLeads / $numberOfWorkingDays) * $dialingDaysInBillingCycle);
				
				$pace = round($pace);
				
				$dialsNeeded = $pace - $appointmentSetCount;
				
				if( $dialsNeeded > 0 && $pace > 0 ) 
				{
					$priority = $dialsNeeded / $pace;
					
					$priority = round($priority, 4);
				}
			}
			else
			{							
				$pace = ((($totalLeads * $maxDials) / $numberOfWorkingDays) * $dialingDaysInBillingCycle);
				
				$pace = round($pace);
				
				$dialsNeeded = (((($totalLeads * $maxDials) / $numberOfWorkingDays) * $dialingDaysInBillingCycle) - $calledLeadCount);
		
				$priority = ( (((($totalLeads * $maxDials) / $numberOfWorkingDays) * $dialingDaysInBillingCycle) - $calledLeadCount) / ((($totalLeads * $maxDials) / $numberOfWorkingDays) * $dialingDaysInBillingCycle) );
			
				$priority = round($priority, 4);
			}
			
			if( $customer->id == 635 )
			{
				$priority = 2;
			}

			//get the latest agent that is calling the customer's leads
			$latestCallers = LeadHopper::model()->findAll(array(
				'group' => 'agent_account_id',
				'condition' => 'customer_id = :customer_id AND type != :lead_search AND agent_account_id IS NOT NULL AND status IN ("INCALL")',
				'params' => array(
					':customer_id' => $customer->id,
					':lead_search' => LeadHopper::TYPE_LEAD_SEARCH,
				),
			));
			
			if( $latestCallers )
			{
				foreach( $latestCallers as $latestCaller )
				{
					$callAgent .= $latestCaller->currentAgentAccount->getFullName();
					$callAgent .= ', ';
				}
				
				$callAgent = rtrim($callAgent, ', ');
			}
		} 
	
		if( $existingQueueViewer )
		{
			$model = $existingQueueViewer;
		}
		else
		{
			$model = new CustomerQueueViewer;
		}
	
		$model->setAttributes(array(
			'customer_id' => $customerSkill->customer_id,
			'contract_id' => $customerSkill->contract_id,
			'skill_id' => $customerSkill->skill_id,
			'customer_name' => $customerSkill->customer->firstname.' '.$customerSkill->customer->lastname,
			'skill_name' => $customerSkill->skill->skill_name,
			'priority_reset_date' => date('m-1-Y', strtotime('+1 month', strtotime('-1 day'))),
			'priority' => $priority,
			'pace' => $pace,
			'current_dials' => $currentDials,
			'current_goals' => $appointmentSetCount,
			'total_leads' => count($leads),
			'available_leads' => $availableLeads,
			'not_completed_leads' => $notCompletedLeads,
			'total_potential_dials' => $totalPotentialDials,
			'next_available_calling_time' => $nextAvailableCallingTime, //get next available calling time
			'available_calling_blocks' => $availableCallingBlocks,
			'call_agent' => $callAgent,
			'max_dials' => $maxDials,
			'dials_needed' => $dialsNeeded,
			'fulfillment_type' => $customerSkill->contract->fulfillment_type == 1 ? 'Goal' : 'Lead',
		));
		
		if( $model->save(false) )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}

?>