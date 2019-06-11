<?php

/**
 * This is the model class for table "{{customer_enrollment}}".
 *
 * The followings are the available columns in table '{{customer_enrollment}}':
 * @property integer $id
 * @property integer $contract_id
 * @property string $firstname
 * @property string $lastname
 * @property string $address
 * @property string $city
 * @property string $state
 * @property string $zip
 * @property string $phone
 * @property string $email_address
 * @property string $date_created
 * @property string $date_updated
 */
class CustomerEnrollment extends CActiveRecord
{
	public $customerEnrollmentLevel;
	public $customerEnrollmentLevelValidation;
	public $companyId;
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{customer_enrollment}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('contract_id, firstname, lastname, address, city, state, zip, phone, email_address, payment_method, cc_address, cc_city, cc_state, cc_zip, signature, start_month, companyId', 'required'),
			array('sales_rep_account_id, send_weekly_emails', 'numerical', 'integerOnly'=>true),
			array('companyId', 'required', 'on' => 'companyOption'),
			array('email_address', 'email'),
			array('email_address', 'validateEmailAccount'),
			array('customerEnrollmentLevelValidation', 'validateContractOption'),
			array('id, contract_id, account_id', 'numerical', 'integerOnly'=>true),
			array('firstname, lastname, address, city, state, zip, phone, email_address, referral', 'length', 'max'=>128),
			array('custom_customer_id', 'length', 'min'=>7, 'max'=>10),
			array('notes', 'safe'),
			array('credit_card_number', 'validateLuhn'),
			array('credit_card_type, credit_card_name, credit_card_number, credit_card_security_code, credit_card_expiration_month, credit_card_expiration_year', 'required', 'on'=>'validateAmexCreditCard'),
			array('credit_card_security_code', 'length', 'min'=>4, 'on'=>'validateAmexCreditCard'),
			array('credit_card_type, credit_card_name, credit_card_number, credit_card_security_code, credit_card_expiration_month, credit_card_expiration_year', 'required', 'on'=>'validateOtherCreditCard'),
			array('credit_card_security_code', 'length', 'min'=>3, 'on'=>'validateOtherCreditCard'),
			array('echeck_account_number, echeck_routing_number, echeck_account_type, echeck_institution_name', 'required', 'on'=>'validateEcheck'),		
			array('payment_method, credit_card_type, credit_card_name, credit_card_number, credit_card_security_code, credit_card_expiration_month, credit_card_expiration_year, echeck_account_number, echeck_routing_number, echeck_account_type, echeck_entity_name, echeck_account_name, echeck_institution_name, cc_address, cc_city, cc_state, cc_zip, signature, start_month', 'length', 'max'=>255),
			
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, contract_id, firstname, lastname, address, city, state, zip, phone, email_address, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'account' => array(self::BELONGS_TO, 'Account', 'account_id'),
			'contract' => array(self::BELONGS_TO, 'Contract', 'contract_id'),
			'customerEnrollmentLevels' => array(self::HAS_MANY, 'CustomerEnrollmentLevel', 'customer_enrollment_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'contract_id' => 'Contract',
			'account_id' => 'Account',
			'firstname' => 'Firstname',
			'lastname' => 'Lastname',
			'address' => 'Address',
			'city' => 'City',
			'state' => 'State',
			'zip' => 'Zip',
			'phone' => 'Phone',
			'email_address' => 'Email Address',
			'date_created' => 'Date Created',
			'date_updated' => 'Date Updated',
			'payment_method' => 'Payment Method',
			'credit_card_type' => 'Credit Card Type',
			'credit_card_name' => 'Credit Card Name',
			'credit_card_number' => 'Credit Card Number',
			'credit_card_security_code' => 'Credit Card Security Code',
			'credit_card_expiration_month' => 'Credit Card Expiration Month',
			'credit_card_expiration_year' => 'Credit Card Expiration Year',
			'echeck_account_number' => 'Account Number', 
			'echeck_routing_number' => 'Routing Number', 
			'echeck_account_type' => 'Account Type', 
			'echeck_entity_name' => 'Entity Name', 
			'echeck_account_name' => 'Name on Account', 
			'echeck_institution_name' => 'Institution',
			'cc_address' => 'Billing Address',
			'cc_city' => 'City',
			'cc_state' => 'State',
			'cc_zip' => 'Zip',
			'signature' => 'Signature',
			'start_month' => 'Start Month',
			'custom_customer_id' => 'Agent ID',
			'companyId' => 'Company',
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
		$criteria->compare('contract_id',$this->contract_id);
		$criteria->compare('account_id',$this->account_id);
		$criteria->compare('firstname',$this->firstname,true);
		$criteria->compare('lastname',$this->lastname,true);
		$criteria->compare('address',$this->address,true);
		$criteria->compare('city',$this->city,true);
		$criteria->compare('state',$this->state,true);
		$criteria->compare('zip',$this->zip,true);
		$criteria->compare('phone',$this->phone,true);
		$criteria->compare('email_address',$this->email_address,true);
		$criteria->compare('date_created',$this->date_created,true);
		$criteria->compare('date_updated',$this->date_updated,true);
		$criteria->compare('payment_method',$this->payment_method,true);
		$criteria->compare('credit_card_type',$this->credit_card_type,true);
		$criteria->compare('credit_card_name',$this->credit_card_name,true);
		$criteria->compare('credit_card_number',$this->credit_card_number,true);
		$criteria->compare('credit_card_security_code',$this->credit_card_security_code,true);
		$criteria->compare('credit_card_expiration_month',$this->credit_card_expiration_month,true);
		$criteria->compare('credit_card_expiration_year',$this->credit_card_expiration_year,true);
		$criteria->compare('cc_address',$this->cc_address,true);
		$criteria->compare('cc_city',$this->cc_city,true);
		$criteria->compare('cc_state',$this->cc_state,true);
		$criteria->compare('cc_zip',$this->cc_zip,true);
		$criteria->compare('signature',$this->signature,true);
		$criteria->compare('start_month',$this->start_month,true);
		
		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CustomerEnrollment the static model class
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

	public function afterFind()
	{
		$this->customerEnrollmentLevelArray = $this->getCustomerEnrollmentLevelArray();
		return parent::afterFind();
	}
	
	public function validateContractOption($attribute,$params)
	{
		$package = 0;
		
		foreach($this->customerEnrollmentLevel as $cel)
		{
			if(!empty($cel['qty']))
			{
				$package++;
			}
		}
		
		if($package == 0)
			$this->addError($attribute, 'Select a quantity in the package of choice.');
	}
	
	
	public function validateEmailAccount($attribute,$params)
	{
		$criteria = new CDbCriteria;
		$criteria->compare('email_address', $this->email_address);
		
		$account = Account::model()->find($criteria);
		
		if($account !== null)
		{
			if($account->account_type_id != Account::TYPE_CUSTOMER)
				$this->addError($attribute, 'Email address already in used for different account.');
		}
	}

	public $customerEnrollmentLevelArray;
	
	public function getCustomerEnrollmentLevelArray()
	{
		if($this->customerEnrollmentLevelArray === null)
		{
			$customerEnrollmentLevels = array();
			foreach($this->customerEnrollmentLevels as $customerEnrollmentLevel)
			{
				$customerEnrollmentLevels[$customerEnrollmentLevel->contract_subsidy_level_group_id]['id'] = $customerEnrollmentLevel->id;
				$customerEnrollmentLevels[$customerEnrollmentLevel->contract_subsidy_level_group_id]['group_id'] = $customerEnrollmentLevel->contract_subsidy_level_group_id;
				$customerEnrollmentLevels[$customerEnrollmentLevel->contract_subsidy_level_group_id]['qty'] = $customerEnrollmentLevel->quantity;
			}
			
			$this->customerEnrollmentLevelArray = $customerEnrollmentLevels;
		}
		
		return $this->customerEnrollmentLevelArray;
	}

	public function validateLuhn($attribute, $params)
	{
		if($this->payment_method == 'Credit Card')
		{
			// return;
			
			if(empty($this->credit_card_type))
			{
				$this->addError($attribute, 'Must select a credit card type');
			}
			
			// Strip any non-digits (useful for credit card numbers with spaces and hyphens)
			$number = preg_replace('/\D/', '', $this->credit_card_number);

			// Set the string length and parity
			$number_length = strlen($number);
			$parity = $number_length % 2;

			// Loop through each digit and do the maths
			$total=0;
			
			for( $i=0; $i < $number_length; $i++ ) 
			{
				
				$digit=$number[$i];
				
				// Multiply alternate digits by two
				if( $i % 2 == $parity ) 
				{
					$digit *= 2;
					
					// If the sum is two digits, add them together (in effect)
					if( $digit > 9 ) 
					{
						$digit -= 9;
					}
				}
				
				// Total up the digits
				$total += $digit;
			}

			// If the total mod 10 equals 0, the number is valid
			if( $total % 10 != 0 )
			{
				$this->addError($attribute, 'The credit card number you entered failed the Luhn Check. It\'s not valid, did you make a typo?');
			}
		
			$error = true;
			
			$cardLength = strlen($this->credit_card_number);
			
			switch($this->credit_card_type)
			{
				case 'Amex': //15
					if($cardLength == 15)
						$error = false;
				break;
				
				case 'Discover': //16
					if($cardLength == 16)
						$error = false;
				break;
				
				case 'MasterCard': //16
					if($cardLength == 16)
						$error = false;
				break;
				
				case 'Visa': //16
					if($cardLength == 16)
						$error = false;
				break;
			}
			
			if($error)
				$this->addError($attribute, 'The credit card number required digits are invalid');
		}
		
	}
}
