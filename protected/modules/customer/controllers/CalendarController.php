<?php 

class CalendarController extends Controller
{
	
	public function actionIndex($customer_id)
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
				
				if( $customer && $customer->id != $customer_id )
				{
					$this->redirect(array('index', 'customer_id'=>$customer->id));
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
				
				if( $customerOfficeStaff && $customerOfficeStaff->customer_id != $customer_id )
				{
					$this->redirect(array('index', 'customer_id'=>$customerOfficeStaff->customer_id));
				}
			}
		}
		
		
		$customer = Customer::model()->findByPk($customer_id);
		
		$offices = CustomerOffice::model()->findAll(array(
			'condition' => 'customer_id = :customer_id AND is_deleted=0',
			'params' => array(
				':customer_id' => $customer->id,
			),
		));
		
		$this->render('index', array(
			'customer' => $customer,
			'offices' => $offices,
		));
	}
	
	
	public function actionCreate()
	{
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => $html,
		);
		
		$holidays = new US_Federal_Holidays();
		
		$holidayArray = $holidays->get_list();
		
		$officeId = isset($_POST['office_id']) ? $_POST['office_id'] : $_POST['Calendar']['office_id'];
		$customerId = isset($_POST['customer_id']) ? $_POST['customer_id'] : $_POST['Calendar']['customer_id'];
		
		$office = CustomerOffice::model()->findByPk( $officeId );
		
		$models = Calendar::model()->findAll(array(
			'condition' => 'office_id = :office_id',
			'params' => array(
				':office_id' => $office->id,
			),
		));
		
		$existingCalendar = Calendar::model()->count( array(
			'condition' => 'customer_id = :customer_id',
			'params' => array(
				':customer_id' => $customerId,
			),
		));
		
		$calendarNameSuffix = '';
		
		if( $existingCalendar > 0 )
		{
			$calendarNameSuffix = ' ('.($existingCalendar + 1).')';
		}
		
		$model = new Calendar;		
		
		$model->setAttributes(array(
			'customer_id' => $customerId,
			'office_id' => $office->id,
			'name' => 'New Calendar' . $calendarNameSuffix,
			'maximum_appointments_per_day' => 3,
			'maximum_appointments_per_week' => 10,
			'minimum_days_appointment_set' => 3,
			'maximum_days_appointment_set' => 30,
			'appointment_start_time' => '7:00 AM',
			'appointment_end_time' => '7:00 PM',
			'appointment_length' => '1 Hour',
			'location_office' => 1,
			'use_default_schedule' => 0,
		));
		
		$calendarStaffAssignment = new CalendarStaffAssignment;
	
		
		if( isset($_POST['Calendar']) && isset($_POST['CalendarStaffAssignment']) )
		{
			$model->attributes = $_POST['Calendar'];
			$calendarStaffAssignment->attributes = $_POST['CalendarStaffAssignment'];
			
			if( $model->save(false) ) 
			{
				$calendarStaffAssignment->calendar_id = $model->id;			
				$calendarStaffAssignment->save(false);
				
				$history = new CustomerHistory;
				
				$history->setAttributes(array(
					'model_id' => $model->id, 
					'customer_id' => $model->customer_id,
					'user_account_id' => Yii::app()->user->id,
					'page_name' => 'Calendar',
					'content' => $model->name,
					'type' => $history::TYPE_ADDED,
				));

				$history->save(false);
				
				
				$existingCustomSchedules = CalendarCustomSchedule::model()->findAll(array(
					'condition' => 'calendar_id = :calendar_id',
					'params' => array(
						':calendar_id' => $model->id,
					),
				));

				
				if($existingCustomSchedules)
				{
					foreach( $existingCustomSchedules as $existingCustomSchedule )
					{
						$existingCustomSchedule->delete();
					}
				}

				//apply holiday settings
				if( isset($_POST['holidays']))
				{
					foreach($_POST['holidays'] as $holiday)
					{
						if(isset($holidayArray[$holiday]))
						{
							$holidaySettings = new CalendarHoliday;
							
							$holidaySettings->setAttributes(array(
								'calendar_id' => $model->id,
								'name' => strtoupper($holidayArray[$holiday]['name']),
								'date' => date('Y-m-d', $holidayArray[$holiday]['timestamp']),
							));
							
							$holidaySettings->save(false);
						}
					}
					
					$result['status'] = 'success';
				}
				
				
				//appyly schedule
				
				if( isset($_POST['CalendarAppointmentSchedule']) )
				{
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
						
					if( $this->applyCustomSchedule($model, $model->date_updated) )
					{
						$result['status'] = 'success';
						$result['message'] = 'Custom schedule has been applied. Calendar has been updated.';
					}
				}
				
				$models = Calendar::model()->findAll(array(
					'condition' => 'office_id = :office_id AND status=1',
					'params' => array(
						':office_id' => $office->id,
					),
				));
		
				if($models)
				{
					foreach( $models as $model )
					{
						$html .= '<tr>';
							$html .= '<td>'.$model->name.'</td>';
							$html .= '<td class="center">';
								$html .= CHtml::link('<i class="fa fa-search"></i> View', array('//calendar/index', 'calendar_id'=>$model->id, 'customer_id'=>$customerId));
								$html .= '&nbsp;&nbsp;&nbsp;&nbsp;';
								$html .= CHtml::link('<i class="fa fa-edit"></i> Edit', 'javascript:void(0);', array('id'=>$model->id, 'class'=>'edit-calendar-btn'));
								$html .= '&nbsp;&nbsp;&nbsp;&nbsp;';
								$html .= CHtml::link('<i class="fa fa-times"></i> Delete', 'javascript:void(0);', array('id'=>$model->id, 'class'=>'delete-calendar-btn'));
							$html .='</td>';
						$html .= '</tr>';
					}
				}
				
				$html .= '<tr>';
					$html .= '<td colspan="2" class="center">';
					$html .= '
						<a customer_office_id="'.$model->office_id.'" customer_id="'.$model->customer_id.'" class="btn btn-xs btn-primary add-calendar-btn" style="border-radius:3px;">
							Add Calendar
						</a>';
					$html .= '</td>';
				$html .= '</tr>';
		
				
				$result['html'] = $html;
				
				$result['status'] = 'success';
				$result['message'] = 'Database has been updated.';
			}
			else
			{
				$result['message'] = 'Database error.';
			}
		}
		
		
		if( isset($_POST['ajax']) )
		{	
			$html = $this->renderPartial('ajax_create', array(
				'calendar' => $model,
				'calendarStaffAssignment' => $calendarStaffAssignment,
				'models' => $models,
				'office' => $office,
			), true);
			
			$result['status'] = 'success';
			$result['html'] = $html;
		}	
		
		
		echo json_encode($result);
	}
	
	
	public function actionUpdate()
	{
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => $html,
		);
		
		$authAccount = Yii::app()->user->account;
		
		$currentValues = array();
		$currentScheduleSettings = array();
		
		$holidays = new US_Federal_Holidays();
		
		$holidayArray = $holidays->get_list();
		
		$customerId = isset($_POST['calendar_id']) ? $_POST['calendar_id'] : $_POST['Calendar']['id'];
		
		$model = Calendar::model()->findByPk( $customerId );
		
		$office = CustomerOffice::model()->findByPk( $model->office_id );
		
		$calendarStaffAssignment = CalendarStaffAssignment::model()->find(array(
			'condition' => 'calendar_id = :calendar_id',
			'params' => array(
				'calendar_id' => $model->id,
			),
		));
		
		$currentStaffId = $calendarStaffAssignment->staff_id;
		
		
		$models = Calendar::model()->findAll(array(
			'condition' => 'office_id = :office_id',
			'params' => array(
				':office_id' => $office->id,
			),
		));
		

		//store current schedule settings to temp array for diff checking
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
	
		
		if( isset($_POST['Calendar']) && isset($_POST['CalendarStaffAssignment']) )
		{
			$currentValues = $model->attributes;
			
			$model->attributes = $_POST['Calendar'];
			
			$calendarStaffAssignment->attributes = $_POST['CalendarStaffAssignment'];
			
			
			//check if current model values has difference from post values
			$difference = array_diff($model->attributes, $currentValues);	
			
			//check if current schedule values has difference from post values
			$scheduleDifference = 0;
			
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

			
			if( $model->save(false) ) 
			{
				$calendarStaffAssignment->calendar_id = $model->id;
				
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
							'content' =>  'staff on calendar '.$model->name.' changed from '.$oldStaffName.' to '.$newStaffName,
							'old_data' => json_encode(array('staff_id'=>$currentStaffId)),
							'new_data' => json_encode(array('staff_id'=>$calendarStaffAssignment->staff_id)),
							'type' => $history::TYPE_UPDATED,
						));

						$history->save(false);
					}
				}
				
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
				
				//check schedule difference
				if( $scheduleDifference > 0 )
				{
					//recreate schedule values if there is a difference
					$existingCustomSchedules = CalendarCustomSchedule::model()->findAll(array(
						'condition' => 'calendar_id = :calendar_id',
						'params' => array(
							':calendar_id' => $model->id,
						),
					));

					
					if($existingCustomSchedules)
					{
						foreach( $existingCustomSchedules as $existingCustomSchedule )
						{
							$existingCustomSchedule->delete();
						}
					}
					
					//appyly schedule
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
				
					if( $this->applyCustomSchedule($model, $model->date_updated) )
					{
						$result['status'] = 'success';
						$result['message'] = 'Custom schedule has been applied. Calendar has been updated.';
					}
				
					
					//create audit record for schedule changes
					$history = new CustomerHistory;
					$history->scenario = 'add2Hours';
					
					$history->setAttributes(array(
						'model_id' => $model->id, 
						'customer_id' => $model->customer_id,
						'user_account_id' => $authAccount->id,
						'page_name' => 'Calendar Schedule',
						'type' => $history::TYPE_UPDATED,
					));
					
					$history->save(false);
				}
				
				
				//check for calendar staff assigment difference
				
				//apply holiday settings
				if( isset($_POST['CalendarCustomSchedule']['calendar_id']) && isset($_POST['holidays']))
				{
					$calendar = Calendar::model()->findByPk($_POST['CalendarCustomSchedule']['calendar_id']);
					
					$existingHolidaySettings = CalendarHoliday::model()->findAll(array(
						'condition' => 'calendar_id = :calendar_id',
						'params' => array(
							':calendar_id' => $model->id,
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
								'calendar_id' => $model->id,
								'name' => strtoupper($holidayArray[$holiday]['name']),
								'date' => date('Y-m-d', $holidayArray[$holiday]['timestamp']),
							));
							
							if($holidaySettings->save(false))
							{
								$existingSlots = CalendarAppointment::model()->findAll(array(
									'condition' => 'calendar_id = :calendar_id AND DATE(start_date) = :start_date AND is_custom !=2',
									'params' => array(
										':calendar_id' => $model->id,
										':start_date' => date('Y-m-d', $holidayArray[$holiday]['timestamp']),
									),
								));

								if( $existingSlots )
								{
									foreach( $existingSlots as $existingSlot )
									{
										$existingSlot->delete();
									}
								}
							}
						}
					}
					
					$result['status'] = 'success';
				}
				
				
				$models = Calendar::model()->findAll(array(
					'condition' => 'office_id = :office_id AND status=1',
					'params' => array(
						':office_id' => $office->id,
					),
				));
		
				if($models)
				{
					foreach( $models as $model )
					{
						$html .= '<tr>';
							$html .= '<td>'.$model->name.'</td>';
							$html .= '<td class="center">';
								$html .= CHtml::link('<i class="fa fa-search"></i> View', array('//calendar/index', 'calendar_id'=>$model->id, 'customer_id'=>$customerId));
								$html .= '&nbsp;&nbsp;&nbsp;&nbsp;';
								$html .= CHtml::link('<i class="fa fa-edit"></i> Edit', 'javascript:void(0);', array('id'=>$model->id, 'class'=>'edit-calendar-btn'));
								$html .= '&nbsp;&nbsp;&nbsp;&nbsp;';
								$html .= CHtml::link('<i class="fa fa-times"></i> Delete', 'javascript:void(0);', array('id'=>$model->id, 'class'=>'delete-calendar-btn'));
							$html .='</td>';
						$html .= '</tr>';
					}
				}
				
				
				$html .= '<tr>';
					$html .= '<td colspan="2" class="center">';
					$html .= '
						<a customer_office_id="'.$model->office_id.'" customer_id="'.$model->customer_id.'" class="btn btn-xs btn-primary add-calendar-btn" style="border-radius:3px;">
							Add Calendar
						</a>';
					$html .= '</td>';
				$html .= '</tr>';
		
				
				$result['html'] = $html;
				
				$result['status'] = 'success';
				$result['message'] = 'Database has been updated.';
			}
			else
			{
				$result['message'] = 'Database error.';
			}
		}
		
		
		if( isset($_POST['ajax']) )
		{	
			$html = $this->renderPartial('ajax_update', array(
				'calendar' => $model,
				'calendarStaffAssignment' => $calendarStaffAssignment,
				'models' => $models,
				'office' => $office,
			), true);
			
			$result['status'] = 'success';
			$result['html'] = $html;
		}	
		
		
		echo json_encode($result);
	}

	
	public function actionDelete()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		if( isset($_POST['ajax']) && isset($_POST['calendar_id']) )
		{	
			$model = Calendar::model()->findByPk($_POST['calendar_id']);
			
			if( $model )
			{
				$model->status = 3;
				
				if($model->save(false))
				{
					$history = new CustomerHistory;
			
					$history->setAttributes(array(
						'model_id' => $model->id, 
						'customer_id' => $model->customer_id,
						'user_account_id' => Yii::app()->user->account->id,
						'page_name' => 'Calendar',
						'content' => $model->name,
						'type' => $history::TYPE_DELETED,
					));

					$history->save(false);
				
					$result['status'] = 'success';
				}
			}
		}	
		
		echo json_encode($result);
	}
	
	
	public function actionGetStandardTabLayout()
	{
		$html = '';
		
		$result = array(
			'status' => 'error',
			'html' => $html,
		);
		
		if( isset($_POST['ajax']) && $_POST['office_id'] )
		{
			$office = CustomerOffice::model()->findByPk($_POST['office_id']);
			
			$customer = Customer::model()->findByPk($office->customer_id);
			
			$calendars = Calendar::model()->findAll(array(
				'condition' => 'office_id = :office_id',
				'params' => array(
					':office_id' => $office->id,
				),
			));
			
			$officeStaffs = CustomerOfficeStaff::model()->findAll(array(
				'condition' => 'customer_id = :customer_id AND customer_office_id = :customer_office_id',
				'params' => array(
					':customer_id' => $customer->id,
					':customer_office_id' => $office->id,
				),
			));
			
			if( $office )
			{
				$html = $this->renderPartial('index_standard_layout', array(
					'office' => $office,
					'officeStaffs' => $officeStaffs,
					'customer' => $customer,
					'calendars' => $calendars,
				), true);
				
				
				$result['status'] = 'success';
				$result['html'] = $html;
			}
		}
		
		echo json_encode($result);
		Yii::app()->end();
	}
	
	

/** START OF PRIVATE FUNCTIONS **/	
	
	private function applyDefaultSchedule($model, $currentDate)
	{
		$currentYear = date('Y', strtotime($currentDate));
		$currentMonth = date('m', strtotime($currentDate)); 
		$currentDay = date('d', strtotime('+'.$model->minimum_days_appointment_set.' days', strtotime($currentDate))); 
		
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
								'condition' => 'calendar_id = :calendar_id AND start_date = :start_date',
								'params' => array(
									':calendar_id' => $model->id,
									':start_date' => $start_date,
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
	
	
	private function applyCustomSchedule($model, $currentDate)
	{
		$currentYear = date('Y', strtotime($currentDate));
		$currentMonth = date('m', strtotime($currentDate)); 
		$currentDay = date('d', strtotime('+'.$model->minimum_days_appointment_set.' days', strtotime($currentDate))); 
		
		$startDate = strtotime($currentYear.'-'.$currentMonth.'-'.$currentDay);
		$endDate = strtotime('+'. $model->maximum_days_appointment_set.' days', $startDate);
		
		$customSchedules = CalendarCustomSchedule::model()->find(array(
			'condition' => 'calendar_id = :calendar_id',
			'params' => array(
				':calendar_id' => $model->id,
			),
		));
		
		$blackoutSlots = CalendarAppointment::model()->findAll(array(
			'condition' => 'title = "BLACKOUT DAYS" AND calendar_id = :calendar_id',
			'params' => array(
				':calendar_id' => $model->id,
			),
		));

		if($customSchedules)
		{
			$dates = array();
			
			while( $startDate <= $endDate ) 
			{	
				$existingHolidaySlot = CalendarHoliday::model()->find(array(
					'condition' => 'calendar_id = :calendar_id AND MONTH(date) = :start_date_month AND DAY(date) = :start_date_day',
					'params' => array(
						':calendar_id' => $model->id,
						':start_date_month' => date('m', $startDate),
						':start_date_day' => date('d', $startDate),
					),
				));
			
				if( empty($existingHolidaySlot) )
				{
					$dates[] = date('Y-m-d', $startDate);
				}
				
				$startDate = strtotime('+1 day', $startDate);
			}
			
			
			if( $blackoutSlots )
			{	
				$blackoutDates = array();
				
				foreach( $blackoutSlots as $blackoutSlot )
				{
					$blackoutStartDate = strtotime($blackoutSlot->start_date);
					$blackoutEndDate = strtotime($blackoutSlot->end_date);
					
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
				foreach( $dates as $date )
				{
					$customSchedules = CalendarCustomSchedule::model()->findAll(array(
						'condition' => 'calendar_id = :calendar_id AND day = :day',
						'params' => array(
							':calendar_id' => $model->id,
							':day' => date('l', strtotime($date)),
						),
					));
					
					if( $customSchedules )
					{
						$model->use_default_schedule = 0;
			
						if( $model->save(false) )
						{
							foreach ($customSchedules as $customSchedule) 
							{
								$start_date = $date.' '.date('H:i:s', strtotime($customSchedule->time));
								$start_date_year = date('Y', strtotime($start_date));
								$start_date_month = date('m', strtotime($start_date));
								$start_date_day = date('d', strtotime($start_date));
								$start_date_time = date('H:i:s', strtotime($start_date));
								
								$end_date = date('Y-m-d H:i:s', strtotime('+'.$model->appointment_length , strtotime($start_date)));
								$end_date_year = date('Y', strtotime($end_date));
								$end_date_month = date('m', strtotime($end_date));
								$end_date_day = date('d', strtotime($end_date));
								$end_date_time = date('H:i:s', strtotime($end_date));
								
								
								$existingAppointment = CalendarAppointment::model()->find(array(
									'condition' => 'calendar_id = :calendar_id AND start_date = :start_date',
									'params' => array(
										':calendar_id' => $model->id,
										':start_date' => $start_date,
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
										'is_custom' => 1,
									));
									
									$newAppointment->save(false);
								}	
							}
						}
					}
				}
			}
		}
		
		return true;
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
				$date = date('Y-', strtotime($currentDate)).date('m-d', strtotime($holiday->date));
				
				$blackoutDayCheck = CalendarAppointment::model()->find(array(
					'condition' => 'title="BLACKOUT DAYS" AND calendar_id = :calendar_id AND DATE(start_date) = :start_date',
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

	
	private function checkMultiDimensionalArrayDiff($array1, $array2)
	{
		foreach($array1 as $key => $value)
		{
			if(is_array($value))
			{
				if(!isset($array2[$key]))
				{
					$difference[$key] = $value;
				}
				elseif(!is_array($array2[$key]))
				{
					$difference[$key] = $value;
				}
				else
				{
					$new_diff = $this->checkMultiDimensionalArrayDiff($value, $array2[$key]);
					if($new_diff != FALSE)
					{
						$difference[$key] = $new_diff;
					}
				}
			}
			elseif(!isset($array2[$key]) || $array2[$key] != $value)
			{
				$difference[$key] = $value;
			}
		}
		return !isset($difference) ? 0 : $difference;
	}
}

?>