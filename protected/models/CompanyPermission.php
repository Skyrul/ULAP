<?php

/**
 * This is the model class for table "{{company_permission}}".
 *
 * The followings are the available columns in table '{{company_permission}}':
 * @property integer $id
 * @property integer $company_id
 * @property string $permission_key
 * @property string $permission_type
 */
class CompanyPermission extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{company_permission}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('company_id, permission_key, permission_type', 'required'),
			array('company_id', 'numerical', 'integerOnly'=>true),
			array('permission_key, permission_type', 'length', 'max'=>255),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, company_id, permission_key, permission_type', 'safe', 'on'=>'search'),
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
			'company_id' => 'Company',
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
		$criteria->compare('company_id',$this->company_id);
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
	 * @return CompanyPermission the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public static function autoAddPermissionKey($company)
	{		
		foreach(CompanyPermission::permissionKeys() as $moduleKey => $module)
		{
			##visible
			$modulePermissionVisible = CompanyPermission::model()->find(array(
				'condition' => '
					company_id = :company_id
					AND permission_key = :permission_key
					AND permission_type = :permission_type
				',
				'params' => array(
					'company_id' => $company->id,
					':permission_key' => $moduleKey,
					':permission_type' => 'visible'
				),
			));
			
			if(empty($modulePermissionVisible))
			{
				$mPV = new CompanyPermission;
				$mPV->company_id = $company->id;
				$mPV->permission_key = $moduleKey;
				$mPV->permission_type = 'visible';
				
				if(!$mPV->save(false))
				{
					echo 'Error in Permission Visible'; exit;
				}
			}
			
			##edit
			
			$modulePermissionEdit = CompanyPermission::model()->find(array(
				'condition' => '
					company_id = :company_id
					AND permission_key = :permission_key
					AND permission_type = :permission_type
				',
				'params' => array(
					'company_id' => $company->id,
					':permission_key' => $moduleKey,
					':permission_type' => 'edit'
				),
			));
			
			if( strpos($moduleKey, 'field') !== false || strpos($moduleKey, 'checkbox') !== false || strpos($moduleKey, 'dropdown') !== false ) 
			{
				
				if(empty($modulePermissionEdit))
				{
					$mPE = new CompanyPermission;
					$mPE->company_id = $company->id;
					$mPE->permission_key = $moduleKey;
					$mPE->permission_type = 'edit';
					
					if(!$mPE->save(false))
					{
						echo 'Error in Permission Edit'; exit;
					}
				}
			}
			
			##report
			$modulePermissionDirectReport = CompanyPermission::model()->find(array(
				'condition' => '
					company_id = :company_id
					AND permission_key = :permission_key
					AND permission_type = :permission_type
				',
				'params' => array(
					'company_id' => $company->id,
					':permission_key' => $moduleKey,
					':permission_type' => 'only_for_direct_reports'
				),
			));
			
			if( isset($module['has_direct_report_checkbox']) ) 
			{
				if(empty($modulePermissionDirectReport))
				{
					$mPR = new CompanyPermission;
					$mPR->company_id = $company->id;
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
					$childModulePermissionVisible = CompanyPermission::model()->find(array(
						'condition' => '
							company_id = :company_id
							AND permission_key = :permission_key
							AND permission_type = :permission_type
						',
						'params' => array(
							'company_id' => $company->id,
							':permission_key' => $childModuleKey,
							':permission_type' => 'visible'
						),
					));
					
					if(empty($childModulePermissionVisible))
					{
						$mPV = new CompanyPermission;
						$mPV->company_id = $company->id;
						$mPV->permission_key = $childModuleKey;
						$mPV->permission_type = 'visible';
						
						if(!$mPV->save(false))
						{
							echo 'Error in Sub module Permission Visible'; exit;
						}
					}
			
					
					$childModulePermissionEdit = CompanyPermission::model()->find(array(
						'condition' => '
							company_id = :company_id
							AND permission_key = :permission_key
							AND permission_type = :permission_type
						',
						'params' => array(
							'company_id' => $company->id,
							':permission_key' => $childModuleKey,
							':permission_type' => 'edit'
						),
					));
					
					if( strpos($childModuleKey, 'field') !== false || strpos($childModuleKey, 'checkbox') !== false || strpos($childModuleKey, 'dropdown') !== false )
					{
						if(empty($childModulePermissionEdit))
						{
							$mPE = new CompanyPermission;
							$mPE->company_id = $company->id;
							$mPE->permission_key = $childModuleKey;
							$mPE->permission_type = 'edit';
							
							if(!$mPE->save(false))
							{
								echo 'Error in Sub Module Permission Edit'; exit;
							}
						}
					}
					
					$childModulePermissionDirectReport = CompanyPermission::model()->find(array(
						'condition' => '
							company_id = :company_id
							AND permission_key = :permission_key
							AND permission_type = :permission_type
						',
						'params' => array(
							'company_id' => $company->id,
							':permission_key' => $childModuleKey,
							':permission_type' => 'only_for_direct_reports'
						),
					));
					
					if( isset($childModule['has_direct_report_checkbox']) )
					{
						if(empty($childModulePermissionDirectReport))
						{
							$mPR = new CompanyPermission;
							$mPR->company_id = $company->id;
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
	
	public static function permissionKeys()
	{
		$defaultModules = array(
			'customer_dashboard_tab' => array(
				'label' => 'Dashboard (tab)',
				'subModules' => array(
					'customer_dashboard_recertify_recycle_names_button' => array('label' => 'Recertify/Recycle Names (button)'),
					'customer_dashboard_schedule_conflict_action_button' => array('label' => 'Schedule Conflict Action (button)'),
					'customer_dashboard_location_conflict_action_button' => array('label' => 'Location Conflict Action (button)'),
				)
			),
			'customer_learning_center_tab' => array(
				'label' => 'Resource Center (tab)',
			),
			'customer_calendar_tab' => array(
				'label' => 'Calendar (button)',
				'subModules' => array(
					'customer_calendar_manage_schedule_button' => array('label' => 'Manage Schedule (button)'),
					'customer_calendar_settings_button' => array('label' => 'Settings (button)'),
					'customer_calendar_action_form' => array('label' => 'Calendar Action Form'),
				),
			),
			'customer_leads_tab' => array(
				'label' => 'Leads (tab)',
				'subModules' => array(
					'customer_leads_create_new_list_dropdown' => array('label' => 'Create New List (button)'),
					'customer_leads_list_details_page' => array('label' => 'List Details (page)'),
					'customer_leads_add_additional_leads_button' => array('label' => 'Add Additional Leads (button)'),
					'customer_leads_download_list_button' => array('label' => 'Download List (button)'),
					'customer_leads_delete_list_button' => array('label' => 'Delete List (button)'),
					'customer_leads_recertify_button' => array('label' => 'Recertify (button)'),
					'customer_leads_lead_edit_button' => array('label' => 'Lead Edit (button)'),
					'customer_leads_lead_delete_button' => array('label' => 'Lead Delete (button)'),
					'customer_leads_lead_remove_button' => array('label' => 'Lead Remove (button)'),
				),
			),
			'customer_reports_tab' => array(
				'label' => 'Reports (tab)',
			),
			'customer_billing_tab' => array(
				'label' => 'Billing (tab)',
				'subModules' => array(
					'customer_billing_add_new_credit_card_button' => array('label' => 'Add New Credit Card (button)'),
					'customer_billing_add_new_echeck_button' => array('label' => 'Add eCheck (button)'),
					'customer_billing_add_new_credit_button' => array('label' => 'Add New Credit (button)'),
					'customer_billing_process_charge_button' => array('label' => 'Process Charge (button)'),
					'customer_billing_process_set_as_default_link' => array('label' => 'Set as Default (link)'),
					'customer_billing_download_button' => array('label' => 'Download (button)'),
					'customer_billing_action_button' => array('label' => 'Action (button)'),
					'customer_billing_credits_delete_link' => array('label' => 'Credits Delete (link)'),
				),
			),
			'customer_skills_tab' => array(
				'label' => 'Skills (tab)',
				'subModules' => array(
					'customer_skills_add_skills_tab' => array('label' => 'Add Skills (tab)'),
					'customer_skills_contract_tab' => array(
						'label' => 'Contract (tab)',
						'subModules' => array(
							'customer_skills_contract_start_date_field' => array('label' => 'Start Date (field)'),
							'customer_skills_contract_end_date_field' => array('label' => 'End Date (field)'),
							'customer_skills_contract_hold_period_on_off_button' => array('label' => 'Contract Hold Period On/Off (button)'),
							'customer_skills_contract_save_button' => array('label' => 'Save (button)'),
							// 'customer_skills_contract_contract_on_off_button' => array('label' => 'Contract On/Off (button)'),
							'customer_skills_contract_quantity_on_off_button' => array('label' => 'Quantity On/Off (button)'),
							'customer_skills_contract_subsidy_on_off_button' => array('label' => 'Subsidy On/Off (button)'),
							'customer_skills_contract_subsidy_level_dropdown' => array('label' => 'Subsidy Level (dropdown)'),
						),
					),
					'customer_skills_skill_child_tab' => array(
						'label' => 'Skills Child (tab)',
						'subModules' => array(
							'customer_skills_skill_child_on_off_button' => array('label' => 'Skill Child On/Off (button)')
						),
					),
					'customer_skills_custom_call_schedule_tab' => array(
						'label' => 'Custom Call Schedule (tab)',
						'subModules' => array(
							'customer_skills_custom_call_schedule_on_off_button' => array('label' => 'Custom Call Schedule On/Off (button)')
						),
					),
					'customer_skills_dialing_settings_tab' => array(
						'label' => 'Dialing Settings (tab)',
						'subModules' => array(
							// 'customer_skills_dialing_settings_button' => array('label' => 'Dial Settings (button)'),
							'customer_skills_save_dialing_settings_button' => array('label' => 'Save Dialing Settings (button)'),
						),
					),
					'customer_skills_extra_tab' => array(
						'label' => 'Extra (tab)',
						'subModules' => array(
							'customer_skills_extra_add_button' => array('label' => 'Add (button)'),
							'customer_skills_extra_edit_button' => array('label' => 'Edit (button)'),
							'customer_skills_extra_remove_button' => array('label' => 'Remove (button)'),
						),
					),
					'customer_skills_remove_skill_button' => array(
						'label' => 'Remove Skill (button)',
					),
				),
			),
			'customer_setup_tab' => array(
				'label' => 'Setup (tab)',
				'subModules' => array(
					'customer_setup_all_fields' => array('label' => 'All fields (except those listed below)'),
					'customer_company_dropdown' => array('label' => 'Company (dropdown)'),
					'customer_setup_sales_rep_dropdown' => array('label' => 'Sales Reps (dropdown)'),
					'customer_setup_customer_notes_field' => array('label' => 'Customer Notes (field)'),
					'customer_setup_customer_save_button' => array('label' => 'Save (button)'),
				),
			),
			'customer_offices_tab' => array(
				'label' => 'Offices (tab)',
				'subModules' => array(
					'customer_offices_add_office' => array('label' => 'Add Office (tab)'),
					'customer_offices_office_settings_all_fields' => array('label' => 'Office Settings (all fields)'),
					'customer_offices_office_settings_save_button' => array('label' => 'Office Settings Save (button)'),
					'customer_offices_staff_list_settings_all_fields' => array('label' => 'Staff List Settings (all fields)'),
					'customer_offices_staff_list_settings_save_button' => array('label' => 'Staff List Settings Save (button)'),
					'customer_offices_add_new_staff_button' => array('label' => 'Add New Staff (button)'),
					'customer_offices_add_existing_staff_button' => array('label' => 'Add Existing Staff (button)'),
					'customer_offices_calendar_view_link' => array('label' => 'Calendar View (link)'),
					'customer_offices_calendar_edit_link' => array('label' => 'Calendar Edit (link)'),
					'customer_offices_calendar_delete_link' => array('label' => 'Calendar Delete (link)'),
					'customer_offices_add_calendar_button' => array('label' => 'Add Calendar (button)'),
				),
			),
			'customer_my_files_tab' => array(
				'label' => 'My Files (tab)',
				'subModules' => array(
					'customer_my_files_click_here_to_upload' => array('label' => 'Click here to upload'),
					'customer_my_files_download_link' => array('label' => 'File Names (download link)'),
					'customer_my_files_delete_button' => array('label' => 'Delete (button)'),
				),
			),
			'customer_history_tab' => array(
				'label' => 'History (tab)',
				'subModules' => array(
					'customer_history_show_audit_records_button' => array('label' => 'Show Audit Records (button)'),
					'customer_history_add_record' => array('label' => 'Add Record (with field and 2 buttons)'),
					'customer_history_manual_notes' => array('label' => 'Manual Notes (view manually entered notes)'),
				),
			),
			'customer_permission_tab' => array(
				'label' => 'Permission (tab)',
			),
			
		);
		
		// foreach( $defaultModules as $moduleKey => $module )
		// {
			// $modulePermissionVisible = CompanyPermission::model()->find(array(
				// 'condition' => '
					// company_id = :company_id
					// AND permission_key = :permission_key
					// AND permission_type = :permission_type
				// ',
				// 'params' => array(
					// 'company_id' => $company_id,
					// ':permission_key' => $moduleKey,
					// ':permission_type' => 'visible'
				// ),
			// ));
			
			// if( $modulePermissionVisible )
			// {
				// $companyModules[$moduleKey]['label'] = $module['label'];
				
				// $companyModules['subModules'] = $module['subModules'];
				
				// if( !empty($module['subModules']) )
				// {
					// foreach( $module['subModules'] as $childModuleKey => $childModule )
					// {
						// $childModulePermissionVisible = CompanyPermission::model()->find(array(
							// 'condition' => '
								// company_id = :company_id
								// AND permission_key = :permission_key
								// AND permission_type = :permission_type
							// ',
							// 'params' => array(
								// 'company_id' => $company_id,
								// ':permission_key' => $childModuleKey,
								// ':permission_type' => 'visible'
							// ),
						// ));
						
						// if( $childModulePermissionVisible )
						// {
							// $companyModules['subModules'][$childModuleKey]['label'] = $childModule['label'];
						// }
					// }
				// }
			// }
		// }
		
		// return $companyModules;
		
		return $defaultModules;
	}
}
