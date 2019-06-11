<?php

/**
 * This is the model class for table "{{lead_hopper}}".
 *
 * The followings are the available columns in table '{{lead_hopper}}':
 * @property integer $id
 * @property integer $lead_id
 * @property integer $list_id
 * @property integer $skill_id
 * @property integer $customer_id
 * @property integer $agent_account_id
 * @property string $lead_timezone
 * @property string $lead_language
 * @property string $status
 * @property integer $type
 * @property integer $priority
 * @property string $callback_date
 * @property string $appointment_date
 */
class LeadHopper extends CActiveRecord
{
	const STATUS_READY = 'READY';
	const STATUS_QUEUE = 'QUEUE';
	const STATUS_INCALL = 'INCALL';
	const STATUS_DISPO = 'DISPO';
	const STATUS_DONE = 'DONE';
	const STATUS_HOLD = 'HOLD';
	const STATUS_DNC = 'DNC';
	const STATUS_CALLBACK = 'CALLBACK';
	const STATUS_CONFIRMATION = 'CONFIRMATION';
	const STATUS_CONFLICT = 'CONFLICT';
	
	const TYPE_CONTACT = 1;
	const TYPE_CALLBACK = 2;
	const TYPE_CONFIRMATION_CALL = 3;
	const TYPE_LEAD_SEARCH = 4;
	const TYPE_CONFLICT = 5;
	const TYPE_RESCHEDULE = 6;
	const TYPE_NO_SHOW_RESCHEDULE = 7;
	
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{lead_hopper}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('lead_id, list_id, skill_id, customer_id, agent_account_id, type, priority, skill_child_confirmation_id, skill_child_reschedule_id, calendar_appointment_id', 'numerical', 'integerOnly'=>true),
			array('lead_timezone, batch_number', 'length', 'max'=>255),
			array('lead_language', 'length', 'max'=>50),
			array('status', 'length', 'max'=>15),
			array('callback_date, appointment_date', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, lead_id, list_id, skill_id, customer_id, agent_account_id, calendar_appointment_id, lead_timezone, batch_number, lead_language, status, type, priority, callback_date, appointment_date', 'safe', 'on'=>'search'),
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
			'list' => array(self::BELONGS_TO, 'Lists', 'list_id'),
			'skill' => array(self::BELONGS_TO, 'Skill', 'skill_id'),
			'customer' => array(self::BELONGS_TO, 'Customer', 'customer_id'),
			'currentAgentAccount' => array(self::BELONGS_TO, 'Account', 'agent_account_id'),
			'calendarAppointment' => array(self::BELONGS_TO, 'CalendarAppointment', 'calendar_appointment_id'),
			'customerSkillChild' => array(self::BELONGS_TO, 'CustomerSkillChild', 'skill_child_confirmation_id'),
			'confirmChildSkill' => array(self::BELONGS_TO, 'SkillChild', 'skill_child_confirmation_id'),
			'rescheduleChildSkill' => array(self::BELONGS_TO, 'SkillChild', 'skill_child_reschedule_id'),
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
			'list_id' => 'List',
			'skill_id' => 'Skill',
			'skill_child_confirmation_id' => 'Skill Child Confirmation',
			'skill_child_reschedule_id' => 'Skill Child ReSchedule',
			'customer_id' => 'Customer',
			'agent_account_id' => 'Agent Account',
			'lead_timezone' => 'Lead Timezone',
			'lead_language' => 'Lead Language',
			'status' => 'Status',
			'type' => 'Type',
			'priority' => 'Priority',
			'callback_date' => 'Callback Date',
			'appointment_date' => 'Appointment Date',
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
		$criteria->compare('list_id',$this->list_id);
		$criteria->compare('skill_id',$this->skill_id);
		$criteria->compare('customer_id',$this->customer_id);
		$criteria->compare('agent_account_id',$this->agent_account_id);
		$criteria->compare('lead_timezone',$this->lead_timezone,true);
		$criteria->compare('lead_language',$this->lead_language,true);
		$criteria->compare('status',$this->status,true);
		$criteria->compare('type',$this->type);
		$criteria->compare('priority',$this->priority);
		$criteria->compare('callback_date',$this->callback_date,true);
		$criteria->compare('appointment_date',$this->appointment_date,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return LeadHopper the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function getType()
	{
		switch( $this->type )
		{
			default: case 1: $type = 'Contact';
			case 2: $type = 'Callback';
			case 3: $type = 'Confirm';
			case 4: $type = 'Lead Search';
			case 5: $type = 'Conflict';
			case 6: $type = 'Reschedule';
			case 7: $type = 'No Show Reschedule';
		}
		
		return $type;
	}
}
