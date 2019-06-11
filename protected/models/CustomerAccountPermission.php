<?php

/**
 * This is the model class for table "{{customer_account_permission}}".
 *
 * The followings are the available columns in table '{{customer_account_permission}}':
 * @property integer $id
 * @property integer $account_id
 * @property string $permission_key
 * @property string $permission_type
 */
class CustomerAccountPermission extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{customer_account_permission}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('account_id, permission_key, permission_type', 'required'),
			array('account_id', 'numerical', 'integerOnly'=>true),
			array('permission_key, permission_type', 'length', 'max'=>255),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, account_id, permission_key, permission_type', 'safe', 'on'=>'search'),
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
			'account_id' => 'Account',
			'permission_key' => 'Permission Key',
			'permission_type' => 'Permission Type',
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
		$criteria->compare('permission_key',$this->permission_key,true);
		$criteria->compare('permission_type',$this->permission_type,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CustomerAccountPermission the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public static function autoAddPermissionKey($account)
	{		
		foreach(CompanyPermission::permissionKeys() as $moduleKey => $module)
		{
			##visible
			$modulePermissionVisible = CustomerAccountPermission::model()->find(array(
				'condition' => '
					account_id = :account_id
					AND permission_key = :permission_key
					AND permission_type = :permission_type
				',
				'params' => array(
					'account_id' => $account->id,
					':permission_key' => $moduleKey,
					':permission_type' => 'visible'
				),
			));
			
			if(empty($modulePermissionVisible))
			{
				$mPV = new CustomerAccountPermission;
				$mPV->account_id = $account->id;
				$mPV->permission_key = $moduleKey;
				$mPV->permission_type = 'visible';
				
				if(!$mPV->save(false))
				{
					echo 'Error in Permission Visible'; exit;
				}
			}
			
			##edit
			
			$modulePermissionEdit = CustomerAccountPermission::model()->find(array(
				'condition' => '
					account_id = :account_id
					AND permission_key = :permission_key
					AND permission_type = :permission_type
				',
				'params' => array(
					'account_id' => $account->id,
					':permission_key' => $moduleKey,
					':permission_type' => 'edit'
				),
			));
			
			if( strpos($moduleKey, 'field') !== false || strpos($moduleKey, 'checkbox') !== false || strpos($moduleKey, 'dropdown') !== false ) 
			{
				
				if(empty($modulePermissionEdit))
				{
					$mPE = new CustomerAccountPermission;
					$mPE->account_id = $account->id;
					$mPE->permission_key = $moduleKey;
					$mPE->permission_type = 'edit';
					
					if(!$mPE->save(false))
					{
						echo 'Error in Permission Edit'; exit;
					}
				}
			}
			
			##report
			$modulePermissionDirectReport = CustomerAccountPermission::model()->find(array(
				'condition' => '
					account_id = :account_id
					AND permission_key = :permission_key
					AND permission_type = :permission_type
				',
				'params' => array(
					'account_id' => $account->id,
					':permission_key' => $moduleKey,
					':permission_type' => 'only_for_direct_reports'
				),
			));
			
			if( isset($module['has_direct_report_checkbox']) ) 
			{
				if(empty($modulePermissionDirectReport))
				{
					$mPR = new CustomerAccountPermission;
					$mPR->account_id = $account->id;
					$mPR->permission_key = $moduleKey;
					$mPR->permission_type = 'only_for_direct_reports';
					
					if(!$mPR->save(false))
					{
						echo 'Error in Direct Report'; exit;
					}
				}
			}
			
			##sub modules###
			if( !empty($module['subModules']) )
			{
				foreach( $module['subModules'] as $childModuleKey => $childModule )
				{
					$childModulePermissionVisible = CustomerAccountPermission::model()->find(array(
						'condition' => '
							account_id = :account_id
							AND permission_key = :permission_key
							AND permission_type = :permission_type
						',
						'params' => array(
							'account_id' => $account->id,
							':permission_key' => $childModuleKey,
							':permission_type' => 'visible'
						),
					));
					
					if(empty($childModulePermissionVisible))
					{
						$mPV = new CustomerAccountPermission;
						$mPV->account_id = $account->id;
						$mPV->permission_key = $childModuleKey;
						$mPV->permission_type = 'visible';
						
						if(!$mPV->save(false))
						{
							echo 'Error in Sub module Permission Visible'; exit;
						}
					}
			
					
					$childModulePermissionEdit = CustomerAccountPermission::model()->find(array(
						'condition' => '
							account_id = :account_id
							AND permission_key = :permission_key
							AND permission_type = :permission_type
						',
						'params' => array(
							'account_id' => $account->id,
							':permission_key' => $childModuleKey,
							':permission_type' => 'edit'
						),
					));
					
					if( strpos($childModuleKey, 'field') !== false || strpos($childModuleKey, 'checkbox') !== false || strpos($childModuleKey, 'dropdown') !== false )
					{
						if(empty($childModulePermissionEdit))
						{
							$mPE = new CustomerAccountPermission;
							$mPE->account_id = $account->id;
							$mPE->permission_key = $childModuleKey;
							$mPE->permission_type = 'edit';
							
							if(!$mPE->save(false))
							{
								echo 'Error in Sub Module Permission Edit'; exit;
							}
						}
					}
					
					$childModulePermissionDirectReport = CustomerAccountPermission::model()->find(array(
						'condition' => '
							account_id = :account_id
							AND permission_key = :permission_key
							AND permission_type = :permission_type
						',
						'params' => array(
							'account_id' => $account->id,
							':permission_key' => $childModuleKey,
							':permission_type' => 'only_for_direct_reports'
						),
					));
					
					if( isset($childModule['has_direct_report_checkbox']) )
					{
						if(empty($childModulePermissionDirectReport))
						{
							$mPR = new CustomerAccountPermission;
							$mPR->account_id = $account->id;
							$mPR->permission_key = $childModuleKey;
							$mPR->permission_type = 'only_for_direct_reports';
							
							if(!$mPR->save(false))
							{
								echo 'Error in Sub module Direct Report'; exit;
							}
						}
					}
				}
			}
		}
	}
}
