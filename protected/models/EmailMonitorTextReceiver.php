<?php

/**
 * This is the model class for table "{{email_monitor_text_receiver}}".
 *
 * The followings are the available columns in table '{{email_monitor_text_receiver}}':
 * @property integer $id
 * @property integer $email_monitor_id
 * @property integer $staff_id
 * @property string $mobile_number
 * @property string $api_code
 * @property string $api_message
 */
class EmailMonitorTextReceiver extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{email_monitor_text_receiver}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('email_monitor_id, staff_id, mobile_number', 'required'),
			array('email_monitor_id, staff_id', 'numerical', 'integerOnly'=>true),
			array('mobile_number, api_code, api_message', 'length', 'max'=>255),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, email_monitor_id, staff_id, mobile_number, api_code, api_message', 'safe', 'on'=>'search'),
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
			'email_monitor_id' => 'Email Monitor',
			'staff_id' => 'Staff',
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
		$criteria->compare('email_monitor_id',$this->email_monitor_id);
		$criteria->compare('staff_id',$this->staff_id);
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
	 * @return EmailMonitorTextReceiver the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
