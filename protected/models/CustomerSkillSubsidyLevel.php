<?php

/**
 * This is the model class for table "{{customer_skill_subsidy_level}}".
 *
 * The followings are the available columns in table '{{customer_skill_subsidy_level}}':
 * @property integer $id
 * @property integer $customer_id
 * @property integer $customer_skill_id
 * @property integer $subsidy_level_id
 * @property integer $type
 * @property integer $status
 * @property string $date_created
 * @property string $date_updated
 */
class CustomerSkillSubsidyLevel extends CActiveRecord
{
	const STATUS_ACTIVE = 1;
	const STATUS_INACTIVE = 2;
	
	const TYPE_COMPANY_SUBSIDY_LEVEL = 1;
	const TYPE_TIER_SUBSIDY_LEVEL = 2;
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{customer_skill_subsidy_level}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('customer_id, customer_skill_id, subsidy_level_id, type, status', 'required'),
			array('customer_id, customer_skill_id, subsidy_level_id, type, status', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, customer_id, customer_skill_id, subsidy_level_id, type, status, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'contractSubsidyLevel' => array(self::BELONGS_TO, 'ContractSubsidyLevel', 'subsidy_level_id'),
			'companySubsidyLevel' => array(self::BELONGS_TO, 'CompanySubsidyLevel', 'subsidy_level_id'),
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
			'customer_skill_id' => 'Customer Skill',
			'subsidy_level_id' => 'Subsidy Level',
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
		$criteria->compare('customer_skill_id',$this->customer_skill_id);
		$criteria->compare('subsidy_level_id',$this->subsidy_level_id);
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
	 * @return CustomerSkillSubsidyLevel the static model class
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
	
}
