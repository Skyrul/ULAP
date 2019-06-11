<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	
	$cs = Yii::app()->clientScript;
	
	$cs->registerScript(uniqid(), '
	
		$(document).ready( function(){
			
			$(document).on("change", "#AccountUser_job_title", function(){
				
				if( $(this).val() == "Sales Agent" )
				{
					$(".commission-rate-field-container").fadeIn();
				}
				else
				{
					$(".commission-rate-field-container").hide();
					$("#AccountUser_commission_rate").val("");
				}
				
			});
		});
	', CClientScript::POS_HEAD);
?>

<div class="form">

	<?php $form=$this->beginWidget('CActiveForm', array(
		'id'=>'account-user-form',
		// Please note: When you enable ajax validation, make sure the corresponding
		// controller action is handling ajax validation correctly.
		// There is a call to performAjaxValidation() commented in generated controller code.
		// See class documentation of CActiveForm for details on this.
		'enableAjaxValidation'=>false,
		'htmlOptions'=>array(
			// 'class'=> 'form-horizontal',
			'enctype' => 'multipart/form-data',
		),
	)); ?>

	<div class="form-group">
		<div class="col-sm-4">
			<div class="row">
				<div class=	"col-sm-4 col-sm-offset-1">
					<span class="profile-picture">
						<?php 
							if($accountUser->getImage())
							{
								echo CHtml::image($accountUser->getImage(), '', array('class'=>'img-responsive'));
							}
							else
							{
								echo '<div style="width:100px; height:100px; text-align:center;">No image uploaded.</div>';
							}
						?>
					</span>
				</div>
				<div class="col-sm-7 text-center">
					<h3><?php echo $accountUser->getFullName(); ?></h3>
					
					<?php 
						if( $accountUser->getAudio() )
						{
							echo '<audio controls>';
							
								echo '<source src="'.$accountUser->getAudio().'" type="audio/wav">';
								echo '<source src="'.$accountUser->getAudio().'" type="audio/mpeg">';
								echo '<source src="'.$accountUser->getAudio().'" type="audio/ogg">';
								
								echo 'Your browser does not support the audio tag.';
							echo '</audio>';
						}
						else
						{
							echo 'No audio uploaded';
						}
					?>
				</div>
			</div>
			
			<div class="space-6"></div>
			
			<div class="row">
				<div class="col-sm-12">
					<label class="col-sm-1">Image</label>
					
					<div class="col-sm-11">
						<?php echo CHtml::activeFileField($fileupload, 'original_filename'); ?>
						<?php echo $form->error($fileupload,'original_filename'); ?>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col-sm-12">
					<label class="col-sm-1">Audio</label>
					
					<div class="col-sm-11">
						<?php echo CHtml::activeFileField($audioFileupload, 'original_filename'); ?>
						<?php echo $form->error($audioFileupload,'original_filename'); ?>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="widget-box">
					<div class="widget-body">
						<div class="widget-main">
						
							<?php if( Yii::app()->user->account->checkPermission('employees_employee_profile_active_employee_checkbox','visible') && Yii::app()->user->account->checkPermission('employees_employee_profile_active_employee_checkbox','only_for_direct_reports', $account->id) ){ ?>
								
								<div class="row center">
									<div class="col-sm-3 col-sm-offset-3">
										<div class="checkbox">
											<label>
												<?php
													echo $form->checkBox($account, 'status', array('class'=>'ace', 'value'=>1, 'uncheckValue'=>2, 'disabled'=>!Yii::app()->user->account->checkPermission('employees_employee_profile_active_employee_checkbox','edit'))); 
												?>
												<span class="lbl">Active Employee</span>
											</label>
										</div>
										
										<?php 
											if( !Yii::app()->user->account->checkPermission('employees_employee_profile_active_employee_checkbox','edit') )
											{
												echo $form->hiddenField($account, 'status');
											} 
										?>
									</div>
									
									<?php if( Yii::app()->user->account->checkPermission('employees_employee_profile_employee_portal_access_checkbox','visible') && Yii::app()->user->account->checkPermission('employees_employee_profile_employee_portal_access_checkbox','only_for_direct_reports', $account->id) ){ ?>
										<div class="col-sm-4">
											<div class="checkbox">
												<label>
													<?php
														echo $form->checkBox($accountUser, 'has_employee_portal_access', array('class'=>'ace', 'value'=>1, 'uncheckValue'=>0, 'disabled'=>!Yii::app()->user->account->checkPermission('employees_employee_profile_employee_portal_access_checkbox','edit'))); 
													?>
													<span class="lbl">Employee Portal Access</span>
												</label>
											</div>
										</div>
										
										<?php 
											if( !Yii::app()->user->account->checkPermission('employees_employee_profile_employee_portal_access_checkbox','edit') )
											{
												echo $form->hiddenField($accountUser, 'has_employee_portal_access');
											}
										?>
										
									<?php } ?>
								</div>

							<?php } ?>
			
							<?php if( Yii::app()->user->account->checkPermission('employees_employee_profile_start_date_field','visible') && Yii::app()->user->account->checkPermission('employees_employee_profile_start_date_field','only_for_direct_reports', $account->id) ){ ?>
								<div class="row">
									<?php echo $form->labelEx($accountUser,'date_hire'); ?>
									
									<div class="col-sm-10 col-sm-offset-1">
										<?php echo $form->textField($accountUser,'date_hire',array('class' => 'date-picker col-sm-12', 'disabled'=>!Yii::app()->user->account->checkPermission('employees_employee_profile_start_date_field','edit'))); ?>
										<?php echo $form->error($accountUser,'date_hire'); ?>
									</div>
								</div>
							<?php } ?>
							
							<?php if( Yii::app()->user->account->checkPermission('employees_employee_profile_termination_date_field','visible') && Yii::app()->user->account->checkPermission('employees_employee_profile_termination_date_field','only_for_direct_reports', $account->id) ){ ?>
								<div class="row">
									<?php echo $form->labelEx($accountUser,'date_termination'); ?>
									
									<div class="col-sm-10 col-sm-offset-1">
										<?php echo $form->textField($accountUser,'date_termination',array('class' => 'date-picker col-sm-12', 'disabled'=>!Yii::app()->user->account->checkPermission('employees_employee_profile_termination_date_field','edit'))); ?>
										<?php echo $form->error($accountUser,'date_termination'); ?>
									</div>
								</div>
							<?php } ?>
							
							<?php if( Yii::app()->user->account->checkPermission('employees_employee_profile_job_title_dropdown','visible') && Yii::app()->user->account->checkPermission('employees_employee_profile_job_title_dropdown','only_for_direct_reports', $account->id) ){ ?>
								<div class="row">
									<?php echo $form->labelEx($accountUser,'job_title'); ?>
									
									<div class="col-sm-10 col-sm-offset-1">
										<?php echo $form->dropDownList($accountUser,'job_title', $accountUser::jobTitleOptions(), array('prompt'=>'- SELECT -', 'class'=>'col-sm-12', 'disabled'=>!Yii::app()->user->account->checkPermission('employees_employee_profile_job_title_dropdown','edit'))); ?>
										<?php echo $form->error($accountUser,'job_title'); ?>
									</div>
								</div>
							<?php } ?>
							
							<?php if( Yii::app()->user->account->checkPermission('employees_employee_profile_phone_extension_field','visible') && Yii::app()->user->account->checkPermission('employees_employee_profile_phone_extension_field','only_for_direct_reports', $account->id) ){ ?>
								<div class="row">
									<?php echo $form->labelEx($accountUser,'phone_extension'); ?>
									
									<div class="col-sm-10 col-sm-offset-1">
										<?php echo $form->textField($accountUser,'phone_extension',array('class' => 'col-sm-12', 'disabled'=>!Yii::app()->user->account->checkPermission('employees_employee_profile_phone_extension_field','edit'))); ?>
										<?php echo $form->error($accountUser,'phone_extension'); ?>
									</div>	
								</div>
							<?php } ?>
							
							<?php if( !$accountUser->isNewRecord && Yii::app()->user->account->checkPermission('employees_employee_profile_reports_to_dropdown','visible') && Yii::app()->user->account->checkPermission('employees_employee_profile_reports_to_dropdown','only_for_direct_reports', $account->id) ){ ?>
								<div class="row">
									<label>Reports To</label>
									
									<div class="col-sm-10 col-sm-offset-1">
										<?php echo $form->dropDownList($position, 'parent_id', $reportsToOptions, array('prompt'=>'- SELECT -', 'class'=>'col-sm-12', 'disabled'=>!Yii::app()->user->account->checkPermission('employees_employee_profile_reports_to_dropdown','edit'))); ?>
									</div>
								</div>
							<?php } ?>
							
							<?php if( Yii::app()->user->account->checkPermission('employees_employee_profile_use_webphone_checkbox','visible') && Yii::app()->user->account->checkPermission('employees_employee_profile_use_webphone_checkbox','only_for_direct_reports', $account->id) ){ ?>
							<div class="row center" style="margin-top:7px;">
								<div class="col-sm-12">
									<div class="checkbox">
										<label>
											<?php
												echo $form->checkBox($account, 'use_webphone', array('class'=>'ace', 'value'=>1, 'uncheckValue'=>0, 'disabled'=>!Yii::app()->user->account->checkPermission('employees_employee_profile_use_webphone_checkbox','edit'))); 
											?>
											<span class="lbl">Use Webphone</span>
										</label>
									</div>
								</div>
							</div>
							<?php } ?>
							
							<?php if( Yii::app()->user->account->checkPermission('employees_employee_profile_sip_username_field','visible') && Yii::app()->user->account->checkPermission('employees_employee_profile_sip_username_field','only_for_direct_reports', $account->id) ){ ?>
								<div class="row">
									<?php echo $form->labelEx($account,'sip_username'); ?>
									
									<div class="col-sm-10 col-sm-offset-1">
										<?php echo $form->textField($account,'sip_username',array('class' => 'col-sm-12', 'disabled'=>!Yii::app()->user->account->checkPermission('employees_employee_profile_sip_username_field','edit'))); ?>
										<?php echo $form->error($account,'sip_username'); ?>
									</div>	
								</div>
							<?php } ?>
							
							<?php if( Yii::app()->user->account->checkPermission('employees_employee_profile_sip_password_field','visible') && Yii::app()->user->account->checkPermission('employees_employee_profile_sip_password_field','only_for_direct_reports', $account->id) ){ ?>
								<div class="row">
									<?php echo $form->labelEx($account,'sip_password'); ?>
									
									<div class="col-sm-10 col-sm-offset-1">
										<?php echo $form->passwordField($account,'sip_password',array('class' => 'col-sm-12', 'disabled'=>!Yii::app()->user->account->checkPermission('employees_employee_profile_sip_password_field','edit'))); ?>
										<?php echo $form->error($account,'sip_password'); ?>
									</div>	
								</div>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="col-sm-4">
			<div class="widget-box">
				<div class="widget-body">
					<div class="widget-main">
					
						<?php if( Yii::app()->user->account->checkPermission('employees_employee_profile_employee_number_field','visible') && Yii::app()->user->account->checkPermission('employees_employee_profile_employee_number_field','only_for_direct_reports', $account->id) ){ ?>
							<div class="row">
								<?php echo $form->labelEx($accountUser,'employee_number'); ?>
								
								<div class="col-sm-10 col-sm-offset-1">
									<?php 
										
										if( $accountUser->isNewRecord )
										{
											echo $form->textField($accountUser,'employee_number',array('class' => 'col-sm-12', 'readOnly'=>true, 'disabled'=>!Yii::app()->user->account->checkPermission('employees_employee_profile_employee_number_field','edit'))); 
										}
										else
										{
											echo $form->textField($accountUser,'employee_number',array('class' => 'col-sm-12', 'disabled'=>!Yii::app()->user->account->checkPermission('employees_employee_profile_employee_number_field','edit'))); 
										}
									?>
									
									<?php echo $form->error($accountUser,'employee_number'); ?>
								</div>	
							</div>	
						<?php }	?>
						
						<?php if( Yii::app()->user->account->checkPermission('employees_employee_profile_badge_id_field','visible') && Yii::app()->user->account->checkPermission('employees_employee_profile_badge_id_field','only_for_direct_reports', $account->id) ){ ?>
							<div class="row">
								<?php echo $form->labelEx($accountUser,'badge_id'); ?>
								
								<div class="col-sm-10 col-sm-offset-1">
									<?php echo $form->textField($accountUser,'badge_id',array('class' => 'col-sm-12', 'disabled'=>!Yii::app()->user->account->checkPermission('employees_employee_profile_badge_id_field','edit'))); ?>
									
									<?php echo $form->error($accountUser,'badge_id'); ?>
								</div>	
							</div>	
						<?php } ?>
						
						<?php if( Yii::app()->user->account->checkPermission('employees_employee_profile_first_name_field','visible') && Yii::app()->user->account->checkPermission('employees_employee_profile_first_name_field','only_for_direct_reports', $account->id) ){ ?>
							<div class="row">
								<?php echo $form->labelEx($accountUser,'first_name'); ?>
								
								<div class="col-sm-10 col-sm-offset-1">
									<?php echo $form->textField($accountUser,'first_name',array('class' => 'col-sm-12', 'disabled'=>!Yii::app()->user->account->checkPermission('employees_employee_profile_first_name_field','edit'))); ?>
									<?php echo $form->error($accountUser,'first_name'); ?>
								</div>	
							</div>
						<?php } ?>
						
						<?php if( Yii::app()->user->account->checkPermission('employees_employee_profile_last_name_field','visible') && Yii::app()->user->account->checkPermission('employees_employee_profile_last_name_field','only_for_direct_reports', $account->id) ){ ?>
							<div class="row">
								<?php echo $form->labelEx($accountUser,'last_name'); ?>
								
								<div class="col-sm-10 col-sm-offset-1">
									<?php echo $form->textField($accountUser,'last_name',array('class' => 'col-sm-12', 'disabled'=>!Yii::app()->user->account->checkPermission('employees_employee_profile_last_name_field','edit'))); ?>
									<?php echo $form->error($accountUser,'last_name'); ?>
								</div>	
							</div>
						<?php } ?>
						
						<?php if( Yii::app()->user->account->checkPermission('employees_employee_profile_address_field','visible') && Yii::app()->user->account->checkPermission('employees_employee_profile_address_field','only_for_direct_reports', $account->id) ){ ?>
							<div class="row">
								<?php echo $form->labelEx($accountUser,'address'); ?>
								
								<div class="col-sm-10 col-sm-offset-1">
									<?php echo $form->textField($accountUser,'address',array('class' => 'col-sm-12', 'disabled'=>!Yii::app()->user->account->checkPermission('employees_employee_profile_address_field','edit'))); ?>
									<?php echo $form->error($accountUser,'address'); ?>
								</div>	
							</div>
						<?php } ?>
						
						<?php if( Yii::app()->user->account->checkPermission('employees_employee_profile_phone_number_field','visible') && Yii::app()->user->account->checkPermission('employees_employee_profile_phone_number_field','only_for_direct_reports', $account->id) ){ ?>
							<div class="row">
								<?php echo $form->labelEx($accountUser,'phone_number'); ?>
								
								<div class="col-sm-10 col-sm-offset-1">
									<?php echo $form->textField($accountUser,'phone_number',array('class' => 'col-sm-12 input-mask-phone', 'disabled'=>!Yii::app()->user->account->checkPermission('employees_employee_profile_phone_number_field','edit'))); ?>
									<?php echo $form->error($accountUser,'phone_number'); ?>
								</div>	
							</div>
						<?php } ?>
						
						<?php if( Yii::app()->user->account->checkPermission('employees_employee_profile_mobile_number_field','visible') && Yii::app()->user->account->checkPermission('employees_employee_profile_mobile_number_field','only_for_direct_reports', $account->id) ){ ?>
							<div class="row">
								<?php echo $form->labelEx($accountUser,'mobile_number'); ?>
								
								<div class="col-sm-10 col-sm-offset-1">
									<?php echo $form->textField($accountUser,'mobile_number',array('class' => 'col-sm-12 input-mask-phone', 'disabled'=>!Yii::app()->user->account->checkPermission('employees_employee_profile_mobile_number_field','edit'))); ?>
									<?php echo $form->error($accountUser,'mobile_number'); ?>
								</div>	
							</div>
						<?php } ?>
						
						<?php if( Yii::app()->user->account->checkPermission('employees_employee_profile_email_address_field','visible') && Yii::app()->user->account->checkPermission('employees_employee_profile_email_address_field','only_for_direct_reports', $account->id) ){ ?>
							<div class="row">
								<?php echo $form->labelEx($account,'email_address'); ?>
								
								<div class="col-sm-10 col-sm-offset-1">
									<?php echo $form->textField($account,'email_address',array('class' => 'col-sm-12', 'disabled'=>!Yii::app()->user->account->checkPermission('employees_employee_profile_email_address_field','edit'))); ?>
									<?php echo $form->error($account,'email_address'); ?>
								</div>	
							</div>
						<?php } ?>
						
						<?php if( Yii::app()->user->account->checkPermission('employees_employee_profile_emergency_contact_field','visible') && Yii::app()->user->account->checkPermission('employees_employee_profile_emergency_contact_field','only_for_direct_reports', $account->id) ){ ?>
							<div class="row">
								<?php echo $form->labelEx($accountUser,'emergency_contact'); ?>
								
								<div class="col-sm-10 col-sm-offset-1">
									<?php echo $form->textField($accountUser,'emergency_contact',array('class' => 'col-sm-12', 'disabled'=>!Yii::app()->user->account->checkPermission('employees_employee_profile_emergency_contact_field','edit'))); ?>
									<?php echo $form->error($accountUser,'emergency_contact'); ?>
								</div>	
							</div>
						<?php } ?>
						
						<?php if( Yii::app()->user->account->checkPermission('employees_employee_profile_social_security_number_field','visible') && Yii::app()->user->account->checkPermission('employees_employee_profile_social_security_number_field','only_for_direct_reports', $account->id) ){ ?>
							<div class="row">
								<?php echo $form->labelEx($accountUser,'social_security_number'); ?>
								
								<div class="col-sm-10 col-sm-offset-1">
									<?php echo $form->textField($accountUser,'social_security_number',array('class' => 'col-sm-12 input-mask-ssn', 'disabled'=>!Yii::app()->user->account->checkPermission('employees_employee_profile_social_security_number_field','edit'))); ?>
									<?php echo $form->error($accountUser,'social_security_number'); ?>
								</div>	
							</div>
						<?php } ?>
						
						<?php if( Yii::app()->user->account->checkPermission('employees_employee_profile_birthday_field','visible') && Yii::app()->user->account->checkPermission('employees_employee_profile_birthday_field','only_for_direct_reports', $account->id) ){ ?>
							<div class="row">
								<?php echo $form->labelEx($accountUser,'birthday'); ?>
								
								<div class="col-sm-10 col-sm-offset-1">
									<?php echo $form->textField($accountUser,'birthday',array('class' => 'col-sm-12 date-picker2', 'disabled'=>!Yii::app()->user->account->checkPermission('employees_employee_profile_birthday_field','edit'))); ?>
									<?php echo $form->error($accountUser,'birthday'); ?>
								</div>	
							</div>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
		
		<div class="col-sm-4">
			<div class="row">
				<div class="widget-box">
					<div class="widget-body">
						<div class="widget-main">
							<?php if( Yii::app()->user->account->checkPermission('employees_employee_profile_full_time_status_dropdown','visible') && Yii::app()->user->account->checkPermission('employees_employee_profile_full_time_status_dropdown','only_for_direct_reports', $account->id) ){ ?>
								<div class="row">
									<?php echo $form->labelEx($accountUser,'full_time_status'); ?>
									
									<div class="col-sm-10 col-sm-offset-1">
										<?php echo $form->dropDownList($accountUser,'full_time_status', $accountUser::fullTimeStatusOptions(), array('prompt'=>'- SELECT -', 'class'=>'col-sm-12', 'disabled'=>!Yii::app()->user->account->checkPermission('employees_employee_profile_full_time_status_dropdown','edit'))); ?>
										<?php echo $form->error($accountUser,'full_time_status'); ?>
									</div>
								</div>
							<?php } ?>
							
							<?php if( Yii::app()->user->account->checkPermission('employees_employee_profile_hour_salary_dropdown','visible') && Yii::app()->user->account->checkPermission('employees_employee_profile_hour_salary_dropdown','only_for_direct_reports', $account->id) ){ ?>
								<div class="row">
									<?php echo $form->labelEx($accountUser,'salary_type'); ?>
									
									<div class="col-sm-10 col-sm-offset-1">
										<?php echo $form->dropDownList($accountUser,'salary_type', $accountUser::salaryOptions(),array('empty'=>'- SELECT -', 'class'=>'col-sm-12', 'disabled'=>!Yii::app()->user->account->checkPermission('employees_employee_profile_hour_salary_dropdown','edit'))); ?>
										<?php echo $form->error($accountUser,'salary_type'); ?>
									</div>
								</div>
							<?php } ?>
							
							<?php if( Yii::app()->user->account->checkPermission('employees_employee_profile_pay_rate_field','visible') && Yii::app()->user->account->checkPermission('employees_employee_profile_pay_rate_field','only_for_direct_reports', $account->id) ){ ?>
								<div class="row">
									<?php echo $form->labelEx($accountUser,'pay_rate'); ?>
									
									<div class="col-sm-10 col-sm-offset-1">
										<?php echo $form->textField($accountUser,'pay_rate',array('class' => 'col-sm-12', 'disabled'=>!Yii::app()->user->account->checkPermission('employees_employee_profile_pay_rate_field','edit'))); ?>
										<?php echo $form->error($accountUser,'pay_rate'); ?>
									</div>	
								</div>
							<?php } ?>
							
							<?php if( Yii::app()->user->account->checkPermission('employees_employee_profile_commission_rate_field','visible') && Yii::app()->user->account->checkPermission('employees_employee_profile_commission_rate_field','only_for_direct_reports', $account->id) ){ ?>
								<div class="row commission-rate-field-container" style="display:<?php echo $accountUser->job_title == 'Sales Agent' ? '' : 'none'; ?>;">
									<?php echo $form->labelEx($accountUser,'commission_rate'); ?>
									
									<div class="col-sm-10 col-sm-offset-1">
										<?php echo $form->textField($accountUser,'commission_rate',array('class' => 'col-sm-12', 'placeholder'=>'example: 10%', 'disabled'=>!Yii::app()->user->account->checkPermission('employees_employee_profile_commission_rate_field','edit'))); ?>
										<?php echo $form->error($accountUser,'commission_rate'); ?>
									</div>	
								</div>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="widget-box">
					<div class="widget-body">
						<div class="widget-main">
						
							<?php if( Yii::app()->user->account->checkPermission('employees_employee_profile_security_group_dropdown','visible') && Yii::app()->user->account->checkPermission('employees_employee_profile_security_group_dropdown','only_for_direct_reports', $account->id) ){ ?>
								<div class="row">
									<?php echo $form->labelEx($account,'account_type_id'); ?>
									
									<div class="col-sm-10 col-sm-offset-1">
										<?php echo $form->dropDownList($account,'account_type_id', Account::listAccountType(),array('empty'=>'- SELECT -', 'class'=>'col-sm-12', 'disabled'=>!Yii::app()->user->account->checkPermission('employees_employee_profile_security_group_dropdown','edit'))); ?>
										<?php echo $form->error($account,'account_type_id'); ?>
									</div>
								</div>
							<?php } ?>
							
							<?php if( Yii::app()->user->account->checkPermission('employees_employee_profile_username_field','visible') && Yii::app()->user->account->checkPermission('employees_employee_profile_username_field','only_for_direct_reports', $account->id) ){ ?>
								<div class="row">
									<?php echo $form->labelEx($account,'username'); ?>
									
									<div class="col-sm-10 col-sm-offset-1">
										<?php echo $form->textField($account,'username',array('class' => 'col-sm-12', 'disabled'=>!Yii::app()->user->account->checkPermission('employees_employee_profile_username_field','edit'))); ?>
										<?php echo $form->error($account,'username'); ?>
									</div>	
								</div>
							<?php } ?>
							
							
							<?php if( (Yii::app()->user->account->getIsAdmin() || Yii::app()->user->account->getIsCustomerService()) && $account->login_attempt > 5){ ?>
								<div class="row">
									<label>Lock Reset</label>
									
									<div class="col-sm-10 col-sm-offset-1">
										<?php echo CHtml::link('Release Lock',array('accountUser/releaseLock','id'=>$account->id),array('class' => 'btn btn-default btn-xs')); ?>
									</div>	
								</div>
							<?php } ?>
					
							<?php if( Yii::app()->user->account->checkPermission('employees_employee_profile_password_field','visible') && Yii::app()->user->account->checkPermission('employees_employee_profile_password_field','only_for_direct_reports', $account->id) ){ ?>
								<div class="row">
									<?php echo $form->labelEx($account,'password'); ?>
									
									<div class="col-sm-10 col-sm-offset-1">
										<?php echo $form->passwordField($account,'password',array('class' => 'col-sm-12', 'disabled'=>!Yii::app()->user->account->checkPermission('employees_employee_profile_password_field','edit'))); ?>
										<?php echo $form->error($account,'password'); ?>
									</div>	
								</div>
							<?php } ?>
							
							<?php if( Yii::app()->user->account->checkPermission('employees_employee_profile_confirm_password_field','visible') && Yii::app()->user->account->checkPermission('employees_employee_profile_confirm_password_field','only_for_direct_reports', $account->id) ){ ?>
								<div class="row">
									<?php echo $form->labelEx($account,'confirmPassword'); ?>
									
									<div class="col-sm-10 col-sm-offset-1">
										<?php echo $form->passwordField($account,'confirmPassword',array('class' => 'col-sm-12', 'disabled'=>!Yii::app()->user->account->checkPermission('employees_employee_profile_confirm_password_field','edit'))); ?>
										<?php echo $form->error($account,'confirmPassword'); ?>
									</div>	
								</div>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<div class="clearfix"></div>
	<div class="space-12"></div>
	
	<?php if( Yii::app()->user->account->checkPermission('employees_employee_profile_save_button','visible') && Yii::app()->user->account->checkPermission('employees_employee_profile_save_button','only_for_direct_reports', $account->id) ){ ?>
		<div class="row form-actions center">
			<button class="btn btn-primary btn-sm">Save</button>
		</div>
	<?php } ?>

	<?php $this->endWidget(); ?>

</div>