<?php

/**
 * This is the model class for table "{{skill_disposition}}".
 *
 * The followings are the available columns in table '{{skill_disposition}}':
 * @property integer $id
 * @property integer $skill_id
 * @property string $skill_disposition_name
 * @property string $description
 * @property integer $is_voice_contact
 * @property string $retry_interval
 * @property integer $is_complete_leads
 * @property integer $is_send_email
 * @property integer $is_visible_on_report
 * @property string $recycle_interval
 * @property integer $is_agent_ownership
 * @property string $ownership_reassignment
 * @property integer $skill_child_id
 * @property string $from
 * @property string $to
 * @property string $cc
 * @property string $bcc
 * @property string $subject
 * @property string $html_body
 */
class SkillDisposition extends CActiveRecord
{
	public $existingId;
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{skill_disposition}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('skill_id, skill_disposition_name', 'required'),
			array('existingId', 'required','on'=>'cloneExisting'),
			array('skill_id, is_voice_contact, is_complete_leads, is_send_email, is_send_text, retry_interval_type, is_visible_on_report, is_agent_ownership, skill_child_id, is_do_not_call, is_callback, is_bad_phone_number, is_appointment_set, is_location_conflict, is_schedule_conflict, existingId, is_survey_complete, sub_dispo_is_required, is_email_require, mark_as_goal', 'numerical', 'integerOnly'=>true),
			array('skill_disposition_name', 'length', 'max'=>128),
			array('description, from, to, cc, bcc, subject', 'length', 'max'=>255),
			array('retry_interval', 'length', 'max'=>30),
			array('retry_interval', 'required','on' => 'retryInterval'),
			array('recycle_interval, ownership_reassignment', 'length', 'max'=>10),
			array('notes_prefill, html_header, html_body, html_footer, text_body', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, skill_id, skill_disposition_name, description, is_voice_contact, retry_interval, is_complete_leads, is_send_email, is_send_text, retry_interval_type, is_visible_on_report, recycle_interval, is_agent_ownership, ownership_reassignment, skill_child_id, from, to, cc, bcc, subject, html_header, html_body, text_body, html_footer, mark_as_goal', 'safe', 'on'=>'search'),
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
			'skillDispositionDetail' => array(self::HAS_ONE, 'SkillDispositionDetail', 'skill_disposition_id'),
			'skillDispositionDetails' => array(self::HAS_MANY, 'SkillDispositionDetail', 'skill_disposition_id'),
			'skillDispositionEmailAttachments' => array(self::HAS_MANY, 'SkillDispositionEmailAttachment', 'skill_disposition_id'),
			'attachments' => array(self::HAS_MANY, 'Fileupload', 'id'),
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
			'skill_disposition_name' => 'Skill Disposition Name',
			'description' => 'Description',
			'is_voice_contact' => 'Voice Contact',
			'is_callback' => 'Callback',
			'retry_interval' => 'Retry Interval',
			'is_complete_leads' => 'Completes Lead',
			'is_send_email' => 'Send Email',
			'is_send_text' => 'Texting Enabled',
			'is_visible_on_report' => 'Visible On Report',
			'recycle_interval' => 'Recycle Interval',
			'is_agent_ownership' => 'Agent Ownership',
			'is_do_not_call' => 'Do Not Call',
			'is_bad_phone_number' => 'Bad Phone Number',
			'is_appointment_set' => 'Appointment Set',
			'is_location_conflict' => 'Location Conflict',
			'is_schedule_conflict' => 'Schedule Conflict',
			'is_survey_complete' => 'Survey Complete',
			'is_email_require' => 'Email Set',
			'ownership_reassignment' => 'Reassign Owner',
			'skill_child_id' => 'Move to Child Skill',
			'from' => 'From',
			'to' => 'To',
			'cc' => 'Cc',
			'bcc' => 'Bcc',
			'subject' => 'Subject',
			'html_header' => 'Html Header',
			'html_body' => 'Html Body',
			'html_footer' => 'Html Footer',
			'existingId' => 'Existing Disposition',
			'sub_dispo_is_required' => 'Require Disposition Detail'
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
		$criteria->compare('skill_disposition_name',$this->skill_disposition_name,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('is_voice_contact',$this->is_voice_contact);
		$criteria->compare('retry_interval',$this->retry_interval,true);
		$criteria->compare('is_complete_leads',$this->is_complete_leads);
		$criteria->compare('is_send_email',$this->is_send_email);
		$criteria->compare('is_visible_on_report',$this->is_visible_on_report);
		$criteria->compare('recycle_interval',$this->recycle_interval,true);
		$criteria->compare('is_agent_ownership',$this->is_agent_ownership);
		$criteria->compare('ownership_reassignment',$this->ownership_reassignment,true);
		$criteria->compare('skill_child_id',$this->skill_child_id);
		$criteria->compare('from',$this->from,true);
		$criteria->compare('to',$this->to,true);
		$criteria->compare('cc',$this->cc,true);
		$criteria->compare('bcc',$this->bcc,true);
		$criteria->compare('subject',$this->subject,true);
		$criteria->compare('html_body',$this->html_body,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return SkillDisposition the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	
	public function bySkillId($skill_id)
	{
		$criteria = new CDbCriteria;
		$criteria->compare('skill_id',$skill_id);
		
		$this->getDbCriteria()->mergeWith($criteria);
		return $this;
	}
	
	
	public static $listRetryIntervals = null;
	public static function listRetryIntervals()
	{
		if(self::$listRetryIntervals === null)
		{
			for($hour = 1; $hour <= 48; $hour++)
			{
				$hourToSeconds = ($hour * (60 * 60));
				
				self::$listRetryIntervals[$hourToSeconds] = $hour.' HR';
			}
		}
		
		return self::$listRetryIntervals;
	}
	
	public static $listRetryDayIntervals = null;
	public static function listRetryDayIntervals()
	{
		if(self::$listRetryDayIntervals === null)
		{
			for($day = 1; $day <= 30; $day++)
			{
				$seconds = 86400 * $day;
				
				$suffix =  $day > 1 ? ' Days' : ' Day';
				
				self::$listRetryDayIntervals[$seconds] = $day.$suffix;
			}
		}
		
		return self::$listRetryDayIntervals;
	}
	
	public static $listRecycleIntervals = null;
	public static function listRecycleIntervals()
	{
		if(self::$listRecycleIntervals === null)
		{
			for($ctr = 1; $ctr <= 12; $ctr++)
			{
				self::$listRecycleIntervals[$ctr] = $ctr;
			}
		}
		
		return self::$listRecycleIntervals;
	}
}
