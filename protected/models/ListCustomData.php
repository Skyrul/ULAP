<?php

/**
 * This is the model class for table "{{list_custom_data}}".
 *
 * The followings are the available columns in table '{{list_custom_data}}':
 * @property integer $id
 * @property integer $list_id
 * @property integer $customer_id
 * @property string $custom_name
 * @property string $original_name
 * @property integer $ordering
 * @property integer $display_on_form
 * @property integer $allow_edit
 * @property integer $type
 * @property integer $status
 * @property string $date_created
 * @property string $date_updated
 */
class ListCustomData extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{list_custom_data}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('list_id, customer_id, ordering, display_on_form, allow_edit, type, status', 'numerical', 'integerOnly'=>true),
			array('custom_name', 'length', 'max'=>55),
			array('original_name, member_number', 'length', 'max'=>255),
			array('date_created, date_updated', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, list_id, customer_id, custom_name, original_name, member_number, ordering, display_on_form, allow_edit, type, status, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'list' => array(self::BELONGS_TO, 'Lists', 'list_id')
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'list_id' => 'List',
			'customer_id' => 'Customer',
			'custom_name' => 'Custom Name',
			'original_name' => 'Original Name',
			'member_number' => 'Member Number',
			'ordering' => 'Ordering',
			'display_on_form' => 'Display On Form',
			'allow_edit' => 'Allow Edit',
			'type' => 'Type',
			'status' => 'Status',
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
		$criteria->compare('list_id',$this->list_id);
		$criteria->compare('customer_id',$this->customer_id);
		$criteria->compare('custom_name',$this->custom_name,true);
		$criteria->compare('original_name',$this->original_name,true);
		$criteria->compare('member_number',$this->member_number,true);
		$criteria->compare('ordering',$this->ordering);
		$criteria->compare('display_on_form',$this->display_on_form);
		$criteria->compare('allow_edit',$this->allow_edit);
		$criteria->compare('type',$this->type);
		$criteria->compare('status',$this->status);
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
	 * @return ListCustomData the static model class
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
