<?php 

class CustomerFileController extends Controller
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
		
		
		$notes = array();
		
		$customer = Customer::model()->findByPk($customer_id);
		
		$models = CustomerFile::model()->findAll(array(
			'condition' => 'customer_id = :customer_id AND status=1 AND DATE(date_created) >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)',
			'params' => array(
				':customer_id' => $customer->id,
			),
			'order' => 'date_created DESC',
		));
	
		
		$dataProvider=new CArrayDataProvider($models, array(
			// 'pagination'=>array(
	            // 'pageSize'=>10,
	        // ),
		));
		
		$this->render('index', array(
			'notes' => $notes,
			'customer' => $customer,
			'dataProvider' => $dataProvider,
		));
		
	}

	
	public function actionDelete($id, $customerId)
	{
		$authAccount = Yii::app()->user->account;
		
		$model = CustomerFile::model()->findByPk($id);
		
		if( $model )
		{
			$model->status = 3;
			
			if( $model->save(false) )
			{
				Yii::app()->user->setFlash('success', 'File has been deleted successfully.');
				
				$history = new CustomerHistory;
						
				$history->setAttributes(array(
					'model_id' => $model->id, 
					'customer_id' => $customerId,
					'user_account_id' => $authAccount->id,
					'page_name' => 'Customer File',
					'type' => $history::TYPE_DELETED,
				));

				$history->save(false);	
			}
		}
		
		$this->redirect(array('index', 'customer_id'=>$customerId));
	}
	
	
	public function actionUpload()
	{
		$authAccount = Yii::app()->user->account;
		
		if (!empty($_FILES)) 
		{
			$tempFile = $_FILES['file']['tmp_name'];         

			$newFileName = time().'-'.$_FILES['file']['name'];
			
			// $targetFile =  'fileupload/' . $_FILES['file']['name'];
			$targetFile =  'fileupload/' . $newFileName;

			if( move_uploaded_file($tempFile, $targetFile) )
			{
				$fileUpload = new Fileupload;
				$fileUpload->original_filename = $_FILES['file']['name'];
				$fileUpload->generated_filename = $newFileName;
				
				if( $fileUpload->save(false) )
				{
					$customerFile = new CustomerFile;
					
					$customerFile->setAttributes(array(
						'customer_id' => $_GET['customer_id'],
						'fileupload_id' => $fileUpload->id,
						'user_account_id' => $authAccount->id,
					));
					
					if( $customerFile->save(false) )
					{
						$history = new CustomerHistory;
						
						$history->setAttributes(array(
							'model_id' => $customerFile->id, 
							'customer_id' => $_GET['customer_id'],
							'user_account_id' => $authAccount->id,
							'page_name' => 'Customer File',
							'type' => $history::TYPE_ADDED,
						));

						$history->save(false);
						
						##Everytime a new files is added to the MyFiles tab 
						##send an email to  
						##notifying of the new file and customer name
						
						$customer = Customer::model()->findByPk($_GET['customer_id']);
						
						if($customer !== null)
						{
							print_r($customer->attributes);
							$subject="Customer File added to ".$customer->fullName;
				
							$emailContent = "New file has been added to customer ".$customer->fullName."<br/>
							Filename: ".$fileUpload->original_filename."<br/>";
								
								
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
							$mail->SetFrom('service@engagex.com', 'Engagex Service', 0);
							$mail->AddAddress('customerservice@engagex.com');
							
							// $mail->AddCC('Callie.brown@engagex.com');
							
							// $mail->AddBCC('markjuan169@gmail.com');
							// $mail->AddBCC('mark.juan@engagex.com');
							$mail->AddBCC('jim.campbell@engagex.com');
							$mail->AddBCC('erwin.datu@engagex.com');
							
							$mail->Subject = $subject;
							$mail->MsgHTML( $emailTemplate);
							
							$mail->Send();
						
						}
					}
				}
			}
		}
	}

}

?>