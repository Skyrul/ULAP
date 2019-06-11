<?php

class SmsReceiveController extends Controller
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
		
		
		$customerStaff = CustomerOfficeStaff::model()->find(array(
			'condition' => 'mobile = :mobile',
			'params' => array(
				':mobile' => "(".substr($_GET['from'], 0, 3).") ".substr($_GET['from'], 3, 3)."-".substr($_GET['from'],6)
			),
		));
		
		if( $customerStaff )
		{
			$lastFiveTexts = EmailMonitor::model()->findAll(array(
				'condition' => 'customer_id = :customer_id AND status = 1',
				'params' => array(
					':customer_id' => $customerStaff->customer_id
				),
				'order' => 'date_updated DESC',
				'limit' => 5 
			));
			
			$mail->Subject = 'Disposition SMS Response';
			
			$mail->AddAddress('customerservice@engagex.com');
			$mail->AddBCC('erwin.datu@engagex.com');
			// $mail->AddAddress('erwin.datu@engagex.com');
			
			$textMsgHTML = '<p><b>Customer Name:</b> '.$customerStaff->staff_name.'</p>';
			
			$textMsgHTML .= '<p><b>Phone number:</b> '.$_GET['from'].'</p>';
		
			$textMsgHTML .= '<p><b>Message text:</b> '.$_GET['text'].'</p>';
			
			$textMsgHTML .= '<p><b>Last five text sent:</b></p>';
			
			if( $lastFiveTexts )
			{
				foreach( $lastFiveTexts as $lastFiveText )
				{
					$dateTime = new DateTime($lastFiveText->date_updated, new DateTimeZone('America/Chicago'));
					$dateTime->setTimezone(new DateTimeZone('America/Denver')); 
					
					$textMsgHTML .= $lastFiveText->lead->first_name.' '.$lastFiveText->lead->last_name.' - '.$lastFiveText->disposition.' - '.$dateTime->format('m/d/Y g:i A'); 
					
					$textMsgHTML .= '<br />';
				}
			}
			
			$mail->MsgHTML($textMsgHTML);
			
			$mail->Send();	
		}
		else
		{
			$employee = AccountUser::model()->find(array(
				'condition' => 'mobile_number = :mobile_number',
				'params' => array(
					':mobile_number' => "(".substr($_GET['from'], 0, 3).") ".substr($_GET['from'], 3, 3)."-".substr($_GET['from'],6)
				),
			));
			
			if( $employee )
			{
				$mail->Subject = 'HR SMS Response';
				
				$mail->AddAddress('hr@engagex.com');
				$mail->AddBCC('erwin.datu@engagex.com');
				// $mail->AddAddress('erwin.datu@engagex.com');
				
				$textMsgHTML = '<p><b>Employee Name:</b> '.$employee->first_name.' '.$employee->last_name.'</p>';
				
				$textMsgHTML .= '<p><b>Phone number:</b> '.$_GET['from'].'</p>';
				
				$textMsgHTML .= '<p><b>Message text:</b> '.$_GET['text'].'</p>';
				
				$mail->MsgHTML($textMsgHTML);
				
				$mail->Send();	
			}
		}
	}
	
}