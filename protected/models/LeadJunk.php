<?php

/**
 * This is the model class for table "{{lead}}".
 *
 * The followings are the available columns in table '{{lead}}':
 * @property integer $id
 * @property integer $list_id
 * @property string $first_name
 * @property string $last_name
 * @property string $partner_first_name
 * @property string $partner_last_name
 * @property string $home_phone_label
 * @property string $home_phone_number
 * @property string $home_phone_disposition
 * @property string $home_phone_disposition_detail
 * @property integer $home_phone_dial_count
 * @property string $mobile_phone_label
 * @property string $mobile_phone_number
 * @property string $mobile_phone_disposition
 * @property string $mobile_phone_disposition_detail
 * @property integer $mobile_phone_dial_count
 * @property string $office_phone_label
 * @property string $office_phone_number
 * @property string $office_phone_disposition
 * @property string $office_phone_disposition_detail
 * @property integer $office_phone_dial_count
 * @property string $email_address
 * @property string $address
 * @property string $address2
 * @property string $address3
 * @property string $city
 * @property string $state
 * @property string $zip_code
 * @property string $language
 * @property string $agent_name
 * @property string $custom_date
 * @property integer $number_of_dials
 * @property string $last_call_date
 * @property integer $type
 * @property integer $status
 * @property string $date_created
 * @property string $date_updated
 */
class LeadJunk extends AuditCActiveRecord
{
	public $ctr;
	//type = 1 , valid number 
	//type = 2, invalid/bad number
	
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{lead_junk}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('list_id, customer_id, home_phone_dial_count, mobile_phone_dial_count, office_phone_dial_count, number_of_dials, recycle_lead_call_history_id, recycle_lead_call_history_disposition_id, type, status', 'numerical', 'integerOnly'=>true),
			array('first_name, last_name, partner_first_name, partner_last_name, home_phone_label, home_phone_number, home_phone_disposition, home_phone_disposition_detail, mobile_phone_label, mobile_phone_number, mobile_phone_disposition, mobile_phone_disposition_detail, office_phone_label, office_phone_number, office_phone_disposition, office_phone_disposition_detail, email_address, address, address2, address3, city, state, zip_code, language, agent_name, custom_date', 'length', 'max'=>255),
			
			array('import_client_primary_key', 'unique'),
			array('import_client_primary_key', 'length', 'max'=>60),
			array('is_imported, is_duplicate, is_bad_number', 'numerical', 'integerOnly'=>true),
			
			array('last_call_date, timezone, recycle_date, recertify_date, date_created, date_updated', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, list_id, customer_id, first_name, last_name, partner_first_name, partner_last_name, home_phone_label, home_phone_number, home_phone_disposition, home_phone_disposition_detail, home_phone_dial_count, mobile_phone_label, mobile_phone_number, mobile_phone_disposition, mobile_phone_disposition_detail, mobile_phone_dial_count, office_phone_label, office_phone_number, office_phone_disposition, office_phone_disposition_detail, office_phone_dial_count, email_address, address, address2, address3, city, state, zip_code, language, agent_name, custom_date, number_of_dials, last_call_date, timezone, type, status, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'list' => array(self::BELONGS_TO, 'Lists', 'list_id'),
			'customer' => array(self::BELONGS_TO, 'Customer', 'customer_id'),
			'recycleLeadCallHistoryDisposition' => array(self::BELONGS_TO, 'SkillDisposition', 'recycle_lead_call_history_disposition_id'),
			'latestCompletedCallHistory' => array(
				self::HAS_ONE, 'LeadCallHistory', 'lead_id', 
				'condition' =>'latestCompletedCallHistory.disposition IN ("Client Complete", "Do Not Call")', 
				'order' => 'latestCompletedCallHistory.id DESC'
			),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'list_id' => 'List',
			'first_name' => 'First Name',
			'last_name' => 'Last Name',
			'partner_first_name' => 'Partner First Name',
			'partner_last_name' => 'Partner Last Name',
			'home_phone_label' => 'Phone Description',
			'home_phone_number' => 'Home Phone Number',
			'home_phone_disposition' => 'Home Phone Disposition',
			'home_phone_disposition_detail' => 'Home Phone Disposition Detail',
			'home_phone_dial_count' => 'Home Phone Dial Count',
			'mobile_phone_label' => 'Phone Description',
			'mobile_phone_number' => 'Mobile Phone Number',
			'mobile_phone_disposition' => 'Mobile Phone Disposition',
			'mobile_phone_disposition_detail' => 'Mobile Phone Disposition Detail',
			'mobile_phone_dial_count' => 'Mobile Phone Dial Count',
			'office_phone_label' => 'Phone Description',
			'office_phone_number' => 'Office Phone Number',
			'office_phone_disposition' => 'Office Phone Disposition',
			'office_phone_disposition_detail' => 'Office Phone Disposition Detail',
			'office_phone_dial_count' => 'Office Phone Dial Count',
			'email_address' => 'Email Address',
			'address' => 'Address',
			'address2' => 'Address2',
			'address3' => 'Address3',
			'city' => 'City',
			'state' => 'State',
			'zip_code' => 'Zip Code',
			'language' => 'Language',
			'agent_name' => 'Agent Name',
			'custom_date' => 'Custom Date',
			'number_of_dials' => 'Number Of Dials',
			'last_call_date' => 'Last Call Date',
			'type' => 'Type',
			'timezone' => 'timezone',
			'recycle_date' => 'Recycle Date',
			'recycle_lead_call_history_id' => 'Recycle Lead Call History',
			'recycle_lead_call_history_disposition_id' => 'Recycle Lead Call History Disposition',
			'recertify_date' => 'Recertify Date',
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
		$criteria->compare('list_id',$this->list_id);
		$criteria->compare('first_name',$this->first_name,true);
		$criteria->compare('last_name',$this->last_name,true);
		$criteria->compare('partner_first_name',$this->partner_first_name,true);
		$criteria->compare('partner_last_name',$this->partner_last_name,true);
		$criteria->compare('home_phone_label',$this->home_phone_label,true);
		$criteria->compare('home_phone_number',$this->home_phone_number,true);
		$criteria->compare('home_phone_disposition',$this->home_phone_disposition,true);
		$criteria->compare('home_phone_disposition_detail',$this->home_phone_disposition_detail,true);
		$criteria->compare('home_phone_dial_count',$this->home_phone_dial_count);
		$criteria->compare('mobile_phone_label',$this->mobile_phone_label,true);
		$criteria->compare('mobile_phone_number',$this->mobile_phone_number,true);
		$criteria->compare('mobile_phone_disposition',$this->mobile_phone_disposition,true);
		$criteria->compare('mobile_phone_disposition_detail',$this->mobile_phone_disposition_detail,true);
		$criteria->compare('mobile_phone_dial_count',$this->mobile_phone_dial_count);
		$criteria->compare('office_phone_label',$this->office_phone_label,true);
		$criteria->compare('office_phone_number',$this->office_phone_number,true);
		$criteria->compare('office_phone_disposition',$this->office_phone_disposition,true);
		$criteria->compare('office_phone_disposition_detail',$this->office_phone_disposition_detail,true);
		$criteria->compare('office_phone_dial_count',$this->office_phone_dial_count);
		$criteria->compare('email_address',$this->email_address,true);
		$criteria->compare('address',$this->address,true);
		$criteria->compare('address2',$this->address2,true);
		$criteria->compare('address3',$this->address3,true);
		$criteria->compare('city',$this->city,true);
		$criteria->compare('state',$this->state,true);
		$criteria->compare('zip_code',$this->zip_code,true);
		$criteria->compare('language',$this->language,true);
		$criteria->compare('agent_name',$this->agent_name,true);
		$criteria->compare('custom_date',$this->custom_date,true);
		$criteria->compare('number_of_dials',$this->number_of_dials);
		$criteria->compare('last_call_date',$this->last_call_date,true);
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
	 * @return Lead the static model class
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
	
	
	public static function items($calendar_id=null)
	{
		$items = array();
	
		if( $calendar_id != null )
		{
			$models = LeadJunk::model()->findAll(array(
				'with' => 'list',
				'together' => true,
				'condition' => 'list.calendar_id = :calendar_id AND t.type=1',
				'params' => array(
					':calendar_id' => $calendar_id,
				),
				'order' => 'first_name ASC',
			));
		}
		else
		{
			$models = LeadJunk::model()->findAll(array(
				'condition' => 'type=1',
				'order' => 'first_name ASC',
			));
		}
		
		if($models)
		{
			foreach($models as $model)
			{
				$items[$model->id] = $model->first_name.' '.$model->last_name;
			}
		}
		
		return $items;
	}
	
	public static function items2($customer_id)
	{
		$items = array();
	
		$models = LeadJunk::model()->findAll(array(
			'with' => 'list',
			'together' => true,
			'condition' => 't.customer_id = :customer_id AND t.type=1 AND t.list_id IS NOT NULL',
			'params' => array(
				':customer_id' => $customer_id,
			),
			'order' => 'first_name ASC',
		));
		
		if($models)
		{
			foreach($models as $model)
			{
				$items[$model->id] = $model->first_name.' '.$model->last_name;
			}
		}
		
		return $items;
	}
	
	public function getFullName()
	{
		return $this->first_name.' '.$this->last_name;
	}
	
	public function getPartnerFullName()
	{
		return $this->partner_first_name.' '.$this->partner_last_name;
	}
	
	public function getStatus()
	{
		if( $this->status == 1 )
		{
			$status = 'Active';
		}
		elseif(  $this->status == 2 )
		{
			$status = 'Inactive';
		}
		else
		{
			$status = 'Completed';
		}
		
		return $status;
	}
	
	public static function statusOptions()
	{
		return array(
			1 => 'Active',
			// 2 => 'Inactive',
			3 => 'Completed',
		);
	}
	
	protected function afterSave()
	{
		if(!$this->isNewRecord)
		{
			##add customer history IF
			#lead name is changed
			#lead phone number is changed or added
			#lead partner changed or added
			#lead is deleted/removed
			$historyHolder = '';
			
			if($this->oldAttributes['first_name'] != $this->newAttributes['first_name'])
			{
				$historyHolder[] = self::model()->getAttributeLabel('first_name').' changed from '.$this->oldAttributes['first_name'].' to '.$this->newAttributes['first_name'];
			}
			
			if($this->oldAttributes['last_name'] != $this->newAttributes['last_name'])
			{
				$historyHolder[] = self::model()->getAttributeLabel('last_name').' changed from '.$this->oldAttributes['last_name'].' to '.$this->newAttributes['last_name'];
			}
			
			if($this->oldAttributes['partner_first_name'] != $this->newAttributes['partner_first_name'])
			{
				$historyHolder[] = self::model()->getAttributeLabel('partner_first_name').' changed from '.$this->oldAttributes['partner_first_name'].' to '.$this->newAttributes['partner_first_name'];
			}
			
			if($this->oldAttributes['partner_last_name'] != $this->newAttributes['partner_last_name'])
			{
				$historyHolder[] = self::model()->getAttributeLabel('partner_last_name').' changed from '.$this->oldAttributes['partner_last_name'].' to '.$this->newAttributes['partner_last_name'];
			}
			
			if($this->oldAttributes['office_phone_number'] != $this->newAttributes['office_phone_number'])
			{
				$historyHolder[] = self::model()->getAttributeLabel('office_phone_number').' changed from '.$this->oldAttributes['office_phone_number'].' to '.$this->newAttributes['office_phone_number'];
			}
			
			if($this->oldAttributes['mobile_phone_number'] != $this->newAttributes['mobile_phone_number'])
			{
				$historyHolder[] = self::model()->getAttributeLabel('mobile_phone_number').' changed from '.$this->oldAttributes['mobile_phone_number'].' to '.$this->newAttributes['mobile_phone_number'];
			}
			
			if($this->oldAttributes['home_phone_number'] != $this->newAttributes['home_phone_number'])
			{
				$historyHolder[] = self::model()->getAttributeLabel('home_phone_number').' changed from '.$this->oldAttributes['home_phone_number'].' to '.$this->newAttributes['home_phone_number'];
			}
			
			if($this->oldAttributes['address'] != $this->newAttributes['address'])
			{
				$historyHolder[] = self::model()->getAttributeLabel('address').' changed from '.$this->oldAttributes['address'].' to '.$this->newAttributes['address'];
			}
			
			if($this->oldAttributes['language'] != $this->newAttributes['language'])
			{
				$historyHolder[] = self::model()->getAttributeLabel('language').' changed from '.$this->oldAttributes['language'].' to '.$this->newAttributes['language'];
			}
			
			if($this->oldAttributes['email_address'] != $this->newAttributes['email_address'])
			{
				$historyHolder[] = self::model()->getAttributeLabel('email_address').' changed from '.$this->oldAttributes['email_address'].' to '.$this->newAttributes['email_address'];
			}
			
			if($this->oldAttributes['timezone'] != $this->newAttributes['timezone'])
			{
				$historyHolder[] = self::model()->getAttributeLabel('timezone').' changed from '.$this->oldAttributes['timezone'].' to '.$this->newAttributes['timezone'];
			}
			
			
			if(!empty($historyHolder))
			{
				$historyString = implode(', ',$historyHolder); 
				$history = new LeadHistory;
				$history->setAttributes(array(
					'content' => $this->getFullName().' | '.$historyString, 
					'lead_id' => $this->id,
					'agent_account_id' => Yii::app()->user->id,
					'old_data' => json_encode($this->oldAttributes),
					'new_data' => json_encode($this->newAttributes),
					'type' => 6,
				));
				
				$history->save(false);
			}		
		}
		
		return parent::afterSave();
	}
}
