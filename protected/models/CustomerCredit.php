<?php

/**
 * This is the model class for table "{{customer_credit}}".
 *
 * The followings are the available columns in table '{{customer_credit}}':
 * @property integer $id
 * @property integer $customer_id
 * @property integer $contract_id
 * @property string $description
 * @property string $start_year
 * @property string $start_month
 * @property string $end_month
 * @property double $amount
 * @property integer $type
 * @property integer $status
 * @property string $date_created
 * @property string $date_updated
 */
class CustomerCredit extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{customer_credit}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('customer_id, contract_id, description, amount', 'required'),
			array('start_month, start_year, end_year, end_month', 'required'),
			array('end_year', 'validateYearRange'),
			array('customer_id, contract_id, type, status', 'numerical', 'integerOnly'=>true),
			array('amount', 'numerical'),
			array('description', 'length', 'max'=>255),
			array('start_year, start_month, end_year,end_month', 'length', 'max'=>50),
			array('date_created, date_updated', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, customer_id, contract_id, description, start_year, start_month, end_month, amount, type, status, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'contract' => array(self::BELONGS_TO, 'Contract', 'contract_id'),
			'customerCreditBillingHistory' => array(self::HAS_MANY, 'CustomerCreditBillingHistory', 'customer_credit_id'),
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
			'description' => 'Description',
			'start_year' => 'Start Year',
			'start_month' => 'Start Month',
			'end_year' => 'End Year',
			'end_month' => 'End Month',
			'amount' => 'Amount',
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
		$criteria->compare('contract_id',$this->contract_id);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('start_year',$this->year,true);
		$criteria->compare('end_year',$this->year,true);
		$criteria->compare('start_month',$this->start_month,true);
		$criteria->compare('end_month',$this->end_month,true);
		$criteria->compare('amount',$this->amount);
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
	 * @return CustomerCredit the static model class
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

	public function validateYearRange($attribute,$params)
	{
		$start = strtotime(date($this->start_year.'-'.$this->start_month.'-01'));
		$end = strtotime(date($this->end_year.'-'.$this->end_month.'-01'));
		
		if($start > $end)
			$this->addError($attribute, 'End Month or End Year not valid.');
		
	}
}
