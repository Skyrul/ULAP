<?php 

ini_set('memory_limit', '4000M');
set_time_limit(0);
	
class TestController extends Controller
{
	
	public $excludeCustomerSql;
	
	public function init()
	{
		$this->excludeCustomerSql = CustomerSkill::model()->removeFromSalesReports();
	}
	
	public function accessRules()
	{
		return array(
			array('allow', 
				'users'=>array('*'),
			),
		);
	}
	
	public function actionLeadPatch()
	{
		exit;
		
		set_time_limit(0);
		ini_set('memory_limit', '512M');
		
		$leads = Lead::model()->findAll(array(
			'condition' => 'customer_id IS NULL',
		));
		
		if( $leads )
		{
			foreach( $leads as $lead )
			{
				$list = $lead->list;
				
				$lead->customer_id = $list->customer_id;
				$lead->save(false);
			}
		}
		
		echo 'end';
	}
	
	public function actionCreditCards()
	{
		$visaMasters = CustomerCreditCard::model()->findAll(array(
			'with' => 'customer',
			'condition' => 'LENGTH(credit_card_number) < 16 AND credit_card_type IN ("Discover", "Visa", "MasterCard") AND customer.company_id NOT IN("17", "18", "23") AND customer.is_deleted=0',
		));
		
		$amexs = CustomerCreditCard::model()->findAll(array(
			'with' => 'customer',
			'condition' => 'LENGTH(credit_card_number) < 15 AND credit_card_type="Amex" AND customer.company_id NOT IN("17", "18", "23") AND customer.is_deleted=0',
		));
		
		$this->render('creditCards', array(
			'visaMasters' => $visaMasters,
			'amexs' => $amexs,
		));
	}

	public function actionRemoveTestAppointments()
	{
		exit;
		$models = CalendarAppointment::model()->findAll(array(
			'with' => 'calendar',
			'condition' => 'calendar.customer_id = :customer_id AND t.title IN ("APPOINTMENT SET", "INSERT APPOINTMENT")',
			'params' => array(
				'customer_id' => 177,
			),
		));
		
		echo 'count: ' . count($models);
		
		echo '<br><br>';
		
		$ctr = 0;
		
		if( $models )
		{
			foreach( $models as $model )
			{
				$model->delete();
				
				$ctr++;
			}
		}
		else
		{
			echo 'no results found';
		}
		
		echo '<br><br>';
		echo 'deleted: ' . $ctr;
		echo '<br><br>';
		echo 'end...';
	}

	public function actionEmailPatch()
	{
		// $leadCallHistories = LeadCallHistory::model()->findAll(array(
			// 'order' => 'date_created DESC',
			// 'condition' => 'lead_id = :lead_id',
			// 'params' => array(
				// ':lead_id' => 1478244,
			// ),
		// ));`
		
		
		
		// echo '<pre>';
		
		// if( $leadCallHistories)
		// {
			// foreach( $leadCallHistories as $leadCallHistory )
			// {
				// print_r($leadCallHistory->attributes);
			// }
		
			// echo $leadCallHistory->getReplacementCodeValues();
		// }
		
		// exit;
		
		//2259
		$leadCallHistory = LeadCallHistory::model()->findByPk(4115206);
		
		$disposition = SkillDisposition::model()->findByPk($leadCallHistory->disposition_id);	
		
		// $leadCallHistory->agent_note = 'CASEY FLOOD has agreed to meet with you for a simple conversation. Thank you!';
		// $leadCallHistory->agent_note = 'We have confirmed that LOUANN BRADFORD has agreed to meet with you for the scheduled appointment.';
		
		// if( $leadCallHistory->calendar_appointment_id == null )
		// {
			// $latestAppointment = CalendarAppointment::model()->find(array(
				// 'order' => 'date_created DESC',
				// 'condition' => 'lead_id = :lead_id AND title="APPOINTMENT SET"',
				// 'params' => array(
					// ':lead_id' => 1478244
				// ),
			// ));
			
			// if( $latestAppointment )
			// {
				// $leadCallHistory->calendar_appointment_id = $latestAppointment->id;
			// }
		// }
		
		// echo '<pre>';
			// print_r($leadCallHistory->attributes);
			
		// echo '<br><br>';
		
		// echo $leadCallHistory->getReplacementCodeValues();
		// exit;
		
		$emailMonitor = new EmailMonitor;
								
		$emailMonitor->setAttributes(array(
			'lead_id' => $leadCallHistory->lead_id,
			'agent_id' => $leadCallHistory->agent_account_id,
			'customer_id' => $leadCallHistory->customer_id,
			'skill_id' => $leadCallHistory->list->skill_id,
			'disposition_id' => $leadCallHistory->disposition_id,
			'child_disposition_id' => $leadCallHistory->skill_child_disposition_id,
			'is_child_skill' => $leadCallHistory->is_skill_child,
			'disposition' => $leadCallHistory->disposition,
			'calendar_appointment_id' => $leadCallHistory->calendar_appointment_id,
			'html_content' => $leadCallHistory->getReplacementCodeValues(),
			'status' => 0,
		));		

		$emailMonitor->text_content = $leadCallHistory->getReplacementCodeValues($disposition->text_body);		
		
		$emailMonitor->save(false);
		
		echo '<br><br>';
		
		echo '<pre>';
			print_r($emailMonitor->attributes);
		
		// $emailMonitor = EmailMonitor::model()->findByPk(29348);
		
		// $emailMonitor->html_content = $leadCallHistory->getReplacementCodeValues();
		// $emailMonitor->calendar_appointment_id = $leadCallHistory->calendar_appointment_id; // send now
		// $emailMonitor->status = 5; // send now

		// if( $emailMonitor->save(false) )
		// {
			// echo 'Success';
		// }
	}

	public function actionCheckDid($customer_id)
	{
		$customer = Customer::model()->findByPk($customer_id);
		
		$companyDid = CompanyDid::model()->find(array(
			'condition' => 'LOWER(company_name) = :company_name AND area_code = :area_code',
			'params' => array(
				':company_name' => strtolower($customer->company->company_name),
				':area_code' => substr($customer->phone, 1,3),
			),
		));	
		
		echo count($companyDid);
		
		echo '<br>';
		
		echo strtolower($customer->company->company_name);
		
		echo '<br>';
		
		echo $customer->phone;
		
		echo '<br>';
		
		echo substr($customer->phone, 1,3);
		
		echo '<br>';
		
		
	}

	public function actionLeadHopperPatch()
	{
		exit;
		
		//calapp - 94275
		$hopperEntry = LeadHopper::model()->findByPk(3132);
		$hopperEntry->type = LeadHopper::TYPE_CONTACT;
		$hopperEntry->callback_date = null;
		$hopperEntry->appointment_date = null;
		
		$lead = Lead::model()->findByPk($hopperEntry->lead_id);
		
		$disposition = SkillDisposition::model()->findByPk(10);
		
		$leadCallHistory = LeadCallHistory::model()->findByPk(531);
		
		if( $disposition->is_appointment_set == 1 )
		{
			// CustomerQueueViewer::model()->updateAll(array('dials_until_reset'=> 0), 'customer_id = ' . $hopperEntry->customer_id);
			
			if( isset($leadCallHistory->calendarAppointment) )
			{
				// $hopperEntry->status = LeadHopper::STATUS_CONFIRMATION;
				$hopperEntry->type = LeadHopper::TYPE_CONFIRMATION_CALL;
				
				$confirmationDate = $leadCallHistory->calendarAppointment->start_date;
				
				//if actual appointment date is on monday move it friday last week
				if( date('N', strtotime($confirmationDate)) == 1 )
				{
					$confirmationDate = date('Y-m-d', strtotime('last friday', strtotime($confirmationDate))).' '.date('H:i:s', strtotime($confirmationDate));
				}
				else
				{
					//move it to 1 business day before the actual appointment date
					$confirmationDate = date('Y-m-d', strtotime('-1 day', strtotime($confirmationDate))).' '.date('H:i:s', strtotime($confirmationDate));
				}
				
				$hopperEntry->calendar_appointment_id = $leadCallHistory->calendar_appointment_id;
				$hopperEntry->appointment_date = $confirmationDate;
			}
		}
		
		$hopperEntry->save(false);
		
		
		if( $disposition->is_complete_leads == 1 )
		{
			$lead->status = 3;
			
			//recyle module
			if(!empty($disposition->recycle_interval))
			{
				$time = strtotime(date("Y-m-d"));
				$finalDate = date("Y-m-d", strtotime("+".($disposition->recycle_interval * 30)." day", $time));
				$lead->recycle_date = $finalDate;
				$lead->recycle_lead_call_history_id = $leadCallHistory->id;
				$lead->recycle_lead_call_history_disposition_id = $leadCallHistory->disposition_id;
			}
		}
		
		if( $disposition->is_send_email == 1 )
		{					
			$emailMonitor = new EmailMonitor;
			
			$emailMonitor->setAttributes(array(
				'lead_id' => $leadCallHistory->lead_id,
				'agent_id' => $hopperEntry->agent_account_id,
				'customer_id' => $leadCallHistory->customer_id,
				'skill_id' => $leadCallHistory->lead->list->skill_id,
				'disposition_id' => $leadCallHistory->disposition_id,
				'calendar_appointment_id' => $leadCallHistory->calendar_appointment_id,
				'html_content' => $leadCallHistory->getReplacementCodeValues(),
			));
			
			$emailMonitor->save(false);
		}
		
		echo '<pre>';
		echo 'end..';
		exit;
	}

	public function actionCheckEmail()
	{
		$leadCallHistory = LeadCallHistory::model()->findByPk(1617496);
		
		echo $leadCallHistory->getReplacementCodeValues();
	}

	public function actionLatestCallAgent()
	{
		//get the latest agent that is calling the customer's leads
		$latestCallers = LeadHopper::model()->findAll(array(
			'group' => 'agent_account_id',
			'condition' => 'type != :lead_search AND agent_account_id IS NOT NULL AND status IN ("READY", "INCALL", "DISPO", "CONFLICT", "CALLBACK", "CONFIRMATION")',
			'params' => array(
				':lead_search' => LeadHopper::TYPE_LEAD_SEARCH,
			),
			// 'order' => 'date_created DESC',
		));
		
		if( $latestCallers )
		{
			foreach( $latestCallers as $latestCaller )
			{
				$callAgent .= $latestCaller->currentAgentAccount->getFullName();
				$callAgent .= ', ';
			}
			
			$callAgent = rtrim($callAgent, ', ');
			
			echo $latestCaller->customer->getFullName().' - '.$callAgent;
			echo '<br >';
		}
	}

	
	public function actionCheckTimeZone()
	{
		$nextAvailableCallingTime = '';
		
		$customerSkill = CustomerSkill::model()->findByPk(661);
		
		$customer = $customerSkill->customer;
		
		$skillScheduleHolder = array();
			
		$currentDateTime = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('America/Denver'));
		
		
		if( $customerSkill->is_custom_call_schedule == 1 )
		{
			$customCallSchedules = CustomerSkillSchedule::model()->findAll(array(
				'condition' => 'customer_skill_id = :customer_skill_id AND schedule_day = :schedule_day',
				'params' => array(
					':customer_skill_id' => $customerSkill->id,
					':schedule_day' => date('N'),
				),
			));
			
			if( $customCallSchedules )
			{
				foreach( $customCallSchedules as $customCallSchedule )
				{
					$skillScheduleHolder[$customer->id][$customCallSchedule->id]['schedule_start'] = date('g:i A', strtotime($customCallSchedule->schedule_start));
					$skillScheduleHolder[$customer->id][$customCallSchedule->id]['schedule_end'] = date('g:i A', strtotime($customCallSchedule->schedule_end));
				}
			}
		}
		else
		{	
			$skillSchedules = SkillSchedule::model()->findAll(array(
				// 'condition' => 'skill_id = :skill_id AND schedule_day = :schedule_day AND status=1 AND is_deleted=0',
				'condition' => 'skill_id = :skill_id AND schedule_day = :schedule_day',
				'params' => array(
					'skill_id' => $customerSkill->skill_id,
					':schedule_day' => date('N'),
				),
			));

			foreach($skillSchedules as $skillSchedule)
			{
				$skillScheduleHolder[$customer->id][$skillSchedule->id]['schedule_start'] = date('g:i A', strtotime($skillSchedule->schedule_start));
				$skillScheduleHolder[$customer->id][$skillSchedule->id]['schedule_end'] = date('g:i A', strtotime($skillSchedule->schedule_end));
			}
		}
	
		
		if( isset($skillScheduleHolder[$customer->id]) )
		{	
			foreach($skillScheduleHolder[$customer->id] as $sched)
			{	
				$timeZone = AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $customer->phone) );

				
				if( !empty($timeZone) )
				{
					$nextAvailableCallingTimeStart = new DateTime($sched['schedule_start'], new DateTimeZone(timezone_name_from_abbr($timeZone)) );
					$nextAvailableCallingTimeEnd = new DateTime($sched['schedule_end'], new DateTimeZone(timezone_name_from_abbr($timeZone)) );
					
					$nextAvailableCallingTimeStart->setTimezone(new DateTimeZone('America/Denver'));
					$nextAvailableCallingTimeEnd->setTimezone(new DateTimeZone('America/Denver'));
					
					
					
					// for debugging
					echo 'timeZone: ' . $timeZone;
					echo '<br>';
					echo 'schedule_start: ' . $sched['schedule_start'];
					echo '<br>';
					echo 'schedule_end: ' . $sched['schedule_end'];
					echo '<br>';
					echo 'currentDateTime: ' . $currentDateTime->format('g:i A');
					echo '<br>';
					echo 'nextAvailableCallingTimeStart: ' . $nextAvailableCallingTimeStart->format('g:i A');
					echo '<br>';
					echo 'nextAvailableCallingTimeEnd: ' . $nextAvailableCallingTimeEnd->format('g:i A');
					
					exit;
				
					$nextAvailableCallingTimeStart->setTimezone(new DateTimeZone('America/Denver'));
					$nextAvailableCallingTime = $nextAvailableCallingTimeStart->format('g:i A');
				
					if( strtotime($currentDateTime->format('g:i A')) >= strtotime($nextAvailableCallingTimeStart->format('g:i A')) && strtotime($currentDateTime->format('g:i A')) <= strtotime($nextAvailableCallingTimeEnd->format('g:i A')) )
					{
						$nextAvailableCallingTime = 'Now';
					}
					
					if( strtotime($currentDateTime->format('g:i A')) >= strtotime($nextAvailableCallingTimeStart->format('g:i A')) && strtotime($currentDateTime->format('g:i A')) >= strtotime($nextAvailableCallingTimeEnd->format('g:i A')) )
					{
						$nextAvailableCallingTime = 'Next Shift';
					}
				}
			}
		}
		else
		{
			$nextAvailableCallingTime = 'Next Shift';
		}
		
		
		echo $nextAvailableCallingTime;
	}

	public function actionCheckCalendar()
	{
		$customer = Customer::model()->find(203);
		
		$calendars = Calendar::model()->findAll(array(
			'condition' => 'customer_id = :customer_id AND status=1',
			'params' => array(
				':customer_id' => $customer->id,
			),
		));
		
		foreach( $calendars as $calendar )
		{
			$appointments = CalendarAppointment::model()->count(array(
				'condition' => 'calendar_id = :calendar_id',
				'params' => array(
					':calendar_id' => $calendar->id,
				),
			));
			
			echo 'calendar: ' . $calendar->name.' = '.$appointments;
			echo '<br>';
		}
	}

	public function actionCheckCron()
	{
		$customerSkills = CustomerSkill::model()->findAll(array(
			'with' => 'customer',
			'condition' => 't.status=1 AND customer.status=1 AND t.id IN (443, 661, 668)',
		));
		
		if( $customerSkills )
		{
			foreach( $customerSkills as $customerSkill )
			{
				$nextAvailableCallingTime = '';
				
				$isCallablCustomer = true;			
					
				if( $customerSkill->is_contract_hold == 1 )
				{
					if( time() >= strtotime($customerSkill->is_contract_hold_start_date) && time() <= strtotime($customerSkill->is_contract_hold_end_date) )
					{
						$isCallablCustomer = false;
					}
				}
				
				if( isset($customerSkill->customer) && $isCallablCustomer )
				{
					$contract = $customerSkill->contract;
					$nextAvailableCallingTime = $this->checkTimeZone($customerSkill);
				}
				
				echo $nextAvailableCallingTime;
				echo '<br>';
			}
		}
		
		echo '<br><br>end..';
	}
	
	
	public function actionSendEmail()
	{
		$leadCallHistory = LeadCallHistory::model()->findByPk(204424);
		
		$emailMonitor = new EmailMonitor;
		
		$emailMonitor->setAttributes(array(
			'lead_id' => $leadCallHistory->lead_id,
			'agent_id' => $authAccount->id,
			'customer_id' => $leadCallHistory->customer_id,
			'skill_id' => $leadCallHistory->lead->list->skill_id,
			'disposition_id' => $leadCallHistory->disposition_id,
			'calendar_appointment_id' => $leadCallHistory->calendar_appointment_id,
			'html_content' => $leadCallHistory->getReplacementCodeValues(),
			'status' => 2,
		));
		
		$emailMonitor->save(false);
	}
	
	public function sendIcal($model)
	{
		$valid = false;
		
		if( isset($model->disposition) )
		{
			$disposition = $model->disposition;
			
			$valid = true;
		}
		else
		{
			$latestCallHistory = LeadCallHistory::model()->find(array(
				'condition' => 'lead_id = :lead_id',
				'params' => array(
					':lead_id' => $model->lead_id,
				),
				'order' => 'date_created DESC',
			));

			if( $latestCallHistory && $latestCallHistory->is_skill_child == 1 )
			{
				$disposition = $latestCallHistory->skillChildDisposition;
				
				$valid = true;
			}
		}
		
		if( $valid )
		{		
			
			echo 'me';
			exit;
			
			// $ccs = !empty($disposition->cc) ?  explode(',', $disposition->cc) : array();
			$ccs = !empty($disposition->cc) ?  $disposition->cc : '';
			
			// if( $ccs )
			// {
				// foreach( $ccs as $cc )
				// {
					// $mail->AddCC($cc);
				// }
			// }

			// $bccs = !empty($disposition->bcc) ?  explode(',', $disposition->bcc) : array();
			$bccs = !empty($disposition->bcc) ?  $disposition->bcc . ', erwin.datu@engagex.com' : 'erwin.datu@engagex.com';
			
			// if( $bccs )
			// {
				// foreach( $bccs as $bcc )
				// {
					// $mail->AddBCC($bcc);
				// }
			// }
						
			$calendarAppointment = $model->calendarAppointment;
			$customer = $model->customer;
		
			$mailName = 'Engage Service';
			$emailAddress = 'service@engagex.com';
			$mime_boundary = "----Meeting Booking-".md5(time());
			
			$replyTo = $disposition->from;
			$mailSubject = $model->getReplacementCodeValues($disposition->subject);
			$emailMonitorContent = $model->html_content;
		
			//Create Email Headers
			$headers = "From: {$mailName} <".$emailAddress.">\n";
			$headers .= "Reply-To: {$replyTo} <".$emailAddress.">\n";

			$headers .= "MIME-Version: 1.0\n";
			$headers .= "Content-Type: multipart/alternative; boundary=\"$mime_boundary\"\n";
			$headers .= "Content-class: urn:content-classes:calendarmessage\n";
			
			if(!empty($bccs))
				$headers .= 'Bcc: '. $bccs . "\r\n";
			
			if(!empty($ccs))
				$headers .= 'Cc: '. $ccs . "\r\n";
			
			//Create Email Body (HTML)
			$message = '';
			$message .= "--$mime_boundary\n";
			$message .= "Content-Type: text/html; charset=UTF-8\n";
			$message .= "Content-Transfer-Encoding: 8bit\n\n";

			$message .= "<html>\n";
			$message .= "<body>\n";
			$message .= $emailMonitorContent;
			$message .= "</body>\n";
			$message .= "</html>\n";
			
			##Disposition Attachments ##
			
			if( $latestCallHistory && $latestCallHistory->is_skill_child == 1 )
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
					// $mail->AddAttachment( Yii::getPathOfAlias('webroot') . '/fileupload/' . $attachment->fileUpload->original_filename );
					$filenamePath = Yii::getPathOfAlias('webroot') . '/fileupload/' . $attachment->fileUpload->original_filename;
					$v = $attachment->fileUpload->original_filename;
					$file = $filenamePath;
					$file_size = filesize($file);
					$handle = fopen($file, "r");
					$content = fread($handle, $file_size);
					fclose($handle);
					$content = chunk_split(base64_encode($content));

					$message .= "Content-Type: application/octet-stream; name=\"".$v."\"\r\n"; // use different content types here
					$message .= "Content-Transfer-Encoding: base64\r\n";
					$message .= "Content-Disposition: attachment; filename=\"".$v."\"\r\n\r\n";
					$message .= $content."\r\n\r\n";
					$message .= "--".$mime_boundary."--"."\r\n";
				}
			}
			
			### ICAL ####
			if(isset($calendarAppointment))
			{
				$dtStart = date('Ymd\THis\Z', strtotime($calendarAppointment->start_date));
				$dtEnd = date('Ymd\THis\Z', strtotime($calendarAppointment->end_date));
				$dtStamp = date('Ymd\THis\Z');
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
				$ical .= "BEGIN:VEVENT\r\n";
				$ical .= "ORGANIZER;CN={$customerName}:MAILTO:".$customerEmail."\r\n";

				$ical .= "UID:".strtoupper(md5($event_id))."\r\n";
				$ical .= "SEQUENCE:".$sequence."\r\n";
				$ical .= "STATUS:".$status."\r\n";

				$ical .= "DTSTAMP:".$dtStamp."\r\n";
				$ical .= "DTSTART:".$dtStart."\r\n";
				$ical .= "DTEND:".$dtEnd."\r\n";

				$ical .= "LOCATION:".$location."\r\n";
				$ical .= "SUMMARY:".$summary."\r\n";
				$ical .= "DESCRIPTION:{$description}"."\r\n";

				$ical .= "END:VEVENT\r\n";
				$ical .= "END:VCALENDAR\r\n";

				$message .= "--$mime_boundary\n";							
				$message .= "Content-Type: text/calendar;name=\"meeting.ics\";method=REQUEST\n";
				$message .= "Content-Transfer-Encoding: 8bit\n\n";
				$message .= $ical;     
			}
			
			//SEND MAIL
			// $mail_sent = mail('jim.campbell@engagex.com', $mailSubject, $message, $headers );
			// $mail_sent = mail('markjuan169@gmail.com', $mailSubject, $message, $headers );
			mail('erwin.datu@engagex.com', $mailSubject, $message, $headers );
			// $mail_sent = mail($model->customer->email_address, $mailSubject, $message, $headers );

			if($mail_sent)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		
		
		return false;
	}

	public function actionCheckHopper()
	{
		$customerWhiteQueues = CustomerQueueViewer::model()->findAll(array(
			'condition' => 'available_leads > 0',
			'order' => 'priority DESC',
		));
		
		$ctr = 1;
		$totalLeads = 0;
		
		if( $customerWhiteQueues )
		{
			foreach( $customerWhiteQueues as $customerQueue )
			{
				$customerSkill = CustomerSkill::model()->find(array(
					'condition' => 'customer_id = :customer_id AND skill_id = :skill_id AND status=1',
					'params' => array(
						':customer_id' => $customerQueue->customer_id,
						':skill_id' => $customerQueue->skill_id,
					),
				));

				$customer = Customer::model()->findByPk($customerSkill->customer_id);
				$skill = Skill::model()->findByPk($customerSkill->skill_id);
				
				// $nextAvailableCallingTime = $this->checkTimeZone($customerSkill);
				
				$lists = Lists::model()->findAll(array(
					'together' => true,
					'condition' => 't.customer_id = :customer_id AND skill_id = :skill_id AND t.status=1',
					'params' => array(
						':customer_id' => $customerQueue->customer_id,
						':skill_id' => $customerQueue->skill_id,
					),
				));
				
				if( $customerQueue->next_available_calling_time == 'Now' )
				{
					$customerSkill = CustomerSkill::model()->find(array(
						'condition' => 'customer_id = :customer_id AND skill_id = :skill_id AND status=1',
						'params' => array(
							':customer_id' => $customerQueue->customer_id,
							':skill_id' => $customerQueue->skill_id,
						),
					));
					
					$customer = $customerSkill->customer;
					
					$lists = Lists::model()->findAll(array(
						'together' => true,
						'condition' => 't.customer_id = :customer_id AND skill_id = :skill_id AND t.status=1',
						'params' => array(
							':customer_id' => $customerQueue->customer_id,
							':skill_id' => $customerQueue->skill_id,
						),
					));

					if( $customer->status == 1 )
					{
						echo $ctr.'.) ID: '.$customer->id.' => '.$customer->firstname .' '.$customer->lastname;
						echo '<br>';
						
						if( $lists )
						{
							foreach( $lists as $list )
							{
								if( $list->status == 1 )
								{
									if( $list->lead_ordering == 2 )
									{
										$order = 'last_name ASC';
									}
									elseif( $list->lead_ordering == 3 )
									{
										$order = 'custom_date DESC';
									}
									else
									{
										$order = 'RAND()';
									}
									
									//get callable leads
									$leads = Lead::model()->findAll(array(
										'condition' => 'list_id = :list_id AND t.type=1 AND t.status=1 AND t.number_of_dials < :skill_max_dials',
										'params' => array(
											':list_id' => $list->id,
											':skill_max_dials' => $customerSkill->skill->max_dials,
										),
										'order' => $order,
										'limit' => 20,
									));
									
									$leadCounter = LeadHopper::model()->count(array(
										'condition' => 't.status IN ("READY", "INCALL") AND t.type=1 AND t.customer_id = :customer_id',
										'params' => array(
											':customer_id' => $customerQueue->customer_id,
										),
									));
									
									if( count($leads) > 0 )
									{
										if( $leadCounter == 0  )
										{
											$totalLeads += count($leads);
										}
										
										echo $list->name.' - '.count($leads).' | counter: ' . $leadCounter;
										echo '<br>';
									}
								}
							}
						}
						
						echo '<br>';
						echo '<br>';
					
						$ctr++;
					}
				}
			}
		}
		
		echo '<br>';
		echo '<br>';
		echo 'totalLeads: ' . $totalLeads;
	}

	
	public function actionCheckConfirms()
	{
		//remove leads with end call, dispo and done status on hopper, enable confirms and callback
		$leadHopperEntries = LeadHopper::model()->findAll(array(
			'condition' => 'status IN ("DONE", "READY") AND type=3 AND date(appointment_date) = "2016-04-01"',
		));
		
		if( $leadHopperEntries )
		{	
			foreach( $leadHopperEntries as $leadHopperEntry )
			{
				$lead = $leadHopperEntry->lead;
				$customer = $leadHopperEntry->customer;
				
				if( !empty($lead->timezone) )
				{
					$timeZone = $lead->timezone;
				}
				else
				{
					$timeZone = AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $customer->phone) );
				}
				
				$currentDateTime = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('America/Denver'));
				$currentDateTime->setTimezone(new DateTimeZone('America/Denver'));

				$leadLocalTime = $currentDateTime->setTimezone(new DateTimeZone(timezone_name_from_abbr($timeZone)));
				
				// if( $leadHopperEntry->status == 'DONE' && in_array($leadHopperEntry->type, array(LeadHopper::TYPE_CONTACT, LeadHopper::TYPE_LEAD_SEARCH)) )
				// {
					// $leadHopperEntry->delete();
				// }

				// if( ($leadHopperEntry->status == 'DONE') && $leadHopperEntry->type == LeadHopper::TYPE_CALLBACK && strtotime($leadLocalTime->format('Y-m-d g:i a')) >= strtotime($leadHopperEntry->callback_date) && $this->checkLeadRetryTime($lead) )
				// {					
					// $leadHopperEntry->status = 'READY';
					// $leadHopperEntry->save(false);
				// }
				 
				
				if( in_array($leadHopperEntry->status, array('DONE', 'READY')) && $leadHopperEntry->type == LeadHopper::TYPE_CONFIRMATION_CALL && date('Y-m-d') >= date('Y-m-d', strtotime($leadHopperEntry->appointment_date)) )
				{
					$customerSkill = CustomerSkill::model()->find(array(
						'condition' => 'customer_id = :customer_id AND skill_id = :skill_id',
						'params' => array(
							':customer_id' => $leadHopperEntry->customer_id,
							':skill_id' => $leadHopperEntry->skill_id,
						),
					));
					
					$lead = $leadHopperEntry->lead;
					
					if( $customerSkill )
					{
						echo $lead->getFullName();
						echo '<br />';
						echo $leadHopperEntry->lead_timezone;
						echo '<br />';
						
						if( $this->checkTimeZone($customerSkill, 'lead', $lead) == 'Now' )
						{
							echo 'Available';
							echo '<br />';
							
							// $leadHopperEntry->status = 'READY';
							// $leadHopperEntry->save(false);
						}
						else
						{
							echo 'Not Available';
							echo '<br />';
						}
						
						echo '<br />';
						echo '<hr>';
						echo '<br />';
					}
				}
			}
		}
		
		echo '<br><br>end...';
	}

	
	public function actionTestMailer()
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
		
		// $mail->SetFrom('service@engagex.com');
		$mail->SetFrom('service@engagex.com', 'Engagex Service', 0);
  
		$mail->Subject = 'test';
		
		$mail->AddAddress('engagex@aaawa.com');
		$mail->AddCC('jim.campbell@engagex.com');
		$mail->AddCC('leonel.imperial@engagex.com');
		$mail->AddBCC('erwin.datu@engagex.com');
		 
		$mail->MsgHTML( 'please reply if you receive this' ); 
								
		if( !$mail->send() ) 
		{
			echo "Mailer Error: " . $mail->ErrorInfo;
		} 
		else 
		{
			echo "Email sent!";
		}
	}

	
	public function actionCheckDst()
	{
		$timezone = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('America/Chicago') );
		
		$timezone->setTimezone(new DateTimeZone('America/New_York'));
		
		echo $timezone->format('g: i A');
	}

	
	public function actionPhpInfo()
	{
		phpinfo();
	}

	
	public function actionCheckBilling()
	{
		Yii::import('application.vendor.*');
		require ('anet_php_sdk/AuthorizeNet.php');
		
		//sandbox
		// define("AUTHORIZENET_API_LOGIN_ID", "5CzTeH72f98D");	
		// define("AUTHORIZENET_TRANSACTION_KEY", "8n25bCL72432SpR9");	
		// define("AUTHORIZENET_SANDBOX", true);
					
		//live
		define("AUTHORIZENET_API_LOGIN_ID", "8Wtz3C6a");	
		define("AUTHORIZENET_TRANSACTION_KEY", "8YEBD7n3Z64r3x39");
		define("AUTHORIZENET_SANDBOX", false);
		
		// $defaultCreditCard = CustomerCreditCard::model()->findByPk(19);
		
		// $authorizeTransaction = new AuthorizeNetAIM;
		
		// $authorizeTransaction->setSandbox(AUTHORIZENET_SANDBOX);
		
		// $authorizeTransaction->setFields(array(
			// 'invoice_num' => ,
			// 'amount' => number_format('1.00', 2),
			// 'first_name' => $defaultCreditCard->first_name,
			// 'last_name' => $defaultCreditCard->last_name,
			// 'email' => 'erwin.datu@engagex.com',
			// 'card_num' => $defaultCreditCard->credit_card_number, 
			// 'card_code' => $defaultCreditCard->security_code,
			// 'exp_date' => $defaultCreditCard->expiration_month . $defaultCreditCard->expiration_year,
			// 'address' => $defaultCreditCard->address,
			// 'city' => $defaultCreditCard->city,
			// 'state' => $defaultCreditCard->state,
			// 'zip' => $defaultCreditCard->zip,
		// ));
		
		// $response = $authorizeTransaction->authorizeAndCapture();
									
		$request  = new AuthorizeNetTD;
		$response_TransactionDetails = $request->getTransactionDetails(20171312863);
		
		echo '<pre>';
		// print_r($response);
			print_r($response_TransactionDetails);	
		echo '</pre>';
		
		echo '<br><br>';
			
		
		if($response_TransactionDetails->xml->messages->resultCode == 'Ok')
		{
			$transaction_Details = $response_TransactionDetails->xml->transaction;
			$order = $transaction_Details->order;
			$anetCustomer = $transaction_Details->customer;
			$billTo = $transaction_Details->billTo;

			$updateBillingRecord = CustomerBilling::model()->find(array(
				'condition' => 'anet_transId = :anet_transId',
				'params' => array(
					':anet_transId' => $transaction_Details->transId
				),
			));
			
			echo 'transId: ' . $transaction_Details->transId;
			
			echo '<br><br>';
			
			echo 'updateBillingRecord: ' . count($updateBillingRecord);
			
			echo '<br><br>';
				
			if($updateBillingRecord)
			{
				$updateBillingRecord->setAttributes(array(
					'anet_transId' => $transaction_Details->transId,
					'anet_invoiceNumber' => $order->invoiceNumber,
					'anet_submitTimeUTC' => $transaction_Details->submitTimeUTC,
					'anet_submitTimeLocal' => $transaction_Details->submitTimeLocal,
					'anet_transactionType' => $transaction_Details->transactionType,
					'anet_transactionStatus' =>$transaction_Details->transactionStatus,
					'anet_responseCode' => $transaction_Details->responseCode,
					'anet_responseReasonCode'=> $transaction_Details->responseReasonCode,
					'anet_responseReasonDescription'=> $transaction_Details->responseReasonDescription,
					'anet_authCode'=> $transaction_Details->authCode,
					'anet_AVSResponse'=> $transaction_Details->AVSResponse,
					'anet_cardCodeResponse'=> $transaction_Details->cardCodeResponse,
					'anet_authAmount'=> $transaction_Details->authAmount,
					'anet_settleAmount'=> $transaction_Details->settleAmount,
					'anet_taxExempt'=> $transaction_Details->taxExempt,
					'anet_customer_Email'=> $anetCustomer->email,
					'anet_billTo_firstName'=> $billTo->firstName,
					'anet_billTo_lastName'=> $billTo->lastName,
					'anet_billTo_address'=> $billTo->address,
					'anet_billTo_city'=> $billTo->city,
					'anet_billTo_state'=> $billTo->state,
					'anet_billTo_zip'=> $billTo->zip,
					'anet_recurringBilling' => $transaction_Details->recurringBilling,
					'anet_product' => $transaction_Details->product,
					'anet_marketType' => $transaction_Details->marketType
				));
				
				if($updateBillingRecord->save(false))
				{
					echo 'Saved';
				}
			}
		}
	}

	
	public function actionCheckNextLead()
	{
		$authAccount = Yii::app()->user->account;
		
		//prioritize leads that is in Confirm type
		// $existingleadHopperEntry = LeadHopperTest::model()->find(array(
			// 'condition' => '
				// t.type != :lead_search 
				// AND t.status IN ("READY","INCALL") AND t.type IN (3,6,7)
			// ',
			// 'params' => array(
				// ':lead_search' => LeadHopper::TYPE_LEAD_SEARCH,
			// ),
			// 'with'=>array('customer'),
		// ));	
		

		$existingleadHopperEntry = LeadHopper::model()->find(array(
			'condition' => '
				t.type != :lead_search 
				AND ( (t.status IN ("READY", "INCALL", "HOLD", "DISPO") AND t.type=1) OR (t.status IN ("READY","INCALL") AND t.type IN (2,5)) )
				AND queueViewer.dials_until_reset > 0
				AND queueViewer.next_available_calling_time = "Now"
			',
			'params' => array(
				':lead_search' => LeadHopper::TYPE_LEAD_SEARCH,
			),
			'order' => 'queueViewer.priority DESC',
			'with'=>array('customer', 'customer.queueViewer'),
		));	
		
		if( $existingleadHopperEntry )
		{
			echo '<pre>';
				print_r($existingleadHopperEntry->attributes);
				print_r($existingleadHopperEntry->customer->queueViewer->attributes);
			exit;
		}
		else
		{
			echo 'Hopper is empty';
		}
	}	

	public function actionCheckCallAgent()
	{
		//get the latest agent that is calling the customer's leads
		$latestCallers = LeadHopper::model()->findAll(array(
			'group' => 'agent_account_id',
			'condition' => 'type != :lead_search AND agent_account_id IS NOT NULL AND status IN ("INCALL") AND t.type IN (1)',
			'params' => array(
				':lead_search' => LeadHopper::TYPE_LEAD_SEARCH,
			),
		));
		
		if( $latestCallers )
		{
			$callAgent = '';
			
			foreach( $latestCallers as $latestCaller )
			{
				$callAgent .= $latestCaller->currentAgentAccount->getFullName();
				$callAgent .= ', ';
				
				echo $latestCaller->customer->getFullName().' => ',rtrim($callAgent, ', ');
				echo '<br>';
				echo '<br>';
			}
		}
	}

	public function actionTestHost()
	{
		echo $_SERVER['HTTP_HOST'];
	}

	public function actionTime()
	{
		if( time() >= strtotime('today 7:00 am') ) 
		{
			echo 'yes';
		}
	}
	
	public function actionCheckCustomer()
	{
		$customerSkill = CustomerSkill::model()->find(array(
			'condition' => 'customer_id=139',
		));
		
		$customerIsCallable = false;	
		
		
		echo 'contract: ' . $customerSkill->contract->contract_name;
			echo '<br>';
			echo '<br>';
		echo 'customer name: ' . $customerSkill->customer->getFullName();
			echo '<br>';
			echo '<br>';
		echo 'customer status: ' . $customerSkill->customer->status;
			echo '<br>';
			echo '<br>';
		echo 'customer deleted: ' . $customerSkill->customer->is_deleted;
			echo '<br>';
			echo '<br>';
		echo 'start month: ' . date('m/d/Y g:i A', strtotime($customerSkill->start_month));
			echo '<br>';
			echo '<br>';
		echo 'start end: ' . date('m/d/Y g:i A', strtotime($customerSkill->end_month));
			echo '<br>';
			echo '<br>';
		echo 'contract hold: ' . $customerSkill->is_contract_hold;
			echo '<br>';
			echo '<br>';
		echo 'contract hold start date: ' . $customerSkill->is_contract_hold_start_date;
			echo '<br>';
			echo '<br>';
		echo 'contract hold end date: ' . $customerSkill->is_contract_hold_end_date;
			echo '<br>';
			echo '<br>';
		
		echo time();
		
			echo '<br>';
			echo '<br>';
			
		echo strtotime($customerSkill->end_month);
			echo '<br>';
			echo '<br>';
			
		if( time() >= strtotime($customerSkill->end_month) )
		{
			echo 'current time is greater than contract end month';
		}
			
		echo '<br>';
		echo '<br>';


		if( isset($customerSkill->contract) && isset($customerSkill->customer) && $customerSkill->customer->status == 1 && $customerSkill->customer->is_deleted == 0 && time() >= strtotime($customerSkill->start_month) )
		{
			$customerIsCallable = true;
		}
		
		if( $customerSkill->is_contract_hold == 1 )
		{
			if( !empty($customerSkill->is_contract_hold_start_date) && !empty($customerSkill->is_contract_hold_end_date) )
			{
				if( time() >= strtotime($customerSkill->is_contract_hold_start_date) && time() <= strtotime($customerSkill->is_contract_hold_end_date) )
				{
					$customerIsCallable = false;
				}
			}
		}
		
		if( !empty($customerSkill->end_month) && date('Y', strtotime($customerSkill->end_month)) >= 2015 )
		{
			if( time() >= strtotime($customerSkill->end_month) )
			{
				$customerIsCallable = false;
			}
		}
		
		if( $existingQueueViewer )
		{
			if( !empty($existingQueueViewer->removal_start_date) && !empty($existingQueueViewer->removal_end_date) )
			{
				if( time() >= strtotime($existingQueueViewer->removal_start_date) && time() <= strtotime($existingQueueViewer->removal_end_date) )
				{
					$customerIsCallable = false;
				}
			}
		}
		
		echo 'customerIsCallable: ' . $customerIsCallable; 

	}

	
	public function actionCheckCustomer2()
	{
		$customerIsCallable = true;
		
		$model = CustomerQueueViewer::model()->find(array(
			'condition' => 'customer_id = 49',
		));
		
		if( $model )
		{
			echo 'removal start date: ' . $model->removal_start_date;
			echo '<br><br>';
			echo 'removal end date: ' . $model->removal_end_date;
			echo '<br><br>';
			echo 'start: ' . date('m/d/Y g:i A', strtotime($model->removal_start_date));
			echo '<br><br>';
			echo 'end: ' . date('m/d/Y g:i A', strtotime($model->removal_end_date));
			echo '<br><br>';
			echo 'current time: ' . date('m/d/Y g:i A');
			echo '<br><br>';
			echo '<br><br>';
			
			if( !empty($model->removal_start_date) && !empty($model->removal_end_date) )
			{
				if( time() >= strtotime($model->removal_start_date) && time() <= strtotime($model->removal_end_date) )
				{
					$customerIsCallable = false;
				}
			}
		}
		
		echo $customerIsCallable;
	}
	
	public function actionAppointmentPatch()
	{
		exit;
		
		$models = CalendarAppointment::model()->findAll(array(
			'with' => 'calendar',
			'condition' => 'calendar.customer_id = :customer_id AND t.lead_id IS NOT NULL AND t.status=2 AND t.title=""',
			'params' => array(
				':customer_id' => 771
			),
		));
		
		if( $models )
		{
			echo 'count: ' . count($models).'<br><br>';

			echo '<pre>';
		
			foreach( $models as $model )
			{
				$model->title = 'APPOINTMENT SET';
				$model->save(false);
			}
		}
		else
		{
			echo 'no results found.';
		}
	}

	public function actionCheckCustomerStaff()
	{
		$customerOfficeStaffs = CustomerOfficeStaff::model()->findAll(array(
			'condition' => 'customer_id = :customer_id AND is_received_email > 0 AND is_deleted=0 AND (email_address IS NOT NULL OR email_address != "")',
			'params' => array(
				':customer_id' => 704,
			),
		));
		
		if( $customerOfficeStaffs )
		{
			foreach( $customerOfficeStaffs as $customerOfficeStaff )
			{
				echo $customerOfficeStaff->staff_name.' - '.$customerOfficeStaff->email_address;
				echo '<br>';
			}
		}
	}

	public function actionLeadHopperTest()
	{
		$lists = Lists::model()->findAll(array(
			'together' => true,
			'condition' => 't.customer_id = :customer_id',
			'params' => array(
				':customer_id' => 48,
			),
		));

		
		if( $lists )
		{
			foreach( $lists as $list )
			{
				$ctr = 1;
				
				//get callable leads
				$leads = Lead::model()->findAll(array(
					'condition' => 'list_id = :list_id AND t.type=1 AND t.status=1',
					'params' => array(
						':list_id' => $list->id,						
					),
					'limit' => 20,
				));
				
				foreach( $leads as $lead )
				{
					$existingHopperEntry = LeadHopper::model()->find(array(
						'condition' => 'lead_id = :lead_id',
						'params' => array(
							':lead_id' => $lead->id,
						),
					));

					if( empty($existingHopperEntry) )
					{
						$hopperEntry = new LeadHopper;
						
						$hopperEntry->setAttributes(array(
							'lead_id' => $lead->id,
							'list_id' => $list->id,
							'skill_id' => $list->skill_id,
							'customer_id' => $list->customer_id,
							'lead_language' => $lead->language,
							'lead_timezone' => $lead->timezone,
						));
						
						if( $hopperEntry->save(false) )
						{
							$ctr++;
						}
					}
					
					echo $ctr;
				}
			}
		}
	}

	public function actionGetBlueAppointments()
	{
		$models = CalendarAppointment::model()->findAll(array(
			'with' => array('calendar', 'calendar.customer'),
			'condition' => 'calendar.status=1 AND customer.status=1 AND t.lead_id IS NOT NULL AND t.title=""',
			'group' => 'customer.id',  
			'order' => 'customer.firstname ASC'
		));

		foreach( $models as $model )
		{
			$blueApps = CalendarAppointment::model()->findAll(array(
				'with' => 'calendar',
				'condition' => 'calendar.customer_id = :customer_id AND t.lead_id IS NOT NULL AND t.title=""',
				'params' => array(
					':customer_id' => $model->calendar->customer_id,
				),
			));
			
			// foreach( $blueApps as $blueApp )
			// {
				// $blueApp->title = 'APPOINTMENT SET';
				// $blueApp->save(false);
			// }
			
			echo $model->calendar->customer->getFullName().' - ' . count($blueApps);
			echo '<br><br>';
		}
	}

	
	public function actionCountAppointments()
	{
		$models = EmailMonitor::model()->findAll(array(
			'with' => 'disposition',
			'condition' => 'disposition.is_appointment_set=1 AND status !=3 AND DATE(date_created) = "2016-04-01"',
		));
		
		echo count($models);
	}
	
	
	private function checkTimeZone($customerSkill, $type='customer', $lead=null)
	{
		$nextAvailableCallingTime = '';
		
		$customer = $customerSkill->customer;
		
		$skillScheduleHolder = array();
			
		$currentDateTime = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('America/Denver'));
		$currentDateTime->setTimezone(new DateTimeZone('America/Denver')); 

		
		if( $customerSkill->is_custom_call_schedule == 1 )
		{
			$customCallSchedules = CustomerSkillSchedule::model()->findAll(array(
				'condition' => 'customer_skill_id = :customer_skill_id AND schedule_day = :schedule_day',
				'params' => array(
					':customer_skill_id' => $customerSkill->id,
					':schedule_day' => date('N'),
				),
			));
			
			if( $customCallSchedules )
			{
				foreach( $customCallSchedules as $customCallSchedule )
				{
					$skillScheduleHolder[$customer->id][$customCallSchedule->id]['schedule_start'] = date('g:i A', strtotime($customCallSchedule->schedule_start));
					$skillScheduleHolder[$customer->id][$customCallSchedule->id]['schedule_end'] = date('g:i A', strtotime($customCallSchedule->schedule_end));
				}
			}
		}
		else
		{	
			$skillSchedules = SkillSchedule::model()->findAll(array(
				// 'condition' => 'skill_id = :skill_id AND schedule_day = :schedule_day AND status=1 AND is_deleted=0',
				'condition' => 'skill_id = :skill_id AND schedule_day = :schedule_day',
				'params' => array(
					'skill_id' => $customerSkill->skill_id,
					':schedule_day' => date('N'),
				),
			));

			foreach($skillSchedules as $skillSchedule)
			{
				$skillScheduleHolder[$customer->id][$skillSchedule->id]['schedule_start'] = date('g:i A', strtotime($skillSchedule->schedule_start));
				$skillScheduleHolder[$customer->id][$skillSchedule->id]['schedule_end'] = date('g:i A', strtotime($skillSchedule->schedule_end));
			}
		}
	
		
		if( isset($skillScheduleHolder[$customer->id]) )
		{	
			foreach($skillScheduleHolder[$customer->id] as $sched)
			{	
				if( $type == 'customer' )
				{
					$timeZone = $customer->getTimeZone();
				}
				else
				{
					if( !empty($lead->timezone) )
					{
						$timeZone = $lead->timezone;
					}
					else
					{
						$timeZone = $customer->getTimeZone();
					}
				}
				
				if( !empty($timeZone) )
				{
					$timeZone = timezone_name_from_abbr($timeZone);
													
					// if( strtoupper($lead->timezone) == 'AST' )
					// {
						// $timeZone = 'America/Puerto_Rico';
					// }
					
					// if( strtoupper($lead->timezone) == 'ADT' )
					// {
						// $timeZone = 'America/Halifax';
					// }
					
					if( $type == 'customer' )
					{
						$nextAvailableCallingTimeStart = new DateTime($sched['schedule_start'], new DateTimeZone($timeZone) );
						$nextAvailableCallingTimeEnd = new DateTime($sched['schedule_end'], new DateTimeZone($timeZone) );
						
						$nextAvailableCallingTimeStart->setTimezone(new DateTimeZone('America/Denver'));
						$nextAvailableCallingTimeEnd->setTimezone(new DateTimeZone('America/Denver'));

						$nextAvailableCallingTimeStart->setTimezone(new DateTimeZone('America/Denver'));
						$nextAvailableCallingTime = $nextAvailableCallingTimeStart->format('g:i A');

						if( strtotime($currentDateTime->format('g:i A')) >= strtotime($nextAvailableCallingTimeStart->format('g:i A')) && strtotime($currentDateTime->format('g:i A')) <= strtotime($nextAvailableCallingTimeEnd->format('g:i A')) )
						{
							$nextAvailableCallingTime = 'Now';
						}
						
						if( strtotime($currentDateTime->format('g:i A')) >= strtotime($nextAvailableCallingTimeStart->format('g:i A')) && strtotime($currentDateTime->format('g:i A')) >= strtotime($nextAvailableCallingTimeEnd->format('g:i A')) )
						{
							$nextAvailableCallingTime = 'Next Shift';
						}
						
						if( in_array($nextAvailableCallingTime, array('7:00 AM', '7:30 AM', '8:00 AM', '8:30 AM', '9:00 AM')) && time() >= strtotime('today 4:00 am') )
						{
							$nextAvailableCallingTime = 'Now';
						}
					}
					else 
					{
						$nextAvailableCallingTimeStart = new DateTime($sched['schedule_start'], new DateTimeZone($timeZone) );
						$nextAvailableCallingTimeEnd = new DateTime($sched['schedule_end'], new DateTimeZone($timeZone) );
						
						$nextAvailableCallingTimeStart->setTimezone(new DateTimeZone('America/Denver'));
						$nextAvailableCallingTimeEnd->setTimezone(new DateTimeZone('America/Denver'));

						$nextAvailableCallingTimeStart->setTimezone(new DateTimeZone('America/Denver'));
						$nextAvailableCallingTime = $nextAvailableCallingTimeStart->format('g:i A');
						
						$currentDateTime = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('America/Denver'));
						$leadLocalTime = $currentDateTime->setTimezone(new DateTimeZone($timeZone));
					
						if( strtotime($leadLocalTime->format('g:i A')) >= strtotime($sched['schedule_start']) && strtotime($leadLocalTime->format('g:i A')) <= strtotime($sched['schedule_end']) )
						{
							$nextAvailableCallingTime = 'Now';
						}
						
						if( strtotime($leadLocalTime->format('g:i A')) >= strtotime($sched['schedule_start']) && strtotime($leadLocalTime->format('g:i A')) >= strtotime($sched['schedule_end']) )
						{
							$nextAvailableCallingTime = 'Next Shift';
						}
						
						if( in_array($nextAvailableCallingTime, array('7:00 AM', '7:30 AM', '8:00 AM', '8:30 AM', '9:00 AM')) && time() >= strtotime('today 4:00 am') )
						{
							$nextAvailableCallingTime = 'Now';
						}
					}
				}
			}
		}
		else
		{
			$nextAvailableCallingTime = 'Next Shift';
		}
		
		return $nextAvailableCallingTime;
	}

	private function checkLeadRetryTime($lead)
	{
		$leadIsCallable = false;
		
		$latestCall = LeadCallHistory::model()->find(array(
			'condition' => 'lead_id = :lead_id',
			'params' => array(
				':lead_id' => $lead->id,
			),
			'order' => 'date_created DESC'
		));
		
		
		if( $latestCall )
		{
			if( $latestCall->is_skill_child == 1 )
			{
				if( isset($latestCall->skillChildDisposition) && !empty($latestCall->skillChildDisposition->retry_interval) )
				{
					if( time() >= (strtotime($latestCall->end_call_time) + $latestCall->skillChildDisposition->retry_interval) )
					{
						$leadIsCallable = true;
					}
				}
			}
			else
			{
				if( isset($latestCall->skillDisposition) && !empty($latestCall->skillDisposition->retry_interval) )
				{
					if( time() >= (strtotime($latestCall->end_call_time) + $latestCall->skillDisposition->retry_interval) )
					{
						$leadIsCallable = true;
					}
				}
			}
		}
		else
		{
			$leadIsCallable = true;
		}
		
		return $leadIsCallable;
	}
	
	private function runCustomerEvaluation($customerSkill, $existingQueueViewer)
	{	
		$priority = 0;
		$pace = 0;
		$currentDials = 0;
		$availableLeads = 0;
		$notCompletedLeads = 0;
		$totalLeads = 0;
		$totalPotentialDials = 0;
		$calledLeadCount = 0;
		$dialsNeeded = 0;
		$maxDials = 0;
		$appointmentSetCount = 0;
		
		$availableCallingBlocks = '';
		$availableCallingBlock_A = '';
		$availableCallingBlock_B = '';
		$availableCallingBlock_C = '';
		
		$callAgent = '';
		
		$nextAvailableCallingTime = $this->checkTimeZone($customerSkill);
		
		$numberOfWorkingDays = $this->getWorkingDaysForThisMonth(date('Y-m-1'), date('Y-m-t'), 'totalCount');
		$dialingDaysInBillingCycle = $this->getWorkingDaysForThisMonth(date('Y-m-1'), date('Y-m-t'), 'pastCount');	
		
		$customer = Customer::model()->findByPk($customerSkill->customer_id);
		$skill = Skill::model()->findByPk($customerSkill->skill_id);
		$contract = $customerSkill->contract;
		
		$maxDials = $skill->max_dials;
		
		$leads = Lead::model()->findAll(array(
			'with' => array('list'),
			'condition' => 'list.skill_id = :skill_id AND list.customer_id = :customer_id AND t.type=1 AND t.status=1',
			'params' => array(
				':skill_id' => $skill->id,
				':customer_id' => $customer->id,
			),
		));

			
		if($contract->fulfillment_type != null )
		{
			if( $contract->fulfillment_type == Contract::TYPE_FULFILLMENT_GOAL_VOLUME )
			{
				if( !empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME]) )
				{
					foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME] as $key => $subsidyLevel )
					{
						$customerSkillLevelArray = $customerSkill->getCustomerSkillLevelArray();
						$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;

						if( $customerSkillLevelArrayGroup != null )
						{							
							if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
							{
								$totalLeads += ( $subsidyLevel['goal'] * $customerSkillLevelArrayGroup->quantity );
							}
						}
					}
				}
			}
			else
			{
				if( !empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME]) )
				{
					foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME] as $key => $subsidyLevel )
					{
						$customerSkillLevelArray = $customerSkill->getCustomerSkillLevelArray();
						
						$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;
						
						if( $customerSkillLevelArrayGroup != null )
						{
							if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
							{
								$totalLeads += ( $subsidyLevel['high'] * $customerSkillLevelArrayGroup->quantity );
							}
						}
					}
				}
			}
		}
	
		if( count($leads) > 0 && $totalLeads > 0 )
		{
			echo count($leads);
			
			echo '<hr>';
			
			foreach( $leads as $lead )
			{
				if( $lead->type == 1 && $lead->status == 1 && $lead->list->status == 1 && $lead->number_of_dials < $maxDials )
				{
					$leadIsCallable = false;
					
					$latestCall = LeadCallHistory::model()->find(array(
						'condition' => 'lead_id = :lead_id',
						'params' => array(
							':lead_id' => $lead->id,
						),
						'order' => 'date_created DESC'
					));
					
					
					if( $latestCall )
					{
						if( time() >= (strtotime($latestCall->end_call_time) + $latestCall->skillDisposition->retry_interval) )
						{
							$leadIsCallable = true;
						}

					}
					else
					{
						$leadIsCallable = true;
					}
					
					if( $leadIsCallable )
					{
						$availableLeads++;
					}
					else
					{
						$notCompletedLeads++;
					}
					
					echo 'name: ' . $lead->getFullName();
					echo '<br>';
					echo '<br>';
					echo 'type: ' . $lead->type;
					echo '<br>';
					echo '<br>';
					echo 'status: ' . $lead->status;
					echo '<br>';
					echo '<br>';
					echo 'list status: ' . $lead->list->status;
					echo '<br>';
					echo '<br>';
					echo 'dials: ' . $lead->number_of_dials;
					echo '<br>';
					echo '<br>';
					echo 'max dials: ' .  $maxDials;
					echo '<br>';
					echo '<br>';
					
					if( $latestCall )
					{
						echo 'next call time after: ' . date('m/d/Y g:i A', (strtotime($latestCall->end_call_time) + $latestCall->skillDisposition->retry_interval));
						echo '<br>';
						echo '<br>';
						echo $latestCall->skillDisposition->skill_disposition_name;
						echo '<br>';
						echo '<br>';
						echo $latestCall->disposition;
						echo '<br>';
						echo '<br>';
					}
					
					echo '<br>----<br>'; 
					echo '---------------------------------------------------------------------------'; 
					echo '<br>----<br>'; 
				}
				
				if( $lead->number_of_dials > 0 )
				{
					$currentDials = $currentDials + $lead->number_of_dials;
					
					$calledLeadCount++;
				}
				
							
				//get total potential dials for lead volume
				if( $customerSkill->contract->fulfillment_type == 2 )
				{
					$leadCallLogs = LeadCallHistory::model()->count(array(
						'condition' => 'lead_id = :lead_id',
						'params' => array(
							':lead_id' => $lead->id,
						),
					));
				
					$totalPotentialDials += ($maxDials - $leadCallLogs);
				}
				
				
				//get available calling blocks
				if($availableCallingBlock_A == '' && $lead->number_of_dials == 0)
				{
					$availableCallingBlock_A = 'A';
					$availableCallingBlock_B = 'B';
					$availableCallingBlock_C = 'C';
				}
	
				if( $availableCallingBlock_B == '' )
				{
					$periodB = LeadCallHistory::model()->find(array(
						'condition' => 'lead_id = :lead_id AND dial_number=1',
						'params' => array(
							':lead_id' => $lead->id,
						),
					));
					
					if( count($periodB) > 0)
					{
						$availableCallingBlock_B = 'B';
					}
				}
				
				if( $availableCallingBlock_C == '' )
				{
					$periodC = LeadCallHistory::model()->find(array(
						'condition' => 'lead_id = :lead_id AND dial_number=2',
						'params' => array(
							':lead_id' => $lead->id,
						),
					));
					
					if( count($periodC) > 0)
					{
						$availableCallingBlock_C = 'C';
					}
				}
				
				if( $availableCallingBlocks == '' )
				{
					$availableCallingBlocks = $availableCallingBlock_A.$availableCallingBlock_B.$availableCallingBlock_C;
				}
			}
			
			echo '<hr>';
			
			echo 'availableLeads: ' . $availableLeads;
		
			exit;
			
			//get total potential dials
			if( $customerSkill->contract->fulfillment_type == 1 )
			{
				$appointmentSetCount = CalendarAppointment::model()->count(array(
					'with' => 'calendar',
					'condition' => 'calendar.customer_id = :customer_id AND t.title IN ("APPOINTMENT SET", "INSERT APPOINTMENT")',
					'params' => array(
						'customer_id' => $customer->id,
					),
				));
				
				$totalPotentialDials = ($totalLeads - $appointmentSetCount);
			}
			
			
			//calculate customer priority
			if( $customerSkill->contract->fulfillment_type == 1 )
			{
				$pace = (($totalLeads / $numberOfWorkingDays) * $dialingDaysInBillingCycle);
				
				$pace = round($pace);
				
				$dialsNeeded = $pace - $appointmentSetCount;
				
				if( $dialsNeeded > 0 && $pace > 0 ) 
				{
					$priority = $dialsNeeded / $pace;
					
					$priority = round($priority, 4);
				}
			}
			else
			{							
				$pace = ((($totalLeads * $maxDials) / $numberOfWorkingDays) * $dialingDaysInBillingCycle);
				
				$pace = round($pace);
				
				$dialsNeeded = (((($totalLeads * $maxDials) / $numberOfWorkingDays) * $dialingDaysInBillingCycle) - $calledLeadCount);
		
				$priority = ( (((($totalLeads * $maxDials) / $numberOfWorkingDays) * $dialingDaysInBillingCycle) - $calledLeadCount) / ((($totalLeads * $maxDials) / $numberOfWorkingDays) * $dialingDaysInBillingCycle) );
			
				$priority = round($priority, 4);
			}
			

			//get the latest agent that is calling the customer's leads
			$latestCallers = LeadHopper::model()->findAll(array(
				'group' => 'agent_account_id',
				'condition' => 'customer_id = :customer_id AND type != :lead_search AND agent_account_id IS NOT NULL AND status IN ("INCALL")',
				'params' => array(
					':customer_id' => $customer->id,
					':lead_search' => LeadHopper::TYPE_LEAD_SEARCH,
				),
			));
			
			if( $latestCallers )
			{
				foreach( $latestCallers as $latestCaller )
				{
					$callAgent .= $latestCaller->currentAgentAccount->getFullName();
					$callAgent .= ', ';
				}
				
				$callAgent = rtrim($callAgent, ', ');
			}
		} 
	
		if( $existingQueueViewer )
		{
			$model = $existingQueueViewer;
		}
		else
		{
			$model = new CustomerQueueViewer;
		}
	
		$model->setAttributes(array(
			'customer_id' => $customerSkill->customer_id,
			'contract_id' => $customerSkill->contract_id,
			'skill_id' => $customerSkill->skill_id,
			'customer_name' => $customerSkill->customer->firstname.' '.$customerSkill->customer->lastname,
			'skill_name' => $customerSkill->skill->skill_name,
			'priority_reset_date' => date('m-1-Y', strtotime('+1 month', strtotime('-1 day'))),
			'priority' => $priority,
			'pace' => $pace,
			'current_dials' => $currentDials,
			'current_goals' => $appointmentSetCount,
			'total_leads' => count($leads),
			'available_leads' => $availableLeads,
			'not_completed_leads' => $notCompletedLeads,
			'total_potential_dials' => $totalPotentialDials,
			'next_available_calling_time' => $nextAvailableCallingTime, //get next available calling time
			'available_calling_blocks' => $availableCallingBlocks,
			'call_agent' => $callAgent,
			'max_dials' => $maxDials,
			'dials_needed' => $dialsNeeded,
			'fulfillment_type' => $customerSkill->contract->fulfillment_type == 1 ? 'Goal' : 'Lead',
		));

		
		if( $model->save(false) )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	private function getWorkingDaysForThisMonth($startDate, $endDate, $returnType='array')
	{
		date_default_timezone_set('America/Denver');
		
		$workdays = array();
		
		$holidays = array(
			strtotime(date('Y-01-01')), // New Year's Day
			//strtotime(date('Y-01-18')), // Birthday of Martin Luther King, Jr.
			strtotime(date('Y-02-15')), // Washington’s Birthday
			strtotime(date('Y-05-30')), // Memorial Day
			strtotime(date('Y-07-04')), // Independence Day
			strtotime(date('Y-09-05')), // Labor Day
			strtotime(date('Y-10-10')), // Columbus Day
			strtotime(date('Y-11-11')), // Veterans Day
			strtotime(date('Y-11-24')), // Thanksgiving Day
			strtotime(date('Y-12-26')), // Christmas Day
		);
		
		$type = CAL_GREGORIAN;
		$month = date('n'); // Month ID, 1 through to 12.
		$year = date('Y'); // Year in 4 digit 2009 format.
		$day_count = cal_days_in_month($type, $month, $year); // Get the amount of days
		
		
		$begin = strtotime($startDate);
		$end = strtotime($endDate);

		
		//loop through all days
		while($begin <= $end)
		{
			if( !in_array($begin, array(strtotime($year.'-5-25'), strtotime($year.'-7-4'))) )
			{
				$get_name = date('l', $begin); //get week day
				$day_name = substr($get_name, 0, 3); // Trim day name to 3 chars
				
				if($returnType == 'pastCount')
				{
					//if not a weekend and is past date add day to array
					if( $day_name != 'Sun' && $day_name != 'Sat' )
					{
						if( time() > $begin )
						{
							$workdays[] = $begin;
						}
					}
				}
				else
				{
					//if not a weekend add day to array
					if( $day_name != 'Sun' && $day_name != 'Sat' )
					{
						$workdays[] = $begin;
					}
				}
			}
			
			$begin += 86400; // +1 day
		}

		
		$workdays = array_diff($workdays, $holidays);
		
		if($returnType == 'array')
		{
			return $workdays;
		}
		else
		{
			return count($workdays);
		}
	}

	
	public function actionCheckContract()
	{
		$customer = Customer::model()->findByPk(706);
		
		$customerSkill = CustomerSkill::model()->find(array(
			'condition' => 'customer_id = ' . $customer->id,
		));
		
		if( isset($customerSkill->contract) && isset($customerSkill->customer) && $customerSkill->customer->status == 1 && $customerSkill->customer->is_deleted == 0 && time() >= strtotime($customerSkill->start_month) )
		{
			$customerIsCallable = true;
		}
		
		if( $customerSkill->is_contract_hold == 1 )
		{
			if( !empty($customerSkill->is_contract_hold_start_date) && !empty($customerSkill->is_contract_hold_end_date) )
			{
				if( time() >= strtotime($customerSkill->is_contract_hold_start_date) && time() <= strtotime($customerSkill->is_contract_hold_end_date) )
				{
					$customerIsCallable = false;
				}
			}
		}
		else
		{
			if( time() >= strtotime($customerSkill->start_month) && time() > strtotime($customerSkill->end_month) )
			{
				$customerIsCallable = false;
			}
		}
		
		echo $customerIsCallable;
	}

	public function actionForceReschedules()
	{
		$models = LeadHopper::model()->findAll(array(
			'condition' => 'type=6',
		));
		
		foreach( $models as $model )
		{
			$model->status = 'READY';
			$model->save(false);
		}
	}

	
	public function actionCheckQueue()
	{
		$callableCustomers = CustomerQueueViewer::model()->count(array(
			'select' => 'customer_id',
			'condition' => 'available_leads > 0',
		));
		
		$customerSkillsCount = CustomerSkill::model()->count(array(
			'with' => array('skill', 'contract'),
			'condition' => 't.status=1 AND skill.id IS NOT NULL AND contract.id IS NOT NULL',
		)); 
		
		// $customerQueueViewerIds = array();
		
		// foreach( $callableCustomers as $callableCustomer )
		// {
			// $customerQueueViewerIds[] = $callableCustomer->customer_id;
		// }
		
		// $customerSkillsCount = CustomerSkill::model()->count(array(
			// 'with' => array('skill', 'contract'),
			// 'condition' => 't.status=1 AND skill.id IS NOT NULL AND contract.id IS NOT NULL AND customer_id AND customer_id IN ('.implode(', ', $customerQueueViewerIds).')',
		// )); 
		
		echo 'callableCustomers: ' . $callableCustomers;
		
		echo '<br>';
		
		echo 'customerSkillsCount: ' . $customerSkillsCount;
	}

	
	public function actionTestStaffEmail()
	{
		$recipientHolder = array();
		
		$customerOfficeStaffs = CustomerOfficeStaff::model()->findAll(array(
			'condition' => 'customer_id = :customer_id AND is_received_email > 0 AND is_deleted=0 AND (email_address IS NOT NULL OR email_address != "")',
			'params' => array(
				':customer_id' => 668,
			),
		));
		
		$calendar = Calendar::model()->findByPk(647);
		
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
			
		}
		
		echo '<pre>';
			print_r($recipientHolder);
	}

	public function actionCheckCalendars()
	{
		$calendars = Lists::model()->findAll(array(
			// 'with' => array('customer', 'customer.account'),
			'condition' => 'calendar_id IS NULL OR calendar_id=""',
		));
		
		echo '<pre>';
		
		if( $calendars )
		{
			foreach( $calendars as $calendar )
			{
				print_r($calendar->attributes);
			}
		}
	}

	public function actionCheckCallBackConfirms()
	{
		$models = LeadHistory::model()->findAll(array(
			'condition' => 'date_created >= "2016-04-13 00:00:00" AND date_created <="2016-04-13 23:59:59" AND disposition="Call Back - Confirm"',
		));
		
		echo 'count' . count($models);
		
		echo '<br>';
		echo '<br>';
		
		if( $models )
		{
			foreach( $models as $model )
			{
				$leadDialCount = LeadCallHistory::model()->find(array(
					'condition' => 'lead_id = :lead_id AND DATE(t.date_updated) = DATE(NOW()) AND status !=4',
					'params' => array(
						':lead_id' => $model->lead_id,
					),
				));
				
				if( count($leadDialCount) == 1 )
				{
					
					echo $model->lead->getFullName();
					
					if( $leadDialCount )
					{
						echo ' - ' . $leadDialCount->lead_phone_number;
					}
					
					echo '<br>';
				}
			}
		}
	}

	public function actionCheckUploadSize()
	{
		phpinfo();
	}

	public function actionListPatch()
	{
		// exit;
		
		$models = Lists::model()->findAll(array(
			'condition' => 'customer_id = :customer_id AND name IN ("System Imported List", "System Imported Completed List") AND status=1 AND YEAR(date_created)="2016" AND MONTH(date_created)="10"',
			'params' => array(
				':customer_id' => 1395
			),
			// 'limit' => 50,
		));
		
		echo count($models);
		
		echo '<br />';
		echo '<br />';
		
		if( $models)
		{
			$ctr = 1;
			
			foreach( $models as $model )
			{
				$leadCount = Lead::model()->count(array(
					'condition' => 'list_id = :list_id AND type=1',
					'params' => array(
						':list_id' => $model->id,
					),
				));
				
				$leadImportLog = CustomerLeadImportLog::model()->find(array(
					'condition' => 'customer_id = :customer_id AND month = :month AND year = :year',
					'params' => array(
						':customer_id' => $model->customer_id,
						':month' => date('F'),
						':year' => date('Y'),
					),
				));
				
				
				if( $leadImportLog )
				{
					$subtractedLeadsImported = $leadImportLog->leads_imported - $leadCount;
					$currentLeadsImported = $leadImportLog->leads_imported;
					
					if( $subtractedLeadsImported < 0 )
					{
						$subtractedLeadsImported = 0;
					}
					
					$leadImportLog->leads_imported = $subtractedLeadsImported;
					
					// if( $leadImportLog->save(false) )
					// {
						
						echo 'Record# ' . $ctr++;
						echo '<br>';
						echo 'Customer Name: ' . $model->customer->firstname.' '.$model->customer->lastname;
						echo '<br>';
						echo 'List Name: ' . $model->name;
						echo '<br>';
						echo 'Year: ' . $leadImportLog->year;
						echo '<br>';
						echo 'Month: ' . $leadImportLog->month;
						echo '<br>';
						echo 'Current Leads Imported: ' . $currentLeadsImported;
						echo '<br>';
						echo 'Lead Count: ' . $leadCount;
						echo '<br>'; 
						echo 'Subtracted Leads Imported: ' . $subtractedLeadsImported;
						echo '<br>';
						
						echo '<br>';
						echo '<br>';
						echo '<hr>';
						echo '<br>';
						echo '<br>';
						
					// }
				}
			}
		}
	}

	public function actionCheckAST()
	{
		$date = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('America/Chicago'));

		$date->setTimezone(new DateTimeZone( 'America/Halifax' ));

		echo timezone_name_from_abbr('AST');
		
		echo '<br><br>';
		
		echo $date->format('m/d/Y g:i A'); 
		
		echo '<br><br>';
		echo '<br><br>';
		
		$dateTime = new DateTime();
		$dateTime->setTimeZone(new DateTimeZone('America/Puerto_Rico'));
		echo $dateTime->format('T'); 
		
		echo '<br><br>';
		echo '<br><br>';
		
		$timezone_identifiers = DateTimeZone::listIdentifiers(DateTimeZone::PER_COUNTRY, 'US');
		
		foreach($timezone_identifiers as $timezone_identifier) 
		{
			echo $timezone_identifier;
			echo '<br>';
		}
	}

	public function actionRemoveAvailableSlots()
	{
		$models = CalendarAppointment::model()->findAll(array(
			'condition'=>'title="AVAILABLE"'
		));
		
		echo 'models: ' . count($models);
		
		echo '<br><br>';
		
		// if( $models )
		// {
			// foreach( $models as $model )
			// {
				// $model->delete();
			// }
		// }
	}

	public function actionRemoveCompletedChildSkills()
	{
		$models = LeadHopper::model()->findAll(array(
			'with' => array('lead', 'lead.latestCompletedCallHistory'),
			'condition' => 't.type IN (3,6) AND lead.id IS NOT NULL AND latestCompletedCallHistory.id IS NOT NULL AND t.lead_id !="62241"',
		));
		
		echo 'count: ' . count($models);
		
		echo '<br><br>';
		
		if( $models )
		{
			$ctr = 1;
			
			foreach( $models as $model )
			{
				$callType = $model->type == 3 ? 'Confirm' : 'Resched';
				
				echo $ctr.'. ('.$callType.') | '.$model->lead_id.' | '.$model->lead->getFullName().' => '.$model->lead->latestCompletedCallHistory->disposition;
				echo '<br>';
				echo '<br>';
				
				$ctr++;
				
				$model->delete();
			}
		}
	}

	public function actionUpdateAttempts()
	{
		$leadCallHistoriesCount = LeadCallHistory::model()->count(array(
			'condition' => 'disposition IS NOT NULL AND date_created >="2016-05-01 00:00:00" AND date_created <= "2016-05-31 <= 23:59:59" AND status !=4 AND attempt IS NULL',
			'order' => 'date_created ASC',
		));
		
		echo 'count: ' . $leadCallHistoriesCount;
		
		echo '<br><br>';
		
		$leadCallHistories = LeadCallHistory::model()->findAll(array(
			'condition' => 'disposition IS NOT NULL AND date_created >="2016-05-01 00:00:00" AND date_created <= "2016-05-31 <= 23:59:59" AND status !=4 AND attempt IS NULL',
			'order' => 'date_created ASC',
			// 'offset' => 0,
			'limit' => 30000,
		));
		
		$ctr = 1;
		
		if( $leadCallHistories )
		{
			foreach( $leadCallHistories as $leadCallHistory )
			{
				if( $leadCallHistory->attempt == null )
				{
					$existingAttempt = LeadCallHistory::model()->find(array(
						'condition' => 'id != :id AND lead_id = :lead_id AND disposition IS NOT NULL AND status !=4 AND attempt IS NOT NULL',
						'params' => array(
							':id' => $leadCallHistory->id,
							':lead_id' => $leadCallHistory->lead_id
						),
						'order' => 'attempt DESC',
					));
						
					if( $existingAttempt )
					{
						$leadCallHistory->attempt = $existingAttempt->attempt + 1;
					}
					else
					{
						$leadCallHistory->attempt = 1;
					}
				}
				
				if( $leadCallHistory->bucket_priority == null || $leadCallHistory->bucket_priority == 0 )
				{
					$leadCallHistory->bucket_priority = $this->getBucketPriorityValue($leadCallHistory->disposition);
				}
				
				if( $leadCallHistory->save(false) )
				{
					$otherAttempts = LeadCallHistory::model()->findAll(array(
						'condition' => 'id != :id AND lead_id = :lead_id AND disposition IS NOT NULL AND date_created >= :start_date AND date_created <= :end_date AND status !=4 AND attempt IS NULL',
						'params' => array(
							':id' => $leadCallHistory->id,
							':lead_id' => $leadCallHistory->lead_id,
							':start_date' => date('Y-m-d H:i:s', strtotime($leadCallHistory->date_created)),
							':end_date' => date('Y-m-d H:i:s', strtotime('+1 Hour', strtotime($leadCallHistory->date_created))),
						),
						'order' => 'date_created ASC',
					));

					if( $otherAttempts )
					{
						foreach( $otherAttempts as $otherAttempt )
						{
							if( $otherAttempt->attempt == null )
							{
								$otherAttempt->attempt = $leadCallHistory->attempt;
							}
							
							if( $otherAttempt->bucket_priority == null || $otherAttempt->bucket_priority == 0 )
							{
								$otherAttempt->bucket_priority = $this->getBucketPriorityValue($otherAttempt->disposition);
							}
							
							$otherAttempt->save(false);
						}
					}
					
					echo $ctr++;
					echo '<br>';
				}
			}
		}
		
		echo '<br><hr><br>';
		
		echo 'dbUpdates: ' . $ctr;
		
		echo '<br><br>';
		
		echo 'end..';
	}
	
	public function getBucketPriorityValue($disposition)
	{
		$completeBucket = array(
			'Language Barrier - Unsupported', 
			'Client to Contact Call Center', 
			'Deceased', 
			'Not Insured', 
			'Not Interested', 
			'Recently Met With', 
			'Referred to Agent', 
			'Retention Alert', 
			'Do Not Call', 
			'Client Complete' 
		);
		
		if( in_array($disposition, $completeBucket) )
		{
			$priorityValue = 1;
		}
		
		$calendarBucket = array(
			'Appointment Set', 
			'Schedule Conflict', 
			'Location Conflict'
		);
		
		if( in_array($disposition, $completeBucket) )
		{
			return 2;
		}
		
		$callBackBucket = array(
			'Call Back'
		);
		
		if( in_array($disposition, $callBackBucket) )
		{
			return 3;
		}
		
		$retryBucket = array(
			'Answering Machine - No Message Left', 
			'Answering Machine - Left Message',
			'Busy', 
			'Client Hung-up', 
			'Language Barrier - Spanish', 
			'No Answer / No Voicemail'
		);
		
		if( in_array($disposition, $retryBucket) )
		{
			return 4;
		}
		
		$badNumberBucket = array(
			'Wrong Number'
		);
		
		if( in_array($disposition, $badNumberBucket) )
		{
			return 5;
		}

		return null;
	}

	
	public function actionCheckEvaluation()
	{	
		$priority = 0;
		$pace = 0;
		$currentDials = 0;
		$availableLeads = 0;
		$notCompletedLeads = 0;
		$totalLeads = 0;
		$totalPotentialDials = 0;
		$calledLeadCount = 0;
		$dialsNeeded = 0;
		$maxDials = 0;
		$appointmentSetCount = 0;
		
		$availableCallingBlocks = '';
		
		$availableCallingBlock_A = 0;
		$availableCallingBlock_B = 0;
		$availableCallingBlock_C = 0;
		
		$callAgent = '';
		
		$customerSkill = CustomerSkill::model()->find(array(
			'condition' => 'customer_id=517',
		));
		
		$nextAvailableCallingTime = $this->checkTimeZone($customerSkill);
		
		$numberOfWorkingDays = $this->getWorkingDaysForThisMonth(date('Y-m-1'), date('Y-m-t'), 'totalCount');
		$dialingDaysInBillingCycle = $this->getWorkingDaysForThisMonth(date('Y-m-1'), date('Y-m-t'), 'pastCount');	
		
		$customer = Customer::model()->findByPk($customerSkill->customer_id);
		$skill = Skill::model()->findByPk($customerSkill->skill_id);
		$contract = $customerSkill->contract;
		
		$maxDials = $skill->max_dials;
		
		$leads = Lead::model()->findAll(array(
			'with' => array('list'),
			'condition' => 'list.skill_id = :skill_id AND list.customer_id = :customer_id AND t.type=1 AND t.status=1',
			'params' => array(
				':skill_id' => $skill->id,
				':customer_id' => $customer->id,
			),
		));
			
		if($contract->fulfillment_type != null )
		{
			if( $contract->fulfillment_type == Contract::TYPE_FULFILLMENT_GOAL_VOLUME )
			{
				if( !empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME]) )
				{
					foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME] as $key => $subsidyLevel )
					{
						$customerSkillLevelArray = $customerSkill->getCustomerSkillLevelArray();
						$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;

						if( $customerSkillLevelArrayGroup != null )
						{							
							if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
							{
								$totalLeads += ( $subsidyLevel['goal'] * $customerSkillLevelArrayGroup->quantity );
							}
						}
					}
				}
				
				$customerExtras = CustomerExtra::model()->findAll(array(
					'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id AND status=1 AND year = :year AND month = :month',
					'params' => array(
						':customer_id' => $customerSkill->customer_id,
						':contract_id' => $customerSkill->contract_id,
						':skill_id' => $customerSkill->skill_id,
						':year' => date('Y'),
						':month' => date('m'),
					),
				));
				
				if( $customerExtras )
				{
					foreach( $customerExtras as $customerExtra )
					{
						$totalLeads += $customerExtra->quantity;
					}
				}
			}
			else
			{
				if( !empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME]) )
				{
					foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME] as $key => $subsidyLevel )
					{
						$customerSkillLevelArray = $customerSkill->getCustomerSkillLevelArray();
						
						$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;
						
						if( $customerSkillLevelArrayGroup != null )
						{
							if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
							{
								$totalLeads += ( $subsidyLevel['high'] * $customerSkillLevelArrayGroup->quantity );
							}
						}
					}
				}
				
				$customerExtras = CustomerExtra::model()->findAll(array(
					'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id AND status=1 AND year = :year AND month = :month',
					'params' => array(
						':customer_id' => $customerSkill->customer_id,
						':contract_id' => $customerSkill->contract_id,
						':skill_id' => $customerSkill->skill_id,
						':year' => date('Y'),
						':month' => date('m'),
					),
				));
				
				if( $customerExtras )
				{
					foreach( $customerExtras as $customerExtra )
					{
						$totalLeads += $customerExtra->quantity;
					}
				}
			}
		}

		if( count($leads) > 0 && $totalLeads > 0 )
		{
			foreach( $leads as $lead )
			{
				$leadDialCount = 0;

				if( $lead->type == 1 && $lead->status == 1 && $lead->list->status == 1 )
				{
					$leadIsCallable = false;
					
					$latestCall = LeadCallHistory::model()->find(array(
						'condition' => 'lead_id = :lead_id',
						'params' => array(
							':lead_id' => $lead->id,
						),
						'order' => 'date_created DESC'
					));
					
					$leadDialCount = LeadCallHistory::model()->count(array(
						'condition' => 'lead_id = :lead_id AND MONTH(t.date_created) = MONTH(NOW()) AND status !=4',
						'params' => array(
							':lead_id' => $lead->id,
						),
					));
					
					if( $leadDialCount > 0 )
					{
						$currentDials = $currentDials + $leadDialCount;
						
						$calledLeadCount++;
					}
					
					//check retry interval time
					if( $latestCall )
					{
						if( $latestCall->is_skill_child == 0 )
						{
							if( !empty($latestCall->skillDisposition->retry_interval) )
							{
								if( isset($latestCall->skillDisposition) && time() >= (strtotime($latestCall->end_call_time) + $latestCall->skillDisposition->retry_interval) )
								{
									$leadIsCallable = true;
								} 
							}
							else
							{
								$leadIsCallable = true;
							}
						}
						else
						{
							if( !empty($latestCall->skillChildDisposition->retry_interval) )
							{
								if( isset($latestCall->skillChildDisposition) && time() >= (strtotime($latestCall->end_call_time) + $latestCall->skillChildDisposition->retry_interval) )
								{
									$leadIsCallable = true;
								} 
							}
							else
							{
								$leadIsCallable = true;
							}
						}
					}
					else
					{
						$leadIsCallable = true;
					}
					
					
					if( $leadIsCallable && $lead->number_of_dials < ($maxDials * 3) )
					{
						$availableLeads++;
						
						echo '<pre>';
						
						print_r( $lead->attributes );
						
						exit;
					}
					else
					{
						echo $notCompletedLeads++;
						echo '<br>';
					}
				}
	
				//get total potential dials for lead volume
				if( $customerSkill->contract->fulfillment_type == 2 )
				{
					$totalPotentialDials += ($maxDials - $leadDialCount);
				}
				

				if( $leadDialCount < 1 )
				{
					$availableCallingBlock_A++;
				}
				
				if( $leadDialCount < 2 )
				{
					$availableCallingBlock_B++;
				}
				
				if( $leadDialCount < 3 )
				{
					$availableCallingBlock_C++;
				}
				
				if( $maxDials > 3 )
				{
					if( $leadDialCount < 4 )
					{
						$availableCallingBlock_A++;
					}
					
					if( $leadDialCount < 5 )
					{
						$availableCallingBlock_B++;
					}
					
					if( $leadDialCount < 6 )
					{
						$availableCallingBlock_C++;
					}
				}
			}
			
			//get available calling blocks
			if( $availableCallingBlocks == '' )
			{
				if( $availableCallingBlock_A > 0 )
				{
					$availableCallingBlocks .= 'A';
				}
				
				if( $availableCallingBlock_B > 0 )
				{
					$availableCallingBlocks .= 'B';
				}
				
				if( $availableCallingBlock_C > 0 )
				{
					$availableCallingBlocks .= 'C';
				}
			}
			
			//get total potential dials
			if( $customerSkill->contract->fulfillment_type == 1 )
			{
				$appointmentSetCount = CalendarAppointment::model()->count(array(
					'with' => 'calendar',
					'condition' => 'calendar.customer_id = :customer_id AND t.title IN ("APPOINTMENT SET", "INSERT APPOINTMENT") AND t.status !=4 AND t.lead_id IS NOT NULL AND MONTH(t.date_created) = MONTH(NOW())',
					'params' => array(
						'customer_id' => $customer->id,
					),
				));
				
				$totalPotentialDials = ($totalLeads - $appointmentSetCount);
			}
			
			
			//calculate customer priority
			if( $customerSkill->contract->fulfillment_type == 1 )
			{
				$pace = (($totalLeads / $numberOfWorkingDays) * $dialingDaysInBillingCycle);
				
				$pace = round($pace);
				
				$dialsNeeded = $pace - $appointmentSetCount;
				
				if( $dialsNeeded > 0 && $pace > 0 ) 
				{
					$priority = $dialsNeeded / $pace;
					
					$priority = round($priority, 4);
				}
			}
			else
			{							
				$pace = ((($totalLeads * $maxDials) / $numberOfWorkingDays) * $dialingDaysInBillingCycle);
				
				$pace = round($pace);
				
				$dialsNeeded = (((($totalLeads * $maxDials) / $numberOfWorkingDays) * $dialingDaysInBillingCycle) - $calledLeadCount);
		
				$priority = ( (((($totalLeads * $maxDials) / $numberOfWorkingDays) * $dialingDaysInBillingCycle) - $calledLeadCount) / ((($totalLeads * $maxDials) / $numberOfWorkingDays) * $dialingDaysInBillingCycle) );
			
				$priority = round($priority, 4);
			}
			

			//get the latest agent that is calling the customer's leads
			$latestCallers = LeadHopper::model()->findAll(array(
				'group' => 'agent_account_id',
				'condition' => 'customer_id = :customer_id AND type != :lead_search AND agent_account_id IS NOT NULL AND status IN ("INCALL")',
				'params' => array(
					':customer_id' => $customer->id,
					':lead_search' => LeadHopper::TYPE_LEAD_SEARCH,
				),
			));
			
			if( $latestCallers )
			{
				foreach( $latestCallers as $latestCaller )
				{
					$callAgent .= $latestCaller->currentAgentAccount->getFullName();
					$callAgent .= ', ';
				}
				
				$callAgent = rtrim($callAgent, ', ');
			}
		} 
	
		if( $existingQueueViewer )
		{
			$model = $existingQueueViewer;
		}
		else
		{
			$model = new CustomerQueueViewer;
		}
	
		$model->setAttributes(array(
			'customer_id' => $customerSkill->customer_id,
			'contract_id' => $customerSkill->contract_id,
			'skill_id' => $customerSkill->skill_id,
			'customer_name' => $customerSkill->customer->firstname.' '.$customerSkill->customer->lastname,
			'skill_name' => $customerSkill->skill->skill_name,
			'priority_reset_date' => date('m-1-Y', strtotime('+1 month', strtotime('-1 day'))),
			'priority' => $priority,
			'pace' => $pace,
			'current_dials' => $currentDials,
			'current_goals' => $appointmentSetCount,
			'total_leads' => count($leads),
			'available_leads' => $availableLeads,
			'not_completed_leads' => $notCompletedLeads,
			'total_potential_dials' => $totalPotentialDials,
			'next_available_calling_time' => $nextAvailableCallingTime, //get next available calling time
			'available_calling_blocks' => $availableCallingBlocks,
			'call_agent' => $callAgent,
			'max_dials' => $maxDials,
			'dials_needed' => $dialsNeeded,
			'fulfillment_type' => $customerSkill->contract->fulfillment_type == 1 ? 'Goal' : 'Lead',
		));
		
		if( $model->save(false) )
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function actionRemoveDuplicateLeadHopper()
	{
		$models = LeadHopper::model()->findAll(array(
			'condition' => 'type=1',
			'group' => 'lead_id'
		));
		
		echo 'models: ' . count($models);
			
		echo '<br />';
		echo '<br />';
		
		foreach( $models as $model )
		{
			echo 'Lead ID: '.$model->lead_id.' => ' . $model->lead->getFullName();
			echo '<br />';
			echo '<br />';
			
			$duplicates = LeadHopper::model()->findAll(array(
				'condition' => 'id <> :id AND lead_id = :lead_id AND type=1',
				'params' => array(
					':id' => $model->id,
					':lead_id' => $model->lead_id,
				),
			)); 
			
			echo 'duplicates: ' . count($duplicates);
			
			echo '<br />';
			echo '<br />';
			
			if( $duplicates )
			{
				foreach( $duplicates as $duplicate ) 
				{
					echo 'Lead ID: '.$duplicate->lead_id.' => ' . $duplicate->lead->getFullName();
					echo '<br />';
					
					$duplicate->delete();
				}
			}
			
			echo '<br><hr><br>';
		}
	}

	public function actionCompleteLeads()
	{
		$leads = Lead::model()->findAll(array(
			'with' => array('list'),
			'condition' => '
				list.status = 1 
				AND t.type=1 
				AND t.status=1
				AND t.number_of_dials >= 3
			',
		));
		
		echo 'count: ' . count($leads);
		
		echo '<br><br>';
		
		// if( $leads )
		// {
			// $ctr = 1;
			
			// foreach( $leads as $lead )
			// {
				// $lead->status = 3;
				
				// if( $lead->save(false) )
				// {
					// $existingLeadHopper = LeadHopper::model()->find(array(
						// 'condition' => 'lead_id = :lead_id AND type=1 AND status="READY"',
						// 'params' => array(
							// ':lead_id' => $lead->id,
						// ),
					// ));
					
					// if( $existingLeadHopper )
					// {
						// $existingLeadHopper->delete();
					// }
					
					// echo $ctr++;
					// echo '<br />';
				// }
			// }
		// }
	}

	public function actionRemoveContacts()
	{
		$models = LeadHopper::model()->findAll(array(
			'condition' => 'type=1',
		));
		
		echo 'count: ' . count($models);
		
		echo '<br>';
		echo '<br>';
		
		exit;
		
		if( $models)
		{
			$ctr = 1;
			
			foreach( $models as $model )
			{
				if( $model->delete() )
				{
					echo $ctr++;
					echo '<br>';
				}
			}
		}
		
		echo '<br><br>end..';
		
		exit;
	}

	public function actionConfirmPatch()
	{
		if( date('w') == 5)
		{
			$startDate = date('Y-m-d 00:00:00', strtotime('+1 day'));
			$endDate = date('Y-m-d 23:59:59', strtotime('+3 day'));
		}
		else
		{
			$startDate = date('Y-m-d 00:00:00', strtotime('+1 day'));
			$endDate = date('Y-m-d 23:59:59', strtotime('+1 day'));
		}
		
		// $startDate = '2017-09-02 00:00:00';
		// $endDate = '2017-09-05 23:59:59';
		
		echo 'startDate: ' . $startDate;
		echo '<br>';
		echo 'endDate: ' . $endDate;
		
		echo '<br><br>';
		// exit;
		
		$models = CalendarAppointment::model()->findAll(array(
			'with' => 'lead',
			// 'condition' => 't.id=248657',
			// 'condition' => 't.id=247588',
			'condition' => '
				t.start_date >= "'.$startDate.'"
				AND t.start_date <= "'.$endDate.'"
				AND t.title IN ("APPOINTMENT SET", "INSERT APPOINTMENT")
				AND t.status !=4
				AND t.lead_id IS NOT NULL
				AND lead.id IS NOT NULL
			',
		));
		
		echo '<pre>';
		
		echo 'count: ' . count($models);
		
		echo '<br><br>'; 
		
		// exit;

		if( $models )
		{
			$ctr = 0;
			$removedCtr = 1;
			
			foreach( $models as $model )
			{
				$createConfirm = false;
				
				$latestCall = LeadCallHistory::model()->find(array(
					'condition' => 'lead_id = :lead_id AND calendar_appointment_id = :calendar_appointment_id',
					'params' => array(
						':lead_id' => $model->lead_id,
						':calendar_appointment_id' => $model->id,
					),
					'order' => 'date_created DESC',
				));
				
				if( $latestCall )
				{
					if( $latestCall->is_skill_child == 0 )
					{
						if( $latestCall->skillDisposition->is_appointment_set == 1 )
						{
							$createConfirm = true;
						}
					}
					else
					{
						if( $latestCall->skillChildDisposition->is_appointment_set == 1 || $latestCall->skillChildDisposition->is_callback == 1 )
						{
							$createConfirm = true;
						}
					}
				}
				else
				{
					$createConfirm = true;
				}
				
				// $createConfirm = true;
			
				if( $createConfirm )
				{			
					echo $ctr++;
					echo '<br>';
					
					$this->createConfirmationCall($model); 

					echo '<br>';
					
					// echo '<br>';
						// echo 'Record counter#' . $ctr++;
					// echo '<br>';
					
					// echo '<br><br>';
					
					// echo '<pre>';
						// print_r($model->attributes);  
						
						// print_r($latestCall->attributes);
						
					// echo '</pre>';
					
					// echo '<br><hr><br>';
				}
				else
				{
					echo '<pre>';
						print_r($model->attributes);  
						
						print_r($latestCall->attributes);
						
					echo '</pre>';
					
					echo '<br><hr><br>';
				}
				
				// $floridaAreaCodes = array('239', '305', '321', '352', '386', '407', '561', '727', '754', '772', '786', '813', '850', '863', '904', '941', '954');
		
				// $georgiaArecodeCodes = array('229', '404', '470', '478', '678', '706', '762', '770', '912');
				
				// $southCarolinaAreaCodes = array('803', '843', '864');
				
				// $deleteConfirm = false;
				
				// $leadPhoneNumber = '';
				
				// if( !empty($latestCall) )
				// {
					// $leadPhoneNumber = $latestCall->lead_phone_number;
				// }
				// else
				// {
					// if( $leadPhoneNumber == '' && !empty($model->lead->home_phone_number) )
					// {
						// $leadPhoneNumber = $model->lead->home_phone_number;
					// }
					
					// if( $leadPhoneNumber == '' && !empty($model->lead->office_phone_number) )
					// {
						// $leadPhoneNumber = $model->lead->office_phone_number;
					// }
					
					// if( $leadPhoneNumber == '' && !empty($model->lead->mobile_phone_number) )
					// {
						// $leadPhoneNumber = $model->lead->mobile_phone_number;
					// }
				// }
	
				// if( in_array(substr($leadPhoneNumber, 0, 3), $floridaAreaCodes) )
				// {
					// $deleteConfirm = true;
				// }
				
				// if( in_array(substr($leadPhoneNumber, 0, 3), $georgiaArecodeCodes) )
				// {
					// $deleteConfirm = true;
				// }
				
				// if( in_array(substr($leadPhoneNumber, 0, 3), $southCarolinaAreaCodes) )
				// {
					// $deleteConfirm = true;
				// }
				
				// if( $deleteConfirm )
				// {
					// echo '<br>';
						// echo 'Record counter#' . $removedCtr++;
					// echo '<br>';
				// }
			}
		}
		
		echo '<br><br>ctr: ' . $ctr;
		
		echo '<br><br>end..';
	}
	
	private function createConfirmationCall($model)
	{
		$lead = $model->lead;
		$list = $lead->list;
		$customer = $list->customer;
		
		$existingLeadHopperEntry = LeadHopper::model()->find(array(
			'condition' => 'lead_id = :lead_id',
			// 'condition' => 'lead_id = :lead_id AND type = :type',
			'params' => array(
				':lead_id' => $model->lead_id,
				// ':type' => LeadHopper::TYPE_CONFIRMATION_CALL,
			),
		));
		
		if( $existingLeadHopperEntry )
		{
			echo 'existing in hopper';
			echo '<br>';

			$confirmationCall = $existingLeadHopperEntry;
		}
		else
		{
			$confirmationCall = new LeadHopper;
			
			$skillChildConfirmation = SkillChild::model()->find(array(
				'condition' => 'skill_id = :skill_id AND type = :type',
				'params' => array(
					':skill_id' => $list->skill_id,
					':type' => SkillChild::TYPE_CONFIRM,
				),
			));
			
			if($skillChildConfirmation !== null)
			{
				$confirmationCall->skill_child_confirmation_id = $skillChildConfirmation->id;
			}
			
			$skillChildReschedule = SkillChild::model()->find(array(
				'condition' => 'skill_id = :skill_id AND type = :type',
				'params' => array(
					':skill_id' => $list->skill_id,
					':type' => SkillChild::TYPE_RESCHEDULE,
				),
			));
			
			if($skillChildReschedule !== null)
			{
				$confirmationCall->skill_child_reschedule_id = $skillChildReschedule->id;
			}
			
			if( !empty($lead->timezone) )
			{
				$timeZone = $lead->timezone;
			}
			else
			{
				$timeZone = AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $customer->phone) );
			}
			
			$confirmationCall->setAttributes(array(
				'lead_id' => $lead->id,
				'list_id' => $list->id,
				// 'skill_id' => $list->skill_id,
				'customer_id' => $customer->id,
				'lead_timezone' => $timeZone,
				'lead_language' => $lead->language,
				// 'status' => 'DONE',
				// 'type' => LeadHopper::TYPE_CONFIRMATION_CALL,
			));
		}
		
		$confirmationDate = $model->start_date;
			
		//if actual appointment date is on monday move it friday last week
		if( date('N', strtotime($confirmationDate)) == 1 )
		{
			$confirmationDate = date('Y-m-d', strtotime('last friday', strtotime($confirmationDate))).' '.date('H:i:s', strtotime($confirmationDate));
		}
		else
		{
			//move it to 1 business day before the actual appointment date
			$confirmationDate = date('Y-m-d', strtotime('-1 day', strtotime($confirmationDate))).' '.date('H:i:s', strtotime($confirmationDate));
		}
		
		// $confirmationDate = date('2017-09-01 H:i:s', strtotime($confirmationDate));
		
		$confirmationCall->calendar_appointment_id = $model->id;
		$confirmationCall->appointment_date = $confirmationDate;
		$confirmationCall->agent_account_id = null;
		
		$confirmationCall->skill_id = $list->skill_id;
		
		if($model->title == 'NO SHOW RESCHEDULE')
		{
			$confirmationCall->status = 'READY';
			$confirmationCall->type = LeadHopper::TYPE_NO_SHOW_RESCHEDULE;
			// $confirmationCall->calendar_appointment_id = null;
			$confirmationCall->appointment_date = null;
		}
		elseif( $model->title == 'RESCHEDULE APPOINTMENT')
		{
			$confirmationCall->status = 'READY';
			$confirmationCall->type = LeadHopper::TYPE_RESCHEDULE;
			// $confirmationCall->calendar_appointment_id = null;
			$confirmationCall->appointment_date = null;
		}
		else
		{
			$confirmationCall->status = 'DONE';
			$confirmationCall->type = LeadHopper::TYPE_CONFIRMATION_CALL;
		}
		
		// echo '<pre>';
		// print_r($confirmationCall->attributes);
		// exit;
		
		if( $confirmationCall->save(false) )
		{
			// $leadPhoneNumber = null;
			
			// $leadHistory = new LeadHistory;
						
			// $leadCallHistory = LeadCallHistory::model()->find(array(
				// 'condition' => 'lead_id = :lead_id',
				// 'params' => array(
					// ':lead_id' => $model->lead_id,
				// ),
				// 'order' => 'date_created DESC',
			// ));
			
			// if( $leadCallHistory )
			// {
				// $leadHistory->lead_call_history_id = $leadCallHistory->id;
				
				// $leadPhoneNumber = $leadCallHistory->lead_phone_number;
				
				// $leadCallHistory->calendar_appointment_id = $model->id;
				// $leadCallHistory->save(false);
			// }
			
			// $leadHistory->setAttributes(array(
				// 'lead_id' => $model->lead_id,
				// 'lead_phone_number' => $leadPhoneNumber,
				// 'disposition' => $model->title,
				// 'agent_account_id' => isset($leadCallHistory->agent_account_id) ? $leadCallHistory->agent_account_id : Yii::app()->user->account->id,
				// 'calendar_appointment_id' => $model->id,
				// 'note' => $model->details,
				// 'type' => 5,
			// ));
			
			// $leadHistory->save(false);
		}
		
		return true;
	}

	public function actionRemovePastCallBacks()
	{
		$models = LeadHopper::model()->findAll(array(
			'condition' => 'type = 2 AND (callback_date IS NULL OR NOW() > DATE(callback_date)) AND status !="INCALL"',
			'order' => 'callback_date DESC',
		));
		
		echo 'count: ' . count($models);
		
		echo '<br><br>';
		
		echo '<pre>';
		
		if( $models )
		{
			foreach( $models as $model )
			{
				// print_r($model->attributes);
				
				echo $model->callback_date;
				echo '<br>';
				
				echo $model->delete();
			}
		}
		
		echo '</pre>';
		
		echo '<br><br>end..';
	}

	public function actionRemovePastAppointments()
	{
		$models = LeadHopper::model()->findAll(array(
			'condition' => 'type=3 AND ( appointment_date IS NULL OR DATE(appointment_date) < DATE(NOW()) )',
			'order' => 'appointment_date DESC',
		));
		
		echo 'count: ' . count($models);
		
		echo '<br><br>';
		
		if( $models )
		{
			foreach( $models as $model )
			{
				echo $model->appointment_date;
				echo '<br>';
				
				echo $model->delete();
			}
		}
		
		echo '<br><br>end..';
	}
	
	public function actionForceEvaluation()
	{
		exit;
		
		$models = CustomerQueueViewer::model()->findAll(array(
			'condition' => 'current_dials >= 100',
		));
		
		echo 'count: ' . count($models);
		
		echo '<br />';
		
		if( $models )
		{
			$ctr = 1;
			
			foreach( $models as $model )
			{
				$evaluation = new CustomerQueueViewerEvaluation;
				$evaluation->customer_queue_viewer_id = $model->id;
				
				if( $evaluation->save(false) )
				{
					echo $ctr++;
					echo '<br>';
				}
			}
		}
	}

	public function actionImportCreditCards()
	{
		$csv_file = 'csv/Customers(1).csv';
	
		if (($handle = fopen($csv_file, "r")) !== FALSE) 
		{
			fgetcsv($handle);   
			
			$ctr = 1;
			
			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
			{
				$num = count($data);
				
				for ($c=1; $c < $num; $c++) {
					$col[$c] = $data[$c];
				}
				
				$agentId = $col[2];
				$cardNumber = $col[13];
				$matchCardNumber = substr($cardNumber, 0, -1);
				
				if( !empty($agentId) && !empty($cardNumber) && !empty($matchCardNumber) )
				{
					$customer = Customer::model()->find(array(
						'condition' => 'custom_customer_id = :custom_customer_id',
						'params' => array(
							':custom_customer_id' => $agentId,
						),
					));

					if( $customer )
					{
						$creditCard = CustomerCreditCard::model()->find(array(
							'condition' => 'customer_id = :customer_id AND credit_card_number LIKE :credit_card_number AND credit_card_type IN ("Visa", "MasterCard")',
							'params' => array(
								':customer_id' => $customer->id,
								':credit_card_number' => $matchCardNumber.'%',
							),
						));
						
						if( $creditCard )
						{					
							// $creditCard->credit_card_number = $cardNumber;
							// $creditCard->save(false);
					
							echo $ctr.'.'.$customer->getFullName().' - '.$creditCard->credit_card_number.' to '.$cardNumber.' => '.$creditCard->credit_card_type;
							echo '<br />';
							
							$ctr++;
						}
					}
				}
			}
			
			fclose($handle);
		}

		echo '<br><br>end..';
	}

	public function actionCheckListUpdatedTime()
	{
		$model = Lead::model()->findByPk(20417);
		
		$currentDateTime = new DateTime($model->date_updated, new DateTimeZone('America/Chicago'));
		$currentDateTime->setTimezone(new DateTimeZone('America/Denver')); 
		echo $currentDateTime->format('m/d/Y g:i A');	
		
		echo '<br><br>';
		
		$currentDateTime = new DateTime($model->date_updated, new DateTimeZone('America/Denver'));
		$currentDateTime->setTimezone(new DateTimeZone('America/Denver')); 
		echo $currentDateTime->format('m/d/Y g:i A');	
	}
	
	public function actionCheckTime()
	{
		echo date('m/d/Y g:i A', time());
		echo '<br>';
		echo date('m/d/Y g:i A', strtotime('today 6:00 pm'));
		
		exit;
	}

	public function actionGoal()
	{
		$criteria = new CDbCriteria;
		$criteria->compare('status', 1);
		$criteria->compare('id', 878);
		$customers = Customer::model()->findAll($criteria);
		
		foreach($customers as $customer)
		{
			$customerSkill = CustomerSkill::model()->find(array(
				'condition' => 'customer_id = :customer_id AND status=1',
				'params' => array(
					':customer_id' => $customer->id,
				),
			));
			
			if($customerSkill !== null)
			{
				echo '<br>';
				echo '---'.$customer->getFullName().'---';
				echo '<br>';
				echo '';
			
				$contract = $customerSkill->contract;
				
				//if contract fullfillment type is GOAL apply the 10x rule and limit customers monthly import
				if(isset($contract))
				{
					
					$importLimit = 0;
					$contractedLeads = 0;
					
					if($contract->fulfillment_type != null )
					{
						if( $contract->fulfillment_type == Contract::TYPE_FULFILLMENT_GOAL_VOLUME )
						{
							if( !empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME]) )
							{
								foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME] as $key => $subsidyLevel )
								{
									$customerSkillLevelArray = $customerSkill->getCustomerSkillLevelArray();
									$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;

									if( $customerSkillLevelArrayGroup != null )
									{							
										if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
										{
											$contractedLeads += ( $subsidyLevel['goal'] * $customerSkillLevelArrayGroup->quantity );
										}
									}
								}
							}
						}						
					}	
					
					$importLimit = $contractedLeads * 10;

					echo 'Import Limit: '.$importLimit;
					echo '<br>';
					
					if( $importLimit > 0 )
					{
						$existingCustomerLeadImportLog = CustomerLeadImportLog::model()->find(array(
							'condition' => 'customer_id = :customer_id AND month = :month AND year = :year',
							'params' => array(
								':customer_id' => $customer->id,
								':month' => date('F'),
								':year' => date('Y'),
							),
						));
						
						if( $existingCustomerLeadImportLog )
						{
							$customerLeadImportLog = $existingCustomerLeadImportLog;
						}
						else
						{
							$customerLeadImportLog = new CustomerLeadImportLog;
							
							$customerLeadImportLog->setAttributes(array(
								'customer_id' => $customer_id,
								'contract_id' => $contract->id,
								'skill_id' => $model->skill_id,
								'month' => date('F'),
								'year' => date('Y'),
							));
							
							$customerLeadImportLog->save(false);
						}
					}
					
					echo 'Imported Leads: '.$customerLeadImportLog->leads_imported;
					echo '<br>';
					
					if($customerLeadImportLog->leads_imported > $importLimit)
					{
						echo 'Limit reached!';
						echo '<br>';
						echo 'No of imported leads in month of '.date('M').': ';
						
						$criteria = new CDbCriteria;
						$criteria->compare('customer_id', $customer->id);
						$criteria->compare('YEAR(date_created)', date('Y'));
						$criteria->compare('MONTH(date_created)', date('m'));
						$criteria->compare('status', 1);
						
						$leadCount = Lead::model()->findAll($criteria);
						echo count($leadCount);
						echo '<br>';
						
						$criteria = new CDbCriteria;
						$criteria->compare('customer_id', $customer->id);
						$criteria->compare('YEAR(date_created)', date('Y'));
						$criteria->compare('MONTH(date_created)', date('m'));
						$criteria->compare('status', 1);
						$criteria->addCondition('list_id IS NULL');
						
						$leadCountWaiting = Lead::model()->findAll($criteria);
						echo 'Leads Waiting: '.count($leadCountWaiting);
						echo '<br>';
						
						
						$criteria = new CDbCriteria;
						$criteria->compare('customer_id', $customer->id);
						$criteria->compare('YEAR(date_created)', date('Y'));
						$criteria->compare('MONTH(date_created)', date('m'));
						$criteria->compare('status', 1);
						$criteria->addCondition('list_id IS NOT NULL');
						
						$leadCountNotWaiting = Lead::model()->findAll($criteria);
						
						if(count($leadCountNotWaiting) <= $importLimit && $importLimit > 0)
						{
							echo 'Leads Active : '.count($leadCountNotWaiting);
							echo '<br>';
							
							echo 'Beginning matching of Leads active to the number of Import Limit';
							echo '<br>';
							
							
							$count = count($leadCountNotWaiting);
							
							$list_id = $leadCountNotWaiting[0]->list_id;
							
							foreach($leadCountWaiting as $leadWaiting)
							{
								if($count < $importLimit)
								{
									$leadWaiting->list_id = $list_id;
									$leadWaiting->save(false);
									$count++;
								}
								else
								{
									break;
								}
							}
						}
						
						// echo 'Lacking Leads : '.count($leadCountNotWaiting);
						// echo '<br>';
					}
				}
				
				echo '<br>';
			}
			else
			{
				echo $customer->getFullName().' : No assigned customer Skill.';
				echo '<br>';
				echo '<br>';
			}
		}
	}

	public function actionReschedulePatch()
	{
		$models = CalendarAppointment::model()->findAll(array(
			'condition' => '
				t.title = "RESCHEDULE APPOINTMENT"
				AND t.lead_id IS NOT NULL
				AND DATE(t.start_date) >= DATE("2017-07-01")
			',
		));
		
		echo count($models);
		
		echo '<br><br>';
		
		// exit;
		
		if( $models )
		{
			$reschedCtr = 0;
			
			foreach( $models as $model )
			{
				$createResched = false;
				
				$latestCall = LeadCallHistory::model()->find(array(
					'condition' => 'lead_id = :lead_id AND calendar_appointment_id = :calendar_appointment_id',
					'params' => array(
						':lead_id' => $model->lead_id,
						':calendar_appointment_id' => $model->id,
					),
					'order' => 'date_created DESC',
				));
				
				if( $latestCall )
				{
					if( $latestCall->is_skill_child == 1 )
					{
						if( $latestCall->skillChildDisposition->is_appointment_reschedule == 1 ) 
						{
							$existingLeadHopperEntry = LeadHopper::model()->find(array(
								'condition' => 'lead_id = :lead_id AND type = :type',
								'params' => array(
									':lead_id' => $model->lead_id,
									':type' => LeadHopper::TYPE_RESCHEDULE,
								),
							));
							
							if( !$existingLeadHopperEntry )
							{
								$createResched = true;
								
								$reschedCtr++;
								
								echo '<pre>';
									print_r($model->attributes);
								echo '</pre>';
							}
						}
					}
				}
			}
			
			echo '<br><br>reschedCtr: ' . $reschedCtr;
		}
	}

	public function actionTodaysLead()
	{
		$todaysLeads = Yii::app()->db->createCommand()
		->select('SUM(available_leads) as todaysLeads')
		->from('ud_customer_queue_viewer')
		->queryRow(); 
		
		print_r($todaysLeads);
	}

	public function actionRequeueConflict()
	{
		$conflicts = CalendarAppointment::model()->findAll(array(
			'condition' => 'title IN ("SCHEDULE CONFLICT", "LOCATION CONFLICT") AND calendar_id=862',
		));
		
		'count: ' . count($conflicts);
		
		echo '<br><br>';
		
		if( $conflicts )
		{
			$ctr = 0;
			
			foreach( $conflicts as $conflict )
			{
				$lead = $conflict->lead;
				$list = $lead->list;
				
				$existingHopperEntry = LeadHopper::model()->find(array(
					'condition' => 'lead_id = :lead_id AND type = :type',
					'params' => array(
						':lead_id' => $conflict->lead_id,
						':type' => LeadHopper::TYPE_CONFLICT,
					),
				));

				if( empty($existingHopperEntry) )
				{
					$hopperEntry = new LeadHopper;
					
					$hopperEntry->setAttributes(array(
						'lead_id' => $lead->id,
						'list_id' => $list->id,
						'skill_id' => $list->skill_id,
						'customer_id' => $list->customer_id,
						'lead_language' => $lead->language,
						'lead_timezone' => $lead->timezone,
						'type' => LeadHopper::TYPE_CONFLICT,
						'status' => 'READY'
					));
					
					$hopperEntry->calendar_appointment_id = $conflict->id;
				}
				else
				{
					$hopperEntry->status = 'READY';
					$hopperEntry->calendar_appointment_id = $conflict->id;
				}
				
				if( $hopperEntry->save(false) )
				{
					echo $ctr++;
					echo '<br />';
				}
			}
		}
	}

	public function actionCheckDnc()
	{
		$completedDispositions = SkillDisposition::model()->findAll(array(
			'condition' => 'is_complete_leads=1',
		));
		
		$childCompletedDispositions = SkillChildDisposition::model()->findAll(array(
			'condition' => 'is_complete_leads=1',
		));
		
		$dispositions = array();
		
		foreach( $completedDispositions as $completedDisposition )
		{
			if( !in_array($completedDisposition->skill_disposition_name, $dispositions) )
			{
				$dispositions[] = $completedDisposition->skill_disposition_name;
			}
		}
		
		foreach( $childCompletedDispositions as $childCompletedDisposition )
		{
			if( !in_array($childCompletedDisposition->skill_child_disposition_name, $dispositions) )
			{
				$dispositions[] = $childCompletedDisposition->skill_child_disposition_name;
			}
		}
		
		$models = LeadHistory::model()->findAll(array(
			'with' => 'lead',
			'condition' => 't.disposition IN ("'.implode('","', $dispositions).'") AND lead.status=1'
		));
		
		echo 'count: ' . count($models); 
		
		echo '<pre>';
		
		foreach( $models as $model )
		{
			$model->lead->status = 3;
			
			if( $model->lead->save(false) )
			{
				echo 'completed status <br />';
				
				$existingLeadHopperEntry = LeadHopper::model()->find(array(
					'condition' => 'lead_id = :lead_id AND type !=3',
					'params' => array(
						':lead_id' => $model->lead_id,
					),
				));
				
				if( $existingLeadHopperEntry )
				{
					if( $existingLeadHopperEntry->delete() )
					{
						echo 'deleted in hopper <br />';
					}
				}
			}
		}
	}

	public function actionCheckCompleted()
	{
		$models = LeadHopper::model()->findAll(array(
			'with' => 'lead',
			'condition' => 'lead.status!=1 AND t.type!=3',
		));
		
		echo 'count: ' . count($models);
		
		echo '<br><br>';
		
		echo '<pre>';
		
		if( $models )
		{
			foreach( $models as $model )
			{
				// $model->delete();
				
				echo 'Hopper ID: ' . $model->id;
				echo '<br />';
				echo 'Lead ID: ' . $model->lead_id;
				echo '<br />';
				echo 'Call Type: ' . $model->type;
				echo '<br />';
				echo 'home_phone_disposition: ' . $model->lead->home_phone_disposition;
				echo '<br />';
				echo 'mobile_phone_disposition: ' . $model->lead->mobile_phone_disposition;
				echo '<br />';
				echo 'office_phone_disposition: ' . $model->lead->office_phone_disposition;
				echo '<br />';
				echo 'Hopper Status: ' . $model->status;
				echo '<br />';
				echo 'Lead Status: ' . $model->lead->status;
				
				echo '<br><br>';
			}
		}
	}

	public function actionCheckGoalCompleted()
	{
		$customers = Customer::model()->findAll();
		
		echo 'count: ' . count($customers);
		
		echo '<br><br>';
		
		echo date('Y-m-01 00:00:00').' - '.date('Y-m-t 23:59:59');
		
		echo '<br><br>';
		
		$appointmentsMtd = 0;
		$goalReached = 0;
		

		if( $customers )
		{
			foreach( $customers as $customer )
			{
				$customerSkills = CustomerSkill::model()->findAll(array(
					'condition' => 'customer_id = :customer_id AND status=1',
					'params' => array(
						':customer_id' => $customer->id,
					),
				));
				
				if( $customerSkills )
				{
					foreach( $customerSkills as $customerSkill )
					{
						### check if the customer has reached his goal on appointments for the month ###
						
						##get Appointment that has been scheduled ##
						$appointmentSetMTDSql = "
							SELECT COUNT(*) AS totalCount
							FROM (
							   SELECT COUNT(ca.id)
							   FROM ud_calendar_appointment ca
							   LEFT JOIN ud_calendar c ON c.id = ca.calendar_id
							   WHERE ca.title IN ('INSERT APPOINTMENT', 'APPOINTMENT SET', 'CANCEL APPOINTMENT', 'RESCHEDULE APPOINTMENT')
							   AND ca.lead_id IS NOT NULL 
							   AND ca.date_created >= '".date('Y-m-01 00:00:00')."' AND ca.date_created <= '".date('Y-m-t 23:59:59')."'
							   AND c.customer_id = '".$customerSkill->customer->id."'
							   GROUP BY ca.lead_id
							) AS totalCount
						";
						
						$command = Yii::app()->db->createCommand($appointmentSetMTDSql);
						$appointmentSetMTD = $command->queryRow();
						
						$noShowMTDSql = "
							SELECT COUNT(*) AS totalCount
							FROM (
							   SELECT COUNT(ca.id)
							   FROM ud_calendar_appointment ca
							   LEFT JOIN ud_calendar c ON c.id = ca.calendar_id
							   WHERE ca.title IN ('NO SHOW RESCHEDULE')
							   AND ca.lead_id IS NOT NULL 
							   AND ca.date_created >= '".date('Y-m-01 00:00:00')."' AND ca.date_created <= '".date('Y-m-t 23:59:59')."'
							   AND c.customer_id = '".$customerSkill->customer->id."'
							   GROUP BY ca.lead_id
							) AS totalCount
						";
						
						$command = Yii::app()->db->createCommand($noShowMTDSql);
						$noShowMTD = $command->queryRow();
						
						$appointmentCount = $appointmentSetMTD['totalCount'] + $noShowMTD['totalCount'];
						
						if( $noShowMTD['totalCount'] > 3 )
						{
							$appointmentCount = $appointmentSetMTD['totalCount']-3;
						}
						else
						{
							$appointmentCount= $appointmentSetMTD['totalCount']-$noShowMTD['totalCount'];
						}
						
						$appointmentsMtd += $appointmentCount;
						
						##get contract Goal##
						$contractGoal = 0;
						
						if(isset($customerSkill->contract))
						{
							$contract = $customerSkill->contract;
							
							if($contract->fulfillment_type != null )
							{
								if( $contract->fulfillment_type == Contract::TYPE_FULFILLMENT_GOAL_VOLUME )
								{
									if( !empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME]) )
									{
										foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME] as $key => $subsidyLevel )
										{
											$customerSkillLevelArray = $customerSkill->getCustomerSkillLevelArray();
											$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;

											if( $customerSkillLevelArrayGroup != null )
											{							
												if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
												{
													$contractGoal += ( $subsidyLevel['goal'] * $customerSkillLevelArrayGroup->quantity );
												}
											}
										}
									}
									
									echo 'Customer ID:'.$customerSkill->customer->id.' - '.$customerSkill->customer->getFullName().' | Goal: '. $contractGoal.' | Appointment Set: '.$appointmentCount;
									
									if( $appointmentCount > 0 && $contractGoal > 0 && $appointmentCount >= $contractGoal )
									{
										echo ' | Goal Appointment Reached';
										$goalReached++;
									}
									
									echo '<br>';
									
									echo 'appointmentSetMTD: ' . $appointmentSetMTD['totalCount'];
						
									echo '<br>';
									
									echo 'noShowMTD: ' . $noShowMTD['totalCount'];
									
									echo '<br><br>';
								}						
							}	
						}
					}
				}
			}
		}
		
		
		echo '<br><br>';
		
		echo 'Goal Appointment Reached: ' . $goalReached;
		
		echo '<br><br>';
		
		echo 'Customer Queue AppointmentsMtd: ' . $appointmentsMtd;
		
		echo '<br><br>';
		
		$totalAppointmentSetMTDSql = "
			SELECT COUNT(*) AS totalCount
			FROM (
			   SELECT COUNT(id)
			   FROM ud_calendar_appointment
			   WHERE title IN ('INSERT APPOINTMENT', 'APPOINTMENT SET', 'CANCEL APPOINTMENT', 'RESCHEDULE APPOINTMENT')
			   AND lead_id IS NOT NULL 
			   AND date_created >= '".date('Y-m-01 00:00:00')."' AND date_created <= '".date('Y-m-t 23:59:59')."'
			   GROUP BY lead_id
			) AS totalCount
		";
		
		$command = Yii::app()->db->createCommand($totalAppointmentSetMTDSql);
		$totalAppointmentSetMTD = $command->queryRow();
		
		
		echo 'totalAppointmentSetMTD: ' . $totalAppointmentSetMTD['totalCount'];
	}

	public function actionRemoveQueueDups()
	{
		$customerSkillsCount = CustomerSkill::model()->count(array(
			'with' => array('skill', 'contract'),
			'condition' => 't.status=1 AND skill.id IS NOT NULL AND contract.id IS NOT NULL',
		));
		
		$models = CustomerQueueViewer::model()->findAll(array(
			'condition' => 'customer_id',
			'group'=>'customer_id',
			'order' => 'id ASC',
			'distinct'=>true,
		));
		
		
		echo 'customerSkillsCount: ' . $customerSkillsCount;
		
		echo '<br><br>';
		
		echo 'count: ' . count($models);
		
		echo '<br><br>';
		
		if( $models )
		{
			foreach( $models as $model )
			{
				$dups = CustomerQueueViewer::model()->findAll(array(
					'condition'=>'id != :id AND customer_id = :customer_id AND skill_id = :skill_id AND contract_id = :contract_id',
					'params' => array(
						':id' => $model->id,
						':customer_id' => $model->customer_id,
						':skill_id' => $model->skill_id,
						':contract_id' => $model->contract_id,
					),
				));
				
				echo $model->id  . ' | Customer ID: ' . $model->customer_id.' | '. $model->customer->getFullName() . ' | dups: ' . count($dups);
				
				echo '<br><br>';
				
				if( $dups )
				{
					foreach( $dups as $dup )
					{
						if( $dup->delete() )
						{
							echo 'deleted ' . $model->customer->getFullName() . ' dup';
							echo '<br>';
						}
					}
				}
				
				echo '<br><br>';
			}
		}
		
		echo '<br><br>end...';
	}

	public function actionCompareCustomers()
	{
		$queueCustomerIds = array();
		$appCustomerIds = array();
		
		$queues = CustomerQueueViewer::model()->findAll(array(
			'select' => 'customer_id',
			'group'=>'customer_id',
			'order' => 'customer_id ASC',
		));
		
		foreach( $queues as $queue )
		{
			if( !in_array($queue->customer_id, $queueCustomerIds) )
			{
				$queueCustomerIds[] = $queue->customer_id;
			}
		}
		
		$apps = CalendarAppointment::model()->findAll(array(
			'with' => 'calendar',
			'condition' => 't.title IN ("INSERT APPOINTMENT", "APPOINTMENT SET", "CANCEL APPOINTMENT", "RESCHEDULE APPOINTMENT", "NO SHOW RESCHEDULE") AND t.date_created >= "2016-05-01 00:00:00" AND t.date_created <= "2016-05-31 23:59:59"',
			'group' => 'calendar.customer_id',
			'order' => 'calendar.customer_id ASC',
		));
		
		foreach( $apps as $app )
		{
			if( !in_array($app->calendar->customer_id, $appCustomerIds) )
			{
				$appCustomerIds[] = $app->calendar->customer_id;
			}
		}
		
		$results = array_diff($appCustomerIds, $queueCustomerIds);

		// echo '<pre>';
		// print_r($result);
		
		// echo implode(', ', $result);
		
		$appTotal = 0;
		
		foreach( $results as $resultId )
		{
			$customer = Customer::model()->findByPk($resultId);
			
			if( $customer )
			{
				##get Appointment that has been scheduled ##
				$appointmentSetMTDSql = "
					SELECT COUNT(*) AS totalCount
					FROM (
					   SELECT COUNT(ca.id)
					   FROM ud_calendar_appointment ca
					   LEFT JOIN ud_calendar c ON c.id = ca.calendar_id
					   WHERE ca.title IN ('INSERT APPOINTMENT', 'APPOINTMENT SET', 'CANCEL APPOINTMENT', 'RESCHEDULE APPOINTMENT')
					   AND ca.lead_id IS NOT NULL 
					   AND ca.date_created >= '".date('Y-m-01 00:00:00')."' AND ca.date_created <= '".date('Y-m-t 23:59:59')."'
					   AND c.customer_id = '".$resultId."'
					   GROUP BY ca.lead_id
					) AS totalCount
				";
				
				$command = Yii::app()->db->createCommand($appointmentSetMTDSql);
				$appointmentSetMTD = $command->queryRow();
				
				$noShowMTDSql = "
					SELECT COUNT(*) AS totalCount
					FROM (
					   SELECT COUNT(ca.id)
					   FROM ud_calendar_appointment ca
					   LEFT JOIN ud_calendar c ON c.id = ca.calendar_id
					   WHERE ca.title IN ('NO SHOW RESCHEDULE')
					   AND ca.lead_id IS NOT NULL 
					   AND ca.date_created >= '".date('Y-m-01 00:00:00')."' AND ca.date_created <= '".date('Y-m-t 23:59:59')."'
					   AND c.customer_id = '".$resultId."'
					   GROUP BY ca.lead_id
					) AS totalCount
				";
				
				$command = Yii::app()->db->createCommand($noShowMTDSql);
				$noShowMTD = $command->queryRow();
				
				$appointmentCount = $appointmentSetMTD['totalCount'] + $noShowMTD['totalCount'];
				
				if( $noShowMTD['totalCount'] > 3 )
				{
					$appointmentCount = $appointmentSetMTD['totalCount']-3;
				}
				else
				{
					$appointmentCount= $appointmentSetMTD['totalCount']-$noShowMTD['totalCount'];
				}
				
				echo 'Customer ID: ' . $customer->id.' => '. $customer->firstname.' '.$customer->lastname .' | Appointment Set: ' . $appointmentCount;
				echo '<br>';
				
				$appTotal += $appointmentCount;
			}
		}
		
		echo '<br><br>appTotal: ' . $appTotal;
	}

	public function actionUpdateGoals()
	{
		$totalAppointmentCount = 0;
		
		$customerQueues = CustomerQueueViewer::model()->findAll(array(
			'order' => 'priority DESC'
		));
		
		echo 'count: ' . count($customerQueues);

		echo '<br><br>';
		
		if( $customerQueues )
		{
			foreach( $customerQueues as $customerQueue )
			{
				$customerSkill = CustomerSkill::model()->find(array(
					'condition' => 'customer_id = :customer_id AND skill_id = :skill_id AND status=1',
					'params' => array(
						':customer_id' => $customerQueue->customer_id,
						':skill_id' => $customerQueue->skill_id,
					),
				));
				
				if( $customerSkill )
				{
					##get Appointment that has been scheduled ##
					$appointmentSetMTDSql = "
						SELECT count(distinct lch.lead_id) AS totalCount 
						FROM ud_lead_call_history lch 
						LEFT JOIN ud_lists ls ON ls.id = lch.list_id 
						LEFT JOIN ud_calendar_appointment ca ON ca.id = lch.calendar_appointment_id 
						WHERE ca.title IN ('INSERT APPOINTMENT', 'APPOINTMENT SET', 'CANCEL APPOINTMENT', 'RESCHEDULE APPOINTMENT') 
						AND lch.disposition = 'Appointment Set'
						AND lch.date_created >= '".date('Y-m-01 00:00:00')."' 
						AND ca.date_created <= '".date('Y-m-t 23:59:59')."'
						AND lch.customer_id = '".$customerSkill->customer->id."'
						AND ls.skill_id = '".$customerSkill->skill_id."' 
					";
				
					
					$command = Yii::app()->db->createCommand($appointmentSetMTDSql);
					$appointmentSetMTD = $command->queryRow();
					
					$noShowMTDSql = "
						SELECT count(distinct lch.lead_id) AS totalCount 
						FROM ud_lead_call_history lch 
						LEFT JOIN ud_lists ls ON ls.id = lch.list_id 
						LEFT JOIN ud_calendar_appointment ca ON ca.id = lch.calendar_appointment_id 
						WHERE ca.title IN ('NO SHOW RESCHEDULE')
						AND lch.disposition = 'Appointment Set'
						AND lch.date_created >= '".date('Y-m-01 00:00:00')."' 
						AND ca.date_created <= '".date('Y-m-t 23:59:59')."'
						AND lch.customer_id = '".$customerSkill->customer->id."'
						AND ls.skill_id = '".$customerSkill->skill_id."' 
					";
					
					
					$command = Yii::app()->db->createCommand($noShowMTDSql);
					$noShowMTD = $command->queryRow();
					
					$noShowCount = $noShowMTD['totalCount'];
					
					$appointmentSetCount = $appointmentSetMTD['totalCount'] + $noShowMTD['totalCount'];
					
					if( $noShowMTD['totalCount'] > 3 )
					{
						$appointmentSetCount = $appointmentSetMTD['totalCount']-3;
					}
					else
					{
						$appointmentSetCount = $appointmentSetCount-$noShowMTD['totalCount'];
					}
					
					$totalAppointmentCount += $appointmentSetCount;
					
					$customerQueue->current_goals = $appointmentSetCount;
					$customerQueue->no_show = $noShowCount;
							
					### check if the customer has reached his goal on appointments for the month ###
					if( $contract->fulfillment_type == Contract::TYPE_FULFILLMENT_GOAL_VOLUME )
					{
						if( !empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME]) )
						{
							foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME] as $key => $subsidyLevel )
							{
								$customerSkillLevelArray = $customerSkill->getCustomerSkillLevelArray();
								$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;

								if( $customerSkillLevelArrayGroup != null )
								{							
									if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
									{
										$contractGoal += ( $subsidyLevel['goal'] * $customerSkillLevelArrayGroup->quantity );
									}
								}
							}
						}
						
						// $customerIsCallable = $customerIsCallable && ($contractGoal > $appointmentSetCount);
						echo 'Customer ID:'.$customerSkill->customer->id.' | Goal: '. $contractGoal.' | Appointment Set: '.$appointmentSetCount.' | Priority: ' . $customerQueue->priority;
						echo '<br>';
						
						if( $appointmentSetCount > 0 && $contractGoal > 0 && $appointmentSetCount >= $contractGoal )
						{
							$customerQueue->next_available_calling_time = 'Goal Appointment Reached';
						}
					}
					
					if( $customerQueue->save(false) )
					{
						echo $customerQueue->customer->getFullName().' current_goals updated: ' . $appointmentSetCount;
						echo '<br>';
						echo '<br>';
					}
				}
			}
		}
		
		echo '<br><br>total: ' . $totalAppointmentCount;
	}

	public function actionUpdatePriorities()
	{
		$existingQueueViewers = CustomerQueueViewer::model()->findAll(array(
			// 'condition' => 'customer_id=928',
			// 'offset' => $evaluationOffset->value,
			// 'limit' => 5,
			'order' => 'priority DESC',
		)); 
		
		$ctr = 0;
		
		foreach( $existingQueueViewers as $existingQueueViewer )
		{
			$customerSkill = CustomerSkill::model()->find(array(
				'condition' => 'customer_id = :customer_id AND skill_id = :skill_id AND status=1',
				'params' => array(
					':customer_id' => $existingQueueViewer->customer_id,
					':skill_id' => $existingQueueViewer->skill_id,
				),
			));
			
			if( $customerSkill )
			{
				$priority = 0;
				$pace = 0;
				$currentDials = 0;
				$availableLeads = 0;
				$notCompletedLeads = 0;
				$totalLeads = 0;
				$totalPotentialDials = 0;
				$calledLeadCount = 0;
				$dialsNeeded = 0;
				$maxDials = 0;
				$appointmentSetCount = 0;
				
				$availableCallingBlocks = '';
				
				$availableCallingBlock_A = 0;
				$availableCallingBlock_B = 0;
				$availableCallingBlock_C = 0;
				
				$callAgent = '';
				
				$nextAvailableCallingTime = $this->checkTimeZone($customerSkill);
				
				$numberOfWorkingDays = $this->getWorkingDaysForThisMonth(date('Y-m-1'), date('Y-m-t'), 'totalCount');
				$dialingDaysInBillingCycle = $this->getWorkingDaysForThisMonth(date('Y-m-1'), date('Y-m-t'), 'pastCount');	
				
				$customer = Customer::model()->findByPk($customerSkill->customer_id);
				$skill = Skill::model()->findByPk($customerSkill->skill_id);
				$contract = $customerSkill->contract;
				
				$maxDials = $skill->max_dials;
				
				$customerIsCallable = false;	

				if( isset($customerSkill->contract) && isset($customerSkill->customer) && $customerSkill->customer->status == 1 && $customerSkill->customer->is_deleted == 0 && time() >= strtotime($customerSkill->start_month) )
				{
					$customerIsCallable = true;
				}
				
				if( $customerSkill->is_contract_hold == 1 )
				{
					if( !empty($customerSkill->is_contract_hold_start_date) && !empty($customerSkill->is_contract_hold_end_date) )
					{
						if( time() >= strtotime($customerSkill->is_contract_hold_start_date) && time() <= strtotime($customerSkill->is_contract_hold_end_date) )
						{
							$customerIsCallable = false;
							
							$nextAvailableCallingTime = 'On Hold';
						}
					}
				}
				
				if( !empty($customerSkill->end_month) && date('Y', strtotime($customerSkill->end_month)) >= 2015 )
				{
					if( time() >= strtotime($customerSkill->end_month) )
					{
						$customerIsCallable = false;
						
						$nextAvailableCallingTime = 'Cancelled';
					}
				}
				
				if( $existingQueueViewer )
				{
					if( !empty($existingQueueViewer->removal_start_date) && !empty($existingQueueViewer->removal_end_date) )
					{
						if( time() >= strtotime($existingQueueViewer->removal_start_date) && time() <= strtotime($existingQueueViewer->removal_end_date) )
						{
							$customerIsCallable = false;
							
							$nextAvailableCallingTime = 'Removed';
						}
					}
				}
				
				
				if( !$customerIsCallable )
				{
					$existingQueueViewer->status = 2;
					$existingQueueViewer->save(false);
				}
				
				
				if($contract->fulfillment_type != null )
				{
					##get Appointment that has been scheduled ##
					$appointmentSetMTDSql = "
						SELECT COUNT(*) AS totalCount
						FROM (
						   SELECT COUNT(ca.id)
						   FROM ud_calendar_appointment ca
						   LEFT JOIN ud_calendar c ON c.id = ca.calendar_id
						   WHERE ca.title IN ('INSERT APPOINTMENT', 'APPOINTMENT SET', 'CANCEL APPOINTMENT', 'RESCHEDULE APPOINTMENT')
						   AND ca.lead_id IS NOT NULL 
						   AND ca.date_created >= '".date('Y-m-01 00:00:00')."' AND ca.date_created <= '".date('Y-m-t 23:59:59')."'
						   AND c.customer_id = '".$customerSkill->customer->id."'
						   GROUP BY ca.lead_id
						) AS totalCount
					";
					
					$command = Yii::app()->db->createCommand($appointmentSetMTDSql);
					$appointmentSetMTD = $command->queryRow();
					
					$noShowMTDSql = "
						SELECT COUNT(*) AS totalCount
						FROM (
						   SELECT COUNT(ca.id)
						   FROM ud_calendar_appointment ca
						   LEFT JOIN ud_calendar c ON c.id = ca.calendar_id
						   WHERE ca.title IN ('NO SHOW RESCHEDULE')
						   AND ca.lead_id IS NOT NULL 
						   AND ca.date_created >= '".date('Y-m-01 00:00:00')."' AND ca.date_created <= '".date('Y-m-t 23:59:59')."'
						   AND c.customer_id = '".$customerSkill->customer->id."'
						   GROUP BY ca.lead_id
						) AS totalCount
					";
					
					$command = Yii::app()->db->createCommand($noShowMTDSql);
					$noShowMTD = $command->queryRow();
					
					$appointmentCount = $appointmentSetMTD['totalCount'] + $noShowMTD['totalCount'];
					
					if( $noShowMTD['totalCount'] > 3 )
					{
						$appointmentCount = $appointmentSetMTD['totalCount']-3;
					}
					else
					{
						$appointmentCount= $appointmentSetMTD['totalCount']-$noShowMTD['totalCount'];
					}
					
					if( $contract->fulfillment_type == Contract::TYPE_FULFILLMENT_GOAL_VOLUME )
					{
						if( !empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME]) )
						{
							foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME] as $key => $subsidyLevel )
							{
								$customerSkillLevelArray = $customerSkill->getCustomerSkillLevelArray();
								$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;

								if( $customerSkillLevelArrayGroup != null )
								{							
									if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
									{
										$totalLeads += ( $subsidyLevel['goal'] * $customerSkillLevelArrayGroup->quantity );
									}
								}
							}
							
							if( $appointmentCount > 0 && $totalLeads > 0 && $appointmentCount >= $totalLeads )
							{
								$nextAvailableCallingTime = 'Goal Appointment Reached';
							}
						}
						
						$customerExtras = CustomerExtra::model()->findAll(array(
							'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id AND status=1 AND year = :year AND month = :month',
							'params' => array(
								':customer_id' => $customerSkill->customer_id,
								':contract_id' => $customerSkill->contract_id,
								':skill_id' => $customerSkill->skill_id,
								':year' => date('Y'),
								':month' => date('m'),
							),
						));
						
						if( $customerExtras )
						{
							foreach( $customerExtras as $customerExtra )
							{
								$totalLeads += $customerExtra->quantity;
							}
						}
					}
					else
					{
						if( !empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME]) )
						{
							foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME] as $key => $subsidyLevel )
							{
								$customerSkillLevelArray = $customerSkill->getCustomerSkillLevelArray();
								
								$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;
								
								if( $customerSkillLevelArrayGroup != null )
								{
									if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
									{
										$totalLeads += ( $subsidyLevel['high'] * $customerSkillLevelArrayGroup->quantity );
									}
								}
							}
						}
						
						$customerExtras = CustomerExtra::model()->findAll(array(
							'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id AND status=1 AND year = :year AND month = :month',
							'params' => array(
								':customer_id' => $customerSkill->customer_id,
								':contract_id' => $customerSkill->contract_id,
								':skill_id' => $customerSkill->skill_id,
								':year' => date('Y'),
								':month' => date('m'),
							),
						));
						
						if( $customerExtras )
						{
							foreach( $customerExtras as $customerExtra )
							{
								$totalLeads += $customerExtra->quantity;
							}
						}
					}
				}
				
				//get total potential dials
				if( $customerSkill->contract->fulfillment_type == 1 )
				{
					$appointmentSetMTDSql = "
						SELECT COUNT(*) AS totalCount
						FROM (
						   SELECT COUNT(ca.id)
						   FROM ud_calendar_appointment ca
						   LEFT JOIN ud_calendar c ON c.id = ca.calendar_id
						   WHERE ca.title IN ('INSERT APPOINTMENT', 'APPOINTMENT SET', 'CANCEL APPOINTMENT', 'RESCHEDULE APPOINTMENT')
						   AND ca.lead_id IS NOT NULL 
						   AND ca.date_created >= '".date('Y-m-01 00:00:00')."' AND ca.date_created <= '".date('Y-m-t 23:59:59')."'
						   AND c.customer_id = '".$customerSkill->customer->id."'
						   GROUP BY ca.lead_id
						) AS totalCount
					";
					
					$command = Yii::app()->db->createCommand($appointmentSetMTDSql);
					$appointmentSetMTD = $command->queryRow();
					
					$noShowMTDSql = "
						SELECT COUNT(*) AS totalCount
						FROM (
						   SELECT COUNT(ca.id)
						   FROM ud_calendar_appointment ca
						   LEFT JOIN ud_calendar c ON c.id = ca.calendar_id
						   WHERE ca.title IN ('NO SHOW RESCHEDULE')
						   AND ca.lead_id IS NOT NULL 
						   AND ca.date_created >= '".date('Y-m-01 00:00:00')."' AND ca.date_created <= '".date('Y-m-t 23:59:59')."'
						   AND c.customer_id = '".$customerSkill->customer->id."'
						   GROUP BY ca.lead_id
						) AS totalCount
					";
					
					$command = Yii::app()->db->createCommand($noShowMTDSql);
					$noShowMTD = $command->queryRow();
					
					$appointmentSetCount = $appointmentSetMTD['totalCount'] + $noShowMTD['totalCount'];
					
					if( $noShowMTD['totalCount'] > 3 )
					{
						$appointmentSetCount = $appointmentSetMTD['totalCount']-3;
					}
					else
					{
						$appointmentSetCount= $appointmentSetMTD['totalCount']-$noShowMTD['totalCount'];
					}
					
					$totalPotentialDials = ($totalLeads - $appointmentSetCount);
				}
				
				
				$calledLeadSql = "
					SELECT COUNT(DISTINCT(lead_id)) as total_count
					FROM ud_lead_call_history lch 
					WHERE start_call_time >= '".date('Y-m-01 00:00:00')."'
					AND start_call_time <= '".date('Y-m-t 23:59:59')."'
					AND customer_id='".$customerSkill->customer->id."'
				";
				
				$command = Yii::app()->db->createCommand($calledLeadSql);
				$calledLeadResult = $command->queryRow();
				
				$calledLeadCount = $calledLeadResult['total_count'];
				
				$currentDialsSql = "
					SELECT COUNT(id) as total_count
					FROM ud_lead_call_history lch 
					WHERE start_call_time >= '".date('Y-m-01 00:00:00')."'
					AND start_call_time <= '".date('Y-m-t 23:59:59')."'
					AND customer_id='".$customerSkill->customer->id."'
				";
				
				$command = Yii::app()->db->createCommand($currentDialsSql);
				$currentDialsResult = $command->queryRow();
				
				$currentDials = $currentDialsResult['total_count'];
				
				echo '<br />';
				echo 'Record #: ' . $ctr++;
				echo '<br />';
				echo 'Customer Name: ' . $customer->getFullName();
				echo '<br />';
				echo $customerSkill->contract->fulfillment_type == 1 ? 'Fulfillment Type: Goal' : 'Fulfillment Type: Lead';
				echo '<br />';
				echo 'Contracted Lead/Goals: ' . $totalLeads;
				echo '<br />';
				echo 'Number of dialing Days: ' . $numberOfWorkingDays;
				echo '<br />';
				echo '<br />';
				echo 'Dialing days in billing cycle: ' . $dialingDaysInBillingCycle;
				echo '<br />';
				echo 'Max Dials: ' . $maxDials; 
				echo '<br />';
				echo 'Current Goal Count: ' . $appointmentSetCount;
				echo '<br />';  
				echo 'Current Dials: ' . $currentDials;
				echo '<br />';  
				echo 'Called Lead: ' . $calledLeadCount;
				echo '<br />';  
				
				if( $totalLeads > 0 )
				{
					//calculate customer priority
					if( $customerSkill->contract->fulfillment_type == 1 )
					{
						$pace = (($totalLeads / $numberOfWorkingDays) * $dialingDaysInBillingCycle);
						
						$pace = round($pace);
						
						$dialsNeeded = $pace - $appointmentSetCount;
						
						if( $dialsNeeded > 0 && $pace > 0 ) 
						{
							$priority = 1-($appointmentSetCount / $pace);
							
							$priority = number_format(round($priority, 1), 2);
						}
					}
					else
					{							
						$pace = ((($totalLeads * $maxDials) / $numberOfWorkingDays) * $dialingDaysInBillingCycle);
						
						$pace = round($pace);
						
						$dialsNeeded = (((($totalLeads * $maxDials) / $numberOfWorkingDays) * $dialingDaysInBillingCycle) - $calledLeadCount);
				
						$priority = ( (((($totalLeads * $maxDials) / $numberOfWorkingDays) * $dialingDaysInBillingCycle) - $calledLeadCount) / ((($totalLeads * $maxDials) / $numberOfWorkingDays) * $dialingDaysInBillingCycle) );
					
						$priority = round($priority, 4);
					}
					
					
					$existingQueueViewer->priority = $priority;
					$existingQueueViewer->pace = $pace;
					$existingQueueViewer->current_goals = $appointmentSetCount;
					$existingQueueViewer->current_dials = $currentDials;
					$existingQueueViewer->next_available_calling_time = $nextAvailableCallingTime;
					$existingQueueViewer->save(false);
					

					echo 'Rounded Pace Calculation: ' . $pace;
					echo '<br />';
					echo 'Rounded Priority Calculation: ' . $priority;
					echo '<br />';
					
					
					
					echo '<br />';
					echo '<hr>';
					echo '<br />';
				}
			}
		}
	}

	
	public function actionUpdateStatus()
	{
		$customerQueues = CustomerQueueViewer::model()->findAll(array(
			// 'condition' => 'customer_id=506',
			'order' => 'priority DESC'
		));
		
		echo 'count: ' . count($customerQueues);

		echo '<br><br>';
		
		$dbUpdates = 0;
		
		if( $customerQueues )
		{
			foreach( $customerQueues as $customerQueue )
			{
				$customerSkill = CustomerSkill::model()->find(array(
					'condition' => 'customer_id = :customer_id AND skill_id = :skill_id AND status=1',
					'params' => array(
						':customer_id' => $customerQueue->customer_id,
						':skill_id' => $customerQueue->skill_id,
					),
				));
				
				if( $customerSkill )
				{
					$contract = $customerSkill->contract;
					
					$nextAvailableCallingTime = $this->checkTimeZone($customerSkill);
					
					$customerIsCallable = false;
					
					$status = 1;					

					
					if( isset($customerSkill->contract) && isset($customerSkill->customer) && $customerSkill->customer->status == 1 && $customerSkill->customer->is_deleted == 0 && time() >= strtotime($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00' )
					{
						$customerIsCallable = true;
					}
					
					if( !$customerIsCallable )
					{
						if( $customerSkill->start_month == '0000-00-00' )
						{
							$nextAvailableCallingTime = 'Blank Start Date';
						}
						
						if( $customerSkill->start_month != '0000-00-00' && strtotime($customerSkill->start_month) > time() )
						{
							$nextAvailableCallingTime = 'Future Start Date';
						}
					}
					
					if( $customerSkill->is_contract_hold == 1 )
					{
						if( !empty($customerSkill->is_contract_hold_start_date) && !empty($customerSkill->is_contract_hold_end_date) )
						{
							if( time() >= strtotime($customerSkill->is_contract_hold_start_date) && time() <= strtotime($customerSkill->is_contract_hold_end_date) )
							{
								$customerIsCallable = false;
								
								$nextAvailableCallingTime = 'On Hold';
							}
						}
					}
					
					if( !empty($customerSkill->end_month) && date('Y', strtotime($customerSkill->end_month)) >= 2015 )
					{
						if( time() >= strtotime($customerSkill->end_month) )
						{
							$customerIsCallable = false;
							
							$nextAvailableCallingTime = 'Cancelled';
						}
					}
					
					if( $customerQueue )
					{
						if( !empty($customerQueue->removal_start_date) && !empty($customerQueue->removal_end_date) )
						{
							if( time() >= strtotime($customerQueue->removal_start_date) && time() <= strtotime($customerQueue->removal_end_date) )
							{
								$customerIsCallable = false;
								
								$nextAvailableCallingTime = 'Removed';
							}
						}
					}
					
					##get contract Goal##
					$contractGoal = 0;
					if(isset($customerSkill->contract))
					{
						$contract = $customerSkill->contract;
						if($contract->fulfillment_type != null )
						{
							##get Appointment that has been scheduled ##
							$appointmentSetMTDSql = "
								SELECT COUNT(*) AS totalCount
								FROM (
								   SELECT COUNT(ca.id)
								   FROM ud_calendar_appointment ca
								   LEFT JOIN ud_calendar c ON c.id = ca.calendar_id
								   WHERE ca.title IN ('INSERT APPOINTMENT', 'APPOINTMENT SET', 'CANCEL APPOINTMENT', 'RESCHEDULE APPOINTMENT')
								   AND ca.lead_id IS NOT NULL 
								   AND ca.date_created >= '".date('Y-m-01 00:00:00')."' AND ca.date_created <= '".date('Y-m-t 23:59:59')."'
								   AND c.customer_id = '".$customerSkill->customer->id."'
								   GROUP BY ca.lead_id
								) AS totalCount
							";
							
							$command = Yii::app()->db->createCommand($appointmentSetMTDSql);
							$appointmentSetMTD = $command->queryRow();
							
							$noShowMTDSql = "
								SELECT COUNT(*) AS totalCount
								FROM (
								   SELECT COUNT(ca.id)
								   FROM ud_calendar_appointment ca
								   LEFT JOIN ud_calendar c ON c.id = ca.calendar_id
								   WHERE ca.title IN ('NO SHOW RESCHEDULE')
								   AND ca.lead_id IS NOT NULL 
								   AND ca.date_created >= '".date('Y-m-01 00:00:00')."' AND ca.date_created <= '".date('Y-m-t 23:59:59')."'
								   AND c.customer_id = '".$customerSkill->customer->id."'
								   GROUP BY ca.lead_id
								) AS totalCount
							";
							
							$command = Yii::app()->db->createCommand($noShowMTDSql);
							$noShowMTD = $command->queryRow();
							
							$appointmentCount = $appointmentSetMTD['totalCount'] + $noShowMTD['totalCount'];
							
							if( $noShowMTD['totalCount'] > 3 )
							{
								$appointmentCount = $appointmentSetMTD['totalCount']-3;
							}
							else
							{
								$appointmentCount= $appointmentSetMTD['totalCount']-$noShowMTD['totalCount'];
							}
							
							$customerQueue->current_goals = $appointmentCount;
							
							### check if the customer has reached his goal on appointments for the month ###
							if( $contract->fulfillment_type == Contract::TYPE_FULFILLMENT_GOAL_VOLUME )
							{
								if( !empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME]) )
								{
									foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME] as $key => $subsidyLevel )
									{
										$customerSkillLevelArray = $customerSkill->getCustomerSkillLevelArray();
										$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;

										if( $customerSkillLevelArrayGroup != null )
										{							
											if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
											{
												$contractGoal += ( $subsidyLevel['goal'] * $customerSkillLevelArrayGroup->quantity );
											}
										}
									}
								}
								
								// $customerIsCallable = $customerIsCallable && ($contractGoal > $appointmentCount);
								echo 'Customer ID:'.$customerSkill->customer->id.' | Goal: '. $contractGoal.' | Appointment Set: '.$appointmentCount.' | Priority: ' . $customerQueue->priority;
								echo '<br>';
								
								if( $appointmentCount > 0 && $contractGoal > 0 && $appointmentCount >= $contractGoal )
								{
									$nextAvailableCallingTime = 'Goal Appointment Reached';
								}
							}						
						}	
					}
					
					if( !$customerIsCallable )
					{
						$customerQueue->status = 2;
						
						echo 'Deleted Leads of Customer ID: ' . $customerQueue->customer_id.' - '.$customerQueue->customer_name;
						echo '<br>';
						
						LeadHopper::model()->deleteAll(array(
							'condition' => 'customer_id = :customer_id AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
							'params' => array(
								':customer_id' => $customerQueue->customer_id,
							),
						));
					}
					
					$customerQueue->next_available_calling_time = $nextAvailableCallingTime;
					
					if( $customerQueue->save(false) )
					{
						$dbUpdates++;	
						
						echo $customerQueue->customer_name;
						echo '<br>';
						echo $customerQueue->next_available_calling_time;
						echo '<br>';
						echo $customerSkill->start_month;
						echo '<br>';
						echo '<br>';
					}
				}
			}
		}
		
		echo '<br><br>';
		
		echo 'dbUpdates: ' . $dbUpdates;
	}

	
	public function actionUpdateHoldDate()
	{
		exit;
		
		$customerQueues = CustomerQueueViewer::model()->findAll(array(
			// 'condition' => 'customer_id=506',
			'order' => 'priority DESC'
		));
		
		echo 'count: ' . count($customerQueues);

		echo '<br><br>';
		
		$dbUpdates = 0;
		
		if( $customerQueues )
		{
			foreach( $customerQueues as $customerQueue )
			{
				$customerSkill = CustomerSkill::model()->find(array(
					'condition' => 'customer_id = :customer_id AND skill_id = :skill_id AND status=1',
					'params' => array(
						':customer_id' => $customerQueue->customer_id,
						':skill_id' => $customerQueue->skill_id,
					),
				));
				
				if( $customerSkill )
				{
					if( date('Y-m-d', strtotime($customerSkill->is_contract_hold_end_date)) == '2016-07-01' )
					{
						// $customerSkill->is_contract_hold_end_date = '2016-07-01';
						// $customerSkill->save(false);

						echo 'Customer: ' . $customerSkill->customer->getFullName().' => Hold End Date: ' . $customerSkill->is_contract_hold_end_date;
						echo '<br>';
						echo 'Deleted Leads of Customer ID: ' . $customerQueue->customer_id.' - '.$customerQueue->customer_name;
						echo '<br>';
						
						LeadHopper::model()->deleteAll(array(
							'condition' => 'customer_id = :customer_id AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
							'params' => array(
								':customer_id' => $customerQueue->customer_id,
							),
						));
						echo '<br>';
						echo '<br>';
						
						$customerQueue->next_available_calling_time = 'On Hold';
						$customerQueue->status = 2;
						$customerQueue->save(false);
						
						$dbUpdates++;
					}
				}
			}
		}
		
		echo '<br><br>';
		echo 'dbUpdates: ' . $dbUpdates;
	}

	public function actionCreditCardPatch()
	{
		$dbUpdates = 0;
		
		$models = CustomerBilling::model()->findAll(array(
			'condition' => 'customer_id=0',
		));
		
		echo 'count: ' . count($models);
		
		echo '<br><br>';
		
		if( $models )
		{
			foreach( $models as $model )
			{
				if( $model->credit_card_id != null || $model->echeck_id != null )
				{
					if( $model->credit_card_id != null )
					{
						$customer = $model->creditCard->customer;
					}
					else
					{
						$customer = $model->echeck->customer;
					}
					
					if( isset($customer) )
					{
						echo 'Customer Name: ' . $customer->getFullName();
						echo '<br>';
						echo '<br>';
						
						$model->customer_id = $customer->id;
						
						if( $model->save(false) )
						{
							$dbUpdates++;
						}					
					}
				}
			}
		}
		
		
		echo '<br><br>dbUpdates: ' . $dbUpdates;
	}

	public function actionStateFarmAgentPatch()
	{
		$models = StateFarmAgentLevelReport::model()->findAll(array(
			'condition' => 'program_type="Goal"',
		));
		
		$dbUpdates = 0;
		
		echo 'count: ' . count($models);
		
		echo '<br><br>';
		
		if( $models )
		{
			foreach( $models as $model )
			{
				$maxLimit = 10 * $model->contracted_quantity;
				
				if( $model->names_submitted > $maxLimit )
				{
					$model->names_submitted = $maxLimit;
					
					if( $model->save(false) )
					{
						echo $dbUpdates++;
						echo '<br>';
					}
				}
			}
		}
		
		echo '<br><br>dbUpdates: ' . $dbUpdates;
	}

	
	public function actionHopperRemoveRegularContacts()
	{
		$models = LeadHopper::model()->findAll(array('condition'=>'type=1'));
		// $models = LeadHopper::model()->findAll(array(
			// 'condition' => '
				// customer_id = "1966"
				// AND skill_id = "36"
				// AND type = 1
				// AND status = "DONE"
			// '
		// ));
		
		echo 'count: ' . count($models);
		
		if( $models )
		{
			foreach( $models as $model )
			{
				$model->delete();
				
				echo $ctr++;
				echo '<br>';
			}
		}
		
		echo '<br><br>ctr: '.$ctr;
	}

	public function actionDeleteCustomerLeads()
	{
		$models = Lead::model()->findAll(array(
			'condition' => 'customer_id = :customer_id AND list_id = :list_id',
			'params' => array(
				':customer_id' => 1894,
				':list_id' => 8015,
			),
			'limit' => 8
		));
		
		echo 'count: ' . count($models);
		
		echo '<br><br>';
		
		$ctr = 0;
		
		if( $models )
		{
			foreach( $models as $model )
			{
				// $model->delete();
				$model->list_id = null;
				$model->save(false);
				
				echo $ctr++;
				echo '<br>';
			}
		}
		
		echo '<br><br> ctr: ' . $ctr;
	}

	public function actionEmailMonitorFix()
	{
		$models = EmailMonitor::model()->findAll(array(
			'with' => 'calendarAppointment',
			'condition' => '
				t.calendar_appointment_id IS NOT NULL 
				AND DATE(calendarAppointment.start_date) <= DATE(NOW())
				AND t.status IN ( 0, 2, 4 ) 
			',
		));
		
		echo 'count: ' . count($models);
		echo '<br><br>';
		
		$ctr = 0;
		
		if( $models )
		{
			foreach( $models as $model )
			{
				$model->status = 3;
				
				if( $model->save(false) )
				{
					echo $ctr++;
					echo '<br>';
				}
			}
		}
		
		echo '<br><br> ctr: ' . $ctr;
	}

	
	public function actionBillingPeriodPatch()
	{
		exit;
		
		$models = CustomerBilling::model()->findAll(array(
			'condition' => 'billing_period LIKE "%2015%" AND YEAR(date_created) = 2016',
		));
		
		$dbUpdates = 0;
		
		echo 'count: ' . count($models);
		
		echo '<br><br>';
		
		if( $models )
		{
			foreach( $models as $model )
			{
				$newBillingPeriod = str_replace('2015', '2016', $model->billing_period);
				
				$model->billing_period = $newBillingPeriod;
				
				// echo $model->billing_period .' => '.$newBillingPeriod;
				// echo '<br><br>';
				 
				if( $model->save(false) )
				{
					echo $dbUpdates++;
					echo '<br>';
				}
			}
		}
		
		echo 'dbUpdates: ' . $dbUpdates;	
	}

	
	public function actionGrowthReportPatch()
	{
		$models = CustomerQueueViewer::model()->findAll(array(
			// 'condition' => 'customer_id IN ()',
		));
		
		echo 'count: ' . count($models);
		
		echo '<br><br>';
		
		$dbUpdates = 0;
		
		if( $models )
		{
			foreach( $models as  $model )
			{
				$enrollmentDate = null;
				$historyEndDateChanged = null;
				$historyEndDateChanger = null;
				
				$enrollmentHistory = CustomerHistory::model()->find(array(
					'select' => 'date_created',
					'condition' => 'customer_id = :customer_id AND content LIKE "%Registered on%"',
					'params' => array(
						':customer_id' => $model->customer_id,
					),
					'order' => 'date_created DESC',
				));
				
				if( $enrollmentHistory )
				{
					$enrollmentDate = $enrollmentHistory->date_created;
				}
				
				
				$endDateHistory = CustomerHistory::model()->find(array(
					'select' => 'user_account_id, date_created',
					'condition' => 'customer_id = :customer_id AND content LIKE "%End Date Changed from%"',
					'params' => array(
						':customer_id' => $model->customer_id,
					),
					'order' => 'date_created DESC',
				));
				
				if( $endDateHistory )
				{
					// echo '<br><br>';
					
					// echo 'End Date History: ';
					
					// echo '<br>';
					
					// echo '<pre>';
						// print_r($endDateHistory->attributes);
					// echo '</pre>';
					
					// echo '<br><br>';
					
					$historyEndDateChanged = $endDateHistory->date_created;
					
					if( isset($endDateHistory->account->accountUser)  )
					{
						$accountUser = $endDateHistory->account->accountUser;
						
						$historyEndDateChanger = $accountUser->first_name.' '.$accountUser->last_name;
					}
				}
				
				$model->setAttributes(array(
					'account_date_created' => $model->customer->account->date_created,
					'enrollment_date' => $enrollmentDate,
					'history_end_date_changed' => $historyEndDateChanged,
					'history_end_date_changer' => $historyEndDateChanger,
				));
				
				if( $model->save(false) )
				{
					echo $dbUpdates++;
					echo '<br>';
				}
			}
		}
		
		echo '<br><br>dbUpdates: ' . $dbUpdates;
	}

	
	public function actionCompletedLeadsPatch()
	{
		$dbUpdates = 0;
		
		$models = LeadCallHistory::model()->findAll(array(
			'with' => array('lead', 'skillDisposition'),
			'condition' => '
				lead.status = 1
				AND lead.number_of_dials > 0
				AND lead.recycle_date IS NOT NULL
				AND lead.recycle_lead_call_history_id IS NOT NULL
				AND lead.recycle_lead_call_history_disposition_id IS NOT NULL
				AND 
				( 
					skillDisposition.is_complete_leads = 1
					OR skillDisposition.is_do_not_call = 1
					OR skillDisposition.is_bad_phone_number = 1
				) 
			',
			'order' => 't.date_created DESC'
		));
		
		echo 'primary count: ' . count($models);
		
		echo '<br><br>';
		
		if( $models )
		{
			foreach( $models as $model )
			{
				$model->lead->status = 3;
				
				if( !empty($model->skillDisposition->recycle_interval) )
				{
					$time = strtotime(date("Y-m-d", strtotime($model->date_created)));
					$finalDate = date("Y-m-d", strtotime("+".($model->skillDisposition->recycle_interval * 30)." day", $time));
					$model->lead->recycle_date = $finalDate;
					$model->lead->recycle_lead_call_history_id = $model->id;
					$model->lead->recycle_lead_call_history_disposition_id = $model->disposition_id;
				}
				
				if( $model->lead->save(false) )
				{
					$dbUpdates++;
					
					echo '#' . $dbUpdates.' | Lead ID: '.$model->lead_id.' | '.$model->lead->first_name.' '.$model->lead->last_name.' => '.$model->disposition . ' | '. date('m/d/Y g:i A', strtotime($model->date_created));
					echo '<br>';
					echo '<br>';
				}
				else
				{
					echo '<pre>';
						print_r($model->getErrors());
					exit;
				}
			}
		}
		
		echo '<br><br>';
		echo '<br><br>';
		
		$models2 = LeadCallHistory::model()->findAll(array(
			'with' => array('lead', 'skillChildDisposition'),
			'condition' => '
				lead.status = 1
				AND lead.number_of_dials > 0
				AND lead.recycle_date IS NOT NULL
				AND lead.recycle_lead_call_history_id IS NOT NULL
				AND lead.recycle_lead_call_history_disposition_id IS NOT NULL
				AND 
				( 
					skillChildDisposition.is_complete_leads = 1
					OR skillChildDisposition.is_do_not_call = 1
					OR skillChildDisposition.is_bad_phone_number = 1
				) 
			',
			'order' => 't.date_created DESC'
		));
		
		// $models2 = LeadCallHistory::model()->findAll(array(
			// 'condition' => 'id="259393"',
		// ));
		
		echo 'child count: ' . count($models2);
		
		echo '<br><br>';
		
		// exit;
		
		if( $models2 )
		{
			foreach( $models2 as $model2 )
			{
				// $model->status = 1;
				$model2->lead->status = 3;
				
				if( !empty($model2->skillChildDisposition->recycle_interval) )
				{
					$time = strtotime(date("Y-m-d", strtotime($model2->date_created)));
					$finalDate = date("Y-m-d", strtotime("+".($model2->skillChildDisposition->recycle_interval * 30)." day", $time));
					$model2->lead->recycle_date = $finalDate;
					$model2->lead->recycle_lead_call_history_id = $model2->id;
					$model2->lead->recycle_lead_call_history_disposition_id = $model2->disposition_id;
				}
				
				if( $model2->lead->save(false) )
				{
					$dbUpdates++;
					
					echo '#' . $dbUpdates.' | Lead ID: '.$model2->lead_id.' | '.$model2->lead->first_name.' '.$model2->lead->last_name.' => '.$model2->disposition . ' | '. date('m/d/Y g:i A', strtotime($model2->date_created));
					echo '<br>';
					echo '<br>';
				}
				else
				{
					echo '<pre>';
						print_r($model2->getErrors());
					exit;
				}
			}
		}
		
		echo '<br><br>';
		
		echo 'dbUpdates: ' . $dbUpdates;
	}

	
	public function actionCheckEnrollmentInfo()
	{
		$model = CustomerEnrollment::model()->findByPk(552);
		
		$dateTime = new DateTime($model->date_created, new DateTimeZone('America/Chicago'));
		$dateTime->setTimezone(new DateTimeZone('America/Denver'));	
		
		echo 'Enrollment Time: ' . $dateTime->format('m/d/Y g:i A');
	}

	
	public function actionCallableLeadPatch()
	{
		$dbUpdates = 0;
		
		$models = Lead::model()->findAll(array(
			'with' => 'list',
			// 'condition' => '
				// list.status = 1 
				// AND t.list_id IS NOT NULL
				// AND t.type = 1  
				// AND t.status = 3 
				// AND t.number_of_dials = 0
				// AND t.customer_id IN (797, 539, 925, 1065)
			// ',
			'condition' => '
				list.status = 1 
				AND t.list_id IS NOT NULL
				AND t.type = 1  
				AND t.status = 3 
				AND t.number_of_dials = 0
			',
		));
		
		echo 'count: ' . count($models);
		
		echo '<br><br>';
		
		exit;
		
		if( $models )
		{
			foreach( $models as $model)
			{
				$model->status = 1;
				
				if( $model->save(false) )
				{
					echo $dbUpdates++;
					echo '<br>';
				}
			}
		}
		
		echo '<br><br>dbUpdates: ' . $dbUpdates;
	}

	
	public function actionEmailContentPatch()
	{
		//email monitor id: 202244
		//call history id: 460577
		
		$model = LeadCallHistory::model()->findByPk(1518507);
		
		if( $model )
		{
			echo $model->getReplacementCodeValues();
		}
	}

	
	public function actionVerifyList()
	{
		// unregister Yii's autoloader
		spl_autoload_unregister(array('YiiBase', 'autoload'));
	
		$phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
		include($phpExcelPath . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'PHPExcel.php');

		spl_autoload_register(array('YiiBase', 'autoload'));
		
		
		$listsCronProcessQueues = ListsCronProcess::model()->findAll(array(
			'condition' => 'list_id=3026',
			// 'limit' => 20,
		));
		
		if( $listsCronProcessQueues )
		{
			$transaction = Yii::app()->db->beginTransaction();
			
			try
			{
				foreach( $listsCronProcessQueues as $listsCronProcessQueue )
				{
					$model = $listsCronProcessQueue->list;
					$customer_id = $listsCronProcessQueue->list->customer_id;
					
					$customerSkill = CustomerSkill::model()->find(array(
						'condition' => 'customer_id = :customer_id AND status=1',
						'params' => array(
							':customer_id' => $customer_id,
						),
					));
					
					$contract = $customerSkill->contract;
					
					$leadsWaiting = Lead::model()->findAll(array(
						'together' => true,
						'condition' => 't.customer_id = :customer_id AND t.list_id IS NULL AND t.type=1 AND t.status !=4',
						'params' => array(
							':customer_id' => $customer_id,
						),
					));
					
					echo 'customer_id: ' . $customer_id;
					
					echo '<br>';
					
					echo 'List ID: ' . $model->id;
					
					echo '<br>';
					
					echo 'List Name: ' . $model->name;
					
					echo '<br><br>';
					
					##### START OF IMPORTING PROCESS #######
					
					$leadsImported = 0;
					$duplicateLeadsCtr = 0;
					$badLeadsCtr = 0;
					$existingLeadUpdatedCtr = 0;
					
					$importLimit = 0;
					$contractedLeads = 0;
					
					//if contract fullfillment type is GOAL apply the 10x rule and limit customers monthly import
					if(isset($contract))
					{
						if($contract->fulfillment_type != null )
						{
							if( $contract->fulfillment_type == Contract::TYPE_FULFILLMENT_GOAL_VOLUME )
							{
								if( !empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME]) )
								{
									foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME] as $key => $subsidyLevel )
									{
										$customerSkillLevelArray = $customerSkill->getCustomerSkillLevelArray();
										$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;

										if( $customerSkillLevelArrayGroup != null )
										{							
											if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
											{
												$contractedLeads += ( $subsidyLevel['goal'] * $customerSkillLevelArrayGroup->quantity );
											}
										}
									}
								}
							}						
						}	
						
						$importLimit = $contractedLeads * 10;

						if( $importLimit > 0 )
						{
							$existingCustomerLeadImportLog = CustomerLeadImportLog::model()->find(array(
								'condition' => 'customer_id = :customer_id AND month = :month AND year = :year',
								'params' => array(
									':customer_id' => $customer_id,
									':month' => date('F'),
									':year' => date('Y'),
								),
							));
							
							if( $existingCustomerLeadImportLog )
							{
								$customerLeadImportLog = $existingCustomerLeadImportLog;
							}
							else
							{
								$customerLeadImportLog = new CustomerLeadImportLog;
								
								$customerLeadImportLog->setAttributes(array(
									'customer_id' => $customer_id,
									'contract_id' => $contract->id,
									'skill_id' => $model->skill_id,
									'month' => date('F'),
									'year' => date('Y'),
								));
								
								// $customerLeadImportLog->save(false);
							}
						}
					}
						
					//import from leads waiting
					if(!empty($listsCronProcessQueue->import_from_leads_waiting))
					{ 
						if( $leadsWaiting )
						{
							foreach( $leadsWaiting as $leadsWaitingModel )
							{
								$customerLeadImportLog = CustomerLeadImportLog::model()->find(array(
									'condition' => 'customer_id = :customer_id AND month = :month AND year = :year',
									'params' => array(
										':customer_id' => $customer_id,
										':month' => date('F'),
										':year' => date('Y'),
									),
								));
								
								$leadsWaitingModel->list_id = $model->id;
								
								if( $customerLeadImportLog && $customerLeadImportLog->leads_imported < $importLimit )
								{
									// if( $leadsWaitingModel->save(false) )
									// {
										$leadsImported++;
											
										if( $customerLeadImportLog )
										{
											$customerLeadImportLog->leads_imported = $customerLeadImportLog->leads_imported + 1;
											// $customerLeadImportLog->save(false);
										}
									// }
								}
							}
						}
						
						if( $leadsImported > 0 )
						{
							$result['status'] = 'success';
							$result['message'] = '<b>'.$model->name.'</b> was updated successfully.';
							
							$history = CustomerHistory::model()->find(array(
								'condition' => 'model_id = :model_id AND page_name ="Leads"',
								'params' => array(
									':model_id' => $model->id
								),
							));
							
							echo '<br><br>';
							
							echo 'old content: ' . $history->content;
							
							echo '<br><br>';
							
							$history->content = $model->name.' | '.$leadsImported.' Leads Imported from Names waiting';
							echo 'new content: ' . $history->content;
							
							echo '<br><br>';
							
							// $history->setAttributes(array(
								// 'model_id' => $model->id, 
								// 'customer_id' => $model->customer_id,
								// 'user_account_id' => Yii::app()->user->account->id,
								// 'page_name' => 'Leads',
								// 'content' => $model->name.' | '.$leadsImported.' Leads Imported from Names waiting',
								// 'type' => $history::TYPE_UPDATED,
							// ));
							
							// echo '<pre>';
								// print_r($history->attributes);
							// echo '</pre>';
							
							// $history->save(false);
						}
						else
						{
							$result['status'] = 'error';
							$result['message'] = 'No leads imported.';
						}
						
						// echo '<pre>';
						// print_r($result);
						// echo '</pre>'; 	
						
						echo '<br><br>';
						echo '<hr>';
						echo '<br><br>';
					}
				
				
					$fileExists = file_exists('leads/' . $listsCronProcessQueue->fileupload->generated_filename);
					$inputFileName = 'leads/' . $listsCronProcessQueue->fileupload->generated_filename;
					
					if( !$fileExists )
					{
						$fileExists =  file_exists('fileupload/' . $listsCronProcessQueue->fileupload->generated_filename);
						$inputFileName = 'fileupload/' . $listsCronProcessQueue->fileupload->generated_filename;
					}
					
					echo 'fileExists: ' . $fileExists;
					echo '<br><br>';
				
					//import from fileupload-
					if( !empty($listsCronProcessQueue->fileupload_id) && $fileExists )
					{
						$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
						
						$worksheet = $objPHPExcel->getActiveSheet();

						// $highestRow         = $worksheet->getHighestRow(); // e.g. 10
						$highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
						$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
						$nrColumns = ord($highestColumn) - 64;
						
						$maxCell = $worksheet->getHighestRowAndColumn();
						$excelData = $worksheet->rangeToArray('A1:' . $maxCell['column'] . $maxCell['row']);
						$excelData = array_map('array_filter', $excelData);
						$excelData = array_filter($excelData);
						
						$highestRow = count($excelData);
						
						echo 'highestRow: ' . ($highestRow-1);
						echo '<br><br>';
						echo 'leadCount: ' . $model->leadCount;
						echo '<br><br>';
						
						// if( $model->status == Lists::STATUS_DELETED || ($highestRow-1) > $model->leadCount )
						// {
							// echo '<br><br>';
							// echo '<hr>';
							// echo '<br><br>';
							
							// continue;
						// }
						
						$validTemplate = true;
						$useDefaultTemplate = true;
						
						$col1 = $worksheet->getCell('A1')->getValue();
						$col2 = $worksheet->getCell('B1')->getValue();
						$col3 = $worksheet->getCell('C1')->getValue();
						$col4 = $worksheet->getCell('D1')->getValue();
						$col5 = $worksheet->getCell('E1')->getValue();
						$col6 = $worksheet->getCell('F1')->getValue();
						$col7 = $worksheet->getCell('G1')->getValue();
						$col8 = $worksheet->getCell('H1')->getValue();
						$col9 = $worksheet->getCell('I1')->getValue();
						$col10 = $worksheet->getCell('J1')->getValue();
						$col11 = $worksheet->getCell('K1')->getValue();
						$col12 = $worksheet->getCell('L1')->getValue();
						$col13 = $worksheet->getCell('M1')->getValue();
						
						if( 
							strtoupper($col1) != 'LAST NAME' 
							|| strtoupper($col2) != 'FIRST NAME' 
							|| strtoupper($col3) != 'PARTNER FIRST NAME' 
							|| strtoupper($col4) != 'PARTNER LAST NAME' 
							|| strtoupper($col5) != 'ADDRESS 1' 
							|| strtoupper($col6) != 'ADDRESS 2' 
							|| strtoupper($col7) != 'CITY' 
							|| strtoupper($col8) != 'STATE' 
							|| strtoupper($col9) != 'ZIP' 
							|| strtoupper($col10) != 'OFFICE PHONE'  
							|| strtoupper($col11) != 'MOBILE PHONE'  
							|| strtoupper($col12) != 'HOME PHONE'						
							|| strtoupper($col13) != 'EMAIL ADDRESS'						
						)
						{
							$validTemplate = false;
						}
						
						$validColumns = array('first name', 'last name', 'phone 1', 'phone 2', 'phone 3');
						$columnsInFile = array();
							
						if( !$validTemplate )
						{
							foreach( range('A', $highestColumn) as $columnInFile )
							{
								if( !empty($columnInFile) )
								{
									$columnsInFile[$columnInFile] = strtolower($worksheet->getCell($columnInFile.'1')->getValue());
								}
							}
							
							if( $columnsInFile )
							{
								$originalColumnsInFile = $columnsInFile;
								$arrayMatch = array_intersect($validColumns, $columnsInFile);
								
								sort($validColumns);
								sort($arrayMatch);

								if( $validColumns == $arrayMatch )
								{
									$validTemplate = true;
									$useDefaultTemplate = false;
									
									$columnsInFile = $originalColumnsInFile;
								}
							}
						}
						
						echo '<br><br>';
						echo 'validTemplate: ' . $validTemplate;
						echo '<br><br>';
						
						if( $validTemplate )
						{
							
							for ($row = 2; $row <= $highestRow; ++$row) 
							{	
								$customerLeadImportLog = CustomerLeadImportLog::model()->find(array(
									'condition' => 'customer_id = :customer_id AND month = :month AND year = :year',
									'params' => array(
										':customer_id' => $customer_id,
										':month' => date('F'),
										':year' => date('Y'),
									),
								));
								
								if( $useDefaultTemplate )
								{
									$last_name = $worksheet->getCell('A'.$row)->getValue();
									$first_name = $worksheet->getCell('B'.$row)->getValue();
									$partner_first_name = $worksheet->getCell('C'.$row)->getValue();
									$partner_last_name = $worksheet->getCell('D'.$row)->getValue();
									$address1 = $worksheet->getCell('E'.$row)->getValue();
									$address2 = $worksheet->getCell('F'.$row)->getValue();
									$city = $worksheet->getCell('G'.$row)->getValue();
									$state = $worksheet->getCell('H'.$row)->getValue();
									$zip = $worksheet->getCell('I'.$row)->getValue();
									$office_phone_number = preg_replace("/[^0-9]/","", $worksheet->getCell('J'.$row)->getValue());
									$mobile_phone_number = preg_replace("/[^0-9]/","", $worksheet->getCell('K'.$row)->getValue());
									$home_phone_number = preg_replace("/[^0-9]/","", $worksheet->getCell('L'.$row)->getValue());
									$email_address = $worksheet->getCell('M'.$row)->getValue();
								}
								else
								{
									$last_name = '';
									$first_name = '';
									$partner_first_name = '';
									$partner_last_name = '';
									$address1 = '';
									$address2 ='';
									$city = '';
									$state = '';
									$zip = '';
									$office_phone_number = '';
									$mobile_phone_number = '';
									$home_phone_number = '';
									$email_address = '';
									
									if( $columnsInFile )
									{
										foreach( $columnsInFile as $columnInFile => $rowValue )
										{
											if( $rowValue == 'first name' )
											{
												$first_name = $worksheet->getCell($columnInFile.$row)->getValue();
											}
											
											if( $rowValue == 'last name' )
											{
												$last_name = $worksheet->getCell($columnInFile.$row)->getValue();
											}
											
											if( $rowValue == 'phone 1' )
											{
												$home_phone_number = preg_replace("/[^0-9]/","", $worksheet->getCell($columnInFile.$row)->getValue());
											}
											
											if( $rowValue == 'phone 2' )
											{
												$mobile_phone_number = preg_replace("/[^0-9]/","", $worksheet->getCell($columnInFile.$row)->getValue());
											}
											
											if( $rowValue == 'phone 3' )
											{
												$office_phone_number = preg_replace("/[^0-9]/","", $worksheet->getCell($columnInFile.$row)->getValue());
											}
											
											if( $rowValue == 'partner first name' )
											{
												$partner_first_name = $worksheet->getCell($columnInFile.$row)->getValue();
											}
											
											if( $rowValue == 'partner last name' )
											{
												$partner_last_name = $worksheet->getCell($columnInFile.$row)->getValue();
											}
											
											if( $rowValue == 'email address' )
											{
												$email_address = $worksheet->getCell($columnInFile.$row)->getValue();
											}
											
											if( $rowValue == 'address 1' )
											{
												$address1 = $worksheet->getCell($columnInFile.$row)->getValue();
											}
											
											if( $rowValue == 'city' )
											{
												$city = $worksheet->getCell($columnInFile.$row)->getValue();
											}
											
											if( $rowValue == 'state' )
											{
												$state = $worksheet->getCell($columnInFile.$row)->getValue();
											}
											
											if( $rowValue == 'zip' )
											{
												$zip = $worksheet->getCell($columnInFile.$row)->getValue();
											}
										}
									}
								}

								$type = 1;
								
								if( strlen($office_phone_number) < 10 && strlen($mobile_phone_number) < 10 && strlen($home_phone_number) < 10 )
								{
									$type = 2;
								}
								
								if( $type == 1 )
								{
									$existingLead = Lead::model()->find(array(
										'condition' => 't.customer_id = :customer_id AND t.status !=4 AND (
											list_id != :list_id
											AND
											(
												(office_phone_number = :office_phone_number AND office_phone_number != "" AND office_phone_number IS NOT NULL) OR 
												(office_phone_number = :mobile_phone_number AND office_phone_number != "" AND office_phone_number IS NOT NULL) OR 
												(office_phone_number = :home_phone_number AND office_phone_number != "" AND office_phone_number IS NOT NULL) OR 
												(mobile_phone_number = :office_phone_number AND mobile_phone_number != "" AND mobile_phone_number IS NOT NULL) OR
												(mobile_phone_number = :mobile_phone_number AND mobile_phone_number != "" AND mobile_phone_number IS NOT NULL) OR
												(mobile_phone_number = :home_phone_number AND mobile_phone_number != "" AND mobile_phone_number IS NOT NULL) OR
												(home_phone_number = :office_phone_number AND home_phone_number != "" AND home_phone_number IS NOT NULL) OR
												(home_phone_number = :mobile_phone_number AND home_phone_number != "" AND home_phone_number IS NOT NULL) OR
												(home_phone_number = :home_phone_number AND home_phone_number != "" AND home_phone_number IS NOT NULL)
											)										
										)',
										'params' => array(
											':list_id' => $model->id,
											':customer_id' => $customer_id,
											':office_phone_number' => $office_phone_number,
											':mobile_phone_number' => $mobile_phone_number,
											':home_phone_number' => $home_phone_number,
										),
									));
								}
								else
								{
									$existingLead = array();
								}
								
								
								if( !empty($existingLead) )
								{
									echo 'duplicate on list ID: ' . $existingLead->list_id . ' => ' . $existingLead->list->name;
									echo '<br>';
									
									//recycle - recertify  module
									$existingLead->recertify_date = $this->_computeForSkillMaxLeadLifeBeforeRecertify($model);
									
									if( $model->duplicate_action !== null )
									{
										if( $model->duplicate_action == $model::DUPLICATES_UPDATE_LEAD_INFO || $model->duplicate_action == $model::DUPLICATES_UPDATE_LEAD_INFO_RESET_DIALS )
										{
											$existingLead->setAttributes(array(
												'list_id' => $model->id,
												'last_name' => $last_name,
												'first_name' => $first_name,
												'partner_first_name' => $partner_first_name,
												'partner_last_name' => $partner_last_name,
												'address' => $address1,
												'address2' => $address2,
												'city' => $city,
												'state' => $state,
												'zip_code' => $zip,
												'office_phone_number' => $office_phone_number,
												'mobile_phone_number' => $mobile_phone_number,
												'home_phone_number' => $home_phone_number,
												'email_address' => $email_address,
												'type' => $type,
												'language' => $model->language,
												'timezone' => AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $model->calendar->office->phone) ),
											));
											
											if( $model->duplicate_action == $model::DUPLICATES_UPDATE_LEAD_INFO_RESET_DIALS )
											{
												$existingLead->number_of_dials = 0;
												$existingLead->status = 1;
											}
											
											// if( $existingLead->save(false) )
											// {
												if( $type == 1 )
												{
													$existingLeadUpdatedCtr++;
												}
												else
												{
													$badLeadsCtr++;
												}
											// }
										}
										elseif( $model->duplicate_action == $model::MOVE_LEAD_TO_CURRENT_LIST_RESET_DIALS )
										{
											$existingLead->list_id = $model->id;
											$existingLead->number_of_dials = 0;
											$existingLead->status = 1;
											
											// if( $existingLead->save(false) )
											// {
												if( $type == 1 )
												{
													$existingLeadUpdatedCtr++;
												}
												else
												{
													$badLeadsCtr++;
												}
											// }
										}
										else
										{
											echo $duplicateLeadsCtr++;
											echo '<br>';
										}
									}
									else
									{
										echo $duplicateLeadsCtr++;
										echo '<br>';
									}
								}
								else
								{
									$lead = new Lead;
									$lead->list_id = $model->id;
									
									if( $customerLeadImportLog && $customerLeadImportLog->leads_imported > $importLimit )
									{
										$lead->list_id = null;
									}

									$lead->setAttributes(array(
										'customer_id' => $model->customer_id,
										'last_name' => $last_name,
										'first_name' => $first_name,
										'partner_first_name' => $partner_first_name,
										'partner_last_name' => $partner_last_name,
										'address' => $address1,
										'address2' => $address2,
										'city' => $city,
										'state' => $state,
										'zip_code' => $zip,
										'office_phone_number' => $office_phone_number,
										'mobile_phone_number' => $mobile_phone_number,
										'home_phone_number' => $home_phone_number,
										'email_address' => $email_address,
										'type' => $type,
										'language' => $model->language,
										'timezone' => AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $model->calendar->office->phone) ),
									));
									
									//recycle - recertify  module
									$lead->recertify_date = $this->_computeForSkillMaxLeadLifeBeforeRecertify($model);	
									
									// if( $lead->save(false) )
									// {
										if( $type == 1 )
										{
											$leadHistory = new LeadHistory;
									
											$leadHistory->setAttributes(array(
												'lead_id' => $lead->id,
												'agent_account_id' => Yii::app()->user->account->id,
												'type' => 4,
											));	
											
											// $leadHistory->save(false);
											
											
											$leadsImported++;
											
											if( $customerLeadImportLog )
											{
												$customerLeadImportLog->leads_imported = $customerLeadImportLog->leads_imported + 1;
												// $customerLeadImportLog->save(false);
											}
										}
										else
										{
											$badLeadsCtr++;
										}
										
										if( $highestColumn != 'J' )
										{
											foreach ( range('K', $highestColumn) as $columnLetter ) 
											{
												$customFieldName = $worksheet->getCell($columnLetter.'1')->getValue();
												$customFieldValue = $worksheet->getCell($columnLetter.$row)->getValue();
												
												$customData = new LeadCustomData;
												
												$customData->setAttributes(array(
													'list_id' => $model->id,
													'lead_id' => $lead->id,
													'name' => $customFieldName,
													'value' => $customFieldValue,
												));
												
												// $customData->save(false);
											}
										}
									// }
								}	
							}
						
							$history = CustomerHistory::model()->find(array(
								'condition' => 'model_id = :model_id AND page_name ="Leads"',
								'params' => array(
									':model_id' => $model->id
								),
							));
							
							echo '<br><br>';
							
							echo 'old content: ' . $history->content;
							
							echo '<br><br>';
							
							$history->content = $model->name.' | '.($highestRow-1).' leads in list | ' . $leadsImported . ' leads imported | '.$existingLeadUpdatedCtr.' existing leads updated | '.$duplicateLeadsCtr.' duplicates | '.$badLeadsCtr.' bad leads';
							echo 'new content: ' . $history->content;
							
							echo '<br><br>';
							
							$history->setAttributes(array(
								// 'model_id' => $model->id, 
								// 'customer_id' => $model->customer_id,
								// 'user_account_id' => Yii::app()->user->account->id,
								// 'page_name' => 'Leads',
								// 'content' => $model->name.' | '.($highestRow-1).' leads in list | ' . $leadsImported . ' leads imported | '.$existingLeadUpdatedCtr.' existing leads updated | '.$duplicateLeadsCtr.' duplicates | '.$badLeadsCtr.' bad leads',
								// 'type' => $history::TYPE_ADDED,
							));

							// echo '<pre>';
								// print_r($history->attributes);
							// echo '</pre>';
							
							// $history->save(false);
							
							
							$result['status'] = 'success';
							$result['message'] = 'List "'.$model->name.'" for customer "'.$model->customer->getFullName().'" import completed successfully. '.$leadsImported . ' leads imported';
							// $result['message'] = 'Database has been updated.';
							
						}
						else
						{
							$result['status'] = 'error';
							$result['message'] = 'Invalid Template of List "'.$model->name.'" for customer "'.$model->customer->getFullName().'"';
						}
					}

					// $listsCronProcessQueue->result_data = json_encode($result);
					// $listsCronProcessQueue->on_going = 0;
					// $listsCronProcessQueue->date_completed = date("Y-m-d H:i:s");
					// $listsCronProcessQueue->save(false);
					
					// echo '<pre>';
					// print_r($result);
					// echo '</pre>'; 	
					
					echo '<br><br>';
					echo '<hr>';
					echo '<br><br>';
				}
				
				// $transaction->commit();
			}
			catch(Exception $e)
			{
				print_r($e);
				$transaction->rollback();
			}
		}
		else
		{
			echo 'record not found.';
		}
	}
	
	public function _computeForSkillMaxLeadLifeBeforeRecertify($model)
	{
		//recycle - recertify  module
		if(!empty($model->skill->max_lead_life_before_recertify))
		{
			$time = strtotime(date("Y-m-d"));
			$finalDate = date("Y-m-d", strtotime("+".($model->skill->max_lead_life_before_recertify)." day", $time));
			return $finalDate;
		}
		else
			return date("Y-m-d");
	}

	
	public function actionGetDialedNumber()
	{
		$str = '3075755058';
		echo $str;
		echo '<br><br>';
		echo substr($str, -4);
	}
	
	public function actionGetString()
	{
		$str = 'End Date Changed from 06/30/2016 to';
		
		$str1 = explode('from', $str);
		$str2 = explode('to', $str);
		
		echo '<pre>';
		
		print_r($str1);
		print_r($str2);
	}

	
	public function actionNoShowCount()
	{
		$customerQueues = CustomerQueueViewer::model()->findAll();
		
		$ctr = 1;
		$totalNoShowCount = 0;
		$totalCount = 0;
		
		foreach( $customerQueues as $customerQueue )
		{
			$noShowMTDSql = "
				SELECT COUNT(*) AS totalCount
				FROM (
				   SELECT COUNT(ca.id)
				   FROM ud_calendar_appointment ca
				   LEFT JOIN ud_calendar c ON c.id = ca.calendar_id
				   WHERE ca.title IN ('NO SHOW RESCHEDULE')
				   AND ca.lead_id IS NOT NULL 
				   AND ca.date_created >= '".date('Y-m-01 00:00:00')."' AND ca.date_created <= '".date('Y-m-t 23:59:59')."'
				   AND c.customer_id = '".$customerQueue->customer_id."'
				   GROUP BY ca.lead_id
				) AS totalCount
			";
			
			$command = Yii::app()->db->createCommand($noShowMTDSql);
			$noShowMTD = $command->queryRow();
			
			$firstNoShow = $noShowMTD['totalCount'];
			
			if( $firstNoShow > 3 )
			{
				$firstNoShow = 3;
			}
			
			$totalNoShowCount += $noShowMTD['totalCount'];
			$totalCount += $firstNoShow;
			
			// $customerQueue->no_show = $firstNoShow;
			// $customerQueue->save(false);
			
			echo $ctr.'. customer name: ' . $customerQueue->customer_name .' => '.$noShowMTD['totalCount'];
			echo '<br><br>';
			
			$ctr++;
		}
		
		echo '<br><br>';
		
		echo 'total customer records: ' . $ctr;
		
		echo '<br><br>';
		
		echo date('Y-m-01 00:00:00').' - ',date('Y-m-t 23:59:59');
		
		echo '<br><br>';
		
		echo 'total NoShow Count: ' . $totalNoShowCount;
		
		echo '<br><br>';
		
		echo 'total First 3 NoShow Count: ' . $totalCount;
	}

	
	public function actionMonthDropDown()
	{
		$billingPeriodOptions = array();
					
		$current    = time();
		$add_time   = strtotime('+1 month', $current);
		$diff       = $add_time-$current;
		
		$startDate = strtotime('-6 months');
		$endDate = strtotime('+6 months');
		
		echo date('m/d/Y', $startDate).' - '.date('m/d/Y', $endDate);
		
		echo '<br><br>';
		
		while( $startDate < $endDate )
		{
			$billingPeriodOptions[date('Y-m-01', $startDate)] = date('M Y', $startDate);
			
			$startDate += $diff;
		}
		
		echo '<pre>';
			print_r($billingPeriodOptions);
	}

	
	public function actionCheckList()
	{
		$lists = Lists::model()->findAll(array(
			// 'condition' => 'status !=4',
			'condition' => 'id = 3296',
		));
		
		if( $lists )
		{
			foreach( $lists as $list )
			{
				if( isset($list->customer) && isset($list->calendar) && isset($list->calendar->office) )
				{
					$leadsWithDifferentAreaCode = array();
		
					$customer = $list->customer;
					$customerAreaCode = substr($list->calendar->office->phone, 1, 3);
					
					$leads = Lead::model()->findAll(array(
						'condition' => 'list_id = :list_id AND type=1 AND status!=4',
						'params' => array(
							':list_id' => $list->id,
						),
					));
					
					$tenPercentOfTotalLeads = (10/100) * count($leads);
					
					if( $leads )
					{
						echo 'List Name: ' . $list->name;
						echo '<br>';
						echo 'Customer Name: ' . $customer->firstname.' '.$customer->lastname;
						echo '<br>';
						echo 'Office Area Code: ' . $customerAreaCode;
						
						echo '<br>';
						
						foreach( $leads as $lead )
						{
							if( !empty($lead->home_phone_number) && substr($lead->home_phone_number,0,3) != $customerAreaCode )
							{
								if ( !array_key_exists($lead->id, $leadsWithDifferentAreaCode) )
								{
									$leadsWithDifferentAreaCode[$lead->id] = array(
										'lead_name' => $lead->first_name.' '.$lead->last_name,
										'phone_number' => $lead->home_phone_number
									);
								}
							}
							
							if( !empty($lead->mobile_phone_number) && substr($lead->mobile_phone_number,0,3) != $customerAreaCode )
							{
								if ( !array_key_exists($lead->id, $leadsWithDifferentAreaCode) )
								{
									$leadsWithDifferentAreaCode[$lead->id] = array(
										'lead_name' => $lead->first_name.' '.$lead->last_name,
										'phone_number' => $lead->mobile_phone_number
									);
								}
							}
							
							if( !empty($lead->office_phone_number) && substr($lead->office_phone_number,0,3) != $customerAreaCode )
							{
								if ( !array_key_exists($lead->id, $leadsWithDifferentAreaCode) )
								{
									$leadsWithDifferentAreaCode[$lead->id] = array(
										'lead_name' => $lead->first_name.' '.$lead->last_name,
										'phone_number' => $lead->office_phone_number
									);
								}
							}
						}
						
						echo '<br>';
						echo 'Total Leads in List: ' . count($leads);
						echo '<br>';
						echo '10% of Total Leads: ' . $tenPercentOfTotalLeads;
						echo '<br>';
						echo 'Leads with Different Area Code: ' . count($leadsWithDifferentAreaCode);
						
						echo '<br><br>';
						
						if( $leadsWithDifferentAreaCode )
						{
							$ctr = 1;
							
							foreach( $leadsWithDifferentAreaCode as $leadWithDifferentAreaCode )
							{
								echo $ctr.'. '.$leadWithDifferentAreaCode['lead_name'].' - '.$leadWithDifferentAreaCode['phone_number'];
								echo '<br>';
								
								$ctr++;
							}
						}
						
						if( count($leadsWithDifferentAreaCode) >= $tenPercentOfTotalLeads )
						{
							
						}
					}
				}
			}
		}
	}

	
	public function actionCheckRecertifyList()
	{
		$customer_id = 175;

		$models = Lead::model()->findAll(array(
			'with' => 'list',
			'together' => true,
			'condition' => 't.list_id IN (60, 1904) AND list.customer_id = :customer_id AND t.type=1 AND t.status = 1 AND list.status = 1  AND (recertify_date = "0000-00-00" || recertify_date IS NULL || NOW() >= recertify_date)',
			'params' => array(
				':customer_id' => $customer_id,
			),
		));
		
		echo 'customer office phone: (972) 874-8801'; 
		
		echo '<br><br>';
		
		echo 'total names in recertify: ' . count($models);
		
		echo '<br><br>';
		
		$leadsWithDifferentAreaCode = array();
		
		foreach( $models as $lead )
		{
			$customer = $lead->list->customer;
			$customerAreaCode = substr($lead->list->calendar->office->phone, 1, 3);
			
			if( !empty($lead->home_phone_number) && substr($lead->home_phone_number,0,3) != $customerAreaCode )
			{
				if ( !array_key_exists($lead->id, $leadsWithDifferentAreaCode) )
				{
					$leadsWithDifferentAreaCode[$lead->id] = array(
						'lead_name' => $lead->first_name.' '.$lead->last_name,
						'phone_number' => $lead->home_phone_number
					);
				}
			}
			
			if( !empty($lead->mobile_phone_number) && substr($lead->mobile_phone_number,0,3) != $customerAreaCode )
			{
				if ( !array_key_exists($lead->id, $leadsWithDifferentAreaCode) )
				{
					$leadsWithDifferentAreaCode[$lead->id] = array(
						'lead_name' => $lead->first_name.' '.$lead->last_name,
						'phone_number' => $lead->mobile_phone_number
					);
				}
			}
			
			if( !empty($lead->office_phone_number) && substr($lead->office_phone_number,0,3) != $customerAreaCode )
			{
				if ( !array_key_exists($lead->id, $leadsWithDifferentAreaCode) )
				{
					$leadsWithDifferentAreaCode[$lead->id] = array(
						'lead_name' => $lead->first_name.' '.$lead->last_name,
						'phone_number' => $lead->office_phone_number
					);
				}
			}
		}
		
		echo 'leads with different area code: '.count($leadsWithDifferentAreaCode);
		
		echo '<br><br>';
		
		$leadCtr = 1;
		
		foreach( $leadsWithDifferentAreaCode as $leadWithDifferentAreaCode )
		{
			echo $leadCtr.'. '.$leadWithDifferentAreaCode['lead_name'].' - '.$leadWithDifferentAreaCode['phone_number'];
			echo '<br>';
			
			$leadCtr++;
		}
	}

	
	public function actionCustomersOnHold()
	{
		$models = CustomerHistory::model()->findAll(array(
			'with' => 'customer',
			'condition' => '
				customer.company_id = 13
				AND
				( 
					t.content LIKE "%Status Changed from Active to Hold%" 
					OR 
					t.content LIKE "%Decline Hold Aug%" 
				)
				AND 
				(
					t.date_created >= "2016-08-01 00:00:00"
					AND t.date_created <= "2016-08-31 23:59:59"
				)
			',
			'group' => 'customer_id'
		));
		
		echo '<table border=1>';
		
		echo '<tr>';
			// echo '<th>#</th>';
			echo '<th>Customer Name</th>';
			echo '<th>Agent ID</th>';
			// echo '<th>Hold Dates</th>';
		echo '</tr>';
		
		if( $models )
		{
			$ctr = 1;
			
			foreach( $models as $model  )
			{
				echo '<tr>';
					// echo '<td>'.$ctr.'</td>';
					echo '<td>'.$model->customer->firstname.' '.$model->customer->lastname.'</td>';
					echo '<td>'.$model->customer->custom_customer_id.'</td>';
					// echo '<td>'.date('m/d/Y', strtotime($model->date_created)).'</td>';
				echo '</tr>';
				
				$ctr++;
			}
		}
		
		echo '</table>';
	}

	
	public function actionImpactReport()
	{
		if( !isset($_GET['debug']) )
		{
			exit;
		}
		
		echo 'Process started at ' . date('g:i A'); 
		echo '<br><br>';
		
		$billingPeriods = array();
		
		$startDate = strtotime('-1 month');
		$endDate = strtotime('+7 month'); 
		
		while( $startDate < $endDate )
		{
			$billingPeriods[] = date('M Y', $startDate);
			
			$addTime   = strtotime('+1 month', $startDate);
			$diff       = $addTime-$startDate;
			
			$startDate += $diff;
		}
		
		
		if( $billingPeriods )
		{
			// ImpactReport::model()->deleteAll();
			
			foreach( $billingPeriods as $billingPeriod )
			{
				$impactReport = ImpactReport::model()->find(array(
					'condition' => 'month_name = :month_name',
					'params' => array(
						':month_name' => date('M-y', strtotime($billingPeriod)),
					),
				));
								
				if( !$impactReport )
				{
					$impactReport = new ImpactReport;
					$impactReport->month_name = date('M-y', strtotime($billingPeriod));
					$impactReport->month_date = date('Y-m-01', strtotime($billingPeriod));
					$impactReport->actual = 0;
					$impactReport->actual_credit_card = 0;
					$impactReport->actual_subsidy = 0;
					$impactReport->projected = 0;
					$impactReport->projected_credit_card = 0;
					$impactReport->projected_subsidy = 0;	
					$impactReport->save(false);
				}
				
				$impactReportSummary = ImpactReportSummary::model()->find(array(
					'condition' => 'month_name = :month_name',
					'params' => array(
						':month_name' => date('M-y', strtotime($billingPeriod)),
					),
				));
				
				if( !$impactReportSummary )
				{
					$impactReportSummary = new ImpactReportSummary;
					$impactReportSummary->month_name = date('M-y', strtotime($billingPeriod));
					
					$impactReportSummary->save(false);
				}
			}
		}
		
		// echo '<pre>';
			// print_r($billingPeriods);
		// exit;
		
		echo '<table border="1">';
			echo '<tr>';
				echo '<td>Start</td>';
				echo '<td>End</td>';
				echo '<td>Billing Period</td>';
				echo '<td>Customer Name</td>';
				echo '<td>Original Amount</td>';
				echo '<td>Billing Credit</td>';
				echo '<td>Subsidy</td>';
				echo '<td>Reduced Amount</td>';
			echo '</tr>';
		
		foreach( $billingPeriods as $billingPeriod )
		{
			// echo 'Billing Period: ' . $billingPeriod;
			// echo '<br>';
			
			$billingPeriodMonth = date('m', strtotime($billingPeriod));
			$billingPeriodYear = date('Y', strtotime($billingPeriod));
						
			$customerQueues = CustomerQueueViewer::model()->findAll(array(
				'with' => 'customer',
				'order' => 'customer.lastname ASC',
				// 'condition' => 'customer_id=1619',
				'condition' => 't.customer_id NOT IN (48)',
				// 'limit' => 100
			));
			
			if( $customerQueues )
			{
				foreach( $customerQueues as $customerQueue )
				{
					$customerSkill = CustomerSkill::model()->find(array(
						'with' => 'customer',
						'condition' => '
							t.customer_id = :customer_id 
							AND t.skill_id = :skill_id 
							AND customer.company_id NOT IN(15, 17,18,23, 24, 25, 26, 27)
							AND customer.status=1
							AND customer.is_deleted=0
						',
						'params' => array(
							':customer_id' => $customerQueue->customer_id,
							':skill_id' => $customerQueue->skill_id,
						),

					));
					
					$customerRemoved = CustomerBillingWindowRemoved::model()->find(array(
						'condition' => '
							customer_id = :customer_id 
							AND skill_id = :skill_id 
							AND MONTH(date_created) = :month
							AND YEAR(date_created) = :year
						',
						'params' => array(
							':customer_id' => $customerQueue->customer_id,
							':skill_id' => $customerQueue->skill_id,
							':month' => date('n', strtotime($billingPeriod)),
							':year' => date('Y', strtotime($billingPeriod))
						),
					));
					
					if( $customerSkill && !empty($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00' && date('Y-m', strtotime($billingPeriod)) >= date('Y-m', strtotime($customerSkill->start_month)) && empty($customerRemoved) )
					{
						if( isset($customerSkill->contract) )
						{
							$contract = $customerSkill->contract;
							$customer = $customerSkill->customer;
							
							$customerIsCallable = false;
							
							$contractCreditSubsidys = $this->getCustomerContractCreditAndSubsidy($customer, $contract, $billingPeriod);		
							$totalAmount = $contractCreditSubsidys[$contract->id]['totalAmount'];										
							$totalCreditAmount = $contractCreditSubsidys[$contract->id]['totalCreditAmount'];		
							$subsidyAmount = $contractCreditSubsidys[$contract->id]['subsidyAmount'];		
							$totalReducedAmount = $contractCreditSubsidys[$contract->id]['totalReducedAmount'];		
							$totalLeads = $contractCreditSubsidys[$contract->id]['totalLeads'];		
							
							//credit amount should not be over the Amount, for the customer will ask it to be billed next month -aug 9, 2016
							if($totalCreditAmount > $totalAmount)
							{
								$totalCreditAmount = $totalAmount - $subsidyAmount;
							}

							$totalReducedAmount = abs($totalAmount - $subsidyAmount);
							
							if( $totalCreditAmount < 0 )
							{
								$totalReducedAmount = $totalReducedAmount + abs($totalCreditAmount);
							}
							else
							{
								$totalReducedAmount = $totalReducedAmount - abs($totalCreditAmount);
							}
							
							if( $totalReducedAmount < 0 )
							{
								$totalReducedAmount = 0;
							}
							
							$totalReducedAmount = number_format($totalReducedAmount, 2);
							
							echo '<tr>';

								echo '<td>'.date('Y-m', strtotime($customerSkill->start_month)).'</td>';
								echo '<td>'.date('Y-m', strtotime('+1 month', strtotime($customerSkill->end_month))).'</td>';
								echo '<td>'.date('Y-m', strtotime($billingPeriod)).'</td>';
								echo '<td>'.$customer->getFullName().'</td>';
								echo '<td>'.$totalAmount.'</td>';
								echo '<td>'.$totalCreditAmount.'</td>';
								echo '<td>'.$subsidyAmount.'</td>';
								echo '<td>'.$totalReducedAmount.'</td>';
								
							echo '</tr>';
							
											
							//check status and start date
							if( isset($customerSkill->contract) && isset($customerSkill->customer) && $customerSkill->customer->status == 1 && $customerSkill->customer->is_deleted == 0 && !empty($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00' && date('Y-m', strtotime($billingPeriod)) >= date('Y-m', strtotime($customerSkill->start_month)) )
							{
								$customerIsCallable = true;
							}

							//check if on hold
							if( $customerSkill->is_contract_hold == 1 )
							{
								if( !empty($customerSkill->is_contract_hold_start_date) && !empty($customerSkill->is_contract_hold_end_date) )
								{
									if( strtotime($billingPeriod) >= strtotime($customerSkill->is_contract_hold_start_date) && strtotime($billingPeriod) <= strtotime($customerSkill->is_contract_hold_end_date) )
									{
										$customerIsCallable = false;
									}
								}
							}

							//check if cancelled
							if( !empty($customerSkill->end_month) && date('Y', strtotime($customerSkill->end_month)) >= 2015 )
							{
								if( strtotime($billingPeriod) >= strtotime($customerSkill->end_month) )
								{
									$customerIsCallable = false;
								}
							}
							
							if( $customerIsCallable )
							{
								//starting
								if( date('Y-m', strtotime($customerQueue->start_date)) == date('Y-m', strtotime($billingPeriod)) )
								{
									$impactReportSummary = ImpactReportSummary::model()->find(array(
										'condition' => 'month_name = :month_name',
										'params' => array(
											':month_name' => date('M-y', strtotime($billingPeriod)),
										),
									));
									
									if( $impactReportSummary )
									{
										$impactReportSummary->sales_starting_amount = $impactReportSummary->sales_starting_amount + ( $totalReducedAmount + $subsidyAmount );
										$impactReportSummary->sales_starting_count = $impactReportSummary->sales_starting_count + 1;
										
										if( $impactReportSummary->save(false) )
										{
											$impactReportLink = new ImpactReportLink;
											
											$impactReportLink->setAttributes(array(
												'customer_id' => $customer->id,
												'customer_name' => $customer->getFullName(),
												'month_name' => date('M-y', strtotime($billingPeriod)),
												'link_name' => 'sales_starting_count'
											));
											
											$impactReportLink->save(false);
										}
									}
								}
							}

							if( $customerIsCallable )
							{
								$impactReport = ImpactReport::model()->find(array(
									'condition' => 'month_name = :month_name',
									'params' => array(
										':month_name' => date('M-y', strtotime($billingPeriod)),
									),
								));
								
								if( $contractCreditSubsidys[$contract->id]['isBilled'] )
								{
									if( in_array(date('M-y', strtotime($billingPeriod)), array('Jan-17', 'Feb-17', 'Mar-17', 'Apr-17', 'May-17', 'Jun-17', 'Jul-17', 'Aug-17', 'Sep-17', 'Oct-17', 'Nov-17', 'Dec-17', 'Jan-18', 'Feb-18', 'Mar-18', 'Apr-18', 'May-18', 'Jun-18', 'Jul-18')) )
									{
										$impactReport->projected_customer_count = $impactReport->projected_customer_count + 1;
									}
									
									$impactReport->actual += ( $totalReducedAmount + $subsidyAmount );
									$impactReport->actual_credit_card += $totalReducedAmount;
									$impactReport->actual_subsidy += $subsidyAmount;
								}
								else
								{
									$impactReport->projected_customer_count = $impactReport->projected_customer_count + 1;				
									
									$impactReport->projected += ( $totalReducedAmount + $subsidyAmount );
									$impactReport->projected_credit_card += $totalReducedAmount;
									$impactReport->projected_subsidy += $subsidyAmount;	
								}
								
								// echo '<pre>';
									// print_r($impactReport->attributes);
								// echo '</pre>';
								
								// echo '<br><br>';
								
								if( $impactReport->save(false) )
								{
									$impactReportLink = new ImpactReportLink;
										
									$impactReportLink->setAttributes(array(
										'customer_id' => $customer->id,
										'customer_name' => $customer->getFullName(),
										'month_name' => date('M-y', strtotime($billingPeriod)),
										'link_name' => 'customer_count'
									));
									
									$impactReportLink->save(false);
								}							
							}
							
						}
					}
				}
			}
		}
	
		echo '</table>';
		
		echo '<br><br>';
		echo 'Process ended at ' . date('g:i A');
	}
	
	public function actionImpactReportSummary()
	{
		echo 'Process started at ' . date('g:i A'); 
		echo '<br><br>';
		
		$billingPeriods = array();
		
		$startDate = strtotime('-1 month');
		$endDate = strtotime('+7 month'); 
		
		while( $startDate < $endDate )
		{
			$billingPeriods[] = date('M Y', $startDate);
			
			$addTime   = strtotime('+1 month', $startDate);
			$diff       = $addTime-$startDate;
			
			$startDate += $diff;
		}
		
		
		if( $billingPeriods )
		{
			// ImpactReport::model()->deleteAll();
			
			foreach( $billingPeriods as $billingPeriod )
			{
				$impactReportSummary = ImpactReportSummary::model()->find(array(
					'condition' => 'month_name = :month_name',
					'params' => array(
						':month_name' => date('M-y', strtotime($billingPeriod)),
					),
				));
				
				if( !$impactReportSummary )
				{
					$impactReportSummary = new ImpactReportSummary;
					$impactReportSummary->month_name = date('M-y', strtotime($billingPeriod));
					
					$impactReportSummary->save(false);
				}
			}
		}
		
		// echo '<pre>';
			// print_r($billingPeriods);
		// exit;
		
		
		foreach( $billingPeriods as $billingPeriod )
		{
			$billingPeriodMonth = date('m', strtotime($billingPeriod));
			$billingPeriodYear = date('Y', strtotime($billingPeriod));
			
			echo 'Billing Period: ' . $billingPeriod;
			echo '<br>';
			echo 'billingPeriodMonth: ' . $billingPeriodMonth;
			echo '<br>';
			echo 'billingPeriodYear: ' . $billingPeriodYear;
			echo '<br>';
			echo '<br>';
			
			$customerQueues = CustomerQueueViewer::model()->findAll(array(
				'with' => 'customer', 
				'condition' => 'YEAR(end_date) = :year AND MONTH(end_date) = :month',
				'params' => array(
					':year' => $billingPeriodYear,
					':month' => $billingPeriodMonth
				),
				'order' => 'customer.lastname ASC',
			));
			
			if( $customerQueues )
			{
				foreach( $customerQueues as $customerQueue )
				{
					echo 'Customer name: ' . $customerQueue->customer_name;

					$customerSkill = CustomerSkill::model()->find(array(
						'with' => 'customer',
						'condition' => '
							t.customer_id = :customer_id 
							AND t.skill_id = :skill_id 
							AND customer.company_id NOT IN(15, 17,18,23, 24, 25, 26, 27)
							AND customer.is_deleted=0
						',
						'params' => array(
							':customer_id' => $customerQueue->customer_id,
							':skill_id' => $customerQueue->skill_id,
						),

					));
					
					// $customerRemoved = CustomerBillingWindowRemoved::model()->find(array(
						// 'condition' => '
							// customer_id = :customer_id 
							// AND skill_id = :skill_id 
							// AND MONTH(date_created) = :month
							// AND YEAR(date_created) = :year
						// ',
						// 'params' => array(
							// ':customer_id' => $customerQueue->customer_id,
							// ':skill_id' => $customerQueue->skill_id,
							// ':month' => date('n', strtotime($billingPeriod)),
							// ':year' => date('Y', strtotime($billingPeriod))
						// ),
					// ));
					
					// echo 'customerRemoved: ' . count($customerRemoved);
					// echo '<br><br>';
					if( $customerSkill )
					{
						if( isset($customerSkill->contract) )
						{
							$contract = $customerSkill->contract;
							$customer = $customerSkill->customer;
							
							$customerIsCallable = false;

							/*
								totalLeads
								totalAmount
								totalReducedAmount
								totalCreditAmount
								subsidyAmount
								isBilled
							*/
							
							$contractCreditSubsidys = $this->getCustomerContractCreditAndSubsidy($customer, $contract, $billingPeriod);		

							// echo '<pre>';
								// print_r($contractCreditSubsidys[$contract->id]);
							// echo '</pre>';
							
							echo '<br>';
							
							echo 'totalReducedAmount: ' . $contractCreditSubsidys[$contract->id]['totalReducedAmount'];
							echo '<br>';
							echo 'subsidyAmount: ' . $contractCreditSubsidys[$contract->id]['subsidyAmount'];
							echo '<br>';
							echo 'starting: ' . $customerSkill->start_month;
							echo '<br><br>';
							echo 'cancels: ' . $customerSkill->end_month;
							echo '<br><br>';
							echo 'billing period: ' . $billingPeriod;
							echo '<br><br>';
							echo date('M-y', strtotime('+1 month', strtotime($billingPeriod)));
							echo '<br><br>';
							echo date('M-y', strtotime('+1 month', strtotime($customerSkill->end_month)));
							echo '<br><br>';
							
							echo date('Y', strtotime($customerSkill->end_month)).' == '.date('Y', strtotime($billingPeriod));
							
							echo '<br><br>';
							
							echo date('M', strtotime($customerSkill->end_month)).' == '.date('M', strtotime($billingPeriod));
							
							echo '<br><br>';
							echo '<br><br>';						

							//cancels
							$impactReportSummary2 = ImpactReportSummary::model()->find(array(
								'condition' => 'month_name = :month_name',
								'params' => array(
									':month_name' => date('M-y', strtotime('+1 month', strtotime($billingPeriod))),
								),
							));
							
							if( $impactReportSummary2 )
							{
								$impactReportSummary2->cancels_affecting_amount = $impactReportSummary2->cancels_affecting_amount + ( $contractCreditSubsidys[$contract->id]['totalReducedAmount'] + $contractCreditSubsidys[$contract->id]['subsidyAmount'] );
								$impactReportSummary2->cancels_affecting_count = $impactReportSummary2->cancels_affecting_count + 1;
								
								if( $impactReportSummary2->save(false) )
								{
									$impactReportLink = new ImpactReportLink;
											
									$impactReportLink->setAttributes(array(
										'customer_id' => $customer->id,
										'customer_name' => $customer->getFullName(),
										'month_name' => date('M-y', strtotime('+1 month', strtotime($billingPeriod))),
										'link_name' => 'cancel_affecting_count'
									));
									
									$impactReportLink->save(false);
								}
							}
						}
						else
						{
							echo '<br>';
							echo 'contract not found';
							echo '<br><br>';
						}
					}
					else
					{
						echo '<br>';
						echo 'customerSkill not found';
						echo '<br><br>';
					}
				}
			}
			
			echo '<br><hr><br>';
		}
		
		echo '<br><br>';
		echo 'Process ended at ' . date('g:i A');
	}
	
	public function getCustomerContractCreditAndSubsidy($customer, $contract, $billing_period)
	{
		$contractCreditSubsidys = array();
		$customerSkills = CustomerSkill::model()->findAll(array(
			'with' => 'customer',
			'condition' => '
				t.customer_id = :customer_id AND t.contract_id = :contract_id
				AND customer.is_deleted=0
			',
			'params' => array(
				':customer_id' => $customer->id,
				':contract_id' => $contract->id,
			),

		));
		
		if( $customerSkills )
		{
			foreach($customerSkills as $customerSkill)
			{
				// $customerRemoved = CustomerBillingWindowRemoved::model()->find(array(
					// 'condition' => '
						// customer_id = :customer_id 
						// AND skill_id = :skill_id 
						// AND MONTH(date_created) = MONTH(NOW())
						// AND YEAR(date_created) = YEAR(NOW())
					// ',
					// 'params' => array(
						// ':customer_id' => $customerSkill->customer_id,
						// ':skill_id' => $customerSkill->skill_id,
					// ),
				// ));
					
				// if( isset($customerSkill->contract) && strtotime($billing_period) >= strtotime($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00' )
				// {
					$isBilled = false;
					
					$contract = $customerSkill->contract;
					$contractCreditSubsidys[$contract->id]['contract_name'] = $contract->contract_name;
					$contractCreditSubsidys[$contract->id]['totalCreditAmount'] = 0;
					$contractCreditSubsidys[$contract->id]['totalSubsidyAmount'] = 0;
					
					$existingBillingForCurrentMonth = CustomerBilling::model()->find(array(
						'condition' => '
							customer_id = :customer_id 
							AND contract_id = :contract_id
							AND transaction_type = "Charge"
							AND billing_period = :billing_period
							AND ( anet_responseCode = 1 OR ( amount = 0 AND anet_responseCode IS NULL ))
						',
						'params' => array(
							':customer_id' => $customerSkill->customer_id,
							':contract_id' => $customerSkill->contract_id,
							':billing_period' => $billing_period
						),
						'order' => 'date_created DESC'
					));
					
					if( $existingBillingForCurrentMonth )
					{
						$isBilled = true;
						
						$existingBillingForCurrentMonthVoidorRefund = CustomerBilling::model()->find(array(
							'condition' => '
								customer_id = :customer_id 
								AND contract_id = :contract_id
								AND anet_responseCode = 1
								AND reference_transaction_id = :reference_transaction_id
								AND (
									transaction_type = "Void"
									OR transaction_type = "Refund"
								)
							',
							'params' => array(
								':customer_id' => $customerSkill->customer_id,
								':contract_id' => $customerSkill->contract_id,
								':reference_transaction_id' => $existingBillingForCurrentMonth->id,
							),
							'order' => 'date_created DESC'
						)); 
						
						if( $existingBillingForCurrentMonthVoidorRefund )
						{
							$isBilled = false;
						}
						else
						{
							$isBilled = true;
						}
					}
					
					$totalLeads = 0;
					$totalAmount = 0;
					$subsidyAmount = 0;
					$month = '';
					$latestTransactionType = '';
					$latestTransactionStatus = '';
					
					if($contract->fulfillment_type != null )
					{
						if( $contract->fulfillment_type == Contract::TYPE_FULFILLMENT_GOAL_VOLUME )
						{
							if( !empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME]) )
							{
								foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME] as $key => $subsidyLevel )
								{
									$customerSkillLevelArray = $customerSkill->getCustomerSkillLevelArray();
									$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;

									if( $customerSkillLevelArrayGroup != null )
									{							
										if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
										{
											$totalLeads += ( $subsidyLevel['goal'] * $customerSkillLevelArrayGroup->quantity );
											$totalAmount += ( $customerSkillLevelArrayGroup->quantity * $subsidyLevel['amount'] );
										}
									}
								}
							}

							$customerExtras = CustomerExtra::model()->findAll(array(
								'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id AND status=1 AND year = :year AND month = :month',
								'params' => array(
									':customer_id' => $customerSkill->customer_id,
									':contract_id' => $customerSkill->contract_id,
									':skill_id' => $customerSkill->skill_id,
									':month' => date('n', strtotime($billingPeriod)),
									':year' => date('Y', strtotime($billingPeriod))
								),
							));
							
							if( $customerExtras )
							{
								foreach( $customerExtras as $customerExtra )
								{
									$totalLeads += $customerExtra->quantity;
								}
							}
						}
						else
						{
							if( !empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME]) )
							{
								foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME] as $key => $subsidyLevel )
								{
									$customerSkillLevelArray = $customerSkill->getCustomerSkillLevelArray();
									
									$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;
									
									if( $customerSkillLevelArrayGroup != null )
									{
										if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
										{
											$totalLeads += ( $subsidyLevel['high'] * $customerSkillLevelArrayGroup->quantity );
											$totalAmount += ( $customerSkillLevelArrayGroup->quantity * $subsidyLevel['amount'] );
										}
									}
								}
							}
							
							$customerExtras = CustomerExtra::model()->findAll(array(
								'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id AND status=1 AND year = :year AND month = :month',
								'params' => array(
									':customer_id' => $customerSkill->customer_id,
									':contract_id' => $customerSkill->contract_id,
									':skill_id' => $customerSkill->skill_id,
									':month' => date('n', strtotime($billingPeriod)),
									':year' => date('Y', strtotime($billingPeriod))
								),
							));
							
							if( $customerExtras )
							{
								foreach( $customerExtras as $customerExtra )
								{
									$totalLeads += $customerExtra->quantity;
								}
							}
						}
					
						$contractCreditSubsidys[$contract->id]['totalAmount'] = $totalAmount;
						
						$customerSkillSubsidyLevel = CustomerSkillSubsidyLevel::model()->find(array(
							'condition' => 'customer_id = :customer_id AND customer_skill_id = :customer_skill_id',
							'params' => array(
								':customer_id' => $customerSkill->customer_id,
								':customer_skill_id' => $customerSkill->id,
							),
						));
						
						$customerSkillSubsidy = CustomerSkillSubsidy::model()->find(array(
							'condition' => 'customer_id = :customer_id AND customer_skill_id = :customer_skill_id',
							'params' => array(
								':customer_id' => $customerSkill->customer_id,
								':customer_skill_id' => $customerSkill->id,
							),
						));
						
						// if( $customerSkillSubsidyLevel )
						if( !empty($customerSkillSubsidyLevel) && !empty($customerSkillSubsidy) && $customerSkillSubsidy->status == CustomerSkillSubsidy::STATUS_ACTIVE )
						{
							$subsidy = CompanySubsidyLevel::model()->find(array(
								'condition' => 'id = :id AND type="%"',
								'params' => array(
									':id' => $customerSkillSubsidyLevel->subsidy_level_id,
								),
							));
							
							if( $subsidy )
							{
								$subsidyPercent = $subsidy->value;
								
								$subsidyPercentInDecimal = $subsidyPercent / 100;

								if( $subsidyPercentInDecimal > 0 )
								{
									// if( !$isBilled )
									// {
										$subsidyAmount = $subsidyPercentInDecimal * $totalAmount; 
									// }
									
									$contractCreditSubsidys[$contract->id]['totalSubsidyAmount'] = $subsidyAmount;
								}
							}
						}
					}
					
					$totalCreditAmount = 0;
					$customerCredits = CustomerCredit::model()->findAll(array(
						'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND status=1',
						'params' => array(
							':customer_id' => $customerSkill->customer_id,
							':contract_id' => $customerSkill->contract_id,
						),
					));
					
					if( $customerCredits )
					{
						foreach( $customerCredits as $customerCredit )
						{
							$creditStartDate = date('Y-'.$customerCredit->start_month.'-1');
											
							if( $customerCredit->type == 2 ) //month range
							{
								if( $customerCredit->end_month == '02' )
								{
									$creditEndDate = date('Y-'.$customerCredit->end_month.'-28');
								}
								else
								{
									$creditEndDate = date('Y-'.$customerCredit->end_month.'-t');
								}
								
								if( $customerCredit->start_month >= $customerCredit->end_month )
								{
									$creditEndDate = date('Y-m-d', strtotime('+1 year', strtotime($creditEndDate)));
								}
							}
							else
							{
								if( $customerCredit->start_month )
								{
									$creditEndDate = date('Y-'.$customerCredit->start_month.'-28');
								}
								else
								{
									$creditEndDate = date('Y-'.$customerCredit->start_month.'-t');
								}
							}
							
							if( (strtotime('now') >= strtotime($creditStartDate)) && (strtotime('now') <= strtotime($creditEndDate)) )
							{
								$totalCreditAmount += $customerCredit->amount;
							}
						}
					}
					
					
					$contractCreditSubsidys[$contract->id]['totalCreditAmount'] = $totalCreditAmount;
					
					$totalReducedAmount = ($totalAmount - $totalCreditAmount - $subsidyAmount);
					if( $totalReducedAmount < 0 )
						$totalReducedAmount = 0;
					
					$contractCreditSubsidys[$contract->id]['totalLeads'] = $totalLeads;
					$contractCreditSubsidys[$contract->id]['totalAmount'] = number_format($totalAmount, 2);
					$contractCreditSubsidys[$contract->id]['totalReducedAmount'] = number_format($totalReducedAmount, 2);
					$contractCreditSubsidys[$contract->id]['totalCreditAmount'] = number_format($totalCreditAmount, 2);
					$contractCreditSubsidys[$contract->id]['subsidyAmount'] = number_format($subsidyAmount, 2);
					$contractCreditSubsidys[$contract->id]['isBilled'] = $isBilled;
				// }
			
			}
		}
	
		return $contractCreditSubsidys;
	}


	public function actionCalendarPatch()
	{
		$models = CalendarAppointment::model()->findAll(array(
			'condition' => '
				calendar_id = :calendar_id 
				AND title="INSERT APPOINTMENT"
				AND lead_id IS NULL
				AND status != 4 
				AND start_date >= NOW()
			',
			'params' => array(
				':calendar_id' => 821,
			),
		)); 
		
		echo 'count: ' . count($models);
		
		echo '<br><br>';
		
		if( $models )
		{
			$ctr = 0;
			
			foreach( $models as $model )
			{
				$model->status = 4;
				
				if( $model->save(false) )
				{
					echo $ctr++;
					echo '<br>';
				}
			}
		}
		
		echo 'ctr: ' . $ctr;
	}
	
	public function actionCustomerPatch()
	{
		$customerBillings = CustomerBilling::model()->findAll(array(
			'condition' => '
				transaction_type = "Charge"
				AND billing_period = :billing_period
				AND id NOT IN (17535, 17573, 17530, 17508, 18205, 25498)
			',
			'params' => array(
				':billing_period' => date('M Y')
			),
			'order' => 'id DESC'
		));
		
		echo 'customerBillings: ' . count($customerBillings);
		
		echo '<br><br>';
		
		if( $customerBillings )
		{
			$ctr = 1;
			$paidCtr = 0;
			$notPaidCtr = 0;
			
			$billedCustomers = array();
			
			foreach( $customerBillings as $customerBilling )
			{
				$customerIdAndContractId = $customerBilling->customer_id.'-'.$customerBilling->contract_id;
				
				if( !in_array($customerIdAndContractId, $billedCustomers) )
				{
					$billedCustomers[] = $customerIdAndContractId;
					
					$customerQueue = CustomerQueueViewer::model()->find(array(
						'condition' => 'customer_id = :customer_id AND contract_id = :contract_id',
						'params' => array(
							':customer_id' => $customerBilling->customer_id,
							':contract_id' => $customerBilling->contract_id,
						),
					));
					
					if( $customerBilling->anet_responseCode == 1 || ( $customerBilling->anet_responseCode == null && $customerBilling->amount == 0 ) )
					{
						$customerSkill = CustomerSkill::model()->find(array(
							'condition' => 'customer_id = :customer_id AND contract_id = :contract_id',
							'params' => array(
								':customer_id' => $customerBilling->customer_id,
								':contract_id' => $customerBilling->contract_id,
							),
						));
						
						if( $customerSkill )
						{
							$existingBillingForCurrentMonthVoidorRefund = CustomerBilling::model()->find(array(
								'condition' => '
									customer_id = :customer_id 
									AND contract_id = :contract_id
									AND anet_responseCode = 1
									AND reference_transaction_id = :reference_transaction_id
									AND (
										transaction_type = "Void"
										OR transaction_type = "Refund"
									)
								',
								'params' => array(
									':customer_id' => $customerBilling->customer_id,
									':contract_id' => $customerBilling->contract_id,
									':reference_transaction_id' => $customerBilling->id,
								),
								'order' => 'date_created DESC'
							));
							
							if( $existingBillingForCurrentMonthVoidorRefund )
							{
								$customerSkill->is_hold_for_billing = 0;
								
								if( $customerSkill->save(false) )
								{
									echo $ctr.'. Customer Name: ' . $customerQueue->customer_name . ' | Paid: No';
									echo $customerQueue->customer_name;
									echo '<br>';
								}
							}
							else
							{
								$customerSkill->is_hold_for_billing = 0;
								
								if( $customerSkill->save(false) )
								{
									echo $ctr.'. Customer Name: ' . $customerQueue->customer_name . ' | Paid: Yes';
									echo '<br>';
								}
							}
						}
					}
					else
					{
						// echo '<br>';
						
						// echo '<pre>';
							// print_r($customerBilling->attributes);
						// echo '</pre>';
						
						// echo '<br>';
						
						$customerSkill = CustomerSkill::model()->find(array(
							'condition' => 'customer_id = :customer_id AND contract_id = :contract_id',
							'params' => array(
								':customer_id' => $customerBilling->customer_id,
								':contract_id' => $customerBilling->contract_id,
							),
						));
						
						if( $customerSkill )
						{
							$customerSkill->is_hold_for_billing = 1;
							
							if( $customerSkill->save(false) )
							{
								$notPaidCtr++;
								
								echo $ctr.'. Customer Name: ' . $customerQueue->customer_name . ' | Paid: No 2';
								echo $customerQueue->customer_name;
								echo '<br>';
							}
						}
					}
					
					if( $customerSkill->customer_id == 892 )
					{
						$customerSkill->is_hold_for_billing = 0;
								
						if( $customerSkill->save(false) )
						{
							$paidCtr++;
							
							echo $ctr.'. Customer Name: ' . $customerQueue->customer_name . ' | Paid: Yes';
							echo '<br>';
						}
					}
					
					$ctr++;
				}
			}
			
			echo '<br>';
			echo '<br>';
			echo 'total unique billing count: ' . count($billedCustomers);
			echo '<br>';
			echo 'removed from hold: ' . $paidCtr;
			echo '<br>';
			echo 'put on hold: ' . $notPaidCtr;
		}
	}

	
	public function actionRestoreList()
	{
		$list = Lists::model()->findByPk(3924);
		
		if( $list )
		{
			$leads = Lead::model()->findAll(array(
				'condition' => 'list_id = 3924 AND status=4',
			));
			
			echo 'count: ' . count($leads);
			
			echo '<br><br>';
			
			exit;
			
			if( $leads )
			{
				$ctr = 0;
				
				foreach( $leads as $lead )
				{
					$lead->status = 1;
					
					if( $lead->save(false) )
					{
						echo $ctr;
						echo '<br>';
					}
				}
			}
			
			echo 'ctr: ' . $ctr; 
			
		}
	}

	
	public function actionMergeLeadHistory()
	{
		//old list id: 3924
		//new list id: 3947
		
		$leads = Lead::model()->findAll(array(
			'condition' => 'list_id = :list_id',
			'params' => array(
				':list_id' => 3947
			),
		));
		
		echo 'leads: ' . count($leads);
		
		echo '<br><br>';
		
		exit;
		
		if( $leads )
		{
			$ctr = 0;
			
			foreach( $leads as $lead )
			{
				$oldLead = Lead::model()->find(array(
					'condition' => 't.list_id = :list_id AND ( 
						(office_phone_number = :office_phone_number AND office_phone_number != "" AND office_phone_number IS NOT NULL) OR 
						(office_phone_number = :mobile_phone_number AND office_phone_number != "" AND office_phone_number IS NOT NULL) OR 
						(office_phone_number = :home_phone_number AND office_phone_number != "" AND office_phone_number IS NOT NULL) OR 
						(mobile_phone_number = :office_phone_number AND mobile_phone_number != "" AND mobile_phone_number IS NOT NULL) OR
						(mobile_phone_number = :mobile_phone_number AND mobile_phone_number != "" AND mobile_phone_number IS NOT NULL) OR
						(mobile_phone_number = :home_phone_number AND mobile_phone_number != "" AND mobile_phone_number IS NOT NULL) OR
						(home_phone_number = :office_phone_number AND home_phone_number != "" AND home_phone_number IS NOT NULL) OR
						(home_phone_number = :mobile_phone_number AND home_phone_number != "" AND home_phone_number IS NOT NULL) OR
						(home_phone_number = :home_phone_number AND home_phone_number != "" AND home_phone_number IS NOT NULL) 
					)',
					'params' => array(
						':list_id' => 3924,
						':office_phone_number' => $lead->office_phone_number,
						':mobile_phone_number' => $lead->mobile_phone_number,
						':home_phone_number' => $lead->home_phone_number,
					),
				));
				
				if( $oldLead )
				{
					$callHistories = LeadCallHistory::model()->findAll(array(
						'condition' => 'lead_id = :lead_id',
						'params' => array(
							':lead_id' => $oldLead->id,
						),
					));
					
					if( $callHistories )
					{
						foreach( $callHistories as $callHistory )
						{
							$callHistory->lead_id = $lead->id;
							$callHistory->list_id = $lead->list_id;
							$callHistory->save(false);
						}
					}
					
					$leadHistories = LeadHistory::model()->findAll(array(
						'condition' => 'lead_id = :lead_id',
						'params' => array(
							':lead_id' => $oldLead->id,
						),
					));
					
					if( $leadHistories )
					{
						foreach( $leadHistories as $leadHistory )
						{
							$leadHistory->lead_id = $lead->id;
							$leadHistory->save(false);
						}
					}
					
					$lead->number_of_dials = count($callHistories);
					
					$lead->home_phone_dial_count = $lead->home_phone_dial_count + $oldLead->home_phone_dial_count;
					$lead->office_phone_dial_count = $lead->office_phone_dial_count + $oldLead->office_phone_dial_count;
					$lead->mobile_phone_dial_count = $lead->mobile_phone_dial_count + $oldLead->mobile_phone_dial_count;
					
					// echo '<pre>';
						// print_r($lead->attributes);
					// echo '</pre>';
					// exit;
					
					if( $lead->save(false) )
					{
						$ctr++;
					}
				}
			}
			
			echo 'ctr: ' . $ctr;
		}
	}

	
	public function actionCustomerList()
	{
		$customerQueues = CustomerQueueViewer::model()->findAll(array(
			'with' => 'customer',
			// 'condition' => 'next_available_calling_time NOT IN ("On Hold", "Decline Hold")',
			'order' => 'customer.lastname ASC',
		));
		
		echo '<table>';
		
			echo '<tr>';
				echo '<td>Agent ID</td>';
				echo '<td>Customer Name</td>';
				echo '<td>Default Credit Card Expiration Date</td>';
				echo '<td>Customer Status</td>';
			echo '</tr>';
		
		if( $customerQueues )
		{
			foreach( $customerQueues as $customerQueue )
			{
				$status = 'Inactive';
				$customerIsCallable = false;
				
				$customerSkill = CustomerSkill::model()->find(array(
					'with' => 'customer',
					'condition' => '
						t.customer_id = :customer_id 
						AND t.skill_id = :skill_id 
						AND t.status=1 
						AND customer.company_id NOT IN(15, 17,18,23, 24, 25, 26, 27)
						AND customer.status=1
						AND customer.is_deleted=0
					',
					'params' => array(
						':customer_id' => $customerQueue->customer_id,
						':skill_id' => $customerQueue->skill_id,
					),

				));
				
				if( $customerSkill )
				{
				
					if( isset($customerSkill->contract) && isset($customerSkill->customer) && $customerSkill->customer->status == 1 && $customerSkill->customer->is_deleted == 0 && time() >= strtotime($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00' )
					{
						$status = 'Active';
					}
					
					if( $customerSkill->is_contract_hold == 1 )
					{
						if( !empty($customerSkill->is_contract_hold_start_date) && !empty($customerSkill->is_contract_hold_end_date) )
						{
							if( time() >= strtotime($customerSkill->is_contract_hold_start_date) && time() <= strtotime($customerSkill->is_contract_hold_end_date) )
							{
								$status = 'On Hold';
							}
						}
					}
					
					if( $customerSkill->is_hold_for_billing == 1 )
					{
						$status = 'Decline Hold';
					}
					
					if( !empty($customerSkill->end_month) && date('Y', strtotime($customerSkill->end_month)) >= 2015 )
					{
						if( time() >= strtotime($customerSkill->end_month) )
						{
							$status = 'Cancelled';
						}
					}
					
					$method = explode('-', CustomerBilling::model()->getDefaultMethod($customerQueue->customer_id));
					$type = $method[0];
					$cardId = $method[1];
					
					$defaultCreditCard = CustomerCreditCard::model()->findByPk($cardId);
				
					if( $defaultCreditCard )
					{
						echo '<tr>';
							echo '<td>'.$customerQueue->custom_customer_id.'</td>';
							echo '<td>'.$customerQueue->customer_name.'</td>';
							echo '<td>'.date('F', mktime(0, 0, 0, $defaultCreditCard->expiration_month, 10)) .' - '.$defaultCreditCard->expiration_year.'</td>';
							echo '<td>'.$status.'</td>';
						echo '</tr>';
					}
				}
			}
		}
		
		echo '</table>';
	}

	
	public function actionMonthlyLimitPatch()
	{
		$customerQueues = CustomerQueueViewer::model()->findAll(array(
			// 'with' => 'customer',
			// 'condition' => 'fulfillment_type="Goal"',
			'condition' => 'fulfillment_type IN ("Lead", "Goal")',
			// 'condition' => 'customer_id IN ("404")',
			// 'order' => 'customer.lastname ASC',
		));
		
		// $customerQueues = CustomerQueueViewer::model()->findAll();
		
		if( $customerQueues )
		{
			foreach( $customerQueues as $customerQueue )
			{
				$customerSkill = CustomerSkill::model()->find(array(
					'condition' => 'customer_id = :customer_id AND status=1',
					'params' => array(
						':customer_id' => $customerQueue->customer_id,
					),
				));
					
				if( $customerSkill )
				{
					$importLimit = 0;
					$contractedLeads = 0;
					
					if( isset($customerSkill->contract) )
					{
						$contract = $customerSkill->contract;
						
						if($contract->fulfillment_type != null )
						{
							if( $contract->fulfillment_type == Contract::TYPE_FULFILLMENT_GOAL_VOLUME )
							{
								foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME] as $key => $subsidyLevel )
								{
									$customerSkillLevelArray = $customerSkill->getCustomerSkillLevelArray();
									$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;

									if( $customerSkillLevelArrayGroup != null )
									{							
										if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
										{
											$contractedLeads += ( $subsidyLevel['goal'] * $customerSkillLevelArrayGroup->quantity );
										}
									}
								}
								
								$importLimit = $contractedLeads * 10;
							}
							else
							{
								foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME] as $key => $subsidyLevel )
								{
									$customerSkillLevelArray = $customerSkill->getCustomerSkillLevelArray();
									$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;

									if( $customerSkillLevelArrayGroup != null )
									{							
										if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
										{
											$contractedLeads += ( $subsidyLevel['high'] * $customerSkillLevelArrayGroup->quantity );
										}
									}
								}
								
								$importLimit = $contractedLeads;
							}
						}
						
					
					}
					
					if( $importLimit > 0 )
					{
						$lists = Lists::model()->findAll(array(
							'condition' => '
								t.customer_id = :customer_id 
								AND skill_id = :skill_id 
								AND t.status != 3 
								AND t.name NOT IN ("System Imported List", "System Imported Completed List")
								AND t.date_created >= "2016-09-01 0000-00-00"
							',
							'params' => array(
								':customer_id' => $customerQueue->customer_id,
								':skill_id' => $customerQueue->skill_id,
							),
						));
						
						if( $lists )
						{
							foreach( $lists as $list )
							{
								$totalLeads = Lead::model()->count(array(
									'condition' => '
										t.list_id IS NOT NULL
										AND t.list_id = :list_id
										AND t.type=1 
										AND t.status != 4
									',
									'params' => array(
										':list_id' => $list->id,
									),
								));
								
								$noDialLeadCount = Lead::model()->count(array(
									'condition' => '
										t.list_id IS NOT NULL
										AND t.list_id = :list_id
										AND t.type=1 
										AND t.status=1
										AND t.number_of_dials = 0
									',
									'params' => array(
										':list_id' => $list->id,
									),
								));
							 						
								if( $totalLeads > $importLimit )
								{	
									$excessCount = $totalLeads - $importLimit; 
									
									$removeNoDialLeads = Lead::model()->findAll(array(
										'condition' => '
											t.list_id IS NOT NULL
											AND t.list_id = :list_id
											AND t.type=1 
											AND t.status=1
											AND t.number_of_dials = 0
										',
										'params' => array(
											':list_id' => $list->id,
										),
										'limit' => $excessCount,
									));
									
									if( $removeNoDialLeads )
									{
										foreach( $removeNoDialLeads as $removeNoDialLead )
										{
											$removeNoDialLead->list_id = null;
											$removeNoDialLead->save(false);
										}
									}
								
									echo $customerQueue->customer_name;
									echo '<br>';
									// echo 'excessCount: ' . $excessCount;
									// echo '<br>';
									// echo 'totalLeads: ' . $totalLeads;
									// echo '<br>';
									// echo 'removeNoDialLeads: ' . count($removeNoDialLeads);
									// echo '<br>';
									// echo 'contracted_quantity: ' . $customerQueue->contracted_quantity;
									// echo '<br>';
									// echo 'import limit: ' . $importLimit;
									// echo '<br>';
									// echo 'list: ' . $list->name;
									// echo '<br>';
									// echo 'totalLeads: ' . $totalLeads;
									// echo '<br>';
									// echo 'noDialLeads: ' . $noDialLeadCount;
									// echo '<br>';
									// echo '<hr>';
									// echo '<br>';
								}
							}
						}
					}
				}
			}
		}
	}

	public function actionCheckLeadTimeZone()
	{
		date_default_timezone_set('America/Denver');
		
		$skillScheduleHolder = array();
		
		$nextAvailableCallingTime = '';
		
		$type = 'customer';
		
		// $lead = Lead::model()->findByPk(828913);
		
		$customerSkill = CustomerSkill::model()->find(array(
			'condition' => 'customer_id = :customer_id',
			'params' => array(
				':customer_id' => 2607
			),
		));

		$customer = $customerSkill->customer;
		
			
		$currentDateTime = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('America/Denver'));
		// $currentDateTime->setTimezone(new DateTimeZone('America/Denver')); 

		
		if( $customerSkill->is_custom_call_schedule == 1 )
		{
			$customCallSchedules = CustomerSkillSchedule::model()->findAll(array(
				'condition' => 'customer_skill_id = :customer_skill_id AND schedule_day = :schedule_day',
				'params' => array(
					':customer_skill_id' => $customerSkill->id,
					':schedule_day' => date('N'),
				),
			));
			
			if( $customCallSchedules )
			{
				foreach( $customCallSchedules as $customCallSchedule )
				{
					$skillScheduleHolder[$customer->id][$customCallSchedule->id]['schedule_start'] = date('g:i A', strtotime($customCallSchedule->schedule_start));
					$skillScheduleHolder[$customer->id][$customCallSchedule->id]['schedule_end'] = date('g:i A', strtotime($customCallSchedule->schedule_end));
				}
			}
		}
		else
		{	
			$skillSchedules = SkillSchedule::model()->findAll(array(
				// 'condition' => 'skill_id = :skill_id AND schedule_day = :schedule_day AND status=1 AND is_deleted=0',
				'condition' => 'skill_id = :skill_id AND schedule_day = :schedule_day',
				'params' => array(
					'skill_id' => $customerSkill->skill_id,
					':schedule_day' => date('N'),
				),
			));

			foreach($skillSchedules as $skillSchedule)
			{
				$skillScheduleHolder[$customer->id][$skillSchedule->id]['schedule_start'] = date('g:i A', strtotime($skillSchedule->schedule_start));
				$skillScheduleHolder[$customer->id][$skillSchedule->id]['schedule_end'] = date('g:i A', strtotime($skillSchedule->schedule_end));
			}
		}
		
		echo '<pre>';
			print_r($skillScheduleHolder);
		echo '</pre>';
		
		echo '<br><br>';
	
		
		if( isset($skillScheduleHolder[$customer->id]) )
		{	
			foreach($skillScheduleHolder[$customer->id] as $sched)
			{	
				if( $type == 'customer' )
				{
					$timeZone = $customer->getTimeZone();
				}
				else
				{
					if( !empty($lead->timezone) )
					{
						$timeZone = $lead->timezone;
					}
					else
					{
						$timeZone = $customer->getTimeZone();
					}
				}
				
				if( !empty($timeZone) )
				{
					$timeZone = timezone_name_from_abbr($timeZone);
													
					// if( strtoupper($lead->timezone) == 'AST' )
					// {
						// $timeZone = 'America/Puerto_Rico';
					// }
					
					// if( strtoupper($lead->timezone) == 'ADT' )
					// {
						// $timeZone = 'America/Halifax';
					// }
					
					if( $type == 'customer' )
					{
						$nextAvailableCallingTimeStart = new DateTime($sched['schedule_start'], new DateTimeZone($timeZone) );
						$nextAvailableCallingTimeEnd = new DateTime($sched['schedule_end'], new DateTimeZone($timeZone) );
						
						$nextAvailableCallingTimeStart->setTimezone(new DateTimeZone('America/Denver'));
						$nextAvailableCallingTimeEnd->setTimezone(new DateTimeZone('America/Denver'));
						
						$nextAvailableCallingTime = $nextAvailableCallingTimeStart->format('g:i A');

						if( strtotime($currentDateTime->format('g:i A')) >= strtotime($nextAvailableCallingTimeStart->format('g:i A')) && strtotime($currentDateTime->format('g:i A')) <= strtotime($nextAvailableCallingTimeEnd->format('g:i A')) )
						{
							$nextAvailableCallingTime = 'Now';
						}
						
						if( strtotime($currentDateTime->format('g:i A')) >= strtotime($nextAvailableCallingTimeStart->format('g:i A')) && strtotime($currentDateTime->format('g:i A')) >= strtotime($nextAvailableCallingTimeEnd->format('g:i A')) )
						{
							$nextAvailableCallingTime = 'Next Shift';
						}
						
						if( in_array($nextAvailableCallingTime, array('7:00 AM', '7:30 AM', '8:00 AM')) && time() >= strtotime('today 6:00 am') )
						{
							$nextAvailableCallingTime = 'Now';
						}
						
						//exclude these customers on the forced call schedule
						//63 - Engagex Email Collection
						//1029 - Alaska Sales Region
						//1031 - Washington Sales Region
						//1032 - Denver Sales Region
						//1036 - Las Vegas Sales Region
						//1035 - Phoenix/Mesa Sales Region	
						//2129 - Sandia
						if( !in_array($customer->id, array(63, 1029, 1031, 1032, 1036, 1035, 2129)) )
						{
							if( in_array($this->get_timezone_abbreviation($timeZone), array('EST', 'EDT')) )
							{
								if( time() >= strtotime('today 9:00 AM') && time() < strtotime('today 5:00 PM') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									$nextAvailableCallingTime = '9:00 AM - 5:00 PM';
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('CST', 'CDT')) )
							{
								if( time() >= strtotime('today 10:00 AM') && time() < strtotime('today 6:00 PM') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									$nextAvailableCallingTime = '10:00 AM - 6:00 PM';
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('MST', 'MDT')) )
							{
								if( time() >= strtotime('today 10:00 AM') && time() < strtotime('today 7:00 PM') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									$nextAvailableCallingTime = '10:00 AM - 7:00 PM';
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('PST', 'PDT')) )
							{
								if( time() >= strtotime('today 11:00 AM') && time() < strtotime('today 8:00 PM') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									$nextAvailableCallingTime = '11:00 AM - 8:00 PM';
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('HAST', 'HADT')) )
							{
								if( time() >= strtotime('today 3:00 pm') && time() < strtotime('today 10:00 PM') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									$nextAvailableCallingTime = '3:00 PM - 10:00 PM';
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('AKST', 'AKDT')) )
							{
								if( time() >= strtotime('today 1:00 pm') && time() < strtotime('today 8:00 PM') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									$nextAvailableCallingTime = '1:00 PM - 8:00 PM';
								}
							}
						}

						
						//modified customer call schedules
						// $modifiedCustomerArray = array(522, 408, 1512, 1499, 1493, 1538, 1505, 1474, 490, 1370, 1627, 1598, 521, 1498, 1155, 1548, 1612, 172, 1481, 1260, 1503, 869, 1420, 1546, 1181);
						// $modifiedCustomerArray = array(522, 1493, 1505, 1598, 1646);
						$modifiedCustomerArray = array(522, 1966);
						
						if( in_array($customer->id, $modifiedCustomerArray) )
						{
							if( in_array($this->get_timezone_abbreviation($timeZone), array('EST', 'EDT')) )
							{
								if( time() >= strtotime('today 8:00 am') && time() < strtotime('today 5:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 8:00 AM') ) 
									{
										$nextAvailableCallingTime = '8:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('CST', 'CDT')) )
							{
								if( time() >= strtotime('today 9:00 am') && time() < strtotime('today 6:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 9:00 AM') ) 
									{
										$nextAvailableCallingTime = '9:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('PST', 'PDT')) )
							{
								if( time() >= strtotime('today 11:00 am') && time() < strtotime('today 8:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 11:00 AM') ) 
									{
										$nextAvailableCallingTime = '11:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('MST', 'MDT')) )
							{
								if( time() >= strtotime('today 10:00 am') && time() < strtotime('today 7:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 10:00 AM') ) 
									{
										$nextAvailableCallingTime = '10:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
						}
						
						
						//49 -Jory Bowers
						//56 - Valerie Strickland
						if( in_array($customer->id, array(49, 56)) )
						{
							if( time() >= strtotime('today 7:00 am') && time() < strtotime('today 6:00 pm') )
							{
								$nextAvailableCallingTime = 'Now';
								
								//temporary code to remove eastern leads after call schedule => 9am - 5pm  local time
								if( time() >= strtotime('today 3:00 pm') )
								{
									LeadHopper::model()->deleteAll(array(
										'condition' => 'customer_id = :customer_id AND lead_timezone IN ("EST", "EDT") AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
										'params' => array(
											':customer_id' => $customer->id,
										),
									));
								}
								
								//temporary code to remove central leads after call schedule => 9am - 5pm  local time
								if( time() >= strtotime('today 4:00 pm') )
								{
									LeadHopper::model()->deleteAll(array(
										'condition' => 'customer_id = :customer_id AND lead_timezone IN ("CST", "CDT") AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
										'params' => array(
											':customer_id' => $customer->id,
										),
									));
								}
											
								//temporary code to remove mountain leads after call schedule => 9am - 5pm  local time
								if( time() >= strtotime('today 5:00 pm') )
								{
									LeadHopper::model()->deleteAll(array(
										'condition' => 'customer_id = :customer_id AND lead_timezone IN ("MST", "MDT") AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
										'params' => array(
											':customer_id' => $customer->id,
										),
									));
								}
								
								//temporary code to remove pacific leads after call schedule => 9am - 5pm  local time
								if( time() >= strtotime('today 6:00 pm') )
								{
									LeadHopper::model()->deleteAll(array(
										'condition' => 'customer_id = :customer_id AND lead_timezone IN ("PST", "PDT") AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
										'params' => array(
											':customer_id' => $customer->id,
										),
									));
								}
							}
							else
							{
								if( time() < strtotime('today 7:00 AM') ) 
								{
									$nextAvailableCallingTime = '7:00 AM';
								}
								else
								{
									$nextAvailableCallingTime = 'Next Shift';
								}
							}
						}
					
					
						//2011 - Indigo Sky
						if( $customer->id == 2011 )
						{
							date_default_timezone_set('America/Denver');
							
							if( time() >= strtotime('today 8:00 am') && time() < strtotime('today 6:00 pm') )
							{
								$nextAvailableCallingTime = 'Now';
								
								//temporary code to remove eastern leads after call schedule => 9am - 5pm  local time
								if( time() >= strtotime('today 5:00 pm') )
								{
									echo '<br><br>';
									echo '<b>Removed eastern leads</b>';
									echo '<br><br>';
									
									LeadHopper::model()->deleteAll(array(
										'condition' => 'customer_id = :customer_id AND lead_timezone IN ("EST", "EDT") AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
										'params' => array(
											':customer_id' => $customer->id,
										),
									));
								}
								
								//temporary code to remove central leads after call schedule => 9am - 5pm  local time
								if( time() >= strtotime('today 6:00 pm') )
								{
									echo '<br><br>';
									echo '<b>Removed central leads</b>';
									echo '<br><br>';
									
									LeadHopper::model()->deleteAll(array(
										'condition' => 'customer_id = :customer_id AND lead_timezone IN ("CST", "CDT") AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
										'params' => array(
											':customer_id' => $customer->id,
										),
									));
								}
											
								//temporary code to remove mountain leads after call schedule => 9am - 5pm  local time
								if( time() >= strtotime('today 7:00 pm') )
								{
									echo '<br><br>';
									echo '<b>Removed mountain leads</b>';
									echo '<br><br>';
									
									LeadHopper::model()->deleteAll(array(
										'condition' => 'customer_id = :customer_id AND lead_timezone IN ("MST", "MDT") AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
										'params' => array(
											':customer_id' => $customer->id,
										),
									));
								}
								
								//temporary code to remove pacific leads after call schedule => 9am - 5pm  local time
								if( time() >= strtotime('today 8:00 pm') )
								{
									echo '<br><br>';
									echo '<b>Removed pacific leads</b>';
									echo '<br><br>';
									
									LeadHopper::model()->deleteAll(array(
										'condition' => 'customer_id = :customer_id AND lead_timezone IN ("PST", "PDT") AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
										'params' => array(
											':customer_id' => $customer->id,
										),
									));
								}
							}
							else
							{
								echo '<br><br>';
								echo '<b>Removed all indigo leads</b>';
								echo '<br><br>';
								
								if( time() < strtotime('today 8:00 AM') ) 
								{
									$nextAvailableCallingTime = '8:00 AM - 6:00 PM';
								}
								else
								{
									$nextAvailableCallingTime = 'Next Shift';
								}
							}
						}
					
						//2363 - Station
						/*
						if( $customer->id == 2363 )
						{
							date_default_timezone_set('America/Denver');
							
							if( time() >= strtotime('today 8:00 am') && time() < strtotime('today 6:00 pm') )
							{
								$nextAvailableCallingTime = 'Now';
								
								//temporary code to remove eastern leads after call schedule => 9am - 5pm  local time
								if( time() >= strtotime('today 2:00 pm') )
								{
									echo '<br><br>';
									echo '<b>Removed eastern leads</b>';
									echo '<br><br>';
									
									LeadHopper::model()->deleteAll(array(
										'condition' => 'customer_id = :customer_id AND lead_timezone IN ("EST", "EDT") AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
										'params' => array(
											':customer_id' => $customer->id,
										),
									));
								}
								
								//temporary code to remove central leads after call schedule => 9am - 5pm  local time
								if( time() >= strtotime('today 3:00 pm') )
								{
									echo '<br><br>';
									echo '<b>Removed central leads</b>';
									echo '<br><br>';
									
									LeadHopper::model()->deleteAll(array(
										'condition' => 'customer_id = :customer_id AND lead_timezone IN ("CST", "CDT") AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
										'params' => array(
											':customer_id' => $customer->id,
										),
									));
								}
											
								//temporary code to remove mountain leads after call schedule => 9am - 5pm  local time
								if( time() >= strtotime('today 4:00 pm') )
								{
									echo '<br><br>';
									echo '<b>Removed mountain leads</b>';
									echo '<br><br>';
									
									LeadHopper::model()->deleteAll(array(
										'condition' => 'customer_id = :customer_id AND lead_timezone IN ("MST", "MDT") AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
										'params' => array(
											':customer_id' => $customer->id,
										),
									));
								}
								
								//temporary code to remove pacific leads after call schedule => 9am - 5pm  local time
								if( time() >= strtotime('today 5:00 pm') )
								{
									echo '<br><br>';
									echo '<b>Removed pacific leads</b>';
									echo '<br><br>';
									
									LeadHopper::model()->deleteAll(array(
										'condition' => 'customer_id = :customer_id AND lead_timezone IN ("PST", "PDT") AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
										'params' => array(
											':customer_id' => $customer->id,
										),
									));
								}
							}
							else
							{
								echo '<br><br>';
								echo '<b>Removed all indigo leads</b>';
								echo '<br><br>';
								
								if( time() < strtotime('today 8:00 AM') ) 
								{
									$nextAvailableCallingTime = '8:00 AM - 6:00 PM';
								}
								else
								{
									$nextAvailableCallingTime = 'Next Shift';
								}
							}
						}
						*/
						
						
						//2607 - Inside Sales
						if( $customer->id == 2607 )
						{
							date_default_timezone_set('America/Denver');
							
							if( time() >= strtotime('today 7:00 am') && time() < strtotime('today 6:00 pm') )
							{
								$nextAvailableCallingTime = 'Now';
								
								//temporary code to remove eastern leads after call schedule => 9am - 5pm  local time
								if( time() >= strtotime('today 3:00 pm') )
								{
									echo '<br><br>';
									echo '<b>Removed eastern leads</b>';
									echo '<br><br>';
									
									LeadHopper::model()->deleteAll(array(
										'condition' => 'customer_id = :customer_id AND lead_timezone IN ("EST", "EDT") AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
										'params' => array(
											':customer_id' => $customer->id,
										),
									));
								}
								
								//temporary code to remove central leads after call schedule => 9am - 5pm  local time
								if( time() >= strtotime('today 4:00 pm') )
								{
									echo '<br><br>';
									echo '<b>Removed central leads</b>';
									echo '<br><br>';
									
									LeadHopper::model()->deleteAll(array(
										'condition' => 'customer_id = :customer_id AND lead_timezone IN ("CST", "CDT") AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
										'params' => array(
											':customer_id' => $customer->id,
										),
									));
								}
											
								//temporary code to remove mountain leads after call schedule => 9am - 5pm  local time
								if( time() >= strtotime('today 5:00 pm') )
								{
									echo '<br><br>';
									echo '<b>Removed mountain leads</b>';
									echo '<br><br>';
									
									LeadHopper::model()->deleteAll(array(
										'condition' => 'customer_id = :customer_id AND lead_timezone IN ("MST", "MDT") AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
										'params' => array(
											':customer_id' => $customer->id,
										),
									));
								}
								
								//temporary code to remove pacific leads after call schedule => 9am - 5pm  local time
								if( time() >= strtotime('today 6:00 pm') )
								{
									echo '<br><br>';
									echo '<b>Removed pacific leads</b>';
									echo '<br><br>';
									
									LeadHopper::model()->deleteAll(array(
										'condition' => 'customer_id = :customer_id AND lead_timezone IN ("PST", "PDT") AND status IN ("READY", "DISPO", "DONE") AND type NOT IN (2,3,5,6,7)',
										'params' => array(
											':customer_id' => $customer->id,
										),
									));
								}
							}
							else
							{
								echo '<br><br>';
								echo '<b>Removed all inside sales leads</b>';
								echo '<br><br>';
								
								if( time() < strtotime('today 7:00 AM') ) 
								{
									$nextAvailableCallingTime = '7:00 AM - 6:00 PM';
								}
								else
								{
									$nextAvailableCallingTime = 'Next Shift';
								}
							}
						}
					}
					else 
					{
						$nextAvailableCallingTimeStart = new DateTime($sched['schedule_start'], new DateTimeZone($timeZone) );
						$nextAvailableCallingTimeEnd = new DateTime($sched['schedule_end'], new DateTimeZone($timeZone) );
						
						$nextAvailableCallingTimeStart->setTimezone(new DateTimeZone('America/Denver'));
						$nextAvailableCallingTimeEnd->setTimezone(new DateTimeZone('America/Denver'));

						$nextAvailableCallingTime = $nextAvailableCallingTimeStart->format('g:i A');
						
						// $currentDateTime = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('America/Denver'));
						$leadLocalTime = $currentDateTime->setTimezone(new DateTimeZone($timeZone));
					
						if( strtotime($leadLocalTime->format('g:i A')) >= strtotime($sched['schedule_start']) && strtotime($leadLocalTime->format('g:i A')) <= strtotime($sched['schedule_end']) )
						{
							$nextAvailableCallingTime = 'Now';
						}
						
						if( strtotime($leadLocalTime->format('g:i A')) >= strtotime($sched['schedule_start']) && strtotime($leadLocalTime->format('g:i A')) >= strtotime($sched['schedule_end']) )
						{
							$nextAvailableCallingTime = 'Next Shift';
						}
						
						//exclude these customers on the forced call schedule
						//63 - Engagex Email Collection
						//1029 - Alaska Sales Region
						//1031 - Washington Sales Region
						//1032 - Denver Sales Region
						//1036 - Las Vegas Sales Region
						//1035 - Phoenix/Mesa Sales Region
						//2129 - Sandia
						if( !in_array($customer->id, array(63, 1029, 1031, 1032, 1036, 1035, 2129)) )
						{
							if( in_array($this->get_timezone_abbreviation($timeZone), array('EST', 'EDT')) )
							{
								if( time() >= strtotime('today 9:00 AM') && time() < strtotime('today 5:00 PM') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									$nextAvailableCallingTime = '9:00 AM - 5:00 PM';
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('CST', 'CDT')) )
							{
								if( time() >= strtotime('today 10:00 AM') && time() < strtotime('today 6:00 PM') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									$nextAvailableCallingTime = '10:00 AM - 6:00 PM';
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('MST', 'MDT')) )
							{
								if( time() >= strtotime('today 10:00 AM') && time() < strtotime('today 7:00 PM') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									$nextAvailableCallingTime = '10:00 AM - 7:00 PM';
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('PST', 'PDT')) )
							{
								if( time() >= strtotime('today 11:00 AM') && time() < strtotime('today 8:00 PM') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									$nextAvailableCallingTime = '11:00 AM - 8:00 PM';
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('HAST', 'HADT')) )
							{
								if( time() >= strtotime('today 3:00 pm') && time() < strtotime('today 10:00 PM') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									$nextAvailableCallingTime = '3:00 PM - 10:00 PM';
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('AKST', 'AKDT')) )
							{
								if( time() >= strtotime('today 1:00 pm') && time() < strtotime('today 8:00 PM') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									$nextAvailableCallingTime = '1:00 PM - 8:00 PM';
								}
							}
						}
						
						//modified customer call schedules
						// $modifiedCustomerArray = array(522, 408, 1512, 1499, 1493, 1538, 1505, 1474, 490, 1370, 1627, 1598, 521, 1498, 1155, 1548, 1612, 172, 1481, 1260, 1503, 869, 1420, 1546, 1181);
						// $modifiedCustomerArray = array(522, 1493, 1505, 1598, 1646);
						$modifiedCustomerArray = array(522, 1966);
						
						if( in_array($customer->id, $modifiedCustomerArray) )
						{
							if( in_array($this->get_timezone_abbreviation($timeZone), array('EST', 'EDT')) )
							{
								if( time() >= strtotime('today 8:00 am') && time() < strtotime('today 5:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 8:00 AM') ) 
									{
										$nextAvailableCallingTime = '8:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('CST', 'CDT')) )
							{
								if( time() >= strtotime('today 9:00 am') && time() < strtotime('today 6:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 9:00 AM') ) 
									{
										$nextAvailableCallingTime = '9:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('PST', 'PDT')) )
							{
								if( time() >= strtotime('today 11:00 am') && time() < strtotime('today 8:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 11:00 AM') ) 
									{
										$nextAvailableCallingTime = '11:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('MST', 'MDT')) )
							{
								if( time() >= strtotime('today 10:00 am') && time() < strtotime('today 7:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 10:00 AM') ) 
									{
										$nextAvailableCallingTime = '10:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
						}
					
					
						//49 -Jory Bowers
						//56 - Valerie Strickland
						if( in_array($customer->id, array(49, 56)) )
						{
							if( in_array($this->get_timezone_abbreviation($timeZone), array('EST', 'EDT')) )
							{
								if( time() >= strtotime('today 7:00 am') && time() < strtotime('today 3:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 7:00 AM') ) 
									{
										$nextAvailableCallingTime = '7:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('CST', 'CDT')) )
							{
								if( time() >= strtotime('today 8:00 am') && time() < strtotime('today 4:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 8:00 AM') ) 
									{
										$nextAvailableCallingTime = '8:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('MST', 'MDT')) )
							{
								if( time() >= strtotime('today 9:00 am') && time() < strtotime('today 5:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 9:00 AM') ) 
									{
										$nextAvailableCallingTime = '9:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('PST', 'PDT')) )
							{
								if( time() >= strtotime('today 10:00 am') && time() < strtotime('today 6:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 10:00 AM') ) 
									{
										$nextAvailableCallingTime = '10:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
						}
						
						//2011 - Indigo Sky
						if( $customer->id == 2011 )
						{
							date_default_timezone_set('America/Denver');
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('EST', 'EDT')) )
							{
								if( time() >= strtotime('today 8:00 am') && time() < strtotime('today 5:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 8:00 AM') ) 
									{
										$nextAvailableCallingTime = '8:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('CST', 'CDT')) )
							{
								if( time() >= strtotime('today 9:00 am') && time() < strtotime('today 6:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 9:00 AM') ) 
									{
										$nextAvailableCallingTime = '9:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('MST', 'MDT')) )
							{
								if( time() >= strtotime('today 10:00 am') && time() < strtotime('today 7:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 10:00 AM') ) 
									{
										$nextAvailableCallingTime = '10:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('PST', 'PDT')) )
							{
								if( time() >= strtotime('today 11:00 am') && time() < strtotime('today 8:00 pm') )
								{ 
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 11:00 AM') ) 
									{
										$nextAvailableCallingTime = '11:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
						}
					
						//2363 - Station
						/*
						if( $customer->id == 2363 )
						{
							date_default_timezone_set('America/Denver');
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('EST', 'EDT')) )
							{
								if( time() >= strtotime('today 8:00 am') && time() < strtotime('today 2:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 8:00 AM') ) 
									{
										$nextAvailableCallingTime = '8:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('CST', 'CDT')) )
							{
								if( time() >= strtotime('today 9:00 am') && time() < strtotime('today 3:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 9:00 AM') ) 
									{
										$nextAvailableCallingTime = '9:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('MST', 'MDT')) )
							{
								if( time() >= strtotime('today 10:00 am') && time() < strtotime('today 4:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 10:00 AM') ) 
									{
										$nextAvailableCallingTime = '10:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('PST', 'PDT')) )
							{
								if( time() >= strtotime('today 11:00 am') && time() < strtotime('today 5:00 pm') )
								{ 
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 11:00 AM') ) 
									{
										$nextAvailableCallingTime = '11:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
						}
						*/
						
						//2607 - Inside Sales
						if( $customer->id == 2607 )
						{
							date_default_timezone_set('America/Denver');
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('EST', 'EDT')) )
							{
								if( time() >= strtotime('today 7:00 am') && time() < strtotime('today 3:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 7:00 AM') ) 
									{
										$nextAvailableCallingTime = '7:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('CST', 'CDT')) )
							{
								if( time() >= strtotime('today 8:00 am') && time() < strtotime('today 4:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 8:00 AM') ) 
									{
										$nextAvailableCallingTime = '8:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('MST', 'MDT')) )
							{
								if( time() >= strtotime('today 9:00 am') && time() < strtotime('today 5:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 9:00 AM') ) 
									{
										$nextAvailableCallingTime = '9:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('PST', 'PDT')) )
							{
								if( time() >= strtotime('today 10:00 am') && time() < strtotime('today 6:00 pm') )
								{ 
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 10:00 AM') ) 
									{
										$nextAvailableCallingTime = '10:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
						}
					}
				}
			}
		}
		else
		{
			$nextAvailableCallingTime = 'Next Shift';
		}

		
		echo 'nextAvailableCallingTime: ' . $nextAvailableCallingTime;
	}

	public function actionRecycleLeadsPatch()
	{
		// exit;
		
		$patchOngoing = CustomerQueueViewerSettings::model()->findByPk(15);
		$patchCurrentOffset = CustomerQueueViewerSettings::model()->findByPk(16);
		
		$ctr = 1;
		
		// $totalRecycleLeadsCount = Lead::model()->count(array(
			// 'condition' => 'type=1 AND status=3',
		// ));
		
		$leads = Lead::model()->findAll(array(
			'condition' => '
				type = 1 
				AND status = 3 
				AND customer_id = 144
				AND recycle_date IS NULL
			',
			// 'limit' => 20000,
			// 'offset' => $patchCurrentOffset->value,
		));
		
		echo 'count: ' . count($leads);
		echo '<br>';
		// echo 'totalRecycleLeadsCount: ' . $totalRecycleLeadsCount;
		
		echo '<br><br>';
		// exit;
		
		// if( $patchOngoing->value == 0 && $patchCurrentOffset->value <= 454182 )
		// {
			// $patchOngoing->value = 1;
			// $patchOngoing->save(false);
			
			if( $leads )
			{
				foreach( $leads as $lead )
				{
					$leadCallHistory = LeadCallHistory::model()->find(array(
						'with' => array('skillDisposition', 'skillChildDisposition'),
						'condition' => '
							t.lead_id = :lead_id 
							AND ( 
								skillDisposition.is_complete_leads = 1 
								OR skillChildDisposition.is_complete_leads = 1
							)
							AND( 
								skillDisposition.is_do_not_call = 1 
								OR skillChildDisposition.is_do_not_call = 1
							)
						',
						'params' => array(
							':lead_id' => $lead->id,
						),
						'order' => 't.date_created DESC'
					));
					
					if( $leadCallHistory )
					{
						if( $leadCallHistory->is_skill_child == 0 )
						{
							$disposition = $leadCallHistory->skillDisposition;
						}
						else
						{
							$disposition = $leadCallHistory->skillChildDisposition;
						}
						
						if( $disposition && $disposition->is_complete_leads == 1 )
						{						
							//recyle module
							
							if( $disposition->is_do_not_call == 0 )
							{
								if( !empty($disposition->recycle_interval) )
								{
									$time = strtotime(date("Y-m-d", strtotime($leadCallHistory->date_created)));
									$finalDate = date("Y-m-d", strtotime("+".($disposition->recycle_interval * 30)." day", $time));
									$lead->recycle_date = $finalDate;
									$lead->recycle_lead_call_history_id = $leadCallHistory->id;
									$lead->recycle_lead_call_history_disposition_id = $leadCallHistory->disposition_id;
									
									if( $lead->save(false) )
									{
										if( $leadCallHistory->is_skill_child == 0 )
										{
											echo 'skillDisposition: ' . $disposition->skill_disposition_name;
										}
										else
										{
											echo 'skillChildDisposition: ' . $disposition->skill_child_disposition_name;
										}
										
										echo '<br>';
										echo 'lead->recycle_date: ' . $lead->recycle_date;
										echo '<br>';
										
										$ctr++;
									}
								}
								// else
								// {
									// echo '<pre>';
										// print_r($leadCallHistory->attributes);
										// print_r($disposition->attributes);
									// echo '</pre>';
									
									// echo '<br>';
									// exit;
								// }
							}
							else
							{
								$lead->is_do_not_call = 1;
								
								$lead->recycle_date = null;
								$lead->recycle_lead_call_history_id = null;
								$lead->recycle_lead_call_history_disposition_id = null;
								
								if( $lead->save(false) )
								{
									$existingDnc = Dnc::model()->find(array(
										'condition' => 'phone_number = :phone_number',
										'params' => array(
											':phone_number' => $leadCallHistory->lead_phone_number,
										),
									));
									
									if( empty($existingDnc) )
									{
										$newDnc = new Dnc;
										
										$newDnc->setAttributes(array(
											'lead_id' => $leadCallHistory->lead_id,
											'phone_number' => $leadCallHistory->lead_phone_number,
											'date_created' => date('Y-m-d H:i:s'),
										));
										
										$newDnc->save(false);
									}
									
									$ctr++;
								}
							}
							
							// echo '<pre>';
								// print_r($lead->attributes);
							// echo '</pre>';
							
							// exit;
						}
					}
					else
					{
						echo 'Lead ID: '.$lead->id.' | List Name: '.$lead->list->name.' | no completed dispo call history found.';
					}
					
					echo '<br>';
					echo '<hr>';
					echo '<br>';

					// $patchCurrentOffset->value = $patchCurrentOffset->value + 1;
					// $patchCurrentOffset->save(false);
				}
			}
			
			// $patchOngoing->value = 0;
			// $patchOngoing->save(false);
		// }
		
		echo '<br><br>ctr: ' . $ctr;
	}

	public function actionSalesManagementPatch()
	{
		$models = CustomerEnrollment::model()->findAll(array(
			'condition' => 'sales_management_deleted=1',
		));
		
		$ctr = 0;
		
		if( $models )
		{
			foreach( $models as $model )
			{
				$model->account->sales_management_deleted = 1;
				
				if( $model->account->save(false) )
				{
					echo $ctr++;
					echo '<br>';
				}
			}
		}
		
		echo 'ctr: ' . $ctr;
	}

	public function actionRemoveList()
	{
		exit;
		
		$models = Lead::model()->findAll(array(
			'condition' => 'list_id = 4151 AND status=1 AND number_of_dials=0',
			'limit' => 101
		));
		
		echo 'count: ' . count($models);
		
		echo '<br><br>';
		
		if( $models )
		{
			foreach( $models as $model )
			{
				$model->list_id = null;
				$model->save(false);
			}
		}
	}

	
	public function actionCheckCustomerTimezone()
	{
		// $condition = '
			// status = 1 
			// AND next_available_calling_time NOT IN ("Goal Appointment Reached", "On Hold", "Cancelled", "Removed", "Decline Hold")
			// AND company IN ("State Farm", "Farmers", "Allstate", "American Family", "Independent Insurance")
		// ';
		
		$condition = '
			status = 2 
			AND next_available_calling_time IN ("Removed") 
			AND company IN ("State Farm", "Farmers", "Allstate", "American Family", "Independent Insurance")
		';
		
		$models = CustomerQueueViewer::model()->findAll(array(
			'condition' => $condition,
			// 'limit' => 50,
			// 'offset' => $customerPriorityCurrentOffset->value, 
			'order' => 'priority DESC'
		));
		
		echo 'count: ' . count($models);
		
		echo '<br>';
		
		if( $models )
		{
			foreach( $models as $model )
			{
				$remainingCallableCount = Lead::model()->count(array(
					'with' => array('list', 'list.skill'),
					'together' => true,
					'condition' => '
						list.customer_id = :customer_id AND list.status = 1 
						AND t.type=1 and t.status=1 AND t.number_of_dials < (skill.max_dials * 3) 
						AND (recertify_date != "0000-00-00" AND recertify_date IS NOT NULL 
						AND NOW() <= recertify_date)
						AND skill.id = :skill_id
					',
					'params' => array(
						':customer_id' => $model->customer_id,
						':skill_id' => $model->skill_id,
					),
				));
				
				$summaryArray[$model->customer->getTimeZone()]['customer_count'] += 1;
				$summaryArray[$model->customer->getTimeZone()]['callable_lead_count'] += $remainingCallableCount;
			}
		}
		
		echo '<br><br>';
		
		echo '<pre>';
			print_r($summaryArray);
		echo '</pre>';
	}

	public function actionMoveJunkLeadToActiveList()
	{
		exit;
		
		$dbUpdates = 0;
		
		$list = Lists::model()->findByPk(4653);
		
		$models = LeadJunk::model()->findAll(array(
			'condition' => 'list_id = :list_id AND is_duplicate=1',
			'params' => array(
				':list_id' => $list->id,
			),
		));
		
		echo 'count: ' . count($models);
		
		echo '<br><br>';
		
		if( $models )
		{
			foreach( $models as $leadJunk )
			{
				$newLead = new Lead;

				$newLead->attributes = $leadJunk->attributes;
				$newLead->recertify_date = $this->_computeForSkillMaxLeadLifeBeforeRecertify($list);	
				
				if( $newLead->save(false) )
				{
					$dbUpdates++;
					
					$leadJunk->status = 3;
					$leadJunk->save(false);
				}
			}
		}
		
		echo '<br><br>dbUpdates: ' . $dbUpdates;
	}

	public function actionCallerIdEmail()
	{
		exit;
		
		$customer = Customer::model()->findByPk(1514);
		
		$areaCode = preg_replace("/[^0-9]/","", $customer->phone);
		$areaCode = substr($areaCode, 0, 3);
		
		$MsgHTML = '<p>Customer Name: ' . $customer->firstname.' '.$customer->lastname.'</p>';
		$MsgHTML .= '<p>Customer ID: '.$customer->account_number.'</p>';
		$MsgHTML .= '<p>Company Name: '.$customer->company->company_name.'</p>';
		$MsgHTML .= '<p>Area Code: '.$areaCode.'</p>';

		Yii::import('application.extensions.phpmailer.JPhpMailer');

		$mail = new JPhpMailer(true);

		$mail->SetFrom('service@engagex.com');
		
		$mail->Subject = 'NO CALLER ID PHONE NUMBER ON FILE - ' . $areaCode;

		$mail->MsgHTML($MsgHTML);
		
		// $mail->AddAddress('customerservice@engagex.com');
		
		$mail->AddAddress('jim.campbell@engagex.com');
		$mail->AddBCC('erwin.datu@engagex.com');

		if( $mail->Send() )
		{
			echo 'sent';
		}
	}

	public function actionExportCallerId()
	{
		// unregister Yii's autoloader
		spl_autoload_unregister(array('YiiBase', 'autoload'));
		
		// register PHPExcel's autoloader ... PHPExcel.php will do it
		$phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
		require_once($phpExcelPath . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'PHPExcel.php');
		
		// register Yii's autoloader again
		spl_autoload_register(array('YiiBase', 'autoload'));
		 
		// This requires Yii's autoloader
		
		$objPHPExcel = new PHPExcel();
		
		$ctr = 1;

		$headers = array(
			'A' => 'Customer Name',
			'B' => 'Company Name',
			'C' => 'Office Area Code',
		);
		
		foreach($headers as $column => $val)
		{		
			$objPHPExcel->getActiveSheet()->SetCellValue($column.$ctr, $val);
			$objPHPExcel->getActiveSheet()->getStyle($column.$ctr)->applyFromArray(array(
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				),
				'font'  => array(
					'bold' => true,
					'name'  => 'Calibri',
				),
			));
		}
		
		$customerQueues = CustomerQueueViewer::model()->findAll(array(
			'condition' => 'company IN ("State Farm", "Farmers") AND next_available_calling_time != "Cancelled"'
		));
		
		// $customerQueues = CustomerQueueViewer::model()->findAll();
		
		// echo 'customerQueues: ' . count($customerQueues);
		
		// echo '<br><br>';
		
		if( $customerQueues )
		{
			$ctr = 2;
			
			foreach( $customerQueues as $customerQueue )
			{
				$callerID = '';
				
				$customerSkill = CustomerSkill::model()->find(array(
					'condition' => 'customer_id = :customer_id AND skill_id = :skill_id',
					'params' => array(
						':customer_id' => $customerQueue->customer_id,
						':skill_id' => $customerQueue->skill_id
					),
				));
						
				if( $customerSkill )
				{
					// if( $customerSkill->skill->caller_option == 1 )
					// {
						// $callerID = $customerSkill->customer->phone_number;
					// }
					// else
					// {
						// if( $customerSkill->skill_caller_option_customer_choice == 1 ) //office phone
						// {
							// if( !empty($customerSkill->customer->phone) )
							// {
								// $callerID = $customerSkill->customer->phone;
							// }
						// }
						// else
						// {		
							$companyDid = CompanyDid::model()->count(array(
								'condition' => 'LOWER(company_name) = :company_name AND area_code = :area_code',
								'params' => array(
									':company_name' => ($customerSkill->customer->company->company_name),
									':area_code' => substr($customerSkill->customer->phone, 1,3),
								),
							));
							
						
							if( $companyDid == 0 )
							{
								// echo 'Customer Name: ' . $customerQueue->customer_name;
								// echo '<br>';
								// echo 'Company Name: ' . $customerSkill->customer->company->company_name;
								// echo '<br>';
								// echo 'Office Phone: ' . $customerSkill->customer->phone;
								// echo '<br>';
								// echo 'Area code: ' . substr($customerSkill->customer->phone, 1,3);
								// echo '<br>';
								// echo 'companyDid found: ' . $companyDid; 
								// echo '<br>';
								// echo '<hr>';
								// echo '<br>';
								
								$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $customerQueue->customer_name);
								$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $customerSkill->customer->company->company_name);
								$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, substr($customerSkill->customer->phone, 1,3));
								
								$ctr++;
							}
						// }
					// }
				}
			}
		}
		
		// echo '<br><br>end..';
		// exit;
		
		date_default_timezone_set('America/Denver');
		$filename  = 'Customer no dialing as # on file';
		
		header('Content-Type: application/vnd.ms-excel'); 
		header('Content-Disposition: attachment; filename="'.$filename.'.xlsx"'); 
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	
		header('Cache-Control: max-age=0');
		
		$objWriter->save('php://output');
	}

	public function actionGetCustomerCallable()
	{
		exit;
		$ctr = 1;
		
		$customerQueues = CustomerQueueViewer::model()->findAll(array(
			// 'condition' => 'customer_id=797',
		));
		
		echo 'count: ' . count($customerQueues);
		
		echo '<br><br>';
		
		if( $customerQueues )
		{
			foreach( $customerQueues as $customerQueue )
			{
				$remainingCallableCount = Lead::model()->count(array(
					'with' => array('list', 'list.skill'),
					'together' => true,
					'condition' => '
						list.customer_id = :customer_id AND list.status = 1 
						AND t.type=1 and t.status=1 AND t.number_of_dials < (skill.max_dials * 3) 
						AND (recertify_date != "0000-00-00" AND recertify_date IS NOT NULL 
						AND NOW() <= recertify_date)
						AND skill.id = :skill_id
					',
					'params' => array(
						':customer_id' => $customerQueue->customer_id,
						':skill_id' => $customerQueue->skill_id,
					),
				));

				$existingModel = CustomerCallableLeadCount::model()->find(array(
					'condition' => 'customer_id = :customer_id AND YEAR(date_created) = :year AND MONTH(date_created) = :month',
					'params' => array(
						':customer_id' => $customerQueue->customer_id,
						':year' => date('Y'),
						':month' => date('m')
					),
				));
				
				if( $existingModel )
				{
					$model = $existingModel;
					$model->customer_id = $customerQueue->customer_id;
					$model->callable_leads = $model->callable_leads + $remainingCallableCount;
				}
				else
				{
					$model = new CustomerCallableLeadCount;
					$model->customer_id = $customerQueue->customer_id;
					$model->callable_leads = $remainingCallableCount;
				}
			
				if( $model->save(false) )
				{
					echo $ctr++;
					echo '<br>';
					echo 'customer_id: ' . $customerQueue->customer_id; 
					echo '<br>';
					echo 'remainingCallableCount: ' . $remainingCallableCount; 
					echo '<br>';
					echo '<br>';
				}
			}
		}
		
		echo '<br><br>ctr: ' . $ctr;
		echo '<br><br>end..';
	}

	public function actionDeleteNamesWaiting()
	{
		exit;
		$models = Lead::model()->findAll(array(
			'together' => true,
			'condition' => 't.customer_id = :customer_id AND t.list_id IS NULL AND t.type=1 AND t.status !=4',
			'params' => array(
				':customer_id' => 1647,
			),
		));
		
		// $models = Lead::model()->findAll(array(
			// 'together' => true,
			// 'condition' => 't.list_id IS NULL AND t.type=1 AND t.status !=4 AND t.date_created < "2016-10-01 23:59:59"',
			// 'params' => array(
			// ),
		// ));
		
		echo 'models: ' . count($models);
		
		echo '<br>';
		
		if( $models )
		{
			foreach( $models as $model )
			{
				$model->delete();
			}
		}
		echo '<br>end...';
	}

	public function actionHolidayPatch()
	{
		exit;
		
		$ctr = 0 ;
		
		$calendars = CalendarHoliday::model()->findAll(array(
			'group' => 'calendar_id'
		));
		
		echo 'calendars: ' . count($calendars);
		
		echo '<br><br>';
		
		if( $calendars )
		{
			foreach( $calendars as $calendar )
			{
				$holidays = new US_Federal_Holidays;
				$holidayArray = $holidays->get_list();
				
				foreach( $holidayArray as $holiday )
				{
					$existingHoliday = CalendarHoliday::model()->find(array(
						'condition' => 'calendar_id = :calendar_id AND name = :name AND date = :date',
						'params' => array(
							':calendar_id' => $calendar->calendar_id,
							':name' => strtoupper($holiday['name']),
							':date' => date('Y-m-d', $holiday['timestamp']),
						),
					));
					
					if( empty($existingHoliday) )
					{
						$newHoliday = new CalendarHoliday;
						
						$newHoliday->setAttributes(array(
							'calendar_id' => $calendar->calendar_id,
							'name' => strtoupper($holiday['name']),
							'date' => date('Y-m-d', $holiday['timestamp']),
						));
						
						$newHoliday->save(false);
					}

					echo $ctr++;
					echo '<br>';
				}
			}
		}
		
		// $calendars = CalendarHoliday::model()->findAll(array(
			// 'condition' => 'name="PRESIDENTS DAY" AND date="2017-02-19"'
		// ));
		
		// echo 'calendars: ' . count($calendars);
		
		// echo '<br><br>';
		
		// if( $calendars )
		// {
			// foreach( $calendars as $calendar )
			// {
				// $calendar->date = '2017-02-20';
				
				// if( $calendar->save(false) )
				// {
					// echo $ctr++;
					// echo '<br>';
				// }
			// }
		// }
	}

	public function actionTransferListSkill()
	{
		exit;
		
		$ctr = 0;
		
		$customerSkills = CustomerSkill::model()->findAll(array(
			'with' => array('skill', 'contract'),
			'condition' => 't.status=1 AND skill.id IS NOT NULL AND contract.id IS NOT NULL AND t.skill_id IN(33,34)',
		)); 
		
		if( $customerSkills )
		{
			foreach( $customerSkills as $customerSkill )
			{
				$lists = Lists::model()->findAll(array(
					'condition' => 'customer_id = :customer_id',
					'params' => array(
						':customer_id' => $customerSkill->customer_id
					),
				));
				
				if( $lists )
				{
					foreach( $lists as $list )
					{
						if( $list->skill_id == 11 )
						{
							$list->skill_id = 33;
						}
						
						if( $list->skill_id == 12 )
						{
							$list->skill_id = 34;
						}
						
						if( $list->save(false) ) 
						{
							echo $ctr;
							echo '<br>';
						}
					}
				}
			}
		}
	}

	public function actionEndSkill()
	{
		$ctr = 0;
		
		// $customerSkills = CustomerSkill::model()->findAll(array(
			// 'with' => array('skill', 'contract'),
			// 'condition' => 'skill.id IS NOT NULL AND contract.id IS NOT NULL AND t.skill_id IN(11,12)',
		// )); 
		
		// if( $customerSkills )
		// {
			// foreach( $customerSkills as $customerSkill )
			// {
				// $customerSkill->end_month = '2016-12-31';
				
				// if( $customerSkill->save(false) )
				// {
					$customerCredits = CustomerCredit::model()->findAll(array(
						'condition' => 'contract_id IN (4,7) AND status=1',
					));
					
					if( $customerCredits )
					{
						foreach( $customerCredits as $customerCredit )
						{
							if( $customerCredit->contract_id == 4 || $customerCredit->contract_id == 34 )
							{
								$customerCredit->contract_id = 51;
							}
							
							if( $customerCredit->contract_id == 7 || $customerCredit->contract_id == 33 )
							{
								$customerCredit->contract_id = 52;
							}
							
							if( $customerCredit->save(false) )
							{
								echo $ctr++;
								echo '<br>';
							}
						}
					}
					
					// echo $ctr++;
					// echo '<br>';
				// }
			// }
		// }
		
		echo '<br><br>end..';
	}

	public function actionBillingCreditDescription()
	{
		$models = CustomerBilling::model()->findAll(array(
			'condition' => '',
		));
		
		echo 'count: ' . count($models);
		
		echo '<br><br>';
		
		if( $models )
		{
			foreach( $models as $model )
			{
				
			}
		}
	}

	public function actionInactiveCustomers()
	{
		$ctr = 0;
		
		$customerSkills = CustomerSkill::model()->findAll(array(
			'with' => array('skill', 'contract', 'customer'),
			'condition' => 'skill.id IS NOT NULL AND contract.id IS NOT NULL AND t.skill_id IN(11,12) AND t.status=1 AND customer.company_id="13"',
		)); 
		
		if( $customerSkills )
		{
			foreach( $customerSkills as $customerSkill )
			{
				$newCustomerSkill = CustomerSkill::model()->findAll(array(
					'with' => array('skill', 'contract'),
					'condition' => 'customer_id = :customer_id AND skill.id IS NOT NULL AND contract.id IS NOT NULL AND t.skill_id IN(33,34) AND t.status=1',
					'params' => array(
						':customer_id' => $customerSkill->customer_id
					),
				));
				
				if( empty($newCustomerSkill) )
				{
					echo $customerSkill->customer->firstname.' '.$customerSkill->customer->lastname;
					
					$customerSkill->customer->status = 2;
					
					if( $customerSkill->customer->save(false) )
					{
						echo $ctr++;
					}
					
					echo '<br>';
				}
			}
		}

		echo 'ctr: ' . $ctr; 
	}

	public function actionCreditsNoContract()
	{
		$ctr = 0;
		
		$models = CustomerCredit::model()->findAll(array(
			'condition' => 'status=1',
		));
		
		echo 'models: ' . count($models);
		
		echo '<br>';
		echo '<br>';
		
		if( $models )
		{
			foreach( $models as $model )
			{
				$creditStartDate = date('Y-'.$model->start_month.'-1');
						
				if( $model->type == 2 ) //month range
				{
					$creditEndDate = date('Y-'.$model->end_month.'-t');
					
					if( $model->start_month >= $model->end_month )
					{
						$creditEndDate = date('Y-m-d', strtotime('+1 year', strtotime($creditEndDate)));
					}
				}
				else
				{
					$creditEndDate = date('Y-'.$model->start_month.'-t');
				}
				
				$contracts = CustomerSkill::getCustomerContracts($model->customer_id);
				
				if( !in_array($model->contract_id, $contracts) && $model->customer->status == 1 && $model->customer->company_id == 13  && strtotime($creditStartDate) >= strtotime('2017-01-01') )
				{
					echo $model->description.' - ' . $model->customer->firstname.' '.$model->customer->lastname;
					echo '<br>';
					
					$ctr++;
				}
			}
		}
		
		echo '<br><br>ctr: ' . $ctr;
	}

	public function actionGetCustomerPaymentMethod()
	{
		$paymentMethod = CustomerBilling::model()->getDefaultMethod(1576);
		
		echo '<pre>';
			print_r($paymentMethod);
		echo '</pre>';
	}

	public function actionQueueTest()
	{
		// $condition = 'status = 1 AND available_leads > 0 AND priority = 1.00 AND next_available_calling_time NOT IN ("Goal Appointment Reached", "On Hold", "Cancelled", "Removed", "Decline Hold")';
		// $condition = 'status = 1 AND available_leads > 0';
		// $condition = '';
		
		// $priorityBracketCount = CustomerQueueViewer::model()->count(array(
			// 'condition' => $condition,
		// ));
		
		// if( $priorityBracketCount < 200 )
		// {
			// $condition = 'status = 1 AND available_leads > 0 AND priority >= 0.75 AND priority <= 1 AND next_available_calling_time NOT IN ("Goal Appointment Reached", "On Hold", "Cancelled", "Removed", "Decline Hold")';
		
			// $priorityBracketCount = CustomerQueueViewer::model()->count(array(
				// 'condition' => $condition,
			// ));
			
			// if( $priorityBracketCount < 300 )
			// {
				// $condition = 'status = 1 AND available_leads > 0 AND priority >= 0.50 AND priority <= 1 AND next_available_calling_time NOT IN ("Goal Appointment Reached", "On Hold", "Cancelled", "Removed", "Decline Hold")';
			
				// $priorityBracketCount = CustomerQueueViewer::model()->count(array(
					// 'condition' => $condition,
				// ));
				
				// if( $priorityBracketCount < 300 )
				// {
					// $condition = 'status = 1 AND available_leads > 0 AND priority >= 0.25 AND priority <= 1 AND next_available_calling_time NOT IN ("Goal Appointment Reached", "On Hold", "Cancelled", "Removed", "Decline Hold")';
				
					// $priorityBracketCount = CustomerQueueViewer::model()->count(array(
						// 'condition' => $condition,
					// ));
					 
					// if( $priorityBracketCount < 300 )
					// {
						// $condition = 'status = 1 AND available_leads > 0 AND priority >= 0 AND priority <= 1 AND next_available_calling_time NOT IN ("Goal Appointment Reached", "On Hold", "Cancelled", "Removed", "Decline Hold")';
					
						// $priorityBracketCount = CustomerQueueViewer::model()->count(array(
							// 'condition' => $condition,
						// ));
					// }
				// }
			// }
		// }
		
		//temporary override for mountain and daniel woods
		// $condition .= ' OR customer_id IN (1021, 981, 1038, 1036, 1035, 1030, 1031, 875, 1049)';
		// $condition .= 'skill_id IN (24)';
		$condition .= 'customer_id IN (522, 408, 1512, 1499, 1493, 1538, 1505, 1474, 490, 1370, 1627, 1598, 521, 1498, 1155, 1548, 1612, 172, 1481, 1260, 1503, 869, 1420, 1546, 1181)';
		
		// $condition = 'status = 1 AND available_leads > 0 AND next_available_calling_time NOT IN ("Goal Appointment Reached", "On Hold", "Cancelled", "Removed")';
		
		$customerQueues = CustomerQueueViewer::model()->findAll(array(
			'condition' => $condition,
			// 'limit' => 50,
			// 'offset' => $customerPriorityCurrentOffset->value, 
			'order' => 'priority DESC'
		));
		
		foreach( $customerQueues as $customerQueue )
		{
			$customerSkill = CustomerSkill::model()->find(array(
				'condition' => 'customer_id = :customer_id AND skill_id = :skill_id AND status=1',
				'params' => array(
					':customer_id' => $customerQueue->customer_id,
					':skill_id' => $customerQueue->skill_id,
				),
			));
			
			if( $customerSkill )
			{
				echo  $customerQueue->customer_id.': ' . $customerQueue->customer_name;
				echo '<br>';
				echo 'next available calling time: ' . $this->checkTimeZoneTest($customerSkill);
				echo '<br>';
				echo '<br>';
			}
		}
	}
	
	private function checkTimeZoneTest($customerSkill, $type='customer', $lead=null)
	{
		date_default_timezone_set('America/Denver');
		
		$nextAvailableCallingTime = '';
		
		$customer = $customerSkill->customer;
		
		$skillScheduleHolder = array();
			
		$currentDateTime = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('America/Denver'));
		// $currentDateTime->setTimezone(new DateTimeZone('America/Denver')); 
		
		
		//temp code to force certain customers to get no dials 
		// $floridaAreaCodes = array('239', '305', '321', '352', '386', '407', '561', '727', '754', '772', '786', '813', '850', '863', '904', '941', '954');
			
		// $georgiaArecodeCodes = array('229', '404', '470', '478', '678', '706', '762', '770', '912');
		
		// $southCarolinaAreaCodes = array('803', '843', '864');
		
		// if( in_array(substr($customer->phone, 1, 3), $floridaAreaCodes) )
		// {
			// return 'Next Shift';
		// }
		
		// if( in_array(substr($customer->phone, 1, 3), $georgiaArecodeCodes) )
		// {
			// return 'Next Shift';
		// }
		
		// if( in_array(substr($customer->phone, 1, 3), $southCarolinaAreaCodes) )
		// {
			// return 'Next Shift';
		// }
		//end of temp code
		
		
		if( $customerSkill->is_custom_call_schedule == 1 )
		{
			$customCallSchedules = CustomerSkillSchedule::model()->findAll(array(
				'condition' => 'customer_skill_id = :customer_skill_id AND schedule_day = :schedule_day',
				'params' => array(
					':customer_skill_id' => $customerSkill->id,
					':schedule_day' => date('N'),
				),
			));
			
			if( $customCallSchedules )
			{
				foreach( $customCallSchedules as $customCallSchedule )
				{
					$skillScheduleHolder[$customer->id][$customCallSchedule->id]['schedule_start'] = date('g:i A', strtotime($customCallSchedule->schedule_start));
					$skillScheduleHolder[$customer->id][$customCallSchedule->id]['schedule_end'] = date('g:i A', strtotime($customCallSchedule->schedule_end));
				}
			}
		}
		else
		{	
			$skillSchedules = SkillSchedule::model()->findAll(array(
				// 'condition' => 'skill_id = :skill_id AND schedule_day = :schedule_day AND status=1 AND is_deleted=0',
				'condition' => 'skill_id = :skill_id AND schedule_day = :schedule_day',
				'params' => array(
					'skill_id' => $customerSkill->skill_id,
					':schedule_day' => date('N'),
				),
			));

			foreach($skillSchedules as $skillSchedule)
			{
				$skillScheduleHolder[$customer->id][$skillSchedule->id]['schedule_start'] = date('g:i A', strtotime($skillSchedule->schedule_start));
				$skillScheduleHolder[$customer->id][$skillSchedule->id]['schedule_end'] = date('g:i A', strtotime($skillSchedule->schedule_end));
			}
		}
	
		
		if( isset($skillScheduleHolder[$customer->id]) )
		{	
			foreach($skillScheduleHolder[$customer->id] as $sched)
			{	
				if( $type == 'customer' )
				{
					$timeZone = $customer->getTimeZone();
				}
				else
				{
					if( !empty($lead->timezone) )
					{
						$timeZone = $lead->timezone;
					}
					else
					{
						$timeZone = $customer->getTimeZone();
					}
				}
				
				if( !empty($timeZone) )
				{
					$timeZone = timezone_name_from_abbr($timeZone);
													
					// if( strtoupper($lead->timezone) == 'AST' )
					// {
						// $timeZone = 'America/Puerto_Rico';
					// }
					
					// if( strtoupper($lead->timezone) == 'ADT' )
					// {
						// $timeZone = 'America/Halifax';
					// }
					
					if( $type == 'customer' )
					{
						$nextAvailableCallingTimeStart = new DateTime($sched['schedule_start'], new DateTimeZone($timeZone) );
						$nextAvailableCallingTimeEnd = new DateTime($sched['schedule_end'], new DateTimeZone($timeZone) );
						
						$nextAvailableCallingTimeStart->setTimezone(new DateTimeZone('America/Denver'));
						$nextAvailableCallingTimeEnd->setTimezone(new DateTimeZone('America/Denver'));
						
						$nextAvailableCallingTime = $nextAvailableCallingTimeStart->format('g:i A');

						if( strtotime($currentDateTime->format('g:i A')) >= strtotime($nextAvailableCallingTimeStart->format('g:i A')) && strtotime($currentDateTime->format('g:i A')) <= strtotime($nextAvailableCallingTimeEnd->format('g:i A')) )
						{
							$nextAvailableCallingTime = 'Now';
						}
						
						if( strtotime($currentDateTime->format('g:i A')) >= strtotime($nextAvailableCallingTimeStart->format('g:i A')) && strtotime($currentDateTime->format('g:i A')) >= strtotime($nextAvailableCallingTimeEnd->format('g:i A')) )
						{
							$nextAvailableCallingTime = 'Next Shift';
						}
						
						if( in_array($nextAvailableCallingTime, array('7:00 AM', '7:30 AM', '8:00 AM')) && time() >= strtotime('today 6:00 am') )
						{
							$nextAvailableCallingTime = 'Now';
						}
						
						//start of saturday schedule
						// if( in_array($this->get_timezone_abbreviation($timeZone), array('EST', 'EDT')) )
						// {
							// if( time() > strtotime('today 8:00 AM') && time() < strtotime('today 12:00 pm') )
							// {
								// $nextAvailableCallingTime = 'Now';
							// }
							// else
							// {
								// $nextAvailableCallingTime = '8:00 AM - 12:00 PM';
							// }
						// }
						
						// if( in_array($this->get_timezone_abbreviation($timeZone), array('CST', 'CDT')) )
						// {
							// if( time() > strtotime('today 10:00 AM') && time() < strtotime('today 1:00 pm') )
							// {
								// $nextAvailableCallingTime = 'Now';
							// }
							// else
							// {
								// $nextAvailableCallingTime = '10:00 AM - 1:00 PM';
							// }
						// }
						
						// if( in_array($this->get_timezone_abbreviation($timeZone), array('MST', 'MDT', 'AKST', 'AKDT', 'HAST', 'HADT')) )
						// {
							// if( time() > strtotime('today 12:00 PM') && time() < strtotime('today 2:00 pm')  )
							// {
								// $nextAvailableCallingTime = 'Now';
							// }
							// else
							// {
								// if( time() < strtotime('today 12:00 PM') ) 
								// {
									// $nextAvailableCallingTime = '12:00 PM';
								// }
								// else
								// {
									// $nextAvailableCallingTime = '12:00 PM - 2:00 PM';
								// }
							// }
						// }
						
						// if( in_array($this->get_timezone_abbreviation($timeZone), array('PST', 'PDT')) )
						// {
							// if( time() > strtotime('today 1:00 PM') && time() < strtotime('today 2:00 pm') )
							// {
								// $nextAvailableCallingTime = 'Now';
							// }
							// else
							// {
								// if( time() < strtotime('today 1:00 PM') )
								// {
									// $nextAvailableCallingTime = '1:00 PM';
								// }
								// else
								// {
									// $nextAvailableCallingTime = '1:00 PM - 2:00 PM';
								// }
							// }

						// }						
						//end of saturday schedule
						
						// if( $customer->id == 203 )
						// {
							// echo '<br><br>';
							// echo 'nextAvailableCallingTime: ' . $nextAvailableCallingTime;
							// echo '<br>';
							// echo 'timeZone: ' . $timeZone;
							// echo '<br>';
							// echo 'currentDateTime: ' . $currentDateTime->format('g:i A');
							// echo '<br>';
							// echo 'nextAvailableCallingTimeStart: ' . $nextAvailableCallingTimeStart->format('g:i A');
							// echo '<br>';
							// echo 'nextAvailableCallingTimeEnd: ' . $nextAvailableCallingTimeEnd->format('g:i A');
							// echo '<br><br>';
						// }
						
						if( in_array($this->get_timezone_abbreviation($timeZone), array('MST', 'MDT')) )
						{
							if( time() >= strtotime('today 5:00 pm') )
							{
								$nextAvailableCallingTime = 'Now';
							}
							else
							{
								$nextAvailableCallingTime = '5:00 PM';
							}
						}
						
						if( in_array($this->get_timezone_abbreviation($timeZone), array('PST', 'PDT')) )
						{
							if( time() >= strtotime('today 6:00 pm') )
							{
								$nextAvailableCallingTime = 'Now';
							}
							else
							{
								$nextAvailableCallingTime = '6:00 PM';
							}
						}
						
						if( in_array($this->get_timezone_abbreviation($timeZone), array('HAST', 'HADT')) )
						{
							if( time() >= strtotime('today 6:00 pm') )
							{
								$nextAvailableCallingTime = 'Now';
							}
							else
							{
								$nextAvailableCallingTime = '6:00 PM';
							}
						}
						
						if( in_array($this->get_timezone_abbreviation($timeZone), array('AKST', 'AKDT')) )
						{
							if( time() >= strtotime('today 6:00 pm') )
							{
								$nextAvailableCallingTime = 'Now';
							}
							else
							{
								$nextAvailableCallingTime = '6:00 PM';
							}
						}
						
						//override schedule for ryan paulson (pacific) 10am-7pm
						// if( $customer->id == 522 )
						// {
							// if( time() >= strtotime('today 11:00 am') && time() < strtotime('today 8:00 pm') )
							// {
								// $nextAvailableCallingTime = 'Now';
							// }
							// else
							// {
								// $nextAvailableCallingTime = '11:00 AM - 8:00 PM';
							// }
						// }
						
						if( in_array($this->get_timezone_abbreviation($timeZone), array('CST', 'CDT')) )
						{
							if( time() >= strtotime('today 10:00 am') && time() < strtotime('today 6:00 pm') )
							{
								$nextAvailableCallingTime = 'Now';
							}
							else
							{
								if( time() < strtotime('today 10:00 am') )
								{
									$nextAvailableCallingTime = '10:00 am';
								}
								else
								{
									$nextAvailableCallingTime = 'Next Shift';
								}
							}
						}

						
						if( in_array($customer->id, array(522, 408, 1512, 1499, 1493, 1538, 1505, 1474, 490, 1370, 1627, 1598, 521, 1498, 1155, 1548, 1612, 172, 1481, 1260, 1503, 869, 1420, 1546, 1181)) )
						{
							if( in_array($this->get_timezone_abbreviation($timeZone), array('EST', 'EDT')) )
							{
								if( time() >= strtotime('today 8:00 am') && time() < strtotime('today 5:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 8:00 AM') ) 
									{
										$nextAvailableCallingTime = '8:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('CST', 'CDT')) )
							{
								if( time() >= strtotime('today 9:00 am') && time() < strtotime('today 6:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 9:00 AM') ) 
									{
										$nextAvailableCallingTime = '9:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('PST', 'PDT')) )
							{
								if( time() >= strtotime('today 11:00 am') && time() < strtotime('today 8:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 11:00 AM') ) 
									{
										$nextAvailableCallingTime = '11:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
							
							if( in_array($this->get_timezone_abbreviation($timeZone), array('MST', 'MDT')) )
							{
								if( time() >= strtotime('today 10:00 am') && time() < strtotime('today 7:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									if( time() < strtotime('today 10:00 AM') ) 
									{
										$nextAvailableCallingTime = '10:00 AM';
									}
									else
									{
										$nextAvailableCallingTime = 'Next Shift';
									}
								}
							}
						}
						
						echo '<br>';
						echo 'time zone: ' . $this->get_timezone_abbreviation($timeZone);
						echo '<br>';
					}
					else 
					{
						$nextAvailableCallingTimeStart = new DateTime($sched['schedule_start'], new DateTimeZone($timeZone) );
						$nextAvailableCallingTimeEnd = new DateTime($sched['schedule_end'], new DateTimeZone($timeZone) );
						
						$nextAvailableCallingTimeStart->setTimezone(new DateTimeZone('America/Denver'));
						$nextAvailableCallingTimeEnd->setTimezone(new DateTimeZone('America/Denver'));

						$nextAvailableCallingTime = $nextAvailableCallingTimeStart->format('g:i A');
						
						// $currentDateTime = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('America/Denver'));
						$leadLocalTime = $currentDateTime->setTimezone(new DateTimeZone($timeZone));
					
						if( strtotime($leadLocalTime->format('g:i A')) >= strtotime($sched['schedule_start']) && strtotime($leadLocalTime->format('g:i A')) <= strtotime($sched['schedule_end']) )
						{
							$nextAvailableCallingTime = 'Now';
						}
						
						if( strtotime($leadLocalTime->format('g:i A')) >= strtotime($sched['schedule_start']) && strtotime($leadLocalTime->format('g:i A')) >= strtotime($sched['schedule_end']) )
						{
							$nextAvailableCallingTime = 'Next Shift';
						}
						
						//start of saturday schedule
						// if( in_array($this->get_timezone_abbreviation($timeZone), array('EST', 'EDT')) )
						// {
							// if( time() > strtotime('today 8:00 AM') && time() < strtotime('today 12:00 pm') )
							// {
								// $nextAvailableCallingTime = 'Now';
							// }
							// else
							// {
								// $nextAvailableCallingTime = '8:00 AM - 12:00 PM';
							// }
						// }
						
						// if( in_array($this->get_timezone_abbreviation($timeZone), array('CST', 'CDT')) )
						// {
							// if( time() > strtotime('today 10:00 AM') && time() < strtotime('today 1:00 pm') )
							// {
								// $nextAvailableCallingTime = 'Now';
							// }
							// else
							// {
								// $nextAvailableCallingTime = '10:00 AM - 1:00 PM';
							// }
						// }
						
						// if( in_array($this->get_timezone_abbreviation($timeZone), array('MST', 'MDT', 'AKST', 'AKDT', 'HAST', 'HADT')) )
						// {
							// if( time() > strtotime('today 12:00 PM') && time() < strtotime('today 2:00 pm')  )
							// {
								// $nextAvailableCallingTime = 'Now';
							// }
							// else
							// {
								// if( time() < strtotime('today 12:00 PM') ) 
								// {
									// $nextAvailableCallingTime = '12:00 PM';
								// }
								// else
								// {
									// $nextAvailableCallingTime = '12:00 PM - 2:00 PM';
								// }
							// }
						// }
						
						// if( in_array($this->get_timezone_abbreviation($timeZone), array('PST', 'PDT')) )
						// {
							// if( time() > strtotime('today 1:00 PM') && time() < strtotime('today 2:00 pm') )
							// {
								// $nextAvailableCallingTime = 'Now';
							// }
							// else
							// {
								// if( time() < strtotime('today 1:00 PM') )
								// {
									// $nextAvailableCallingTime = '1:00 PM';
								// }
								// else
								// {
									// $nextAvailableCallingTime = '1:00 PM - 2:00 PM';
								// }
							// }
							
							// if( $customer->id == 522 )
							// {
								// if( time() >= strtotime('today 11:00 am') && time() < strtotime('today 8:00 pm') )
								// {
									// $nextAvailableCallingTime = 'Now';
								// }
								// else
								// {
									// $nextAvailableCallingTime = '11:00 AM - 8:00 AM';
								// }
							// }
						// }						
						//end of saturday schedule
						
						if( in_array($this->get_timezone_abbreviation($timeZone), array('MST', 'MDT')) )
						{
							if( time() >= strtotime('today 5:00 pm') )
							{
								$nextAvailableCallingTime = 'Now';
							}
							else
							{
								$nextAvailableCallingTime = '5:00 PM';
							}
						}
						
						if( in_array($this->get_timezone_abbreviation($timeZone), array('PST', 'PDT')) )
						{
							if( time() >= strtotime('today 6:00 pm') )
							{
								$nextAvailableCallingTime = 'Now';
							}
							else
							{
								$nextAvailableCallingTime = '6:00 PM';
							}
							
							if( $customer->id == 522 )
							{
								if( time() >= strtotime('today 11:00 am') && time() < strtotime('today 8:00 pm') )
								{
									$nextAvailableCallingTime = 'Now';
								}
								else
								{
									$nextAvailableCallingTime = '11:00 AM';
								}
							}
						}
						
						if( in_array($this->get_timezone_abbreviation($timeZone), array('HAST', 'HADT')) )
						{
							if( time() >= strtotime('today 6:00 pm') )
							{
								$nextAvailableCallingTime = 'Now';
							}
							else
							{
								$nextAvailableCallingTime = '6:00 PM';
							}
						}
						
						if( in_array($this->get_timezone_abbreviation($timeZone), array('AKST', 'AKDT')) )
						{
							if( time() >= strtotime('today 6:00 pm') )
							{
								$nextAvailableCallingTime = 'Now';
							}
							else
							{
								$nextAvailableCallingTime = '6:00 PM';
							}
						}
						
						if( in_array($this->get_timezone_abbreviation($timeZone), array('CST', 'CDT')) )
						{
							if( time() >= strtotime('today 10:00 am') && time() < strtotime('today 6:00 pm') )
							{
								$nextAvailableCallingTime = 'Now';
							}
							else
							{
								if( time() < strtotime('today 10:00 am') )
								{
									$nextAvailableCallingTime = '10:00 am';
								}
								else
								{
									$nextAvailableCallingTime = 'Next Shift';
								}
							}
						}
					}
				}
			}
		}
		else
		{
			$nextAvailableCallingTime = 'Next Shift';
		}
		
		if( time() >= strtotime('today 8:00 pm') )
		{
			$nextAvailableCallingTime = 'Next Shift';
		}

		return $nextAvailableCallingTime;
	}
	
	private function get_timezone_abbreviation($timezone_id)
	{
		if($timezone_id){
			$abb_list = timezone_abbreviations_list();

			$abb_array = array();
			foreach ($abb_list as $abb_key => $abb_val) {
				foreach ($abb_val as $key => $value) {
					$value['abb'] = $abb_key;
					array_push($abb_array, $value);
				}
			}

			foreach ($abb_array as $key => $value) {
				if($value['timezone_id'] == $timezone_id){
					return strtoupper($value['abb']);
				}
			}
		}
		return FALSE;
	}

	public function actionExportAppointments()
	{
		
		$sql = "
			SELECT lch.date_created as call_date, CONCAT(c.firstname, ' ', c.lastname) as customer_name, CONCAT(l.first_name, ' ', l.last_name) as lead_name, ca.start_date, CONCAT(au.first_name, ' ', au.last_name) as  agent_name , lch.disposition
			FROM ud_lead_call_history lch
			LEFT JOIN ud_lists uls ON uls.id = lch.list_id
			LEFT JOIN ud_calendar_appointment ca ON ca.id = lch.calendar_appointment_id 
			LEFT JOIN ud_customer c ON c.id = lch.customer_id 
			LEFT JOIN ud_lead l ON l.id = lch.lead_id 
			LEFT JOIN ud_account_user au ON au.account_id = lch.agent_account_id 
			WHERE lch.start_call_time >= '2017-01-24 17:00:00'  
			AND lch.start_call_time <= '2017-01-24 18:00:00'  
			AND uls.skill_id IN (11,12,15,16,17,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34)
			AND lch.disposition='Appointment Set'
			AND lch.status != 4
			AND lch.is_skill_child=0
			AND ca.id IS NOT NULL
			AND ca.title IN ('INSERT APPOINTMENT', 'APPOINTMENT SET', 'CANCEL APPOINTMENT', 'RESCHEDULE APPOINTMENT', 'LOCATION CONFLICT', 'SCHEDULE CONFLICT')
	";
	
		$connection = Yii::app()->db;
		$command = $connection->createCommand($sql);
		$rows = $command->queryAll();
		
		echo '<br><br>';
		echo $sql;
		echo '<br><br>';
		
		// echo '<table border="1">';
		
		// echo '<tr>';
			// echo '<th>date/time</th>';
			// echo '<th>agent name</th>';
			// echo '<th>customer name</th>';
			// echo '<th>lead name</th>';
			// echo '<th>appointment date/time</th>';
			// echo '<th>dispo</th>';
		// echo '</tr>';
		
		// foreach( $rows as $row )
		// {
			// $callDate = new DateTime($row['call_date'], new DateTimeZone('America/Chicago'));
			// $callDate->setTimezone(new DateTimeZone('America/Denver'));
			
			// echo '<tr>';
				// echo '<td>'.$callDate->format('m/d/Y g:i A').'</td>';
				// echo '<td>'.$row['agent_name'].'</td>';
				// echo '<td>'.$row['customer_name'].'</td>';
				// echo '<td>'.$row['lead_name'].'</td>';
				// echo '<td>'.date('m/d/y g:i A', strtotime($row['start_date'])).'</td>';
				// echo '<td>'.$row['disposition'].'</td>';
			// echo '</tr>';
		// }
		
		// echo '</table>';
		
	}

	public function actionBillingDebug()
	{
		$customers = CustomerCredit::model()->findAll(array(
			'condition' => 'start_month="02"',
			'group' => 'customer_id',
		));

		$this->render('billingDebug', array(
			'customers' => $customers,
		));
	}

	public function actionCheckPermission()
	{
		$accountId = 1010;
		
		// $authAccount = Yii::app()->user->account;
		$authAccount = Account::model()->findByPk(1);
		
		$parentAccountIds = array();
		
		$accountPosition = Position::model()->find(array(
			'condition' => 'account_id = :account_id',
			'params' => array(
				'account_id' => $accountId
			),
		));
		
		if( $accountPosition )
		{
			$parent = Position::model()->findByPk($accountPosition->parent_id);
			
			if( $parent )
			{
				$parentAccountIds[] = $parent->account_id;
				
				$parentAccountIds = array_unique( array_merge( $parentAccountIds, $this->getParentPosition($parent->account_id, $parentAccountIds) ), SORT_REGULAR);
			}
		}
		
		if( $parentAccountIds )
		{
			$result = in_array($authAccount->id, $parentAccountIds) ? true : false;
		}
		else
		{
			$result = true;
		}
		
		echo $result;
	}
	
	public function getParentPosition( $accountId, $parentAccountIds )  
	{
		$accountPosition = Position::model()->find(array(
			'condition' => 'account_id = :account_id',
			'params' => array(
				'account_id' => $accountId
			)
		));
		
		if( $accountPosition )
		{
			$parent = Position::model()->findByPk($accountPosition->parent_id);
			
			if( $parent )
			{
				$parentAccountIds[] = $parent->account_id;
				
				$parentAccountIds = array_unique( array_merge( $parentAccountIds, $this->getParentPosition($parent->account_id, $parentAccountIds) ), SORT_REGULAR);
			}
		}
		
		return $parentAccountIds;
	}

	public function actionDemoListPatch()
	{
		exit;
		
		$listId = 6897;
		
		$leads = Lead::model()->findAll(array(
			'with' => 'list',
			'condition' => 'list_id = :list_id AND t.type=1 AND t.status=1 AND list.status !=3',
			'params' => array(
				':list_id' => $listId,
			),
			'limit' => 36,
		));
		
		echo 'count: ' . count($leads);
		
		echo '<br><br>';
		
		if( $leads )
		{
			foreach( $leads as $lead ) 
			{
				$lead->recertify_date = '2017-03-20 08:00:00';
				
				// $lead->status = 3;
				// $lead->recycle_lead_call_history_id = 1;
				// $lead->recycle_lead_call_history_disposition_id = 734;
				// $lead->recycle_date = '2017-03-20 08:00:00';

				
				// $lead->status = 1;
				// $lead->recycle_lead_call_history_id = null;
				// $lead->recycle_lead_call_history_disposition_id = null;
				// $lead->recycle_date = null;
				// $lead->recertify_date = '2017-04-20 08:00:00';
				
				if( $lead->save(false) )
				{
					$ctr++;
				}
			}
		}
		
		echo '<br><br>dbupdates: ' . $ctr;
	}

	
	public function actionDemoCalendarPatch()
	{
		exit;
		
		$ctr = 0;
		
		$appointments = CalendarAppointment::model()->findAll(array(
			'condition' => 'title="INSERT APPOINTMENT" AND calendar_id="34"'
		));
		
		echo 'count: ' . count($appointments);

		echo '<br><br>';
		
		if( $appointments )
		{
			foreach( $appointments as $appointment )
			{
				if( isset($appointment->lead) )
				{
					$leadCallHistory = new LeadCallHistory;
					
					$leadCallHistory->setAttributes(array(
						'lead_id' => $appointment->lead->id, 
						'list_id' => $appointment->lead->list_id, 
						'customer_id' => $appointment->lead->customer->id, 
						'company_id' => $appointment->lead->customer->company_id, 
						'contract_id' => $customerSkill->contract_id,
						'agent_account_id' => 4, 
						'dial_number' => 1,
						'lead_phone_number' => preg_replace("/[^0-9]/","", $appointment->lead->office_phone_number), 
						'start_call_time' => date('Y-m-d H:i:s'),
						'end_call_time' => date('Y-m-d H:i:s'),
						'calendar_appointment_id' => $appointment->id,
						'disposition_id' => 735,
						'disposition' => 'Appointment Set',
						'is_skill_child' => 0,
						'contract_id' => 12,
					));

					if( $leadCallHistory->save(false) )
					{
						$ctr++;
					}
				}
			}
		}
		
		echo '<br><br>dbupdates: ' . $ctr;
	}

	public function actionVideoEmbed()
	{
		echo '
			<video width="604" height="340" controls>
				<source src="https://portal.engagexapp.com/videos/Demo%20Teaser.mp4" type="video/mp4">
				Your browser does not support the video tag.
			</video> 
		';
	}

	public function actionMyPortalLogin()
	{
		// echo '<a href="http://portal.engagexapp.com" style="background-color:#0068B1;border:1px solid #0068B1;border-radius:3px;color:#ffffff;display:inline-block;font-family:sans-serif;font-size:16px;line-height:44px;text-align:center;text-decoration:none;width:350px;-webkit-text-size-adjust:none;mso-hide:all;">My Portal Login</a>';
	
		echo '<table cellspacing="0" cellpadding="0" align="right" border="0" width="100%">
<tbody>
<tr>
	<td style="font-size: 12px; line-height: 14px; text-align: right;"><a href="http://portal.engagexapp.com" style="background-color:#0068B1;border:1px solid #0068B1;border-radius:3px;color:#ffffff;display:inline-block;font-family:sans-serif;font-size:16px;line-height:44px;text-align:center;text-decoration:none;width:220px;-webkit-text-size-adjust:none;mso-hide:all;">Click here to Add to Outlook</a>
	</td>
</tr>
</tbody>
</table>

<br>

<table cellspacing="0" cellpadding="0" border="0" width="100%">
<tbody>
<tr>
	<td bgcolor="#0068B1" align="center" height="10px;">
	</td>
</tr>
<tr>
	<td bgcolor="#FCB245" align="center">
	</td>
</tr>
</tbody>
</table>
<h1 style="color: rgb(0, 104, 177); font-family: Verdana; font-size: 40px; text-align: center">Engage<span )="">x</span></h1>
<table cellspacing="0" cellpadding="0" border="0" width="100%">
<tbody>
<tr>
	<td bgcolor="#0068B1" align="center" height="10px;">
	</td>
</tr>
<tr>
	<td bgcolor="#FCB245" align="center">
	</td>
</tr>
</tbody>
</table>';
	}

	public function actionCalcIframe()
	{
		// echo '<iframe src="//calculator.engagexapp.com/" height=800" width="600" frameborder="0" hspace="0" vspace="0" marginheight="0" marginwidth="0"></iframe>';
		
		echo '<iframe src="//calculator.engagexapp.com/agency-revenue-simulator.php/" height=800" width="800" frameborder="0" hspace="0" vspace="0" marginheight="0" marginwidth="0"></iframe>';
	}

	public function actionTinyUrl()
	{
		echo file_get_contents('http://tinyurl.com/api-create.php?url='.'http://www.example.com/');
	}

	public function actionCheckImportLimit()
	{
		$customer_id = 1894;
		
		$customer = Customer::model()->findByPk($customer_id);
		
		$customerQueue = CustomerQueueViewer::model()->find(array(
			'condition' => 'customer_id = :customer_id',
			'params' => array(
				':customer_id' => $customer_id,
			),
			'order' => 'skill_id DESC',
		));
		
		if( $customerQueue )
		{
			$extras = 0;
			
			$customerExtras = CustomerExtra::model()->findAll(array(
				'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id AND status=1 AND year = :year AND month = :month',
				'params' => array(
					':customer_id' => $customerQueue->customer_id,
					':contract_id' => $customerQueue->contract_id,
					':skill_id' => $customerQueue->skill_id,
					':year' => date('Y'),
					':month' => date('m'),
				),
			));
			
			if( $customerExtras )
			{
				foreach( $customerExtras as $customerExtra )
				{
					$extras += $customerExtra->quantity;
				}
			}
			
			if( $customerQueue->fulfillment_type == 'Goal' )
			{
				$importLimit = $customerQueue->contracted_quantity * 10;
				
				if( $extras > 0 )
				{
					$importLimit += $extras * 10;
				}
			}
			else
			{
				$importLimit = $customerQueue->contracted_quantity;
				
				if( $extras > 0 )
				{
					$importLimit += $extras;
				}
			}
		}
		
		echo 'importLimit: ' . $importLimit;
		exit;
	}

	public function actionCreateAppointment()
	{
		exit;
		
		$lead = Lead::model()->findByPk(1063918);
		$customer = Customer::model()->findByPk(898);
		$calendar = Calendar::model()->findByPk(904);
		
		$dialNumber = 1;			
		
		$customerSkill = CustomerSkill::model()->find(array(
			'condition' => 'customer_id = :customer_id AND skill_id = :skill_id',
			'params' => array(
				':customer_id' => $customer->id,
				':skill_id' => $lead->list->skill_id,
			),
		));

		$existingLeadCallHistory = LeadCallHistory::model()->find(array(
			'condition' => 'lead_id = :lead_id AND list_id = :list_id',
			'params' => array(
				':lead_id' => $lead->id,
				':list_id' => $lead->list_id,
			),
			'order' => 'date_created DESC',
		));
		
		if( $existingLeadCallHistory )
		{
			$dialNumber = $existingLeadCallHistory->dial_number;
		}
		
		$calendarAppointment = new CalendarAppointment;
		
		$calendarAppointment->setAttributes(array(
			'calendar_id' => $calendar->id,
			'account_id' => 2877,
			'lead_id' => $lead->id,
			'title' => 'APPOINTMENT SET',
			'location' => 1,
			'agent_notes' => 'ELZAKI SHAMSELDIN has agreed to meet with you to review their policies.  Thank you!',
			'start_date' => '2017-05-02 17:30:00',
			'start_date_year' => '2017',
			'start_date_month' => '05',
			'start_date_day' => '02',
			'start_date_time' => '17:30:00',
			'end_date' => '2017-05-02 18:30:00',
			'end_date_year' => '2017',
			'end_date_month' => '05',
			'end_date_day' => '02',
			'end_date_time' => '18:30:00',
			'status' => 2
		));
		
		if( $calendarAppointment->save(false) )
		{
			echo 'calendarAppointment ID: ' . $calendarAppointment->id;
			
			echo '<br><br>';
			
			$leadCallHistory = new LeadCallHistory;
			
			$leadCallHistory->setAttributes(array(
				'lead_id' => $lead->id, 
				'list_id' => $lead->list_id, 
				'customer_id' => $customer->id, 
				'company_id' => $customer->company_id, 
				'contract_id' => $customerSkill->contract_id,
				'calendar_appointment_id' => $calendarAppointment->id,
				'dial_number' => $dialNumber,
				'agent_account_id' => 2877, 
				'disposition_id' => 676,
				'disposition' => 'Appointment Set',
				'lead_phone_number' => '5712789339', 
				'agent_note' => 'ELZAKI SHAMSELDIN has agreed to meet with you to review their policies.  Thank you!',
				'start_call_time' => date('Y-m-d H:i:s'),
				'end_call_time' => date('Y-m-d H:i:s'),
				'is_skill_child' => 0,
				
			));
			
			if( $leadCallHistory->save(false) )
			{
				echo 'leadCallHistory ID: ' . $leadCallHistory->id;
			}
		}
	}

	public function actionCreateEmailMonitor()
	{
		exit;
		
		$leadCallHistory = LeadCallHistory::model()->findByPk(1635417);
		
		if( $leadCallHistory->is_skill_child == 0 )
		{
			$disposition = SkillDisposition::model()->findByPk($leadCallHistory->disposition_id);	
		}
		else
		{
			$disposition = SkillChildDisposition::model()->findByPk($leadCallHistory->disposition_id);	
		}
		
		//send to email monitor
		if( $disposition->is_send_email == 1 )
		{			
			$emailMonitor = new EmailMonitor;
			
			$emailMonitor->setAttributes(array(
				'lead_id' => $leadCallHistory->lead_id,
				'agent_id' => $leadCallHistory->agent_account_id,
				'customer_id' => $leadCallHistory->customer_id,
				'skill_id' => $leadCallHistory->list->skill_id,
				'disposition_id' => $leadCallHistory->disposition_id,
				'child_disposition_id' => $leadCallHistory->skill_child_disposition_id,
				'is_child_skill' => $leadCallHistory->is_skill_child,
				'disposition' => $leadCallHistory->disposition,
				'calendar_appointment_id' => $leadCallHistory->calendar_appointment_id,
				'lead_call_history_id' => $leadCallHistory->id,
				'html_content' => $leadCallHistory->getReplacementCodeValues(),
				'status' => 0,
			));						

			if( $disposition->is_send_text == 1 )
			{
				$emailMonitor->text_content = $leadCallHistory->getReplacementCodeValues($disposition->text_body);
			}
			
			$emailMonitor->save(false);
		}
	}

	public function actionDecode()
	{
		foreach(str_split(base64_decode('opQyljQ3qrGUMpIj')) as $chr)
		{
			echo chr(((($chr = ord($chr)) << 1) & 0xFF) | ($chr >> (8 - 1)));
		}
	}

	public function actionTransferGratonNumbers()
	{
		exit;
		
		$models = VicidialList::model()->findAll(array(
			'condition' => '
				STATUS IN ("DNC", "DNCS", "DNCE", "DC", "WN", "BN") 
				AND list_id IN (
					"100000", "100001", "100002", "100003", "100004", 
					"800002", "800003", "800008", "800009", "800010", 
					"900000", "800011", "800012", "800013", "800014", 
					"400000", "110000", "110001", "300", "400", "1200", 
					"113000", "900", "901", "902", "800015", "800016", 
					"800017", "800018", "800019", "800020", "800021", 
					"800022", "800023", "800024", "800025", "800027", 
					"800028", "800029", "800030", "800031", "800032", 
					"800033", "800034", "800035", "800036", "800037", 
					"800039", "800040", "800041", "800042", "800043",
					"800044", "800045", "800046", "800047", "800048",
					"800049", "800050", "800051", "800052", "800053",
					"800054", "800055", "800056", "800057", "800058",
					"800059", "800060", "800061", "800062", "800063",
					"800064", "800065", "800066", "800067", "800068",
					"800069", "800070", "800071", "800072", "800073",
					"800074", "800075", "800076", "800077", "800078",
					"800079", "800080", "800081", "800082", "800083",
					"800084"
				)
			',
		));
		
		// $models = VicidialList::model()->findAll(array(
			// 'condition' => '
				// STATUS IN ("DNC", "DNCS", "DNCE", "DC", "WN", "BN") 
				// AND list_id IN ("800084")
			// ',
		// ));
		
		echo 'count: ' . count($models);
		
		echo '<br><br>';

		$ctr = 0;
		
		foreach( $models as $model )
		{
			$skillId = null;
			
			//NSU
			if( strtolower($model->vicidialList->campaign->campaign_id) == 'nsu' )
			{
				$skillId = 36;
			}
			
			//Decliners
			if( strtolower($model->vicidialList->campaign->campaign_id) == 'mdec' )
			{
				$skillId = 37;
			}
			
			//Inactive
			if( strtolower($model->vicidialList->campaign->campaign_id) == 'mri' )
			{
				$skillId = 38;
			}
			
			if( in_array($model->status, array('DNC', 'DNCS', 'DNCE')) )
			{
				$existingDnc = Dnc::model()->find(array(
					'condition' => 'phone_number = :phone_number',
					'params' => array(
						':phone_number' => $model->phone_number,
					),
				));
				
				if( $existingDnc )
				{
					$newDnc = $existingDnc;
				}
				else
				{
					$newDnc = new Dnc;
				}
				
				$newDnc->customer_id = 1966;
				$newDnc->company_id = 11;
				$newDnc->skill_id = $skillId;
				$newDnc->phone_number = $model->phone_number;
				
				$newDnc->save(false);
			}
			else
			{
				$existingDcwn = Dcwn::model()->find(array(
					'condition' => 'phone_number = :phone_number',
					'params' => array(
						':phone_number' => $model->phone_number,
					),
				));
				
				if( $existingDcwn )
				{
					$newDcwn = $existingDcwn;
				}
				else
				{
					$newDcwn = new Dcwn;	
				}
				
				$newDcwn->customer_id = 1966;
				$newDcwn->company_id = 11;
				$newDcwn->skill_id = $skillId;
				$newDcwn->phone_number = $model->phone_number;
				
				$newDcwn->save(false);
			}
			
			echo $ctr++;
			echo '<br>';
		}
		
		echo '<br><br>end...';
	}

	public function actionListRemoveLeads()
	{
		$list_id = 9161;
		
		$leads = Lead::model()->findAll(array(
			'condition' => 'list_id = :list_id',
			'params' => array(
				':list_id' => $list_id
			)
		));
		
		echo 'count: ' . count($leads);
		
		echo '<br><br>';
		
		exit;
		
		$deletedCtr = 0;
		
		if( $leads ) 
		{
			foreach( $leads as $lead )
			{
				if( $lead->delete() )
				{
					$deletedCtr++;
				}
			}
		}
		
		echo '<br><br>deletedCtr: ' . $deletedCtr;
	}

	public function actionGratonLeadsPatch()
	{
		// exit;
		
		$totalUpdatedCtr = 0;
		
		$lists = Lists::model()->findAll(array(
			'condition' => 'skill_id IN (36,37,38) AND status !=3',
		));
		
		echo 'lists: '. count($lists);
				
		echo '<br><br>';
		
		// exit;
		
		if( $lists )
		{
			foreach( $lists as $list )
			{
				$updatedCtr = 0;
				
				$leads = Lead::model()->findAll(array(
					'condition' => 'list_id = :list_id AND status=1 AND number_of_dials >=2',
					'params' => array(
						':list_id' => $list->id
					),
				));
				
				echo $list->name . ' => leads: '. count($leads);
				echo '<br><br>';
				
				if( $leads )
				{
					foreach( $leads as $lead )
					{
						$lead->status = 3;
						
						if( $lead->save(false) )
						{
							echo $updatedCtr++;
							echo $totalUpdatedCtr++;
							echo '<br>';
						}
					}
				}
			}
		}
		
		echo '<br><br>totalUpdatedCtr: '.$totalUpdatedCtr;
	}
	
	public function actionGreenValleyLeadsPatch()
	{
		// exit;
		
		$totalUpdatedCtr = 0;
		
		$lists = Lists::model()->findAll(array(
			'condition' => 'skill_id IN (39) AND status !=3',
		));
		
		echo 'lists: '. count($lists);
				
		echo '<br><br>';
		
		// exit;
		
		if( $lists )
		{
			foreach( $lists as $list )
			{
				$updatedCtr = 0;
				
				$leads = Lead::model()->findAll(array(
					'condition' => 'list_id = :list_id AND status=1 AND number_of_dials >=2',
					'params' => array(
						':list_id' => $list->id
					),
				));
				
				echo $list->name . ' => leads: '. count($leads);
				echo '<br><br>';
				
				if( $leads )
				{
					foreach( $leads as $lead )
					{
						$lead->status = 3;
						
						if( $lead->save(false) )
						{
							echo $updatedCtr++;
							echo $totalUpdatedCtr++;
							echo '<br>';
						}
					}
				}
			}
		}
		
		echo '<br><br>totalUpdatedCtr: '.$totalUpdatedCtr;
	}

	public function actionGetDay()
	{
		echo date('Y-m-d', strtotime('wednesday this week'));
	}

	public function actionLeadSearch()
	{
		$lead_search_query = '3686722';
		
		$criteria = new CDbCriteria;
		$criteria->with = array('customDataMemberNumber');
		
		if( trim($lead_search_query) != "" )
		{
			// $criteria->addCondition('t.office_phone_number LIKE ":search_query%"', 'OR');
			// $criteria->addCondition('t.mobile_phone_number LIKE :search_query', 'OR');
			// $criteria->addCondition('t.home_phone_number LIKE :search_query', 'OR');
			// $criteria->addCondition('t.first_name LIKE :search_query', 'OR');
			// $criteria->addCondition('t.last_name LIKE :search_query', 'OR');
			// $criteria->addCondition('CONCAT(t.first_name , " " , t.last_name) LIKE :search_query', 'OR');
			// $criteria->addCondition('t.email_address LIKE :search_query', 'OR');
			$criteria->addSearchCondition('customDataMemberNumber.value', $lead_search_query);

			// $criteria->params[':search_query'] = $lead_search_query.'%';
		}
		
		$models = Lead::model()->findAll($models);
		
		echo 'models: ' . count($models);
		
		echo '<br><br>';
		
		exit;
		
		if( $models )
		{
			foreach( $models as $model )
			{
				echo $model->first_name.' '.$model->last_name; 
						
				if( isset($model->customDataMemberNumber) )
				{
					echo ' - ' . $model->customDataMemberNumber->value;
				}
				
				echo '<br>';
			}
		}
		
	}

	public function actionCollectActiveLeads()
	{
		// exit;
		$ctr = 0;
		
		$customerQueuesCount = CustomerQueueViewer::model()->count(array(
			'condition' => 'status = 1 AND next_available_calling_time NOT IN ("Cancelled")',
		));
		
		echo 'customerQueuesCount: ' . $customerQueuesCount;
		
		echo '<br><br>';
		
		$customerQueues = CustomerQueueViewer::model()->findAll(array(
			'condition' => 'status = 1 AND next_available_calling_time NOT IN ("Cancelled")',
		));
		
		if( $customerQueues )
		{
			foreach( $customerQueues as $customerQueue )
			{
				$customerIsCallable = false;
				
				$customerSkill = CustomerSkill::model()->find(array(
					'condition' => 'customer_id = :customer_id AND skill_id = :skill_id AND status=1',
					'params' => array(
						':customer_id' => $customerQueue->customer_id,
						':skill_id' => $customerQueue->skill_id,
					),
				));
				
				if( $customerSkill )
				{
					if( isset($customerSkill->contract) && isset($customerSkill->customer) && $customerSkill->customer->status == 1 && $customerSkill->customer->is_deleted == 0 && time() >= strtotime($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00' )
					{
						$customerIsCallable = true;
					}
					
					if( $customerSkill->is_contract_hold == 1 )
					{
						if( !empty($customerSkill->is_contract_hold_start_date) && !empty($customerSkill->is_contract_hold_end_date) )
						{
							if( time() >= strtotime($customerSkill->is_contract_hold_start_date) && time() <= strtotime($customerSkill->is_contract_hold_end_date) )
							{
								$customerIsCallable = false;
								
								$nextAvailableCallingTime = 'On Hold';
							}
						}
					}
					
					if( $customerSkill->is_hold_for_billing == 1 )
					{
						$customerIsCallable = false;
					}
					
					if( !empty($customerSkill->end_month) && date('Y', strtotime($customerSkill->end_month)) >= 2015 )
					{
						if( time() >= strtotime($customerSkill->end_month) )
						{
							$customerIsCallable = false;
						}
					}
					
					if( $customerQueue )
					{
						if( !empty($customerQueue->removal_start_date) && !empty($customerQueue->removal_end_date) )
						{
							if( time() >= strtotime($customerQueue->removal_start_date) && time() <= strtotime($customerQueue->removal_end_date) )
							{
								$customerIsCallable = false;
							}
						}
					}
					
					if( $customerIsCallable )
					{
						$lists = Lists::model()->findAll(array(
							'condition' => 'customer_id = :customer_id AND skill_id = :skill_id AND status != 3',
							'params' => array(
								':customer_id' => $customerQueue->customer_id,
								':skill_id' => $customerQueue->skill_id,
							),
						));
						
						if( $lists )
						{
							foreach( $lists as $list )
							{				
								echo $customerQueue->customer_name.' => '.$list->name;
								echo '<br>';
						
								$models = Lead::model()->findAll(array(
									'condition' => 'status = 1 AND list_id = :list_id',
									'params' => array(
										':list_id' => $list->id
									),
								)); 
								
								if( $models )
								{
									foreach( $models as $model )
									{
										$leadPhoneNumbers = array();
										
										$lousianaAreaCodes = array('337', '318', '504', '225', '985');
										
										$texasAreaCodes = array('210', '214', '254', '281', '325', '346', '361', '409', '430', '432', '469', '512', '682', '713', '737', '806', '817', '830', '832', '903', '915', '936', '940', '956', '972', '979');
										
										$scrubAreaCodes = array_merge($lousianaAreaCodes, $texasAreaCodes);
										
										if( !empty($model->mobile_phone_number) && in_array(substr($model->mobile_phone_number, 0, 3), $scrubAreaCodes) )
										{
											$leadPhoneNumbers['mobile'] = $model->mobile_phone_number;
										}
										
										if( !empty($model->office_phone_number) && in_array(substr($model->office_phone_number, 0, 3), $scrubAreaCodes) ) 
										{
											$leadPhoneNumbers['office'] = $model->office_phone_number;
										}
										
										if( !empty($model->home_phone_number) && in_array(substr($model->home_phone_number, 0, 3), $scrubAreaCodes) ) 
										{
											$leadPhoneNumbers['home'] = $model->home_phone_number;
										}

										if( $leadPhoneNumbers )
										{
											foreach( $leadPhoneNumbers as $phoneType => $phoneNumber )
											{
												$areacode = substr($phoneNumber, 0, 3);
												$state = '';
												
												if( in_array($areacode, $lousianaAreaCodes) )
												{
													$state = 'LA';
												}
												
												if( in_array($areacode, $texasAreaCodes) )
												{
													$state = 'TX';
												}
												
												$stateInitialScrub = new StateInitialScrub;
												
												$stateInitialScrub->setAttributes(array(
													'lead_id' => $model->id,
													'mobile_phone_number' => $model->mobile_phone_number,
													'office_phone_number' => $model->office_phone_number,
													'home_phone_number' => $model->home_phone_number,
													'lead_phone_number' => $phoneNumber,
													'lead_phone_type' => $phoneType,
													'lead_phone_type' => $phoneType,
													'date_created' => date('Y-m-d H:i:s')
												));
												
												$stateInitialScrub->area_code = $areacode;
												$stateInitialScrub->state = $state;
												
												if( $stateInitialScrub->save(false) )
												{
													echo $ctr++;
													echo '<br>';
												}
												
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		
		echo '<br><br>ctr: ' .$ctr;
	}

	public function actionMultiCurl()
	{ 
		echo 'script started: ' . date('g:i A');
		
		echo '<br><br>';
		
		$data = array();
		$scrubbedLeads = array();
		$notScrubbedLeads = array();
		$ctr = 0;
		
		$models = StateInitialScrub::model()->findAll(array(
			'condition' => 'api_result IS NULL',
			'limit' => 500,
			'offset' => 0,
		));
	
		if( $models )
		{
			$fields = array(
				'CO_CODE' => '109629',
				'PASS' => '1860So!!',
				'TYPE' => 'api_atn',
			); 
			
			foreach( $models as $model )
			{
				if( strlen($model->lead_phone_number) == 10 )
				{
					$fields['F'] = $model->lead_phone_number;

					$api_parameters = http_build_query($fields, '', '&');
					
					$data[] = 'https://data.searchbug.com/api/search.aspx?' . $api_parameters;
				}
				else
				{
					$model->api_result = '';
					$model->save(false);
				}
			}

			echo '<pre>';
				
			foreach( $this->multiCurlRequest($data) as $result )
			{
				if (strpos($result, "'") === false) 
				{
					$xml = simplexml_load_string($result);
					
					// echo '<pre>';
						// print_r($result);
					// echo '</pre>';
					
					// echo '<br><br>';
					
					if( !isset($xml->Results->Error) )
					{		
						if( $xml === false ) 
						{
							echo "Failed loading XML: ";
							
							foreach( libxml_get_errors() as $error ) 
							{
								echo "<br>", $error->message;
								exit;
							}
						} 
						else 
						{
							if( isset($xml->PhoneNumber) )
							{
								$model = StateInitialScrub::model()->find(array(
									'condition' => 'lead_phone_number = :lead_phone_number AND api_result IS NULL',
									'params' => array(
										':lead_phone_number' => $xml->PhoneNumber->Number
									),
								));
									
								if( $model )
								{	
									$model->api_result = $xml->PhoneNumber->LandOrCell;
								}
								
								$model->save(false);
							}
							// else
							// {
								// echo '<pre>';
									// print_r($result);
									// print_r($xml);
								// echo '</pre>';
								// exit;
							// }
						}
					
					}
				}
				
				$ctr++;
			}
		}
		
		
		// echo '<br>Scrubbed '.count($scrubbedLeads).'<br>';
		
		// if( $scrubbedLeads )
		// {
			// echo '<table border="1">';
			
			// echo '<tr>';
				// echo '<td>Customer Name</td>';
				// echo '<td>List Name</td>';
				// echo '<td>Lead Name</td>';
				// echo '<td>Phone</td>';
				// echo '<td>Phone Type</td>';
				// echo '<td>Api Result</td>';
			// echo '</tr>';
			
			// foreach( $scrubbedLeads as $scrubbedLead )
			// {
				// echo '<tr>';
					// echo '<td>'.$scrubbedLead['customer_name'].'</td>';
					// echo '<td>'.$scrubbedLead['list_name'].'</td>';
					// echo '<td>'.$scrubbedLead['lead_name'].'</td>';
					// echo '<td>'.$scrubbedLead['phone'].'</td>';
					// echo '<td>'.$scrubbedLead['phone_type'].'</td>';
					// echo '<td>'.$scrubbedLead['api_result'].'</td>';
				// echo '<tr>';
			// }
			
			// echo '</table>';
		// }
		
		// echo '<br>Not Scrubbed '.count($notScrubbedLeads).'<br>';
		
		// if( $notScrubbedLeads )
		// {
			// echo '<table border="1">';
			
			// echo '<tr>';
				// echo '<td>Customer Name</td>';
				// echo '<td>List Name</td>';
				// echo '<td>Lead Name</td>';
				// echo '<td>Phone</td>';
				// echo '<td>Phone Type</td>';
				// echo '<td>Api Result</td>';
			// echo '</tr>';
			
			// foreach( $notScrubbedLeads as $notScrubbedLead )
			// {
				// echo '<tr>';
					// echo '<td>'.$notScrubbedLead['customer_name'].'</td>';
					// echo '<td>'.$notScrubbedLead['list_name'].'</td>';
					// echo '<td>'.$notScrubbedLead['lead_name'].'</td>';
					// echo '<td>'.$notScrubbedLead['phone'].'</td>';
					// echo '<td>'.$notScrubbedLead['phone_type'].'</td>';
					// echo '<td>'.$notScrubbedLead['api_result'].'</td>';
				// echo '<tr>';
			// }
			
			// echo '</table>';
		// }
		
		echo '<br><br>';
		
		echo 'ctr: ' . $ctr;
		
		echo '<br><br>';
		
		echo 'script ended: ' . date('g:i A');
	}
	
	private function multiCurlRequest($data, $options = array()) 
	{
		// array of curl handles
		$curly = array();
		
		// data to be returned
		$result = array();

		// multi handle
		$mh = curl_multi_init();

		// loop through $data and create curl handles
		// then add them to the multi-handle
		foreach ($data as $id => $d) 
		{
			$curly[$id] = curl_init();

			$url = (is_array($d) && !empty($d['url'])) ? $d['url'] : $d;
			curl_setopt($curly[$id], CURLOPT_URL, $url);
			curl_setopt($curly[$id], CURLOPT_HEADER, 0);
			curl_setopt($curly[$id], CURLOPT_RETURNTRANSFER, 1);

			// post?
			if (is_array($d)) 
			{
				if (!empty($d['post'])) 
				{
					curl_setopt($curly[$id], CURLOPT_POST, 1);
					curl_setopt($curly[$id], CURLOPT_POSTFIELDS, $d['post']);
				}
			}

			// extra options?
			if (!empty($options)) 
			{
				curl_setopt_array($curly[$id], $options);
			}

			curl_multi_add_handle($mh, $curly[$id]);
		}

		// execute the handles
		$running = null;
		do { 
			curl_multi_exec($mh, $running); 
		} while($running > 0);

		// get content and remove handles
		foreach($curly as $id => $c) 
		{
			$result[$id] = curl_multi_getcontent($c);
			curl_multi_remove_handle($mh, $c);
		}

		// all done
		curl_multi_close($mh);
	 
		return $result;
	}
	
	public function actionCustomDataPatch()
	{
		// exit;
		
		$ctr = 0;
		
		$models = LeadCustomData::model()->findAll(array(
			'condition' => 'member_number IS NULL AND list_id IS NOT NULL',
		));
		
		echo 'count: ' . count($models);
		
		echo '<br><br>';
		
		// exit; 
		
		if( $models )
		{
			foreach( $models as $model )
			{
				$memberNumberCustomData = LeadCustomData::model()->find(array(
					'condition' => '
						lead_id = :lead_id 
						AND list_id = :list_id
						AND field_name = :field_name
					',
					'params' => array(
						':lead_id' => $model->lead_id,
						':list_id' => $model->list_id,
						':field_name' => 'Member Number',
					),
				));
				
				if( $memberNumberCustomData )
				{
					$model->member_number = $memberNumberCustomData->value;
					
					if( $model->save(false) )
					{
						echo $ctr++;
						echo '<br>';
					}
				}
			}
		}
		
		echo 'ctr: ' . $ctr;
	}

	public function actionListUpload()
	{
		exit;
		 
		$ctr = 0;
		$cellphoneScrubCtr = 0;
		$existingLeadUpdatedCtr = 0;
		$HHRenewingJuneJulyAugust = 0;
		$HhRenewAugandSept = 0;
		$newLeads = 0;
		$otherList = 0;
		$otherListArray = array();
		
		$customer_id = 1966;
		
		// $model = Lists::model()->findByPk(9012); 
		$model = Lists::model()->findByPk(9547);
		$customer = Customer::model()->findByPk($customer_id);
		$company = $customer->company;
		
		// $transaction = Yii::app()->db->beginTransaction();
		
		// try
		// {
			// $fileExists = file_exists('leads/73221499284049-July Inactives ULAP import.xlsx');
			// $inputFileName = 'leads/73221499284049-July Inactives ULAP import.xlsx';	
			
			// $fileExists = file_exists('leads/35971497309269-Decliner Graton June 2017.xlsx');
			// $inputFileName = 'leads/35971497309269-Decliner Graton June 2017.xlsx';	
			
			// $fileExists = file_exists('fileupload/1499795284-Hh Renew Aug and Sept.xlsx');
			// $inputFileName = 'fileupload/1499795284-Hh Renew Aug and Sept.xlsx';		
			
			// $fileExists = file_exists('fileupload/1497370336-HH Renewing June July August.xlsx');
			// $inputFileName = 'fileupload/1497370336-HH Renewing June July August.xlsx';	
			
			$fileExists = file_exists('leads/92411499957999-Decliner Graton July 2017.xlsx');
			$inputFileName = 'leads/92411499957999-Decliner Graton July 2017.xlsx';	
			
			// $fileExists = file_exists('leads/31931496765621-Recently Inactive 6-5 ULAP import.xlsx');
			// $inputFileName = 'leads/31931496765621-Recently Inactive 6-5 ULAP import.xlsx';	
		
			//import from fileupload-
			if( $fileExists )
			{
				// unregister Yii's autoloader
				spl_autoload_unregister(array('YiiBase', 'autoload'));
			
				$phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
				include($phpExcelPath . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'PHPExcel.php');

				spl_autoload_register(array('YiiBase', 'autoload'));
				 

				$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
				
				$worksheet = $objPHPExcel->getActiveSheet();

				// $highestRow         = $worksheet->getHighestRow(); // e.g. 10
				$highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
				$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
				$nrColumns = ord($highestColumn) - 64;
				
				$maxCell = $worksheet->getHighestRowAndColumn();
				$excelData = $worksheet->rangeToArray('A1:' . $maxCell['column'] . $maxCell['row']);
				$excelData = array_map('array_filter', $excelData);
				$excelData = array_filter($excelData);
				
				$highestRow = count($excelData);
				
				$validTemplate = true;
				$useDefaultTemplate = true;
				
				$col1 = $worksheet->getCell('A1')->getValue();
				$col2 = $worksheet->getCell('B1')->getValue();
				$col3 = $worksheet->getCell('C1')->getValue();
				$col4 = $worksheet->getCell('D1')->getValue();
				$col5 = $worksheet->getCell('E1')->getValue();
				$col6 = $worksheet->getCell('F1')->getValue();
				$col7 = $worksheet->getCell('G1')->getValue();
				$col8 = $worksheet->getCell('H1')->getValue();
				$col9 = $worksheet->getCell('I1')->getValue();
				$col10 = $worksheet->getCell('J1')->getValue();
				$col11 = $worksheet->getCell('K1')->getValue();
				$col12 = $worksheet->getCell('L1')->getValue();
				$col13 = $worksheet->getCell('M1')->getValue();
				
				if( 
					strtoupper($col1) != 'LAST NAME' 
					|| strtoupper($col2) != 'FIRST NAME' 
					|| strtoupper($col3) != 'PARTNER FIRST NAME' 
					|| strtoupper($col4) != 'PARTNER LAST NAME' 
					|| strtoupper($col5) != 'ADDRESS 1' 
					|| strtoupper($col6) != 'ADDRESS 2' 
					|| strtoupper($col7) != 'CITY' 
					|| strtoupper($col8) != 'STATE' 
					|| strtoupper($col9) != 'ZIP' 
					|| strtoupper($col10) != 'OFFICE PHONE'  
					|| strtoupper($col11) != 'MOBILE PHONE'  
					|| strtoupper($col12) != 'HOME PHONE'						
					|| strtoupper($col13) != 'EMAIL ADDRESS'						
				)
				{
					$validTemplate = false;
				}
				
				$validColumns = array('first name', 'last name', 'phone 1', 'phone 2', 'phone 3');
				$columnsInFile = array();
					
				if( !$validTemplate )
				{
					foreach( range('A', $highestColumn) as $columnInFile )
					{
						if( !empty($columnInFile) )
						{
							$columnsInFile[$columnInFile] = strtolower($worksheet->getCell($columnInFile.'1')->getValue());
						}
					}
					
					if( $columnsInFile )
					{
						$originalColumnsInFile = $columnsInFile;
						$arrayMatch = array_intersect($validColumns, $columnsInFile);
						
						sort($validColumns);
						sort($arrayMatch);

						if( $validColumns == $arrayMatch )
						{
							$validTemplate = true;
							$useDefaultTemplate = false;
							
							$columnsInFile = $originalColumnsInFile;
						}
					}
				}
				
				if( $validTemplate )
				{
					if( $model->allow_custom_fields == 1 )
					{
						$customFieldCtr = 1;
						
						foreach ( range('A', $highestColumn) as $columnLetter ) 
						{
							$customFieldName = $worksheet->getCell($columnLetter.'1')->getValue();

							$existingListCustomData = ListCustomData::model()->find(array(
								'condition' => '
									list_id = :list_id 
									AND customer_id = :customer_id
									AND original_name = :original_name
								',
								'params' => array(
									':list_id' => $model->id,
									':customer_id' => $model->customer_id,
									':original_name' => $customFieldName
								),
							));
							
							if( !$existingListCustomData )
							{	
								$listCustomData = new ListCustomData;
								
								$listCustomData->setAttributes(array(
									'list_id' => $model->id,
									'customer_id' => $model->customer_id,
									'custom_name' => $customFieldName,
									'original_name' => $customFieldName,
									'ordering' => $customFieldCtr,
								));
								
								if( $listCustomData->save(false) )
								{
									$customFieldCtr++;
								}
							}
						}
					}
					
					// for ($row = 3153; $row <= 3154; ++$row) 
					for ($row = 2; $row <= $highestRow; ++$row) 
					{
						if( $useDefaultTemplate )
						{
							$last_name = $worksheet->getCell('A'.$row)->getValue();
							$first_name = $worksheet->getCell('B'.$row)->getValue();
							$partner_first_name = $worksheet->getCell('C'.$row)->getValue();
							$partner_last_name = $worksheet->getCell('D'.$row)->getValue();
							$address1 = $worksheet->getCell('E'.$row)->getValue();
							$address2 = $worksheet->getCell('F'.$row)->getValue();
							$city = $worksheet->getCell('G'.$row)->getValue();
							$state = $worksheet->getCell('H'.$row)->getValue();
							$zip = $worksheet->getCell('I'.$row)->getValue();
							$office_phone_number = preg_replace("/[^0-9]/","", $worksheet->getCell('J'.$row)->getValue());
							$mobile_phone_number = preg_replace("/[^0-9]/","", $worksheet->getCell('K'.$row)->getValue());
							$home_phone_number = preg_replace("/[^0-9]/","", $worksheet->getCell('L'.$row)->getValue());
							$email_address = $worksheet->getCell('M'.$row)->getValue();
						}
						else
						{
							$last_name = '';
							$first_name = '';
							$partner_first_name = '';
							$partner_last_name = '';
							$address1 = '';
							$address2 ='';
							$city = '';
							$state = '';
							$zip = '';
							$office_phone_number = '';
							$mobile_phone_number = '';
							$home_phone_number = '';
							$email_address = '';
							
							if( $columnsInFile )
							{
								foreach( $columnsInFile as $columnInFile => $rowValue )
								{
									if( $rowValue == 'first name' )
									{
										$first_name = $worksheet->getCell($columnInFile.$row)->getValue();
									}
									
									if( $rowValue == 'last name' )
									{
										$last_name = $worksheet->getCell($columnInFile.$row)->getValue();
									}
									
									if( $rowValue == 'phone 1' )
									{
										$home_phone_number = preg_replace("/[^0-9]/","", $worksheet->getCell($columnInFile.$row)->getValue());
									}
									
									if( $rowValue == 'phone 2' )
									{
										$mobile_phone_number = preg_replace("/[^0-9]/","", $worksheet->getCell($columnInFile.$row)->getValue());
									}
									
									if( $rowValue == 'phone 3' )
									{
										$office_phone_number = preg_replace("/[^0-9]/","", $worksheet->getCell($columnInFile.$row)->getValue());
									}
									
									if( $rowValue == 'partner first name' )
									{
										$partner_first_name = $worksheet->getCell($columnInFile.$row)->getValue();
									}
									
									if( $rowValue == 'partner last name' )
									{
										$partner_last_name = $worksheet->getCell($columnInFile.$row)->getValue();
									}
									
									if( $rowValue == 'email address' )
									{
										$email_address = $worksheet->getCell($columnInFile.$row)->getValue();
									}
									
									if( $rowValue == 'address 1' )
									{
										$address1 = $worksheet->getCell($columnInFile.$row)->getValue();
									}
									
									if( $rowValue == 'city' )
									{
										$city = $worksheet->getCell($columnInFile.$row)->getValue();
									}
									
									if( $rowValue == 'state' )
									{
										$state = $worksheet->getCell($columnInFile.$row)->getValue();
									}
									
									if( $rowValue == 'zip' )
									{
										$zip = $worksheet->getCell($columnInFile.$row)->getValue();
									}
								}
							}
						}

						
						$type = 2;
						
						$office_phone_number = ltrim($office_phone_number, '1');
						$mobile_phone_number = ltrim($mobile_phone_number, '1');
						$home_phone_number = ltrim($home_phone_number, '1');
						
						if( strlen($office_phone_number) == 10 )
						{
							$type = 1;
						}
						else
						{
							$office_phone_number = null;
						}
						
						if( strlen($mobile_phone_number) == 10 )
						{
							$type = 1;
						}
						else
						{
							$mobile_phone_number = null;
						}
						
						if( strlen($home_phone_number) == 10 )
						{
							$type = 1;					
						}
						else
						{
							$home_phone_number = null;
						}

						if( $type == 1 )
						{
							//DNC AND DC/WN Scrubbing
							if( $company->scrub_settings > 0  )
							{
								//ON Customer WN
								if( $company->scrub_settings == 1 )
								{
									$existingDcwn = Dcwn::model()->find(array(
										'condition' => 'customer_id = :customer_id AND phone_number = :phone_number',
										'params' => array(
											':customer_id' => $customer->id,
											':phone_number' => $home_phone_number,
										),
									));
									
									if( $existingDcwn )
									{
										$dcWnCtr++;
										continue;
									}
								}
								
								//ON Customer DNC
								if( $company->scrub_settings == 2 )
								{
									$existingDnc = Dnc::model()->find(array(
										'condition' => 'customer_id = :customer_id AND phone_number = :phone_number',
										'params' => array(
											':customer_id' => $customer->id,
											':phone_number' => $home_phone_number,
										),
									));
									
									if( $existingDnc )
									{
										$dncCtr++;
										continue;
									}
								}
								
								//ON Customer BOTH
								if( $company->scrub_settings == 3 )
								{
									$existingDnc = Dnc::model()->find(array(
										'condition' => 'customer_id = :customer_id AND phone_number = :phone_number',
										'params' => array(
											':customer_id' => $customer->id,
											':phone_number' => $home_phone_number,
										),
									));
									
									if( $existingDnc )
									{
										$dncCtr++;
										continue;
									}
									
									$existingDcwn = Dcwn::model()->find(array(
										'condition' => 'customer_id = :customer_id AND phone_number = :phone_number',
										'params' => array(
											':customer_id' => $customer->id,
											':phone_number' => $home_phone_number,
										),
									));
									
									if( $existingDcwn )
									{
										$dcWnCtr++;
										continue;
									}
								}
								
								//ON COMPANY DNC
								if( $company->scrub_settings == 4 )
								{
									$existingDnc = Dnc::model()->find(array(
										'condition' => 'company_id = :company_id AND phone_number = :phone_number',
										'params' => array(
											':company_id' => $customer->company_id,
											':phone_number' => $home_phone_number,
										),
									));
									
									if( $existingDnc )
									{
										$dncCtr++;
										continue;
									}
								}
								
								//ON COMPANY WN
								if( $company->scrub_settings == 5 )
								{
									$existingDcwn = Dcwn::model()->find(array(
										'condition' => 'company_id = :company_id AND phone_number = :phone_number',
										'params' => array(
											':company_id' => $customer->company_id,
											':phone_number' => $home_phone_number,
										),
									));
									
									if( $existingDcwn )
									{
										$dcWnCtr++;
										continue;
									}
								}
								
								//ON COMPANY BOTH
								if( $company->scrub_settings == 6 )
								{
									$existingDnc = Dnc::model()->find(array(
										'condition' => 'company_id = :company_id AND phone_number = :phone_number',
										'params' => array(
											':company_id' => $customer->company_id,
											':phone_number' => $home_phone_number,
										),
									));
									
									if( $existingDnc )
									{
										$dncCtr++;
										continue;
									}
									
									$existingDcwn = Dcwn::model()->find(array(
										'condition' => 'company_id = :company_id AND phone_number = :phone_number',
										'params' => array(
											':company_id' => $customer->company_id,
											':phone_number' => $home_phone_number,
										),
									));
									
									if( $existingDcwn )
									{
										$dcWnCtr++;
										continue;
									}
								}
							}
							
							//Cellphone Scrubbing API
							// $cellphoneScrubApi = new CellphoneScrubApi;
							
							// if( $cellphoneScrubApi->process($home_phone_number) )
							// {
								// $cellphoneScrubCtr++;
								// continue;
							// }		
							
							$existingLead = Lead::model()->find(array(
								'condition' => 't.customer_id = :customer_id AND t.status !=4 AND ( 
									(office_phone_number = :office_phone_number AND office_phone_number != "" AND office_phone_number IS NOT NULL) OR 
									(office_phone_number = :mobile_phone_number AND office_phone_number != "" AND office_phone_number IS NOT NULL) OR 
									(office_phone_number = :home_phone_number AND office_phone_number != "" AND office_phone_number IS NOT NULL) OR 
									(mobile_phone_number = :office_phone_number AND mobile_phone_number != "" AND mobile_phone_number IS NOT NULL) OR
									(mobile_phone_number = :mobile_phone_number AND mobile_phone_number != "" AND mobile_phone_number IS NOT NULL) OR
									(mobile_phone_number = :home_phone_number AND mobile_phone_number != "" AND mobile_phone_number IS NOT NULL) OR
									(home_phone_number = :office_phone_number AND home_phone_number != "" AND home_phone_number IS NOT NULL) OR
									(home_phone_number = :mobile_phone_number AND home_phone_number != "" AND home_phone_number IS NOT NULL) OR
									(home_phone_number = :home_phone_number AND home_phone_number != "" AND home_phone_number IS NOT NULL) 
								)',
								'params' => array(
									':customer_id' => $customer_id,
									':office_phone_number' => $office_phone_number,
									':mobile_phone_number' => $mobile_phone_number,
									':home_phone_number' => $home_phone_number,
								),
							));
						}
						else
						{
							$existingLead = array();
						}
						
						if( !empty($existingLead) )
						{
							// echo $ctr.'.'.$existingLead->first_name.' '.$existingLead->last_name . ' => LIST ID: ' . $existingLead->list_id.' - '.$existingLead->list->name;			
							// echo '<br>';
							// echo 'firstDayOfMonth: ' .  date('Y-m-01');
							// echo '<br>';
							// echo 'lastDayOfMonth: ' . date('Y-m-t');
							// echo '<br>';
							// echo '<br>';
							// exit;
							
							// $existingImportHistoryThisMonth = LeadHistory::model()->find(array(
								// 'condition' => '
									// type = 4
									// AND lead_id = :lead_id 
									// AND DATE(date_created) >= :firstDayOfMonth 
									// AND DATE(date_created) <= :lastDayOfMonth
								// ',
								// 'params' => array(
									// 'lead_id' => $existingLead->id,
									// ':firstDayOfMonth' => date('Y-m-01'),
									// ':lastDayOfMonth' => date('Y-m-t')
								// ),
							// ));
							
							// echo 'existingImportHistoryThisMonth: ' . count($existingImportHistoryThisMonth);
							// echo '<br>';
							// exit;
							
							// if( empty($existingImportHistoryThisMonth) )
							// {
								// $leadHistory = new LeadHistory;
										
								// $leadHistory->setAttributes(array(
									// 'lead_id' => $existingLead->id,
									// 'agent_account_id' => null,
									// 'type' => 4,
								// ));	
								
								// if( $leadHistory->save(false) )
								// {
									// $existingLeadUpdatedCtr++;
								// }
							// }
							
							// save custom lead fields that are not in template
							if( $model->allow_custom_fields == 1 )
							{
								if( $highestColumn > 'M' )
								{
									foreach ( range('N', $highestColumn) as $columnLetter ) 
									{
										$customFieldName = $worksheet->getCell($columnLetter.'1')->getValue();
										$customFieldValue = $worksheet->getCell($columnLetter.$row)->getValue();

										$existingCustomData = LeadCustomData::model()->find(array(
											'condition' => '
												lead_id = :lead_id 
												AND list_id = :list_id
												AND field_name = :field_name
											',
											'params' => array(
												':lead_id' => $existingLead->id,
												':list_id' => $model->id,
												':field_name' => $customFieldName, 
											),
										));
										
										if( $existingCustomData )
										{
											$customData = $existingCustomData;
										}
										else
										{
											$customData = new LeadCustomData;
										}
										
										echo 'existingLead list ID: ' . $existingLead->list_id;
										echo '<br>';
										echo 'list_id: ' . $model->id;
										echo '<br>';
										echo 'customFieldName: ' . $customFieldName;
										echo '<br>';
										echo 'customFieldValue: ' . $customFieldValue;
										echo '<br>';
										echo '<br>';
										
										$customData->setAttributes(array(
											'lead_id' => $existingLead->id,
											'list_id' => $model->id,
											'field_name' => $customFieldName,
											'value' => $customFieldValue,
										));
										
										$customData->save(false);
									}
								}
								
								
								$memberNumberCustomData = LeadCustomData::model()->find(array(
									'with' => 'list',
									'condition' => '
										t.lead_id = :lead_id 
										AND t.list_id = :list_id
										AND t.field_name = :field_name
										AND list.status != 3
									',
									'params' => array(
										':lead_id' => $existingLead->id,
										':list_id' => $model->id,
										':field_name' => 'Member Number',
									),
									'order' => 't.date_created DESC',
								));
								
								if( $memberNumberCustomData )
								{
									$existingCustomDatas = LeadCustomData::model()->findAll(array(
										'condition' => '
											lead_id = :lead_id 
											AND list_id = :list_id
											AND member_number IS NULL
										',
										'params' => array(
											':lead_id' => $existingLead->id,
											':list_id' => $model->id,
										),
									));

									if( $existingCustomDatas )
									{
										foreach( $existingCustomDatas as $existingCustomData )
										{
											$existingCustomData->member_number = $memberNumberCustomData->value;
											
											$existingCustomData->save(false);
										}
									}
								}
							}
							
							

							// if( $existingLead->list_id == 9035 )
							// {
								// $HHRenewingJuneJulyAugust++;
							// }
							// elseif( $existingLead->list_id == 9508 )
							// {
								// $HhRenewAugandSept++;
							// }
							// else
							// {
								// $otherList++;
								
								// if( !in_array($existingLead->list_id, $otherListArray) )
								// {
									// $otherListArray[] = $existingLead->list_id;
								// }
							// }
							
							// $madeCallToday = LeadCallHistory::model()->find(array(
								// 'condition' => 'lead_id = :lead_id AND DATE(date_created) >= DATE("2017-07-05")',
								// 'params' => array(
									// ':lead_id' => $existingLead->id
								// ), 
							// ));
							
							// if( empty($madeCallToday) )
							// {
								// echo ' - no calls made today';
								
								// $existingLead->setAttributes(array(
									// 'list_id' => $model->id,
									// 'last_name' => $last_name,
									// 'first_name' => $first_name,
									// 'partner_first_name' => $partner_first_name,
									// 'partner_last_name' => $partner_last_name,
									// 'address' => $address1,
									// 'address2' => $address2,
									// 'city' => $city,
									// 'state' => $state,
									// 'zip_code' => $zip,
									// 'office_phone_number' => $office_phone_number,
									// 'mobile_phone_number' => $mobile_phone_number,
									// 'home_phone_number' => $home_phone_number,
									// 'email_address' => $email_address,
									// 'type' => $type,
									// 'language' => $model->language,
									// 'timezone' => AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $model->calendar->office->phone) ),
								// ));
								
								// $existingLead->number_of_dials = 0;
								// $existingLead->home_phone_dial_count = 0;
								// $existingLead->mobile_phone_dial_count = 0;
								// $existingLead->office_phone_dial_count = 0;
								// $existingLead->status = 1;

								// if( $existingLead->save(false) )
								// {
									// $existingLeadUpdatedCtr++;
								// }							
							// }
							// else
							// {
								// echo ' - made call today';
							// }
						}
						else
						{
							//create new lead
							echo $ctr.'.'.$first_name.' '.$last_name.' - no dup found';
							$newLeads++;
						}

						echo '<br><br>';
						
						$ctr++;
					}						
				}
				else
				{
					$result['status'] = 'error';
					$result['message'] = 'Invalid Template of List';
				}
			}
			
			// $transaction->commit();
		// }
		// catch(Exception $e)
		// {
			// print_r($e);
			// $transaction->rollback();
		// }
		
		echo '<br><br> existingLeadUpdatedCtr: ' . $existingLeadUpdatedCtr;
		echo '<br><br> newLeads: ' . $newLeads;
		echo '<br><br> HHRenewingJuneJulyAugust: ' . $HHRenewingJuneJulyAugust;
		echo '<br><br> HhRenewAugandSept: ' . $HhRenewAugandSept;
		echo '<br><br> otherList: ' . $otherList;
		echo '<br><br> cellphoneScrubCtr: ' . $cellphoneScrubCtr;
		echo '<br><br> customFieldCtr: ' . $customFieldCtr;
		
		echo '<br><br>';
		echo '<pre>';
		print_r($otherListArray);
		
	}

	public function actionServerName()
	{
		echo 'https://' . Yii::app()->getRequest()->serverName.'/ulap/index.php/changePassword';
	}

	public function actionCellPhoneScrub()
	{
		exit;
		
		$stateScrubs = StateInitialScrub::model()->findAll(array(
			'condition' => 'api_result="CELLULAR"',
		)); 
		
		if( $stateScrubs )
		{
			foreach( $stateScrubs as $stateScrub )
			{
				if( isset($stateScrub->lead) )
				{
					$lead = $stateScrub->lead;
					
					$history = new LeadHistory;

					$history->setAttributes(array(
						'lead_id' => $lead->id, 
						'content' => $lead->getFullName() . ' | '.ucfirst($stateScrub->lead_phone_type).' Phone Number | ('.substr($stateScrub->lead_phone_number, 0, 3).') '.substr($stateScrub->lead_phone_number, 3, 3).'-'.substr($stateScrub->lead_phone_number,6) . ' removed per state do not call cell phone scrub.',
						'type' => 8,
						'old_data' => json_encode($lead->attributes)
					));
					
					if( $history->save(false) )
					{
						if( $stateScrub->lead_phone_type == 'home' )
						{
							$lead->home_phone_number = null;
						}
						elseif( $stateScrub->lead_phone_type == 'office' )
						{
							$lead->office_phone_number = null;
						}
						else
						{
							$lead->mobile_phone_number = null;
						}
						
						if( $lead->home_phone_number == null && $lead->office_phone_number == null && $lead->mobile_phone_number == null )
						{
							$lead->status = 3;
						}
						
						if( $lead->save(false) )
						{
							echo 'LEAD ID: ' . $lead->id;
							echo '<br>';
						}
					}
				}
			}
		}
	}

	public function actionRecoverList()
	{
		exit;
		
		$leadCtr = 0;
		
		$list = Lists::model()->findByPk(11680);
		
		$list->status = 2;
		
		if( $list->save(false) )
		{
			$leads = Lead::model()->findAll(array(
				'condition' => 'list_id = :list_id AND type = 1 AND status = 4',
				'params' => array(
					':list_id' => $list->id,
				),
			));
			
			echo 'leads: ' . count($leads);
			
			echo '<br><br>';
			
			// exit;
			
			if( $leads )
			{
				foreach( $leads as $lead )
				{
					$lead->status = 1;
					
					if( $lead->save(false) )
					{
						$leadCtr++;
					}
				}
			}
		}
		
		echo '<br><br>leadCtr: ' . $leadCtr;
	}

	public function actionDownloadFile()
	{ 
		// $filename = '73221499284049-July Inactives ULAP import.xlsx';
		// $filename = '35971497309269-Decliner Graton June 2017.xlsx';
		// $filename = '92411499957999-Decliner Graton July 2017.xlsx';
		$filename = '31931496765621-Recently Inactive 6-5 ULAP import.xlsx';
		
		$filePath = Yii::getPathOfAlias('webroot') . '/leads/' . $filename;
			
		$allowDownload = false;
		
		if( file_exists($filePath) )
		{
			$allowDownload = true;
		}
		else
		{
			$filePath = Yii::getPathOfAlias('webroot') . '/fileupload/' . $filename;
			
			if( file_exists($filePath) )
			{
				$allowDownload = true;
			}
		}
		
		if ( $allowDownload )
		{
			// required for IE
			if(ini_get('zlib.output_compression')) { ini_set('zlib.output_compression', 'Off');	}
			
			$ctype="application/force-download";
			
			header("Pragma: public"); 
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private",false); // required for certain browsers
			header("Content-Type: $ctype");

			# change, added quotes to allow spaces in filenames, 
			header("Content-Disposition: attachment; filename=\"".basename($filePath)."\";" );
			
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: ".filesize($filePath));

			readfile("$filePath");
		} 
		else
		{
			// Do processing for invalid/non existing files here
			echo 'File not found.';
		}
	}

	public function actionDcwnPatch()
	{
		// exit;
		
		$callSql = '
			SELECT ulc.lead_id, ulc.list_id, ulc.customer_id, ulc.company_id, ulc.disposition, ulc.lead_phone_number, ul.first_name, ul.last_name, ul.status
			FROM `ud_lead_call_history` ulc
			LEFT JOIN `ud_lead` ul 
			ON ul.id = ulc.lead_id
			WHERE ulc.disposition IN ("Wrong Number", "Disconnected Number")
			AND ul.status = 1
			AND ulc.status !=4
		';
		
		$command = Yii::app()->db->createCommand($callSql);
		$calls = $command->queryAll();
		
		echo 'count: ' . count($calls);
		
		echo '<br><br>';
		
		// exit;  
		
		if( $calls )
		{
			$updatedLeadCtr = 0;
			$addedDcwnCtr = 0;
			
			foreach( $calls as $call )
			{
				$lead = Lead::model()->findByPk($call['lead_id']);
				
				if( $lead )
				{   
					// $lead->status = 3;
					
					// if( $lead->save(false) )
					// {
						$existingDcwn = Dcwn::model()->find(array(
							'condition' => 'phone_number = :phone_number',
							'params' => array(
								':phone_number' => $call['lead_phone_number'],
							),
						));
						
						if( empty($existingDcwn) )
						{
							$newDcwn = new Dcwn;
							
							$newDcwn->setAttributes(array(
								'lead_id' => $call['lead_id'],
								'skill_id' => $lead->list->skill_id,
								'company_id' => $call['company_id'],
								'phone_number' => $call['lead_phone_number'],
							));
							
							$newDcwn->customer_id = $call['customer_id'];
							
							if( $newDcwn->save(false) )
							{
								$addedDcwnCtr++;
							}
						}
						
						$updatedLeadCtr++;
					// }
				}
			}
			
			echo '<br>updatedLeadCtr: ' . $updatedLeadCtr;
			echo '<br>addedDcwnCtr: ' . $addedDcwnCtr;
		}
	}

	public function actionCompleteDncMatch()
	{
		$dncs = Dnc::model()->findAll(array(
			// 'limit' => 10
		));
		
		echo 'dncs:' . count($dncs);
		echo '<br><br>';
		
		$ctr = 0;
		$updatedLeads = 0;
		
		if( $dncs )
		{
			foreach( $dncs as $dnc )
			{
				echo $ctr++;
				echo '<br>';
				
				$lead = Lead::model()->find(array(
					'with' => 'customer',
					'condition' => '
						t.status = 1
						AND ( 
							t.home_phone_number = :phone_number
							OR t.office_phone_number = :phone_number
							OR t.mobile_phone_number = :phone_number
						)
						AND customer.company_id NOT IN (17,18,23)
					',
					'params' => array(
						':phone_number' => $dnc->phone_number
					),
				));
				
				if( $lead )
				{
					$updatedLeads++;
				}
			}
		}
		
		echo '<br><br>ctr: '. $ctr;
		echo '<br><br>updatedLeads: '. $updatedLeads;
	}
	
	public function actionServerInfo()
	{
		phpinfo();
	}

	public function actionDeletedLeadPatch()
	{
		// exit;
		
		$ctr = 0;
		
		$models = Lead::model()->findAll(array(
			'with' => 'list',
			'condition' => 't.status != 4 AND list.status=3',
		));
		
		echo 'models: ' . count($models);
		
		echo '<br><br>';
		
		if( $models )
		{
			foreach( $models as $model )
			{
				$model->status = 4;
				
				if( $model->save(false) )
				{
					echo $ctr++;
					echo '<br>';
				}
			}
		}
		
		echo '<br><br>ctr: ' . $ctr;
	}

	public function actionMergeResult()
	{
		exit;
		
		$existingQueueViewers = CustomerQueueViewer::model()->findAll(array(
			'limit' => 25,
			'order' => 'priority DESC',
		)); 
		
		$existingQueueViewers2 = CustomerQueueViewer::model()->findAll(array(
			'condition' => 'skill_id IN (36,37,38)',
			'order' => 'priority DESC',
		)); 
		
		$models = array_merge($existingQueueViewers, $existingQueueViewers2);
		
		echo 'models: ' . count($models);
		echo '<br><br>';
		
		if( $models )
		{
			echo '<pre>';
			
			foreach( $models as $model )
			{
				print_r($model->attributes);
			}
		}
	}	

	public function actionGetSpanishLeads()
	{
		$leads = Lead::model()->findAll(array(
			'with' => array('customer', 'list'),
			'condition' => '
				list.status != 3 
				AND t.status=1 
				AND t.language="Spanish"
				AND customer.company_id IN (9, 13)
				AND customer.status = 1
				AND customer.is_deleted = 0
			',
		));
		
		echo 'leads: ' . count($leads);
		echo '<br><br>';
		exit;
		
		$customerArray = array();
		
		$est = 0;
		$cst = 0;
		$mst = 0;
		$pst = 0;
		$hast = 0;
		$akst = 0;
		
		if( $leads )
		{
			foreach( $leads as $lead )
			{
				if( isset($lead->customer) && $lead->customer->account->status != 3 && $lead->customer->account->is_deleted == 0 )
				{
					$customer = $lead->customer;
					
					if( !in_array($customer->getFullName(), $customerArray) )
					{
						$customerArray[$customer->getFullName()] = $customer->getFullName().' => '.$customer->company->company_name;
					}
					
					if( !empty($lead->timezone) )
					{
						$timeZone = $lead->timezone;
					}
					else
					{
						$timeZone = AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $customer->phone) );
					}
					
					if( $timeZone == 'EST' )
					{
						$est++;
					}
					
					if( $timeZone == 'CST' )
					{
						$cst++;
					}
					
					if( $timeZone == 'MST' )
					{
						$mst++;
					}
					
					if( $timeZone == 'PST' )
					{
						$pst++;
					}
				
					if( $timeZone == 'AKST' )
					{
						$akst++;
					}
					
					if( $timeZone == 'HAST' )
					{
						$hast++;
					}
				}
			}
		}
		
		echo '<br><br>';
		echo '<br/> est: ' . $est;
		echo '<br/> cst: ' . $cst;
		echo '<br/> mst: ' . $mst;
		echo '<br/> pst: ' . $pst;
		echo '<br/> hast: ' . $hast;
		echo '<br/> akst: ' . $akst;
		echo '<br><br>';
		
		echo '<pre>';
			print_r($customerArray);
	}

	public function actionCharCount()
	{
		$str = 'BBQ will occur at 11am and 2pm and each of you can decide when you\'d like to eat! Hamburgers and Hot Dogs will be hot and ready on the grill for you to enjoy. This will be held north of the building, (in the same spot that we had the last event.)';
	
		echo strlen($str);
	}

	
	public function actionTestRetryTime()
	{
		date_default_timezone_set('America/Denver');
		
		$lead = Lead::model()->findByPk(1505316);
		
		$leadIsCallable = false;
		
		$latestCall = LeadCallHistory::model()->find(array(
			'condition' => 'lead_id = :lead_id',
			'params' => array(
				':lead_id' => $lead->id,
			),
			'order' => 'date_created DESC'
		));
		
		
		echo '<pre>';
			print_r($latestCall->attributes);
			
		echo '<br>';
		echo '<br>';
		
		// if( isset($lead->customer) )
		// {
			if( $latestCall )
			{
				if( $latestCall->is_skill_child == 1 && isset($latestCall->skillChildDisposition) )
				{
					echo 1;
					echo '<br>';
					
					if( time() >= (strtotime($latestCall->end_call_time) + $latestCall->skillChildDisposition->retry_interval) )
					{
						$leadIsCallable = true;
					}
				}
				elseif( $latestCall->is_skill_child == 0 && isset($latestCall->skillDisposition) )
				{
					echo 2;
					echo '<br>';
					
					echo '<pre>';
						print_r($latestCall->skillDisposition->attributes);
					
					echo '<br>';
					echo '<br>';
					echo 'current time: ' . date('m/d/Y g:i A');
					echo '<br>';
					echo 'with retry time: ' . date('m/d/Y g:i A', strtotime($latestCall->end_call_time) + $latestCall->skillDisposition->retry_interval);
					echo '<br>';
					echo 'without retry time: ' . date('m/d/Y g:i A', strtotime($latestCall->end_call_time));
					echo '<br>';
					echo 'retry interval: ' . $latestCall->skillDisposition->retry_interval;

					if( time() >= (strtotime($latestCall->end_call_time) + $latestCall->skillDisposition->retry_interval) )
					{
						$leadIsCallable = true;
					}
				}
				else
				{
					echo 3;
					echo '<br>';
					
					if ( strtotime($latestCall->end_call_time) <= strtotime('-12 hours') ) 
					{
						$leadIsCallable = true;
					}
				} 
			} 
		// } 
		
		echo '<br>';
		echo 'leadIsCallable: ' . $leadIsCallable;
	}

	public function actionWaxieLeadsPatch()
	{
		$totalUpdatedCtr = 0;
		
		$lists = Lists::model()->findAll(array(
			'condition' => 'skill_id IN (23) AND status !=3',
		));
		
		echo 'lists: '. count($lists);
				
		echo '<br><br>';
		
		// exit;
		
		if( $lists )
		{
			foreach( $lists as $list )
			{
				$updatedCtr = 0;
				
				$leads = Lead::model()->findAll(array(
					'condition' => 'list_id = :list_id AND status=1 AND number_of_dials >=3',
					'params' => array(
						':list_id' => $list->id
					),
				));
				
				echo $list->name . ' => leads: '. count($leads);
				echo '<br><br>';
				
				if( $leads )
				{
					foreach( $leads as $lead )
					{
						$lead->status = 3;
						
						if( $lead->save(false) )
						{
							echo $updatedCtr++;
							echo $totalUpdatedCtr++;
							echo '<br>';
						}
					}
				}
			}
		}
		
		echo '<br><br>totalUpdatedCtr: '.$totalUpdatedCtr;
	}

	public function actionAppointmentSetPatch()
	{
		$models = LeadCallHistory::model()->findAll(array(
			'condition' => '
				disposition="Appointment Set" 
				AND calendar_appointment_id IS NULL 
				AND date_created >= "2017-08-18 00:00:01" 
				AND date_created <= "2017-08-18 23:59:59" 
			',
		));
		
		// $models = LeadCallHistory::model()->findAll(array(
			// 'condition' => '
				// disposition="Location Conflict" 
				// AND calendar_appointment_id IS NULL 
				// AND date_created >= "2017-08-18 00:00:01" 
				// AND date_created <= "2017-08-18 23:59:59" 
			// ',
		// ));
		
		echo 'count: ' . count($models);
		
		echo '<br><br>';
		exit;
		
		if( $models )
		{
			foreach( $models as $model )
			{
				$latestAppointment = CalendarAppointment::model()->find(array(
					'condition' => '
						lead_id = :lead_id 
						AND title="APPOINTMENT SET"
						AND status !=4
					',
					'params' => array(
						':lead_id' => $model->lead_id,
					),
					'order' => 'date_created DESC',
				));
				
				// $latestAppointment = CalendarAppointment::model()->find(array(
					// 'condition' => '
						// lead_id = :lead_id 
						// AND title="LOCATION CONFLICT"
						// AND status !=4
					// ',
					// 'params' => array(
						// ':lead_id' => $model->lead_id,
					// ),
					// 'order' => 'date_created DESC',
				// ));
				
				if( $latestAppointment )
				{
					$model->calendar_appointment_id = $latestAppointment->id;
					$model->save(false);
					
					$emailMonitor = EmailMonitor::model()->find(array(
						'condition' => 'lead_call_history_id = :lead_call_history_id',
						'params' => array(
							':lead_call_history_id' => $model->id
						),
					));
					
					if( $emailMonitor )
					{
						echo 'callhistory ID: ' . $model->id;
						echo '<br>';
						echo 'calendar app ID: ' . $model->calendar_appointment_id;
						echo '<br>';
						echo '<br>';
						
						$emailMonitor->html_content = $model->getReplacementCodeValues();
						$emailMonitor->save(false);
					}
				}
			}
		}
	}

	public function actionResendEmail()
	{
		exit;
		
		$models = EmailMonitor::model()->findAll(array(
			'condition' => '
				disposition="Appointment Set"
				AND date_created >= "2017-08-18 00:00:01" 
				AND date_created <= "2017-08-18 23:59:59" 
				AND (
					html_content LIKE :match1
					OR html_content LIKE :match2
					OR html_content LIKE :match3
					OR html_content LIKE :match4
					OR html_content LIKE :match5
					OR html_content LIKE :match6
					OR html_content LIKE :match7
					OR html_content LIKE :match8
					OR html_content LIKE :match9
					OR html_content LIKE :match10
					OR html_content LIKE :match11
					OR html_content LIKE :match12
					OR html_content LIKE :match13
					OR html_content LIKE :match14
					OR html_content LIKE :match15
					OR html_content LIKE :match16
					OR html_content LIKE :match17
					OR html_content LIKE :match18
					OR html_content LIKE :match19
					OR html_content LIKE :match20
					OR html_content LIKE :match21
					OR html_content LIKE :match22
					OR html_content LIKE :match23
					OR html_content LIKE :match24
					OR html_content LIKE :match25
					OR html_content LIKE :match26
					OR html_content LIKE :match27
					OR html_content LIKE :match28
					OR html_content LIKE :match29
					OR html_content LIKE :match30
					OR html_content LIKE :match31
				)
			',
			'params' => array(
				':match1' => '%debra watkins%',
				':match2' => '%william baur%',
				':match3' => '%alesia alexander%',
				':match4' => '%shara lamar%',
				':match5' => '%michael allen%',
				':match6' => '%william cornett%',
				':match7' => '%jeremy griffel%',
				':match8' => '%frank bobek%',
				':match9' => '%john akers%',
				':match10' => '%christina fenwick%',
				':match11' => '%heather delgado%',
				':match12' => '%dravon adams%',
				':match13' => '%michael fries%',
				':match14' => '%lidia akers%',
				':match15' => '%shannon kilzer%',
				':match16' => '%alphonse di trolio%',
				':match17' => '%mark chance%',
				':match18' => '%patrick hogan%',
				':match19' => '%khadijah jones%',
				':match20' => '%john d hopkins%',
				':match21' => '%dennis mulvaney%',
				':match22' => '%lula dunmore%',
				':match23' => '%stacie davis%',
				':match24' => '%jesus perez%',
				':match25' => '%jeffrey hood%',
				':match26' => '%neptali cruz-morales%',
				':match27' => '%judith furnas%',
				':match28' => '%aislyn byrd%',
				':match29' => '%jeri feldhaus%',
				':match30' => '%bridget honor%',
				':match31' => '%jesse kirchner%',
			),
		));
		
		echo 'count: ' . count($models);
		
		echo '<br><br>';
		
		exit;
		
		if( $models )
		{
			$ctr = 1; 
			foreach( $models as $model )
			{
				$model->status = 0;
				
				if( $model->save(false) )
				{
					echo $ctr.'. '.$model->lead->getFullName();
					echo '<br>';
					
					$ctr++;
				}
			}
		}
	}

	public function actionCustomerCreditPatch()
	{
		Yii::import('application.vendor.*');
		require ('anet_php_sdk/AuthorizeNet.php');
		
		//live
		define("AUTHORIZENET_API_LOGIN_ID", "8Wtz3C6a");	
		define("AUTHORIZENET_TRANSACTION_KEY", "8YEBD7n3Z64r3x39");
		define("AUTHORIZENET_SANDBOX", false);

		$updatedCtr = 0;
	
		$creditHistories = CustomerCreditBillingHistory::model()->findAll(array(
			'with' => 'customerBilling',
			'condition' => '
				t.date_created >= "2017-09-01 00:00:00" 
				AND t.date_created <= "2017-09-01 23:59:59"
				AND customerBilling.transaction_type = "Charge"
				AND customerBilling.anet_responseCode = 1
				AND customerBilling.credit_amount = "0.00"
			',
			'params' => array(
				':credit_amount' => '0.00'
			),
			// 'limit' => 1,
		));
		
		echo 'count: ' . count($creditHistories);
		echo '<br><br>';
		// exit;
		
		$pendingExtraCharge = 0;
		$pendingRefund = 0;
		
		if( $creditHistories )
		{
			echo '<pre>';
			
			echo '<table border=1>';
			
			foreach( $creditHistories as $creditHistory )
			{
				// echo 'Customer name: ' . $creditHistory->customerBilling->customer->getFullName();
				// echo '<br>';
				// echo $creditHistory->customerCredit->description .': '. $creditHistory->customerCredit->amount;
				// echo '<br>';
				// echo 'Billing credit amount: ' . $creditHistory->customerBilling->credit_amount;
				
				// echo '<br><hr><br>';
				
				$transaction = $creditHistory->customerBilling;
				
				if( $creditHistory->customerCredit->amount > 0 )
				{
					$pendingRefund++;
				}
				else
				{
					$pendingExtraCharge++;
				}
				
				if( $transaction )
				{
					echo '<tr>';
						echo '<td>';
							echo $creditHistory->customerBilling->customer->getFullName();
						echo '</td>';
						
						echo '<td>';
							echo $creditHistory->customerCredit->amount;
						echo '</td>';
					echo '</tr>';
				}
			}
			
			echo '</table>';
		}
		
		echo '<br>';
		echo '<br>';
		echo 'pendingRefund: ' . $pendingRefund;
		echo '<br>';
		echo 'pendingExtraCharge: ' . $pendingExtraCharge;
		echo '<br>';
		
		echo '<br><br>updatedCtr: ' . $updatedCtr;
	}

	public function actionGetTransaction($transaction_id)
	{
		Yii::import('application.vendor.*');
		require ('anet_php_sdk/AuthorizeNet.php');
		
		define("AUTHORIZENET_API_LOGIN_ID", "8Wtz3C6a");	
		define("AUTHORIZENET_TRANSACTION_KEY", "8YEBD7n3Z64r3x39");
		define("AUTHORIZENET_SANDBOX", false);
		
		$request  = new AuthorizeNetTD;
		$response_TransactionDetails = $request->getTransactionDetails($transaction_id);
		
		if( $response_TransactionDetails->xml->messages->resultCode == 'Ok' )
		{
			$transaction_Details = $response_TransactionDetails->xml->transaction;
			
			echo '<pre>';
				print_r($transaction_Details);
			exit;
		}
	}

	public function actionAgentAssignmentPatch()
	{
		// exit;
		
		$agents = Account::model()->findAll(array(
			'condition' => 'account_type_id IN (2,14,15) AND status=1',
		));
		
		echo 'agents: ' . count($agents);
		
		echo '<br><br>';
		
		if( $agents )
		{
			foreach( $agents as $agent )
			{
				$companyIds = array();
				
				$accountAssignedSkills = AccountSkillAssigned::model()->findAll(array(
					'with' => 'skill',
					'condition' => 't.account_id = :account_id AND skill.status=1 AND skill.is_deleted=0',
					'params' => array(
						':account_id' => $agent->id,
					),
				));
				
				if( $accountAssignedSkills )
				{
					foreach( $accountAssignedSkills as $accountAssignedSkill )
					{
						$skillCompanies = SkillCompany::model()->findAll(array(
							'condition' => 'skill_id = :skill_id',
							'params' => array(
								':skill_id' => $accountAssignedSkill->skill_id
							),
						));
						
						if( $skillCompanies )
						{
							foreach( $skillCompanies as $skillCompany )
							{
								if( !in_array($skillCompany->company_id, $companyIds) )
								{
									$companyIds[] = $skillCompany->company_id;
								}
							}
						}
					}
				}
				
				$accountAssignedSkillChilds = AccountSkillChildAssigned::model()->findAll(array(
					'with' => 'skillChild',
					'condition' => 't.account_id = :account_id AND skillChild.status=1 AND skillChild.is_deleted=0',
					'params' => array(
						':account_id' => $agent->id,
					),
				));
				
				if( $accountAssignedSkillChilds )
				{
					foreach( $accountAssignedSkillChilds as $accountAssignedSkillChild )
					{
						$skillCompanies = SkillCompany::model()->findAll(array(
							'condition' => 'skill_id = :skill_id',
							'params' => array(
								':skill_id' => $accountAssignedSkillChild->skill_child_id
							),
						));
						
						if( $skillCompanies )
						{
							foreach( $skillCompanies as $skillCompany )
							{
								if( !in_array($skillCompany->company_id, $companyIds) )
								{
									$companyIds[] = $skillCompany->company_id;
								}
							}
						}
					}
				}
				
				if( !empty($companyIds) )
				{
					$companies = Company::model()->findAll(array(
						// 'condition' => 'status=1 AND is_deleted=0',
						'condition' => 't.id IN ("'.implode('","', $companyIds).'")',
						'order' => 'company_name ASC',
					));
				}
				
				echo $agent->getFullName();
				echo '<br>';
				
				// echo count($companies);
				// exit;
				
				if( $companies )
				{
					foreach( $companies as $company )
					{
						$existingAssignedCompany = AccountCompanyAssigned::model()->find(array(
							'condition' => 'account_id = :account_id AND company_id = :company_id',
							'params' => array(
								':account_id' => $agent->id,
								'company_id' => $company->id
							),
						));
						
						if( !$existingAssignedCompany ) 
						{
							$model = new AccountCompanyAssigned;
					
							$model->setAttributes(array(
								'account_id' => $agent->id,
								'company_id' => $company->id,
							));
							
							$model->save(false);
						}
						
						$customers = Customer::model()->findAll(array(
							'with' => 'account',
							'condition' => '
								account.status != 3 
								AND account.is_deleted = 0
								AND t.status != 3
								AND t.is_deleted = 0 
								AND t.company_id = :company_id
							',
							'params' => array(
								':company_id' => $company->id
							),
						));
						
						if( $customers )
						{
							foreach( $customers as $customer )
							{
								$existingAssignedCustomer = AccountCustomerAssigned::model()->find(array(
									'condition' => 'account_id = :account_id AND customer_id = :customer_id AND company_id = :company_id',
									'params' => array(
										':account_id' => $agent->id,
										':customer_id' => $customer->id,
										'company_id' => $customer->company_id
									),
								));
								
								if( !$existingAssignedCustomer )
								{
									$model2 = new AccountCustomerAssigned;
				
									$model2->setAttributes(array(
										'account_id' => $agent->id,
										'customer_id' => $customer->id,
										'company_id' => $customer->company_id,
									));
									
									$model2->save(false);
								}
							}
						}
						
					}
				}
			}
		}
		
	}

	public function actionDuplicateLeadPatch()
	{
		exit;
		
		$leads = Lead::model()->findAll(array(
			'condition' => 't.list_id="10709" AND status !=4',
			'group' => 't.home_phone_number',
			'order' => 't.number_of_dials',
		));
	
		echo 'count: ' . count($leads);
		
		echo '<br>';
		// exit;
		
		if( $leads )
		{
			foreach( $leads as $lead )
			{
				$dup = Lead::model()->find(array(
					'condition' => 't.list_id="10709" AND home_phone_number = :home_phone_number AND t.id != :id',
					'params' => array(
						':home_phone_number' => $lead->home_phone_number,
						':id' => $lead->id,
					),
				));
				
				if( $dup )
				{
					echo '<pre>';
					print_r($lead->attributes);
					print_r($dup->attributes);
					exit;
					
					// $dup->status = 4;
					
					// if( $dup->save(false) )
					// {
						// $dupCallHistories = LeadCallHistory::model()->findAll(array(
							// 'condition' => 'lead_id = :lead_id',
							// 'params' => array(
								// ':lead_id' => $dup->id,
							// ),
						// ));
						
						// if( $dupCallHistories )
						// {
							// foreach( $dupCallHistories as $dupCallHistory )
							// {
								// $lead->number_of_dials += 1;
								// $lead->save(false);

								// $dupCallHistory->lead_id = $lead->id;
								// $dupCallHistory->save(false);
							// }
						// }
						
						// $dupLeadHistories = LeadHistory::model()->findAll(array(
							// 'condition' => 'lead_id = :lead_id',
							// 'params' => array(
								// ':lead_id' => $dup->id,
							// ),
						// ));
						
						// if( $dupLeadHistories )
						// {
							// foreach( $dupLeadHistories as $dupLeadHistory )
							// {
								// $dupLeadHistory->lead_id = $lead->id;
								// $dupLeadHistory->save(false);
							// }
						// }
					// }
				}
			}
		}
	}

	public function actionCheckState()
	{
		//temp code to force certain customers to get no dials 
		$georgiaArecodeCodes = array('229', '404', '470', '478', '678', '706', '762', '770', '912');
			
		$northCarolinaAreaCodes = array('252', '336', '704', '828', '910', '919', '980');
		
		$southCarolinaAreaCodes = array('803', '843', '864');
		
		$texasAreaCodes = array('210', '214', '254', '281', '361', '409', '469', '512', '682', '713', '806', '817', '830', '832', '903', '915', '936', '940', '956', '972', '979');
		
		$louisianaAreaCodes = array('225', '318', '337', '504', '985');
		
		$floridaAreaCodes = array('305', '321', '352', '386', '407', '561', '727', '754', '772', '786', '813', '850', '863', '904', '941', '954');

		$condition = 'status = 1 AND available_leads > 0 AND priority >= 0 AND priority <= 10 AND next_available_calling_time NOT IN ("Goal Appointment Reached", "On Hold", "Cancelled", "Removed", "Decline Hold")';
		$condition .= ' OR customer_id IN (1021, 981, 1038, 1036, 1035, 1030, 1031, 875, 1049, 1966, 2011)';
		$condition .= ' OR skill_id IN (23)';
		
		$customerQueues = CustomerQueueViewer::model()->findAll(array(
			'condition' => $condition,
			// 'limit' => 50,
			// 'offset' => $customerPriorityCurrentOffset->value, 
			'order' => 'priority DESC'
		));
		
		$texasCustomers = array();
		$louisianaCustomers = array();
		$floridaCustomers = array();
		
		if( $customerQueues )
		{
			foreach( $customerQueues as $customerQueue )
			{
				$customer = $customerQueue->customer;

				if( !in_array($customer->id, array(613, 2084, 2136, 1908, 2068, 438, 198, 2102, 2176, 1579, 1209, 633, 2101, 1476, 1166, 1742, 1765, 931, 1846, 1625, 2177, 2071, 2144, 444, 1176, 2222, 2187, 2029, 482, 1943, 2002, 1817, 691, 2093, 762, 445, 631, 1791, 2243)) )
				{
					if( in_array(substr($customer->phone, 1, 3), $texasAreaCodes) || $customer->state == 43 )
					{
						$texasCustomers[] = $customerQueue->customer_name;
					}
					
					if( in_array(substr($customer->phone, 1, 3), $louisianaAreaCodes) || $customer->state == 18 )
					{
						$louisianaCustomers[] = $customerQueue->customer_name;
					}
					
					if( in_array(substr($customer->phone, 1, 3), $floridaAreaCodes) || $customer->state == 9 )
					{
						$floridaCustomers[] = $customerQueue->customer_name;
					}
				}
			}
		}
		
		echo '<pre>';
		
		echo 'TEXAS<br>';
			print_r($texasCustomers);
			
		echo '<br><br>';
		
		echo 'LOUSIANA<br>';
			print_r($louisianaCustomers);
			
		echo '<br><br>';
		
		echo 'FLORIDA<br>';
			print_r($floridaCustomers);
		
	}

	public function actionFixDeclines()
	{
		exit;
		
		$customerQueues = CustomerQueueViewer::model()->findAll(array(
			'condition' => 'contract_name="Farmers Life Specific"',
		));
		
		echo 'count: ' . count($customerQueues);
		
		echo '<br><br>';
		
		echo '<pre>';
		
		if( $customerQueues )
		{
			$ctr = 1;
			
			foreach( $customerQueues as $customerQueue )
			{
				echo $ctr.'. '.$customerQueue->customer_name;
				
				$octoberDeclineRecord = CustomerBilling::model()->find(array(
					'condition' => '
						customer_id = :customer_id 
						AND billing_period = "Oct 2017"
					',
					'params' => array(
						':customer_id' => $customerQueue->customer_id
					),
				));
				
				if( $octoberDeclineRecord )
				{
					echo '<br> has decline record';
					// echo '<br>';
					// print_r($octoberDeclineRecord->attributes);
					
					$octoberDeclineRecord->delete();
				}
				
				$customerSkill = CustomerSkill::model()->find(array(
					'condition' => '
						customer_id = :customer_id 
						AND contract_id = :contract_id 
						AND skill_id = :skill_id
						AND is_hold_for_billing = 1
					',
					'params' => array(
						':customer_id' => $customerQueue->customer_id,
						':contract_id' => $customerQueue->contract_id,
						':skill_id' => $customerQueue->skill_id
					),
				));
				
				if( $customerSkill )
				{
					echo '<br> has customer skill on hold';
					
					$customerSkill->is_hold_for_billing = 0;
					$customerSkill->save(false);
				}
				
				echo '<br>';
				echo '<br>';
				
				$ctr++;
			}
		}
	}

	public function actionManualAddRecycles()
	{
		$customer_id = 23;
		
		//dispo 1
		$leads = Lead::model()->findAll(array(
			'with' => array('list', 'list.skill'),
			'together' => true,
			'condition' => '
				t.customer_id = :customer_id 
				AND list.status = 1 
				AND t.type = 1 
				AND t.is_do_not_call = 0
				AND is_recycle_removed = 0
			',
			'params' => array(
				':customer_id' => $customer_id,
			),
			'limit' => 6
		));
		
		echo 'leads: ' . count($leads);
		exit;
		
		if( $leads )
		{
			foreach( $leads as $lead )
			{
				$lead->recycle_lead_call_history_id = 9;
				$lead->recycle_lead_call_history_disposition_id = 322;
				$lead->number_of_dials = 3;
				$lead->recycle_date = null;
				$lead->status = 3;
				$lead->save(false);
				
			}
		} 
	}

	public function actionIndigoSkyFirstDial()
	{
		$lists = Lists::model()->findAll(array(
			'condition' => '
				t.customer_id = :customer_id 
				AND t.skill_id IS NOT NULL 
				AND t.skill_id != ""
				AND t.skill_id = :skill_id 
				AND t.status = 1
				AND t.id IN ("11533", "11899", "11913")
			',
			'params' => array(
				':customer_id' => 2011,
				':skill_id' => 38
			),
		));
		
		echo 'lists: ' . count($lists);
		
		echo '<br><br>';
		
		if( $lists )
		{
			foreach( $lists as $list ) 
			{
				echo 'list: ' . $list->name;
				echo '<br>';
				
				switch( $list->lead_ordering )
				{
					default: case 1: $order = 'RAND()';
					case 2: $order = 't.last_name ASC'; break;
					case 3: $order = 't.custom_date ASC'; break;
					case 4: $order = 't.number_of_dials ASC'; break;
					case 5: $order = 't.specific_date ASC'; break;
				}
			
				//get callable leads
				if( in_array($customerSkill->skill_id, array(36,37,38,39)) )
				{
					$skillMaxDials = $customerSkill->skill->max_dials;
				}
				else
				{
					$skillMaxDials = $customerSkill->skill->max_dials * 3;
				}
				
				$leads = Lead::model()->findAll(array(
					'with' => array('list', 'list.skill'),
					'condition' => ' 
						list.status = 1 
						AND t.list_id IS NOT NULL
						AND t.list_id = :list_id
						AND t.type=1 
						AND t.status=1
						AND t.is_do_not_call = 0
						AND t.is_bad_number = 0
						AND t.number_of_dials = 0
						AND (
							t.recertify_date != "0000-00-00" 
							AND t.recertify_date IS NOT NULL 
							AND NOW() <= t.recertify_date
						)
						AND ( 
							t.home_phone_number IS NOT NULL
							OR t.office_phone_number IS NOT NULL
							OR t.mobile_phone_number IS NOT NULL
						)
					',
					'params' => array(
						':list_id' => $list->id,
					),
					'order' => $order,
				));
				
				echo 'leads: '. count($leads);
				echo '<br><br>';
			}
		}
	}

	public function actionCustomerAccountPermissionPatch()
	{
		exit;
		
		$account = Account::model()->findByPk(4460);
		CustomerAccountPermission::autoAddPermissionKey($account);
	}

	public function actionCheckDncStateHoliday()
	{
		$customer = Customer::model()->findByPk(2365);
		
		echo 'state: ' . $customer->state .' => '.State::model()->findByPk($customer->state)->name;
		
		echo '<br><br>';
		
		echo 'date: ' . date('Y-m-d');
		
		echo '<br><br>';
		
		$dncHolidayState = DncHolidayState::model()->find(array(
			'condition' => 'state = :state AND date = :date',
			'params' => array(
				':state' => $customer->state,
				':date' => date('Y-m-d')
			),
		));
		
		echo '<pre>';
		
		if( $dncHolidayState )
		{
			print_r($dncHolidayState->attributes);
		}
	}

	public function actionChuckchansiPatch()
	{
		$leads = Lead::model()->findAll(array(
			'condition' => '
				customer_id = :customer_id 
				AND type=1 
			',
			'params' => array(
				':customer_id' => 2374
			),
		));
		
		echo 'leads: ' . count($leads);
		
		echo '<br><br>';
	
		$leadCtr = 1;
		
		if( $leads )
		{
			foreach( $leads as $lead )
			{
				$multipleRoomOffer1 = LeadCustomData::model()->findAll(array(
					'condition' => '
						lead_id = :lead_id 
						AND field_name = "Room Offer 1"
					',
					'params' => array(
						':lead_id' => $lead->id
					),
				));
				
				if( count($multipleRoomOffer1) > 1 )
				{
					echo $lead->first_name.' '.$lead->last_name;
					echo '<br><br>';
					echo 'multipleRoomOffer1: ' . count($multipleRoomOffer1);
					echo '<br><br>';
					
					$ctr = 1;
					
					foreach( $multipleRoomOffer1 as $roomOffer1 )
					{
						$roomOffer1->list_id = 11968;
						$roomOffer1->field_name = 'Room Offer ' . $ctr;
						
						$roomOffer1->save(false);
						$ctr++;
					}
				}
				
				echo '<br>';
				echo $leadCtr;
				echo '<br>';
			}
		}
	}

	public function actionQueuePop()
	{
		$customerQueue = CustomerQueueViewer::model()->find(array(
			'condition' => 'customer_id = :customer_id',
			'params' => array(
				':customer_id' => 1694,
			),
		));
		
		$this->render('queuePop', array(
			'customerQueue' => $customerQueue,
		));
	}

	public function actionSubDispo()
	{
		$model = EmailMonitor::model()->find(array(
			'condition' => 'lead_id = :lead_id',
			'params' => array(
				':lead_id' => 1835420
			),
			'order' => 'date_created DESC', 
		));
		
		if( $model )
		{
			echo '<b>lead: </b>' . $model->lead->getFullName();
			echo '<br><br>';
			
			echo '<b>customer: </b>' . $model->customer->getFullName();
			echo '<br><br>';
			
			echo '<b>disposition: </b>' . $model->disposition;
			echo '<br><br>';
			
			echo '<b>disposition_id: </b>' . $model->disposition->skill_disposition_name;
			echo '<br><br>';
			
			if( isset($model->disposition->skillDispositionDetail) )
			{
				echo '<b>sub disposition: </b>' . $model->disposition->skillDispositionDetail->skill_disposition_detail_name;
			}
			
			echo '<br><br>';
			echo '<b>html_content: </b>';
			echo '<br><br>';
			echo $model->html_content;
		}
	}

	public function actionDataTabPatch()
	{
		$models = LeadCustomData::model()->findAll(array(
			'condition' => 'value = "Now -Nov 14 & again Dec 15 - Dec 31"',
		));
		
		echo 'count: ' . count($models);
		
		echo '<br><br>';
		
		$ctr = 0;
		
		if( $models )
		{
			foreach( $models as $model )
			{
				$model->value = 'Now - Dec 14 & again Dec 15 - Dec 31';
				
				if( $model->save(false) )
				{
					echo $ctr;
					echo '<br>';
				}
			}
		}
		
		echo '<hr><br>ctr: ' . $ctr;
	}

	public function actionCustomerContractLevelPatch()
	{
		exit;
		
		$customerQueues = CustomerQueueViewer::model()->findAll(array(
			'condition' => 'contract_id="52" AND skill_id="33"',
			// 'condition' => 'customer_id="1569"',
		));
		
		echo 'customerQueues:' . count($customerQueues);
		
		echo '<br><br>';
		
		if( $customerQueues )
		{
			$ctr = 1;
			$contractSubsidyLevelGroupIds = array();
			
			foreach( $customerQueues as $customerQueue )
			{
				$quantity = 0;
				$goal = 0;
				
				// $history = CustomerHistory::model()->find(array(
					// 'condition' => '
						// customer_id = :customer_id
						// AND page_name = "Customer Skill"
					// ',
					// 'params' => array(
						// ':customer_id' => $customerQueue->customer_id
					// ),
					// 'order' => 'date_created DESC',
				// ));
				
				// if( $history )
				// {
					// $quantity = explode('Quantity: ', $history->content);
					// $quantity = $quantity[1];
					// $quantity = explode(',', $quantity);
					// $quantity = $quantity[0];
					
					// $goal = explode('Goal: ', $history->content);
					// $goal = $goal[1];
					// $goal = explode(',', $goal);
					// $goal = $goal[0];
					
					// continue;
				// }
				
				$lastWorkingCustomerSkilllevels = CustomerSkillLevel::model()->findAll(array(
					'condition' => '
						customer_id = :customer_id
						AND status = 1
						AND customer_skill_contract_id = "52"
						AND contract_subsidy_level_group_id IN ("1169", "1181", "1183", "1185", "1187", "1189")
					',
					'order' => 'date_created DESC',
					'params' => array(
						':customer_id' => $customerQueue->customer_id,
					), 
				));
				
				if( $lastWorkingCustomerSkilllevels )
				{
					foreach( $lastWorkingCustomerSkilllevels as $lastWorkingCustomerSkilllevel )
					{
						$quantity = $lastWorkingCustomerSkilllevel->quantity;
						
						if( $lastWorkingCustomerSkilllevel->contract_subsidy_level_group_id == 1169 )
						{
							$goal = 10;
						}
						
						if( $lastWorkingCustomerSkilllevel->contract_subsidy_level_group_id == 1181 )
						{
							$goal = 15;
						}
						
						if( $lastWorkingCustomerSkilllevel->contract_subsidy_level_group_id == 1183 )
						{
							$goal = 20;
						}
						
						if( $lastWorkingCustomerSkilllevel->contract_subsidy_level_group_id == 1185 )
						{
							$goal = 25;
						}
						
						if( $lastWorkingCustomerSkilllevel->contract_subsidy_level_group_id == 1187 )
						{
							$goal = 30;
						}
						
						if( $lastWorkingCustomerSkilllevel->contract_subsidy_level_group_id == 1189 )
						{
							$goal = 40;
						}
						
						$valid = false;
						
						// if( $valid )
						if( $quantity > 0 && $goal > 0 )
						{
							$contractSubsidyLevel = ContractSubsidyLevel::model()->find(array(
								'condition' => '
									contract_id = "52" 
									AND column_name = "goal"
									AND column_value = :goal
								',
								'params' => array(
									':goal' => $goal
								),
							)); 
							
							if( $contractSubsidyLevel )
							{
								$existingCustomerSkillLevel = CustomerSkillLevel::model()->find(array(
									'condition' => '
										customer_id = :customer_id
										AND customer_skill_contract_id = "52"
										AND contract_subsidy_level_group_id = :contract_subsidy_level_group_id
									',
									'params' => array(
										':customer_id' => $customerQueue->customer_id,
										':contract_subsidy_level_group_id' => $contractSubsidyLevel->id
									),
								));
								
								if( !$existingCustomerSkillLevel )
								{
									$cs = CustomerSkill::model()->find(array(
										'condition' => '
											customer_id = :customer_id
											AND contract_id = :contract_id
											AND skill_id = :skill_id
										',
										'params' => array(
											':customer_id' => $customerQueue->customer_id,
											':contract_id' => $customerQueue->contract_id,
											':skill_id' => $customerQueue->skill_id,
										),
									));
									
									if( $cs )
									{
										$csl = new CustomerSkillLevel;
										$csl->customer_id = $customerQueue->customer_id;
										$csl->customer_skill_id = $cs->id;
										$csl->customer_skill_contract_id = $cs->contract_id;
										$csl->contract_subsidy_level_group_id = $contractSubsidyLevel->id;
										
										$csl->quantity = $quantity;
										
										$csl->status = CustomerSkillLevel::STATUS_ACTIVE;
															
										// echo '<pre>';
											// print_r($csl->attributes);
										// echo '</pre>';
										
										if( !$csl->save(false) )
										{
											print_r( $csl->getErrors() );
											exit;
										}
										else
										{
											echo $customerQueue->customer_name . ' => Customer Skill Level successfully created.';
											echo '<br>';
										}
									}
									else
									{
										echo $customerQueue->customer_name . ' => Customer Skill not found.';
										echo '<br>';
									}
								}
								else
								{
									echo $customerQueue->customer_name . ' => Customer Skill Level already exists.';
									echo '<br>';
								}
							}
						}
						else
						{
							echo $ctr.'. '.$customerQueue->customer_name . '('.$customerQueue->customer_id.')';
							echo '<br>';
							echo 'Quantity: ' . $quantity;
							echo '<br>';
							echo 'Goal: ' . $goal;
							echo '<br>';
							echo 'lastWorkingCustomerSkilllevels: ' . count($lastWorkingCustomerSkilllevels);
							echo '<br>';
							echo '<br>';
							
							$ctr++;
						}
					}
				}
			}
		}
	}

	public function actionCreditIssue()
	{
		exit;
		
		$models = CustomerBilling::model()->findAll(array(
			'condition' => '
				billing_period = :billing_period 
				AND transaction_type = "Charge"
				AND anet_responseCode = 1
				AND billing_type = "Service Fee"
				AND customer_id NOT IN (2367)
			',
			'params' => array(
				':billing_period' => date('M Y'),
			),
		));
		
		if( $models )
		{
			Yii::import('application.vendor.*');
			require ('anet_php_sdk/AuthorizeNet.php');
			
			//sandbox
			// define("AUTHORIZENET_API_LOGIN_ID", "5CzTeH72f98D");	
			// define("AUTHORIZENET_TRANSACTION_KEY", "8n25bCL72432SpR9");	
			// define("AUTHORIZENET_SANDBOX", true);
			
			//live
			define("AUTHORIZENET_API_LOGIN_ID", "8Wtz3C6a");	
			define("AUTHORIZENET_TRANSACTION_KEY", "8YEBD7n3Z64r3x39");
			define("AUTHORIZENET_SANDBOX", false);
			
			$ctr = 0;
			$grandTotalCreditAmount = 0;
		
			foreach( $models as $model )
			{
				// $skillStatus = 'Active';
				
				// $customerSkill = CustomerSkill::model()->find(array(
					// 'condition' => '
						// customer_id = :customer_id
						// AND contract_id = :contract_id
					// ',
					// 'params' => array(
						// ':customer_id' => $model->customer_id,
						// ':contract_id' => $model->contract_id
					// ),
					// 'order' => 'date_created DESC'
				// ));
				
				// if( !empty($customerSkill->end_month) && date('Y', strtotime($customerSkill->end_month)) >= 2015 )
				// {
					// if( time() >= strtotime($customerSkill->end_month) )
					// {
						// $skillStatus = 'Cancelled';
					// }
				// }
				
				$existingBillingForCurrentMonthVoidorRefund = CustomerBilling::model()->find(array(
					'condition' => '
						customer_id = :customer_id 
						AND contract_id = :contract_id
						AND anet_responseCode = 1
						AND reference_transaction_id = :reference_transaction_id
						AND (
							transaction_type = "Void"
							OR transaction_type = "Refund"
							OR transaction_type = "Partial Refund"
						)
					',
					'params' => array(
						':customer_id' => $model->customer_id,
						':contract_id' => $model->contract_id,
						':reference_transaction_id' => $model->id,
					),
					'order' => 'date_created DESC'
				)); 
				
				if( !$existingBillingForCurrentMonthVoidorRefund )
				{
					$customerCredits = CustomerCredit::model()->findAll(array(
						'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND status=1',
						'params' => array(
							':customer_id' => $model->customer_id,
							':contract_id' => $model->contract_id,
						),
					));
					
					if( $customerCredits )
					{
						$remainingCredit = 0;
						$creditNames = array();
					
						foreach( $customerCredits as $customerCredit )
						{
							$creditStartDate = date($customerCredit->start_year.'-'.$customerCredit->start_month.'-01');
								
							if( $customerCredit->type == 2 ) //month range
							{
								if( $customerCredit->end_month == '02' )
								{
									$creditEndDate = date($customerCredit->end_year.'-'.$customerCredit->end_month.'-28');
								}
								elseif( $customerCredit->end_month == '12' )
								{
									$creditEndDate = date($customerCredit->end_year.'-'.$customerCredit->end_month.'-31');
								}
								else
								{
									$creditEndDate = date($customerCredit->end_year.'-'.$customerCredit->end_month.'-t');
								}
								
								if( $customerCredit->start_month >= $customerCredit->end_month )
								{
									$creditEndDate = date('Y-m-d', strtotime('+1 year', strtotime($creditEndDate)));
								}
							}
							else
							{
								if( $customerCredit->start_month == '02' )
								{
									$creditEndDate = date($customerCredit->start_year.'-'.$customerCredit->start_month.'-28');
								}
								elseif( $customerCredit->start_month == '12' )
								{
									$creditEndDate = date($customerCredit->start_year.'-'.$customerCredit->start_month.'-31');
								}
								else
								{
									$creditEndDate = date($customerCredit->start_year.'-'.$customerCredit->start_month.'-t');
								}
							}
							 
							if( (time() >= strtotime($creditStartDate)) && (time() <= strtotime($creditEndDate)) )
							{
								$existingCreditHistory = CustomerCreditBillingHistory::model()->find(array(
									'condition' => '
										customer_id = :customer_id
										AND customer_credit_id = :customer_credit_id
										AND customer_billing_id = :customer_billing_id
									',
									'params' => array(
										':customer_id' => $model->customer_id,
										':customer_credit_id' => $customerCredit->id,
										':customer_billing_id' => $model->id 
									),
								));
								
								if( !$existingCreditHistory )
								{
									$remainingCredit += $customerCredit->amount;
								}
								
								$creditNames[$customerCredit->id] = array(
									'description' => $customerCredit->description,
									'date' => $creditStartDate.' to '.$creditEndDate,
									'amount' => $customerCredit->amount,
									'status' => empty($existingCreditHistory) ? 'Not used' : 'Used' 
								);
							}
						}
						
						$remainingCredit = number_format($remainingCredit, 2);
						
						if( $creditNames && $remainingCredit != 0 && $model->credit_amount != $remainingCredit )
						{
							$partialRefund = new CustomerBilling;
		
							$transaction = CustomerBilling::model()->findByPk($model->id);

							$partialRefund->setAttributes(array(
								'customer_id' => $transaction->customer_id,
								'account_id' => $authAccount->id,
								'customer_id' => $transaction->customer_id,
								'credit_card_id' => $transaction->credit_card_id,
								'contract_id' => $transaction->contract_id,
								'amount' => $remainingCredit,
								'reference_transaction_id' => $transaction->id,
								'anet_transId' => time(),
								'description' => 'January 2018 Missing Credit',
								'transaction_type' => 'Partial Refund',
							));
							
							if( $partialRefund->save(false) )
							{	
								$updateModel = CustomerBilling::model()->findByPk($partialRefund->id);
							
								$authorizeTransaction = new AuthorizeNetAIM;
								$authorizeTransaction->setSandbox(AUTHORIZENET_SANDBOX);
								$response = $authorizeTransaction->credit($transaction->anet_transId, $remainingCredit, $transaction->credit_card_number);

								if ($response->approved)
								{
									$request  = new AuthorizeNetTD;
									$response_TransactionDetails = $request->getTransactionDetails($response->transaction_id);
									
									if($response_TransactionDetails->xml->messages->resultCode == 'Ok')
									{
										$transaction_Details = $response_TransactionDetails->xml->transaction;
										$order = $transaction_Details->order;
										$anetCustomer = $transaction_Details->customer;
										$billTo = $transaction_Details->billTo;
										
										$billing_Date = date('Y-m-d H:i:s', strtotime($transaction_Details->submitTimeUTC));
										
										if($updateModel)
										{
											$updateModel->setAttributes(array(
												'anet_transId' => $transaction_Details->transId,
												'anet_invoiceNumber' => $order->invoiceNumber,
												'anet_submitTimeUTC' => $transaction_Details->submitTimeUTC,
												'anet_submitTimeLocal' => $transaction_Details->submitTimeLocal,
												'anet_transactionType' => $transaction_Details->transactionType,
												'anet_transactionStatus' =>$transaction_Details->transactionStatus,
												'anet_responseCode' => $transaction_Details->responseCode,
												'anet_responseReasonCode'=> $transaction_Details->responseReasonCode,
												'anet_responseReasonDescription'=> $transaction_Details->responseReasonDescription,
												'anet_authCode'=> $transaction_Details->authCode,
												'anet_AVSResponse'=> $transaction_Details->AVSResponse,
												'anet_cardCodeResponse'=> $transaction_Details->cardCodeResponse,
												'anet_authAmount'=> $transaction_Details->authAmount,
												'anet_settleAmount'=> $transaction_Details->settleAmount,
												'anet_taxExempt'=> $transaction_Details->taxExempt,
												'anet_customer_Email'=> $anetCustomer->email,
												'anet_billTo_firstName'=> $billTo->firstName,
												'anet_billTo_lastName'=> $billTo->lastName,
												'anet_billTo_address'=> $billTo->address,
												'anet_billTo_city'=> $billTo->city,
												'anet_billTo_state'=> $billTo->state,
												'anet_billTo_zip'=> $billTo->zip,
												'anet_recurringBilling' => $transaction_Details->recurringBilling,
												'anet_product' => $transaction_Details->product,
												'anet_marketType' => $transaction_Details->marketType
											));
											
											if($updateModel->save(false))
											{
												echo 'success';
												echo '<br>';
												echo 'Transaction refunded.';
												echo '<br>';
												echo '<br>';
												// exit;
											}
											else
											{
												echo 'error';
												echo '<br>';
												echo 'Database error. customer => '. $model->customer_id;
												exit;
											}
										}
										else
										{
											echo 'error';
											echo '<br>';
											echo 'Database error. customer => '. $model->customer_id;
											exit;
										}
									}
									else
									{
										echo 'error';
										echo '<br>';
										echo 'Database error. customer => '. $model->customer_id;
										exit;
									}
								}
								else
								{
									echo 'error';
									echo '<br>';
									echo 'Transaction error: ' . $response->response_reason_code . ' - ' . $response->response_reason_text;
									
									if( $updateModel )
									{
										$updateModel->anet_transId = $response->response_reason_code.': '.$response->response_reason_text;
										$updateModel->save(false);
									}
									
									exit;
								}
							}
							else
							{
								echo 'error';
								echo '<br>';
								echo 'Database error.';
								exit;
							}
							
							$grandTotalCreditAmount += $remainingCredit;
							echo $ctr++;
							echo '<br>';
						}
					}
				}
			}
		}
		
		echo '<br><br>ctr: ' . $ctr;
		echo '<br><br>grandTotalCreditAmount: $' . number_format($grandTotalCreditAmount, 2);
	}

	public function actionUpdateFederalHolidayDials()
	{
		date_default_timezone_set('America/Denver');
		
		echo 'Process started at ' . date('g:i A');
		
		echo '<br><br>';
		
		$models = DncHolidayFederal::model()->findAll(array(
			'condition' => 'status = 1',
			'order' => 'date ASC',
		));
		
		echo 'models: ' . count($models);
		
		echo '<br><br>';
		
		if( $models )
		{
			foreach( $models as $model )
			{
				$dialCount = LeadCallHistory::model()->count(array(
					'condition' => '
						status=1 
						AND DATE(date_created) = DATE(:date) 
						AND end_call_time > start_call_time 
						AND lead_id IS NOT NULL 
						AND disposition_id IS NOT NULL
					',
					'params' => array(
						':date' => $model->date
					),
				));
				
				$model->dials = $dialCount;
				
				if( $model->save(false) )
				{
					echo $ctr++;
					echo '<br>';
				}
			}
		}
		
		echo '<br><br>ctr: ' . $ctr;
		
		echo '<br><br>';
		
		echo 'Process ended at ' . date('g:i A');
	}
	
	public function actionUpdateStateHolidayDials()
	{
		date_default_timezone_set('America/Denver');
		
		echo 'Process started at ' . date('g:i A');
		
		echo '<br><br>';
		
		$models = DncHolidayState::model()->findAll(array(
			'condition' => 'status = 1',
			'order' => 'date ASC',
		));
		
		echo 'models: ' . count($models);
		
		echo '<br><br>';
		
		if( $models )
		{
			foreach( $models as $model )
			{
				$dialCount = LeadCallHistory::model()->count(array(
					'with' => 'customer',
					'condition' => '
						t.status=1 
						AND DATE(t.date_created) = DATE(:date) 
						AND t.end_call_time > t.start_call_time 
						AND t.lead_id IS NOT NULL 
						AND t.disposition_id IS NOT NULL
						AND t.disposition NOT IN ("Appointment Confirmed", "Appointment Confirm - No Answer", "Appointment Confirmed - Left Message")
						AND customer.state = :state
					',
					'params' => array(
						':date' => $model->date,
						':state' => $model->state
					),
				));
				
				$model->dials = $dialCount;
				
				if( $model->save(false) )
				{
					echo $ctr++;
					echo '<br>';
				}
			}
		}
		
		echo '<br><br>ctr: ' . $ctr;
		
		echo '<br><br>';
		
		echo 'Process ended at ' . date('g:i A');
	}

	public function actionCheckAgent()
	{
		$authAccount = Account::model()->findByPk(4528);
		
		$assignedSkillIds = array();
		$assignedSkillChildIds = array();
		$assignedLanguageIds = array();
		$assignedCustomerIds = array();

		$assignedSkills = AccountSkillAssigned::model()->findAll(array(
			'condition' => 'account_id = :account_id',
			'params' => array(
				':account_id' => $authAccount->id,
			),
		));
		
		$assignedSkillChilds = AccountSkillChildAssigned::model()->findAll(array(
			'condition' => 'account_id = :account_id',
			'params' => array(
				':account_id' => $authAccount->id,
			),
		));
		
		$assignedLanguages = AccountLanguageAssigned::model()->findAll(array(
			'condition' => 'account_id = :account_id',
			'params' => array(
				':account_id' => $authAccount->id,
			),
		));

		
		if( $assignedSkills )
		{
			foreach( $assignedSkills as $assignedSkill )
			{
				$assignedSkillIds[] = $assignedSkill->skill_id;
			}
		}
		
		if( $assignedSkillChilds )
		{
			foreach( $assignedSkillChilds as $assignedSkillChild )
			{
				$assignedSkillChildIds[] = $assignedSkillChild->skill_child_id;
			}
		}
		
		$assignedCustomers = AccountCustomerAssigned::model()->findAll(array(
			'condition' => 'account_id = :account_id',
			'params' => array(
				':account_id' => $authAccount->id,
			),
		));
		
		if( $assignedCustomers )
		{
			foreach( $assignedCustomers as $assignedCustomer )
			{
				$assignedCustomerIds[] = $assignedCustomer->customer_id;
			}
		}
		
		echo '<pre>';
		
		echo 'assignedSkillIds: <br>';
			print_r($assignedSkillIds);
			
		echo '<br><br>';
		
		echo 'assignedSkillChildIds: <br>';
			print_r($assignedSkillChildIds);
			
		echo '<br><br>';
		
		echo 'assignedCustomerIds: <br>';
			print_r($assignedCustomerIds);
	}

	public function actionRemoveLeads()
	{
		exit;
		
		$models = Lead::model()->findAll(array(
			'condition' => 'list_id IN ("17185")',
		));
		
		echo 'models: ' . count($models);
		
		echo '<br><br>';
		
		if( $models )
		{
			foreach( $models as $model )
			{
				if( $model->delete() )
				{
					echo $ctr++;
					echo '<br>';
				}
			}
		}
		
		echo '<br><br>';
		
		echo 'ctr: ' . $ctr;
		
		echo '<br><br>';
		echo '<br><br>';
		
		$models = LeadJunk::model()->findAll(array(
			'condition' => 'list_id IN ("17185")',
		));
		
		echo 'models: ' . count($models);
		
		echo '<br><br>';
		
		if( $models )
		{
			foreach( $models as $model )
			{
				if( $model->delete() )
				{
					echo $ctr++;
					echo '<br>';
				}
			}
		}
		
		echo '<br><br>';
		
		echo 'ctr: ' . $ctr;
	}

	public function actionPermissionPatch()
	{
		$modules = CompanyPermission::permissionKeys(13);
			
		echo '<pre>';
			print_r($modules);
		exit;
		
		$account = Account::model()->findByPk(4120);
		
		foreach($modules as $moduleKey => $module)
		{
			##visible
			$modulePermissionVisible = CustomerAccountPermission::model()->find(array(
				'condition' => '
					account_id = :account_id
					AND permission_key = :permission_key
					AND permission_type = :permission_type
				',
				'params' => array(
					'account_id' => $account->id,
					':permission_key' => $moduleKey,
					':permission_type' => 'visible'
				),
			));
			
			if(empty($modulePermissionVisible))
			{
				$mPV = new CustomerAccountPermission;
				$mPV->account_id = $account->id;
				$mPV->permission_key = $moduleKey;
				$mPV->permission_type = 'visible';
				
				if(!$mPV->save(false))
				{
					echo 'Error in Permission Visible'; exit;
				}
			}
			
			##edit
			
			$modulePermissionEdit = CustomerAccountPermission::model()->find(array(
				'condition' => '
					account_id = :account_id
					AND permission_key = :permission_key
					AND permission_type = :permission_type
				',
				'params' => array(
					'account_id' => $account->id,
					':permission_key' => $moduleKey,
					':permission_type' => 'edit'
				),
			));
			
			if( strpos($moduleKey, 'field') !== false || strpos($moduleKey, 'checkbox') !== false || strpos($moduleKey, 'dropdown') !== false ) 
			{
				
				if(empty($modulePermissionEdit))
				{
					$mPE = new CustomerAccountPermission;
					$mPE->account_id = $account->id;
					$mPE->permission_key = $moduleKey;
					$mPE->permission_type = 'edit';
					
					if(!$mPE->save(false))
					{
						echo 'Error in Permission Edit'; exit;
					}
				}
			}
			
			##report
			$modulePermissionDirectReport = CustomerAccountPermission::model()->find(array(
				'condition' => '
					account_id = :account_id
					AND permission_key = :permission_key
					AND permission_type = :permission_type
				',
				'params' => array(
					'account_id' => $account->id,
					':permission_key' => $moduleKey,
					':permission_type' => 'only_for_direct_reports'
				),
			));
			
			if( isset($module['has_direct_report_checkbox']) ) 
			{
				if(empty($modulePermissionDirectReport))
				{
					$mPR = new CustomerAccountPermission;
					$mPR->account_id = $account->id;
					$mPR->permission_key = $moduleKey;
					$mPR->permission_type = 'only_for_direct_reports';
					
					if(!$mPR->save(false))
					{
						echo 'Error in Direct Report'; exit;
					}
				}
			}
			
			##sub modules###
			if( !empty($module['subModules']) )
			{
				foreach( $module['subModules'] as $childModuleKey => $childModule )
				{
					$childModulePermissionVisible = CustomerAccountPermission::model()->find(array(
						'condition' => '
							account_id = :account_id
							AND permission_key = :permission_key
							AND permission_type = :permission_type
						',
						'params' => array(
							'account_id' => $account->id,
							':permission_key' => $childModuleKey,
							':permission_type' => 'visible'
						),
					));
					
					if(empty($childModulePermissionVisible))
					{
						$mPV = new CustomerAccountPermission;
						$mPV->account_id = $account->id;
						$mPV->permission_key = $childModuleKey;
						$mPV->permission_type = 'visible';
						
						if(!$mPV->save(false))
						{
							echo 'Error in Sub module Permission Visible'; exit;
						}
					}
			
					
					$childModulePermissionEdit = CustomerAccountPermission::model()->find(array(
						'condition' => '
							account_id = :account_id
							AND permission_key = :permission_key
							AND permission_type = :permission_type
						',
						'params' => array(
							'account_id' => $account->id,
							':permission_key' => $childModuleKey,
							':permission_type' => 'edit'
						),
					));
					
					if( strpos($childModuleKey, 'field') !== false || strpos($childModuleKey, 'checkbox') !== false || strpos($childModuleKey, 'dropdown') !== false )
					{
						if(empty($childModulePermissionEdit))
						{
							$mPE = new CustomerAccountPermission;
							$mPE->account_id = $account->id;
							$mPE->permission_key = $childModuleKey;
							$mPE->permission_type = 'edit';
							
							if(!$mPE->save(false))
							{
								echo 'Error in Sub Module Permission Edit'; exit;
							}
						}
					}
					
					$childModulePermissionDirectReport = CustomerAccountPermission::model()->find(array(
						'condition' => '
							account_id = :account_id
							AND permission_key = :permission_key
							AND permission_type = :permission_type
						',
						'params' => array(
							'account_id' => $account->id,
							':permission_key' => $childModuleKey,
							':permission_type' => 'only_for_direct_reports'
						),
					));
					
					if( isset($childModule['has_direct_report_checkbox']) )
					{
						if(empty($childModulePermissionDirectReport))
						{
							$mPR = new CustomerAccountPermission;
							$mPR->account_id = $account->id;
							$mPR->permission_key = $childModuleKey;
							$mPR->permission_type = 'only_for_direct_reports';
							
							if(!$mPR->save(false))
							{
								echo 'Error in Sub module Direct Report'; exit;
							}
						}
					}
				}
			}
		}
	}

	public function actionSqlYogPasswordDecode()
	{
		foreach(str_split(base64_decode('YOUR_ENCODED_PASS_HERE')) as $chr)
			echo chr(((($chr = ord($chr)) << 1) & 0xFF) | ($chr >> (8 - 1)));
	}

	public function actionGetModule()
	{
		echo Yii::app()->controller->module->id;
	}

	public function actionAssignSkill()
	{
		exit;
		
		foreach( range(5338, 5349) as $accountId )
		{
			$model = new AccountSkillAssigned;
			$model->setAttributes(array(
				'account_id' => $accountId,
				'skill_id' => 36
			));
			$model->save(false);
		}
	}

	public function actionGetLeadTimeZone()
	{
		$ctr = 0;
		
		$list = Lists::model()->findByPk(14599);
	
		$leads = Lead::model()->findAll(array(
			'condition' => 'list_id = "14599" AND type=1 AND timezone IS NULL',
		));
		
		echo count($leads);
		exit;
		
		if( $leads )
		{
			foreach( $leads as $lead )
			{
				//area code assignment checkbox - follow leads phone instead of customers office phone to determine the timezone
				if( $lead->timezone == null )
				{
					if( $list->allow_area_code_assignment == 1 )
					{
						if( $lead->office_phone_number != null )
						{
							$lead->timezone = AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $lead->office_phone_number) );
						}
						
						if( $lead->mobile_phone_number != null )
						{
							$lead->timezone = AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $lead->mobile_phone_number) );
						}
						
						if( $lead->home_phone_number != null )
						{
							$lead->timezone = AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $lead->home_phone_number) );
						}
					}
					
					if( $lead->timezone == null )
					{
						$lead->timezone = $lead->customer->phone_timezone;
					}
					
					if( $lead->save(false) )
					{
						echo $ctr++;
					}
				}
			}
		}
	
		echo '<br><br>';
		echo 'ctr: ' . $ctr;
	}

	public function actionResetQueueAndLeads()
	{
		echo 'started: ' . date('g:i A');
		
		//check for customer queue without agent and set their dials until reset back to 20
		CustomerQueueViewer::model()->updateAll(array('dials_until_reset' => 20), 'dials_until_reset > 0 AND dials_until_reset < 20 AND (call_agent IS NULL OR call_agent="")');				
		
		LeadHopper::model()->updateAll(array('status' => 'DONE'), 'type IN (3,6,7) AND status = "INCALL" AND agent_account_id IS NULL');	
		
		echo '<br><br>';
		
		echo 'ended: ' . date('g:i A');
	}

	public function actionCheckPtoForms()
	{
		$approvedPtoForms = AccountPtoForm::model()->findAll(array(
			'condition' => 'account_id = :account_id AND status=1',
			'params' => array(
				':account_id' => 4,
			),
		));
		
		if($approvedPtoForms)
		{
			foreach( $approvedPtoForms as $approvedPtoForm )
			{
				if( $approvedPtoForm->is_full_shift == 1 )
				{
					$start_date = date('Y-m-d', strtotime($approvedPtoForm->date_of_request_start)) .'T'. date('08:00:00');
					$end_date = date('Y-m-d', strtotime($approvedPtoForm->date_of_request_end)) .'T'. date('20:00:00');
				} 
				else
				{
					$start_date = date('Y-m-d', strtotime($approvedPtoForm->date_of_request_start)) .'T'. date('H:i:s', strtotime($approvedPtoForm->off_hour_from.':'.$approvedPtoForm->off_min_from.' '.$approvedPtoForm->off_md_from ));
					$end_date = date('Y-m-d', strtotime($approvedPtoForm->date_of_request_end)) .'T'. date('H:i:s', strtotime($approvedPtoForm->off_hour_to.':'.$approvedPtoForm->off_min_to.' '.$approvedPtoForm->off_md_to ));
				}			
				
				if( $approvedPtoForm->date_of_request_start == $approvedPtoForm->date_of_request_end )
				{
					$result['events'][] = array(
						'id' => 'pto-form-'.$approvedPtoForm->id,
						'start_date' => $start_date,
						'end_date' => $end_date,
						'title' => !empty($approvedPtoForm->reason_for_request) ? $approvedPtoForm->reason_for_request : 'Time Off Request',
						'color' => '#D6487E',
						'is_custom' => 2,
						'allDay' => false,
					);
				}
				else
				{
					if( $approvedPtoForm->is_full_shift == 1 )
					{
						$ptoStartDate = strtotime($approvedPtoForm->date_of_request_start);
						// $ptoStartDate = strtotime('+1 day', $ptoStartDate);
					
						$ptoEndDate = strtotime($approvedPtoForm->date_of_request_end);
					}
					else
					{
						$ptoStartDate = strtotime($approvedPtoForm->date_of_request_start);

						$ptoEndDate = strtotime($approvedPtoForm->date_of_request_end);
					}
					
					$ptoCtr = 1;
					
					// while( $ptoStartDate <= $ptoEndDate ) 
					// {
						// $scheduledDays = array();
						
						// $schedules = AccountLoginSchedule::model()->findAll(array(
							// 'condition' => 'account_id = :account_id AND type=1',
							// 'params' => array(
								// ':account_id' => $approvedPtoForm->account_id,
							// ),
							// 'order' => 'date_created ASC',
						// ));
						
						// if( $schedules )
						// {
							// foreach( $schedules as $schedule )
							// {
								// $scheduledDays[] = $schedule->day_name;
							// }
						// }
						
						// if( in_array( date('l', $ptoStartDate),  $scheduledDays) )
						// {
							if( $approvedPtoForm->is_full_shift == 1 )
							{
								$pto_start_date = date('Y-m-d', $ptoStartDate) .'T'. date('08:00:00');
								$pto_end_date = date('Y-m-d', $ptoEndDate) .'T'. date('20:00:00');
							}
							else
							{
								$pto_start_date = date('Y-m-d', $ptoStartDate) .'T'. date('H:i:s', strtotime($approvedPtoForm->off_hour_from.':'.$approvedPtoForm->off_min_from.' '.$approvedPtoForm->off_md_from));
								$pto_end_date = date('Y-m-d', $ptoEndDate) .'T'. date('H:i:s', strtotime($approvedPtoForm->off_hour_to.':'.$approvedPtoForm->off_min_to.' '.$approvedPtoForm->off_md_to));
							}
							
							$result['events'][] = array(
								'id' => 'pto-form-'.$approvedPtoForm->id.'-'.$ptoCtr,
								'start_date' => $pto_start_date,
								'end_date' => $pto_end_date,
								'title' => !empty($approvedPtoForm->reason_for_request) ? $approvedPtoForm->reason_for_request : 'Time Off Request',
								'color' => '#D6487E',
								'is_custom' => 2,
								'allDay' => false,
							);

							// $ptoCtr++;
						// }
						
						// $ptoStartDate = strtotime('+1 day', $ptoStartDate);
					// }
				}
			}
		}
		
		echo '<pre>';	
			print_r($result);
	}

	public function actionCustomerVoiceCheck()
	{
		$customers = CustomerQueueViewer::model()->findAll(array(
			'with' => array('customer', 'skill'),
			'condition' => '
				t.company NOT IN ("Training Company", "Test Company", "Demo Company")
				AND t.next_available_calling_time NOT IN ("Cancelled")
				AND customer.status = 1 
				AND customer.is_deleted = 0 
				AND skill.status = 1 
			',
			'group' => 't.customer_id',
		));
		 
		if( $customers )
		{
			echo '<table border="1">';
			
			foreach( $customers as $customer )
			{
				if( !$customer->customer->getVoice() )
				{
					echo '<tr>';
						echo '<td>'.$customer->customer_name.'</td>';
					echo '</tr>';
				}
			}
			
			echo '</table>';
		}
	}

	public function actionGetReplacementCodeValues( )
	{
		$leadCallHistory = LeadCallHistory::model()->findByPk(1853273);
		
		$string = '[DTAB1]';
		
		if( $string != '' )
		{			
			//replace img src
			preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $string, $matches);
			
			if( isset($matches[1]) )
			{
				$string = str_replace($matches[1], ' http://system.engagexapp.com' . $matches[1], $string);
			}
			
			preg_match_all("/\[[^\]]*\]/", $string, $matches);
			
			if( $matches[0] )
			{
				foreach( $matches[0]  as $match )
				{
					switch( $match )
					{
						//lead
						case '[first_name]': 			$string = str_replace($match, !empty($leadCallHistory->lead->first_name) ? $leadCallHistory->lead->first_name : '', $string); break;
						case '[last_name]':				$string = str_replace($match, !empty($leadCallHistory->lead->last_name) ? $leadCallHistory->lead->last_name : '', $string); break;
						case '[partner_first_name]': 	$string = str_replace($match, !empty($leadCallHistory->lead->partner_first_name) ? $leadCallHistory->lead->partner_first_name : '', $string); break;
						case '[partner_last_name]': 	$string = str_replace($match, !empty($leadCallHistory->lead->partner_last_name) ? $leadCallHistory->lead->partner_last_name : '', $string); break;
						case '[office_phone_number]': 	$string = str_replace($match, !empty($leadCallHistory->lead->office_phone_number) ? $leadCallHistory->lead->office_phone_number : '', $string); break;
						case '[mobile_phone_number]': 	$string = str_replace($match, !empty($leadCallHistory->lead->mobile_phone_number) ? $leadCallHistory->lead->mobile_phone_number : '', $string); break;
						case '[home_phone_number]': 	$string = str_replace($match, !empty($leadCallHistory->lead->home_phone_number) ? $leadCallHistory->lead->home_phone_number : '', $string); break;
						case '[city]': 					$string = str_replace($match, !empty($leadCallHistory->lead->city) ? $leadCallHistory->lead->city : '', $string); break;
						case '[state]': 				$string = str_replace($match, !empty($leadCallHistory->lead->state) ? $leadCallHistory->lead->state : '', $string); break;
						case '[zip_code]': 				$string = str_replace($match, !empty($leadCallHistory->lead->zip_code) ? $leadCallHistory->lead->zip_code : '', $string); break;
						case '[address]': 				$string = str_replace($match, !empty($leadCallHistory->lead->address) ? $leadCallHistory->lead->address : '', $string); break;
						case '[address2]': 				$string = str_replace($match, !empty($leadCallHistory->lead->address2) ? $leadCallHistory->lead->address2 : '', $string); break;
						case '[email_address]': 		$string = str_replace($match, !empty($leadCallHistory->lead->email_address) ? $leadCallHistory->lead->email_address : '', $string); break;
						
						
						//customer
						case '[customer_first_name]': $string = str_replace($match, !empty($leadCallHistory->customer->firstname) ? $leadCallHistory->customer->firstname : '', $string); break;
						case '[customer_last_name]':  $string = str_replace($match, !empty($leadCallHistory->customer->lastname) ? $leadCallHistory->customer->lastname : '', $string); break;
						case '[customer_phone]':  $string = str_replace($match, !empty($leadCallHistory->customer->phone) ? $leadCallHistory->customer->phone : '', $string); break;
						
						
						//calendar
						case '[calendar_name]': 	
							
							if( $leadCallHistory->calendar_appointment_id != null )
							{
								$calendarAppointment = CalendarAppointment::model()->findByPk($leadCallHistory->calendar_appointment_id);
								
								$string = str_replace($match, !empty($calendarAppointment) ? $calendarAppointment->calendar->name : '', $string); break;
							}
						break;
						
						case '[office_assigned_to_calendar]': 	
							
							if( $leadCallHistory->calendar_appointment_id != null )
							{
								$calendarAppointment = CalendarAppointment::model()->findByPk($leadCallHistory->calendar_appointment_id);
								
								$string = str_replace($match, !empty($calendarAppointment) ? $calendarAppointment->calendar->office->office_name : '', $string); break;
							}
						break;
						
						case '[staff_assigned_to_calendar]': 	
							
							if( isset($leadCallHistory->calendarAppointment) )
							{
								$calendarStaffAssignment = CalendarStaffAssignment::model()->find(array(
									'condition' => 'calendar_id = :calendar_id',
									'params' => array(
										'calendar_id' => $leadCallHistory->calendarAppointment->calendar->id,
									),
								));
								
								if( $calendarStaffAssignment && isset($calendarStaffAssignment->staff) )
								{
									$string = str_replace($match, !empty($calendarStaffAssignment) ? $calendarStaffAssignment->staff->staff_name : '', $string); break;
								}
							}
						break;
						
						
						//calendar appoint
						case '[appointment_location]': 	
							
							if( $leadCallHistory->calendar_appointment_id != null )
							{
								$calendarAppointment = CalendarAppointment::model()->findByPk($leadCallHistory->calendar_appointment_id);
								
								if( !empty($calendarAppointment) )
								{
									if(  $calendarAppointment->location == 1 )
									{
										$appointmentLocation = 'Office';
									}
									
									if(  $calendarAppointment->location == 2 )
									{
										$appointmentLocation = 'Home';
									}
									
									if(  $calendarAppointment->location == 3 )
									{
										$appointmentLocation = 'Phone';
									}
									
									if(  $calendarAppointment->location == 4 )
									{
										$appointmentLocation = 'Skype';
									}
								}
								
								$string = str_replace($match, $appointmentLocation, $string); break;
							}
							
						break;
						
						case '[appointment_date]': 	
							
							if( $leadCallHistory->calendar_appointment_id != null )
							{
								$calendarAppointment = CalendarAppointment::model()->findByPk($leadCallHistory->calendar_appointment_id);
								
								$string = str_replace($match, !empty($calendarAppointment->start_date) ? date('m/d/Y', strtotime($calendarAppointment->start_date)) : '', $string); break;
							}
							
						break;
						
						case '[appointment_time]': 	
							
							if( $leadCallHistory->calendar_appointment_id != null )
							{
								$calendarAppointment = CalendarAppointment::model()->findByPk($leadCallHistory->calendar_appointment_id);
								
								$string = str_replace($match, !empty($calendarAppointment->start_date) ? date('g:i A', strtotime($calendarAppointment->start_date)) : '', $string); break;
							}
							
						break;
						
						case '[changed_appointment_date]': 	
							
							if( $leadCallHistory->calendar_appointment_id != null )
							{
								$calendarAppointment = CalendarAppointment::model()->findByPk($leadCallHistory->calendar_appointment_id);
								
								$string = str_replace($match, !empty($calendarAppointment->previous_start_date) ? date('m/d/Y', strtotime($calendarAppointment->previous_start_date)) : '', $string); break;
							}
							
						break;
						
						case '[changed_appointment_time]': 	
							
							if( $leadCallHistory->calendar_appointment_id != null )
							{
								$calendarAppointment = CalendarAppointment::model()->findByPk($leadCallHistory->calendar_appointment_id);
								
								$string = str_replace($match, !empty($calendarAppointment->previous_start_date) ? date('g:i A', strtotime($calendarAppointment->previous_start_date)) : '', $string); break;
							}
							
						break;
						
						case '[agent_dispo_note]': 	

							$string = str_replace($match, !empty($leadCallHistory->agent_note) ? $leadCallHistory->agent_note : '', $string); break;
							
						break;
						
						case '[dialed_number]': 	

							$string = str_replace($match, !empty($leadCallHistory->lead_phone_number) ? $leadCallHistory->lead_phone_number : '', $string); break;
							
						break;
						
						case '[dialed_number_last_4_digits]': 	

							$string = str_replace($match, !empty($leadCallHistory->lead_phone_number) ? substr($leadCallHistory->lead_phone_number, -4) : '', $string); break;
							
						break;
						
						case '[ics_file_link]': 	
						
						if( $leadCallHistory->calendar_appointment_id != null )
						{
							$htmlICSLink = '';
							
							$calendarAppointment = CalendarAppointment::model()->findByPk($leadCallHistory->calendar_appointment_id);
							
							if($calendarAppointment !== null)
							{
								
								$icsLink = 'http://portal.engagexapp.com/index.php/site/DownloadICS?calendarAppointmentId='.$calendarAppointment->id;
								$icsFileText = 'Click here to Add to Outlook';
								
								$htmlICSLink = '<table width="100%" border="0" cellspacing="0" cellpadding="0">
								  <tr>
									<td>
									  <div>
										<!--[if mso]>
										  <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="'.$icsLink.'" style="height:36px;v-text-anchor:middle;width:350px;" arcsize="5%" strokecolor="#0068B1" fillcolor="#0068B1">
											<w:anchorlock/>
											<center style="color:#ffffff;font-family:Helvetica, Arial,sans-serif;font-size:16px;">'.$icsFileText.'</center>
										  </v:roundrect>
										<![endif]-->
										<a href="'.$icsLink.'" style="background-color:#0068B1;border:1px solid #0068B1;border-radius:3px;color:#ffffff;display:inline-block;font-family:sans-serif;font-size:16px;line-height:44px;text-align:center;text-decoration:none;width:220px;-webkit-text-size-adjust:none;mso-hide:all;">'.$icsFileText.'</a>
									  </div>
									</td>
								  </tr>
								</table>';
								
								$string = str_replace($match, $htmlICSLink, $string); 
							}
						}
						
						break;
						
						case '[my_portal_login_button]':
						
						$htmlMyPortalButton = '
							<table cellspacing="0" cellpadding="0" border="0" width="100%">
									<tbody>
									<tr>
										<td align="right">
											<p>
												<a href="http://portal.engagexapp.com" style="background-color:#0068B1;border:1px solid #0068B1;border-radius:3px;color:#ffffff;display:inline-block;font-family:sans-serif;font-size:16px;line-height:44px;text-align:center;text-decoration:none;width:150px;-webkit-text-size-adjust:none;mso-hide:all;">My Portal Login</a>
											</p><br>
										</td>
									</tr>
								</tbody>
							</table>
						';
						
						$string = str_replace($match, $htmlMyPortalButton, $string); 
						
						break;
						
						case '[ics_file_link_non_html]': 	
						
						if( $leadCallHistory->calendar_appointment_id != null )
						{
							$calendarAppointment = CalendarAppointment::model()->findByPk($leadCallHistory->calendar_appointment_id);
							
							if($calendarAppointment !== null)
							{
								$icsLink = 'http://portal.engagexapp.com/index.php/site/DownloadICS?calendarAppointmentId='.$calendarAppointment->id;
								
								$icsLink = preg_replace('#^https?://#', '', rtrim( file_get_contents('http://tinyurl.com/api-create.php?url='.$icsLink) ));
								
								$string = str_replace($match, $icsLink, $string); 
							}
						}
						
						case '[my_portal_login_button_non_html]': 	
						
						$link = preg_replace('#^https?://#', '', rtrim( file_get_contents('http://tinyurl.com/api-create.php?url=http://portal.engagexapp.com') ));
						
						$string = str_replace($match, $link, $string); 
					
						break;
						
						case '[agent_dispo_note_sms]': 	
						
							$previewLink = 'http://portal.engagexapp.com/index.php/smsView/index/id/'.$leadCallHistory->id;
							
							$previewLink = preg_replace('#^https?://#', '', rtrim( file_get_contents('http://tinyurl.com/api-create.php?url='.$previewLink) ));
							
							$string = str_replace($match, $previewLink, $string); 
							
						break;
						
						case '[customer_reply_link_sms]': 	
						
							$replyLink = 'http://portal.engagexapp.com/index.php/smsView/reply/id/'.$leadCallHistory->id;
							
							$replyLink = preg_replace('#^https?://#', '', rtrim( file_get_contents('http://tinyurl.com/api-create.php?url='.$replyLink) ));
							
							$string = str_replace($match, $replyLink, $string); 
							
						break;
						
						case '[sub_disposition_name]': 	
						
							$string = str_replace($match, !empty($leadCallHistory->disposition_detail) ? $leadCallHistory->disposition_detail : '', $string); break;
							
						break;
						
						case '[sub_disposition_note]': 	
						
							$string = str_replace($match, !empty($leadCallHistory->skillDispositionDetail->external_notes) ? $leadCallHistory->skillDispositionDetail->external_notes : '', $string); break;
							
						break;

						case (strpos($match, 'DTAB') !== false):

							$field_name = trim($match, '[]');

							$leadCustomData = LeadCustomData::model()->find(array(
								'condition' => '
									lead_id = :lead_id
									AND field_name = :field_name
								',
								'params' => array(
									':lead_id' => $leadCallHistory->lead_id,
									':field_name' => $field_name
								)
							));
							
							if( $leadCustomData )
							{
								$string = str_replace($match, !empty($leadCustomData->value) ? $leadCustomData->value : '', $string); break;
							}
												
						break;
						
						default: $string = str_replace($match, '', $string);
					}
				}
			}
		}
		
		echo  $string;
	}

	public function actionEarlyConflicts()
	{
		$existingConflicts = LeadHopper::model()->find(array(
			'with' => 'calendarAppointment',
			'condition' => '
				t.status="DONE" 
				AND t.type = :type
				AND calendarAppointment.status = 2
			',
			'params' => array(
				':type' => LeadHopper::TYPE_CONFLICT,
			),
		));
		
		echo count($existingConflicts);
	}

	public function actionCheckHost()
	{
		echo $_SERVER['SERVER_NAME'];
	}

	public function actionLeadCustomFields()
	{
		exit;
		
		$listsCronProcessQueue = ListsCronProcess::model()->findByPk(14891);
		
		if( !empty($listsCronProcessQueue) )
		{
			$model = $listsCronProcessQueue->list;
			$customer_id = $listsCronProcessQueue->list->customer_id;
			$customer = Customer::model()->findByPk($customer_id);
			$company = $customer->company;
			
			$transaction = Yii::app()->db->beginTransaction();
			
			try
			{
				##### START OF IMPORTING PROCESS #######
				
				$leadsImported = 0;
				$duplicateLeadsCtr = 0;
				$badLeadsCtr = 0;
				$existingLeadUpdatedCtr = 0;
				$dncCtr = 0;
				$dcWnCtr = 0;
				$cellphoneScrubCtr = 0;
				
				$importLimit = 0;
				$contractedLeads = 0;
				
				$cellphoneScrubApiFields = array(
					'CO_CODE' => '109629',
					'PASS' => '1860So!!',
					'TYPE' => 'api_atn',
				); 			
			
				if( isset($listsCronProcessQueue->fileupload) )
				{
					$fileExists = file_exists('leads/' . $listsCronProcessQueue->fileupload->generated_filename);
					$inputFileName = 'leads/' . $listsCronProcessQueue->fileupload->generated_filename;
					
					if( !$fileExists )
					{
						$fileExists =  file_exists('fileupload/' . $listsCronProcessQueue->fileupload->generated_filename);
						$inputFileName = 'fileupload/' . $listsCronProcessQueue->fileupload->generated_filename;
					}
				
					//import from fileupload-
					if( !empty($listsCronProcessQueue->fileupload_id) && $fileExists )
					{
						// unregister Yii's autoloader
						spl_autoload_unregister(array('YiiBase', 'autoload'));
					
						$phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
						include($phpExcelPath . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'PHPExcel.php');

						spl_autoload_register(array('YiiBase', 'autoload'));
						 

						$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
						
						$worksheet = $objPHPExcel->getActiveSheet();

						// $highestRow         = $worksheet->getHighestRow(); // e.g. 10
						$highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
						$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
						$nrColumns = ord($highestColumn) - 64;
						
						$maxCell = $worksheet->getHighestRowAndColumn();
						$excelData = $worksheet->rangeToArray('A1:' . $maxCell['column'] . $maxCell['row']);
						$excelData = array_map('array_filter', $excelData);
						$excelData = array_filter($excelData);
						
						$highestRow = count($excelData);
						
						$validTemplate = true;
						$useDefaultTemplate = true;
						
						$col1 = $worksheet->getCell('A1')->getValue();
						$col2 = $worksheet->getCell('B1')->getValue();
						$col3 = $worksheet->getCell('C1')->getValue();
						$col4 = $worksheet->getCell('D1')->getValue();
						$col5 = $worksheet->getCell('E1')->getValue();
						$col6 = $worksheet->getCell('F1')->getValue();
						$col7 = $worksheet->getCell('G1')->getValue();
						$col8 = $worksheet->getCell('H1')->getValue();
						$col9 = $worksheet->getCell('I1')->getValue();
						$col10 = $worksheet->getCell('J1')->getValue();
						$col11 = $worksheet->getCell('K1')->getValue();
						$col12 = $worksheet->getCell('L1')->getValue();
						$col13 = $worksheet->getCell('M1')->getValue();
						
						if( 
							strtoupper($col1) != 'LAST NAME' 
							|| strtoupper($col2) != 'FIRST NAME' 
							|| strtoupper($col3) != 'PARTNER FIRST NAME' 
							|| strtoupper($col4) != 'PARTNER LAST NAME' 
							|| strtoupper($col5) != 'ADDRESS 1' 
							|| strtoupper($col6) != 'ADDRESS 2' 
							|| strtoupper($col7) != 'CITY' 
							|| strtoupper($col8) != 'STATE' 
							|| strtoupper($col9) != 'ZIP' 
							|| strtoupper($col10) != 'OFFICE PHONE'  
							|| strtoupper($col11) != 'MOBILE PHONE'  
							|| strtoupper($col12) != 'HOME PHONE'						
							|| strtoupper($col13) != 'EMAIL ADDRESS'						
						)
						{
							$validTemplate = false;
						}
						
						$validColumns = array('first name', 'last name', 'phone 1', 'phone 2', 'phone 3');
						$columnsInFile = array();
							
						if( !$validTemplate )
						{
							foreach( range('A', $highestColumn) as $columnInFile )
							{
								if( !empty($columnInFile) )
								{
									$columnsInFile[$columnInFile] = strtolower($worksheet->getCell($columnInFile.'1')->getValue());
								}
							}
							
							if( $columnsInFile )
							{
								$originalColumnsInFile = $columnsInFile;
								$arrayMatch = array_intersect($validColumns, $columnsInFile);
								
								sort($validColumns);
								sort($arrayMatch);

								if( $validColumns == $arrayMatch )
								{
									$validTemplate = true;
									$useDefaultTemplate = false;
									
									$columnsInFile = $originalColumnsInFile;
								}
							}
						}
						
						// echo 'allow_custom_fields: ' . $model->allow_custom_fields;
						// exit;
						
						if( $validTemplate )
						{
							// if( $model->allow_custom_fields == 1 )
							// {
								// echo '<pre>';
								
								// $customFieldCtr = 1;
								
								// foreach ( $this->excelColumnRange('A', $highestColumn) as $columnLetter ) 
								// {
									// $customFieldName = $worksheet->getCell($columnLetter.'1')->getValue();

									// if( !empty($customFieldName) )
									// {
										// $existingListCustomData = ListCustomData::model()->find(array(
											// 'condition' => '
												// list_id = :list_id 
												// AND customer_id = :customer_id
												// AND original_name = :original_name
											// ',
											// 'params' => array(
												// ':list_id' => $model->id,
												// ':customer_id' => $model->customer_id,
												// ':original_name' => $customFieldName
											// ),
										// ));
										
										// if( !$existingListCustomData )
										// {	
											// $listCustomData = new ListCustomData;
											
											// $listCustomData->setAttributes(array(
												// 'list_id' => $model->id,
												// 'customer_id' => $model->customer_id,
												// 'custom_name' => $customFieldName,
												// 'original_name' => $customFieldName,
												// 'ordering' => $customFieldCtr,
											// ));
											
											// if( $listCustomData->save(false) )
											// {
												// echo $customFieldCtr++;
												
												// print_r($listCustomData->attributes);
												
												// echo '<br>';
											// }
											// else
											// {
												// echo '<pre>';
													// print_r( $listCustomData->getErrors() );
												// exit;
											// }
										// }
									// }
								// }
							// }
							
							$customDataCtr = 1;
							
							for ($row = 2; $row <= $highestRow; ++$row) 
							{
								if( $useDefaultTemplate )
								{
									$last_name = $worksheet->getCell('A'.$row)->getValue();
									$first_name = $worksheet->getCell('B'.$row)->getValue();
									$partner_first_name = $worksheet->getCell('C'.$row)->getValue();
									$partner_last_name = $worksheet->getCell('D'.$row)->getValue();
									$address1 = $worksheet->getCell('E'.$row)->getValue();
									$address2 = $worksheet->getCell('F'.$row)->getValue();
									$city = $worksheet->getCell('G'.$row)->getValue();
									$state = $worksheet->getCell('H'.$row)->getValue();
									$zip = $worksheet->getCell('I'.$row)->getValue();
									$office_phone_number = preg_replace("/[^0-9]/","", $worksheet->getCell('J'.$row)->getValue());
									$mobile_phone_number = preg_replace("/[^0-9]/","", $worksheet->getCell('K'.$row)->getValue());
									$home_phone_number = preg_replace("/[^0-9]/","", $worksheet->getCell('L'.$row)->getValue());
									$email_address = $worksheet->getCell('M'.$row)->getValue();
									$gender = $worksheet->getCell('N'.$row)->getValue();
									
									$specificDateCell = $worksheet->getCell('N'.$row);
									$specific_date = $specificDateCell->getValue();
									
									if( PHPExcel_Shared_Date::isDateTime($specificDateCell) ) 
									{
										$specific_date = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($specific_date)); 
										$specific_date = date('Y-m-d', strtotime($specific_date . '+1 day')); 
									}
									
									if( date('N', strtotime($specific_date)) >= 6 )
									{
										$specific_date = date('Y-m-d', strtotime('last friday', strtotime($specific_date)));
									}
									
									if( $specific_date == '2017-10-09' )
									{
										$specific_date = '2017-10-06';
									}
									
									if( $specific_date == '2017-11-10' )
									{
										$specific_date = '2017-11-09';
									}
									
									if( $specific_date == '2017-11-23' )
									{
										$specific_date = '2017-11-22';
									}
									
									if( $specific_date == '2017-12-25' )
									{
										$specific_date = '2017-12-22';
									}
									
									if( $specific_date == '2018-01-01' )
									{
										$specific_date = '2017-12-29';
									}
								}
								else
								{
									$last_name = '';
									$first_name = '';
									$gender = '';
									$partner_first_name = '';
									$partner_last_name = '';
									$address1 = '';
									$address2 ='';
									$city = '';
									$state = '';
									$zip = '';
									$office_phone_number = '';
									$mobile_phone_number = '';
									$home_phone_number = '';
									$email_address = '';
									$specific_date = '';
									
									if( $columnsInFile )
									{
										foreach( $columnsInFile as $columnInFile => $rowValue )
										{
											if( $rowValue == 'first name' )
											{
												$first_name = $worksheet->getCell($columnInFile.$row)->getValue();
											}
											
											if( $rowValue == 'last name' )
											{
												$last_name = $worksheet->getCell($columnInFile.$row)->getValue();
											}
											
											if( $rowValue == 'gender' )
											{
												$gender = $worksheet->getCell($columnInFile.$row)->getValue();
											}
											
											if( $rowValue == 'phone 1' )
											{
												$home_phone_number = preg_replace("/[^0-9]/","", $worksheet->getCell($columnInFile.$row)->getValue());
											}
											
											if( $rowValue == 'phone 2' )
											{
												$mobile_phone_number = preg_replace("/[^0-9]/","", $worksheet->getCell($columnInFile.$row)->getValue());
											}
											
											if( $rowValue == 'phone 3' )
											{
												$office_phone_number = preg_replace("/[^0-9]/","", $worksheet->getCell($columnInFile.$row)->getValue());
											}
											
											if( $rowValue == 'partner first name' )
											{
												$partner_first_name = $worksheet->getCell($columnInFile.$row)->getValue();
											}
											
											if( $rowValue == 'partner last name' )
											{
												$partner_last_name = $worksheet->getCell($columnInFile.$row)->getValue();
											}
											
											if( $rowValue == 'email address' )
											{
												$email_address = $worksheet->getCell($columnInFile.$row)->getValue();
											}
											
											if( $rowValue == 'address 1' )
											{
												$address1 = $worksheet->getCell($columnInFile.$row)->getValue();
											}
											
											if( $rowValue == 'city' )
											{
												$city = $worksheet->getCell($columnInFile.$row)->getValue();
											}
											
											if( $rowValue == 'state' )
											{
												$state = $worksheet->getCell($columnInFile.$row)->getValue();
											}
											
											if( $rowValue == 'zip' )
											{
												$zip = $worksheet->getCell($columnInFile.$row)->getValue();
											}
											
											if( $rowValue == 'call date' )
											{
												$specificDateCell = $worksheet->getCell($columnInFile.$row);
												
												$specific_date = $specificDateCell->getValue();
												
												if( PHPExcel_Shared_Date::isDateTime($specificDateCell) ) 
												{
													$specific_date = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($specific_date)); 
													$specific_date = date('Y-m-d', strtotime($specific_date . '+1 day')); 
												}
												
												if( date('N', strtotime($specific_date)) >= 6 )
												{
													$specific_date = date('Y-m-d', strtotime('last friday', strtotime($specific_date)));
												}
												
												if( $specific_date == '2017-10-09' )
												{
													$specific_date = '2017-10-06';
												}
												
												if( $specific_date == '2017-11-10' )
												{
													$specific_date = '2017-11-09';
												}
												
												if( $specific_date == '2017-11-23' )
												{
													$specific_date = '2017-11-22';
												}
												
												if( $specific_date == '2017-12-25' )
												{
													$specific_date = '2017-12-22';
												}
												
												if( $specific_date == '2018-01-01' )
												{
													$specific_date = '2017-12-29';
												}
											}
										}
									}
								}

								
								$type = 2;
								$uniqueAreaCodes = array();
								
								$office_phone_number = ltrim($office_phone_number, '1');
								$mobile_phone_number = ltrim($mobile_phone_number, '1');
								$home_phone_number = ltrim($home_phone_number, '1');
								
								$cellphoneScrubData = array();
								
								if( strlen($office_phone_number) == 10 )
								{
									$type = 1;
									
									$uniqueAreaCodes[] = substr($office_phone_number, 0, 3);
								}
								else
								{
									$office_phone_number = null;
								}
								
								if( strlen($mobile_phone_number) == 10 )
								{
									$type = 1;
									
									$uniqueAreaCodes[] = substr($mobile_phone_number, 0, 3);
								}
								else
								{
									$mobile_phone_number = null;
								}
								
								if( strlen($home_phone_number) == 10 )
								{
									$type = 1;
									
									$uniqueAreaCodes[] = substr($home_phone_number, 0, 3);
								}
								else
								{
									$home_phone_number = null;
								}

								//area code assignment checkbox - temp logic for lead with multiple phones tag them as bad number for now so customer service can check and reimport them 
								if( $model->allow_area_code_assignment == 1 )
								{
									$uniqueAreaCodes = array_unique($uniqueAreaCodes);
									
									if( count($uniqueAreaCodes) > 1 )
									{
										$type = 2;
									}
								}
	
								if( $type == 1 )
								{
									//Cellphone Scrubbing API
									// $cellphoneScrubApi = new CellphoneScrubApi;
									
									// if( $cellphoneScrubApi->process($home_phone_number) )
									// {
										// $cellphoneScrubCtr++;
										// continue;
									// }
									
									$existingLead = Lead::model()->find(array(
										'condition' => 't.customer_id = :customer_id AND t.status !=4 AND ( 
											(office_phone_number = :office_phone_number AND office_phone_number != "" AND office_phone_number IS NOT NULL) OR 
											(office_phone_number = :mobile_phone_number AND office_phone_number != "" AND office_phone_number IS NOT NULL) OR 
											(office_phone_number = :home_phone_number AND office_phone_number != "" AND office_phone_number IS NOT NULL) OR 
											(mobile_phone_number = :office_phone_number AND mobile_phone_number != "" AND mobile_phone_number IS NOT NULL) OR
											(mobile_phone_number = :mobile_phone_number AND mobile_phone_number != "" AND mobile_phone_number IS NOT NULL) OR
											(mobile_phone_number = :home_phone_number AND mobile_phone_number != "" AND mobile_phone_number IS NOT NULL) OR
											(home_phone_number = :office_phone_number AND home_phone_number != "" AND home_phone_number IS NOT NULL) OR
											(home_phone_number = :mobile_phone_number AND home_phone_number != "" AND home_phone_number IS NOT NULL) OR
											(home_phone_number = :home_phone_number AND home_phone_number != "" AND home_phone_number IS NOT NULL) 
										)',
										'params' => array(
											':customer_id' => $customer_id,
											':office_phone_number' => $office_phone_number,
											':mobile_phone_number' => $mobile_phone_number,
											':home_phone_number' => $home_phone_number,
										),
									));
								}
								else
								{
									$existingLead = array();
								}
								
								if( !empty($existingLead) )
								{
									//save custom lead fields that are not in template
									if( $model->allow_custom_fields == 1 )
									{
										// if( $model->skill->enable_specific_date_calling == 1 )
											// {
												$lastOfStandardColumn = 'M';
												$startofCustomColumn = 'N';
											// }
											// else
											// {
												// $lastOfStandardColumn = 'M';
												// $startofCustomColumn = 'N';
											// }
										
										if( $highestColumn > $lastOfStandardColumn || strlen($highestColumn) > 1 )
										{	
											foreach ( $this->excelColumnRange($startofCustomColumn, $highestColumn) as $columnLetter ) 
											{
												$customFieldName = $worksheet->getCell($columnLetter.'1')->getValue();
												$customFieldValue = $worksheet->getCell($columnLetter.$row)->getValue();
												
												if( !empty($customFieldName) )
												{
													$existingCustomData = LeadCustomData::model()->find(array(
														'condition' => '
															lead_id = :lead_id
															AND list_id = :list_id
															AND field_name = :field_name
														',
														'params' => array(
															':lead_id' => $existingLead->id,
															':list_id' => $model->id,
															':field_name' => $customFieldName,
														),
													));
													
													if( empty($existingCustomData) )
													{
														$customData = new LeadCustomData;
														
														$customData->setAttributes(array(
															'lead_id' => $existingLead->id,
															'list_id' => $model->id,
															'field_name' => $customFieldName,
															'value' => $customFieldValue,
														));
														
														if( $customData->save(false) )
														{
															echo $customDataCtr++;
															echo '<br>';
														}
													}
												}
											}
										}
										
										$memberNumberCustomData = LeadCustomData::model()->find(array(
											'with' => 'list',
											'condition' => '
												t.lead_id = :lead_id 
												AND t.list_id = :list_id
												AND t.field_name = :field_name
												AND list.status != 3
											',
											'params' => array(
												':lead_id' => $existingLead->id,
												':list_id' => $model->id,
												':field_name' => 'Member Number',
											),
											'order' => 't.date_created DESC',
										));
										
										if( $memberNumberCustomData )
										{
											$existingCustomDatas = LeadCustomData::model()->findAll(array(
												'condition' => '
													lead_id = :lead_id 
													AND list_id = :list_id
													AND member_number IS NULL
												',
												'params' => array(
													':lead_id' => $existingLead->id,
													':list_id' => $model->id,
												),
											));
											
											if( $existingCustomDatas )
											{
												foreach( $existingCustomDatas as $existingCustomData )
												{
													$existingCustomData->member_number = $memberNumberCustomData->value;
													$existingCustomData->save(false);
												}
											}
										}
									}
							
								}
							}
							
							
							$result['status'] = 'success';
							$result['message'] = 'List "'.$model->name.'" for customer "'.$model->customer->getFullName().'" import completed successfully. '.$leadsImported . ' leads imported';
							
						}
						else
						{
							$result['status'] = 'error';
							$result['message'] = 'Invalid Template of List "'.$model->name.'" for customer "'.$model->customer->getFullName().'"';
						}
					}
					
				}
				
				$transaction->commit();
			}
			catch(Exception $e)
			{
				print_r($e);
				$transaction->rollback();
			}
			
			echo '<pre>';
			print_r($result);
			echo '</pre>';
			
			echo '<br><br>customDataCtr: ' . $customDataCtr;
		}
	}

	private function excelColumnRange($lower, $upper) 
	{
		++$upper;
		
		for ($i = $lower; $i !== $upper; ++$i) 
		{
			yield $i;
		}
	}

	public function actionAgentCallLog()
	{
		exit;
		
		ini_set('memory_limit', '2048M');
		set_time_limit(0); 

		$authAccount = Account::model()->findByPk(4);
	
		$latestCallsSql = "
			SELECT c.`firstname` as customer_first_name, c.`lastname` as customer_last_name, l.`first_name` as lead_first_name, l.`last_name` as lead_last_name, lch.`lead_phone_number`, lch.`disposition`, lch.`start_call_time`
			FROM ud_lead_call_history lch
			LEFT JOIN ud_customer c ON c.`id` = lch.`customer_id`
			LEFT JOIN ud_lead l ON l.`id` = lch.`lead_id`
			WHERE lch.status !=4
			AND lch.`agent_account_id` = '".$authAccount->id."'
			AND lch.date_created >= '".date('Y-m-d 00:00:00', strtotime('today'))."' 
			AND lch.date_created <= '".date('Y-m-d 23:59:59', strtotime('today'))."' 
			ORDER BY lch.`start_call_time` DESC
		";
		
		$connection = Yii::app()->db;
		$command = $connection->createCommand($latestCallsSql);
		$latestCalls = $command->queryAll();
		
		if( $latestCalls )
		{
			// unregister Yii's autoloader
			spl_autoload_unregister(array('YiiBase', 'autoload'));
			
			// register PHPExcel's autoloader ... PHPExcel.php will do it
			$phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
			require_once($phpExcelPath . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'PHPExcel.php');
			
			// register Yii's autoloader again
			spl_autoload_register(array('YiiBase', 'autoload'));
			 
			// This requires Yii's autoloader
			
			$objPHPExcel = new PHPExcel();
			
			$ctr = 1;

			$headers = array(
				'A' => 'Customer First',
				'B' => 'Customer Last',
				'C' => 'Lead First Name',
				'D' => 'Lead Last Name',
				'E' => 'Lead Phone Number',
				'F' => 'Disposition',
				'G' => 'Call Date/Time',
			);
			
			foreach($headers as $column => $val)
			{		
				$objPHPExcel->getActiveSheet()->SetCellValue($column.$ctr, $val);
			}
			
			$ctr = 2;
			
			foreach( $latestCalls as $latestCall )
			{
				$callDate = new DateTime($latestCall['start_call_time'], new DateTimeZone('America/Chicago'));
				$callDate->setTimezone(new DateTimeZone('America/Denver'));	
				
				$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $latestCall['customer_first_name'] );
				
				$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $latestCall['customer_last_name'] );
				
				$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $latestCall['lead_first_name'] );
				
				$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $latestCall['lead_last_name'] );
				
				$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $latestCall['lead_phone_number'] );
				
				$objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $latestCall['disposition'] );
				
				$objPHPExcel->getActiveSheet()->SetCellValue('G'.$ctr, $callDate->format('m/d/Y g:i A') );
				
				$ctr++;
			}
		}
		
		
		$webroot = Yii::getPathOfAlias('webroot');
		$folder =  $webroot . DIRECTORY_SEPARATOR . 'agent_daily_call_logs' . DIRECTORY_SEPARATOR;
		
		if( $authAccount->getIsHostDialer() )
		{
			$filename = $authAccount->customerOfficeStaff->staff_name;
			$filenamePath = $folder.$filename.'.xlsx';
		}
		else
		{
			$filename = $authAccount->accountUser->first_name.' '.$authAccount->accountUser->last_name;
			$filenamePath = $folder.$filename.'.xlsx';
		}
		
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save($folder. DIRECTORY_SEPARATOR .$filename.'.xlsx');
		
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

		$mail->SetFrom('service@engagex.com', 'Engagex Service');
		
		$mail->Subject = 'Agent Call Log';
		
		// $mail->AddAddress( $authAccount->email_address );
		
		$mail->AddAddress('erwin.datu@engagex.com');
		// $mail->AddBCC('erwin.datu@engagex.com');
		 
		$mail->MsgHTML('Agent Call Log');
		
		$mail->AddAttachment($filenamePath,$filename.'.xlsx');	
		
		$mail->Send();
	}

	public function actionCheckLeadHistoryTime()
	{
		$lead = Lead::model()->findByPk(679);
		
		$timeZone = AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $lead->customer->phone) );
								
		if( !empty($lead->timezone) )
		{
			$timeZone = $lead->timezone;
		}
		
		echo '<pre>';
			print_r($lead->attributes);
			
			echo '<br><br>';
			
			echo 'timeZone: ' . $timeZone;
			
			echo '<br><br>';
			
			echo 'timezone_name_from_abbr: ' . timezone_name_from_abbr($timeZone);
			
			echo '<br><br>';
		
			$leadLocalTime = new DateTime('', new DateTimeZone(timezone_name_from_abbr($timeZone)) );
			echo $leadLocalTime->format('m/d/Y g:i A');
	}

	public function actionDialPatch()
	{
		$leadCallHistories = LeadCallHistory::model()->findAll(array(
			'condition' => '
				DATE(date_created) >= DATE("2018-05-04") 
				AND customer_id IS NULL
			',
		));
		
		echo 'leadCallHistories: ' . count($leadCallHistories);
		
		echo '<br><br>';
		
		if( $leadCallHistories )
		{
			foreach( $leadCallHistories as $leadCallHistory )
			{
				$lead = $leadCallHistory->lead;
				$list = $lead->list;
				$customer = $lead->customer;
				
				$customerSkill = CustomerSkill::model()->find(array(
					'condition' => 'customer_id = :customer_id AND skill_id = :skill_id',
					'params' => array(
						':customer_id' => $customer->id,
						':skill_id' => $list->skill_id,
					),
				));
				
				$leadCallHistory->list_id = $list->id;
				$leadCallHistory->customer_id = $customer->id;
				$leadCallHistory->company_id = $customer->company_id;
				$leadCallHistory->contract_id = $customerSkill->contract_id;
				
				// echo '<pre>';
					// print_r($leadCallHistory->attributes);
				// exit;
				
				if( $leadCallHistory->save(false) )
				{
					echo $dbUpdates++;
					echo '<br>';
				}
			}
		}
		
		echo '<br><br>dbUpdates: ' . $dbUpdates;
	}
	
	public function actionEmailMonitorPatch()
	{
		$emailMonitors = EmailMonitor::model()->findAll(array(
			'condition' => '
				DATE(date_created) >= DATE("2018-05-04") 
				AND customer_id IS NULL
				AND skill_id IS NULL
			',
		));
		
		echo 'emailMonitors: ' . count($emailMonitors);
		
		echo '<br><br>';
		
		if( $emailMonitors )
		{
			foreach( $emailMonitors as $emailMonitor )
			{
				$lead = $emailMonitor->lead;
				$list = $lead->list;
				$customer = $lead->customer;
				
				$emailMonitor->customer_id = $customer->id;
				$emailMonitor->skill_id = $list->skill_id;
				
				// echo '<pre>';
					// print_r($emailMonitor->attributes);
				// exit;
				
				if( $emailMonitor->save(false) )
				{
					echo $dbUpdates++;
					echo '<br>';
				}
			}
		}
		
		echo '<br><br>dbUpdates: ' . $dbUpdates;
	}

	public function actionRemainingAppt()
	{
		$settings = CustomerQueueViewerSettings::model()->findByPk(17);
		
		$whiteCriteria = new CDbCriteria;
		$whiteCriteria->addCondition('status = 1 AND available_leads > 0 AND next_available_calling_time != "Goal Appointment Reached" AND skill_id IN (15, 17, 33, 34)');
		$whiteCriteria->order = 'priority DESC';
	
		$customerWhiteQueues = CustomerQueueViewer::model()->findAll($whiteCriteria); 
		
		$totalRemainingAppt = 0;
		
		if( $customerWhiteQueues )
		{
			foreach( $customerWhiteQueues as $customerWhiteQueue  )
			{
				$leadsCallableNow = round($customerWhiteQueue['available_leads'] / 9);
				$totalPotentialDials = $customerWhiteQueue['total_potential_dials'];

				if( $leadsCallableNow > $totalPotentialDials )
				{
					$totalRemainingAppt += $totalPotentialDials;
					$usedVal = $totalPotentialDials;
				}
				else
				{
					$totalRemainingAppt += $leadsCallableNow;
					$usedVal = $leadsCallableNow;
				}
			}
		}
		
		$greyCriteria = new CDbCriteria;
		$greyCriteria->addCondition('(status = 2 OR available_leads = 0 OR next_available_calling_time = "Goal Appointment Reached") AND next_available_calling_time NOT IN("Cancelled", "Future Start Date", "Blank Start Date") AND skill_id IN (15, 17, 33, 34)');
		$greyCriteria->order = 'priority DESC';
		
		$customerGreyQueues = CustomerQueueViewer::model()->findAll($greyCriteria); 
		
		if( $customerGreyQueues )
		{
			foreach( $customerGreyQueues as $customerGreyQueue  )
			{
				$leadsCallableNow = round($customerGreyQueue['available_leads'] / 9);
				$totalPotentialDials = $customerGreyQueue['total_potential_dials'];

				if( $leadsCallableNow > $totalPotentialDials )
				{
					$totalRemainingAppt += $totalPotentialDials;
					$usedVal = $totalPotentialDials;
				}
				else
				{
					$totalRemainingAppt += $leadsCallableNow;
					$usedVal = $leadsCallableNow;
				}
			}
		}
		
		$settings->value = $totalRemainingAppt;
		$settings->save(false);
	}

	public function actionAsteriskTest()
	{
		// unregister Yii's autoloader
		spl_autoload_unregister(array('YiiBase', 'autoload'));
		
		// register extension
		$extPath = Yii::getPathOfAlias('ext.phpagi'); 
		require_once($extPath . '/phpagi-asmanager.php');
		
		// register Yii's autoloader again
		spl_autoload_register(array('YiiBase', 'autoload'));
		
		$asm = new AGI_AsteriskManager;

		$asm->connect("107.182.238.147:5038", "ulap", "1234");
		
		if( $asm != false )
		{
			echo '<pre>';
				print_r($asm);
			echo '</pre>';
		}
		else
		{
			echo 'fail';
		}
	}

	public function actionBoulderStationLeads()
	{
		$callableLeads = Lead::model()->count(array(
			'with' => array('list', 'list.skill'),
			'condition' => ' 
				list.status = 1 
				AND t.list_id IS NOT NULL
				AND t.list_id = :list_id
				AND t.type=1 
				AND t.status=1
				AND t.is_do_not_call = 0
				AND t.is_bad_number = 0
				AND t.number_of_dials <= 1
				AND (
					t.recertify_date != "0000-00-00" 
					AND t.recertify_date IS NOT NULL 
					AND NOW() <= t.recertify_date
				)
				AND ( 
					t.home_phone_number IS NOT NULL
					OR t.office_phone_number IS NOT NULL
					OR t.mobile_phone_number IS NOT NULL
				)
			',
			'params' => array(
				':list_id' => 15887,
			),
		));
		
		echo '<br><br>';
		
		echo '0 and 1 dials: ' . $callableLeads;
		
		echo '<br><br>';
		
		$leads = Lead::model()->findAll(array(
			'with' => array('list', 'list.skill'),
			'condition' => ' 
				list.status = 1 
				AND t.list_id IS NOT NULL
				AND t.list_id = :list_id
				AND t.type=1 
				AND t.status=1
				AND t.is_do_not_call = 0
				AND t.is_bad_number = 0
				AND t.number_of_dials >= 2
				AND (
					t.recertify_date != "0000-00-00" 
					AND t.recertify_date IS NOT NULL 
					AND NOW() <= t.recertify_date
				)
				AND ( 
					t.home_phone_number IS NOT NULL
					OR t.office_phone_number IS NOT NULL
					OR t.mobile_phone_number IS NOT NULL
				)
			',
			'params' => array(
				':list_id' => 15887,
			),
		));
		
		echo '<pre>';
		
		echo 'leads: '. count($leads);
		
		echo '<br><br>';
		// exit;
		
		if( $leads )
		{
			$ctr = 1;
				
			foreach( $leads as $lead )
			{
				if( $lead->number_of_dials >= 2 )
				{
					$lead->status = 3;
					
					if( $lead->save(false) )
					{
						echo $ctr++;
						echo '<br>';
					}
				}
			}
		}
	}
	
	public function actionChukchansiLeads()
	{
		$zeroDialLeads = Lead::model()->findAll(array(
			'with' => array('list', 'list.skill'),
			'condition' => ' 
				list.status = 1 
				AND t.list_id IS NOT NULL
				AND t.list_id = :list_id
				AND t.type=1 
				AND t.status=1
				AND t.is_do_not_call = 0
				AND t.is_bad_number = 0
				AND t.number_of_dials = 0
				AND (
					t.recertify_date != "0000-00-00" 
					AND t.recertify_date IS NOT NULL 
					AND NOW() <= t.recertify_date
				)
				AND ( 
					t.home_phone_number IS NOT NULL
					OR t.office_phone_number IS NOT NULL
					OR t.mobile_phone_number IS NOT NULL
				)
			',
			'params' => array(
				':list_id' => 15895,
			),
		));
		
		$oneDialLeads = Lead::model()->count(array(
			'with' => array('list', 'list.skill'),
			'condition' => ' 
				list.status = 1 
				AND t.list_id IS NOT NULL
				AND t.list_id = :list_id
				AND t.type=1 
				AND t.status=1
				AND t.is_do_not_call = 0
				AND t.is_bad_number = 0
				AND t.number_of_dials = 1
				AND (
					t.recertify_date != "0000-00-00" 
					AND t.recertify_date IS NOT NULL 
					AND NOW() <= t.recertify_date
				)
				AND ( 
					t.home_phone_number IS NOT NULL
					OR t.office_phone_number IS NOT NULL
					OR t.mobile_phone_number IS NOT NULL
				)
			',
			'params' => array(
				':list_id' => 15895,
			),
		));
		
		echo '<br><br>';
		
		echo '0 Dials: ' . count($zeroDialLeads);
		
		echo '<br><br>';
		
		if( $zeroDialLeads )
		{
			foreach( $zeroDialLeads as $zeroDialLead )
			{
				$inQueue = LeadHopper::model()->count(array(
					'condition' => 'lead_id = :lead_id',
					'params' => array(
						':lead_id' => $zeroDialLead->id,
					),
				));	
				
				$existingDnc = Dnc::model()->find(array(
					'condition' => '
						phone_number IS NOT NULL 
						AND phone_number !=""
						AND (
							phone_number = :home_phone_number 
							OR phone_number = :mobile_phone_number 
							OR phone_number = :office_phone_number
						)
					',
					'params' => array(
						':home_phone_number' => $zeroDialLead->home_phone_number,
						':mobile_phone_number' => $zeroDialLead->mobile_phone_number,
						':office_phone_number' => $zeroDialLead->office_phone_number,
					),
				));
				
				if( $existingDnc )
				{
					$zeroDialLead->status = 3;
					$zeroDialLead->is_do_not_call = 1;
				
					$zeroDialLead->recycle_date = null;
					$zeroDialLead->recycle_lead_call_history_id = null;
					$zeroDialLead->recycle_lead_call_history_disposition_id = null;
					$zeroDialLead->save(false);
				}
				
				$existingDcwn = Dcwn::model()->find(array(
					'condition' => '
						phone_number IS NOT NULL 
						AND phone_number !=""
						AND (
							phone_number = :home_phone_number 
							OR phone_number = :mobile_phone_number 
							OR phone_number = :office_phone_number
						)
					',
					'params' => array(
						':home_phone_number' => $zeroDialLead->home_phone_number,
						':mobile_phone_number' => $zeroDialLead->mobile_phone_number,
						':office_phone_number' => $zeroDialLead->office_phone_number,
					),
				));
				
				if( $existingDcwn )
				{
					$zeroDialLead->status = 3;
					$zeroDialLead->is_bad_number = 1;
			
					$zeroDialLead->recycle_date = null;
					$zeroDialLead->recycle_lead_call_history_id = null;
					$zeroDialLead->recycle_lead_call_history_disposition_id = null;
					$zeroDialLead->save(false);
				}
				
				echo $zeroDialLead->id .' : '.$zeroDialLead->getFullName().' | ' .$zeroDialLead->timezone.' => ' . $inQueue;
				echo '<br>';
			}
		}
		
		echo '<br><hr><br>';
		
		echo '1 Dials: ' . $oneDialLeads;
		
		echo '<br><br>';
		
		$leads = Lead::model()->findAll(array(
			'with' => array('list', 'list.skill'),
			'condition' => ' 
				list.status = 1 
				AND t.list_id IS NOT NULL
				AND t.list_id = :list_id
				AND t.type=1 
				AND t.status=1
				AND t.is_do_not_call = 0
				AND t.is_bad_number = 0
				AND t.number_of_dials >= 2
				AND (
					t.recertify_date != "0000-00-00" 
					AND t.recertify_date IS NOT NULL 
					AND NOW() <= t.recertify_date
				)
				AND ( 
					t.home_phone_number IS NOT NULL
					OR t.office_phone_number IS NOT NULL
					OR t.mobile_phone_number IS NOT NULL
				)
			',
			'params' => array(
				':list_id' => 15895,
			),
		));
		
		echo '<pre>';
		
		echo 'leads: '. count($leads);
		
		echo '<br><br>';
		// exit; 
		
		if( $leads )
		{
			$ctr = 1;
				
			foreach( $leads as $lead )
			{
				if( $lead->number_of_dials >= 2 )
				{
					$lead->status = 3;
					
					if( $lead->save(false) )
					{
						echo $ctr++;
						echo '<br>';
					}
				}
			}
		}
	}

	public function actionRemoveLeadsInQueue()
	{
		exit;
		
		$hopperEntries = LeadHopper::model()->findAll(array(
			'condition' => '
				customer_id = "2374" 
				AND skill_id = "54"
				AND type = 1
				AND status = "READY"
			',
		));
		
		echo 'hopperEntries: ' . count($hopperEntries);
		
		if( $hopperEntries )
		{
			$ctr = 1;
			
			foreach( $hopperEntries as $hopperEntry )
			{
				if( $hopperEntry->delete() )
				{
					echo $ctr++;
					echo '<br>';
				}
			}
		}
	}

	public function actionGamingGuestLeads()
	{
		$callableLeads = Lead::model()->count(array(
			'with' => array('list', 'list.skill'),
			'condition' => ' 
				list.status = 1 
				AND t.list_id IS NOT NULL
				AND t.list_id = :list_id
				AND t.type=1 
				AND t.status=1
				AND t.is_do_not_call = 0
				AND t.is_bad_number = 0
				AND t.number_of_dials <= 2
				AND (
					t.recertify_date != "0000-00-00" 
					AND t.recertify_date IS NOT NULL 
					AND NOW() <= t.recertify_date
				)
				AND ( 
					t.home_phone_number IS NOT NULL
					OR t.office_phone_number IS NOT NULL
					OR t.mobile_phone_number IS NOT NULL
				)
			',
			'params' => array(
				':list_id' => 15890,
			),
		));
		
		echo '<br><br>';
		
		echo '0 and 1 dials: ' . $callableLeads;
		
		echo '<br><br>';
		
		$leads = Lead::model()->findAll(array(
			'with' => array('list', 'list.skill'),
			'condition' => ' 
				list.status = 1 
				AND t.list_id IS NOT NULL
				AND t.list_id = :list_id
				AND t.type=1 
				AND t.status=1
				AND t.is_do_not_call = 0
				AND t.is_bad_number = 0
				AND t.number_of_dials >= 2
				AND (
					t.recertify_date != "0000-00-00" 
					AND t.recertify_date IS NOT NULL 
					AND NOW() <= t.recertify_date
				)
				AND ( 
					t.home_phone_number IS NOT NULL
					OR t.office_phone_number IS NOT NULL
					OR t.mobile_phone_number IS NOT NULL
				)
			',
			'params' => array(
				':list_id' => 15890,
			),
		));
		
		echo '<pre>';
		
		echo 'leads: '. count($leads);
		
		echo '<br><br>';
		
		$list = Lists::model()->findByPk(15890);
		
		echo 'max dial: ' . $list->skill->max_dials;
		
		echo '<br><br>';
		exit;
		
		if( $leads )
		{
			$ctr = 1;
				
			foreach( $leads as $lead )
			{
				if( $lead->number_of_dials >= 2 )
				{
					$lead->status = 3;
					
					if( $lead->save(false) )
					{
						echo $ctr++;
						echo '<br>';
					}
				}
			}
		}
	}

	public function actionLeadImportLimitIssue()
	{
		$models = CustomerHistory::model()->findAll(array(
			'condition' => '
				DATE(date_created) >= DATE("2018-06-01")
				AND content LIKE :content
			',
			'params' => array(
				':content' => '%| 0 leads imported | 0 existing leads updated | 0 DNC | 0 DC/WN | 0 cellphone |%',
			),
			'group' => 'customer_id',
		));
		
		// echo 'count: ' . count($models);
		
		// echo '<br><br>';
		
		if( $models )
		{
			$ctr = 1;
			
			foreach( $models as $model )
			{
				$explodedContent = explode(' | ', strip_tags($model->content) );
				
				// echo '<pre>';
					// print_r($explodedContent);
				// echo '</pre>';
				
				// echo '<br>';
				
				// if( isset($explodedContent[7]) && $explodedContent[7] == '0 duplicates' && isset($explodedContent[8]) && $explodedContent[8] == '0 bad leads' )
				if( isset($explodedContent[7]) && $explodedContent[7] == '0 duplicates' )
				{

					echo $ctr++ .'. '. CHtml::link($model->customer->getFullName(), array('customer/history', 'customer_id'=>$model->customer_id), array('target'=>'_blank'));
					echo '<br>';

				}
				
				// echo '<br>';
				// echo '<hr>';
				// echo '<br>';
			}
		}
	}

	public function actionAddLeadHistory()
	{
		exit;
		
		$model = new LeadHistory;
		
		$model->setAttributes(array(
			'lead_id' => 2542976,
			'content' => '323-755-5410 is listed in the Engagex Do Not Call / Wrong Number listing.',
			'type' => 6 
		));
	
		$model->save(false);
	}

	public function actionManualInsertAppt()
	{
		exit;
		
		$lead = Lead::model()->findByPk(2596031);
		$customer = $lead->customer;
		
		$customerSkill = CustomerSkill::model()->find(array(
			'condition' => 'customer_id = :customer_id AND skill_id = :skill_id',
			'params' => array(
				':customer_id' => $customer->id,
				':skill_id' => $lead->list->skill_id,
			),
		));
		
		$start_date_time = date('16:45:00');
					
		$start_date = date('2018-07-10 '.$start_date_time);
		
		$start_date_year = date('Y', strtotime($start_date));
		$start_date_month = date('m', strtotime($start_date));
		$start_date_day = date('d', strtotime($start_date));
		
		$end_date = date('Y-m-d H:i:s', strtotime('+45 Minutes' , strtotime($start_date)));
		
		$end_date_time = date('H:i:s', strtotime($end_date));
		
		$end_date_year = date('Y', strtotime($end_date));
		$end_date_month = date('m', strtotime($end_date));
		$end_date_day = date('d', strtotime($end_date));
		
		$calendarAppointment = new CalendarAppointment;
			
		$calendarAppointment->setAttributes(array(
			'title' => 'APPOINTMENT SET',
			'agent_notes' => 'KASEY DAIGLE has agreed to meet with you to review their policies.  Thank you!',
			'calendar_id' => 3105, 
			'account_id' => null, 
			'lead_id' => $lead->id, 
			'start_date' => $start_date,
			'start_date_year' => $start_date_year,
			'start_date_month' => $start_date_month,
			'start_date_day' => $start_date_day,
			'start_date_time' => $start_date_time,
			'end_date' =>  $end_date,
			'end_date_year' =>  $end_date_year,
			'end_date_month' =>  $end_date_month,
			'end_date_day' =>  $end_date_day,
			'end_date_time' =>  $end_date_time,
			'is_custom' => 1,
			'location' => 1,
			'status' => 2,
		));
		
		// echo '<pre>';
			// print_r( $calendarAppointment->attributes );
		// exit;
		
		if( $calendarAppointment->save(false) )
		{
			$leadCallHistory = new LeadCallHistory;
			
			$leadCallHistory->setAttributes(array(
				'lead_id' => $lead->id, 
				'list_id' => $lead->list_id, 
				'customer_id' => $customer, 
				'company_id' => $customer->company_id, 
				'contract_id' => $customerSkill->contract_id,
				'agent_account_id' => 4580, 
				'dial_number' => 1,
				'lead_phone_number' => preg_replace("/[^0-9]/","", 9853205524), 
				'start_call_time' => date('Y-m-d H:i:s'),
				'end_call_time' => date('Y-m-d H:i:s'),
			));
			
			$leadCallHistory->save(false);
		}
		
		echo '<br><br>end...';
	}

	public function actionYoyTrendsReport()
	{
		if( $_GET['page'] == 'sales' )
		{
			$customerQueues = CustomerQueueViewer::model()->findAll(array(
				'with' => 'customer',
				'order' => 'customer.lastname ASC',
				// 'condition' => 'customer_id=1619',
				'condition' => 't.customer_id NOT IN (48)',
				// 'limit' => 100
			));
			
			if( $customerQueues )
			{
				foreach( $customerQueues as $customerQueue )
				{
					//starting
					if( date('Y-m', strtotime($customerQueue->start_date)) )
					{
						$existingYoyTrend = ReportYoyTrends::model()->find(array(
							'condition' => '
								YEAR(report_date) = :report_date_year
								AND MONTH(report_date) = :report_date_month
							',
							'params' => array(
								':report_date_year' => date('Y', strtotime($customerQueue->start_date)),
								':report_date_month' => date('m', strtotime($customerQueue->start_date)),
							),
						));
						
						if( $existingYoyTrend )
						{
							$yoyTrend = $existingYoyTrend;
						}
						else
						{
							$yoyTrend = new ReportYoyTrends;
							$yoyTrend->report_date = date('Y-m-01', strtotime($customerQueue->start_date));
						}
					
						$yoyTrend->sales = $yoyTrend->sales + 1;
						$yoyTrend->save(false);
						
						
						echo 'name: ' . $customerQueue->customer_name;
						echo '<br>';
						echo 'start date: ' . $customerQueue->start_date;
						echo '<br><br>';
					}
				}
			}
		}
		
		if( $_GET['page'] == 'cancels' )
		{
			$customerHistories = CustomerHistory::model()->findAll(array(
				'condition' => '
					t.content LIKE "%End Date Changed%"
					AND t.date_created >= "'.date('2017-01-01 00:00:01').'"
					AND t.date_created <= "'.date('2018-07-31 23:59:59').'"
				',
				'group' => 't.customer_id',
				'order' => 't.date_created ASC',
			));
			
			echo '<pre>';
			
			echo 'customerHistories: ' . count($customerHistories);
			echo '<br><br>';
			// exit;  
			
			$ctr = 1;
			
			if( $customerHistories )
			{
				foreach( $customerHistories as $customerHistory )
				{
					$explodedContent = explode('to', $customerHistory->content);
					$endDate = str_replace(' ', '', $explodedContent[1]);
					
					// echo 'endDate: ' . $endDate;
					
					// echo '<br><br>';
					
					// print_r( $customerHistory->attributes );
					
					// exit;
					
					if( $endDate != '12/31/1969' )
					{
						// $customerSkill = CustomerSkill::model()->findByPk($customerHistory->model_id);
						$customer = $customerHistory->customer;
						
						$dateTime = new DateTime($customerHistory->date_created, new DateTimeZone('America/Chicago'));
						$dateTime->setTimezone(new DateTimeZone('America/Denver'));	
						$dateEntered = $dateTime->format('m/d/Y g:i A');

						$existingYoyTrend = ReportYoyTrends::model()->find(array(
							'condition' => '
								YEAR(report_date) = :report_date_year
								AND MONTH(report_date) = :report_date_month
							',
							'params' => array(
								':report_date_year' => date('Y', strtotime($customerHistory->date_created)),
								':report_date_month' => date('m', strtotime($customerHistory->date_created)),
							),
						));
						
						if( $existingYoyTrend )
						{
							$yoyTrend = $existingYoyTrend;
						}
						else
						{
							$yoyTrend = new ReportYoyTrends;
							$yoyTrend->report_date = date('Y-m-01', strtotime($customerHistory->date_created));
						}
						
						// echo '<pre>';
							// print_r( $yoyTrend->attributes );
						// exit;
						
						$yoyTrend->cancels = $yoyTrend->cancels + 1;
						$yoyTrend->save(false);
						
						echo 'Customer Name: ' . $customer->getFullName();
						echo '<br>';
						echo 'End Date: ' . $endDate;
						echo '<br>';
						echo 'Cancel Date: ' . $dateEntered; 
						
						echo '<br>';
						echo '<br>';
						
						
						$ctr++;
					}
				}
			}
			
			echo '<br><br>ctr: ' . $ctr;
		}
	}

	public function actionLastPassChangeDateList()
	{
		$models = Account::model()->findAll(array(
			'with' => array('accountUser'),
			'condition' => '
				accountUser.id IS NOT NULL 
				AND t.status = :status 
				AND t.account_type_id NOT IN (14,15,16)
				AND t.date_last_password_change IS NOT NULL
			',
			'params' => array(
				':status' => Account::STATUS_ACTIVE,
			),
			'order' => 'accountUser.last_name ASC',
		));
		
		echo 'models: ' . count($models);
		
		echo '<br><br>';
		
		echo '<table border=1>';
		
		if( $models )
		{
			foreach( $models as $model )
			{
				$dateTime = new DateTime($model->date_last_password_change, new DateTimeZone('America/Chicago'));
				$dateTime->setTimezone(new DateTimeZone('America/Denver'));	
				
				echo '<tr>';
					// echo '<td>'.$model->id.'</td>';
					echo '<td>'.$model->getFullName().'</td>';
					echo '<td>'.$dateTime->format('m/d/Y g:i A').'</td>';
				echo '<t/r>';
			}
		}
		
		echo '</table>';
	}

	private $encryptionKey = 'a37b7ed9';
	
	public function actionEncryptData()
	{
		// $password = "1Jupiterpepsi!!";
		// $password = "123456";
		// $password = "9616846844646";
		$password = "4007000000027";
		
		$encrypted = $this->encryptData($password);
		
		$decrypted = $this->decryptData($encrypted);
		
		// echo 'encryptionKey: ' . substr(hash('sha256', 'erwind'), 0, 8);
		// echo '<br><br>';
		echo 'encrypted: ' . $encrypted;
		echo '<br><br>';
		echo 'decrypted: ' . $decrypted;
	}
	
	private function encryptData($str)
	{
		$block = mcrypt_get_block_size('des', 'ecb');
		
		if( ($pad = $block - (strlen($str) % $block)) < $block ) 
		{
			$str .= str_repeat(chr($pad), $pad);
		}
		
		return base64_encode(mcrypt_encrypt(MCRYPT_DES, $this->encryptionKey, $str, MCRYPT_MODE_ECB));
	}
	
	private function decryptData($str)
	{
		$str = mcrypt_decrypt(MCRYPT_DES, $this->encryptionKey, base64_decode($str), MCRYPT_MODE_ECB);
		
		$block = mcrypt_get_block_size('des', 'ecb');
		$pad = ord($str[($len = strlen($str)) - 1]);
		
		if ($pad && $pad < $block && preg_match('/' . chr($pad) . '{' . $pad . '}$/', $str)) 
		{
			return substr($str, 0, strlen($str) - $pad);
		}
		
		return $str;
	}
}



?>