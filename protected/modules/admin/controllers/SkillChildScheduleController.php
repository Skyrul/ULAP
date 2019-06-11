<?php

class SkillChildScheduleController extends Controller
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
				'actions'=>array('update'),
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
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($skill_child_id)
	{
		$skillChild = SkillChild::model()->findByPk($skill_child_id);
		if($skillChild===null)
			throw new CHttpException(404,'The requested page does not exist.');
		
		$model = new SkillChildSchedule;
		$model->skill_child_id = $skillChild->id;
		
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['SkillChildSchedule']))
		{
			foreach($_POST['SkillChildSchedule']['schedule_day'] as $schedule_day => $attributes)
			{
				$SkillChildSchedule = SkillChildSchedule::model()->find(array(
					'condition'=> 'skill_child_id = :skill_child_id AND schedule_day = :schedule_day',
					'params'=>array(
						':skill_child_id' => $skillChild->id,
						':schedule_day' => $schedule_day,
					),
				));
				
				if($SkillChildSchedule === null)
					$SkillChildSchedule = new SkillChildSchedule;
				
				$SkillChildSchedule->skill_child_id = $skill_child_id;
				$SkillChildSchedule->schedule_day = $schedule_day;
				$SkillChildSchedule->schedule_start = $attributes['schedule_start'];
				$SkillChildSchedule->schedule_end = $attributes['schedule_end'];
				$SkillChildSchedule->status = $attributes['status'];
				
				$SkillChildSchedule->save(false);
			}
			
			$this->redirect(array('update','skill_child_id'=>$skillChild->id));
		}

		$this->render('update',array(
			'model'=>$model,
			'skillChild'=>$skillChild,
		));
	}

	/**
	 * Performs the AJAX validation.
	 * @param SkillChildSchedule $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='skill-schedule-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
