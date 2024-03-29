<?php 

ini_set('memory_limit', '1000M');
set_time_limit(0);

class CustomerExtraController extends Controller
{
	
	public function actionCreate()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		if( isset($_POST['ajax']) && isset($_POST['CustomerExtra']) )
		{
			$model = new CustomerExtra;
			
			$model->attributes = $_POST['CustomerExtra'];
			
			if( $model->validate() )
			{
				if( $model->save() )
				{
					$history = new CustomerHistory;
				
					$history->setAttributes(array(
						'model_id' => $model->id, 
						'customer_id' => $model->customer_id,
						'user_account_id' => Yii::app()->user->account->id,
						'page_name' => 'Customer Extra',
						'content' => $model->description.' | '.$model->quantity.' | '.$model->year.' | '.date('F', mktime(0, 0, 0, $model->month, 10)), 
						'type' => $history::TYPE_ADDED,
					));

					$history->save(false);
					
					if( $model->year == date('Y') && $model->month == date('m') )
					{
						//force customer to re evaluation
						$customerSkill = CustomerSkill::model()->find(array(
							'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id AND status=1',
							'params' => array(
								':customer_id' => $model->customer_id,
								':contract_id' => $model->contract_id,
								':skill_id' => $model->skill_id,
							),
						));
						
						if( $customerSkill )
						{
							$existingQueueViewer = CustomerQueueViewer::model()->find(array(
								'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id',
								'params' => array(
									':customer_id' => $model->customer_id,
									':contract_id' => $model->contract_id,
									':skill_id' => $model->skill_id,
								),
							));
							
							$this->runCustomerEvaluation($customerSkill, $existingQueueViewer);
						}
					}
					
					$result['status'] = 'success';
					$result['message'] = 'Extra was successfully added.';
					
					$result['skill_id'] = $model->skill_id;
					$result['contract_id'] = $model->contract_id;
					$result['customer_id'] = $model->customer_id;
				}
			}
			else
			{
				$message = '';
				
				foreach( $model->getErrors() as $error )
				{
					$message .= $error[0] . "\r\n \r\n";
				}
				
				$result['message'] = $message;
			}
		}
		
		echo json_encode($result);
	}
	
	public function actionUpdate()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => '',
		);
		
		if( isset($_POST['ajax']) && isset($_POST['id']) )
		{
			$model = CustomerExtra::model()->findByPk($_POST['id']);
			
			if( isset($_POST['CustomerExtra']) )
			{
				$model->attributes = $_POST['CustomerExtra'];
				
				if( $model->validate() )
				{
					if( $model->save() )
					{
						$history = new CustomerHistory;
					
						$history->setAttributes(array(
							'model_id' => $model->id, 
							'customer_id' => $model->customer_id,
							'user_account_id' => Yii::app()->user->account->id,
							'page_name' => 'Customer Extra',
							'content' => $model->description.' | '.$model->quantity.' | '.$model->year.' | '.date('F', mktime(0, 0, 0, $model->month, 10)), 
							'type' => $history::TYPE_UPDATED,
						));

						$history->save(false);
						
						if( $model->year == date('Y') && $model->month == date('m') )
						{
							//force customer to re evaluation
							$customerSkill = CustomerSkill::model()->find(array(
								'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id AND status=1',
								'params' => array(
									':customer_id' => $model->customer_id,
									':contract_id' => $model->contract_id,
									':skill_id' => $model->skill_id,
								),
							));
							
							if( $customerSkill )
							{
								$existingQueueViewer = CustomerQueueViewer::model()->find(array(
									'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id',
									'params' => array(
										':customer_id' => $model->customer_id,
										':contract_id' => $model->contract_id,
										':skill_id' => $model->skill_id,
									),
								));
								
								$this->runCustomerEvaluation($customerSkill, $existingQueueViewer);
							}
						}
						
						$result['status'] = 'success';
						$result['message'] = 'Extra was successfully updated.';
						
						$result['skill_id'] = $model->skill_id;
						$result['contract_id'] = $model->contract_id;
						$result['customer_id'] = $model->customer_id;
					}
				}
				else
				{
					$message = '';
					
					foreach( $model->getErrors() as $error )
					{
						$message .= $error[0] . "\r\n \r\n";
					}
					
					$result['message'] = $message;
				}
				
				echo json_encode($result);
				Yii::app()->end();
			}
			
			if( $model )
			{
				$html = $this->renderPartial('ajaxEditExtra', array(
					'model' => $model,
				), true);
				
				$result['html'] = $html;
			}
			else
			{
				$result['message'] = 'Record not found.';
			}
		}
		
		echo json_encode($result);
	}
	
	public function actionDelete()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		if( isset($_POST['ajax']) && isset($_POST['id']) )
		{
			$model = CustomerExtra::model()->findByPk($_POST['id']);
			
			if( $model )
			{
				$model->status = 3;
				
				if( $model->save(false) )
				{
					$history = new CustomerHistory;
				
					$history->setAttributes(array(
						'model_id' => $model->id, 
						'customer_id' => $model->customer_id,
						'user_account_id' => Yii::app()->user->account->id,
						'page_name' => 'Customer Extra',
						'content' => $model->description.' | '.$model->quantity.' | '.$model->year.' | '.date('F', mktime(0, 0, 0, $model->month, 10)), 
						'type' => $history::TYPE_DELETED,
					));

					$history->save(false);
					
					$result['status'] = 'success';
					$result['message'] = 'Extra was deleted successfully.';
					
					$result['skill_id'] = $model->skill_id;
					$result['contract_id'] = $model->contract_id;
					$result['customer_id'] = $model->customer_id;
				}
			}
		}
		
		echo json_encode($result);
	}
	
	
	public function actionUpdateExtrasTable()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => '',
		);
		
		$customerExras = CustomerExtra::model()->findAll(array(
			'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id AND status=1',
			'params' => array(
				':customer_id' => $_POST['customer_id'],
				':contract_id' => $_POST['contract_id'],
				':skill_id' => $_POST['skill_id'],
			),
		));
		
		$html = $this->renderPartial('ajaxExtrasTable', array(
			'customerExras' => $customerExras,
		), true);
		
		$result['html'] = $html;
		
		echo json_encode($result);
	}
	
	
	private function getWorkingDaysForThisMonth($startDate, $endDate, $returnType='array')
	{
		date_default_timezone_set('America/Denver');
		
		$workdays = array();
		
		$holidays = array(
			strtotime(date('Y-01-01')), // New Year's Day
			//strtotime(date('Y-01-18')), // Birthday of Martin Luther King, Jr.
			strtotime(date('Y-02-15')), // Washington�s Birthday
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
					if( $type == 'customer' )
					{
						$nextAvailableCallingTimeStart = new DateTime($sched['schedule_start'], new DateTimeZone(timezone_name_from_abbr($timeZone)) );
						$nextAvailableCallingTimeEnd = new DateTime($sched['schedule_end'], new DateTimeZone(timezone_name_from_abbr($timeZone)) );
						
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
		}
		
	
		if( count($leads) > 0 && $totalLeads > 0 )
		{
			foreach( $leads as $lead )
			{
				$leadDialCount = 0;

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
					
					if( $latestCall )
					{
						if( $latestCall->is_skill_child == 0 )
						{
							if( !empty($latestCall->skillDisposition->retry_interval) )
							{
								if( isset($latestCall->skillDisposition) && time() >= (strtotime($latestCall->end_call_time) + $latestCall->skillDisposition->retry_interval) )
								{
									$leadIsCallable = true;
								} 
							}
							else
							{
								$leadIsCallable = true;
							}
						}
						else
						{
							if( !empty($latestCall->skillChildDisposition->retry_interval) )
							{
								if( isset($latestCall->skillChildDisposition) && time() >= (strtotime($latestCall->end_call_time) + $latestCall->skillChildDisposition->retry_interval) )
								{
									$leadIsCallable = true;
								} 
							}
							else
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