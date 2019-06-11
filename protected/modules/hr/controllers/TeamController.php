<?php

class TeamController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='//layouts/column2';

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
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
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('index', 'create', 'update', 'delete', 'addMember', 'removeMember'),
				'users'=>array('*'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}


	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new Team;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Team']))
		{
			$model->attributes=$_POST['Team'];
			
			if( $model->validate() )
			{
				if( $model->save() )
				{
					Yii::app()->user->setFlash('success', 'Team has been created successfully');
					$this->redirect(array('update','id'=>$model->id));
				}
			}
		}
		
		$currentMemberIds = array();
		
		$members = TeamMember::model()->findAll(array(
			'condition' => 'team_id = :team_id',
			'params' => array(
				':team_id' => $model->id,
			),
		));
		
		if( $members )
		{
			foreach( $members as $member )
			{
				$currentMemberIds[] = $member->account_id;
			}
		}
		
		if( $currentMemberIds )
		{
			$employees = AccountUser::model()->findAll(array(
				'with' => 'account',
				'together' => true,
				'condition' => 'account.status=1 AND account.account_type_id NOT IN (3,5) AND account.is_deleted=0 AND account.id NOT IN('.implode(', ', $currentMemberIds).')',
				'params' => array(),
			));
		}
		else	
		{
			$employees = AccountUser::model()->findAll(array(
				'with' => 'account',
				'together' => true,
				'condition' => 'account.status=1 AND account.account_type_id NOT IN (3,5) AND account.is_deleted=0',
				'params' => array(),
			));
		}

		$this->render('create',array(
			'model'=>$model,
			'members'=>$members,
			'employees'=>$employees,
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

		if(isset($_POST['Team']))
		{
			$model->attributes=$_POST['Team'];
			
			if( $model->validate() )
			{
				if( $model->save() )
				{
					Yii::app()->user->setFlash('success', 'Team has been updated successfully');
					$this->redirect(array('update','id'=>$model->id));
				}
			}
		}
		
		$currentMemberIds = array();
		
		$members = TeamMember::model()->findAll(array(
			'condition' => 'team_id = :team_id',
			'params' => array(
				':team_id' => $model->id,
			),
		));
		
		if( $members )
		{
			foreach( $members as $member )
			{
				$currentMemberIds[] = $member->account_id;
			}
		}
		
		if( $currentMemberIds )
		{
			$employees = AccountUser::model()->findAll(array(
				'with' => 'account',
				'together' => true,
				'condition' => 'account.status=1 AND account.account_type_id NOT IN (3,5) AND account.is_deleted=0 AND account.id NOT IN('.implode(', ', $currentMemberIds).')',
				'params' => array(),
			));
		}
		else	
		{
			$employees = AccountUser::model()->findAll(array(
				'with' => 'account',
				'together' => true,
				'condition' => 'account.status=1 AND account.account_type_id NOT IN (3,5) AND account.is_deleted=0',
				'params' => array(),
			));
		}

		$this->render('update',array(
			'model'=>$model,
			'members'=>$members,
			'employees'=>$employees,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		$model = $this->loadModel($id);

		$model->status = 3;
		
		$model->save(false);
		
		Yii::app()->user->setFlash('success', 'Team has been deleted successfully');
		$this->redirect(array('index'));
	}
	

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$models = Team::model()->findAll(array(
			'condition' => 'status !=3',
		));
		
		$dataProvider=new CArrayDataProvider($models);
		
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}


	
	public function actionAddMember()
	{
		$model = new TeamMember;
		
		$model->setAttributes(array(
			'team_id' => $_POST['team_id'],
			'account_id' => $_POST['account_id'],
		));
		
		$model->save(false);
	}
	
	public function actionRemoveMember()
	{
		$model = TeamMember::model()->find(array(
			'condition' => 'account_id = :account_id AND team_id = :team_id',
			'params' => array(
				':account_id' => $_POST['account_id'],
				':team_id' => $_POST['team_id'],
			),
		));
		
		if( $model )
		{
			$model->delete();
		}
	}
	
	
	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Team the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Team::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Team $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='team-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
