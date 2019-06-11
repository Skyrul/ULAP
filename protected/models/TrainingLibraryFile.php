<?php

/**
 * This is the model class for table "{{training_library_file}}".
 *
 * The followings are the available columns in table '{{training_library_file}}':
 * @property integer $id
 * @property integer $category_id
 * @property integer $thumbnail_fileupload_id
 * @property integer $fileupload_id
 * @property integer $account_id
 * @property string $title
 * @property string $description
 * @property string $url
 * @property integer $sort_order
 * @property string $security_groups
 * @property integer $status
 * @property integer $type
 * @property string $date_created
 * @property string $date_updated
 */
class TrainingLibraryFile extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{training_library_file}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('fileupload_id, title', 'required'),
			array('category_id, thumbnail_fileupload_id, fileupload_id, account_id, sort_order, status, type', 'numerical', 'integerOnly'=>true),
			array('title, url, security_groups', 'length', 'max'=>255),
			array('description, date_created, date_updated', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, category_id, thumbnail_fileupload_id, fileupload_id, account_id, title, description, url, sort_order, security_groups, status, type, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'category' => array(self::BELONGS_TO, 'TrainingLibraryCategory', 'category_id'),
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
			'category_id' => 'Category',
			'thumbnail_fileupload_id' => 'Thumbnail Fileupload',
			'fileupload_id' => 'Fileupload',
			'account_id' => 'Account',
			'title' => 'Title',
			'description' => 'Description',
			'url' => 'Url',
			'sort_order' => 'Sort Order',
			'security_groups' => 'Security Groups',
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
		$criteria->compare('category_id',$this->category_id);
		$criteria->compare('thumbnail_fileupload_id',$this->thumbnail_fileupload_id);
		$criteria->compare('fileupload_id',$this->fileupload_id);
		$criteria->compare('account_id',$this->account_id);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('url',$this->url,true);
		$criteria->compare('sort_order',$this->sort_order);
		$criteria->compare('security_groups',$this->security_groups,true);
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
	 * @return TrainingLibraryFile the static model class
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
			case 4: 'Link'; break;
		}
		
		return $type;
	}

	public function getCategories()
	{
		$items = array();
		
		$categories = TrainingLibraryCategory::model()->findAll(array(
			'condition' => 'status != 3',
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
