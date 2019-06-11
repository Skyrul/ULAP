<?php 

class TextingController extends Controller
{
	
	public function actionIndex()
	{
		$authAccount = Yii::app()->user->account;
		
		$model = new SmsEmployee;
		
		$securityGroups = Account::listAccountType();
		
		if( isset($_POST['SmsEmployee']) )
		{
			$valid = true;
			
			$model->attributes = $_POST['SmsEmployee'];
			$model->account_id = $authAccount->id;
			
			if( isset($_POST['submitBtn']) )
			{
				$model->schedule_send_date = null;
			}
			else
			{
				$model->type = 2;
				$model->status = 0;
			}
			
			if( isset($_POST['scheduleSendSubmitBtn']) )
			{
				if( !empty($_POST['scheduleSendDate']) && !empty($_POST['scheduleSendTime']) )
				{
					$model->schedule_send_date = date('Y-m-d H:i:s', strtotime($_POST['scheduleSendDate'].' '.$_POST['scheduleSendTime']));
				}
				else
				{
					$valid = false;
					Yii::app()->user->setFlash('danger', 'Schedule send date and time is required.');
				}
			}
			
			if( !empty($_POST['SmsEmployee']['securityGroupIds']) )
			{
				$securityGroupText = '';
				
				$model->security_group_ids = implode(',', $_POST['SmsEmployee']['securityGroupIds']);
				
				foreach( $_POST['SmsEmployee']['securityGroupIds'] as $securityGroupId )
				{
					$securityGroupText .= $securityGroups[$securityGroupId] . ', ';
				}
				
				$model->security_group_text = rtrim($securityGroupText, ', ');
			}
			
			if( $valid && $model->validate() )
			{
				if( $model->save() )
				{
					$status = 'success';
					$message = 'Message was successfully sent.';
					
					if( $model->type == 1 )
					{
						//simpletexting api
						$url = 'https://app.simpletexting.com/v1/send';
						$apiToken = 'a6fb7969e0e4140d27427afc7e9841d1';
						
						$accounts = Account::model()->findAll(array(
							'with' => 'accountUser',
							'condition' => 't.status=1 AND t.is_deleted=0 AND accountUser.mobile_number IS NOT NULL AND accountUser.mobile_number != "" AND t.account_type_id IN ('.implode(',', $_POST['SmsEmployee']['securityGroupIds']).')',
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
					}
				}
				else
				{
					$status = 'danger';
					$message = 'Database error.';
				}
				
				Yii::app()->user->setFlash($status, $message);
				$this->redirect(array('index'));
			}
		}
		
		
		$histories = SmsEmployee::model()->findAll(array(
			'condition' => 'status!=3',
			'order' => 'date_created DESC',
		));
		
		$dataProvider = new CArrayDataProvider($histories, array(
			'pagination' => array(
				'pageSize' => 1000
			),
		));
		
		$this->render('index', array(
			'authAccount' => $authAccount,
			'model' => $model,
			'securityGroups' => $securityGroups,
			'dataProvider' => $dataProvider
		));
	}
	
	
	public function actionView()
	{
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => $html,
		);
		
		if( isset($_POST['ajax']) && isset($_POST['id']) )
		{
			$model = SmsEmployee::model()->findByPk($_POST['id']);
			
			$receivers = SmsEmployeeReceiver::model()->findAll(array(
				'condition' => 'sms_employee_id = :sms_employee_id',
				'params' => array(
					':sms_employee_id' => $_POST['id']
				),
			));
			
			$html = $this->renderPartial('view', array('model' =>$model, 'receivers' => $receivers), true);
			
			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}
}

?>