<?php
$this->pageTitle=Yii::app()->name . ' - Registration';
$this->breadcrumbs=array(
    'Forgot Password',
);
?>

<h1>Registration</h1>

<?php
/* @var $this AccountUserController */
/* @var $model AccountUser */
/* @var $form CActiveForm */
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'account-user-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
)); ?>

	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<?php echo $form->errorSummary(array($account)); ?>
	
	<div class="row">
		<div class="col-md-12">
		<?php echo $form->labelEx($account,'email_address'); ?>
			<?php echo $form->textField($account,'email_address'); ?>
		<?php echo $form->error($account,'email_address'); ?>
		</div>
	</div>
	
	<div class="row">
		<div class="col-md-12">
		<?php echo $form->labelEx($account,'username'); ?>
			<?php echo $form->textField($account,'username'); ?>
		<?php echo $form->error($account,'username'); ?>
		</div>
	</div>
	
	<div class="row">
		<div class="col-md-12">
		<?php echo $form->labelEx($account,'password'); ?>
		<?php echo $form->passwordField($account,'password',array('size'=>60,'maxlength'=>80)); ?>
		<?php echo $form->error($account,'password'); ?>
		</div>
	</div>
	
	<div class="row">
		<div class="col-md-12">
		<?php echo $form->labelEx($account,'confirmPassword'); ?>
		<?php echo $form->passwordField($account,'confirmPassword',array('size'=>60,'maxlength'=>80)); ?>
		<?php echo $form->error($account,'confirmPassword'); ?>
		</div>
	</div>

	<div class="row buttons">
		<div class="col-md-12">
		<?php echo CHtml::submitButton($account->isNewRecord ? 'Create' : 'Save', array('class'=> 'btn btn-success')); ?>
		</div>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->

<?php Yii::app()->clientScript->registerScript('fieldDatePIcker','

	$(".date-picker").datepicker({
		autoclose: true,
		todayHighlight: true
	})

	
',CClientScript::POS_END); ?>