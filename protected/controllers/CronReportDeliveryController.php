<?php 

ini_set('memory_limit', '2000M');
set_time_limit(0);

class CronReportDeliveryController extends Controller
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
		$report = ReportDeliverySettings::model()->find(array(
			'condition' => 'id=1'
		));
		
		if( $report )
		{
			$valid = false;
			$filePath = null;	
				
			if( $report->date_last_sent == null )
			{
				$valid = true;
			}
			else
			{
				if( $report->auto_email_frequency == 'End of each week day' && (strtotime($report->date_last_sent) < strtotime('1 day ago', strtotime($report->date_last_sent))) )
				{
					$valid = true;
				}
				
				if( $report->auto_email_frequency == 'End of work week' && (strtotime($report->date_last_sent) < strtotime('1 week ago', strtotime($report->date_last_sent))) )
				{
					$valid = true;
				}
				
				if( $report->auto_email_frequency == 'End of work week' && (strtotime($report->date_last_sent) < strtotime('1 month ago', strtotime($report->date_last_sent))) )
				{
					$valid = true;
				}
			}
			
			if( $report->type == 2 ) //send to email
			{
				$emails = explode(', ', $report->auto_email_recipients);
				
				if( !$emails ) 
				{
					$valid = false;
				}
			}
			
			if( $valid )
			{
				if( $report->auto_email_frequency == 'End of each week day' )
				{
					$dateFilterStart = date('Y-m-d 00:00:00');
					$dateFilterEnd = date('Y-m-d 23:59:59');
					
					$dateFilterStart = date('2018-06-14 00:00:00');
					$dateFilterEnd = date('2018-06-14 23:59:59');
				}
				
				if( $report->auto_email_frequency == 'End of work week' )
				{
					$dateFilterStart = date('Y-m-d 00:00:00', strtotime('this monday'));
					$dateFilterEnd = date('Y-m-d 23:59:59', strtotime('this sunday'));
				}
				
				if( $report->auto_email_frequency == 'End of the month' )
				{
					$dateFilterStart = date('Y-m-01 00:00:00');
					$dateFilterEnd = date('Y-m-t 23:59:59');
				}
				
				// unregister Yii's autoloader
				spl_autoload_unregister(array('YiiBase', 'autoload'));
				
				// register PHPExcel's autoloader ... PHPExcel.php will do it
				$phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
				require_once($phpExcelPath . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'PHPExcel.php');
				
				// register Yii's autoloader again
				spl_autoload_register(array('YiiBase', 'autoload'));
				 
				// This requires Yii's autoloader
				
				$objPHPExcel = new PHPExcel();
				
				if( $report->report_name == 'genericSkill' )
				{
					$filename = 'Generic Skill Report - '. $report->customer->getFullName().' - ' . date('m d Y');

					$folder = $report->type == 1 ? 'fileupload' : 'tempfileupload';
					
					$filePath = Yii::getPathOfAlias('webroot') . DIRECTORY_SEPARATOR . $folder. DIRECTORY_SEPARATOR .$filename.'.xlsx';
					
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
						WHERE ls.skill_id = '".$report->skill_id."'
						AND lch.disposition IS NOT NULL 
						AND lch.start_call_time >= '".$dateFilterStart."' 
						AND lch.start_call_time <= '".$dateFilterEnd."' 
						AND lch.status !=4 
						AND lch.customer_id = '".$report->customer_id."'
						ORDER BY lch.start_call_time DESC
					";
					
					$connection = Yii::app()->db;
					$command = $connection->createCommand($sql);
					$models = $command->queryAll();	
					
					// echo 'dateFilterStart: ' . $dateFilterStart; 
					// echo '<br><br>';
					// echo 'dateFilterEnd: ' . $dateFilterEnd; 
					// echo '<br><br>';
					// echo 'count: ' . count($models);
					// exit;
					
					if( $models )
					{
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
						
						$ctr = 2;
						
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
						
						//output to browser
						// header('Content-Type: application/vnd.ms-excel'); 
						// header('Content-Disposition: attachment; filename="'.$filename.'.xlsx"'); 
						// header('Cache-Control: max-age=0');
						
						//output to folder
						$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
						// $objWriter->save('php://output');
						
						$objWriter->save($filePath);
					}
				}

				if( $report->report_name == 'aaaReport' )
				{
					$filename = 'AAA Report - '. $report->customer->getFullName().' - ' . date('m d Y');

					$folder = $report->type == 1 ? 'fileupload' : 'tempfileupload';
					
					$filePath = Yii::getPathOfAlias('webroot') . DIRECTORY_SEPARATOR . $folder. DIRECTORY_SEPARATOR .$filename.'.xlsx';
					
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
						WHERE ls.skill_id = '".$report->skill_id."'
						AND lch.disposition IS NOT NULL 
						AND lch.start_call_time >= '".$dateFilterStart."' 
						AND lch.start_call_time <= '".$dateFilterEnd."' 
						AND lch.status !=4 
						AND lch.customer_id = '".$report->customer_id."'
						ORDER BY lch.start_call_time DESC
					";
					
					$connection = Yii::app()->db;
					$command = $connection->createCommand($sql);
					$models = $command->queryAll();	
					
					echo 'dateFilterStart: ' . $dateFilterStart; 
					echo '<br><br>';
					echo 'dateFilterEnd: ' . $dateFilterEnd; 
					echo '<br><br>';
					echo 'count: ' . count($models);
					exit;
					
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
						
						$ctr = 2;
						
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
						
						//output to browser
						// header('Content-Type: application/vnd.ms-excel'); 
						// header('Content-Disposition: attachment; filename="'.$filename.'.xlsx"'); 
						// header('Cache-Control: max-age=0');
						
						//output to folder
						$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
						// $objWriter->save('php://output');

						$objWriter->save($filePath);
					}
				}
				
				if( !empty($filePath) && file_exists($filePath) )
				{
					if( $report->type == 1 ) //send to customer files
					{
						$fileUpload = new Fileupload;
						$fileUpload->original_filename = $filename.'.xlsx';
						$fileUpload->generated_filename = $filename.'.xlsx';
						
						if( $fileUpload->save(false) )
						{
							$customerFile = new CustomerFile;
							
							$customerFile->setAttributes(array(
								'customer_id' => $report->customer_id,
								'fileupload_id' => $fileUpload->id,
								'user_account_id' => $report->account_id,
							));
							
							if( $customerFile->save(false) )
							{
								$report->date_last_sent = date('Y-m-d H:i:s');
								
								if( $report->save(false) )
								{
									echo 'Customer file created.';
								}
							}
						}
					}
					else //send to email_address
					{
						Yii::import('application.extensions.phpmailer.JPhpMailer');
							
						$mail = new JPhpMailer;
					
						$mail->SMTPAuth = true;		
						$mail->SMTPSecure = 'tls';   		
						$mail->SMTPDebug = 2; 
						$mail->Port = 25;      
						$mail->Host = 'mail.engagex.com';	
						$mail->Username = 'service@engagex.com';  
						$mail->Password = "_T*8c>ja";  
						$mail->SetFrom('service@engagex.com', 'Engagex Service', 0);
						
						$mail->AddBCC('erwin.datu@engagex.com');

						foreach( $emails as $email )
						{
							$mail->AddAddress($email);
						}
						
						$mail->Subject = $filename;
						$mail->MsgHTML( $filename );
						$mail->AddAttachment( $folder. DIRECTORY_SEPARATOR .$filename.'.xlsx' );
						
						if( $mail->Send() )
						{
							$report->date_last_sent = date('Y-m-d H:i:s');
							
							if( $report->save(false) )
							{
								echo 'Email sent';
							}
						}
					}
				}
				else
				{
					echo 'File not found.';
				}
			}
		}
	}
}

