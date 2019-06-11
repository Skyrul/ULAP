<?php

/**
 * This is the model class for table "{{fileupload}}".
 *
 * The followings are the available columns in table '{{fileupload}}':
 * @property integer $id
 * @property string $original_filename
 * @property string $generated_filename
 * @property string $date_created
 */
class FileuploadAudio extends CActiveRecord
{
	public $audioPath;
	
	public function init()
	{
		$this->audioPath = Yii::app()->basePath.'/../voice/';
	}
	
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{fileupload}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			// array('original_filename, generated_filename', 'required'),
			array('original_filename, generated_filename', 'length', 'max'=>150),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, original_filename, generated_filename, date_created', 'safe', 'on'=>'search'),
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
			'original_filename' => 'Original Filename',
			'generated_filename' => 'Generated Filename',
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
		$criteria->compare('original_filename',$this->original_filename,true);
		$criteria->compare('generated_filename',$this->generated_filename,true);
		$criteria->compare('date_created',$this->date_created,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Fileupload the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function beforeSave()
	{
		if($this->isNewRecord)
			$this->date_created = date("Y-m-d H:i:s");
		
		if ($this->original_filename instanceof CUploadedFile) {
			$this->original_filename = $this->original_filename->getName() . "." . $this->original_filename->getExtensionName();
		}
  
		return parent::beforeSave();
	}
	
	public function getVoiceFullPath()
	{
		return Yii::app()->request->baseUrl.'/voice/'.$this->generated_filename;
	}
}
