<?php

/**
 * This is the model class for table "{{customer}}".
 *
 * The followings are the available columns in table '{{customer}}':
 * @property integer $id
 * @property string $firstname
 * @property string $middlename
 * @property string $lastname
 * @property string $gender
 * @property string $phone
 * @property string $fax
 * @property string $mobile
 * @property string $email_address
 * @property string $address1
 * @property string $address2
 * @property string $city
 * @property integer $state
 * @property string $zip
 * @property integer $status
 * @property integer $is_deleted
 * @property string $date_created
 * @property string $date_updated
 *
 * The followings are the available model relations:
 * @property State $state0
 * @property CustomerCompany[] $customerCompanies
 * @property CustomerOffice[] $customerOffices
 * @property CustomerOfficeStaff[] $customerOfficeStaff
 */
class Customer extends CActiveRecord
{
	const STATUS_ACTIVE = 1;
	const STATUS_INACTIVE = 2;
	
	public $_phone_timezone_list;
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{customer}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('firstname, lastname, company_id, status, phone, phone_timezone, email_address, address1, city, state, zip', 'required'),
			array('email_address', 'email'),
			array('account_id, fileupload_id, voiceupload_id, company_id, tier_id, state, status, is_deleted, sales_rep_account_id', 'numerical', 'integerOnly'=>true),
			array('firstname, middlename, lastname, name_alias', 'length', 'max'=>120),
			array('gender', 'length', 'max'=>6),
			array('phone, phone_timezone, fax, mobile', 'length', 'max'=>60),
			array('import_customer_primary_key', 'unique'),
			array('import_customer_primary_key, import_agent_id', 'length', 'max'=>60),
			array('email_address', 'length', 'max'=>128),
			array('address1, address2', 'length', 'max'=>250),
			array('notes, direction, landmark', 'safe'),
			array('city', 'length', 'max'=>64),
			array('zip', 'length', 'max'=>12),
			array('account_number', 'length', 'max'=>20),
			array('custom_customer_id', 'length', 'max'=>10),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, company_id, tier_id, firstname, middlename, lastname, gender, phone, fax, mobile, email_address, address1, address2, city, state, zip, status, is_deleted, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'company' => array(self::BELONGS_TO, 'Company', 'company_id'),
			'tier' => array(self::BELONGS_TO, 'Tier', 'tier_id'),
			'state0' => array(self::BELONGS_TO, 'State', 'state'),
			'customerSkills' => array(self::HAS_MANY, 'CustomerSkill', 'customer_id'),
			'customerCompanies' => array(self::HAS_MANY, 'CustomerCompany', 'customer_id'),
			'customerOffices' => array(self::HAS_MANY, 'CustomerOffice', 'customer_id'),
			'customerOfficeStaff' => array(self::HAS_MANY, 'CustomerOfficeStaff', 'customer_id'),
			'officeStaff' => array(self::HAS_MANY, 'CustomerOfficeStaff', 'customer_id'),
			'fileupload' => array(self::BELONGS_TO, 'Fileupload', 'fileupload_id'),
			'voiceupload' => array(self::BELONGS_TO, 'Fileupload', 'voiceupload_id'),
			'account' => array(self::BELONGS_TO, 'Account', 'account_id'),
			'latestCustomerFile' => array(self::HAS_ONE, 'CustomerFile', 'customer_id', 'order'=>'latestCustomerFile.date_created DESC'),
			'queueViewer' => array(self::HAS_ONE, 'CustomerQueueViewer', 'customer_id'),
			'queueViewerTest' => array(self::HAS_ONE, 'CustomerQueueViewerTest', 'customer_id'),
			'customerHistory' => array(self::HAS_ONE, 'CustomerHistory', 'customer_id'),
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
			'tier_id' => 'Tier',
			'firstname' => ($this->scenario == 'hostDial') ? 'Property Name' : 'Firstname',
			'middlename' => 'Middlename',
			'lastname' => ($this->scenario == 'hostDial') ? 'Primary Contact' : 'Lastname',
			'gender' => 'Gender',
			'phone' => 'Phone',
			'phone_timezone' => 'Phone Time Zone',
			'fax' => 'Fax',
			'mobile' => 'Mobile',
			'email_address' => 'Email Address',
			'address1' => 'Address1',
			'address2' => 'Address2',
			'city' => 'City',
			'state' => 'State',
			'zip' => 'Zip',
			'status' => 'Status',
			'is_deleted' => 'Is Deleted',
			'date_created' => 'Date Created',
			'date_updated' => 'Date Updated',
			'statusLabel' => 'statusLabel',
			'fileupload_id' => 'Fileupload',
			'voiceupload_id' => 'Voice upload',
			'notes' => 'Customer Notes to Agent',
			'direction' => 'Direction',
			'landmark' => 'Landmark',
			// 'custom_customer_id' => 'Customer ID',
			'custom_customer_id' => 'Agent ID',
			'account_number' => 'Account Number',
			'name_alias' => 'Also Known As',
			'sales_rep_account_id' => 'Sales Rep',
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
		$criteria->compare('firstname',$this->firstname,true);
		$criteria->compare('middlename',$this->middlename,true);
		$criteria->compare('lastname',$this->lastname,true);
		$criteria->compare('gender',$this->gender,true);
		$criteria->compare('phone',$this->phone,true);
		$criteria->compare('fax',$this->fax,true);
		$criteria->compare('mobile',$this->mobile,true);
		$criteria->compare('email_address',$this->email_address,true);
		$criteria->compare('address1',$this->address1,true);
		$criteria->compare('address2',$this->address2,true);
		$criteria->compare('city',$this->city,true);
		$criteria->compare('state',$this->state);
		$criteria->compare('zip',$this->zip,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('is_deleted',$this->is_deleted);
		$criteria->compare('date_created',$this->date_created,true);
		$criteria->compare('date_updated',$this->date_updated,true);
		$criteria->compare('custom_customer_id',$this->custom_customer_id,true);
		$criteria->compare('account_number',$this->account_number,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Customer the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
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
		
		
		// if($this->isNewRecord || empty($this->account_number))
		if($this->isNewRecord)
		{
			$this->account_number = $this->generateAccountNumber();
		}
		
		return parent::beforeSave();
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

	public function getFullName()
	{
		if(!empty($this->middlename))
			return $this->firstname.' '.$this->middlename.' '.$this->lastname;
		else
			return $this->firstname.' '.$this->lastname;
	}
	
	public function getFullNameReverse()
	{
		if(!empty($this->middlename))
			return $this->lastname.', '.$this->firstname.' '.$this->middlename;
		else
			return $this->lastname.', '.$this->firstname;
	}

	public function byStatus($status)
	{
		$criteria = new CDbCriteria;
		$criteria->compare('status',$status);
		$criteria->compare('is_deleted',0);
		$this->getDbCriteria()->mergeWith($criteria);
		return $this;
	}
	
	public function byIsDeletedNot()
	{
		$criteria = new CDbCriteria;
		$criteria->compare('t.is_deleted',0);
		
		$this->getDbCriteria()->mergeWith($criteria);
		return $this;
	}
	
	public function byAccountId($accountId)
	{
		$criteria = new CDbCriteria;
		$criteria->compare('t.account_id', $accountId);
		
		$this->getDbCriteria()->mergeWith($criteria);
		return $this;
	}
	
	public function byCompanyId($companyId)
	{
		$criteria = new CDbCriteria;
		$criteria->compare('t.company_id', $companyId);
		
		$this->getDbCriteria()->mergeWith($criteria);
		return $this;
	}
	
	public function getImage()
	{
		if(isset($this->fileupload))
		{
			return $this->fileupload->getImageFullPath();
		}
		
		return null;
	}

	public function getVoice()
	{
		if(isset($this->voiceupload))
		{
			return $this->voiceupload->getVoiceFullPath();
		}
		
		return null;
	}
	
	
	
	public function generateAccountNumber()
	{
		$dateToBeUsed = date('Y-m-d');
		
		if(!$this->isNewRecord && empty($this->account_number))
		{
			$dateToBeUsed = $this->date_created;
		}
		
		// echo $dateToBeUsed; exit;
		#generate Account Number, based on the Year - Month - and the number of customer for the said month.

		$criteria = new CDbCriteria;
		$criteria->addCondition('DAY(date_created) = DAY(\''.$dateToBeUsed.'\') ');
		$criteria->addCondition('MONTH(date_created) = MONTH(\''.$dateToBeUsed.'\') ');
		$criteria->addCondition('YEAR(date_created) = YEAR(\''.$dateToBeUsed.'\') ');
		
		$customers = Customer::model()->findAll($criteria);
		
		$ctr = count($customers) + 1;
		
		$ctr = str_pad($ctr, 2, "0", STR_PAD_LEFT);
		
		$yyyy = date("Y",strtotime($dateToBeUsed));
		$mm = date("m",strtotime($dateToBeUsed));
		$dd = date("d",strtotime($dateToBeUsed));
		
		$accountNumber = $yyyy.$mm.$dd.$ctr;
		
		### find customer account number if there will be matching, regenerate the accountNumber###
		
		do
		{
			$criteria = new CDbCriteria;
			$criteria->compare('account_number', $accountNumber);
			$customer = Customer::model()->find($criteria);
			
			if($customer !== null)
			{
				$ctr++;
				$ctr = str_pad($ctr, 2, "0", STR_PAD_LEFT);
				
				$accountNumber = $yyyy.$mm.$dd.$ctr;
			}
		}
		while($customer !== null);
		
		return $accountNumber;
	}
	
	
	public function orderByDateCreated($type='DESC')
	{
		$criteria = new CDbCriteria;
		$criteria->with = array('account', 'latestCustomerFile');
		$criteria->together = true;
		$criteria->order = 'account.date_created ' . $type;
		
		$this->getDbCriteria()->mergeWith($criteria);
		return $this;
	}
	
	public function searchAndFilter($criteria)
	{
		$this->getDbCriteria()->mergeWith($criteria);
		return $this;
	}

	
	public function getTimeZone()
	{
		if( $this->phone_timezone !== null )
		{
			return $this->phone_timezone;
		}
		else
		{
			return AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $this->phone) );
		}
	}
	
	public function getCustomerSkill($skill_id)
	{
		$result = array(
			'start_date' => '',
			'end_date' => '',
		);
		
		$customerSkill = CustomerSkill::model()->find(array(
			'condition' => '
				customer_id = :customer_id 
				AND skill_id = :skill_id
				AND status = 1
			',
			'params' => array(
				':customer_id' => $this->id,
				':skill_id' => $skill_id,
			),
		));
		
		if( $customerSkill )
		{
			if( !empty($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00' )
			{
				$result['start_date'] = date('m/d/Y', strtotime($customerSkill->start_month));
			}
			
			if( !empty($customerSkill->end_month) && $customerSkill->end_month != '0000-00-00' )
			{
				$result['end_date'] = date('m/d/Y', strtotime($customerSkill->end_month));
			}
		}
		
		return $result;
	}
}
