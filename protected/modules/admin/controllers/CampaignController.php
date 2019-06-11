<?php

class CampaignController extends Controller
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
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('view', 'create','update', 'delete', 'index', 'list'),
				'users'=>array('@'),
			),
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
		$model=new Campaign;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Campaign']))
		{
			$model->attributes=$_POST['Campaign'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
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

		if(isset($_POST['Campaign']))
		{
			// echo '<pre>';
			// print_r($_REQUEST);
			$transaction = Yii::app()->db->beginTransaction();
				
			try{
				
				if(isset($_POST['CampaignSkill']))
				{
					$selectedSkill = array();
					foreach($_POST['CampaignSkill'] as $key => $skill_id)
					{
						$campaignSkill = CampaignSkill::model()->find(array(
							'condition'=>'skill_id = :skill_id AND campaign_id = :campaign_id',
							'params'=>array(
								':skill_id' => $skill_id,
								':campaign_id' => $model->id,
							),
						));
				
						if(empty($campaignSkill))
						{
							$campaignSkill = new CampaignSkill;
							$campaignSkill->campaign_id = $model->id;
							$campaignSkill->skill_id = $skill_id;
						}
						
						$campaignSkill->is_active = 1;
						if(!$campaignSkill->save())
						{
							print_r($campaignSkill->getErrors());
							exit;
						}
						// print_r($campaignSkill->attributes);
					}
					
					foreach($model->campaignSkills as $campaignSkill)
					{
						if(!in_array($campaignSkill->skill_id, $_POST['CampaignSkill'] ))
						{
							$campaignSkill->is_active = 0;
							$campaignSkill->save(false);
						}
					}
				}
				
				$model->attributes=$_POST['Campaign'];
				if($model->save())
				{
					$transaction->commit();
					$this->redirect(array('view','id'=>$model->id));
				}
			
			}
			catch(Exception $ex){
				$transaction->rollback();
				print_r($ex->getMessage()); exit;
			}
		}

		$campaignSkills = $model->campaignSkills;
		$campaignSkillsArray = array();
	   
	   
	   	if(!empty($campaignSkills))
		{
			foreach($campaignSkills as $campaignSkill)
			{
				if($campaignSkill->is_active)
					$campaignSkillsArray[] = $campaignSkill->skill_id;
			}
		}
		
		// print_r($campaignSkillsArray); exit;
		$this->render('update',array(
			'model'=>$model,
			'campaignSkillsArray'=>$campaignSkillsArray,
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
		$dataProvider=new CActiveDataProvider('Campaign');
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}

	/**
	 * Manages all models.
	 */
	public function actionList()
	{
		$model=new Campaign('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Campaign']))
			$model->attributes=$_GET['Campaign'];

		$this->renderPartial('_list',array(
			'model'=>$model,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Campaign the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Campaign::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Campaign $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='campaign-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
