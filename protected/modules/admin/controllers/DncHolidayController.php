<?php 

class DncHolidayController extends Controller
{
	
	public function actionIndex()
	{
		$this->redirect(array('dncHoliday/federalHolidays'));
		exit;
		
		// $federalHolidays = DncHolidayFederal::model()->findAll(array(
			// 'condition' => 'status = 1',
			// 'order' => 'date ASC',
		// ));
		
		// $federalHolidayDataProvider = new CArrayDataProvider($federalHolidays, array(
			// 'pagination' => array(
				// 'pageSize' => 100,
			// ),
		// ));
		
		// $stateHolidays = DncHolidayState::model()->findAll(array(
			// 'condition' => 'status = 1',
			// 'order' => 'date ASC',
			// 'order' => 'state, name ASC',
		// ));

		// $stateHolidayDataProvider = new CArrayDataProvider($stateHolidays, array(
			// 'pagination' => array(
				// 'pageSize' => 100,
			// ),
		// ));
	
		
		// $this->render('index', array(
			// 'federalHolidayDataProvider' => $federalHolidayDataProvider,
			// 'stateHolidayDataProvider' => $stateHolidayDataProvider
		// ));
	}
	
	public function actionFederalholidays()
	{
		$models = DncHolidayFederal::model()->findAll(array(
			'condition' => 'status = 1',
			'order' => 'date ASC',
		));
		
		$this->render('federalHolidays', array(
			'models' => $models,
		));
	}
	
	public function actionAddFederalHoliday()
	{
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => $html
		);
		

		$model = new DncHolidayFederal;
		
		if( isset($_POST['DncHolidayFederal']) )
		{
			$result['status'] = 'success';
			
			$model->attributes = $_POST['DncHolidayFederal'];
			
			$model->date = date('Y-m-d', strtotime($model->date));
			
			if( $model->save(false) )
			{
				$result['status'] = 'success';
				$result['message'] = $model->name . ' was added successfully.';
				
				$models = DncHolidayFederal::model()->findAll(array(
					'condition' => 'status = 1',
					'order' => 'date ASC',
				));
				
				$html = $this->renderPartial('ajax_federal_holidays_list', array(
					'models' => $models,
				), true);

				$result['html'] = $html;
			}
			else
			{
				// echo '<pre>';
				// print_r($model->getErrors());
				// exit;
				
				$result['status'] = 'error';
				$result['message'] = 'Database error.';
			}
			
			echo json_encode($result);
			Yii::app()->end();
		}
		else
		{
			$html = $this->renderPartial('addFederalHoliday', array(
				'model' => $model
			), true);
			
			$result['html'] = $html;
		}
			
		echo json_encode($result);
	}

	public function actionEditFederalHoliday()
	{
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => $html,
			'updated_name' => '',
		);
		
		if( isset($_POST['ajax']) )
		{
			$result['status'] = 'success';
			
			$model = DncHolidayFederal::model()->findByPk($_POST['id']);
			$model->date = date('m/d/Y', strtotime($model->date));
			
			if( isset($_POST['DncHolidayFederal']) )
			{
				$model->attributes = $_POST['DncHolidayFederal'];
				
				$model->date = date('Y-m-d', strtotime($model->date));
				
				if( $model->save(false) )
				{
					$result['status'] = 'success';
					$result['message'] = $model->name . ' was updated successfully.';
					$result['updated_name'] = $model->name; 
				}
				else
				{
					// echo '<pre>';
					// print_r($model->getErrors());
					// exit;
					
					$result['status'] = 'error';
					$result['message'] = 'Database error.';
				}
				
				echo json_encode($result);
				Yii::app()->end();
			}
			
			$html = $this->renderPartial('editFederalHoliday', array(
				'model' => $model
			), true);
			
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}
	
	public function actionDeleteFederalHoliday()
	{
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => $html
		);
		
		if( isset($_POST['ajax']) )
		{
			$model = DncHolidayFederal::model()->findByPk($_POST['id']);
			
			if( $model && $model->delete() )
			{
				$result['status'] = 'success';
			}
			
			echo json_encode($result);
			Yii::app()->end();
		}
	
	}
	
	
	//start of state holiday actions
	public function actionStateholidays()
	{
		$models = DncHolidayState::model()->findAll(array(
			'condition' => 'status = 1',
			'order' => 'state, name ASC',
		));
		
		$this->render('stateHolidays', array(
			'models' => $models,
		));
	}
	
	public function actionAddStateHoliday()
	{
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => $html
		);

		$model = new DncHolidayState;
		
		if( isset($_POST['DncHolidayState']) )
		{
			$result['status'] = 'success';
			
			$model->attributes = $_POST['DncHolidayState'];
			
			$model->date = date('Y-m-d', strtotime($model->date));
			
			if( $model->save(false) )
			{
				$result['status'] = 'success';
				$result['message'] = $model->name . ' was added successfully.';
				
				$models = DncHolidayState::model()->findAll(array(
					'condition' => 'status = 1',
					'order' => 'state, name ASC',
				));
				
				$html = $this->renderPartial('ajax_state_holidays_list', array(
					'models' => $models,
				), true);

				$result['html'] = $html;
			}
			else
			{
				// echo '<pre>';
				// print_r($model->getErrors());
				// exit;
				
				$result['status'] = 'error';
				$result['message'] = 'Database error.';
			}
			
			echo json_encode($result);
			Yii::app()->end();
		}
		else
		{
			$html = $this->renderPartial('addStateHoliday', array(
				'model' => $model
			), true);
			
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}

	public function actionEditStateHoliday()
	{
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => $html,
			'updated_name' => '',
		);
		
		if( isset($_POST['ajax']) )
		{
			$result['status'] = 'success';
			
			$model = DncHolidayState::model()->findByPk($_POST['id']);
			$model->date = date('m/d/Y', strtotime($model->date));
			
			if( isset($_POST['DncHolidayState']) )
			{
				$model->attributes = $_POST['DncHolidayState'];
				
				$model->date = date('Y-m-d', strtotime($model->date));
				
				if( $model->save(false) )
				{
					$result['status'] = 'success';
					$result['message'] = $model->name . ' was updated successfully.';
					$result['updated_name'] = $model->name;
				}
				else
				{
					// echo '<pre>';
					// print_r($model->getErrors());
					// exit;
					
					$result['status'] = 'error';
					$result['message'] = 'Database error.';
				}
				
				echo json_encode($result);
				Yii::app()->end();
			}
			
			$html = $this->renderPartial('editStateHoliday', array(
				'model' => $model
			), true);
			
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}

	public function actionDeleteStateHoliday()
	{
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => $html
		);
		
		if( isset($_POST['ajax']) )
		{
			$model = DncHolidayState::model()->findByPk($_POST['id']);
			
			if( $model && $model->delete() )
			{
				$result['status'] = 'success';
			}
			
			echo json_encode($result);
			Yii::app()->end();
		}
	
	}
}

?>