<?php

class DefaultController extends Controller
{
	public $layout='//layouts/agent_dialer';
		
	public function actionIndex($action='')
	{
		$authAccount = Yii::app()->user->account;

		if( $authAccount->account_type_id != Account::TYPE_AGENT )
		{
			Yii::app()->user->setFlash('danger', 'You are not authorized to access the dialer page.');
			$this->redirect(array('/customer/data/index'));
			exit;
		}
		
		$assignedSkillIds = array();

		$assignedSkills = AccountSkillAssigned::model()->findAll(array(
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
		
		$leadHopperEntry = null;
		
		if( $assignedSkillIds )
		{
			$lead = null;
			$leadHistoryDataProvider = null;
			$list = null;
			$calendar = null;
			$customer = null;
			$office = null;
			$officeOptions = array();
			$calendarOptions = array();
			$dispositionOptions = array();
			$dispositionHtmlOptions = array('options'=>array());

			$customerInHopperCount = LeadHopper::model()->count(array(
				'group' => 'customer_id',
			));
			
			if( $action == 'nextLead' )
			{
				//check for customer queue without agent and set their dials until reset back to 20
				CustomerQueueViewer::model()->updateAll(array('dials_until_reset' => 20), 'dials_until_reset < 20 AND (call_agent IS NULL OR call_agent="")');				
				
				
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
					$existingleadHopperEntry = LeadHopper::model()->find(array(
						'condition' => 'customer.status = 1 AND list.status = 1 AND t.agent_account_id = :agent_account_id AND t.type != :lead_search AND ( (t.status IN ("READY", "INCALL", "HOLD", "DISPO") AND t.type=1) OR (t.status="READY" AND t.type IN (2,3,5)) ) AND t.skill_id IN ('.implode(', ', $assignedSkillIds).')',
						'params' => array(
							':agent_account_id' => $authAccount->id,
							':lead_search' => LeadHopper::TYPE_LEAD_SEARCH,
						),
						'with'=>array('customer','list'),
					));	
				}
				
				if( $existingleadHopperEntry )
				{
					if( isset($_GET['lead_hopper_id']) && !isset($_GET['ajax']) )
					{
						$leadHopperEntry = LeadHopper::model()->find(array(
							'condition' => 't.id != :current_lead_hopper_id AND customer.status = 1 AND list.status = 1 AND t.customer_id = :customer_id AND agent_account_id = :agent_account_id AND ( (t.status IN ("READY", "INCALL", "HOLD", "DISPO") AND t.type=1) OR (t.status="READY" AND t.type IN (2,3,5)) ) AND t.skill_id IN ('.implode(', ', $assignedSkillIds).')',
							'params' => array(
								':current_lead_hopper_id' => $_GET['lead_hopper_id'],
								':customer_id' => $existingleadHopperEntry->customer_id,
								':agent_account_id' => $authAccount->id,
							),
							'with'=>array('customer','list'),
						));
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
					
					if( $customerInHopperCount >= 2 )
					{						
						$addedCondition .= ' AND agent_account_id IS NULL';
					}	
					
					// $leadHopperEntry = LeadHopper::model()->find(array(
						// 'condition' => 'customer.status = 1 AND list.status = 1 AND ( (t.status IN ("READY", "INCALL", "HOLD", "DISPO") AND t.type=1) OR (t.status="READY" AND t.type IN (2,3,5)) ) AND t.skill_id IN ('.implode(', ', $assignedSkillIds).')' . $addedCondition,
						// 'with'=>array('customer','list'),
						// 'order' => 't.id ASC',
					// ));	
					
					// $leadHopperEntry = LeadHopper::model()->find(array(
						// 'condition' => 'customer.status = 1 AND ( (t.status IN ("READY", "INCALL", "HOLD", "DISPO") AND t.type=1) OR (t.status="READY" AND t.type IN (2,3,5)) ) AND t.skill_id IN ('.implode(', ', $assignedSkillIds).')' . $addedCondition,
						// 'with'=>array('customer','list'),
						// 'order' => 't.id ASC',
					// ));	
				}
				
				$leadHopperEntry = LeadHopper::model()->findByPk(325);

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
							
							if( $currentAssignedToAgent >= 1 )
							{
								// LeadHopper::model()->updateAll(array('agent_account_id' => null ), 'agent_account_id = ' . $authAccount->id);
								
								if( $customerInHopperCount >= 2 )
								{
									LeadHopper::model()->updateAll(array('agent_account_id' => $authAccount->id), 'customer_id = ' . $leadHopperEntry->customer_id);
								}
								
								// CustomerQueueViewer::model()->updateAll(array('call_agent' => $authAccount->id), 'customer_id = ' . $leadHopperEntry->customer_id);
							}
						}
					}
				}
				

				//get lead details
				if( $lead != null )
				{
					$leadHistories = LeadHistory::model()->findAll(array(
						'condition' => 'lead_id = :lead_id',
						'params' => array(
							':lead_id' => $lead->id,
						), 
						'order' => 'date_created DESC',
					));

					$leadHistoryDataProvider = new CArrayDataProvider($leadHistories);
					
					$list = $lead->list;
					
					$calendar_id = isset($_GET['calendar_id']) != null ? $_GET['calendar_id'] : $list->calendar_id;
					
					$calendar = Calendar::model()->findByPk($calendar_id);
					
					$office_id = isset($_GET['office_id']) != null ? $_GET['office_id'] : $calendar->office_id;
					
					$office = CustomerOffice::model()->findByPk($office_id);	
					
					$customer = Customer::model()->findByPk($calendar->customer_id);

					if( $office )
					{
						$calendars = Calendar::model()->findAll(array(
							'condition' => 'office_id = :office_id',
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
						'condition' => 'customer_id = :customer_id',
						'params' => array(
							':customer_id' => $customer->id
						),
					));

					$officeOptions = CHtml::listData( $offices, 'id', 'office_name');
														
					#change disposition list from Parent Skill to (Child Skill Disposition find TYPE_CONFIRM)
					#when... 
					# 1. LeadHopper = TYPE_CONFIRMATION_CALL;
					
					if($leadHopperEntry->type == LeadHopper::TYPE_CONFIRMATION_CALL)
					{
						$dispositionOptions = array();
						
						$skillChild = SkillChild::model()->find(array(
							'condition' => 'skill_id = :skill_id',
							'params' => array(
								':skill_id' => $list->skill_id,
							),
						));
						
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
				}
				else
				{
					Yii::app()->user->setFlash('danger', '<i class="fa fa-warning"></i> There are no more leads in the hopper.');
				}
			}

			// echo '<pre>';
			// print_r($leadHopperEntry->attributes);
			// exit;
			
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
		
		$result = array(
			'status' => 'success',
			'message' => '',
			'call_history_id' => '',
		);
		
		if( isset($_POST['ajax']) )
		{
			$dialNumber = 1;
			
			$existingLeadCallHistory = LeadCallHistory::model()->find(array(
				'condition' => 'lead_id = :lead_id AND list_id = :list_id',
				'params' => array(
					':lead_id' => $_POST['lead_id'],
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
					$customerQueueViewer->dials_until_reset = $customerQueueViewer->dials_until_reset - 1;
					$customerQueueViewer->save(false);
				}
				
				
				if( isset($leadCallHistory->lead) )
				{
					$lead = $leadCallHistory->lead;

					$field = $_POST['phone_type'].'_phone_dial_count';
					
					$lead->$field = $lead->$field + 1;
					
					$lead->number_of_dials = $lead->number_of_dials + 1;
					
					if( $lead->number_of_dials == ($customerSkill->skill->max_dials * 3 ) ) 
					{
						$lead->status = 3;
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
			$disposition = SkillDisposition::model()->findByPk($_POST['value']);
			
			$leadCallHistory = LeadCallHistory::model()->findByPk($_POST['call_history_id']);
			
			if( $disposition && $leadCallHistory )
			{
				$dispositionDetailOptions = array();
				
				$models = SkillDispositionDetail::model()->findAll(array(
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
				
				$html .= $this->renderPartial('ajaxDisposition', array(
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
		$authAccount = Yii::app()->user->account;
		
		$result = array(
			'status' => 'success',
			'message' => '',
		);
		
		
		if( isset($_POST['ajax']) && $_POST['call_history_id'] )
		{
			$leadCallHistory = LeadCallHistory::model()->findByPk($_POST['call_history_id']);
			
			if( $leadCallHistory )
			{
				$lead = $leadCallHistory->lead;
				$customer = $leadCallHistory->customer;
						
				$hopperEntry = LeadHopper::model()->find(array(
					'condition' => 'lead_id = :lead_id AND agent_account_id = :agent_account_id',
					'params' => array(
						':lead_id' => $leadCallHistory->lead_id,
						':agent_account_id' => $authAccount->id,
					),
				));
				
				if(!empty($hopperEntry))
					$result['lead_hopper_id'] = $hopperEntry->id;
					
				$disposition = SkillDisposition::model()->findByPk($_POST['dispo_id']);	
			
				if( $disposition )
				{
					$leadCallHistory->disposition_id = $disposition->id;
					$leadCallHistory->disposition = $disposition->skill_disposition_name;
					
					$result['is_send_email'] = $disposition->is_send_email;
					
								
					if( isset($_POST['dispo_detail_id']) && is_numeric($_POST['dispo_detail_id']) )
					{
						$dispositionDetail = SkillDispositionDetail::model()->findByPk($_POST['dispo_detail_id']);	
						
						if( $dispositionDetail )
						{
							$leadCallHistory->disposition_detail_id = $dispositionDetail->id;
							$leadCallHistory->disposition_detail = $dispositionDetail->skill_disposition_detail_name;
						}
					}
					
					### disposition scenarios ####
					$dispositionTxt = $leadCallHistory->disposition;
						
					if(!empty($hopperEntry) && !empty($disposition) && !empty($leadCallHistory))
					{
						$hopperEntry->type = LeadHopper::TYPE_CONTACT;
						$hopperEntry->callback_date = null;
						$hopperEntry->appointment_date = null;
						
						if( $disposition->is_appointment_set == 1 )
						{
							//re-update LeadHopper and unset agent
							// LeadHopper::model()->updateAll(array('agent_account_id' => null ), 'agent_account_id = ' . $authAccount->id);
							// CustomerQueueViewer::model()->updateAll(array('call_agent' => null,'dials_until_reset'=> 0), 'customer_id = ' . $hopperEntry->customer_id);
							CustomerQueueViewer::model()->updateAll(array('dials_until_reset'=> 0), 'customer_id = ' . $hopperEntry->customer_id);
							
							if( isset($leadCallHistory->calendarAppointment) )
							{
								// $hopperEntry->status = LeadHopper::STATUS_CONFIRMATION;
								$hopperEntry->type = LeadHopper::TYPE_CONFIRMATION_CALL;
								$hopperEntry->appointment_date = $leadCallHistory->calendarAppointment->start_date;
							}
						}
						
						if( isset($leadCallHistory->calendarAppointment) && ($disposition->is_schedule_conflict == 1 || $disposition->is_location_conflict == 1) )
						{
							$dispositionTxt .= ' - Pending';
							
							// $hopperEntry->status = LeadHopper::STATUS_CONFLICT;
							$hopperEntry->type = LeadHopper::TYPE_CONFLICT;
							
							
							if($disposition->is_schedule_conflict == 1)
							{
								$calendarAppointment = $leadCallHistory->calendarAppointment;
								// $calendarAppointment->title = "SCHEDULE CONFLICT";
								$calendarAppointment->status= $calendarAppointment::STATUS_PENDING;
								$calendarAppointment->save(false);
							}
							
							if($disposition->is_location_conflict == 1)
							{
								$calendarAppointment = $leadCallHistory->calendarAppointment;
								// $calendarAppointment->title = "LOCATION CONFLICT";
								$calendarAppointment->status= $calendarAppointment::STATUS_PENDING;
								$calendarAppointment->save(false);
							}
							
						}
						
						$hopperEntry->save(false);
					}
					
					
					if( $disposition->is_complete_leads == 1 )
					{
						$lead->status = 3;
						
						//recyle module
						if(!empty($disposition->recycle_interval))
						{
							$time = strtotime(date("Y-m-d"));
							$finalDate = date("Y-m-d", strtotime("+".($disposition->recycle_interval * 30)." day", $time));
							$lead->recycle_date = $finalDate;
							$lead->recycle_lead_call_history_id = $leadCallHistory->id;
							$lead->recycle_lead_call_history_disposition_id = $leadCallHistory->disposition_id;
						}
					}
					
					if( $disposition->is_send_email == 1 )
					{					
						$emailMonitor = new EmailMonitor;
						
						$emailMonitor->setAttributes(array(
							'lead_id' => $leadCallHistory->lead_id,
							'agent_id' => $authAccount->id,
							'customer_id' => $leadCallHistory->customer_id,
							'skill_id' => $leadCallHistory->lead->list->skill_id,
							'disposition_id' => $leadCallHistory->disposition_id,
							'calendar_appointment_id' => $leadCallHistory->calendar_appointment_id,
							'html_content' => $leadCallHistory->getReplacementCodeValues(),
						));
						
						$emailMonitor->save(false);
					}
				}
				
				if(isset($_POST['phone_type']) && !empty($lead) )
				{
					$field = $_POST['phone_type'].'_phone_disposition';
							
					if( $_POST['phone_type'] != 'manual' )
					{
						$lead->$field = $leadCallHistory->disposition;

						$field = $_POST['phone_type'].'_phone_disposition_detail';
						$lead->$field = $leadCallHistory->disposition_detail;
					}
				}
				
				if( isset($_POST['note']) )
				{
					$leadCallHistory->agent_note = $_POST['note'];
				}
				
				if( !empty($_POST['callback_date']) && !empty($_POST['callback_time']) )
				{
					$leadCallHistory->callback_time = date('Y-m-d H:i:s', strtotime($_POST['callback_date'].' '.$_POST['callback_time']));
				
					if(!empty($customer) && !empty($lead) && !empty($hopperEntry))
					{
						$timeZone = AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $customer->phone) );
						
						if( !empty($lead->timezone) )
							$timeZone = $lead->timezone;
						
						$leadLocalTime = new DateTime($leadCallHistory->callback_time, new DateTimeZone(timezone_name_from_abbr($timeZone)) );
						
						// $hopperEntry->status = LeadHopper::STATUS_CALLBACK;
						$hopperEntry->type = LeadHopper::TYPE_CALLBACK;
						$hopperEntry->callback_date = $leadLocalTime->format('Y-m-d H:i:s');
						
						$hopperEntry->agent_account_id = null;
					}
				}
				
					
				if( $leadCallHistory->save(false) )
				{
					if( $lead->save(false) )
					{
						$leadHistory = new LeadHistory;
							
						$leadHistory->setAttributes(array(
							'lead_call_history_id' => $leadCallHistory->id,
							'lead_id' => $leadCallHistory->lead_id,
							'agent_account_id' => $authAccount->id,
							'calendar_appointment_id' => $leadCallHistory->calendar_appointment_id,
							'lead_phone_number' => $leadCallHistory->lead_phone_number,
							'dial_number' => 1,
							'call_date' => $leadCallHistory->start_call_time,
							'disposition' => $dispositionTxt,
							'disposition_detail' => $leadCallHistory->disposition_detail,
							'note' => $leadCallHistory->agent_note,
							'type' => 2,
						));
						
						// if( $disposition->is_appointment_set == 1 || $disposition->is_location_conflict == 1 || $disposition->is_schedule_conflict == 1 ) 
						// {
							// $leadHistory->calendar_appointment_id = $leadCallHistory->calendar_appointment_id;
						// }
						
						$leadHistory->save(false);
					}

					$result['status'] = 'success';
					$result['message'] = 'Database has been updated.';
				}
			}
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
						$date = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('America/New_York'));

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
						$model->office_phone_number = !empty($model->office_phone_number) ? "(".substr($model->office_phone_number, 0, 3).") ".substr($model->office_phone_number, 3, 3)."-".substr($model->office_phone_number,6) : '';
						$model->mobile_phone_number = !empty($model->mobile_phone_number) ? "(".substr($model->mobile_phone_number, 0, 3).") ".substr($model->mobile_phone_number, 3, 3)."-".substr($model->mobile_phone_number,6) : '';
						$model->home_phone_number = !empty($model->home_phone_number) ? "(".substr($model->home_phone_number, 0, 3).") ".substr($model->home_phone_number, 3, 3)."-".substr($model->home_phone_number,6) : '';
						
						$result['status'] = 'success';
						$result['message'] = 'Database has been updated';
						
						$result['updated_field_name'] = $_POST['field_name'];
						$result['updated_values'] = $model->attributes;
						
						
						if( isset($model->list) )
						{
							$list = $model->list;
							$customer = $list->customer;
							
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
							
							$html = $this->renderPartial('ajaxLeadPhoneNumbers', array(
								'lead' => $model,
								'list' => $list,
								'customer' => $customer,
								'dispositionOptions' => $dispositionOptions,
								'dispositionHtmlOptions' => $dispositionHtmlOptions,
								'isManualDial' => $_POST['Lead']['current_phone_type'] == 'manual_dial_phone_number' ? true : false,
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
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => '',
		);
		
		if( isset($_POST['lead_search_query']) )
		{
			$models = Lead::model()->findAll(array(
				'together' => true,
				'condition' => '
					t.type=1 
					AND 
					( 
						t.office_phone_number LIKE :search_query OR 
						t.mobile_phone_number LIKE :search_query OR 
						t.home_phone_number LIKE :search_query OR 
						t.first_name LIKE :search_query OR
						t.last_name LIKE :search_query OR
						t.email_address LIKE :search_query
					)',
				'params' => array(
					':search_query' => $_POST['lead_search_query'].'%',
				),
			));
			
			$html = $this->renderPartial('_lead_search_list', array(
				'models' => $models,
			), true); 
			
			$result['status'] = 'success';
			$result['html'] = $html;	
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

	
	public function actionLoadLeadToHopper()
	{
		$authAccount = Yii::app()->user->account;
		
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		if( isset($_POST['ajax']) && isset($_POST['id']) )
		{
			$lead = Lead::model()->findByPk($_POST['id']);
			
			if( $lead )
			{
				$existingHopperEntry = LeadHopper::model()->find(array(
					'condition' => 'lead_id = :lead_id',
					'params' => array(
						':lead_id' => $_POST['id'],
					),
				));
				
				if( $existingHopperEntry )
				{
					$existingHopperEntry->setAttributes(array(
						'agent_account_id' => $authAccount->id,
						'status' => LeadHopper::STATUS_INCALL,
						'type' => LeadHopper::TYPE_LEAD_SEARCH,
					));
					
					if( $existingHopperEntry->save(false) )
					{			
						$result['status'] = 'success';	
						$result['search_lead_id'] = $existingHopperEntry->id;	
					}
				}
				else
				{
					$hopperEntry = new LeadHopper;
					
					$hopperEntry->setAttributes(array(
						'lead_id' => $lead->id,
						'list_id' => $lead->list->id,
						'skill_id' => $lead->list->skill_id,
						'customer_id' => $lead->list->customer_id,
						'agent_account_id' => $authAccount->id,
						'lead_language' => $lead->language,
						'status' => LeadHopper::STATUS_INCALL,
						'type' => LeadHopper::TYPE_LEAD_SEARCH,
					));
					
					if( $hopperEntry->save(false) )
					{
						$result['status'] = 'success';	
						$result['search_lead_id'] = $hopperEntry->id;	
					}
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
}