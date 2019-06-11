<?php 

class PhoneSearchController extends Controller
{
	
	public function actionIndex()
	{
		$models = array();
		
		if( isset($_POST['phoneNumber']) )
		{
			if( !empty($_POST['phoneNumber']) )
			{
				$models = Lead::model()->findAll(array(
					'condition' => 't.status !=4 AND ( 
						(office_phone_number = :search_phone_number AND office_phone_number != "" AND office_phone_number IS NOT NULL) OR 
						(office_phone_number = :search_phone_number AND office_phone_number != "" AND office_phone_number IS NOT NULL) OR 
						(office_phone_number = :search_phone_number AND office_phone_number != "" AND office_phone_number IS NOT NULL) OR 
						(mobile_phone_number = :search_phone_number AND mobile_phone_number != "" AND mobile_phone_number IS NOT NULL) OR
						(mobile_phone_number = :search_phone_number AND mobile_phone_number != "" AND mobile_phone_number IS NOT NULL) OR
						(mobile_phone_number = :search_phone_number AND mobile_phone_number != "" AND mobile_phone_number IS NOT NULL) OR
						(home_phone_number = :search_phone_number AND home_phone_number != "" AND home_phone_number IS NOT NULL) OR
						(home_phone_number = :search_phone_number AND home_phone_number != "" AND home_phone_number IS NOT NULL) OR
						(home_phone_number = :search_phone_number AND home_phone_number != "" AND home_phone_number IS NOT NULL) 
					)',
					'params' => array(
						':search_phone_number' => preg_replace('/[^0-9]/', '', $_POST['phoneNumber'])
					),
				));

				Yii::app()->user->setFlash('success', count($models) . ' records found.');
			}
			else
			{
				Yii::app()->user->setFlash('danger', 'Phone number is required.');
			}
		}
		
		$this->render('index', array(
			'models' => $models
		));
	}
	
}

?>