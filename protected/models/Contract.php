<?php

/**
 * This is the model class for table "{{contract}}".
 *
 * The followings are the available columns in table '{{contract}}':
 * @property integer $id
 * @property integer $company_id
 * @property integer $skill_id
 * @property string $contract_name
 * @property string $description
 * @property string $billing_calculation
 * @property string $fulfillment_type
 * @property integer $is_subsidy
 * @property string $subsidy_name
 * @property string $subsidy_expiration
 * @property integer $is_fee_start_activate
 * @property double $start_fee_amount
 * @property integer $start_fee_day
 * @property integer $start_fee_billing_cycle
 * @property integer $status
 * @property integer $is_deleted
 * @property string $date_created
 * @property string $date_updated
 */
class Contract extends CActiveRecord
{
	const STATUS_ACTIVE = 1;
	const STATUS_INACTIVE = 2;
	
	const TYPE_FULFILLMENT_GOAL_VOLUME = 1;
	const TYPE_FULFILLMENT_LEAD_VOLUME = 2;
	
	const REFERENCE_SUBSIDY_TYPE_COMPANY = 1;
	const REFERENCE_SUBSIDY_TYPE_TIER = 2;
	
	public $reference_subsidy_id_reference_id;
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{contract}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('company_id, skill_id, contract_name', 'required'),
			// array('subsidy_expiration', 'type', 'type' => 'date', 'message' => '{attribute}: must be a valid date.', 'dateFormat' => 'yyyy-MM-dd'),
			array('company_id, skill_id, is_subsidy, is_fee_start_activate, start_fee_day, start_fee_billing_cycle, status, reference_subsidy_id, reference_subsidy_type, is_deleted', 'numerical', 'integerOnly'=>true),
			array('start_fee_amount', 'numerical'),
			array('contract_name', 'length', 'max'=>128),
			array('description', 'length', 'max'=>250),
			array('billing_calculation, fulfillment_type', 'length', 'max'=>60),
			array('subsidyLevelArray', 'subsidyLevelValidate'),
			array('reference_subsidy_id_reference_id, billing_date', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, company_id, skill_id, contract_name, description, billing_calculation, fulfillment_type, is_subsidy, is_fee_start_activate, start_fee_amount, start_fee_day, start_fee_billing_cycle, status, is_deleted, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'company' => array(self::BELONGS_TO, 'Company', 'company_id'),
			'skill' => array(self::BELONGS_TO, 'Skill', 'skill_id'),
			'companySubsidies' => array(self::HAS_MANY, 'CompanySubsidy', 'contract_id'),
			'tierSubsidies' => array(self::HAS_MANY, 'TierSubsidy', 'contract_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'company_id' => 'Company',
			'skill_id' => 'Skill',
			'contract_name' => 'Contract Name',
			'description' => 'Description',
			'billing_calculation' => 'Billing Calculation',
			'fulfillment_type' => 'Fulfillment Type',
			'is_subsidy' => 'Contract has Subsidy',
			'is_fee_start_activate' => 'Start Fee',
			'start_fee_amount' => 'Start Fee Amount',
			'start_fee_day' => 'Bill Start Fee How Many Days After Signup',
			'start_fee_billing_cycle' => 'Number of Successful Billing Cycles to Remove Start Fee',
			'status' => 'Status',
			'is_deleted' => 'Is Deleted',
			'date_created' => 'Date Created',
			'date_updated' => 'Date Updated',
			'reference_subsidy_id_reference_id' => 'Subsidy',
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
		$criteria->compare('company_id',$this->company_id);
		$criteria->compare('skill_id',$this->skill_id);
		$criteria->compare('contract_name',$this->contract_name,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('billing_calculation',$this->billing_calculation,true);
		$criteria->compare('fulfillment_type',$this->fulfillment_type,true);
		$criteria->compare('is_subsidy',$this->is_subsidy);
		$criteria->compare('is_fee_start_activate',$this->is_fee_start_activate);
		$criteria->compare('start_fee_amount',$this->start_fee_amount);
		$criteria->compare('start_fee_day',$this->start_fee_day);
		$criteria->compare('start_fee_billing_cycle',$this->start_fee_billing_cycle);
		$criteria->compare('status',$this->status);
		$criteria->compare('is_deleted',$this->is_deleted);
		$criteria->compare('date_created',$this->date_created,true);
		$criteria->compare('date_updated',$this->date_updated,true);

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
	 * @return Contract the static model class
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
		
		// print_r($this->reference_subsidy_id_reference_id);
		if(!empty($this->reference_subsidy_id_reference_id))
		{
			$subsidy = explode('-',$this->reference_subsidy_id_reference_id);
			// print_r($subsidy); exit;
			if(isset($subsidy[0]))
				$this->reference_subsidy_id = $subsidy[0];
		
			if(isset($subsidy[1]))
				$this->reference_subsidy_type = $subsidy[1];
		}
		
		return parent::beforeSave();
	}
	
	public function afterFind()
	{
		// if($this->subsidy_expiration == '0000-00-00' || empty($this->subsidy_expiration))
		// {
			// $this->subsidy_expiration = null;
		// }
		
		$this->subsidyLevelArray = $this->getSubsidyLevelArray();
		
		$this->reference_subsidy_id_reference_id = $this->reference_subsidy_id.'-'.$this->reference_subsidy_type;
		
		return parent::afterFind();
	}
	
	public function subsidyLevelValidate($attribute, $params)
	{
		return true;
	}
	
	public static function listTypeFulfillment()
	{
		return array(
			self::TYPE_FULFILLMENT_GOAL_VOLUME => 'GOAL VOLUME',
			self::TYPE_FULFILLMENT_LEAD_VOLUME => 'LEAD VOLUME',
		);
	}
	
	public static function listStartFreeDay()
	{
		return array(
			30 => '30',
			60 => '60',
			90 => '90',
			100 => '100',
			120 => '120',
		);
	}
	
	public static function listStartFeeBillingCycle()
	{
		$billingArray = array();
		
		for($billingCycle = 1; $billingCycle <= 45; $billingCycle++)
		{
			$billingArray[$billingCycle] = $billingCycle;
		}
		
		return $billingArray;
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
	
	public $subsidyLevelArray = array(
		self::TYPE_FULFILLMENT_GOAL_VOLUME => array(),
		self::TYPE_FULFILLMENT_LEAD_VOLUME => array(),
	);
	
	public function getSubsidyLevelArray()
	{
		if(empty($this->subsidyLevelArray[self::TYPE_FULFILLMENT_GOAL_VOLUME]) || empty($this->subsidyLevelArray[self::TYPE_FULFILLMENT_LEAD_VOLUME]) )
		{
			
			if(!empty($this->id))
			{
				$criteria = new CDbCriteria;
				$criteria->compare('contract_id', $this->id);
				$csls = ContractSubsidyLevel::model()->findAll($criteria);
				
				if(!empty($csls))
				{
					foreach($csls as $csl)
					{
						$this->subsidyLevelArray[$csl->type][$csl->group_id]['group_id'] = $csl->group_id;
						// $this->subsidyLevelArray[$csl->type][$csl->group_id]['id'] = $csl->id;
						$this->subsidyLevelArray[$csl->type][$csl->group_id][$csl->column_name] = $csl->column_value;
					}
				}
				else
				{
					
				}
			}
		}
		
		return $this->subsidyLevelArray;
	}

	public static function subsidyList($company_id = null)
	{
		$criteria = new CDbCriteria;
		$criteria->compare('company_id', $company_id);
		$criteria->order = 'subsidy_name ASC';
		
		$subsidyList = array();
		
		$companySubsidys = CompanySubsidy::model()->findAll($criteria);
		$tierSubsidys = TierSubsidy::model()->findAll($criteria);
		
		foreach($companySubsidys as $companySubsidy)
		{
			$subsidyList['Company Subsidy'][$companySubsidy->id.'-'.self::REFERENCE_SUBSIDY_TYPE_COMPANY] = $companySubsidy->subsidy_name;
		}
		
		foreach($tierSubsidys as $tierSubsidy)
		{
			$subsidyList['Tier Subsidy'][$tierSubsidy->id.'-'.self::REFERENCE_SUBSIDY_TYPE_TIER] = $tierSubsidy->subsidy_name;
		}
		
		return $subsidyList;
	}		

	public static function quantityList()
	{
		return array(
		 1=> 1,
		 2=> 2,
		 3=> 3,
		 4=> 4,
		 5=> 5,
		 6=> 6,
		 7=> 7,
		 8=> 8,
		 9=> 9,
		);
	}
}


