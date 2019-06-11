<?php

/**
 * This is the model class for table "{{customer_billing}}".
 *
 * The followings are the available columns in table '{{customer_billing}}':
 * @property integer $id
 * @property integer $customer_id
 * @property integer $credit_card_id
 * @property integer $account_id
 * @property integer $contract_id
 * @property integer $reference_transaction_id
 * @property double $amount
 * @property string $transaction_type
 * @property string $payment_method
 * @property string $description
 * @property string $credit_card_type
 * @property string $credit_card_number
 * @property string $credit_card_security_code
 * @property string $credit_card_expiration_month
 * @property string $credit_card_expiration_year
 * @property string $ach_account_name
 * @property string $ach_routing_number
 * @property string $ach_account_number
 * @property string $ach_account_type
 * @property string $ach_bank_name
 * @property string $anet_transId
 * @property string $anet_invoiceNumber
 * @property string $anet_submitTimeUTC
 * @property string $anet_submitTimeLocal
 * @property string $anet_transactionType
 * @property string $anet_transactionStatus
 * @property string $anet_responseCode
 * @property string $anet_responseReasonCode
 * @property string $anet_responseReasonDescription
 * @property string $anet_authCode
 * @property string $anet_AVSResponse
 * @property string $anet_cardCodeResponse
 * @property string $anet_authAmount
 * @property string $anet_settleAmount
 * @property string $anet_taxExempt
 * @property string $anet_customer_Email
 * @property string $anet_billTo_firstName
 * @property string $anet_billTo_lastName
 * @property string $anet_billTo_address
 * @property string $anet_billTo_city
 * @property string $anet_billTo_state
 * @property string $anet_billTo_zip
 * @property string $anet_recurringBilling
 * @property string $anet_product
 * @property string $anet_marketType
 * @property string $date_created
 * @property string $date_updated
 */
class CustomerBilling extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{customer_billing}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('customer_id, amount, transaction_type, payment_method', 'required'),
			array('customer_id, credit_card_id, echeck_id, account_id, contract_id, reference_transaction_id', 'numerical', 'integerOnly'=>true),
			array('amount, credit_amount, subsidy_amount, original_amount', 'numerical'),
			array('transaction_type, billing_type, payment_method, billing_period, credit_card_type, credit_card_number, credit_card_security_code, credit_card_expiration_month, credit_card_expiration_year, ach_account_name, ach_routing_number, ach_account_number, ach_account_type, ach_bank_name, anet_transId, anet_invoiceNumber, anet_submitTimeUTC, anet_submitTimeLocal, anet_transactionType, anet_transactionStatus, anet_responseCode, anet_responseReasonCode, anet_responseReasonDescription, anet_authCode, anet_AVSResponse, anet_cardCodeResponse, anet_authAmount, anet_settleAmount, anet_taxExempt, anet_customer_Email, anet_billTo_firstName, anet_billTo_lastName, anet_billTo_address, anet_billTo_city, anet_billTo_state, anet_billTo_zip, anet_recurringBilling, anet_product, anet_marketType', 'length', 'max'=>255),
			
			array('is_imported', 'numerical', 'integerOnly'=>true),
			
			array('credit_amount, subsidy_amount, original_amount, description, date_created, date_updated', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, customer_id, credit_card_id, echeck_id, account_id, contract_id, reference_transaction_id, amount, transaction_type, billing_type, payment_method, description, credit_card_type, credit_card_number, credit_card_security_code, credit_card_expiration_month, credit_card_expiration_year, ach_account_name, ach_routing_number, ach_account_number, ach_account_type, ach_bank_name, anet_transId, anet_invoiceNumber, anet_submitTimeUTC, anet_submitTimeLocal, anet_transactionType, anet_transactionStatus, anet_responseCode, anet_responseReasonCode, anet_responseReasonDescription, anet_authCode, anet_AVSResponse, anet_cardCodeResponse, anet_authAmount, anet_settleAmount, anet_taxExempt, anet_customer_Email, anet_billTo_firstName, anet_billTo_lastName, anet_billTo_address, anet_billTo_city, anet_billTo_state, anet_billTo_zip, anet_recurringBilling, anet_product, anet_marketType, date_created, date_updated', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'creditCard' => array(self::BELONGS_TO, 'CustomerCreditCard', 'credit_card_id'),
			'echeck' => array(self::BELONGS_TO, 'CustomerEcheck', 'echeck_id'),
			'account' => array(self::BELONGS_TO, 'Account', 'account_id'),
			'customer' => array(self::BELONGS_TO, 'Customer', 'customer_id'),
			'contract' => array(self::BELONGS_TO, 'Contract', 'contract_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'customer_id' => 'Customer',
			'credit_card_id' => 'Credit Card',
			'echeck_id' => 'eCheck',
			'account_id' => 'Account',
			'contract_id' => 'Contract',
			'reference_transaction_id' => 'Reference Transaction',
			'amount' => 'Amount',
			'transaction_type' => 'Transaction Type',
			'payment_method' => 'Payment Method',
			'description' => 'Description',
			'billing_period' => 'Billing Period',
			'credit_card_type' => 'Credit Card Type',
			'credit_card_number' => 'Credit Card Number',
			'credit_card_security_code' => 'Credit Card Security Code',
			'credit_card_expiration_month' => 'Credit Card Expiration Month',
			'credit_card_expiration_year' => 'Credit Card Expiration Year',
			'ach_account_name' => 'Ach Account Name',
			'ach_routing_number' => 'Ach Routing Number',
			'ach_account_number' => 'Ach Account Number',
			'ach_account_type' => 'Ach Account Type',
			'ach_bank_name' => 'Ach Bank Name',
			'anet_transId' => 'Anet Trans',
			'anet_invoiceNumber' => 'Anet Invoice Number',
			'anet_submitTimeUTC' => 'Anet Submit Time Utc',
			'anet_submitTimeLocal' => 'Anet Submit Time Local',
			'anet_transactionType' => 'Anet Transaction Type',
			'anet_transactionStatus' => 'Anet Transaction Status',
			'anet_responseCode' => 'Anet Response Code',
			'anet_responseReasonCode' => 'Anet Response Reason Code',
			'anet_responseReasonDescription' => 'Anet Response Reason Description',
			'anet_authCode' => 'Anet Auth Code',
			'anet_AVSResponse' => 'Anet Avsresponse',
			'anet_cardCodeResponse' => 'Anet Card Code Response',
			'anet_authAmount' => 'Anet Auth Amount',
			'anet_settleAmount' => 'Anet Settle Amount',
			'anet_taxExempt' => 'Anet Tax Exempt',
			'anet_customer_Email' => 'Anet Customer Email',
			'anet_billTo_firstName' => 'Anet Bill To First Name',
			'anet_billTo_lastName' => 'Anet Bill To Last Name',
			'anet_billTo_address' => 'Anet Bill To Address',
			'anet_billTo_city' => 'Anet Bill To City',
			'anet_billTo_state' => 'Anet Bill To State',
			'anet_billTo_zip' => 'Anet Bill To Zip',
			'anet_recurringBilling' => 'Anet Recurring Billing',
			'anet_product' => 'Anet Product',
			'anet_marketType' => 'Anet Market Type',
			'date_created' => 'Date Created',
			'date_updated' => 'Date Updated',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('customer_id',$this->customer_id);
		$criteria->compare('credit_card_id',$this->credit_card_id);
		$criteria->compare('echeck_id',$this->echeck_id);
		$criteria->compare('account_id',$this->account_id);
		$criteria->compare('contract_id',$this->contract_id);
		$criteria->compare('reference_transaction_id',$this->reference_transaction_id);
		$criteria->compare('amount',$this->amount);
		$criteria->compare('transaction_type',$this->transaction_type,true);
		$criteria->compare('payment_method',$this->payment_method,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('credit_card_type',$this->credit_card_type,true);
		$criteria->compare('credit_card_number',$this->credit_card_number,true);
		$criteria->compare('credit_card_security_code',$this->credit_card_security_code,true);
		$criteria->compare('credit_card_expiration_month',$this->credit_card_expiration_month,true);
		$criteria->compare('credit_card_expiration_year',$this->credit_card_expiration_year,true);
		$criteria->compare('ach_account_name',$this->ach_account_name,true);
		$criteria->compare('ach_routing_number',$this->ach_routing_number,true);
		$criteria->compare('ach_account_number',$this->ach_account_number,true);
		$criteria->compare('ach_account_type',$this->ach_account_type,true);
		$criteria->compare('ach_bank_name',$this->ach_bank_name,true);
		$criteria->compare('anet_transId',$this->anet_transId,true);
		$criteria->compare('anet_invoiceNumber',$this->anet_invoiceNumber,true);
		$criteria->compare('anet_submitTimeUTC',$this->anet_submitTimeUTC,true);
		$criteria->compare('anet_submitTimeLocal',$this->anet_submitTimeLocal,true);
		$criteria->compare('anet_transactionType',$this->anet_transactionType,true);
		$criteria->compare('anet_transactionStatus',$this->anet_transactionStatus,true);
		$criteria->compare('anet_responseCode',$this->anet_responseCode,true);
		$criteria->compare('anet_responseReasonCode',$this->anet_responseReasonCode,true);
		$criteria->compare('anet_responseReasonDescription',$this->anet_responseReasonDescription,true);
		$criteria->compare('anet_authCode',$this->anet_authCode,true);
		$criteria->compare('anet_AVSResponse',$this->anet_AVSResponse,true);
		$criteria->compare('anet_cardCodeResponse',$this->anet_cardCodeResponse,true);
		$criteria->compare('anet_authAmount',$this->anet_authAmount,true);
		$criteria->compare('anet_settleAmount',$this->anet_settleAmount,true);
		$criteria->compare('anet_taxExempt',$this->anet_taxExempt,true);
		$criteria->compare('anet_customer_Email',$this->anet_customer_Email,true);
		$criteria->compare('anet_billTo_firstName',$this->anet_billTo_firstName,true);
		$criteria->compare('anet_billTo_lastName',$this->anet_billTo_lastName,true);
		$criteria->compare('anet_billTo_address',$this->anet_billTo_address,true);
		$criteria->compare('anet_billTo_city',$this->anet_billTo_city,true);
		$criteria->compare('anet_billTo_state',$this->anet_billTo_state,true);
		$criteria->compare('anet_billTo_zip',$this->anet_billTo_zip,true);
		$criteria->compare('anet_recurringBilling',$this->anet_recurringBilling,true);
		$criteria->compare('anet_product',$this->anet_product,true);
		$criteria->compare('anet_marketType',$this->anet_marketType,true);
		$criteria->compare('date_created',$this->date_created,true);
		$criteria->compare('date_updated',$this->date_updated,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CustomerBilling the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	
	public function beforeSave()
	{
		if($this->isNewRecord)
			$this->date_created = $this->date_updated = date("Y-m-d H:i:s");
		else
			$this->date_updated = date("Y-m-d H:i:s");
		
		return parent::beforeSave();
	}
	
	
	public function getHtmlChargeReceipt($type='Charge')
	{
		if( $this->payment_method == 'echeck' )
		{
			$creditCardNumber = '**** **** **** ' . substr($this->ach_account_number, -4);
		}
		else
		{
			$creditCardNumber = '**** **** **** ' . substr($this->credit_card_number, -4);
		}
		
		$customerName = ucfirst($this->customer->firstname).' '.ucfirst($this->customer->lastname);
		
		
		$itemName = '';
		
		$criteria = new CDbCriteria;
		$criteria->compare('customer_id',$this->customer->id);
		$criteria->compare('status',CustomerSkill::STATUS_ACTIVE);
		$selectedCustomerSkills = CustomerSkill::model()->findAll($criteria);
		
		if( !empty($selectedCustomerSkills) )
		{
			foreach( $selectedCustomerSkills as $selectedCustomerSkill )
			{
				$contract = $selectedCustomerSkill->contract;
				
				if( isset($contract) && $contract->fulfillment_type != null )
				{
					if( $contract->fulfillment_type == Contract::TYPE_FULFILLMENT_GOAL_VOLUME )
					{
						if( !empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME]) )
						{
							foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME] as $key => $subsidyLevel )
							{
								$customerSkillLevelArray = $selectedCustomerSkill->getCustomerSkillLevelArray();
								$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;

								if( $customerSkillLevelArrayGroup != null )
								{							
									if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
									{
										$itemName .= $contract->description.' - ';
		
										$itemName .= $this->billing_period . ' | ';
								
										$itemName .= 'Goal Volume';
										
										$itemName .= ' | Quantity ' . $customerSkillLevelArrayGroup->quantity;
										
										$itemName .= ' | ' . $subsidyLevel['goal'];
										
										$itemName .= ' <br />';
									}
								}
							}
						}
					}
					else
					{
						if( !empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME]) )
						{
							foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME] as $key => $subsidyLevel )
							{
								$customerSkillLevelArray = $selectedCustomerSkill->getCustomerSkillLevelArray();
								
								$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;
								
								if( $customerSkillLevelArrayGroup != null )
								{
									if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
									{
										$itemName .= $contract->description.' - ';
		
										$itemName .= $this->billing_period . ' | ';
								
										$itemName .= 'Lead Volume';
										
										$itemName .= ' | Quantity ' . $customerSkillLevelArrayGroup->quantity;
										
										$itemName .= ' | ' . $subsidyLevel['low'].' - '.$subsidyLevel['high'];
										
										$itemName .= ' <br />';
									}
								}
							}
						}
					}
				}
			}
		}

			
		$html = '
			<br />
			<table width="80%" align="center">
				<tr>
					<td>
						<table width="100%" border="0" cellpadding="0" cellspacing="0">
							<tr>
								<td width="63%"><img src="http://system.engagexapp.com/webform/images/engagex-logo.jpg" width="300"></td>
								<td style="font-size:24px;"><br /><br />'.$type.' Receipt</td>
							</tr>
						</table>
						
						<br />
						
						<table width="100%" border="0" cellpadding="0" cellspacing="0">
							<tr>
								<td valign="top" width="30%">
									From: 
									<br />
									Engagex
									<br />
									585 E 1860 S
									<br />
									Provo, UT 84606
									<br />
									1.800.515.8734

									<br />
									<br />
									<a href="http://insurance.engagex.com" target="_blank">insurance.engagex.com</a>
								</td>
								
								<td valign="top" width="5%" style="border-left: 3px solid #2C6AA0;"></td>
								
								<td valign="top" width="30%">
									Billed to:
									<br />
									'.$customerName.'
									<br />
									<br />
									Account #
									<br />
									'.$this->customer->account_number.'
									<br />
									<br />
									<a href="mailto:'.$this->customer->email_address.'">'.$this->customer->email_address.'</a>
								</td>
								
								<td valign="top" width="5%" style="border-left: 3px solid #2C6AA0;"></td>
								
								<td valign="top" width="30%">
									<br />
									<table width="100%" border="0" cellpadding="0" cellspacing="0">
										<tr>
											<td width="50%">Receipt Date</td>
											<td width="50%" align="right">'.date('m/d/Y').'</td>
										</tr>
										<tr>
											<td width="50%">Card Number</td>
											<td width="50%" align="right">'.$creditCardNumber.'</td>
										</tr>
										<tr>
											<td width="50%">Total '.$type.'</td>
											<td width="50%" align="right">$'.number_format($this->amount, 2).'</td>
										</tr>
									</table>
								</td>
							</tr>
							
						</table>
						
						<br />
						<br />
						<br />
						
						<table width="100%" border="0" cellpadding="0" cellspacing="0">
							<tr bgcolor="#2C6AA0">
								<td width="80%" style="padding:2px 0 2px 15px"><font color="#ffffff">Description</font></td>
								<td width="20%" style="padding:2px 0 2px 15px"><font color="#ffffff">'.$type.' Amount</font></td>
							</tr>
							
							<tr>
								<td>'.$itemName.'</td>
								<td align="center">$'.number_format($this->amount, 2).'</td>
							</tr>
							
						</table>
						
						<br />
						<br />
						<br />
						
						<table width="100%" border="0" cellpadding="0" cellspacing="0">
							<tr bgcolor="#2C6AA0">
								<td width="60%" style="padding:2px 0 2px 15px"><font color="#ffffff">Total</font></td>
								<td width="40%" style="padding:2px 0 2px 80px"><font color="#ffffff">$'.number_format($this->amount, 2).'</font></td>
							</tr>
						</table>
					</td>
			</table>
		';
		
		return $html;
	}

	public function getDefaultMethod($customerId)
	{
		$item = array();
		
		$defaultCreditCard = CustomerCreditCard::model()->find(array(
			'condition' => 'customer_id = :customer_id AND status=1 AND is_preferred=1',
			'params' => array(
				':customer_id' => $customerId,
			),
		));
		
		if( $defaultCreditCard )
		{
			$item['type'] = 'creditCard';
			$item['id'] = $defaultCreditCard->id;
		}
		
		$defaultEcheck = CustomerEcheck::model()->find(array(
			'condition' => 'customer_id = :customer_id AND status=1 AND is_preferred=1',
			'params' => array(
				':customer_id' => $customerId,
			),
		));
		
		if( $defaultEcheck )
		{
			$item['type'] = 'echeck';
			$item['id'] = $defaultEcheck->id;
		}
		
		return $item['type'].'-'.$item['id'];
	}
	
	public static function getPaymentMethods($customerId)
	{
		$items = array();
		
		$customerCreditCards = CustomerCreditCard::model()->findAll(array(
			'condition' => 'customer_id = :customer_id AND status=1',
			'params' => array(
				':customer_id' => $customerId,
			),
		));
		
		$customerEchecks = CustomerEcheck::model()->findAll(array(
			'condition' => 'customer_id = :customer_id AND status=1',
			'params' => array(
				':customer_id' => $customerId,
			),
		));
		
		if( $customerCreditCards )
		{
			foreach( $customerCreditCards as $customerCreditCard )
			{
				$items['creditCard-'.$customerCreditCard->id] = !empty($customerCreditCard->nick_name) ? $customerCreditCard->nick_name : $customerCreditCard->credit_card_type;
			}
		}
		
		if( $customerEchecks )
		{
			foreach( $customerEchecks as $customerEcheck )
			{
				$items['echeck-'.$customerEcheck->id] = $customerEcheck->account_name;
			}
		}
		
		return $items;
	}	
}
