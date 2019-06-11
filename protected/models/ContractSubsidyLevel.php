<?php

/**
 * This is the model class for table "{{contract_subsidy_level}}".
 *
 * The followings are the available columns in table '{{contract_subsidy_level}}':
 * @property integer $id
 * @property integer $contract_id
 * @property integer $type
 * @property integer $group_id
 * @property string $column_name
 * @property string $column_value
 */
class ContractSubsidyLevel extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{contract_subsidy_level}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('contract_id, type, group_id, column_name, column_value', 'required'),
			array('contract_id, type, group_id', 'numerical', 'integerOnly'=>true),
			array('column_name, column_value', 'length', 'max'=>60),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, contract_id, type, group_id, column_name, column_value', 'safe', 'on'=>'search'),
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
			'contract' => array(self::BELONGS_TO, 'Contract', 'contract_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'contract_id' => 'Contract',
			'type' => 'Type',
			'group_id' => 'Group',
			'column_name' => 'Column Name',
			'column_value' => 'Column Value',
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
		$criteria->compare('contract_id',$this->contract_id);
		$criteria->compare('type',$this->type);
		$criteria->compare('group_id',$this->group_id);
		$criteria->compare('column_name',$this->column_name,true);
		$criteria->compare('column_value',$this->column_value,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ContractSubsidyLevel the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
