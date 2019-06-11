<?php

class DefaultController extends CController
{
	public function actionIndex()
	{
		$url = array('/customer/data/index');
		$noPermission = true;
		
		if( Yii::app()->user->account->checkPermission('employees_hostdial_users_tab','visible') )
		{
			$url = array('/hr/accountUser/hostdialUser');
			$noPermission = false;
		}
		
		if( Yii::app()->user->account->checkPermission('employees_employees_tab','visible') )
		{
			$url = array('/hr/accountUser/index');
			$noPermission = false;
		}
		
		if( $noPermission )
		{
			Yii::app()->user->setFlash('danger', 'Your security group has no permission to access the employee pages.');
		}
		
		$this->redirect($url);
	}
}