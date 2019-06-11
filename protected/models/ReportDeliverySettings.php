<?php

/**
 * This is the model class for table "{{report_delivery_settings}}".
 *
 * The followings are the available columns in table '{{report_delivery_settings}}':
 * @property integer $id
 * @property integer $account_id
 * @property integer $skill_id
 * @property integer $customer_id
 * @property string $report_name
 * @property string $auto_email_frequency
 * @property string $auto_email_recipients
 * @property integer $status
 * @property integer $type
 * @property string $date_created
 * @property string $date_updated
 */
class ReportDeliverySettings extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{report_delivery_settings}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('account_id, skill_id, customer_id, report_name, auto_email_frequency', 'required'),
			array('account_id, skill_id, customer_id, status, type', 'numerical', 'integerOnly'=>true),
			array('report_name', 'length', 'max'=>128),
			array('auto_email_frequency', 'length', 'max'=>50),
			array('auto_email_recipients', 'length', 'max'=>255),
			array('date_last_sent, date_created, date_updated', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, account_id, skill_id, customer_id, report_name, auto_email_frequency, auto_email_recipients, status, type, date_last_sent, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'account' => array(self::BELONGS_TO, 'Account', 'account_id'),
			'skill' => array(self::BELONGS_TO, 'Skill', 'skill_id'),
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
			'account_id' => 'Account',
			'skill_id' => 'Skill',
			'customer_id' => 'Customer',
			'report_name' => 'Report Name',
			'auto_email_frequency' => 'Auto Email Frequency',
			'auto_email_recipients' => 'Auto Email Recipients',
			'status' => 'Status',
			'type' => 'Type',
			'date_last_sent' => 'Date Last Sent',
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
		$criteria->compare('skill_id',$this->skill_id);
		$criteria->compare('customer_id',$this->customer_id);
		$criteria->compare('report_name',$this->report_name,true);
		$criteria->compare('auto_email_frequency',$this->auto_email_frequency,true);
		$criteria->compare('auto_email_recipients',$this->auto_email_recipients,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('type',$this->type);
		$criteria->compare('date_last_sent',$this->date_last_sent,true);
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
	 * @return ReportDeliverySettings the static model class
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
