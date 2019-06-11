<?php

class TimeKeepingQueueController extends Controller
{
	
	public $layout='main-no-navbar';
	
	public function actionIndex()
	{
		$this->render('index');
	}
	
	public function actionList()
	{
		$criteria=new CDbCriteria;
		$criteria->compare('is_deleted',0, true);
		$criteria->compare('status',2, true);
		
		$model = new CActiveDataProvider('AccountPtoForm',array(			
			'pagination'=>array(				
				'pageSize'=>40,			
			),		
		));
		
		$model->criteria->mergeWith($criteria);
		
		$this->renderPartial('_list',array(
			'model'=>$model,
		));
	}
	
	public function actionView($id)
	{
		$model=$this->loadModel($id);


		$this->render('view',array(
			'model'=>$model,
		));
	}
	
	public function actionApprove($id)
	{
		$model = $this->loadModel($id);
		$model->status = 1;
		
		if($model->save(false))
		{
			#  $account->accountUser->mobile_number
			$mobileNo = $model->account->accountUser->mobile_number;
			$content = 'Engagex Time off Request '.$model->requestDateWithTime().' was Approved.';
			
			$jsonObject = $this->textAccount($mobileNo, $content);
			
			if($jsonObject !== false)
			{
				$accountPtoFormSms = new AccountPtoFormSms;
												
					$accountPtoFormSms->setAttributes(array(
						'account_pto_form_id' => $model->id,
						'account_id' => $model->account_id,
						'mobile_number' => urlencode($mobileNo),
						'api_code' => $jsonObject->code,
						'api_message' => $jsonObject->message
					));
					
				$accountPtoFormSms->save(false);
			}
		}
		
		Yii::app()->user->setFlash('success', 'Schedule Change Request has been approved!');
		$this->redirect(array('index'));
	}
	
	public function actionDeny($id)
	{
		$model = $this->loadModel($id);
		$model->status = 3;
		if($model->save(false))
		{
			$mobileNo = $model->account->accountUser->mobile_number;
			$content = 'Engagex Time off Request '.$model->requestDateWithTime().' was Denied.';
			
			$jsonObject = $this->textAccount($mobileNo, $content);
			
			if($jsonObject !== false)
			{
				$accountPtoFormSms = new AccountPtoFormSms;
												
					$accountPtoFormSms->setAttributes(array(
						'account_pto_form_id' => $model->id,
						'account_id' => $model->account_id,
						'mobile_number' => urlencode($mobileNo),
						'api_code' => $jsonObject->code,
						'api_message' => $jsonObject->message
					));
					
				$accountPtoFormSms->save(false);
			}
		}
		
		Yii::app()->user->setFlash('success', 'Schedule Change Request has been denied!');
		$this->redirect(array('index'));
	}
	
	public function actionPending($id)
	{
		$model = $this->loadModel($id);
		$model->status = 2;
		$model->save(false);
		
		Yii::app()->user->setFlash('success', 'Schedule Change Request has been set back to For Approval!');
		$this->redirect(array('index'));
	}
	
	public function loadModel($id)
	{
		$model=AccountPtoForm::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}
	
	public function actionDelete($id)
	{
		$model = $this->loadModel($id);
		$model->is_deleted = 1;
		$model->save(false);
		
		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
		{
			Yii::app()->user->setFlash('success', "Schedule Change Request has been deleted!");
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
		}
	}

	public function textAccount($mobileNo, $content)
	{
		//simpletexting api
		$url = 'https://app.simpletexting.com/v1/send';
		$apiToken = 'a6fb7969e0e4140d27427afc7e9841d1';
		
		#  $account->accountUser->mobile_number
		$phoneNumber = str_replace('(', '', $mobileNo);
		$phoneNumber = str_replace(')', '', $phoneNumber);
		$phoneNumber = str_replace('-', '', $phoneNumber);
		$phoneNumber = str_replace(' ', '', $phoneNumber);
		
		if( strlen( $phoneNumber ) >= 10 )
		{
			$fields = array(
				'token' => $apiToken,
				'phone' => urlencode($phoneNumber),
				'message' => urlencode($content)
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
			
			//close connection
			curl_close($ch);
			
			return $jsonObject;
		}
		
		return false;
	}
}