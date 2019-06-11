<?php

class DefaultController extends Controller
{
	public function actionIndex()
	{
		$url = array('/customer');
		$noPermission = true;
		
		if( Yii::app()->user->account->checkPermission('accounting_exception_punches_tab','visible') )
		{
			$url = array('/accounting/accounting/timeKeeping');
			$noPermission = false;
		}
		
		if( Yii::app()->user->account->checkPermission('accounting_sales_goals_tab','visible') )
		{
			$url = array('/salesManagement/goals');
			$noPermission = false;
		}
		
		if( Yii::app()->user->account->checkPermission('accounting_enrollment_listing_tab','visible') )
		{
			$url = array('/salesManagement');
			$noPermission = false;
		}
		
		if( Yii::app()->user->account->checkPermission('accounting_payroll_file_tab','visible') )
		{
			$url = array('/accounting/accounting/index');
			$noPermission = false;
		}
		
		if( Yii::app()->user->account->checkPermission('accounting_billing_windows_tab','visible') )
		{
			$url = array('/accounting/accounting/billingWindow');
			$noPermission = false;
		}
		
		if( $noPermission )
		{
			Yii::app()->user->setFlash('danger', 'Your security group has no permission to access the accounting pages.');
		}
		
		$this->redirect($url);
	}
}