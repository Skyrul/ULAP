<?php

/**
 * This is the model class for table "{{list}}".
 *
 * The followings are the available columns in table '{{list}}':
 * @property string $lead_id
 * @property string $entry_date
 * @property string $modify_date
 * @property string $status
 * @property string $user
 * @property string $vendor_lead_code
 * @property string $source_id
 * @property string $list_id
 * @property string $gmt_offset_now
 * @property string $called_since_last_reset
 * @property string $phone_code
 * @property string $phone_number
 * @property string $title
 * @property string $first_name
 * @property string $middle_initial
 * @property string $last_name
 * @property string $address1
 * @property string $address2
 * @property string $address3
 * @property string $city
 * @property string $state
 * @property string $province
 * @property string $postal_code
 * @property string $country_code
 * @property string $gender
 * @property string $date_of_birth
 * @property string $alt_phone
 * @property string $email
 * @property string $security_phrase
 * @property string $comments
 * @property integer $called_count
 * @property string $last_local_call_time
 * @property integer $rank
 * @property string $owner
 * @property string $entry_list_id
 */
class VicidialList extends MyActiveRecord
{
	public function getDbConnection()
    {
        return self::getExternalDbConnection();
    }
	
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{list}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('modify_date, phone_number', 'required'),
			array('called_count, rank', 'numerical', 'integerOnly'=>true),
			array('status', 'length', 'max'=>6),
			array('user, vendor_lead_code, owner', 'length', 'max'=>20),
			array('source_id, city, province', 'length', 'max'=>50),
			array('list_id, entry_list_id', 'length', 'max'=>14),
			array('gmt_offset_now, title', 'length', 'max'=>4),
			array('called_since_last_reset, country_code', 'length', 'max'=>3),
			array('phone_code, postal_code', 'length', 'max'=>10),
			array('phone_number', 'length', 'max'=>18),
			array('first_name, last_name', 'length', 'max'=>30),
			array('middle_initial, gender', 'length', 'max'=>1),
			array('address1, address2, address3, security_phrase', 'length', 'max'=>100),
			array('state', 'length', 'max'=>2),
			array('alt_phone', 'length', 'max'=>12),
			array('email', 'length', 'max'=>70),
			array('comments', 'length', 'max'=>255),
			array('entry_date, date_of_birth, last_local_call_time', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('lead_id, entry_date, modify_date, status, user, vendor_lead_code, source_id, list_id, gmt_offset_now, called_since_last_reset, phone_code, phone_number, title, first_name, middle_initial, last_name, address1, address2, address3, city, state, province, postal_code, country_code, gender, date_of_birth, alt_phone, email, security_phrase, comments, called_count, last_local_call_time, rank, owner, entry_list_id', 'safe', 'on'=>'search'),
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
			'vicidialList' => array(self::BELONGS_TO, 'VicidialLists', 'list_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'lead_id' => 'Lead',
			'entry_date' => 'Entry Date',
			'modify_date' => 'Modify Date',
			'status' => 'Status',
			'user' => 'User',
			'vendor_lead_code' => 'Vendor Lead Code',
			'source_id' => 'Source',
			'list_id' => 'List',
			'gmt_offset_now' => 'Gmt Offset Now',
			'called_since_last_reset' => 'Called Since Last Reset',
			'phone_code' => 'Phone Code',
			'phone_number' => 'Phone Number',
			'title' => 'Title',
			'first_name' => 'First Name',
			'middle_initial' => 'Middle Initial',
			'last_name' => 'Last Name',
			'address1' => 'Address1',
			'address2' => 'Address2',
			'address3' => 'Address3',
			'city' => 'City',
			'state' => 'State',
			'province' => 'Province',
			'postal_code' => 'Postal Code',
			'country_code' => 'Country Code',
			'gender' => 'Gender',
			'date_of_birth' => 'Date Of Birth',
			'alt_phone' => 'Alt Phone',
			'email' => 'Email',
			'security_phrase' => 'Security Phrase',
			'comments' => 'Comments',
			'called_count' => 'Called Count',
			'last_local_call_time' => 'Last Local Call Time',
			'rank' => 'Rank',
			'owner' => 'Owner',
			'entry_list_id' => 'Entry List',
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

		$criteria->compare('lead_id',$this->lead_id,true);
		$criteria->compare('entry_date',$this->entry_date,true);
		$criteria->compare('modify_date',$this->modify_date,true);
		$criteria->compare('status',$this->status,true);
		$criteria->compare('user',$this->user,true);
		$criteria->compare('vendor_lead_code',$this->vendor_lead_code,true);
		$criteria->compare('source_id',$this->source_id,true);
		$criteria->compare('list_id',$this->list_id,true);
		$criteria->compare('gmt_offset_now',$this->gmt_offset_now,true);
		$criteria->compare('called_since_last_reset',$this->called_since_last_reset,true);
		$criteria->compare('phone_code',$this->phone_code,true);
		$criteria->compare('phone_number',$this->phone_number,true);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('first_name',$this->first_name,true);
		$criteria->compare('middle_initial',$this->middle_initial,true);
		$criteria->compare('last_name',$this->last_name,true);
		$criteria->compare('address1',$this->address1,true);
		$criteria->compare('address2',$this->address2,true);
		$criteria->compare('address3',$this->address3,true);
		$criteria->compare('city',$this->city,true);
		$criteria->compare('state',$this->state,true);
		$criteria->compare('province',$this->province,true);
		$criteria->compare('postal_code',$this->postal_code,true);
		$criteria->compare('country_code',$this->country_code,true);
		$criteria->compare('gender',$this->gender,true);
		$criteria->compare('date_of_birth',$this->date_of_birth,true);
		$criteria->compare('alt_phone',$this->alt_phone,true);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('security_phrase',$this->security_phrase,true);
		$criteria->compare('comments',$this->comments,true);
		$criteria->compare('called_count',$this->called_count);
		$criteria->compare('last_local_call_time',$this->last_local_call_time,true);
		$criteria->compare('rank',$this->rank);
		$criteria->compare('owner',$this->owner,true);
		$criteria->compare('entry_list_id',$this->entry_list_id,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return VicidialList the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function completeName()
	{
		return $this->title.' '.$this->first_name.' '.$this->middle_initial.' '.$this->last_name;
	}
}
