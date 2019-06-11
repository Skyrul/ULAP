<?php

class SalesController extends Controller
{
	public $layout='//layouts/sales';
	
	private $excludeCustomerSql;
	
	public function init()
	{
		$this->excludeCustomerSql = CustomerSkill::model()->removeFromSalesReports();
	}

	public function filters()
	{
		
		return array(
			// 'accessControl', // perform access control for CRUD operations
			// 'postOnly + delete', // we only allow deletion via POST request
		);
	}
	
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
		);
	}

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{
		$salesDataCurrentEnrollValue = 0;
		
		$models = CustomerSkill::model()->findAll(array(
			'condition' => '
				MONTH(date_created) = MONTH(CURDATE()) 
				AND YEAR(date_created) = YEAR(CURDATE()) 
				AND date_created NOT BETWEEN "2016-12-22 05:03:14" AND "2016-12-22 05:03:32" 
				'.$this->excludeCustomerSql.'
			',
		));
		
		$todayModels = CustomerSkill::model()->findAll(array(
			'condition' => '
				MONTH(date_created) = MONTH(CURDATE()) 
				AND YEAR(date_created) = YEAR(CURDATE()) 
				AND DAY(date_created) = DAY(CURDATE())
				AND date_created NOT BETWEEN "2016-12-22 05:03:14" AND "2016-12-22 05:03:32"
				'.$this->excludeCustomerSql.'
			',
		));
		
		
		$previousWorkingDay = $this->getPreviousWorkingDay(date('Y'), date('m'), date('d'));
		
		$previousModels = CustomerSkill::model()->findAll(array(
			'condition' => '
				MONTH(date_created) = "'.date('m').'" 
				AND YEAR(date_created) = "'.date('Y').'"
				AND DAY(date_created) <= "'.$previousWorkingDay.'"
				'.$this->excludeCustomerSql.'
			',
		));
		
		##echo 'Previous Working Day: '. date('Y').'-'.date('m').'-'.$previousWorkingDay; 
		##echo '<br>';
		
		foreach($models as $model)
		{
			$customerContract = $this->getCustomerContractData($model);
			
			$salesDataCurrentEnrollValue = $salesDataCurrentEnrollValue + $customerContract['contractedAmount'];
			
		}
		
		$monthlyTeamGoal = SalesTeamMonthlyGoal::model()->findByPk(1);
		
		$salesData = array();
		$salesData['currentEnrollCount'] = count($models);
		$salesData['currentEnrollCountMonthlyGoal'] = $monthlyTeamGoal->sales_count;
		$salesData['currentEnrollCountRemaining'] = ($salesData['currentEnrollCountMonthlyGoal'] - $salesData['currentEnrollCount']);
		
		$salesData['todayEnrollCount'] = count($todayModels);
		
		$salesData['currentEnrollValue'] = number_format($salesDataCurrentEnrollValue,0);
		$salesData['currentEnrollValueMonthlyGoal'] = number_format($monthlyTeamGoal->sales_revenue,0);
		$salesData['currentEnrollValueRemaining'] =  number_format( ($monthlyTeamGoal->sales_revenue - $salesDataCurrentEnrollValue) , 0);
		
		$salesData['previousEnrollCount'] = count($previousModels);
		$salesData['previousEnrollCountRemaining'] = ($salesData['currentEnrollCountMonthlyGoal'] - $salesData['previousEnrollCount']);
		
		###Daily Pace ###
		
		
		$workingDays = $this->getMonthlyWorkingDays(date('Y'), date('m'));
		if(date('m') == 12)
			$workingDays = $workingDays - 1;
		
		##echo 'Working Days: '. $workingDays;
		##echo '<br>';
		
		$monthlyGoal = $monthlyTeamGoal->sales_count;
		
		##echo 'monthly goals: '. $monthlyGoal;
		##echo '<br>';
		
		$original = number_format($monthlyGoal / $workingDays, 1);
		
		
		##echo 'original: '. $original;
		##echo '<br>';
		
		$currentWorkingDayIsToday = $this->getCurrentWorkingDayIsToday(date('Y'), date('m'), date('d'));
		$currentWorkingDayIsYesterday = $this->getCurrentWorkingDayIsToday(date('Y'), date('m'), $previousWorkingDay);
		
		
		##echo 'current working day: '. $currentWorkingDayIsToday;
		##echo '<br>';
		
		##echo 'current working day is yesterday: '. $currentWorkingDayIsYesterday;
		##echo '<br>';
		
		##echo 'month to previous day enroll count: '. $salesData['previousEnrollCount'];
		##echo '<br>';
		
		##echo 'month to current day enroll count: '. $salesData['currentEnrollCount'];
		##echo '<br>';
		
		// $variance = ($original * $currentWorkingDayIsYesterday) - $salesData['previousEnrollCount'];
		$variance = ($original * $currentWorkingDayIsToday) - $salesData['currentEnrollCount'];
		
		if($salesData['currentEnrollCount'] > ($original * $currentWorkingDayIsToday) )
			$addNegativeSign = true;
		
		$varianceString = number_format( $variance , 0);
		
		if($addNegativeSign)
			$varianceString = number_format($varianceString * (-1) , 0);
			
		### new pace computation ### number of work days left divided by remaining enrollees needed for goal
		$remainingWorkingDay = ($workingDays - $currentWorkingDayIsYesterday);
		
		$salesData['remainingWorkingDay'] = $remainingWorkingDay;
		
		##echo 'working days left : '. $remainingWorkingDay;
		##echo '<br>';
		
		##echo 'previous enroll count remaining : '. $salesData['previousEnrollCountRemaining'];
		##echo '<br>';
		
		$newPace = number_format($salesData['currentEnrollCountRemaining'] / $remainingWorkingDay , 1);
		
		
		
		$salesData['dailyPaceOriginal'] = $original;
		$salesData['dailyPaceVariance'] = $varianceString;
		$salesData['dailyPaceNew'] = $newPace;
		
		###salesRep###
		$salesRepData = $this->getListOfSalesRep();
		
		##scrolling bottom - enrolment for today##
		$marqueeData = $this->getEnrollmentList();
		
		$this->render('index', array(
			'salesData' => $salesData,
			'salesRepData' => $salesRepData,
			'marqueeData' => $marqueeData,
		));
	}
	
	public function actionCheckSalesCount()
	{
		// $modelsCount = CustomerEnrollment::model()->count(array(
			// 'condition' => 'sales_management_deleted = 0 AND MONTH(date_created) = MONTH(CURDATE()) AND YEAR(date_created) = YEAR(CURDATE())',
			# 'order' => 'date_created DESC'
		// ));
		
		$modelsCount = CustomerSkill::model()->count(array(
			'condition' => '
				MONTH(date_created) = MONTH(CURDATE()) 
				AND YEAR(date_created) = YEAR(CURDATE()) 
				AND DAY(date_created) = DAY(CURDATE())
				AND date_created NOT BETWEEN "2016-12-22 05:03:14" AND "2016-12-22 05:03:32"
				'.$this->excludeCustomerSql.'
			',
		));
		
		echo CJSON::encode(array(
			'count' => $modelsCount
		));
		
		Yii::app()->end();
	}
	
	public function getEnrollmentList()
	{
		// $marqueeData = array(
			// array('a'=>'Valerie Strickland','b'=>'State Farm', 'c'=> 'John Doe - 445.00'),
			// array('a'=>'Ashley Paxton','b'=>'Farmers', 'c'=> 'Mary Jane - 360.00'),
			// array('a'=>'Alejandra Clark','b'=>'Safeco', 'c'=> 'Tom Collins - 445.00')
		// );
		
		$marqueeData = array();
		
		$todayModels = CustomerSkill::model()->findAll(array(
			'condition' => '
				MONTH(date_created) = MONTH(CURDATE()) 
				AND YEAR(date_created) = YEAR(CURDATE()) 
				AND DAY(date_created) = DAY(CURDATE())
				AND date_created NOT BETWEEN "2016-12-22 05:03:14" AND "2016-12-22 05:03:32"
				'.$this->excludeCustomerSql.'
			',
			'order' => 'date_created DESC'
		));
		
		foreach($todayModels as $model)
		{
			$customerContract = $this->getCustomerContractData($model);
			
			$marqueeData[$model->id]['a'] = $customerContract['salesReps'];
			$marqueeData[$model->id]['b'] = $customerContract['contract']->company_id;
			$marqueeData[$model->id]['c'] = $model->customer->firstname.' '.$model->customer->lastname.' - '.$customerContract['contractedAmount'];
			
			$marqueeData[$model->id]['salesRepID'] = $customerContract['salesRepID'];
			$marqueeData[$model->id]['salesRepName'] = $customerContract['salesRepName'];
			$marqueeData[$model->id]['companyName'] = $customerContract['contract']->company->company_name;
			$marqueeData[$model->id]['customerName'] = $model->customer->firstname.' '.$model->customer->lastname;
			$marqueeData[$model->id]['contractedAmount'] = $customerContract['contractedAmount'];
		}
		
		// exit;
			
		return $marqueeData;
	}
	
	public function getMonthlyWorkingDays($year, $month)
	{
		//FOR ENHANCEMENT USE -- function getWorkingDaysForThisMonth in Cron QUEUE Evalutation
		
		$ignore = array(0, 6); //sunday and saturday
		
			$count = 0;
			$counter = mktime(0, 0, 0, $month, 1, $year);
			
			while (date("n", $counter) == $month) {
				if (in_array(date("w", $counter), $ignore) == false) {
					$count++;
				}
				$counter = strtotime("+1 day", $counter);
			}
			return $count;
	}
	
	public function getCurrentWorkingDayIsToday($year, $month, $day)
	{
		$ignore = array(0, 6); //sunday and saturday
		
		$count = 0;
		$counter = mktime(0, 0, 0, $month, 1, $year);
		
		while (date("n", $counter) == $month && date("d", $counter) <= $day) {
			if (in_array(date("w", $counter), $ignore) == false) {
				$count++;
			}
			$counter = strtotime("+1 day", $counter);
		}
		
		return $count;
	}
	
	public function getPreviousWorkingDay($year, $month, $day)
	{
		$givenDate = date("Y-m-d", $year.'-'.$month.'-'.$day);
		$isMonday = false;
		$isFirstDay = false;
		
		if(date('D', $givenDate) === 'Mon') 
			$isMonday = true;
		
		if(date('j', $timestamp) === '1') 
			$isFirstDay = true;

		if(!$isFirstDay)
		{
			if(!$isMonday)
			{
				$previousWorkingDay = $day - 1;
			}
			else
			{
				$previousWorkingDay = $day - 3;
			}
		}
		
		return $previousWorkingDay;
	}
	
	public function getCustomerContractData($model)
	{
		$totalLeads = 0;
		$contractedAmount = 0;
		
		$customer = Customer::model()->find(array(
			'condition' => 'id = :customer_id',
			'params' => array(
				':customer_id' => $model->customer_id,
			),
		));

		if( $customer )
		{
			$selectedSalesReps = 0;
			$commissionRate = 0;
			$selectedSalesRepId = '';
			$selectedSalesRepName = '';
			
			$salesReps = CustomerSalesRep::model()->find(array(
				'condition' => 'customer_id = :customer_id',
				'params' => array(
					':customer_id' => $customer->id,
				),
				'order' => 'date_created DESC',
			));
			
			if( $salesReps )
			{
				// foreach( $salesReps as $salesRep )
				// {
					$selectedSalesReps = $salesReps->sales_rep_account_id;
					$selectedSalesRepId = isset($salesReps->account) ? $salesReps->account->id : '';
					$selectedSalesRepName = isset($salesReps->account) ? $salesReps->account->getFullName() : '';
					
					// $userMonthlyGoal = SalesAccountMonthlyGoal::model()->find(array(
						// 'condition' => 'account_id = :account_id',
						// 'params' => array(
							// ':account_id' => $salesRep->sales_rep_account_id,
						// ),
					// ));
					
					// if( $userMonthlyGoal )
					// {
						// $userCommissionRate = str_replace('%', '', $userMonthlyGoal->commission_rate);

						// $commissionRate = ($userCommissionRate / 100);
					// }
				// }
			}
			
			
			$customerSkill = CustomerSkill::model()->find(array(
				'condition' => '
					id = :model_id 
					AND customer_id = :customer_id 
					AND status=1 
					'.$this->excludeCustomerSql.'
				',
				'params' => array(
					':customer_id' => $customer->id,
					':model_id' => $model->id,
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
				}
			}
		
		}
		
		return array(
			'contract' => $contract,
			'totalLeads' => $totalLeads,
			'contractedAmount' => $contractedAmount,
			'salesReps' => $selectedSalesReps,
			'salesRepID' => $selectedSalesRepId,
			'salesRepName' => $selectedSalesRepName,
		);
	}
	
	public function getListOfSalesRep()
	{
		$workingDays = $this->getMonthlyWorkingDays(date('Y'), date('m'));

		
		$previousWorkingDay = $this->getPreviousWorkingDay(date('Y'), date('m'), date('d'));
		$currentWorkingDayIsToday = $this->getCurrentWorkingDayIsToday(date('Y'), date('m'), date('d'));
		$currentWorkingDayIsYesterday = $this->getCurrentWorkingDayIsToday(date('Y'), date('m'), $previousWorkingDay);
		
		
		$remainingWorkingDay = ($workingDays - $currentWorkingDayIsYesterday);
		
		$salesRepArray = AccountUser::listSalesAgents();
		$salesRepData = array();
		
		
		
		foreach($salesRepArray as $account_id => $salesRepName)
		{
			$salesRepData[$account_id] = array();
			$salesRepData[$account_id]['fullname'] = $salesRepName; 
			
		
			$userMonthlyGoal = SalesAccountMonthlyGoal::model()->find(array(
				'condition' => 'account_id = :account_id',
				'params' => array(
					':account_id' => $account_id,
				),
			));
			
			if( $userMonthlyGoal )
			{
				$salesRepData[$account_id]['monthly_goal'] = $userMonthlyGoal->sales_count;
				$salesRepData[$account_id]['monthly_stretch_goal'] = $userMonthlyGoal->stretch_count;
				
				if( is_numeric($userMonthlyGoal->sales_revenue) )
				{
					$salesRepData[$account_id]['sales_revenue'] = number_format($userMonthlyGoal->sales_revenue);
				}
				else
				{
					$salesRepData[$account_id]['sales_revenue'] = $userMonthlyGoal->sales_revenue;
				}
			}
			else
			{
				$salesRepData[$account_id]['monthly_goal'] = 0;
				$salesRepData[$account_id]['monthly_stretch_goal'] = 0;
				$salesRepData[$account_id]['sales_revenue'] = 0;
			}
			
			// $customerEnrollmentGroupCount = CustomerEnrollment::model()->count(array(
				// 'select'=>'t.*',
				// 'condition' => 't.sales_management_deleted = 0 AND MONTH(t.date_created) = MONTH(CURDATE()) AND YEAR(t.date_created) = YEAR(CURDATE()) AND t.sales_rep_account_id = :sales_rep_id', 
				// 'join' => 'LEFT JOIN `ud_customer_sales_rep` csr ON t.account_id = csr.customer_id',
				#'group' => 'csr.sales_rep_account_id',
				// 'params'=>array(
					// ':sales_rep_id' => $account_id
				// ),
			// ));
			
			
			$customerEnrollmentGroupCount = CustomerSkill::model()->count(array(
				'select'=>'t.*',
				'condition' => '
					MONTH(t.date_created) = MONTH(CURDATE()) 
					AND YEAR(t.date_created) = YEAR(CURDATE()) 
					AND csr.sales_rep_account_id = :sales_rep_id 
					AND t.date_created NOT BETWEEN "2016-12-22 05:03:14" AND "2016-12-22 05:03:32" 
					'.$this->excludeCustomerSql.'
				', 
				'join' => 'LEFT JOIN `ud_customer_sales_rep` csr ON t.customer_id = csr.customer_id',
				// 'group' => 'csr.sales_rep_account_id',
				'params'=>array(
					':sales_rep_id' => $account_id
				),
			));

			$previousCustomerEnrollmentGroupCount = CustomerSkill::model()->count(array(
				'select'=>'t.*',
				'condition' => 'MONTH(t.date_created) = "'.date('m').'" 
					AND YEAR(t.date_created) = "'.date('Y').'" 
					AND DAY(t.date_created) <= "'.$previousWorkingDay.'"
					AND csr.sales_rep_account_id = :sales_rep_id
					AND t.date_created NOT BETWEEN "2016-12-22 05:03:14" AND "2016-12-22 05:03:32"
					'.$this->excludeCustomerSql.'
				', 
				'join' => 'LEFT JOIN `ud_customer_sales_rep` csr ON t.customer_id = csr.customer_id',
				'params'=>array(
					':sales_rep_id' => $account_id
				),
			));
			
			$salesRepData[$account_id]['sales_month_to_date'] = $customerEnrollmentGroupCount;
			
			##daily pace##
			
			$original = number_format( $salesRepData[$account_id]['monthly_goal'] / $workingDays, 1);
			
			
			$variance = ($original * $currentWorkingDayIsToday) - $customerEnrollmentGroupCount;
			// if($variance > 0)
			if($customerEnrollmentGroupCount > ($original * $currentWorkingDayIsToday) )
				$addNegativeSign = true;
			
			$varianceString = number_format( $variance , 0);
			
			if($addNegativeSign)
				$varianceString = number_format($varianceString * (-1) , 0);
			
			
			$currentEnrollCountRemaining = $salesRepData[$account_id]['monthly_goal'] - $customerEnrollmentGroupCount;
			$newPace = number_format($currentEnrollCountRemaining / $remainingWorkingDay , 1);
			
			
			$salesRepData[$account_id]['salesRepDailyPaceOriginal'] = $original;
			$salesRepData[$account_id]['salesRepDailyPaceVariance'] = $varianceString;
			$salesRepData[$account_id]['salesRepDailyPaceNew'] = $newPace;
			
			##get the salesRep daily enrollees##
			$customerEnrollmentGroupDailys = CustomerSkill::model()->findAll(array(
				// 'select'=>'count(t.date_created) as id, DAY(t.date_created) as date_created, t.customer_id as customer_id, t.contract_id as contract_id, t.skill_id as skill_id',
				'select'=>'t.*',
				'condition' => '
					MONTH(t.date_created) = MONTH(CURDATE()) 
					AND YEAR(t.date_created) = YEAR(CURDATE()) 
					AND csr.sales_rep_account_id = :sales_rep_id 
					AND t.date_created NOT BETWEEN "2016-12-22 05:03:14" AND "2016-12-22 05:03:32" 
					'.$this->excludeCustomerSql.'
				', 
				'join' => 'LEFT JOIN `ud_customer_sales_rep` csr ON t.customer_id = csr.customer_id',
				'group' => 'DATE(t.date_created)',
				'params'=>array(
					':sales_rep_id' => $account_id
				),
			));
			
			$customerEnrollmentMonthToDate = CustomerSkill::model()->findAll(array(
				// 'select'=>'count(t.date_created) as id, DAY(t.date_created) as date_created, t.customer_id as customer_id, t.contract_id as contract_id, t.skill_id as skill_id',
				'select'=>'t.*',
				'condition' => '
					MONTH(t.date_created) = MONTH(CURDATE()) 
					AND YEAR(t.date_created) = YEAR(CURDATE()) 
					AND csr.sales_rep_account_id = :sales_rep_id 
					AND t.date_created NOT BETWEEN "2016-12-22 05:03:14" AND "2016-12-22 05:03:32" 
					'.$this->excludeCustomerSql.'
				', 
				'join' => 'LEFT JOIN `ud_customer_sales_rep` csr ON t.customer_id = csr.customer_id',
				'params'=>array(
					':sales_rep_id' => $account_id
				),
			));
			
			$customerEnrollmentToday = CustomerSkill::model()->findAll(array(
				// 'select'=>'count(t.date_created) as id, DAY(t.date_created) as date_created, t.customer_id as customer_id, t.contract_id as contract_id, t.skill_id as skill_id',
				'select'=>'t.*',
				'condition' => '
					MONTH(t.date_created) = MONTH(CURDATE()) 
					AND YEAR(t.date_created) = YEAR(CURDATE()) 
					AND DAY(t.date_created) = DAY(CURDATE()) 
					AND csr.sales_rep_account_id = :sales_rep_id 
					AND t.date_created NOT BETWEEN "2016-12-22 05:03:14" AND "2016-12-22 05:03:32" 
					'.$this->excludeCustomerSql.'
				', 
				'join' => 'LEFT JOIN `ud_customer_sales_rep` csr ON t.customer_id = csr.customer_id',
				'params'=>array(
					':sales_rep_id' => $account_id
				),
			));
			
			$monthDays = range(1, date('t',strtotime(time())));
			$countMonthDays = count($monthDays);
			
			$monthlyGoalProduct = round($salesRepData[$account_id]['monthly_goal'] / $countMonthDays, 2);
			$monthlyStretchProduct = round($salesRepData[$account_id]['monthly_stretch_goal'] / $countMonthDays, 2);
			
			$salesRepDaySales = array();
			$salesRepGoalSales = array();
			$salesRepStretchSales = array();
			
			$salesRepGoalSales[1] = $monthlyGoalProduct;
			$salesRepStretchSales[1] = $monthlyStretchProduct;
			
			
			
			$previousGoalSum = 0;
			$previousStretchSum = 0;
			
			foreach($monthDays as $days)
			{
				if(!isset($salesRepDaySales[$days]))
				{
					$salesRepDaySales[$days] = 0;
				}
				
				$salesRepGoalSales[$days] = $previousGoalSum + $monthlyGoalProduct;
				$previousGoalSum = $salesRepGoalSales[$days];
				
				$salesRepStretchSales[$days] = $previousStretchSum + $monthlyStretchProduct;
				$previousStretchSum = $salesRepStretchSales[$days];
			}
			
			// if( $customerEnrollmentGroupDailys )
			// {
				// foreach($customerEnrollmentGroupDailys as $customerEnrollmentGroupDaily)
				// {
					// $salesRepDaySales[$customerEnrollmentGroupDaily->date_created] = (int)$customerEnrollmentGroupDaily->id;
				// }
			// }
			
			$monthToDate = 0;
			
			if( $customerEnrollmentMonthToDate )
			{	
				foreach( $customerEnrollmentMonthToDate as $customerSkill )
				{
					$contractedAmount = 0;
						
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
						
						$monthToDate += $contractedAmount;
					}
				}
			}
			
			$salesRepData[$account_id]['monthToDate'] = number_format($monthToDate);
			
			
			$todaySales = 0;
			
			if( $customerEnrollmentToday )
			{	
				foreach( $customerEnrollmentToday as $customerSkill )
				{
					$contractedAmount = 0;
						
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
						
						$todaySales += $contractedAmount;
					}
				}
			}
			
			$salesRepData[$account_id]['todaySalesCount'] = count($customerEnrollmentToday);
			$salesRepData[$account_id]['todaySales'] = number_format($todaySales);
			
			
			
			$previousDailySum = 0;
			$salesRepDaySalesSum = array();
			
			foreach($salesRepDaySales as $days => $daySales)
			{
				if($days  <= date("d"))
				{
					$salesRepDaySalesSum[$days] = $previousDailySum + $daySales;
					
					$previousDailySum = $salesRepDaySalesSum[$days];
				}
			}
			
			$salesRepData[$account_id]['dailySales'] = $salesRepDaySalesSum;
			$salesRepData[$account_id]['goalSales'] = $salesRepGoalSales;
			$salesRepData[$account_id]['stretchSales'] = $salesRepStretchSales; 
		}
		
		// echo '<pre>';
		// print_r($salesRepData);
		// exit;
		return $salesRepData;
	}

	public function actionView()
	{
		$salesDataCurrentEnrollValue = 0;
		
		$models = CustomerSkill::model()->findAll(array(
			'condition' => '
				MONTH(date_created) = MONTH(CURDATE()) 
				AND YEAR(date_created) = YEAR(CURDATE()) 
				AND date_created NOT BETWEEN "2016-12-22 05:03:14" 
				AND "2016-12-22 05:03:32"
				'.$this->excludeCustomerSql.'
			',
		));
		
		$todayModels = CustomerSkill::model()->findAll(array(
			'condition' => '
				MONTH(date_created) = MONTH(CURDATE()) 
				AND YEAR(date_created) = YEAR(CURDATE()) 
				AND DAY(date_created) = DAY(CURDATE())
				AND date_created NOT BETWEEN "2016-12-22 05:03:14" AND "2016-12-22 05:03:32"
				'.$this->excludeCustomerSql.'
			',
		));
		
		
		$previousWorkingDay = $this->getPreviousWorkingDay(date('Y'), date('m'), date('d'));
		
		$previousModels = CustomerSkill::model()->findAll(array(
			'condition' => '
				MONTH(date_created) = "'.date('m').'" 
				AND YEAR(date_created) = "'.date('Y').'"
				AND DAY(date_created) <= "'.$previousWorkingDay.'"
				'.$this->excludeCustomerSql.'
			',
		));
		
		##echo 'Previous Working Day: '. date('Y').'-'.date('m').'-'.$previousWorkingDay; 
		##echo '<br>';
		
		foreach($models as $model)
		{
			$customerContract = $this->getCustomerContractData($model);
			
			$salesDataCurrentEnrollValue = $salesDataCurrentEnrollValue + $customerContract['contractedAmount'];
			
		}
		
		$monthlyTeamGoal = SalesTeamMonthlyGoal::model()->findByPk(1);
		
		$salesData = array();
		$salesData['currentEnrollCount'] = count($models);
		$salesData['currentEnrollCountMonthlyGoal'] = $monthlyTeamGoal->sales_count;
		$salesData['currentEnrollCountRemaining'] = ($salesData['currentEnrollCountMonthlyGoal'] - $salesData['currentEnrollCount']);
		
		$salesData['todayEnrollCount'] = count($todayModels);
		
		$salesData['currentEnrollValue'] = number_format($salesDataCurrentEnrollValue,0);
		$salesData['currentEnrollValueMonthlyGoal'] = number_format($monthlyTeamGoal->sales_revenue,0);
		$salesData['currentEnrollValueRemaining'] =  number_format( ($monthlyTeamGoal->sales_revenue - $salesDataCurrentEnrollValue) , 0);
		
		$salesData['previousEnrollCount'] = count($previousModels);
		$salesData['previousEnrollCountRemaining'] = ($salesData['currentEnrollCountMonthlyGoal'] - $salesData['previousEnrollCount']);
		
		###Daily Pace ###
		
		
		$workingDays = $this->getMonthlyWorkingDays(date('Y'), date('m'));
		
		if( in_array(date('m'), array(5,12)) )
		{
			if( date('m') == 12 ) //2017 rule
			{
				$workingDays = $workingDays - 3;
			}
			else
			{
				$workingDays = $workingDays - 1;
			}
		}
		
		if( date('m') == 1 ) //2018 jan rule
		{
			$workingDays = $workingDays - 2;
		}
		
		##echo 'Working Days: '. $workingDays;
		##echo '<br>';
		
		$monthlyGoal = $monthlyTeamGoal->sales_count;
		
		##echo 'monthly goals: '. $monthlyGoal;
		##echo '<br>';
		
		$original = number_format($monthlyGoal / $workingDays, 1);
		
		
		##echo 'original: '. $original;
		##echo '<br>';
		
		$currentWorkingDayIsToday = $this->getCurrentWorkingDayIsToday(date('Y'), date('m'), date('d'));
		$currentWorkingDayIsYesterday = $this->getCurrentWorkingDayIsToday(date('Y'), date('m'), $previousWorkingDay);
		
		
		##echo 'current working day: '. $currentWorkingDayIsToday;
		##echo '<br>';
		
		##echo 'current working day is yesterday: '. $currentWorkingDayIsYesterday;
		##echo '<br>';
		
		##echo 'month to previous day enroll count: '. $salesData['previousEnrollCount'];
		##echo '<br>';
		
		##echo 'month to current day enroll count: '. $salesData['currentEnrollCount'];
		##echo '<br>';
		
		// $variance = ($original * $currentWorkingDayIsYesterday) - $salesData['previousEnrollCount'];
		$variance = ($original * $currentWorkingDayIsToday) - $salesData['currentEnrollCount'];
		
		if($salesData['currentEnrollCount'] > ($original * $currentWorkingDayIsToday) )
			$addNegativeSign = true;
		
		$varianceString = number_format( $variance , 0);
		
		if($addNegativeSign)
			$varianceString = number_format($varianceString * (-1) , 0);
			
		### new pace computation ### number of work days left divided by remaining enrollees needed for goal
		$remainingWorkingDay = ($workingDays - $currentWorkingDayIsYesterday);
		
		if( date('m') == 5 ) //2018 jan rule
		{
			$remainingWorkingDay = $remainingWorkingDay + 1;
		}
		
		if( $remainingWorkingDay == 0 )
		{
			$remainingWorkingDay = 1;
		}
		
		$salesData['workingDays'] = $workingDays;
		$salesData['remainingWorkingDay'] = $remainingWorkingDay;
		$salesData['daysWorked'] = $workingDays - $remainingWorkingDay;
		
		if( $salesData['daysWorked'] == 0 )
		{
			$salesData['daysWorked'] = 1;
		}
		
		if( $salesDataCurrentEnrollValue > 0 )
		{
			$salesData['pacingToAmount'] = number_format( (($salesDataCurrentEnrollValue / $salesData['daysWorked'] ) * $remainingWorkingDay) + $salesDataCurrentEnrollValue, 0 );
		}
		else
		{
			$salesData['pacingToAmount'] = 0;
		}
		
		if( $salesDataCurrentEnrollValue > 0 )
		{
			$salesData['toAchieveGoalAmount'] = number_format( ($monthlyTeamGoal->sales_revenue - $salesDataCurrentEnrollValue) / $salesData['remainingWorkingDay']);
		}
		else
		{
			$salesData['toAchieveGoalAmount'] = number_format( $monthlyTeamGoal->sales_revenue );
		}

		if( $salesDataCurrentEnrollValue > 0 )
		{
			$salesData['monthToDateAmount'] = number_format($salesDataCurrentEnrollValue / $salesData['daysWorked']);
		}
		else
		{
			$salesData['monthToDateAmount'] = number_format($salesDataCurrentEnrollValue);
		}

		##echo 'working days left : '. $remainingWorkingDay;
		##echo '<br>';
		
		##echo 'previous enroll count remaining : '. $salesData['previousEnrollCountRemaining'];
		##echo '<br>';

		$newPace = number_format($salesData['currentEnrollCountRemaining'] / $remainingWorkingDay , 1);
		
		$salesData['dailyPaceOriginal'] = $original;
		$salesData['dailyPaceVariance'] = $varianceString;
		$salesData['dailyPaceNew'] = $newPace;
		
		###salesRep###
		$salesRepData = $this->getListOfSalesRep();
		
		##scrolling bottom - enrolment for today##
		$marqueeData = $this->getEnrollmentList();
		
		// $page = 'view';
		
		// if( isset($_GET['test']) )
		// {
			$page = 'view2';
		// }
		
		$this->render($page, array(
			'salesData' => $salesData,
			'salesRepData' => $salesRepData,
			'marqueeData' => $marqueeData,
		));
	}
}