<?php

/**
 * This is the model class for table "{{skill_history}}".
 *
 * The followings are the available columns in table '{{skill_history}}':
 * @property integer $id
 * @property integer $skill_id
 * @property integer $model_id
 * @property integer $account_id
 * @property integer $field_name
 * @property integer $content
 * @property string $old_data
 * @property string $new_data
 * @property integer $status
 * @property integer $type
 * @property string $date_created
 * @property string $date_updated
 */
class SkillHistory extends CActiveRecord
{
	const TYPE_ADDED = 1;
	const TYPE_UPDATED = 2;
	const TYPE_DELETED = 3;
	const TYPE_DOWNLOADED = 4;
	const TYPE_REMOVED = 5;
	
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{skill_history}}';
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
			array('skill_id, model_id, account_id, status, type', 'numerical', 'integerOnly'=>true),
			array('field_name', 'length', 'max'=>255),
			array('content, old_data, new_data', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, skill_id, model_id, account_id, field_name, content, old_data, new_data, status, type, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'account' => array(self::BELONGS_TO, 'Account', 'account_id'),
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
			'model_id' => 'Model',
			'account_id' => 'Account',
			'field_name' => 'Field Name',
			'content' => 'Content',
			'old_data' => 'Old Data',
			'new_data' => 'New Data',
			'status' => 'Status',
			'type' => 'Type',
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
		$criteria->compare('model_id',$this->model_id);
		$criteria->compare('account_id',$this->account_id);
		$criteria->compare('field_name',$this->field_name);
		$criteria->compare('content',$this->content);
		$criteria->compare('old_data',$this->old_data,true);
		$criteria->compare('new_data',$this->new_data,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('type',$this->type);
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
	 * @return SkillHistory the static model class
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
