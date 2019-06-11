<?php

ini_set('memory_limit', '-1');
ini_set('max_execution_time', 300);

class InsightTestController extends Controller
{
	public function actionIndex($customer_id = null)
	{
		if( !Yii::app()->user->isGuest && in_array(Yii::app()->user->account->account_type_id, array(Account::TYPE_CUSTOMER, Account::TYPE_CUSTOMER_OFFICE_STAFF)) )
		{
			$authAccount = Yii::app()->user->account;
			
			if( $authAccount->getIsCustomer() )
			{
				$customer = Customer::model()->find(array(
					'condition' => 'account_id = :account_id',
					'params' => array(
						':account_id' => $authAccount->id,
					),
				));
				
				if( $customer && $customer->id != $customer_id )
				{
					$this->redirect(array('index', 'customer_id'=>$customer->id));
				}
			}
			
			if( $authAccount->getIsCustomerOfficeStaff() )
			{
				$customerOfficeStaff = CustomerOfficeStaff::model()->find(array(
					'condition' => 'account_id = :account_id',
					'params' => array(
						':account_id' => $authAccount->id,
					),
				));
				
				if( $customerOfficeStaff && $customerOfficeStaff->customer_id != $customer_id )
				{
					$this->redirect(array('index', 'customer_id'=>$customerOfficeStaff->customer_id));
				}
			}
		}
		
		
		if( !Yii::app()->user->account->checkPermission('customer_dashboard_tab','visible') )
		{
			$url = array('/customer');
			$noPermission = true;
			
			if( Yii::app()->user->account->checkPermission('customer_history_tab','visible') )
			{
				$url = array('/customer/history/index', 'customer_id'=>$customer_id);
				$noPermission = false;
			}
			
			if( Yii::app()->user->account->checkPermission('customer_my_files_tab','visible') )
			{
				$url = array('/customer/customerFile/index', 'customer_id'=>$customer_id);
				$noPermission = false;
			}
			
			if( Yii::app()->user->account->checkPermission('customer_offices_tab','visible') )
			{
				$url = array('/customer/calendar/index', 'customer_id'=>$customer_id);
				$noPermission = false;
			}
			
			if( Yii::app()->user->account->checkPermission('customer_setup_tab','visible') )
			{
				$url = array('/customer/data/view', 'id'=>$customer_id);
				$noPermission = false;
			}
			
			if( Yii::app()->user->account->checkPermission('customer_skills_tab','visible') )
			{
				$url = array('/customer/customerSkill/index', 'customer_id'=>$customer_id);
				$noPermission = false;
			}
			
			if( Yii::app()->user->account->checkPermission('customer_billing_tab','visible') )
			{
				$url = array('/customer/billing/index', 'customer_id'=>$customer_id);
				$noPermission = false;
			}
			
			if( Yii::app()->user->account->checkPermission('customer_reports_tab','visible') )
			{
				$url = array('/customer/reports/index', 'customer_id'=>$customer_id);
				$noPermission = false;
			}
			
			if( Yii::app()->user->account->checkPermission('customer_leads_tab','visible') )
			{
				$url = array('/customer/leads/index', 'customer_id'=>$customer_id);
				$noPermission = false;
			}
			
			if( Yii::app()->user->account->checkPermission('customer_calendar_tab','visible') )
			{
				$url = array('/calendar/index', 'customer_id'=>$customer_id);
				$noPermission = false;
			}
			
			if( $noPermission )
			{
				Yii::app()->user->setFlash('danger', 'Your security group has no permission to access customer account details pages.');
			}
			
			$this->redirect($url);
		}
		
		$customer = Customer::model()->findByPk($customer_id);
		
		$customerSkills = CustomerSkill::model()->findAll(array(
			'condition' => 'customer_id = :customer_id AND status=1',
			'params' => array(
				':customer_id' => $customer->id,
			),
		));
		
		$appointmentSetCount = 0;
		$remainingCallableCount = 0;
		// $appointmentSetCount = CalendarAppointment::model()->count(array(
			// 'with' => 'calendar',
			// 'together' => true,
			// 'condition' => 'calendar.customer_id = :customer_id AND t.title IN ("APPOINTMENT SET", "INSERT APPOINTMENT") AND t.status !=4 AND t.lead_id IS NOT NULL AND MONTH(t.date_created) = MONTH(NOW()) AND YEAR(t.date_created) = YEAR(NOW())',
			// 'params' => array(
				// 'customer_id' => $customer_id,
			// ),
		// ));
		
		$scheduleConflicts = CalendarAppointment::model()->findAll(array(
			'with' => 'calendar',
			'condition' => 'calendar.customer_id = :customer_id AND t.title = "SCHEDULE CONFLICT" and t.status=2',
			'params' => array(
				'customer_id' => $customer->id,
			),
		));
		
		$locationConflicts = CalendarAppointment::model()->findAll(array(
			'with' => 'calendar',
			'condition' => 'calendar.customer_id = :customer_id AND t.title = "LOCATION CONFLICT" and t.status=2',
			'params' => array(
				'customer_id' => $customer->id,
			),
		));

		$locationConflictDataProvider = new CArrayDataProvider($locationConflicts, array(
			'pagination' => array(
				'pageSize' => 100,
			),
		));
		
		$scheduleConflictDataProvider = new CArrayDataProvider($scheduleConflicts, array(
			'pagination' => array(
				'pageSize' => 100,
			),
		));
		
		
		
		if( isset($_POST['ajax']) )
		{
			if( $_POST['ajax'] == 'getCount' )
			{		
				echo json_encode(array(
					'action_center' => $locationConflictDataProvider->totalItemCount + $scheduleConflictDataProvider->totalItemCount,
					'location_conflict' => $locationConflictDataProvider->totalItemCount, 
					'schedule_conflict' => $scheduleConflictDataProvider->totalItemCount, 
				));
			}
			
			if( $_POST['ajax'] == 'customerSummary' )
			{
				$html = $this->renderPartial('ajaxIndex', array(
					'customer' => $customer,
					'appointmentSetCount' => $appointmentSetCount,
					'remainingCallableCount' => $remainingCallableCount,
					'locationConflictDataProvider' => $locationConflictDataProvider,
					'scheduleConflictDataProvider' => $scheduleConflictDataProvider,
					'customerSkills' => $customerSkills,
				), true);
				
				echo json_encode(array(
					'status' => 'success',
					'html' => $html,
				));
			}
			
			Yii::app()->end();
		}
		
		
		$this->render('index',array(
			'customer' => $customer,
			'appointmentSetCount' => $appointmentSetCount,
			'remainingCallableCount' => $remainingCallableCount,
			'locationConflictDataProvider' => $locationConflictDataProvider,
			'scheduleConflictDataProvider' => $scheduleConflictDataProvider,
			'customerSkills' => $customerSkills,
		));
		
	}

	public function actionRecycleLeads($customer_id, $list_id = null, $page = null)
	{
		$customer = Customer::model()->findByPk($customer_id);
		
		//update line 42 && 122 && 154, when updating this query
		if( empty($list_id) )
		{
			$leadRecyclesGrouped = Lead::model()->findAll(array(
				'select' => 'count(recycle_lead_call_history_disposition_id) AS ctr, t.*',
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
				',
				'group'=> 'recycle_lead_call_history_disposition_id',
				'params' => array(
					':customer_id' => $customer_id,
				),
			));
		}
		else
		{
			$leadRecyclesGrouped = Lead::model()->findAll(array(
				'select' => 'count(recycle_lead_call_history_disposition_id) AS ctr, t.*',
				'with' => array('list', 'list.skill'),
				'together' => true,
				'condition' => '
					t.list_id = :list_id 
					AND t.customer_id = :customer_id 
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
				',
				'group'=> 'recycle_lead_call_history_disposition_id',
				'params' => array(
					':list_id' => $list_id,
					':customer_id' => $customer_id,
				),
			));
		}
		
		
		//update line 53 && 157 && 187, if updating this query
		
		if(empty($list_id))
		{
			$leadRecertifyCount = Lead::model()->count(array(
				'with' => 'list',
				'together' => true,
				'condition' => '
					t.customer_id = :customer_id 
					AND list.status = 1 
					AND t.type = 1 
					AND t.status = 1 
					AND (
						t.recertify_date = "0000-00-00" 
						OR t.recertify_date IS NULL 
						OR NOW() >= t.recertify_date
					)
				',
				'params' => array(
					':customer_id' => $customer_id,
				),
			));
			
			$leadRecertifyGroupedCount = Lead::model()->findAll(array(
				'select' => 'count(t.id) AS ctr, t.*',
				'with' => 'list',
				'together' => true,
				'condition' => '
					t.customer_id = :customer_id 
					AND list.status = 1 
					AND t.type = 1
					AND t.status = 1 
					AND (
						t.recertify_date = "0000-00-00" 
						OR t.recertify_date IS NULL 
						OR NOW() >= t.recertify_date
					)
				',
				'group'=> 'list_id',
				'params' => array(
					':customer_id' => $customer_id,
				),
			));
		}
		else
		{
			$leadRecertifyCount = Lead::model()->count(array(
				'with' => 'list',
				'together' => true,
				'condition' => '
					t.list_id = :list_id 
					AND list.customer_id = :customer_id 
					AND t.type = 1 
					AND t.status = 1 
					AND (
						t.recertify_date = "0000-00-00" 
						OR t.recertify_date IS NULL 
						OR NOW() >= t.recertify_date
					)',
				'params' => array(
					':list_id' => $list_id,
					':customer_id' => $customer_id,
				),
			));
			
			$leadRecertifyGroupedCount = Lead::model()->findAll(array(
				'select' => 'count(t.id) AS ctr, t.*',
				'with' => 'list',
				'together' => true,
				'condition' => '
					t.list_id = :list_id 
					AND list.customer_id = :customer_id 
					AND t.type = 1 
					AND t.status = 1 
					AND (
						t.recertify_date = "0000-00-00" 
						OR t.recertify_date IS NULL 
						OR NOW() >= t.recertify_date
					)',
				'group'=> 'list_id',
				'params' => array(
					':list_id' => $list_id,
					':customer_id' => $customer_id,
				),
			));
		}
		
		
		Yii::app()->clientscript->scriptMap['jquery.min.js'] = false;
		$this->renderPartial('recycleLeads',array(
			'customer' => $customer,
			'leadRecyclesGrouped' => $leadRecyclesGrouped,
			'leadRecertifyCount' => $leadRecertifyCount,
			'leadRecertifyGroupedCount' => $leadRecertifyGroupedCount,
			'list_id' => $list_id,
			'page' => $page,
		),false, true);
	}

	public function actionRecycle($customer_id, $recycle_lead_call_history_disposition_id)
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);

		$valid = false;
		
		$importLimit = 0;
	
		$customer = Customer::model()->findByPk($customer_id);
		
		$customerQueue = CustomerQueueViewer::model()->find(array(
			'condition' => 'customer_id = :customer_id',
			'params' => array(
				':customer_id' => $customer_id,
			),
			'order' => 'skill_id DESC',
		));
		
		if( $customerQueue )
		{
			$extras = 0;
			
			$customerExtras = CustomerExtra::model()->findAll(array(
				'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id AND status=1 AND year = :year AND month = :month',
				'params' => array(
					':customer_id' => $customerQueue->customer_id,
					':contract_id' => $customerQueue->contract_id,
					':skill_id' => $customerQueue->skill_id,
					':year' => date('Y'),
					':month' => date('m'),
				),
			));
			
			if( $customerExtras )
			{
				foreach( $customerExtras as $customerExtra )
				{
					$extras += $customerExtra->quantity;
				}
			}
			
			if( $customerQueue->fulfillment_type == 'Goal' )
			{
				$importLimit = $customerQueue->contracted_quantity * 10;
				
				if( $extras > 0 )
				{
					$importLimit += $extras * 10;
				}
			}
			else
			{
				$importLimit = $customerQueue->contracted_quantity;
				
				if( $extras > 0 )
				{
					$importLimit += $extras;
				}
			}
		}
			
		$existingCustomerLeadImportLog = CustomerLeadImportLog::model()->find(array(
			'condition' => 'customer_id = :customer_id AND month = :month AND year = :year',
			'params' => array(
				':customer_id' => $customer_id,
				':month' => date('F'),
				':year' => date('Y'),
			),
		));
						
		if( $existingCustomerLeadImportLog )
		{
			$customerLeadImportLog = $existingCustomerLeadImportLog;
			$importLimit = $importLimit - $customerLeadImportLog->leads_imported;
		}
		else
		{
			$customerLeadImportLog = new CustomerLeadImportLog;
			
			$customerLeadImportLog->setAttributes(array(
				'customer_id' => $customer_id,
				'contract_id' => $customerQueue->contract_id,
				'skill_id' => $customerQueue->skill_id,
				'month' => date('F'),
				'year' => date('Y'),
			));
			
			$customerLeadImportLog->save(false);
		}

		if( $customerLeadImportLog->leads_imported <= $importLimit )
		{
			//update line 42 && 122 && 154, if updating this query
			$leadRecycles = Lead::model()->findAll(array(
				'with' => array('list', 'list.skill'),
				'together' => true,
				'condition' => '
					t.customer_id = :customer_id 
					AND list.status = 1 
					AND t.type = 1 
					AND t.is_do_not_call = 0
					AND recycle_lead_call_history_id IS NOT NULL
					AND recycle_lead_call_history_disposition_id = :recycle_lead_call_history_disposition_id
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
				',
				'params' => array(
					':customer_id' => $customer_id,
					':recycle_lead_call_history_disposition_id' => $recycle_lead_call_history_disposition_id,
				),
			));
			
			// echo 'count: ' . count($leadRecycles);
			
			// echo '<br><br>';
			
			// echo '<pre>';
				// if( $leadRecycles )
				// {
					// foreach( $leadRecycles as $leadRecycle )
					// {
						// print_r($leadRecycle->attributes);
					// }
				// }
			// exit;
			
			
			$disposition = SkillDisposition::model()->findByPk($recycle_lead_call_history_disposition_id);	
			
			if(!empty($leadRecycles) && !empty($disposition) && $disposition->is_do_not_call == 0 )
			{
				$listIds = array();
				
				foreach($leadRecycles as $leadRecycle)
				{ 
					if( $customerLeadImportLog && $customerLeadImportLog->leads_imported > $importLimit )
					{
						break;
					}
					
					$leadRecycle->status = 1;
					$leadRecycle->number_of_dials = 0;
					$leadRecycle->home_phone_dial_count = 0;
					$leadRecycle->mobile_phone_dial_count = 0;
					$leadRecycle->office_phone_dial_count = 0;
					
					$leadRecycle->recycle_date = NULL;
					$leadRecycle->recycle_lead_call_history_id = NULL;
					$leadRecycle->recycle_lead_call_history_disposition_id = NULL;
					
					$leadRecycle->recertify_date = $this->_computeForSkillMaxLeadLifeBeforeRecertify($leadRecycle->list);

					if( $leadRecycle->save(false) )
					{
						$listIds[$leadRecycle->list_id][] = $leadRecycle->id;
						
						$leadHistory = new LeadHistory;
						
						$leadHistory->setAttributes(array(
							'lead_id' => $leadRecycle->id,
							'note' => 'Lead was recycled and certified for DNC compliance.',
							'type' => 1,
						));
						
						$leadHistory->save(false);
						
						if( $customerLeadImportLog )
						{
							$customerLeadImportLog->leads_imported = $customerLeadImportLog->leads_imported + 1;
							$customerLeadImportLog->save(false);
						}
					}
				}

				if( $listIds )
				{
					foreach( $listIds as $listId => $leadIds )
					{
						$list = Lists::model()->findByPk($listId);
						
						$history = new CustomerHistory;
										
						$history->setAttributes(array(
							'model_id' => $listId, 
							'customer_id' => $customer_id,
							'user_account_id' => Yii::app()->user->account->id,
							'page_name' => 'List',
							// 'content' => count($leadIds) . ' leads were recycled and certified for DNC compliance from list ' . $list->name,
							'content' => count($leadIds) . ' leads were recycled from the list ' . $list->name,
							'old_data' => implode(',', $leadIds),
							'type' => $history::TYPE_UPDATED,
						));
							
						$history->save(false);
					}
				}
				
				$result['status'] = 'success';
			}
		}
		else
		{
			$result['status'] = 'error';
			$result['message'] = 'You have reached your lead import/recycle limit for this month.';
		}
		
		// $this->redirect(array('insight/index','customer_id'=> $customer->id));
		echo json_encode($result);
	}
	
	public function actionRecertify($customer_id, $list_id = null, $page = null)
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);

		$list = Lists::model()->findByPk($list_id);
		$leadIds = array();
		
		//update line 53 && 135 && 187, if updating this query
		if(empty($list_id))
		{
			$leadRecertifys = Lead::model()->findAll(array(
				'with' => 'list',
				'together' => true,
				'condition' => '
					list.customer_id = :customer_id 
					AND list.status = 1 
					AND t.status = 1
					AND t.type = 1 
					AND (
						recertify_date = "0000-00-00" 
						OR recertify_date IS NULL 
						OR NOW() >= recertify_date
					)',
				'params' => array(
					':customer_id' => $customer_id,
				),
			));
		}
		else
		{ 
			$leadRecertifys = Lead::model()->findAll(array(
				'with' => 'list',
				'together' => true,
				'condition' => '
					t.list_id = :list_id 
					AND list.customer_id = :customer_id 
					AND t.status = 1 
					AND list.status = 1 
					AND t.type = 1 
					AND (
						recertify_date = "0000-00-00" 
						OR recertify_date IS NULL 
						OR NOW() >= recertify_date
					)',
				'params' => array(
					':list_id' => $list_id,
					':customer_id' => $customer_id,
				),
			));
			
		}
		
		if( !empty($leadRecertifys) )
		{ 
			foreach($leadRecertifys as $leadRecertify)
			{
				$leadRecertify->recertify_date = $this->_computeForSkillMaxLeadLifeBeforeRecertify($leadRecertify->list);
				// $leadRecertify->number_of_dials = 0;
				// $leadRecertify->status = 1;
				if( $leadRecertify->save(false) )
				{
					$leadIds[] = $leadRecertify->id;
				}
			}
			
			$history = new CustomerHistory;
									
			$history->setAttributes(array(
				'model_id' => $list_id, 
				'customer_id' => $customer_id,
				'user_account_id' => Yii::app()->user->account->id,
				'page_name' => 'List',
				'content' => count($leadRecertifys) . ' leads were recertified from list ' . $list->name,
				'old_data' => implode(', ', $leadIds),
				'type' => $history::TYPE_UPDATED,
			));

			$history->save(false); 
		
			$result['status'] = 'success';
		}
		
		// if(!empty($page) && $page == 'lead')
			// $this->redirect(array('leads/index','id'=> $list_id, 'customer_id'=> $customer->id));
		// else
			// $this->redirect(array('insight/index','customer_id'=> $customer->id));
		
		echo json_encode($result);
	}
	
	public function actionRecertifyRemoveLead($lead_id)
	{
		$result = array(
			'status' => 'error',
			'message' > '',
		);
		
		$lead = Lead::model()->findByPk($lead_id);
		
		if( $lead )
		{
			$lead->status = 3;
			
			if( $lead->save(false) )
			{
				$result['status'] = 'success';
			}
		}
		
		echo json_encode($result);
	}
	
	public function actionRecycleRemoveLead($lead_id)
	{
		$result = array(
			'status' => 'error',
			'message' > '',
		);
		
		$lead = Lead::model()->findByPk($lead_id);
		
		if( $lead )
		{
			$lead->is_recycle_removed = 1;
			
			if( $lead->save(false) )
			{
				$result['status'] = 'success';
			}
		}
		
		echo json_encode($result);
	}
	
	public function _computeForSkillMaxLeadLifeBeforeRecertify($model)
	{
		//recycle - recertify  module
		if(!empty($model->skill->max_lead_life_before_recertify))
		{
			$time = strtotime(date("Y-m-d"));
			$finalDate = date("Y-m-d", strtotime("+".($model->skill->max_lead_life_before_recertify)." day", $time));
			return $finalDate;
		}
		else
			return date("Y-m-d");
	}

	public function actionStaffList($customer_id)
	{
		$models = CustomerOfficeStaff::model()->findAll(array(
			'condition' => 'customer_id = :customer_id AND is_deleted=0',
			'params' => array(
				':customer_id' => $customer_id,
			),
		));
		
		$html = $this->renderPartial('staffList', array(
			'models' => $models,
		), true);
		
		echo json_encode(array(
			'status' => 'success',
			'html' => $html,
		));
	}

	
	public function actionAjaxRecertifyShowLeads()
	{
		$html = '';
		
		$result = array(
			'status' => 'error',
			'html' => $html,
		);
		
		$leads = Lead::model()->findAll(array(
			'with' => 'list',
			'together' => true,
			'condition' => 't.list_id = :list_id AND list.customer_id = :customer_id AND t.status = 1 AND list.status = 1 AND t.type = 1 AND (recertify_date = "0000-00-00" || recertify_date IS NULL || NOW() >= recertify_date)',
			'params' => array(
				':list_id' => $_POST['list_id'],
				':customer_id' => $_POST['customer_id'],
			),
		));
		
		if( $leads )
		{
			$result['status'] = 'success';
			
			$html .= '<table class="table table-striped table-condensed tabl-hover">';
					
			foreach( $leads as $lead )
			{
				$html .= '<tr>';
					$html .= '<td>'.$lead->first_name.' '.$lead->last_name.'</td>';
					$html .= '<td>'.CHtml::link('Remove',array('insight/recertify','customer_id' => $lead->customer_id,'list_id' => $lead->list_id),array('class'=>'btn btn-minier btn-info btn-recertify')).'</td>';
				$html .= '</tr>';
			}
			
			$html .= '</table>';
			
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}
}
