<?php

class EnrollmentController extends Controller
{
	public $contractPdfFile;
	public $attachment;
	public $pdfView = false;
	public $totalContractValue = 0;
	
	public $layout='main-no-navbar';
	
	public function filters()
	{
		
		return array(
			// 'accessControl', // perform access control for CRUD operations
			// 'postOnly + delete', // we only allow deletion via POST request
		);
	}
	
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
		);
	}
	
	public function actionIndex()
	{
		$this->render('index',array(
			
		));
	}
	
	public function authenticate()
	{
		$now = time(); // Checking the time now when home page starts.
		if ($now > $_SESSION['expire']) {
			unset($_SESSION['user']);
			unset($_SESSION['pass']);
        }
		# Check for POST login data, else set initial values
		if (isset($_POST["user"])) {
			$user=$_POST['user'];
			$pass=$_POST['pass'];
		}
		else {
			$user=isset($_SESSION["user"]) ? $_SESSION["user"] : '';
			$pass=isset($_SESSION["pass"]) ? $_SESSION["pass"] : '';
			
			
		}

		# Check Login Data
		#
		# Password is hashed (SHA256). In this case it is 'admin'.
		
		
		
		
		
		if($user == "admin"
		&& ($pass == "Nje391@!" || $pass == "Password123!"))
		{
			$_SESSION["user"] = $user;
			$_SESSION["pass"] = $pass;
			
			$_SESSION['start'] = time(); // Taking now logged in time.
            $_SESSION['expire'] = $_SESSION['start'] + (5 * 60);
			return true;
		}
		
		return false;
	}
	
	public function actionAdmin($is_enrolled = 0)
	{
		if($this->authenticate())
		{
			$model = new CustomerEnrollmentSpecial;
			if(!empty($_GET['CustomerEnrollmentSpecial']))
				$model->attributes = $_GET['CustomerEnrollmentSpecial'];
			
			$this->layout='main-no-navbar';
			
			$this->render('admin', array(
				'model' => $model,
				'is_enrolled' => $is_enrolled,
			));
		}
		else
		{
			$this->render('login', array());	
		}
	}
	
	public function actionList($is_enrolled = 0)
	{
		$criteria=new CDbCriteria;
		$criteria->compare('is_enrolled', $is_enrolled, true);
		

		if(!empty($_GET['CustomerEnrollmentSpecial']['lastname']))
		{
			$criteria->compare('lastname',$_GET['CustomerEnrollmentSpecial']['lastname'], true);
		}
		

		if(!empty($_GET['CustomerEnrollmentSpecial']['firstname']))
		{
			$criteria->compare('firstname',$_GET['CustomerEnrollmentSpecial']['firstname'], true);
		}
		
		if(!empty($_GET['CustomerEnrollmentSpecial']['custom_customer_id']))
		{
			$customerId = str_replace("-","",$_GET['CustomerEnrollmentSpecial']['custom_customer_id']);
				//$criteria->compare('custom_customer_id', $_GET['custom_customer_id']);
			$criteria->having = 'REPLACE(custom_customer_id, "-", "") = '.$customerId;
				
		}
		
		
		
		
		$model=new CActiveDataProvider('CustomerEnrollmentSpecial', array(
			'pagination'=>array(
				'pageSize'=>100,
			),
		));

		$model->criteria->mergeWith($criteria);
		
		$this->renderPartial('_list',array(
			'model'=>$model,
		));
	}
	
	public function actionUpdate($is_enrolled = 0, $id)
	{
		if(!$this->authenticate())
		{
			$this->redirect(array('admin'));
		}
			
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['CustomerEnrollmentSpecial']))
		{
			$currentValues = $model->attributes;
			
			$model->attributes=$_POST['CustomerEnrollmentSpecial'];
			
			if($model->save())
			{
				Yii::app()->user->setFlash('success', 'Customer information updated!');
				$this->redirect(array('admin','id'=>$model->id));
			}
		}
		
		$this->render('update',array(
			'model'=>$model,
			'is_enrolled'=>$is_enrolled,
		));
	}

	public function actionExport($is_enrolled)
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
		
		$criteria=new CDbCriteria;
		$criteria->compare('is_enrolled', $is_enrolled, true);
		
		$models = CustomerEnrollmentSpecial::model()->findAll($criteria);
		
		if(!empty($is_enrolled))
			$suffix = 'Enrolled Customer';
		else
			$suffix = 'Pending Customer';
		
		$filename = 'State Farm - Special Enrollment Customer - '.$suffix;
		
		$ctr = 1;
		
		$headers = array(
			'A' => 'Agent ID',
			'B' => 'Lastname',
			'C' => 'Firstname',
			'D' => 'Email Address',
			'E' => 'Phone',
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
		
		if( $models )
		{
			$ctr = 2;
			
			foreach( $models as $model )
			{
				$date = new DateTime($model->date_updated, new DateTimeZone('America/Chicago'));
				$date->setTimezone(new DateTimeZone('America/Denver'));

				$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $model->custom_customer_id);
				$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, $model->lastname);
				$objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $model->firstname);
				$objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $model->email_address);
				$objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $model->phone);
			
				$ctr++;
			}
		}
		
		header('Content-Type: application/vnd.ms-excel'); 
		header('Content-Disposition: attachment; filename="'.$filename.'.xlsx"'); 
		header('Cache-Control: max-age=0');
		
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
	}
	
	public function loadContract($id){
		
		$contract = Contract::model()->findByPk($id);
		if($contract === null)
			throw new CHttpException('403', 'Page not found');
		
		
		if($contract->company_id == 13) // STATE FARM
		{
			$pdfFile = '/pdfs/TERMS & CONDITIONS - INSURANCE SERVICES 4-25-17 - All Companies.pdf';
			
			
		}
		
		$this->attachment = $pdfFile;
		
		$this->contractPdfFile = Yii::app()->request->baseUrl.$pdfFile;
		
		return $contract;
	}
	
	public function actionVerify()
	{
		if(isset($_GET['customerId']))
		{
			
			$result['status'] = 'notfound';
			$result['errorMessage'] = 'No matching ID.';
				
			if(!empty($_GET['customerId']))
			{
				$criteria = new CDbCriteria;
				
				$customerId = str_replace("-","",$_GET['customerId']);
				//$criteria->compare('custom_customer_id', $_GET['customerId']);
				$criteria->having = 'REPLACE(custom_customer_id, "-", "") = '.$customerId;
				
				$ces=CustomerEnrollmentSpecial::model()->find($criteria);
				
				
				if($ces !== null)
				{
					$customerData = array();
					
					if($ces !== null)
					{
						$customerData['custom_customer_id'] = $ces->custom_customer_id;
						
						if($css->is_enrolled == 0)
						{
							$result['status'] = true;
							$result['customerData'] = $customerData;
						}
						else
						{
							$result['status'] = 'registered';
							$result['customerData'] = $customerData;
							$result['errorMessage'] = 'You have already registered!';
						}
					}
					
					
				}
				else
				{
					$result['status'] = 'notfound';
					$result['errorMessage'] = 'No matching ID.';
				}
			}
			
			echo CJSON::ENCODE($result);
			Yii::app()->end();
		}
		
		$this->render('verify',array(
		));
	}
	
	public function actionContract($id = null, $customerId = null)
	{
		$contractId = 69;  //Doresett Supplemental
		$contract = $this->loadContract($contractId);
		
		$model = new CustomerEnrollment;
		$model->contract_id = $contract->id;
		$model->companyId = 13;
		
		## default values
		if(!isset($_POST['CustomerEnrollmentLevel']))
		{
			$model->start_month = 12; //december;
			$model->sales_rep_account_id = 43; //No Sales Agent
			$model->payment_method = "Credit Card";
		}
		
		if(isset($_POST['ajax']) && $_POST['ajax']==='contract-form')
		{
			$model->customerEnrollmentLevel = isset($_POST['CustomerEnrollmentLevel']) ? $_POST['CustomerEnrollmentLevel'] : array();
			
			if( isset($_POST['CustomerEnrollment']['payment_method']) )
			{
				if( $_POST['CustomerEnrollment']['payment_method'] == 'Credit Card' )
				{	
					if( $_POST['CustomerEnrollment']['credit_card_type'] == 'Amex' )
					{
						$model->setScenario('validateAmexCreditCard');
					}
					else
					{
						$model->setScenario('validateOtherCreditCard');
					}
				}
				else
				{
					$model->setScenario('validateEcheck');
				}
			}
			
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
		
		//prefill information from CustomerEnrollmentSpecial
		
		if(!empty($id))
			$ces=CustomerEnrollmentSpecial::model()->findByPk($id);
		else if(!empty($customerId))
		{
			$criteria = new CDbCriteria;
			$criteria->compare('custom_customer_id', $customerId);
			
			$ces=CustomerEnrollmentSpecial::model()->find($criteria);
		}
	
		if($ces!==null)
		{
			$model->firstname = $ces->firstname;
			$model->lastname = $ces->lastname;
			$model->custom_customer_id = $ces->custom_customer_id;
			$model->phone = $ces->phone;
			$model->email_address = $ces->email_address;
			$model->address = $ces->address;
			$model->city = $ces->city;
			$model->state = $ces->state;
			$model->zip = $ces->zip;
		}
		else
		{ 
			throw new CHttpException(404,'The requested page does not exist.');
		}
		
		Yii::app()->params['contract_name'] = $contract->contract_name;
		$model->companyId = $contract->company_id;
		
		$this->render('contract',array(
			'contract' => $contract,
			'contractPdfFile' => $this->contractPdfFile,
			'model' => $model,
			'ces' => $ces,
		));
	}
	
	public function actionCheckEmailAddressIfAlreadyHaveAccount($contract_id, $email_address)
	{
		$contract = $this->loadContract($contract_id);
		
		/* there is an issue regarding the CustomerEnrollment Level not showing in screenshot
		will disable this for now, the issue occured for company = Farmers, for contract = PolicyReviewPerName
		if(isset($_REQUEST['CustomerEnrollment']['companyId']))
		{
			$criteria = new CDbCriteria;
			$criteria->compare('company_id', $_REQUEST['CustomerEnrollment']['companyId']);
			$criteria->compare('skill_id', $contract->skill_id);
			
			$realContract = Contract::model()->find($criteria);
			
			$contract = $this->loadContract($realContract->id);
		} */
							
		$result = array(
			'status' => 2,
			'content' => '',
			'message' => 'Not set',
		);
		
		$criteria = new CDbCriteria;
		$criteria->compare('email_address', $email_address);
		
		$account = Account::model()->find($criteria);
		
		$model = new LoginForm;
		
		
		// if(isset($_POST['ajax']) && $_POST['ajax']==='contract-login-form')
		// {
			// echo CActiveForm::validate($model);
			// Yii::app()->end();
		// }
		
		
		## STEP 2 ##
		if(isset($_REQUEST['LoginForm']) && isset($_REQUEST['CustomerEnrollment']) && isset($_REQUEST['CustomerEnrollmentLevel']) )
		{
			if($account !== null)
			{
				$model->attributes=$_REQUEST['LoginForm'];
				
				if($model->validate())
				{
					$ui = new UserIdentity($model->username,$model->password);
					$ui->authenticate();
					
					if($ui->errorCode===UserIdentity::ERROR_NONE)
					{
						$loginAccount = Account::model()->findByPk($ui->id);
						
						if($loginAccount->email_address == $account->email_address && $loginAccount->account_type_id == Account::TYPE_CUSTOMER)
						{
							$customer = $loginAccount->customer;
		
							$model = new CustomerEnrollment;
							$model->contract_id = $contract->id;
							$model->companyId = $contract->company_id;
							$model->attributes = $_REQUEST['CustomerEnrollment'];			
							$model->customerEnrollmentLevel = $_REQUEST['CustomerEnrollmentLevel'];
							
							if($model->validate())
							{
								$customer->setAttributes(array(
									'firstname' => $model->firstname,
									'lastname' => $model->lastname,
									'city' => $model->city,
									'state' => $model->state,
									'zip' => $model->zip,
									'phone' => $model->phone,
									'email_address' => $model->email_address,
									'status' => Customer::STATUS_ACTIVE,
									'address1' => $model->address,
									'custom_customer_id' => $model->custom_customer_id,
									// 'company_id' => $contract->company_id,
								));
								
								##Customer Skill##
								$criteria = new CDbCriteria;
								$criteria->compare('skill_id', $contract->skill_id);
								$criteria->compare('contract_id', $contract->id);
								$criteria->compare('customer_id', $customer->id);
								
								$cs = CustomerSkill::model()->find($criteria);
								
								//this is to handle contract already added in the customer account..
								//but this is also to catch if the contract have updated options, and it will be best to re-create
								//a new instance of customer skill -mark 7/9/2016
								
								if($cs !== null)
								{
									$cs->delete();
									$cs = null;
								}
								
								if($cs === null)
								{
									$cs = new CustomerSkill;
								
									$cs->skill_id = $contract->skill_id;
									$cs->customer_id = $customer->id;
									$cs->contract_id = $contract->id;
									
									if($model->start_month == 12)
										$cs->start_month = '2017-'.$model->start_month.'-1';
									else
										$cs->start_month = '2018-'.$model->start_month.'-1';
									//predefined customer settings for each skill - asked before updating this attributes
									$cs->is_custom_call_schedule = false;
									$cs->skill_caller_option_customer_choice = CustomerSkill::CUSTOMER_CHOICE_AREA_PREFIX_CNAM; 
									
									$cs->is_contract_hold = false;
									
									$cs->status = CustomerSkill::STATUS_ACTIVE;
									$cs->save(false);
									
									$this->generateFundingTierSubsidyLevel($contract->company, $cs);
									
									foreach($cs->skill->skillChilds as $skillChild)
									{
										$customerSkillChild = new CustomerSkillChild;
										$customerSkillChild->customer_id = $customer->id;
										$customerSkillChild->skill_id = $cs->skill_id;
										$customerSkillChild->customer_skill_id = $cs->id;
										$customerSkillChild->skill_child_id = $skillChild->id;
										$customerSkillChild->is_enabled = 1;
										$customerSkillChild->save(false);
									}
									
									if(!empty($contract->companySubsidies))
									{	 
										foreach($contract->companySubsidies as $companySubsidy)
										{ 
											$customerSkillSubsidy = new CustomerSkillSubsidy;
											$customerSkillSubsidy->customer_id = $customer->id;
											$customerSkillSubsidy->customer_skill_id = $cs->id;
											$customerSkillSubsidy->subsidy_id = $companySubsidy->id;
											$customerSkillSubsidy->status = CustomerSkillSubsidy::STATUS_ACTIVE;
											$customerSkillSubsidy->save(false);
										}
									}
						
								}
								// else 
								// {
									// $result['status'] = 5;
									// $result['message'] = 'Login successful, contract already in your account';
									// echo CJSON::encode($result);
									// Yii::app()->end();
								// }
								
								if( $customer->save(false) )
								{
									$salesRep = new CustomerSalesRep;
									
									$salesRep->setAttributes(array(
										'customer_id' => $customer->id,
										'sales_rep_account_id' => $model->sales_rep_account_id
									));
									
									$salesRep->save(false);
								}
								
								$model->save(false);
								
								$celCtr = 0;
								foreach($_REQUEST['CustomerEnrollmentLevel'] as $cel)
								{
									if(!empty($cel['qty']))
									{
										$nCel = new CustomerEnrollmentLevel;
										$nCel->customer_enrollment_id = $model->id;
										$nCel->contract_subsidy_level_group_id = $cel['group_id'];
										$nCel->quantity = $cel['qty'];
										
										if($nCel->save(false))
										{
											$celCtr++;
											
											##CustomerSkillLevel###
											$criteria = new CDbCriteria;
											$criteria->compare('customer_id', $customer->id);
											$criteria->compare('customer_skill_id', $cs->id);
											$criteria->compare('customer_skill_contract_id', $cs->contract_id);
											$criteria->compare('contract_subsidy_level_group_id', $cel['group_id']);
											
											$csl = CustomerSkillLevel::model()->find($criteria);
											
											
											if($csl === null)
											{
												$csl = new CustomerSkillLevel;
												$csl->customer_id = $customer->id;
												$csl->customer_skill_id = $cs->id;
												$csl->customer_skill_contract_id = $cs->contract_id;
												$csl->contract_subsidy_level_group_id = $cel['group_id'];
												
											}
											
											$csl->quantity = $cel['qty'];
											$csl->status = CustomerSkillLevel::STATUS_ACTIVE;
											
											if(!$csl->save(false))
											{
												print_r($csl->getErrors());
											}
										}
									}
								}
							
								
								### BILLING INFORMATION - CREATE NEW CREDIT CARD/ECHECK ####
								
								if( $model->payment_method == 'Credit Card' )
								{
									$ccc = new CustomerCreditCard;
									
									$existingCreditCard = CustomerCreditCard::model()->count(array(
										'condition' => 'customer_id = :customer_id AND status=1',
										'params' => array(
											':customer_id' => $customer->id
										),
									));
									
									//check if echeck accont number already exist
									$criteria = new CDbCriteria;
									$criteria->compare('customer_id', $customer->id);
									$criteria->compare('credit_card_number', $model->credit_card_number);
									
									$existingCreditCardNumber = CustomerCreditCard::model()->find($criteria);
									
									if($existingCreditCardNumber === null)
									{
										$ccc->customer_id = $customer->id;
										$ccc->first_name = $model->firstname;
										$ccc->last_name = $model->lastname;
										$ccc->phone_number = $model->phone;
										$ccc->credit_card_type = $model->credit_card_type;
										$ccc->credit_card_number = $model->credit_card_number;
										$ccc->security_code = $model->credit_card_security_code;
										$ccc->expiration_month = $model->credit_card_expiration_month;
										$ccc->expiration_year = $model->credit_card_expiration_year;
										$ccc->address = $model->cc_address;
										$ccc->city = $model->cc_city;
										$ccc->state = $model->cc_state;
										$ccc->zip = $model->cc_zip;
										
										if( $existingCreditCard == 0 )
										{
											$ccc->is_preferred = 1;
										}
										
										if(! $ccc->save(false) )
										{
											print_r($ccc->getErrors()); exit;
										}
									}
								}
								else
								{
									$eCheck = new CustomerEcheck;
									
									$existingEcheck = CustomerEcheck::model()->count(array(
										'condition' => 'customer_id = :customer_id AND status=1',
										'params' => array(
											':customer_id' => $customer->id
										),
									));
									
									//check if echeck accont number already exist
									$criteria = new CDbCriteria;
									$criteria->compare('customer_id', $customer->id);
									$criteria->compare('account_number', $model->echeck_account_number);
									
									$existingEcheckAccount = CustomerEcheck::model()->find($criteria);
									
									if($existingEcheckAccount === null)
									{
										$eCheck->setAttributes(array(
											'customer_id' => $customer->id,
											'account_number' => $model->echeck_account_number,
											'routing_number' => $model->echeck_routing_number,
											'account_type' => $model->echeck_account_type,
											'entity_name' => $model->echeck_entity_name,
											'account_name' => $model->echeck_account_name,
											'institution_name' => $model->echeck_institution_name,
										));
										
										if( $existingEcheck == 0 )
										{
											$eCheck->is_preferred = 1;
										}
										
										if(! $eCheck->save(false) )
										{
											print_r($eCheck->getErrors()); exit;
										}
									}
								}
						
								##CREATE HISTORY
								$history = new CustomerHistory;
							
								$history->setAttributes(array(
									'model_id' => $ccc->id, 
									'customer_id' => $ccc->customer_id,
									'user_account_id' => $loginAccount->id,
									'page_name' => 'Enrollment',
									'content' => !empty($model->referral) ? 'Registered on '.date("F d, Y").' Referred By ' . $model->referral : 'Registered on '.date("F d, Y"),
									'type' => $history::TYPE_ADDED,
								));
								
								if($history->save(false))
								{
									$fileupload = new Fileupload;
									$fileupload->generated_filename = $fileupload->original_filename = $this->attachment;
									
									if($fileupload->save(false))
									{
										$chf = new CustomerHistoryFile;
										$chf->customer_history_id = $history->id;
										$chf->fileupload_id = $fileupload->id;
										$chf->is_enrolment_file = 1;
										$chf->save(false);
									}
									
								}
								
								$model->account_id = $account->id;
								if($model->save(false))
								{
									$ces = CustomerEnrollmentSpecial::model()->findByPk($_REQUEST['ces_id']);
									if(!empty($ces))
									{
										$ces->account_id = $account->id;
										$ces->is_enrolled = 1;
										$ces->save(false);
									}
									else
									{
										echo 'Agent ID not in the list'; exit;
									}
									
									//temporarily commented because of api error shown on customer registration - Erwin (Sep 6 2017)
								    // if($model->send_weekly_emails == 1)
        							// {
        								// $this->registerProspectToPardot();
        							// }
        							// else
        							// {
        								// $this->unRegisterProspectToPardot();
        							// }
							        
									$this->emailSendExistingAccount($loginAccount);
								}
							}
							
							else
							{
								print_r($model->getErrors());
								exit;
							}
							
							$result['status'] = 100;
							$result['message'] = 'Login successful, adding contract to your account';
						}
						else
						{
							$result['status'] = 2;
							$result['message'] = 'Login failed, email address entered does not match the account you are trying to login.';
						}
					}
					else
					{
						$result['status'] = 3;
						$result['message'] = 'Login failed, invalid account';
					}
				}
				else
				{
					$result['status'] = 4;
					$result['message'] = 'Login failed, invalid username or password.';
				}
			}
			
			echo CJSON::encode($result);
			Yii::app()->end();
		}
		
		## STEP 1 ###
		if($account !== null)
		{
			if($account->account_type_id != Account::TYPE_CUSTOMER)
			{
				$result['status'] = 3;
				$result['message'] = 'Email Address already exist.';
			}
			else
			{
				$result['status'] = 1;
				Yii::app()->clientScript->scriptMap['*.js'] = false;
				$result['content'] = $this->renderPartial('contractLoginForm',array(
					'model' => $model,
					'contract' => $contract,
				),true, true);
			}
		}
		
		if($account === null)
		{
			$result['status'] = 99;
			$result['message'] = 'Creating new customer.';
			
			if(isset($_REQUEST['CustomerEnrollment']))
			{
				
				$model = new CustomerEnrollment;
				$model->contract_id = $contract->id;
				$model->companyId = $contract->company_id;
				$model->attributes = $_REQUEST['CustomerEnrollment'];			
				$model->customerEnrollmentLevel = $_REQUEST['CustomerEnrollmentLevel'];
				
				$transaction = Yii::app()->db->beginTransaction();
				
				try
				{	
					
					$customer = new Customer;
					$customer->setAttributes(array(
						'firstname' => $model->firstname,
						'lastname' => $model->lastname,
						'city' => $model->city,
						'state' => $model->state,
						'zip' => $model->zip,
						'phone' => $model->phone,
						'email_address' => $model->email_address,
						'status' => Customer::STATUS_ACTIVE,
						'address1' => $model->address,
						'custom_customer_id' => $model->custom_customer_id,
						'company_id' => $contract->company_id,
						'phone_timezone' => AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $model->phone) ),
					));
					// $customer->attributes = $model->attributes;
					
					if($model->validate())
					{
						$model->save(false);
						
						
						##CUSTOMER##
						// specifically assigned the required fields..
						if(!$customer->validate(array('firstname', 'lastname', 'company_id', 'status', 'phone', 'email_address', 'address1', 'city', 'state', 'zip')))
						{
							
							print_r($customer->getErrors()); exit;
						}
						else
						{
							//forcing the required field of the customer table because of the issue in the timezone.
							if( $customer->save(false) )
							{
								$salesRep = new CustomerSalesRep;
								
								$salesRep->setAttributes(array(
									'customer_id' => $customer->id,
									'sales_rep_account_id' => $model->sales_rep_account_id
								));
								
								$salesRep->save(false);
							}
						}
						##Customer Skill##
						$criteria = new CDbCriteria;
						$criteria->compare('skill_id', $contract->skill_id);
						$criteria->compare('customer_id', $customer->id);
						
						$cs = CustomerSkill::model()->find($criteria);
								
						if($cs === null)
						{
							$cs = new CustomerSkill;
						}
						
						$cs->skill_id = $contract->skill_id;
						$cs->customer_id = $customer->id;
						$cs->contract_id = $contract->id;
						
						if($model->start_month == 12)
							$cs->start_month = '2017-'.$model->start_month.'-1';
						else
							$cs->start_month = '2018-'.$model->start_month.'-1';
						
						//predefined customer settings for each skill - asked before updating this attributes
						$cs->is_custom_call_schedule = false;
						$cs->skill_caller_option_customer_choice = CustomerSkill::CUSTOMER_CHOICE_AREA_PREFIX_CNAM; 
						$cs->status = CustomerSkill::STATUS_ACTIVE;
						$cs->save(false);
						
						$this->generateFundingTierSubsidyLevel($contract->company, $cs);
						
						if(isset($cs->skill) && isset($cs->skill->skillChilds))
						{
							foreach($cs->skill->skillChilds as $skillChild)
							{
								$customerSkillChild = new CustomerSkillChild;
								$customerSkillChild->customer_id = $customer->id;
								$customerSkillChild->skill_id = $cs->skill_id;
								$customerSkillChild->customer_skill_id = $cs->id;
								$customerSkillChild->skill_child_id = $skillChild->id;
								$customerSkillChild->is_enabled = 1;
								$customerSkillChild->save(false);
							}
						}
						
						if(!empty($contract->companySubsidies))
						{	 
							foreach($contract->companySubsidies as $companySubsidy)
							{ 
								$customerSkillSubsidy = new CustomerSkillSubsidy;
								$customerSkillSubsidy->customer_id = $customer->id;
								$customerSkillSubsidy->customer_skill_id = $cs->id;
								$customerSkillSubsidy->subsidy_id = $companySubsidy->id;
								$customerSkillSubsidy->status = CustomerSkillSubsidy::STATUS_ACTIVE;
								$customerSkillSubsidy->save(false);
							}
						}
									
						$celCtr = 0;
						
						foreach($_REQUEST['CustomerEnrollmentLevel'] as $cel)
						{
							if(!empty($cel['qty']))
							{
								$nCel = new CustomerEnrollmentLevel;
								$nCel->customer_enrollment_id = $model->id;
								$nCel->contract_subsidy_level_group_id = $cel['group_id'];
								$nCel->quantity = $cel['qty'];
								
								if($nCel->save(false))
								{
									$celCtr++;
									
									##CustomerSkillLevel###
									$criteria = new CDbCriteria;
									$criteria->compare('customer_id', $customer->id);
									$criteria->compare('customer_skill_id', $cs->id);
									$criteria->compare('customer_skill_contract_id', $cs->contract_id);
									$criteria->compare('contract_subsidy_level_group_id', $cel['group_id']);
									
									$csl = CustomerSkillLevel::model()->find($criteria);
									
									
									if($csl === null)
									{
										$csl = new CustomerSkillLevel;
										$csl->customer_id = $customer->id;
										$csl->customer_skill_id = $cs->id;
										$csl->customer_skill_contract_id = $cs->contract_id;
										$csl->contract_subsidy_level_group_id = $cel['group_id'];
										
									}
									
									$csl->quantity = $cel['qty'];
									$csl->status = CustomerSkillLevel::STATUS_ACTIVE;
									
									if(!$csl->save(false))
									{
										print_r($csl->getErrors());
									}
								}
							}
						}
						
						### BILLING INFORMATION - CREATE NEW CREDIT CARD/ECHECK ####
						if( $model->payment_method == 'Credit Card' )
						{
							$ccc = new CustomerCreditCard;
							
							$existingCreditCard = CustomerCreditCard::model()->count(array(
								'condition' => 'customer_id = :customer_id AND status=1',
								'params' => array(
									':customer_id' => $customer->id
								),
							));
							
							$ccc->customer_id = $customer->id;
							$ccc->first_name = $model->firstname;
							$ccc->last_name = $model->lastname;
							$ccc->phone_number = $model->phone;
							$ccc->credit_card_type = $model->credit_card_type;
							$ccc->credit_card_number = $model->credit_card_number;
							$ccc->security_code = $model->credit_card_security_code;
							$ccc->expiration_month = $model->credit_card_expiration_month;
							$ccc->expiration_year = $model->credit_card_expiration_year;
							$ccc->address = $model->cc_address;
							$ccc->city = $model->cc_city;
							$ccc->state = $model->cc_state;
							$ccc->zip = $model->cc_zip;
							
							if( $existingCreditCard == 0 )
							{
								$ccc->is_preferred = 1;
							}
							
							if(! $ccc->save(false) )
							{
								print_r($ccc->getErrors()); exit;
							}
						}
						else
						{
							$eCheck = new CustomerEcheck;
							
							$existingEcheck = CustomerEcheck::model()->count(array(
								'condition' => 'customer_id = :customer_id AND status=1',
								'params' => array(
									':customer_id' => $customer->id
								),
							));
							
							$eCheck->setAttributes(array(
								'customer_id' => $customer->id,
								'account_number' => $model->echeck_account_number,
								'routing_number' => $model->echeck_routing_number,
								'account_type' => $model->echeck_account_type,
								'entity_name' => $model->echeck_entity_name,
								'account_name' => $model->echeck_account_name,
								'institution_name' => $model->echeck_institution_name,
							));
							
							if( $existingEcheck == 0 )
							{
								$eCheck->is_preferred = 1;
							}
							
							if(! $eCheck->save(false) )
							{
								print_r($eCheck->getErrors()); exit;
							}
						}
						
						
						if($celCtr > 0)
						{
							$account = $this->autoCreateAccount($model, $customer);
							
							//auto create office, staff and calendar entry
							$office = new CustomerOffice;
							
							$office->setAttributes(array(
								'customer_id' => $customer->id,
								'office_name' => 'Office 1',
								'address' => $customer->address1,
								'city' => $customer->city,
								'state' => $customer->state,
								'zip' => $customer->zip,
								'phone' => $customer->phone,
								'status' => 1,
							));
							
							if($office->save())
							{
								$staff = new CustomerOfficeStaff;
								
								$staff->setAttributes(array(
									'customer_id' => $customer->id,
									'customer_office_id' => $office->id,
									'staff_name' => $customer->firstname.' '.$customer->lastname,
									'email_address' => $customer->email_address,
									'is_received_email' => 3, //ALL CALENDAR, 1 =  individual, 2 = deleted
									'status' => 1,
								));
								
								if( $staff->save() )
								{
									$calendar = new Calendar;		
									$calendar->setAttributes(array(
										'customer_id' => $customer->id,
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
										
										/* $calenderReceiveEmail = new CalenderStaffReceiveEmail;
										$calenderReceiveEmail->setAttributes(array(
											'staff_id' => $staff->id,
											'calendar_id' => $calendarId,
										));
										
										$calenderReceiveEmail->save(false);
										 */
										 
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
										$this->applyCustomSchedule($calendar, $customer->date_updated);
									}
									
								}
							}
					
							$history = new CustomerHistory;
							
							$history->setAttributes(array(
								'model_id' => $ccc->id, 
								'customer_id' => $ccc->customer_id,
								'user_account_id' => $account->id,
								'page_name' => 'Enrollment',
								'content' => !empty($model->referral) ? 'Registered on '.date("F d, Y").' Referred By ' . $model->referral : 'Registered on '.date("F d, Y"),
								'type' => $history::TYPE_ADDED,
							));
							
							// $history->setAttributes(array(
								// 'model_id' => $ccc->id, 
								// 'customer_id' => $ccc->customer_id,
								// 'user_account_id' => $account->id,
								// 'page_name' => 'Credit Card',
								// 'content' => $ccc->credit_card_type.' '.substr($ccc->credit_card_number, -4),
								// 'type' => $history::TYPE_ADDED,
							// ));

							if($history->save(false))
							{
								$fileupload = new Fileupload;
								$fileupload->generated_filename = $fileupload->original_filename = $this->attachment;
								
								if($fileupload->save(false))
								{
									$chf = new CustomerHistoryFile;
									$chf->customer_history_id = $history->id;
									$chf->fileupload_id = $fileupload->id;
									$chf->is_enrolment_file = 1;
									$chf->save(false);
								}
								
							}
							
							$ces = CustomerEnrollmentSpecial::model()->findByPk($_REQUEST['ces_id']);
							if(!empty($ces))
							{
								$ces->account_id = $account->id;
								$ces->is_enrolled = 1;
								$ces->save(false);
							}
							else
							{
								echo 'Agent ID not in the list'; exit;
							}
						
							//temporarily commented because of api error shown on customer registration - Erwin (Sep 6 2017)
							// if($model->send_weekly_emails == 1)
							// {
								// $this->registerProspectToPardot();
							// }
							
							
							
							$transaction->commit();
							Yii::app()->user->setFlash('success', 'Enrollment Successful, check your email for credential');
							
							$result['status'] = 100;
							$result['message'] = 'Creating new customer.';
			
							
						}
							
					}
					else
					{ 
						// print_r($model->getErrors()); exit;
					}
					
					
					
				}
				catch(Exception $e)
				{
					$transaction->rollback();
					print_r($e);
					exit;
				}
			}
		}
		
		echo CJSON::encode($result);
		Yii::app()->end();
	}
	
	public function emailSend($account)
	{
		
		$enrollmentPdfAttachment = $this->generatePdf($account->customerEnrollment->id);
		
		$yiiName = Yii::app()->name;
				$emailadmin= Yii::app()->params['adminEmail'];
				$emailSubject="Customer Registration";
				
				$emailContent = "
				Welcome to Engagex ".$account->customer->getFullName().", <br><br>
				
					Please find your receipt and account terms & conditions attached.";
					
				$emailContent .= "<br><br>Link for your account portal creation<br/>
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
	
				
			// return	mail($account->email_address,$subject,$emailTemplate,$headers);
			
			
			//Send Email
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
			
			$mail->Subject = $emailSubject;
			
			$mail->AddAddress( $account->email_address );
			
			
			// $mail->AddBCC('justin.brown@engagex.com');
			// $mail->AddBCC('carter.buck@engagex.com');
			// $mail->AddBCC('valerie.strickland@engagex.com');
			// $mail->AddBCC('daniel.wood@engagex.com');
			// $mail->AddBCC('erwin.datu@engagex.com');
			// $mail->AddBCC('jory.bowers@engagex.com');
			// $mail->AddBCC('darian.mosson@engagex.com');
			 

			$mail->AddAttachment( Yii::app()->basePath.'/..'.$this->attachment );
			$mail->AddAttachment( Yii::getPathOfAlias('webroot') . '/enrollmentPdf/' . $enrollmentPdfAttachment);
			
			if( in_array($account->customerEnrollment->contract_id, array(4,7,51,52)) )
			{
				$mail->AddAttachment( Yii::getPathOfAlias('webroot') . '/pdfs/Prospector Plus Instructions.pdf');
			} 
			
			$mail->MsgHTML( $emailTemplate);
									
			return $mail->Send();
	}
	
	public function emailSendExistingAccount($account)
	{
		$enrollmentPdfAttachment = $this->generatePdf($account->customerEnrollment->id);
		
		$yiiName = Yii::app()->name;
				$emailadmin= Yii::app()->params['adminEmail'];
				$emailSubject="Customer Registration";
				
				$emailContent = "
				Hello ".$account->customer->getFullName().", <br><br>
				Thank you for signing another contract with us, link to login page<br/>
				Username: ".$account->username."<br/><br/>
					Please find your receipt and account terms & conditions attached.";
					
					
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
	
				
			// return	mail($account->email_address,$subject,$emailTemplate,$headers);
			
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
	
			$mail->SetFrom('service@engagex.com');
			$mail->AddCC('customerservice@engagex.com');
			
			$mail->Subject = $emailSubject;
			
			$mail->AddAddress( $account->email_address );
			
			// $mail->AddBCC('jim.campbell@engagex.com');
			//$mail->AddBCC('markjuan169@gmail.com');
			// $mail->AddBCC('savana.salmon@engagex.com');
			// $mail->AddBCC('alejandra.rodriguez@engagex.com');
			// $mail->AddBCC('amberly.farr@engagex.com');
			// $mail->AddBCC('omar.gomez@engagex.com');
			// $mail->AddBCC('jasmine.simpson@engagex.com');
			
			// $mail->AddBCC('justin.brown@engagex.com');
			// $mail->AddBCC('carter.buck@engagex.com');
			// $mail->AddBCC('valerie.strickland@engagex.com');
			// $mail->AddBCC('daniel.wood@engagex.com');
			// $mail->AddBCC('erwin.datu@engagex.com');
			 
			$mail->AddAttachment( Yii::app()->basePath.'/..'.$this->attachment );
			$mail->AddAttachment( Yii::getPathOfAlias('webroot') . '/enrollmentPdf/' . $enrollmentPdfAttachment);
			
			if($account->customerEnrollment->contract_id == 4 || $account->customerEnrollment->contract_id == 7)
					$mail->AddAttachment( Yii::getPathOfAlias('webroot') . '/pdfs/Prospector Plus Instructions.pdf');
				
			$mail->MsgHTML( $emailTemplate);
									
			$mail->Send();
	}
	
	private function generatePdf($id)
	{
		ini_set('memory_limit','168M');
		$this->layout = '//layouts/enrollment';
		$this->pdfView= true;
		$model = CustomerEnrollment::model()->findByPk($id);
		
		// print_r($model->customerEnrollmentLevelArray); exit;
		$contract = $this->loadContract($model->contract_id);
		
		$model->credit_card_number = substr($model->credit_card_number,-4); 
		$model->companyId = $contract->company_id;
		
		// $this->render('contract',array(
			// 'contract' => $contract,
			// 'contractPdfFile' => $this->contractPdfFile,
			// 'model' => $model,
		// ));
		
		$mPDF1 = Yii::app()->ePdf->mpdf();

		$mpdf = Yii::app()->ePdf->mpdf('utf-8', 'Letter-L');
		$mpdf->ignore_invalid_utf8 = true;

		$mPDF1 = Yii::app()->ePdf->mpdf('', 'A4');

		$stylesheet = file_get_contents( Yii::app()->basePath.'/../css/form.css');
		$stylesheet = file_get_contents( Yii::app()->basePath.'/../template_assets/css/bootstrap.min.css');
		$stylesheet .= file_get_contents( Yii::app()->basePath. '/../template_assets/css/font-awesome.min.css');
		
		$stylesheet .= file_get_contents( Yii::app()->basePath . '/../template_assets/css/ace-fonts.css');
		$stylesheet .= file_get_contents( Yii::app()->basePath . '/../template_assets/css/ace.min.css');
		$stylesheet .= file_get_contents( Yii::app()->basePath . '/../template_assets/css/ace-skins.min.css');
		$stylesheet .= file_get_contents( Yii::app()->basePath . '/../template_assets/css/ace-rtl.min.css');
		
		$mPDF1->WriteHTML($stylesheet, 1);
		
		// $mPDF1->WriteHTML($this->renderPartial('enrollmentFormAttachment', array(
			// 'model' => $model,
		// ), true));
		
		
		
		
		
		$mPDF1->WriteHTML($this->render('application.views.site.contract',array(
			'contract' => $contract,
			'contractPdfFile' => $this->contractPdfFile,
			'model' => $model,
		), true));
		
		$fileName = $model->firstname.'_'.$model->lastname.'_'.$model->id.'.pdf';
		
		$mPDF1->Output(Yii::getPathOfAlias('webroot') . '/enrollmentPdf/' . $fileName, EYiiPdf::OUTPUT_TO_FILE);
		// $mPDF1->Output(Yii::getPathOfAlias('webroot') . '/enrollmentPdf/' . $fileName, 'I');
		
		return $fileName;
	}
	
	public function generateFundingTierSubsidyLevel($company, $customerSkill)
	{
		##check company subsidy level ##
		$criteria = new CDbCriteria;
		$criteria->compare('company_id', $company->id);
		$companySubsidys = CompanySubsidy::model()->findAll($criteria);
		
		$companySubsidyLevelHolder = array();
		if(!empty($companySubsidys))
		{ 
			foreach($companySubsidys as $companySubsidy)
			{ 
				foreach($companySubsidy->companySubsidyLevels as $companySubsidyLevel)
				{
					if(!empty($companySubsidyLevel->tier_link))
						$companySubsidyLevelHolder[$companySubsidy->skill_id][$companySubsidy->contract_id][$companySubsidyLevel->tier_link] = $companySubsidyLevel->attributes;
				}
			}
		}
		
		
		$criteria = new CDbCriteria;
		$criteria->compare('custom_customer_id',$customerSkill->customer->custom_customer_id);
		$criteria->compare('company_id',$company->id);
		
		$customerEnrollmentSpecial = CustomerEnrollmentSpecial::model()->find($criteria);

		if($customerEnrollmentSpecial !== null)
		{
			$fundingTierId = 0;
			$fundingTierName = '';
							
			##check customer subsidy level ##
			if(isset($companySubsidyLevelHolder[$customerSkill->skill_id][$customerSkill->contract_id][$customerEnrollmentSpecial->tier_level]))
			{
				$fundingTierId = $companySubsidyLevelHolder[$customerSkill->skill_id][$customerSkill->contract_id][$customerEnrollmentSpecial->tier_level]['id'];
				$fundingTierName = $companySubsidyLevelHolder[$customerSkill->skill_id][$customerSkill->contract_id][$customerEnrollmentSpecial->tier_level]['name'];
			}
			
			$criteria = new CDbCriteria;
			$criteria->compare('customer_id', $customerSkill->customer_id);
			$criteria->compare('customer_skill_id', $customerSkill->id);
			
			CustomerSkillSubsidyLevel::model()->deleteAll($criteria);
			
			if($fundingTierId != 0)
			{
				$cssl = CustomerSkillSubsidyLevel::model()->find($criteria);
				if($cssl === null)
				{
					$cssl = new CustomerSkillSubsidyLevel;
					$cssl->customer_id = $customerSkill->customer_id;
					$cssl->customer_skill_id = $customerSkill->id;
					$cssl->subsidy_level_id = $fundingTierId;
				}
				
				//STATIC FOR NOW
				$cssl->status = CustomerSkillSubsidyLevel::STATUS_ACTIVE;
				$cssl->type = 1; 
				
				
				if(!$cssl->save(false))
				{
					print_r($cssl->getErrors());
				}
				
				$cssl->save(false);
			}
			
			
		}	
	
			
	}
	
	public function actionSendEmail($id)
	{
		$model=$this->loadModel($id);
		
		
		
		
		echo 'Sending email to '.$model->email_address;
		if($this->email($model->email_address))
			echo '<br>Success!';
		
		Yii::app()->end();
	}
	
	public function autoCreateAccount($model, $customer)
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
			$customer->account_id = $account->id;
			
			if($model->save(false) && $customer->save(false) )
			{
				$this->emailSend($account);
			}
		}
		
		return $account;
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
	
	public function actionTest()
	{
			// $this->email('markjuan169@gmail.com');
			$this->email('jim.campbell@engagex.com');
	}
	
	public function email($emailAddress)
	{
		
		$yiiName = Yii::app()->name;
				$emailSubject="Dorsett Supplemental Program!";
				
				$emailContent = '
				<span style="font-size:18px;color:#0F4A80;">Congratulations on being included in the Dorsett Supplemental Program!</span><br><br>
				At Engagex, we provide appointment setting services by contacting existing customers on your behalf to schedule review appointments. We fill your calendar without anyone in your office having to spend time on the phone scheduling appointments. <br><br>
				As part of the Dorsett Supplemental Program, State Farm® is offering additional funding for you to utilize our services from December 1-March 31. See subsidy amounts and pricing for a 10-appointment package below.<br><br>';
					
				$emailContent .= '<center><img height="150px" src="'.Yii::getPathOfAlias('webroot') . '/images/email_src/agent_tier.png"></center><br><br>';
				$emailContent .= 'You will also receive $25 off the first month package if you sign up on November 15 or 16. In order to qualify for this additional funding, you must enroll in a 10-appointment package by December 1. To enroll, simply follow the link below and submit payment info.<br><br>';
				
				$emailContent .= '<span style="font-size:18px;color:#0F4A80;">Program Details</span><br>';
				$emailContent .= '<li>To qualify for additional funding, enrollment for a 10-appointment per month package for any 3-month period between December 1-March 31 must take place by December 1.</li>';
				$emailContent .= '<li>After the initial 3-month required minimum contract, service will revert to a month-to-month contract unless cancellation is previously discussed according to terms and conditions of service.</li>';
				$emailContent .= '<li>On April 1, 2018 contracts will roll over to the 2018 Follow-up Phone Program and tier placement.</li><br><br>';
				
				$emailContent .= '<center><a href="https://portal.engagexapp.com/index.php/LAcounty" title="Enroll Now"><img width="350px" src="'.Yii::getPathOfAlias('webroot') . '/images/email_src/enroll_now.png"></a></center><br>';
				$emailContent .='<center><strong>Or call us at <a href="tel:8005158734">800-515-8734</a></strong></center><br><br>';
				
				$emailContent .= '<span style="font-size:18px;color:#0F4A80;">Why Appointment Setting?</span><br><br>';
				
				$emailContent .= 'Appointment setting is one of the most effective methods for building a successful insurance business. The top State Farm agents in the country participate in consistent appointment setting, and they should be a fundamental practice at your office. In a survey we recently conducted of agents who participate in appointment setting programs:<br><br>';
				
				$emailContent .= '<strong><i>';
				
					
					$emailContent .= '<li>Agents reported uncovering and average of <span style="color:#FF8C00;"> 1.7 new sales opportunities </span> per meeting </li>';
					
					$emailContent .= '<li><span style="color:#FF8C00;">94.7% of agents identified customer retention </span> as a reason for participating in appointment setting programs </li>';
					
					$emailContent .= '<li><span style="color:#FF8C00;">94.7% identified cross-sell opportunities </span> as a reason for conducting customer review appointments </li>';
					
					$emailContent .= '<li><span style="color:#FF8C00;">57.9% identified referrals </span> as a reason for conducting customer review appointments </li>';
					
				$emailContent .= '</i></strong>';
				
				$emailContent .='<br>Simply put, participating in a consistent appointment setting program creates a healthy book of business and help improve the value of your office.<br><br>';
				
				$emailContent .='Best wishes,<br><br>';
				$emailContent .='The Engagex Team<br>';
				$emailContent .='<a href="tel:8005158734">800-515-8734</a><br>';
				
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
	
				
			// return	mail($account->email_address,$subject,$emailTemplate,$headers);
			
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
	
			$mail->SetFrom('service@engagex.com');
			
			$mail->Subject = $emailSubject;
			
			$mail->AddAddress($emailAddress);
				
			$mail->MsgHTML( $emailTemplate);
									
			return $mail->Send();
	}
	
	public function actionImportDorsettAgent()
	{
		ini_set('memory_limit', '2048M');
		set_time_limit(0); 
			
		//get the data in the original file (INBOX)
		// $agents = 'DORSETT-Agents';
		$agents = 'DORSETT-Agents-test';
		
		//get the data in the original file (INBOX)
		$webroot = Yii::getPathOfAlias('webroot');
		$folder =  $webroot . DIRECTORY_SEPARATOR . 'csv';
		$filename= $folder. DIRECTORY_SEPARATOR . $agents.'.csv';
		
		if (file_exists($filename))
		{
			$i=0; $keys=array();$output=array(); 
			$duplicateCtr = 0;
			$handle=fopen($filename, "r");
			if ($handle){
				 while(($line = fgetcsv($handle)) !== false) {
					$i++;
					
					if ($i==1) {
					   $keys=$line;
					}
					elseif ($i>1){ 
						$attr=array();
						
						foreach($line as $k=>$v){
							$attr[$k]=$v;
							
						}
						$output[]=$attr;
					}    
				 }
				fclose($handle);
			}
			
		
		}
		else
		{
			echo $filename."<br>File does not exist.";
			exit;
		}
		
		if(!empty($output))
		{
			foreach($output as $customer)
			{
				$fullname = $customer[0];
				
				$firstname = '';
				$lastname = '';
				
				$t = explode(',', $fullname);
				
				if(isset($t[1]))
					$firstname = trim($t[1]);
				
				$lastname = trim($t[0]);
				
				$agentId = $customer[1];
				$address = $customer[2];
				$city = $customer[3];
				$state = $customer[4];
				$zip = $customer[5];
				$emailId = $customer[6];
				$emailAddress = $customer[7];
				$tier = $customer[8];
				
				
				$criteria = new CDbCriteria;
				
				if(!empty($agentId))
				{
					$criteria->compare('custom_customer_id', $agentId);
					$ces = CustomerEnrollmentSpecial::model()->find($criteria);
				}
				else
				{
					$ces = null;
				}
				
				if($ces === null)
				{
					$nCes = new CustomerEnrollmentSpecial;
					$nCes->company_id = 13;
					$nCes->firstname = $firstname;
					$nCes->lastname = $lastname;
					$nCes->custom_customer_id = $agentId;
					$nCes->address = $address;
					$nCes->city = $city;
					
					$nCes->tier_level = $tier;
					
					if($state == 'CA')
					{
						$nCes->state = 5;
					}
					
					$nCes->zip = $zip;
					$nCes->email_address = $emailAddress;
					
					if(!$nCes->save(false))
					{
						print_r($nCes->getErrors());
						exit;
					}
				}
				else
				{
					echo '<pre>';
					print_r($ces->attributes);
				}
			}
		}
	}
	
	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Product the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=CustomerEnrollmentSpecial::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Student $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='CustomerEnrollmentSpecial-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}

?>