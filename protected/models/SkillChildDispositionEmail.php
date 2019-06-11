<?php

/**
 * This is the model class for table "{{skill_child_disposition_email}}".
 *
 * The followings are the available columns in table '{{skill_child_disposition_email}}':
 * @property integer $id
 * @property integer $skill_child_disposition_id
 * @property string $email_address
 * @property string $email_subject
 * @property string $email_content
 * @property integer $is_goal_disposition
 * @property integer $is_details
 * @property integer $is_callback_date
 * @property integer $is_callback_time
 * @property integer $is_note
 */
class SkillChildDispositionEmail extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{skill_child_disposition_email}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('skill_child_disposition_id, email_address, email_subject, email_content, is_goal_disposition, is_details, is_callback_date, is_callback_time, is_note', 'required'),
			array('skill_child_disposition_id, is_goal_disposition, is_details, is_callback_date, is_callback_time, is_note', 'numerical', 'integerOnly'=>true),
			array('email_address', 'length', 'max'=>128),
			array('email_subject', 'length', 'max'=>250),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, skill_child_disposition_id, email_address, email_subject, email_content, is_goal_disposition, is_details, is_callback_date, is_callback_time, is_note', 'safe', 'on'=>'search'),
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
			'skill_child_disposition_id' => 'Skill Disposition',
			'email_address' => 'Email Address',
			'email_subject' => 'Email Subject',
			'email_content' => 'Email Content',
			'is_goal_disposition' => 'Is Goal Disposition',
			'is_details' => 'Is Details',
			'is_callback_date' => 'Is Callback Date',
			'is_callback_time' => 'Is Callback Time',
			'is_note' => 'Is Note',
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
		$criteria->compare('skill_child_disposition_id',$this->skill_child_disposition_id);
		$criteria->compare('email_address',$this->email_address,true);
		$criteria->compare('email_subject',$this->email_subject,true);
		$criteria->compare('email_content',$this->email_content,true);
		$criteria->compare('is_goal_disposition',$this->is_goal_disposition);
		$criteria->compare('is_details',$this->is_details);
		$criteria->compare('is_callback_date',$this->is_callback_date);
		$criteria->compare('is_callback_time',$this->is_callback_time);
		$criteria->compare('is_note',$this->is_note);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return SkillDispositionEmail the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
