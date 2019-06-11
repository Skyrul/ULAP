<?php

ini_set('memory_limit', '5000M');
set_time_limit(0);

class AccountUserController extends Controller
{
	public $layout = '//layouts/column_no_component_sidebar';
	
	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
			'postOnly + delete', // we only allow deletion via POST request
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
				'actions'=>array(
					'view', 'create', 'update', 'delete', 'index', 'list', 'fileupload', 'releaseLock',
					'employeeProfile', 'upload',  // Employee Profile tab
					'employeeFile', 'employeeDeleteFile', 'employeeUpdateFile', 'employeeExportHistory', 'documentUpload', 'employeeAddDocumentType', 'employeeUpdateDocumentType', 'employeeDeleteDocumentType', //Employee File tab
					'timeKeeping', 'timeKeepingTest', 'addPto', 'approvePto', 'denyPto', 'approvePtoForm', 'denyPtoForm', 'ajaxAddPtoForm', 'ajaxGetTotalLoginHours', //Time Keeping tab
					'payPeriodVarianceAction', 'mergeVariance', 'editVariance', 'addVariance', 'deleteVariance',
					'workSchedule', 'addworkSchedule', 'updateWorkSchedule', 'deleteWorkSchedule', 
					'exportPayPeriod',
					'assignments', 'updateAccountLanguage', 'updateAccountSkill', 'updateAccountSkillChild', 'updateAccountCompany', 'updateAccountCustomer', 'updateCustomerList', 'moveCustomers',  //Assigments tab
					'performance', 'ajaxStats',//Performance tab
					'export', 'ajaxLeadHistory', 'employeeDetails',
					'hostdialUser', 'test'
				),
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
		$account = new Account;
		$account->setScenario('createEmployee');  
		
		$accountUser = new AccountUser;
		
		if( $accountUser->isNewRecord )
		{
			$employeeNumber = "000100";
			
			$latestUserSql = "
				SELECT MAX(employee_number) AS employee_number FROM ud_account_user
				WHERE employee_number <= '000900'
			";
			
			$command = Yii::app()->db->createCommand($latestUserSql);
			$latestUser = $command->queryRow();
			
			if( $latestUser )
			{
				$employeeNumber = str_pad( ($latestUser['employee_number'] + 1), 6, "0", STR_PAD_LEFT);
			}

			$accountUser->employee_number = $employeeNumber;
		}

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		$fileupload = new Fileupload;  // this is my model related to table
        if(isset($_POST['Fileupload']))
        {
            $rnd = rand(0,9999).strtotime(date("Y-m-d H:i:s"));  // generate random number between 0-9999
            $fileupload->attributes=$_POST['Fileupload'];
 
            $uploadedFile=CUploadedFile::getInstance($fileupload,'original_filename');
            $fileName = "{$rnd}-{$uploadedFile}";  // random number + file name
            $fileupload->generated_filename = $fileName;
			
			// var_dump($uploadedFile); exit;
			if($uploadedFile !== null)
			{
				if($fileupload->save())
				{
					$imagePath = $fileupload->imagePath.$fileName;
					$uploadedFile->saveAs($imagePath);  // image will uplode to rootDirectory/fileupload/
					
					$thumb=Yii::app()->phpThumb->create($fileupload->imagePath.$fileName);
					$thumb->resize(100,100);
					$thumb->save($fileupload->imagePathThumb.$fileName);
					
					$accountUser->fileupload_id = $fileupload->id;
					$accountUser->save(false);
					
					// $this->redirect(array('/customer/data/update','id'=>$customer->id));
				}
			}
        }
		
		$audioFileupload = new FileuploadAudio;  // this is my model related to table
		if(isset($_POST['FileuploadAudio']))
        {
            $rnd = rand(0,9999).strtotime(date("Y-m-d H:i:s"));  // generate random number between 0-9999
            $audioFileupload->attributes=$_POST['FileuploadAudio'];
 
            $uploadedFile2=CUploadedFile::getInstance($audioFileupload,'original_filename');
            $fileName2 = "{$rnd}-{$uploadedFile2}";  // random number + file name
            $audioFileupload->generated_filename = $fileName2;
			
			// var_dump($uploadedFile); exit;
			if($uploadedFile2 !== null)
			{
				if($audioFileupload->save())
				{
					$audioPath = $audioFileupload->audioPath.$fileName2;
					$uploadedFile2->saveAs($audioPath);  
					
					$accountUser->voiceupload_id = $audioFileupload->id;
					$accountUser->save(false);
				}
			}
        }
		
		if(isset($_POST['Account']))
		{
			$account->attributes=$_POST['Account'];
			$accountUser->attributes=$_POST['AccountUser'];
			
			$validate = $account->validate();
			$validate = $accountUser->validate() && $validate;
			
			if( $_POST['AccountUser']['job_title'] == 'Sales Agent' && trim($_POST['AccountUser']['commission_rate']) != "" && !strpos($_POST['AccountUser']['commission_rate'], '%') ) 
			{
				$validate = false;
				$accountUser->addError('commission_rate', 'Invalid format');
			}
			
			if($validate)
			{
				$transaction = Yii::app()->db->beginTransaction();
				
				try
				{
					$account->date_last_password_change = date('Y-m-d H:i:s');
					$account->save(false);
					
					$accountUser->account_id = $account->id;
					$accountUser->save(false);
					
					$transaction->commit();
					
					$this->redirect(array('employeeProfile','id'=>$account->id));
					
				}
				catch(Exception $e)
				{
					$transaction->rollback();
				}
					
			}
			// else
			// {
				// echo '<pre>';
					// print_r($account->getErrors());
					// print_r($accountUser->getErrors());
				// exit;
			// }
			
			// if($model->save())
				// $this->redirect(array('view','id'=>$model->id));
		}

		$this->render('create',array(
			'account'=>$account,
			'accountUser'=>$accountUser,
			'fileupload'=>$fileupload,
			'audioFileupload'=>$audioFileupload,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$account = $this->loadModel($id);
		
		$oldPassword = $account->password;
		
		$account->password = null;
		$accountUser = $account->accountUser;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		$fileupload = new Fileupload;  // this is my model related to table
        if(isset($_POST['Fileupload']))
        {
            $rnd = rand(0,9999).strtotime(date("Y-m-d H:i:s"));  // generate random number between 0-9999
            $fileupload->attributes=$_POST['Fileupload'];
 
            $uploadedFile=CUploadedFile::getInstance($fileupload,'original_filename');
            $fileName = "{$rnd}-{$uploadedFile}";  // random number + file name
            $fileupload->generated_filename = $fileName;
			
			// var_dump($uploadedFile); exit;
			if($uploadedFile !== null)
			{
				if($fileupload->save())
				{
					$imagePath = $fileupload->imagePath.$fileName;
					$uploadedFile->saveAs($imagePath);  // image will uplode to rootDirectory/fileupload/
					
					$thumb=Yii::app()->phpThumb->create($fileupload->imagePath.$fileName);
					$thumb->resize(100,100);
					$thumb->save($fileupload->imagePathThumb.$fileName);
					
					$accountUser->fileupload_id = $fileupload->id;
					$accountUser->save(false);
					
					// $this->redirect(array('/customer/data/update','id'=>$customer->id));
				}
			}
        }
		
		if(isset($_POST['Account']))
		{
			$account->attributes=$_POST['Account'];
			
			if(empty($account->password))
			{
				$account->password = $oldPassword;
				$account->confirmPassword = $oldPassword;
			}
			
			$accountUser->attributes=$_POST['AccountUser'];
			
			$validate = $account->validate();
			$validate = $accountUser->validate() && $validate;
			
			if($validate)
			{
				$transaction = Yii::app()->db->beginTransaction();
				
				try
				{
					$account->save(false);
					$accountUser->save(false);
					
					$transaction->commit();
					
					Yii::app()->user->setFlash('user','User updated successfully!');
					$this->redirect(array('update','id'=>$account->id));
					
				}
				catch(Exception $e)
				{
				}
					
			}
			// if($model->save())
				// $this->redirect(array('view','id'=>$model->id));
		}

		$this->render('update',array(
			'account'=>$account,
			'accountUser'=>$accountUser,
			'fileupload'=>$fileupload,
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
		// $model = new Account('search');
		// $model->unsetAttributes();  // clear any default values
		
		// if(isset($_GET['Account']))
		// {
			// $model->attributes=$_GET['Account'];
		// }
		
		$criteria = new CDbCriteria;
		
		$criteria->with = 'accountUser';
		$criteria->together = true;
		$criteria->addCondition('accountUser.id IS NOT NULL', 'AND');
				
		if( !empty($_GET['search_query']) )
		{
			if( strpbrk($_GET['search_query'], '1234567890') !== FALSE )
			{
				if( strpos($_GET['search_query'], '(') !== false )
				{
					$searchQuery = $_GET['search_query'];
				}
				else
				{
					$searchQuery = $_GET['search_query'];
					$searchQuery = substr_replace($searchQuery, '(', 0, 0);
					$searchQuery = substr_replace($searchQuery, ')', 4, 0);
					$searchQuery = substr_replace($searchQuery, ' ', 5, 0);
					$searchQuery = substr_replace($searchQuery, '-', 9, 0);
				}
				
				$criteria->addCondition('accountUser.phone_number LIKE :search_query', 'AND');
				$criteria->addCondition('accountUser.mobile_number LIKE :search_query', 'OR');
				$criteria->params[':search_query'] = $searchQuery.'%';
			}
			else
			{
				$criteria->addCondition('accountUser.first_name LIKE :search_query', 'AND');
				$criteria->addCondition('accountUser.last_name LIKE :search_query', 'OR');
				$criteria->addCondition('CONCAT(accountUser.first_name , " " , accountUser.last_name) LIKE :search_query', 'OR');
				$criteria->params[':search_query'] = $_GET['search_query'].'%';
			}
		}
		
		if( !empty($_GET['search_filter']) )
		{
			if( $_GET['search_filter'] == 'hideInactive' )
			{
				$criteria->addCondition('t.status = :active', 'AND');
				$criteria->params[':active'] = 1;
			} 
		}
		
		$models = Account::model()->findAll($criteria);
		
		$dataProvider = new CArrayDataProvider($models, array(
			'pagination' => array(
				'pageSize' => 50
			),
		));
		
		$positions = Position::model()->findAll(array(
			'with' => 'account',
			'condition' => 'parent_id IS NULL AND account.status=1',
			'order' => 't.order ASC',
		));
		
		$this->renderPartial('_list',array(
			'dataProvider'=>$dataProvider,
			'positions'=>$positions,
		));
	}

	public function actionHostdialUser()
	{
		$criteria = new CDbCriteria;
		
		$criteria->with = 'accountUser';
		$criteria->together = true;
		$criteria->addCondition('accountUser.id IS NOT NULL', 'AND');
		$criteria->addCondition('t.account_type_id = :hostDialAgent', 'AND');
		$criteria->params[':hostDialAgent'] = 15;
				
		if( !empty($_GET['search_query']) )
		{
			$criteria->addCondition('accountUser.first_name LIKE :search_query', 'AND');
			$criteria->addCondition('accountUser.last_name LIKE :search_query', 'OR');
			$criteria->addCondition('CONCAT(accountUser.first_name , " " , accountUser.last_name) LIKE :search_query', 'OR');
			
			$criteria->params[':search_query'] = $_GET['search_query'].'%';
		}
		
		if( !empty($_GET['search_filter']) )
		{
			if( $_GET['search_filter'] == 'hideInactive' )
			{
				$criteria->addCondition('t.status = :active', 'AND');
				$criteria->params[':active'] = 1;
			} 
		}
		
		$models = Account::model()->findAll($criteria);
		
		$dataProvider = new CArrayDataProvider($models, array(
			'pagination' => array(
				'pageSize' => 50
			),
		));
		
		$positions = Position::model()->findAll(array(
			'with' => 'account',
			'condition' => 'parent_id IS NULL AND account.status=1',
			'order' => 't.order ASC',
		));
		
		$this->render('hostdialUser',array(
			'dataProvider'=>$dataProvider,
			'positions'=>$positions,
		));
	}
	
	public function actionEmployeeDetails($id)
	{
		$url = array('index');
		$noPermission = true;
		
		if( Yii::app()->user->account->checkPermission('employees_performance_tab','visible') && Yii::app()->user->account->checkPermission('employees_performance_tab','only_for_direct_reports', $id) )
		{
			$url = array('performance', 'id'=>$id);
			$noPermission = false;
		}
		
		if( Yii::app()->user->account->checkPermission('employees_assigments_tab','visible') && Yii::app()->user->account->checkPermission('employees_assigments_tab','only_for_direct_reports', $id) )
		{
			$url = array('assignments', 'id'=>$id);
			$noPermission = false;
		}
		
		if( Yii::app()->user->account->checkPermission('employees_time_keeping_tab','visible') && Yii::app()->user->account->checkPermission('employees_time_keeping_tab','only_for_direct_reports', $id) )
		{
			$url = array('timeKeeping', 'id'=>$id);
			$noPermission = false;
		}
		
		if( Yii::app()->user->account->checkPermission('employees_employee_file_tab','visible') && Yii::app()->user->account->checkPermission('employees_employee_file_tab','only_for_direct_reports', $id) )
		{
			$url = array('employeeFile', 'id'=>$id);
			$noPermission = false;
		}
		
		if( Yii::app()->user->account->checkPermission('employees_employee_profile_tab','visible') && Yii::app()->user->account->checkPermission('employees_employee_profile_tab','only_for_direct_reports', $id) )
		{
			$url = array('employeeProfile', 'id'=>$id);
			$noPermission = false;
		}
		
		if( $noPermission )
		{
			Yii::app()->user->setFlash('danger', 'Your security group has no access to the employee details pages.');
		}
		
		$this->redirect($url);
	}
	
	//employee profile tab
	public function actionEmployeeProfile($id)
	{
		if( !Yii::app()->user->account->checkPermission('employees_employee_profile_tab','visible') || !Yii::app()->user->account->checkPermission('employees_employee_profile_tab','only_for_direct_reports', $id) )
		{
			Yii::app()->user->setFlash('error', 'Your security group has no access to the employee profile page.');
			$this->redirect(array('accountUser/index'));
		}
		
		$account = $this->loadModel($id);
		
		$oldPassword = $account->password;
		
		$account->password = null;
		$accountUser = $account->accountUser;
		
		$accountLanguages = AccountLanguageAssigned::model()->findAll(array(
			'condition' => 'account_id = :account_id',
			'params' => array(
				':account_id' => $account->id,
			),
		));
		
		if( empty($accountLanguages) )
		{
			$autoCreateLanguageEntry = new AccountLanguageAssigned;
			
			$autoCreateLanguageEntry->setAttributes(array(
				'account_id' => $account->id,
				'language_id' => 1,
			));
			
			$autoCreateLanguageEntry->save(false);
		}
		
		$existingPosition = Position::model()->find(array(
			'condition' => 'account_id = :account_id',
			'params' => array(
				':account_id' => $account->id
			),
		));
		
		if( $existingPosition )
		{
			$position = $existingPosition;
		}
		else
		{
			$position = new Position;
			$position->account_id = $account->id;
		}
		
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		$fileupload = new Fileupload;  // this is my model related to table
        if(isset($_POST['Fileupload']))
        {
            $rnd = rand(0,9999).strtotime(date("Y-m-d H:i:s"));  // generate random number between 0-9999
            $fileupload->attributes=$_POST['Fileupload'];
 
            $uploadedFile=CUploadedFile::getInstance($fileupload,'original_filename');
            $fileName = "{$rnd}-{$uploadedFile}";  // random number + file name
            $fileupload->generated_filename = $fileName;
			
			// var_dump($uploadedFile); exit;
			if($uploadedFile !== null)
			{
				if($fileupload->save())
				{
					$imagePath = $fileupload->imagePath.$fileName;
					$uploadedFile->saveAs($imagePath);  // image will uplode to rootDirectory/fileupload/
					
					$thumb=Yii::app()->phpThumb->create($fileupload->imagePath.$fileName);
					$thumb->resize(100,100);
					$thumb->save($fileupload->imagePathThumb.$fileName);
					
					$accountUser->fileupload_id = $fileupload->id;
					$accountUser->save(false);
					
					// $this->redirect(array('/customer/data/update','id'=>$customer->id));
				}
			}
        }
		
		$audioFileupload = new FileuploadAudio;  // this is my model related to table
		if(isset($_POST['FileuploadAudio']))
        {
            $rnd = rand(0,9999).strtotime(date("Y-m-d H:i:s"));  // generate random number between 0-9999
            $audioFileupload->attributes=$_POST['FileuploadAudio'];
 
            $uploadedFile2=CUploadedFile::getInstance($audioFileupload,'original_filename');
            $fileName2 = "{$rnd}-{$uploadedFile2}";  // random number + file name
            $audioFileupload->generated_filename = $fileName2;
			
			// var_dump($uploadedFile); exit;
			if($uploadedFile2 !== null)
			{
				if($audioFileupload->save())
				{
					$audioPath = $audioFileupload->audioPath.$fileName2;
					$uploadedFile2->saveAs($audioPath);  
					
					$accountUser->voiceupload_id = $audioFileupload->id;
					$accountUser->save(false);
				}
			}
        }
		
		if(isset($_POST['Account']))
		{
			if( !empty($_POST['Account']['password']) )
			{
				$account->setScenario('updateEmployee');   
			}
			
			$accountCurrentValues = $account->attributes;
			
			$account->attributes=$_POST['Account'];
			
			$accountDifference = array_diff($account->attributes, $accountCurrentValues);
			
			if( $account->status != $accountCurrentValues['status'] )
			{
				$accountDifference['status'] = $account->status;
			}

			if( isset($_POST['Position']) )
			{
				$position->parent_id = $_POST['Position']['parent_id'];			
				$position->save(false);
			}
			
			if( $account->status != 1 )
			{
				$currentPosition = Position::model()->find(array(
					'condition' => 'account_id = :account_id',
					'params' => array(
						':account_id' => $account->id,
					),
				));
				
				if( $currentPosition )
				{
					Position::model()->updateAll(array('parent_id' => null), 'parent_id ='.$currentPosition->id);
				}
			}
			
			if(empty($account->password))
			{
				$account->password = $oldPassword;
				$account->confirmPassword = $oldPassword;
			}
			
			$currentEmployeeNumber = $accountUser->employee_number;
			
			$accountUserCurrentValues = $accountUser->attributes;
			
			$accountUser->attributes=$_POST['AccountUser'];
			
			$accountUserDifference = array_diff($accountUser->attributes, $accountUserCurrentValues);
			
			$duplicateEmployeeNumber = AccountUser::model()->find(array(
				'condition' => 'id != :id AND employee_number = :employee_number',
				'params' => array(
					':id' => $accountUser->id,
					':employee_number' => $_POST['AccountUser']['employee_number'],
				),
			));
			
			if( $duplicateEmployeeNumber )
			{
				$accountUser->employee_number = $currentEmployeeNumber;
				Yii::app()->user->setFlash('danger', 'Employee number <b>'.$_POST['AccountUser']['employee_number'].'</b> already exists.');
			}
			
			$validate = $account->validate();
			$validate = $accountUser->validate() && $validate;
			
			if( $_POST['AccountUser']['job_title'] == 'Sales Agent' && trim($_POST['AccountUser']['commission_rate']) != "" && !strpos($_POST['AccountUser']['commission_rate'], '%') ) 
			{
				$validate = false;
				$accountUser->addError('commission_rate', 'Invalid format');
			}
			
			if($validate)
			{
				$transaction = Yii::app()->db->beginTransaction();
				
				try
				{	
					if( !empty($_POST['Account']['password']) )
					{
						$account->date_last_password_change = date('Y-m-d H:i:s'); 
					}
					
					if( $account->save(false) && $accountUser->save(false) && $position->save(false) )
					{
						//create record for auditing
						
						$updateFields = '';
						
						if( $accountDifference )
						{
							foreach( $accountDifference as $attributeName => $value)
							{
								if( $attributeName == 'status' )
								{
									if( $value == 1 )
									{
										$value = 'checked';
									}
									else
									{
										$value = 'unchecked';
									}
									
									$updateFields .= '<b>Active Employee checkbox</b> was ' . $value.'<br>';
								}
								else
								{
									$updateFields .= '<b>'.$account->getAttributeLabel($attributeName) .'</b> changed from '.$accountCurrentValues[$attributeName].' to '.$value.'<br>';
								}
							}
							
							$updateFields = rtrim($updateFields, '<br>');
						}
						
						if( $accountUserDifference )
						{
							foreach( $accountUserDifference as $attributeName => $value)
							{
								if( $attributeName != 'phone_extension' )
								{
									$updateFields .= '<b>'.$accountUser->getAttributeLabel($attributeName) .'</b> changed from '.$accountUserCurrentValues[$attributeName].' to '.$value.'<br>';
								}
							}
							
							$updateFields = rtrim($updateFields, '<br>');
						}
						
						if( $updateFields != '' )
						{
							$audit = new AccountUserNote;
								
							$audit->setAttributes(array(
								'account_id' => Yii::app()->user->account->id,
								'account_user_id' => $accountUser->id,
								'content' => $updateFields,
								'old_data' => json_encode($accountCurrentValues) .'<br>'. json_encode($accountUserCurrentValues),
								'new_data' => json_encode($account->attributes) .'<br>'.json_encode($accountUser->attributes),
								'category_id' => 10,
							));
							
							$audit->save(false);
						}
					}
					
					$transaction->commit();
					
					Yii::app()->user->setFlash('success', '<b>Employee Profile</b> has been updated successfully!');
					$this->redirect(array('employeeProfile', 'id'=>$account->id));
					
				}
				catch(Exception $e)
				{
					$transaction->rollback();
				}
					
			}

			// if($model->save())
				// $this->redirect(array('view','id'=>$model->id));
		}

		$reportsToOptions = array();
		
		$reportsToModels = Position::model()->findAll(array(
			'with' => 'account',
			'condition' => 't.account_id != :account_id AND account.status=1',
			'params' => array(
				':account_id' => $account->id
			),
		));
		
		if( $reportsToModels )
		{
			foreach( $reportsToModels as $reportsToModel )
			{
				$reportsToOptions[$reportsToModel->id] = $reportsToModel->account->getFullName();
			}
		}
		
		$this->render('employeeProfile',array(
			'account'=>$account,
			'accountUser'=>$accountUser,
			'fileupload'=>$fileupload,
			'audioFileupload'=>$audioFileupload,
			'position'=>$position,
			'reportsToOptions'=>$reportsToOptions,
		));
	}
	
	
	//employee file tab
	public function actionEmployeeFile()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		$authAccount = Yii::app()->user->account;
		
		$account = $this->loadModel(isset($_POST['id']) ? $_POST['id'] : $_GET['id']);
		
		$accountUser = $account->accountUser;
		
			$result = array(
			'status' => 'error',
			'message' => '',
		);
	
		$notes = AccountUserNote::model()->findAll(array(
			'condition' => 'account_user_id = :account_user_id',
			'params' => array(
				':account_user_id' => $accountUser->id,
			),
			'order' => 'date_created DESC',
		));
		
		$docs = AccountUserDocument::model()->findAll(array(
			'condition' => 'account_user_id = :account_user_id',
			'params' => array(
				':account_user_id' => $accountUser->id,
			),
			'order' => 'date_created DESC',
		));
	
		$notesDataProvider = new CArrayDataProvider($notes);
		$docsDataProvider = new CArrayDataProvider($docs);
		
		if( isset($_POST['ajax']) && isset($_POST['AccountUserNote']) )
		{
			$model = new AccountUserNote;
			$model->account_id = $authAccount->id;
			$model->account_user_id = $accountUser->id;
			
			$model->attributes = $_POST['AccountUserNote'];
			
			if( $model->save() )
			{
				if( isset($_POST['fileUploads']) )
				{
					foreach( $_POST['fileUploads'] as $fileId )
					{
						$attachedFile = new AccountUserNoteFile;
						
						$attachedFile->setAttributes(array(
							'note_id' => $model->id,
							'fileupload_id' => $fileId,
						));
						
						$attachedFile->save(false);
					}
				}
				
				$result['status'] = 'success';
				$result['message'] = 'Database has been updated.';
			}
			
			echo json_encode($result);
			Yii::app()->end();
		}
		
		
		$this->render('employeeFile', array(
			'account' => $account,
			'accountUser' => $accountUser,
			'notesDataProvider' => $notesDataProvider,
			'docsDataProvider' => $docsDataProvider,
			'authAccount' => $authAccount,
		));
	}
	
	public function actionEmployeeExportHistory($id)
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
			'A' => 'Category',
			'B' => 'User',
			'C' => 'Date',
			'D' => 'Note',
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
		
		$accountUser = AccountUser::model()->findByPk($id);
		
		$models = AccountUserNote::model()->findAll(array(
			'condition' => 'account_user_id = :account_user_id',
			'params' => array(
				':account_user_id' => $accountUser->id,
			),
		));
		
		if($models)
		{
			$ctr = 2;
			
			foreach($models as $model)
			{
				$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $model->getCategory() );
				
				$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $model->account->accountUser->getFullName() );
				
				$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, date('m/d/y', strtotime($model->date_created)));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $model->content);
				
				$ctr++;
			}
		}

		header('Content-Type: application/vnd.ms-excel'); 
		header('Content-Disposition: attachment; filename="'.$accountUser->getFullName().' - Employee History.xlsx"'); 
		header('Cache-Control: max-age=0');
		
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
	}
	
	public function actionEmployeeUpdateFile()
	{
		$result = array(
			'status' => 'error',
		);
		
		$model = AccountUserDocument::model()->findByPk($_POST['id']);
		$model->type_id = $_POST['type'];
		
		if( $model->save(false) )
		{
			$result['status'] = 'success';
		}
		
		echo json_encode($result);
	}
	
	public function actionEmployeeDeleteFile()
	{
		$result = array(
			'status' => 'error',
		);
		
		$model = AccountUserDocument::model()->findByPk($_POST['id']);
		
		if( $model )
		{
			$file = Fileupload::model()->findByPk($model->fileupload_id);
			$file->delete();
			
			$model->delete();
			
			$result['status'] = 'success';
		}
		
		echo json_encode($result);
	}
	
	public function actionEmployeeDeleteDocumentType()
	{
		$result = array(
			'status' => 'error',
		);
		
		$model = AccountUserDocumentType::model()->findByPk($_POST['id']);
		
		if( $model )
		{
			$model->status = 3;
			$model->save(false);
			
			$result['status'] = 'success';
		}
		
		echo json_encode($result);
	}
	
	public function actionEmployeeAddDocumentType()
	{
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => $html,
		);
		
		$model = new AccountUserDocumentType;
		
		if( isset($_POST['AccountUserDocumentType']) )
		{
			$result['status'] = 'error';
			
			$model->attributes = $_POST['AccountUserDocumentType'];
			
			if( $model->save(false) )
			{
				$result['status'] = 'success';
				$result['message'] =  'Type was sucessfully added.';
				
				$models = AccountUserDocumentType::model()->findAll(array(
					'condition' => 't.status != 3',
					'order' => 't.date_created DESC',
				));
				
				if( $models )
				{
					foreach( $models as $modelType )
					{
						$activeChecked = $modelType->status == 1 ? 'checked' : '';
						$showDeleteChecked = $modelType->show_delete_button == 1 ? 'checked' : '';
						
						$html .= '<tr id="'.$modelType->id.'">';
						
							$html .= '<td><input type="text" class="col-sm-12" value="'.$modelType->name.'" style="margin-top:4px"></td>';
							
							$html .= '<td class="center">';
								$html .= '
									<div class="checkbox">
										<label>
											<input name="states[]" class="ace checkbox-status" type="checkbox" value="1" '.$activeChecked.'>
											<span class="lbl"></span>
										</label>
									</div>
								';
							$html .= '</td>';
							
							$html .= '<td class="center">';
								$html .= '
									<div class="checkbox">
										<label>
											<input name="states[]" class="ace checkbox-show-delete" type="checkbox" value="1" '.$showDeleteChecked.'>
											<span class="lbl"></span>
										</label>
									</div>
								';
							$html .= '</td>';
							
							$html .= '<td class="center">';
								$html .= '<button type="button" id="'.$modelType->id.'" class="btn btn-minier btn-danger btn-delete-document-type" style="margin-top:9px"><i class="fa fa-times"></i> Delete</button>';
							$html .= '</td>';
							
						$html .= '</tr>';
					}
				}

				$result['html'] = $html;
			}
			else
			{
				$result['status'] = 'error';
				$result['message'] = 'Database error.';
			}
		}
		else
		{
			$result['status'] = 'success';
			
			$models = AccountUserDocumentType::model()->findAll(array(
				'condition' => 't.status != 3',
				'order' => 't.date_created DESC',
			));
			
			$html = $this->renderPartial('ajax_add_document_type', array(
				'model' => $model,
				'models' => $models,
			), true);
			
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}
	
	public function actionEmployeeUpdateDocumentType()
	{
		$result = array(
			'status' => 'error',
		);
		
		$model = AccountUserDocumentType::model()->findByPk($_POST['id']);
		
		if( $_POST['field'] == 'name' )
		{
			$model->name = $_POST['value'];
		}
		elseif( $_POST['field'] == 'status' )
		{
			$model->status = $_POST['value'];
		}
		elseif( $_POST['field'] == 'show_edit_button' )
		{
			$model->show_edit_button = $_POST['value'];
		}
		else
		{
			$model->show_delete_button = $_POST['value'];
		}
		
		if( $model->save(false) )
		{
			$result['status'] = 'success';
		}
		
		echo json_encode($result);
	}
	
	public function actionTimeKeeping($filter='')
	{
		$payPeriodOptions = array();
		
		foreach( range(2015, 2018) as $year)
		{
			foreach( range( $year == 2015 ? 11 : 1 , 12) as $monthNumber )
			{
				$firstDayOfMonth = date('M d', strtotime($year.'-'.$monthNumber.'-01'));
				$fifteenthDayOfMonth = date('M d', strtotime($year.'-'.$monthNumber.'-16'));
				$lastDayOfMonth = date('d', strtotime('last day of this month', strtotime($firstDayOfMonth)));
				
				$payPeriodOptions[] = $firstDayOfMonth.' - 15 '.$year;
				$payPeriodOptions[] = $fifteenthDayOfMonth.' - '.$lastDayOfMonth.' '.$year;
			}
		}
		
		if( date('d') < 16 )
		{
			$currentPayPeriod =  date('M').' 01 - 15 ' . date('Y');
		}
		else
		{
			$currentPayPeriod = date('M').' 16 - '.date('d', strtotime('last day of this month')) .' '. date('Y');
		}
		
		$currentPayPeriod = array_search( $currentPayPeriod, $payPeriodOptions  );
		
		
		$authAccount = Yii::app()->user->account;
		
		$account = $this->loadModel(isset($_POST['id']) ? $_POST['id'] : $_GET['id']);
		
		$accountUser = $account->accountUser;
		
		$totalScheduledWorkHours = 0;
		
		$ptoFormRequests = AccountPtoForm::model()->findAll(array(
			'condition' => 'account_id = :account_id AND is_deleted = 0',
			'params' => array(
				':account_id' => $account->id,
			),
			'order' => 'date_created DESC',
		));
		
		$ptoRequests = AccountPtoRequest::model()->findAll(array(
			'condition' => 'account_id = :account_id AND status!=4',
			'params' => array(
				':account_id' => $account->id,
			),
			'order' => 'date_created DESC',
		));
		
		
		if( $filter != '')
		{
			$explodedDilterDate = explode(' - ', $payPeriodOptions[$filter]);
			$explodedElement1 = explode(' ', $explodedDilterDate[0]);
			$explodedElement2 = explode(' ', $explodedDilterDate[1]);

			$month = $explodedElement1[0];
			$start_day = $explodedElement1[1];
			$end_day = $explodedElement2[0];
			$year = $explodedElement2[1];
			
			$startDate =  $year.'-'.$month.'-'.$start_day;
			$endDate =  $year.'-'.$month.'-'.$end_day;
			
			if( $account->id == 1706 )
			{
				$payPeriods = AccountLoginTracker::model()->findAll(array(
					'condition' => '
						account_id = :account_id 
						AND DATE_SUB(time_in, INTERVAL 1 HOUR) >= :start_date 
						AND DATE_SUB(time_in, INTERVAL 1 HOUR) <= :end_date 
						AND status!=4
					',
					'params' => array(
						':account_id' => $account->id,
						':start_date' => date('Y-m-d 00:00:01', strtotime($startDate)),
						':end_date' => date('Y-m-d 23:59:59', strtotime($endDate)),
					),
					'order' => 'time_in DESC',
				));
			}
			else
			{
				$payPeriods = AccountLoginTracker::model()->findAll(array(
					'condition' => '
						account_id = :account_id 
						AND time_in >= :start_date 
						AND time_in <= :end_date 
						AND status!=4
					',
					'params' => array(
						':account_id' => $account->id,
						':start_date' => date('Y-m-d 00:00:01', strtotime($startDate)),
						':end_date' => date('Y-m-d 23:59:59', strtotime($endDate)),
					),
					'order' => 'time_in DESC',
				));
			}
		}
		else
		{
			$payPeriods = AccountLoginTracker::model()->findAll(array(
				'condition' => 'account_id = :account_id AND status!=4',
				'params' => array(
					':account_id' => $account->id,
				),
				'order' => 'time_in DESC',
			));
		}
		
		$ptoFormDataProvider = new CArrayDataProvider($ptoFormRequests, array(
			'pagination' => array(
				'pageSize' => 5,
			),
		));
		
		$ptoDataProvider = new CArrayDataProvider($ptoRequests, array(
			'pagination' => array(
				'pageSize' => 5,
			),
		));
		
		$payPeriodDataProvider = new CArrayDataProvider($payPeriods, array(
			'pagination' => array(
				'pageSize' => 5,
			),
		));

		$this->render('timeKeeping', array(
			'account' => $account,
			'accountUser' => $accountUser,
			'ptoFormDataProvider' => $ptoFormDataProvider,
			'ptoDataProvider' => $ptoDataProvider,
			'payPeriodDataProvider' => $payPeriodDataProvider,
			'payPeriodOptions' => $payPeriodOptions,
			'currentPayPeriod' => $currentPayPeriod,
		));
	}
	
	
	//time keeping tab - pto form || employee module
	public function actionApprovePtoForm()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		if( isset($_POST['Ids']) )
		{
			$models = AccountPtoForm::model()->findAll(array(
				'condition' => 'id IN ('.implode(', ', $_POST['Ids']).')',
			));
			
			if( $models )
			{
				foreach( $models as $model )
				{
					$model->status = 1;
					
					if( $model->save(false) )
					{
						#  $account->accountUser->mobile_number
						$mobileNo = $model->account->accountUser->mobile_number;
						$content = 'Engagex Time off Request '.$model->requestDateWithTime().' was Approved.';
						
						$jsonObject = $this->textAccount($mobileNo, $content);
						
						if($jsonObject !== false)
						{
							$accountPtoFormSms = new AccountPtoFormSms;
															
								$accountPtoFormSms->setAttributes(array(
									'account_pto_form_id' => $model->id,
									'account_id' => $model->account_id,
									'mobile_number' => urlencode($mobileNo),
									'api_code' => $jsonObject->code,
									'api_message' => $jsonObject->message
								));
								
							$accountPtoFormSms->save(false);
						}
		
						// $account = Account::model()->findByPk($model->account_id);
						// $accountUser = $account->accountUser;
						
						// $audit = new AccountUserNote;
							
						// $audit->setAttributes(array(
							// 'account_id' => Yii::app()->user->account->id,
							// 'account_user_id' => $accountUser->id,
							// 'content' => 'Approved PTO Request: '.$model->name,
							// 'old_data' => json_encode($currentValues),
							// 'new_data' => json_encode($model->attributes),
							// 'category_id' => 10,
						// ));

						// $audit->save(false);
					}
				}
			}
		}
		
		echo json_encode($result);
	}
	
	public function actionDenyPtoForm()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		if( isset($_POST['Ids']) )
		{
			$models = AccountPtoForm::model()->findAll(array(
				'condition' => 'id IN ('.implode(', ', $_POST['Ids']).')',
			));
			
			if( $models )
			{
				foreach( $models as $model )
				{
					$model->status = 3;
					
					if( $model->save(false) )
					{
						#  $account->accountUser->mobile_number
						$mobileNo = $model->account->accountUser->mobile_number;
						$content = 'Engagex Time off Request '.$model->requestDateWithTime().' was Denied.';
						
						$jsonObject = $this->textAccount($mobileNo, $content);
						
						if($jsonObject !== false)
						{
							$accountPtoFormSms = new AccountPtoFormSms;
															
								$accountPtoFormSms->setAttributes(array(
									'account_pto_form_id' => $model->id,
									'account_id' => $model->account_id,
									'mobile_number' => urlencode($mobileNo),
									'api_code' => $jsonObject->code,
									'api_message' => $jsonObject->message
								));
								
							$accountPtoFormSms->save(false);
						}
						
						// $account = Account::model()->findByPk($model->account_id);
						// $accountUser = $account->accountUser;
						
						// $audit = new AccountUserNote;
							
						// $audit->setAttributes(array(
							// 'account_id' => Yii::app()->user->account->id,
							// 'account_user_id' => $accountUser->id,
							// 'content' => 'Denied PTO Request: '.$model->name,
							// 'old_data' => json_encode($currentValues),
							// 'new_data' => json_encode($model->attributes),
							// 'category_id' => 10,
						// ));

						// $audit->save(false);
					}
				}
			}
		}
		
		echo json_encode($result);
	}
	
	public function actionAjaxAddPtoForm()
	{
		$contractOptions = array();		
		
		$authAccount = Yii::app()->user->account;
		
		$account = $this->loadModel(isset($_POST['id']) ? $_POST['id'] : $_GET['id']);
		
		$accountUser = $account->accountUser;

		$model = new AccountPtoForm;
		$model->account_id = $account->id;
		$model->is_full_shift = 1;
		$model->is_make_time_up = 2;
	
		if(isset($_POST['ajax']) && $_POST['ajax']==='account_pto_form-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
		
		if(isset($_POST['AccountPtoForm']))
		{			
			$model->attributes=$_POST['AccountPtoForm'];
			
			if(isset($_POST['AccountPtoForm']['date_of_request_start']) && ($_POST['AccountPtoForm']['date_of_request_start'] != '' && $_POST['AccountPtoForm']['date_of_request_start'] != '0000-00-00'))
			{
				$model->date_of_request_start = date("Y-m-d",strtotime($_POST['AccountPtoForm']['date_of_request_start']));
			}
			
			if(isset($_POST['AccountPtoForm']['date_of_request_end']) && ($_POST['AccountPtoForm']['date_of_request_end'] != '' && $_POST['AccountPtoForm']['date_of_request_end'] != '0000-00-00'))
			{
				$model->date_of_request_end = date("Y-m-d",strtotime($_POST['AccountPtoForm']['date_of_request_end']));
			}
			
			if(isset($_POST['AccountPtoForm']['date_of_make_time_up_start']) && ($_POST['AccountPtoForm']['date_of_make_time_up_start'] != '' && $_POST['AccountPtoForm']['date_of_make_time_up_start'] != '0000-00-00'))
			{
				$model->date_of_make_time_up_start = date("Y-m-d",strtotime($_POST['AccountPtoForm']['date_of_make_time_up_start']));
			}
			
			if(isset($_POST['AccountPtoForm']['date_of_make_time_up_end']) && ($_POST['AccountPtoForm']['date_of_make_time_up_end'] != '' && $_POST['AccountPtoForm']['date_of_make_time_up_end'] != '0000-00-00'))
			{
				$model->date_of_make_time_up_end = date("Y-m-d",strtotime($_POST['AccountPtoForm']['date_of_make_time_up_end']));
			}

			
			$model->computed_off_hour = $this->computedOffHours($model);
			
			if($model->save())
			{
				$response = array(
					'success' => true,
					'message' => 'Adding Time-off Request successful!',
					'scenario' => 'add',
				);
			}
			else
			{
				// print_r($model->getErrors());
				$response = array(
					'success' => false,
					'message' => 'Adding Time-off Request error!',
					'scenario' => 'add',
				);
			}
			
			echo CJSON::encode($response);
			Yii::app()->end();
		}
		
		Yii::app()->clientscript->scriptMap['jquery.min.js'] = false;
		$this->renderPartial('timeKeepingPtoForm',array(
			'model' => $model,
			'actionController' => Yii::app()->createUrl('/hr/accountUser/AjaxAddPtoForm',array('id' => $account->id)),
		),false,true);
	}
	
	public function computedOffHours($model)
	{
		$startTime = $model->off_hour_from.':'.$model->off_min_from.' '.$model->off_md_from;
		$endTime = $model->off_hour_to.':'.$model->off_min_to.' '.$model->off_md_to;

		$totalScheduledHours = 0;
		
		$startDate = strtotime($model->date_of_request_start);
		$endDate = strtotime($model->date_of_request_end);
		
		while( $startDate <= $endDate ) 
		{
			$startDateRequest = date("Y-m-d", $startDate);
			
			$schedules = AccountLoginSchedule::model()->findAll(array(
				'condition' => 'account_id = :account_id AND day_name = :day_name AND type=1',
				'params' => array(
					':account_id' => $model->account_id,
					':day_name' => date('l', $startDate),
				),
			));
			
			
			
			if( $schedules )
			{
				foreach( $schedules as $schedule )
				{
					$scheduleStart = strtotime($startDateRequest.' '.$schedule->start_time);
					$scheduleEnd = strtotime($startDateRequest.' '.$schedule->end_time);
					
					
					##increment schedule per 30 mins to get the computed off hours
					while($scheduleStart < $scheduleEnd)
					{
						$user_ts = $scheduleStart;
						
						if($model->is_full_shift == 1)
						{
							$start_ts = strtotime($startDateRequest.' '.$schedule->start_time);
							$end_ts = strtotime($startDateRequest.' '.$schedule->end_time);
							
						}
						else
						{
							$start_ts = strtotime($startDateRequest.' '.$startTime);
							$end_ts = strtotime($startDateRequest.' '.$endTime);
						}
						
						
						if(($user_ts >= $start_ts) && ($user_ts <= $end_ts))
						{
							$totalScheduledHours += 0.5;
						}
						
						$scheduleStart = strtotime('+30 minutes', $scheduleStart);
					}
				}
			}
			
			$startDate = strtotime('+1 day', $startDate);
		}
		
		// echo $totalScheduledHours;
		// exit;
		return $totalScheduledHours;
	}
	
	/* TIME KEEPING UPDATED WITH SCHEDULE CHANGED REQUEST 2018-01-06 can delete if no more issue
	//time keeping tab
	public function actionTimeKeeping($filter='')
	{
		$payPeriodOptions = array();
		
		foreach( range(2015, 2018) as $year)
		{
			foreach( range( $year == 2015 ? 11 : 1 , 12) as $monthNumber )
			{
				$firstDayOfMonth = date('M d', strtotime($year.'-'.$monthNumber.'-01'));
				$fifteenthDayOfMonth = date('M d', strtotime($year.'-'.$monthNumber.'-16'));
				$lastDayOfMonth = date('d', strtotime('last day of this month', strtotime($firstDayOfMonth)));
				
				$payPeriodOptions[] = $firstDayOfMonth.' - 15 '.$year;
				$payPeriodOptions[] = $fifteenthDayOfMonth.' - '.$lastDayOfMonth.' '.$year;
			}
		}
		
		if( date('d') < 16 )
		{
			$currentPayPeriod =  date('M').' 01 - 15 ' . date('Y');
		}
		else
		{
			$currentPayPeriod = date('M').' 16 - '.date('d', strtotime('last day of this month')) .' '. date('Y');
		}
		
		$currentPayPeriod = array_search( $currentPayPeriod, $payPeriodOptions  );
		
		
		$authAccount = Yii::app()->user->account;
		
		$account = $this->loadModel(isset($_POST['id']) ? $_POST['id'] : $_GET['id']);
		
		$accountUser = $account->accountUser;
		
		$totalScheduledWorkHours = 0;
		
		$ptoRequests = AccountPtoRequest::model()->findAll(array(
			'condition' => 'account_id = :account_id AND status!=4',
			'params' => array(
				':account_id' => $account->id,
			),
			'order' => 'date_created DESC',
		));
		
		
		if( $filter != '')
		{
			$explodedDilterDate = explode(' - ', $payPeriodOptions[$filter]);
			$explodedElement1 = explode(' ', $explodedDilterDate[0]);
			$explodedElement2 = explode(' ', $explodedDilterDate[1]);

			$month = $explodedElement1[0];
			$start_day = $explodedElement1[1];
			$end_day = $explodedElement2[0];
			$year = $explodedElement2[1];
			
			$startDate =  $year.'-'.$month.'-'.$start_day;
			$endDate =  $year.'-'.$month.'-'.$end_day;
			
			$payPeriods = AccountLoginTracker::model()->findAll(array(
				'condition' => 'account_id = :account_id AND time_in >= :start_date AND time_in <= :end_date AND status!=4',
				'params' => array(
					':account_id' => $account->id,
					':start_date' => date('Y-m-d 00:00:00', strtotime($startDate)),
					':end_date' => date('Y-m-d 23:59:59', strtotime($endDate)),
				),
				'order' => 'time_in DESC',
			));
		}
		else
		{
			$payPeriods = AccountLoginTracker::model()->findAll(array(
				'condition' => 'account_id = :account_id AND status!=4',
				'params' => array(
					':account_id' => $account->id,
				),
				'order' => 'time_in DESC',
			));
		}
		
		$ptoDataProvider = new CArrayDataProvider($ptoRequests, array(
			'pagination' => array(
				'pageSize' => 5,
			),
		));
		
		$payPeriodDataProvider = new CArrayDataProvider($payPeriods, array(
			'pagination' => array(
				'pageSize' => 5,
			),
		));

		$this->render('timeKeeping', array(
			'account' => $account,
			'accountUser' => $accountUser,
			'ptoDataProvider' => $ptoDataProvider,
			'payPeriodDataProvider' => $payPeriodDataProvider,
			'payPeriodOptions' => $payPeriodOptions,
			'currentPayPeriod' => $currentPayPeriod,
		));
	}
	*/
	
	//time keeping tab - pto
	public function actionAddPto()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => '',
		);
		

		$model = new AccountPtoRequest;
		$model->account_id = $_POST['account_id'];
		
		if( isset($_POST['AccountPtoRequest']) )
		{
			$model->attributes = $_POST['AccountPtoRequest'];
			
			if( $model->save() )
			{
				$result['status'] = 'success';
				$result['message'] = 'Database has been updated.';
				
				$account = Account::model()->findByPk($_POST['account_id']);
				$accountUser = $account->accountUser;
				
				$audit = new AccountUserNote;
					
				$audit->setAttributes(array(
					'account_id' => Yii::app()->user->account->id,
					'account_user_id' => $accountUser->id,
					'content' => 'Added PTO Request: '.$model->name,
					'old_data' => json_encode($currentValues),
					'new_data' => json_encode($model->attributes),
					'category_id' => 10,
				));

				$audit->save(false);
			}
		}
		
		if( isset( $_POST['ajax']) )
		{
			$html = $this->renderPartial('ajax_pto_create', array(
				'model' => $model,
			), true);

			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}
	
	public function actionApprovePto()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		if( isset($_POST['Ids']) )
		{
			$models = AccountPtoRequest::model()->findAll(array(
				'condition' => 'id IN ('.implode(', ', $_POST['Ids']).')',
			));
			
			if( $models )
			{
				foreach( $models as $model )
				{
					$model->status = 1;
					
					if( $model->save(false) )
					{
						$account = Account::model()->findByPk($model->account_id);
						$accountUser = $account->accountUser;
						
						$audit = new AccountUserNote;
							
						$audit->setAttributes(array(
							'account_id' => Yii::app()->user->account->id,
							'account_user_id' => $accountUser->id,
							'content' => 'Approved PTO Request: '.$model->name,
							'old_data' => json_encode($currentValues),
							'new_data' => json_encode($model->attributes),
							'category_id' => 10,
						));

						$audit->save(false);
					}
				}
			}
		}
		
		echo json_encode($result);
	}
	
	public function actionDenyPto()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		if( isset($_POST['Ids']) )
		{
			$models = AccountPtoRequest::model()->findAll(array(
				'condition' => 'id IN ('.implode(', ', $_POST['Ids']).')',
			));
			
			if( $models )
			{
				foreach( $models as $model )
				{
					$model->status = 3;
					
					if( $model->save(false) )
					{
						$account = Account::model()->findByPk($model->account_id);
						$accountUser = $account->accountUser;
						
						$audit = new AccountUserNote;
							
						$audit->setAttributes(array(
							'account_id' => Yii::app()->user->account->id,
							'account_user_id' => $accountUser->id,
							'content' => 'Denied PTO Request: '.$model->name,
							'old_data' => json_encode($currentValues),
							'new_data' => json_encode($model->attributes),
							'category_id' => 10,
						));

						$audit->save(false);
					}
				}
			}
		}
		
		echo json_encode($result);
	}
	
	public function actionExportPayPeriod($filter, $account_id)
	{
		// unregister Yii's autoloader
		spl_autoload_unregister(array('YiiBase', 'autoload'));
		
		// register PHPExcel's autoloader ... PHPExcel.php will do it
		$phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
		require_once($phpExcelPath . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'PHPExcel.php');
		
		// register Yii's autoloader again
		spl_autoload_register(array('YiiBase', 'autoload'));
		 
		// This requires Yii's autoloader
		
		$payPeriodOptions = array();
		
		foreach( range(2015, 2018) as $year)
		{
			foreach( range( $year == 2015 ? 11 : 1 , 12) as $monthNumber )
			{
				$firstDayOfMonth = date('M d', strtotime($year.'-'.$monthNumber.'-01'));
				$fifteenthDayOfMonth = date('M d', strtotime($year.'-'.$monthNumber.'-16'));
				$lastDayOfMonth = date('d', strtotime('last day of this month', strtotime($firstDayOfMonth)));
				
				$payPeriodOptions[] = $firstDayOfMonth.' - 15 '.$year;
				$payPeriodOptions[] = $fifteenthDayOfMonth.' - '.$lastDayOfMonth.' '.$year;
			}
		}
		
		$account = Account::model()->findByPk($account_id);
		$accountUser = $account->accountUser;
		
		
		$objPHPExcel = new PHPExcel();

		
		$objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Employee Name: ' . $accountUser->getFullName() );
		
		$objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Employee #: ' . $accountUser->employee_number );
		$objPHPExcel->getActiveSheet()->getStyle('B1')->getNumberFormat()->setFormatCode('000000');
		
		$objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Pay Period: ' . $payPeriodOptions[$filter] );
		
		
		$ctr = 3;
		
		$headers = array(
			'A' => 'Date',
			'B' => 'Login Time',
			'C' => 'Logout Time',
			'D' => 'Status',
			'E' => 'Note',
			'F' => 'Total Time',
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
	
		
		$explodedDilterDate = explode(' - ', $payPeriodOptions[$filter]);
		$explodedElement1 = explode(' ', $explodedDilterDate[0]);
		$explodedElement2 = explode(' ', $explodedDilterDate[1]);

		$month = $explodedElement1[0];
		$start_day = $explodedElement1[1];
		$end_day = $explodedElement2[0];
		$year = $explodedElement2[1];
		
		$startDate =  $year.'-'.$month.'-'.$start_day;
		$endDate =  $year.'-'.$month.'-'.$end_day;

		$loginRecords = AccountLoginTracker::model()->findAll(array(
			'condition' => 'account_id = :account_id AND date_created >= :start_date AND date_created <= :end_date AND status !=4',
			'params' => array(
				':account_id' => $account_id,
				':start_date' => date('Y-m-d 00:00:00', strtotime($startDate)),
				':end_date' => date('Y-m-d 23:59:59', strtotime($endDate)),
			),
			'order' => 'date_created DESC',
		));
		
		if( $loginRecords )
		{		
			$ctr = 4 ;
			
			foreach( $loginRecords as $loginRecord )
			{
				$totalTime = '00:00';
				$totalHours = 0;
				$totalMinutes = 0;
				
				$timeIn = new DateTime($loginRecord->time_in, new DateTimeZone('America/Chicago'));
				$timeIn->setTimezone(new DateTimeZone('America/Denver'));
				
				if( $loginRecord->type == 1 )
				{
					$timeOut = new DateTime($loginRecord->time_out, new DateTimeZone('America/Chicago'));
					$timeOut->setTimezone(new DateTimeZone('America/Denver'));
				}
				else
				{
					$timeOut = new DateTime($loginRecord->time_out);
				}

				if( $loginRecord->time_out != null )
				{
					$totalMinutes += round(abs(strtotime($loginRecord->time_in) - strtotime($loginRecord->time_out)) / 60,2);
					
					$totalHours =  floor($totalMinutes/60);
					$totalMinutes =   $totalMinutes % 60;
					
					if( strlen($totalHours) == 1)
					{
						$totalHours = '0'.$totalHours;
					}
					
					if( strlen($totalMinutes) == 1)
					{
						$totalMinutes = '0'.$totalMinutes;
					}
					
					$totalTime = $totalHours.':'.$totalMinutes;
				}
				
				if($loginRecord->status == 1)
				{
					$status = 'Approved';
				}
				elseif($loginRecord->status == 2)
				{
					// $status = 'Approved';
					
					// if( $loginRecord->employee_note != null )
					// {
						$status = 'For Approval';
					// }
				}
				else
				{
					$status = 'Denied';
				}
				
			
				$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, date('m/d/Y', strtotime($loginRecord->date_created)) );
			
				$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $timeIn->format('g:i A') );
				
				$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr,  $loginRecord->time_out != null ? $timeOut->format('g:i A') : '' );
				
				$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $status );
				
				$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $loginRecord->note );
				
				$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $totalTime );
				
				
				$ctr++;
			}
		}

		header('Content-Type: application/vnd.ms-excel'); 
		header('Content-Disposition: attachment; filename="Employee Pay Period.xlsx"'); 
		header('Cache-Control: max-age=0');
		
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
	}
		
	
	//time keeping tab - pay period
	public function actionPayPeriodVarianceAction()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => '',
		);

		
		if( isset($_POST['id']) )
		{
			$model = AccountLoginTracker::model()->findByPk($_POST['id']);
			
			if( isset($_POST['AccountLoginTracker']) )
			{
				$model->attributes = $_POST['AccountLoginTracker'];
				$model->status = $_POST['status'];
				
				if( $model->save(false) )
				{
					$result['status'] = 'success';
					
					$account = Account::model()->findByPk($model->account_id);
					$accountUser = $account->accountUser;
					
					$timeIn = new DateTime($model->time_in, new DateTimeZone('America/Chicago'));
					$timeIn->setTimezone(new DateTimeZone('America/Denver'));	

					$timeOut = new DateTime($model->time_out, new DateTimeZone('America/Chicago'));
					$timeOut->setTimezone(new DateTimeZone('America/Denver'));	
					
					$startTime = $timeIn->format('m/d/Y g:i A');
					
					$endTime = $model->time_out != null ? $timeOut->format('m/d/Y g:i A') : '';
					
					$status = $model->status == 1 ? 'Approved' : 'Denied';
					
					$content = $status.' time punch: '.$startTime.' to '.$endTime; 
					
					$audit = new AccountUserNote;
						
					$audit->setAttributes(array(
						'account_id' => Yii::app()->user->account->id,
						'account_user_id' => $accountUser->id,
						'content' => $content,
						'old_data' => json_encode($currentValues),
						'new_data' => json_encode($model->attributes),
						'category_id' => 10,
					));

					$audit->save(false);
					
				}
			}
			
			$html = $this->renderPartial('ajax_pay_period_variance_form', array(
				'model' => $model,
			), true);

			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}
	
	public function actionMergeVariance()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		if( isset($_POST['Ids']) )
		{
			$firstRecord = AccountLoginTracker::model()->find(array(
				'condition' => 'id IN ('.implode(', ', $_POST['Ids']).')',
				'order' => 'id ASC',
			));
			
			$lastRecord = AccountLoginTracker::model()->find(array(
				'condition' => 'id IN ('.implode(', ', $_POST['Ids']).')',
				'order' => 'id DESC',
			));
			
			$employeeNotes = '';
			$supervisorNotes = '';
			
			$punches = AccountLoginTracker::model()->findAll(array(
				'condition' => 'id IN ('.implode(', ', $_POST['Ids']).')',
				'order' => 'id ASC',
			));
			
			if( $punches )
			{
				foreach( $punches as $punch )
				{
					if( !empty($punch->employee_note) )
					{
						$employeeNotes .= '<p>'.$punch->employee_note.'</p>';
					}
					
					if( !empty($punch->note) )
					{
						$supervisorNotes .= '<p>'.$punch->note.'</p>';
					}
				}
			}
			
			if( $firstRecord && $lastRecord )
			{
				// if( $lastRecord->type == 1)
				// {
					// $timeOut = new DateTime($lastRecord->time_out, new DateTimeZone('America/Denver'));
					// $timeOut->setTimezone(new DateTimeZone('America/Chicago'));		
				// }
				// else
				// {
					$timeOut = new DateTime($lastRecord->time_out);
				// }	
				
				$firstRecord->setAttributes(array(
					'time_out' => $timeOut->format('Y-m-d H:i:s'),
					'login_session_token' => $lastRecord->login_session_token,
					'status' => 1,
					'note' => $supervisorNotes,
					'employee_note' => $employeeNotes,
					'type' => $lastRecord->type,
				));
				
				if( $firstRecord->save(false) )
				{
					$models = AccountLoginTracker::model()->findAll(array(
						'condition' => 'id IN ('.implode(', ', $_POST['Ids']).')',
					));
					
					if( $models )
					{ 
						foreach( $models as $model )
						{
							if( $firstRecord->id != $model->id )
							{
								$model->delete();
							}
						}
						
						$result['status'] = 'success';
					}
				}
			}
		}
		
		echo json_encode($result);
	}
	
	
	public function actionEditVariance()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);

		
		if( isset($_POST['id']) )
		{
			$model = AccountLoginTracker::model()->findByPk($_POST['id']);
			$account = $model->account;
			$accountUser = $account->accountUser;
			
			if( isset($_POST['AccountLoginTracker']) )
			{
				$originalTimeIn = new DateTime($model->time_in, new DateTimeZone('America/Chicago'));
				$originalTimeIn->setTimezone(new DateTimeZone('America/Denver'));	

				$originalTimeOut = new DateTime($model->time_out, new DateTimeZone('America/Chicago'));
				$originalTimeOut->setTimezone(new DateTimeZone('America/Denver'));	

				$originalTimeOut = $model->time_out != null ? $originalTimeOut->format('m/d/Y g:i A') : '';
				
				$model->attributes = $_POST['AccountLoginTracker'];

				$model->time_in = date('Y-m-d', strtotime($_POST['AccountLoginTracker']['time_in_date'])).' '.date('H:i:s', strtotime($_POST['AccountLoginTracker']['time_in_time']));
				$model->time_out = date('Y-m-d', strtotime($_POST['AccountLoginTracker']['time_out_date'])).' '.date('H:i:s', strtotime($_POST['AccountLoginTracker']['time_out_time']));
				
				$timeIn = new DateTime($model->time_in, new DateTimeZone('America/Denver'));
				$timeIn->setTimezone(new DateTimeZone('America/Chicago'));	

				if( $model->type == 1)
				{
					$timeOut = new DateTime($model->time_out, new DateTimeZone('America/Denver'));
					$timeOut->setTimezone(new DateTimeZone('America/Chicago'));		
				}
				else
				{
					$timeOut = new DateTime($model->time_out);
				}	
				
				$model->time_in = $timeIn->format('Y-m-d H:i:s');
				$model->time_out = $timeOut->format('Y-m-d H:i:s');
				
				// $model->type = 2;
				
				if( $model->save(false) )
				{
					$result['status'] = 'success';
					
					$timeIn = new DateTime($model->time_in, new DateTimeZone('America/Chicago'));
					$timeIn->setTimezone(new DateTimeZone('America/Denver'));	

					$timeOut = new DateTime($model->time_out, new DateTimeZone('America/Chicago'));
					$timeOut->setTimezone(new DateTimeZone('America/Denver'));	
					
					$startTime = $timeIn->format('m/d/Y g:i A');
					
					$endTime = $model->time_out != null ? $timeOut->format('m/d/Y g:i A') : '';
					
					$content = 'Update time punch:'; 
					$content.= '<br>';
					$content.= '<b>Time In</b> from '.$originalTimeIn->format('m/d/Y g:i A').' to '.$startTime;
					$content.= '<br>';
					$content.= '<b>Time Out</b> from '.$originalTimeOut.' to '.$endTime;
					
					$audit = new AccountUserNote;
						
					$audit->setAttributes(array(
						'account_id' => Yii::app()->user->account->id,
						'account_user_id' => $accountUser->id,
						'content' => $content,
						'old_data' => json_encode($currentValues),
						'new_data' => json_encode($model->attributes),
						'category_id' => 10,
					));

					$audit->save(false);
				}
			}
			
			$html = $this->renderPartial('ajax_edit_variance_form', array(
				'model' => $model,
			), true);

			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}

	public function actionAddVariance()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);

		$model = new AccountLoginTracker; 
			
		if( isset($_POST['AccountLoginTracker']) )
		{
			$model->attributes = $_POST['AccountLoginTracker'];
			
			$model->account_id = $_POST['account_id'];
			
			$model->time_in = date('Y-m-d', strtotime($_POST['AccountLoginTracker']['time_in_date'])).' '.date('H:i:s', strtotime($_POST['AccountLoginTracker']['time_in_time']));
			$model->time_out = date('Y-m-d', strtotime($_POST['AccountLoginTracker']['time_out_date'])).' '.date('H:i:s', strtotime($_POST['AccountLoginTracker']['time_out_time']));
			
			$timeIn = new DateTime($model->time_in, new DateTimeZone('America/Denver'));
			$timeIn->setTimezone(new DateTimeZone('America/Chicago'));	

			if( $model->type == 1)
			{
				$timeOut = new DateTime($model->time_out, new DateTimeZone('America/Denver'));
				$timeOut->setTimezone(new DateTimeZone('America/Chicago'));		
			}
			else
			{
				$timeOut = new DateTime($model->time_out);
			}	
			
			$model->time_in = $timeIn->format('Y-m-d H:i:s');
			$model->time_out = $timeOut->format('Y-m-d H:i:s');
			
			// $model->type = 2;
			
			if( $model->save(false) )
			{
				$result['status'] = 'success';
				
				$account = Account::model()->findByPk($_POST['account_id']);
				$accountUser = $account->accountUser;
				
				$timeIn = new DateTime($model->time_in, new DateTimeZone('America/Chicago'));
				$timeIn->setTimezone(new DateTimeZone('America/Denver'));	

				$timeOut = new DateTime($model->time_out, new DateTimeZone('America/Chicago'));
				$timeOut->setTimezone(new DateTimeZone('America/Denver'));	
				
				$startTime = $timeIn->format('m/d/Y g:i A');
				
				$endTime = $model->time_out != null ? $timeOut->format('m/d/Y g:i A') : '';
				
				$content = 'Added time punch: '.$startTime.' to '.$endTime;
				
				$audit = new AccountUserNote;
								
				$audit->setAttributes(array(
					'account_id' => Yii::app()->user->account->id,
					'account_user_id' => $accountUser->id,
					'content' => $content,
					'category_id' => 10,
				));

				$audit->save(false);
			}
		}
		
		$html = $this->renderPartial('ajax_add_variance_form', array(
			'model' => $model,
		), true);

		$result['status'] = 'success';
		$result['html'] = $html;
	
		echo json_encode($result);
	}
	
	public function actionDeleteVariance()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		$dbUpdates = 0;
		
		if( isset($_POST['Ids']) )
		{
			$models = AccountLoginTracker::model()->findAll(array(
				'condition' => 'id IN ('.implode(', ', $_POST['Ids']).')',
			));
			
			if( $models )
			{
				foreach( $models as $model )
				{
					$model->status = 4;
					
					if( $model->save(false) )
					{
						$account = Account::model()->findByPk($model->account_id);
						$accountUser = $account->accountUser;
						
						$timeIn = new DateTime($model->time_in, new DateTimeZone('America/Chicago'));
						$timeIn->setTimezone(new DateTimeZone('America/Denver'));	

						$timeOut = new DateTime($model->time_out, new DateTimeZone('America/Chicago'));
						$timeOut->setTimezone(new DateTimeZone('America/Denver'));	
						
						$startTime = $timeIn->format('m/d/Y g:i A');
						
						$endTime = $model->time_out != null ? $timeOut->format('m/d/Y g:i A') : '';
						
						$content = 'Deleted time punch: '.$startTime.' to '.$endTime;
						
						$audit = new AccountUserNote;
										
						$audit->setAttributes(array(
							'account_id' => Yii::app()->user->account->id,
							'account_user_id' => $accountUser->id,
							'content' => $content,
							'category_id' => 10,
						));

						$audit->save(false);
					}
				}
			}
			
			if( $dbUpdates > 0 )
			{
				$result['status'] = 'success';
			}
		}
		
		echo json_encode($result);
	}
	
	//time keeping tab - work schedule
	public function actionWorkSchedule()
	{
		$result = array(
			'status' => 'error',
			'message' => 'No events found.',
			'events' => array(),
		);
		
		$totalScheduledWorkHours = 0;
		
		$startDate = strtotime('monday this week', strtotime($_POST['currentDate']));
		$endDate = strtotime('sunday this week', strtotime($_POST['currentDate']));	
		
		$approvedPtoRequests = AccountPtoRequest::model()->findAll(array(
			'condition' => 'account_id = :account_id AND status=1',
			'params' => array(
				':account_id' => $_POST['account_id'],
			),
		));
		
		$approvedPtoForms = AccountPtoForm::model()->findAll(array(
			'condition' => 'account_id = :account_id AND status=1',
			'params' => array(
				':account_id' => $_POST['account_id'],
			),
		));
		
		$allDayHeaders = array();
		
		$allSchedules = AccountLoginSchedule::model()->findAll(array(
			'condition' => 'account_id = :account_id',
			'params' => array(
				':account_id' => $_POST['account_id'],
			),
		));
	
		foreach( $allSchedules as $schedule )
		{
			if( !in_array($schedule->day_name, $allDayHeaders) )
			{
				$allDayHeaders[] =  $schedule->day_name;
			}
		}
		
		if( $allDayHeaders )
		{
			foreach( $allDayHeaders as $allDayHeader )
			{
				$allDaySchedules = AccountLoginSchedule::model()->findAll(array(
					'condition' => 'account_id = :account_id AND day_name = :day_name AND type=1',
					'params' => array(
						':account_id' => $_POST['account_id'],
						':day_name' => $allDayHeader,
					),
				));
				
				$hours = 0;
				
				if( $allDaySchedules )
				{
					foreach( $allDaySchedules as $allDaySchedule )
					{
						$subtractTime = strtotime($allDaySchedule->end_time) - strtotime($allDaySchedule->start_time);
						
						$hours += floor($subtractTime/3600);
						
						$minutes = round(($subtractTime%3600)/60);
						
						if( $minutes >= 30 )
						{
							$hours += .5;
							
							if( $allDaySchedule->end_time == '11:59 PM' )
							{
								$hours += .5;
							}
						}
					}
				}
				
				$result['events'][]  = array(
					'id' => $allDayHeader,
					'start_date' => date('Y-m-d', strtotime( strtolower($allDayHeader) . ' this week', strtotime($_POST['currentDate']))),
					'end_date' => date('Y-m-d', strtotime(strtolower($allDayHeader). ' this week', strtotime($_POST['currentDate']))),
					'title' => $hours. ' WORK HOURS ',
					'color' => '#82AF6F',
					'is_custom' => 3,
					'allDay' => true,
				);
			}
		}
		
		
		while( $startDate <= $endDate )
		{
			$daySchedules = AccountLoginSchedule::model()->findAll(array(
				'condition' => 'account_id = :account_id AND day_name = :day_name',
				'params' => array(
					':account_id' => $_POST['account_id'],
					':day_name' => date('l', $startDate),
				),
			));
		
			if( $daySchedules )
			{
				foreach( $daySchedules as $daySchedule )
				{
					$subtractTime = strtotime($daySchedule->end_time) - strtotime($daySchedule->start_time);
					$hours = floor($subtractTime/3600);
					
					$minutes = round(($subtractTime%3600)/60);
				
					if( $minutes >= 30 )
					{
						$hours += .5;
						
						if( $daySchedule->end_time == '11:59 PM' )
						{
							$hours += .5;
						}
					}
					
					if( $hours > 0 && $daySchedule->type == 1)
					{
						$totalScheduledWorkHours += $hours;
					}
					
					$start_date = date('Y-m-d', $startDate) .'T'. date('H:i:s', strtotime($daySchedule->start_time));
					$end_date = date('Y-m-d', $startDate) .'T'. date('H:i:s', strtotime($daySchedule->end_time));
					
					$result['events'][] = array(
						'id' => $daySchedule->id,
						'start_date' => $start_date,
						'end_date' => $end_date,
						'title' => $daySchedule->type == AccountLoginSchedule::TYPE_WORK_HOURS ? 'WORK HOURS' : 'LUNCH HOURS',
						'color' => $daySchedule->type == AccountLoginSchedule::TYPE_WORK_HOURS ? '#82AF6F' : '#9585BF',
						'is_custom' => 1,
						'allDay' => false,
					);
				}
			}
			
			$startDate = strtotime('+1 day', $startDate);
		}
		
		//pto requests
		if($approvedPtoRequests)
		{
			foreach( $approvedPtoRequests as $approvedPtoRequest )
			{
				$start_date = date('Y-m-d', strtotime($approvedPtoRequest->request_date)) .'T'. date('H:i:s', strtotime($approvedPtoRequest->start_time));
				$end_date = date('Y-m-d', strtotime($approvedPtoRequest->request_date)) .'T'. date('H:i:s', strtotime($approvedPtoRequest->end_time));
				
				if( $approvedPtoRequest->request_date == $approvedPtoRequest->request_date_end )
				{
					$result['events'][] = array(
						'id' => 'pto-'.$approvedPtoRequest->id,
						'start_date' => $start_date,
						'end_date' => $end_date,
						'title' => $approvedPtoRequest->name,
						'color' => '#D6487E',
						'is_custom' => 2,
						'allDay' => false,
					);
				}
				else
				{
					$ptoStartDate = strtotime($approvedPtoRequest->request_date.' '.$approvedPtoRequest->start_time);
					// $ptoStartDate = strtotime('+1 day', $ptoStartDate);
					
					$ptoEndDate = strtotime($approvedPtoRequest->request_date_end.' '.$approvedPtoRequest->end_time);
					
					$ptoCtr = 1;
					
					while( $ptoStartDate <= $ptoEndDate ) 
					{
						$scheduledDays = array();
						
						$schedules = AccountLoginSchedule::model()->findAll(array(
							'condition' => 'account_id = :account_id AND type=1',
							'params' => array(
								':account_id' => $approvedPtoRequest->account_id,
							),
							'order' => 'date_created ASC',
						));
						
						if( $schedules )
						{
							foreach( $schedules as $schedule )
							{
								$scheduledDays[] = $schedule->day_name;
							}
						}
						
						if( in_array( date('l', $ptoStartDate),  $scheduledDays) )
						{
							$pto_start_date = date('Y-m-d', $ptoStartDate) .'T'. date('H:i:s', strtotime($approvedPtoRequest->start_time));
							$pto_end_date = date('Y-m-d', $ptoStartDate) .'T'. date('H:i:s', strtotime($approvedPtoRequest->end_time));
							
							$result['events'][] = array(
								'id' => 'pto-'.$approvedPtoRequest->id.'-'.$ptoCtr,
								'start_date' => $pto_start_date,
								'end_date' => $pto_end_date,
								'title' => $approvedPtoRequest->name,
								'color' => '#D6487E',
								'is_custom' => 2,
								'allDay' => false,
							);

							$ptoCtr++;
						}
						
						$ptoStartDate = strtotime('+1 day', $ptoStartDate);
					}
				}
			}
		}
		
		//time offs
		if($approvedPtoForms)
		{
			foreach( $approvedPtoForms as $approvedPtoForm )
			{
				if( $approvedPtoForm->is_full_shift == 1 )
				{
					$start_date = date('Y-m-d', strtotime($approvedPtoForm->date_of_request_start)) .'T'. date('08:00:00');
					$end_date = date('Y-m-d', strtotime($approvedPtoForm->date_of_request_end)) .'T'. date('20:00:00');
				} 
				else
				{
					$start_date = date('Y-m-d', strtotime($approvedPtoForm->date_of_request_start)) .'T'. date('H:i:s', strtotime($approvedPtoForm->off_hour_from.':'.$approvedPtoForm->off_min_from.' '.$approvedPtoForm->off_md_from ));
					$end_date = date('Y-m-d', strtotime($approvedPtoForm->date_of_request_end)) .'T'. date('H:i:s', strtotime($approvedPtoForm->off_hour_to.':'.$approvedPtoForm->off_min_to.' '.$approvedPtoForm->off_md_to ));
				}			
				
				if( $approvedPtoForm->date_of_request_start == $approvedPtoForm->date_of_request_end )
				{
					$result['events'][] = array(
						'id' => 'pto-form-'.$approvedPtoForm->id,
						'start_date' => $start_date,
						'end_date' => $end_date,
						'title' => !empty($approvedPtoForm->reason_for_request) ? $approvedPtoForm->reason_for_request : 'TIME OFF REQUEST',
						'color' => '#D6487E',
						'is_custom' => 2,
						'allDay' => false,
					);
				}
				else
				{
					if( $approvedPtoForm->is_full_shift == 1 )
					{
						$ptoStartDate = strtotime($approvedPtoForm->date_of_request_start);
						// $ptoStartDate = strtotime('+1 day', $ptoStartDate);
					
						$ptoEndDate = strtotime($approvedPtoForm->date_of_request_end);
					}
					else
					{
						$ptoStartDate = strtotime($approvedPtoForm->date_of_request_start);

						$ptoEndDate = strtotime($approvedPtoForm->date_of_request_end);
					}
					
					$ptoCtr = 1;
					
					while( $ptoStartDate <= $ptoEndDate ) 
					{
						// $scheduledDays = array();
						
						// $schedules = AccountLoginSchedule::model()->findAll(array(
							// 'condition' => 'account_id = :account_id AND type=1',
							// 'params' => array(
								// ':account_id' => $approvedPtoForm->account_id,
							// ),
							// 'order' => 'date_created ASC',
						// ));
						
						// if( $schedules )
						// {
							// foreach( $schedules as $schedule )
							// {
								// $scheduledDays[] = $schedule->day_name;
							// }
						// }
						
						// if( in_array( date('l', $ptoStartDate),  $scheduledDays) )
						// {
							if( $approvedPtoForm->is_full_shift == 1 )
							{
								$pto_start_date = date('Y-m-d', $ptoStartDate) .'T'. date('H:i:s');
								$pto_end_date = date('Y-m-d', $ptoStartDate) .'T'. date('H:i:s');
							}
							else
							{
								$pto_start_date = date('Y-m-d', $ptoStartDate) .'T'. date('H:i:s', strtotime($approvedPtoForm->off_hour_from.':'.$approvedPtoForm->off_min_from.' '.$approvedPtoForm->off_md_from));
								$pto_end_date = date('Y-m-d', $ptoStartDate) .'T'. date('H:i:s', strtotime($approvedPtoForm->off_hour_to.':'.$approvedPtoForm->off_min_to.' '.$approvedPtoForm->off_md_to));
							}
							
							$result['events'][] = array(
								'id' => 'pto-form-'.$approvedPtoForm->id.'-'.$ptoCtr,
								'start_date' => $pto_start_date,
								'end_date' => $pto_end_date,
								'title' => !empty($approvedPtoForm->reason_for_request) ? $approvedPtoForm->reason_for_request : 'TIME OFF REQUEST',
								'color' => '#D6487E',
								'is_custom' => 2,
								'allDay' => false,
							);

							$ptoCtr++;
						// }
						
						$ptoStartDate = strtotime('+1 day', $ptoStartDate);
					}
				}
			}
		}

		
		if( $result['events'] )
		{
			$result['status'] = 'success';
			$result['message'] = 'Work schedules are successfully loaded.';
		}
		
		$result['total_scheduled_work_hours'] = $totalScheduledWorkHours;
		
		echo json_encode($result);
	}
	
	public function actionAddWorkSchedule()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		if( isset($_POST['account_id']) && $_POST['title'] && $_POST['start_date'] )
		{
			$model = new AccountLoginSchedule;
			
			$day_name = date('l', strtotime($_POST['start_date']));
			$start_time = date('g:i A', strtotime($_POST['start_date']));
			$end_time = date('g:i A', strtotime('+1 Hour', strtotime($start_time)));
			$type = $_POST['title'] == 'WORK HOURS' ? $model::TYPE_WORK_HOURS : $model::TYPE_LUNCH_HOURS;
			
			$model->setAttributes(array(
				'account_id' => $_POST['account_id'],
				'day_name' => $day_name,
				'start_time' => $start_time,
				'end_time' => $end_time,
				'type' => $type,
			));
			
			if( $model->save() )
			{
				$result['status'] = 'success';
				$result['message'] = 'Schedule slot successfully added.';
				
				$account = Account::model()->findByPk($_POST['account_id']);
				$accountUser = $account->accountUser;
				
				$audit = new AccountUserNote;
								
				$audit->setAttributes(array(
					'account_id' => Yii::app()->user->account->id,
					'account_user_id' => $accountUser->id,
					'content' => 'Added work schedule: ' .$day_name.' '.$start_time.' to '.$end_time,
					'category_id' => 10,
				));

				$audit->save(false);
			}
		}
		
		echo json_encode($result);
	}
	
	public function actionUpdateWorkSchedule()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		if( $_POST['event_id'] && $_POST['start_date'] && $_POST['end_date'] )
		{
			$model = AccountLoginSchedule::model()->findByPk($_POST['event_id']);

			$content = 'Old work schedule: ' .date('l', strtotime($model->day_name)).' '.date('g:i A', strtotime($model->start_time)).' to '.date('g:i A', strtotime($model->end_time));
				
			$day_name = date('l', strtotime($_POST['start_date']));
			$start_time = date('g:i A', strtotime($_POST['start_date']));
			$end_time = date('g:i A', strtotime($_POST['end_date']));
			
			if( $end_time == '12:00 AM' && date('Y-m-d', strtotime($_POST['end_date'])) > date('Y-m-d', strtotime($_POST['start_date'])) )
			{
				$end_time = '11:59 PM';
			}
			
			$model->setAttributes(array(
				'day_name' => $day_name,
				'start_time' => $start_time,
				'end_time' => $end_time,
			));
			
			if( $model->save() )
			{
				$result['status'] = 'success';
				$result['message'] = 'Schedule slot successfully added.';
				
				$account = Account::model()->findByPk($model->account_id);
				$accountUser = $account->accountUser;
				
				$content .= '<br>';
				$content .= 'Updated work schedule: ' .$day_name.' '.$start_time.' to '.$end_time;
			
				$audit = new AccountUserNote;
								
				$audit->setAttributes(array(
					'account_id' => Yii::app()->user->account->id,
					'account_user_id' => $accountUser->id,
					'content' => $content,
					'category_id' => 10,
				));

				$audit->save(false);
			}
		}
		
		echo json_encode($result);
	}

	public function actionDeleteWorkSchedule()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		if( isset($_POST['account_id']) && $_POST['event_id'] )
		{
			$model = AccountLoginSchedule::model()->find(array(
				'condition' => 'id = :id AND account_id = :account_id',
				'params' => array(
					':id' => $_POST['event_id'],
					':account_id' => $_POST['account_id'],
				),
			));
			
			$account = Account::model()->findByPk($_POST['account_id']);
			$accountUser = $account->accountUser;
			
			$audit = new AccountUserNote;
							
			$audit->setAttributes(array(
				'account_id' => Yii::app()->user->account->id,
				'account_user_id' => $accountUser->id,
				'content' => 'Deleted work schedule: ' .$day_name.' '.date('g:i A', strtotime($model->start_time)).' to '.date('g:i A', strtotime($model->end_time)),
				'category_id' => 10,
			));
			
			if( $model->delete() )
			{
				$result['status'] = 'success';
				$result['message'] = 'Schedule slot successfully added.';
				
				$audit->save(false);
			}
		}
		
		echo json_encode($result);
	}	

	public function actionAjaxGetTotalLoginHours()
	{
		$result = array(
			'status' => 'error',
			'value' => 0,
		);
		
		if( isset($_POST['ajax']) && isset($_POST['filter']) && isset($_POST['account_id']) )
		{
			$account = Account::model()->findByPk($_POST['account_id']);
			
			if( $account )
			{
				$payPeriodOptions = array();
				
				foreach( range(2015, 2018) as $year)
				{
					foreach( range( $year == 2015 ? 11 : 1 , 12) as $monthNumber )
					{
						$firstDayOfMonth = date('M d', strtotime($year.'-'.$monthNumber.'-01'));
						$fifteenthDayOfMonth = date('M d', strtotime($year.'-'.$monthNumber.'-16'));
						$lastDayOfMonth = date('d', strtotime('last day of this month', strtotime($firstDayOfMonth)));
						
						$payPeriodOptions[] = $firstDayOfMonth.' - 15 '.$year;
						$payPeriodOptions[] = $fifteenthDayOfMonth.' - '.$lastDayOfMonth.' '.$year;
					}
				}
				
				if( $_POST['filter'] != '')
				{
					$filter = $_POST['filter'];
					
					$explodedDilterDate = explode(' - ', $payPeriodOptions[$filter]);
					$explodedElement1 = explode(' ', $explodedDilterDate[0]);
					$explodedElement2 = explode(' ', $explodedDilterDate[1]);

					$month = $explodedElement1[0];
					$start_day = $explodedElement1[1];
					$end_day = $explodedElement2[0];
					$year = $explodedElement2[1];
					
					$startDate =  $year.'-'.$month.'-'.$start_day;
					$endDate =  $year.'-'.$month.'-'.$end_day;
					
					$result['status'] = 'success';
					$result['value'] = $account->getTotalLoginHours($startDate, $endDate);
				}
			}
		}
		
		echo json_encode($result);
	}
	
	
	//assignments tabs
	
	public function actionAssignments()
	{
		$authAccount = Yii::app()->user->account;
		
		$account = $this->loadModel(isset($_POST['id']) ? $_POST['id'] : $_GET['id']);
		
		$accountUser = $account->accountUser;

		$accountLanguages = AccountLanguageAssigned::model()->findAll(array(
			'condition' => 'account_id = :account_id',
			'params' => array(
				':account_id' => $account->id,
			),
		));
		
		#skills
		$accountTrainedSkills = AccountSkillTrained::model()->findAll(array(
			'with' => 'skill',
			'condition' => 't.account_id = :account_id AND skill.status=1 AND skill.is_deleted=0',
			'params' => array(
				':account_id' => $account->id,
			),
		));
		
		$accountAssignedSkills = AccountSkillAssigned::model()->findAll(array(
			'with' => 'skill',
			'condition' => 't.account_id = :account_id AND skill.status=1 AND skill.is_deleted=0',
			'params' => array(
				':account_id' => $account->id,
			),
		));
		
		#skill Child
		$accountTrainedSkillChilds = AccountSkillChildTrained::model()->findAll(array(
			'with' => 'skillChild',
			'condition' => 't.account_id = :account_id AND skillChild.status=1 AND skillChild.is_deleted=0',
			'params' => array(
				':account_id' => $account->id,
			),
		));
		
		$accountAssignedSkillChilds = AccountSkillChildAssigned::model()->findAll(array(
			'with' => 'skillChild',
			'condition' => 't.account_id = :account_id AND skillChild.status=1 AND skillChild.is_deleted=0',
			'params' => array(
				':account_id' => $account->id,
			),
		));
		
		$accountAssignedCompanies = AccountCompanyAssigned::model()->findAll(array(
			'with' => 'company',
			'condition' => 't.account_id = :account_id AND company.status != 3',
			'params' => array(
				':account_id' => $account->id,
			),
		));
		
		$this->render('assignments', array(
			'account' => $account,
			'accountUser' => $accountUser,
			'accountLanguages' => $accountLanguages,
			'accountTrainedSkills' => $accountTrainedSkills,
			'accountAssignedSkills' => $accountAssignedSkills,
			'accountTrainedSkillChilds' => $accountTrainedSkillChilds,
			'accountAssignedSkillChilds' => $accountAssignedSkillChilds,
			'accountAssignedCompanies' => $accountAssignedCompanies,
		));
	}
	
	public function actionUpdateAccountLanguage()
	{
		$result = array(
			'status' => '',
			'message' => '',
		);
		
		if( isset($_POST['ajax']) )
		{
			if( $_POST['type'] == 'add' )
			{
				$model = new AccountLanguageAssigned;
				
				$model->setAttributes(array(
					'account_id' => $_POST['account_id'],
					'language_id' => $_POST['item_id'],
				));
				
				if( $model->save(false) )
				{
					$result['status'] = 'success';
				}
			}
			
			if( $_POST['type'] == 'remove' )
			{
				$model = AccountLanguageAssigned::model()->find(array(
					'condition' => 'account_id = :account_id AND language_id = :language_id',
					'params' => array(
						':account_id' => $_POST['account_id'],
						':language_id' => $_POST['item_id'],
					),
				));
				
				if( $model )
				{
					if( $model->delete() )
					{
						$result['status'] = 'success';
					}
				}
			}
		}
		
		echo json_encode($result);
	}
	
	public function actionUpdateAccountSkill()
	{
		$result = array(
			'status' => '',
			'message' => '',
		);
		
		if( isset($_POST['ajax']) )
		{
			if( $_POST['type'] == 'addTrained' )
			{
				$modelAssigned = AccountSkillAssigned::model()->find(array(
					'condition' => 'account_id = :account_id AND skill_id = :skill_id',
					'params' => array(
						':account_id' => $_POST['account_id'],
						':skill_id' => $_POST['item_id'],
					),
				));
				
				if( $modelAssigned )
				{
					if( $modelAssigned->delete() )
					{
						$result['status'] = 'success';
					}
				}
				
				
				$model = new AccountSkillTrained;
				
				$model->setAttributes(array(
					'account_id' => $_POST['account_id'],
					'skill_id' => $_POST['item_id'],
				));
				
				if( $model->save(false) )
				{
					$result['status'] = 'success';
				}
			}
			
			if( $_POST['type'] == 'addAssigned' )
			{
				$modelTrained = AccountSkillTrained::model()->find(array(
					'condition' => 'account_id = :account_id AND skill_id = :skill_id',
					'params' => array(
						':account_id' => $_POST['account_id'],
						':skill_id' => $_POST['item_id'],
					),
				));
				
				if( $modelTrained )
				{
					if( $modelTrained->delete() )
					{
						$result['status'] = 'success';
					}
				}
				
				$model = new AccountSkillAssigned;
				
				$model->setAttributes(array(
					'account_id' => $_POST['account_id'],
					'skill_id' => $_POST['item_id'],
				));
				
				if( $model->save(false) )
				{
					$result['status'] = 'success';
				}
			}
			
			if( $_POST['type'] == 'remove' )
			{
				$modelAssigned = AccountSkillAssigned::model()->find(array(
					'condition' => 'account_id = :account_id AND skill_id = :skill_id',
					'params' => array(
						':account_id' => $_POST['account_id'],
						':skill_id' => $_POST['item_id'],
					),
				));
				
				if( $modelAssigned )
				{
					if( $modelAssigned->delete() )
					{
						$result['status'] = 'success';
					}
				}
				
				$modelTrained = AccountSkillTrained::model()->find(array(
					'condition' => 'account_id = :account_id AND skill_id = :skill_id',
					'params' => array(
						':account_id' => $_POST['account_id'],
						':skill_id' => $_POST['item_id'],
					),
				));
				
				if( $modelTrained )
				{
					if( $modelTrained->delete() )
					{
						$result['status'] = 'success';
					}
				}
			}
		}
		
		echo json_encode($result);
	}
	
	public function actionUpdateAccountSkillChild()
	{
		$result = array(
			'status' => '',
			'message' => '',
		);
		
		if( isset($_POST['ajax']) )
		{
			if( $_POST['type'] == 'addTrained' )
			{
				$modelAssigned = AccountSkillChildAssigned::model()->find(array(
					'condition' => 'account_id = :account_id AND skill_child_id = :skill_child_id',
					'params' => array(
						':account_id' => $_POST['account_id'],
						':skill_child_id' => $_POST['item_id'],
					),
				));
				
				if( $modelAssigned )
				{
					if( $modelAssigned->delete() )
					{
						$result['status'] = 'success';
					}
				}
				
				
				$model = new AccountSkillChildTrained;
				
				$model->setAttributes(array(
					'account_id' => $_POST['account_id'],
					'skill_child_id' => $_POST['item_id'],
				));
				
				if( $model->save(false) )
				{
					$result['status'] = 'success';
				}
			}
			
			if( $_POST['type'] == 'addAssigned' )
			{
				$modelTrained = AccountSkillChildTrained::model()->find(array(
					'condition' => 'account_id = :account_id AND skill_child_id = :skill_child_id',
					'params' => array(
						':account_id' => $_POST['account_id'],
						':skill_child_id' => $_POST['item_id'],
					),
				));
				
				if( $modelTrained )
				{
					if( $modelTrained->delete() )
					{
						$result['status'] = 'success';
					}
				}
				
				$model = new AccountSkillChildAssigned;
				
				$model->setAttributes(array(
					'account_id' => $_POST['account_id'],
					'skill_child_id' => $_POST['item_id'],
				));
				
				if( $model->save(false) )
				{
					$result['status'] = 'success';
				}
			}
			
			if( $_POST['type'] == 'remove' )
			{
				$modelAssigned = AccountSkillChildAssigned::model()->find(array(
					'condition' => 'account_id = :account_id AND skill_child_id = :skill_child_id',
					'params' => array(
						':account_id' => $_POST['account_id'],
						':skill_child_id' => $_POST['item_id'],
					),
				));
				
				if( $modelAssigned )
				{
					if( $modelAssigned->delete() )
					{
						$result['status'] = 'success';
					}
				}
				
				$modelTrained = AccountSkillChildTrained::model()->find(array(
					'condition' => 'account_id = :account_id AND skill_child_id = :skill_child_id',
					'params' => array(
						':account_id' => $_POST['account_id'],
						':skill_child_id' => $_POST['item_id'],
					),
				));
				
				if( $modelTrained )
				{
					if( $modelTrained->delete() )
					{
						$result['status'] = 'success';
					}
				}
			}
		}
		
		echo json_encode($result);
	}
	
	public function actionUpdateAccountCompany()
	{
		$result = array(
			'status' => '',
			'message' => '',
		);
		
		if( isset($_POST['ajax']) )
		{
			if( $_POST['type'] == 'addAssigned' )
			{
				$model = new AccountCompanyAssigned;
				
				$model->setAttributes(array(
					'account_id' => $_POST['account_id'],
					'company_id' => $_POST['item_id'],
				));
				
				if( $model->save(false) )
				{
					$result['status'] = 'success';
				}
			}
			
			if( $_POST['type'] == 'remove' )
			{
				$modelAssigned = AccountCompanyAssigned::model()->find(array(
					'condition' => 'account_id = :account_id AND company_id = :company_id',
					'params' => array(
						':account_id' => $_POST['account_id'],
						':company_id' => $_POST['item_id'],
					),
				));
				
				if( $modelAssigned )
				{
					if( $modelAssigned->delete() )
					{
						$assignedCustomers = AccountCustomerAssigned::model()->deleteAll(array(
							'condition' => 'account_id = :account_id AND company_id = :company_id',
							'params' => array(
								':account_id' => $_POST['account_id'],
								':company_id' => $modelAssigned->company_id,
							),
						));
						
						$result['status'] = 'success';
					}
				}
			}
		}
		
		echo json_encode($result);
	}
	
	public function actionUpdateAccountCustomer()
	{
		$result = array(
			'status' => '',
			'message' => '',
		);
		
		if( isset($_POST['ajax']) )
		{
			if( $_POST['type'] == 'addAssigned' )
			{
				$customer = Customer::model()->findByPk($_POST['item_id']);
				
				$model = new AccountCustomerAssigned;
				
				$model->setAttributes(array(
					'account_id' => $_POST['account_id'],
					'customer_id' => $_POST['item_id'],
					'company_id' => $customer->company_id,
				));
				
				if( $model->save(false) )
				{
					$result['status'] = 'success';
				}
			}
			
			if( $_POST['type'] == 'remove' )
			{
				$modelAssigned = AccountCustomerAssigned::model()->find(array(
					'condition' => 'account_id = :account_id AND customer_id = :customer_id',
					'params' => array(
						':account_id' => $_POST['account_id'],
						':customer_id' => $_POST['item_id'],
					),
				));
				
				if( $modelAssigned )
				{
					if( $modelAssigned->delete() )
					{
						$result['status'] = 'success';
					}
				}
			}
		}
		
		echo json_encode($result);
	}
	
	public function actionUpdateCustomerList()
	{
		$html = '';
		$html2 = '';
		
		$result = array(
			'status' => '',
			'message' => '',
			'html' => $html,
			'html2' => $html2,
		);
		
		if( isset($_POST['ajax']) )
		{
			$assignedCompanyIds = array();
			
			$assignedCompanies = AccountCompanyAssigned::model()->findAll(array(
				'condition' => 'account_id = :account_id',
				'params' => array(
					':account_id' => $_POST['account_id'],
				),
			));
			
			if( $assignedCompanies )
			{
				foreach( $assignedCompanies as $assignedCompany )
				{
					$assignedCompanyIds[] = $assignedCompany->company_id;
				}
			}
			
			if( $assignedCompanyIds )
			{
				$customers = Customer::model()->findAll(array(
					'with' => 'account',
					'condition' => '
						account.status != 3 
						AND account.is_deleted = 0
						AND t.status != 3
						AND t.is_deleted = 0 
						AND t.company_id IN ('.implode(', ', $assignedCompanyIds).')
					',
				));
				
				if( $customers )
				{
					foreach( $customers as $customer )
					{
						$assignedCustomer = AccountCustomerAssigned::model()->find(array(
							'condition' => 'account_id = :account_id AND customer_id = :customer_id',
							'params' => array(
								':account_id' => $_POST['account_id'],
								':customer_id' => $customer->id,
							),
						));
						
						if( empty($assignedCustomer) )
						{
							$html .= '<li class="ui-state-default" data-id="'.$customer->id.'" >'.$customer->getFullName().'</li>';
						}
					}
				}
			}
			
			$assignedCustomers = AccountCustomerAssigned::model()->findAll(array(
				'condition' => 'account_id = :account_id',
				'params' => array(
					':account_id' => $_POST['account_id'],
				),
			));
			
			if( $assignedCustomers )
			{
				foreach( $assignedCustomers as $assignedCustomer )
				{
					$html2 .= '<li class="ui-state-default" data-id="'.$assignedCustomer->customer->id.'" >'.$assignedCustomer->customer->getFullName().'</li>';
				}
			}
		}
		
		$result['html'] = $html;
		$result['html2'] = $html2;
		
		echo json_encode($result);
	}
	
	public function actionMoveCustomers()
	{
		$result = array(
			'status' => '',
			'message' => '',
		);
		
		if( $_POST['type'] == 'toAssigned' )
		{
			$assignedCompanyIds = array();
			
			$assignedCompanies = AccountCompanyAssigned::model()->findAll(array(
				'condition' => 'account_id = :account_id',
				'params' => array(
					':account_id' => $_POST['account_id'],
				),
			));
			
			if( $assignedCompanies )
			{
				foreach( $assignedCompanies as $assignedCompany )
				{
					$assignedCompanyIds[] = $assignedCompany->company_id;
				}
			}
			
			if( $assignedCompanyIds )
			{
				$customers = Customer::model()->findAll(array(
					'with' => 'account',
					'condition' => '
						account.status != 3 
						AND account.is_deleted = 0
						AND t.status != 3
						AND t.is_deleted = 0 
						AND t.company_id IN ('.implode(', ', $assignedCompanyIds).')
					',
				));
				
				if( $customers )
				{
					foreach( $customers as $customer )
					{
						$assignedCustomer = AccountCustomerAssigned::model()->find(array(
							'condition' => 'account_id = :account_id AND customer_id = :customer_id',
							'params' => array(
								':account_id' => $_POST['account_id'],
								':customer_id' => $customer->id,
							),
						));
						
						if( empty($assignedCustomer) )
						{
							$newAssignedCustomer = new AccountCustomerAssigned;
							
							$newAssignedCustomer->setAttributes(array(
								'account_id' => $_POST['account_id'],
								'customer_id' => $customer->id,
								'company_id' => $customer->company_id,
							));
							
							$newAssignedCustomer->save(false);
						}
					}
				}
			}
		}
		else
		{
			$assignedCustomers = AccountCustomerAssigned::model()->deleteAll(array(
				'condition' => 'account_id = :account_id',
				'params' => array(
					':account_id' => $_POST['account_id'],
				),
			));
		}
	
		echo json_encode($result);
	}
	
	//employee tabs upload handlers
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
		// $fileName = $_FILES['FileUpload']['name']['filename'];
		
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
	
	public function actionDocumentUpload()
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
		// $fileName = $_FILES['FileUpload']['name']['filename'];
		
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
				$document = new AccountUserDocument;
				
				$document->setAttributes(array(
					'account_id' => Yii::app()->user->account->id,
					'account_user_id' => isset($_REQUEST['user_account_id']) ? $_REQUEST['user_account_id'] : null,
					'fileupload_id' => $fileUpload->id,
				));
				
				$document->save(false);
					
				die('{"jsonrpc" : "2.0", "generatedFileUploadId": "'.$fileUpload->id.'", "generatedFilename": "' . $fileName . '", "fileExtension": "' . $manFileExt. '"}');
			}
		}
		
		// Return JSON-RPC response
		die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
	}
	
	
	//performance tab
	public function actionPerformance()
	{
		ini_set('memory_limit', '1024M');
		set_time_limit(0);
		
		$authAccount = Yii::app()->user->account;
		
		$account = $this->loadModel(isset($_POST['id']) ? $_POST['id'] : $_GET['id']);
		
		$accountUser = $account->accountUser;
		
		$addCondition = '';
		$order = 'lch.start_call_time DESC';
		
		if( !empty($_GET['search_query']) )
		{
			$addCondition .= ' AND ( lch.lead_phone_number LIKE "'.$_GET['search_query'].'%"';
			$addCondition .= ' OR CONCAT(ld.first_name , " " , ld.last_name) LIKE "'.$_GET['search_query'].'%" )';
		}
		
		if( !empty($_GET['start_date']) && !empty($_GET['end_date']) )
		{
			$addCondition .= ' AND DATE(lch.start_call_time) >= "'.date('Y-m-d 00:00:00', strtotime($_GET['start_date'])).'"';
			$addCondition .= ' AND DATE(lch.start_call_time) <= "'.date('Y-m-d 23:59:59', strtotime($_GET['end_date'])).'"';
		}
		
		if( isset($_GET['sorter']) )
		{
			if( $_GET['sorter'] == 'date_time' )
			{
				$order = 'lch.start_call_time DESC';
			}
			
			if( $_GET['sorter'] == 'skill' )
			{
				$order = 'sk.skill_name ASC';
			}
			
			if( $_GET['sorter'] == 'customer_name' )
			{
				$order = 'c.lastname ASC';
			}
			
			if( $_GET['sorter'] == 'disposition' )
			{
				$order = 'lch.disposition ASC';
			}
		}
		
		// $models = LeadCallHistory::model()->findAll(array(	
			// 'with' => array('lead', 'list', 'list.skill', 'customer'),
			// 'together' => true,
			// 'condition' => 't.agent_account_id = :agent_account_id' . $addCondition,
			// 'params' => array(
				// ':agent_account_id' => $account->id,
			// ),
			// 'order' => $order,
		// ));
		
		$page = (isset($_GET['page']) ? $_GET['page'] : 1);
		$page = $page > 0 ? $page-1 : 0;
		
		$callsCountSql = "
			SELECT COUNT(lch.id) as total_count
			FROM ud_lead_call_history lch 
			LEFT JOIN ud_lead ld ON ld.id = lch.lead_id
			WHERE lch.`agent_account_id`='".$account->id."'
			AND lch.disposition IS NOT NULL 
			AND lch.status !=4
			".$addCondition."
		";

		$modelsCount = Yii::app()->db->createCommand($callsCountSql)->queryRow();
		
		$callsSql = "
			SELECT
				lch.id,
				sk.skill_name, 
				CONCAT (c.firstname, ' ', c.lastname) AS customer_name,
				ld.id as lead_id,
				lch.lead_phone_number,
				CONCAT(COALESCE(ld.first_name,''), ' ', COALESCE(ld.last_name,'')) as lead_name,
				lch.start_call_time,
				lch.disposition,
				lch.type
			FROM ud_lead_call_history lch 
			LEFT JOIN ud_customer c ON lch.customer_id = c.id
			LEFT JOIN ud_lists ls ON ls.id = lch.list_id
			LEFT JOIN ud_lead ld ON ld.id = lch.lead_id
			LEFT JOIN ud_skill sk ON sk.id = ls.skill_id
			WHERE lch.`agent_account_id`='".$account->id."'
			AND lch.disposition IS NOT NULL 
			AND lch.status !=4
			".$addCondition."
			ORDER BY ".$order."
		";

		$models = Yii::app()->db->createCommand($callsSql)->queryAll();
		
		$dataProvider = new CArrayDataProvider($models, array(
			'pagination' => array(
				'pageSize' => 15,
			),
			'totalItemCount' => $modelsCount['total_count'],
		));
		
		$this->render('performance', array(
			'authAccount' => $authAccount,
			'account' => $account,
			'accountUser' => $accountUser,
			'dataProvider' => $dataProvider,
		));
	}
	
	public function actionAjaxStats()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => '',
		);
		
		if( isset($_POST['ajax']) && !empty($_POST['agent_stat_start_date']) && !empty($_POST['agent_stat_end_date']) && !empty($_POST['agent_account_id']) )
		{
			$skillsSql = '
				SELECT ls.`skill_id`, sk.`skill_name`
				FROM ud_lead_call_history lch 
				LEFT JOIN ud_lists ls ON ls.`id` = lch.`list_id`
				LEFT JOIN ud_skill sk ON sk.`id` = ls.`skill_id`
				WHERE lch.`agent_account_id`="'.$_POST['agent_account_id'].'"
				GROUP BY ls.`skill_id`
			';

			$skills = Yii::app()->db->createCommand($skillsSql)->queryAll();
			
			$html = '';
			
			if( $skills )
			{
				foreach( $skills as $skill )
				{
					$addCondition = '';
					$addCondition2 = '';
					
					if( !empty($_POST['agent_stat_start_date']) && !empty($_POST['agent_stat_end_date']) )
					{
						$addCondition .= ' AND DATE(alt.time_in) >= "'.date('Y-m-d 00:00:00', strtotime($_POST['agent_stat_start_date'])).'"';
						$addCondition .= ' AND DATE(alt.time_in) <= "'.date('Y-m-d 23:59:59', strtotime($_POST['agent_stat_end_date'])).'"';
					}
					else
					{
						$addCondition .= ' AND DATE(alt.time_in) >= "'.date('Y-m-d 00:00:00').'"';
						$addCondition .= ' AND DATE(alt.time_in) <= "'.date('Y-m-d 23:59:59').'"';
					}
					
					if( !empty($_POST['agent_stat_start_date']) && !empty($_POST['agent_stat_end_date']) )
					{
						$addCondition2 .= ' AND DATE(lch.start_call_time) >= "'.date('Y-m-d 00:00:00', strtotime($_POST['agent_stat_start_date'])).'"';
						$addCondition2 .= ' AND DATE(lch.start_call_time) <= "'.date('Y-m-d 23:59:59', strtotime($_POST['agent_stat_end_date'])).'"';
					}
					else
					{
						$addCondition2 .= ' AND DATE(lch.start_call_time) >= "'.date('Y-m-d 00:00:00').'"';
						$addCondition2 .= ' AND DATE(lch.start_call_time) <= "'.date('Y-m-d 23:59:59').'"';	
					}

					$sql = "
						SELECT
						(
							SELECT SUM(
								CASE WHEN time_out IS NOT NULL THEN TIME_TO_SEC(TIMEDIFF(time_out, time_in))/3600
									ELSE TIME_TO_SEC(TIMEDIFF(DATE_SUB(NOW(), INTERVAL 1 HOUR), time_in))/3600 
								END
							)
							FROM ud_account_login_tracker alt
							WHERE alt.account_id = a.`id`
							AND alt.status !=4
							".$addCondition."
						) AS total_hours,
						(
							SELECT COUNT(lch.id) 
							FROM ud_lead_call_history lch
							LEFT JOIN ud_lists uls ON uls.id = lch.list_id
							WHERE lch.agent_account_id = a.`id`															
							AND uls.skill_id ='".$skill['skill_id']."'
							AND lch.status != 4
							".$addCondition2."
						) AS dials,
						(
							SELECT COUNT(lch.id) 
							FROM ud_lead_call_history lch
							LEFT JOIN ud_lists uls ON uls.id = lch.list_id
							WHERE lch.agent_account_id = a.`id`
							AND uls.skill_id ='".$skill['skill_id']."'
							AND lch.disposition='Appointment Set'
							AND lch.status != 4
							AND lch.is_skill_child=0
							".$addCondition2."
						) AS appointments,
						(
							SELECT COUNT(lch.id) 
							FROM ud_lead_call_history lch
							LEFT JOIN ud_lists uls ON uls.id = lch.list_id
							LEFT JOIN ud_skill_disposition sd ON sd.id = lch.disposition_id 
							WHERE lch.agent_account_id = a.`id` 
							AND uls.skill_id ='".$skill['skill_id']."'
							AND sd.is_voice_contact = 1
							AND sd.id IS NOT NULL
							AND lch.status != 4
							".$addCondition2."
						) AS voice_contacts
						FROM ud_account a
						WHERE a.id = '".$_POST['agent_account_id']."'
					";
					
					$stats = Yii::app()->db->createCommand($sql)->queryRow();

					$html .= '<tr>';
					
						$html .= '<td>'.$skill['skill_name'].'</td>';
						
						$html .= '<td class="center">';
							if( $stats['dials'] > 0 )
							{
								$html .= round($stats['total_hours'], 2);
							}
							else
							{
								$html .= 0;
							}
						$html .= '</td>';
						
						$html .= '<td class="center">'.$stats['appointments'].'</td>';
						
						$html .= '<td>';
							if( $stats['appointments'] > 0 && $stats['total_hours'] > 0 )
							{
								$html .= round($stats['appointments'] / $stats['total_hours'], 2);
							}
							else
							{
								
								$html .= 0;
							}
						$html .= '</td>';
						
						$html .= '<td class="center">'.$stats['dials'].'</td>';
						
						$html .= '<td class="center">';
	
							if( $stats['dials'] > 0 && $stats['total_hours'] > 0 )
							{
								$html .= round($stats['dials'] / $stats['total_hours'], 2);
							}
							else
							{
								
								$html .= 0;
							}
						
						$html .= '</td>';
						
						$html .= '<td class="center">';
						
							if( $stats['appointments'] > 0 && $stats['voice_contacts'] > 0 )
							{
								$html .= round($stats['appointments'] / $stats['voice_contacts'], 2) * 100 . '%';
							}
							else
							{
								
								$html .= '0%';
							}
						
						$html .= '</td>';
						
					$html .= '</tr>';
				}
			}
			
			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}
	
	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return AccountUser the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Account::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param AccountUser $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='account-user-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}

	public function actionFileupload($id)
    {
		$account = Account::model()->findByPk($id);
		
		if($account === null)
			throw new CHttpException(404,'The requested page does not exist.');
		
        $model=new Fileupload;  // this is my model related to table
        if(isset($_POST['Fileupload']))
        {
            $rnd = rand(0,9999).strtotime(date("Y-m-d H:i:s"));  // generate random number between 0-9999
            $model->attributes=$_POST['Fileupload'];
 
            $uploadedFile=CUploadedFile::getInstance($model,'original_filename');
            $fileName = "{$rnd}-{$uploadedFile}";  // random number + file name
            $model->generated_filename = $fileName;
 
            if($model->save())
            {
				$imagePath = $model->imagePath.$fileName;
                $uploadedFile->saveAs($imagePath);  // image will uplode to rootDirectory/fileupload/
				
				$thumb=Yii::app()->phpThumb->create($model->imagePath.$fileName);
				$thumb->resize(100,100);
				$thumb->save($model->imagePathThumb.$fileName);
				
				$account->accountUser->fileupload_id = $model->id;
				$account->accountUser->save(false);
				
                $this->redirect(array('/hr/accountUser/update','id'=>$account->id));
            }
        }
		
        $this->render('fileupload',array(
            'model'=>$model,
        ));
    }

	public function actionReleaseLock($id)
	{
		$account=$this->loadModel($id);
		
		$account->login_attempt = 0;
		$account->save(false);
			
		Yii::app()->user->setFlash('success', 'Employee account has been unlocked!');
		$this->redirect(array('accountUser/employeeProfile','id' => $account->id));
	}

	
	public function actionExport()
	{
		$authAccount = Yii::app()->user->account;
		
		$account = $this->loadModel(isset($_POST['id']) ? $_POST['id'] : $_GET['id']);
		
		$accountUser = $account->accountUser;
		
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
			'A' => 'Date & Time',
			'B' => 'Gap',
			'C' => 'Skill',
			'D' => 'Phone Number',
			'E' => 'Lead Name',
			'F' => 'Customer Name',
			'G' => 'Disposition',
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
		
		$addCondition = '';
		$order = 'lch.start_call_time DESC';
		
		if( !empty($_GET['search_query']) )
		{
			$addCondition .= ' AND ( lch.lead_phone_number LIKE "'.$_GET['search_query'].'%"';
			$addCondition .= ' OR CONCAT(ld.first_name , " " , ld.last_name) LIKE "'.$_GET['search_query'].'%" )';
		}
		
		if( !empty($_GET['start_date']) && !empty($_GET['end_date']) )
		{
			$addCondition .= ' AND DATE(lch.start_call_time) >= "'.date('Y-m-d 00:00:00', strtotime($_GET['start_date'])).'"';
			$addCondition .= ' AND DATE(lch.start_call_time) <= "'.date('Y-m-d 23:59:59', strtotime($_GET['end_date'])).'"';
		}
		
		if( isset($_GET['sorter']) )
		{
			if( $_GET['sorter'] == 'date_time' )
			{
				$order = 'lch.start_call_time DESC';
			}
			
			if( $_GET['sorter'] == 'skill' )
			{
				$order = 'sk.skill_name ASC';
			}
			
			if( $_GET['sorter'] == 'customer_name' )
			{
				$order = 'c.lastname ASC';
			}
			
			if( $_GET['sorter'] == 'disposition' )
			{
				$order = 'lch.disposition ASC';
			}
		}
		
		$callsSql = "
			SELECT
				lch.id,
				sk.skill_name, 
				CONCAT (c.firstname, ' ', c.lastname) AS customer_name,
				lch.lead_phone_number,
				CONCAT(ld.first_name, ' ', ld.last_name) as lead_name,
				lch.start_call_time,
				lch.disposition,
				lch.type
			FROM ud_lead_call_history lch 
			LEFT JOIN ud_customer c ON lch.customer_id = c.id
			LEFT JOIN ud_lists ls ON ls.id = lch.list_id
			LEFT JOIN ud_lead ld ON ld.id = lch.lead_id
			LEFT JOIN ud_skill sk ON sk.id = ls.skill_id
			WHERE lch.`agent_account_id`='".$account->id."'
			AND lch.disposition IS NOT NULL 
			AND lch.status !=4
			".$addCondition."
			ORDER BY ".$order."
		";

		$models = Yii::app()->db->createCommand($callsSql)->queryAll();
		
		// echo $callsSql;
		
		// echo '<br><br>';
		
		// echo count($models);
		// exit;
		
		if( $models )
		{
			$ctr = 2;
			
			foreach( $models as $key => $model )
			{
				$gap = '';

				$previousModel = $models[$key-1];
			
				if( $previousModel )
				{
					$to_time = strtotime($model['start_call_time']);
					$from_time = strtotime($previousModel['start_call_time']);
					
					$gap = round(abs($to_time - $from_time) / 60);
				}
				
				$callTime = new DateTime($model['start_call_time'], new DateTimeZone('America/Chicago'));
				$callTime->setTimezone(new DateTimeZone('America/Denver'));	

				$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $callTime->format('m/d/Y g:i A'));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $gap);
				
				if( $gap != '' && $gap > 5 )
				{
					$objPHPExcel->getActiveSheet()
					->getStyle('A'.$ctr.':G'.$ctr)
					->getFill()
					->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
					->getStartColor()
					->setARGB('FFFF00');
				}
				
				$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $model['skill_name']);
				$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, !empty($model['lead_phone_number']) ? "(".substr($model['lead_phone_number'], 0, 3).") ".substr($model['lead_phone_number'], 3, 3)."-".substr($model['lead_phone_number'],6) : '');
				$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $model['lead_name']);
				$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $model['customer_name']);
				$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $model['disposition']);
			
				$ctr++;
			}
		}
		
		date_default_timezone_set('America/Denver');
		$filename  = $accountUser->getFullName() . ' - Call Agent History';
		
		header('Content-Type: application/vnd.ms-excel'); 
		header('Content-Disposition: attachment; filename="'.$filename.'.xlsx"'); 
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	
		header('Cache-Control: max-age=0');
		
		$objWriter->save('php://output');
	}

	public function actionAjaxLeadHistory()
	{
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => $html
		);
		
		if( isset($_POST['ajax']) && isset($_POST['id']) )
		{
			$html = $this->renderPartial('ajax_lead_history', array(''), true);
			
			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}

	function getPrevKey($key, $hash = array())
	{
		$keys = array_keys($hash);
		$found_index = array_search($key, $keys);
		if ($found_index === false || $found_index === 0)
			return false;
		return $keys[$found_index-1];
	}
	
	public function textAccount($mobileNo, $content)
	{
		//simpletexting api
		$url = 'https://app.simpletexting.com/v1/send';
		$apiToken = 'a6fb7969e0e4140d27427afc7e9841d1';
		
		#  $account->accountUser->mobile_number
		$phoneNumber = str_replace('(', '', $mobileNo);
		$phoneNumber = str_replace(')', '', $phoneNumber);
		$phoneNumber = str_replace('-', '', $phoneNumber);
		$phoneNumber = str_replace(' ', '', $phoneNumber);
		
		if( strlen( $phoneNumber ) >= 10 )
		{
			$fields = array(
				'token' => $apiToken,
				'phone' => urlencode($phoneNumber),
				'message' => urlencode($content)
			);
			
			//url-ify the data for the POST
			$fields_string = '';
			
			foreach( $fields as $key => $value ) 
			{ 
				$fields_string .= $key.'='.$value.'&'; 
			}
			
			$fields_string = rtrim($fields_string, '&');

			//open connection
			$ch = curl_init();

			//set the url, number of POST vars, POST data
			curl_setopt($ch,CURLOPT_URL, $url);
			curl_setopt($ch,CURLOPT_POST, count($fields));
			curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
			curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);  
			curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);  

			//execute post
			$result = curl_exec($ch);	
			$jsonObject = json_decode($result);
			
			//close connection
			curl_close($ch);
			
			return $jsonObject;
		}
		
		return false;
	}

	function urlExists($url)
	{
	   $headers = get_headers($url);
	   
	   return stripos($headers[0],"200 OK") ? true : false;
	}
}
