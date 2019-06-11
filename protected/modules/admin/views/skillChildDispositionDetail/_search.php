<?php
/* @var $this SkillDispositionDetailController */
/* @var $model SkillDispositionDetail */
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
		<?php echo $form->label($model,'skill_id'); ?>
		<?php echo $form->textField($model,'skill_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'skill_disposition_id'); ?>
		<?php echo $form->textField($model,'skill_disposition_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'skill_disposition_detail_name'); ?>
		<?php echo $form->textField($model,'skill_disposition_detail_name',array('size'=>60,'maxlength'=>150)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'description'); ?>
		<?php echo $form->textField($model,'description',array('size'=>60,'maxlength'=>150)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'internal_notes'); ?>
		<?php echo $form->textArea($model,'internal_notes',array('rows'=>6, 'cols'=>50)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'external_notes'); ?>
		<?php echo $form->textArea($model,'external_notes',array('rows'=>6, 'cols'=>50)); ?>
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