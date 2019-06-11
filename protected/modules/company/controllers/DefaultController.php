<?php

class DefaultController extends CController
{
	public function actionIndex()
	{
		$this->redirect(array('/company/companyFile/index','company_id'=>Yii::app()->user->account->company->id));
	}
}