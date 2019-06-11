<?php

/**
 * This is the model class for table "{{lead_call_wrap_time}}".
 *
 * The followings are the available columns in table '{{lead_call_wrap_time}}':
 * @property integer $id
 * @property integer $agent_account_id
 * @property integer $lead_id
 * @property integer $main_skill_id
 * @property integer $child_skill_id
 * @property string $start_time
 * @property string $end_time
 * @property integer $call_type
 * @property integer $status
 * @property integer $type
 * @property string $date_created
 * @property string $date_updated
 */
class LeadCallWrapTime extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{lead_call_wrap_time}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('agent_account_id, lead_id', 'required'),
			array('agent_account_id, lead_id, main_skill_id, child_skill_id, call_type, status, type, group_id', 'numerical', 'integerOnly'=>true),
			array('start_time, end_time, date_created, date_updated', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, agent_account_id, lead_id, main_skill_id, child_skill_id, start_time, end_time, call_type, status, type, date_created, date_updated, group_id', 'safe', 'on'=>'search'),
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
			'agent_account_id' => 'Agent Account',
			'lead_id' => 'Lead',
			'main_skill_id' => 'Main Skill',
			'child_skill_id' => 'Child Skill',
			'start_time' => 'Start Time',
			'end_time' => 'End Time',
			'call_type' => 'Call Type',
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
		$criteria->compare('agent_account_id',$this->agent_account_id);
		$criteria->compare('lead_id',$this->lead_id);
		$criteria->compare('main_skill_id',$this->main_skill_id);
		$criteria->compare('child_skill_id',$this->child_skill_id);
		$criteria->compare('start_time',$this->start_time,true);
		$criteria->compare('end_time',$this->end_time,true);
		$criteria->compare('call_type',$this->call_type);
		$criteria->compare('status',$this->status);
		$criteria->compare('type',$this->type);
		$criteria->compare('date_created',$this->date_created,true);
		$criteria->compare('date_updated',$this->date_updated,true);
		$criteria->compare('group_id',$this->date_updated,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return LeadCallWrapTime the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	protected function beforeSave()
	{
		if (parent::beforeSave())
		{
			if ($this->isNewRecord)
			{
				$this->date_created = $this->date_updated = date('Y-m-d H:i:s');
			}
			else
			{
				$this->date_updated = date('Y-m-d H:i:s');
			}
			
			return true;
		}
	}
}
