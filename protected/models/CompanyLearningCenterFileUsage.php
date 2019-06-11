<?php

/**
 * This is the model class for table "{{company_learning_center_file_usage}}".
 *
 * The followings are the available columns in table '{{company_learning_center_file_usage}}':
 * @property integer $id
 * @property integer $account_id
 * @property integer $company_id
 * @property integer $company_learning_center_file_id
 * @property string $date_created
 */
class CompanyLearningCenterFileUsage extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{company_learning_center_file_usage}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('customer_id, company_id, company_learning_center_file_id', 'required'),
			array('customer_id, company_id, company_learning_center_file_id', 'numerical', 'integerOnly'=>true),
			array('date_created', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, customer_id, company_id, company_learning_center_file_id, date_created', 'safe', 'on'=>'search'),
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
			'customer' => array(self::BELONGS_TO, 'Customer', 'customer_id'),
			'company' => array(self::BELONGS_TO, 'Company', 'company_id'),
			'learningCenterFile' => array(self::BELONGS_TO, 'CompanyLearningCenterFile', 'company_learning_center_file_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'customer_id' => 'Customer',
			'company_id' => 'Company',
			'company_learning_center_file_id' => 'Company Learning Center File',
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
		$criteria->compare('customer_id',$this->customer_id);
		$criteria->compare('company_id',$this->company_id);
		$criteria->compare('company_learning_center_file_id',$this->company_learning_center_file_id);
		$criteria->compare('date_created',$this->date_created,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CompanyLearningCenterFileUsage the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function beforeSave()
	{
		if( $this->isNewRecord )
		{
			$this->date_created = date("Y-m-d H:i:s");
		}
		
		return parent::beforeSave();
	}
}