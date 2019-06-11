<?php

/**
 * This is the model class for table "{{state_schedule}}".
 *
 * The followings are the available columns in table '{{state_schedule}}':
 * @property integer $id
 * @property integer $state_id
 * @property string $schedule_start
 * @property string $schedule_end
 * @property string $schedule_day
 * @property integer $status
 * @property integer $is_deleted
 * @property string $date_created
 * @property string $date_updated
 */
class StateSchedule extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{state_schedule}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('state_id, schedule_start, schedule_end, schedule_day, status, is_deleted, date_created, date_updated', 'required'),
			array('state_id, status, is_deleted', 'numerical', 'integerOnly'=>true),
			array('schedule_start, schedule_end', 'length', 'max'=>8),
			array('schedule_day', 'length', 'max'=>5),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, state_id, schedule_start, schedule_end, schedule_day, status, is_deleted, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'state_id' => 'State',
			'schedule_start' => 'Schedule Start',
			'schedule_end' => 'Schedule End',
			'schedule_day' => 'Schedule Day',
			'status' => 'Status',
			'is_deleted' => 'Is Deleted',
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
		$criteria->compare('state_id',$this->state_id);
		$criteria->compare('schedule_start',$this->schedule_start,true);
		$criteria->compare('schedule_end',$this->schedule_end,true);
		$criteria->compare('schedule_day',$this->schedule_day,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('is_deleted',$this->is_deleted);
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
	 * @return StateSchedule the static model class
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
	
	
	public static $listScheduleTime = null;
	public static function listScheduleTime($startTime = null, $endTime = null)
	{
		
		
		if($startTime == null)
		{
			$startTime = 8;
			$startMinute = '00';
		}
		else
		{
			$startTimeData = explode(":",$startTime);
			$startTime = $startTimeData[0];
			$startMinute = $startTimeData[1];
		}
		
		if($endTime == null)
		{
			$endTime = 21;
			$endMinute = '00';
		}
		else 
		{
			$endTimeData = explode(":",$endTime);
			$endTime = $endTimeData[0];
			$endMinute = $endTimeData[1];
			
		}
		
		if(self::$listScheduleTime === null)
		{
			for($time = $startTime; $time <= $endTime; $time++)
			{
				$value1 = $time.':'.'00'.':'.'00';
				$value2 = $time.':'.'30'.':'.'00';
					
				if($startMinute == '00')
				{
					self::$listScheduleTime[$value1] = date("h:i A",strtotime($value1));
					
					if($time == $endTime && $endMinute == '00')
						self::$listScheduleTime[$value1] = date("h:i A",strtotime($value1));
					else if($time == $endTime && $endMinute == '30')
					{
						self::$listScheduleTime[$value2] = date("h:i A",strtotime($value2));
					}
					else 
					{
						self::$listScheduleTime[$value1] = date("h:i A",strtotime($value1));
						self::$listScheduleTime[$value2] = date("h:i A",strtotime($value2));
					}
				}
				
				if($startMinute == '30')
				{
					self::$listScheduleTime[$value2] = date("h:i A",strtotime($value2));
					
					$startMinute = '00';
				}
			}
		}
		
		return self::$listScheduleTime;
	}

	public static function listStatus()
	{
		return array(
			self::STATUS_ACTIVE => 'Active',
			self::STATUS_INACTIVE => 'Inactive',
		);	
	}
	
	public $statusLabel = null;
	public function getStatusLabel()
	{
		if($this->statusLabel === null)
		{
			$listStatus = self::listStatus();
			
			if(isset($listStatus[$this->status]))
			{
				$this->statusLabel = $listStatus[$this->status];
			}
		}
		
		return $this->statusLabel;
	}
}
