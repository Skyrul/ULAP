<?php

/**
 * This is the model class for table "{{survey}}".
 *
 * The followings are the available columns in table '{{survey}}':
 * @property integer $id
 * @property string $survey_name
 * @property string $description
 * @property integer $status
 * @property integer $is_deleted
 * @property string $date_created
 * @property string $date_updated
 */
class Survey extends CActiveRecord
{
	const STATUS_ACTIVE = 1;
	const STATUS_INACTIVE = 2;
	
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{survey}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('survey_name, description, status', 'required'),
			array('status, is_deleted', 'numerical', 'integerOnly'=>true),
			array('survey_name', 'length', 'max'=>250),
			array('description', 'length', 'max'=>255),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, survey_name, description, status, is_deleted, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'surveySkill' => array(self::HAS_ONE, 'SurveySkill', 'survey_id'),
			'surveySkills' => array(self::HAS_MANY, 'SurveySkill', 'survey_id'),
			'surveyCustomers' => array(self::HAS_MANY, 'SurveyCustomer', 'survey_id'),
			'surveyQuestions' => array(self::HAS_MANY, 'SurveyQuestion', 'survey_id'),
			'surveyQuestionsParentOnly' => array(self::HAS_MANY, 'SurveyQuestion', 'survey_id', 'condition'=>'is_child_of_id = 0 OR is_child_of_id IS NULL','order'=>'question_order ASC'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'survey_name' => 'Survey Name',
			'description' => 'Description',
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
		$criteria->compare('survey_name',$this->survey_name,true);
		$criteria->compare('description',$this->description,true);
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
	 * @return Survey the static model class
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

	public function scopes()
	{
		return array(
			'active' => array(
				'condition' => 'status = :status',
				'params' => array(
					':status' => self::STATUS_ACTIVE,
				),
			),
		);
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

	public static function items($skill_id = null, $customer_id = null)
	{
		
		
		$skill = Skill::model()->findByPk($skill_id);
		if($skill->enable_survey_tab != 1)
		{
			return array();
		}
		
		$items = array();
		$models = array();
		
		if( $skill_id != null && $customer_id != null)
		{
			$criteria = new CDbCriteria;
			 
			 $criteria->with = array('surveySkills');
			 $criteria->compare('surveySkills.skill_id', $skill_id);
			 $criteria->compare('surveySkills.is_active', 1);
			 
			 
			$models = self::model()->active()->findAll($criteria);
		}
		else if( $customer_id != null )
		{
			$criteria = new CDbCriteria;
			 
			 $criteria->with = array('surveyCustomers');
			 $criteria->compare('surveyCustomers.customer_id', $customer_id);
			 $criteria->compare('surveyCustomers.is_active', 1);
			 
			 
			$models = self::model()->active()->findAll($criteria);
		}
		else
		{
			$models = self::model()->active()->findAll();
		}
		
		foreach($models as $model)
		{
			$items[$model->id] = $model->survey_name;
		}
		
		return $items;
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
}
