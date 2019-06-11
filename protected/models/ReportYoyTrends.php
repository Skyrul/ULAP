<?php

/**
 * This is the model class for table "{{report_yoy_trends}}".
 *
 * The followings are the available columns in table '{{report_yoy_trends}}':
 * @property integer $id
 * @property string $report_date
 * @property integer $sales
 * @property integer $cancels
 * @property integer $appointments
 * @property integer $dials
 */
class ReportYoyTrends extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{report_yoy_trends}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('sales, cancels, appointments, dials', 'numerical', 'integerOnly'=>true),
			array('report_date', 'length', 'max'=>24),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, report_date, sales, cancels, appointments, dials', 'safe', 'on'=>'search'),
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
			'report_date' => 'Report Date',
			'sales' => 'Sales',
			'cancels' => 'Cancels',
			'appointments' => 'Appointments',
			'dials' => 'Dials',
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
		$criteria->compare('report_date',$this->report_date,true);
		$criteria->compare('sales',$this->sales);
		$criteria->compare('cancels',$this->cancels);
		$criteria->compare('appointments',$this->appointments);
		$criteria->compare('dials',$this->dials);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ReportYoyTrends the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
