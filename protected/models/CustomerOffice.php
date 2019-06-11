<?php

/**
 * This is the model class for table "{{customer_office}}".
 *
 * The followings are the available columns in table '{{customer_office}}':
 * @property integer $id
 * @property integer $customer_id
 * @property string $office_name
 * @property string $email_address
 * @property string $address
 * @property string $phone
 * @property string $city
 * @property string $fax
 * @property integer $state
 * @property string $zip
 * @property string $landmark
 * @property integer $status
 * @property integer $is_deleted
 * @property string $date_created
 * @property string $date_updated
 *
 * The followings are the available model relations:
 * @property Customer $customer
 * @property State $state0
 * @property CustomerOfficeStaff[] $customerOfficeStaff
 */
class CustomerOffice extends CActiveRecord
{
	const STATUS_ACTIVE = 1;
	const STATUS_INACTIVE = 2;
	
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{customer_office}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('customer_id, office_name', 'required'),
			array('email_address', 'email'),
			array('customer_id, state, status, is_deleted', 'numerical', 'integerOnly'=>true),
			array('office_name, email_address', 'length', 'max'=>120),
			array('address, landmark', 'length', 'max'=>250),
			array('phone, city, fax', 'length', 'max'=>60),
			array('zip', 'length', 'max'=>30),
			array('directions', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, customer_id, office_name, email_address, address, phone, city, fax, state, zip, landmark, status, is_deleted, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'state0' => array(self::BELONGS_TO, 'State', 'state'),
			'customerOfficeStaff' => array(self::HAS_MANY, 'CustomerOfficeStaff', 'customer_office_id'),
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
			'office_name' => 'Office Name',
			'email_address' => 'Email Address',
			'address' => 'Address',
			'phone' => 'Phone',
			'city' => 'City',
			'fax' => 'Fax',
			'state' => 'State',
			'zip' => 'Zip',
			'directions' => 'Directions',
			'landmark' => 'Landmark',
			'status' => 'Status',
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

		return new CActiveDataProvider($this);
	}
	
	public function afterFind()
	{
		$this->statusLabel = $this->getStatusLabel();
		
		return parent::afterFind();
	}
	
	public function beforeSave()
	{
		if($this->isNewRecord)
			$this->date_created = $this->date_updated = date("Y-m-d H:i:s");
		else
			$this->date_updated = date("Y-m-d H:i:s");
		
		return parent::beforeSave();
	}
	
	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CustomerOffice the static model class
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
}
