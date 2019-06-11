<div class="form">
	<?php $form=$this->beginWidget('CActiveForm', array(
		'enableAjaxValidation'=>false,
		'htmlOptions' => array(
			'class' => 'form-horizontal',
		),
	)); ?>
	
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> Account # <span class="required">*</span></div>

				<div class="profile-info-value">
					<?php echo $form->textField($model, 'account_number', array('class'=>'form-control', 'disabled'=> $viewOnly)); ?>
				</div>
			</div>
		</div>
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> Routing # <span class="required">*</span></div>

				<div class="profile-info-value">
					<?php echo $form->textField($model, 'routing_number', array('class'=>'form-control','disabled'=> $viewOnly)); ?>
				</div>
			</div>
		</div>
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> Account Type <span class="required">*</span></div>

				<div class="profile-info-value">
				
					<?php 
						$achAccountTypes = array(
							''=>'- Select -',
							'CHECKING'=>'CHECKING',
							'SAVINGS'=>'SAVINGS',
						); 
					?>
					
					<?php echo $form->dropDownList($model,'account_type', $achAccountTypes, array('class'=>'form-control', 'style'=>'width:auto;','disabled'=> $viewOnly)); ?>
				</div>
			</div>
		</div>
		
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> Entity Name</div>

				<div class="profile-info-value">
					<?php 
						$entityTypes = array(
							''=>'- Select -',
							'Business'=>'Business',
							'Personal'=>'Personal',
						); 
					?>
					
					<?php echo $form->dropDownList($model, 'entity_name', $entityTypes, array('class'=>'form-control', 'style'=>'width:auto;', 'disabled'=> $viewOnly)); ?>
				</div>
			</div>
		</div>
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> Name on Account <span class="required">*</span></div>

				<div class="profile-info-value">
					<?php echo $form->textField($model, 'account_name', array('class'=>'form-control','disabled'=> $viewOnly)); ?>
				</div>
			</div>
		</div>
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> Institution <span class="required">*</span></div>

				<div class="profile-info-value">
					<?php echo $form->textField($model, 'institution_name', array('class'=>'form-control','disabled'=> $viewOnly)); ?>
				</div>
			</div>
		</div>
		
		<div class="space-12"></div>
		
		<div class="center">
			<button type="button" class="btn btn-sm btn-info" data-action="save">Save</button>
		</div>
	
	<?php $this->endWidget(); ?>
</div>