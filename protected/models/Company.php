<?php

/**
 * This is the model class for table "{{company}}".
 *
 * The followings are the available columns in table '{{company}}':
 * @property integer $id
 * @property string $company_name
 * @property string $description
 * @property string $email_address
 * @property string $contact
 * @property string $phone
 * @property integer $status
 * @property integer $is_deleted
 * @property string $date_created
 * @property string $date_updated
 */
class Company extends CActiveRecord
{	
	const STATUS_ACTIVE = 1;
	const STATUS_INACTIVE = 2;
	
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{company}}';
	}
 
	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('company_name, status', 'required'),
			array('email_address', 'email'),
			array('account_id, status, display_tab_on_customer, display_flyer_image, is_deleted, is_portal_access, fileupload_id, flyer_fileupload_id, scrub_settings, popup_show, popup_logins, customer_specific_skill_scripts, is_host_dialer, enable_manual_entry', 'numerical', 'integerOnly'=>true),
			array('company_name', 'length', 'max'=>250),
			array('description', 'length', 'max'=>255),
			array('email_address', 'length', 'max'=>128),
			array('contact', 'length', 'max'=>60),
			array('phone', 'length', 'max'=>30),
			array('flyer_message, popup_html_content', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, company_name, description, email_address, contact, phone, status, display_tab_on_customer, display_flyer_image, flyer_message, popup_show, popup_logins, popup_html_content, is_deleted, date_created, date_updated, scrub_settings, customer_specific_skill_scripts, is_host_dialer, enable_manual_entry', 'safe', 'on'=>'search'),
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
			'tiers' => array(self::HAS_MANY, 'Tier', 'company_id'),
			'account' => array(self::BELONGS_TO, 'Account', 'account_id'),
			'fileupload' => array(self::BELONGS_TO, 'Fileupload', 'fileupload_id'),
			'flyerFileupload' => array(self::BELONGS_TO, 'Fileupload', 'flyer_fileupload_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'company_name' => 'Company Name',
			'description' => 'Description',
			'email_address' => 'Email Address',
			'contact' => 'Contact',
			'phone' => 'Phone',
			'status' => 'Status',
			'is_deleted' => 'Is Deleted',
			'is_portal_access' => 'Portal Access',
			'date_created' => 'Date Created',
			'date_updated' => 'Date Updated',
			'fileupload_id' => 'Flyer Image',
			'display_flyer_image' => 'Flyer Display Type',
			'flyer_message' => 'Flyer Html Message',
			'popup_show' => 'Show popup on login',
			'popup_logins' => 'Number of logins the popup will be displayed',
			'popup_html_content' => 'Popup Html Message',
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
		$criteria->compare('company_name',$this->company_name,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('email_address',$this->email_address,true);
		$criteria->compare('contact',$this->contact,true);
		$criteria->compare('phone',$this->phone,true);
		$criteria->compare('status',$this->status);
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
	 * @return Company the static model class
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
	
	public static $listCompanies = null;
	public static function listCompanies()
	{
		if(self::$listCompanies === null)
		{
			self::$listCompanies = CHtml::listData(Company::model()->findAll(),'id','company_name');
		}
		
		return self::$listCompanies;
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

	public $parentTiers = array();
	
	public function parentTiers()
	{
		if(empty($this->parentTiers))
		{
			$criteria = new CDbCriteria;
			$criteria->addCondition('parent_tier_id IS NULL');
			$criteria->addCondition('parent_sub_tier_id IS NULL');
			$criteria->compare('status', Tier::STATUS_ACTIVE);
			$criteria->compare('is_deleted', 0);
			
			$this->parentTiers = $this->tiers($criteria);
		}
		
		return $this->parentTiers;
	}

	public function getImage()
	{
		if(isset($this->fileupload))
		{
			return $this->fileupload->getImageFullPath();
		}
		
		return null;
	}
	
	public function getFlyerImage()
	{
		if(isset($this->flyerFileupload))
		{
			return $this->flyerFileupload->getFlyerImageFullPath();
		}
		
		return null;
	}
	
	public static function getScrubOptions()
	{
		$options = array(
			'0' => 'OFF',
			'1' => 'ON Customer WN',
			'2' => 'ON Customer DNC',
			'3' => 'ON Customer BOTH',
			'4' => 'ON COMPANY DNC',
			'5' => 'ON COMPANY WN',
			'6' => 'ON COMPANY BOTH',
		);
		
		return $options;
	}
}
