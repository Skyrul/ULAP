<?php 

error_reporting(E_ALL);
ini_set('display_errors', 1);

ini_set('memory_limit', '20000M');
set_time_limit(0);

class ManualQueueViewerController extends Controller
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
		// exit;
		
		date_default_timezone_set('America/Denver');
		
		echo 'Process started at ' . date('H:i:s');
		
		echo '<br><br>';
		
		$customerQueueViewerEvaluated = 0;
		$customerQueueViewerUpdates = 0;
		$leadAddedToHopper = 0;
		$processCtr = 1;
		
		
		//Queue Viewer Settings Variables
		$customerPriorityCurrentOffset = CustomerQueueViewerSettings::model()->findByPk(2);
		$customerPriorityQueryOngoing = CustomerQueueViewerSettings::model()->findByPk(3);
		
		$leadHopperReordering = CustomerQueueViewerSettings::model()->findByPk(4);
		$leadHopperReorderOffset = CustomerQueueViewerSettings::model()->findByPk(5);
		
		$currentRecordInQueue = CustomerQueueViewerSettings::model()->findByPk(6);
		$currentRecordInQueueChecker = CustomerQueueViewerSettings::model()->findByPk(7);
		
		// $condition = 'status = 1 AND available_leads > 0 AND next_available_calling_time NOT IN ("Goal Appointment Reached", "On Hold", "Cancelled", "Removed")';
		
		$customerQueues = CustomerQueueViewer::model()->findAll(array(
			// 'condition' => $condition
			// 'condition' => 'status = 1 AND available_leads > 0 AND priority = 1 AND next_available_calling_time NOT IN ("Goal Appointment Reached", "No Available Leads", "Not Callable")',
			// 'condition' => 'priority >= 0.75 AND priority <= 0.99 AND available_leads > 0',
			// 'condition' => 'status = 1 AND available_leads > 0 AND priority >= 0 AND priority <= 1',
			'condition' => 'customer_id = 2646',
			// 'condition' => 'customer_id IN (1059, 880, 848, 691, 598, 1255, 970, 589, 408, 554, 960, 1276, 505, 411, 430, 448, 762, 1215, 899, 461, 652, 925, 445, 1046)',
			// 'condition' => 'customer_id IN(1038, 1029, 1031, 1032, 1036, 1035, 1030)',
			// 'condition' => 'customer_id IN(1038, 1029, 1031, 1032, 1036, 1035, 1030)',
			// 'condition' => 'customer_id = 1',
			// 'condition' => 'customer_id IN (1966) AND skill_id="39"',
			// 'condition' => 'priority = 10',
			// 'condition' => 'priority >= 0.50 AND priority <= 0.99 AND available_leads > 0',
			// 'condition' => 'next_available_calling_time="Now"',
			'order' => 'priority DESC',
		));

		echo 'customerQueues: ' . count($customerQueues);
		
		echo '<br><br>';
		
		if( $customerQueues )
		{
			foreach( $customerQueues as $customerQueue )
			{
				$customerSkill = CustomerSkill::model()->find(array(
					'condition' => 'customer_id = :customer_id AND skill_id = :skill_id AND status=1',
					'params' => array(
						':customer_id' => $customerQueue->customer_id,
						':skill_id' => $customerQueue->skill_id,
					),
				));
				
				if( $customerSkill )
				{
					$nextAvailableCallingTime = $this->checkTimeZone($customerSkill);
					
					$customerQueue->call_agent = null;
					
					$customerIsCallable = false;
					
					$status = 1;					

					
					if( isset($customerSkill->contract) && isset($customerSkill->customer) && $customerSkill->customer->status == 1 && $customerSkill->customer->is_deleted == 0 && time() >= strtotime($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00' )
					{
						$customerIsCallable = true;
					}
					
					if( !$customerIsCallable )
					{
						if( $customerSkill->start_month == '0000-00-00' )
						{
							$nextAvailableCallingTime = 'Blank Start Date';
						}
						
						if( $customerSkill->start_month != '0000-00-00' && strtotime($customerSkill->start_month) > time() )
						{
							$nextAvailableCallingTime = 'Future Start Date';
						}
					}
					
					if( $customerSkill->is_contract_hold == 1 )
					{
						if( !empty($customerSkill->is_contract_hold_start_date) && !empty($customerSkill->is_contract_hold_end_date) )
						{
							if( time() >= strtotime($customerSkill->is_contract_hold_start_date) && time() <= strtotime($customerSkill->is_contract_hold_end_date) )
							{
								$customerIsCallable = false;
								
								$nextAvailableCallingTime = 'On Hold';
							}
						}
					}
					
					if( $customerSkill->is_hold_for_billing == 1 )
					{
						$customerIsCallable = false;
								
						$nextAvailableCallingTime = 'Decline Hold';
					}
					
					if( !empty($customerSkill->end_month) && date('Y', strtotime($customerSkill->end_month)) >= 2015 )
					{
						if( time() >= strtotime($customerSkill->end_month) )
						{
							$customerIsCallable = false;
							
							$nextAvailableCallingTime = 'Cancelled';
						}
					}
					
					if( $customerQueue )
					{
						if( !empty($customerQueue->removal_start_date) && !empty($customerQueue->removal_end_date) )
						{
							if( time() >= strtotime($customerQueue->removal_start_date) && time() <= strtotime($customerQueue->removal_end_date) )
							{
								$customerIsCallable = false;
								
								$nextAvailableCallingTime = 'Removed';
							}
						}
					}

					
					##get contract Goal##
					$contractGoal = 0;
					if(isset($customerSkill->contract))
					{
						$contract = $customerSkill->contract;
						if($contract->fulfillment_type != null )
						{
							##get Appointment that has been scheduled ##
							$appointmentSetMTDSql = "
								SELECT count(distinct lch.lead_id) AS totalCount 
								FROM ud_lead_call_history lch 
								LEFT JOIN ud_lists ls ON ls.id = lch.list_id 
								LEFT JOIN ud_calendar_appointment ca ON ca.id = lch.calendar_appointment_id 
								WHERE ca.title IN ('INSERT APPOINTMENT', 'APPOINTMENT SET', 'CANCEL APPOINTMENT', 'RESCHEDULE APPOINTMENT') 
								AND lch.disposition = 'Appointment Set'
								AND lch.date_created >= '".date('Y-m-01 00:00:00')."' 
								AND lch.date_created <= '".date('Y-m-t 23:59:59')."'
								AND lch.customer_id = '".$customerSkill->customer->id."'
								AND ls.skill_id = '".$customerSkill->skill_id."' 
							";
						
							
							$command = Yii::app()->db->createCommand($appointmentSetMTDSql);
							$appointmentSetMTD = $command->queryRow();
							
							$noShowMTDSql = "
								SELECT count(distinct lch.lead_id) AS totalCount 
								FROM ud_lead_call_history lch 
								LEFT JOIN ud_lists ls ON ls.id = lch.list_id 
								LEFT JOIN ud_calendar_appointment ca ON ca.id = lch.calendar_appointment_id 
								WHERE ca.title IN ('NO SHOW RESCHEDULE')
								AND lch.disposition = 'Appointment Set'
								AND lch.date_created >= '".date('Y-m-01 00:00:00')."' 
								AND lch.date_created <= '".date('Y-m-t 23:59:59')."'
								AND lch.customer_id = '".$customerSkill->customer->id."'
								AND ls.skill_id = '".$customerSkill->skill_id."' 
							";
							
							
							$command = Yii::app()->db->createCommand($noShowMTDSql);
							$noShowMTD = $command->queryRow();
							
							$noShowCount = $noShowMTD['totalCount'];
							
							$appointmentSetCount = $appointmentSetMTD['totalCount'] + $noShowMTD['totalCount'];
							
							if( $noShowMTD['totalCount'] > 3 )
							{
								$appointmentSetCount = $appointmentSetMTD['totalCount']-3;
							}
							else
							{
								$appointmentSetCount = $appointmentSetCount-$noShowMTD['totalCount'];
							}
							
							$customerQueue->current_goals = $appointmentSetCount;
							
							### check if the customer has reached his goal on appointments for the month ###
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
												$contractGoal += ( $subsidyLevel['goal'] * $customerSkillLevelArrayGroup->quantity );
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
										$contractGoal += $customerExtra->quantity;
									}
								}
								
								// $customerIsCallable = $customerIsCallable && ($contractGoal > $appointmentSetCount);
								echo 'Customer ID:'.$customerSkill->customer->id.' | Goal: '. $contractGoal.' | Appointment Set: '.$appointmentSetCount.' | Priority: ' . $customerQueue->priority;
								echo '<br>';
								
								if( $appointmentSetCount > 0 && $contractGoal > 0 && $appointmentSetCount >= $contractGoal )
								{
									$nextAvailableCallingTime = 'Goal Appointment Reached';
								}
							}						
						}	
					}
					
					
					if( !$customerIsCallable )
					{
						$status = 2;
					}
					
					if( in_array($nextAvailableCallingTime, array("On Hold", "Cancelled", "Removed", "Goal Appointment Reached", "Blank Start Date", "Future Start Date", "Decline Hold")) )
					{
						$status = 2;
					}
					
					$customerQueue->next_available_calling_time = $nextAvailableCallingTime;
					$customerQueue->status = $status;
					
					echo 'customerIsCallable:" ' . $customerIsCallable;
					
					echo '<br>';
					
					echo 'nextAvailableCallingTime: ' . $nextAvailableCallingTime;
					
					echo '<br>';
					
					echo 'status: ' . $status;
					// exit;
					
					//temporarily disabled customers from florida because of hurricane
					// $temporaryDisabledCustomers = array(1059, 880, 848, 691, 598, 1255, 970, 589, 408, 554, 960, 1276, 505, 411, 430, 448, 762, 1215, 899, 461, 652, 925, 445, 1046);
					
					// if( in_array($customerQueue->customer_id, $temporaryDisabledCustomers) )
					// {
						// $customerQueue->next_available_calling_time = 'Next Shift';
					// }
				
					//get the latest agent that is calling the customer's leads
					$latestCallers = LeadHopper::model()->findAll(array(
						'group' => 'agent_account_id',
						'condition' => 'customer_id = :customer_id AND type != :lead_search AND agent_account_id IS NOT NULL AND status IN ("READY", "INCALL")',
						'params' => array(
							':customer_id' => $customerQueue->customer_id,
							':lead_search' => LeadHopper::TYPE_LEAD_SEARCH,
						),
					));
					
					if( $latestCallers )
					{
						$callAgent = '';
						
						foreach( $latestCallers as $latestCaller )
						{
							if( $latestCaller->type == 3 )
							{
								$callAgent .= '*' . $latestCaller->currentAgentAccount->getFullName();
								$callAgent .= ', ';
							}
							else
							{				
								$callAgent .= $latestCaller->currentAgentAccount->getFullName();
								$callAgent .= ', ';
							}
						}
						
						$customerQueue->call_agent = rtrim($callAgent, ', ');
					}
					
					if( $customerQueue->customer_id == 1021 )
					{
						$customerQueue->dials_until_reset = 20;
					}
					
					if( $customerQueue->save(false) )
					{
						$customerQueueViewerUpdates++;
					}

					if( $customerQueue->next_available_calling_time == 'Now' )
					{
						$leadCounter = LeadHopper::model()->count(array(
							'condition' => 'status IN ("READY") AND type=1 AND customer_id = :customer_id AND skill_id = :skill_id',
							'params' => array(
								':customer_id' => $customerQueue->customer_id,
								':skill_id' => $customerQueue->skill_id,
							),
						));				
						
						if( $customerQueue->dials_until_reset < 20 && $leadCounter == 0 )
						{
							$customerQueue->dials_until_reset = 0;
							$customerQueue->save(false);
						}

						$customer = $customerSkill->customer;
						
						$lists = Lists::model()->findAll(array(
							'together' => true,
							'condition' => 't.customer_id = :customer_id AND skill_id = :skill_id AND t.status=1',
							'params' => array(
								':customer_id' => $customerQueue->customer_id,
								':skill_id' => $customerQueue->skill_id,
							),
						));
						
						$customerIsCallable = true;
						
						if( !empty($customerQueue->removal_start_date) && !empty($customerQueue->removal_end_date) )
						{
							if( time() >= strtotime($customerQueue->removal_start_date) && time() <= strtotime($customerQueue->removal_end_date) )
							{
								$customerIsCallable = false;
							}
						}
						
						if( $customer->status == 1 && $customerIsCallable )
						{
							if( $lists )
							{
								foreach( $lists as $list )
								{
									switch( $list->lead_ordering )
									{
										default: case 1: $order = 'RAND()';
										case 2: $order = 't.last_name ASC'; break;
										case 3: $order = 't.custom_date ASC'; break;
										case 4: $order = 't.number_of_dials ASC'; break;
									}
								
									//get callable leads
									if( in_array($customerSkill->skill_id, array(36,37,38)) )
									{
										$skillMaxDials = $customerSkill->skill->max_dials;
									}
									else
									{
										$skillMaxDials = $customerSkill->skill->max_dials * 3;
									}
									
									$leads = Lead::model()->findAll(array(
										'with' => array('list', 'list.skill'),
										'condition' => ' 
											list.status = 1 
											AND t.list_id IS NOT NULL
											AND t.list_id = :list_id
											AND t.type=1 
											AND t.status=1
											AND t.is_do_not_call = 0
											AND t.number_of_dials < :skill_max_dials
											AND (
												t.recertify_date != "0000-00-00" 
												AND t.recertify_date IS NOT NULL 
												AND NOW() <= t.recertify_date
											)
											AND ( 
												t.home_phone_number IS NOT NULL
												OR t.office_phone_number IS NOT NULL
												OR t.mobile_phone_number IS NOT NULL
											)
										',
										'params' => array(
											':list_id' => $list->id,
											':skill_max_dials' => $skillMaxDials
										),
										'order' => $order,
									));
									
									echo '<br>';
									
									echo 'count($leads): ' . count($leads);
									
									echo '<br>';
									
									echo 'leadCounter: ' . $leadCounter;
									
									echo '<br>';
									echo '<br>';
									
									// exit;
									
									//casino and inside skills - remove the lead limit queued on hopper
									if( in_array($customerSkill->skill_id, array(36,37,38,39,44)) && $leadCounter < 1000 )
									{
										$leadCounter = 0;
									}
									
									if( $customer->id == 2646 )
									{
										$leadCounter = 0;
									}
												
									if( count($leads) > 0 && $leadCounter == 0 )
									{
										$customerLeadAdded = 0;
										
										foreach( $leads as $lead )
										{
											if( in_array($customerSkill->skill_id, array(36,37,38,39,43,44)) )
											{
												$maxDials = $lead->list->skill->max_dials;
											}
											else
											{
												$maxDials = $lead->list->skill->max_dials * 3;
											}
											
											if( $lead->status == 1 && $lead->type == 1 && $lead->number_of_dials < $maxDials && $lead->is_do_not_call == 0 && $lead->list->status == 1 )
											{
												$existingHopperEntry = LeadHopper::model()->find(array(
													'condition' => 'lead_id = :lead_id AND type NOT IN (6,7)',
													'params' => array(
														':lead_id' => $lead->id,
													),
												));
												
												$existingDnc = Dnc::model()->find(array(
													'condition' => '
														phone_number IS NOT NULL 
														AND phone_number !=""
														AND (
															phone_number = :home_phone_number 
															OR phone_number = :mobile_phone_number 
															OR phone_number = :office_phone_number
														)
													',
													'params' => array(
														':home_phone_number' => $lead->home_phone_number,
														':mobile_phone_number' => $lead->mobile_phone_number,
														':office_phone_number' => $lead->office_phone_number,
													),
												));
												
												$existingDcwn = Dcwn::model()->find(array(
													'condition' => '
														phone_number IS NOT NULL 
														AND phone_number !=""
														AND (
															phone_number = :home_phone_number 
															OR phone_number = :mobile_phone_number 
															OR phone_number = :office_phone_number
														)
													',
													'params' => array(
														':home_phone_number' => $lead->home_phone_number,
														':mobile_phone_number' => $lead->mobile_phone_number,
														':office_phone_number' => $lead->office_phone_number,
													),
												));

												if( empty($existingHopperEntry) )
												{
													if( empty($existingDnc) && empty($existingDcwn) )
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
																	'condition' => 'lead_id = :lead_id AND customer_id = :customer_id AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
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
																'condition' => 'lead_id = :lead_id AND customer_id = :customer_id AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
																'params' => array(
																	':lead_id' => $lead->id,
																),
															));
														}
													}
													else
													{
														$scrubType = '';
														
														if( !empty($existingDnc) )
														{
															$scrubType = 'DNC ';
															
															echo '<pre>';
																print_r($existingDnc->attributes);
															echo '<pre>';
														}
														
														if( !empty($existingDcwn) )
														{
															$scrubType .= 'DCWN ';
															
															echo '<pre>';
																print_r($existingDcwn->attributes);
															echo '<pre>';
														}
														
														echo '<br>';

														echo 'Not Added '.$scrubType.'found on Lead of Customer ID: '.$customerQueue->customer_id.' - '.$customerQueue->customer_name. ' => Lead ID: ' . $lead->id.' - '.$lead->getFullName() .' => Timezone:'. $lead->timezone.' => '.$this->checkTimeZone($customerSkill, 'lead', $lead);
														
														echo '<br> home:' . $lead->home_phone_number;
														echo '<br> office:' . $lead->office_phone_number;
														echo '<br> mobile:' . $lead->mobile_phone_number;

														
														echo '<br>';
											
														LeadHopper::model()->delete(array(
															'condition' => 'lead_id = :lead_id AND customer_id = :customer_id AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
															'params' => array(
																':lead_id' => $lead->id,
															),
														));
													}
												}
												else
												{
													echo 'Not Added Existing Lead of Customer ID: '.$customerQueue->customer_id.' - '.$customerQueue->customer_name. ' => Lead ID: ' . $lead->id.' - '.$lead->getFullName() .' => Timezone:'. $lead->timezone.' => '.$this->checkTimeZone($customerSkill, 'lead', $lead);
													
													if( $existingHopperEntry )
													{
														echo ' | TYPE: ' . $existingHopperEntry->type . ' => ' . date('m/d/Y', strtotime($existingHopperEntry->callback_date));
													}
													echo '<br>';
													
													if( $existingHopperEntry && $existingHopperEntry->type == 2 && (time() > strtotime($existingHopperEntry->callback_date) || $existingHopperEntry->callback_date == null) )
													{
														echo 'today: ' . date('m/d/Y');
														echo '<br>';
														echo 'callback date: ' . date('m/d/Y', strtotime($existingHopperEntry->callback_date));
														echo '<br>';
														echo '<br>';
														
														$existingHopperEntry->status='READY';
														$existingHopperEntry->save(false);
													}
										
													LeadHopper::model()->delete(array(
														'condition' => 'lead_id = :lead_id AND customer_id = :customer_id AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
														'params' => array(
															':lead_id' => $lead->id,
														),
													));
												}
											
												// Mountain View Utah
												if( isset($customerQueue->skill) && $customerQueue->skill->max_agent_per_customer > 1 )
												{
													if( $customerLeadAdded == 1000 )
													{
														break;
													}
												}
												else
												{
													$cqviewerBoost = CustomerQueueViewerBoost::model()->find(array(
														'condition' => '
															customer_id = :customer_id
															AND skill_id = :skill_id
															AND is_boost_triggered = 1
															AND status = 1
														',
														'params' => array(
															':customer_id' => $customerQueue->customer_id,
															':skill_id' => $customerQueue->skill_id,
														),
													));
													
													if( $cqviewerBoost )
													{
														if( $customerLeadAdded == 100 * $cqviewerBoost->magnitude_value )
														{
															break;
														}
													}
													else
													{
														if( $customerLeadAdded == 20 )
														{
															break;
														}
													}
												}
											}
											else
											{
												LeadHopper::model()->delete(array(
													'condition' => 'lead_id = :lead_id AND customer_id = :customer_id AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (3,5,6,7)',
													'params' => array(
														':lead_id' => $lead->id,
													),
												));
											}
										}
									}						
								}
							}
						}
						else
						{
							$customerQueue->next_available_calling_time = 'Next Shift';
							$customerQueue->save(false);
							
							echo 'Deleted Leads of Customer ID: ' . $customerQueue->customer_id.' - '.$customerQueue->customer_name;
							echo '<br>';
							
							LeadHopper::model()->deleteAll(array(
								'condition' => 'customer_id = :customer_id AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
								'params' => array(
									':customer_id' => $customerQueue->customer_id,
								),
							));
						}
					}
					else
					{
						echo 'Deleted Leads of Customer ID: ' . $customerQueue->customer_id.' - '.$customerQueue->customer_name . ' | ' . $nextAvailableCallingTime;
						echo '<br>';
						
						LeadHopper::model()->deleteAll(array(
							'condition' => 'customer_id = :customer_id AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
							'params' => array(
								':customer_id' => $customerQueue->customer_id,
							),
						));
						
					}
					
					echo $processCtr++;
					echo '<br>';
				}
			}
		
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
	
	private function checkTimeZone($customerSkill, $type='customer', $lead=null)
	{
		date_default_timezone_set('America/Denver');
		
		$nextAvailableCallingTime = '';
		
		$customer = $customerSkill->customer;
		
		$skillScheduleHolder = array();
			
		$currentDateTime = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('America/Denver'));
		// $currentDateTime->setTimezone(new DateTimeZone('America/Denver')); 
		
		
		//temp code to force certain customers to get no dials 
		// $georgiaArecodeCodes = array('229', '404', '470', '478', '678', '706', '762', '770', '912');
			
		// $northCarolinaAreaCodes = array('252', '336', '704', '828', '910', '919', '980');
		
		// $southCarolinaAreaCodes = array('803', '843', '864');
		
		// $texasAreaCodes = array('210', '214', '254', '281', '361', '409', '469', '512', '682', '713', '806', '817', '830', '832', '903', '915', '936', '940', '956', '972', '979');
		
		// $louisianaAreaCodes = array('225', '318', '337', '504', '985');
		
		// $floridaAreaCodes = array('305', '321', '352', '386', '407', '561', '727', '754', '772', '786', '813', '850', '863', '904', '941', '954');
		
		// if( !in_array($customer->id, array(613, 2084, 2136, 1908, 2068, 438, 198, 2102, 2176, 1579, 1209, 633, 2101, 1476, 1166, 1742, 1765, 931, 1846, 1625, 2177, 2071, 2144, 444, 1176, 2222, 2187, 2029, 482, 1943, 2002, 1817, 691, 2093, 762, 445, 631, 1791, 2243)) )
		// {
			// if( in_array(substr($customer->phone, 1, 3), $georgiaArecodeCodes) || $customer->state == 10 )
			// {
				// return 'Next Shift';
			// }
			
			// if( in_array(substr($customer->phone, 1, 3), $northCarolinaAreaCodes) )
			// {
				// return 'Next Shift';
			// }
			
			// if( in_array(substr($customer->phone, 1, 3), $southCarolinaAreaCodes) )
			// {
				// return 'Next Shift';
			// }
			
			// if( in_array(substr($customer->phone, 1, 3), $texasAreaCodes) || $customer->state == 43 )
			// {
				// return 'Next Shift';
			// }
			
			// if( in_array(substr($customer->phone, 1, 3), $louisianaAreaCodes) || $customer->state == 18 )
			// {
				// return 'Next Shift';
			// }
			
			// if( in_array(substr($customer->phone, 1, 3), $floridaAreaCodes) || $customer->state == 9 )
			// {
				// return 'Next Shift';
			// }
		// }
		//end of temp code
		
		
		//emergency LA pull out
		// $losAngeles = array('805', '818', '747', '626');
		
		// if( in_array(substr($customer->phone, 1, 3), $losAngeles) || in_array($customer->id, array(2133, 1679, 2286, 2391, 2431, 2417, 1760, 2313, 2415, 2422, 2393, 2398, 1646, 2242, 2241, 2285, 2255, 609, 788)) )
		// {
			// return 'Next Shift';
		// }
		
		
		//check dnc state holiday
		$dncHolidayState = DncHolidayState::model()->find(array(
			'condition' => 'state = :state AND date = :date',
			'params' => array(
				':state' => $customer->state,
				':date' => date('Y-m-d')
			),
		));
		
		if( $dncHolidayState )
		{
			return 'Next Shift';
		}
		
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
						
						$nextAvailableCallingTime = $nextAvailableCallingTimeStart->format('g:i A');

						if( strtotime($currentDateTime->format('g:i A')) >= strtotime($nextAvailableCallingTimeStart->format('g:i A')) && strtotime($currentDateTime->format('g:i A')) <= strtotime($nextAvailableCallingTimeEnd->format('g:i A')) )
						{
							$nextAvailableCallingTime = 'Now';
						}
						
						if( strtotime($currentDateTime->format('g:i A')) >= strtotime($nextAvailableCallingTimeStart->format('g:i A')) && strtotime($currentDateTime->format('g:i A')) >= strtotime($nextAvailableCallingTimeEnd->format('g:i A')) )
						{
							$nextAvailableCallingTime = 'Next Shift';
						}
						
						if( in_array($nextAvailableCallingTime, array('7:00 AM', '7:30 AM', '8:00 AM')) && time() >= strtotime('today 6:00 am') )
						{
							$nextAvailableCallingTime = 'Now';
						}
						
						//exclude these customers on the forced call schedule
						//63 - Engagex Email Collection
						//1029 - Alaska Sales Region
						//1031 - Washington Sales Region
						//1032 - Denver Sales Region
						//1036 - Las Vegas Sales Region
						//1035 - Phoenix/Mesa Sales Region	
						//2129 - Sandia
						if( !in_array($customer->id, array(63, 1029, 1031, 1032, 1036, 1035, 2129)) )
						{
							if( in_array($this->get_timezone_abbreviation($timeZone), array('EST', 'EDT')) )
							{
								if( time() >= strtotime('today 9:00 AM') && time() < strtotime('today 5:00 PM') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									$nextAvailableCallingTime = '9:00 AM - 5:00 PM';
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('CST', 'CDT')) )
							{
								if( time() >= strtotime('today 10:00 AM') && time() < strtotime('today 6:00 PM') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									$nextAvailableCallingTime = '10:00 AM - 6:00 PM';
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('MST', 'MDT')) )
							{
								if( time() >= strtotime('today 10:00 AM') && time() < strtotime('today 7:00 PM') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									$nextAvailableCallingTime = '10:00 AM - 7:00 PM';
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('PST', 'PDT')) )
							{
								if( time() >= strtotime('today 11:00 AM') && time() < strtotime('today 8:00 PM') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									$nextAvailableCallingTime = '11:00 AM - 8:00 PM';
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('HAST', 'HADT')) )
							{
								if( time() >= strtotime('today 3:00 pm') && time() < strtotime('today 10:00 PM') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									$nextAvailableCallingTime = '3:00 PM - 10:00 PM';
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('AKST', 'AKDT')) )
							{
								if( time() >= strtotime('today 1:00 pm') && time() < strtotime('today 8:00 PM') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									$nextAvailableCallingTime = '1:00 PM - 8:00 PM';
								}
							}
						}

						
						//modified customer call schedules
						// $modifiedCustomerArray = array(522, 408, 1512, 1499, 1493, 1538, 1505, 1474, 490, 1370, 1627, 1598, 521, 1498, 1155, 1548, 1612, 172, 1481, 1260, 1503, 869, 1420, 1546, 1181);
						// $modifiedCustomerArray = array(522, 1493, 1505, 1598, 1646);
						$modifiedCustomerArray = array(522);
						
						if( in_array($customer->id, $modifiedCustomerArray) )
						{
							if( in_array($this->get_timezone_abbreviation($timeZone), array('EST', 'EDT')) )
							{
								if( time() >= strtotime('today 8:00 am') && time() < strtotime('today 5:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 8:00 AM') ) 
									{
										$nextAvailableCallingTime = '8:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('CST', 'CDT')) )
							{
								if( time() >= strtotime('today 9:00 am') && time() < strtotime('today 6:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 9:00 AM') ) 
									{
										$nextAvailableCallingTime = '9:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('PST', 'PDT')) )
							{
								if( time() >= strtotime('today 11:00 am') && time() < strtotime('today 8:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 11:00 AM') ) 
									{
										$nextAvailableCallingTime = '11:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('MST', 'MDT')) )
							{
								if( time() >= strtotime('today 10:00 am') && time() < strtotime('today 7:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 10:00 AM') ) 
									{
										$nextAvailableCallingTime = '10:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
						}
										
						//49 -Jory Bowers
						//56 - Valerie Strickland
						if( in_array($customer->id, array(49, 56)) )
						{
							if( time() >= strtotime('today 7:00 am') && time() < strtotime('today 6:00 pm') )
							{
								$nextAvailableCallingTime = 'Now';
								
								//temporary code to remove eastern leads after call schedule => 9am - 5pm  local time
								if( time() >= strtotime('today 3:00 pm') )
								{
									LeadHopper::model()->deleteAll(array(
										'condition' => 'customer_id = :customer_id AND lead_timezone IN ("EST", "EDT") AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
										'params' => array(
											':customer_id' => $customer->id,
										),
									));
								}
								
								//temporary code to remove central leads after call schedule => 9am - 5pm  local time
								if( time() >= strtotime('today 4:00 pm') )
								{
									LeadHopper::model()->deleteAll(array(
										'condition' => 'customer_id = :customer_id AND lead_timezone IN ("CST", "CDT") AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
										'params' => array(
											':customer_id' => $customer->id,
										),
									));
								}
											
								//temporary code to remove mountain leads after call schedule => 9am - 5pm  local time
								if( time() >= strtotime('today 5:00 pm') )
								{
									LeadHopper::model()->deleteAll(array(
										'condition' => 'customer_id = :customer_id AND lead_timezone IN ("MST", "MDT") AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
										'params' => array(
											':customer_id' => $customer->id,
										),
									));
								}
								
								//temporary code to remove pacific leads after call schedule => 9am - 5pm  local time
								if( time() >= strtotime('today 6:00 pm') )
								{
									LeadHopper::model()->deleteAll(array(
										'condition' => 'customer_id = :customer_id AND lead_timezone IN ("PST", "PDT") AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
										'params' => array(
											':customer_id' => $customer->id,
										),
									));
								}
							}
							else
							{
								if( time() < strtotime('today 7:00 AM') ) 
								{
									$nextAvailableCallingTime = '7:00 AM';
								}
								else
								{
									$nextAvailableCallingTime = 'Next Shift';
								}
							}
						}
										
						//2011 - Indigo Sky
						if( $customer->id == 2011 )
						{
							date_default_timezone_set('America/Denver');
							
							if( time() >= strtotime('today 8:00 am') && time() < strtotime('today 6:00 pm') )
							{
								$nextAvailableCallingTime = 'Now';
								
								//temporary code to remove eastern leads after call schedule => 9am - 5pm  local time
								if( time() >= strtotime('today 5:00 pm') )
								{
									echo '<br><br>';
									echo '<b>Removed eastern leads</b>';
									echo '<br><br>';
									
									LeadHopper::model()->deleteAll(array(
										'condition' => 'customer_id = :customer_id AND lead_timezone IN ("EST", "EDT") AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
										'params' => array(
											':customer_id' => $customer->id,
										),
									));
								}
								
								//temporary code to remove central leads after call schedule => 9am - 5pm  local time
								if( time() >= strtotime('today 6:00 pm') )
								{
									echo '<br><br>';
									echo '<b>Removed central leads</b>';
									echo '<br><br>';
									
									LeadHopper::model()->deleteAll(array(
										'condition' => 'customer_id = :customer_id AND lead_timezone IN ("CST", "CDT") AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
										'params' => array(
											':customer_id' => $customer->id,
										),
									));
								}
											
								//temporary code to remove mountain leads after call schedule => 9am - 5pm  local time
								if( time() >= strtotime('today 7:00 pm') )
								{
									echo '<br><br>';
									echo '<b>Removed mountain leads</b>';
									echo '<br><br>';
									
									LeadHopper::model()->deleteAll(array(
										'condition' => 'customer_id = :customer_id AND lead_timezone IN ("MST", "MDT") AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
										'params' => array(
											':customer_id' => $customer->id,
										),
									));
								}
								
								//temporary code to remove pacific leads after call schedule => 9am - 5pm  local time
								if( time() >= strtotime('today 8:00 pm') )
								{
									echo '<br><br>';
									echo '<b>Removed pacific leads</b>';
									echo '<br><br>';
									
									LeadHopper::model()->deleteAll(array(
										'condition' => 'customer_id = :customer_id AND lead_timezone IN ("PST", "PDT") AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
										'params' => array(
											':customer_id' => $customer->id,
										),
									));
								}
							}
							else
							{
								echo '<br><br>';
								echo '<b>Removed all indigo leads</b>';
								echo '<br><br>';
								
								if( time() < strtotime('today 8:00 AM') ) 
								{
									$nextAvailableCallingTime = '8:00 AM - 6:00 PM';
								}
								else
								{
									$nextAvailableCallingTime = 'Next Shift';
								}
							}
						}
										
						//2607 - Inside Sales
						if( $customer->id == 2607 || $customer->id == 2683 )
						{
							date_default_timezone_set('America/Denver');
							
							if( time() >= strtotime('today 7:00 am') && time() < strtotime('today 5:00 pm') )
							{
								$nextAvailableCallingTime = 'Now';
								
								//temporary code to remove eastern leads after call schedule => 9am - 5pm  local time
								if( time() >= strtotime('today 2:00 pm') )
								{
									echo '<br><br>';
									echo '<b>Removed eastern leads</b>';
									echo '<br><br>';
									
									LeadHopper::model()->deleteAll(array(
										'condition' => 'customer_id = :customer_id AND lead_timezone IN ("EST", "EDT") AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
										'params' => array(
											':customer_id' => $customer->id,
										),
									));
								}
								
								//temporary code to remove central leads after call schedule => 9am - 5pm  local time
								if( time() >= strtotime('today 3:00 pm') )
								{
									echo '<br><br>';
									echo '<b>Removed central leads</b>';
									echo '<br><br>';
									
									LeadHopper::model()->deleteAll(array(
										'condition' => 'customer_id = :customer_id AND lead_timezone IN ("CST", "CDT") AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
										'params' => array(
											':customer_id' => $customer->id,
										),
									));
								}
											
								//temporary code to remove mountain leads after call schedule => 9am - 5pm  local time
								if( time() >= strtotime('today 4:00 pm') )
								{
									echo '<br><br>';
									echo '<b>Removed mountain leads</b>';
									echo '<br><br>';
									
									LeadHopper::model()->deleteAll(array(
										'condition' => 'customer_id = :customer_id AND lead_timezone IN ("MST", "MDT") AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
										'params' => array(
											':customer_id' => $customer->id,
										),
									));
								}
								
								//temporary code to remove pacific leads after call schedule => 9am - 5pm  local time
								if( time() >= strtotime('today 5:00 pm') )
								{
									echo '<br><br>';
									echo '<b>Removed pacific leads</b>';
									echo '<br><br>';
									
									LeadHopper::model()->deleteAll(array(
										'condition' => 'customer_id = :customer_id AND lead_timezone IN ("PST", "PDT") AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
										'params' => array(
											':customer_id' => $customer->id,
										),
									));
								}
							}
							else
							{
								echo '<br><br>';
								echo '<b>Removed all inside sales leads</b>';
								echo '<br><br>';
								
								if( time() < strtotime('today 7:00 AM') ) 
								{
									$nextAvailableCallingTime = '7:00 AM - 5:00 PM';
								}
								else
								{
									$nextAvailableCallingTime = 'Next Shift';
								}
							}
						}
					
						//1966 - Graton
						if( $customer->id == 1966 )
						{
							if( $customerSkill->skill_id == 39 ) //event enrollment skill
							{
								if( in_array($this->get_timezone_abbreviation($timeZone), array('EST', 'EDT')) )
								{
									if( time() >= strtotime('today 7:00 am') && time() < strtotime('today 5:30 pm') )
									{
										$nextAvailableCallingTime = 'Now';
									}
									else
									{
										if( time() < strtotime('today 7:00 AM') ) 
										{
											$nextAvailableCallingTime = '7:00 AM';
										}
										else
										{
											$nextAvailableCallingTime = 'Next Shift';
										}
									}
								}
								
								if( in_array($this->get_timezone_abbreviation($timeZone), array('CST', 'CDT')) )
								{
									if( time() >= strtotime('today 8:00 am') && time() < strtotime('today 6:30 pm') )
									{
										$nextAvailableCallingTime = 'Now';
									}
									else
									{
										if( time() < strtotime('today 8:00 AM') ) 
										{
											$nextAvailableCallingTime = '8:00 AM';
										}
										else
										{
											$nextAvailableCallingTime = 'Next Shift';
										}
									}
								}
								
								if( in_array($this->get_timezone_abbreviation($timeZone), array('MST', 'MDT')) )
								{
									if( time() >= strtotime('today 9:00 am') && time() < strtotime('today 7:30 pm') )
									{
										$nextAvailableCallingTime = 'Now';
									}
									else
									{
										if( time() < strtotime('today 9:00 AM') ) 
										{
											$nextAvailableCallingTime = '9:00 AM';
										}
										else
										{
											$nextAvailableCallingTime = 'Next Shift';
										}
									}
								}
								
								if( in_array($this->get_timezone_abbreviation($timeZone), array('PST', 'PDT')) )
								{
									if( time() >= strtotime('today 10:00 am') && time() < strtotime('today 8:30 pm') )
									{
										$nextAvailableCallingTime = 'Now';
									}
									else
									{
										if( time() < strtotime('today 10:00 AM') ) 
										{
											$nextAvailableCallingTime = '10:00 AM';
										}
										else
										{
											$nextAvailableCallingTime = 'Next Shift';
										}
									}
								}
							}
							else
							{
								if( in_array($this->get_timezone_abbreviation($timeZone), array('EST', 'EDT')) )
								{
									if( time() >= strtotime('today 8:00 am') && time() < strtotime('today 5:00 pm') )
									{
										$nextAvailableCallingTime = 'Now';
									}
									else
									{
										if( time() < strtotime('today 8:00 AM') ) 
										{
											$nextAvailableCallingTime = '8:00 AM';
										}
										else
										{
											$nextAvailableCallingTime = 'Next Shift';
										}
									}
								}
								
								if( in_array($this->get_timezone_abbreviation($timeZone), array('CST', 'CDT')) )
								{
									if( time() >= strtotime('today 9:00 am') && time() < strtotime('today 6:00 pm') )
									{
										$nextAvailableCallingTime = 'Now';
									}
									else
									{
										if( time() < strtotime('today 9:00 AM') ) 
										{
											$nextAvailableCallingTime = '9:00 AM';
										}
										else
										{
											$nextAvailableCallingTime = 'Next Shift';
										}
									}
								}
								
								if( in_array($this->get_timezone_abbreviation($timeZone), array('MST', 'MDT')) )
								{
									if( time() >= strtotime('today 10:00 am') && time() < strtotime('today 7:00 pm') )
									{
										$nextAvailableCallingTime = 'Now';
									}
									else
									{
										if( time() < strtotime('today 10:00 AM') ) 
										{
											$nextAvailableCallingTime = '10:00 AM';
										}
										else
										{
											$nextAvailableCallingTime = 'Next Shift';
										}
									}
								}
								
								if( in_array($this->get_timezone_abbreviation($timeZone), array('PST', 'PDT')) )
								{
									if( time() >= strtotime('today 11:00 am') && time() < strtotime('today 8:00 pm') )
									{
										$nextAvailableCallingTime = 'Now';
									}
									else
									{
										if( time() < strtotime('today 11:00 AM') ) 
										{
											$nextAvailableCallingTime = '11:00 AM';
										}
										else
										{
											$nextAvailableCallingTime = 'Next Shift';
										}
									}
								}
							}
						}
					
						//Saturday call schedule for SF Policy Review per Appointment / Name
						if( date('w') == 6 && in_array($customerSkill->skill_id, array(33, 34)) )
						{
							date_default_timezone_set('America/Denver');
							
							if( time() >= strtotime('today 8:00 am') && time() < strtotime('today 4:00 pm') )
							{
								$nextAvailableCallingTime = 'Now';
								
								//temporary code to remove eastern leads after call schedule => 9am - 5pm  local time
								if( time() >= strtotime('today 1:00 pm') )
								{
									echo '<br><br>';
									echo '<b>Removed eastern leads</b>';
									echo '<br><br>';
									
									LeadHopper::model()->deleteAll(array(
										'condition' => 'customer_id = :customer_id AND lead_timezone IN ("EST", "EDT") AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
										'params' => array(
											':customer_id' => $customer->id,
										),
									));
								}
								
								//temporary code to remove central leads after call schedule => 9am - 5pm  local time
								if( time() >= strtotime('today 2:00 pm') )
								{
									echo '<br><br>';
									echo '<b>Removed central leads</b>';
									echo '<br><br>';
									
									LeadHopper::model()->deleteAll(array(
										'condition' => 'customer_id = :customer_id AND lead_timezone IN ("CST", "CDT") AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
										'params' => array(
											':customer_id' => $customer->id,
										),
									));
								}
											
								//temporary code to remove mountain leads after call schedule => 9am - 5pm  local time
								if( time() >= strtotime('today 3:00 pm') )
								{
									echo '<br><br>';
									echo '<b>Removed mountain leads</b>';
									echo '<br><br>';
									
									LeadHopper::model()->deleteAll(array(
										'condition' => 'customer_id = :customer_id AND lead_timezone IN ("MST", "MDT") AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
										'params' => array(
											':customer_id' => $customer->id,
										),
									));
								}
								
								//temporary code to remove pacific leads after call schedule => 9am - 5pm  local time
								if( time() >= strtotime('today 4:00 pm') )
								{
									echo '<br><br>';
									echo '<b>Removed pacific leads</b>';
									echo '<br><br>';
									
									LeadHopper::model()->deleteAll(array(
										'condition' => 'customer_id = :customer_id AND lead_timezone IN ("PST", "PDT") AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
										'params' => array(
											':customer_id' => $customer->id,
										),
									));
								}
							}
							else
							{
								echo '<br><br>';
								echo '<b>Removed customer: '.$customer->getFullName().' saturday leads</b>';
								echo '<br><br>';
								
								if( time() < strtotime('today 8:00 AM') ) 
								{
									$nextAvailableCallingTime = '8:00 AM - 4:00 PM';
								}
								else
								{
									$nextAvailableCallingTime = 'Next Shift';
								}
							}
						}
					}
					else 
					{
						$nextAvailableCallingTimeStart = new DateTime($sched['schedule_start'], new DateTimeZone($timeZone) );
						$nextAvailableCallingTimeEnd = new DateTime($sched['schedule_end'], new DateTimeZone($timeZone) );
						
						$nextAvailableCallingTimeStart->setTimezone(new DateTimeZone('America/Denver'));
						$nextAvailableCallingTimeEnd->setTimezone(new DateTimeZone('America/Denver'));

						$nextAvailableCallingTime = $nextAvailableCallingTimeStart->format('g:i A');
						
						// $currentDateTime = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('America/Denver'));
						$leadLocalTime = $currentDateTime->setTimezone(new DateTimeZone($timeZone));
					
						if( strtotime($leadLocalTime->format('g:i A')) >= strtotime($sched['schedule_start']) && strtotime($leadLocalTime->format('g:i A')) <= strtotime($sched['schedule_end']) )
						{
							$nextAvailableCallingTime = 'Now';
						}
						
						if( strtotime($leadLocalTime->format('g:i A')) >= strtotime($sched['schedule_start']) && strtotime($leadLocalTime->format('g:i A')) >= strtotime($sched['schedule_end']) )
						{
							$nextAvailableCallingTime = 'Next Shift';
						}
						
						//exclude these customers on the forced call schedule
						//63 - Engagex Email Collection
						//1029 - Alaska Sales Region
						//1031 - Washington Sales Region
						//1032 - Denver Sales Region
						//1036 - Las Vegas Sales Region
						//1035 - Phoenix/Mesa Sales Region
						//2129 - Sandia
						if( !in_array($customer->id, array(63, 1029, 1031, 1032, 1036, 1035, 2129)) )
						{
							if( in_array($this->get_timezone_abbreviation($timeZone), array('EST', 'EDT')) )
							{
								if( time() >= strtotime('today 9:00 AM') && time() < strtotime('today 5:00 PM') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									$nextAvailableCallingTime = '9:00 AM - 5:00 PM';
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('CST', 'CDT')) )
							{
								if( time() >= strtotime('today 10:00 AM') && time() < strtotime('today 6:00 PM') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									$nextAvailableCallingTime = '10:00 AM - 6:00 PM';
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('MST', 'MDT')) )
							{
								if( time() >= strtotime('today 10:00 AM') && time() < strtotime('today 7:00 PM') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									$nextAvailableCallingTime = '10:00 AM - 7:00 PM';
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('PST', 'PDT')) )
							{
								if( time() >= strtotime('today 11:00 AM') && time() < strtotime('today 8:00 PM') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									$nextAvailableCallingTime = '11:00 AM - 8:00 PM';
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('HAST', 'HADT')) )
							{
								if( time() >= strtotime('today 3:00 pm') && time() < strtotime('today 10:00 PM') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									$nextAvailableCallingTime = '3:00 PM - 10:00 PM';
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('AKST', 'AKDT')) )
							{
								if( time() >= strtotime('today 1:00 pm') && time() < strtotime('today 8:00 PM') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									$nextAvailableCallingTime = '1:00 PM - 8:00 PM';
								}
							}
						}
						
						//modified customer call schedules
						// $modifiedCustomerArray = array(522, 408, 1512, 1499, 1493, 1538, 1505, 1474, 490, 1370, 1627, 1598, 521, 1498, 1155, 1548, 1612, 172, 1481, 1260, 1503, 869, 1420, 1546, 1181);
						// $modifiedCustomerArray = array(522, 1493, 1505, 1598, 1646);
						$modifiedCustomerArray = array(522);
						
						if( in_array($customer->id, $modifiedCustomerArray) )
						{
							if( in_array($this->get_timezone_abbreviation($timeZone), array('EST', 'EDT')) )
							{
								if( time() >= strtotime('today 8:00 am') && time() < strtotime('today 5:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 8:00 AM') ) 
									{
										$nextAvailableCallingTime = '8:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('CST', 'CDT')) )
							{
								if( time() >= strtotime('today 9:00 am') && time() < strtotime('today 6:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 9:00 AM') ) 
									{
										$nextAvailableCallingTime = '9:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('PST', 'PDT')) )
							{
								if( time() >= strtotime('today 11:00 am') && time() < strtotime('today 8:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 11:00 AM') ) 
									{
										$nextAvailableCallingTime = '11:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('MST', 'MDT')) )
							{
								if( time() >= strtotime('today 10:00 am') && time() < strtotime('today 7:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 10:00 AM') ) 
									{
										$nextAvailableCallingTime = '10:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
						}
						
						//49 -Jory Bowers
						//56 - Valerie Strickland
						if( in_array($customer->id, array(49, 56)) )
						{
							if( in_array($this->get_timezone_abbreviation($timeZone), array('EST', 'EDT')) )
							{
								if( time() >= strtotime('today 7:00 am') && time() < strtotime('today 3:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 7:00 AM') ) 
									{
										$nextAvailableCallingTime = '7:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('CST', 'CDT')) )
							{
								if( time() >= strtotime('today 8:00 am') && time() < strtotime('today 4:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 8:00 AM') ) 
									{
										$nextAvailableCallingTime = '8:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('MST', 'MDT')) )
							{
								if( time() >= strtotime('today 9:00 am') && time() < strtotime('today 5:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 9:00 AM') ) 
									{
										$nextAvailableCallingTime = '9:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('PST', 'PDT')) )
							{
								if( time() >= strtotime('today 10:00 am') && time() < strtotime('today 6:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 10:00 AM') ) 
									{
										$nextAvailableCallingTime = '10:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
						}
						
						//2011 - Indigo Sky
						if( $customer->id == 2011 )
						{
							date_default_timezone_set('America/Denver');
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('EST', 'EDT')) )
							{
								if( time() >= strtotime('today 8:00 am') && time() < strtotime('today 5:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 8:00 AM') ) 
									{
										$nextAvailableCallingTime = '8:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('CST', 'CDT')) )
							{
								if( time() >= strtotime('today 9:00 am') && time() < strtotime('today 6:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 9:00 AM') ) 
									{
										$nextAvailableCallingTime = '9:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('MST', 'MDT')) )
							{
								if( time() >= strtotime('today 10:00 am') && time() < strtotime('today 7:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 10:00 AM') ) 
									{
										$nextAvailableCallingTime = '10:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('PST', 'PDT')) )
							{
								if( time() >= strtotime('today 11:00 am') && time() < strtotime('today 8:00 pm') )
								{ 
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 11:00 AM') ) 
									{
										$nextAvailableCallingTime = '11:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
						}
										
						//2607 - Inside Sales
						if( $customer->id == 2607 || $customer->id == 2683 )
						{
							date_default_timezone_set('America/Denver');
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('EST', 'EDT')) )
							{
								if( time() >= strtotime('today 7:00 am') && time() < strtotime('today 2:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 7:00 AM') ) 
									{
										$nextAvailableCallingTime = '7:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('CST', 'CDT')) )
							{
								if( time() >= strtotime('today 8:00 am') && time() < strtotime('today 3:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 8:00 AM') ) 
									{
										$nextAvailableCallingTime = '8:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('MST', 'MDT')) )
							{
								if( time() >= strtotime('today 9:00 am') && time() < strtotime('today 4:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 9:00 AM') ) 
									{
										$nextAvailableCallingTime = '9:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('PST', 'PDT')) )
							{
								if( time() >= strtotime('today 10:00 am') && time() < strtotime('today 5:00 pm') )
								{ 
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 10:00 AM') ) 
									{
										$nextAvailableCallingTime = '10:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
						}
									
						//1966 - Graton
						if( $customer->id == 1966 )
						{
							if( $customerSkill->skill_id == 39 ) //event enrollment skill
							{
								if( in_array($this->get_timezone_abbreviation($timeZone), array('EST', 'EDT')) )
								{
									if( time() >= strtotime('today 7:00 am') && time() < strtotime('today 5:30 pm') )
									{
										$nextAvailableCallingTime = 'Now';
									}
									else
									{
										if( time() < strtotime('today 7:00 AM') ) 
										{
											$nextAvailableCallingTime = '7:00 AM';
										}
										else
										{
											$nextAvailableCallingTime = 'Next Shift';
										}
									}
								}
								
								if( in_array($this->get_timezone_abbreviation($timeZone), array('CST', 'CDT')) )
								{
									if( time() >= strtotime('today 8:00 am') && time() < strtotime('today 6:30 pm') )
									{
										$nextAvailableCallingTime = 'Now';
									}
									else
									{
										if( time() < strtotime('today 8:00 AM') ) 
										{
											$nextAvailableCallingTime = '8:00 AM';
										}
										else
										{
											$nextAvailableCallingTime = 'Next Shift';
										}
									}
								}
								
								if( in_array($this->get_timezone_abbreviation($timeZone), array('MST', 'MDT')) )
								{
									if( time() >= strtotime('today 9:00 am') && time() < strtotime('today 7:30 pm') )
									{
										$nextAvailableCallingTime = 'Now';
									}
									else
									{
										if( time() < strtotime('today 9:00 AM') ) 
										{
											$nextAvailableCallingTime = '9:00 AM';
										}
										else
										{
											$nextAvailableCallingTime = 'Next Shift';
										}
									}
								}
								
								if( in_array($this->get_timezone_abbreviation($timeZone), array('PST', 'PDT')) )
								{
									if( time() >= strtotime('today 10:00 am') && time() < strtotime('today 8:30 pm') )
									{
										$nextAvailableCallingTime = 'Now';
									}
									else
									{
										if( time() < strtotime('today 10:00 AM') ) 
										{
											$nextAvailableCallingTime = '10:00 AM';
										}
										else
										{
											$nextAvailableCallingTime = 'Next Shift';
										}
									}
								}
							}
							else
							{

								if( in_array($this->get_timezone_abbreviation($timeZone), array('EST', 'EDT')) )
								{
									if( time() >= strtotime('today 8:00 am') && time() < strtotime('today 5:00 pm') )
									{
										$nextAvailableCallingTime = 'Now';
									}
									else
									{
										if( time() < strtotime('today 8:00 AM') ) 
										{
											$nextAvailableCallingTime = '8:00 AM';
										}
										else
										{
											$nextAvailableCallingTime = 'Next Shift';
										}
									}
								}
								
								if( in_array($this->get_timezone_abbreviation($timeZone), array('CST', 'CDT')) )
								{
									if( time() >= strtotime('today 9:00 am') && time() < strtotime('today 6:00 pm') )
									{
										$nextAvailableCallingTime = 'Now';
									}
									else
									{
										if( time() < strtotime('today 9:00 AM') ) 
										{
											$nextAvailableCallingTime = '9:00 AM';
										}
										else
										{
											$nextAvailableCallingTime = 'Next Shift';
										}
									}
								}
								
								if( in_array($this->get_timezone_abbreviation($timeZone), array('PST', 'PDT')) )
								{
									if( time() >= strtotime('today 11:00 am') && time() < strtotime('today 8:00 pm') )
									{
										$nextAvailableCallingTime = 'Now';
									}
									else
									{
										if( time() < strtotime('today 11:00 AM') ) 
										{
											$nextAvailableCallingTime = '11:00 AM';
										}
										else
										{
											$nextAvailableCallingTime = 'Next Shift';
										}
									}
								}
								
								if( in_array($this->get_timezone_abbreviation($timeZone), array('MST', 'MDT')) )
								{
									if( time() >= strtotime('today 10:00 am') && time() < strtotime('today 7:00 pm') )
									{
										$nextAvailableCallingTime = 'Now';
									}
									else
									{
										if( time() < strtotime('today 10:00 AM') ) 
										{
											$nextAvailableCallingTime = '10:00 AM';
										}
										else
										{
											$nextAvailableCallingTime = 'Next Shift';
										}
									}
								}
							}
						}
						
						//Saturday call schedule for SF Policy Review per Appointment / Name
						if( date('w') == 6 && in_array($customerSkill->skill_id, array(33, 34)) )
						{
							date_default_timezone_set('America/Denver');
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('EST', 'EDT')) )
							{
								if( time() >= strtotime('today 8:00 am') && time() < strtotime('today 1:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 8:00 AM') ) 
									{
										$nextAvailableCallingTime = '8:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('CST', 'CDT')) )
							{
								if( time() >= strtotime('today 9:00 am') && time() < strtotime('today 2:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 9:00 AM') ) 
									{
										$nextAvailableCallingTime = '9:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('MST', 'MDT')) )
							{
								if( time() >= strtotime('today 10:00 am') && time() < strtotime('today 3:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 10:00 AM') ) 
									{
										$nextAvailableCallingTime = '10:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('PST', 'PDT')) )
							{
								if( time() >= strtotime('today 11:00 am') && time() < strtotime('today 4:00 pm') )
								{ 
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 11:00 AM') ) 
									{
										$nextAvailableCallingTime = '11:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
						}
					}
				}
			}
		}
		else
		{
			$nextAvailableCallingTime = 'Next Shift';
		}

		if( time() >= strtotime('today 8:00 pm') )
		{
			$nextAvailableCallingTime = 'Next Shift';
		}

		return $nextAvailableCallingTime;
	}

	private function checkLeadRetryTime($lead)
	{
		date_default_timezone_set('America/Denver');
		
		$leadIsCallable = false;
		
		$latestCall = LeadCallHistory::model()->find(array(
			'condition' => 'lead_id = :lead_id',
			'params' => array(
				':lead_id' => $lead->id,
			),
			'order' => 'date_created DESC'
		));
		
		
		if( isset($lead->customer) )
		{
			if( $latestCall )
			{
				// if( in_array($lead->customer->getTimeZone(), array('MST', 'PST', 'HAST', 'AKST')) )
				// {
					// if( $lead->customer->getTimeZone() == 'PST' && time() >= strtotime('today 5:00 pm')  )
					// {
						// if ( strtotime($latestCall->end_call_time) <= strtotime('-12 hours') ) 
						// {
							// $leadIsCallable = true;
						// }
					// }
					
					// if( in_array($lead->customer->getTimeZone(), array('HAST')) && time() >= strtotime('today 6:00 pm')  )
					// {
						// if ( strtotime($latestCall->end_call_time) <= strtotime('-12 hours') ) 
						// {
							// $leadIsCallable = true;
						// }
					// }
					
					// if( in_array($lead->customer->getTimeZone(), array('AKST')) && time() >= strtotime('today 7:00 pm')  )
					// {
						// if ( strtotime($latestCall->end_call_time) <= strtotime('-12 hours') ) 
						// {
							// $leadIsCallable = true;
						// }
					// }
					
					// if( in_array($lead->customer->getTimeZone(), array('MST', 'PST', 'HAST', 'AKST')) )
					// {
						// if ( strtotime($latestCall->end_call_time) <= strtotime('-12 hours') ) 
						// {
							// $leadIsCallable = true;
						// }
					// }
				// }
				// elseif( in_array($lead->customer->getTimeZone(), array('CST', 'EST')) )
				// {
					// if ( strtotime($latestCall->end_call_time) <= strtotime('-12 hours') ) 
					// {
						// $leadIsCallable = true;
					// }
				// }
				// else
				// {
					if( $latestCall->is_skill_child == 1 && isset($latestCall->skillChildDisposition) )
					{
						if( time() >= (strtotime($latestCall->end_call_time) + $latestCall->skillChildDisposition->retry_interval) )
						{
							$leadIsCallable = true;
						}
					}
					elseif( $latestCall->is_skill_child == 0 && isset($latestCall->skillDisposition) )
					{
						if( time() >= (strtotime($latestCall->end_call_time) + $latestCall->skillDisposition->retry_interval) )
						{
							$leadIsCallable = true;
						}
					}
					else
					{
						if ( strtotime($latestCall->end_call_time) <= strtotime('-12 hours') ) 
						{
							$leadIsCallable = true;
						}
					} 
				// }
				
				// echo 'lead id: ' . $lead->id;
				// echo '<br>';
				// echo 'timezone: ' . $lead->customer->getTimeZone();
				// echo '<br>';
				// echo 'last call time: ' . date('m/d/Y g:i A', strtotime($latestCall->end_call_time));
				// echo '<br>';
				
				// if( $latestCall->is_skill_child == 1 )
				// {
					// echo 'dispo: ' . $latestCall->skillChildDisposition->skill_child_disposition_name;
					// echo '<br>';
					// echo 'dispo retry interval: ' . ($latestCall->skillChildDisposition->retry_interval / 3600) . ' hours';
					// echo '<br>';
					// echo 'next callable time: ' . date('m/d/Y g:i A', (strtotime($latestCall->end_call_time) + $latestCall->skillChildDisposition->retry_interval));
				// }
				// else
				// {
					// echo 'dispo: ' . $latestCall->skillDisposition->skill_disposition_name;
					// echo '<br>';
					// echo 'dispo retry interval: ' . ($latestCall->skillDisposition->retry_interval / 3600) . ' hours';
					// echo '<br>';
					// echo 'next callable time: ' . date('m/d/Y g:i A', (strtotime($latestCall->end_call_time) + $latestCall->skillDisposition->retry_interval));
				// } 

				// echo '<br>';
				// echo 'leadIsCallable: ' . $leadIsCallable;
				// echo '<br>';
				// echo '<hr>';
				// echo '<br>';
			}
			else
			{
				$leadIsCallable = true;
				
				// echo 'No calls yet';
				// echo '<br>';
				// echo '<hr>';
				// echo '<br>';
			}
		}
		
		if( in_array($lead->customer_id, array('2646')) )
		{
			$leadIsCallable = true;
		}
		
		return $leadIsCallable;
	}
	
	private function get_timezone_abbreviation($timezone_id)
	{
		if($timezone_id){
			$abb_list = timezone_abbreviations_list();

			$abb_array = array();
			foreach ($abb_list as $abb_key => $abb_val) {
				foreach ($abb_val as $key => $value) {
					$value['abb'] = $abb_key;
					array_push($abb_array, $value);
				}
			}

			foreach ($abb_array as $key => $value) {
				if($value['timezone_id'] == $timezone_id){
					return strtoupper($value['abb']);
				}
			}
		}
		return false;
	}
	
}

?>