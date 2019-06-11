<?php

/**
 * This is the model class for table "{{cron_child_skill_setting}}".
 *
 * The followings are the available columns in table '{{cron_child_skill_setting}}':
 * @property integer $id
 * @property integer $ongoing
 * @property integer $completed
 * @property string $date_ongoing
 * @property string $date_completed
 */
class CronChildSkillSetting extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{cron_child_skill_setting}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('ongoing, completed, date_ongoing, date_completed', 'required'),
			array('ongoing, completed', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, ongoing, completed, date_ongoing, date_completed', 'safe', 'on'=>'search'),
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
			'ongoing' => 'Ongoing',
			'completed' => 'Completed',
			'date_ongoing' => 'Date Ongoing',
			'date_completed' => 'Date Completed',
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
		$criteria->compare('ongoing',$this->ongoing);
		$criteria->compare('completed',$this->completed);
		$criteria->compare('date_ongoing',$this->date_ongoing,true);
		$criteria->compare('date_completed',$this->date_completed,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CronCustomerContractSetting the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
