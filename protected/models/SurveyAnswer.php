<?php

/**
 * This is the model class for table "{{survey_answer}}".
 *
 * The followings are the available columns in table '{{survey_answer}}':
 * @property integer $id
 * @property integer $survey_id
 * @property integer $skill_id
 * @property integer $contract_id
 * @property integer $customer_id
 * @property integer $list_id
 * @property integer $lead_id
 * @property integer $question_id
 * @property string $answer
 * @property string $date_created
 * @property string $date_updated
 */
class SurveyAnswer extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{survey_answer}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('survey_id, skill_id, customer_id, list_id, lead_id, question_id, answer', 'required'),
			array('survey_id, skill_id, contract_id, customer_id, list_id, lead_id, question_id, call_history_id', 'numerical', 'integerOnly'=>true),
			array('answer, answer_key', 'length', 'max'=>255),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, survey_id, skill_id, contract_id, customer_id, list_id, lead_id, question_id, answer, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'survey' => array(self::BELONGS_TO, 'Survey', 'survey_id'),
			'skill' => array(self::BELONGS_TO, 'Skill', 'skill_id'),
			'customer' => array(self::BELONGS_TO, 'Customer', 'customer_id'),
			'list' => array(self::BELONGS_TO, 'Lists', 'list_id'),
			'lead' => array(self::BELONGS_TO, 'Lead', 'lead_id'),
			'leadCallHistory' => array(self::BELONGS_TO, 'LeadCallHistory', 'call_history_id'),
			'surveyQuestion' => array(self::BELONGS_TO, 'SurveyQuestion', 'question_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'survey_id' => 'Survey',
			'skill_id' => 'Skill',
			'contract_id' => 'Contract',
			'customer_id' => 'Customer',
			'list_id' => 'List',
			'lead_id' => 'Lead',
			'call_history_id' => 'Lead Call History',
			'question_id' => 'Question',
			'answer' => 'Answer',
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
		$criteria->compare('survey_id',$this->survey_id);
		$criteria->compare('skill_id',$this->skill_id);
		$criteria->compare('contract_id',$this->contract_id);
		$criteria->compare('customer_id',$this->customer_id);
		$criteria->compare('list_id',$this->list_id);
		$criteria->compare('lead_id',$this->lead_id);
		$criteria->compare('question_id',$this->question_id);
		$criteria->compare('answer',$this->answer,true);
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
	 * @return SurveyAnswer the static model class
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
