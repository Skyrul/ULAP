<?php

/**
 * This is the model class for table "{{customer_skill_child}}".
 *
 * The followings are the available columns in table '{{customer_skill_child}}':
 * @property integer $id
 * @property integer $customer_id
 * @property integer $skill_id
 * @property integer $customer_skill_id
 * @property integer $skill_child_id
 * @property integer $is_enabled
 * @property string $date_created
 * @property string $date_updated
 */
class CustomerSkillChild extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{customer_skill_child}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('customer_id, skill_id, customer_skill_id, skill_child_id, is_enabled', 'required'),
			array('customer_id, skill_id, customer_skill_id, skill_child_id, is_enabled', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, customer_id, skill_id, customer_skill_id, skill_child_id, is_enabled, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'skillChild' => array(self::BELONGS_TO, 'SkillChild', 'skill_child_id'),
			'customerSkill' => array(self::BELONGS_TO, 'CustomerSkill', 'customer_skill_id'),
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
			'skill_id' => 'Skill',
			'customer_skill_id' => 'Customer Skill',
			'skill_child_id' => 'Skill Child',
			'is_enabled' => 'Is Enabled',
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
		$criteria->compare('skill_id',$this->skill_id);
		$criteria->compare('customer_skill_id',$this->customer_skill_id);
		$criteria->compare('skill_child_id',$this->skill_child_id);
		$criteria->compare('is_enabled',$this->is_enabled);
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
	 * @return CustomerSkillChild the static model class
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
