<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

ini_set('memory_limit', '10000M');
set_time_limit(0);

class ManualQueueCustomerController extends Controller
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
		$processCtr = 1;
		$existingCtr = 0;
	
		
		// $sql = '
			// SELECT csk.`customer_id` FROM ud_customer_skill csk
			// LEFT JOIN ud_skill sk ON sk.`id` = csk.`skill_id`
			// LEFT JOIN ud_contract co ON co.`id` = csk.`contract_id`
			// WHERE csk.`status`=1 
			// AND co.`id` IS NOT NULL
			// AND sk.`id` IS NOT NULL
			// AND customer_id NOT IN ( SELECT customer_id FROM ud_customer_queue_viewer )
		// ';
		
		// $command = Yii::app()->db->createCommand($sql);
		// $customers = $command->queryAll();
		
		// $customerIds = array();
		
		// if( $customers )
		// {
			// foreach( $customers as $customer )  
			// {
				// $customerIds[] = $customer['customer_id'];
			// }
		// }
		
		// $customerSkills = CustomerSkill::model()->findAll(array(
			// 'with' => array('skill', 'contract'),
			// 'condition' => 't.status=1 AND skill.id IS NOT NULL AND contract.id IS NOT NULL AND t.customer_id IN(1587, 1599)',
		// )); 	
		
		$customerSkills = CustomerSkill::model()->findAll(array(
			'with' => array('skill', 'contract'),
			'condition' => 't.status=1 AND skill.id IS NOT NULL AND contract.id IS NOT NULL',
			// 'limit' => 10,
			'order' => 't.date_created DESC',
			// 'offset' => $cronCustomerSkillCurrentOffset->value
		)); 
		
		echo 'count: ' . count($customerSkills);
		
		echo '<br><br>';

		foreach( $customerSkills as $customerSkill )
		{
			$existingQueueViewer = CustomerQueueViewer::model()->find(array(
				'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id',
				'params' => array(
					':customer_id' => $customerSkill->customer_id,
					':contract_id' => $customerSkill->contract_id,
					':skill_id' => $customerSkill->skill_id,
				),
			));
			
			if( empty($existingQueueViewer) )
			{
				if( $this->runCustomerEvaluation($customerSkill, $existingQueueViewer) )
				{
					$customerQueueViewerEvaluated++;
					$customerQueueViewerUpdates++;
				}
			}
			else
			{
				$existingCtr++;
				
				echo $existingQueueViewer->customer_id.' - '.$existingQueueViewer->customer_name;
				echo '<br>';
				echo $existingQueueViewer->contract_name;
				echo '<br>';
				echo $existingQueueViewer->skill_name;
				echo '<br>';
				echo '<br><hr><br>';
			}
			
			// echo $processCtr++;
			// echo '<br>';
		}

		
		echo '<br><br> Process ended at '.date('H:i:s');
		
		echo '<br><br>Customer Queue Viewer Table Changes: '.$customerQueueViewerUpdates;
		
		echo '<br><br>Customer Queue Viewer Evaluated: '.$customerQueueViewerEvaluated;
		
		echo '<br><br>existingCtr: '.$existingCtr;
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
		date_default_timezone_set('America/Denver');
		
		$nextAvailableCallingTime = '';
		
		$customer = $customerSkill->customer;
		
		$skillScheduleHolder = array();
			
		$currentDateTime = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('America/Denver'));
		// $currentDateTime->setTimezone(new DateTimeZone('America/Denver')); 
		
		
		//temp code to force certain customers to get no dials 
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
						$modifiedCustomerArray = array(522, 1966);
						
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
							if( time() >= strtotime('today 8:00 am') && time() < strtotime('today 7:00 pm') )
							{
								$nextAvailableCallingTime = 'Now';
								
								//temporary code to remove eastern leads after call schedule => 9am - 5pm  local time
								if( time() >= strtotime('today 4:00 pm') )
								{
									LeadHopper::model()->deleteAll(array(
										'condition' => 'customer_id = :customer_id AND lead_timezone IN ("EST", "EDT") AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
										'params' => array(
											':customer_id' => $customer->id,
										),
									));
								}
								
								//temporary code to remove central leads after call schedule => 9am - 5pm  local time
								if( time() >= strtotime('today 5:00 pm') )
								{
									LeadHopper::model()->deleteAll(array(
										'condition' => 'customer_id = :customer_id AND lead_timezone IN ("CST", "CDT") AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
										'params' => array(
											':customer_id' => $customer->id,
										),
									));
								}
											
								//temporary code to remove mountain leads after call schedule => 9am - 5pm  local time
								if( time() >= strtotime('today 6:00 pm') )
								{
									LeadHopper::model()->deleteAll(array(
										'condition' => 'customer_id = :customer_id AND lead_timezone IN ("MST", "MDT") AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
										'params' => array(
											':customer_id' => $customer->id,
										),
									));
								}
								
								//temporary code to remove pacific leads after call schedule => 9am - 5pm  local time
								if( time() >= strtotime('today 7:00 pm') )
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
								if( time() < strtotime('today 8:00 AM') ) 
								{
									$nextAvailableCallingTime = '8:00 AM - 7:00 PM';
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
						$modifiedCustomerArray = array(522, 1966);
						
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
							if( in_array($this->get_timezone_abbreviation($timeZone), array('EST', 'EDT')) )
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
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('CST', 'CDT')) )
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
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('MST', 'MDT')) )
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
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('PST', 'PDT')) )
							{
								if( time() >= strtotime('today 11:00 am') && time() < strtotime('today 7:00 pm') )
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
		$contractedAmount = 0;
		
		$availableCallingBlocks = '';
		
		$availableCallingBlock_A = 0;
		$availableCallingBlock_B = 0;
		$availableCallingBlock_C = 0;
		
		$noShowCount = 0;
		
		$callAgent = '';
		
		$accountDateCreated = null;
		$enrollmentDate = null;
		$historyEndDateChanged = null;
		$historyEndDateChanger = null;
		
		$historyCreditAddedDate = null;
		$historyCreditChanger = null;
		$historyCreditAmount = null;
		
		$nextAvailableCallingTime = $this->checkTimeZone($customerSkill);
		
		$numberOfWorkingDays = $this->getWorkingDaysForThisMonth(date('Y-m-1'), date('Y-m-t'), 'totalCount');
		$dialingDaysInBillingCycle = $this->getWorkingDaysForThisMonth(date('Y-m-1'), date('Y-m-t'), 'pastCount');	
		
		if( $dialingDaysInBillingCycle == 0 )
		{
			$dialingDaysInBillingCycle = 1;
		}
		
		$customer = Customer::model()->findByPk($customerSkill->customer_id);
		$skill = Skill::model()->findByPk($customerSkill->skill_id);
		$contract = $customerSkill->contract;
		
		$maxDials = $skill->max_dials;
		
		$customerIsCallable = false;	

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
		
		if( $existingQueueViewer )
		{
			if( !empty($existingQueueViewer->removal_start_date) && !empty($existingQueueViewer->removal_end_date) )
			{
				if( time() >= strtotime($existingQueueViewer->removal_start_date) && time() <= strtotime($existingQueueViewer->removal_end_date) )
				{
					$customerIsCallable = false;
					
					$nextAvailableCallingTime = 'Removed';
				}
			}
		}
		
		
		//get callable leads
		$callableLeadsSql = "
			SELECT 
				COUNT(t.id) as total_count,
				(
					SELECT COUNT(lch.id) as current_dials 
					FROM ud_lead_call_history lch
					LEFT JOIN ud_lists ls
					ON lch.list_id = ls.id					
					WHERE lch.customer_id = '".$customer->id."'
					AND ls.skill_id = '".$customerSkill->skill_id."'
					AND lch.date_created >= '".date('Y-m-01 00:00:00')."' 
					AND lch.date_created <= '".date('Y-m-t 23:59:59')."'
				) as current_dials,
				COUNT(t.id) as total_count,
				(
					SELECT DISTINCT(COUNT(lch.lead_id)) as current_dials 
					FROM ud_lead_call_history lch 
					LEFT JOIN ud_lists ls
					ON lch.list_id = ls.id		
					WHERE lch.customer_id = '".$customer->id."'
					AND ls.skill_id = '".$customerSkill->skill_id."'
					AND lch.date_created >= '".date('Y-m-01 00:00:00')."'
					AND lch.date_created <= '".date('Y-m-t 23:59:59')."'
				) as called_leads,
				(
					SUM(
						CASE
						WHEN t.number_of_dials < 1 THEN 1
						ELSE 0
						END
					)
				) as available_calling_block_a,
				(
					SUM(
						CASE
						WHEN t.number_of_dials < 2 THEN 1
						ELSE 0
						END
					)
				) as available_calling_block_b,
				(
					SUM(
						CASE
						WHEN t.number_of_dials < 3 THEN 1
						ELSE 0
						END
					)
				) as available_calling_block_c
			FROM ud_lead t
			LEFT JOIN ud_lists list ON list.id = t.list_id
			LEFT JOIN ud_skill skill ON skill.id = list.skill_id
			WHERE list.status = 1 
			AND t.type = 1
			AND t.status = 1
			AND t.number_of_dials < (skill.max_dials * 3)
			AND list.skill_id = '".$skill->id."' 
			AND list.customer_id = '".$customer->id."'
			AND (recertify_date != '0000-00-00' AND recertify_date IS NOT NULL AND NOW() <= recertify_date)
		";

		$command = Yii::app()->db->createCommand($callableLeadsSql);
		$callableLeads = $command->queryRow();
		
		$customerLeads = Lead::model()->findAll(array(
			'with' => array('list', 'list.skill'),
			'condition' => '
				list.status = 1
				AND t.type = 1
				AND t.status = 1
				AND t.number_of_dials < (skill.max_dials * 3)
				AND list.skill_id = :skill_id
				AND list.customer_id = :customer_id
				AND (recertify_date != "0000-00-00" AND recertify_date IS NOT NULL AND NOW() <= recertify_date)
			',
			'params' => array(
				':skill_id' => $skill->id,
				':customer_id' => $customer->id
			),
		));
		
		if( $customerLeads  )
		{
			foreach( $customerLeads as $customerLead )
			{
				$isCallbackOrAppointment = LeadHopper::model()->findAll(array(
					'condition' => 'lead_id = :lead_id AND type IN (2,3)',
					'params' => array(
						':lead_id' => $customerLead->id,
					),
				));
				
				if( $isCallbackOrAppointment )
				{
					$notCompletedLeads++;
				}
				else
				{
					$inQueueOrAlreadyCalled = LeadHopper::model()->findAll(array(
						'condition' => 'lead_id = :lead_id AND type=1',
						'params' => array(
							':lead_id' => $customerLead->id,
						),
					));
				
					if( $this->checkLeadRetryTime($customerLead) && empty($inQueueOrAlreadyCalled) )
					{
						$availableLeads++;
					}
					else
					{
						$notCompletedLeads++;
					}
				}
			}
		}

		if($contract->fulfillment_type != null )
		{
			if( $contract->fulfillment_type == Contract::TYPE_FULFILLMENT_GOAL_VOLUME )
			{
				if( !empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME]) )
				{
					##get Appointment that has been scheduled ##
					$appointmentSetMTDSql = "
						SELECT count(distinct lch.lead_id) AS totalCount 
						FROM ud_lead_call_history lch 
						LEFT JOIN ud_lists ls ON ls.id = lch.list_id 
						LEFT JOIN ud_calendar_appointment ca ON ca.id = lch.calendar_appointment_id 
						WHERE ca.title IN ('APPOINTMENT SET', 'CANCEL APPOINTMENT', 'RESCHEDULE APPOINTMENT') 
						AND lch.disposition = 'Appointment Set'
						AND lch.date_created >= '".date('Y-m-01 00:00:00')."' 
						AND lch.date_created <= '".date('Y-m-t 23:59:59')."'
						AND lch.customer_id = '".$customerSkill->customer->id."'
						AND ls.skill_id = '".$customerSkill->skill_id."' 
					";
				
					
					$command = Yii::app()->db->createCommand($appointmentSetMTDSql);
					$appointmentSetMTD = $command->queryRow();
					
					//insert appointment count
					$insertAppointmentMTDSql = "
						SELECT count(distinct ca.lead_id) AS totalCount 
						FROM ud_calendar_appointment ca
						LEFT JOIN ud_lead ld ON ld.id = ca.lead_id
						LEFT JOIN ud_lists ls ON ls.id = ld.list_id
						WHERE ca.title = 'INSERT APPOINTMENT' 
						AND ca.status != 4
						AND ca.date_created >= '".date('Y-m-01 00:00:00')."' 
						AND ca.date_created <= '".date('Y-m-t 23:59:59')."'				
						AND ld.customer_id = '".$customerSkill->customer->id."'						
						AND ls.skill_id = '".$customerSkill->skill_id."'   
					";
					
					$command = Yii::app()->db->createCommand($insertAppointmentMTDSql);
					$insertAppointmentMTD = $command->queryRow();
					
					//no show count
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
					
					$appointmentSetCount = $appointmentSetMTD['totalCount'] + $noShowMTD['totalCount'] + $insertAppointmentMTD['totalCount'];
					
					if( $noShowMTD['totalCount'] > 3 )
					{
						$appointmentSetCount = $appointmentSetMTD['totalCount']-3;
					}
					else
					{
						$appointmentSetCount = $appointmentSetCount-$noShowMTD['totalCount'];
					}
					
					foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME] as $key => $subsidyLevel )
					{
						$customerSkillLevelArray = $customerSkill->getCustomerSkillLevelArray();
						$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;

						if( $customerSkillLevelArrayGroup != null )
						{							
							if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
							{
								$totalLeads += ( $subsidyLevel['goal'] * $customerSkillLevelArrayGroup->quantity );
								
								$contractedAmount += ( $subsidyLevel['amount'] * $customerSkillLevelArrayGroup->quantity );
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
					
					if( $appointmentSetCount > 0 && $totalLeads > 0 && $appointmentSetCount >= $totalLeads )
					{
						$nextAvailableCallingTime = 'Goal Appointment Reached';
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
								
								$contractedAmount += ( $subsidyLevel['amount'] * $customerSkillLevelArrayGroup->quantity );
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
		
		echo '<br><br>';
		echo '<br><br>';
		
		echo $callableLeadsSql;
		
		echo '<br><br>';
		echo '<br><br>';
		
		echo 'Customer ID: '.$customerSkill->customer_id.' => running evaluation script';
		echo '<br><br>';
		echo 'skill_id: ' . $skill->id;
		echo '<br><br>';
		echo 'customer_id: ' . $customer->id;
		echo '<br><br>';
		echo 'callableLeads: ' . $callableLeads['total_count'];
		echo '<br><br>';
		echo 'totalLeads: ' . $totalLeads;
		echo '<br><br>';
		echo 'currentDials: ' . $callableLeads['current_dials'];
		echo '<br><br>';
		echo 'available_calling_block_a: ' . $callableLeads['available_calling_block_a'];
		echo '<br><br>';
		echo 'available_calling_block_b: ' . $callableLeads['available_calling_block_b'];
		echo '<br><br>';
		echo 'available_calling_block_c: ' . $callableLeads['available_calling_block_c'];
		echo '<br><br>';
		echo  $contract->fulfillment_type == 1 ? 'fulfillment_type: GOAL' : 'fulfillment_type: LEAD';
		echo '<br><br>';
		echo '<br><br>';
	
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
		
		$appointmentSetCount = $appointmentSetMTD['totalCount'] + $noShowMTD['totalCount'];
		
		if( $noShowMTD['totalCount'] > 3 )
		{
			$appointmentSetCount = $appointmentSetMTD['totalCount']-3;
		}
		else
		{
			$appointmentSetCount = $appointmentSetCount-$noShowMTD['totalCount'];
		}
	
		if( $callableLeads['total_count'] > 0 && $totalLeads > 0 )
		{
			//get available calling blocks
			if( $availableCallingBlocks == '' )
			{
				if( $callableLeads['available_calling_block_a'] > 0 )
				{
					$availableCallingBlocks .= 'A';
				}
				
				if( $callableLeads['available_calling_block_b'] > 0 )
				{
					$availableCallingBlocks .= 'B';
				}
				
				if( $callableLeads['available_calling_block_c'] > 0 )
				{
					$availableCallingBlocks .= 'C';
				}
			}
			
			//get total potential dials
			if( $customerSkill->contract->fulfillment_type == 1 )
			{
				$totalPotentialDials = ($totalLeads - $appointmentSetCount);
			}
			else
			{
				// $totalPotentialDials += ($maxDials - $leadDialCount);
				$totalPotentialDials = (($callableLeads['called_leads'] * $maxDials) - $callableLeads['current_dials']);
				
				if( $totalPotentialDials == 0 )
				{
					$totalPotentialDials = $availableLeads;
				}
			}
			
			//calculate customer priority
			if( $customerSkill->contract->fulfillment_type == 1 )
			{
				$pace = (($totalLeads / $numberOfWorkingDays) * $dialingDaysInBillingCycle);
				
				$pace = round($pace);
				
				$dialsNeeded = $pace - $appointmentSetCount;
				
				if( $dialsNeeded > 0 && $pace > 0 ) 
				{
					$priority = 1-($appointmentSetCount / $pace);
					
					$priority = number_format(round($priority, 1), 2);
				}
			}
			else
			{							
				$pace = ((($totalLeads * $maxDials) / $numberOfWorkingDays) * $dialingDaysInBillingCycle);
				
				$pace = round($pace);
				
				$dialsNeeded = (((($totalLeads * $maxDials) / $numberOfWorkingDays) * $dialingDaysInBillingCycle) - $callableLeads['called_leads']);
		
				$priority = ( (((($totalLeads * $maxDials) / $numberOfWorkingDays) * $dialingDaysInBillingCycle) - $callableLeads['called_leads']) / ((($totalLeads * $maxDials) / $numberOfWorkingDays) * $dialingDaysInBillingCycle) );
			
				$priority = number_format(round($priority, 1), 2);
			}
		}

		$leadRecycleCount = Lead::model()->count(array(
			'with' => array('list', 'list.skill'),
			'together' => true,
			'condition' => '
				t.customer_id = :customer_id 
				AND list.status = 1 
				AND t.type = 1 
				AND t.is_do_not_call = 0
				AND recycle_lead_call_history_id IS NOT NULL
				AND is_recycle_removed = 0
				AND (
					recycle_date IS NULL
					OR recycle_date = "0000-00-00"
					OR NOW() >= recycle_date 
				)
				AND ( 
					t.status = 3
					OR t.number_of_dials >= (skill.max_dials * 3)
				)
				AND skill.id = :skill_id
			',
			'params' => array(
				':customer_id' => $customerSkill->customer_id,
				':skill_id' => $customerSkill->skill_id,
			),
		));
		
		$leadRecertifyCount = Lead::model()->count(array(
			'with' => array('list', 'list.skill'),
			'together' => true,
			// 'condition' => 'list.customer_id = :customer_id AND (recertify_date = "0000-00-00" || recertify_date IS NULL) AND NOW() >= recertify_date',
			'condition' => '
				list.customer_id = :customer_id 
				AND list.status = 1 
				AND t.type = 1
				AND t.status = 1
				AND (recertify_date = "0000-00-00" || recertify_date IS NULL || NOW() >= recertify_date)
				AND skill.id = :skill_id
			',
			'params' => array(
				':customer_id' => $customerSkill->customer_id,
				':skill_id' => $customerSkill->skill_id,
			),
		));
		
		$namesWaiting = Lead::model()->count(array(
			'condition' => 'customer_id = :customer_id AND t.list_id IS NULL AND t.type=1 AND t.status=1',
			'params' => array(
				':customer_id' => $customerSkill->customer_id,
			),
		));
		
		
		$enrollmentHistory = CustomerHistory::model()->find(array(
			'select' => 'date_created',
			'condition' => 'customer_id = :customer_id AND (content LIKE "%Registered on%" OR content LIKE "%Start Date Changed from%")',
			'params' => array(
				':customer_id' => $customerSkill->customer_id,
			),
			'order' => 'date_created DESC',
		));
		
		if( $enrollmentHistory )
		{
			$enrollmentDate = $enrollmentHistory->date_created;
		}
		else
		{
			$enrollmentDate = $customer->account->date_created;
		}
		
		
		$endDateHistory = CustomerHistory::model()->find(array(
			'select' => 'user_account_id, date_created',
			'condition' => 'customer_id = :customer_id AND content LIKE "%End Date Changed from%"',
			'params' => array(
				':customer_id' => $customerSkill->customer_id,
			),
			'order' => 'date_created DESC',
		));
		
		if( $endDateHistory )
		{
			echo '<br><br>';
			
			echo 'End Date History: ';
			
			echo '<br>';
			
			echo '<pre>';
				print_r($endDateHistory->attributes);
			echo '</pre>';
			
			echo '<br><br>';
			
			$historyEndDateChanged = $endDateHistory->date_created;
			
			if( isset($endDateHistory->account->accountUser)  )
			{
				$accountUser = $endDateHistory->account->accountUser;
				
				$historyEndDateChanger = $accountUser->first_name.' '.$accountUser->last_name;
			}
		}
		
		$addedCreditHistory = CustomerHistory::model()->find(array(
			'select' => 'model_id, user_account_id, date_created',
			'condition' => 'customer_id = :customer_id AND page_name = :page_name AND type = :type',
			'params' => array(
				':customer_id' => $customerSkill->customer_id,
				':page_name' => 'Credit',
				':type' => CustomerHistory::TYPE_ADDED,
			),
			'order' => 'date_created DESC',
		));
		
		if( $addedCreditHistory )
		{
			echo '<br><br>';
			
			echo 'Credit History: ';
			
			echo '<br>';
			
			echo '<pre>';
				print_r($addedCreditHistory->attributes);
			echo '</pre>';
			
			echo '<br><br>';
			
			$historyCreditAddedDate = $addedCreditHistory->date_created;
			
			if( isset($addedCreditHistory->account->accountUser)  )
			{
				$accountUser = $addedCreditHistory->account->accountUser;
				$historyCreditChanger = $accountUser->first_name.' '.$accountUser->last_name;
			}
			
			$creditModel = CustomerCredit::model()->find(array(
				'select' => 'amount',
				'condition' => 'id = :id',
				'params' => array(
					':id' => $addedCreditHistory->model_id,
				),
			));
			
			if( $creditModel )
			{
				$historyCreditAmount = $creditModel->amount;
			}
		}
		
		// echo '<br><br>end...';
		// exit;
	
		### Queue Booster Module
		$cqviewerBoost = CustomerQueueViewerBoost::model()->find(array(
			'condition' => '
				customer_id = :customer_id
				AND skill_id = :skill_id
				AND is_boost_triggered = 1
				AND status = 1
			',
			'params' => array(
				':customer_id' => $customerSkill->customer_id,
				':skill_id' => $customerSkill->skill_id,
			),
		));
		
		if( $cqviewerBoost )
		{
			if( $cqviewerBoost->goal_value > 0 )
			{
				$priority = 10;
				$cqviewerBoost->goal_value = $totalPotentialDials;
				$cqviewerBoost->save(false);
			}
			
			if( $cqviewerBoost->dial_value > 0 )
			{
				$priority = 10;
				$cqviewerBoost->dial_value = $totalPotentialDials;
				$cqviewerBoost->save(false);
			}
			
			if( $cqviewerBoost->goal_value == 0 && $cqviewerBoost->dial_value == 0 )
			{
				$cqviewerBoost->status = 2;
				$cqviewerBoost->save(false);
			}
		}
		
		if( !empty($existingQueueViewer) )
		{
			$model = $existingQueueViewer;
		}
		else
		{
			$model = new CustomerQueueViewer;
		}
		
		$status = 1;
		
		if( !$customerIsCallable )
		{
			$status = 2;
		}
		
		if( in_array($nextAvailableCallingTime, array("On Hold", "Cancelled", "Removed", "Goal Appointment Reached", "Decline Hold")) )
		{
			$status = 2;
		}
		
		if( $callableLeads['total_count'] == 0 )
		{
			$status = 2;
		}
		
		//temporarily disabled customers from florida because of hurricane
		// $temporaryDisabledCustomers = array(1059, 880, 848, 691, 598, 1255, 970, 589, 408, 554, 960, 1276, 505, 411, 430, 448, 762, 1215, 899, 461, 652, 925, 445, 1046);
		
		// if( in_array($customerSkill->customer_id, $temporaryDisabledCustomers) )
		// {
			// $nextAvailableCallingTime = 'Next Shift';
		// }
		
		$notCompletedLeadsInQueue = LeadHopper::model()->count(array(
			'condition' => 'customer_id = :customer_id AND type IN (2,3)',
			'params' => array(
				':customer_id' => $customerSkill->customer_id,
			),
		));
		
		if( $priority < 0 )
		{
			$priority = 0;
		}
		
		if( isset($customerSkill->skill) && $customerSkill->skill->max_agent_per_customer > 1 )
		{
			$dialsUntilReset = 1000;
		}
		else
		{
			$dialsUntilReset = 20;
		}

		$model->setAttributes(array(
			'customer_id' => $customerSkill->customer_id,
			'contract_id' => $customerSkill->contract_id,
			'skill_id' => $customerSkill->skill_id,
			'customer_name' => $customer->firstname.' '.$customer->lastname,
			'customer_first_name' => $customer->firstname,
			'customer_last_name' => $customer->lastname,
			'custom_customer_id' => $customer->custom_customer_id,
			'skill_name' => $customerSkill->skill->skill_name,
			'contract_name' => $contract->contract_name,
			'priority_reset_date' => date('m-1-Y', strtotime('+1 month')),
			'priority' => $priority,
			'pace' => $pace,
			'current_dials' => $callableLeads['current_dials'],
			'current_goals' => $appointmentSetCount,
			'total_leads' => $callableLeads['total_count'],
			'available_leads' => $availableLeads,
			'not_completed_leads' => $notCompletedLeads,
			'total_potential_dials' => $totalPotentialDials,
			'next_available_calling_time' => $nextAvailableCallingTime, //get next available calling time
			'available_calling_blocks' => $availableCallingBlocks,
			'call_agent' => $callAgent,
			'max_dials' => $maxDials,
			'dials_needed' => $dialsNeeded,
			'fulfillment_type' => $customerSkill->contract->fulfillment_type == 1 ? 'Goal' : 'Lead',
			'dials_until_reset' => $dialsUntilReset,
			'status' => $status,
			'company' => $customerSkill->customer->company->company_name,
			'phone_number' => $customerSkill->customer->phone,
			'email_address' => $customerSkill->customer->email_address,
			'start_date' => $customerSkill->start_month,
			'end_date' => $customerSkill->end_month,
			'recertifiable_leads' => $leadRecertifyCount,
			'recyclable_leads' => $leadRecycleCount,
			'contracted_quantity' => $totalLeads,
			'contracted_amount' => $contractedAmount,
			'names_waiting' => $namesWaiting,
			'no_show' => $noShowCount,
			'account_date_created' => $customer->account->date_created,
			'enrollment_date' => $enrollmentDate,
			'history_end_date_changed' => $historyEndDateChanged,
			'history_end_date_changer' => $historyEndDateChanger,
			'history_credit_added_date' => $historyCreditAddedDate,
			'history_credit_changer' => $historyCreditChanger,
			'history_credit_amount' => $historyCreditAmount,
		));
		
		$model->save(false);
		
		echo '<pre>';
			print_r($model->attributes);
		echo '</pre>';
		
		return true;
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