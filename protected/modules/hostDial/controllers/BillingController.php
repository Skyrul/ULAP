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
					'creatEcheck', 'updateEcheck', 'deleteEcheck', 'setDefaultEcheck', 'downloadReceipt',
					'createCredit', 'updateCredit', 'deleteCredit'
				),
				'users'=>array('*'),
			),
		);
	}
	
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
		
		$credits = CustomerCredit::model()->findAll(array(
			'condition' => 'customer_id = :customer_id AND status=1',
			'params' => array(
				':customer_id' => $customer->id,
			),
			'order'=> 'date_created DESC',
		));
		
		$creditCardDataProvider=new CArrayDataProvider($creditCards, array(
			'pagination'=>array(
	            'pageSize'=>100,
	        ),
		));
		
		$echeckDataProvider=new CArrayDataProvider($echecks, array(
			'pagination'=>array(
	            'pageSize'=>100,
	        ),
		));
		
		$creditDataProvider=new CArrayDataProvider($credits, array(
			'pagination'=>array(
	            'pageSize'=>100,
	        ),
		));
		
		$transactions = CustomerBilling::model()->findAll(array(
			'condition' => 'customer_id = :customer_id AND id NOT IN(12250, 12251)',
			'params' => array(
				':customer_id' => $customer->id,
			),
			'order'=> 'date_created DESC',
		));
		
		$transactionDataProvider=new CArrayDataProvider($transactions, array(
			'pagination'=>array(
	            'pageSize'=>100,
	        ),
		));
		
		
		$this->render('index', array(
			'customer' => $customer,
			'creditCardDataProvider' => $creditCardDataProvider,
			'transactionDataProvider' => $transactionDataProvider,
			'echeckDataProvider' => $echeckDataProvider,
			'creditDataProvider' => $creditDataProvider,
		));
		
	}
	
	
	public function getCustomerContractCreditAndSubsidy($customer, $contract, $billing_period, $billing_type)
	{
		$contractCreditSubsidys = array();
		$customerSkills = CustomerSkill::model()->findAll(array(
			'with' => 'customer',
			'condition' => '
				t.customer_id = :customer_id AND t.contract_id = :contract_id
				AND t.status=1 
				AND customer.status=1
				AND customer.is_deleted=0
			',
			'params' => array(
				':customer_id' => $customer->id,
				':contract_id' => $contract->id,
			),

		));
		
		foreach($customerSkills as $customerSkill)
		{
			// $customerRemoved = CustomerBillingWindowRemoved::model()->find(array(
				// 'condition' => '
					// customer_id = :customer_id 
					// AND skill_id = :skill_id 
					// AND MONTH(date_created) = MONTH(NOW())
					// AND YEAR(date_created) = YEAR(NOW())
				// ',
				// 'params' => array(
					// ':customer_id' => $customerSkill->customer_id,
					// ':skill_id' => $customerSkill->skill_id,
				// ),
			// ));
			
			// if( $customer->id == 698 )
			// {
				// echo 'contract: ' . $contract->id;
				// echo ' | ';
				// echo 'customerRemoved: ' . count($customerRemoved);
				// exit;
			// }
				
			if( isset($customerSkill->contract) && $customerSkill && time() >= strtotime($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00' )
			{
				$isBilled = false;
				
				$contract = $customerSkill->contract;
				$contractCreditSubsidys[$contract->id]['contract_name'] = $contract->contract_name;
				$contractCreditSubsidys[$contract->id]['totalCreditAmount'] = 0;
				$contractCreditSubsidys[$contract->id]['totalSubsidyAmount'] = 0;
				
				$existingBillingForCurrentMonth = CustomerBilling::model()->find(array(
					'condition' => '
						customer_id = :customer_id 
						AND contract_id = :contract_id
						AND transaction_type = "Charge"
						AND billing_period = :billing_period
						AND anet_responseCode = 1
					',
					'params' => array(
						':customer_id' => $customerSkill->customer_id,
						':contract_id' => $customerSkill->contract_id,
						':billing_period' => $billing_period
					),
					'order' => 'date_created DESC'
				));
				
				if( $existingBillingForCurrentMonth )
				{
					$isBilled = true;
					
					$existingBillingForCurrentMonthVoidorRefund = CustomerBilling::model()->find(array(
						'condition' => '
							customer_id = :customer_id 
							AND contract_id = :contract_id
							AND anet_responseCode = 1
							AND reference_transaction_id = :reference_transaction_id
							AND (
								transaction_type = "Void"
								OR transaction_type = "Refund"
								OR transaction_type = "Partial Refund"
							)
						',
						'params' => array(
							':customer_id' => $customerSkill->customer_id,
							':contract_id' => $customerSkill->contract_id,
							':reference_transaction_id' => $existingBillingForCurrentMonth->id,
						),
						'order' => 'date_created DESC'
					)); 
					
					if( $existingBillingForCurrentMonthVoidorRefund )
					{
						if( $existingBillingForCurrentMonthVoidorRefund->transaction_type == 'Partial Refund' )
						{
							$latestCharge = CustomerBilling::model()->find(array(
								'condition' => '
									customer_id = :customer_id 
									AND contract_id = :contract_id
									AND anet_responseCode = 1
									AND transaction_type = "Charge"
								',
								'params' => array(
									':customer_id' => $customerSkill->customer_id,
									':contract_id' => $customerSkill->contract_id,
								),
								'order' => 'date_created DESC'
							));
							
							if( $existingBillingForCurrentMonthVoidorRefund->amount == $latestCharge->amount )
							{
								$isBilled = false;
							}
							else
							{
								$isBilled = true;
							}
						}
						else
						{
							$isBilled = false;
						}
					}
					else
					{
						$isBilled = true;
					}
				}
			
				$totalLeads = 0;
				$totalAmount = 0;
				$subsidyAmount = 0;
				$month = '';
				$latestTransactionType = '';
				$latestTransactionStatus = '';
				
				if($contract->fulfillment_type != null )
				{
					if( $contract->fulfillment_type == Contract::TYPE_FULFILLMENT_GOAL_VOLUME )
					{
						if( !empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME]) )
						{
							foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME] as $key => $subsidyLevel )
							{
								$customerSkillLevelArray = $customerSkill->getCustomerSkillLevelArray();
								$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;

								if( $customerSkillLevelArrayGroup != null )
								{							
									if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
									{
										$totalLeads += ( $subsidyLevel['goal'] * $customerSkillLevelArrayGroup->quantity );
										$totalAmount += ( $customerSkillLevelArrayGroup->quantity * $subsidyLevel['amount'] );
									}
								}
							}
						}
						
						$customerExtras = CustomerExtra::model()->findAll(array(
							'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id AND status=1 AND year = :year AND month = :month',
							'params' => array(
								':customer_id' => $customerSkill->customer_id,
								':contract_id' => $customerSkill->contract_id,
								':skill_id' => $customerSkill->skill_id,
								':year' => date('Y'),
								':month' => date('m'),
							),
						));
						
						if( $customerExtras )
						{
							foreach( $customerExtras as $customerExtra )
							{
								$totalLeads += $customerExtra->quantity;
							}
						}
					}
					else
					{
						if( !empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME]) )
						{
							foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME] as $key => $subsidyLevel )
							{
								$customerSkillLevelArray = $customerSkill->getCustomerSkillLevelArray();
								
								$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;
								
								if( $customerSkillLevelArrayGroup != null )
								{
									if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
									{
										$totalLeads += ( $subsidyLevel['high'] * $customerSkillLevelArrayGroup->quantity );
										$totalAmount += ( $customerSkillLevelArrayGroup->quantity * $subsidyLevel['amount'] );
									}
								}
							}
						}
						
						$customerExtras = CustomerExtra::model()->findAll(array(
							'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id AND status=1 AND year = :year AND month = :month',
							'params' => array(
								':customer_id' => $customerSkill->customer_id,
								':contract_id' => $customerSkill->contract_id,
								':skill_id' => $customerSkill->skill_id,
								':year' => date('Y'),
								':month' => date('m'),
							),
						));
						
						if( $customerExtras )
						{
							foreach( $customerExtras as $customerExtra )
							{
								$totalLeads += $customerExtra->quantity;
							}
						}
					}
				
					$contractCreditSubsidys[$contract->id]['totalAmount'] = $totalAmount;
					
					$customerSkillSubsidyLevel = CustomerSkillSubsidyLevel::model()->find(array(
						'condition' => 'customer_id = :customer_id AND customer_skill_id = :customer_skill_id',
						'params' => array(
							':customer_id' => $customerSkill->customer_id,
							':customer_skill_id' => $customerSkill->id,
						),
					));
					
					$customerSkillSubsidy = CustomerSkillSubsidy::model()->find(array(
						'condition' => 'customer_id = :customer_id AND customer_skill_id = :customer_skill_id',
						'params' => array(
							':customer_id' => $customerSkill->customer_id,
							':customer_skill_id' => $customerSkill->id,
						),
					));
					
					// if( $customerSkillSubsidyLevel )
					if( !empty($customerSkillSubsidyLevel) && !empty($customerSkillSubsidy) && $customerSkillSubsidy->status == CustomerSkillSubsidy::STATUS_ACTIVE )
					{
						$subsidy = CompanySubsidyLevel::model()->find(array(
							'condition' => 'id = :id AND type IN ("%", "$")',
							'params' => array(
								':id' => $customerSkillSubsidyLevel->subsidy_level_id,
							),
						));
						
						if( $subsidy )
						{
							if( $subsidy->type == '%' )
							{
								$subsidyPercent = $subsidy->value;
								
								$subsidyPercentInDecimal = $subsidyPercent / 100;

								if( $subsidyPercentInDecimal > 0 )
								{
									if( !$isBilled )
									{
										$subsidyAmount = $subsidyPercentInDecimal * $totalAmount; 
									}
									
									$contractCreditSubsidys[$contract->id]['totalSubsidyAmount'] = $subsidyAmount;
								}
							}
							else
							{
								if( !$isBilled )
								{
									$subsidyAmount = $subsidy->value;
								}
								
								$contractCreditSubsidys[$contract->id]['totalSubsidyAmount'] = $subsidyAmount;
							}							
						}
					}
				}
				
				$totalCreditAmount = 0;
				$creditDescriptions = $billing_period." - ".$customerSkill->contract->contract_name . "\n";
				
				$customerCredits = CustomerCredit::model()->findAll(array(
					'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND status=1',
					'params' => array(
						':customer_id' => $customerSkill->customer_id,
						':contract_id' => $customerSkill->contract_id,
					),
				));
				
				if( $customerCredits )
				{
					foreach( $customerCredits as $customerCredit )
					{
						$creditStartDate = date($customerCredit->start_year.'-'.$customerCredit->start_month.'-01');
											
						if( $customerCredit->type == 2 ) //month range
						{
							if( $customerCredit->end_month == '02' )
							{
								$creditEndDate = date($customerCredit->end_year.'-'.$customerCredit->end_month.'-28');
							}
							elseif( $customerCredit->end_month == '12' )
							{
								$creditEndDate = date($customerCredit->end_year.'-'.$customerCredit->end_month.'-31');
							}
							else
							{
								$creditEndDate = date($customerCredit->end_year.'-'.$customerCredit->end_month.'-t');
							}
							
							if( $customerCredit->start_month >= $customerCredit->end_month )
							{
								$creditEndDate = date($customerCredit->end_year.'-'.$customerCredit->end_month.'-d', strtotime('+1 year', strtotime($creditEndDate)));
							}
						}
						else
						{
							if( $customerCredit->start_month == '02' )
							{
								$creditEndDate = date($customerCredit->start_year.'-'.$customerCredit->start_month.'-28');
							}
							elseif( $customerCredit->start_month == '12' )
							{
								$creditEndDate = date($customerCredit->start_year.'-'.$customerCredit->start_month.'-31');
							}
							else
							{
								$creditEndDate = date($customerCredit->start_year.'-'.$customerCredit->start_month.'-t');
							}
						}
						
						// if( (time() >= strtotime($creditStartDate)) && (time() <= strtotime($creditEndDate)) )
						// {
							// $totalCreditAmount += $customerCredit->amount;
						// }
						
						$monthBillingPeriod = explode(' ',$billing_period);
						$monthPeriod = date('m', strtotime("$monthBillingPeriod[0] 1 ".date('Y')));
						$startDayOfBillingPeriod = date("Y-m-d",strtotime(date('Y')."-".$monthPeriod."-1"));
						$lastDayOfBillingPeriod = date("Y-m-t", strtotime($startDayOfBillingPeriod));
						
						// echo 'isBilled: ' . $isBilled;
						
						// echo ' | ';
						
						// echo $startDayOfBillingPeriod.' >= '.$creditStartDate;
						
						// echo ' | ';
						
						// echo $lastDayOfBillingPeriod.' >= '.$creditEndDate;
						
						// echo ' => ';
						
						if( !$isBilled && (strtotime($startDayOfBillingPeriod) >= strtotime($creditStartDate)) && (strtotime($lastDayOfBillingPeriod) <= strtotime($creditEndDate)) )
						{
							$totalCreditAmount += $customerCredit->amount;
							$creditDescriptions .= "Credit - " . $customerCredit->description.' - '.number_format($customerCredit->amount, 2) . "\n";
						}
					}
				}
				
				$contractCreditSubsidys[$contract->id]['totalCreditAmount'] = $totalCreditAmount;
				
				$totalReducedAmount = $totalAmount;
				$totalReducedAmount = $totalReducedAmount - $subsidyAmount;
				
				if( $totalCreditAmount < 0 )
				{
					$totalReducedAmount = $totalReducedAmount + abs($totalCreditAmount);
				}
				else
				{
					$totalReducedAmount = $totalReducedAmount - abs($totalCreditAmount);
				}
				
				if( $totalReducedAmount < 0 )
					$totalReducedAmount = 0;
				
				$contractCreditSubsidys[$contract->id]['totalReducedAmount'] = $totalReducedAmount;
				$contractCreditSubsidys[$contract->id]['creditDescriptions'] = $creditDescriptions;
				
				if( $billing_type == 'Termination Fee' )
				{
					$contractCreditSubsidys[$contract->id]['totalReducedAmount'] = $totalAmount;
					$contractCreditSubsidys[$contract->id]['creditDescriptions'] = '';
					$contractCreditSubsidys[$contract->id]['totalCreditAmount'] = 0;
					$contractCreditSubsidys[$contract->id]['totalSubsidyAmount'] = 0;
				}
			}
		
		}
	
		return $contractCreditSubsidys;
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
					$updateFieldsForEmail = '';
				
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
						
						$updateFieldsForEmail .= $model->getAttributeLabel($attributeName) .' updated <br>';
					}
					
					$updateFields = rtrim($updateFields, ', ');
					$updateFields = rtrim($updateFields, '<br>');
					
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
					
					//send email to customer service if there are changes made on a credit card
					// if( $updateFieldsForEmail != '' && $authAccount->id == 2 )
					if( $updateFields != '' && in_array($authAccount->account_type_id, array(3,5,6)) )
					{
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
						
						$mail->SetFrom('service@engagex.com', 'Engagex Service', 0);
						
						$mail->AddAddress('customerservice@engagex.com');
						$mail->AddBCC('jim.campbell@engagex.com');						
						$mail->AddBCC('erwin.datu@engagex.com');		

						$mail->Subject = 'Credit Card Change - '.$model->customer->getFullName();
						
						$mail->MsgHTML($updateFieldsForEmail);
						
						$mail->Send();
					}
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
		$model->billing_period = date('M Y');
		$model->billing_type = 'Service Fee';
		
		if( isset($_POST['CustomerBilling']) )
		{
			$model->attributes = $_POST['CustomerBilling'];
			$model->transaction_type = 'Charge';

			$totalAmount = isset($_POST['CustomerBilling']['total_reduced_amount']) ? $_POST['CustomerBilling']['total_reduced_amount'] : $model->amount;
			$model->amount = $totalAmount;		
			
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
					// echo '<pre>';
						// print_r($model->attributes);
					// exit;
					
					if( $totalAmount > 0 )
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
								'amount' => $totalAmount,
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
							$authorizeTransaction->amount = $totalAmount;
										
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
										//save customer credit used for the Customer Billing (the total of all the credits were already submitted in the view)
										$customerCredits = CustomerCredit::model()->findAll(array(
											'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND status=1',
											'params' => array(
												':customer_id' => $customer->id,
												':contract_id' => $model->contract_id,
											),
										));
										
										if( $customerCredits )
										{
											foreach( $customerCredits as $customerCredit )
											{
												$creditStartDate = date($customerCredit->start_year.'-'.$customerCredit->start_month.'-01');
											
												if( $customerCredit->type == 2 ) //month range
												{
													if( $customerCredit->end_month == '02' )
													{
														$creditEndDate = date($customerCredit->end_year.'-'.$customerCredit->end_month.'-28');
													}
													elseif( $customerCredit->end_month == '12' )
													{
														$creditEndDate = date($customerCredit->end_year.'-'.$customerCredit->end_month.'-31');
													}
													else
													{
														$creditEndDate = date($customerCredit->end_year.'-'.$customerCredit->end_month.'-t');
													}
													
													if( $customerCredit->start_month >= $customerCredit->end_month )
													{
														$creditEndDate = date($customerCredit->end_year.'-'.$customerCredit->end_month.'-d', strtotime('+1 year', strtotime($creditEndDate)));
													}
												}
												else
												{
													if( $customerCredit->start_month == '02' )
													{
														$creditEndDate = date($customerCredit->start_year.'-'.$customerCredit->start_month.'-28');
													}
													elseif( $customerCredit->start_month == '12' )
													{
														$creditEndDate = date($customerCredit->start_year.'-'.$customerCredit->start_month.'-31');
													}
													else
													{
														$creditEndDate = date($customerCredit->start_year.'-'.$customerCredit->start_month.'-t');
													}
												}
												 
												if( (time() >= strtotime($creditStartDate)) && (time() <= strtotime($creditEndDate)) )
												{
													$customerCreditBillingHistory = new CustomerCreditBillingHistory;
													$customerCreditBillingHistory->customer_id = $customer->id;
													$customerCreditBillingHistory->contract_id = $model->contract_id;
													$customerCreditBillingHistory->customer_credit_id = $customerCredit->id;
													$customerCreditBillingHistory->customer_billing_id = $model->id;
													$customerCreditBillingHistory->save(false);
												}
											}
										}
										
										
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
								$updateBillingRecord->setAttributes(array(
									'anet_transId' => $response->response_code.': '.$response->response_reason_text,
									'anet_transactionType' => $response->transaction_type,
									'anet_responseCode' => $response->response_code,
									'anet_responseReasonCode'=> $response->response_reason_code,
									'anet_responseReasonDescription'=> $response->response_reason_text,
									'anet_authCode'=> $response->authorization_code,
									'anet_AVSResponse'=> $response->avs_response,
									'anet_cardCodeResponse'=> $response->card_code_response,
									'anet_authAmount'=> $response->amount,
									'anet_customer_Email'=> $anetCustomer->email,
									'anet_billTo_firstName'=> $response->first_name,
									'anet_billTo_lastName'=> $response->last_name,
									'anet_billTo_address'=> $response->address,
									'anet_billTo_city'=> $response->city,
									'anet_billTo_state'=> $response->state,
									'anet_billTo_zip'=> $response->zip_code,
									'anet_product' => $transaction_Details->product,
								));

								$updateBillingRecord->save(false);
							}
						}
					}
					else
					{
						if( $model->credit_amount != null )
						{
							$updateBillingRecord = CustomerBilling::model()->findByPk($model->id);
							$updateBillingRecord->anet_responseCode = 1;
							
							if( $updateBillingRecord->save(false) )
							{
								//save customer credit used for the Customer Billing (the total of all the credits were already submitted in the view)
								$customerCredits = CustomerCredit::model()->findAll(array(
									'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND status=1',
									'params' => array(
										':customer_id' => $customer->id,
										':contract_id' => $model->contract_id,
									),
								));
								
								if( $customerCredits )
								{
									foreach( $customerCredits as $customerCredit )
									{
										$creditStartDate = date($customerCredit->start_year.'-'.$customerCredit->start_month.'-01');
											
										if( $customerCredit->type == 2 ) //month range
										{
											if( $customerCredit->end_month == '02' )
											{
												$creditEndDate = date($customerCredit->end_year.'-'.$customerCredit->end_month.'-28');
											}
											elseif( $customerCredit->end_month == '12' )
											{
												$creditEndDate = date($customerCredit->end_year.'-'.$customerCredit->end_month.'-31');
											}
											else
											{
												$creditEndDate = date($customerCredit->end_year.'-'.$customerCredit->end_month.'-t');
											}
											
											if( $customerCredit->start_month >= $customerCredit->end_month )
											{
												$creditEndDate = date($customerCredit->end_year.'-'.$customerCredit->end_month.'-d', strtotime('+1 year', strtotime($creditEndDate)));
											}
										}
										else
										{
											if( $customerCredit->start_month == '02' )
											{
												$creditEndDate = date($customerCredit->start_year.'-'.$customerCredit->start_month.'-28');
											}
											elseif( $customerCredit->start_month == '12' )
											{
												$creditEndDate = date($customerCredit->start_year.'-'.$customerCredit->start_month.'-31');
											}
											else
											{
												$creditEndDate = date($customerCredit->start_year.'-'.$customerCredit->start_month.'-t');
											}
										}
										 
										if( (time() >= strtotime($creditStartDate)) && (time() <= strtotime($creditEndDate)) )
										{
											$customerCreditBillingHistory = new CustomerCreditBillingHistory;
											$customerCreditBillingHistory->customer_id = $customer->id;
											$customerCreditBillingHistory->contract_id = $model->contract_id;
											$customerCreditBillingHistory->customer_credit_id = $customerCredit->id;
											$customerCreditBillingHistory->customer_billing_id = $model->id;
											$customerCreditBillingHistory->save(false);
										}
									}
								}
							}
						}
					}
					
					$result['status'] = 'success';
					$result['message'] = 'Database has been updated.';
				}
			}
			else
			{
				$result['status'] = 'error';
				$result['message'] = 'No default payment method selected.';
			}
		}
		else
		{
			$result['status'] = 'error';
			$result['message'] = 'Please fill in all the required fields.';
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
		
			$billingPeriodOptions['Early Terminiation Fee'] =  'Early Terminiation Fee';
			$billingPeriodOptions['Enrollment Fee'] =  'Enrollment Fee';
			
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

	public function actionGetBillingContractCreditSubsidy($customer_id, $contract_id, $billing_period, $amount, $billing_type)
	{
		$result = array();
		$result['status'] = false;
		
		$customer = Customer::model()->findByPk($customer_id);
		$contract = Contract::model()->findByPK($contract_id);
		
		
		$existingBillingForCurrentMonth = CustomerBilling::model()->find(array(
			'condition' => '
				customer_id = :customer_id 
				AND contract_id = :contract_id
				AND transaction_type = "Charge"
				AND billing_period = :billing_period
				AND anet_responseCode = 1
			',
			'params' => array(
				':customer_id' => $customer->id,
				':contract_id' => $contract->id,
				':billing_period' => $billing_period
			),
			'order' => 'date_created DESC'
		));
		
		$contractCreditSubsidys = $this->getCustomerContractCreditAndSubsidy($customer, $contract, $billing_period, $billing_type);
		
		$html = $this->renderPartial('_billing_contract_credit_subsidy', array(
			'contractCreditSubsidys' => $contractCreditSubsidys,
			'existingBillingForCurrentMonth' => $existingBillingForCurrentMonth,
			'amount' => $amount,
			'billing_type' => $billing_type,
		), true);
		
		$result['status'] = 'success';
		$result['content'] = $html;
		$result['creditDescriptions'] = $contractCreditSubsidys[$contract_id]['creditDescriptions'];

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
	
	// Credit Functions
	
	public function actionCreateCredit()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		$authAccount = Yii::app()->user->account;
		
		$model = new CustomerCredit;
		$model->customer_id = $_POST['customer_id'];	
	
		if( isset($_POST['CustomerCredit']) )
		{
			$model->attributes = $_POST['CustomerCredit'];

			$model->customer_id = $_POST['customer_id'];	
			
			if( $model->type == 1 ) // 1 month
			{
				$model->end_month = date('m',(strtotime('next month',strtotime(date($model->start_year.'-'.$model->start_month.'-01')))));
				$model->end_year = date('Y',(strtotime('next month',strtotime(date($model->start_year.'-'.$model->start_month.'-01')))));
			}
			
			if( $model->save() )
			{
				$history = new CustomerHistory;
				
				$history->setAttributes(array(
					'model_id' => $model->id, 
					'customer_id' => $model->customer_id,
					'user_account_id' => $authAccount->id,
					'page_name' => 'Credit',
					'content' => $model->description,
					'type' => $history::TYPE_ADDED,
				));

				if( $history->save(false) )
				{
					CustomerQueueViewer::model()->updateAll(
						array(
							'history_credit_added_date' => date('Y-m-d H:i:s'),
							'history_credit_changer' => $authAccount->accountUser->first_name.' '.$authAccount->accountUser->last_name,
							'history_credit_amount' => $model->amount
						)
					, 'customer_id = ' . $model->customer_id);
				}
				
				$result['status'] = 'success';
				$result['message'] = 'Database has been updated.';
			}
			else
			{
				$message = '';
				foreach($model->getErrors() as $errors)
				{
					foreach($errors as $error)
						$message .= $error."\n";
				}
				
				$result['status'] = 'error';
				$result['message'] = $message;
			}
		}
		
		if( isset($_POST['ajax']) )
		{
			$html = $this->renderPartial('credit_create', array(
				'model' => $model,
			), true);

			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}
	
	public function actionUpdateCredit()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		$authAccount = Yii::app()->user->account;
		
		$currentValues = array();
		
		$model = CustomerCredit::model()->findByPk($_POST['id']);
		
		if( isset($_POST['CustomerCredit']) )
		{
			
			if( $model->type == 1 ) // 1 month
			{
				$model->end_month = date('m',(strtotime('next month',strtotime(date($model->start_year.'-'.$model->start_month.'-01')))));
				$model->end_year = date('Y',(strtotime('next month',strtotime(date($model->start_year.'-'.$model->start_month.'-01')))));
			}
			
			if( $model->type == 2 ) // Month Range
			{
				$originalStartMonth = $model->start_month;
				$originalEndMonth = $model->end_month;
			}
				
			
			$currentValues = $model->attributes;
			
			$model->attributes = $_POST['CustomerCredit'];	
			
			$difference = array_diff($model->attributes, $currentValues);

			if( $model->type == 2 && empty($difference) )
			{
				if( $originalStartMonth != $model->start_month )
				{
					$difference['start_month'] = $model->start_month;
				}
				
				if( $originalEndMonth != $model->end_month )
				{
					$difference['end_month'] = $model->end_month;
				}
			}
			
			if( $model->save() )
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
						'page_name' => 'Credit',
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
			else
			{
				$message = '';
				foreach($model->getErrors() as $errors)
				{
					foreach($errors as $error)
						$message .= $error."\n";
				}
				
				$result['status'] = 'error';
				$result['message'] = $message;
			}
		}
		
		if( isset($_POST['ajax']) )
		{
			$viewOnly = !empty($model->customerCreditBillingHistory);
			
			if( Yii::app()->user->account->getIsCustomer() || Yii::app()->user->account->getIsCustomerOfficeStaff() )
			{
				$viewOnly = true;
			}
			
			$html = $this->renderPartial('credit_update', array(
				'model' => $model,
				'viewOnly' => $viewOnly,
			), true);

			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}
	
	public function actionDeleteCredit()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		$authAccount = Yii::app()->user->account;
		
		$model = CustomerCredit::model()->findByPk($_POST['id']);
		$model->status = 3;
		
		if( $model->save(false) )
		{
			$history = new CustomerHistory;
			
			$history->setAttributes(array(
				'model_id' => $model->id, 
				'customer_id' => $model->customer_id,
				'user_account_id' => $authAccount->id,
				'page_name' => 'Credit',
				'content' => $model->description,
				'type' => $history::TYPE_DELETED,
			));

			$history->save(false);
			
			$result['status'] = 'success';
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