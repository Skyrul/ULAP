<?php 

Class WebPhoneController extends Controller
{
	public function accessRules()
	{
		return array(
			array('allow', 
				'users'=>array('*'),
			),
		);
	}
	
	public function actionIndex()
	{
		$this->layout = 'blank2';
		$this->render('index');
	}
	
	public function actionClick2call()
	{
		$this->layout = 'blank2';
		$this->render('click2call');
	}

}

?>