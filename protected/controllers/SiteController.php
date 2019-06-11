<?php

class SiteController extends Controller
{
	public $contractPdfFile;
	public $attachment;
	public $pdfView = false;
	public $totalContractValue = 0;
	public $list_id_newsletter = 2537;
	
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

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{
		$this->getUserHomeUrl();
		// $this->redirect(array('customer/data/index'));
		
		// $customers = Customer::model()->byStatus(Customer::STATUS_ACTIVE)->byIsDeletedNot()->findAll();
		
		// $this->render('index',array(
			// 'customers' => $customers,
		// ));
	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{ 
		if($error=Yii::app()->errorHandler->error)
		{
			if(Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', $error);
		}
	}

	/**
	 * Displays the contact page
	 */
	public function actionContact()
	{
		$model=new ContactForm;
		if(isset($_POST['ContactForm']))
		{
			$model->attributes=$_POST['ContactForm'];
			if($model->validate())
			{
				$name='=?UTF-8?B?'.base64_encode($model->name).'?=';
				$subject='=?UTF-8?B?'.base64_encode($model->subject).'?=';
				$headers="From: $name <{$model->email}>\r\n".
					"Reply-To: {$model->email}\r\n".
					"MIME-Version: 1.0\r\n".
					"Content-Type: text/plain; charset=UTF-8";

				mail(Yii::app()->params['adminEmail'],$subject,$model->body,$headers);
				Yii::app()->user->setFlash('contact','Thank you for contacting us. We will respond to you as soon as possible.');
				$this->refresh();
			}
		}
		$this->render('contact',array('model'=>$model));
	}

	public function actionAutoLogin($token)
	{
		$account = Account::model()->find(array(
			'condition'=>'login_token = :token',
			'params'=>array(
				':token' => $token,
			),
		));
		
		if($account !== null)
		{
			$account->login_token = null;
			$account->save(false);
			
			$_POST['LoginForm']['username'] = $account->username;
			$_POST['LoginForm']['password'] = $account->password;
			$_POST['customerHomepage'] = 1;
			
			$this->forward('site/login');
		}
		
		echo 'Login invalid.';
	}
	
	/**
	 * Displays the login page
	 */
	public function actionLogin()
	{
		$this->layout = '//layouts/login-main';
		$displayLockMessage = false;
		$lockMessage= '';
		
		$model=new LoginForm;

		// if it is ajax validation request
		if(isset($_POST['ajax']) && $_POST['ajax']==='login-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		// collect user input data
		if(isset($_POST['LoginForm']))
		{
			$model->attributes=$_POST['LoginForm'];
			
			$account = Account::model()->find(array(
				'condition' => 'LOWER(username=:username) AND status = 1',
				'params' => array(
					':username' => $model->username,
				),
			));
			
			
			if($account !== null && ($account->login_attempt > 5))
			{
				$displayLockMessage = true;
					$lockMessage = 'Sorry for the inconvenience your account has been locked because of multiple failed password attempts.<br> Please contact Customer Service 800-515-8734';
			
				
				if($account->account_type_id == Account::TYPE_AGENT || $account->account_type_id == Account::TYPE_CUSTOMER_OFFICE_STAFF)
					$lockMessage = 'Please contact your supervisor';
			}
			
			$securityGroups = Account::listAccountType();
				
			$securityGroupPermissionSwitch = AccountPermission::model()->find(array(
				'condition' => 'permission_key = :permission_key AND permission_type = :permission_type',
				'params' => array(
					':permission_key' => 'security_group_'.strtolower($securityGroups[$account->account_type_id]).'_master',
					':permission_type' => 'master_switch'
				),
			));
			
			$IPrestriction = AccountPermission::model()->find(array(
				'condition' => '
					account_type_id = :account_type_id
					AND permission_key = :permission_key
					AND permission_type = :permission_type
				',
				'params' => array(
					':account_type_id' => $account->account_type_id,
					':permission_key' => 'ip_restriction',
					':permission_type' => 'visible'
				),
			)); 
			 
			if( $securityGroupPermissionSwitch && $IPrestriction && !in_array($_SERVER['REMOTE_ADDR'], array('66.7.114.18', '66.7.114.19', '216.21.163.236', '23.228.170.26')) && in_array($_SERVER['SERVER_NAME'], array('system.engagexapp.com', 'portal.engagexapp.com', 'test.engagexapp.com')) )
			{
				Yii::app()->user->setFlash('danger', 'Invalid IP Address: ' . $_SERVER['REMOTE_ADDR']);
				$this->redirect(array('site/login'));
			}
			
			// validate user input and redirect to the previous page if valid
			if($model->validate() && $model->login())
			{
				$existingLoggedinRecord = AccountLoginTracker::model()->find(array(
					'condition' => 'account_id = :account_id AND time_out IS NULL',
					'params' => array(
						':account_id' => Yii::app()->user->account->id,
					),
					'order' => 'date_created DESC',
				));
				
				if( $existingLoggedinRecord )
				{
					$existingLoggedinRecord->time_out = date('Y-m-d H:i:s');

					if( $existingLoggedinRecord->save(false) )
					{
						Yii::app()->user->setFlash('danger', '<p><b>Notice:</b> Your previous session has been logged out.</p>');
					}
				}
				
				$daySchedule = AccountLoginSchedule::model()->find(array(
					'condition' => 'account_id = :account_id AND day_name = :day_name',
					'params' => array(
						':account_id' => Yii::app()->user->account->id,
						':day_name' => date('l'),
					),
					'order' => 'date_created ASC',
				));
			
				if( $daySchedule )
				{
					$status = time() > strtotime($daySchedule->start_time) ? 2 : 1;
				}
				else
				{
					$status = 3;
				}	
				
				if( in_array(Yii::app()->user->account->account_type_id, array(Account::TYPE_HOSTDIAL_AGENT, Account::TYPE_GAMING_PROJECT_MANAGER)) )
				{
					$status = 1;
				}
				
				$loginTracker = new AccountLoginTracker;
			
				$loginTracker->setAttributes(array(
					'account_id' => Yii::app()->user->account->id,
					'time_in' => date('Y-m-d H:i:s'),
					'status' => $status,
					'login_session_token' => sha1(time()),
				));
				
				if( $loginTracker->save(false) )
				{
					if( (isset(Yii::app()->user->account->accountUser) && Yii::app()->user->account->accountUser->salary_type == 'HOURLY') || Yii::app()->user->account->getIsHostDialer() )
					{
						$currentLoginState = AccountLoginState::model()->find(array(
							'condition' => 'account_id = :account_id AND end_time IS NULL',
							'params' => array(
								':account_id' => Yii::app()->user->account->id,
							),
							'order' => 'date_created DESC',
						));
						
						if( $currentLoginState )
						{
							$currentLoginState->end_time = date('Y-m-d H:i:s');
							$currentLoginState->save(false);
						}

						$loginState = new AccountLoginState;
						$loginState->setAttributes(array(
							'account_id' => Yii::app()->user->account->id,
							'start_time' => date('Y-m-d H:i:s'),
							'type' => AccountLoginState::TYPE_AVAILABLE,
						));
						
						$loginState->save(false);
					}
				}
				
				
				$this->getUserHomeUrl();
			}
		}
		
		// display the login form
		$this->render('login',array(
			'model'=>$model, 
			'displayLockMessage' => $displayLockMessage, 
			'lockMessage' => $lockMessage
		));
	}
	
	public function getUserHomeUrl()
	{
		if( !Yii::app()->user->isGuest )
		{		
			if( Yii::app()->user->account->account_type_id == Account::TYPE_AGENT || Yii::app()->user->account->account_type_id == Account::TYPE_GRATON_AGENT )
			{
				// $this->redirect(array('/agent'));
				$this->redirect(array('/news'));
			}
			elseif( Yii::app()->user->account->account_type_id == Account::TYPE_HOSTDIAL_AGENT )
			{
				if( Yii::app()->user->account->use_webphone == 1  )
				{
					$this->redirect(array('/agent/webphone'));
				}
				else
				{
					$this->redirect(array('/agent'));
				}
			}
			elseif( Yii::app()->user->account->account_type_id == Account::TYPE_GAMING_PROJECT_MANAGER )
			{
				$customerOfficeStaff = CustomerOfficeStaff::model()->find(array(
					'condition' => 'account_id = :account_id',
					'params' => array(
						':account_id' => Yii::app()->user->account->id
					),					
				));
				
				if( $customerOfficeStaff )
				{
					$this->redirect(array('hostDial/insight/index', 'customer_id'=>$customerOfficeStaff->customer_id));
				}
				else
				{
					$this->redirect(array('customer/data/index'));
				}
			}
			elseif( Yii::app()->user->account->account_type_id == Account::TYPE_COMPANY )
			{
				$this->redirect(array('/company'));
			}
			else
			{ 
				if(isset($_POST['customerHomepage']))
				{
					$this->redirect(array('customer/data/index'));
				}
				
				if( !empty(Yii::app()->user->returnUrl) && Yii::app()->user->returnUrl != Yii::app()->homeUrl )
				{
					$this->redirect(Yii::app()->user->returnUrl);
				}
				else
				{
					if( in_array(Yii::app()->user->account->account_type_id, array(Account::TYPE_PORTAL, Account::TYPE_CUSTOMER, Account::TYPE_CUSTOMER_OFFICE_STAFF)) )
					{
						if( Yii::app()->user->account->account_type_id == Account::TYPE_CUSTOMER)
						{
							$customer = Customer::model()->find(array(
								'condition' => 'account_id = :account_id',
								'params' => array(
									':account_id' => Yii::app()->user->account->id
								),
							));
						}
						
						if( Yii::app()->user->account->account_type_id == Account::TYPE_CUSTOMER_OFFICE_STAFF)
						{
							$staff = CustomerOfficeStaff::model()->find(array(
								'condition' => 'account_id = :account_id',
								'params' => array(
									':account_id' => Yii::app()->user->account->id
								),
							));
							
							if( $staff )
							{
								$customer = $staff->customer;
							}
						}
						
						if( $customer )
						{
							$customerPopupLoginCount = CustomerPopupLogin::model()->count(array(
								'condition' => '
									customer_id = :customer_id 
									AND company_id = :company_id 
									AND account_id = :account_id
								',
								'params' => array(
									'customer_id' => $customer->id,
									'company_id' => $customer->company_id,
									'account_id' => Yii::app()->user->account->id
								)
							));
							
							if( isset($customer->company) && $customer->company->popup_show == 1 && $customerPopupLoginCount < $customer->company->popup_logins )
							{
								$popupLogin = new CustomerPopupLogin;
								
								$popupLogin->setAttributes(array(
									'customer_id' => $customer->id,
									'company_id' => $customer->company_id,
									'account_id' => Yii::app()->user->account->id
								));
								
								$popupLogin->save(false);
								
								$this->redirect(array('customer/data/index','popup'=>1));
							}
							else
							{
								$this->redirect(array('customer/data/index'));
							}
						}
						else
						{
							$this->redirect(array('customer/data/index'));
						}
					}
					else
					{
						$this->redirect(array('/news'));
					}
				}
			}	
		}
		else
		{
			$this->redirect(array('site/login'));
		}
	}
	
	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
		if( !Yii::app()->user->isGuest )
		{
			$authAccount = Yii::app()->user->account;
			
			if( !in_array( $authAccount->account_type_id, array($authAccount::TYPE_CUSTOMER, $authAccount::TYPE_COMPANY, $authAccount::TYPE_CUSTOMER_OFFICE_STAFF, $authAccount::TYPE_HOSTDIAL_AGENT, $authAccount::TYPE_GAMING_PROJECT_MANAGER)) )
			{
				$model = AccountLoginTracker::model()->find(array(
					'condition' => 'account_id = :account_id AND time_out IS NULL',
					'params' => array(
						':account_id' => $authAccount->id,
					),
					'order' => 'date_created DESC',
				));
				
				$currentLoginState = AccountLoginState::model()->find(array(
					'condition' => 'account_id = :account_id AND end_time IS NULL',
					'params' => array(
						':account_id' => $authAccount->id,
					),
					'order' => 'date_created DESC',
				));
				
				if( isset($_GET['loginAuth']) && $_GET['loginAuth'] == 'expired' )
				{
					if( $model )
					{
						$logoutSchedule = AccountLoginSchedule::model()->find(array(
							'condition' => 'account_id = :account_id AND day_name = :day_name',
							'params' => array(
								':account_id' => $authAccount->id,
								':day_name' => date('l'),
							),
							'order' => 'date_created DESC',
						));
						
					
						if( $logoutSchedule )
						{
							if( empty($_POST['AccountLoginTracker']['[employee_note]']) )
							{
								$status = 1;
							}
							else
							{
								$status = time() < strtotime($logoutSchedule->end_time) ? 2 : 1;
							}
							
							// $status = time() < strtotime($logoutSchedule->end_time) ? 2 : 1;
						}
						else
						{
							$status = 3;
						}
						
						$model->time_out = date('Y-m-d H:i:s');
						$model->status = $status;
						
						if( $model->save(false) )
						{
							if( $currentLoginState )
							{
								$currentLoginState->end_time = date('Y-m-d H:i:s');
								
								$currentLoginState->save(false);
							}
							
							LeadHopper::model()->updateAll(array('agent_account_id' => null), 'type!=5 AND agent_account_id = ' . $authAccount->id);
							CustomerQueueViewer::model()->updateAll(array('dials_until_reset' => 20, 'call_agent'=>null), 'call_agent = ' . $authAccount->id);	
							
							Yii::app()->user->logout();
							$this->redirect(Yii::app()->homeUrl);
						}
					}
				}
				
				if( $model )
				{
					if( isset($_POST['AccountLoginTracker']) )
					{
						$model->attributes = $_POST['AccountLoginTracker'];
						
						$logoutSchedule = AccountLoginSchedule::model()->find(array(
							'condition' => 'account_id = :account_id AND day_name = :day_name',
							'params' => array(
								':account_id' => $authAccount->id,
								':day_name' => date('l'),
							),
							'order' => 'date_created DESC',
						));
						
					
						if( $logoutSchedule )
						{
							if( empty($_POST['AccountLoginTracker']['[employee_note]']) )
							{
								$status = 1;
							}
							else
							{
								$status = time() < strtotime($logoutSchedule->end_time) ? 2 : 1;
							}
							
							// $status = time() < strtotime($logoutSchedule->end_time) ? 2 : 1;
						}
						else
						{
							$status = 3;
						}
						
						$model->time_out = date('Y-m-d H:i:s');
						$model->status = $status;
						
						if( $model->save(false) )
						{
							if( $currentLoginState )
							{
								$currentLoginState->end_time = date('Y-m-d H:i:s');
								
								$currentLoginState->save(false);
							}
							
							LeadHopper::model()->updateAll(array('agent_account_id' => null), 'type!=5 AND agent_account_id = ' . $authAccount->id);
							CustomerQueueViewer::model()->updateAll(array('dials_until_reset' => 20, 'call_agent'=>null), 'call_agent = ' . $authAccount->id);	
							
							Yii::app()->user->logout();
							$this->redirect(Yii::app()->homeUrl);
						}
					}
					
					if( date('d') >= 1 && date('d') <= 15 )
					{
						$startDate = date('Y-m-1 00:00:00');
						$endDate = date('Y-m-15 23:59:59');
					}
					
					if( date('d') >= 16 && date('d') <= 31 )
					{
						$startDate = date('Y-m-16 00:00:00');
						$endDate = date('Y-m-t 23:59:59');
					}
					
					$payPeriods = AccountLoginTracker::model()->findAll(array(
						'condition' => 'account_id = :account_id AND time_in >= :start_date AND time_in <= :end_date AND status!=4',
						'params' => array(
							':account_id' => $authAccount->id,
							':start_date' => $startDate,
							':end_date' => $endDate,
						),
						'order' => 'time_in DESC',
					));
					
					$payPeriodDataProvider = new CArrayDataProvider($payPeriods, array(
						'pagination' => array(
							'pageSize' => 5,
						),
					));
					
					$this->layout = '//layouts/login-main';
					
					$this->render('logout', array(
						'authAccount' => $authAccount,
						'payPeriodDataProvider' => $payPeriodDataProvider,
						'model' => $model,
						'startDate' => $startDate,
						'endDate' => $endDate,
					));
				}
				else
				{
					Yii::app()->user->logout();
					$this->redirect(Yii::app()->homeUrl);
				}
			}
			else
			{	
				Yii::app()->user->logout();
				$this->redirect(Yii::app()->homeUrl);
			}
		}
		else
		{
			$this->redirect(Yii::app()->homeUrl);
		}
	}
	
	public function actionUpdateLoginState()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => '',
			'login_state_type' => '',
		);
		
		if( !Yii::app()->user->isGuest && isset($_POST['ajax']) && isset($_POST['type']) )
		{
			$authAccount = Yii::app()->user->account;
			
			$currentLoginState = AccountLoginState::model()->find(array(
				'condition' => 'account_id = :account_id AND end_time IS NULL',
				'params' => array(
					':account_id' => $authAccount->id,
				),
				'order' => 'date_created DESC',
			));
			
			
			if( $currentLoginState )
			{
				$currentLoginState->end_time = date('Y-m-d H:i:s');
				$currentLoginState->save(false);
			}
			
			$newLoginState = new AccountLoginState;
			
			$newLoginState->setAttributes(array(
				'account_id' => $authAccount->id,
				'start_time' => date('Y-m-d H:i:s'),
				'type' => $_POST['type'],
			));
			
			if( $newLoginState->save(false) )
			{
				if( $newLoginState->type == 1 )
				{
					$html = '<i class="ace-icon fa fa-circle light-green"></i>';
				}
				else
				{
					$html = '<i class="ace-icon fa fa-circle light-red"></i>';
				}
				
				$result['status'] = 'success';
				$result['message'] = 'Account login state has been updated.';
				$result['html'] = $html;
				
				if( $authAccount->account_type_id == Account::TYPE_AGENT || $authAccount->account_type_id == Account::TYPE_HOSTDIAL_AGENT )
				{
					$result['login_state_type'] = $newLoginState->type;
				}
				
				if( $_POST['type'] == 4 ) // LUNCH state
				{
					$currentLoginTracker = AccountLoginTracker::model()->find(array(
						'condition' => 'account_id = :account_id AND time_out IS NULL',
						'params' => array(
							':account_id' => $authAccount->id,
						),
						'order' => 'date_created DESC',
					));
					
					$currentLoginTracker->time_out = date('Y-m-d H:i:s');
					$currentLoginTracker->save(false);
				}
			}
		}
		
		echo json_encode($result);
	}
	

	public function actionRegister($token)
	{
		$account = $this->getToken(array('token'=>$token));
		
 		$account->setScenario('register');
		
		if(isset($_POST['Account']))
		{
			$account->attributes = $_POST['Account'];
			
			$validate = $account->validate();
			
			if($validate)
			{
				$transaction = Yii::app()->db->beginTransaction();
				
				try
				{
					$account->token = null;
					$account->status = Account::STATUS_ACTIVE;
					$account->save(false);
					
					$transaction->commit();
					
					$this->redirect(array('site/login'));
					
				}
				catch(Exception $e)
				{
					$transaction->rollback();
				}
					
			}
			// if($model->save())
				// $this->redirect(array('view','id'=>$model->id));
		}
		
		$this->render('register',array(
			'account' => $account,
		));
	}
	
	public function getToken($token)
    {
        $model= Account::model()->findByAttributes(array('token'=>$token));
		
        if($model===null)
            throw new CHttpException('403','The requested page does not exist.');
		
		if( strtotime($model->token_date) < strtotime(date("Y-m-d H:i:s")) )
			throw new CHttpException('403','The token used has already expired.');
		
		
		$model->setScenario('forgotPassword');
		
        return $model;
    }
 
 
	public function actionVerToken($token)
	{
		// $this->layout = '//layouts/main-no-navbar';
		$this->layout = '//layouts/login-main';
		
		$model=$this->getToken($token);
		if(isset($_POST['Ganti']))
		{
			if($model->token==$_POST['Ganti']['tokenhid']){
				
				
				$model->password= $_POST['Ganti']['password'];
				$model->confirmPassword= $_POST['Ganti']['confirmPassword'];

				if( $model->validate(array('password','confirmPassword')) )
				{
					if( in_array($model->account_type_id, array(Account::TYPE_CUSTOMER, Account::TYPE_CUSTOMER_OFFICE_STAFF)) )
					{
						$existingPassword = CustomerHistory::model()->find(array(
							'condition' => '
								customer_id = :customer_id
								AND user_account_id = :user_account_id
								AND old_data = :old_data
								AND page_name = :page_name
							',
							'params' => array(
								':customer_id' => $model->customer->id,
								':user_account_id' => $model->id,
								':old_data' => $_POST['Ganti']['confirmPassword'],
								':page_name' => 'Password',
							),
						));
					}
					else
					{
						$existingPassword = AccountUserNote::model()->find(array(
							'condition' => '
								account_id = :account_id
								AND old_data = :old_data
								AND category_id = :category_id
								AND content = :content
							',
							'params' => array(
								':account_id' => $model->id,
								':old_data' => $_POST['Ganti']['confirmPassword'],
								':category_id' => 10,
								':content' => 'Changed Password',
							),
						));
					}
						
					if( $existingPassword )
					{
						$errors .= '<li>Passwords cannot be reused and must be unique.</li>';
					}
					
					if( $errors == '' )
					{
						$model->date_last_password_change = date('Y-m-d H:i:s');
						$model->token=null;
						
						if( $model->save(false) )
						{
							//Add Audit Record
							if( in_array($model->account_type_id, array(Account::TYPE_CUSTOMER, Account::TYPE_CUSTOMER_OFFICE_STAFF)) )
							{
								$audit = new CustomerHistory;
								
								$audit->setAttributes(array(
									'model_id' => null, 
									'customer_id' => $model->customer->id,
									'user_account_id' => $model->id,
									'page_name' => 'Password',
									'old_data' => $_POST['Ganti']['password'],
									'new_data' => $_POST['Ganti']['confirmPassword'],
									'type' => $audit::TYPE_UPDATED,
								));

								$audit->save(false);
							}
							else
							{
								$audit = new AccountUserNote;
										
								$audit->setAttributes(array(
									'account_id' => $model->id,
									'account_user_id' => $model->accountUser->id,
									'content' => 'Changed Password',
									'old_data' => $_POST['Ganti']['password'],
									'new_data' => $_POST['Ganti']['confirmPassword'],
									'category_id' => 10,
								));

								$audit->save(false);
							}
						}
						
						Yii::app()->user->setFlash('success','<b>Password has been successfully changed, please login!</b>');
						$this->redirect(array('site/login'));
						$this->refresh();
					}
					else
					{
						$errorMessage = '<b>Please fix the following to continue: </b> <br /><br /> <ul>'.$errors.'</ul>';
						
						Yii::app()->user->setFlash('error', $errorMessage);
					}
				}
				
			}
		}
		
		$this->render('verification',array(
			'model'=>$model,
		));
	}
 
 
	public function actionForgot()
    {
		$this->layout = '//layouts/login-main';
		
		if(isset($_POST['Lupa']))
		{
			$getEmail=$_POST['Lupa']['email'];
			$getModel= Account::model()->findByAttributes(array('email_address'=>$getEmail));
			
			if($getModel === null)
			{
				Yii::app()->user->setFlash('danger','Email address not found.');
				$this->redirect(array('site/forgot'));
			}
			
			if(empty($getModel->username))
			{
				Yii::app()->user->setFlash('danger','Email address have not yet completed the account setup.');
				$this->redirect(array('site/forgot'));
			}
			
			$getToken=rand(0, 99999);
			$getTime=date("H:i:s");
			$getModel->token=md5($getToken.$getTime);
			$getModel->token_date = date("Y-m-d H:i:s", strtotime("+30 minutes"));
			
			$namaPengirim = Yii::app()->name;
			$emailadmin= Yii::app()->params['adminEmail'];
			$subjek="Reset Password";
			
			$emailContent = "You have requested to reset your password<br/>
			Username: ".$getModel->username."<br/>
				<a href='http://portal.engagexapp.com/index.php/site/verToken?token=".$getModel->token."'>Click Here to Reset Password</a>";
				
				
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
			
				
			if($getModel->save(false))
			{
				$name='=?UTF-8?B?'.base64_encode($namaPengirim).'?=';
				$subject='=?UTF-8?B?'.base64_encode($subjek).'?=';
				$headers="From: $name <{$emailadmin}>\r\n".
					"Reply-To: {$emailadmin}\r\n".
					"MIME-Version: 1.0\r\n".
					"Content-type: text/html; charset=UTF-8";

				// mail($getEmail,$subject,$emailTemplate,$headers);
				
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
				
				$mail->Subject = $subject;
				
				$mail->AddAddress( $getEmail );
				
				// $mail->AddBCC('jim.campbell@engagex.com');
				// $mail->AddBCC('markjuan169@gmail.com');
				 
				 
				$mail->MsgHTML( $emailTemplate);
										
				$mail->Send();
			
				Yii::app()->user->setFlash('success','Link to reset your password has been sent to your email');
				$this->redirect(array('site/login'));
			}
			else
			{
				print_r($getModel->getErrors());
				exit;
			}

		}
		
        $this->render('forgot');
    }

	
	public function actionDownload($file = null)
	{
		$account = null;
		
		if(!Yii::app()->user->isGuest)
			$authAccount = Yii::app()->user->account;
		
		if ($file == null)
		{
			throw new CHttpException(404,'The requested page does not exist.');
		}
		
		$extension = strtolower(substr(strrchr($file,"."),1));
		
		$explodedFile = explode('/',$file);
		
		if(isset($explodedFile[1]) && $explodedFile[1] == 'pdfs')
			$filePath = Yii::getPathOfAlias('webroot') . $file;
		else
			$filePath = Yii::getPathOfAlias('webroot') . '/fileupload/' . $file;
		
		$customerFileDownloadName = null;
		$allowDownload = false;
		
		if(file_exists($filePath))
		{
			$allowDownload = true;
		}
		else 
		{
			if(isset($_GET['customerFileId']))
			{ 
				$customerFile = CustomerFile::model()->findByPk($_GET['customerFileId']);
				if( $customerFile )
				{
					$file = $customerFile->fileUpload->generated_filename;
					
					$filePath = Yii::getPathOfAlias('webroot') . '/fileupload/import/' . $file;
					
					if(file_exists($filePath))
					{
						$allowDownload = true;
						$customerFileDownloadName = $customerFile->fileUpload->original_filename;
					}
				}
			}
			
			if(isset($_GET['customerHistoryFileId']))
			{ 
				$customerHistoryFile = CustomerHistoryFile::model()->findByPk($_GET['customerHistoryFileId']);
				if( $customerHistoryFile )
				{
					$file = $customerHistoryFile->fileUpload->generated_filename;
					
					$filePath = Yii::getPathOfAlias('webroot') . '/fileupload/import/' . $file;
					
					if(file_exists($filePath))
					{
						$allowDownload = true;
						$customerFileDownloadName = $customerHistoryFile->fileUpload->original_filename;
					}
				}
			}
			
			
			if(isset($_GET['customerReportId']))
			{ 
				$customerReport = CustomerReport::model()->findByPk($_GET['customerReportId']);
				if( $customerReport )
				{
					$file = $customerReport->fileUpload->generated_filename;
					
					$filePath = Yii::getPathOfAlias('webroot') . '/fileupload/report/' . $file;
					
					if(file_exists($filePath))
					{
						$allowDownload = true;
						$customerFileDownloadName = $customerReport->fileUpload->original_filename;
					}
				}
			}
			
			if(isset($_GET['isEnrolmentFile']))
			{
				$filePath = Yii::getPathOfAlias('webroot') . '/enrollmentPdf/' . $file;
				
				if(file_exists($filePath))
				{
					$allowDownload = true;
					$customerFileDownloadName = $file;
				}
			}
		}
		
		if ( $allowDownload )
		{
			if(isset($_GET['CompanyFile']))
			{
				$companyFile = CompanyFile::model()->findByPk($_GET['CompanyFile']['id']);
				
				if($companyFile !== null)
				{
					$history = new CompanyHistory;
					$history->setAttributes(array(
						'model_id' => $companyFile->id, 
						'company_id' => $companyFile->company_id,
						'user_account_id' => $authAccount->id,
						'page_name' => 'Company File',
						'type' => $history::TYPE_DOWNLOADED,
						'ip_address' => $_SERVER['REMOTE_ADDR'],
					));
					
					$history->save(false);
				}
			}
			
			if(isset($_GET['customerFileId']))
			{
				$customerFile = CustomerFile::model()->findByPk($_GET['customerFileId']);
				
				if( $customerFile )
				{
					$customerFile->is_new = 0;
					
					if( $customerFile->save(false) )
					{			
						$history = new CustomerHistory;
						$history->setAttributes(array(
							'model_id' => $customerFile->id, 
							'customer_id' => $customerFile->customer_id,
							'user_account_id' => $authAccount->id,
							'page_name' => 'Customer File',
							'type' => $history::TYPE_DOWNLOADED,
						));
					}
					
					$history->save(false);
				}
			}
			
			// required for IE
			if(ini_get('zlib.output_compression')) { ini_set('zlib.output_compression', 'Off');	}
			
			$ctype="application/force-download";
			
			header("Pragma: public"); 
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private",false); // required for certain browsers
			header("Content-Type: $ctype");

			# change, added quotes to allow spaces in filenames, 
			
			if($customerFileDownloadName !== null)
				header("Content-Disposition: attachment; filename=\"".basename($customerFileDownloadName)."\";" );
			else
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
	
	public function actionDownloadICS($calendarAppointmentId = null)
	{
		$account = null;
		
		if(!Yii::app()->user->isGuest)
			$authAccount = Yii::app()->user->account;
		
		if ($calendarAppointmentId == null)
		{
			throw new CHttpException(404,'The requested page does not exist.');
		}
		
		$calendarAppointment = CalendarAppointment::model()->findByPk($calendarAppointmentId);
		
		### ICAL ####
		## issue when the calendar is already updated, but the email link have accessed late 
		#if(isset($calendarAppointment) && $calendarAppointment->title == 'APPOINTMENT SET'/*  && $disposition->is_appointment_set == 1 */)
		
		if(isset($calendarAppointment))
		{	
			$customer = Customer::model()->findByPk($calendarAppointment->lead->customer_id);		
			
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

			// $message .= "--$mime_boundary\n";							
			// $message .= "Content-Type: text/calendar;name=\"meeting.ics\";method=REQUEST\n";
			// $message .= "Content-Transfer-Encoding: 8bit\n\n";
			// $message .= $ical;     
			
			// header('Content-type: text/calendar; charset=utf-8');
			// header('Content-Disposition: attachment; filename=meeting.ics');
			// echo $ical;
		}
		
		if(!empty($ical))
		{
			// required for IE
			if(ini_get('zlib.output_compression')) { ini_set('zlib.output_compression', 'Off');	}
			
			$ctype="application/force-download";
			
			header("Pragma: public"); 
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private",false); // required for certain browsers
			header('Content-Type: text/calendar; charset=utf-8');
			// header("Content-Type: $ctype");

			header('Content-Disposition: attachment; filename="'.$calendarAppointment->lead->getFullName().'.ics"');
			
			echo $ical;
			// header("Content-Transfer-Encoding: binary");
			// header("Content-Length: ".filesize($filePath));

			// readfile("$filePath");
		} 
		else
		{
			// Do processing for invalid/non existing files here
			echo 'File not found.';
		}
	}
	
	public function actionValidateLoginSession()
	{
		$result = array(
			'status' => '',
			'message' => '',
			'login_state_type' => '',
			'login_session_token' => '',
		);
		
		if( !Yii::app()->user->isGuest && isset($_POST['ajax']) && !isset($_POST['loginAuth']) )
		{
			$authAccount = Yii::app()->user->account;
			
			if( isset($authAccount->accountUser) && $authAccount->accountUser->salary_type == 'HOURLY' )
			{
				$currentLoginState = AccountLoginState::model()->find(array(
					'condition' => 'account_id = :account_id',
					'params' => array(
						':account_id' => $authAccount->id,
					),
					'order' => 'date_created DESC',
				));
				
				if( $currentLoginState )
				{
					$currentLoginState->date_updated = date('Y-m-d H:i:s');
					
					if( $currentLoginState->save(false) )
					{
						if( $authAccount->account_type_id == Account::TYPE_AGENT)
						{
							$result['login_state_type'] = $currentLoginState->type;
						}
					}
				}
				else
				{
					$newLoginState = new AccountLoginState;
					
					$newLoginState->account_id = $authAccount->id;
					$newLoginState->type = 1;
					$newLoginState->start_time = date;
					
					if( $newLoginState->save(false) )
					{
						if( $authAccount->account_type_id == Account::TYPE_AGENT)
						{
							$result['login_state_type'] = $newLoginState->type;
						}
					}
				}
			}
			
			
			$model = AccountLoginTracker::model()->find(array(
				// 'limit' => 1,
				// 'offset' => 1,
				'condition' => 'account_id = :account_id',
				'params' => array(
					':account_id' => $authAccount->id,
				),				
				'order' => 'date_created DESc'
			));
			
			if($model)
			{
				$result['status'] = 'success';
				$result['login_session_token'] = $model->login_session_token;
			}
		}
		
		echo json_encode($result);
	}

	public function loadContract($id){
		
		$contract = Contract::model()->findByPk($id);
		if($contract === null)
			throw new CHttpException('403', 'Page not found');
		
		
		$pdfFile = '/pdfs/TERMS & CONDITIONS - ALL SERVICES 11-20-15 - General.pdf';
		
		if($contract->company_id == 9) // FARM
		{
			$pdfFile = '/pdfs/TERMS & CONDITIONS - ALL SERVICES 11-20-15 - Farmers.pdf';
		}
		
		// if($contract->company_id == 4 || $contract->company_id == 7) // STATE FARM
		if($contract->company_id == 13) // STATE FARM
		{
			// $pdfFile = '/pdfs/TERMS & CONDITIONS - ALL SERVICES 11-20-15 - State Farm.pdf';
			// $pdfFile = '/pdfs/TERMS & CONDITIONS - ALL SERVICES 5-6-16 - State Farm.pdf';
			// $pdfFile = '/pdfs/TERMS & CONDITIONS - ALL SERVICES 5-18-16 - State Farm.pdf';
			// $pdfFile = '/pdfs/TERMS & CONDITIONS - ALL SERVICES 7-27-16 - All Companies.pdf';
			// $pdfFile = '/pdfs/TERMS & CONDITIONS - INSURANCE SERVICES 12-12-16 - All Companies.pdf';
			// $pdfFile = '/pdfs/TERMS & CONDITIONS - INSURANCE SERVICES 4-25-17 - All Companies.pdf';
			// $pdfFile = '/pdfs/TERMS & CONDITIONS - INSURANCE SERVICES 3-13-18 - All Companies.pdf';
			$pdfFile = '/pdfs/TERMS & CONDITIONS - INSURANCE SERVICES 5-10-18 - All Companies.pdf';
			
			##overwrite for WINBACK contract
			if($contract->id == 18 || $contract->skill_id == 24 ) //win-back
				$pdfFile = '/pdfs/TERMS & CONDITIONS - WinBack - 6-28-16.pdf';
		}
		
		##overwrite for Standard Policy Review skill contract
		if($contract->skill_id == 17)//Standard Policy Review
			// $pdfFile = '/pdfs/TERMS & CONDITIONS - ALL SERVICES 6-28-16 - All Companies.pdf';
			$pdfFile = '/pdfs/TERMS & CONDITIONS - INSURANCE SERVICES 5-10-18 - All Companies.pdf';
		
		##overwrite for Standard Policy Review Per Name = skill contract
		if($contract->skill_id == 15)//Standard Policy Review Per Name
			$pdfFile = '/pdfs/TERMS & CONDITIONS - ALL SERVICES 7-27-16 - All Companies.pdf';
			
		$this->contractPdfFile = Yii::app()->request->baseUrl.$pdfFile;
		
		// if($contract->company_id == 1) // GENERAL
		// {
			
		// }
		
		$this->attachment = $pdfFile;
		
		return $contract;
	}
	
	public function actionContract($id)
	{
		// $this->layout = '//layouts/main-no-navbar';
		
		$contract = $this->loadContract($id);
		
		$model = new CustomerEnrollment;
		$model->contract_id = $contract->id;
		
		if($contract->skill_id == 15)
		{
			Yii::app()->params['contract_name'] = $contract->skill->skill_name;
			Yii::app()->params['is_policy_review_per_name'] = true;
		}
		elseif($contract->skill_id == 24)
		{
			Yii::app()->params['contract_name'] = $contract->skill->skill_name;
			Yii::app()->params['is_win_back'] = true;
		}
		elseif($contract->skill_id == 17)
		{
			Yii::app()->params['contract_name'] = $contract->skill->skill_name;
			Yii::app()->params['is_policy_review'] = true;
		}
		else
		{
			Yii::app()->params['contract_name'] = $contract->contract_name;
			$model->companyId = $contract->company_id;
		}
		
		
		

		// echo '<pre>';
		// print_r($_POST); exit;
		
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
		
		$this->layout = '//layouts/enrollment';
		
		if($contract->skill_id == 15)//Standard Policy Review per Name
		{
			$criteria = new CDbCriteria;
			$criteria->compare('skill_id', 15); //Standard Policy Review per Name
			$contractCompanies = Contract::model()->findAll($criteria);
		
			$selectedContractCompany = '';
			
			$this->render('contractCompanyOption',array(
				'contract' => $contract,
				'contractPdfFile' => $this->contractPdfFile,
				'contractCompanies' => $contractCompanies,
				'selectedContractCompany' => $selectedContractCompany,
				'model' => $model,
			));
		}
		else if($contract->skill_id == 24)//Win-back
		{
			$criteria = new CDbCriteria;
			$criteria->compare('skill_id', 24); //Win-back
			$contractCompanies = Contract::model()->findAll($criteria);
		
			$selectedContractCompany = '';
			
			$this->render('contractCompanyOption',array(
				'contract' => $contract,
				'contractPdfFile' => $this->contractPdfFile,
				'contractCompanies' => $contractCompanies,
				'selectedContractCompany' => $selectedContractCompany,
				'model' => $model,
			));
		}
		elseif($contract->skill_id == 17)//Standard Policy Review
		{
			$criteria = new CDbCriteria;
			// $criteria->compare('skill_id', 17); //Standard Policy Review
			$criteria->condition = "company_id NOT IN(22) AND skill_id=17";
			$criteria->order = 'contract_name ASC';
			
			$contractCompanies = Contract::model()->findAll($criteria);
		
			$selectedContractCompany = '';
			
			$this->render('contractCompanyOption',array(
				'contract' => $contract,
				'contractPdfFile' => $this->contractPdfFile,
				'contractCompanies' => $contractCompanies,
				'selectedContractCompany' => $selectedContractCompany,
				'model' => $model,
			));
		}
		else
		{
			$this->render('contract',array(
				'contract' => $contract,
				'contractPdfFile' => $this->contractPdfFile,
				'model' => $model,
			));
		}
		
		
	}

	public function actionThankYou()
	{
		$this->render('thankyou',array(
		));
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
	
	public function autoCreateAccount($model, $customer)
	{
		$account = new Account;
		$account->email_address = $model->email_address;
		$account->account_type_id = Account::TYPE_CUSTOMER;
		
		$getToken=rand(0, 99999);
		$getTime=date("H:i:s");
		$account->token = md5($getToken.$getTime);
		$account->token_date = date("Y-m-d H:i:s", strtotime("+48 hours"));
		
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
	
	public function actionTest()
	{
		exit;
		$account = Account::model()->findByPk(5400);
		
		if($account !== null)
		{
			$email = $this->emailSend($account);
			
			var_dump($email); exit;
		}
		
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
			
			// $mail->AddBCC('jim.campbell@engagex.com');
			// $mail->AddBCC('markjuan169@gmail.com');
			// $mail->AddBCC('savana.salmon@engagex.com');
			// $mail->AddBCC('alejandra.rodriguez@engagex.com');
			// $mail->AddBCC('amberly.farr@engagex.com');
			// $mail->AddBCC('omar.gomez@engagex.com');
			// $mail->AddBCC('jasmine.simpson@engagex.com');
			
			// $mail->AddBCC('justin.brown@engagex.com');
			$mail->AddBCC('carter.buck@engagex.com');
			$mail->AddBCC('valerie.strickland@engagex.com');
			// $mail->AddBCC('ashley.paxton@engagex.com');
			// $mail->AddBCC('daniel.wood@engagex.com');
			$mail->AddBCC('erwin.datu@engagex.com');
			// $mail->AddBCC('alejandra.clark@engagex.com');
			// $mail->AddBCC('jory.bowers@engagex.com');
			$mail->AddBCC('darian.mosson@engagex.com');
			$mail->AddBCC('zach.guillaume@engagex.com');
			$mail->AddBCC('joel.wallin@engagex.com');
			$mail->AddBCC('caleb.hennen@engagex.com');
			 

			$mail->AddAttachment( Yii::app()->basePath.'/..'.$this->attachment );
			$mail->AddAttachment( Yii::getPathOfAlias('webroot') . '/enrollmentPdf/' . $enrollmentPdfAttachment);
			
			if( in_array($account->customerEnrollment->contract_id, array(4,7,51,52)) )
			{
				// $mail->AddAttachment( Yii::getPathOfAlias('webroot') . '/pdfs/Prospector Plus Instructions.pdf');
				$mail->AddAttachment( Yii::getPathOfAlias('webroot') . '/pdfs/ECRM Names List Instructions.pdf');
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
			// $mail->AddBCC('markjuan169@gmail.com');
			// $mail->AddBCC('savana.salmon@engagex.com');
			// $mail->AddBCC('alejandra.rodriguez@engagex.com');
			// $mail->AddBCC('amberly.farr@engagex.com');
			// $mail->AddBCC('omar.gomez@engagex.com');
			// $mail->AddBCC('jasmine.simpson@engagex.com');
			
			$mail->AddBCC('justin.brown@engagex.com');
			$mail->AddBCC('carter.buck@engagex.com');
			$mail->AddBCC('valerie.strickland@engagex.com');
			// $mail->AddBCC('ashley.paxton@engagex.com');
			$mail->AddBCC('daniel.wood@engagex.com');
			$mail->AddBCC('erwin.datu@engagex.com');
			// $mail->AddBCC('alejandra.clark@engagex.com');
			 
			$mail->AddAttachment( Yii::app()->basePath.'/..'.$this->attachment );
			$mail->AddAttachment( Yii::getPathOfAlias('webroot') . '/enrollmentPdf/' . $enrollmentPdfAttachment);
			
			if($account->customerEnrollment->contract_id == 4 || $account->customerEnrollment->contract_id == 7)
					$mail->AddAttachment( Yii::getPathOfAlias('webroot') . '/pdfs/Prospector Plus Instructions.pdf');
				
			$mail->MsgHTML( $emailTemplate);
									
			$mail->Send();
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
		$criteria->compare('agent_code',$customerSkill->customer->custom_customer_id);
		$criteria->compare('company_id',$company->id);
		$companyCustomerFundingTier = CompanyCustomerFundingTier::model()->find($criteria);
		
		if($companyCustomerFundingTier !== null)
		{
			$fundingTierId = 0;
			$fundingTierName = '';
							
			##check customer subsidy level ##
			if(isset($companySubsidyLevelHolder[$customerSkill->skill_id][$customerSkill->contract_id][$companyCustomerFundingTier->funding_tier]))
			{
				$fundingTierId = $companySubsidyLevelHolder[$customerSkill->skill_id][$customerSkill->contract_id][$companyCustomerFundingTier->funding_tier]['id'];
				$fundingTierName = $companySubsidyLevelHolder[$customerSkill->skill_id][$customerSkill->contract_id][$companyCustomerFundingTier->funding_tier]['name'];
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
	
	public function actionGetContractByCompany($company_id)
	{
		
		$company = Company::model()->findByPk($company_id);
		// $skill = Skill::model()->findByPk($skill_id);
		
		if($company === null)
			throw new CHttpException('403', 'Page not found');
		
		$criteria = new CDbCriteria;
		$criteria->compare('company_id', $company->id);
		// $criteria->compare('skill_id', $skill->id);
		$criteria->order = 'contract_name ASC';
		
		$contracts = Contract::model()->findAll($criteria);
		
		$contractList = array();
		foreach($contracts as $contract)
		{
			$contractList[$contract->id]['id'] = $contract->id;
			$contractList[$contract->id]['contract_name'] = $contract->contract_name;
		}
		
		echo CJSON::ENCODE($contractList);
		
		Yii::app()->end();
	}
	
	
	public function actionGetContractLevelById($contract_id)
	{
		$contract = Contract::model()->findByPk($contract_id);
		
		$this->renderPartial('_contractLevel', array(
			'contract'=>$contract,
		));
		
		Yii::app()->end();
	}

	public function actionGetCompanyCustomerFundingTierByAgentCode($company_id = null, $agent_code)
	{
		$company = Company::model()->findByPk($company_id);
		
		if($company !== null)
		{
			$criteria = new CDbCriteria;
			$criteria->compare('company_id', $company->id);
			$criteria->compare('agent_code', $agent_code);
			
			$ccft = CompanyCustomerFundingTier::model()->find($criteria);
			
			$result['status'] = false;
			$result['company_name'] = $company->company_name;
			$agentData = array();
			
			if($ccft !== null)
			{
				$agentData['agent_code'] = $ccft->agent_code;
				$agentData['agent_firstname'] = $ccft->agent_firstname;
				$agentData['agent_lastname'] = $ccft->agent_lastname;
				$agentData['funding_tier'] = $ccft->funding_tier;
				
				$result['status'] = true;
				$result['agent'] = $agentData;
			}
		}
		else
		{
			$result['status'] = 'notfound';
			$result['errorMessage'] = 'Must select a company first';
		}
		
		
		echo CJSON::ENCODE($result);
		
		Yii::app()->end();
	}
	
	public function actionUpdateContractCompanyOptionForm($company_id = null, $contract_id = null)
	{
		$company = Company::model()->findByPk($company_id);
		$contract = Contract::model()->findByPk($contract_id);
		$result = '';
		if($company !== null && $contract !== null)
		{
			$criteria = new CDbCriteria;
			$criteria->compare('company_id', $company->id);
			$criteria->compare('skill_id', $contract->skill_id);
			$criteria->order = 'date_created DESC';
			
			$realContract = Contract::model()->find($criteria);
			
			if($realContract !== null)
			{
				$result = $this->renderPartial('_contractLevel',array(
					'contract' => $realContract,
					'model' => new CustomerEnrollment,
				),true);
			}
			
		}
		
		echo CJSON::ENCODE($result);
		
		Yii::app()->end();
	}
	
	//For Testing only
	public function actionViewPdf($id)
	{
		$this->layout = '//layouts/enrollment';
		$this->pdfView= true;
		$model = CustomerEnrollment::model()->findByPk($id);
		
		// print_r($model->customerEnrollmentLevelArray); 
		
		// echo $model->customerEnrollmentLevelArray[415]['qty'];
		$contract = $this->loadContract($model->contract_id);
		$model->credit_card_number = substr($model->credit_card_number,-4); 
		
		// $this->render('contract',array(
			// 'contract' => $contract,
			// 'contractPdfFile' => $this->contractPdfFile,
			// 'model' => $model,
		// ));
		
		$mPDF1 = Yii::app()->ePdf->mpdf('', 'A4');

		$stylesheet = file_get_contents( Yii::app()->basePath.'/../css/form.css');
		$stylesheet = file_get_contents( Yii::app()->basePath.'/../template_assets/css/bootstrap.min.css');
		$stylesheet .= file_get_contents( Yii::app()->basePath. '/../template_assets/css/font-awesome.min.css');
		
		$stylesheet .= file_get_contents( Yii::app()->basePath . '/../template_assets/css/ace-fonts.css');
		$stylesheet .= file_get_contents( Yii::app()->basePath . '/../template_assets/css/ace.min.css');
		$stylesheet .= file_get_contents( Yii::app()->basePath . '/../template_assets/css/ace-skins.min.css');
		$stylesheet .= file_get_contents( Yii::app()->basePath . '/../template_assets/css/ace-rtl.min.css');
		
		$mPDF1->WriteHTML($stylesheet, 1);
		
		$mPDF1->WriteHTML($this->render('contract',array(
			'contract' => $contract,
			'contractPdfFile' => $this->contractPdfFile,
			'model' => $model,
		), true));
		
		$fileName = $model->firstname.'_'.$model->lastname.'_'.$model->id.'.pdf';
		
		$mPDF1->Output(Yii::getPathOfAlias('webroot') . '/enrollmentPdf/' . $fileName, 'I');
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
		
		
		
		
		if($contract->skill_id == 15)//Standard Policy Review per Name
		{
			$criteria = new CDbCriteria;
			$criteria->compare('skill_id', 15); //Standard Policy Review per Name
			$contractCompanies = Contract::model()->findAll($criteria);
		
			$selectedContractCompany = '';
			
			
			$mPDF1->WriteHTML($this->render('contractCompanyOption',array(
				'contract' => $contract,
				'contractPdfFile' => $this->contractPdfFile,
				'model' => $model,
				'contractCompanies' => $contractCompanies
			), true));
		}	
		else if($contract->skill_id == 24)//Win-back
		{
			$criteria = new CDbCriteria;
			$criteria->compare('skill_id', 24); //Win-back
			$contractCompanies = Contract::model()->findAll($criteria);
		
			$selectedContractCompany = '';
			
			
			$mPDF1->WriteHTML($this->render('contractCompanyOption',array(
				'contract' => $contract,
				'contractPdfFile' => $this->contractPdfFile,
				'model' => $model,
				'contractCompanies' => $contractCompanies
			), true));
		}	
		else if($contract->skill_id == 17)//Standard Policy Review
		{
			$criteria = new CDbCriteria;
			$criteria->compare('skill_id', 17); //Standard Policy Review
			$contractCompanies = Contract::model()->findAll($criteria);
		
			$selectedContractCompany = '';
			
			
			$mPDF1->WriteHTML($this->render('contractCompanyOption',array(
				'contract' => $contract,
				'contractPdfFile' => $this->contractPdfFile,
				'model' => $model,
				'contractCompanies' => $contractCompanies
			), true));
		}
		else
		{
			$mPDF1->WriteHTML($this->render('contract',array(
				'contract' => $contract,
				'contractPdfFile' => $this->contractPdfFile,
				'model' => $model,
			), true));
		}
		
		$fileName = $model->firstname.'_'.$model->lastname.'_'.$model->id.'.pdf';
		
		$mPDF1->Output(Yii::getPathOfAlias('webroot') . '/enrollmentPdf/' . $fileName, EYiiPdf::OUTPUT_TO_FILE);
		// $mPDF1->Output(Yii::getPathOfAlias('webroot') . '/enrollmentPdf/' . $fileName, 'I');
		
		return $fileName;
	}
		
	public function actionCustomerEnrollmentReport($customer_enrollment_id)
	{
		$customerEnrollment = CustomerEnrollment::model()->findByPk($customer_enrollment_id);
		
		Yii::import('ext.MYPDF');
		
		$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		spl_autoload_register(array('YiiBase','autoload'));
         
		
		// set default header data
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

		// remove default header/footer
		// $pdf->setPrintHeader(true);
		// $pdf->setPrintFooter(true);

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		// set paddings
		// $pdf->setCellPaddings(0,0,0,0);
		
		// set margins
		$pdf->SetMargins(20,40);
		$pdf->setHeaderMargin(10);
		$pdf->setFooterMargin(30);
		$pdf->SetAutoPageBreak(true, 40);


		//Set zoom 90%
		$pdf->SetDisplayMode(100,'SinglePage','UseNone');

		// set font
		$pdf->SetFont('freesans', '', 11);

		$pdf->AddPage();
		

		//Write the html
		$html = $this->renderPartial('customerEnrollmentReportLayout', array('customerEnrollment'=>$customerEnrollment), true);
		// $html = 'test';
		
		//Convert the Html to a pdf document
		$pdf->writeHTML($html, true, false, true, false, '');
		
		// reset pointer to the last page
		$pdf->lastPage();

		//Close and output PDF document
		$attach = $pdf->Output(Yii::getPathOfAlias('webroot') . '/enrollmentPdf/Test'. '.pdf', 'F');
		
		
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
		
		$mail->Subject = 'Test';
		$mail->AddAttachment(Yii::getPathOfAlias('webroot') . '/enrollmentPdf/Test'. '.pdf');
		
		// $mail->AddAddress( 'markjuan1');
		
		// $mail->AddBCC('jim.campbell@engagex.com');
		$mail->AddBCC('markjuan169@gmail.com');
		 
		// $mail->AddAttachment( $attach );
		 
		$mail->MsgHTML( 'test' );
								
		$mail->Send();
			
		Yii::app()->end();
	}
	
	/**
     * Call the Pardot API and get the raw XML response back
     *
     * @param string $url the full Pardot API URL to call, e.g. "https://pi.pardot.com/api/prospect/version/3/do/query"
     * @param array $data the data to send to the API - make sure to include your api_key and user_key for authentication
     * @param string $method the HTTP method, one of "GET", "POST", "DELETE"
     * @return string the raw XML response from the Pardot API
     * @throws Exception if we were unable to contact the Pardot API or something went wrong
     */
    public function callPardotApi($url, $data, $method = 'GET')
    {
        // build out the full url, with the query string attached.
        $queryString = http_build_query($data, null, '&');
        if (strpos($url, '?') !== false) {
            $url = $url . '&' . $queryString;
        } else {
            $url = $url . '?' . $queryString;
        }
    
        $curl_handle = curl_init($url);
    
        // wait 5 seconds to connect to the Pardot API, and 30
        // total seconds for everything to complete
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl_handle, CURLOPT_TIMEOUT, 30);
    
        // https only, please!
        curl_setopt($curl_handle, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
    
        // ALWAYS verify SSL - this should NEVER be changed. 2 = strict verify
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, 2);
        // curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, 0);
        // curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, 0);
    
        // return the result from the server as the return value of curl_exec instead of echoing it
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
    
    	
        if (strcasecmp($method, 'POST') === 0) {
    
            curl_setopt($curl_handle, CURLOPT_POST, true);
        } elseif (strcasecmp($method, 'GET') !== 0) {
            // perhaps a DELETE?
            curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        }
    
        $pardotApiResponse = curl_exec($curl_handle);
        if ($pardotApiResponse === false) {
    
            // failure - a timeout or other problem. depending on how you want to handle failures,
            // you may want to modify this code. Some folks might throw an exception here. Some might
            // log the error. May you want to return a value that signifies an error. The choice is yours!
    
            // let's see what went wrong -- first look at curl
            $humanReadableError = curl_error($curl_handle);
    
            // you can also get the HTTP response code
            $httpResponseCode = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
    
            // make sure to close your handle before you bug out!
            curl_close($curl_handle);
    
            throw new Exception("Unable to successfully complete Pardot API call to $url -- curl error: \"".
                                    "$humanReadableError\", HTTP response code was: $httpResponseCode");
        }
    
        // make sure to close your handle before you bug out!
        curl_close($curl_handle);
    
        return $pardotApiResponse;
    }
    
    public function callPardotApiProspects($url, $data, $prospects, $method = 'GET')
    {
        // build out the full url, with the query string attached.
        $queryString = http_build_query($data, null, '&');
        if (strpos($url, '?') !== false) {
            $url = $url . '&' . $queryString;
        } else {
            $url = $url . '?' . $queryString;
        }
    
        $curl_handle = curl_init($url);
    
        // wait 5 seconds to connect to the Pardot API, and 30
        // total seconds for everything to complete
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl_handle, CURLOPT_TIMEOUT, 30);
    
        // https only, please!
        curl_setopt($curl_handle, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
    
        // ALWAYS verify SSL - this should NEVER be changed. 2 = strict verify
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, 2);
        // curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, 0);
        // curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, 0);
    
        // return the result from the server as the return value of curl_exec instead of echoing it
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
    
    	
        if (strcasecmp($method, 'POST') === 0) {
    
            curl_setopt($curl_handle, CURLOPT_POST, true);
            curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $prospects);
        } elseif (strcasecmp($method, 'GET') !== 0) {
            // perhaps a DELETE?
            curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        }
    
        $pardotApiResponse = curl_exec($curl_handle);
        if ($pardotApiResponse === false) {
    
            // failure - a timeout or other problem. depending on how you want to handle failures,
            // you may want to modify this code. Some folks might throw an exception here. Some might
            // log the error. May you want to return a value that signifies an error. The choice is yours!
    
            // let's see what went wrong -- first look at curl
            $humanReadableError = curl_error($curl_handle);
    
            // you can also get the HTTP response code
            $httpResponseCode = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
    
            // make sure to close your handle before you bug out!
            curl_close($curl_handle);
    
            throw new Exception("Unable to successfully complete Pardot API call to $url -- curl error: \"".
                                    "$humanReadableError\", HTTP response code was: $httpResponseCode");
        }
    
        // make sure to close your handle before you bug out!
        curl_close($curl_handle);
    
        return $pardotApiResponse;
    }
    
    public function registerProspectToPardot(){
    
        $user_key = 'd37cbc8161e42ecfec393a5afe07967d';
    
        //trigger Authentication
        $return = $this->callPardotApi('https://pi.pardot.com/api/login/version/4',
        	array(
        		'email' => 'mitchell.hill@engagex.com',
        		'password' => '1860So!!',
        		'user_key' => $user_key,
        		'format' => 'json'
        	)
        );
        
        $jsonDecoded = json_decode($return, true);
        $api_key = $jsonDecoded['api_key'];
        
        
        $yourEmail = $_GET['CustomerEnrollment']['email_address'];
        $first_name = $_GET['CustomerEnrollment']['firstname'];
        $last_name = $_GET['CustomerEnrollment']['lastname'];
        $list_id = $this->list_id_newsletter;
    
    	if(!empty($yourEmail))
    	{
    		$url = 'https://pi.pardot.com/api/prospect/version/4/do/read';
    		$data = array(
    			'api_key' => $api_key,
    			'user_key' => $user_key,
    			'email' => $yourEmail,
    			'format' => 'json',
    		);
    
    		$return = $this->callPardotApi($url, $data); 
    		$jsonDecoded = json_decode($return, true);
    				
    		$prospectId = 0;
    		$customerPageUrl = '';
    		
    		if(!isset($jsonDecoded['err']))
    		{
    			//update prospect by using the prospect's ID to trigger update.
    			
    			if(!isset($jsonDecoded['prospect']['id']))
    			{
    				//the prospect have more than 1 campaign.
    				
    				foreach($jsonDecoded['prospect'] as $campaignProspect)
    				{
    					if(isset($campaignProspect['campaign_id']))
    						$prospectId = $campaignProspect['id'];
    						
    				}
    				
    				// if campaign is not in the array and prospect ID still in 0 create new prospect and assign it to with the list id
    				if($prospectId == 0)
    				{
    					//create new prospect
    					$jsonGroup['prospects'][] = array(
    						'email' => $yourEmail,
    						'first_name' => $first_name,
    						'last_name' => $last_name,
    						'list_'.$list_id => 1
    					);
    				}
    				else
    				{	
    					//update prospect
    					$jsonGroup['prospects'][] = array(
    						'id' => $prospectId,
    						'email' => $yourEmail,
    						'first_name' => $first_name,
    						'last_name' => $last_name,
    						'list_'.$list_id => 1
    					);
    				}
    				
    			
    			}
    			else
    			{
    				$prospectId = $jsonDecoded['prospect']['id'];
    				
    				$jsonGroup['prospects'][] = array(
    					'id' => $prospectId,
    					'email' => $yourEmail,
    					'first_name' => $first_name,
						'last_name' => $last_name,
						'list_'.$list_id => 1
    				);
    				
    			}
    			
    		}
    		else
    		{
    			//create new prospect
    			$jsonGroup['prospects'][] = array(
    				'email' => $yourEmail,
    				'first_name' => $first_name,
					'last_name' => $last_name,
					'list_'.$list_id => 1
    			);
    		}
    		
    		
    		
    		$jsonProspectGroup = json_encode($jsonGroup);
    		
    		//trigger API 
    		$url = 'https://pi.pardot.com/api/prospect/version/4/do/batchUpsert';
    
    		$data = array(
    			'api_key' => $api_key,
    			'user_key' => $user_key,
    			'format' => 'json'
    		);
    
    		$prospects = array(
    			'prospects' => $jsonProspectGroup
    		);
    
    		$return =  $this->callPardotApiProspects($url, $data, $prospects, 'POST');
    		$jsonDecoded = json_decode($return, true);
    		
    		if(isset($jsonDecoded['err']))
    		{
    			throw new Exception("Batch Upsert Error:".$jsonDecoded['err']);
    			break;
    		}
    		
    	}
    }
    
	public function unRegisterProspectToPardot(){
    
        $user_key = 'd37cbc8161e42ecfec393a5afe07967d';
    
        //trigger Authentication
        $return = $this->callPardotApi('https://pi.pardot.com/api/login/version/4',
        	array(
        		'email' => 'mitchell.hill@engagex.com',
        		'password' => '1860So!!',
        		'user_key' => $user_key,
        		'format' => 'json'
        	)
        );
        
        $jsonDecoded = json_decode($return, true);
        $api_key = $jsonDecoded['api_key'];
        
        
        $yourEmail = $_GET['CustomerEnrollment']['email_address'];
        $first_name = $_GET['CustomerEnrollment']['firstname'];
        $last_name = $_GET['CustomerEnrollment']['lastname'];
        $list_id = $this->list_id_newsletter;
    
    	if(!empty($yourEmail))
    	{
    		$url = 'https://pi.pardot.com/api/prospect/version/4/do/read';
    		$data = array(
    			'api_key' => $api_key,
    			'user_key' => $user_key,
    			'email' => $yourEmail,
    			'format' => 'json',
    		);
    
    		$return = $this->callPardotApi($url, $data); 
    		$jsonDecoded = json_decode($return, true);
    				
    		$prospectId = 0;
    		$customerPageUrl = '';
    		
    		if(!isset($jsonDecoded['err']))
    		{
    			//update prospect by using the prospect's ID to trigger update.
    			
    			if(!isset($jsonDecoded['prospect']['id']))
    			{
    				//the prospect have more than 1 campaign.
    				
    				foreach($jsonDecoded['prospect'] as $campaignProspect)
    				{
    					if(isset($campaignProspect['campaign_id']))
    						$prospectId = $campaignProspect['id'];
    						
    				}
    				
    				if($prospectId != 0)
    				{
    					//update prospect
    					$jsonGroup['prospects'][] = array(
    						'id' => $prospectId,
    						'email' => $yourEmail,
    						'list_'.$list_id => 0
    					);
    				}
    				
    			
    			}
    			else
    			{
    				$prospectId = $jsonDecoded['prospect']['id'];
    				
    				$jsonGroup['prospects'][] = array(
    					'id' => $prospectId,
    					'email' => $yourEmail,
    					'list_'.$list_id => 0
    				);
    				
    			}
    			
    		}
    		
    		
    		
    		$jsonProspectGroup = json_encode($jsonGroup);
    		
    		//trigger API 
    		$url = 'https://pi.pardot.com/api/prospect/version/4/do/batchUpsert';
    
    		$data = array(
    			'api_key' => $api_key,
    			'user_key' => $user_key,
    			'format' => 'json'
    		);
    
    		$prospects = array(
    			'prospects' => $jsonProspectGroup
    		);
    
    		$return =  $this->callPardotApiProspects($url, $data, $prospects, 'POST');
    		$jsonDecoded = json_decode($return, true);
    		
    		if(isset($jsonDecoded['err']))
    		{
    			throw new Exception("Batch Upsert Error:".$jsonDecoded['err']);
    			break;
    		}
    		
    	}
    }
	
    public function actionPardotTest()
    {
        $_GET['yourEmail'] = 'ilovefaye@gmail.com';
        $this->registerProspectToPardot();
    }
}