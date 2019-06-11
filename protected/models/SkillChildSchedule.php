<?php

/**
 * This is the model class for table "{{skill_child_schedule}}".
 *
 * The followings are the available columns in table '{{skill_child_schedule}}':
 * @property integer $id
 * @property integer $skill_child_id
 * @property integer $schedule_start
 * @property integer $schedule_end
 * @property integer $schedule_day
 * @property integer $status
 * @property integer $is_deleted
 * @property string $date_created
 * @property string $date_updated
 */
class SkillChildSchedule extends CActiveRecord
{
	const STATUS_ACTIVE = 1;
	const STATUS_INACTIVE = 2;
	
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{skill_child_schedule}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('skill_child_id, schedule_start, schedule_end, schedule_day, status', 'required'),
			array('skill_child_id, schedule_start, schedule_end, schedule_day, status, is_deleted', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, skill_child_id, schedule_start, schedule_end, schedule_day, status, is_deleted, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'skill_child_id' => 'Skill Child',
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
		$criteria->compare('skill_child_id',$this->skill_child_id);
		$criteria->compare('schedule_start',$this->schedule_start);
		$criteria->compare('schedule_end',$this->schedule_end);
		$criteria->compare('schedule_day',$this->schedule_day);
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
	 * @return SkillSchedule the static model class
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
	public static function listScheduleTime()
	{
		if(self::$listScheduleTime === null)
		{
			for($time = 7; $time <= 19; $time++)
			{
				$value1 = $time.':'.'00'.':'.'00';
				$value2 = $time.':'.'30'.':'.'00';
				
				self::$listScheduleTime[$value1] = date("h:i A",strtotime($value1));
				self::$listScheduleTime[$value2] = date("h:i A",strtotime($value2));
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
