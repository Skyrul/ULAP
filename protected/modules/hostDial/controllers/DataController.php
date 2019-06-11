<?php

ini_set('memory_limit', '4000M');
set_time_limit(0);

class DataController extends Controller
{
	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
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
				
				if( $customer && $customer->id != $id )
				{
					$this->redirect(array('update', 'id'=>$customer->id));
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
				
				if( $customerOfficeStaff && $customerOfficeStaff->customer_id != $id )
				{
					$this->redirect(array('update', 'id'=>$customerOfficeStaff->customer_id));
				}
			}
		}
		
		
		$authAccount = Yii::app()->user->account;
		
		$currentValues = array();
		
		$model=$this->loadModel($id);
		$model->setScenario("hostDial");
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Customer']))
		{
			$currentValues = $model->attributes;
			
			$model->attributes=$_POST['Customer'];
			
			$difference = array_diff($model->attributes, $currentValues);
			
			if($model->save())
			{
				if( !empty($_POST['Customer']['salesRepIds']) )
				{
					CustomerSalesRep::model()->deleteAll(array(
						'condition' => 'customer_id = :customer_id',
						'params' => array(
							':customer_id' => $model->id,
						),
					));
					
					foreach( $_POST['Customer']['salesRepIds'] as $salesRepAccountId )
					{
						$salesRep = new CustomerSalesRep;
								
						$salesRep->setAttributes(array(
							'customer_id' => $model->id,
							'sales_rep_account_id' => $salesRepAccountId
						));
						
						$salesRep->save(false);
					}
				}
				
				//create record for auditing
				if( $difference )
				{
					$updateFields = '';
				
					foreach( $difference as $attributeName => $value)
					{
						if( $attributeName == 'state' )
						{
							$oldStateValue = State::model()->findByPk($currentValues[$attributeName])->name;
							$newStateValue = State::model()->findByPk($value)->name;
							
							$updateFields .= $model->getAttributeLabel($attributeName) .' changed from '.$oldStateValue.' to '.$newStateValue.', ';
						}
						else
						{
							$updateFields .= $model->getAttributeLabel($attributeName) .' changed from '.$currentValues[$attributeName].' to '.$value.', ';
						}
					}
					
					$updateFields = rtrim($updateFields, ', ');
					
					$history = new CustomerHistory;
					
					$history->setAttributes(array(
						'model_id' => $model->id, 
						'customer_id' => $model->id,
						'user_account_id' => $authAccount->id,
						'page_name' => 'Customer Setup',
						'content' => $updateFields,
						'old_data' => json_encode($currentValues),
						'new_data' => json_encode($model->attributes),
						'type' => $history::TYPE_UPDATED,
					));

					$history->save(false);
				}
				
				
				//auto create Account
				if(!isset($model->account))
				{
					$account = $this->autoCreateAccount($model);
				}
				else
				{
					$account = $model->account;
					if(isset($_POST['Account']))
					{
						$account->username = $_POST['Account']['username'];
						$account->email_address = $model->email_address;

						if($account->save(false))
						{
							$staff = CustomerOfficeStaff::model()->find(array(
								'condition' => 'account_id = :account_id',
								'params' => array(
									':account_id' => $account->id,
								),
							));
							
							if( $staff )
							{
								$staff->account->username = $account->username;
								$staff->account->email_address = $account->email_address;
								$staff->account->save(false);
							}
							
							if($account->id == $authAccount->id)
							{
								Yii::app()->user->name = $account->username;
							}
						}
					}
				}
				
				Yii::app()->user->setFlash('success', 'Customer setup updated!');
				$this->redirect(array('update','id'=>$model->id));
			}
		}
		
		## Skill Company ##
		$criteria = new CDbCriteria;
		$criteria->compare('customer_id',$model->id);
		$selectedSalesReps = CHtml::listData(CustomerSalesRep::model()->findAll($criteria),'sales_rep_account_id','sales_rep_account_id');

		$this->render('update',array(
			'model'=>$model,
			'selectedSalesReps'=>$selectedSalesReps,
		));
	}

	
	public function actionUpload()
	{
		// Settings
		$targetDir = 'fileupload';

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
			
			$file = CUploadedFile::getInstanceByName('FileUpload[filename]');
			
			$getFileExtension = explode('.', $fileName);

			if(count($getFileExtension > 1))
			{
				$manFileExt = $getFileExtension[count($getFileExtension) - 1];
			}
			
			$rnd = rand(0,9999).strtotime(date("Y-m-d H:i:s")); 
			$fileName = "{$rnd}-{$fileName}";
		
			// Rename file to use generated unique filename
			rename($filePath, $targetDir . DIRECTORY_SEPARATOR . $fileName);
			
			
			$fileupload = new Fileupload;
			
			
			$fileupload->setAttributes(array(
				'generated_filename' => $fileName,
			));
			
			if( $fileupload->save(false) )
			{
				$thumb=Yii::app()->phpThumb->create($fileupload->imagePath.$fileName);
				$thumb->resize(100,100);
				$thumb->save($fileupload->imagePathThumb.$fileName);
				
				$customer = Customer::model()->findByPk($_REQUEST['event_id']);
				
				if($customer !== null)
				{
					$currentValues = $customer->attributes;
					
					$customer->fileupload_id = $fileupload->id;

					$difference = array_diff($customer->attributes, $currentValues);
					
					if($customer->save(false))
					{
						//create record for auditing
						if( $difference )
						{
							$updateFields = 'Photo';
							
							$history = new CustomerHistory;
							
							$history->setAttributes(array(
								'model_id' => $customer->id, 
								'customer_id' => $customer->id,
								'user_account_id' => Yii::app()->user->account->id,
								'page_name' => 'Customer Setup',
								'content' => $updateFields,
								'old_data' => json_encode($currentValues),
								'new_data' => json_encode($customer->attributes),
								'type' => $history::TYPE_UPDATED,
							));

							$history->save(false);
						}
				
						die('{"jsonrpc" : "2.0", "generatedFileUploadId": "'.$fileupload->id.'", "generatedFilename": "' . $fileName . '", "fileExtension": "' . $manFileExt. '"}');
					}
				}
				
				
			}
		}
		
		// Return JSON-RPC response
		die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
	}
	
	public function actionVoiceRecord()
	{
		$model = Customer::model()->findByPk($_POST['id']);
		if($model === null)
				throw new CHttpException('403', 'Page not found.');
		
		$rnd = rand(0,9999).strtotime(date("Y-m-d H:i:s")).'-'.preg_replace('/[^a-zA-Z0-9-_\.]/','', $model->getFullName()).'.wav'; 
		
		if(isset($_POST['audio'])){
			  $audio = $_POST['audio'];
			  
			  $file = str_replace('data:audio/wav;base64,', '', $audio); 
			  
			  $decoded = base64_decode($file);
			  // $file_location = Yii::app()->request->baseUrl."/voice/recorded_audio.wav";
			  $file_location =Yii::app()->basePath.'/../voice/'.$rnd;
			  file_put_contents($file_location, $decoded);
			  
			  
			  $fileupload = new Fileupload;
			  $fileupload->original_filename = $rnd;
			  $fileupload->generated_filename = $rnd;
			  
			  if($fileupload->save(false))
			  {
				  $model->voiceupload_id = $fileupload->id;
				  $model->save(false);
			  }
			  
			  echo Yii::app()->request->baseUrl."/voice/".$rnd;
		}
		
		
		
	}
	
	public function actionRegenerateToken($id)
	{
		$model = $this->loadModel($id);
		
		$account = $model->account;
		
		$getToken=rand(0, 99999);
		$getTime=date("H:i:s");
		$account->token = md5($getToken.$getTime);
		$account->token_date = date("Y-m-d H:i:s", strtotime("+3 days"));
		
		if( $account->save(false) )
		{
			$this->emailSend($account);
		}
		
		$this->redirect(array('data/update','id' => $model->id));
	}
	
	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Customer the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Customer::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Customer $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='customer-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}

}
