<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	$cs = Yii::app()->clientScript;

	$cs->registerCss(uniqid(), '
	
		.dd{ max-width:100% !important; } 
		span.checkbox{ display:inline !important; }
	');
	
	$cs->registerScriptFile($baseUrl.'/template_assets/js/jquery.nestable.min.js');

	$cs->registerScript(uniqid(), "
		
		$(document).ready( function(){
			
			$('#nestable').nestable({
				noDragClass: 'dd-nodrag',
			});
	
			$('.dd-nodrag').on('mousedown', function(e){
				e.stopPropagation();
			});
			
			$('[data-rel=\"tooltip\"]').tooltip();
			
			$('#nestable').nestable('collapseAll');
		});

	", CClientScript::POS_END);
	
	$cs->registerScript(uniqid(), '
		
		$(document).ready( function(){
			
			$(document).on("change", ".permission-checkbox", function(){
				
				var value;
				var account_type_id = "'.$id.'";
				var permission_key = $(this).prop("id");
				var permission_type = $(this).attr("permission_type");
				
				if( $(this).is(":checked") )
				{
					value = 1;
				}
				else
				{
					value = 0;
				}
				
				$.ajax({
					url: "'.$this->createUrl('update').'",
					type: "post",
					dataType: "json", 
					data: {
						"ajax":1,
						"account_type_id": account_type_id,				
						"permission_key": permission_key, 
						"permission_type": permission_type,
						"value": value
					},
					success: function(response){
						
						console.log(response.status);
						
					},
				});
			});
			
		});
	
	', CClientScript::POS_END);
	
	$modules = array(
		'structure_main_tab' => array(
			'label' => 'Structure (Main Tab)',
			'subModules' => array(
				'structure_companies_tab' => array(
					'label' => 'Companies (tab)',
					'subModules' => array(
						'structure_companies_add_button' => array('label' => 'Add Companies (button)'),
						'structure_companies_edit_button' => array('label' => 'Edit (button)'),
						'structure_companies_delete_button' => array('label' => 'Delete (button)'),
					),
				),
				'structure_skills_tab' => array(
					'label' => 'Skills (tab)',
					'subModules' => array(
						'structure_skills_add_button' => array('label' => 'Add Skills (button)'),
						'structure_skills_edit_button' => array('label' => 'Edit (button)'),
						'structure_skills_delete_button' => array('label' => 'Delete (button)'),
						'structure_skills_disposition_link' => array(
							'label' => 'Disposition (link)',
							'subModules' => array(
								'structure_skills_disposition_add_button' => array('label' => 'Add Skill Disposition (button)'),
								'structure_skills_disposition_clone_button' => array('label' => 'Clone Skill Disposition (button)'),
								'structure_skills_disposition_edit_button' => array('label' => 'Edit (button)'),
								'structure_skills_disposition_sub_dispo_button' => array('label' => 'Sub Dispo (button)'),
								'structure_skills_disposition_email_settings_button' => array('label' => 'Email Settings (button)'),
								'structure_skills_disposition_text_settings_button' => array('label' => 'Text Settings (button)'),
								'structure_skills_disposition_delete_button' => array('label' => 'Delete (button)'),
							),
						),
					),
				),
				'structure_campaign_tab' => array(
					'label' => 'Campaign (tab)',
					'subModules' => array(
						'structure_campaign_add_button' => array('label' => 'Add Campaign (button)'),
						'structure_campaign_edit_button' => array('label' => 'Edit (button)'),
						'structure_campaign_delete_button' => array('label' => 'Delete (button)'),
					),
				),
				'structure_contract_tab' => array(
					'label' => 'Contract (tab)',
					'subModules' => array(
						'structure_contract_add_button' => array('label' => 'Add Contract (button)'),
						'structure_contract_edit_button' => array('label' => 'Edit (button)'),
						'structure_contract_delete_button' => array('label' => 'Delete (button)'),
					),
				),
				'structure_enrollment_tab' => array(
					'label' => 'Enrollment (tab)',
				),
				'structure_dnc_holidays_tab' => array(
					'label' => 'DNC Holidays (tab)',
				),
				'structure_state_cellphone_dnc_tab' => array(
					'label' => 'State Cellphone DNC (tab)',
				),
				'structure_state_schedule_tab' => array(
					'label' => 'State Schedule (tab)',
				),
				'structure_survey_tab' => array(
					'label' => 'Surveys (tab)',
					'subModules' => array(
						'structure_survey_add_button' => array('label' => 'Add (button)'),
						'structure_survey_edit_button' => array('label' => 'Edit (button)'),
						'structure_survey_delete_button' => array('label' => 'Delete (button)'),
					),
				),
				'structure_phone_search_tab' => array(
					'label' => 'Search Phone Number (tab)',
				)
			),
		),
		'customers_main_tab' => array(
			'label' => 'Customers (Main Tab)', 
			'subModules' => array(
				'customer_add_new_button' => array(
					'label' => 'Add new Customer (button)',
				),
				'customer_list_of_staff_button' => array(
					'label' => 'List of staff (button)',
				),
				'customer_account_details_button' => array(
					'label' => 'Account Details (button)',
					'subModules' => array(
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
								'customer_leads_import_settings_button' => array('label' => 'Import Settings (button)'),
								'customer_leads_list_details_page' => array('label' => 'List Details (page)'),
								'customer_leads_add_additional_leads_button' => array('label' => 'Add Additional Leads (button)'),
								'customer_leads_download_list_button' => array('label' => 'Download List (button)'),
								'customer_leads_delete_list_button' => array('label' => 'Delete List (button)'),
								'customer_leads_recertify_button' => array('label' => 'Recertify (button)'),
								'customer_leads_lead_edit_button' => array('label' => 'Lead Edit (button)'),
								'customer_leads_lead_delete_button' => array('label' => 'Lead Delete (button)'),
								'customer_leads_lead_remove_button' => array('label' => 'Lead Remove (button)'),
								'customer_leads_list_delete_names_waiting_button' => array('label' => 'Delete Names Waiting (button)'),
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
								'customer_skills_script_tab' => array(
									'label' => 'Script (tab)',
									'subModules' => array(
									),
								),
								'customer_skills_remove_skill_button' => array(
									'label' => 'Remove Skill (button)',
								),
								'customer_skills_cancel_skill_button' => array(
									'label' => 'Cancel Skill (button)',
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
						)
					),
				)
			),
		),
		'employees_main_tab' => array(
			'label' => 'Employees (Main Tab)', 
			'subModules' => array(
				'employees_add_users_button' => array('label' => 'Add Users (button)'),
				'employees_settings_button' => array('label' => 'Settings (button)'),
				'employees_employees_tab' => array(
					'label' => 'Employees (tab)',
					'subModules' => array(
						'employees_employee_details_button' => array(
							'label' => 'Employee Details (button)',
							'subModules' => array(
								'employees_employee_profile_tab' => array(
									'label' => 'Employee Profile (tab)',
									'subModules' => array(
										'employees_employee_profile_active_employee_checkbox' => array('label' => 'Active Employee (checkbox)', 'has_direct_report_checkbox' => true),
										'employees_employee_profile_use_webphone_checkbox' => array('label' => 'Use Webphone (checkbox)', 'has_direct_report_checkbox' => true),
										'employees_employee_profile_sip_username_field' => array('label' => 'Webphone Username (field)', 'has_direct_report_checkbox' => true),
										'employees_employee_profile_sip_password_field' => array('label' => 'Webphone Password (field)', 'has_direct_report_checkbox' => true),
										'employees_employee_profile_start_date_field' => array('label' => 'Start Date (field)', 'has_direct_report_checkbox' => true),
										'employees_employee_profile_termination_date_field' => array('label' => 'Termination Date (field)', 'has_direct_report_checkbox' => true),
										'employees_employee_profile_job_title_dropdown' => array('label' => 'Job Title (dropdown)', 'has_direct_report_checkbox' => true),
										'employees_employee_profile_phone_extension_field' => array('label' => 'Phone Extension (field)', 'has_direct_report_checkbox' => true),
										'employees_employee_profile_reports_to_dropdown' => array('label' => 'Reports to (dropdown)', 'has_direct_report_checkbox' => true),
										'employees_employee_profile_employee_portal_access_checkbox' => array('label' => 'Employee Portal Access (checkbox)', 'has_direct_report_checkbox' => true),
										'employees_employee_profile_employee_number_field' => array('label' => 'Employee # (field)', 'has_direct_report_checkbox' => true),
										'employees_employee_profile_badge_id_field' => array('label' => 'Badge ID (field)', 'has_direct_report_checkbox' => true),
										'employees_employee_profile_first_name_field' => array('label' => 'First name (field)', 'has_direct_report_checkbox' => true),
										'employees_employee_profile_last_name_field' => array('label' => 'Last name (field)', 'has_direct_report_checkbox' => true),
										'employees_employee_profile_address_field' => array('label' => 'Address (field)', 'has_direct_report_checkbox' => true),
										'employees_employee_profile_phone_number_field' => array('label' => 'Phone Number (field)', 'has_direct_report_checkbox' => true),
										'employees_employee_profile_mobile_number_field' => array('label' => 'Mobile Number (field)', 'has_direct_report_checkbox' => true),
										'employees_employee_profile_email_address_field' => array('label' => 'Email Address (field)', 'has_direct_report_checkbox' => true),
										'employees_employee_profile_emergency_contact_field' => array('label' => 'Emergency Contact (field)', 'has_direct_report_checkbox' => true),
										'employees_employee_profile_social_security_number_field' => array('label' => 'Social Security Number (field)', 'has_direct_report_checkbox' => true),
										'employees_employee_profile_birthday_field' => array('label' => 'Date of Birth (field)', 'has_direct_report_checkbox' => true),
										'employees_employee_profile_full_time_status_dropdown' => array('label' => 'Full-Time Status (dropdown)', 'has_direct_report_checkbox' => true),
										'employees_employee_profile_hour_salary_dropdown' => array('label' => 'Hour/Salary (dropdown)', 'has_direct_report_checkbox' => true),
										'employees_employee_profile_pay_rate_field' => array('label' => 'Pay Rate (field)', 'has_direct_report_checkbox' => true),
										'employees_employee_profile_commission_rate_field' => array('label' => 'Commission Rate (field)', 'has_direct_report_checkbox' => true),
										'employees_employee_profile_security_group_dropdown' => array('label' => 'Security Group (dropdown)', 'has_direct_report_checkbox' => true),
										'employees_employee_profile_username_field' => array('label' => 'Username (field)', 'has_direct_report_checkbox' => true),
										'employees_employee_profile_password_field' => array('label' => 'Password (field)', 'has_direct_report_checkbox' => true),
										'employees_employee_profile_confirm_password_field' => array('label' => 'Confirm Password (field)', 'has_direct_report_checkbox' => true),
										'employees_employee_profile_save_button' => array('label' => 'Save (button)', 'has_direct_report_checkbox' => true),
									),
									'has_direct_report_checkbox' => true,
								),
								'employees_employee_file_tab' => array(
									'label' => 'Employee File (tab)',
									'subModules' => array(
										'employees_employee_file_upload_button' => array('label' => 'Upload (button)', 'has_direct_report_checkbox' => true),
										'employees_employee_file_download_link' => array('label' => 'File Name (download link)', 'has_direct_report_checkbox' => true),
										'employees_employee_file_delete_button' => array('label' => 'Delete (button)', 'has_direct_report_checkbox' => true),
										'employees_employee_file_export_history_button' => array('label' => 'Export History (button)', 'has_direct_report_checkbox' => true),
										'employees_employee_file_attachments_button' => array('label' => 'Attachments (button)', 'has_direct_report_checkbox' => true),
										'employees_employee_file_submit_button' => array('label' => 'Submit (button)', 'has_direct_report_checkbox' => true),
										'employees_employee_file_view_history_list' => array('label' => ' View History (list )', 'has_direct_report_checkbox' => true),
										'employees_employee_file_document_types_button' => array('label' => ' Document Types (button )'),
									),
									'has_direct_report_checkbox' => true,
								),
								'employees_time_keeping_tab' => array(
									'label' => 'Time Keeping (tab)',
									'subModules' => array(
										'employees_time_keeping_approve_pto_button' => array('label' => 'Approve PTO (button)', 'has_direct_report_checkbox' => true),
										'employees_time_keeping_deny_pto_button' => array('label' => 'Deny PTO (button)', 'has_direct_report_checkbox' => true),
										'employees_time_keeping_add_pto_button' => array('label' => 'Add PTO (button)', 'has_direct_report_checkbox' => true),
										'employees_time_keeping_merge_button' => array('label' => 'Merge (button)', 'has_direct_report_checkbox' => true),
										'employees_time_keeping_add_button' => array('label' => 'Add (button)', 'has_direct_report_checkbox' => true),
										'employees_time_keeping_delete_button' => array('label' => 'Delete (button)', 'has_direct_report_checkbox' => true),
										'employees_time_keeping_edit_button' => array('label' => 'Edit (button)', 'has_direct_report_checkbox' => true),
										'employees_time_keeping_action_button' => array('label' => 'Action (button)', 'has_direct_report_checkbox' => true),
										'employees_time_keeping_export_button' => array('label' => 'Export (button)', 'has_direct_report_checkbox' => true),
										'employees_time_keeping_calendar' => array('label' => 'Calendar (calendar)', 'has_direct_report_checkbox' => true),
									),
									'has_direct_report_checkbox' => true,
								),
								'employees_assigments_tab' => array(
									'label' => 'Assignments (tab)',
									'subModules' => array(
										'employees_assigments_languages' => array('label' => 'Languages', 'has_direct_report_checkbox' => true),
										'employees_assigments_skills' => array('label' => 'Skills', 'has_direct_report_checkbox' => true),
										'employees_assigments_child_skills' => array('label' => 'Child Skills', 'has_direct_report_checkbox' => true),
									),
									'has_direct_report_checkbox' => true,
								),
								'employees_performance_tab' => array(
									'label' => 'Performance (tab)',
									'subModules' => array(
										'employees_performance_agent_stats' => array('label' => 'Agent Stats', 'has_direct_report_checkbox' => true),
										'employees_performance_call_agent_history' => array('label' => 'Call Agent History', 'has_direct_report_checkbox' => true),
										'employees_performance_recording_link' => array('label' => 'Recording Link (link)', 'has_direct_report_checkbox' => true),
									),
									'has_direct_report_checkbox' => true,
								),
							),
							'has_direct_report_checkbox' => true,
						),
					),
				),
				'employees_hostdial_users_tab' => array(
					'label' => 'Hostdial Users (tab)',
					'subModules' => array(
						'employees_hostdial_users_details_button' => array(
							'label' => 'User Details (button)',
							'has_direct_report_checkbox' => true,
						),
					),
				),
				'employees_permissions_tab' => array('label' => 'Permissions (tab)'),
				'employees_teams_tab' => array('label' => 'Teams (tab)'),
				'employees_news_tab' => array('label' => 'News (tab)'),
				'employees_training_library_tab' => array('label' => 'Training Library (tab)'),
				'employees_texting_main_tab' => array('label' => 'Texting (tab)'),
			),
		),
		'reports_main_tab' => array(
			'label' => 'Reports (Main Tab)', 
			'subModules' => array(
				'reports_real_time_monitors_tab' => array(
					'label' => 'Real-Time Monitors (tab)',
					'subModules' => array(
						'reports_real_time_monitors_email_monitor_button' => array('label' => 'Email Monitor (button)'),
						'reports_real_time_monitors_queue_viewer_button' => array('label' => 'Queue Viewer (button)'),
						'reports_real_time_monitors_employee_state_button' => array('label' => 'Employee State (button)'),
						'reports_real_time_monitors_call_management_button' => array('label' => 'Call Management (button)'),
					),
				),
				'reports_reports_tab' => array(
					'label' => 'Reports (tab)',
					'subModules' => array(
						'reports_reports_customer_contact_info' => array('label' => 'Customer Contact Info'),
						'reports_reports_customers_with_files' => array('label' => 'Customer with Files'),
						'reports_reports_credit_card_transactions' => array('label' => 'Credit Card Transactions'),
						'reports_reports_billing_projections' => array('label' => 'Billing Projections'),
						'reports_reports_contract_leads' => array('label' => 'Contract Leads'),
						'reports_reports_agent_performance' => array('label' => 'Agent Performance'),
						'reports_reports_agent_performance_lite' => array('label' => 'Agent Performance Lite'),
						'reports_reports_queue_listing' => array('label' => 'Queue Listing'),
						'reports_reports_confirmations' => array('label' => 'Confirmations'),
						'reports_reports_reschedules' => array('label' => 'Reschedules'),
						'reports_reports_employee_summary' => array('label' => 'Employee Summary'),
						'reports_reports_generic_skill' => array('label' => 'Generic Skill'),
						'reports_reports_change_log' => array('label' => 'Change Log'),
						'reports_reports_low_names' => array('label' => 'Low Names'),
						'reports_reports_impact' => array('label' => 'Impact'),
						'reports_reports_list_import_log' => array('label' => 'List Import Log'),
						'reports_reports_agent_states' => array('label' => 'Agent States'),
						'reports_reports_commision' => array('label' => 'Commision'),
						'reports_reports_time_zones' => array('label' => 'Time Zones'),
						'reports_reports_time_off' => array('label' => 'Time Off'),
						'reports_reports_news' => array('label' => 'News'),
						'reports_reports_learning_center_usage' => array('label' => 'Resource Center Report'),
						'reports_reports_training_library_usage' => array('label' => 'Training Library Usage'),
						'reports_reports_no_show_usage' => array('label' => 'No Show'),
						'reports_reports_dnc' => array('label' => 'Do Not Call'),
						'reports_reports_dnc_master_list' => array('label' => 'Master DNC Listing'),
						'reports_reports_custom_data' => array('label' => 'Custom Data'),
						'reports_reports_customer_company_wndnc' => array('label' => 'Company-Customer DNC/WN'),
						'reports_reports_cellphone_scrub' => array('label' => 'Cellphone Scrub Report'),
						'reports_reports_document_type' => array('label' => 'Document Type Report'),
						'reports_reports_cancellation' => array('label' => 'Cancellation Report'),
						'reports_reports_extra_appt' => array('label' => 'Extra Appt'),
						'reports_reports_master_schedule' => array('label' => 'Master Schedule'),
					),
				),
				'reports_caller_id_listing_tab' => array(
					'label' => 'Caller ID Listing (tab)',
					'subModules' => array(
						'reports_caller_id_listing_remove_button' => array('label' => 'Remove (button)'),
						'reports_caller_id_listing_assigned_customers_link' => array('label' => 'Assigned customers (link)'),
					),
				),
				'reports_call_history_monitor_tab' => array('label' => 'Call History Monitor (tab)')
			),
		),
		'accounting_main_tab' => array(
			'label' => 'Accounting (Main Tab)', 
			'subModules' => array(
				// 'accounting_enrollment_tab' => array('label' => 'Enrollment (tab)'),
				'accounting_billing_windows_tab' => array(
					'label' => 'Billing Windows (tab)',
					'subModules' => array(
						'accounting_billing_windows_pending_tab' => array(
							'label' => 'Pending (tab)',
							'subModules' => array(
								'accounting_billing_windows_pending_export_button' => array('label' => 'Export to Excel (button)'),
								'accounting_billing_windows_pending_charge_button' => array('label' => 'Charge (button)'),
								'accounting_billing_windows_pending_remove_button' => array('label' => 'Remove (button)'),
								'accounting_billing_windows_pending_hold_button' => array('label' => 'Hold (button)'),
							),
						),
						'accounting_billing_windows_decline_tab' => array(
							'label' => 'Decline (tab)',
							'subModules' => array(
								'accounting_billing_windows_decline_export_button' => array('label' => 'Export to Excel (button)'),
								'accounting_billing_windows_decline_charge_button' => array('label' => 'Charge (button)'),
								'accounting_billing_windows_decline_write_off_button' => array('label' => 'Write-Off (button)'),
							),
						),
					),
				),
				'accounting_payroll_file_tab' => array(
					'label' => 'Payroll File (tab)',
					'subModules' => array(
						'accounting_payroll_file_export_button' => array('label' => 'Export Payroll File (button)'),
					),
				),
				'accounting_enrollment_listing_tab' => array('label' => 'Enrollment Listing (tab)'),
				'accounting_sales_goals_tab' => array('label' => 'Sales Goals (tab)'),
				'accounting_exception_punches_tab' => array(
					'label' => 'Exception Punches (tab)',
					'subModules' => array(
						'accounting_exception_punches_edit_button' => array('label' => 'Edit (button)'),
						'accounting_exception_punches_action_button' => array('label' => 'Action (button)'),
					),
				),				
			),
		),
		'news_main_tab' => array(
			'label' => 'News (Main Tab)', 
		),
		'training_library_main_tab' => array(
			'label' => 'Training Library (Main Tab)', 
		),
		'dual_access_dialer_crm' => array(
			'label' => 'Dual Access Dialer/CRM'
		),
		'ip_restriction' => array(
			'label' => 'IP Restriction'
		),
	);
?>

<div class="tabbable tabs-left">

	<ul id="myTab" class="nav nav-tabs">
		<?php if( Yii::app()->user->account->checkPermission('employees_employees_tab','visible') ){ ?>
		
		<li class="<?php echo Yii::app()->getController()->getId() == 'accountUser' && Yii::app()->controller->action->id == 'index'  ? 'active' : ''; ?>">
			<a href="<?php echo $this->createUrl('accountUser/index'); ?>">
				Employees
			</a>
		</li>
		
		<?php } ?>
		
		<?php if( Yii::app()->user->account->checkPermission('employees_hostdial_users_tab','visible') ){ ?>
		
		<li class="<?php echo Yii::app()->controller->action->id == 'hostdialUser' ? 'active' : ''; ?>">
			<a href="<?php echo $this->createUrl('accountUser/hostdialUser'); ?>">
				Hostdial Users
			</a>
		</li>
		
		<?php } ?>
		
		<?php if( Yii::app()->user->account->checkPermission('employees_permissions_tab','visible') ){ ?>
		
			<li class="<?php echo Yii::app()->getController()->getId() == 'permission' ? 'active' : ''; ?>">
				<a href="<?php echo $this->createUrl('/hr/permission'); ?>">
					Permissions
				</a>
			</li>
		<?php } ?>

		<?php if( Yii::app()->user->account->checkPermission('employees_teams_tab','visible') ){ ?>
		
			<li class="<?php echo Yii::app()->getController()->getId() == 'team' ? 'active' : ''; ?>">
				<a href="<?php echo $this->createUrl('/hr/team'); ?>">
					Teams
				</a>
			</li>
			
		<?php } ?>
		
		<?php if( Yii::app()->user->account->checkPermission('employees_news_tab','visible') ){ ?>
		
			<li class="<?php echo Yii::app()->getController()->getId() == 'news' ? 'active' : ''; ?>">
				<a href="<?php echo $this->createUrl('/hr/news'); ?>">
					News
				</a>
			</li>
		
		<?php } ?>
		
		<?php if( Yii::app()->user->account->checkPermission('training_library_main_tab','visible') ){ ?>
		
			<li class="<?php echo Yii::app()->getController()->getId() == 'trainingLibrary' ? 'active' : ''; ?>">
				<a href="<?php echo $this->createUrl('/hr/trainingLibrary'); ?>">
					Training Library
				</a>
			</li>
		
		<?php } ?>
		
		<?php if( Yii::app()->user->account->checkPermission('employees_texting_main_tab','visible') ){ ?>
		
			<li class="<?php echo Yii::app()->getController()->getId() == 'texting' ? 'active' : ''; ?>">
				<a href="<?php echo $this->createUrl('/hr/texting'); ?>">
					Texting
				</a>
			</li>
		
		<?php } ?>
	</ul>
	
	<div class="tab-content">
		<?php
			foreach(Yii::app()->user->getFlashes() as $key => $message) {
				echo '<div class="alert alert-' . $key . '"><button data-dismiss="alert" class="close" type="button"><i class="ace-icon fa fa-times"></i></button> ' . $message . "</div>\n";
			}
		?>
		
		<div class="row">
			<div class="page-header">
				<h1><?php echo $securityGroups[$id]; ?> Permissions 
					<label>
						<input id="security_group_<?php echo strtolower($securityGroups[$id]); ?>_master" class="ace ace-switch permission-checkbox" type="checkbox" value="1" permission_type="master_switch" <?php echo !empty($securityGroupPermissionSwitch) ? "checked" : ""; ?>>
						<span class="lbl"></span>
					</label>
				</h1>
			</div>
		</div>
		
		<div class="row">
			<div class="col-xs-12">
				<!-- PAGE CONTENT BEGINS -->
				<div class="row">
					<div class="col-sm-9">
						<div class="dd dd-nodrag" id="nestable"> 
							<ol class="dd-list">

								<?php 
									foreach($modules as $moduleKey => $module)
									{
										$modulePermissionVisible = AccountPermission::model()->find(array(
											'condition' => '
												account_type_id = :account_type_id
												AND permission_key = :permission_key
												AND permission_type = :permission_type
											',
											'params' => array(
												'account_type_id' => $id,
												':permission_key' => $moduleKey,
												':permission_type' => 'visible'
											),
										));
										
										$modulePermissionEdit = AccountPermission::model()->find(array(
											'condition' => '
												account_type_id = :account_type_id
												AND permission_key = :permission_key
												AND permission_type = :permission_type
											',
											'params' => array(
												'account_type_id' => $id,
												':permission_key' => $moduleKey,
												':permission_type' => 'edit'
											),
										));
										
										$modulePermissionDirectReport = AccountPermission::model()->find(array(
											'condition' => '
												account_type_id = :account_type_id
												AND permission_key = :permission_key
												AND permission_type = :permission_type
											',
											'params' => array(
												'account_type_id' => $id,
												':permission_key' => $moduleKey,
												':permission_type' => 'only_for_direct_reports'
											),
										));
										
									?>
										<li class="dd-item dd-nodrag" data-id="<?php echo $moduleKey.'-'; ?>">
											<div class="dd-handle">
												<?php echo $module['label']; ?>
												
												<div class="pull-right action-buttons">
													<span class="checkbox">
														<label>
															<input id="<?php echo $moduleKey; ?>" class="ace permission-checkbox" type="checkbox" value="1" permission_type="visible" <?php echo !empty($modulePermissionVisible) ? "checked" : ""; ?>>
															<span class="lbl"> <?php echo $moduleKey == 'ip_restriction' ? 'Enforce' : 'Visible'; ?></span>
														</label>
													</span>
													
													<?php if( strpos($moduleKey, 'field') !== false || strpos($moduleKey, 'checkbox') !== false || strpos($moduleKey, 'dropdown') !== false ) { ?>
													<span class="checkbox">
														<label>
															<input id="<?php echo $moduleKey; ?>" class="ace permission-checkbox" type="checkbox" value="1" permission_type="edit" <?php echo !empty($modulePermissionEdit) ? "checked" : ""; ?>>
															<span class="lbl"> Edit</span>
														</label>
													</span>
													<?php } ?>
													
													<?php if( isset($module['has_direct_report_checkbox']) ) { ?>
													<span class="checkbox">
														<label>
															<input id="<?php echo $moduleKey; ?>" class="ace permission-checkbox" type="checkbox" value="1" permission_type="only_for_direct_reports" <?php echo !empty($modulePermissionDirectReport) ? "checked" : ""; ?>>
															<span class="lbl"> Only For Direct Reports</span>
														</label>
													</span>
													<?php } ?>
												</div>
											</div>
												
											<?php 
												if( !empty($module['subModules']) )
												{
												?>
													<ol class="dd-list">
												
													<?php
														foreach( $module['subModules'] as $childModuleKey => $childModule )
														{
															$childModulePermissionVisible = AccountPermission::model()->find(array(
																'condition' => '
																	account_type_id = :account_type_id
																	AND permission_key = :permission_key
																	AND permission_type = :permission_type
																',
																'params' => array(
																	'account_type_id' => $id,
																	':permission_key' => $childModuleKey,
																	':permission_type' => 'visible'
																),
															));
															
															$childModulePermissionEdit = AccountPermission::model()->find(array(
																'condition' => '
																	account_type_id = :account_type_id
																	AND permission_key = :permission_key
																	AND permission_type = :permission_type
																',
																'params' => array(
																	'account_type_id' => $id,
																	':permission_key' => $childModuleKey,
																	':permission_type' => 'edit'
																),
															));
															
															$childModulePermissionDirectReport = AccountPermission::model()->find(array(
																'condition' => '
																	account_type_id = :account_type_id
																	AND permission_key = :permission_key
																	AND permission_type = :permission_type
																',
																'params' => array(
																	'account_type_id' => $id,
																	':permission_key' => $childModuleKey,
																	':permission_type' => 'only_for_direct_reports'
																),
															));
														?>
															<li class="dd-item dd-nodrag" data-id="<?php echo $childModuleKey ?>">
																<div class="dd-handle">
																	<?php echo $childModule['label']; ?>
																	
																	<div class="pull-right action-buttons">
																		<span class="checkbox">
																			<label>
																				<input id="<?php echo $childModuleKey; ?>" class="ace permission-checkbox" type="checkbox" value="1" permission_type="visible" <?php echo !empty($childModulePermissionVisible) ? "checked" : ""; ?>>
																				<span class="lbl"> Visible</span>
																			</label>
																		</span>
																		
																		<?php if( strpos($childModuleKey, 'field') !== false || strpos($childModuleKey, 'checkbox') !== false || strpos($childModuleKey, 'dropdown') !== false ) { ?>
																		<span class="checkbox">
																			<label>
																				<input id="<?php echo $childModuleKey; ?>" class="ace permission-checkbox" type="checkbox" value="1" permission_type="edit" <?php echo !empty($childModulePermissionEdit) ? "checked" : ""; ?>>
																				<span class="lbl"> Edit</span>
																			</label>
																		</span>
																		<?php } ?>
																		
																		<?php if( isset($childModule['has_direct_report_checkbox']) ) { ?>
																		<span class="checkbox">
																			<label>
																				<input id="<?php echo $childModuleKey; ?>" class="ace permission-checkbox" type="checkbox" value="1" permission_type="only_for_direct_reports" <?php echo !empty($childModulePermissionDirectReport) ? "checked" : ""; ?>>
																				<span class="lbl"> Only For Direct Reports</span>
																			</label>
																		</span>
																		<?php } ?>
																	</div>
																</div>
																	
																<?php 
																	if( !empty($childModule['subModules']) )
																	{
																	?>
																		<ol class="dd-list">
																	
																		<?php
																			foreach( $childModule['subModules'] as $subModuleKey => $subModule )
																			{
																				$subModulePermissionVisible = AccountPermission::model()->find(array(
																					'condition' => '
																						account_type_id = :account_type_id
																						AND permission_key = :permission_key
																						AND permission_type = :permission_type
																					',
																					'params' => array(
																						'account_type_id' => $id,
																						':permission_key' => $subModuleKey,
																						':permission_type' => 'visible'
																					),
																				));
																				
																				$subModulePermissionEdit = AccountPermission::model()->find(array(
																					'condition' => '
																						account_type_id = :account_type_id
																						AND permission_key = :permission_key
																						AND permission_type = :permission_type
																					',
																					'params' => array(
																						'account_type_id' => $id,
																						':permission_key' => $subModuleKey,
																						':permission_type' => 'edit'
																					),
																				));
																				
																				$subModulePermissionDirectReport = AccountPermission::model()->find(array(
																					'condition' => '
																						account_type_id = :account_type_id
																						AND permission_key = :permission_key
																						AND permission_type = :permission_type
																					',
																					'params' => array(
																						'account_type_id' => $id,
																						':permission_key' => $subModuleKey,
																						':permission_type' => 'only_for_direct_reports'
																					),
																				));
																			?>
																				<li class="dd-item dd-nodrag" data-id="<?php echo $subModuleKey.'-'.$authAccount->id; ?>">
																					<div class="dd-handle">
																						<?php echo $subModule['label']; ?>
																						
																						<div class="pull-right action-buttons">
																							<span class="checkbox">
																								<label>
																									<input id="<?php echo $subModuleKey; ?>" class="ace permission-checkbox" type="checkbox" value="1" permission_type="visible" <?php echo !empty($subModulePermissionVisible) ? "checked" : ""; ?>>
																									<span class="lbl"> Visible</span>
																								</label>
																							</span>
																							
																							<?php if( strpos($subModuleKey, 'field') !== false || strpos($subModuleKey, 'checkbox') !== false || strpos($subModuleKey, 'dropdown') !== false ) { ?>
																							<span class="checkbox">
																								<label>
																									<input id="<?php echo $subModuleKey; ?>" class="ace permission-checkbox" type="checkbox" value="1" permission_type="edit" <?php echo !empty($subModulePermissionEdit) ? "checked" : ""; ?>>
																									<span class="lbl"> Edit</span>
																								</label>
																							</span>
																							<?php } ?>
																							
																							<?php if( isset($subModule['has_direct_report_checkbox']) ) { ?>
																							<span class="checkbox">
																								<label>
																									<input id="<?php echo $subModuleKey; ?>" class="ace permission-checkbox" type="checkbox" value="1" permission_type="only_for_direct_reports" <?php echo !empty($subModulePermissionDirectReport) ? "checked" : ""; ?>>
																									<span class="lbl"> Only For Direct Reports</span>
																								</label>
																							</span>
																							<?php } ?>
																						</div>
																					</div>
																					
																					<?php 
																						if( !empty($subModule['subModules']) )
																						{
																						?>
																							<ol class="dd-list">
																						
																							<?php
																								foreach( $subModule['subModules'] as $subModuleKey2 => $subModule2 )
																								{
																									$subModule2PermissionVisible = AccountPermission::model()->find(array(
																										'condition' => '
																											account_type_id = :account_type_id
																											AND permission_key = :permission_key
																											AND permission_type = :permission_type
																										',
																										'params' => array(
																											'account_type_id' => $id,
																											':permission_key' => $subModuleKey2,
																											':permission_type' => 'visible'
																										),
																									));
																									
																									$subModule2PermissionEdit = AccountPermission::model()->find(array(
																										'condition' => '
																											account_type_id = :account_type_id
																											AND permission_key = :permission_key
																											AND permission_type = :permission_type
																										',
																										'params' => array(
																											'account_type_id' => $id,
																											':permission_key' => $subModuleKey2,
																											':permission_type' => 'edit'
																										),
																									));
																									
																									$subModule2PermissionDirectReport = AccountPermission::model()->find(array(
																										'condition' => '
																											account_type_id = :account_type_id
																											AND permission_key = :permission_key
																											AND permission_type = :permission_type
																										',
																										'params' => array(
																											'account_type_id' => $id,
																											':permission_key' => $subModuleKey2,
																											':permission_type' => 'only_for_direct_reports'
																										),
																									));
																								?>
																									<li class="dd-item dd-nodrag" data-id="<?php echo $subModuleKey2.'-'.$authAccount->id; ?>">
																										<div class="dd-handle">
																											<?php echo $subModule2['label']; ?>
																											
																											<div class="pull-right action-buttons">
																												<span class="checkbox">
																													<label>
																														<input id="<?php echo $subModuleKey2; ?>" class="ace permission-checkbox" type="checkbox" value="1" permission_type="visible" <?php echo !empty($subModule2PermissionVisible) ? "checked" : ""; ?>>
																														<span class="lbl"> Visible</span>
																													</label>
																												</span>
																												
																												<?php if( strpos($subModuleKey2, 'field') !== false || strpos($subModuleKey2, 'checkbox') !== false || strpos($subModuleKey2, 'dropdown') !== false ) { ?>
																												<span class="checkbox">
																													<label>
																														<input id="<?php echo $subModuleKey2; ?>" class="ace permission-checkbox" type="checkbox" value="1" permission_type="edit" <?php echo !empty($subModule2PermissionEdit) ? "checked" : ""; ?>>
																														<span class="lbl"> Edit</span>
																													</label>
																												</span>
																												<?php } ?>
																												
																												<?php if( isset($subModule2['has_direct_report_checkbox']) ) { ?>
																												<span class="checkbox">
																													<label>
																														<input id="<?php echo $subModuleKey2; ?>" class="ace permission-checkbox" type="checkbox" value="1" permission_type="only_for_direct_reports" <?php echo !empty($subModule2PermissionDirectReport) ? "checked" : ""; ?>>
																														<span class="lbl"> Only For Direct Reports</span>
																													</label>
																												</span>
																												<?php } ?>
																											</div>
																										</div>
																										
																										<?php 
																											if( !empty($subModule2['subModules']) )
																											{
																											?>
																												<ol class="dd-list">
																											
																												<?php
																													foreach( $subModule2['subModules'] as $subModuleKey3 => $subModule3 )
																													{
																														$subModule3PermissionVisible = AccountPermission::model()->find(array(
																															'condition' => '
																																account_type_id = :account_type_id
																																AND permission_key = :permission_key
																																AND permission_type = :permission_type
																															',
																															'params' => array(
																																'account_type_id' => $id,
																																':permission_key' => $subModuleKey3,
																																':permission_type' => 'visible'
																															),
																														));
																														
																														$subModule3PermissionEdit = AccountPermission::model()->find(array(
																															'condition' => '
																																account_type_id = :account_type_id
																																AND permission_key = :permission_key
																																AND permission_type = :permission_type
																															',
																															'params' => array(
																																'account_type_id' => $id,
																																':permission_key' => $subModuleKey3,
																																':permission_type' => 'edit'
																															),
																														));
																														
																														$subModule3PermissionDirectReport = AccountPermission::model()->find(array(
																															'condition' => '
																																account_type_id = :account_type_id
																																AND permission_key = :permission_key
																																AND permission_type = :permission_type
																															',
																															'params' => array(
																																'account_type_id' => $id,
																																':permission_key' => $subModuleKey3,
																																':permission_type' => 'only_for_direct_reports'
																															),
																														));
																													?>
																														<li class="dd-item dd-nodrag" data-id="<?php echo $subModuleKey3.'-'.$authAccount->id; ?>">
																															<div class="dd-handle">
																																<?php echo $subModule3['label']; ?>
																																
																																<div class="pull-right action-buttons">
																																	<span class="checkbox">
																																		<label>
																																			<input id="<?php echo $subModuleKey3; ?>" class="ace permission-checkbox" type="checkbox" value="1" permission_type="visible" <?php echo !empty($subModule3PermissionVisible) ? "checked" : ""; ?>>
																																			<span class="lbl"> Visible</span>
																																		</label>
																																	</span>
																																	
																																	<?php if( strpos($subModuleKey3, 'field') !== false || strpos($subModuleKey3, 'checkbox') !== false || strpos($subModuleKey3, 'dropdown') !== false ) { ?>
																																	<span class="checkbox">
																																		<label>
																																			<input id="<?php echo $subModuleKey3; ?>" class="ace permission-checkbox" type="checkbox" value="1" permission_type="edit" <?php echo !empty($subModule3PermissionEdit) ? "checked" : ""; ?>>
																																			<span class="lbl"> Edit</span>
																																		</label>
																																	</span>
																																	<?php } ?>
																																	
																																	<?php if( isset($subModule3['has_direct_report_checkbox']) ) { ?>
																																	<span class="checkbox">
																																		<label>
																																			<input id="<?php echo $subModuleKey3; ?>" class="ace permission-checkbox" type="checkbox" value="1" permission_type="only_for_direct_reports" <?php echo !empty($subModule3PermissionDirectReport) ? "checked" : ""; ?>>
																																			<span class="lbl"> Only For Direct Reports</span>
																																		</label>
																																	</span>
																																	<?php } ?>
																																</div>
																															</div>
																														</li>
																													<?php
																													}
																												?>
																												
																												</ol>
																											<?php
																											}
																										?>
																									</li>
																								<?php
																								}
																							?>
																							
																							</ol>
																						<?php
																						}
																					?>
																				</li>
			
																			<?php
																			}
																		?>
																		
																		</ol>
																	<?php
																	}
																?>																
															</li>
														<?php
														}
													?>													
													</ol>
												<?php
												}
											?>
										</li>
									
								
									<?php
									}
								?>
								
							</ol>
						</div>
					</div>

				</div><!-- PAGE CONTENT ENDS -->
			</div><!-- /.col -->
		</div><!-- /.row -->
		
	</div>
</div>