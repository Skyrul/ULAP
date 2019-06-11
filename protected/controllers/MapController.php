<?php 

class MapController extends Controller
{
	
	public function filters()
	{
		
		return array(
			// 'accessControl', // perform access control for CRUD operations
			// 'postOnly + delete', // we only allow deletion via POST request
		);
	}
	
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
		);
	}
	
	public function actionIndex()
	{
		$this->render('index',array(
			
		));
	}

	public function actionEmail()
	{
		print_r($_REQUEST);
		exit;
	}
	
	public function actionView($lead_id, $office_id)
	{
		$lead = Lead::model()->findByPk($lead_id);
		$office = CustomerOffice::model()->findByPk($office_id);
		
		$this->render('view', array(
			'lead' => $lead,
			'office' => $office,
		));
	}
}