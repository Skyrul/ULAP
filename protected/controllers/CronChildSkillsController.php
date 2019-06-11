<?php 

ini_set('memory_limit', '10000M');
set_time_limit(0);

class CronChildSkillsController extends Controller
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
		
		if( !isset($_GET['debug']) && time() < strtotime('today 6:00 am') )
		{
			echo date('g:i A');
			exit;
		}
			
		echo 'Process started at ' . date('g:i A');
		
		echo '<br><br>';
		
		$cronChildSkillSettingOnGoing = CronChildSkillSetting::model()->find(array(
			'condition' => 'ongoing=1',
		));
		
		if( count($cronChildSkillSettingOnGoing) == 0 )
		{
			$cronChildSkillSettingNew = CronChildSkillSetting::model()->find(array(
				'condition' => 'ongoing=0',
			));
		
			$cronChildSkillSettingNew->ongoing = 1;
			$cronChildSkillSettingNew->date_ongoing = date("Y-m-d H:i:s");
			$cronChildSkillSettingNew->completed = 0;
			// $cronChildSkillSettingNew->date_completed = date("Y-m-d H:i:s");
			$cronChildSkillSettingNew->save(false);
			
			
			$dbUpdates = 0;
			
			$this->unsetLeadHopperOfIdleAgents();
			
			//remove leads with end call, dispo and done status on hopper, enable confirms and callback
			$leadHopperEntries = LeadHopper::model()->findAll(array(
				// 'condition' => 'status != "INCALL"',
				 
				'condition' => '
					( t.type=3 AND DATE(t.appointment_date) = DATE(NOW()) ) 
					OR ( t.type =2 AND t.callback_date IS NOT NULL AND DATE(NOW()) >= DATE(t.callback_date) )
					OR ( t.type IN (5,6,7) )
				',
				// 'condition' => '(status="DONE" OR (type=3 AND status="READY"))',
				// 'condition' => '(status="DONE" OR (type=3 AND status="READY")) AND customer_id = 575',
				// 'condition' => 'customer_id IS NOT NULL AND skill_id IS NOT NULL AND lead_id IS NOT NULL',
			)); 
			
			if( $leadHopperEntries )
			{	
				$ctr = 1;
				$customerSkillHolder = array();
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
					
					if(empty($timeZone))
					{
						$timeZone = $customer->phone_timezone;
					}
					
					$currentDateTime = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('America/Chicago'));
					$currentDateTime->setTimezone(new DateTimeZone('America/Denver'));

					if(!empty($timeZone))
					{
						$leadLocalTime = $currentDateTime->setTimezone(new DateTimeZone(timezone_name_from_abbr($timeZone)));
					
						
						## check customer's contract setup from the Customer Skill##
						$customerIsCallable = false;
						
						$customerSkill = CustomerSkill::model()->find(array(
							'condition' => 'customer_id = :customer_id AND skill_id = :skill_id AND status=1',
							'params' => array(
								':customer_id' => $leadHopperEntry->customer_id,
								':skill_id' => $leadHopperEntry->skill_id,
							),
						));
						
						if( $customerSkill )
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
						}
						
						echo 'Customer ID: '.$leadHopperEntry->customer_id.' | Skill ID: '.$leadHopperEntry->skill_id . ' | TYPE: ' . $leadHopperEntry->getType() . ' | STATUS: ' . $leadHopperEntry->status . ' | Is Callable: ' . $customerIsCallable;
						echo '<br><br>';
						#### end ###
					
					
						if(in_array($leadHopperEntry->type, array(LeadHopper::TYPE_CONTACT, LeadHopper::TYPE_LEAD_SEARCH)) )
						{
							if(!$customerIsCallable)
							{
								if( $leadHopperEntry->delete() )
								{
									$dbUpdates++;
								}
							}
							else
							{
								if($leadHopperEntry->status == 'DONE')
								{
									if( $leadHopperEntry->delete() )
									{
										$dbUpdates++;
									}
								}
							}
						}

						if($leadHopperEntry->type == LeadHopper::TYPE_CALLBACK)
						{
							if( $lead->status == 1 )
							{
								if($customerIsCallable && !empty($leadHopperEntry->callback_date) && strtotime($leadLocalTime->format('Y-m-d g:i a')) >= strtotime($leadHopperEntry->callback_date) && $this->checkLeadRetryTime($lead))
								{
									$leadHopperEntry->status = 'READY';
								}
								else
								{
									$leadHopperEntry->status = 'DONE';
								}
								
								if( $leadHopperEntry->save(false) )
								{
									$dbUpdates++;
								}
							}
							else
							{
								if( $leadHopperEntry->delete() )
								{
									$dbUpdates++;
								}
							}
						}
						
						if( $leadHopperEntry->type == LeadHopper::TYPE_RESCHEDULE || $leadHopperEntry->type == LeadHopper::TYPE_NO_SHOW_RESCHEDULE )
						{
							/* $customerSkill = CustomerSkill::model()->find(array(
								'condition' => 'customer_id = :customer_id AND skill_id = :skill_id AND status=1',
								'params' => array(
									':customer_id' => $leadHopperEntry->customer_id,
									':skill_id' => $leadHopperEntry->skill_id,
								),
							)); */
							
							if( $customerSkill )
							{
								/* if( $customerSkill->status == 1 && $customerIsCallable)
								{ */
									$customerSkillChild = CustomerSkillChild::model()->find(array(
										'condition' => 'customer_id = :customer_id AND skill_id = :skill_id AND customer_skill_id = :customer_skill_id AND skill_child_id = :skill_child_id',
										'params' => array(
											':customer_id' => $leadHopperEntry->customer_id,
											':skill_id' => $leadHopperEntry->skill_id,
											':customer_skill_id' => $customerSkill->id,
											':skill_child_id' => $leadHopperEntry->skill_child_reschedule_id
										),
									));
						
									if( $customerSkillChild )
									{
										if( $customerSkillChild->is_enabled == 1 )
										{
											if( isset($leadHopperEntry->lead) )
											{
												if( $leadHopperEntry->status == 'DONE' && $this->checkTimeZone($customerSkill, 'lead', $leadHopperEntry->lead, $leadHopperEntry->skill_child_reschedule_id) == 'Now' )
												{
													$leadHopperEntry->agent_account_id = null;
													
													if($customerIsCallable && $customerSkill->status == 1)
													{
														$leadHopperEntry->status = 'READY';
													}
													
													$latestCallHistory = LeadCallHistory::model()->find(array(
														'condition' => 'lead_id = :lead_id AND status !=4 AND disposition="Client Complete"',
														'params' => array(
															':lead_id' => $leadHopperEntry->lead->id, 
														),
													));
													
													if( $latestCallHistory )
													{
														if( $leadHopperEntry->delete() )
														{
															$dbUpdates++;
														}
													}
													
													if( $leadHopperEntry->save(false) )
													{
														$dbUpdates++;
													}
												}
												
												if( $leadHopperEntry->status != 'DONE' && $this->checkTimeZone($customerSkill, 'lead', $leadHopperEntry->lead, $leadHopperEntry->skill_child_reschedule_id) != 'Now' )
												{
													$leadHopperEntry->status = 'DONE';
										
													if( $leadHopperEntry->save(false) )
													{
														$dbUpdates++;
													}
												}
												
											}
										}
										else
										{
											
											if( $leadHopperEntry->delete() )
											{
												$dbUpdates++;
											}
										}
									}
									else
									{
										if( $leadHopperEntry->delete() )
										{
											$dbUpdates++;
										}
									}
								/* }
								else
								{
									$leadHopperEntry->status = 'DONE';
									
									if( $leadHopperEntry->save(false) )
									{
										$dbUpdates++;
									}
								} */
							}
							else
							{
								if( $leadHopperEntry->delete() )
								{
									$dbUpdates++;
								}
							}
						
						}
						 
						if( $leadHopperEntry->type == LeadHopper::TYPE_CONFIRMATION_CALL)
						{
							/* $customerSkill = CustomerSkill::model()->find(array(
								'condition' => 'customer_id = :customer_id AND skill_id = :skill_id AND status=1',
								'params' => array(
									':customer_id' => $leadHopperEntry->customer_id,
									':skill_id' => $leadHopperEntry->skill_id,
								),
							)); */
							
							if( $customerSkill )
							{
								if(date('Y-m-d') == date('Y-m-d', strtotime($leadHopperEntry->appointment_date)))
								{
									
									if($customerSkill->status == 1 && $customerIsCallable)
									{
									 
										$customerSkillChild = CustomerSkillChild::model()->find(array(
											'condition' => 'customer_id = :customer_id AND skill_id = :skill_id AND customer_skill_id = :customer_skill_id AND skill_child_id = :skill_child_id',
											'params' => array(
												':customer_id' => $leadHopperEntry->customer_id,
												':skill_id' => $leadHopperEntry->skill_id,
												':customer_skill_id' => $customerSkill->id,
												':skill_child_id' => $leadHopperEntry->skill_child_confirmation_id
											),
										));
							
										if( $customerSkillChild )
										{
											if( $customerSkillChild->is_enabled == 1 )
											{
												if( isset($leadHopperEntry->lead) )
												{
													echo 'Hopper ID: ' . $leadHopperEntry->id;
													echo '<br>';	
													echo 'Lead ID: ' . $leadHopperEntry->lead_id; 
													echo '<br>';	
													echo 'Lead Name: ' . $leadHopperEntry->lead->first_name.' '.$leadHopperEntry->lead->last_name;
													echo '<br>';	
													echo 'Check timezone: ' . $this->checkTimeZone($customerSkill, 'lead', $leadHopperEntry->lead, $leadHopperEntry->skill_child_confirmation_id);
													echo '<br>';
													echo '<br>';
													
													if( $leadHopperEntry->status == 'DONE' && $this->checkTimeZone($customerSkill, 'lead', $leadHopperEntry->lead, $leadHopperEntry->skill_child_confirmation_id) == 'Now' )
													{
														$leadHopperEntry->status = 'READY';
														
														if( $leadHopperEntry->save(false) )
														{
															$dbUpdates++;
														}
													}
													
													if( $leadHopperEntry->status == 'READY' && $this->checkTimeZone($customerSkill, 'lead', $leadHopperEntry->lead, $leadHopperEntry->skill_child_confirmation_id) != 'Now' )
													{
														$leadHopperEntry->status = 'DONE';
											
														if( $leadHopperEntry->save(false) )
														{
															$dbUpdates++;
														}
													}
													
												}
											}
											else
											{
												if( $leadHopperEntry->delete() )
												{
													$dbUpdates++;
												}
											}
										}
										else
										{
											if( $leadHopperEntry->delete() )
											{
												$dbUpdates++;
											}
										}
									}
									else
									{
										if( $leadHopperEntry->delete() )
										{ echo 'deleted';
											$dbUpdates++;
										}
									}
								}
								else
								{
									$leadHopperEntry->status = 'DONE';
									
									if( $leadHopperEntry->save(false) )
									{
										$dbUpdates++;
									}
								}
								
							}
							else
							{
								$leadHopperEntry->status = 'DONE';
								
								if( $leadHopperEntry->save(false) )
								{
									$dbUpdates++;
								}
							}
						}
								
						if( $leadHopperEntry->type == LeadHopper::TYPE_CONFLICT )
						{
							if( isset($leadHopperEntry->calendarAppointment) )
							{
								//approved, declined and alt suggested only
								if( in_array($leadHopperEntry->calendarAppointment->status, array(1,3,5)) )
								{
									//check if CONFLICT is more than 24 hours and still not called set agent_account_id to null so it can be reassigned to other agents
									if( time() >= strtotime('+24 hours', strtotime($leadHopperEntry->calendarAppointment->date_created)) )
									{
										$leadHopperEntry->agent_account_id = null;
									}
									
									if( $customerSkill )
									{
										if( $customerSkill->status == 1 && $customerIsCallable )
										{
											if( isset($leadHopperEntry->lead) )
											{
												echo 'Hopper ID: ' . $leadHopperEntry->id;
												echo '<br>';	
												echo 'Lead ID: ' . $leadHopperEntry->lead_id; 
												echo '<br>';	
												echo 'Lead Name: ' . $leadHopperEntry->lead->first_name.' '.$leadHopperEntry->lead->last_name;
												echo '<br>';	
												echo 'Check timezone: ' . $this->checkTimeZone($customerSkill, 'lead', $leadHopperEntry->lead, $leadHopperEntry->skill_child_confirmation_id);
												echo '<br>';

												if( $leadHopperEntry->status == 'DONE' && $this->checkTimeZone($customerSkill, 'lead', $leadHopperEntry->lead, $leadHopperEntry->skill_child_confirmation_id) == 'Now' )
												{
													$leadHopperEntry->status = 'READY';
													
													if( $leadHopperEntry->save(false) )
													{
														$dbUpdates++;
													}
												}
												
												if( $leadHopperEntry->status == 'READY' && $this->checkTimeZone($customerSkill, 'lead', $leadHopperEntry->lead, $leadHopperEntry->skill_child_confirmation_id) != 'Now' )
												{
													$leadHopperEntry->status = 'DONE';
										
													if( $leadHopperEntry->save(false) )
													{
														$dbUpdates++;
													}
												}
											}
										}
										else
										{
											$leadHopperEntry->status = 'DONE';
												
											if( $leadHopperEntry->save(false) )
											{
												$dbUpdates++;
											}
										}
									}
									else
									{
										$leadHopperEntry->status = 'DONE';
										
										if( $leadHopperEntry->save(false) )
										{
											$dbUpdates++;
										}
									}
								}
								else
								{
									$leadHopperEntry->status = 'DONE';
									
									if( $leadHopperEntry->save(false) )
									{
										$dbUpdates++;
									}
								}
							}
							else
							{
								$leadHopperEntry->status = 'DONE';
								
								if( $leadHopperEntry->save(false) )
								{
									$dbUpdates++;
								}
							}
						}
						
						echo $ctr++;
						echo '<br />';
						echo '<br />';
					}
				}
			}
			
			echo '<br><br> Process ended at '.date('H:i:s');
			
			echo '<br><br>dbUpdates: ' . $dbUpdates;
			
			$cronChildSkillSettingNew->ongoing = 0;
			// $cronCustomerContractSettingOnGoing->date_ongoing = date("Y-m-d H:i:s");
			$cronChildSkillSettingNew->completed = 1;
			$cronChildSkillSettingNew->date_completed = date("Y-m-d H:i:s");
			$cronChildSkillSettingNew->save(false);
		}
	}
	
	public function actionGetExpiredHoldCustomer()
	{
		$cronCustomerContractSettingOnGoing = CronCustomerContractSetting::model()->find(array(
			'condition' => 'ongoing=1',
		));
		
		if( count($cronCustomerContractSettingOnGoing) == 0 )
		{
			echo 'Initializing cron process...';
			$cronCustomerContractSettingNew = CronCustomerContractSetting::model()->find(array(
				'condition' => 'ongoing=0',
			));
		
			$cronCustomerContractSettingNew->ongoing = 1;
			$cronCustomerContractSettingNew->date_ongoing = date("Y-m-d H:i:s");
			$cronCustomerContractSettingNew->completed = 0;
			// $cronCustomerContractSettingNew->date_completed = date("Y-m-d H:i:s");
			$cronCustomerContractSettingNew->save(false);
			
			$customerSkills = CustomerSkill::model()->findAll(array(
				'with' => array('skill', 'contract'),
				'condition' => 't.status=1 AND skill.id IS NOT NULL AND contract.id IS NOT NULL',
				// 'limit' => 50,
			)); 
			
			$customerHolderFalse = array();
			foreach( $customerSkills as $customerSkill )
			{
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
				
				// echo 'Customer ID: '.$customerSkill->customer_id.' | Skill ID: '.$customerSkill->skill_id;
				// echo '<br>';
				// var_dump($customerIsCallable);
				// echo '<br>';
				
				if(!$customerIsCallable)
				{
					$customerHolderFalse[$customerSkill->customer_id.'-'.$customerSkill->skill_id] = $customerSkill->customer_id;
					LeadHopper::model()->updateAll(array('status' => 'DONE' ), 'customer_id IN ('.$customerSkill->customer_id.') AND skill_id IN ('.$customerSkill->skill_id.')');
					// $customerSkill->cron_expired_hold_contract_flag = 1;
					// $customerSkill->save(false);
				}
			}
			
			echo '<br>#########Canceled/Hold Contract####<br>';
			
			/* if(!empty($customerHolderFalse))
			{
				LeadHopper::model()->updateAll(array('status' => 'DONE' ), 'customer_id IN ('.implode(",", $customerHolderFalse).')' );
			} */
			
			echo implode(",", $customerHolderFalse);
			
			$cronCustomerContractSettingNew->ongoing = 0;
			// $cronCustomerContractSettingOnGoing->date_ongoing = date("Y-m-d H:i:s");
			$cronCustomerContractSettingNew->completed = 1;
			$cronCustomerContractSettingNew->date_completed = date("Y-m-d H:i:s");
			$cronCustomerContractSettingNew->save(false);
		}
		else
		{
			echo 'Process still ongoing...';
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
					if( strtotime( $currentLoginState->date_updated ) < strtotime('-12 seconds') )
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
	
	private function checkTimeZone($customerSkill, $type='customer', $lead=null, $skillChildId)
	{
		date_default_timezone_set('America/Denver');
		
		$nextAvailableCallingTime = '';
		
		$customer = $customerSkill->customer;
		
		$skillScheduleHolder = array();
			
		$currentDateTime = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('America/Denver'));
		// $currentDateTime->setTimezone(new DateTimeZone('America/Denver')); 
		
		//temp code to force certain customers to get no confirm dials 
		$georgiaArecodeCodes = array('229', '404', '470', '478', '678', '706', '762', '770', '912');
			
		$northCarolinaAreaCodes = array('252', '336', '704', '828', '910', '919', '980');
		
		$southCarolinaAreaCodes = array('803', '843', '864');
		
		$texasAreaCodes = array('210', '214', '254', '281', '361', '409', '469', '512', '682', '713', '806', '817', '830', '832', '903', '915', '936', '940', '956', '972', '979');
		
		$louisianaAreaCodes = array('225', '318', '337', '504', '985');
		
		$floridaAreaCodes = array('305', '321', '352', '386', '407', '561', '727', '754', '772', '786', '813', '850', '863', '904', '941', '954');
		
		
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
			// $skillSchedules = SkillSchedule::model()->findAll(array(
				// 'condition' => 'skill_id = :skill_id AND schedule_day = :schedule_day',
				// 'params' => array(
					// 'skill_id' => $customerSkill->skill_id,
					// ':schedule_day' => date('N'),
				// ),
			// ));
			
			$skillSchedules = SkillChildSchedule::model()->findAll(array(
				'condition' => 'skill_child_id = :skill_child_id AND schedule_day = :schedule_day',
				'params' => array(
					'skill_child_id' => $skillChildId,
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
				if( time() >= (strtotime($latestCall->end_call_time) + $latestCall->skillChildDisposition->retry_interval) )
				{
					$leadIsCallable = true;
				}
			}
			else
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
		
		return $leadIsCallable;
	}
	
}

?>