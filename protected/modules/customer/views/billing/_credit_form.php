<div class="form">
	<?php $form=$this->beginWidget('CActiveForm', array(
		'enableAjaxValidation'=>false,
		'htmlOptions' => array(
			'class' => 'form-horizontal',
		),
	)); ?>
	
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> Description <span class="required">*</span></div>

				<div class="profile-info-value">
					<?php echo $form->textField($model, 'description', array('class'=>'form-control','disabled'=> $viewOnly)); ?>
				</div>
			</div>
		</div>
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> Type <span class="required">*</span></div>

				<div class="profile-info-value">
					<?php echo $form->dropDownList($model,'type', array(1=>'1 Month', 2=>'Month Range'), array('disabled'=> $viewOnly)); ?>
				</div>
			</div>
		</div>
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> Month <span class="required">*</span></div>

				<div class="profile-info-value">
					<?php echo $form->dropDownList($model,'start_month', CustomerCreditCard::cardExpirationMonths(), array('prompt'=>'- Month -', 'style'=>'margin-right:10px;', 'disabled'=> $viewOnly)); ?>
					<?php echo $form->dropDownList($model,'start_year', CustomerCreditCard::cardExpirationYears(), array('prompt'=>'- Year -', 'style'=>'margin-right:10px;', 'disabled'=> $viewOnly)); ?>
					
					<span class="end-month-range" style="display:<?php echo $model->type == 2 ? '' : 'none;'?>;">
						to
						<?php echo $form->dropDownList($model,'end_month', CustomerCreditCard::cardExpirationMonths(), array('prompt'=>'- Month -', 'style'=>'margin-left:10px;', 'disabled'=> $model->type == 2 ? false : true)); ?>
						<?php echo $form->dropDownList($model,'end_year', CustomerCreditCard::cardExpirationYears(), array('prompt'=>'- Year -', 'style'=>'margin-left:10px;', 'disabled'=> $model->type == 2 ? false : true)); ?>
					</span>
				</div>
			</div>
		</div>
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> Contract <span class="required">*</span></div>

				<div class="profile-info-value">
					<?php echo $form->dropDownList($model,'contract_id', CustomerSkill::getCustomerContracts($model->customer_id), array('prompt'=>'- Select -','disabled'=> $viewOnly)); ?>
				</div>
			</div>
		</div>
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> Amount <span class="required">*</span></div>

				<div class="profile-info-value">
					<?php echo $form->textField($model, 'amount', array('class'=>'form-control','disabled'=> $viewOnly)); ?>
				</div>
			</div>
		</div>
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> Date Created</div>

				<div class="profile-info-value">
					<?php 
						$dateCreated = new DateTime($model->date_created, new DateTimeZone('America/Chicago'));
						$dateCreated->setTimezone(new DateTimeZone('America/Denver')); 
						
						echo $dateCreated->format('m/d/Y g:i A');
					?>
				</div>
			</div>
		</div>
		
		<div class="space-12"></div>
		
		<div class="center">
			<button type="button" class="btn btn-sm btn-info" data-action="save">Save</button>
		</div>
	
	<?php $this->endWidget(); ?>
</div>