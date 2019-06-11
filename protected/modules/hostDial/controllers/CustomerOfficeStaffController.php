<?php

class CustomerOfficeStaffController extends Controller
{

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$this->redirect(array('update','id' => $id));
		
		$this->render('view',array(
			'model'=>$this->loadModel($id),
		));
	}

	public function autoCreateAccount($model)
	{
		$account = new Account;
		$account->email_address = $model->email_address;
		$account->account_type_id = Account::TYPE_CUSTOMER_OFFICE_STAFF;
		
		$getToken=rand(0, 99999);
		$getTime=date("H:i:s");
		$account->token = md5($getToken.$getTime);
		$account->token_date = date("Y-m-d H:i:s", strtotime("+3 days"));
		
		if($account->save(false))
		{
			$model->account_id = $account->id;
			if($model->save(false))
			{
				if( $model->is_portal_access == 1 )
				{
					$this->emailSend($account);
				}
			}
		}
		
		return $account;
	}
	
	public function actionRegenerateToken($id)
	{
		$result = array(
			'status' => 'success',
			'message' => '',
			'html' => '',
		);
		
		$model = $this->loadModel($id);
		
		if($model->account)
		{
			$account = $model->account;
			
			$getToken=rand(0, 99999);
			$getTime=date("H:i:s");
			$account->token = md5($getToken.$getTime);
			$account->token_date = date("Y-m-d H:i:s", strtotime("+30 mins"));
			
			if( $account->save(false) )
				$this->emailSend($account);
		
		}
		else
		{
			$account = $this->autoCreateAccount($model);
		}

		
		echo json_encode($result);
		Yii::app()->end();
			
		// $this->redirect(array('data/update','id' => $account->id));
	}
	
	public function emailSend($account)
	{
		$yiiName = Yii::app()->name;
				$emailadmin= Yii::app()->params['adminEmail'];
				$emailSubject="Customer Office Staff Registration";
				
				$emailContent = "Link for your account portal creation<br/>
				Email Address: ".$account->email_address."<br/>
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
				
				
				// $name='=?UTF-8?B?'.base64_encode($yiiName).'?=';
				// $subject='=?UTF-8?B?'.base64_encode($emailSubject).'?=';
				// $headers="From: $name <{$emailadmin}>\r\n".
					// "Reply-To: {$emailadmin}\r\n".
					// "MIME-Version: 1.0\r\n".
					// "Content-type: text/html; charset=UTF-8";
				
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
			
			$mail->AddBCC('erwin.datu@engagex.com');
			// $mail->AddBCC('jim.campbell@engagex.com');
			// $mail->AddBCC('markjuan169@gmail.com');
			// $mail->AddBCC('justin.brown@engagex.com');
			// $mail->AddBCC('savana.salmon@engagex.com');
			// $mail->AddBCC('alejandra.rodriguez@engagex.com');
			// $mail->AddBCC('amberly.farr@engagex.com');
			// $mail->AddBCC('omar.gomez@engagex.com');
			// $mail->AddBCC('carter.buck@engagex.com');
			// $mail->AddBCC('jasmine.simpson@engagex.com');
			 
				
			$mail->MsgHTML( $emailTemplate);
									
			$mail->Send();
	}
	
	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate($customer_id = null, $customer_office_id = null)
	{
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => $html,
		);
		
		$authAccount = Yii::app()->user->account;
		
		$model = new CustomerOfficeStaff;
		$model->customer_id = isset($_POST['customer_id']) ? $_POST['customer_id'] : $customer_id;
		$model->customer_office_id = isset($_POST['customer_office_id']) ? $_POST['customer_office_id'] : $customer_office_id;
		
		 
		$account = new Account;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);
		if( isset($_POST['CustomerOfficeStaff']) )
		{
			$model->attributes = $_POST['CustomerOfficeStaff'];
			$model->status = 1;
			
			if( !empty($_POST['Account']['username']) )
			{
				$existingUsername = Account::model()->find(array(
					'condition' => 'username = :username',
					'params' => array(
						':username' => $_POST['Account']['username']
					),
				));
				
				if( $existingUsername )
				{
					$result['message'] = 'Username is in use.';
					
					echo json_encode($result);
					Yii::app()->end();
				}
			}
			
			if( $model->save() )
			{
				$account->attributes = $_POST['Account'];
				$account->email_address = $model->email_address;
				
				$getToken = rand(0, 99999);
				$getTime = date("H:i:s");
				$account->token = md5($getToken.$getTime);
				$account->token_date = date("Y-m-d H:i:s", strtotime("+3 days"));
				$account->date_last_password_change = date('Y-m-d H:i:s');
				$account->status = 1;
				
				if( $account->save(false) )
				{
					$model->account_id = $account->id;
					$model->save(false);
					
					CustomerAccountPermission::autoAddPermissionKey($account);
				}
				
				if( $account->account_type_id == Account::TYPE_HOSTDIAL_AGENT )
				{
					//auto assign customer for dialing
					$assignedCustomers = new AccountCustomerAssigned;
					
					$assignedCustomers->setAttributes(array(
						'account_id' => $account->id,
						'customer_id' => $model->customer_id,
						'company_id' => $model->customer->company_id,
					));
					
					$assignedCustomers->save(false);
					
					//auto assign English language
					$assignedLanguage = new AccountLanguageAssigned;
				
					$assignedLanguage->setAttributes(array(
						'account_id' => $account->id,
						'language_id' => 1,
					));
					
					$assignedLanguage->save(false);
				}
				
				//audit record
				$history = new CustomerHistory;
				
				$history->setAttributes(array(
					'model_id' => $model->id, 
					'customer_id' => $model->customer_id,
					'user_account_id' => $authAccount->id,
					'page_name' => 'Staff',
					'content' => $model->staff_name,
					'type' => $history::TYPE_ADDED,
				));

				$history->save(false);
				
				
				if( isset($_POST['ajax']) )
				{
					$models = CustomerOfficeStaff::model()->findAll(array(
						'condition' => 'customer_id = :customer_id AND is_deleted=0',
						'params' => array(
							':customer_id' => $model->customer_id,
						),
					));
					
					if($models)
					{
						foreach( $models as $model )
						{
							if( !empty($model->account_id) )
							{
								$hasCalendarAssigned = CalendarStaffAssignment::model()->count(array(
									'condition' => 'staff_id = :staff_id',
									'params' => array(
										':staff_id' => $model->id,
									),
								));
																	
								$html .= '<tr>';
									$html .= '<td>'.$model->staff_name.'</td>';
									$html .= '<td>';
									
										$html .= CHtml::link('<i class="fa fa-edit"></i> Edit', array('customerOfficeStaff/update', 'id'=>$model->id, 'customer_id'=>$model->customer_id));
											
										$html .= '&nbsp;&nbsp;&nbsp;&nbsp;';
										
										if( $model->account_id != null )
										{
											$html .= CHtml::link('<i class="fa fa-times"></i> Delete', 'javascript:void(0);', array('id'=>$model->id, 'has_calendar_assigned'=>$hasCalendarAssigned, 'class'=>'delete-staff-btn'));
										}
										
									$html .= '</td>';
								$html .= '</tr>';
							}
						}
					}
					
					// $html .= '<tr>';
						// $html .= '<td colspan="2" class="center">';
						
						// $html .= '
							// <a customer_office_id="'.$model->customer_office_id.'" customer_id="'.$model->customer_id.'" class="btn btn-xs btn-primary add-staff-btn" style="border-radius:3px;">
								// Add New Staff
							// </a>';
						
						// $html .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
						
						// $html .= '
							// <a customer_office_id="'.$model->customer_office_id.'" customer_id="'.$model->customer_id.'" class="btn btn-xs btn-success add-existing-staff-btn" style="border-radius:3px;">
								// Add Existing Staff
							// </a>';
							
						// $html .= '</td>';
					// $html .= '</tr>';
		
					$result['status'] = 'success';
					$result['message'] = 'Staff was added successfully.';
					$result['html'] = $html;
					
					echo json_encode($result);
					Yii::app()->end();
				}
				else
				{
					$this->redirect(array('view','id'=>$model->id));
				}
			}
		}

		
		if( isset($_POST['ajax']) )
		{
			$html = $this->renderPartial('ajax_create', array(
				'account' => $account,
				'model' => $model,
			), true);
			
			$result['status'] = 'success';
			$result['html'] = $html;
			
			echo json_encode($result);
			Yii::app()->end();
		}
		else
		{
			$this->render('create',array(
				'model'=>$model,
				'customer_id'=>$customer_id,
				'customer_office_id'=>$customer_office_id,
			));
		}
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => $html,
		);
		
		$authAccount = Yii::app()->user->account;
		
		$currentValues = array();
		
		$model = $this->loadModel($id);
		
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['CustomerOfficeStaff']))
		{
			$currentValues = $model->attributes;
			
			$model->attributes=$_POST['CustomerOfficeStaff'];
			
			$difference = array_diff($model->attributes, $currentValues);
			
			if($model->save())
			{
				if( isset($model->account) )
				{
					$model->account->attributes = $_POST['Account'];			
					$model->account->email_address = $model->email_address;
					$model->account->save(false);
				}
								
				if( $difference || $model->enable_texting != $currentValues['enable_texting'] )
				{
					$updateFields = '';
				
					if( $difference )
					{
						foreach( $difference as $attributeName => $value)
						{
							$updateFields .= $model->getAttributeLabel($attributeName) .' changed from '.$currentValues[$attributeName].' to '.$value.', ';
						}
					}
					
					$updateFields = rtrim($updateFields, ', ');
					
					$history = new CustomerHistory;
					
					$history->setAttributes(array(
						'model_id' => $model->id, 
						'customer_id' => $model->customer_id,
						'user_account_id' => $authAccount->id,
						'page_name' => 'Staff',
						'content' => $updateFields,
						'old_data' => json_encode($currentValues),
						'new_data' => json_encode($model->attributes),
						'type' => $history::TYPE_UPDATED,
					));

					$history->save(false);
				}
				
				
				if( isset($_POST['ajax']) )
				{
					$models = CustomerOfficeStaff::model()->findAll(array(
						'condition' => 'customer_office_id = :customer_office_id AND is_deleted=0',
						'params' => array(
							':customer_office_id' => $model->customer_office_id,
						),
					));
					
					if($models)
					{
						foreach( $models as $model )
						{
							$hasCalendarAssigned = CalendarStaffAssignment::model()->count(array(
								'condition' => 'staff_id = :staff_id',
								'params' => array(
									':staff_id' => $model->id,
								),
							));
																
							$html .= '<tr>';
								$html .= '<td>'.$model->staff_name.'</td>';
								$html .= '<td>';
									$html .= CHtml::link('<i class="fa fa-edit"></i> Edit', 'javascript:void(0);', array('id'=>$model->id, 'class'=>'edit-staff-btn'));
									$html .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
									$html .= CHtml::link('<i class="fa fa-edit"></i> Delete', 'javascript:void(0);', array('id'=>$model->id, 'has_calendar_assigned'=>$hasCalendarAssigned, 'class'=>'delete-staff-btn'));
								$html .= '</td>';
							$html .= '</tr>';
						}
					}
					
					$html .= '<tr>';
						$html .= '<td colspan="2" class="center">';
						
						$html .= '
							<a customer_office_id="'.$model->customer_office_id.'" customer_id="'.$model->customer_id.'" class="btn btn-xs btn-primary add-staff-btn" style="border-radius:3px;">
								Add New Staff
							</a>';
						
						$html .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
						
						$html .= '
							<a customer_office_id="'.$model->customer_office_id.'" customer_id="'.$model->customer_id.'" class="btn btn-xs btn-success add-existing-staff-btn" style="border-radius:3px;">
								Add Existing Staff
							</a>';
							
						$html .= '</td>';
					$html .= '</tr>';
		
					$result['status'] = 'success';
					$result['message'] = 'User settings was updated successfully.';
					$result['html'] = $html;
					
					echo json_encode($result);
					Yii::app()->end();
				}
				else
				{
					$this->redirect(array('view','id'=>$model->id));
				}
			}
		}

		$accountLanguages = AccountLanguageAssigned::model()->findAll(array(
			'condition' => 'account_id = :account_id',
			'params' => array(
				':account_id' => $model->account_id,
			),
		));
		
		$this->render('update',array(
			'model'=>$model,
			'existingCalenderStaffReceiveEmails'=>$existingCalenderStaffReceiveEmails,
			'accountLanguages'=>$accountLanguages,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id=null)
	{
		// $this->loadModel($id)->delete();

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		// if(!isset($_GET['ajax']))
			// $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
		
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => $html,
		);
		
		$authAccount = Yii::app()->user->account;
		
		if( isset($_POST['ajax']) && isset($_POST['id']) && isset($_POST['show_reassign_form']) )
		{
			$model = $this->loadModel($_POST['id']);
			
			$model->is_deleted = 1;
			
			if( $model->save(false) )
			{
				$history = new CustomerHistory;
			
				$history->setAttributes(array(
					'model_id' => $model->id, 
					'customer_id' => $model->customer_id,
					'user_account_id' => $authAccount->id,
					'page_name' => 'Staff',
					'content' => $model->staff_name,
					'type' => $history::TYPE_DELETED,
				));

				$history->save(false);
				
			
				$models = CustomerOfficeStaff::model()->findAll(array(
					'condition' => 'customer_id = :customer_id AND is_deleted=0',
					'params' => array(
						':customer_id' => $model->customer_id,
					),
				));
				
				if($models)
				{
					foreach( $models as $model )
					{
						if( !empty($model->account_id) )
						{
							$hasCalendarAssigned = CalendarStaffAssignment::model()->count(array(
								'condition' => 'staff_id = :staff_id',
								'params' => array(
									':staff_id' => $model->id,
								),
							));
																
							$html .= '<tr>';
								$html .= '<td>'.$model->staff_name.'</td>';
								$html .= '<td>';
								
									$html .= CHtml::link('<i class="fa fa-edit"></i> Edit', array('customerOfficeStaff/update', 'id'=>$model->id, 'customer_id'=>$model->customer_id));
										
									$html .= '&nbsp;&nbsp;&nbsp;&nbsp;';
									
									if( $model->account_id != null )
									{
										$html .= CHtml::link('<i class="fa fa-times"></i> Delete', 'javascript:void(0);', array('id'=>$model->id, 'has_calendar_assigned'=>$hasCalendarAssigned, 'class'=>'delete-staff-btn'));
									}
									
								$html .= '</td>';
							$html .= '</tr>';
						}
					}
				}

				
				$result['html'] = $html;
				$result['status'] = 'success';
				$result['message'] = 'Staff has been deleted successfully.';
			}
		}
		
		echo json_encode($result);
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex($customer_id = null, $customer_office_id = null)
	{
		$customer = new Customer;
		
		if(!empty($customer_id))
			$customer = Customer::model()->findByPk($customer_id);
		
		$this->render('index',array(
			'customer_id' => $customer_id,
			'customer_office_id' => $customer_office_id,
			'customer' => $customer,
		));
	}

	
	public function actionAddExistingStaff()
	{
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => $html,
		);
		
		if( isset($_POST['addExistingStaff']) )
		{
			$office = CustomerOffice::model()->findByPk( $_POST['office_id'] );
			
			foreach( $_POST['addExistingStaff'] as $staffId => $val )
			{
				$staff = CustomerOfficeStaff::model()->findByPk( $staffId );
				
				if( $staff )
				{
					$staff->customer_office_id = $office->id; 
					$staff->save(false);
				}
			}
			
			$models = CustomerOfficeStaff::model()->findAll(array(
				'condition' => 'customer_office_id = :customer_office_id AND is_deleted=0',
				'params' => array(
					':customer_office_id' => $office->id,
				),
			));
			
			if($models)
			{
				foreach( $models as $model )
				{
					$hasCalendarAssigned = CalendarStaffAssignment::model()->count(array(
						'condition' => 'staff_id = :staff_id',
						'params' => array(
							':staff_id' => $model->id,
						),
					));
														
					$html .= '<tr>';
						$html .= '<td>'.$model->staff_name.'</td>';
						$html .= '<td>';
							$html .= CHtml::link('<i class="fa fa-edit"></i> Edit', 'javascript:void(0);', array('id'=>$model->id, 'class'=>'edit-staff-btn'));
							$html .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
							
							if( $model->account_id != null )
							{
								$html .= CHtml::link('<i class="fa fa-edit"></i> Delete', 'javascript:void(0);', array('id'=>$model->id, 'has_calendar_assigned'=>$hasCalendarAssigned, 'class'=>'delete-staff-btn'));
							}
						
						$html .= '</td>';
					$html .= '</tr>';
				}
			}
			
			$html .= '<tr>';
				$html .= '<td colspan="2" class="center">';
				
				$html .= '
					<a customer_office_id="'.$model->customer_office_id.'" customer_id="'.$model->customer_id.'" class="btn btn-xs btn-primary add-staff-btn" style="border-radius:3px;">
						Add New Staff
					</a>';
				
				$html .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				
				$html .= '
					<a customer_office_id="'.$model->customer_office_id.'" customer_id="'.$model->customer_id.'" class="btn btn-xs btn-success add-existing-staff-btn" style="border-radius:3px;">
						Add Existing Staff
					</a>';
					
				$html .= '</td>';
			$html .= '</tr>';

			$result['status'] = 'success';
			$result['message'] = 'Staff was added successfully.';
			$result['html'] = $html;
		}
		
		if( isset($_POST['ajax']) && $_POST['office_id'] )
		{
			$office = CustomerOffice::model()->findByPk( $_POST['office_id'] );
			
			$staffOptions = CustomerOfficeStaff::model()->findAll(array(
				'condition' => 'customer_id = :customer_id AND is_deleted=0',
				'params' => array(
					':customer_id' => $office->customer_id,
				),
			));
				
			$html = $this->renderPartial('ajax_add_existing_staff', array(
				'office' => $office,
				'staffOptions' => $staffOptions,
			), true);
			
			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}
	
	
	public function actionList()
	{ 
		$model = $this->_getList();
		
		if(isset($_GET['CustomerOfficeStaff']))
			$model->attributes=$_GET['CustomerOfficeStaff'];
		
		if(isset($_GET['ajaxRequest']) && $_GET['ajaxRequest'] == 1){
            Yii::app()->clientScript->scriptMap['*.js'] = false;
            $this->renderPartial('_list', array('model' => $model), false, true);
        }
        else{
			$this->renderPartial('_list', array('model' => $model));
		}
	}
	
	public function _getList()
	{
		$model=new CustomerOfficeStaff('search');
		$model->unsetAttributes();  // clear any default values
		
		if(isset($_REQUEST['customer_id']))
		{
			$model->byCustomerId($_REQUEST['customer_id']);
		}
		
		if(isset($_REQUEST['customer_office_id']))
		{
			$model->byCustomerOfficeId($_REQUEST['customer_office_id']);
		}
		
		return $model;
	}
	
	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model=new CustomerOfficeStaff('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['CustomerOfficeStaff']))
			$model->attributes=$_GET['CustomerOfficeStaff'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

	
	public function actionTimeKeeping($filter='')
	{
		$payPeriodOptions = array();
		
		foreach( range(2015, 2018) as $year)
		{
			foreach( range( $year == 2015 ? 11 : 1 , 12) as $monthNumber )
			{
				$firstDayOfMonth = date('M d', strtotime($year.'-'.$monthNumber.'-01'));
				$fifteenthDayOfMonth = date('M d', strtotime($year.'-'.$monthNumber.'-16'));
				$lastDayOfMonth = date('d', strtotime('last day of this month', strtotime($firstDayOfMonth)));
				
				$payPeriodOptions[] = $firstDayOfMonth.' - 15 '.$year;
				$payPeriodOptions[] = $fifteenthDayOfMonth.' - '.$lastDayOfMonth.' '.$year;
			}
		}
		
		if( date('d') < 16 )
		{
			$currentPayPeriod =  date('M').' 01 -	 15 ' . date('Y');
		}
		else
		{
			$currentPayPeriod = date('M').' 16 - '.date('d', strtotime('last day of this month')) .' '. date('Y');
		}
		
		$currentPayPeriod = array_search( $currentPayPeriod, $payPeriodOptions  );
		
		$model = $this->loadModel(isset($_POST['id']) ? $_POST['id'] : $_GET['id']);

		$account = $model->account;
		
		$totalScheduledWorkHours = 0;
		
		$ptoFormRequests = AccountPtoForm::model()->findAll(array(
			'condition' => 'account_id = :account_id AND is_deleted = 0',
			'params' => array(
				':account_id' => $account->id,
			),
			'order' => 'date_created DESC',
		));
		
		$ptoRequests = AccountPtoRequest::model()->findAll(array(
			'condition' => 'account_id = :account_id AND status!=4',
			'params' => array(
				':account_id' => $account->id,
			),
			'order' => 'date_created DESC',
		));
		
		
		if( $filter != '')
		{
			$explodedDilterDate = explode(' - ', $payPeriodOptions[$filter]);
			$explodedElement1 = explode(' ', $explodedDilterDate[0]);
			$explodedElement2 = explode(' ', $explodedDilterDate[1]);

			$month = $explodedElement1[0];
			$start_day = $explodedElement1[1];
			$end_day = $explodedElement2[0];
			$year = $explodedElement2[1];
			
			$startDate =  $year.'-'.$month.'-'.$start_day;
			$endDate =  $year.'-'.$month.'-'.$end_day;
			
			$payPeriods = AccountLoginTracker::model()->findAll(array(
				'condition' => 'account_id = :account_id AND time_in >= :start_date AND time_in <= :end_date AND status!=4',
				'params' => array(
					':account_id' => $account->id,
					':start_date' => date('Y-m-d 00:00:00', strtotime($startDate)),
					':end_date' => date('Y-m-d 23:59:59', strtotime($endDate)),
				),
				'order' => 'time_in DESC',
			));
		}
		else
		{
			$payPeriods = AccountLoginTracker::model()->findAll(array(
				'condition' => 'account_id = :account_id AND status!=4',
				'params' => array(
					':account_id' => $account->id,
				),
				'order' => 'time_in DESC',
			));
		}
		
		$ptoFormDataProvider = new CArrayDataProvider($ptoFormRequests, array(
			'pagination' => array(
				'pageSize' => 5,
			),
		));
		
		$ptoDataProvider = new CArrayDataProvider($ptoRequests, array(
			'pagination' => array(
				'pageSize' => 5,
			),
		));
		
		$payPeriodDataProvider = new CArrayDataProvider($payPeriods, array(
			'pagination' => array(
				'pageSize' => 5,
			),
		));

		$this->render('timeKeeping', array(
			'model' => $model,
			'account' => $account,
			'ptoFormDataProvider' => $ptoFormDataProvider,
			'ptoDataProvider' => $ptoDataProvider,
			'payPeriodDataProvider' => $payPeriodDataProvider,
			'payPeriodOptions' => $payPeriodOptions,
			'currentPayPeriod' => $currentPayPeriod,
		));
	}
	
	//time keeping tab - pay period
	public function actionPayPeriodVarianceAction()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => '',
		);

		
		if( isset($_POST['id']) )
		{
			$model = AccountLoginTracker::model()->findByPk($_POST['id']);
			
			if( isset($_POST['AccountLoginTracker']) )
			{
				$model->attributes = $_POST['AccountLoginTracker'];
				$model->status = $_POST['status'];
				
				if( $model->save(false) )
				{
					$result['status'] = 'success';
					
					$account = Account::model()->findByPk($model->account_id);
					$accountUser = $account->accountUser;
					
					$timeIn = new DateTime($model->time_in, new DateTimeZone('America/Chicago'));
					$timeIn->setTimezone(new DateTimeZone('America/Denver'));	

					$timeOut = new DateTime($model->time_out, new DateTimeZone('America/Chicago'));
					$timeOut->setTimezone(new DateTimeZone('America/Denver'));	
					
					$startTime = $timeIn->format('m/d/Y g:i A');
					
					$endTime = $model->time_out != null ? $timeOut->format('m/d/Y g:i A') : '';
					
					$status = $model->status == 1 ? 'Approved' : 'Denied';
					
					$content = $status.' time punch: '.$startTime.' to '.$endTime; 
					
					$audit = new AccountUserNote;
						
					$audit->setAttributes(array(
						'account_id' => Yii::app()->user->account->id,
						'account_user_id' => $accountUser->id,
						'content' => $content,
						'old_data' => json_encode($currentValues),
						'new_data' => json_encode($model->attributes),
						'category_id' => 10,
					));

					$audit->save(false);
					
				}
			}
			
			$html = $this->renderPartial('ajax_pay_period_variance_form', array(
				'model' => $model,
			), true);

			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}
	
	public function actionMergeVariance()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		if( isset($_POST['Ids']) )
		{
			$firstRecord = AccountLoginTracker::model()->find(array(
				'condition' => 'id IN ('.implode(', ', $_POST['Ids']).')',
				'order' => 'id ASC',
			));
			
			$lastRecord = AccountLoginTracker::model()->find(array(
				'condition' => 'id IN ('.implode(', ', $_POST['Ids']).')',
				'order' => 'id DESC',
			));
			
			$employeeNotes = '';
			$supervisorNotes = '';
			
			$punches = AccountLoginTracker::model()->findAll(array(
				'condition' => 'id IN ('.implode(', ', $_POST['Ids']).')',
				'order' => 'id ASC',
			));
			
			if( $punches )
			{
				foreach( $punches as $punch )
				{
					if( !empty($punch->employee_note) )
					{
						$employeeNotes .= '<p>'.$punch->employee_note.'</p>';
					}
					
					if( !empty($punch->note) )
					{
						$supervisorNotes .= '<p>'.$punch->note.'</p>';
					}
				}
			}
			
			if( $firstRecord && $lastRecord )
			{
				// if( $lastRecord->type == 1)
				// {
					// $timeOut = new DateTime($lastRecord->time_out, new DateTimeZone('America/Denver'));
					// $timeOut->setTimezone(new DateTimeZone('America/Chicago'));		
				// }
				// else
				// {
					$timeOut = new DateTime($lastRecord->time_out);
				// }	
				
				$firstRecord->setAttributes(array(
					'time_out' => $timeOut->format('Y-m-d H:i:s'),
					'login_session_token' => $lastRecord->login_session_token,
					'status' => 1,
					'note' => $supervisorNotes,
					'employee_note' => $employeeNotes,
					'type' => $lastRecord->type,
				));
				
				if( $firstRecord->save(false) )
				{
					$models = AccountLoginTracker::model()->findAll(array(
						'condition' => 'id IN ('.implode(', ', $_POST['Ids']).')',
					));
					
					if( $models )
					{ 
						foreach( $models as $model )
						{
							if( $firstRecord->id != $model->id )
							{
								$model->delete();
							}
						}
						
						$result['status'] = 'success';
					}
				}
			}
		}
		
		echo json_encode($result);
	}
	
	
	public function actionEditVariance()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);

		
		if( isset($_POST['id']) )
		{
			$model = AccountLoginTracker::model()->findByPk($_POST['id']);
			$account = $model->account;
			$accountUser = $account->accountUser;
			
			if( isset($_POST['AccountLoginTracker']) )
			{
				$originalTimeIn = new DateTime($model->time_in, new DateTimeZone('America/Chicago'));
				$originalTimeIn->setTimezone(new DateTimeZone('America/Denver'));	

				$originalTimeOut = new DateTime($model->time_out, new DateTimeZone('America/Chicago'));
				$originalTimeOut->setTimezone(new DateTimeZone('America/Denver'));	

				$originalTimeOut = $model->time_out != null ? $originalTimeOut->format('m/d/Y g:i A') : '';
				
				$model->attributes = $_POST['AccountLoginTracker'];

				$model->time_in = date('Y-m-d', strtotime($_POST['AccountLoginTracker']['time_in_date'])).' '.date('H:i:s', strtotime($_POST['AccountLoginTracker']['time_in_time']));
				$model->time_out = date('Y-m-d', strtotime($_POST['AccountLoginTracker']['time_out_date'])).' '.date('H:i:s', strtotime($_POST['AccountLoginTracker']['time_out_time']));
				
				$timeIn = new DateTime($model->time_in, new DateTimeZone('America/Denver'));
				$timeIn->setTimezone(new DateTimeZone('America/Chicago'));	

				if( $model->type == 1)
				{
					$timeOut = new DateTime($model->time_out, new DateTimeZone('America/Denver'));
					$timeOut->setTimezone(new DateTimeZone('America/Chicago'));		
				}
				else
				{
					$timeOut = new DateTime($model->time_out);
				}	
				
				$model->time_in = $timeIn->format('Y-m-d H:i:s');
				$model->time_out = $timeOut->format('Y-m-d H:i:s');
				
				// $model->type = 2;
				
				if( $model->save(false) )
				{
					$result['status'] = 'success';
					
					$timeIn = new DateTime($model->time_in, new DateTimeZone('America/Chicago'));
					$timeIn->setTimezone(new DateTimeZone('America/Denver'));	

					$timeOut = new DateTime($model->time_out, new DateTimeZone('America/Chicago'));
					$timeOut->setTimezone(new DateTimeZone('America/Denver'));	
					
					$startTime = $timeIn->format('m/d/Y g:i A');
					
					$endTime = $model->time_out != null ? $timeOut->format('m/d/Y g:i A') : '';
					
					$content = 'Update time punch:'; 
					$content.= '<br>';
					$content.= '<b>Time In</b> from '.$originalTimeIn->format('m/d/Y g:i A').' to '.$startTime;
					$content.= '<br>';
					$content.= '<b>Time Out</b> from '.$originalTimeOut.' to '.$endTime;
					
					$audit = new AccountUserNote;
						
					$audit->setAttributes(array(
						'account_id' => Yii::app()->user->account->id,
						'account_user_id' => $accountUser->id,
						'content' => $content,
						'old_data' => json_encode($currentValues),
						'new_data' => json_encode($model->attributes),
						'category_id' => 10,
					));

					$audit->save(false);
				}
			}
			
			$html = $this->renderPartial('ajax_edit_variance_form', array(
				'model' => $model,
			), true);

			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}

	public function actionAddVariance()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);

		$model = new AccountLoginTracker; 
			
		if( isset($_POST['AccountLoginTracker']) )
		{
			$model->attributes = $_POST['AccountLoginTracker'];
			
			$model->account_id = $_POST['account_id'];
			
			$model->time_in = date('Y-m-d', strtotime($_POST['AccountLoginTracker']['time_in_date'])).' '.date('H:i:s', strtotime($_POST['AccountLoginTracker']['time_in_time']));
			$model->time_out = date('Y-m-d', strtotime($_POST['AccountLoginTracker']['time_out_date'])).' '.date('H:i:s', strtotime($_POST['AccountLoginTracker']['time_out_time']));
			
			$timeIn = new DateTime($model->time_in, new DateTimeZone('America/Denver'));
			$timeIn->setTimezone(new DateTimeZone('America/Chicago'));	

			if( $model->type == 1)
			{
				$timeOut = new DateTime($model->time_out, new DateTimeZone('America/Denver'));
				$timeOut->setTimezone(new DateTimeZone('America/Chicago'));		
			}
			else
			{
				$timeOut = new DateTime($model->time_out);
			}	
			
			$model->time_in = $timeIn->format('Y-m-d H:i:s');
			$model->time_out = $timeOut->format('Y-m-d H:i:s');
			
			// $model->type = 2;
			
			if( $model->save(false) )
			{
				$result['status'] = 'success';
				
				$account = Account::model()->findByPk($_POST['account_id']);
				$accountUser = $account->accountUser;
				
				$timeIn = new DateTime($model->time_in, new DateTimeZone('America/Chicago'));
				$timeIn->setTimezone(new DateTimeZone('America/Denver'));	

				$timeOut = new DateTime($model->time_out, new DateTimeZone('America/Chicago'));
				$timeOut->setTimezone(new DateTimeZone('America/Denver'));	
				
				$startTime = $timeIn->format('m/d/Y g:i A');
				
				$endTime = $model->time_out != null ? $timeOut->format('m/d/Y g:i A') : '';
				
				$content = 'Added time punch: '.$startTime.' to '.$endTime;
				
				$audit = new AccountUserNote;
								
				$audit->setAttributes(array(
					'account_id' => Yii::app()->user->account->id,
					'account_user_id' => $accountUser->id,
					'content' => $content,
					'category_id' => 10,
				));

				$audit->save(false);
			}
		}
		
		$html = $this->renderPartial('ajax_add_variance_form', array(
			'model' => $model,
		), true);

		$result['status'] = 'success';
		$result['html'] = $html;
	
		echo json_encode($result);
	}
	
	public function actionDeleteVariance()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		$dbUpdates = 0;
		
		if( isset($_POST['Ids']) )
		{
			$models = AccountLoginTracker::model()->findAll(array(
				'condition' => 'id IN ('.implode(', ', $_POST['Ids']).')',
			));
			
			if( $models )
			{
				foreach( $models as $model )
				{
					$model->status = 4;
					
					if( $model->save(false) )
					{
						$account = Account::model()->findByPk($model->account_id);
						$accountUser = $account->accountUser;
						
						$timeIn = new DateTime($model->time_in, new DateTimeZone('America/Chicago'));
						$timeIn->setTimezone(new DateTimeZone('America/Denver'));	

						$timeOut = new DateTime($model->time_out, new DateTimeZone('America/Chicago'));
						$timeOut->setTimezone(new DateTimeZone('America/Denver'));	
						
						$startTime = $timeIn->format('m/d/Y g:i A');
						
						$endTime = $model->time_out != null ? $timeOut->format('m/d/Y g:i A') : '';
						
						$content = 'Deleted time punch: '.$startTime.' to '.$endTime;
						
						$audit = new AccountUserNote;
										
						$audit->setAttributes(array(
							'account_id' => Yii::app()->user->account->id,
							'account_user_id' => $accountUser->id,
							'content' => $content,
							'category_id' => 10,
						));

						$audit->save(false);
					}
				}
			}
			
			if( $dbUpdates > 0 )
			{
				$result['status'] = 'success';
			}
		}
		
		echo json_encode($result);
	}
	
	//time keeping tab - work schedule
	public function actionAjaxGetTotalLoginHours()
	{
		$result = array(
			'status' => 'error',
			'value' => 0,
		);
		
		if( isset($_POST['ajax']) && isset($_POST['filter']) && isset($_POST['account_id']) )
		{
			$account = Account::model()->findByPk($_POST['account_id']);
			
			if( $account )
			{
				$payPeriodOptions = array();
				
				foreach( range(2015, 2018) as $year)
				{
					foreach( range( $year == 2015 ? 11 : 1 , 12) as $monthNumber )
					{
						$firstDayOfMonth = date('M d', strtotime($year.'-'.$monthNumber.'-01'));
						$fifteenthDayOfMonth = date('M d', strtotime($year.'-'.$monthNumber.'-16'));
						$lastDayOfMonth = date('d', strtotime('last day of this month', strtotime($firstDayOfMonth)));
						
						$payPeriodOptions[] = $firstDayOfMonth.' - 15 '.$year;
						$payPeriodOptions[] = $fifteenthDayOfMonth.' - '.$lastDayOfMonth.' '.$year;
					}
				}
				
				if( $_POST['filter'] != '')
				{
					$filter = $_POST['filter'];
					
					$explodedDilterDate = explode(' - ', $payPeriodOptions[$filter]);
					$explodedElement1 = explode(' ', $explodedDilterDate[0]);
					$explodedElement2 = explode(' ', $explodedDilterDate[1]);

					$month = $explodedElement1[0];
					$start_day = $explodedElement1[1];
					$end_day = $explodedElement2[0];
					$year = $explodedElement2[1];
					
					$startDate =  $year.'-'.$month.'-'.$start_day;
					$endDate =  $year.'-'.$month.'-'.$end_day;
					
					$result['status'] = 'success';
					$result['value'] = $account->getTotalLoginHours($startDate, $endDate);
				}
			}
		}
		
		echo json_encode($result);
	}
	
	
	//assignments tabs
	public function actionAssignments()
	{
		$authAccount = Yii::app()->user->account;
		
		$model = $this->loadModel(isset($_POST['id']) ? $_POST['id'] : $_GET['id']);
		
		$account = $model->account;

		$accountLanguages = AccountLanguageAssigned::model()->findAll(array(
			'condition' => 'account_id = :account_id',
			'params' => array(
				':account_id' => $account->id,
			),
		));
		
		#skills
		$accountTrainedSkills = AccountSkillTrained::model()->findAll(array(
			'with' => 'skill',
			'condition' => 't.account_id = :account_id AND skill.status=1 AND skill.is_deleted=0',
			'params' => array(
				':account_id' => $account->id,
			),
		));
		
		$accountAssignedSkills = AccountSkillAssigned::model()->findAll(array(
			'with' => 'skill',
			'condition' => 't.account_id = :account_id AND skill.status=1 AND skill.is_deleted=0',
			'params' => array(
				':account_id' => $account->id,
			),
		));
		
		#skill Child
		$accountTrainedSkillChilds = AccountSkillChildTrained::model()->findAll(array(
			'with' => 'skillChild',
			'condition' => 't.account_id = :account_id AND skillChild.status=1 AND skillChild.is_deleted=0',
			'params' => array(
				':account_id' => $account->id,
			),
		));
		
		$accountAssignedSkillChilds = AccountSkillChildAssigned::model()->findAll(array(
			'with' => 'skillChild',
			'condition' => 't.account_id = :account_id AND skillChild.status=1 AND skillChild.is_deleted=0',
			'params' => array(
				':account_id' => $account->id,
			),
		));
		
		$accountAssignedCompanies = AccountCompanyAssigned::model()->findAll(array(
			'with' => 'company',
			'condition' => 't.account_id = :account_id AND company.status != 3',
			'params' => array(
				':account_id' => $account->id,
			),
		));
		
		$this->render('assignments', array(
			'model' => $model,
			'account' => $account,
			'accountLanguages' => $accountLanguages,
			'accountTrainedSkills' => $accountTrainedSkills,
			'accountAssignedSkills' => $accountAssignedSkills,
			'accountTrainedSkillChilds' => $accountTrainedSkillChilds,
			'accountAssignedSkillChilds' => $accountAssignedSkillChilds,
			'accountAssignedCompanies' => $accountAssignedCompanies,
		));
	}
	
	public function actionUpdateAccountLanguage()
	{
		$result = array(
			'status' => '',
			'message' => '',
		);
		
		if( isset($_POST['ajax']) )
		{
			if( $_POST['type'] == 'add' )
			{
				$model = new AccountLanguageAssigned;
				
				$model->setAttributes(array(
					'account_id' => $_POST['account_id'],
					'language_id' => $_POST['item_id'],
				));
				
				if( $model->save(false) )
				{
					$result['status'] = 'success';
				}
			}
			
			if( $_POST['type'] == 'remove' )
			{
				$model = AccountLanguageAssigned::model()->find(array(
					'condition' => 'account_id = :account_id AND language_id = :language_id',
					'params' => array(
						':account_id' => $_POST['account_id'],
						':language_id' => $_POST['item_id'],
					),
				));
				
				if( $model )
				{
					if( $model->delete() )
					{
						$result['status'] = 'success';
					}
				}
			}
		}
		
		echo json_encode($result);
	}
	
	
	//performance tab
	public function actionPerformance()
	{
		ini_set('memory_limit', '1024M');
		set_time_limit(0);
		
		$authAccount = Yii::app()->user->account;
		
		$model = $this->loadModel(isset($_POST['id']) ? $_POST['id'] : $_GET['id']);
		
		$account = $model->account;
		
		$addCondition = '';
		$order = 'lch.start_call_time DESC';
		
		if( !empty($_GET['search_query']) )
		{
			$addCondition .= ' AND ( lch.lead_phone_number LIKE "'.$_GET['search_query'].'%"';
			$addCondition .= ' OR CONCAT(ld.first_name , " " , ld.last_name) LIKE "'.$_GET['search_query'].'%" )';
		}
		
		if( !empty($_GET['start_date']) && !empty($_GET['end_date']) )
		{
			$addCondition .= ' AND DATE(lch.start_call_time) >= "'.date('Y-m-d 00:00:00', strtotime($_GET['start_date'])).'"';
			$addCondition .= ' AND DATE(lch.start_call_time) <= "'.date('Y-m-d 23:59:59', strtotime($_GET['end_date'])).'"';
		}
		
		if( isset($_GET['sorter']) )
		{
			if( $_GET['sorter'] == 'date_time' )
			{
				$order = 'lch.start_call_time DESC';
			}
			
			if( $_GET['sorter'] == 'skill' )
			{
				$order = 'sk.skill_name ASC';
			}
			
			if( $_GET['sorter'] == 'customer_name' )
			{
				$order = 'c.lastname ASC';
			}
			
			if( $_GET['sorter'] == 'disposition' )
			{
				$order = 'lch.disposition ASC';
			}
		}
		
		// $models = LeadCallHistory::model()->findAll(array(	
			// 'with' => array('lead', 'list', 'list.skill', 'customer'),
			// 'together' => true,
			// 'condition' => 't.agent_account_id = :agent_account_id' . $addCondition,
			// 'params' => array(
				// ':agent_account_id' => $account->id,
			// ),
			// 'order' => $order,
		// ));
		
		$page = (isset($_GET['page']) ? $_GET['page'] : 1);
		$page = $page > 0 ? $page-1 : 0;
		
		$callsCountSql = "
			SELECT COUNT(lch.id) as total_count
			FROM ud_lead_call_history lch 
			LEFT JOIN ud_lead ld ON ld.id = lch.lead_id
			WHERE lch.`agent_account_id`='".$account->id."'
			AND lch.disposition IS NOT NULL 
			AND lch.status !=4
			".$addCondition."
		";

		$modelsCount = Yii::app()->db->createCommand($callsCountSql)->queryRow();
		
		$callsSql = "
			SELECT
				lch.id,
				sk.skill_name, 
				CONCAT (c.firstname, ' ', c.lastname) AS customer_name,
				ld.id as lead_id,
				lch.lead_phone_number,
				CONCAT(COALESCE(ld.first_name,''), ' ', COALESCE(ld.last_name,'')) as lead_name,
				lch.start_call_time,
				lch.disposition,
				lch.type
			FROM ud_lead_call_history lch 
			LEFT JOIN ud_customer c ON lch.customer_id = c.id
			LEFT JOIN ud_lists ls ON ls.id = lch.list_id
			LEFT JOIN ud_lead ld ON ld.id = lch.lead_id
			LEFT JOIN ud_skill sk ON sk.id = ls.skill_id
			WHERE lch.`agent_account_id`='".$account->id."'
			AND lch.disposition IS NOT NULL 
			AND lch.status !=4
			".$addCondition."
			ORDER BY ".$order."
		";

		$models = Yii::app()->db->createCommand($callsSql)->queryAll();
		
		$dataProvider = new CArrayDataProvider($models, array(
			'pagination' => array(
				'pageSize' => 15,
			),
			'totalItemCount' => $modelsCount['total_count'],
		));
		
		$this->render('performance', array(
			'authAccount' => $authAccount,
			'account' => $account,
			'model' => $model,
			'dataProvider' => $dataProvider,
		));
	}
	
	public function actionAjaxStats()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => '',
		);
		
		if( isset($_POST['ajax']) && !empty($_POST['agent_stat_start_date']) && !empty($_POST['agent_stat_end_date']) && !empty($_POST['agent_account_id']) )
		{
			$skillsSql = '
				SELECT ls.`skill_id`, sk.`skill_name`
				FROM ud_lead_call_history lch 
				LEFT JOIN ud_lists ls ON ls.`id` = lch.`list_id`
				LEFT JOIN ud_skill sk ON sk.`id` = ls.`skill_id`
				WHERE lch.`agent_account_id`="'.$_POST['agent_account_id'].'"
				GROUP BY ls.`skill_id`
			';

			$skills = Yii::app()->db->createCommand($skillsSql)->queryAll();
			
			$html = '';
			
			if( $skills )
			{
				foreach( $skills as $skill )
				{
					$addCondition = '';
					$addCondition2 = '';
					
					if( !empty($_POST['agent_stat_start_date']) && !empty($_POST['agent_stat_end_date']) )
					{
						$addCondition .= ' AND DATE(alt.time_in) >= "'.date('Y-m-d 00:00:00', strtotime($_POST['agent_stat_start_date'])).'"';
						$addCondition .= ' AND DATE(alt.time_in) <= "'.date('Y-m-d 23:59:59', strtotime($_POST['agent_stat_end_date'])).'"';
					}
					else
					{
						$addCondition .= ' AND DATE(alt.time_in) >= "'.date('Y-m-d 00:00:00').'"';
						$addCondition .= ' AND DATE(alt.time_in) <= "'.date('Y-m-d 23:59:59').'"';
					}
					
					if( !empty($_POST['agent_stat_start_date']) && !empty($_POST['agent_stat_end_date']) )
					{
						$addCondition2 .= ' AND DATE(lch.start_call_time) >= "'.date('Y-m-d 00:00:00', strtotime($_POST['agent_stat_start_date'])).'"';
						$addCondition2 .= ' AND DATE(lch.start_call_time) <= "'.date('Y-m-d 23:59:59', strtotime($_POST['agent_stat_end_date'])).'"';
					}
					else
					{
						$addCondition2 .= ' AND DATE(lch.start_call_time) >= "'.date('Y-m-d 00:00:00').'"';
						$addCondition2 .= ' AND DATE(lch.start_call_time) <= "'.date('Y-m-d 23:59:59').'"';	
					}

					$sql = "
						SELECT
						(
							SELECT SUM(
								CASE WHEN time_out IS NOT NULL THEN TIME_TO_SEC(TIMEDIFF(time_out, time_in))/3600
									ELSE TIME_TO_SEC(TIMEDIFF(DATE_SUB(NOW(), INTERVAL 1 HOUR), time_in))/3600 
								END
							)
							FROM ud_account_login_tracker alt
							WHERE alt.account_id = a.`id`
							AND alt.status !=4
							".$addCondition."
						) AS total_hours,
						(
							SELECT COUNT(lch.id) 
							FROM ud_lead_call_history lch
							LEFT JOIN ud_lists uls ON uls.id = lch.list_id
							WHERE lch.agent_account_id = a.`id`															
							AND uls.skill_id ='".$skill['skill_id']."'
							AND lch.status != 4
							".$addCondition2."
						) AS dials,
						(
							SELECT COUNT(lch.id) 
							FROM ud_lead_call_history lch
							LEFT JOIN ud_lists uls ON uls.id = lch.list_id
							WHERE lch.agent_account_id = a.`id`
							AND uls.skill_id ='".$skill['skill_id']."'
							AND lch.disposition='Appointment Set'
							AND lch.status != 4
							AND lch.is_skill_child=0
							".$addCondition2."
						) AS appointments,
						(
							SELECT COUNT(lch.id) 
							FROM ud_lead_call_history lch
							LEFT JOIN ud_lists uls ON uls.id = lch.list_id
							LEFT JOIN ud_skill_disposition sd ON sd.id = lch.disposition_id 
							WHERE lch.agent_account_id = a.`id` 
							AND uls.skill_id ='".$skill['skill_id']."'
							AND sd.is_voice_contact = 1
							AND sd.id IS NOT NULL
							AND lch.status != 4
							".$addCondition2."
						) AS voice_contacts
						FROM ud_account a
						WHERE a.id = '".$_POST['agent_account_id']."'
					";
					
					$stats = Yii::app()->db->createCommand($sql)->queryRow();

					$html .= '<tr>';
					
						$html .= '<td>'.$skill['skill_name'].'</td>';
						
						$html .= '<td class="center">';
							if( $stats['dials'] > 0 )
							{
								$html .= round($stats['total_hours'], 2);
							}
							else
							{
								$html .= 0;
							}
						$html .= '</td>';
						
						$html .= '<td class="center">'.$stats['appointments'].'</td>';
						
						$html .= '<td>';
							if( $stats['appointments'] > 0 && $stats['total_hours'] > 0 )
							{
								$html .= round($stats['appointments'] / $stats['total_hours'], 2);
							}
							else
							{
								
								$html .= 0;
							}
						$html .= '</td>';
						
						$html .= '<td class="center">'.$stats['dials'].'</td>';
						
						$html .= '<td class="center">';
	
							if( $stats['dials'] > 0 && $stats['total_hours'] > 0 )
							{
								$html .= round($stats['dials'] / $stats['total_hours'], 2);
							}
							else
							{
								
								$html .= 0;
							}
						
						$html .= '</td>';
						
						$html .= '<td class="center">';
						
							if( $stats['appointments'] > 0 && $stats['voice_contacts'] > 0 )
							{
								$html .= round($stats['appointments'] / $stats['voice_contacts'], 2) * 100 . '%';
							}
							else
							{
								
								$html .= '0%';
							}
						
						$html .= '</td>';
						
					$html .= '</tr>';
				}
			}
			
			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}
	
	
	//permissions tab
	public function actionPermissions($id = null, $customer_id = null)
	{
		if(!empty($_POST['customer_id']))
			$customer_id = $_POST['customer_id'];
		
		if(!empty($_POST['id']))
			$id = $_POST['id'];
		
		$customer = Customer::model()->findByPk($customer_id);
		
		if($customer === null)
			throw new CHttpException("403", "Customer not found.");
		
		$customerOfficeStaff = CustomerOfficeStaff::model()->find(array(
			'condition' => 'id = :id',
			'params' => array(
				':id' => $id,
			),
		));
				
		if($customerOfficeStaff === null)
			throw new CHttpException("403", "Office Staff not found.");
		
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		// $id = $_REQUEST['id'];
		
		$authAccount = Yii::app()->user->account;
		
		if( isset($_POST['ajax']) && isset($_POST['permission_key']) && isset($_POST['permission_type']) && isset($_POST['value']) )
		{
			$permissionConfig = CustomerAccountPermission::model()->find(array(
				'condition' => '
					account_id = :account_id
					AND permission_key = :permission_key
					AND permission_type = :permission_type
				',
				'params' => array(
					':account_id' => $customerOfficeStaff->account_id,
					':permission_key' => $_POST['permission_key'],
					':permission_type' => $_POST['permission_type'],
				),
			));
			
			
			if( $_POST['value'] == 1 )
			{
				
				if( empty($permissionConfig) )
				{
					$permissionConfig = new CustomerAccountPermission;
					
					$permissionConfig->account_id = $customerOfficeStaff->account_id;
					$permissionConfig->permission_key = $_POST['permission_key'];
					$permissionConfig->permission_type = $_POST['permission_type'];

					if( $permissionConfig->save(false) )
					{
						$result['status'] = 'success';
					}
					else
					{
						print_r($permissionConfig->getErrors());
					}
				}
			}
			else
			{
				if( $permissionConfig )
				{
					if( $permissionConfig->delete() )
					{
						$result['status'] = 'success';
					}
				}
			}

			echo json_encode($result);
			Yii::app()->end();
		}
		
		// $securityGroups = Account::listAccountType();
		
		// $securityGroupPermissionSwitch = AccountPermission::model()->find(array(
			// 'condition' => 'permission_key = :permission_key AND permission_type = "master_switch"',
			// 'params' => array(
				// ':permission_key' => 'security_group_'.strtolower($securityGroups[$id]).'_master',
			// ),
		// ));
		
		$this->render('permissions', array(
			// 'id' => $id,
			'customer' => $customer,
			'authAccount' => $authAccount,
			'customerOfficeStaff' => $customerOfficeStaff,
			'securityGroups' => $securityGroups,
			// 'securityGroupPermissionSwitch' => $securityGroupPermissionSwitch,
		));
	}
		
	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return CustomerOfficeStaff the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=CustomerOfficeStaff::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param CustomerOfficeStaff $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='customer-office-staff-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
