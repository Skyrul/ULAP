<?php 

ini_set('memory_limit', '2000M');
set_time_limit(0);
		
class LeadsController extends Controller
{
	// public $layout='//layouts/column2';
	
	public function actionIndex($id=null, $customer_id)
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
		
		
		if($id == null)
		{
			$model = Lists::model()->find(array(
				'condition' => 'customer_id = :customer_id AND status != 3',
				'params' => array(
					':customer_id' => $customer_id,
				),
				'order' => 'status ASC',
			));
			
			if(!$model)
			{
				$model = new Lists; 
			}
		}
		else 
		{
			$model = Lists::model()->findByPk($id);
		}
		
		if( !empty($_GET['search_query']) )
		{
			$searchQuery = $_GET['search_query'];	
			$searchQuery = str_replace('-', '', $searchQuery); // Removes all hyphens.
			$searchQuery = preg_replace('/[^A-Za-z0-9\-]/', '', $searchQuery); // Removes special chars.
		
			$leads = Lead::model()->findAll(array(
				'condition' => '
					t.customer_id = :customer_id AND t.type=1 AND t.status != 4
					AND 
					( 
						t.office_phone_number LIKE :search_query OR 
						t.mobile_phone_number LIKE :search_query OR 
						t.home_phone_number LIKE :search_query OR 
						t.first_name LIKE :search_query OR
						t.last_name LIKE :search_query OR
						CONCAT(t.first_name , " " , t.last_name) LIKE :search_query OR
						t.email_address LIKE :search_query
					)',
				'params' => array(
					':customer_id' => $model->customer_id,
					':search_query' => $searchQuery.'%',
				),
			));
		}
		else
		{		
			$leads = Lead::model()->findAll(array(
				'with' => 'list',
				'together' => true,
				'condition' => '
					list.customer_id = :customer_id 
					AND t.list_id = :list_id 
					AND t.type = 1 
					AND t.type = 1 
					AND t.status != 4
				',
				'params' => array(
					':customer_id' => $model->customer_id,
					':list_id' => $model->id
				),
			));
		}
		
		$lists = Lists::model()->findAll(array(
			'condition' => 'customer_id = :customer_id AND status !=3',
			'params' => array(
				':customer_id' => $customer_id,
			),
		));
		
		$dataProvider = new CArrayDataProvider($leads, array(
			'pagination' => array(
				'pageSize' => 10,
			),
		));
		
		
		$totalLeads = 0;
		
		$customerSkill = CustomerSkill::model()->find(array(
			'condition' => 'customer_id = :customer_id AND skill_id = :skill_id',
			'params' => array(
				'customer_id' => $customer_id,
				'skill_id' => $model->skill_id,
			),
		));
		
		if( $customerSkill )
		{
			if( isset($customerSkill->contract) )
			{
				$contract = $customerSkill->contract;
				
				if( isset($contract) && $contract->fulfillment_type != null )
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
						}
					}
				}
			}
		}
		
		$this->render('index', array(
			'lists' => $lists,
			'leads' => $leads,
			'dataProvider' => $dataProvider,
			'model' => $model,
			'customer_id' => $customer_id,
			'totalLeads' => $totalLeads,
		));
	}
	
	
	public function actionView()
	{
		$result = array(
			'status' => '',
			'message' => '',
			'html' => '',
		);
		
		if( isset($_POST['Lead']) )
		{
			$model = $this->loadModel($_POST['Lead']['id']);
			
			if( $model->language != $_POST['Lead']['language'] )
			{
				$existingHopperRecord = LeadHopper::model()->find(array(
					'condition' => 'lead_id = :lead_id',
					'params' => array(
						':lead_id' => $model->id
					),
				));
				
				if( $existingHopperRecord )
				{
					$existingHopperRecord->language = $_POST['Lead']['language'];
					$existingHopperRecord->save(false);
				}
			}
			
			$model->attributes = $_POST['Lead'];
			
			$model->office_phone_number = preg_replace("/[^0-9]/","", $model->office_phone_number);
			$model->mobile_phone_number = preg_replace("/[^0-9]/","", $model->mobile_phone_number);
			$model->home_phone_number = preg_replace("/[^0-9]/","", $model->home_phone_number);
			
			if( empty($model->office_phone_number) && empty($model->mobile_phone_number) && empty($model->home_phone_number) )
			{
				$model->status = 3;
				$model->recycle_date = null;
				$model->recycle_lead_call_history_id = null;
				$model->recycle_lead_call_history_disposition_id = null;
			}
			else
			{
				if( $model->status == 1 )
				{
					$model->recycle_date = null;
					$model->recycle_lead_call_history_id = null;
					$model->recycle_lead_call_history_disposition_id = null;
				}
			}
			
			if( $model->save(false) )
			{
				$result['status'] = 'success';
				$result['message'] = 'Lead info has been updated.';
			}
		}
		
		if( isset($_POST['ajax']) && isset($_POST['id']) )
		{
			$model = $this->loadModel($_POST['id']);
			$listsArray = array();
			
			$leadHistories = LeadHistory::model()->findAll(array(
				'condition' => '
					lead_id = :lead_id 
					AND type NOT IN(6,9) 
					AND status !=3
					AND (
						disposition IS NULL 
						OR CAST(disposition AS BINARY) NOT IN("APPOINTMENT SET - Pending", "NO SHOW RESCHEDULE - Pending", "LOCATION CONFLICT - Pending")
					)
				',
				'params' => array(
					':lead_id' => $model->id,
				), 
			));
			
			$leadUpdateHistories = LeadHistory::model()->findAll(array(
				'condition' => 'lead_id = :lead_id AND type IN (6,8,9,10)',
				'params' => array(
					':lead_id' => $model->id,
				), 
			));
			
			$memberNumberCustomDatas = LeadCustomData::model()->findAll(array(
				'with' => 'list',
				'condition' => '
					t.lead_id = :lead_id 
					AND t.field_name = :field_name
					AND t.list_id IS NOT NULL
					AND list.status != 3
				',
				'group' => 't.list_id',
				'params' => array(
					':lead_id' => $model->id,
					':field_name' => 'Member Number',
				),
			));
			
			if( $memberNumberCustomDatas )
			{
				foreach( $memberNumberCustomDatas as $memberNumberCustomData )
				{
					$leadCustomDatas = LeadCustomData::model()->findAll(array(
						'with' => 'list',
						'condition' => 't.member_number = :member_number AND t.list_id IS NOT NULL AND list.status != 3',
						'group' => 'list_id',
						'params' => array(
							':member_number' => $memberNumberCustomData->value,
						),
						'order' => 't.date_created DESC'
					));
					
					if( $leadCustomDatas )
					{
						foreach( $leadCustomDatas as $leadCustomData )
						{
							if( !in_array($leadCustomData->list_id, $listsArray) )
							{
								$listsArray[$leadCustomData->list_id] = $leadCustomData->list->name;
							}
						}
					}
				}
			}
			
			$html = $this->renderPartial('ajax_view', array(
				'model' => $model,
				'listsArray' => $listsArray,
				'leadHistories' => $leadHistories,
				'leadUpdateHistories' => $leadUpdateHistories,
			), true);
			
			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}


	public function actionCreate()
	{
		$result = array(
			'status' => 'error',
			'message' => 'Database error.',
		);
		
		$model = new Lead;
		
		if( isset($_POST['Lead']) )
		{	
			$list = Lists::model()->findByPk($_POST['list_id']);
			
			if( $list )
			{
				$model->attributes = $_POST['Lead'];
				$model->list_id = $_POST['list_id'];
				$model->customer_id = $_POST['customer_id'];
				
				$model->office_phone_number = preg_replace("/[^0-9]/","", $model->office_phone_number);
				$model->mobile_phone_number = preg_replace("/[^0-9]/","", $model->mobile_phone_number);
				$model->home_phone_number = preg_replace("/[^0-9]/","", $model->home_phone_number);
				
				$model->recertify_date = $this->_computeForSkillMaxLeadLifeBeforeRecertify($list);
				$model->timezone = AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $list->calendar->office->phone) );
				
				if($model->save(false))
				{
					$result['status'] = 'success';
					$result['message'] = 'Database has been updated.';
				}
			}
		}
		
		echo json_encode($result);
		Yii::app()->end();
	}
	
	
	public function actionDelete()
	{
		$result = array(
			'status' => '',
			'message' => '',
			'html' => '',
		);
		
		if( isset($_POST['ajax']) && isset($_POST['id']) )
		{
			$model = $this->loadModel($_POST['id']);
			
			if( $model )
			{
				$model->status = 4;
				
				if( $model->save(false) )
				{
					$history = new CustomerHistory;
			
					$history->setAttributes(array(
						'model_id' => $model->id, 
						'customer_id' => $model->customer_id,
						'user_account_id' => Yii::app()->user->account->id,
						'page_name' => 'Lead',
						'content' => $model->first_name.' '.$model->last_name,
						'type' => $history::TYPE_DELETED,
					));

					$history->save(false);
					
					$result['status'] = 'success';
				}
			}
		}
		
		echo json_encode($result);
	}
	
	
	public function actionRemove()
	{
		$result = array(
			'status' => '',
			'message' => '',
			'html' => '',
		);
		
		if( isset($_POST['ajax']) && isset($_POST['id']) )
		{
			$model = $this->loadModel($_POST['id']);
			
			if( $model )
			{
				$model->status = 3;
				$model->recycle_date = null;
				$model->recycle_lead_call_history_id = null;
				
				if( $model->save(false) )
				{
					$history = new CustomerHistory;
			
					$history->setAttributes(array(
						'model_id' => $model->id, 
						'customer_id' => $model->customer_id,
						'user_account_id' => Yii::app()->user->account->id,
						'page_name' => 'Lead',
						'content' => $model->first_name.' '.$model->last_name,
						'type' => $history::TYPE_REMOVED,
					));

					$history->save(false);
					
					$result['status'] = 'success';
				}
			}
		}
		
		echo json_encode($result);
	}
	
	
	public function actionListPerformance()
	{
		$callables = Lead::model()->count(array(
			'with' => 'list',
			'condition' => 'list_id = :list_id AND t.type=1 AND t.status=1 AND list.status !=3',
			'params' => array(
				':list_id' => $_POST['list_id'],
			), 
		));
		
		$wrongNumbers = LeadCallHistory::model()->count(array(
			'with' => array('list', 'skillDisposition'),
			'condition' => 'list_id = :list_id AND skillDisposition.is_bad_phone_number=1 AND list.status != 3',
			'params' => array(
				':list_id' => $_POST['list_id'],
			), 
		));
	
		$completedLeads = Lead::model()->count(array(
			'with' => 'list',
			'condition' => 'list_id = :list_id AND t.type=1 AND t.status=3 AND list.status !=3',
			'params' => array(
				':list_id' => $_POST['list_id'],
			), 
		));
		
		$appointments = LeadCallHistory::model()->count(array(
			'with' => array('list', 'skillDisposition'),
			'condition' => 'list_id = :list_id AND skillDisposition.is_appointment_set=1 AND list.status != 3',
			'params' => array(
				':list_id' => $_POST['list_id'],
			), 
		));

		echo json_encode(array(
			'callables' => $callables,
			'appointments' => $appointments,
			'wrong_numbers' => $wrongNumbers,
			'completed_leads' => $completedLeads,
		));
		
	}
	
	
	public function actionCreateLeadHistory()
	{
		$authAccount = Yii::app()->user->account;
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => '',
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
		
		$leadHistories = LeadHistory::model()->findAll(array(
			'condition' => 'lead_id = :lead_id',
			'params' => array(
				':lead_id' => $_POST['LeadHistory']['lead_id'],
			), 
		));
		
		$result['html'] = $this->renderPartial('_lead_history_table', array('leadHistories'=>$leadHistories), true);
		
		echo json_encode($result);
	}
	
	public function loadModel($id)
	{
		$model = Lead::model()->findByPk($id);
		
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
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
			$memberNumber = null;
			
			$listCustomDatas = array();
			
			$memberNumberCustomData = LeadCustomData::model()->find(array(
				'condition' => '
					t.lead_id = :lead_id 
					AND t.list_id = :list_id 
					AND t.field_name = :field_name
				',
				'params' => array(
					':lead_id' => $_POST['lead_id'],
					':list_id' => $_POST['list_id'],
					':field_name' => 'Member Number',
				),
			));
			
			if( $memberNumberCustomData )
			{
				$memberNumber = $memberNumberCustomData->value;
				
				$listCustomDatas = ListCustomData::model()->findAll(array(
					'condition' => 'list_id = :list_id AND status=1',
					'params' => array(
						':list_id' => $_POST['list_id']
					),
					'order' => 'ordering ASC',
				));
			}
		

			$html = $this->renderPartial('ajax_custom_data_table', array(
				'memberNumber' => $memberNumber,
				'listCustomDatas' => $listCustomDatas,
				'listId' => $_POST['list_id'],
			), true);
			
			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}

	public function actionGetSurveyBySkill($skill_id)
	{
		$skill = Skill::model()->findByPk($skill_id);
		
		if($skill === null)
			throw new CHttpException('403', 'Page not found');
		
		$criteria = new CDbCriteria;
		// $criteria->compare('skill_id', $skill->id);
		$criteria->order = 'survey_name ASC';
		
		$surveys = Survey::model()->findAll($criteria);
		
		$surveyList = array();
		foreach($surveys as $survey)
		{
			$surveyList[$survey->id]['id'] = $survey->id;
			$surveyList[$survey->id]['survey_name'] = $survey->survey_name;
		}
		
		echo CJSON::ENCODE($surveyList);
		
		Yii::app()->end();
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

	function urlExists($url)
	{
	   $headers = get_headers($url);
	   
	   return stripos($headers[0],"200 OK") ? true : false;
	}
}

?>