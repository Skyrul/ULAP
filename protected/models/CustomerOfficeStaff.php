<?php

/**
 * This is the model class for table "{{customer_office_staff}}".
 *
 * The followings are the available columns in table '{{customer_office_staff}}':
 * @property integer $id
 * @property integer $customer_id
 * @property integer $customer_office_id
 * @property string $staff_name
 * @property string $email_address
 * @property string $position
 * @property integer $is_received_email
 * @property integer $is_portal_access
 * @property string $phone
 * @property string $mobile
 * @property string $fax
 * @property integer $status
 * @property integer $is_deleted
 * @property string $date_created
 * @property string $date_updated
 *
 * The followings are the available model relations:
 * @property Customer $customer
 * @property CustomerOffice $customerOffice
 */
class CustomerOfficeStaff extends CActiveRecord
{
	const STATUS_ACTIVE = 1;
	const STATUS_INACTIVE = 2;
	
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{customer_office_staff}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('customer_id, customer_office_id, staff_name', 'required'),
			array('email_address', 'email'),
			array('customer_id, customer_office_id, account_id, is_received_email, is_received_low_on_names_email, is_portal_access, type, status, enable_texting, is_deleted, sip_username, use_phone_as_dial_as_option', 'numerical', 'integerOnly'=>true),
			array('staff_name, position', 'length', 'max'=>120),
			array('email_address, sip_password', 'length', 'max'=>128),
			array('phone, mobile, fax', 'length', 'max'=>60),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, customer_id, customer_office_id, staff_name, email_address, position, is_received_email, is_received_low_on_names_email, is_portal_access, phone, mobile, fax, type, status, enable_texting, is_deleted, sip_username, sip_password, use_phone_as_dial_as_option, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'customerOffice' => array(self::BELONGS_TO, 'CustomerOffice', 'customer_office_id'),
			'account' => array(self::BELONGS_TO, 'Account', 'account_id'),
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
			'customer_office_id' => 'Customer Office',
			'account_id' => 'Account ID',
			'staff_name' => 'Staff Name',
			'email_address' => 'Email Address',
			'position' => 'Position',
			'is_received_email' => 'Is Received Email',
			'is_portal_access' => 'Is Portal Access',
			'phone' => 'Phone',
			'mobile' => 'Mobile',
			'fax' => 'Fax',
			'type' => 'Type',
			'status' => 'Status',
			'enable_texting' => 'Enable Texting',
			'is_deleted' => 'Is Deleted',
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
		$criteria->compare('customer_office_id',$this->customer_office_id);
		$criteria->compare('account_id',$this->account_id);
		$criteria->compare('staff_name',$this->staff_name,true);
		$criteria->compare('email_address',$this->email_address,true);
		$criteria->compare('position',$this->position,true);
		$criteria->compare('is_received_email',$this->is_received_email);
		$criteria->compare('is_portal_access',$this->is_portal_access);
		$criteria->compare('phone',$this->phone,true);
		$criteria->compare('mobile',$this->mobile,true);
		$criteria->compare('fax',$this->fax,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('enable_texting',$this->enable_texting);
		$criteria->compare('is_deleted',$this->is_deleted);
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
	 * @return CustomerOfficeStaff the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function byCustomerId($customer_id)
	{
		$criteria = new CDbCriteria;
        $criteria->compare('customer_id',$customer_id);

        $this->getDbCriteria()->mergeWith($criteria);

        return $this;
	}
	
	public function byCustomerOfficeId($customer_office_id)
	{
		$criteria = new CDbCriteria;
        $criteria->compare('customer_office_id',$customer_office_id);

        $this->getDbCriteria()->mergeWith($criteria);

        return $this;
	}

	public static function listStatus()
	{
		return array(
			self::STATUS_ACTIVE => 'Active',
			self::STATUS_INACTIVE => 'Inactive',
		);	
	}
	
	public $statusLabel = null;
	public function getStatusLabel()
	{
		if($this->statusLabel === null)
		{
			$listStatus = self::listStatus();
			
			if(isset($listStatus[$this->status]))
			{
				$this->statusLabel = $listStatus[$this->status];
			}
		}
		
		return $this->statusLabel;
	}

	public function beforeSave()
	{
		if($this->isNewRecord)
			$this->date_created = $this->date_updated = date("Y-m-d H:i:s");
		else
			$this->date_updated = date("Y-m-d H:i:s");
		
		return parent::beforeSave();
	}

}
