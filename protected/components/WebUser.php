<?php

class WebUser extends CWebUser
{
	private $_model;
	
	function getAccount()
	{
		$model = $this->loadAccount(Yii::app()->user->id);
		
		if ($model === null && !Yii::app()->user->isGuest)
		{
			Yii::app()->user->logout();
			Yii::app()->controller->redirect(Yii::app()->homeUrl);
			Yii::app()->end();
		}
		
		return $model;
	}
	
	protected function loadAccount($id = null)
	{
		if ($this->_model === null)
		{
			if ($id !== null)
			{
				$this->_model = Account::model()->findByPk($id);
			}
		}
		
		return $this->_model;
	}
	
	public function beforeLogout()
	{
		$existingLoginRecordToday = AccountLoginTracker::model()->find(array(
			'condition' => 'account_id = :account_id AND DATE(time_in) = :today AND time_out IS NULL',
			'params' => array(
				':account_id' => Yii::app()->user->account->id,
				':today' => date('Y-m-d'),
			),
		));
		
		if( $existingLoginRecordToday )
		{
			$status = 1;
			
			$daySchedule = AccountLoginSchedule::model()->find(array(
				'condition' => 'account_id = :account_id AND day_name = :day_name',
				'params' => array(
					':account_id' => Yii::app()->user->account->id,
					':day_name' => date('l'),
				),
				'order' => 'date_created DESC',
			));
		
			if( $daySchedule && time() < strtotime($daySchedule->end_time) )
			{
				$status = 2;
			}
			
			$existingLoginRecordToday->setAttributes(array(
				'time_out' => date('Y-m-d H:i:s'),
				'status' => $status,
			));
			
			$existingLoginRecordToday->save(false);
		}
			
		return true;
	}
}
?>