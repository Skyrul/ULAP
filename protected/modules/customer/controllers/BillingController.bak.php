<?php 

class BillingController extends Controller
{
	public function accessRules()
	{
		return array(
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array(
					'index', 
					'createCreditCard', 'updateCreditCard', 'deleteCreditCard', 'setDefaultCreditCard',
					'processTransaction', 'voidTransaction', 'refundTransaction', 'partialRefundRecord',
					'creatEcheck', 'updateEcheck', 'deleteEcheck', 'setDefaultEcheck', 'downloadReceipt'
				),
				'users'=>array('*'),
			),
		);
	}
	
	public function actionIndex($customer_id)
	{
		$customer = Customer::model()->findByPk($customer_id);
		
		$creditCards = CustomerCreditCard::model()->findAll(array(
			'condition' => 'customer_id = :customer_id AND status=1',
			'params' => array(
				':customer_id' => $customer->id,
			),
			'order'=> 'date_created DESC',
		));
		
		$echecks = CustomerEcheck::model()->findAll(array(
			'condition' => 'customer_id = :customer_id AND status=1',
			'params' => array(
				':customer_id' => $customer->id,
			),
			'order'=> 'date_created DESC',
		));
		
		$creditCardDataProvider=new CArrayDataProvider($creditCards, array(
			// 'pagination'=>array(
	            // 'pageSize'=>10,
	        // ),
		));
		
		$echeckDataProvider=new CArrayDataProvider($echecks, array(
			// 'pagination'=>array(
	            // 'pageSize'=>10,
	        // ),
		));
		
		$transactions = CustomerBilling::model()->findAll(array(
			'condition' => 'customer_id = :customer_id',
			'params' => array(
				':customer_id' => $customer->id,
			),
			'order'=> 'date_created DESC',
		));
		
		$transactionDataProvider=new CArrayDataProvider($transactions, array(
			// 'pagination'=>array(
	            // 'pageSize'=>10,
	        // ),
		));
		
		$this->render('index', array(
			'customer' => $customer,
			'creditCardDataProvider' => $creditCardDataProvider,
			'transactionDataProvider' => $transactionDataProvider,
			'echeckDataProvider' => $echeckDataProvider,
		));
		
	}
	
	
	//Credit Card Functions
	
	public function actionCreateCreditCard()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		$authAccount = Yii::app()->user->account;
		
		$model = new CustomerCreditCard;
	
		if( isset($_POST['CustomerCreditCard']) )
		{
			$existingCreditCard = CustomerCreditCard::model()->count(array(
				'condition' => 'customer_id = :customer_id AND status=1',
				'params' => array(
					':customer_id' => $_POST['customer_id']
				),
			));
			
			$existingEcheck = CustomerEcheck::model()->count(array(
				'condition' => 'customer_id = :customer_id AND status=1',
				'params' => array(
					':customer_id' => $_POST['customer_id']
				),
			));
			
			$model->attributes = $_POST['CustomerCreditCard'];
			
			if( empty($_POST['CustomerCreditCard']['nick_name']) )
			{
				$sameCreditCardType = CustomerCreditCard::model()->findall(array(
					'condition' => 'customer_id = :customer_id AND credit_card_type = :credit_card_type AND status=1',
					'params' => array(
						':customer_id' => $_POST['customer_id'],
						':credit_card_type' => $model->credit_card_type,
					),
				));

				if( count($sameCreditCardType) > 0 )
				{
					$tempNickName = $model->credit_card_type;
					$tempNickName .= ' ' . count($sameCreditCardType) + 1;
				}
				else
				{
					$tempNickName = $model->credit_card_type;
				}
				
				$model->nick_name = $tempNickName;
			}

			
			$model->customer_id = $_POST['customer_id'];	

			if( $existingCreditCard == 0 && $existingEcheck == 0 )
			{
				$model->is_preferred = 1;
			}
			
			if( $model->save(false) )
			{
				$history = new CustomerHistory;
				
				$history->setAttributes(array(
					'model_id' => $model->id, 
					'customer_id' => $model->customer_id,
					'user_account_id' => $authAccount->id,
					'page_name' => 'Credit Card',
					'content' => $model->credit_card_type.' '.substr($model->credit_card_number, -4),
					'type' => $history::TYPE_ADDED,
				));

				$history->save(false);
				
				$result['status'] = 'success';
				$result['message'] = 'Database has been updated.';
			}
		}
		
		if( isset($_POST['ajax']) )
		{
			$html = $this->renderPartial('credit_card_create', array(
				'model' => $model,
			), true);

			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}
	
	public function actionViewCreditCard()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		$authAccount = Yii::app()->user->account;
		
		$currentValues = array();
		
		$model = CustomerCreditCard::model()->findByPk($_POST['id']);
		
		if( isset($_POST['ajax']) )
		{
			$html = $this->renderPartial('credit_card_update', array(
				'model' => $model,
				'viewOnly' => true,
			), true);

			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}
	
	public function actionUpdateCreditCard()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		$authAccount = Yii::app()->user->account;
		
		$currentValues = array();
		
		$model = CustomerCreditCard::model()->findByPk($_POST['id']);
		
		if( isset($_POST['CustomerCreditCard']) )
		{
			$currentValues = $model->attributes;
			
			$model->attributes = $_POST['CustomerCreditCard'];	
			
			if( empty($_POST['CustomerCreditCard']['nick_name']) )
			{
				$sameCreditCardType = CustomerCreditCard::model()->findAll(array(
					'condition' => 'customer_id = :customer_id AND credit_card_type = :credit_card_type AND status=1',
					'params' => array(
						':customer_id' => $model->customer_id,
						':credit_card_type' => $model->credit_card_type,
					),
				));

				if( count($sameCreditCardType) > 0 )
				{
					$tempNickName = $model->credit_card_type;
					$tempNickName .= ' ' . count($sameCreditCardType) + 1;
				}
				else
				{
					$tempNickName = $model->credit_card_type;
				}
				
				$model->nick_name = $tempNickName;
			}
			
			$difference = array_diff($model->attributes, $currentValues);
			
			if( $model->save(false) )
			{
				if( $difference )
				{
					$updateFields = '';
				
					foreach( $difference as $attributeName => $value)
					{
						if( $attributeName == 'credit_card_number' )
						{
							if( $model->credit_card_type == 'Amex' )
							{
								$updateFields .= $model->getAttributeLabel($attributeName) .' changed from ************'.substr($currentValues[$attributeName], -4).' to ************'.substr($value, -4).', ';
							}
							else
							{
								$updateFields .= $model->getAttributeLabel($attributeName) .' changed from ***********'.substr($currentValues[$attributeName], -4).' to ***********'.substr($value, -4).', ';
							}
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
						'customer_id' => $model->customer_id,
						'user_account_id' => $authAccount->id,
						'page_name' => 'Credit Card',
						'content' => $updateFields,
						'old_data' => json_encode($currentValues),
						'new_data' => json_encode($model->attributes),
						'type' => $history::TYPE_UPDATED,
					));

					$history->save(false);
				}
			
				$result['status'] = 'success';
				$result['message'] = 'Database has been updated.';
			}
		}
		
		if( isset($_POST['ajax']) )
		{
			$html = $this->renderPartial('credit_card_update', array(
				'model' => $model,
			), true);

			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}
	
	public function actionDeleteCreditCard()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		$authAccount = Yii::app()->user->account;
		
		$model = CustomerCreditCard::model()->findByPk($_POST['id']);
		$model->status = 3;
		
		if( $model->save(false) )
		{
			$history = new CustomerHistory;
			
			$history->setAttributes(array(
				'model_id' => $model->id, 
				'customer_id' => $model->customer_id,
				'user_account_id' => $authAccount->id,
				'page_name' => 'Credit Card',
				'content' => $model->credit_card_type.' '.substr($model->credit_card_number, -4),
				'type' => $history::TYPE_DELETED,
			));

			$history->save(false);
			
			$result['status'] = 'success';
		}
		
		echo json_encode($result);
	}
	
	public function actionSetDefaultCreditCard()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		$model = CustomerCreditCard::model()->findByPk($_POST['id']);
		
		if( $model )
		{
			CustomerCreditCard::model()->updateAll(array('is_preferred' => 0), 'customer_id='.$model->customer_id);		
			CustomerEcheck::model()->updateAll(array('is_preferred' => 0), 'customer_id='.$model->customer_id);		
			
			$model->is_preferred = 1;
			
			if( $model->save(false) )
			{
				$result['status'] = 'success';
			}
		}
		
		echo json_encode($result);
	}

	
	
	//Authorize.net Functions 
	
	public function actionProcessTransaction()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		$authAccount = Yii::app()->user->account;
		
		$customer = Customer::model()->find(array(
			'condition' => 'id = :id',
			'params' => array(
				':id' => $_POST['customer_id'],
			),
		));
		
		$model = new CustomerBilling;
		$model->customer_id = $customer->id;		
		$model->account_id = $authAccount->id;	
		
	
		if( isset($_POST['CustomerBilling']) )
		{
			$model->attributes = $_POST['CustomerBilling'];
			$model->transaction_type = 'Charge';
			
			if( isset($_POST['method']) )
			{
				$method = explode('-', $_POST['method']);
				$type = $method[0];
				$cardId = $method[1];
				
				if( $type == 'creditCard' )
				{
					$defaultCreditCard = CustomerCreditCard::model()->findByPk($cardId);
					
					$model->credit_card_id = $defaultCreditCard->id;	
					$model->credit_card_type = $defaultCreditCard->credit_card_type;	
					$model->credit_card_number = $defaultCreditCard->credit_card_number;	
					$model->credit_card_security_code = $defaultCreditCard->security_code;	
					$model->credit_card_expiration_month = $defaultCreditCard->expiration_month;	
					$model->credit_card_expiration_year = $defaultCreditCard->expiration_year;	
				}
				
				if( $type == 'echeck' )
				{
					$defaultEcheck = CustomerEcheck::model()->findByPk($cardId);
					
					$model->echeck_id = $defaultEcheck->id;
					$model->ach_account_name = $defaultEcheck->account_name;
					$model->ach_account_number = $defaultEcheck->account_number;
					$model->ach_routing_number = $defaultEcheck->routing_number;
					$model->ach_account_type = $defaultEcheck->account_type;
					$model->ach_bank_name = $defaultEcheck->institution_name;
				}
				
				$model->payment_method = $type;
				
				if( $model->save(false) )
				{
					$updateBillingRecord = CustomerBilling::model()->findByPk($model->id);
					
					Yii::import('application.vendor.*');
					require ('anet_php_sdk/AuthorizeNet.php');
					
					//sandbox
					// define("AUTHORIZENET_API_LOGIN_ID", "5CzTeH72f98D");	
					// define("AUTHORIZENET_TRANSACTION_KEY", "8n25bCL72432SpR9");	
					// define("AUTHORIZENET_SANDBOX", true);
					
					//live
					define("AUTHORIZENET_API_LOGIN_ID", "8Wtz3C6a");	
					define("AUTHORIZENET_TRANSACTION_KEY", "8YEBD7n3Z64r3x39");
					define("AUTHORIZENET_SANDBOX", false);
					
					$authorizeTransaction = new AuthorizeNetAIM;
					
					$authorizeTransaction->setSandbox(AUTHORIZENET_SANDBOX);

					if( $model->payment_method == 'creditCard' )
					{
						$authorizeTransaction->setFields(array(
							// 'invoice_num' => ,
							'amount' => number_format($model->amount, 2),
							'first_name' => $defaultCreditCard->first_name,
							'last_name' => $defaultCreditCard->last_name,
							'email' => $customer->email_address,
							'card_num' => $defaultCreditCard->credit_card_number, 
							'card_code' => $defaultCreditCard->security_code,
							'exp_date' => $defaultCreditCard->expiration_month . $defaultCreditCard->expiration_year,
							'address' => $defaultCreditCard->address,
							'city' => $defaultCreditCard->city,
							'state' => $defaultCreditCard->state,
							'zip' => $defaultCreditCard->zip,
						));
					}
					
					if( $model->payment_method == 'echeck' )
					{
						$authorizeTransaction->amount = number_format($model->amount, 2);
									
						$authorizeTransaction->setECheck(
							$defaultEcheck->routing_number,
							$defaultEcheck->account_number,
							$defaultEcheck->account_type,
							$defaultEcheck->institution_name,
							$defaultEcheck->account_name,
							'WEB'
						);
					}
					
					$response = $authorizeTransaction->authorizeAndCapture();
									
					$request  = new AuthorizeNetTD;
					$response_TransactionDetails = $request->getTransactionDetails($response->transaction_id);

					
					if($response_TransactionDetails->xml->messages->resultCode == 'Ok')
					{
						$transaction_Details = $response_TransactionDetails->xml->transaction;
						$order = $transaction_Details->order;
						$anetCustomer = $transaction_Details->customer;
						$billTo = $transaction_Details->billTo;
						
						$billing_Date = date('Y-m-d H:i:s', strtotime($transaction_Details->submitTimeUTC));
						
						if($updateBillingRecord)
						{
							$updateBillingRecord->setAttributes(array(
								'anet_transId' => $transaction_Details->transId,
								'anet_invoiceNumber' => $order->invoiceNumber,
								'anet_submitTimeUTC' => $transaction_Details->submitTimeUTC,
								'anet_submitTimeLocal' => $transaction_Details->submitTimeLocal,
								'anet_transactionType' => $transaction_Details->transactionType,
								'anet_transactionStatus' =>$transaction_Details->transactionStatus,
								'anet_responseCode' => $transaction_Details->responseCode,
								'anet_responseReasonCode'=> $transaction_Details->responseReasonCode,
								'anet_responseReasonDescription'=> $transaction_Details->responseReasonDescription,
								'anet_authCode'=> $transaction_Details->authCode,
								'anet_AVSResponse'=> $transaction_Details->AVSResponse,
								'anet_cardCodeResponse'=> $transaction_Details->cardCodeResponse,
								'anet_authAmount'=> $transaction_Details->authAmount,
								'anet_settleAmount'=> $transaction_Details->settleAmount,
								'anet_taxExempt'=> $transaction_Details->taxExempt,
								'anet_customer_Email'=> $anetCustomer->email,
								'anet_billTo_firstName'=> $billTo->firstName,
								'anet_billTo_lastName'=> $billTo->lastName,
								'anet_billTo_address'=> $billTo->address,
								'anet_billTo_city'=> $billTo->city,
								'anet_billTo_state'=> $billTo->state,
								'anet_billTo_zip'=> $billTo->zip,
								'anet_recurringBilling' => $transaction_Details->recurringBilling,
								'anet_product' => $transaction_Details->product,
								'anet_marketType' => $transaction_Details->marketType
							));
							
							if($updateBillingRecord->save(false))
							{											
								if($response->approved)
								{
									// Transaction approved! Do your logic here.
									$result['status'] = 'success';
									$result['message'] = 'Charge successful';
										
									//Send Invoice Email
									// Yii::import('application.extensions.phpmailer.JPhpMailer');
							
									// $mail = new JPhpMailer;       											
							
									// $mail->SetFrom('customerservice@engagex.com');
									
									// $mail->Subject = 'Engagex Receipt';
									
									// $mail->AddAddress( $customer->email_address );
									
									// $mail->AddBCC('jim.campbell@engagex.com');
									// $mail->AddBCC('erwin.datu@engagex.com');
									 
									// $mail->MsgHTML( $model->getHtmlChargeReceipt('Charge') );
									 
									// if($mail->Send())
									// {		
										// $result['status'] = 'success';
										// $result['message'] = 'Charge successful & email sent to customer';
									// }
									// else
									// {	
										// $result['status'] = 'error';
										// $result['message'] = 'Charge unsuccessful';
									// }
								
								}
								else
								{
									$result['status'] = 'error';
									$result['message'] = 'Transaction error: ' . $response->response_reason_code . ' - ' . $response->response_reason_text;
								}
							}
						}
					}
					else
					{
						if($updateBillingRecord)
						{
							$updateBillingRecord->anet_transId = $response_TransactionDetails->xml->messages->message->code.': '.$response_TransactionDetails->xml->messages->message->text;
							$updateBillingRecord->save(false);
						}
					}
						
					
					$result['status'] = 'success';
					$result['message'] = 'Database has been updated.';
				}
			}
			else
			{
				$result['message'] = 'No default payment method selected.';
			}
		}
		
		if( isset($_POST['ajax']) )
		{
			$customerSkills = CustomerSkill::model()->findAll(array(
				'condition' => 'customer_id = :customer_id AND status = :status',
				'params' => array(
					':customer_id' => $customer->id,
					':status' => CustomerSkill::STATUS_ACTIVE,
				),
			));
			
			$contractOptions = array();
			
			if( $customerSkills )
			{
				foreach( $customerSkills as $customerSkill )
				{
					$contractOptions[$customerSkill->contract_id] = $customerSkill->contract->contract_name;
				}
			}	
			
			
			$billingPeriodOptions = array();
		
			foreach( range(2015, date('Y')+1) as $year)
			{
				foreach( range( $year == 2015 ? date('m') : 1 , 12) as $monthNumber )
				{
					$monthName = date('M', strtotime($year.'-'.$monthNumber.'-01'));
					
					$billingPeriodOptions[$monthName.' '.$year] = $monthName.' '.$year;
				}
			}
			
			$html = $this->renderPartial('process_transaction', array(
				'model' => $model,
				'contractOptions' => $contractOptions,
				'billingPeriodOptions' => $billingPeriodOptions,
			), true);

			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}

	public function actionVoidTransaction()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		$authAccount = Yii::app()->user->account;
		
		$transaction = CustomerBilling::model()->findByPk($_POST['id']);
		
		$customer = Customer::model()->find(array(
			'condition' => 'id = :id',
			'params' => array(
				':id' => $transaction->customer_id,
			),
		));
		
		$model = new CustomerBilling;
		
		if( isset($_POST['CustomerBilling']) )
		{
			$model->setAttributes(array(
				'customer_id' => $transaction->customer_id,
				'account_id' => $authAccount->id,
				'customer_id' => $transaction->customer_id,
				'credit_card_id' => $transaction->credit_card_id,
				'contract_id' => $transaction->contract_id,
				'amount' => $transaction->amount,
				'reference_transaction_id' => $transaction->id,
				'anet_transId' => time(),
				'description' => $_POST['CustomerBilling']['description'],
				'transaction_type' => 'Void',
			));
			
			if( $model->save(false) )
			{	
				$updateModel = CustomerBilling::model()->findByPk($model->id);
				
				Yii::import('application.vendor.*');
				require ('anet_php_sdk/AuthorizeNet.php');
				
				//sandbox
				// define("AUTHORIZENET_API_LOGIN_ID", "5CzTeH72f98D");	
				// define("AUTHORIZENET_TRANSACTION_KEY", "8n25bCL72432SpR9");	
				// define("AUTHORIZENET_SANDBOX", true);
				
				//live
				define("AUTHORIZENET_API_LOGIN_ID", "8Wtz3C6a");	
				define("AUTHORIZENET_TRANSACTION_KEY", "8YEBD7n3Z64r3x39");
				define("AUTHORIZENET_SANDBOX", false);	
				
				$authorizeTransaction = new AuthorizeNetAIM;
				$authorizeTransaction->setSandbox(AUTHORIZENET_SANDBOX);
				$response = $authorizeTransaction->void($transaction->anet_transId);

				if ($response->approved)
				{
					$request  = new AuthorizeNetTD;
					$response_TransactionDetails = $request->getTransactionDetails($response->transaction_id);
					
					if($response_TransactionDetails->xml->messages->resultCode == 'Ok')
					{
						$transaction_Details = $response_TransactionDetails->xml->transaction;
						$order = $transaction_Details->order;
						$anetCustomer = $transaction_Details->customer;
						$billTo = $transaction_Details->billTo;
						
						$billing_Date = date('Y-m-d H:i:s', strtotime($transaction_Details->submitTimeUTC));						
							
						if($updateModel)
						{
							$updateModel->setAttributes(array(
								'anet_transId' => $transaction_Details->transId,
								'anet_invoiceNumber' => $order->invoiceNumber,
								'anet_submitTimeUTC' => $transaction_Details->submitTimeUTC,
								'anet_submitTimeLocal' => $transaction_Details->submitTimeLocal,
								'anet_transactionType' => $transaction_Details->transactionType,
								'anet_transactionStatus' =>$transaction_Details->transactionStatus,
								'anet_responseCode' => $transaction_Details->responseCode,
								'anet_responseReasonCode'=> $transaction_Details->responseReasonCode,
								'anet_responseReasonDescription'=> $transaction_Details->responseReasonDescription,
								'anet_authCode'=> $transaction_Details->authCode,
								'anet_AVSResponse'=> $transaction_Details->AVSResponse,
								'anet_cardCodeResponse'=> $transaction_Details->cardCodeResponse,
								'anet_authAmount'=> $transaction_Details->authAmount,
								'anet_settleAmount'=> $transaction_Details->settleAmount,
								'anet_taxExempt'=> $transaction_Details->taxExempt,
								'anet_customer_Email'=> $anetCustomer->email,
								'anet_billTo_firstName'=> $billTo->firstName,
								'anet_billTo_lastName'=> $billTo->lastName,
								'anet_billTo_address'=> $billTo->address,
								'anet_billTo_city'=> $billTo->city,
								'anet_billTo_state'=> $billTo->state,
								'anet_billTo_zip'=> $billTo->zip,
								'anet_recurringBilling' => $transaction_Details->recurringBilling,
								'anet_product' => $transaction_Details->product,
								'anet_marketType' => $transaction_Details->marketType
							));
							
							if($updateModel->save(false))
							{
								$result['status'] = 'success';
								$result['message'] = 'Transaction voided.';
								
								//Send Invoice Email
								// Yii::import('application.extensions.phpmailer.JPhpMailer');
						
								// $mail = new JPhpMailer;      											
						
								// $mail->SetFrom('customerservice@engagex.com');
								
								// $mail->Subject = 'Engagex Receipt';
								
								// $mail->AddAddress( $customer->email_address );
								
								// $mail->AddBCC('jim.campbell@engagex.com');
								// $mail->AddBCC('erwin.datu@engagex.com');
								 
								// $mail->MsgHTML( $model->getHtmlChargeReceipt('Void') );
								 
								// if($mail->Send())
								// {		
									// $result['status'] = 'success';
									// $result['message'] = 'Void successful & email sent to customer';
								// }
								// else
								// {	
									// $result['status'] = 'error';
									// $result['message'] = 'Void successful. Mail not sent';
								// }
							}
							else
							{
								$result['status'] = 'error';
								$result['message'] = 'Database error.';
							}
						}
						else
						{
							$result['status'] = 'error';
							$result['message'] = 'Database error.';
						}
					}
				}
				else
				{
					$status = 'error';
					$result['message'] = 'Transaction error: ' . $response->response_reason_code . ' - ' . $response->response_reason_text;
					
					if( $updateModel )
					{
						$updateModel->anet_transId = $response->response_reason_code.': '.$response->response_reason_text;
						$updateModel->save(false);
					}
				}
			}
		}
		
		if( isset($_POST['ajax']) )
		{
			$html = $this->renderPartial('void_form', array(
				'model' => $model,
				'transaction' => $transaction,
			), true);

			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}
	
	public function actionRefundTransaction()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		$authAccount = Yii::app()->user->account;
		
		$transaction = CustomerBilling::model()->findByPk($_POST['id']);
		
		$customer = Customer::model()->find(array(
			'condition' => 'id = :id',
			'params' => array(
				':id' => $transaction->customer_id,
			),
		));
		
		$model = new CustomerBilling;
		
		if( isset($_POST['CustomerBilling']) )
		{
			$totalAmount = $transaction->amount;
			
			$partialRefundRecords = CustomerBilling::model()->findAll(array(
				'condition' => 'reference_transaction_id = :reference_transaction_id AND transaction_type="Partial Refund"',
				'params' => array(
					':reference_transaction_id' => $transaction->id,
				),
			));
			
			if( $partialRefundRecords )
			{
				$existingAmount = 0;
					
				foreach( $partialRefundRecords as $partialRefundRecord )
				{
					$existingAmount += $partialRefundRecord->amount;
				}
				
				$totalAmount = ($totalAmount - $existingAmount);
			}
			
			$model->setAttributes(array(
				'customer_id' => $transaction->customer_id,
				'account_id' => $authAccount->id,
				'customer_id' => $transaction->customer_id,
				'credit_card_id' => $transaction->credit_card_id,
				'contract_id' => $transaction->contract_id,
				'amount' => $totalAmount,
				'reference_transaction_id' => $transaction->id,
				'anet_transId' => time(),
				'description' => $_POST['CustomerBilling']['description'],
				'transaction_type' => 'Refund',
			));
			
			if( $model->save(false) )
			{	
				$updateModel = CustomerBilling::model()->findByPk($model->id);
				
				Yii::import('application.vendor.*');
				require ('anet_php_sdk/AuthorizeNet.php');
				
				//sandbox
				// define("AUTHORIZENET_API_LOGIN_ID", "5CzTeH72f98D");	
				// define("AUTHORIZENET_TRANSACTION_KEY", "8n25bCL72432SpR9");	
				// define("AUTHORIZENET_SANDBOX", true);
				
				//live
				define("AUTHORIZENET_API_LOGIN_ID", "8Wtz3C6a");	
				define("AUTHORIZENET_TRANSACTION_KEY", "8YEBD7n3Z64r3x39");
				define("AUTHORIZENET_SANDBOX", false);
				
				$authorizeTransaction = new AuthorizeNetAIM;
				$authorizeTransaction->setSandbox(AUTHORIZENET_SANDBOX);
				$response = $authorizeTransaction->credit($transaction->anet_transId, $transaction->amount, $transaction->credit_card_number);

				if ($response->approved)
				{
					$request  = new AuthorizeNetTD;
					$response_TransactionDetails = $request->getTransactionDetails($response->transaction_id);
					
					if($response_TransactionDetails->xml->messages->resultCode == 'Ok')
					{
						$transaction_Details = $response_TransactionDetails->xml->transaction;
						$order = $transaction_Details->order;
						$anetCustomer = $transaction_Details->customer;
						$billTo = $transaction_Details->billTo;
						
						$billing_Date = date('Y-m-d H:i:s', strtotime($transaction_Details->submitTimeUTC));						
						
						
						if($updateModel)
						{
							$updateModel->setAttributes(array(
								'anet_transId' => $transaction_Details->transId,
								'anet_invoiceNumber' => $order->invoiceNumber,
								'anet_submitTimeUTC' => $transaction_Details->submitTimeUTC,
								'anet_submitTimeLocal' => $transaction_Details->submitTimeLocal,
								'anet_transactionType' => $transaction_Details->transactionType,
								'anet_transactionStatus' =>$transaction_Details->transactionStatus,
								'anet_responseCode' => $transaction_Details->responseCode,
								'anet_responseReasonCode'=> $transaction_Details->responseReasonCode,
								'anet_responseReasonDescription'=> $transaction_Details->responseReasonDescription,
								'anet_authCode'=> $transaction_Details->authCode,
								'anet_AVSResponse'=> $transaction_Details->AVSResponse,
								'anet_cardCodeResponse'=> $transaction_Details->cardCodeResponse,
								'anet_authAmount'=> $transaction_Details->authAmount,
								'anet_settleAmount'=> $transaction_Details->settleAmount,
								'anet_taxExempt'=> $transaction_Details->taxExempt,
								'anet_customer_Email'=> $anetCustomer->email,
								'anet_billTo_firstName'=> $billTo->firstName,
								'anet_billTo_lastName'=> $billTo->lastName,
								'anet_billTo_address'=> $billTo->address,
								'anet_billTo_city'=> $billTo->city,
								'anet_billTo_state'=> $billTo->state,
								'anet_billTo_zip'=> $billTo->zip,
								'anet_recurringBilling' => $transaction_Details->recurringBilling,
								'anet_product' => $transaction_Details->product,
								'anet_marketType' => $transaction_Details->marketType
							));
							
							if($updateModel->save(false))
							{
								$status = 'success';
								$message = 'Transaction refunded.';
								
								//Send Invoice Email
								// Yii::import('application.extensions.phpmailer.JPhpMailer');
						
								// $mail = new JPhpMailer;    											
						
								// $mail->SetFrom('customerservice@engagex.com');
								
								// $mail->Subject = 'Engagex Receipt';
								
								// $mail->AddAddress( $customer->email_address );
								
								// $mail->AddBCC('jim.campbell@engagex.com');
								// $mail->AddBCC('erwin.datu@engagex.com');
								 
								// $mail->MsgHTML( $model->getHtmlChargeReceipt('Refund') );
								 
								// if($mail->Send())
								// {		
									// $result['status'] = 'success';
									// $result['message'] = 'Refund successful & email sent to customer';
								// }
								// else
								// {	
									// $result['status'] = 'error';
									// $result['message'] = 'Refund unsuccessful';
								// }
							}
							else
							{
								$result['status'] = 'error';
								$result['message'] = 'Database error.';
							}
						}
						else
						{
							$result['status'] = 'error';
							$result['message'] = 'Database error.';
						}
					}
				}
				else
				{
					$result['status'] = 'error';
					$result['message'] = 'Transaction error: ' . $response->response_reason_code . ' - ' . $response->response_reason_text;
					
					if( $updateModel )
					{
						$updateModel->anet_transId = $response->response_reason_code.': '.$response->response_reason_text;
						$updateModel->save(false);
					}
				}
			}
		}
		
		if( isset($_POST['ajax']) )
		{
			$html = $this->renderPartial('refund_form', array(
				'model' => $model,
				'transaction' => $transaction,
			), true);

			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}

	
	public function actionPartialRefundTransaction()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		$authAccount = Yii::app()->user->account;
		
		$model = new CustomerBilling;
		
		$transaction = CustomerBilling::model()->findByPk($_POST['id']);
	
		$customer = Customer::model()->find(array(
			'condition' => 'id = :id',
			'params' => array(
				':id' => $transaction->customer_id,
			),
		));
	
		if( isset($_POST['partialRefund_amount']) )
		{
			$model->setAttributes(array(
				'customer_id' => $transaction->customer_id,
				'account_id' => $authAccount->id,
				'customer_id' => $transaction->customer_id,
				'credit_card_id' => $transaction->credit_card_id,
				'contract_id' => $transaction->contract_id,
				'amount' => $_POST['partialRefund_amount'],
				'reference_transaction_id' => $transaction->id,
				'anet_transId' => time(),
				'description' => $_POST['partialRefund_memo'],
				'transaction_type' => 'Partial Refund',
			));
			
			if( $model->save(false) )
			{	
				$updateModel = CustomerBilling::model()->findByPk($model->id);
				
				Yii::import('application.vendor.*');
				require ('anet_php_sdk/AuthorizeNet.php');
				
				//sandbox
				// define("AUTHORIZENET_API_LOGIN_ID", "5CzTeH72f98D");	
				// define("AUTHORIZENET_TRANSACTION_KEY", "8n25bCL72432SpR9");	
				// define("AUTHORIZENET_SANDBOX", true);
				
				//live
				define("AUTHORIZENET_API_LOGIN_ID", "8Wtz3C6a");	
				define("AUTHORIZENET_TRANSACTION_KEY", "8YEBD7n3Z64r3x39");
				define("AUTHORIZENET_SANDBOX", false);
				
				$authorizeTransaction = new AuthorizeNetAIM;
				$authorizeTransaction->setSandbox(AUTHORIZENET_SANDBOX);
				$response = $authorizeTransaction->credit($transaction->anet_transId, $_POST['partialRefund_amount'], $transaction->credit_card_number);

				if ($response->approved)
				{
					$request  = new AuthorizeNetTD;
					$response_TransactionDetails = $request->getTransactionDetails($response->transaction_id);
					
					if($response_TransactionDetails->xml->messages->resultCode == 'Ok')
					{
						$transaction_Details = $response_TransactionDetails->xml->transaction;
						$order = $transaction_Details->order;
						$anetCustomer = $transaction_Details->customer;
						$billTo = $transaction_Details->billTo;
						
						$billing_Date = date('Y-m-d H:i:s', strtotime($transaction_Details->submitTimeUTC));
						
						if($updateModel)
						{
							$updateModel->setAttributes(array(
								'anet_transId' => $transaction_Details->transId,
								'anet_invoiceNumber' => $order->invoiceNumber,
								'anet_submitTimeUTC' => $transaction_Details->submitTimeUTC,
								'anet_submitTimeLocal' => $transaction_Details->submitTimeLocal,
								'anet_transactionType' => $transaction_Details->transactionType,
								'anet_transactionStatus' =>$transaction_Details->transactionStatus,
								'anet_responseCode' => $transaction_Details->responseCode,
								'anet_responseReasonCode'=> $transaction_Details->responseReasonCode,
								'anet_responseReasonDescription'=> $transaction_Details->responseReasonDescription,
								'anet_authCode'=> $transaction_Details->authCode,
								'anet_AVSResponse'=> $transaction_Details->AVSResponse,
								'anet_cardCodeResponse'=> $transaction_Details->cardCodeResponse,
								'anet_authAmount'=> $transaction_Details->authAmount,
								'anet_settleAmount'=> $transaction_Details->settleAmount,
								'anet_taxExempt'=> $transaction_Details->taxExempt,
								'anet_customer_Email'=> $anetCustomer->email,
								'anet_billTo_firstName'=> $billTo->firstName,
								'anet_billTo_lastName'=> $billTo->lastName,
								'anet_billTo_address'=> $billTo->address,
								'anet_billTo_city'=> $billTo->city,
								'anet_billTo_state'=> $billTo->state,
								'anet_billTo_zip'=> $billTo->zip,
								'anet_recurringBilling' => $transaction_Details->recurringBilling,
								'anet_product' => $transaction_Details->product,
								'anet_marketType' => $transaction_Details->marketType
							));
							
							if($updateModel->save(false))
							{
								$status = 'success';
								$message = 'Transaction refunded.';
								
								//Send Invoice Email
								// Yii::import('application.extensions.phpmailer.JPhpMailer');
						
								// $mail = new JPhpMailer;        											
						
								// $mail->SetFrom('customerservice@engagex.com');
								
								// $mail->Subject = 'Engagex Receipt';
								
								// $mail->AddAddress( $customer->email_address );
								
								// $mail->AddBCC('jim.campbell@engagex.com');
								// $mail->AddBCC('erwin.datu@engagex.com');
								 
								// $mail->MsgHTML( $model->getHtmlChargeReceipt('Partial Refund') );
								 
								// if($mail->Send())
								// {		
									// $result['status'] = 'success';
									// $result['message'] = 'Partial Refund successful & email sent to customer';
								// }
								// else
								// {	
									// $result['status'] = 'error';
									// $result['message'] = 'Partial Refund unsuccessful';
								// }
							}
							else
							{
								$result['status'] = 'error';
								$result['message'] = 'Database error.';
							}
						}
						else
						{
							$result['status'] = 'error';
							$result['message'] = 'Database error.';
						}
					}
				}
				else
				{
					$result['status'] = 'error';
					$result['message'] = 'Transaction error: ' . $response->response_reason_code . ' - ' . $response->response_reason_text;
					
					if( $updateModel )
					{
						$updateModel->anet_transId = $response->response_reason_code.': '.$response->response_reason_text;
						$updateModel->save(false);
					}
				}
			}
		}
		
		if( isset($_POST['ajax']) )
		{
			$totalAmount = $transaction->amount;
			
			$voidedRecordIds = array();
			
			$voidedRecords = CustomerBilling::model()->findAll(array(
				'condition' => 'customer_id = :customer_id AND transaction_type = :transaction_type',
				'params' => array(
					':customer_id' => $transaction->customer_id,
					':transaction_type' => 'Void',
				),
			));
			
			if( $voidedRecords )
			{
				foreach( $voidedRecords as $voidedRecord )
				{
					$voidedRecordIds[] = $voidedRecord->reference_transaction_id;
				}
			}
			
			if( $voidedRecordIds )
			{
				$partialRefundRecords = CustomerBilling::model()->findAll(array(
					'condition' => '
						reference_transaction_id = :reference_transaction_id 
						AND transaction_type IN ("Partial Refund") 
						AND id NOT IN ('.implode(', ', $voidedRecordIds).')
					',
					'params' => array(
						':reference_transaction_id' => $transaction->id,
					),
				));
			}
			else
			{
				$partialRefundRecords = CustomerBilling::model()->findAll(array(
					'condition' => '
						reference_transaction_id = :reference_transaction_id 
						AND transaction_type IN ("Partial Refund") 
					',
					'params' => array(
						':reference_transaction_id' => $transaction->id,
					),
				));
			}
			
			if( $partialRefundRecords )
			{
				$existingAmount = 0;
					
				foreach( $partialRefundRecords as $partialRefundRecord )
				{
					$existingAmount += $partialRefundRecord->amount;
				}
				
				$totalAmount = ($totalAmount - $existingAmount);
			}
			
			
			$html = $this->renderPartial('partial_refund_form', array(
				'model' => $model,
				'transaction' => $transaction,
				'totalAmount' => $totalAmount,
			), true);

			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}

	
	//Echeck Functions
	
	public function actionCreateEcheck()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		$authAccount = Yii::app()->user->account;
		
		$model = new CustomerEcheck;
	
		if( isset($_POST['CustomerEcheck']) )
		{
			$model->attributes = $_POST['CustomerEcheck'];

			$model->customer_id = $_POST['customer_id'];	
			
			$existingCreditCard = CustomerCreditCard::model()->count(array(
				'condition' => 'customer_id = :customer_id AND status=1',
				'params' => array(
					':customer_id' => $_POST['customer_id']
				),
			));
			
			$existingEcheck = CustomerEcheck::model()->count(array(
				'condition' => 'customer_id = :customer_id AND status=1',
				'params' => array(
					':customer_id' => $_POST['customer_id']
				),
			));

			if( $existingCreditCard == 0 && $existingEcheck == 0 )
			{
				$model->is_preferred = 1;
			}
			
			if( $model->save(false) )
			{
				$history = new CustomerHistory;
				
				$history->setAttributes(array(
					'model_id' => $model->id, 
					'customer_id' => $model->customer_id,
					'user_account_id' => $authAccount->id,
					'page_name' => 'ECheck',
					'content' => $model->account_type.' '.$model->account_name,
					'type' => $history::TYPE_ADDED,
				));

				$history->save(false);
				
				$result['status'] = 'success';
				$result['message'] = 'Database has been updated.';
			}
		}
		
		if( isset($_POST['ajax']) )
		{
			$html = $this->renderPartial('echeck_create', array(
				'model' => $model,
			), true);

			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}
	
	public function actionUpdateEcheck()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		$authAccount = Yii::app()->user->account;
		
		$currentValues = array();
		
		$model = CustomerEcheck::model()->findByPk($_POST['id']);
		
		if( isset($_POST['CustomerEcheck']) )
		{
			$currentValues = $model->attributes;
			
			$model->attributes = $_POST['CustomerEcheck'];	
			
			$difference = array_diff($model->attributes, $currentValues);
			
			if( $model->save(false) )
			{
				if( $difference )
				{
					$updateFields = '';
				
					foreach( $difference as $attributeName => $value)
					{
						$updateFields .= $model->getAttributeLabel($attributeName) .' changed from '.$currentValues[$attributeName].' to '.$value.', ';
					}
					
					$updateFields = rtrim($updateFields, ', ');
					
					$history = new CustomerHistory;
					
					$history->setAttributes(array(
						'model_id' => $model->id, 
						'customer_id' => $model->customer_id,
						'user_account_id' => $authAccount->id,
						'page_name' => 'ECheck',
						'content' => $updateFields,
						'old_data' => json_encode($currentValues),
						'new_data' => json_encode($model->attributes),
						'type' => $history::TYPE_UPDATED,
					));

					$history->save(false);
				}
			
				$result['status'] = 'success';
				$result['message'] = 'Database has been updated.';
			}
		}
		
		if( isset($_POST['ajax']) )
		{
			$html = $this->renderPartial('echeck_update', array(
				'model' => $model,
			), true);

			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}

	public function actionDeleteEcheck()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		$authAccount = Yii::app()->user->account;
		
		$model = CustomerEcheck::model()->findByPk($_POST['id']);
		$model->status = 3;
		
		if( $model->save(false) )
		{
			$history = new CustomerHistory;
			
			$history->setAttributes(array(
				'model_id' => $model->id, 
				'customer_id' => $model->customer_id,
				'user_account_id' => $authAccount->id,
				'page_name' => 'Echeck',
				'content' => $model->account_type.' '.$model->account_name,
				'type' => $history::TYPE_DELETED,
			));

			$history->save(false);
			
			$result['status'] = 'success';
		}
		
		echo json_encode($result);
	}

	public function actionSetDefaultEcheck()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		$model = CustomerEcheck::model()->findByPk($_POST['id']);
		
		if( $model )
		{
			CustomerCreditCard::model()->updateAll(array('is_preferred' => 0), 'customer_id='.$model->customer_id);		
			CustomerEcheck::model()->updateAll(array('is_preferred' => 0), 'customer_id='.$model->customer_id);		
			
			$model->is_preferred = 1;
			
			if( $model->save(false) )
			{
				$result['status'] = 'success';
			}
		}
		
		echo json_encode($result);
	}

	
	public function actionDownloadReceipt($id)
	{
		$model = CustomerBilling::model()->findByPk($id);
		
		Yii::import('ext.CustomerBillingReceiptPDF');
		
		$pdf = new CustomerBillingReceiptPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		spl_autoload_register(array('YiiBase','autoload'));
         
		
		// set default header data
		// $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

		// remove default header/footer
		// $pdf->setPrintHeader(true);
		// $pdf->setPrintFooter(true);

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		// set paddings
		// $pdf->setCellPaddings(0,0,0,0);
		
		// set margins
		$pdf->SetMargins(10,20);
		$pdf->setHeaderMargin(0);
		$pdf->setFooterMargin(0);
		$pdf->SetAutoPageBreak(true, 40);


		//Set zoom 90%
		$pdf->SetDisplayMode(100,'SinglePage','UseNone');

		// set font
		$pdf->SetFont('freesans', '', 11);

		$pdf->AddPage();
		

		//Write the html
		$html = $this->renderPartial('receiptLayout', array(
			'model'=>$model,
		), true);
		
		//Convert the Html to a pdf document
		$pdf->writeHTML($html, true, false, true, false, '');
		
		// reset pointer to the last page
		$pdf->lastPage();

		//Close and output PDF document
		$pdf->Output( $model->customer->getFullName() . '.pdf', 'I');
		Yii::app()->end();
	}
}

?>