<?php
/* @var $this CustomerOfficeStaffController */
/* @var $model CustomerOfficeStaff */
/* @var $form CActiveForm */
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'customer-office-staff-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
)); ?>

	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<?php echo $form->errorSummary($model); ?>

	<?php /*<div class="row">
		<?php echo $form->labelEx($model,'status'); ?>
		<?php echo $form->dropDownList($model,'status',CustomerOfficeStaff::listStatus()); ?>
		<?php echo $form->error($model,'status'); ?>
	</div>*/ ?>
	
	<div class="row">
		<?php echo $form->labelEx($model,'customer_id'); ?>
		<?php echo $form->dropDownList($model,'customer_id',CHtml::listData(Customer::model()->findAll(),'id','fullNameReverse'),array('empty' =>'-Select Customer-')); ?>
		<?php echo $form->error($model,'customer_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'customer_office_id'); ?>
		<?php echo $form->dropDownList($model,'customer_office_id',CHtml::listData(CustomerOffice::model()->byCustomerId($customer_id)->findAll(),'id','office_name'),array('empty' =>'-Select Office-')); ?>
		<?php echo $form->error($model,'customer_office_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'staff_name'); ?>
		<?php echo $form->textField($model,'staff_name',array('size'=>60,'maxlength'=>120)); ?>
		<?php echo $form->error($model,'staff_name'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'email_address'); ?>
		<?php echo $form->textField($model,'email_address',array('size'=>60,'maxlength'=>128)); ?>
		<?php echo $form->error($model,'email_address'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'position'); ?>
		<?php echo $form->textField($model,'position',array('size'=>60,'maxlength'=>120)); ?>
		<?php echo $form->error($model,'position'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'is_received_email'); ?>
		<?php echo $form->dropDownList($model,'is_received_email',array(1=> 'Yes', 0 => 'No')); ?>
		<?php echo $form->error($model,'is_received_email'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'is_portal_access'); ?>
		<?php echo $form->dropDownList($model,'is_portal_access',array(1=> 'Yes', 0 => 'No')); ?>
		<?php echo $form->error($model,'is_portal_access'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'phone'); ?>
		<?php echo $form->textField($model,'phone',array('size'=>60,'maxlength'=>60)); ?>
		<?php echo $form->error($model,'phone'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'mobile'); ?>
		<?php echo $form->textField($model,'mobile',array('size'=>60,'maxlength'=>60)); ?>
		<?php echo $form->error($model,'mobile'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'fax'); ?>
		<?php echo $form->textField($model,'fax',array('size'=>60,'maxlength'=>60)); ?>
		<?php echo $form->error($model,'fax'); ?>
	</div>


	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save',array('class'=>'btn btn-success')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->