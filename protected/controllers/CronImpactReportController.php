<?php 

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

ini_set('memory_limit', '2000M');
set_time_limit(0);

class CronImpactreportController extends Controller
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
		
		$model = ImpactReportSettings::model()->findByPk(1);
		
		$recipients = explode(', ', $model->auto_email_recipients);
		
		$valid = false;
		
		if( $model->auto_email_frequency == 'DAILY' )
		{
			if( date('l') == $model->auto_email_time )
			{
				
			}
		}
		else
		{
			if( date('l') == $model->auto_email_day )
			{
				
			}
		}
		
		echo 'Settings Recipients: ' . $model->auto_email_recipients;
		
		echo '<br>';
		echo '<br>';
		
		echo 'Settings Frequency: ' . $model->auto_email_frequency;
		
		echo '<br>';
		echo '<br>';
		
		echo 'Settings Day: ' . $model->auto_email_day;
		
		echo '<br>';
		echo '<br>';
		
		echo 'Settings Time: ' . $model->auto_email_time;
		
		echo '<br>';
		echo '<br>';
		
		echo 'Settings Email Last Sent: ' . date('m/d/y g:i A', strtotime($model->auto_email_last_sent));
		
		echo '<br>';
		echo '<br>';
		
		echo 'Current Day of week: ' . date('l');
		
		echo '<br>';
		echo '<br>';
		
		echo 'Current Date/Time: ' . date('m/d/y g:i A');
		
		echo '<br>';
		echo '<br>';
		
		echo 'valid: ' . $valid;
		exit;
		
		$valid = true;
		
		if( $valid && !empty($recipients) )
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
	
			$mail->SetFrom('service@engagex.com');
			
			$mail->Subject = 'Impact Report';
			
			$mail->MsgHTML( date('m/d/y h:i:s') );
			
			foreach( $recipients as $recipient )
			{
				$mail->AddAddress( $recipient );
			}
		
			$mail->AddAttachment( Yii::getPathOfAlias('webroot') . '/impactReport/Impact Report.xlsx' );
			
			if( $mail->Send() )
			{
				date_default_timezone_set('America/Chicago');
				
				$model->auto_email_last_sent = date('Y-m-d H:i:s');
				$model->save(false);
			}
		}
	}
	
}

?>