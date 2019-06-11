<?php

/**
 * This is the model class for table "{{customer_history}}".
 *
 * The followings are the available columns in table '{{customer_history}}':
 * @property integer $id
 * @property integer $model_id
 * @property integer $customer_id
 * @property integer $user_account_id
 * @property string $content
 * @property string $page_name
 * @property string $old_data
 * @property string $new_data
 * @property integer $type
 * @property integer $status
 * @property string $date_created
 * @property string $date_updated
 */
class CustomerHistory extends CActiveRecord
{
	const TYPE_ADDED = 1;
	const TYPE_UPDATED = 2;
	const TYPE_DELETED = 3;
	const TYPE_DOWNLOADED = 4;
	const TYPE_REMOVED = 5;
	
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{customer_history}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('model_id, customer_id, user_account_id, type, status', 'numerical', 'integerOnly'=>true),
			array('page_name', 'length', 'max'=>255),
			array('content, old_data, new_data, date_created, date_updated', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, model_id, customer_id, user_account_id, content, page_name, old_data, new_data, type, status, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'account' => array(self::BELONGS_TO, 'Account', 'user_account_id'),
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
			'model_id' => 'Model',
			'customer_id' => 'Customer',
			'user_account_id' => 'User Account',
			'content' => 'Content',
			'page_name' => 'Page Name',
			'old_data' => 'Old Data',
			'new_data' => 'New Data',
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
		$criteria->compare('model_id',$this->model_id);
		$criteria->compare('customer_id',$this->customer_id);
		$criteria->compare('user_account_id',$this->user_account_id);
		$criteria->compare('content',$this->content,true);
		$criteria->compare('page_name',$this->page_name,true);
		$criteria->compare('old_data',$this->old_data,true);
		$criteria->compare('new_data',$this->new_data,true);
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
	 * @return CustomerHistory the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function beforeSave()
	{
		if( $this->scenario == 'add2Hours' )
		{
			if($this->isNewRecord)
				$this->date_created = $this->date_updated = date("Y-m-d H:i:s", strtotime('+2 hours'));
			else
				$this->date_updated = date("Y-m-d H:i:s", strtotime('+2 hours'));
		}
		else
		{
			if($this->isNewRecord)
				$this->date_created = $this->date_updated = date("Y-m-d H:i:s");
			else
				$this->date_updated = date("Y-m-d H:i:s");
		}
		
		return parent::beforeSave();
	}
}
