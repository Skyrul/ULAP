<?php

/**
 * This is the model class for table "{{customer_extra}}".
 *
 * The followings are the available columns in table '{{customer_extra}}':
 * @property integer $id
 * @property integer $account_id
 * @property integer $customer_id
 * @property integer $skill_id
 * @property integer $contract_id
 * @property string $description
 * @property integer $quantity
 * @property string $year
 * @property string $month
 * @property integer $type
 * @property integer $status
 * @property string $date_created
 * @property string $date_updated
 */
class CustomerExtra extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{customer_extra}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('account_id, customer_id, skill_id, contract_id, description, quantity, year, month', 'required'),
			array('account_id, customer_id, skill_id, contract_id, quantity, type, status', 'numerical', 'integerOnly'=>true),
			array('description', 'length', 'max'=>255),
			array('year, month', 'length', 'max'=>128),
			array('date_created, date_updated', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, account_id, customer_id, skill_id, contract_id, description, quantity, year, month, type, status, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'account' => array(self::BELONGS_TO, 'Account', 'account_id'),
			'contract' => array(self::BELONGS_TO, 'Contract', 'contract_id'),
			'skill' => array(self::BELONGS_TO, 'Skill', 'skill_id'),
			// 'customerSkill' => array(self::HAS_ONE, 'CustomerSkill', 'skill_id', 'on' => 'customerSkill.skill_id = t.skill_id AND customerSkill.customer_id = t.skill_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'account_id' => 'Account',
			'customer_id' => 'Customer',
			'skill_id' => 'Skill',
			'contract_id' => 'Contract',
			'description' => 'Description',
			'quantity' => 'Quantity',
			'year' => 'Year',
			'month' => 'Month',
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
		$criteria->compare('account_id',$this->account_id);
		$criteria->compare('customer_id',$this->customer_id);
		$criteria->compare('skill_id',$this->skill_id);
		$criteria->compare('contract_id',$this->contract_id);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('quantity',$this->quantity);
		$criteria->compare('year',$this->year,true);
		$criteria->compare('month',$this->month,true);
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
	 * @return CustomerExtra the static model class
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
