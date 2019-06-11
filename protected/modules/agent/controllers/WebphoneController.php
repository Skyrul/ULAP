<?php 

ini_set('memory_limit', '4000M');
set_time_limit(0);

Class WebphoneController extends Controller
{
	public $layout='//layouts/agent_dialer';
	
	public function actionIndex($action='')
	{
		$result = array(
			'status' => '',
			'message' => '',
			'html' => '',
			'getFlashesHtml' => '',
		);
		
		$authAccount = Yii::app()->user->account;

		$readyForCalling = true;
		$hasDialerAccess = false;
		
		if( $authAccount->account_type_id == Account::TYPE_HOSTDIAL_AGENT )
		{
			$hasDialerAccess = true;
		}
		
		if( $hasDialerAccess ) 
		{
			if( isset($authAccount->accountUser) && empty($authAccount->accountUser->phone_extension) )
			{
				$readyForCalling = false;
				
				Yii::app()->user->setFlash('danger', 'Phone extension is not yet set.');
			}
			
			if( isset($authAccount->customerOfficeStaff) && empty($authAccount->customerOfficeStaff->sip_username) )
			{
				$readyForCalling = false;
				Yii::app()->user->setFlash('danger', 'Webphone extension is not yet set.');
			}
		}
		else
		{
			Yii::app()->user->setFlash('danger', 'Your security group has no permission to access the webphone page.');
			$this->redirect(array('/site/logout'));
			exit;
		}

		
		//get sip login info
		if( isset($authAccount->customerOfficeStaff) )
		{
			$sipUsername = $authAccount->customerOfficeStaff->sip_username;
			$sipPassword = $authAccount->customerOfficeStaff->sip_password;
			$sipServer = '107.182.238.147';
			
			$callerID = $sipUsername;
		}
		
		if( isset($authAccount->accountUser) )
		{
			$sipUsername = $authAccount->accountUser->phone_extension;
			$sipPassword = 'Enagagex123';
			$sipServer = '64.251.13.2';
		}
			
			
		//agent assignment
		$assignedSkillIds = array();
		$assignedSkillChildIds = array();
		$assignedLanguageIds = array();
		$assignedCustomerIds = array();

		$assignedSkills = AccountSkillAssigned::model()->findAll(array(
			'condition' => 'account_id = :account_id',
			'params' => array(
				':account_id' => $authAccount->id,
			),
		));
		
		$assignedSkillChilds = AccountSkillChildAssigned::model()->findAll(array(
			'condition' => 'account_id = :account_id',
			'params' => array(
				':account_id' => $authAccount->id,
			),
		));
		
		$assignedLanguages = AccountLanguageAssigned::model()->findAll(array(
			'condition' => 'account_id = :account_id',
			'params' => array(
				':account_id' => $authAccount->id,
			),
		));

		
		if( $assignedSkills )
		{
			foreach( $assignedSkills as $assignedSkill )
			{
				$assignedSkillIds[] = $assignedSkill->skill_id;
			}
		}
		
		if( $assignedSkillChilds )
		{
			foreach( $assignedSkillChilds as $assignedSkillChild )
			{
				$assignedSkillChildIds[] = $assignedSkillChild->skill_child_id;
			}
		}
		
		if( $assignedLanguages )
		{
			$languageLookup = array(
				1 => 'English',
				2 => 'Spanish',
				3 => 'Mandarin',
				4 => 'French',
				5 => 'Korean',
			);
			
			foreach( $assignedLanguages as $assignedLanguage )
			{
				$assignedLanguageIds[] = $languageLookup[$assignedLanguage->language_id];
			}
		}

		$leadHopperEntry = null;
		
		$assignedCustomers = AccountCustomerAssigned::model()->findAll(array(
			'condition' => 'account_id = :account_id',
			'params' => array(
				':account_id' => $authAccount->id,
			),
		));
		
		if( $assignedCustomers )
		{
			foreach( $assignedCustomers as $assignedCustomer )
			{
				$assignedCustomerIds[] = $assignedCustomer->customer_id;
			}
		}
		
		if( empty($assignedCustomerIds) )
		{
			$this->render('_error_agent_no_customer');
			Yii::app()->end();
		}

		
		if( !empty($assignedSkillIds) || !empty($assignedSkillChildIds) )
		{
			if(empty($assignedSkillIds))
				$assignedSkillIds = array(0);
			
			if(empty($assignedSkillChildIds))
				$assignedSkillChildIds = array(0);
			
			$lead = null;
			$leadHistoryDataProvider = null;
			$list = null;
			$calendar = null;
			$customer = null;
			$office = null;
			$leadCallHistoryId = null;
			$officeOptions = array();
			$calendarOptions = array();
			$dispositionOptions = array();
			$dispositionHtmlOptions = array('options'=>array());
			$xfrs = array();

			$customerInHopperCount = LeadHopper::model()->count(array(
				'group' => 'customer_id',
			));
			
			if( isset($_REQUEST['ajax']) && isset($_REQUEST['action']) && $_REQUEST['action'] == 'nextLead' && $readyForCalling )
			{
				//force skip lead 
				if( isset($_REQUEST['skipCall']) && isset($_REQUEST['lead_id']) )
				{
					$existingleadHopperEntry = LeadHopper::model()->find(array(
						'condition' => 'lead_id = :lead_id AND type !=3',
						'params' => array(
							':lead_id' => $_REQUEST['lead_id'],
						),
					));
					
					if( $existingleadHopperEntry )
					{
						if( $existingleadHopperEntry->delete() )
						{
							$dialerSkipLeadTracker = new DialerSkipLeadTracker;
							
							$dialerSkipLeadTracker->setAttributes(array(
								'agent_account_id' => $authAccount->id,
								'lead_id' => $_REQUEST['lead_id'],
							));
							
							$dialerSkipLeadTracker->save(false);
						}
					}
				}
				

				if( isset($_REQUEST['lead_hopper_id']) || isset($_REQUEST['search_lead_id']))
				{
					
					if(!isset($_REQUEST['ajax']))
					{
						//end record for lead wrap time
						$this->recordWrapTime(null, $authAccount->id, 'endOnly');
					}
				}
				
				//start getting a lead from the hopper
				if( isset($_REQUEST['current_lead_id']) )
				{
					//used to maintain the lead history of the current lead
					$existingleadHopperEntry = LeadHopper::model()->find(array(
						'condition' => 'lead_id = :lead_id',
						'params' => array(
							':lead_id' => $_REQUEST['current_lead_id'],
						),
					));
				}
				else
				{
					$callHistoryNoDispo = LeadCallHistory::model()->find(array(
						'condition' => 'date_created > "2016-03-18 22:06:11" AND disposition_id IS NULL AND skill_child_disposition_id IS NULL AND agent_account_id = :agent_account_id AND status=1 AND date_updated > date_created',
						'params' => array(
							':agent_account_id' => $authAccount->id,
						),
						'order' => 'date_created DESC',
					));

					
					//if there is a lead call histo
					if( $callHistoryNoDispo )
					{
						Yii::app()->user->setFlash('danger', 'Lead has no disposition for the last call.');
						
						$leadCallHistoryId = $callHistoryNoDispo->id;
						
						$existingleadHopperEntry = LeadHopper::model()->find(array(
							'condition' => 'lead_id = :lead_id',
							'params' => array(
								':lead_id' => $callHistoryNoDispo->lead_id,
							),
						));
						
						if( empty($existingleadHopperEntry) )
						{
							$lead = $callHistoryNoDispo->lead;
							$list = $lead->list;
							$customer = $callHistoryNoDispo->customer;
							
							if( !empty($lead->timezone) )
							{
								$timeZone = $lead->timezone;
							}
							else
							{
								$timeZone = AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $customer->phone) );
							}
							
							$existingleadHopperEntry = new LeadHopper;
							
							$existingleadHopperEntry->setAttributes(array(
								'lead_id' => $lead->id,
								'list_id' => $lead->list_id,
								'skill_id' => $list->skill_id,
								'customer_id' => $customer->id,
								'lead_language' => $lead->language,
								'lead_timezone' => $timeZone,
								'agent_account_id' => $authAccount->id,
							));
							
							$skillChildConfirmation = SkillChild::model()->find(array(
								'condition' => 'skill_id = :skill_id AND type = :type',
								'params' => array(
									':skill_id' => $list->skill_id,
									':type' => SkillChild::TYPE_CONFIRM,
								),
							));
							
							if($skillChildConfirmation !== null)
							{
								$existingleadHopperEntry->skill_child_confirmation_id = $skillChildConfirmation->id;
							}
							
							$skillChildReschedule = SkillChild::model()->find(array(
								'condition' => 'skill_id = :skill_id AND type = :type',
								'params' => array(
									':skill_id' => $list->skill_id,
									':type' => SkillChild::TYPE_RESCHEDULE,
								),
							));
							
							if($skillChildReschedule !== null)
							{
								$existingleadHopperEntry->skill_child_reschedule_id = $skillChildReschedule->id;
							}
							
							$existingleadHopperEntry->save(false);
							
							
						}
					}
					else
					{
						// if( $authAccount->account_type_id == Account::TYPE_HOSTDIAL_AGENT )
						if( $assignedCustomerIds )
						{
							//prioritize leads that is in Callback 
							if(empty($existingleadHopperEntry))
							{
								$existingleadHopperEntry = LeadHopper::model()->find(array(
									'condition' => '
										t.agent_account_id = :agent_account_id 
										AND t.type != :lead_search 
										AND (t.status IN ("READY","INCALL")) 
										AND ( (t.type=2 AND t.callback_date IS NOT NULL AND NOW() >= t.callback_date) )
										AND (t.skill_id IN ('.implode(', ', $assignedSkillIds).'))
										AND queueViewer.dials_until_reset > 0
										AND queueViewer.next_available_calling_time = "Now"
										AND (t.lead_language IN ("'.implode('","', $assignedLanguageIds).'"))
										AND (t.customer_id IN ("'.implode('","', $assignedCustomerIds).'"))
									',
									'params' => array(
										':agent_account_id' => $authAccount->id,
										':lead_search' => LeadHopper::TYPE_LEAD_SEARCH,
									),
									'with'=>array('customer','customer.queueViewer'),
									'order' => 'queueViewer.priority DESC',
								));	
							}
							
							if(empty($existingleadHopperEntry))
							{
								$existingleadHopperEntry = LeadHopper::model()->find(array(
									'condition' => '
										t.agent_account_id = :agent_account_id 
										AND t.type != :lead_search 
										AND (t.status IN ("READY", "INCALL", "HOLD", "DISPO")) AND t.type=1
										AND (t.skill_id IN ('.implode(', ', $assignedSkillIds).'))
										AND queueViewer.dials_until_reset > 0
										AND queueViewer.next_available_calling_time = "Now"
										AND (t.lead_language IN ("'.implode('","', $assignedLanguageIds).'"))
										AND (t.customer_id IN ("'.implode('","', $assignedCustomerIds).'"))
									',
									'params' => array(
										':agent_account_id' => $authAccount->id,
										':lead_search' => LeadHopper::TYPE_LEAD_SEARCH,
									),
									'with'=>array('customer','customer.queueViewer'),
									'order' => 'queueViewer.priority DESC',
								));	
							}
						}
						else
						{
							//prioritize leads that is in Callback
							if(empty($existingleadHopperEntry))
							{
								$existingleadHopperEntry = LeadHopper::model()->find(array(
									'condition' => '
										t.agent_account_id = :agent_account_id 
										AND t.type != :lead_search 
										AND (t.status IN ("READY","INCALL")) 
										AND ( (t.type=2 AND t.callback_date IS NOT NULL AND NOW() >= t.callback_date) )
										AND (t.skill_id IN ('.implode(', ', $assignedSkillIds).'))
										AND queueViewer.dials_until_reset > 0
										AND queueViewer.next_available_calling_time = "Now"
										AND (t.lead_language IN ("'.implode('","', $assignedLanguageIds).'"))
									',
									'params' => array(
										':agent_account_id' => $authAccount->id,
										':lead_search' => LeadHopper::TYPE_LEAD_SEARCH,
									),
									'with'=>array('customer','customer.queueViewer'),
									'order' => 'queueViewer.priority DESC',
								));	
							}
							
							if(empty($existingleadHopperEntry))
							{
								$existingleadHopperEntry = LeadHopper::model()->find(array(
									'condition' => '
										t.agent_account_id = :agent_account_id 
										AND t.type != :lead_search 
										AND (t.status IN ("READY", "INCALL", "HOLD", "DISPO")) AND t.type=1
										AND (t.skill_id IN ('.implode(', ', $assignedSkillIds).'))
										AND queueViewer.dials_until_reset > 0
										AND queueViewer.next_available_calling_time = "Now"
										AND (t.lead_language IN ("'.implode('","', $assignedLanguageIds).'"))
									',
									'params' => array(
										':agent_account_id' => $authAccount->id,
										':lead_search' => LeadHopper::TYPE_LEAD_SEARCH,
									),
									'with'=>array('customer','customer.queueViewer'),
									'order' => 'queueViewer.priority DESC',
								));	
							}
						}
					}
				}
				
				if( $existingleadHopperEntry )
				{
					if( isset($_REQUEST['lead_hopper_id']) && !isset($_REQUEST['ajax']) )
					{
						if(empty($leadHopperEntry))
						{
							//prioritize leads that is in Callback 
							$leadHopperEntry = LeadHopper::model()->find(array(
								'condition' => '
									t.id != :current_lead_hopper_id 
									AND t.customer_id = :customer_id 
									AND agent_account_id = :agent_account_id 
									AND (t.status IN ("READY","INCALL") )
									AND ( (t.type=2 AND t.callback_date IS NOT NULL AND NOW() >= t.callback_date) )
									AND (t.skill_id IN ('.implode(', ', $assignedSkillIds).'))
									AND queueViewer.dials_until_reset > 0
									AND queueViewer.next_available_calling_time = "Now"
									AND (t.lead_language IN ("'.implode('","', $assignedLanguageIds).'"))
								',
								'params' => array(
									':current_lead_hopper_id' => $_REQUEST['lead_hopper_id'],
									':customer_id' => $existingleadHopperEntry->customer_id,
									':agent_account_id' => $authAccount->id,
								),
								'with'=>array('customer','customer.queueViewer'),
								'order' => 'queueViewer.priority DESC',
							));
						}
						
						if(empty($leadHopperEntry))
						{
							$leadHopperEntry = LeadHopper::model()->find(array(
								'condition' => '
									t.id != :current_lead_hopper_id 
									AND t.customer_id = :customer_id 
									AND agent_account_id = :agent_account_id 
									AND (t.status IN ("READY", "INCALL", "HOLD", "DISPO")) AND t.type=1
									AND (t.skill_id IN ('.implode(', ', $assignedSkillIds).'))
									AND queueViewer.dials_until_reset > 0
									AND queueViewer.next_available_calling_time = "Now"
									AND (t.lead_language IN ("'.implode('","', $assignedLanguageIds).'"))
								',
								'params' => array(
									':current_lead_hopper_id' => $_REQUEST['lead_hopper_id'],
									':customer_id' => $existingleadHopperEntry->customer_id,
									':agent_account_id' => $authAccount->id,
								),
								'with'=>array('customer','customer.queueViewer'),
								'order' => 'queueViewer.priority DESC',
							));
						}
					}
					else
					{
						$leadHopperEntry = $existingleadHopperEntry;
					}
				}
				
				if( isset($_REQUEST['search_lead_id']) && !isset($_REQUEST['ajax']))
				{
					$leadHopperEntry = LeadHopper::model()->find(array(
						'condition' => 'id = :id',
						'params' => array(
							':id' => $_REQUEST['search_lead_id'],
						),
						'order' => 't.id ASC',
					));	
				}
				
				if($leadHopperEntry == null)
				{				
					$addedCondition .= ' AND agent_account_id IS NULL';
					
					if( $assignedCustomerIds )
					{
						if(empty($leadHopperEntry))
						{
							//prioritize leads that is in Callback 
							$leadHopperEntry = LeadHopper::model()->find(array(
								'condition' => '
									(t.status IN ("READY") )
									AND ( (t.type=2 AND t.callback_date IS NOT NULL AND NOW() >= t.callback_date) )
									AND (t.skill_id IN ('.implode(', ', $assignedSkillIds).'))
									AND queueViewer.dials_until_reset > 0
									AND queueViewer.next_available_calling_time = "Now"
									AND (t.lead_language IN ("'.implode('","', $assignedLanguageIds).'"))
									AND (t.customer_id IN ("'.implode('","', $assignedCustomerIds).'"))
								' . $addedCondition,
								'with'=>array('customer','customer.queueViewer'),
								'order' => 'queueViewer.priority DESC',
							));	
						}
						
						if(empty($leadHopperEntry))
						{
							$leadHopperEntry = LeadHopper::model()->find(array(
								'condition' => '(t.status IN ("READY", "HOLD", "DISPO")) AND t.type=1
									AND (t.skill_id IN ('.implode(', ', $assignedSkillIds).'))
									AND queueViewer.dials_until_reset > 0
									AND queueViewer.next_available_calling_time = "Now"
									AND (t.lead_language IN ("'.implode('","', $assignedLanguageIds).'"))
									AND (t.customer_id IN ("'.implode('","', $assignedCustomerIds).'"))
								' . $addedCondition,
								'with'=>array('customer','customer.queueViewer'),
								'order' => 'queueViewer.priority DESC',
							));	
						}
					}
					else
					{
						if(empty($leadHopperEntry))
						{
							//prioritize leads that is in Callback 
							$leadHopperEntry = LeadHopper::model()->find(array(
								'condition' => '
									(t.status IN ("READY") )
									AND ( (t.type=2 AND t.callback_date IS NOT NULL AND NOW() >= t.callback_date) )
									AND (t.skill_id IN ('.implode(', ', $assignedSkillIds).'))
									AND queueViewer.dials_until_reset > 0
									AND queueViewer.next_available_calling_time = "Now"
									AND (t.lead_language IN ("'.implode('","', $assignedLanguageIds).'"))
								' . $addedCondition,
								'with'=>array('customer','customer.queueViewer'),
								'order' => 'queueViewer.priority DESC',
							));	
						}
						
						if(empty($leadHopperEntry))
						{
							$leadHopperEntry = LeadHopper::model()->find(array(
								'condition' => '(t.status IN ("READY", "HOLD", "DISPO")) AND t.type=1
									AND (t.skill_id IN ('.implode(', ', $assignedSkillIds).'))
									AND queueViewer.dials_until_reset > 0
									AND queueViewer.next_available_calling_time = "Now"
									AND (t.lead_language IN ("'.implode('","', $assignedLanguageIds).'"))
								' . $addedCondition,
								'with'=>array('customer','customer.queueViewer'),
								'order' => 'queueViewer.priority DESC',
							));	
						}
					}
					
					if(empty($leadHopperEntry))
					{
						if( $assignedCustomerIds )
						{
							$leadHopperEntry = LeadHopper::model()->find(array(
								'condition' => '
									t.agent_account_id IS NULL
									AND t.status="READY" 
									AND t.type = 1
									AND t.skill_id IN ('.implode(', ', $assignedSkillIds).')
									AND t.lead_language IN ("'.implode('","', $assignedLanguageIds).'")
									AND (t.customer_id IN ("'.implode('","', $assignedCustomerIds).'"))
								',
							));	
						}
						else
						{						
							$leadHopperEntry = LeadHopper::model()->find(array(
								'condition' => '
									t.agent_account_id IS NULL
									AND t.status="READY" 
									AND t.type = 1
									AND t.skill_id IN ('.implode(', ', $assignedSkillIds).')
									AND t.lead_language IN ("'.implode('","', $assignedLanguageIds).'")
								',
							));	
						}
					}
				}
				
				// $leadHopperEntry = LeadHopper::model()->findByPk(227897);

				if( $leadHopperEntry )
				{
					$lead = $leadHopperEntry->lead;
					$leadId = $leadHopperEntry->lead_id;
					$customerId = $leadHopperEntry->customer_id;
					
					$leadHopperEntry->agent_account_id = $authAccount->id;
					
					if( $leadHopperEntry->status == LeadHopper::STATUS_READY )
					{
						$leadHopperEntry->status = LeadHopper::STATUS_INCALL;
					}
					
					//get customer skill transfer list
					$customerSkill = CustomerSkill::model()->find(array(
						'condition' => 'customer_id = :customer_id AND skill_id = :skill_id AND status=1',
						'params' => array(
							':customer_id' => $leadHopperEntry->customer_id,
							':skill_id' => $leadHopperEntry->skill_id,
						)
					));
					
					if( $customerSkill )
					{
						$xfrs = CustomerSkillXfrAddressBook::model()->findAll(array(
							'condition' => 'customer_skill_id = :customer_skill_id',
							'params' => array(
								':customer_skill_id' => $customerSkill->id
							)
						));
					}
					
					//get caller ID
					if( $lead->list->dialing_as_number == 1 )
					{
						$callerID = $customerSkill->customer->phone;
					}
					else
					{
						$hostManager = CustomerOfficeStaff::model()->findByPk($lead->list->dialing_as_number);
						
						if( $hostManager )
						{
							$callerID = preg_replace('/[^0-9]/', '', $hostManager->phone);
						}
					}
					
					if( $leadHopperEntry->save(false) )
					{
						if( $leadHopperEntry->type != LeadHopper::TYPE_LEAD_SEARCH && !isset($_REQUEST['ajax']) )
						{
							$currentAssignedToAgent = LeadHopper::model()->count(array(
								'condition' => 'agent_account_id = :agent_account_id',
								'params' => array(
									':agent_account_id' => $authAccount->id,
								),
							));
							
								
							//assign multiple agents to one customer
							if( isset($leadHopperEntry->list->skill) && $leadHopperEntry->list->skill->max_agent_per_customer > 1 )
							{
								$leadHopperEntry->agent_account_id = $authAccount->id;
								$leadHopperEntry->save(false);
							}
							else
							{
								if( $currentAssignedToAgent >= 1 )
								{
									if($leadHopperEntry->type == 1)
									{
										if( $customerInHopperCount >= 2 )
										{
											LeadHopper::model()->updateAll(array('agent_account_id' => $authAccount->id), 'type=1 AND customer_id = ' . $leadHopperEntry->customer_id);
										}
										else
										{
											// this code was added when there is leadhopper where it's call history has not yet set its disposition in the dialer
											LeadHopper::model()->updateAll(array('agent_account_id' => $authAccount->id), 'type = 1 AND lead_id = ' . $leadHopperEntry->lead_id . ' AND agent_account_id = ' . $authAccount->id);
										}
									}
								}
							}
						}
					}
				}
				
				
				//get lead details
				if( $lead != null )
				{
					if( !isset($_REQUEST['ajax']))
					{
						//create record for lead wrap time
						$this->recordWrapTime($lead->id, $authAccount->id);
					}
					
					//start getting lead data
					$leadHistories = LeadHistory::model()->findAll(array(
						'condition' => 'lead_id = :lead_id AND type NOT IN(9) AND status=1',
						'params' => array(
							':lead_id' => $lead->id,
						), 
						'order' => 'date_created DESC',
					));
				
					$leadHistoryDataProvider = new CArrayDataProvider($leadHistories);
					
					$list = Lists::model()->findByPk($leadHopperEntry->list_id);
					
					$calendar_id = isset($_REQUEST['calendar_id']) != null ? $_REQUEST['calendar_id'] : $list->calendar_id;
					
					$calendar = Calendar::model()->findByPk($calendar_id);
					
					$office_id = isset($_REQUEST['office_id']) != null ? $_REQUEST['office_id'] : $calendar->office_id;
					
					$office = CustomerOffice::model()->findByPk($office_id);	

					$customer = Customer::model()->findByPk($leadHopperEntry->customer_id);
					
					if( $office )
					{
						$calendars = Calendar::model()->findAll(array(
							'condition' => 'office_id = :office_id AND status=1',
							'params' => array(
								':office_id' => $office->id
							),
						));
						
						$calendarOptions = CHtml::listData( $calendars, 'id', 'name');
					}
					else
					{
						$calendarOptions = array();
					}
					
					$offices = CustomerOffice::model()->findAll(array(
						'condition' => 'customer_id = :customer_id AND status=1 AND is_deleted=0',
						'params' => array(
							':customer_id' => $customer->id
						),
					));

					$officeOptions = CHtml::listData( $offices, 'id', 'office_name');
														
					#change disposition list from Parent Skill to (Child Skill Disposition find TYPE_CONFIRM)
					#when... 
					# 1. LeadHopper = TYPE_CONFIRMATION_CALL;

					$dispositions = SkillDisposition::model()->findAll(array(
						'condition' => 'skill_id = :skill_id',
						'params' => array(
							':skill_id' => $list->skill_id,
						),
						'order' => 'skill_disposition_name ASC',
					));
					
					$dispositionOptions = CHtml::listData( $dispositions, 'id', 'skill_disposition_name');
				
					if( $dispositionOptions )
					{
						foreach( $dispositions as $disposition )
						{
							$dispositionHtmlOptions['options'][$disposition->id] = array(
								'is_appointment_set' => $disposition->is_appointment_set,
								'is_location_conflict' => $disposition->is_location_conflict,
								'is_schedule_conflict' => $disposition->is_schedule_conflict,
							);
						}
					}
					
					$showAppointmentTab = false;
					$showSurveyTab = false;
					$showScriptTab = false;
					$showDataTab = false;
					
					//check appointment tab settings
					if( isset($leadHopperEntry->skill) && $leadHopperEntry->skill->enable_dialer_appointment_tab == 1 )
					{
						$showAppointmentTab = true;
					}
					
					//check appointment tab settings
					if( isset($leadHopperEntry->skill) && $leadHopperEntry->skill->enable_survey_tab == 1 )
					{
						$showSurveyTab = true;
					}
	
					//check script tab settings
					if( $leadHopperEntry->type == LeadHopper::TYPE_CONFIRMATION_CALL )
					{
						if( $leadHopperEntry->confirmChildSkill->enable_dialer_script_tab == 1 )
						{
							$showScriptTab = true;
						}
					}
					elseif( $leadHopperEntry->type == LeadHopper::TYPE_RESCHEDULE )
					{
						if( $leadHopperEntry->rescheduleChildSkill->enable_dialer_script_tab == 1 )
						{
							$showScriptTab = true;
						}
					}
					else
					{
						if( $leadHopperEntry->skill->enable_dialer_script_tab == 1 )
						{
							$showScriptTab = true;
						}
					}
					
					//check data tab settings
					if( isset($leadHopperEntry->skill) && $leadHopperEntry->skill->enable_dialer_data_tab == 1 )
					{
						$showDataTab = true;
					}
					
					switch( $leadHopperEntry->type )
					{
						default: case LeadHopper::TYPE_CONTACT: 
							$callType = 'CONTACT CALL';
							$callTypeClass = 'label-success';
						break;
						
						case LeadHopper::TYPE_CALLBACK: 
							$callType = 'CALLBACK'; 
							$callTypeClass = 'label-danger';
						break;
						
						case LeadHopper::TYPE_CONFIRMATION_CALL: 
							$callType = 'CONFIRMATION CALL'; 
							$callTypeClass = 'label-warning';
						break;
						
						case LeadHopper::TYPE_LEAD_SEARCH: 
							$callType = 'LEAD SEARCH'; 
							$callTypeClass = 'label-info';
						break;
						
						case LeadHopper::TYPE_CONFLICT: 
							$callType = 'CONFLICT'; 
							$callTypeClass = 'label-danger';
						break;
						
						case LeadHopper::TYPE_RESCHEDULE: 
							$callType = 'RESCHEDULE'; 
							$callTypeClass = 'label-danger';
						break;
						
						case LeadHopper::TYPE_NO_SHOW_RESCHEDULE: 
							$callType = 'NO SHOW RESCHEDULE'; 
							$callTypeClass = 'label-danger';
						break;
					}
						
					
					/** MAIN TABS **/
					$result['html']['tabs'] = $this->renderPartial('_get_lead_tabs', array(
						'showAppointmentTab' => $showAppointmentTab,
						'showSurveyTab' => $showSurveyTab,
						'showScriptTab' => $showScriptTab,
						'showDataTab' => $showDataTab,
					), true);
					
					
					/** DIALER TAB **/
						
					//LEAD INFORMATION
						$result['html']['title'] = $this->renderPartial('_get_lead_lead_info_title', array(
							'callType' => $callType,
							'callTypeClass' => $callTypeClass,
							'list' => $list,
						), true);
					
						$result['html']['lead_info_fields'] = $this->renderPartial('_get_lead_lead_info_fields', array(
							'lead' => $lead,
						), true);

						$result['html']['lead_info_dialer_buttons'] = $this->renderPartial('_get_lead_dialer_buttons', array(
							'lead' => $lead,
						), true);			
						
						$result['html']['lead_info_lead_phone_numbers'] = $this->renderPartial('_get_lead_info_phone_numbers', array(
							'leadCallHistoryId' => $leadCallHistoryId,
							'lead' => $lead,
							'list' => $list,
							'customer' => $customer,
							'dispositionOptions' => $dispositionOptions,
							'dispositionHtmlOptions' => $dispositionHtmlOptions,
							'xfrs' => $xfrs,
						), true);
							
					//CUSTOMER INFORMATION
						$result['html']['customer_info_fields'] = $this->renderPartial('_get_lead_customer_info', array(
							'customer' => $customer,
							'office' => $office,
							'officeOptions' => $officeOptions,
						), true);
					
					//LEAD HISTORY
						$result['html']['lead_history'] = $this->renderPartial('_get_lead_history', array(
							'lead' => $lead,
							'leadHistoryDataProvider' => $leadHistoryDataProvider,
						), true);
						
						
					/** OTHER TAB CONTENT **/
					
						$tabContentsHtml = '';
						
						if( $showSurveyTab )
						{
							$tabContentsHtml .= '<div id="surveys" class="tab-pane fade"></div>';
						}

						$tabContentsHtml .= '<div id="googlemap" class="tab-pane fade in"></div>';

						if( $showScriptTab )
						{
							$tabContentsHtml .= '<div id="script" class="tab-pane fade in"></div>';	
						}

						if( $showDataTab )
						{
							$tabContentsHtml .= '<div id="data" class="tab-pane fade in"></div>';
						}
							
					$result['html']['tab_contents'] = $tabContentsHtml;
					
					$result['current_lead_id'] = $lead->id;
					$result['current_calendar_id'] = $calendar->id;
					$result['customer_id'] = $customer->id;
					$result['current_call_history_id'] = $leadCallHistoryId;
					
					$result['sip_username'] = $sipUsername;
					$result['sip_password'] = $sipPassword;
					$result['sip_server'] = $sipServer;
					$result['caller_id'] = $callerID;
					
					$result['status'] = 'success';	
				}
				else
				{
					Yii::app()->user->setFlash('danger', '<i class="fa fa-warning"></i> There are no more leads in the hopper.');
				}
				
				if( isset($_POST['ajax']) )
				{
					$ctr = 1;
					
					foreach( Yii::app()->user->getFlashes() as $key => $message ) 
					{
						$result['getFlashesHtml'] = '<div class="alert alert-'.$key.'"><button data-dismiss="alert" class="close" type="button"><i class="ace-icon fa fa-times"></i></button> ' . $message . "</div>\n";
					}
					
					echo json_encode($result);
					Yii::app()->end();
				}
			}

			// echo '<pre>';
			// print_r($leadHopperEntry->attributes);
			// exit;

			$enginePriorityWebrtc = 5;
			$enginePriorityNS = 0;
			
			//override for webphone dev page
			if( in_array( $authAccount->id, array(5345)) )  //HostDialer8
			{
				$enginePriorityWebrtc = 0;
				$enginePriorityNS = 5;
			}
			
			if( in_array( $authAccount->id, array(5340)) )  //HostDialer3
			{
				$enginePriorityWebrtc = 5;
				$enginePriorityNS = 0;
			}

			$this->render('index', array(
				'leadHopperEntry' => $leadHopperEntry,
				'lead' => $lead,
				'leadHistoryDataProvider' => $leadHistoryDataProvider,
				'list' => $list,
				'calendar' => $calendar,
				'customer' => $customer,
				'office' => $office,
				'officeOptions' => $officeOptions,
				'calendarOptions' => $calendarOptions,
				'dispositionOptions' => $dispositionOptions,
				'dispositionHtmlOptions' => $dispositionHtmlOptions,
				'leadCallHistoryId' => $leadCallHistoryId,
				'accountQueuePopup' => $accountQueuePopup,			
				'authAccount' => $authAccount,				
				'sipUsername' => $sipUsername,				
				'sipPassword' => $sipPassword,				
				'sipServer' => $sipServer,				
				'callerID' => $callerID,				
				'leadId' => $leadId,				
				'customerId' => $customerId,				
				'leadCallHistoryId' => $leadCallHistoryId,				
				'xfrs' => $xfrs,
				'enginePriorityWebrtc' => $enginePriorityWebrtc,						
				'enginePriorityNS' => $enginePriorityNS				
			));
		}
		else
		{
			$this->render('_error_agent_no_skill');
		}
	}
	
	public function actionDial()
	{
		$authAccount = Yii::app()->user->account;
		$accountUser = $authAccount->accountUser;
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'call_history_id' => '',
		);
		
		if( isset($_POST['ajax']) )
		{
			$dialNumber = 1;			
		
			$customerSkill = CustomerSkill::model()->find(array(
				'condition' => 'customer_id = :customer_id AND skill_id = :skill_id',
				'params' => array(
					':customer_id' => $_POST['customer_id'],
					':skill_id' => $_POST['skill_id'],
				),
			));

			$existingLeadCallHistory = LeadCallHistory::model()->find(array(
				'condition' => 'lead_id = :lead_id AND list_id = :list_id',
				'params' => array(
					':lead_id' => $_POST['lead_id'],
					':list_id' => $_POST['list_id'],
				),
				'order' => 'date_created DESC',
			));
			
			$lead = Lead::model()->findByPk($_POST['lead_id']);
			
			$leadCallHistory = new LeadCallHistory;
			
			if( $existingLeadCallHistory )
			{
				if( $existingLeadCallHistory->disposition_id == null && $existingLeadCallHistory->skill_child_disposition_id == null )
				{
					$leadCallHistory = $existingLeadCallHistory;
				}
				
				$dialNumber = $existingLeadCallHistory->dial_number + 1;
				$leadCallHistory->calendar_appointment_id = $existingLeadCallHistory->calendar_appointment_id;
			}
			
			$leadCallHistory->setAttributes(array(
				'lead_id' => $_POST['lead_id'], 
				'list_id' => $_POST['list_id'], 
				'customer_id' => $_POST['customer_id'], 
				'company_id' => $_POST['company_id'], 
				'contract_id' => $customerSkill->contract_id,
				'agent_account_id' => $authAccount->id, 
				'dial_number' => $dialNumber,
				'lead_phone_number' => preg_replace("/[^0-9]/","", $_POST['lead_phone_number']), 
				'start_call_time' => date('Y-m-d H:i:s'),
			));
			
			
			if( $leadCallHistory->save(false) )
			{
				//create record for lead wrap time
				$this->recordWrapTime($leadCallHistory->lead_id, $authAccount->id, $type = 'endOnly');
			
				$customerQueueViewer = CustomerQueueViewer::model()->find(array(
					'condition' => 'customer_id = :customer_id AND skill_id = :skill_id AND contract_id = :contract_id',
					'params' => array(
						':customer_id' => $leadCallHistory->customer_id,
						':skill_id' => $customerSkill->skill_id,
						':contract_id' => $leadCallHistory->contract_id,
					),
				));
				
				if( $customerQueueViewer && $customerQueueViewer->dials_until_reset > 0 )
				{
					$cqviewerBoost = CustomerQueueViewerBoost::model()->find(array(
						'condition' => '
							customer_id = :customer_id
							AND skill_id = :skill_id
							AND is_boost_triggered = 1
							AND status = 1
						',
						'params' => array(
							':customer_id' => $customerQueueViewer->customer_id,
							':skill_id' => $customerQueueViewer->skill_id,
						),
					));
					
					if( !in_array($customerQueueViewer->customer_id, array(1021, 63)) && empty($cqviewerBoost) )
					{
						$customerQueueViewer->dials_until_reset = $customerQueueViewer->dials_until_reset - 1;
						$customerQueueViewer->save(false);
					}
				}
				
				//update lead dial count and status based on max dials
				if( $lead )
				{
					$field = $_POST['phone_type'].'_phone_dial_count';
					
					$lead->$field = $lead->$field + 1;
					
					$lead->number_of_dials = $lead->number_of_dials + 1;
					
					//temporary for graton skills
					if( in_array($customerSkill->skill_id, array(36,37,38,39)) )
					{
						if( $lead->number_of_dials >= $customerSkill->skill->max_dials ) 
						{
							$lead->status = 3;
						}
					}
					else
					{
						if( $lead->number_of_dials >= ($customerSkill->skill->max_dials * 3 ) ) 
						{
							$lead->status = 3;
						}
					}
					
					$lead->save(false);
				}
				
				$result['status'] = 'success';
				$result['message'] = 'Database has been updated.';
				$result['call_history_id'] = $leadCallHistory->id;
			}
		}
		
		echo json_encode($result);
	}
		
	public function actionEndCall()
	{
		$authAccount = Yii::app()->user->account;
		
		$result = array(
			'status' => 'success',
			'message' => '',
		);
		
		if( isset($_POST['ajax']) && $_POST['call_history_id'] )
		{
			$leadCallHistory = LeadCallHistory::model()->findByPk($_POST['call_history_id']);
			$leadCallHistory->end_call_time = date('Y-m-d H:i:s');
			
			if( $leadCallHistory->save(false) )
			{
				//create record for lead wrap time
				$this->recordWrapTime($leadCallHistory->lead_id, $authAccount->id);
					
				//find active asterisk channel
				$existingChannel = AsteriskChannel::model()->find(array(
					'condition' => 'call_history_id = :call_history_id',
					'params' => array(
						':call_history_id' => $leadCallHistory->id,
					),
				));
				
				if( $existingChannel )
				{
					$existingChannel->status = 1;
					$existingChannel->save(false);
					
					$asterisk = new Asterisk;
					$asterisk->hangup($existingChannel->channel);
				}

				$result['status'] = 'success';
				$result['message'] = 'Database has been updated.';
			}
		}
		
		echo json_encode($result);
	}
		
	public function actionUpdateLeadHopper()
	{
		$result = array(
			'status' => 'success',
			'message' => '',
		);
		
		if( isset($_POST['ajax']) && $_POST['current_lead_id'] )
		{
			$hopperEntry = LeadHopper::model()->find(array(
				'condition' => 'lead_id = :lead_id',
				'params' => array(
					':lead_id' => $_POST['current_lead_id'],
				),
			));	
			
			$hopperEntry->status = LeadHopper::STATUS_DONE;
			
			if( $hopperEntry->save(false) )
			{
				$result['status'] = 'success';
				$result['message'] = 'Database has been updated.';
			}
		}
		
		echo json_encode($result);
	}

	public function actionLoadLeadToHopper()
	{
		$authAccount = Yii::app()->user->account;
		$date = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('America/Denver'));
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => array(
				'title' => '',
				'tabs' => '',
				'lead_info_fields' => '',
				'lead_info_dialer_buttons' => '',
				'lead_info_lead_phone_numbers' => '',
				'customer_info_fields' => '',
				'lead_history' => '',
				'appointment_tab' => '',
				'survey_tab' => '',
			),
			'current_lead_id' => '',
			'current_calendar_id' => '',
			'customer_id' => '',
			'caller_id' => '',
		);
		
		if( isset($_POST['ajax']) && isset($_POST['id']) && isset($_POST['type']) )
		{
			$leadCallHistoryId = null;
			
			$customerInHopperCount = LeadHopper::model()->count(array(
				'group' => 'customer_id',
			));
			
			$lead = Lead::model()->findByPk($_POST['id']);
			
			if( $lead )
			{
				$list = $lead->list;
				$customer = $lead->customer;
				
				$existingHopperEntry = LeadHopper::model()->find(array(
					'condition' => 'lead_id = :lead_id',
					'params' => array(
						':lead_id' => $_POST['id'],
					),
				));
				
				if( !empty($lead->timezone) )
				{
					$timeZone = $lead->timezone;
				}
				else
				{
					$timeZone = AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $customer->phone) );
				}
					
				if( $existingHopperEntry )
				{
					$leadHopperEntry = $existingHopperEntry;
				}
				else
				{
					$leadHopperEntry = new LeadHopper;										
				}

				$leadHopperEntry->setAttributes(array(
					'lead_id' => $lead->id,
					'list_id' => $list->id,
					'skill_id' => $list->skill_id,
					'customer_id' => $list->customer_id,
					'agent_account_id' => $authAccount->id,
					'lead_language' => $lead->language,
					'lead_timezone' => $timeZone,
					'status' => LeadHopper::STATUS_INCALL,
					'type' => $_POST['type'],
				));
				
				$skillChildConfirmation = SkillChild::model()->find(array(
					'condition' => 'skill_id = :skill_id AND type = :type',
					'params' => array(
						':skill_id' => $list->skill_id,
						':type' => SkillChild::TYPE_CONFIRM,
					),
				));
				
				if($skillChildConfirmation !== null)
				{
					$leadHopperEntry->skill_child_confirmation_id = $skillChildConfirmation->id;
				}
				
				$skillChildReschedule = SkillChild::model()->find(array(
					'condition' => 'skill_id = :skill_id AND type = :type',
					'params' => array(
						':skill_id' => $list->skill_id,
						':type' => SkillChild::TYPE_RESCHEDULE,
					),
				));
				
				if($skillChildReschedule !== null)
				{
					$leadHopperEntry->skill_child_reschedule_id = $skillChildReschedule->id;
				}
				
				if( !isset($_GET['ajax']))
				{
					//create record for lead wrap time
					$this->recordWrapTime($lead->id, $authAccount->id);
				}
				
				//start getting lead data
				$leadHistories = LeadHistory::model()->findAll(array(
					'condition' => 'lead_id = :lead_id AND type NOT IN(9) AND status=1',
					'params' => array(
						':lead_id' => $lead->id,
					), 
					'order' => 'date_created DESC',
				));
				
				$leadHistoryDataProvider = new CArrayDataProvider($leadHistories);
					
				$calendar = Calendar::model()->findByPk($list->calendar_id);

				$office = CustomerOffice::model()->findByPk( $calendar->office_id);	
				
				$customer = Customer::model()->findByPk($leadHopperEntry->customer_id);
					
				if( $office )
				{
					$calendars = Calendar::model()->findAll(array(
						'condition' => 'office_id = :office_id AND status=1',
						'params' => array(
							':office_id' => $office->id
						),
					));
					
					$calendarOptions = CHtml::listData( $calendars, 'id', 'name');
				}
				else
				{
					$calendarOptions = array();
				}
				
				$offices = CustomerOffice::model()->findAll(array(
					'condition' => 'customer_id = :customer_id AND status=1 AND is_deleted=0',
					'params' => array(
						':customer_id' => $customer->id
					),
				));

				$officeOptions = CHtml::listData( $offices, 'id', 'office_name');

				#change disposition list from Parent Skill to (Child Skill Disposition find TYPE_CONFIRM)
				#when... 
				# 1. LeadHopper = TYPE_CONFIRMATION_CALL;
			
				
				if( $_POST['type'] == LeadHopper::TYPE_CONFIRMATION_CALL || $_POST['type'] == LeadHopper::TYPE_RESCHEDULE || $_POST['type'] == LeadHopper::TYPE_NO_SHOW_RESCHEDULE )
				{
					$dispositionOptions = array();
					
					if($leadHopperEntry->type == LeadHopper::TYPE_CONFIRMATION_CALL)
					{
						$skillChild = SkillChild::model()->find(array(
							'condition' => 'skill_id = :skill_id AND type = :type',
							'params' => array(
								':skill_id' => $list->skill_id,
								':type' => SkillChild::TYPE_CONFIRM,
							),
						));
					}
					
					if($leadHopperEntry->type == LeadHopper::TYPE_RESCHEDULE || $leadHopperEntry->type == LeadHopper::TYPE_NO_SHOW_RESCHEDULE)
					{ 
						$skillChild = SkillChild::model()->find(array(
							'condition' => 'skill_id = :skill_id AND type = :type',
							'params' => array(
								':skill_id' => $list->skill_id,
								':type' => SkillChild::TYPE_RESCHEDULE,
							),
						));
						
					}
					
					if($skillChild !== null)
					{
						$childDispositions = SkillChildDisposition::model()->findAll(array(
							'condition' => 'skill_child_id = :skill_child_id',
							'params' => array(
								':skill_child_id' => $skillChild->id,
							),
							'order' => 'skill_child_disposition_name ASC',
						));
						
						$dispositionOptions = CHtml::listData( $childDispositions, 'id', 'skill_child_disposition_name');
						
						
					}
												
					if( $dispositionOptions )
					{
						foreach( $childDispositions as $childDisposition )
						{
							$dispositionHtmlOptions['options'][$childDisposition->id] = array(
								'is_appointment_set' => $childDisposition->is_appointment_set,
								'is_location_conflict' => $childDisposition->is_location_conflict,
								'is_schedule_conflict' => $childDisposition->is_schedule_conflict,
							);
						}
					}
				}
				else
				{
					$dispositions = SkillDisposition::model()->findAll(array(
						'condition' => 'skill_id = :skill_id',
						'params' => array(
							':skill_id' => $list->skill_id,
						),
						'order' => 'skill_disposition_name ASC',
					));
					
					$dispositionOptions = CHtml::listData( $dispositions, 'id', 'skill_disposition_name');
				
					if( $dispositionOptions )
					{
						foreach( $dispositions as $disposition )
						{
							$dispositionHtmlOptions['options'][$disposition->id] = array(
								'is_appointment_set' => $disposition->is_appointment_set,
								'is_location_conflict' => $disposition->is_location_conflict,
								'is_schedule_conflict' => $disposition->is_schedule_conflict,
							);
						}
					}
				}
				
				//get caller ID
				if( $lead->list->dialing_as_number == 1 )
				{
					$callerID = $customerSkill->customer->phone;
				}
				else
				{
					$hostManager = CustomerOfficeStaff::model()->findByPk($lead->list->dialing_as_number);
					
					if( $hostManager )
					{
						$callerID = preg_replace('/[^0-9]/', '', $hostManager->phone);
					}
				}


				if( $leadHopperEntry->save(false) )
				{	
					$showAppointmentTab = false;
					$showSurveyTab = false;
					$showScriptTab = false;
					$showDataTab = false;
					
					//check appointment tab settings
					if( isset($leadHopperEntry->skill) && $leadHopperEntry->skill->enable_dialer_appointment_tab == 1 )
					{
						$showAppointmentTab = true;
					}
					
					//check appointment tab settings
					if( isset($leadHopperEntry->skill) && $leadHopperEntry->skill->enable_survey_tab == 1 )
					{
						$showSurveyTab = true;
					}
	
					//check script tab settings
					if( $leadHopperEntry->type == LeadHopper::TYPE_CONFIRMATION_CALL )
					{
						if( $leadHopperEntry->confirmChildSkill->enable_dialer_script_tab == 1 )
						{
							$showScriptTab = true;
						}
					}
					elseif( $leadHopperEntry->type == LeadHopper::TYPE_RESCHEDULE )
					{
						if( $leadHopperEntry->rescheduleChildSkill->enable_dialer_script_tab == 1 )
						{
							$showScriptTab = true;
						}
					}
					else
					{
						if( $leadHopperEntry->skill->enable_dialer_script_tab == 1 )
						{
							$showScriptTab = true;
						}
					}
					
					//check data tab settings
					if( isset($leadHopperEntry->skill) && $leadHopperEntry->skill->enable_dialer_data_tab == 1 )
					{
						$showDataTab = true;
					}
					
					switch( $leadHopperEntry->type )
					{
						default: case LeadHopper::TYPE_CONTACT: 
							$callType = 'CONTACT CALL';
							$callTypeClass = 'label-success';
						break;
						
						case LeadHopper::TYPE_CALLBACK: 
							$callType = 'CALLBACK'; 
							$callTypeClass = 'label-danger';
						break;
						
						case LeadHopper::TYPE_CONFIRMATION_CALL: 
							$callType = 'CONFIRMATION CALL'; 
							$callTypeClass = 'label-warning';
						break;
						
						case LeadHopper::TYPE_LEAD_SEARCH: 
							$callType = 'LEAD SEARCH'; 
							$callTypeClass = 'label-info';
						break;
						
						case LeadHopper::TYPE_CONFLICT: 
							$callType = 'CONFLICT'; 
							$callTypeClass = 'label-danger';
						break;
						
						case LeadHopper::TYPE_RESCHEDULE: 
							$callType = 'RESCHEDULE'; 
							$callTypeClass = 'label-danger';
						break;
						
						case LeadHopper::TYPE_NO_SHOW_RESCHEDULE: 
							$callType = 'NO SHOW RESCHEDULE'; 
							$callTypeClass = 'label-danger';
						break;
					}
						
					/** MAIN TABS **/
					$result['html']['tabs'] = $this->renderPartial('_get_lead_tabs', array(
						'showAppointmentTab' => $showAppointmentTab,
						'showSurveyTab' => $showSurveyTab,
						'showScriptTab' => $showScriptTab,
						'showDataTab' => $showDataTab,
					), true);
					
					
					/** DIALER TAB **/
						
					//LEAD INFORMATION
						$result['html']['title'] = $this->renderPartial('_get_lead_lead_info_title', array(
							'callType' => $callType,
							'callTypeClass' => $callTypeClass,
							'list' => $list,
						), true);
					
						$result['html']['lead_info_fields'] = $this->renderPartial('_get_lead_lead_info_fields', array(
							'lead' => $lead,
						), true);

						$result['html']['lead_info_dialer_buttons'] = $this->renderPartial('_get_lead_dialer_buttons', array(
							'lead' => $lead,
						), true);			
						
						$result['html']['lead_info_lead_phone_numbers'] = $this->renderPartial('_get_lead_info_phone_numbers', array(
							'leadCallHistoryId' => $leadCallHistoryId,
							'lead' => $lead,
							'list' => $list,
							'customer' => $customer,
							'dispositionOptions' => $dispositionOptions,
							'dispositionHtmlOptions' => $dispositionHtmlOptions,
							'xfrs' => $xfrs,
						), true);
							
					//CUSTOMER INFORMATION
						$result['html']['customer_info_fields'] = $this->renderPartial('_get_lead_customer_info', array(
							'customer' => $customer,
							'office' => $office,
							'officeOptions' => $officeOptions,
						), true);
					
					//LEAD HISTORY
						$result['html']['lead_history'] = $this->renderPartial('_get_lead_history', array(
							'lead' => $lead,
							'leadHistoryDataProvider' => $leadHistoryDataProvider,
						), true);
						
						
					/** OTHER TAB CONTENT **/
					
						$tabContentsHtml = '';
						
						if( $showSurveyTab )
						{
							$tabContentsHtml .= '<div id="surveys" class="tab-pane fade"></div>';
						}

						$tabContentsHtml .= '<div id="googlemap" class="tab-pane fade in"></div>';

						if( $showScriptTab )
						{
							$tabContentsHtml .= '<div id="script" class="tab-pane fade in"></div>';	
						}

						if( $showDataTab )
						{
							$tabContentsHtml .= '<div id="data" class="tab-pane fade in"></div>';
						}
							
					$result['html']['tab_contents'] = $tabContentsHtml;
					
					$result['current_skill_id'] = $list->skill_id;
					$result['current_lead_id'] = $lead->id;
					$result['current_calendar_id'] = $calendar->id;
					$result['customer_id'] = $customer->id;
					$result['current_call_history_id'] = $leadCallHistoryId;
					$result['caller_id'] = $callerID;
					$result['status'] = 'success';		
				}
			}
			
			if(isset($_POST['is_emailSupervisor']) && $_POST['is_emailSupervisor'] == true)
			{
				if( isset($authAccount->accountUser) )
				{
					$agentName = $authAccount->accountUser->getFullName();
				}
				else
				{
					$agentName = $authAccount->customerOfficeStaff->staff_name;
				}
				
				$MsgHTML = '<p>Agent Name: ' . $agentName.'</p>';
				$MsgHTML .= '<p>Customer Name: ' . $customer->firstname.' '.$customer->lastname.'</p>';
				$MsgHTML .= '<p>Lead Name: '.$lead->getFullName().'</p>';
				$MsgHTML .= '<p>Time of Call: '. $date->format("M d, Y g:i a").'</p>';
									
				Yii::import('application.extensions.phpmailer.JPhpMailer');
							
				$mail = new JPhpMailer(true);

				$mail->SetFrom('service@engagex.com');
				
				$mail->Subject = 'Lead Single Searched - Called before schedule';

				$mail->MsgHTML($MsgHTML);
				
				// $mail->AddAddress('sophie.valentine@engagex.com');
				$mail->AddAddress('douglas.larsen@engagex.com');
				// $mail->AddAddress('lucas.ashburn@engagex.com');
				
				$mail->AddBCC('jim.campbell@engagex.com');
				// $mail->AddBCC('markjuan169@gmail.com');
				
				if( !$mail->Send() )
				{
					$result = array(
						'status' => 'error',
						'message' => 'Email not send.',
					);
				}
			}	
		}
		
		echo json_encode($result);
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
		
		// var_dump($latestCall);
		if( $latestCall )
		{
			echo 'Last Call: '.date("M d, Y g:i A", strtotime($latestCall->end_call_time));
			echo '<br>';
			if( $latestCall->is_skill_child == 1 )
			{
				if( time() >= (strtotime($latestCall->end_call_time) + $latestCall->skillChildDisposition->retry_interval) )
				{
					$leadIsCallable = true;
				}
			}
			else
			{
				if( isset($latestCall->skillDisposition) && time() >= (strtotime($latestCall->end_call_time) + $latestCall->skillDisposition->retry_interval) )
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
	
	public function recordWrapTime($lead_id=null, $agent_account_id=null, $type='')
	{
		$groupWraptime = LeadCallWrapTime::model()->find(array(
			'condition' => 'agent_account_id = :agent_account_id',
			'params' => array(
				':agent_account_id' => $agent_account_id,
			),
			'order' => 'start_time DESC',
		));
			
		if( $type == 'endOnly' )
		{
			$existingWrapTime = LeadCallWrapTime::model()->find(array(
				'condition' => 'agent_account_id = :agent_account_id AND end_time IS NULL',
				'params' => array(
					':agent_account_id' => $agent_account_id,
				),
				'order' => 'start_time DESC',
			));
			
			if( $existingWrapTime )
			{
				$existingWrapTime->end_time = date('Y-m-d H:i:s');
				
				
				// if($groupWraptime === null)
				// {
					// $existingWrapTime->group_id = $wrapTime->id;
				// }
				// else
				// {
					// if($existingWrapTime->lead_id == $groupWraptime->lead_id)
					// {
						// $existingWrapTime->group_id = $groupWraptime->group_id;
					// }
					// else
					// {
						// $existingWrapTime->group_id = $wrapTime->group_id;
					// }
				// }
				
				return $existingWrapTime->save(false);
			}
		}
		else
		{
			$existingWrapTime = LeadCallWrapTime::model()->find(array(
				'condition' => 'lead_id = :lead_id AND agent_account_id = :agent_account_id AND end_time IS NULL',
				'params' => array(
					':lead_id' => $lead_id,
					':agent_account_id' => $agent_account_id,
				),
				'order' => 'start_time DESC',
			));
			
			if( $existingWrapTime )
			{
				
				$wrapTime = $existingWrapTime;
			}
			else
			{
				$wrapTime = new LeadCallWrapTime;
			}
			
			$wrapTime->setAttributes(array(
				'lead_id' => $lead_id,
				'agent_account_id' => $agent_account_id,
			));
			
			if( $wrapTime->isNewRecord )
			{
				$existingleadHopperEntry = LeadHopper::model()->find(array(
					'condition' => 'lead_id = :lead_id',
					'params' => array(
						':lead_id' => $lead_id,
					),
				));
				
				if( $existingleadHopperEntry )
				{
					$wrapTime->call_type = $existingleadHopperEntry->type;
					
					$wrapTime->main_skill_id = $existingleadHopperEntry->skill_id;
					
					if( $existingleadHopperEntry->type == LeadHopper::TYPE_CONFIRMATION_CALL )
					{
						$wrapTime->child_skill_id = $existingleadHopperEntry->skill_child_confirmation_id;
					}
					else
					{
						$wrapTime->child_skill_id = $existingleadHopperEntry->skill_child_reschedule_id;
					}
				}
				
				$wrapTime->start_time = date('Y-m-d H:i:s');
				
			}
			
			if($wrapTime->save(false))
			{
				if($groupWraptime === null)
				{
					$wrapTime->group_id = $wrapTime->id;
				}
				else
				{
					if($wrapTime->lead_id == $groupWraptime->lead_id)
					{
						$wrapTime->group_id = $groupWraptime->group_id;
					}
					else
					{
						$wrapTime->group_id = $wrapTime->id;
					}
				}
				
				$wrapTime->save(false);
			}
			
			
			return $wrapTime;
		}
	}

}

?>