<?php

class CompanyController extends Controller
{
	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$this->redirect(array('update','id'=>$id));
		
		$this->render('view',array(
			'model'=>$this->loadModel($id),
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new Company;
		$model->scrub_settings = 4; //ON COMPANY DNC selected by default
		
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Company']))
		{
			$model->attributes=$_POST['Company'];
			if($model->save())
			{
				
				//auto create Account
				$account = $this->autoCreateAccount($model);
				
				//auto create Company Permission
				CompanyPermission::autoAddPermissionKey($model);
				
				$this->redirect(array('view','id'=>$model->id));
			}
		}

		$this->render('create',array(
			'model'=>$model,
		));
	}

	public function autoCreateAccount($model)
	{
		$account = new Account;
		$account->email_address = $model->email_address;
		$account->account_type_id = Account::TYPE_COMPANY;
		
		$getToken=rand(0, 99999);
		$getTime=date("H:i:s");
		$account->token = md5($getToken.$getTime);
		$account->token_date = date("Y-m-d H:i:s", strtotime("+3 days"));
		
		if($account->save(false))
		{
			$model->account_id = $account->id;
			if($model->save(false))
			{
				$this->emailSend($account);
		
			}
		}
		
		return $account;
	}
	
	public function emailSend($account)
	{
		$yiiName = Yii::app()->name;
				$emailadmin= Yii::app()->params['adminEmail'];
				$emailSubject="Company Registration";
				
				$emailContent = "Link for your account portal creation<br/>
				Email Address: ".$account->email_address."<br/>
					<a href='https://portal.engagexapp.com/index.php/site/register?token=".$account->token."'>Click Here to create your account</a>";
					
					
				$emailTemplate = '<table width="80%" align="center">
					<tr>
						<td>
							<table width="100%" border="0" cellpadding="0" cellspacing="0">
								<tr>
									<td align="center" bgcolor="#0068B1" height="10px;"></td>
								</tr>
								<tr>
									<td align="center" bgcolor="#FCB245">&nbsp;</td>
								</tr>
							</table>
							
							<br />
							
							'.$emailContent.'
							
							<br /><br/>
							
							<table width="100%" border="0" cellpadding="0" cellspacing="0">
								<tr>
									<td align="center" bgcolor="#FCB245">&nbsp;</td>
								</tr>
								<tr>
									<td align="left" bgcolor="#0068B1" height="10px;" style="font-size:18px; padding:5px;">&copy; Engagex, 2015</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>';
				
				
				$name='=?UTF-8?B?'.base64_encode($yiiName).'?=';
				$subject='=?UTF-8?B?'.base64_encode($emailSubject).'?=';
				$headers="From: $name <{$emailadmin}>\r\n".
					"Reply-To: {$emailadmin}\r\n".
					"MIME-Version: 1.0\r\n".
					"Content-type: text/html; charset=UTF-8";
				
			// return	mail($account->email_address,$subject,$emailTemplate,$headers);
			
			//Send Email
			Yii::import('application.extensions.phpmailer.JPhpMailer');
	
			$mail = new JPhpMailer;
			 
			// $mail->Host = "64.251.10.115";
			
			// $mail->IsSMTP(); 											
									
			// $mail->SMTPAuth = true;
			
			// $mail->SMTPSecure = "tls";   

			// $mail->Port = 587;      
			
			// $mail->Username = "service@engagex.com";  
			
			// $mail->Password = "Engagex123";          											
	
			$mail->SetFrom('service@engagex.com');
			$mail->AddCC('customerservice@engagex.com');
			
			$mail->Subject = $emailSubject;
			
			$mail->AddAddress( $account->email_address );
			
			$mail->MsgHTML( $emailTemplate);
									
			$mail->Send();
	}
	
	public function actionRegenerateToken($id)
	{
		$model = $this->loadModel($id);
		
		$account = $model->account;
		
		if(!empty($account->email_address))
		{
			$getToken=rand(0, 99999);
			$getTime=date("H:i:s");
			$account->token = md5($getToken.$getTime);
			$account->token_date = date("Y-m-d H:i:s", strtotime("+30 minutes"));
			
			if( $account->save(false) )
				$this->emailSend($account);
			
			Yii::app()->user->setFlash('success','Resending customer email successful');
		}
		else
			Yii::app()->user->setFlash('success','No email address found, sending failed');
		$this->redirect(array('company/update','id' => $model->id));
	}
	
	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Company']))
		{
			$model->attributes=$_POST['Company'];
			if($model->save())
			{
				//auto create Account
				if(!isset($model->account))
				{
					$account = $this->autoCreateAccount($model);
				}
				else
				{
					$model->account->email_address = $model->email_address;
					$model->account->save(false);
				}
				
				Yii::app()->user->setFlash('success', 'Company has been updated successfully!');
				$this->redirect(array('view','id'=>$model->id));
			}
		}

		$this->render('update',array(
			'model'=>$model,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		$this->loadModel($id)->delete();

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$this->render('index',array(
		));
	}

	/**
	 * Manages all models.
	 */
	public function actionList()
	{
		$model=new Company('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Company']))
			$model->attributes=$_GET['Company'];

		$this->renderPartial('_list',array(
			'model'=>$model,
		));
	}

	public function actionAjaxLoadChild()
	{
		$tier_ParentSubTier_Id = $_POST['tier_ParentSubTier_Id'];
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => '',
		);

		$criteria = new CDbCriteria;
		$criteria->compare('parent_tier_id', $tier_ParentSubTier_Id);
		$criteria->compare('status', Tier::STATUS_ACTIVE);
		$childTiers = Tier::model()->findAll($criteria);
		
		if(!empty($childTiers))
		{	
			$result['status'] = 'success';
			
			foreach($childTiers as $childTier)
			{
				$html .= '<li id="'.$childTier->id.'" class="tree-branch">';
					
					$html .= '<div class="tree-branch-header">';
						
						$html .= '<span class="tree-branch-name">';
							$html .= '<i class="icon-folder ace-icon tree-plus"></i>';
							
							$html .= '<span class="tree-label">';
							$html .= $childTier->tier_name;
							$html .= '</span>';
						$html .= '</span>';
						
						$html .= ' <a id="parentTier-'.$childTier->tier_name.'" class="btn btn-minier add-child-tier" tier_ParentTier_Id="'.$childTier->id.'" tier_ParentSubTier_Id="'.$childTier->id.'" tier_Company_Id="'.$childTier->company_id.'" tier_Level="'.$childTier->tier_level.'" tier_Name="'.$childTier->tier_name.'">Add</a>';
						$html .= ' <a class="btn btn-minier edit-tier" id="'.$childTier->id.'" tier_Company_Id="'.$childTier->company_id.'" tier_Name="'.$childTier->tier_name.'">Edit</a>';
					$html .= '</div>';
					
					$html .= '<ul class="tree-branch-children"></ul>';
					
			
				$html .= '</li>';
			}
			
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}
	
	public function actionAddTier($companyId = null, $parentTierId = null)
	{
		$model = new Tier;
		$model->company_id = $companyId;
		$model->parent_tier_id = $parentTierId;
		
		if(isset($_POST['ajax']) && $_POST['ajax']==='tier-tierForm-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
		
		if(isset($_POST['Tier']))
		{
			$model->attributes = $_POST['Tier'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->company_id));
		}
		
		$this->render('tierForm',array(
			'model' => $model
		));
	}
	
	public function actionDidList($id)
	{
		$company = $this->loadModel($id);
		
		$criteria = new CDbCriteria;
		$criteria->compare('company_id', $company->id);
		$companyDids = CompanyDid::model()->findAll($criteria);
		
		$this->renderPartial('didList',array(
			'companyDids' => $companyDids,
		));
	}
	
	public function actionAjaxAddDid($id)
	{
		$company = $this->loadModel($id);
		
		$model = new CompanyDid;
		$model->company_id = $company->id;
		
		if(isset($_POST['ajax']) && $_POST['ajax']==='companyDid-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
		
		if(isset($_POST['CompanyDid']))
		{
			$model->attributes = $_POST['CompanyDid'];
			
			if($model->save())
			{
				
				$response = array(
					'success' => true,
					'message' => 'Adding DID successful!',
					'scenario' => 'add',
				);
			}
			else
			{
				$response = array(
					'success' => false,
					'message' => 'Adding DiD error!',
					'scenario' => 'add',
				);
			}
			
			echo CJSON::encode($response);
			Yii::app()->end();
		}
		
		Yii::app()->clientscript->scriptMap['jquery.min.js'] = false;
		$this->renderPartial('didForm',array(
			'model' => $model,
			'actionController' => Yii::app()->createUrl('/admin/company/ajaxAddDid',array('id' => $company->id)),
		),false,true);
	}
	
	public function actionAjaxEditDid()
	{
		$model = CompanyDid::model()->findByPk($_REQUEST['companydid_id']);
		
		if(isset($_POST['ajax']) && $_POST['ajax']==='companyDid-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
		
		if(isset($_POST['CompanyDid']))
		{
			$model->attributes = $_POST['CompanyDid'];
			
			if($model->save())
			{
				
				$response = array(
					'success' => true,
					'message' => 'Editing DID successful!',
					'companydid_id' => $model->id,
					'scenario' => 'edit',
				);
			}
			else
			{
				$response = array(
					'success' => false,
					'message' => 'Editing DID error!',
					'scenario' => 'edit',
				);
			}
			
			echo CJSON::encode($response);
			Yii::app()->end();
		}
		
		Yii::app()->clientscript->scriptMap['jquery.min.js'] = false;
		$this->renderPartial('didForm',array(
			'model' => $model,
			'actionController' => Yii::app()->createUrl('/admin/company/ajaxEditDid',array('companydid_id' => $model->id)),
		),false,true);
	}
	
	public function actionAjaxRemoveDid()
	{
		$model = CompanyDid::model()->findByPk($_REQUEST['companydid_id']);
		if($model !== null)
			$model->delete();
		
		
	}
	/* public function actionAjaxLoadForm()
	{
		
		$model = new Tier;
		
		if(isset($_POST['ajax']) && $_POST['ajax']==='tier-tierForm-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
		
		if(isset($_POST['Tier']))
		{
			Yii::app()->end();
		}
		
		
		$this->renderPartial('tierForm',array(
			'model' => $model,
		),false,true);
	} */
	
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
				
				$company = Company::model()->findByPk($_REQUEST['event_id']);
				
				if($company !== null)
				{					
					$company->fileupload_id = $fileupload->id;
					
					if($company->save(false))
					{
						die('{"jsonrpc" : "2.0", "generatedFileUploadId": "'.$fileupload->id.'", "generatedFilename": "' . $fileName . '", "fileExtension": "' . $manFileExt. '"}');
					}
				}
				
				
			}
		}
		
		// Return JSON-RPC response
		die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
	}
	
	public function actionFlyerUpload()
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
				// $thumb=Yii::app()->phpThumb->create($fileupload->imagePath.$fileName);
				// $thumb->resize(100,100);
				// $thumb->save($fileupload->imagePathThumb.$fileName);
				
				$company = Company::model()->findByPk($_REQUEST['event_id']);
				
				if($company !== null)
				{					
					$company->flyer_fileupload_id = $fileupload->id;
					
					if($company->save(false))
					{
						die('{"jsonrpc" : "2.0", "generatedFileUploadId": "'.$fileupload->id.'", "generatedFilename": "' . $fileName . '", "fileExtension": "' . $manFileExt. '"}');
					}
				}
				
				
			}
		}
		
		// Return JSON-RPC response
		die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
	}
	
	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Company the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Company::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Company $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='company-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}

	
	public function actionResetPopupLogins($id)
	{
		$models = CustomerPopupLogin::model()->findAll(array(
			'condition' => 'company_id = :company_id',
			'params' => array(
				'company_id' => $id,
			)
		));
		
		if( $models )
		{
			foreach( $models as $model )
			{
				$model->delete();
			}
		}
		
		Yii::app()->user->setFlash('success', 'You have successfully reset the popup login views.');
		$this->redirect(array('company/update', 'id'=>$id));
	}
	
	public function actionRedactorUpload()
	{
		if( $_FILES )
		{
			$dir  = Yii::getPathOfAlias('webroot') . '/fileupload/';
			$baseUrl  = Yii::app()->request->baseUrl . '/fileupload/';
			
			$fileExtension = strtolower( pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION) );
	 
			if ( in_array( $fileExtension, array('jpg', 'jpeg', 'pjpeg', 'png', 'gif')) )
			{
				// setting file's mysterious name
				// $filename = md5(date('YmdHis')).'.'.$fileExtension;
				$filename = $_FILES['file']['name'];
			 
				// copying
				move_uploaded_file($_FILES['file']['tmp_name'], $dir . $filename);
			 
				// displaying file
				echo json_encode(array('filelink' => $baseUrl . $filename));
			}
		}
	}
}
