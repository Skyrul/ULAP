<?php

class DialerTestController extends Controller
{
		
	public function accessRules()
	{
		return array(
			array('allow',  // deny all users
				'users'=>array('*'),
			),
		);
	}
	
	public function actionIndex()
	{
		$asterisk = new Asterisk;
		
		//temporary values
		$asteriskParams = array(
			'call_history_id' => '999',
			'agent_extension' => '999',
			'caller_id' => 'Ulap Test',
			// 'lead_phone_number' => '91' . preg_replace("/[^0-9]/","", $_POST['lead_phone_number']), 
			// 'lead_phone_number' => '918005158734', //provo office number
			// 'lead_phone_number' => '918019001203', //sir nathan
			// 'lead_phone_number' => '918042221111',
			'lead_phone_number' => '818042221111',
		); 
		
		$asterisk = new Asterisk;
		$asterisk->call($asteriskParams);
	}
	
	public function actionHangup($channel)
	{
		$asterisk = new Asterisk;
		$asterisk->hangup($channel);
	}
	
	public function actionGetStatus()
	{
		$existingChannel = AsteriskChannel::model()->findByPk(7);
		
		if( $existingChannel )
		{
			$asterisk = new Asterisk;
			$result['call_status'] = $asterisk->getCallStatus($existingChannel);
		}
	}
}

?>