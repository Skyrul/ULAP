<?php

ini_set('memory_limit', '4000M');
set_time_limit(0);

class DataController extends Controller
{

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$this->redirect(array('update','id'=> $id));
		
		// $this->render('view',array(
			// 'model'=>$this->loadModel($id),
		// ));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new Customer;
		
		$selectedSalesReps = array();

		if(isset($_POST['Customer']['salesRepIds']))
			$selectedSalesReps = $_POST['Customer']['salesRepIds'];
		
		$authAccount = Yii::app()->user->account;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Customer']))
		{
			$model->attributes=$_POST['Customer'];
			
			if( $model->validate() )
			{
				if($model->save())
				{
					if( !empty($_POST['Customer']['salesRepIds']) )
					{
						CustomerSalesRep::model()->deleteAll(array(
							'condition' => 'customer_id = :customer_id',
							'params' => array(
								':customer_id' => $model->id,
							),
						));
						
						foreach( $_POST['Customer']['salesRepIds'] as $salesRepAccountId )
						{
							$salesRep = new CustomerSalesRep;
									
							$salesRep->setAttributes(array(
								'customer_id' => $model->id,
								'sales_rep_account_id' => $salesRepAccountId
							));
							
							$salesRep->save(false);
						}
					}
					
					
					//auto create Account
					$account = $this->autoCreateAccount($model);
					
					//auto create office, staff and calendar entry
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
					}
					
					
					$this->redirect(array('view','id'=>$model->id));
				}
			}
		}

		$this->render('create',array(
			'model'=>$model,
			'selectedSalesReps'=>$selectedSalesReps,
		));
	}
	
	public function autoCreateAccount($model)
	{
		$account = new Account;
		$account->email_address = $model->email_address;
		$account->account_type_id = Account::TYPE_CUSTOMER;
		
		$getToken=rand(0, 99999);
		$getTime=date("H:i:s");
		$account->token = md5($getToken.$getTime);
		$account->token_date = date("Y-m-d H:i:s", strtotime("+3 days"));
		
		if($account->save(false))
		{
			$model->account_id = $account->id;
			if($model->save(false))
			{
				$this->emailSend($account);
		
			}
		}
		
		return $account;
	}
	
	public function emailSend($account)
	{
		$yiiName = Yii::app()->name;
				$emailadmin= Yii::app()->params['adminEmail'];
				$emailSubject="Customer Registration";
				
				$emailContent = "Link for your account portal creation<br/>
				Email Address: ".$account->customer->email_address."<br/>
					<a href='http://portal.engagexapp.com/index.php/site/register?token=".$account->token."'>Click Here to create your account</a>";
					
					
				$emailTemplate = '<table width="80%" align="center">
					<tr>
						<td>
							<table width="100%" border="0" cellpadding="0" cellspacing="0">
								<tr>
									<td align="center" bgcolor="#0068B1" height="10px;"></td>
								</tr>
								<tr>
									<td align="center" bgcolor="#FCB245">&nbsp;</td>
								</tr>
							</table>
							
							<br />
							
							'.$emailContent.'
							
							<br /><br/>
							
							<table width="100%" border="0" cellpadding="0" cellspacing="0">
								<tr>
									<td align="center" bgcolor="#FCB245">&nbsp;</td>
								</tr>
								<tr>
									<td align="left" bgcolor="#0068B1" height="10px;" style="font-size:18px; padding:5px;">&copy; Engagex, 2015</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>';
				
				
				$name='=?UTF-8?B?'.base64_encode($yiiName).'?=';
				$subject='=?UTF-8?B?'.base64_encode($emailSubject).'?=';
				$headers="From: $name <{$emailadmin}>\r\n".
					"Reply-To: {$emailadmin}\r\n".
					"MIME-Version: 1.0\r\n".
					"Content-type: text/html; charset=UTF-8";
				
			// return	mail($account->email_address,$subject,$emailTemplate,$headers);
			
			//Send  Email
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
	
			$mail->SetFrom('service@engagex.com');
			$mail->AddCC('customerservice@engagex.com');
			
			$mail->Subject = $subject;
			
			$mail->AddAddress( $account->customer->email_address );
			
			// $mail->AddBCC('jim.campbell@engagex.com');
			$mail->AddBCC('markjuan169@gmail.com');
			
			$mail->MsgHTML( $emailTemplate);
									
			$mail->Send();
			
	}
	
	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
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
				
				if( $customer && $customer->id != $id )
				{
					$this->redirect(array('update', 'id'=>$customer->id));
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
				
				if( $customerOfficeStaff && $customerOfficeStaff->customer_id != $id )
				{
					$this->redirect(array('update', 'id'=>$customerOfficeStaff->customer_id));
				}
			}
		}
		
		
		$authAccount = Yii::app()->user->account;
		
		$currentValues = array();
		
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Customer']))
		{
			$currentValues = $model->attributes;
			
			$model->attributes=$_POST['Customer'];
			
			$difference = array_diff($model->attributes, $currentValues);
			
			if($model->save())
			{
				if( !empty($_POST['Customer']['salesRepIds']) )
				{
					CustomerSalesRep::model()->deleteAll(array(
						'condition' => 'customer_id = :customer_id',
						'params' => array(
							':customer_id' => $model->id,
						),
					));
					
					foreach( $_POST['Customer']['salesRepIds'] as $salesRepAccountId )
					{
						$salesRep = new CustomerSalesRep;
								
						$salesRep->setAttributes(array(
							'customer_id' => $model->id,
							'sales_rep_account_id' => $salesRepAccountId
						));
						
						$salesRep->save(false);
					}
				}
				
				//create record for auditing
				if( $difference )
				{
					$updateFields = '';
				
					foreach( $difference as $attributeName => $value)
					{
						if( $attributeName == 'state' )
						{
							$oldStateValue = State::model()->findByPk($currentValues[$attributeName])->name;
							$newStateValue = State::model()->findByPk($value)->name;
							
							$updateFields .= $model->getAttributeLabel($attributeName) .' changed from '.$oldStateValue.' to '.$newStateValue.', ';
						}
						else
						{
							$updateFields .= $model->getAttributeLabel($attributeName) .' changed from '.$currentValues[$attributeName].' to '.$value.', ';
						}
					}
					
					$updateFields = rtrim($updateFields, ', ');
					
					$history = new CustomerHistory;
					
					$history->setAttributes(array(
						'model_id' => $model->id, 
						'customer_id' => $model->id,
						'user_account_id' => $authAccount->id,
						'page_name' => 'Customer Setup',
						'content' => $updateFields,
						'old_data' => json_encode($currentValues),
						'new_data' => json_encode($model->attributes),
						'type' => $history::TYPE_UPDATED,
					));

					$history->save(false);
				}
				
				
				//auto create Account
				if(!isset($model->account))
				{
					$account = $this->autoCreateAccount($model);
				}
				else
				{
					$account = $model->account;
					if(isset($_POST['Account']))
					{
						$account->username = $_POST['Account']['username'];
						$account->email_address = $model->email_address;

						if($account->save(false))
						{
							$staff = CustomerOfficeStaff::model()->find(array(
								'condition' => 'account_id = :account_id',
								'params' => array(
									':account_id' => $account->id,
								),
							));
							
							if( $staff )
							{
								$staff->account->username = $account->username;
								$staff->account->email_address = $account->email_address;
								$staff->account->save(false);
							}
							
							if($account->id == $authAccount->id)
							{
								Yii::app()->user->name = $account->username;
							}
						}
					}
				}
				
				Yii::app()->user->setFlash('success', 'Customer setup updated!');
				$this->redirect(array('view','id'=>$model->id));
			}
		}
		
		## Skill Company ##
		$criteria = new CDbCriteria;
		$criteria->compare('customer_id',$model->id);
		$selectedSalesReps = CHtml::listData(CustomerSalesRep::model()->findAll($criteria),'sales_rep_account_id','sales_rep_account_id');

		$this->render('update',array(
			'model'=>$model,
			'selectedSalesReps'=>$selectedSalesReps,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		$this->loadModel($id)->delete();

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		if(Yii::app()->user->account->getIsCustomer())
		{
			if( isset($_GET['popup']) )
			{
				$this->redirect(array('insight/index','customer_id'=> Yii::app()->user->account->customer->id, 'popup'=>$_GET['popup']));
			}
			else
			{
				$this->redirect(array('insight/index','customer_id'=> Yii::app()->user->account->customer->id));
			}
		}
		
		if(Yii::app()->user->account->getIsCustomerOfficeStaff())
		{
			if( isset($_GET['popup']) )
			{
				$this->redirect(array('insight/index','customer_id'=> Yii::app()->user->account->customerOfficeStaff->customer_id, 'popup'=>$_GET['popup']));
			}
			else
			{
				$this->redirect(array('insight/index','customer_id'=> Yii::app()->user->account->customerOfficeStaff->customer_id));
			}
		}
		
		if( Yii::app()->user->account->account_type_id == Account::TYPE_HOSTDIAL_AGENT )
		{
			$this->redirect(array('/agent'));
		}
		
		$this->render('index',array(
			
		));
	}

	public function actionList()
	{
		$criteria = new CDbCriteria;
		
		if( Yii::app()->user->account->account_type_id == Account::TYPE_GAMING_PROJECT_MANAGER )
		{
			$assignedCustomerIds = array();
			
			$assignedCustomers = AccountCustomerAssigned::model()->findAll(array(
				'condition' => 'account_id = :account_id',
				'params' => array(
					':account_id' => Yii::app()->user->account->id,
				),
			));
			
			if( $assignedCustomers )
			{
				foreach( $assignedCustomers as $assignedCustomer )
				{
					$assignedCustomerIds[] = $assignedCustomer->customer_id;
				}
			}
			
			$criteria->with = array('account.customerEnrollment', 'officeStaff', 'company');
			$criteria->addCondition('company.is_host_dialer = 1', 'AND');
			
			$criteria->addCondition('t.id IN ("'.implode('","', $assignedCustomerIds).'")', 'AND');
		}
		else
		{
			$criteria->with = array('account.customerEnrollment', 'officeStaff');
		}
		
		$criteria->order = 'CASE WHEN customerEnrollment.date_created IS NOT NULL THEN customerEnrollment.date_created ELSE t.date_created END DESC';
		
		if( empty($_GET['search_query']) && empty($_GET['search_filter']) && in_array($_SERVER['SERVER_NAME'], array('system.engagexapp.com', 'portal.engagexapp.com')) )
		{
			$criteria->addCondition('t.company_id NOT IN("17", "18", "23")', 'AND');
		}
		
		if( isset($_GET['search_query']) && trim($_GET['search_query']) != "" )
		{
			$criteria->addCondition('t.firstname LIKE :search_query', 'OR');
			$criteria->addCondition('t.lastname LIKE :search_query', 'OR');
			$criteria->addCondition('CONCAT(t.firstname , " " , t.lastname) LIKE :search_query', 'OR');
			
			$criteria->addCondition('officeStaff.staff_name LIKE :search_query', 'OR');
			
			$criteria->params[':search_query'] = $_GET['search_query'].'%';
		}
		
		if( $_SERVER['SERVER_NAME'] == 'test.engagexapp.com' )
		{
			$_GET['search_filter'] = 'showTests';
		}
		
		if( isset($_GET['search_filter']) && trim($_GET['search_filter']) != "" )
		{
			if( $_GET['search_filter'] == 'showAll' )
			{
				$criteria->addCondition('t.company_id NOT IN("17", "18", "23")', 'AND');
			}
			
			if( $_GET['search_filter'] == 'hideInactive' )
			{
				$criteria->addCondition('t.company_id NOT IN("17", "18", "23")', 'AND');
				
				$criteria->addCondition('t.status != :inactive', 'AND');
				$criteria->params[':inactive'] = 2;
			}
			
			if( $_GET['search_filter'] == 'showNewOnly' )
			{
				$criteria->addCondition('t.company_id NOT IN("17", "18", "23")', 'AND');
				
				$criteria->addCondition('DATE_ADD(account.date_created, INTERVAL 3 DAY) > :today', 'AND');
				$criteria->params[':today'] = date('Y-m-d H:i:s');
			} 
			
			if( $_GET['search_filter'] == 'showNewFilesOnly' )
			{
				$criteria->addCondition('t.company_id NOT IN("17", "18", "23")', 'AND');
				
				$criteria->addCondition('DATE_ADD(latestCustomerFile.date_created, INTERVAL 72 HOUR) > :today', 'AND');
				$criteria->params[':today'] = date('Y-m-d H:i:s');
			} 
			
			if( $_GET['search_filter'] == 'showTests' )
			{
				$criteria->addCondition('t.company_id IN("17", "18", "23")', 'AND');
			}
		}
		else
		{
			$criteria->addCondition('t.company_id NOT IN("17", "18", "23")', 'AND');
			
			//hide inactives as default view
			$criteria->addCondition('t.status != :inactive', 'AND');
			$criteria->params[':inactive'] = 2;
		}	
		
		
		if(Yii::app()->user->account->getIsCustomer())
		{
			// $customers = Customer::model()->byStatus(Customer::STATUS_ACTIVE)->byIsDeletedNot()->byAccountId(Yii::app()->user->account->id)->findAll();
			$customers = Customer::model()->byIsDeletedNot()->byAccountId(Yii::app()->user->account->id)->orderByDateCreated()->searchAndFilter($criteria)->findAll();
		}
		else if(Yii::app()->user->account->getIsCustomerOfficeStaff())
		{ 
			// $customers = Customer::model()->byStatus(Customer::STATUS_ACTIVE)->byIsDeletedNot()->byAccountId(Yii::app()->user->account->customerOfficeStaff->customer_id)->findAll();
			$customers = Customer::model()->byIsDeletedNot()->byAccountId(Yii::app()->user->account->customerOfficeStaff->customer_id)->orderByDateCreated()->searchAndFilter($criteria)->findAll();

		}
		else if(Yii::app()->user->account->getIsCompany())
		{ 
			// $customers = Customer::model()->byStatus(Customer::STATUS_ACTIVE)->byIsDeletedNot()->byCompanyId(Yii::app()->user->account->company->id)->findAll();
			$customers = Customer::model()->byIsDeletedNot()->byCompanyId(Yii::app()->user->account->company->id)->orderByDateCreated()->searchAndFilter($criteria)->findAll();

		}
		else{
			// $customers = Customer::model()->byStatus(Customer::STATUS_ACTIVE)->byIsDeletedNot()->findAll();
			$customers = Customer::model()->byIsDeletedNot()->orderByDateCreated()->searchAndFilter($criteria)->findAll();
		}

		$dataProvider = new CArrayDataProvider($customers, array(
			// 'sort'=>array(
				// 'defaultOrder'=>'CASE WHEN customerEnrollment.date_created IS NULL THEN 0 ELSE 1 END  , customerEnrollment.date_created DESC, t.date_created DESC',
			  // ),
			'pagination' => array(
				'pageSize' => 100,
			),
		));
		
		// $customers->findAll();
		
		// $model=new Customer('search');
		// $model->unsetAttributes();  // clear any default values
		// if(isset($_GET['Customer']))
			// $model->attributes=$_GET['Customer'];
		
		if(isset($_GET['ajaxRequest']) && $_GET['ajaxRequest'] == 1)
		{
            Yii::app()->clientScript->scriptMap['*.js'] = false;
			
            $this->renderPartial('_list', array(
				'customers' => $customers,
				'dataProvider' => $dataProvider,
			), false, true);
        }
        else
		{
			$this->renderPartial('_list', array(
				'customers' => $customers,
				'dataProvider' => $dataProvider,
			));
		}
	}
	
	public function actionCustomerSummary($id = null)
	{
		$customer = Customer::model()->findByPk($id);
		
		$this->renderPartial('customerSummary',array(
			'customer' => $customer,
		),false,true);
		
	}
	
	public function actionUpload()
	{
		// Settings
		$targetDir = 'fileupload';

		$cleanupTargetDir = true; // Remove old files
		$maxFileAge = 5 * 3600; // Temp file age in seconds

		// 5 minutes execution time
		@set_time_limit(5 * 60);

		// Get parameters
		$chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
		$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
		// $fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';
		$fileName = $_FILES['FileUpload']['name']['filename'];
		
		$filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

		// Create target dir
		if (!file_exists($targetDir))
			@mkdir($targetDir);

		// Remove old temp files	
		if ($cleanupTargetDir && is_dir($targetDir) && ($dir = opendir($targetDir))) {
			while (($file = readdir($dir)) !== false) {
				$tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

				// Remove temp file if it is older than the max age and is not the current file
				if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge) && ($tmpfilePath != "{$filePath}.part")) {
					@unlink($tmpfilePath);
				}
			}

			closedir($dir);
		} else
			die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
		
			

		// Look for the content type header
		if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
			$contentType = $_SERVER["HTTP_CONTENT_TYPE"];

		if (isset($_SERVER["CONTENT_TYPE"]))
			$contentType = $_SERVER["CONTENT_TYPE"];
		
		
		
		
		// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
		if (strpos($contentType, "multipart") !== false) {
			if (isset($_FILES['FileUpload']['tmp_name']['filename']) && is_uploaded_file($_FILES['FileUpload']['tmp_name']['filename'])) {
				// Open temp file
				$out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
				if ($out) {
					// Read binary input stream and append it to temp file
					$in = fopen($_FILES['FileUpload']['tmp_name']['filename'], "rb");

					if ($in) {
						while ($buff = fread($in, 4096))
							fwrite($out, $buff);
					} else
						die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
					fclose($in);
					fclose($out);
					//@unlink($_FILES['file']['tmp_name']);
				} else
					die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
			} else
				die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
		} else {
			// Open temp file
			$out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
			if ($out) {
				// Read binary input stream and append it to temp file
				$in = fopen("php://input", "rb");

				if ($in) {
					while ($buff = fread($in, 4096))
						fwrite($out, $buff);
				} else
					die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');

				fclose($in);
				fclose($out);
			} else
				die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
		}
		
		
		// Check if file has been uploaded
		if (!$chunks || $chunk == $chunks - 1)
		{
			// Strip the temp .part suffix off 
			rename("{$filePath}.part", $filePath);
			
			$file = CUploadedFile::getInstanceByName('FileUpload[filename]');
			
			$getFileExtension = explode('.', $fileName);

			if(count($getFileExtension > 1))
			{
				$manFileExt = $getFileExtension[count($getFileExtension) - 1];
			}
			
			$rnd = rand(0,9999).strtotime(date("Y-m-d H:i:s")); 
			$fileName = "{$rnd}-{$fileName}";
		
			// Rename file to use generated unique filename
			rename($filePath, $targetDir . DIRECTORY_SEPARATOR . $fileName);
			
			
			$fileupload = new Fileupload;
			
			
			$fileupload->setAttributes(array(
				'generated_filename' => $fileName,
			));
			
			if( $fileupload->save(false) )
			{
				$thumb=Yii::app()->phpThumb->create($fileupload->imagePath.$fileName);
				$thumb->resize(100,100);
				$thumb->save($fileupload->imagePathThumb.$fileName);
				
				$customer = Customer::model()->findByPk($_REQUEST['event_id']);
				
				if($customer !== null)
				{
					$currentValues = $customer->attributes;
					
					$customer->fileupload_id = $fileupload->id;

					$difference = array_diff($customer->attributes, $currentValues);
					
					if($customer->save(false))
					{
						//create record for auditing
						if( $difference )
						{
							$updateFields = 'Photo';
							
							$history = new CustomerHistory;
							
							$history->setAttributes(array(
								'model_id' => $customer->id, 
								'customer_id' => $customer->id,
								'user_account_id' => Yii::app()->user->account->id,
								'page_name' => 'Customer Setup',
								'content' => $updateFields,
								'old_data' => json_encode($currentValues),
								'new_data' => json_encode($customer->attributes),
								'type' => $history::TYPE_UPDATED,
							));

							$history->save(false);
						}
				
						die('{"jsonrpc" : "2.0", "generatedFileUploadId": "'.$fileupload->id.'", "generatedFilename": "' . $fileName . '", "fileExtension": "' . $manFileExt. '"}');
					}
				}
				
				
			}
		}
		
		// Return JSON-RPC response
		die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
	}
	
	public function actionVoiceRecord()
	{
		$model = Customer::model()->findByPk($_POST['id']);
		if($model === null)
				throw new CHttpException('403', 'Page not found.');
		
		$rnd = rand(0,9999).strtotime(date("Y-m-d H:i:s")).'-'.preg_replace('/[^a-zA-Z0-9-_\.]/','', $model->getFullName()).'.wav'; 
		
		if(isset($_POST['audio'])){
			  $audio = $_POST['audio'];
			  
			  $file = str_replace('data:audio/wav;base64,', '', $audio); 
			  
			  $decoded = base64_decode($file);
			  // $file_location = Yii::app()->request->baseUrl."/voice/recorded_audio.wav";
			  $file_location =Yii::app()->basePath.'/../voice/'.$rnd;
			  file_put_contents($file_location, $decoded);
			  
			  
			  $fileupload = new Fileupload;
			  $fileupload->original_filename = $rnd;
			  $fileupload->generated_filename = $rnd;
			  
			  if($fileupload->save(false))
			  {
				  $model->voiceupload_id = $fileupload->id;
				  $model->save(false);
			  }
			  
			  echo Yii::app()->request->baseUrl."/voice/".$rnd;
		}
		
		
		
	}
	
	public function actionRegenerateToken($id)
	{
		$model = $this->loadModel($id);
		
		$account = $model->account;
		
		$getToken=rand(0, 99999);
		$getTime=date("H:i:s");
		$account->token = md5($getToken.$getTime);
		$account->token_date = date("Y-m-d H:i:s", strtotime("+3 days"));
		
		if( $account->save(false) )
		{
			$this->emailSend($account);
		}
		
		$this->redirect(array('data/update','id' => $model->id));
	}
	
	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Customer the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Customer::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Customer $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='customer-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}

	public function actionFileupload($id)
    {
		$customer = Customer::model()->findByPk($id);
		
		if($customer === null)
			throw new CHttpException(404,'The requested page does not exist.');
		
        $model=new Fileupload;  // this is my model related to table
        if(isset($_POST['Fileupload']))
        {
            $rnd = rand(0,9999).strtotime(date("Y-m-d H:i:s"));  // generate random number between 0-9999
            $model->attributes=$_POST['Fileupload'];
 
            $uploadedFile=CUploadedFile::getInstance($model,'original_filename');
            $fileName = "{$rnd}-{$uploadedFile}";  // random number + file name
            $model->generated_filename = $fileName;
 
            if($model->save())
            {
				$imagePath = $model->imagePath.$fileName;
                $uploadedFile->saveAs($imagePath);  // image will uplode to rootDirectory/fileupload/
				
				$thumb=Yii::app()->phpThumb->create($model->imagePath.$fileName);
				$thumb->resize(100,100);
				$thumb->save($model->imagePathThumb.$fileName);
				
				$customer->fileupload_id = $model->id;
				$customer->save(false);
				
                $this->redirect(array('/customer/data/update','id'=>$customer->id));
            }
        }
		
        $this->render('fileupload',array(
            'model'=>$model,
            'customer'=>$customer,
        ));
    }

	public function actionTiersJsonSearch($companyId)
	{
		
		$tiers = Tier::model()->byCompanyId($companyId)->findAll();
		
		$tiersArray = array();
		foreach($tiers as $tier)
		{
			$tiersArray[] = array(
				'id' => $tier->id, 
				'tier_name'=>$tier->tier_name
			);
		}
		
		echo CJSON::ENCODE($tiersArray);
	}
	
	public function actionTiersModalSearch($companyId)
	{
		$company = Company::model()->findByPk($companyId);
		
		$this->renderPartial('_tiersModalSearch',array(
			'company' => $company,
		),false,true);
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

	
	public function actionCheckPhoneTimeZone()
	{
		$phoneTimeZone = null;
		
		if(isset($_GET['phone_number']))
		{
			$phoneNumber = $_GET['phone_number'];
			$_phoneNumber = substr($phoneNumber, 1, 3);
			$phoneTimeZone = AreacodeTimezoneLookup::model()->getAreaCodeTimeZone($_phoneNumber, false);
		}
	
		$return = array();
		$return['items'] = AreacodeTimezoneLookup::items();
		$return['selected'] = $phoneTimeZone;
		
		echo CJSON::encode($return);
	}

	public function actionReleaseLock($id)
	{
		$model=$this->loadModel($id);
		
		if(isset($model->account))
		{
			$account = $model->account;
			$account->login_attempt = 0;
			$account->save(false);
			
			Yii::app()->user->setFlash('success', 'Customer account has been unlocked!');
			$this->redirect(array('data/update','id' => $model->id));
		}
	}
}
