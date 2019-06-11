<?php

/**
 * This is the model class for table "{{account_user}}".
 *
 * The followings are the available columns in table '{{account_user}}':
 * @property integer $id
 * @property integer $fileupload_id
 * @property integer $account_id
 * @property string $first_name
 * @property string $last_name
 * @property string $salary
 * @property string $salary_type
 * @property string $date_hire
 * @property string $date_termination
 * @property string $language
 * @property string $employee_number
 * @property string $job_tile
 * @property string $emergency_contact
 * @property string $social_security_number
 * @property string $full_time_status
 * @property string $pay_rate
 * @property string $security_group
 * @property string $has_employee_portal_access
 */
class AccountUser extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{account_user}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('first_name, last_name', 'required'),
			array('fileupload_id, account_id, phone_extension, badge_id', 'numerical', 'integerOnly'=>true),
			array('first_name, last_name', 'length', 'max'=>80),
			array('badge_id', 'length', 'max'=>8),
			array('salary, salary_type, language', 'length', 'max'=>60),
			array('address, phone_number, mobile_number, employee_number, job_title, security_group, emergency_contact, social_security_number, full_time_status, pay_rate, has_employee_portal_access, commission_rate, date_hire, date_termination, birthday', 'length', 'max'=>255),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, fileupload_id, account_id, first_name, last_name, salary, salary_type, date_hire, date_termination, birthday, language, address, phone_number, mobile_number, employee_number, job_title, security_group, emergency_contact, social_security_number, full_time_status, pay_rate, has_employee_portal_access, commission_rate, phone_extension', 'safe', 'on'=>'search'),
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
			'fileupload' => array(self::BELONGS_TO, 'Fileupload', 'fileupload_id'),
			'fileuploadAudio' => array(self::BELONGS_TO, 'FileuploadAudio', 'voiceupload_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'fileupload_id' => 'Fileupload',
			'account_id' => 'Account',
			'first_name' => 'First Name',
			'last_name' => 'Last Name',
			'salary' => 'Salary',
			'salary_type' => 'Hour/Salary',
			'date_hire' => 'Start Date',
			'date_termination' => 'Termination Date',
			'language' => 'Language',
			'employee_number' => 'Employee #',
			'badge_id' => 'Badge ID',
			'job_title' => 'Job Title',
			'emergency_contact' => 'Emergency Contact',
			'social_security_number' => 'Social Security Number',
			'full_time_status' => 'Full-Time Status',
			'pay_rate' => 'Pay Rate',
			'security_group' => 'Security Group',
			'has_employee_portal_access' => 'Employee Portal Access',
			'phone_extension' => 'Phone Extension',
			'birthday' => 'Date of Birth',
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
		$criteria->compare('fileupload_id',$this->fileupload_id);
		$criteria->compare('account_id',$this->account_id);
		$criteria->compare('first_name',$this->first_name,true);
		$criteria->compare('last_name',$this->last_name,true);
		$criteria->compare('salary',$this->salary,true);
		$criteria->compare('salary_type',$this->salary_type,true);
		$criteria->compare('date_hire',$this->date_hire,true);
		$criteria->compare('date_termination',$this->date_termination,true);
		$criteria->compare('language',$this->language,true);
		$criteria->compare('employee_number',$this->employee_number,true);
		$criteria->compare('job_title',$this->job_title,true);
		$criteria->compare('emergency_contact',$this->emergency_contact,true);
		$criteria->compare('social_security_number',$this->social_security_number,true);
		$criteria->compare('full_time_status',$this->full_time_status,true);
		$criteria->compare('pay_rate',$this->pay_rate,true);
		$criteria->compare('security_group',$this->security_group,true);
		$criteria->compare('has_employee_portal_access',$this->has_employee_portal_access,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return AccountUser the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	
	public function scopes()
	{
		return array(
			'latestRecord' => array(
				'order' => 'id DESC',
			),
		);
	}
	
	public function getFullName()
	{
		// if(!empty($this->middlename))
			// return $this->firstname.' '.$this->middlename.' '.$this->lastname;
		// else
			return $this->first_name.' '.$this->last_name;
	}
	
	public function getFullNameReverse()
	{
		// if(!empty($this->middlename))
			// return $this->lastname.', '.$this->firstname.' '.$this->middlename;
		// else
			return $this->last_name.', '.$this->first_name;
	}

	public function beforeSave()
	{
		// $employeeNumber = "000100";
		
		// $latestUser = self::model()->latestRecord()->find();
		
		// if( $latestUser )
		// {
			// $employeeNumber = str_pad( ($latestUser->employee_number + 1), 6, "0", STR_PAD_LEFT);
		// }
		
		// if( $this->isNewRecord )
		// {
			// $this->employee_number = $employeeNumber;
		// }
		
		if($this->date_hire == '0000-00-00' || empty($this->date_hire) )
			$this->date_hire = null;
		else
			$this->date_hire = date("Y-m-d", strtotime($this->date_hire));
		
		if($this->date_termination == '0000-00-00' || empty($this->date_termination) )
			$this->date_termination = null;
		else
			$this->date_termination = date("Y-m-d", strtotime($this->date_termination));
		
		if($this->birthday == '0000-00-00' || empty($this->birthday) )
			$this->birthday = null;
		else
			$this->birthday = date("Y-m-d", strtotime($this->birthday));
		
		return parent::beforeSave();
	}
	
	public function afterFind()
	{
		if($this->date_hire == '0000-00-00' || empty($this->date_hire) )
			$this->date_hire = null;
		else
			$this->date_hire = date("m/d/Y", strtotime($this->date_hire));
		
		if($this->date_termination == '0000-00-00' || empty($this->date_termination) )
			$this->date_termination = null;
		else
			$this->date_termination = date("m/d/Y", strtotime($this->date_termination));
		
		
		if($this->birthday == '0000-00-00' || empty($this->birthday) )
			$this->birthday = null;
		else
			$this->birthday = date("m/d/Y", strtotime($this->birthday));
		
		return parent::afterFind();
	}
	
	public function getAudio()
	{
		if(isset($this->fileuploadAudio))
		{
			return $this->fileuploadAudio->getVoiceFullPath();
		}
		
		return null;
	}
	
	public function getImage()
	{
		if(isset($this->fileupload))
		{
			return $this->fileupload->getImageFullPath();
		}
		
		return null;
	}

	public function getOriginalImage()
	{
		if(isset($this->fileupload))
		{
			return $this->fileupload->getOriginalImageFullPath();
		}
		
		return null;
	}
	
	public static function jobTitleOptions()
	{
		return array(
			'Call Agent' => 'Call Agent',
			'Customer Service Agent' => 'Customer Service Agent',
			'Data Entry' => 'Data Entry',
			'Manager' => 'Manager',
			'Payroll Clerk' => 'Payroll Clerk',
			'Sales Agent' => 'Sales Agent',
			'Supervisor' => 'Supervisor',
			'Team Leader' => 'Team Leader',
		);
	}
	
	public static function fullTimeStatusOptions()
	{
		return array(
			'FULL TIME' => 'FULL TIME',
			'PART TIME' => 'PART TIME',
			'PROJECT' => 'PROJECT',
			'SYSTEM' => 'SYSTEM',
		);
		
	}
	
	public static function salaryOptions()
	{
		return array(
			'HOURLY' => 'HOURLY', 
			'SALARY' => 'SALARY'
		);
	}	
	
	public static function securityGroupOptions()
	{
		return array(
			'ADMIN' => 'ADMIN',
			'SUPERVISOR' => 'SUPERVISOR',
			'AGENT' => 'AGENT',
			'CUSTOMER SERVICE' => 'CUSTOMER SERVICE',
		);
	}

	
	public static function items()
	{
		$items = array();
		
		$models = self::model()->findAll(array(
			'with' => 'account',
			'together'=>true,
			'condition' => 'account.status=1 AND account.is_deleted=0 AND account.account_type_id NOT IN (3,5) AND account.status=1',
			'order' => 't.last_name ASC',
		));
		
		if( $models )
		{
			foreach( $models as $model )
			{
				$items[$model->account_id] = $model->getFullName();
			}
		}
		
		return $items;
	}
	
	public static function listSalesAgents()
	{
		$items = array();
		
		$models = self::model()->findAll(array(
			'with' => 'account',
			'together'=>true,
			'condition' => 'account.status=1 AND account.is_deleted=0 AND t.job_title = "Sales Agent"',
			'order' => 't.last_name ASC',
		));
		
		if( $models )
		{
			foreach( $models as $model )
			{
				$items[$model->account_id] = $model->getFullName();
			}
		}
		
		return $items;
	}
}
