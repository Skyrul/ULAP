<?php 

error_reporting(E_ALL);
ini_set('display_errors', 1);

ini_set('memory_limit', '10000M');
set_time_limit(0);

class StateFarmReportController extends Controller
{
	public function accessRules()
	{
		return array(
			array('allow', 
				'users'=>array('*'),
			),
		);
	}
	
	public function actionIndex()
	{
		date_default_timezone_set('America/Chicago');
		
		echo 'Process started at ' . date('g:i A');
		
		echo '<br><br>';
		
		$leadCallHistoriesCount = LeadCallHistory::model()->count(array(
			'condition' => 'company_id=13 AND t.disposition IS NOT NULL AND t.date_created >="2018-06-01 00:00:00" AND t.date_created <= "2018-06-30 23:59:59" AND t.status !=4 AND t.attempt IS NULL',
		));
		
		$leadCallHistories = LeadCallHistory::model()->findAll(array(
			'condition' => 'company_id=13 AND t.disposition IS NOT NULL AND t.date_created >="2018-06-01 00:00:00" AND t.date_created <= "2018-06-30 23:59:59" AND t.status !=4 AND t.attempt IS NULL',
			'order' => 't.date_created ASC',
			'limit' => 30000 
			// 'offset' => 0,
		));
		
		echo 'total remaining: ' . $leadCallHistoriesCount; 
		
		echo '<br><br>';
		
		echo 'count: ' . count($leadCallHistories);
		
		echo '<br><br>';
		
		
		$ctr = 0;
		
		if( $leadCallHistories )
		{
			foreach( $leadCallHistories as $leadCallHistory )
			{
				if( $leadCallHistory->attempt == null || $leadCallHistory->attempt == 0 )
				{
					$existingAttempt = LeadCallHistory::model()->find(array(
						'condition' => 'id != :id AND lead_id = :lead_id AND disposition IS NOT NULL AND status !=4 AND attempt IS NOT NULL AND date_created >="2018-06-01 00:00:00" AND date_created <= "2018-06-30 23:59:59"',
						'params' => array(
							':id' => $leadCallHistory->id,
							':lead_id' => $leadCallHistory->lead_id
						),
						'order' => 'attempt DESC',
					));
						
					if( $existingAttempt )
					{
						$leadCallHistory->attempt = $existingAttempt->attempt + 1;
					}
					else
					{
						$leadCallHistory->attempt = 1;
					}
					
					echo 'attempt null';
					echo '<br>';
				}
				
				if( $leadCallHistory->bucket_priority == null || $leadCallHistory->bucket_priority == 0 )
				{
					$leadCallHistory->bucket_priority = $this->getBucketPriorityValue($leadCallHistory->disposition);
					
					echo 'bucket_priority null';
					echo '<br>';
				}
				
				if( $leadCallHistory->save(false) )
				{
					$otherAttempts = LeadCallHistory::model()->findAll(array(
						'condition' => 'id != :id AND lead_id = :lead_id AND disposition IS NOT NULL AND date_created >= :start_date AND date_created <= :end_date AND status !=4 AND attempt IS NULL',
						'params' => array(
							':id' => $leadCallHistory->id,
							':lead_id' => $leadCallHistory->lead_id,
							':start_date' => date('Y-m-d H:i:s', strtotime($leadCallHistory->date_created)),
							':end_date' => date('Y-m-d H:i:s', strtotime('+1 Hour', strtotime($leadCallHistory->date_created))),
						),
						'order' => 'date_created ASC',
					));

					if( $otherAttempts )
					{
						foreach( $otherAttempts as $otherAttempt )
						{
							if( $otherAttempt->attempt == null ||  $otherAttempt->attempt == 0 )
							{
								$otherAttempt->attempt = $leadCallHistory->attempt;
							}
							
							if( $otherAttempt->bucket_priority == null || $leadCallHistory->bucket_priority == 0 )
							{
								$otherAttempt->bucket_priority = $this->getBucketPriorityValue($otherAttempt->disposition);
							}
							
							$otherAttempt->save(false);
						}
					}
				}
				
				echo $ctr++;
				echo '<br>';
			}
		}
		
		echo '<br><br> Process ended at '.date('g:i A');
		
		echo '<br><hr><br>';
		
		echo 'dbUpdates: ' . $ctr;
		
		echo '<br><br>';
		
		echo 'end..';
	}
	
	public function actionIndex2()
	{
		date_default_timezone_set('America/Denver');
		
		echo 'Process started at ' . date('H:i:s');
		
		echo '<br><br>';
		
		$leadCallHistoriesCount = LeadCallHistory::model()->count(array(
			'condition' => 'company_id=13 AND t.disposition="Appointment Set" AND t.date_created >="2016-05-01 00:00:00" AND t.date_created <= "2016-05-31 <= 23:59:59" AND t.status !=4 AND t.bucket_priority IS NULL'
		));
		
		$leadCallHistories = LeadCallHistory::model()->findAll(array(
			'condition' => 'company_id=13 AND t.disposition="Appointment Set" AND t.date_created >="2016-05-01 00:00:00" AND t.date_created <= "2016-05-31 <= 23:59:59" AND t.status !=4 AND t.bucket_priority IS NULL',
			'order' => 't.date_created ASC',
			// 'offset' => 0,
		));
		
		echo 'count: ' . $leadCallHistoriesCount;
		
		echo '<br><br>';
		
		// exit;
		
		$ctr = 0;
		
		if( $leadCallHistories )
		{
			foreach( $leadCallHistories as $leadCallHistory )
			{
				$leadCallHistory->bucket_priority = $this->getBucketPriorityValue($leadCallHistory->disposition);
				
				if( $leadCallHistory->save(false) )
				{
					echo $ctr++;
					echo '<br>';
				}
			}
		}
		
		echo '<br><br>end...';
	}
	
	public function actionExport()
	{
		// $bucketDispos = array(
			// 'Language Barrier - Unsupported', 
			// 'Client to Contact Call Center', 
			// 'Deceased', 
			// 'Not Insured', 
			// 'Not Interested', 
			// 'Recently Met With', 
			// 'Referred to Agent', 
			// 'Retention Alert', 
			// 'Do Not Call', 
			// 'Client Complete',
			// 'Appointment Set', 
			// 'Schedule Conflict', 
			// 'Location Conflict',
			// 'Call Back',
			// 'Answering Machine - No Message Left', 
			// 'Answering Machine - Left Message',
			// 'Busy', 
			// 'Client Hung-up', 
			// 'Language Barrier - Spanish', 
			// 'No Answer / No Voicemail',
			// 'Wrong Number'				
		// );
		
		$models = LeadCallHistory::model()->findAll(array(
			'with' => array('customer', 'lead', 'calendarAppointment'),
			'condition' => '
				t.disposition IS NOT NULL 
				AND t.start_call_time >="2016-04-01 00:00:00" 
				AND t.start_call_time <= "2016-04-30 <= 23:59:59" 
				AND t.status !=4 
				AND t.disposition IN (
					"Language Barrier - Unsupported", "Client to Contact Call Center", "Deceased", "Not Insured", "Not Interested", "Recently Met With", "Referred to Agent", 
					"Retention Alert", "Do Not Call", "Client Complete", "Appointment Set", "Schedule Conflict", "Location Conflict", "Call Back", "Answering Machine - No Message Left",
					"Answering Machine - Left Message", "Busy", "Client Hung-up", "Language Barrier - Spanish", "No Answer / No Voicemail", "Wrong Number"
				)
			',
			'order' => 't.start_call_time DESC, t.bucket_priority DESC',
			'group' => 'CONCAT(t.lead_id,"-",t.attempt)',
		));
		
		echo 'count: ' . count($models);
		
		echo '<br><br>';
		
		$ctr = 1;
		
		foreach( $models as $model )
		{
			if( isset($model->calendarAppointment) )
			{
				$appointmentDate = new DateTime($model->calendarAppointment->start_date, new DateTimeZone('America/Chicago'));
				$appointmentDate->setTimezone(new DateTimeZone('America/Denver'));	
				
				$appointmentDate = $appointmentDate->format('m/d/Y g:i A');
				
				if( in_array($model->calendarAppointment->location, array(3,4)) )
				{
					$appointmentType = 'Phone';
				}
				else
				{
					$appointmentType = 'In Person';
				}
			}
			else
			{
				$appointmentDate = '';
				$appointmentType = '';
			}
			
			$totalDials = LeadCallHistory::model()->count(array(
				'condition' => 'lead_id = :lead_id AND date_created >="2016-04-01 00:00:00" AND date_created <= "2016-04-30 <= 23:59:59" AND status !=4 AND disposition!="Skip Call"',
				'params' => array(
					':lead_id' => $model->lead_id,
				),
			));
			
			$appointmentSet = $model->disposition == 'Appointment Set' ? 'YES' : 'NO';
			
			$dialDate = new DateTime($model->start_call_time, new DateTimeZone('America/Chicago'));
			$dialDate->setTimezone(new DateTimeZone('America/Denver'));

			$export = new StateFarmExport;
			
			$export->setAttributes(array(
				'agent_code' => $model->customer->custom_customer_id,
				'agent_first' => $model->customer->firstname,
				'agent_last' => $model->customer->lastname,
				'state' => State::model()->findByPk($model->customer->state)->abbreviation,
				'skill' => $model->list->skill->skill_name,
				'customer_last' => $model->lead->last_name,
				'customer_first' => $model->lead->first_name,
				'dial_date' => $dialDate->format('m/d/Y'),
				'dial_time' => $dialDate->format('g:i A'),
				'phone_number_dialed' => $model->lead_phone_number,
				'total_dials' => $totalDials,
				'attempts_at_appointment' =>$model->attempt ,
				'appointment_set' => $appointmentSet,
				'appointment_type' => $appointmentType,
				'appointment_date' => $appointmentDate,
				'disposition' => $model->disposition,
			));
			
			if( $export->save(false) )
			{
				echo $ctr++;
				echo '<br />';
			}
		}
	}
	
	public function actionExport2()
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
		
		$filename = 'State Farm - Client Level Data';
			
		$models = LeadCallHistory::model()->findAll(array(
			'condition' => 'disposition IS NOT NULL AND start_call_time >="2016-04-01 00:00:00" AND start_call_time <= "2016-04-30 <= 23:59:59" AND status !=4 AND disposition!="Skip Call"',
			'order' => 'start_call_time DESC',
			'limit' => 500,
		));
		
		$ctr = 1;

		$headers = array(
			'A' => 'Agent Code',
			'B' => 'Agent First',
			'C' => 'Agent Last',
			'D' => 'State',
			'E' => 'Skill',
			'F' => 'Customer Last',
			'G' => 'Customer First',
			'H' => 'Dial Date',
			'I' => 'Dial Time',
			'J' => 'Phone Number Dialed',
			'K' => 'Total Dials',
			'L' => 'Attempts at Appointment',
			'M' => 'Appointment Set',
			'N' => 'Appointment Type',
			'O' => 'Appointment Date',
			'P' => 'Disposition',
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
				if( isset($model->calendarAppointment) )
				{
					$appointmentDate = new DateTime($model->calendarAppointment->start_date, new DateTimeZone('America/Chicago'));
					$appointmentDate->setTimezone(new DateTimeZone('America/Denver'));	
					
					$appointmentDate = $appointmentDate->format('m/d/Y g:i A');
					
					if( in_array($model->calendarAppointment->location, array(3,4)) )
					{
						$appointmentType = 'Phone';
					}
					else
					{
						$appointmentType = 'In Person';
					}
				}
				else
				{
					$appointmentDate = '';
					$appointmentType = '';
				}
				
				$totalDials = LeadCallHistory::model()->count(array(
					'condition' => 'lead_id = :lead_id AND date_created >="2016-04-01 00:00:00" AND date_created <= "2016-04-30 <= 23:59:59" AND status !=4 AND disposition!="Skip Call"',
					'params' => array(
						':lead_id' => $model->lead_id,
					),
				));
				
				$appointmentSet = $model->disposition == 'Appointment Set' ? 'YES' : 'NO';
				
				$dialDate = new DateTime($model->start_call_time, new DateTimeZone('America/Chicago'));
				$dialDate->setTimezone(new DateTimeZone('America/Denver'));

				
				$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $model->customer->custom_customer_id );
				
				$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $model->customer->firstname );
				
				$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $model->customer->lastname );
				
				$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, State::model()->findByPk($model->customer->state)->abbreviation );
				
				
				$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $model->list->skill->skill_name );
				
				$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $model->lead->last_name );
				
				$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $model->lead->first_name );
				
				$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $dialDate->format('m/d/Y') );
				
				$objPHPExcel->getActiveSheet()->SetCellValue('I'.$ctr, $dialDate->format('g:i A') );
				
				$objPHPExcel->getActiveSheet()->SetCellValue('J'.$ctr, $model->lead_phone_number );
				
				$objPHPExcel->getActiveSheet()->SetCellValue('K'.$ctr, $totalDials );
				
				$objPHPExcel->getActiveSheet()->SetCellValue('L'.$ctr, $model->attempt );
				
				$objPHPExcel->getActiveSheet()->SetCellValue('M'.$ctr, $appointmentSet );
				
				$objPHPExcel->getActiveSheet()->SetCellValue('N'.$ctr, $appointmentType );
			
				$objPHPExcel->getActiveSheet()->SetCellValue('O'.$ctr, $appointmentDate );
				
				$objPHPExcel->getActiveSheet()->SetCellValue('P'.$ctr, $model->disposition );
				
				$ctr++;	
			}
		}
		
		header('Content-Type: application/vnd.ms-excel'); 
		header('Content-Disposition: attachment; filename="'.$filename.'.xlsx"'); 
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		
		header('Cache-Control: max-age=0');
		
		$objWriter->save('php://output');
	}
	
	public function actionAgentLevelReport()
	{
		$dbUpdates = 0;
		
		$customerSkills = CustomerSkill::model()->findAll(array(
			'with' => 'customer',
			'condition' => '
				customer.id IS NOT NULL 
				AND customer.company_id=13 
				AND t.status = 1
				AND t.skill_id IN (33,34)
			',
			'order' => 't.date_created DESC'
			// 'limit' => 10,
		));
		
		echo '<pre>';
		
		echo '<br><br>' . date('Y-m-d g:i A');
		
		echo '<br><br> customerSkills: ' . count($customerSkills);
		
		if( $customerSkills )
		{
			$customersArray = array();
			
			foreach( $customerSkills as $customerSkill )
			{
				if( !in_array($customerSkill->customer_id, $customersArray) && isset($customerSkill->customer) && isset($customerSkill->contract) && $customerSkill->customer->is_deleted == 0 )
				{	
					$customersArray[] = $customerSkill->customer_id;
					
					$customer = $customerSkill->customer;
					$contract = $customerSkill->contract;
					$programType = $contract->fulfillment_type == 1 ? 'Goal' : 'Lead';
					
								
					$effectiveMonth = 'June';			
					$previousMonth = 'May';			
					$finalTier = '';
					$subsidyPercentage = '';
					$contractedQuantity = 0;
					$status = 'Inactive';
					$startDate = '';
					$endDate = '';
					$cancelDate = '';
					$namesSubmitted = 0;
					$namesFromPreviousMonths = 0;
					$totalServiceDials = 0;
					$totalAppointmentSetAttemptDials = 0;
					$totalApptCustomersCalled = 0;
					$totalUniqueApptCustomerContacts = 0;
					$totalNonUniqueApptCustomerContacts = 0;
					$inPersonAppointments = 0;
					$phoneAppointments = 0;
					$totalAppointments = 0;
					$apptToUniqueContacts = 0;
					$apptToCustomersCalled = 0;
					$apptToDials = 0;
					
					$fundingTier = CompanyCustomerFundingTier::model()->find(array(
						'condition' => 'company_id = :company_id AND agent_code = :agent_code',
						'params' => array(
							':company_id' => $customer->company_id,
							':agent_code' => $customer->custom_customer_id,
						),
					));
					
					if( $fundingTier )
					{
						$finalTier = $fundingTier->funding_tier;
					}
					
					$customerSkillSubsidyLevel = CustomerSkillSubsidyLevel::model()->find(array(
						'condition' => 'customer_id = :customer_id AND customer_skill_id = :customer_skill_id',
						'params' => array(
							':customer_id' => $customer->id,
							':customer_skill_id' => $customerSkill->id,
						),
					));
					
					if( $customerSkillSubsidyLevel )
					{
						$subsidy = CompanySubsidyLevel::model()->find(array(
							'condition' => 'id = :id AND type="%"',
							'params' => array(
								':id' => $customerSkillSubsidyLevel->subsidy_level_id,
							),
						));
						
						if( $subsidy )
						{
							$subsidyPercentage = $subsidy->value.'%';
						}
					}

					
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
											$contractedQuantity += ( $subsidyLevel['goal'] * $customerSkillLevelArrayGroup->quantity );
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
									$contractedQuantity += $customerExtra->quantity;
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
											$contractedQuantity += ( $subsidyLevel['high'] * $customerSkillLevelArrayGroup->quantity );
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
					
					if( $customerSkill->start_month != '0000-00-00' )
					{
						$startDate = $customerSkill->start_month;
					}
					
					$endDate = $customerSkill->end_month;
					
					if( time() >= strtotime($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00' )
					{
						$status = 'Active';
					}
		
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
							$cancelDate = $customerSkill->end_month;
						}
					}
					
					if( $customerSkill->is_hold_for_billing == 1 )
					{
						$status = 'Hold';
					}
							
					$totalServiceDials = LeadCallHistory::model()->count(array(
						'with' => 'list',
						'condition' => '
							t.customer_id = :customer_id
							AND t.start_call_time >="2018-06-01 00:00:00" 
							AND t.start_call_time <= "2018-06-30 23:59:59"
							AND t.status !=4 
							AND list.skill_id IN (33,34) 
						',
						'params' => array(
							':customer_id' => $customer->id,
						),
					));
					
					$totalAppointmentSetAttemptDials = LeadCallHistory::model()->count(array(
						'with' => 'list',
						'condition' => '
							t.customer_id = :customer_id
							AND t.start_call_time >="2018-06-01 00:00:00" 
							AND t.start_call_time <= "2018-06-30 23:59:59"
							AND t.status !=4 
							AND t.is_skill_child=0 
							AND list.skill_id IN (33,34) 
						',
						'params' => array(
							':customer_id' => $customer->id,
						),
					));
					
					$totalApptCustomersCalled = LeadCallHistory::model()->count(array(
						'with' => 'list',
						'condition' => '
							t.customer_id = :customer_id
							AND t.start_call_time >="2018-06-01 00:00:00" 
							AND t.start_call_time <= "2018-06-30 23:59:59"
							AND t.status !=4 
							AND t.is_skill_child=0 
							AND list.skill_id IN (33,34) 
						',
						'params' => array(
							':customer_id' => $customer->id,
						),
						'group' => 't.lead_id'
					));
					
					$totalUniqueApptCustomerContacts = LeadCallHistory::model()->count(array(
						'with' => array('list', 'skillDisposition'),
						'condition' => '
							t.customer_id = :customer_id
							AND t.start_call_time >="2018-06-01 00:00:00" 
							AND t.start_call_time <= "2018-06-30 23:59:59"
							AND t.status !=4 
							AND t.is_skill_child=0 
							AND list.skill_id IN (33,34) 
							AND skillDisposition.id IS NOT NULL
							AND skillDisposition.is_voice_contact=1
						',
						'params' => array(
							':customer_id' => $customer->id,
						),
						'group' => 't.lead_id'
					));
					
					$totalNonUniqueApptCustomerContacts = LeadCallHistory::model()->count(array(
						'with' => array('list', 'skillChildDisposition'),
						'condition' => '
							t.customer_id = :customer_id
							AND t.start_call_time >="2018-06-01 00:00:00" 
							AND t.start_call_time <= "2018-06-30 23:59:59"
							AND t.status !=4 
							AND t.is_skill_child=1
							AND list.skill_id IN (33,34) 
							AND skillChildDisposition.id IS NOT NULL
							AND skillChildDisposition.is_voice_contact=1
						',
						'params' => array(
							':customer_id' => $customer->id,
						),
						'group' => 't.lead_id'
					));
					
					$inPersonAppointments = LeadCallHistory::model()->count(array(
						'with' => array('list', 'skillDisposition', 'calendarAppointment'),
						'condition' => '
							t.customer_id = :customer_id
							AND t.start_call_time >="2018-06-01 00:00:00" 
							AND t.start_call_time <= "2018-06-30 23:59:59"
							AND t.status !=4 
							AND t.is_skill_child=0 
							AND list.skill_id IN (33,34) 
							AND skillDisposition.id IS NOT NULL
							AND skillDisposition.is_appointment_set=1
							AND calendarAppointment.id IS NOT NULL
							AND calendarAppointment.location IN (1,2)
						',
						'params' => array(
							':customer_id' => $customer->id,
						),
					));
					
					$phoneAppointments = LeadCallHistory::model()->count(array(
						'with' => array('list', 'skillDisposition', 'calendarAppointment'),
						'condition' => '
							t.customer_id = :customer_id
							AND t.start_call_time >="2018-06-01 00:00:00" 
							AND t.start_call_time <= "2018-06-30 23:59:59"
							AND t.status !=4 
							AND t.is_skill_child=0 
							AND list.skill_id IN (33,34) 
							AND skillDisposition.id IS NOT NULL
							AND skillDisposition.is_appointment_set=1
							AND calendarAppointment.id IS NOT NULL
							AND calendarAppointment.location IN (3,4)
						',
						'params' => array(
							':customer_id' => $customer->id,
						),
					));
					
					$totalAppointments = ($inPersonAppointments + $phoneAppointments);
					
					if( $totalUniqueApptCustomerContacts > 0 && $totalAppointments > 0 )
					{
						$apptToUniqueContacts = ( $totalUniqueApptCustomerContacts / $totalAppointments );
					}
					
					if( $totalApptCustomersCalled > 0 && $totalAppointments > 0 )
					{
						$apptToCustomersCalled = ( $totalApptCustomersCalled / $totalAppointments );
					}
					
					if( $totalAppointmentSetAttemptDials > 0 && $totalAppointments > 0 )
					{
						$apptToDials = ( $totalAppointmentSetAttemptDials / $totalAppointments );
					}
					
					
					$currentMonthImportedLeads = CustomerHistory::model()->findAll(array(
						'condition' => '
							customer_id = :customer_id
							AND page_name = :page_name
							AND type=1
							AND MONTH(date_created) = :month
							AND YEAR(date_created) = :year
							AND content LIKE "%leads imported%"
						',
						'params' => array(
							':customer_id' => $customer->id,
							':page_name' => 'Leads',
							':month' => date('m', strtotime($effectiveMonth.'-'.date('Y'))),
							':year' => date('Y')
						),
					));
					
					if( $currentMonthImportedLeads )
					{
						foreach( $currentMonthImportedLeads as $currentMonthImportedLead )
						{
							$explodedContent = explode('|', $currentMonthImportedLead->content);
	
							if( isset($explodedContent[2]) )
							{
								$namesSubmitted += filter_var($explodedContent[2], FILTER_SANITIZE_NUMBER_INT);
							}
						}
					}
					
					$previousMonthImportedLeads = CustomerHistory::model()->findAll(array(
						'condition' => '
							customer_id = :customer_id
							AND page_name = :page_name
							AND type=1
							AND MONTH(date_created) = :month
							AND YEAR(date_created) = :year
							AND content LIKE "%leads imported%"
						',
						'params' => array(
							':customer_id' => $customer->id,
							':page_name' => 'Leads',
							':month' => date('m', strtotime($previousMonth.'-'.date('Y'))),
							':year' => date('Y')
						),
					));
					
					if( $previousMonthImportedLeads )
					{
						foreach( $previousMonthImportedLeads as $previousMonthImportedLead )
						{
							$explodedContent = explode('|', $previousMonthImportedLead->content);
							
							if( isset($explodedContent[2]) )
							{
								$namesFromPreviousMonths += filter_var($explodedContent[2], FILTER_SANITIZE_NUMBER_INT);
							}
						}
					}
					
					
					$stateFarm = new StateFarmAgentLevelReport;
					
					$stateFarm->setAttributes(array(
						'agent_last_name' => $customer->lastname,
						'agent_first_name' => $customer->firstname,
						'agent_code' => $customer->custom_customer_id,
						'alias' => null,
						'subsidy_percentage' => $subsidyPercentage,
						'final_tier' => $finalTier,
						'effective_month' => $effectiveMonth,
						'program_type' =>  $programType,
						'contracted_quantity' => $contractedQuantity,
						'names_submitted' => abs($namesSubmitted),
						'names_from_previous_months' => abs($namesFromPreviousMonths),
						'status' => $status,
						'start_date' => $startDate,
						'end_date' => $endDate,
						'cancel_date' => $cancelDate,
						'total_service_dials' => $totalServiceDials,
						'total_appointment_set_attempt_dials' => $totalAppointmentSetAttemptDials,
						'total_appt_customers_called' => $totalApptCustomersCalled,
						'total_unique_appt_customer_contacts' => $totalUniqueApptCustomerContacts,
						'total_unique_non_appt_customer_contacts' => $totalNonUniqueApptCustomerContacts,
						'in_person_appointments' => $inPersonAppointments,
						'phone_appointments' => $phoneAppointments,
						'total_appointments' => $totalAppointments,
						'appt_to_unique_contacts' => round($apptToUniqueContacts, 2),
						'appt_to_customers_called' => round($apptToCustomersCalled, 2),
						'appt_to_dials' => round($apptToDials, 2),
					));
					
					
					
					
					if( $stateFarm->save(false) )
					{
						echo $dbUpdates++;
						echo '<br>';
						print_r($stateFarm->attributes);
						echo '<br>';
						echo '<br>';
					}
				}
			}
		}
		
		echo '<br><br>' . date('Y-m-d g:i A');
		
		echo '<br><br>dbUpdates: ' . $dbUpdates;
	}
	
	
	public function getBucketPriorityValue($disposition)
	{
		$completeBucket = array(
			'Language Barrier - Unsupported', 
			'Client to Contact Call Center', 
			'Deceased', 
			'Not Insured', 
			'Not Interested', 
			'Recently Met With', 
			'Referred to Agent', 
			'Retention Alert', 
			'Do Not Call', 
			'Client Complete' 
		);
		
		if( in_array($disposition, $completeBucket) )
		{
			return 1;
		}
		
		$calendarBucket = array(
			'Appointment Set', 
			'Schedule Conflict', 
			'Location Conflict'
		);
		
		if( in_array($disposition, $calendarBucket) )
		{
			return 2;
		}
		
		$callBackBucket = array(
			'Call Back'
		);
		
		if( in_array($disposition, $callBackBucket) )
		{
			return 3;
		}
		
		$retryBucket = array(
			'Answering Machine - No Message Left', 
			'Answering Machine - Left Message',
			'Busy', 
			'Client Hung-up', 
			'Language Barrier - Spanish', 
			'No Answer / No Voicemail'
		);
		
		if( in_array($disposition, $retryBucket) )
		{
			return 4;
		}
		
		$badNumberBucket = array(
			'Wrong Number'
		);
		
		if( in_array($disposition, $badNumberBucket) )
		{
			return 5;
		}

		return null;
	}
}

?>