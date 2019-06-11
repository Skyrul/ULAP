<?php

/**
 * This is the model class for table "{{impact_report_settings}}".
 *
 * The followings are the available columns in table '{{impact_report_settings}}':
 * @property integer $id
 * @property string $auto_email_recipients
 * @property string $auto_email_frequency
 * @property string $auto_email_day
 * @property string $auto_email_time
 * @property string $auto_email_last_sent
 * @property string $date_created
 * @property string $date_updated
 */
class ImpactReportSettings extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{impact_report_settings}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('auto_email_frequency', 'length', 'max'=>50),
			array('auto_email_day, auto_email_time', 'length', 'max'=>20),
			array('auto_email_recipients, auto_email_last_sent, date_created, date_updated', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, auto_email_recipients, auto_email_frequency, auto_email_day, auto_email_time, auto_email_last_sent, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'auto_email_recipients' => 'Auto Email Recipients',
			'auto_email_frequency' => 'Auto Email Frequency',
			'auto_email_day' => 'Auto Email Day',
			'auto_email_time' => 'Auto Email Time',
			'auto_email_last_sent' => 'Auto Email Last Sent',
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
		$criteria->compare('auto_email_recipients',$this->auto_email_recipients,true);
		$criteria->compare('auto_email_frequency',$this->auto_email_frequency,true);
		$criteria->compare('auto_email_day',$this->auto_email_day,true);
		$criteria->compare('auto_email_time',$this->auto_email_time,true);
		$criteria->compare('auto_email_last_sent',$this->auto_email_last_sent,true);
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
	 * @return ImpactReportSettings the static model class
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
