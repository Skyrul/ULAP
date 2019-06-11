<?php

class DefaultController extends CController
{
	public function actionIndex()
	{
		$url = array('/customer');
		$noPermission = true;
		
		if( Yii::app()->user->account->checkPermission('structure_enrollment_tab','visible') )
		{
			$url = array('/admin/enrollment/index');
			$noPermission = false;
		}
		
		if( Yii::app()->user->account->checkPermission('structure_contract_tab','visible') )
		{
			$url = array('/admin/contract/index');
			$noPermission = false;
		}
		
		if( Yii::app()->user->account->checkPermission('structure_campaign_tab','visible') )
		{
			$url = array('/admin/campaign/index');
			$noPermission = false;
		}
		
		if( Yii::app()->user->account->checkPermission('structure_skills_tab','visible') )
		{
			$url = array('/admin/skill/index');
			$noPermission = false;
		}
		
		if( Yii::app()->user->account->checkPermission('structure_companies_tab','visible') )
		{
			$url = array('/admin/company/index');
			$noPermission = false;
		}
		
		if( $noPermission )
		{
			Yii::app()->user->setFlash('danger', 'Your security group has no permission to access the structure pages.');
		}
		
		$this->redirect($url);
	}
}