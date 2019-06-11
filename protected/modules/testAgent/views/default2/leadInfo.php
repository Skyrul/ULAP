<div class="row">
	<div class="col-sm-12">
		<div class="col-sm-6 widget-container">
			<div class="widget-box widget-color-blue2">
				<div class="widget-header">
					<h4 class="widget-title lighter smaller">
						LEAD INFORMATION
					</h4>
					<div class="widget-toolbar no-border">					 
						<?php 
							switch($leadHopperEntry->type)
							{
								default: case LeadHopper::TYPE_CONTACT: 
									$callType = 'CONTACT CALL';
									$callTypeClass = 'label-success';
								break;
								
								case LeadHopper::TYPE_CALLBACK: 
									$callType = 'CALLBACK'; 
									$callTypeClass = 'label-danger';
								break;
								
								case LeadHopper::TYPE_CONFIRMATION_CALL: 
									$callType = 'CONFIRMATION CALL'; 
									$callTypeClass = 'label-warning';
								break;
								
								case LeadHopper::TYPE_LEAD_SEARCH: 
									$callType = 'LEAD SEARCH'; 
									$callTypeClass = 'label-info';
								break;
								
								case LeadHopper::TYPE_CONFLICT: 
									$callType = 'CONFLICT'; 
									$callTypeClass = 'label-danger';
								break;
								
								case LeadHopper::TYPE_RESCHEDULE: 
									$callType = 'CONFLICT'; 
									$callTypeClass = 'label-danger';
								break;
								
								case LeadHopper::TYPE_NO_SHOW_RESCHEDULE: 
									$callType = 'NO SHOW RESCHEDULE'; 
									$callTypeClass = 'label-danger';
								break;
							}
						?>
						<span class="label <?php echo $callTypeClass; ?> arrowed-in-right">CALL TYPE: <?php echo $callType; ?></span>
						<span class="label label-default arrowed">SKILL: <?php echo strtoupper($list->skill->skill_name); ?></span>
					</div>

				</div>
				<div class="widget-body">
					<div class="widget-main">
						
						<div class="row-fluid">
							<div class="profile-user-info profile-user-info-striped">
								<div class="profile-info-row">
									<div class="profile-info-name"> LEAD NAME </div>

									<div class="profile-info-value">
										<span><?php echo $lead->first_name.' '.$lead->last_name; ?></span>
										<div class="pull-right">
											<a class="edit-lead-info blue" href="javascript:void(0);" title="Edit" lead_id="<?php echo $lead->id; ?>" field_name="lead_name">
												<i class="ace-icon fa fa-pencil bigger-125"></i>
											</a>
										</div>
									</div>
								</div>
								
								<div class="profile-info-row">
									<div class="profile-info-name"> PARTNER NAME </div>

									<div class="profile-info-value">
										<span><?php echo $lead->partner_first_name.' '.$lead->partner_last_name; ?></span>
										<div class="pull-right">
											<a class="edit-lead-info blue" href="javascript:void(0);" title="Edit" lead_id="<?php echo $lead->id; ?>" field_name="partner_name">
												<i class="ace-icon fa fa-pencil bigger-125"></i>
											</a>
										</div>
									</div>
								</div>
								
								<div class="profile-info-row">
									<div class="profile-info-name"> EMAIL ADDRESS </div>

									<div class="profile-info-value">
										<span><?php echo $lead->email_address; ?></span>
										<div class="pull-right">
											<a class="edit-lead-info blue" href="javascript:void(0);" title="Edit" lead_id="<?php echo $lead->id; ?>" field_name="email_address">
												<i class="ace-icon fa fa-pencil bigger-125"></i>
											</a>
										</div>
									</div>
								</div>
								
								<div class="profile-info-row">
									<div class="profile-info-name"> ADDRESS </div>

									<div class="profile-info-value">
										<span><?php echo $lead->address; ?></span>
										<div class="pull-right">
											<a class="edit-lead-info blue" href="javascript:void(0);" title="Edit" lead_id="<?php echo $lead->id; ?>" field_name="address">
												<i class="ace-icon fa fa-pencil bigger-125"></i>
											</a>
										</div>
									</div>
								</div>
								
								<div class="profile-info-row">
									<div class="profile-info-name"> LANGUAGE </div>

									<div class="profile-info-value">
										<span>
											<?php 
												$languageOptions = array(
													'English' => 'English',
													// 'French' => 'French',
													// 'Korean' => 'Korean',
													// 'Mandarin' => 'Mandarin',
													'Spanish' => 'Spanish',
												);
												
												echo CHtml::dropDownList('Lead[language]', $lead->language, $languageOptions, array('class'=>'edit-lead-info', 'lead_id'=>$lead->id, 'field_name'=>'language')); 
											?>
										</span>
									</div>
								</div>
								
								<div class="profile-info-row">
									<div class="profile-info-name"> TIME ZONE </div>

									<div class="profile-info-value">
										<div class="col-sm-7">
											<span>
												<?php
													echo CHtml::dropDownList('Lead[timezone]', $lead->timezone, AreacodeTimezoneLookup::items(), array('class'=>'edit-lead-info', 'lead_id'=>$lead->id, 'field_name'=>'timezone', 'prompt'=>'- Select -'));
												?>
											</span>
										</div>
										
										<div class="col-sm-5 text-right">
											<?php 
												if( !empty($lead->timezone) )
												{
													$date = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('America/Chicago'));

													$date->setTimezone(new DateTimeZone( timezone_name_from_abbr($lead->timezone) ));

													echo $date->format('m/d/Y g:i A'); 
												}
											?>
										</div>
									</div>
								</div>
							</div>
						</div>
						
						<hr class="hr-dotted">
						
						<div class="row center">
							<div class="col-sm-9 dialer-function-btn-container">
								<button class="btn btn-grey btn-xs" data-toggle="modal" data-target="#myModal" style="margin-right:40px;">DIAL PAD</button>

								<button class="btn btn-warning btn-circle hold-call-btn disabled">HOLD</button>				
															
								<button class="btn btn-primary btn-circle mute-call-btn disabled">MUTE</button>																				

								<button class="btn btn-purple btn-circle transfer-call-btn disabled">XFR</button>

								<button class="btn btn-success btn-circle conference-call-btn disabled">CONF</button>
								
								<button class="btn btn-danger btn-circle end-call-btn disabled">END</button>
							</div>
							
							<div class="col-sm-3">
								<div class="col-sm-6">
									<button style="border-radius:10px;" class="btn btn-success btn-xs">&nbsp;</button> LINE 1
								</div>
								
								<div class="col-sm-6">
									<button style="border-radius:10px;" class="btn btn-danger btn-xs">&nbsp;</button> LINE 2
								</div>
							</div>
						</div>
						
						<div class="row">
							<div class="col-sm-12">
								<div class="col-sm-6">
									Dialing as # <span class="dialingAs"></span>
								</div>
							</div>
						</div>
						
						<br />
						<br />
						
						<div class="row">
							<table id="leadPhoneNumbers" class="table table-condensed">
								
								<?php if(!empty($lead->home_phone_number)): ?>
								
								<tr>
									<th><?php echo !empty($lead->home_phone_label) ? $lead->home_phone_label : 'HOME';?></th>
									
									<td width="16%">
										<?php echo !empty($lead->home_phone_number) ? "(".substr($lead->home_phone_number, 0, 3).") ".substr($lead->home_phone_number, 3, 3)."-".substr($lead->home_phone_number,6) : ''; ?>
									</td>
									
									<td>
										<a class="edit-lead-info blue" href="javascript:void(0);" title="Edit" lead_id="<?php echo $lead->id; ?>" field_name="home_phone_number">
											<i class="ace-icon fa fa-pencil bigger-125"></i>
										</a>
									</td>
									
									<td width="18%">
										<span style="margin-right:10px;">DIAL COUNT</span> 
										<span><?php echo $lead->home_phone_dial_count; ?></span>
									</td>
									
									<td>
										<div class="form-group">
											<label for="form-field-1" class="col-sm-5 control-label no-padding-right"> DISPOSITION </label>

											<div class="col-sm-7">
												<?php 
													$homeDispoHtmlOptions =  array(
														'class' => 'dispo-select', 
														'phone_type' => 'home', 
														'field_type' => 'dispo',
														'prompt' => '- Select -',
														'disabled' => true,
														'style'=>'width:250px;',
													);
													
													$homeDispoHtmlOptions = array_merge($dispositionHtmlOptions, $homeDispoHtmlOptions);
													
													echo CHtml::dropDownList('Lead[home_phone_dispo_id]', '', $dispositionOptions, $homeDispoHtmlOptions); 
												?>
											</div>
										</div>
										
										<div class="dispo-detail-container"></div>
									</td>
									
									<td>
										<?php 
											$homePhoneHtmlOptions = array(
												'class' => 'green dial-phonenumber-btn',
												'phone_type'=>'home',
												'lead_id' => $lead->id,
												'list_id' => $list->id,
												'customer_id' => $customer->id,
												'company_id' => $customer->company_id,
												'skill_id' => $list->skill_id,
												'lead_phone_number' => $lead->home_phone_number,
												'title' => 'Dial this number',
											);
											
											echo CHtml::link('<i class="fa fa-phone fa-2x"></i>', 'javascript:void(0);', $homePhoneHtmlOptions); 
										?>
									</td>
								</tr>
								
								<?php endif; ?>
								
								
								<?php if(!empty($lead->mobile_phone_number)): ?>
								
								<tr>
									<th><?php echo !empty($lead->mobile_phone_label) ? $lead->mobile_phone_label : 'MOBILE';?></th>
									
									<td width="16%">
										<?php echo !empty($lead->mobile_phone_number) ? "(".substr($lead->mobile_phone_number, 0, 3).") ".substr($lead->mobile_phone_number, 3, 3)."-".substr($lead->mobile_phone_number,6) : ''; ?>
									</td>
									
									<td>
										<a class="edit-lead-info blue" href="javascript:void(0);" title="Edit" lead_id="<?php echo $lead->id; ?>" field_name="mobile_phone_number">
											<i class="ace-icon fa fa-pencil bigger-125"></i>
										</a>
									</td>
									
									<td width="18%">
										<span style="margin-right:10px;">DIAL COUNT</span>  
										<span><?php echo $lead->mobile_phone_dial_count; ?></span>
									</td>
									
									<td>
										<div class="form-group">
											<label for="form-field-1" class="col-sm-5 control-label no-padding-right"> DISPOSITION </label>

											<div class="col-sm-7">
												<?php 
													$mobileDispoHtmlOptions = array(
														'class' => 'dispo-select', 
														'phone_type' => 'mobile', 
														'field_type' => 'dispo', 
														'prompt' => '- Select -', 
														'disabled' => true,
														'style'=>'width:250px;',
													);
													
													$mobileDispoHtmlOptions = array_merge($dispositionHtmlOptions, $mobileDispoHtmlOptions);
													
													echo CHtml::dropDownList('Lead[mobile_phone_dispo_id]', '', $dispositionOptions, $mobileDispoHtmlOptions); 
												?>
											</div>
										</div>
										
										<div class="dispo-detail-container"></div>
									</td>
									
									<td>
										<?php 
											$mobilePhoneHtmlOptions = array(
												'class' => 'green dial-phonenumber-btn',
												'phone_type'=>'mobile',
												'lead_id' => $lead->id,
												'list_id' => $list->id,
												'customer_id' => $customer->id,
												'company_id' => $customer->company_id,
												'skill_id' => $list->skill_id,
												'lead_phone_number' => $lead->mobile_phone_number,
												'title' => 'Dial this number',
											);
											
											echo CHtml::link('<i class="fa fa-phone fa-2x"></i>', 'javascript:void(0);', $mobilePhoneHtmlOptions); 
										?>
									</td>
								</tr>
								
								<?php endif; ?>
								
								
								<?php if(!empty($lead->office_phone_number)): ?>
								
								<tr>
									<th><?php echo !empty($lead->office_phone_label) ? $lead->office_phone_label : 'OFFICE';?></th>
									
									<td width="16%">
										<?php echo !empty($lead->office_phone_number) ? "(".substr($lead->office_phone_number, 0, 3).") ".substr($lead->office_phone_number, 3, 3)."-".substr($lead->office_phone_number,6) : ''; ?>
									</td>
									
									<td>
										<a class="edit-lead-info blue" href="javascript:void(0);" title="Edit" lead_id="<?php echo $lead->id; ?>" field_name="office_phone_number">
											<i class="ace-icon fa fa-pencil bigger-125"></i>
										</a>
									</td>
									
									<td width="18%">
										<span style="margin-right:10px;">DIAL COUNT</span> 
										<span><?php echo $lead->office_phone_dial_count; ?></span>
									</td>
									
									<td>
										<div class="form-group">
											<label for="form-field-1" class="col-sm-5 control-label no-padding-right"> DISPOSITION </label>

											<div class="col-sm-7">
												<?php
													$officeDispoHtmlOptions = array(
														'class' => 'dispo-select', 
														'phone_type' => 'office',
														'field_type' => 'dispo',
														'prompt' => '- Select -', 
														'disabled' => true,
														'style'=>'width:250px;',
													);
													
													$officeDispoHtmlOptions = array_merge($dispositionHtmlOptions, $officeDispoHtmlOptions);
													
													echo CHtml::dropDownList('Lead[office_phone_dispo_id]', '', $dispositionOptions, $officeDispoHtmlOptions); 
												?>
											</div>
										</div>
										
										<div class="dispo-detail-container"></div>
									</td>
									
									<td>
										<?php 
											$officePhoneHtmlOptions = array(
												'class' => 'green dial-phonenumber-btn',
												'phone_type'=>'office',
												'lead_id' => $lead->id,
												'list_id' => $list->id,
												'customer_id' => $customer->id,
												'company_id' => $customer->company_id,
												'skill_id' => $list->skill_id,
												'lead_phone_number' => $lead->office_phone_number,
												'title' => 'Dial this number',
											);
											
											echo CHtml::link('<i class="fa fa-phone fa-2x"></i>', 'javascript:void(0);', $officePhoneHtmlOptions); 
										?>
									</td>
								</tr>
								
								<?php endif; ?>
								
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="col-sm-6 widget-container">
			<div class="widget-box widget-color-blue2">
				<div class="widget-header">
						<h4 class="widget-title lighter smaller">
							CUSTOMER INFORMATION - <?php echo $customer->company->company_name; ?>
						</h4>
					</div>
				<div class="widget-body">
					<div class="widget-main">
						<div class="row">
							<div class="col-xs-12 col-sm-3 center">
								<?php 
									$borderStyle = '';
									
									if( !empty($customer->gender) )
									{
										$borderStyle = strtolower($customer->gender) == 'male' ? 'border:3px solid #337ab7;' : 'border:3px solid #c6699f;';
									}
								?>
							
								<span class="profile-picture" style="<?php echo $borderStyle; ?>">
									<?php 
										if( $customer->getImage() != null )
										{
											echo CHtml::image($customer->getImage(), '', array('class'=>'responsive'));
										}
										else
										{
											echo '<div style="height:180px; border:1px dashed #ccc; text-align:center; line-height: 180px;">No Image Uploaded.</div>';
										}
									?>
								</span>
								
								<?php 
									if($customer->getVoice())
									{
										echo '<div class="space-12"></div> <audio controls src="'.$customer->getVoice().'" id="audio" style="width:100%"></audio>';
									}
									else
									{
										echo '<div class="space-12"></div> No voice file.'; 
									}
								?>
							</div><!-- /.col -->

							<div class="col-xs-12 col-sm-9">	
								<div class="row-fluid">
									<div class="profile-user-info profile-user-info-striped">
										<div class="profile-info-row">
											<div class="profile-info-name"> CUSTOMER NAME </div>

											<div class="profile-info-value">
												<span><?php echo $customer->firstname.' '.$customer->lastname; ?></span>
											</div>
										</div>
										
										<div class="profile-info-row">
											<div class="profile-info-name"> OFFICE </div>

											<div class="profile-info-value">
												<span>
													<?php echo CHtml::dropDownList('', $office->id, $officeOptions, array('id'=>'office-select', 'prompt'=>'- SELECT -')); ?>
												</span>
											</div>
										</div>
										
										<div class="profile-info-row">
											<div class="profile-info-name"> OFFICE ADDRESS </div>

											<div class="profile-info-value">
												<span><?php echo $office->address; ?></span>
											</div>
										</div>
										
										<div class="profile-info-row">
											<div class="profile-info-name"> OFFICE CITY </div>

											<div class="profile-info-value">
												<span><?php echo $office->city; ?></span>
											</div>
										</div>
										
										<div class="profile-info-row">
											<div class="profile-info-name"> OFFICE STATE </div>

											<div class="profile-info-value">
												<span>
													<?php 
														$state = State::model()->findByPk($office->state);
														
														if( $state )
														{
															echo $state->name;
														}
													?>
												</span>
											</div>
										</div>
										
										<div class="profile-info-row">
											<div class="profile-info-name"> OFFICE PHONE # </div>

											<div class="profile-info-value">
												<span><?php echo $office->phone; ?></span>
											</div>
										</div>
										
										<div class="profile-info-row">
											<div class="profile-info-name"> OFFICE EMAIL </div>

											<div class="profile-info-value">
												<span><?php echo $office->email_address; ?></span>
											</div>
										</div>
										
										<!--<div class="profile-info-row">
											<div class="profile-info-name"> CUSTOMER WEBSITE </div>

											<div class="profile-info-value">
												<span></span>
											</div>
										</div>-->
										
										<div class="profile-info-row">
											<div class="profile-info-name"> CUSTOMER NOTES </div>

											<div class="profile-info-value">
												<span>
													<?php echo $customer->notes; ?>
												</span>
											</div>
										</div>
									</div>
								</div>
						
							</div><!-- /.col -->
						</div>
					</div>
				</div>
			</div>
			
			<div class="space-12"></div>

			<div class="row">
				<div class="col-sm-12 widget-container">				
					<div class="widget-box widget-color-blue2">
						<div class="widget-header">
							<h4 class="widget-title lighter smaller">
								LEAD HISTORY
							</h4>
						</div>

						<div class="widget-body">
							<div class="widget-main no-padding">
								<!-- #section:pages/dashboard.conversations -->
								<div class="dialogs scrollable">
									<div class="timeline-container">
										<!--<div class="timeline-label">
											<span class="label label-primary arrowed-in-right label-lg">
												<b>Today</b>
											</span>
										</div>-->
									
										<?php 
											$this->widget('zii.widgets.CListView', array(
												'id'=>'leadHistoryList',
												'dataProvider'=>$leadHistoryDataProvider,
												'itemView'=>'_lead_history_list',
												'template'=>'<div class="timeline-items">{items}</div>',
											)); 
										?>
	
									</div><!-- /.timeline-container -->
			
								</div>

								<!-- /section:pages/dashboard.conversations -->
								<form id="leadHistoryForm">
								
									<input type="hidden" name="LeadHistory[lead_id]" value="<?php echo $lead->id; ?>">
									<input type="hidden" name="LeadHistory[lead_name]" value="<?php echo $lead->first_name.' '.$lead->last_name; ?>">
									<input type="hidden" name="LeadHistory[lead_phone_number]" value="<?php echo $lead->office_phone_number; ?>">
									<input type="hidden" name="LeadHistory[type]" value="1">
								
									<div class="form-actions clearfix">
										<div class="row-fluid clearfix">							
											<textarea class="col-xs-12" id="LeadHistory_note" name="LeadHistory[note]"></textarea>
										</div>

										<div class="space-6"></div>

										<button class="btn btn-sm btn-info no-radius pull-right lead-history-submit-btn" type="button">
											SUBMIT NOTE
										</button>
									</div>
								</form>
							</div><!-- /.widget-main -->
						</div><!-- /.widget-body -->
					</div>		
				</div><!-- /.col -->
			</div>
		</div>

	</div>
</div>