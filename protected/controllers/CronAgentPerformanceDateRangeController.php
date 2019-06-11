<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

ini_set('memory_limit', '1000M');
set_time_limit(0);

class CronAgentPerformanceDateRangeController extends Controller
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
		
		// if( !isset($_GET['debug']) )
		// {
			// exit;
		// }
		
		date_default_timezone_set('America/Denver');
		
		echo 'Process started at ' . date('H:i:s');
		
		echo '<br><br>';
			
		$agentPerformanceReportOngoing = AgentPerformanceExportSettings::model()->find(array(
			'condition' => 'ongoing=1',
		));
		
					
		$agentPerformanceReportNotDone = AgentPerformanceExportSettings::model()->find(array(
			'condition' => 'done=0',
			'order' => 'id ASC'
		));
		
		echo 'Agent Performace Report Ongoing Count: '.count($agentPerformanceReportOngoing);
		echo '<br>';
		
		echo 'Agent Performace Report Not Done Count: '.count($agentPerformanceReportNotDone);
		echo '<br>';
		
		if( count($agentPerformanceReportOngoing) == 0 && count($agentPerformanceReportNotDone) > 0 )
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
			
			
			$dateFilterStart = $agentPerformanceReportNotDone->date_from;
			$dateFilterEnd = $agentPerformanceReportNotDone->date_to;
			
			$filename = date('m-d-y',strtotime($dateFilterStart)).' to '.date('m-d-y',strtotime($dateFilterEnd));
			
			echo 'Filename - Start - End';
			echo '<br>';
			echo $filename.' | '.$dateFilterStart.' | '.$dateFilterEnd;
			echo '<br>';
			
			
			if( $dateFilterStart != '' && $dateFilterEnd != '' )
			{
				// $selectedSkills = '11,12,15,16,17,19,20,21';
				$selectedSkills = '11,12,15,16,17,19,20,21,22,23,24,25,26,27,28,29';
				
				$models = Account::model()->findAll(array(
					'with' => array('accountUser'),
					'condition' => 't.account_type_id = :account_type_id AND t.status = :status AND t.id NOT IN (4, 5)',
					// 'condition' => 't.account_type_id = :account_type_id AND t.status = :status AND t.id NOT IN (5)',
					'params' => array(
						':account_type_id' => Account::TYPE_AGENT,
						':status' => Account::STATUS_ACTIVE,
					),
					'order' => 'accountUser.last_name ASC, accountUser.first_name ASC',
				));
				
				$ctr = 1;
					
				$headers = array(
					'A' => 'Team',
					'B' => 'Agent First/Last Name',
					'C' => 'Total Hours',
					'D' => 'Primary Hours',
					'E' => 'Outbound Dials',
					'F' => 'Voice Contact Dispositions',
					'G' => 'Appointments',
					'H' => 'Total dials per hour',
					'I' => 'Appointments/hour',
					'J' => 'Conversion rate',
					'K' => 'Child Skill Hours',
					'L' => 'Outbound Dials',
					'M' => 'Voice Contact Dispositions',
					'N' => 'Total dials per hour',
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
			
					### get Agents logged in hours###
					/* SELECT SUM(
						CASE WHEN time_out IS NOT NULL 
							THEN TIME_TO_SEC(TIMEDIFF(time_out, time_in))/3600
							ELSE TIME_TO_SEC(TIMEDIFF(NOW(), time_in))/3600
						END
					)
					FROM ud_account_login_tracker alt
					WHERE alt.account_id = a.`id`
					AND alt.time_in >= '".date('Y-m-d 00:00:00', strtotime($dateFilterStart))."' 
					AND alt.time_in <= '".date('Y-m-d 23:59:59', strtotime($dateFilterEnd))."' 
					AND alt.status=1 */
					
					$accountLoginTrackerHolder = array();
					$accountLoginTrackers = AccountLoginTracker::model()->findAll(array(
						'select'=>'
						SUM(
							CASE 
								WHEN time_out IS NOT NULL THEN TIME_TO_SEC(TIMEDIFF(time_out, time_in))/3600
								ELSE TIME_TO_SEC(TIMEDIFF(DATE_SUB(NOW(), INTERVAL 1 HOUR), time_in))/3600 
							END
						) as id, account_id
						',
						'condition' => ' 1
							AND DATE(t.time_in) >= :dateFilterStart 
							AND DATE(t.time_in) <= :dateFilterEnd 
							AND t.status != 4
						',
						'group'=> 'account_id',
						'params' => array(
							':dateFilterStart' => $dateFilterStart,
							':dateFilterEnd' => $dateFilterEnd,
						),
					));
					
					if(!empty($accountLoginTrackers))
					{
						foreach($accountLoginTrackers as $accountLoginTracker)
						{
							$accountLoginTrackerHolder[$accountLoginTracker->account_id] = $accountLoginTracker->id;
						}
					}
					
					### get Agents Primary Call ###
					$primaryCallsHolder = array();
					$primaryCalls = LeadCallHistory::model()->findAll(array(
						'select'=>'count(t.id) as id, agent_account_id',
						'with' => array('list'=>array('joinType' => 'INNER JOIN')),
						'condition' => '
							1
							AND DATE(t.start_call_time) >= :dateFilterStart 
							AND DATE(t.start_call_time) <= :dateFilterEnd 
							AND t.end_call_time > t.start_call_time
							AND list.skill_id IN ('.$selectedSkills.')
							AND t.status != 4
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
							// $primaryCallsHolder2[$primaryCall->agent_account_id.'-'.$primaryCall->agentAccount->accountUser->first_name.' '.$primaryCall->agentAccount->accountUser->last_name] = $primaryCall->id;
						}
					}
					
					// echo '<pre>';
					// print_r($primaryCallsHolder2);
					// exit;
					
					### get Agents Voice Contacts ###
					
					$primaryVoiceContactsHolder = array();
					$primaryVoiceContacts = LeadCallHistory::model()->findAll(array(
						'select'=>'count(t.id) as id, agent_account_id',
						// 'with' => array('list','skillDisposition'),
						'with' => array('list'=>array('joinType' => 'INNER JOIN'), 'skillDisposition'=>array('joinType' => 'INNER JOIN')),
						'condition' => '
							1	
							AND DATE(t.start_call_time) >= :dateFilterStart 
							AND DATE(t.start_call_time) <= :dateFilterEnd
							AND t.is_skill_child=0				
							AND skillDisposition.id IS NOT NULL
							AND skillDisposition.is_voice_contact=1
							AND list.skill_id IN ('.$selectedSkills.')
							AND t.status != 4
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
						// 'with' => array('list','skillDisposition'),
						'with' => array('list'=>array('joinType' => 'INNER JOIN'), 'skillDisposition'=>array('joinType' => 'INNER JOIN')),
						'condition' => '
							1
							AND DATE(t.start_call_time) >= :dateFilterStart 
							AND DATE(t.start_call_time) <= :dateFilterEnd
							AND t.is_skill_child=0
							AND skillDisposition.id IS NOT NULL
							#AND skillDisposition.is_appointment_set=1
							AND t.disposition="Appointment Set"
							AND list.skill_id IN ('.$selectedSkills.')
							AND t.status != 4
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
						// 'with' => array('list'),
						'with' => array('list'=>array('joinType' => 'INNER JOIN')),
						'condition' => '
							1
							AND DATE(t.start_call_time) >= :dateFilterStart 
							AND DATE(t.start_call_time) <= :dateFilterEnd 
							AND t.end_call_time > t.start_call_time
							AND list.skill_id IN ('.$selectedSkills.')
							AND t.is_skill_child=1
							AND t.status != 4
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
						// 'with' => array('list','skillChildDisposition'),
						'with' => array('list'=>array('joinType' => 'INNER JOIN'), 'skillChildDisposition'=>array('joinType' => 'INNER JOIN')),
						'condition' => '
							1
							AND DATE(t.start_call_time) >= :dateFilterStart 
							AND DATE(t.start_call_time) <= :dateFilterEnd
							AND t.is_skill_child=1				
							AND skillChildDisposition.id IS NOT NULL
							AND skillChildDisposition.is_voice_contact=1
							AND list.skill_id IN ('.$selectedSkills.')
							AND t.status != 4
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
						
						## GET AGENT - PRIMARY  HOURS ###
						
						$primarySkillLeadCallWrapTimeHoursTimeMinutes = 0;
						$primarySkillLeadCallWrapTimeInHours = 0;
						$primarySkillLeadCallWrapTimeHIS = '00:00:00';
						
						
						$primarySkillHoursTimeMinutes = 0;
						$primarySkillInHours = 0;
						$primarySkillHoursHIS = '00:00:00';
						
						$primaryOutboundDials = 0;
						$primaryVoiceContacts = 0;
						$primaryAppointments = 0;
						$primaryTotalDialsPerHour = 0;
						$primaryAppointmentsPerHour = 0;
						$primaryConversionRate = 0;
						
						$leadCallWrapTimePrimaryHours = LeadCallWrapTime::model()->findAll(array(
							'condition' => '
								agent_account_id = :agent_account_id 
								AND DATE(start_time) >= :dateFilterStart 
								AND DATE(start_time) <= :dateFilterEnd
								AND end_time > start_time
								AND DATE(start_time) = DATE(end_time)
								AND call_type NOT IN (3,6) 
								AND main_skill_id IN ('.$selectedSkills.')
							',
							'order'=> 't.start_time',
							'params' => array(
								':agent_account_id' => $model->id,
								':dateFilterStart' => $dateFilterStart,
								':dateFilterEnd' => $dateFilterEnd,
							),
						));
						
						if($leadCallWrapTimePrimaryHours)
						{
							$primaryHoursHolder = array();
							
							
							foreach($leadCallWrapTimePrimaryHours as $leadCallWrapTimePrimaryHour)
							{
								//get agent primary skill call wrap time
								$primarySkillLeadCallWrapTimeHoursTimeMinutes += (strtotime($leadCallWrapTimePrimaryHour->end_time) - strtotime($leadCallWrapTimePrimaryHour->start_time));
								
								// echo '<br>';
								// echo date("i:s",(strtotime($leadCallWrapTimePrimaryHour->end_time) - strtotime($leadCallWrapTimePrimaryHour->start_time)));
								
								// echo '<br>';
								// echo '--'.date("i:s",$primarySkillLeadCallWrapTimeHoursTimeMinutes);
								
								
								
								if(!isset($primaryHoursHolder[$leadCallWrapTimePrimaryHour->group_id]['start_time']))
									$primaryHoursHolder[$leadCallWrapTimePrimaryHour->group_id]['start_time'] = $leadCallWrapTimePrimaryHour->start_time;
								
								//always override the end_time, because we 'order the leadcallwraptime by start_time'
								$primaryHoursHolder[$leadCallWrapTimePrimaryHour->group_id]['end_time'] = $leadCallWrapTimePrimaryHour->end_time;
							}
							
							
							
							if( count($primaryHoursHolder) > 0 )
							{
								foreach( $primaryHoursHolder as $_primaryHoursHolder )
								{
									$primarySkillHoursTimeMinutes += (strtotime($_primaryHoursHolder['end_time']) - strtotime($_primaryHoursHolder['start_time']));
								}
								
								
							}
						
						
							//Computing the H:I:S of the Primary Skill Lead CalL Wrap Time Hours
							$primarySkillLeadCallWrapTimeInHours = (int) ($primarySkillLeadCallWrapTimeHoursTimeMinutes/ (60 * 60));
							$primarySkillLeadCallWrapTimeHIS = date('i:s',$primarySkillLeadCallWrapTimeHoursTimeMinutes);
							
							if($primarySkillLeadCallWrapTimeInHours > 0)
							{
								$primarySkillLeadCallWrapTimeHIS = str_pad($primarySkillLeadCallWrapTimeInHours,2,"0",STR_PAD_LEFT).':'.$primarySkillLeadCallWrapTimeHIS;
							}
							else
							{
								$primarySkillLeadCallWrapTimeHIS = '00:'.$primarySkillLeadCallWrapTimeHIS;
							}
							
							echo '<br>';
							echo $primarySkillLeadCallWrapTimeHIS;
							echo '<br>';
							
							//Computing the H:I:S of the Primary Skill Hours
							$primarySkillInHours = (int) ($primarySkillHoursTimeMinutes/ (60 * 60));
							$primarySkillHoursHIS = date('i:s',$primarySkillHoursTimeMinutes);
							
							if($primarySkillInHours > 0)
							{
								$primarySkillHoursHIS = str_pad($primarySkillInHours,2,"0",STR_PAD_LEFT).':'.$primarySkillHoursHIS;
							}
							else
							{
								$primarySkillHoursHIS = '00:'.$primarySkillHoursHIS;
							}
								
							// echo '<br>';
							// echo $primarySkillHoursHIS; 
							
						}
						
						$agentLoggedInHours = isset($accountLoginTrackerHolder[$model->id]) ? $accountLoginTrackerHolder[$model->id] : 0;
						
						$primaryOutboundDials = isset($primaryCallsHolder[$model->id]) ? $primaryCallsHolder[$model->id] : 0;
						
						$primaryVoiceContacts = isset($primaryVoiceContactsHolder[$model->id]) ? $primaryVoiceContactsHolder[$model->id] : 0;
						
						$primaryAppointments = isset($primaryAppointmentsHolder[$model->id]) ? $primaryAppointmentsHolder[$model->id] : 0;
						
						
						if( $primaryOutboundDials > 0 && $primarySkillInHours > 0 )
						{
							$primaryTotalDialsPerHour = $primaryOutboundDials / $primarySkillInHours;
						}
						
						if( $primaryAppointments > 0 && $primarySkillInHours > 0 )
						{
							$primaryAppointmentsPerHour = $primaryAppointments / $primarySkillInHours;
						}
						
						if( $primaryAppointments > 0 && $primaryVoiceContacts > 0 )
						{
							$primaryConversionRate = $primaryAppointments / $primaryVoiceContacts;
						}
						
						//start getting child skill values
						$childSkillLeadCallWrapTimeHoursTimeMinutes = 0;
						$childSkillLeadCallWrapTimeInHours = 0;
						$childSkillLeadCallWrapTimeHIS = '00:00:00';
						
						$childSkillHoursTimeMinutes = 0;
						$childSkillInHours = 0;
						$childSkillHoursHIS = '00:00:00';
						
						$childOutboundDials = 0;
						$childVoiceContacts = 0;
						$childAppointments = 0;
						$childTotalDialsPerHour = 0;
						$childAppointmentsPerHour = 0;
						$childConversionRate = 0;
						
						$leadCallWrapTimeChildHours = LeadCallWrapTime::model()->findAll(array(
							'condition' => '
								agent_account_id = :agent_account_id 
								AND DATE(start_time) >= :dateFilterStart 
								AND DATE(start_time) <= :dateFilterEnd
								AND end_time > start_time
								AND DATE(start_time) = DATE(end_time)
								AND call_type IN (3,6) 
								AND main_skill_id IN ('.$selectedSkills.')
							',
							'order'=> 't.start_time',
							'params' => array(
								':agent_account_id' => $model->id,
								':dateFilterStart' => $dateFilterStart,
								':dateFilterEnd' => $dateFilterEnd,
							),
						));
						
						if($leadCallWrapTimeChildHours)
						{
							$childHoursHolder = array();
							
							
							foreach($leadCallWrapTimeChildHours as $leadCallWrapTimeChildHour)
							{
								//get agent child skill call wrap time
								$childSkillLeadCallWrapTimeHoursTimeMinutes += (strtotime($leadCallWrapTimeChildHour->end_time) - strtotime($leadCallWrapTimeChildHour->start_time));
								
								if(!isset($childHoursHolder[$leadCallWrapTimeChildHour->group_id]['start_time']))
									$childHoursHolder[$leadCallWrapTimeChildHour->group_id]['start_time'] = $leadCallWrapTimeChildHour->start_time;
								
								//always override the end_time, because we 'order the leadcallwraptime by start_time'
								$childHoursHolder[$leadCallWrapTimeChildHour->group_id]['end_time'] = $leadCallWrapTimeChildHour->end_time;
							}
							
							
							
							if( count($childHoursHolder) > 0 )
							{
								foreach( $childHoursHolder as $_childHoursHolder )
								{
									$childSkillHoursTimeMinutes += (strtotime($_childHoursHolder['end_time']) - strtotime($_childHoursHolder['start_time']));
								}
							}
							
							//Computing the H:I:S of the Child Skill Lead CalL Wrap Time Hours
							$childSkillLeadCallWrapTimeInHours = (int) ($childSkillLeadCallWrapTimeHoursTimeMinutes/ (60 * 60));
							$childSkillLeadCallWrapTimeHIS = date('i:s',$childSkillLeadCallWrapTimeHoursTimeMinutes);
							
							if($primarySkillLeadCallWrapTimeInHours > 0)
							{
								$childSkillLeadCallWrapTimeHIS = str_pad($childSkillLeadCallWrapTimeInHours,2,"0",STR_PAD_LEFT).':'.$childSkillLeadCallWrapTimeHIS;
							}
							else
							{
								$childSkillLeadCallWrapTimeHIS = '00:'.$childSkillLeadCallWrapTimeHIS;
							}
							
							//Computing the H:I:S of the CHILD Skill Hours
							$childSkillInHours = (int) ($childSkillHoursTimeMinutes/ (60 * 60));
							$childSkillHoursHIS = date('i:s',$childSkillHoursTimeMinutes);
							
							if($childSkillInHours > 0)
							{
								$childSkillHoursHIS = str_pad($childSkillInHours,2,"0",STR_PAD_LEFT).':'.$childSkillHoursHIS;
							}
							else
							{
								$childSkillHoursHIS = '00:'.$childSkillHoursHIS;
							}
								
							// echo '<br>';
							// echo $childSkillHoursHIS; 
						}
						
						if( !empty($selectedSkills) )
						{
							$childOutboundDials = isset($childCallsHolder[$model->id]) ? $childCallsHolder[$model->id] : 0;
							$childVoiceContacts = isset($childVoiceContactsHolder[$model->id]) ? $childVoiceContactsHolder[$model->id] : 0;
						}
						
						
						if( $childOutboundDials > 0 && $childSkillInHours > 0 )
						{
							$childTotalDialsPerHour = $childOutboundDials / $childSkillInHours;
						}
						
						
						## compute total hours = sum of primary hours and child skill hours ##
						$totalHours = 0;
						$totalHoursHIS = 0;
						
						#before totalHours is based on primary skill hours + child Hours, but now it is just the logged in hours
						/* $totalHours = $childSkillHoursTimeMinutes + $primarySkillHoursTimeMinutes;
						
						//Computing the H:I:S of the total Hours
						$t = (int) ($totalHours/ (60 * 60));
						
						if($t > 0)
							$totalHoursHIS = date('H:i:s',$totalHours);
						else
						{
							$totalHoursHIS = date('i:s',$totalHours);
							
							$totalHoursHIS = '00:'.$totalHoursHIS;
						} */
						
						// $clockedHours = $model->getTotalLoginHours($dateFilterStart, $dateFilterEnd);						
						
						## generate excel data ##
						if( $primaryOutboundDials > 0 || $childOutboundDials > 0 )
						{
							$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $team);
						
							$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $model->accountUser->first_name.' '.$model->accountUser->last_name);
							
							
							// $objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $clockedHours);
							// $objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, '-');
							$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $agentLoggedInHours);

							$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $primarySkillHoursHIS);
							
							// $objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, '-');
							// $objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $primarySkillLeadCallWrapTimeHIS);
							
							$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $primaryOutboundDials);
							
							$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $primaryVoiceContacts);
							
							$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, round($primaryAppointments, 2));
							
							$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, round($primaryTotalDialsPerHour, 2));
							
							$objPHPExcel->getActiveSheet()->SetCellValue('I'.$ctr, round($primaryAppointmentsPerHour, 2));
							
							$objPHPExcel->getActiveSheet()->SetCellValue('J'.$ctr, (round($primaryConversionRate, 2) * 100).'%');
							
							$objPHPExcel->getActiveSheet()->SetCellValue('K'.$ctr, $childSkillHoursHIS);
							
							// $objPHPExcel->getActiveSheet()->SetCellValue('M'.$ctr, $childSkillWrapTimeHours.':'.$childSkillWrapTimeMinutes);
							// $objPHPExcel->getActiveSheet()->SetCellValue('M'.$ctr, '-');
							// $objPHPExcel->getActiveSheet()->SetCellValue('M'.$ctr, $childSkillLeadCallWrapTimeHIS);
							// $objPHPExcel->getActiveSheet()->SetCellValue('M'.$ctr, $childSkillHoursHIS);
							
							$objPHPExcel->getActiveSheet()->SetCellValue('L'.$ctr, $childOutboundDials);
							$objPHPExcel->getActiveSheet()->SetCellValue('M'.$ctr, $childVoiceContacts);
							$objPHPExcel->getActiveSheet()->SetCellValue('N'.$ctr, round($childTotalDialsPerHour, 2));
							
							$ctr++;	
						}
						
						// echo '<br><br> Account Loop: '.date('H:i:s').' - ID:'.$model->id.'-'.$model->accountUser->first_name.' '.$model->accountUser->last_name;
						// echo '<br>';
						// echo $primaryOutboundDials.' | '.$primaryAppointments;
						// exit;
					}
				}
				
				// exit;
				
				
				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
				
				// header('Cache-Control: max-age=0');
				
				$objWriter->save(str_replace(__FILE__,'agentPerformanceReports/'.$filename.'.xlsx',__FILE__));
				
				### mailing ###
				$filenamePath = Yii::getPathOfAlias('webroot') . '/agentPerformanceReports/'.$filename.'.xlsx';
				$emailSubject = 'Engagex Service: '.$filename.' - Agent Performance Report';
			
				Yii::import('application.extensions.phpmailer.JPhpMailer');

				$mail = new JPhpMailer;
				$mail->SetFrom('service@engagex.com');
				
				$mail->Subject = $emailSubject;
				
				// $mail->AddAddress('sophie.valentine@engagex.com');
				// $mail->AddAddress('lucas.ashburn@engagex.com');
				// $mail->AddAddress('douglas.larsen@engagex.com');
				
				if(isset($agentPerformanceReportNotDone->email_address))
				{
					$email_address = $agentPerformanceReportNotDone->email_address;
					
					if( filter_var($email_address, FILTER_VALIDATE_EMAIL) ) 
					{
						$mail->AddAddress($email_address);
					}
				}
				
				$mail->AddBCC('markjuan169@gmail.com');
				// $mail->AddBCC('jim.campbell@engagex.com');
				
				$mail->MsgHTML('Agent Performance Report');
				$mail->AddAttachment($filenamePath,$filename.'.xlsx');						
				$mail->Send();

				
				$agentPerformanceReportNotDone->ongoing = 0;
				$agentPerformanceReportNotDone->done = 1;
				$agentPerformanceReportNotDone->save(false);
			}
			
			echo '<br><br> Process ended at '.date('H:i:s');
		}
	}
}

?>