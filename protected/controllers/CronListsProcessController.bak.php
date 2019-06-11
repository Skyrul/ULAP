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
		
		$criteria = new CDbCriteria;
		$criteria->compare('on_going',1);
		$criteria->compare('date_completed', '0000-00-00');
		
		$listsCronProcessOngoing = ListsCronProcess::model()->find($criteria);
		
		$fileExists = file_exists('leads/' . $listsCronProcessQueue->fileupload->generated_filename);
			
		if( !$fileExists )
		{
			$fileExists =  file_exists('fileupload/' . $listsCronProcessQueue->fileupload->generated_filename);
		}

		echo 'filename: ' . $listsCronProcessQueue->fileupload->generated_filename;
		
		echo '<br><br>';
		
		echo $fileExists == 1 ? 'file exists' : 'file not found';
		 
		echo '<br><br>';
		// exit;
		
		echo '<pre>';
			echo 'listsCronProcessQueue: <br>';
			print_r($listsCronProcessQueue->attributes);
			
			echo '<br />';
			
			echo 'listsCronProcessOngoing: <br>';
			print_r($listsCronProcessOngoing->attributes);
		// exit;
		
		if(!empty($listsCronProcessOngoing))
		{
			echo 'There is still ongoing upload';
			echo '<br>';
			echo '<pre>';
			print_r($listsCronProcessOngoing->attributes);
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
			
			$customerCalendar = Calendar::model()->find(array(
				'condition' => 'customer_id = :customer_id',
				'params' => array(
					':customer_id' => $customer_id,
				),
			));
			
			$customerSkill = CustomerSkill::model()->find(array(
				'condition' => 'customer_id = :customer_id AND status=1',
				'params' => array(
					':customer_id' => $customer_id,
				),
			));
			
			$contract = $customerSkill->contract;
			
			$leadsWaiting = Lead::model()->findAll(array(
				'together' => true,
				'condition' => 't.customer_id = :customer_id AND t.list_id IS NULL AND t.type=1 AND t.status !=4',
				'params' => array(
					':customer_id' => $customer_id,
				),
			));
			
			
			$transaction = Yii::app()->db->beginTransaction();
			
			try
			{
				##### START OF IMPORTING PROCESS #######
				
				$leadsImported = 0;
				$duplicateLeadsCtr = 0;
				$badLeadsCtr = 0;
				$existingLeadUpdatedCtr = 0;
				
				$importLimit = 0;
				$contractedLeads = 0;
				
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
						}						
					}	
					
					$importLimit = $contractedLeads * 10;

					if( $importLimit > 0 )
					{
						$existingCustomerLeadImportLog = CustomerLeadImportLog::model()->find(array(
							'condition' => 'customer_id = :customer_id AND month = :month AND year = :year',
							'params' => array(
								':customer_id' => $customer_id,
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
					
				//import from leads waiting
				if(!empty($listsCronProcessQueue->import_from_leads_waiting))
				{ 
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

					$highestRow         = $worksheet->getHighestRow(); // e.g. 10
					$highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
					$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
					$nrColumns = ord($highestColumn) - 64;
					
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
						
						for ($row = 2; $row <= $highestRow; ++$row) 
						{	
							$customerLeadImportLog = CustomerLeadImportLog::model()->find(array(
								'condition' => 'customer_id = :customer_id AND month = :month AND year = :year',
								'params' => array(
									':customer_id' => $customer_id,
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
							}
							else
							{
								$last_name = '';
								$first_name = '';
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
									}
								}
							}

							$type = 1;
							
							if( strlen($office_phone_number) < 10 && strlen($mobile_phone_number) < 10 && strlen($home_phone_number) < 10 )
							{
								$type = 2;
							}
							
							if( $type == 1 )
							{
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
									if( $model->duplicate_action == $model::DUPLICATES_UPDATE_LEAD_INFO || $model->duplicate_action == $model::DUPLICATES_UPDATE_LEAD_INFO_RESET_DIALS )
									{
										$existingLead->setAttributes(array(
											'list_id' => $model->id,
											'last_name' => $last_name,
											'first_name' => $first_name,
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
										));
										
										if( $model->duplicate_action == $model::DUPLICATES_UPDATE_LEAD_INFO_RESET_DIALS )
										{
											$existingLead->number_of_dials = 0;
											$existingLead->status = 1;
										}
										
										if( $existingLead->save(false) )
										{
											if( $type == 1 )
											{
												$existingLeadUpdatedCtr++;
											}
											else
											{
												$badLeadsCtr++;
											}
										}
									}
									elseif( $model->duplicate_action == $model::MOVE_LEAD_TO_CURRENT_LIST_RESET_DIALS )
									{
										$existingLead->list_id = $model->id;
										$existingLead->number_of_dials = 0;
										$existingLead->status = 1;
										
										if( $existingLead->save(false) )
										{
											if( $type == 1 )
											{
												$existingLeadUpdatedCtr++;
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
								
								if( $customerLeadImportLog && $customerLeadImportLog->leads_imported > $importLimit )
								{
									$lead->list_id = null;
								}

								$lead->setAttributes(array(
									'customer_id' => $model->customer_id,
									'last_name' => $last_name,
									'first_name' => $first_name,
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
								));
								
								//recycle - recertify  module
								$lead->recertify_date = $this->_computeForSkillMaxLeadLifeBeforeRecertify($model);	
								
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
										
										
										$leadsImported++;
										
										if( $customerLeadImportLog )
										{
											$customerLeadImportLog->leads_imported = $customerLeadImportLog->leads_imported + 1;
											$customerLeadImportLog->save(false);
										}
									}
									else
									{
										$badLeadsCtr++;
									}
									
									if( $highestColumn != 'J' )
									{
										foreach ( range('K', $highestColumn) as $columnLetter ) 
										{
											$customFieldName = $worksheet->getCell($columnLetter.'1')->getValue();
											$customFieldValue = $worksheet->getCell($columnLetter.$row)->getValue();
											
											$customData = new LeadCustomData;
											
											$customData->setAttributes(array(
												'list_id' => $model->id,
												'lead_id' => $lead->id,
												'name' => $customFieldName,
												'value' => $customFieldValue,
											));
											
											$customData->save(false);
										}
									}
								}
							}	
						}
					
						$history = new CustomerHistory;
						
						$history->setAttributes(array(
							'model_id' => $model->id, 
							'customer_id' => $model->customer_id,
							'user_account_id' => Yii::app()->user->account->id,
							'page_name' => 'Leads',
							'content' => $model->name.' | '.($highestRow-1).' leads in list | ' . $leadsImported . ' leads imported | '.$existingLeadUpdatedCtr.' existing leads updated | '.$duplicateLeadsCtr.' duplicates | '.$badLeadsCtr.' bad leads',
							'type' => $history::TYPE_ADDED,
						));

						$history->save(false);
						
						
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
}

?>