<?php
/* @var $this SkillDispositionController */
/* @var $model SkillDisposition */
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
		<?php echo $form->label($model,'skill_child_id'); ?>
		<?php echo $form->textField($model,'skill_child_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'skill_disposition_name'); ?>
		<?php echo $form->textField($model,'skill_disposition_name',array('size'=>60,'maxlength'=>128)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'description'); ?>
		<?php echo $form->textField($model,'description',array('size'=>60,'maxlength'=>255)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'is_voice_contact'); ?>
		<?php echo $form->textField($model,'is_voice_contact'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'retry_interval'); ?>
		<?php echo $form->textField($model,'retry_interval',array('size'=>30,'maxlength'=>30)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'is_complete_leads'); ?>
		<?php echo $form->textField($model,'is_complete_leads'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'is_send_email'); ?>
		<?php echo $form->textField($model,'is_send_email'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton('Search'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- search-form -->