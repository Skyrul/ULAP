<?php 
$isCustomerDisabled = "";
														
if(Yii::app()->user->account->getIsCustomer() || Yii::app()->user->account->getIsCustomerOfficeStaff())
	$isCustomerDisabled = 'disabled';
?>

<h3>Dialing Settings</h3>

<?php $form=$this->beginWidget('CActiveForm', array(
	// 'id'=>'customer-skill-schedule-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
)); ?>

<?php echo CHtml::radioButtonList('dialing_setting', $customerSkill->skill_caller_option_customer_choice,CustomerSkill::listCustomerChoiceOption(), array('disabled' => $isCustomerDisabled)); ?>

<div class="row buttons">
	<div class="col-md-12">
		<?php if( empty($isCustomerDisabled) && Yii::app()->user->account->checkPermission('customer_skills_save_dialing_settings_button','visible') ){ ?>
			<?php echo CHtml::submitButton('Save Dialing Setting' ,array('class'=>'btn btn-success btn-xs')); ?>
		<?php } ?>
	</div>
</div>
	
<?php $this->endWidget(); ?>
