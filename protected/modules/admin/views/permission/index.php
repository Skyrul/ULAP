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
				var company_id = "'.$company->id.'";
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
						"company_id": company_id,				
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
?>

<div class="row">
	<div class="col-xs-12">
		<!-- PAGE CONTENT BEGINS -->
		<div class="row">
			<div class="col-sm-9">
				<div class="dd dd-nodrag" id="nestable"> 
					<ol class="dd-list">

						<?php 
							foreach(CompanyPermission::permissionKeys() as $moduleKey => $module)
							{
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
																		$subModulePermissionVisible = CompanyPermission::model()->find(array(
																			'condition' => '
																				company_id = :company_id
																				AND permission_key = :permission_key
																				AND permission_type = :permission_type
																			',
																			'params' => array(
																				'company_id' => $company->id,
																				':permission_key' => $subModuleKey,
																				':permission_type' => 'visible'
																			),
																		));
																		
																		$subModulePermissionEdit = CompanyPermission::model()->find(array(
																			'condition' => '
																				company_id = :company_id
																				AND permission_key = :permission_key
																				AND permission_type = :permission_type
																			',
																			'params' => array(
																				'company_id' => $company->id,
																				':permission_key' => $subModuleKey,
																				':permission_type' => 'edit'
																			),
																		));
																		
																		$subModulePermissionDirectReport = CompanyPermission::model()->find(array(
																			'condition' => '
																				company_id = :company_id
																				AND permission_key = :permission_key
																				AND permission_type = :permission_type
																			',
																			'params' => array(
																				'company_id' => $company->id,
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
																							$subModule2PermissionVisible = CompanyPermission::model()->find(array(
																								'condition' => '
																									company_id = :company_id
																									AND permission_key = :permission_key
																									AND permission_type = :permission_type
																								',
																								'params' => array(
																									'company_id' => $company->id,
																									':permission_key' => $subModuleKey2,
																									':permission_type' => 'visible'
																								),
																							));
																							
																							$subModule2PermissionEdit = CompanyPermission::model()->find(array(
																								'condition' => '
																									company_id = :company_id
																									AND permission_key = :permission_key
																									AND permission_type = :permission_type
																								',
																								'params' => array(
																									'company_id' => $company->id,
																									':permission_key' => $subModuleKey2,
																									':permission_type' => 'edit'
																								),
																							));
																							
																							$subModule2PermissionDirectReport = CompanyPermission::model()->find(array(
																								'condition' => '
																									company_id = :company_id
																									AND permission_key = :permission_key
																									AND permission_type = :permission_type
																								',
																								'params' => array(
																									'company_id' => $company->id,
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
																												$subModule3PermissionVisible = CompanyPermission::model()->find(array(
																													'condition' => '
																														company_id = :company_id
																														AND permission_key = :permission_key
																														AND permission_type = :permission_type
																													',
																													'params' => array(
																														'company_id' => $company->id,
																														':permission_key' => $subModuleKey3,
																														':permission_type' => 'visible'
																													),
																												));
																												
																												$subModule3PermissionEdit = CompanyPermission::model()->find(array(
																													'condition' => '
																														company_id = :company_id
																														AND permission_key = :permission_key
																														AND permission_type = :permission_type
																													',
																													'params' => array(
																														'company_id' => $company->id,
																														':permission_key' => $subModuleKey3,
																														':permission_type' => 'edit'
																													),
																												));
																												
																												$subModule3PermissionDirectReport = CompanyPermission::model()->find(array(
																													'condition' => '
																														company_id = :company_id
																														AND permission_key = :permission_key
																														AND permission_type = :permission_type
																													',
																													'params' => array(
																														'company_id' => $company->id,
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