<?php 

class ListsController extends Controller
{
	
	public function actionIndex()
	{
		$this->render('index');
	}

	
	public function actionCreate($customer_id)
	{
		$result = array(
			'status' => 'error',
			'message' => 'Database error.',
			'html' => '',
		);
		
		$model = new Lists;
		
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
			
			if( isset( $_POST['fileUploadId']) )
			{
				$model->fileupload_id = $_POST['fileUploadId'];
			}
	
			if($model->save(false))
			{
				if( isset( $_POST['fileUploadId']) )
				{
					ini_set('memory_limit', '512M');
					set_time_limit(0);
					
					// unregister Yii's autoloader
					spl_autoload_unregister(array('YiiBase', 'autoload'));
				
					$phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
					include($phpExcelPath . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'PHPExcel.php');

					spl_autoload_register(array('YiiBase', 'autoload'));
					 
					
					$inputFileName = 'leads/' . $model->fileupload->generated_filename;

					$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
					
					$worksheet = $objPHPExcel->getActiveSheet();

					$highestRow         = $worksheet->getHighestRow(); // e.g. 10
					$highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
					$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
					$nrColumns = ord($highestColumn) - 64;
					
					$leadsImported = 0;
					$duplicateLeadsCtr = 0;
					$badLeadsCtr = 0;
					$existingLeadUpdatedCtr = 0;
					
					for ($row = 2; $row <= $highestRow; ++$row) 
					{	
						$last_name = $worksheet->getCell('A'.$row)->getValue();
						$first_name = $worksheet->getCell('B'.$row)->getValue();
						$address1 = $worksheet->getCell('C'.$row)->getValue();
						$address2 = $worksheet->getCell('D'.$row)->getValue();
						$city = $worksheet->getCell('E'.$row)->getValue();
						$state = $worksheet->getCell('F'.$row)->getValue();
						$zip = $worksheet->getCell('G'.$row)->getValue();
						$office_phone_number = preg_replace("/[^0-9]/","", $worksheet->getCell('H'.$row)->getValue());
						$mobile_phone_number = preg_replace("/[^0-9]/","", $worksheet->getCell('I'.$row)->getValue());
						$home_phone_number = preg_replace("/[^0-9]/","", $worksheet->getCell('J'.$row)->getValue());
						
						$type = 1;
						
						if( strlen($office_phone_number) < 10 && strlen($mobile_phone_number) < 10 && strlen($home_phone_number) < 10 )
						{
							$type = 2;
						}
						
						if( $type == 1 )
						{
							$existingLead = Lead::model()->find(array(
								'with' => 'list',
								'condition' => 'list.customer_id = :customer_id AND ( 
									(office_phone_number = :office_phone_number AND office_phone_number != "" AND office_phone_number IS NOT NULL) OR 
									(mobile_phone_number = :mobile_phone_number AND mobile_phone_number != "" AND mobile_phone_number IS NOT NULL) OR
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
							if( $model->duplicate_action !== null )
							{
								if( $model->duplicate_action == $model::DUPLICATES_UPDATE_LEAD_INFO || $model->duplicate_action == $model::DUPLICATES_UPDATE_LEAD_INFO_RESET_DIALS )
								{
									$existingLead->setAttributes(array(
										'list_id' => $model->id,
										'last_name' => $last_name,
										'first_name' => $first_name,
										'address1' => $address1,
										'address2' => $address2,
										'city' => $city,
										'state' => $state,
										'zip' => $zip,
										'office_phone_number' => $office_phone_number,
										'mobile_phone_number' => $mobile_phone_number,
										'home_phone_number' => $home_phone_number,
										'type' => $type,
										'language' => $model->language,
									));
									
									if( $model->duplicate_action == $model::DUPLICATES_UPDATE_LEAD_INFO_RESET_DIALS )
									{
										$existingLead->number_of_dials = 0;
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
							
							$lead->setAttributes(array(
								'list_id' => $model->id,
								'last_name' => $last_name,
								'first_name' => $first_name,
								'address1' => $address1,
								'address2' => $address2,
								'city' => $city,
								'state' => $state,
								'zip' => $zip,
								'office_phone_number' => $office_phone_number,
								'mobile_phone_number' => $mobile_phone_number,
								'home_phone_number' => $home_phone_number,
								'type' => $type,
								'language' => $model->language,
							));
							
							if( $lead->save(false) )
							{
								$leadHistory = new LeadHistory;
								
								$leadHistory->setAttributes(array(
									'lead_id' => $lead->id,
									'agent_account_id' => Yii::app()->user->account->id,
									'type' => 4,								
								));
								
								
								$leadHistory->save(false);
								
								if( $type == 1 )
								{
									$leadsImported++;
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
				}
			
				Yii::app()->user->setFlash('success', '<b>'.$model->name.'</b> was created successfully.');
				
				$this->redirect(array('leads/index', 'id'=>$model->id, 'customer_id'=>$customer_id));
			}
		}
		
		$this->render('create', array(
			'model' => $model,
			'customer_id' => $customer_id,
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
		
		if( isset($_POST['Lists']) )
		{	
			$model->attributes = $_POST['Lists'];
			
			if( isset( $_POST['fileUploadId']) )
			{
				$model->fileupload_id = $_POST['fileUploadId'];
			}
	
			if($model->save(false))
			{
				if( isset( $_POST['fileUploadId']) )
				{
					ini_set('memory_limit', '512M');
					set_time_limit(0);
					
					// unregister Yii's autoloader
					spl_autoload_unregister(array('YiiBase', 'autoload'));
				
					$phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
					include($phpExcelPath . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'PHPExcel.php');

					spl_autoload_register(array('YiiBase', 'autoload'));
					 
					
					$inputFileName = 'leads/' . $model->fileupload->generated_filename;

					$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
					
					$worksheet = $objPHPExcel->getActiveSheet();

					$highestRow         = $worksheet->getHighestRow(); // e.g. 10
					$highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
					$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
					$nrColumns = ord($highestColumn) - 64;		
					
					$leadsImported = 0;
					$duplicateLeadsCtr = 0;
					$badLeadsCtr = 0;
					$existingLeadUpdatedCtr = 0;
					
					for ($row = 2; $row <= $highestRow; ++$row) 
					{	
						$last_name = $worksheet->getCell('A'.$row)->getValue();
						$first_name = $worksheet->getCell('B'.$row)->getValue();
						$address1 = $worksheet->getCell('C'.$row)->getValue();
						$address2 = $worksheet->getCell('D'.$row)->getValue();
						$city = $worksheet->getCell('E'.$row)->getValue();
						$state = $worksheet->getCell('F'.$row)->getValue();
						$zip = $worksheet->getCell('G'.$row)->getValue();
						$office_phone_number = preg_replace("/[^0-9]/","", $worksheet->getCell('H'.$row)->getValue());
						$mobile_phone_number = preg_replace("/[^0-9]/","", $worksheet->getCell('I'.$row)->getValue());
						$home_phone_number = preg_replace("/[^0-9]/","", $worksheet->getCell('J'.$row)->getValue());
						
						$type = 1;
						
						if( strlen($office_phone_number) < 10 && strlen($mobile_phone_number) < 10 && strlen($home_phone_number) < 10 )
						{
							$type = 2;
						}
						
						if( $type == 1 )
						{
							$existingLead = Lead::model()->find(array(
								'with' => 'list',
								'condition' => 'list.customer_id = :customer_id AND ( 
									(office_phone_number = :office_phone_number AND office_phone_number != "" AND office_phone_number IS NOT NULL) OR 
									(mobile_phone_number = :mobile_phone_number AND mobile_phone_number != "" AND mobile_phone_number IS NOT NULL) OR
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
							if( $model->duplicate_action !== null )
							{
								if( $model->duplicate_action == $model::DUPLICATES_UPDATE_LEAD_INFO || $model->duplicate_action == $model::DUPLICATES_UPDATE_LEAD_INFO_RESET_DIALS )
								{
									$existingLead->setAttributes(array(
										'list_id' => $model->id,
										'last_name' => $last_name,
										'first_name' => $first_name,
										'address1' => $address1,
										'address2' => $address2,
										'city' => $city,
										'state' => $state,
										'zip' => $zip,
										'office_phone_number' => $office_phone_number,
										'mobile_phone_number' => $mobile_phone_number,
										'home_phone_number' => $home_phone_number,
										'type' => $type,
										'language' => $model->language,
									));
									
									if( $model->duplicate_action == $model::DUPLICATES_UPDATE_LEAD_INFO_RESET_DIALS )
									{
										$existingLead->number_of_dials = 0;
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
							
							$lead->setAttributes(array(
								'list_id' => $model->id,
								'last_name' => $last_name,
								'first_name' => $first_name,
								'address1' => $address1,
								'address2' => $address2,
								'city' => $city,
								'state' => $state,
								'zip' => $zip,
								'office_phone_number' => $office_phone_number,
								'mobile_phone_number' => $mobile_phone_number,
								'home_phone_number' => $home_phone_number,
								'type' => $type,
								'language' => $model->language,
							));
							
							if( $lead->save(false) )
							{
								$leadHistory = new LeadHistory;
								
								$leadHistory->setAttributes(array(
									'lead_id' => $lead->id,
									'agent_account_id' => Yii::app()->user->account->id,
									'type' => 4,								
								));
								
								$leadHistory->save(false);

								
								if( $type == 1 )
								{
									$leadsImported++;
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
				}
			
			
				if( isset($_POST['ajax']) )
				{
					echo json_encode(array(
						'status' => 'success',
						'message' => 'Database has been updated.',
					));
				}
				else
				{
					Yii::app()->user->setFlash('success', '<b>Database</b> has been updated.');
					$this->redirect(array('leads/index', 'id'=>$model->id, 'customer_id'=>$customer_id));
				}
			}
		}
		
		$this->render('update', array(
			'model' => $model,
			'customer_id' => $customer_id,
			'simpleView' => $simpleView,
		));
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
			'C' => 'Address 1',
			'D' => 'Address 2',
			'E' => 'City',
			'F' => 'State',
			'G' => 'Zip',
			'H' => 'Office Phone',
			'I' => 'Mobile Phone',
			'J' => 'Home Phone',
			'K' => 'Number of Dials',
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
			'condition' => 'list_id = :list_id and type=1',
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
				$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $lead->address);
				$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $lead->address2);
				$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $lead->city);
				$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $lead->state);
				$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $lead->zip_code);
				$objPHPExcel->getActiveSheet()->SetCellValue('H'.$ctr, $lead->office_phone_number);
				$objPHPExcel->getActiveSheet()->SetCellValue('I'.$ctr, $lead->mobile_phone_number);
				$objPHPExcel->getActiveSheet()->SetCellValue('J'.$ctr, $lead->home_phone_number);
				$objPHPExcel->getActiveSheet()->SetCellValue('K'.$ctr, $lead->number_of_dials);
				
				
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
		$fileName = $_FILES['FileUpload']['name']['filename'];

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
			$fileUpload->original_filename = $fileName;
			$fileUpload->generated_filename = $fileName;
			
			if( $fileUpload->save(false) )
			{
				die('{"jsonrpc" : "2.0", "generatedFileUploadId": "'.$fileUpload->id.'", "generatedFilename": "' . $fileName . '", "fileExtension": "' . $manFileExt. '"}');
			}
		}
		
		// Return JSON-RPC response
		die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
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
			'C' => 'Address 1',
			'D' => 'Address 2',
			'E' => 'City',
			'F' => 'State',
			'G' => 'Zip',
			'H' => 'Office Phone',
			'I' => 'Mobile Phone',
			'J' => 'Home Phone',
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
	
}

?>