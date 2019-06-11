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
		
		$model=new CustomerOfficeStaff;
		$model->customer_id = isset($_POST['customer_id']) ? $_POST['customer_id'] : $customer_id;
		$model->customer_office_id = isset($_POST['customer_office_id']) ? $_POST['customer_office_id'] : $customer_office_id;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['CustomerOfficeStaff']))
		{
			$model->attributes=$_POST['CustomerOfficeStaff'];
			$model->status = 1;
			
			if($model->save())
			{
				if( !empty($_POST['calenderReceiveEmails']) )
				{
					foreach( $_POST['calenderReceiveEmails'] as $calendarId )
					{
						$calenderReceiveEmail = new CalenderStaffReceiveEmail;
						$calenderReceiveEmail->setAttributes(array(
							'staff_id' => $model->id,
							'calendar_id' => $calendarId,
						));
						
						$calenderReceiveEmail->save(false);
					}
				}
				
				$account = $this->autoCreateAccount($model);
				CustomerAccountPermission::autoAddPermissionKey($account);
				
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
	public function actionUpdate($id=null)
	{
		$id = isset($_POST['id']) ? $_POST['id'] : $id;
		
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => $html,
		);
		
		$authAccount = Yii::app()->user->account;
		
		$currentValues = array();
		
		$model=$this->loadModel($id);
		
		$existingCalenderStaffReceiveEmails = CalenderStaffReceiveEmail::model()->findAll(array(
			'condition' => 'staff_id = :staff_id',
			'params' => array(
				':staff_id' => $model->id,
			),
		));
		
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
					$model->account->email_address = $model->email_address;
					$model->account->save(false);
				}
				
				if( $existingCalenderStaffReceiveEmails )
				{
					foreach( $existingCalenderStaffReceiveEmails as $existingCalenderStaffReceiveEmail )
					{
						$existingCalenderStaffReceiveEmail->delete();
					}
				}
					
				if( !empty($_POST['calenderReceiveEmails']) )
				{
					foreach( $_POST['calenderReceiveEmails'] as $calendarId )
					{
						$calenderReceiveEmail = new CalenderStaffReceiveEmail;
						$calenderReceiveEmail->setAttributes(array(
							'staff_id' => $model->id,
							'calendar_id' => $calendarId,
						));
						
						$calenderReceiveEmail->save(false);
					}
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
					
					$enableTextingCurrentValue = $currentValues['enable_texting'] == 1 ? 'Yes' : 'No';

					$enableTextingNewValue = $model->enable_texting == 1 ? 'Yes' : 'No';
					
					$updateFields = 'Enable texting changed from '.$enableTextingCurrentValue.' to '.$enableTextingNewValue.', ';
					
					
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
					$result['message'] = 'Staff was updated successfully.';
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
			$html = $this->renderPartial('ajax_update', array(
				'model' => $model,
				'existingCalenderStaffReceiveEmails' => $existingCalenderStaffReceiveEmails,
			), true);
			
			$result['status'] = 'success';
			$result['html'] = $html;
			
			echo json_encode($result);
			Yii::app()->end();
		}
		else
		{
			$this->render('update',array(
				'model'=>$model,
				'customer_id'=>$model->customer_id,
				'customer_office_id'=>null,
			));
		}
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

		
		if( isset($_POST['reassign_calendar']) && isset($_POST['id']) )
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
				
				$result['status'] = 'success';
				
			
				foreach( $_POST['reassign_calendar'] as $calendarId => $staffId)
				{
					$calendarStaffAssignment = CalendarStaffAssignment::model()->find(array(
						'condition' => 'calendar_id = :calendar_id',
						'params' => array(
							':calendar_id' => $calendarId
						),
					));
					
					if( $calendarStaffAssignment )
					{
						$calendarStaffAssignment->staff_id = $staffId;
						
						$calendarStaffAssignment->save(false);
					}
				}
				
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

				$result['html'] = $html;
				$result['status'] = 'success';
				$result['message'] = 'Calendars are reassigned and Staff has been deleted successfully.';
			}
		}
		
		if( isset($_POST['ajax']) && isset($_POST['id']) && isset($_POST['show_reassign_form']) )
		{
			$model = $this->loadModel($_POST['id']);
			
			if( $_POST['show_reassign_form'] == 1 )
			{
				$calendars = CalendarStaffAssignment::model()->findAll(array(
					'condition' => 'staff_id = :staff_id',
					'params' => array(
						':staff_id' => $model->id,
					),
				));
				
				$staffOptions = CustomerOfficeStaff::model()->findAll(array(
					'condition' => 'id != :id AND customer_id = :customer_id AND is_deleted=0',
					'params' => array(
						':id' => $model->id,
						':customer_id' => $model->customer_id,
					),
				));
				
				$html = $this->renderPartial('ajax_reassign_form', array(
					'model' =>$model,
					'calendars' =>$calendars,
					'staffOptions' =>$staffOptions,
				), true);
				
				$result['status'] = 'success';
				$result['html'] = $html;
			}
			else
			{
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

					
					$result['html'] = $html;
					$result['status'] = 'success';
					$result['message'] = 'Staff has been deleted successfully.';
				}
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
