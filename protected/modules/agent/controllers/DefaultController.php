<?php

ini_set('memory_limit', '4000M');
set_time_limit(0);

class DefaultController extends Controller
{
	public $layout='//layouts/agent_dialer';
		
	public function actionIndex($action='')
	{
		ini_set('memory_limit', '4000M');
		set_time_limit(0);
		
		$authAccount = Yii::app()->user->account;

		$readyForCalling = true;
		$hasDialerAccess = false;
		
		if( $authAccount->account_type_id == Account::TYPE_AGENT || $authAccount->account_type_id == Account::TYPE_GRATON_AGENT || $authAccount->account_type_id == Account::TYPE_HOSTDIAL_AGENT || $authAccount->checkPermission('dual_access_dialer_crm','visible') )
		{
			$hasDialerAccess = true;
		}
		
		if( in_array($authAccount->account_type_id, array(Account::TYPE_CUSTOMER, Account::TYPE_CUSTOMER_OFFICE_STAFF, Account::TYPE_COMPANY)) )
		{
			$hasDialerAccess = false;
		}
		
		if( $hasDialerAccess ) 
		{
			if( isset($authAccount->accountUser) && empty($authAccount->accountUser->phone_extension) )
			{
				$readyForCalling = false;
				
				Yii::app()->user->setFlash('danger', 'Phone extension is not yet set.');
			}
			elseif( isset($authAccount->customerOfficeStaff->sip_username) && empty($authAccount->customerOfficeStaff->sip_username) )
			{
				$readyForCalling = false;
				
				Yii::app()->user->setFlash('danger', 'Phone extension is not yet set.');
			}
			else
			{
				$currentLoginState = AccountLoginState::model()->find(array(
					'condition' => 'account_id = :account_id AND end_time IS NULL',
					'params' => array(
						':account_id' => $authAccount->id,
					),
					'order' => 'date_created DESC',
				));
				
				if( $currentLoginState && $currentLoginState->type != 1 ) 
				{
					$this->redirect(array('/agent/idle'));
				}
			}
		}
		else
		{
			Yii::app()->user->setFlash('danger', 'Your security group has no permission to access the dialer page.');
			$this->redirect(array('/customer/data/index'));
			exit;
		}
		
		//get sip login info
		if( $authAccount->getIsHostDialer() )
		{
			$sipUsername = $authAccount->customerOfficeStaff->sip_username;
			$sipPassword = $authAccount->customerOfficeStaff->sip_password;
		}
		else
		{
			$sipUsername = $authAccount->sip_username;
			$sipPassword = $authAccount->sip_password;
		}
		
		$sipServer = '107.182.238.147';
		$callerID = $sipUsername;	
		
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
		
		// if( $authAccount->account_type_id == Account::TYPE_HOSTDIAL_AGENT )
		// {
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
		// }
		
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
			
			if( $action == 'nextLead' && $readyForCalling )
			{
				//force skip lead 
				if( isset($_GET['skipCall']) && isset($_GET['lead_id']) )
				{
					$existingleadHopperEntry = LeadHopper::model()->find(array(
						'condition' => 'lead_id = :lead_id AND type !=3',
						'params' => array(
							':lead_id' => $_GET['lead_id'],
						),
					));
					
					if( $existingleadHopperEntry )
					{
						if( $existingleadHopperEntry->delete() )
						{
							$dialerSkipLeadTracker = new DialerSkipLeadTracker;
							
							$dialerSkipLeadTracker->setAttributes(array(
								'agent_account_id' => $authAccount->id,
								'lead_id' => $_GET['lead_id'],
							));
							
							$dialerSkipLeadTracker->save(false);
						}
					}
				}
				
				//check for customer queue without agent and set their dials until reset back to 20
				// CustomerQueueViewer::model()->updateAll(array('dials_until_reset' => 20), 'dials_until_reset > 0 AND dials_until_reset < 20 AND (call_agent IS NULL OR call_agent="")');				
				// CustomerQueueViewer::model()->updateAll(array('dials_until_reset' => 20), 'dials_until_reset < 20 AND (call_agent IS NULL OR call_agent="")');				
				// LeadHopper::model()->updateAll(array('status' => 'DONE'), 'type IN (3,6,7) AND status = "INCALL" AND agent_account_id IS NULL');	
				
				if( isset($_GET['lead_hopper_id']) || isset($_GET['search_lead_id']))
				{
					
					if(!isset($_REQUEST['ajax']))
					{
						//end record for lead wrap time
						$this->recordWrapTime(null, $authAccount->id, 'endOnly');
					}
				}
				
				//start getting a lead from the hopper
				if( isset($_GET['current_lead_id']) )
				{
					//used to maintain the lead history of the current lead
					$existingleadHopperEntry = LeadHopper::model()->find(array(
						'condition' => 'lead_id = :lead_id',
						'params' => array(
							':lead_id' => $_GET['current_lead_id'],
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
					if( isset($_GET['lead_hopper_id']) && !isset($_GET['ajax']) )
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
								':current_lead_hopper_id' => $_GET['lead_hopper_id'],
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
									':current_lead_hopper_id' => $_GET['lead_hopper_id'],
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
									':current_lead_hopper_id' => $_GET['lead_hopper_id'],
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
									':current_lead_hopper_id' => $_GET['lead_hopper_id'],
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
				
				if( isset($_GET['search_lead_id']) && !isset($_GET['ajax']))
				{
					$leadHopperEntry = LeadHopper::model()->find(array(
						'condition' => 'id = :id',
						'params' => array(
							':id' => $_GET['search_lead_id'],
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
						if( $leadHopperEntry->type != LeadHopper::TYPE_LEAD_SEARCH && !isset($_GET['ajax']) )
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
					
					// $list = $lead->list;
					
					$list = Lists::model()->findByPk($leadHopperEntry->list_id);
					
					$calendar_id = isset($_GET['calendar_id']) != null ? $_GET['calendar_id'] : $list->calendar_id;
					
					$calendar = Calendar::model()->findByPk($calendar_id);
					
					$office_id = isset($_GET['office_id']) != null ? $_GET['office_id'] : $calendar->office_id;
					
					$office = CustomerOffice::model()->findByPk($office_id);	
					
					// $customer = Customer::model()->findByPk($calendar->customer_id);
					
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
					else
					{
						if( $authAccount->getIsHostDialer() )
						{
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
						}
						else
						{
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
										}
									}
								}
							}
						}
					}
				}
				else
				{
					Yii::app()->user->setFlash('danger', '<i class="fa fa-warning"></i> There are no more leads in the hopper.');
				}
			}
			
			$page = $authAccount->use_webphone == 1 ? 'webphone' : 'index';

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
			if( $authAccount->use_webphone == 1 && in_array( $authAccount->id, array(4)) ) //Agent 1
			{
				$page = 'webphone_test';
				$callerID = '3155338216';
				
				$enginePriorityWebrtc = 0;
				$enginePriorityNS = 5;
			}
			
			$this->render($page, array(
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
		
		if( $authAccount->getIsHostDialer() )
		{
			$phoneExtension = $authAccount->customerOfficeStaff->sip_username;
		}
		else
		{
			$phoneExtension = $accountUser->phone_extension;
		}
		
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
				$callerID = '';
				
				if( $authAccount->getIsHostDialer() )
				{
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
				}
				else
				{
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
				
				$asteriskParams = array( 
					'call_history_id' => $leadCallHistory->id,
					'agent_extension' => $phoneExtension,
					// 'agent_extension' => '999',
					'caller_id' => $callerID,
					'lead_phone_number' => '81' . preg_replace("/[^0-9]/","", $_POST['lead_phone_number']), 
					// 'lead_phone_number' => '91' . preg_replace("/[^0-9]/","", $_POST['lead_phone_number']), 
					// 'lead_phone_number' => '918005158734', //provo office number
					// 'lead_phone_number' => '918019001203', //sir nathan
					// 'lead_phone_number' => '918042221111', 
				); 

				if( in_array( $authAccount->id, array(5338) ) ) //Hostdialer1 force to call jim
				{
					$asteriskParams['lead_phone_number'] = '814356503300';
				}
				
				$asterisk = new Asterisk;
				
				// if( true )
				if( $asterisk->call($asteriskParams) )
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
					$result['caller_id'] = $callerID;
				}
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
					
					// $asterisk = new Asterisk;
					// $asterisk->hangup($existingChannel->channel);
				}

				$result['status'] = 'success';
				$result['message'] = 'Database has been updated.';
			}
		}
		
		echo json_encode($result);
	}
	
	public function actionHangupListener()
	{
		$authAccount = Yii::app()->user->account;
		
		$result = array(
			'status' => 'success',
			'call_status' => 0,
			'message' => '',
		);
		
		if( isset($_POST['ajax']) && $_POST['call_history_id'] )
		{
			$leadCallHistory = LeadCallHistory::model()->findByPk($_POST['call_history_id']);
			
			if( $leadCallHistory )
			{
				$existingChannel = AsteriskChannel::model()->find(array(
					'condition' => 'call_history_id = :call_history_id',
					'params' => array(
						':call_history_id' => $leadCallHistory->id,
					),
				));
				
				if( $existingChannel )
				{
					$asterisk = new Asterisk;
					$result['call_status'] = $asterisk->getCallStatus($existingChannel);
				}
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
	
	
	public function actionUpdateDisposition()
	{
		$authAccount = Yii::app()->user->account;
		
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => $html,
		);
		
		if( isset($_POST['ajax']) && isset($_POST['value']) && isset($_POST['call_history_id']) )
		{
			$leadCallHistory = LeadCallHistory::model()->findByPk($_POST['call_history_id']);

			$hopperEntry = LeadHopper::model()->find(array(
				'select' => 'type',
				'condition' => 'lead_id = :lead_id AND agent_account_id = :agent_account_id',
				'params' => array(
					':lead_id' => $leadCallHistory->lead_id,
					':agent_account_id' => $authAccount->id,
				),
			));
			
			//confirm, reschedule, no show reschedule
			if( in_array($hopperEntry->type, array(3,6,7)) )
			{
				$disposition = SkillChildDisposition::model()->findByPk($_POST['value']);
			}
			else
			{
				$disposition = SkillDisposition::model()->findByPk($_POST['value']);
			}
			
			if( $disposition && $leadCallHistory )
			{
				$dispositionDetailOptions = array();
				$dispositionDetailOptionsAttributes = array();
				
				//confirm, reschedule, no show reschedule
				if( in_array($hopperEntry->type, array(3,6,7)) )
				{
					$models = SkillChildDispositionDetail::model()->findAll(array(
						'select' => 'id, skill_child_disposition_detail_name, is_required',
						'condition' => 'skill_child_disposition_id = :skill_child_disposition_id',
						'params' => array(
							':skill_child_disposition_id' => $disposition->id,
						),
						'order' => 'skill_child_disposition_detail_name ASC',
					));
					
					if( $models )
					{
						foreach( $models as $model )
						{
							$dispositionDetailOptions[$model->id] = $model->skill_child_disposition_detail_name;
						}
					}
				}
				else
				{
					$models = SkillDispositionDetail::model()->findAll(array(
						'select' => 'id, skill_disposition_detail_name, is_required',
						'condition' => 'skill_disposition_id = :skill_disposition_id',
						'params' => array(
							':skill_disposition_id' => $disposition->id,
						),
						'order' => 'skill_disposition_detail_name ASC',
					));
					
					if( $models )
					{
						foreach( $models as $model )
						{
							$dispositionDetailOptions[$model->id] = $model->skill_disposition_detail_name;
						}
					}
				}
				
				$html .= $this->renderPartial('ajaxDisposition', array(
					'authAccount' => $authAccount,
					'disposition' => $disposition,
					'dispositionDetailOptions' => $dispositionDetailOptions,
					'leadCallHistory' => $leadCallHistory,
				), true);
				
				$result['status'] = 'success';
				$result['html'] = $html;
			}
		}
		
		echo json_encode($result);
	}
	
	
	public function actionSaveDisposition()
	{
		ini_set('error_reporting', E_STRICT);
		
		$authAccount = Yii::app()->user->account;
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => '',
		);
		
		if( isset($_POST['ajax']) && $_POST['call_history_id'] )
		{
			$leadCallHistory = LeadCallHistory::model()->findByPk($_POST['call_history_id']);
			
			if( $leadCallHistory )
			{
				$customer = $leadCallHistory->customer;
				$lead = $leadCallHistory->lead;
				
				// $hopperEntry = LeadHopper::model()->find(array(
					// 'condition' => 'lead_id = :lead_id AND agent_account_id = :agent_account_id',
					// 'params' => array(
						// ':lead_id' => $leadCallHistory->lead_id,
						// ':agent_account_id' => $authAccount->id,
					// ),
				// ));
				
				$hopperEntry = LeadHopper::model()->find(array(
					'condition' => 'lead_id = :lead_id',
					'params' => array(
						':lead_id' => $leadCallHistory->lead_id,
					),
				));
			
			
				$transaction = Yii::app()->db->beginTransaction();
				
				try
				{
					if( $hopperEntry )
					{
						$result['lead_hopper_id'] = $hopperEntry->id;

						$isSkillChild = 0;
						
						//confirm, reschedule, no show reschedule
						if( in_array($hopperEntry->type, array(3,6,7)) )
						{
							$isSkillChild = 1;
						}
						
						//temp fix to avoid the fuel line subject email from going out
						if( isset($_POST['dispo_id']) && $_POST['dispo_id'] == 5 ) 
						{
							$isSkillChild = 1;
						}
						
						//temp fix for deceased email subject
						if( !empty($_POST['dispo_text']) && $_POST['dispo_text'] == 'Appointment Confirmed - Left Message' )
						{
							$isSkillChild = 1;
						}
						
						$leadCallHistory->is_skill_child = $isSkillChild;
						
						if( $leadCallHistory->calendar_appointment_id == null && $hopperEntry->calendar_appointment_id != null )
						{
							$leadCallHistory->calendar_appointment_id = $hopperEntry->calendar_appointment_id;
						}
						
						$leadCallCronProcess = new LeadCallCronProcess;
					
						$leadCallCronProcess->setAttributes(array(
							'lead_id' => $leadCallHistory->lead_id,
							'lead_hopper_id' => $hopperEntry->id,
							'customer_id' => $leadCallHistory->customer_id,
							'agent_account_id' => $authAccount->id,
							'lead_call_history_id' => $leadCallHistory->id,
							'calendar_appointment_id' => $leadCallHistory->calendar_appointment_id,
							'is_skill_child' => $isSkillChild,
							'hopper_type' => $hopperEntry->type,
							'lead_list_id' => $hopperEntry->list_id,
							'lead_timezone' => $hopperEntry->lead_timezone,
							'lead_language' => $hopperEntry->lead_language,
						));
						
						if( !empty($_POST['dispo_id']) )
						{
							$disposition = SkillDisposition::model()->findByPk($_POST['dispo_id']);
							
							$leadCallHistory->disposition_id = $_POST['dispo_id'];
							$leadCallCronProcess->disposition_id = $_POST['dispo_id'];
							
							$hopperEntry->type = LeadHopper::TYPE_CONTACT;
							
							if( $isSkillChild == 1 )
							{
								$leadCallHistory->skill_child_disposition_id = $_POST['dispo_id'];
								
								$disposition = SkillChildDisposition::model()->findByPk($_POST['dispo_id']);
							}
							
							
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
								
								if( $disposition->is_do_not_call == 0 )
								{
									//recyle module
									if( $disposition->is_complete_leads == 1 && !empty($disposition->recycle_interval) )
									{
										$time = strtotime(date("Y-m-d"));
										$finalDate = date("Y-m-d", strtotime("+".($disposition->recycle_interval * 30)." day", $time));
										$lead->recycle_date = $finalDate;
										$lead->recycle_lead_call_history_id = $leadCallHistory->id;
										$lead->recycle_lead_call_history_disposition_id = $leadCallHistory->disposition_id;
									}
								}
								else
								{
									$lead->status = 3;
									$lead->is_do_not_call = 1;
								
									$lead->recycle_date = null;
									$lead->recycle_lead_call_history_id = null;
									$lead->recycle_lead_call_history_disposition_id = null;
									
									$existingDnc = Dnc::model()->find(array(
										'condition' => 'phone_number = :phone_number',
										'params' => array(
											':phone_number' => $leadCallHistory->lead_phone_number,
										),
									));
									
									if( empty($existingDnc) )
									{
										$newDnc = new Dnc;
										
										$newDnc->setAttributes(array(
											'lead_id' => $leadCallHistory->lead_id,
											'skill_id' => $leadCallHistory->list->skill_id,
											'company_id' => $leadCallHistory->company_id,
											'phone_number' => $leadCallHistory->lead_phone_number,
										));
										
										$newDnc->customer_id = $leadCallHistory->customer_id;
										
										$newDnc->save(false);
									}
								}
								
								if( $isSkillChild == 0 && isset($disposition->skill_disposition_name) )
								{
									//add to dc/wn table
									if( in_array($disposition->skill_disposition_name, array('Wrong Number', 'Disconnected Number')) )
									{
										$lead->status = 3;
										$lead->is_bad_number = 1;
								
										$lead->recycle_date = null;
										$lead->recycle_lead_call_history_id = null;
										$lead->recycle_lead_call_history_disposition_id = null;
										
										$existingDcwn = Dcwn::model()->find(array(
											'condition' => 'phone_number = :phone_number',
											'params' => array(
												':phone_number' => $leadCallHistory->lead_phone_number,
											),
										));
										
										if( empty($existingDcwn) )
										{
											$newDcwn = new Dcwn;
											
											$newDcwn->setAttributes(array(
												'lead_id' => $leadCallHistory->lead_id,
												'skill_id' => $leadCallHistory->list->skill_id,
												'company_id' => $leadCallHistory->company_id,
												'phone_number' => $leadCallHistory->lead_phone_number,
											));
											
											$newDcwn->customer_id = $leadCallHistory->customer_id;
											
											$newDcwn->save(false);
										}
									}
									
									//no dial count for skip calls
									if( $disposition->skill_disposition_name == 'Skip Call' )
									{
										$lead->number_of_dials = $lead->number_of_dials - 1;
									}
								}
								
								$lead->save(false);
							}
						
						
							if( !empty($_POST['dispo_text']) )
							{
								$leadCallHistory->disposition = $_POST['dispo_text'];
								$leadCallCronProcess->disposition = $_POST['dispo_text'];
							}
							
							if( !empty($_POST['dispo_detail_text']) )
							{
								$leadCallHistory->disposition_detail = $_POST['dispo_detail_text'];
								$leadCallCronProcess->disposition_detail = $_POST['dispo_detail_text'];
							}
							
							if( !empty($_POST['dispo_detail_id']) )
							{
								$leadCallHistory->disposition_detail_id = $_POST['dispo_detail_id'];
								$leadCallCronProcess->disposition_detail_id = $_POST['dispo_detail_id'];
							}
							
							if( !empty($_POST['phone_type']) )
							{
								$leadCallCronProcess->lead_phone_type = $_POST['phone_type'];
							}
							
							if( !empty($_POST['note']) )
							{
								$leadCallHistory->agent_note = $_POST['note'];
								
								$leadCallCronProcess->note = $_POST['note'];
							}
							
							if( !empty($_POST['callback_date']) && !empty($_POST['callback_time']) )
							{
								$leadCallHistory->callback_time = date('Y-m-d H:i:s', strtotime($_POST['callback_date'].' '.$_POST['callback_time']));
								$leadCallCronProcess->callback_time = $leadCallHistory->callback_time;
							
								if( !empty($customer) && !empty($lead) && !empty($hopperEntry))
								{
									$timeZone = AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $customer->phone) );
									
									if( !empty($lead->timezone) )
										$timeZone = $lead->timezone;
									
									$leadLocalTime = new DateTime($leadCallHistory->callback_time, new DateTimeZone(timezone_name_from_abbr($timeZone)) );

									
									$callBackDateIsValid = true;
									
									$skillScheduleHolder = array();
									
									$customerSkill = CustomerSkill::model()->find(array(
										'select' => 'id, is_custom_call_schedule, skill_id',
										'condition' => 'customer_id = :customer_id AND skill_id = :skill_id',
										'params' => array(
											':customer_id' => $hopperEntry->customer_id,
											':skill_id' => $hopperEntry->skill_id
										),
									));
									
									if( $customerSkill )
									{
										if( $customerSkill->is_custom_call_schedule == 1 )
										{
											$customCallSchedules = CustomerSkillSchedule::model()->findAll(array(
												'select' => 'id, schedule_start, schedule_end',
												'condition' => 'customer_skill_id = :customer_skill_id AND schedule_day = :schedule_day',
												'params' => array(
													':customer_skill_id' => $customerSkill->id,
													':schedule_day' => $leadLocalTime->format('N'),
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
												'select' => 'id, schedule_start, schedule_end',
												'condition' => 'skill_id = :skill_id AND schedule_day = :schedule_day',
												'params' => array(
													'skill_id' => $customerSkill->skill_id,
													':schedule_day' => $leadLocalTime->format('N'),
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
											$callBackDateIsValid = false;

											foreach($skillScheduleHolder[$customer->id] as $sched)
											{	
												if( strtotime($leadLocalTime->format('g:i A')) >= strtotime($sched['schedule_start']) && strtotime($leadLocalTime->format('g:i A')) <= strtotime($sched['schedule_end']) )
												{
													$callBackDateIsValid = true;
												}
											}
										}
									}

									if( !$callBackDateIsValid )
									{
										$result['status'] = 'error';
										$result['message'] = 'Callback date is out of call schedule.';
										
										echo json_encode($result);
										Yii::app()->end();
									}
									
		
									$hopperEntry->callback_date = $leadLocalTime->format('Y-m-d H:i:s');
									$hopperEntry->agent_account_id = null;
									
									if( $isSkillChild == 1 )
									{
										$hopperEntry->type = LeadHopper::TYPE_RESCHEDULE;
										$leadCallCronProcess->hopper_type = $hopperEntry->type;
									}
									else
									{
										$hopperEntry->type = LeadHopper::TYPE_CALLBACK;
										$leadCallCronProcess->hopper_type = $hopperEntry->type;
									}
								}
							}

							
							// echo '<pre>';
								// print_r($_POST);
								// print_r($leadCallHistory->attributes);
								// print_r($hopperEntry->attributes);
								// print_r($leadCallCronProcess->attributes);
							// exit;
							
							if( $leadCallCronProcess->save(false) && $leadCallHistory->save(false) && $hopperEntry->save(false) )
							{
								$result['status'] = 'success';
								$result['message'] = 'Database has been updated.';
								
								if( isset($_POST['dispo_text']) && isset($_POST['lead_phone_number']) && isset($_POST['phone_type']) )
								{
									$html = $this->renderPartial('_lead_history_list_dispo', array(
										'authAccount' => $authAccount,
										'lead' => $lead,
										'disposition' => $_POST['dispo_text'],
										'leadPhoneNumber' => $_POST['lead_phone_number'],
										'phoneType' => $_POST['phone_type'],
									), true);
									
									$result['html'] = $html;
								}
								
								$transaction->commit();
							}
							else
							{
								$result['message'] = 'Database has error. Disposition not submitted.';
							}
						}
						else
						{
							$result['message'] = 'Missing parameter: Disposition not submitted.';
						}
					}
					else
					{
						$result['message'] = 'hopper entry not found.';
					}
				}
				catch(Exception $e)
				{
					$transaction->rollback();
					$result['message'] = 'transaction error: ' .$e;
				}
			}
			else
			{
				$result['message'] = 'call history model not found.';
			}
		}
		else
		{
			$result['message'] = 'Missing parameter: ajax or call_history_id.';
		}
		
		echo json_encode($result);
	}
	
	
	public function actionEditLeadInfo()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
			'field_name' => '',
			'html' => '',
		);
		
		if( isset($_POST['ajax']) && $_POST['lead_id'] )
		{
			$model = Lead::model()->findByPk($_POST['lead_id']);
			
			if( $model )
			{
				if( $_POST['field_name'] == 'language' )
				{
					$model->language = $_POST['field_value'];
					
					if( $model->save(false) )
					{
						$model->office_phone_number = preg_replace("/[^0-9]/","", $model->office_phone_number);
						$model->mobile_phone_number = preg_replace("/[^0-9]/","", $model->mobile_phone_number);
						$model->home_phone_number = preg_replace("/[^0-9]/","", $model->home_phone_number);
						
						$result['status'] = 'success';
						$result['message'] = 'Database has been updated';
						
						$result['updated_field_name'] = $_POST['field_name'];
						$result['updated_values'] = $model->attributes;
						
						echo json_encode($result);
						Yii::app()->end();
					}
				}
				
				if( $_POST['field_name'] == 'timezone' )
				{
					$model->timezone = $_POST['field_value'];
					
					if( $model->save(false) )
					{
						$date = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('America/Chicago'));

						$date->setTimezone(new DateTimeZone( timezone_name_from_abbr($model->timezone) ));
						
						$result['updated_field_name'] = $_POST['field_name'];
						$result['timezone_date_time'] = $date->format('m/d/Y g:i A');
						$result['timezone_date'] = $date->format('m/d/Y');
						$result['timezone_time'] = $date->format('g:i A');
						
						echo json_encode($result);
						Yii::app()->end();
					}
				}
				
				
				if( isset($_POST['Lead']) )
				{
					$model->attributes = $_POST['Lead'];
					
					if( isset($_POST['Lead']['current_phone_type']) && isset($_POST['Lead']['new_phone_type']) &&  $_POST['Lead']['current_phone_type'] != $_POST['Lead']['new_phone_type'] )
					{
						//current values
						if( $_POST['Lead']['current_phone_type'] == 'manual_dial_phone_number' )
						{
							$explodedNewPhoneType = explode('_number', $_POST['Lead']['new_phone_type']);
							$newPhoneField = $explodedNewPhoneType[0];

							$newPhoneLabel = $newPhoneField.'_label';						
							$model->$newPhoneLabel = $_POST['Lead']['manual_dial_phone_label'];
							
							$newPhoneNumber = $newPhoneField.'_number';						
							$model->$newPhoneNumber = $_POST['Lead']['manual_dial_phone_number'];
						}
						else
						{
							$explodedCurrentPhoneType = explode('_number', $_POST['Lead']['current_phone_type']);
							$currentPhoneField = $explodedCurrentPhoneType[0];

							$phoneLabel = $currentPhoneField.'_label';						
							$currentPhoneLabelValue = $model->$phoneLabel;
							$model->$phoneLabel = null;
							
							$phoneNumber = $currentPhoneField.'_number';					
							$currentPhoneNumberValue = $model->$phoneNumber;
							$model->$phoneNumber = null;
							
							$phoneDisposition = $currentPhoneField.'_disposition';					
							$currentDispositionValue = $model->$phoneDisposition;
							$model->$phoneDisposition = null;
							
							$phoneDispositionDetail = $currentPhoneField.'_disposition_detail';					
							$currentDispositionDetailValue = $model->$phoneDispositionDetail;
							$model->$phoneDispositionDetail = null;
							
							$phoneDialCount = $currentPhoneField.'_dial_count';					
							$currentPhoneDialCountValue = $model->$phoneDialCount;
							$model->$phoneDialCount = null;
							
							
							//new values
							$explodedNewPhoneType = explode('_number', $_POST['Lead']['new_phone_type']);
							$newPhoneField = $explodedNewPhoneType[0];

							$newPhoneLabel = $newPhoneField.'_label';						
							$model->$newPhoneLabel = $currentPhoneLabelValue;
							
							$newPhoneNumber = $newPhoneField.'_number';						
							$model->$newPhoneNumber = $currentPhoneNumberValue;
							
							$newPhoneDisposition = $newPhoneField.'_disposition';						
							$model->$newPhoneDisposition = $currentDispositionValue;
							
							$newPhoneDispositionDetail = $newPhoneField.'_disposition_detail';						
							$model->$newPhoneDispositionDetail = $currentDispositionDetailValue;
							
							$newPhoneDialCount = $newPhoneField.'_dial_count';						
							$model->$newPhoneDialCount = $currentPhoneDialCountValue;
						}		
					}
					
					$model->office_phone_number = preg_replace("/[^0-9]/","", $model->office_phone_number);
					$model->mobile_phone_number = preg_replace("/[^0-9]/","", $model->mobile_phone_number);
					$model->home_phone_number = preg_replace("/[^0-9]/","", $model->home_phone_number);
					
					if( $model->save(false) )
					{
						//$model->office_phone_number = !empty($model->office_phone_number) ? "(".substr($model->office_phone_number, 0, 3).") ".substr($model->office_phone_number, 3, 3)."-".substr($model->office_phone_number,6) : '';
						//$model->mobile_phone_number = !empty($model->mobile_phone_number) ? "(".substr($model->mobile_phone_number, 0, 3).") ".substr($model->mobile_phone_number, 3, 3)."-".substr($model->mobile_phone_number,6) : '';
						//$model->home_phone_number = !empty($model->home_phone_number) ? "(".substr($model->home_phone_number, 0, 3).") ".substr($model->home_phone_number, 3, 3)."-".substr($model->home_phone_number,6) : '';
						
						$result['status'] = 'success';
						$result['message'] = 'Database has been updated';
						
						$result['updated_field_name'] = $_POST['field_name'];
						$result['updated_values'] = $model->attributes;
						
						
						if( isset($model->list) )
						{
							$list = $model->list;
							$customer = $list->customer;
							
							$leadHopperEntry = LeadHopper::model()->find(array(
								'condition' => 'lead_id = :lead_id',
								'params' => array(
									':lead_id' => $model->id,
								),
							));
							
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
										);
									}
								}
							
							}
							
							$html = $this->renderPartial('ajaxLeadPhoneNumbers', array(
								'lead' => $model,
								'list' => $list,
								'customer' => $customer,
								'dispositionOptions' => $dispositionOptions,
								'dispositionHtmlOptions' => $dispositionHtmlOptions,
								'isManualDial' => isset($_POST['Lead']['current_phone_type']) && $_POST['Lead']['current_phone_type'] == 'manual_dial_phone_number' ? true : false,
							), true); 
							
							$result['html'] = $html;
						}
						
						echo json_encode($result);
						Yii::app()->end();
					}
				}
	
				$html = $this->renderPartial('ajaxEditLeadInfo', array(
					'model' => $model,
					'fieldName' => $_POST['field_name'],
				), true);
				
				$result['status'] = 'success';
				$result['html'] = $html;
			}
		}
		
		echo json_encode($result);
	}
	
	
	public function actionCreateLeadHistory()
	{
		$authAccount = Yii::app()->user->account;
		
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		
		if( isset($_POST['LeadHistory']) )
		{
			$leadHistory = new LeadHistory;
			
			$leadHistory->attributes = $_POST['LeadHistory'];
			
			$leadHistory->agent_account_id = $authAccount->id;
			
			if( $leadHistory->save(false) )
			{
				$result['status'] = 'success';
				
				$data = LeadHistory::model()->find(array(
					'condition' => 'lead_id = :lead_id AND type NOT IN(9) AND status=1',
					'params' => array(
						':lead_id' => $leadHistory->lead_id
					),
					'order' => 'date_created DESC',
				));
				
				if( $data )
				{
					$html = $this->renderPartial('_lead_history_list', array(
						'data' => $data,
					), true);
					
					$result['html'] = $html;
				}
				
			}
		}
		
		echo json_encode($result);
	}

	
	public function actionEmailDirections()
	{
		$result = array(
			'status' => '',
			'message' => '',
		);
		
		$lead = Lead::model()->findByPk($_GET['lead_id']);
		$office = CustomerOffice::model()->findByPk($_GET['office_id']);

		if( $lead )
		{
			if( filter_var($lead->email_address, FILTER_VALIDATE_EMAIL) ) 
			{	
				//Send Invoice Email
				Yii::import('application.extensions.phpmailer.JPhpMailer');
			
				$mail = new JPhpMailer;
					
				$mail->Host = "64.251.10.115";
				
				$mail->IsSMTP(); 																						
				$mail->SMTPAuth = true;					
				$mail->SMTPSecure = "tls";  
				$mail->Port = 587;      					
				$mail->Username = "service@engagex.com";  					
				$mail->Password = "Engagex123";       
						
				$mail->SetFrom = 'service@engagex.com';
				
				$mail->Subject = 'Directions';

				$MsgHTML = '<p>Directions: </p>';
				
				$MsgHTML .= $_POST['direction'];
				
				$MsgHTML .= '<p>'.CHtml::link('Click to view the map with directions', Yii::app()->createAbsoluteUrl('map/view', array('lead_id'=>$lead->id, 'office_id'=>$office->id))).'</p>';
				
				$mail->MsgHTML( $MsgHTML );
				
				$mail->AddAddress($lead->email_address);
				
				if( $mail->Send() )
				{
					$result['status'] = 'success';
					$result['message'] = 'Email was successfully sent.';
				}
			}
			else
			{
				$result['message'] = 'Invalid lead email address.';
			}
		}
		
		echo json_encode($result);
	}

	
	public function actionLeadSearch()
	{
		$authAccount = Yii::app()->user->account;
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => '',
		);
	
		$assignedCustomerIds = array();
		
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
			$result['message'] = 'No customer assigned.';
		}
		
		if( isset($_POST['lead_search_query']) && !empty($assignedCustomerIds) )
		{
			$renderPage = '_lead_search_list2';
			
			$models = LeadCustomData::model()->findAll(array(
				'with' => 'lead',
				'condition' => '
					lead.id IS NOT NULL
					AND lead.type = 1 
					AND lead.status != 4
					AND t.lead_id IS NOT NULL 
					AND t.field_name ="Member Number" 
					AND t.value = :search_query
					AND (lead.customer_id IN ("'.implode('","', $assignedCustomerIds).'"))
				',
				'params' => array(
					':search_query' => $_POST['lead_search_query'],
				),
			));
			
			if( !$models )
			{
				$models = Lead::model()->findAll(array(
					'condition' => '
						t.type=1 
						AND t.status !=4
						AND 
						(
							t.office_phone_number LIKE :search_query OR 
							t.mobile_phone_number LIKE :search_query OR 
							t.home_phone_number LIKE :search_query OR 
							t.first_name LIKE :search_query OR
							t.last_name LIKE :search_query OR
							CONCAT(t.first_name , " " , t.last_name) LIKE :search_query OR
							t.email_address LIKE :search_query
						)
						AND (t.customer_id IN ("'.implode('","', $assignedCustomerIds).'"))
					',
					'params' => array(
						':search_query' => $_POST['lead_search_query'].'%',
					),
					'limit' => 25,
				));
				
				$renderPage = '_lead_search_list';
			}
			
			if( $models )
			{
				$html = $this->renderPartial($renderPage, array(
					'models' => $models,
				), true); 
				
				$result['status'] = 'success';
				$result['html'] = $html;
			}
			else
			{
				$result['message'] = 'Lead not found.';
			}
		}
		
		if( isset($_POST['ajax']) )
		{
			$models = Lead::model()->findAll(array(
				'condition' => 'type=1',
				'limit' => 50,
			));
			
			$html = $this->renderPartial('ajaxLeadSearch', array(
				'models' => $models,
			), true); 
			
			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}

	public function getLeadCallingTime($leadModel)
	{
		$lead = $leadModel;
		
		$debug = false;
		
		$date = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('America/Chicago'));
		$date->setTimezone(new DateTimeZone('America/Denver')); 
		$americaDenverTime = $date->format('g:i A');
		
		if($debug){
			echo 'Server Time: '. $date->format('g:i A (e)');
			echo '<br>';
		}
		
		
		
		if(!empty($customer) && !empty($lead) && !empty($lead->list) )
		{
			$customer = $lead->list->customer;
			
			$timeZone = AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $customer->phone) );
			
			if($debug)
			{
				echo 'Customer Timezone: '.$timeZone;
				echo '<Br>';
			}
			
			if( !empty($lead->timezone) )
			{
				$timeZone = $lead->timezone;
				
				if($debug){
					echo 'Lead Timezone: '.$timeZone;
					echo '<Br>';
				}
			}
			
			// $leadLocalTime = new DateTime( $date->format('Y-m-d H:i:s'), new DateTimeZone(timezone_name_from_abbr($timeZone)) );
			// $leadLocalTime = new DateTime( $date->format('Y-m-d H:i:s'), new DateTimeZone('Asia/Manila') );
			$leadLocalTime = $date->setTimezone(new DateTimeZone(timezone_name_from_abbr($timeZone)));
					
			echo 'Lead Local Time: '. $leadLocalTime->format('g:i a');
			echo '<br>';
			
			$criteria = new CDbCriteria;
			$criteria->compare('lead_id', $lead->id);
			$criteria->compare('type', LeadHopper::TYPE_CALLBACK);
			$leadHopper = LeadHopper::model()->find($criteria);
			
			// var_dump($lead->id);
			if($leadHopper !== null)
			{
				echo 'Callback Date: '. date('M d, Y g:i a',strtotime($leadHopper->callback_date));
				echo '<br>';
			}
			
			$callingTimeAvailable = false;
			
			$skillScheduleHolder = array();
			
			$customerSkill = CustomerSkill::model()->find(array(
				'condition' => 'customer_id = :customer_id AND skill_id = :skill_id',
				'params' => array(
					':customer_id' => $customer->id,
					':skill_id' => $lead->list->skill_id
				),
			));
			
			if( $customerSkill )
			{
				if( $customerSkill->is_custom_call_schedule == 1 )
				{
					$customCallSchedules = CustomerSkillSchedule::model()->findAll(array(
						'condition' => 'customer_skill_id = :customer_skill_id AND schedule_day = :schedule_day',
						'params' => array(
							':customer_skill_id' => $customerSkill->id,
							':schedule_day' => $leadLocalTime->format('N'),
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
						'condition' => 'skill_id = :skill_id AND schedule_day = :schedule_day',
						'params' => array(
							'skill_id' => $customerSkill->skill_id,
							':schedule_day' => $leadLocalTime->format('N'),
						),
					));

					foreach($skillSchedules as $skillSchedule)
					{
						$skillScheduleHolder[$customer->id][$skillSchedule->id]['schedule_start'] = date('g:i A', strtotime($skillSchedule->schedule_start));
						$skillScheduleHolder[$customer->id][$skillSchedule->id]['schedule_end'] = date('g:i A', strtotime($skillSchedule->schedule_end));
					}
				}
				
				$customerSched = 'Not Set';
				if( isset($skillScheduleHolder[$customer->id]) )
				{	
					
					foreach($skillScheduleHolder[$customer->id] as $sched)
					{	
						if( strtotime($leadLocalTime->format('g:i A')) >= strtotime($sched['schedule_start']) && strtotime($leadLocalTime->format('g:i A')) <= strtotime($sched['schedule_end']) )
						{
							$callingTimeAvailable = true;
						}
						
						$customerSched = $sched['schedule_start'].'-'.$sched['schedule_end'].'<br>';
					}
				}
				
				echo 'Call Sched: '.$customerSched;
				
				##callback code
				if($leadHopper !== null)
				{
					$callBackTime = date('g:i A',strtotime($leadHopper->callback_date));
					
					if( (strtotime($callBackTime) < strtotime($americaDenverTime)))
					{
						$callingTimeAvailable = false;
						echo '<b>Pending Callback</b><br>';
					}
				}
				else
				{
					
					##retry time code
					$callingTimeAvailable && $this->checkLeadRetryTime($lead);
				
					if($callingTimeAvailable)
					{
						echo '<b>Now</b><br>';
					}
					else
					{
						echo '<b>Next Shift</b><br>';
					}
				}
				
				
			}
			else
			{
				echo '<b>Customer Skill not set.</b><br>';
			}
		}
		
		## display the prompt and emailing when the Agent is assigned into a child skill
		$asca = AccountSkillChildAssigned::model()->find(array(
			'condition' => 'account_id = :account_id',
			'params' => array(
				':account_id' => Yii::app()->user->account->id,
			),
		));
		
		if(!empty($asca))
			return 'Now';
		
		
		if(!empty($callingTimeAvailable))
		{
			return 'Now';
		}
		else
		{
			return 'Next Shift';
		}
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
	
	public function actionLoadLeadToHopper()
	{
		$authAccount = Yii::app()->user->account;
		$date = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('America/Denver'));
		
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		if( isset($_POST['ajax']) && isset($_POST['id']) && isset($_POST['type']) )
		{
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
					$hopperEntry = $existingHopperEntry;
				}
				else
				{
					$hopperEntry = new LeadHopper;										
				}

				$hopperEntry->setAttributes(array(
					'lead_id' => $lead->id,
					'list_id' => $lead->list->id,
					'skill_id' => $lead->list->skill_id,
					'customer_id' => $lead->list->customer_id,
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
					$hopperEntry->skill_child_confirmation_id = $skillChildConfirmation->id;
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
					$hopperEntry->skill_child_reschedule_id = $skillChildReschedule->id;
				}
				
				if( $hopperEntry->save(false) )
				{			
					$result['status'] = 'success';	
					$result['search_lead_id'] = $hopperEntry->id;	
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
				
				if(! $mail->Send() )
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

	
	public function actionManualDial()
	{
		$authAccount = Yii::app()->user->account;
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => '',
			'call_history_id' => '',
		);
		
		if( isset($_POST['ajax']) )
		{
			$lead = Lead::model()->findByPk($_POST['lead_id']);
			
			if( $lead )
			{
				if( $lead->home_phone_number != $_POST['dialed_phone_number'] && $lead->mobile_phone_number != $_POST['dialed_phone_number'] && $lead->office_phone_number != $_POST['dialed_phone_number'] )
				{
					$dialNumber = 1;
					
					$existingLeadCallHistory = LeadCallHistory::model()->find(array(
						'condition' => 'lead_id = :lead_id AND list_id = :list_id',
						'params' => array(
							':lead_id' => $lead->id,
							':list_id' => $_POST['list_id'],
						),
					));
					
					if( $existingLeadCallHistory )
					{
						$dialNumber = $existingLeadCallHistory->dial_number + 1;
					}
					
					$customerSkill = CustomerSkill::model()->find(array(
						'condition' => 'customer_id = :customer_id AND skill_id = :skill_id',
						'params' => array(
							':customer_id' => $_POST['customer_id'],
							':skill_id' => $_POST['skill_id'],
						),
					));
				
					$leadCallHistory = new LeadCallHistory;
			
					$leadCallHistory->setAttributes(array(
						'lead_id' => $lead->id, 
						'list_id' => $_POST['list_id'], 
						'customer_id' => $_POST['customer_id'], 
						'company_id' => $_POST['company_id'], 
						'contract_id' => $customerSkill->contract_id,
						'agent_account_id' => $authAccount->id, 
						'dial_number' => $dialNumber,
						'lead_phone_number' => preg_replace("/[^0-9]/","", $_POST['dialed_phone_number']), 
						'start_call_time' => date('Y-m-d H:i:s'),
					));
					
					if( $leadCallHistory->save(false) )
					{					
						$dispositionHtmlOptions = array('options'=>array());
						
						$dispositions = SkillDisposition::model()->findAll(array(
							'condition' => 'skill_id = :skill_id',
							'params' => array(
								':skill_id' => $_POST['skill_id'],
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
						
						$html = $this->renderPartial('ajaxManualDialPhoneNumber', array(
							'lead' => $lead,
							'dispositionOptions' => $dispositionOptions,
							'dispositionHtmlOptions' => $dispositionHtmlOptions,
							'leadCallHistory' => $leadCallHistory,
							'list_id' => $_POST['list_id'],
							'customer_id' => $_POST['customer_id'],
							'company_id' => $_POST['company_id'],
							'dialed_phone_number' => $_POST['dialed_phone_number'],				
						), true);
						
						
						$result['status'] = 'success';
						$result['html'] = $html;
						$result['call_history_id'] = $leadCallHistory->id;
					}
				}
			}
		}
		
		echo json_encode($result);
	}

	
	public function actionAjaxUpdateCalendarOptions()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
			'calendar_html' => '',
			'office_html' => '',
			'first_calendar_id' => '',
		);
		
		if( isset($_POST['ajax']) && isset($_POST['office_id']) && isset($_POST['current_lead_id']) )
		{	
			$calendarOptions = array();
			
			$office = CustomerOffice::model()->findByPk($_POST['office_id']);	
			$lead = Lead::model()->findByPk($_POST['current_lead_id']);	

			
			if( $office && $lead )
			{		
				$customer = $office->customer;
				
				$offices = CustomerOffice::model()->findAll(array(
					'condition' => 'customer_id = :customer_id AND status=1 AND is_deleted=0',
					'params' => array(
						':customer_id' => $customer->id,
					),
				));

				$officeOptions = CHtml::listData( $offices, 'id', 'office_name');
				
				$officeHtml = $this->renderPartial('ajaxOfficeInfo', array(
					'customer' => $customer,
					'office' => $office,
					'officeOptions' => $officeOptions,
				), true);
				
				$result['office_html'] = $officeHtml;
				
				
				$calendars = Calendar::model()->findAll(array(
					'condition' => 'office_id = :office_id AND status=1',
					'params' => array(
						':office_id' => $office->id
					),
				));

				if( $calendars )
				{
					$firstCalendarId = null;

					$calendarOptions = CHtml::listData( $calendars, 'id', 'name');
					
					foreach( $calendars as $calendar )
					{
						if( $firstCalendarId == null )
						{
							$firstCalendarId = $calendar->id;
							
							$calendarHtml = $this->renderPartial('ajaxCalendarInfo', array(
								'lead' => $lead,
								'customer' => $customer,
								'office' => $office,
								'calendar' => $calendar,
								'officeOptions' => $officeOptions,
								'calendarOptions' => $calendarOptions,
							), true);
							
							$result['calendar_html'] = $calendarHtml;
							
							$result['first_calendar_id'] = $firstCalendarId;
						}
					}
					
					$result['status'] = 'success';
				}
			}
	
		}
		
		echo json_encode($result);
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

	
	public function actionLoadAgentStats()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => '',
		);
		
		if( isset($_POST['ajax']) )
		{
			$html = $this->renderPartial('ajaxAgentStats', array(), true); 
			
			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}

	public function actionLoadDataTab()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => '',
		);
		
		if( isset($_POST['ajax']) && isset($_POST['lead_id']) )
		{
			$lead = Lead::model()->findByPk($_POST['lead_id']);
			
			$html = $this->renderPartial('dataTab', array('lead'=>$lead), true); 
			
			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}
	
	public function actionUpdateDataTab()
	{
		$result = array(
			'status' => 'error',
			'message' => 'Database Error.',
			'html' => '',
		);
		
		if( isset($_POST['ajax']) && isset($_POST['updateLeadCustomDatas']) )
		{
			$lead = Lead::model()->findByPk($_POST['lead_id']);
			
			if( $lead )
			{
				$defaultLeadColumns = array(
					'Last Name',
					'First Name',
					'Partner First Name',
					'Partner Last Name',
					'Address 1',
					'Address 2',
					'City',
					'State',
					'Zip',
					'Office Phone',
					'Mobile Phone',
					'Home Phone',
					'Email Address',
				);
				
				foreach( $_POST['updateLeadCustomDatas'] as $updateLeadCustomDataKey => $updateLeadCustomDataValue )
				{
					if( in_array($updateLeadCustomDataKey, $defaultLeadColumns) )
					{
						if( $updateLeadCustomDataKey == 'Last Name' )
						{
							$lead->last_name = $updateLeadCustomDataValue;
						}
						
						if( $updateLeadCustomDataKey == 'First Name' )
						{
							$lead->first_name = $updateLeadCustomDataValue;
						}
						
						if( $updateLeadCustomDataKey == 'Partner First Name' )
						{
							$lead->partner_first_name = $updateLeadCustomDataValue;
						}
						
						if( $updateLeadCustomDataKey == 'Partner Last Name' )
						{
							$lead->partner_last_name = $updateLeadCustomDataValue;
						}
						
						if( $updateLeadCustomDataKey == 'Address 1' )
						{
							$lead->address = $updateLeadCustomDataValue;
						}
						
						if( $updateLeadCustomDataKey == 'Address 2' )
						{
							$lead->address2 = $updateLeadCustomDataValue;
						}
						
						if( $updateLeadCustomDataKey == 'City' )
						{
							$lead->city = $updateLeadCustomDataValue;
						}
						
						if( $updateLeadCustomDataKey == 'State' )
						{
							$lead->state = $updateLeadCustomDataValue;
						}
						
						if( $updateLeadCustomDataKey == 'Zip' )
						{
							$lead->zip_code = $updateLeadCustomDataValue;
						}
						
						if( $updateLeadCustomDataKey == 'Office Phone' )
						{
							$lead->office_phone_number = $updateLeadCustomDataValue;
						}
						
						if( $updateLeadCustomDataKey == 'Mobile Phone' )
						{
							$lead->mobile_phone_number = $updateLeadCustomDataValue;
						}
						
						if( $updateLeadCustomDataKey == 'Home Phone' )
						{
							$lead->home_phone_number = $updateLeadCustomDataValue;
						}
						
						if( $updateLeadCustomDataKey == 'Email Address' )
						{
							$lead->email_address = $updateLeadCustomDataValue;
						}
						
						$lead->save(false);
					}
					else
					{				
						$updateLeadCustomDataModel = LeadCustomData::model()->find(array(
							'condition' => 'lead_id = :lead_id AND field_name = :field_name',
							'params' => array(
								':lead_id' => $lead->id,
								':field_name' => $updateLeadCustomDataKey
							),
						));
						
						
						if( $updateLeadCustomDataModel )
						{
							$updateLeadCustomDataModel->value = $updateLeadCustomDataValue;
							
							$updateLeadCustomDataModel->save(false);
						}
					}
				}
				
				$result['status'] = 'success';
				$result['message'] = 'Data tab was updated successfully.';
			}
		}
		
		echo json_encode($result);
	}

	public function actionLoadSurveyTab()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => '',
		);
		
		if( isset($_POST['ajax']) && isset($_POST['lead_id']) )
		{
			$lead = Lead::model()->findByPk($_POST['lead_id']);
			
			$html = $this->renderPartial('_surveyTab', array(
				'lead' => $lead,
				'list' => $lead->list,
				'customer' => $lead->customer
			), true, true); 
			
			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}
	
	public function actionLoadScriptTab()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => '',
		);
		
		if( isset($_POST['ajax']) && isset($_POST['lead_id']) )
		{
			$leadHopperEntry = LeadHopper::model()->find(array(
				'condition' => 'lead_id = :lead_id',
				'params' => array(
					':lead_id' => $_POST['lead_id']
				),
			));
			
			$html = $this->renderPartial('scriptTab', array('leadHopperEntry'=>$leadHopperEntry), true); 
			
			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}
	
	public function actionLoadAppointmentsTab()
	{
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => $html
		);
		
		if( isset($_POST['ajax']) && $_POST['lead_id'] )
		{
			$lead = Lead::model()->findByPk($_POST['lead_id']);
			$list = $lead->list;
			$customer = $lead->customer;
			$calendar = Calendar::model()->findByPk( $list->calendar_id );
			$office = CustomerOffice::model()->findByPk( $calendar->office_id );	
			
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
			
			$html .= $this->renderPartial('ajaxAppointmentsTab', array(
				'lead' => $lead,
				'list' => $list,
				'customer' => $customer,
				'calendar' => $calendar,
				'office' => $office,
				'calendarOptions' => $calendarOptions,
			), true, true); 
			
			$result['html'] = $html;
			$result['status'] = 'success';
		}
		
		echo json_encode($result);
	}
	
	public function actionLoadMapTab()
	{
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => $html
		);
		
		if( isset($_POST['ajax']) && $_POST['lead_id'] )
		{
			$lead = Lead::model()->findByPk($_POST['lead_id']);
			$list = $lead->list;
			$calendar = Calendar::model()->findByPk( $list->calendar_id );
			$office = CustomerOffice::model()->findByPk( $calendar->office_id );	

			$html .= $this->renderPartial('ajaxMapTab', array(
				'lead' => $lead,
				'office' => $office,
			), true, true); 
			
			$result['html'] = $html;
			$result['status'] = 'success';
		}
		
		echo json_encode($result);
	}
	
	public function actionAjaxLoadCustomData()
	{
		$result = array(
			'status' => '',
			'message' => '',
			'html' => '',
		);
		
		if( isset($_POST['ajax']) && isset($_POST['lead_id']) && isset($_POST['list_id']) )
		{
			$model = Lead::model()->FindByPk($_POST['lead_id']);
			
			if( $_POST['list_id'] == $model->list_id )
			{
				$page = 'ajaxCustomDataTableForm';
				
				$html = $this->renderPartial($page, array(
					'lead' => $model,
				), true);
			}
			else
			{
				$page = 'ajaxCustomDataTable';
				
				$listCustomDatas = ListCustomData::model()->findAll(array(
					'condition' => 'list_id = :list_id AND display_on_form=1 AND status=1',
					'params' => array(
						':list_id' => $_POST['list_id']
					),
					'order' => 'ordering ASC',
				));
				
				$html = $this->renderPartial($page, array(
					'listCustomDatas' => $listCustomDatas,
					'leadId' => $_POST['lead_id'],
					'listId' => $_POST['list_id'],
				), true);
			}
			
			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}

	public function actionExportAgentCallLog()
	{
		ini_set('memory_limit', '2048M');
		set_time_limit(0); 

		$authAccount = Yii::app()->user->account;
	
		$latestCallsSql = "
			SELECT c.`firstname` as customer_first_name, c.`lastname` as customer_last_name, l.`first_name` as lead_first_name, l.`last_name` as lead_last_name, lch.`lead_phone_number`, lch.`disposition`, lch.`start_call_time`
			FROM ud_lead_call_history lch
			LEFT JOIN ud_customer c ON c.`id` = lch.`customer_id`
			LEFT JOIN ud_lead l ON l.`id` = lch.`lead_id`
			WHERE lch.status !=4
			AND lch.`agent_account_id` = '".$authAccount->id."'
			AND lch.date_created >= '".date('Y-m-d 00:00:00', strtotime('today'))."' 
			AND lch.date_created <= '".date('Y-m-d 23:59:59', strtotime('today'))."' 
			ORDER BY lch.`start_call_time` DESC
		";
		
		$connection = Yii::app()->db;
		$command = $connection->createCommand($latestCallsSql);
		$latestCalls = $command->queryAll();
		
		// unregister Yii's autoloader
		spl_autoload_unregister(array('YiiBase', 'autoload'));
		
		// register PHPExcel's autoloader ... PHPExcel.php will do it
		$phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
		require_once($phpExcelPath . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'PHPExcel.php');
		
		// register Yii's autoloader again
		spl_autoload_register(array('YiiBase', 'autoload'));
		 
		// This requires Yii's autoloader
		
		$objPHPExcel = new PHPExcel();
		
		$ctr = 1;

		$headers = array(
			'A' => 'Customer First',
			'B' => 'Customer Last',
			'C' => 'Lead First Name',
			'D' => 'Lead Last Name',
			'E' => 'Lead Phone Number',
			'F' => 'Disposition',
			'G' => 'Call Date/Time',
		);
		
		foreach($headers as $column => $val)
		{		
			$objPHPExcel->getActiveSheet()->SetCellValue($column.$ctr, $val);
		}
		
		$ctr = 2;
		
		if( $latestCalls )
		{
			foreach( $latestCalls as $latestCall )
			{
				$callDate = new DateTime($latestCall['start_call_time'], new DateTimeZone('America/Chicago'));
				$callDate->setTimezone(new DateTimeZone('America/Denver'));	
				
				$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $latestCall['customer_first_name'] );
				
				$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $latestCall['customer_last_name'] );
				
				$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $latestCall['lead_first_name'] );
				
				$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $latestCall['lead_last_name'] );
				
				$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $latestCall['lead_phone_number'] );
				
				$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $latestCall['disposition'] );
				
				$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $callDate->format('m/d/Y g:i A') );
				
				$ctr++;
			}
		}
		
		$webroot = Yii::getPathOfAlias('webroot');
		$folder =  $webroot . DIRECTORY_SEPARATOR . 'agent_daily_call_logs' . DIRECTORY_SEPARATOR;
		
		if( $authAccount->getIsHostDialer() )
		{
			$filename = $authAccount->customerOfficeStaff->staff_name;
			$filenamePath = $folder.$filename.'.xlsx';
		}
		else
		{
			$filename = $authAccount->accountUser->first_name.' '.$authAccount->accountUser->last_name;
			$filenamePath = $folder.$filename.'.xlsx';
		}
		
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save($folder. DIRECTORY_SEPARATOR .$filename.'.xlsx');
		
		//Send Invoice Email
		Yii::import('application.extensions.phpmailer.JPhpMailer');

		$mail = new JPhpMailer;
		$mail->SMTPAuth = true;		
		$mail->SMTPSecure = 'tls';   		
		$mail->SMTPDebug = 2; 
		$mail->Port = 25;      
		$mail->Host = 'mail.engagex.com';	
		$mail->Username = 'service@engagex.com';  
		$mail->Password = "_T*8c>ja";            											

		$mail->SetFrom('service@engagex.com', 'Engagex Service');
		
		$mail->Subject = 'Agent Call Log';
		
		$mail->AddAddress( $authAccount->email_address );

		$mail->AddBCC('erwin.datu@engagex.com');
		 
		$mail->MsgHTML('Agent Call Log');
		
		$mail->AddAttachment($filenamePath,$filename.'.xlsx');	
		
		$mail->Send();
	}
}