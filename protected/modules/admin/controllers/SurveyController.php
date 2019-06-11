<?php

class SurveyController extends Controller
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
		$model=new Survey;
		
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Survey']))
		{
			$model->attributes=$_POST['Survey'];
			if($model->save())
			{
				Yii::app()->user->setFlash('success', 'Survey has been created successfully!');
				$this->redirect(array('view','id'=>$model->id));
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
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);
		
		
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Survey']))
		{
			// echo '<pre>';
			// print_r($_REQUEST);
			$transaction = Yii::app()->db->beginTransaction();
				
			try{
				
				if(isset($_POST['SurveySkill']))
				{
					$selectedSkill = array();
					foreach($_POST['SurveySkill'] as $key => $skill_id)
					{
						$surveySkill = SurveySkill::model()->find(array(
							'condition'=>'skill_id = :skill_id AND survey_id = :survey_id',
							'params'=>array(
								':skill_id' => $skill_id,
								':survey_id' => $model->id,
							),
						));
				
						if(empty($surveySkill))
						{
							$surveySkill = new SurveySkill;
							$surveySkill->survey_id = $model->id;
							$surveySkill->skill_id = $skill_id;
						}
						
						$surveySkill->is_active = 1;
						if(!$surveySkill->save())
						{
							print_r($surveySkill->getErrors());
							exit;
						}
						// print_r($SurveySkill->attributes);
					}
					
					foreach($model->surveySkills as $surveySkill)
					{
						if(!in_array($surveySkill->skill_id, $_POST['SurveySkill'] ))
						{
							$surveySkill->is_active = 0;
							$surveySkill->save(false);
						}
					}
				}
				
				if(isset($_POST['SurveyCustomer']))
				{
					
					$selectedCustomer = array();
					foreach($_POST['SurveyCustomer'] as $key => $customer_id)
					{
						$surveyCustomer = SurveyCustomer::model()->find(array(
							'condition'=>'customer_id = :customer_id AND survey_id = :survey_id',
							'params'=>array(
								':customer_id' => $customer_id,
								':survey_id' => $model->id,
							),
						));
				
						if(empty($surveyCustomer))
						{
							$surveyCustomer = new SurveyCustomer;
							$surveyCustomer->survey_id = $model->id;
							$surveyCustomer->customer_id = $customer_id;
						}
						
						$surveyCustomer->is_active = 1;
						if(!$surveyCustomer->save())
						{
							print_r($surveyCustomer->getErrors());
							exit;
						}
						// print_r($surveyCustomer->attributes);
					}
					
					foreach($model->surveyCustomers as $surveyCustomer)
					{
						if(!in_array($surveyCustomer->customer_id, $_POST['SurveyCustomer'] ))
						{
							$surveyCustomer->is_active = 0;
							$surveyCustomer->save(false);
						}
					}
				}
				
				$model->attributes=$_POST['Survey'];
				if($model->save())
				{
					$transaction->commit();
					Yii::app()->user->setFlash('success', 'Survey has been updated successfully!');
					$this->redirect(array('view','id'=>$model->id));
				}
			
			}
			catch(Exception $ex){
				$transaction->rollback();
				print_r($ex->getMessage()); exit;
			}
		}

		$surveySkills = $model->surveySkills;
		$surveySkillsArray = array();
	   
	   
	   	if(!empty($surveySkills))
		{
			foreach($surveySkills as $surveySkill)
			{
				if($surveySkill->is_active)
					$surveySkillsArray[] = $surveySkill->skill_id;
			}
		}
		
		$customers = array();
		
		if(!empty($surveySkillsArray))
		{
			$criteria = new CDbCriteria;
			$criteria->with = array('customerSkills');
			$criteria->addCondition('customerSkills.skill_id IN ('.implode(',', $surveySkillsArray).')');
			$customers = Customer::model()->findAll($criteria);
		}
		
		$surveyCustomers = $model->surveyCustomers;
		$surveyCustomersArray = array();
	   
	   
	   	if(!empty($surveyCustomers))
		{
			foreach($surveyCustomers as $surveyCustomer)
			{
				if($surveyCustomer->is_active)
					$surveyCustomersArray[] = $surveyCustomer->customer_id;
			}
		}
		
		
		// print_r($surveySkillsArray); exit;
		$this->render('update',array(
			'model'=>$model,
			'surveySkillsArray'=>$surveySkillsArray,
			'customers'=>$customers,
			'surveyCustomersArray'=>$surveyCustomersArray,
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
		$model=new Survey('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Survey']))
			$model->attributes=$_GET['Survey'];

		$this->renderPartial('_list',array(
			'model'=>$model,
		));
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
		$model=Survey::model()->findByPk($id);
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
		if(isset($_POST['ajax']) && $_POST['ajax']==='survey-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
