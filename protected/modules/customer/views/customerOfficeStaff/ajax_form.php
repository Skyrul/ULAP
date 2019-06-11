<?php 
	$inputDisabled = !Yii::app()->user->account->checkPermission('customer_offices_staff_list_settings_all_fields','edit') ? true : false;
	$originalInputDisabledValue = $inputDisabled;
?>

<div class="form">
	<?php $form=$this->beginWidget('CActiveForm', array(
		'enableAjaxValidation'=>false,
		'htmlOptions' => array(
			'class' => 'form-horizontal',
		),
	)); ?>
	
		
		<?php echo $form->hiddenField($model, 'customer_id'); ?>
		<?php echo $form->hiddenField($model, 'customer_office_id'); ?>
	
		<?php if( Yii::app()->user->account->checkPermission('customer_offices_staff_list_settings_all_fields','visible') ){ ?>
			
			<div class="profile-user-info profile-user-info-striped">
				<div class="profile-info-row">
					<div class="profile-info-name"> Name <span class="required">*</span></div>

					<div class="profile-info-value">
						<?php echo $form->textField($model, 'staff_name', array('class'=>'form-control', 'disabled'=>$inputDisabled)); ?>
					</div>
				</div>
			</div>
			
			<div class="profile-user-info profile-user-info-striped">
				<div class="profile-info-row">
					<div class="profile-info-name"> Email Address <span class="required">*</span></div>

					<div class="profile-info-value">
						<?php echo $form->textField($model, 'email_address', array('class'=>'form-control', 'disabled'=>$inputDisabled)); ?>
					</div>
				</div>
			</div>
			
			<div class="profile-user-info profile-user-info-striped">
				<div class="profile-info-row">
					<div class="profile-info-name"> Position </div>

					<div class="profile-info-value">
						<?php echo $form->textField($model, 'position', array('class'=>'form-control', 'disabled'=>$inputDisabled)); ?>
					</div>
				</div>
			</div>
			
			<div class="profile-user-info profile-user-info-striped">
				<div class="profile-info-row">
					<div class="profile-info-name"> Phone</div>

					<div class="profile-info-value">
						<?php echo $form->textField($model, 'phone', array('class'=>'form-control input-mask-phone', 'disabled'=>$inputDisabled)); ?>
					</div>
				</div>
			</div>
			
			<div class="profile-user-info profile-user-info-striped">
				<div class="profile-info-row">
					<div class="profile-info-name"> Mobile </div>

					<div class="profile-info-value">
						<?php echo $form->textField($model, 'mobile', array('class'=>'form-control input-mask-phone', 'disabled'=>$inputDisabled)); ?>
					</div>
				</div>
			</div>
			
			<div class="profile-user-info profile-user-info-striped">
				<div class="profile-info-row">
					<div class="profile-info-name"> Enable Texting </div>

					<div class="profile-info-value">
						<div class="col-sm-12">
							<div class="col-sm-2">
								<?php echo $form->dropDownList($model, 'enable_texting', array(1=>'Yes', 0=>'No'), array('class'=>'form-control', 'style'=>'width:auto;', 'disabled'=>$inputDisabled)); ?>
							</div>
							<div class="col-sm-10">
								By marking "Enable Texting" as YES you are agreeing to receive SMS messages at the mobile phone number provided above.  
								SMS messages will only be related to time sensitive Engagex dispositions from calls made to your customers.  
								Texting can be turned off by marking "Enable texting" as NO or typing STOP in the SMS message thread.
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<div class="profile-user-info profile-user-info-striped">
				<div class="profile-info-row">
					<div class="profile-info-name"> Fax </div>

					<div class="profile-info-value">
						<?php echo $form->textField($model, 'fax', array('class'=>'form-control input-mask-phone', 'disabled'=>$inputDisabled)); ?>
					</div>
				</div>
			</div>

			<div class="profile-user-info profile-user-info-striped">
				<div class="profile-info-row">
					<div class="profile-info-name"> Recieves Dispostion Emails </div>

					<div class="profile-info-value">
						<div class="col-sm-12">
							<?php echo $form->dropDownList($model, 'is_received_email', array(1=>'Individual Calendars', '3' => 'All Calendars', 2=>'No'), array('class'=>'form-control js-is-received-email', 'style'=>'width:auto;', 'disabled'=>$inputDisabled)); ?>
						</div>
						
						<?php 
							$isHidden = '';
								
							if($model->is_received_email != 1)
								$isHidden = 'hidden';
						?>
						
						<div class="col-sm-12 calenderReceiveEmails-container <?php echo $isHidden; ?>" style="margin-top:3px;">
							<?php 
								$selectedOptions = array();
								
								if( $existingCalenderStaffReceiveEmails )
								{
									foreach( $existingCalenderStaffReceiveEmails as $existingCalenderStaffReceiveEmail )
									{
										$selectedOptions[$existingCalenderStaffReceiveEmail->calendar_id] = array('selected'=>true);
									}
								}
								
								$htmlOptions = array(
									'class'=>'chosen tag-input-style js-calenderReceiveEmails', 
									'multiple'=>true, 
									'data-placeholder'=>'Select...',
									'style'=>'width:100%;',
									'options' => $selectedOptions, 
									'disabled'=>$inputDisabled
								);
								
								echo Chtml::dropDownList('calenderReceiveEmails[]', '', Calendar::items($model->customer_id, $model->customer_office_id), $htmlOptions); 
							?>
						</div>
					</div>
				</div>
			</div>
			
			<div class="profile-user-info profile-user-info-striped">
				<div class="profile-info-row">
					<div class="profile-info-name"> Receives Low on Names Emails </div>

					<div class="profile-info-value">
						<div class="col-sm-12">
							<?php echo $form->dropDownList($model, 'is_received_low_on_names_email', array(1=>'Yes', 2=>'No'), array('class'=>'form-control', 'style'=>'width:auto;', 'disabled'=>$inputDisabled)); ?>
						</div>
					</div>
				</div>
			</div>

			<div class="profile-user-info profile-user-info-striped">
				<div class="profile-info-row">
					<div class="profile-info-name"> Portal Access </div>

					<div class="profile-info-value">
						<?php 
							if( $model->account_id == null )
							{
								$inputDisabled = true;
							}
							
							if( $model->isNewRecord )
							{
								$inputDisabled = false;
							}
							
							echo $form->dropDownList($model, 'is_portal_access', array(1=>'Yes', 2=>'No'), array('class'=>'form-control', 'style'=>'width:auto;', 'disabled'=>$inputDisabled)); 
							
							$inputDisabled = $originalInputDisabledValue;
						?>
					</div>
				</div>
			</div>
			
			<?php if(!$model->isNewRecord){ ?>
			<div id="portal-access-container" style="<?php echo ($model->is_portal_access == 1) ? 'dislay:block;' : 'display:none'; ?>" >
				<?php if(isset($model->account) && !empty($model->account->username) ){ ?>
				<div class="profile-user-info profile-user-info-striped">
					<div class="profile-info-row">
						<div class="profile-info-name "><?php echo CHtml::label($model->account->getAttributeLabel('username'),''); ?></div>
						<div class="profile-info-value">
							<?php echo $form->textField($model->account,'username',array('maxlength'=>128,'disabled'=>true)); ?>
						</div>
					</div>
				</div>
				<?php }else{ ?>
 
					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name ">Account Setup</div>
							<div class="profile-info-value">
								<?php
									if( $inputDisabled )
									{
										echo CHtml::link('Resend Staff Email', '#',array('class' => 'btn btn-default btn-xs disabled'));
									}
									else
									{
										echo CHtml::link('Resend Staff Email',array('customerOfficeStaff/regenerateToken','id'=>$model->id),array('class' => 'btn btn-default btn-xs resend-staff-email')); 
									}
								?>
							</div>
						</div>
					</div>

				<?php } ?>
			</div>
			<?php } ?>
			<div class="profile-user-info profile-user-info-striped">
				<div class="profile-info-row">
					<div class="profile-info-name"> Status </div>

					<div class="profile-info-value">
						<?php 
							if( $model->account_id == null )
							{
								$inputDisabled = true;
							}
							
							echo $form->dropDownList($model, 'status', array(1=>'Active', 2=>'Inactive'), array('class'=>'form-control', 'style'=>'width:auto;', 'disabled'=>$inputDisabled)); 
						?>
					</div>
				</div>
			</div>
		
		<?php } ?>
		
		<div class="space-12"></div>
		
		<?php if( Yii::app()->user->account->checkPermission('customer_offices_staff_list_settings_save_button','visible') ){ ?>
		
		<div class="center">
			<button type="button" class="btn btn-sm btn-info" data-action="save">Save</button>
		</div>
		
		<?php } ?>
	
	<?php $this->endWidget(); ?>
</div>