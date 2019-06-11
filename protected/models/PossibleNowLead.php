<?php

/**
 * This is the model class for table "{{possible_now_lead}}".
 *
 * The followings are the available columns in table '{{possible_now_lead}}':
 * @property integer $id
 * @property integer $company_id
 * @property integer $customer_id
 * @property integer $lead_id
 * @property string $phone_number
 * @property string $phone_number_type
 * @property string $date_created
 */
class PossibleNowLead extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{possible_now_lead}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('company_id, customer_id, lead_id, phone_number, phone_number_type, date_created', 'required'),
			array('company_id, customer_id, lead_id', 'numerical', 'integerOnly'=>true),
			array('phone_number', 'length', 'max'=>10),
			array('phone_number_type', 'length', 'max'=>20),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, company_id, customer_id, lead_id, phone_number, phone_number_type, date_created', 'safe', 'on'=>'search'),
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
			'customer' => array(self::BELONGS_TO, 'Customer', 'customer_id'),
			'lead' => array(self::BELONGS_TO, 'Lead', 'lead_id'),
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
			'customer_id' => 'Customer',
			'lead_id' => 'Lead',
			'phone_number' => 'Phone Number',
			'phone_number_type' => 'Phone Number Type',
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
		$criteria->compare('company_id',$this->company_id);
		$criteria->compare('customer_id',$this->customer_id);
		$criteria->compare('lead_id',$this->lead_id);
		$criteria->compare('phone_number',$this->phone_number,true);
		$criteria->compare('phone_number_type',$this->phone_number_type,true);
		$criteria->compare('date_created',$this->date_created,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return PossibleNowLead the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
