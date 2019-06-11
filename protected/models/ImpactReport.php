<?php

/**
 * This is the model class for table "{{impact_report}}".
 *
 * The followings are the available columns in table '{{impact_report}}':
 * @property integer $id
 * @property string $month_name
 * @property string $month_date
 * @property double $actual
 * @property double $actual_credit_card
 * @property double $actual_subsidy
 * @property double $projected
 * @property double $projected_credit_card
 * @property double $projected_subsidy
 */
class ImpactReport extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{impact_report}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('actual, actual_credit_card, actual_subsidy, projected, projected_credit_card, projected_subsidy', 'numerical'),
			array('month_name, month_date', 'length', 'max'=>255),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, month_name, month_date, actual, actual_credit_card, actual_subsidy, projected, projected_credit_card, projected_subsidy', 'safe', 'on'=>'search'),
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
			'month_name' => 'Month Name',
			'month_date' => 'Month Date',
			'actual' => 'Actual',
			'actual_credit_card' => 'Actual Credit Card',
			'actual_subsidy' => 'Actual Subsidy',
			'projected' => 'Projected',
			'projected_credit_card' => 'Projected Credit Card',
			'projected_subsidy' => 'Projected Subsidy',
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
		$criteria->compare('month_name',$this->month_name,true);
		$criteria->compare('month_date',$this->month_date,true);
		$criteria->compare('actual',$this->actual);
		$criteria->compare('actual_credit_card',$this->actual_credit_card);
		$criteria->compare('actual_subsidy',$this->actual_subsidy);
		$criteria->compare('projected',$this->projected);
		$criteria->compare('projected_credit_card',$this->projected_credit_card);
		$criteria->compare('projected_subsidy',$this->projected_subsidy);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ImpactReport the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
