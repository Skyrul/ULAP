<?php

/**
 * This is the model class for table "{{news}}".
 *
 * The followings are the available columns in table '{{news}}':
 * @property integer $id
 * @property integer $account_id
 * @property integer $fileupload_id
 * @property string $title
 * @property string $body
 * @property integer $status
 * @property integer $type
 * @property string $date_created
 * @property string $date_updated
 */
class News extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{news}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('title', 'required'),
			array('account_id, fileupload_id, sort_order, status, type', 'numerical', 'integerOnly'=>true),
			array('title', 'length', 'max'=>255),
			array('body, status, status, type, date_created, date_updated', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, account_id, fileupload_id, title, body, status, status, type, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'hiddenSettings' => array(self::HAS_ONE, 'NewsAccountSettings', 'news_id'),
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
			'fileupload_id' => 'Fileupload',
			'title' => 'Title',
			'body' => 'Body',
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
		$criteria->compare('account_id',$this->account_id);
		$criteria->compare('fileupload_id',$this->fileupload_id);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('body',$this->body,true);
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
	 * @return News the static model class
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
	
	public function scopes()
	{
		return array(
			'orderBySortASC' => array(
				'order' => 'sort_order ASC',
			),
			'orderBySortDESC' => array(
				'order' => 'sort_order DESC',
			),
			'active' => array(
				'condition' => 't.status = 1',
			),
			'htmlNewsOnly' => array(
				'condition' => 't.type = 1',
			),
			'imageNewsOnly' => array(
				'condition' => 't.type = 2',
			),
		);
	}
	
	public static function checkNewPosts(){
		if(!Yii::app()->user->isGuest)
		{
			$count = 0;
			$noSettings = array();
			
			$result = array(
				'count' => $count,
				'noSettings' => $noSettings
			);

			$authAccount = Yii::app()->user->account;
			
			$models = News::model()->findAll(array(
				'condition' => 't.status = 1 AND t.type = 1',
				'order' => 't.sort_order ASC',
			));
			
			if( $models )
			{
				foreach( $models as $model )
				{
					$existingSettings = NewsAccountSettings::model()->find(array(
						'condition' => 'account_id = :account_id AND news_id = :news_id',
						'params' => array(
							':account_id' => $authAccount->id,
							':news_id' => $model->id
						),
					));
					
					if( empty($existingSettings) )
					{
						$count++;
						
						$noSettings[] = $model->id;
					}
					else
					{
						if( $existingSettings->is_seen == 0 )
						{
							$count++;
						}
					}
				}
			}
			
			$result['count'] = $count;
			$result['noSettings'] = $noSettings;
			
			echo json_encode($result);
		}
	}
}
