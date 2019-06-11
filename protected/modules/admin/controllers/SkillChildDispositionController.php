<?php

class SkillChildDispositionController extends Controller
{

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			// 'accessControl', // perform access control for CRUD operations
			// 'postOnly + delete', // we only allow deletion via POST request
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			// array('allow',  // allow all users to perform 'index' and 'view' actions
				// 'actions'=>array('index','view'),
				// 'users'=>array('*'),
			// ),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('view', 'create', 'update', 'delete', 'index', 'admin', 'emailSettings', 'redactorUpload', 'upload', 'deleteEmailAttachment'),
				'users'=>array('@'),
			),
			// array('allow', // allow admin user to perform 'admin' and 'delete' actions
				// 'actions'=>array('admin','delete'),
				// 'users'=>array('admin'),
			// ),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$this->render('view',array(
			'model'=>$this->loadModel($id),
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate($skill_child_id = null)
	{
		$model=new SkillChildDisposition;
		$model->skill_child_id = $skill_child_id;
		
		$scenario = $model->getScenario();
		
		$model->setScenario('retryInterval');
		
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['SkillChildDisposition']))
		{
			$model->attributes=$_POST['SkillChildDisposition'];
			
			if($model->is_complete_leads > 0 || $model->is_callback > 0)
				$model->setScenario($scenario);
			
			if($model->save())
				$this->redirect(array('index','skill_child_id'=>$model->skill_child_id));
		}

		$this->render('create',array(
			'model'=>$model,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);
		
		$scenario = $model->getScenario();
		
		$model->setScenario('retryInterval');
		
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['SkillChildDisposition']))
		{
			$model->attributes=$_POST['SkillChildDisposition'];
			
			if($model->is_complete_leads > 0 || $model->is_callback > 0)
				$model->setScenario($scenario);
			
			if($model->save())
				$this->redirect(array('index','skill_child_id'=>$model->skill_child_id));
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
	public function actionDelete($id,$skill_child_id)
	{
		$this->loadModel($id)->delete();

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index','skill_child_id'=>$skill_child_id));
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex($skill_child_id)
	{
		$skillChild = SkillChild::model()->findByPk($skill_child_id);
		
		if($skillChild === null)
			throw new CHttpException('403', 'Page not found.');
		
		$SkillChildDisposition = $this->_getSkillChildDispositionList();
		
		$dataProvider=new CActiveDataProvider($SkillChildDisposition);
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
			'skillChild'=>$skillChild,
		));
	}

	
	public function actionEmailSettings($id)
	{
		$model = $this->loadModel($id);
		
		$attachments = SkillChildDispositionEmailAttachment::model()->findAll(array(
			'condition' => 'skill_disposition_id = :skill_disposition_id',
			'params' => array(
				':skill_disposition_id' => $model->id,
			),
		));
		
		if( isset($_POST['SkillChildDisposition']) )
		{
			$model->attributes = $_POST['SkillChildDisposition'];
			
			if($model->save(false))
			{
				if( isset($_POST['fileUploads']) )
				{
					foreach( $_POST['fileUploads'] as $fileUploadId)
					{
						$emailAttachment = new SkillChildDispositionEmailAttachment;
						
						$emailAttachment->setAttributes(array(
							'skill_disposition_id' => $model->id,
							'fileupload_id' => $fileUploadId,
						));
						
						$emailAttachment->save(false);
					}
				}
				
				$status = 'success';
				$message = 'Email settings has been updated.';
			}
			else
			{
				$status = 'danger';
				$message = 'Database error: ' . print_r($model->getErrors());
			}
			
			Yii::app()->user->setFlash($status, $message);
			$this->redirect(array('emailSettings','id'=>$model->id));
		}
		
		$this->render('emailSettings', array(
			'model' => $model,
			'attachments' => $attachments,
		));
	}
	
	public function actionTextSettings($id)
	{
		$model = $this->loadModel($id);
		
		if( isset($_POST['SkillChildDisposition']) )
		{
			$model->attributes = $_POST['SkillChildDisposition'];
			
			if($model->save(false))
			{
				$status = 'success';
				$message = 'Text settings has been updated.';
			}
			else
			{
				$status = 'danger';
				$message = 'Database error: ' . print_r($model->getErrors());
			}
			
			Yii::app()->user->setFlash($status, $message);
			$this->redirect(array('textSettings','id'=>$model->id));
		}
		
		$this->render('textSettings', array(
			'model' => $model,
		));
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
	
	public function actionDeleteEmailAttachment()
	{
		$result = array(
			'status' => 'error',
		);
		
		if( isset( $_POST['id'] ))
		{
			$model = SkillDispositionEmailAttachment::model()->findByPk($_POST['id']);
			
			if( $model && $model->delete() )
			{
				$result['status'] = 'success';
			}
		}
		
		echo json_encode($result);
	}
	
	
	
	public function _getSkillChildDispositionList()
	{
		$model = new SkillChildDisposition;
		
		if(!empty($_REQUEST['skill_child_id']))
		{
			$model->byChildSkillId($_REQUEST['skill_child_id']);
		}
		
		return $model;
	}
	
	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model=new SkillChildDisposition('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['SkillChildDisposition']))
			$model->attributes=$_GET['SkillChildDisposition'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return SkillChildDisposition the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=SkillChildDisposition::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param SkillChildDisposition $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='skill-disposition-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}

	public function actionAjaxUpdateRetryIntervalOptions()
	{
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => $html,
		);
		
		if( isset($_POST['ajax']) && isset($_POST['type']) )
		{
			//type 1 - HR
			//type 2 - Days
			$options = $_POST['type'] == 1 ? SkillChildDisposition::listIntervals() : SkillChildDisposition::listRetryDayIntervals();

			$html .= '<option value="">--</option>';
			
			foreach( $options as $optionValue => $optionLabel )
			{
				$html .= '<option value="'.$optionValue.'">'.$optionLabel.'</option>';
			}
			
			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}
}
