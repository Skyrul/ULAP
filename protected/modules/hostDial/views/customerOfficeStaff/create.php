<?php

$this->widget("application.components.CustomerSideMenu",array(
		'active'=> Yii::app()->controller->id,
		'customer' => $model->customer,
));
?>

<h1>Add Office Staff</h1>


<?php $form=$this->beginWidget('CActiveForm', array(
	'enableAjaxValidation'=>false,
	'htmlOptions' => array(
		'class' => 'form-horizontal',
	),
)); ?>

	<div class="form">

		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> User Type </div>

				<div class="profile-info-value">
					<?php echo $form->dropDownList($model->account, 'account_type_id', array(15=>'Host Dialer', 16=>'Host Manager'), array('class'=>'form-control', 'style'=>'width:auto;', 'disabled'=>$inputDisabled)); ?>
				</div>
			</div>
		</div>

		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> Username <span class="required">*</span></div>

				<div class="profile-info-value">
					<?php echo $form->textField($model->account, 'username', array('class'=>'form-control', 'disabled'=>$inputDisabled)); ?>
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
		
		<div class="space-12"></div>
		
		<div class="row">
			<div class="form-actions center">
				<button type="button" class="btn btn-sm btn-primary" data-action="save">Save</button>
			</div>
		</div>
	</div>
	
<?php $this->endWidget(); ?>