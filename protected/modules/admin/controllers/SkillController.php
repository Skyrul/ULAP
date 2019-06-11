<?php

class SkillController extends Controller
{
	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$this->redirect(array('update','id'=> $id));
		
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
		$model=new Skill;
		$selectedSkillCompany = array();
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Skill']['companyIds']))
			$selectedSkillCompany = $_POST['Skill']['companyIds'];
		
		if(isset($_POST['Skill']))
		{
			$model->attributes=$_POST['Skill'];
			
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
			
			$transaction = Yii::app()->db->beginTransaction();
			
			try
			{
				
				if($model->save())
				{
					if(isset($_POST['Skill']['companyIds']))
					{
						foreach($_POST['Skill']['companyIds'] as $company_id)
						{
							$sc = new SkillCompany;
							$sc->company_id = $company_id;
							$sc->skill_id = $model->id;
							$sc->save(false);
						}
					}
					
					$transaction->commit();
					$this->redirect(array('view','id'=>$model->id));
				}
				// else
				// {
					// print_r($model->getErrors()); exit;
				// }
			}
			catch(Exception $e)
			{
				print_r($e); exit;
				$transaction->rollback();
			}
				
		}

		$this->render('create',array(
			'model'=>$model,
			'selectedSkillCompany'=>$selectedSkillCompany,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id, $tab = '')
	{
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Skill']))
		{
			// echo '<pre>';
			// print_r($_REQUEST);
			$transaction = Yii::app()->db->beginTransaction();
				
			try{
				if(isset($_POST['SkillServiceTab']))
				{
					if(isset($_POST['SkillServiceTab']['tab_values']))
					{
						foreach($_POST['SkillServiceTab']['tab_values'] as $key => $tab_value)
						{
							$skillServiceTab = SkillServiceTab::model()->find(array(
								'condition'=>'skill_id = :skill_id AND tab_value = :tab_value',
								'params'=>array(
									':skill_id' => $model->id,
									':tab_value' => $tab_value,
								),
							));
					
							if(empty($skillServiceTab))
							{
								$skillServiceTab = new SkillServiceTab;
								$skillServiceTab->skill_id = $model->id;
								$skillServiceTab->tab_value = $tab_value;
							}
							
							
							if(!$skillServiceTab->save())
							{
								print_r($skillServiceTab->attributes);
								print_r($skillServiceTab->getErrors());
								exit;
							}
							
						}
						
						
						foreach($model->skillServiceTabs as $skillServiceTab)
						{
							if(!in_array($skillServiceTab->tab_value, $_POST['SkillServiceTab']['tab_values'] ))
							{
								$skillServiceTab->delete(false);
							}
						}
					
					}
				}
				else
				{
					foreach($model->skillServiceTabs as $skillServiceTab)
					{
						$skillServiceTab->delete(false);
					}
				}
				
				$criteria = new CDbCriteria;
				$criteria->compare('skill_id',$model->id);
				$scs = SkillCompany::model()->findAll($criteria);
				
				##skill company##
				if(isset($_POST['Skill']['companyIds']))
				{
					foreach($scs as $_scs)
					{
						if(!in_array($_scs->company_id, $_POST['Skill']['companyIds']) )
						{
							$_scs->delete();
						}
						
					}
					
					foreach($_POST['Skill']['companyIds'] as $company_id)
					{
						$criteria = new CDbCriteria;
						$criteria->compare('company_id', $company_id);
						$criteria->compare('skill_id', $model->id);
						
						$sc = SkillCompany::model()->find($criteria);
						
						if($sc === null)
						{
							$cs = new SkillCompany;
							$cs->company_id = $company_id;
							$cs->skill_id = $model->id;
							$cs->save(false);
						}
					}
				}
				else 
				{
					foreach($scs as $_scs)
					{
						$_scs->delete();
					}
				}
				
				$model->attributes = $_POST['Skill'];
				
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
							$history = new SkillHistory;
							
							$history->setAttributes(array(
								'model_id' => $fileupload->id, 
								'skill_id' => $model->id,
								'account_id' => Yii::app()->user->account->id,
								'field_name' => 'Script Tab File',
								'content' => $fileupload->original_filename,
								'type' => empty($model->script_tab_fileupload_id) ? $history::TYPE_ADDED : $history::TYPE_UPDATED,
							));
								
							$history->save(false);
							
							$model->script_tab_fileupload_id = $fileupload->id;
						}
					}
				}
				
				if( $model->save() )
				{
					$transaction->commit();
					$this->redirect(array('view','id'=>$model->id));
				}
			
			}
			catch(Exception $ex){
				$transaction->rollback();
				// print_r($ex->getMessage()); exit;
			}
		}

		## Skill Accounts ##
		$skillAccounts = $model->skillAccounts;
		$skillAccountsArray = array();
	   
	   
	   	if(!empty($skillAccounts))
		{
			foreach($skillAccounts as $skillAccount)
			{
					$skillAccountsArray[$skillAccount->train_type][] = $skillAccount->agent_id;
			}
		}

		## Skill Service Tab ##
		$criteria = new CDbCriteria;
		$criteria->compare('skill_id',$model->id);
		$selectedServiceTab = CHtml::listData(SkillServiceTab::model()->findAll($criteria),'tab_value','tab_value');
		
		
		## Skill Company ##
		$criteria = new CDbCriteria;
		$criteria->compare('skill_id',$model->id);
		$selectedSkillCompany = CHtml::listData(SkillCompany::model()->findAll($criteria),'company_id','company_id');
		
		
		$skillEmailTemplateId = null;
		if(isset($_GET['skillEmailTemplateId']))
		{
			$skillEmailTemplateId = $_GET['skillEmailTemplateId'];
		}
		
		
		$attachments = SkillEmailTemplateAttachment::model()->findAll(array(
			'condition' => 'skill_id = :skill_id',
			'params' => array(
				':skill_id' => $model->id,
			),
		));
		
		$this->render('update',array(
			'model'=>$model,
			'tab'=>$tab,
			'skillEmailTemplateId'=>$skillEmailTemplateId,
			'skillAccountsArray'=>$skillAccountsArray,
			'selectedSkillCompany'=>$selectedSkillCompany,
			'selectedServiceTab'=>$selectedServiceTab,
			'attachments'=>$attachments,
		));
	}

	public function actionAddSkillAccount($skill_id, $account_id)
	{
		// $skill = Skill::model()->findByPk($skill_id);
		
		// if($skill !== null)
		// {
			// $criteria = new CDbCriteria;
			// $criteria->compare('skill_id', $skill->id);
			// $criteria->compare('agent_id', $account_id);
			
			// $skillAccount = SkillAccount::model()->find($criteria);
			
			// if($skillAccount === null)
			// {
				// $skillAccount = new SkillAccount;
				// $skillAccount->skill_id = $skill->id;
				// $skillAccount->agent_id = $account_id;
				// $skillAccount->train_type = 1;
				// $skillAccount->save(false);
				
				// print_r($skillAccount->getErrors());
			// }
		// }
		
		$assignedSkill = AccountSkillAssigned::model()->find(array(
			'condition' => 'skill_id = :skill_id AND account_id = :account_id',
			'params' => array(
				':skill_id' => $skill_id,
				':account_id' => $account_id,
			),
		));
		
		if( $assignedSkill )
		{
			$assignedSkill->delete();
		}
		
		$trainedSkill = new AccountSkillTrained;
		
		$trainedSkill->setAttributes(array(
			'skill_id' => $skill_id,
			'account_id' => $account_id,
		));
		
		if(!$trainedSkill->save(false))
		{
			print_r($trainedSkill->getErrors());
		}
	}
	
	public function actionRemoveSkillAccount($skill_id, $account_id)
	{
		// $skill = Skill::model()->findByPk($skill_id);
		
		// if($skill !== null)
		// {
			// $criteria = new CDbCriteria;
			// $criteria->compare('skill_id', $skill->id);
			// $criteria->compare('agent_id', $account_id);
			
			// $skillAccount = SkillAccount::model()->find($criteria);
			
			// if($skillAccount !== null)
			// {
				// $skillAccount->delete();
			// }
		// }
		
		
		$trainedSkill = AccountSkillTrained::model()->find(array(
			'condition' => 'skill_id = :skill_id AND account_id = :account_id',
			'params' => array(
				':skill_id' => $skill_id,
				':account_id' => $account_id,
			),
		));
		
		if( $trainedSkill )
		{
			$trainedSkill->delete();
		}
		
		$assignedSkill = new AccountSkillAssigned;
		
		$assignedSkill->setAttributes(array(
			'skill_id' => $skill_id,
			'account_id' => $account_id,
		));
		
		$assignedSkill->save(false);
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
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$dataProvider=new CActiveDataProvider('Skill');
		
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}

	/**
	 * Manages all models.
	 */
	public function actionList()
	{
		$model=new Skill('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Skill']))
			$model->attributes=$_GET['Skill'];

		$this->renderPartial('_list',array(
			'model'=>$model,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Skill the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Skill::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Skill $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='skill-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}

	public function actionClone($skill_id)
	{
		$transaction = Yii::app()->db->beginTransaction();
					
		try
		{
			$skill = new Skill;
			$cloneSkill = Skill::model()->findByPk($skill_id);
			
			$skill->attributes = $cloneSkill->attributes;
			
			if($skill->save())
			{
				$error = 0;
				
				$this->cloneSkillDisposition($skill, $cloneSkill);
				$this->cloneSkillScheduleAndPeriodAssignment($skill, $cloneSkill);
				$this->cloneSkillChild($skill, $cloneSkill);
				
				//skill Agent Assigned
				$criteria = new CDbCriteria;
				$criteria->compare('skill_id', $cloneSkill->id);
				
				$asas = AccountSkillAssigned::model()->findAll($criteria);
				
				foreach($asas as $asa)
				{
					$newAsa = new AccountSkillAssigned;
					$newAsa->account_id = $asa->account_id;
					$newAsa->skill_id = $skill->id;
					
					if(!$newAsa->save(false))
					{
						$error++;
					}
				}
				
				//skill Agent Trained
				$criteria = new CDbCriteria;
				$criteria->compare('skill_id', $cloneSkill->id);
				
				$asts = AccountSkillTrained::model()->findAll($criteria);
				
				foreach($asts as $ast)
				{
					$newAst = new AccountSkillTrained;
					$newAst->account_id = $ast->account_id;
					$newAst->skill_id = $skill->id;
					
					if(!$newAst->save(false))
					{
						$error++;
					}
				}
				
				if($error != 0)
				{
					throw new CHttpException('404', 'Cloning Skill Disposition error!');
				}
						
				$transaction->commit();
			}
			
		}
		catch(Exception $e)
		{
			$transaction->rollback();
			throw new CHttpException('404', 'Error Cloning Skill');
			
		}
	}
	
	public function cloneSkillDisposition(&$skill, $cloneSkill)
	{
		if($cloneSkill !== null)
		{
			$error = 0;
			
			if(!empty($cloneSkill->skillDispositions))
			{
				foreach($cloneSkill->skillDispositions as $skillDisposition)
				{
					$sd = new SkillDisposition;
					$sd->attributes = $skillDisposition->attributes;
					$sd->skill_id = $skill->id;
					
					if($sd->save(false))
					{
						//Skill Disposition Detail
						if(!empty($skillDisposition->skillDispositionDetails))
						{
							foreach($skillDisposition->skillDispositionDetails as $skillDispositionDetail)
							{
								$sdd = new SkillDispositionDetail;
								$sdd->attributes = $skillDispositionDetail->attributes;
								$sdd->skill_disposition_id = $sd->id;
								$sdd->skill_id = $sd->skill_id;
								if(!$sdd->save(false))
								{
									$error++;
								}
							}
						}
						
						//skillDispositionEmailAttachments
						if(!empty($skillDisposition->skillDispositionEmailAttachments))
						{
							foreach($skillDisposition->skillDispositionEmailAttachments as $skillDispositionEmailAttachment)
							{
								$sdea = new SkillDispositionEmailAttachment;
								$sdea->attributes = $skillDispositionEmailAttachment->attributes;
								$sdea->skill_disposition_id = $sd->id;
								
								if(!$sdea->save(false))
								{
									$error++;
								}
							}
						}
					}
				}
			}
			
			if($error != 0)
			{
				throw new CHttpException('404', 'Cloning Skill Disposition error!');
			}
		}
			
	}
	
	public function cloneSkillScheduleAndPeriodAssignment(&$skill, $cloneSkill)
	{
		if($cloneSkill !== null)
		{
			$error = 0;
			
			if(!empty($cloneSkill->skillSchedules))
			{
				foreach($cloneSkill->skillSchedules as $skillSchedules)
				{
					$ss = new SkillSchedule;
					$ss->attributes = $skillSchedules->attributes;
					$ss->skill_id = $skill->id;
					
					if(!$ss->save(false))
					{
						$error++;
					}
				}
			}
			
			if($error != 0)
			{
				throw new CHttpException('404', 'Cloning Skill Schedules error!');
			}
		}
			
	}
	
	public function cloneSkillChild(&$skill, $cloneSkill)
	{
		if($cloneSkill !== null)
		{
			$error = 0;
			
			if(!empty($cloneSkill->skillChilds))
			{
				foreach($cloneSkill->skillChilds as $skillChild)
				{
					$model=new SkillChild;
					$model->attributes = $skillChild->attributes;
					$model->skill_id = $skill->id;
					
					if($model->save())
					{
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
						
						//skillChild Agent Assigned
						$criteria = new CDbCriteria;
						$criteria->compare('skill_child_id', $skillChild->id);
						
						$asas = AccountSkillChildAssigned::model()->findAll($criteria);
						
						foreach($asas as $asa)
						{
							$newAsa = new AccountSkillChildAssigned;
							$newAsa->account_id = $asa->account_id;
							$newAsa->skill_child_id = $model->id;
							
							if(!$newAsa->save(false))
							{
								$error++;
							}
						}
						
						
						//skillChild Agent Trained
						$criteria = new CDbCriteria;
						$criteria->compare('skill_child_id', $skillChild->id);
						
						$asts = AccountSkillChildTrained::model()->findAll($criteria);
						
						foreach($asts as $ast)
						{
							$newAst = new AccountSkillChildTrained;
							$newAst->account_id = $ast->account_id;
							$newAst->skill_child_id = $model->id;
							
							if(!$newAst->save(false))
							{
								$error++;
							}
						}
					}
					else
					{
						$error++;
					}
				}
			}
			
			if($error != 0)
			{
				throw new CHttpException('404', 'Cloning Skill Child error!');
			}
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

	public function actionHistory()
	{
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => $html,
		);
		
		if( isset($_POST['ajax']) && isset($_POST['skill_id']) )
		{
			$model = Skill::model()->findByPk($_POST['skill_id']);
			
			$models = SkillHistory::model()->findAll(array(
				'condition' => 'skill_id = :skill_id AND status != 3',
				'params' => array(
					':skill_id' => $_POST['skill_id'],
				),
				'order' => 'date_created DESC',
			));

			$html = $this->renderPartial('history', array(
				'model' => $model,
				'models' => $models,
			), true);

			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}

	public function actionEmailSetting($id)
	{
		$model = $this->loadModel($id);
		$skillEmailTemplate = new SkillEmailTemplate;
		
		$attachments = SkillEmailTemplateAttachment::model()->findAll(array(
			'condition' => 'skill_email_template_id = :skill_email_template_id',
			'params' => array(
				':skill_email_template_id' => $model->id,
			),
		));
		
		$this->renderPartial('emailSetting', array(
			'model' => $model,
			'skillEmailTemplateId' => $skillEmailTemplateId,
		));
	}
	
	public function actionEmailSettingCreate($id)
	{
		$model = $this->loadModel($id);
		
		$skillEmailTemplate = new SkillEmailTemplate;
		
		
		if( isset($_POST['SkillEmailTemplate']) )
		{
			$skillEmailTemplate->skill_id = $model->id;
			$skillEmailTemplate->attributes = $_POST['SkillEmailTemplate'];
			
			if($skillEmailTemplate->save(false))
			{
				if( isset($_POST['fileUploads']) )
				{
					foreach( $_POST['fileUploads'] as $fileUploadId)
					{
						$emailAttachment = new SkillEmailTemplateAttachment;
						
						$emailAttachment->setAttributes(array(
							'skill_id' => $model->id,
							'skill_email_template_id' => $skillEmailTemplate->id,
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
			$this->redirect(array('update','id'=>$model->id,'tab'=>'emailSetting'));
		}
		
		$this->renderPartial('emailSettingCreate', array(
			'model' => $model,
			'skillEmailTemplate' => $skillEmailTemplate,
		));
	}
	
	public function actionEmailSettingUpdate($id, $skillEmailTemplateId)
	{
		$model = $this->loadModel($id);
		$skillEmailTemplate = SkillEmailTemplate::model()->findByPk($skillEmailTemplateId);
		
		$attachments = SkillEmailTemplateAttachment::model()->findAll(array(
			'condition' => 'skill_email_template_id = :skill_email_template_id',
			'params' => array(
				// ':skill_id' => $model->id,
				':skill_email_template_id' => $skillEmailTemplate->id,
			),
		));
		
		
		if( isset($_POST['SkillEmailTemplate']) )
		{
			$skillEmailTemplate->attributes = $_POST['SkillEmailTemplate'];
			
			if($skillEmailTemplate->save(false))
			{
				if( isset($_POST['fileUploads']) )
				{
					foreach( $_POST['fileUploads'] as $fileUploadId)
					{
						$emailAttachment = new SkillEmailTemplateAttachment;
						
						$emailAttachment->setAttributes(array(
							'skill_id' => $model->id,
							'skill_email_template_id' => $skillEmailTemplate->id,
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
			$this->redirect(array('update','id'=>$model->id,'tab'=>'emailSetting'));
		}
		
		$this->renderPartial('emailSettingUpdate', array(
			'model' => $model,
			'skillEmailTemplate' => $skillEmailTemplate,
			'attachments' => $attachments,
		));
	}
	
	public function actionEmailSettingDelete($skillEmailTemplateId)
	{
		$skillEmailTemplate = SkillEmailTemplate::model()->findByPk($skillEmailTemplateId);
		
		$skillEmailTemplate->delete();
		
		// foreach($skillEmailTemplate->skillEmailAttachments as $skillEmailAttachment)
		// {
		// }
		
		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(array('update','id'=>$skillEmailTemplate->skill_id,'tab'=>'emailSetting'));
	}
	
	public function actionDeleteEmailAttachment($attachment_id)
	{
		$seta = SkillEmailTemplateAttachment::model()->findByPK($attachment_id);
		$seta->delete();
		
		$this->redirect(array('update','id'=>$seta->skill_id,'tab'=>'emailSettingAttachment'));
	}

}
