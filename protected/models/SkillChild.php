<?php

/**
 * This is the model class for table "{{skill_child}}".
 *
 * The followings are the available columns in table '{{skill_child}}':
 * @property integer $id
 * @property integer $skill_id
 * @property string $child_name
 * @property string $description
 * @property integer $is_language
 * @property string $language
 * @property integer $is_reminder_call
 * @property integer $status
 * @property integer $is_deleted
 * @property string $date_created
 * @property string $date_updated
 */
class SkillChild extends CActiveRecord
{
	public $existingId;
	public $fileUpload;
	
	const STATUS_ACTIVE = 1;
	const STATUS_INACTIVE = 2;
	
	const LANG_ENGLISH = 'ENGLISH';
	const LANG_FRENCH = 'FRENCH';
	const LANG_KOREAN = 'KOREAN';
	const LANG_MANDARIN = 'MANDARIN';
	
	const TYPE_CONFIRM = 1;
	const TYPE_RESCHEDULE = 2;
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{skill_child}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('skill_id, child_name, is_language, is_reminder_call, type, status', 'required'),
			array('existingId', 'required','on'=>'cloneExisting'),
			array('skill_id, is_language, is_reminder_call, status, is_deleted, max_dials, type, existingId, enable_dialer_script_tab, script_tab_fileupload_id', 'numerical', 'integerOnly'=>true),
			array('child_name', 'length', 'max'=>128),
			array('description', 'length', 'max'=>250),
			array('language', 'length', 'max'=>60),
			array('fileUpload', 'file', 'types'=>'pdf', 'allowEmpty'=>true),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, skill_id, child_name, description, is_language, language, is_reminder_call, status, is_deleted, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'skillChildDispositions' => array(self::HAS_MANY, 'SkillChildDisposition', 'skill_child_id'),
			'skillChildSchedules' => array(self::HAS_MANY, 'SkillChildSchedule', 'skill_child_id'),
			'skill' => array(self::BELONGS_TO, 'Skill', 'skill_id'),
			'skillChildAccounts' => array(self::HAS_MANY, 'SkillChildAccount', 'skill_child_id'),
			'scriptFileupload' => array(self::BELONGS_TO, 'Fileupload', 'script_tab_fileupload_id'),
			
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'skill_id' => 'Skill',
			'child_name' => 'Child Name',
			'description' => 'Description',
			'is_language' => 'Special Language',
			'language' => 'Language',
			'is_reminder_call' => 'Reminder Call',
			'status' => 'Status',
			'is_deleted' => 'Is Deleted',
			'date_created' => 'Date Created',
			'date_updated' => 'Date Updated',
			'max_dials' => 'Max Dials',
			'type' => 'Call Type',
			'existingId' => 'Existing Child Skill',
			'enable_dialer_script_tab' => 'Enable Dialer Script Tab',
			'fileUpload' => 'Script Tab File',
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
		$criteria->compare('skill_id',$this->skill_id);
		$criteria->compare('child_name',$this->child_name,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('is_language',$this->is_language);
		$criteria->compare('language',$this->language,true);
		$criteria->compare('is_reminder_call',$this->is_reminder_call);
		$criteria->compare('status',$this->status);
		$criteria->compare('is_deleted',$this->is_deleted);
		$criteria->compare('date_created',$this->date_created,true);
		$criteria->compare('date_updated',$this->date_updated,true);
		$criteria->compare('max_dials',$this->max_dials,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return SkillChild the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function afterFind()
	{
		$this->skillChildSchedulesArray = $this->getSkillChildSchedulesArray();
		return parent::afterFind();
	}
	
	public function beforeSave()
	{
		if($this->isNewRecord)
			$this->date_created = $this->date_updated = date("Y-m-d H:i:s");
		else
			$this->date_updated = date("Y-m-d H:i:s");
		
		return parent::beforeSave();
	}

	public function bySkillId($skill_id)
	{
		$criteria = new CDbCriteria;
		$criteria->compare('skill_id',$skill_id);
		
		$this->getDbCriteria()->mergeWith($criteria);
		return $this;
	}
	
	public static function listLanguage()
	{
		return array(
			self::LANG_ENGLISH => 'English',
			self::LANG_FRENCH => 'French',
			self::LANG_KOREAN => 'Korean',
			self::LANG_MANDARIN => 'Mandarin',
		);
	}
	
	public static function listStatus()
	{
		return array(
			self::STATUS_ACTIVE => 'Active',
			self::STATUS_INACTIVE => 'Inactive',
		);	
	}
	
	public static function listTypes()
	{
		return array(
			self::TYPE_CONFIRM => 'Confirm Call',
			self::TYPE_RESCHEDULE => 'Reschedule Call',
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

	public function byStatus($status)
	{
		$criteria = new CDbCriteria;
		$criteria->compare('status',$status);
		$criteria->compare('is_deleted',0);
		$this->getDbCriteria()->mergeWith($criteria);
		return $this;
	}

	public function byIsDeletedNot()
	{
		$criteria = new CDbCriteria;
		$criteria->compare('is_deleted',0);
		
		$this->getDbCriteria()->mergeWith($criteria);
		return $this;
	}

	public $skillChildSchedulesArray = array();
	
	public function getSkillChildSchedulesArray()
	{
		if(empty($this->skillChildSchedulesArray))
		{
			foreach($this->skillChildSchedules as $skillChildSchedule)
			{
				$this->skillChildSchedulesArray[$skillChildSchedule->schedule_day]['schedule_start'] = $skillChildSchedule->schedule_start;
				$this->skillChildSchedulesArray[$skillChildSchedule->schedule_day]['schedule_end'] = $skillChildSchedule->schedule_end;
				$this->skillChildSchedulesArray[$skillChildSchedule->schedule_day]['status'] = $skillChildSchedule->status;
			}
		}
		
		// echo '<pre>';
		// print_r($this->skillChildSchedulesArray);
		// exit;
		
		return $this->skillChildSchedulesArray;
	}
}
