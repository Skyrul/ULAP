<?php

class CustomerOfficeController extends Controller
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
				'actions'=>array('view', 'create', 'update', 'delete', 'index', 'list', 'admin'),
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
		$this->redirect(array('update','id' => $id));
		
		// $this->render('view',array(
			// 'model'=>$this->loadModel($id),
		// ));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate($customer_id = null)
	{
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => '',
			'office' => array(),
		);
		
		$authAccount = Yii::app()->user->account;
		
		$model=new CustomerOffice;
		$model->customer_id = isset($_POST['customer_id']) ? $_POST['customer_id'] : $customer_id;
	
		$customer = Customer::model()->findByPk($customer_id);
		
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['CustomerOffice']))
		{
			$model->attributes=$_POST['CustomerOffice'];
			
			if($model->save())
			{
				$history = new CustomerHistory;
				
				$history->setAttributes(array(
					'model_id' => $model->id, 
					'customer_id' => $model->customer_id,
					'user_account_id' => $authAccount->id,
					'page_name' => 'Office',
					'content' => $model->office_name,
					'type' => $history::TYPE_ADDED,
				));

				$history->save(false);
				
				if( isset($_POST['ajax']) )
				{
					$result['status'] = 'success';
					$result['message'] = 'Office was created successfully.';
					
					$result['office'] = array(
						'id' => $model->id,
						'name' => $model->office_name
					);
					
					
					$models = CustomerOffice::model()->findAll(array(
						'condition' => 'customer_id = :customer_id',
						'params' => array(
							':customer_id' => $model->customer_id,
						),
					));
					
					$customer = Customer::model()->findByPk($model->customer_id);
			
					$calendars = Calendar::model()->findAll(array(
						'condition' => 'office_id = :office_id',
						'params' => array(
							':office_id' => $model->id,
						),
					));
					
					$officeStaffs = CustomerOfficeStaff::model()->findAll(array(
						'condition' => 'customer_id = :customer_id AND customer_office_id = :customer_office_id',
						'params' => array(
							':customer_id' => $customer->id,
							':customer_office_id' => $model->id,
						),
					));
					

					$html = $this->renderPartial('ajax_office_tab_content', array(
						'office' => $model,
						'officeStaffs' => $officeStaffs,
						'customer' => $customer,
						'calendars' => $calendars,
						'models' => $models,
					), true);

					$result['html'] = $html;
					
					echo json_encode($result);
					Yii::app()->end();
				}
				else
				{
					$this->redirect(array('view','id'=>$model->id));
				}
			}
		}

		
		if( isset($_POST['ajax']) )
		{
			$html = $this->renderPartial('ajax_create', array(
				'model' => $model,
			), true);
			
			$result['status'] = 'success';
			$result['html'] = $html;
			
			echo json_encode($result);
			Yii::app()->end();
		}
		else
		{
			$this->render('create',array(
				'model'=>$model,
				'customer_id'=>$customer_id,
				'customer'=>$customer,
			));
		}
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id=null)
	{
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => '',
		);
		
		$authAccount = Yii::app()->user->account;
		
		$currentValues = array();
		
		$id = isset($_POST['office_id']) ? $_POST['office_id'] : $id;
		
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['CustomerOffice']))
		{
			$currentValues = $model->attributes;
			
			$model->attributes=$_POST['CustomerOffice'];
			
			$difference = array_diff($model->attributes, $currentValues);
			
			if($model->save())
			{
				if( $difference )
				{
					$updateFields = '';
				
					foreach( $difference as $attributeName => $value)
					{
						$updateFields .= $model->getAttributeLabel($attributeName) .' changed from '.$currentValues[$attributeName].' to '.$value.', ';
					}
					
					$updateFields = rtrim($updateFields, ', ');
					
					$history = new CustomerHistory;
					
					$history->setAttributes(array(
						'model_id' => $model->id, 
						'customer_id' => $model->customer_id,
						'user_account_id' => $authAccount->id,
						'page_name' => 'Office',
						'content' => $updateFields,
						'old_data' => json_encode($currentValues),
						'new_data' => json_encode($model->attributes),
						'type' => $history::TYPE_UPDATED,
					));

					$history->save(false);
				}
				
				
				if( isset($_POST['ajax']) )
				{
					
					$result['status'] = 'success';
					$result['message'] = 'Office was updated successfully.';
					$result['office_name'] = $model->office_name;
					
					echo json_encode($result);
					Yii::app()->end();
				}
				else
				{
					$this->redirect(array('view','id'=>$model->id));
				}
			}
		}

		
		if( isset($_POST['ajax']) )
		{
			$html = $this->renderPartial('ajax_update', array(
				'model' => $model,
			), true);
			
			$result['status'] = 'success';
			$result['html'] = $html;
			
			echo json_encode($result);
			Yii::app()->end();
		}
		else
		{
			$this->render('update',array(
				'model'=>$model,
			));
		}
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
	public function actionIndex($customer_id = null)
	{
		$customer = new Customer;
		
		if(!empty($customer_id))
			$customer = Customer::model()->findByPk($customer_id);
		
		$this->render('index',array(
			'customer_id' => $customer_id,
			'customer' => $customer,
		));
	}
	
	public function actionList()
	{ 
		$model = $this->_getList();
		
		if(isset($_GET['CustomerOffice']))
			$model->attributes=$_GET['CustomerOffice'];
		
		if(isset($_GET['ajaxRequest']) && $_GET['ajaxRequest'] == 1){
            Yii::app()->clientScript->scriptMap['*.js'] = false;
            $this->renderPartial('_list', array('model' => $model), false, true);
        }
        else{
			$this->renderPartial('_list', array('model' => $model));
		}
	}
	
	public function _getList()
	{
		$model=new CustomerOffice('search');
		$model->unsetAttributes();  // clear any default values
		
		if(isset($_REQUEST['customer_id']))
		{
			$model->byCustomerId($_REQUEST['customer_id']);
		}
		
		return $model;
	}
	
	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model=new CustomerOffice('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['CustomerOffice']))
			$model->attributes=$_GET['CustomerOffice'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return CustomerOffice the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=CustomerOffice::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param CustomerOffice $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='customer-office-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
