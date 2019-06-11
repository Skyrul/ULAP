<?php

/**
 * This is the model class for table "{{company_enrollment}}".
 *
 * The followings are the available columns in table '{{company_enrollment}}':
 * @property integer $id
 * @property integer $company_id
 * @property string $enrollment_url
 * @property string $html_content
 * @property integer $status
 * @property integer $type
 * @property string $date_created
 * @property string $date_updated
 */
class CompanyEnrollment extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{company_enrollment}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('enrollment_url, html_content', 'required'),
			array('company_id, status, type', 'numerical', 'integerOnly'=>true),
			array('enrollment_url', 'length', 'max'=>255),
			array('html_content, date_created, date_updated', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, company_id, enrollment_url, html_content, status, type, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'enrollment_url' => 'Enrollment Url',
			'html_content' => 'Html Content',
			'status' => 'Display on enrollment form',
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
		$criteria->compare('company_id',$this->company_id);
		$criteria->compare('enrollment_url',$this->enrollment_url,true);
		$criteria->compare('html_content',$this->html_content,true);
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
	 * @return CompanyEnrollment the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
