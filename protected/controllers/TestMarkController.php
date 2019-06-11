<?php 

ini_set('memory_limit', '2000M');
set_time_limit(0);
	
class TestMarkController extends Controller
{
	
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
				// ':lead_id' => 9139,
			// ),
		// ));
		
		
		
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
		$leadCallHistory = LeadCallHistory::model()->findByPk(16860);
		echo $leadCallHistory->getReplacementCodeValues();
		
		// $emailMonitor = EmailMonitor::model()->findByPk(1942);
		
		// $emailMonitor->html_content = $leadCallHistory->getReplacementCodeValues();
		
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
		$leadCallHistory = LeadCallHistory::model()->findByPk(866);
	
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
		exit;
		
		$leadCallHistory = LeadCallHistory::model()->findByPk(30525);
		
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
		
		$mail->SMTPDebug = 2;
		// $mail->Host = "64.251.10.115";
		
		// $mail->IsSMTP(); 											
								
		// $mail->SMTPAuth = true;
		
		// $mail->SMTPSecure = "tls";   

		// $mail->Port = 587;      
		
		// $mail->Username = "service@engagex.com";  
		
		// $mail->Password = "Engagex123";          											

		$mail->SetFrom('service@engagex.com');

		$mail->Subject = 'test';
		
		$mail->AddAddress( 'imperialleonel@gmail.com' );
		$mail->AddAddress( 'leonel.imperial@engagex.com' );
		$mail->AddAddress( 'erwin.datu@engagex.com' );

		 
		$mail->MsgHTML( 'test only');
								
		if( $mail->Send() )
		{
			echo 'success';
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
		exit;
		
		Yii::import('application.vendor.*');
		require ('anet_php_sdk/AuthorizeNet.php');
		
		//sandbox
		define("AUTHORIZENET_API_LOGIN_ID", "5CzTeH72f98D");	
		define("AUTHORIZENET_TRANSACTION_KEY", "8n25bCL72432SpR9");	
		define("AUTHORIZENET_SANDBOX", true);
					
		//live
		// define("AUTHORIZENET_API_LOGIN_ID", "8Wtz3C6a");	
		// define("AUTHORIZENET_TRANSACTION_KEY", "8YEBD7n3Z64r3x39");
		// define("AUTHORIZENET_SANDBOX", false);
		
		$defaultCreditCard = CustomerCreditCard::model()->findByPk(19);
		
		$authorizeTransaction = new AuthorizeNetAIM;
		
		$authorizeTransaction->setSandbox(AUTHORIZENET_SANDBOX);
		
		$authorizeTransaction->setFields(array(
			// 'invoice_num' => ,
			'amount' => number_format('1.00', 2),
			'first_name' => $defaultCreditCard->first_name,
			'last_name' => $defaultCreditCard->last_name,
			'email' => 'erwin.datu@engagex.com',
			'card_num' => $defaultCreditCard->credit_card_number, 
			'card_code' => $defaultCreditCard->security_code,
			'exp_date' => $defaultCreditCard->expiration_month . $defaultCreditCard->expiration_year,
			'address' => $defaultCreditCard->address,
			'city' => $defaultCreditCard->city,
			'state' => $defaultCreditCard->state,
			'zip' => $defaultCreditCard->zip,
		));
		
		$response = $authorizeTransaction->authorizeAndCapture();
									
		$request  = new AuthorizeNetTD;
		$response_TransactionDetails = $request->getTransactionDetails($response->transaction_id);
		
		
		echo '<pre>';
		print_r($response);
		print_r($response_TransactionDetails);
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
		$customerQueueViewerEvaluated = 0;
		$customerQueueViewerUpdates = 0;

		$customerSkill = CustomerSkill::model()->find(array(
			'condition' => 'customer_id=763',
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
			echo '1st: ' . $customerIsCallable = true;
			echo '<br>';
			echo '<br>';
		}
		
		if( $customerSkill->is_contract_hold == 1 )
		{
			if( !empty($customerSkill->is_contract_hold_start_date) && !empty($customerSkill->is_contract_hold_end_date) )
			{
				if( time() >= strtotime($customerSkill->is_contract_hold_start_date) && time() <= strtotime($customerSkill->is_contract_hold_end_date) )
				{
					echo '2nd: ' . $customerIsCallable = false;
					echo '<br>';
					echo '<br>';
				}
			}
		}
		
		if( !empty($customerSkill->end_month) && date('Y', strtotime($customerSkill->end_month)) > 2015 )
		{
			if( time() >= strtotime($customerSkill->end_month) )
			{
				echo '3rd: ' . $customerIsCallable = false;
				echo '<br>';
				echo '<br>';
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
					if( $type == 'customer' )
					{
						$nextAvailableCallingTimeStart = new DateTime($sched['schedule_start'], new DateTimeZone(timezone_name_from_abbr($timeZone)) );
						$nextAvailableCallingTimeEnd = new DateTime($sched['schedule_end'], new DateTimeZone(timezone_name_from_abbr($timeZone)) );
						
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
					}
					else 
					{
						$currentDateTime = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('America/Denver'));
						$leadLocalTime = $currentDateTime->setTimezone(new DateTimeZone(timezone_name_from_abbr($timeZone)));
					
						if( strtotime($leadLocalTime->format('g:i A')) >= strtotime($sched['schedule_start']) && strtotime($leadLocalTime->format('g:i A')) <= strtotime($sched['schedule_end']) )
						{
							$nextAvailableCallingTime = 'Now';
						}
						
						if( strtotime($leadLocalTime->format('g:i A')) >= strtotime($sched['schedule_start']) && strtotime($leadLocalTime->format('g:i A')) >= strtotime($sched['schedule_end']) )
						{
							$nextAvailableCallingTime = 'Next Shift';
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
		exit;
		
		$models = Lists::model()->findAll(array(
			'condition' => 'name IN ("System Imported List", "System Imported Completed List") AND status=1 AND YEAR(date_created)="2016" AND MONTH(date_created)="04"',
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
		$leadCallHistories = LeadCallHistory::model()->findAll(array(
			'condition' => 'disposition IS NOT NULL AND date_created >="2016-04-01 00:00:00" AND date_created <= "2016-04-30 <= 23:59:59" AND status !=4 AND attempt IS NULL',
			'order' => 'date_created ASC',
			'offset' => 0,
			'limit' => 1000,
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
				
				if( $leadCallHistory->bucket_priority == null )
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
							
							if( $otherAttempt->bucket_priority == null )
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
		$models = CalendarAppointment::model()->findAll(array(
			'with' => 'lead',
			'condition' => '
				t.start_date >= "2016-04-29 00:00:00"
				AND t.title IN ("APPOINTMENT SET", "INSERT APPOINTMENT")
				AND t.status !=4
				AND t.lead_id IS NOT NULL
				AND lead.id IS NOT NULL
			',
		));
		
		echo 'count: ' . count($models);
		
		echo '<br><br>'; 
		
		if( $models )
		{
			$ctr = 1;
			
			foreach( $models as $model )
			{
				$this->createConfirmationCall($model);
				
				echo $ctr++;
				echo '<br>';
			}
		}
		
		echo '<br><br>end..';
	}
	
	private function createConfirmationCall($model)
	{
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
			$confirmationCall = $existingLeadHopperEntry;
		}
		else
		{
			$lead = $model->lead;
			$list = $lead->list;
			$customer = $list->customer;
			
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
				'skill_id' => $list->skill_id,
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
		
		
		$confirmationCall->calendar_appointment_id = $model->id;
		$confirmationCall->appointment_date = $confirmationDate;
		$confirmationCall->agent_account_id = null;
		
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
			'condition' => 'type=2 AND ( callback_date IS NULL OR DATE(callback_date) < DATE(NOW()) )',
			'order' => 'callback_date DESC',
		));
		
		echo 'count: ' . count($models);
		
		echo '<br><br>';
		
		if( $models )
		{
			foreach( $models as $model )
			{
				echo $model->callback_date;
				echo '<br>';
				
				// echo $model->delete();
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
		$customerQueues = CustomerQueueViewer::model()->findAll(array(
			// 'condition' => 'available_leads > 0',
			// 'order' => 'priority DESC',
			// 'limit' => 10,
		));
		
		echo 'count: ' . count($customerQueues);
		
		echo '<br><br>';
		
		echo date('Y-m-01 00:00:00').' - '.date('Y-m-t 23:59:59');
		
		echo '<br><br>';
		
		$appointmentsMtd = 0;
		

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
					
					echo $appointmentSetMTDSql;
					exit;
					
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
								
								if( $appointmentCount > $contractGoal )
								{
									$customerQueue->next_available_calling_time = 'Goal Appointment Reached';
								}
								
								echo 'Customer ID:'.$customerSkill->customer->id.' - '.$customerSkill->customer->getFullName().' | Goal: '. $contractGoal.' | Appointment Set: '.$appointmentCount.' | Priority: ' . $customerQueue->priority . ' | ' . $customerQueue->next_available_calling_time;
								
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
		
		
		echo '<br><br>';
		
		echo 'Goal Appointment Reached: ' . CustomerQueueViewer::model()->count(array(
			'condition' => 'next_available_calling_time = "Goal Appointment Reached"',
		));
		
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

	public function actionMayMemorialDayConfirmPatch()
	{
		$models = CalendarAppointment::model()->findAll(array(
			'with' => 'lead',
			'condition' => '
				t.start_date >= "2016-05-28 00:00:00" 
				AND t.start_date <= "2016-05-31 23:59:59"
				AND t.title IN ("APPOINTMENT SET", "INSERT APPOINTMENT")
				AND t.status !=4
				AND t.lead_id IS NOT NULL
				AND lead.id IS NOT NULL
			',
		));
		
		echo 'count: ' . count($models);
		
		echo '<br><br>'; 
		
		if( $models )
		{
			$ctr = 1;
			
			foreach( $models as $model )
			{
				echo $ctr++;
				echo '=';
				echo $model->start_date;
				echo '<br>';
				
				$criteria = new CDbCriteria;
				$criteria->compare('calendar_appointment_id', $model->id);
				$leadHopper = LeadHopper::model()->find($criteria);
				if($leadHopper !== null)
				{
					echo 'Found - Lead Hopper...'.$leadHopper->id;
					echo '<br>';
					echo 'Confirm Date: '.$leadHopper->appointment_date;
					echo '<br>';
					$leadHopper->appointment_date = date("2016-05-27 H:i:s",strtotime($leadHopper->appointment_date));
					$leadHopper->save(false);
					echo 'Updated Confirm Date: '.$leadHopper->appointment_date;
					echo '<br>';
				}
				else
				{
					echo 'Not found - Lead Hopper...creating confirm';
					
					$this->createConfirmationCall($model);
					
				}
				
				echo '<br>';
			}
		}
		
		echo '<br><br>end..';
	}

	public function actionIndependenceDayConfirmPatch()
	{
		$models = CalendarAppointment::model()->findAll(array(
			'with' => 'lead',
			'condition' => '
				t.start_date >= "2016-07-02 00:00:00" 
				AND t.start_date <= "2016-07-05 23:59:59"
				AND t.title IN ("APPOINTMENT SET", "INSERT APPOINTMENT")
				AND t.status !=4
				AND t.lead_id IS NOT NULL
				AND lead.id IS NOT NULL
			',
		));
		
		echo 'count: ' . count($models);
		
		echo '<br><br>'; 
		
		if( $models )
		{
			$ctr = 1;
			
			foreach( $models as $model )
			{
				echo $ctr++;
				echo '=';
				echo $model->start_date;
				echo '<br>';
				
				$criteria = new CDbCriteria;
				$criteria->compare('calendar_appointment_id', $model->id);
				$leadHopper = LeadHopper::model()->find($criteria);
				if($leadHopper !== null)
				{
					echo 'Found - Lead Hopper...'.$leadHopper->id;
					echo '<br>';
					echo 'Confirm Date: '.$leadHopper->appointment_date;
					echo '<br>';
					$leadHopper->appointment_date = date("2016-07-01 H:i:s",strtotime($leadHopper->appointment_date));
					$leadHopper->save(false);
					echo 'Updated Confirm Date: '.$leadHopper->appointment_date;
					echo '<br>';
				}
				else
				{
					echo 'Not found - Lead Hopper...creating confirm';
					
					$this->createConfirmationCall($model);
					
				}
				
				echo '<br>';
			}
		}
		
		echo '<br><br>end..';
	}
	
	
	public function actionLaborDayConfirmPatch()
	{
		$models = CalendarAppointment::model()->findAll(array(
			'with' => 'lead',
			'condition' => '
				t.start_date >= "2016-09-03 00:00:00" 
				AND t.start_date <= "2016-09-06 23:59:59"
				AND t.title IN ("APPOINTMENT SET", "INSERT APPOINTMENT")
				AND t.status !=4
				AND t.lead_id IS NOT NULL
				AND lead.id IS NOT NULL
			',
		));
		
		echo 'count: ' . count($models);
		
		echo '<br><br>'; 
		
		if( $models )
		{
			$ctr = 1;
			
			foreach( $models as $model )
			{
				echo $ctr++;
				echo '=';
				echo $model->start_date;
				echo '<br>';
				
				$criteria = new CDbCriteria;
				$criteria->compare('calendar_appointment_id', $model->id);
				$leadHopper = LeadHopper::model()->find($criteria);
				if($leadHopper !== null)
				{
					echo 'Found - Lead Hopper...'.$leadHopper->id;
					echo '<br>';
					echo 'Confirm Date: '.$leadHopper->appointment_date;
					echo '<br>';
					$leadHopper->appointment_date = date("2016-09-02 H:i:s",strtotime($leadHopper->appointment_date));
					$leadHopper->save(false);
					echo 'Updated Confirm Date: '.$leadHopper->appointment_date;
					echo '<br>';
				}
				else
				{
					echo 'Not found - Lead Hopper...creating confirm';
					
					$this->createConfirmationCall($model);
					
				}
				
				echo '<br>';
			}
		}
		
		echo '<br><br>end..';
	}
	
	public function actionGetExpiredHoldCustomer()
	{
		$customerSkills = CustomerSkill::model()->findAll(array(
			'with' => array('skill', 'contract'),
			'condition' => 't.status=1 AND skill.id IS NOT NULL AND contract.id IS NOT NULL',
			// 'limit' => 50,
		)); 
		
		$customerHolderFalse = array();
		foreach( $customerSkills as $customerSkill )
		{
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
			
			echo 'Customer ID: '.$customerSkill->customer_id.' | Skill ID: '.$customerSkill->skill_id;
			echo '<br>';
			var_dump($customerIsCallable);
			echo '<br>';
			
			if(!$customerIsCallable)
				$customerHolderFalse[$customerSkill->customer_id] = $customerSkill->customer_id;
		}
		
		echo '<br>#########Canceled/Hold Contract####<br>';
		
		if(!empty($customerHolderFalse))
		{
			LeadHopper::model()->updateAll(array('status' => 'DONE' ), 'customer_id IN ('.implode(",", $customerHolderFalse).')' );
		}
		
		// echo implode(",", $customerHolderFalse);
	}

	public function actionCheckCustomerAgentIDFormat()
	{
		$customers = Customer::model()->findAll();
		
		foreach($customers as $customer)
		{
			echo 'Customer ID: '.$customer->id;
			echo '<br>';
			echo 'Custom Customer ID: '.$customer->custom_customer_id;
			
			echo '<br>';
			$trimmed = str_replace('-','',$customer->custom_customer_id);
			echo 'TRIMMED : '.$customer->custom_customer_id;
			echo '<br>';
			$str1= substr($trimmed, 0, 2); 
			$str2= substr($trimmed, 2, 4); 
			echo ' : ';
			echo $correctFormat = $str1.'-'.$str2;
			echo '<br>';
			
		}
	}

	public function actionLeadTimeZone()
	{
		$leadHopperEntries = LeadHopper::model()->findAll(array(
			'condition' => 'lead_timezone IS NULL',
		));
		
		if( $leadHopperEntries )
		{	
			foreach($leadHopperEntries as $leadHopper)
			{
				$lead = $leadHopper->lead;
				$customer = $leadHopper->customer;
				
				echo 'Lead ID:'.$lead->id;
				echo '<br>';
				
				if( !empty($lead->timezone) )
				{
					$timeZone = $lead->timezone;
				}
				else
				{	echo 'Empty Lead Timezone, checking on Phone area code..';
					
					$timeZone = AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $customer->phone) );
				}
				
				var_dump($timeZone);
				echo '<br>';
				
				$leadHopper->lead_timezone = $timeZone;
				$leadHopper->save(false);
			}
		}
	}

	### this function still needs to be fix to just import Leads that is EQUAL to remaining REMAINING IMPORT
	public function actionCreateJuneList()
	{
		ini_set('memory_limit', '512M');
		set_time_limit(0);
		
		spl_autoload_unregister(array('YiiBase', 'autoload'));
	
		$phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
		include($phpExcelPath . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'PHPExcel.php');

		spl_autoload_register(array('YiiBase', 'autoload'));
		
		// echo $inputFileName = 'csv/Customer-Waiting-List.csv';
		echo $inputFileName = 'csv/Customer-Waiting-List-August.xlsx';
		$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
		
		$worksheet = $objPHPExcel->getActiveSheet();

		$highestRow         = $worksheet->getHighestRow(); // e.g. 10
		$highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
		$nrColumns = ord($highestColumn) - 64;
		
		$transaction = Yii::app()->db->beginTransaction();
		
		$customerKeyHolder = array();
		try
		{
			$ctr = 1;
			for ($row = 2; $row <= $highestRow; ++$row) 
			{
				
				// $firstName = $worksheet->getCell('A'.$row)->getValue(); //ClientPrimaryKey
				// $lastName = $worksheet->getCell('B'.$row)->getValue(); //ClientPrimaryKey
				// $waitingLeadCount = $worksheet->getCell('C'.$row)->getValue(); //ClientPrimaryKey
				// $agentCode = $worksheet->getCell('D'.$row)->getValue(); //ClientPrimaryKey
				
				$agentCode = $worksheet->getCell('A'.$row)->getValue(); //ClientPrimaryKey
				$waitingLeadCount = $worksheet->getCell('D'.$row)->getValue(); //ClientPrimaryKey
				
				$customerKeyHolder[$agentCode] = array(
					// 'first_name' => $firstName,
					// 'lastName' => $lastName,
					'waitingLeadCount' => $waitingLeadCount,
					'agentCode' => $agentCode,
				);
			}
			
			foreach($customerKeyHolder as $agentCode => $customerData){
			
				$criteria = new CDbCriteria;
				$criteria->compare('custom_customer_id', $agentCode);
				// $criteria->compare('status', 1);
				$customer = Customer::model()->find($criteria);
				
				if($customer !== null)
				{
					echo '<br>';
					echo '<Br>';
					echo '--------- '.$agentCode;
					echo ' ##### '.$customer->id;
					echo '<br>';
					$customer_id = $customer->id;
					
					$customerCalendar = Calendar::model()->find(array(
						'condition' => 'customer_id = :customer_id',
						'params' => array(
							':customer_id' => $customer->id,
						),
					));
			
					$customerSkill = CustomerSkill::model()->find(array(
						'condition' => 'customer_id = :customer_id AND status=1',
						'params' => array(
							':customer_id' => $customer->id,
						),
					));
				
					$contract = $customerSkill->contract;
					
					if($customerCalendar !== null && $customerSkill !== null)
					{
						$criteria = new CDbCriteria;
						$criteria->compare('name', 'August 2016');
						$criteria->compare('customer_id', $customer->id);
						
						$model = Lists::model()->find($criteria);
						
						if($model === null)
						{
							$model = new Lists;
							$model->name = 'August 2016';
							$model->customer_id = $customer->id;
							$model->skill_id = $customerSkill->skill_id;
							$model->calendar_id = $customerCalendar->id;
							$model->status = 1;
							
							if(!$model->save())
							{
								print_r($model->getErrors());
								exit;
							}
							else
							{
								echo '<br>New List created<br>';
							}
							
						}
						else
						{
							$model->skill_id = $customerSkill->skill_id;
							$model->calendar_id = $customerCalendar->id;
							$model->save(false);
						}
						
						#### adding of leads ####
						
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

							echo 'Import Limit: '.$importLimit;
							echo '<br>';
							
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
									
									$customerLeadImportLog->save(false);
								}
							}
						}
					
						$criteria = new CDbCriteria;
						$criteria->compare('customer_id', $customer->id);
						$criteria->compare('type', 1);
						$criteria->compare('status', 1);
						$criteria->compare('list_id', null);
						// $criteria->limit = $importLimit + 50;
						$leadsWaiting = Lead::model()->findAll($criteria);
						
						//import from leads waiting
						
						echo 'Leads Waiting: '.count($leadsWaiting);
						
						
						$customerLeadImportLog = CustomerLeadImportLog::model()->find(array(
							'condition' => 'customer_id = :customer_id AND month = :month AND year = :year',
							'params' => array(
								':customer_id' => $customer_id,
								':month' => date('F'),
								':year' => date('Y'),
							),
						));
								
						echo '<Br>';
						echo 'Leads already imported: '.$customerLeadImportLog->leads_imported;
						
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
								
								if($leadsWaiting > $importLimit)
									$customerLeadImportLog->leads_imported = $customerLeadImportLog->leads_imported - $importLimit;
								else 
									$customerLeadImportLog->leads_imported = $customerLeadImportLog->leads_imported - $leadsWaiting;
								
								$leadsWaitingModel->list_id = $model->id;
								
								if( $customerLeadImportLog && $customerLeadImportLog->leads_imported < $importLimit )
								{
									if( $leadsWaitingModel->save(false) )
									{
										$leadsImported++;
											
										if( $customerLeadImportLog )
										{
											$customerLeadImportLog->leads_imported = $customerLeadImportLog->leads_imported + 1;
											$customerLeadImportLog->save(false);
										}
									}
								}
							}
						}
						
						if( $leadsImported > 0 )
						{
							Yii::app()->user->setFlash('success', '<b>'.$model->name.'</b> was created successfully.');
							
							$history = new CustomerHistory;
							
							$history->setAttributes(array(
								'model_id' => $model->id, 
								'customer_id' => $model->customer_id,
								'user_account_id' => Yii::app()->user->account->id,
								'page_name' => 'Leads',
								'content' => $model->name.' | '.$leadsImported.' Leads Imported from Names waiting',
								'type' => $history::TYPE_ADDED,
							));
							
							$history->save(false);
							
							// $transaction->commit();
							
							// $this->redirect(array('leads/index', 'id'=>$model->id, 'customer_id'=>$customer_id));
						}
						else
						{
							echo $customer_id.': No leads imported.';
						}
						
					}
					else 
					{
						$setFlashMessage = '';
						'<br>Customer lack information: '.$customerPk;
						
						echo '<br>';
						if( empty($customerCalendar) )
						{
							$setFlashMessage .= '<li>Please create atleast <b>1 calendar</b> in order to create a list - '.CHtml::link('Click to create a calendar', array('/customer/calendar', 'customer_id'=>$customer_id)).'.</li>';
						}
						
						if( empty($customerSkill) )
						{
							$setFlashMessage .= '<li>Please add atleast <b>1 skill</b> in order to create a list - '.CHtml::link('Click to add a skill', array('/customer/customerSkill', 'customer_id'=>$customer_id)).'.</li>';
						}
						
						echo $setFlashMessage;
					}
				}
				else
				{
					echo '<br>Customer #agent : ';
					echo $agentCode.' not found <br>';
				}
			}
			
			$transaction->commit();
		}
		catch(Exception $e)
		{
			$transaction->rollback();
			echo '<pre>';
			print_r($e);
		}
	}

	public function actionUpdateRecertifyDate()
	{
		$criteria = new CDbCriteria;
		$criteria->compare('name', 'June 2016');
		
		$models = Lists::model()->findAll($criteria);
		
		foreach($models as $model)
		{
			$criteria = new CDbCriteria;
			$criteria->compare('list_id', $model->id);
			$leads = Lead::model()->findAll($criteria);
			
			foreach($leads as $lead)
			{
				$lead->recertify_date = $this->_computeForSkillMaxLeadLifeBeforeRecertify($model);
				$lead->save(false);
			}
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
	
	public function actionUpdateQueueViewer(){
		$customerQueues = CustomerQueueViewer::model()->updateAll(array('dials_until_reset'=> 0),
			'customer_id IN (
				958	,
				524	,
				919	,
				671	,
				442	,
				780	,
				833	,
				897	,
				94	,
				826	,
				1005,
				983	,
				738	,
				607	,
				966	,
				635	,
				779	,
				996	,
				771	,
				579	,
				888	,
				830	,
				451	,
				587	,
				756	,
				744	,
				775	,
				1020,
				159	,
				160	,
				877	,
				937	,
				845	,
				677	,
				433	,
				985	,
				903	,
				399	,
				838	,
				486	,
				166	,
				472	,
				669	,
				884	,
				849	,
				786	,
				147	,
				778	,
				527	,
				202	,
				800	,
				923	,
				537	,
				940	,
				798	,
				980	,
				851	,
				846	,
				893	,
				754	,
				87	,
				175	,
				751	,
				703	,
				470	,
				127	,
				645	,
				859	,
				904	,
				927	,
				655	,
				613	,
				799	,
				154	,
				694	,
				862	,
				844	,
				922	,
				476	,
				1066,
				782	,
				519	,
				784	,
				934	,
				947	,
				608	,
				969	,
				627	,
				982	,
				961	,
				203	,
				657	,
				791	,
				907	,
				878	,
				993	,
				843	,
				108	,
				1004,
				889	,
				121	,
				930	,
				439	,
				171	,
				933	,
				885	,
				88	,
				971	,
				895	,
				603	,
				719	,
				161	,
				783	,
				589	,
				693	,
				824	,
				455	,
				847	,
				872	,
				887	,
				946	,
				140	,
				580	,
				82	,
				834	,
				112	,
				806	,
				437	,
				924	,
				145	,
				773	,
				914	,
				976	,
				169	,
				767	,
				184	,
				963	,
				431	,
				974	,
				803	
			)'
		);
	}

	public function actionCronTriggerCustomerBoost()
	{
		$criteria = new CDbCriteria;
		$criteria->compare('status', 1);
		$criteria->compare('is_boost_triggered', 0);
		
		$cqviewerBoosts = CustomerQueueViewerBoost::model()->find($criteria);
		
		foreach($cqviewerBoosts as $cqviewerBoost)
		{
			
			$criteria = new CDbCriteria;
			$criteria->compare('customer_id', $cqviewerBoost->customer_id);
			$criteria->compare('skill_id', $cqviewerBoost->skill_id);
			
			$cQueueViewer= CustomerQueueViewer::model()->find($criteria);
			
			if($cQueueViewer === null)
			{
				echo 'ID: '.$cqviewerBoost->id.' - QueueViewer does not exist. Can\'t Boost.';
				echo '<br>';
			}
			
			if($cqviewerBoost->type == 1) // NOW
			{
				if($cQueueViewer !== null)
				{
					$cqviewerBoosts->is_boost_triggered = 1;
					$this->forceUpdateCustomerQueueViewerPriority($cQueueViewer);
					$cqviewerBoosts->save(false);
				}
				
			}
			
			if($cqviewerBoost->type == 2) // BY SCHEDULE
			{
				if( strtotime($cqviewerBoost->beginning_date) > strtotime(date('Y-m-d H:i:s')) )
				{
					if($cQueueViewer !== null)
					{
						$cqviewerBoosts->is_boost_triggered = 1;
						$this->forceUpdateCustomerQueueViewerPriority($cQueueViewer);
						$cqviewerBoosts->save(false);
					}
					
				}
			}
			
			
		}
		
	}
	
	public function forceUpdateCustomerQueueViewerPriority()
	{
		$cQueueViewer->priority = 10;
		$cQueueViewer->save(false);
		
		return;
	}

	public function actionRecertifyList()
	{
		ini_set('memory_limit', '512M');
		set_time_limit(0);
		
		spl_autoload_unregister(array('YiiBase', 'autoload'));
	
		$phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
		include($phpExcelPath . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'PHPExcel.php');

		spl_autoload_register(array('YiiBase', 'autoload'));
		
		// echo $inputFileName = 'csv/Customer-Waiting-List.csv';
		echo $inputFileName = 'csv/Customer-Waiting-List-August.xlsx';
		$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
		
		$worksheet = $objPHPExcel->getActiveSheet();

		$highestRow         = $worksheet->getHighestRow(); // e.g. 10
		$highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
		$nrColumns = ord($highestColumn) - 64;
		
		$transaction = Yii::app()->db->beginTransaction();
		
		$customerKeyHolder = array();
		try
		{
			$ctr = 1;
			for ($row = 2; $row <= $highestRow; ++$row) 
			{
				
				// $firstName = $worksheet->getCell('A'.$row)->getValue(); //ClientPrimaryKey
				// $lastName = $worksheet->getCell('B'.$row)->getValue(); //ClientPrimaryKey
				// $waitingLeadCount = $worksheet->getCell('C'.$row)->getValue(); //ClientPrimaryKey
				// $agentCode = $worksheet->getCell('D'.$row)->getValue(); //ClientPrimaryKey
				
				$agentCode = $worksheet->getCell('A'.$row)->getValue(); //ClientPrimaryKey
				$waitingLeadCount = $worksheet->getCell('D'.$row)->getValue(); //ClientPrimaryKey
				
				$customerKeyHolder[$agentCode] = array(
					// 'first_name' => $firstName,
					// 'lastName' => $lastName,
					'waitingLeadCount' => $waitingLeadCount,
					'agentCode' => $agentCode,
				);
			}
			
			$customerOffset =array();
			$totalLeads = 0;
			foreach($customerKeyHolder as $agentCode => $customerData){
			
				$criteria = new CDbCriteria;
				$criteria->compare('custom_customer_id', $agentCode);
				// $criteria->compare('status', 1);
				$customer = Customer::model()->find($criteria);
				
				if($customer !== null)
				{
					// echo '<br>';
					// echo '<Br>';
					// echo '--------- '.$agentCode;
					// echo ' ##### '.$customer->id;
					// echo '<br>';
					$customer_id = $customer->id;
					
					$customerCalendar = Calendar::model()->find(array(
						'condition' => 'customer_id = :customer_id',
						'params' => array(
							':customer_id' => $customer->id,
						),
					));
			
					$customerSkill = CustomerSkill::model()->find(array(
						'condition' => 'customer_id = :customer_id AND status=1',
						'params' => array(
							':customer_id' => $customer->id,
						),
					));
				
					$contract = $customerSkill->contract;
					
					if($customerCalendar !== null && $customerSkill !== null)
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
						// echo '<br>Lead Limit:'.$importLimit;
						
						$criteria = new CDbCriteria;
						$criteria->compare('name', 'August 2016');
						$criteria->compare('customer_id', $customer->id);
						
						$model = Lists::model()->find($criteria);
						
						$criteria = new CDbCriteria;
						$criteria->compare('list_id', $model->id);
						$leads = Lead::model()->findAll($criteria);
						
						
						if(!empty($leads))
						{
							
							$totalLeads = $totalLeads + count($leads);
							echo '<br>Customer ID:'.$customer->id.' - Lead Count:'.count($leads);
							// foreach($leads as $lead)
							// {
								// $lead->recertify_date = $this->_computeForSkillMaxLeadLifeBeforeRecertify($lead->list);
								// $lead->recertify_date = '2016-09-01';
								// $lead->number_of_dials = 0;
								// $lead->status = 1;
								// $lead->save(false);
							
							// }
						}
						else
						{
							echo '<br> No Leads in List';
						}
						
						$criteria = new CDbCriteria;
						$criteria->compare('list_id', $model->id);
						$criteria->offset = $importLimit;
						$leads = Lead::model()->findAll($criteria);
						
						
						if(!empty($leads))
						{
							echo '<br>Lead Count Offset:'.count($leads);
							foreach($leads as $lead)
							{
								// $lead->recertify_date = $this->_computeForSkillMaxLeadLifeBeforeRecertify($lead->list);
								// $lead->recertify_date = '2016-09-01';
								// $lead->number_of_dials = 0;
								// $lead->status = 1;
								$lead->list_id = null;
								$lead->save(false);
								$customerOffset[$customer->id] = $customer->id;
							
							}
						}
						else
						{
							// echo '<br> No Leads Offset in List';
						}
					}
				}
				else
				{
					echo '<br>Customer #agent : ';
					echo $agentCode.' not found <br>';
				}
			}
			
			
			$transaction->commit();
			
			echo '<br>totalLeads: '.$totalLeads;
			echo '<br>customer with offset<br>';
			echo implode(',',$customerOffset);
		}
		catch(Exception $e)
		{
			$transaction->rollback();
			echo '<pre>';
			print_r($e);
		}
	}

	public function actionStaffPermissionScript()
	{
		$staffBatch0 = array(25,
		28,
		34,
		818,
		819,
		821,
		828,
		829,
		831,
		832,
		835,
		836,
		838,
		839,
		841,
		844,
		845,
		846,
		847,
		848,
		853,
		864,
		866,
		867,
		868,
		870,
		914,
		917,
		922,
		926,
		928,
		929,
		933,
		935,
		940,
		943,
		944,
		949,
		950,
		952,
		957,
		961,
		963,
		967,
		971,
		973,
		978,
		979,
		984,
		991,
		998,
		1008,
		1014,
		1015,
		1016,
		1029,
		1033,
		1040,
		1046,
		1049,
		1054,
		1056,
		1057,
		1069,
		1075,
		1082,
		1087,
		1093,
		1099,
		1101,
		1102,
		1110,
		1111,
		1115,
		1118,
		1135,
		1145,
		1151,
		1152,
		1155,
		1156,
		1157,
		1162,
		1169,
		1171,
		1174,
		1180,
		1183,
		1184,
		1191,
		1192,
		1193,
		1199,
		1201,
		1202,
		1205,
		1206,
		1208,
		1214,
		1216
		);

		$staffBatch1 = array(1224,
		1225,
		1227,
		1232,
		1233,
		1239,
		1240,
		1241,
		1242,
		1254,
		1258,
		1263,
		1265,
		1266,
		1271,
		1278,
		1281,
		1291,
		1294,
		1295,
		1296,
		1298,
		1302,
		1313,
		1315,
		1316,
		1318,
		1319,
		1327,
		1342,
		1344,
		1345,
		1348,
		1350,
		1352,
		1367,
		1369,
		1371,
		1383,
		1385,
		1387,
		1388,
		1389,
		1392,
		1404,
		1407,
		1408,
		1409,
		1412,
		1415,
		1428,
		1429,
		1430,
		1431,
		1438,
		1439,
		1442,
		1443,
		1444,
		1456,
		1458,
		1467,
		1468,
		1469,
		1470,
		1475,
		1476,
		1477,
		1481,
		1482,
		1483,
		1505,
		1506,
		1511,
		1512,
		1515,
		1516,
		1531,
		1532,
		1546,
		1551,
		1553,
		1555,
		1556,
		1563,
		1570,
		1572,
		1578,
		1580,
		1584,
		1585,
		1586,
		1587,
		1590,
		1591,
		1595,
		1603,
		1604);


		$staffBatch3 = array(1605,
		1606,
		1609,
		1615,
		1617,
		1622,
		1623,
		1626,
		1627,
		1630,
		1632,
		1634,
		1641,
		1642,
		1644,
		1645,
		1647,
		1651,
		1660,
		1661,
		1670,
		1682,
		1688,
		1692,
		1693,
		1695,
		1696,
		1702,
		1712,
		1714,
		1724,
		1737,
		1742,
		1745,
		1747,
		1749,
		1750,
		1752,
		1757,
		1761,
		1765,
		1774,
		1778,
		1779,
		1781,
		1790,
		1791,
		1792,
		1793,
		1794,
		1797,
		1807,
		1809,
		1813,
		1832,
		1834,
		1837,
		1849,
		1852,
		1854,
		1859,
		1862,
		1866,
		1871,
		1874,
		1876,
		1889,
		1890,
		1894,
		1897,
		1907,
		1911,
		1913,
		1914,
		1915,
		1937,
		1938,
		1939,
		1941,
		1942,
		1948,
		1954,
		1955,
		1961,
		1966,
		1971,
		1972,
		1986,
		1992,
		2005,
		2019,
		2023,
		2043,
		2048,
		2052,
		2058,
		2059,
		2060);

		$staffBatch4 = array(2066,
		2071,
		2072,
		2076,
		2079,
		2082,
		2083,
		2084,
		2087,
		2088,
		2089,
		2100,
		2104,
		2105,
		2121,
		2146,
		2158,
		2159,
		2162,
		2166,
		2169,
		2172,
		2175,
		2176,
		2177,
		2183,
		2186,
		2194,
		2195,
		2201,
		2205,
		2207,
		2211,
		2213,
		2228,
		2230,
		2232,
		2238,
		2248,
		2250,
		2258,
		2260,
		2261,
		2264,
		2267,
		2269,
		2273,
		2275,
		2289,
		2299,
		2300,
		2301,
		2305,
		2312,
		2315,
		2316,
		2318,
		2328,
		2342,
		2344,
		2345,
		2350,
		2353,
		2354,
		2355,
		2363,
		2364,
		2368,
		2371,
		2372,
		2373,
		2374,
		2375,
		2376,
		2385,
		2386,
		2389,
		2395,
		2396,
		2398,
		2403,
		2404,
		2413,
		2419,
		2423,
		2425,
		2426,
		2432,
		2441,
		2447,
		2450,
		2461,
		2465,
		2473,
		2476,
		2480,
		2481,
		2490,
		2491);


		$staffBatch5 = array(2493,
		2494,
		2496,
		2497,
		2504,
		2506,
		2509,
		2513,
		2514,
		2520,
		2529,
		2539,
		2549,
		2551,
		2555,
		2556,
		2563,
		2569,
		2570,
		2571,
		2572,
		2574,
		2578,
		2583,
		2585,
		2588,
		2590,
		2592,
		2598,
		2600,
		2601,
		2602,
		2603,
		2604,
		2606,
		2609,
		2611,
		2616,
		2617,
		2619,
		2620,
		2623,
		2628,
		2629,
		2630,
		2632,
		2633,
		2644,
		2651,
		2657,
		2664,
		2665,
		2670,
		2672,
		2676,
		2680,
		2690,
		2695,
		2697,
		2708,
		2709,
		2717,
		2723,
		2731,
		2736,
		2738,
		2741,
		2742,
		2744,
		2747,
		2751,
		2754,
		2756,
		2766,
		2767,
		2770,
		2778,
		2782,
		2790,
		2795,
		2811,
		2815,
		2817,
		2821,
		2822,
		2824,
		2825,
		2829,
		2831,
		2841,
		2844,
		2846,
		2847,
		2849,
		2850,
		2862,
		2863,
		2867);

		$staffBatch6 = array(2874,
		2889,
		2892,
		2895,
		2898,
		2901,
		2902,
		2905,
		2907,
		2909,
		2910,
		2914,
		2915,
		2918,
		2921,
		2937,
		2938,
		2939,
		2943,
		2948,
		2957,
		2958,
		2967,
		2968,
		2969,
		2970,
		2971,
		2974,
		2976,
		2978,
		2979,
		2981,
		2989,
		2990,
		2994,
		2997,
		2999,
		3001,
		3002,
		3004,
		3005,
		3006,
		3007,
		3015,
		3037,
		3038,
		3042,
		3044,
		3045,
		3048,
		3052,
		3053,
		3058,
		3066,
		3068,
		3072,
		3075,
		3077,
		3083,
		3085,
		3086,
		3089,
		3092,
		3094,
		3098,
		3099,
		3105,
		3106,
		3114,
		3126,
		3128,
		3132,
		3136,
		3138,
		3139,
		3140,
		3144,
		3150,
		3153,
		3159,
		3161,
		3169,
		3189,
		3193,
		3194,
		3196,
		3198,
		3200,
		3203,
		3204,
		3205,
		3206,
		3207,
		3215,
		3222,
		3223,
		3226,
		3227,
		3229);

		$staffBatch7 = array(3236,
		3237,
		3238,
		3243,
		3245,
		3246,
		3248,
		3249,
		3250,
		3252,
		3254,
		3259,
		3264,
		3271,
		3272,
		3273,
		3274,
		3275,
		3290,
		3298,
		3300,
		3301,
		3302,
		3303,
		3304,
		3309,
		3319,
		3322,
		3325,
		3328,
		3331,
		3335,
		3336,
		3337,
		3341,
		3346,
		3351,
		3357,
		3363,
		3364,
		3365,
		3366,
		3373,
		3382,
		3385,
		3387,
		3390,
		3395,
		3410,
		3414,
		3417,
		3421,
		3424,
		3431,
		3440,
		3441,
		3447,
		3453,
		3454,
		3458,
		3459,
		3461,
		3462,
		3464,
		3475,
		3477,
		3478,
		3481,
		3484,
		3487,
		3489,
		3498,
		3499,
		3500,
		3504,
		3506,
		3516,
		3527,
		3529,
		3530,
		3534,
		3535,
		3542,
		3543,
		3544,
		3546,
		3550,
		3551,
		3553,
		3555,
		3560,
		3563,
		3566,
		3569,
		3570,
		3573,
		3579,
		3582,
		3583);

		$staffBatch8 = array(3588,
		3594,
		3604,
		3605,
		3606,
		3609,
		3611,
		3614,
		3616,
		3629,
		3634,
		3635,
		3636,
		3641,
		3642,
		3644,
		3647,
		3656,
		3659,
		3666,
		3669,
		3670,
		3671,
		3685,
		3686,
		3692,
		3698,
		3699,
		3702,
		3708,
		3715,
		3724,
		3727,
		3733,
		3735,
		3737,
		3738,
		3741,
		3745,
		3747,
		3751,
		3752,
		3753,
		3756,
		3764,
		3766,
		3767,
		3769,
		3772,
		3779,
		3792,
		3793,
		3797,
		3801,
		3802,
		3803,
		3815,
		3823,
		3824,
		3826,
		3829,
		3840,
		3842,
		3844,
		3847,
		3848,
		3855,
		3856,
		3857,
		3859,
		3860,
		3869,
		3875,
		3881,
		3885,
		3888,
		3890,
		3893,
		3894,
		3895,
		3896,
		3899,
		3901,
		3903,
		3905,
		3906,
		3907,
		3910,
		3912,
		3918,
		3922,
		3923,
		3925,
		3927,
		3928,
		3930,
		3934,
		3935,
		3936
		);

		$staffBatch9 = array(3939,
		3946,
		3948,
		3950,
		3951,
		3953,
		3954,
		3955,
		3958,
		3959,
		3966,
		3972,
		3976,
		3979,
		3981,
		3983,
		3984,
		3987,
		3990,
		3992,
		3994,
		4006,
		4007,
		4008,
		4009,
		4010,
		4016,
		4019,
		4034,
		4046,
		4074,
		4092,
		4093,
		4106,
		4110,
		4112,
		4114,
		4117,
		4127,
		4129,
		4132,
		4139,
		4140,
		4141,
		4142,
		4150,
		4156,
		4160,
		4166,
		4167,
		4170,
		4171,
		4181,
		4182,
		4183,
		4184,
		4186,
		4206,
		4208,
		4216,
		4220,
		4221,
		4224,
		4233,
		4234,
		4235,
		4236,
		4237,
		4240,
		4257,
		4260,
		4261,
		4262,
		4268,
		4269,
		4270,
		4278,
		4279,
		4297,
		4298,
		4300);
		
		$ctr = 0;
		foreach($staffBatch8 as $accountId)
		{
			$account = Account::model()->findByPk($accountId);
			
			if($account !== null)
			{
				CustomerAccountPermission::autoAddPermissionKey($account);
				$ctr++;
			}
			else
			{
				echo $accountId;
				echo ',';
			}
		}
		
		echo 'Total Number of Staff:'.count($staffBatch8);
		echo '<br>';
		echo 'Added Staff Permission:'.$ctr;
		echo '<br>';
	}
}

?>