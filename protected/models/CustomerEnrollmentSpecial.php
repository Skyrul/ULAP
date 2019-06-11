<?php

/**
 * This is the model class for table "{{customer_enrollment_special}}".
 *
 * The followings are the available columns in table '{{customer_enrollment_special}}':
 * @property integer $id
 * @property integer $company_id
 * @property integer $contract_id
 * @property integer $account_id
 * @property string $firstname
 * @property string $lastname
 * @property string $custom_customer_id
 * @property string $address
 * @property string $city
 * @property string $state
 * @property string $zip
 * @property string $phone
 * @property string $email_address
 * @property string $referral
 * @property string $date_created
 * @property string $date_updated
 * @property string $payment_method
 * @property string $credit_card_type
 * @property string $credit_card_name
 * @property string $credit_card_number
 * @property string $credit_card_security_code
 * @property string $credit_card_expiration_month
 * @property string $credit_card_expiration_year
 * @property string $echeck_account_number
 * @property string $echeck_routing_number
 * @property string $echeck_account_type
 * @property string $echeck_entity_name
 * @property string $echeck_account_name
 * @property string $echeck_institution_name
 * @property string $cc_address
 * @property string $cc_city
 * @property string $cc_state
 * @property string $cc_zip
 * @property string $signature
 * @property string $start_month
 * @property string $notes
 * @property integer $sales_rep_account_id
 * @property integer $sales_management_deleted
 * @property integer $send_weekly_emails
 */
class CustomerEnrollmentSpecial extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{customer_enrollment_special}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('company_id, firstname, lastname, custom_customer_id, address, city, state, zip, email_address', 'required'),
			array('email_address', 'email'),
			array('company_id, contract_id, account_id, sales_rep_account_id, sales_management_deleted, send_weekly_emails, tier_level, is_enrolled', 'numerical', 'integerOnly'=>true),
			array('firstname, lastname, address, city, state, zip, phone, email_address, referral', 'length', 'max'=>128),
			array('custom_customer_id', 'length', 'max'=>10),
			array('payment_method, credit_card_type, credit_card_name, credit_card_number, credit_card_security_code, credit_card_expiration_month, credit_card_expiration_year, echeck_account_number, echeck_routing_number, echeck_account_type, echeck_entity_name, echeck_account_name, echeck_institution_name, cc_address, cc_city, cc_state, cc_zip, signature, start_month', 'length', 'max'=>255),
			array('notes', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, company_id, contract_id, account_id, firstname, lastname, custom_customer_id, address, city, state, zip, phone, email_address, referral, date_created, date_updated, payment_method, credit_card_type, credit_card_name, credit_card_number, credit_card_security_code, credit_card_expiration_month, credit_card_expiration_year, echeck_account_number, echeck_routing_number, echeck_account_type, echeck_entity_name, echeck_account_name, echeck_institution_name, cc_address, cc_city, cc_state, cc_zip, signature, start_month, notes, sales_rep_account_id, sales_management_deleted, send_weekly_emails', 'safe', 'on'=>'search'),
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
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'company_id' => 'Company',
			'contract_id' => 'Contract',
			'account_id' => 'Account',
			'firstname' => 'Firstname',
			'lastname' => 'Lastname',
			'custom_customer_id' => 'Agent ID',
			'address' => 'Address',
			'city' => 'City',
			'state' => 'State',
			'zip' => 'Zip',
			'phone' => 'Phone',
			'email_address' => 'Email Address',
			'referral' => 'Referral',
			'date_created' => 'Date Created',
			'date_updated' => 'Date Updated',
			'payment_method' => 'Payment Method',
			'credit_card_type' => 'Credit Card Type',
			'credit_card_name' => 'Credit Card Name',
			'credit_card_number' => 'Credit Card Number',
			'credit_card_security_code' => 'Credit Card Security Code',
			'credit_card_expiration_month' => 'Credit Card Expiration Month',
			'credit_card_expiration_year' => 'Credit Card Expiration Year',
			'echeck_account_number' => 'Echeck Account Number',
			'echeck_routing_number' => 'Echeck Routing Number',
			'echeck_account_type' => 'Echeck Account Type',
			'echeck_entity_name' => 'Echeck Entity Name',
			'echeck_account_name' => 'Echeck Account Name',
			'echeck_institution_name' => 'Echeck Institution Name',
			'cc_address' => 'Cc Address',
			'cc_city' => 'Cc City',
			'cc_state' => 'Cc State',
			'cc_zip' => 'Cc Zip',
			'signature' => 'Signature',
			'start_month' => 'Start Month',
			'notes' => 'Notes',
			'sales_rep_account_id' => 'Sales Rep Account',
			'sales_management_deleted' => 'Sales Management Deleted',
			'send_weekly_emails' => 'Send Weekly Emails',
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
		$criteria->compare('company_id',$this->company_id);
		$criteria->compare('contract_id',$this->contract_id);
		$criteria->compare('account_id',$this->account_id);
		$criteria->compare('firstname',$this->firstname,true);
		$criteria->compare('lastname',$this->lastname,true);
		$criteria->compare('custom_customer_id',$this->custom_customer_id,true);
		$criteria->compare('address',$this->address,true);
		$criteria->compare('city',$this->city,true);
		$criteria->compare('state',$this->state,true);
		$criteria->compare('zip',$this->zip,true);
		$criteria->compare('phone',$this->phone,true);
		$criteria->compare('email_address',$this->email_address,true);
		$criteria->compare('referral',$this->referral,true);
		$criteria->compare('date_created',$this->date_created,true);
		$criteria->compare('date_updated',$this->date_updated,true);
		$criteria->compare('payment_method',$this->payment_method,true);
		$criteria->compare('credit_card_type',$this->credit_card_type,true);
		$criteria->compare('credit_card_name',$this->credit_card_name,true);
		$criteria->compare('credit_card_number',$this->credit_card_number,true);
		$criteria->compare('credit_card_security_code',$this->credit_card_security_code,true);
		$criteria->compare('credit_card_expiration_month',$this->credit_card_expiration_month,true);
		$criteria->compare('credit_card_expiration_year',$this->credit_card_expiration_year,true);
		$criteria->compare('echeck_account_number',$this->echeck_account_number,true);
		$criteria->compare('echeck_routing_number',$this->echeck_routing_number,true);
		$criteria->compare('echeck_account_type',$this->echeck_account_type,true);
		$criteria->compare('echeck_entity_name',$this->echeck_entity_name,true);
		$criteria->compare('echeck_account_name',$this->echeck_account_name,true);
		$criteria->compare('echeck_institution_name',$this->echeck_institution_name,true);
		$criteria->compare('cc_address',$this->cc_address,true);
		$criteria->compare('cc_city',$this->cc_city,true);
		$criteria->compare('cc_state',$this->cc_state,true);
		$criteria->compare('cc_zip',$this->cc_zip,true);
		$criteria->compare('signature',$this->signature,true);
		$criteria->compare('start_month',$this->start_month,true);
		$criteria->compare('notes',$this->notes,true);
		$criteria->compare('sales_rep_account_id',$this->sales_rep_account_id);
		$criteria->compare('sales_management_deleted',$this->sales_management_deleted);
		$criteria->compare('send_weekly_emails',$this->send_weekly_emails);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CustomerEnrollmentSpecial the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function getFullName()
	{
		// if(!empty($this->middlename))
			// return $this->firstname.' '.$this->middlename.' '.$this->lastname;
		// else
			return $this->firstname.' '.$this->lastname;
	}
	
	public function getFullNameReverse()
	{
		// if(!empty($this->middlename))
			// return $this->lastname.', '.$this->firstname.' '.$this->middlename;
		// else
			return $this->lastname.', '.$this->firstname;
	}
}
