<?php 

class ReportsController extends Controller
{
	
	public function actionIndex($customer_id)
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
		
		
		$customer = Customer::model()->findByPk($customer_id);
		
		$this->render('index', array(
			'customer' => $customer,
		));
	}
	
	public function actionGenerateCustomerReport($customer_id)
	{
		ini_set('memory_limit', '512M');
		set_time_limit(0);
		
		$customer = Customer::model()->findByPk($customer_id);
		
		// $appointments = CalendarAppointment::model()->findAll(array(
			// 'with' => 'calendar',
			// 'condition' => 'calendar.customer_id = :customer_id AND t.title IN ("INSERT APPOINTMENT", "APPOINTMENT SET", "CANCEL APPOINTMENT", "RESCHEDULE APPOINTMENT", "NO SHOW RESCHEDULE") AND t.lead_id IS NOT NULL AND MONTH(t.date_created) = MONTH(NOW()) AND YEAR(t.date_created) = YEAR(NOW())',
			// 'params' => array(
				// 'customer_id' => $customer_id,
			// ),
			// 'group' => 't.lead_id',
		// ));
		
		##get Appointment that has been scheduled ##
		$appointmentSetMTDSql = "
			SELECT ca.start_date, ca.title, lch.lead_id, ld.first_name, ld.last_name
			FROM ud_lead_call_history lch 
			INNER JOIN ud_calendar_appointment ca ON ca.id = lch.calendar_appointment_id
			INNER JOIN ud_lead ld ON ld.id = lch.lead_id
			WHERE ca.title IN ('APPOINTMENT SET', 'CANCEL APPOINTMENT', 'RESCHEDULE APPOINTMENT', 'NO SHOW RESCHEDULE') 
			AND lch.disposition = 'Appointment Set'
			AND lch.date_created >= '".date('Y-m-01 00:00:00')."' 
			AND lch.date_created <= '".date('Y-m-t 23:59:59')."'
			AND lch.customer_id = '".$customer->id."'
			GROUP BY lch.lead_id
			ORDER BY ld.last_name ASC 
		";

		$command = Yii::app()->db->createCommand($appointmentSetMTDSql);
		$appointmentSets = $command->queryAll();

		$appointments = array();
		$noShowCtr = 0;
		
		if( $appointmentSets )
		{
			foreach( $appointmentSets as $appointmentSet)
			{
				if( $appointmentSet['title'] == 'NO SHOW RESCHEDULE')
				{
					$noShowCtr++;
				}
				
				if( ($appointmentSet['title'] == 'NO SHOW RESCHEDULE' && $noShowCtr > 3) || $appointmentSet['title'] != 'NO SHOW RESCHEDULE' )
				{
					$appointments[] = array(
						'title' => $appointmentSet['title'],
						'start_date' => $appointmentSet['start_date'],
						'lead_id' => $appointmentSet['lead_id'],
						'first_name' => $appointmentSet['first_name'],
						'last_name' => $appointmentSet['last_name'],
					);
				}
			}
		}
		
		$insertAppointmentMTDSql = "
			SELECT ca.start_date, ca.title, ca.lead_id, ld.first_name, ld.last_name
			FROM ud_calendar_appointment ca
			LEFT JOIN ud_lead ld ON ld.id = ca.lead_id
			LEFT JOIN ud_lists ls ON ls.id = ld.list_id
			WHERE ca.title = 'INSERT APPOINTMENT' 
			AND ca.status != 4
			AND ca.date_created >= '".date('Y-m-01 00:00:00')."' 
			AND ca.date_created <= '".date('Y-m-t 23:59:59')."'				
			AND ld.customer_id = '".$customer->id."'						  
		";
		
		$command = Yii::app()->db->createCommand($insertAppointmentMTDSql);
		$insertAppointments = $command->queryAll();
		
		if( $insertAppointments )
		{
			foreach( $insertAppointments as $insertAppointment )
			{
				$appointments[] = array(
					'title' => $insertAppointment['title'],
					'start_date' => $insertAppointment['start_date'],
					'lead_id' => $insertAppointment['lead_id'],
					'first_name' => $insertAppointment['first_name'],
					'last_name' => $insertAppointment['last_name'],
				);
			}
		}
		
		// echo 'noShowCtr: ' . $noShowCtr;
		// echo '<br>';
		// echo 'appointments count: ' . count($appointments);
		
		// echo '<br>';
		// echo '<br>';
		
		// if( $appointments )
		// {
			// $ctr = 1;
			
			// foreach( $appointments as $appointment )
			// {
				// echo $ctr.' - '.$appointment['first_name'].' '.$appointment['last_name'];
				// echo '<br>';
				
				// $ctr++;
			// }
		// }
		
		// exit;
		
		$conflicts = CalendarAppointment::model()->findAll(array(
			'with' => 'calendar',
			'condition' => '
				calendar.customer_id = :customer_id 
				AND t.title IN ("SCHEDULE CONFLICT", "LOCATION CONFLICT") 
				AND t.status=2 AND MONTH(t.date_created) = MONTH(NOW()) 
				AND YEAR(t.date_created) = YEAR(NOW())
			',
			'params' => array(
				'customer_id' => $customer_id,
			),
		));
		
		$calls = LeadCallHistory::model()->findAll(array(
			'with' => 'lead',
			'condition' => '
				t.customer_id = :customer_id
				AND t.disposition IS NOT NULL 
				AND lead.status != 4 
				AND t.status!=4 
				AND MONTH(t.date_created) = MONTH(NOW()) AND YEAR(t.date_created) = YEAR(NOW())
			',
			'params' => array(
				':customer_id' => $customer_id,
			),
			'order' => 'lead.last_name ASC',
		));
		
		$dispositions = array();
 		
		if( $calls )
		{
			foreach( $calls as $call )
			{
				if( $call->is_skill_child == 0 )
				{
					if( !in_array($call->skillDisposition->skill_disposition_name, $dispositions) && $call->skillDisposition->is_visible_on_report == 1)
					{
				
						$dispositions[] = $call->skillDisposition->skill_disposition_name;
					}
				}
				else
				{
					if( !in_array($call->skillChildDisposition->skill_child_disposition_name, $dispositions) )
					{
				
						$dispositions[] = $call->skillChildDisposition->skill_child_disposition_name;
					}
				}
			}
		}

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
		$pdf->SetFont('freesans', '', 11);

		$pdf->AddPage();
		

		//Write the html
		$html = $this->renderPartial('customerReportLayout', array(
			'customer'=>$customer,
			'appointments' => $appointments,
			'conflicts' => $conflicts,
			'calls' => $calls,
			'dispositions' => $dispositions,
		), true);
		
		//Convert the Html to a pdf document
		$pdf->writeHTML($html, true, false, true, false, '');
		
		// reset pointer to the last page
		$pdf->lastPage();

		//Close and output PDF document
		$pdf->Output( $customer->getFullName() . '.pdf', 'I');
		Yii::app()->end();
	}
	
	
	public function actionGenerateCustomerMonthlyReport($customer_id, $year, $month)
	{
		ini_set('memory_limit', '512M');
		set_time_limit(0);
		
		$customer = Customer::model()->findByPk($customer_id);
		
		$start_date = date($year.'-'.$month.'-1 00:00:00');
				
		$end_date = date($year.'-'.$month.'-31 23:59:59');
		
		// $appointments = CalendarAppointment::model()->findAll(array(
			// 'with' => 'calendar',
			// 'condition' => 'calendar.customer_id = :customer_id AND t.title IN ("INSERT APPOINTMENT", "APPOINTMENT SET", "CANCEL APPOINTMENT", "RESCHEDULE APPOINTMENT", "NO SHOW RESCHEDULE") AND t.lead_id IS NOT NULL AND t.date_updated >= "'.$start_date.'" AND t.date_updated <= "'.$end_date.'"',
			// 'params' => array(
				// 'customer_id' => $customer_id,
			// ),
			// 'group' => 't.lead_id',
		// ));
		
		##get Appointment that has been scheduled ##
		$appointmentSetMTDSql = "
			SELECT ca.start_date, ca.title, lch.lead_id, ld.first_name, ld.last_name
			FROM ud_lead_call_history lch 
			INNER JOIN ud_calendar_appointment ca ON ca.id = lch.calendar_appointment_id
			INNER JOIN ud_lead ld ON ld.id = lch.lead_id
			WHERE ca.title IN ('APPOINTMENT SET', 'CANCEL APPOINTMENT', 'RESCHEDULE APPOINTMENT', 'NO SHOW RESCHEDULE') 
			AND lch.disposition = 'Appointment Set'
			AND lch.date_created >= '".$start_date."' 
			AND lch.date_created <= '".$end_date."'
			AND lch.customer_id = '".$customer_id."'
			GROUP BY lch.lead_id
			ORDER BY ld.last_name ASC 
		";

		$command = Yii::app()->db->createCommand($appointmentSetMTDSql);
		$appointmentSets = $command->queryAll();
		
		$appointments = array();
		
		$noShowCtr = 0;
		
		if( $appointmentSets )
		{
			foreach( $appointmentSets as $appointmentSet)
			{
				if( $appointmentSet['title'] == 'NO SHOW RESCHEDULE')
				{
					$noShowCtr++;
				}
				
				if( ($appointmentSet['title'] == 'NO SHOW RESCHEDULE' && $noShowCtr > 3) || $appointmentSet['title'] != 'NO SHOW RESCHEDULE' )
				{
					$appointments[] = array(
						'title' => $appointmentSet['title'],
						'start_date' => $appointmentSet['start_date'],
						'lead_id' => $appointmentSet['lead_id'],
						'first_name' => $appointmentSet['first_name'],
						'last_name' => $appointmentSet['last_name'],
					);
				}
			}
		}
		
		$insertAppointmentMTDSql = "
			SELECT ca.start_date, ca.title, ca.lead_id, ld.first_name, ld.last_name
			FROM ud_calendar_appointment ca
			LEFT JOIN ud_calendar c ON c.id = ca.calendar_id
			LEFT JOIN ud_lead ld ON ld.id = ca.lead_id
			LEFT JOIN ud_lists ls ON ls.id = ld.list_id
			WHERE ca.title = 'INSERT APPOINTMENT' 
			AND ca.status != 4
			AND ca.date_created >= '".$start_date."' 
			AND ca.date_created <= '".$end_date."'
			AND c.customer_id = '".$customer_id."'		  
		";
		
		$command = Yii::app()->db->createCommand($insertAppointmentMTDSql);
		$insertAppointments = $command->queryAll();
		
		if( $insertAppointments )
		{
			foreach( $insertAppointments as $insertAppointment )
			{
				$appointments[] = array(
					'title' => $insertAppointment['title'],
					'start_date' => $insertAppointment['start_date'],
					'lead_id' => $insertAppointment['lead_id'],
					'first_name' => $insertAppointment['first_name'],
					'last_name' => $insertAppointment['last_name'],
				);
			}
		}
		
		$conflicts = CalendarAppointment::model()->findAll(array(
			'with' => 'calendar',
			'condition' => 'calendar.customer_id = :customer_id AND t.title IN ("SCHEDULE CONFLICT", "LOCATION CONFLICT") AND t.status=2 AND t.start_date >= "'.$start_date.'" AND t.start_date <= "'.$end_date.'"',
			'params' => array(
				'customer_id' => $customer_id,
			),
		));
		
		$calls = LeadCallHistory::model()->findAll(array(
			'with' => 'lead',
			'condition' => 't.customer_id = :customer_id AND t.status!=4 AND t.lead_id IS NOT NULL AND t.date_created >= "'.$start_date.'" AND t.date_created <= "'.$end_date.'"',
			'params' => array(
				':customer_id' => $customer_id,
			),
			'order' => 'lead.last_name ASC',
		));
		
		$dispositions = array();
 		
		if( $calls )
		{
			foreach( $calls as $call )
			{
				if( $call->is_skill_child == 0 )
				{
					if( !in_array($call->skillDisposition->skill_disposition_name, $dispositions) && $call->skillDisposition->is_visible_on_report == 1)
					{
				
						$dispositions[] = $call->skillDisposition->skill_disposition_name;
					}
				}
				else
				{
					if( !in_array($call->skillChildDisposition->skill_child_disposition_name, $dispositions) )
					{
				
						$dispositions[] = $call->skillChildDisposition->skill_child_disposition_name;
					}
				}
			}
		}
		
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
		$pdf->SetFont('freesans', '', 11);

		$pdf->AddPage();
		

		//Write the html
		$html = $this->renderPartial('customerMonthlyReportLayout', array(
			'customer'=>$customer,
			'appointments' => $appointments,
			'conflicts' => $conflicts,
			'calls' => $calls,
			'dispositions' => $dispositions,
			'start_date' => $start_date,
			'end_date' => $end_date,
		), true);
		
		//Convert the Html to a pdf document
		$pdf->writeHTML($html, true, false, true, false, '');
		
		// reset pointer to the last page
		$pdf->lastPage();

		//Close and output PDF document
		$pdf->Output( $customer->getFullName() . '.pdf', 'I');
		Yii::app()->end();
	}

	
	public function actionGenerateCustomerMonthlyWnDncReport($customer_id, $year, $month)
	{
		ini_set('memory_limit', '512M');
		set_time_limit(0);
		
		$customer = Customer::model()->findByPk($customer_id);
		
		$skills = array();
											
		$customerSkills = CustomerSkill::model()->findAll(array(
			'condition' => 'customer_id = :customer_id AND status=1',
			'params' => array(
				':customer_id' => $customer->id,
			),
		));

		if( $customerSkills )
		{
			foreach( $customerSkills as $customerSkills )
			{
				$skills[] = $customerSkills->skill->skill_name;
			}
		}
		
		$start_date = date($year.'-'.$month.'-1 00:00:00');
				
		$end_date = date($year.'-'.$month.'-31 23:59:59');
		
		$calls = LeadCallHistory::model()->findAll(array(
			'with' => 'lead',
			'condition' => '
				t.customer_id = :customer_id 
				AND t.status!=4 
				AND t.lead_id IS NOT NULL 
				AND t.date_created >= "'.$start_date.'" 
				AND t.date_created <= "'.$end_date.'"
				AND t.disposition IN ("Wrong Number", "Do Not Call")
			',
			'params' => array(
				':customer_id' => $customer_id,
			),
			'order' => 'lead.last_name ASC',
		));
		
		
		$dispositions = array();
 		
		if( $calls )
		{
			foreach( $calls as $call )
			{
				if( $call->is_skill_child == 0 )
				{
					if( !in_array($call->skillDisposition->skill_disposition_name, $dispositions) && $call->skillDisposition->is_visible_on_report == 1)
					{
				
						$dispositions[] = $call->skillDisposition->skill_disposition_name;
					}
				}
				else
				{
					if( !in_array($call->skillChildDisposition->skill_child_disposition_name, $dispositions) )
					{
				
						$dispositions[] = $call->skillChildDisposition->skill_child_disposition_name;
					}
				}
			}
		}
		
		
		if( $_REQUEST['exportType'] == 'excel' )
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
			
			$filename = 'Monthly Wrong Number & Do Not Call Report';
			
			$callableLead = CustomerCallableLeadCount::model()->find(array(
				'condition' => 'customer_id = :customer_id AND YEAR(date_created) = :report_year AND MONTH(date_created) = :report_month',
				'params' => array(
					':customer_id' => $customer->id,
					':report_year' => date('Y', strtotime($start_date)),
					':report_month' => date('m', strtotime($start_date))
				),
			));

			$skillTxt = !empty( $skills ) ? implode(', ', $skills) : '';
			
			$objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Name: ' . strtoupper($customer->getFullName()));
			$objPHPExcel->getActiveSheet()->SetCellValue('A2', 'Skill: ' . $skillTxt);
			$objPHPExcel->getActiveSheet()->SetCellValue('A3', 'Report Range: ' . date('m/d/Y', strtotime($start_date)).' - '.date('m/d/Y', strtotime($end_date)));
			
			if( $callableLead )
			{
				$objPHPExcel->getActiveSheet()->SetCellValue('A4', 'Callable leads on the Month 1st: ' . $callableLead->callable_leads);
			}
			else
			{
				$objPHPExcel->getActiveSheet()->SetCellValue('A4', 'Callable leads on the Month 1st: 0');
			}
			
			
			$objPHPExcel->getActiveSheet()->SetCellValue('A6', 'Dispositions');
			$objPHPExcel->getActiveSheet()->getStyle('A6')->applyFromArray(array(
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				),
				'font'  => array(
					'bold' => true,
					'name'  => 'Calibri',
				),
			));
			
			$ctr = 7;
			
			if( $dispositions )
			{
				foreach( $dispositions as $disposition )
				{
					$dispositionCount = LeadCallHistory::model()->count(array(
						'condition' => 'disposition = :disposition AND customer_id = :customer_id AND t.status!=4 AND t.lead_id IS NOT NULL AND t.date_created >= "'.$start_date.'" AND t.date_created <= "'.$end_date.'"',
						'params' => array(
							':disposition' => $disposition,
							':customer_id' => $customer->id,
						),
					));
					
					$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $disposition);
					$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $dispositionCount);
					
					$ctr++;
				}
			}
			
			$ctr = $ctr + 1;
			
			$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, 'Call History');
			$objPHPExcel->getActiveSheet()->getStyle('A'.$ctr)->applyFromArray(array(
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				),
				'font'  => array(
					'bold' => true,
					'name'  => 'Calibri',
				),
			));
			
			$ctr = $ctr + 1;
			
			if( $calls )
			{
				foreach( $calls as $call )
				{
					$valid = true;
					
					if( $call->is_skill_child == 0 )
					{
						if( $call->skillDisposition->is_visible_on_report == 1)
						{
					
							$valid = true;
						}
						else
						{
							$valid = false;
						}
					}
					
					if( $valid )
					{
						$callTime = new DateTime($call->start_call_time, new DateTimeZone('America/Chicago'));
						$callTime->setTimezone(new DateTimeZone('America/Denver'));	

						
						$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $call->lead->last_name.', '.$call->lead->first_name);
						
						if( $call->is_skill_child == 0 )
						{
							if( isset( $call->skillDisposition) )
							{
								$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $call->skillDisposition->skill_disposition_name);
							}
						}
						else
						{
							if( $call->skillChildDisposition )
							{
								$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $call->skillChildDisposition->skill_child_disposition_name);
							}
						}
						
						$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $callTime->format('m/d/Y g:i A'));
					}
					
					$ctr++;	
				}
			}
			
			header('Content-Type: application/vnd.ms-excel'); 
			header('Content-Disposition: attachment; filename="'.$filename.'.xlsx"'); 
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			
			header('Cache-Control: max-age=0');
		
			$objWriter->save('php://output');
		}
		else
		{
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
			$pdf->SetFont('freesans', '', 11);

			$pdf->AddPage();
			

			//Write the html
			$html = $this->renderPartial('customerMonthlyWnDncReportLayout', array(
				'customer'=>$customer,
				'calls' => $calls,
				'dispositions' => $dispositions,
				'start_date' => $start_date,
				'end_date' => $end_date,
				'skills' => $skills,
			), true);
			
			//Convert the Html to a pdf document
			$pdf->writeHTML($html, true, false, true, false, '');
			
			// reset pointer to the last page
			$pdf->lastPage();

			//Close and output PDF document
			$pdf->Output( $customer->getFullName() . '.pdf', 'I');
		}
		
		Yii::app()->end();
	}

	public function actionGenerateCustomerAllWnDncReport($customer_id)
	{
		ini_set('memory_limit', '512M');
		set_time_limit(0);
		
		$customer = Customer::model()->findByPk($customer_id);
		
		$skills = array();
											
		$customerSkills = CustomerSkill::model()->findAll(array(
			'condition' => 'customer_id = :customer_id AND status=1',
			'params' => array(
				':customer_id' => $customer->id,
			),
		));

		if( $customerSkills )
		{
			foreach( $customerSkills as $customerSkills )
			{
				$skills[] = $customerSkills->skill->skill_name;
			}
		}
		
		$calls = LeadCallHistory::model()->findAll(array(
			'with' => 'lead',
			'condition' => '
				t.customer_id = :customer_id 
				AND t.status!=4 
				AND t.lead_id IS NOT NULL 
				AND t.disposition IN ("Wrong Number", "Do Not Call")
			',
			'params' => array(
				':customer_id' => $customer_id,
			),
			'order' => 'lead.last_name ASC',
		));
		
		$dispositions = array();
 		
		if( $calls )
		{
			foreach( $calls as $call )
			{
				if( $call->is_skill_child == 0 )
				{
					if( !in_array($call->skillDisposition->skill_disposition_name, $dispositions) && $call->skillDisposition->is_visible_on_report == 1)
					{
				
						$dispositions[] = $call->skillDisposition->skill_disposition_name;
					}
				}
				else
				{
					if( !in_array($call->skillChildDisposition->skill_child_disposition_name, $dispositions) )
					{
				
						$dispositions[] = $call->skillChildDisposition->skill_child_disposition_name;
					}
				}
			}
		}
		
		if( $_REQUEST['exportType'] == 'excel' )
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
			
			$filename = 'Monthly Wrong Number & Do Not Call Report';
			
			$callableLead = CustomerCallableLeadCount::model()->find(array(
				'condition' => 'customer_id = :customer_id AND YEAR(date_created) = :report_year AND MONTH(date_created) = :report_month',
				'params' => array(
					':customer_id' => $customer->id,
					':report_year' => date('Y', strtotime($start_date)),
					':report_month' => date('m', strtotime($start_date))
				),
			));

			$skillTxt = !empty( $skills ) ? implode(', ', $skills) : '';
			
			$objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Name: ' . strtoupper($customer->getFullName()));
			$objPHPExcel->getActiveSheet()->SetCellValue('A2', 'Skill: ' . $skillTxt);	
			
			$objPHPExcel->getActiveSheet()->SetCellValue('A4', 'Dispositions');
			$objPHPExcel->getActiveSheet()->getStyle('A4')->applyFromArray(array(
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				),
				'font'  => array(
					'bold' => true,
					'name'  => 'Calibri',
				),
			));
			
			$ctr = 5;
			
			if( $dispositions )
			{
				foreach( $dispositions as $disposition )
				{
					$dispositionCount = LeadCallHistory::model()->count(array(
						'condition' => 'disposition = :disposition AND customer_id = :customer_id AND t.status!=4 AND t.lead_id IS NOT NULL',
						'params' => array(
							':disposition' => $disposition,
							':customer_id' => $customer->id,
						),
					));
					
					$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $disposition);
					$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $dispositionCount);
					
					$ctr++;
				}
			}
			
			$ctr = $ctr + 1;
			
			$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, 'Call History');
			$objPHPExcel->getActiveSheet()->getStyle('A'.$ctr)->applyFromArray(array(
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				),
				'font'  => array(
					'bold' => true,
					'name'  => 'Calibri',
				),
			));
			
			$ctr = $ctr + 1;
			
			if( $calls )
			{
				foreach( $calls as $call )
				{
					$valid = true;
					
					if( $call->is_skill_child == 0 )
					{
						if( $call->skillDisposition->is_visible_on_report == 1)
						{
					
							$valid = true;
						}
						else
						{
							$valid = false;
						}
					}
					
					if( $valid )
					{
						$callTime = new DateTime($call->start_call_time, new DateTimeZone('America/Chicago'));
						$callTime->setTimezone(new DateTimeZone('America/Denver'));	

						
						$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $call->lead->last_name.', '.$call->lead->first_name);
						
						if( $call->is_skill_child == 0 )
						{
							if( isset( $call->skillDisposition) )
							{
								$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $call->skillDisposition->skill_disposition_name);
							}
						}
						else
						{
							if( $call->skillChildDisposition )
							{
								$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $call->skillChildDisposition->skill_child_disposition_name);
							}
						}
						
						$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $callTime->format('m/d/Y g:i A'));
					}
					
					$ctr++;	
				}
			}
			
			header('Content-Type: application/vnd.ms-excel'); 
			header('Content-Disposition: attachment; filename="'.$filename.'.xlsx"'); 
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			
			header('Cache-Control: max-age=0');
		
			$objWriter->save('php://output');
		}
		else
		{
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
			$pdf->SetFont('freesans', '', 11);

			$pdf->AddPage();
			

			//Write the html
			$html = $this->renderPartial('customerAllWnDncReportLayout', array(
				'customer'=>$customer,
				'calls' => $calls,
				'dispositions' => $dispositions,
				'start_date' => $start_date,
				'end_date' => $end_date,
				'skills' => $skills,
			), true);
			
			//Convert the Html to a pdf document
			$pdf->writeHTML($html, true, false, true, false, '');
			
			// reset pointer to the last page
			$pdf->lastPage();

			//Close and output PDF document
			$pdf->Output( $customer->getFullName() . '.pdf', 'I');
			Yii::app()->end();
		}
	}
}

?>