<?php

/**
 * This is the model class for table "{{state_initial_scrub}}".
 *
 * The followings are the available columns in table '{{state_initial_scrub}}':
 * @property integer $id
 * @property integer $lead_id
 * @property string $home_phone_number
 * @property string $office_phone_number
 * @property string $mobile_phone_number
 * @property string $lead_phone_number
 * @property string $lead_phone_type
 * @property string $date_created
 */
class StateInitialScrub extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{state_initial_scrub}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('lead_id', 'numerical', 'integerOnly'=>true),
			array('home_phone_number, office_phone_number, mobile_phone_number, lead_phone_number, lead_phone_type', 'length', 'max'=>25),
			array('date_created', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, lead_id, home_phone_number, office_phone_number, mobile_phone_number, lead_phone_number, lead_phone_type, date_created', 'safe', 'on'=>'search'),
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
			'home_phone_number' => 'Home Phone Number',
			'office_phone_number' => 'Office Phone Number',
			'mobile_phone_number' => 'Mobile Phone Number',
			'lead_phone_number' => 'Lead Phone Number',
			'lead_phone_type' => 'Lead Phone Type',
			'date_created' => 'Date Created',
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
		$criteria->compare('home_phone_number',$this->home_phone_number,true);
		$criteria->compare('office_phone_number',$this->office_phone_number,true);
		$criteria->compare('mobile_phone_number',$this->mobile_phone_number,true);
		$criteria->compare('lead_phone_number',$this->lead_phone_number,true);
		$criteria->compare('lead_phone_type',$this->lead_phone_type,true);
		$criteria->compare('date_created',$this->date_created,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return StateInitialScrub the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
