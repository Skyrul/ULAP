<?php

/**
 * This is the model class for table "{{agent_call_management_report}}".
 *
 * The followings are the available columns in table '{{agent_call_management_report}}':
 * @property integer $id
 * @property integer $agent_account_id
 * @property integer $skill_id
 * @property string $stats_per_hour
 * @property string $fulfillment_type
 * @property integer $trending
 * @property string $date_created
 * @property string $date_updated
 */
class AgentCallManagementReport extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{agent_call_management_report}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('agent_account_id, skill_id, trending', 'numerical', 'integerOnly'=>true),
			array('stats_per_hour', 'length', 'max'=>255),
			array('fulfillment_type', 'length', 'max'=>15),
			array('date_created, date_updated', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, agent_account_id, skill_id, stats_per_hour, fulfillment_type, trending, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'account' => array(self::BELONGS_TO, 'Account', 'agent_account_id'),
			'skill' => array(self::BELONGS_TO, 'Skill', 'skill_id'),
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
			'skill_id' => 'Skill',
			'stats_per_hour' => 'Stats Per Hour',
			'fulfillment_type' => 'Fulfillment Type',
			'trending' => 'Trending',
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
		$criteria->compare('skill_id',$this->skill_id);
		$criteria->compare('stats_per_hour',$this->stats_per_hour,true);
		$criteria->compare('fulfillment_type',$this->fulfillment_type,true);
		$criteria->compare('trending',$this->trending);
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
	 * @return AgentCallManagementReport the static model class
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
