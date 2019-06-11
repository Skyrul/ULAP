<?php

/**
 * This is the model class for table "{{skill_disposition}}".
 *
 * The followings are the available columns in table '{{skill_disposition}}':
 * @property integer $id
 * @property integer $skill_id
 * @property string $template_name
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
class SkillEmailTemplate extends CActiveRecord
{
	public $existingId;
	public $lead;
	public $customer;
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{skill_email_template}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('skill_id, template_name, is_sending_option_default', 'required'),
			array('existingId', 'required','on'=>'cloneExisting'),
			array('skill_id, is_sending_option_default', 'numerical', 'integerOnly'=>true),
			array('template_name', 'length', 'max'=>128),
			array('smtp_host, smtp_username, smtp_password', 'length', 'max'=>120),
			array('description, from, to, cc, bcc, subject', 'length', 'max'=>255),
			array('html_header, html_body, html_footer, text_body', 'safe'),
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
			// 'skillDispositionDetail' => array(self::HAS_ONE, 'SkillDispositionDetail', 'skill_disposition_id'),
			// 'skillDispositionDetails' => array(self::HAS_MANY, 'SkillDispositionDetail', 'skill_disposition_id'),
			// 'skillDispositionEmailAttachments' => array(self::HAS_MANY, 'SkillDispositionEmailAttachment', 'skill_disposition_id'),
			// 'attachments' => array(self::HAS_MANY, 'Fileupload', 'id'),
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
			'template_name' => 'Template Name',
			'description' => 'Description',
			
			'from' => 'Send as Address',
			'to' => 'To',
			'cc' => 'Cc',
			'bcc' => 'Bcc',
			'subject' => 'Subject',
			'html_header' => 'Html Header',
			'html_body' => 'Html Body',
			'html_footer' => 'Html Footer',
			'existingId' => 'Existing Template',
			'is_sending_option_default' => 'Sending Option',
			'smtp_host' => 'Mail Hostname',
			'smtp_username' => 'Mail Username',
			'smtp_password' => 'Mail Password',
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
		$criteria->compare('template_name',$this->template_name,true);
		$criteria->compare('description',$this->description,true);
		
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

	public function getReplacementCodeValues($lead, $string = '', $personal_note = '')
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
						case '[first_name]': 			$string = str_replace($match, !empty($lead->first_name) ? $lead->first_name : '', $string); break;
						case '[last_name]':				$string = str_replace($match, !empty($lead->last_name) ? $lead->last_name : '', $string); break;
						case '[partner_first_name]': 	$string = str_replace($match, !empty($lead->partner_first_name) ? $lead->partner_first_name : '', $string); break;
						case '[partner_last_name]': 	$string = str_replace($match, !empty($lead->partner_last_name) ? $lead->partner_last_name : '', $string); break;
						case '[office_phone_number]': 	$string = str_replace($match, !empty($lead->office_phone_number) ? $lead->office_phone_number : '', $string); break;
						case '[mobile_phone_number]': 	$string = str_replace($match, !empty($lead->mobile_phone_number) ? $lead->mobile_phone_number : '', $string); break;
						case '[home_phone_number]': 	$string = str_replace($match, !empty($lead->home_phone_number) ? $lead->home_phone_number : '', $string); break;
						case '[city]': 					$string = str_replace($match, !empty($lead->city) ? $lead->city : '', $string); break;
						case '[state]': 				$string = str_replace($match, !empty($lead->state) ? $lead->state : '', $string); break;
						case '[zip_code]': 				$string = str_replace($match, !empty($lead->zip_code) ? $lead->zip_code : '', $string); break;
						case '[address]': 				$string = str_replace($match, !empty($lead->address) ? $lead->address : '', $string); break;
						case '[address2]': 				$string = str_replace($match, !empty($lead->address2) ? $lead->address2 : '', $string); break;
						
						
						//customer
						case '[customer_first_name]': $string = str_replace($match, !empty($this->customer->firstname) ? $this->customer->firstname : '', $string); break;
						case '[customer_last_name]':  $string = str_replace($match, !empty($this->customer->lastname) ? $this->customer->lastname : '', $string); break;
						
						case '[personal_note]':  $string = str_replace($match, $personal_note, $string); break;
						
						default: $string = str_replace($match, '', $string);
					}
				}
			}
		}
		
		return $string;
	}

	public function getHtmlContent()
	{
		return $this->html_header.$this->html_body.$this->html_footer;
	}
}
