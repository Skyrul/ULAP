<?php

/**
 * This is the model class for table "{{company_subsidy_level}}".
 *
 * The followings are the available columns in table '{{company_subsidy_level}}':
 * @property integer $id
 * @property integer $company_id
 * @property integer $subsidy_id
 * @property string $name
 * @property string $type
 * @property string $value
 * @property string $date_created
 * @property string $date_updated
 */
class CompanySubsidyLevel extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{company_subsidy_level}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id, company_id, subsidy_id, name, type, value', 'required'),
			array('id, company_id, subsidy_id', 'numerical', 'integerOnly'=>true),
			array('name, value', 'length', 'max'=>60),
			array('type, tier_link', 'length', 'max'=>10),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, company_id, subsidy_id, name, type, value, tier_link, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'company_id' => 'Company',
			'subsidy_id' => 'Subsidy',
			'name' => 'Name',
			'type' => 'Type',
			'value' => 'Value',
			'tier_link' => 'Tier Link',
			'date_created' => 'Date Created',
			'date_updated' => 'Date Updated',
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
		$criteria->compare('company_id',$this->company_id);
		$criteria->compare('subsidy_id',$this->subsidy_id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('type',$this->type,true);
		$criteria->compare('value',$this->value,true);
		$criteria->compare('date_created',$this->date_created,true);
		$criteria->compare('date_updated',$this->date_updated,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CompanySubsidyLevel the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function beforeSave()
	{
		if($this->isNewRecord)
			$this->date_created = $this->date_updated = date("Y-m-d H:i:s");
		else
			$this->date_updated = date("Y-m-d H:i:s");
		
		return parent::beforeSave();
	}
}


