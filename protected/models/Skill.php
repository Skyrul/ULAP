<?php

/**
 * This is the model class for table "{{skill}}".
 *
 * The followings are the available columns in table '{{skill}}':
 * @property integer $id
 * @property string $skill_name
 * @property string $description
 * @property integer $status
 * @property integer $is_deleted
 * @property string $date_created
 * @property string $date_updated
 */
class Skill extends CActiveRecord
{
	const STATUS_ACTIVE = 1;
	const STATUS_INACTIVE = 2;
	
	const CALLER_OPTION_DIAL_AS = 1;
	const CALLER_OPTION_CUSTOMER_CHOICE = 2;
			
	
	const SEARCH_SETTING_ALL = 1;
	const SEARCH_SETTING_ACTIVE = 2;
	const SEARCH_SETTING_CURRENT = 3;
	const SEARCH_SETTING_VIEW_ONLY = 4;
	const SEARCH_SETTINT_NO_LEAD_SEARCH = 5;
	
	public $_clone_skill_id;
	public $fileUpload;
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{skill}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('skill_name', 'required'),
			array('status, is_deleted, max_dials, max_numbers, caller_option, call_agent_lead_search_setting, has_inbound, max_lead_life_before_recertify, enable_dialer_appointment_tab, enable_dialer_data_tab, enable_list_custom_mapping, enable_list_area_code_assignment, enable_specific_date_calling, enable_dialer_script_tab, enable_survey_tab, enable_email_setting, script_tab_fileupload_id, max_agent_per_customer, workforce_dials, workforce_appointments, customer_popup_delay, use_system_default_list_settings, enable_goal_disposition', 'numerical', 'integerOnly'=>true),
			array('skill_name', 'length', 'max'=>128),
			array('description', 'length', 'max'=>255),
			array('phone_number, cnam', 'length', 'max'=>60),
			array('fileUpload', 'file', 'types'=>'pdf', 'allowEmpty'=>true),

			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, skill_name, description, status, is_deleted, enable_dialer_appointment_tab, enable_dialer_data_tab, enable_list_custom_mapping, enable_list_area_code_assignment, enable_specific_date_calling, enable_dialer_script_tab, enable_survey_tab, enable_email_setting, script_tab_fileupload_id, max_agent_per_customer, date_created, date_updated, enable_goal_disposition', 'safe', 'on'=>'search'),
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
			'skillDispositions' => array(self::HAS_MANY, 'SkillDisposition', 'skill_id'),
			'skillSchedules' => array(self::HAS_MANY, 'SkillSchedule', 'skill_id'),
			'skillChilds' => array(self::HAS_MANY, 'SkillChild', 'skill_id'),
			'skillCompany' => array(self::HAS_MANY, 'SkillCompany', 'skill_id'),
			'skillAccounts' => array(self::HAS_MANY, 'SkillAccount', 'skill_id'),
			'skillServiceTabs' => array(self::HAS_MANY, 'SkillServiceTab', 'skill_id'),
			'skillEmailTemplates' => array(self::HAS_MANY, 'SkillEmailTemplate', 'skill_id'),
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
			'skill_name' => 'Skill Name',
			'description' => 'Description',
			'status' => 'Status',
			'is_deleted' => 'Is Deleted',
			'date_created' => 'Date Created',
			'date_updated' => 'Date Updated',
			'max_dials' => 'Max Dials Per Phone Number',
			'caller_option' => 'Caller ID',
			'call_agent_lead_search_setting' => 'Call Agent Lead Search Setting',
			'has_inbound' => 'Enable Inbound',
			'max_numbers' => 'Max Phone Numbers',
			'max_lead_life_before_recertify' => 'Max Lead Life (Days) Before Recertify',
			'enable_dialer_appointment_tab' => 'Appointment Tab',
			'enable_dialer_data_tab' => 'Data Tab',
			'enable_dialer_script_tab' => 'Script Tab',
			'enable_survey_tab' => 'Survey Tab',
			'enable_email_setting' => 'Email Setting Tab',
			'fileUpload' => 'Script Tab File',
			'enable_list_custom_mapping' => 'List Custom Mapping',
			'enable_list_area_code_assignment' => 'List Area Code Assignment',
			'enable_specific_date_calling' => 'Specific Date Calling',
			'max_agent_per_customer' => 'Agent per Customer',
			'workforce_dials' => 'Dials',
			'workforce_appointments' => '(Disposition) Appointments',
			'customer_popup_delay' => 'Customer Notes Delay (Seconds)',
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
		$criteria->compare('skill_name',$this->skill_name,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('is_deleted',$this->is_deleted);
		$criteria->compare('date_created',$this->date_created,true);
		$criteria->compare('date_updated',$this->date_updated,true);
		// $criteria->compare('company_id',$this->company_id,true);
		$criteria->compare('max_dials',$this->max_dials,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'pagination'=>array(
				'pageSize'=>100,
			),
		));
		
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Skill the static model class
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
	
	public function afterFind()
	{
		$this->skillSchedulesArray = $this->getSkillSchedulesArray();
		$this->callerOptionLabel = $this->getCallerOptionLabel();
		return parent::afterFind();
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

	public static function listCallerOption()
	{
		return array(
			self::CALLER_OPTION_DIAL_AS => 'Dial As',
			self::CALLER_OPTION_CUSTOMER_CHOICE => 'Customer Choice',
		);	
	}
	
	public $callerOptionLabel = null;
	public function getCallerOptionLabel()
	{
		if($this->callerOptionLabel === null)
		{
			$listCallerOption = self::listCallerOption();
			
			if(isset($listCallerOption[$this->caller_option]))
			{
				$this->callerOptionLabel = $listCallerOption[$this->caller_option];
			}
		}
		
		return $this->callerOptionLabel;
	}
	
	public static function listLeadSearchSetting()
	{
		return array(
			self::SEARCH_SETTING_ALL => 'View\Edit All',
			self::SEARCH_SETTING_ACTIVE => 'View\Edit Active Customers',
			self::SEARCH_SETTING_CURRENT => 'View Past\Edit Current Customers',
			self::SEARCH_SETTING_VIEW_ONLY => 'View Only All',
			self::SEARCH_SETTINT_NO_LEAD_SEARCH => 'No Lead Search',
		);	
	}
	
	public $leadSearchSettingLabel = null;
	public function getLeadSearchSettingLabel()
	{
		if($this->leadSearchSettingLabel === null)
		{
			$listLeadSearchSetting = self::listLeadSearchSetting();
			
			if(isset($listLeadSearchSetting[$this->call_agent_lead_search_setting]))
			{
				$this->leadSearchSettingLabel = $listLeadSearchSetting[$this->call_agent_lead_search_setting];
			}
		}
		
		return $this->leadSearchSettingLabel;
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

	public function byEnableSurveyTab()
	{
		$criteria = new CDbCriteria;
		$criteria->compare('enable_survey_tab',1);
		
		$this->getDbCriteria()->mergeWith($criteria);
		return $this;
	}
	
	public $skillSchedulesArray = array();
	
	public function getSkillSchedulesArray()
	{
		if(empty($this->skillSchedulesArray))
		{
			foreach($this->skillSchedules as $skillSchedule)
			{
				$this->skillSchedulesArray[$skillSchedule->schedule_day][$skillSchedule->id]['schedule_start'] = $skillSchedule->schedule_start;
				$this->skillSchedulesArray[$skillSchedule->schedule_day][$skillSchedule->id]['schedule_end'] = $skillSchedule->schedule_end;
				$this->skillSchedulesArray[$skillSchedule->schedule_day][$skillSchedule->id]['status'] = $skillSchedule->status;
				$this->skillSchedulesArray[$skillSchedule->schedule_day][$skillSchedule->id]['id'] = $skillSchedule->id;
			}
		}
		
		return $this->skillSchedulesArray;
	}

	public function byCompanyId($companyId)
	{
		$criteria = new CDbCriteria;
		$criteria->with = array('skillCompany' => array('joinType'=>'INNER JOIN'));
		$criteria->compare('skillCompany.company_id', $companyId);
		
		$this->getDbCriteria()->mergeWith($criteria);
		return $this;
	}

	
	public function byExcludedIds($ids = array())
	{
		if(!empty($ids))
		{
			$criteria = new CDbCriteria;
			$criteria->addNotInCondition('t.id', $ids);
			
			$this->getDbCriteria()->mergeWith($criteria);
		
		}
		
		return $this;
	}

	public function scopes()
	{
		return array(
			'active' => array(
				'condition'=>'status=1',
			), 
		);
	}
	
	public static function emailTemplateList($skillId)
	{
		$criteria = new CDbCriteria;
		$criteria->compare('skill_id', $skillId);
		
		$skillEmailTemplates = SkillEmailTemplate::model()->findAll($criteria);
		
		$list = CHtml::listData($skillEmailTemplates,'id','template_name');
		
		return $list;
	}
}
