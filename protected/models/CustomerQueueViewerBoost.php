<?php

/**
 * This is the model class for table "{{customer_queue_viewer_boost}}".
 *
 * The followings are the available columns in table '{{customer_queue_viewer_boost}}':
 * @property integer $id
 * @property integer $customer_id
 * @property integer $skill_id
 * @property integer $value
 * @property integer $magnitude_value
 * @property integer $type
 * @property integer $status
 * @property string $date_created
 * @property string $date_updated
 */
class CustomerQueueViewerBoost extends CActiveRecord
{
	const STATUS_ACTIVE = 1;
	const STATUS_INACTIVE = 2;
	
	
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{customer_queue_viewer_boost}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('customer_id, skill_id, magnitude_value', 'required'),
			array('goal_value, dial_value', 'validateGoalorDialValue'),
			array('beginning_date', 'required', 'on'=> 'scheduledType'),
			array('customer_id, skill_id, goal_value, dial_value, magnitude_value, is_boost_triggered, type, status', 'numerical', 'integerOnly'=>true),
			array('beginning_date', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, customer_id, skill_id, goal_value, dial_value, magnitude_value, type, status, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'goal_value' => 'No. of Appoinments/Goals',
			'dial_value' => 'No. of Dials',
			'magnitude_value' => 'No. of Agent assigned',
			'beginning_date' => 'Beginning Date',
			'type' => 'Schedule Type',
			'status' => 'Status',
			'date_created' => 'Date Created',
			'date_updated' => 'Date Updated',
		);
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
		$criteria->compare('value',$this->value);
		$criteria->compare('magnitude_value',$this->magnitude_value);
		$criteria->compare('type',$this->type);
		$criteria->compare('status',$this->status);
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
	 * @return CustomerQueueViewerBoost the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function validateGoalorDialValue($attribute, $params)
	{
		if (empty($this->goal_value) && empty($this->dial_value))
			$this->addError($attribute, 'No of Goal or Dial is required');
	  
		return;
	}
}
