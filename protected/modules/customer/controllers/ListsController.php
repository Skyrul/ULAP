<?php 

ini_set('memory_limit', '10000M');
set_time_limit(0);

class ListsController extends Controller
{
	
	public function actionIndex()
	{
		$this->render('index');
	}

	
	public function actionCreate($customer_id)
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
				
				if( $customer && $customer->id != $_REQUEST['customer_id'] )
				{
					$this->redirect(array('create', 'customer_id'=>$customer->id));
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
				
				if( $customerOfficeStaff && $customerOfficeStaff->customer_id != $_REQUEST['customer_id'] )
				{
					$this->redirect(array('create', 'customer_id'=>$customerOfficeStaff->customer_id));
				}
			}
		}
		
		
		$result = array(
			'status' => 'error',
			'message' => 'Database error.',
			'html' => '',
		);
		
		$existingImportSettings = CustomerListImportSettings::model()->find(array(
			'condition' => 'customer_id = :customer_id',
			'params' => array(
				':customer_id' => $customer_id
			),
		));
		
		$model = new Lists;
		
		if( $existingImportSettings )
		{
			$model->attributes = $existingImportSettings->attributes;
		}
		else
		{
			$model->duplicate_action = $model::MOVE_RECYCLABLE_LEAD_TO_CURRENT_LIST;
		}
		
		$setFlashMessage = '';
		
		
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
					
		if( empty($customerCalendar) )
		{
			$setFlashMessage .= '<li>Please create atleast <b>1 calendar</b> in order to create a list - '.CHtml::link('Click to create a calendar', array('/customer/calendar', 'customer_id'=>$customer_id)).'.</li>';
		}
		
		if( empty($customerSkill) )
		{
			$setFlashMessage .= '<li>Please add atleast <b>1 skill</b> in order to create a list - '.CHtml::link('Click to add a skill', array('/customer/customerSkill', 'customer_id'=>$customer_id)).'.</li>';
		}
		
		if( $setFlashMessage != '' )
		{
			Yii::app()->user->setFlash('danger', '<ul>'.$setFlashMessage.'</ul>');
		}
		
		if( isset($_POST['Lists']) )
		{
			$model->attributes = $_POST['Lists'];
			$model->customer_id = $customer_id;
			
			$lcp = new ListsCronProcess;
			$lcp->account_id = Yii::app()->user->account->id;
			$lcp->allow_custom_fields = $model->allow_custom_fields;
				
			if( isset($_POST['import_from_leads_waiting']) )
			{
				$lcp->import_from_leads_waiting = $_POST['import_from_leads_waiting'];
			}
					
			if( isset( $_POST['fileUploadId']) )
			{
				$model->fileupload_id = $_POST['fileUploadId'];
				$lcp->fileupload_id = $model->fileupload_id;
				
				
				// unregister Yii's autoloader
				spl_autoload_unregister(array('YiiBase', 'autoload'));
			
				$phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
				include($phpExcelPath . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'PHPExcel.php');

				spl_autoload_register(array('YiiBase', 'autoload'));
				 
				
				$inputFileName = 'leads/' . $model->fileupload->generated_filename;
				
				if( !file_exists($inputFileName) )
				{
					$inputFileName = 'fileupload/' . $model->fileupload->generated_filename;
				}

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
							
							$columnsInFile = $originalColumnsInFile;
						}
					}
				}
			}

			if( $validTemplate || (empty( $_POST['fileUploadId']) && !empty($_POST['import_from_leads_waiting'])) )
			{
				if($model->save(false))
				{
					if(!empty( $_POST['fileUploadId']) || !empty($_POST['import_from_leads_waiting']) )
					{
						$lcp->list_id = $model->id;
						$lcp->save(false);
						
						Yii::app()->user->setFlash('success', '<b>'.$model->name.'</b> was created successfully. You can proceed to other tasks now once file processing is complete you will receive a notification');
					}
					else
					{
						Yii::app()->user->setFlash('success', '<b>'.$model->name.'</b> was created successfully.');
					}
					
					$this->redirect(array('leads/index', 'id'=>$model->id, 'customer_id'=>$customer_id));
				}
			}
			else
			{
				Yii::app()->user->setFlash('danger', 'Invalid Template');
				$this->redirect(array('leads/index', 'customer_id'=>$customer_id));
			}
		}
		
		$this->render('create', array(
			'model' => $model,
			'customer_id' => $customer_id,
			'leadsWaiting' => $leadsWaiting,
			'contract' => $contract,
		));
	}
	
	
	public function actionUpdate($id=null, $customer_id=null, $simpleView=false)
	{
		$result = array(
			'status' => 'error',
			'message' => 'Database error.',
			'html' => '',
		);
		
		$id = isset($_POST['id']) ? $_POST['id'] : $id;
		$customer_id = isset($_POST['customer_id']) ? $_POST['customer_id'] : $customer_id;
		
		$model = Lists::model()->findByPk($id);
		$model->duplicate_action = $model::MOVE_RECYCLABLE_LEAD_TO_CURRENT_LIST;
		
		$customerSkill = CustomerSkill::model()->find(array(
			'condition' => 'customer_id = :customer_id AND status=1',
			'params' => array(
				':customer_id' => $customer_id,
			),
		));
		
		$contract = $customerSkill->contract;
		
		$leadsWaiting = Lead::model()->findAll(array(
			'condition' => 'customer_id = :customer_id AND t.list_id IS NULL AND t.type=1 AND t.status=1',
			'params' => array(
				':customer_id' => $customer_id,
			),
		));
		
		
		if( isset($_POST['Lists']) )
		{	
			if( $model->language != $_POST['Lists']['language'] )
			{
				Lead::model()->updateAll(array('language' => $_POST['Lists']['language']), 'list_id = ' . $model->id);
				LeadHopper::model()->updateAll(array('lead_language' => $_POST['Lists']['language']), 'list_id = ' . $model->id);
			}
			
			if( isset($_POST['Lists']['status']) && $_POST['Lists']['status'] != 1 )
			{
				$queuedLeads = LeadHopper::model()->findAll(array(
					'condition' => '
						list_id = :list_id
						AND status="READY"
						AND type=1
					',
					'params' => array(
						':list_id' => $model->id,
					),
				));
				
				if( $queuedLeads )
				{
					foreach( $queuedLeads as $queuedLead )
					{
						$queuedLead->delete();
					}
				}
			}
			
			$model->attributes = $_POST['Lists'];
			
			$lcp = new ListsCronProcess;
			$lcp->account_id = Yii::app()->user->account->id;
			$lcp->allow_custom_fields = $model->allow_custom_fields;
			
			if( isset($_POST['import_from_leads_waiting']) )
			{
				$lcp->import_from_leads_waiting = $_POST['import_from_leads_waiting'];
			}
						
			if( isset( $_POST['fileUploadId']) )
			{
				$model->fileupload_id = $_POST['fileUploadId'];
				$lcp->fileupload_id = $model->fileupload_id;
				
				// unregister Yii's autoloader
				spl_autoload_unregister(array('YiiBase', 'autoload'));
			
				$phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
				include($phpExcelPath . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'PHPExcel.php');

				spl_autoload_register(array('YiiBase', 'autoload'));
				 
				
				$inputFileName = 'leads/' . $model->fileupload->generated_filename;
				
				if( !file_exists($inputFileName) )
				{
					$inputFileName = 'fileupload/' . $model->fileupload->generated_filename;
				}

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
							
							$columnsInFile = $originalColumnsInFile;
						}
					}
				}
				
				if( !$validTemplate )
				{
					Yii::app()->user->setFlash('danger', 'Invalid Template');
					$this->redirect(array('lists/update', 'id'=>$model->id, 'customer_id'=>$customer_id,'simpleView'=>1));
				}
			}
		
			if($model->save(false))
			{
				if(!empty( $_POST['fileUploadId'] ) || !empty($_POST['import_from_leads_waiting']) )
				{
					$lcp->list_id = $model->id;
					$lcp->save(false);
					
					Yii::app()->user->setFlash('success', '<b>'.$model->name.'</b> has been updated. You can proceed to other tasks now once file processing is complete you will receive a notification');
				
				}
				else
				{
					Yii::app()->user->setFlash('success', '<b>'.$model->name.'</b> has been updated.');
					
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
				}

				if( isset($_POST['ajax']) )
				{
					echo json_encode(array(
						'status' => 'success',
						'message' => 'list has been updated.',
					));
				}
				else
				{
					$this->redirect(array('leads/index', 'id'=>$model->id, 'customer_id'=>$customer_id));
				}
				
			}
			
		}
		
		$this->render('update', array(
			'model' => $model,
			'customer_id' => $customer_id,
			'simpleView' => $simpleView,
			'leadsWaiting' => $leadsWaiting,
			'contract' => $contract,
		));
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
	
	public function actionView($id) 
	{
		$html = '';
		
		$result = array(
			'status' => '',
			'message' => '',
			'html' => $html,
		);

		if( isset($_POST['ajax']) )
		{
			$model = Lists::model()->findByPk( $_POST['id'] );
			
			$html = $this->renderPartial('ajax_view', array(
				'model' => $model,
			), true);
			
			$result['status'] = 'success';
			$result['html'] = $html;
		
			echo json_encode($result);
			Yii::app()->end();
		}
		else
		{
			 $this->render('view', array(
				'model' => $model,
			));
		}
	}

	public function actionDelete($id)
	{
		$model = Lists::model()->findByPk($id);
		
		if( $model )
		{
			$model->status = $model::STATUS_DELETED;
			
			if( $model->save(false) ) 
			{
				$history = new CustomerHistory;
				
				$history->setAttributes(array(
					'model_id' => $model->id, 
					'customer_id' => $model->customer_id,
					'user_account_id' => Yii::app()->user->account->id,
					'page_name' => 'Lists',
					'content' => $model->name,
					'type' => $history::TYPE_DELETED,
				));

				$history->save(false);
				
				Lead::model()->updateAll(array('status' => 4), 'list_id = ' . $model->id);
				
				$existingCustomerLeadImportLog = CustomerLeadImportLog::model()->find(array(
					'condition' => 'customer_id = :customer_id AND month = :month AND year = :year',
					'params' => array(
						':customer_id' => $model->customer_id,
						':month' => date('F'),
						':year' => date('Y'),
					),
				));
				
				if( $existingCustomerLeadImportLog )
				{
					$leadCount = Lead::model()->count(array(
						'condition' => 'status=4 AND list_id = ' . $model->id,
					));
					
					$existingCustomerLeadImportLog->leads_imported = $existingCustomerLeadImportLog->leads_imported - $leadCount;
					
					if( $existingCustomerLeadImportLog->leads_imported < 0 )
					{
						$existingCustomerLeadImportLog->leads_imported = 0;
					}
					
					$existingCustomerLeadImportLog->save(false);
				}
				
				Yii::app()->user->setFlash('success', 'List was successfully deleted.');
			}
			else
			{
				Yii::app()->user->setFlash('error', 'Sorry an error has occurred list was not deleted.');
			}
			
			$this->redirect(array('leads/index', 'customer_id'=>$model->customer_id));
		}
	}
	
	public function actionDownloadList($id)
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
			'N' => 'Number of Dials',
			'O' => 'Status',
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
		
		
		$list = Lists::model()->findByPk($id);
		
		$leads = Lead::model()->findAll(array(
			'condition' => 'list_id = :list_id and type=1 AND status !=4',
			'params' => array(
				':list_id' => $list->id,
			),
		));
		

		if( $leads )
		{
			foreach( $leads as $lead )
			{
				$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $lead->last_name);
				$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $lead->first_name);
				$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $lead->partner_first_name);
				$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $lead->partner_last_name);
				$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $lead->address);
				$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $lead->address2);
				$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $lead->city);
				$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $lead->state);
				$objPHPExcel->getActiveSheet()->SetCellValue('I'.$ctr, $lead->zip_code);
				$objPHPExcel->getActiveSheet()->SetCellValue('J'.$ctr, $lead->office_phone_number);
				$objPHPExcel->getActiveSheet()->SetCellValue('K'.$ctr, $lead->mobile_phone_number);
				$objPHPExcel->getActiveSheet()->SetCellValue('L'.$ctr, $lead->home_phone_number);
				$objPHPExcel->getActiveSheet()->SetCellValue('M'.$ctr, $lead->email_address);
				$objPHPExcel->getActiveSheet()->SetCellValue('N'.$ctr, $lead->number_of_dials);
				$objPHPExcel->getActiveSheet()->SetCellValue('O'.$ctr, $lead->getStatus());
				
				
				$ctr++;
			}
		}
		
		
		header('Content-Type: application/vnd.ms-excel'); 
		header('Content-Disposition: attachment; filename="'.$list->name.'.xlsx"'); 
		header('Cache-Control: max-age=0');
		
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
	}
	
	public function actionListDuplicates()
	{
		
	}
	
	public function actionImportSettings($customer_id)
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
				
				if( $customer && $customer->id != $_REQUEST['customer_id'] )
				{
					$this->redirect(array('importSettings', 'customer_id'=>$customer->id));
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
				
				if( $customerOfficeStaff && $customerOfficeStaff->customer_id != $_REQUEST['customer_id'] )
				{
					$this->redirect(array('importSettings', 'customer_id'=>$customerOfficeStaff->customer_id));
				}
			}
		}
		
		$existingModel = CustomerListImportSettings::model()->find(array(
			'condition' => 'customer_id = :customer_id',
			'params' => array(
				':customer_id' => isset($_POST['CustomerListImportSettings']['customer_id']) ? $_POST['CustomerListImportSettings']['customer_id'] : $customer_id
			),
		));
		
		if( $existingModel )
		{
			$model = $existingModel;
		}
		else
		{
			$model = new CustomerListImportSettings;
			$model->customer_id = $customer_id;
		}
		
		if( isset($_POST['CustomerListImportSettings']) )
		{
			$status = 'error';
			$message = 'Database error.';
			
			$model->attributes = $_POST['CustomerListImportSettings'];			
			 
			if( $model->save(false) )
			{
				$status = 'success';
				$message = '<b>Import settings</b> were successfully saved.';
			}
			
			Yii::app()->user->setFlash($status, $message);
			$this->redirect(array('importSettings', 'customer_id'=>$customer_id));
		}

		$this->render('importSettings', array(
			'model' => $model,
			'customer_id' => $customer_id,
		));
	}
	
	public function actionUpload()
	{
		// Settings
		$targetDir = 'leads';

		$cleanupTargetDir = true; // Remove old files
		$maxFileAge = 5 * 3600; // Temp file age in seconds

		// 5 minutes execution time
		@set_time_limit(5 * 60);

		// Get parameters
		$chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
		$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
		// $fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';
		
		 
		$origFileName = $_FILES['FileUpload']['name']['filename'];
		
		// random number + file name
		$rnd = rand(0,9999).strtotime(date("Y-m-d H:i:s"));  // generate random number between 0-9999
		$fileName = "{$rnd}-{$origFileName}";
		
		$filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

		// Create target dir
		if (!file_exists($targetDir))
			@mkdir($targetDir);

		// Remove old temp files	
		if ($cleanupTargetDir && is_dir($targetDir) && ($dir = opendir($targetDir))) {
			while (($file = readdir($dir)) !== false) {
				$tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

				// Remove temp file if it is older than the max age and is not the current file
				if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge) && ($tmpfilePath != "{$filePath}.part")) {
					@unlink($tmpfilePath);
				}
			}

			closedir($dir);
		} else
			die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
		
			

		// Look for the content type header
		if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
			$contentType = $_SERVER["HTTP_CONTENT_TYPE"];

		if (isset($_SERVER["CONTENT_TYPE"]))
			$contentType = $_SERVER["CONTENT_TYPE"];
		
		
		
		
		// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
		if (strpos($contentType, "multipart") !== false) {
			if (isset($_FILES['FileUpload']['tmp_name']['filename']) && is_uploaded_file($_FILES['FileUpload']['tmp_name']['filename'])) {
				// Open temp file
				$out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
				if ($out) {
					// Read binary input stream and append it to temp file
					$in = fopen($_FILES['FileUpload']['tmp_name']['filename'], "rb");

					if ($in) {
						while ($buff = fread($in, 4096))
							fwrite($out, $buff);
					} else
						die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
					fclose($in);
					fclose($out);
					//@unlink($_FILES['file']['tmp_name']);
				} else
					die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
			} else
				die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
		} else {
			// Open temp file
			$out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
			if ($out) {
				// Read binary input stream and append it to temp file
				$in = fopen("php://input", "rb");

				if ($in) {
					while ($buff = fread($in, 4096))
						fwrite($out, $buff);
				} else
					die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');

				fclose($in);
				fclose($out);
			} else
				die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
		}
		
		
		// Check if file has been uploaded
		if (!$chunks || $chunk == $chunks - 1)
		{
			// Strip the temp .part suffix off 
			rename("{$filePath}.part", $filePath);
			
			// $file = CUploadedFile::getInstanceByName('FileUpload[filename]');
			 
			$getFileExtension = explode('.', $fileName);
			
			// $generatedFilename = rand(0,9999).strtotime(date("Y-m-d H:i:s")).'.'.$getFileExtension;

			if(count($getFileExtension > 1))
			{
				$manFileExt = $getFileExtension[count($getFileExtension) - 1];
			}
			
			// Rename file to use generated unique filename
			rename($filePath, $targetDir . DIRECTORY_SEPARATOR . $fileName);
			
			$fileUpload = new Fileupload;
			$fileUpload->original_filename = $origFileName;
			$fileUpload->generated_filename = $fileName;
			
			if( $fileUpload->save(false) )
			{
				die('{"jsonrpc" : "2.0", "generatedFileUploadId": "'.$fileUpload->id.'", "generatedFilename": "' . $fileName . '", "fileExtension": "' . $manFileExt. '"}');
			}
		}
		
		// Return JSON-RPC response
		die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
	}

	public function actionDeleteNamesWaiting($customer_id)
	{
		$models = Lead::model()->findAll(array(
			'together' => true,
			'condition' => 't.customer_id = :customer_id AND t.list_id IS NULL AND t.type=1 AND t.status !=4',
			'params' => array(
				':customer_id' => $customer_id,
			),
		));


		if( $models )
		{
			foreach( $models as $model )
			{
				$model->delete();
			}
			
			Yii::app()->user->setFlash('success', 'Names waiting were deleted successfully.');
		}
		else
		{
			Yii::app()->user->setFlash('danger', 'No names waiting found.');
		}
		
		$this->redirect(array('create', 'customer_id'=>$customer_id));
	}
	
	public function actionDownloadStandardTemplate()
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
		
		header('Content-Type: application/vnd.ms-excel'); 
		header('Content-Disposition: attachment; filename="List Template.xlsx"'); 
		header('Cache-Control: max-age=0');
		
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
	}
	
	public function actionProcessLeadUploads()
	{
		$criteria = new CDbCriteria;
		$criteria->compare('on_going',0);
		$criteria->compare('date_completed', '0000-00-00');
		
		$listsCronProcessQueue = ListsCronProcess::model()->find($criteria);
		
		$criteria = new CDbCriteria;
		$criteria->compare('on_going',1);
		$criteria->compare('date_completed', '0000-00-00');
		
		$listsCronProcessOngoing = ListsCronProcess::model()->find($criteria);
		
		if(!empty($listsCronProcessOngoing))
		{
			echo 'There is still ongoing upload';
			echo '<br>';
			echo '<pre>';
			print_r($listsCronProcessOngoing->attributes);
			echo '</pre>';
		}
		
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
					
				ini_set('memory_limit', '2000M');
				set_time_limit(0);
					
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
			
				//import from fileupload-
				if(!empty($listsCronProcessQueue->fileupload_id))
				{
					// unregister Yii's autoloader
					spl_autoload_unregister(array('YiiBase', 'autoload'));
				
					$phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
					include($phpExcelPath . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'PHPExcel.php');

					spl_autoload_register(array('YiiBase', 'autoload'));
					 
					
					$inputFileName = 'leads/' . $listsCronProcessQueue->fileupload->generated_filename;

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

	public function actionAjaxListCustomerFile($customer_id)
	{
		$customer = Customer::model()->findByPk($customer_id);
		
		$models = CustomerFile::model()->findAll(array(
			'condition' => 'customer_id = :customer_id AND status=1 AND DATE(date_created) >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)',
			'params' => array(
				':customer_id' => $customer->id,
			),
			'order' => 'date_created DESC',
		));
	
		
		$dataProvider=new CArrayDataProvider($models, array(
			// 'pagination'=>array(
	            // 'pageSize'=>10,
	        // ),
		));
		
		Yii::app()->clientscript->scriptMap['jquery.min.js'] = false;
		$this->renderPartial('_ajaxListCustomerFile',array(
			'customer' => $customer,
			'dataProvider' => $dataProvider,
			//'actionController' => Yii::app()->createUrl('/admin/company/ajaxAddDid',array('id' => $company->id)),
		),false, false);
	}

	public function actionCustomMapping($id, $customer_id)
	{
		$list = Lists::model()->findByPk($id);
		
		if( isset($_POST['updateListCustomDatas']) )
		{
			foreach( $_POST['updateListCustomDatas'] as $updateListCustomDataKey => $updateListCustomDataValues )
			{
				$updateListCustomDataModel = ListCustomData::model()->findByPk($updateListCustomDataKey);
				
				if( $updateListCustomDataModel )
				{
					$updateListCustomDataModel->display_on_form = isset($updateListCustomDataValues['display_on_form']) ? 1 : 0;
					$updateListCustomDataModel->allow_edit = isset($updateListCustomDataValues['allow_edit']) ? 1 : 0;
					$updateListCustomDataModel->ordering = $updateListCustomDataValues['ordering'];
					$updateListCustomDataModel->custom_name = $updateListCustomDataValues['custom_name'];
					
					$updateListCustomDataModel->save(false);
				}
			}
		}
		
		if( isset($_POST['newListCustomDatas']) )
		{
			foreach( $_POST['newListCustomDatas'] as $newListCustomDataKey => $newListCustomDataValues )
			{
				$newListCustomDataModel = ListCustomData::model()->findByPk($newListCustomDataKey);
				
				if( $newListCustomDataModel )
				{
					$newListCustomDataModel->display_on_form = isset($newListCustomDataValues['display_on_form']) ? 1 : 0;
					$newListCustomDataModel->allow_edit = isset($newListCustomDataValues['allow_edit']) ? 1 : 0;
					$newListCustomDataModel->ordering = $newListCustomDataValues['ordering'];
					$newListCustomDataModel->custom_name = $newListCustomDataValues['custom_name'];
					$newListCustomDataModel->original_name = $newListCustomDataValues['custom_name'];
					
					$newListCustomDataModel->save(false);
				}
			}
		}
		
		$listCustomDatas = ListCustomData::model()->findAll(array(
			'condition' => 'list_id = :list_id AND status=1',
			'params' => array(
				':list_id' => $id,
			),
			'order' => 'ordering ASC',
		));
		
		$this->render('customMapping', array(
			'list' => $list,
			'customer_id' => $customer_id,
			'listCustomDatas' => $listCustomDatas,
		));
	}

	public function actionAjaxCheckCustomMapping()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
			'remaining_import_text' => '',
			'enable_list_custom_mapping' => 0,
			'enable_specific_date_calling' => 0,
		);
		
		if( isset($_POST['ajax']) && isset($_POST['skill_id']) )
		{
			$skill = Skill::model()->findByPk($_POST['skill_id']);
			
			if( $skill )
		 	{
				$result['status'] = 'success';
				
				$result['enable_list_custom_mapping'] = $skill->enable_list_custom_mapping;
			
				$result['enable_specific_date_calling'] = $skill->enable_specific_date_calling;
				
				
				$customerSkill = CustomerSkill::model()->find(array(
					'condition' => '
						customer_id = :customer_id 
						AND skill_id = :skill_id
						AND status=1
					',
					'params' => array(
						':customer_id' => $_POST['customer_id'],
						':skill_id' => $_POST['skill_id'],
					),
					'order' => 't.id DESC',
				));
				
				if( $customerSkill )
				{
					$contract = $customerSkill->contract;
					
					//if contract fullfillment type is GOAL apply the 10x rule and limit customers monthly import
					if(isset($contract))
					{
						$importLimit = 0;
						
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
									':customer_id' => $_POST['customer_id'],
									':skill_id' => $_POST['skill_id'],
									':month' => date('F'),
									':year' => date('Y'),
								),
							));
							
							$importLimit = $importLimit - $existingCustomerLeadImportLog->leads_imported;
						}
						
						$result['remaining_import_text'] =  'Lead Import Limit <small class="red">('.$importLimit.' Remaining)</small>';
					}
				}
			}
		}
		
		echo json_encode($result);
	}

	public function actionAjaxDeleteCustomField()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		if( isset($_POST['ajax']) && isset($_POST['field_id']) )
		{
			$model = ListCustomData::model()->findByPk($_POST['field_id']);
			
			if( $model )
		 	{
				$model->status = 3;
				
				if( $model->save(false) )
				{
					$result['status'] = 'success';
				}
			}
		}
		
		echo json_encode($result);
	}

}

?>