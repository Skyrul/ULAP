<?php 

ini_set('memory_limit', '10000M');
set_time_limit(0);

class CronTextMonitorController extends Controller
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
		$models = TextMonitor::model()->findAll(array(
			'condition'=>'status IN ("0", "4", "5") AND text_content !=""',
			'limit' => 25, 
		));
		
		echo 'No of models: '.count($models);
		
		echo '<br>';
		echo '<br>';
		
		if($models)
		{
			//simpletexting api
			$url = 'https://app.simpletexting.com/v1/send';
			$apiToken = 'a6fb7969e0e4140d27427afc7e9841d1';
			
			foreach($models as $model)
			{
				if( (date('Y-m-d H:i:s') >= date('Y-m-d H:i:s', strtotime("+30 minutes", strtotime($model->date_created))) || $model->status == 4 || $model->status == 5) ) //5 = send now
				{
					$customerOfficeStaffs = CustomerOfficeStaff::model()->findAll(array(
						'condition' => 'customer_id = :customer_id AND enable_texting = 1 AND is_deleted=0 AND (mobile IS NOT NULL OR mobile != "")',
						'params' => array(
							':customer_id' => $model->customer_id,
						),
					));
					
					if( $customerOfficeStaffs )
					{
						foreach( $customerOfficeStaffs as $customerOfficeStaff)
						{
							$phoneNumber = str_replace('(', '', $customerOfficeStaff->mobile);
							$phoneNumber = str_replace(')', '', $phoneNumber);
							$phoneNumber = str_replace('-', '', $phoneNumber);
							$phoneNumber = str_replace(' ', '', $phoneNumber);
							
							if( strlen( $phoneNumber ) >= 10 )
							{
								$fields = array(
									'token' => $apiToken,
									'phone' => urlencode($phoneNumber),
									'message' => urlencode($model->text_content)
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
								
								$textReceiver = new TextMonitorReceiver;
								
								$textReceiver->setAttributes(array(
									'text_monitor_id' => $model->id,
									'employee_account_id' => $customerOfficeStaff->account_id,
									'staff_id' => $customerOfficeStaff->id,
									'mobile_number' => urlencode($phoneNumber),
									'api_code' => $jsonObject->code,
									'api_message' => $jsonObject->message
								));
								
								$textReceiver->save(false);
								
								//close connection
								curl_close($ch);
							}
						}
					}
				}
			}
		}
		
		echo '<br><br>end...';
	}
}

?>