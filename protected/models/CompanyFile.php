<?php

/**
 * This is the model class for table "{{customer_file}}".
 *
 * The followings are the available columns in table '{{customer_file}}':
 * @property integer $id
 * @property integer $company_id
 * @property integer $fileupload_id
 * @property integer $user_account_id
 * @property integer $type
 * @property integer $status
 * @property string $date_created
 * @property string $date_updated
 */
class CompanyFile extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{company_file}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('company_id, fileupload_id, user_account_id, type, status', 'numerical', 'integerOnly'=>true),
			array('date_created, date_updated', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, company_id, fileupload_id, user_account_id, type, status, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'fileUpload' => array(self::BELONGS_TO, 'Fileupload', 'fileupload_id'),
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
			'fileupload_id' => 'Fileupload',
			'user_account_id' => 'User Account',
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
		$criteria->compare('company_id',$this->company_id);
		$criteria->compare('fileupload_id',$this->fileupload_id);
		$criteria->compare('user_account_id',$this->user_account_id);
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
	 * @return CompanyFile the static model class
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
