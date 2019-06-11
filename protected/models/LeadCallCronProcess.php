<?php

/**
 * This is the model class for table "{{lead_call_cron_process}}".
 *
 * The followings are the available columns in table '{{lead_call_cron_process}}':
 * @property integer $id
 * @property integer $lead_id
 * @property integer $lead_hopper_id
 * @property integer $customer_id
 * @property integer $agent_account_id
 * @property integer $lead_call_history_id
 * @property integer $calendar_appointment_id
 * @property string $disposition
 * @property integer $disposition_id
 * @property string $disposition_detail
 * @property integer $disposition_detail_id
 * @property integer $is_skill_child
 * @property integer $hopper_type
 * @property string $lead_phone_type
 * @property integer $lead_list_id
 * @property string $lead_timezone
 * @property string $lead_language
 * @property string $note
 * @property string $callback_time
 * @property integer $is_pending
 * @property string $date_created
 * @property string $date_updated
 */
class LeadCallCronProcess extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{lead_call_cron_process}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('lead_id, lead_hopper_id, customer_id, agent_account_id, lead_call_history_id, calendar_appointment_id, disposition_id, disposition_detail_id, is_skill_child, hopper_type, lead_list_id, is_pending', 'numerical', 'integerOnly'=>true),
			array('disposition, disposition_detail', 'length', 'max'=>255),
			array('lead_phone_type, lead_timezone, lead_language', 'length', 'max'=>128),
			array('note, callback_time, date_created, date_updated', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, lead_id, lead_hopper_id, customer_id, agent_account_id, lead_call_history_id, calendar_appointment_id, disposition, disposition_id, disposition_detail, disposition_detail_id, is_skill_child, hopper_type, lead_phone_type, lead_list_id, lead_timezone, lead_language, note, callback_time, is_pending, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'lead' => array(self::BELONGS_TO, 'Lead', 'lead_id'),
			'lists' => array(self::BELONGS_TO, 'Lists', 'lead_list_id'),
			'customer' => array(self::BELONGS_TO, 'Customer', 'customer_id'),
			'agentAccount' => array(self::BELONGS_TO, 'Account', 'agent_account_id'),
			'calendarAppointment' => array(self::BELONGS_TO, 'CalendarAppointment', 'calendar_appointment_id'),
			'leadCallHistory' => array(self::BELONGS_TO, 'LeadCallHistory', 'lead_call_history_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'lead_id' => 'Lead',
			'lead_hopper_id' => 'Lead Hopper',
			'customer_id' => 'Customer',
			'agent_account_id' => 'Agent Account',
			'lead_call_history_id' => 'Lead Call History',
			'calendar_appointment_id' => 'Calendar Appointment',
			'disposition' => 'Disposition',
			'disposition_id' => 'Disposition',
			'disposition_detail' => 'Disposition Detail',
			'disposition_detail_id' => 'Disposition Detail',
			'is_skill_child' => 'Is Skill Child',
			'hopper_type' => 'Hopper Type',
			'lead_phone_type' => 'Lead Phone Type',
			'lead_list_id' => 'Lead List',
			'lead_timezone' => 'Lead Timezone',
			'lead_language' => 'Lead Language',
			'note' => 'Note',
			'callback_time' => 'Callback Time',
			'is_pending' => 'Is Pending',
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
		$criteria->compare('lead_id',$this->lead_id);
		$criteria->compare('lead_hopper_id',$this->lead_hopper_id);
		$criteria->compare('customer_id',$this->customer_id);
		$criteria->compare('agent_account_id',$this->agent_account_id);
		$criteria->compare('lead_call_history_id',$this->lead_call_history_id);
		$criteria->compare('calendar_appointment_id',$this->calendar_appointment_id);
		$criteria->compare('disposition',$this->disposition,true);
		$criteria->compare('disposition_id',$this->disposition_id);
		$criteria->compare('disposition_detail',$this->disposition_detail,true);
		$criteria->compare('disposition_detail_id',$this->disposition_detail_id);
		$criteria->compare('is_skill_child',$this->is_skill_child);
		$criteria->compare('hopper_type',$this->hopper_type);
		$criteria->compare('lead_phone_type',$this->lead_phone_type,true);
		$criteria->compare('lead_list_id',$this->lead_list_id);
		$criteria->compare('lead_timezone',$this->lead_timezone,true);
		$criteria->compare('lead_language',$this->lead_language,true);
		$criteria->compare('note',$this->note,true);
		$criteria->compare('callback_time',$this->callback_time,true);
		$criteria->compare('is_pending',$this->is_pending);
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
	 * @return LeadCallCronProcess the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	protected function beforeSave()
	{
		if (parent::beforeSave())
		{
			if ($this->isNewRecord)
			{
				$this->date_created = $this->date_updated = date('Y-m-d H:i:s');
			}
			else
			{
				$this->date_updated = date('Y-m-d H:i:s');
			}
			
			return true;
		}
	}
}
