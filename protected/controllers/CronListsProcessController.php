<?php 

ini_set('memory_limit', '10000M');
set_time_limit(0);

class CronListsProcessController extends Controller
{
	public function accessRules()
	{
		return array(
			array('allow',  // deny all users
				'users'=>array('*'),
			),
		);
	}
	
	public function actionProcessLeadUploads()
	{
		// if( !isset($_GET['debug']) )
		// {
			// exit;
		// }  
		
		$criteria = new CDbCriteria;
		$criteria->compare('on_going',0);
		$criteria->compare('date_completed', '0000-00-00');
		
		$listsCronProcessQueue = ListsCronProcess::model()->find($criteria);
		// $listsCronProcessQueue = ListsCronProcess::model()->findByPk(15592);
		
		$criteria = new CDbCriteria;
		$criteria->compare('on_going',1);
		$criteria->compare('date_completed', '0000-00-00');
		
		$listsCronProcessOngoing = ListsCronProcess::model()->find($criteria);
		
		if(isset($listsCronProcessQueue->fileupload))
		{
			$fileExists = file_exists('leads/' . $listsCronProcessQueue->fileupload->generated_filename);
				
			if( !$fileExists )
			{
				$fileExists =  file_exists('fileupload/' . $listsCronProcessQueue->fileupload->generated_filename);
			}

			echo 'filename: ' . $listsCronProcessQueue->fileupload->generated_filename;
			
			echo '<br><br>';
			
			echo $fileExists == 1 ? 'file exists' : 'file not found';
			 
			echo '<br><br>';
			
			echo 'listsCronProcessOngoing: <br>';
			// print_r($listsCronProcessOngoing->attributes);
		}
		// exit;
		
		echo '<pre>';
			echo 'listsCronProcessQueue: <br>';
			// print_r($listsCronProcessQueue->attributes);
			
			echo '<br />';
			
			
		// exit;
		
		if(!empty($listsCronProcessOngoing))
		{
			echo 'There is still ongoing upload';
			echo '<br>';
			echo '<pre>';
			// print_r($listsCronProcessOngoing->attributes);
			echo '</pre>';
		}
		
		// exit;
		
		if(empty($listsCronProcessQueue))
		{
			echo 'No pending list to process';
			echo '<br>';
		}
		
		if(empty($listsCronProcessOngoing) && !empty($listsCronProcessQueue))
		{
			$listsCronProcessQueue->on_going = 1;
			$listsCronProcessQueue->date_started = date('Y-m-d H:i:s');
			$listsCronProcessQueue->save(false);
			 
			
			$model = $listsCronProcessQueue->list;
			$customer_id = $listsCronProcessQueue->list->customer_id;
			$customer = Customer::model()->findByPk($customer_id);
			$company = $customer->company;
			
			// $customerCalendar = Calendar::model()->find(array(
				// 'condition' => 'customer_id = :customer_id',
				// 'params' => array(
					// ':customer_id' => $customer_id,
				// ),
			// ));
			
			$customerSkill = CustomerSkill::model()->find(array(
				'condition' => '
					customer_id = :customer_id 
					AND skill_id = :skill_id
					AND status=1
				',
				'params' => array(
					':customer_id' => $customer_id,
					':skill_id' => $model->skill_id,
				),
				'order' => 't.id DESC',
			));
			
			$contract = $customerSkill->contract;
			
			
			$transaction = Yii::app()->db->beginTransaction();
			
			try
			{
				##### START OF IMPORTING PROCESS #######
				
				$leadsImported = 0;
				$duplicateLeadsCtr = 0;
				$badLeadsCtr = 0;
				$existingLeadUpdatedCtr = 0;
				$dncCtr = 0;
				$dcWnCtr = 0;
				$cellphoneScrubCtr = 0;
				$namesWaitingCtr = 0;
				
				$importLimit = 0;
				$contractedLeads = 0;
				
				$cellphoneScrubApiFields = array(
					'CO_CODE' => '109629',
					'PASS' => '1860So!!',
					'TYPE' => 'api_atn',
				); 
				
				//if contract fullfillment type is GOAL apply the 10x rule and limit customers monthly import
				if(isset($contract))
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
											$contractedLeads += ( $subsidyLevel['goal'] * $customerSkillLevelArrayGroup->quantity );
										}
									}
								}
							}
							
							$importLimit = $contractedLeads * 10;
						}
						else
						{
							foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME] as $key => $subsidyLevel )
							{
								$customerSkillLevelArray = $customerSkill->getCustomerSkillLevelArray();
								$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;

								if( $customerSkillLevelArrayGroup != null )
								{							
									if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
									{
										$contractedLeads += ( $subsidyLevel['high'] * $customerSkillLevelArrayGroup->quantity );
									}
								}
							}
							
							$importLimit = $contractedLeads;
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
								$importLimit += $customerExtra->quantity * 10;
							}
						}						
					}			

					if( $importLimit > 0 )
					{
						$existingCustomerLeadImportLog = CustomerLeadImportLog::model()->find(array(
							'condition' => '
								customer_id = :customer_id 
								AND skill_id = :skill_id 
								AND month = :month 
								AND year = :year
							',
							'params' => array(
								':customer_id' => $customer_id,
								':skill_id' => $model->skill_id,
								':month' => date('F'),
								':year' => date('Y'),
							),
						));
						
						if( $existingCustomerLeadImportLog )
						{
							$customerLeadImportLog = $existingCustomerLeadImportLog;
						}
						else
						{
							$customerLeadImportLog = new CustomerLeadImportLog;
							
							$customerLeadImportLog->setAttributes(array(
								'customer_id' => $customer_id,
								'contract_id' => $contract->id,
								'skill_id' => $model->skill_id,
								'month' => date('F'),
								'year' => date('Y'),
							));
							
							$customerLeadImportLog->save(false);
						}
					}
				}
				
				//temporary override for graton breach security list
				if( $model->id == 10857 )
				{ 
					$importLimit = 50000;
				}
					
				//import from leads waiting
				if( !empty($listsCronProcessQueue->import_from_leads_waiting) )
				{
					$leadsWaiting = Lead::model()->findAll(array(
						'together' => true,
						'condition' => 't.customer_id = :customer_id AND t.list_id IS NULL AND t.type=1 AND t.status !=4',
						'params' => array(
							':customer_id' => $customer_id,
						),
						'limit' => $importLimit
					));
			
					if( $leadsWaiting )
					{
						foreach( $leadsWaiting as $leadsWaitingModel )
						{
							$customerLeadImportLog = CustomerLeadImportLog::model()->find(array(
								'condition' => 'customer_id = :customer_id AND month = :month AND year = :year',
								'params' => array(
									':customer_id' => $customer_id,
									':month' => date('F'),
									':year' => date('Y'),
								),
							));
							
							$leadsWaitingModel->list_id = $model->id;
							
							if( $customerLeadImportLog && $customerLeadImportLog->leads_imported < $importLimit )
							{
								if( $leadsWaitingModel->save(false) )
								{
									$leadsImported++;
										
									if( $customerLeadImportLog )
									{
										$customerLeadImportLog->leads_imported = $customerLeadImportLog->leads_imported + 1;
										$customerLeadImportLog->save(false);
									}
								}
							}
						}
					}
					
					if( $leadsImported > 0 )
					{
						$result['status'] = 'success';
						$result['message'] = '<b>'.$model->name.'</b> was updated successfully.';
						
						$history = new CustomerHistory;
						
						$history->setAttributes(array(
							'model_id' => $model->id, 
							'customer_id' => $model->customer_id,
							'user_account_id' => Yii::app()->user->account->id,
							'page_name' => 'Leads',
							'content' => $model->name.' | '.$leadsImported.' Leads Imported from Names waiting',
							'type' => $history::TYPE_UPDATED,
						));
						
						$history->save(false);
					}
					else
					{
						$result['status'] = 'error';
						$result['message'] = 'No leads imported.';
					}
				}
						
				if( isset($listsCronProcessQueue->fileupload) )
				{
					$fileExists = file_exists('leads/' . $listsCronProcessQueue->fileupload->generated_filename);
					$inputFileName = 'leads/' . $listsCronProcessQueue->fileupload->generated_filename;
					
					if( !$fileExists )
					{
						$fileExists =  file_exists('fileupload/' . $listsCronProcessQueue->fileupload->generated_filename);
						$inputFileName = 'fileupload/' . $listsCronProcessQueue->fileupload->generated_filename;
					}
				
					//import from fileupload-
					if( !empty($listsCronProcessQueue->fileupload_id) && $fileExists )
					{
						// unregister Yii's autoloader
						spl_autoload_unregister(array('YiiBase', 'autoload'));
					
						$phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
						include($phpExcelPath . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'PHPExcel.php');

						spl_autoload_register(array('YiiBase', 'autoload'));
						 

						$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
						
						$worksheet = $objPHPExcel->getActiveSheet();

						// $highestRow         = $worksheet->getHighestRow(); // e.g. 10
						$highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
						$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
						$nrColumns = ord($highestColumn) - 64;
						
						$maxCell = $worksheet->getHighestRowAndColumn();
						$excelData = $worksheet->rangeToArray('A1:' . $maxCell['column'] . $maxCell['row']);
						$excelData = array_map('array_filter', $excelData);
						$excelData = array_filter($excelData);
						
						$highestRow = count($excelData);
						
						$validTemplate = true;
						$useDefaultTemplate = true;
						
						$col1 = $worksheet->getCell('A1')->getValue();
						$col2 = $worksheet->getCell('B1')->getValue();
						$col3 = $worksheet->getCell('C1')->getValue();
						$col4 = $worksheet->getCell('D1')->getValue();
						$col5 = $worksheet->getCell('E1')->getValue();
						$col6 = $worksheet->getCell('F1')->getValue();
						$col7 = $worksheet->getCell('G1')->getValue();
						$col8 = $worksheet->getCell('H1')->getValue();
						$col9 = $worksheet->getCell('I1')->getValue();
						$col10 = $worksheet->getCell('J1')->getValue();
						$col11 = $worksheet->getCell('K1')->getValue();
						$col12 = $worksheet->getCell('L1')->getValue();
						$col13 = $worksheet->getCell('M1')->getValue();
						
						if( 
							strtoupper($col1) != 'LAST NAME' 
							|| strtoupper($col2) != 'FIRST NAME' 
							|| strtoupper($col3) != 'PARTNER FIRST NAME' 
							|| strtoupper($col4) != 'PARTNER LAST NAME' 
							|| strtoupper($col5) != 'ADDRESS 1' 
							|| strtoupper($col6) != 'ADDRESS 2' 
							|| strtoupper($col7) != 'CITY' 
							|| strtoupper($col8) != 'STATE' 
							|| strtoupper($col9) != 'ZIP' 
							|| strtoupper($col10) != 'OFFICE PHONE'  
							|| strtoupper($col11) != 'MOBILE PHONE'  
							|| strtoupper($col12) != 'HOME PHONE'						
							|| strtoupper($col13) != 'EMAIL ADDRESS'						
						)
						{
							$validTemplate = false;
						}
						
						$validColumns = array('first name', 'last name', 'phone 1', 'phone 2', 'phone 3');
						$columnsInFile = array();
							
						if( !$validTemplate )
						{
							foreach( range('A', $highestColumn) as $columnInFile )
							{
								if( !empty($columnInFile) )
								{
									$columnsInFile[$columnInFile] = strtolower($worksheet->getCell($columnInFile.'1')->getValue());
								}
							}
							
							if( $columnsInFile )
							{
								$originalColumnsInFile = $columnsInFile;
								$arrayMatch = array_intersect($validColumns, $columnsInFile);
								
								sort($validColumns);
								sort($arrayMatch);

								if( $validColumns == $arrayMatch )
								{
									$validTemplate = true;
									$useDefaultTemplate = false;
									
									$columnsInFile = $originalColumnsInFile;
								}
							}
						}
						
						if( $validTemplate )
						{
							if( $model->allow_custom_fields == 1 )
							{
								$customFieldCtr = 1;
								
								foreach ( $this->excelColumnRange('A', $highestColumn) as $columnLetter ) 
								{
									$customFieldName = $worksheet->getCell($columnLetter.'1')->getValue();
									
									if( !empty($customFieldName) )
									{
										$existingListCustomData = ListCustomData::model()->find(array(
											'condition' => '
												list_id = :list_id 
												AND customer_id = :customer_id
												AND original_name = :original_name
											',
											'params' => array(
												':list_id' => $model->id,
												':customer_id' => $model->customer_id,
												':original_name' => $customFieldName
											),
										));
										
										if( !$existingListCustomData )
										{	
											$listCustomData = new ListCustomData;
											
											$listCustomData->setAttributes(array(
												'list_id' => $model->id,
												'customer_id' => $model->customer_id,
												'custom_name' => $customFieldName,
												'original_name' => $customFieldName,
												'ordering' => $customFieldCtr,
											));
											
											if( $listCustomData->save(false) )
											{
												$customFieldCtr++;
											}
										}
									}
								}
							}
							
							for ($row = 2; $row <= $highestRow; ++$row) 
							{
								$customerLeadImportLog = CustomerLeadImportLog::model()->find(array(
									'condition' => '
										customer_id = :customer_id 
										AND skill_id = :skill_id 
										AND month = :month 
										AND year = :year
									',
									'params' => array(
										':customer_id' => $customer_id,
										':skill_id' => $model->skill_id,
										':month' => date('F'),
										':year' => date('Y'),
									),
								));
								
								if( $useDefaultTemplate )
								{
									$last_name = $worksheet->getCell('A'.$row)->getValue();
									$first_name = $worksheet->getCell('B'.$row)->getValue();
									$partner_first_name = $worksheet->getCell('C'.$row)->getValue();
									$partner_last_name = $worksheet->getCell('D'.$row)->getValue();
									$address1 = $worksheet->getCell('E'.$row)->getValue();
									$address2 = $worksheet->getCell('F'.$row)->getValue();
									$city = $worksheet->getCell('G'.$row)->getValue();
									$state = $worksheet->getCell('H'.$row)->getValue();
									$zip = $worksheet->getCell('I'.$row)->getValue();
									$office_phone_number = preg_replace("/[^0-9]/","", $worksheet->getCell('J'.$row)->getValue());
									$mobile_phone_number = preg_replace("/[^0-9]/","", $worksheet->getCell('K'.$row)->getValue());
									$home_phone_number = preg_replace("/[^0-9]/","", $worksheet->getCell('L'.$row)->getValue());
									$email_address = $worksheet->getCell('M'.$row)->getValue();
									$gender = $worksheet->getCell('N'.$row)->getValue();
									
									$specificDateCell = $worksheet->getCell('N'.$row);
									$specific_date = $specificDateCell->getValue();
									
									if( PHPExcel_Shared_Date::isDateTime($specificDateCell) ) 
									{
										$specific_date = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($specific_date)); 
										$specific_date = date('Y-m-d', strtotime($specific_date . '+1 day')); 
									}
									
									if( date('N', strtotime($specific_date)) >= 6 )
									{
										$specific_date = date('Y-m-d', strtotime('last friday', strtotime($specific_date)));
									}
									
									if( $specific_date == '2017-10-09' )
									{
										$specific_date = '2017-10-06';
									}
									
									if( $specific_date == '2017-11-10' )
									{
										$specific_date = '2017-11-09';
									}
									
									if( $specific_date == '2017-11-23' )
									{
										$specific_date = '2017-11-22';
									}
									
									if( $specific_date == '2017-12-25' )
									{
										$specific_date = '2017-12-22';
									}
									
									if( $specific_date == '2018-01-01' )
									{
										$specific_date = '2017-12-29';
									}
								}
								else
								{
									$last_name = '';
									$first_name = '';
									$gender = '';
									$partner_first_name = '';
									$partner_last_name = '';
									$address1 = '';
									$address2 ='';
									$city = '';
									$state = '';
									$zip = '';
									$office_phone_number = '';
									$mobile_phone_number = '';
									$home_phone_number = '';
									$email_address = '';
									$specific_date = '';
									
									if( $columnsInFile )
									{
										foreach( $columnsInFile as $columnInFile => $rowValue )
										{
											if( $rowValue == 'first name' )
											{
												$first_name = $worksheet->getCell($columnInFile.$row)->getValue();
											}
											
											if( $rowValue == 'last name' )
											{
												$last_name = $worksheet->getCell($columnInFile.$row)->getValue();
											}
											
											if( $rowValue == 'gender' )
											{
												$gender = $worksheet->getCell($columnInFile.$row)->getValue();
											}
											
											if( $rowValue == 'phone 1' )
											{
												$home_phone_number = preg_replace("/[^0-9]/","", $worksheet->getCell($columnInFile.$row)->getValue());
											}
											
											if( $rowValue == 'phone 2' )
											{
												$mobile_phone_number = preg_replace("/[^0-9]/","", $worksheet->getCell($columnInFile.$row)->getValue());
											}
											
											if( $rowValue == 'phone 3' )
											{
												$office_phone_number = preg_replace("/[^0-9]/","", $worksheet->getCell($columnInFile.$row)->getValue());
											}
											
											if( $rowValue == 'partner first name' )
											{
												$partner_first_name = $worksheet->getCell($columnInFile.$row)->getValue();
											}
											
											if( $rowValue == 'partner last name' )
											{
												$partner_last_name = $worksheet->getCell($columnInFile.$row)->getValue();
											}
											
											if( $rowValue == 'email address' )
											{
												$email_address = $worksheet->getCell($columnInFile.$row)->getValue();
											}
											
											if( $rowValue == 'address 1' )
											{
												$address1 = $worksheet->getCell($columnInFile.$row)->getValue();
											}
											
											if( $rowValue == 'city' )
											{
												$city = $worksheet->getCell($columnInFile.$row)->getValue();
											}
											
											if( $rowValue == 'state' )
											{
												$state = $worksheet->getCell($columnInFile.$row)->getValue();
											}
											
											if( $rowValue == 'zip' )
											{
												$zip = $worksheet->getCell($columnInFile.$row)->getValue();
											}
											
											if( $rowValue == 'call date' )
											{
												$specificDateCell = $worksheet->getCell($columnInFile.$row);
												
												$specific_date = $specificDateCell->getValue();
												
												if( PHPExcel_Shared_Date::isDateTime($specificDateCell) ) 
												{
													$specific_date = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($specific_date)); 
													$specific_date = date('Y-m-d', strtotime($specific_date . '+1 day')); 
												}
												
												if( date('N', strtotime($specific_date)) >= 6 )
												{
													$specific_date = date('Y-m-d', strtotime('last friday', strtotime($specific_date)));
												}
												
												if( $specific_date == '2017-10-09' )
												{
													$specific_date = '2017-10-06';
												}
												
												if( $specific_date == '2017-11-10' )
												{
													$specific_date = '2017-11-09';
												}
												
												if( $specific_date == '2017-11-23' )
												{
													$specific_date = '2017-11-22';
												}
												
												if( $specific_date == '2017-12-25' )
												{
													$specific_date = '2017-12-22';
												}
												
												if( $specific_date == '2018-01-01' )
												{
													$specific_date = '2017-12-29';
												}
											}
										}
									}
								}

								
								$type = 2;
								$uniqueAreaCodes = array();
								
								$office_phone_number = ltrim($office_phone_number, '1');
								$mobile_phone_number = ltrim($mobile_phone_number, '1');
								$home_phone_number = ltrim($home_phone_number, '1');
								
								$cellphoneScrubData = array();
								
								if( strlen($office_phone_number) == 10 )
								{
									$type = 1;
									
									$uniqueAreaCodes[] = substr($office_phone_number, 0, 3);
								}
								else
								{
									$office_phone_number = null;
								}
								
								if( strlen($mobile_phone_number) == 10 )
								{
									$type = 1;
									
									$uniqueAreaCodes[] = substr($mobile_phone_number, 0, 3);
								}
								else
								{
									$mobile_phone_number = null;
								}
								
								if( strlen($home_phone_number) == 10 )
								{
									$type = 1;
									
									$uniqueAreaCodes[] = substr($home_phone_number, 0, 3);
								}
								else
								{
									$home_phone_number = null;
								}

								//area code assignment checkbox - temp logic for lead with multiple phones tag them as bad number for now so customer service can check and reimport them 
								if( $model->allow_area_code_assignment == 1 )
								{
									$uniqueAreaCodes = array_unique($uniqueAreaCodes);
									
									if( count($uniqueAreaCodes) > 1 )
									{
										$type = 2;
									}
								}
								
								// echo 'first_name: ' . $first_name;
								// echo '<br>';
								// echo 'last_name: ' . $last_name;
								// echo '<br>';
								// echo 'office_phone_number ('.strlen($office_phone_number).') - ' . $office_phone_number;
								// echo '<br>';
								// echo 'mobile_phone_number ('.strlen($mobile_phone_number).') - ' . $mobile_phone_number;
								// echo '<br>';
								// echo 'home_phone_number ('.strlen($home_phone_number).') - ' . $home_phone_number;
								
								// exit;
	
								if( $type == 1 )
								{
									//DNC AND DC/WN Scrubbing
									if( $company->scrub_settings > 0  )
									{
										//ON Customer WN
										if( $company->scrub_settings == 1 )
										{
											$existingDcwn = Dcwn::model()->find(array(
												'condition' => 'customer_id = :customer_id AND phone_number = :phone_number',
												'params' => array(
													':customer_id' => $customer->id,
													':phone_number' => $home_phone_number,
												),
											));
											
											if( $existingDcwn )
											{
												$dcWnCtr++;
												continue;
											}
										}
										
										//ON Customer DNC
										if( $company->scrub_settings == 2 )
										{
											$existingDnc = Dnc::model()->find(array(
												'condition' => 'customer_id = :customer_id AND phone_number = :phone_number',
												'params' => array(
													':customer_id' => $customer->id,
													':phone_number' => $home_phone_number,
												),
											));
											
											if( $existingDnc )
											{
												$dncCtr++;
												continue;
											}
										}
										
										//ON Customer BOTH
										if( $company->scrub_settings == 3 )
										{
											$existingDnc = Dnc::model()->find(array(
												'condition' => 'customer_id = :customer_id AND phone_number = :phone_number',
												'params' => array(
													':customer_id' => $customer->id,
													':phone_number' => $home_phone_number,
												),
											));
											
											if( $existingDnc )
											{
												$dncCtr++;
												continue;
											}
											
											$existingDcwn = Dcwn::model()->find(array(
												'condition' => 'customer_id = :customer_id AND phone_number = :phone_number',
												'params' => array(
													':customer_id' => $customer->id,
													':phone_number' => $home_phone_number,
												),
											));
											
											if( $existingDcwn )
											{
												$dcWnCtr++;
												continue;
											}
										}
										
										//ON COMPANY DNC
										if( $company->scrub_settings == 4 )
										{
											$existingDnc = Dnc::model()->find(array(
												'condition' => 'company_id = :company_id AND phone_number = :phone_number',
												'params' => array(
													':company_id' => $customer->company_id,
													':phone_number' => $home_phone_number,
												),
											));
											
											if( $existingDnc )
											{
												$dncCtr++;
												continue;
											}
										}
										
										//ON COMPANY WN
										if( $company->scrub_settings == 5 )
										{
											$existingDcwn = Dcwn::model()->find(array(
												'condition' => 'company_id = :company_id AND phone_number = :phone_number',
												'params' => array(
													':company_id' => $customer->company_id,
													':phone_number' => $home_phone_number,
												),
											));
											
											if( $existingDcwn )
											{
												$dcWnCtr++;
												continue;
											}
										}
										
										//ON COMPANY BOTH
										if( $company->scrub_settings == 6 )
										{
											$existingDnc = Dnc::model()->find(array(
												'condition' => 'company_id = :company_id AND phone_number = :phone_number',
												'params' => array(
													':company_id' => $customer->company_id,
													':phone_number' => $home_phone_number,
												),
											));
											
											if( $existingDnc )
											{
												$dncCtr++;
												continue;
											}
											
											$existingDcwn = Dcwn::model()->find(array(
												'condition' => 'company_id = :company_id AND phone_number = :phone_number',
												'params' => array(
													':company_id' => $customer->company_id,
													':phone_number' => $home_phone_number,
												),
											));
											
											if( $existingDcwn )
											{
												$dcWnCtr++;
												continue;
											}
										}
									}
									
									//Cellphone Scrubbing API
									// $cellphoneScrubApi = new CellphoneScrubApi;
									
									// if( $cellphoneScrubApi->process($home_phone_number) )
									// {
										// $cellphoneScrubCtr++;
										// continue;
									// }
									
									$existingLead = Lead::model()->find(array(
										'condition' => 't.customer_id = :customer_id AND t.status !=4 AND ( 
											(office_phone_number = :office_phone_number AND office_phone_number != "" AND office_phone_number IS NOT NULL) OR 
											(office_phone_number = :mobile_phone_number AND office_phone_number != "" AND office_phone_number IS NOT NULL) OR 
											(office_phone_number = :home_phone_number AND office_phone_number != "" AND office_phone_number IS NOT NULL) OR 
											(mobile_phone_number = :office_phone_number AND mobile_phone_number != "" AND mobile_phone_number IS NOT NULL) OR
											(mobile_phone_number = :mobile_phone_number AND mobile_phone_number != "" AND mobile_phone_number IS NOT NULL) OR
											(mobile_phone_number = :home_phone_number AND mobile_phone_number != "" AND mobile_phone_number IS NOT NULL) OR
											(home_phone_number = :office_phone_number AND home_phone_number != "" AND home_phone_number IS NOT NULL) OR
											(home_phone_number = :mobile_phone_number AND home_phone_number != "" AND home_phone_number IS NOT NULL) OR
											(home_phone_number = :home_phone_number AND home_phone_number != "" AND home_phone_number IS NOT NULL) 
										)',
										'params' => array(
											':customer_id' => $customer_id,
											':office_phone_number' => $office_phone_number,
											':mobile_phone_number' => $mobile_phone_number,
											':home_phone_number' => $home_phone_number,
										),
									));
								}
								else
								{
									$existingLead = array();
								}
								
								if( !empty($existingLead) )
								{
									//recycle - recertify  module
									$existingLead->recertify_date = $this->_computeForSkillMaxLeadLifeBeforeRecertify($model);
									
									if( $model->duplicate_action !== null )
									{
										//save custom lead fields that are not in template
										if( $model->allow_custom_fields == 1 )
										{
											// if( $model->skill->enable_specific_date_calling == 1 )
											// {
												$lastOfStandardColumn = 'N';
												$startofCustomColumn = 'O';
											// }
											// else
											// {
												// $lastOfStandardColumn = 'M';
												// $startofCustomColumn = 'N';
											// }
											
											if( $highestColumn > $lastOfStandardColumn || strlen($highestColumn) > 1 )
											{
												foreach ( $this->excelColumnRange($startofCustomColumn, $highestColumn) as $columnLetter ) 
												{
													$customFieldName = $worksheet->getCell($columnLetter.'1')->getValue();
													$customFieldValue = $worksheet->getCell($columnLetter.$row)->getValue();
													
													if( !empty($customFieldName) )
													{
														$customData = new LeadCustomData;
														
														$customData->setAttributes(array(
															'lead_id' => $existingLead->id,
															'list_id' => $model->id,
															'field_name' => $customFieldName,
															'value' => $customFieldValue,
														));
														
														$customData->save(false);
													}
												}
											}
											
											$memberNumberCustomData = LeadCustomData::model()->find(array(
												'with' => 'list',
												'condition' => '
													t.lead_id = :lead_id 
													AND t.list_id = :list_id
													AND t.field_name = :field_name
													AND list.status != 3
												',
												'params' => array(
													':lead_id' => $existingLead->id,
													':list_id' => $model->id,
													':field_name' => 'Member Number',
												),
												'order' => 't.date_created DESC',
											));
											
											if( $memberNumberCustomData )
											{
												$existingCustomDatas = LeadCustomData::model()->findAll(array(
													'condition' => '
														lead_id = :lead_id 
														AND list_id = :list_id
														AND member_number IS NULL
													',
													'params' => array(
														':lead_id' => $existingLead->id,
														':list_id' => $model->id,
													),
												));
												
												if( $existingCustomDatas )
												{
													foreach( $existingCustomDatas as $existingCustomData )
													{
														$existingCustomData->member_number = $memberNumberCustomData->value;
														$existingCustomData->save(false);
													}
												}
											}
										}
										
										
										if( $model->duplicate_action == $model::DUPLICATES_UPDATE_LEAD_INFO || $model->duplicate_action == $model::DUPLICATES_UPDATE_LEAD_INFO_RESET_DIALS )
										{
											$existingLead->setAttributes(array(
												'list_id' => $model->id,
												'last_name' => $last_name,
												'first_name' => $first_name,
												'gender' => $gender,
												'partner_first_name' => $partner_first_name,
												'partner_last_name' => $partner_last_name,
												'address' => $address1,
												'address2' => $address2,
												'city' => $city,
												'state' => $state,
												'zip_code' => $zip,
												'office_phone_number' => $office_phone_number,
												'mobile_phone_number' => $mobile_phone_number,
												'home_phone_number' => $home_phone_number,
												'email_address' => $email_address,
												'type' => $type,
												'specific_date' => $specific_date,
												// 'language' => $model->language,
												'timezone' => AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $model->calendar->office->phone) ),
											));
											
											//area code assignment checkbox - follow leads phone instead of customers office phone to determine the timezone
											if( $model->allow_area_code_assignment == 1 )
											{
												if( $office_phone_number != null )
												{
													$existingLead->timezone = AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $office_phone_number) );
												}
												
												if( $mobile_phone_number != null )
												{
													$existingLead->timezone = AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $mobile_phone_number) );
												}
												
												if( $home_phone_number != null )
												{
													$existingLead->timezone = AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $home_phone_number) );
												}
											}
											
											if( $model->duplicate_action == $model::DUPLICATES_UPDATE_LEAD_INFO_RESET_DIALS )
											{
												$resetDials = false;
												
												//if list skill assignment is NSU, INACTIVE OR DECLINERS allow dial reset for completed leads
												if( in_array($model->skill_id, array(36,37,38)) )
												{
													$resetDials = true;
												}
												else
												{
													if( $existingLead->status != 3 )
													{
														$resetDials = true;
													}
												}
												
												if( $resetDials )
												{
													$existingLead->number_of_dials = 0;
													$existingLead->home_phone_dial_count = 0;
													$existingLead->mobile_phone_dial_count = 0;
													$existingLead->office_phone_dial_count = 0;
													$existingLead->status = 1;
												}
											}
											
											if( $existingLead->save(false) )
											{
												$existingLeadUpdatedCtr++;
												
												if( in_array($model->skill_id, array(36,37,38)) )
												{
													$this->createImportHistory($existingLead);
												}
											}
										}
										elseif( $model->duplicate_action == $model::MOVE_LEAD_TO_CURRENT_LIST_RESET_DIALS && $existingLead->status != 3 )
										{
											$existingLead->list_id = $model->id;
											$existingLead->number_of_dials = 0;
											$existingLead->home_phone_dial_count = 0;
											$existingLead->mobile_phone_dial_count = 0;
											$existingLead->office_phone_dial_count = 0;
											$existingLead->status = 1;
											
											if( $existingLead->save(false) )
											{
												$existingLeadUpdatedCtr++;
											}
										}
										elseif( $model->duplicate_action == $model::CUSTOMER_SERVICE_OVERRIDE && $existingLead->is_do_not_call == 0 )
										{
											$existingLead->list_id = $model->id;
											$existingLead->number_of_dials = 0;
											$existingLead->home_phone_dial_count = 0;
											$existingLead->mobile_phone_dial_count = 0;
											$existingLead->office_phone_dial_count = 0;
											$existingLead->status = 1;
											
											if( $existingLead->save(false) )
											{
												$existingLeadUpdatedCtr++;
											}
										}
										elseif( $model->duplicate_action == $model::MOVE_RECERTIFIABLE_LEAD_TO_CURRENT_LIST )
										{
											if( $customerLeadImportLog )
											{
												if( 
													$existingLead->status == 1 
													&& ($existingLead->recertify_date == "0000-00-00" || $existingLead->recertify_date == null || time() >= strtotime($existingLead->recertify_date)) 
												)
												{
													if( $customerLeadImportLog->leads_imported < $importLimit )
													{
														$existingLead->list_id = $model->id;
														$existingLeadUpdatedCtr++;
													}
													else
													{
														$existingLead->list_id = null;
														$namesWaitingCtr++;
													}
				
													if( $existingLead->save(false) )
													{
														if( $customerLeadImportLog )
														{
															$customerLeadImportLog->leads_imported = $customerLeadImportLog->leads_imported + 1;
															$customerLeadImportLog->save(false);
														}
													}
												}
												else
												{
													$duplicateLeadsCtr++;
												}
											}
										}
										elseif( $model->duplicate_action == $model::MOVE_RECYCLABLE_LEAD_TO_CURRENT_LIST )
										{
											if( $customerLeadImportLog )
											{
												if( 
													$existingLead->recycle_lead_call_history_id != null 
													&& ($existingLead->status == 3 || $existingLead->number_of_dials >= $model->skill->max_dials) 
													&& ($existingLead->recycle_date == "0000-00-00" || $existingLead->recycle_date == null || time() >= strtotime($existingLead->recycle_date)) 
												)
												{
													if( $customerLeadImportLog->leads_imported < $importLimit )
													{
														$existingLead->list_id = $model->id;
														$existingLeadUpdatedCtr++;
													}
													else
													{
														$existingLead->list_id = null;
														$namesWaitingCtr++;
													}
													
													$existingLead->status = 1;
													$existingLead->number_of_dials = 0;
													$existingLead->home_phone_dial_count = 0;
													$existingLead->mobile_phone_dial_count = 0;
													$existingLead->office_phone_dial_count = 0;
													
													$existingLead->recycle_date = NULL;
													$existingLead->recycle_lead_call_history_id = NULL;
													$existingLead->recycle_lead_call_history_disposition_id = NULL;
													
													$existingLead->recertify_date = $this->_computeForSkillMaxLeadLifeBeforeRecertify($model);
				
													if( $existingLead->save(false) )
													{
														$leadHistory = new LeadHistory;
												
														$leadHistory->setAttributes(array(
															'lead_id' => $existingLead->id,
															'note' => 'Lead was recycled and certified for DNC compliance.',
															'type' => 1,
														));
														
														$leadHistory->save(false);
														
														if( $customerLeadImportLog )
														{
															$customerLeadImportLog->leads_imported = $customerLeadImportLog->leads_imported + 1;
															$customerLeadImportLog->save(false);
														}
													}
												}
												else
												{
													$duplicateLeadsCtr++;
												}
											}
										}
										elseif( $model->duplicate_action == $model::CUSTOMER_SERVICE_ALLOW_DUPLICATES && $existingLead->is_do_not_call == 0 )
										{
											$lead = new Lead;
											$lead->list_id = $model->id;
											
											if( $customerLeadImportLog && $customerLeadImportLog->leads_imported > $importLimit )
											{
												$lead->list_id = null;
												$namesWaitingCtr++;
											}

											$lead->setAttributes(array(
												'customer_id' => $model->customer_id,
												'last_name' => $last_name,
												'first_name' => $first_name,
												'gender' => $gender,
												'partner_first_name' => $partner_first_name,
												'partner_last_name' => $partner_last_name,
												'address' => $address1,
												'address2' => $address2,
												'city' => $city,
												'state' => $state,
												'zip_code' => $zip,
												'office_phone_number' => $office_phone_number,
												'mobile_phone_number' => $mobile_phone_number,
												'home_phone_number' => $home_phone_number,
												'email_address' => $email_address,
												'type' => $type,
												'specific_date' => $specific_date,
												// 'language' => $model->language,
												'timezone' => AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $model->calendar->office->phone) ),
											));
											
											//area code assignment checkbox - follow leads phone instead of customers office phone to determine the timezone
											if( $model->allow_area_code_assignment == 1 )
											{
												if( $office_phone_number != null )
												{
													$lead->timezone = AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $office_phone_number) );
												}
												
												if( $mobile_phone_number != null )
												{
													$lead->timezone = AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $mobile_phone_number) );
												}
												
												if( $home_phone_number != null )
												{
													$lead->timezone = AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $home_phone_number) );
												}
											}
											
											//recycle - recertify  module
											$lead->recertify_date = $this->_computeForSkillMaxLeadLifeBeforeRecertify($model);	
											
											if( $lead->save(false) )
											{
												if( $type == 1 )
												{
													//save custom lead fields that are not in template
													if( $model->allow_custom_fields == 1 )
													{
														if( $highestColumn > 'M' )
														{
															foreach ( range('N', $highestColumn) as $columnLetter ) 
															{
																$customFieldName = $worksheet->getCell($columnLetter.'1')->getValue();
																$customFieldValue = $worksheet->getCell($columnLetter.$row)->getValue();
																
																$customData = new LeadCustomData;
																
																$customData->setAttributes(array(
																	'lead_id' => $lead->id,
																	'list_id' => $model->id,
																	'field_name' => $customFieldName,
																	'value' => $customFieldValue,
																));
																
																$customData->save(false);
															}
														}
														
														$memberNumberCustomData = LeadCustomData::model()->find(array(
															'condition' => '
																lead_id = :lead_id 
																AND list_id = :list_id
																AND field_name = :field_name
															',
															'params' => array(
																':lead_id' => $lead->id,
																':list_id' => $model->id,
																':field_name' => 'Member Number',
															),
														));
														
														if( $memberNumberCustomData )
														{
															$existingCustomDatas = LeadCustomData::model()->findAll(array(
																'condition' => '
																	lead_id = :lead_id 
																	AND list_id = :list_id
																	AND member_number IS NULL
																',
																'params' => array(
																	':lead_id' => $lead->id,
																	':list_id' => $model->id,
																),
															));
															
															if( $existingCustomDatas )
															{
																foreach( $existingCustomDatas as $existingCustomData )
																{
																	$existingCustomData->member_number = $memberNumberCustomData->member_number;
																	$existingCustomData->save(false);
																}
															}
														}
													}
													
													// $leadHistory = new LeadHistory;
											
													// $leadHistory->setAttributes(array(
														// 'lead_id' => $lead->id,
														// 'agent_account_id' => Yii::app()->user->account->id,
														// 'type' => 4,
													// ));	
													
													// $leadHistory->save(false);
													
													
													//add history for allowed duplicates
													$allowedDupHistory = new LeadHistory;
											
													$allowedDupHistory->setAttributes(array(
														'lead_id' => $lead->id,
														'agent_account_id' => Yii::app()->user->account->id,
														'note' => 'Duplicate allowed to be imported',
														'type' => 1,
													));	
													
													$allowedDupHistory->save(false);
													
													
													//copy lead history from existing lead
													$existingHistories = LeadHistory::model()->findAll(array(
														'condition' => 'lead_id = :lead_id',
														'params' => array(
															':lead_id' => $existingLead->id,
														), 
													));
													
													if( $existingHistories )
													{
														foreach( $existingHistories as $existingHistory )
														{
															$copiedHistory = new LeadHistory;
															$copiedHistory->attributes = $existingHistory->attributes;
															
															$copiedHistory->lead_id = $lead->id;
															
															$copiedHistory->save(false);
															
															
															//overwrite date created to still display the ORIGINAL date
															$copiedHistory->date_created = $existingHistory->date_created;
															$copiedHistory->save(false);
														}
													}
													
													if( $customerLeadImportLog->leads_imported <= $importLimit )
													{
														$leadsImported++;
													}
													
													if( $customerLeadImportLog && $customerLeadImportLog->leads_imported <= $importLimit )
													{
														$customerLeadImportLog->leads_imported = $customerLeadImportLog->leads_imported + 1;
														$customerLeadImportLog->save(false);
													}
												}
												else
												{
													$badLeadsCtr++;
												}
											}
										}
										else
										{
											$duplicateLeadsCtr++;
										}
									}
									else
									{
										$duplicateLeadsCtr++;
									}
								}
								else
								{
									$lead = new Lead;
									$lead->list_id = $model->id;
									
									if( $customerLeadImportLog )
									{
										if( $customerLeadImportLog->leads_imported > $importLimit )
										{
											$lead->list_id = null;
											$namesWaitingCtr++;
										}
										else
										{
											$leadsImported++;
										}
									}

									$lead->setAttributes(array(
										'customer_id' => $model->customer_id,
										'last_name' => $last_name,
										'first_name' => $first_name,
										'gender' => $gender,
										'partner_first_name' => $partner_first_name,
										'partner_last_name' => $partner_last_name,
										'address' => $address1,
										'address2' => $address2,
										'city' => $city,
										'state' => $state,
										'zip_code' => $zip,
										'office_phone_number' => $office_phone_number,
										'mobile_phone_number' => $mobile_phone_number,
										'home_phone_number' => $home_phone_number,
										'email_address' => $email_address,
										'type' => $type,
										'language' => $model->language,
										'specific_date' => $specific_date,
										'timezone' => AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $model->calendar->office->phone) ),
									));
									
									//recycle - recertify  module
									$lead->recertify_date = $this->_computeForSkillMaxLeadLifeBeforeRecertify($model);

									//area code assignment checkbox - follow leads phone instead of customers office phone to determine the timezone
									if( $model->allow_area_code_assignment == 1 )
									{
										if( $office_phone_number != null )
										{
											$lead->timezone = AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $office_phone_number) );
										}
										
										if( $mobile_phone_number != null )
										{
											$lead->timezone = AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $mobile_phone_number) );
										}
										
										if( $home_phone_number != null )
										{
											$lead->timezone = AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $home_phone_number) );
										}
									}									
									
									if( $lead->save(false) )
									{
										if( $type == 1 )
										{
											$leadHistory = new LeadHistory;
									
											$leadHistory->setAttributes(array(
												'lead_id' => $lead->id,
												'agent_account_id' => Yii::app()->user->account->id,
												'type' => 4,
											));	
											
											$leadHistory->save(false);											
											
											
											if( $customerLeadImportLog && $customerLeadImportLog->leads_imported <= $importLimit )
											{
												$customerLeadImportLog->leads_imported = $customerLeadImportLog->leads_imported + 1;
												$customerLeadImportLog->save(false);
											}
											
											//save custom lead fields that are not in template
											if( $model->allow_custom_fields == 1 )
											{
												if( $model->skill->enable_specific_date_calling == 1 || $company->is_host_dialer == 1 )
												{
													$lastOfStandardColumn = 'O';
													$startofCustomColumn = 'P';
												}
												else
												{
													$lastOfStandardColumn = 'M';
													$startofCustomColumn = 'N';
												}
												
												if( $highestColumn > $lastOfStandardColumn )
												{
													foreach ( range($startofCustomColumn, $highestColumn) as $columnLetter ) 
													{
														$customFieldName = $worksheet->getCell($columnLetter.'1')->getValue();
														$customFieldValue = $worksheet->getCell($columnLetter.$row)->getValue();
														
														$customData = new LeadCustomData;
														
														$customData->setAttributes(array(
															'lead_id' => $lead->id,
															'list_id' => $model->id,
															'field_name' => $customFieldName,
															'value' => $customFieldValue,
														));
														
														$customData->save(false);
													}
												}
												
												$memberNumberCustomData = LeadCustomData::model()->find(array(
													'condition' => '
														lead_id = :lead_id 
														AND list_id = :list_id
														AND field_name = :field_name
													',
													'params' => array(
														':lead_id' => $lead->id,
														':list_id' => $model->id,
														':field_name' => 'Member Number',
													),
												));
												
												if( $memberNumberCustomData )
												{
													$existingCustomDatas = LeadCustomData::model()->findAll(array(
														'condition' => '
															lead_id = :lead_id 
															AND list_id = :list_id
															AND member_number IS NULL
														',
														'params' => array(
															':lead_id' => $lead->id,
															':list_id' => $model->id,
														),
													));
													
													if( $existingCustomDatas )
													{
														foreach( $existingCustomDatas as $existingCustomData )
														{
															$existingCustomData->member_number = $memberNumberCustomData->member_number;
															$existingCustomData->save(false);
														}
													}
												}
											}
										}
										else
										{
											$badLeadsCtr++;
										}
									}
								}	
							
								//LEAD JUNK CODE STARTS HERE, archiving Duplicate and Bad Leads
								
								$isDuplicate = 0;
								$isBadNumber = 0;
								
								if( !empty($existingLead) )
								{
									$isDuplicate = 1;
								}
								
								if( $type != 1 )
								{
									$isBadNumber = 1;
								}
								
								if( $isDuplicate == 1 ||  $isBadNumber == 1)
								{
									$leadJunk = new LeadJunk;
									
									$leadJunk->list_id = $model->id;								
									$leadJunk->setAttributes(array(
										'customer_id' => $model->customer_id,
										'last_name' => $last_name,
										'first_name' => $first_name,
										'gender' => $gender,
										'partner_first_name' => $partner_first_name,
										'partner_last_name' => $partner_last_name,
										'address' => $address1,
										'address2' => $address2,
										'city' => $city,
										'state' => $state,
										'zip_code' => $zip,
										'office_phone_number' => $office_phone_number,
										'mobile_phone_number' => $mobile_phone_number,
										'home_phone_number' => $home_phone_number,
										'email_address' => $email_address,
										'type' => $type,
										'language' => $model->language,
										'timezone' => AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $model->calendar->office->phone) ),
										'is_duplicate' => $isDuplicate,
										'is_bad_number' => $isBadNumber,
									));
									
									$leadJunk->save(false);
								}
							}
						
							$history = new CustomerHistory;
							
							$history->setAttributes(array(
								'model_id' => $model->id, 
								'customer_id' => $model->customer_id,
								'user_account_id' => $listsCronProcessQueue->account_id,
								'page_name' => 'Leads',
								'content' => $model->name.' | '.($highestRow-1).' leads in list | ' . $leadsImported . ' leads imported | '.$existingLeadUpdatedCtr.' existing leads updated | '.$dncCtr.' DNC | '.$dcWnCtr.' DC/WN | '.$cellphoneScrubCtr.' cellphone | <a href="../../../history/leadReport/is_duplicate/1/customer_id/'.$model->customer_id.'/list_id/'.$model->id.'">'.$duplicateLeadsCtr.' duplicates </a>| <a href="../../../history/leadReport/is_bad_number/1/customer_id/'.$model->customer_id.'/list_id/'.$model->id.'">'.$badLeadsCtr.' bad leads </a> | '.$namesWaitingCtr.' names waiting',
								'type' => $history::TYPE_ADDED,
							));

							$history->save(false);
							
							//add to evaluation queue
							$customerQueue = CustomerQueueViewer::model()->find(array(
								'condition' => 'customer_id = :customer_id AND skill_id = :skill_id',
								'params' => array(
									':customer_id' => $model->customer_id,
									':skill_id' => $model->skill_id,
								),
							));
							
							if( $customerQueue )
							{
								$customerQueue->dials_until_reset = 0;
								$customerQueue->save(false);
							}
							
							
							$result['status'] = 'success';
							$result['message'] = 'List "'.$model->name.'" for customer "'.$model->customer->getFullName().'" import completed successfully. '.$leadsImported . ' leads imported';
							// $result['message'] = 'Database has been updated.';
							
						}
						else
						{
							$result['status'] = 'error';
							$result['message'] = 'Invalid Template of List "'.$model->name.'" for customer "'.$model->customer->getFullName().'"';
						}
					}
					
				}
				
				$transaction->commit();
				
				$listsCronProcessQueue->result_data = json_encode($result);
				$listsCronProcessQueue->on_going = 0;
				$listsCronProcessQueue->date_completed = date("Y-m-d H:i:s");
				$listsCronProcessQueue->save(false);
			}
			catch(Exception $e)
			{
				print_r($e);
				$transaction->rollback();
			}
			
			echo '<pre>';
			print_r($result);
			echo '</pre>';
		}
	}

	public function _computeForSkillMaxLeadLifeBeforeRecertify($model)
	{
		//recycle - recertify  module
		if(!empty($model->skill->max_lead_life_before_recertify))
		{
			$time = strtotime(date("Y-m-d"));
			$finalDate = date("Y-m-d", strtotime("+".($model->skill->max_lead_life_before_recertify)." day", $time));
			return $finalDate;
		}
		else
			return date("Y-m-d");
	}
	
	public function createImportHistory($existingLead)
	{
		$existingImportHistoryThisMonth = LeadHistory::model()->find(array(
			'condition' => '
				type = 4
				AND lead_id = :lead_id 
				AND DATE(date_created) >= :firstDayOfMonth 
				AND DATE(date_created) <= :lastDayOfMonth
			',
			'params' => array(
				'lead_id' => $existingLead->id,
				':firstDayOfMonth' => date('Y-m-01'),
				':lastDayOfMonth' => date('Y-m-t')
			),
		));
		
		// echo 'existingImportHistoryThisMonth: ' . count($existingImportHistoryThisMonth);
		// echo '<br>';
		// exit;
		
		if( empty($existingImportHistoryThisMonth) )
		{
			$leadHistory = new LeadHistory;
					
			$leadHistory->setAttributes(array(
				'lead_id' => $existingLead->id,
				'agent_account_id' => null,
				'type' => 4,
			));	
			
			$leadHistory->save(false);
		}
	}

	public function multiCurlRequest($data, $options = array()) 
	{
		// array of curl handles
		$curly = array();
		
		// data to be returned
		$result = array();

		// multi handle
		$mh = curl_multi_init();

		// loop through $data and create curl handles
		// then add them to the multi-handle
		foreach ($data as $id => $d) 
		{
			$curly[$id] = curl_init();

			$url = (is_array($d) && !empty($d['url'])) ? $d['url'] : $d;
			curl_setopt($curly[$id], CURLOPT_URL, $url);
			curl_setopt($curly[$id], CURLOPT_HEADER, 0);
			curl_setopt($curly[$id], CURLOPT_RETURNTRANSFER, 1);

			// post?
			if (is_array($d)) 
			{
				if (!empty($d['post'])) 
				{
					curl_setopt($curly[$id], CURLOPT_POST, 1);
					curl_setopt($curly[$id], CURLOPT_POSTFIELDS, $d['post']);
				}
			}

			// extra options?
			if (!empty($options)) 
			{
				curl_setopt_array($curly[$id], $options);
			}

			curl_multi_add_handle($mh, $curly[$id]);
		}

		// execute the handles
		$running = null;
		do { 
			curl_multi_exec($mh, $running); 
		} while($running > 0);

		// get content and remove handles
		foreach($curly as $id => $c) 
		{
			$result[$id] = curl_multi_getcontent($c);
			curl_multi_remove_handle($mh, $c);
		}

		// all done
		curl_multi_close($mh);
	 
		return $result;
	}

	private function excelColumnRange($lower, $upper) 
	{
		++$upper;
		
		for ($i = $lower; $i !== $upper; ++$i) 
		{
			yield $i;
		}
	}
}

?>