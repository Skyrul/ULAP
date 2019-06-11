<?php 


class PromosController extends Controller
{
	
	public function actionIndex()
	{
		$this->render('index',array(
		));
	}
	
	public function actionList()
	{
		$model=new Promo('search');
		$model->is_deleted = 0;
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Promo']))
			$model->attributes=$_GET['Promo'];

		$this->renderPartial('_list',array(
			'model'=>$model,
		));
	}
	
	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new Promo;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Promo']))
		{
			$model->attributes=$_POST['Promo'];
			if($model->save())
			{
				$this->redirect(array('index'));
			// $this->redirect(array('update','id'=>$model->id));
			}
		}

		$this->render('create',array(
			'model'=>$model,
		));
	}
	
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Promo']))
		{
			$model->attributes=$_POST['Promo'];
			if($model->save())
			{
				$this->redirect(array('index'));
				// $this->redirect(array('update','id'=>$model->id));
			}
		}
		
		$this->render('update',array(
			'model'=>$model,
		));
		
	}
	
	public function loadModel($id)
	{
		$model=Promo::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	public function actionDelete($id)
	{
		$model = $this->loadModel($id);
		$model->is_deleted = 1;
		$model->save(false);

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
	}
}

?>