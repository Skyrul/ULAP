<?php

/**
 * This is the model class for table "tier".
 *
 * The followings are the available columns in table 'tier':
 * @property integer $id
 * @property integer $parent_tier_id
 * @property integer $parent_sub_tier_id
 * @property integer $company_id
 * @property string $tier_name
 * @property string $description
 * @property string $contact
 * @property string $position
 * @property string $email_address
 * @property string $address
 * @property string $phone_office
 * @property string $mobile_phone
 * @property integer $billing_cycle
 * @property string $define
 * @property integer $volume_low
 * @property integer $volume_high
 * @property double $volume_amount
 * @property integer $volume_subsidy
 * @property string $tier_off
 * @property double $tier_off_amount
 * @property string $volume_fee_start
 * @property double $volume_fee_start_amount
 * @property string $volume_fee_start_billed
 * @property string $volume_fee_success_billed
 * @property integer $status
 * @property integer $tier_level
 * @property string $date_created
 * @property string $date_updated
 */
class Tier extends CActiveRecord
{
	const STATUS_ACTIVE = 1;
	const STATUS_INACTIVE = 2;
	
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{tier}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('company_id, tier_name', 'required'),
			array('parent_tier_id, parent_sub_tier_id, company_id, billing_cycle, volume_low, volume_high, volume_subsidy, status, tier_level, is_deleted', 'numerical', 'integerOnly'=>true),
			array('volume_amount, tier_off_amount, volume_fee_start_amount', 'numerical'),
			array('tier_name, description, contact, position, email_address, address, phone_office, mobile_phone, define, tier_off, volume_fee_start, volume_fee_success_billed', 'length', 'max'=>255),
			array('volume_fee_start_billed, date_created, date_updated', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, parent_tier_id, parent_sub_tier_id, company_id, tier_name, description, contact, position, email_address, address, phone_office, mobile_phone, billing_cycle, define, volume_low, volume_high, volume_amount, volume_subsidy, tier_off, tier_off_amount, volume_fee_start, volume_fee_start_amount, volume_fee_start_billed, volume_fee_success_billed, status, tier_level, date_created, date_updated, is_deleted', 'safe', 'on'=>'search'),
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
			'parent_tier_id' => 'Parent Tier',
			'parent_sub_tier_id' => 'Parent Sub Tier',
			'company_id' => 'Company',
			'tier_name' => 'Tier Name',
			'description' => 'Description',
			'contact' => 'Contact',
			'position' => 'Position',
			'email_address' => 'Email Address',
			'address' => 'Address',
			'phone_office' => 'Phone Office',
			'mobile_phone' => 'Mobile Phone',
			'billing_cycle' => 'Billing Cycle',
			'define' => 'Define',
			'volume_low' => 'Volume Low',
			'volume_high' => 'Volume High',
			'volume_amount' => 'Volume Amount',
			'volume_subsidy' => 'Volume Subsidy',
			'tier_off' => 'Tier Off',
			'tier_off_amount' => 'Tier Off Amount',
			'volume_fee_start' => 'Volume Fee Start',
			'volume_fee_start_amount' => 'Volume Fee Start Amount',
			'volume_fee_start_billed' => 'Volume Fee Start Billed',
			'volume_fee_success_billed' => 'Volume Fee Success Billed',
			'status' => 'Status',
			'tier_level' => 'Tier Level',
			'date_created' => 'Date Created',
			'date_updated' => 'Date Updated',
			'is_deleted' => 'Is Deleted',
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
		$criteria->compare('parent_tier_id',$this->parent_tier_id);
		$criteria->compare('parent_sub_tier_id',$this->parent_sub_tier_id);
		$criteria->compare('company_id',$this->company_id);
		$criteria->compare('tier_name',$this->tier_name,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('contact',$this->contact,true);
		$criteria->compare('position',$this->position,true);
		$criteria->compare('email_address',$this->email_address,true);
		$criteria->compare('address',$this->address,true);
		$criteria->compare('phone_office',$this->phone_office,true);
		$criteria->compare('mobile_phone',$this->mobile_phone,true);
		$criteria->compare('billing_cycle',$this->billing_cycle);
		$criteria->compare('define',$this->define,true);
		$criteria->compare('volume_low',$this->volume_low);
		$criteria->compare('volume_high',$this->volume_high);
		$criteria->compare('volume_amount',$this->volume_amount);
		$criteria->compare('volume_subsidy',$this->volume_subsidy);
		$criteria->compare('tier_off',$this->tier_off,true);
		$criteria->compare('tier_off_amount',$this->tier_off_amount);
		$criteria->compare('volume_fee_start',$this->volume_fee_start,true);
		$criteria->compare('volume_fee_start_amount',$this->volume_fee_start_amount);
		$criteria->compare('volume_fee_start_billed',$this->volume_fee_start_billed,true);
		$criteria->compare('volume_fee_success_billed',$this->volume_fee_success_billed,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('tier_level',$this->tier_level);
		$criteria->compare('date_created',$this->date_created,true);
		$criteria->compare('date_updated',$this->date_updated,true);
		$criteria->compare('is_deleted',$this->is_deleted);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Tier the static model class
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
	
	public function byCompanyId($companyId)
	{
		$criteria = new CDbCriteria;
		$criteria->compare('company_id', $companyId);
		
		$this->getDbCriteria()->mergeWith($criteria);
		return $this;
	}
}
