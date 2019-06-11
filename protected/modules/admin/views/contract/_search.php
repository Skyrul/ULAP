<?php
/* @var $this ContractController */
/* @var $model Contract */
/* @var $form CActiveForm */
?>

<div class="wide form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'action'=>Yii::app()->createUrl($this->route),
	'method'=>'get',
)); ?>

	<div class="row">
		<?php echo $form->label($model,'id'); ?>
		<?php echo $form->textField($model,'id'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'company_id'); ?>
		<?php echo $form->textField($model,'company_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'skill_id'); ?>
		<?php echo $form->textField($model,'skill_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'contract_name'); ?>
		<?php echo $form->textField($model,'contract_name',array('size'=>60,'maxlength'=>128)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'description'); ?>
		<?php echo $form->textField($model,'description',array('size'=>60,'maxlength'=>250)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'billing_calculation'); ?>
		<?php echo $form->textField($model,'billing_calculation',array('size'=>60,'maxlength'=>60)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'fulfillment_type'); ?>
		<?php echo $form->textField($model,'fulfillment_type',array('size'=>60,'maxlength'=>60)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'is_subsidy'); ?>
		<?php echo $form->textField($model,'is_subsidy'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'subsidy_name'); ?>
		<?php echo $form->textField($model,'subsidy_name',array('size'=>60,'maxlength'=>128)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'subsidy_expiration'); ?>
		<?php echo $form->textField($model,'subsidy_expiration'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'is_fee_start_activate'); ?>
		<?php echo $form->textField($model,'is_fee_start_activate'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'start_fee_amount'); ?>
		<?php echo $form->textField($model,'start_fee_amount'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'start_fee_day'); ?>
		<?php echo $form->textField($model,'start_fee_day'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'start_fee_billing_cycle'); ?>
		<?php echo $form->textField($model,'start_fee_billing_cycle'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'status'); ?>
		<?php echo $form->textField($model,'status'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'is_deleted'); ?>
		<?php echo $form->textField($model,'is_deleted'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'date_created'); ?>
		<?php echo $form->textField($model,'date_created'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'date_updated'); ?>
		<?php echo $form->textField($model,'date_updated'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton('Search'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- search-form -->