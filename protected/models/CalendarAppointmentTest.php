<?php

/**
 * This is the model class for table "{{calendar_appointment}}".
 *
 * The followings are the available columns in table '{{calendar_appointment}}':
 * @property integer $id
 * @property integer $calendar_id
 * @property integer $account_id
 * @property integer $lead_id
 * @property string $agent_name
 * @property string $title
 * @property string $details
 * @property integer $location
 * @property string $customer_notes
 * @property string $agent_notes
 * @property string $start_date
 * @property string $start_date_year
 * @property string $start_date_month
 * @property string $start_date_day
 * @property string $start_date_time
 * @property string $end_date
 * @property string $end_date_year
 * @property string $end_date_month
 * @property string $end_date_day
 * @property string $end_date_time
 * @property integer $all_day
 * @property integer $is_custom
 * @property integer $type
 * @property integer $status
 * @property string $date_created
 * @property string $date_updated
 */
class CalendarAppointmentTest extends CActiveRecord
{
	const STATUS_APPROVED = 1;
	const STATUS_PENDING = 2;
	const STATUS_DECLINED = 3;
	const STATUS_DELETED = 4;
	const STATUS_SUGGEST = 5;
	
	
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{calendar_appointment_test}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('calendar_id, account_id, lead_id, location, all_day, is_custom, type, status', 'numerical', 'integerOnly'=>true),
			array('agent_name, title, start_date_year, start_date_month, start_date_day, start_date_time, end_date_year, end_date_month, end_date_day, end_date_time', 'length', 'max'=>255),
			array('details, customer_notes, agent_notes, start_date, end_date, date_created, date_updated', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, calendar_id, account_id, lead_id, agent_name, title, details, location, customer_notes, agent_notes, start_date, start_date_year, start_date_month, start_date_day, start_date_time, end_date, end_date_year, end_date_month, end_date_day, end_date_time, all_day, is_custom, type, status, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'calendar' => array(self::BELONGS_TO, 'Calendar', 'calendar_id'),
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
			'calendar_id' => 'Calendar',
			'account_id' => 'Account',
			'lead_id' => 'Lead',
			'agent_name' => 'Agent Name',
			'title' => 'Title',
			'details' => 'Details',
			'location' => 'Location',
			'customer_notes' => 'Customer Notes',
			'agent_notes' => 'Agent Notes',
			'start_date' => 'Start Date',
			'start_date_year' => 'Start Date Year',
			'start_date_month' => 'Start Date Month',
			'start_date_day' => 'Start Date Day',
			'start_date_time' => 'Start Date Time',
			'end_date' => 'End Date',
			'end_date_year' => 'End Date Year',
			'end_date_month' => 'End Date Month',
			'end_date_day' => 'End Date Day',
			'end_date_time' => 'End Date Time',
			'all_day' => 'All Day',
			'is_custom' => 'Is Custom',
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
		$criteria->compare('calendar_id',$this->calendar_id);
		$criteria->compare('account_id',$this->account_id);
		$criteria->compare('lead_id',$this->lead_id);
		$criteria->compare('agent_name',$this->agent_name,true);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('details',$this->details,true);
		$criteria->compare('location',$this->location);
		$criteria->compare('customer_notes',$this->customer_notes,true);
		$criteria->compare('agent_notes',$this->agent_notes,true);
		$criteria->compare('start_date',$this->start_date,true);
		$criteria->compare('start_date_year',$this->start_date_year,true);
		$criteria->compare('start_date_month',$this->start_date_month,true);
		$criteria->compare('start_date_day',$this->start_date_day,true);
		$criteria->compare('start_date_time',$this->start_date_time,true);
		$criteria->compare('end_date',$this->end_date,true);
		$criteria->compare('end_date_year',$this->end_date_year,true);
		$criteria->compare('end_date_month',$this->end_date_month,true);
		$criteria->compare('end_date_day',$this->end_date_day,true);
		$criteria->compare('end_date_time',$this->end_date_time,true);
		$criteria->compare('all_day',$this->all_day);
		$criteria->compare('is_custom',$this->is_custom);
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
	 * @return CalendarAppointment the static model class
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

		
	public function scopes()
	{
		return array(
			'nonPastDates' => array(
				'condition' => 'DATE(start_date) >= DATE(NOW())',
			),
			'holidays' => array(
				// 'condition' => 'title NOT IN("AVAILABLE", "CONFIRMED APPOINTMENT", "APPOINTMENT SET", "LOCATION CONFLICT", "SCHEDULE CONFLICT", "RESCHEDULE APPOINTMENT", "NO SHOW RESCHEDULE", "BLACKOUT DAYS")',
				'condition' => 'is_custom=2',
			),
		);
	}		
		
	public function getEventColor()
	{							
		switch( strtoupper($this->title) )
		{
			default: case 'AVAILABLE': $color = '#6FB3E0'; break;
			case 'CONFIRMED APPOINTMENT': $color = '#87B87F'; break;
			case 'APPOINTMENT SET': $color = '#FFB752'; break;
			case 'CHANGE APPOINTMENT': $color = '#FFB752'; break;
			case 'INSERT APPOINTMENT': $color = '#FFB752'; break;
			case 'LOCATION CONFLICT': $color = '#D15B47'; break;
			case 'SCHEDULE CONFLICT': $color = '#D15B47'; break;
			case 'RESCHEDULE APPOINTMENT': $color = '#D15B47'; break;
			case 'NO SHOW RESCHEDULE': $color = '#D58CDF'; break;	
			case 'CANCEL APPOINTMENT': $color = '#D15B47'; break;	
			case 'BLACKOUT DAYS': $color = '#333333'; break;	
		}
		
		if( !in_array($this->title, array('BLACKOUT DAYS', 'INSERT APPOINTMENT', 'APPOINTMENT SET', 'NO SHOW RESCHEDULE')) && strtotime($this->start_date) < time())
		{
			$color = '#A0A0A0';
		}
		
		return $color;
	}
	
	public function getTitleOptions($viewer)
	{
		if( $viewer == 'agent' )
		{
			$options = array(
				'APPOINTMENT SET' => 'APPOINTMENT SET',
				'LOCATION CONFLICT' => 'LOCATION CONFLICT',
				'SCHEDULE CONFLICT' => 'SCHEDULE CONFLICT',
			);
		}
		else
		{
			$options = array(
				'INSERT APPOINTMENT' => 'INSERT APPOINTMENT',
				'NO SHOW RESCHEDULE' => 'NO SHOW RESCHEDULE',
				'RESCHEDULE APPOINTMENT' => 'RESCHEDULE APPOINTMENT',
				'CANCEL APPOINTMENT' => 'CANCEL APPOINTMENT',
				'RESCHEDULE APPOINTMENT' => 'RESCHEDULE APPOINTMENT',
			);
		}
		
		return $options;
	}
	
	public function getEventTitle()
	{
		$title = $this->title;
		
		if( $this->lead_id != null )
		{
			$title = $this->lead->first_name.' '.$this->lead->last_name;
		}
		
		return $title;
	}
	
	public function getCustomValue()
	{
		$value = $this->is_custom;
		
		if( $this->lead_id != null )
		{
			$value = 3;
		}
		
		if( strtotime($this->start_date) < time() )
		{
			$value = 3;
		}
		
		return $value;
	}
}
