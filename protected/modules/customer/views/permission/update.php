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
				var account_id = "'.$customerOfficeStaff->account->id.'";
				var customer_id = "'.$customer->id.'";
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
						"account_id": account_id,				
						"customer_id": customer_id,				
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
			),
		),
		'customer_setup_tab' => array(
			'label' => 'Setup (tab)',
			'subModules' => array(
				'customer_setup_all_fields' => array('label' => 'All fields (except those listed below)'),
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
	);
	
	if( $customer->id == 2203 )
	{
		$modules = array(
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
		);
	}
?>

<?php 
if(!empty($customer) && !$customer->isNewRecord){
	
	$this->widget("application.components.CustomerSideMenu",array(
		'active'=> Yii::app()->controller->id,
		'customer' => $customer,
	));

}

?>

		<?php
			foreach(Yii::app()->user->getFlashes() as $key => $message) {
				echo '<div class="alert alert-' . $key . '"><button data-dismiss="alert" class="close" type="button"><i class="ace-icon fa fa-times"></i></button> ' . $message . "</div>\n";
			}
		?>
		
		<div class="row">
			<div class="page-header">
				<h1><?php echo $customerOfficeStaff->account->getFullName(); ?>
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
										$modulePermissionVisible = CustomerAccountPermission::model()->find(array(
											'condition' => '
												account_id = :account_id
												AND permission_key = :permission_key
												AND permission_type = :permission_type
											',
											'params' => array(
												'account_id' => $customerOfficeStaff->account->id,
												':permission_key' => $moduleKey,
												':permission_type' => 'visible'
											),
										));
										
										$modulePermissionEdit = CustomerAccountPermission::model()->find(array(
											'condition' => '
												account_id = :account_id
												AND permission_key = :permission_key
												AND permission_type = :permission_type
											',
											'params' => array(
												'account_id' => $customerOfficeStaff->account->id,
												':permission_key' => $moduleKey,
												':permission_type' => 'edit'
											),
										));
										
										$modulePermissionDirectReport = CustomerAccountPermission::model()->find(array(
											'condition' => '
												account_id = :account_id
												AND permission_key = :permission_key
												AND permission_type = :permission_type
											',
											'params' => array(
												'account_id' => $customerOfficeStaff->account->id,
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
															$childModulePermissionVisible = CustomerAccountPermission::model()->find(array(
																'condition' => '
																	account_id = :account_id
																	AND permission_key = :permission_key
																	AND permission_type = :permission_type
																',
																'params' => array(
																	'account_id' => $customerOfficeStaff->account->id,
																	':permission_key' => $childModuleKey,
																	':permission_type' => 'visible'
																),
															));
															
															$childModulePermissionEdit = CustomerAccountPermission::model()->find(array(
																'condition' => '
																	account_id = :account_id
																	AND permission_key = :permission_key
																	AND permission_type = :permission_type
																',
																'params' => array(
																	'account_id' => $customerOfficeStaff->account->id,
																	':permission_key' => $childModuleKey,
																	':permission_type' => 'edit'
																),
															));
															
															$childModulePermissionDirectReport = CustomerAccountPermission::model()->find(array(
																'condition' => '
																	account_id = :account_id
																	AND permission_key = :permission_key
																	AND permission_type = :permission_type
																',
																'params' => array(
																	'account_id' => $customerOfficeStaff->account->id,
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
																				$subModulePermissionVisible = CustomerAccountPermission::model()->find(array(
																					'condition' => '
																						account_id = :account_id
																						AND permission_key = :permission_key
																						AND permission_type = :permission_type
																					',
																					'params' => array(
																						'account_id' => $customerOfficeStaff->account->id,
																						':permission_key' => $subModuleKey,
																						':permission_type' => 'visible'
																					),
																				));
																				
																				$subModulePermissionEdit = CustomerAccountPermission::model()->find(array(
																					'condition' => '
																						account_id = :account_id
																						AND permission_key = :permission_key
																						AND permission_type = :permission_type
																					',
																					'params' => array(
																						'account_id' => $customerOfficeStaff->account->id,
																						':permission_key' => $subModuleKey,
																						':permission_type' => 'edit'
																					),
																				));
																				
																				$subModulePermissionDirectReport = CustomerAccountPermission::model()->find(array(
																					'condition' => '
																						account_id = :account_id
																						AND permission_key = :permission_key
																						AND permission_type = :permission_type
																					',
																					'params' => array(
																						'account_id' => $customerOfficeStaff->account->id,
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
																									$subModule2PermissionVisible = CustomerAccountPermission::model()->find(array(
																										'condition' => '
																											account_id = :account_id
																											AND permission_key = :permission_key
																											AND permission_type = :permission_type
																										',
																										'params' => array(
																											'account_id' => $customerOfficeStaff->account->id,
																											':permission_key' => $subModuleKey2,
																											':permission_type' => 'visible'
																										),
																									));
																									
																									$subModule2PermissionEdit = CustomerAccountPermission::model()->find(array(
																										'condition' => '
																											account_id = :account_id
																											AND permission_key = :permission_key
																											AND permission_type = :permission_type
																										',
																										'params' => array(
																											'account_id' => $customerOfficeStaff->account->id,
																											':permission_key' => $subModuleKey2,
																											':permission_type' => 'edit'
																										),
																									));
																									
																									$subModule2PermissionDirectReport = CustomerAccountPermission::model()->find(array(
																										'condition' => '
																											account_id = :account_id
																											AND permission_key = :permission_key
																											AND permission_type = :permission_type
																										',
																										'params' => array(
																											'account_id' => $customerOfficeStaff->account->id,
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
																														$subModule3PermissionVisible = CustomerAccountPermission::model()->find(array(
																															'condition' => '
																																account_id = :account_id
																																AND permission_key = :permission_key
																																AND permission_type = :permission_type
																															',
																															'params' => array(
																																'account_id' => $customerOfficeStaff->account->id,
																																':permission_key' => $subModuleKey3,
																																':permission_type' => 'visible'
																															),
																														));
																														
																														$subModule3PermissionEdit = CustomerAccountPermission::model()->find(array(
																															'condition' => '
																																account_id = :account_id
																																AND permission_key = :permission_key
																																AND permission_type = :permission_type
																															',
																															'params' => array(
																																'account_id' => $customerOfficeStaff->account->id,
																																':permission_key' => $subModuleKey3,
																																':permission_type' => 'edit'
																															),
																														));
																														
																														$subModule3PermissionDirectReport = CustomerAccountPermission::model()->find(array(
																															'condition' => '
																																account_id = :account_id
																																AND permission_key = :permission_key
																																AND permission_type = :permission_type
																															',
																															'params' => array(
																																'account_id' => $customerOfficeStaff->account->id,
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
		