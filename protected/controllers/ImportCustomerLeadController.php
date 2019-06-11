<?php 

class ImportCustomerLeadController extends Controller
{
	
	public function actionIndex()
	{
		// echo 'Check file before running this import script';
		// exit;
		
		ini_set('memory_limit', '512M');
		set_time_limit(0);
		
		// unregister Yii's autoloader
		spl_autoload_unregister(array('YiiBase', 'autoload'));
	
		$phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
		include($phpExcelPath . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'PHPExcel.php');

		spl_autoload_register(array('YiiBase', 'autoload'));
		 
		
		// $inputFileName = 'csv/ImportCustomerLead/Name-1000.csv';
		// echo $inputFileName = 'csv/ImportCustomerLead/RealLead-8.csv';
		// echo $inputFileName = 'csv/ImportCustomerLead/RealLead-CustomerFound-1.csv';
		// echo $inputFileName = 'csv/ImportCustomerLead/RealLead-CustomerFound-Pether-Mathison.csv';
		// echo $inputFileName = 'csv/ImportCustomerLead/SecondLead/SecondLeads-Test.csv';
		// echo $inputFileName = 'csv/ThreeCustomerLeads.csv';
		// echo $inputFileName = 'csv/ImportThreeCustomer/ThreeCustomerLeads.csv';
		// echo $inputFileName = 'csv/Richard/Names.csv';
		// echo $inputFileName = 'csv/Thomas Keasler/Names.csv';
		echo $inputFileName = 'csv/ImportThreeCustomer/Names-5-11-2016.csv';
		$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
		
		$worksheet = $objPHPExcel->getActiveSheet();

		$highestRow         = $worksheet->getHighestRow(); // e.g. 10
		$highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
		$nrColumns = ord($highestColumn) - 64;
		
		$transaction = Yii::app()->db->beginTransaction();
		
		$customerKeyHolder = array();
		try
		{
			$ctr = 1;
			for ($row = 2; $row <= $highestRow; ++$row) 
			{
				
				$clientPrimaryKey = $worksheet->getCell('A'.$row)->getValue(); //ClientPrimaryKey
				$customerPrimaryKey = $worksheet->getCell('B'.$row)->getValue(); //CustomerPrimaryKey
				$language = $worksheet->getCell('C'.$row)->getValue(); //language
				$firstName = $worksheet->getCell('D'.$row)->getValue(); //1st F
				$lastName = $worksheet->getCell('E'.$row)->getValue(); //1st L
				$secondFirstName = $worksheet->getCell('F'.$row)->getValue(); //2nd F
				$secondLastName = $worksheet->getCell('G'.$row)->getValue(); //2nd L
				$phone = $worksheet->getCell('H'.$row)->getValue(); //Phone
				$address = $worksheet->getCell('I'.$row)->getValue(); //address
				
				$customerKeyHolder[$customerPrimaryKey][$clientPrimaryKey] = array(
					'import_client_primary_key' => $clientPrimaryKey,
					'import_customer_primary_key' => $customerPrimaryKey,
					'language' => $language,
					'first_name' => $firstName,
					'last_name' => $lastName,
					'second_first_name' => $secondFirstName,
					'second_last_name' => $secondLastName,
					'phone' => $phone,
					'address' => $address,
				);
				
				// $transaction->commit();
				// echo 'Success';
			}
			
			
			$customerNotFound = array();
			
			echo '<pre>';
			
			foreach($customerKeyHolder as $customerPk => $customerLeadData)
			{
				echo '<br>';
				echo '--- '.$customerPk.' ---';
				echo '<br>';
				
				$criteria = new CDbCriteria;
				$criteria->compare('import_customer_primary_key', $customerPk);
				$customer = Customer::model()->find($criteria);
				
				if($customer !== null)
				{
					$customer_id = $customer->id;
					
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
					
					
					if($customerCalendar !== null && $customerSkill !== null)
					{
						$criteria = new CDbCriteria;
						$criteria->compare('name', 'System Imported List');
						$criteria->compare('customer_id', $customer_id);
						
						$model = Lists::model()->find($criteria);
						
						if($model === null)
						{
							
							$model = new Lists;
							$model->name = 'System Imported List';
							$model->customer_id = $customer_id;
							$model->skill_id = $customerSkill->skill_id;
							// $model->skill_id = 18;
							$model->calendar_id = $customerCalendar->id;
							$model->status = 1;
							
							if(!$model->save())
							{
								print_r($model->getErrors());
								exit;
							}
							else
							{
								# echo '<br>New List created<br>';
							}
						}
						else
						{
							$model->skill_id = $customerSkill->skill_id;
							$model->calendar_id = $customerCalendar->id;
							$model->save(false);
						}
						
						$leadsImported = 0;
						$duplicateLeadsCtr = 0;
						$badLeadsCtr = 0;
						$existingLeadUpdatedCtr = 0;
						
						$importLimit = 0;
						$contractedLeads = 0;
						
						foreach($customerLeadData as  $clientPrimaryKey => $lead)
						{
							echo '<br>';
							echo '--- Client Primary Key: '.$clientPrimaryKey.'---';
							echo '<br>';
							
							$customerLeadImportLog = CustomerLeadImportLog::model()->find(array(
								'condition' => 'customer_id = :customer_id AND month = :month AND year = :year',
								'params' => array(
									':customer_id' => $customer_id,
									':month' => date('F'),
									':year' => date('Y'),
								),
							));
									
							$last_name = $lead['last_name'];
							$first_name = $lead['first_name'];
							$address1 = $lead['address'];
							$address2 = '';
							$city = '';
							$state = '';
							$zip = '';
							$office_phone_number = $lead['phone'];
							$mobile_phone_number = '';
							$home_phone_number = '';
							$language = $lead['language'];
							
							$type = 1;
							
							if( strlen($office_phone_number) < 10 && strlen($mobile_phone_number) < 10 && strlen($home_phone_number) < 10 )
							{
								$type = 2;
							}
									
							if( $type == 1 )
							{
								$existingLead = Lead::model()->find(array(
									'condition' => 't.customer_id = :customer_id AND ( 
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
								
								// if( $model->duplicate_action !== null )
								// {
									// if( $model->duplicate_action == $model::DUPLICATES_UPDATE_LEAD_INFO || $model->duplicate_action == $model::DUPLICATES_UPDATE_LEAD_INFO_RESET_DIALS )
									// {
										// $existingLead->setAttributes(array(
											// 'list_id' => $model->id,
											// 'last_name' => $last_name,
											// 'first_name' => $first_name,
											// 'address1' => $address1,
											// 'address2' => $address2,
											// 'city' => $city,
											// 'state' => $state,
											// 'zip' => $zip,
											// 'office_phone_number' => $office_phone_number,
											// 'mobile_phone_number' => $mobile_phone_number,
											// 'home_phone_number' => $home_phone_number,
											// 'type' => $type,
											// 'language' => $model->language,
											// 'timezone' => AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $model->calendar->office->phone) ),
										// ));
										
										// if( $model->duplicate_action == $model::DUPLICATES_UPDATE_LEAD_INFO_RESET_DIALS )
										// {
											// $existingLead->number_of_dials = 0;
										// }
										
										// if( $existingLead->save(false) )
										// {
											// if( $type == 1 )
											// {
												// $existingLeadUpdatedCtr++;
											// }
											// else
											// {
												// $badLeadsCtr++;
											// }
										// }
									// }
									// elseif( $model->duplicate_action == $model::MOVE_LEAD_TO_CURRENT_LIST_RESET_DIALS )
									// {
										// $existingLead->list_id = $model->id;
										// $existingLead->number_of_dials = 0;
										
										// if( $existingLead->save(false) )
										// {
											// if( $type == 1 )
											// {
												// $existingLeadUpdatedCtr++;
											// }
											// else
											// {
												// $badLeadsCtr++;
											// }
										// }
									// }
									// else
									// {
										// $duplicateLeadsCtr++;
									// }
								// }
								// else
								// {
									
									$duplicateLeadsCtr++;
								// }
								
								// echo '<br>';
								// echo 'Duplicate Lead: '.$existingLead->id;
								// echo '<br>';
								
								if(empty($existingLead->import_client_primary_key))
								{
									$existingLead->import_client_primary_key = $clientPrimaryKey;
									$existingLead->save(false);
								}
								
								// if($clientPrimaryKey == '86fdd180-cffb-48dc-98c5-79e63913cd3f')
								// {
									// var_dump($lead);
									// print_r($existingLead->attributes);
								// }
								
								echo 'Duplicate Lead - Status:'.$existingLead->status;
								echo '</br>';
								
								echo $existingLead->customer->getFullName();
								echo '</br>';
							}
							else
							{
								$lead = new Lead;
								$lead->list_id = $model->id;
								
								// if( $customerLeadImportLog && $customerLeadImportLog->leads_imported > $importLimit )
								// {
									// $lead->list_id = null;
								// }

								$lead->setAttributes(array(
									'customer_id' => $model->customer_id,
									'last_name' => $last_name,
									'first_name' => $first_name,
									'address' => $address1,
									'address2' => $address2,
									'city' => $city,
									'state' => $state,
									'zip_code' => $zip,
									'office_phone_number' => $office_phone_number,
									'mobile_phone_number' => $mobile_phone_number,
									'home_phone_number' => $home_phone_number,
									'type' => $type,
									'language' => $model->language,
									'timezone' => AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $model->calendar->office->phone) ),
								));
								
								$lead->import_client_primary_key = $clientPrimaryKey;
								$lead->is_imported = 1;
								$lead->status = 1;
								
								//recycle - recertify  module
								$lead->recertify_date = $this->_computeForSkillMaxLeadLifeBeforeRecertify($model);	
								
								if( $lead->save(false) )
								{
									if( $type == 1 )
									{
										$leadHistory = new LeadHistory;
								
										$leadHistory->setAttributes(array(
											'lead_id' => $lead->id,
											'agent_account_id' => null,
											'type' => 4,
										));	
										
										$leadHistory->save(false);
										
										
										$leadsImported++;
										
										if( $customerLeadImportLog )
										{
											$customerLeadImportLog->leads_imported = $customerLeadImportLog->leads_imported + 1;
											$customerLeadImportLog->save(false);
										}
										/*
										### CREATE HISTORY - LEAD COMPLETED ###
										$leadHistoryModel = new LeadHistory;
										$leadHistoryModel->lead_id = $lead->id;
										// $leadHistory->lead_name = $lead->first_name.' '.$lead->last_name;
										$leadHistoryModel->type = 1;
										$leadHistoryModel->disposition = 'Completed Lead';
										$leadHistoryModel->note = 'Completed Lead';
										$leadHistoryModel->is_imported = 1;
							
										
										if( $leadHistoryModel->save(false) )
										{
											
											echo 'Completed Lead History Saved';
											echo '</br>';
										}
										else
										{
											print_r($leadHistoryModel->getErrors());
											exit;
										}
										*/
										
									}
									else
									{
										$badLeadsCtr++;
									}
									
									// if( $highestColumn != 'J' )
									// {
										// foreach ( range('K', $highestColumn) as $columnLetter ) 
										// {
											// $customFieldName = $worksheet->getCell($columnLetter.'1')->getValue();
											// $customFieldValue = $worksheet->getCell($columnLetter.$row)->getValue();
											
											// $customData = new LeadCustomData;
											
											// $customData->setAttributes(array(
												// 'list_id' => $model->id,
												// 'lead_id' => $lead->id,
												// 'name' => $customFieldName,
												// 'value' => $customFieldValue,
											// ));
											
											// $customData->save(false);
										// }
									// }
								}
								else
								{
									print_r($lead->getErrors()); exit;
								}
							}
						}
			
			
						$history = new CustomerHistory;
							
						$history->setAttributes(array(
							'model_id' => $model->id, 
							'customer_id' => $model->customer_id,
							'user_account_id' => null,
							'page_name' => 'Leads',
							'content' => $model->name.' | '.count($customerKeyHolder[$customerPk]).' leads in list | ' . $leadsImported . ' leads imported | '.$existingLeadUpdatedCtr.' existing leads updated | '.$duplicateLeadsCtr.' duplicates | '.$badLeadsCtr.' bad leads',
							'type' => $history::TYPE_ADDED,
						));

						$history->save(false);
						
						echo '<br>';
						echo $customerPk.'-'.$history->content;
						echo '<br>';
						
						
					}
					else 
					{
						$setFlashMessage = '';
						'<br>Customer lack information: '.$customerPk;
						
						echo '<br>';
						if( empty($customerCalendar) )
						{
							$setFlashMessage .= '<li>Please create atleast <b>1 calendar</b> in order to create a list - '.CHtml::link('Click to create a calendar', array('/customer/calendar', 'customer_id'=>$customer_id)).'.</li>';
						}
						
						if( empty($customerSkill) )
						{
							$setFlashMessage .= '<li>Please add atleast <b>1 skill</b> in order to create a list - '.CHtml::link('Click to add a skill', array('/customer/customerSkill', 'customer_id'=>$customer_id)).'.</li>';
						}
						
						echo $setFlashMessage;
						
						$customerNotFound[$customerPk]['CUSTOMER SETTING'] = 'No of Leads: '.count($customerKeyHolder[$customerPk]);
					}
				}
				else
				{
					
					$customerNotFound[$customerPk] = 'No of Leads: '.count($customerKeyHolder[$customerPk]);
				}

			}
			
			echo '<br>';
			echo 'Customer not found:';
			echo '<br>';
			print_r($customerNotFound);
			echo '<br>';
			
			$transaction->commit();
		}
		catch(Exception $e)
		{
			$transaction->rollback();
			print_r($e);
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