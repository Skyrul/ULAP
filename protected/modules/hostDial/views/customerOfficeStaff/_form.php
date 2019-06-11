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
					<div class="profile-info-name"> User Type </div>

					<div class="profile-info-value">
						<?php echo $form->dropDownList($model, 'type', array(1=>'Host Dialer', 2=>'Host Manager'), array('class'=>'form-control', 'style'=>'width:auto;', 'disabled'=>$inputDisabled)); ?>
					</div>
				</div>
			</div>
			
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
					<div class="profile-info-name"> Phone</div>

					<div class="profile-info-value">
						<?php echo $form->textField($model, 'phone', array('class'=>'form-control input-mask-phone', 'disabled'=>$inputDisabled)); ?>
					</div>
				</div>
			</div>
			
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
			
		
		<?php } ?>
		
		<div class="space-12"></div>
		
		<?php if( Yii::app()->user->account->checkPermission('customer_offices_staff_list_settings_save_button','visible') ){ ?>
		
		<div class="form-actions center">
			<button type="button" class="btn btn-sm btn-primary" data-action="save">Save</button>
		</div>
		
		<?php } ?>
	
	<?php $this->endWidget(); ?>
</div>