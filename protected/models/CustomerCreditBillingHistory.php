<?php

/**
 * This is the model class for table "{{customer_credit_billing_history}}".
 *
 * The followings are the available columns in table '{{customer_credit_billing_history}}':
 * @property integer $id
 * @property integer $customer_id
 * @property integer $contract_id
 * @property integer $customer_billing_id
 * @property integer $customer_credit_id
 * @property string $date_created
 * @property string $date_updated
 */
class CustomerCreditBillingHistory extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{customer_credit_billing_history}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('customer_id, contract_id, customer_credit_id, customer_billing_id', 'required'),
			array('customer_id, contract_id, customer_credit_id, customer_billing_id', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, customer_id, contract_id, customer_credit_id, customer_billing_id, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'customerBilling' => array(self::BELONGS_TO, 'CustomerBilling', 'customer_billing_id'),
			'customerCredit' => array(self::BELONGS_TO, 'CustomerCredit', 'customer_credit_id'),
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
			'contract_id' => 'Contract',
			'customer_billing_id' => 'Customer Billing',
			'customer_credit_id' => 'Customer Credit',
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
		$criteria->compare('contract_id',$this->contract_id);
		$criteria->compare('customer_credit_id',$this->customer_credit_id);
		$criteria->compare('customer_billing_id',$this->customer_billing_id);
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
	 * @return CustomerCreditBillingHistory the static model class
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
