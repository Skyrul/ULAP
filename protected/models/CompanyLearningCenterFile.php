<?php

/**
 * This is the model class for table "{{company_learning_center_file}}".
 *
 * The followings are the available columns in table '{{company_learning_center_file}}':
 * @property integer $id
 * @property integer $company_id
 * @property integer $fileupload_id
 * @property integer $account_id
 * @property string $title
 * @property string $description
 * @property integer $order
 * @property integer $status
 * @property integer $type
 * @property integer $date_created
 * @property integer $date_updated
 */
class CompanyLearningCenterFile extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{company_learning_center_file}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('company_id, category_id, fileupload_id, thumbnail_fileupload_id, account_id, title', 'required'),
			array('company_id, category_id, fileupload_id, thumbnail_fileupload_id, account_id, sort_order, status, type, date_created, date_updated', 'numerical', 'integerOnly'=>true),
			array('title', 'length', 'max'=>255),
			array('description', 'safe'),
			array('fileupload_id, thumbnail_fileupload_id', 'file', 'safe' => false),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, company_id, category_id, fileupload_id, thumbnail_fileupload_id, account_id, title, description, sort_order, status, type, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'thumbnailFileUpload' => array(self::BELONGS_TO, 'Fileupload', 'thumbnail_fileupload_id'),
			'category' => array(self::BELONGS_TO, 'CompanyLearningCenterCategory', 'category_id'),
			'company' => array(self::BELONGS_TO, 'Company', 'company_id'),
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
			'company_id' => 'Company',
			'fileupload_id' => 'File',
			'thumbnail_fileupload_id' => 'Thumbnail',
			'account_id' => 'Account',
			'title' => 'Title',
			'description' => 'Description',
			'sort_order' => 'Order',
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
		$criteria->compare('company_id',$this->company_id);
		$criteria->compare('fileupload_id',$this->fileupload_id);
		$criteria->compare('thumbnail_fileupload_id',$this->thumbnail_fileupload_id);
		$criteria->compare('account_id',$this->account_id);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('sort_order',$this->sort_order);
		$criteria->compare('status',$this->status);
		$criteria->compare('type',$this->type);
		$criteria->compare('date_created',$this->date_created);
		$criteria->compare('date_updated',$this->date_updated);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CompanyLearningCenterFile the static model class
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
	
	public function getType()
	{		
		switch( $this->type )
		{
			default: case 1: $type = 'Video'; break;
			case 2: 'Audio'; break;
			case 3: 'Document'; break;
		}
		
		return $type;
	}

	public function getCategories($company_id)
	{
		$items = array();
		
		$categories = CompanyLearningCenterCategory::model()->findAll(array(
			'condition' => 'company_id = :company_id AND status != 3',
			'params' => array(
				':company_id' => $company_id,
			),
			'order' => 'sort_order ASC',
		));
		
		if( $categories )
		{
			foreach( $categories as $category )
			{
				$items[$category->id] = $category->name;
			}
		}
		
		return $items;
	}
}
