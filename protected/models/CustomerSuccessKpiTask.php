<?php

/**
 * This is the model class for table "{{customer_success_kpi_task}}".
 *
 * The followings are the available columns in table '{{customer_success_kpi_task}}':
 * @property integer $id
 * @property integer $customer_success_kpi_id
 * @property integer $assigned_account_id
 * @property string $task_name
 * @property integer $delay_from_initial_days
 * @property integer $starting_priority
 * @property integer $max_priority
 * @property integer $priority_add
 * @property integer $sends_email
 * @property string $email_from
 * @property string $email_to
 * @property string $email_cc
 * @property string $email_bcc
 * @property string $email_subject
 * @property string $email_html_header
 * @property string $email_html_body
 * @property string $email_html_footer
 * @property integer $status
 * @property integer $type
 * @property string $date_created
 * @property string $date_updated
 */
class CustomerSuccessKpiTask extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{customer_success_kpi_task}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('customer_success_kpi_id, task_name', 'required'),
			array('customer_success_kpi_id, assigned_account_id, delay_from_initial_days, starting_priority, max_priority, priority_add, sends_email, status, type', 'numerical', 'integerOnly'=>true),
			array('task_name, email_from, email_to, email_cc, email_bcc, email_subject', 'length', 'max'=>255),
			array('email_html_header, email_html_body, email_html_footer, date_created, date_updated', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, customer_success_kpi_id, assigned_account_id, task_name, delay_from_initial_days, starting_priority, max_priority, priority_add, sends_email, email_from, email_to, email_cc, email_bcc, email_subject, email_html_header, email_html_body, email_html_footer, status, type, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'customer_success_kpi_id' => 'Customer Success Kpi',
			'assigned_account_id' => 'Assigned Account',
			'task_name' => 'Task Name',
			'delay_from_initial_days' => 'Delay From Initial Days',
			'starting_priority' => 'Starting Priority',
			'max_priority' => 'Max Priority',
			'priority_add' => 'Priority Add',
			'sends_email' => 'Sends Email',
			'email_from' => 'Email From',
			'email_to' => 'Email To',
			'email_cc' => 'Email Cc',
			'email_bcc' => 'Email Bcc',
			'email_subject' => 'Email Subject',
			'email_html_header' => 'Email Html Header',
			'email_html_body' => 'Email Html Body',
			'email_html_footer' => 'Email Html Footer',
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
		$criteria->compare('customer_success_kpi_id',$this->customer_success_kpi_id);
		$criteria->compare('assigned_account_id',$this->assigned_account_id);
		$criteria->compare('task_name',$this->task_name,true);
		$criteria->compare('delay_from_initial_days',$this->delay_from_initial_days);
		$criteria->compare('starting_priority',$this->starting_priority);
		$criteria->compare('max_priority',$this->max_priority);
		$criteria->compare('priority_add',$this->priority_add);
		$criteria->compare('sends_email',$this->sends_email);
		$criteria->compare('email_from',$this->email_from,true);
		$criteria->compare('email_to',$this->email_to,true);
		$criteria->compare('email_cc',$this->email_cc,true);
		$criteria->compare('email_bcc',$this->email_bcc,true);
		$criteria->compare('email_subject',$this->email_subject,true);
		$criteria->compare('email_html_header',$this->email_html_header,true);
		$criteria->compare('email_html_body',$this->email_html_body,true);
		$criteria->compare('email_html_footer',$this->email_html_footer,true);
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
	 * @return CustomerSuccessKpiTask the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
