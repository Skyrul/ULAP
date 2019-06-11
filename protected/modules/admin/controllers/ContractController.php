<?php

class ContractController extends Controller
{

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
				'actions'=>array('view', 'create', 'update', 'delete', 'index', 'list', 'addGoalVolume', 'addLeadVolume'),
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
		$model=new Contract;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Contract']))
		{
			$this->saveContractWithSubsidyLevels($model);
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

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		
		if(isset($_POST['Contract']))
		{
			$this->saveContractWithSubsidyLevels($model);
		}

		// echo '<pre>'; print_r($model->subsidyLevelArray); exit;
		$this->render('update',array(
			'model'=>$model,
		));
	}

	public function saveContractWithSubsidyLevels($model)
	{
		$model->attributes = $_POST['Contract'];
			
		$transaction=Yii::app()->db->beginTransaction();
		try
		{
			if($model->save())
			{	
				$groupIds = array();
				if(isset($_POST['Contract']['subsidyLevelArray']))
				{
					
					foreach($model->subsidyLevelArray as $type_fulfillment => $subsidyLevels)
					{
						foreach($subsidyLevels as $gId => $subsidyLevel)
						{
							$group_id = 0;
							
							
							$_id = explode('-',$gId);
							
							
							foreach($subsidyLevel as $attribute => $value)
							{
								$contractSubsidyLevel = null;
								
								if(count($_id) == 1)
								{
									$criteria = new CDbCriteria;
									$criteria->compare('group_id', $_id[0]);
									$criteria->compare('column_name', $attribute);
									
									$contractSubsidyLevel = ContractSubsidyLevel::model()->find($criteria);
									
									if(!empty($contractSubsidyLevel))
									{
										$group_id = $contractSubsidyLevel->group_id;
										$groupIds[$group_id] = $group_id;
									}
								}
							
								if($contractSubsidyLevel === null)
								{
									if($attribute == 'id')
										continue;
									
									$contractSubsidyLevel = new ContractSubsidyLevel;
									$contractSubsidyLevel->contract_id = $model->id;
									$contractSubsidyLevel->type = $type_fulfillment;
									$contractSubsidyLevel->group_id = $group_id;
									$contractSubsidyLevel->column_name = $attribute;
								}
								
								$contractSubsidyLevel->column_value = $value;
								
								if(!$contractSubsidyLevel->save(false))
								{
									print_r($contractSubsidyLevel->getErrors()); exit;
								}
								else
								{
									if($group_id == 0)
									{
										$group_id = $contractSubsidyLevel->id;
										$contractSubsidyLevel->group_id = $group_id;
										$contractSubsidyLevel->save(false);
									}
								}
								
								$groupIds[$group_id] = $group_id;
							}
							
						}
					}
					
					//delete subsidy levels
					$criteria = new CDbCriteria;
					$criteria->compare('contract_id', $model->id);
					$criteria->addNotInCondition('group_id', $groupIds);
					$cslDelete = ContractSubsidyLevel::model()->deleteAll($criteria);
				}
				
				if(empty($_POST['Contract']['subsidyLevelArray']))
				{
					//delete subsidy levels
					$criteria = new CDbCriteria;
					$criteria->compare('contract_id', $model->id);
					$cslDelete = ContractSubsidyLevel::model()->deleteAll($criteria);
				}
				
				$transaction->commit();
				// $this->redirect(array('view','id'=>$model->id));
				$this->redirect(array('update','id'=>$model->id));
			}
		}
		catch(Exception $e) 
		{
			$transaction->rollback();
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
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$dataProvider=new CActiveDataProvider('Contract');
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}

	/**
	 * Manages all models.
	 */
	public function actionList()
	{
		$model=new Contract('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Contract']))
			$model->attributes=$_GET['Contract'];

		$this->renderPartial('_list',array(
			'model'=>$model,
		));
	}

	public function actionAddGoalVolume($id = null, $goal = null, $amount = null, $type = null, $subsidy = null)
	{
		$subsidyLevel['group_id'] = $subsidyLevel['id'] = $id;
		$subsidyLevel['goal'] = $goal;
		$subsidyLevel['amount'] = $amount;
		$subsidyLevel['type'] = $type;
		$subsidyLevel['subsidy'] = $subsidy;
		
		$this->renderPartial('_goalVolume',array('subsidyLevel' => $subsidyLevel));
		
		Yii::app()->end();
	}
	
	public function actionAddLeadVolume($id = null, $low = null, $high = null, $amount = null, $type = null, $subsidy = null)
	{
		//Contract::TYPE_FULFILLMENT_LEAD_VOLUME
		$subsidyLevel['group_id'] = $subsidyLevel['id'] = $id;
		$subsidyLevel['low'] = $low;
		$subsidyLevel['high'] = $high;
		$subsidyLevel['amount'] = $amount;
		$subsidyLevel['type'] = $type;
		$subsidyLevel['subsidy'] = $subsidy;
		
		$this->renderPartial('_leadVolume',array('subsidyLevel' => $subsidyLevel));
	}
			
	
	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Contract the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Contract::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Contract $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='contract-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
