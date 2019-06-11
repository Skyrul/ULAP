<?php

/**
 * This is the model class for table "{{lead_history}}".
 *
 * The followings are the available columns in table '{{lead_history}}':
 * @property integer $id
 * @property integer $lead_id
 * @property integer $agent_account_id
 * @property integer $calendar_appointment_id
 * @property string $lead_phone_number
 * @property string $disposition
 * @property string $disposition_detail
 * @property integer $dial_number
 * @property string $call_date
 * @property string $note
 * @property integer $type
 * @property integer $status
 * @property string $date_created
 * @property string $date_updated
 */
class LeadHistory extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{lead_history}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('lead_call_history_id, lead_id, agent_account_id, calendar_appointment_id, account_id, dial_number, type, status', 'numerical', 'integerOnly'=>true),
			array('lead_phone_number, disposition, disposition_detail', 'length', 'max'=>255),
			array('is_imported', 'numerical', 'integerOnly'=>true),
			array('call_date, note, date_created, date_updated, content, old_data, new_data', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, lead_call_history_id, lead_id, agent_account_id, calendar_appointment_id, account_id, lead_phone_number, disposition, disposition_detail, dial_number, call_date, note, type, status, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'agentAccount' => array(self::BELONGS_TO, 'Account', 'agent_account_id'),
			'calendarAppointment' => array(self::BELONGS_TO, 'CalendarAppointment', 'calendar_appointment_id'),
			'leadCallHistory' => array(self::BELONGS_TO, 'LeadCallHistory', 'lead_call_history_id'),
			'account' => array(self::BELONGS_TO, 'Account', 'account_id'),
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
			'lead_id' => 'Lead',
			'agent_account_id' => 'Agent Account',
			'calendar_appointment_id' => 'Calendar Appointment',
			'lead_phone_number' => 'Lead Phone Number',
			'disposition' => 'Disposition',
			'disposition_detail' => 'Disposition Detail',
			'dial_number' => 'Dial Number',
			'call_date' => 'Call Date',
			'note' => 'Note',
			'type' => 'Type',
			'status' => 'Status',
			'date_created' => 'Date Created',
			'date_updated' => 'Date Updated',
			'content' => 'Content',
			'old_data' => 'Old Data',
			'new_data' => 'New Data',
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
		$criteria->compare('agent_account_id',$this->agent_account_id);
		$criteria->compare('calendar_appointment_id',$this->calendar_appointment_id);
		$criteria->compare('lead_phone_number',$this->lead_phone_number,true);
		$criteria->compare('disposition',$this->disposition,true);
		$criteria->compare('disposition_detail',$this->disposition_detail,true);
		$criteria->compare('dial_number',$this->dial_number);
		$criteria->compare('call_date',$this->call_date,true);
		$criteria->compare('note',$this->note,true);
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
	 * @return LeadHistory the static model class
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
