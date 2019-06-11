<?php

/**
 * This is the model class for table "{{customer_skill}}".
 *
 * The followings are the available columns in table '{{customer_skill}}':
 * @property integer $id
 * @property integer $customer_id
 * @property integer $skill_id
 * @property integer $status
 * @property string $date_created
 * @property string $date_updated
 */
class CustomerSkill extends CActiveRecord
{
	const STATUS_ACTIVE = 1;
	const STATUS_INACTIVE = 2;
	
	const CUSTOMER_CHOICE_PHONE = 1;
	const CUSTOMER_CHOICE_AREA_PREFIX_CNAM = 2;
	
	public $fileUpload;
	
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{customer_skill}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('customer_id, skill_id, contract_id', 'required'),
			array('customer_id, skill_id, contract_id, status, is_custom_call_schedule, skill_caller_option_customer_choice, is_contract_hold, is_hold_for_billing, promo_id, enable_goal_disposition', 'numerical', 'integerOnly'=>true),
			array('is_contract_hold_start_date, is_contract_hold_end_date, start_month, end_month', 'length', 'max'=>255),
			array('fileUpload', 'file', 'types'=>'pdf', 'allowEmpty'=>true),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, customer_id, skill_id, status, date_created, date_updated, enable_goal_disposition', 'safe', 'on'=>'search'),
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
			'skill' => array(self::BELONGS_TO, 'Skill', 'skill_id'),
			'contract' => array(self::BELONGS_TO, 'Contract', 'contract_id'),
			'customerSkillSchedules' => array(self::HAS_MANY, 'CustomerSkillSchedule', 'customer_skill_id'),
			'customerSkillLevels' => array(self::HAS_MANY, 'CustomerSkillLevel', 'customer_skill_id'),
			'customerSkillSubsidys' => array(self::HAS_MANY, 'CustomerSkillSubsidy', 'customer_skill_id'),
			'customerSkillSubsidyLevels' => array(self::HAS_MANY, 'CustomerSkillSubsidyLevel', 'customer_skill_id'),
			'customer' => array(self::BELONGS_TO, 'Customer', 'customer_id'),
			'scriptFileupload' => array(self::BELONGS_TO, 'Fileupload', 'script_tab_fileupload_id'),
			'promo' => array(self::BELONGS_TO, 'Promo', 'promo_id'),
			'customerSkillEmailTemplates' => array(self::HAS_MANY, 'CustomerSkillEmailTemplate', 'customer_skill_id'),
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
			'skill_id' => 'Skill',
			'contract_id' => 'Contract',
			'status' => 'Status',
			'date_created' => 'Date Created',
			'date_updated' => 'Date Updated',
			'is_custom_call_schedule' => 'Is Custom Call Schedule',
			'skill_caller_option_customer_choice' => 'Skill Caller Option Customer Choice', // this attribute will only be used, if the skill's caller_option was set to Customer Choice
			'is_contract_hold' => 'Contract Hold',
			'is_contract_hold_start_date' => 'Start date', 
			'is_contract_hold_end_date' => 'End date', 
			'start_month' => 'Start Date',
			'end_month' => 'End Date',
			'promo_id' => 'Promo',
			
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
		$criteria->compare('status',$this->status);
		$criteria->compare('date_created',$this->date_created,true);
		$criteria->compare('date_updated',$this->date_updated,true);
		$criteria->compare('is_custom_call_schedule',$this->is_custom_call_schedule,true);
		$criteria->compare('is_contract_hold',$this->is_contract_hold,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CustomerSkill the static model class
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
		
		
		if(!empty($this->is_contract_hold_start_date))
			$this->is_contract_hold_start_date = date("Y-m-d",strtotime($this->is_contract_hold_start_date) );
		
		if(!empty($this->is_contract_hold_end_date))
			$this->is_contract_hold_end_date = date("Y-m-d",strtotime($this->is_contract_hold_end_date) );
		
		if(!empty($this->start_month))
			$this->start_month = date("Y-m-d",strtotime($this->start_month) );
		
		if(!empty($this->end_month))
			$this->end_month = date("Y-m-d",strtotime($this->end_month) );
		
		return parent::beforeSave();
	}
	
	public function afterFind()
	{
		$this->customerSkillSchedulesArray = $this->getCustomerSkillSchedulesArray();
		return parent::afterFind();
	}
	
	public static function items($customer_id = null)
	{
		$items = array();
		
		if( $customer_id != null )
		{
			$models = self::model()->findAll(array(
				'condition' => 'customer_id = :customer_id',
				'params' => array(
					':customer_id' => $customer_id,
				),
			));
		}
		else
		{
			$models = self::model()->findAll();
		}
		
		foreach($models as $model)
		{
			if( $model->skill->status == 1 )
			{
				$items[$model->skill_id] = $model->skill->skill_name;
			}
		}
		
		return $items;
	}

	
	public static function listCustomerChoiceOption()
	{
		if( Yii::app()->user->account->account_type_id == Account::TYPE_GAMING_PROJECT_MANAGER )
		{
			$options = array(
				self::CUSTOMER_CHOICE_PHONE => 'Dial As Host Cell Phone',
				self::CUSTOMER_CHOICE_AREA_PREFIX_CNAM => 'Dial As Property Phone Number',
			);
		}
		else
		{
			$options = array(
				self::CUSTOMER_CHOICE_PHONE => 'Dial As Office Phone number',
				self::CUSTOMER_CHOICE_AREA_PREFIX_CNAM => 'Dial As Office Area Code & Company Name',
			);
		}		
		
		return $options;
	}
	
	public $customerChoiceLabel = null;
	public function getCustomerChoiceLabel()
	{
		if($this->customerChoiceLabel === null)
		{
			$customerChoiceOption = self::listCustomerChoiceOption();
			
			if(isset($customerChoiceOption[$this->skill_caller_option_customer_choice]))
			{
				$this->customerChoiceLabel = $customerChoiceOption[$this->skill_caller_option_customer_choice];
			}
		}
		
		return $this->customerChoiceLabel;
	}
	
	public $customerSkillSchedulesArray = array();
	
	public function getCustomerSkillSchedulesArray()
	{
		if(empty($this->customerSkillSchedulesArray))
		{
			foreach($this->customerSkillSchedules as $customerSkillSchedule)
			{
				$this->customerSkillSchedulesArray[$customerSkillSchedule->schedule_day][$customerSkillSchedule->id]['schedule_start'] = $customerSkillSchedule->schedule_start;
				$this->customerSkillSchedulesArray[$customerSkillSchedule->schedule_day][$customerSkillSchedule->id]['schedule_end'] = $customerSkillSchedule->schedule_end;
				$this->customerSkillSchedulesArray[$customerSkillSchedule->schedule_day][$customerSkillSchedule->id]['status'] = $customerSkillSchedule->status;
				$this->customerSkillSchedulesArray[$customerSkillSchedule->schedule_day][$customerSkillSchedule->id]['id'] = $customerSkillSchedule->id;
			}
		}
		
		return $this->customerSkillSchedulesArray;
	}
	
	public $customerSkillLevelArray;
	
	public function getCustomerSkillLevelArray()
	{
		if($this->customerSkillLevelArray === null)
		{
			$customerSkillLevels = array();
			foreach($this->customerSkillLevels as $customerSkillLevel)
			{
				$customerSkillLevels[$customerSkillLevel->contract_subsidy_level_group_id] = $customerSkillLevel;
			}
			
			$this->customerSkillLevelArray = $customerSkillLevels;
		}
		
		return $this->customerSkillLevelArray;
	}
	
	
	public $customerSkillSubsidyArray;
	
	public function getCustomerSkillSubsidyArray()
	{
		if($this->customerSkillSubsidyArray === null)
		{
			$customerSkillSubsidys = array();
			foreach($this->customerSkillSubsidys as $customerSkillSubsidy)
			{
				$customerSkillSubsidys[$customerSkillSubsidy->subsidy_id] = $customerSkillSubsidy;
			}
			
			$this->customerSkillSubsidyArray = $customerSkillSubsidys;
		}
		
		return $this->customerSkillSubsidyArray;
	}
	
	public $customerSkillSubsidyLevelArray;
	
	public function getCustomerSkillSubsidyLevelArray()
	{
		if($this->customerSkillSubsidyLevelArray === null)
		{
			$customerSkillSubsidyLevels = array();
			foreach($this->customerSkillSubsidyLevels as $customerSkillSubsidyLevel)
			{
				$customerSkillSubsidyLevels[$customerSkillSubsidyLevel->subsidy_level_id.'-'.$customerSkillSubsidyLevel->type] = $customerSkillSubsidyLevel;
			}
			
			$this->customerSkillSubsidyLevelArray = $customerSkillSubsidyLevels;
		}
		
		return $this->customerSkillSubsidyLevelArray;
	}

	
	public static function getCustomerContracts($customer_id)
	{
		$items = array();
		
		$models = self::model()->findAll(array(
			'with' => 'contract',
			'select' => 'contract_id',
			'condition' => 'customer_id = :customer_id AND contract.id IS NOT NULL',
			'params' => array(
				':customer_id' => $customer_id,
			),
		));
		
		if( $models )
		{
			foreach( $models as $model )
			{
				$items[$model->contract_id] = $model->contract->contract_name;
			}
		}
		
		return $items;
	}

	public function removeFromSalesReports()
	{
		$sql = '
			AND t.customer_id NOT IN(
				48, 1337, 32, 56, 49, 37, 46, 63, 23, 1966, 2011, 62, 2095, 804, 2129, 2007, 
				2317, 2374, 2363, 2387, 2362, 2527, 2607, 2646, 2678, 2683, 2689, 2698, 2751, 
				2820, 2821, 2822, 2828, 2830, 2876, 2889, 2892, 2893, 2894, 2903, 2904, 2905, 
				2910, 2915, 2916, 2917, 2918, 2921, 2941, 2954, 2955, 2986
			)
		';
		
		return $sql;
	}
}
