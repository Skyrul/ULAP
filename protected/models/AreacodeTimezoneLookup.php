<?php

/**
 * This is the model class for table "{{areacode_timezone_lookup}}".
 *
 * The followings are the available columns in table '{{areacode_timezone_lookup}}':
 * @property integer $id
 * @property string $areacode
 * @property string $timezone_abbr
 * @property string $city
 */
class AreacodeTimezoneLookup extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{areacode_timezone_lookup}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('areacode', 'length', 'max'=>10),
			array('timezone_abbr', 'length', 'max'=>25),
			array('city', 'length', 'max'=>50),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, areacode, timezone_abbr, city', 'safe', 'on'=>'search'),
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
			'areacode' => 'Areacode',
			'timezone_abbr' => 'Timezone Abbr',
			'city' => 'City',
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
		$criteria->compare('areacode',$this->areacode,true);
		$criteria->compare('timezone_abbr',$this->timezone_abbr,true);
		$criteria->compare('city',$this->city,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return AreacodeTimezoneLookup the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	
	public static function items()
	{
		$items = array();
		
		$models = self::model()->findAll(array(
			'condition' => 'timezone_abbr IS NOT NULL',
			'order' => 'timezone_abbr ASC',
		));
		
		if( $models )
		{
			foreach( $models as $model )
			{
				if( !in_array($model->timezone_abbr, $items) )
				{
					$timeZoneLabel = '';
					
					if( $model->timezone_abbr == 'EST' )
					{
						$timeZoneLabel = 'EASTERN STANDARD TIME';
					}
					
					if( $model->timezone_abbr == 'CST' )
					{
						$timeZoneLabel = 'CENTRAL STANDARD TIME';
					}
					
					if( $model->timezone_abbr == 'PST' )
					{
						$timeZoneLabel = 'PACIFIC STANDARD TIME';
					}
					
					if( $model->timezone_abbr == 'MST' )
					{
						$timeZoneLabel = 'MOUNTAIN STANDARD TIME';
					}
					
					if( $model->timezone_abbr == 'HAST' )
					{
						$timeZoneLabel = 'HAWAII-ALEUTIAN STANDARD TIME';
					}
					
					if( $model->timezone_abbr == 'AST' )
					{
						$timeZoneLabel = 'ATLANTIC STANDARD TIME';
					}
					
					if( $model->timezone_abbr == 'AKST' )
					{
						$timeZoneLabel = 'ALASKA STANDARD TIME';
					}
					
					$items[$model->timezone_abbr] = $timeZoneLabel;
				}
			}
		}
		
		return $items;
	}
	
	
	public static function getAreaCodeTimeZone($phoneNumber, $toSubStr = true)
	{
		if($toSubStr)
			$_phoneNumber = substr($phoneNumber, 0, 3);
		else
			$_phoneNumber = $phoneNumber;
		
		$model = self::model()->find(array(
			'condition' => 'areacode = :areacode',
			'params' => array(
				':areacode' => $_phoneNumber,
			),
		));
		
		if( $model )
		{
			return $model->timezone_abbr;
		}
		else
		{
			return null;
		}
	}
}
