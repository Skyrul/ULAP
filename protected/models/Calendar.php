<?php

/**
 * This is the model class for table "{{calendar}}".
 *
 * The followings are the available columns in table '{{calendar}}':
 * @property integer $id
 * @property integer $customer_id
 * @property integer $office_id
 * @property integer $list_id
 * @property string $name
 * @property string $description
 * @property string $appointment_start_time
 * @property string $appointment_end_time
 * @property string $appointment_length
 * @property integer $maximum_appointments_per_day
 * @property integer $maximum_appointments_per_week
 * @property integer $minimum_days_appointment_set
 * @property integer $maximum_days_appointment_set
 * @property integer $location_office
 * @property integer $location_phone
 * @property integer $location_home
 * @property integer $location_skype
 * @property integer $use_default_schedule
 * @property integer $type
 * @property integer $status
 * @property string $date_created
 * @property string $date_updated
 */
class Calendar extends CActiveRecord
{
	const STATUS_ACTIVE = 1;
	const STATUS_INACTIVE = 2;
	const STATUS_DELETED = 3;
	
	
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{calendar}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('customer_id, office_id, list_id, maximum_appointments_per_day, maximum_appointments_per_week, minimum_days_appointment_set, maximum_days_appointment_set, location_office, location_phone, location_home, location_skype, use_default_schedule, type, status', 'numerical', 'integerOnly'=>true),
			array('name, appointment_start_time, appointment_end_time, appointment_length', 'length', 'max'=>255),
			array('description, date_created, date_updated', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, customer_id, office_id, list_id, name, description, appointment_start_time, appointment_end_time, appointment_length, maximum_appointments_per_day, maximum_appointments_per_week, minimum_days_appointment_set, maximum_days_appointment_set, location_office, location_phone, location_home, location_skype, use_default_schedule, type, status, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'office' => array(self::BELONGS_TO, 'CustomerOffice', 'office_id'),
			'customer' => array(self::BELONGS_TO, 'Customer', 'customer_id'),
			'list' => array(self::BELONGS_TO, 'Lists', 'list_id'),
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
			'office_id' => 'Office',
			'list_id' => 'List',
			'name' => 'Name',
			'description' => 'Description',
			'appointment_start_time' => 'Appointment Start Time',
			'appointment_end_time' => 'Appointment End Time',
			'appointment_length' => 'Appointment Length',
			'maximum_appointments_per_day' => 'Maximum Appointments Per Day',
			'maximum_appointments_per_week' => 'Maximum Appointments Per Week',
			'minimum_days_appointment_set' => 'Minimum Days Appointment Set',
			'maximum_days_appointment_set' => 'Maximum Days Appointment Set',
			'location_office' => 'Location Office',
			'location_phone' => 'Location Phone',
			'location_home' => 'Location Home',
			'location_skype' => 'Location Skype',
			'use_default_schedule' => 'Use Default Schedule',
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
		$criteria->compare('office_id',$this->office_id);
		$criteria->compare('list_id',$this->list_id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('appointment_start_time',$this->appointment_start_time,true);
		$criteria->compare('appointment_end_time',$this->appointment_end_time,true);
		$criteria->compare('appointment_length',$this->appointment_length,true);
		$criteria->compare('maximum_appointments_per_day',$this->maximum_appointments_per_day);
		$criteria->compare('maximum_appointments_per_week',$this->maximum_appointments_per_week);
		$criteria->compare('minimum_days_appointment_set',$this->minimum_days_appointment_set);
		$criteria->compare('maximum_days_appointment_set',$this->maximum_days_appointment_set);
		$criteria->compare('location_office',$this->location_office);
		$criteria->compare('location_phone',$this->location_phone);
		$criteria->compare('location_home',$this->location_home);
		$criteria->compare('location_skype',$this->location_skype);
		$criteria->compare('use_default_schedule',$this->use_default_schedule);
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
	 * @return Calendar the static model class
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
			'active' => array(
				'condition' => 'status = :status',
				'params' => array(
					':status' => self::STATUS_ACTIVE,
				),
			),
			'nonDeleted' => array(
				'condition' => 'status != :status',
				'params' => array(
					':status' => self::STATUS_DELETED,
				),
			)
		);
	}
	
	
	public static function createTimeRange($start, $end, $by='30 mins') 
	{
		$start_time = strtotime($start);
		$end_time   = strtotime($end);

		$current    = time();
		$add_time   = strtotime('+'.$by, $current);
		$diff       = $add_time-$current;

		$times = array();
		
		while ($start_time <= $end_time) {
			$times[] = $start_time;
			$start_time += $diff;
		}
	
		return $times;
	}
	
	
	public function locationOptions($type='', $appointment=null)
	{
		$locationOptions = array();	
		
		if( $type == 'all' )
		{
			$locationOptions = array(
				1 => 'Office',
				2 => 'Home',
				3 => 'Phone',
				4 => 'Skype',
			);
		}
		else
		{
			if( $this->location_office == 1 )
			{
				$locationOptions[1] = 'Office';
			}
			
			if( $this->location_home == 1 )
			{
				$locationOptions[2] = 'Home';
			}
			
			if( $this->location_phone == 1 )
			{
				$locationOptions[3] = 'Phone';
			}
			
			if( $this->location_skype == 1 )
			{
				$locationOptions[4] = 'Skype';
			}
		}

		if( $appointment !=null && !empty($appointment->location) && !array_key_exists($appointment->location, $locationOptions) )
		{
			switch( $appointment->location )
			{
				default: case 1: $locationLabel = 'Office'; break;
				case 2: $locationLabel = 'Home'; break;
				case 3: $locationLabel = 'Phone'; break;
				case 4: $locationLabel = 'Skype'; break;
			}
			
			$locationOptions[$appointment->location] = $locationLabel;
		}
		
		return $locationOptions;
	}
	
	public function locationOptionsLabel($locationKey)
	{
		$locationOptions = $this->locationOptions($type='all');
		
		if(isset($locationOptions[$locationKey]))
			return $locationOptions[$locationKey];
		else	
			return null;
	}
	
	public function timeOptions()
	{
		$existingTime = array();
		
		if( isset($_POST['calendar_id']) && isset($_POST['current_date']) )
		{
			$calendarAppointments = CalendarAppointment::model()->findAll(array(
				'condition' => 'calendar_id = :calendar_id AND DATE(start_date) = :start_date AND status NOT IN (3,4)',
				'params' => array(
					':calendar_id' => $_POST['calendar_id'],
					':start_date' => date('Y-m-d', strtotime($_POST['current_date']))
				),
			));
			
			if( $calendarAppointments )
			{
				foreach( $calendarAppointments as $calendarAppointment )
				{
					$existingTime[] = $calendarAppointment->start_date_time;
				}
			}
		}

		$timeOptions = array();
		
		if( $this->use_default_schedule == 1 )
		{
			$times = Calendar::createTimeRange($this->appointment_start_time, $this->appointment_end_time, '1 Hour');
		}
		else
		{
			$times = Calendar::createTimeRange($this->appointment_start_time, $this->appointment_end_time, $this->appointment_length);
		}
			
			
		if( $times )
		{
			foreach ($times as $time) 
			{
				if( !in_array(date('H:i:s', $time), $existingTime) )
				{
					$timeOptions[date('H:i:s', $time)] = date('g:i A', $time);
				}
			}
		}
		
		return $timeOptions;
	}
	
	
	public static function items($customer_id = null, $office_id = null)
	{
		$items = array();
		
		if( $customer_id != null )
		{
			
			$criteria = new CDbCriteria;
			$criteria->compare('customer_id', $customer_id);
			
			if(!empty($office_id))
				$criteria->compare('office_id', $office_id);
			
			$models = self::model()->active()->findAll($criteria);
		}
		else
		{
			$models = self::model()->active()->findAll();
		}
		
		foreach($models as $model)
		{
			if( isset($model->office) )
			{
				$items[$model->id] = $model->office->office_name.' - '.$model->name;
			}
			else
			{
				$items[$model->id] = $model->name;
			}
		}
		
		return $items;
	}
}
