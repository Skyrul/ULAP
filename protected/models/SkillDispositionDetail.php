<?php

/**
 * This is the model class for table "skill_disposition_detail".
 *
 * The followings are the available columns in table 'skill_disposition_detail':
 * @property integer $id
 * @property integer $skill_id
 * @property integer $skill_disposition_id
 * @property string $skill_disposition_detail_name
 * @property string $description
 * @property string $internal_notes
 * @property string $external_notes
 * @property string $date_created
 * @property string $date_updated
 */
class SkillDispositionDetail extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{skill_disposition_detail}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('skill_id, skill_disposition_id, skill_disposition_detail_name', 'required'),
			array('skill_id, skill_disposition_id', 'numerical', 'integerOnly'=>true),
			array('skill_disposition_detail_name, description', 'length', 'max'=>150),
			array('internal_notes, external_notes', 'length', 'max'=>500),
			array('internal_notes, external_notes', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, skill_id, skill_disposition_id, skill_disposition_detail_name, description, internal_notes, external_notes, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'skill_id' => 'Skill',
			'skill_disposition_id' => 'Skill Disposition',
			'skill_disposition_detail_name' => 'Skill Disposition Detail Name',
			'description' => 'Description',
			'internal_notes' => 'Internal Notes',
			'external_notes' => 'Notes Prefill',
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
		$criteria->compare('skill_id',$this->skill_id);
		$criteria->compare('skill_disposition_id',$this->skill_disposition_id);
		$criteria->compare('skill_disposition_detail_name',$this->skill_disposition_detail_name,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('internal_notes',$this->internal_notes,true);
		$criteria->compare('external_notes',$this->external_notes,true);
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
	 * @return SkillDispositionDetail the static model class
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
	
	public function bySkillDispositionId($skill_disposition_id)
	{
		$criteria = new CDbCriteria;
		$criteria->compare('skill_disposition_id',$skill_disposition_id);
		
		$this->getDbCriteria()->mergeWith($criteria);
		return $this;
	}
}
