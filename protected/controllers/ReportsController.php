<?php 

ini_set('memory_limit', '10000M');
set_time_limit(0);

class ReportsController extends Controller
{
	public $excludeCustomerSql;
	
	public function init()
	{
		$this->excludeCustomerSql = CustomerSkill::model()->removeFromSalesReports();
	}
	
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
	
	public function getCustomerContractCreditAndSubsidy($customer, $contract, $billing_period)
	{
		$contractCreditSubsidys = array();
		$customerSkills = CustomerSkill::model()->findAll(array(
			'with' => 'customer',
			'condition' => '
				t.customer_id = :customer_id AND t.contract_id = :contract_id
				AND customer.is_deleted=0
			',
			'params' => array(
				':customer_id' => $customer->id,
				':contract_id' => $contract->id,
			),

		));
		
		if( $customerSkills )
		{
			foreach($customerSkills as $customerSkill)
			{
				// $customerRemoved = CustomerBillingWindowRemoved::model()->find(array(
					// 'condition' => '
						// customer_id = :customer_id 
						// AND skill_id = :skill_id 
						// AND MONTH(date_created) = MONTH(NOW())
						// AND YEAR(date_created) = YEAR(NOW())
					// ',
					// 'params' => array(
						// ':customer_id' => $customerSkill->customer_id,
						// ':skill_id' => $customerSkill->skill_id,
					// ),
				// ));
					
				// if( isset($customerSkill->contract) && strtotime($billing_period) >= strtotime($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00' )
				// {
					$isBilled = false;
					
					$contract = $customerSkill->contract;
					$contractCreditSubsidys[$contract->id]['contract_name'] = $contract->contract_name;
					$contractCreditSubsidys[$contract->id]['totalCreditAmount'] = 0;
					$contractCreditSubsidys[$contract->id]['totalSubsidyAmount'] = 0;
					
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
							':contract_id' => $customerSkill->contract_id,
							':billing_period' => $billing_period
						),
						'order' => 'date_created DESC'
					));
					
					if( $existingBillingForCurrentMonth )
					{
						$isBilled = true;
						
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
							$isBilled = false;
						}
						else
						{
							$isBilled = true;
						}
					}
					
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
									':month' => date('n', strtotime($billingPeriod)),
									':year' => date('Y', strtotime($billingPeriod))
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
									':month' => date('n', strtotime($billingPeriod)),
									':year' => date('Y', strtotime($billingPeriod))
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
									if( !$isBilled )
									{
										$subsidyAmount = $subsidyPercentInDecimal * $totalAmount; 
									}
									
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
								
								if( $customerCredit->start_month >= $customerCredit->end_month )
								{
									$creditEndDate = date('Y-m-d', strtotime('+1 year', strtotime($creditEndDate)));
								}
							}
							else
							{
								$creditEndDate = date('Y-'.$customerCredit->start_month.'-t');
							}
							
							if( (time() >= strtotime($creditStartDate)) && (time() <= strtotime($creditEndDate)) )
							{
								$totalCreditAmount += $customerCredit->amount;
							}
						}
					}
					
					
					$contractCreditSubsidys[$contract->id]['totalCreditAmount'] = $totalCreditAmount;
					
					$totalReducedAmount = ($totalAmount - $totalCreditAmount - $subsidyAmount);
					if( $totalReducedAmount < 0 )
						$totalReducedAmount = 0;
					
					$contractCreditSubsidys[$contract->id]['totalLeads'] = $totalLeads;
					$contractCreditSubsidys[$contract->id]['totalAmount'] = number_format($totalAmount, 2);
					$contractCreditSubsidys[$contract->id]['totalReducedAmount'] = number_format($totalReducedAmount, 2);
					$contractCreditSubsidys[$contract->id]['totalCreditAmount'] = number_format($totalCreditAmount, 2);
					$contractCreditSubsidys[$contract->id]['subsidyAmount'] = number_format($subsidyAmount, 2);
					$contractCreditSubsidys[$contract->id]['isBilled'] = $isBilled;
				// }
			
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
		
		
		$addedCondition = '';
		
		if( $page == 'aaaReport' )
		{
			$addedCondition = ' AND id IN (50)';
		}

		$skills = Skill::model()->findAll(array(
			'condition' => 'is_deleted=0' . $addedCondition,
			'order' => 'skill_name ASC'
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
	
	
	public function actionExport($page, $selectedSkills='', $contractId='', $dateFilterStart='', $dateFilterEnd='', $searchQuery='')
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
			
			$customerSkillIds = array();
					
			$customerSkills = CustomerSkill::model()->findAll(array(
				'with' => 'customer',
				'select' => 't.id, t.customer_id',
				'condition' => '
					customer.is_deleted = 0
					AND customer.company_id NOT IN("17", "18", "23")
					AND t.status = 1
				',
				'order' => 't.date_created DESC'
			));
			
			if( $customerSkills )
			{
				foreach( $customerSkills as $customerSkill )
				{
					if( !array_key_exists($customerSkill->customer_id, $customerSkillIds) )
					{
						$customerSkillIds[$customerSkill->customer_id] = $customerSkill->id;
					}
				}
			}
			
			##updated by mark 1/20/2017##
			/*
			$models = CustomerSkill::model()->findAll(array(
				'with' => 'customer',
				'condition' => 't.id IN('.implode(', ', $customerSkillIds).')',
				'order' => 'customer.lastname ASC',
				// 'limit' => 500,
			));
			*/
			
			$models = Customer::model()->findAll(array(
				'with' => 'customerSkills',
				'condition' => '
					t.is_deleted = 0
					AND t.company_id NOT IN("17", "18", "23")
					AND (customerSkills.status = 1 OR customerSkills.status IS NULL)
					AND (customerSkills.id IN ('.implode(', ', $customerSkillIds).') OR customerSkills.id IS NULL)
				',
				'order' => 't.lastname ASC',
			));
			
			
			
			// $models = CustomerSkill::model()->findAll(array(
				// 'with' => 'customer',
				// 'condition' => '
					// customer.is_deleted = 0
					// AND customer.status=1 
					// AND customer.company_id NOT IN("17", "18", "23")
					// AND t.status = 1
					// AND (
						// end_month IS NULL
						// OR end_month = ""
						// OR end_month = "0000-00-00" 
						// OR DATE(end_month) > NOW()
					// )
				// ',
				// 'order' => 'customer.lastname ASC'
			// ));
			
			$ctr = 1;

			$headers = array(
				'A' => 'Agent ID',
				'B' => 'Last Name',
				'C' => 'First Name',
				'D' => 'Status',
				'E' => 'Company',
				'F' => 'Phone',
				'G' => 'Email Address',
				'H' => 'Address',
				'I' => 'City',
				'J' => 'State',
				'K' => 'Zip',
				'L' => 'Skills',
				'M' => 'Contracts',
				'N' => 'Promo',
				'O' => 'Quantity',
				'P' => 'Start Date',
				'Q' => 'End Date',
				'R' => 'On Hold',
				'S' => 'Off Hold',
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
				'K' => 'Transaction Type',
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
				'B' => 'Badge ID',
				'C' => 'First Name',
				'D' => 'Last Name',
				'E' => 'Employee Classification',
				'F' => 'Status',
				'G' => 'Start Date',
				'H' => 'Term Date',
				'I' => 'Phone Extension',
				'J' => 'Job Title',
				'K' => 'Security Group',
				'L' => 'Schedule Hours per Week',
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
			
			if( isset($_GET['customer_id']) )
			{
				$headers = array(
					'A' => 'Agent Name',
					'B' => 'Status',
					'C' => 'Total Hours',
					'D' => 'Dials',
					'E' => 'Dials/Hour',
				);
			}
			else
			{
				$headers = array(
					'A' => 'Agent Name',
					'B' => 'Status',
					'C' => 'Total Hours',
					'D' => 'Dials',
					'E' => 'Dials/Hour',
					'F' => 'Appointments',
					'G' => 'Appts/Hour',
				);
			}
			
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
			
			$skills = Skill::model()->findAll(array(
				'select' => 't.id',
				'condition' => 't.is_deleted = 0',
			));
			
			$skillIds = array();
			
			if( $skills )
			{
				foreach( $skills as $skill )
				{
					$skillIds[] = $skill->id;
				}
			}
			
			if( isset($_GET['customer_id']) )
			{
				$sql = "
					SELECT a.id as agent_id, cs.`staff_name` AS agent_name, a.status AS agent_status,
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
						AND uls.skill_id IN (".implode(', ', $skillIds).")
						AND lch.status != 4
						AND lch.customer_id = '".$_GET['customer_id']."'
					) AS dials
					FROM ud_account a
					LEFT JOIN ud_account_user au ON au.`account_id` = a.`id`
					LEFT JOIN ud_customer_office_staff cs ON cs.`account_id` = a.`id`
					WHERE a.`account_type_id` IN (15)
					ORDER BY au.last_name ASC
				";
			}
			else
			{
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
						AND uls.skill_id IN (".implode(', ', $skillIds).")
						AND lch.status != 4
					) AS dials,
					(
						SELECT COUNT(lch.id) 
						FROM ud_lead_call_history lch
						LEFT JOIN ud_lists uls ON uls.id = lch.list_id
						WHERE lch.agent_account_id = a.`id`
						AND lch.start_call_time >= '".$dateFilterStart."'  
						AND lch.start_call_time <= '".$dateFilterEnd."'  
						AND uls.skill_id IN (".implode(', ', $skillIds).")
						AND lch.disposition='Appointment Set'
						AND lch.status != 4
						AND lch.is_skill_child=0
					) AS appointments
					FROM ud_account a
					LEFT JOIN ud_account_user au ON au.`account_id` = a.`id`
					WHERE a.`account_type_id` IN (1,2,12,13,14)
					AND a.`id` NOT IN (4, 5)
					ORDER BY au.last_name ASC
				";
			}
			
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
			
			$skillIds[] = $selectedSkills; 
			
			if( $selectedSkills == 11 )
			{
				$skillIds = array(11, 33);
			}
			
			if( $selectedSkills == 12 )
			{
				$skillIds = array(12, 34);
			}
			
			if( isset($_GET['customer_id']) )
			{
				$sql = "
					SELECT 
						co.company_name as company_name,
						CONCAT (c.firstname, ' ', c.lastname) AS customer_name,
						lch.lead_phone_number AS lead_phone,
						ld.first_name AS lead_first_name, 
						ld.last_name AS lead_last_name,
						ld.partner_first_name AS partner_first_name,
						ld.partner_last_name AS partner_last_name,
						ld.email_address AS lead_email,
						lch.is_skill_child,
						lch.disposition,
						lch.disposition_detail,
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
					WHERE ls.skill_id IN(".implode(', ', $skillIds).")
					AND lch.disposition IS NOT NULL 
					AND lch.start_call_time >= '".date('Y-m-d 00:00:00', strtotime($dateFilterStart2))."' 
					AND lch.start_call_time <= '".date('Y-m-d 23:59:59', strtotime($dateFilterEnd2))."' 
					AND lch.status !=4 
					AND lch.customer_id = '".$_GET['customer_id']."'
					ORDER BY lch.start_call_time DESC
				";
			}
			else
			{
				$sql = "
					SELECT 
						co.company_name as company_name,
						CONCAT (c.firstname, ' ', c.lastname) AS customer_name,
						lch.lead_phone_number AS lead_phone,
						ld.first_name AS lead_first_name, 
						ld.last_name AS lead_last_name,
						ld.partner_first_name AS partner_first_name,
						ld.partner_last_name AS partner_last_name,
						ld.email_address AS lead_email,
						lch.is_skill_child,
						lch.disposition,
						lch.disposition_detail,
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
					WHERE ls.skill_id IN(".implode(', ', $skillIds).")
					AND lch.disposition IS NOT NULL 
					AND lch.start_call_time >= '".date('Y-m-d 00:00:00', strtotime($dateFilterStart))."' 
					AND lch.start_call_time <= '".date('Y-m-d 23:59:59', strtotime($dateFilterEnd))."'
					AND lch.status !=4 
					ORDER BY lch.start_call_time DESC
				";
			}
			
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
				'H' => 'Lead Email Address',
				'I' => 'Date/Time',
				'J' => 'Skill',
				'K' => 'Disposition',
				'L' => 'Sub Disposition',
				'M' => 'Callback Date/Time',
				'N' => 'Disposition Note',
				'O' => 'Agent',
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
				'with' => array('customer', 'skill'),
				'condition' => '
					t.company NOT IN ("Training Company", "Test Company", "Engagex Inside Sales", "Waxie", "Mountain View Network", "Audigy Group", "Graton", "GunLake")
					AND t.next_available_calling_time NOT IN ("On Hold", "Cancelled", "Removed")
					AND customer.status = 1 
					AND customer.is_deleted = 0 
					AND skill.status = 1 
				',
				// 'limit' => 5,
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
				'H' => 'Status',
				'I' => 'Skill',
				'J' => 'Start Date',
				'K' => 'End Date',
				'L' => 'Qty',
				'M' => 'Current Goal Count',
				'N' => 'Dials in Current Month',
				'O' => 'Callable Now',
				'P' => 'Not Callable Now',
				'Q' => 'Recertifiable',
				'R' => 'Recyclabe Names',
				'S' => 'Names waiting',
				'T' => 'Needs Names',
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
				'condition' => 't.customer_id NOT IN (48)',
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
				'I' => 'Cellphone Count',
				'J' => 'Bad Count',
				'K' => '% Not Imported',
				'L' => 'List Status',
				'M' => 'Email',
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

			//Enrollments
			$enrollmentModels = CustomerSkill::model()->findAll(array(
				'condition' => '
					DATE(date_created) >= "'.date('Y-m-d', strtotime($dateFilterStart)).'" 
					AND DATE(date_created) <= "'.date('Y-m-d', strtotime($dateFilterEnd)).'"
					AND date_created NOT BETWEEN "2016-12-22 05:03:14" 
					AND "2016-12-22 05:03:32"
					'.$this->excludeCustomerSql.'
				',
				'order' => 'date_created DESC'
			));
			
			if( $enrollmentModels )
			{
				foreach( $enrollmentModels as $enrollmentModel )
				{
					$totalLeads = 0;
					$contractedAmount = 0;
					
					$customer = Customer::model()->find(array(
						'condition' => 'id = :customer_id',
						'params' => array(
							':customer_id' => $enrollmentModel->customer_id,
						),
					));

					if( $customer )
					{
						$contract = $enrollmentModel->contract;

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
											$customerSkillLevelArray = $enrollmentModel->getCustomerSkillLevelArray();
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
											':customer_id' => $enrollmentModel->customer_id,
											':contract_id' => $enrollmentModel->contract_id,
											':skill_id' => $enrollmentModel->skill_id,
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
											$customerSkillLevelArray = $enrollmentModel->getCustomerSkillLevelArray();
											
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
											':customer_id' => $enrollmentModel->customer_id,
											':contract_id' => $enrollmentModel->contract_id,
											':skill_id' => $enrollmentModel->skill_id,
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
							
							$selectedSalesReps = '';
							
							$salesReps = CustomerSalesRep::model()->findAll(array(
								'condition' => 'customer_id = :customer_id',
								'params' => array(
									':customer_id' => $enrollmentModel->customer_id,
								),
							));
							
							if( $salesReps )
							{
								$salesAgents = AccountUser::listSalesAgents();
											
								foreach( $salesReps as $salesRep )
								{
									// if( isset($salesRep->account) )
									// {
										// $selectedSalesReps .= $salesRep->account->getFullName() . ', ';
									// }
									
									$selectedSalesReps .= $salesAgents[$salesRep->sales_rep_account_id];
								}
								
								$selectedSalesReps = trim($selectedSalesReps, ', ');
							}
							
							$agentName = '';
							
							$customerHistory = CustomerHistory::model()->find(array(
								'condition' => '
									content LIKE "%Registered on%"
									AND customer_id = :customer_id
								',
								'params' => array(
									':customer_id' => $customer->id,
								),
							));
							
							if( $customerHistory )
							{
								$agentName = $customerHistory->account->getFullName();
							}
							
							$enrollmentsTotalAmount += $contractedAmount;
							$totalNet += $contractedAmount;
							
							$dateTime = new DateTime($enrollmentModel->date_created, new DateTimeZone('America/Chicago'));
							$dateTime->setTimezone(new DateTimeZone('America/Denver'));	
							
							$enrollments[] = array(
								'date_entered' => $dateTime->format('m/d/Y g:i A'),
								'sales_agent' => $selectedSalesReps,
								'agent' => $agentName,
								'start_date' => date('m-d-Y', strtotime($enrollmentModel->start_month)),
								'end_date' => $enrollmentModel->end_month != '0000-00-00' && !empty($enrollmentModel->end_month) ? date('m-d-Y', strtotime($enrollmentModel->end_month)) : '',
								'company' => $customer->company->company_name,
								'customer_name' => $customer->getFullName(),
								'customer_id' => $customer->custom_customer_id,
								'skill' => $enrollmentModel->skill->skill_name,
								'contract' => $enrollmentModel->contract->contract_name,
								'quantity' => $totalLeads,
								'amount' => $contractedAmount,
							);
						}
					}
				}
			}
			
			
			//Changes
			$changesModels = CustomerHistory::model()->findAll(array(
				'condition' => '
					DATE(date_created) >= "'.date('Y-m-d', strtotime($dateFilterStart)).'" 
					AND DATE(date_created) <= "'.date('Y-m-d', strtotime($dateFilterEnd)).'"
					AND (
						content LIKE "%Contract Upgrade%"
						OR content LIKE "%Contract Downgrade%"
						OR ( 
							content LIKE "%Status Changed from On Hold to Active%"
							AND page_name = "Customer Skill"
						)
						OR ( 
							content LIKE "%Status Changed from Active to Hold%"
							AND page_name = "Customer Skill"
						)
						OR (
							page_name = "Credit"
							AND type = 1
						)
					)
					AND customer_id NOT IN(48)
				',
				'order' => 'date_created DESC'
			));
			
			if( $changesModels )
			{
				foreach( $changesModels as $changesModel )
				{
					$totalLeads = 0;
					$contractedAmount = 0;
					
					$customer = Customer::model()->find(array(
						'condition' => 'id = :customer_id',
						'params' => array(
							':customer_id' => $changesModel->customer_id,
						),
					));
					
					if( $customer && $customer->is_deleted == 0 && $customer->company->company_name != "Test Company" )
					{
						if( $changesModel->page_name == 'Credit' || strpos($changesModel->content, 'Status Changed from Hold to Active') !== false || strpos($changesModel->content, 'Status Changed from Active to Hold') !== false )
						{
							$customerSkill = CustomerSkill::model()->find(array(
								'with' => 'contract',
								'condition' => 't.customer_id = :customer_id AND t.status=1 AND contract.contract_name NOT IN("Audigy Surveys", "Audigy Group", "Tournament Reservations", "Training Company Contract", "Mountain View Network")',
								'params' => array(
									':customer_id' => $customer->id,
								),
								'order' => 't.date_created DESC',
							));
						}
						else
						{
							$customerSkill = CustomerSkill::model()->find(array(
								'with' => 'contract',
								'condition' => 't.id = :id AND t.customer_id = :customer_id AND t.status=1 AND contract.contract_name NOT IN("Audigy Surveys", "Audigy Group", "Tournament Reservations", "Training Company Contract", "Mountain View Network")',
								'params' => array(
									':id' => $changesModel->model_id,
									':customer_id' => $customer->id,
								),
								'order' => 't.date_created DESC',
							));
						}
						
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
								
								$dateTime = new DateTime($changesModel->date_created, new DateTimeZone('America/Chicago'));
								$dateTime->setTimezone(new DateTimeZone('America/Denver'));	
								
								$creditAmount = 0;
								$selectedSalesReps = '';
								$changeType = '';
								
								if ( strpos(strtolower($changesModel->content), 'upgrade') !== false ) 
								{
									if( $contractedAmount > 0 )
									{
										$changeType = 'Upgrade'; 
										
										$totalNet += $contractedAmount;
										$changesTotalAmount += $contractedAmount;
										
										$changes[] = array(
											'date_entered' => $dateTime->format('m/d/Y g:i A'),
											'sales_agent' => $selectedSalesReps,
											'agent' => isset($changesModel->account) ? $changesModel->account->getFullName() : '',
											'start_date' => date('m-d-Y', strtotime($customerSkill->start_month)),
											'end_date' => $customerSkill->end_month != '0000-00-00' && !empty($customerSkill->end_month) ? date('m-d-Y', strtotime($customerSkill->end_month)) : '',
											'company' => $customer->company->company_name,
											'customer_name' => $customer->getFullName(),
											'customer_id' => $customer->custom_customer_id,
											'skill' => $customerSkill->skill->skill_name,
											'contract' => $customerSkill->contract->contract_name,
											'quantity' => $totalLeads,
											'amount' => $contractedAmount,
											'credit_amount' => $creditAmount,
											'change_type' => $changeType
										);
									}
								}
								
								if ( strpos(strtolower($changesModel->content), 'downgrade') !== false ) 
								{
									if( $contractedAmount > 0 )
									{
										$changeType = 'Downgrade'; 
									
										$totalNet -= $contractedAmount;
										$changesTotalAmount += -$contractedAmount;
										
										$changes[] = array(
											'date_entered' => $dateTime->format('m/d/Y g:i A'),
											'sales_agent' => $selectedSalesReps,
											'agent' => isset($changesModel->account) ? $changesModel->account->getFullName() : '',
											'start_date' => date('m-d-Y', strtotime($customerSkill->start_month)),
											'end_date' => $customerSkill->end_month != '0000-00-00' && !empty($customerSkill->end_month) ? date('m-d-Y', strtotime($customerSkill->end_month)) : '',
											'company' => $customer->company->company_name,
											'customer_name' => $customer->getFullName(),
											'customer_id' => $customer->custom_customer_id,
											'skill' => $customerSkill->skill->skill_name,
											'contract' => $customerSkill->contract->contract_name,
											'quantity' => $totalLeads,
											'amount' => $contractedAmount,
											'credit_amount' => $creditAmount,
											'change_type' => $changeType
										);
									}
								}
								
								if ( strpos($changesModel->content, 'Status Changed from Active to Hold') !== false || strpos($changesModel->content, 'Status Changed from Hold to Active') !== false ) 
								{
									if( $contractedAmount > 0 )
									{
										if ( strpos($changesModel->content, 'Status Changed from Hold to Active') !== false ) 
										{
											$changeType = 'Active'; 
											
											$totalNet += $contractedAmount;
											$changesTotalAmount += $contractedAmount;
										}

										if ( strpos($changesModel->content, 'Status Changed from Active to Hold') !== false ) 
										{
											$changeType = 'On Hold'; 
											
											$totalNet -= $contractedAmount;
											$changesTotalAmount += -$contractedAmount;
										}
										
										$changes[] = array(
											'date_entered' => $dateTime->format('m/d/Y g:i A'),
											'sales_agent' => $selectedSalesReps,
											'agent' => isset($changesModel->account) ? $changesModel->account->getFullName() : '',
											'start_date' => date('m-d-Y', strtotime($customerSkill->start_month)),
											'end_date' => $customerSkill->end_month != '0000-00-00' && !empty($customerSkill->end_month) ? date('m-d-Y', strtotime($customerSkill->end_month)) : '',
											'company' => $customer->company->company_name,
											'customer_name' => $customer->getFullName(),
											'customer_id' => $customer->custom_customer_id,
											'skill' => $customerSkill->skill->skill_name,
											'contract' => $customerSkill->contract->contract_name,
											'quantity' => $totalLeads,
											'amount' => $contractedAmount,
											'credit_amount' => $creditAmount,
											'change_type' => $changeType
										);
									}
								}
								
								if( $changesModel->page_name == 'Credit' AND $changesModel->type == 1 )
								{
									$changeType = 'New Credit';
									
									$customerCredit = CustomerCredit::model()->findByPk($changesModel->model_id);
									
									if( $customerCredit )
									{
										if( $creditAmount > 0 )
										{
											$creditAmount = $customerCredit->amount;
										
											$totalNet -= $creditAmount;
											$changesTotalAmount += -$creditAmount;
											
											$changes[] = array(
												'date_entered' => $dateTime->format('m/d/Y g:i A'),
												'sales_agent' => $selectedSalesReps,
												'agent' => isset($changesModel->account) ? $changesModel->account->getFullName() : '',
												'start_date' => date('m-d-Y', strtotime($customerSkill->start_month)),
												'end_date' => $customerSkill->end_month != '0000-00-00' && !empty($customerSkill->end_month) ? date('m-d-Y', strtotime($customerSkill->end_month)) : '',
												'company' => $customer->company->company_name,
												'customer_name' => $customer->getFullName(),
												'customer_id' => $customer->custom_customer_id,
												'skill' => $customerSkill->skill->skill_name,
												'contract' => $customerSkill->contract->contract_name,
												'quantity' => $totalLeads,
												'amount' => $contractedAmount,
												'credit_amount' => $creditAmount,
												'change_type' => $changeType
											);
										}
									}
								}
							}
						}
					}
				}
			}
			
			
			$cancelModels = CustomerSkill::model()->findAll(array(
				'condition' => '
					DATE(end_month) >= DATE("2017-01-01") 
					AND skill_id NOT IN (11,12,19,21,22,24,27,30) 
					'.$this->excludeCustomerSql.'
				',
				'order' => 'date_created DESC'
			));
			
			if( $cancelModels )
			{
				foreach( $cancelModels as $cancelModel )
				{
					$totalLeads = 0;
					$contractedAmount = 0;
					
					$customer = Customer::model()->find(array(
						'condition' => 'id = :customer_id',
						'params' => array(
							':customer_id' => $cancelModel->customer_id,
						),
					));

					if( $customer )
					{
						$contract = $cancelModel->contract;

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
											$customerSkillLevelArray = $cancelModel->getCustomerSkillLevelArray();
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
											':customer_id' => $cancelModel->customer_id,
											':contract_id' => $cancelModel->contract_id,
											':skill_id' => $cancelModel->skill_id,
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
											$customerSkillLevelArray = $cancelModel->getCustomerSkillLevelArray();
											
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
											':customer_id' => $cancelModel->customer_id,
											':contract_id' => $cancelModel->contract_id,
											':skill_id' => $cancelModel->skill_id,
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

							$valid = false;
							$dateEntered = '';
							$agentName = '';
							
							$customerHistory = CustomerHistory::model()->find(array(
								'condition' => '
									content LIKE "%End Date Changed%"
									AND model_id = :model_id
								',
								'params' => array(
									':model_id' => $cancelModel->id,
								),
								'order' => 'date_created DESC'
							));
							
							if( $customerHistory )
							{
								$dateTime = new DateTime($customerHistory->date_created, new DateTimeZone('America/Chicago'));
								$dateTime->setTimezone(new DateTimeZone('America/Denver'));	
								$dateEntered = $dateTime->format('m/d/Y g:i A');
								
								$agentName = $customerHistory->account->getFullName();
								
								if( (date('Y-m-d', strtotime($customerHistory->date_created)) >= date('Y-m-d', strtotime($dateFilterStart))) && (date('Y-m-d', strtotime($customerHistory->date_created)) <= date('Y-m-d', strtotime($dateFilterEnd))) )
								{
									$valid = true;
								}
							}
							
							if( $valid )
							{
								$cancellationsTotalAmount += $contractedAmount;
								$totalNet -= $contractedAmount;
								
								$cancellations[] = array(
									'date_entered' => $dateEntered,
									'sales_agent' => '',
									'agent' => $agentName,
									'start_date' => date('m-d-Y', strtotime($cancelModel->start_month)),
									'end_date' => $cancelModel->end_month != '0000-00-00' && !empty($cancelModel->end_month) ? date('m-d-Y', strtotime($cancelModel->end_month)) : '',
									'company' => $customer->company->company_name,
									'customer_name' => $customer->getFullName(),
									'customer_id' => $customer->custom_customer_id,
									'skill' => $cancelModel->skill->skill_name,
									'contract' => $cancelModel->contract->contract_name,
									'quantity' => $totalLeads,
									'amount' => $contractedAmount,
								);
							}
						}
					}
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
		
		if( $page == 'timezones' )
		{
			$filename = 'Timezone_'. date('YmdHi');
			$ctr = 1;
			$headers = array(
				'A' => '',
				'B' => 'Eastern',
				'C' => 'Central',
				'D' => 'Mountain',
				'E' => 'Pacific',
				'F' => 'Alaska',
				'G' => 'Hawaii',
				'H' => ''
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
			
			$totalActiveCustomers = 0;
			$totalOnHoldCustomers = 0;
			$totalRemovedCustomers = 0;
			
			$totalActiveCallableLeads = 0;
			$totalonHoldCallableLeads = 0;
			$totalRemovedCallableLeads = 0;
			
			$totalActiveGoals = 0;
			$totalonHoldGoals = 0;
			$totalRemovedGoals = 0;
			
			$totalActiveGoalRemaining = 0;
			$totalonHoldGoalRemaining = 0;
			$totalRemovedGoalRemaining = 0;
			
			
			$resultArray = array();
			
			$timezones = array('EST', 'CST', 'MST', 'PST', 'AKST', 'HAST');
			
			foreach( $timezones as $timezone )
			{
				$resultArray[$timezone] = array(
					'customers' => array(
						'active'  => 0,
						'on_hold'  => 0,
						'removed'  => 0,
					),
					'callable_leads' => array(
						'active'  => 0,
						'on_hold'  => 0,
						'removed'  => 0,
					),
					'goals' => array(
						'active'  => 0,
						'on_hold'  => 0,
						'removed'  => 0,
					),
					'goal_remaining' => array(
						'active' => 0,
						'on_hold' => 0,
						'removed' => 0,
					),
				);
			}
			
			$customerQueues = CustomerQueueViewer::model()->findAll(array(
				'condition' => 'company IN ("State Farm", "Farmers", "Allstate", "American Family", "Independent Insurance")',
			));
			
			if( $customerQueues )
			{
				foreach( $customerQueues as $customerQueue )
				{
					$customerSkill = CustomerSkill::model()->find(array(
						'condition' => 'customer_id = :customer_id AND skill_id = :skill_id AND status=1',
						'params' => array(
							':customer_id' => $customerQueue->customer_id,
							':skill_id' => $customerQueue->skill_id,
						),
					));
					
					if( $customerSkill )
					{				
						$status = 'Inactive';
						
						if( isset($customerSkill->contract) && isset($customerSkill->customer) && $customerSkill->customer->status == 1 && $customerSkill->customer->is_deleted == 0 && time() >= strtotime($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00' )
						{
							$status = 'Active';
						}
						
						if( !$customerIsCallable )
						{
							if( $customerSkill->start_month == '0000-00-00' )
							{
								$status = 'Active';
							}
							
							if( $customerSkill->start_month != '0000-00-00' && strtotime($customerSkill->start_month) > time() )
							{
								$status = 'Active';
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
						
						if( $customerQueue )
						{
							if( !empty($customerQueue->removal_start_date) && !empty($customerQueue->removal_end_date) )
							{
								if( time() >= strtotime($customerQueue->removal_start_date) && time() <= strtotime($customerQueue->removal_end_date) )
								{
									$status = 'Removed';
								}
							}
						}
						
						$appointmentSetMTDSql = "
							SELECT count(distinct lch.lead_id) AS totalCount 
							FROM ud_lead_call_history lch 
							LEFT JOIN ud_lists ls ON ls.id = lch.list_id 
							LEFT JOIN ud_calendar_appointment ca ON ca.id = lch.calendar_appointment_id 
							WHERE ca.title IN ('INSERT APPOINTMENT', 'APPOINTMENT SET', 'CANCEL APPOINTMENT', 'RESCHEDULE APPOINTMENT') 
							AND lch.disposition = 'Appointment Set'
							AND lch.date_created >= '".date('Y-m-01 00:00:00')."' 
							AND ca.date_created <= '".date('Y-m-t 23:59:59')."'
							AND lch.customer_id = '".$customerQueue->customer_id."'
							AND ls.skill_id = '".$customerSkill->skill_id."' 
						";
					
						
						$command = Yii::app()->db->createCommand($appointmentSetMTDSql);
						$appointmentSetMTD = $command->queryRow();
						
						$noShowMTDSql = "
							SELECT count(distinct lch.lead_id) AS totalCount 
							FROM ud_lead_call_history lch 
							LEFT JOIN ud_lists ls ON ls.id = lch.list_id 
							LEFT JOIN ud_calendar_appointment ca ON ca.id = lch.calendar_appointment_id 
							WHERE ca.title IN ('NO SHOW RESCHEDULE')
							AND lch.disposition = 'Appointment Set'
							AND lch.date_created >= '".date('Y-m-01 00:00:00')."' 
							AND ca.date_created <= '".date('Y-m-t 23:59:59')."'
							AND lch.customer_id = '".$customerQueue->customer_id."'
							AND ls.skill_id = '".$customerSkill->skill_id."' 
						";
						
						
						$command = Yii::app()->db->createCommand($noShowMTDSql);
						$noShowMTD = $command->queryRow();
						
						$appointmentSetCount = $appointmentSetMTD['totalCount'] + $noShowMTD['totalCount'];
						
						if( $noShowMTD['totalCount'] > 3 )
						{
							$appointmentSetCount = $appointmentSetMTD['totalCount']-3;
						}
						else
						{
							$appointmentSetCount = $appointmentSetCount-$noShowMTD['totalCount'];
						}
						
						$remainingCallableCount = Lead::model()->count(array(
							'with' => array('list', 'list.skill'),
							'together' => true,
							'condition' => '
								list.customer_id = :customer_id AND list.status = 1 
								AND t.type=1 and t.status=1 AND t.number_of_dials < (skill.max_dials * 3) 
								AND (recertify_date != "0000-00-00" AND recertify_date IS NOT NULL 
								AND NOW() <= recertify_date)
								AND skill.id = :skill_id
							',
							'params' => array(
								':customer_id' => $customerQueue->customer_id,
								':skill_id' => $customerQueue->skill_id,
							),
						));
						
						$goalRemaining = $customerQueue->contracted_quantity;
						
						if( $status == 'Active' )
						{
							$resultArray[$customerQueue->customer->getTimeZone()]['customers']['active'] += 1;
							$resultArray[$customerQueue->customer->getTimeZone()]['callable_leads']['active'] += $remainingCallableCount;
							$resultArray[$customerQueue->customer->getTimeZone()]['goals']['active'] += $appointmentSetCount;
							
							if( $customerQueue->fulfillment_type == 'Goal' )
							{
								$goalRemaining = $goalRemaining - $appointmentSetCount;
								
								if( $goalRemaining < 0 )
								{
									$goalRemaining = 0;
								}
								
								$resultArray[$customerQueue->customer->getTimeZone()]['goal_remaining']['active'] += $goalRemaining;
							}
						}
						
						if( in_array($status, array("On Hold", "Decline Hold")) )
						{
							$resultArray[$customerQueue->customer->getTimeZone()]['customers']['on_hold'] += 1;
							$resultArray[$customerQueue->customer->getTimeZone()]['callable_leads']['on_hold'] += $remainingCallableCount;
							$resultArray[$customerQueue->customer->getTimeZone()]['goals']['on_hold'] += $appointmentSetCount;
							
							if( $customerQueue->fulfillment_type == 'Goal' )
							{
								$goalRemaining = $goalRemaining - $appointmentSetCount;
								
								if( $goalRemaining < 0 )
								{
									$goalRemaining = 0;
								}
								
								$resultArray[$customerQueue->customer->getTimeZone()]['goal_remaining']['on_hold'] += $goalRemaining;
							}	
						}	
						
						if( $status == "Removed" )
						{
							$resultArray[$customerQueue->customer->getTimeZone()]['customers']['removed'] += 1;
							$resultArray[$customerQueue->customer->getTimeZone()]['callable_leads']['removed'] += $remainingCallableCount;
							$resultArray[$customerQueue->customer->getTimeZone()]['goals']['removed'] += $appointmentSetCount;
							
							if( $customerQueue->fulfillment_type == 'Goal' )
							{
								$goalRemaining = $goalRemaining - $appointmentSetCount;
								
								if( $goalRemaining < 0 )
								{
									$goalRemaining = 0;
								}
								
								$resultArray[$customerQueue->customer->getTimeZone()]['goal_remaining']['removed'] += $goalRemaining;
							}
						}
					}
				}

				
				foreach( $resultArray as $timezoneValues)
				{
					$totalActiveCustomers += $timezoneValues['customers']['active'];
					$totalOnHoldCustomers += $timezoneValues['customers']['on_hold'];
					$totalRemovedCustomers += $timezoneValues['customers']['removed'];
					
					$totalActiveCallableLeads +=  $timezoneValues['callable_leads']['active'];
					$totalOnHoldCallableLeads +=  $timezoneValues['callable_leads']['on_hold'];
					$totalRemovedCallableLeads +=  $timezoneValues['callable_leads']['removed'];
					
					$totalActiveGoals +=  $timezoneValues['goals']['active'];
					$totalOnHoldGoals +=  $timezoneValues['goals']['on_hold'];
					$totalRemovedGoals +=  $timezoneValues['goals']['removed'];
					
					$totalActiveGoalRemaining +=  $timezoneValues['goal_remaining']['active'];
					$totalOnHoldGoalRemaining +=  $timezoneValues['goal_remaining']['on_hold'];
					$totalRemovedGoalRemaining +=  $timezoneValues['goal_remaining']['removed'];
				}
			}
		
			$ctr = 3;
			
			$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, 'Customers');
			$ctr++;
			
			$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, 'Active');
			$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $resultArray['EST']['customers']['active']);
			$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $resultArray['CST']['customers']['active']);
			$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $resultArray['MST']['customers']['active']);
			$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $resultArray['PST']['customers']['active']);
			$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $resultArray['AKST']['customers']['active']);
			$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $resultArray['HAST']['customers']['active']);
			$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $totalActiveCustomers);
			
			$ctr++;
			
			$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, 'Hold/Decline');
			$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $resultArray['EST']['customers']['on_hold']);
			$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $resultArray['CST']['customers']['on_hold']);
			$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $resultArray['MST']['customers']['on_hold']);
			$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $resultArray['PST']['customers']['on_hold']);
			$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $resultArray['AKST']['customers']['on_hold']);
			$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $resultArray['HAST']['customers']['on_hold']);
			$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $totalOnHoldCustomers);
			
			$ctr++;
			
			$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, 'Removed');
			$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $resultArray['EST']['customers']['removed']);
			$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $resultArray['CST']['customers']['removed']);
			$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $resultArray['MST']['customers']['removed']);
			$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $resultArray['PST']['customers']['removed']);
			$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $resultArray['AKST']['customers']['removed']);
			$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $resultArray['HAST']['customers']['removed']);
			$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $totalRemovedCustomers);	
			$ctr++;
			
			$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, '');
			$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, ($resultArray['EST']['customers']['active'] + $resultArray['EST']['customers']['on_hold'] + $resultArray['EST']['customers']['removed']) );
			$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, ($resultArray['CST']['customers']['active'] + $resultArray['CST']['customers']['on_hold'] + $resultArray['CST']['customers']['removed']) );
			$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, ($resultArray['MST']['customers']['active'] + $resultArray['MST']['customers']['on_hold'] + $resultArray['MST']['customers']['removed']) );
			$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, ($resultArray['PST']['customers']['active'] + $resultArray['PST']['customers']['on_hold'] + $resultArray['PST']['customers']['removed']) );
			$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, ($resultArray['AKST']['customers']['active'] + $resultArray['AKST']['customers']['on_hold'] + $resultArray['AKST']['customers']['removed']) );
			$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, ($resultArray['HAST']['customers']['active'] + $resultArray['HAST']['customers']['on_hold'] + $resultArray['HAST']['customers']['removed']) );
			$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, ($totalActiveCustomers + $totalOnHoldCustomers + $totalRemovedCustomers) );
			$ctr++;
			
			$ctr++; //breakline
			
			$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, 'Callable Leads');
			$ctr++;
			
			$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, 'Active');
			$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $resultArray['EST']['callable_leads']['active']);
			$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $resultArray['CST']['callable_leads']['active']);
			$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $resultArray['MST']['callable_leads']['active']);
			$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $resultArray['PST']['callable_leads']['active']);
			$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $resultArray['AKST']['callable_leads']['active']);
			$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $resultArray['HAST']['callable_leads']['active']);
			$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $totalActiveCallableLeads);
			$ctr++;
			
			$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, 'Hold/Decline');
			$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $resultArray['EST']['callable_leads']['on_hold']);
			$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $resultArray['CST']['callable_leads']['on_hold']);
			$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $resultArray['MST']['callable_leads']['on_hold']);
			$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $resultArray['PST']['callable_leads']['on_hold']);
			$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $resultArray['AKST']['callable_leads']['on_hold']);
			$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $resultArray['HAST']['callable_leads']['on_hold']);
			$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $totalOnHoldCallableLeads);
			$ctr++;
			
			$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, 'Removed');
			$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $resultArray['EST']['callable_leads']['removed']);
			$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $resultArray['CST']['callable_leads']['removed']);
			$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $resultArray['MST']['callable_leads']['removed']);
			$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $resultArray['PST']['callable_leads']['removed']);
			$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $resultArray['AKST']['callable_leads']['removed']);
			$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $resultArray['HAST']['callable_leads']['removed']);
			$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $totalRemovedCallableLeads);
			$ctr++;
			
			$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, '');
			$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, ($resultArray['EST']['callable_leads']['active'] + $resultArray['EST']['callable_leads']['on_hold'] + $resultArray['EST']['callable_leads']['removed']) );
			$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, ($resultArray['CST']['callable_leads']['active'] + $resultArray['CST']['callable_leads']['on_hold'] + $resultArray['CST']['callable_leads']['removed']) );
			$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, ($resultArray['MST']['callable_leads']['active'] + $resultArray['MST']['callable_leads']['on_hold'] + $resultArray['MST']['callable_leads']['removed']) );
			$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, ($resultArray['PST']['callable_leads']['active'] + $resultArray['PST']['callable_leads']['on_hold'] + $resultArray['PST']['callable_leads']['removed']) );
			$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, ($resultArray['AKST']['callable_leads']['active'] + $resultArray['AKST']['callable_leads']['on_hold'] + $resultArray['AKST']['callable_leads']['removed']) );
			$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, ($resultArray['HAST']['callable_leads']['active'] + $resultArray['HAST']['callable_leads']['on_hold'] + $resultArray['HAST']['callable_leads']['removed']) );
			$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, ($totalActiveCallableLeads + $totalOnHoldCallableLeads + $totalRemovedCallableLeads) );
			$ctr++;
			
			$ctr++; //breakline
			
			$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, 'Goal');
			$ctr++;
			
			$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, 'Active');
			$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $resultArray['EST']['goals']['active']);
			$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $resultArray['CST']['goals']['active']);
			$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $resultArray['MST']['goals']['active']);
			$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $resultArray['PST']['goals']['active']);
			$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $resultArray['AKST']['goals']['active']);
			$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $resultArray['HAST']['goals']['active']);
			$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $totalActiveGoals);
			$ctr++;
			
			$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, 'Hold/Decline');
			$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $resultArray['EST']['goals']['on_hold']);
			$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $resultArray['CST']['goals']['on_hold']);
			$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $resultArray['MST']['goals']['on_hold']);
			$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $resultArray['PST']['goals']['on_hold']);
			$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $resultArray['AKST']['goals']['on_hold']);
			$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $resultArray['HAST']['goals']['on_hold']);
			$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $totalOnHoldGoals);
			$ctr++;
			
			$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, 'Removed');
			$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $resultArray['EST']['goals']['removed']);
			$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $resultArray['CST']['goals']['removed']);
			$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $resultArray['MST']['goals']['removed']);
			$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $resultArray['PST']['goals']['removed']);
			$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $resultArray['AKST']['goals']['removed']);
			$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $resultArray['HAST']['goals']['removed']);
			$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $totalRemovedGoals);
			$ctr++;
			
			$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, '');
			$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, ($resultArray['EST']['goals']['active'] + $resultArray['EST']['goals']['on_hold'] + $resultArray['EST']['goals']['removed']) );
			$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, ($resultArray['CST']['goals']['active'] + $resultArray['CST']['goals']['on_hold'] + $resultArray['CST']['goals']['removed']) );
			$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, ($resultArray['MST']['goals']['active'] + $resultArray['MST']['goals']['on_hold'] + $resultArray['MST']['goals']['removed']) );
			$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, ($resultArray['PST']['goals']['active'] + $resultArray['PST']['goals']['on_hold'] + $resultArray['PST']['goals']['removed']) );
			$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, ($resultArray['AKST']['goals']['active'] + $resultArray['AKST']['goals']['on_hold'] + $resultArray['AKST']['goals']['removed']) );
			$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, ($resultArray['HAST']['goals']['active'] + $resultArray['HAST']['goals']['on_hold'] + $resultArray['HAST']['goals']['removed']) );
			$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, ($totalActiveGoals + $totalOnHoldGoals + $totalRemovedGoals) );
			$ctr++;
			
			$ctr++; //breakline
			
			$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, 'Goal Remaining');
			$ctr++;
			
			$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, 'Active');
			$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $resultArray['EST']['goal_remaining']['active']);
			$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $resultArray['CST']['goal_remaining']['active']);
			$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $resultArray['MST']['goal_remaining']['active']);
			$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $resultArray['PST']['goal_remaining']['active']);
			$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $resultArray['AKST']['goal_remaining']['active']);
			$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $resultArray['HAST']['goal_remaining']['active']);
			$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $totalActiveGoalRemaining);
			$ctr++;
			
			$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, 'Hold/Decline');
			$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $resultArray['EST']['goal_remaining']['on_hold']);
			$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $resultArray['CST']['goal_remaining']['on_hold']);
			$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $resultArray['MST']['goal_remaining']['on_hold']);
			$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $resultArray['PST']['goal_remaining']['on_hold']);
			$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $resultArray['AKST']['goal_remaining']['on_hold']);
			$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $resultArray['HAST']['goal_remaining']['on_hold']);
			$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $totalOnHoldGoalRemaining);
			$ctr++;
			
			$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, 'Removed');
			$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $resultArray['EST']['goal_remaining']['removed']);
			$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $resultArray['CST']['goal_remaining']['removed']);
			$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $resultArray['MST']['goal_remaining']['removed']);
			$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $resultArray['PST']['goal_remaining']['removed']);
			$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $resultArray['AKST']['goal_remaining']['removed']);
			$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $resultArray['HAST']['goal_remaining']['removed']);
			$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $totalRemovedGoalRemaining);
			$ctr++;
			
			$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, '');
			$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, ($resultArray['EST']['goal_remaining']['active'] + $resultArray['EST']['goal_remaining']['on_hold'] + $resultArray['EST']['goal_remaining']['removed']) );
			$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, ($resultArray['CST']['goal_remaining']['active'] + $resultArray['CST']['goal_remaining']['on_hold'] + $resultArray['CST']['goal_remaining']['removed']) );
			$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, ($resultArray['MST']['goal_remaining']['active'] + $resultArray['MST']['goal_remaining']['on_hold'] + $resultArray['MST']['goal_remaining']['removed']) );
			$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, ($resultArray['PST']['goal_remaining']['active'] + $resultArray['PST']['goal_remaining']['on_hold'] + $resultArray['PST']['goal_remaining']['removed']) );
			$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, ($resultArray['AKST']['goal_remaining']['active'] + $resultArray['AKST']['goal_remaining']['on_hold'] + $resultArray['AKST']['goal_remaining']['removed']) );
			$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, ($resultArray['HAST']['goal_remaining']['active'] + $resultArray['HAST']['goal_remaining']['on_hold'] + $resultArray['HAST']['goal_remaining']['removed']) );
			$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, ($totalActiveGoalRemaining + $totalOnHoldGoalRemaining + $totalRemovedGoalRemaining) );
			$ctr++;
			
			$ctr++; //breakline
			
		}
		
		if( $page == 'timeOff' )
		{
			$dateFilterStart = date('Y-m-d 00:00:00', strtotime($dateFilterStart));
			$dateFilterEnd = date('Y-m-d 23:59:59', strtotime($dateFilterEnd));
			
			if( !empty($_POST['dateFilterStartTime']) )
			{
				$dateFilterStart = date('Y-m-d', strtotime($dateFilterStart)).' '.date('H:i:s', strtotime($_POST['dateFilterStartTime']));
				
				// $dateFilterStartTime = date('H:i:s', strtotime('+1 hour', strtotime($_POST['dateFilterStartTime'])));
				
				// $dateFilterStart = date('Y-m-d', strtotime($dateFilterStart)).' '.$dateFilterStartTime;
			}
			
			if( !empty($_POST['dateFilterEndTime']) )
			{
				$dateFilterEndTime = date('H:i:s', strtotime('+1 hour', strtotime($_POST['dateFilterEndTime'])));
				
				$dateFilterEnd = date('Y-m-d', strtotime($dateFilterEnd)).' '.$dateFilterEndTime;
			}
		
			$models = AccountPtoRequest::model()->findAll(array(
				'condition' => '
					STR_TO_DATE(request_date, "%m/%d/%Y") >= "'.$dateFilterStart.'"
					AND STR_TO_DATE(request_date, "%m/%d/%Y") <= "'.$dateFilterEnd.'"
				',
				'order' => 'STR_TO_DATE(request_date, "%m/%d/%Y") ASC'
			));
			
			$filename = 'Time Off';
			
			$ctr = 1;
			
			$headers = array(
				'A' => 'Employee Name',
				'B' => 'Request Date/Time',
				'C' => 'Hours',
				'D' => 'Status',
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
			
			if( $models )
			{
				$ctr = 2;
				
				foreach( $models as $model )
				{
					$startDate = strtotime($model->request_date.' '.$model->start_time);
					$endDate = strtotime($model->request_date_end.' '.$model->end_time);

					$totalScheduledHours = 0;
					
					while( $startDate <= $endDate ) 
					{
						$schedules = AccountLoginSchedule::model()->findAll(array(
							'condition' => 'account_id = :account_id AND day_name = :day_name AND type=1',
							'params' => array(
								':account_id' => $model->account_id,
								':day_name' => date('l', $startDate),
							),
							'order' => 'date_created ASC',
						));
						
						
						
						if( $schedules )
						{
							foreach( $schedules as $schedule )
							{
								$startTime = date('g:i A', strtotime($schedule->start_time));
								$endTime = date('g:i A', strtotime($schedule->end_time));

								if( strtotime($model->request_date.' '.$schedule->start_time) >= strtotime($model->request_date.' '.$schedule->start_time) && strtotime($model->request_date.' '.$schedule->end_time) <= strtotime($model->request_date.' '.$schedule->end_time) )
								{
									$totalScheduledHours += round((strtotime($schedule->end_time) - strtotime($schedule->start_time))/3600, 1);
								}
							}
						}
						
						$startDate = strtotime('+1 day', $startDate);
					}
					
					if($model->status == 1)
					{
						$status = 'Approved';
					}
					elseif($model->status == 2)
					{
						$status = 'For Approval';
					}
					else
					{
						$status = 'Denied';
					}
					
					$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $model->account->getFullName());
					$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, date('m/d/Y g:i A', strtotime($model->request_date.' '.$model->start_time)).' - '.date('m/d/Y g:i A', strtotime($model->request_date_end.' '.$model->end_time)));
					$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $totalScheduledHours);
					$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $status);
				
					$ctr++;
				}
			}
		}
		
		if( $page == 'news' )
		{
			$models = NewsAccountSettings::model()->findAll(array(
				'condition' => 'is_marked_read=1',
			));
			
			$filename = 'News';
			
			$ctr = 1;
			
			$headers = array(
				'A' => 'User name',
				'B' => 'Article name',
				'C' => 'Date/Time',
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
			
			if( $models )
			{
				$ctr = 2;
				
				foreach( $models as $model )
				{
					$date = new DateTime($model->date_created, new DateTimeZone('America/Chicago'));
					$date->setTimezone(new DateTimeZone('America/Denver'));

					$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $model->account->getFullName());
					$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $model->news->title);
					$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $date->format('m/d/Y g:i A'));
				
					$ctr++;
				}
			}
		}
		
		if( $page == 'learningCenterUsage' )
		{
			$models = CompanyLearningCenterFileUsage::model()->findAll(array(
				'order' => 'date_created DESC',
			));
			
			$filename = 'Resource Center Report';
			
			$ctr = 1;
			
			$headers = array(
				'A' => 'Date/Time',
				'B' => 'User',
				'C' => 'Company',
				'D' => 'File Name',
				'E' => 'Category',
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
			
			if( $models )
			{
				$ctr = 2;
				
				foreach( $models as $model )
				{
					$date = new DateTime($model->date_created, new DateTimeZone('America/Chicago'));
					$date->setTimezone(new DateTimeZone('America/Denver'));

					$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $date->format('m/d/Y g:i A'));
					$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $model->customer->firstname.' '.$model->customer->lastname);
					$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $model->company->company_name);
					$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $model->learningCenterFile->title);
					$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $model->learningCenterFile->category->name);
				
					$ctr++;
				}
			}
		}
		
		if( $page == 'trainingLibraryUsage' )
		{
			$models = TrainingLibraryFileUsage::model()->findAll(array(
				'order' => 'date_created DESC',
			));
			
			$filename = 'Training Library Usage';
			
			$ctr = 1;
			
			$headers = array(
				'A' => 'Date/Time',
				'B' => 'User',
				'C' => 'Security Group',
				'D' => 'File Name',
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
			
			if( $models )
			{
				$ctr = 2;
				
				foreach( $models as $model )
				{
					$securityGroups = Account::listAccountType();
					
					$date = new DateTime($model->date_created, new DateTimeZone('America/Chicago'));
					$date->setTimezone(new DateTimeZone('America/Denver'));

					$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $date->format('m/d/Y g:i A'));
					$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $model->account->getFullName());
					$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $securityGroups[$model->security_group]);
					$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $model->trainingLibraryFile->title);
				
					$ctr++;
				}
			}
		}
		
		if( $page == 'noShow' )
		{
			$dateFilterStart = date('Y-m-d 00:00:00', strtotime($dateFilterStart));
			$dateFilterEnd = date('Y-m-d 23:59:59', strtotime($dateFilterEnd));
			

			$dateFilterStart = date('Y-m-d', strtotime($dateFilterStart)).' '.date('H:i:s', strtotime($dateFilterStart));

			$dateFilterEndTime = date('H:i:s', strtotime('+1 hour', strtotime($dateFilterEndTime)));
			
			$dateFilterEnd = date('Y-m-d', strtotime($dateFilterEnd)).' '.$dateFilterEndTime;
			
			$sql = "
				SELECT a.id as agent_id, CONCAT(au.`first_name`, ' ', au.`last_name`) AS agent_name,
				(
					SELECT COUNT(lch.id) 
					FROM ud_lead_call_history lch
					LEFT JOIN ud_lists uls ON uls.id = lch.list_id
					LEFT JOIN ud_calendar_appointment ca ON ca.id = lch.calendar_appointment_id 
					WHERE lch.agent_account_id = a.`id`
					AND lch.start_call_time >= '".$dateFilterStart."'  
					AND lch.start_call_time <= '".$dateFilterEnd."'  
					AND uls.skill_id IN (11,12,15,16,17,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34)
					AND lch.disposition='Appointment Set'
					AND lch.status != 4
					AND lch.is_skill_child=0
					AND ca.id IS NOT NULL
					AND ca.title IN ('INSERT APPOINTMENT', 'APPOINTMENT SET', 'CANCEL APPOINTMENT', 'RESCHEDULE APPOINTMENT', 'LOCATION CONFLICT', 'SCHEDULE CONFLICT')
				) AS appointment_set_count,
				(
					SELECT COUNT(lch.id) 
					FROM ud_lead_call_history lch
					LEFT JOIN ud_lists uls ON uls.id = lch.list_id
					LEFT JOIN ud_calendar_appointment ca ON ca.id = lch.calendar_appointment_id 
					WHERE lch.agent_account_id = a.`id`
					AND lch.start_call_time >= '".$dateFilterStart."'  
					AND lch.start_call_time <= '".$dateFilterEnd."'  
					AND uls.skill_id IN (11,12,15,16,17,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34)
					AND lch.disposition='Appointment Set'
					AND lch.status != 4
					AND lch.is_skill_child=0
					AND ca.id IS NOT NULL
					AND ca.title IN ('NO SHOW RESCHEDULE')
				) AS no_show_count
				FROM ud_account a
				LEFT JOIN ud_account_user au ON au.`account_id` = a.`id`
				WHERE a.`account_type_id` IN (1,2,13)
				AND a.`id` NOT IN (4, 5)
				ORDER BY au.last_name ASC
			";
			
			$connection = Yii::app()->db;
			$command = $connection->createCommand($sql);
			$models = $command->queryAll();
			
			$filename = 'No Show';
			
			$ctr = 1;

			$headers = array(
				'A' => 'Agent Name',
				'B' => 'No show count',
				'C' => 'Appt set count',
				'D' => 'No Show %',
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
			
			if( $models )
			{
				$ctr = 2;
				
				foreach( $models as $model )
				{
					if( $model['appointment_set_count'] > 0 )
					{
						$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $model['agent_name']);
						$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $model['no_show_count']);
						$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $model['appointment_set_count']);
						
						if( $model['no_show_count'] > 0 )
						{
							$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, round( ($model['no_show_count'] / $model['appointment_set_count']) * 100, 2).'%');
						}
						else
						{
							$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, '0%');
						}
						$ctr++;
					}
				}
			}
		}
		
		if( $page == 'dnc' )
		{
			$dateFilterStart = date('Y-m-d 00:00:00', strtotime($dateFilterStart));
			$dateFilterEnd = date('Y-m-d 23:59:59', strtotime($dateFilterEnd));
			
			if( !empty($_POST['dateFilterStartTime']) )
			{
				$dateFilterStart = date('Y-m-d', strtotime($dateFilterStart)).' '.date('H:i:s', strtotime($_POST['dateFilterStartTime']));
				
				// $dateFilterStartTime = date('H:i:s', strtotime('+1 hour', strtotime($_POST['dateFilterStartTime'])));
				
				// $dateFilterStart = date('Y-m-d', strtotime($dateFilterStart)).' '.$dateFilterStartTime;
			}
			
			if( !empty($_POST['dateFilterEndTime']) )
			{
				$dateFilterEndTime = date('H:i:s', strtotime('+1 hour', strtotime($_POST['dateFilterEndTime'])));
				
				$dateFilterEnd = date('Y-m-d', strtotime($dateFilterEnd)).' '.$dateFilterEndTime;
			}
			
			$models = LeadCallHistory::model()->findAll(array(
				'condition' => '
					disposition = "Do Not Call"
					AND company_id NOT IN ("17", "18", "23")
					AND status = 1
					AND DATE(date_created) >= "'.date('Y-m-d', strtotime($dateFilterStart)).'" 
					AND DATE(date_created) <= "'.date('Y-m-d', strtotime($dateFilterEnd)).'"
				',
				'order' => 'date_created DESC',
			));
			
			$filename = 'Do Not Call';
			
			$ctr = 1;

			$headers = array(
				'A' => 'Date/Time',
				'B' => 'Lead Name',
				'C' => 'Lead Phone',
				'D' => 'Customer',
				'E' => 'Agent',
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
			
			if( $models )
			{
				$ctr = 2;
				
				foreach( $models as $model )
				{
					$dateTime = new DateTime($model->date_created, new DateTimeZone('America/Chicago'));
					$dateTime->setTimezone(new DateTimeZone('America/Denver'));
					
					$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $dateTime->format('m/d/Y g:i A'));
					$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $model->lead->getFullName());
					$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $model->lead_phone_number);
					$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $model->customer->getFullName());
					$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $model->agentAccount->getFullName());

					$ctr++;
				}
			}
		}
		
		if( $page == 'dncMasterList' )
		{
			$addedCondition = '';
					
			if( $dateFilterStart != "" && $dateFilterEnd != "" )
			{
				$dateFilterStart = date('Y-m-d 00:00:00', strtotime($dateFilterStart));
				$dateFilterEnd = date('Y-m-d 23:59:59', strtotime($dateFilterEnd));
				
				if( !empty($_POST['dateFilterStartTime']) )
				{
					$dateFilterStart = date('Y-m-d', strtotime($dateFilterStart)).' '.date('H:i:s', strtotime($_POST['dateFilterStartTime']));
					
					// $dateFilterStartTime = date('H:i:s', strtotime('+1 hour', strtotime($_POST['dateFilterStartTime'])));
					
					// $dateFilterStart = date('Y-m-d', strtotime($dateFilterStart)).' '.$dateFilterStartTime;
				}
				
				if( !empty($_POST['dateFilterEndTime']) )
				{
					$dateFilterEndTime = date('H:i:s', strtotime('+1 hour', strtotime($_POST['dateFilterEndTime'])));
					
					$dateFilterEnd = date('Y-m-d', strtotime($dateFilterEnd)).' '.$dateFilterEndTime;
				}
				
				$addedCondition .= ' AND DATE(t.date_created) >= "'.date('Y-m-d', strtotime($dateFilterStart)).'" AND DATE(t.date_created) <= "'.date('Y-m-d', strtotime($dateFilterEnd)).'"';
			}
			
			if( !empty($searchQuery) )
			{
				$addedCondition .= ' AND(t.lead_phone_number LIKE "%'.$searchQuery.'%" OR lead.last_name LIKE "%'.$searchQuery.'%")';
			}
				
			$models = LeadCallHistory::model()->findAll(array(
				'with' => 'lead',
				'condition' => '
					t.disposition = "Do Not Call"
					AND t.lead_id IS NOT NULL
					AND t.company_id NOT IN ("17", "18", "23")
					AND t.status = 1' . $addedCondition,
				'order' => 't.date_created DESC',
			));
			
			$filename = 'Master DNC Listing';
			
			$ctr = 1;

			$headers = array(
				'A' => 'Date Added',
				'B' => 'Phone #',
				'C' => 'Lead Name',
				'D' => 'Skill',
				'E' => 'Internal/Company',
				'F' => 'Company',
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
			
			if( $models )
			{
				$ctr = 2;
				
				foreach( $models as $model )
				{
					$dateTime = new DateTime($model->date_created, new DateTimeZone('America/Chicago'));
					$dateTime->setTimezone(new DateTimeZone('America/Denver'));
					
					$subDispositionName = '';
								
					if( isset($model->skillDispositionDetail) )
					{
						$subDispositionName = $model->skillDispositionDetail->skill_disposition_detail_name;
					}
					
					$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $dateTime->format('m/d/Y g:i A'));
					$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $model->lead_phone_number);
					$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $model->lead->getFullName());
					$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $model->lead->list->skill->skill_name);
					$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $subDispositionName);
					$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $model->customer->company->company_name);

					$ctr++;
				}
			}
		}
		
		if( $page == 'customData' )
		{
			if( $dateFilterStart != "" && $dateFilterEnd != "" )
			{
				$dateFilterStart = date('Y-m-d 00:00:00', strtotime($dateFilterStart));
				$dateFilterEnd = date('Y-m-d 23:59:59', strtotime($dateFilterEnd));
				
				if( !empty($_POST['dateFilterStartTime']) )
				{
					$dateFilterStart = date('Y-m-d', strtotime($dateFilterStart)).' '.date('H:i:s', strtotime($_POST['dateFilterStartTime']));
					
					// $dateFilterStartTime = date('H:i:s', strtotime('+1 hour', strtotime($_POST['dateFilterStartTime'])));
					
					// $dateFilterStart = date('Y-m-d', strtotime($dateFilterStart)).' '.$dateFilterStartTime;
				}
				
				if( !empty($_POST['dateFilterEndTime']) )
				{
					$dateFilterEndTime = date('H:i:s', strtotime('+1 hour', strtotime($_POST['dateFilterEndTime'])));
					
					$dateFilterEnd = date('Y-m-d', strtotime($dateFilterEnd)).' '.$dateFilterEndTime;
				}
				
				$addedCondition .= ' AND DATE(t.date_created) >= "'.date('Y-m-d', strtotime($dateFilterStart)).'" AND DATE(t.date_created) <= "'.date('Y-m-d', strtotime($dateFilterEnd)).'"';
			}
			
			if( !empty($_REQUEST['skillId']) && $dateFilterStart != "" && $dateFilterEnd != "" )
			{	
				$models = LeadCallHistory::model()->findAll(array(
					'with' => array('list', 'list.skill'),
					'condition' => '
						skill.enable_list_custom_mapping = 1
						AND t.lead_id IS NOT NULL
						AND t.company_id NOT IN ("17", "18", "23")
						AND t.status = 1
						AND DATE(t.date_created) >= "2017-06-07"
						AND list.skill_id = "'. $_REQUEST['skillId'].'"'. $addedCondition,
					'order' => 't.date_created DESC',
				));
			}
			
			$filename = 'Custom Data Report';
			
			$ctr = 1;
			
			$headers = array(
				'A' => 'Date/Time',
				'B' => 'Disposition',
				'C' => 'Company',
				'D' => 'Customer',
				'E' => 'Skill',
				'F' => 'List',
				'G' => 'Lead First Name',
				'H' => 'Lead Last Name',
				'I' => 'Lead Phone Number',
				'J' => 'Custom Data',
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
			
			
			if( $models )
			{
				$ctr = 2;
				
				foreach( $models as $model )
				{
					$customDataStr = '';
					
					$listCustomDatas = ListCustomData::model()->findAll(array(
						'condition' => 'list_id = :list_id AND display_on_form=1 AND status=1',
						'params' => array(
							':list_id' => $model->list_id,
						),
						'order' => 'ordering ASC',
					));
	
					if( $listCustomDatas )
					{
						$lfcr = chr(10) . chr(13);
						
						foreach( $listCustomDatas as $listCustomData )
						{	
							$leadCustomData = LeadCustomData::model()->find(array(
								'condition' => 'lead_id = :lead_id AND field_name = :field_name',
								'params' => array(
									':lead_id' => $model->lead_id,
									':field_name' => $listCustomData->original_name
								),
							));
							
							if( $leadCustomData )
							{
								$customDataStr .= $listCustomData->custom_name.' - '.$leadCustomData->value . $lfcr;
							}
						}
					}
					else
					{
						$customDataStr .= 'No custom fields found.';
					}
					
					$dateTime = new DateTime($model->date_created, new DateTimeZone('America/Chicago'));
					$dateTime->setTimezone(new DateTimeZone('America/Denver'));
					
					$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $dateTime->format('m/d/Y g:i A'));
					$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $model->disposition);
					$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $model->customer->company->company_name);
					$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $model->customer->getFullName());
					$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $model->list->skill->skill_name);
					$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $model->list->name);
					$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $model->lead->first_name);
					$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $model->lead->last_name);
					$objPHPExcel->getActiveSheet()->SetCellValue('I'.$ctr, $model->lead_phone_number);
					$objPHPExcel->getActiveSheet()->SetCellValue('J'.$ctr, $customDataStr);

					$ctr++;
				}
			}
		}
		
		if( $page == 'customerCompanyDncWn' )
		{
			$addedCondition = '';
			
			if( $dateFilterStart != "" && $dateFilterEnd != "" )
			{
				$dateFilterStart = date('Y-m-d 00:00:00', strtotime($dateFilterStart));
				$dateFilterEnd = date('Y-m-d 23:59:59', strtotime($dateFilterEnd));
				
				if( !empty($_POST['dateFilterStartTime']) )
				{
					$dateFilterStart = date('Y-m-d', strtotime($dateFilterStart)).' '.date('H:i:s', strtotime($_POST['dateFilterStartTime']));
					
					// $dateFilterStartTime = date('H:i:s', strtotime('+1 hour', strtotime($_POST['dateFilterStartTime'])));
					
					// $dateFilterStart = date('Y-m-d', strtotime($dateFilterStart)).' '.$dateFilterStartTime;
				}
				
				if( !empty($_POST['dateFilterEndTime']) )
				{
					$dateFilterEndTime = date('H:i:s', strtotime('+1 hour', strtotime($_POST['dateFilterEndTime'])));
					
					$dateFilterEnd = date('Y-m-d', strtotime($dateFilterEnd)).' '.$dateFilterEndTime;
				}
				
				$addedCondition .= ' AND DATE(t.date_created) >= "'.date('Y-m-d', strtotime($dateFilterStart)).'" AND DATE(t.date_created) <= "'.date('Y-m-d', strtotime($dateFilterEnd)).'"';
			}
			
			if( !empty($_REQUEST['companyId']) )
			{
				$addedCondition .= ' AND t.company_id = ' . $_REQUEST['companyId'];
			}
			
			if( !empty($_POST['customerId']) )
			{
				$addedCondition .= ' AND t.customer_id = ' . $_REQUEST['customerId'];
			}
			
			
			if( $dateFilterStart != "" && $dateFilterEnd != "" )
			{	
				$models = LeadCallHistory::model()->findAll(array(
					'with' => array('list', 'list.skill'),
					'condition' => '
						skill.enable_list_custom_mapping = 1
						AND t.disposition IN ("DO NOT CALL", "Disconnected Number", "Wrong Number")
						AND t.lead_id IS NOT NULL
						AND t.company_id NOT IN ("17", "18", "23")
						AND t.status = 1
						AND DATE(t.date_created) >= "2017-06-07"
					' . $addedCondition,
					'order' => 't.date_created DESC',
				));
			}
			
			$filename = 'Company-Customer DNC WN';
			
			$ctr = 1;
			
			$headers = array(
				'A' => 'Date/Time',
				'B' => 'Disposition',
				'C' => 'Company',
				'D' => 'Customer',
				'E' => 'Skill',
				'F' => 'Lead First Name',
				'G' => 'Lead Last Name',
				'H' => 'Lead Phone Number',
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
			
			
			if( $models )
			{
				$ctr = 2;
				
				foreach( $models as $model )
				{
					$dateTime = new DateTime($model->date_created, new DateTimeZone('America/Chicago'));
					$dateTime->setTimezone(new DateTimeZone('America/Denver'));
					
					$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $dateTime->format('m/d/Y g:i A'));
					$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $model->disposition);
					$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $model->customer->company->company_name);
					$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $model->customer->getFullName());
					$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $model->list->skill->skill_name);
					$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $model->lead->first_name);
					$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $model->lead->last_name);
					$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $model->lead_phone_number);

					$ctr++;
				}
			}
		}
		
		if( $page == 'cellphoneScrub' )
		{
			$addedCondition = '';
			
			if( $dateFilterStart != "" && $dateFilterEnd != "" )
			{
				$dateFilterStart = date('Y-m-d 00:00:00', strtotime($dateFilterStart));
				$dateFilterEnd = date('Y-m-d 23:59:59', strtotime($dateFilterEnd));
				
				if( !empty($_POST['dateFilterStartTime']) )
				{
					$dateFilterStart = date('Y-m-d', strtotime($dateFilterStart)).' '.date('H:i:s', strtotime($_POST['dateFilterStartTime']));
					
					// $dateFilterStartTime = date('H:i:s', strtotime('+1 hour', strtotime($_POST['dateFilterStartTime'])));
					
					// $dateFilterStart = date('Y-m-d', strtotime($dateFilterStart)).' '.$dateFilterStartTime;
				}
				
				if( !empty($_POST['dateFilterEndTime']) )
				{
					$dateFilterEndTime = date('H:i:s', strtotime('+1 hour', strtotime($_POST['dateFilterEndTime'])));
					
					$dateFilterEnd = date('Y-m-d', strtotime($dateFilterEnd)).' '.$dateFilterEndTime;
				}
				
				$addedCondition .= ' AND DATE(t.date_created) >= "'.date('Y-m-d', strtotime($dateFilterStart)).'" AND DATE(t.date_created) <= "'.date('Y-m-d', strtotime($dateFilterEnd)).'"';
			}
			
			$models = StateInitialScrub::model()->findAll(array(
				'with' => 'lead',
				'condition' => 't.api_result="CELLULAR"' . $addedCondition,
				'order' => 'lead.date_updated DESC',
			));
			
			$filename = 'Cellphone Scrub Report';
			
			$ctr = 1;
			
			$headers = array(
				'A' => 'Date/Time',
				'B' => 'Customer Name',
				'C' => 'Company',
				'D' => 'State',
				'E' => 'Lead Name',
				'F' => 'Lead Phone',
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
			
			
			if( $models )
			{
				$ctr = 2;
				
				foreach( $models as $model )
				{
					$dateTime = new DateTime($model->lead->date_updated, new DateTimeZone('America/Chicago'));
					$dateTime->setTimezone(new DateTimeZone('America/Denver'));
					
					$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $dateTime->format('m/d/Y g:i A'));
					$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $model->lead->customer->getFullName());
					$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $model->lead->customer->company->company_name);
					$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $model->state);
					$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $model->lead->getFullName());
					$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $model->lead_phone_number);

					$ctr++;
				}
			}
		}
		
		
		if( $page == 'possibleNow' )
		{
			$addedCondition = ' 1';
			
			if( $dateFilterStart != "" && $dateFilterEnd != "" )
			{
				$dateFilterStart = date('Y-m-d 00:00:00', strtotime($dateFilterStart));
				$dateFilterEnd = date('Y-m-d 23:59:59', strtotime($dateFilterEnd));
				
				if( !empty($_POST['dateFilterStartTime']) )
				{
					$dateFilterStart = date('Y-m-d', strtotime($dateFilterStart)).' '.date('H:i:s', strtotime($_POST['dateFilterStartTime']));
					
					$dateFilterStartTime = date('H:i:s', strtotime('+1 hour', strtotime($_POST['dateFilterStartTime'])));
					
					$dateFilterStart = date('Y-m-d', strtotime($dateFilterStart)).' '.$dateFilterStartTime;
				}
				
				if( !empty($_POST['dateFilterEndTime']) )
				{
					$dateFilterEndTime = date('H:i:s', strtotime('+1 hour', strtotime($_POST['dateFilterEndTime'])));
					
					$dateFilterEnd = date('Y-m-d', strtotime($dateFilterEnd)).' '.$dateFilterEndTime;
				}
				
				$addedCondition .= ' AND DATE(t.date_created) >= "'.date('Y-m-d', strtotime($dateFilterStart)).'" AND DATE(t.date_created) <= "'.date('Y-m-d', strtotime($dateFilterEnd)).'"';
			}
			
			$models = PossibleNowLead::model()->findAll(array(
				'condition' => '1 AND' . $addedCondition,
				'order' => 'date_created DESC',
			));

			$filename = 'Possible Now Report';
			
			$ctr = 1;
			
			
			$headers = array(
				'A' => 'Date/Time',
				'B' => 'Customer Name',
				'C' => 'Company',
				'D' => 'Lead Name',
				'E' => 'Phone Type',
				'F' => 'Lead Phone',
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
			
			
			if( $models )
			{
				$ctr = 2;
				
				foreach( $models as $model )
				{
					$dateTime = new DateTime($model->date_created, new DateTimeZone('America/Chicago'));
					$dateTime = new DateTime($model->date_created);
					
					$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $dateTime->format('m/d/Y g:i A'));
					$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $model->customer->getFullName());
					$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $model->company->company_name);
					$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $model->lead->getFullName());
					$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $model->phone_number_type);
					$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $model->phone_number);

					$ctr++;
				}
			}
		}
		
		if( $page == 'documentType' )
		{
			$ctr = 1;
			
			$filename = 'Document Type Report';
			
			$documentTypeIds = array();
					
			$documentTypes = AccountUserDocumentType::model()->findAll(array(
				'condition' => 'status=1',
				'order' => 'date_created DESC',
			));

			$headers = array(
				'A' => 'Employee Last Name',
				'B' => 'Employee First Name',
				'C' => 'Status',
				'D' => 'Hire Date',
				'E' => 'Termination Date',
			);
			
			$letter = 'F';
			
			if( $documentTypes )
			{
				foreach( $documentTypes as $documentType )
				{
					$documentTypeIds[$documentType->id] = $documentType->id;
					
					$headers[$letter] = $documentType->name;
					
					$letter++;
				}
			}
			
			foreach($headers as $column => $val)
			{	
				$objPHPExcel->getActiveSheet()->getColumnDimension($column)->setWidth(15);
				
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
			
			
			$userDocsArray = array();
					
			$userDocs = AccountUserDocument::model()->findAll(array(
				'with' => 'docType',
				'condition' => 'docType.status=1',
				'order' => 'docType.date_created DESC',
			));
			
			if( $userDocs )
			{
				foreach( $userDocs as $userDoc )
				{
					$date = new DateTime($userDoc->date_created, new DateTimeZone('America/Chicago'));
					$date->setTimezone(new DateTimeZone('America/Denver'));

					$userDocsArray[$userDoc->account_user_id][$userDoc->type_id][$userDoc->id] = array(
						'account_user_id' => $userDoc->account_user_id,
						'fileupload_id' => $userDoc->fileupload_id,
						'name' => $userDoc->fileUpload->original_filename,
						'date' => $date->format('m/d/Y'),
					);
				}
			}

			$models = Account::model()->findAll(array(
				'together' => true,
				'with' => 'accountUser',
				'condition' => '
					t.is_deleted=0 
					AND accountUser.id IS NOT NULL 
					AND t.id NOT IN (4, 5, 294, 295, 296, 1635, 49, 2) 
					AND t.account_type_id NOT IN (15) 
					AND accountUser.full_time_status != "SYSTEM"
				',
				'order' => 'accountUser.last_name ASC'
			));
			
			if( $models )
			{
				$ctr = 2;
				
				foreach( $models as $model )
				{
					$status = $model->status == 1 ? 'Active' : 'Inactive';
							
					$hireDate = '';
					
					if( !empty($model->accountUser->date_hire) )
					{
						$hireDate = date('m/d/Y', strtotime($model->accountUser->date_hire));
					}
					
					$terminationDate = '';
					
					if( !empty($model->accountUser->date_termination) )
					{
						$terminationDate = date('m/d/Y', strtotime($model->accountUser->date_termination));
					}
					
					$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $model->accountUser->last_name);
					$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $model->accountUser->first_name);
					$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $status);
					$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $hireDate);
					$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $terminationDate);

					if( $documentTypeIds )
					{
						$letter = 'F';
						// $lfcr = chr(10);					
						
						foreach( $documentTypeIds as $documentTypeId )
						{
							$cellData = "";
								
							if( $userDocsArray[$model->accountUser->id][$documentTypeId] )
							{
								foreach( $userDocsArray[$model->accountUser->id][$documentTypeId] as $userDoc )
								{
									$cellData .= $userDoc['date'] . "\r";
								}
							}
							
							$objPHPExcel->getActiveSheet()->SetCellValue($letter.$ctr, $cellData);
							$objPHPExcel->getActiveSheet()->getStyle($letter.$ctr)->getAlignment()->setWrapText(true);
							
							$letter++;
						}
					}

					$ctr++;
				}
			}
		}
		
		if( $page == 'cancellation' )
		{
			$models = CustomerCancellation::model()->findAll(array(
						'condition' => 't.status=1',
						'order' => 't.date_created DESC',
					));
			
			$filename = 'Cancellation Report';
			
			$ctr = 1;

			$headers = array(
				'A' => 'Date/Time',
				'B' => 'Customer Name',
				'C' => 'Company',
				'D' => 'Skill',
				'E' => 'Reason',
				'F' => 'Other',
				'G' => 'Start Date',
				'H' => 'Months retained',
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
			
			if( $models )
			{
				$ctr = 2;
				
				foreach( $models as $model )
				{
					$dateTime = new DateTime($model->date_created, new DateTimeZone('America/Chicago'));
					$dateTime->setTimezone(new DateTimeZone('America/Denver'));

					$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $dateTime->format('m/d/Y g:i A'));
					$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $model->first_name.' '.$model->last_name);
					$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $model->customer->company->company_name);
					$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $model->skill->skill_name);
					$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $model->reason);
					$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $model->other_reason);
					$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, date('m/d/Y', strtotime($model->start_date)));
					$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, round(((time() - strtotime($model->start_date)) / (60 * 60 * 24)) / 30.4, 2));

					$ctr++;
				}
			}
		}
		
		if( $page == 'extraAppt' )
		{
			if( $dateFilterStart != "" && $dateFilterEnd != "" )
			{
				$addedCondition = '';
				
				$dateFilterStart = date('Y-m-d 00:00:00', strtotime($dateFilterStart));
				$dateFilterEnd = date('Y-m-d 23:59:59', strtotime($dateFilterEnd));
				
				if( !empty($_POST['dateFilterStartTime']) )
				{
					$dateFilterStart = date('Y-m-d', strtotime($dateFilterStart)).' '.date('H:i:s', strtotime($_POST['dateFilterStartTime']));
					
					// $dateFilterStartTime = date('H:i:s', strtotime('+1 hour', strtotime($_POST['dateFilterStartTime'])));
					
					// $dateFilterStart = date('Y-m-d', strtotime($dateFilterStart)).' '.$dateFilterStartTime;
				}
				
				if( !empty($_POST['dateFilterEndTime']) )
				{
					$dateFilterEndTime = date('H:i:s', strtotime('+1 hour', strtotime($_POST['dateFilterEndTime'])));
					
					$dateFilterEnd = date('Y-m-d', strtotime($dateFilterEnd)).' '.$dateFilterEndTime;
				}
				
				$addedCondition .= ' AND DATE(t.date_created) >= "'.date('Y-m-d', strtotime($dateFilterStart)).'" AND DATE(t.date_created) <= "'.date('Y-m-d', strtotime($dateFilterEnd)).'"';
				
				$models = CustomerExtra::model()->findAll(array(
					'condition' => 't.status=1' . $addedCondition,
					'order' => 't.date_created DESC',
				));
			}
			
			$filename = 'Extra Appt';
			
			$ctr = 1;

			$headers = array(
				'A' => 'Company',
				'B' => 'Customer Name',
				'C' => 'Start Date',
				'D' => 'End Date',
				'E' => 'Count of Extras',
				'F' => 'Reason',
				'G' => 'User',
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
			
			if( $models )
			{
				$ctr = 2;
				
				foreach( $models as $model )
				{
					$customerSkill = $model->customer->getCustomerSkill($model->skill_id);
					
					$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $model->customer->company->company_name);
					$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $model->customer->getFullName());
					$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $customerSkill['start_date']);
					$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $customerSkill['end_date']);
					$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $model->quantity);
					$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $model->description);
					$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $model->account->getFullName());

					$ctr++;
				}
			}
		}
		
		if( $page == 'masterSchedule' )
		{
			switch( $_REQUEST['scheduleFilterVal'] )
			{
				default:
				{
					$dateFilterStart = date('Y-m-d 00:00:00', strtotime('this week monday'));
					$dateFilterEnd = date('Y-m-d 23:59:59', strtotime('this week sunday')); 
				}
				break;
				
				case '1 Week Out':
				{						
					$dateFilterStart = date('Y-m-d 00:00:00', strtotime('next week monday'));
					$dateFilterEnd = date('Y-m-d 23:59:59', strtotime('sunday this week', strtotime($dateFilterStart))); 
				}
				break;
				
				case '2 Weeks Out':
				{
					$dateFilterStart = date('Y-m-d 00:00:00', strtotime('+1 week monday'));
					$dateFilterEnd = date('Y-m-d 23:59:59', strtotime('sunday this week', strtotime($dateFilterStart))); 
				}
				break;
				
				case '3 Weeks Out':
				{
					$dateFilterStart = date('Y-m-d 00:00:00', strtotime('+2 weeks monday'));
					$dateFilterEnd = date('Y-m-d 23:59:59', strtotime('sunday this week', strtotime($dateFilterStart))); 
				}
				break;
				
				case '4 Weeks Out':
				{
					$dateFilterStart = date('Y-m-d 00:00:00', strtotime('+3 weeks monday'));
					$dateFilterEnd = date('Y-m-d 23:59:59', strtotime('sunday this week', strtotime($dateFilterStart))); 
				}
				break;
			}
			
			if( !empty($_REQUEST['teamLeadFilter']) )
			{
				$position = Position::model()->find(array(
					'condition' => 'account_id = :account_id',
					'params' => array(
						':account_id' => $_REQUEST['teamLeadFilter'],
					),
				));
				
				if( $position )
				{
					$agentAccountIds = array();
					
					$agentAccountIds[] = $_REQUEST['teamLeadFilter'];
					
					$employeesUnder = Position::model()->findAll(array(
						'condition' => 'parent_id = :parent_id',
						'params' => array(
							':parent_id' => $position->id
						),
					));
					
					if( $employeesUnder )
					{
						foreach( $employeesUnder as $employee )
						{
							$agentAccountIds[] = $employee->account_id;
						}
					}
					
					if( $agentAccountIds )
					{
						$agents = Account::model()->findAll(array(
							'with' => 'accountUser',
							'condition' => '
								t.account_type_id IN (2,12,13,14)
								AND t.id NOT IN (4, 5)
								AND t.status = 1
								AND t.id IN ('.implode(', ', $agentAccountIds).')
							',
							'order' => 'accountUser.last_name ASC',
						));
					}
				}
			}
			else
			{
				$agents = Account::model()->findAll(array(
					'with' => 'accountUser',
					'condition' => '
						t.account_type_id IN (2,12,13,14)
						AND t.id NOT IN (4, 5)
						AND t.status = 1
					',
					'order' => 'accountUser.last_name ASC',
				));
			}
			
			$filename = 'Master Schedule';
			
			$ctr = 1;

			$lfcr = chr(10) . chr(13);
			
			$headers = array(
				'A' => 'Employee Name',
				'B' => 'Security Group',
				'C' => 'Team',
				'D' => 'Monday'.$lfcr.' Sch Clock In',
				'E' => 'Sch Clock Out',
				'F' => 'Sch Total',
				'G' => 'Act Total',
				'H' => 'Tuesday'.$lfcr.' Sch Clock In',
				'I' => 'Sch Clock Out',
				'J' => 'Sch Total',
				'K' => 'Act Total',
				'L' => 'Wednesday'.$lfcr.' Sch Clock In',
				'M' => 'Sch Clock Out',
				'N' => 'Sch Total',
				'O' => 'Act Total',
				'P' => 'Thursday'.$lfcr.' Sch Clock In',
				'Q' => 'Sch Clock Out',
				'R' => 'Sch Total',
				'S' => 'Act Total',
				'T' => 'Friday'.$lfcr.' Sch Clock In',
				'U' => 'Sch Clock Out',
				'V' => 'Sch Total',
				'W' => 'Act Total',
				'X' => 'Saturday'.$lfcr.' Sch Clock In',
				'Y' => 'Sch Clock Out',
				'Z' => 'Sch Total',
				'AA' => 'Act Total',
				'AB' => 'Sunday'.$lfcr.' Sch Clock In',
				'AC' => 'Sch Clock Out',
				'AD' => 'Sch Total',
				'AE' => 'Act Total',
				'AF' => 'Week Total'.$lfcr.' Sch Total ',
				'AG' => 'Act Total',
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
			
			if( $agents )
			{
				$ctr = 2;
				
				$securityGroups = Account::listAccountType();
				
				$mondaySchTotal = 0;
				$mondayActTotal = 0;
				
				$tuesdaySchTotal = 0;
				$tuesdayActTotal = 0;
				
				$wednesdaySchTotal = 0;
				$wednesdayActTotal = 0;
				
				$thursdaySchTotal = 0;
				$thursdayActTotal = 0;
				
				$fridaySchTotal = 0;
				$fridayActTotal = 0;
				
				$saturdaySchTotal = 0;
				$saturdayActTotal = 0;
				
				$sundaydaySchTotal = 0;
				$sunayActTotal = 0;
				
				$weekGrandTotal = 0;
				$actGrandTotal = 0;
				
				foreach( $agents as $agent )
				{
					$weekTotal = 0;
								
					$schedule = array();

					$agentSchedules = AccountLoginSchedule::model()->findAll(array(
						'condition' => 'account_id = :account_id',
						'params' => array(
							':account_id' => $agent->id
						),
					));
					
					if( $agentSchedules )
					{
						foreach( $agentSchedules as $agentScheduleId => $agentSchedule )
						{
							$schedule[$agentSchedule->day_name][] = array(
								'start_time' => $agentSchedule->start_time,
								'end_time' => $agentSchedule->end_time,
							);
						}
					}
					
					$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $agent->getFullName());
					$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $securityGroups[$agent->account_type_id]);
					$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, '');

					$letter = 'D';
					
					foreach( array('Monday', 'Tuesday', 'Wednesday' ,'Thursday' , 'Friday', 'Saturday', 'Sunday') as $dayName )
					{
						if( isset($schedule[$dayName]) )
						{
							$schDayTotal = 0;
							
							foreach( $schedule[$dayName] as $daySchedule )
							{
								$scheduleStart = date('Y-m-d', strtotime($dayName . ' this week', strtotime($dateFilterStart))).' '.$daySchedule['start_time'];
								
								$scheduleEnd = date('Y-m-d', strtotime($dayName . ' this week', strtotime($dateFilterStart))).' '.$daySchedule['end_time'];
								
								$schDayTotal += round((strtotime(date('Y-m-d').' '.$daySchedule['end_time']) - strtotime(date('Y-m-d').' '.$daySchedule['start_time']))/3600, 1);
								
								$schTotal = round((strtotime(date('Y-m-d').' '.$daySchedule['end_time']) - strtotime(date('Y-m-d').' '.$daySchedule['start_time']))/3600, 1);
							
								$weekTotal += $schTotal;
								
								$actTotal = $agent->getTotalLoginHours( $scheduleStart, $scheduleEnd, '', 'decimal' );
								
								if( $dayName == 'Monday' )
								{
									$mondaySchTotal += $schTotal;
									$mondayActTotal += $actTotal;
								}
								
								if( $dayName == 'Tuesday' )
								{
									$tuesdaySchTotal += $schTotal;
									$tuesdayActTotal += $actTotal;
								}
								
								if( $dayName == 'Wednesday' )
								{
									$wednesdaySchTotal += $schTotal;
									$wednesdayActTotal += $actTotal;
								}
								
								if( $dayName == 'Thursday' )
								{
									$thursdaySchTotal += $schTotal;
									$thursdayActTotal += $actTotal;
								}
								
								if( $dayName == 'Friday' )
								{
									$fridaySchTotal += $schTotal;
									$fridayActTotal += $actTotal;
								}
								
								if( $dayName == 'Saturday' )
								{
									$saturdaySchTotal += $schTotal;
									$saturdayActTotal += $actTotal;
								}
								
								if( $dayName == 'Sunday' )
								{
									$sundaySchTotal += $schTotal;
									$sundayActTotal += $actTotal;
								}
							}
							
							$objPHPExcel->getActiveSheet()->SetCellValue($letter.$ctr, $schedule[$dayName][0]['start_time']);
							$letter++;
							
							$objPHPExcel->getActiveSheet()->SetCellValue($letter.$ctr, $schedule[$dayName][0]['end_time']);
							$letter++;
							
							$objPHPExcel->getActiveSheet()->SetCellValue($letter.$ctr, $schDayTotal);
							$letter++;
							
							$objPHPExcel->getActiveSheet()->SetCellValue($letter.$ctr, $actTotal);
							$letter++;
						}
						else
						{
							$objPHPExcel->getActiveSheet()->SetCellValue($letter.$ctr, '');
							$letter++;
							
							$objPHPExcel->getActiveSheet()->SetCellValue($letter.$ctr, '');
							$letter++;
							
							$objPHPExcel->getActiveSheet()->SetCellValue($letter.$ctr, '');
							$letter++;
							
							$objPHPExcel->getActiveSheet()->SetCellValue($letter.$ctr, '');
							$letter++;
						}
					}
					
					$weekGrandTotal += $weekTotal;
					$actGrandTotal += $agent->getTotalLoginHours($dateFilterStart, $dateFilterEnd, '', 'decimal');
					
					$objPHPExcel->getActiveSheet()->SetCellValue('AF'.$ctr, $weekTotal);
					$objPHPExcel->getActiveSheet()->SetCellValue('AG'.$ctr, $agent->getTotalLoginHours($dateFilterStart, $dateFilterEnd, '', 'decimal'));
					
					$ctr++;
				}
				
				$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, 'TOTAL');
				
				$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $mondaySchTotal);
				$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $mondayActTotal);
				
				$objPHPExcel->getActiveSheet()->SetCellValue('J'.$ctr, $tuesdaySchTotal);
				$objPHPExcel->getActiveSheet()->SetCellValue('K'.$ctr, $tuesdayActTotal);
				
				$objPHPExcel->getActiveSheet()->SetCellValue('N'.$ctr, $wednesdaySchTotal);
				$objPHPExcel->getActiveSheet()->SetCellValue('O'.$ctr, $wednesdayActTotal);
				
				$objPHPExcel->getActiveSheet()->SetCellValue('R'.$ctr, $thursdaySchTotal);
				$objPHPExcel->getActiveSheet()->SetCellValue('S'.$ctr, $thursdayActTotal);
				
				$objPHPExcel->getActiveSheet()->SetCellValue('V'.$ctr, $fridaySchTotal);
				$objPHPExcel->getActiveSheet()->SetCellValue('W'.$ctr, $fridayActTotal);
				
				$objPHPExcel->getActiveSheet()->SetCellValue('Z'.$ctr, $saturdaySchTotal);
				$objPHPExcel->getActiveSheet()->SetCellValue('AA'.$ctr, $saturdayActTotal);
				
				$objPHPExcel->getActiveSheet()->SetCellValue('AD'.$ctr, $sundaySchTotal);
				$objPHPExcel->getActiveSheet()->SetCellValue('AE'.$ctr, $sundayActTotal);
				
				$objPHPExcel->getActiveSheet()->SetCellValue('AF'.$ctr, $weekGrandTotal);
				$objPHPExcel->getActiveSheet()->SetCellValue('AG'.$ctr, $actGrandTotal);
			}
		}
		
		if( $page == 'impactReport' )
		{
			$filename = 'Impact Report';
			
			$month1 = ImpactReport::model()->findByPk(1);
			$month2 = ImpactReport::model()->findByPk(2);
			$month3 = ImpactReport::model()->findByPk(3);
			$month4 = ImpactReport::model()->findByPk(4);
			$month5 = ImpactReport::model()->findByPk(5);
			$month6 = ImpactReport::model()->findByPk(6);
			$month7 = ImpactReport::model()->findByPk(7);
			$month8 = ImpactReport::model()->findByPk(8);
			
			if( $month1 && $month2 && $month3 && $month4 && $month5 && $month6 && $month7 && $month8 )
			{
				//column width
				$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
				$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
				$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
				$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
				
				//Month headers
				$objPHPExcel->getActiveSheet()->SetCellValue('A1', '');
				
				$objPHPExcel->getActiveSheet()->SetCellValue('B1', $month1->month_name);
				$objPHPExcel->getActiveSheet()->getStyle('B1')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('C1', $month2->month_name);
				$objPHPExcel->getActiveSheet()->getStyle('C1')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('D1', $month3->month_name);
				$objPHPExcel->getActiveSheet()->getStyle('D1')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('E1', $month4->month_name);
				$objPHPExcel->getActiveSheet()->getStyle('E1')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('F1', $month5->month_name);
				$objPHPExcel->getActiveSheet()->getStyle('F1')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('G1', $month6->month_name);
				$objPHPExcel->getActiveSheet()->getStyle('G1')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('H1', $month7->month_name);
				$objPHPExcel->getActiveSheet()->getStyle('H1')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('I1', $month8->month_name);
				$objPHPExcel->getActiveSheet()->getStyle('I1')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					),
				));
				
				//Remaining
				$objPHPExcel->getActiveSheet()->SetCellValue('A2', 'Remaining');
				$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
					// 'font'  => array(
						// 'bold' => true,
						// 'name'  => 'Calibri',
					// ),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('B2', '$'.number_format($month1->projected, 2));
				$objPHPExcel->getActiveSheet()->getStyle('B2')->applyFromArray(array(
					'font'  => array(
						'bold' => true,
						'name'  => 'Calibri',
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('C2', '$'.number_format($month2->projected, 2));
				$objPHPExcel->getActiveSheet()->getStyle('C2')->applyFromArray(array(
					'font'  => array(
						'bold' => true,
						'name'  => 'Calibri',
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('D2', '$'.number_format($month3->projected, 2));
				$objPHPExcel->getActiveSheet()->getStyle('D2')->applyFromArray(array(
					'font'  => array(
						'bold' => true,
						'name'  => 'Calibri',
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('E2', '$'.number_format($month4->projected, 2));
				$objPHPExcel->getActiveSheet()->getStyle('E2')->applyFromArray(array(
					'font'  => array(
						'bold' => true,
						'name'  => 'Calibri',
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('F2', '$'.number_format($month5->projected, 2));
				$objPHPExcel->getActiveSheet()->getStyle('F2')->applyFromArray(array(
					'font'  => array(
						'bold' => true,
						'name'  => 'Calibri',
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('G2', '$'.number_format($month6->projected, 2));
				$objPHPExcel->getActiveSheet()->getStyle('G2')->applyFromArray(array(
					'font'  => array(
						'bold' => true,
						'name'  => 'Calibri',
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('H2', '$'.number_format($month7->projected, 2));
				$objPHPExcel->getActiveSheet()->getStyle('H2')->applyFromArray(array(
					'font'  => array(
						'bold' => true,
						'name'  => 'Calibri',
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('I2', '$'.number_format($month8->projected, 2));
				$objPHPExcel->getActiveSheet()->getStyle('I2')->applyFromArray(array(
					'font'  => array(
						'bold' => true,
						'name'  => 'Calibri',
					),
				));
				
				//Remaining - Subsidy
				$objPHPExcel->getActiveSheet()->SetCellValue('A3', 'Subsidy');
				$objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('B3', '$'.number_format($month1->projected_subsidy, 2));
				$objPHPExcel->getActiveSheet()->SetCellValue('C3', '$'.number_format($month2->projected_subsidy, 2));
				$objPHPExcel->getActiveSheet()->SetCellValue('D3', '$'.number_format($month3->projected_subsidy, 2));
				$objPHPExcel->getActiveSheet()->SetCellValue('E3', '$'.number_format($month4->projected_subsidy, 2));
				$objPHPExcel->getActiveSheet()->SetCellValue('F3', '$'.number_format($month5->projected_subsidy, 2));
				$objPHPExcel->getActiveSheet()->SetCellValue('G3', '$'.number_format($month6->projected_subsidy, 2));
				$objPHPExcel->getActiveSheet()->SetCellValue('H3', '$'.number_format($month7->projected_subsidy, 2));
				$objPHPExcel->getActiveSheet()->SetCellValue('I3', '$'.number_format($month8->projected_subsidy, 2));
				
				//Remaining - Credit card
				$objPHPExcel->getActiveSheet()->SetCellValue('A4', 'Credit Card');
				$objPHPExcel->getActiveSheet()->getStyle('A4')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('B4', '$'.number_format($month1->projected_credit_card, 2));
				$objPHPExcel->getActiveSheet()->SetCellValue('C4', '$'.number_format($month2->projected_credit_card, 2));
				$objPHPExcel->getActiveSheet()->SetCellValue('D4', '$'.number_format($month3->projected_credit_card, 2));
				$objPHPExcel->getActiveSheet()->SetCellValue('E4', '$'.number_format($month4->projected_credit_card, 2));
				$objPHPExcel->getActiveSheet()->SetCellValue('F4', '$'.number_format($month5->projected_credit_card, 2));
				$objPHPExcel->getActiveSheet()->SetCellValue('G4', '$'.number_format($month6->projected_credit_card, 2));
				$objPHPExcel->getActiveSheet()->SetCellValue('H4', '$'.number_format($month7->projected_credit_card, 2));
				$objPHPExcel->getActiveSheet()->SetCellValue('I4', '$'.number_format($month8->projected_credit_card, 2));
				
				//Collected
				$objPHPExcel->getActiveSheet()->SetCellValue('A6', 'Collected*');
				$objPHPExcel->getActiveSheet()->getStyle('A6')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('B6', '$'.number_format($month1->actual, 2));
				$objPHPExcel->getActiveSheet()->getStyle('B6')->applyFromArray(array(
					'font'  => array(
						'bold' => true,
						'name'  => 'Calibri',
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('C6', '$'.number_format($month2->actual, 2));
				$objPHPExcel->getActiveSheet()->getStyle('C6')->applyFromArray(array(
					'font'  => array(
						'bold' => true,
						'name'  => 'Calibri',
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('D6', '$'.number_format($month3->actual, 2));
				$objPHPExcel->getActiveSheet()->getStyle('D6')->applyFromArray(array(
					'font'  => array(
						'bold' => true,
						'name'  => 'Calibri',
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('E6', '$'.number_format($month4->actual, 2));
				$objPHPExcel->getActiveSheet()->getStyle('E6')->applyFromArray(array(
					'font'  => array(
						'bold' => true,
						'name'  => 'Calibri',
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('F6', '$'.number_format($month5->actual, 2));
				$objPHPExcel->getActiveSheet()->getStyle('F6')->applyFromArray(array(
					'font'  => array(
						'bold' => true,
						'name'  => 'Calibri',
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('G6', '$'.number_format($month6->actual, 2));
				$objPHPExcel->getActiveSheet()->getStyle('G6')->applyFromArray(array(
					'font'  => array(
						'bold' => true,
						'name'  => 'Calibri',
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('H6', '$'.number_format($month7->actual, 2));
				$objPHPExcel->getActiveSheet()->getStyle('H6')->applyFromArray(array(
					'font'  => array(
						'bold' => true,
						'name'  => 'Calibri',
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('I6', '$'.number_format($month8->actual, 2));
				$objPHPExcel->getActiveSheet()->getStyle('I6')->applyFromArray(array(
					'font'  => array(
						'bold' => true,
						'name'  => 'Calibri',
					),
				));
				
				//Collected - Subsidy
				$objPHPExcel->getActiveSheet()->SetCellValue('A6', 'Subsidy');
				$objPHPExcel->getActiveSheet()->getStyle('A6')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('B7', '$'.number_format($month1->actual_subsidy, 2));
				$objPHPExcel->getActiveSheet()->SetCellValue('C7', '$'.number_format($month2->actual_subsidy, 2));
				$objPHPExcel->getActiveSheet()->SetCellValue('D7', '$0.00');
				$objPHPExcel->getActiveSheet()->SetCellValue('E7', '$0.00');
				$objPHPExcel->getActiveSheet()->SetCellValue('F7', '$0.00');
				$objPHPExcel->getActiveSheet()->SetCellValue('G7', '$0.00');
				$objPHPExcel->getActiveSheet()->SetCellValue('H7', '$0.00');
				$objPHPExcel->getActiveSheet()->SetCellValue('I7', '$0.00');
				
				//Collected - Credit card
				$objPHPExcel->getActiveSheet()->SetCellValue('A8', 'Credit card');
				$objPHPExcel->getActiveSheet()->getStyle('A8')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('B8', '$'.number_format($month1->actual_credit_card, 2));
				$objPHPExcel->getActiveSheet()->SetCellValue('C8', '$'.number_format($month2->actual_credit_card, 2));
				$objPHPExcel->getActiveSheet()->SetCellValue('D8', '$0.00');
				$objPHPExcel->getActiveSheet()->SetCellValue('E8', '$0.00');
				$objPHPExcel->getActiveSheet()->SetCellValue('F8', '$0.00');
				$objPHPExcel->getActiveSheet()->SetCellValue('G8', '$0.00');
				$objPHPExcel->getActiveSheet()->SetCellValue('H8', '$0.00');
				$objPHPExcel->getActiveSheet()->SetCellValue('I8', '$0.00');
				
				//Projected
				$objPHPExcel->getActiveSheet()->SetCellValue('A10', 'Projected');
				$objPHPExcel->getActiveSheet()->getStyle('A10')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
					'font'  => array(
						'bold' => true,
						'name'  => 'Calibri',
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('B10', '$'.number_format($month1->projected + $month1->actual, 2));
				$objPHPExcel->getActiveSheet()->getStyle('B10')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
					'font'  => array(
						'bold' => true,
						'name'  => 'Calibri',
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('C10', '$'.number_format($month2->projected + $month2->actual, 2));
				$objPHPExcel->getActiveSheet()->getStyle('C10')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
					'font'  => array(
						'bold' => true,
						'name'  => 'Calibri',
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('D10', '$'.number_format($month3->projected + $month3->actual, 2));
				$objPHPExcel->getActiveSheet()->getStyle('D10')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
					'font'  => array(
						'bold' => true,
						'name'  => 'Calibri',
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('E10', '$'.number_format($month4->projected + $month4->actual, 2));
				$objPHPExcel->getActiveSheet()->getStyle('E10')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
					'font'  => array(
						'bold' => true,
						'name'  => 'Calibri',
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('F10', '$'.number_format($month5->projected + $month5->actual, 2));
				$objPHPExcel->getActiveSheet()->getStyle('F10')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
					'font'  => array(
						'bold' => true,
						'name'  => 'Calibri',
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('G10', '$'.number_format($month6->projected + $month6->actual, 2));
				$objPHPExcel->getActiveSheet()->getStyle('G10')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
					'font'  => array(
						'bold' => true,
						'name'  => 'Calibri',
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('H10', '$'.number_format($month7->projected + $month7->actual, 2));
				$objPHPExcel->getActiveSheet()->getStyle('H10')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
					'font'  => array(
						'bold' => true,
						'name'  => 'Calibri',
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('I10', '$'.number_format($month8->projected + $month8->actual, 2));
				$objPHPExcel->getActiveSheet()->getStyle('I10')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
					'font'  => array(
						'bold' => true,
						'name'  => 'Calibri',
					),
				));
				
				//Customer Count
				$objPHPExcel->getActiveSheet()->SetCellValue('A11', 'Customer Count');
				$objPHPExcel->getActiveSheet()->getStyle('A11')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
					'font'  => array(
						'bold' => true,
						'name'  => 'Calibri',
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('B11', $month1->projected_customer_count);
				$objPHPExcel->getActiveSheet()->getStyle('B11')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
					'font'  => array(
						'bold' => true,
						'name'  => 'Calibri',
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('C11', $month2->projected_customer_count);
				$objPHPExcel->getActiveSheet()->getStyle('C11')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
					'font'  => array(
						'bold' => true,
						'name'  => 'Calibri',
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('D11', $month3->projected_customer_count);
				$objPHPExcel->getActiveSheet()->getStyle('D11')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
					'font'  => array(
						'bold' => true,
						'name'  => 'Calibri',
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('E11', $month4->projected_customer_count);
				$objPHPExcel->getActiveSheet()->getStyle('E11')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
					'font'  => array(
						'bold' => true,
						'name'  => 'Calibri',
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('F11', $month5->projected_customer_count);
				$objPHPExcel->getActiveSheet()->getStyle('F11')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
					'font'  => array(
						'bold' => true,
						'name'  => 'Calibri',
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('G11', $month6->projected_customer_count);
				$objPHPExcel->getActiveSheet()->getStyle('G11')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
					'font'  => array(
						'bold' => true,
						'name'  => 'Calibri',
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('H11', $month7->projected_customer_count);
				$objPHPExcel->getActiveSheet()->getStyle('H11')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
					'font'  => array(
						'bold' => true,
						'name'  => 'Calibri',
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('I11', $month8->projected_customer_count);
				$objPHPExcel->getActiveSheet()->getStyle('I11')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
					'font'  => array(
						'bold' => true,
						'name'  => 'Calibri',
					),
				));
				
				//Sales Starting ($)
				$objPHPExcel->getActiveSheet()->SetCellValue('A14', 'Sales Starting ($)');
				$objPHPExcel->getActiveSheet()->getStyle('A14')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('B14', number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month1->month_name))->sales_starting_amount, 2));
				$objPHPExcel->getActiveSheet()->getStyle('B14')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('C14', number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month2->month_name))->sales_starting_amount, 2));
				$objPHPExcel->getActiveSheet()->getStyle('C14')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('D14', number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month3->month_name))->sales_starting_amount, 2));
				$objPHPExcel->getActiveSheet()->getStyle('D14')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('E14', number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month4->month_name))->sales_starting_amount, 2));
				$objPHPExcel->getActiveSheet()->getStyle('E14')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('F14', number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month5->month_name))->sales_starting_amount, 2));
				$objPHPExcel->getActiveSheet()->getStyle('F14')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('G14', number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month6->month_name))->sales_starting_amount, 2));
				$objPHPExcel->getActiveSheet()->getStyle('G14')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('H14', number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month7->month_name))->sales_starting_amount, 2));
				$objPHPExcel->getActiveSheet()->getStyle('H14')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('I14', number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month8->month_name))->sales_starting_amount, 2));
				$objPHPExcel->getActiveSheet()->getStyle('I14')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				//Sales Starting (count)
				$objPHPExcel->getActiveSheet()->SetCellValue('A15', 'Sales Starting (count)');
				$objPHPExcel->getActiveSheet()->getStyle('A15')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('B15', number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month1->month_name))->sales_starting_count, 2));
				$objPHPExcel->getActiveSheet()->getStyle('B15')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('C15', number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month2->month_name))->sales_starting_count, 2));
				$objPHPExcel->getActiveSheet()->getStyle('C15')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('D15', number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month3->month_name))->sales_starting_count, 2));
				$objPHPExcel->getActiveSheet()->getStyle('D15')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('E15', number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month4->month_name))->sales_starting_count, 2));
				$objPHPExcel->getActiveSheet()->getStyle('E15')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('F15', number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month5->month_name))->sales_starting_count, 2));
				$objPHPExcel->getActiveSheet()->getStyle('F15')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('G15', number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month6->month_name))->sales_starting_count, 2));
				$objPHPExcel->getActiveSheet()->getStyle('G15')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('H15', number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month7->month_name))->sales_starting_count, 2));
				$objPHPExcel->getActiveSheet()->getStyle('H15')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('I15', number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month8->month_name))->sales_starting_count, 2));
				$objPHPExcel->getActiveSheet()->getStyle('I15')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				//Cancels Affecting ($)
				$objPHPExcel->getActiveSheet()->SetCellValue('A16', 'Cancels Affecting ($)');
				$objPHPExcel->getActiveSheet()->getStyle('A16')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('B16', number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month1->month_name))->cancels_affecting_amount, 2));
				$objPHPExcel->getActiveSheet()->getStyle('B16')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('C16', number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month2->month_name))->cancels_affecting_amount, 2));
				$objPHPExcel->getActiveSheet()->getStyle('C16')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('D16', number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month3->month_name))->cancels_affecting_amount, 2));
				$objPHPExcel->getActiveSheet()->getStyle('D16')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('E16', number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month4->month_name))->cancels_affecting_amount, 2));
				$objPHPExcel->getActiveSheet()->getStyle('E16')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('F16', number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month5->month_name))->cancels_affecting_amount, 2));
				$objPHPExcel->getActiveSheet()->getStyle('F16')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('G16', number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month6->month_name))->cancels_affecting_amount, 2));
				$objPHPExcel->getActiveSheet()->getStyle('G16')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('H16', number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month7->month_name))->cancels_affecting_amount, 2));
				$objPHPExcel->getActiveSheet()->getStyle('H16')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('I16', number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month8->month_name))->cancels_affecting_amount, 2));
				$objPHPExcel->getActiveSheet()->getStyle('I16')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				//Cancels Affecting (count)
				$objPHPExcel->getActiveSheet()->SetCellValue('A17', 'Cancels Affecting (count)');
				$objPHPExcel->getActiveSheet()->getStyle('A17')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('B17', number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month1->month_name))->cancels_affecting_count, 2));
				$objPHPExcel->getActiveSheet()->getStyle('B17')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('C17', number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month2->month_name))->cancels_affecting_count, 2));
				$objPHPExcel->getActiveSheet()->getStyle('C17')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('D17', number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month3->month_name))->cancels_affecting_count, 2));
				$objPHPExcel->getActiveSheet()->getStyle('D17')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('E17', number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month4->month_name))->cancels_affecting_count, 2));
				$objPHPExcel->getActiveSheet()->getStyle('E17')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('F17', number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month5->month_name))->cancels_affecting_count, 2));
				$objPHPExcel->getActiveSheet()->getStyle('F17')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('G17', number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month6->month_name))->cancels_affecting_count, 2));
				$objPHPExcel->getActiveSheet()->getStyle('G17')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('H17', number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month7->month_name))->cancels_affecting_count, 2));
				$objPHPExcel->getActiveSheet()->getStyle('H17')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('I17', number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month8->month_name))->cancels_affecting_count, 2));
				$objPHPExcel->getActiveSheet()->getStyle('I17')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				//Net Change ($)
				$objPHPExcel->getActiveSheet()->SetCellValue('A18', 'Net Change ($)');
				$objPHPExcel->getActiveSheet()->getStyle('A18')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('B18', number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month1->month_name))->sales_starting_amount - ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month1->month_name))->cancels_affecting_amount, 2));
				$objPHPExcel->getActiveSheet()->getStyle('B18')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('C18', number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month2->month_name))->sales_starting_amount - ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month2->month_name))->cancels_affecting_amount, 2));
				$objPHPExcel->getActiveSheet()->getStyle('C18')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('D18', number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month3->month_name))->sales_starting_amount - ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month3->month_name))->cancels_affecting_amount, 2));
				$objPHPExcel->getActiveSheet()->getStyle('D18')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('E18', number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month4->month_name))->sales_starting_amount - ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month4->month_name))->cancels_affecting_amount, 2));
				$objPHPExcel->getActiveSheet()->getStyle('E18')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('F18', number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month5->month_name))->sales_starting_amount - ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month5->month_name))->cancels_affecting_amount, 2));
				$objPHPExcel->getActiveSheet()->getStyle('F18')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('G18', number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month6->month_name))->sales_starting_amount - ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month6->month_name))->cancels_affecting_amount, 2));
				$objPHPExcel->getActiveSheet()->getStyle('G18')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('H18', number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month7->month_name))->sales_starting_amount - ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month7->month_name))->cancels_affecting_amount, 2));
				$objPHPExcel->getActiveSheet()->getStyle('H18')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('I18', number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month8->month_name))->sales_starting_amount - ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month8->month_name))->cancels_affecting_amount, 2));
				$objPHPExcel->getActiveSheet()->getStyle('I18')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				//Net Change (count)
				$objPHPExcel->getActiveSheet()->SetCellValue('A19', 'Net Change (count)');
				$objPHPExcel->getActiveSheet()->getStyle('A19')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('B19', (ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month1->month_name))->sales_starting_count - ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month1->month_name))->cancels_affecting_count));
				$objPHPExcel->getActiveSheet()->getStyle('B19')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('C19', (ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month2->month_name))->sales_starting_count - ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month2->month_name))->cancels_affecting_count));
				$objPHPExcel->getActiveSheet()->getStyle('C19')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('D19', (ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month3->month_name))->sales_starting_count - ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month3->month_name))->cancels_affecting_count));
				$objPHPExcel->getActiveSheet()->getStyle('D19')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('E19', (ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month4->month_name))->sales_starting_count - ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month4->month_name))->cancels_affecting_count));
				$objPHPExcel->getActiveSheet()->getStyle('E19')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('F19', (ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month5->month_name))->sales_starting_count - ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month5->month_name))->cancels_affecting_count));
				$objPHPExcel->getActiveSheet()->getStyle('F19')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('G19', (ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month6->month_name))->sales_starting_count - ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month6->month_name))->cancels_affecting_count));
				$objPHPExcel->getActiveSheet()->getStyle('G19')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('H19', (ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month7->month_name))->sales_starting_count - ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month7->month_name))->cancels_affecting_count));
				$objPHPExcel->getActiveSheet()->getStyle('H19')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('I19', (ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month8->month_name))->sales_starting_count - ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month8->month_name))->cancels_affecting_count));
				$objPHPExcel->getActiveSheet()->getStyle('I19')->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
				));
			}
		}
		
		if( $page == 'aaaReport' )
		{
			$filename = 'AAA Report';
			
			$skillIds[] = $selectedSkills; 
			
			if( $selectedSkills == 11 )
			{
				$skillIds = array(11, 33);
			}
			
			if( $selectedSkills == 12 )
			{
				$skillIds = array(12, 34);
			}
			
			$sql = "
				SELECT 
					co.company_name as company_name,
					CONCAT (c.firstname, ' ', c.lastname) AS customer_name,
					lch.lead_phone_number AS lead_phone,
					ld.first_name AS lead_first_name, 
					ld.last_name AS lead_last_name,
					ld.partner_first_name AS partner_first_name,
					ld.partner_last_name AS partner_last_name,
					ld.email_address AS lead_email,
					lch.is_skill_child,
					lch.disposition,
					lch.disposition_detail,
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
				WHERE ls.skill_id IN(".implode(', ', $skillIds).")
				AND lch.disposition IS NOT NULL 
				AND lch.start_call_time >= '".date('Y-m-d 00:00:00', strtotime($dateFilterStart))."' 
				AND lch.start_call_time <= '".date('Y-m-d 23:59:59', strtotime($dateFilterEnd))."'
				AND lch.status !=4 
				ORDER BY lch.start_call_time DESC
			";
			
			$connection = Yii::app()->db;
			$command = $connection->createCommand($sql);
			$models = $command->queryAll();
			
			if( $models )
			{
				//start of first sheet 			
				$objPHPExcel->setActiveSheetIndex(0);
				
				$ctr = 1;
				
				$dispositions = array();
				
				foreach( $models as $model )
				{
					$dispositions[$model['disposition']]['count'][] = $model['disposition'];
				}

				if( $dispositions )
				{
					foreach( $dispositions as $key => $value )
					{
						$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $key );
						$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, count($value['count']) );
						
						$ctr++;
					}
				}
				
				//start of second sheet
				$objPHPExcel->createSheet();
				$objPHPExcel->setActiveSheetIndex(1);
				
				$ctr = 1;
										
				$headers = array(
					'A' => 'Company',
					'B' => 'Customer',
					'C' => 'Lead Phone',
					'D' => 'Lead First',
					'E' => 'Lead Last',
					'F' => 'Partner First',
					'G' => 'Partner Last',
					'H' => 'Lead Email Address',
					'I' => 'Date/Time',
					'J' => 'Disposition',
					'K' => 'Sub Disposition',
					'L' => 'Callback Date/Time',
					'M' => 'Disposition Note',
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
			
				foreach( $models as $model )
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
					$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $model['lead_email']);
					$objPHPExcel->getActiveSheet()->SetCellValue('I'.$ctr, $callDate->format('m/d/Y g:i A'));
					
					$objPHPExcel->getActiveSheet()->SetCellValue('J'.$ctr, $model['disposition']);
					
					$objPHPExcel->getActiveSheet()->SetCellValue('K'.$ctr, $model['disposition_detail']);
					
					if( in_array($model['disposition'], array('Call Back', 'Callback', 'Call Back - Confirm')) )
					{
						$objPHPExcel->getActiveSheet()->SetCellValue('L'.$ctr, $callBackDate->format('m/d/Y g:i A'));
					}
					else
					{
						$objPHPExcel->getActiveSheet()->SetCellValue('L'.$ctr, '');
					}
					
					$objPHPExcel->getActiveSheet()->SetCellValue('M'.$ctr, $model['agent_note']);
					
					$ctr++;	
				}
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
				
				if( isset($_REQUEST['billing_period']) )
				{
					$grandTotalReducedAmount = 0;
					$grandTotalSubsidyAmount = 0;
					
					$newCustomerCtr = 0;
					$cancelledCustomerCtr = 0;
					
					$billingPeriod = $_REQUEST['billing_period'];
					$billingPeriodMonth = date('m', strtotime($_REQUEST['billing_period']));
					$billingPeriodYear = date('Y', strtotime($_REQUEST['billing_period']));
					
					foreach( $models as $customerQueue )
					{
						$customerSkill = CustomerSkill::model()->find(array(
							'with' => 'customer',
							'condition' => '
								t.customer_id = :customer_id 
								AND t.skill_id = :skill_id 
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
								':month' => date('n', strtotime($billingPeriod)),
								':year' => date('Y', strtotime($billingPeriod))
							),
						));
						
						if( $customerSkill && !empty($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00' && date('Y-m', strtotime($billingPeriod)) >= date('Y-m', strtotime($customerSkill->start_month)) && empty($customerRemoved) )
						{
							if( isset($customerSkill->contract) )
							{
								$contract = $customerSkill->contract;
								$customer = $customerSkill->customer;
								
								$customerIsCallable = false;
								
								$totalLeads = 0;
								$totalAmount = 0;
								$subsidyAmount = 0;
								$month = '';
								$latestTransactionType = '';
								$latestTransactionStatus = '';
								
								$isOnHold = '';
								$isCancelled = '';
								$customerStatus = 'Active';
								
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
										':billing_period' => date('M Y', strtotime($billingPeriod))
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
								
								if( empty($existingBilling) || ($existingBilling && empty($existingBillingForCurrentMonth) && $existingBilling->billing_period != $billingPeriod) )
								{
									$customerIsCallable = false;
									
									//check status and start date
									if( isset($customerSkill->contract) && isset($customerSkill->customer) && $customerSkill->customer->status == 1 && $customerSkill->customer->is_deleted == 0 && !empty($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00' && date('Y-m', strtotime($billingPeriod)) >= date('Y-m', strtotime($customerSkill->start_month)) )
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
											if( strtotime($billingPeriod) >= strtotime($customerSkill->is_contract_hold_start_date) && strtotime($billingPeriod) <= strtotime($customerSkill->is_contract_hold_end_date) )
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
										if( strtotime($billingPeriod) >= strtotime($customerSkill->end_month) )
										{
											$customerIsCallable = false;
											$isCancelled = 'Y';
											$customerStatus = 'Cancelled';
										}
									}
									
									// echo 'customerStatus: ' . $customerStatus;
									
									// echo '<br>';
									
									// echo 'customerIsCallable: ' . $customerIsCallable;
									
									// echo '<br><br>';
									
									//&& ($creditCardCount > 0 || $echecksCount > 0)
									if( $customerIsCallable )
									{
										/*
											totalLeads
											totalAmount
											totalReducedAmount
											totalCreditAmount
											subsidyAmount
											isBilled
										*/
										
										$contractCreditSubsidys = $this->getCustomerContractCreditAndSubsidy($customer, $contract, $billingPeriod);	

										$totalAmount = $contractCreditSubsidys[$contract->id]['totalAmount'];										
										$totalCreditAmount = $contractCreditSubsidys[$contract->id]['totalCreditAmount'];		
										$subsidyAmount = $contractCreditSubsidys[$contract->id]['subsidyAmount'];		
										$totalReducedAmount = $contractCreditSubsidys[$contract->id]['totalReducedAmount'];		
										$totalLeads = $contractCreditSubsidys[$contract->id]['totalLeads'];		
								
										$month = date('M Y', strtotime($_REQUEST['billing_period']));

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
										else
										{
											if( $paymentMethodType == '-' )
											{
												$paymentMethodType = '';
											}
										}
																						
										//credit amount should not be over the Amount, for the customer will ask it to be billed next month -aug 9, 2016
										if($totalCreditAmount > $totalAmount)
										{
											$totalCreditAmount = $totalAmount - $subsidyAmount;
										}
										
										// if( in_array($contract->contract_name, array('Farmers Per Appointment 2016 FOLIO','Farmers Per Name 2016 FOLIO')) )
										// {
											// $totalReducedAmount = ($totalAmount - $totalCreditAmount);
										// }
										// else
										// {
											// $totalReducedAmount = ($totalAmount - $totalCreditAmount - $subsidyAmount);
										// }

										$totalReducedAmount = abs($totalAmount - $subsidyAmount);
										
										if( $totalCreditAmount < 0 )
										{
											$totalReducedAmount = $totalReducedAmount + abs($totalCreditAmount);
										}
										else
										{
											$totalReducedAmount = $totalReducedAmount - abs($totalCreditAmount);
										}
										
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
										
										if( date('Y-m', strtotime($customerSkill->start_month)) == date('Y-m', strtotime('+1 month', strtotime($billingPeriod))) )
										{
											$newCustomerCtr++;
										}
										
										if( date('Y-m', strtotime($customerSkill->end_month)) == date('Y-m', strtotime($billingPeriod)) )
										{
											$cancelledCustomerCtr++;
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
											'contract' => $customerQueue->contract_name,
											'quantity' => $totalLeads,
											'billing_cycle' => $month,
											'payment_method' => $paymentMethodType,
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
				}
				
				
				$ctr = 1;
				
				// $objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, 'New: ' . $newCustomerCtr);
				// $objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, 'Cancelled: ' . $cancelledCustomerCtr);
				// $objPHPExcel->getActiveSheet()->SetCellValue('K'.$ctr, 'Net: ' . ($newCustomerCtr - $cancelledCustomerCtr));
				
				// $ctr = 3;
				
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
					
					if( count($explodedContent) == 1 )
					{
						$importedCount = 0;										
						$duplicateCount = 0;
						$badCount = 0;
						$cellphoneCount = 0;
					}
					elseif( count($explodedContent) == 2 )
					{
						$importedCount = filter_var(strip_tags($explodedContent[1]), FILTER_SANITIZE_NUMBER_INT);
						$duplicateCount = 0;
						$badCount = 0;
						$cellphoneCount = 0;
					}
					elseif( count($explodedContent) == 8 )
					{
						$importedCount = filter_var(strip_tags($explodedContent[2]), FILTER_SANITIZE_NUMBER_INT);												
						$duplicateCount = filter_var(strip_tags($explodedContent[6]), FILTER_SANITIZE_NUMBER_INT);
						$badCount = filter_var(strip_tags($explodedContent[7]), FILTER_SANITIZE_NUMBER_INT);
						$cellphoneCount = 0;
					}
					elseif( count($explodedContent) == 9 )
					{
						$importedCount = filter_var(strip_tags($explodedContent[1]), FILTER_SANITIZE_NUMBER_INT);												
						$duplicateCount = filter_var(strip_tags($explodedContent[7]), FILTER_SANITIZE_NUMBER_INT);
						$badCount = filter_var(strip_tags($explodedContent[8]), FILTER_SANITIZE_NUMBER_INT);
						$cellphoneCount = filter_var(strip_tags($explodedContent[6]), FILTER_SANITIZE_NUMBER_INT);
					}
					elseif( count($explodedContent) == 10 )
					{
						$importedCount = filter_var(strip_tags($explodedContent[1]), FILTER_SANITIZE_NUMBER_INT);												
						$duplicateCount = filter_var(strip_tags($explodedContent[7]), FILTER_SANITIZE_NUMBER_INT);
						$badCount = filter_var(strip_tags($explodedContent[8]), FILTER_SANITIZE_NUMBER_INT);
						$cellphoneCount = filter_var(strip_tags($explodedContent[6]), FILTER_SANITIZE_NUMBER_INT);
					}
					else
					{	
						$importedCount = filter_var(strip_tags($explodedContent[2]), FILTER_SANITIZE_NUMBER_INT);												
						$duplicateCount = filter_var(strip_tags($explodedContent[4]), FILTER_SANITIZE_NUMBER_INT);
						$badCount = filter_var(strip_tags($explodedContent[5]), FILTER_SANITIZE_NUMBER_INT);
						$cellphoneCount = 0;
					}											


					$total = $importedCount + $duplicateCount + $badCount + $cellphoneCount;
					
					$percentageOfNotImported = 0;
					
					if($total != 0)
						$percentageOfNotImported = (($duplicateCount + $badCount + $cellphoneCount) / $total) * 100;
											
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
					
					$objPHPExcel->getActiveSheet()->SetCellValue('I'.$ctr, $cellphoneCount);
					
					$objPHPExcel->getActiveSheet()->SetCellValue('J'.$ctr, $badCount);
					
					$objPHPExcel->getActiveSheet()->SetCellValue('K'.$ctr, number_format($percentageOfNotImported,2).'%');
					
					
					$objPHPExcel->getActiveSheet()->SetCellValue('L'.$ctr, $listStatus);
					
					$objPHPExcel->getActiveSheet()->SetCellValue('M'.$ctr, $officeStaffString);
				
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
			elseif( $page == 'agentPerformanceLite' )
			{
				$ctr = $ctr + 1;
				
				$totalDials = 0;
				$totalAppointments = 0;
				$totalHours = 0;
				
				foreach( $models as $model )
				{
					if( $model['total_hours'] != '' )
					{
						$totalDials += $model['dials'];
						$totalAppointments += $model['appointments'];
						$totalHours += round($model['total_hours'], 2);
						
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
						$objPHPExcel->getActiveSheet()->getStyle('C'.$ctr)->applyFromArray(array(
							'alignment' => array(
								'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
							),
						));
						
						$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $model['dials']);
						$objPHPExcel->getActiveSheet()->getStyle('D'.$ctr)->applyFromArray(array(
							'alignment' => array(
								'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
							),
						));
						
						if( $model['dials'] > 0 && $model['total_hours'] > 0 )
						{
							$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, round($model['dials'] / $model['total_hours'], 2));
							$objPHPExcel->getActiveSheet()->getStyle('E'.$ctr)->applyFromArray(array(
								'alignment' => array(
									'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
								),
							));
						}
						else
						{
							
							$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, 0);
							$objPHPExcel->getActiveSheet()->getStyle('E'.$ctr)->applyFromArray(array(
								'alignment' => array(
									'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
								),
							));
						}
						
						if( !isset($_GET['customer_id']) )
						{
							$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $model['appointments']);
							$objPHPExcel->getActiveSheet()->getStyle('F'.$ctr)->applyFromArray(array(
								'alignment' => array(
									'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
								),
							));

							if( $model['appointments'] > 0 && $model['total_hours'] > 0 )
							{
								$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, round($model['appointments'] / $model['total_hours'], 2));
								$objPHPExcel->getActiveSheet()->getStyle('G'.$ctr)->applyFromArray(array(
									'alignment' => array(
										'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
									),
								));
							}
							else
							{
								
								$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, 0);
								$objPHPExcel->getActiveSheet()->getStyle('G'.$ctr)->applyFromArray(array(
									'alignment' => array(
										'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
									),
								));
							}
						}
						
						$ctr++;	
					}
				}
				
				$ctr = $ctr + 1;
					
				$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, 'TOTAL');
				$objPHPExcel->getActiveSheet()->getStyle('A'.$ctr)->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					),
					'font'  => array(
						'bold' => true,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, round($totalHours, 2));
				$objPHPExcel->getActiveSheet()->getStyle('C'.$ctr)->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					),
					'font'  => array(
						'bold' => true,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, number_format($totalDials));
				$objPHPExcel->getActiveSheet()->getStyle('D'.$ctr)->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					),
					'font'  => array(
						'bold' => true,
					),
				));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, round($totalDials/$totalHours, 2));
				$objPHPExcel->getActiveSheet()->getStyle('E'.$ctr)->applyFromArray(array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					),
					'font'  => array(
						'bold' => true,
					),
				));
				
				if( !isset($_GET['customer_id']) )
				{
					$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, number_format($totalAppointments));
					$objPHPExcel->getActiveSheet()->getStyle('F'.$ctr)->applyFromArray(array(
						'alignment' => array(
							'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
						),
						'font'  => array(
							'bold' => true,
						),
					));
					
					$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, round($totalAppointments/$totalHours, 2));
					$objPHPExcel->getActiveSheet()->getStyle('G'.$ctr)->applyFromArray(array(
						'alignment' => array(
							'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
						),
						'font'  => array(
							'bold' => true,
						),
					));
				}
			}
			else
			{
				$ctr = 2;
				
				foreach($models as $model)
				{
					if( $page == 'customerContactInfo' )
					{
						$customer = $model;
						
						$customerSkills = $customer->customerSkills;
										
						if(!empty($customerSkills))
						{
							foreach( $customerSkills as $customerSkill )
							{
								$skill = '';
								$contractName = '';
								$startDate = '';
								$endDate = '';
								$holdStartDate = '';
								$holdEndDate = '';
								$promo = '';
								$quantity = 0;
								$status = 'Inactive';
								
								if( isset($customerSkill->contract) )
								{
									$contract = $customerSkill->contract;
									$skill = $customerSkill->skill->skill_name;
									
									if( isset($contract) && $contract->fulfillment_type != null )
									{
										$contractName = $contract->contract_name;
										
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
									
									
									if( time() >= strtotime($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00' )
									{
										$status = 'Active';
									
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
										
										// if( $status == 'On Hold' )
										// {
											if( !empty($customerSkill->is_contract_hold_start_date) && !in_array($customerSkill->is_contract_hold_start_date, array('0000-00-00', '1969-12-31')) )
											{
												$holdStartDate .= date('m/d/Y', strtotime($customerSkill->is_contract_hold_start_date));
											}
											
											if( !empty($customerSkill->is_contract_hold_end_date) && !in_array($customerSkill->is_contract_hold_end_date, array('0000-00-00', '1969-12-31')) )
											{
												$holdEndDate .= date('m/d/Y', strtotime($customerSkill->is_contract_hold_end_date));
											}
											
										// }
									}
								}
							
						
								$state = !empty($customerSkill->customer->state) ? State::model()->findByPk($customerSkill->customer->state)->name : '';
								
								if( !empty($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00' )
								{
									$startDate .= date('m/d/Y', strtotime($customerSkill->start_month));
								}
								
								if( !empty($customerSkill->end_month) && $customerSkill->end_month != '0000-00-00' )
								{
									$endDate = date('m/d/Y', strtotime($customerSkill->end_month));
								}
								
								if( in_array($customerSkill->skill_id, array(11,12)) )
								{
									$quantity = 0;
									$skill = '';
									$contractName = '';
									$status = 'Inactive';
									$startDate = '';
									$endDate = '';
									$holdStartDate = '';
									$holdEndDate = '';
								}
								
								if(!empty($customerSkill->promo))
								{
									$promo = $customerSkill->promo->promo_name;
								}
								
								$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $customerSkill->customer->custom_customer_id );
								
								$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $customerSkill->customer->lastname );
								
								$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $customerSkill->customer->firstname );
								
								$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $status );
								
								$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, isset($customerSkill->customer->company) ? $customerSkill->customer->company->company_name : '' );
								
								$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $customerSkill->customer->phone );
								
								$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $customerSkill->customer->email_address );
								
								$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $customerSkill->customer->address1 );
								
								$objPHPExcel->getActiveSheet()->SetCellValue('I'.$ctr, $customerSkill->customer->city );
								
								$objPHPExcel->getActiveSheet()->SetCellValue('J'.$ctr, $state );
								
								$objPHPExcel->getActiveSheet()->SetCellValue('K'.$ctr, $customerSkill->customer->zip );
								
								$objPHPExcel->getActiveSheet()->SetCellValue('L'.$ctr, $skill );
								
								$objPHPExcel->getActiveSheet()->SetCellValue('M'.$ctr, $contractName );	
								
								$objPHPExcel->getActiveSheet()->SetCellValue('N'.$ctr, $promo );	
							
								$objPHPExcel->getActiveSheet()->SetCellValue('O'.$ctr, $quantity );	
								
								$objPHPExcel->getActiveSheet()->SetCellValue('P'.$ctr, $startDate );
								
								$objPHPExcel->getActiveSheet()->SetCellValue('Q'.$ctr, $endDate );	
								
								$objPHPExcel->getActiveSheet()->SetCellValue('R'.$ctr, $holdStartDate );	
								
								$objPHPExcel->getActiveSheet()->SetCellValue('S'.$ctr, $holdEndDate );	
								
								$ctr++;	
							}
						}
						else
						{
							$status = 'No Skill';
							$state = !empty($customer->state) ? State::model()->findByPk($customer->state)->name : '';
							$skill = '';
							$contractName = '';
							$startDate = '';
							$endDate = '';
							$holdStartDate = '';
							$holdEndDate = '';
							$promo = '';
							$quantity = 0;
											
							$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $customer->custom_customer_id );
								
							$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $customer->lastname );
							
							$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $customer->firstname );
							
							$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $status );
							
							$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, isset($customer->company) ? $customer->company->company_name : '' );
							
							$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $customer->phone );
							
							$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $customer->email_address );
							
							$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $customer->address1 );
							
							$objPHPExcel->getActiveSheet()->SetCellValue('I'.$ctr, $customer->city );
							
							$objPHPExcel->getActiveSheet()->SetCellValue('J'.$ctr, $state );
							
							$objPHPExcel->getActiveSheet()->SetCellValue('K'.$ctr, $customer->zip );
							
							$objPHPExcel->getActiveSheet()->SetCellValue('L'.$ctr, $skill );
							
							$objPHPExcel->getActiveSheet()->SetCellValue('M'.$ctr, $contractName );	
							
							$objPHPExcel->getActiveSheet()->SetCellValue('N'.$ctr, $promo );	
							
							$objPHPExcel->getActiveSheet()->SetCellValue('O'.$ctr, $quantity );	
							
							$objPHPExcel->getActiveSheet()->SetCellValue('P'.$ctr, $startDate );
							
							$objPHPExcel->getActiveSheet()->SetCellValue('Q'.$ctr, $endDate );	
							
							$objPHPExcel->getActiveSheet()->SetCellValue('R'.$ctr, $holdStartDate );	
							
							$objPHPExcel->getActiveSheet()->SetCellValue('S'.$ctr, $holdEndDate );	
							
							$ctr++;	
						}
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
						
						$objPHPExcel->getActiveSheet()->SetCellValue('K'.$ctr, $model->billing_type );
						
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
						
						$securityGroups = Account::listAccountType();

						
						$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $model->accountUser->employee_number);
						
						$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $model->accountUser->badge_id);
						
						$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $model->accountUser->first_name);
						
						$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $model->accountUser->last_name);
						
						$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $model->accountUser->full_time_status);
						
						$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $status);
						
						$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $model->accountUser->date_hire);
						
						$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $model->accountUser->date_termination);
						
						$objPHPExcel->getActiveSheet()->SetCellValue('I'.$ctr, $model->accountUser->phone_extension);
						
						$objPHPExcel->getActiveSheet()->SetCellValue('J'.$ctr, $model->accountUser->job_title);
						
						$objPHPExcel->getActiveSheet()->SetCellValue('K'.$ctr, $securityGroups[$model->account_type_id]);
						
						$objPHPExcel->getActiveSheet()->SetCellValue('L'.$ctr, $totalScheduledWorkHours);
						
						$ctr++;	
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
						$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $model['lead_email']);
						$objPHPExcel->getActiveSheet()->SetCellValue('I'.$ctr, $callDate->format('m/d/Y g:i A'));
						
						if( $model['is_skill_child'] == 1 )
						{
							$objPHPExcel->getActiveSheet()->SetCellValue('J'.$ctr, 'Child');
						}
						else
						{
							$objPHPExcel->getActiveSheet()->SetCellValue('J'.$ctr, 'Parent');
						}
						
						$objPHPExcel->getActiveSheet()->SetCellValue('K'.$ctr, $model['disposition']);
						
						$objPHPExcel->getActiveSheet()->SetCellValue('L'.$ctr, $model['disposition_detail']);
						
						if( in_array($model['disposition'], array('Call Back', 'Callback', 'Call Back - Confirm')) )
						{
							$objPHPExcel->getActiveSheet()->SetCellValue('M'.$ctr, $callBackDate->format('m/d/Y g:i A'));
						}
						else
						{
							$objPHPExcel->getActiveSheet()->SetCellValue('M'.$ctr, '');
						}
						
						$objPHPExcel->getActiveSheet()->SetCellValue('N'.$ctr, $model['agent_note']);
						$objPHPExcel->getActiveSheet()->SetCellValue('O'.$ctr, $model['agent']); 
						
						$ctr++;	
					}
					
					if( $page == 'lowNames' )
					{
						if( time() > strtotime($model->start_date) )
						{
							$skillStatus = 'Active';
							
							$customerSkill = CustomerSkill::model()->find(array(
								'condition' => '
									customer_id = :customer_id
									AND skill_id = :skill_id
								',
								'params' => array(
									':customer_id' => $model->customer_id,
									':skill_id' => $model->skill_id
								)
							));
							
							if( isset($customerSkill->contract) && isset($customerSkill->customer) && $customerSkill->customer->status == 1 && $customerSkill->customer->is_deleted == 0 && time() >= strtotime($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00' )
							{
								$skillStatus = 'Active';
							}
							
							if( !empty($customerSkill->end_month) && date('Y', strtotime($customerSkill->end_month)) >= 2015 )
							{
								if( time() >= strtotime($customerSkill->end_month) )
								{
									$skillStatus = 'Cancelled';
								}
							}
							
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
							
							$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $skillStatus );
							$objPHPExcel->getActiveSheet()->SetCellValue('I'.$ctr, $model->skill_name );
							$objPHPExcel->getActiveSheet()->SetCellValue('J'.$ctr, $model->start_date );
							$objPHPExcel->getActiveSheet()->SetCellValue('K'.$ctr, $endDate );
							$objPHPExcel->getActiveSheet()->SetCellValue('L'.$ctr, $model->contracted_quantity );
							$objPHPExcel->getActiveSheet()->SetCellValue('M'.$ctr, $model->current_goals );
							$objPHPExcel->getActiveSheet()->SetCellValue('N'.$ctr, $model->current_dials );
							$objPHPExcel->getActiveSheet()->SetCellValue('O'.$ctr, $model->available_leads );
							$objPHPExcel->getActiveSheet()->SetCellValue('P'.$ctr, $model->not_completed_leads );
							$objPHPExcel->getActiveSheet()->SetCellValue('Q'.$ctr, $model->recertifiable_leads );
							$objPHPExcel->getActiveSheet()->SetCellValue('R'.$ctr, $model->recyclable_leads );
							$objPHPExcel->getActiveSheet()->SetCellValue('S'.$ctr, $model->names_waiting );
							$objPHPExcel->getActiveSheet()->SetCellValue('T'.$ctr, $needsNames );
						
							$ctr++;
						}
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
			'with' => 'skill',
			'condition' => 'skill.status=1',
		));
		
		if( $filter == 'All' )
		{
			$models = EmailMonitor::model()->findAll(array(
				'condition' => 'date_created > DATE_SUB(NOW(), INTERVAL 24 HOUR) AND status IN (0,2,4) AND html_content !=""',
			));
		}
		else
		{
			$models = EmailMonitor::model()->findAll(array(
				'condition' => 'skill_id = :skill_id AND date_created > DATE_SUB(NOW(), INTERVAL 24 HOUR) AND status IN (0,2,4) AND html_content !=""',
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
	
	public function actionPreviewText($id, $filter)
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
			
			$this->redirect(array('reports/previewText', 'id'=>$model->id, 'filter'=>$filter));
		}
		
		$this->layout='main-no-navbar';
		$this->render('previewText', array('model'=>$model, 'filter'=>$filter));
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
		exit;
		
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
						AND uls.skill_id IN (11,12,15,16,17,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34)
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
		
		if( Yii::app()->user->isGuest )
		{
			$this->redirect(array('site/login'));
		}
		
		$this->render('callerIdListing', array(
			'models' => $models, 
		));
	}
	
	public function actionAjaxOpenCnamApi()
	{	
		$result = array(
			'status' => 'error',
			'name' => '',
		);
		
		if( isset($_POST['ajax']) )
		{	
			$accountSid = 'ACb486b8c85c7e479c8a3713e834541db5';
			$authToken = 'AUa69438d470a54ae4ab82b4e07ae1df6d';
			
			if( $_POST['type'] == 'checkName'  )
			{	
				$model = CompanyDid::model()->findByPk($_POST['id']);
				
				if( $model )
				{
					$phone = $model->did;

					$url = "https://api.opencnam.com/v3/phone/+" . $phone . "?account_sid=".$accountSid."&auth_token=".$authToken;
					
					$name =  file_get_contents($url);
					
					if( !empty($name) )
					{
						$model->cname = $name;
						
						if( $model->save(false) )
						{	
							$result['status'] = 'success';
							$result['name'] = $name;
						}
					}
				}
			}
			elseif( $_POST['type'] == 'checkDID' )
			{
				$phone = $_POST['did'];

				$url = "https://api.opencnam.com/v3/phone/+" . $phone . "?account_sid=".$accountSid."&auth_token=".$authToken;
				
				$name =  file_get_contents($url);
				
				if( !empty($name) )
				{
					$result['status'] = 'success';
					$result['name'] = $phone.' - '.$name;
				}
			}
			else
			{
				$models = CompanyDid::model()->findAll();
				
				if( $models )
				{
					foreach( $models as $model )
					{
						$phone = $model->did;

						$url = "https://api.opencnam.com/v3/phone/+" . $phone . "?account_sid=".$accountSid."&auth_token=".$authToken;
						
						$name =  file_get_contents($url);
						
						if( !empty($name) )
						{
							$model->cname = $name;
							$model->save(false);
						}
					}
					
					$html = $this->renderPartial('ajaxCallerIdListingTable', array(
						'models' => $models,
					), true);
					
					$result['status'] = 'success';
					$result['html'] = $html;
				}
			}
		}
		
		echo json_encode($result);
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
	
			$mail = new JPhpMailer;
			
			$mail->SMTPAuth = true;		
			$mail->SMTPSecure = 'tls';   		
			$mail->SMTPDebug = 2; 
			$mail->Port = 25;      
			$mail->Host = 'mail.engagex.com';	
			$mail->Username = 'service@engagex.com';   
			$mail->Password = "_T*8c>ja";      
			
			$mail->SetFrom('service@engagex.com');
			
			$mail->Subject = 'Cancel DID';

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
			$headers = "From: {$mailName} <".$emailAddress.">'.$lfcr.'";
			$headers .= "Reply-To: {$replyTo} <".$emailAddress.">'.$lfcr.'";

			$headers .= "MIME-Version: 1.0'.$lfcr.'";
			$headers .= "Content-Type: multipart/alternative; boundary=\"$mime_boundary\"'.$lfcr.'";
			$headers .= "Content-class: urn:content-classes:calendarmessage'.$lfcr.'";
			
			if(!empty($bccs))
				$headers .= 'Bcc: '. $bccs . "'.$lfcr.''.$lfcr.'";
			
			if(!empty($ccs))
				$headers .= 'Cc: '. $ccs . "'.$lfcr.''.$lfcr.'";
			
			$recipientHolder = array();

			//Create Email Body (HTML)
			$message = '';
			$message .= "--$mime_boundary'.$lfcr.'";
			$message .= "Content-Type: text/html; charset=UTF-8'.$lfcr.'";
			$message .= "Content-Transfer-Encoding: 8bit'.$lfcr.''.$lfcr.'";

			$message .= "<html>'.$lfcr.'";
			$message .= "<body>'.$lfcr.'";
			$message .= $emailMonitorContent;
			$message .= "</body>'.$lfcr.'";
			$message .= "</html>'.$lfcr.'";
			
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

					$message .= "Content-Type: application/octet-stream; name=\"".$v."\"'.$lfcr.''.$lfcr.'"; // use different content types here
					$message .= "Content-Transfer-Encoding: base64'.$lfcr.''.$lfcr.'";
					$message .= "Content-Disposition: attachment; filename=\"".$v."\"'.$lfcr.''.$lfcr.''.$lfcr.''.$lfcr.'";
					$message .= $content."'.$lfcr.''.$lfcr.''.$lfcr.''.$lfcr.'";
					$message .= "--".$mime_boundary."--"."'.$lfcr.''.$lfcr.'";
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

				$ical = "BEGIN:VCALENDAR'.$lfcr.''.$lfcr.'";
				$ical .= "VERSION:2.0'.$lfcr.''.$lfcr.'";
				$ical .= "PRODID:-//Microsoft Corporation//Outlook 14.0 MIMEDIR//EN'.$lfcr.''.$lfcr.'";
				$ical .= "METHOD:PUBLISH'.$lfcr.''.$lfcr.'";
				
				
				$ical .= "BEGIN:VTIMEZONE'.$lfcr.'";
				$ical .= "TZID:{$timeZone}'.$lfcr.'";
				$ical .= "TZURL:http://tzurl.org/zoneinfo-outlook/{$timeZone}'.$lfcr.'";
				$ical .= "X-LIC-LOCATION:{$timeZone}'.$lfcr.'";
				$ical .= "BEGIN:DAYLIGHT'.$lfcr.'";
				$ical .= "TZOFFSETFROM:{$start_zone}'.$lfcr.'";
				$ical .= "TZOFFSETTO:{$end_zone}'.$lfcr.'";
				$ical .= "TZNAME:". date("T")."'.$lfcr.'";
				$ical .= "DTSTART:{$dtStart}'.$lfcr.'";
				$ical .= "END:DAYLIGHT'.$lfcr.'";
				$ical .= "BEGIN:STANDARD'.$lfcr.'";
				$ical .= "TZOFFSETFROM:{$start_zone}'.$lfcr.'";
				$ical .= "TZOFFSETTO:{$end_zone}'.$lfcr.'";
				$ical .= "TZNAME:".date("T")."'.$lfcr.'";
				$ical .= "DTSTART:{$dtStart}'.$lfcr.'";
				$ical .= "END:STANDARD'.$lfcr.'";      
				$ical .= "END:VTIMEZONE'.$lfcr.'";

				$ical .= "BEGIN:VEVENT'.$lfcr.''.$lfcr.'";
				$ical .= "ORGANIZER;CN={$customerName}:MAILTO:".$customerEmail."'.$lfcr.''.$lfcr.'";

				$ical .= "UID:".strtoupper(md5($event_id))."'.$lfcr.''.$lfcr.'";
				$ical .= "SEQUENCE:".$sequence."'.$lfcr.''.$lfcr.'";
				$ical .= "STATUS:".$status."'.$lfcr.''.$lfcr.'";

				$ical .= "DTSTAMP:".$dtStamp."'.$lfcr.''.$lfcr.'";
				$ical .= "DTSTART;TZID=".$timeZone.":".$dtStart."'.$lfcr.''.$lfcr.'";
				$ical .= "DTEND;TZID=".$timeZone.":".$dtEnd."'.$lfcr.''.$lfcr.'";

				$ical .= "LOCATION:".$location."'.$lfcr.''.$lfcr.'";
				$ical .= "SUMMARY:".$summary."'.$lfcr.''.$lfcr.'";
				$ical .= "DESCRIPTION:{$description}"."'.$lfcr.''.$lfcr.'";

				$ical .= "END:VEVENT'.$lfcr.''.$lfcr.'";
				$ical .= "END:VCALENDAR'.$lfcr.''.$lfcr.'";

				### disable attachment of ICAL for now, we have the ICAL in the LINK using replacement_code (see Lead Call History) ##
				#$message .= "--$mime_boundary'.$lfcr.'";							
				#$message .= "Content-Type: text/calendar;name=\"meeting.ics\";method=REQUEST'.$lfcr.'";
				#$message .= "Content-Transfer-Encoding: 8bit'.$lfcr.''.$lfcr.'";
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

	public function actionHourlyAttendance()
	{
		$this->layout='main-no-navbar';

		$html = '';
		
		$models = AccountUser::model()->findAll(array(
			'with' => 'account',
			'condition' => 't.job_title IN ("Call Agent", "Team Leader") AND account.status = 1 AND account.id NOT IN(4)',
		));
		
		if ( $models )
		{
			foreach( $models as $model )
			{
				$totalScheduledHours = 0;
				
				//SCHEDULES
				$schedules = AccountLoginSchedule::model()->findAll(array(
					'condition' => 'account_id = :account_id AND day_name = :day_name AND type=1',
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
						AND alt.time_in >= '".date('Y-m-d 07:00:00')."' 
						AND alt.time_in <= '".date('Y-m-d 22:00:00')."'
						AND alt.status !=4 
					) AS total_hours_worked,
					(
						SELECT COUNT(lch.id) 
						FROM ud_lead_call_history lch
						LEFT JOIN ud_lists uls ON uls.id = lch.list_id
						WHERE lch.agent_account_id = a.`id`
						AND lch.start_call_time >= '".date('Y-m-d 00:00:00')."' 
						AND lch.start_call_time <= '".date('Y-m-d 22:00:00')."' 
						AND uls.skill_id IN (11,12,15,16,17,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34)
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

				$html .= '<tr style="background:'.$trStyle.';">';
							
					$html .= '<td>';
					
						$html .= '<div style="width:200px;">';
							$html .= CHtml::link($model->first_name.' '.$model->last_name, array('/hr/accountUser/assignments/', 'id'=>$model->account_id), array('target'=>'_blank')); 
						$html .= '</div>';	
						
					$html .= '</td>';
							
					$html .= '<td class="center">';

						$html .= round($totalScheduledHours, 2);
						
					$html .= '</td>';
					
					$html .= '<td class="center">';

						if(  $agent['total_hours_worked'] > 0 )
						{
							$html .= round($agent['total_hours_worked'], 2);
						}
						else
						{
							$html .= '0';
						}
						
					$html .= '</td>';

					foreach (Calendar::createTimeRange('7:00 AM', '10:00 PM', '15 minutes') as $time) 
					{
						// if( in_array(date('g:i A', $time), $agentWorkTimes) || in_array(date('g:i A', $time), $agentBreakTimes) )
						// {
							// if( in_array(date('g:i A', $time), $agentWorkTimes) )
							// {
								// $html .= '<td class="success">&nbsp;</td>';
							// }
							// else
							// {
								// $html .= '<td class="info">&nbsp;</td>';
							// }
						// }
						// else
						// {
							$html .= '<td class="">&nbsp;</td>';

						// }
					}
								
				$html .= '</tr>';
			}
		}
		else
		{
			$html .= '<tr><td colspan="38">No results found.<td></tr>';
		}
		
		if( isset($_POST['ajax']) )
		{
			$html = $this->renderPartial('ajaxHourlyAttendanceTable', array(
				'models' => $models,
				'html' => $html,
			), true);
			
			$result['status'] = 'success';
			$result['html'] = $html;
			
			echo json_encode($result);
			Yii::app()->end();
		}
		else
		{	
			$this->render('hourlyAttendance', array(
				'models' => $models,
				'html' => $html,
			));
		}
	}

	public function actionAjaxLeadHistory()
	{
		$result = array(
			'status' => '',
			'message' => '',
			'html' => '',
		);
		
		if( isset($_POST['ajax']) && isset($_POST['id']) )
		{
			$model = Lead::model()->findByPk($_POST['id']);
			
			$leadHistories = LeadHistory::model()->findAll(array(
				'condition' => 'lead_id = :lead_id AND type != 6 AND status !=3',
				'params' => array(
					':lead_id' => $_POST['id'],
				), 
			));
			
			
			$html = $this->renderPartial('ajaxLeadHistory', array(
				'model' => $model,
				'leadHistories' => $leadHistories,
			), true);
			
			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}
	
	public function actionAjaxViewCustomData()
	{
		$result = array(
			'status' => '',
			'message' => '',
			'html' => '',
		);
		
		if( isset($_POST['ajax']) && isset($_POST['list_id']) && isset($_POST['lead_id']) )
		{
			$model = Lead::model()->findByPk($_POST['lead_id']);
			
			$listCustomDatas = ListCustomData::model()->findAll(array(
				'condition' => 'list_id = :list_id AND display_on_form=1 AND status=1',
				'params' => array(
					':list_id' => $_POST['list_id'],
				),
				'order' => 'ordering ASC',
			));
			
			
			$html = $this->renderPartial('ajaxViewCustomData', array(
				'leadId' => $_POST['lead_id'],
				'model' => $model,
				'listCustomDatas' => $listCustomDatas,
			), true);
			
			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}

	
	//Impact Report
	public function actionAjaxViewImpactReportLink()
	{
		$result = array(
			'status' => '',
			'message' => '',
			'html' => '',
		);
		
		if( isset($_POST['ajax']) && isset($_POST['month_name']) && isset($_POST['link_name']) )
		{
			$models = ImpactReportLink::model()->findAll(array(
				'condition' => 'month_name = :month_name AND link_name = :link_name',
				'params' => array(
					':month_name' => $_POST['month_name'],
					':link_name' => $_POST['link_name']
				)
			));
			
			$html = $this->renderPartial('ajaxViewImpactReportLink', array(
				'month_name' => $_POST['month_name'],
				'link_name' => $_POST['link_name'],
				'models' => $models,
			), true);
			
			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}

	public function actionAjaxImpactReportSettings()
	{
		$result = array(
			'status' => '',
			'message' => '',
			'html' => '',
		);
		
		$model = ImpactReportSettings::model()->findByPk(1);
		
		if( isset($_POST['ImpactReportSettings']) )
		{
			$model->attributes = $_POST['ImpactReportSettings'];
			
			if( $model->auto_email_frequency == 'DAILY' )
			{
				$model->auto_email_day = null;
			}
			
			if( $model->save(false) )
			{
				$result['status'] = 'success';
				$result['message'] = 'Settings has been updated successfully.';
			}
			else
			{
				$result['message'] = 'Database error.';
			}
		}
		elseif( isset($_POST['updateDayOptions']) )
		{
			if( $_POST['frequency'] == 'ONCE A MONTH' )
			{
				$html .= '
					<option value="">- Select -</option>
					<option value="First day of month">First day of month</option>
					<option value="Last day of month">Last day of month</option>
					<option value="End of Day 15th">End of Day 15th</option>
				';
			}
			else
			{
				$html .= '
					<option value="">- Select -</option>
					<option value="Monday">Monday</option>
					<option value="Tuesday">Tuesday</option>
					<option value="Wednesday">Wednesday</option>
					<option value="Thursday">Thursday</option>
					<option value="Friday">Friday</option>
					<option value="Saturday">Saturday</option>
					<option value="Sunday">Sunday</option>
				';
			}
			
			$result['status'] = 'success';
			$result['html'] = $html;
		}
		else
		{
			$html = $this->renderPartial('ajaxViewImpactReportSettings', array(
				'model' => $model,
			), true);
			
			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}

	
	//conflict monitors functions
	public function actionConflictMonitor()
	{
		$models = CalendarAppointment::model()->findAll(array(
			'with' => array('lead', 'lead.customer'),
			'condition' => ' 
				YEAR(t.start_date) = "2018"
				AND t.title IN ("SCHEDULE CONFLICT", "LOCATION CONFLICT")
				AND t.status IN (1,5)
				AND customer.id IS NOT NULL	
				AND DATE(t.start_date) >= NOW()
			',
			'params' => array(
				
			),
			'order' => 'DATE(t.start_date) ASC',
			// 'order' => 'customer.id ASC, DATE(t.start_date) ASC',
		));
		
		$this->render('conflictMonitor', array(
			'models' => $models
		));
	}
	
	public function actionConflictAddToQueue($calendar_appointment_id, $force=0)
	{
		$model = CalendarAppointment::model()->findByPk($calendar_appointment_id);
		
		$existingLeadHopperEntry = LeadHopper::model()->find(array(
			'condition' => 'lead_id = :lead_id',
			'params' => array(
				':lead_id' => $model->lead_id,
			),
		));
		
		if( $existingLeadHopperEntry )
		{
			$conflictCall = $existingLeadHopperEntry;
		}
		else
		{
			$lead = $model->lead;
			$list = $lead->list;
			$customer = $lead->customer;
			
			$conflictCall = new LeadHopper;
			
			$skillChildConfirmation = SkillChild::model()->find(array(
				'condition' => 'skill_id = :skill_id AND type = :type',
				'params' => array(
					':skill_id' => $list->skill_id,
					':type' => SkillChild::TYPE_CONFIRM,
				),
			));
			
			if($skillChildConfirmation !== null)
			{
				$conflictCall->skill_child_confirmation_id = $skillChildConfirmation->id;
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
				$conflictCall->skill_child_reschedule_id = $skillChildReschedule->id;
			}
			
			if( !empty($lead->timezone) )
			{
				$timeZone = $lead->timezone;
			}
			else
			{
				$timeZone = AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $customer->phone) );
			}
			
			$conflictCall->setAttributes(array(
				'lead_id' => $lead->id,
				'list_id' => $list->id,
				'skill_id' => $list->skill_id,
				'customer_id' => $customer->id,
				'lead_timezone' => $timeZone,
				'lead_language' => $lead->language,
				// 'status' => 'DONE',
				// 'type' => LeadHopper::TYPE_CONFIRMATION_CALL,
			));
		}
		
		$confirmationDate = $model->start_date;
			
		//if actual appointment date is on monday move it friday last week
		if( date('N', strtotime($confirmationDate)) == 1 )
		{
			$confirmationDate = date('Y-m-d', strtotime('last friday', strtotime($confirmationDate))).' '.date('H:i:s', strtotime($confirmationDate));
		}
		else
		{
			//move it to 1 business day before the actual appointment date
			$confirmationDate = date('Y-m-d', strtotime('-1 day', strtotime($confirmationDate))).' '.date('H:i:s', strtotime($confirmationDate));
		}
		
		
		$conflictCall->calendar_appointment_id = $model->id;
		$conflictCall->appointment_date = $confirmationDate;
		$conflictCall->agent_account_id = null;
		
		$conflictCall->status = $force == 0 ? 'DONE' : 'READY';
		
		$conflictCall->type = LeadHopper::TYPE_CONFLICT;
		
		if( $conflictCall->save(false) )
		{
			Yii::app()->user->setFlash('success', 'Lead was added to queue successfully.');
		}
		else
		{
			Yii::app()->user->setFlash('success', 'Database error. Lead not added.');
		}
		
		$this->redirect(array('conflictMonitor'));
	}

	public function actionConflictsLoadAll()
	{
		$loadedCtr = 0;
		
		$models = CalendarAppointment::model()->findAll(array(
			'with' => array('lead', 'lead.customer'),
			'condition' => ' 
				YEAR(t.start_date) = "2018"
				AND t.title IN ("SCHEDULE CONFLICT", "LOCATION CONFLICT")
				AND t.status IN (1,5)
				AND customer.id IS NOT NULL	
				AND DATE(t.start_date) >= NOW()
			',
			'params' => array(
				
			),
			'order' => 'DATE(t.start_date) ASC',
		));
		
		if( $models )
		{
			$completedConflictDispos = array('Appointment Set', 'Client Complete', 'Appointment Confirmed', 'Appointment Confirmed - Left Message', 'Answering Machine - Left Message', 'Client to Contact Agent', 'Do Not Call');
			
			foreach( $models as $model )
			{
				$existingLeadHopperEntry = LeadHopper::model()->find(array(
					'condition' => 'lead_id = :lead_id',
					'params' => array(
						':lead_id' => $model->lead_id,
					),
				));

				$afterConflictLast3Calls = LeadCallHistory::model()->findAll(array(
					'condition' => '
						lead_id = :lead_id 
					',
					'params' => array(
						':lead_id' => $model->lead_id,
					),
					'order' => 'date_created DESC',
					'limit' => 3
				));
				
				$disposAfterConflict = array();
				
				foreach( $afterConflictLast3Calls as $afterConflictLast3Call )
				{
					$disposAfterConflict[] = $afterConflictLast3Call->disposition;
				}
				
				$dispoMatch = array_intersect($disposAfterConflict, $completedConflictDispos);
				
				if( empty($existingLeadHopperEntry) && count($dispoMatch) == 0 )
				{
					$customerIsCallable = false;
							
					$customerSkill = CustomerSkill::model()->find(array(
						'condition' => 'customer_id = :customer_id AND skill_id = :skill_id AND status=1',
						'params' => array(
							':customer_id' => $model->lead->customer_id,
							':skill_id' => $model->lead->list->skill_id,
						),
					));
					
					if( $customerSkill )
					{
						if( isset($customerSkill->contract) && isset($customerSkill->customer) && $customerSkill->customer->status == 1 && $customerSkill->customer->is_deleted == 0 && time() >= strtotime($customerSkill->start_month) )
						{
							$customerIsCallable = true;
						}
						
						if( $customerSkill->is_contract_hold == 1 )
						{
							if( !empty($customerSkill->is_contract_hold_start_date) && !empty($customerSkill->is_contract_hold_end_date) )
							{
								if( time() >= strtotime($customerSkill->is_contract_hold_start_date) && time() <= strtotime($customerSkill->is_contract_hold_end_date) )
								{
									$customerIsCallable = false;
								}
							}
						}
						
						if( !empty($customerSkill->end_month) && date('Y', strtotime($customerSkill->end_month)) >= 2015 )
						{
							if( time() >= strtotime($customerSkill->end_month) )
							{
								$customerIsCallable = false;
							}
						}
					}
					
					if( $customerSkill->status == 1 && $customerIsCallable )
					{
						$skillChildConfirmation = SkillChild::model()->find(array(
							'condition' => 'skill_id = :skill_id AND type = :type',
							'params' => array(
								':skill_id' => $model->lead->list->skill_id,
								':type' => SkillChild::TYPE_CONFIRM,
							),
						));
						
						if( $skillChildConfirmation )
						{
							$customerSkillChild = CustomerSkillChild::model()->find(array(
								'condition' => 'customer_id = :customer_id AND skill_id = :skill_id AND customer_skill_id = :customer_skill_id AND skill_child_id = :skill_child_id',
								'params' => array(
									':customer_id' => $model->lead->customer_id,
									':skill_id' => $model->lead->list->skill_id,
									':customer_skill_id' => $customerSkill->id,
									':skill_child_id' => $skillChildConfirmation->id
								),
							));
							
							if( $customerSkillChild && $customerSkillChild->is_enabled == 1 )
							{
						
								$lead = $model->lead;
								$list = $lead->list;
								$customer = $lead->customer;
								
								$conflictCall = new LeadHopper;
								
								$skillChildConfirmation = SkillChild::model()->find(array(
									'condition' => 'skill_id = :skill_id AND type = :type',
									'params' => array(
										':skill_id' => $list->skill_id,
										':type' => SkillChild::TYPE_CONFIRM,
									),
								));
								
								if($skillChildConfirmation !== null)
								{
									$conflictCall->skill_child_confirmation_id = $skillChildConfirmation->id;
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
									$conflictCall->skill_child_reschedule_id = $skillChildReschedule->id;
								}
								
								if( !empty($lead->timezone) )
								{
									$timeZone = $lead->timezone;
								}
								else
								{
									$timeZone = AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $customer->phone) );
								}
								
								$conflictCall->setAttributes(array(
									'lead_id' => $lead->id,
									'list_id' => $list->id,
									'skill_id' => $list->skill_id,
									'customer_id' => $customer->id,
									'lead_timezone' => $timeZone,
									'lead_language' => $lead->language,
								));
								
								$confirmationDate = $model->start_date;
								
								//if actual appointment date is on monday move it friday last week
								if( date('N', strtotime($confirmationDate)) == 1 )
								{
									$confirmationDate = date('Y-m-d', strtotime('last friday', strtotime($confirmationDate))).' '.date('H:i:s', strtotime($confirmationDate));
								}
								else
								{
									//move it to 1 business day before the actual appointment date
									$confirmationDate = date('Y-m-d', strtotime('-1 day', strtotime($confirmationDate))).' '.date('H:i:s', strtotime($confirmationDate));
								}
								
								
								$conflictCall->calendar_appointment_id = $model->id;
								$conflictCall->appointment_date = $confirmationDate;
								$conflictCall->agent_account_id = null;
								
								$conflictCall->status = 'DONE';
								
								$conflictCall->type = LeadHopper::TYPE_CONFLICT;
								
								if( $conflictCall->save(false) )
								{
									$loadedCtr++;
								}
							}
						}
					}
				}
			}
		}
		
		if( $loadedCtr > 0 )
		{
			Yii::app()->user->setFlash('success', '<b>'.$loadedCtr.' conflict(s)</b> loaded to queue.');
		}
		else
		{
			Yii::app()->user->setFlash('danger', '<b>No conflict(s)</b> found.');
		}
		
		$this->redirect(array('conflictMonitor'));
	}

	public function actionConflictsForceAll()
	{
		$existingConflicts = LeadHopper::model()->find(array(
			'with' => 'calendarAppointment',
			'condition' => '
				t.status="DONE" 
				AND t.type = :type
				AND calendarAppointment.status IN (1,5)
			',
			'params' => array(
				':type' => LeadHopper::TYPE_CONFLICT,
			),
		));
		
		if( $existingConflicts )
		{
			LeadHopper::model()->updateAll(array('status'=> 'READY'), 'type = ' . LeadHopper::TYPE_CONFLICT);
			Yii::app()->user->setFlash('success', '<b>'.count($existingConflicts) . ' conflict(s)</b> forced to queue.');
		}
		else
		{
			Yii::app()->user->setFlash('danger', '<b>No conflict(s)</b> found.');
		}
		
		$this->redirect(array('conflictMonitor'));
	}

	//confirm monitor functions
	public function actionAppointmentMonitor()
	{
		if( date('w') == 5)
		{
			$startDate = date('Y-m-d 00:00:00', strtotime('+1 day'));
			$endDate = date('Y-m-d 23:59:59', strtotime('+3 day'));
		}
		else
		{
			$startDate = date('Y-m-d 00:00:00', strtotime('+1 day'));
			$endDate = date('Y-m-d 23:59:59', strtotime('+1 day'));
		}
		
		// $startDate = '2018-05-26 00:00:00';
		// $endDate = '2018-05-29 23:59:59';
		
		// $models = CalendarAppointment::model()->findAll(array(
			// 'with' => 'lead',
			// 'condition' => '
				// t.start_date >= "'.$startDate.'"
				// AND t.start_date <= "'.$endDate.'"
				// AND t.title IN ("APPOINTMENT SET", "INSERT APPOINTMENT")
				// AND t.status !=4
				// AND t.lead_id IS NOT NULL
				// AND lead.id IS NOT NULL
			// ',
		// ));
		
		$sql = '
			SELECT ca.id AS appointment_id, ca.lead_id, ca.title AS appointment_title, ca.status AS appointment_status, ca.start_date AS appointment_start_date, ca.date_updated AS appointment_date_updated,
			CONCAT(ul.first_name, " ", ul.last_name) AS lead_name, ul.timezone AS lead_timezone, ul.customer_id, ul.list_id, uls.skill_id,
			CONCAT(c.firstname, " ", c.lastname) AS customer_name 
			FROM ud_calendar_appointment ca
			LEFT JOIN ud_lead ul 
			ON ul.id = ca.lead_id
			LEFT JOIN ud_customer c 
			ON c.id = ul.customer_id
			LEFT JOIN ud_lists AS uls
			ON uls.id = ul.list_id
			WHERE ca.start_date >= "'.$startDate.'"
			AND ca.start_date <= "'.$endDate.'"
			AND ca.title IN ("APPOINTMENT SET", "INSERT APPOINTMENT")
			AND ca.status !=4
			AND ca.lead_id IS NOT NULL
			ORDER BY CASE ul.timezone
				WHEN "AST" THEN 1
				WHEN "EST" THEN 2
				WHEN "CST" THEN 3
				WHEN "MST" THEN 4
				WHEN "PST" THEN 5
				WHEN "AKST" THEN 6
				ELSE 7
			END
		';
		
		$connection = Yii::app()->db;
		$command = $connection->createCommand($sql);
		$models = $command->queryAll();
		
		// echo 'w: ' . date('w');
		// echo '<br>';
		// echo 'startDate: ' . $startDate;
		// echo '<br>';
		// echo 'endDate: ' . $endDate;
		// echo '<br>';
		// echo 'models: ' . count($models);

		// exit;
		
		$this->render('appointmentMonitor', array(
			'models' => $models
		));	
	}
	
	public function actionAppointmentAddtoQueue($calendar_appointment_id)
	{
		$model = CalendarAppointment::model()->findByPk($calendar_appointment_id, $force=0);
		
		$lead = $model->lead;
		$list = $lead->list;
		$customer = $list->customer;
		
		$existingLeadHopperEntry = LeadHopper::model()->find(array(
			'condition' => 'lead_id = :lead_id',
			'params' => array(
				':lead_id' => $model->lead_id,
			),
		));
		
		if( $existingLeadHopperEntry )
		{
			$confirmationCall = $existingLeadHopperEntry;
		}
		else
		{
			$confirmationCall = new LeadHopper;
			
			$skillChildConfirmation = SkillChild::model()->find(array(
				'condition' => 'skill_id = :skill_id AND type = :type',
				'params' => array(
					':skill_id' => $list->skill_id,
					':type' => SkillChild::TYPE_CONFIRM,
				),
			));
			
			if($skillChildConfirmation !== null)
			{
				$confirmationCall->skill_child_confirmation_id = $skillChildConfirmation->id;
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
				$confirmationCall->skill_child_reschedule_id = $skillChildReschedule->id;
			}
			
			if( !empty($lead->timezone) )
			{
				$timeZone = $lead->timezone;
			}
			else
			{
				$timeZone = AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $customer->phone) );
			}
			
			$confirmationCall->setAttributes(array(
				'lead_id' => $lead->id,
				'list_id' => $list->id,
				// 'skill_id' => $list->skill_id,
				'customer_id' => $customer->id,
				'lead_timezone' => $timeZone,
				'lead_language' => $lead->language,
				// 'status' => 'DONE',
				// 'type' => LeadHopper::TYPE_CONFIRMATION_CALL,
			));
		}
		
		$confirmationDate = $model->start_date;
			
		//if actual appointment date is on monday move it friday last week
		if( date('N', strtotime($confirmationDate)) == 1 )
		{
			$confirmationDate = date('Y-m-d', strtotime('last friday', strtotime($confirmationDate))).' '.date('H:i:s', strtotime($confirmationDate));
		}
		else
		{
			//move it to 1 business day before the actual appointment date
			$confirmationDate = date('Y-m-d', strtotime('-1 day', strtotime($confirmationDate))).' '.date('H:i:s', strtotime($confirmationDate));
		}
		
		// $confirmationDate = date('2017-09-01 H:i:s', strtotime($confirmationDate));
		
		$confirmationCall->calendar_appointment_id = $model->id;
		$confirmationCall->appointment_date = $confirmationDate;
		$confirmationCall->agent_account_id = null;
		
		$confirmationCall->skill_id = $list->skill_id;
		
		if($model->title == 'NO SHOW RESCHEDULE')
		{
			$confirmationCall->status = 'READY';
			$confirmationCall->type = LeadHopper::TYPE_NO_SHOW_RESCHEDULE;
			// $confirmationCall->calendar_appointment_id = null;
			$confirmationCall->appointment_date = null;
		}
		elseif( $model->title == 'RESCHEDULE APPOINTMENT')
		{
			$confirmationCall->status = 'READY';
			$confirmationCall->type = LeadHopper::TYPE_RESCHEDULE;
			// $confirmationCall->calendar_appointment_id = null;
			$confirmationCall->appointment_date = null;
		}
		else
		{
			$confirmationCall->status = 'DONE';
			$confirmationCall->type = LeadHopper::TYPE_CONFIRMATION_CALL;
		}
		
		$confirmationCall->status = $force == 0 ? 'DONE' : 'READY';
			
		if( $confirmationCall->save(false) )
		{
			Yii::app()->user->setFlash('success', 'Lead was added to queue successfully.');
		}
		else
		{
			Yii::app()->user->setFlash('success', 'Database error. Lead not added.');
		}
		
		$this->redirect(array('appointmentMonitor'));
	}

	public function actionAppointmentsLoadAll()
	{
		$loadedCtr = 0;
		
		if( date('w', strtotime('+1 day')) == 5)
		{
			$startDate = date('Y-m-d 00:00:00', strtotime('+2 day'));
			$endDate = date('Y-m-d 23:59:59', strtotime('+4 day'));
		}
		else
		{
			$startDate = date('Y-m-d 00:00:00', strtotime('+1 day'));
			$endDate = date('Y-m-d 23:59:59', strtotime('+1 day'));
		}
		
		// $startDate = '2017-09-02 00:00:00';
		// $endDate = '2017-09-05 23:59:59';

		$models = CalendarAppointment::model()->findAll(array(
			'with' => 'lead',
			'condition' => '
				t.start_date >= "'.$startDate.'"
				AND t.start_date <= "'.$endDate.'"
				AND t.title IN ("APPOINTMENT SET", "INSERT APPOINTMENT")
				AND t.status !=4
				AND t.lead_id IS NOT NULL
				AND lead.id IS NOT NULL
			',
		));
		
		// echo 'startDate: ' . $startDate;
		// echo '<br>';
		// echo 'endDate: ' . $endDate;
		// echo '<br>';
		// echo 'models: ' . count($models);
		
		// echo '<br><br>';
		// exit;
		
		if( $models )
		{
			$completedConflictDispos = array('Appointment Confirmed', 'Appointment Confirmed - Left Message');
			
			foreach( $models as $model )
			{
				$existingLeadHopperEntry = LeadHopper::model()->find(array(
					'condition' => 'lead_id = :lead_id',
					'params' => array(
						':lead_id' => $model->lead_id,
					),
				));
				
				$afterConflictLast3Calls = LeadCallHistory::model()->findAll(array(
					'condition' => '
						lead_id = :lead_id 
						AND DATE(date_created) > :appointment_date
					',
					'params' => array(
						':lead_id' => $model->lead_id,
						':appointment_date' => date('Y-m-d', strtotime($model->date_updated)),
					),
					'order' => 'date_created DESC',
					'limit' => 3
				));
				
				$disposAfterConflict = array();
				
				foreach( $afterConflictLast3Calls as $afterConflictLast3Call )
				{
					$disposAfterConflict[] = $afterConflictLast3Call->disposition;
				}
				
				$dispoMatch = array_intersect($disposAfterConflict, $completedConflictDispos);
				
				if( $model && empty($existingLeadHopperEntry) && count($dispoMatch) == 0 )
				{
					$confirmationCall = new LeadHopper;
					
					$skillChildConfirmation = SkillChild::model()->find(array(
						'condition' => 'skill_id = :skill_id AND type = :type',
						'params' => array(
							':skill_id' => $list->skill_id,
							':type' => SkillChild::TYPE_CONFIRM,
						),
					));
					
					if($skillChildConfirmation !== null)
					{
						$confirmationCall->skill_child_confirmation_id = $skillChildConfirmation->id;
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
						$confirmationCall->skill_child_reschedule_id = $skillChildReschedule->id;
					}
					
					if( !empty($lead->timezone) )
					{
						$timeZone = $lead->timezone;
					}
					else
					{
						$timeZone = AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $customer->phone) );
					}
					
					$confirmationCall->setAttributes(array(
						'lead_id' => $lead->id,
						'list_id' => $list->id,
						// 'skill_id' => $list->skill_id,
						'customer_id' => $customer->id,
						'lead_timezone' => $timeZone,
						'lead_language' => $lead->language,
						// 'status' => 'DONE',
						// 'type' => LeadHopper::TYPE_CONFIRMATION_CALL,
					));
				
					$confirmationDate = $model->start_date;
						
					//if actual appointment date is on monday move it friday last week
					if( date('N', strtotime($confirmationDate)) == 1 )
					{
						$confirmationDate = date('Y-m-d', strtotime('last friday', strtotime($confirmationDate))).' '.date('H:i:s', strtotime($confirmationDate));
					}
					else
					{
						//move it to 1 business day before the actual appointment date
						$confirmationDate = date('Y-m-d', strtotime('-1 day', strtotime($confirmationDate))).' '.date('H:i:s', strtotime($confirmationDate));
					}
					
					// $confirmationDate = date('2017-09-01 H:i:s', strtotime($confirmationDate));
					
					$confirmationCall->calendar_appointment_id = $model->id;
					$confirmationCall->appointment_date = $confirmationDate;
					$confirmationCall->agent_account_id = null;
					
					$confirmationCall->skill_id = $list->skill_id;
					
					if($model->title == 'NO SHOW RESCHEDULE')
					{
						$confirmationCall->status = 'READY';
						$confirmationCall->type = LeadHopper::TYPE_NO_SHOW_RESCHEDULE;
						// $confirmationCall->calendar_appointment_id = null;
						$confirmationCall->appointment_date = null;
					}
					elseif( $model->title == 'RESCHEDULE APPOINTMENT')
					{
						$confirmationCall->status = 'READY';
						$confirmationCall->type = LeadHopper::TYPE_RESCHEDULE;
						// $confirmationCall->calendar_appointment_id = null;
						$confirmationCall->appointment_date = null;
					}
					else
					{
						$confirmationCall->status = 'DONE';
						$confirmationCall->type = LeadHopper::TYPE_CONFIRMATION_CALL;
					}
					
					$confirmationCall->status = $force == 0 ? 'DONE' : 'READY';
						
					// if( $confirmationCall->save(false) )
					// {
						$loadedCtr++;
					// }
				}
			}
		}
		
		if( $loadedCtr > 0 )
		{
			Yii::app()->user->setFlash('success', '<b>'.$loadedCtr. ' confirm(s)</b> loaded to queue.');
		}
		else
		{
			Yii::app()->user->setFlash('danger', '<b>No confirm(s)</b> found.');
		}
		
		$this->redirect(array('appointmentMonitor'));
	}
	
	public function actionAppointmentsForceAll()
	{
		$existingConfirms = LeadHopper::model()->find(array(
			'condition' => '
				t.status="DONE" 
				AND t.type = :type
			',
			'params' => array(
				':type' => LeadHopper::TYPE_CONFIRMATION_CALL,
			),
		));
		
		if( $existingConfirms )
		{
			LeadHopper::model()->updateAll(array('status'=> 'READY'), 'type = ' . LeadHopper::TYPE_CONFIRMATION_CALL);
			Yii::app()->user->setFlash('success', '<b>'.count($existingConfirms) . ' confirm(s)</b> forced to queue.');
		}
		else
		{
			Yii::app()->user->setFlash('danger', '<b>No confirm(s)</b> found.');
		}
		
		$this->redirect(array('appointmentMonitor'));
	}

	// reschedule monitor functions
	public function actionRescheduleMonitor()
	{
		// $models = LeadCallHistory::model()->findAll(array(
			// 'condition' => '
				// t.disposition IN (
					// "Agent Rescheduled", "Agent Rescheduled - Appt time not known", "Appointment Cancelled - Reschedule Later", 
					// "Appointment Cancelled - Rescheduled Later", "Appointment Rescheduled", "Appointment Rescheduled with Agent"
				// )
				// AND YEAR(t.date_created) = "2018"
			// '
		// ));
		
		$models = LeadHopper::model()->findAll(array(
			'with' => 'calendarAppointment',
			'condition' => '
				t.type IN (6,7) 
				AND t.lead_id IS NOT NULL 
				AND calendarAppointment.date_updated >= "2018-04-01"
			',
			'order'=>'calendarAppointment.date_updated DESC',
		));
		
		$this->render('rescheduleMonitor', array(
			'models' => $models
		));
	}

	public function actionReschedulesLoadAll()
	{
		Yii::app()->user->setFlash('success', '0 loaded to queue.');
		
		$this->redirect('rescheduleMonitor');
	}
	
	public function actionReschedulesForceAll()
	{
		$models = LeadHopper::model()->findAll(array(
			'with' => 'calendarAppointment',
			'condition' => '
				t.type IN (6,7) 
				AND t.lead_id IS NOT NULL 
				AND calendarAppointment.date_updated >= "2018-04-01"
			',
			'order'=>'calendarAppointment.date_updated DESC',
		));
		
		Yii::app()->user->setFlash('success', count($models) . ' forced to queue.');
		
		$this->redirect('rescheduleMonitor');
	}
	
	public function actionRescheduleAddToQueue()
	{
		Yii::app()->user->setFlash('success', 'Lead was added to queue successfully.');
		
		$this->redirect('rescheduleMonitor');
	}

	//callback monitor functions
	public function actionCallBackMonitor()
	{
		$models = LeadCallCronProcess::model()->findAll(array(
			'condition' => '
				disposition IN ("Call Back", "Call Back - Confirm", "Will Call Back")
				AND DATE(callback_time) = DATE(NOW())
				AND callback_time IS NOT NULL 
				AND callback_time != ""
			',
			'order'=>'callback_time ASC',
			// 'limit' => 50
		));
		
		$this->render('callBackMonitor', array(
			'models' => $models
		));
	}
	
	public function actionCallBackAddtoQueue($id, $force=0)
	{
		$model = LeadCallCronProcess::model()->findByPk($id);

		$existingLeadHopperEntry = LeadHopper::model()->find(array(
			'condition' => 'lead_id = :lead_id',
			'params' => array(
				':lead_id' => $model->lead_id,
			),
		));
		
		if( $existingLeadHopperEntry )
		{
			$hopperEntry = $existingLeadHopperEntry;
		}
		else
		{
			$hopperEntry = new LeadHopper;
							
			$hopperEntry->setAttributes(array(
				'lead_id' => $model->lead_id,
				'list_id' => $model->lead_list_id,
				'skill_id' => $model->lead->list->skill_id,
				'customer_id' => $model->customer_id,
				'calendar_appointment_id' => $model->calendar_appointment_id,
				'lead_language' => $model->lead_language,
				'lead_timezone' => $model->lead_timezone,
				'callback_date' => $model->callback_time,
				'type' => $hopperEntry::TYPE_CALLBACK,
				'status' => 'DONE'
			));
		}

		$hopperEntry->status = $force == 0 ? 'DONE' : 'READY';
			
		if( $hopperEntry->save(false) )
		{
			Yii::app()->user->setFlash('success', 'Lead was added to queue successfully.');
		}
		else
		{
			Yii::app()->user->setFlash('success', 'Database error. Lead not added.');
		}
		
		$this->redirect(array('callBackMonitor'));
	}
	
	public function actionCallBackLoadAll()
	{
		$loadedCtr = 0;
		
		$models = LeadCallCronProcess::model()->findAll(array(
			'condition' => '
				disposition IN ("Call Back", "Call Back - Confirm", "Will Call Back")
				AND DATE(callback_time) = DATE(NOW())
				AND callback_time IS NOT NULL 
				AND callback_time != ""
			',
			'order'=>'callback_time ASC',
		));
		
		if( $models )
		{
			foreach( $models as $model )
			{
				$existingLeadHopperEntry = LeadHopper::model()->find(array(
					'condition' => 'lead_id = :lead_id',
					'params' => array(
						':lead_id' => $model->lead_id,
					),
				));
				
				$lastCall = LeadCallHistory::model()->find(array(
					'condition' => 'lead_id = :lead_id',
					'params' => array(
						':lead_id' => $model->lead_id
					),
					'order' => 'date_created DESC',
				));
				
				if( empty($existingLeadHopperEntry) && strtotime($model->callback_time) >= strtotime($lastCall->date_created) && in_array($lastCall->disposition, array("Call Back", "Call Back - Confirm", "Will Call Back")) )
				{
					$hopperEntry = new LeadHopper;
							
					$hopperEntry->setAttributes(array(
						'lead_id' => $model->lead_id,
						'list_id' => $model->lead_list_id,
						'skill_id' => $model->lead->list->skill_id,
						'customer_id' => $model->customer_id,
						'calendar_appointment_id' => $model->calendar_appointment_id,
						'lead_language' => $model->lead_language,
						'lead_timezone' => $model->lead_timezone,
						'callback_date' => $model->callback_time,
						'type' => $hopperEntry::TYPE_CALLBACK,
						'status' => 'DONE'
					));
					
					if( $hopperEntry->save(false) )
					{
						$loadedCtr++;
					}
				}
			}
		}
		
		if( $loadedCtr > 0 )
		{
			Yii::app()->user->setFlash('success', '<b>'.$loadedCtr. ' callback(s)</b> loaded to queue.');
		}
		else
		{
			Yii::app()->user->setFlash('danger', '<b>No callback(s)</b> found.');
		}
		
		$this->redirect(array('callBackMonitor'));
	}

	public function actionCallBackForceAll()
	{
		$loadedCtr = 0;
		
		$models = LeadCallCronProcess::model()->findAll(array(
			'condition' => '
				disposition IN ("Call Back", "Call Back - Confirm", "Will Call Back")
				AND DATE(callback_time) = DATE(NOW())
				AND callback_time IS NOT NULL 
				AND callback_time != ""
			',
			'order'=>'callback_time ASC',
		));
		
		if( $models )
		{
			foreach( $models as $model )
			{
				$existingLeadHopperEntry = LeadHopper::model()->find(array(
					'condition' => 'lead_id = :lead_id',
					'params' => array(
						':lead_id' => $model->lead_id,
					),
				));
				
				if( $existingLeadHopperEntry )
				{
					$existingLeadHopperEntry->status = "READY";

					if( $existingLeadHopperEntry->save(false) )
					{
						$loadedCtr++;
					}
				}
			}
		}
		
		if( $loadedCtr > 0 )
		{
			Yii::app()->user->setFlash('success', '<b>'.$loadedCtr. ' callback(s)</b> forced to queue.');
		}
		else
		{
			Yii::app()->user->setFlash('danger', '<b>No callback(s)</b> found.');
		}
		
		$this->redirect(array('callBackMonitor'));
	}

	//completed leads monitor functions
	public function actionCompletedLeadMonitor()
	{
		$models = LeadHopper::model()->findAll(array(
			'with' => 'lead',
			'condition' => '
				t.type = 1
				AND t.status = "READY"
				AND lead.status = 3
			',
		));
		
		$this->render('completedLeadMonitor', array(
			'models' => $models
		));
	}
	
	public function actionRemoveCompletedLead($id)
	{
		$model = LeadHopper::model()->findByPk($id);
		
		if( $model && $model->delete() )
		{
			Yii::app()->user->setFlash('success', 'Lead was removed from queue.');
		}
		
		$this->redirect(array('completedLeadMonitor'));
	}
	
	public function actionRemoveAllCompletedLeads()
	{
		$deleteCtr = 0;
		
		$models = LeadHopper::model()->findAll(array(
			'with' => 'lead',
			'condition' => '
				t.type = 1
				AND t.status = "READY"
				AND lead.status = 3
			',
		));
		
		if( $models )
		{
			foreach( $models as $model )
			{
				if( $model->delete() )
				{
					$deleteCtr++;
				}
			}
		}
		
		Yii::app()->user->setFlash('success', $deleteCtr . ' lead(s) were removed from queue.');
		
		$this->redirect(array('completedLeadMonitor'));
	}

	//generic skill
	public function actionAjaxGenericSkillAddSettings()
	{
		$html = '';
		
		$result = array(
			'status' => '',
			'message' => '',
			'html' => $html,
		);
		
		if( isset($_POST['ReportDeliverySettings']) )
		{
			$model = new ReportDeliverySettings;
			
			$model->account_id = Yii::app()->user->account->id;
			
			$model->attributes = $_POST['ReportDeliverySettings'];
			
			if( $model->save(false) )
			{
				$result['status'] = 'success';
			}
		}
		else
		{
			$skills = Skill::model()->findAll(array(
				'condition' => 'status=1',
				'order' => 'skill_name ASC',
			));

			$html = $this->renderPartial('ajaxGenericSkillAddSettings', array(
				'skills' => $skills,
			), true);
			
			$result['status'] = 'success';
			$result['html'] = $html;
		}

		echo json_encode($result);
	}

	public function actionAjaxGenericSkillDeleteSettings()
	{
		$html = '';
		
		$result = array(
			'status' => '',
			'message' => '',
			'html' => $html,
		);
		
		if( isset($_POST['ajax']) && $_POST['id'] )
		{
			$model = ReportDeliverySettings::model()->findByPk($_POST['id']);
			
			if( $model->delete() )
			{
				$result['status'] = 'success';
			}
		}

		echo json_encode($result);
	}
	
	public function actionAjaxGetCustomers()
	{
		$html = '<option value="">- Select -</option>';
		
		$result = array(
			'status' => '',
			'message' => '',
			'html' => $html,
		);
		
		if( isset($_POST['ajax']) && isset($_POST['skill_id']) )
		{
			$customerQueues = CustomerQueueViewer::model()->findAll(array(
				'condition' => '
					skill_id = :skill_id
					AND status=1
				',
				'params' => array(
					':skill_id' => $_POST['skill_id']
				),
				'order' => 'customer_name ASC',
			));

			if( $customerQueues )
			{
				foreach( $customerQueues as $customerQueue )
				{
					$html .= '<option value="'.$customerQueue->customer_id.'">'.$customerQueue->customer_name.'</option>';
				}
			}		
		}
		
		$result['html'] = $html;
		
		echo json_encode($result);
	}

	
	public function actionYoyTrends()
	{
		if( isset($_POST['ajax']) )
		{
			$result = array(
				'status' => 'error',
				'message' => '',
			);
			
			
			
			echo json_decode($result);
			Yii::app()->end();
		}
		
		$this->render('yoyTrends');
	}
}

?>