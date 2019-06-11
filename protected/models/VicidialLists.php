<?php

/**
 * This is the model class for table "{{lists}}".
 *
 * The followings are the available columns in table '{{lists}}':
 * @property string $list_id
 * @property string $list_name
 * @property string $campaign_id
 * @property string $active
 * @property string $list_description
 * @property string $list_changedate
 * @property string $list_lastcalldate
 * @property string $reset_time
 * @property string $agent_script_override
 * @property string $campaign_cid_override
 * @property string $am_message_exten_override
 * @property string $drop_inbound_group_override
 * @property string $xferconf_a_number
 * @property string $xferconf_b_number
 * @property string $xferconf_c_number
 * @property string $xferconf_d_number
 * @property string $xferconf_e_number
 * @property string $web_form_address
 * @property string $web_form_address_two
 * @property string $time_zone_setting
 * @property string $inventory_report
 * @property string $expiration_date
 */
class VicidialLists extends MyActiveRecord
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
		return '{{lists}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('list_id', 'required'),
			array('list_id', 'length', 'max'=>14),
			array('list_name', 'length', 'max'=>30),
			array('campaign_id', 'length', 'max'=>8),
			array('active, inventory_report', 'length', 'max'=>1),
			array('list_description', 'length', 'max'=>255),
			array('reset_time, am_message_exten_override', 'length', 'max'=>100),
			array('agent_script_override', 'length', 'max'=>10),
			array('campaign_cid_override, drop_inbound_group_override', 'length', 'max'=>20),
			array('xferconf_a_number, xferconf_b_number, xferconf_c_number, xferconf_d_number, xferconf_e_number', 'length', 'max'=>50),
			array('time_zone_setting', 'length', 'max'=>21),
			array('list_changedate, list_lastcalldate, web_form_address, web_form_address_two, expiration_date', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('list_id, list_name, campaign_id, active, list_description, list_changedate, list_lastcalldate, reset_time, agent_script_override, campaign_cid_override, am_message_exten_override, drop_inbound_group_override, xferconf_a_number, xferconf_b_number, xferconf_c_number, xferconf_d_number, xferconf_e_number, web_form_address, web_form_address_two, time_zone_setting, inventory_report, expiration_date', 'safe', 'on'=>'search'),
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
			'emailConfiguration' => array(self::HAS_ONE, 'EmailConfiguration', 'list_id'),
			'campaign' => array(self::BELONGS_TO, 'VicidialCampaigns', 'campaign_id')
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'list_id' => 'List',
			'list_name' => 'List Name',
			'campaign_id' => 'Campaign',
			'active' => 'Active',
			'list_description' => 'List Description',
			'list_changedate' => 'List Changedate',
			'list_lastcalldate' => 'List Lastcalldate',
			'reset_time' => 'Reset Time',
			'agent_script_override' => 'Agent Script Override',
			'campaign_cid_override' => 'Campaign Cid Override',
			'am_message_exten_override' => 'Am Message Exten Override',
			'drop_inbound_group_override' => 'Drop Inbound Group Override',
			'xferconf_a_number' => 'Xferconf A Number',
			'xferconf_b_number' => 'Xferconf B Number',
			'xferconf_c_number' => 'Xferconf C Number',
			'xferconf_d_number' => 'Xferconf D Number',
			'xferconf_e_number' => 'Xferconf E Number',
			'web_form_address' => 'Web Form Address',
			'web_form_address_two' => 'Web Form Address Two',
			'time_zone_setting' => 'Time Zone Setting',
			'inventory_report' => 'Inventory Report',
			'expiration_date' => 'Expiration Date',
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

		$criteria->compare('list_id',$this->list_id,true);
		$criteria->compare('list_name',$this->list_name,true);
		$criteria->compare('campaign_id',$this->campaign_id,true);
		$criteria->compare('active',$this->active,true);
		$criteria->compare('list_description',$this->list_description,true);
		$criteria->compare('list_changedate',$this->list_changedate,true);
		$criteria->compare('list_lastcalldate',$this->list_lastcalldate,true);
		$criteria->compare('reset_time',$this->reset_time,true);
		$criteria->compare('agent_script_override',$this->agent_script_override,true);
		$criteria->compare('campaign_cid_override',$this->campaign_cid_override,true);
		$criteria->compare('am_message_exten_override',$this->am_message_exten_override,true);
		$criteria->compare('drop_inbound_group_override',$this->drop_inbound_group_override,true);
		$criteria->compare('xferconf_a_number',$this->xferconf_a_number,true);
		$criteria->compare('xferconf_b_number',$this->xferconf_b_number,true);
		$criteria->compare('xferconf_c_number',$this->xferconf_c_number,true);
		$criteria->compare('xferconf_d_number',$this->xferconf_d_number,true);
		$criteria->compare('xferconf_e_number',$this->xferconf_e_number,true);
		$criteria->compare('web_form_address',$this->web_form_address,true);
		$criteria->compare('web_form_address_two',$this->web_form_address_two,true);
		$criteria->compare('time_zone_setting',$this->time_zone_setting,true);
		$criteria->compare('inventory_report',$this->inventory_report,true);
		$criteria->compare('expiration_date',$this->expiration_date,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return VicidialLists the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
