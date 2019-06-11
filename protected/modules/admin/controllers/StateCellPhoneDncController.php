<?php 

class StateCellPhoneDncController extends Controller
{
	
	public function actionIndex()
	{	
		if( isset($_POST['ajax']) )
		{
			$existingSettings = StateDncSettings::model()->deleteAll();
			
			if( !empty($_POST['states']) )
			{
				foreach( $_POST['states'] as $stateId )
				{
					$newSettings = new StateDncSettings;				
					$newSettings->state_id = $stateId;				
					$newSettings->date_created = date('Y-m-d H:i:s');	
					
					$newSettings->save(false);
				}
			}
			
			echo json_encode(array('status'=>'success'));
			Yii::app()->end();
		}
		
		
		$states = State::model()->findAll(array(
			'order' => 'name ASC'
		));
		
		$this->render('index', array(
			'states' => $states
		));
	}
	
}

?>