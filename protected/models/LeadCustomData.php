<?php

/**
 * This is the model class for table "{{lead_custom_data}}".
 *
 * The followings are the available columns in table '{{lead_custom_data}}':
 * @property integer $id
 * @property integer $lead_id
 * @property string $original_name
 * @property string $custom_name
 * @property string $value
 * @property integer $order
 * @property integer $display_on_form
 * @property integer $allow_edit
 * @property string $date_created
 * @property string $date_updated
 */
class LeadCustomData extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{lead_custom_data}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('lead_id, list_id', 'numerical', 'integerOnly'=>true),
			array('field_name', 'length', 'max'=>255),
			array('list_id, value, date_created, date_updated', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, lead_id, lead_id, field_name, value, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'lead' => array(self::BELONGS_TO, 'Lead', 'lead_id'),
			'list' => array(self::BELONGS_TO, 'Lists', 'list_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'lead_id' => 'Lead',
			'list_id' => 'List',
			'field_name' => 'Field Name',
			'value' => 'Value',
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
		$criteria->compare('lead_id',$this->lead_id);
		$criteria->compare('list_id',$this->list_id);
		$criteria->compare('field_name',$this->field_name,true);
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
	 * @return LeadCustomData the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	protected function beforeSave()
	{
		if (parent::beforeSave())
		{
			if ($this->isNewRecord)
			{
				$this->date_created = $this->date_updated = date('Y-m-d H:i:s');
			}
			else
			{
				$this->date_updated = date('Y-m-d H:i:s');
			}
			
			return true;
		}
	}
}
