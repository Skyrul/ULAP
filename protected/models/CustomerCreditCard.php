<?php

/**
 * This is the model class for table "{{customer_credit_card}}".
 *
 * The followings are the available columns in table '{{customer_credit_card}}':
 * @property integer $id
 * @property integer $customer_id
 * @property string $nick_name
 * @property string $phone_number
 * @property string $credit_card_number
 * @property string $credit_card_type
 * @property string $security_code
 * @property string $expiration_month
 * @property string $expiration_year
 * @property string $first_name
 * @property string $last_name
 * @property string $address
 * @property string $city
 * @property string $state
 * @property string $zip
 * @property integer $is_preferred
 * @property integer $type
 * @property integer $status
 * @property string $date_created
 * @property string $date_updated
 */
class CustomerCreditCard extends CActiveRecord
{
	private $encryptionKey = 'e95u2xy7';
	
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{customer_credit_card}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('customer_id, is_preferred, type, status', 'numerical', 'integerOnly'=>true),
			array('nick_name, phone_number, credit_card_number, credit_card_type, security_code, expiration_month, expiration_year, first_name, last_name, address, city, state, zip', 'length', 'max'=>255),
			array('date_created, date_updated', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, customer_id, nick_name, phone_number, credit_card_number, credit_card_type, security_code, expiration_month, expiration_year, first_name, last_name, address, city, state, zip, is_preferred, type, status, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'customer' => array(self::BELONGS_TO, 'Customer', 'customer_id'),
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
			'nick_name' => 'Nick Name',
			'phone_number' => 'Phone Number',
			'credit_card_number' => 'Credit Card Number',
			'credit_card_type' => 'Credit Card Type',
			'security_code' => 'Security Code',
			'expiration_month' => 'Expiration Month',
			'expiration_year' => 'Expiration Year',
			'first_name' => 'First Name',
			'last_name' => 'Last Name',
			'address' => 'Address',
			'city' => 'City',
			'state' => 'State',
			'zip' => 'Zip',
			'is_preferred' => 'Is Preferred',
			'type' => 'Type',
			'status' => 'Status',
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
		$criteria->compare('nick_name',$this->nick_name,true);
		$criteria->compare('phone_number',$this->phone_number,true);
		$criteria->compare('credit_card_number',$this->credit_card_number,true);
		$criteria->compare('credit_card_type',$this->credit_card_type,true);
		$criteria->compare('security_code',$this->security_code,true);
		$criteria->compare('expiration_month',$this->expiration_month,true);
		$criteria->compare('expiration_year',$this->expiration_year,true);
		$criteria->compare('first_name',$this->first_name,true);
		$criteria->compare('last_name',$this->last_name,true);
		$criteria->compare('address',$this->address,true);
		$criteria->compare('city',$this->city,true);
		$criteria->compare('state',$this->state,true);
		$criteria->compare('zip',$this->zip,true);
		$criteria->compare('is_preferred',$this->is_preferred);
		$criteria->compare('type',$this->type);
		$criteria->compare('status',$this->status);
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
	 * @return CustomerCreditCard the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	private function encryptData($str)
	{
		$block = mcrypt_get_block_size('des', 'ecb');
		
		if( ($pad = $block - (strlen($str) % $block)) < $block ) 
		{
			$str .= str_repeat(chr($pad), $pad);
		}
		
		return base64_encode(mcrypt_encrypt(MCRYPT_DES, $this->encryptionKey, $str, MCRYPT_MODE_ECB));
	}
	
	public function beforeSave()
	{
		$this->credit_card_number = $this->encryptData($this->credit_card_number);
		
		if( $this->isNewRecord )
		{
			$this->date_created = $this->date_updated = date("Y-m-d H:i:s");
		}
		else
		{
			$this->date_updated = date("Y-m-d H:i:s");
		}
		
		return parent::beforeSave();
	}
	
	
	public static function cardExpirationMonths()
	{
		$cardExpirationMonths = array();
		
		foreach (range(1, 12) as $month)
		{
			$cardExpirationMonths[date('m', strtotime('2013-' . $month . '-1'))] = date('F', strtotime('2013-' . $month . '-1'));
		}
		
		return $cardExpirationMonths;
	}

	
	public static function cardExpirationYears()
	{
		$cardExpirationYears = array();
		
		foreach (range(date('Y'), (date('Y') + 10)) as $year)
		{
			$cardExpirationYears[$year] = $year;
		}
		
		return $cardExpirationYears;
	}

	public static function cardTypes()
	{
		return array(
			'Amex' => 'American Express',
			'Discover' => 'Discover',
			'MasterCard' => 'MasterCard',
			'Visa' => 'Visa',
		);
	}
	
	public static function items($customer_id = null)
	{
		$items = array();
		
		if( $customer_id != null )
		{
			$models = self::model()->findAll(array(
				'condition' => 'customer_id = :customer_id AND status=1',
				'params' => array(
					':customer_id' => $customer_id,
				),
			));
		}
		else
		{
			$models = self::model()->findAll();
		}
		
		foreach($models as $model)
		{
			$items[$model->id] = !empty($model->nick_name) ? $model->nick_name : $model->credit_card_type;
		}
		
		return $items;
	}
}
