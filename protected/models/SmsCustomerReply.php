<?php

/**
 * This is the model class for table "{{sms_customer_reply}}".
 *
 * The followings are the available columns in table '{{sms_customer_reply}}':
 * @property integer $id
 * @property integer $customer_id
 * @property integer $lead_id
 * @property integer $lead_call_history_id
 * @property integer $lead_phone_number
 * @property string $reply_note
 * @property integer $type
 * @property integer $status
 * @property string $date_created
 * @property string $date_updated
 */
class SmsCustomerReply extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{sms_customer_reply}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('customer_id, lead_id, lead_call_history_id, lead_phone_number, reply_note', 'required'),
			array('customer_id, lead_id, lead_call_history_id, lead_phone_number, type, status', 'numerical', 'integerOnly'=>true),
			array('date_created, date_updated', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, customer_id, lead_id, lead_call_history_id, lead_phone_number, reply_note, type, status, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'lead_id' => 'Lead',
			'lead_call_history_id' => 'Lead Call History',
			'lead_phone_number' => 'Lead Phone Number',
			'reply_note' => 'Reply Note',
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
		$criteria->compare('lead_id',$this->lead_id);
		$criteria->compare('lead_call_history_id',$this->lead_call_history_id);
		$criteria->compare('lead_phone_number',$this->lead_phone_number);
		$criteria->compare('reply_note',$this->reply_note,true);
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
	 * @return SmsCustomerReply the static model class
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
