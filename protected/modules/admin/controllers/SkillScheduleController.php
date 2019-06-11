<?php

class SkillScheduleController extends Controller
{
	public function actionUpdate($skill_id)
	{
		$skill = Skill::model()->findByPk($skill_id);
		if($skill===null)
			throw new CHttpException(404,'The requested page does not exist.');
		
		$model = new SkillSchedule;
		$model->skill_id = $skill->id;
		
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);


		if(isset($_POST['SkillSchedule']))
		{ 
			$deleteNotInSkillScheduleIds = array();
			
			if(isset($_POST['SkillSchedule']['schedule_day']))
			{
				
				foreach($_POST['SkillSchedule']['schedule_day'] as $schedule_day => $attributes)
				{
					foreach($attributes as $key => $attribute)
					{
						
						if (strpos($key,'new') !== false) {
							
							$skillSchedule = new SkillSchedule;
							$skillSchedule->skill_id = $skill_id;
							$skillSchedule->schedule_day = $schedule_day;
							$skillSchedule->schedule_start = $attribute['schedule_start'];
							$skillSchedule->schedule_end = $attribute['schedule_end'];
							//$skillSchedule->status = $attribute['status'];
							$skillSchedule->save(false);
							
							$deleteNotInSkillScheduleIds[$skillSchedule->id] = $skillSchedule->id;
						}
						else
						{
							$deleteNotInSkillScheduleIds[$key] = $key;
							
							$skillSchedule = SkillSchedule::model()->find(array(
								'condition'=> 'skill_id = :skill_id AND schedule_day = :schedule_day AND id = :id_key',
								'params'=>array(
									':skill_id' => $skill->id,
									':schedule_day' => $schedule_day,
									':id_key' => $key,
								),
							));
							
							if($skillSchedule === null)
								$skillSchedule = new SkillSchedule;
							
							$skillSchedule->skill_id = $skill_id;
							$skillSchedule->schedule_day = $schedule_day;
							$skillSchedule->schedule_start = $attribute['schedule_start'];
							$skillSchedule->schedule_end = $attribute['schedule_end'];
							// $skillSchedule->status = $attribute['status'];
							
							$skillSchedule->save(false);
						}
					}
				}
			}
			
			if(isset($_POST['SkillSchedule']['skill_id']))
			{
				if(!empty($deleteNotInSkillScheduleIds))
				{
					$criteria = new CDbCriteria;
					$criteria->compare('skill_id',$skill->id);
					$criteria->addNotInCondition('id', $deleteNotInSkillScheduleIds);
					
					$skillScheduleToBeDeleted = SkillSchedule::model()->findAll($criteria);
					
					if(!empty($skillScheduleToBeDeleted))
					{
						foreach($skillScheduleToBeDeleted as $ssd)
						{
							$ssd->delete();
						}
					}
				}
				else if(!isset($_POST['SkillSchedule']['schedule_day']))
				{
					$criteria = new CDbCriteria;
					$criteria->compare('skill_id',$skill->id);
					$skillScheduleToBeDeleted = SkillSchedule::model()->findAll($criteria);
					
					if(!empty($skillScheduleToBeDeleted))
					{
						foreach($skillScheduleToBeDeleted as $ssd)
						{
							$ssd->delete();
						}
					}
				}
			}
			
			$this->redirect(array('update','skill_id'=>$skill->id));
		}

		$this->render('update',array(
			'model'=>$model,
			'skill'=>$skill,
		));
	}
	
	public function actionPeriodAssignment($skill_id)
	{
		$skill = Skill::model()->findByPk($skill_id);
		if($skill===null)
			throw new CHttpException(404,'The requested page does not exist.');
		
		$model = new SkillSchedule;
		$model->skill_id = $skill->id;
		
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['SkillSchedule']))
		{
			foreach($_POST['SkillSchedule']['schedule_day'] as $schedule_day => $attributes)
			{
				foreach($attributes as $key => $attribute)
				{
					
					if (strpos($key,'new') !== false) {
						
						$skillSchedule = new SkillSchedule;
						$skillSchedule->skill_id = $skill_id;
						$skillSchedule->schedule_day = $schedule_day;
						$skillSchedule->schedule_start = $attribute['schedule_start'];
						$skillSchedule->schedule_end = $attribute['schedule_end'];
						$skillSchedule->status = $attribute['status'];
						$skillSchedule->save(false);
						
					}
					else
					{
						$skillSchedule = SkillSchedule::model()->find(array(
							'condition'=> 'skill_id = :skill_id AND schedule_day = :schedule_day AND id = :id_key',
							'params'=>array(
								':skill_id' => $skill->id,
								':schedule_day' => $schedule_day,
								':id_key' => $key,
							),
						));
						
						if($skillSchedule === null)
							$skillSchedule = new SkillSchedule;
						
						$skillSchedule->skill_id = $skill_id;
						$skillSchedule->schedule_day = $schedule_day;
						$skillSchedule->schedule_start = $attribute['schedule_start'];
						$skillSchedule->schedule_end = $attribute['schedule_end'];
						$skillSchedule->status = $attribute['status'];
						
						$skillSchedule->save(false);
					}
				}
			}
			
			$this->redirect(array('periodAssignment','skill_id'=>$skill->id));
		}

		$this->render('periodAssignment',array(
			'model'=>$model,
			'skill'=>$skill,
		));
	}
	
	public function actionAddNewSchedule($day,$ctr, $type = 1)
	{
		$nameCtr = 'new-'.$day.'-'.$ctr;
		
		$view = '_formSchedule';
		
		if($type == 2)
			$view = '_formScheduleAppointment';
		
		
		$this->renderPartial($view,array(
			'model' => new SkillSchedule,
			'day' => $day,
			'skill' => new Skill,
			'name' => $nameCtr,
		));
		
		if(isset($_REQUEST['ajax']))
			Yii::app()->end();
	}
	
	/**
	 * Performs the AJAX validation.
	 * @param SkillSchedule $model the model to be validated
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
