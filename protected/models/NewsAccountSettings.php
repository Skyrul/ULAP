<?php

/**
 * This is the model class for table "{{news_account_settings}}".
 *
 * The followings are the available columns in table '{{news_account_settings}}':
 * @property integer $id
 * @property integer $account_id
 * @property integer $news_id
 * @property integer $is_marked_read
 * @property integer $is_marked_hide
 * @property string $date_created
 */
class NewsAccountSettings extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{news_account_settings}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('account_id, news_id', 'required'),
			array('account_id, news_id, is_marked_read, is_marked_hide, is_seen', 'numerical', 'integerOnly'=>true),
			array('date_created', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, account_id, news_id, is_marked_read, is_marked_hide, is_seen, date_created', 'safe', 'on'=>'search'),
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
			'account' => array(self::BELONGS_TO, 'Account', 'account_id'),
			'news' => array(self::BELONGS_TO, 'News', 'news_id') 
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
			'news_id' => 'News',
			'is_marked_read' => 'Is Marked Read',
			'is_marked_hide' => 'Is Marked Hide',
			'is_seen' => 'Is Seen',
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
		$criteria->compare('news_id',$this->news_id);
		$criteria->compare('is_marked_read',$this->is_marked_read);
		$criteria->compare('is_marked_hide',$this->is_marked_hide);
		$criteria->compare('is_seen',$this->is_seen);
		$criteria->compare('date_created',$this->date_created,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return NewsAccountSettings the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
