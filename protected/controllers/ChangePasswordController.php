<?php 

class ChangePasswordController extends Controller
{
	public function accessRules()
	{
		return array(
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('index'),
				'users'=>array('*'),
			),
		);
	}
	
	public function actionIndex()
	{
		$authAccount = Yii::app()->user->account;
		$authAccount->setscenario('changePassword');  
	
		if( isset($_POST['Account']) )
		{
			$errors = '';
			
			if( empty($_POST['Account']['password']) || empty($_POST['Account']['newpassword']) || empty($_POST['Account']['confirmpassword']) )
			{
				if( empty($_POST['Account']['password']) )
				{
					$errors .= '<li>Current Password is required.</li>';
				}
				else
				{
					if( $authAccount->password != $_POST['Account']['password'] )
					{
						$errors .= '<li>Invalid Current Password</li>';
					}
				}
				
				if( empty($_POST['Account']['newpassword']) )
				{
					$errors .= '<li>New Password is required.</li>';
				}
				
				if( empty($_POST['Account']['confirmpassword']) )
				{
					$errors .= '<li>Confirm Password is required.</li>';
				}	
			}
			else
			{				
				if( $authAccount->password != $_POST['Account']['password'] )
				{
					$errors .= '<li>Invalid Current Password</li>';
				}
				
				if( !Yii::app()->user->isGuest && !in_array(Yii::app()->user->account->account_type_id, array(Account::TYPE_COMPANY, Account::TYPE_CUSTOMER, Account::TYPE_CUSTOMER_OFFICE_STAFF, Account::TYPE_AGENT, Account::TYPE_COMPANY, Account::TYPE_HOSTDIAL_AGENT, Account::TYPE_GAMING_PROJECT_MANAGER)) )
				{
					if( strlen($_POST['Account']['newpassword']) < 8 )
					{
						$errors .= '<li>Password must be a minimum of 8 characters!</li>';
					}
					
					if( preg_match("/\d/", $_POST['Account']['newpassword']) == 0 )
					{
						$errors .= '<li>Password must contain atleast one number</li>';
					}
					
					if( preg_match('/[A-Z]/', $_POST['Account']['newpassword']) == 0 )
					{
						$errors .= '<li>Password must contain atleast one upper case letter</li>';
					}
					
					if( preg_match('/[^a-z0-9 _]+/i', $_POST['Account']['newpassword']) == 0 )
					{
						$errors .= '<li>Password must contain atleast one special character</li>';
					}
				}
				else
				{
					if( strlen($_POST['Account']['newpassword']) < 6 )
					{
						$errors .= '<li>Password must be a minimum of 6 characters</li>';
					}
				}
				
				if( $_POST['Account']['newpassword'] != $_POST['Account']['confirmpassword'] )
				{
					$errors .= '<li>Password do not match</li>';
				}
				else
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
								':customer_id' => $authAccount->customer->id,
								':user_account_id' => $authAccount->id,
								':old_data' => $_POST['Account']['confirmpassword'],
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
								':account_id' => $authAccount->id,
								':old_data' => $_POST['Account']['confirmpassword'],
								':category_id' => 10,
								':content' => 'Changed Password',
							),
						));
					}
						
					if( $existingPassword || $authAccount->password == $_POST['Account']['confirmpassword'] )
					{
						$errors .= '<li>Passwords cannot be reused and must be unique.</li>';
					}
				}
			}
			
			// exit;
			
			if( $errors == '' )
			{
				$authAccount->password = $_POST['Account']['confirmpassword'];
				$authAccount->date_last_password_change = date('Y-m-d H:i:s');
				
				if( $authAccount->save(false) )
				{
					//Add Audit Record
					if( in_array($authAccount->account_type_id, array(Account::TYPE_CUSTOMER, Account::TYPE_CUSTOMER_OFFICE_STAFF)) )
					{
						$audit = new CustomerHistory;
						
						$audit->setAttributes(array(
							'model_id' => null, 
							'customer_id' => $authAccount->customer->id,
							'user_account_id' => $authAccount->id,
							'page_name' => 'Password',
							'old_data' => $_POST['Account']['password'],
							'new_data' => $_POST['Account']['confirmpassword'],
							'type' => $audit::TYPE_UPDATED,
						));

						$audit->save(false);
					}
					else
					{
						$audit = new AccountUserNote;
								
						$audit->setAttributes(array(
							'account_id' => $authAccount->id,
							'account_user_id' => $authAccount->accountUser->id,
							'content' => 'Changed Password',
							'old_data' => $_POST['Account']['password'],
							'new_data' => $_POST['Account']['confirmpassword'],
							'category_id' => 10,
						));

						$audit->save(false);
					}
					
					//Send Email
					Yii::import('application.extensions.phpmailer.JPhpMailer');

					$mail = new JPhpMailer;
					$mail->SMTPAuth = true;		
					$mail->SMTPSecure = 'tls';   		
					$mail->SMTPDebug = 2; 
					$mail->Port = 25;      
					$mail->Host = 'mail.engagex.com';	
					$mail->Username = 'service@engagex.com';  
					$mail->Password = "_T*8c>ja";  

					$mail->SetFrom('service@engagex.com', 'Engagex Service', 0);
					
					$mail->AddReplyTo('customerservice@engagex.com', 'Engagex Customer Service');

					$mail->Subject = 'Account Password Change';
					
					$mail->MsgHTML('
						<p>This email is to inform you that your account password has been changed.</p>
						<p>If you did not initiate this password change please contact <a href="mailto:customerservice@engagex.com">customerservice@engagex.com</a></p> 
						<p>Thank you</p>
					');
					
					$mail->AddAddress($authAccount->email_address);					
					$mail->AddBCC('erwin.datu@engagex.com');					
					$mail->AddCC('service@engagex.com');
					$mail->Send();	
					
					
					$status = 'success';
					$message = 'Password was successfully changed.';
				}
				else
				{
					$status = 'danger';
					$message = 'Database Error.';
				}
				
				Yii::app()->user->setFlash($status, $message);
				// $this->redirect(array('changePassword/index'));
				$this->redirect(array('site/logout'));
			}
			else
			{
				$status = 'danger';
				$message = '<b>Please fix the following to continue: </b> <br /><br /> <ul>'.$errors.'</ul>';
				
				Yii::app()->user->setFlash($status, $message);
			}
		}
		
		$this->render('index', array(
			'authAccount' => $authAccount,
		));
	}
}

?>