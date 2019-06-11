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
						<?php echo $form->dropDownList($account, 'account_type_id', array(15=>'Host Dialer', 16=>'Host Manager'), array('class'=>'form-control', 'style'=>'width:auto;', 'disabled'=>$inputDisabled)); ?>
					</div>
				</div>
			</div>
			
			<div class="profile-user-info profile-user-info-striped">
				<div class="profile-info-row">
					<div class="profile-info-name"> Username <span class="required">*</span></div>

					<div class="profile-info-value">
						<?php echo $form->textField($account, 'username', array('class'=>'form-control', 'disabled'=>$inputDisabled)); ?>
					</div>
				</div>
			</div>
			
			<div class="profile-user-info profile-user-info-striped">
				<div class="profile-info-row">
					<div class="profile-info-name"> Password <span class="required">*</span></div>

					<div class="profile-info-value">
						<?php echo $form->textField($account, 'password', array('class'=>'form-control', 'disabled'=>$inputDisabled)); ?>
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
					<div class="profile-info-name"> Email Address</div>

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
		
		<?php } ?>
		
		<div class="space-12"></div>
		
		<?php if( Yii::app()->user->account->checkPermission('customer_offices_staff_list_settings_save_button','visible') ){ ?>
		
		<div class="center">
			<button type="button" class="btn btn-sm btn-info" data-action="save">Save</button>
		</div>
		
		<?php } ?>
	
	<?php $this->endWidget(); ?>
</div>