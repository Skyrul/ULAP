<?php 

class OldCronQueueViewerController extends Controller
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
		ini_set('memory_limit', '1024M');
		set_time_limit(0);
		
		date_default_timezone_set('America/Denver');
		
		echo 'Process started at ' . date('H:i:s');
		
		echo '<br><br>';
		
		// $this->unsetLeadHopperOfIdleAgents();
		
		//check for customer queue without agent and set their dials until reset back to 20
		// CustomerQueueViewer::model()->updateAll(array('dials_until_reset' => 20), 'dials_until_reset < 20 AND (call_agent IS NULL OR call_agent="")');	
		
		//remove leads with end call, dispo and done status on hopper
		// $leadHopperEntries = LeadHopper::model()->findAll(array(
			// 'condition' => 'status IN ("DONE", "INCALL")',
		// ));
		
		// if( $leadHopperEntries )
		// {	
			// foreach( $leadHopperEntries as $leadHopperEntry )
			// {
				// $lead = $leadHopperEntry->lead;
				// $customer = $leadHopperEntry->customer;
				
				// if( !empty($lead->timezone) )
				// {
					// $timeZone = $lead->timezone;
				// }
				// else
				// {
					// $timeZone = AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $customer->phone) );
				// }
				
				// $currentDateTime = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('America/Denver'));

				// $leadLocalTime = $currentDateTime->setTimezone(new DateTimeZone(timezone_name_from_abbr($timeZone)));
				
				// if( $leadHopperEntry->status == 'DONE' && in_array($leadHopperEntry->type, array(LeadHopper::TYPE_CONTACT, LeadHopper::TYPE_LEAD_SEARCH)) )
				// {
					// $leadHopperEntry->delete();
				// }

				// if( ($leadHopperEntry->status == 'DONE' || $leadHopperEntry->status == 'INCALL') && $leadHopperEntry->type == LeadHopper::TYPE_CALLBACK && strtotime($leadLocalTime->format('Y-m-d g:i a')) >= strtotime($leadHopperEntry->callback_date) )
				// {
					// $leadHopperEntry->status = 'READY';
					// $leadHopperEntry->save(false);
				// }
				
				
				// if( ($leadHopperEntry->status == 'DONE' || $leadHopperEntry->status == 'INCALL') && $leadHopperEntry->type == LeadHopper::TYPE_CONFIRMATION_CALL && time() >= strtotime($leadHopperEntry->appointment_date) )
				// {
					// $leadHopperEntry->status = 'READY';
					// $leadHopperEntry->save(false);
				// }
			// }
		// }

		// exit;
		
		$customerQueueViewerUpdates = 0;
		$leadAddedToHopper = 0;
		
		$customerPriorityFlag = CustomerQueueViewerSettings::model()->findByPk(1);
		$customerPriorityCurrentOffset = CustomerQueueViewerSettings::model()->findByPk(2);
		$customerPriorityQueryOngoing = CustomerQueueViewerSettings::model()->findByPk(3);
		$leadHopperReordering = CustomerQueueViewerSettings::model()->findByPk(4);
		
		// $customerReachedResets = CustomerQueueViewer::model()->findAll(array(
			// 'condition' => 'dials_until_reset=0',
		// ));
		
		// if( $customerReachedResets )
		// {
			//update Customer Priority Re-evaluation Flag
			// $customerPriorityFlag->value = 1;
			// $customerPriorityFlag->save(false);
		// }
		
		
		// $totalCustomerSkills = CustomerSkill::model()->count(array(	
			// 'with' => 'customer',
			// 'condition' => 't.status=1 AND customer.status=1 AND customer.is_deleted=0',
		// ));
		
		$customerSkills = CustomerSkill::model()->findAll(array(
			'with' => 'customer',
			'condition' => 't.status=1 AND customer.status=1 AND customer.is_deleted=0',
			// 'limit' => 30,
			// 'offset' => $customerPriorityCurrentOffset->value,
		)); 
		
		if( $customerPriorityQueryOngoing->value == 0 && $leadHopperReordering->value == 0 )
		{
			$customerPriorityQueryOngoing->value = 1;
			$customerPriorityQueryOngoing->save(false);
			
			if( $customerSkills )
			{
				foreach( $customerSkills as $customerSkill )
				{
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

					
					if( isset($customerSkill->customer) && $isCallablCustomer )
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
						$availableCallingBlock_A = '';
						$availableCallingBlock_B = '';
						$availableCallingBlock_C = '';
						
						$callAgent = '';
						
						$nextAvailableCallingTime = $this->checkTimeZone($customerSkill);
						
						$numberOfWorkingDays = $this->getWorkingDaysForThisMonth(date('Y-m-1'), date('Y-m-t'), 'totalCount');
						$dialingDaysInBillingCycle = $this->getWorkingDaysForThisMonth(date('Y-m-1'), date('Y-m-t'), 'pastCount');	
						
						$customer = Customer::model()->findByPk($customerSkill->customer_id);
						$skill = Skill::model()->findByPk($customerSkill->skill_id);
						
						$maxDials = $skill->max_dials;
						
						$leads = Lead::model()->findAll(array(
							'with' => array('list'),
							'condition' => 'list.skill_id = :skill_id AND list.customer_id = :customer_id AND t.type=1 AND t.status=1',
							'params' => array(
								':skill_id' => $skill->id,
								':customer_id' => $customer->id,
							),
						));
						
						$existingQueueViewer = CustomerQueueViewer::model()->find(array(
							'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id',
							'params' => array(
								':customer_id' => $customerSkill->customer_id,
								':contract_id' => $customerSkill->contract_id,
								':skill_id' => $customerSkill->skill_id,
							),
						));
						
						$contract = $customerSkill->contract;
						
						if(isset($contract))
						{
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
								}
							}
						
							if( count($leads) > 0 && $totalLeads > 0 )
							{
								foreach( $leads as $lead )
								{
									if( $lead->type == 1 && $lead->status == 1 && $lead->list->status == 1 && $lead->number_of_dials < $maxDials )
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
											if( isset($latestCall->skillDisposition) && !empty($latestCall->skillDisposition->retry_interval) )
											{
												if( time() >= (strtotime($latestCall->end_call_time) + $latestCall->skillDisposition->retry_interval) )
												{
													$leadIsCallable = true;
												}
											}
										}
										else
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
									
									if( $lead->number_of_dials > 0 )
									{
										$currentDials = $currentDials + $lead->number_of_dials;
										
										$calledLeadCount++;
									}
									
									
									
									//get total potential dials for lead volume
									if( $customerSkill->contract->fulfillment_type == 2 )
									{
										$leadCallLogs = LeadCallHistory::model()->count(array(
											'condition' => 'lead_id = :lead_id',
											'params' => array(
												':lead_id' => $lead->id,
											),
										));
									
										$totalPotentialDials += ($maxDials - $leadCallLogs);
									}
									
									
									//get available calling blocks
									if($availableCallingBlock_A == '' && $lead->number_of_dials == 0)
									{
										$availableCallingBlock_A = 'A';
										$availableCallingBlock_B = 'B';
										$availableCallingBlock_C = 'C';
									}
						
									if( $availableCallingBlock_B == '' )
									{
										$periodB = LeadCallHistory::model()->find(array(
											'condition' => 'lead_id = :lead_id AND dial_number=1',
											'params' => array(
												':lead_id' => $lead->id,
											),
										));
										
										if( count($periodB) > 0)
										{
											$availableCallingBlock_B = 'B';
										}
									}
									
									if( $availableCallingBlock_C == '' )
									{
										$periodC = LeadCallHistory::model()->find(array(
											'condition' => 'lead_id = :lead_id AND dial_number=2',
											'params' => array(
												':lead_id' => $lead->id,
											),
										));
										
										if( count($periodC) > 0)
										{
											$availableCallingBlock_C = 'C';
										}
									}
									
									if( $availableCallingBlocks == '' )
									{
										$availableCallingBlocks = $availableCallingBlock_A.$availableCallingBlock_B.$availableCallingBlock_C;
									}
								}
							
								
								//get total potential dials
								if( $customerSkill->contract->fulfillment_type == 1 )
								{
									$appointmentSetCount = CalendarAppointment::model()->count(array(
										'with' => 'calendar',
										'condition' => 'calendar.customer_id = :customer_id AND t.title IN ("APPOINTMENT SET", "INSERT APPOINTMENT")',
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
								

								//get the latest agent that is calling the customer's leads
								$latestCallers = LeadHopper::model()->findAll(array(
									'group' => 'agent_account_id',
									'condition' => 'customer_id = :customer_id AND type != :lead_search AND agent_account_id IS NOT NULL AND status IN ("INCALL", "DISPO", "CONFLICT", "CALLBACK", "CONFIRMATION")',
									'params' => array(
										':customer_id' => $customer->id,
										':lead_search' => LeadHopper::TYPE_LEAD_SEARCH,
									),
									// 'order' => 'date_created DESC',
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
						
							if( $existingQueueViewer)
							{
								$model = $existingQueueViewer;
								
								if( $customerPriorityFlag->value == 0 )
								{
									$priority = $existingQueueViewer->priority;
								}
							}
							else
							{
								$model = new CustomerQueueViewer;
								$model->initial_priority = $priority;
							}
						
						
							$model->setAttributes(array(
								'customer_id' => $customerSkill->customer_id,
								'contract_id' => $customerSkill->contract_id,
								'skill_id' => $customerSkill->skill_id,
								'customer_name' => $customerSkill->customer->firstname.' '.$customerSkill->customer->lastname,
								'skill_name' => $customerSkill->skill->skill_name,
								'priority_reset_date' => date('m-1-Y', strtotime('+1 month', strtotime('-1 day'))),
								#'initial_priority' => $priority,
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
								$customerQueueViewerUpdates++;
							}
							
						}
					}
					else
					{
						LeadHopper::model()->deleteAll(array(
							'condition' => 'customer_id = :customer_id AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
							'params' => array(
								':customer_id' => $customerSkill->customer_id,
							),
						));
					}
					
					
					$customerPriorityCurrentOffset->value = $customerPriorityCurrentOffset->value + 1;
					$customerPriorityCurrentOffset->save(false);
				}
			}
		}
	
	
		$customerPriorityQueryOngoing->value = 0;
		$customerPriorityQueryOngoing->save(false);
		
		echo '<br><br>Customer Queue Viewer Table Changes: '.$customerQueueViewerUpdates;
		
		echo '<br><br> Process ended at '.date('H:i:s');
		
		exit;
	
		if( $customerReachedResets )
		{
			LeadHopper::model()->deleteAll(array('condition' => '(status="DONE" AND type=1) OR (type = 4)'));
	
			foreach($customerReachedResets as $customerReachedReset)
			{
				$customerReachedReset->call_agent = '';
				$customerReachedReset->dials_until_reset = 20;
				
				if( $customerReachedReset->save(false) )
				{
					LeadHopper::model()->deleteAll(array(
						'condition' => 'status IN ("READY", "INCALL", "DONE") AND type=1 AND customer_id = :customer_id',
						'params' => array(
							':customer_id' => $customerReachedReset->customer_id,
						),
					));
				}
			}
		}

		//check if there are remaining customer queue viewer to be evaluated
		if( $customerPriorityCurrentOffset->value >= $totalCustomerSkills )
		{  
			$customerPriorityFlag->value = 0;
			$customerPriorityFlag->save(false);
			
			$customerPriorityCurrentOffset->value = 0;
			$customerPriorityCurrentOffset->save(false);
			
			$leadHopperReordering->value = 1;
			$leadHopperReordering->save(false);
			
			//get all active customer queues with callable leads
			$customerWhiteQueues = CustomerQueueViewer::model()->findAll(array(
				'condition' => 'available_leads > 0',
				'order' => 'priority DESC',						
			)); 
			
			//sort customers by priority and insert available leads to hopper
			if( $customerWhiteQueues )
			{
				foreach($customerWhiteQueues as $customerQueue)
				{
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
											'limit' => 30,
										));
										
										if( $leads )
										{
											$leadHopperCounter = LeadHopper::model()->count(array(
												'condition' => 'status="READY" AND customer_id = :customer_id',
												'params' => array(
													':customer_id' => $customerQueue->customer_id,
												),
											));
											
											foreach( $leads as $lead )
											{
												$leadIsCallable = false;
												
												$existingHopperEntry = LeadHopper::model()->find(array(
													'condition' => 'lead_id = :lead_id',
													'params' => array(
														':lead_id' => $lead->id,
													),
												));

												if( empty($existingHopperEntry) )
												{
													if( $this->checkTimeZone($customerSkill, 'lead', $lead) == 'Now' && $leadHopperCounter < 30 )
													{
														$latestCall = LeadCallHistory::model()->find(array(
															'condition' => 'lead_id = :lead_id',
															'params' => array(
																':lead_id' => $lead->id,
															),
															'order' => 'date_created DESC'
														));
														
														
														if( $latestCall )
														{
															if( isset($latestCall->skillDisposition) && !empty($latestCall->skillDisposition->retry_interval) )
															{
																if( time() >= (strtotime($latestCall->end_call_time) + $latestCall->skillDisposition->retry_interval) )
																{
																	$leadIsCallable = true;
																}
															}
														}
														else
														{
															$leadIsCallable = true;
														}
													}
												}

												if( $leadIsCallable )
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
														
														echo 'Added Lead of Customer ID: '.$customerQueue->customer_id.' - '.$customerQueue->customer_name.' => Lead ID: '.$lead->id.' - Name: '.$lead->getFullName();
														echo '<br>';
													}
												}
												else
												{
													// if( $this->checkTimeZone($customerSkill, 'lead', $lead)  != 'Now' )
													// {
														echo 'Deleted Lead ID: ' . $lead->id.' - '.$lead->getFullName() .' => Timezone:'. $lead->timezone;
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
		
			$leadHopperReordering->value = 0;
			$leadHopperReordering->save(false);
		}
		
		$customerPriorityQueryOngoing->value = 0;
		$customerPriorityQueryOngoing->save(false);
		
		echo '<br><br> Process ended at '.date('H:i:s');
		
		echo '<br><br>Customer Queue Viewer Table Changes: '.$customerQueueViewerUpdates;
		
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

	public function unsetLeadHopperOfIdleAgents()
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
}

?>