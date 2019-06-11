<?php

/**
 * This is the model class for table "{{account}}".
 *
 * The followings are the available columns in table '{{account}}':
 * @property integer $id
 * @property string $email_address
 * @property string $username
 * @property string $password
 * @property string $date_created
 * @property string $date_updated
 * @property integer $account_type_id
 * @property integer $status
 * @property integer $is_deleted
 */
class Account extends CActiveRecord
{
	const STATUS_ACTIVE = 1;
	const STATUS_INACTIVE = 2;
	
	const TYPE_ADMIN = 1;
	const TYPE_AGENT = 2;
	const TYPE_PORTAL = 3;
	const TYPE_CUSTOMER_SERVICE = 4;
	const TYPE_CUSTOMER = 5;
	const TYPE_CUSTOMER_OFFICE_STAFF = 6;
	const TYPE_COMPANY = 7;
	const TYPE_DATA_ENTRY = 8;
	const TYPE_HUMAN_RESOURCES = 9;
	const TYPE_MANAGER = 10;
	const TYPE_SALES = 11;
	const TYPE_SUPERVISOR = 12;
	const TYPE_TEAM_LEAD = 13;
	const TYPE_GRATON_AGENT = 14;
	const TYPE_HOSTDIAL_AGENT = 15;
	const TYPE_GAMING_PROJECT_MANAGER = 16;
	
	public $confirmPassword;
	
	private $encryptionKey = 'a37b7ed9';
	
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{account}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('email_address, username', 'required'),
			array('password', 'required', 'on' => 'insert, register, forgotPassword, changePassword, createEmployee'),
			array('confirmPassword', 'required', 'on' => 'changePassword, createEmployee'),
			array('confirmPassword', 'validatePassword'),
			array('confirmPassword', 'validateEmployeePassword', 'on' => 'changePassword, createEmployee, updateEmployee'),
			array('username', 'unique'),
			array('account_type_id, status, is_deleted, login_attempt, use_webphone, sip_username', 'numerical', 'integerOnly'=>true),
			array('email_address, username, password, sip_password', 'length', 'max'=>128),
			array('token, login_token', 'length', 'max'=>128),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, email_address, username, password, token, token_date, date_created, date_updated, account_type_id, status, is_deleted, login_attempt, use_webphone', 'safe', 'on'=>'search'),
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
			'accountUser' => array(self::HAS_ONE, 'AccountUser', 'account_id'),
			'customer' => array(self::HAS_ONE, 'Customer', 'account_id'),
			'customerOfficeStaff' => array(self::HAS_ONE, 'CustomerOfficeStaff', 'account_id'),
			'company' => array(self::HAS_ONE, 'Company', 'account_id'),
			'accountSkill' => array(self::HAS_ONE, 'AccountSkill', 'account_id'),
			'customerEnrollment' => array(self::HAS_ONE, 'CustomerEnrollment', 'account_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'email_address' => 'Email Address',
			'username' => 'Username',
			'password' => 'Password',
			'date_created' => 'Date Created',
			'date_updated' => 'Date Updated',
			'account_type_id' => 'Security Group', //Account type
 			'status' => 'Status',
			'is_deleted' => 'Is Deleted',
			'token' => 'Token',
			'token_date' => 'Token Date',
			'login_token' => 'Token',
			'login_attempt' => 'Login Attempt',
			'use_webphone' => 'Use Webphone',
			'sip_username' => 'Webphone Extension',
			'sip_password' => 'Webphone Password',
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
		$criteria->compare('email_address',$this->email_address,true);
		$criteria->compare('username',$this->username,true);
		$criteria->compare('password',$this->password,true);
		$criteria->compare('date_created',$this->date_created,true);
		$criteria->compare('date_updated',$this->date_updated,true);
		$criteria->compare('account_type_id',$this->account_type_id);
		$criteria->compare('status',$this->status);
		$criteria->compare('is_deleted',$this->is_deleted);
		$criteria->compare('token',$this->token);
		$criteria->compare('token_date',$this->token_date);
		$criteria->compare('login_token',$this->login_token);
		$criteria->compare('login_attempt',$this->login_attempt);
		$criteria->compare('use_webphone',$this->use_webphone);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Account the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	private function encryptData($str)
	{
		$block = mcrypt_get_block_size('des', 'ecb');
		
		if( ($pad = $block - (strlen($str) % $block)) < $block ) 
		{
			$str .= str_repeat(chr($pad), $pad);
		}
		
		return base64_encode(mcrypt_encrypt(MCRYPT_DES, $this->encryptionKey, $str, MCRYPT_MODE_ECB));
	}
	
	public function beforeSave()
	{
		$this->password = $this->encryptData($this->password);
		
		if( $this->isNewRecord )
		{
			$this->date_created = $this->date_updated = date("Y-m-d H:i:s");
		}
		else
		{
			$this->date_updated = date("Y-m-d H:i:s");
		}
		
		return parent::beforeSave();
	}

	public function afterFind()
	{
		$accountTypeLabel = $this->getAccountTypeLabel();
		return parent::afterFind();
	}
	
	public static function listStatus()
	{
		return array(
			self::STATUS_ACTIVE => 'Active',
			self::STATUS_INACTIVE => 'Inactive',
		);	
	}
	
	public $statusLabel = null;
	public function getStatusLabel()
	{
		if($this->statusLabel === null)
		{
			$listStatus = self::listStatus();
			
			if(isset($listStatus[$this->status]))
			{
				$this->statusLabel = $listStatus[$this->status];
			}
		}
		
		return $this->statusLabel;
	}
	
	public static function listAccountType()
	{ 
		$types =  array(
			self::TYPE_ADMIN => 'Admin',
			self::TYPE_AGENT => 'Agent',
			self::TYPE_PORTAL => 'Portal',
			self::TYPE_CUSTOMER_SERVICE => 'Customer Service',
			self::TYPE_DATA_ENTRY => 'Data Entry',
			self::TYPE_GAMING_PROJECT_MANAGER => 'Gaming Project Manager',
			self::TYPE_GRATON_AGENT => 'Graton Agent',
			self::TYPE_HOSTDIAL_AGENT => 'Hostdial Agent',
			self::TYPE_HUMAN_RESOURCES => 'Human Resources',
			self::TYPE_MANAGER => 'Manager',
			self::TYPE_SALES => 'Sales',
			self::TYPE_SUPERVISOR => 'Supervisor',
			self::TYPE_TEAM_LEAD => 'Team Lead',
		);	
		
		if( !Yii::app()->user->isGuest && isset(Yii::app()->user->account) && Yii::app()->user->account->account_type_id != self::TYPE_ADMIN )
		{
			unset($types[self::TYPE_ADMIN]);
		}
		
		return $types;
	}
	
	public $accountTypeLabel = null;
	public function getAccountTypeLabel()
	{
		if($this->accountTypeLabel === null)
		{
			$listAccountType = self::listAccountType();
			
			if(isset($listAccountType[$this->account_type_id]))
			{
				$this->accountTypeLabel = $listAccountType[$this->account_type_id];
			}
		}
		
		return $this->accountTypeLabel;
	}
	
	### USER TYPES ####
	
	public function getIsAdmin()
	{
		$value = false;
		
		if( Yii::app()->user->account->account_type_id == Account::TYPE_ADMIN || Yii::app()->user->account->account_type_id == null )
		{
			$value = true;
		}
		
		return $value;
	}
	
	public function getIsAgent()
	{
		if(Yii::app()->user->isGuest)
            return false;
		
		return (Yii::app()->user->account->account_type_id == Account::TYPE_AGENT);
	}
	
	public function getIsPortal()
	{
		if(Yii::app()->user->isGuest)
            return false;
		
		return (Yii::app()->user->account->account_type_id == Account::TYPE_PORTAL);
	}
	
	public function getIsCustomerService()
	{
		if(Yii::app()->user->isGuest)
            return false;
		
		return (Yii::app()->user->account->account_type_id == Account::TYPE_CUSTOMER_SERVICE);
	}
	
	public function getIsCustomer()
	{
		if(Yii::app()->user->isGuest)
            return false;
		
		return (Yii::app()->user->account->account_type_id == Account::TYPE_CUSTOMER);
	}
	
	public function getIsCustomerOfficeStaff()
	{
		if(Yii::app()->user->isGuest)
            return false;
		
		return (Yii::app()->user->account->account_type_id == Account::TYPE_CUSTOMER_OFFICE_STAFF);
	}

	public function getIsCompany()
	{
		if(Yii::app()->user->isGuest)
            return false;
		
		return (Yii::app()->user->account->account_type_id == Account::TYPE_COMPANY);
	}
	
	public function getIsHostDialer()
	{
		if(Yii::app()->user->isGuest)
            return false;
		
		return (Yii::app()->user->account->account_type_id == Account::TYPE_HOSTDIAL_AGENT);
	}
	
	public function getIsHostManager()
	{
		if(Yii::app()->user->isGuest)
            return false;
		
		return (Yii::app()->user->account->account_type_id == Account::TYPE_GAMING_PROJECT_MANAGER);
	}
	
	public function getIsSales()
	{
		if(Yii::app()->user->isGuest)
            return false;
		
		return (Yii::app()->user->account->account_type_id == Account::TYPE_SALES);
	}
	
	### END OF USER TYPES ####
	
	public function getFullName()
	{
		// const TYPE_ADMIN = 1;
		// const TYPE_AGENT = 2;
		// const TYPE_PORTAL = 3;
		// const TYPE_CUSTOMER_SERVICE = 4;
		// const TYPE_CUSTOMER = 5;
		// const TYPE_CUSTOMER_OFFICE_STAFF = 6;
		// const TYPE_COMPANY = 7;
	
		// if(in_array($this->account_type_id, array(self::TYPE_ADMIN, self::TYPE_AGENT, self::TYPE_PORTAL, self::TYPE_CUSTOMER_SERVICE)))
		// {
			// return $this->accountUser->getFullName();
		// }
		
		if( in_array($this->account_type_id, array(self::TYPE_CUSTOMER)) )
		{
			return $this->customer->getFullName();
		}
		elseif( in_array($this->account_type_id, array(self::TYPE_CUSTOMER_OFFICE_STAFF, self::TYPE_HOSTDIAL_AGENT, self::TYPE_GAMING_PROJECT_MANAGER)) )
		{
			return $this->customerOfficeStaff->staff_name;
		}
		else
		{
			return $this->accountUser->getFullName();
		}
	}
	
	public function getFullNameReverse()
	{
		return $this->accountUser->getFullNameReverse();
	}

	public function validatePassword($attribute,$params)
	{
		if(!empty($this->password))
		{
			if( strlen($this->password) < 6)
			{
				$this->addError('password', 'Password must be a minimum of 6 characters!');
			}
			else if($this->password != $this->confirmPassword)
			{
				$this->addError('confirmPassword', 'Confirm Password does not match!');
			}
		}
	}
	
	public function validateEmployeePassword()
	{
		if(!empty($this->password))
		{
			if( strlen($this->password) < 8 )
			{
				$this->addError('password', 'Password must be a minimum of 8 characters');
			}
			
			if( preg_match("/\d/", $this->password) == 0 )
			{
				$this->addError('password', 'Password must contain atleast one number');
			}
			
			if( preg_match('/[A-Z]/', $this->password) == 0 )
			{
				$this->addError('password', 'Password must contain atleast one upper case letter');
			}
			
			if( preg_match('/[^a-z0-9 _]+/i', $this->password) == 0 )
			{
				$this->addError('password', 'Password must contain atleast one special character');
			}
			
			if( $this->password != $this->confirmPassword )
			{
				$this->addError('confirmPassword', 'Confirm Password does not match!');
			}
		}
	}
	
	public function byAccountTypeId($type)
	{
		$criteria = new CDbCriteria;
		$criteria->compare('account_type_id', $type);
		
		$this->getDbCriteria()->mergeWith($criteria);
		return $this;
	}
	
	public function byIdsInCondition($ids = array())
	{
		
		$criteria = new CDbCriteria;
		$criteria->addInCondition('id', $ids);
		
		$this->getDbCriteria()->mergeWith($criteria);
		return $this;
	}
	
	public function byIdsNotInCondition($ids = array())
	{
		$criteria = new CDbCriteria;
		$criteria->addNotInCondition('id', $ids);
		
		$this->getDbCriteria()->mergeWith($criteria);
		return $this;
	}
	
	public function getTotalLoginHours($dateFilterStart='', $dateFilterEnd='', $page='', $type='')
	{
		$totalTime = 0;
		
		$totalHours = 0;
		$totalMinutes = 0;

		if( $dateFilterStart != '' && $dateFilterEnd != '' )
		{
			if( $page == '' )
			{
				$models = AccountLoginTracker::model()->findAll(array(
					'condition' => 'account_id = :account_id AND time_in >= :start_date AND time_in <= :end_date AND status=1',
					'params' => array(
						':account_id' => $this->id,
						':start_date' => date('Y-m-d 00:00:00', strtotime($dateFilterStart)),
						':end_date' => date('Y-m-d 23:59:59', strtotime($dateFilterEnd)),
					),
					'order' => 'date_created DESC',
				));
			}
			else
			{
				if( $page == 'logout' )
				{
					$models = AccountLoginTracker::model()->findAll(array(
						'condition' => 'account_id = :account_id AND time_in >= :start_date AND time_in <= :end_date',
						'params' => array(
							':account_id' => $this->id,
							':start_date' => date('Y-m-d 00:00:00', strtotime($dateFilterStart)),
							':end_date' => date('Y-m-d 23:59:59', strtotime($dateFilterEnd)),
						),
						'order' => 'date_created DESC',
					));
				}
			}
		}
		else
		{
			$models = AccountLoginTracker::model()->findAll(array(
				'condition' => 'account_id = :account_id',
				'params' => array(
					':account_id' => $this->id,
				),
			));
		}

		if( $models )
		{
			foreach( $models as $model )
			{
				// $existingPtoRequest = AccountPtoRequest::model()->find(array(
					// 'condition' => 'STR_TO_DATE(request_date, "%m/%d/%Y") = :loginDate AND status=1',
					// 'params' => array(
						// ':loginDate' => date('Y-m-d', strtotime($model->date_created)),
					// ),
				// ));
				
				// $timeIn = new DateTime($model->time_in, new DateTimeZone('America/Chicago'));
				// $timeIn->setTimezone(new DateTimeZone('America/Denver'));
				
				// if( $model->type == 1 )
				// {
					// $timeOut = new DateTime($model->time_out, new DateTimeZone('America/Chicago'));
					// $timeOut->setTimezone(new DateTimeZone('America/Denver'));
				// }
				// else
				// {
					// $timeOut = new DateTime($model->time_out);
				// }
				

				if( $model->time_out != null )
				{
					$totalMinutes += round(abs(strtotime($model->time_in) - strtotime($model->time_out)) / 60,2);
				}
				else
				{
					if( $page == 'logout' )
					{
						$totalMinutes += round(abs(strtotime($model->time_in) - strtotime('now')) / 60,2);
					}
				}
			}
			
			if( $type == 'decimal' )
			{
				$totalTime = round($totalMinutes / 60, 2);
			}
			else
			{
				$totalHours =  floor($totalMinutes/60);
				$totalMinutes =   $totalMinutes % 60;
				
				if( strlen($totalHours) == 1)
				{
					$totalHours = '0'.$totalHours;
				}
				
				if( strlen($totalMinutes) == 1)
				{
					$totalMinutes = '0'.$totalMinutes;
				}
				
				$totalTime = $totalHours.':'.$totalMinutes;
			}
		}

		return $totalTime;
	}
	
	public function getTotalLoginHoursTest($dateFilterStart='', $dateFilterEnd='')
	{
		$totalTime = 0;
		
		$totalHours = 0;
		$totalMinutes = 0;

		if( $dateFilterStart != '' && $dateFilterEnd != '' )
		{
			$models = AccountLoginTracker::model()->findAll(array(
				'condition' => 'account_id = :account_id AND time_in >= :start_date AND time_in <= :end_date AND status=1',
				'params' => array(
					':account_id' => $this->id,
					':start_date' => date('Y-m-d 00:00:00', strtotime($dateFilterStart)),
					':end_date' => date('Y-m-d 23:59:59', strtotime($dateFilterEnd)),
				),
				'order' => 'date_created DESC',
			));
		}
		else
		{
			$models = AccountLoginTracker::model()->findAll(array(
				'condition' => 'account_id = :account_id',
				'params' => array(
					':account_id' => $this->id,
				),
			));
		}

		if( $models )
		{
			foreach( $models as $model )
			{
				$existingPtoRequest = AccountPtoRequest::model()->find(array(
					'condition' => 'STR_TO_DATE(request_date, "%m/%d/%Y") = :loginDate AND status=1',
					'params' => array(
						':loginDate' => date('Y-m-d', strtotime($model->date_created)),
					),
				));
				
				$timeIn = new DateTime($model->time_in, new DateTimeZone('America/Chicago'));
				$timeIn->setTimezone(new DateTimeZone('America/Denver'));
				
				if( $model->type == 1 )
				{
					$timeOut = new DateTime($model->time_out, new DateTimeZone('America/Chicago'));
					$timeOut->setTimezone(new DateTimeZone('America/Denver'));
				}
				else
				{
					$timeOut = new DateTime($model->time_out);
				}
				

				if( $model->time_out != null )
				{
					$totalMinutes += round(abs(strtotime($model->time_in) - strtotime($model->time_out)) / 60,2);
				}
			}
			
			echo '<br>';
			echo 'Total Minutes: '.$totalMinutes.'<br>';
			echo 'Total Hours: '.$totalHours.'<br>';
			$totalHours =  floor($totalMinutes/60);
			$totalMinutes =   $totalMinutes % 60;
			
			echo 'computed--<br>';
			echo 'Total Minutes: '.$totalMinutes.'<br>';
			echo 'Total Hours: '.$totalHours.'<br>';
			
			if( strlen($totalHours) == 1)
			{
				$totalHours = '0'.$totalHours;
			}
			
			if( strlen($totalMinutes) == 1)
			{
				$totalMinutes = '0'.$totalMinutes;
			}
			
			$totalTime = $totalHours.':'.$totalMinutes;
		}

		return $totalTime;
	}

	public function getSecurityGroup()
	{
		switch( $this->account_type_id )
		{
			case 1 : $val = 'Admin'; break; 
			case 2 : $val = 'Agent'; break; 
			case 3 : $val = 'Portal'; break; 
			case 4 : $val = 'Customer Service'; break; 
			
			default: $val = null;
		}
		
		return $val;
	}

	public function checkPermission_backup($permissionKey, $permissionType, $accountId=null)
	{
		$authAccount = Yii::app()->user->account;
		
		$securityGroups = Account::listAccountType();
		
		$securityGroupPermissionSwitch = AccountPermission::model()->find(array(
			'condition' => 'permission_key = :permission_key AND permission_type = :permission_type',
			'params' => array(
				':permission_key' => 'security_group_'.strtolower($securityGroups[$authAccount->account_type_id]).'_master',
				':permission_type' => 'master_switch'
			),
		));
		
		if( $securityGroupPermissionSwitch )
		{
			$permission = AccountPermission::model()->find(array(
				'condition' => '
					account_type_id = :account_type_id
					AND permission_key = :permission_key
					AND permission_type = :permission_type
				',
				'params' => array(
					':account_type_id' => $authAccount->account_type_id,
					':permission_key' => $permissionKey,
					':permission_type' => $permissionType
				),
			));
			
			if( $permissionType == 'only_for_direct_reports' )
			{
				if( $permission )
				{
					$parentAccountIds = array();
					
					if( $accountId != null )
					{
						$accountPosition = Position::model()->find(array(
							'condition' => 'account_id = :account_id',
							'params' => array(
								'account_id' => $accountId
							),
						));
						
						if( $accountPosition )
						{
							$parent = Position::model()->findByPk($accountPosition->parent_id);
							
							if( $parent )
							{
								$parentAccountIds[] = $parent->account_id;
								
								$parentAccountIds = array_unique( array_merge( $parentAccountIds, $this->getParentPosition($parent->account_id, $parentAccountIds) ), SORT_REGULAR);
							}
						}
						
						if( $parentAccountIds )
						{
							$result = in_array($authAccount->id, $parentAccountIds) ? true : false;
						}
						else
						{
							$result = true;
						}
					}
					else
					{
						$result = true;
					}
				}
				else
				{
					$result = true;
				}
			}
			else
			{
				$result = !empty($permission) ? true : false;
			}
		}
		else
		{
			$result = true;
		}
		
		return $result;
	}
	
	public function checkPermission($permissionKey, $permissionType, $accountId=null)
	{
		$authAccount = Yii::app()->user->account;
		
		##account type permission
		$securityGroups = Account::listAccountType();
		
		$securityGroupPermissionSwitch = AccountPermission::model()->find(array(
			'condition' => 'permission_key = :permission_key AND permission_type = :permission_type',
			'params' => array(
				':permission_key' => 'security_group_'.strtolower($securityGroups[$authAccount->account_type_id]).'_master',
				':permission_type' => 'master_switch'
			),
		));
		
		##company permission
		if($authAccount->IsCustomer || $authAccount->IsCustomerOfficeStaff)
		{
			$result = false;
			
			if($authAccount->IsCustomer)
			{
				$companyId = $authAccount->customer->company_id;
			}
			
			if($authAccount->IsCustomerOfficeStaff)
			{
				$companyId = $authAccount->customerOfficeStaff->customer->company_id;
			}
			
		
			$criteria = new CDbCriteria;
			$criteria->compare('company_id', $companyId);
			$criteria->compare('permission_key', $permissionKey);
			$criteria->compare('permission_type', $permissionType);
			$companyPermission = CompanyPermission::model()->find($criteria);
		
			if(!empty($companyPermission))
			{
				## not sure if need to add this
				##if( $permissionType == 'only_for_direct_reports' )
					
				$result = true;
			}
			
			
			if($authAccount->IsCustomerOfficeStaff)
			{
				$result = false;
				
				$criteria = new CDbCriteria;
				$criteria->compare('account_id', $authAccount->id);
				$criteria->compare('permission_key', $permissionKey);
				$criteria->compare('permission_type', $permissionType);
				$customerAccountPermission = CustomerAccountPermission::model()->find($criteria);
				
				if(!empty($customerAccountPermission))
				{
					$result = true;
				}
			}
		}
		elseif( $securityGroupPermissionSwitch )
		{
			$permission = AccountPermission::model()->find(array(
				'condition' => '
					account_type_id = :account_type_id
					AND permission_key = :permission_key
					AND permission_type = :permission_type
				',
				'params' => array(
					':account_type_id' => $authAccount->account_type_id,
					':permission_key' => $permissionKey,
					':permission_type' => $permissionType
				),
			));
			
			if( $permissionType == 'only_for_direct_reports' )
			{
				if( $permission )
				{
					$parentAccountIds = array();
					
					if( $accountId != null )
					{
						$accountPosition = Position::model()->find(array(
							'condition' => 'account_id = :account_id',
							'params' => array(
								'account_id' => $accountId
							),
						));
						
						if( $accountPosition )
						{
							$parent = Position::model()->findByPk($accountPosition->parent_id);
							
							if( $parent )
							{
								$parentAccountIds[] = $parent->account_id;
								
								$parentAccountIds = array_unique( array_merge( $parentAccountIds, $this->getParentPosition($parent->account_id, $parentAccountIds) ), SORT_REGULAR);
							}
						}
						
						if( $parentAccountIds )
						{
							$result = in_array($authAccount->id, $parentAccountIds) ? true : false;
						}
						else
						{
							$result = true;
						}
					}
					else
					{
						$result = true;
					}
				}
				else
				{
					$result = true;
				}
			}
			else
			{
				$result = !empty($permission) ? true : false;
			}
		}
		else
		{
			$result = true;
		}
		
		return $result;
	}
	
	public function getParentPosition( $accountId, $parentAccountIds )  
	{
		$accountPosition = Position::model()->find(array(
			'condition' => 'account_id = :account_id',
			'params' => array(
				'account_id' => $accountId
			)
		));
		
		if( $accountPosition )
		{
			$parent = Position::model()->findByPk($accountPosition->parent_id);
			
			if( $parent )
			{
				$parentAccountIds[] = $parent->account_id;
				
				$parentAccountIds = array_unique( array_merge( $parentAccountIds, $this->getParentPosition($parent->account_id, $parentAccountIds) ), SORT_REGULAR);
			}
		}
		
		return $parentAccountIds;
	}
}
