<?php

/**
 * This is the model class for table "{{list_file_upload}}".
 *
 * The followings are the available columns in table '{{list_file_upload}}':
 * @property integer $id
 * @property integer $list_id
 * @property string $file_name
 * @property string $original_file_name
 * @property integer $number_of_leads
 * @property integer $type
 * @property integer $status
 * @property string $date_created
 * @property string $date_updated
 */
class ListFileUpload extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{list_file_upload}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('list_id, number_of_leads, type, status', 'numerical', 'integerOnly'=>true),
			array('file_name, original_file_name', 'length', 'max'=>255),
			array('date_created, date_updated', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, list_id, file_name, original_file_name, number_of_leads, type, status, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'list_id' => 'List',
			'file_name' => 'File Name',
			'original_file_name' => 'Original File Name',
			'number_of_leads' => 'Number Of Leads',
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
		$criteria->compare('file_name',$this->file_name,true);
		$criteria->compare('original_file_name',$this->original_file_name,true);
		$criteria->compare('number_of_leads',$this->number_of_leads);
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
	 * @return ListFileUpload the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
