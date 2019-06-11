<?php

class SkillChildDispositionDetailController extends Controller
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
				'actions'=>array('index', 'view', 'create','update', 'delete'),
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
	public function actionCreate($skill_child_disposition_id)
	{
		$sk = SkillChildDisposition::model()->findByPk($skill_child_disposition_id);
		
		if($sk === null)
			throw new CHttpException(404,'The requested page does not exist.');
		
		$model=new SkillChildDispositionDetail;
		$model->skill_child_disposition_id = $sk->id;
		$model->skill_child_id = $sk->skill_child_id;
		
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['SkillChildDispositionDetail']))
		{
			$model->attributes=$_POST['SkillChildDispositionDetail'];
			if($model->save())
			{
				// $this->redirect(array('index','id'=>$model->id));
				$this->redirect(array('skillChildDispositionDetail/index','skill_child_id' => $model->skill_child_id, 'skill_child_disposition_id'=>$model->skill_child_disposition_id));
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

		if(isset($_POST['SkillChildDispositionDetail']))
		{
			$model->attributes=$_POST['SkillChildDispositionDetail'];
			if($model->save())
			{
				// $this->redirect(array('index','id'=>$model->id));
				$this->redirect(array('skillChildDispositionDetail/index','skill_child_id' => $model->skill_child_id, 'skill_child_disposition_id'=>$model->skill_child_disposition_id));
			}
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
	public function actionDelete($id)
	{
		$skd = $this->loadModel($id);
		
		$skill_child_id = $skd->skill_child_id;
		$skill_child_disposition_id = $skd->skill_child_disposition_id;
		
		$skd->delete();

		$this->redirect(array('skillChildDispositionDetail/index','skill_child_id' => $skill_child_id, 'skill_child_disposition_id'=>$skill_child_disposition_id));
		
		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		// if(!isset($_GET['ajax']))
			// $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
	}

	public function actionIndex($skill_child_disposition_id)
	{
		$skillChildDisposition = SkillChildDisposition::model()->findByPk($skill_child_disposition_id);
		
		if($skillChildDisposition === null)
			throw new CHttpException('403', 'Page not found.');
		
		$skillChildDispositionDetail = $this->_getSkillChildDispositionDetailList();
		
		$dataProvider=new CActiveDataProvider($skillChildDispositionDetail);
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
			'skillChildDisposition'=>$skillChildDisposition,
		));
	}

	public function _getSkillChildDispositionDetailList()
	{
		$model = new SkillChildDispositionDetail;
		
		if(!empty($_REQUEST['skill_child_disposition_id']))
		{
			$model->bySkillChildDispositionId($_REQUEST['skill_child_disposition_id']);
		}
		
		return $model;
	}


	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return SkillDispositionDetail the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=SkillChildDispositionDetail::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param SkillDispositionDetail $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='skill-disposition-detail-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
