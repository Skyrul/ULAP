<?php

/**
 * This is the model class for table "{{state_farm_agent_level_report}}".
 *
 * The followings are the available columns in table '{{state_farm_agent_level_report}}':
 * @property integer $id
 * @property string $agent_last_name
 * @property string $agent_first_name
 * @property string $alias
 * @property string $agent_code
 * @property string $subsidy_percentage
 * @property string $final_tier
 * @property string $effective_month
 * @property string $program_type
 * @property string $contracted_quantity
 * @property string $names_submitted
 * @property string $names_from_previous_months
 * @property string $status
 * @property string $start_date
 * @property string $end_date
 * @property string $cancel_date
 * @property string $total_service_dials
 * @property string $total_appointment_set_attempt_dials
 * @property string $total_appt_customers_called
 * @property string $total_unique_appt_customer_contacts
 * @property string $total_unique_non_appt_customer_contacts
 * @property string $in_person_appointments
 * @property string $phone_appointments
 * @property string $total_appointments
 * @property string $appt_to_unique_contacts
 * @property string $appt_to_customers_called
 * @property string $appt_to_dials
 * @property string $date_created
 * @property string $date_updated
 */
class StateFarmAgentLevelReport extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{state_farm_agent_level_report}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('agent_last_name, agent_first_name, alias, agent_code, subsidy_percentage, final_tier, effective_month, program_type, contracted_quantity, names_submitted, names_from_previous_months, status, start_date, end_date, cancel_date, total_service_dials, total_appointment_set_attempt_dials, total_appt_customers_called, total_unique_appt_customer_contacts, total_unique_non_appt_customer_contacts, in_person_appointments, phone_appointments, total_appointments, appt_to_unique_contacts, appt_to_customers_called, appt_to_dials', 'length', 'max'=>255),
			array('date_created, date_updated', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, agent_last_name, agent_first_name, alias, agent_code, subsidy_percentage, final_tier, effective_month, program_type, contracted_quantity, names_submitted, names_from_previous_months, status, start_date, end_date, cancel_date, total_service_dials, total_appointment_set_attempt_dials, total_appt_customers_called, total_unique_appt_customer_contacts, total_unique_non_appt_customer_contacts, in_person_appointments, phone_appointments, total_appointments, appt_to_unique_contacts, appt_to_customers_called, appt_to_dials, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'agent_last_name' => 'Agent Last Name',
			'agent_first_name' => 'Agent First Name',
			'alias' => 'Alias',
			'agent_code' => 'Agent Code',
			'subsidy_percentage' => 'Subsidy Percentage',
			'final_tier' => 'Final Tier',
			'effective_month' => 'Effective Month',
			'program_type' => 'Program Type',
			'contracted_quantity' => 'Contracted Quantity',
			'names_submitted' => 'Names Submitted',
			'names_from_previous_months' => 'Names From Previous Months',
			'status' => 'Status',
			'start_date' => 'Start Date',
			'end_date' => 'End Date',
			'cancel_date' => 'Cancel Date',
			'total_service_dials' => 'Total Service Dials',
			'total_appointment_set_attempt_dials' => 'Total Appointment Set Attempt Dials',
			'total_appt_customers_called' => 'Total Appt Customers Called',
			'total_unique_appt_customer_contacts' => 'Total Unique Appt Customer Contacts',
			'total_unique_non_appt_customer_contacts' => 'Total Unique Non Appt Customer Contacts',
			'in_person_appointments' => 'In Person Appointments',
			'phone_appointments' => 'Phone Appointments',
			'total_appointments' => 'Total Appointments',
			'appt_to_unique_contacts' => 'Appt To Unique Contacts',
			'appt_to_customers_called' => 'Appt To Customers Called',
			'appt_to_dials' => 'Appt To Dials',
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
		$criteria->compare('agent_last_name',$this->agent_last_name,true);
		$criteria->compare('agent_first_name',$this->agent_first_name,true);
		$criteria->compare('alias',$this->alias,true);
		$criteria->compare('agent_code',$this->agent_code,true);
		$criteria->compare('subsidy_percentage',$this->subsidy_percentage,true);
		$criteria->compare('final_tier',$this->final_tier,true);
		$criteria->compare('effective_month',$this->effective_month,true);
		$criteria->compare('program_type',$this->program_type,true);
		$criteria->compare('contracted_quantity',$this->contracted_quantity,true);
		$criteria->compare('names_submitted',$this->names_submitted,true);
		$criteria->compare('names_from_previous_months',$this->names_from_previous_months,true);
		$criteria->compare('status',$this->status,true);
		$criteria->compare('start_date',$this->start_date,true);
		$criteria->compare('end_date',$this->end_date,true);
		$criteria->compare('cancel_date',$this->cancel_date,true);
		$criteria->compare('total_service_dials',$this->total_service_dials,true);
		$criteria->compare('total_appointment_set_attempt_dials',$this->total_appointment_set_attempt_dials,true);
		$criteria->compare('total_appt_customers_called',$this->total_appt_customers_called,true);
		$criteria->compare('total_unique_appt_customer_contacts',$this->total_unique_appt_customer_contacts,true);
		$criteria->compare('total_unique_non_appt_customer_contacts',$this->total_unique_non_appt_customer_contacts,true);
		$criteria->compare('in_person_appointments',$this->in_person_appointments,true);
		$criteria->compare('phone_appointments',$this->phone_appointments,true);
		$criteria->compare('total_appointments',$this->total_appointments,true);
		$criteria->compare('appt_to_unique_contacts',$this->appt_to_unique_contacts,true);
		$criteria->compare('appt_to_customers_called',$this->appt_to_customers_called,true);
		$criteria->compare('appt_to_dials',$this->appt_to_dials,true);
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
	 * @return StateFarmAgentLevelReport the static model class
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
