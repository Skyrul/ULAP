<?php

/**
 * This is the model class for table "{{customer_list_import_settings}}".
 *
 * The followings are the available columns in table '{{customer_list_import_settings}}':
 * @property integer $id
 * @property integer $customer_id
 * @property integer $skill_id
 * @property integer $calendar_id
 * @property integer $lead_ordering
 * @property integer $language
 * @property integer $duplicate_action
 * @property integer $manually_enter
 * @property integer $import_from_leads_waiting
 * @property string $date_created
 * @property string $date_updated
 */
class CustomerListImportSettings extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{customer_list_import_settings}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('date_created, date_updated', 'required'),
			array('customer_id, skill_id, calendar_id, lead_ordering, language, duplicate_action, manually_enter, import_from_leads_waiting', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, customer_id, skill_id, calendar_id, lead_ordering, language, duplicate_action, manually_enter, import_from_leads_waiting, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'customer_id' => 'Customer',
			'skill_id' => 'Skill',
			'calendar_id' => 'Calendar',
			'lead_ordering' => 'Lead Ordering',
			'language' => 'Language',
			'duplicate_action' => 'Duplicate Action',
			'manually_enter' => 'Manually Enter',
			'import_from_leads_waiting' => 'Import From Leads Waiting',
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
		$criteria->compare('skill_id',$this->skill_id);
		$criteria->compare('calendar_id',$this->calendar_id);
		$criteria->compare('lead_ordering',$this->lead_ordering);
		$criteria->compare('language',$this->language);
		$criteria->compare('duplicate_action',$this->duplicate_action);
		$criteria->compare('manually_enter',$this->manually_enter);
		$criteria->compare('import_from_leads_waiting',$this->import_from_leads_waiting);
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
	 * @return CustomerListImportSettings the static model class
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
}
