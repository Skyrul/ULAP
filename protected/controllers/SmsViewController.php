<?php

class SmsViewController extends Controller
{
	public function accessRules()
	{
		return array(
			array('allow', 
				'users'=>array('*'),
			),
		);
	}
	
	public function actionIndex($id)
	{
		$model = LeadCallHistory::model()->findByPk($id);
		
		$this->layout = 'sms';
		$this->render('index', array(
			'model' => $model
		));
	}
	
	public function actionReply($id)
	{
		$leadCallHistory = LeadCallHistory::model()->findByPk($id);
		
		$model = new SmsCustomerReply;
		$model->customer_id = $leadCallHistory->customer_id;
		$model->lead_id = $leadCallHistory->lead_id;
		$model->lead_call_history_id = $leadCallHistory->id;
		$model->lead_phone_number = $leadCallHistory->lead_phone_number;
		
		if( isset($_POST['SmsCustomerReply']) )
		{
			$model->attributes = $_POST['SmsCustomerReply'];	
			
			if( $model->validate() )
			{
				if( $model->save() )
				{
					//Send Invoice Email
					Yii::import('application.extensions.phpmailer.JPhpMailer');

					$mail = new JPhpMailer;
					$mail->SMTPAuth = true;		
					$mail->SMTPSecure = 'tls';   		
					$mail->SMTPDebug = 2; 
					$mail->Port = 25;      
					$mail->Host = 'mail.engagex.com';	
					$mail->Username = 'service@engagex.com';  
					$mail->Password = "_T*8c>ja";      

					$mail->SetFrom('service@engagex.com', 'Engagex Service', 0);
					$mail->AddReplyTo('service@engagex.com');
					$mail->AddAddress('customerservice@engagex.com');
					$mail->AddBCC('erwin.datu@engagex.com');
					
					$mail->Subject = 'Text Reply from ' . $leadCallHistory->customer->getFullName();
					
					$textMsgHTML = '<p><b>Lead Name:</b> '.$leadCallHistory->lead->getFullName().'</p>';
			
					$textMsgHTML .= '<p><b>Phone number:</b> '.$leadCallHistory->lead_phone_number.'</p>';
				
					$textMsgHTML .= '<p><b>Disposition:</b> '.$leadCallHistory->disposition.'</p>';
					
					$textMsgHTML .= '<p><b>Customer Note:</b> '.$model->reply_note.'</p>';
					
					$mail->MsgHTML($textMsgHTML);
			
					$mail->Send();	
					
					Yii::app()->user->setFlash('success', 'Message sent.');
					$this->redirect(array('reply', 'id'=>$id));
				}
			}
		}
		
		$this->layout = 'sms';
		$this->render('reply', array(
			'model' => $model,
			'leadCallHistory' => $leadCallHistory
		));
	}
}