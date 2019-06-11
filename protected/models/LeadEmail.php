<?php
class LeadEmail extends CActiveRecord
{	
	
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{lead_email}}';
	}
 
	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('lead_id, email_address, skill_email_template_id', 'required'),
			array('email_address', 'email'),
			array('lead_id, is_sent, skill_email_template_id', 'numerical', 'integerOnly'=>true),
			array('personal_note', 'length', 'max'=>500),
			array('date_created', 'safe'),
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
			'lead' => array(self::BELONGS_TO, 'Lead', 'lead_id'),
			'skillEmailTemplate' => array(self::BELONGS_TO, 'SkillEmailTemplate', 'skill_email_template_id'),
			'leadEmailAttachment' => array(self::HAS_MANY, 'LeadEmailAttachment', 'lead_email_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'lead_id' => 'Lead',
			'skill_email_template_id' => 'Skill Email Template',
			'email_address' => 'Email Address',
			'is_sent' => 'Is Sent',
			'date_created' => 'Date Created',
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
		$criteria->compare('company_name',$this->company_name,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('email_address',$this->email_address,true);
		$criteria->compare('contact',$this->contact,true);
		$criteria->compare('phone',$this->phone,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('is_deleted',$this->is_deleted);
		$criteria->compare('date_created',$this->date_created,true);
		// $criteria->compare('date_updated',$this->date_updated,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Company the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function beforeSave()
	{
		if($this->isNewRecord)
			$this->date_created = date("Y-m-d H:i:s");
		// else
			// $this->date_updated = date("Y-m-d H:i:s");
		
		return parent::beforeSave();
	}

}
