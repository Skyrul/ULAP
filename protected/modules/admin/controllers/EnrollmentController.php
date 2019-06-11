<?php 

class EnrollmentController extends Controller
{
	
	public function actionIndex()
	{
		$models = CompanyEnrollment::model()->findAll(array(
			'condition' => 'status != 3',
		));
		
		$this->render('index', array(
			'models' => $models,
		));
	}
	
	public function actionCreate()
	{
		$model = new CompanyEnrollment;
		
		if( isset($_POST['CompanyEnrollment']) )
		{
			$model->attributes = $_POST['CompanyEnrollment'];

			if( $model->validate() )
			{
				if( $model->save() )
				{
					$status = 'success';
					$message = 'Enrollment content was successfully added.';
				}
				else
				{
					$status = 'danger';
					$message = 'Database error.';
				}
				
				Yii::app()->user->setFlash($status, $message);
				$this->redirect(array('enrollment/index'));
			}
		}
		
		$this->render('create', array(
			'model' => $model,
		));
	}
	
	public function actionUpdate($id)
	{
		$model = CompanyEnrollment::model()->findByPk($id);
		
		if( isset($_POST['CompanyEnrollment']) )
		{
			$model->attributes = $_POST['CompanyEnrollment'];
			
			if( $model->validate() )
			{
				if( $model->save() )
				{
					$status = 'success';
					$message = 'Enrollment content was successfully updated.';
				}
				else
				{
					$status = 'danger';
					$message = 'Database error.';
				}
				
				Yii::app()->user->setFlash($status, $message);
				$this->redirect(array('enrollment/index'));
			}
		}
		
		$this->render('update', array(
			'model' => $model,
		));
	}

	public function actionDelete($id)
	{
		$model = CompanyEnrollment::model()->findByPk($id);
		
		if( $model )
		{
			$model->status = 3;
			
			if( $model->save() )
			{
				$status = 'success';
				$message = 'Enrollment content was successfully added.';
			}
			else
			{
				$status = 'danger';
				$message = 'Database error.';
			}
		}
		else
		{
			$status = 'danger';
			$message = 'Database error.';
		}
		
		Yii::app()->user->setFlash($status, $message);
		$this->redirect(array('enrollment/index'));
	}
}

?>