<?php 

class ManualEntryController extends Controller
{
	
	public function actionIndex()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
			'lead_id' => '',
		);

		$authAccount = Yii::app()->user->account;
		
		$customer_id = $_REQUEST['customer_id'];
				
		$list = Lists::model()->find(array(
			'condition' => 't.from_manual_entry = 1 AND t.manual_entry_is_submitted = 0',
			'order' => 't.date_created DESC',
		));
		
		if( !$list )
		{
			$list = new Lists;
			$list->name = 'Manual Entry ' . date('mdY');
			
			$existingImportSettings = CustomerListImportSettings::model()->find(array(
				'condition' => 'customer_id = :customer_id',
				'params' => array(
					':customer_id' => $customer_id
				),
			));
			
			if( $existingImportSettings )
			{
				$list->attributes = $existingImportSettings->attributes;
			}
			else
			{
				$list->duplicate_action = $model::MOVE_RECYCLABLE_LEAD_TO_CURRENT_LIST;
			}
		}
		
		if( isset($_POST['ajax']) )
		{
			if( isset($_POST['autosave']) )
			{
				$errors = array();
				
				if( empty($_POST['home_phone_number']) && empty($_POST['mobile_phone_number']) )
				{
					$errors['home_phone_number'] = 'Enter atleast 1 phone number.';
					$errors['mobile_phone_number'] = 'Enter atleast 1 phone number.';
				}
				else
				{
					if( !empty($_POST['home_phone_number']) && strlen($_POST['home_phone_number']) < 10 )
					{
						$errors['home_phone_number'] = 'Phone number must be 10 digits.';
					}
					
					if( !empty($_POST['mobile_phone_number']) && strlen($_POST['mobile_phone_number']) < 10 )
					{
						$errors['mobile_phone_number'] = 'Phone number must be 10 digits.';
					}
				}
				
				if( empty($errors) )
				{
					$model = new LeadManualEntry;
					
					if( !empty($_POST['lead_id']) )
					{
						$existingModel = LeadManualEntry::model()->findByPk($_POST['lead_id']);
						
						if( $existingModel )
						{
							$model = $existingModel;
						}
					}			
					
					$model->customer_id = $_POST['customer_id'];
					$model->list_id = $_POST['list_id'];
					$model->first_name = $_POST['first_name'];
					$model->last_name = $_POST['last_name'];
					$model->mobile_phone_number = $_POST['mobile_phone_number'];
					$model->home_phone_number = $_POST['home_phone_number'];
					
					if( $model->save(false) )
					{
						$result['status'] = 'success';
						$result['lead_id'] = $model->id;
					}
				}
				else
				{
					$result['errors'] = $errors;
				}
			}
			
			if( isset($_POST['formSubmit']) )
			{
				$tempLeads = LeadManualEntry::model()->findAll(array(
					'condition' => 'list_id = :list_id',
					'params' => array(
						':list_id' => $_POST['list_id']
					)
				));
				
				if( $tempLeads )
				{
					$officeCount = CustomerOffice::model()->count(array(
						'condition' => 'customer_id = :customer_id AND status=1 AND is_deleted=0',
						'params' => array(
							':customer_id' => $_POST['customer_id']
						),
					));
					
					$calendarCount = Calendar::model()->count(array(
						'condition' => 'customer_id = :customer_id AND status=1',
						'params' => array(
							':customer_id' => $_POST['customer_id']
						),
					));
					
					if( $officeCount > 1 || $calendarCount > 1 )
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
							'A' => 'Last Name',
							'B' => 'First Name',
							'C' => 'Partner First Name',
							'D' => 'Partner Last Name',
							'E' => 'Address 1',
							'F' => 'Address 2',
							'G' => 'City',
							'H' => 'State',
							'I' => 'Zip',
							'J' => 'Office Phone',
							'K' => 'Mobile Phone',
							'L' => 'Home Phone',
							'M' => 'Email Address',
							'N' => 'Gender'
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
						
						if( $tempLeads )
						{
							foreach($tempLeads as $teampLead)
							{
								$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $teampLead->last_name );
								$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $teampLead->first_name );
								$objPHPExcel->getActiveSheet()->SetCellValue('K'.$ctr, $teampLead->mobile_phone_number );
								$objPHPExcel->getActiveSheet()->SetCellValue('L'.$ctr, $teampLead->home_phone_number );
								
								$ctr++;
							}
							
							$objWriter = new PHPExcel_Writer_CSV($objPHPExcel);
							$objWriter->setDelimiter(',');

							$objWriter->setEnclosure('');

							$objWriter->setLineEnding("\r\n");
							$objWriter->setSheetIndex(0);
							
							$filename = $list->name;
							
							$webroot = Yii::getPathOfAlias('webroot');
							$folder =  $webroot . DIRECTORY_SEPARATOR . 'tempfileupload';
							
							if( $objWriter->save($folder. DIRECTORY_SEPARATOR .$filename.'.csv') )
							{
								// $fileUpload = new Fileupload;
								// $fileUpload->original_filename = $filename;
								// $fileUpload->generated_filename = $filename;
								
								// if( $fileUpload->save(false) )
								// {
									// $customerFile = new CustomerFile;
									
									// $customerFile->setAttributes(array(
										// 'customer_id' => $_POST['customer_id'],
										// 'fileupload_id' => $fileUpload->id,
										// 'user_account_id' => $authAccount->id,
									// ));
									
									// if( $customerFile->save(false) )
									// {
										// $history = new CustomerHistory;
										
										// $history->setAttributes(array(
											// 'model_id' => $customerFile->id, 
											// 'customer_id' => $_POST['customer_id'],
											// 'user_account_id' => $authAccount->id,
											// 'page_name' => 'Customer File',
											// 'type' => $history::TYPE_ADDED,
										// ));

										// $history->save(false);
									// }	
								// }	

								// $list->manual_entry_is_submitted = 1;
								// $list->save(false);
								
								$result['status'] = 'success';
							}	
						}
					}
				}
			}
			
			echo json_encode($result);
			Yii::app()->end();
		}
		else
		{
			$list->save();
		}
		
		$this->render('index', array(
			'customer_id' => $_REQUEST['customer_id'],
			'list' => $list
		));
	}
	
	private function actionAjaxScrubLead($model, $tempLead)
	{
		$type = 2;
		$uniqueAreaCodes = array();
		
		$first_name = $tempLead->first_name;
		$first_name = $tempLead->first_name;
		$home_phone_number = $tempLead->home_phone_number;
		$mobile_phone_number = $tempLead->mobile_phone_number;
		
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
					if( $customerLeadImportLog && $customerLeadImportLog->leads_imported < $importLimit )
					{
						if( 
							$existingLead->status == 1 
							&& ($existingLead->recertify_date == "0000-00-00" || $existingLead->recertify_date == null || time() >= strtotime($existingLead->recertify_date)) 
						)
						{
							$existingLead->list_id = $model->id;

							if( $existingLead->save(false) )
							{
								$existingLeadUpdatedCtr++;
								
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
					if( $customerLeadImportLog && $customerLeadImportLog->leads_imported < $importLimit )
					{
						if( 
							$existingLead->recycle_lead_call_history_id != null 
							&& ($existingLead->status == 3 || $existingLead->number_of_dials >= $model->skill->max_dials) 
							&& ($existingLead->recycle_date == "0000-00-00" || $existingLead->recycle_date == null || time() >= strtotime($existingLead->recycle_date)) 
						)
						{
							$existingLead->list_id = $model->id;
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
								
								$existingLeadUpdatedCtr++;
								
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
			
			if( $customerLeadImportLog && $customerLeadImportLog->leads_imported > $importLimit )
			{
				$lead->list_id = null;
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
					
					if( $customerLeadImportLog->leads_imported <= $importLimit )
					{
						$leadsImported++;
					}
					
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
		
		return true;
	}
}

?>