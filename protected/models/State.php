<?php

/**
 * This is the model class for table "{{state}}".
 *
 * The followings are the available columns in table '{{state}}':
 * @property integer $id
 * @property string $name
 * @property string $abbreviation
 * @property string $country
 * @property string $type
 * @property integer $sort
 * @property string $status
 * @property string $occupied
 * @property string $notes
 * @property string $fips_state
 * @property string $assoc_press
 * @property string $standard_federal_region
 * @property string $census_region
 * @property string $census_region_name
 * @property string $census_division
 * @property string $census_division_name
 * @property string $circuit_court
 *
 * The followings are the available model relations:
 * @property Customer[] $customers
 * @property CustomerOffice[] $customerOffices
 */
class State extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{state}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id, sort', 'numerical', 'integerOnly'=>true),
			array('name, abbreviation, country, type, status, occupied, notes, fips_state, assoc_press, standard_federal_region, census_region, census_region_name, census_division, census_division_name, circuit_court', 'length', 'max'=>255),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, name, abbreviation, country, type, sort, status, occupied, notes, fips_state, assoc_press, standard_federal_region, census_region, census_region_name, census_division, census_division_name, circuit_court', 'safe', 'on'=>'search'),
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
			'customers' => array(self::HAS_MANY, 'Customer', 'state'),
			'customerOffices' => array(self::HAS_MANY, 'CustomerOffice', 'state'),
			'stateSchedules' => array(self::HAS_MANY, 'StateSchedule', 'state_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'name' => 'State',
			'abbreviation' => 'Abbreviation',
			'country' => 'Country',
			'type' => 'Type',
			'sort' => 'Sort',
			'status' => 'Status',
			'occupied' => 'Occupied',
			'notes' => 'Notes',
			'fips_state' => 'Fips State',
			'assoc_press' => 'Assoc Press',
			'standard_federal_region' => 'Standard Federal Region',
			'census_region' => 'Census Region',
			'census_region_name' => 'Census Region Name',
			'census_division' => 'Census Division',
			'census_division_name' => 'Census Division Name',
			'circuit_court' => 'Circuit Court',
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
		$criteria->compare('name',$this->name,true);
		$criteria->compare('abbreviation',$this->abbreviation,true);
		$criteria->compare('country',$this->country,true);
		$criteria->compare('type',$this->type,true);
		$criteria->compare('sort',$this->sort);
		$criteria->compare('status',$this->status,true);
		$criteria->compare('occupied',$this->occupied,true);
		$criteria->compare('notes',$this->notes,true);
		$criteria->compare('fips_state',$this->fips_state,true);
		$criteria->compare('assoc_press',$this->assoc_press,true);
		$criteria->compare('standard_federal_region',$this->standard_federal_region,true);
		$criteria->compare('census_region',$this->census_region,true);
		$criteria->compare('census_region_name',$this->census_region_name,true);
		$criteria->compare('census_division',$this->census_division,true);
		$criteria->compare('census_division_name',$this->census_division_name,true);
		$criteria->compare('circuit_court',$this->circuit_court,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return State the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public static $listStates = null;
	public static function listStates($type='')
	{
		if(self::$listStates === null)
		{
			if( $type == 'name' )
			{
				self::$listStates = CHtml::listData(State::model()->findAll(),'name','name');
			}
			else
			{
				self::$listStates = CHtml::listData(State::model()->findAll(),'id','name');	
			}
		}
		
		return self::$listStates;
	}
	
	public function afterFind()
	{
		$this->stateSchedulesArray = $this->getStateSchedulesArray();
		return parent::afterFind();
	}
	
	public $stateSchedulesArray = array();
	
	public function getStateSchedulesArray()
	{
		if(empty($this->stateSchedulesArray))
		{
			foreach($this->stateSchedules as $stateSchedule)
			{
				$this->stateSchedulesArray[$stateSchedule->schedule_day][$stateSchedule->id]['schedule_start'] = $stateSchedule->schedule_start;
				$this->stateSchedulesArray[$stateSchedule->schedule_day][$stateSchedule->id]['schedule_end'] = $stateSchedule->schedule_end;
				$this->stateSchedulesArray[$stateSchedule->schedule_day][$stateSchedule->id]['status'] = $stateSchedule->status;
				$this->stateSchedulesArray[$stateSchedule->schedule_day][$stateSchedule->id]['id'] = $stateSchedule->id;
			}
		}
		
		return $this->stateSchedulesArray;
	}
}
