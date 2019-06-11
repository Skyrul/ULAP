<?php 

class StateScheduleController extends Controller
{
	
	public function actionIndex()
	{	
		$states = State::model()->findAll(array(
			'order' => 'name ASC'
		));
		
		$this->render('index', array(
			'states' => $states
		));
	}
	
	public function actionUpdate($state_id)
	{
		$state = State::model()->findByPk($state_id);
		if($state===null)
			throw new CHttpException(404,'The requested page does not exist.');
		
		$model = new StateSchedule;
		$model->state_id = $state->id;
		
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);


		if(isset($_POST['StateSchedule']))
		{ 
			$deleteNotInStateScheduleIds = array();
			
			if(isset($_POST['StateSchedule']['schedule_day']))
			{
				
				foreach($_POST['StateSchedule']['schedule_day'] as $schedule_day => $attributes)
				{
					foreach($attributes as $key => $attribute)
					{
						
						if (strpos($key,'new') !== false) {
							
							$stateSchedule = new StateSchedule;
							$stateSchedule->state_id = $state_id;
							$stateSchedule->schedule_day = $schedule_day;
							$stateSchedule->schedule_start = $attribute['schedule_start'];
							$stateSchedule->schedule_end = $attribute['schedule_end'];
							//$stateSchedule->status = $attribute['status'];
							$stateSchedule->save(false);
							
							$deleteNotInStateScheduleIds[$stateSchedule->id] = $stateSchedule->id;
						}
						else
						{
							$deleteNotInStateScheduleIds[$key] = $key;
							
							$stateSchedule = StateSchedule::model()->find(array(
								'condition'=> 'state_id = :state_id AND schedule_day = :schedule_day AND id = :id_key',
								'params'=>array(
									':state_id' => $state->id,
									':schedule_day' => $schedule_day,
									':id_key' => $key,
								),
							));
							
							if($stateSchedule === null)
								$stateSchedule = new StateSchedule;
							
							$stateSchedule->state_id = $state_id;
							$stateSchedule->schedule_day = $schedule_day;
							$stateSchedule->schedule_start = $attribute['schedule_start'];
							$stateSchedule->schedule_end = $attribute['schedule_end'];
							// $stateSchedule->status = $attribute['status'];
							
							$stateSchedule->save(false);
						}
					}
				}
			}
			
			if(isset($_POST['StateSchedule']['state_id']))
			{
				if(!empty($deleteNotInStateScheduleIds))
				{
					$criteria = new CDbCriteria;
					$criteria->compare('state_id',$state->id);
					$criteria->addNotInCondition('id', $deleteNotInStateScheduleIds);
					
					$stateScheduleToBeDeleted = StateSchedule::model()->findAll($criteria);
					
					if(!empty($stateScheduleToBeDeleted))
					{
						foreach($stateScheduleToBeDeleted as $ssd)
						{
							$ssd->delete();
						}
					}
				}
				else if(!isset($_POST['StateSchedule']['schedule_day']))
				{
					$criteria = new CDbCriteria;
					$criteria->compare('state_id',$state->id);
					$stateScheduleToBeDeleted = StateSchedule::model()->findAll($criteria);
					
					if(!empty($stateScheduleToBeDeleted))
					{
						foreach($stateScheduleToBeDeleted as $ssd)
						{
							$ssd->delete();
						}
					}
				}
			}
			
			$this->redirect(array('update','state_id'=>$state->id));
		}

		$this->render('update',array(
			'model'=>$model,
			'state'=>$state,
		));
	}
	
	public function actionPeriodAssignment($state_id)
	{
		$state = State::model()->findByPk($state_id);
		if($state===null)
			throw new CHttpException(404,'The requested page does not exist.');
		
		$model = new StateSchedule;
		$model->state_id = $state->id;
		
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['StateSchedule']))
		{
			foreach($_POST['StateSchedule']['schedule_day'] as $schedule_day => $attributes)
			{
				foreach($attributes as $key => $attribute)
				{
					
					if (strpos($key,'new') !== false) {
						
						$stateSchedule = new StateSchedule;
						$stateSchedule->state_id = $state_id;
						$stateSchedule->schedule_day = $schedule_day;
						$stateSchedule->schedule_start = $attribute['schedule_start'];
						$stateSchedule->schedule_end = $attribute['schedule_end'];
						$stateSchedule->status = $attribute['status'];
						$stateSchedule->save(false);
						
					}
					else
					{
						$stateSchedule = StateSchedule::model()->find(array(
							'condition'=> 'state_id = :state_id AND schedule_day = :schedule_day AND id = :id_key',
							'params'=>array(
								':state_id' => $state->id,
								':schedule_day' => $schedule_day,
								':id_key' => $key,
							),
						));
						
						if($stateSchedule === null)
							$stateSchedule = new StateSchedule;
						
						$stateSchedule->state_id = $state_id;
						$stateSchedule->schedule_day = $schedule_day;
						$stateSchedule->schedule_start = $attribute['schedule_start'];
						$stateSchedule->schedule_end = $attribute['schedule_end'];
						$stateSchedule->status = $attribute['status'];
						
						$stateSchedule->save(false);
					}
				}
			}
			
			$this->redirect(array('periodAssignment','state_id'=>$state->id));
		}

		$this->render('periodAssignment',array(
			'model'=>$model,
			'state'=>$state,
		));
	}
	
	public function actionAddNewSchedule($day,$ctr, $type = 1)
	{
		$nameCtr = 'new-'.$day.'-'.$ctr;
		
		$view = '_formSchedule';
		
		if($type == 2)
			$view = '_formScheduleAppointment';
		
		
		$this->renderPartial($view,array(
			'model' => new StateSchedule,
			'day' => $day,
			'state' => new State,
			'name' => $nameCtr,
		));
		
		if(isset($_REQUEST['ajax']))
			Yii::app()->end();
	}
	
	/**
	 * Performs the AJAX validation.
	 * @param StateSchedule $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='state-schedule-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}

?>