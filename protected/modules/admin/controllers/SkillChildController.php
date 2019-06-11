<?php

class SkillChildController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	// public $layout='//layouts/column2';

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
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
				'actions'=>array('view', 'create', 'update', 'delete', 'index', 'AddSkillAccount', 'RemoveSkillChildAccount'),
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
	public function actionCreate($skill_id = null)
	{
		$model=new SkillChild;
		$model->skill_id = $skill_id;
		
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['SkillChild']))
		{
			$model->attributes=$_POST['SkillChild'];
			
			$uploadedFile = CUploadedFile::getInstance($model,'fileUpload');
				
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
					}
				}
			}
			
			if(isset($_REQUEST['createNewOrCloneExisting']) && $_REQUEST['createNewOrCloneExisting'] == 2)
			{
				$transaction = Yii::app()->db->beginTransaction();
					
				try
				{
					$model->setScenario('cloneExisting');
					
					$skillChild = SkillChild::model()->findByPk($model->existingId);
					
					if($skillChild !== null)
					{
						// print_r($skillChild->attributes); exit;
						$model->attributes = $skillChild->attributes;
						$model->setAttributes(array(
							'skill_id' => $_POST['SkillChild']['skill_id'],
							'child_name' => $_POST['SkillChild']['child_name'],
							'description' => $_POST['SkillChild']['description'],
						));
					}
					
					$valid = $model->validate();
					
					if($valid)
					{
						$model->save(false);
						$error = 0;
						
						//Skill Disposition
						if(!empty($skillChild->skillChildDispositions))
						{
							foreach($skillChild->skillChildDispositions as $skillChildDisposition)
							{
								$scd = new SkillChildDisposition;
								$scd->attributes = $skillChildDisposition->attributes;
								$scd->skill_child_id = $model->id;
								if(!$scd->save(false))
								{
									$error++;
								}
								else
								{
									//create disposition detail
									if(!empty($skillChildDisposition->skillChildDispositionDetails))
									{
										foreach($skillChildDisposition->skillChildDispositionDetails as $skillChildDispositionDetail)
										{
											$scdd = new SkillChildDispositionDetail;
											$scdd->attributes = $skillChildDispositionDetail->attributes;
											$scdd->skill_child_id = $model->id;
											$scdd->skill_child_disposition_id = $scd->id;
											if(!$scdd->save(false))
											{
												$error++;
											}
										}
									}
								}
								
							}
						}
						
						//skillChildSchedules
						if(!empty($skillChild->skillChildSchedules))
						{
							foreach($skillChild->skillChildSchedules as $skillChildSchedule)
							{
								$scs = new SkillChildSchedule;
								$scs->attributes = $skillChildSchedule->attributes;
								$scs->skill_child_id = $model->id;
								if(!$scs->save(false))
								{
									$error++;
								}
							}
						}
						
						if($error == 0)
						{
							$transaction->commit();
						}
					}
					
				}
				catch(Exception $e)
				{
					$transaction->rollback();
					print_r($e); exit;
				}
			}
			else
			{
				$valid = $model->validate();
			}
			
			if($valid)
			{
				$model->save(false);
				$this->redirect(array('index','skill_id'=>$model->skill_id));
			}
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
	public function actionUpdate($id, $skill_id = null)
	{
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['SkillChild']))
		{
			$transaction = Yii::app()->db->beginTransaction();
				
			try
			{
				$selectedAgent = array();
					
				// echo '<pre>';
				// print_r($_REQUEST);
				// exit;
				if(isset($_POST['SkillChildAccount']))
				{
					
					if(isset($_POST['SkillChildAccount']['trained']))
					{
						foreach($_POST['SkillChildAccount']['trained'] as $key => $account_id)
						{
							$skillChildAccount = SkillChildAccount::model()->find(array(
								'condition'=>'skill_child_id = :skill_child_id AND agent_id = :agent_id  AND train_type = :train_type',
								'params'=>array(
									':agent_id' => $account_id,
									':skill_child_id' => $model->id,
									':train_type' => 1,
								),
							));
					
							if(empty($skillChildAccount))
							{
								$skillChildAccount = new SkillChildAccount;
								$skillChildAccount->skill_child_id = $model->id;
								$skillChildAccount->agent_id = $account_id;
							}
							
							
							$skillChildAccount->train_type = 1;
							
							if(!$skillChildAccount->save())
							{
								print_r($skillChildAccount->attributes);
								print_r($skillChildAccount->getErrors());
								exit;
							}
							
						}
						
						
						foreach($model->skillChildAccounts as $skillChildAccount)
						{
							if(!in_array($skillChildAccount->agent_id, $_POST['SkillChildAccount']['trained'] ) && $skillChildAccount->train_type == 1)
							{
								$skillChildAccount->delete(false);
							}
						}
					
					}
					###############################
					
					if(isset($_POST['SkillChildAccount']['not_trained']))
					{
						foreach($_POST['SkillChildAccount']['not_trained'] as $key => $account_id)
						{
							$skillChildAccount = SkillChildAccount::model()->find(array(
								'condition'=>'skill_child_id = :skill_child_id AND agent_id = :agent_id AND train_type = :train_type',
								'params'=>array(
									':agent_id' => $account_id,
									':skill_child_id' => $model->id,
									':train_type' => 2,
								),
							));
					
							if(empty($skillChildAccount))
							{
								$skillChildAccount = new SkillChildAccount;
								$skillChildAccount->skill_child_id = $model->id;
								$skillChildAccount->agent_id = $account_id;
							}
							
							
							$skillChildAccount->train_type = 2;
							
							if(!$skillChildAccount->save())
							{
								print_r($skillChildAccount->attributes);
								print_r($skillChildAccount->getErrors());
								exit;
							}
							
						}
						
						
						foreach($model->skillChildAccounts as $skillChildAccount)
						{
							if(!in_array($skillChildAccount->agent_id, $_POST['SkillChildAccount']['not_trained'] ) && $skillChildAccount->train_type == 2)
							{
								$skillChildAccount->delete(false);
							}
						}
					}
				}
						
				$model->attributes=$_POST['SkillChild'];
				
				$uploadedFile = CUploadedFile::getInstance($model,'fileUpload');
				
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
						}
					}
				}
				
				if($model->save())
				{	
					$transaction->commit();
					$this->redirect(array('update','id'=> $model->id, 'skill_id'=>$model->skill_id));
				}
			}
			catch(Exception $ex)
			{
				$transaction->rollback();
				print_r($ex->getMessage()); exit;
			}
		}
			
		$skillChildAccounts = $model->skillChildAccounts;
		
		$skillChildAccountsArray = array();
	   
	   
		if(!empty($skillChildAccounts))
		{
			foreach($skillChildAccounts as $skillChildAccount)
			{
					$skillChildAccountsArray[$skillChildAccount->train_type][] = $skillChildAccount->agent_id;
			}
		}
		
		
		$this->render('update',array(
			'model'=>$model,
			'skillChildAccountsArray'=>$skillChildAccountsArray,
		));
	}

	public function actionAddSkillAccount($skill_child_id, $account_id)
	{
		$assignedSkill = AccountSkillChildAssigned::model()->find(array(
			'condition' => 'skill_child_id = :skill_child_id AND account_id = :account_id',
			'params' => array(
				':skill_child_id' => $skill_child_id,
				':account_id' => $account_id,
			),
		));
		
		if( $assignedSkill )
		{
			$assignedSkill->delete();
		}
		
		$trainedSkill = new AccountSkillChildTrained;
		
		$trainedSkill->setAttributes(array(
			'skill_child_id' => $skill_child_id,
			'account_id' => $account_id,
		));
		
		if(!$trainedSkill->save(false))
		{
			print_r($trainedSkill->getErrors());
		}
	}
	
	public function actionRemoveSkillChildAccount($skill_child_id, $account_id)
	{
		
		$trainedSkill = AccountSkillChildTrained::model()->find(array(
			'condition' => 'skill_child_id = :skill_child_id AND account_id = :account_id',
			'params' => array(
				':skill_child_id' => $skill_child_id,
				':account_id' => $account_id,
			),
		));
		
		if( $trainedSkill )
		{
			$trainedSkill->delete();
		}
		
		$assignedSkill = new AccountSkillChildAssigned;
		
		$assignedSkill->setAttributes(array(
			'skill_child_id' => $skill_child_id,
			'account_id' => $account_id,
		));
		
		$assignedSkill->save(false);
	}
	
	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id, $skill_id)
	{ 
		$skillChild = $this->loadModel($id)->delete();
		// $skillChild = $this->loadModel($id);
		// $skillChild->is_deleted = 1;
		// $skillChild->save(false);
		
		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index','skill_id'=>$skill_id));
	}

	public function actionIndex($skill_id)
	{
		$skill = Skill::model()->findByPk($skill_id);
		
		if($skill === null)
			throw new CHttpException('403', 'Page not found.');
		
		$skillChild = $this->_getSkillChildList();
		
		$dataProvider=new CActiveDataProvider($skillChild);
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
			'skill'=>$skill,
		));
	}

	public function _getSkillChildList()
	{
		$model = new SkillChild;
		
		if(!empty($_REQUEST['skill_id']))
		{
			$model->bySkillId($_REQUEST['skill_id']);
		}
		
		return $model;
	}


	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return SkillChild the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=SkillChild::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param SkillChild $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='skill-child-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}

	public function actionDownload($id)
	{
		$model = Fileupload::model()->findByPk($id);

		$filePath = Yii::getPathOfAlias('webroot') . '/fileupload/' . $model->generated_filename;

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
}
