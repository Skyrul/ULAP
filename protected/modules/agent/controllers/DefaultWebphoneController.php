<?php 

ini_set('memory_limit', '4000M');
set_time_limit(0);

Class DefaultWebphoneController extends Controller
{
	public $layout='//layouts/agent_dialer';
	
	public function actionIndex($action='')
	{
		$result = array(
			'status' => '',
			'message' => '',
			'getFlashes' => '',
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
				'customer_queue_popup' => '',
			),
			'current_lead_id' => '',
			'current_calendar_id' => '',
			'customer_id' => '',
			'caller_id' => '',
			'customer_popup_delay' => 0,
		);
		
		$authAccount = Yii::app()->user->account;
	
		//get sip login info
		if( isset($authAccount->accountUser) )
		{
			$sipUsername = $authAccount->accountUser->phone_extension;
			$sipPassword = 'Enagagex123';
			$sipServer = '64.251.13.2';
		}
		
		//queue popup settings
		$accountQueuePopup = new AccountQueuePopup;
		
		$existingAccountQueuePopup = AccountQueuePopup::model()->find(array(
			'condition' => 'account_id = :account_id',
			'params' => array(
				':account_id' => $authAccount->id
			),
		));
		
		if( $existingAccountQueuePopup )
		{
			$accountQueuePopup = $existingAccountQueuePopup;
		}
		else
		{
			$accountQueuePopup->setAttributes(array(
				'account_id' => $authAccount->id,
				'current_customer_id' => $customer->id,
				'showpop' => 1,
			));
			
			$accountQueuePopup->save(false);
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

			if( isset($_REQUEST['ajax']) && isset($_REQUEST['action']) && $_REQUEST['action'] == 'nextLead' )
			{
				//force skip lead 
				if( isset($_POST['skipCall']) && isset($_POST['lead_id']) )
				{
					$existingleadHopperEntry = LeadHopper::model()->find(array(
						'condition' => 'lead_id = :lead_id AND type !=3',
						'params' => array(
							':lead_id' => $_POST['lead_id'],
						),
					));
					
					if( $existingleadHopperEntry )
					{
						if( $existingleadHopperEntry->delete() )
						{
							$dialerSkipLeadTracker = new DialerSkipLeadTracker;
							
							$dialerSkipLeadTracker->setAttributes(array(
								'agent_account_id' => $authAccount->id,
								'lead_id' => $_POST['lead_id'],
							));
							
							$dialerSkipLeadTracker->save(false);
						}
					}
				}
				
				//check for customer queue without agent and set their dials until reset back to 20
				// CustomerQueueViewer::model()->updateAll(array('dials_until_reset' => 20), 'dials_until_reset > 0 AND dials_until_reset < 20 AND (call_agent IS NULL OR call_agent="")');				
				// CustomerQueueViewer::model()->updateAll(array('dials_until_reset' => 20), 'dials_until_reset < 20 AND (call_agent IS NULL OR call_agent="")');				
				// LeadHopper::model()->updateAll(array('status' => 'DONE'), 'type IN (3,6,7) AND status = "INCALL" AND agent_account_id IS NULL');	
				
				if( !empty($_POST['lead_hopper_id']) || isset($_POST['search_lead_id']))
				{
					
					if(!isset($_REQUEST['ajax']))
					{
						//end record for lead wrap time
						$this->recordWrapTime(null, $authAccount->id, 'endOnly');
					}
				}
				
				//start getting a lead from the hopper
				if( isset($_POST['current_lead_id']) )
				{
					//used to maintain the lead history of the current lead
					$existingleadHopperEntry = LeadHopper::model()->find(array(
						'condition' => 'lead_id = :lead_id',
						'params' => array(
							':lead_id' => $_POST['current_lead_id'],
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
							//prioritize leads that is in Confirm type
							$existingleadHopperEntry = LeadHopper::model()->find(array(
								'condition' => '
									t.agent_account_id = :agent_account_id 
									AND t.type != :lead_search 
									AND (t.status IN ("READY","INCALL")) AND t.type=3
									AND (t.skill_child_confirmation_id IN ('.implode(', ', $assignedSkillChildIds).') )
									AND (t.lead_language IN ("'.implode('","', $assignedLanguageIds).'"))
									AND (t.customer_id IN ("'.implode('","', $assignedCustomerIds).'"))
									AND ( DATE(t.appointment_date) = DATE(NOW()) )
								',
								'params' => array(
									':agent_account_id' => $authAccount->id,
									':lead_search' => LeadHopper::TYPE_LEAD_SEARCH,
								),
								'with'=>array('customer','customer.queueViewer'),
								'order' => 'queueViewer.priority DESC',
							));

							if(empty($existingleadHopperEntry))
							{
								//prioritize leads that is in Reschedule and No show type
								$existingleadHopperEntry = LeadHopper::model()->find(array(
									'condition' => '
										t.agent_account_id = :agent_account_id 
										AND t.type != :lead_search 
										AND (t.status IN ("READY","INCALL")) AND (t.type IN (6,7))
										AND (t.skill_child_reschedule_id IN ('.implode(', ', $assignedSkillChildIds).')) 
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
							
							//prioritize leads that is in Callback and Conflict type
							if(empty($existingleadHopperEntry))
							{
								$existingleadHopperEntry = LeadHopper::model()->find(array(
									'condition' => '
										t.agent_account_id = :agent_account_id 
										AND t.type != :lead_search 
										AND (t.status IN ("READY","INCALL")) 
										AND ( (t.type=2 AND t.callback_date IS NOT NULL AND NOW() >= t.callback_date) OR (t.type = 5) )
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
							//prioritize leads that is in Confirm type
							$existingleadHopperEntry = LeadHopper::model()->find(array(
								'condition' => '
									t.agent_account_id = :agent_account_id 
									AND t.type != :lead_search 
									AND (t.status IN ("READY","INCALL")) AND t.type=3
									AND (t.skill_child_confirmation_id IN ('.implode(', ', $assignedSkillChildIds).') )
									AND (t.lead_language IN ("'.implode('","', $assignedLanguageIds).'"))
									AND ( DATE(t.appointment_date) = DATE(NOW()) )
								',
								'params' => array(
									':agent_account_id' => $authAccount->id,
									':lead_search' => LeadHopper::TYPE_LEAD_SEARCH,
								),
								'with'=>array('customer','customer.queueViewer'),
								'order' => 'queueViewer.priority DESC',
							));

							if(empty($existingleadHopperEntry))
							{
								//prioritize leads that is in Reschedule and No show type
								$existingleadHopperEntry = LeadHopper::model()->find(array(
									'condition' => '
										t.agent_account_id = :agent_account_id 
										AND t.type != :lead_search 
										AND (t.status IN ("READY","INCALL")) AND (t.type IN (6,7))
										AND (t.skill_child_reschedule_id IN ('.implode(', ', $assignedSkillChildIds).')) 
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
							
							//prioritize leads that is in Callback and Conflict type
							if(empty($existingleadHopperEntry))
							{
								$existingleadHopperEntry = LeadHopper::model()->find(array(
									'condition' => '
										t.agent_account_id = :agent_account_id 
										AND t.type != :lead_search 
										AND (t.status IN ("READY","INCALL")) 
										AND ( (t.type=2 AND t.callback_date IS NOT NULL AND NOW() >= t.callback_date) OR (t.type = 5) )
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
					if( !empty($_POST['lead_hopper_id']) && !isset($_POST['ajax']) )
					{
						//prioritize leads that is in Confirm type
						$leadHopperEntry = LeadHopper::model()->find(array(
							'condition' => '
								t.id != :current_lead_hopper_id  
								AND t.customer_id = :customer_id 
								AND agent_account_id = :agent_account_id 
								AND (t.status IN ("READY","INCALL")) 
								AND (
										( t.type = 3 AND ( DATE(t.appointment_date) = DATE(NOW()) ) ) 
									)
								AND (t.skill_child_confirmation_id IN ('.implode(', ', $assignedSkillChildIds).')) 
								AND (t.lead_language IN ("'.implode('","', $assignedLanguageIds).'"))
							',
							'params' => array(
								':current_lead_hopper_id' => $_POST['lead_hopper_id'],
								':customer_id' => $existingleadHopperEntry->customer_id,
								':agent_account_id' => $authAccount->id,
							),
							'with'=>array('customer','customer.queueViewer'),
							'order' => 'queueViewer.priority DESC',
						));
						
						if(empty($leadHopperEntry))
						{
							//prioritize leads that is in Reschedule and No show type
							$leadHopperEntry = LeadHopper::model()->find(array(
								'condition' => '
									t.id != :current_lead_hopper_id  
									AND t.customer_id = :customer_id 
									AND agent_account_id = :agent_account_id 
									AND (t.status IN ("READY","INCALL")) AND (t.type IN (6,7)) 
									AND (t.skill_child_reschedule_id IN ('.implode(', ', $assignedSkillChildIds).') )
									AND (t.lead_language IN ("'.implode('","', $assignedLanguageIds).'"))
								',
								'params' => array(
									':current_lead_hopper_id' => $_POST['lead_hopper_id'],
									':customer_id' => $existingleadHopperEntry->customer_id,
									':agent_account_id' => $authAccount->id,
								),
								'with'=>array('customer','customer.queueViewer'),
								'order' => 'queueViewer.priority DESC',
							));
						}
						
						if(empty($leadHopperEntry))
						{
							//prioritize leads that is in Callback and Conflict type
							$leadHopperEntry = LeadHopper::model()->find(array(
								'condition' => '
									t.id != :current_lead_hopper_id 
									AND t.customer_id = :customer_id 
									AND agent_account_id = :agent_account_id 
									AND (t.status IN ("READY","INCALL") )
									AND ( (t.type=2 AND t.callback_date IS NOT NULL AND NOW() >= t.callback_date) OR (t.type = 5) )
									AND (t.skill_id IN ('.implode(', ', $assignedSkillIds).'))
									AND queueViewer.dials_until_reset > 0
									AND queueViewer.next_available_calling_time = "Now"
									AND (t.lead_language IN ("'.implode('","', $assignedLanguageIds).'"))
								',
								'params' => array(
									':current_lead_hopper_id' => $_POST['lead_hopper_id'],
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
									':current_lead_hopper_id' => $_POST['lead_hopper_id'],
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
				
				if( isset($_POST['search_lead_id']) && !isset($_POST['ajax']))
				{
					$leadHopperEntry = LeadHopper::model()->find(array(
						'condition' => 'id = :id',
						'params' => array(
							':id' => $_POST['search_lead_id'],
						),
						'order' => 't.id ASC',
					));	
				}
				
				if($leadHopperEntry == null)
				{
					$addedCondition = '';
					
					// if( $customerInHopperCount >= 2 )
					// {						
						$addedCondition .= ' AND agent_account_id IS NULL';
					// }	
					
					if( $assignedCustomerIds )
					{
						//prioritize leads that is in Confirm type
						$leadHopperEntry = LeadHopper::model()->find(array(
							'condition' => '
								(t.status IN ("READY")) AND t.type=3
								AND (t.skill_child_confirmation_id IN ('.implode(', ', $assignedSkillChildIds).') )
								AND (t.lead_language IN ("'.implode('","', $assignedLanguageIds).'"))
								AND (t.customer_id IN ("'.implode('","', $assignedCustomerIds).'"))
								AND ( DATE(t.appointment_date) = DATE(NOW()) )
							' . $addedCondition,
							'order' => 't.id ASC',
						));
						
						if(empty($leadHopperEntry))
						{
							//prioritize leads that is in Reschedule type
							$leadHopperEntry = LeadHopper::model()->find(array(
								'with' => array('calendarAppointment', 'lead'),
								'condition' => '
									(t.status IN ("READY")) AND (t.type IN (6,7))
									AND (t.skill_child_reschedule_id IN ('.implode(', ', $assignedSkillChildIds).')) 
									AND (t.lead_language IN ("'.implode('","', $assignedLanguageIds).'"))
									AND (t.customer_id IN ("'.implode('","', $assignedCustomerIds).'"))
									AND lead.id IS NOT NULL
								' . $addedCondition,
								'order' => 'calendarAppointment.date_updated DESC',
							));
						}
						
						if(empty($leadHopperEntry))
						{
							$leadHopperEntry = LeadHopper::model()->find(array(
								'condition' => '
									(t.status IN ("READY") )
									AND ( (t.type=2 AND t.callback_date IS NOT NULL AND NOW() >= t.callback_date) OR (t.type = 5) )
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
						//prioritize leads that is in Confirm type
						$leadHopperEntry = LeadHopper::model()->find(array(
							'condition' => '
								(t.status IN ("READY")) AND t.type=3
								AND (t.skill_child_confirmation_id IN ('.implode(', ', $assignedSkillChildIds).') )
								AND (t.lead_language IN ("'.implode('","', $assignedLanguageIds).'"))
								AND ( DATE(t.appointment_date) = DATE(NOW()) )
							' . $addedCondition,
							'order' => 't.id ASC',
						));
						
						if(empty($leadHopperEntry))
						{
							//prioritize leads that is in Reschedule type
							$leadHopperEntry = LeadHopper::model()->find(array(
								'with' => array('calendarAppointment', 'lead'),
								'condition' => '
									(t.status IN ("READY")) AND (t.type IN (6,7))
									AND (t.skill_child_reschedule_id IN ('.implode(', ', $assignedSkillChildIds).')) 
									AND (t.lead_language IN ("'.implode('","', $assignedLanguageIds).'"))
									AND lead.id IS NOT NULL
								' . $addedCondition,
								'order' => 'calendarAppointment.date_updated DESC',
							));
						}
						
						if(empty($leadHopperEntry))
						{
							$leadHopperEntry = LeadHopper::model()->find(array(
								'condition' => '
									(t.status IN ("READY") )
									AND ( (t.type=2 AND t.callback_date IS NOT NULL AND NOW() >= t.callback_date) OR (t.type = 5) )
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
					
					$leadHopperEntry->agent_account_id = $authAccount->id;
					
					if( $leadHopperEntry->status == LeadHopper::STATUS_READY )
					{
						$leadHopperEntry->status = LeadHopper::STATUS_INCALL;
					}
					
					if( $leadHopperEntry->save(false) )
					{
						if( $leadHopperEntry->type != LeadHopper::TYPE_LEAD_SEARCH && !isset($_POST['ajax']) )
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
									// LeadHopper::model()->updateAll(array('agent_account_id' => null ), 'agent_account_id = ' . $authAccount->id);
									
									
									if($leadHopperEntry->type == 1)
									{
										// LeadHopper::model()->updateAll(array('agent_account_id' => null), 'type = 1 AND lead_id = ' . $leadHopperEntry->lead_id);
										// LeadHopper::model()->updateAll(array('agent_account_id' => null), 'type = 1 AND agent_account_id = ' . $authAccount->id);

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
									
									// CustomerQueueViewer::model()->updateAll(array('call_agent' => $authAccount->id), 'customer_id = ' . $leadHopperEntry->customer_id);
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

					#change disposition list from Parent Skill to (Child Skill Disposition find TYPE_CONFIRM)
					#when... 
					# 1. LeadHopper = TYPE_CONFIRMATION_CALL;
					
					if($leadHopperEntry->type == LeadHopper::TYPE_CONFIRMATION_CALL || $leadHopperEntry->type == LeadHopper::TYPE_RESCHEDULE || $leadHopperEntry->type == LeadHopper::TYPE_NO_SHOW_RESCHEDULE)
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
									'is_email_require' => $disposition->is_email_require,
								);
							}
						}
					
						if( $accountQueuePopup->current_customer_id != $customer->id )
						{
							$accountQueuePopup->current_customer_id = $customer->id;
							$accountQueuePopup->show_popup = 1;
							$accountQueuePopup->save(false);
						}
					}
					
					//get callerid for webphone
					if( $authAccount->use_webphone == 1 )
					{
						$customerSkill = CustomerSkill::model()->find(array(
							'condition' => 'customer_id = :customer_id AND skill_id = :skill_id',
							'params' => array(
								':customer_id' => $leadHopperEntry->customer_id,
								':skill_id' => $leadHopperEntry->skill_id,
							),
						));
						
						if( $customerSkill )
						{
							//get xfr address book
							$xfrs = CustomerSkillXfrAddressBook::model()->findAll(array(
								'condition' => 'customer_skill_id = :customer_skill_id',
								'params' => array(
									':customer_skill_id' => $customerSkill->id
								)
							));
							
							if( $customerSkill->skill->caller_option == 1 )
							{
								$callerID = $customerSkill->customer->phone_number;
							}
							else
							{
								if( $customerSkill->skill_caller_option_customer_choice == 1 ) //office phone
								{
									if( !empty($customerSkill->customer->phone) )
									{
										$callerID = $customerSkill->customer->phone;
									}
								}
								else
								{		
									$areaCode = preg_replace("/[^0-9]/","", $customerSkill->customer->phone);
									$areaCode = substr($areaCode, 0, 3);
									
									//temp override for customer courtney obrien
									if( $customerSkill->customer_id == 2437 && $areaCode == 610 )
									{
										$areaCode = 484;
									}
							
									$companyDid = CompanyDid::model()->find(array(
										'condition' => 'LOWER(company_name) = :company_name AND area_code = :area_code',
										'params' => array(
											':company_name' => ($customerSkill->customer->company->company_name),
											':area_code' => $areaCode,
										),
									));
									
									if( $companyDid )
									{
										$callerID = $companyDid->did;
									}
									else
									{
										if( !empty($customerSkill->customer->phone) )
										{
											$callerID = $customerSkill->customer->phone;
											
											$noDidEmailNotification = CustomerNoDidEmailer::model()->find(array(
												'condition' => 'phone_number = :phone_number AND date_created > DATE_SUB(NOW(), INTERVAL 1 MONTH)',
												'params' => array(
													':phone_number' => $customerSkill->customer->phone,
												),
											));
											
											if( empty($noDidEmailNotification) )
											{
												//And send an email to customer service@engagex.com 
												// include the customer name, customer Id, company name. 
												// And in the subject put "NO CALLER ID PHONE NUMBER ON FILE"
												
												$MsgHTML = '<p>Customer Name: ' . $customerSkill->customer->firstname.' '.$customerSkill->customer->lastname.'</p>';
												$MsgHTML .= '<p>Customer ID: '.$customerSkill->customer->account_number.'</p>';
												$MsgHTML .= '<p>Company Name: '.$customerSkill->customer->company->company_name.'</p>';
												$MsgHTML .= '<p>Area Code: '.$areaCode.'</p>';

												Yii::import('application.extensions.phpmailer.JPhpMailer');
										
												$mail = new JPhpMailer(true);

												$mail->SetFrom('service@engagex.com');
												
												$mail->Subject = 'NO CALLER ID PHONE NUMBER ON FILE - ' . $areaCode;

												$mail->MsgHTML($MsgHTML);
												
												$mail->AddAddress('customerservice@engagex.com');
												
												$mail->AddBCC('erwin.datu@engagex.com');
												// $mail->AddAddress('erwin.datu@engagex.com');

												if( $mail->Send() )
												{
													$newEmailNotification = new CustomerNoDidEmailer;
												
													$newEmailNotification->setAttributes(array(
														'customer_id' => $customerSkill->customer->id,
														'phone_number' => $customerSkill->customer->phone, 
														'date_created' => date('Y-m-d H:i:s'),
													));
													
													$newEmailNotification->save(false);
												}
											}
										}
									}
								}
							}
						}
					}
					
					$showAppointmentTab = false;
					$showSurveyTab = false;
					$showScriptTab = false;
					$showDataTab = false;
					$showEmailSettingTab = false;
					
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
	
					if( isset($leadHopperEntry->skill) && $leadHopperEntry->skill->enable_email_setting == 1 )
					{
						$showEmailSettingTab = true;
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
						'showEmailSettingTab' => $showEmailSettingTab,
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
						
					//Customer Queue Popup
						if( $accountQueuePopup && $accountQueuePopup->show_popup == 1 )
						{
							$accountQueuePopup->show_popup = 0;
							
							if( $accountQueuePopup->save(false) )
							{
								$result['html']['customer_queue_popup'] = $this->renderPartial('customerQueuePopup', array(
									'leadHopperEntry' => $leadHopperEntry,
								), true);		

								$result['customer_popup_delay'] = $leadHopperEntry->skill->customer_popup_delay;
							}
						}
						
						
					/** OTHER TAB CONTENT **/
					
						$tabContentsHtml = '';
						
						if( $showAppointmentTab )
						{
							$tabContentsHtml .= '<div id="appointments" class="tab-pane fade"></div>';
						}
						
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
						
						if( $showEmailSettingTab)
						{
							$tabContentsHtml .= '<div id="emailSettingTab" class="tab-pane fade in"></div>';
						}
							
					$result['html']['tab_contents'] = $tabContentsHtml;
					
					$result['current_lead_id'] = $lead->id;
					$result['current_calendar_id'] = $calendar->id;
					$result['customer_id'] = $customer->id;
					$result['current_call_history_id'] = $leadCallHistoryId;
					
					$result['sip_username'] = $sipUsername;
					$result['sip_password'] = $sipPassword;
					$result['sip_server'] = $sipServer;
					$result['caller_id'] = preg_replace('/[^0-9]/', '', $callerID);
					
					$result['status'] = 'success';	
				}
				else
				{
					$result['status'] = 'error';
					
					$result['html']['title'] = $this->getEmptyLeadInfoHtml('title');
					$result['html']['lead_info_fields'] = $this->getEmptyLeadInfoHtml('lead_info_fields');
					$result['html']['lead_info_dialer_buttons'] = $this->getEmptyLeadInfoHtml('lead_info_dialer_buttons');
					$result['html']['lead_info_lead_phone_numbers'] = $this->getEmptyLeadInfoHtml('lead_info_lead_phone_numbers');
					$result['html']['customer_info_fields'] = $this->getEmptyLeadInfoHtml('customer_info_fields');
					$result['html']['lead_history'] = $this->getEmptyLeadInfoHtml('lead_history');

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

			/* webphone engine priority
				> possible values:
				0: Disabled (never use this engine)
				1: Lower (decrease the engine priority)
				2: Normal (default)
				3: Higher (will boost engine priority)
				4: Highest (will use this engine whenever possible)
				5: Force (only this engine will be used)
			*/
			
			$enginePriorityWebrtc = 5;
			$enginePriorityNS = 0;
			
			//override for webphone dev page
			if( in_array( $authAccount->id, array(5345)) )  //HostDialer8
			{
				$enginePriorityWebrtc = 0;
				$enginePriorityNS = 5;
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
			'getFlashes' => '',
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
				'customer_queue_popup' => '',
			),
			'current_lead_id' => '',
			'current_calendar_id' => '',
			'customer_id' => '',
			'caller_id' => '',
			'customer_popup_delay' => 0,
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
				
				//queue popup settings
				$accountQueuePopup = new AccountQueuePopup;
				
				$existingAccountQueuePopup = AccountQueuePopup::model()->find(array(
					'condition' => 'account_id = :account_id',
					'params' => array(
						':account_id' => $authAccount->id
					),
				));
				
				if( $existingAccountQueuePopup )
				{
					$accountQueuePopup = $existingAccountQueuePopup;
				}
				else
				{
					$accountQueuePopup->setAttributes(array(
						'account_id' => $authAccount->id,
						'current_customer_id' => $customer->id,
						'showpop' => 1,
					));
					
					$accountQueuePopup->save(false);
				}
				
				
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
								'is_email_require' => $disposition->is_email_require,
							);
						}
					}
					
					if( $accountQueuePopup->current_customer_id != $customer->id )
					{
						$accountQueuePopup->current_customer_id = $customer->id;
						$accountQueuePopup->show_popup = 1;
						$accountQueuePopup->save(false);
					}
				}
				
				//get callerid for webphone
				if( $authAccount->use_webphone == 1 )
				{
					$customerSkill = CustomerSkill::model()->find(array(
						'condition' => 'customer_id = :customer_id AND skill_id = :skill_id',
						'params' => array(
							':customer_id' => $leadHopperEntry->customer_id,
							':skill_id' => $leadHopperEntry->skill_id,
						),
					));
					
					if( $customerSkill )
					{
						//get xfr address book
						$xfrs = CustomerSkillXfrAddressBook::model()->findAll(array(
							'condition' => 'customer_skill_id = :customer_skill_id',
							'params' => array(
								':customer_skill_id' => $customerSkill->id
							)
						));
						
						if( $customerSkill->skill->caller_option == 1 )
						{
							$callerID = $customerSkill->customer->phone_number;
						}
						else
						{
							if( $customerSkill->skill_caller_option_customer_choice == 1 ) //office phone
							{
								if( !empty($customerSkill->customer->phone) )
								{
									$callerID = $customerSkill->customer->phone;
								}
							}
							else
							{		
								$areaCode = preg_replace("/[^0-9]/","", $customerSkill->customer->phone);
								$areaCode = substr($areaCode, 0, 3);
								
								//temp override for customer courtney obrien
								if( $customerSkill->customer_id == 2437 && $areaCode == 610 )
								{
									$areaCode = 484;
								}
						
								$companyDid = CompanyDid::model()->find(array(
									'condition' => 'LOWER(company_name) = :company_name AND area_code = :area_code',
									'params' => array(
										':company_name' => ($customerSkill->customer->company->company_name),
										':area_code' => $areaCode,
									),
								));
								
								if( $companyDid )
								{
									$callerID = $companyDid->did;
								}
								else
								{
									if( !empty($customerSkill->customer->phone) )
									{
										$callerID = $customerSkill->customer->phone;
										
										$noDidEmailNotification = CustomerNoDidEmailer::model()->find(array(
											'condition' => 'phone_number = :phone_number AND date_created > DATE_SUB(NOW(), INTERVAL 1 MONTH)',
											'params' => array(
												':phone_number' => $customerSkill->customer->phone,
											),
										));
										
										if( empty($noDidEmailNotification) )
										{
											//And send an email to customer service@engagex.com 
											// include the customer name, customer Id, company name. 
											// And in the subject put "NO CALLER ID PHONE NUMBER ON FILE"
											
											$MsgHTML = '<p>Customer Name: ' . $customerSkill->customer->firstname.' '.$customerSkill->customer->lastname.'</p>';
											$MsgHTML .= '<p>Customer ID: '.$customerSkill->customer->account_number.'</p>';
											$MsgHTML .= '<p>Company Name: '.$customerSkill->customer->company->company_name.'</p>';
											$MsgHTML .= '<p>Area Code: '.$areaCode.'</p>';

											Yii::import('application.extensions.phpmailer.JPhpMailer');
									
											$mail = new JPhpMailer(true);

											$mail->SetFrom('service@engagex.com');
											
											$mail->Subject = 'NO CALLER ID PHONE NUMBER ON FILE - ' . $areaCode;

											$mail->MsgHTML($MsgHTML);
											
											$mail->AddAddress('customerservice@engagex.com');
											
											$mail->AddBCC('erwin.datu@engagex.com');
											// $mail->AddAddress('erwin.datu@engagex.com');

											if( $mail->Send() )
											{
												$newEmailNotification = new CustomerNoDidEmailer;
											
												$newEmailNotification->setAttributes(array(
													'customer_id' => $customerSkill->customer->id,
													'phone_number' => $customerSkill->customer->phone, 
													'date_created' => date('Y-m-d H:i:s'),
												));
												
												$newEmailNotification->save(false);
											}
										}
									}
								}
							}
						}
					}
				}


				if( $leadHopperEntry->save(false) )
				{	
					$showAppointmentTab = false;
					$showSurveyTab = false;
					$showScriptTab = false;
					$showDataTab = false;
					$showEmailSettingTab = false;
					
					//check appointment tab settings
					if( isset($leadHopperEntry->skill) && $leadHopperEntry->skill->enable_dialer_appointment_tab == 1 )
					{
						$showAppointmentTab = true;
					}
					
					//check survey tab settings
					if( isset($leadHopperEntry->skill) && $leadHopperEntry->skill->enable_survey_tab == 1 )
					{
						$showSurveyTab = true;
					}
					
					//check email setting tab settings
					if( isset($leadHopperEntry->skill) && $leadHopperEntry->skill->enable_email_setting == 1 )
					{
						$showEmailSettingTab = true;
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
						'showEmailSettingTab' => $showEmailSettingTab,
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
						
					//Customer Queue Popup
						if( $accountQueuePopup && $accountQueuePopup->show_popup == 1 )
						{
							$accountQueuePopup->show_popup = 0;
							
							if( $accountQueuePopup->save(false) )
							{
								$result['html']['customer_queue_popup'] = $this->renderPartial('customerQueuePopup', array(
									'leadHopperEntry' => $leadHopperEntry,
								), true);				
							
								$result['customer_popup_delay'] = $leadHopperEntry->skill->customer_popup_delay;
							}
						}
						
						
					/** OTHER TAB CONTENT **/
					
						$tabContentsHtml = '';
						
						if( $showAppointmentTab )
						{
							$tabContentsHtml .= '<div id="appointments" class="tab-pane fade"></div>';
						}
						
						if( $showSurveyTab )
						{
							$tabContentsHtml .= '<div id="surveys" class="tab-pane fade"></div>';
						}

						if( $showEmailSettingTab )
						{
							$tabContentsHtml .= '<div id="emailSetting" class="tab-pane fade"></div>';
						}
						
						// $tabContentsHtml .= '<div id="googlemap" class="tab-pane fade in"></div>';

						if( $showScriptTab )
						{
							$tabContentsHtml .= '<div id="script" class="tab-pane fade in"></div>';	
						}

						if( $showDataTab )
						{
							$tabContentsHtml .= '<div id="data" class="tab-pane fade in"></div>';
						}
							
					$result['html']['tab_contents'] = $tabContentsHtml;
					
					if( $authAccount->id == 4 && $authAccount->use_webphone == 1 ) //Agent 1
					{
						$callerID = '3155338216';
					}
					
					$result['current_skill_id'] = $list->skill_id;
					$result['current_lead_id'] = $lead->id;
					$result['current_calendar_id'] = $calendar->id;
					$result['customer_id'] = $customer->id;
					$result['current_call_history_id'] = $leadCallHistoryId;
					$result['caller_id'] = preg_replace('/[^0-9]/', '', $callerID);
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
	
	public function actionGetRecording()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		if( isset($_POST['ajax']) && $_POST['call_history_id'] )
		{
			$authAccount = Yii::app()->user->account;

			$channel = new AsteriskChannel;
			$channel->channel = 'SIP/' . $authAccount->sip_username;				
			$channel->call_history_id = $_POST['call_history_id'];							
			
			if( $channel->save(false) )
			{
				$asterisk = new AsteriskWebphone;
				$asterisk->getStatus();
			}
			
			$result['status'] = 'success';
			$result['message'] = 'Database has been updated.';
		}
		
		json_encode($result);
	}

	private function getEmptyLeadInfoHtml($section)
	{
		switch( $section )
		{
			default: 
			case 'title':
				
				$html = '
					<span class="label label-danger arrowed-in-right">CALL TYPE: </span>
					<span class="label label-success arrowed">SKILL:</span>
				';
				
			break;
			
			case 'lead_info_fields': 
			
				$html = '
					<div class="profile-info-row">
						<div class="profile-info-name"> LEAD NAME </div>

						<div class="profile-info-value">
							<span></span>
						</div>
					</div>
					
					<div class="profile-info-row">
						<div class="profile-info-name"> PARTNER NAME </div>

						<div class="profile-info-value">
							<span></span>
						</div>
					</div>
					
					<div class="profile-info-row">
						<div class="profile-info-name"> EMAIL ADDRESS </div>

						<div class="profile-info-value">
							<span></span>
						</div>
					</div>
					
					<div class="profile-info-row">
						<div class="profile-info-name"> ADDRESS </div>

						<div class="profile-info-value">
							<span></span>
						</div>
					</div>
					
					<div class="profile-info-row">
						<div class="profile-info-name"> LANGUAGE </div>

						<div class="profile-info-value">
							<span>
								<select disabled>
									<option>- Select -</option>
								</select>
							</span>
						</div>
					</div>
					
					<div class="profile-info-row">
						<div class="profile-info-name"> TIME ZONE </div>

						<div class="profile-info-value">
							<span>
								<select disabled>
									<option>- Select -</option>
								</select>
							</span>
						</div>
					</div>
				';
			
			break;
			
			case 'lead_info_dialer_buttons':
				
				$html = '
					<button class="btn btn-grey btn-xs dial-pad-btn disabled" data-toggle="modal" data-target="#dialPadModal" style="margin-right:40px;">DIAL PAD</button>

					<button class="btn btn-warning btn-circle hold-call-btn disabled">MUTE</button>																																	

					<button class="btn btn-purple btn-circle transfer-call-btn disabled">XFR</button>
					
					<button class="btn btn-danger btn-circle end-call-btn disabled">END</button>
				';
				
			break;
			
			case 'lead_info_lead_phone_numbers':
				
				$html = '
					<table class="table table-condensed">
						<tr>
							<th>HOME PHONE</th>
							
							<td><a href="#"></a></td>
							
							<td>
								<a class="blue" href="#" title="Edit">
									<i class="ace-icon fa fa-pencil bigger-125"></i>
								</a>
							</td>
							
							<td><span style="margin-right:10px;">DIAL COUNT</span> 0</td>
							
							<td>
								<div class="form-group">
									<label for="form-field-1" class="col-sm-5 control-label no-padding-right"> DISPOSITION </label>

									<div class="col-sm-7">
										<select class="col-xs-12" disabled>
											<option></option>
										</select>
									</div>
								</div>
								
								<div class="form-group">
									<label for="form-field-1" class="col-sm-5 control-label no-padding-right"> DETAIL </label>

									<div class="col-sm-7">
										<select class="col-xs-12" disabled>
											<option></option>
										</select>
									</div>
								</div>
							</td>
						</tr>
						
						<tr>
							<th>CELL PHONE</th>
							
							<td><a href="#"></a></td>
							
							<td>
								<a class="blue" href="#" title="Edit">
									<i class="ace-icon fa fa-pencil bigger-125"></i>
								</a>
							</td>
							
							<td><span style="margin-right:10px;">DIAL COUNT</span>  0</td>
							
							<td>
								<div class="form-group">
									<label for="form-field-1" class="col-sm-5 control-label no-padding-right"> DISPOSITION </label>

									<div class="col-sm-7">
										<select class="col-xs-12" disabled>
											<option></option>
										</select>
									</div>
								</div>
								
								<div class="form-group">
									<label for="form-field-1" class="col-sm-5 control-label no-padding-right"> DETAIL </label>

									<div class="col-sm-7">
										<select class="col-xs-12" disabled>
											<option></option>
										</select>
									</div>
								</div>
							</td>
						</tr>
						
						<tr>
							<th>OFFICE PHONE</th>
							
							<td><a href="#"></a></td>
							
							<td>
								<a class="blue" href="#" title="Edit">
									<i class="ace-icon fa fa-pencil bigger-125"></i>
								</a>
							</td>
							
							<td><span style="margin-right:10px;">DIAL COUNT</span> 0</td>
							
							<td>
								<div class="form-group">
									<label for="form-field-1" class="col-sm-5 control-label no-padding-right"> DISPOSITION </label>

									<div class="col-sm-7">
										<select class="col-xs-12" disabled>
											<option></option>
										</select>
									</div>
								</div>
								
								<div class="form-group">
									<label for="form-field-1" class="col-sm-5 control-label no-padding-right"> DETAIL </label>

									<div class="col-sm-7">
										<select class="col-xs-12" disabled>
											<option></option>
										</select>
									</div>
								</div>
							</td>
						</tr>
					</table>
				';
				
			break;
			
			case 'customer_info_fields':
				
				$html = '
					<div class="widget-header">
						<h4 class="widget-title lighter smaller">
							CUSTOMER INFORMATION
						</h4>
					</div>
					<div class="widget-body">
						<div class="widget-main">
							<div class="row">
								<div class="col-xs-12 col-sm-3 center">
									<span class="profile-picture">
										<div style="height:180px; border:1px dashed #ccc; text-align:center; line-height: 180px;">No Image Uploaded.</div>
									</span>
								</div><!-- /.col -->

								<div class="col-xs-12 col-sm-9">	
									<div class="row-fluid">
										<div class="profile-user-info profile-user-info-striped">
											<div class="profile-info-row">
												<div class="profile-info-name"> CUSTOMER NAME </div>

												<div class="profile-info-value">
													<span></span>
												</div>
											</div>
											
											<div class="profile-info-row">
												<div class="profile-info-name"> OFFICE </div>

												<div class="profile-info-value">
													<span>
														<select disabled>
															<option>- Select -</option>
														</select>
													</span>
												</div>
											</div>
											
											<div class="profile-info-row">
												<div class="profile-info-name"> OFFICE ADDRESS </div>

												<div class="profile-info-value">
													<span></span>
												</div>
											</div>
											
											<div class="profile-info-row">
												<div class="profile-info-name"> OFFICE CITY </div>

												<div class="profile-info-value">
													<span></span>
												</div>
											</div>
											
											<div class="profile-info-row">
												<div class="profile-info-name"> OFFICE STATE </div>

												<div class="profile-info-value">
													<span></span>
												</div>
											</div>
											
											<div class="profile-info-row">
												<div class="profile-info-name"> OFFICE PHONE # </div>

												<div class="profile-info-value">
													<span></span>
												</div>
											</div>
											
											<div class="profile-info-row">
												<div class="profile-info-name"> OFFICE EMAIL </div>

												<div class="profile-info-value">
													<span></span>
												</div>
											</div>
											
											<div class="profile-info-row">
												<div class="profile-info-name"> CUSTOMER NOTES </div>

												<div class="profile-info-value">
													<span></span>
												</div>
											</div>
										</div>
									</div>
							
								</div><!-- /.col -->
							</div>
						</div>
					</div>
				';
				
			break;
			
			case 'lead_history':
				
				$html = '
					<div class="widget-header">
						<h4 class="widget-title lighter smaller">
							LEAD HISTORY
						</h4>
					</div>

					<div class="widget-body">
						<div class="widget-main no-padding lead-history-container">
							<!-- #section:pages/dashboard.conversations -->
							<div class="dialogs" style="max-height: 400px; overflow:auto;">
								<div class="timeline-container">
									<div id="leadHistoryList" class="list-view">
										<div class="timeline-items">
											<div class="items">
												No results found.
											</div>
										</div>
									</div>
								</div>
							</div>

							<form id="leadHistoryForm">
								<div class="form-actions clearfix">
									<div class="row-fluid clearfix">							
										<textarea class="col-xs-12" disabled></textarea>
									</div>

									<div class="space-6"></div>

									<button class="btn btn-sm btn-info no-radius pull-right disabled" type="button">
										SUBMIT NOTE
									</button>
								</div>
							</form>
						</div>
					</div>
				';
				
			break;
		}
		
		return $html;
	}
}

?>