<?php

/**
 * This is the model class for table "{{sms_employee_receiver}}".
 *
 * The followings are the available columns in table '{{sms_employee_receiver}}':
 * @property integer $id
 * @property integer $account_pto_form_id
 * @property integer $account_id
 * @property integer $security_group_id
 * @property string $mobile_number
 * @property string $api_code
 * @property string $api_message
 */
class AccountPtoFormSms extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{account_pto_form_sms}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('account_pto_form_id, account_id, mobile_number', 'required'),
			array('account_pto_form_id, account_id', 'numerical', 'integerOnly'=>true),
			array('mobile_number, api_code, api_message', 'length', 'max'=>255),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, account_pto_form_id, account_id, mobile_number, api_code, api_message', 'safe', 'on'=>'search'),
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
			'sms' => array(self::BELONGS_TO, 'SmsEmployee', 'account_pto_form_id'),
			'account' => array(self::BELONGS_TO, 'Account', 'account_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'account_pto_form_id' => 'Sms Employee',
			'account_id' => 'Employee Account',
			'mobile_number' => 'Mobile Number',
			'api_code' => 'Api Code',
			'api_message' => 'Api Message',
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
		$criteria->compare('account_pto_form_id',$this->account_pto_form_id);
		$criteria->compare('account_id',$this->account_id);
		$criteria->compare('mobile_number',$this->mobile_number,true);
		$criteria->compare('api_code',$this->api_code,true);
		$criteria->compare('api_message',$this->api_message,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return SmsEmployeeReceiver the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
