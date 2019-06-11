<?php

/**
 * This is the model class for table "{{company_subsidy}}".
 *
 * The followings are the available columns in table '{{company_subsidy}}':
 * @property integer $id
 * @property integer $company_id
 * @property string $start_date
 * @property string $end_date
 * @property string $subsidy_name
 * @property string $date_created
 * @property string $date_updated
 */
class CompanySubsidy extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{company_subsidy}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('start_date, end_date, subsidy_name, skill_id, contract_id', 'required'),
			// array('start_date, end_date', 'type', 'type' => 'date', 'message' => '{attribute}: must be a valid date.', 'dateFormat' => 'yyyy-MM-dd'),
			array('company_id, skill_id, contract_id, exclude_from_company_file_update', 'numerical', 'integerOnly'=>true),
			array('subsidy_name', 'length', 'max'=>60),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, company_id, start_date, end_date, subsidy_name, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'companySubsidyLevels' => array(self::HAS_MANY, 'CompanySubsidyLevel', 'subsidy_id'),
		
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
			'contract_id' => 'Contract',
			'start_date' => 'Start Date',
			'end_date' => 'End Date',
			'subsidy_name' => 'Subsidy Name',
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
		$criteria->compare('company_id',$this->company_id);
		$criteria->compare('start_date',$this->start_date,true);
		$criteria->compare('end_date',$this->end_date,true);
		$criteria->compare('subsidy_name',$this->subsidy_name,true);
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
	 * @return CompanySubsidy the static model class
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
		
		if(!empty($this->start_date))
			$this->start_date = date("Y-m-d",strtotime($this->start_date) );
		
		if(!empty($this->end_date))
			$this->end_date = date("Y-m-d",strtotime($this->end_date) );
		
		return parent::beforeSave();
	}
	
	
	
}