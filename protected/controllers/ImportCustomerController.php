<?php 

class ImportCustomerController extends Controller
{
	
	public function actionIndex()
	{
		ini_set('memory_limit', '512M');
		set_time_limit(0);
		
		// unregister Yii's autoloader
		spl_autoload_unregister(array('YiiBase', 'autoload'));
	
		$phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
		include($phpExcelPath . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'PHPExcel.php');

		spl_autoload_register(array('YiiBase', 'autoload'));
		 
		
		// $inputFileName = 'csv/Customers-2-27-2016-v2.csv';
		// $inputFileName = 'csv/Customers-3.csv';
		// $inputFileName = 'csv/Customers-2-27-2016.csv';
		$inputFileName = 'csv/ImportThreeCustomer/Customers-5-11-2016.csv';


		$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
		
		$worksheet = $objPHPExcel->getActiveSheet();

		$highestRow         = $worksheet->getHighestRow(); // e.g. 10
		$highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
		$nrColumns = ord($highestColumn) - 64;
		
		$transaction = Yii::app()->db->beginTransaction();
		$customerNotAdded = array();
		try
		{
			for ($row = 2; $row <= $highestRow; ++$row) 
			{
				$customerPrimaryKey = $worksheet->getCell('A'.$row)->getValue();
				$cmpCompanyName = $worksheet->getCell('B'.$row)->getValue();
				$agentId = $worksheet->getCell('C'.$row)->getValue();
				
				$firstname = $worksheet->getCell('D'.$row)->getValue();
				$lastname = $worksheet->getCell('E'.$row)->getValue();
				$phone = $worksheet->getCell('F'.$row)->getValue();
				$address = $worksheet->getCell('G'.$row)->getValue();
				$city = $worksheet->getCell('H'.$row)->getValue();
				$state = $worksheet->getCell('I'.$row)->getValue();
				$zip = $worksheet->getCell('J'.$row)->getValue();
				
				$emailnightly = $worksheet->getCell('K'.$row)->getValue();
				
				$email = $worksheet->getCell('L'.$row)->getValue();
				
				$cctype = $worksheet->getCell('M'.$row)->getValue();
				$ccnumber = $worksheet->getCell('N'.$row)->getValue();
				$ccsecuritycode = $worksheet->getCell('O'.$row)->getValue();
				$ccexpdate = $worksheet->getCell('P'.$row)->getValue();
				
				$gatewayusername = $worksheet->getCell('Q'.$row)->getValue();
				$gatewaypassword = $worksheet->getCell('R'.$row)->getValue();
				
				// var_dump($customerPrimaryKey);
				// exit;
				
				#COMPANY ID MANIPULATION
				$companyId = 0;
				
				if($cmpCompanyName == 'State Farm Insurance')
					$companyId = 13;
				
				if($cmpCompanyName == 'Farmers Insurance')
					$companyId = 9;
				
				if($cmpCompanyName == 'Safeco')
					$companyId = 14;
				
				#STATE ID MANIPULATION
				$stateId = 0;
				
				if(!empty($state))
				{
					$criteria = new CDbCriteria;
					$criteria->compare('abbreviation', $state);
					
					$fState = State::model()->find($criteria);
					
					if($fState !== null)
						$stateId = $fState->id;
				}
				
				//CUSTOMER's EMAIL ADDRESS MANIPULATION
				##email column have multiple email address, we are just going to get the 1st one.
				if(!empty($email))
				{
					$_email = explode(";", $email);
					$cleanEmail = isset($_email[0]) ? $_email[0] : $_email[1];
					$cleanEmail = trim($cleanEmail);
				}
				
				## Customer's Credit Card manipulation
				$creditCardType = '';
				if(!empty($cctype))
				{
					switch($cctype)
					{
						case 'A':
							$creditCardType = 'Amex';
						break;
						
						case 'D':
							$creditCardType = 'Discover';
						break;
						
						case 'M':
							$creditCardType = 'MasterCard';
						break;
						
						case 'V':
							$creditCardType = 'Visa';
						break;
					}
				}
				
				//create customer module
				$model=new Customer;
				
				$model->setAttributes(array(
					'firstname' => $firstname,
					'lastname' => $lastname,
					'company_id' => $companyId,
					'custom_customer_id' => $agentId,
					'status' => Customer::STATUS_ACTIVE,
					'phone' => $phone,
					'phone_timezone' => '', //set null :D
					'email_address' => $cleanEmail, 
					'address1' => $address, 
					'city' => $city, 
					'state' => $stateId, 
					'zip' => $zip, 
					'import_customer_primary_key' => $customerPrimaryKey, 
					'import_agent_id' => $agentId, 
				));
				
				
				if($model->validate(array('firstname', 'lastname', 'company_id', 'status', 'phone', 'email_address', 'address1', 'city', 'state', 'zip','import_customer_primary_key')))
				{
					$model->save(false);
					
					echo $model->id. ' | '. $model->firstname.' '.$model->lastname.'<br>';
					
					#auto create Account
					$account = $this->autoCreateAccount($model, $gatewayusername, $gatewaypassword);
					
					#auto create office, staff and calendar entry
					$office = new CustomerOffice;
					
					$office->setAttributes(array(
						'customer_id' => $model->id,
						'office_name' => 'Office 1',
						'address' => $model->address1,
						'city' => $model->city,
						'state' => $model->state,
						'zip' => $model->zip,
						'phone' => $model->phone,
						'status' => 1,
					));
					
					if($office->save())
					{
						$staff = new CustomerOfficeStaff;
						
						$staff->setAttributes(array(
							'customer_id' => $model->id,
							'customer_office_id' => $office->id,
							'staff_name' => $model->firstname.' '.$model->lastname,
							'email_address' => $model->email_address,
							'status' => 1,
						));
						
						if( $staff->save() )
						{
							$calendar = new Calendar;		
							$calendar->setAttributes(array(
								'customer_id' => $model->id,
								'office_id' => $office->id,
								'staff_id' => $staff->id,
								'name' => $staff->staff_name,
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
							
							if($calendar->save())
							{
								$calendarStaffAssignment = new CalendarStaffAssignment;
								$calendarStaffAssignment->setAttributes(array(
									'calendar_id' => $calendar->id,
									'staff_id' => $staff->id
								));
								
								$calendarStaffAssignment->save(false);
								
								//save schedule settings
								$schedules = array(
									'Monday' => array(
										'10:00 AM', 
										'2:00 PM', 
										'4:00 PM'
									),
									'Tuesday' => array(
										'10:00 AM', 
										'2:00 PM',
										'4:00 PM'
									),
									'Wednesday' => array(
										'10:00 AM', 
										'2:00 PM',
										'4:00 PM'
									),
									'Thursday' => array(
										'10:00 AM', 
										'2:00 PM',
										'4:00 PM'
									),
									'Friday' => array(
										'10:00 AM',
										'2:00 PM',
										'4:00 PM'
									),
								);
								
								foreach( $schedules as $day => $times )
								{
									foreach($times as $time )
									{									
										$schedule = new CalendarCustomSchedule;
										$schedule->setAttributes(array(
											'calendar_id' => $calendar->id,
											'day' => $day,
											'time' => $time,
										));
										
										$schedule->save(false);
									}
								}
								
								
								//Apply holidays
								$holidays = new US_Federal_Holidays();
			
								$holidayArray = $holidays->get_list();
								
								foreach($holidayArray as $holiday)
								{
									$holidaySettings = new CalendarHoliday;
									
									$holidaySettings->setAttributes(array(
										'calendar_id' => $calendar->id,
										'name' => strtoupper($holiday['name']),
										'date' => date('Y-m-d', $holiday['timestamp']),
									));
									
									$holidaySettings->save(false);
								}
									
								//apply default schedule
								$this->applyCustomSchedule($calendar, $model->date_updated);
							}
							
						}
					
						$email_staffs = explode(';',$emailnightly);
						foreach($email_staffs as $emailStaff)
						{
							$emailStaff = trim($emailStaff);
							$this->autoCreateCustomerOfficeStaff($emailStaff, $model->id, $office->id);
						}
					}
					
					#create billing and credit card
					if(!empty($creditCardType))
					{
						$this->createCustomerCreditCard($model, $creditCardType, $ccnumber, $ccsecuritycode, $ccexpdate);
					}
					// $this->redirect(array('view','id'=>$model->id));
					
					
				}
				else
				{
					// print_r($model->attributes);
					// echo '<br><br>';
					// print_r($model->getErrors()); exit;
					
					$customerNotAdded[] = $model;
					// print_r($model->attributes);
					
					// exit;
				}
			}	
		
			$transaction->commit();
			echo 'Success';
			echo '<br><br>';
			echo '<pre>';
			foreach($customerNotAdded as $notAdded)
			{
				print_r($notAdded->firstname.' '.$notAdded->lastname);
				print_r($notAdded->getErrors());
			}
			echo '</pre>';
		}
		catch(Exception $e)
		{
			print_r($model->attributes);
			print_r($e);
			
		}
			
	}

	public function autoCreateAccount($model, $username, $password)
	{
		if(!empty($username) && !empty($password))
		{
			$account = new Account;
			$account->email_address = $model->email_address;
			$account->account_type_id = Account::TYPE_CUSTOMER;
			$account->username = $username;
			$account->password = $password;
			$account->status = Account::STATUS_ACTIVE;
			
			// $getToken=rand(0, 99999);
			// $getTime=date("H:i:s");
			// $account->token = md5($getToken.$getTime);
			// $account->token_date = date("Y-m-d H:i:s", strtotime("+30 minutes"));
			
			if($account->validate(array('email_address', 'username', 'password')))
			{
				$account->save(false);
				
				$model->account_id = $account->id;
				if($model->save(false))
				{
					
				}
			}
			else
			{
				//check if emailaddress already exist, but doesn't have username yet
				$criteria = new CDbCriteria;
				$criteria->compare('email_address',$model->email_address);
				$findAccount = Account::model()->find($criteria);
				if($findAccount !== null)
				{
					if(empty($findAccount->username))
					{
						$findAccount->username = $account->username;
						$findAccount->password = $account->password;
						$findAccount->save(false);
						
						$model->account_id = $findAccount->id;
						if($model->save(false))
						{
							
						}
					}
				}
				else
				{
					$criteria = new CDbCriteria;
					$criteria->compare('username',$username);
					$findAccount = Account::model()->find($criteria);
					
					if($findAccount !== null)
					{
						
					}
					else 
					{
						// if($model->import_customer_primary_key == 
						print_r($account->attributes);
						echo '<br><br>';
						print_r($account->getErrors());
						exit;
					}
					
					
				}
				
			}
			
			// return $account;
		}
	}

	public function autoCreateCustomerOfficeStaff($email,$customer_id,$customer_office_id)
	{
		
		$authAccountId = 2; // username = markjuan;
		
		$model=new CustomerOfficeStaff;
		$model->customer_id = $customer_id;
		$model->customer_office_id = $customer_office_id;
		
		$model->staff_name = $email;
		$model->email_address = $email;
		$model->status = 1;
		
		if($model->save())
		{
			// $account = $this->autoCreateAccountOfficeStaff($model);
			
			$history = new CustomerHistory;
			
			$history->setAttributes(array(
				'model_id' => $model->id, 
				'customer_id' => $model->customer_id,
				'user_account_id' => $authAccountId,
				'page_name' => 'Staff',
				'content' => $model->staff_name,
				'type' => $history::TYPE_ADDED,
			));

			$history->save(false);
		}
	}
	
	public function autoCreateAccountOfficeStaff($model)
	{
		$account = new Account;
		$account->email_address = $model->email_address;
		$account->account_type_id = Account::TYPE_CUSTOMER_OFFICE_STAFF;
		
		$getToken=rand(0, 99999);
		$getTime=date("H:i:s");
		$account->token = md5($getToken.$getTime);
		$account->token_date = date("Y-m-d H:i:s", strtotime("+30 minutes"));
		
		if($account->save())
		{
			$model->account_id = $account->id;
			if($model->save(false))
			{
				// if($model->is_portal_access)
					// $this->emailSend($account);
			}
		}
		
		return $account;
	}
	
	public function createCustomerCreditCard($customer, $creditCardType, $ccnumber, $ccsecuritycode, $ccexpdate)
	{
		
		$expirationDate = explode("/",$ccexpdate);
		
		$expirationMonth = '';
		$expirationYear = '';
		
		if(isset($expirationDate[1]))
		{
			//10/2017
			$expirationMonth = $expirationDate[0];
			$expirationYear = $expirationDate[1];
		}
		else
		{
			 //012017
			$expirationMonth = substr($ccexpdate,0,2);
			$expirationYear = substr($ccexpdate,2,4);
		}
		
		// echo $expirationMonth;
		// echo $expirationYear;
		// exit;
		
		$authAccountId = 2; // username = markjuan;
		
		$model = new CustomerCreditCard;
		$model->setAttributes(array(
			'credit_card_number' => $ccnumber,
			'credit_card_type' => $creditCardType,
			'security_code' => $ccsecuritycode,
			'expiration_month' => $expirationMonth,
			'expiration_year' => $expirationYear,
			'first_name' => $customer->firstname,
			'last_name' => $customer->lastname,
			'address' => $customer->address1,
			'city' => $customer->city,
			'state' => $customer->state,
			'zip' => $customer->zip
		));
		
		// if( isset($_POST['CustomerCreditCard']) )
		// {
			// $existingCreditCard = CustomerCreditCard::model()->count(array(
				// 'condition' => 'customer_id = :customer_id AND status=1',
				// 'params' => array(
					// ':customer_id' => $customer->id
				// ),
			// ));
			
			// $model->attributes = ;
			
			// if( empty($_POST['CustomerCreditCard']['nick_name']) )
			// {
				// $sameCreditCardType = CustomerCreditCard::model()->findall(array(
					// 'condition' => 'customer_id = :customer_id AND credit_card_type = :credit_card_type AND status=1',
					// 'params' => array(
						// ':customer_id' => $_POST['customer_id'],
						// ':credit_card_type' => $model->credit_card_type,
					// ),
				// ));

				// if( count($sameCreditCardType) > 0 )
				// {
					$tempNickName = $model->credit_card_type;
					// $tempNickName .= ' ' . count($sameCreditCardType) + 1;
				// }
				// else
				// {
					// $tempNickName = $model->credit_card_type;
				// }
				
				$model->nick_name = $tempNickName;
			// }

			
			$model->customer_id = $customer->id;	

			// if( $existingCreditCard == 0 )
			// {
				$model->is_preferred = 1;
			// }
			
			if( $model->save(false) )
			{
				$history = new CustomerHistory;
				
				$history->setAttributes(array(
					'model_id' => $model->id, 
					'customer_id' => $model->customer_id,
					'user_account_id' => $authAccountId,
					'page_name' => 'Credit Card',
					'content' => $model->credit_card_type.' '.substr($model->credit_card_number, -4),
					'type' => $history::TYPE_ADDED,
				));

				$history->save(false);
			}
		// }
	}
	
	//used to insert calendar appointment slots
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
		
		
		$timeCtr = 1;

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
			
			
			if( $dates )
			{
				foreach( $dates as $date )
				{
					$timeCtr = 1;
					
					$customSchedules = CalendarCustomSchedule::model()->findAll(array(
						'condition' => 'calendar_id = :calendar_id AND day = :day',
						'params' => array(
							':calendar_id' => $model->id,
							':day' => date('l', strtotime($date)),
						),
					));
					
					if( $customSchedules )
					{
						foreach ($customSchedules as $customSchedule) 
						{
							if( $timeCtr <= $model->maximum_appointments_per_day )
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
								
								$timeCtr++;
							}
						}
						
					}
				}
			}
		}
		
		return true;
	}

	
}

?>