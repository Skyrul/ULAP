<?php 

ini_set('memory_limit', '10000M');
set_time_limit(0);

class ListsController extends Controller
{
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
		$model->setScenario("hostDial");
		if( $existingImportSettings )
		{
			$model->attributes = $existingImportSettings->attributes;
		}
		else
		{
			$model->duplicate_action = $model::MOVE_RECYCLABLE_LEAD_TO_CURRENT_LIST;
		}
		
		$setFlashMessage = '';
		
		
		// $customerCalendar = Calendar::model()->find(array(
			// 'condition' => 'customer_id = :customer_id',
			// 'params' => array(
				// ':customer_id' => $customer_id,
			// ),
		// ));
		
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
					
		// if( empty($customerCalendar) )
		// {
			// $setFlashMessage .= '<li>Please create atleast <b>1 calendar</b> in order to create a list - '.CHtml::link('Click to create a calendar', array('/hostDial/calendar', 'customer_id'=>$customer_id)).'.</li>';
		// }
		
		if( empty($customerSkill) )
		{
			// $setFlashMessage .= '<li>Please add atleast <b>1 skill</b> in order to create a list - '.CHtml::link('Click to add a skill', array('/hostDial/customerSkill', 'customer_id'=>$customer_id)).'.</li>';
			$setFlashMessage .= '<li>Please add atleast <b>1 skill</b> in order to create a list</li>';
		}
		
		if( $setFlashMessage != '' )
		{
			Yii::app()->user->setFlash('danger', '<ul>'.$setFlashMessage.'</ul>');
		}
		
		if( isset($_POST['Lists']) )
		{
			$model->attributes = $_POST['Lists'];
			$model->customer_id = $customer_id;
			
			
			$model->start_date = date("Y-m-d",strtotime($_POST['Lists']['start_date']));
			$model->end_date = date("Y-m-d",strtotime($_POST['Lists']['end_date']));
			
			if ( $model->start_date == '1969-12-31' || $model->start_date == '0000-00-00' )
			{
				$model->start_date = '';
			}
			
			if ( $model->end_date == '1969-12-31' || $model->end_date == '0000-00-00' )
			{
				$model->end_date = '';
			}
			
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

			// Testing List creation code only, can delete anytime.
			
			/* if($model->save(false))
			{
				if(!empty( $_POST['fileUploadId']) || !empty($_POST['import_from_leads_waiting']) )
				{
					$lcp->list_id = $model->id;
					$lcp->save(false);
					
					Yii::app()->user->setFlash('success', '<b>'.$model->name.'</b> was created successfully. You can proceed to other tasks now once file processing is complete you will receive a notification');
				}
				else
				{
					Yii::app()->user->setFlash('success', 'List <b>'.$model->name.'</b> was created successfully.');
				}
				
				$this->redirect(array('leads/index', 'id'=>$model->id, 'customer_id'=>$customer_id));
			} */
			
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
				
				if(!$validTemplate)
				{
					Yii::app()->user->setFlash('danger', 'Invalid Template');
				}
				$this->redirect(array('leads/index', 'list_id'=>$model->id, 'customer_id'=>$customer_id));
			}
		}
		
		$this->renderPartial('create', array(
			'model' => $model,
			'customer_id' => $customer_id,
			'leadsWaiting' => $leadsWaiting,
			'contract' => $contract,
		));
	}
	
	public function actionAjaxUpdateForm($id=null, $customer_id=null)
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
		
		if ($model->start_date == '0000-00-00' )
		{
			$model->start_date = '';
		}
		else
		{
			$model->start_date = date("m/d/Y",strtotime($model->start_date));
		}
		
		if ($model->end_date == '0000-00-00' )
		{
			$model->end_date = '';
		}
		else
		{
			$model->end_date = date("m/d/Y",strtotime($model->end_date));
		}
		
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
			$model->start_date = date("Y-m-d",strtotime($_POST['Lists']['start_date']));
			$model->end_date = date("Y-m-d",strtotime($_POST['Lists']['end_date']));
			
			if ( $model->start_date == '1969-12-31' || $model->start_date == '0000-00-00' )
			{
				$model->start_date = '';
			}
			
			if ( $model->end_date == '1969-12-31' || $model->end_date == '0000-00-00' )
			{
				$model->end_date = '';
			}
			
			$lcp = new ListsCronProcess;
			$lcp->account_id = Yii::app()->user->account->id;
			$lcp->allow_custom_fields = $model->allow_custom_fields;
			
			if( isset($_POST['import_from_leads_waiting']) )
			{
				$lcp->import_from_leads_waiting = $_POST['import_from_leads_waiting'];
			}
			
			
			if($model->save(false))
			{
				$message = '';
				if(!empty($_POST['import_from_leads_waiting']) )
				{
					$lcp->list_id = $model->id;
					$lcp->save(false);
					
					$message = '<b>'.$model->name.'</b> has been updated. You can proceed to other tasks now once file processing is complete you will receive a notification';

				}
				else
				{
					$message = '<b>'.$model->name.'</b> has been updated.';
					
				}
				
				Yii::app()->user->setFlash('success', $message);
				$response = array(
					'success' => true,
					'message' => 'List '.$model->name.' update successful!',
					'scenario' => 'update',
				);
				
				
			}
			else
			{
				// print_r($model->getErrors());
				$response = array(
					'success' => false,
					'message' => 'List '.$model->name.' update error!',
					'scenario' => 'update',
				);
			}
			
			
			echo CJSON::encode($response);
			Yii::app()->end();
		}
		
		Yii::app()->clientscript->scriptMap['jquery.min.js'] = false;
		$this->renderPartial('_ajax_form_update',array(
			'model' => $model,
			'customer_id' => $customer_id,
			'actionController' => Yii::app()->createUrl('/hostDial/lists/AjaxUpdateForm',array('id' => $id, 'customer_id' => $customer_id)),
		),false,true);
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
			'N' => 'Gender',
			'O' => 'Member Number',
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

	public function actionAjaxAgentAssignment($id=null, $customer_id=null)
	{
		$result = array(
			'status' => 'error',
			'message' => 'Database error.',
			'html' => '',
		);
		
		$id = isset($_POST['id']) ? $_POST['id'] : $id;
		$customer_id = isset($_POST['customer_id']) ? $_POST['customer_id'] : $customer_id;
		
		$model = Lists::model()->findByPk($id);
		
		$listAssignedAgents = ListsAgentAssigned::model()->findAll(array(
			'condition' => 'lists_id = :lists_id',
			'params' => array(
				':lists_id' => $id
			),
		));
		
		$availableAgents = array();
		
		$hostDialers = CustomerOfficeStaff::model()->findAll(array(
			'with' => 'account',
			'condition' => '
				t.customer_id = :customer_id 
				AND t.is_deleted=0
				AND t.account_id IS NOT NULL
				AND account.account_type_id = :account_type_id
			',
			'params' => array(
				':customer_id' => $customer_id,
				':account_type_id' => Account::TYPE_HOSTDIAL_AGENT,
			),
		));
		
		if( $hostDialers )
		{
			foreach( $hostDialers as $hostDialer )
			{
				$availableAgents[$hostDialer->account_id] = $hostDialer->staff_name; 
			}
		}
		
		Yii::app()->clientscript->scriptMap['jquery.min.js'] = false;
		
		$this->renderPartial('ajax_agent_assignment',array(
			'id' => $id,
			'model' => $model,
			'customer_id' => $customer_id,
			'availableAgents' => $availableAgents,
			'listAssignedAgents' => $listAssignedAgents,
		),false,true);
	}
	
	public function actionUpdateListAgentAssigned()
	{
		$result = array(
			'status' => '',
			'message' => '',
		);
		
		if( isset($_POST['ajax']) )
		{
			if( $_POST['type'] == 'add' )
			{
				$model = new ListsAgentAssigned;
				
				$model->setAttributes(array(
					'account_id' => $_POST['item_id'],
					'lists_id' => $_POST['lists_id'],
				));
				
				if( $model->save(false) )
				{
					$accountSkill = new AccountSkillAssigned;
					$accountSkill->setAttributes(array(
						'account_id' => $_POST['item_id'],
						'skill_id' => $_POST['skill_id']
					));
					$accountSkill->save(false);
					
					$result['status'] = 'success';
				}
			}
			
			if( $_POST['type'] == 'remove' )
			{
				$model = ListsAgentAssigned::model()->find(array(
					'condition' => 'account_id = :account_id AND lists_id = :lists_id',
					'params' => array(
						':account_id' => $_POST['item_id'],
						':lists_id' => $_POST['lists_id'],
					),
				));
				
				if( $model )
				{
					if( $model->delete() )
					{
						$accountSkill = AccountSkillAssigned::model()->find(array(
							'condition' => 'account_id = :account_id AND skill_id = :skill_id',
							'params' => array(
								':account_id' => $_POST['item_id'],
								':skill_id' => $_POST['skill_id']
							),
						));
						$accountSkill->delete();
						
						$result['status'] = 'success';
					}
				}
			}
		}
		
		echo json_encode($result);
	}
	
	public function actionUploadScript123($id = null, $customer_id = null)
	{
		$id = isset($_POST['id']) ? $_POST['id'] : $id;
		$customer_id = isset($_POST['customer_id']) ? $_POST['customer_id'] : $customer_id;
		
		$model = Lists::model()->findByPk($id);
		
		$criteria = new CDbCriteria;
		$criteria->compare('skill_id', $model->skill_id);
		$criteria->compare('customer_id', $customer_id);
		
		$customerSkill = CustomerSkill::model()->find($criteria);
		
		if($customerSkill === null)
		{
			throw new CHttpException(404,'The requested page does not exist.');
		}
		else
		{
			if($model->customer_id != $customerSkill->customer_id)
				throw new CHttpException(404,'The requested page does not exist.');
		}
		
		if( isset($_POST['CustomerSkill']) )
		{
			$customerSkill = $_POST['CustomerSkill'];
			
			var_dump($_FILES); exit;
			if( $customerSkill )
			{
				$uploadedFile = CUploadedFile::getInstance($customerSkill,'fileUpload');
				
				if( $uploadedFile )
				{
					if( $uploadedFile->type == 'application/pdf' )
					{
						$originalFileName = $uploadedFile->name;

						$rnd = rand(0,9999).strtotime(date("Y-m-d H:i:s")); 
						$fileName = $rnd.'-'.$originalFileName;
						
						$targetDir = 'fileupload' . DIRECTORY_SEPARATOR . $fileName;
						
						$uploadedFile->saveAs($targetDir);
						
						
						$fileupload = new Fileupload;
						
						$fileupload->setAttributes(array(
							'original_filename' => $originalFileName,
							'generated_filename' => $fileName,
						));
						
						if( $fileupload->save(false) )
						{
							$model->script_tab_fileupload_id = $fileupload->id;
							
							if( $model->save(false) )
							{
								$status = 'success';
								$message = 'Script file was saved successfully.';
							}
							else
							{
								$status = 'error';
								$message = 'File upload error.';
							}
						}
						else
						{
							$status = 'error';
							$message = 'File upload error.';
						}
					}
					else
					{
						$status = 'error';
						$message = 'Please attach a pdf file.';
					}
					
					Yii::app()->user->setFlash($status, $message);
				}
				else
				{
					echo '123aaa1'; exit;
				}
			}
			
			$response = array(
					'success' => $status,
					'message' => $message,
					'scenario' => 'upload',
			);
				
			echo CJSON::encode($response);
			Yii::app()->end();
		}
		
		Yii::app()->clientscript->scriptMap['jquery.min.js'] = false;
		$this->renderPartial('_ajax_script_upload',array(
			'model' => $model,
			'customerSkill' => $customerSkill,
			'actionController' => Yii::app()->createUrl('/hostDial/lists/UploadScript',array('id' => $id, 'customer_id' => $customer_id)),
		),false,true);
		
	}
	
	public function actionUploadScript($id = null, $customer_id = null)
	{
		$authAccount = Yii::app()->user->account;
		
		$id = isset($_POST['id']) ? $_POST['id'] : $id;
		$customer_id = isset($_POST['customer_id']) ? $_POST['customer_id'] : $customer_id;
		
		$model = Lists::model()->findByPk($id);
		
		$criteria = new CDbCriteria;
		$criteria->compare('skill_id', $model->skill_id);
		$criteria->compare('customer_id', $customer_id);
		
		$customerSkill = CustomerSkill::model()->find($criteria);
		
		if($customerSkill === null)
		{
			throw new CHttpException(404,'The requested page does not exist.');
		}
		else
		{
			if($model->customer_id != $customerSkill->customer_id)
				throw new CHttpException(404,'The requested page does not exist.');
		}
		
		if (!empty($_FILES)) 
		{
			
			$originalFileName = $uploadedFile->name;

			$rnd = rand(0,9999).strtotime(date("Y-m-d H:i:s")); 
			$fileName = $rnd.'-'.$_FILES['file']['name'];
						
			$tempFile = $_FILES['file']['tmp_name'];         

			$targetFile =  'fileupload/' . $fileName;

			if( move_uploaded_file($tempFile, $targetFile) )
			{
				
				
				$fileUpload = new Fileupload;
				
				
				$fileUpload->original_filename = $_FILES['file']['name'];
				$fileUpload->generated_filename = $fileName;
				
				
				
				if( $fileUpload->save(false) )
				{
					$customerSkill->script_tab_fileupload_id = $fileUpload->id;
					$customerSkill->save(false);
				}
			}
			
			Yii::app()->end();
		}
		
		Yii::app()->clientscript->scriptMap['jquery.min.js'] = false;
		
		$this->renderPartial('_ajax_script_upload',array(
			'model' => $model,
			'customerSkill' => $customerSkill,
			'actionController' => Yii::app()->createUrl('/hostDial/lists/UploadScript',array('id' => $id, 'customer_id' => $customer_id)),
		),false,true);
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
			'enable_list_custom_mapping' => 0,
			'enable_specific_date_calling' => 0,
		);
		
		if( isset($_POST['ajax']) && isset($_POST['skill_id']) )
		{
			$skill = Skill::model()->findByPk($_POST['skill_id']);
			
			if( $skill )
		 	{
				$result['enable_list_custom_mapping'] = $skill->enable_list_custom_mapping;
			
				$result['enable_specific_date_calling'] = $skill->enable_specific_date_calling;
				
				$result['status'] = 'success';
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

	public function actionDownload($id = null)
	{
		if ($id == null)
		{
			throw new CHttpException(404,'The requested page does not exist.');
		}
		
		$fileUpload = Fileupload::model()->findByPk($id);
		
		if( $fileUpload )
		{
			$file = $fileUpload->generated_filename;
			
			$extension = strtolower(substr(strrchr($file,"."),1));
			
			$explodedFile = explode('/',$file);
			
			if(isset($explodedFile[1]) && $explodedFile[1] == 'pdfs')
				$filePath = Yii::getPathOfAlias('webroot') . $file;
			else
				$filePath = Yii::getPathOfAlias('webroot') . '/fileupload/' . $file;
			
			$customerFileDownloadName = null;
			$allowDownload = false;
			
			if(file_exists($filePath))
			{
				$allowDownload = true;
			}
			
			if ( $allowDownload )
			{
				// required for IE
				if(ini_get('zlib.output_compression')) { ini_set('zlib.output_compression', 'Off');	}
				
				$ctype="application/force-download";
				
				header("Pragma: public"); 
				header("Expires: 0");
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Cache-Control: private",false); // required for certain browsers
				header("Content-Type: $ctype");

				# change, added quotes to allow spaces in filenames, 
				
				if($customerFileDownloadName !== null)
					header("Content-Disposition: attachment; filename=\"".basename($customerFileDownloadName)."\";" );
				else
					header("Content-Disposition: attachment; filename=\"".basename($filePath)."\";" );
				
				header("Content-Transfer-Encoding: binary");
				header("Content-Length: ".filesize($filePath));

				readfile("$filePath");
			} 
			else
			{
				// Do processing for invalid/non existing files here
				echo 'File not found.';
			}
		}
		else
		{
			echo 'File not found.';
		}
	}
	
	public function actionGetSurveyBySkill($skill_id, $customer_id)
	{
		$skill = Skill::model()->findByPk($skill_id);
		
		if($skill === null)
			throw new CHttpException('403', 'Page not found');
		
		
		if($skill->enable_survey_tab != 1)
		{
			$surveys = array();
		}
		
		$surveyList = array();
		$surveys = array();
		
		if( $skill_id != null && $customer_id != null)
		{
			$criteria = new CDbCriteria;
			 
			 $criteria->with = array('surveySkills');
			 $criteria->compare('surveySkills.skill_id', $skill_id);
			 $criteria->compare('surveySkills.is_active', 1);
			 
			 
			$surveys = Survey::model()->active()->findAll($criteria);
		}
		else if( $customer_id != null )
		{
			$criteria = new CDbCriteria;
			 
			 $criteria->with = array('surveyCustomers');
			 $criteria->compare('surveyCustomers.customer_id', $customer_id);
			 $criteria->compare('surveyCustomers.is_active', 1);
			 
			 
			$surveys = Survey::model()->active()->findAll($criteria);
		}
		else
		{
			$surveys = Survey::model()->active()->findAll();
		}
		
		
		if($skill->enable_survey_tab == 1)
		{
			foreach($surveys as $survey)
			{
				$surveyList[$survey->id]['id'] = $survey->id;
				$surveyList[$survey->id]['survey_name'] = $survey->survey_name;
			}
		}
		
		echo CJSON::ENCODE($surveyList);
		
		Yii::app()->end();
	}
}

?>