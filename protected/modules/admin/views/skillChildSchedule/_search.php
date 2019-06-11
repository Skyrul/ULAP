<?php
/* @var $this SkillScheduleController */
/* @var $model SkillSchedule */
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
		<?php echo $form->label($model,'schedule_start'); ?>
		<?php echo $form->textField($model,'schedule_start'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'schedule_end'); ?>
		<?php echo $form->textField($model,'schedule_end'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'schedule_day'); ?>
		<?php echo $form->textField($model,'schedule_day'); ?>
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