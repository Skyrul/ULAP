<div class="form">
	<?php $form=$this->beginWidget('CActiveForm', array(
		'enableAjaxValidation'=>false,
		'htmlOptions' => array(
			'class' => 'form-horizontal',
		),
	)); ?>
	
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> First Name <span class="required">*</span></div>

				<div class="profile-info-value">
					<?php echo $form->textField($model, 'first_name', array('class'=>'form-control','disabled'=> $viewOnly)); ?>
				</div>
			</div>
		</div>
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> Last Name <span class="required">*</span></div>

				<div class="profile-info-value">
					<?php echo $form->textField($model, 'last_name', array('class'=>'form-control','disabled'=> $viewOnly)); ?>
				</div>
			</div>
		</div>
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> Phone Number <span class="required">*</span></div>

				<div class="profile-info-value">
					<?php echo $form->textField($model, 'phone_number', array('class'=>'form-control input-mask-phone','disabled'=> $viewOnly)); ?>
				</div>
			</div>
		</div>
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> Address <span class="required">*</span></div>

				<div class="profile-info-value">
					<?php echo $form->textField($model, 'address', array('class'=>'form-control','disabled'=> $viewOnly)); ?>
				</div>
			</div>
		</div>
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> City <span class="required">*</span></div>

				<div class="profile-info-value">
					<?php echo $form->textField($model, 'city', array('class'=>'form-control','disabled'=> $viewOnly)); ?>
				</div>
			</div>
		</div>
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> State <span class="required">*</span></div>

				<div class="profile-info-value">
					<?php echo $form->dropDownList($model,'state',State::listStates(),array('empty'=>'-Select State-','disabled'=> $viewOnly)); ?>
				</div>
			</div>
		</div>
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> Zip <span class="required">*</span></div>

				<div class="profile-info-value">
					<?php echo $form->textField($model, 'zip', array('class'=>'form-control input-mask-zip','disabled'=> $viewOnly)); ?>
				</div>
			</div>
		</div>
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> Credit Card Nick Name</div>

				<div class="profile-info-value">
					<?php echo $form->textField($model, 'nick_name', array('class'=>'form-control','disabled'=> $viewOnly)); ?>
				</div>
			</div>
		</div>
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> Card Type <span class="required">*</span></div>

				<div class="profile-info-value">
					<?php echo $form->dropDownList($model, 'credit_card_type', $model::cardTypes(), array('prompt'=>'- Select -', 'class'=>'form-control', 'style'=>'width:auto;','disabled'=> $viewOnly)); ?>
				</div>
			</div>
		</div>
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> Card Number <span class="required">*</span></div>

				<div class="profile-info-value">
					<div class="pull-left">
						<?php echo $form->passwordField($model, 'credit_card_number', array('class'=>'form-control', 'maxLength'=>16, 'disabled'=> $viewOnly, 'style'=>'width:150px;')); ?>
					</div>
					
					<div class="pull-left">
						<span class="cc-show-last-4" style="line-height:40px; margin-left:15px;"></span>
					</div>						
				</div>
			</div>
		</div>
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> Security Code <span class="required">*</span></div>

				<div class="profile-info-value">
					<?php echo $form->textField($model, 'security_code', array('class'=>'form-control', 'maxLength'=>3,'disabled'=> $viewOnly)); ?>
				</div>
			</div>
		</div>
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> Card Expiration <span class="required">*</span></div>

				<div class="profile-info-value">
					<?php echo $form->dropDownList($model,'expiration_month', $model::cardExpirationMonths(), array('prompt'=>'- Month -','disabled'=> $viewOnly)); ?>
					
					<?php echo $form->dropDownList($model,'expiration_year', $model::cardExpirationYears(), array('prompt'=>'- Year -','disabled'=> $viewOnly)); ?>
				</div>
			</div>
		</div>
		
		<div class="space-12"></div>
		
		<div class="center">
			<button type="button" class="btn btn-sm btn-info" data-action="save">Save</button>
		</div>
	
	<?php $this->endWidget(); ?>
</div>