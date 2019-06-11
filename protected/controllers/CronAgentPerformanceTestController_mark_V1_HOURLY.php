<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

ini_set('memory_limit', '1000M');
set_time_limit(0);

class CronAgentPerformanceTestController extends Controller
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
		date_default_timezone_set('America/Denver');
		
		echo 'Process started at ' . date('H:i:s');
		
		echo '<br><br>';
		
		// time() >= strtotime('today 4:00 am')
		
		$agentPerformanceReportOngoing = AgentPerformanceExportSettings::model()->find(array(
			'condition' => 'ongoing=1',
		));
		
					
		$agentPerformanceReportNotDone = AgentPerformanceExportSettings::model()->find(array(
			'condition' => 'done=0',
			'order' => 'id ASC'
		));
		
		$agentPerformanceReportNotDone = AgentPerformanceExportSettings::model()->findByPk(12);
		
		
		
		echo 'Agent Performace Report Ongoing Count: '.count($agentPerformanceReportOngoing);
		echo '<br>';
		
		echo 'Agent Performace Report Not Done Count: '.count($agentPerformanceReportNotDone);
		echo '<br>';
		
		
		echo '<br>';
		
		// if( count($agentPerformanceReportOngoing) == 0 && count($agentPerformanceReportNotDone) > 0 )
		if( true )
		{
			// $agentPerformanceReportNotDone->ongoing = 1;
			// $agentPerformanceReportNotDone->save(false);
			
			// unregister Yii's autoloader
			spl_autoload_unregister(array('YiiBase', 'autoload'));
			
			// register PHPExcel's autoloader ... PHPExcel.php will do it
			$phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
			require_once($phpExcelPath . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'PHPExcel.php');
			
			// register Yii's autoloader again
			spl_autoload_register(array('YiiBase', 'autoload'));
			 
			// This requires Yii's autoloader
			
			$objPHPExcel = new PHPExcel();
			
			$filename = '';
			$dateFilterStart = '';
			$dateFilterEnd = '';
			
			if( $agentPerformanceReportNotDone->name == '9 AM' && time() >= strtotime('today 09:00 am') )
			{
				$filename = '9 AM';
				
				$dateFilterStart = date('Y-m-d H:i:s', strtotime('today 8:00 am'));
				$dateFilterEnd = date('Y-m-d H:i:s', strtotime('today 9:00 am'));
			}
			
			if( $agentPerformanceReportNotDone->name == '10 AM' && time() >= strtotime('today 10:00 am') )
			{
				$filename = '10 AM';
				
				$dateFilterStart = date('Y-m-d H:i:s', strtotime('today 8:00 am'));
				$dateFilterEnd = date('Y-m-d H:i:s', strtotime('today 10:00 am'));
			}
			
			if( $agentPerformanceReportNotDone->name == '11 AM' && time() >= strtotime('today 11:00 am') )
			{
				$filename = '11 AM';
				
				$dateFilterStart = date('Y-m-d H:i:s', strtotime('today 8:00 am'));
				$dateFilterEnd = date('Y-m-d H:i:s', strtotime('today 11:00 am'));
			}
			
			if( $agentPerformanceReportNotDone->name == '12 PM' && time() >= strtotime('today 12:00 pm') )
			{
				$filename = '12 PM';
				
				$dateFilterStart = date('Y-m-d H:i:s', strtotime('today 8:00 am'));
				$dateFilterEnd = date('Y-m-d H:i:s', strtotime('today 12:00 pm'));
			}
			
			if( $agentPerformanceReportNotDone->name == '1 PM' && time() >= strtotime('today 1:00 pm') )
			{
				$filename = '1 PM';
				
				$dateFilterStart = date('Y-m-d H:i:s', strtotime('today 8:00 am'));
				$dateFilterEnd = date('Y-m-d H:i:s', strtotime('today 1:00 pm'));
			}
			
			if( $agentPerformanceReportNotDone->name == '2 PM' && time() >= strtotime('today 2:00 pm'))
			{
				$filename = '2 PM';
				
				$dateFilterStart = date('Y-m-d H:i:s', strtotime('today 8:00 am'));
				$dateFilterEnd = date('Y-m-d H:i:s', strtotime('today 2:00 pm'));
			}
			
			if( $agentPerformanceReportNotDone->name == '3 PM' && time() >= strtotime('today 3:00 pm'))
			{
				$filename = '3 PM';
				
				$dateFilterStart = date('Y-m-d H:i:s', strtotime('today 8:00 am'));
				$dateFilterEnd = date('Y-m-d H:i:s', strtotime('today 3:00 pm'));
			}
			
			if( $agentPerformanceReportNotDone->name == '4 PM' && time() >= strtotime('today 4:00 pm'))
			{
				$filename = '4 PM';
				
				$dateFilterStart = date('Y-m-d H:i:s', strtotime('today 8:00 am'));
				$dateFilterEnd = date('Y-m-d H:i:s', strtotime('today 4:00 pm'));
			}
			
			if( $agentPerformanceReportNotDone->name == '5 PM' && time() >= strtotime('today 5:00 pm'))
			{
				$filename = '5 PM';
				
				$dateFilterStart = date('Y-m-d H:i:s', strtotime('today 8:00 am'));
				$dateFilterEnd = date('Y-m-d H:i:s', strtotime('today 5:00 pm'));
			}
			
			if( $agentPerformanceReportNotDone->name == '6 PM' && time() >= strtotime('today 6:00 pm'))
			{
				$filename = '6 PM';
				
				$dateFilterStart = date('Y-m-d H:i:s', strtotime('today 8:00 am'));
				$dateFilterEnd = date('Y-m-d H:i:s', strtotime('today 6:00 pm'));
			}
			
			if( $agentPerformanceReportNotDone->name == '7 PM' && time() >= strtotime('today 7:00 pm'))
			{
				$filename = '7 PM';
				
				$dateFilterStart = date('Y-m-d H:i:s', strtotime('today 8:00 am'));
				$dateFilterEnd = date('Y-m-d H:i:s', strtotime('today 7:00 pm'));
			}
			
			if( $agentPerformanceReportNotDone->name == '8 PM' && time() >= strtotime('today 8:00 pm'))
			{
				$filename = '8 PM - ' . date('m-d-Y');
				
				$dateFilterStart = date('Y-m-d H:i:s', strtotime('today 8:00 am'));
				$dateFilterEnd = date('Y-m-d H:i:s', strtotime('today 8:00 pm'));
			}
			
			// var_dump(( $agentPerformanceReportNotDone->name == 'Month to date' && time() >= strtotime('today 9:00 pm'))); exit;
			
			if( $agentPerformanceReportNotDone->name == 'Month to date' && time() >= strtotime('today 9:00 pm'))
			{
				$filename = 'Month to date - ' . date('m-d-Y');
				
				$dateFilterStart = date('Y-m-d 00:00:00', strtotime('first day of this month'));
				$dateFilterEnd = date('Y-m-d 23:59:59');
			}
			
			
			$filename = '8 PM - 05-09-2016';
			$dateFilterStart = '2016-05-09 00:00:00';
			$dateFilterEnd = '2016-05-09 23:59:59';
			
			echo 'Filename - Start - End';
			echo '<br>';
			echo $filename.' | '.$dateFilterStart.' | '.$dateFilterEnd;
			echo '<br>';
			
			
			if( $dateFilterStart != '' && $dateFilterEnd != '' )
			{
				$selectedSkills = '11,12,15,16,17,19,20,21';
				
				$models = Account::model()->findAll(array(
					'with' => array('accountUser'),
					'condition' => 't.account_type_id = :account_type_id AND t.status = :status AND t.id NOT IN (4, 5)',
					'params' => array(
						':account_type_id' => Account::TYPE_AGENT,
						':status' => Account::STATUS_ACTIVE,
					),
					'order' => 'accountUser.last_name DESC',
				));
				
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

			
				$ctr = 2;
				
				echo 'Account Agent count: '.count($models).'<br>';
				
				
				if( $models )
				{	
					### get Agents Primary Call ###
					$primaryCallsHolder = array();
					$primaryCalls = LeadCallHistory::model()->findAll(array(
						'select'=>'count(t.id) as id, agent_account_id',
						'with' => array('list'),
						'condition' => '
							1
							AND t.start_call_time >= :dateFilterStart 
							AND t.start_call_time <= :dateFilterEnd 
							AND t.end_call_time > t.start_call_time
							AND list.skill_id IN ('.$selectedSkills.')
							AND t.is_skill_child=0
						',
						'group'=> 'agent_account_id',
						'params' => array(
							':dateFilterStart' => $dateFilterStart,
							':dateFilterEnd' => $dateFilterEnd,
						),
					));
					
					if(!empty($primaryCalls))
					{
						foreach($primaryCalls as $primaryCall)
						{
							$primaryCallsHolder[$primaryCall->agent_account_id] = $primaryCall->id;
						}
					}
					
					### get Agents Voice Contacts ###
					
					$primaryVoiceContactsHolder = array();
					$primaryVoiceContacts = LeadCallHistory::model()->findAll(array(
						'select'=>'count(t.id) as id, agent_account_id',
						'with' => array('list','skillDisposition'),
						'condition' => '
							1	
							AND t.start_call_time >= :dateFilterStart 
							AND t.start_call_time <= :dateFilterEnd
							AND t.is_skill_child=0				
							AND skillDisposition.id IS NOT NULL
							AND skillDisposition.is_voice_contact=1
							AND list.skill_id IN ('.$selectedSkills.')
						',
						'group'=> 'agent_account_id',
						'params' => array(
							':dateFilterStart' => $dateFilterStart,
							':dateFilterEnd' => $dateFilterEnd,
						),
					));
					
					if(!empty($primaryVoiceContacts))
					{
						foreach($primaryVoiceContacts as $primaryVoiceContact)
						{
							$primaryVoiceContactsHolder[$primaryVoiceContact->agent_account_id] = $primaryVoiceContact->id;
						}
					}
						
					## get agent appointments ##
					$primaryAppointmentsHolder = array();
					$primaryAppointments = LeadCallHistory::model()->findAll(array(
						'select'=>'count(t.id) as id, agent_account_id',
						'with' => array('list','skillDisposition'),
						'condition' => '
							1
							AND t.start_call_time >= :dateFilterStart 
							AND t.start_call_time <= :dateFilterEnd
							AND t.is_skill_child=0
							AND skillDisposition.id IS NOT NULL
							AND skillDisposition.is_appointment_set=1
							AND list.skill_id IN ('.$selectedSkills.')
						',
						'group'=> 'agent_account_id',
						'params' => array(
							':dateFilterStart' => $dateFilterStart,
							':dateFilterEnd' => $dateFilterEnd,
						),
					));
					
					if(!empty($primaryAppointments))
					{
						foreach($primaryAppointments as $primaryAppointment)
						{
							$primaryAppointmentsHolder[$primaryAppointment->agent_account_id] = $primaryAppointment->id;
						}
					}
					
					## get agent child calls ##
					$childCallsHolder = array();
					$childCalls = LeadCallHistory::model()->findAll(array(
						'select'=>'count(t.id) as id, agent_account_id',
						'with' => array('list'),
						'condition' => '
							1
							AND t.start_call_time >= :dateFilterStart 
							AND t.start_call_time <= :dateFilterEnd 
							AND t.end_call_time > t.start_call_time
							AND list.skill_id IN ('.$selectedSkills.')
							AND t.is_skill_child=1
						',
						'group'=> 'agent_account_id',
						'params' => array(
							':dateFilterStart' => $dateFilterStart,
							':dateFilterEnd' => $dateFilterEnd,
						),
					));
					
					if(!empty($childCalls))
					{
						foreach($childCalls as $childCall)
						{
							$childCallsHolder[$childCall->agent_account_id] = $childCall->id;
						}
					}
					
					
					## get agent child voice contacts ##
					$childVoiceContactsHolder = array();
					$childVoiceContacts = LeadCallHistory::model()->findAll(array(
						'select'=>'count(t.id) as id, agent_account_id',
						'with' => array('list','skillChildDisposition'),
						'condition' => '
							1
							AND t.start_call_time >= :dateFilterStart 
							AND t.start_call_time <= :dateFilterEnd
							AND t.is_skill_child=1				
							AND skillChildDisposition.id IS NOT NULL
							AND skillChildDisposition.is_voice_contact=1
							AND list.skill_id IN ('.$selectedSkills.')
						',
						'group'=> 'agent_account_id',
						'params' => array(
							':dateFilterStart' => $dateFilterStart,
							':dateFilterEnd' => $dateFilterEnd,
						),
					)); 
					
					
					if(!empty($childVoiceContacts))
					{
						foreach($childVoiceContacts as $childVoiceContact)
						{
							$childVoiceContactsHolder[$childVoiceContact->agent_account_id] = $childVoiceContact->id;
						}
					}
							
							
					foreach($models as $model)
					{
						$primaryVoiceContacts = 0;
						$primaryAppointments = 0;
						$primaryConversionRate = 0;
						$primaryTotalDialsPerHour = 0;
						$primaryAppointmentsPerHour = 0;
						
						$primaryCallHours = 0;
						$primaryCallMinutes = 0;
						
						$primaryOutboundDials = 0;
						
						$clockedHours = $model->getTotalLoginHoursTest($dateFilterStart, $dateFilterEnd);

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
						
						$primaryOutboundDials = isset($primaryCallsHolder[$model->id]) ? $primaryCallsHolder[$model->id] : 0;
						
						$primaryVoiceContacts = isset($primaryVoiceContactsHolder[$model->id]) ? $primaryVoiceContactsHolder[$model->id] : 0;
						
						$primaryAppointments = isset($primaryAppointmentsHolder[$model->id]) ? $primaryAppointmentsHolder[$model->id] : 0;
						
						
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
								':dateFilterStart' => $dateFilterStart,
								':dateFilterEnd' => $dateFilterEnd,
							),
						));
						
						
						
						$primarySkillWrapTimeMinutes = 0;
						
						if( $primarySkillWrapTimes )
						{
							$primarySkillWrapTimesHolder = array();
							foreach( $primarySkillWrapTimes as $primarySkillWrapTime )
							{
								$primarySkillWrapTimesHolder[strtotime($primarySkillWrapTime->end_time)] = strtotime($primarySkillWrapTime->end_time);
								$primarySkillWrapTimesHolder[strtotime($primarySkillWrapTime->start_time)] = strtotime($primarySkillWrapTime->start_time);
								
								$primarySkillWrapTimeMinutes += round( (strtotime($primarySkillWrapTime->end_time) - strtotime($primarySkillWrapTime->start_time)) / 60,2);
							}
							
							echo '<pre>';
							print_r($primarySkillWrapTimesHolder);
							exit;
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
							$childOutboundDials = isset($childCallsHolder[$model->id]) ? $childCallsHolder[$model->id] : 0;
							$childVoiceContacts = isset($childVoiceContactsHolder[$model->id]) ? $childVoiceContactsHolder[$model->id] : 0;
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
								':dateFilterStart' => $dateFilterStart,
								':dateFilterEnd' => $dateFilterEnd,
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
						
						/* 
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
								':dateFilterStart' => $dateFilterStart,
								':dateFilterEnd' => $dateFilterEnd,
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
						} */
						
						
						if( $primaryOutboundDials > 0 || $childOutboundDials > 0 )
						{
							$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $team);
						
							$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $model->accountUser->first_name.' '.$model->accountUser->last_name);
							
							$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $clockedHours);

							// if( strlen($primaryCallHours) == 1)
							// {
								// $primaryCallHours = '0'.$primaryCallHours;
							// }
							
							// if( strlen($primaryCallMinutes) == 1)
							// {
								// $primaryCallMinutes = '0'.$primaryCallMinutes;
							// }
							
							// $objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $primaryCallHours.':'.$primaryCallMinutes);
							$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, '-');
							
							// $primarySkillWrapTimeHours =  floor($primarySkillWrapTimeMinutes/60);
							// $primarySkillWrapTimeMinutes =   $primarySkillWrapTimeMinutes % 60;
							
							// if( strlen($primarySkillWrapTimeHours) == 1)
							// {
								// $primarySkillWrapTimeHours = '0'.$primarySkillWrapTimeHours;
							// }
							
							// if( strlen($primarySkillWrapTimeMinutes) == 1)
							// {
								// $primarySkillWrapTimeMinutes = '0'.$primarySkillWrapTimeMinutes;
							// }
							
							// $objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $primarySkillWrapTimeHours.':'.$primarySkillWrapTimeMinutes);
							$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, '-');
							
							$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $primaryOutboundDials);
							
							$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $primaryVoiceContacts);
							
							$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, round($primaryAppointments, 2));
							
							$objPHPExcel->getActiveSheet()->SetCellValue('I'.$ctr, round($primaryTotalDialsPerHour, 2));
							
							$objPHPExcel->getActiveSheet()->SetCellValue('J'.$ctr, round($primaryAppointmentsPerHour, 2));
							
							$objPHPExcel->getActiveSheet()->SetCellValue('K'.$ctr, (round($primaryConversionRate, 2) * 100).'%');
							
							
							// if( strlen($childCallHours) == 1)
							// {
								// $childCallHours = '0'.$childCallHours;
							// }
							
							// if( strlen($childCallMinutes) == 1)
							// {
								// $childCallMinutes = '0'.$childCallMinutes;
							// }
							
							// $objPHPExcel->getActiveSheet()->SetCellValue('L'.$ctr, $childCallHours.':'.$childCallMinutes);
							$objPHPExcel->getActiveSheet()->SetCellValue('L'.$ctr, '-');
							
							// $childSkillWrapTimeHours =  floor($childSkillWrapTimeMinutes/60);
							// $childSkillWrapTimeMinutes =   $childSkillWrapTimeMinutes % 60;
							
							// if( strlen($childSkillWrapTimeHours) == 1)
							// {
								// $childSkillWrapTimeHours = '0'.$childSkillWrapTimeHours;
							// }
							
							// if( strlen($childSkillWrapTimeMinutes) == 1)
							// {
								// $childSkillWrapTimeMinutes = '0'.$childSkillWrapTimeMinutes;
							// }
							
							// $objPHPExcel->getActiveSheet()->SetCellValue('M'.$ctr, $childSkillWrapTimeHours.':'.$childSkillWrapTimeMinutes);
							$objPHPExcel->getActiveSheet()->SetCellValue('M'.$ctr, '-');
							
							
							$objPHPExcel->getActiveSheet()->SetCellValue('N'.$ctr, $childOutboundDials);
							$objPHPExcel->getActiveSheet()->SetCellValue('O'.$ctr, $childVoiceContacts);
							$objPHPExcel->getActiveSheet()->SetCellValue('P'.$ctr, round($childTotalDialsPerHour, 2));
							
							$ctr++;	
						}
						
						echo '<br><br> Account Loop: '.date('H:i:s').' - ID:'.$model->id.'-'.$model->accountUser->first_name.' '.$model->accountUser->last_name;
						exit;
					}
				}
				
				// header('Content-Type: application/vnd.ms-excel'); 
				// header('Content-Disposition: attachment; filename="'.$filename.'.xlsx"'); 
				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
				
				// header('Cache-Control: max-age=0');
				
				$objWriter->save(str_replace(__FILE__,'agentPerformanceReports/'.$filename.'.xlsx',__FILE__));
				
				// $agentPerformanceReportNotDone->ongoing = 0;
				// $agentPerformanceReportNotDone->done = 1;
				// $agentPerformanceReportNotDone->save(false);
				
				### mailing ###
				$filenamePath = Yii::getPathOfAlias('webroot') . '/agentPerformanceReports/'.$filename.'.xlsx';
				$emailSubject = 'Engagex Service: '.date("m/d/Y").' - '.$filename.' - Agent Performance Report';
			
				Yii::import('application.extensions.phpmailer.JPhpMailer');

				$mail = new JPhpMailer;
				$mail->SetFrom('service@engagex.com');
				
				$mail->Subject = $emailSubject;
				
				// $mail->AddAddress('sophie.valentine@engagex.com');
				// $mail->AddAddress('lucas.ashburn@engagex.com');
				// $mail->AddAddress('douglas.larsen@engagex.com');
				
				$mail->AddBCC('markjuan169@gmail.com');
				// $mail->AddBCC('jim.campbell@engagex.com');
				
				$mail->MsgHTML('Agent Performance Report');
				$mail->AddAttachment($filenamePath,$filename.'.xlsx');						
				$mail->Send();

		
			}
			
			
		}
		
		// var_dump(count($agentPerformanceReportNotDone) == 0 && time() >= strtotime('today 11:30 pm') );
		
		// var_dump(time());
		/* if( count($agentPerformanceReportNotDone) == 0 && time() >= strtotime('today 11:30 pm') )
		{
			$agentPerformanceReportsDone = AgentPerformanceExportSettings::model()->findAll();
			
			if( $agentPerformanceReportsDone )
			{
				foreach( $agentPerformanceReportsDone as $agentPerformanceReportDone )
				{
					$agentPerformanceReportDone->ongoing = 0;
					$agentPerformanceReportDone->done = 0;
					$agentPerformanceReportDone->save(false);
				}
			}
		} */
		
		
		echo '<br><br> Process ended at '.date('H:i:s');
	}
	
	public function actionTest()
	{
		$filenamePath = Yii::getPathOfAlias('webroot') . '/agentPerformanceReports/8 PM - 05-16-2016.xlsx';
		
			
		$emailSubject = 'Engagex Service: '.date("m/d/Y").' - 8:00pm - Agent Performance Report';
	
		Yii::import('application.extensions.phpmailer.JPhpMailer');

		$mail = new JPhpMailer;
		$mail->SetFrom('service@engagex.com');
		
		$mail->Subject = $emailSubject;
		
		$mail->AddAddress('markjuan169@gmail.com');
	
		$mail->MsgHTML('Agent Performance Report');
		$mail->AddAttachment($filenamePath,'8 PM - 05-16-2016.xlsx');						
		$mail->Send();
	}
}

?>