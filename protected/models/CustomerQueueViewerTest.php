<?php

/**
 * This is the model class for table "{{customer_queue_viewer_test}}".
 *
 * The followings are the available columns in table '{{customer_queue_viewer_test}}':
 * @property integer $id
 * @property integer $customer_id
 * @property integer $contract_id
 * @property integer $skill_id
 * @property string $customer_name
 * @property string $skill_name
 * @property string $priority_reset_date
 * @property string $initial_priority
 * @property string $priority
 * @property string $pace
 * @property integer $current_dials
 * @property integer $current_goals
 * @property integer $total_leads
 * @property integer $available_leads
 * @property integer $total_potential_dials
 * @property string $next_available_calling_time
 * @property string $available_calling_blocks
 * @property string $call_agent
 * @property integer $max_dials
 * @property integer $dials_needed
 * @property integer $dials_until_reset
 * @property string $fulfillment_type
 * @property integer $type
 * @property integer $status
 * @property string $date_created
 * @property string $date_updated
 */
class CustomerQueueViewerTest extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{customer_queue_viewer_test}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('customer_id, contract_id, skill_id, current_dials, current_goals, total_leads, available_leads, not_completed_leads, total_potential_dials, max_dials, dials_needed, dials_until_reset, type, status', 'numerical', 'integerOnly'=>true),
			array('customer_name, skill_name, priority_reset_date, initial_priority, priority, pace, next_available_calling_time, available_calling_blocks, call_agent, fulfillment_type', 'length', 'max'=>255),
			array('date_created, date_updated', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, customer_id, contract_id, skill_id, customer_name, skill_name, priority_reset_date, initial_priority, priority, pace, current_dials, current_goals, total_leads, available_leads, not_completed_leads, total_potential_dials, next_available_calling_time, available_calling_blocks, call_agent, max_dials, dials_needed, dials_until_reset, fulfillment_type, type, status, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'customer' => array(self::BELONGS_TO, 'Customer', 'customer_id'),
			'contract' => array(self::BELONGS_TO, 'Contract', 'contract_id'),
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
			'customer_id' => 'Customer',
			'contract_id' => 'Contract',
			'skill_id' => 'Skill',
			'customer_name' => 'Customer Name',
			'skill_name' => 'Skill Name',
			'priority_reset_date' => 'Priority Reset Date',
			'initial_priority' => 'Initial Priority',
			'priority' => 'Priority',
			'pace' => 'Pace',
			'current_dials' => 'Current Dials',
			'current_goals' => 'Current Goals',
			'total_leads' => 'Total Leads',
			'available_leads' => 'Available Leads',
			'total_potential_dials' => 'Total Potential Dials',
			'next_available_calling_time' => 'Next Available Calling Time',
			'available_calling_blocks' => 'Available Calling Blocks',
			'call_agent' => 'Call Agent',
			'max_dials' => 'Max Dials',
			'dials_needed' => 'Dials Needed',
			'dials_until_reset' => 'Dials Until Reset',
			'fulfillment_type' => 'Fulfillment Type',
			'type' => 'Type',
			'status' => 'Status',
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
		$criteria->compare('contract_id',$this->contract_id);
		$criteria->compare('skill_id',$this->skill_id);
		$criteria->compare('customer_name',$this->customer_name,true);
		$criteria->compare('skill_name',$this->skill_name,true);
		$criteria->compare('priority_reset_date',$this->priority_reset_date,true);
		$criteria->compare('initial_priority',$this->initial_priority,true);
		$criteria->compare('priority',$this->priority,true);
		$criteria->compare('pace',$this->pace,true);
		$criteria->compare('current_dials',$this->current_dials);
		$criteria->compare('current_goals',$this->current_goals);
		$criteria->compare('total_leads',$this->total_leads);
		$criteria->compare('available_leads',$this->available_leads);
		$criteria->compare('total_potential_dials',$this->total_potential_dials);
		$criteria->compare('next_available_calling_time',$this->next_available_calling_time,true);
		$criteria->compare('available_calling_blocks',$this->available_calling_blocks,true);
		$criteria->compare('call_agent',$this->call_agent,true);
		$criteria->compare('max_dials',$this->max_dials);
		$criteria->compare('dials_needed',$this->dials_needed);
		$criteria->compare('dials_until_reset',$this->dials_until_reset);
		$criteria->compare('fulfillment_type',$this->fulfillment_type,true);
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
	 * @return CustomerQueueViewer the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
