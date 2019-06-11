<?php 

ini_set('memory_limit', '20000M');
set_time_limit(0);

class CalendarTestController extends Controller
{
	// public $layout='//layouts/column2';
	
	
	public function actionIndex()
	{
		if( !Yii::app()->user->isGuest && in_array(Yii::app()->user->account->account_type_id, array(Account::TYPE_CUSTOMER, Account::TYPE_CUSTOMER_OFFICE_STAFF)) )
		{
			$authAccount = Yii::app()->user->account;
			
			if( $authAccount->getIsCustomer() )
			{
				$customer = Customer::model()->find(array(
					'condition' => 'account_id = :account_id',
					'params' => array(
						':account_id' => $authAccount->id,
					),
				));
				
				if( $customer && $customer->id != $_REQUEST['customer_id'] )
				{
					$this->redirect(array('/calendar/index', 'customer_id'=>$customer->id));
				}
			}
			
			if( $authAccount->getIsCustomerOfficeStaff() )
			{
				$customerOfficeStaff = CustomerOfficeStaff::model()->find(array(
					'condition' => 'account_id = :account_id',
					'params' => array(
						':account_id' => $authAccount->id,
					),
				));
				
				if( $customerOfficeStaff && $customerOfficeStaff->customer_id != $_REQUEST['customer_id'] )
				{
					$this->redirect(array('/calendar/index', 'customer_id'=>$customerOfficeStaff->customer_id));
				}
			}
		}
		
		
		$model = array();
		
		if( isset($_REQUEST['calendar_id']) )
		{
			$model = Calendar::model()->find(array(
				'condition' => 'id = :id AND status=1',
				'params' => array(
					':id' => $_REQUEST['calendar_id'],
				),
			));
		}

		if( empty($model) )
		{
			$activeCalendar = Calendar::model()->find(array(
				'condition' => 'customer_id = :customer_id AND status=1',
				'params' => array(
					':customer_id' => $_REQUEST['customer_id'],
				),
			));

			if( $activeCalendar )
			{
				$model = $activeCalendar;
			}
			
			echo '<pre>';
				print_r($model->attributes);
			exit;
		}
		
		$officeOptions = array();
		
		$offices = CustomerOffice::model()->findAll(array(
			'condition' => 'customer_id = :customer_id AND status=1 AND is_deleted=0',
			'params' => array(
				':customer_id' => $_REQUEST['customer_id'],
			),							
		));
		
		if( $offices )
		{
			foreach( $offices as $office )
			{
				$officeOptions[$office->id] = $office->office_name; 
			}
		}
		
		$calendarOptions = array();
		
		if( $model )
		{
			$calendars = Calendar::model()->findAll(array(
				'condition' => 'office_id = :office_id AND status=1',
				'params' => array(
					':office_id' => $model->office_id,
				),							
			));
			
			if( $calendars )
			{
				foreach( $calendars as $calendar )
				{
					$calendarOptions[$calendar->id] = $calendar->name; 
				}
			}
		}
		
		
		if( isset($_POST['loadEvents']) )
		{
			$result = array(
				'status' => 'error',
				'message' => 'No events found.',
				'events' => array(),
			);
			
			// $currentDate = $_POST['currentDate'];
			$currentDate = $_POST['today'];
			// $currentDate = $model->date_updated;
			
			if( $model->use_default_schedule == 1 )
			{
				// $this->applyDefaultSchedule($model, $currentDate);
			}
			else
			{
				$result['events'] = array_merge($result['events'], $this->applyCustomSchedule($model, $currentDate)); 				
			}
			
			
			$appointmentsQuery = "
				SELECT 
					ca.id, ca.account_id, ca.lead_id, l.first_name, l.last_name, ca.title, ca.details, ca.start_date, ca.end_date, ca.is_custom, ca.status
				FROM ud_calendar_appointment ca
				LEFT JOIN ud_lead l ON l.id = ca.lead_id
				WHERE ca.calendar_id = '".$model->id."'
				AND ca.status NOT IN (3, 4) 
				AND (
					DATE(ca.start_date) >= '".date('Y-m-d', strtotime('+'.$model->minimum_days_appointment_set.' days', strtotime($_POST['today'])))."' 
					OR 
					ca.title NOT IN ('AVAILABLE', 'CANCEL APPOINTMENT', 'RESCHEDULE APPOINTMENT')
				)
			";

			$appointments = Yii::app()->db->createCommand($appointmentsQuery)->queryAll();

			if( $appointments )
			{
				foreach( $appointments as $appointment )
				{
					$oldAppointmentData = $appointment;
					
					switch( strtoupper($appointment['title']) )
					{
						default: case 'AVAILABLE': $color = '#6FB3E0'; break;
						case 'CONFIRMED APPOINTMENT': $color = '#87B87F'; break;
						case 'APPOINTMENT SET': $color = '#FFB752'; break;
						case 'CHANGE APPOINTMENT': $color = '#FFB752'; break;
						case 'INSERT APPOINTMENT': $color = '#FFB752'; break;
						case 'LOCATION CONFLICT': $color = '#D15B47'; break;
						case 'SCHEDULE CONFLICT': $color = '#D15B47'; break;
						case 'RESCHEDULE APPOINTMENT': $color = '#D15B47'; break;
						case 'NO SHOW RESCHEDULE': $color = '#D58CDF'; break;	
						case 'CANCEL APPOINTMENT': $color = '#D15B47'; break;	
						case 'BLACKOUT DAYS': $color = '#333333'; break;	
					}
					
					if( !in_array($appointment['title'], array('BLACKOUT DAYS', 'INSERT APPOINTMENT', 'APPOINTMENT SET', 'NO SHOW RESCHEDULE')) && strtotime($appointment['start_date']) < time())
					{
						$color = '#A0A0A0';
					}
					
					if( !empty($appointment['lead_id']) )
					{
						$appointment['title'] = $appointment['first_name'].' '.$appointment['last_name'];
					}
					
					// if( !in_array($appointment['title'], array('BLACKOUT DAYS', 'INSERT APPOINTMENT', 'APPOINTMENT SET', 'NO SHOW RESCHEDULE')) && strtotime($appointment['start_date']) < time() && $appointment['is_custom'] != 2)
					// {
						// $appointment['title'] = 'PAST DATE';
					// }
					
					$isCustom = $appointment['is_custom'];
		
					if( !empty($appointment['lead_id']) )
					{
						$appointment['is_custom'] = 3;
					}
					
					if( strtotime($appointment['start_date']) < time() )
					{
						$appointment['is_custom'] = 3;
					}
					
					if( $appointment['title'] == 'BLACKOUT DAYS' )
					{
						$result['events'][] = array(
							'id' => $appointment['id'],
							'title' => $appointment['title'],
							'details' => $appointment['details'],
							'start_date' => date('c', strtotime($appointment['start_date'])),
							'end_date' => date('c', strtotime('+1 day', strtotime($appointment['end_date']))),
							'color' => $color,
							'allDay' => true ,
							'is_custom' => $appointment['is_custom'],
							'status' => $appointment['status'],
						);
					}
					else
					{
						$authAccount = Yii::app()->user->account;
						$displayTitle = $appointment['title'];
						
						if( in_array($oldAppointmentData['title'], array("LOCATION CONFLICT","SCHEDULE CONFLICT")) && $authAccount->account_type_id == Account::TYPE_AGENT)
						{
							if($appointment['account_id'] == $authAccount->id)
								$displayTitle = $appointment['title'];
							else
								$displayTitle = '';
						}
						
						$result['events'][] = array(
							'id' => $appointment['id'],
							'title' => $displayTitle,
							'details' => $appointment['details'],
							'start_date' => date('c', strtotime($appointment['start_date'])),
							'end_date' => date('c', strtotime($appointment['end_date'])),
							'color' => $color,
							'allDay' => false,
							'is_custom' => $appointment['is_custom'],
							'status' => $appointment['status'],
						);
					}
				}
			}
			
			
			$result['events'] = array_merge($result['events'], $this->loadHolidaySlots($model, $currentDate));
			
			$result['status'] = 'success';
			$result['message'] = '';
			$result['min_days_out'] = date('Y-m-d', strtotime('+'.$model->minimum_days_appointment_set.' days', strtotime($_POST['today'])));
				
			echo json_encode($result);
			Yii::app()->end();
		}
		
		$this->render('index', array(
			'model' => $model,
			'calendar_id' => isset($_REQUEST['calendar_id']) ? $_REQUEST['calendar_id'] : $model->id,
			'customer_id' => $_REQUEST['customer_id'] ? $_REQUEST['customer_id'] : null,
			'office_id' => isset($_REQUEST['office_id']) ? $_REQUEST['office_id'] : $model->office_id,
			'officeOptions' => $officeOptions,
			'calendarOptions' => $calendarOptions,
		));
	}

	
	public function actionCreate()
	{
		$authAccount = Yii::app()->user->account;
		
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'event' => array(),
			'html' => $html,
		);
		
		$valid = false;
		
		$calendar = Calendar::model()->findByPk( isset($_POST['calendar_id']) ? $_POST['calendar_id'] : $_POST['CalendarAppointment']['calendar_id']);
		
		
		if( isset($_POST['CalendarAppointment']) )
		{
			if( $_POST['CalendarAppointment']['title'] == 'AVAILABLE' )
			{
				$message = '';
				
				if( empty($_POST['CalendarAppointment']['start_date_time']) || empty($_POST['CalendarAppointment']['end_date_time']) ) 
				{
					$message .= 'Start time and End time are required';
				}
				
				if( $message != '' )
				{
					$result['message'] = $message;
					
					echo json_encode($result);
					exit;
				}
			}
			
			if( in_array($_POST['CalendarAppointment']['title'], array('APPOINTMENT SET', 'INSERT APPOINTMENT')) )
			{
				$message = '';
				
				if( empty($_POST['CalendarAppointment']['lead_id']) ) 
				{
					$message .= 'Lead is required';
				}
				
				if( empty($_POST['CalendarAppointment']['start_date_time']) || empty($_POST['CalendarAppointment']['end_date_time']) ) 
				{
					$message .= 'Start time and End time are required';
				}
				
				if( empty($_POST['CalendarAppointment']['lead_id']) && (empty($_POST['CalendarAppointment']['start_date_time']) || empty($_POST['CalendarAppointment']['end_date_time'])) ) 
				{
					$message = 'Lead, Start time and End time are required';
				}
				
				if( $message != '' )
				{
					$result['message'] = $message;
					
					echo json_encode($result);
					exit;
				}
				
				if( $message != '' )
				{
					$result['message'] = $message;
					
					echo json_encode($result);
					exit;
				}
			}
			
			$dbChanges = 0;
		
			$startDateTime = date('H:i:s', strtotime($_POST['CalendarAppointment']['start_date_time']));
			
			$startDate = date('Y-m-d', strtotime($_POST['current_date'])).' '.$startDateTime;
			
			$endDateTime = date('H:i:s', strtotime($_POST['CalendarAppointment']['end_date_time']));
			
			$endDate = date('Y-m-d', strtotime($_POST['current_date'])).' '.$endDateTime;
			$endDate = date('Y-m-d H:i:s', strtotime($endDate));
			
			$postedAppointmentLength = floor( (strtotime($endDate) - strtotime($startDate)) / 60);
				
			switch( $calendar->appointment_length )
			{
				default: 
				case '30 Minutes': $calendarAppointmentLength=30; break; 
				case '45 Minutes': $calendarAppointmentLength=45; break; 
				case '1 Hour': $calendarAppointmentLength=60; break; 
				case '1 Hour 30 Minutes': $calendarAppointmentLength=90; break; 
				case '2 Hours': $calendarAppointmentLength=120; break; 
			} 
			
			if( $postedAppointmentLength <= $calendarAppointmentLength )
			{
				foreach( Calendar::createTimeRange($startDate, $endDate, $postedAppointmentLength . ' Minutes') as $time ) 
				{
					$model = new CalendarAppointment;
					$model->calendar_id = $calendar->id;
		
					$model->attributes = $_POST['CalendarAppointment'];
					
					if( $model->title == 'SCHEDULE CONFLICT' )
					{
						$model->status = 2;
					}
					
					$start_date_time = date('H:i:s', $time);
					
					$start_date = date('Y-m-d', strtotime($_POST['current_date'])).' '.$start_date_time;
					
					$start_date_year = date('Y', strtotime($start_date));
					$start_date_month = date('m', strtotime($start_date));
					$start_date_day = date('d', strtotime($start_date));
					
					$end_date = date('Y-m-d H:i:s', strtotime('+'.$postedAppointmentLength.' Minutes' , strtotime($start_date)));
					
					$end_date_time = date('H:i:s', strtotime($end_date));
					
					$end_date_year = date('Y', strtotime($end_date));
					$end_date_month = date('m', strtotime($end_date));
					$end_date_day = date('d', strtotime($end_date));
						
					if( strtotime($end_date) <= strtotime($endDate) )
					{	
						$model->setAttributes(array(
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
						));
							
						$existingCalendarAppointment = CalendarAppointment::model()->find(array(
							'condition' => 'calendar_id = :calendar_id AND status NOT IN(3,4) AND ((end_date > :start_date ) AND (start_date < :end_date))',
							'params' => array(
								':calendar_id' => $calendar->id,
								':start_date' => date('Y-m-d H:i:s', strtotime($start_date)),
								':end_date' => date('Y-m-d H:i:s', strtotime($end_date)),
							),
						));
						
						if( $existingCalendarAppointment )
						{
							if( $existingCalendarAppointment->lead_id === null )
							{
								$existingCalendarAppointment->status = 4;
								$existingCalendarAppointment->save(false);
								
								$valid = true;
							}
						}
						else
						{
							$valid = true;
						}
						
						if( $valid )
						{
							if( $model->save(false))
							{
								if( $model->lead_id != null )
								{
									$existingEvents = CalendarAppointment::model()->findAll(array(
										'with' => array('calendar'),
										'condition' => '
											t.id != :event_id 
											AND t.calendar_id = :calendar_id 
											AND t.lead_id = :lead_id 
											AND calendar.customer_id = :customer_id 
											AND 
											( 
												(
													t.title IN ("APPOINTMENT SET", "INSERT APPOINTMENT") 
													AND t.start_date >= CURDATE()
												) 
												OR 
												(
													t.title IN("SCHEDULE CONFLICT", "LOCATION CONFLICT") 
												)
											)',
										'params' => array(
											':event_id' => $model->id,
											':calendar_id' => $calendar->id,
											':customer_id' => $calendar->customer_id,
											':lead_id' => $model->lead_id,
										),
									));
									
									if( $existingEvents )
									{
										foreach( $existingEvents as $existingEvent )
										{
											$existingEvent->status = 4;
											$existingEvent->save(false);
										}
									}
									
									
									$leadPhoneNumber = null;
						
									$leadHistory = new LeadHistory;
									
									$leadCallHistory = LeadCallHistory::model()->find(array(
										'condition' => 'lead_id = :lead_id AND agent_account_id = :agent_account_id AND calendar_appointment_id IS NULL',
										'params' => array(
											':lead_id' => $model->lead_id,
											':agent_account_id' => $authAccount->id,
										),
										'order' => 'date_created DESC',
									));
									
									if( $leadCallHistory )
									{
										$leadHistory->lead_call_history_id = $leadCallHistory->id;
										
										$leadCallHistory->agent_note = $_POST['CalendarAppointment']['agent_notes'];
										
										$leadPhoneNumber = $leadCallHistory->lead_phone_number;
										
										$leadCallHistory->calendar_appointment_id = $model->id;
										$leadCallHistory->agent_note = $model->agent_notes;
										$leadCallHistory->save(false);
									}
									
									$leadHistory->setAttributes(array(
										'lead_id' => $model->lead_id,
										'lead_phone_number' => $leadPhoneNumber,
										'disposition' => $model->title,
										'agent_account_id' => $authAccount->account_type_id == Account::TYPE_AGENT ? $authAccount->id : '',
										'calendar_appointment_id' => $model->id,
										'note' => $model->details,
										'type' => 3,
									));
									
									
									if($leadHistory->disposition != 'SCHEDULE CONFLICT' 
										&& $leadHistory->disposition != 'LOCATION CONFLICT - Pending'
										// && $leadHistory->disposition != 'APPOINTMENT SET'
									)
									$leadHistory->save(false);
									
									
									//if its an insert appointment insert a hopper entry for confirmation call
									if( $model->title == 'INSERT APPOINTMENT' )
									{
										$content = $model->title.' made by '.$authAccount->accountUser->getFullName();
										
										$insertAppLeadHistory = new LeadHistory;

										$insertAppLeadHistory->setAttributes(array(
											'lead_id' => $model->lead_id,
											'lead_phone_number' => $leadPhoneNumber,
											'disposition' => $model->title,
											'account_id' => $authAccount->id,
											'calendar_appointment_id' => $model->id,
											'note' => $model->details,
											'type' => 9,
											'content' => $content
										));
										
										$insertAppLeadHistory->save(false);
										
										$this->createConfirmationCall($model);
									}
								}
								
								$dbChanges++;
							}
						}
					
					}
				}
			}
			else
			{
				$result['message'] = 'Maximum appointment lenght is up to ' . $calendar->appointment_length . ' only.';
			}
			
			if( $dbChanges > 0 )
			{
				$result['status'] = 'success';
			}
			else
			{
				$result['message'] = 'The appointment time overlaps with other appointment.';
			}
		}
		
		
		if( isset($_POST['ajax']) )
		{
			$model = new CalendarAppointment;
			$model->calendar_id = $calendar->id;
			
			$existingEvent = 0;
			
			if( !empty($_POST['current_lead_id']) )
			{
				$lead = Lead::model()->findByPk($_POST['current_lead_id']);
				
				$model->lead_id = $lead->id;
				
				$disposition = SkillDisposition::model()->find(array(
					'condition' => 'skill_id = :skill_id AND is_schedule_conflict=1',
					'params' => array(
						':skill_id' => $lead->list->skill_id,
					),
				));
				
				if( $disposition )
				{
					$leadCallHistory = LeadCallHistory::model()->find(array(
						'condition' => 'lead_id = :lead_id AND agent_account_id = :agent_account_id',
						'params' => array(
							':lead_id' => $model->lead_id,
							':agent_account_id' => Yii::app()->user->account->id,
						),
						'order' => 'date_created DESC',
					));
					
					if( $leadCallHistory )
					{
						$model->details = $leadCallHistory->getReplacementCodeValues($disposition->notes_prefill);
					}
					else
					{
						$model->details = $disposition->notes_prefill;
					}
				}
				
				$existingEvent = CalendarAppointment::model()->count(array(
					'with' => array('calendar'),
					'condition' => '
						t.calendar_id = :calendar_id 
						AND t.lead_id = :lead_id 
						AND calendar.customer_id = :customer_id 
						AND 
						( 
							(
								t.title IN ("APPOINTMENT SET", "INSERT APPOINTMENT") 
								AND t.start_date >= CURDATE() 
							) 
							OR 
							(
								t.title IN("SCHEDULE CONFLICT", "LOCATION CONFLICT") 
								AND t.status=2 
							)
						)',
					'params' => array(
						':calendar_id' => $calendar->id,
						':customer_id' => $calendar->customer_id,
						':lead_id' => $model->lead_id,
					),
				));
			}
			
			$eventsTodayCount = CalendarAppointment::model()->count(array(
				'condition' => 'calendar_id = :calendar_id AND DATE(start_date) = :current_date AND title = :title AND status !=4',
				'params' => array(
					':title' => 'APPOINTMENT SET',
					':calendar_id' => $calendar->id,
					':current_date' => date('Y-m-d', strtotime($_POST['current_date'])),
				),
			));
			
			$blackoutSlots = CalendarAppointment::model()->count(array(
				'condition' => 'title = "BLACKOUT DAYS" AND calendar_id = :calendar_id AND DATE(start_date) = :start_date AND status !=4',
				'params' => array(
					':calendar_id' => $calendar->id,
					':start_date' => date('Y-m-d', strtotime($_POST['current_date'])),
				),
			));
			
			$existingHolidaySlot = CalendarHoliday::model()->count(array(
				'condition' => 'calendar_id = :calendar_id AND YEAR(date) = :start_date_year AND MONTH(date) = :start_date_month AND DAY(date) = :start_date_day',
				'params' => array(
					':calendar_id' => $calendar->id,
					':start_date_year' => date('Y', strtotime($_POST['current_date'])),
					':start_date_month' => date('m', strtotime($_POST['current_date'])),
					':start_date_day' => date('d', strtotime($_POST['current_date'])),
				),
			));
			
			$currentDate = date('Y-m-d', strtotime($_POST['current_date']));
			$minDaysOut = date('Y-m-d', strtotime('+'.$calendar->minimum_days_appointment_set.' days'));
			
			if( $eventsTodayCount < $calendar->maximum_appointments_per_day && $blackoutSlots == 0 && $existingHolidaySlot == 0 )
			{
				$valid = false;
				
				if($_POST['viewer'] == 'agent')
				{
					if(  $currentDate >= $minDaysOut )
					{
						$valid = true;
					}
				}
				else
				{
					$valid = true;
				}
				
				if( $valid )
				{
					$result['status'] = 'success';
						
					$html = $this->renderPartial('ajax_create', array(
						'model' => $model,
						'calendar' => $calendar,
						'viewer' => $_POST['viewer'],
						'currentDateSelected' => date('l F d, Y', strtotime($_POST['current_date'])),
						'existingEvent' => $existingEvent,
					), true);
				}
			}
		}
		
		$result['html'] = $html;
		
		echo json_encode($result);
	}
	
	
	public function actionUpdate()
	{
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => '',
			'event' => array(),
			'events' => array(),
			'removeEvents' => array(),
		);
		

		$model = CalendarAppointment::model()->findByPk($_POST['appointment_id']);
		$calendar = $model->calendar;
		
		$blackoutSlots = CalendarAppointment::model()->count(array(
			'condition' => 'title = "BLACKOUT DAYS" AND calendar_id = :calendar_id AND DATE(start_date) = :start_date',
			'params' => array(
				':calendar_id' => $calendar->id,
				':start_date' => date('Y-m-d', strtotime($_POST['start_date'])),
			),
		));
		
		$existingHolidaySlot = CalendarHoliday::model()->count(array(
			'condition' => 'calendar_id = :calendar_id AND MONTH(date) = :start_date_month AND DAY(date) = :start_date_day',
			'params' => array(
				':calendar_id' => $calendar->id,
				':start_date_month' => date('m', strtotime($_POST['start_date'])),
				':start_date_day' => date('d', strtotime($_POST['start_date'])),
			),
		));
	
		
		if( $model->title == 'BLACKOUT DAYS' || ($blackoutSlots == 0 && $existingHolidaySlot == 0) )
		{	
			$currentStartDate = $model->start_date;
			$currentEndDate = $model->end_date;

			$start_date = date('Y-m-d H:i:s', strtotime($_POST['start_date']));
			$start_date_year = date('Y', strtotime($start_date));
			$start_date_month = date('m', strtotime($start_date));
			$start_date_day = date('d', strtotime($start_date));
			
			$start_date_time = date('H:i:s', strtotime($model->start_date));
			
			// if($_POST['type'] == 'eventResize')
			// {
				$end_date = date('Y-m-d H:i:s', strtotime('-1 day', strtotime($_POST['end_date'])));
			// }
			// else
			// {
				// $end_date = date('Y-m-d H:i:s', strtotime($_POST['end_date']));
			// }
			
			$end_date_year = date('Y', strtotime($end_date));
			$end_date_month = date('m', strtotime($end_date));
			$end_date_day = date('d', strtotime($end_date));
			
			$end_date_time = date('H:i:s', strtotime($model->end_date));
		
			$model->setAttributes(array(
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
			));
			
			$dates = array();
					
			$startDate = strtotime($model->start_date);
			$endDate = strtotime($model->end_date);
			
			while( $startDate <= $endDate )
			{
				$dates[] = date('Y-m-d', $startDate);	
				$startDate = strtotime('+1 day', $startDate);
			}
			
			$existingRecords = CalendarAppointment::model()->findAll(array(
				'condition' => 'calendar_id = :calendar_id AND DATE(start_date) IN ("'.implode('", "', $dates).'") AND status !=4 AND title NOT IN ("BLACKOUT DAYS", "AVAILABLE")',
				'params' => array(
					':calendar_id' => $calendar->id,
				),
			));
			
			if( $existingRecords && $dates && $model->title == 'BLACKOUT DAYS' && !isset($_POST['submitBlackoutForm']) )
			{
				$html = $this->renderPartial('ajax_blackout_form', array(
					'calendar_id' => $calendar->id,
					'appointment_id' => $_POST['appointment_id'],
					'start_date' => $_POST['start_date'],
					'end_date' => $_POST['end_date'],
					'existingRecords' => $existingRecords,
					'formAction' => 'update',
				), true);
				
				$result['status'] = 'success';
				$result['html'] = $html;
				
				echo json_encode($result);
				Yii::app()->end();
			}
			
			if($model->save(false))
			{
				$availableSlots = CalendarAppointment::model()->findAll(array(
					'condition' => 'calendar_id = :calendar_id AND DATE(start_date) IN ("'.implode('", "', $dates).'") AND status !=4 AND title="AVAILABLE"',
					'params' => array(
						':calendar_id' => $model->calendar_id,
					),
				));
				
				if( $availableSlots )
				{
					foreach( $availableSlots as $availableSlot )
					{
						$availableSlot->status = 4;
						$availableSlot->save(false);
					}
				}
				
				if( $model->title == 'BLACKOUT DAYS' )
				{
					if( !empty($_POST['existingRecordOptions']) )
					{
						foreach( $_POST['existingRecordOptions'] as $appointmentId => $optionValue )
						{
							$appointmentModel = CalendarAppointment::model()->findByPk($appointmentId);
							
							if( $appointmentModel )
							{
								if( $optionValue == 2 )
								{
									$appointmentModel->title = 'CANCEL APPOINTMENT';
									$appointmentModel->status = $appointmentModel::STATUS_DELETED;
									
									if( $appointmentModel->save(false) )
									{
										$existingLeadHopperEntry = LeadHopper::model()->find(array(
											'condition' => 'lead_id = :lead_id',
											'params' => array(
												':lead_id' => $appointmentModel->lead_id,
											),
										));
										
										if( $existingLeadHopperEntry )
										{
											$existingLeadHopperEntry->delete();
										}
									}
								}
								
								if( $optionValue == 3 )
								{
									$appointmentModel->title = 'RESCHEDULE APPOINTMENT';
									$appointmentModel->status = $appointmentModel::STATUS_DELETED;
									$appointmentModel->save(false);
									
									$this->createConfirmationCall($appointmentModel);
								}
							}
						}
					}
					
					$existingCustomerHistory = CustomerHistory::model()->find(array(
						'condition' => 'model_id = :model_id',
						'params' => array(
							':model_id' => $model->id,
						),
					));
					
					if( $existingCustomerHistory )
					{
						if( date('m/d/Y', strtotime($model->start_date)) == date('m/d/Y', strtotime($model->end_date)) )
						{
							$content = 'Blackout Days | ' . date('m/d/Y', strtotime($model->start_date));
						}
						else
						{
							$content = 'Blackout Days | ' . date('m/d/Y', strtotime($model->start_date)).' to '.date('m/d/Y', strtotime($model->end_date));
						}
						
						$existingCustomerHistory->setAttributes(array(
							'user_account_id' => Yii::app()->user->account->id,
							'content' => $content,
						));

						$existingCustomerHistory->save(false);
					}
				}
				
				$result['status'] = 'success';
				$result['message'] = 'Appointment slot has been updated.';
				
				$result['event'] = array(
					'id' => $model->id,
					'title' => $model->title,
					'details' => '',
					'start_date' => date('c', strtotime($model->start_date)),
					'end_date' => date('c', strtotime($model->end_date)),
					'color' => $model->getEventColor(),
					'allDay' => $model->title == 'BLACKOUT DAYS' ? true : false,
					'is_custom' => $model->is_custom,
				);
			}
			else
			{
				$result['message'] = 'Database error.';
			}
		}

		echo json_encode($result);
		Yii::app()->end();
	}
	
	
	public function actionActionForm()
	{
		$authAccount = Yii::app()->user->account;
		
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => $html,
		);
		
		
		if( isset($_POST['CalendarAppointment']) )
		{
			$model = CalendarAppointment::model()->findByPk($_POST['CalendarAppointment']['id']);	
			
			if( $model )
			{ 
				$currentTitle = $model->title;
				
				$model->attributes = $_POST['CalendarAppointment'];
				
				if( !empty($_POST['current_lead_id']) )
				{
					$model->lead_id = $_POST['current_lead_id'];
				}
				
				if( $model->title == 'CHANGE APPOINTMENT' || $model->title == 'UPDATE CALENDAR ONLY' )
				{
					$model->title = $currentTitle;
					
					if( $model->title == 'CHANGE APPOINTMENT' )
					{
						$model->previous_start_date = date('Y-m-d H:i:s', strtotime($model->start_date));
					}
				}
				
				$start_date_time = date('H:i:s', strtotime($model->start_date_time));
				
				if( isset($_POST['suggest']) )
				{
					$start_date = date('Y-m-d', strtotime($_POST['CalendarAppointment']['alt_date'])).' '.$start_date_time;
				}
				else
				{
					if( isset($_POST['CalendarAppointment']['alt_date']) )
					{
						$start_date = date('Y-m-d', strtotime($_POST['CalendarAppointment']['alt_date'])).' '.$start_date_time;
					}
					else
					{
						$start_date = date('Y-m-d', strtotime($model->start_date)).' '.$start_date_time;
					}
				}
				
				$start_date_year = date('Y', strtotime($start_date));
				$start_date_month = date('m', strtotime($start_date));
				$start_date_day = date('d', strtotime($start_date));
				
				$end_date_time = date('H:i:s', strtotime($model->end_date_time));
				
				if( isset($_POST['suggest']) || (isset($_POST['CalendarAppointment']['title']) && $_POST['CalendarAppointment']['title'] == 'UPDATE CALENDAR ONLY') )
				{
					$end_date = date('Y-m-d', strtotime($_POST['CalendarAppointment']['alt_date'])).' '.$end_date_time;
				}
				else
				{
					$end_date = date('Y-m-d', strtotime($model->end_date)).' '.$end_date_time;
				}
				
				$end_date = date('Y-m-d H:i:s', strtotime($end_date));
				$end_date_year = date('Y', strtotime($end_date));
				$end_date_month = date('m', strtotime($end_date));
				$end_date_day = date('d', strtotime($end_date));
				
				
				$model->setAttributes(array(
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
				));
				
				$dispositionTxt = $model->title; 
				
				if( isset($_POST['approved']) || isset($_POST['declined']) || isset($_POST['suggest']) )
				{
					if( isset($_POST['approved']) )
					{
						$model->status = $model::STATUS_APPROVED;
						$dispositionTxt .= ' - Approved';
					}
					elseif( isset($_POST['declined']) )
					{
						$model->status = $model::STATUS_DECLINED;
						$dispositionTxt .= ' - Denied';
					}
					else
					{
						$model->status = $model::STATUS_SUGGEST;
						$dispositionTxt .= ' - Alt Suggested';
					}
				}
				else
				{
					if( $model->title == 'AVAILABLE' )
					{
						$model->status = $model::STATUS_APPROVED;
					}
					else
					{
						$model->status = $model::STATUS_PENDING;
						$dispositionTxt .= ' - Pending';
					}
				}		

				if( in_array($model->title, array('CANCEL APPOINTMENT', 'RESCHEDULE APPOINTMENT')) )
				{
					$model->status = $model::STATUS_DELETED;
					
					if( $model->title == 'CANCEL APPOINTMENT' )
					{
						$existingLeadHopperEntry = LeadHopper::model()->find(array(
							'condition' => 'lead_id = :lead_id',
							'params' => array(
								':lead_id' => $model->lead_id,
							),
						));
						
						if( $existingLeadHopperEntry )
						{
							$existingLeadHopperEntry->delete();
						}
					}
				}
				
				if( $model->save() )
				{
					if( $model->lead_id != null )
					{
						$calendar = $model->calendar;
						
						$existingEvents = CalendarAppointment::model()->findAll(array(
							'with' => array('calendar'),
							'condition' => '
								t.id != :event_id AND t.calendar_id = :calendar_id 
								AND t.lead_id = :lead_id
								AND calendar.customer_id = :customer_id
								AND
								( 
									(
										t.title IN ("APPOINTMENT SET", "INSERT APPOINTMENT") 
										AND t.start_date >= CURDATE()
									) 
									OR 
									( 
										t.title IN("SCHEDULE CONFLICT", "LOCATION CONFLICT") 
										AND t.start_date >= CURDATE()
									)
								)
							',
							'params' => array(
								':event_id' => $model->id,
								':calendar_id' => $calendar->id,
								':customer_id' => $calendar->customer_id,
								':lead_id' => $model->lead_id,
							),
						));
						
						if( $existingEvents )
						{
							foreach( $existingEvents as $existingEvent )
							{
								$existingEvent->status = 4;
								$existingEvent->save(false);
							}
						}

						
						$leadPhoneNumber = null;
				

						$leadHistory = new LeadHistory;
						
						$leadCallHistory = LeadCallHistory::model()->find(array(
							// 'condition' => 'lead_id = :lead_id AND calendar_appointment_id = :calendar_appointment_id',
							'condition' => 'lead_id = :lead_id',
							'params' => array(
								':lead_id' => $model->lead_id,
								// ':calendar_appointment_id' => $model->id,
							),
							'order' => 'date_created DESC',
						));
						
						if( $leadCallHistory )
						{
							$leadHistory->lead_call_history_id = $leadCallHistory->id;
							
							$leadPhoneNumber = $leadCallHistory->lead_phone_number;
							
							$leadCallHistory->calendar_appointment_id = $model->id;
							$leadCallHistory->save(false);
						}
						
						if( isset($_POST['CalendarAppointment']['agent_notes']) || isset($_POST['CalendarAppointment']['customer_notes']) )
						{
							if( isset($_POST['CalendarAppointment']['agent_notes']) )
							{
								$leadHistory->note = $_POST['CalendarAppointment']['agent_notes'];
							}
							
							if( isset($_POST['CalendarAppointment']['customer_notes']) )
							{
								$leadHistory->note = $_POST['CalendarAppointment']['customer_notes'];
							}
						}
						else
						{
							$leadHistory->note = $model->details;
						}
						
						$leadHistory->setAttributes(array(
							'lead_id' => $model->lead_id,
							'lead_phone_number' => $leadPhoneNumber,
							'disposition' => $dispositionTxt,
							'agent_account_id' => $leadCallHistory->agent_account_id,
							'calendar_appointment_id' => $model->id,
							'type' => 3,
						));
						
						if( isset($_POST['approved']) || isset($_POST['declined']) || isset($_POST['suggest']) || in_array($model->title, array('CANCEL APPOINTMENT', 'RESCHEDULE APPOINTMENT', 'CHANGE APPOINTMENT', 'UPDATE CALENDAR ONLY', 'NO SHOW RESCHEDULE')) )
						{
							$leadHistory->account_id = $authAccount->id;
							
							if( isset($_POST['suggest']) && $model->title == 'SCHEDULE CONFLICT' )
							{
								$leadHistory->note = 'New suggested Appointment: ' . date('m/d/Y g:ia', strtotime($model->start_date));
							}
							
						}
						
						$leadHistory->save(false);
						
						if( isset($_POST['approved']) || isset($_POST['declined']) || isset($_POST['suggest']) )
						{
							$hopperEntry = LeadHopper::model()->find(array(
								'condition' => 'lead_id = :lead_id AND type = :type',
								'params' => array(
									':lead_id' => $model->lead_id,
									':type' => LeadHopper::TYPE_CONFLICT,
								),
							));
							
							if( $hopperEntry )
							{ 
								if( isset($_POST['declined']) )
								{
									$hopperEntry->delete();
								}
								else
								{
									$hopperEntry->status = LeadHopper::STATUS_READY;
									$hopperEntry->save(false);
								}
							}
						}
						
						
						//if its an appointment set insert a hopper entry for confirmation call
						if( $model->title == 'INSERT APPOINTMENT' || $model->title == 'NO SHOW RESCHEDULE' || $model->title == 'CHANGE APPOINTMENT' || $model->title == 'RESCHEDULE APPOINTMENT' )
						{
							$this->createConfirmationCall($model);
						}
					}
					
					$result['event'] = array(
						'title' => $model->getEventTitle(),
						'details' => $model->details,
						'start_date' => date('c', strtotime($model->start_date)),
						'end_date' => date('c', strtotime($model->end_date)),
						'color' => $model->getEventColor(),
					);
					
					$result['status'] = 'success';
					$result['message'] = 'Appointment slot has been updated.';
					$result['customer_id'] = $model->lead->list->customer_id;
				}
				else
				{
					$result['status'] = 'error';
					$result['message'] = 'Database error.';
					$result['error'] = $model->getErrors();
				}
				
			}
		}
			
			
		if( isset($_POST['ajax']) && isset($_POST['id']) )
		{
			$model = CalendarAppointment::model()->findByPk($_POST['id']);	
			
			$calendar = $model->calendar;
			$existingEvent = 0;
			
			if( !empty($_POST['current_lead_id']) )
			{
				$model->lead_id = $_POST['current_lead_id'];
				
				if( isset($_POST['title']) && isset($_POST['getDisposition']) )
				{
					$notesPrefill = '';
					
					
					if( $_POST['title'] == 'APPOINTMENT SET' )
					{
						$disposition = SkillDisposition::model()->find(array(
							'condition' => 'skill_id = :skill_id AND is_appointment_set=1',
							'params' => array(
								':skill_id' => $model->lead->list->skill_id,
							),
						));
						
						if( $disposition )
						{
							$leadCallHistory = LeadCallHistory::model()->find(array(
								'condition' => 'lead_id = :lead_id AND agent_account_id = :agent_account_id',
								'params' => array(
									':lead_id' => $model->lead_id,
									':agent_account_id' => Yii::app()->user->account->id,
								),
								'order' => 'date_created DESC',
							));
							
							if( $leadCallHistory )
							{
								$notesPrefill = $leadCallHistory->getReplacementCodeValues($disposition->notes_prefill);
							}
							else
							{
								$notesPrefill = $disposition->notes_prefill;
							}
						}
					}
					
					if( $_POST['title'] == 'LOCATION CONFLICT' )
					{
						$disposition = SkillDisposition::model()->find(array(
							'condition' => 'skill_id = :skill_id AND is_location_conflict=1',
							'params' => array(
								':skill_id' => $model->lead->list->skill_id,
							),
						));
						
						if( $disposition )
						{
							$leadCallHistory = LeadCallHistory::model()->find(array(
								'condition' => 'lead_id = :lead_id AND agent_account_id = :agent_account_id',
								'params' => array(
									':lead_id' => $model->lead_id,
									':agent_account_id' => Yii::app()->user->account->id,
								),
								'order' => 'date_created DESC',
							));
							
							if( $leadCallHistory )
							{
								$notesPrefill = $leadCallHistory->getReplacementCodeValues($disposition->notes_prefill);
							}
							else
							{
								$notesPrefill = $disposition->notes_prefill;
							}
						}
					}
					

					echo json_encode(array(
						'notesPrefill' => $notesPrefill,
					));
					
					Yii::app()->end();
				}
				
				$existingEvent = CalendarAppointment::model()->count(array(
					'with' => array('calendar'),
					'condition' => '
						t.calendar_id = :calendar_id 
						AND t.lead_id = :lead_id
						AND calendar.customer_id = :customer_id
						AND 
						( 
							(
								t.title IN ("APPOINTMENT SET", "INSERT APPOINTMENT") 
								AND t.start_date >= CURDATE() 
							)
							OR 
							(
								t.title IN("SCHEDULE CONFLICT", "LOCATION CONFLICT") 
								AND t.status=2 
							)
						)',
					'params' => array(
						':calendar_id' => $calendar->id,
						':customer_id' => $calendar->customer_id,
						':lead_id' => $model->lead_id,
					),
				));
			}
			
			$html = $this->renderPartial('ajax_action_form', array(
				'model' => $model,
				'calendar' => $calendar,
				'viewer' => $_POST['viewer'],
				'currentDateSelected' => isset($_POST['current_date']) ? date('l F d, Y', strtotime($_POST['current_date'])) : null,
				'existingEvent' => $existingEvent,
			), true);
		}
		
		$result['status'] = 'success';
		$result['html'] = $html;
		
		echo json_encode($result);
	}
	
	public function actionActionFormAvailableSlot()
	{
		$authAccount = Yii::app()->user->account;
		
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => $html,
		);
		
		
		if( isset($_POST['CalendarAppointment']) )
		{
			if( !empty($_POST['CalendarAppointment']['title']) )
			{
				$model = new CalendarAppointment;

				$model->attributes = $_POST['CalendarAppointment'];
				
				if( !empty($_POST['current_lead_id']) )
				{
					$model->lead_id = $_POST['current_lead_id'];
				}
				
				$start_date_time = date('H:i:s', strtotime($model->start_date_time));
				$start_date = date('Y-m-d', strtotime($model->start_date)).' '.$start_date_time;
				
				$start_date_year = date('Y', strtotime($start_date));
				$start_date_month = date('m', strtotime($start_date));
				$start_date_day = date('d', strtotime($start_date));
				
				$end_date_time = date('H:i:s', strtotime($model->end_date_time));
				$end_date = date('Y-m-d', strtotime($model->end_date)).' '.$end_date_time;
				
				$end_date_year = date('Y', strtotime($end_date));
				$end_date_month = date('m', strtotime($end_date));
				$end_date_day = date('d', strtotime($end_date));
				
				
				$model->setAttributes(array(
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
				));
				
				$dispositionTxt = $model->title; 	
				
				if( $model->title == 'AVAILABLE' )
				{
					$model->status = $model::STATUS_APPROVED;
				}
				else
				{
					$model->status = $model::STATUS_PENDING;
					$dispositionTxt .= ' - Pending';
				}
				
				if( $model->save() )
				{
					if( $model->lead_id != null )
					{
						$calendar = $model->calendar;
						
						$existingEvents = CalendarAppointment::model()->findAll(array(
							'with' => array('calendar'),
							'condition' => '
								t.id != :event_id AND t.calendar_id = :calendar_id 
								AND t.lead_id = :lead_id
								AND calendar.customer_id = :customer_id
								AND
								( 
									(
										t.title IN ("APPOINTMENT SET", "INSERT APPOINTMENT") 
										AND t.start_date >= CURDATE()
									) 
									OR 
									( 
										t.title IN("SCHEDULE CONFLICT", "LOCATION CONFLICT") 
										AND t.start_date >= CURDATE()
									)
								)
							',
							'params' => array(
								':event_id' => $model->id,
								':calendar_id' => $calendar->id,
								':customer_id' => $calendar->customer_id,
								':lead_id' => $model->lead_id,
							),
						));
						
						if( $existingEvents )
						{
							foreach( $existingEvents as $existingEvent )
							{
								$existingEvent->status = 4;
								$existingEvent->save(false);
							}
						}

						
						$leadPhoneNumber = null;

						$leadHistory = new LeadHistory;
						
						$leadCallHistory = LeadCallHistory::model()->find(array(
							// 'condition' => 'lead_id = :lead_id AND calendar_appointment_id = :calendar_appointment_id',
							'condition' => 'lead_id = :lead_id',
							'params' => array(
								':lead_id' => $model->lead_id,
								// ':calendar_appointment_id' => $model->id,
							),
							'order' => 'date_created DESC',
						));
						
						if( $leadCallHistory )
						{
							$leadHistory->lead_call_history_id = $leadCallHistory->id;
							
							$leadCallHistory->agent_note = $_POST['CalendarAppointment']['agent_notes'];
							
							$leadPhoneNumber = $leadCallHistory->lead_phone_number;
							
							$leadCallHistory->calendar_appointment_id = $model->id;
							$leadCallHistory->save(false);
						}
						
						$leadHistory->note = $model->details;
						
						if( isset($_POST['CalendarAppointment']['agent_notes']) || isset($_POST['CalendarAppointment']['customer_notes']) )
						{
							if( isset($_POST['CalendarAppointment']['agent_notes']) )
							{
								$leadHistory->note .= '<br /><br />' . $_POST['CalendarAppointment']['agent_notes'];
							}
							
							if( isset($_POST['CalendarAppointment']['customer_notes']) )
							{
								$leadHistory->note .= '<br /><br />' . $_POST['CalendarAppointment']['customer_notes'];
							}
						}
						
						$leadHistory->setAttributes(array(
							'lead_id' => $model->lead_id,
							'lead_phone_number' => $leadPhoneNumber,
							'disposition' => $dispositionTxt,
							'agent_account_id' => isset($leadCallHistory->agent_account_id) ? $leadCallHistory->agent_account_id : $authAccount->id,
							'calendar_appointment_id' => $model->id,
							'type' => 3,
						));
						
						if($leadHistory->disposition != 'LOCATION CONFLICT - Pending'
						)
						$leadHistory->save(false);
						
						//if its an appointment set insert a hopper entry for confirmation call
						if( $model->title == 'INSERT APPOINTMENT' )
						{
							$content = $model->title.' made by '.$authAccount->accountUser->getFullName();
										
							$insertAppLeadHistory = new LeadHistory;

							$insertAppLeadHistory->setAttributes(array(
								'lead_id' => $model->lead_id,
								'lead_phone_number' => $leadPhoneNumber,
								'disposition' => $model->title,
								'account_id' => $authAccount->id,
								'calendar_appointment_id' => $model->id,
								'note' => $model->details,
								'type' => 9,
								'content' => $content
							));
							
							$insertAppLeadHistory->save(false);
							
							$this->createConfirmationCall($model);
						}
					}
					
					$result['event'] = array(
						'title' => $model->getEventTitle(),
						'details' => $model->details,
						'start_date' => date('c', strtotime($model->start_date)),
						'end_date' => date('c', strtotime($model->end_date)),
						'color' => $model->getEventColor(),
					);
					
					$result['status'] = 'success';
					$result['message'] = 'Appointment slot has been updated.';
					$result['customer_id'] = $model->lead->list->customer_id;
				}
				else
				{
					$result['message'] = 'Database error.';
				}
			}
			else
			{
				$result['message'] = 'Action is required';
			}
		}
			
			
		if( isset($_POST['ajax']) && isset($_POST['id']) )
		{		
			$calendar = Calendar::model()->findByPk($_POST['calendar_id']);

			// $calendarEventsArray = $this->applyCustomSchedule($calendar, $_POST['current_date']);
			
			// $calendarEventKey = array_search($_POST['id'], array_column($calendarEventsArray, 'id'));
			
			// $calendarEvent = $calendarEventsArray[$calendarEventKey];

			// $calendarEventsArray = array();
			
			// foreach( $this->applyCustomSchedule($calendar, $_POST['current_date']) as $availableSlot )
			// {
				// $calendarEventsArray[$availableSlot['id']] = $availableSlot;
			// }
	
			// $calendarEvent = $calendarEventsArray[$_POST['id']];
			
			// $start_date_time = date('H:i:s', strtotime($calendarEvent['start_date']));
			// $start_date = date('Y-m-d', strtotime($calendarEvent['start_date'])).' '.$start_date_time;
			
			// $start_date_year = date('Y', strtotime($start_date));
			// $start_date_month = date('m', strtotime($start_date));
			// $start_date_day = date('d', strtotime($start_date));
			
			// $end_date_time = date('H:i:s', strtotime($calendarEvent['end_date']));
			// $end_date = date('Y-m-d', strtotime($calendarEvent['end_date'])).' '.$end_date_time;
			
			// $end_date_year = date('Y', strtotime($end_date));
			// $end_date_month = date('m', strtotime($end_date));
			// $end_date_day = date('d', strtotime($end_date));
			
			
			$start_date_time = date('H:i:s', strtotime($_POST['start_date']));
			$start_date = date('Y-m-d', strtotime($_POST['start_date'])).' '.$start_date_time;
			
			$start_date_year = date('Y', strtotime($start_date));
			$start_date_month = date('m', strtotime($start_date));
			$start_date_day = date('d', strtotime($start_date));
			
			$end_date_time = date('H:i:s', strtotime($_POST['end_date']));
			$end_date = date('Y-m-d', strtotime($_POST['end_date'])).' '.$end_date_time;
			
			$end_date_year = date('Y', strtotime($end_date));
			$end_date_month = date('m', strtotime($end_date));
			$end_date_day = date('d', strtotime($end_date));
			
			$model = new CalendarAppointment;
			
			$model->setAttributes(array(
				'calendar_id' => $calendar->id,
				'account_id' => $authAccount->id,
				'title' => 'AVAILABLE',
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
			));
			
			$existingEvent = 0;
			
			if( !empty($_POST['current_lead_id']) )
			{
				$model->lead_id = $_POST['current_lead_id'];
				
				if( isset($_POST['title']) && isset($_POST['getDisposition']) )
				{
					$notesPrefill = '';
					
					
					if( $_POST['title'] == 'APPOINTMENT SET' )
					{
						$disposition = SkillDisposition::model()->find(array(
							'condition' => 'skill_id = :skill_id AND is_appointment_set=1',
							'params' => array(
								':skill_id' => $model->lead->list->skill_id,
							),
						));
						
						if( $disposition )
						{
							$leadCallHistory = LeadCallHistory::model()->find(array(
								'condition' => 'lead_id = :lead_id AND agent_account_id = :agent_account_id',
								'params' => array(
									':lead_id' => $model->lead_id,
									':agent_account_id' => Yii::app()->user->account->id,
								),
								'order' => 'date_created DESC',
							));
							
							if( $leadCallHistory )
							{
								$notesPrefill = $leadCallHistory->getReplacementCodeValues($disposition->notes_prefill);
							}
							else
							{
								$notesPrefill = $disposition->notes_prefill;
							}
						}
					}
					
					if( $_POST['title'] == 'LOCATION CONFLICT' )
					{
						$disposition = SkillDisposition::model()->find(array(
							'condition' => 'skill_id = :skill_id AND is_location_conflict=1',
							'params' => array(
								':skill_id' => $model->lead->list->skill_id,
							),
						));
						
						if( $disposition )
						{
							$leadCallHistory = LeadCallHistory::model()->find(array(
								'condition' => 'lead_id = :lead_id AND agent_account_id = :agent_account_id',
								'params' => array(
									':lead_id' => $model->lead_id,
									':agent_account_id' => Yii::app()->user->account->id,
								),
								'order' => 'date_created DESC',
							));
							
							if( $leadCallHistory )
							{
								$notesPrefill = $leadCallHistory->getReplacementCodeValues($disposition->notes_prefill);
							}
							else
							{
								$notesPrefill = $disposition->notes_prefill;
							}
						}
					}
					

					echo json_encode(array(
						'notesPrefill' => $notesPrefill,
					));
					
					Yii::app()->end();
				}
				
				$existingEvent = CalendarAppointment::model()->count(array(
					'with' => array('calendar'),
					'condition' => '
						t.calendar_id = :calendar_id 
						AND t.lead_id = :lead_id
						AND calendar.customer_id = :customer_id
						AND 
						( 
							(
								t.title IN ("APPOINTMENT SET", "INSERT APPOINTMENT") 
								AND t.start_date >= CURDATE() 
							)
							OR 
							(
								t.title IN("SCHEDULE CONFLICT", "LOCATION CONFLICT") 
								AND t.status=2 
							)
						)',
					'params' => array(
						':calendar_id' => $calendar->id,
						':customer_id' => $calendar->customer_id,
						':lead_id' => $model->lead_id,
					),
				));
			}
			
			$html = $this->renderPartial('ajax_action_form', array(
				'model' => $model,
				'calendar' => $calendar,
				'viewer' => $_POST['viewer'],
				'currentDateSelected' => isset($_POST['current_date']) ? date('l F d, Y', strtotime($_POST['current_date'])) : null,
				'existingEvent' => $existingEvent,
			), true);
			
			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}
	
	public function actionSettings()
	{
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => $html,
		);
		
		$authAccount = Yii::app()->user->account;
		
		$currentValues = array();
		
		if( isset($_POST['id']) )
		{
			$model = Calendar::model()->findByPk($_POST['id']);
			
			$calendarStaffAssignment = CalendarStaffAssignment::model()->find(array(
				'condition' => 'calendar_id = :calendar_id',
				'params' => array(
					'calendar_id' => $model->id,
				),
			));
			
			$html = $this->renderPartial('ajax_settings', array('model' =>$model, 'calendarStaffAssignment'=>$calendarStaffAssignment), true);
			
			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		if( isset($_POST['Calendar']) )
		{
			$model = Calendar::model()->findByPk($_POST['Calendar']['id']);
			
			$calendarStaffAssignment = CalendarStaffAssignment::model()->find(array(
				'condition' => 'calendar_id = :calendar_id',
				'params' => array(
					'calendar_id' => $model->id,
				),
			));
			
			$currentStaffId = $calendarStaffAssignment->staff_id;
			
			$currentValues = $model->attributes;
			
			$model->attributes = $_POST['Calendar'];
			
			$calendarStaffAssignment->attributes = $_POST['CalendarStaffAssignment'];
			
			$difference = array_diff($model->attributes, $currentValues);
			
			if( $model->save(false) ) 
			{
				if( $calendarStaffAssignment->save(false) )
				{
					if( $currentStaffId != $calendarStaffAssignment->staff_id )
					{
						$oldStaffName = CustomerOfficeStaff::model()->findByPk($currentStaffId)->staff_name;
						$newStaffName = CustomerOfficeStaff::model()->findByPk($calendarStaffAssignment->staff_id)->staff_name;
														
						$history = new CustomerHistory;
						$history->scenario = 'add2Hours';
						
						$history->setAttributes(array(
							'model_id' => $model->id, 
							'customer_id' => $model->customer_id,
							'user_account_id' => $authAccount->id,
							'page_name' => 'Calendar',
							'content' =>  'Staff changed from '.$oldStaffName.' to '.$newStaffName,
							'old_data' => json_encode(array('staff_id'=>$currentStaffId)),
							'new_data' => json_encode(array('staff_id'=>$calendarStaffAssignment->staff_id)),
							'type' => $history::TYPE_UPDATED,
						));

						$history->save(false);
					}
				}
				
				
				if( $difference )
				{
					//check model has difference
					if( $difference )
					{
						$updateFields = '';
					
						foreach( $difference as $attributeName => $value)
						{
							if( in_array($attributeName, array('location_office', 'location_phone', 'location_home', 'location_skype')) )
							{
								$oldLocations = array();
								$newLocations = array();
								
								if( $attributeName == 'location_office' && $currentValues['location_office'] == 1 )
								{
									$oldLocations[] = 'Office';
								}
								
								if( $attributeName == 'location_phone' && $currentValues['location_phone'] == 1 )
								{
									$oldLocations[] = 'Phone';
								}
								
								if( $attributeName == 'location_home' && $currentValues['location_home'] == 1 )
								{
									$oldLocations[] = 'Home';
								}
								
								if( $attributeName == 'location_skype' && $currentValues['location_skype'] == 1 )
								{
									$oldLocations[] = 'Skype';
								}
								
								
								if( $attributeName == 'location_office' && $model->location_office == 1 )
								{
									$newLocations[] = 'Office';
								}
								
								if( $attributeName == 'location_phone' && $model->location_phone == 1 )
								{
									$newLocations[] = 'Phone';
								}
								
								if( $attributeName == 'location_home' && $model->location_home == 1 )
								{
									$newLocations[] = 'Home';
								}
								
								if( $attributeName == 'location_skype' && $model->location_skype == 1 )
								{
									$newLocations[] = 'Skype';
								}
								
								$updateFields .= 'Location changed from '.implode(', ', $oldLocations).' to '.implode(', ', $newLocations).', ';
							}
							else
							{
								$updateFields .= $model->getAttributeLabel($attributeName) .' changed from '.$currentValues[$attributeName].' to '.$value.', ';
							}
						}
						
						$updateFields = rtrim($updateFields, ', ');
						
						$history = new CustomerHistory;
						$history->scenario = 'add2Hours';
						
						$history->setAttributes(array(
							'model_id' => $model->id, 
							'customer_id' => $model->customer_id,
							'user_account_id' => $authAccount->id,
							'page_name' => 'Calendar',
							'content' => $updateFields,
							'old_data' => json_encode($currentValues),
							'new_data' => json_encode($model->attributes),
							'type' => $history::TYPE_UPDATED,
						));

						$history->save(false);
					}
					
				}
				
				// $calendarSlots = CalendarAppointment::model()->findAll(array(
					// 'condition' => 'title = :title AND calendar_id = :calendar_id AND YEAR(NOW()) = :currentYear AND MONTH(NOW()) = :currentMonth',
					// 'params' => array(
						// ':title' => 'AVAILABLE',
						// ':calendar_id' => $model->id,
						// ':currentYear' => date('Y'),
						// ':currentMonth' => date('m'),
					// ),
				// ));

				
				// if($calendarSlots )
				// {
					// foreach($calendarSlots as $calendarSlot)
					// {
						// $calendarSlot->delete();
					// }
				// }
				
				if( $model->use_default_schedule == 1 )
				{
					$this->applyDefaultSchedule($model, date('Y-m-d'));
				}
				else
				{
					$this->applyCustomSchedule($model, date('Y-m-d'));
				}
			
				
				$result['status'] = 'success';
				$result['message'] = 'Database has been updated.';
			}
			else
			{
				$result['message'] = 'Database error.';
			}
		}
		
		echo json_encode($result);
	}
	
	
	public function actionManageSchedule()
	{
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => $html,
		);
		
		if( isset($_POST['ajax']) && isset($_POST['id']) )
		{
			$model = Calendar::model()->findByPk($_POST['id']);
			
			$html = $this->renderPartial('ajax_manage_schedule', array('model' =>$model), true);
			
			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		
		if( isset($_POST['use_default_schedule']) )
		{
			if( isset($_POST['CalendarCustomSchedule']['calendar_id']) )
			{
				$model = Calendar::model()->findByPk($_POST['CalendarCustomSchedule']['calendar_id']);
			}
			
			if($model)
			{
				if( isset($_POST['CalendarAppointmentSchedule']) )
				{
					$scheduleDifference = 0;
					$currentScheduleSettings = array();
					
					$currentCustomSchedules = CalendarCustomSchedule::model()->findAll(array(
						'condition' => 'calendar_id = :calendar_id',
						'params' => array(
							':calendar_id' => $model->id,
						),
					));
					
					if( $currentCustomSchedules )
					{
						foreach( $currentCustomSchedules as $currentCustomSchedule )
						{
							if($currentCustomSchedule)
							{
								$currentScheduleSettings[$currentCustomSchedule->day][] = $currentCustomSchedule->time;
							}
						}
					}
					
					foreach( $_POST['CalendarAppointmentSchedule'] as $key => $value)
					{
						if( !isset($currentScheduleSettings[$key]) )
						{
							$scheduleDifference++;
						}
						else
						{
							if( $currentScheduleSettings[$key] != $value )
							{
								$scheduleDifference++;
							}
						}
					}

					CalendarAppointment::model()->deleteAll(array(
						'condition' => 'calendar_id = :calendar_id AND title = "AVAILABLE"',
						'params' => array(
							':calendar_id' => $model->id,
						)
					));

					CalendarCustomSchedule::model()->deleteAll(array(
						'condition' => 'calendar_id = :calendar_id',
						'params' => array(
							':calendar_id' => $model->id,
						),
					));
					
					foreach( $_POST['CalendarAppointmentSchedule'] as $day => $times )
					{
						foreach($times as $time )
						{									
							$schedule = new CalendarCustomSchedule;
							$schedule->setAttributes(array(
								'calendar_id' => $model->id,
								'day' => $day,
								'time' => $time,
							));
							
							$schedule->save(false);
						}
					}
						
					if( $this->applyCustomSchedule($model, date('Y-m-d H:i:s')) )
					{
						$result['status'] = 'success';
						$result['message'] = 'Custom schedule has been applied. Calendar has been updated.';
					}
					
					if( $scheduleDifference > 0 )
					{
						//create audit record for schedule changes
						$history = new CustomerHistory;
						
						$history->setAttributes(array(
							'model_id' => $model->id, 
							'customer_id' => $model->customer_id,
							'user_account_id' => Yii::app()->user->account->id,
							'page_name' => 'Calendar Schedule',
							'type' => $history::TYPE_UPDATED,
						));
						
						$history->save(false);
					}
					else
					{
						$result['status'] = 'success';
						$result['message'] = 'No changes made.';
					}
				}
			}
		}
		
		echo json_encode($result);
	}
	
	
	public function actionBlackoutDays()
	{
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => '',
			'event' => array(),
			'events' => array(),
		);
		
		
		if( isset($_POST['calendar_id']) )
		{
			$start_date_time = '00:00:00';
			$end_date_time = '23:59:59';
			
			$start_date = date('Y-m-d', strtotime($_POST['start_date'])).' '.$start_date_time;
			$start_date_year = date('Y', strtotime($start_date));
			$start_date_month = date('m', strtotime($start_date));
			$start_date_day = date('d', strtotime($start_date));
			
			$end_date = date('Y-m-d', strtotime($_POST['start_date'])).' '.$end_date_time;
			$end_date_year = date('Y', strtotime($start_date));
			$end_date_month = date('m', strtotime($start_date));
			$end_date_day = date('d', strtotime($start_date));
			
			$model = new CalendarAppointment;
			
			$model->setAttributes(array(
				'calendar_id' => $_POST['calendar_id'],
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
				'title' => 'BLACKOUT DAYS',
				'all_day' => 1,
			));
			
			
			$existingRecords = CalendarAppointment::model()->findAll(array(
				'condition' => 'calendar_id = :calendar_id AND DATE(start_date) = :start_date AND status !=4 AND title NOT IN ("BLACKOUT DAYS", "AVAILABLE", "RESCHEDULE APPOINTMENT")',
				'params' => array(
					':calendar_id' => $_POST['calendar_id'],
					':start_date' => date('Y-m-d', strtotime($start_date)),
				),
			));
				
			if( $existingRecords && !isset($_POST['submitBlackoutForm']) )
			{
				$html = $this->renderPartial('ajax_blackout_form', array(
					'calendar_id' => $_POST['calendar_id'],
					'appointment_id' => null,
					'start_date' => $start_date,
					'end_date' => $end_date,
					'existingRecords' => $existingRecords,
					'formAction' => 'blackoutDays',
				), true);
				
				$result['status'] = 'success';
				$result['html'] = $html;
				
				echo json_encode($result);
				Yii::app()->end();
			}

			if($model->save())
			{
				$availableSlots = CalendarAppointment::model()->findAll(array(
					'condition' => 'calendar_id = :calendar_id AND DATE(start_date) = :start_date AND status !=4 AND title="AVAILABLE"',
					'params' => array(
						':calendar_id' => $_POST['calendar_id'],
						':start_date' => date('Y-m-d', strtotime($start_date)),
					),
				));
				
				if( $availableSlots )
				{
					foreach( $availableSlots as $availableSlot )
					{
						$availableSlot->status = 4;
						$availableSlot->save(false);
					}
				}
				
				if( date('m/d/Y', strtotime($model->start_date)) == date('m/d/Y', strtotime($model->end_date)) )
				{
					$content = 'Blackout Days | ' . date('m/d/Y', strtotime($model->start_date));
				}
				else
				{
					$content = 'Blackout Days | ' . date('m/d/Y', strtotime($model->start_date)).' to '.date('m/d/Y', strtotime($model->end_date));
				}
				
				$history = new CustomerHistory;
				
				$history->setAttributes(array(
					'model_id' => $model->id, 
					'customer_id' => $model->calendar->customer_id,
					'user_account_id' => Yii::app()->user->account->id,
					'page_name' => 'Calendar Appointment | ' . $model->calendar->name,
					'content' => $content,
					'type' => $history::TYPE_ADDED,
				));

				$history->save(false);
				
				if( !empty($_POST['existingRecordOptions']) )
				{
					foreach( $_POST['existingRecordOptions'] as $appointmentId => $optionValue )
					{
						$appointmentModel = CalendarAppointment::model()->findByPk($appointmentId);
						
						if( $appointmentModel )
						{
							if( $optionValue == 2 )
							{
								$appointmentModel->title = 'CANCEL APPOINTMENT';
								$appointmentModel->status = $appointmentModel::STATUS_DELETED;
								
								if( $appointmentModel->save(false) )
								{
									$existingLeadHopperEntry = LeadHopper::model()->find(array(
										'condition' => 'lead_id = :lead_id',
										'params' => array(
											':lead_id' => $appointmentModel->lead_id,
										),
									));
									
									if( $existingLeadHopperEntry )
									{
										$existingLeadHopperEntry->delete();
									}
								}
							}
							
							if( $optionValue == 3 )
							{
								$appointmentModel->title = 'RESCHEDULE APPOINTMENT';
								$appointmentModel->status = $appointmentModel::STATUS_DELETED;
								$appointmentModel->save(false);
								
								$this->createConfirmationCall($appointmentModel);
							}
							
							$history = new LeadHistory;
							
							if( $optionValue == 1 )
							{
								$content = 'Appointment will be kept for '.date("M d, Y g:i a",strtotime($appointmentModel->start_date));
							}
							else
							{
								$content = 'A reschedule request has been initiated for Appointment '.date("M d, Y g:i a",strtotime($appointmentModel->start_date)).' because of blackout';
							}

							$history->setAttributes(array(
								'content' => $content,
								'lead_id' => $appointmentModel->lead_id,
								'agent_account_id' => Yii::app()->user->id,
								'type' => 7,
							));
							
							$history->save(false);
						}
					}
				}

				$result['status'] = 'success';
				
				$result['event'] = array(
					'id' => $model->id,
					'title' => $model->title,
					'details' => $model->details,
					'start_date' => date('c', strtotime($model->start_date)),
					'end_date' => date('c', strtotime($model->end_date)),
					'color' => $model->getEventColor(),
				);
			}
		}
		
		if( isset($_POST['remove']) && isset($_POST['appointment_id']) )
		{
			$model = CalendarAppointment::model()->findByPk($_POST['appointment_id']);
			$calendar = $model->calendar;
			
			if( $model )
			{	
				$model->status = 4;
				
				if( $model->save(false) )
				{
					$result['status'] = 'success';
					$result['message'] = 'BLACKOUT DAYS slot has been removed.';
				}
			}
			else
			{
				$result['message'] = 'Database error.';
			}
		}
		
		echo json_encode($result);
	}
	
	public function actionApplyHolidays()
	{
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => $html,
		);
		
		
		$holidays = new US_Federal_Holidays();
		
		$holidayArray = $holidays->get_list();

		if( isset($_POST['CalendarCustomSchedule']['calendar_id']) && isset($_POST['holidays']))
		{
			$calendar = Calendar::model()->findByPk($_POST['CalendarCustomSchedule']['calendar_id']);
			
			$existingHolidaySettings = CalendarHoliday::model()->findAll(array(
				'condition' => 'calendar_id = :calendar_id',
				'params' => array(
					':calendar_id' => $calendar->id,
				),
			));
			
			if( $existingHolidaySettings )
			{
				foreach($existingHolidaySettings as $existingHolidaySetting)
				{
					$existingHolidaySetting->delete();
				}
			}
			
			foreach($_POST['holidays'] as $holiday)
			{
				if(isset($holidayArray[$holiday]))
				{
					$holidaySettings = new CalendarHoliday;
					
					$holidaySettings->setAttributes(array(
						'calendar_id' => $calendar->id,
						'name' => strtoupper($holidayArray[$holiday]['name']),
						'date' => date('Y-m-d', $holidayArray[$holiday]['timestamp']),
					));
					
					if($holidaySettings->save(false))
					{
						$existingSlots = CalendarAppointment::model()->findAll(array(
							'condition' => 'calendar_id = :calendar_id AND DATE(start_date) = :start_date AND is_custom !=2',
							'params' => array(
								':calendar_id' => $calendar->id,
								':start_date' => date('Y-m-d', $holidayArray[$holiday]['timestamp']),
							),
						));

						if( $existingSlots )
						{
							foreach( $existingSlots as $existingSlot )
							{
								$existingSlot->status = 4;
								$existingSlot->save(false);
							}
						}
					}
				}
			}
			
			$result['status'] = 'success';
		}
		
		echo json_encode($result);
	}
	
	public function actionUpdateCalendarOptions()
	{
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => $html,
		);
		
		if( isset($_POST['office_id']) )
		{
			$models = Calendar::model()->findAll(array(
				'condition' => 'office_id = :office_id AND status=1',
				'params' => array(
					':office_id' => $_POST['office_id'],
				),
			));
			
			$html .= '<option value="">- Select -</option>';
			
			if( $models )
			{
				foreach( $models as $model )
				{
					$html .= '<option value="'.$model->id.'">'.$model->name.'</option>';
				}
				
				$result['status'] = 'success';
				$result['html'] = $html;
			}
		}
		
		echo json_encode($result);
	}
	
	public function actionDeleteSlot()
	{
		$authAccount = Yii::app()->user->account;
		
		$result = array(
			'status' => 'error',
			'message' => '',			
		);
		
		if( isset($_POST['ajax']) && $_POST['event_id'] )
		{
			if( is_numeric($_POST['event_id']) )
			{
				$model = CalendarAppointment::model()->findByPk( $_POST['event_id'] );
				
				if( $model )
				{
					$model->status = CalendarAppointment::STATUS_DELETED;
					
					if( $model->save(false) )
					{
						$existingLeadHopperEntry = LeadHopper::model()->find(array(
							'condition' => 'lead_id = :lead_id',
							'params' => array(
								':lead_id' => $model->lead_id,
							),
						));
						
						if( $existingLeadHopperEntry )
						{
							$existingLeadHopperEntry->delete();
						}
						
						$result['status'] = 'success';
						$result['message'] = 'Slot has been deleted successfully.';
					}
				}
			}
			else
			{
				$calendar = Calendar::model()->findByPk($_POST['calendar_id']);
				
				// $currentDate = date('Y-m-d');
				
				// $calendarEventsArray = $this->applyCustomSchedule($calendar, $currentDate);
				
				// $calendarEventKey = array_search($_POST['event_id'], array_column($calendarEventsArray, 'id'));
				
				// $calendarEvent = $calendarEventsArray[$calendarEventKey];
				
				switch( $calendar->appointment_length )
				{
					default: 
					case '30 Minutes': $calendarAppointmentLength=30; break; 
					case '45 Minutes': $calendarAppointmentLength=45; break; 
					case '1 Hour': $calendarAppointmentLength=60; break; 
					case '1 Hour 30 Minutes': $calendarAppointmentLength=90; break; 
					case '2 Hours': $calendarAppointmentLength=120; break; 
				} 
					
				$start_date = date('Y-m-d H:i:s', strtotime($_POST['current_date']));
				
				$start_date_time = date('H:i:s', strtotime($start_date));
				
				$start_date_year = date('Y', strtotime($start_date));
				$start_date_month = date('m', strtotime($start_date));
				$start_date_day = date('d', strtotime($start_date));
				
				$end_date = date('Y-m-d H:i:s', strtotime('+'.$calendarAppointmentLength.' Minutes' , strtotime($start_date)));
					
				$end_date_time = date('H:i:s', strtotime($end_date));
				
				$end_date_year = date('Y', strtotime($end_date));
				$end_date_month = date('m', strtotime($end_date));
				$end_date_day = date('d', strtotime($end_date));
				
				
				$model = new CalendarAppointment;
				
				$model->setAttributes(array(
					'calendar_id' => $calendar->id,
					'account_id' => $authAccount->id,
					'title' => 'AVAILABLE',
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
					'is_custom' => 4,
					'status' => $model::STATUS_DELETED,
				));
				
								
				// echo '<pre>';
					// print_r($model->attributes);
				// exit;
				
				if( $model->save(false) )
				{
					$result['status'] = 'success';
					$result['message'] = 'Slot has been deleted successfully.';
				}
			}
		}
		
		echo json_encode($result);
	}
	
	public function actionAutoFillEndDate()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		if( isset($_POST['ajax']) && isset($_POST['calendar_id']) && isset($_POST['start_date_time']) )
		{
			$model = Calendar::model()->findByPk($_POST['calendar_id']);
			
			if( $model )
			{
				$start_date_time = date('H:i:s', strtotime($_POST['start_date_time']));
				$end_date_time_value = date('H:i:s', strtotime('+' . $model->appointment_length, strtotime($start_date_time)));
				$end_date_time_label = date('g:i A', strtotime('+' . $model->appointment_length, strtotime($start_date_time)));
				
				$result['status'] = 'success';
				$result['end_date_time_value'] = $end_date_time_value;
				$result['end_date_time_label'] = $end_date_time_label;
			}
		}
		
		echo json_encode($result);
	}
	
/* Start of Private Functions */
	
	private function applyDefaultSchedule($model, $currentDate)
	{
		// $currentYear = date('Y', strtotime($currentDate));
		// $currentMonth = date('m', strtotime($currentDate)); 
		// $currentDay = date('d', strtotime('+'.$model->minimum_days_appointment_set.' days', strtotime($currentDate))); 
		
		// $startDate = strtotime($currentYear.'-'.$currentMonth.'-'.$currentDay);
		// $endDate = strtotime('+'. $model->maximum_days_appointment_set.' days', $startDate);
		
		$currentDate = date_create();
		
		//if today is friday +2 days so that weekend will not count
		if( date('N') == 1 )
		{
			$minDaysOut = $model->minimum_days_appointment_set+2;
		}
		else
		{
			$minDaysOut = $model->minimum_days_appointment_set;
		}
	
		date_add($currentDate, date_interval_create_from_date_string( $minDaysOut. ' days' ));
		
		$currentYear = date_format($currentDate, 'Y');
		$currentMonth = date_format($currentDate, 'm');
		$currentDay = date_format($currentDate, 'd');
		
		$startDate = strtotime($currentYear.'-'.$currentMonth.'-'.$currentDay);
		$endDate = strtotime('+'. $model->maximum_days_appointment_set.' days', $startDate);
		
		
		$times = Calendar::createTimeRange('7:00am', '7:00pm', '1 hour');
		
		$dates = array();
		
		while( $startDate <= $endDate ) 
		{	
			$existingBlackoutDaySlot = CalendarAppointment::model()->find(array(
				'condition' => 'title = "BLACKOUT DAYS" AND calendar_id = :calendar_id AND DATE(start_date) = :start_date OR DATE(end_date) = :start_date',
				'params' => array(
					':calendar_id' => $model->id,
					':start_date' => date('Y-m-d', $startDate),
				),
			));
			
			$existingHolidaySlot = CalendarHoliday::model()->find(array(
				'condition' => 'calendar_id = :calendar_id AND MONTH(date) = :start_date_month AND DAY(date) = :start_date_day',
				'params' => array(
					':calendar_id' => $model->id,
					':start_date_month' => date('m', $startDate),
					':start_date_day' => date('d', $startDate),
				),
			));

			if( empty( $existingBlackoutDaySlot) && empty( $existingHolidaySlot) && in_array(date('N', $startDate), array(1,3,5)) )
			{
				$dates[] = date('Y-m-d', $startDate);
			}
			
			$startDate = strtotime('+1 day', $startDate);
		}
		
		
		if( $dates )
		{
			$model->use_default_schedule = 1;
			
			if( $model->save(false) )
			{
				foreach( $dates as $date )
				{
					$timeCtr = 1;
					
					foreach ($times as $time) 
					{
						if( in_array(date('g:i A', $time), array('10:00 AM', '2:00 PM', '4:00 PM')) )
						{
							$start_date = $date.' '.date('H:i:s', $time);
							$start_date_year = date('Y', strtotime($start_date));
							$start_date_month = date('m', strtotime($start_date));
							$start_date_day = date('d', strtotime($start_date));
							$start_date_time = date('H:i:s', strtotime($start_date));
							
							$end_date = $date.' '.date('H:i:s', $time);
							$end_date = date('Y-m-d H:i:s', strtotime('+1 Hour', strtotime($end_date)));
							$end_date_year = date('Y', strtotime($end_date));
							$end_date_month = date('m', strtotime($end_date));
							$end_date_day = date('d', strtotime($end_date));
							$end_date_time = date('H:i:s', strtotime($end_date));
							
							
							$existingAppointment = CalendarAppointment::model()->find(array(
								'condition' => 'calendar_id = :calendar_id AND start_date = :start_date AND status!=4',
								'params' => array(
									':calendar_id' => $model->id,
									':start_date' => $start_date,
									// ':start_date' => date('Y-m-d', '2016-02-15'),
								),
							));
							
							if(empty($existingAppointment))
							{
								$newAppointment = new CalendarAppointment;
								
								$newAppointment->setAttributes(array(
									'calendar_id' => $model->id,
									'title' => 'AVAILABLE',
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
								));
								
								$newAppointment->save(false);
							}	
						}
					}
				}
			}
		}
		
		return true;
	}
	
	
	private function applyCustomSchedule($model, $currentDate, $type='index')
	{
		// $currentYear = date('Y', strtotime($currentDate));
		// $currentMonth = date('m', strtotime($currentDate)); 
		// $currentDay = date('d', strtotime('+'.$model->minimum_days_appointment_set.' days', strtotime($currentDate)));

		$result = array();
		
		// $currentDate = date_create();

		$minDaysOut = $model->minimum_days_appointment_set;
		
		//if today is friday +2 days so that weekend will not count
		/* if( date('N') == 1 )
		{
			$minDaysOut = $model->minimum_days_appointment_set+2;
		}
		else
		{
			$minDaysOut = $model->minimum_days_appointment_set;
		} */

		// $minDaysOut = $minDaysOut-1;
		
		#date_add($currentDate, date_interval_create_from_date_string( $minDaysOut. ' days' ));
		
		// $currentYear = date_format($currentDate, 'Y');
		// $currentMonth = date_format($currentDate, 'm');
		// $currentDay = date_format($currentDate, 'd');
		
		if( $type == 'index' )
		{
			$startDate = strtotime($currentDate);
			$endDate = strtotime('+'. $model->maximum_days_appointment_set.' days', $startDate);
		}
		else
		{
			$startDate = strtotime($currentDate);
			$endDate = strtotime($currentDate);
			
			// echo 'currentDate: '. $currentDate;
			// echo ' | ';
			// echo 'startDate: ' . date('m/d/Y', $startDate);
			// echo ' | ';
			// echo 'endDate: ' . date('m/d/Y', $endDate);
			// exit; 
		}

		// $endDate = strtotime('+ 1 month', $endDate);
		
		$customSchedulesCountQuery = "
			SELECT COUNT(id) as total_count 
			FROM ud_calendar_custom_schedule
			WHERE calendar_id = '".$model->id."'
		";

		$customSchedulesCount = Yii::app()->db->createCommand($customSchedulesCountQuery)->queryRow();
		
		$blackoutSlotsQuery = "
			SELECT start_date, end_date
			FROM ud_calendar_appointment
			WHERE calendar_id = '".$model->id."'
			AND title = 'BLACKOUT DAYS'
			AND status = 1
		";

		$blackoutSlots = Yii::app()->db->createCommand($blackoutSlotsQuery)->queryAll();
	
		$timeCtr = 1;

		if( $customSchedulesCount['total_count'] > 0 )
		{
			$dates = array();
			
			while( $startDate <= $endDate ) 
			{	
				if( $type == 'index' )
				{
					$existingHolidaySlotQuery = "
						SELECT COUNT(id) as total_count
						FROM ud_calendar_holiday
						WHERE calendar_id = '".$model->id."'
						AND YEAR(date) = '".date('Y', $startDate)."'
						AND MONTH(date) = '".date('m', $startDate)."'
						AND DAY(date) = '".date('d', $startDate)."'
					";

					$existingHolidaySlot = Yii::app()->db->createCommand($existingHolidaySlotQuery)->queryRow();
					
					$checkWeekDays = date('N', $startDate);
					$isWeekDays = true;
					
					if($checkWeekDays == 6 || $checkWeekDays == 7)
					{
						$isWeekDays = false;
					}
					
					if($minDaysOut <= 1)
						$isWeekDays = true;
					
					if( $existingHolidaySlot['total_count'] == 0 && $startDate >= time() && $isWeekDays)
					{
						if($minDaysOut <= 1)
						{
							$dates[] = date('Y-m-d', $startDate);
						}
						else
							$minDaysOut--;
					}
				}
				else
				{
					$dates[] = date('Y-m-d', $startDate);
				}
				
				$startDate = strtotime('+1 day', $startDate);
			}
			
			if( $blackoutSlots && $type == 'index' )
			{	
				$blackoutDates = array();
				
				foreach( $blackoutSlots as $blackoutSlot )
				{
					$blackoutStartDate = strtotime($blackoutSlot['start_date']);
					$blackoutEndDate = strtotime($blackoutSlot['end_date']);
					
					while( $blackoutStartDate <= $blackoutEndDate )
					{
						$blackoutDates[] = date('Y-m-d', $blackoutStartDate);
						
						$blackoutStartDate = strtotime('+1 day', $blackoutStartDate);
					}
				}
				
				$dates = array_diff($dates, $blackoutDates);
			}

			if( $dates )
			{
				$ctr = 1;
				
				foreach( $dates as $date )
				{
					$appointmentCountTodayQuery = "
						SELECT COUNT(id) as total_count
						FROM ud_calendar_appointment
						WHERE calendar_id = '".$model->id."'
						AND start_date >= '".date('Y-m-d 00:00:00', strtotime($date))."'
						AND start_date <= '".date('Y-m-d 23:59:59', strtotime($date))."'
						AND title IN ('APPOINTMENT SET', 'INSERT APPOINTMENT')
						AND status NOT IN (3, 4)
					";

					$appointmentCountToday = Yii::app()->db->createCommand($appointmentCountTodayQuery)->queryRow();

					$appointmentCountThisWeekQuery = "
						SELECT COUNT(id) as total_count
						FROM ud_calendar_appointment
						WHERE calendar_id = '".$model->id."'
						AND start_date >= '".date('Y-m-d', strtotime('monday this week', strtotime($date)))."'
						AND start_date <= '".date('Y-m-d', strtotime('sunday this week', strtotime($date)))."'
						AND title IN ('APPOINTMENT SET', 'INSERT APPOINTMENT')
						AND status NOT IN (3, 4)
					";

					$appointmentCountThisWeek = Yii::app()->db->createCommand($appointmentCountThisWeekQuery)->queryRow();
					
					// echo '\n';
					// echo date('Y-m-d', strtotime($date));
					// echo '\n';
					// echo date('Y-m-d', strtotime('monday this week', strtotime($date)));
					// echo '\n';
					// echo date('Y-m-d', strtotime('sunday this week', strtotime($date)));
					// echo '\n';
					// echo 'appointmentCountThisWeek: ' . $appointmentCountThisWeek['total_count'];
					// echo '\n';
					// echo '\n';
					
					$existingAppointmentQuery = "
						SELECT start_date
						FROM ud_calendar_appointment
						WHERE calendar_id = '".$model->id."'
						AND DATE(start_date) = '".$date."'
						AND ( (status NOT IN (3, 4)) OR (status = 4 AND title='AVAILABLE') )
					";

					$existingAppointmentsToday = Yii::app()->db->createCommand($existingAppointmentQuery)->queryAll();
					
					$existingAppointmentsTodayTimes = array();
					
					if( $existingAppointmentsToday )
					{
						foreach( $existingAppointmentsToday as $existingAppointmentToday )
						{
							$existingAppointmentsTodayTimes[] = date('g:i A', strtotime($existingAppointmentToday['start_date']));
						}
					}
					
					if( $existingAppointmentsTodayTimes )
					{
						$customSchedulesQuery = "
							SELECT time
							FROM ud_calendar_custom_schedule
							WHERE calendar_id = '".$model->id."'
							AND day = '".date('l', strtotime($date))."'
							AND time NOT IN ('".implode("', '", $existingAppointmentsTodayTimes)."')
						";
					}
					else
					{
						$customSchedulesQuery = "
							SELECT time
							FROM ud_calendar_custom_schedule
							WHERE calendar_id = '".$model->id."'
							AND day = '".date('l', strtotime($date))."'
						";
					}
					
					$customSchedules = Yii::app()->db->createCommand($customSchedulesQuery)->queryAll();

					if( $customSchedules && $appointmentCountToday['total_count'] < $model->maximum_appointments_per_day && $appointmentCountThisWeek['total_count'] < $model->maximum_appointments_per_week )
					{
						$model->use_default_schedule = 0;
			
						if( $model->save(false) )
						{
							foreach ($customSchedules as $customSchedule) 
							{
								$start_date = $date.' '.date('H:i:s', strtotime($customSchedule['time']));
								$start_date_year = date('Y', strtotime($start_date));
								$start_date_month = date('m', strtotime($start_date));
								$start_date_day = date('d', strtotime($start_date));
								$start_date_time = date('H:i:s', strtotime($start_date));
								
								$end_date = date('Y-m-d H:i:s', strtotime('+'.$model->appointment_length , strtotime($start_date)));
								$end_date_year = date('Y', strtotime($end_date));
								$end_date_month = date('m', strtotime($end_date));
								$end_date_day = date('d', strtotime($end_date));
								$end_date_time = date('H:i:s', strtotime($end_date));
								
								// if( $existingAppointmentToday['total_count'] == 0 )
								// {
									$result[] = array(
										'id' => 'cal-'.$model->id.'-event-'.$ctr,
										'title' => 'AVAILABLE',
										'details' => '',
										'start_date' => date('c', strtotime($start_date)),
										'end_date' => date('c', strtotime($end_date)),
										'color' => '#6FB3E0',
										'allDay' => false,
										'is_custom' => 4,
										'status' => 1,
									);
									
									$ctr++;
								// }	
							}
						}
					}
				}
			}
		}
		
		return $result;
	}

	
	private function loadHolidaySlots($model, $currentDate)
	{
		$result = array();
		
		$holidays = CalendarHoliday::model()->findAll(array(
			'condition' => 'calendar_id = :calendar_id',
			'params' => array(
				':calendar_id' => $model->id,
			),
		));
		
		if( $holidays )
		{
			foreach( $holidays as $holiday )
			{
				// $date = date('Y-', strtotime($currentDate)).date('m-d', strtotime($holiday->date));
				$date = date('Y-m-d', strtotime($holiday->date));
				
				$blackoutDayCheck = CalendarAppointment::model()->find(array(
					'condition' => 'title="BLACKOUT DAYS" AND calendar_id = :calendar_id AND DATE(start_date) = :start_date AND status=1',
					'params' => array(
						':calendar_id' => $model->id,
						':start_date' => date('Y-m-d', strtotime($date)),
					),
				));
			
				if( empty($blackoutDayCheck) )
				{
					$result[] = array(
						'id' => $holiday->id,
						'title' => $holiday->name,
						'details' => '',
						'start_date' => date('c', strtotime($date)),
						'end_date' => date('c', strtotime($date)),
						'color' => '#9585BF',
						'allDay' => 1,
						'is_custom' => 2,
					);
				}
			}
		}
		
		return $result;
	}
	
	
	private function checkAppointmentsThisWeek($model, $date) 
	{
		// Assuming $date is in format DD-MM-YYYY
		list($year, $month, $day) = explode("-", $date);
		
		// Get the weekday of the given date
		$weekday = date('l',mktime('0','0','0', $month, $day, $year));

		switch($weekday) {
			case 'Monday': $numDaysToMon = 0; break;
			case 'Tuesday': $numDaysToMon = 1; break;
			case 'Wednesday': $numDaysToMon = 2; break;
			case 'Thursday': $numDaysToMon = 3; break;
			case 'Friday': $numDaysToMon = 4; break;
			case 'Saturday': $numDaysToMon = 5; break;
			case 'Sunday': $numDaysToMon = 6; break;   
		}

		// Timestamp of the monday for that week
		$monday = mktime('0','0','0', $month, $day-$numDaysToMon, $year);

		$seconds_in_a_day = 86400;

		// Get date for 7 days from Monday (inclusive)
		for($i=0; $i<7; $i++)
		{
			$dates[$i] = date('Y-m-d', $monday+($seconds_in_a_day*$i) );
		}
		
		foreach( $dates as $date )
		{
			$existingAppointment = CalendarAppointment::model()->find(array(
				'condition' => 'calendar_id = :calendar_id AND start_date = :start_date',
				'params' => array(
					':calendar_id' => $model->id,
					':start_date' => $date,
				),
			));
			
			echo 'date: ' . $date.' - '. count($existingAppointment);
			echo '<br>';
		}

		echo '<br><br>end..';	
		exit;
		
		return true;
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
			$customer = $lead->customer;
			
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
			$leadPhoneNumber = null;
			
			$leadHistory = new LeadHistory;
						
			$leadCallHistory = LeadCallHistory::model()->find(array(
				'condition' => 'lead_id = :lead_id',
				'params' => array(
					':lead_id' => $model->lead_id,
				),
				'order' => 'date_created DESC',
			));
			
			if( $leadCallHistory )
			{
				$leadHistory->lead_call_history_id = $leadCallHistory->id;
				
				$leadPhoneNumber = $leadCallHistory->lead_phone_number;
				
				$leadCallHistory->calendar_appointment_id = $model->id;
				$leadCallHistory->save(false);
			}
			
			$leadHistory->setAttributes(array(
				'lead_id' => $model->lead_id,
				'lead_phone_number' => $leadPhoneNumber,
				'disposition' => $model->title,
				'agent_account_id' => isset($leadCallHistory->agent_account_id) ? $leadCallHistory->agent_account_id : Yii::app()->user->account->id,
				'calendar_appointment_id' => $model->id,
				'note' => $model->details,
				'type' => 5,
			));
			
			$leadHistory->save(false);
		}
		
		return true;
	}
}

?>