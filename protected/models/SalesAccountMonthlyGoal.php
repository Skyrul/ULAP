<?php

/**
 * This is the model class for table "{{sales_account_monthly_goal}}".
 *
 * The followings are the available columns in table '{{sales_account_monthly_goal}}':
 * @property integer $id
 * @property integer $account_id
 * @property string $sales_count
 * @property string $sales_revenue
 * @property string $stretch_count
 * @property string $commission_rate
 * @property string $user_accelerator
 */
class SalesAccountMonthlyGoal extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{sales_account_monthly_goal}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('account_id', 'numerical', 'integerOnly'=>true),
			array('sales_count, sales_revenue, stretch_count, commission_rate, user_accelerator', 'length', 'max'=>255),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, account_id, sales_count, sales_revenue, stretch_count, commission_rate, user_accelerator', 'safe', 'on'=>'search'),
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
			'account_id' => 'Account',
			'sales_count' => 'Sales Count',
			'sales_revenue' => 'Sales Revenue',
			'stretch_count' => 'Stretch Count',
			'commission_rate' => 'Commission Rate',
			'user_accelerator' => 'User Accelerator',
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
		$criteria->compare('account_id',$this->account_id);
		$criteria->compare('sales_count',$this->sales_count,true);
		$criteria->compare('sales_revenue',$this->sales_revenue,true);
		$criteria->compare('stretch_count',$this->stretch_count,true);
		$criteria->compare('commission_rate',$this->commission_rate,true);
		$criteria->compare('user_accelerator',$this->user_accelerator,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return SalesAccountMonthlyGoal the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
