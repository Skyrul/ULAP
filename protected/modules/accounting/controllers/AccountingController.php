<?php 

ini_set('memory_limit', '1000M');
set_time_limit(0);

class AccountingController extends Controller
{
	
	public function actionIndex()
	{
		$payPeriodOptions = array();
		
		foreach( range(2015, 2018) as $year)
		{
			foreach( range( $year == 2015 ? 11 : 1 , 12) as $monthNumber )
			{
				$firstDayOfMonth = date('M d', strtotime($year.'-'.$monthNumber.'-01'));
				$fifteenthDayOfMonth = date('M d', strtotime($year.'-'.$monthNumber.'-16'));
				$lastDayOfMonth = date('d', strtotime('last day of this month', strtotime($firstDayOfMonth)));
				
				$payPeriodOptions[] = $firstDayOfMonth.' - 15 '.$year;
				$payPeriodOptions[] = $fifteenthDayOfMonth.' - '.$lastDayOfMonth.' '.$year;
			}
		}
		
		if( date('d') < 16 )
		{
			$currentPayPeriod =  date('M').' 01 - 15 ' . date('Y');
		}
		else
		{
			$currentPayPeriod = date('M').' 16 - '.date('d', strtotime('last day of this month')) .' '. date('Y');
		}
		
		$currentPayPeriod = array_search( $currentPayPeriod, $payPeriodOptions  );

		
		$this->render('index', array(
			'payPeriodOptions' => $payPeriodOptions,
			'currentPayPeriod' => $currentPayPeriod,
		));
	}
	
	public function actionExportPayrollFile($filter)
	{
		// unregister Yii's autoloader
		spl_autoload_unregister(array('YiiBase', 'autoload'));
		
		// register PHPExcel's autoloader ... PHPExcel.php will do it
		$phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
		require_once($phpExcelPath . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'PHPExcel.php');
		
		// register Yii's autoloader again
		spl_autoload_register(array('YiiBase', 'autoload'));
		 
		// This requires Yii's autoloader
		
		$payPeriodOptions = array();
		
		foreach( range(2015, 2018) as $year)
		{
			foreach( range( $year == 2015 ? 11 : 1 , 12) as $monthNumber )
			{
				$firstDayOfMonth = date('M d', strtotime($year.'-'.$monthNumber.'-01'));
				$fifteenthDayOfMonth = date('M d', strtotime($year.'-'.$monthNumber.'-16'));
				$lastDayOfMonth = date('d', strtotime('last day of this month', strtotime($firstDayOfMonth)));
				
				$payPeriodOptions[] = $firstDayOfMonth.' - 15 '.$year;
				$payPeriodOptions[] = $fifteenthDayOfMonth.' - '.$lastDayOfMonth.' '.$year;
			}
		}
		
		$objPHPExcel = new PHPExcel();
		
		$objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Pay Period: ' . $payPeriodOptions[$filter] );
		
		$ctr = 3;
		
		
		
		$headers = array(
			'A' => 'Co Code',
			'B' => 'Batch ID',
			'C' => 'File #',
			'D' => 'Name',
			'E' => 'Full Time Status',
			'F' => 'Scheduled to work',
			'G' => 'Regular Hours',
			'H' => 'O/T Hours',
			'I' => 'Hours 3 Code',
			'J' => 'Hours 3 Amount',	 
			'K' => 'Hours 3 Code',	 
			'L' => 'Hours 3 Amount',	 
			'M' => 'Hours 3 Code',	
			'N' => 'Hours 3 Amount',	 
			'O' => 'Earnings 3 Code', 
			'P' => 'Earnings 3 Amount',	 
			'Q' => 'Earnings 3 Code',	 
			'R' => 'Earnings 3 Amount',
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
	
		
		$explodedDilterDate = explode(' - ', $payPeriodOptions[$filter]);
		$explodedElement1 = explode(' ', $explodedDilterDate[0]);
		$explodedElement2 = explode(' ', $explodedDilterDate[1]);

		$month = $explodedElement1[0];
		$start_day = $explodedElement1[1];
		$end_day = $explodedElement2[0];
		$year = $explodedElement2[1];
		$filterYear = $year;
		
		$payPeriodStartDate =  $year.'-'.$month.'-'.$start_day;
		$payPeriodEndDate =  $year.'-'.$month.'-'.$end_day;
		
		$payDayWeeks = array();
		
		$startDate = strtotime($payPeriodStartDate);
		$endDate = strtotime($payPeriodEndDate);
		
		while( $startDate <= $endDate )
		{
			// if( !in_array(date('W', $startDate), $payDayWeeks) )
			// {
				// $payDayWeeks[] = date('W', $startDate);
			// }
			
			// if( date('l', $startDate) == 'Sunday' )
			// {
				// $payDayWeeks[] = date('Y-m-d', $startDate);
			// }
			
			// echo date('Y-m-d', $startDate).' => '.date('Y-m-d', strtotime('sunday last week', $startDate));
			// echo '<br>';
			
			if( !in_array(date('Y-m-d', strtotime('sunday last week', $startDate)), $payDayWeeks) )
			{
				$payDayWeeks[] = date('Y-m-d', strtotime('sunday last week', $startDate));
			}
			
			$startDate = strtotime('+1 day', $startDate);
		}
		
		// echo '<pre>';
			// print_r($payDayWeeks);
		// exit;
		
		$accounts = Account::model()->findAll(array(
			'together' => true,
			'with' => 'accountUser',
			'condition' => 'accountUser.id IS NOT NULL AND t.id NOT IN (4, 5, 294, 295, 296, 1635, 49, 2) AND t.account_type_id NOT IN (15) AND accountUser.full_time_status != "SYSTEM"',
			// 'condition' => 't.id IN (106, 2881, 119, 112, 117)',
			// 'condition' => 't.id IN (4271)',
		));
		
		//  AND t.id IN ("162")"1194", 
		
		if( $accounts )
		{		
			$ctr = 4;
			
			foreach( $accounts as $account )
			{
				$includeInFile = true;
				
				$totalRegularTime = 0;
				$totalOverTime = 0;
				$ptoHours = 0;
				$ptoHours = 0;
				$scheduledToWork = 0;
				
				$accountPtoRequests = AccountPtoRequest::model()->findAll(array(
					'condition' => '
						account_id = :account_id
						AND STR_TO_DATE(request_date, "%m/%d/%Y") >= :start_date 
						AND STR_TO_DATE(request_date, "%m/%d/%Y") <= :end_date
						AND status = 1
					',
					'params' => array(
						':account_id' => $account->id,
						':start_date' => date('Y-m-d', strtotime($payPeriodStartDate)),
						':end_date' => date('Y-m-d', strtotime($payPeriodEndDate)),
					),
				));
				
				if( $accountPtoRequests )
				{
					foreach( $accountPtoRequests as $accountPtoRequest )
					{
						$subtractTime = strtotime($accountPtoRequest->end_time) - strtotime($accountPtoRequest->start_time);

						$ptoHours += ($subtractTime/(60*60)) % 24;
					}
				}
				
				if( $ptoHours == 0 )
				{
					$ptoHours = '';
				}
				
				$startDate = strtotime($payPeriodStartDate);
				$endDate = strtotime($payPeriodEndDate);
				
				while( $startDate <= $endDate )
				{
					$daySchedules = AccountLoginSchedule::model()->findAll(array(
						'condition' => 'account_id = :account_id AND day_name = :day_name',
						'params' => array(
							':account_id' => $account->id,
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
								$scheduledToWork += $hours;
							}
						}
					}
					
					$startDate = strtotime('+1 day', $startDate);
				}
				
				// echo 'getFullName: ' . $account->accountUser->getFullName();
					
				// echo '<br>';
				
				// echo 'scheduledToWork: ' . $scheduledToWork;
				
				// echo '<br>';
				
				// echo 'salary_type: ' . $account->accountUser->salary_type;
				
				// echo '<br>';
				
				// echo 'full_time_status: ' . $account->accountUser->full_time_status;
				
				// echo '<br>';
				// echo '<br>';
				
				if( $account->accountUser->salary_type == 'HOURLY' )
				{
					// echo 'payDayWeeks: ';
					// echo '<br>';
					
					foreach( $payDayWeeks as $payDayWeek )
					{
						// $payDayWeekDates = $this->getStartAndEndDate($payDayWeek, $filterYear);		
						// $payDayWeekStart = date('Y-m-d 00:00:00', strtotime($payDayWeekDates['week_start']));
						// $payDayWeekEnd = date('Y-m-d 23:59:59', strtotime($payDayWeekDates['week_end']));
						
						// if( strtotime($payDayWeekDates['week_start']) < strtotime($payPeriodStartDate) )
						// {
							// $payDayWeekStart = date('Y-m-d 00:00:00', strtotime($payPeriodStartDate));
						// }
						
						$payDayWeekStart = date('Y-m-d 00:00:00', strtotime($payDayWeek));
					
						if( strtotime($payDayWeek) < strtotime($payPeriodStartDate) )
						{
							$payDayWeekStart = date('Y-m-d 00:00:00', strtotime($payPeriodStartDate));
						}
					
						$payDayWeekEnd = date('Y-m-d 23:59:59', strtotime('+6 days', strtotime($payDayWeek)));
						
						if( strtotime($payDayWeekEnd) > strtotime($payPeriodEndDate) )
						{
							$payDayWeekEnd = date('Y-m-d 23:59:59', strtotime($payPeriodEndDate));
						}

						// echo '<br>';
						
						// echo 'payDayWeekStart: ' . $payDayWeekStart;
						
						// echo '<br>';
						
						// echo 'payDayWeekEnd: ' . $payDayWeekEnd;
						
						// echo '<br>';
						
						// exit;
						
						$loginRecordSql = "
							SELECT SUM(TIME_TO_SEC(TIMEDIFF(time_out, time_in))/3600) AS total_login_time
							FROM ud_account_login_tracker
							WHERE account_id = '".$account->id."'
							AND time_in >= '".$payDayWeekStart."' 
							AND time_in <= '".$payDayWeekEnd."' 
							AND status=1
						";

						$loginRecords = Yii::app()->db->createCommand($loginRecordSql)->queryRow();
						
						$regularTime = $loginRecords['total_login_time'];
						
						$totalRegularTime += $regularTime;
						
						//overtime calc
						$payDayWeekStart = date('Y-m-d 00:00:00', strtotime($payDayWeek));
					
						$payDayWeekEnd = date('Y-m-d 23:59:59', strtotime('+6 days', strtotime($payDayWeek)));
						
						$loginRecordSql = "
							SELECT SUM(TIME_TO_SEC(TIMEDIFF(time_out, time_in))/3600) AS total_login_time
							FROM ud_account_login_tracker
							WHERE account_id = '".$account->id."'
							AND time_in >= '".$payDayWeekStart."' 
							AND time_in <= '".$payDayWeekEnd."' 
							AND status=1
						";
						
						$overTime = 0;
						
						if( $regularTime > 40 )
						{
							$overTime = $regularTime - 40;
							// $regularTime = 40;
						}
						
						$totalOverTime += $overTime;
						
						// echo '<br>';
						
						// echo 'regularTime: ' . round($regularTime, 2);

						// echo '<br>';
						
						// echo 'overTime: ' . round($overTime, 2);
						
						// echo '<br>';
						// echo '<br>';
					}
				}
				
				// echo 'totalRegularTime: ' . round($totalRegularTime, 2);
				
				// echo '<br>';
				
				// echo 'totalOverTime: ' . round($totalOverTime, 2);
				
				// echo '<br>';
				
				// echo '<br>';
				// echo '<hr>';
				// echo '<br>';
				
				// exit;
				
				
				if( $account->status == 2 && $totalRegularTime == 0 )
				{
					$includeInFile = false;
				}

				if( $includeInFile )
				{
					$employeeNumber = (string) $account->accountUser->employee_number;
				
					$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, 'APW');
				
					$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, date('md') . substr(date('Y'), -3) );
					
					$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $employeeNumber);
					$objPHPExcel->getActiveSheet()->getStyle('C'.$ctr)->getNumberFormat()->setFormatCode('000000');
					
					$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $account->accountUser->getFullName() );
					
					$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $account->accountUser->full_time_status );
					
					$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $scheduledToWork);	
					
					$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, round($totalRegularTime, 2) );
					
					$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, round($totalOverTime, 2) );
					
					$objPHPExcel->getActiveSheet()->SetCellValue('I'.$ctr, 'DT');
					
					$objPHPExcel->getActiveSheet()->SetCellValue('J'.$ctr, '');
					
					$objPHPExcel->getActiveSheet()->SetCellValue('K'.$ctr, 'HOL');
					
					$objPHPExcel->getActiveSheet()->SetCellValue('L'.$ctr, '');
					
					$objPHPExcel->getActiveSheet()->SetCellValue('M'.$ctr, 'PTO');
					
					$objPHPExcel->getActiveSheet()->SetCellValue('N'.$ctr, $ptoHours);
					
					$objPHPExcel->getActiveSheet()->SetCellValue('O'.$ctr, 'COM');
					
					$objPHPExcel->getActiveSheet()->SetCellValue('P'.$ctr, '');
					
					$objPHPExcel->getActiveSheet()->SetCellValue('Q'.$ctr, 'BON');	
					
					$ctr++;
				}
			}
		}
		
		// exit;

		header('Content-Type: application/vnd.ms-excel'); 
		header('Content-Disposition: attachment; filename="Pay Roll File.xlsx"'); 
		header('Cache-Control: max-age=0');
		
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
	}
	
	
	//time keeping tab
	public function actionTimeKeeping($filter='')
	{
		$payPeriodOptions = array();
		
		foreach( range(2015, 2018) as $year)
		{
			foreach( range( $year == 2015 ? 11 : 1 , 12) as $monthNumber )
			{
				$firstDayOfMonth = date('M d', strtotime($year.'-'.$monthNumber.'-01'));
				$fifteenthDayOfMonth = date('M d', strtotime($year.'-'.$monthNumber.'-16'));
				$lastDayOfMonth = date('d', strtotime('last day of this month', strtotime($firstDayOfMonth)));
				
				$payPeriodOptions[] = $firstDayOfMonth.' - 15 '.$year;
				$payPeriodOptions[] = $fifteenthDayOfMonth.' - '.$lastDayOfMonth.' '.$year;
			}
		}
		
		if( $filter != '')
		{
			$explodedDilterDate = explode(' - ', $payPeriodOptions[$filter]);
			$explodedElement1 = explode(' ', $explodedDilterDate[0]);
			$explodedElement2 = explode(' ', $explodedDilterDate[1]);

			$month = $explodedElement1[0];
			$start_day = $explodedElement1[1];
			$end_day = $explodedElement2[0];
			$year = $explodedElement2[1];
			
			$startDate =  $year.'-'.$month.'-'.$start_day;
			$endDate =  $year.'-'.$month.'-'.$end_day;
			
			$payPeriods = AccountLoginTracker::model()->findAll(array(
				'with' => 'account',
				'condition' => 'account.account_type_id IN (2,4) t.status!=1 AND t.date_created >= :start_date AND t.date_created <= :end_date AND DATE(t.date_created) >= "2016-04-01" AND t.status!=4',
				'params' => array(
					':start_date' => date('Y-m-d', strtotime($startDate)),
					':end_date' => date('Y-m-d', strtotime($endDate)),
				),
				'order' => 't.status ASC, t.date_created DESC',
			));
		}
		else
		{ 
			$payPeriods = AccountLoginTracker::model()->findAll(array(
				'with' => 'account',
				'condition' => 'account.account_type_id IN (2,4) AND t.status!=1 AND DATE(t.date_created) >= "2016-04-01" AND t.status!=4',
				'order' => 't.status ASC, t.date_created DESC',
			));
		}
		
		
		$payPeriodDataProvider = new CArrayDataProvider($payPeriods, array(
			'pagination' => array(
				'pageSize' => 50,
			),
		));

		$this->render('timeKeeping', array(
			'payPeriodDataProvider' => $payPeriodDataProvider,
			'payPeriodOptions' => $payPeriodOptions,
		));
	}
	
	//time keeping tab - pay period
	public function actionPayPeriodVarianceAction()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => '',
		);

		
		if( isset($_POST['id']) )
		{
			$model = AccountLoginTracker::model()->findByPk($_POST['id']);
			
			if( isset($_POST['AccountLoginTracker']) )
			{
				$model->attributes = $_POST['AccountLoginTracker'];
				$model->status = $_POST['status'];
				
				if( $model->save(false) )
				{
					$result['status'] = 'success';
				}
			}
			
			$html = $this->renderPartial('ajax_pay_period_variance_form', array(
				'model' => $model,
			), true);

			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}
	
	public function actionMergeVariance()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		if( isset($_POST['Ids']) )
		{
			$firstRecord = AccountLoginTracker::model()->find(array(
				'condition' => 'id IN ('.implode(', ', $_POST['Ids']).')',
				'order' => 'id ASC',
			));
			
			$lastRecord = AccountLoginTracker::model()->find(array(
				'condition' => 'id IN ('.implode(', ', $_POST['Ids']).')',
				'order' => 'id DESC',
			));
			
			if( $firstRecord && $lastRecord )
			{
				// if( $lastRecord->type == 1)
				// {
					// $timeOut = new DateTime($lastRecord->time_out, new DateTimeZone('America/Denver'));
					// $timeOut->setTimezone(new DateTimeZone('America/Chicago'));		
				// }
				// else
				// {
					$timeOut = new DateTime($lastRecord->time_out);
				// }	
				
				$firstRecord->setAttributes(array(
					'time_out' => $timeOut->format('Y-m-d H:i:s'),
					'login_session_token' => $lastRecord->login_session_token,
					'status' => 1,
					'type' => $lastRecord->type,
				));
				
				if( $firstRecord->save(false) )
				{
					$models = AccountLoginTracker::model()->findAll(array(
						'condition' => 'id IN ('.implode(', ', $_POST['Ids']).')',
					));
					
					if( $models )
					{ 
						foreach( $models as $model )
						{
							if( $firstRecord->id != $model->id )
							{
								$model->delete();
							}
						}
						
						$result['status'] = 'success';
					}
				}
			}
		}
		
		echo json_encode($result);
	}
	
	
	public function actionEditVariance()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);

		
		if( isset($_POST['id']) )
		{
			$model = AccountLoginTracker::model()->findByPk($_POST['id']);
			
			if( isset($_POST['AccountLoginTracker']) )
			{
				$model->attributes = $_POST['AccountLoginTracker'];
				
				$model->time_in = date('Y-m-d', strtotime($_POST['AccountLoginTracker']['time_in_date'])).' '.date('H:i:s', strtotime($_POST['AccountLoginTracker']['time_in_time']));
				$model->time_out = date('Y-m-d', strtotime($_POST['AccountLoginTracker']['time_out_date'])).' '.date('H:i:s', strtotime($_POST['AccountLoginTracker']['time_out_time']));
				
				$timeIn = new DateTime($model->time_in, new DateTimeZone('America/Denver'));
				$timeIn->setTimezone(new DateTimeZone('America/Chicago'));	

				if( $model->type == 1)
				{
					$timeOut = new DateTime($model->time_out, new DateTimeZone('America/Denver'));
					$timeOut->setTimezone(new DateTimeZone('America/Chicago'));		
				}
				else
				{
					$timeOut = new DateTime($model->time_out);
				}	
				
				$model->time_in = $timeIn->format('Y-m-d H:i:s');
				$model->time_out = $timeOut->format('Y-m-d H:i:s');
				
				// $model->type = 2;
				
				if( $model->save(false) )
				{
					$result['status'] = 'success';
				}
			}
			
			$html = $this->renderPartial('ajax_edit_variance_form', array(
				'model' => $model,
			), true);

			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}

	public function actionAddVariance()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);

		$model = new AccountLoginTracker; 
			
		if( isset($_POST['AccountLoginTracker']) )
		{
			$model->attributes = $_POST['AccountLoginTracker'];
			
			$model->account_id = $_POST['account_id'];
			
			$model->time_in = date('Y-m-d', strtotime($_POST['AccountLoginTracker']['time_in_date'])).' '.date('H:i:s', strtotime($_POST['AccountLoginTracker']['time_in_time']));
			$model->time_out = date('Y-m-d', strtotime($_POST['AccountLoginTracker']['time_out_date'])).' '.date('H:i:s', strtotime($_POST['AccountLoginTracker']['time_out_time']));
			
			$timeIn = new DateTime($model->time_in, new DateTimeZone('America/Denver'));
			$timeIn->setTimezone(new DateTimeZone('America/Chicago'));	

			if( $model->type == 1)
			{
				$timeOut = new DateTime($model->time_out, new DateTimeZone('America/Denver'));
				$timeOut->setTimezone(new DateTimeZone('America/Chicago'));		
			}
			else
			{
				$timeOut = new DateTime($model->time_out);
			}	
			
			$model->time_in = $timeIn->format('Y-m-d H:i:s');
			$model->time_out = $timeOut->format('Y-m-d H:i:s');
			
			// $model->type = 2;
			
			if( $model->save(false) )
			{
				$result['status'] = 'success';
			}
		}
		
		$html = $this->renderPartial('ajax_add_variance_form', array(
			'model' => $model,
		), true);

		$result['status'] = 'success';
		$result['html'] = $html;
	
		echo json_encode($result);
	}
	
	
	public function getStartAndEndDate($week, $year) 
	{
		$dateTime = new DateTime();
		
		$dateTime->setISODate($year, $week);
		
		$result['week_start'] = $dateTime->format('Y-m-d');
		
		$dateTime->modify('+6 days');
		
		$result['week_end'] = $dateTime->format('Y-m-d');
		
		return $result;
	}


	//billing window tab
	public function actionBillingWindow()
	{
		CustomerBillingScheduled::model()->deleteAll();
		
		$billingPeriod = date('M Y');
		
		if( isset($_GET['billingPeriod']) )
		{
			$billingPeriod = $_GET['billingPeriod'];
		}
		
		$pendingBillings = array();
		$pendingBillingsCount = 0;
		
		$declinedBillings = array();
		$declinedBillingsCount = 0;
		
		$pendingBillingsTotalAmount = 0;
		$declinedBillingsTotalAmount = 0;
		
		$customerQueues = CustomerQueueViewer::model()->findAll(array(
			'with' => 'customer',
			'order' => 'customer.lastname ASC',
			'condition' => 't.customer_id NOT IN (48) AND t.skill_id NOT IN (11,12)',
			// 'condition' => 'customer_id IN (1568) AND t.skill_id NOT IN (11,12)',
			// 'condition' => 'next_available_calling_time NOT IN ("On Hold", "Cancelled")',
			// 'limit' => 5
		));
		
		// echo 'customerQueues: ' . count($customerQueues);
		
		// echo '<br><br>';
		
		if( $customerQueues )
		{
			foreach( $customerQueues as $customerQueue )
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
						':month' => date('n', strtotime($billingPeriod)),
						':year' => date('Y', strtotime($billingPeriod))
					),
				));
				
				if( $customerSkill && !empty($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00' && date('Y-m', strtotime($billingPeriod)) >= date('Y-m', strtotime($customerSkill->start_month)) )
				{
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
								'condition' => 'id = :id AND type IN ("%", "$")',
								'params' => array(
									':id' => $customerSkillSubsidyLevel->subsidy_level_id,
								),
							));
							
							if( $subsidy )
							{
								if( $subsidy->type == '%' )
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
								else
								{
									if( !$isBilled )
									{
										$subsidyAmount = $subsidy->value;
									}
									
									$contractCreditSubsidys[$contract->id]['totalSubsidyAmount'] = $subsidyAmount;
								}							
							}
						}
					}

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
							':billing_period' => $billingPeriod
						),
						'order' => 'date_created DESC'
					));
					
					if( $existingBillingForCurrentMonth )
					{
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
							$existingBillingForCurrentMonth = array();
						}
					}
					
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
					
					$customerIsCallable = false;
						
					//check status and start date
					if( 
						isset($customerSkill->contract) && isset($customerSkill->customer) 
						&& $customerSkill->customer->status == 1 
						&& $customerSkill->customer->is_deleted == 0 
						&& !empty($customerSkill->start_month) 
						&& $customerSkill->start_month != '0000-00-00' 
						&& date('Y-m', strtotime($billingPeriod)) >= date('Y-m', strtotime($customerSkill->start_month)) 
					)
					{
						$customerIsCallable = true;
						
						// echo '<br>';
						// echo 'contract: ' . isset($customerSkill->contract);
						// echo '<br>';
						// echo 'status: ' . $customerSkill->customer->status;
						// echo '<br>';
						// echo 'is_deleted: ' . $customerSkill->customer->is_deleted;
						// echo '<br>';
						// echo 'start_month: ' . $customerSkill->start_month;
						// echo '<br>';
						// echo 'billingPeriod: ' . $billingPeriod;
						// echo '<br>';
						// echo 'customer skill year month: ' . date('Y-m', strtotime($customerSkill->start_month));
						// echo '<br>';
						
						// if( date('Y-m', strtotime($billingPeriod)) >= date('Y-m', strtotime($customerSkill->start_month)) )
						// {
							// echo 'current billing period is > customer skill start month';
						// }
						// else
						// {
							// echo 'current billing period is < customer skill start month';
						// }
						
						// echo '<br>';
						// echo 'customerIsCallable: ' . $customerIsCallable;
					}
					
					//check if on hold
					if( $customerSkill->is_contract_hold == 1 )
					{
						if( !empty($customerSkill->is_contract_hold_start_date) && !empty($customerSkill->is_contract_hold_end_date) )
						{
							// if( strtotime($billingPeriod) >= strtotime($customerSkill->is_contract_hold_start_date) && strtotime($billingPeriod) <= strtotime($customerSkill->is_contract_hold_end_date) )
						
							// if( date('Y-m', strtotime($billingPeriod)).date('-d') >= date('Y-m-d', strtotime($customerSkill->is_contract_hold_start_date)) )
							// {
								// echo date('Y-m', strtotime($billingPeriod)).date('-d').' >= '. date('Y-m-d', strtotime($customerSkill->is_contract_hold_start_date));
								// echo '<br>';
							// }
							
							// if( date('Y-m', strtotime($billingPeriod)).date('-d') <= date('Y-m-d', strtotime($customerSkill->is_contract_hold_end_date)) )
							// {
								// echo date('Y-m', strtotime($billingPeriod)).date('-d').' <= '. date('Y-m-d', strtotime($customerSkill->is_contract_hold_end_date));
								// echo '<br>';
							// }
							
							if( isset($_GET['billingPeriod']) )
							{
								if( date('Y-m', strtotime($billingPeriod)).date('-t') >= date('Y-m-d', strtotime($customerSkill->is_contract_hold_start_date)) && date('Y-m', strtotime($billingPeriod)).date('-t') <= date('Y-m-d', strtotime($customerSkill->is_contract_hold_end_date)) )
								{
									$customerIsCallable = false;
								}
							}
							else
							{
								if( date('Y-m', strtotime($billingPeriod)).date('-d') >= date('Y-m-d', strtotime($customerSkill->is_contract_hold_start_date)) && date('Y-m', strtotime($billingPeriod)).date('-d') <= date('Y-m-d', strtotime($customerSkill->is_contract_hold_end_date)) )
								{
									$customerIsCallable = false;
								}
							}
						}
					}
					
					//check if cancelled
					if( !empty($customerSkill->end_month) && date('Y', strtotime($customerSkill->end_month)) >= 2015 )
					{
						// echo '<br><br>';
						
						// echo date('Y-m-d', strtotime($billingPeriod));
						
						// echo '<br><br>';
						
						// echo date('Y-m-d', strtotime($customerSkill->end_month));
						
						if( date('Y-m-d', strtotime($billingPeriod)) >= date('Y-m-d', strtotime($customerSkill->end_month)) )
						{
							$customerIsCallable = false;
						}
					}
					
					if( empty($existingBilling) || ($existingBilling && empty($existingBillingForCurrentMonth) && $existingBilling->billing_period != $billingPeriod) )
					{
						$skillStatus = 'Inactive';
							
						if( isset($customerSkill->contract) && isset($customerSkill->customer) && $customerSkill->customer->status == 1 && $customerSkill->customer->is_deleted == 0 && time() >= strtotime($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00' )
						{
							$skillStatus = 'Active';
						}
						
						if( $customerSkill->is_contract_hold == 1 )
						{
							if( !empty($customerSkill->is_contract_hold_start_date) && !empty($customerSkill->is_contract_hold_end_date) )
							{
								if( time() >= strtotime($customerSkill->is_contract_hold_start_date) && time() <= strtotime($customerSkill->is_contract_hold_end_date) )
								{
									$skillStatus = 'Active - On Hold';

								}
							}
						}
						
						if( $customerSkill->is_hold_for_billing == 1 )
						{
							$skillStatus = 'Active - Decline Hold';
						}
						
						if( !empty($customerSkill->end_month) && date('Y', strtotime($customerSkill->end_month)) >= 2015 )
						{
							if( time() >= strtotime($customerSkill->end_month) )
							{
								$skillStatus = 'Cancelled';
							}
						}
						
						if( $customerIsCallable && empty($customerRemoved) )
						{
							if( $creditCardCount > 0 || $echecksCount > 0 )
							{
								$month = $billingPeriod;
								
								$pendingBillings[$customerQueue->customer_id][$customerQueue->skill_id] = array(
									'customer_id' => $customerQueue->customer->id,
									'agent_id' => $customerQueue->customer->custom_customer_id,
									'skill_id' => $customerQueue->skill_id,
									'contract_id' => $contract->id,
									'customer_name' => $customerQueue->customer->getFullName(),
									'contract' => $customerQueue->contract->contract_name,
									'quantity' => $totalLeads,
									'amount' => $totalAmount,
									'subsidy' => $subsidyAmount,
									'month' => $month,
									'latest_transaction_type' => $latestTransactionType,
									'latest_transaction_status' => $latestTransactionStatus,
									'skill_status' => $skillStatus,
									'no_billing' => false
								);
							}
							else
							{
								$month = $billingPeriod;
								
								$pendingBillings[$customerQueue->customer_id][$customerQueue->skill_id] = array(
									'customer_id' => $customerQueue->customer->id,
									'agent_id' => $customerQueue->customer->custom_customer_id,
									'skill_id' => $customerQueue->skill_id,
									'contract_id' => $contract->id,
									'customer_name' => $customerQueue->customer->getFullName(),
									'contract' => $customerQueue->contract->contract_name,
									'quantity' => $totalLeads,
									'amount' => $totalAmount,
									'subsidy' => $subsidyAmount,
									'month' => $month,
									'latest_transaction_type' => $latestTransactionType,
									'latest_transaction_status' => $latestTransactionStatus,
									'skill_status' => $skillStatus,
									'no_billing' => true
								);
							}
						}
					}
					else
					{
						if( $existingBilling && empty($existingBillingForCurrentMonth) && $existingBilling->anet_responseCode != 1 && ($creditCardCount > 0 || $echecksCount > 0) )
						{
							if( strtotime($existingBilling->date_created) > strtotime($customerRemoved->date_created) || empty($customerRemoved) )
							{
								$skillStatus = 'Inactive';
									
								if( isset($customerSkill->contract) && isset($customerSkill->customer) && $customerSkill->customer->status == 1 && $customerSkill->customer->is_deleted == 0 && time() >= strtotime($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00' )
								{
									$skillStatus = 'Active';
								}
								
								if( $customerSkill->is_contract_hold == 1 )
								{
									if( !empty($customerSkill->is_contract_hold_start_date) && !empty($customerSkill->is_contract_hold_end_date) )
									{
										if( time() >= strtotime($customerSkill->is_contract_hold_start_date) && time() <= strtotime($customerSkill->is_contract_hold_end_date) )
										{
											$skillStatus = 'Active - On Hold';

										}
									}
								}
								
								if( $customerSkill->is_hold_for_billing == 1 )
								{
									$skillStatus = 'Active - Decline Hold';
								}
								
								if( !empty($customerSkill->end_month) && date('Y', strtotime($customerSkill->end_month)) >= 2015 )
								{
									if( time() >= strtotime($customerSkill->end_month) )
									{
										$skillStatus = 'Cancelled';
									}
								}
								
								$month = $existingBilling->billing_period;
								$latestTransactionType = $existingBilling->transaction_type;
								$latestTransactionStatus = $existingBilling->anet_responseCode;
								
								$declinedBillings[$customerQueue->customer_id][$customerQueue->skill_id] = array(
									'customer_id' => $customerQueue->customer->id,
									'agent_id' => $customerQueue->customer->custom_customer_id,
									'skill_id' => $customerQueue->skill_id,
									'contract_id' => $contract->id,
									'customer_name' => $customerQueue->customer->getFullName(),
									'contract' => $customerQueue->contract->contract_name,
									'quantity' => $totalLeads,
									'amount' => $totalAmount,
									'subsidy' => $subsidyAmount,
									'month' => $month,
									'latest_transaction_type' => $latestTransactionType,
									'latest_transaction_status' => $latestTransactionStatus,
									'skill_status' => $skillStatus,
								);
							}
						}
					}
				}
			}
		}
		
		
		// echo '<pre>';
			// echo 'Pending Billings ('.count($pendingBillings).'): <br />';
			// print_r($pendingBillings);
			
			// echo '<br><hr><br>';
			
			// echo 'Declined Billings ('.count($declinedBillings).'): <br />';
			
			// print_r($declinedBillings);
		// exit;
		
		if( $pendingBillings )
		{
			// echo '<pre>';
			// print_r($pendingBillings);
			// exit;
			foreach( $pendingBillings as $customerId => $pendingSkillBilling )
			{
				foreach($pendingSkillBilling as $pendingBilling)
				{
					$totalCreditAmount = 0;
					$customerCredits = CustomerCredit::model()->findAll(array(
						'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND status=1',
						'params' => array(
							':customer_id' => $customerId,
							':contract_id' => $pendingBilling['contract_id'],
						),
					));
					
					if( $customerCredits )
					{
						foreach( $customerCredits as $customerCredit )
						{
							$creditStartDate = date($customerCredit->start_year.'-'.$customerCredit->start_month.'-01');
											
							if( $customerCredit->type == 2 ) //month range
							{
								if( $customerCredit->end_month == '02' )
								{
									$creditEndDate = date($customerCredit->end_year.'-'.$customerCredit->end_month.'-28');
								}
								elseif( $customerCredit->end_month == '12' )
								{
									$creditEndDate = date($customerCredit->end_year.'-'.$customerCredit->end_month.'-31');
								}
								else
								{
									$creditEndDate = date($customerCredit->end_year.'-'.$customerCredit->end_month.'-t');
								}
								
								if( $customerCredit->start_month >= $customerCredit->end_month )
								{
									$creditEndDate = date($customerCredit->end_year.'-'.$customerCredit->end_month.'-d', strtotime('+1 year', strtotime($creditEndDate)));
								}
							}
							else
							{
								if( $customerCredit->start_month == '02' )
								{
									$creditEndDate = date($customerCredit->start_year.'-'.$customerCredit->start_month.'-28');
								}
								elseif( $customerCredit->start_month == '12' )
								{
									$creditEndDate = date($customerCredit->start_year.'-'.$customerCredit->start_month.'-31');
								}
								else
								{
									$creditEndDate = date($customerCredit->start_year.'-'.$customerCredit->start_month.'-t');
								}
							}
							
							$monthBillingPeriod = explode(' ',$billingPeriod);
							$monthPeriod = date('m', strtotime("$monthBillingPeriod[0] 1 ".date('Y')));
							$startDayOfBillingPeriod = date("Y-m-d",strtotime(date('Y')."-".$monthPeriod."-1"));
							$lastDayOfBillingPeriod = date("Y-m-t", strtotime($startDayOfBillingPeriod));
																			
							if( (strtotime($startDayOfBillingPeriod) >= strtotime($creditStartDate)) && (strtotime($lastDayOfBillingPeriod) <= strtotime($creditEndDate)) )
							{
								$totalCreditAmount += $customerCredit->amount;
							}
						}
					}
					
					
					//credit amount should not be over the Amount, for the customer will ask it to be billed next month -aug 9, 2016
					if($totalCreditAmount > $pendingBilling['amount'])
					{
						$totalCreditAmount = $pendingBilling['amount'] - $pendingBilling['subsidy'];
					}
										
					if($pendingBilling['no_billing'] != true)
					{
						$totalReducedAmount = ($pendingBilling['amount'] - $totalCreditAmount - $pendingBilling['subsidy']);
						if( $totalReducedAmount < 0 )
							$totalReducedAmount = 0;
						
						$pendingBillingsTotalAmount += $totalReducedAmount;
					}
					// var_dump($pendingBillingsTotalAmount);
					
					$pendingBillingsCount++;
				}
			}
		}
		
		if( $declinedBillings )
		{
			foreach( $declinedBillings as $customerId => $declinedSkillBilling )
			{
				
				foreach($declinedSkillBilling as $declinedBilling)
				{
					$totalCreditAmount = 0;
					$customerCredits = CustomerCredit::model()->findAll(array(
						'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND status=1',
						'params' => array(
							':customer_id' => $customerId,
							':contract_id' => $declinedBilling['contract_id'],
						),
					));
					
					if( $customerCredits )
					{
						foreach( $customerCredits as $customerCredit )
						{
							$creditStartDate = date($customerCredit->start_year.'-'.$customerCredit->start_month.'-01');
											
							if( $customerCredit->type == 2 ) //month range
							{
								if( $customerCredit->end_month == '02' )
								{
									$creditEndDate = date($customerCredit->end_year.'-'.$customerCredit->end_month.'-28');
								}
								elseif( $customerCredit->end_month == '12' )
								{
									$creditEndDate = date($customerCredit->end_year.'-'.$customerCredit->end_month.'-31');
								}
								else
								{
									$creditEndDate = date($customerCredit->end_year.'-'.$customerCredit->end_month.'-t');
								}
								
								if( $customerCredit->start_month >= $customerCredit->end_month )
								{
									$creditEndDate = date($customerCredit->end_year.'-'.$customerCredit->end_month.'-d', strtotime('+1 year', strtotime($creditEndDate)));
								}
							}
							else
							{
								if( $customerCredit->start_month == '02' )
								{
									$creditEndDate = date($customerCredit->start_year.'-'.$customerCredit->start_month.'-28');
								}
								elseif( $customerCredit->start_month == '12' )
								{
									$creditEndDate = date($customerCredit->start_year.'-'.$customerCredit->start_month.'-31');
								}
								else
								{
									$creditEndDate = date($customerCredit->start_year.'-'.$customerCredit->start_month.'-t');
								}
							}
							
							$monthBillingPeriod = explode(' ',$billingPeriod);
							$monthPeriod = date('m', strtotime("$monthBillingPeriod[0] 1 ".date('Y')));
							$startDayOfBillingPeriod = date("Y-m-d",strtotime(date('Y')."-".$monthPeriod."-1"));
							$lastDayOfBillingPeriod = date("Y-m-t", strtotime($startDayOfBillingPeriod));
																			
							if( (strtotime($startDayOfBillingPeriod) >= strtotime($creditStartDate)) && (strtotime($lastDayOfBillingPeriod) <= strtotime($creditEndDate)) )
							{
								$totalCreditAmount += $customerCredit->amount;
							}
							
							// echo '$customerCredit->amount: ' . $customerCredit->amount;
							// echo '<br>';
						}
					}
					
					//credit amount should not be over the Amount, for the customer will ask it to be billed next month -aug 9, 2016
					if($totalCreditAmount > $declinedBilling['amount'])
					{
						$totalCreditAmount = $declinedBilling['amount'] - $declinedBilling['subsidy'];
					}
										
					$totalReducedAmount = ($declinedBilling['amount'] - $totalCreditAmount - $declinedBilling['subsidy']);
					if( $totalReducedAmount < 0 )
						$totalReducedAmount = 0;
					
					
					$declinedBillingsTotalAmount += $totalReducedAmount;
					
					$declinedBillingsCount++;
					
					// echo 'customerId: ' . $customerId;
					// echo '<br>';
					// echo 'contract_id: ' . $declinedBilling['contract_id'];
					// echo '<br>';
					// echo 'customer :' . $declinedBilling['customer_name'];
					// echo '<br>';
					// echo 'original :' . $declinedBilling['amount'];
					// echo '<br>';
					// echo 'credit: ' . $totalCreditAmount . ' | '.$creditStartDate.'-'.$creditEndDate.' | ' . count($customerCredits);
					// echo '<br>';
					// echo 'subsidy :' . $declinedBilling['subsidy'];
					// echo '<br>';
					// echo 'totalReducedAmount :' . $totalReducedAmount;
					// echo '<br>';
					// echo '<br>';
				}
			}
		}
		
		// echo '<br><hr><br>';
	
		// echo 'declinedBillingsTotalAmount: ' .$declinedBillingsTotalAmount;
		// exit;
		
		// echo '<pre>';
			// print_r($pendingBillings);
			// print_r($declinedBillings);
		// echo '</pre>';
		// exit;
		
		$this->render('billingWindow', array(
			'billingPeriod' => $billingPeriod,
			'pendingBillings' => $pendingBillings,
			'pendingBillingsCount' => $pendingBillingsCount,
			'declinedBillings' => $declinedBillings,
			'declinedBillingsCount' => $declinedBillingsCount,
			'pendingBillingsTotalAmount' => '$' . number_format($pendingBillingsTotalAmount, 2),
			'declinedBillingsTotalAmount' => '$' . number_format($declinedBillingsTotalAmount, 2),
		));
	}

	
	public function actionBillingWindowExport($type)
	{
		$billingPeriod = date('M Y');
		
		if( isset($_GET['billingPeriod']) )
		{
			$billingPeriod = $_GET['billingPeriod'];
		}
		
		$pendingBillings = array();
		$declinedBillings = array();
		
		$pendingBillingsTotalAmount = 0;
		$declinedBillingsTotalAmount = 0;
		
		$customerQueues = CustomerQueueViewer::model()->findAll(array(
			'with' => 'customer',
			'order' => 'customer.lastname ASC',
			'condition' => 't.customer_id NOT IN (48) AND t.skill_id NOT IN (11,12)',
			// 'condition' => 'customer_id=1629',
			// 'condition' => 'next_available_calling_time NOT IN ("On Hold", "Cancelled")',
			// 'limit' => 100
		));
		
		$pendingBillingsTotalAmount = 0;
		$declinedBillingsTotalAmount = 0;
		
		$customerQueues = CustomerQueueViewer::model()->findAll(array(
			'with' => 'customer',
			'order' => 'customer.lastname ASC',
			'condition' => 't.customer_id NOT IN (48)',
			// 'condition' => 'customer_id=405',
			// 'condition' => 'next_available_calling_time NOT IN ("On Hold", "Cancelled")',
			// 'limit' => 100
		));
		
		if( $customerQueues )
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
			
			$ctr = 1;

			$headers = array(
				'A' => 'Customer ID',
				'B' => 'Customer Name',
				'C' => 'Customer Email Address',
				'D' => 'Staff Email Addresses',
				'E' => 'Contract',
				'F' => 'Quantity',
				'G' => 'Original Amount',
				'H' => 'Billing Credit',
				'I' => 'Subsidy',
				'J' => 'Reduced Amount',
				'K' => 'Month',
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
			
			foreach( $customerQueues as $customerQueue )
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
						':month' => date('n', strtotime($billingPeriod)),
						':year' => date('Y', strtotime($billingPeriod))
					),
				));
				
				if( $customerSkill && !empty($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00' && date('Y-m', strtotime($billingPeriod)) >= date('Y-m', strtotime($customerSkill->start_month)) )
				{
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
							':billing_period' => $billingPeriod
						),
						'order' => 'date_created DESC'
					));
					
					if( $existingBillingForCurrentMonth )
					{
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
							$existingBillingForCurrentMonth = array();
						}
					}
					
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
					
					$customerIsCallable = false;
						
					//check status and start date
					if( 
						isset($customerSkill->contract) && isset($customerSkill->customer) 
						&& $customerSkill->customer->status == 1 
						&& $customerSkill->customer->is_deleted == 0 
						&& !empty($customerSkill->start_month) 
						&& $customerSkill->start_month != '0000-00-00' 
						&& date('Y-m', strtotime($billingPeriod)) >= date('Y-m', strtotime($customerSkill->start_month)) 
					)
					{
						$customerIsCallable = true;
						
						// echo '<br>';
						// echo 'contract: ' . isset($customerSkill->contract);
						// echo '<br>';
						// echo 'status: ' . $customerSkill->customer->status;
						// echo '<br>';
						// echo 'is_deleted: ' . $customerSkill->customer->is_deleted;
						// echo '<br>';
						// echo 'start_month: ' . $customerSkill->start_month;
						// echo '<br>';
						// echo 'billingPeriod: ' . $billingPeriod;
						// echo '<br>';
						// echo 'customer skill year month: ' . date('Y-m', strtotime($customerSkill->start_month));
						// echo '<br>';
						
						// if( date('Y-m', strtotime($billingPeriod)) >= date('Y-m', strtotime($customerSkill->start_month)) )
						// {
							// echo 'current billing period is > customer skill start month';
						// }
						// else
						// {
							// echo 'current billing period is < customer skill start month';
						// }
						
						// echo '<br>';
					}

					//check if on hold
					if( $customerSkill->is_contract_hold == 1 )
					{
						if( !empty($customerSkill->is_contract_hold_start_date) && !empty($customerSkill->is_contract_hold_end_date) )
						{
							// if( strtotime($billingPeriod) >= strtotime($customerSkill->is_contract_hold_start_date) && strtotime($billingPeriod) <= strtotime($customerSkill->is_contract_hold_end_date) )
						
							// if( date('Y-m', strtotime($billingPeriod)).date('-d') >= date('Y-m-d', strtotime($customerSkill->is_contract_hold_start_date)) )
							// {
								// echo date('Y-m', strtotime($billingPeriod)).date('-d').' >= '. date('Y-m-d', strtotime($customerSkill->is_contract_hold_start_date));
								// echo '<br>';
							// }
							
							// if( date('Y-m', strtotime($billingPeriod)).date('-d') <= date('Y-m-d', strtotime($customerSkill->is_contract_hold_end_date)) )
							// {
								// echo date('Y-m', strtotime($billingPeriod)).date('-d').' <= '. date('Y-m-d', strtotime($customerSkill->is_contract_hold_end_date));
								// echo '<br>';
							// }
							
							if( date('Y-m', strtotime($billingPeriod)).date('-d') >= date('Y-m-d', strtotime($customerSkill->is_contract_hold_start_date)) && date('Y-m', strtotime($billingPeriod)).date('-d') <= date('Y-m-d', strtotime($customerSkill->is_contract_hold_end_date)) )
							{
								$customerIsCallable = false;
							}
						}
					}
					
					//check if cancelled
					if( !empty($customerSkill->end_month) && date('Y', strtotime($customerSkill->end_month)) >= 2015 )
					{
						if( strtotime($billingPeriod) >= strtotime($customerSkill->end_month) )
						{
							$customerIsCallable = false;
						}
						
						// if( isset($_GET['billingPeriod']) )
						// {
							// if( date('Y-m-d', strtotime($customerSkill->end_month)) <= date('Y-m', strtotime($billingPeriod)).date('-t') )
							// {
								// $customerIsCallable = false;
							// }
						// }
						// else
						// {
							// if( date('Y-m-d', strtotime($customerSkill->end_month)) <= date('Y-m', strtotime($billingPeriod)).date('-d') )
							// {
								// $customerIsCallable = false;
							// }
						// }
					}
					
					if( empty($existingBilling) || ($existingBilling && empty($existingBillingForCurrentMonth) && $existingBilling->billing_period != $billingPeriod) )
					{
						if( $customerIsCallable && empty($customerRemoved) )
						{
							if( $creditCardCount > 0 || $echecksCount > 0 )
							{
								$month = $billingPeriod;
								
								$pendingBillings[$customerQueue->customer_id][$customerQueue->skill_id] = array(
									'customer_id' => $customerQueue->customer->id,
									'agent_id' => $customerQueue->customer->custom_customer_id,
									'skill_id' => $customerQueue->skill_id,
									'contract_id' => $contract->id,
									'customer_name' => $customerQueue->customer->getFullName(),
									'contract' => $customerQueue->contract->contract_name,
									'quantity' => $totalLeads,
									'amount' => $totalAmount,
									'subsidy' => $subsidyAmount,
									'month' => $month,
									'latest_transaction_type' => $latestTransactionType,
									'latest_transaction_status' => $latestTransactionStatus,
									'no_billing' => false
								);
							}
							else
							{
								$month = $billingPeriod;
								
								$pendingBillings[$customerQueue->customer_id][$customerQueue->skill_id] = array(
									'customer_id' => $customerQueue->customer->id,
									'agent_id' => $customerQueue->customer->custom_customer_id,
									'skill_id' => $customerQueue->skill_id,
									'contract_id' => $contract->id,
									'customer_name' => $customerQueue->customer->getFullName(),
									'contract' => $customerQueue->contract->contract_name,
									'quantity' => $totalLeads,
									'amount' => $totalAmount,
									'subsidy' => $subsidyAmount,
									'month' => $month,
									'latest_transaction_type' => $latestTransactionType,
									'latest_transaction_status' => $latestTransactionStatus,
									'no_billing' => true
								);
							}
						}
					}
					else
					{
						if( $existingBilling && empty($existingBillingForCurrentMonth) && $existingBilling->anet_responseCode != 1 && ($creditCardCount > 0 || $echecksCount > 0) )
						{
							if( strtotime($existingBilling->date_created) > strtotime($customerRemoved->date_created) || empty($customerRemoved) )
							{
								$month = $existingBilling->billing_period;
								$latestTransactionType = $existingBilling->transaction_type;
								$latestTransactionStatus = $existingBilling->anet_responseCode;
								
								$declinedBillings[$customerQueue->customer_id][$customerQueue->skill_id] = array(
									'customer_id' => $customerQueue->customer->id,
									'agent_id' => $customerQueue->customer->custom_customer_id,
									'skill_id' => $customerQueue->skill_id,
									'contract_id' => $contract->id,
									'customer_name' => $customerQueue->customer->getFullName(),
									'contract' => $customerQueue->contract->contract_name,
									'quantity' => $totalLeads,
									'amount' => $totalAmount,
									'subsidy' => $subsidyAmount,
									'month' => $month,
									'latest_transaction_type' => $latestTransactionType,
									'latest_transaction_status' => $latestTransactionStatus
								);
							}
						}
					}
				}
			}
			
			$ctr = 2;
			
			if( $type == 'pending' )
			{
				$filename = 'Billing Window - Pending';
				
				if( $pendingBillings )
				{
					foreach( $pendingBillings as $customerId => $pendingSkillBilling )
					{
						foreach($pendingSkillBilling as $pendingBilling)
						{
							$totalCreditAmount = 0;
							$customerCredits = CustomerCredit::model()->findAll(array(
								'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND status=1',
								'params' => array(
									':customer_id' => $customerId,
									':contract_id' => $pendingBilling['contract_id'],
								),
							));
							
							if( $customerCredits )
							{
								foreach( $customerCredits as $customerCredit )
								{
									$creditStartDate = date('Y-'.$customerCredit->start_month.'-1');
									
									if( $customerCredit->type == 2 ) //month range
									{
										if( $customerCredit->end_month == '02' )
										{
											$creditEndDate = date('Y-'.$customerCredit->end_month.'-28');
										}
										else
										{
											$creditEndDate = date('Y-'.$customerCredit->end_month.'-t');
										}
										
										if( $customerCredit->start_month >= $customerCredit->end_month )
										{
											$creditEndDate = date('Y-m-d', strtotime('+1 year', strtotime($creditEndDate)));
										}
									}
									else
									{
										if( $customerCredit->start_month )
										{
											$creditEndDate = date('Y-'.$customerCredit->start_month.'-28');
										}
										else
										{
											$creditEndDate = date('Y-'.$customerCredit->start_month.'-t');
										}
									}
									
									if( (strtotime('now') >= strtotime($creditStartDate)) && (strtotime('now') <= strtotime($creditEndDate)) )
									{
										$totalCreditAmount += $customerCredit->amount;
									}
								}
							}
							
							//credit amount should not be over the Amount, for the customer will ask it to be billed next month -aug 9, 2016
							if($totalCreditAmount > $pendingBilling['amount'])
							{
								$totalCreditAmount = $pendingBilling['amount'] - $pendingBilling['subsidy'];
							}

							$totalReducedAmount = $pendingBilling['amount'];
							$totalReducedAmount = $totalReducedAmount - $pendingBilling['subsidy'];
							
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
							
							// if( $pendingBilling['no_billing'] != true || in_array($pendingBilling['contract'], array('Farmers Per Appointment 2016 FOLIO','Farmers Per Name 2016 FOLIO', 'Farmers Per Appointment FOLIO')) )
							if( $pendingBilling['no_billing'] != true )
							{
								$totalCreditAmount = '$'.$totalCreditAmount;
							
								$amount = '$'.$pendingBilling['amount'];
								
								$totalReducedAmount = '$'.$totalReducedAmount;
								
								$subsidy = '$'.$pendingBilling['subsidy'];
							}
							else
							{
								$totalCreditAmount = '-';
								$amount = '-';
								$totalReducedAmount = '-';
								$subsidy = '-';
							}
							
							// $totalCreditAmount = $pendingBilling['no_billing'] != true ? '$'.$totalCreditAmount : '-';
							
							// $amount = $pendingBilling['no_billing'] != true ? '$'.$pendingBilling['amount'] : '-';
							
							// $totalReducedAmount = $pendingBilling['no_billing'] != true ? '$'.$totalReducedAmount : '-';
							
							// $subsidy = $pendingBilling['no_billing'] != true ? '$'.$pendingBilling['subsidy'] : '-';
							
							$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $pendingBilling['agent_id']);
							$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $pendingBilling['customer_name']);
							$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $pendingBilling['customer_email_address']);
							$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $pendingBilling['staff_email_addresses']);
							$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $pendingBilling['contract']);
							$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $pendingBilling['quantity']);
							$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $amount);
							$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $totalCreditAmount);
							$objPHPExcel->getActiveSheet()->SetCellValue('I'.$ctr, $subsidy);
							$objPHPExcel->getActiveSheet()->SetCellValue('J'.$ctr, $totalReducedAmount);
							$objPHPExcel->getActiveSheet()->SetCellValue('K'.$ctr, $pendingBilling['month']);
							
							$ctr++;
						}
					}
				}	
			}
			else
			{
				$filename = 'Billing Window - Declined';
				
				if( $declinedBillings )
				{
					foreach( $declinedBillings as $customerId => $declinedSkillBilling )
					{
						foreach($declinedSkillBilling as $declinedBilling)
						{
							$totalCreditAmount = 0;
							$customerCredits = CustomerCredit::model()->findAll(array(
								'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND status=1',
								'params' => array(
									':customer_id' => $customerId,
									':contract_id' => $declinedBilling['contract_id'],
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
									
									if( (time() >= strtotime($creditStartDate)) && (time() <= strtotime($creditEndDate)) )
									{
										$totalCreditAmount += $customerCredit->amount;
									}
								}
							}
							
							//credit amount should not be over the Amount, for the customer will ask it to be billed next month -aug 9, 2016
							if($totalCreditAmount > $declinedBilling['amount'])
							{
								$totalCreditAmount = $declinedBilling['amount'] - $declinedBilling['subsidy'];
							}
							
							$totalReducedAmount = ($declinedBilling['amount'] - $totalCreditAmount - $declinedBilling['subsidy']);

							if( $totalReducedAmount < 0 )
							{
								$totalReducedAmount = 0;
							}
							
							$totalReducedAmount = number_format($totalReducedAmount, 2);
							
							$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $declinedBilling['agent_id']);
							$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $declinedBilling['customer_name']);
							$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $declinedBilling['customer_email_address']);
							$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $declinedBilling['staff_email_addresses']);
							$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $declinedBilling['contract']);
							$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $declinedBilling['quantity']);
							$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, '$'.$declinedBilling['amount']);
							$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, '$'.$totalCreditAmount);
							$objPHPExcel->getActiveSheet()->SetCellValue('I'.$ctr, '$'.$declinedBilling['subsidy']);
							$objPHPExcel->getActiveSheet()->SetCellValue('J'.$ctr, '$'.$totalReducedAmount);
							$objPHPExcel->getActiveSheet()->SetCellValue('K'.$ctr, $declinedBilling['month']);
							
							$ctr++;
						}
					}
				}
			}
		}
		
		header('Content-Type: application/vnd.ms-excel'); 
		header('Content-Disposition: attachment; filename="'.$filename.'.xlsx"'); 
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		
		header('Cache-Control: max-age=0');
		
		$objWriter->save('php://output');
	}
	
	public function actionRemoveCustomer()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		$model = new CustomerBillingWindowRemoved;
		
		$model->setAttributes(array(
			'customer_id' => $_POST['customer_id'],
			'skill_id' => $_POST['skill_id'],
			'account_id' => Yii::app()->user->account->id,
		));
		
		if( isset($_POST['CustomerBillingWindowRemoved']) && !empty($_POST['note']) )
		{
			$model->attributes = $_POST['CustomerBillingWindowRemoved'];
			
			if( $model->save(false) )
			{
				// $history = new CustomerHistory;
								
				// $history->setAttributes(array(
					// 'model_id' => $model->id, 
					// 'customer_id' => $model->customer_id,
					// 'user_account_id' => Yii::app()->user->account->id,
					// 'page_name' => 'Billing Window',
					// 'content' => $_POST['note'],
					// 'type' => $history::TYPE_DELETED,
				// ));
				
				// $history->save(false);
				
				$history = new CustomerBilling;
				
				$history->setAttributes(array(
					'model_id' => $model->id, 
					'customer_id' => $model->customer_id,
					'account_id' => Yii::app()->user->account->id,
					'description' => $_POST['note'],
					'contract_id' => $_POST['contract_id'],
					'amount' => $_POST['amount'],
					'credit_amount' => $_POST['credit_amount'],
					'subsidy_amount' => $_POST['subsidy_amount'],
					'original_amount' => $_POST['original_amount'],
					'transaction_type' => $_POST['transaction_type'],
					'billing_period' => $_POST['billing_period'],
				));
				
				$history->save(false);

				$result['status'] = 'success';
				$result['message'] = 'Customer has been removed successfully.';
			}
			else
			{
				$result['message'] = 'Error on removing the customer.';
			}
		}
		
		if( isset($_POST['ajax']) )
		{
			$html = $this->renderPartial('ajax_remove_customer_form', array(
				'model' => $model,
				'amount' => $_POST['amount'],
				'billing_period' => $_POST['billing_period'],
				'contract' => $_POST['contract'],
				'customer_name' => $_POST['customer_name'],
				'subsidy_amount' => $_POST['subsidy_amount'],
				'amount' => $_POST['amount'],
				'original_amount' => $_POST['original_amount'],
				'transaction_type' => $_POST['transaction_type'],
				'contract_id' => $_POST['contract_id'],
			), true);

			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}
	
	
	public function actionProcessTransaction()
	{
		// exit;
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		$authAccount = Yii::app()->user->account;
		
		$customer = Customer::model()->find(array(
			'condition' => 'id = :id',
			'params' => array(
				':id' => $_POST['customer_id'],
			),
		));
		
		$customerSkill = CustomerSkill::model()->find(array(
			'condition' => 'customer_id = :customer_id AND skill_id = :skill_id',
			'params' => array(
				':customer_id' => $customer->id,
				':skill_id' => $_POST['skill_id'],
			),
		));
								
		$model = new CustomerBilling;
		$model->customer_id = $customer->id;		
		$model->account_id = $authAccount->id;	
		$model->billing_type = 'Service Fee';
			
		if( isset($_POST['ajax']) )
		{
			$model->transaction_type = 'Charge';
			$model->billing_period = $_POST['billing_period'];
			$model->description = $_POST['billing_period'].' - '.$_POST['contract'];
			$model->description .= '<br>' . $_POST['credit_description'];
			
			$totalAmount = $_POST['amount'];
			
			$model->amount = number_format($totalAmount, 2);
			$model->credit_amount = $_POST['credit_amount'];
			$model->subsidy_amount = $_POST['subsidy_amount'];
			$model->original_amount = $_POST['original_amount'];
			// $model->contract_id = $_POST['contract'];
			$model->contract_id = $customerSkill->contract_id;
			
			$method = explode('-', $model->getDefaultMethod($model->customer_id));
			$type = $method[0];
			$cardId = $method[1];
			
			$model->payment_method = $type;
			
			if( $type == 'creditCard' )
			{
				$defaultCreditCard = CustomerCreditCard::model()->findByPk($cardId);
				
				$model->credit_card_id = $defaultCreditCard->id;	
				$model->credit_card_type = $defaultCreditCard->credit_card_type;	
				$model->credit_card_number = $defaultCreditCard->credit_card_number;	
				$model->credit_card_security_code = $defaultCreditCard->security_code;	
				$model->credit_card_expiration_month = $defaultCreditCard->expiration_month;	
				$model->credit_card_expiration_year = $defaultCreditCard->expiration_year;	
			}
			
			if( $type == 'echeck' )
			{
				$defaultEcheck = CustomerEcheck::model()->findByPk($cardId);
				
				$model->echeck_id = $defaultEcheck->id;
				$model->ach_account_name = $defaultEcheck->account_name;
				$model->ach_account_number = $defaultEcheck->account_number;
				$model->ach_routing_number = $defaultEcheck->routing_number;
				$model->ach_account_type = $defaultEcheck->account_type;
				$model->ach_bank_name = $defaultEcheck->institution_name;
			}
			
			// echo '<pre>';
				// print_r($model->attributes);
			// exit;
			
			if( $totalAmount > 0 )
			{
				if( $model->save(false) )
				{
					$updateBillingRecord = CustomerBilling::model()->findByPk($model->id);
					
					Yii::import('application.vendor.*');
					require ('anet_php_sdk/AuthorizeNet.php');
					
					//sandbox
					// define("AUTHORIZENET_API_LOGIN_ID", "5CzTeH72f98D");	
					// define("AUTHORIZENET_TRANSACTION_KEY", "8n25bCL72432SpR9");	
					// define("AUTHORIZENET_SANDBOX", true);
					
					//live
					define("AUTHORIZENET_API_LOGIN_ID", "8Wtz3C6a");	
					define("AUTHORIZENET_TRANSACTION_KEY", "8YEBD7n3Z64r3x39");
					define("AUTHORIZENET_SANDBOX", false);
					
					$authorizeTransaction = new AuthorizeNetAIM;
					
					$authorizeTransaction->setSandbox(AUTHORIZENET_SANDBOX);

					if( $model->payment_method == 'creditCard' )
					{
						$authorizeTransaction->setFields(array(
							// 'invoice_num' => ,
							'amount' => number_format($totalAmount, 2),
							'first_name' => $defaultCreditCard->first_name,
							'last_name' => $defaultCreditCard->last_name,
							'email' => $customer->email_address,
							'card_num' => $defaultCreditCard->credit_card_number, 
							'card_code' => $defaultCreditCard->security_code,
							'exp_date' => $defaultCreditCard->expiration_month . $defaultCreditCard->expiration_year,
							'address' => $defaultCreditCard->address,
							'city' => $defaultCreditCard->city,
							'state' => $defaultCreditCard->state,
							'zip' => $defaultCreditCard->zip,
						));
					}
					
					if( $model->payment_method == 'echeck' )
					{
						$authorizeTransaction->amount = number_format($totalAmount, 2);
									
						$authorizeTransaction->setECheck(
							$defaultEcheck->routing_number,
							$defaultEcheck->account_number,
							$defaultEcheck->account_type,
							$defaultEcheck->institution_name,
							$defaultEcheck->account_name,
							'WEB'
						);
					}
					
					$response = $authorizeTransaction->authorizeAndCapture();
									
					$request  = new AuthorizeNetTD;
					$response_TransactionDetails = $request->getTransactionDetails($response->transaction_id);
					
					$content = '';
					if( $response->approved )
					{
						$transaction_Details = $response_TransactionDetails->xml->transaction;
						$order = $transaction_Details->order;
						$anetCustomer = $transaction_Details->customer;
						$billTo = $transaction_Details->billTo;
						
						$billing_Date = date('Y-m-d H:i:s', strtotime($transaction_Details->submitTimeUTC));
						
						if($updateBillingRecord)
						{
							$updateBillingRecord->setAttributes(array(
								'anet_transId' => $transaction_Details->transId,
								'anet_invoiceNumber' => $order->invoiceNumber,
								'anet_submitTimeUTC' => $transaction_Details->submitTimeUTC,
								'anet_submitTimeLocal' => $transaction_Details->submitTimeLocal,
								'anet_transactionType' => $transaction_Details->transactionType,
								'anet_transactionStatus' =>$transaction_Details->transactionStatus,
								'anet_responseCode' => $transaction_Details->responseCode,
								'anet_responseReasonCode'=> $transaction_Details->responseReasonCode,
								'anet_responseReasonDescription'=> $transaction_Details->responseReasonDescription,
								'anet_authCode'=> $transaction_Details->authCode,
								'anet_AVSResponse'=> $transaction_Details->AVSResponse,
								'anet_cardCodeResponse'=> $transaction_Details->cardCodeResponse,
								'anet_authAmount'=> $transaction_Details->authAmount,
								'anet_settleAmount'=> $transaction_Details->settleAmount,
								'anet_taxExempt'=> $transaction_Details->taxExempt,
								'anet_customer_Email'=> $anetCustomer->email,
								'anet_billTo_firstName'=> $billTo->firstName,
								'anet_billTo_lastName'=> $billTo->lastName,
								'anet_billTo_address'=> $billTo->address,
								'anet_billTo_city'=> $billTo->city,
								'anet_billTo_state'=> $billTo->state,
								'anet_billTo_zip'=> $billTo->zip,
								'anet_recurringBilling' => $transaction_Details->recurringBilling,
								'anet_product' => $transaction_Details->product,
								'anet_marketType' => $transaction_Details->marketType
							));
							
							if($updateBillingRecord->save(false))
							{		
								if( $response_TransactionDetails->xml->messages->resultCode == 'Ok' )
								{
									// Transaction approved! Do your logic here.
									$result['status'] = 'success';
									$result['message'] = 'Charge successful';
									
									//save customer credit used for the Customer Billing (the total of all the credits were already submitted in the view)
									$customerCredits = CustomerCredit::model()->findAll(array(
										'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND status=1',
										'params' => array(
											':customer_id' => $customer->id,
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
												if( $customerCredit->end_month == '02' )
												{
													$creditEndDate = date('Y-'.$customerCredit->end_month.'-28');
												}
												else
												{
													$creditEndDate = date('Y-'.$customerCredit->end_month.'-t');
												}
												
												if( $customerCredit->start_month >= $customerCredit->end_month )
												{
													$creditEndDate = date('Y-m-d', strtotime('+1 year', strtotime($creditEndDate)));
												}
											}
											else
											{
												if( $customerCredit->start_month )
												{
													$creditEndDate = date('Y-'.$customerCredit->start_month.'-28');
												}
												else
												{
													$creditEndDate = date('Y-'.$customerCredit->start_month.'-t');
												}
											}
											
											if( (strtotime('now') >= strtotime($creditStartDate)) && (strtotime('now') <= strtotime($creditEndDate)) )
											{
												$customerCreditBillingHistory = new CustomerCreditBillingHistory;
												$customerCreditBillingHistory->customer_id = $customer->id;
												$customerCreditBillingHistory->contract_id = $customerSkill->contract_id;
												$customerCreditBillingHistory->customer_credit_id = $customerCredit->id;
												$customerCreditBillingHistory->customer_billing_id = $model->id;
												$customerCreditBillingHistory->save(false);
											}
										}
									}
				
									if(!empty($customerSkill) && $customerSkill->is_hold_for_billing == 1)
									{
										$customerSkill->is_hold_for_billing = 0;
										$customerSkill->save(false);
										$content = 'Charge Successful - Status changed to active';
									}
								}
								else
								{
									$result['status'] = 'error';
									$result['message'] = 'Transaction error: ' . $response->response_reason_code . ' - ' . $response->response_reason_text;
									
									
									if(!empty($customerSkill))
									{
										$customerSkill->is_hold_for_billing = 1;
										$customerSkill->save(false);
										$content = 'Charge declined - Status changed to Declined hold.';
									}
									
									
									
								}
							}
						}
					}
					else
					{
						if($updateBillingRecord)
						{
							$updateBillingRecord->setAttributes(array(
								'anet_transId' => $response->response_code.': '.$response->response_reason_text,
								'anet_transactionType' => $response->transaction_type,
								'anet_responseCode' => $response->response_code,
								'anet_responseReasonCode'=> $response->response_reason_code,
								'anet_responseReasonDescription'=> $response->response_reason_text,
								'anet_authCode'=> $response->authorization_code,
								'anet_AVSResponse'=> $response->avs_response,
								'anet_cardCodeResponse'=> $response->card_code_response,
								'anet_authAmount'=> $response->amount,
								'anet_customer_Email'=> $anetCustomer->email,
								'anet_billTo_firstName'=> $response->first_name,
								'anet_billTo_lastName'=> $response->last_name,
								'anet_billTo_address'=> $response->address,
								'anet_billTo_city'=> $response->city,
								'anet_billTo_state'=> $response->state,
								'anet_billTo_zip'=> $response->zip_code,
								'anet_product' => $transaction_Details->product,
							));

							$updateBillingRecord->save(false);
						}
					}
					
					if( $content != '' )
					{
						$history = new CustomerHistory;
											
						$history->setAttributes(array(
							'customer_id' => $model->customer_id,
							'user_account_id' => Yii::app()->user->account->id,
							'page_name' => 'Billing Window',
							'content' => $content,
							'type' => $history::TYPE_UPDATED,
						));

						$history->save(false);
					}
				
					$result['status'] = 'success';
					$result['message'] = 'Database has been updated.';
				}
			}
			else
			{
				$result['status'] = 'zeroValue';
				$result['message'] = 'This is a $0.00 record please click ok to remove';
				$model->is_imported = 1;
				$model->save(false);
			}
		}
			
		echo json_encode($result);
	}

	public function actionAutoBilling()
	{
		Yii::import('application.vendor.*');
		require ('anet_php_sdk/AuthorizeNet.php');
		
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		$authAccount = Yii::app()->user->account;
		
		$scheduleModels = CustomerBillingScheduled::model()->findAll(array(
			// 'limit' => 50
		));
		
		$settings = CustomerBillingScheduledSettings::model()->findByPk(1);
		
		// exit;
		
		if( $scheduleModels )
		{
			if( $settings->ongoing == 0 )
			{
				$settings->ongoing = 1;
				$settings->date_ongoing = date('Y-m-d H:i:s');
				$settings->save(false);
				
				foreach( $scheduleModels as $scheduleModel )
				{
					$existingBillingForCurrentMonth = CustomerBilling::model()->find(array(
						'condition' => '
							customer_id = :customer_id 
							AND contract_id = :contract_id
							AND transaction_type = "Charge"
							AND billing_period = :billing_period
							AND ( anet_responseCode = 1 OR ( amount = 0 AND anet_responseCode IS NULL ))
						',
						'params' => array(
							':customer_id' => $scheduleModel->customer_id,
							':contract_id' => $scheduleModel->contract_id,
							':billing_period' => $scheduleModel->billing_period
						),
						'order' => 'date_created DESC'
					));
					
					$customerRemoved = CustomerBillingWindowRemoved::model()->find(array(
						'condition' => '
							customer_id = :customer_id 
							AND skill_id = :skill_id 
							AND MONTH(date_created) = :month
							AND YEAR(date_created) = :year
						',
						'params' => array(
							':customer_id' => $scheduleModel->customer_id,
							':skill_id' => $scheduleModel->skill_id,
							':month' => date('n', strtotime($scheduleModel->billing_period)),
							':year' => date('Y', strtotime($scheduleModel->billing_period))
						),
					));
					
					if( !$existingBillingForCurrentMonth && empty($customerRemoved) )
					{
						$customer = Customer::model()->find(array(
							'condition' => 'id = :id',
							'params' => array(
								':id' => $scheduleModel->customer_id,
							),
						));
						
						$customerSkill = CustomerSkill::model()->find(array(
							'condition' => 'customer_id = :customer_id AND skill_id = :skill_id',
							'params' => array(
								':customer_id' => $customer->id,
								':skill_id' => $scheduleModel->skill_id,
							),
						));
												
						$model = new CustomerBilling;
						$model->customer_id = $customer->id;		
						$model->account_id = $authAccount->id;
						$model->billing_type = 'Service Fee';
						
						// $model->is_imported = 2;
							
						if( isset($_POST['ajax']) )
						{
							$model->transaction_type = 'Charge';
							$model->billing_period = $scheduleModel->billing_period;
							$model->description = $scheduleModel->billing_period.' - '.$scheduleModel->contract;
							$model->description .= '<br>' . $scheduleModel->credit_description;
							
							$totalAmount = $scheduleModel->amount;
							
							$model->amount = number_format($totalAmount, 2);
							$model->credit_amount = $scheduleModel->credit_amount;
							$model->subsidy_amount = $scheduleModel->subsidy_amount;
							$model->original_amount = $scheduleModel->original_amount;
							// $model->contract_id = $_POST['contract'];
							$model->contract_id = $customerSkill->contract_id;
							
							$method = explode('-', $model->getDefaultMethod($scheduleModel->customer_id));
							$type = $method[0];
							$cardId = $method[1];
							
							$model->payment_method = $type;
							
							if( $type == 'creditCard' )
							{
								$defaultCreditCard = CustomerCreditCard::model()->findByPk($cardId);
								
								$model->credit_card_id = $defaultCreditCard->id;	
								$model->credit_card_type = $defaultCreditCard->credit_card_type;	
								$model->credit_card_number = $defaultCreditCard->credit_card_number;	
								$model->credit_card_security_code = $defaultCreditCard->security_code;	
								$model->credit_card_expiration_month = $defaultCreditCard->expiration_month;	
								$model->credit_card_expiration_year = $defaultCreditCard->expiration_year;	
							}
							
							if( $type == 'echeck' )
							{
								$defaultEcheck = CustomerEcheck::model()->findByPk($cardId);
								
								$model->echeck_id = $defaultEcheck->id;
								$model->ach_account_name = $defaultEcheck->account_name;
								$model->ach_account_number = $defaultEcheck->account_number;
								$model->ach_routing_number = $defaultEcheck->routing_number;
								$model->ach_account_type = $defaultEcheck->account_type;
								$model->ach_bank_name = $defaultEcheck->institution_name;
							}
							
							if( $totalAmount > 0 )
							{
								if( $model->save(false) )
								{
									$updateBillingRecord = CustomerBilling::model()->findByPk($model->id);
									
									//sandbox
									// define("AUTHORIZENET_API_LOGIN_ID", "5CzTeH72f98D");	
									// define("AUTHORIZENET_TRANSACTION_KEY", "8n25bCL72432SpR9");	
									// define("AUTHORIZENET_SANDBOX", true);
									
									//live
									define("AUTHORIZENET_API_LOGIN_ID", "8Wtz3C6a");	
									define("AUTHORIZENET_TRANSACTION_KEY", "8YEBD7n3Z64r3x39");
									define("AUTHORIZENET_SANDBOX", false);
									
									$authorizeTransaction = new AuthorizeNetAIM;
									
									$authorizeTransaction->setSandbox(AUTHORIZENET_SANDBOX);

									if( $model->payment_method == 'creditCard' )
									{
										$authorizeTransaction->setFields(array(
											// 'invoice_num' => ,
											'amount' => number_format($totalAmount, 2),
											'first_name' => $defaultCreditCard->first_name,
											'last_name' => $defaultCreditCard->last_name,
											'email' => $customer->email_address,
											'card_num' => $defaultCreditCard->credit_card_number, 
											'card_code' => $defaultCreditCard->security_code,
											'exp_date' => $defaultCreditCard->expiration_month . $defaultCreditCard->expiration_year,
											'address' => $defaultCreditCard->address,
											'city' => $defaultCreditCard->city,
											'state' => $defaultCreditCard->state,
											'zip' => $defaultCreditCard->zip,
										));
									}
									
									if( $model->payment_method == 'echeck' )
									{
										$authorizeTransaction->amount = number_format($totalAmount, 2);
													
										$authorizeTransaction->setECheck(
											$defaultEcheck->routing_number,
											$defaultEcheck->account_number,
											$defaultEcheck->account_type,
											$defaultEcheck->institution_name,
											$defaultEcheck->account_name,
											'WEB'
										);
									}
									
									$response = $authorizeTransaction->authorizeAndCapture();
													
									$request  = new AuthorizeNetTD;
									$response_TransactionDetails = $request->getTransactionDetails($response->transaction_id);
									
									$content = '';
									if( $response->approved )
									{
										$transaction_Details = $response_TransactionDetails->xml->transaction;
										$order = $transaction_Details->order;
										$anetCustomer = $transaction_Details->customer;
										$billTo = $transaction_Details->billTo;
										
										$billing_Date = date('Y-m-d H:i:s', strtotime($transaction_Details->submitTimeUTC));
										
										if($updateBillingRecord)
										{
											$updateBillingRecord->setAttributes(array(
												'anet_transId' => $transaction_Details->transId,
												'anet_invoiceNumber' => $order->invoiceNumber,
												'anet_submitTimeUTC' => $transaction_Details->submitTimeUTC,
												'anet_submitTimeLocal' => $transaction_Details->submitTimeLocal,
												'anet_transactionType' => $transaction_Details->transactionType,
												'anet_transactionStatus' =>$transaction_Details->transactionStatus,
												'anet_responseCode' => $transaction_Details->responseCode,
												'anet_responseReasonCode'=> $transaction_Details->responseReasonCode,
												'anet_responseReasonDescription'=> $transaction_Details->responseReasonDescription,
												'anet_authCode'=> $transaction_Details->authCode,
												'anet_AVSResponse'=> $transaction_Details->AVSResponse,
												'anet_cardCodeResponse'=> $transaction_Details->cardCodeResponse,
												'anet_authAmount'=> $transaction_Details->authAmount,
												'anet_settleAmount'=> $transaction_Details->settleAmount,
												'anet_taxExempt'=> $transaction_Details->taxExempt,
												'anet_customer_Email'=> $anetCustomer->email,
												'anet_billTo_firstName'=> $billTo->firstName,
												'anet_billTo_lastName'=> $billTo->lastName,
												'anet_billTo_address'=> $billTo->address,
												'anet_billTo_city'=> $billTo->city,
												'anet_billTo_state'=> $billTo->state,
												'anet_billTo_zip'=> $billTo->zip,
												'anet_recurringBilling' => $transaction_Details->recurringBilling,
												'anet_product' => $transaction_Details->product,
												'anet_marketType' => $transaction_Details->marketType
											));
											
											if($updateBillingRecord->save(false))
											{		
												if( $response_TransactionDetails->xml->messages->resultCode == 'Ok' )
												{
													// Transaction approved! Do your logic here.
													$result['status'] = 'success';
													$result['message'] = 'Charge successful';
													
													//save customer credit used for the Customer Billing (the total of all the credits were already submitted in the view)
													$customerCredits = CustomerCredit::model()->findAll(array(
														'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND status=1',
														'params' => array(
															':customer_id' => $customer->id,
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
																if( $customerCredit->end_month == '02' )
																{
																	$creditEndDate = date('Y-'.$customerCredit->end_month.'-28');
																}
																else
																{
																	$creditEndDate = date('Y-'.$customerCredit->end_month.'-t');
																}
																
																if( $customerCredit->start_month >= $customerCredit->end_month )
																{
																	$creditEndDate = date('Y-m-d', strtotime('+1 year', strtotime($creditEndDate)));
																}
															}
															else
															{
																if( $customerCredit->start_month )
																{
																	$creditEndDate = date('Y-'.$customerCredit->start_month.'-28');
																}
																else
																{
																	$creditEndDate = date('Y-'.$customerCredit->start_month.'-t');
																}
															}
															
															if( (strtotime('now') >= strtotime($creditStartDate)) && (strtotime('now') <= strtotime($creditEndDate)) )
															{
																$customerCreditBillingHistory = new CustomerCreditBillingHistory;
																$customerCreditBillingHistory->customer_id = $customer->id;
																$customerCreditBillingHistory->contract_id = $customerSkill->contract_id;
																$customerCreditBillingHistory->customer_credit_id = $customerCredit->id;
																$customerCreditBillingHistory->customer_billing_id = $model->id;
																$customerCreditBillingHistory->save(false);
															}
														}
													}
								
													if(!empty($customerSkill) && $customerSkill->is_hold_for_billing == 1)
													{
														$customerSkill->is_hold_for_billing = 0;
														$customerSkill->save(false);
														$content = 'Charge Successful - Status changed to active';
													}
												}
												else
												{
													$result['status'] = 'error';
													$result['message'] = 'Transaction error: ' . $response->response_reason_code . ' - ' . $response->response_reason_text;
													
													
													if(!empty($customerSkill))
													{
														$customerSkill->is_hold_for_billing = 1;
														$customerSkill->save(false);
														$content = 'Charge declined - Status changed to Declined hold.';
													}
													
													
													
												}
											}
										}
									}
									else
									{
										if($updateBillingRecord)
										{
											$updateBillingRecord->setAttributes(array(
												'anet_transId' => $response->response_code.': '.$response->response_reason_text,
												'anet_transactionType' => $response->transaction_type,
												'anet_responseCode' => $response->response_code,
												'anet_responseReasonCode'=> $response->response_reason_code,
												'anet_responseReasonDescription'=> $response->response_reason_text,
												'anet_authCode'=> $response->authorization_code,
												'anet_AVSResponse'=> $response->avs_response,
												'anet_cardCodeResponse'=> $response->card_code_response,
												'anet_authAmount'=> $response->amount,
												'anet_customer_Email'=> $anetCustomer->email,
												'anet_billTo_firstName'=> $response->first_name,
												'anet_billTo_lastName'=> $response->last_name,
												'anet_billTo_address'=> $response->address,
												'anet_billTo_city'=> $response->city,
												'anet_billTo_state'=> $response->state,
												'anet_billTo_zip'=> $response->zip_code,
												'anet_product' => $transaction_Details->product,
											));

											$updateBillingRecord->save(false);
										}
									}
									
									if( $content != '' )
									{
										$history = new CustomerHistory;
															
										$history->setAttributes(array(
											'customer_id' => $model->customer_id,
											'user_account_id' => Yii::app()->user->account->id,
											'page_name' => 'Billing Window',
											'content' => $content,
											'type' => $history::TYPE_UPDATED,
										));

										$history->save(false);
									}
								
									$result['status'] = 'success';
									$result['message'] = 'Database has been updated.';
								}
							}
							else
							{
								$result['status'] = 'zeroValue';
								$result['message'] = 'This is a $0.00 record please click ok to remove';
								$model->is_imported = 1;
								$model->save(false);
							}
						}
						
					}
				}
			
				$settings->ongoing = 0;
				$settings->date_completed = date('Y-m-d H:i:s');
				$settings->save(false);
			}
		}
			
		echo json_encode($result);
	}

	public function actionScheduleBilling()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		$model = CustomerBillingScheduledSettings::model()->findByPk(1);

		if( isset($_POST['CustomerBillingScheduledSettings']) )
		{
			$model->attributes = $_POST['CustomerBillingScheduledSettings'];
			
			echo '<pre>';
				print_r($model->attributes);
			exit;
			
			if( $model->save(false) )
			{
				
			}
		}
		
		if( isset($_POST['ajax']) )
		{
			$html = $this->renderPartial('ajax_schedule_billing', array(
				'model' => $model,
			), true);

			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}
}

?>