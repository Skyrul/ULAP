<?php

/**
 * This is the model class for table "{{customer_echeck}}".
 *
 * The followings are the available columns in table '{{customer_echeck}}':
 * @property integer $id
 * @property integer $customer_id
 * @property string $account_number
 * @property string $routing_number
 * @property string $account_type
 * @property string $entity_name
 * @property string $account_name
 * @property string $institution_name
 * @property integer $status
 * @property integer $type
 * @property string $date_created
 * @property string $date_updated
 */
class CustomerEcheck extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{customer_echeck}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('account_number, routing_number, account_type, account_name, institution_name', 'required'),
			array('customer_id, status, type', 'numerical', 'integerOnly'=>true),
			array('account_number, routing_number, account_type, entity_name, account_name, institution_name', 'length', 'max'=>255),
			array('date_created, date_updated', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, customer_id, account_number, routing_number, account_type, entity_name, account_name, institution_name, status, type, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'account_number' => 'Account Number',
			'routing_number' => 'Routing Number',
			'account_type' => 'Account Type',
			'entity_name' => 'Entity Name',
			'account_name' => 'Account Name',
			'institution_name' => 'Institution Name',
			'status' => 'Status',
			'type' => 'Type',
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
		$criteria->compare('customer_id',$this->customer_id);
		$criteria->compare('account_number',$this->account_number,true);
		$criteria->compare('routing_number',$this->routing_number,true);
		$criteria->compare('account_type',$this->account_type,true);
		$criteria->compare('entity_name',$this->entity_name,true);
		$criteria->compare('account_name',$this->account_name,true);
		$criteria->compare('institution_name',$this->institution_name,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('type',$this->type);
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
	 * @return CustomerEcheck the static model class
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
	
	public static function items($customer_id = null)
	{
		$items = array();
		
		if( $customer_id != null )
		{
			$models = self::model()->findAll(array(
				'condition' => 'customer_id = :customer_id AND status=1',
				'params' => array(
					':customer_id' => $customer_id,
				),
			));
		}
		else
		{
			$models = self::model()->findAll();
		}
		
		foreach($models as $model)
		{
			$items[$model->id] = !empty($model->account_name) ? $model->account_name : $model->routing_number;
		}
		
		return $items;
	}
}
