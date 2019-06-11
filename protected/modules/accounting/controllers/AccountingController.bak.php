<?php 

class AccountingController extends Controller
{
	
	public function actionIndex()
	{
		$payPeriodOptions = array();
		
		foreach( range(2015, 2016) as $year)
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
		
		foreach( range(2015, 2016) as $year)
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
			'E' => 'Regular Hours',
			'F' => 'O/T Hours',
			'G' => 'Hours 3 Code',
			'H' => 'Hours 3 Amount',	 
			'I' => 'Hours 3 Code',	 
			'J' => 'Hours 3 Amount',	 
			'K' => 'Hours 3 Code',	
			'L' => 'Hours 3 Amount',	 
			'M' => 'Earnings 3 Code', 
			'N' => 'Earnings 3 Amount',	 
			'O' => 'Earnings 3 Code',	 
			'P' => 'Earnings 3 Amount',
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
		
		$startDate =  $year.'-'.$month.'-'.$start_day;
		$endDate =  $year.'-'.$month.'-'.$end_day;
		
		
		$accounts = Account::model()->findAll(array(
			'together' => true,
			'with' => 'accountUser',
			'condition' => 'accountUser.id IS NOT NULL',
		));
		
		//  AND t.id IN ("162")"1194", 
		
		if( $accounts )
		{		
			$ctr = 4;
			
			foreach( $accounts as $account )
			{
				$totalHours = 0;
				$totalMinutes = 0;
						
				$totalRegularMinutes = 0;
				$regularHours = '00:00';
				
				$totalOtMinutes = 0;
				$otHours = '00:00';
				
				$loginRecords = AccountLoginTracker::model()->findAll(array(
					'condition' => 'account_id = :account_id AND time_in >= :start_date AND time_in <= :end_date AND status=1',
					'params' => array(
						':account_id' => $account->id,
						':start_date' => date('Y-m-d 00:00:00', strtotime($startDate)),
						':end_date' => date('Y-m-d 23:59:59', strtotime($endDate)),
					),
					'order' => 'date_created DESC',
				));
				
				if( $loginRecords ) 
				{
					foreach( $loginRecords as $loginRecord )
					{
						$existingPtoRequest = AccountPtoRequest::model()->find(array(
							'condition' => 'STR_TO_DATE(request_date, "%m/%d/%Y") = :loginDate AND status=1',
							'params' => array(
								':loginDate' => date('Y-m-d', strtotime($loginRecord->date_created)),
							),
						));
						
						$timeIn = new DateTime($loginRecord->time_in, new DateTimeZone('America/Chicago'));
						$timeIn->setTimezone(new DateTimeZone('America/Denver'));
						
						if( $loginRecord->type == 1 )
						{
							$timeOut = new DateTime($loginRecord->time_out, new DateTimeZone('America/Chicago'));
							$timeOut->setTimezone(new DateTimeZone('America/Denver'));
						}
						else
						{
							$timeOut = new DateTime($loginRecord->time_out);
						}
						
						// $interval = $timeIn->diff($timeOut);
					
						// echo date('m/d/Y g:i A', strtotime($loginRecord->time_in)).' - '.date('m/d/Y g:i A', strtotime($loginRecord->time_out));
						// echo '<br>';
						// echo 'hours: ' . $interval->format('%H');
						// echo '<br>';
						// echo  'minutes: ' . $interval->format('%I');
						// echo '<br>';
						// echo '<br>';
	

						if( $loginRecord->time_out != null )
						{
							$totalMinutes += round(abs(strtotime($loginRecord->time_in) - strtotime($loginRecord->time_out)) / 60,2);
							
							// if( $existingPtoRequest )
							// {
								// $ptoStart = new DateTime($existingPtoRequest->start_time);
								// $ptoEnd = new DateTime($existingPtoRequest->end_time);
								
								// $ptoInterval = $ptoStart->diff($ptoEnd);
								
								// $totalMinutes += abs( $interval->format('%I') - $ptoInterval->format('%I') );
							// }
							// else
							// {
								// $totalHours += $interval->format('%H');
								// $totalMinutes += $interval->format('%I');
							// }
							
	
							$totalRegularMinutes += round(abs(strtotime($loginRecord->time_in) - strtotime($loginRecord->time_out)) / 60,2);
						}
					}
				}
		
				$totalHours =  floor($totalMinutes/60);
				$totalMinutes =   $totalMinutes % 60;
				
				if( $totalRegularMinutes > 4800 )
				{
					$totalOtMinutes = $totalRegularMinutes - 4800;
					$totalRegularMinutes = 4800;
				}
				
				$regularHours = floor($totalRegularMinutes/60);
				$totalRegularMinutes = $totalRegularMinutes % 60;
				
				$otHours = floor($totalOtMinutes/60);
				$totalOtMinutes = $totalOtMinutes % 60;

				if( strlen($totalHours) == 1)
				{
					$totalHours = '0'.$totalHours;
				}
				
				if( strlen($totalMinutes) == 1)
				{
					$totalMinutes = '0'.$totalMinutes;
				}
				
				if( strlen($regularHours) == 1)
				{
					$regularHours = '0'.$regularHours;
				}
				
				if( strlen($totalRegularMinutes) == 1)
				{
					$totalRegularMinutes = '0'.$totalRegularMinutes;
				}
				
				$regularHours = $regularHours.':'.$totalRegularMinutes;
				
				if( strlen($otHours) == 1)
				{
					$otHours = '0'.$otHours;
				}
				
				if( strlen($totalOtMinutes) == 1)
				{
					$totalOtMinutes = '0'.$totalOtMinutes;
				}
				
				$otHours = $otHours.':'.$totalOtMinutes;
				
				// echo $account->accountUser->getFullName();
				
				// echo '<br>';
				// echo '<br>';
				
				// echo 'count: ' . count($loginRecords);
				
				// echo '<br>';
				// echo '<br>';
				
				// echo 'totalHours: ' . $totalHours;
				
				// echo '<br>';
				// echo '<br>';
				
				// echo 'totalMinutes: ' . $totalMinutes;
				
				// echo '<br>';
				// echo '<br>';
				
				// echo 'totalRegularHours: ' . $regularHours;
				
				// echo '<br>';
				// echo '<br>';
				
				// echo 'totalRegularMinutes: ' . $totalRegularMinutes % 60;
				
				// echo '<br>';
				// echo '<br>';
				
				// echo 'totalOtHours: ' .  $otHours;
				
				// echo '<br>';
				// echo '<br>';
				
				// echo 'totalOtMinutes: ' .  $totalOtMinutes % 60;
				
				// exit;
			
				$employeeNumber = (string) $account->accountUser->employee_number;
			
				$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, 'APW');
			
				$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, date('md') . substr(date('Y'), -3) );
				
				$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $employeeNumber);
				$objPHPExcel->getActiveSheet()->getStyle('C'.$ctr)->getNumberFormat()->setFormatCode('000000');
				
				$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $account->accountUser->getFullName() );
				
				$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $regularHours );
				
				$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $otHours);
				
				
				$ctr++;
			}
		}

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
		
		foreach( range(2015, 2016) as $year)
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
	
}

?>