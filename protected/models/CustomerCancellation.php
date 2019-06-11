<?php

/**
 * This is the model class for table "{{customer_cancellation}}".
 *
 * The followings are the available columns in table '{{customer_cancellation}}':
 * @property integer $id
 * @property integer $customer_id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $phone_number
 * @property string $reason
 * @property string $other_reason
 * @property string $agent_signature
 * @property integer $type
 * @property integer $status
 * @property string $date_created
 * @property string $date_updated
 */
class CustomerCancellation extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{customer_cancellation}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('first_name, last_name, email, phone_number, reason, agent_signature', 'required', 'on' => 'customerCancellation'),
			array('account_id, customer_id, contract_id, skill_id, type, status', 'numerical', 'integerOnly'=>true),
			array('first_name, last_name', 'length', 'max'=>128),
			array('email, reason, agent_signature, token', 'length', 'max'=>255),
			array('phone_number', 'length', 'max'=>20),
			array('start_date, other_reason, date_created, date_updated', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, account_id, customer_id, contract_id, skill_id, first_name, last_name, email, phone_number, reason, other_reason, agent_signature, token, type, status, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'skill' => array(self::BELONGS_TO, 'Skill', 'skill_id'),
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
			'skill_id' => 'Skill',
			'first_name' => 'First Name',
			'last_name' => 'Last Name',
			'email' => 'Email',
			'phone_number' => 'Phone Number',
			'reason' => 'Reason for Cancelling',
			'other_reason' => 'Other',
			'agent_signature' => 'Agent Signature',
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
		$criteria->compare('first_name',$this->first_name,true);
		$criteria->compare('last_name',$this->last_name,true);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('phone_number',$this->phone_number,true);
		$criteria->compare('reason',$this->reason,true);
		$criteria->compare('other_reason',$this->other_reason,true);
		$criteria->compare('agent_signature',$this->agent_signature,true);
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
	 * @return CustomerCancellation the static model class
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
