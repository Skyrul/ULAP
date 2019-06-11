<?php

class SmsTestController extends Controller
{
	
	public function actionIndex()
	{
		exit;
		
		$url = 'https://app.simpletexting.com/v1/send';
		// $url = 'https://app.simpletexting.com/v1/messaging/sent/list';
		
		$apiToken = 'a6fb7969e0e4140d27427afc7e9841d1';
		
		$phone = '8018456877';
		
		$leadCallHistory = LeadCallHistory::model()->findByPk(1624065);
		
		// echo 'leadCallHistory ID: ' . $leadCallHistory->id;
		
		// echo '<br>';
		
		// echo 'leadCallHistory Agent Note: ' . $leadCallHistory->agent_note;
		
		// echo '<br><br>';
		
		$message = '
			This is a test for the reply link
			
		';

		$previewLink = 'http://portal.engagexapp.com/index.php/smsView/reply/id/'.$leadCallHistory->id;
		
		$message .= preg_replace('#^https?://#', '', rtrim( file_get_contents('http://tinyurl.com/api-create.php?url='.$previewLink) ));

		$fields = array(
			'token' => $apiToken,
			'phone' => urlencode($phone),
			'message' => urlencode($message)
		);
		
		//url-ify the data for the POST
		foreach( $fields as $key => $value ) 
		{ 
			$fields_string .= $key.'='.$value.'&'; 
		}
		
		rtrim($fields_string, '&');

		//open connection
		$ch = curl_init();

		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_POST, count($fields));
		curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);  
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);  

		//execute post
		$result = curl_exec($ch);	
		$jsonObject = json_decode($result);
		
		//close connection
		curl_close($ch);
		
		echo '<pre>';
			print_r($jsonObject);
		echo '</pre>';
	}
	
}

?>