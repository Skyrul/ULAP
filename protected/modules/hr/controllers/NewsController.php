<?php 

class NewsController extends Controller
{
	
	public function actionIndex()
	{
		$models = News::model()->findAll(array(
			'condition' => 'status != 3',
			'order' => 'date_created DESC'
		));

		$dataProvider = new CArrayDataProvider($models, array(
			'pagination' => array(
				'pageSize' => 50
			),
		));
		 
		$this->render('index', array(
			'dataProvider' => $dataProvider
		));
		
	}
	
	public function actionCreate()
	{
		$authAccount = Yii::app()->user->account;
		
		$model = new News;
		$model->sort_order = 1;
		
		if( isset($_POST['News']) )
		{
			$model->attributes = $_POST['News'];
			$model->account_id = $authAccount->id;
			
			if( $model->validate() )
			{
				if( $model->save() )
				{
					$otherModels = News::model()->findAll(array(
						'condition' => 'status != 3 AND sort_order >= :sort_order',
						'params' => array(
							':sort_order' => $model->sort_order
						),
						'order' => 'date_created DESC'
					));
					
					if( $otherModels )
					{
						foreach( $otherModels as $otherModel )
						{
							$otherModel->sort_order = $otherModel->sort_order + 1;
							$otherModel->save(false);
						}
					}
					
					Yii::app()->user->setFlash('success', 'News has been created successfully.');
					$this->redirect(array('/hr/news'));
				}
			}
		}
		
		$this->render('create', array(
			'model' => $model
		));
	}
	
	public function actionUpdate($id)
	{
		$authAccount = Yii::app()->user->account;
		
		$model = News::model()->findByPk($id);
		
		if( isset($_POST['News']) )
		{
			$model->attributes = $_POST['News'];
			
			if( $model->validate() )
			{
				if( $model->save() )
				{
					if( $model->sort_order == 1 )
					{
						$otherModels = News::model()->findAll(array(
							'condition' => 'id != :id AND status != 3 AND sort_order >= :sort_order',
							'params' => array(
								':id' => $model->id,
								':sort_order' => $model->sort_order
							),
							'order' => 'date_created DESC'
						));
						
						if( $otherModels )
						{
							foreach( $otherModels as $otherModel )
							{
								$otherModel->sort_order = $otherModel->sort_order + 1;
								$otherModel->save(false);
							}
						}
					}
					
					Yii::app()->user->setFlash('success', 'News has been updated successfully.');
					$this->redirect(array('/hr/news'));
				}
			}
		}
		
		$this->render('update', array(
			'model' => $model
		));
	}
	
	public function actionDelete($id)
	{
		$model = News::model()->findByPk($id);
		
		if( $model )
		{
			$model->status = 3;
			
			if( $model->save() )
			{
				Yii::app()->user->setFlash('success', 'News has been deleted successfully.');
			}
		}
		
		$this->redirect(array('/hr/news'));
	}
	
	public function actionRedactorUpload()
	{
		if( $_FILES )
		{
			$dir  = Yii::getPathOfAlias('webroot') . '/fileupload/';
			$baseUrl  = Yii::app()->request->baseUrl . '/fileupload/';
			
			$fileExtension = strtolower( pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION) );
	 
			if ( in_array( $fileExtension, array('jpg', 'jpeg', 'pjpeg', 'png', 'gif')) )
			{
				// setting file's mysterious name
				// $filename = md5(date('YmdHis')).'.'.$fileExtension;
				$filename = $_FILES['file']['name'];
			 
				// copying
				move_uploaded_file($_FILES['file']['tmp_name'], $dir . $filename);
			 
				// displaying file
				echo json_encode(array('filelink' => $baseUrl . $filename));
			}
		}
	}

	public function actionAjaxSaveOrder()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		if( isset($_POST['ajax']) && isset($_POST['id']) && isset($_POST['order']) )
		{
			$model = News::model()->findByPk($_POST['id']);
			
			if( $model )
			{
				$model->sort_order = $_POST['order'];
				
				if( $model->save(false) )
				{
					$result['status'] = 'success';
					$result['message'] = 'Changes was successfully saved.';
				}
			}
		}
		
		echo json_encode($result);
	}
}

?>