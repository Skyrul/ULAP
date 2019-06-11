<?php 

ini_set('memory_limit', '10000M');
set_time_limit(0);

class CronEmailMonitorController extends Controller
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
		$models = EmailMonitor::model()->findAll(array(
			'condition'=>'status IN ("0", "4", "5") AND html_content !=""',
			// 'condition'=>'id=108603',
			'limit' => 25, 
			'order' => 'status DESC'
			// 'limit' => 1, 
		));
		
		// $models = EmailMonitor::model()->findAll(array(
			// 'condition'=>'id = 318',
		// ));
		
		echo 'No of models: '.count($models);
		
		echo '<br>';
		echo '<br>';
		
		if($models)
		{
			foreach($models as $model)
			{
				if( (date('Y-m-d H:i:s') >= date('Y-m-d H:i:s', strtotime("+30 minutes", strtotime($model->date_created))) || $model->status == 4 || $model->status == 5) ) //5 = send now
				{
					try 
					{	
						// if( filter_var($model->customer->email_address, FILTER_VALIDATE_EMAIL) ) 
						// {
							
							if( $this->sendIcal($model) )
							{	
								$model->status = 1; //mail sent
							}
							else
							{	
								$model->status = 4; //mailing error
							}
							
							if( $model->save() )
							{
								echo $model->id;
								echo '<br>';
							}
							else
							{
								print_r($model->getErrors());
							}
							
						// }
						
					} 
					catch (phpmailerException $e) 
					{
						echo $e->errorMessage(); //Pretty error messages from PHPMailer
					} 
					catch (Exception $e) 
					{
						echo $e->getMessage(); //Boring error messages from anything else!
					}
				}
			}
		}
		
		echo '<br><br>end...';
	}
	
	public function sendIcal($model)
	{
		//Send Invoice Email
		Yii::import('application.extensions.phpmailer.JPhpMailer');

		$mail = new JPhpMailer;
		// $mail->SMTPDebug = true;
		// $mail->Host = "mail.engagex.com";
		// $mail->Port = 25;
	
		$mail->SMTPAuth = true;		
		$mail->SMTPSecure = 'tls';   		
		$mail->SMTPDebug = 2; 
		$mail->Port = 25;      
		$mail->Host = 'mail.engagex.com';	
		$mail->Username = 'service@engagex.com';  
		$mail->Password = "_T*8c>ja";          				
		
		$recipientHolder = array();
		$receiverPhoneNumbers = array();
	
		if( $model->is_child_skill == 0 )
		{
			$disposition = SkillDisposition::model()->findByPk($model->disposition_id);	
		}
		
		if( $model->is_child_skill == 1 )
		{
			$disposition = SkillChildDisposition::model()->findByPk($model->child_disposition_id);	
		}
		
		if( $disposition && !empty($model->html_content) )
		{		
			$ccs = !empty($disposition->cc) ? explode(',', $disposition->cc) : array();
			// $ccs = !empty($disposition->cc) ?  $disposition->cc : '';
			
			if( $ccs )
			{
				foreach( $ccs as $cc )
				{
					$mail->AddCC($cc);
				}
			}

			$bccs = !empty($disposition->bcc) ? explode(',', $disposition->bcc) : array();
			// $bccs = !empty($disposition->bcc) ?  $disposition->bcc . ', erwin.datu@engagex.com, jim.campbell@engagex.com' : 'erwin.datu@engagex.com, jim.campbell@engagex.com';
			
			if( $bccs )
			{
				foreach( $bccs as $bcc )
				{
					$mail->AddBCC($bcc);
				}
			}
			
			$mail->AddBCC('erwin.datu@engagex.com');
			$mail->AddBCC('jim.campbell@engagex.com');
	
						
			$calendarAppointment = $model->calendarAppointment;
			$customer = $model->customer;
		
			// $mailName = 'Engagex Service';
			// $emailAddress = 'service@engagex.com';
			// $mime_boundary = "----Meeting Booking-".md5(time());
			
			// $replyTo = $disposition->from;
			// $mailSubject = $model->getReplacementCodeValues($disposition->subject);
			// $emailMonitorContent = $model->html_content;
			
			$mail->SetFrom('service@engagex.com', 'Engagex Service', 0);
			
			$mail->AddReplyTo($disposition->from);

			$mail->Subject = $model->getReplacementCodeValues($disposition->subject);
			
			$mail->MsgHTML($model->html_content);
		
			//Create Email Headers
			// $headers = "From: {$mailName} <".$emailAddress.">\n";
			// $headers .= "Reply-To: {$replyTo} <".$emailAddress.">\n";

			// $headers .= "MIME-Version: 1.0\n";
			// $headers .= "Content-Type: multipart/alternative; boundary=\"$mime_boundary\"\n";
			// $headers .= "Content-class: urn:content-classes:calendarmessage\n";
			
			// if(!empty($bccs))
				// $headers .= 'Bcc: '. $bccs . "\r\n";
			
			// if(!empty($ccs))
				// $headers .= 'Cc: '. $ccs . "\r\n";


			//Create Email Body (HTML)
			// $message = '';
			// $message .= "--$mime_boundary\n";
			// $message .= "Content-Type: text/html; charset=UTF-8\n";
			// $message .= "Content-Transfer-Encoding: 8bit\n\n";

			// $message .= "<html>\n";
			// $message .= "<body>\n";
			// $message .= $emailMonitorContent;
			// $message .= "</body>\n";
			// $message .= "</html>\n";
			
			##Disposition Attachments ##
			if( $model->is_child_skill == 1 )
			{
				$attachments = SkillChildDispositionEmailAttachment::model()->findAll(array(
					'condition' => 'skill_disposition_id = :skill_disposition_id',
					'params' => array(
						':skill_disposition_id' => $disposition->id,
					),
				));
			}
			else
			{
				$attachments = SkillDispositionEmailAttachment::model()->findAll(array(
					'condition' => 'skill_disposition_id = :skill_disposition_id',
					'params' => array(
						':skill_disposition_id' => $disposition->id,
					),
				));
			}
			
			if( $attachments )
			{
				foreach( $attachments as $attachment )
				{
					$mail->AddAttachment( Yii::getPathOfAlias('webroot') . '/fileupload/' . $attachment->fileUpload->original_filename );
					
					// $filenamePath = Yii::getPathOfAlias('webroot') . '/fileupload/' . $attachment->fileUpload->original_filename;
					// $v = $attachment->fileUpload->original_filename;
					// $file = $filenamePath;
					// $file_size = filesize($file);
					// $handle = fopen($file, "r");
					// $content = fread($handle, $file_size);
					// fclose($handle);
					// $content = chunk_split(base64_encode($content));

					// $message .= "Content-Type: application/octet-stream; name=\"".$v."\"\r\n"; // use different content types here
					// $message .= "Content-Transfer-Encoding: base64\r\n";
					// $message .= "Content-Disposition: attachment; filename=\"".$v."\"\r\n\r\n";
					// $message .= $content."\r\n\r\n";
					// $message .= "--".$mime_boundary."--"."\r\n";
				}
			}
			
			### ICAL ####
			 
			if(isset($calendarAppointment) && $calendarAppointment->title == 'APPOINTMENT SET' && $disposition->is_appointment_set == 1)
			{	
				$customer = Customer::model()->findByPk($model->customer_id);		
				
				$timeZone = $customer->getTimeZone();

				$timeZone = timezone_name_from_abbr($timeZone); // dynamically fetched from DB
				
				date_default_timezone_set($timeZone);
				
				$dtStart = date('Ymd\THis', strtotime($calendarAppointment->start_date));
				$dtEnd = date('Ymd\THis', strtotime($calendarAppointment->end_date));
				
				$start_zone = date('O', strtotime($calendarAppointment->start_date));
				$end_zone = date('O', strtotime($calendarAppointment->end_date));
				
				$dtStamp = date('Ymd\THis');
				
				// echo '<br>'.$dtStart.'<br>';
				$location = Calendar::model()->locationOptionsLabel($calendarAppointment->location);
				// $summary = $calendarAppointment->lead->getFullName().'-'.$calendarAppointment->title;
				$summary = $calendarAppointment->lead->getFullName();
				$customerName = $customer->getFullName();
				$customerEmail = $customer->email_address;
				$description = $calendarAppointment->details;
					 
				$event_id = uniqid();
				$sequence = 0;
				$status = 'CONFIRMED';

				$ical = "BEGIN:VCALENDAR\r\n";
				$ical .= "VERSION:2.0\r\n";
				$ical .= "PRODID:-//Microsoft Corporation//Outlook 14.0 MIMEDIR//EN\r\n";
				$ical .= "METHOD:PUBLISH\r\n";
				
				
				$ical .= "BEGIN:VTIMEZONE\n";
				$ical .= "TZID:{$timeZone}\n";
				$ical .= "TZURL:http://tzurl.org/zoneinfo-outlook/{$timeZone}\n";
				$ical .= "X-LIC-LOCATION:{$timeZone}\n";
				$ical .= "BEGIN:DAYLIGHT\n";
				$ical .= "TZOFFSETFROM:{$start_zone}\n";
				$ical .= "TZOFFSETTO:{$end_zone}\n";
				$ical .= "TZNAME:". date("T")."\n";
				$ical .= "DTSTART:{$dtStart}\n";
				$ical .= "END:DAYLIGHT\n";
				$ical .= "BEGIN:STANDARD\n";
				$ical .= "TZOFFSETFROM:{$start_zone}\n";
				$ical .= "TZOFFSETTO:{$end_zone}\n";
				$ical .= "TZNAME:".date("T")."\n";
				$ical .= "DTSTART:{$dtStart}\n";
				$ical .= "END:STANDARD\n";      
				$ical .= "END:VTIMEZONE\n";

				$ical .= "BEGIN:VEVENT\r\n";
				$ical .= "ORGANIZER;CN={$customerName}:MAILTO:".$customerEmail."\r\n";

				$ical .= "UID:".strtoupper(md5($event_id))."\r\n";
				$ical .= "SEQUENCE:".$sequence."\r\n";
				$ical .= "STATUS:".$status."\r\n";

				$ical .= "DTSTAMP:".$dtStamp."\r\n";
				$ical .= "DTSTART;TZID=".$timeZone.":".$dtStart."\r\n";
				$ical .= "DTEND;TZID=".$timeZone.":".$dtEnd."\r\n";

				$ical .= "LOCATION:".$location."\r\n";
				$ical .= "SUMMARY:".$summary."\r\n";
				$ical .= "DESCRIPTION:{$description}"."\r\n";

				$ical .= "END:VEVENT\r\n";
				$ical .= "END:VCALENDAR\r\n";

				### disable attachment of ICAL for now, we have the ICAL in the LINK using replacement_code (see Lead Call History) ##
				#$message .= "--$mime_boundary\n";							
				#$message .= "Content-Type: text/calendar;name=\"meeting.ics\";method=REQUEST\n";
				#$message .= "Content-Transfer-Encoding: 8bit\n\n";
				#$message .= $ical;     
			}
			
			if(isset($model->lead) && isset($model->lead->list) && isset($model->lead->list->calendar))
			{
				$calendar = $model->lead->list->calendar;
				
				if( isset($calendarAppointment) && isset($calendarAppointment->calendar) )
				{
					$calendar = $calendarAppointment->calendar; 
				}
				
				$customerOfficeStaffs = CustomerOfficeStaff::model()->findAll(array(
					'condition' => 'customer_id = :customer_id AND is_received_email > 0 AND is_deleted=0 AND (email_address IS NOT NULL OR email_address != "")',
					'params' => array(
						':customer_id' => $customer->id,
					),
				));
				
				
				//simpletexting api
				$url = 'https://app.simpletexting.com/v1/send';
				$apiToken = 'a6fb7969e0e4140d27427afc7e9841d1';
				
				foreach($customerOfficeStaffs as $staff)
				{
					## 3 = ALL CALENDAR in that OFFICE where the Staff was assigned to. ##
					if($staff->is_received_email == 3 && $staff->customer_office_id == $calendar->office_id)
					{
						if(!isset($recipientHolder[$staff->email_address]))
							$recipientHolder[$staff->email_address] = $staff->email_address;
					}
					
					if($staff->is_received_email == 1)
					{
						$existingCalenderStaffReceiveEmail = CalenderStaffReceiveEmail::model()->find(array(
							'condition' => 'staff_id = :staff_id AND calendar_id = :calendar_id',
							'params' => array(
								':staff_id' => $staff->id,
								':calendar_id' => $calendar->id,
							),
						));
						
						if($existingCalenderStaffReceiveEmail !== null)
						{
							if(!isset($recipientHolder[$staff->email_address]))
								$recipientHolder[$staff->email_address] = $staff->email_address;
						}
					}
					
					
					//send text
					if( $disposition->is_send_text == 1 && !empty($model->text_content) && $staff->enable_texting == 1 && !empty($staff->mobile) )
					{
						$phoneNumber = str_replace('(', '', $staff->mobile);
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
							
							$smsReceiver = new EmailMonitorTextReceiver;
							
							$smsReceiver->setAttributes(array(
								'email_monitor_id' => $model->id,
								'staff_id' => $staff->id,
								'mobile_number' => urlencode($phoneNumber),
								'api_code' => $jsonObject->code,
								'api_message' => $jsonObject->message
							));
							
							if( $smsReceiver->save(false) )
							{
								$receiverPhoneNumbers[] = urlencode($phoneNumber);
							}
							
							//close connection
							curl_close($ch);
						}
					}
				}
			}
			
			if( $receiverPhoneNumbers )
			{
				$mail2 = new JPhpMailer;
				$mail2->SMTPAuth = true;		
				$mail2->SMTPSecure = 'tls';   		
				$mail2->SMTPDebug = 2; 
				$mail2->Port = 25;      
				$mail2->Host = 'mail.engagex.com';	
				$mail2->Username = 'service@engagex.com';  
				$mail2->Password = "_T*8c>ja";      

				$mail2->SetFrom('service@engagex.com', 'Engagex Service', 0);
				
				$mail2->AddAddress('service@engagex.com');
				$mail2->AddBCC('erwin.datu@engagex.com');
			 
				$mail2->AddReplyTo('service@engagex.com');

				$mail2->Subject = $model->disposition.' - '.$model->lead->first_name.' '.$model->lead->last_name.' - Text Message';
				
				$textMsgHTML = '<p><b>Lead Name:</b> '.$model->lead->first_name.' '.$model->lead->last_name.'</p>';
				
				if( isset($model->leadCallHistory) )
				{
					$textMsgHTML .= '<p><b>Lead Phone:</b> '.$model->leadCallHistory->lead_phone_number.'</p>';
				}
				
				$textMsgHTML .= '<p><b>Phone numbers text message was sent to:</b> '.implode(', ', $receiverPhoneNumbers).'</p>';
				
				$textMsgHTML .= '<p><b>Text body:</b> '.$model->text_content.'</p>';
				
				$mail2->MsgHTML($textMsgHTML);
				
				$mail2->Send();
			}
			
			if( $recipientHolder )
			{
				foreach( $recipientHolder as $recipient )
				{
					$mail->AddAddress($recipient);
				}
			}
			
			// $mail->AddAddress('erwin.datu@engagex.com');		
			
			// if(!isset($recipientHolder[$model->customer->email_address]))
				// $recipientHolder[$model->customer->email_address] = $model->customer->email_address;
			
			
			// $recipients = implode(',',$recipientHolder);
			
			//SEND MAIL
			// $mail_sent = mail('jim.campbell@engagex.com', $mailSubject, $message, $headers );
			// $mail_sent = mail('markjuan169@gmail.com', $mailSubject, $message, $headers );
			// mail('erwin.datu@engagex.com', $mailSubject, $message, $headers );
			
			// $mail_sent = mail($recipients, $mailSubject, $message, $headers );

			// if($mail_sent)
			// {
				// return true;
			// }
			// else
			// {
				// return false;
			// }
			
			return $mail->Send();
		}
		
		// echo '<pre>';
			// print_r($mail);
		// exit;
		
		return false;
	}

}

?>