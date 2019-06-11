<?php

/**
 * This is the model class for table "{{growth_report}}".
 *
 * The followings are the available columns in table '{{growth_report}}':
 * @property integer $id
 * @property integer $customer_id
 * @property string $user
 * @property string $skill_start_date
 * @property string $skill_end_date
 * @property string $hold_start_date
 * @property string $hold_end_date
 * @property string $company_name
 * @property string $customer_name
 * @property string $skill_name
 * @property string $contract_name
 * @property string $contract_quantity
 * @property string $contract_amount
 * @property string $new_credit_amount
 * @property string $type
 * @property string $date_entered
 * @property string $date_created
 * @property string $date_updated
 */
class GrowthReport extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{growth_report}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('customer_id', 'numerical', 'integerOnly'=>true),
			array('user, company_name, customer_name, skill_name, contract_name, contract_quantity, contract_amount, new_credit_amount, type', 'length', 'max'=>255),
			array('skill_start_date, skill_end_date, hold_start_date, hold_end_date, date_entered, date_created, date_updated', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, customer_id, user, skill_start_date, skill_end_date, hold_start_date, hold_end_date, company_name, customer_name, skill_name, contract_name, contract_quantity, contract_amount, new_credit_amount, type, date_entered, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'customer_id' => 'Customer',
			'user' => 'User',
			'skill_start_date' => 'Skill Start Date',
			'skill_end_date' => 'Skill End Date',
			'hold_start_date' => 'Hold Start Date',
			'hold_end_date' => 'Hold End Date',
			'company_name' => 'Company Name',
			'customer_name' => 'Customer Name',
			'skill_name' => 'Skill Name',
			'contract_name' => 'Contract Name',
			'contract_quantity' => 'Contract Quantity',
			'contract_amount' => 'Contract Amount',
			'new_credit_amount' => 'New Credit Amount',
			'type' => 'Type',
			'date_entered' => 'Date Entered',
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
		$criteria->compare('user',$this->user,true);
		$criteria->compare('skill_start_date',$this->skill_start_date,true);
		$criteria->compare('skill_end_date',$this->skill_end_date,true);
		$criteria->compare('hold_start_date',$this->hold_start_date,true);
		$criteria->compare('hold_end_date',$this->hold_end_date,true);
		$criteria->compare('company_name',$this->company_name,true);
		$criteria->compare('customer_name',$this->customer_name,true);
		$criteria->compare('skill_name',$this->skill_name,true);
		$criteria->compare('contract_name',$this->contract_name,true);
		$criteria->compare('contract_quantity',$this->contract_quantity,true);
		$criteria->compare('contract_amount',$this->contract_amount,true);
		$criteria->compare('new_credit_amount',$this->new_credit_amount,true);
		$criteria->compare('type',$this->type,true);
		$criteria->compare('date_entered',$this->date_entered,true);
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
	 * @return GrowthReport the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
