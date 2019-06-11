<?php

/**
 * This is the model class for table "{{account_user_document}}".
 *
 * The followings are the available columns in table '{{account_user_document}}':
 * @property integer $id
 * @property integer $account_id
 * @property integer $account_user_id
 * @property integer $fileupload_id
 * @property integer $type
 * @property string $date_created
 */
class AccountUserDocument extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{account_user_document}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('account_id, account_user_id, fileupload_id, type_id', 'numerical', 'integerOnly'=>true),
			array('date_created', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, account_id, account_user_id, fileupload_id, type_id, date_created', 'safe', 'on'=>'search'),
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
			'docType' => array(self::BELONGS_TO, 'AccountUserDocumentType', 'type_id'),
			'account' => array(self::BELONGS_TO, 'Account', 'account_id'),
			'accountUser' => array(self::BELONGS_TO, 'AccountUser', 'account_user_id'),
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
			'account_user_id' => 'Account User',
			'fileupload_id' => 'Fileupload',
			'type_id' => 'Type',
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
		$criteria->compare('account_id',$this->account_id);
		$criteria->compare('account_user_id',$this->account_user_id);
		$criteria->compare('fileupload_id',$this->fileupload_id);
		$criteria->compare('type_id',$this->type_id);
		$criteria->compare('date_created',$this->date_created,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.`
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return AccountUserDocument the static model class
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
				$this->date_created = date('Y-m-d H:i:s');
			}
			
			return true;
		}
	}
	
	public function getType()
	{
		$options = self::documentTypeOptions();
		
		return $this->type != null ? $options[$this->type] : '';
	}

	public static function documentTypeOptions()
	{
		return array(
			1 => 'Hire',
			2 => 'Change of Status',
			3 => 'Termination',
			4 => 'Other',
		);
	}
}
