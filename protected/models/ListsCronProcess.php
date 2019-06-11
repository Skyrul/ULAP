<?php

/**
 * This is the model class for table "{{lists_cron_process}}".
 *
 * The followings are the available columns in table '{{lists_cron_process}}':
 * @property integer $id
 * @property integer $list_id
 * @property integer $file_upload_id
 * @property integer $on_going
 * @property string $date_created
 * @property string $date_completed
 */
class ListsCronProcess extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{lists_cron_process}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('list_id, account_id', 'required'),
			array('account_id, list_id, fileupload_id, on_going, is_notified', 'numerical', 'integerOnly'=>true),
			array('result_data', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, list_id, fileupload_id, on_going, date_created, date_started, date_completed', 'safe', 'on'=>'search'),
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
			'list' => array(self::BELONGS_TO, 'Lists', 'list_id'),
			'fileupload' => array(self::BELONGS_TO, 'Fileupload', 'fileupload_id'),
			'account' => array(self::BELONGS_TO, 'Account', 'account_id'),
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
			'fileupload_id' => 'File Upload',
			'on_going' => 'On Going',
			'date_created' => 'Date Created',
			'date_completed' => 'Date Completed',
			'result_data' => 'Result Data',
			'is_notified' => 'Date Completed',
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
		$criteria->compare('fileupload_id',$this->fileupload_id);
		$criteria->compare('on_going',$this->on_going);
		$criteria->compare('date_created',$this->date_created,true);
		$criteria->compare('date_completed',$this->date_completed,true);
		$criteria->compare('result_data',$this->result_data,true);
		$criteria->compare('is_notified',$this->is_notified,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ListsCronProcess the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	
	public function beforeSave()
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
}
