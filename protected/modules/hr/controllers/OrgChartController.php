<?php 

class OrgChartController extends Controller
{
	public $layout = '//layouts/column_no_component_sidebar';
	
	public function accessRules()
	{
		return array(
			// array('allow',  // allow all users to perform 'index' and 'view' actions
				// 'actions'=>array('index','view'),
				// 'users'=>array('*'),
			// ),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array(
					'index', 'create', 'delete', 'updatePositionOrder',
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
	
	public function actionIndex()
	{
		$models = Position::model()->findAll(array(
			'with' => 'account',
			'condition' => 'parent_id IS NULL AND account.status=1',
			'order' => 't.order ASC',
		));
		
		$this->render('index', array(
			'models' => $models,
		));	
	}
	
	
	public function actionCreate()
	{
		$model = new Position;
		$fileupload = new Fileupload;  // this is my model related to table
		
		if( isset($_POST['Position']) )
		{	
			$model->attributes = $_POST['Position'];
			
			if( $model->validate() )
			{
				if( $model->save() )
				{
					Yii::app()->user->setFlash('success', 'Position is successfully added.');
					$this->redirect(array('index'));
				}
			}
		}
		
		$this->render('create', array(
			'model' => $model,
			'fileupload' => $fileupload,
		));
	}

	
	public function actionDelete($id)
	{
		$model = Position::model()->findByPk($id);

		if( $model )
		{
			$positionId = $model->id;
			
			if( $model->delete() )
			{
				Position::model()->updateAll(array('parent_id' => null), 'parent_id ='.$positionId);				
				
				Yii::app()->user->setFlash('success', 'Position was successfully deleted.');
			}
		}
		
		$this->redirect(array('index'));
	}


	public function actionUpdatePositionOrder()
	{
		if( isset($_POST['items']) )
		{
			foreach( $_POST['items'] as $item )
			{
				$model = Position::model()->findByPk($item['id']);
				
				if( !empty($item['children']) )
				{
					$this->getChildPositions($item['id'], $item['children']);
				}
			}
		}	
	}
	
	public function getChildPositions($parentId, $children)
	{
		foreach( $children as $item )
		{
			$model = Position::model()->findByPk($item['id']);

			$model->parent_id = $parentId;
			$model->save(false);
			
			if( !empty($item['children']) )
			{
				$this->getChildPositions($item['id'], $item['children']);
			}
		}
	}
}

?>