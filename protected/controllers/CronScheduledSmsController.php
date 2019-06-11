<?php 
	
class CronScheduledSmsController extends Controller
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
		date_default_timezone_set('America/Denver');
		
		$models = SmsEmployee::model()->findAll(array(
			'condition' => 'status=0',
		));
		
		echo 'count: ' . count($models);
		
		echo '<br><br>';
		
		if( $models )
		{
			//simpletexting api
			$url = 'https://app.simpletexting.com/v1/send';
			$apiToken = 'a6fb7969e0e4140d27427afc7e9841d1';
			
			foreach( $models as $model )
			{
				if( (date('Y-m-d H:i:s') >= date('Y-m-d H:i:s', strtotime($model->schedule_send_date))) )
				{
					$accounts = Account::model()->findAll(array(
						'with' => 'accountUser',
						'condition' => 't.status=1 AND t.is_deleted=0 AND accountUser.mobile_number IS NOT NULL AND accountUser.mobile_number != "" AND t.account_type_id IN ('.$model->security_group_ids.')',
					));
					
					if( $accounts )
					{
						foreach( $accounts as $account )
						{
							if( isset($account->accountUser) )
							{
								$phoneNumber = str_replace('(', '', $account->accountUser->mobile_number);
								$phoneNumber = str_replace(')', '', $phoneNumber);
								$phoneNumber = str_replace('-', '', $phoneNumber);
								$phoneNumber = str_replace(' ', '', $phoneNumber);
								
								if( strlen( $phoneNumber ) >= 10 )
								{
									$fields = array(
										'token' => $apiToken,
										'phone' => urlencode($phoneNumber),
										'message' => urlencode($model->content)
									);
									
									//url-ify the data for the POST
									
									$fields_string = '';
									
									foreach( $fields as $key => $value ) 
									{ 
										$fields_string .= $key.'='.$value.'&'; 
									}
									
									$fields_string = rtrim($fields_string, '&');

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
									
									$smsReceiver = new SmsEmployeeReceiver;
									
									$smsReceiver->setAttributes(array(
										'sms_employee_id' => $model->id,
										'employee_account_id' => $account->id,
										'security_group_id' => $account->account_type_id,
										'mobile_number' => urlencode($phoneNumber),
										'api_code' => $jsonObject->code,
										'api_message' => $jsonObject->message
									));
									
									$smsReceiver->save(false);
									
									//close connection
									curl_close($ch);
								}
							}
						}
					}
					
					
					$model->status = 1;
					$model->save(false);
				}
			}
		}
	}
}