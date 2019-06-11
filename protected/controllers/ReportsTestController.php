<?php 

ini_set('memory_limit', '10000M');
set_time_limit(0);

class ReportsTestController extends Controller
{
	public function accessRules()
	{
		return array(
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('cronSendEmails'),
				'users'=>array('*'),
			),
		);
	}
	
	public function actionIndex()
	{
		$this->render('index');	
	}
	
	public function getCustomerContractCreditAndSubsidy($customer, $contract, $fromDate = null, $toDate = null)
	{
		$fromTime = date("Y-m-d H:i:s",strtotime($fromDate));
		$fromTimeToStr = strtotime($fromTime);
		
		$contractCreditSubsidys = array();
		$customerSkills = CustomerSkill::model()->findAll(array(
			'with' => 'customer',
			'condition' => '
				t.customer_id = :customer_id AND t.contract_id = :contract_id
				AND t.status=1 
				AND customer.status=1
				AND customer.is_deleted=0
			',
			'params' => array(
				':customer_id' => $customer->id,
				':contract_id' => $contract->id,
			),

		));
		
		foreach($customerSkills as $customerSkill)
		{
			$customerRemoved = CustomerBillingWindowRemoved::model()->find(array(
				'condition' => '
					customer_id = :customer_id 
					AND skill_id = :skill_id 
					AND MONTH(date_created) = MONTH(:time)
					AND YEAR(date_created) = YEAR(:time)
				',
				'params' => array(
					':customer_id' => $customerSkill->customer_id,
					':skill_id' => $customerSkill->skill_id,
					':time' => $fromTime,
				),
			));
				
			if( isset($customerSkill->contract) && $customerSkill && $fromTimeToStr >= strtotime($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00' && empty($customerRemoved) )
			{
				$contract = $customerSkill->contract;
				$contractCreditSubsidys[$contract->id]['contract_name'] = $contract->contract_name;
				$contractCreditSubsidys[$contract->id]['totalCreditAmount'] = 0;
				$contractCreditSubsidys[$contract->id]['totalSubsidyAmount'] = 0;
				
				
				$totalLeads = 0;
				$totalAmount = 0;
				$subsidyAmount = 0;
				$month = '';
				$latestTransactionType = '';
				$latestTransactionStatus = '';
				
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
										$totalAmount += $customerSkillLevelArrayGroup->quantity * $subsidyLevel['amount'];
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
								':year' => date('Y', $fromTimeToStr),
								':month' => date('m', $fromTimeToStr),
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
										$totalAmount += $subsidyLevel['amount'];
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
								':year' => date('Y', $fromTimeToStr),
								':month' => date('m', $fromTimeToStr),
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
				
					$contractCreditSubsidys[$contract->id]['totalAmount'] = $totalAmount;
					
					$customerSkillSubsidyLevel = CustomerSkillSubsidyLevel::model()->find(array(
						'condition' => 'customer_id = :customer_id AND customer_skill_id = :customer_skill_id',
						'params' => array(
							':customer_id' => $customerSkill->customer_id,
							':customer_skill_id' => $customerSkill->id,
						),
					));
					
					$customerSkillSubsidy = CustomerSkillSubsidy::model()->find(array(
						'condition' => 'customer_id = :customer_id AND customer_skill_id = :customer_skill_id',
						'params' => array(
							':customer_id' => $customerSkill->customer_id,
							':customer_skill_id' => $customerSkill->id,
						),
					));
					
					// if( $customerSkillSubsidyLevel )
					if( !empty($customerSkillSubsidyLevel) && !empty($customerSkillSubsidy) && $customerSkillSubsidy->status == CustomerSkillSubsidy::STATUS_ACTIVE )
					{
						$subsidy = CompanySubsidyLevel::model()->find(array(
							'condition' => 'id = :id AND type="%"',
							'params' => array(
								':id' => $customerSkillSubsidyLevel->subsidy_level_id,
							),
						));
						
						if( $subsidy )
						{
							$subsidyPercent = $subsidy->value;
							
							$subsidyPercentInDecimal = $subsidyPercent / 100;

							if( $subsidyPercentInDecimal > 0 )
							{
								$subsidyAmount = $subsidyPercentInDecimal * $totalAmount;
								$contractCreditSubsidys[$contract->id]['totalSubsidyAmount'] = $subsidyAmount;
								
							}
						}
					}
				}
				
				$totalCreditAmount = 0;
				$customerCredits = CustomerCredit::model()->findAll(array(
					'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND status=1',
					'params' => array(
						':customer_id' => $customerSkill->customer_id,
						':contract_id' => $customerSkill->contract_id,
					),
				));
				
				if( $customerCredits )
				{
					foreach( $customerCredits as $customerCredit )
					{
						$creditStartDate = date('Y-'.$customerCredit->start_month.'-1');
						
						if( $customerCredit->type == 2 ) //month range
						{
							$creditEndDate = date('Y-'.$customerCredit->end_month.'-t');
						}
						else
						{
							$creditEndDate = date('Y-'.$customerCredit->start_month.'-t');
						}
						
						// if( (time() >= strtotime($creditStartDate)) && (time() <= strtotime($creditEndDate)) )
						// {
							// $totalCreditAmount += $customerCredit->amount;
						// }
						
						// $monthBillingPeriod = explode(' ',$billing_period);
						// $monthPeriod = date('m', strtotime("$monthBillingPeriod[0] 1 ".date('Y')));
						$monthPeriod = date('m', $fromTimeToStr);
						$startDayOfBillingPeriod = date("Y-m-d",strtotime(date('Y')."-".$monthPeriod."-1"));
						$lastDayOfBillingPeriod = date("Y-m-t", strtotime($startDayOfBillingPeriod));
						
						if( (strtotime($startDayOfBillingPeriod) >= strtotime($creditStartDate)) && (strtotime($lastDayOfBillingPeriod) <= strtotime($creditEndDate)) )
						{
							$totalCreditAmount += $customerCredit->amount;
						}
					}
				}
				
				
				$contractCreditSubsidys[$contract->id]['totalCreditAmount'] = $totalCreditAmount;
				
				$totalReducedAmount = ($totalAmount - $totalCreditAmount - $subsidyAmount);
				if( $totalReducedAmount < 0 )
					$totalReducedAmount = 0;
				
				$contractCreditSubsidys[$contract->id]['totalReducedAmount'] = $totalReducedAmount;
			}
		
		}
	
		return $contractCreditSubsidys;
	}
	
	public function actionReports($page='')
	{
		$contractId = isset($_POST['contractId']) ? $_POST['contractId'] : '';
		$dateFilterStart = isset($_REQUEST['dateFilterStart']) ? $_REQUEST['dateFilterStart'] : '';
		$dateFilterEnd = isset($_REQUEST['dateFilterEnd']) ? $_REQUEST['dateFilterEnd'] : '';
		
		$models = array();
		$models2 = array();
		$itemView = '';
		
		if( $page == 'customerContactInfo' )
		{
			$itemView = '_customer_contact_info_list';
			
			$models = Customer::model()->findAll(array(
				'condition' => 'company_id NOT IN("17", "18", "23") AND is_deleted=0',
				'order' => 'lastname ASC',
			));
		}
		
		if( $page == 'creditCardTransactions' )
		{
			$itemView = '_credit_card_transaction_list';
			
			if( $dateFilterStart != '' && $dateFilterEnd != '' )
			{
				$models = CustomerBilling::model()->findAll(array(
					'condition' => 'DATE(date_created) >= :dateFilterStart AND DATE(date_created) <= :dateFilterEnd',
					'params' => array(
						':dateFilterStart' => date('Y-m-d', strtotime($dateFilterStart)),
						':dateFilterEnd' => date('Y-m-d', strtotime($dateFilterEnd)),
					),
					'order' => 'date_created DESC',
				));
			}
			elseif( $dateFilterStart != '' )
			{
				$models = CustomerBilling::model()->findAll(array(
					'condition' => 'DATE(date_created) >= :dateFilterStart',
					'params' => array(
						':dateFilterStart' => date('Y-m-d', strtotime($dateFilterStart)),
					),
				));
			}
			else
			{
				$models = CustomerBilling::model()->findAll(array(
					'order' => 'date_created DESC',
				));
			}
			
		}
		
		if( $page == 'billingResults' )
		{
			$itemView = '_billing_results';
			
			if( $dateFilterStart != '' && $dateFilterEnd != '' )
			{
				$models = CustomerBilling::model()->findAll(array(
					'condition' => 'DATE(date_created) >= :dateFilterStart AND DATE(date_created) <= :dateFilterEnd',
					'params' => array(
						':dateFilterStart' => date('Y-m-d', strtotime($dateFilterStart)),
						':dateFilterEnd' => date('Y-m-d', strtotime($dateFilterEnd)),
					),
					'order' => 'date_created DESC',
				));
			}
			elseif( $dateFilterStart != '' )
			{
				$models = CustomerBilling::model()->findAll(array(
					'condition' => 'DATE(date_created) >= :dateFilterStart',
					'params' => array(
						':dateFilterStart' => date('Y-m-d', strtotime($dateFilterStart)),
					),
				));
			}
			else
			{
				$models = CustomerBilling::model()->findAll(array(
					'order' => 'date_created DESC',
				));
			}
			
		}
		
		if($page == 'billingProjections')
		{
			// print_r($_REQUEST);
			$itemView = '_billing_projections';
			
			if( $dateFilterStart != '' && $dateFilterEnd != '' )
			{
				$models = $this->getBillingProjections($dateFilterStart, $dateFilterEnd);
			}
			elseif( $dateFilterStart != '' )
			{
				$models = $this->getBillingProjections($dateFilterStart);
			}	
			
			// exit;
		}
		
		if( $page == 'contractLeads' )
		{
			$itemView = '_contract_lead_list';
			
			$addCondition = '';
			
			if( $contractId != '' )
			{
				$addCondition = ' AND contract_id = ' . $contractId;
			}
			
			if( $dateFilterStart != '' && $dateFilterEnd != '' )
			{
				$models = LeadCallHistory::model()->findAll(array(
					'condition' => 'DATE(start_call_time) >= :dateFilterStart AND DATE(start_call_time) <= :dateFilterEnd' . $addCondition,
					'params' => array(
						':dateFilterStart' => date('Y-m-d', strtotime($dateFilterStart)),
						':dateFilterEnd' => date('Y-m-d', strtotime($dateFilterEnd)),
					),
					'order' => 'start_call_time DESC',
				));
			}
			elseif( $dateFilterStart != '' )
			{
				$models = LeadCallHistory::model()->findAll(array(
					'condition' => 'DATE(start_call_time) >= :dateFilterStart' . $addCondition,
					'params' => array(
						':dateFilterStart' => date('Y-m-d', strtotime($dateFilterStart)),
					),
					'order' => 'start_call_time DESC',
				));
			}
			else
			{
				$models = LeadCallHistory::model()->findAll(array(
					'condition' => '1' . $addCondition,
					'order' => 'start_call_time DESC',
				));
			}
		}
		
		if( $page == 'agentPerformance' )
		{
			
			$email_address = isset($_POST['email_address']) ? $_POST['email_address'] : '';
			#$itemView = '_agent_performance_list';

			/* $models = array();
			
			if( isset($_POST['skillIds']) )
			{
				$models = Account::model()->findAll(array(
					'with' => array('accountUser'),
					'condition' => 't.account_type_id = :account_type_id AND t.status = :status AND t.id NOT IN (4, 5)',
					'params' => array(
						':account_type_id' => Account::TYPE_AGENT,
						':status' => Account::STATUS_ACTIVE,
					),
					'order' => 'accountUser.last_name DESC',
				));
			} */
			
			if(isset($_POST['email_address']))
			{
				if( $dateFilterStart != '' && $dateFilterEnd != '' && !empty($email_address))
				{
					if( filter_var($email_address, FILTER_VALIDATE_EMAIL) && (strtotime($dateFilterEnd) >= strtotime($dateFilterStart)) )
					{
						$agentPerformanceExportSettings = new AgentPerformanceExportSettings;
						$agentPerformanceExportSettings->email_address = $email_address;
						$agentPerformanceExportSettings->date_from = date("Y-m-d",strtotime($dateFilterStart));
						$agentPerformanceExportSettings->date_to = date("Y-m-d",strtotime($dateFilterEnd));
						
						$agentPerformanceExportSettings->ongoing = 0;
						$agentPerformanceExportSettings->done = 0;
						
						if($agentPerformanceExportSettings->save(false))
						{
							Yii::app()->user->setFlash('success','Request submitted successfully! You will receive the report by email.');
						}
					}						
						
					
				}
				else
				{
					Yii::app()->user->setFlash('error','Email Address and Date Range are required.');
				}
			}
		}
		
		if( $page == 'queueListing' )
		{
			$itemView = '_queue_listing_list';

			$models = CustomerQueueViewer::model()->findAll();
		}
		
		if( $page == 'stateFarm' )
		{
			$itemView = '_state_farm_list';

			if( $dateFilterStart != '' && $dateFilterEnd != '' )
			{
				$models = LeadCallHistory::model()->findAll(array(
					'condition' => 'company_id = :company_id AND DATE(start_call_time) >= :dateFilterStart AND DATE(start_call_time) <= :dateFilterEnd',
					'params' => array(
						':company_id' => 9,
						':dateFilterStart' => date('Y-m-d', strtotime($dateFilterStart)),
						':dateFilterEnd' => date('Y-m-d', strtotime($dateFilterEnd)),
					),
					'order' => 'start_call_time DESC',
				));
			}
			elseif( $dateFilterStart != '' )
			{
				$models = LeadCallHistory::model()->findAll(array(
					'condition' => 'company_id = :company_id AND DATE(start_call_time) >= :dateFilterStart',
					'params' => array(
						':company_id' => 9,
						':dateFilterStart' => date('Y-m-d', strtotime($dateFilterStart)),
					),
					'order' => 'start_call_time DESC',
				));
			}
			else
			{
				$models = LeadCallHistory::model()->findAll(array(
					'condition' => 'company_id = :company_id',
					'params' => array(
						':company_id' => 9,
					)
				));
			}
		}

		if( $page == 'pendingCalls' )
		{
			$itemView = '_pendingCalls';

			$models = LeadHopper::model()->findAll(array(
				'with' => 'lead',
				'condition' => 't.type=3 AND DATE(t.appointment_date) = DATE(NOW()) AND lead.id IS NOT NULL',
				// 'limit' => 50,
			));
		}
		
		if( $page == 'pendingCallsReschedule' )
		{
			$itemView = '_pendingCallsReschedule';

			$models = LeadHopper::model()->findAll(array(
				'with' => array('lead', 'calendarAppointment'),
				// 'condition' => 't.type=6 AND lead.id IS NOT NULL AND lead.number_of_dials < (skill.max_dials * 3) /* AND t.status IN ("READY","INCALL") */',
				'condition' => 't.type IN (6,7) AND lead.id IS NOT NULL AND calendarAppointment.date_updated >= "2016-06-01"',
				'order'=>'calendarAppointment.date_updated DESC',
				// 'limit' => 100,
			));
		}
		
		if( $page == 'customerWithFiles' )
		{
			$itemView = '_customer_with_files';
			
			$models = CustomerFile::model()->findAll(array(
				'with' => 'customer',
				'condition' => 't.status=1 AND customer.company_id NOT IN("17", "18", "23")',
				'order' => 't.date_created DESC',
			));
		}
		
		if( $page == 'employeeSummary' )
		{
			$itemView = '_employee_summary_list';
			
			$models = Account::model()->findAll(array(
				'with' => array('accountUser'),
				'condition' => 'accountUser.id IS NOT NULL',
				'params' => array(
					':status' => Account::STATUS_ACTIVE,
				),
				'order' => 'accountUser.last_name ASC',
			));
		}
		
		$modelProvider = new CArrayDataProvider($models, array(
			'pagination' => array(
				'pageSize' => 1000,
			),
		));
		
		$modelProvider2 = new CArrayDataProvider($models2, array(
			'pagination' => array(
				'pageSize' => 1000,
			),
		));
		

		$contracts = Contract::model()->findAll(array(
			'condition' => 'is_deleted=0',
		));
		
		$contractOptions = CHtml::listData( $contracts, 'id', 'contract_name');
		
		$skills = Skill::model()->findAll(array(
			'condition' => 'is_deleted=0',
		));
		
		$skillOptions = CHtml::listData( $skills, 'id', 'skill_name');
		
		$selectedSkills = array();
		
		if( !empty($_POST['skillIds']) )	
		{
			foreach( $_POST['skillIds'] as $skillId )
			{
				$selectedSkills[$skillId] = array('selected'=>'selected');
			}
		}
		
		$this->render('reports', array(
			'dataProvider' => $modelProvider,
			'dataProvider2' => $modelProvider2,
			'page' => $page,
			'itemView' => $itemView,
			'contractOptions' => $contractOptions,
			'skillOptions' => $skillOptions,
			'selectedSkills' => $selectedSkills,
			'contractId' => $contractId,
			'dateFilterStart' => $dateFilterStart,
			'dateFilterEnd' => $dateFilterEnd,
		));
	}
	
	
	public function actionExport($page, $selectedSkills='', $contractId='', $dateFilterStart='', $dateFilterEnd='')
	{
		// unregister Yii's autoloader
		spl_autoload_unregister(array('YiiBase', 'autoload'));
		
		// register PHPExcel's autoloader ... PHPExcel.php will do it
		$phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
		require_once($phpExcelPath . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'PHPExcel.php');
		
		// register Yii's autoloader again
		spl_autoload_register(array('YiiBase', 'autoload'));
		 
		// This requires Yii's autoloader
		
		$objPHPExcel = new PHPExcel();
		
		if( $page == 'customerContactInfo' )
		{
			$filename = 'Customer Contact Info';
			
			$models = Customer::model()->findAll(array(
				'condition' => 'company_id NOT IN("17", "18", "23") AND is_deleted=0',
				'order' => 'lastname ASC',
			));
			
			$ctr = 1;

			$headers = array(
				'A' => 'Agent ID',
				'B' => 'Name',
				'C' => 'Status',
				'D' => 'Company',
				'E' => 'Phone',
				'F' => 'Email Address',
				'G' => 'Address',
				'H' => 'City',
				'I' => 'State',
				'J' => 'Zip',
				'K' => 'Skills',
				'L' => 'Contracts',
				'M' => 'Quantity',
				'N' => 'Start Date',
				'O' => 'End Date',
				'P' => 'On Hold',
				'Q' => 'Off Hold',
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
		}
		
		if( $page == 'creditCardTransactions' )
		{
			$filename = 'Credit Card Transactions';
			
			if( $dateFilterStart != '' && $dateFilterEnd != '' )
			{
				$models = CustomerBilling::model()->findAll(array(
					'condition' => 'DATE(date_created) >= :dateFilterStart AND DATE(date_created) <= :dateFilterEnd',
					'params' => array(
						':dateFilterStart' => date('Y-m-d', strtotime($dateFilterStart)),
						':dateFilterEnd' => date('Y-m-d', strtotime($dateFilterEnd)),
					),
					'order' => 'date_created DESC',
				));
			}
			elseif( $dateFilterStart != '' )
			{
				$models = CustomerBilling::model()->findAll(array(
					'condition' => 'DATE(date_created) >= :dateFilterStart',
					'params' => array(
						':dateFilterStart' => date('Y-m-d', strtotime($dateFilterStart)),
					),
				));
			}
			else
			{
				$models = CustomerBilling::model()->findAll();
			}
			
			
			$ctr = 1;

			$headers = array(
				'A' => 'Date/Time',
				'B' => 'Agent ID',
				'C' => 'Customer Name',
				'D' => 'Company',
				'E' => 'Skill',
				'F' => 'Contract',
				'G' => 'Billing Cycle',
				'H' => 'Memo',
				'I' => 'Payment Method',
				'J' => 'Credit Card Type',
				'K' => 'Action',
				'L' => 'Original Amount',
				'M' => 'Billing Credit',
				'N' => 'Subsidy',
				'O' => 'Reduced Amount',
				'P' => 'Authorize Transaction ID',
				'Q' => 'User',
				'R' => 'Result',
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
		}
		
		if( $page == 'billingResults' )
		{
			$filename = 'Billing Results';
			
			if( $dateFilterStart != '' && $dateFilterEnd != '' )
			{
				$models = CustomerBilling::model()->findAll(array(
					'condition' => 'DATE(date_created) >= :dateFilterStart AND DATE(date_created) <= :dateFilterEnd',
					'params' => array(
						':dateFilterStart' => date('Y-m-d', strtotime($dateFilterStart)),
						':dateFilterEnd' => date('Y-m-d', strtotime($dateFilterEnd)),
					),
					'order' => 'date_created DESC',
				));
			}
			elseif( $dateFilterStart != '' )
			{
				$models = CustomerBilling::model()->findAll(array(
					'condition' => 'DATE(date_created) >= :dateFilterStart',
					'params' => array(
						':dateFilterStart' => date('Y-m-d', strtotime($dateFilterStart)),
					),
				));
			}
			else
			{
				$models = CustomerBilling::model()->findAll();
			}
			
			
			$ctr = 1;

			$headers = array(
				'A' => 'Date/Time',
				'B' => 'Agent ID',
				'C' => 'Customer Name',
				'D' => 'Company',
				'E' => 'Skill',
				'F' => 'Contract',
				'G' => 'Quantity',
				'H' => 'Billing Cycle',
				'I' => 'Memo',
				'J' => 'Payment Method',
				'K' => 'Credit Card Type',
				'L' => 'Action',
				'M' => 'Original Amount',
				'N' => 'Billing Credit',
				'O' => 'Subsidy',
				'P' => 'Reduced Amount',
				'Q' => 'Authorize Transaction ID',
				'R' => 'User',
				'S' => 'Result',
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
		}
		
		if( $page == 'contractLeads' )
		{
			$filename = 'Contract Leads';
			
			$addCondition = '';
			
			if( $contractId != '' )
			{
				$addCondition = ' AND contract_id = ' . $contractId;
			}
			
			if( $dateFilterStart != '' && $dateFilterEnd != '' )
			{
				$models = LeadCallHistory::model()->findAll(array(
					'condition' => 'DATE(start_call_time) >= :dateFilterStart AND DATE(start_call_time) <= :dateFilterEnd' . $addCondition,
					'params' => array(
						':dateFilterStart' => date('Y-m-d', strtotime($dateFilterStart)),
						':dateFilterEnd' => date('Y-m-d', strtotime($dateFilterEnd)),
					),
					'order' => 'start_call_time DESC',
				));
			}
			elseif( $dateFilterStart != '' )
			{
				$models = LeadCallHistory::model()->findAll(array(
					'condition' => 'DATE(start_call_time) >= :dateFilterStart' . $addCondition,
					'params' => array(
						':dateFilterStart' => date('Y-m-d', strtotime($dateFilterStart)),
					),
					'order' => 'start_call_time DESC',
				));
			}
			else
			{
				$models = LeadCallHistory::model()->findAll(array(
					'condition' => '1' . $addCondition,
					'order' => 'start_call_time DESC',
				));
			}
			
			$ctr = 1;
			
			$headers = array(
				'A' => 'Company',
				'B' => 'Customer ID',
				'C' => 'Company First Name',
				'D' => 'Customer Last Name',
				'E' => 'Customer State',
				'F' => 'Lead First Name',
				'G' => 'Lead Last Name',
				'H' => 'Lead Dial Count',
				'I' => 'Call Date',
				'J' => 'Call Time',
				'K' => 'Disposition',
				'L' => 'Agent Note',
				'M' => 'External Note',
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
		}
				
		if( $page == 'agentPerformance' )
		{
			$filename = 'Agent Performance';

			if( $selectedSkills != '' )
			{
				$models = Account::model()->findAll(array(
					'with' => array('accountUser'),
					'condition' => 't.account_type_id = :account_type_id AND t.status = :status AND t.id NOT IN (4, 5)',
					'params' => array(
						':account_type_id' => Account::TYPE_AGENT,
						':status' => Account::STATUS_ACTIVE,
					),
					'order' => 'accountUser.last_name DESC',
				));
			}
			else
			{
				$models = array();
			}
			
			$ctr = 1;
			
			$headers = array(
				'A' => 'Team',
				'B' => 'Agent First/Last Name',
				'C' => 'Total Hours',
				'D' => 'Primary Hours',
				'E' => 'Wrap Time',
				'F' => 'Outbound Dials',
				'G' => 'Voice Contact Dispositions',
				'H' => 'Appointments',
				'I' => 'Total dials per hour',
				'J' => 'Appointments/hour',
				'K' => 'Conversion rate',
				'L' => 'Child Skill Hours',
				'M' => 'Wrap Time',
				'N' => 'Outbound Dials',
				'O' => 'Voice Contact Dispositions',
				'P' => 'Total dials per hour',
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
		}

		if( $page == 'queueListing' )
		{
			$filename = 'Queue Listing';

			$models = CustomerQueueViewer::model()->findAll();
			
			$ctr = 1;
			
			$headers = array(
				'A' => 'Company',
				'B' => 'Customer Name',
				'C' => 'Skill',
				'D' => 'Contract',
				'E' => 'Total Callable',
				'F' => 'Callable Today',
				'G' => 'Future Callable',
				'H' => 'Dials',
				'I' => 'Cycle End Day',
				'J' => 'Fulfilment Type (Goal or Lead)',
				'K' => 'Contracted Leads/Goals',
				'L' => 'Current Goal/Dial Count',
				'M' => 'Pace',
				'N' => 'Total Potential',
				'O' => 'Priority',
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
		}	
		
		if( $page == 'stateFarm' )
		{
			$filename = 'SFcall_EngageX_' . date('YmdHi');
			
			$ctr = 1;
			
			$headers = array(
				'A' => 'AGT_FNAME;AGT_LNAME;AGT_CODE;AGT_ALIAS;CUST_FNAME;CUST_LNAME;PHONE;CONT_DATE;CONT_TIME;APPT_SET;APPT_DATE;APPT_TYPE;CALL_DISP',
			);
			
			foreach($headers as $column => $val)
			{		
				$objPHPExcel->getActiveSheet()->SetCellValue($column.$ctr, $val);
				$objPHPExcel->getActiveSheet()->getStyle($column.$ctr)->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
					'font'  => array(
						// 'bold' => true,
						'name'  => 'Calibri',
					),
				));
			}

			if( $dateFilterStart != '' && $dateFilterEnd != '' )
			{
				$models = LeadCallHistory::model()->findAll(array(
					'condition' => 'company_id = :company_id AND DATE(start_call_time) >= :dateFilterStart AND DATE(start_call_time) <= :dateFilterEnd',
					'params' => array(
						':company_id' => 9,
						':dateFilterStart' => date('Y-m-d', strtotime($dateFilterStart)),
						':dateFilterEnd' => date('Y-m-d', strtotime($dateFilterEnd)),
					),
					'order' => 'start_call_time DESC',
				));
			}
			elseif( $dateFilterStart != '' )
			{
				$models = LeadCallHistory::model()->findAll(array(
					'condition' => 'company_id = :company_id AND DATE(start_call_time) >= :dateFilterStart',
					'params' => array(
						':company_id' => 9,
						':dateFilterStart' => date('Y-m-d', strtotime($dateFilterStart)),
					),
					'order' => 'start_call_time DESC',
				));
			}
			else
			{
				$models = LeadCallHistory::model()->findAll(array(
					'condition' => 'company_id = :company_id',
					'params' => array(
						':company_id' => 9,
					)
				));
			}
		}
			
		if( $page == 'pendingCalls' )
		{
			$filename = 'Confirm';
			
			$ctr = 1;
			
			$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, 'Confirmations');
			
			$ctr = 3;
			
			$headers = array(
				'A' => 'Company',
				'B' => 'Customer Name',
				'C' => 'Status',
				'D' => 'Lead Name',
				'E' => 'Lead Phone',
				'F' => 'Appointment Date/Time',
				'G' => 'Timezone of lead',
				'H' => 'Date Added',
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

			$models = LeadHopper::model()->findAll(array(
				'with' => 'lead',
				'condition' => 't.type=3 AND DATE(t.appointment_date) = DATE(NOW()) AND lead.id IS NOT NULL',
				// 'limit' => 50,
			));
		}
		
		if( $page == 'pendingCallsReschedule' )
		{
			$filename = 'Reschedules ';
			
			$ctr = 1;
			
			$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, 'Reschedules');
			
			$ctr = 3;
			
			$headers = array(
				'A' => 'Company',
				'B' => 'Customer Name',
				'C' => 'Status',
				'D' => 'Lead Name',
				'E' => 'Lead Phone',
				'F' => 'Timezone of Lead',
				'G' => 'Date Added',
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
			
			// $models = LeadHopper::model()->findAll(array(
				// 'with' => array('lead','list','list.skill', 'calendarAppointment'),
				// 'condition' => 't.type=6 AND lead.id IS NOT NULL AND lead.number_of_dials < (skill.max_dials * 3) /* AND t.status IN ("READY","INCALL") */',
				// 'order'=>'calendarAppointment.date_updated DESC',
				// 'limit' => 100,
			// ));
			
			$models = LeadHopper::model()->findAll(array(
				'with' => array('lead', 'calendarAppointment'),
				// 'condition' => 't.type=6 AND lead.id IS NOT NULL AND lead.number_of_dials < (skill.max_dials * 3) /* AND t.status IN ("READY","INCALL") */',
				'condition' => 't.type IN (6,7) AND lead.id IS NOT NULL AND DATE(calendarAppointment.date_updated) >= "2016-06-01"',
				'order'=>'calendarAppointment.date_updated DESC',
				// 'limit' => 100,
			));
		}
		
		if( $page == 'employeeSummary' )
		{
			$filename = 'Employee Summary';
			
			$ctr = 1;
			
			$headers = array(
				'A' => 'Employee #',
				'B' => 'First Name',
				'C' => 'Last Name',
				'D' => 'Employee Classification',
				'E' => 'Status',
				'F' => 'Start Date',
				'G' => 'Term Date',
				'H' => 'Phone Extension',
				'I' => 'Job Title',
				'J' => 'Security Group',
				'K' => 'Schedule Hours per Week',
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
			
			$models = Account::model()->findAll(array(
				'with' => array('accountUser'),
				'condition' => 'accountUser.id IS NOT NULL',
				'params' => array(
					':status' => Account::STATUS_ACTIVE,
				),
				'order' => 'accountUser.last_name ASC',
			));
		}
		
		if( $page == 'agentPerformanceLite' )
		{
			$filename = 'Agent Performance';
			
			$ctr = 1;
			
			$headers = array(
				'A' => 'Agent Name',
				'B' => 'Status',
				'C' => 'Total Hours',
				'D' => 'Dials',
				'E' => 'Dials/Hour',
				'F' => 'Appointments',
				'G' => 'Appts/Hour',
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

			$dateFilterStart = date('Y-m-d 00:00:00', strtotime($dateFilterStart));
			$dateFilterEnd = date('Y-m-d 23:59:59', strtotime($dateFilterEnd));
			
			if( !empty($_GET['dateFilterStartTime']) )
			{
				$dateFilterStart = date('Y-m-d', strtotime($dateFilterStart)).' '.date('H:i:s', strtotime($_GET['dateFilterStartTime']));
			}
			
			if( !empty($_GET['dateFilterEndTime']) )
			{
				$dateFilterEndTime = date('H:i:s', strtotime('+1 hour', strtotime($_GET['dateFilterEndTime'])));
				
				$dateFilterEnd = date('Y-m-d', strtotime($dateFilterEnd)).' '.$dateFilterEndTime;
			}
			
			$sql = "
				SELECT a.id as agent_id, CONCAT(au.`first_name`, ' ', au.`last_name`) AS agent_name, a.status AS agent_status,
				(
					SELECT SUM(
						CASE WHEN time_out IS NOT NULL THEN TIME_TO_SEC(TIMEDIFF(time_out, time_in))/3600
							ELSE TIME_TO_SEC(TIMEDIFF(DATE_SUB(NOW(), INTERVAL 1 HOUR), time_in))/3600 
						END
					)
					FROM ud_account_login_tracker alt
					WHERE alt.account_id = a.`id`
					AND alt.time_in >= '".$dateFilterStart."' 
					AND alt.time_in <= '".$dateFilterEnd."'
					AND alt.status !=4 
				) AS total_hours,
				(
					SELECT COUNT(lch.id) 
					FROM ud_lead_call_history lch
					LEFT JOIN ud_lists uls ON uls.id = lch.list_id
					WHERE lch.agent_account_id = a.`id`
					AND lch.start_call_time >= '".$dateFilterStart."' 
					AND lch.start_call_time <= '".$dateFilterEnd."' 
					AND uls.skill_id IN (11,12,15,16,17,19,20,21,22,23,24,25,26,27,28,29,30,31)
					AND lch.status != 4
				) AS dials,
				(
					SELECT COUNT(lch.id) 
					FROM ud_lead_call_history lch
					LEFT JOIN ud_lists uls ON uls.id = lch.list_id
					LEFT JOIN ud_calendar_appointment ca ON ca.id = lch.calendar_appointment_id 
					WHERE lch.agent_account_id = a.`id`
					AND lch.start_call_time >= '".$dateFilterStart."'  
					AND lch.start_call_time <= '".$dateFilterEnd."'  
					AND uls.skill_id IN (11,12,15,16,17,19,20,21,22,23,24,25,26,27,28,29,30,31)
					AND lch.disposition='Appointment Set'
					AND lch.status != 4
					AND lch.is_skill_child=0
					AND ca.id IS NOT NULL
					AND ca.title IN ('INSERT APPOINTMENT', 'APPOINTMENT SET', 'CANCEL APPOINTMENT', 'RESCHEDULE APPOINTMENT', 'LOCATION CONFLICT', 'SCHEDULE CONFLICT')
				) AS appointments
				FROM ud_account a
				LEFT JOIN ud_account_user au ON au.`account_id` = a.`id`
				WHERE a.`account_type_id` = 2
				AND a.`id` NOT IN (4, 5)
				ORDER BY au.last_name ASC
			";
			
			$connection = Yii::app()->db;
			$command = $connection->createCommand($sql);
			$models = $command->queryAll();
		}
		
		if( $page == 'waxieCampaign' )
		{
			$filename = 'Waxie Campaign';
			
			$models = Customer::model()->findAll(array(
				'condition' => 'company_id NOT IN("17", "18", "23") AND is_deleted=0',
				'order' => 'lastname ASC',
			));
			
			$ctr = 1;

			$headers = array(
				'A' => 'Lead',
				'B' => 'Phone#',
				'C' => '#Dials',
				'D' => 'Disposition',
				'E' => 'Agent Notes',
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
		}
		
		if( $page == 'genericSkill' )
		{
			$filename = 'Generic Skill';
			
			$sql = "
				SELECT 
					co.company_name as company_name,
					CONCAT (c.firstname, ' ', c.lastname) AS customer_name,
					lch.lead_phone_number AS lead_phone,
					ld.first_name AS lead_first_name, 
					ld.last_name AS lead_last_name,
					ld.partner_first_name AS partner_first_name,
					ld.partner_last_name AS partner_last_name,
					lch.is_skill_child,
					lch.disposition,
					lch.agent_note,
					CONCAT(au.first_name, ' ', au.last_name) AS agent,
					lch.start_call_time as call_date, 
					lch.callback_time as callback_date
				FROM ud_lead_call_history lch 
				LEFT JOIN ud_customer c ON lch.customer_id = c.id
				LEFT JOIN ud_company co ON co.id = c.company_id
				LEFT JOIN ud_lists ls ON ls.id = lch.list_id
				LEFT JOIN ud_lead ld ON ld.id = lch.lead_id
				LEFT JOIN ud_account_user au ON au.account_id = lch.agent_account_id
				WHERE ls.skill_id = '".$selectedSkills."'
				AND lch.disposition IS NOT NULL 
				AND lch.start_call_time >= '".date('Y-m-d 00:00:00', strtotime($dateFilterStart))."' 
				AND lch.start_call_time <= '".date('Y-m-d 23:59:59', strtotime($dateFilterEnd))."'
				AND lch.status !=4 
				ORDER BY lch.start_call_time DESC
			";
			
			$connection = Yii::app()->db;
			$command = $connection->createCommand($sql);
			$models = $command->queryAll();
			
			$ctr = 1;
									
			$headers = array(
				'A' => 'Company',
				'B' => 'Customer',
				'C' => 'Lead Phone',
				'D' => 'Lead First',
				'E' => 'Lead Last',
				'F' => 'Partner First',
				'G' => 'Partner Last',
				'H' => 'Date/Time',
				'I' => 'Skill',
				'J' => 'Disposition',
				'K' => 'Callback Date/Time',
				'L' => 'Disposition Note',
				'M' => 'Agent',
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
		}
		
		if( $page == 'lowNames' )
		{
			$models = CustomerQueueViewer::model()->findAll(array(
				'condition' => '
					company NOT IN ("Training Company", "Test Company", "Engagex Inside Sales", "Waxie", "Mountain View Network", "Audigy Group", "Graton", "GunLake")
					AND next_available_calling_time NOT IN ("On Hold", "Cancelled", "Removed")
					',
				// 'limit' => 50,
			));

			$filename = 'Low Names';
			
			$ctr = 1;
			
			$headers = array(
				'A' => 'Company',
				'B' => 'Customer ID',
				'C' => 'First Name',
				'D' => 'Last Name',
				'E' => 'Phone Number',
				'F' => 'Email Address',
				'G' => 'Staff Email Address',
				'H' => 'Skill',
				'I' => 'Start Date',
				'J' => 'End Date',
				'K' => 'Qty',
				'L' => 'Current Goal Count',
				'M' => 'Dials in Current Month',
				'N' => 'Callable Now',
				'O' => 'Recertifiable',
				'P' => 'Names waiting',
				'Q' => 'Needs Names',
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
		}
		
		if( $page == 'billingProjections' )
		{
			$models = CustomerQueueViewer::model()->findAll(array(
				'with' => 'customer',
				'order' => 'customer.lastname ASC',
			));
			
			$filename = 'Billing Projections';
			
			$ctr = 3;
			
			$headers = array(
				'A' => 'Agent ID',
				'B' => 'Status',
				'C' => 'Start Date',
				'D' => 'End Date',
				'E' => 'Customer Name',
				'F' => 'Company',
				'G' => 'Skill',
				'H' => 'Contract',
				'I' => 'Quantity',
				'J' => 'Billing Cycle',
				'K' => 'Payment Method',
				'L' => 'Credit Card Type',
				'M' => 'Action',
				'N' => 'Original Amount',
				'O' => 'Billing Credit',
				'P' => 'Subsidy',
				'Q' => 'Reduced Amount',
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
		}
		
		if( $page == 'listImportLog' )
		{
			$models = CustomerHistory::model()->findAll(array(
				'condition' => '
					content LIKE"%Imported%"
					AND DATE(date_created) >= "'.date('Y-m-d', strtotime($dateFilterStart)).'" 
					AND DATE(date_created) <= "'.date('Y-m-d', strtotime($dateFilterEnd)).'"
				',
				'order' => 'date_created DESC',
			));
			
			$filename = 'List Import Log';
			
			$ctr = 1;
			
			$headers = array(
				'A' => 'Import Date/Time',
				'B' => 'User',
				'C' => 'Customer Name',
				'D' => 'Agent ID',
				'E' => 'List Name',
				'F' => 'Total',
				'G' => 'Imported Count',
				'H' => 'Duplicate Count',
				'I' => 'Bad Count',
				'J' => '% Not Imported',
				'K' => 'List Status',
				'L' => 'Email',
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
		}
		
		if( $page == 'growth' )
		{
			$filename = 'Change Log';

			$enrollments = array();
			$cancellations = array();
			$changes = array();
			
			$enrollmentsTotalAmount = 0;
			$cancellationsTotalAmount = 0;
			$changesTotalAmount = 0;
			
			$customerQuantityChanges = CustomerQueueViewer::model()->findAll(array(
				'with' => 'customer',
				'condition' => '
					customer.is_deleted=0
					AND company != "Test Company"
					AND contract_name NOT IN ("Audigy Surveys", "Audigy Group", "Tournament Reservations", "Training Company Contract", "Mountain View Network")
					AND history_contract_quantity_change_date IS NOT NULL
					AND DATE(history_contract_quantity_change_date) >= "'.date('Y-m-d', strtotime($dateFilterStart)).'" 
					AND DATE(history_contract_quantity_change_date) <= "'.date('Y-m-d', strtotime($dateFilterEnd)).'"
				',
				'order' => 'customer_last_name ASC',
			));
			
			$customerCreditChanges = CustomerQueueViewer::model()->findAll(array(
				'with' => 'customer',
				'condition' => '
					customer.is_deleted=0
					AND company != "Test Company"
					AND contract_name NOT IN ("Audigy Surveys", "Audigy Group", "Tournament Reservations", "Training Company Contract", "Mountain View Network")
					AND history_credit_added_date IS NOT NULL
					AND DATE(history_credit_added_date) >= "'.date('Y-m-d', strtotime($dateFilterStart)).'" 
					AND DATE(history_credit_added_date) <= "'.date('Y-m-d', strtotime($dateFilterEnd)).'"
				',
				'order' => 'customer_last_name ASC',
			));
			
			$customerSkillChanges = CustomerQueueViewer::model()->findAll(array(
				'with' => 'customer',
				'condition' => '
					customer.is_deleted=0
					AND company != "Test Company"
					AND contract_name NOT IN ("Audigy Surveys", "Audigy Group", "Tournament Reservations", "Training Company Contract", "Mountain View Network")
					AND history_hold_change_date IS NOT NULL
					AND DATE(history_hold_change_date) >= "'.date('Y-m-d', strtotime($dateFilterStart)).'" 
					AND DATE(history_hold_change_date) <= "'.date('Y-m-d', strtotime($dateFilterEnd)).'"
				',
				'order' => 'customer_last_name ASC',
			));
			
			$customerQueueViewers = CustomerQueueViewer::model()->findAll(array(
				'with' => 'customer',
				'condition' => '
					customer.is_deleted=0
					AND company != "Test Company"
					AND contract_name NOT IN ("Audigy Surveys", "Audigy Group", "Tournament Reservations", "Training Company Contract", "Mountain View Network")
					AND 
					(
						(
							account_date_created IS NOT NULL
							AND account_date_created != ""
							AND account_date_created != "0000-00-00"
							AND DATE(account_date_created) >= "'.date('Y-m-d', strtotime($dateFilterStart)).'" 
							AND DATE(account_date_created) <= "'.date('Y-m-d', strtotime($dateFilterEnd)).'"
						)
						OR
						(
							enrollment_date IS NOT NULL
							AND enrollment_date != ""
							AND enrollment_date != "0000-00-00"
							AND DATE(enrollment_date) >= "'.date('Y-m-d', strtotime($dateFilterStart)).'" 
							AND DATE(enrollment_date) <= "'.date('Y-m-d', strtotime($dateFilterEnd)).'"
						)
						OR
						(
							end_date IS NOT NULL
							AND end_date != ""
							AND end_date != "0000-00-00"
							AND DATE(history_end_date_changed) >= "'.date('Y-m-d', strtotime($dateFilterStart)).'" 
							AND DATE(history_end_date_changed) <= "'.date('Y-m-d', strtotime($dateFilterEnd)).'" 
						)
					)
				',
				'order' => 'customer_last_name ASC',
			));
								 
			if( $customerQueueViewers )
			{
				foreach( $customerQueueViewers as $customerQueueViewer )
				{
					$end_month = '';
			
					if( !empty($customerQueueViewer->end_date) && $customerQueueViewer->end_date != '0000-00-00' )
					{
						$end_month = date('m-d-Y', strtotime($customerQueueViewer->end_date));
					}
					
					$quantity = 0;
					$amount = 0;
					
					$dateEntered = '';
					$agentName = '';
					
					if( $end_month == '' )
					{
						$dateEntered = date('m-d-Y', strtotime($customerQueueViewer->enrollment_date));
					}
					else
					{
						$dateEntered = date('m-d-Y', strtotime($customerQueueViewer->history_end_date_changed));
						
						$agentName = $customerQueueViewer->history_end_date_changer;
					}
					
					$salesAgent = '';
					
					$salesReps = CustomerSalesRep::model()->findAll(array(
						'condition' => 'customer_id = :customer_id',
						'params' => array(
							':customer_id' => $customerQueueViewer->customer_id
						),
					));
					
					if( $salesReps )
					{
						foreach( $salesReps as $salesRep )
						{
							$salesAgent .= $salesRep->account->getFullName() . ', ';
						}
					}

					if( $end_month == '' )
					{
						$totalNet += $customerQueueViewer->contracted_amount;
						$enrollmentsTotalAmount += $customerQueueViewer->contracted_amount;
						
						$enrollments[ $customerQueueViewer->customer_id.'-'.$customerQueueViewer->skill_id ] = array(
							'date_entered' => $dateEntered,
							'sales_agent' => rtrim($salesAgent, ', '),
							'agent' => $agentName,
							'start_date' => date('m-d-Y', strtotime($customerQueueViewer->start_date)),
							'end_date' => $end_month,
							'company' => $customerQueueViewer->customer->company->company_name,
							'customer_name' => $customerQueueViewer->customer_name,
							'customer_id' => $customerQueueViewer->custom_customer_id,
							'skill' => $customerQueueViewer->skill_name,
							'contract' => $customerQueueViewer->contract_name,
							'quantity' => $customerQueueViewer->contracted_quantity,
							'amount' => $customerQueueViewer->contracted_amount 
						);
					}
					else
					{
						$totalNet -= $customerQueueViewer->contracted_amount;
						$cancellationsTotalAmount += $customerQueueViewer->contracted_amount;
						
						$cancellations[ $customerQueueViewer->customer_id.'-'.$customerQueueViewer->skill_id ] = array(
							'date_entered' => $dateEntered,
							'sales_agent' => rtrim($salesAgent, ', '),
							'agent' => $agentName,
							'start_date' => date('m-d-Y', strtotime($customerQueueViewer->start_date)),
							'end_date' => $end_month,
							'company' => $customerQueueViewer->customer->company->company_name,
							'customer_name' => $customerQueueViewer->customer_name,
							'customer_id' => $customerQueueViewer->custom_customer_id,
							'skill' => $customerQueueViewer->skill_name,
							'contract' => $customerQueueViewer->contract_name,
							'quantity' => $customerQueueViewer->contracted_quantity,
							'amount' => $customerQueueViewer->contracted_amount 
						);
					}
				}
			}

			if( $customerQuantityChanges )
			{
				foreach( $customerQuantityChanges as $customerQuantityChange )
				{
					if( $customerQuantityChange->history_contract_quantity_change_type == 'Upgrade' )
					{
						$totalNet += $customerQuantityChange->contracted_amount;
						$changesTotalAmount += $customerQuantityChange->contracted_amount;
					}
					else
					{
						$totalNet -= $customerQuantityChange->contracted_amount;
						$changesTotalAmount = $changesTotalAmount-$customerQuantityChange->contracted_amount;
					}
					
					$end_month = '';
			
					if( !empty($customerQuantityChange->end_date) && $customerQuantityChange->end_date != '0000-00-00' )
					{
						$end_month = date('m-d-Y', strtotime($customerQuantityChange->end_date));
					}
					
					$dateEntered = date('m-d-Y', strtotime($customerQuantityChange->history_contract_quantity_change_date));
					
					$agentName = $customerQuantityChange->history_contract_quantity_changer;
					
					$changes[ $customerQuantityChange->customer_id.'-'.$customerQuantityChange->skill_id.'-Quantity' ] = array(
						'date_entered' => $dateEntered,
						'agent' => $agentName,
						'start_date' => date('m-d-Y', strtotime($customerQuantityChange->start_date)),
						'end_date' => $end_month,
						'company' => $customerQuantityChange->customer->company->company_name,
						'customer_name' => $customerQuantityChange->customer_name,
						'customer_id' => $customerQuantityChange->custom_customer_id,
						'skill' => $customerQuantityChange->skill_name,
						'contract' => $customerQuantityChange->contract_name,
						'quantity' => $customerQuantityChange->contracted_quantity,
						'amount' => $customerQuantityChange->contracted_amount,
						'change_type' => $customerQuantityChange->history_contract_quantity_change_type,
					);
				}
			}
			
			if( $customerCreditChanges )
			{
				foreach( $customerCreditChanges as $customerCreditChange )
				{
					$totalNet -= $customerCreditChange->history_credit_amount;
					$changesTotalAmount = $changesTotalAmount-$customerCreditChange->history_credit_amount;

					$end_month = '';
			
					if( !empty($customerCreditChange->end_date) && $customerCreditChange->end_date != '0000-00-00' )
					{
						$end_month = date('m-d-Y', strtotime($customerCreditChange->end_date));
					}
					
					$dateEntered = date('m-d-Y', strtotime($customerCreditChange->history_credit_added_date));
					
					$agentName = $customerCreditChange->history_credit_changer;
					
					$changes[ $customerCreditChange->customer_id.'-'.$customerCreditChange->skill_id.'-Credit' ] = array(
						'date_entered' => $dateEntered,
						'agent' => $agentName,
						'start_date' => date('m-d-Y', strtotime($customerCreditChange->start_date)),
						'end_date' => $end_month,
						'company' => $customerCreditChange->customer->company->company_name,
						'customer_name' => $customerCreditChange->customer_name,
						'customer_id' => $customerCreditChange->custom_customer_id,
						'skill' => $customerCreditChange->skill_name,
						'contract' => $customerCreditChange->contract_name,
						'quantity' => $customerCreditChange->contracted_quantity,
						'amount' => $customerCreditChange->contracted_amount,
						'change_type' => 'New Credit',
						'credit_amount' => $customerCreditChange->history_credit_amount,
					);
				}
			}
			
			if( $customerSkillChanges )
			{
				foreach( $customerSkillChanges as $customerSkillChange )
				{
					if( $customerSkillChange->history_hold_status == 'On Hold' )
					{
						$totalNet -= $customerSkillChange->contracted_amount;
						$changesTotalAmount = $changesTotalAmount-$customerSkillChange->contracted_amount;
					}
					else
					{
						$totalNet += $customerSkillChange->contracted_amount;
						$enrollmentsTotalAmount += $customerSkillChange->contracted_amount;
					}
					
					$end_month = '';
			
					if( !empty($customerSkillChange->end_date) && $customerSkillChange->end_date != '0000-00-00' )
					{
						$end_month = date('m-d-Y', strtotime($customerSkillChange->end_date));
					}
					
					$dateEntered = date('m-d-Y', strtotime($customerSkillChange->history_hold_change_date));
					
					$agentName = $customerSkillChange->history_hold_changer;

					$changes[ $customerSkillChange->customer_id.'-'.$customerSkillChange->skill_id.'-Hold' ] = array(
						'date_entered' => $dateEntered,
						'agent' => $agentName,
						'start_date' => date('m-d-Y', strtotime($customerSkillChange->start_date)),
						'end_date' => $end_month,
						'company' => $customerSkillChange->customer->company->company_name,
						'customer_name' => $customerSkillChange->customer_name,
						'customer_id' => $customerSkillChange->custom_customer_id,
						'skill' => $customerSkillChange->skill_name,
						'contract' => $customerSkillChange->contract_name,
						'quantity' => $customerSkillChange->contracted_quantity,
						'amount' => $customerSkillChange->contracted_amount,
						'change_type' => $customerSkillChange->history_hold_status,
						'credit_amount' => $customerSkillChange->history_credit_amount,
					);

				}
			}
			
			$ctr = 3;
			
			$headers = array(
				'A' => 'Date Entered',
				'B' => 'Sales Agent',
				'C' => 'User',
				'D' => 'Start Date',
				'E' => 'End Date',
				'F' => 'Company',
				'G' => 'Customer Name',
				'H' => 'Customer ID',
				'I' => 'Skill',
				'J' => 'Contract',
				'K' => 'Quantity',
				'L' => 'Amount',
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
			
			$ctr = 1;
					
			$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, 'Enrollment');
			$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, 'Net');
			$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, ( count($enrollments) - count($cancellations) ));
			
			if( $totalNet > 0 )
			{
				$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, '$'.$totalNet );	
			}
			else
			{
				$objPHPExcel->getActiveSheet()->getStyle('G'.$ctr)->applyFromArray(array(
					'font' => array(
						'color' => array('rgb' => 'FF0000'),
					),
				));	
				
				$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, '$'.number_format( abs($totalNet), 2) );		
			}

			
			$ctr = 4;
			
			if( $enrollments )
			{
				foreach( $enrollments as $enrollment )
				{
					$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $enrollment['date_entered'] );
					$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $enrollment['sales_agent'] );
					$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $enrollment['agent'] );
					$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $enrollment['start_date'] );
					$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $enrollment['end_date'] );
					$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $enrollment['company'] );
					$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $enrollment['customer_name'] );
					$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $enrollment['customer_id'] );
					$objPHPExcel->getActiveSheet()->SetCellValue('I'.$ctr, $enrollment['skill'] );
					$objPHPExcel->getActiveSheet()->SetCellValue('J'.$ctr, $enrollment['contract'] );
					$objPHPExcel->getActiveSheet()->SetCellValue('K'.$ctr, $enrollment['quantity'] );
					
					// $objPHPExcel->getActiveSheet()->getStyle('L'.$ctr)->applyFromArray(array(
						// 'font' => array(
							// 'color' => array('rgb' => 'FF0000'),
						// ),
					// ));		
					
					$objPHPExcel->getActiveSheet()->SetCellValue('L'.$ctr, '$'.$enrollment['amount'] );
					
					$ctr++;
				}

				$objPHPExcel->getActiveSheet()->getStyle('A'.$ctr)->applyFromArray(array(
					'font' => array(
						'bold' => true,
					),
				));	
				
				$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, 'TOTAL' );
				
				$objPHPExcel->getActiveSheet()->SetCellValue('L'.$ctr, '$'.number_format($enrollmentsTotalAmount, 2) );
			}
			
			$ctr = $ctr+3;
			
			$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, 'Changes');
			
			$ctr = $ctr+2;
			
			$headers = array(
				'A' => 'Date Entered',
				'B' => 'Sales Agent',
				'C' => 'User',
				'D' => 'Start Date',
				'E' => 'End Date',
				'F' => 'Company',
				'G' => 'Customer Name',
				'H' => 'Customer ID',
				'I' => 'Skill',
				'J' => 'Contract',
				'K' => 'Quantity',
				'L' => 'Change',
				'M' => 'Amount',
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
			
			$ctr = $ctr+1;
			
			if( $changes )
			{
				foreach( $changes as $change )
				{					
					$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $change['date_entered'] );
					$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $change['sales_agent'] );
					$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $change['agent'] );
					$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $change['start_date'] );
					$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $change['end_date'] );
					$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $change['company'] );
					$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $change['customer_name'] );
					$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $change['customer_id'] );
					$objPHPExcel->getActiveSheet()->SetCellValue('I'.$ctr, $change['skill'] );
					$objPHPExcel->getActiveSheet()->SetCellValue('J'.$ctr, $change['contract'] );
					$objPHPExcel->getActiveSheet()->SetCellValue('K'.$ctr, $change['quantity'] );
					$objPHPExcel->getActiveSheet()->SetCellValue('L'.$ctr, $change['change_type'] );
					
					if( $change['change_type'] == 'New Credit' )
					{
						$objPHPExcel->getActiveSheet()->getStyle('M'.$ctr)->applyFromArray(array(
							'font' => array(
								'color' => array('rgb' => 'FF0000'),
							),
						));	
						
						$objPHPExcel->getActiveSheet()->SetCellValue('M'.$ctr, '-$'.$change['credit_amount'] );
						
					}
					elseif( $change['change_type'] == 'Downgrade' )
					{
						$objPHPExcel->getActiveSheet()->getStyle('M'.$ctr)->applyFromArray(array(
							'font' => array(
								'color' => array('rgb' => 'FF0000'),
							),
						));	
						
						$objPHPExcel->getActiveSheet()->SetCellValue('M'.$ctr, '-$'.$change['amount'] );
					}
					elseif( $change['change_type'] == 'On Hold' )
					{
						$objPHPExcel->getActiveSheet()->getStyle('M'.$ctr)->applyFromArray(array(
							'font' => array(
								'color' => array('rgb' => 'FF0000'),
							),
						));	
						
						$objPHPExcel->getActiveSheet()->SetCellValue('M'.$ctr, '-$'.$change['amount'] );
					}									
					else
					{ 
						$objPHPExcel->getActiveSheet()->SetCellValue('M'.$ctr, '$'.$change['amount'] );
					}
					
					
					$ctr++;
				}

				$objPHPExcel->getActiveSheet()->getStyle('A'.$ctr)->applyFromArray(array(
					'font' => array(
						'bold' => true,
					),
				));	
				
				$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, 'TOTAL' );
				
				if( $changesTotalAmount > 0 )
				{

					$objPHPExcel->getActiveSheet()->SetCellValue('M'.$ctr, '$'.number_format($changesTotalAmount, 2) );
				}
				else
				{
					$objPHPExcel->getActiveSheet()->getStyle('M'.$ctr)->applyFromArray(array(
						'font' => array(
							'bold' => true,
							'color' => array('rgb' => 'FF0000'),
						),
					));
					
					$objPHPExcel->getActiveSheet()->SetCellValue('M'.$ctr, '-$'.number_format(abs($changesTotalAmount), 2) );
				}
			}
			
			$ctr = $ctr+3;
			
			$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, 'Cancellation');
			
			$ctr = $ctr+2;
			
			$headers = array(
				'A' => 'Date Entered',
				'B' => 'Sales Agent',
				'C' => 'User',
				'D' => 'Start Date',
				'E' => 'End Date',
				'F' => 'Company',
				'G' => 'Customer Name',
				'H' => 'Customer ID',
				'I' => 'Skill',
				'J' => 'Contract',
				'K' => 'Quantity',
				'L' => 'Amount',
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

			$ctr = $ctr+1;
			
			if( $cancellations )
			{
				foreach( $cancellations as $cancellation )
				{					
					$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $cancellation['date_entered'] );
					$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $cancellation['sales_agent'] );
					$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $cancellation['agent'] );
					$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $cancellation['start_date'] );
					$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $cancellation['end_date'] );
					$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $cancellation['company'] );
					$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $cancellation['customer_name'] );
					$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $cancellation['customer_id'] );
					$objPHPExcel->getActiveSheet()->SetCellValue('I'.$ctr, $cancellation['skill'] );
					$objPHPExcel->getActiveSheet()->SetCellValue('J'.$ctr, $cancellation['contract'] );
					$objPHPExcel->getActiveSheet()->SetCellValue('K'.$ctr, $cancellation['quantity'] );
					
					$objPHPExcel->getActiveSheet()->getStyle('L'.$ctr)->applyFromArray(array(
						'font' => array(
							'color' => array('rgb' => 'FF0000'),
						),
					));		
					
					$objPHPExcel->getActiveSheet()->SetCellValue('L'.$ctr, '-$'.$cancellation['amount'] );
					
					$ctr++;
				}
				
				$objPHPExcel->getActiveSheet()->getStyle('A'.$ctr)->applyFromArray(array(
					'font' => array(
						'bold' => true,
					),
				));	
				
				$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, 'TOTAL' );
				
				$objPHPExcel->getActiveSheet()->getStyle('L'.$ctr)->applyFromArray(array(
					'font' => array(
						'bold' => true,
						'color' => array('rgb' => 'FF0000'),
					),
				));		
				
				$objPHPExcel->getActiveSheet()->SetCellValue('L'.$ctr, '-$'.number_format($cancellationsTotalAmount, 2) );
			}
		}
		
		if( $page == 'agentStates' )
		{
			
			$filename = 'Agent States';
			
			$ctr = 1;
			
			$headers = array(
				'A' => '',
				'B' => 'Login Time',
				'C' => 'Available',
				'D' => 'Unavailable',
				'E' => 'Lunch',
				'F' => 'Break',
				'G' => 'Meeting',
				'H' => 'Training',
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
			
			if( $dateFilterStart != '' && $dateFilterEnd != '' )
			{
				$sql = "
					SELECT a.id AS agent_id, CONCAT(au.`first_name`, ' ', au.`last_name`) AS agent_name,
					(
						SELECT SUM(
							CASE WHEN time_out IS NOT NULL THEN TIME_TO_SEC(TIMEDIFF(time_out, time_in))
								ELSE TIME_TO_SEC(TIMEDIFF(DATE_SUB(NOW(), INTERVAL 1 HOUR), time_in)) 
							END
						)
						FROM ud_account_login_tracker alt
						WHERE alt.account_id = a.`id`
						AND alt.time_in >= '".date('Y-m-d 00:00:00', strtotime($dateFilterStart))."' 
						AND alt.time_in <= '".date('Y-m-d 23:59:59', strtotime($dateFilterEnd))."'
					) AS login_total_seconds,
					(
						SELECT SUM(
							CASE WHEN end_time IS NOT NULL THEN TIME_TO_SEC(TIMEDIFF(end_time, start_time))
								ELSE TIME_TO_SEC(TIMEDIFF(DATE_SUB(NOW(), INTERVAL 1 HOUR), start_time)) 
							END
						)
						FROM ud_account_login_state als
						WHERE als.account_id = a.`id`
						AND als.start_time >= '".date('Y-m-d 00:00:00', strtotime($dateFilterStart))."' 
						AND als.start_time <= '".date('Y-m-d 23:59:59', strtotime($dateFilterEnd))."'
						AND als.type = 1 
					) AS available_total_seconds,
					(
						SELECT SUM(
							CASE WHEN end_time IS NOT NULL THEN TIME_TO_SEC(TIMEDIFF(end_time, start_time))
								ELSE TIME_TO_SEC(TIMEDIFF(DATE_SUB(NOW(), INTERVAL 1 HOUR), start_time)) 
							END
						)
						FROM ud_account_login_state als
						WHERE als.account_id = a.`id`
						AND als.start_time >= '".date('Y-m-d 00:00:00', strtotime($dateFilterStart))."' 
						AND als.start_time <= '".date('Y-m-d 23:59:59', strtotime($dateFilterEnd))."'
						AND als.type = 2
					) AS unavailable_total_seconds,
					(
						SELECT SUM(
							CASE WHEN end_time IS NOT NULL THEN TIME_TO_SEC(TIMEDIFF(end_time, start_time))
								ELSE TIME_TO_SEC(TIMEDIFF(DATE_SUB(NOW(), INTERVAL 1 HOUR), start_time)) 
							END
						)
						FROM ud_account_login_state als
						WHERE als.account_id = a.`id`
						AND als.start_time >= '".date('Y-m-d 00:00:00', strtotime($dateFilterStart))."' 
						AND als.start_time <= '".date('Y-m-d 23:59:59', strtotime($dateFilterEnd))."'
						AND als.type = 3
					) AS lunch_total_seconds,
					(
						SELECT SUM(
							CASE WHEN end_time IS NOT NULL THEN TIME_TO_SEC(TIMEDIFF(end_time, start_time))
								ELSE TIME_TO_SEC(TIMEDIFF(DATE_SUB(NOW(), INTERVAL 1 HOUR), start_time)) 
							END
						)
						FROM ud_account_login_state als
						WHERE als.account_id = a.`id`
						AND als.start_time >= '".date('Y-m-d 00:00:00', strtotime($dateFilterStart))."' 
						AND als.start_time <= '".date('Y-m-d 23:59:59', strtotime($dateFilterEnd))."'
						AND als.type = 4
					) AS break_total_seconds,
					(
						SELECT SUM(
							CASE WHEN end_time IS NOT NULL THEN TIME_TO_SEC(TIMEDIFF(end_time, start_time))
								ELSE TIME_TO_SEC(TIMEDIFF(DATE_SUB(NOW(), INTERVAL 1 HOUR), start_time)) 
							END
						)
						FROM ud_account_login_state als
						WHERE als.account_id = a.`id`
						AND als.start_time >= '".date('Y-m-d 00:00:00', strtotime($dateFilterStart))."' 
						AND als.start_time <= '".date('Y-m-d 23:59:59', strtotime($dateFilterEnd))."'
						AND als.type = 5 
					) AS meeting_total_seconds,
					(
						SELECT SUM(
							CASE WHEN end_time IS NOT NULL THEN TIME_TO_SEC(TIMEDIFF(end_time, start_time))
								ELSE TIME_TO_SEC(TIMEDIFF(DATE_SUB(NOW(), INTERVAL 1 HOUR), start_time)) 
							END
						)
						FROM ud_account_login_state als
						WHERE als.account_id = a.`id`
						AND als.start_time >= '".date('Y-m-d 00:00:00', strtotime($dateFilterStart))."' 
						AND als.start_time <= '".date('Y-m-d 23:59:59', strtotime($dateFilterEnd))."'
						AND als.type = 6 
					) AS training_total_seconds
					FROM ud_account a
					LEFT JOIN ud_account_user au ON au.`account_id` = a.`id`
					WHERE au.job_title IN ('Call Agent', 'Team Leader') 
					AND a.`id` NOT IN (4, 5)
					AND a.status = 1
					ORDER BY au.last_name ASC
				";
				
				// echo '<br><br>';
				// echo $sql;
				// echo '<br><br>';
				
				$connection = Yii::app()->db;
				$command = $connection->createCommand($sql);
				$agents = $command->queryAll();
				
				$agentData = array();
				$totalStateValues = array();
				
				if( $agents )
				{
					foreach( $agents as $agent )
					{
						$totalStateValues['login_time'] += $agent['login_total_seconds'];
						$totalStateValues['available'] += $agent['available_total_seconds'];
						$totalStateValues['unavailable'] += $agent['unavailable_total_seconds'];
						$totalStateValues['lunch'] += $agent['lunch_total_seconds'];
						$totalStateValues['break'] += $agent['break_total_seconds'];
						$totalStateValues['meeting'] += $agent['meeting_total_seconds'];
						$totalStateValues['training'] += $agent['training_total_seconds'];
						
						$agentData[ $agent['agent_id'] ] = array(
							'agent_name' => $agent['agent_name'],
							'available_total_seconds' => $agent['available_total_seconds'],
							'login_time' => AccountLoginState::formatTime($agent['login_total_seconds']),
							'available' => AccountLoginState::formatTime($agent['available_total_seconds']),
							'unavailable' => AccountLoginState::formatTime($agent['unavailable_total_seconds']),
							'lunch' => AccountLoginState::formatTime($agent['lunch_total_seconds']),
							'break' => AccountLoginState::formatTime($agent['break_total_seconds']),
							'meeting' => AccountLoginState::formatTime($agent['meeting_total_seconds']),
							'training' => AccountLoginState::formatTime($agent['training_total_seconds']),
						);
					}
					
					$ctr = 3;
					
					$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, 'Total');
					$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, AccountLoginState::formatTime($totalStateValues['login_time']));
					$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, AccountLoginState::formatTime($totalStateValues['available']));
					$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, AccountLoginState::formatTime($totalStateValues['unavailable']));
					$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, AccountLoginState::formatTime($totalStateValues['lunch']));
					$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, AccountLoginState::formatTime($totalStateValues['break']));
					$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, AccountLoginState::formatTime($totalStateValues['meeting']));
					$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, AccountLoginState::formatTime($totalStateValues['training']));
				}
				
				usort($agentData, function ($a, $b) {
					if($a['available_total_seconds'] == $b['available_total_seconds']) return 0;
					return $a['available_total_seconds'] < $b['available_total_seconds'] ? 1 : -1;
				});
				
				if( $agentData )
				{
					$ctr = 5;
					
					foreach( $agentData as $data ) 
					{
						$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $data['agent_name']);
						$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $data['login_time']);
						$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $data['available']);
						$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $data['unavailable']);
						$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $data['lunch']);
						$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $data['break']);
						$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $data['meeting']);
						$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $data['training']);
						
						$ctr++;
					}
				}
			}
		}
		
		if( $page == 'commision' )
		{
			$models = CustomerSkill::model()->findAll(array(
				'condition' => '
					date_created >= "'.date('Y-m-d 00:00:00', strtotime($dateFilterStart)).'" 
					AND date_created <= "'.date('Y-m-d 23:59:59', strtotime($dateFilterEnd)).'"
				',
				'order' => 'date_created DESC'
			));
			
			$filename = 'Commision Report';
			
			$ctr = 1;
			
			$headers = array(
				'A' => 'Date',
				'B' => 'Sales Agent',
				'C' => 'Customer Name',
				'D' => 'Start Date',
				'E' => 'Status',
				'F' => 'Skill',
				'G' => 'Qty',
				'H' => 'Original Amount',
				'I' => 'Credit',
				'J' => 'Charged',
				'K' => 'Split',
				'L' => 'Commision',
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
		}
		
		if( $models || $models2 )
		{
			if( $page == 'pendingCalls')
			{
				$ctr = 4;
				
				foreach( $models as $model )
				{
					$callHistory = LeadCallHistory::model()->find(array(
						'condition' => 'lead_id	= :lead_id AND calendar_appointment_id = :calendar_appointment_id',
						'params' => array(
							':lead_id' => $model->lead_id,
							':calendar_appointment_id' => $model->calendar_appointment_id,
						),
						'order' => 'date_created DESC', 
					));
					
					$leadPhoneNumber = '';
					
					if( !empty($callHistory) )
					{
						$leadPhoneNumber = $callHistory->lead_phone_number;
					}
					else
					{
						if( $leadPhoneNumber == '' && !empty($model->lead->home_phone_number) )
						{
							$leadPhoneNumber = $model->lead->home_phone_number;
						}
						
						if( $leadPhoneNumber == '' && !empty($model->lead->office_phone_number) )
						{
							$leadPhoneNumber = $model->lead->office_phone_number;
						}
						
						if( $leadPhoneNumber == '' && !empty($model->lead->mobile_phone_number) )
						{
							$leadPhoneNumber = $model->lead->mobile_phone_number;
						}
					}
					
					
					$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, isset($model->customer->company) ? $model->customer->company->company_name : '' );
					
					$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $model->customer->firstname.' '.$model->customer->lastname );
					
					
					
					$status = '';
					$customerSkill = CustomerSkill::model()->find(array(
						'condition' => 'customer_id = :customer_id AND skill_id = :skill_id AND status=1',
						'params' => array(
							':customer_id' => $model->customer_id,
							':skill_id' => $model->skill_id,
						),
					));
					
					if( $customerSkill )
					{
						$status = 'Active';
						
						if( $customerSkill->is_contract_hold == 1 )
						{
							if( !empty($customerSkill->is_contract_hold_start_date) && !empty($customerSkill->is_contract_hold_end_date) )
							{
								if( time() >= strtotime($customerSkill->is_contract_hold_start_date) && time() <= strtotime($customerSkill->is_contract_hold_end_date) )
								{
									$status = 'Hold';
								}
							}
						}
						
						if( !empty($customerSkill->end_month) && date('Y', strtotime($customerSkill->end_month)) >= 2015 )
						{
							if( time() >= strtotime($customerSkill->end_month) )
							{
								$status = 'Cancelled';
							}
						}
						
						if( $customerSkill->is_hold_for_billing == 1 )
						{
							$status = 'Hold';
						}
					}
					
					$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $status);
					
					$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $model->lead->first_name.' '.$model->lead->last_name );
					
					$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, "(".substr($leadPhoneNumber, 0, 3).") ".substr($leadPhoneNumber, 3, 3)."-".substr($leadPhoneNumber,6) );
					
					$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, isset($model->calendarAppointment) ? date('Y-m-d g:i a', strtotime($model->calendarAppointment->start_date)) : null );
					
					$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $model->lead_timezone );
					
					if( isset($model->calendarAppointment) )
					{
						$dateTime = new DateTime($model->calendarAppointment->date_updated, new DateTimeZone('America/Chicago'));
						$dateTime->setTimezone(new DateTimeZone('America/Denver'));							
						$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $dateTime->format('m/d/Y g:i A') );
					}
					
					$ctr++;	
				}
			}
			elseif( $page == 'pendingCallsReschedule')
			{
				$ctr = 4;
				
				$ctr = $ctr + 1;
				
				if( $models )
				{
					foreach( $models as $model2 )
					{
						$callHistory = LeadCallHistory::model()->find(array(
							'condition' => 'lead_id	= :lead_id AND calendar_appointment_id = :calendar_appointment_id',
							'params' => array(
								':lead_id' => $model2->lead_id,
								':calendar_appointment_id' => $model2->calendar_appointment_id,
							),
							'order' => 'date_created DESC', 
						));			

						
						$status = '';
						$customerSkill = CustomerSkill::model()->find(array(
							'condition' => 'customer_id = :customer_id AND skill_id = :skill_id AND status=1',
							'params' => array(
								':customer_id' => $model2->customer_id,
								':skill_id' => $model2->skill_id,
							),
						));
						
						$status = 'Active';
						
						if( $customerSkill->is_contract_hold == 1 )
						{
							if( !empty($customerSkill->is_contract_hold_start_date) && !empty($customerSkill->is_contract_hold_end_date) )
							{
								if( time() >= strtotime($customerSkill->is_contract_hold_start_date) && time() <= strtotime($customerSkill->is_contract_hold_end_date) )
								{
									$status = 'Hold';
								}
							}
						}
						
						if( !empty($customerSkill->end_month) && date('Y', strtotime($customerSkill->end_month)) >= 2015 )
						{
							if( time() >= strtotime($customerSkill->end_month) )
							{
								$status = 'Cancelled';
							}
						}
					
						if( $customerSkill->is_hold_for_billing == 1 )
						{
							$status = 'Hold';
						}
							
						
						if( $status == 'Active' )
						{
							$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, isset($model2->customer->company) ? $model2->customer->company->company_name : '' );
							
							$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $model2->customer->firstname.' '.$model2->customer->lastname );
							
							
							$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $status);
						
							$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $model2->lead->first_name.' '.$model2->lead->last_name );
							
							$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, !empty($callHistory) ? $callHistory->lead_phone_number : null );
							
							$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $model2->lead_timezone );
							
							if( isset($model2->calendarAppointment) )
							{
								$dateTime = new DateTime($model2->calendarAppointment->date_created, new DateTimeZone('America/Chicago'));
								$dateTime->setTimezone(new DateTimeZone('America/Denver'));							
								$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $dateTime->format('m/d/Y g:i A') );
							}
							
							$ctr++;	
						}
					}
				}
			}
			elseif( $page == 'waxieCampaign' )
			{
				$calls = array();
					
				$callSql = "
					SELECT ld.id as lead_id, ld.first_name, ld.last_name,
						lch.lead_phone_number AS phone_number,
						(
							SELECT COUNT(id) from ud_lead_call_history WHERE lead_id = ld.id 
						) as dials,									
						lch.disposition, lch.agent_note 
					FROM ud_lead_call_history lch 
					LEFT JOIN ud_lead ld ON ld.id = lch.lead_id
					LEFT JOIN ud_lists uls ON uls.id = lch.list_id  
					WHERE lch.customer_id = '".$_GET['customer_id']."'
					AND lch.start_call_time >= '".date('Y-m-d 00:00:00', strtotime($dateFilterStart))."' 
					AND lch.start_call_time <= '".date('Y-m-d 23:59:59', strtotime($dateFilterEnd))."' 
					AND lch.end_call_time > lch.start_call_time
					AND lch.status != 4
					AND uls.skill_id=23
				";

				$connection = Yii::app()->db;
				$command = $connection->createCommand($callSql);
				$calls = $command->queryAll();
			
				if( $calls )
				{
					$ctr = 2;
					
					foreach( $calls as $call )
					{
						$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $call['first_name'].' '.$call['last_name']);
						$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $call['phone_number']);
						$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $call['dials']);
						$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $call['disposition']);
						$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $call['agent_note']);
						
						$ctr++;	
					}
				}
			}
			elseif( $page == 'billingProjections' )
			{
				$grandTotalReducedAmount = 0;
				$grandTotalSubsidyAmount = 0;
				
				if( isset($_GET['billing_period']) )
				{
					$grandTotalReducedAmount = 0;
					$grandTotalSubsidyAmount = 0;
					
					$billingPeriodMonth = date('m', strtotime($_REQUEST['billing_period']));
					$billingPeriodYear = date('Y', strtotime($_REQUEST['billing_period']));
					
					foreach( $models as $customerQueue )
					{
						$customerSkill = CustomerSkill::model()->find(array(
							'with' => 'customer',
							'condition' => '
								t.customer_id = :customer_id 
								AND t.skill_id = :skill_id 
								AND t.status=1 
								AND customer.company_id NOT IN(15, 17,18,23, 24, 25, 26, 27)
								AND customer.status=1
								AND customer.is_deleted=0
							',
							'params' => array(
								':customer_id' => $customerQueue->customer_id,
								':skill_id' => $customerQueue->skill_id,
							),

						));
						
						$customerRemoved = CustomerBillingWindowRemoved::model()->find(array(
							'condition' => '
								customer_id = :customer_id 
								AND skill_id = :skill_id 
								AND MONTH(date_created) = :month
								AND YEAR(date_created) = :year
							',
							'params' => array(
								':customer_id' => $customerQueue->customer_id,
								':skill_id' => $customerQueue->skill_id,
								':month' => $billingPeriodMonth,
								':year' => $billingPeriodYear,
							),
						));
						
						if( $customerSkill && strtotime($_REQUEST['billing_period']) >= strtotime($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00' && empty($customerRemoved) )
						{
							$contract = $customerSkill->contract;
							
							$totalLeads = 0;
							$totalAmount = 0;
							$subsidyAmount = 0;
							$month = '';
							$latestTransactionType = '';
							$latestTransactionStatus = '';
							
							$isOnHold = '';
							$isCancelled = '';
							$customerStatus = 'Active';
							
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
													$totalAmount += ( $customerSkillLevelArrayGroup->quantity * $subsidyLevel['amount'] );
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
											':year' => $billingPeriodYear,
											':month' => $billingPeriodMonth,
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
													$totalAmount += ( $customerSkillLevelArrayGroup->quantity * $subsidyLevel['amount'] );
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
											':year' => $billingPeriodYear,
											':month' => $billingPeriodMonth,
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
							
								$customerSkillSubsidyLevel = CustomerSkillSubsidyLevel::model()->find(array(
									'condition' => 'customer_id = :customer_id AND customer_skill_id = :customer_skill_id',
									'params' => array(
										':customer_id' => $customerSkill->customer_id,
										':customer_skill_id' => $customerSkill->id,
									),
								));
								
								$customerSkillSubsidy = CustomerSkillSubsidy::model()->find(array(
									'condition' => 'customer_id = :customer_id AND customer_skill_id = :customer_skill_id',
									'params' => array(
										':customer_id' => $customerSkill->customer_id,
										':customer_skill_id' => $customerSkill->id,
									),
								));
								
								// if( $customerSkillSubsidyLevel )
								if( !empty($customerSkillSubsidyLevel) && !empty($customerSkillSubsidy) && $customerSkillSubsidy->status == CustomerSkillSubsidy::STATUS_ACTIVE )
								{
									$subsidy = CompanySubsidyLevel::model()->find(array(
										'condition' => 'id = :id AND type="%"',
										'params' => array(
											':id' => $customerSkillSubsidyLevel->subsidy_level_id,
										),
									));
									
									if( $subsidy )
									{
										$subsidyPercent = $subsidy->value;
										
										$subsidyPercentInDecimal = $subsidyPercent / 100;

										if( $subsidyPercentInDecimal > 0 )
										{
											$subsidyAmount = $subsidyPercentInDecimal * $totalAmount;
										}
									}
								}
							}
							
							//patch to turn on subsidy
							// if( $subsidyAmount == 0 )
							// {
								// if(!empty($contract->companySubsidies))
								// {
									// foreach($contract->companySubsidies as $companySubsidy)
									// {
										// $criteria = new CDbCriteria;
										// $criteria->compare('customer_id', $customerQueue->customer_id);
										// $criteria->compare('customer_skill_id', $customerSkill->id);
										// $criteria->compare('subsidy_id', $companySubsidy->id);
										
										// $css = CustomerSkillSubsidy::model()->find($criteria);
										
										// if($css === null)
										// {
											// $css = new CustomerSkillSubsidy;
											// $css->customer_id = $customerQueue->customer_id;
											// $css->customer_skill_id = $customerSkill->id;
											// $css->subsidy_id = $companySubsidy->id;
										// }
										
										// $css->status = CustomerSkillSubsidy::STATUS_ACTIVE;
										// $css->save(false);
									// }
								// }
							// }
							
							//find if customer has billing for the current month
							$existingBilling = CustomerBilling::model()->find(array(
								'condition' => '
									customer_id = :customer_id AND contract_id = :contract_id
									AND transaction_type = "Charge"
								',
								'params' => array(
									':customer_id' => $customerQueue->customer_id,
									':contract_id' => $contract->id,
								),
								'order' => 'date_created DESC'
							));
							
							$existingBillingForCurrentMonth = CustomerBilling::model()->find(array(
								'condition' => '
									customer_id = :customer_id 
									AND contract_id = :contract_id
									AND transaction_type = "Charge"
									AND billing_period = :billing_period
									AND ( anet_responseCode = 1 OR ( amount = 0 AND anet_responseCode IS NULL ))
								',
								'params' => array(
									':customer_id' => $customerQueue->customer_id,
									':contract_id' => $contract->id,
									':billing_period' => date('M Y', strtotime($_REQUEST['billing_period']))
								),
								'order' => 'date_created DESC'
							));
							
							$creditCardCount = CustomerCreditCard::model()->count(array(
								'condition' => 'customer_id = :customer_id AND status=1',
								'params' => array(
									':customer_id' => $customerQueue->customer_id,
								),
							));
							
							$echecksCount = CustomerEcheck::model()->count(array(
								'condition' => 'customer_id = :customer_id AND status=1',
								'params' => array(
									':customer_id' => $customerQueue->customer_id,
								),
							));

							if( empty($existingBilling) || ($existingBilling && empty($existingBillingForCurrentMonth) && $existingBilling->billing_period != date('M Y', strtotime($_REQUEST['billing_period']))) )
							{
								$customerIsCallable = false;
								
								//check status and start date
								if( isset($customerSkill->contract) && isset($customerSkill->customer) && $customerSkill->customer->status == 1 && $customerSkill->customer->is_deleted == 0 && strtotime($_REQUEST['billing_period']) >= strtotime($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00' )
								{
									$customerIsCallable = true;
								}
								else
								{
									$customerStatus = 'Inactive';
								}
								
								//check if on hold
								if( $customerSkill->is_contract_hold == 1 )
								{
									if( !empty($customerSkill->is_contract_hold_start_date) && !empty($customerSkill->is_contract_hold_end_date) )
									{
										if( strtotime($_REQUEST['billing_period']) >= strtotime($customerSkill->is_contract_hold_start_date) && strtotime($_REQUEST['billing_period']) < strtotime($customerSkill->is_contract_hold_end_date) )
										{
											$customerIsCallable = false;
											$isOnHold = 'Y';
											$customerStatus = 'On Hold';
										}
									}
								}
								
								// if( $customerSkill->is_hold_for_billing == 1 )
								// {
									// $customerIsCallable = false;
									// $isOnHold = 'Y';
									// $customerStatus = 'On Hold';
								// }
								
								//check if cancelled
								if( !empty($customerSkill->end_month) && date('Y', strtotime($customerSkill->end_month)) >= 2015 )
								{
									if( strtotime($_REQUEST['billing_period']) >= strtotime($customerSkill->end_month) )
									{
										$customerIsCallable = false;
										$isCancelled = 'Y';
										$customerStatus = 'Cancelled';
									}
								}
								
								//&& ($creditCardCount > 0 || $echecksCount > 0)
								if( $customerIsCallable )
								{
									$month = date('M Y', strtotime($_REQUEST['billing_period']));
									
									$totalCreditAmount = 0;
									$customerCredits = CustomerCredit::model()->findAll(array(
										'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND status=1',
										'params' => array(
											':customer_id' => $customerQueue['customer_id'],
											':contract_id' => $contract->id,
										),
									));
									
									if( $customerCredits )
									{
										foreach( $customerCredits as $customerCredit )
										{
											$creditStartDate = date('Y-'.$customerCredit->start_month.'-1');
											
											if( $customerCredit->type == 2 ) //month range
											{
												$creditEndDate = date('Y-'.$customerCredit->end_month.'-t');
											}
											else
											{
												$creditEndDate = date('Y-'.$customerCredit->start_month.'-t');
											}
											
											if( (strtotime($_REQUEST['billing_period']) >= strtotime($creditStartDate)) && (strtotime($_REQUEST['billing_period']) <= strtotime($creditEndDate)) )
											{
												$totalCreditAmount += $customerCredit->amount;
											}
										}
									}
									
									$paymentMethod = CustomerBilling::model()->getDefaultMethod($customerQueue->customer_id);
									
									$paymentMethod = explode('-', $paymentMethod);
									$paymentMethodType = $paymentMethod[0];
									$paymentMethodId = $paymentMethod[1];
									
									$creditCardType = null;
									$creditCardIsExpired = false;
									
									if( $paymentMethodType == 'creditCard' )
									{
										$creditCard = CustomerCreditCard::model()->findByPk($paymentMethodId);
										
										if( $creditCard )
										{
											$creditCardType = $creditCard->credit_card_type;
											
											if( strtotime($_REQUEST['billing_period']) >= strtotime($creditCard->expiration_year.'-'.$creditCard->expiration_month.'-01') )
											{
												$creditCardIsExpired = true;
											}
										}
									}
									
									
									//credit amount should not be over the Amount, for the customer will ask it to be billed next month -aug 9, 2016
									if($totalCreditAmount > $totalAmount)
									{
										$totalCreditAmount = $totalAmount - $subsidyAmount;
									}
									
									$totalReducedAmount = ($totalAmount - $totalCreditAmount - $subsidyAmount);

									if( $totalReducedAmount < 0 )
									{
										$totalReducedAmount = 0;
									}
									
									$totalReducedAmount = number_format($totalReducedAmount, 2);
									
									$grandTotalReducedAmount += $totalReducedAmount;
									$grandTotalSubsidyAmount += $subsidyAmount;
									
									$endDate = '';
									
									if( !empty($customerSkill->end_month) && $customerSkill->end_month != '0000-00-00' )
									{
										$endDate = date('m/d/Y', strtotime($customerSkill->end_month));
									}
									
									$pendingBillings[$customerQueue->customer_id.'-'.$customerQueue->skill_id] = array(
										'customer_id' => $customerQueue->customer_id,
										'agent_id' => $customerQueue->customer->custom_customer_id,
										'status' => $customerStatus,
										'hold' => $isOnHold,
										'cancel' => $isCancelled,
										'start_date' => date('m/d/Y', strtotime($customerSkill->start_month)),
										'end_date' => $endDate,
										'customer_name' => $customerQueue->customer->getFullName(),
										'company' => $customerQueue->company,
										'skill' => $customerQueue->skill->skill_name,
										'contract' => $contract->contract_name,
										'quantity' => $totalLeads,
										'billing_cycle' => $month,
										'payment_method' => $paymentMethodType == 'creditCard' ? 'Credit Card' : 'eCheck',
										'credit_card_type' => $creditCardType,
										'action' => 'Charge',
										'original_amount' => $totalAmount,
										'billing_credit' => $totalCreditAmount,
										'subsidy' => $subsidyAmount,
										'reduced_amount' => $totalReducedAmount,
										'credit_is_expired' => $creditCardIsExpired
									);
								}
							}
						}
					}
				}
				
				
				$ctr = 1;
				
				$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, 'Credit Card - $' . number_format($grandTotalReducedAmount, 2));
				$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, 'Subsidy - $' . number_format($grandTotalSubsidyAmount, 2));
				$objPHPExcel->getActiveSheet()->SetCellValue('K'.$ctr, 'Total - $' . number_format($grandTotalReducedAmount + $grandTotalSubsidyAmount, 2));
				
				if( $pendingBillings )
				{
					$ctr = 4;
					
					foreach( $pendingBillings as $pendingBilling )
					{
						if( $pendingBilling['credit_is_expired'] )
						{
							$objPHPExcel->getActiveSheet()->getStyle('A'.$ctr.':Q'.$ctr)->applyFromArray(
								array(
									'fill' => array(
										'type' => PHPExcel_Style_Fill::FILL_SOLID,
										'color' => array('rgb' => 'FF0000')
									)
								)
							);
						}
					
						$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $pendingBilling['agent_id']);
						$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $pendingBilling['status']);
						$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $pendingBilling['start_date']);
						$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $pendingBilling['end_date']);
						$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $pendingBilling['customer_name']);
						$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $pendingBilling['company']);
						$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $pendingBilling['skill']);
						$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $pendingBilling['contract']);
						$objPHPExcel->getActiveSheet()->SetCellValue('I'.$ctr, $pendingBilling['quantity']);
						$objPHPExcel->getActiveSheet()->SetCellValue('J'.$ctr, $pendingBilling['billing_cycle']);
						$objPHPExcel->getActiveSheet()->SetCellValue('K'.$ctr, $pendingBilling['payment_method']);
						$objPHPExcel->getActiveSheet()->SetCellValue('L'.$ctr, $pendingBilling['credit_card_type']);
						$objPHPExcel->getActiveSheet()->SetCellValue('M'.$ctr, $pendingBilling['action']);
						$objPHPExcel->getActiveSheet()->SetCellValue('N'.$ctr, $pendingBilling['original_amount']);
						$objPHPExcel->getActiveSheet()->SetCellValue('O'.$ctr, $pendingBilling['billing_credit']);
						$objPHPExcel->getActiveSheet()->SetCellValue('P'.$ctr, $pendingBilling['subsidy']);
						$objPHPExcel->getActiveSheet()->SetCellValue('Q'.$ctr, $pendingBilling['reduced_amount']);
	
						$ctr++;
					}
				}
			}
			elseif( $page == 'listImportLog')
			{
				$ctr = 2;
					
				foreach( $models as $model )
				{
					$list = Lists::model()->findByPk($model->model_id);
											
					$listStatus = $list->status == 1 ? 'Active' : 'Inactive';
					
					$date = new DateTime($model->date_created, new DateTimeZone('America/Chicago'));

					$date->setTimezone(new DateTimeZone('America/Denver'));
						
					$explodedContent = explode('|', $model->content);
					
					if( count($explodedContent) == 2 )
					{
						$importedCount = filter_var(strip_tags($explodedContent[1]), FILTER_SANITIZE_NUMBER_INT);
						$duplicateCount = 0;
						$badCount = 0;
					}
					else
					{	
						$importedCount = filter_var(strip_tags($explodedContent[2]), FILTER_SANITIZE_NUMBER_INT);												
						$duplicateCount = filter_var(strip_tags($explodedContent[4]), FILTER_SANITIZE_NUMBER_INT);
						$badCount = filter_var(strip_tags($explodedContent[5]), FILTER_SANITIZE_NUMBER_INT);
					}											
					
					$total = $importedCount + $duplicateCount + $badCount;
											
					$percentageOfNotImported = 0;
					
					if($total != 0)
						$percentageOfNotImported = ($duplicateCount + $badCount) / $total;
											
					$modelAccountName = '';
					if( isset($model->account) )
					{
						if( $model->account->account_type_id == Account::TYPE_CUSTOMER )
						{
							$modelAccountName =  $model->account->customer->firstname.' '.$model->account->customer->lastname;
						}
						elseif( $model->account->account_type_id == TYPE_CUSTOMER_OFFICE_STAFF )
						{
							$modelAccountName =  $model->account->customerOfficeStaff->staff_name;
						}
						else
						{
							$modelAccountName =  $model->account->getFullName();
						}
						
					}
						
					$officeStaffs = CustomerOfficeStaff::model()->findAll(array(
						'condition' => 'customer_id = :customer_id AND is_deleted=0 AND is_received_low_on_names_email=1',
						'params' => array(
							':customer_id' => $model->customer_id,
						),
					));
					
					$officeStaffString = '';
					if( $officeStaffs )
					{
						$emailAddresses = array();
						
						foreach( $officeStaffs as $officeStaff )
						{
							$emailAddresses[] = $officeStaff->email_address;
						}
						
						$officeStaffString =  implode(', ', $emailAddresses);
					}

					$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $date->format('m/d/Y g:i A'));
					
					$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $modelAccountName);
					
					$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $model->customer->firstname.' '.$model->customer->lastname);
					
					$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $model->customer->custom_customer_id	);
					
					$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $list->name);
					
					$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $total);
					
					$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $importedCount);
					
					$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $duplicateCount);
					
					$objPHPExcel->getActiveSheet()->SetCellValue('I'.$ctr, $badCount);
					
					$objPHPExcel->getActiveSheet()->SetCellValue('J'.$ctr, number_format($percentageOfNotImported,2).'%');
					
					
					$objPHPExcel->getActiveSheet()->SetCellValue('K'.$ctr, $listStatus);
					
					$objPHPExcel->getActiveSheet()->SetCellValue('L'.$ctr, $officeStaffString);
				
					$ctr++;	
				}
			}
			elseif( $page == 'commision' )
			{
				$ctr = 2;
				
				foreach( $models as $model )
				{
					$totalLeads = 0;
					$totalCreditAmount = 0;
					$contractedAmount = 0;
					$commissionRate = 0;
					
					$status = 'Inactive';
					$selectedSalesReps = '';
					$charged = 'N';
					
					$customer = Customer::model()->find(array(
						'condition' => 'id = :customer_id',
						'params' => array(
							':customer_id' => $model->customer_id,
						),
					));
					
					if( $customer )
					{
						$salesReps = CustomerSalesRep::model()->findAll(array(
							'condition' => 'customer_id = :customer_id',
							'params' => array(
								':customer_id' => $customer->id,
							),
						));
						
						if( $salesReps )
						{
							foreach( $salesReps as $salesRep )
							{
								$selectedSalesReps .= $salesRep->account->getFullName().', ';
				
								$userMonthlyGoal = SalesAccountMonthlyGoal::model()->find(array(
									'condition' => 'account_id = :account_id',
									'params' => array(
										':account_id' => $salesRep->sales_rep_account_id,
									),
								));
								
								if( $userMonthlyGoal )
								{
									$userCommissionRate = str_replace('%', '', $userMonthlyGoal->commission_rate);

									$commissionRate = ($userCommissionRate / 100);
								}
							}
							
							$selectedSalesReps = rtrim($selectedSalesReps, ', ');
						}
						
						$customerSkill = CustomerSkill::model()->find(array(
							'condition' => 'customer_id = :customer_id AND status=1',
							'params' => array(
								':customer_id' => $customer->id,
							),
						));
						
						if( $customerSkill )
						{
							$contract = $customerSkill->contract;
			
							if( $contract )
							{
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

								$status = 'Inactive';
								
								if( isset($customerSkill->contract) && isset($customerSkill->customer) && $customerSkill->customer->status == 1 && $customerSkill->customer->is_deleted == 0 && time() >= strtotime($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00' )
								{
									$status = 'Active';
								}
								
								if( $customerSkill->is_contract_hold == 1 )
								{
									if( !empty($customerSkill->is_contract_hold_start_date) && !empty($customerSkill->is_contract_hold_end_date) )
									{
										if( time() >= strtotime($customerSkill->is_contract_hold_start_date) && time() <= strtotime($customerSkill->is_contract_hold_end_date) )
										{
											$status = 'On Hold';
										}
									}
								}
								
								if( $customerSkill->is_hold_for_billing == 1 )
								{
									$status = 'Decline Hold';
								}
								
								if( !empty($customerSkill->end_month) && date('Y', strtotime($customerSkill->end_month)) >= 2015 )
								{
									if( time() >= strtotime($customerSkill->end_month) )
									{
										$status = 'Cancelled';
									}
								}

								
								$billingPeriod = date('M Y', strtotime($dateFilterStart));
								
								$existingBillingForCurrentMonth = CustomerBilling::model()->find(array(
									'condition' => '
										customer_id = :customer_id 
										AND contract_id = :contract_id
										AND transaction_type = "Charge"
										AND billing_period = :billing_period
										AND ( anet_responseCode = 1 OR ( amount = 0 AND anet_responseCode IS NULL ))
									',
									'params' => array(
										':customer_id' => $customerSkill->customer_id,
										':contract_id' => $contract->id,
										':billing_period' => $billingPeriod
									),
									'order' => 'date_created DESC'
								));
								
								if( $existingBillingForCurrentMonth )
								{
									$charged = 'Y';
									
									$existingBillingForCurrentMonthVoidorRefund = CustomerBilling::model()->find(array(
										'condition' => '
											customer_id = :customer_id 
											AND contract_id = :contract_id
											AND anet_responseCode = 1
											AND reference_transaction_id = :reference_transaction_id
											AND (
												transaction_type = "Void"
												OR transaction_type = "Refund"
											)
										',
										'params' => array(
											':customer_id' => $customerSkill->customer_id,
											':contract_id' => $customerSkill->contract_id,
											':reference_transaction_id' => $existingBillingForCurrentMonth->id,
										),
										'order' => 'date_created DESC'
									)); 
									
									if( $existingBillingForCurrentMonthVoidorRefund )
									{
										$charged  = 'N';
									}
								}
								
								
								$customerCredits = CustomerCredit::model()->findAll(array(
									'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND status=1',
									'params' => array(
										':customer_id' => $customerSkill->customer_id,
										':contract_id' => $customerSkill->contract_id,
									),
								));
								
								if( $customerCredits )
								{
									foreach( $customerCredits as $customerCredit )
									{
										$creditStartDate = date('Y-'.$customerCredit->start_month.'-1');
										
										if( $customerCredit->type == 2 ) //month range
										{
											$creditEndDate = date('Y-'.$customerCredit->end_month.'-t');
										}
										else
										{
											$creditEndDate = date('Y-'.$customerCredit->start_month.'-t');
										}
										
										
										$monthBillingPeriod = explode(' ',$billing_period);
										$monthPeriod = date('m', strtotime("$monthBillingPeriod[0] 1 ".date('Y')));
										$startDayOfBillingPeriod = date("Y-m-d",strtotime(date('Y')."-".$monthPeriod."-1"));
										$lastDayOfBillingPeriod = date("Y-m-t", strtotime($startDayOfBillingPeriod));
										
										if( (strtotime($startDayOfBillingPeriod) >= strtotime($creditStartDate)) && (strtotime($lastDayOfBillingPeriod) <= strtotime($creditEndDate)) )
										{
											$totalCreditAmount += $customerCredit->amount;
										}
									}
								}
								
								$dateTime = new DateTime($model->date_created, new DateTimeZone('America/Chicago'));
								$dateTime->setTimezone(new DateTimeZone('America/Denver'));	
				
								$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $dateTime->format('m/d/Y g:i A') );
								$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $selectedSalesReps );
								$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $customer->getFullName() );
								
								if( $customerSkill->start_month != '0000-00-00' && $customerSkill->start_month != '' )
								{
									$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, date('m/d/Y', strtotime($customerSkill->start_month)) );
								}
								
								$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $status );
								
								$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $customerSkill->skill->skill_name );
								$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $totalLeads );
								$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $contractedAmount );
								$objPHPExcel->getActiveSheet()->SetCellValue('I'.$ctr, $totalCreditAmount );
								$objPHPExcel->getActiveSheet()->SetCellValue('J'.$ctr, $charged );

								if( count($salesReps) > 1 )
								{
									$objPHPExcel->getActiveSheet()->SetCellValue('K'.$ctr, 'Y' );
								}
								else
								{
									$objPHPExcel->getActiveSheet()->SetCellValue('K'.$ctr, 'N' );
								}

								if( $commissionRate > 0 )
								{
									$objPHPExcel->getActiveSheet()->SetCellValue('L'.$ctr, '$'.number_format( ($commissionRate * $contractedAmount) / count($selectedSalesReps), 2) );
								}
								else
								{
									$objPHPExcel->getActiveSheet()->SetCellValue('L'.$ctr, '$0.00' );
								}
								
								$ctr++;
							}
						}
					}
				}
			}
			else
			{
				$ctr = 2;
				
				foreach($models as $model)
				{
					if( $page == 'customerContactInfo' )
					{
						$state = !empty($model->state) ? State::model()->findByPk($model->state)->name : '';
						
						$customerSkills = CustomerSkill::model()->findAll(array(
							'condition' => 'customer_id = :customer_id AND status=1',
							'params' => array(
								'customer_id' => $model->id,
							),
						));
						
						$skillArray = array();
						$contractArray = array();
						
						$startDate = '';
						$endDate = '';
						$quantity = 0;
						
						if( $customerSkills )
						{
							foreach( $customerSkills as $customerSkill )
							{
								if( !empty($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00' )
								{
									$startDate = date('m/d/Y', strtotime($customerSkill->start_month));
								}
								
								if( !empty($customerSkill->end_month) && $customerSkill->end_month != '0000-00-00' )
								{
									$endDate = date('m/d/Y', strtotime($customerSkill->end_month));
								}
								
								if( isset($customerSkill->skill) && !in_array($customerSkill->skill->skill_name, $skillArray) )
								{
									$skillArray[] = $customerSkill->skill->skill_name;
								}
								
								if( isset($customerSkill->contract) && !in_array($customerSkill->contract->contract_name, $contractArray) )
								{
									$contract = $customerSkill->contract;
									
									$contractArray[] = $contract->contract_name;
							
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
															$quantity += ( $subsidyLevel['goal'] * $customerSkillLevelArrayGroup->quantity );
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
															$quantity += ( $subsidyLevel['high'] * $customerSkillLevelArrayGroup->quantity );
														}
													}
												}
											}
										}
									}
								}
							}
						}
						

						$status = 'Inactive';
		
						if( isset($customerSkill->contract) && isset($customerSkill->customer) && $customerSkill->customer->status == 1 && $customerSkill->customer->is_deleted == 0 && time() >= strtotime($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00' )
						{
							$status = 'Active';
						}
						
						if( !$customerIsCallable )
						{
							if( $customerSkill->start_month == '0000-00-00' )
							{
								$status = 'Blank Start Date';
							}
							
							if( $customerSkill->start_month != '0000-00-00' && strtotime($customerSkill->start_month) > time() )
							{
								$status = 'Future Start Date';
							}
						}
						
						if( $customerSkill->is_contract_hold == 1 )
						{
							if( !empty($customerSkill->is_contract_hold_start_date) && !empty($customerSkill->is_contract_hold_end_date) )
							{
								if( time() >= strtotime($customerSkill->is_contract_hold_start_date) && time() <= strtotime($customerSkill->is_contract_hold_end_date) )
								{
									$status = 'On Hold';
								}
							}
						}
						
						if( $customerSkill->is_hold_for_billing == 1 )
						{
							$status = 'Decline Hold';
						}
						
						if( !empty($customerSkill->end_month) && date('Y', strtotime($customerSkill->end_month)) >= 2015 )
						{
							if( time() >= strtotime($customerSkill->end_month) )
							{
								$status = 'Cancelled';
							}
						}
						
						$holdStartDate = '';
						$holdEndDate = '';
						
						if( $status == 'Hold' )
						{
							$holdStartDate = date('m/d/Y', strtotime($customerSkill->is_contract_hold_start_date));
							$holdEndDate = date('m/d/Y', strtotime($customerSkill->is_contract_hold_end_date));
						}
							
						$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $model->custom_customer_id );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $model->firstname . ', '. $model->lastname );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $status );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, isset($model->company) ? $model->company->company_name : '' );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $model->phone );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $model->email_address );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $model->address1 );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $model->city );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('I'.$ctr, $state );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('J'.$ctr, $model->zip );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('K'.$ctr, !empty($skillArray) ? implode(', ', $skillArray) : '' );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('L'.$ctr, !empty($contractArray) ? implode(', ', $contractArray) : '' );	
						
						$objPHPExcel->getActiveSheet()->SetCellValue('M'.$ctr, $quantity );	
						
						$objPHPExcel->getActiveSheet()->SetCellValue('N'.$ctr, $startDate );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('O'.$ctr, $endDate );	
						
						$objPHPExcel->getActiveSheet()->SetCellValue('P'.$ctr, $holdStartDate );	
						
						$objPHPExcel->getActiveSheet()->SetCellValue('P'.$ctr, $holdEndDate );	
						
						
						
						$ctr++;	
					}
										
					if( $page == 'creditCardTransactions' )
					{
						$customerSkills = CustomerSkill::model()->findAll(array(
							'condition' => 'customer_id = :customer_id AND status=1',
							'params' => array(
								'customer_id' => $model->customer_id,
							),
						));
						
						$skillArray = array();
						$contractArray = array();
						
						if( $customerSkills )
						{
							foreach( $customerSkills as $customerSkill )
							{
								if( !in_array($customerSkill->skill->skill_name, $skillArray) )
								{
									$skillArray[] = $customerSkill->skill->skill_name;
								}
								
								if( !in_array($customerSkill->contract->contract_name, $contractArray) )
								{
									$contractArray[] = $customerSkill->contract->contract_name;
								}
							}
						}	
						
						$result = '';
						
						if( $model->anet_responseCode == 1 )
						{
							$result = 'Success';
						}
						else
						{
							$result = 'Decline';
							
							if( !empty($model->anet_responseReasonDescription) )
							{
								$result .= ' - ' . $model->anet_responseReasonDescription;
							}
						}
						
						if( $model->payment_method == 'echeck' )
						{
							$paymentMethod = 'eCheck';
						}
						else
						{
							$paymentMethod = 'Credit Card';
						}
						
						
						if( $model->transaction_type == 'Void' && $model->reference_transaction_id != null )
						{
							$chargeRecord = CustomerBilling::model()->findByPk($model->reference_transaction_id);
							
							if( $chargeRecord )
							{
								$model->credit_amount = $chargeRecord->credit_amount;
							}
						}

						$dateTime = new DateTime($model->date_created, new DateTimeZone('America/Chicago'));
						$dateTime->setTimezone(new DateTimeZone('America/Denver'));	

						
						$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $dateTime->format('m/d/Y g:i A') );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $model->customer->custom_customer_id );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $model->customer->firstname . ', '. $model->customer->lastname );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, isset($model->customer->company) ? $model->customer->company->company_name : '' );

						$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, !empty($skillArray) ? implode(', ', $skillArray) : '' );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, !empty($contractArray) ? implode(', ', $contractArray) : '' );	
						
						$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $model->billing_period );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $model->description );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('I'.$ctr, $paymentMethod );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('J'.$ctr, $model->credit_card_type );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('K'.$ctr, $model->transaction_type );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('L'.$ctr, '$'.number_format( ($model->amount + $model->credit_amount + $model->subsidy_amount), 2) );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('M'.$ctr, '$'.number_format($model->credit_amount, 2) );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('N'.$ctr, '$'.number_format($model->subsidy_amount, 2) );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('O'.$ctr, '$'.number_format($model->amount, 2) );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('P'.$ctr, $model->anet_transId );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('Q'.$ctr, isset($model->account) ? $model->account->getFullName() : '' );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('R'.$ctr, $result );
						
						$ctr++;	
					}
					
					if( $page == 'billingResults' )
					{
						$customerSkill = CustomerSkill::model()->find(array(
							'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND status=1',
							'params' => array(
								'customer_id' => $model->customer_id,
								'contract_id' => $model->contract_id,
							),
						));
						
						$contract = $customerSkill->contract;
					
						$totalLeads = 0;
						$totalAmount = 0;
						$subsidyAmount = 0;
						$month = '';
						$latestTransactionType = '';
						$latestTransactionStatus = '';
						
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
												$totalAmount += $customerSkillLevelArrayGroup->quantity * $subsidyLevel['amount'];
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
												$totalAmount += $subsidyLevel['amount'];
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
	
						$result = '';
						
						if( $model->anet_responseCode == 1 )
						{
							$result = 'Success';
						}
						else
						{
							$result = 'Decline';
							
							if( !empty($model->anet_responseReasonDescription) )
							{
								$result .= ' - ' . $model->anet_responseReasonDescription;
							}
						}
						
						if( $model->payment_method == 'echeck' )
						{
							$paymentMethod = 'eCheck';
						}
						else
						{
							$paymentMethod = 'Credit Card';
						}
						
						
						if( $model->transaction_type == 'Void' && $model->reference_transaction_id != null )
						{
							$chargeRecord = CustomerBilling::model()->findByPk($model->reference_transaction_id);
							
							if( $chargeRecord )
							{
								$model->credit_amount = $chargeRecord->credit_amount;
							}
						}

						$dateTime = new DateTime($model->date_created, new DateTimeZone('America/Chicago'));
						$dateTime->setTimezone(new DateTimeZone('America/Denver'));	

						
						$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $dateTime->format('m/d/Y g:i A') );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $model->customer->custom_customer_id );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $model->customer->firstname . ', '. $model->customer->lastname );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, isset($model->customer->company) ? $model->customer->company->company_name : '' );

						$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, !empty($customerSkill) ? $customerSkill->skill->skill_name : '' );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, !empty($customerSkill) ? $customerSkill->contract->contract_name : '');	
						
						$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, !empty($customerSkill) ? $totalLeads : '');	
						
						$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $model->billing_period );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('I'.$ctr, $model->description );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('J'.$ctr, $paymentMethod );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('K'.$ctr, $model->credit_card_type );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('L'.$ctr, $model->transaction_type );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('M'.$ctr, '$'.number_format( ($model->amount + $model->credit_amount + $model->subsidy_amount), 2) );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('N'.$ctr, '$'.number_format($model->credit_amount, 2) );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('O'.$ctr, '$'.number_format($model->subsidy_amount, 2) );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('P'.$ctr, '$'.number_format($model->amount, 2) );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('Q'.$ctr, $model->anet_transId );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('R'.$ctr, isset($model->account) ? $model->account->getFullName() : '' );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('S'.$ctr, $result );
						
						$ctr++;	
					}
					
					if( $page == 'contractLeads' )
					{
						$callDateTime = new DateTime($model->start_call_time, new DateTimeZone('America/New_York'));
						$callDateTime->setTimezone(new DateTimeZone('America/Denver'));	

						$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $model->company->company_name);
						
						$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $model->customer->account_number);
						
						$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $model->customer->firstname);
						
						$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $model->customer->lastname);
						
						$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, State::model()->findByPk($model->customer->state)->name);
						
						$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $model->lead->first_name);
						
						$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $model->lead->last_name);
						
						$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $model->lead_dial_count);
						
						$objPHPExcel->getActiveSheet()->SetCellValue('I'.$ctr, $callDateTime->format('m/d/Y'));
						
						$objPHPExcel->getActiveSheet()->SetCellValue('J'.$ctr, $callDateTime->format('g:i A'));
						
						$objPHPExcel->getActiveSheet()->SetCellValue('K'.$ctr, $model->disposition->skill_disposition_name);
						
						$objPHPExcel->getActiveSheet()->SetCellValue('L'.$ctr, $model->agent_note);
						
						$objPHPExcel->getActiveSheet()->SetCellValue('M'.$ctr, $model->external_note);
						
						$ctr++;	
					}
										
					if( $page == 'agentPerformance' )
					{
						$primaryVoiceContacts = 0;
						$primaryAppointments = 0;
						$primaryConversionRate = 0;
						$primaryTotalDialsPerHour = 0;
						$primaryAppointmentsPerHour = 0;
						
						$primaryCallHours = 0;
						$primaryCallMinutes = 0;
						
						$primaryOutboundDials = 0;
						
						$clockedHours = $model->getTotalLoginHours($dateFilterStart, $dateFilterEnd);

						$teamMember = TeamMember::model()->find(array(
							'condition' => 'account_id',
							'params' => array(
								':account_id' => $model->id,
							),
						));

						if( $teamMember )
						{
							$team = $teamMember->team->name;
						}
						else
						{
							$team = '';	
						}
						
						if( !empty($_POST['skillIds']) )
						{
							$primaryCalls = LeadCallHistory::model()->findAll(array(
								'with' => array('list'),
								'condition' => '
									t.agent_account_id = :agent_account_id 
									AND t.start_call_time >= :dateFilterStart 
									AND t.start_call_time <= :dateFilterEnd 
									AND t.end_call_time > t.start_call_time
									AND list.skill_id IN ('.$selectedSkills.')
									AND t.is_skill_child=0
								',
								'params' => array(
									':agent_account_id' => $model->id,
									':dateFilterStart' => date('Y-m-d 00:00:00', strtotime($dateFilterStart)),
									':dateFilterEnd' => date('Y-m-d 23:59:59', strtotime($dateFilterEnd)),
								),
							));
						
							$primaryOutboundDials = count($primaryCalls);
							
							$primaryVoiceContacts = LeadCallHistory::model()->count(array(
								'with' => array('list','skillDisposition'),
								'condition' => '
									t.agent_account_id = :agent_account_id 
									AND t.start_call_time >= :dateFilterStart 
									AND t.start_call_time <= :dateFilterEnd
									AND t.is_skill_child=0				
									AND skillDisposition.id IS NOT NULL
									AND skillDisposition.is_voice_contact=1
									AND list.skill_id IN ('.$selectedSkills.')
								',
								'params' => array(
									':agent_account_id' => $model->id,
									':dateFilterStart' => date('Y-m-d 00:00:00', strtotime($dateFilterStart)),
									':dateFilterEnd' => date('Y-m-d 23:59:59', strtotime($dateFilterEnd)),
								),
							));
							
							$primaryAppointments = LeadCallHistory::model()->count(array(
								'with' => array('list','skillDisposition'),
								'condition' => '
									t.agent_account_id = :agent_account_id 
									AND t.start_call_time >= :dateFilterStart 
									AND t.start_call_time <= :dateFilterEnd
									AND t.is_skill_child=0
									AND skillDisposition.id IS NOT NULL
									AND skillDisposition.is_appointment_set=1
									AND list.skill_id IN ('.$selectedSkills.')
								',
								'params' => array(
									':agent_account_id' => $model->id,
									':dateFilterStart' => date('Y-m-d 00:00:00', strtotime($dateFilterStart)),
									':dateFilterEnd' => date('Y-m-d 23:59:59', strtotime($dateFilterEnd)),
								),
							));
						}
						
						if( $primaryOutboundDials > 0 && $clockedHours > 0 )
						{
							$primaryTotalDialsPerHour = $primaryOutboundDials / $clockedHours;
						}
						
						if( $primaryAppointments > 0 && $clockedHours > 0 )
						{
							$primaryAppointmentsPerHour = $primaryAppointments / $clockedHours;
						}
						
						if( $primaryAppointments > 0 && $primaryVoiceContacts > 0 )
						{
							$primaryConversionRate = $primaryAppointments / $primaryVoiceContacts;
						}
						
						$primarySkillWrapTimes = LeadCallWrapTime::model()->findAll(array(
							'condition' => '
								agent_account_id = :agent_account_id 
								AND start_time >= :dateFilterStart 
								AND start_time <= :dateFilterEnd
								AND end_time > start_time
								AND DATE(start_time) = DATE(end_time)
								AND call_type NOT IN (3,6) 
								AND main_skill_id IN ('.$selectedSkills.')
							',
							'params' => array(
								':agent_account_id' => $model->id,
								':dateFilterStart' => date('Y-m-d 00:00:00', strtotime($dateFilterStart)),
								':dateFilterEnd' => date('Y-m-d 23:59:59', strtotime($dateFilterEnd)),
							),
						));
						
						$primarySkillWrapTimeMinutes = 0;
						
						if( $primarySkillWrapTimes )
						{
							foreach( $primarySkillWrapTimes as $primarySkillWrapTime )
							{
								$primarySkillWrapTimeMinutes += round( (strtotime($primarySkillWrapTime->end_time) - strtotime($primarySkillWrapTime->start_time)) / 60,2);
							}
						}
						
						$primaryHours = LeadCallWrapTime::model()->findAll(array(
							'condition' => '
								agent_account_id = :agent_account_id 
								AND start_time >= :dateFilterStart 
								AND start_time <= :dateFilterEnd
								AND end_time > start_time
								AND DATE(start_time) = DATE(end_time)
								AND call_type NOT IN (3,6) 
								AND main_skill_id IN ('.$selectedSkills.')
							',
							'params' => array(
								':agent_account_id' => $model->id,
								':dateFilterStart' => date('Y-m-d 00:00:00', strtotime($dateFilterStart)),
								':dateFilterEnd' => date('Y-m-d 23:59:59', strtotime($dateFilterEnd)),
							),
							'group' => 'lead_id',
							'order' => 'start_time ASC',
						));
						
						if( $primaryHours )
						{
							foreach( $primaryHours as $primaryHour )
							{
								$primaryHourEnd = LeadCallWrapTime::model()->find(array(
									'condition' => '
										id != :id
										AND agent_account_id = :agent_account_id 
										AND lead_id = :lead_id 
										AND start_time >= :dateFilterStart 
										AND start_time <= :dateFilterEnd
										AND end_time > start_time
										AND DATE(start_time) = DATE(end_time)
									',
									'params' => array(
										':id' => $primaryHour->id,
										':agent_account_id' => $model->id,
										':lead_id' => $primaryHour->lead_id,
										':dateFilterStart' => date('Y-m-d H:i:s', strtotime($primaryHour->start_time)),
										':dateFilterEnd' => date('Y-m-d H:i:s', strtotime('+1 hour', strtotime($primaryHour->start_time))),
									),
									'order' => 'end_time DESC',
								));
								
								if( $primaryHourEnd )
								{
									$primaryCallMinutes += round( (strtotime($primaryHourEnd->end_time) - strtotime($primaryHour->start_time)) / 60,2);
								}
							}

							$primaryCallHours =  floor($primaryCallMinutes/60);
							$primaryCallMinutes =   $primaryCallMinutes % 60;
						}

						
						//start geting child skill values
						
						$childVoiceContacts = 0;
						$childAppointments = 0;
						$childConversionRate = 0;
						$childTotalDialsPerHour = 0;
						$childAppointmentsPerHour = 0;
						
						$childCallHours = 0;
						$childCallMinutes = 0;
						
						$childOutboundDials = 0;
						
						if( !empty($selectedSkills) )
						{
							$childCalls = LeadCallHistory::model()->findAll(array(
								'with' => array('list'),
								'condition' => '
									t.agent_account_id = :agent_account_id 
									AND t.start_call_time >= :dateFilterStart 
									AND t.start_call_time <= :dateFilterEnd 
									AND t.end_call_time > t.start_call_time
									AND list.skill_id IN ('.$selectedSkills.')
									AND t.is_skill_child=1
								',
								'params' => array(
									':agent_account_id' => $model->id,
									':dateFilterStart' => date('Y-m-d 00:00:00', strtotime($dateFilterStart)),
									':dateFilterEnd' => date('Y-m-d 23:59:59', strtotime($dateFilterEnd)),
								),
							));
							
							$childOutboundDials = count($childCalls);
							
							$childVoiceContacts = LeadCallHistory::model()->count(array(
								'with' => array('list','skillChildDisposition'),
								'condition' => '
									t.agent_account_id = :agent_account_id 
									AND t.start_call_time >= :dateFilterStart 
									AND t.start_call_time <= :dateFilterEnd
									AND t.is_skill_child=1				
									AND skillChildDisposition.id IS NOT NULL
									AND skillChildDisposition.is_voice_contact=1
									AND list.skill_id IN ('.$selectedSkills.')
								',
								'params' => array(
									':agent_account_id' => $model->id,
									':dateFilterStart' => date('Y-m-d 00:00:00', strtotime($dateFilterStart)),
									':dateFilterEnd' => date('Y-m-d 23:59:59', strtotime($dateFilterEnd)),
								),
							));
						}
						
						
						if( $childOutboundDials > 0 && $childCallHours > 0 )
						{
							$childTotalDialsPerHour = $childOutboundDials / $childCallHours;
						}
						
						$childSkilWrapTimes = LeadCallWrapTime::model()->findAll(array(
							'condition' => '
								agent_account_id = :agent_account_id 
								AND start_time >= :dateFilterStart 
								AND start_time <= :dateFilterEnd
								AND end_time > start_time
								AND DATE(start_time) = DATE(end_time)
								AND call_type IN (3,6) 
								AND main_skill_id IN ('.$selectedSkills.')
							',
							'params' => array(
								':agent_account_id' => $model->id,
								':dateFilterStart' => date('Y-m-d 00:00:00', strtotime($dateFilterStart)),
								':dateFilterEnd' => date('Y-m-d 23:59:59', strtotime($dateFilterEnd)),
							),
						));
						

						$childSkillWrapTimeMinutes = 0;
						
						if( $childSkilWrapTimes )
						{
							foreach( $childSkilWrapTimes as $childSkilWrapTime )
							{
								$childSkillWrapTimeMinutes += round( (strtotime($childSkilWrapTime->end_time) - strtotime($childSkilWrapTime->start_time)) / 60,2);
							}
						}
						
						$childHours = LeadCallWrapTime::model()->findAll(array(
							'condition' => '
								agent_account_id = :agent_account_id 
								AND start_time >= :dateFilterStart 
								AND start_time <= :dateFilterEnd
								AND end_time > start_time
								AND DATE(start_time) = DATE(end_time)
								AND call_type IN (3,6) 
								AND main_skill_id IN ('.$selectedSkills.')
							',
							'params' => array(
								':agent_account_id' => $model->id,
								':dateFilterStart' => date('Y-m-d 00:00:00', strtotime($dateFilterStart)),
								':dateFilterEnd' => date('Y-m-d 23:59:59', strtotime($dateFilterEnd)),
							),
							'group' => 'lead_id',
							'order' => 'start_time ASC',
						));
						
						if( $childHours )
						{
							foreach( $childHours as $childHour )
							{
								$childHourEnd = LeadCallWrapTime::model()->find(array(
									'condition' => '
										id != :id
										AND agent_account_id = :agent_account_id 
										AND lead_id = :lead_id 
										AND start_time >= :dateFilterStart 
										AND start_time <= :dateFilterEnd
										AND end_time > start_time
										AND DATE(start_time) = DATE(end_time)
									',
									'params' => array(
										':id' => $childHour->id,
										':agent_account_id' => $model->id,
										':lead_id' => $childHour->lead_id,
										':dateFilterStart' => date('Y-m-d H:i:s', strtotime($childHour->start_time)),
										':dateFilterEnd' => date('Y-m-d H:i:s', strtotime('+1 hour', strtotime($childHour->start_time))),
									),
									'order' => 'end_time DESC',
								));
								
								if( $childHourEnd )
								{
									$childCallMinutes += round( (strtotime($childHourEnd->end_time) - strtotime($childHour->start_time)) / 60,2);
								}
							}

							$childCallHours =  floor($childCallMinutes/60);
							$childCallMinutes =   $childCallMinutes % 60;
						}
						
						
						if( $primaryOutboundDials > 0 || $childOutboundDials > 0 )
						{
							$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $team);
						
							$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $model->accountUser->first_name.' '.$model->accountUser->last_name);
							
							$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $clockedHours);

							if( strlen($primaryCallHours) == 1)
							{
								$primaryCallHours = '0'.$primaryCallHours;
							}
							
							if( strlen($primaryCallMinutes) == 1)
							{
								$primaryCallMinutes = '0'.$primaryCallMinutes;
							}
							
							$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $primaryCallHours.':'.$primaryCallMinutes);
							
							$primarySkillWrapTimeHours =  floor($primarySkillWrapTimeMinutes/60);
							$primarySkillWrapTimeMinutes =   $primarySkillWrapTimeMinutes % 60;
							
							if( strlen($primarySkillWrapTimeHours) == 1)
							{
								$primarySkillWrapTimeHours = '0'.$primarySkillWrapTimeHours;
							}
							
							if( strlen($primarySkillWrapTimeMinutes) == 1)
							{
								$primarySkillWrapTimeMinutes = '0'.$primarySkillWrapTimeMinutes;
							}
							
							$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $primarySkillWrapTimeHours.':'.$primarySkillWrapTimeMinutes);
							
							$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $primaryOutboundDials);
							
							$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $primaryVoiceContacts);
							
							$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, round($primaryAppointments, 2));
							
							$objPHPExcel->getActiveSheet()->SetCellValue('I'.$ctr, round($primaryTotalDialsPerHour, 2));
							
							$objPHPExcel->getActiveSheet()->SetCellValue('J'.$ctr, round($primaryAppointmentsPerHour, 2));
							
							$objPHPExcel->getActiveSheet()->SetCellValue('K'.$ctr, (round($primaryConversionRate, 2) * 100).'%');
							
							
							if( strlen($childCallHours) == 1)
							{
								$childCallHours = '0'.$childCallHours;
							}
							
							if( strlen($childCallMinutes) == 1)
							{
								$childCallMinutes = '0'.$childCallMinutes;
							}
							
							$objPHPExcel->getActiveSheet()->SetCellValue('L'.$ctr, $childCallHours.':'.$childCallMinutes);
							
							$childSkillWrapTimeHours =  floor($childSkillWrapTimeMinutes/60);
							$childSkillWrapTimeMinutes =   $childSkillWrapTimeMinutes % 60;
							
							if( strlen($childSkillWrapTimeHours) == 1)
							{
								$childSkillWrapTimeHours = '0'.$childSkillWrapTimeHours;
							}
							
							if( strlen($childSkillWrapTimeMinutes) == 1)
							{
								$childSkillWrapTimeMinutes = '0'.$childSkillWrapTimeMinutes;
							}
							
							$objPHPExcel->getActiveSheet()->SetCellValue('M'.$ctr, $childSkillWrapTimeHours.':'.$childSkillWrapTimeMinutes);
							
							$objPHPExcel->getActiveSheet()->SetCellValue('N'.$ctr, $childOutboundDials);
							$objPHPExcel->getActiveSheet()->SetCellValue('O'.$ctr, $childVoiceContacts);
							$objPHPExcel->getActiveSheet()->SetCellValue('P'.$ctr, round($childTotalDialsPerHour, 1));
							
							$ctr++;	
						}
					}
										
					if( $page == 'queueListing' )
					{
						$dials = 0;
		
						$contractedLeads = 0;
						
						$leads = Lead::model()->findAll(array(
							'with' => 'list',
							'condition' => 'list.customer_id = :customer_id AND t.type=1 AND t.status=1',
							'params' => array(
								':customer_id' => $model->customer_id,
							),
						));
						
						if( $leads )
						{
							foreach( $leads as $lead )
							{
								$dials += $lead->number_of_dials;
							}
						}
						
						$contractLevels = ContractSubsidyLevel::model()->findAll(array(
							'condition' => 'contract_id = :contract_id',
							'params' => array(
								':contract_id' => $model->contract_id,
							),
						));
						
						if( $contractLevels )
						{
							foreach( $contractLevels as $contractLevel )
							{
								if( $contractLevel->column_name == 'high' )
								{
									$contractedLeads += $contractLevel->column_value;
								}
								
								if( $contractLevel->column_name == 'high' )
								{
									$contractedLeads += $contractLevel->column_value;
								}
							}
						}
		
	 
						if( $dials > 0 && $contractedLeads > 0 )
						{
							$goalsDials = number_format($contractedLeads / $dials, 2);
						}
						else
						{
							$goalsDials = '0';
						}
						
						$date = new DateTime('now');
						$date->modify('last day of this month');
						
		
						$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, isset($model->customer->company) ? $model->customer->company->company_name : '');
						$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $model->customer->firstname . ', '. $model->customer->lastname);
						$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $model->skill->skill_name);
						$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $model->contract->contract_name);
						$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, count($leads));
						$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, count($leads));
						$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, count($leads));
						$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $dials);
						$objPHPExcel->getActiveSheet()->SetCellValue('I'.$ctr, $date->format('m/d/Y'));
						$objPHPExcel->getActiveSheet()->SetCellValue('J'.$ctr, $model->contract->fulfillment_type == 1 ? 'Goal' : 'Lead');
						$objPHPExcel->getActiveSheet()->SetCellValue('K'.$ctr, $contractedLeads);
						$objPHPExcel->getActiveSheet()->SetCellValue('L'.$ctr, $goalsDials);
						$objPHPExcel->getActiveSheet()->SetCellValue('M'.$ctr, 0);
						$objPHPExcel->getActiveSheet()->SetCellValue('N'.$ctr, 0);
						$objPHPExcel->getActiveSheet()->SetCellValue('O'.$ctr, 0);
						
						$ctr++;	
					}
							
					if( $page == 'stateFarm' )
					{
						$lineContent = '';
						
						$callDateTime = new DateTime($model->start_call_time, new DateTimeZone('America/New_York'));
						$callDateTime->setTimezone(new DateTimeZone('America/Denver'));	
						
						$appointmentDate = '';
						$appointmentTime = '';
						$appointmentType = '';
						
						if( isset($model->calendarAppointment) )
						{
							$appointmentDateTime = new DateTime($model->calendarAppointment->start_date, new DateTimeZone('America/New_York'));
							$appointmentDateTime->setTimezone(new DateTimeZone('America/Denver'));	
							
							$appointmentDate = $appointmentDateTime->format('m/d/Y');
							
							$appointmentTime = $appointmentDateTime->format('g:i A');
							
							
							if( $model->calendarAppointment->location == 1 )
							{
								$appointmentType = 'Office';
							}
							
							if( $model->calendarAppointment->location == 2 )
							{
								$appointmentType = 'Home';
							}
							
							if( $model->calendarAppointment->location == 3 )
							{
								$appointmentType = 'Phone';
							}
							
							if( $model->calendarAppointment->location == 4 )
							{
								$appointmentType = 'Skype';
							}
						}
						

						$lineContent .= $model->customer->firstname . ';';
						
						$lineContent .= $model->customer->lastname . ';';
						
						$lineContent .= $model->customer->account_number . ';';
						
						$lineContent .= State::model()->findByPk($model->customer->state)->name . ';';
						
						$lineContent .= $model->lead->first_name . ';';
						
						$lineContent .= $model->lead->last_name . ';';
						
						$lineContent .= $model->dial_number . ';';
						
						$lineContent .= $callDateTime->format('m/d/Y') . ';';
						
						$lineContent .= $callDateTime->format('g:i A') . ';';
						
						$lineContent .= $appointmentDate . ';';
						
						$lineContent .= $appointmentTime . ';';
						
						$lineContent .= $appointmentType . ';';
						
						$lineContent .= $model->disposition . ';';
						
						
						$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $lineContent);
						
						$ctr++;	
					}
							
					if( $page == 'employeeSummary' )
					{
						$totalScheduledWorkHours = 0;
		
						$startDate = strtotime('monday this week');
						$endDate = strtotime('sunday this week');	
						
						while( $startDate <= $endDate )
						{
							$daySchedules = AccountLoginSchedule::model()->findAll(array(
								'condition' => 'account_id = :account_id AND day_name = :day_name',
								'params' => array(
									':account_id' => $model->id,
									':day_name' => date('l', $startDate),
								),
							));
						
							if( $daySchedules )
							{
								foreach( $daySchedules as $daySchedule )
								{
									$subtractTime = strtotime($daySchedule->end_time) - strtotime($daySchedule->start_time);
									$hours = floor($subtractTime/3600);
									
									$minutes = round(($subtractTime%3600)/60);
									
									if( $minutes >= 30 )
									{
										$hours += .5;
									}
									
									if( $hours > 0 && $daySchedule->type == 1)
									{
										$totalScheduledWorkHours += $hours;
									}
								}
							}
							
							$startDate = strtotime('+1 day', $startDate);
						}
						
						if( $model->status == 1 )
						{
							$status = 'ACTIVE';
						}
						else
						{
							$status = 'INACTIVE';
						}

						
						$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $model->accountUser->employee_number);
						
						$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $model->accountUser->first_name);
						
						$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $model->accountUser->last_name);
						
						$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $model->accountUser->full_time_status);
						
						$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $status);
						
						$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $model->accountUser->date_hire);
						
						$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $model->accountUser->date_termination);
						
						$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $model->accountUser->phone_extension);
						
						$objPHPExcel->getActiveSheet()->SetCellValue('I'.$ctr, $model->accountUser->job_title);
						
						$objPHPExcel->getActiveSheet()->SetCellValue('J'.$ctr, $model->getSecurityGroup());
						
						$objPHPExcel->getActiveSheet()->SetCellValue('K'.$ctr, $totalScheduledWorkHours);
						
						$ctr++;	
					}
								
					if( $page == 'agentPerformanceLite' )
					{
						if( $model['total_hours'] != '' )
						{
							$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $model['agent_name']);
							
							if( $model['agent_status'] == 1 )
							{
								$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, 'Active');
							}
							else
							{
								$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, 'Inactive');
							}

							$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, round($model['total_hours'], 2));
							
							$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $model['dials']);
							
							if( $model['dials'] > 0 && $model['total_hours'] > 0 )
							{
								$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, round($model['dials'] / $model['total_hours'], 2));
							}
							else
							{
								
								$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, 0);
							}
							
							$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $model['appointments']);
							
							if( $model['appointments'] > 0 && $model['total_hours'] > 0 )
							{
								$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, round($model['appointments'] / $model['total_hours'], 2));
							}
							else
							{
								
								$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, 0);
							}
							
							$ctr++;	
						}
					}
							
					if( $page == 'genericSkill' )
					{
						$callDate = new DateTime($model['call_date'], new DateTimeZone('America/Chicago'));
						$callDate->setTimezone(new DateTimeZone('America/Denver'));
						
						$callBackDate = new DateTime($model['callback_date'], new DateTimeZone('America/Chicago'));
						$callBackDate->setTimezone(new DateTimeZone('America/Denver'));
						
						$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $model['company_name']);
						$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $model['customer_name']);
						$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $model['lead_phone']);
						$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $model['lead_first_name']);
						$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $model['lead_last_name']);
						$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $model['partner_first_name']);
						$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $model['partner_last_name']);
						$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $callDate->format('m/d/Y g:i A'));
						
						if( $model['is_skill_child'] == 1 )
						{
							$objPHPExcel->getActiveSheet()->SetCellValue('I'.$ctr, 'Child');
						}
						else
						{
							$objPHPExcel->getActiveSheet()->SetCellValue('I'.$ctr, 'Parent');
						}
						
						$objPHPExcel->getActiveSheet()->SetCellValue('J'.$ctr, $model['disposition']);
						
						if( in_array($model['disposition'], array('Call Back', 'Callback', 'Call Back - Confirm')) )
						{
							$objPHPExcel->getActiveSheet()->SetCellValue('K'.$ctr, $callBackDate->format('m/d/Y g:i A'));
						}
						else
						{
							$objPHPExcel->getActiveSheet()->SetCellValue('K'.$ctr, '');
						}
						
						$objPHPExcel->getActiveSheet()->SetCellValue('L'.$ctr, $model['agent_note']);
						$objPHPExcel->getActiveSheet()->SetCellValue('M'.$ctr, $model['agent']);
						
						$ctr++;	
					}
					
					if( $page == 'lowNames' )
					{
						$endDate = '';
						
						if( $model->end_date != '0000-00-00' )
						{
							$endDate = $model->end_date;
						}
						
						$officeStaffs = CustomerOfficeStaff::model()->findAll(array(
							'condition' => 'customer_id = :customer_id AND is_deleted=0 AND is_received_low_on_names_email=1',
							'params' => array(
								':customer_id' => $model->customer_id,
							),
						));
						
						if( $officeStaffs )
						{
							$emailAddresses = array();
							
							foreach( $officeStaffs as $officeStaff )
							{
								$emailAddresses[] = $officeStaff->email_address;
							}
							
							// echo implode(', ', $emailAddresses);
						}
						
						$quantityMinusGoals = $model->contracted_quantity - $model->current_goals;
						$roundedQuantyCallableDividedBy9 = round($model->available_leads/5);
						
						$needsNames = $roundedQuantyCallableDividedBy9 < $quantityMinusGoals ? 'Yes' : 'No';
						
						$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $model->company );
						$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $model->custom_customer_id );
						$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $model->customer_first_name );
						$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $model->customer_last_name );
						$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $model->phone_number );
						
						$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $model->email_address );
						
						if( $emailAddresses )
						{
							$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, implode(', ', $emailAddresses));
						}
						else
						{
							$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, '');
						}
						
						$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $model->skill_name );
						$objPHPExcel->getActiveSheet()->SetCellValue('I'.$ctr, $model->start_date );
						$objPHPExcel->getActiveSheet()->SetCellValue('J'.$ctr, $endDate );
						$objPHPExcel->getActiveSheet()->SetCellValue('K'.$ctr, $model->contracted_quantity );
						$objPHPExcel->getActiveSheet()->SetCellValue('L'.$ctr, $model->current_goals );
						$objPHPExcel->getActiveSheet()->SetCellValue('M'.$ctr, $model->current_dials );
						$objPHPExcel->getActiveSheet()->SetCellValue('N'.$ctr, $model->available_leads );
						$objPHPExcel->getActiveSheet()->SetCellValue('O'.$ctr, $model->recertifiable_leads );
						$objPHPExcel->getActiveSheet()->SetCellValue('P'.$ctr, $model->names_waiting );
						$objPHPExcel->getActiveSheet()->SetCellValue('Q'.$ctr, $needsNames );
					
						$ctr++;
					}
				}
			}
		}
		
		if( $page == 'stateFarm' )
		{
			header('Content-type: text/csv');
			header('Content-Disposition: attachment; filename="'.$filename.'.csv"'); 
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
		}
		else
		{
			header('Content-Type: application/vnd.ms-excel'); 
			header('Content-Disposition: attachment; filename="'.$filename.'.xlsx"'); 
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		}
		
		header('Cache-Control: max-age=0');
		
		$objWriter->save('php://output');
	}
	
	
	public function actionCrmList()
	{	
		$customers = Customer::model()->byStatus(Customer::STATUS_ACTIVE)->byIsDeletedNot()->findAll();
		
		$this->render('crmList', array(
			'customers' => $customers,
		));
	}
	
	public function actionViewCustomerReports($id)
	{
		$customer = Customer::model()->findByPk($id);
		
		$this->render('viewCustomerReports', array(
			'customer' => $customer,
		));
	}
	
	public function actionGenerateCustomerReport($id)
	{
		$customer = Customer::model()->findByPk($id);
		
		
		Yii::import('ext.MYPDF');
		
		$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		spl_autoload_register(array('YiiBase','autoload'));
         
		
		// set default header data
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

		// remove default header/footer
		// $pdf->setPrintHeader(true);
		// $pdf->setPrintFooter(true);

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		// set paddings
		// $pdf->setCellPaddings(0,0,0,0);
		
		// set margins
		$pdf->SetMargins(20,40);
		$pdf->setHeaderMargin(10);
		$pdf->setFooterMargin(30);
		$pdf->SetAutoPageBreak(true, 40);


		//Set zoom 90%
		$pdf->SetDisplayMode(100,'SinglePage','UseNone');

		// set font
		// $pdf->SetFont('times', 'BI', 20);

		$pdf->AddPage();
		

		//Write the html
		$html = $this->renderPartial('customerReportLayout', array('customer'=>$customer), true);
		
		//Convert the Html to a pdf document
		$pdf->writeHTML($html, true, false, true, false, '');
		
		// reset pointer to the last page
		$pdf->lastPage();

		//Close and output PDF document
		$pdf->Output( $customer->getFullName() . '.pdf', 'I');
		Yii::app()->end();
	}
	
	
	//Start of Email monitor actions
	public function actionEmailMonitor($filter='All')
	{
		if( isset($_POST['filter']) )
		{
			$filter = $_POST['filter'];
		}
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => '',
		);
		
		$filters = EmailMonitor::model()->findAll(array(
			'select' => 't.skill_id',
			'group' => 't.skill_id',
			'distinct' => true,
		));
		
		if( $filter == 'All' )
		{
			$models = EmailMonitor::model()->findAll(array(
				'condition' => 'date_created > DATE_SUB(NOW(), INTERVAL 24 HOUR) AND status IN (0,2,4)',
			));
		}
		else
		{
			$models = EmailMonitor::model()->findAll(array(
				'condition' => 'skill_id = :skill_id AND date_created > DATE_SUB(NOW(), INTERVAL 24 HOUR) AND status IN (0,2,4)',
				'params' => array(
					':skill_id' => $filter,
				),
			));
		}
		
		
		if( isset($_POST['ajax']) )
		{
			$html = $this->renderPartial('ajaxEmailMonitorTable', array(
				'models' => $models,
				'filter' => $filter,
				'filters' => $filters,
			), true);
			
			$result['status'] = 'success';
			$result['html'] = $html;
			
			echo json_encode($result);
			Yii::app()->end();
		}
		else
		{
			$this->layout='main-no-navbar';
			
			$this->render('emailMonitor', array(
				'models' => $models,
				'filter' => $filter,
				'filters' => $filters,
			));
		}
	}

	
	public function actionRemoveEmail()
	{
		$model = EmailMonitor::model()->findByPk($_POST['id']);
		
		if($model)
		{
			$model->status = 3; // stop email from going out
			
			$model->save(false);
		}
	}
	
	
	public function actionSendEmail()
	{
		$model = EmailMonitor::model()->findByPk($_POST['id']);
		
		if($model)
		{
			$model->status = 5; // send now
			
			$model->save(false);
		}
	}

	
	public function actionHoldEmail()
	{
		$model = EmailMonitor::model()->findByPk($_POST['id']);
		
		if($model)
		{
			$model->status = 2; // stop email from going out
			
			$model->save(false);
		}
	}
	
	
	public function actionPreviewEmail($id, $filter)
	{
		$model = EmailMonitor::model()->findByPk($id);
		
		if(isset($_POST['EmailMonitor']))
		{
			$model->attributes = $_POST['EmailMonitor'];
			
			if( $model->save() )
			{
				Yii::app()->user->setFlash('success', 'Database has been updated.');
			}
			else
			{
				Yii::app()->user->setFlash('error', 'Sorry but an error occurred. Please try again later.');
			}
			
			$this->redirect(array('reports/previewEmail', 'id'=>$model->id, 'filter'=>$filter));
		}
		
		$this->layout='main-no-navbar';
		$this->render('preview', array('model'=>$model, 'filter'=>$filter));
	}

	
	public function actionRedactorUpload()
	{
		if( $_FILES )
		{
			$dir  = Yii::getPathOfAlias('webroot') . '/fileupload/';
			$baseUrl  = Yii::app()->request->baseUrl . '/fileupload/';
			
			$fileExtension = strtolower( pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION) );
	 
			if ( in_array( $fileExtension, array('jpg', 'jpeg', 'pjpeg', 'png', 'gif')) )
			{
				// setting file's mysterious name
				// $filename = md5(date('YmdHis')).'.'.$fileExtension;
				$filename = $_FILES['file']['name'];
			 
				// copying
				move_uploaded_file($_FILES['file']['tmp_name'], $dir . $filename);
			 
				// displaying file
				echo json_encode(array('filelink' => $baseUrl . $filename));
			}
		}
	}
	
	
	public function actionCronSendEmails()
	{
		ini_set("memory_limit","1024M");

		set_time_limit(0);

		$models = EmailMonitor::model()->findAll(array(
			'condition'=>'status IN ("0", "4", "5")',
			'limit' => 25, 
		));
		
		// $models = EmailMonitor::model()->findAll(array(
			// 'condition'=>'id = 318',
		// ));
		
		if($models)
		{		
			echo 'No of models: '.count($models);
			echo '<br>';
			foreach($models as $model)
			{
				if( (date('Y-m-d H:i:s') >= date('Y-m-d H:i:s', strtotime("+30 minutes", strtotime($model->date_created))) || $model->status == 4 || $model->status == 5) ) //5 = send now
				{
					
					try 
					{	
						// if( filter_var($model->customer->email_address, FILTER_VALIDATE_EMAIL) ) 
						// {
							if($this->sendIcal($model))
							{	
								$model->status = 1; //mail sent
							}
							else
							{	
								$model->status = 4; //mailing error
							}
							
							if( $model->save() )
							{
								echo $model->id;
								echo '<br>';
							}
							else
							{
								print_r($model->getErrors());
							}
						// }
						
					} 
					catch (phpmailerException $e) 
					{
						echo $e->errorMessage(); //Pretty error messages from PHPMailer
					} 
					catch (Exception $e) 
					{
						echo $e->getMessage(); //Boring error messages from anything else!
					}
				}
			}
		}
		
		echo '<br><br>end...';
	}

	
	public function actionQueueViewer()
	{
		$this->layout='main-no-navbar';
		
		$customers = Customer::model()->findAll(array(
			'condition' => 'status=1',
		));
		
		$this->render('queueViewer', array(
			'customers' => $customers,
		));
	}

	
	public function actionTestMail()
	{
		Yii::import('application.extensions.phpmailer.JPhpMailer');

		$mail = new JPhpMailer;
						
		// $mail->Host = "64.251.10.115";
		
		// $mail->IsSMTP(); 		
							
		// $mail->SMTPDebug  = 1;										
								
		// $mail->SMTPAuth = true;
		
		// $mail->SMTPSecure = "tls";   

		// $mail->Port = 587;      
		
		// $mail->Username = "service@engagex.com";  
		
		// $mail->Password = "Engagex123";          											

		$mail->SetFrom('service@engagex.com');
		
		$mail->AddReplyTo('service@engagex.com');

		$mail->Subject = 'test';
		
		$mail->AddAddress('erwin.datu@engagex.com');
		$mail->AddAddress('einlanzer10@gmail.com');

		$mail->MsgHTML('test only');
			
			
		if($mail->Send())
		{
			echo 'mail sent';
		}
		else
		{
			echo 'mail error';
		}
	}

	
	//Agent State
	public function actionAgentState()
	{
		$this->layout='main-no-navbar';
		
		$agentAccounts = Account::model()->findAll(array(
			'with' => 'accountUser',
			'together' => true,
			'condition' => 'accountUser.salary_type="HOURLY"',
		));
		
		if( isset($_POST['ajax']) )
		{
			$html = $this->renderPartial('ajaxAgentStateTable', array(
				'agentAccounts' => $agentAccounts,
			), true);
			
			$result['status'] = 'success';
			$result['html'] = $html;
			
			echo json_encode($result);
			Yii::app()->end();
		}
		else
		{	
			$this->render('agentState', array(
				'agentAccounts' => $agentAccounts,
			));
		}
	}

	
	public function actionForceLogout()
	{
		$authAccount  = Yii::app()->user->account;
		
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		if( isset($_POST['ajax']) && isset($_POST['id']) )
		{
			$model = AccountLoginTracker::model()->find(array(
				'condition' => 'account_id = :account_id',
				'params' => array(
					':account_id' => $_POST['id'],
				),
				'order' => 'date_created DESC',
			));
			
			$currentLoginState = AccountLoginState::model()->find(array(
				'condition' => 'account_id = :account_id',
				'params' => array(
					':account_id' => $_POST['id'],
				),
				'order' => 'date_created DESC',
			));
			
			if( $model )
			{
				$note = 'Forced logout by ';
				$note .= isset($authAccount->accountUser) ? $authAccount->getFullName() : 'Account ID: ' . $authAccount->id;
				
				$model->note = $note;
				$model->status = 2;
				$model->time_out = date('Y-m-d H:i:s');
				$model->login_session_token = sha1(time());
				
				if( $model->save(false) )
				{
					$result['status'] = 'success';
				}
			}
			
			if( $currentLoginState )
			{
				$currentLoginState->end_time = date('Y-m-d H:i:s');
				
				if( $currentLoginState->save(false) )
				{
					$result['status'] = 'success';
				}
			}
		}
		
		echo json_encode($result);
		Yii::app()->end();
	}


	public function actionCallManagement()
	{
		$this->layout='main-no-navbar';
		
		$totalScheduledHours = 0;
		$callManagementTableHtml = '';
		
		$models = AccountUser::model()->findAll(array(
			'with' => 'account',
			'condition' => 't.job_title IN ("Call Agent", "Team Leader") AND account.status = 1 AND account.id NOT IN(4)',
		));
		
		if ( $models )
		{
			foreach( $models as $model )
			{
				$agentWorkTimes = array();
				$agentBreakTimes = array();
				
				$status = 'Out';
				$trStyle = '#FFF';
				
				$currentLoginState = AccountLoginState::model()->find(array(
					'condition' => 'account_id = :account_id',
					'params' => array(
						':account_id' => $model->account_id,
					),
					'order' => 'date_created DESC',
				));
								
				if( $currentLoginState )
				{
					if( $currentLoginState->type == AccountLoginState::TYPE_AVAILABLE && $currentLoginState->end_time == null )
					{
						$status = 'In';
					}
				}
				
				//APH / DPH
				
				//SCHEDULES
				$schedules = AccountLoginSchedule::model()->findAll(array(
					'condition' => 'account_id = :account_id AND day_name = :day_name',
					'params' => array(
						':account_id' => $model->account_id,
						':day_name' => date('l'),
					),
					'order' => 'date_created ASC',
				));
				
							
				if( $schedules )
				{
					foreach( $schedules as $schedule )
					{
						$startTime = date('g:i A', strtotime($schedule->start_time));
						$endTime = date('g:i A', strtotime($schedule->end_time));
						
						$totalScheduledHours += round((strtotime($schedule->end_time) - strtotime($schedule->start_time))/3600, 1);
						
						foreach (Calendar::createTimeRange($startTime, $endTime, '15 minutes') as $time) 
						{
							if( !in_array(date('g:i A', $time), $agentWorkTimes) )
							{
								if( $schedule->type == 1 )
								{
									$agentWorkTimes[] = date('g:i A', $time);
								}
								else
								{
									$agentBreakTimes[] = date('g:i A', $time);
								}
							}
						}
					}
				}
				
				$sql = "
					SELECT
					(
						SELECT SUM(
							CASE WHEN time_out IS NOT NULL THEN TIME_TO_SEC(TIMEDIFF(time_out, time_in))/3600
								ELSE TIME_TO_SEC(TIMEDIFF(DATE_SUB(NOW(), INTERVAL 1 HOUR), time_in))/3600 
							END
						)
						FROM ud_account_login_tracker alt
						WHERE alt.account_id = a.`id`
						AND alt.time_in >= '".date('Y-m-d 00:00:00')."' 
						AND alt.time_in <= '".date('Y-m-d 23:59:59')."'
						AND alt.status !=4 
					) AS total_hours,
					(
						SELECT COUNT(lch.id) 
						FROM ud_lead_call_history lch
						LEFT JOIN ud_lists uls ON uls.id = lch.list_id
						WHERE lch.agent_account_id = a.`id`
						AND lch.start_call_time >= '".date('Y-m-d 00:00:00')."' 
						AND lch.start_call_time <= '".date('Y-m-d 23:59:59')."' 
						AND uls.skill_id IN (11,12,15,16,17,19,20,21,22,23,24,25,26,27,28,29)
						AND lch.status != 4
					) AS dials
					FROM ud_account a
					WHERE a.id='".$model->account_id."'
				";
				
				// echo '<br><br>';
				// echo $sql;
				// echo '<br><br>';
				
				$connection = Yii::app()->db;
				$command = $connection->createCommand($sql);
				$agent = $command->queryRow();

				$callManagementTableHtml .= '<tr style="background:'.$trStyle.';">';
							
					$callManagementTableHtml .= '<td>';
					
						$callManagementTableHtml .= '<div style="width:200px;">';
							$callManagementTableHtml .= CHtml::link($model->first_name.' '.$model->last_name, array('/hr/accountUser/assignments/', 'id'=>$model->account_id), array('target'=>'_blank')); 
						$callManagementTableHtml .= '</div>';	
						
					$callManagementTableHtml .= '</td>';
							
					$callManagementTableHtml .= '<td>'.$status.'</td>';
						
					$callManagementTableHtml .= '<td class="center">';
					
						if( $agent['dials'] > 0 && $agent['total_hours'] > 0 )
						{
							$callManagementTableHtml .= round($agent['dials'] / $agent['total_hours'], 2);
						}
						else
						{
							
							$callManagementTableHtml .= '0';
						}
						
					$callManagementTableHtml .= '</td>';

					foreach (Calendar::createTimeRange('7:00 AM', '10:00 PM', '15 minutes') as $time) 
					{
						if( in_array(date('g:i A', $time), $agentWorkTimes) || in_array(date('g:i A', $time), $agentBreakTimes) )
						{
							if( in_array(date('g:i A', $time), $agentWorkTimes) )
							{
								$callManagementTableHtml .= '<td class="success">&nbsp;</td>';
							}
							else
							{
								$callManagementTableHtml .= '<td class="info">&nbsp;</td>';
							}
						}
						else
						{
							$callManagementTableHtml .= '<td class="">&nbsp;</td>';

						}
					}
								
				$callManagementTableHtml .= '</tr>';
			}
		}
		else
		{
			$callManagementTableHtml .= '<tr><td colspan="38">No results found.<td></tr>';
		}
		
		if( isset($_POST['ajax']) )
		{
			$html = $this->renderPartial('ajaxCallManagementTable', array(
				'models' => $models,
				'totalScheduledHours' => $totalScheduledHours,
				'callManagementTableHtml' => $callManagementTableHtml,
			), true);
			
			$result['status'] = 'success';
			$result['html'] = $html;
			
			echo json_encode($result);
			Yii::app()->end();
		}
		else
		{	
			$this->render('callManagement', array(
				'models' => $models,
				'totalScheduledHours' => $totalScheduledHours,
				'callManagementTableHtml' => $callManagementTableHtml,
			));
		}
	}	

	
	//Caller Id
	public function actionCallerIdListing()
	{
		$models = CompanyDid::model()->findAll();
		
		// $dataProvider = new CArrayDataProvider($models, array(
			// 'pagination' => array(
				// 'pageSize' => 500,
			// )
		// ));
		
		$this->render('callerIdListing', array(
			'models' => $models, 
		));
	}
	
	public function actionViewDidAssignedCustomers($id)
	{
		$model = CompanyDid::model()->findByPk($id);
		
		$models = CustomerSkill::model()->findAll(array(
			'group' => 't.customer_id',
			'with' => array('customer', 'customer.company'),
			'condition' => 't.skill_caller_option_customer_choice=2 AND LOWER(company.company_name) = :company_name AND SUBSTR(customer.phone,2,3) = :area_code',
			'params' => array(
				':company_name' => strtolower($model->company_name),
				':area_code' => $model->area_code,
			),
		));
		
		$this->render('viewDidAssignedCustomers', array(
			'model' => $model,
			'models' => $models,
		));
	}

	public function actionRemoveDid($id)
	{
		$status = 'danger';
		$message = 'Record not found.';
		
		$model = CompanyDid::model()->findByPk($id);
		
		$did = $model->did;
		
		if( $model && $model->delete() )
		{
			$status = 'success';
			$message = '<b>DID: '.$did.'</b> was successfully deleted.';
			
			//Send Invoice Email
			Yii::import('application.extensions.phpmailer.JPhpMailer');
	
			$mail = new JPhpMailer(true);
			
			$mail->Host = "64.251.10.115";
			
			$mail->IsSMTP(); 		
								
			$mail->SMTPDebug  = 1;										
									
			$mail->SMTPAuth = true;
			
			$mail->SMTPSecure = "tls";   

			$mail->Port = 587;      
			
			$mail->Username = "service@engagex.com";  
			
			$mail->Password = "Engagex123"; 
			
			$mail->AllowEmpty = true;
	
			$mail->SetFrom('service@engagex.com');
			
			$mail->Subject = 'Test - Cancel DID';

			$mail->MsgHTML($did);
			
			$mail->AddAddress('helpdesk@engagex.com');
			
			$mail->AddBCC('erwin.datu@engagex.com');

			$mail->Send();
		}
		
		Yii::app()->user->setFlash($status, $message);
		$this->redirect(array('callerIdListing'));
	}

	public function sendIcal($model)
	{
		// $valid = false;
		
		// if( isset($model->disposition) )
		// {
			// $disposition = $model->disposition;
			
			// $valid = true;
		// }
		// else
		// {
			// $latestCallHistory = LeadCallHistory::model()->find(array(
				// 'condition' => 'lead_id = :lead_id',
				// 'params' => array(
					// ':lead_id' => $model->lead_id,
				// ),
				// 'order' => 'date_created DESC',
			// ));

			// if( $latestCallHistory && $latestCallHistory->is_skill_child == 1 )
			// {
				// $disposition = $latestCallHistory->skillChildDisposition;
				
				// $valid = true;
			// }
		// }
		
		if( $model->is_child_skill == 0 )
		{
			$disposition = SkillDisposition::model()->findByPk($model->disposition_id);	
		}
		
		if( $model->is_child_skill == 1 )
		{
			$disposition = SkillChildDisposition::model()->findByPk($model->child_disposition_id);	
		}
		
		if( $disposition )
		{		
			// $ccs = !empty($disposition->cc) ?  explode(',', $disposition->cc) : array();
			$ccs = !empty($disposition->cc) ?  $disposition->cc : '';
			
			// if( $ccs )
			// {
				// foreach( $ccs as $cc )
				// {
					// $mail->AddCC($cc);
				// }
			// }

			// $bccs = !empty($disposition->bcc) ?  explode(',', $disposition->bcc) : array();
			$bccs = !empty($disposition->bcc) ?  $disposition->bcc . ', erwin.datu@engagex.com, jim.campbell@engagex.com' : 'erwin.datu@engagex.com, jim.campbell@engagex.com';
			
			// if( $bccs )
			// {
				// foreach( $bccs as $bcc )
				// {
					// $mail->AddBCC($bcc);
				// }
			// }
						
			$calendarAppointment = $model->calendarAppointment;
			$customer = $model->customer;
		
			$mailName = 'Engagex Service';
			$emailAddress = 'service@engagex.com';
			$mime_boundary = "----Meeting Booking-".md5(time());
			
			$replyTo = $disposition->from;
			$mailSubject = $model->getReplacementCodeValues($disposition->subject);
			$emailMonitorContent = $model->html_content;
		
			//Create Email Headers
			$headers = "From: {$mailName} <".$emailAddress.">\n";
			$headers .= "Reply-To: {$replyTo} <".$emailAddress.">\n";

			$headers .= "MIME-Version: 1.0\n";
			$headers .= "Content-Type: multipart/alternative; boundary=\"$mime_boundary\"\n";
			$headers .= "Content-class: urn:content-classes:calendarmessage\n";
			
			if(!empty($bccs))
				$headers .= 'Bcc: '. $bccs . "\r\n";
			
			if(!empty($ccs))
				$headers .= 'Cc: '. $ccs . "\r\n";
			
			$recipientHolder = array();

			//Create Email Body (HTML)
			$message = '';
			$message .= "--$mime_boundary\n";
			$message .= "Content-Type: text/html; charset=UTF-8\n";
			$message .= "Content-Transfer-Encoding: 8bit\n\n";

			$message .= "<html>\n";
			$message .= "<body>\n";
			$message .= $emailMonitorContent;
			$message .= "</body>\n";
			$message .= "</html>\n";
			
			##Disposition Attachments ##
			if( $model->is_child_skill == 1 )
			{
				$attachments = SkillChildDispositionEmailAttachment::model()->findAll(array(
					'condition' => 'skill_disposition_id = :skill_disposition_id',
					'params' => array(
						':skill_disposition_id' => $disposition->id,
					),
				));
			}
			else
			{
				$attachments = SkillDispositionEmailAttachment::model()->findAll(array(
					'condition' => 'skill_disposition_id = :skill_disposition_id',
					'params' => array(
						':skill_disposition_id' => $disposition->id,
					),
				));
			}
			
			if( $attachments )
			{
				foreach( $attachments as $attachment )
				{
					// $mail->AddAttachment( Yii::getPathOfAlias('webroot') . '/fileupload/' . $attachment->fileUpload->original_filename );
					$filenamePath = Yii::getPathOfAlias('webroot') . '/fileupload/' . $attachment->fileUpload->original_filename;
					$v = $attachment->fileUpload->original_filename;
					$file = $filenamePath;
					$file_size = filesize($file);
					$handle = fopen($file, "r");
					$content = fread($handle, $file_size);
					fclose($handle);
					$content = chunk_split(base64_encode($content));

					$message .= "Content-Type: application/octet-stream; name=\"".$v."\"\r\n"; // use different content types here
					$message .= "Content-Transfer-Encoding: base64\r\n";
					$message .= "Content-Disposition: attachment; filename=\"".$v."\"\r\n\r\n";
					$message .= $content."\r\n\r\n";
					$message .= "--".$mime_boundary."--"."\r\n";
				}
			}
			
			### ICAL ####
			 
			if(isset($calendarAppointment) && $calendarAppointment->title == 'APPOINTMENT SET' && $disposition->is_appointment_set == 1)
			{	
				$customer = Customer::model()->findByPk($model->customer_id);		
				
				$timeZone = $customer->getTimeZone();

				$timeZone = timezone_name_from_abbr($timeZone); // dynamically fetched from DB
				
				date_default_timezone_set($timeZone);
				
				$dtStart = date('Ymd\THis', strtotime($calendarAppointment->start_date));
				$dtEnd = date('Ymd\THis', strtotime($calendarAppointment->end_date));
				
				$start_zone = date('O', strtotime($calendarAppointment->start_date));
				$end_zone = date('O', strtotime($calendarAppointment->end_date));
				
				$dtStamp = date('Ymd\THis');
				
				echo '<br>'.$dtStart.'<br>';
				$location = Calendar::model()->locationOptionsLabel($calendarAppointment->location);
				// $summary = $calendarAppointment->lead->getFullName().'-'.$calendarAppointment->title;
				$summary = $calendarAppointment->lead->getFullName();
				$customerName = $customer->getFullName();
				$customerEmail = $customer->email_address;
				$description = $calendarAppointment->details;
					 
				$event_id = uniqid();
				$sequence = 0;
				$status = 'CONFIRMED';

				$ical = "BEGIN:VCALENDAR\r\n";
				$ical .= "VERSION:2.0\r\n";
				$ical .= "PRODID:-//Microsoft Corporation//Outlook 14.0 MIMEDIR//EN\r\n";
				$ical .= "METHOD:PUBLISH\r\n";
				
				
				$ical .= "BEGIN:VTIMEZONE\n";
				$ical .= "TZID:{$timeZone}\n";
				$ical .= "TZURL:http://tzurl.org/zoneinfo-outlook/{$timeZone}\n";
				$ical .= "X-LIC-LOCATION:{$timeZone}\n";
				$ical .= "BEGIN:DAYLIGHT\n";
				$ical .= "TZOFFSETFROM:{$start_zone}\n";
				$ical .= "TZOFFSETTO:{$end_zone}\n";
				$ical .= "TZNAME:". date("T")."\n";
				$ical .= "DTSTART:{$dtStart}\n";
				$ical .= "END:DAYLIGHT\n";
				$ical .= "BEGIN:STANDARD\n";
				$ical .= "TZOFFSETFROM:{$start_zone}\n";
				$ical .= "TZOFFSETTO:{$end_zone}\n";
				$ical .= "TZNAME:".date("T")."\n";
				$ical .= "DTSTART:{$dtStart}\n";
				$ical .= "END:STANDARD\n";      
				$ical .= "END:VTIMEZONE\n";

				$ical .= "BEGIN:VEVENT\r\n";
				$ical .= "ORGANIZER;CN={$customerName}:MAILTO:".$customerEmail."\r\n";

				$ical .= "UID:".strtoupper(md5($event_id))."\r\n";
				$ical .= "SEQUENCE:".$sequence."\r\n";
				$ical .= "STATUS:".$status."\r\n";

				$ical .= "DTSTAMP:".$dtStamp."\r\n";
				$ical .= "DTSTART;TZID=".$timeZone.":".$dtStart."\r\n";
				$ical .= "DTEND;TZID=".$timeZone.":".$dtEnd."\r\n";

				$ical .= "LOCATION:".$location."\r\n";
				$ical .= "SUMMARY:".$summary."\r\n";
				$ical .= "DESCRIPTION:{$description}"."\r\n";

				$ical .= "END:VEVENT\r\n";
				$ical .= "END:VCALENDAR\r\n";

				### disable attachment of ICAL for now, we have the ICAL in the LINK using replacement_code (see Lead Call History) ##
				#$message .= "--$mime_boundary\n";							
				#$message .= "Content-Type: text/calendar;name=\"meeting.ics\";method=REQUEST\n";
				#$message .= "Content-Transfer-Encoding: 8bit\n\n";
				#$message .= $ical;     
			}
			
			if(isset($model->lead) && isset($model->lead->list) && isset($model->lead->list->calendar))
			{
				$calendar = $model->lead->list->calendar;
				
				if( isset($calendarAppointment) && isset($calendarAppointment->calendar) )
				{
					$calendar = $calendarAppointment->calendar; 
				}
				
				// $criteria = new CDbCriteria;
				// $criteria->compare('customer_id', $customer->id);
				// $criteria->addCondition('email_address IS NOT NULL OR email_address != ""');
				// $criteria->addCondition('is_received_email > 0');
				// $criteria->compare('is_deleted', 0);
				
				$customerOfficeStaffs = CustomerOfficeStaff::model()->findAll(array(
					'condition' => 'customer_id = :customer_id AND is_received_email > 0 AND is_deleted=0 AND (email_address IS NOT NULL OR email_address != "")',
					'params' => array(
						':customer_id' => $customer->id,
					),
				));
				
				foreach($customerOfficeStaffs as $staff)
				{
					## 3 = ALL CALENDAR in that OFFICE where the Staff was assigned to. ##
					if($staff->is_received_email == 3 && $staff->customer_office_id == $calendar->office_id)
					{
						if(!isset($recipientHolder[$staff->email_address]))
							$recipientHolder[$staff->email_address] = $staff->email_address;
					}
					
					if($staff->is_received_email == 1)
					{
						$existingCalenderStaffReceiveEmail = CalenderStaffReceiveEmail::model()->find(array(
							'condition' => 'staff_id = :staff_id AND calendar_id = :calendar_id',
							'params' => array(
								':staff_id' => $staff->id,
								':calendar_id' => $calendar->id,
							),
						));
						
						if($existingCalenderStaffReceiveEmail !== null)
						{
							if(!isset($recipientHolder[$staff->email_address]))
								$recipientHolder[$staff->email_address] = $staff->email_address;
						}
					}
					
				}
			}
			
			// if(!isset($recipientHolder[$model->customer->email_address]))
				// $recipientHolder[$model->customer->email_address] = $model->customer->email_address;
			
			
			$recipients = implode(',',$recipientHolder);
			
			//SEND MAIL
			// $mail_sent = mail('jim.campbell@engagex.com', $mailSubject, $message, $headers );
			// $mail_sent = mail('markjuan169@gmail.com', $mailSubject, $message, $headers );
			// mail('erwin.datu@engagex.com', $mailSubject, $message, $headers );
			$mail_sent = mail($recipients, $mailSubject, $message, $headers );

			if($mail_sent)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		
		
		return false;
	}

	public function actionTest()
	{
		$customer = Customer::model()->findByPk(49);
		$calendar = $model->calendarAppointment->calendar;
		$recipientHolder = array();
		
		$criteria = new CDbCriteria;
		$criteria->compare('customer_id', $customer->id);
		$criteria->addCondition('email_address IS NOT NULL OR email_address != ""');
		$criteria->addCondition('is_received_email > 0');
		
		$customerOfficeStaffs = CustomerOfficeStaff::model()->findAll($criteria);
		
		
		foreach($customerOfficeStaffs as $staff)
		{
			## 2 = ALL CALENDAR in that OFFICE where the Staff was assigned to. ##
			if($staff->is_received_email == 2 && $staff->customer_office_id == $calendar->office_id)
			{
				$recipientHolder[] = $staff->email_address;
			}
			
			if($staff->is_received_email == 1)
			{
				$existingCalenderStaffReceiveEmail = CalenderStaffReceiveEmail::model()->find(array(
					'condition' => 'staff_id = :staff_id AND calendar_id = :calendar_id',
					'params' => array(
						':staff_id' => $staff->id,
						':calendar_id' => $calendar->id,
					),
				));
				
				if($existingCalenderStaffReceiveEmail !== null)
				{
					$recipientHolder[] = $staff->email_address;
				}
			}
			
		}
	}
	
	
	//Call History Monitor
	public function actionCallHistoryMonitor()
	{
		$models = LeadCallHistory::model()->findAll(array(
			'condition' => 'DATE(date_created) > "2016-03-20" AND disposition_id IS NULL AND skill_child_disposition_id IS NULL AND status=1',
			'order' => 'date_created DESC',
		));
		
		$this->render('callHistoryMonitor', array(
			'models' => $models,
		));
	}

	
	public function actionDownloadAgentPerformance($file = null)
	{
		$account = null;
		
		if(!Yii::app()->user->isGuest)
			$authAccount = Yii::app()->user->account;
		
		if ($file == null)
		{
			throw new CHttpException(404,'The requested page does not exist.');
		}
		
		$extension = strtolower(substr(strrchr($file,"."),1));
		
		$explodedFile = explode('/',$file);

		$filePath = Yii::getPathOfAlias('webroot') . '/agentPerformanceReports/' . $file;
		
		$allowDownload = false;
		
		if(file_exists($filePath))
		{
			$allowDownload = true;
		}
		
		if ( $allowDownload )
		{
			// required for IE
			if(ini_get('zlib.output_compression')) { ini_set('zlib.output_compression', 'Off');	}
			
			$ctype="application/force-download";
			
			header("Pragma: public"); 
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private",false); // required for certain browsers
			header("Content-Type: $ctype");

			# change, added quotes to allow spaces in filenames, 
			header("Content-Disposition: attachment; filename=\"".basename($filePath)."\";" );
			
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: ".filesize($filePath));

			readfile("$filePath");
		} 
		else
		{
			// Do processing for invalid/non existing files here
			echo 'File not found.';
		}
	}
	
	public function getBillingProjections($dateFilterStart, $dateFilterEnd = null)
	{
		echo $fromDate = date("Y-m-d",strtotime($dateFilterStart));
		
		$customerSkills = CustomerSkill::model()->findAll(array(
			'with' => 'customer',
			'condition' => '
				t.status=1 
				AND customer.company_id NOT IN(15, 17,18,23, 24, 25, 26, 27)
				AND customer.status=1
				AND customer.is_deleted=0
			',
		));
		
		$data = array();
		foreach($customerSkills as $customerSkill)
		{
			$data[$customerSkill->customer_id] = $this->getCustomerContractCreditAndSubsidy($customerSkill->customer, $customerSkill->contract, $fromDate);
			$data[$customerSkill->customer_id][$customerSkill->contract_id]['Agent ID'] = $customerSkill->customer->custom_customer_id;
			$data[$customerSkill->customer_id][$customerSkill->contract_id]['Customer Name'] = $customerSkill->customer->lastname . ', '. $customerSkill->customer->firstname ;
			$data[$customerSkill->customer_id][$customerSkill->contract_id]['Company'] =  isset($customerSkill->customer->company) ? $customerSkill->customer->company->company_name : '';
			$data[$customerSkill->customer_id][$customerSkill->contract_id]['Skill'] = $customerSkill->skill->skill_name;
			$data[$customerSkill->customer_id][$customerSkill->contract_id]['Contract'] = $customerSkill->contract->contract_name;
			$data[$customerSkill->customer_id][$customerSkill->contract_id]['Quantity'] = 0;
			$data[$customerSkill->customer_id][$customerSkill->contract_id]['Billing Cycle'] = 0;
		}
		
		echo '<pre>';
		print_r($data);
		echo '</pre>';
		exit;
	}
}

?>