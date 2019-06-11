<?php 
$baseUrl = Yii::app()->request->baseUrl;

$cs = Yii::app()->clientScript;

$cs->registerScriptFile( $baseUrl . '/template_assets/js/jquery.maskedinput.min.js');

$cs->registerScript(uniqid(), "
	$.mask.definitions['~']='[+-]';
	$('.input-mask-phone').mask('(999) 999-9999');
	$('.input-mask-zip').mask('99999');
", CClientScript::POS_END);

$inputDisabled = !Yii::app()->user->account->checkPermission('customer_offices_office_settings_all_fields','edit') ? true : false;

?>

<div class="form">
	<?php $form=$this->beginWidget('CActiveForm', array(
		'enableAjaxValidation'=>false,
		'htmlOptions' => array(
			'class' => 'form-horizontal',
		),
	)); ?>
	
		
		<?php echo $form->hiddenField($model, 'customer_id'); ?>
	
		<?php if( Yii::app()->user->account->checkPermission('customer_offices_office_settings_all_fields','visible') ){ ?>
	
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> Name <span class="required">*</span></div>

				<div class="profile-info-value">
					<?php echo $form->textField($model, 'office_name', array('class'=>'form-control', 'disabled'=>$inputDisabled)); ?>
				</div>
			</div>
		</div>
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> Address <span class="required">*</span></div>

				<div class="profile-info-value">
					<?php echo $form->textField($model, 'address', array('class'=>'form-control', 'disabled'=>$inputDisabled)); ?>
				</div>
			</div>
		</div>
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> Phone <span class="required">*</span></div>

				<div class="profile-info-value">
					<?php echo $form->textField($model, 'phone', array('class'=>'form-control input-mask-phone', 'disabled'=>$inputDisabled)); ?>
				</div>
			</div>
		</div>
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> City <span class="required">*</span></div>

				<div class="profile-info-value">
					<?php echo $form->textField($model, 'city', array('class'=>'form-control', 'disabled'=>$inputDisabled)); ?>
				</div>
			</div>
		</div>
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> Fax </div>

				<div class="profile-info-value">
					<?php echo $form->textField($model, 'fax', array('class'=>'form-control', 'class'=>'input-mask-phone', 'disabled'=>$inputDisabled)); ?>
				</div>
			</div>
		</div>
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> State <span class="required">*</span></div>

				<div class="profile-info-value">
					<?php echo $form->dropDownList($model,'state',State::listStates(),array('empty'=>'-Select State-', 'disabled'=>$inputDisabled)); ?>
				</div>
			</div>
		</div>
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> Zip <span class="required">*</span></div>

				<div class="profile-info-value">
					<?php echo $form->textField($model, 'zip', array('class'=>'form-control input-mask-zip', 'disabled'=>$inputDisabled)); ?>
				</div>
			</div>
		</div>
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> Directions </div>

				<div class="profile-info-value">
					<?php echo $form->textField($model, 'directions', array('class'=>'form-control', 'disabled'=>$inputDisabled)); ?>
				</div>
			</div>
		</div>
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> Landmark </div>

				<div class="profile-info-value">
					<?php echo $form->textField($model, 'landmark', array('class'=>'form-control', 'disabled'=>$inputDisabled)); ?>
				</div>
			</div>
		</div>
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> Status </div>

				<div class="profile-info-value">
					<?php echo $form->dropDownList($model, 'status', array(1=>'Active', 2=>'Inactive'), array('class'=>'form-control', 'style'=>'width:auto;', 'disabled'=>$inputDisabled)); ?>
				</div>
			</div>
		</div>
		
		<?php } ?>
		
		<div class="space-12"></div>
		
		<?php if( Yii::app()->user->account->checkPermission('customer_offices_office_settings_save_button','visible') ){ ?>
		
		<div class="center">
			<button type="button" class="btn btn-sm btn-info save-office-btn" data-action="save">Save</button>
		</div>
		
		<?php } ?>
	
	<?php $this->endWidget(); ?>
</div>