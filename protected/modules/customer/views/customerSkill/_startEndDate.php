<?php 
Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl . '/template_assets/js/jquery.maskedinput.min.js');

Yii::app()->clientScript->registerScript(uniqid(), "
	$.mask.definitions['~']='[+-]';
	$('.input-mask-phone').mask('(999) 999-9999');

	$('.date-picker').datepicker({
		autoclose: true,
		todayHighlight: true
	});
	
	$(document).on('click', '.remove-date', function(){
		
		$(this).parent().find('.date-picker').val('');
		
	});
	  
", CClientScript::POS_END);



?>

<?php 
	$isCustomerDisabled = '';
															
	if( Yii::app()->user->account->getIsCustomer() || Yii::app()->user->account->getIsCustomerOfficeStaff() )
	{
		$isCustomerDisabled = 'disabled';
	}
?>

<?php if( Yii::app()->user->account->checkPermission('customer_skills_contract_start_date_field','visible') || Yii::app()->user->account->checkPermission('customer_skills_contract_end_date_field','visible') ){ ?>

<h3>Contract Start/End Date</h3>

<?php } ?>

<?php $form=$this->beginWidget('CActiveForm', array(
	// 'id'=>'customer-skill-schedule-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
)); ?>

<?php if( Yii::app()->user->account->checkPermission('customer_skills_contract_start_date_field','visible') ){ ?>

<div class="row">
	<div class="col-md-5">
		<?php echo $form->labelEx($customerSkill,'start_month'); ?>
		
		<div class="input-group">
			<?php 
				$startDateAddedClass = '';
				
				if( Yii::app()->user->account->checkPermission('customer_skills_contract_start_date_field','edit') )
				{
					$startDateAddedClass = ' date-picker ';
				}
				
				echo $form->textField($customerSkill, 'start_month', array('id' =>CHtml::activeId($customerSkill,'start_month').'_'.$customerSkill->id, 'class'=>'form-control'.$startDateAddedClass, 'disabled' => $isCustomerDisabled, 'readonly'=>true)); 
			?>
			
			<?php if( $startDateAddedClass != '' ){ ?>
				<span class="input-group-addon remove-date" style="cursor:pointer; background:#D15B47; border: 1px solid #d15b47; color:#ffffff;" title="Remove Date">
					<i class="ace-icon fa fa-times"></i>
				</span>
			<?php } ?>
		</div>
	</div>
</div>

<?php } ?>

<?php if( Yii::app()->user->account->checkPermission('customer_skills_contract_end_date_field','visible') ){ ?>

<div class="row">
	<div class="col-md-5">
		<?php echo $form->labelEx($customerSkill,'end_month'); ?>
		
		<div class="input-group">
			<?php 
				$endDateAddedClass = '';
				
				if( Yii::app()->user->account->checkPermission('customer_skills_contract_end_date_field','edit') )
				{
					$endDateAddedClass = ' date-picker ';
				}
				
				echo $form->textField($customerSkill, 'end_month', array('id' =>CHtml::activeId($customerSkill,'end_month').'_'.$customerSkill->id,'class'=>'form-control'.$endDateAddedClass, 'disabled' => $isCustomerDisabled, 'readonly'=>true)); 
			?>
			
			<?php if( $endDateAddedClass != '' ){ ?>
				<span class="input-group-addon remove-date" style="cursor:pointer; background:#D15B47; border: 1px solid #d15b47; color:#ffffff;" title="Remove Date">
					<i class="ace-icon fa fa-times"></i>
				</span>
			<?php } ?>
		</div>
	</div>
</div>

<?php } ?>

<?php if( Yii::app()->user->account->checkPermission('customer_skills_contract_hold_period_on_off_button','visible') ){ ?>

	<h3>Contract Hold Period</h3>

	<small>
		<input type="checkbox" class="toggle-contract-hold ace ace-switch ace-switch-1" value="<?php echo $customerSkill->id; ?>"  <?php echo ($customerSkill->is_contract_hold == 1) ? "checked" : ""; ?> <?php echo $isCustomerDisabled; ?>>
		<span class="lbl middle"></span>
	</small>
	
<?php } ?>
			
<div class="contract-hold-container" style="<?php echo ($customerSkill->is_contract_hold) ? "" : "display:none"; ?>">
	<div class="row">
		<div class="col-md-5">
			<?php echo $form->labelEx($customerSkill,'is_contract_hold_start_date'); ?>
			
			<div class="input-group">
				<?php echo $form->textField($customerSkill, 'is_contract_hold_start_date', array('id' =>CHtml::activeId($customerSkill,'is_contract_hold_start_date').'_'.$customerSkill->id,'class'=>'date-picker form-control', 'disabled' => $isCustomerDisabled, 'readonly'=>true)); ?>
				<span class="input-group-addon remove-date" style="cursor:pointer; background:#D15B47; border: 1px solid #d15b47; color:#ffffff;" title="Remove Date">
					<i class="ace-icon fa fa-times"></i>
				</span>
			</div>
		</div>
	</div>

	<div class="row">	
		<div class="col-md-5">
			<?php echo $form->labelEx($customerSkill,'is_contract_hold_end_date'); ?>
			
			<div class="input-group">
				<?php echo $form->textField($customerSkill, 'is_contract_hold_end_date', array('id' =>CHtml::activeId($customerSkill,'is_contract_hold_end_date').'_'.$customerSkill->id, 'class'=>'date-picker form-control', 'disabled' => $isCustomerDisabled, 'readonly'=>true)); ?>
				<span class="input-group-addon remove-date" style="cursor:pointer; background:#D15B47; border: 1px solid #d15b47; color:#ffffff;" title="Remove Date">
					<i class="ace-icon fa fa-times"></i>
				</span>
			</div>
		</div>
	</div>
</div>

<?php echo CHtml::hiddenField('submitted_customer_skill_id', $customerSkill->id); ?>
	
<div class="space-6"></div>
	
<div class="row buttons">
	<div class="col-md-5 center">
		<?php if( empty($isCustomerDisabled) && Yii::app()->user->account->checkPermission('customer_skills_contract_save_button','visible') ){ ?>
			<button type="submit" class="btn btn-success btn-xs"><i class="fa fa-check"></i> Save</button>
		<?php } ?>
	</div>
</div>
	
<?php $this->endWidget(); ?>

<div class="space-12"></div>
<div class="space-12"></div>
