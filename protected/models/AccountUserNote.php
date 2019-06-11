<?php

/**
 * This is the model class for table "{{account_user_note}}".
 *
 * The followings are the available columns in table '{{account_user_note}}':
 * @property integer $id
 * @property integer $account_id
 * @property integer $account_user_id
 * @property integer $category_id
 * @property string $content
 * @property integer $type
 * @property integer $status
 * @property string $date_created
 * @property string $date_updated
 */
class AccountUserNote extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{account_user_note}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('account_id, account_user_id, category_id, type, status', 'numerical', 'integerOnly'=>true),
			array('content, old_data, new_data, date_created, date_updated', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, account_id, account_user_id, category_id, content, old_data, new_data, type, status, date_created, date_updated', 'safe', 'on'=>'search'),
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
			'category_id' => 'Category',
			'content' => 'Content',
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
		$criteria->compare('account_id',$this->account_id);
		$criteria->compare('account_user_id',$this->account_user_id);
		$criteria->compare('category_id',$this->category_id);
		$criteria->compare('content',$this->content,true);
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
	 * @return AccountUserNote the static model class
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
				$this->date_created = $this->date_updated = date('Y-m-d H:i:s');
			}
			else
			{
				$this->date_updated = date('Y-m-d H:i:s');
			}
			
			return true;
		}
	}
	
	public function getType()
	{
		$options = self::documentTypeOptions();
		
		return $this->type != null ? $options[$this->type] : '';
	}
	
	public function getCategory()
	{
		$options = self::noteCategoryOptions(true);
		
		return $this->category_id != null ? $options[$this->category_id] : '';
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
	
	public static function noteTypeOptions()
	{
		return array(
			1 => 'Hire',
			2 => 'Change of Status',
			3 => 'Termination',
			4 => 'Other',
		);
	}
	
	
	public static function noteCategoryOptions($includeAuditRecord=false)
	{
		$options = array(
			1 => 'Attendance',
			// 2 => 'Disciplinary',
			// 3 => 'Attitude',
			// 4 => 'Performance',
			5 => 'Payroll',
			6 => 'Paperwork',
			7 => 'Performance',
			8 => 'Promotion',
			9 => 'Change of Status',
			10 => 'Correction',
		);
		
		if( $includeAuditRecord )
		{
			$options[10] = 'Audit Record';
		}
		
		return $options;
	}
}
