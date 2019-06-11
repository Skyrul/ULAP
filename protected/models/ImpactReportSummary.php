<?php

/**
 * This is the model class for table "{{impact_report_summary}}".
 *
 * The followings are the available columns in table '{{impact_report_summary}}':
 * @property integer $id
 * @property string $field_name
 * @property string $month1
 * @property string $month2
 * @property string $month3
 * @property string $month4
 * @property string $month5
 * @property string $month6
 * @property string $month7
 * @property string $month8
 */
class ImpactReportSummary extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{impact_report_summary}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('field_name, month1, month2, month3, month4, month5, month6, month7, month8', 'length', 'max'=>255),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, field_name, month1, month2, month3, month4, month5, month6, month7, month8', 'safe', 'on'=>'search'),
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
			'field_name' => 'Field Name',
			'month1' => 'Month1',
			'month2' => 'Month2',
			'month3' => 'Month3',
			'month4' => 'Month4',
			'month5' => 'Month5',
			'month6' => 'Month6',
			'month7' => 'Month7',
			'month8' => 'Month8',
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
		$criteria->compare('field_name',$this->field_name,true);
		$criteria->compare('month1',$this->month1,true);
		$criteria->compare('month2',$this->month2,true);
		$criteria->compare('month3',$this->month3,true);
		$criteria->compare('month4',$this->month4,true);
		$criteria->compare('month5',$this->month5,true);
		$criteria->compare('month6',$this->month6,true);
		$criteria->compare('month7',$this->month7,true);
		$criteria->compare('month8',$this->month8,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ImpactReportSummary the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
