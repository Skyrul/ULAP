<?php

/**
 * This is the model class for table "{{lead_call_history}}".
 *
 * The followings are the available columns in table '{{lead_call_history}}':
 * @property integer $id
 * @property integer $lead_id
 * @property integer $list_id
 * @property integer $customer_id
 * @property integer $agent_account_id
 * @property integer $company_id
 * @property integer $contract_id
 * @property integer $calendar_appointment_id
 * @property string $disposition
 * @property string $disposition_detail
 * @property string $phone_code
 * @property string $lead_phone_number
 * @property string $agent_note
 * @property string $external_note
 * @property integer $dial_number
 * @property integer $type
 * @property integer $status
 * @property string $start_call_time
 * @property string $end_call_time
 * @property string $date_created
 * @property string $date_updated
 */
class LeadCallHistory extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{lead_call_history}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('lead_id, list_id, customer_id, agent_account_id, company_id, contract_id, calendar_appointment_id, disposition_id, disposition_detail_id, dial_number, type, status, is_skill_child, skill_child_disposition_id', 'numerical', 'integerOnly'=>true),
			array('disposition, disposition_detail', 'length', 'max'=>255),
			array('phone_code', 'length', 'max'=>10),
			array('lead_phone_number', 'length', 'max'=>18),
			array('agent_note, external_note, start_call_time, end_call_time, date_created, date_updated', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, lead_id, list_id, customer_id, agent_account_id, company_id, contract_id, calendar_appointment_id, disposition_id, disposition_detail_id, disposition, disposition_detail, phone_code, lead_phone_number, agent_note, external_note, dial_number, type, status, start_call_time, end_call_time, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'list' => array(self::BELONGS_TO, 'Lists', 'list_id'),
			'skillDisposition' => array(self::BELONGS_TO, 'SkillDisposition', 'disposition_id'),
			'skillDispositionDetail' => array(self::BELONGS_TO, 'SkillDispositionDetail', 'disposition_detail_id'),
			'skillChildDisposition' => array(self::BELONGS_TO, 'SkillChildDisposition', 'skill_child_disposition_id'),
			'company' => array(self::BELONGS_TO, 'Company', 'company_id'),
			'customer' => array(self::BELONGS_TO, 'Customer', 'customer_id'),
			'contract' => array(self::BELONGS_TO, 'Contract', 'contract_id'),
			'agentAccount' => array(self::BELONGS_TO, 'Account', 'agent_account_id'),
			'calendarAppointment' => array(self::BELONGS_TO, 'CalendarAppointment', 'calendar_appointment_id'),
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
			'list_id' => 'List',
			'customer_id' => 'Customer',
			'agent_account_id' => 'Agent Account',
			'company_id' => 'Company',
			'contract_id' => 'Contract',
			'calendar_appointment_id' => 'Calendar Appointment',
			'disposition' => 'Disposition',
			'disposition_detail' => 'Disposition Detail',
			'phone_code' => 'Phone Code',
			'lead_phone_number' => 'Lead Phone Number',
			'agent_note' => 'Agent Note',
			'external_note' => 'External Note',
			'dial_number' => 'Dial Number',
			'type' => 'Type',
			'status' => 'Status',
			'start_call_time' => 'Start Call Time',
			'end_call_time' => 'End Call Time',
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
		$criteria->compare('list_id',$this->list_id);
		$criteria->compare('customer_id',$this->customer_id);
		$criteria->compare('agent_account_id',$this->agent_account_id);
		$criteria->compare('company_id',$this->company_id);
		$criteria->compare('contract_id',$this->contract_id);
		$criteria->compare('calendar_appointment_id',$this->calendar_appointment_id);
		$criteria->compare('disposition',$this->disposition,true);
		$criteria->compare('disposition_detail',$this->disposition_detail,true);
		$criteria->compare('phone_code',$this->phone_code,true);
		$criteria->compare('lead_phone_number',$this->lead_phone_number,true);
		$criteria->compare('agent_note',$this->agent_note,true);
		$criteria->compare('external_note',$this->external_note,true);
		$criteria->compare('dial_number',$this->dial_number);
		$criteria->compare('type',$this->type);
		$criteria->compare('status',$this->status);
		$criteria->compare('start_call_time',$this->start_call_time,true);
		$criteria->compare('end_call_time',$this->end_call_time,true);
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
	 * @return LeadCallHistory the static model class
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

	public function getReplacementCodeValues($customString='')
	{
		$string = '';
		
		if( $customString == '' )
		{
			if( $this->is_skill_child == 0 ) 
			{
				if( isset($this->skillDisposition) )
				{
					$string = $this->skillDisposition->html_header;
					
					$string .= $this->skillDisposition->html_body;
					
					$string .= $this->skillDisposition->html_footer;
				}
			}
			else
			{
				if( isset($this->skillChildDisposition) )
				{
					$string = $this->skillChildDisposition->html_header;
					
					$string .= $this->skillChildDisposition->html_body;
					
					$string .= $this->skillChildDisposition->html_footer;
				}
				else
				{
					$skillChildDisposition = SkillChildDisposition::model()->findByPk($this->disposition_id);
					
					if( $skillChildDisposition )
					{
						$string = $skillChildDisposition->html_header;
						
						$string .= $skillChildDisposition->html_body;
						
						$string .= $skillChildDisposition->html_footer;
					}
				}
			}
		}
		else
		{
			$string = $customString;
		}
		
		if( $string != '' )
		{			
			//replace img src
			preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $string, $matches);
			
			if( isset($matches[1]) )
			{
				$string = str_replace($matches[1], ' http://system.engagexapp.com' . $matches[1], $string);
			}
			
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
						case '[email_address]': 		$string = str_replace($match, !empty($this->lead->email_address) ? $this->lead->email_address : '', $string); break;
						
						
						//customer
						case '[customer_first_name]': $string = str_replace($match, !empty($this->customer->firstname) ? $this->customer->firstname : '', $string); break;
						case '[customer_last_name]':  $string = str_replace($match, !empty($this->customer->lastname) ? $this->customer->lastname : '', $string); break;
						case '[customer_phone]':  $string = str_replace($match, !empty($this->customer->phone) ? $this->customer->phone : '', $string); break;
						
						
						//calendar
						case '[calendar_name]': 	
							
							if( $this->calendar_appointment_id != null )
							{
								$calendarAppointment = CalendarAppointment::model()->findByPk($this->calendar_appointment_id);
								
								$string = str_replace($match, !empty($calendarAppointment) ? $calendarAppointment->calendar->name : '', $string); break;
							}
						break;
						
						case '[office_assigned_to_calendar]': 	
							
							if( $this->calendar_appointment_id != null )
							{
								$calendarAppointment = CalendarAppointment::model()->findByPk($this->calendar_appointment_id);
								
								$string = str_replace($match, !empty($calendarAppointment) ? $calendarAppointment->calendar->office->office_name : '', $string); break;
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
									$string = str_replace($match, !empty($calendarStaffAssignment) ? $calendarStaffAssignment->staff->staff_name : '', $string); break;
								}
							}
						break;
						
						
						//calendar appoint
						case '[appointment_location]': 	
							
							if( $this->calendar_appointment_id != null )
							{
								$calendarAppointment = CalendarAppointment::model()->findByPk($this->calendar_appointment_id);
								
								if( !empty($calendarAppointment) )
								{
									if(  $calendarAppointment->location == 1 )
									{
										$appointmentLocation = 'Office';
									}
									
									if(  $calendarAppointment->location == 2 )
									{
										$appointmentLocation = 'Home';
									}
									
									if(  $calendarAppointment->location == 3 )
									{
										$appointmentLocation = 'Phone';
									}
									
									if(  $calendarAppointment->location == 4 )
									{
										$appointmentLocation = 'Skype';
									}
								}
								
								$string = str_replace($match, $appointmentLocation, $string); break;
							}
							
						break;
						
						case '[appointment_date]': 	
							
							if( $this->calendar_appointment_id != null )
							{
								$calendarAppointment = CalendarAppointment::model()->findByPk($this->calendar_appointment_id);
								
								$string = str_replace($match, !empty($calendarAppointment->start_date) ? date('m/d/Y', strtotime($calendarAppointment->start_date)) : '', $string); break;
							}
							
						break;
						
						case '[appointment_time]': 	
							
							if( $this->calendar_appointment_id != null )
							{
								$calendarAppointment = CalendarAppointment::model()->findByPk($this->calendar_appointment_id);
								
								$string = str_replace($match, !empty($calendarAppointment->start_date) ? date('g:i A', strtotime($calendarAppointment->start_date)) : '', $string); break;
							}
							
						break;
						
						case '[changed_appointment_date]': 	
							
							if( $this->calendar_appointment_id != null )
							{
								$calendarAppointment = CalendarAppointment::model()->findByPk($this->calendar_appointment_id);
								
								$string = str_replace($match, !empty($calendarAppointment->previous_start_date) ? date('m/d/Y', strtotime($calendarAppointment->previous_start_date)) : '', $string); break;
							}
							
						break;
						
						case '[changed_appointment_time]': 	
							
							if( $this->calendar_appointment_id != null )
							{
								$calendarAppointment = CalendarAppointment::model()->findByPk($this->calendar_appointment_id);
								
								$string = str_replace($match, !empty($calendarAppointment->previous_start_date) ? date('g:i A', strtotime($calendarAppointment->previous_start_date)) : '', $string); break;
							}
							
						break;
						
						case '[agent_dispo_note]': 	

							$string = str_replace($match, !empty($this->agent_note) ? $this->agent_note : '', $string); break;
							
						break;
						
						case '[dialed_number]': 	

							$string = str_replace($match, !empty($this->lead_phone_number) ? $this->lead_phone_number : '', $string); break;
							
						break;
						
						case '[dialed_number_last_4_digits]': 	

							$string = str_replace($match, !empty($this->lead_phone_number) ? substr($this->lead_phone_number, -4) : '', $string); break;
							
						break;
						
						case '[ics_file_link]': 	
						
						if( $this->calendar_appointment_id != null )
						{
							$htmlICSLink = '';
							
							$calendarAppointment = CalendarAppointment::model()->findByPk($this->calendar_appointment_id);
							
							if($calendarAppointment !== null)
							{
								
								$icsLink = 'http://portal.engagexapp.com/index.php/site/DownloadICS?calendarAppointmentId='.$calendarAppointment->id;
								$icsFileText = 'Click here to Add to Outlook';
								
								$htmlICSLink = '<table width="100%" border="0" cellspacing="0" cellpadding="0">
								  <tr>
									<td>
									  <div>
										<!--[if mso]>
										  <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="'.$icsLink.'" style="height:36px;v-text-anchor:middle;width:350px;" arcsize="5%" strokecolor="#0068B1" fillcolor="#0068B1">
											<w:anchorlock/>
											<center style="color:#ffffff;font-family:Helvetica, Arial,sans-serif;font-size:16px;">'.$icsFileText.'</center>
										  </v:roundrect>
										<![endif]-->
										<a href="'.$icsLink.'" style="background-color:#0068B1;border:1px solid #0068B1;border-radius:3px;color:#ffffff;display:inline-block;font-family:sans-serif;font-size:16px;line-height:44px;text-align:center;text-decoration:none;width:220px;-webkit-text-size-adjust:none;mso-hide:all;">'.$icsFileText.'</a>
									  </div>
									</td>
								  </tr>
								</table>';
								
								$string = str_replace($match, $htmlICSLink, $string); 
							}
						}
						
						break;
						
						case '[my_portal_login_button]':
						
						$htmlMyPortalButton = '
							<table cellspacing="0" cellpadding="0" border="0" width="100%">
									<tbody>
									<tr>
										<td align="right">
											<p>
												<a href="http://portal.engagexapp.com" style="background-color:#0068B1;border:1px solid #0068B1;border-radius:3px;color:#ffffff;display:inline-block;font-family:sans-serif;font-size:16px;line-height:44px;text-align:center;text-decoration:none;width:150px;-webkit-text-size-adjust:none;mso-hide:all;">My Portal Login</a>
											</p><br>
										</td>
									</tr>
								</tbody>
							</table>
						';
						
						$string = str_replace($match, $htmlMyPortalButton, $string); 
						
						break;
						
						case '[ics_file_link_non_html]': 	
						
						if( $this->calendar_appointment_id != null )
						{
							$calendarAppointment = CalendarAppointment::model()->findByPk($this->calendar_appointment_id);
							
							if($calendarAppointment !== null)
							{
								$icsLink = 'http://portal.engagexapp.com/index.php/site/DownloadICS?calendarAppointmentId='.$calendarAppointment->id;
								
								$icsLink = preg_replace('#^https?://#', '', rtrim( file_get_contents('http://tinyurl.com/api-create.php?url='.$icsLink) ));
								
								$string = str_replace($match, $icsLink, $string); 
							}
						}
						
						case '[my_portal_login_button_non_html]': 	
						
						$link = preg_replace('#^https?://#', '', rtrim( file_get_contents('http://tinyurl.com/api-create.php?url=http://portal.engagexapp.com') ));
						
						$string = str_replace($match, $link, $string); 
					
						break;
						
						case '[agent_dispo_note_sms]': 	
						
							$previewLink = 'http://portal.engagexapp.com/index.php/smsView/index/id/'.$this->id;
							
							$previewLink = preg_replace('#^https?://#', '', rtrim( file_get_contents('http://tinyurl.com/api-create.php?url='.$previewLink) ));
							
							$string = str_replace($match, $previewLink, $string); 
							
						break;
						
						case '[customer_reply_link_sms]': 	
						
							$replyLink = 'http://portal.engagexapp.com/index.php/smsView/reply/id/'.$this->id;
							
							$replyLink = preg_replace('#^https?://#', '', rtrim( file_get_contents('http://tinyurl.com/api-create.php?url='.$replyLink) ));
							
							$string = str_replace($match, $replyLink, $string); 
							
						break;
						
						case '[sub_disposition_name]': 	
						
							$string = str_replace($match, !empty($this->disposition_detail) ? $this->disposition_detail : '', $string); break;
							
						break;
						
						case '[sub_disposition_note]': 	
						
							$string = str_replace($match, !empty($this->skillDispositionDetail->external_notes) ? $this->skillDispositionDetail->external_notes : '', $string); break;
							
						break;
						
						case (strpos($match, 'DTAB') !== false):

							$field_name = trim($match, '[]');

							$leadCustomData = LeadCustomData::model()->find(array(
								'condition' => '
									lead_id = :lead_id
									AND field_name = :field_name
								',
								'params' => array(
									':lead_id' => $this->lead_id,
									':field_name' => $field_name
								)
							));
							
							if( $leadCustomData )
							{
								$string = str_replace($match, !empty($leadCustomData->value) ? $leadCustomData->value : '', $string); break;
							}
												
						break;
						
						default: $string = str_replace($match, '', $string);
					}
				}
			}
		}
		
		return $string;
	}
}
