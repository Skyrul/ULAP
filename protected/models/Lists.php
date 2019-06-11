<?php

/**
 * This is the model class for table "{{lists}}".
 *
 * The followings are the available columns in table '{{lists}}':
 * @property integer $id
 * @property integer $customer_id
 * @property integer $skill_id
 * @property integer $calendar_id
 * @property integer $fileupload_id
 * @property string $name
 * @property string $description
 * @property integer $lead_ordering
 * @property integer $manually_enter
 * @property integer $duplicate_action
 * @property integer $number_of_leads
 * @property integer $type
 * @property integer $status
 * @property string $date_created
 * @property string $date_updated
 */
class Lists extends AuditCActiveRecord
{
	const STATUS_ACTIVE = 1;
	const STATUS_INACTIVE = 2;
	const STATUS_DELETED = 3;
	
	const ORDERING_RANDOM = 1;
	const ORDERING_BY_LASTNAME = 2;
	const ORDERING_BY_CUSTOM_DATE = 3;
	const ORDERING_BY_DIALS = 4;
	const ORDERING_BY_SPECIFIC_DATE = 5;
	
	const DUPLICATES_DO_NOT_IMPORT = 1;
	const DUPLICATES_UPDATE_LEAD_INFO = 2;
	const DUPLICATES_UPDATE_LEAD_INFO_RESET_DIALS = 3;
	const MOVE_LEAD_TO_CURRENT_LIST_RESET_DIALS = 4;
	const CUSTOMER_SERVICE_OVERRIDE = 5;
	const MOVE_RECERTIFIABLE_LEAD_TO_CURRENT_LIST = 6;
	const MOVE_RECYCLABLE_LEAD_TO_CURRENT_LIST = 7; 
	const CUSTOMER_SERVICE_ALLOW_DUPLICATES = 8; 
	
	
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{lists}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('skill_id, calendar_id, name', 'required'),
			array('customer_id, skill_id, calendar_id, survey_id, fileupload_id, lead_ordering, number_of_dials_per_guest, manually_enter, allow_custom_fields, allow_area_code_assignment, duplicate_action, number_of_leads, type, status, is_default_call_schedule, is_host_dial', 'numerical', 'integerOnly'=>true),
			array('name, language', 'length', 'max'=>255),
			array('dialing_as_number, time_zone_assignment', 'length', 'max'=>20),
			array('description, date_created, date_updated, start_date, end_date', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, customer_id, skill_id, calendar_id, fileupload_id, name, description, lead_ordering, manually_enter, allow_custom_fields, allow_area_code_assignment, duplicate_action, number_of_leads, type, status, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'leadCount' => array(self::STAT, 'Lead', 'list_id', 'condition'=>'type=1'),
			'leadCallablesCount' => array(self::STAT, 'Lead', 'list_id', 'condition'=>'type=1 AND status=1'), //AND list.status != 3
			'calendar' => array(self::BELONGS_TO, 'Calendar', 'calendar_id'),
			'fileupload' => array(self::BELONGS_TO, 'Fileupload', 'fileupload_id'),
			'skill' => array(self::BELONGS_TO, 'Skill', 'skill_id'),
			'customer' => array(self::BELONGS_TO, 'Customer', 'customer_id'),
			'survey' => array(self::BELONGS_TO, 'Survey', 'survey_id'),
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
			'skill_id' => ($this->scenario == 'hostDial') ? 'Call Type' : 'Skill Assignment',
			'calendar_id' => 'Calendar Assignment',
			'survey_id' => 'Survey',
			'fileupload_id' => 'Fileupload',
			'name' => 'List Name',
			'description' => 'List Description',
			'lead_ordering' => 'Call Order',
			'manually_enter' => 'Manually Enter',
			'allow_custom_fields' => 'Allow Custom Fields',
			'allow_area_code_assignment' => 'Allow Area Code Assignment',
			'duplicate_action' => 'Action for Duplicates',
			'language' => 'Language',
			'number_of_leads' => 'Number Of Leads',
			'type' => 'Type',
			'status' => 'Status',
			'date_created' => 'Date Created',
			'date_updated' => 'Date Updated',
			
			'dialing_as_number' => 'Dialing as Number',
			'number_of_dials_per_guest' => 'Number of Dials per Guest',
			'start_date' => 'Start Date',
			'end_date' => 'End Date',
			'is_default_call_schedule' => 'Call Schedule',
			'time_zone_assignment' => 'Time Zone Assignment',
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
		$criteria->compare('skill_id',$this->skill_id);
		$criteria->compare('calendar_id',$this->calendar_id);
		$criteria->compare('fileupload_id',$this->fileupload_id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('lead_ordering',$this->lead_ordering);
		$criteria->compare('manually_enter',$this->manually_enter);
		$criteria->compare('duplicate_action',$this->duplicate_action);
		$criteria->compare('language',$this->language);
		$criteria->compare('number_of_leads',$this->number_of_leads);
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
	 * @return Lists the static model class
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
	
	
	public function getOrderingOptions()
	{			
		$items = array(
			self::ORDERING_RANDOM => 'Random',
			self::ORDERING_BY_LASTNAME => 'Alpha by last name',
			self::ORDERING_BY_CUSTOM_DATE => 'Custom Date',
			self::ORDERING_BY_DIALS => 'Complete 1st Dials first',
		);
		
		if( isset($this->skill) && $this->skill->enable_specific_date_calling == 1 )
		{
			$items[self::ORDERING_BY_SPECIFIC_DATE] = 'Specific Date';
		}
		
		return $items;
	}
	
	
	public static function getStatusOptions()
	{			
		return array(
			self::STATUS_ACTIVE => 'ACTIVE',
			self::STATUS_INACTIVE => 'INACTIVE',
		);
	}
	
	public static function allStatuses()
	{
		return array(
			self::STATUS_ACTIVE => 'ACTIVE',
			self::STATUS_INACTIVE => 'INACTIVE',
			self::STATUS_DELETED => 'DELETED',
		);
	}
	
	public static function getLanguageOptions()
	{
		return array(
			'English' => 'English',
			// 'French' => 'French',
			// 'Korean' => 'Korean',
			// 'Mandarin' => 'Mandarin',
			'Spanish' => 'Spanish',
		);
	}
	
	public static function items($customer_id=null)
	{
		$items = array();
		
		if( $customer_id != null )
		{
			$models = self::model()->findAll(array(
				'condition' => 'customer_id = :customer_id AND status!=3',
				'params' => array(
					':customer_id' => $customer_id,
				),
			));
		}
		else
		{
			$models = self::model()->findAll(array(
				'condition' => 'status!=3',
			));
		}
		
		if( $models )
		{
			foreach( $models as $model )
			{
				$items[$model->id] = $model->name;
			}
		}
			
		return $items;
	}

	protected function afterSave()
	{
		if(!$this->isNewRecord)
		{
			##add customer history IF
			#list is marked active or inactive
			#list is deleted/removed
			
			$historyHolder = '';
			
			if($this->oldAttributes['status'] != $this->newAttributes['status'])
			{
				$getStatusOptions = self::allStatuses();
				
				if(isset($getStatusOptions[$this->oldAttributes['status']]))
					$oldStatus = $getStatusOptions[$this->oldAttributes['status']];
				
				if(isset($getStatusOptions[$this->newAttributes['status']]))
					$newStatus = $getStatusOptions[$this->newAttributes['status']];
				
				$historyHolder[] = self::model()->getAttributeLabel('status').' changed from '.$oldStatus.' to '.$newStatus;
			}
			
			if(!empty($historyHolder))
			{
				$historyString = implode(', ',$historyHolder); 
				$history = new CustomerHistory;
				$history->setAttributes(array(
					'content' => $this->name.' | '.$historyString, 
					'model_id' => $this->id, 
					'customer_id' => $this->customer_id,
					'user_account_id' => Yii::app()->user->id,
					'page_name' => 'List',
					'old_data' => json_encode($this->oldAttributes),
					'new_data' => json_encode($this->newAttributes),
					'type' => $history::TYPE_UPDATED,
				));
				
				$history->save(false);
			}
		}
		
		return parent::afterSave();
	}
}
