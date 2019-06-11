<?php

/**
 * This is the model class for table "{{customer_billing_scheduled}}".
 *
 * The followings are the available columns in table '{{customer_billing_scheduled}}':
 * @property integer $id
 * @property integer $customer_id
 * @property integer $skill_id
 * @property integer $contract_id
 * @property integer $account_id
 * @property string $customer_name
 * @property string $contract
 * @property string $billing_period
 * @property double $amount
 * @property double $original_amount
 * @property double $credit_amount
 * @property double $subsidy_amount
 * @property string $credit_description
 * @property string $date_created
 */
class CustomerBillingScheduled extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{customer_billing_scheduled}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('customer_id, skill_id, contract_id, account_id, customer_name, contract, billing_period, amount, original_amount, credit_amount, subsidy_amount, credit_description', 'required'),
			array('customer_id, skill_id, contract_id, account_id', 'numerical', 'integerOnly'=>true),
			array('amount, original_amount, credit_amount, subsidy_amount', 'numerical'),
			array('customer_name, contract, billing_period, credit_description', 'length', 'max'=>255),
			array('date_created', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, customer_id, skill_id, contract_id, account_id, customer_name, contract, billing_period, amount, original_amount, credit_amount, subsidy_amount, credit_description, date_created', 'safe', 'on'=>'search'),
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
			'skill_id' => 'Skill',
			'contract_id' => 'Contract',
			'account_id' => 'Account',
			'customer_name' => 'Customer Name',
			'contract' => 'Contract',
			'billing_period' => 'Billing Period',
			'amount' => 'Amount',
			'original_amount' => 'Original Amount',
			'credit_amount' => 'Credit Amount',
			'subsidy_amount' => 'Subsidy Amount',
			'credit_description' => 'Credit Description',
			'date_created' => 'Date Created',
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
		$criteria->compare('contract_id',$this->contract_id);
		$criteria->compare('account_id',$this->account_id);
		$criteria->compare('customer_name',$this->customer_name,true);
		$criteria->compare('contract',$this->contract,true);
		$criteria->compare('billing_period',$this->billing_period,true);
		$criteria->compare('amount',$this->amount);
		$criteria->compare('original_amount',$this->original_amount);
		$criteria->compare('credit_amount',$this->credit_amount);
		$criteria->compare('subsidy_amount',$this->subsidy_amount);
		$criteria->compare('credit_description',$this->credit_description,true);
		$criteria->compare('date_created',$this->date_created,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CustomerBillingScheduled the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function beforeSave()
	{
		if( $this->isNewRecord )
		{
			$this->date_created = date("Y-m-d H:i:s");
		}
		
		return parent::beforeSave();
	}
}
