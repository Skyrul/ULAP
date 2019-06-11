<?php

class SurveyQuestionController extends Controller
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
				'actions'=>array('view', 'create', 'update', 'delete', 'index', 'admin', 'emailSettings', 'redactorUpload', 'upload', 'deleteEmailAttachment', 'clone'),
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
	public function actionCreate($survey_id)
	{
		
		$survey = Survey::model()->findByPk($survey_id);
		
		if($survey===null)
			throw new CHttpException(404,'The requested page does not exist.');
		
		$model = new SurveyQuestion;
		$model->survey_id = $survey->id;
		
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['SurveyQuestion']))
		{
			$model->attributes=$_POST['SurveyQuestion'];
			
			$model->setInputTypeScenario();
			
			if($model->save())
			{
				Yii::app()->user->setFlash('success','Survey question created successfully!');
				$this->redirect(array('surveyQuestion/update','survey_id'=>$model->survey_id,'id'=>$model->id));
			}
		}

		$ssqList = CHtml::listData(SurveyQuestion::model()->bySurveyId($model->survey_id)->findAll(),'id','survey_question');
		
		$this->render('create',array(
			'skill'=>$skill,
			'model'=>$model,
			'ssqList' => $ssqList
		));
	}
  

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($survey_id, $id)
	{
		$survey = Survey::model()->findByPk($survey_id);
		
		if($survey===null)
			throw new CHttpException(404,'The requested page does not exist.');
		
		$model = $this->loadModel($id);
		
		$scenario = $model->getScenario();
		
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['SurveyQuestion']))
		{
			$model->attributes=$_POST['SurveyQuestion'];
			
			$model->setInputTypeScenario();
			
			if($model->save())
			{
				Yii::app()->user->setFlash('success','Survey question updated successfully!');
				$this->redirect(array('surveyQuestion/update','survey_id'=>$model->survey_id,'id'=>$model->id));
			}
		}

		$ssqList = CHtml::listData(SurveyQuestion::model()->bySurveyId($model->survey_id)->byNotSelf($model->id)->findAll(),'id','survey_question');
		
		$this->render('update',array(
			'skill'=>$skill,
			'model'=>$model,
			'ssqList'=>$ssqList,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id,$survey_id)
	{
		$this->loadModel($id)->delete();

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index','survey_id'=>$survey_id));
	}


	/**
	 * Lists all models.
	 */
	public function actionIndex($survey_id)
	{
		$survey = Survey::model()->findByPk($survey_id);
		
		if($survey === null)
			throw new CHttpException('403', 'Page not found.');
		
		$surveyQuestions = $this->_getSurveyQuestionList()->findAll();
		
		$this->render('index',array(
			'surveyQuestions'=>$surveyQuestions,
			'survey'=>$survey,
		));
	}

	public function _getSurveyQuestionList()
	{
		$model = new SurveyQuestion;
		
		if(!empty($_REQUEST['survey_id']))
		{
			$model->bySurveyId($_REQUEST['survey_id']);
		}
		
		$criteria = new CDbCriteria;
		$criteria->order = 'question_order ASC';
		
		$model->getDbCriteria()->mergeWith($criteria);
		
		return $model;
	}
	
	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model=new SkillDisposition('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['SkillDisposition']))
			$model->attributes=$_GET['SkillDisposition'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return SkillDisposition the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=SurveyQuestion::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param SkillDisposition $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='skill-disposition-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
	
	public function actionGetQuestionPreview($survey_question_id)
	{
		$model = $this->loadModel($survey_question_id);
			
			
		$this->renderPartial('_inputTypePreview',array(
			'model' => $model,
		), false, true);
		
		Yii::app()->end();
	}

	public function actionGetChildQuestion($survey_question_id, $is_child_answer_condition)
	{
		//get survey question for email and go to option
		$model = SurveyQuestion::model()->findByPk($survey_question_id);
		
		$answerScenario = $model->getHtmlOptionsExtraValueForAnswer($is_child_answer_condition);
		if(!empty($answerScenario))
		{
			$answerScenario['checkExtraValue'] = true;
			
			if($answerScenario['extraValue'] == 'goto')
			{
				$answerScenario['question_order_from'] = $model->question_order;
				
				if($model->question_order > $answerScenario['question_order'])
				{
					$answerScenario['extraValue'] = 'undo_goto';
					$answerScenario['question_order_from'] = $answerScenario['question_order'];
					$answerScenario['question_order'] = $model->question_order;
				}
			}
			
			if($answerScenario['extraValue'] == 'email')
			{
				 // $answerScenario['email_address'];
			}
		}
		else
			$answerScenario['checkExtraValue'] = false;
		
		## get the child question
		$model = SurveyQuestion::model()->find(array(
			'condition' => 'is_child_of_id = :ssq_id AND is_child_answer_condition = :icac',
			'params' => array(
				':ssq_id' => $survey_question_id, 
				':icac' => $is_child_answer_condition
			),
		));
			
		if($model !== null)
		{
			$childHtml = $this->renderPartial('_inputTypePreview',array(
				'model' => $model,
			), true, true);
			
			$answerScenario['childHtml'] = $childHtml;
			$answerScenario['checkChildHtml'] = true;
			
		}
		else
			$answerScenario['checkChildHtml'] = false;
		
		echo CJSON::encode($answerScenario);
		Yii::app()->end();
	}
	
	public function actionViewSurveyForm($survey_id)
	{
		$survey = Survey::model()->findByPk($survey_id);
		
		if($survey === null)
			throw new CHttpException('403', 'Page not found.');
		
		$surveyQuestions = $this->_getSurveyQuestionList()->byNotChild()->findAll();
		
		
		$this->render('viewSurveyForm',array(
			'survey' => $survey,
			'surveyQuestions' => $surveyQuestions,			
		));
	}
}
