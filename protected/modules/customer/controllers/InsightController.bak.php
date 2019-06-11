<?php

class InsightController extends Controller
{
	public function actionIndex($customer_id = null)
	{
		$customer = Customer::model()->findByPk($customer_id);
		
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
			'locationConflictDataProvider' => $locationConflictDataProvider,
			'scheduleConflictDataProvider' => $scheduleConflictDataProvider,
		));
		
	}

	public function actionRecycleLeads($customer_id, $list_id = null, $page = null)
	{
		$customer = Customer::model()->findByPk($customer_id);
		
		//update line 42 && 122 && 154, when updating this query
		if(empty($list_id))
		{
			$leadRecyclesGrouped = Lead::model()->findAll(array(
				'select' => 'count(recycle_lead_call_history_disposition_id) AS ctr, t.*',
				'with' => 'list',
				'together' => true,
				'condition' => 'list.customer_id = :customer_id AND list.status = 1 AND t.type=1 AND t.status = 1 AND (recycle_date != "0000-00-00" AND recycle_date IS NOT NULL) AND NOW() >= recycle_date AND recycle_lead_call_history_id IS NOT NULL',
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
				'with' => 'list',
				'together' => true,
				'condition' => 't.list_id = :list_id AND list.customer_id = :customer_id AND list.status = 1 AND t.type=1 AND t.status = 1 AND (recycle_date != "0000-00-00" AND recycle_date IS NOT NULL) AND NOW() >= recycle_date AND recycle_lead_call_history_id IS NOT NULL',
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
				'condition' => 'list.customer_id = :customer_id AND list.status = 1 AND t.type=1 AND t.status = 1 AND (recertify_date = "0000-00-00" || recertify_date IS NULL || NOW() >= recertify_date)',
				'params' => array(
					':customer_id' => $customer_id,
				),
			));
			
			$leadRecertifyGroupedCount = Lead::model()->findAll(array(
				'select' => 'count(t.id) AS ctr, t.*',
				'with' => 'list',
				'together' => true,
				'condition' => 'list.customer_id = :customer_id AND list.status = 1 AND t.type=1 AND t.status = 1 AND (recertify_date = "0000-00-00" || recertify_date IS NULL || NOW() >= recertify_date)',
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
				'condition' => 't.list_id = :list_id AND list.customer_id = :customer_id AND t.type=1 AND t.status = 1 AND (recertify_date = "0000-00-00" || recertify_date IS NULL || NOW() >= recertify_date)',
				'params' => array(
					':list_id' => $list_id,
					':customer_id' => $customer_id,
				),
			));
			
			$leadRecertifyGroupedCount = Lead::model()->findAll(array(
				'select' => 'count(t.id) AS ctr, t.*',
				'with' => 'list',
				'together' => true,
				'condition' => 't.list_id = :list_id AND list.customer_id = :customer_id AND t.type=1 AND t.status = 1 AND (recertify_date = "0000-00-00" || recertify_date IS NULL || NOW() >= recertify_date)',
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
		$customer = Customer::model()->findByPk($customer_id);

		//update line 42 && 122 && 154, if updating this query
		$leadRecycles = Lead::model()->findAll(array(
			'with' => 'list',
			'together' => true,
			'condition' => 'list.customer_id = :customer_id AND list.status = 1 AND t.type=1 AND (recycle_date != "0000-00-00" AND recycle_date IS NOT NULL) AND NOW() >= recycle_date AND recycle_lead_call_history_id IS NOT NULL AND recycle_lead_call_history_disposition_id = :recycle_lead_call_history_disposition_id',
			'params' => array(
				':customer_id' => $customer_id,
				':recycle_lead_call_history_disposition_id' => $recycle_lead_call_history_disposition_id,
			),
		));
		
		
		$disposition = SkillDisposition::model()->findByPk($recycle_lead_call_history_disposition_id);	
		
		if(!empty($leadRecycles) && !empty($disposition))
		{
			$listIds = array();
			
			foreach($leadRecycles as $leadRecycle)
			{
				// $leadRecycle->status = 1;
				$leadRecycle->recycle_date = NULL;
				$leadRecycle->recycle_lead_call_history_id = NULL;
				$leadRecycle->recycle_lead_call_history_disposition_id = NULL;
				
				if( $leadRecycle->save(false) )
				{
					$listIds[$leadRecycle->list_id][] = $leadRecycle->id;
				}
			}
			
			if( $listIds )
			{
				foreach( $listIds as $listId )
				{
					$list = Lists::model()->findByPk($listIds);
					
					$history = new CustomerHistory;
									
					$history->setAttributes(array(
						'model_id' => $listId, 
						'customer_id' => $customer_id,
						'user_account_id' => Yii::app()->user->account->id,
						'page_name' => 'List',
						'content' => count($listId) . ' leads were recycled and certified for DNC compliance from list ' . $list->name,
						'type' => $history::TYPE_UPDATED,
					));

					$history->save(false);
				}
			}
		}
		
		$this->redirect(array('insight/index','customer_id'=> $customer->id));
	}
	
	public function actionRecertify($customer_id, $list_id = null, $page = null)
	{
		ini_set('memory_limit', '-1');
		ini_set('max_execution_time', 300);
		
		$customer = Customer::model()->findByPk($customer_id);
		$list = Lists::model()->findByPk($list_id);
		
		//update line 53 && 135 && 187, if updating this query
		if(empty($list_id))
		{
			$leadRecertifys = Lead::model()->findAll(array(
				'with' => 'list',
				'together' => true,
				'condition' => 'list.customer_id = :customer_id AND list.status = 1 AND t.status = 1 AND t.type = 1 AND (recertify_date = "0000-00-00" || recertify_date IS NULL || NOW() >= recertify_date)',
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
				'condition' => 't.list_id = :list_id AND list.customer_id = :customer_id AND t.status = 1 AND list.status = 1 AND t.type = 1 AND (recertify_date = "0000-00-00" || recertify_date IS NULL || NOW() >= recertify_date)',
				'params' => array(
					':list_id' => $list_id,
					':customer_id' => $customer_id,
				),
			));
			
		}
		
		if(!empty($customer) && !empty($leadRecertifys))
		{ 
			foreach($leadRecertifys as $leadRecertify)
			{
				$leadRecertify->recertify_date = $this->_computeForSkillMaxLeadLifeBeforeRecertify($leadRecertify->list);
				// $leadRecertify->number_of_dials = 0;
				// $leadRecertify->status = 1;
				$leadRecertify->save(false);
			}
			
			$history = new CustomerHistory;
									
			$history->setAttributes(array(
				'model_id' => $list_id, 
				'customer_id' => $customer_id,
				'user_account_id' => Yii::app()->user->account->id,
				'page_name' => 'List',
				'content' => count($leadRecertifys) . ' leads were recertified from list ' . $list->name,
				'type' => $history::TYPE_UPDATED,
			));

			$history->save(false);
		}
		
		if(!empty($page) && $page == 'lead')
			$this->redirect(array('leads/index','id'=> $list_id, 'customer_id'=> $customer->id));
		else
			$this->redirect(array('insight/index','customer_id'=> $customer->id));
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
}
