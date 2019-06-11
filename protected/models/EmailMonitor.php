<?php

/**
 * This is the model class for table "{{email_monitor}}".
 *
 * The followings are the available columns in table '{{email_monitor}}':
 * @property integer $id
 * @property integer $lead_id
 * @property integer $agent_id
 * @property integer $customer_id
 * @property integer $skill_id
 * @property integer $disposition_id
 * @property string $html_content
 * @property integer $type
 * @property integer $status
 * @property string $date_created
 * @property string $date_updated
 */
class EmailMonitor extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{email_monitor}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('lead_id, agent_id, customer_id, skill_id, disposition_id, child_disposition_id, is_child_skill, calendar_appointment_id, lead_call_history_id, type, status', 'numerical', 'integerOnly'=>true),
			array('disposition', 'length', 'max'=>255),
			array('html_content, text_content, date_created, date_updated', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, lead_id, agent_id, customer_id, skill_id, disposition_id, child_disposition_id, is_child_skill, calendar_appointment_id, lead_call_history_id, disposition, html_content, text_content, type, status, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'agentAccount' => array(self::BELONGS_TO, 'Account', 'agent_id'),
			'customer' => array(self::BELONGS_TO, 'Customer', 'customer_id'),
			'skill' => array(self::BELONGS_TO, 'Skill', 'skill_id'),
			'disposition' => array(self::BELONGS_TO, 'SkillDisposition', 'disposition_id'),
			'calendarAppointment' => array(self::BELONGS_TO, 'CalendarAppointment', 'calendar_appointment_id'),
			'leadCallHistory' => array(self::BELONGS_TO, 'LeadCallHistory', 'lead_call_history_id'),
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
			'agent_id' => 'Agent',
			'customer_id' => 'Customer',
			'skill_id' => 'Skill',
			'disposition_id' => 'Disposition',
			'html_content' => 'Html Content',
			'text_content' => 'Text Content',
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
		$criteria->compare('lead_id',$this->lead_id);
		$criteria->compare('agent_id',$this->agent_id);
		$criteria->compare('customer_id',$this->customer_id);
		$criteria->compare('skill_id',$this->skill_id);
		$criteria->compare('disposition_id',$this->disposition_id);
		$criteria->compare('html_content',$this->html_content,true);
		$criteria->compare('text_content',$this->text_content,true);
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
	 * @return EmailMonitor the static model class
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

	
	public function getReplacementCodeValues($string = '')
	{	
		if( $string != '' )
		{			
			preg_match_all("/\[[^\]]*\]/", $string, $matches);
			
			if( $matches[0] )
			{
				foreach( $matches[0]  as $match )
				{
					switch( $match )
					{
						//lead
						case '[first_name]': 			$string = str_replace($match, !empty($this->lead->first_name) ? $this->lead->first_name : '', $string); break;
						case '[last_name]':				$string = str_replace($match, !empty($this->lead->last_name) ? $this->lead->last_name : '', $string); break;
						case '[partner_first_name]': 	$string = str_replace($match, !empty($this->lead->partner_first_name) ? $this->lead->partner_first_name : '', $string); break;
						case '[partner_last_name]': 	$string = str_replace($match, !empty($this->lead->partner_last_name) ? $this->lead->partner_last_name : '', $string); break;
						case '[office_phone_number]': 	$string = str_replace($match, !empty($this->lead->office_phone_number) ? $this->lead->office_phone_number : '', $string); break;
						case '[mobile_phone_number]': 	$string = str_replace($match, !empty($this->lead->mobile_phone_number) ? $this->lead->mobile_phone_number : '', $string); break;
						case '[home_phone_number]': 	$string = str_replace($match, !empty($this->lead->home_phone_number) ? $this->lead->home_phone_number : '', $string); break;
						case '[city]': 					$string = str_replace($match, !empty($this->lead->city) ? $this->lead->city : '', $string); break;
						case '[state]': 				$string = str_replace($match, !empty($this->lead->state) ? $this->lead->state : '', $string); break;
						case '[zip_code]': 				$string = str_replace($match, !empty($this->lead->zip_code) ? $this->lead->zip_code : '', $string); break;
						case '[address]': 				$string = str_replace($match, !empty($this->lead->address) ? $this->lead->address : '', $string); break;
						case '[address2]': 				$string = str_replace($match, !empty($this->lead->address2) ? $this->lead->address2 : '', $string); break;
						
						
						//customer
						case '[customer_first_name]': $string = str_replace($match, !empty($this->customer->firstname) ? $this->customer->firstname : '', $string); break;
						case '[customer_last_name]':  $string = str_replace($match, !empty($this->customer->lastname) ? $this->customer->lastname : '', $string); break;
						
						
						//calendar
						case '[calendar_name]': 	
							
							if( isset($this->calendarAppointment->calendar) )
							{
								$string = str_replace($match, !empty($this->calendarAppointment->calendar->calendar_name) ? $this->calendarAppointment->calendar->calendar_name : '', $string); break;
							}
						break;
						
						case '[office_assigned_to_calendar]': 	
							
							if( isset($this->calendarAppointment->calendar->office) )
							{
								$string = str_replace($match, !empty($this->calendarAppointment->calendar->office->office_name) ? $this->calendarAppointment->calendar->office->office_name : '', $string); break;
							}
						break;
						
						case '[staff_assigned_to_calendar]': 	
							
							if( isset($this->calendarAppointment) )
							{
								$calendarStaffAssignment = CalendarStaffAssignment::model()->find(array(
									'condition' => 'calendar_id = :calendar_id',
									'params' => array(
										'calendar_id' => $this->calendarAppointment->calendar->id,
									),
								));
								
								if( $calendarStaffAssignment && isset($calendarStaffAssignment->staff) )
								{
									$string = str_replace($match, !empty($calendarStaffAssignment->staff->staff_name) ? $calendarStaffAssignment->staff->staff_name : '', $string); break;
								}
							}
						break;
						
						
						//calendar appoint
						case '[appointment_location]': 	
							
							if( isset($this->calendarAppointment) )
							{
								$string = str_replace($match, !empty($this->calendarAppointment->location) ? $this->calendarAppointment->location : '', $string); break;
							}
							
						break;
						
						case '[appointment_date]': 	
							
							if( isset($this->calendarAppointment) )
							{
								$string = str_replace($match, !empty($this->calendarAppointment->start_date) ? date('m/d/Y', strtotime($this->calendarAppointment->start_date)) : '', $string); break;
							}
							
						break;
						
						case '[appointment_time]': 	
							
							if( isset($this->calendarAppointment) )
							{
								$string = str_replace($match, !empty($this->calendarAppointment->start_date) ? date('g:i A', strtotime($this->calendarAppointment->start_date)) : '', $string); break;
							}
							
						break;
						
						
						case '[ics_file_link]': 	
							
						if( isset($this->calendarAppointment) )
						{
							$htmlICSLink = CHtml::link('Click here to Download ICS File','http://portal.engagexapp.com/index.php/site/DownloadICS?calendarAppointmentId='.$this->calendarAppointment);
							$string = str_replace($match, !empty($this->calendarAppointment->location) ? $htmlICSLink : '', $string); break;
						}
							
						default: $string = str_replace($match, '', $string);
					}
				}
			}
		}
		
		return $string;
	}
		
}
