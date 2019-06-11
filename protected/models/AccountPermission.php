<?php

/**
 * This is the model class for table "{{account_permission}}".
 *
 * The followings are the available columns in table '{{account_permission}}':
 * @property integer $id
 * @property integer $account_id
 * @property integer $account_type_id
 * @property string $permission_key
 * @property string $permission_type
 */
class AccountPermission extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{account_permission}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('account_id, account_type_id, permission_key, permission_type', 'required'),
			array('account_id, account_type_id', 'numerical', 'integerOnly'=>true),
			array('permission_key, permission_type', 'length', 'max'=>255),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, account_id, account_type_id, permission_key, permission_type', 'safe', 'on'=>'search'),
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
			'account_type_id' => 'Account Type',
			'permission_key' => 'Permission Key',
			'permission_type' => 'Permission Type',
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
		$criteria->compare('account_type_id',$this->account_type_id);
		$criteria->compare('permission_key',$this->permission_key,true);
		$criteria->compare('permission_type',$this->permission_type,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return AccountPermission the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
