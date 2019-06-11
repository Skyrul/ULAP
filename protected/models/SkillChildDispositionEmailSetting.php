<?php

/**
 * This is the model class for table "{{skill_child_disposition_email_setting}}".
 *
 * The followings are the available columns in table '{{skill_child_disposition_email_setting}}':
 * @property integer $id
 * @property integer $skill_child_disposition_id
 * @property integer $skill_child_disposition_email_id
 * @property integer $type
 * @property string $email_address
 * @property integer $is_deleted
 * @property string $date_created
 * @property string $date_updated
 */
class SkillChildDispositionEmailSetting extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{skill_child_disposition_email_setting}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('skill_child_disposition_id, skill_child_disposition_email_id, type, email_address, is_deleted, date_created, date_updated', 'required'),
			array('skill_child_disposition_id, skill_child_disposition_email_id, type, is_deleted', 'numerical', 'integerOnly'=>true),
			array('email_address', 'length', 'max'=>128),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, skill_child_disposition_id, skill_child_disposition_email_id, type, email_address, is_deleted, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'skill_child_disposition_id' => 'Skill Child Disposition',
			'skill_child_disposition_email_id' => 'Skill Child Disposition Email',
			'type' => 'Type',
			'email_address' => 'Email Address',
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
		$criteria->compare('skill_child_disposition_id',$this->skill_child_disposition_id);
		$criteria->compare('skill_child_disposition_email_id',$this->skill_child_disposition_email_id);
		$criteria->compare('type',$this->type);
		$criteria->compare('email_address',$this->email_address,true);
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
	 * @return SkillChildDispositionEmailSetting the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
