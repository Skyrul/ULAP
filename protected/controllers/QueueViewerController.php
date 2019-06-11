<?php 

ini_set('memory_limit', '5000M');
set_time_limit(0); 

class QueueViewerController extends Controller
{
	public function actionIndex()
	{
		if( !Yii::app()->user->isGuest )
		{
			if( in_array(Yii::app()->user->account->account_type_id, array(Account::TYPE_PORTAL, Account::TYPE_CUSTOMER, Account::TYPE_CUSTOMER_OFFICE_STAFF)) )
			{
				if( Yii::app()->user->account->account_type_id == Account::TYPE_CUSTOMER)
				{
					$customer = Customer::model()->find(array(
						'condition' => 'account_id = :account_id',
						'params' => array(
							':account_id' => Yii::app()->user->account->id
						),
					));
				}
				
				if( Yii::app()->user->account->account_type_id == Account::TYPE_CUSTOMER_OFFICE_STAFF)
				{
					$staff = CustomerOfficeStaff::model()->find(array(
						'condition' => 'account_id = :account_id',
						'params' => array(
							':account_id' => Yii::app()->user->account->id
						),
					));
					
					if( $staff )
					{
						$customer = $staff->customer;
					}
				}
				
				if( $customer )
				{
					$customerPopupLoginCount = CustomerPopupLogin::model()->count(array(
						'condition' => '
							customer_id = :customer_id 
							AND company_id = :company_id 
							AND account_id = :account_id
						',
						'params' => array(
							'customer_id' => $customer->id,
							'company_id' => $customer->company_id,
							'account_id' => Yii::app()->user->account->id
						)
					));
					
					if( isset($customer->company) && $customer->company->popup_show == 1 && $customerPopupLoginCount < $customer->company->popup_logins )
					{
						$popupLogin = new CustomerPopupLogin;
						
						$popupLogin->setAttributes(array(
							'customer_id' => $customer->id,
							'company_id' => $customer->company_id,
							'account_id' => Yii::app()->user->account->id
						));
						
						$popupLogin->save(false);
						
						$this->redirect(array('customer/data/index','popup'=>1));
					}
					else
					{
						$this->redirect(array('customer/data/index'));
					}
				}
				else
				{
					$this->redirect(array('customer/data/index'));
				}
			}
			elseif( Yii::app()->user->account->account_type_id == Account::TYPE_HOSTDIAL_AGENT )
			{
				if( Yii::app()->user->account->use_webphone == 1  )
				{
					$this->redirect(array('/agent/webphone'));
				}
				else
				{
					$this->redirect(array('/agent'));
				}
			}
			elseif( Yii::app()->user->account->account_type_id == Account::TYPE_GAMING_PROJECT_MANAGER )
			{
				$customerOfficeStaff = CustomerOfficeStaff::model()->find(array(
					'condition' => 'account_id = :account_id',
					'params' => array(
						':account_id' => Yii::app()->user->account->id
					),					
				));
				
				if( $customerOfficeStaff )
				{
					$this->redirect(array('hostDial/insight/index', 'customer_id'=>$customerOfficeStaff->customer_id));
				}
				else
				{
					$this->redirect(array('customer/data/index'));
				}
			}
			else
			{
				
			}
		}
		
		$skill_id = null;
		$campaign_id = null;
		
		$is_header = "On"; 
		
		if(isset($_REQUEST['skill_id']))
		{
			$skill_id = $_REQUEST['skill_id'];
		}
		
		if(isset($_REQUEST['campaign_id']))
		{
			$campaign_id = $_REQUEST['campaign_id'];
		}
		
		if(isset($_REQUEST['is_header']))
		{
			$is_header = $_REQUEST['is_header'];
		}
		
		
		$customerQueuesArray = array();
		
		$whiteCriteria = new CDbCriteria;
		$whiteCriteria->addCondition('status = 1 AND available_leads > 0 AND next_available_calling_time != "Goal Appointment Reached"');
		$whiteCriteria->order = 'priority DESC';
		
		if( isset($_REQUEST['skill_id']) )
		{
			$campaignSkillIds[] = $skill_id;
			
			$whiteCriteria->compare('skill_id', $skill_id);
		}
		
		if( $campaign_id != null )
		{
			$campaignSkills = CampaignSkill::model()->findAll(array(
				'condition' => 't.campaign_id = :campaign_id AND t.is_active = 1',
				'params' => array(
					':campaign_id' => $campaign_id
				),
			));
			
			if( $campaignSkills )
			{
				$campaignSkillIds = CHtml::listData($campaignSkills,'skill_id','skill_id');
				
				$whiteCriteria->addInCondition('skill_id', $campaignSkillIds);
			}
		}
		
		//get all customer queues
		$customerWhiteQueues = CustomerQueueViewer::model()->findAll($whiteCriteria); 
		
		$greyCriteria = new CDbCriteria;
		$greyCriteria->addCondition('(status = 2 OR available_leads = 0 OR next_available_calling_time = "Goal Appointment Reached") AND next_available_calling_time NOT IN("Cancelled", "Future Start Date", "Blank Start Date")');
		$greyCriteria->order = 'priority DESC';
		
		if(isset($_REQUEST['skill_id']))
		{
			$campaignSkillIds[] = $skill_id;
			
			$greyCriteria->compare('skill_id', $skill_id);
		}
		
		if( $campaign_id != null )
		{
			if( $campaignSkills )
			{
				$greyCriteria->addInCondition('skill_id', $campaignSkillIds);
			}
		}
		
		
		$customerGreyQueues = CustomerQueueViewer::model()->findAll($greyCriteria); 
		
		
		$customerQueueViewerBoosts = CustomerQueueViewerBoost::model()->findAll(array(
			'condition' => 'status = 1 AND is_boost_triggered = 1',
		)); 
		
		$cqvBoostHolder = array();
		if(!empty($customerQueueViewerBoosts))
		{
			foreach($customerQueueViewerBoosts as $customerQueueViewerBoost)
			{
				$cqvBoostHolder[$customerQueueViewerBoost->customer_id][$customerQueueViewerBoost->skill_id] = $customerQueueViewerBoost->attributes;
			}
		}
		
		
		$queueSkillList = array();
		
		$criteria = new CDbCriteria;
		$criteria->group = 'skill_id';
		$criteria->condition = 'status=1';
		
		if( $campaign_id != null )
		{
			$criteria->addInCondition('skill_id', $campaignSkillIds);
		}
		
		$cqvs = CustomerQueueViewer::model()->findAll($criteria);
		
		// $queueSkillList = CHtml::listData($cqvs,'skill_id','skill_name');
		
		if( $cqvs )
		{
			foreach( $cqvs as $cqv )
			{
				$assignedAgentCount = AccountSkillAssigned::model()->count(array(
					'with' => 'account',
					'condition' => 't.skill_id = :skill_id AND account.status=1',
					'params' => array(
						':skill_id' => $cqv->skill_id
					),
				));
				
				$queueSkillList[$cqv->skill_id] = $cqv->skill_name . ' ('.$assignedAgentCount.')';
			}
		}
		
		$queueCampaignList = array();
		
		$criteria = new CDbCriteria;
		$criteria->addCondition('status = 1 AND is_deleted=0');
		$campaigns = Campaign::model()->findAll($criteria);
		
		$queueCampaignList = CHtml::listData($campaigns,'id','campaign_name');
		
		if( isset($_POST['ajax']) )
		{
			$html = $this->renderPartial('ajaxIndex', array(
				'customerWhiteQueues' => $customerWhiteQueues,
				'customerGreyQueues' => $customerGreyQueues,
				'cqvBoostHolder' => $cqvBoostHolder,
				'queueSkillList' => $queueSkillList,
				'queueCampaignList' => $queueCampaignList,
				'skill_id' => $skill_id,
				'campaign_id' => $campaign_id,
				'campaignSkillIds' => $campaignSkillIds,
				'is_header' => $is_header,
			), true);
			
			echo json_encode(array(
				'status' => 'success',
				'html' => $html
			));
			
			Yii::app()->end();
		}
		else
		{
			$this->layout='main-no-navbar';
			
			$this->render('index', array(
				'customerWhiteQueues' => $customerWhiteQueues,
				'customerGreyQueues' => $customerGreyQueues,
				'cqvBoostHolder' => $cqvBoostHolder,
				'queueSkillList' => $queueSkillList,
				'queueCampaignList' => $queueCampaignList,
				'skill_id' => $skill_id,
				'campaign_id' => $campaign_id,
				'campaignSkillIds' => $campaignSkillIds,
				'is_header' => $is_header,
			));
		}
	}
	
	
	public function actionRemove()
	{
		$result = array(
			'status' => '',
			'message' => '',
			'html' => '',
		);
		
		if( isset($_POST['ajax']) && isset($_POST['customer_id']) )
		{
			$model = CustomerQueueViewer::model()->find(array(
				'condition' => 'customer_id = :customer_id',
				'params' => array(
					':customer_id' => $_POST['customer_id']
				),
			));
			
			if( isset($_POST['CustomerQueueViewer']) )
			{
				if( !empty($_POST['CustomerQueueViewer']['removal_start_date']) )
				{
					$model->removal_start_date = $_POST['CustomerQueueViewer']['removal_start_date'];
				}
				else
				{
					$model->removal_start_date = null;
				}
				
				if( !empty($_POST['CustomerQueueViewer']['removal_end_date']) )
				{
					$model->removal_end_date = $_POST['CustomerQueueViewer']['removal_end_date'];
				}
				else
				{
					$model->removal_end_date = null;
				}
			
				if( $model->save(false) )
				{
					$result['status'] = 'success';
				}
			}
			
			$html = $this->renderPartial('removalForm', array(
				'model' => $model,
			), true);
			
			$result['html'] = $html;
			$result['status'] = 'success';
		}
		
		echo json_encode($result);
	}
	
	public function actionAddCustomerBoost($customer_id, $skill_id)
	{ 
		$criteria = new CDbCriteria;
		$criteria->compare('customer_id', $customer_id);
		$criteria->compare('skill_id', $skill_id);
		
		$customerSkill = CustomerSkill::model()->find($criteria);
		
		if($customerSkill === null)
		{
			throw new CHttpException('403', 'Page not found');
		}
		
		$customer = $customerSkill->customer;
		$skill = $customerSkill->skill;
		
		$criteria = new CDbCriteria;
		$criteria->compare('customer_id', $customer_id);
		$criteria->compare('skill_id', $skill_id);
		$criteria->compare('status', CustomerQueueViewerBoost::STATUS_ACTIVE);
		
		$model = CustomerQueueViewerBoost::model()->find($criteria);
		
		if($model === null)
		{
			$model = new CustomerQueueViewerBoost;
			$model->customer_id = $customer_id;
			$model->skill_id = $skill_id;
			$model->type = 1;
		}
		
		if (isset($_POST['ajax']) && $_POST['ajax'] == "boost-form") {
			
			if($_POST['CustomerQueueViewerBoost']['type'] == 2)
				$model->setScenario('scheduledType');
			
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
		
		if(isset($_POST['CustomerQueueViewerBoost']))
		{
			$model->attributes = $_POST['CustomerQueueViewerBoost'];
			$model->status = CustomerQueueViewerBoost::STATUS_ACTIVE;
			
			
			
			//CANCEL BOOST
			if(isset($_POST['cancelBtnField']) && $_POST['cancelBtnField'] == "true")
			{
				$model->status = CustomerQueueViewerBoost::STATUS_INACTIVE;
				if($model->save())
				{
					$response = array(
						'success' => true,
						'message' => 'Saving customer boost canceled successfully!',
					);
				}
				else
				{
					$response = array(
						'success' => false,
						'message' => 'Saving customer boost canceled error!',
					);
				}
			}
			else // create/update  boost entry
			{
				if($model->save())
				{
					$response = array(
						'success' => true,
						'message' => 'Saving customer boost successful!',
					);
				}
				else
				{
					$response = array(
						'success' => false,
						'message' => 'Saving customer boost error!',
					);
				}
			}
			
			echo CJSON::encode($response);
			Yii::app()->end();
		}
		
		Yii::app()->clientscript->scriptMap['jquery.min.js'] = false;
		
		$this->renderPartial('boostForm',array(
			'customer' => $customer,
			'skill' => $skill,
			'model' => $model,
			'actionController' => Yii::app()->createUrl('/queueViewer/addCustomerBoost',array('customer_id' => $customer_id, 'skill_id' => $skill_id)),
		),false,true);
	}
	
	public function actionForceQueue()
	{ 
		$result = array('status'=>'error');
		
		$authAccount = Yii::app()->user->account;
		
		if( in_array($authAccount->id, array(1, 2)) )
		{
			$customerPriorityQueryOngoing = CustomerQueueViewerSettings::model()->findByPk(3);	
			$customerPriorityQueryOngoing->value = 0;
			
			$customerPriorityCurrentOffset = CustomerQueueViewerSettings::model()->findByPk(2);
			$customerPriorityCurrentOffset->value = 0;
			
			if( $customerPriorityQueryOngoing->save(false) && $customerPriorityCurrentOffset->save(false) )
			{
				$result['status'] = 'success';
			}
		}
		
		echo json_encode($result);
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
		
		// echo '<pre>';
		
		$workdays = array_diff($workdays, $holidays);
		
		// echo 'count: ' . count($workdays);
		
		// echo '<br><br>';
		
		// foreach( $workdays as $workday )
		// {
			// echo date('m/d/Y', $workday);
			// echo '<br />';
		// }
		
		// exit;
		
		
		if($returnType == 'array')
		{
			return $workdays;
		}
		else
		{
			return count($workdays);
		}
	}
	
	public function actionExport()
	{
		ini_set('memory_limit', '2048M');
		set_time_limit(0); 
			
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
			'A' => 'Customer Name',
			'B' => 'Skill',
			'C' => 'Goal/Lead',
			'D' => 'Priority Reset Date',
			'E' => 'Priority',
			'F' => 'Pace',
			'G' => 'Current Goals',
			'H' => 'Current Dials',
			'I' => 'Leads Callable Now',
			'J' => 'Leads Not Callable Now',
			'K' => 'Total Potential',
			'L' => 'Next Available Calling Time',
			'M' => 'Available Calling Blocks',
			'N' => 'Call Agent',
			'O' => 'Dials until Re-evaluation',
		);
		
		foreach($headers as $column => $val)
		{		
			$objPHPExcel->getActiveSheet()->SetCellValue($column.$ctr, $val);
			$objPHPExcel->getActiveSheet()->getStyle($column.$ctr)->applyFromArray(array(
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				),
				'font'  => array(
					'bold' => true,
					'name'  => 'Calibri',
				),
			));
		}
		$ctr++;
		
		$customerQueuesArray = array();
		
		$whiteCriteria = new CDbCriteria;
		$whiteCriteria->addCondition('status = 1 AND available_leads > 0 AND next_available_calling_time != "Goal Appointment Reached"');
		$whiteCriteria->order = 'priority DESC';
		
		if(!empty($_REQUEST['skill_id']))
			$whiteCriteria->compare('skill_id', $_REQUEST['skill_id']);
		
		//get all customer queues
		$customerWhiteQueues = CustomerQueueViewer::model()->findAll($whiteCriteria); 
		
		if( $customerWhiteQueues )
		{
			foreach( $customerWhiteQueues as $customerWhiteQueue  )
			{					
				$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $customerWhiteQueue->customer_name );
				$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $customerWhiteQueue->skill_name );
				$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $customerWhiteQueue->fulfillment_type );
				$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $customerWhiteQueue->priority_reset_date );
				$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $customerWhiteQueue->priority );
				$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $customerWhiteQueue->pace );
				$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $customerWhiteQueue->fulfillment_type == 'Goal' ? $customerWhiteQueue->current_goals : '' );
				$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $customerWhiteQueue->current_dials );
				$objPHPExcel->getActiveSheet()->SetCellValue('I'.$ctr, $customerWhiteQueue->available_leads );
				$objPHPExcel->getActiveSheet()->SetCellValue('J'.$ctr, $customerWhiteQueue->not_completed_leads );
				$objPHPExcel->getActiveSheet()->SetCellValue('K'.$ctr, $customerWhiteQueue->total_potential_dials );
				$objPHPExcel->getActiveSheet()->SetCellValue('L'.$ctr, $customerWhiteQueue->next_available_calling_time );
				$objPHPExcel->getActiveSheet()->SetCellValue('M'.$ctr, strtotime($customerWhiteQueue->start_date) > time() ? 'Future Start Date' : $customerGreyQueue->next_available_calling_time );
				$objPHPExcel->getActiveSheet()->SetCellValue('N'.$ctr, $customerWhiteQueue->call_agent );
				$objPHPExcel->getActiveSheet()->SetCellValue('O'.$ctr, $customerWhiteQueue->dials_until_reset );
				
				$ctr++;
			}
			
			
		}
		
		$greyCriteria = new CDbCriteria;
		$greyCriteria->addCondition('(status = 2 OR available_leads = 0 OR next_available_calling_time = "Goal Appointment Reached") AND next_available_calling_time NOT IN("Cancelled", "Future Start Date", "Blank Start Date")');
		$greyCriteria->order = 'priority DESC';
		
		if(!empty($_REQUEST['skill_id']))
			$greyCriteria->compare('skill_id', $_REQUEST['skill_id']);
		
		$customerGreyQueues = CustomerQueueViewer::model()->findAll($greyCriteria); 
		
		if( $customerGreyQueues )
		{
			foreach( $customerGreyQueues as $customerGreyQueue  )
			{					
				$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $customerGreyQueue->customer_name );
				$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $customerGreyQueue->skill_name );
				$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $customerGreyQueue->fulfillment_type );
				$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $customerGreyQueue->priority_reset_date );
				$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $customerGreyQueue->priority );
				$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $customerGreyQueue->pace );
				$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $customerGreyQueue->fulfillment_type == 'Goal' ? $customerGreyQueue->current_goals : '' );
				$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $customerGreyQueue->current_dials );
				$objPHPExcel->getActiveSheet()->SetCellValue('I'.$ctr, $customerGreyQueue->available_leads );
				$objPHPExcel->getActiveSheet()->SetCellValue('J'.$ctr, $customerGreyQueue->not_completed_leads );
				$objPHPExcel->getActiveSheet()->SetCellValue('K'.$ctr, $customerGreyQueue->total_potential_dials );
				$objPHPExcel->getActiveSheet()->SetCellValue('L'.$ctr,  strtotime($customerGreyQueue->start_date) > time() ? 'Future Start Date' : $customerGreyQueue->next_available_calling_time );
				$objPHPExcel->getActiveSheet()->SetCellValue('M'.$ctr, $customerGreyQueue->available_calling_blocks );
				$objPHPExcel->getActiveSheet()->SetCellValue('N'.$ctr, $customerGreyQueue->call_agent );
				$objPHPExcel->getActiveSheet()->SetCellValue('O'.$ctr, $customerGreyQueue->dials_until_reset );
				
				$ctr++;
			}
			
			
		}
		
		$filename= 'QueueViewer_'.date("Y-m-d");
		header('Content-type: text/csv');
		header('Content-Disposition: attachment; filename="'.$filename.'.csv"'); 
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
		
		header('Cache-Control: max-age=0');
		
		$objWriter->save('php://output');
	}
}

?>