<?php 

class ImportCustomerLeadAppointmentController extends Controller
{
	
	public function actionIndex()
	{
		// echo 'Check file before running this import script';
		// exit;
		
		ini_set('memory_limit', '512M');
		set_time_limit(0);
		
		// unregister Yii's autoloader
		spl_autoload_unregister(array('YiiBase', 'autoload'));
	
		$phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
		include($phpExcelPath . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'PHPExcel.php');

		spl_autoload_register(array('YiiBase', 'autoload'));
		 
		
		// $inputFileName = 'csv/ImportCustomerLeadHistory/CallHistory-1000.csv';
		// $inputFileName = 'csv/ImportCustomerLeadAppointment/Appointments.csv';
		// $inputFileName = 'csv/ImportCustomerLeadAppointment/Appointments-CustomerFound.csv';
		// $inputFileName = 'csv/ImportCustomerLeadAppointment/Appointments-CustomerFound-Peter-Mathison.csv';
		// echo $inputFileName = 'csv/Richard/Appointments.csv';
		echo $inputFileName = 'csv/Thomas Keasler/Appointments.csv';

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
				
				$customerPrimaryKey = $worksheet->getCell('A'.$row)->getValue(); //CustomerPrimaryKey
				$officePrimaryKey = $worksheet->getCell('B'.$row)->getValue(); //Office Primary Key
				$clientPrimaryKey = $worksheet->getCell('C'.$row)->getValue(); //ClientPrimaryKey
				$datetime = $worksheet->getCell('D'.$row)->getValue(); //datetime
				$name = $worksheet->getCell('E'.$row)->getValue(); //name
				$phone = $worksheet->getCell('F'.$row)->getValue(); //phone
				
				$customerKeyHolder[$customerPrimaryKey][$clientPrimaryKey][$datetime] = array(
					'import_client_primary_key' => $clientPrimaryKey,
					'import_customer_primary_key' => $customerPrimaryKey,
					'datetime' => $datetime,
					'name' => $name,
					'phone' => $phone,
				);
			}
			
			
			$customerNotFound = array();
			$leadNotFound = array();
			
			echo '<pre>';
			
			foreach($customerKeyHolder as $customerPk => $customerLeadData)
			{
				echo '<br>';
				echo '--- '.$customerPk.' ---';
				echo '<br>';
				
				$criteria = new CDbCriteria;
				$criteria->compare('import_customer_primary_key', $customerPk);
				$customer = Customer::model()->find($criteria);
				
				if($customer !== null)
				{
					$customer_id = $customer->id;
					
					$customerCalendar = Calendar::model()->find(array(
						'condition' => 'customer_id = :customer_id',
						'params' => array(
							':customer_id' => $customer_id,
						),
					));
		
					$customerSkill = CustomerSkill::model()->find(array(
						'condition' => 'customer_id = :customer_id AND status=1',
						'params' => array(
							':customer_id' => $customer_id,
						),
					));
					
					if($customerCalendar !== null && $customerSkill !== null)
					{
						
						foreach($customerLeadData as  $clientPrimaryKey => $appointmentLeadData)
						{
							echo '<br>';
							echo '--- Client Primary Key: '.$clientPrimaryKey.'---';
							echo '<br>';
							
							$criteria = new CDbCriteria;
							$criteria->compare('customer_id', $customer_id);
							$criteria->compare('import_client_primary_key', $clientPrimaryKey);
							
							$lead = Lead::model()->find($criteria);
							
							if($lead !== null)
							{
								foreach($appointmentLeadData as $appointmentData)
								{
									echo '<br>';
									echo 'Creating new appointment';
									echo '</br>';
									
									if(isset($lead->list))
										$this->createAppointment($lead, $appointmentData, $customerCalendar);
									else
									{
										$leadNotFound[$customerPk][$clientPrimaryKey]['lead without a list'] = $clientPrimaryKey;
									}
								}
							}
							else
							{
								$leadNotFound[$customerPk][$clientPrimaryKey] = $clientPrimaryKey;
							}
							
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
					$customerNotFound[$customerPk] = $customerPk;
				}
			}
			
			echo '<br>';
			echo 'Customer not found:';
			echo '<br>';
			print_r($customerNotFound);
			echo '<br>';
			echo '<br>-------------<br>';
			echo '<br>';
			echo 'Lead not found:';
			echo '<br>';
			print_r($leadNotFound);
			echo '<br>';
			
			$transaction->commit();
		}
		catch(Exception $e)
		{
			$transaction->rollback();
			print_r($e);
		}
			
	}
	
	public function createAppointment($lead, $appointmentData, $calendar)
	{
		
		// $customerKeyHolder[$customerPrimaryKey][$clientPrimaryKey][$datetime] = array(
					// 'import_client_primary_key' => $clientPrimaryKey,
					// 'import_customer_primary_key' => $customerPrimaryKey,
					// 'datetime' => $datetime,
					// 'name' => $name,
					// 'phone' => $phone,
				// );
		$dataTime = date("Y-m-d H:i:s",strtotime($appointmentData['datetime']));
		
		$_POST['CalendarAppointment']['start_date_time'] = date("H:i",strtotime($dataTime));
		$_POST['current_date'] = date("Y-m-d",strtotime($dataTime));
		$_POST['CalendarAppointment']['end_date_time'] = date("H:i", strtotime($dataTime . "+1hour"));
		$_POST['CalendarAppointment']['title'] = 'INSERT APPOINTMENT';
		$_POST['CalendarAppointment']['lead_id'] = $lead->id;
		
		// if( isset($_POST['CalendarAppointment']) )
		// {
			$dbChanges = 0;
		
			$startDateTime = date('H:i:s', strtotime($_POST['CalendarAppointment']['start_date_time']));
			
			$startDate = date('Y-m-d', strtotime($_POST['current_date'])).' '.$startDateTime;
			
			$endDateTime = date('H:i:s', strtotime($_POST['CalendarAppointment']['end_date_time']));
			
			$endDate = date('Y-m-d', strtotime($_POST['current_date'])).' '.$endDateTime;
			$endDate = date('Y-m-d H:i:s', strtotime($endDate));
			
			
			foreach (Calendar::createTimeRange($startDate, $endDate, $calendar->appointment_length) as $time) 
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
				
				$end_date = date('Y-m-d H:i:s', strtotime('+'.$calendar->appointment_length , strtotime($start_date)));
				
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
						'condition' => 'calendar_id = :calendar_id AND status !=4 AND ((end_date > :start_date ) AND (start_date < :end_date))',
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
							$existingCalendarAppointment->delete();
							
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
							$model->date_created = $model->date_updated = date("2016-03-31 00:00:00");
							$model->save(false);
							
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
												AND t.status=2 
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
									'condition' => 'lead_id = :lead_id AND agent_account_id = :agent_account_id',
									'params' => array(
										':lead_id' => $model->lead_id,
										':agent_account_id' => null,
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
									'agent_account_id' => Yii::app()->user->account->account_type_id == Account::TYPE_AGENT ? Yii::app()->user->account->id : '',
									'calendar_appointment_id' => $model->id,
									'note' => $model->details,
									'type' => 3,
								));
								
								$leadHistory->save(false);
								
								
								//if its an insert appointment insert a hopper entry for confirmation call
								if( $model->title == 'INSERT APPOINTMENT' )
								{
									$this->createConfirmationCall($model);
								}
							}
							
							$dbChanges++;
						}
					}
				}
			}
			
			if( $dbChanges > 0 )
			{
				$result['status'] = 'success';
			}
		// }
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
			
		if($model->title == 'NO SHOW RESCHEDULE')
		{
			$confirmationCall->status = 'READY';
			$confirmationCall->type = LeadHopper::TYPE_NO_SHOW_RESCHEDULE;
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
				'agent_account_id' => isset($leadCallHistory->agent_account_id) ? $leadCallHistory->agent_account_id : null,
				'calendar_appointment_id' => $model->id,
				'note' => $model->details,
				'type' => 5,
			));
			
			$leadHistory->save(false);
		}
		
		return true;
	}

	public function actionRemoveAppointment()
	{
		$criteria = new CDbCriteria;
		$criteria->compare('is_imported', 1);
		
		$leads = Lead::model()->findAll($criteria);
		
		foreach($leads as $lead)
		{
			echo 'Lead ID: '.$lead->id;
			echo '<Br>';

			$criteria = new CDbCriteria;
			$criteria->compare('lead_id', $lead->id);
			
			$calendarAppointments = CalendarAppointment::model()->findAll($criteria);
			
			foreach($calendarAppointments as $calendarAppointment)
			{
				$calendarAppointment->status = 4;
				$calendarAppointment->save(false);
				echo 'Appointment Deleted.<br>';
			}
			
			$criteria = new CDbCriteria;
			$criteria->compare('lead_id', $lead->id);
			
			$leadHoppers = LeadHopper::model()->findAll($criteria);
			
			foreach($leadHoppers as $leadHopper)
			{
				$leadHopper->delete(false);
				echo 'Hopper Deleted.<br>';
			}
		}
		
		exit;
	}
	
	public function actionRemoveLead()
	{
		$criteria = new CDbCriteria;
		$criteria->compare('is_imported', 1);
		
		$leads = Lead::model()->findAll($criteria);
		
		$ctr = 0;
		foreach($leads as $lead)
		{
			$lead->delete();
			echo 'Lead Deleted.<br>';
			$ctr++;
		}
		
		echo '<br>Total Lead deleted: '.$ctr;
	}
	
	public function actionRemoveCallHistory()
	{
		$criteria = new CDbCriteria;
		$criteria->compare('is_imported', 1);
		
		$leads = LeadHistory::model()->findAll($criteria);
		
		$ctr = 0;
		foreach($leads as $lead)
		{
			$lead->delete();
			echo 'Lead History Deleted.<br>';
			$ctr++;
		}
		
		echo '<br>Total Lead History deleted: '.$ctr;
	}
	
	public function actionRemoveImportList()
	{
		$criteria = new CDbCriteria;
		$criteria->compare('name', 'System Imported List');
		
		$models = Lists::model()->findAll($criteria);
		
		
		$ctr = 0;
		foreach($models as $model)
		{
			$model->delete();
			echo 'List Deleted.<br>';
			$ctr++;
		}
		
		echo '<br>Total Lists deleted: '.$ctr;
	}
}

?>