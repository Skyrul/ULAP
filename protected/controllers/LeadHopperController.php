<?php 

class LeadHopperController extends Controller
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
		$models = LeadHopper::model()->findAll(array(
			'limit' => 200,
		));
		
		$this->render('index', array(
			'models' => $models,
		));
	}

}

?>