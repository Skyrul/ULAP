<?php
/* @var $this SkillDispositionDetailController */
/* @var $model SkillDispositionDetail */
/* @var $form CActiveForm */
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'skill-disposition-detail-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
	'htmlOptions' => array('class'=>'form'),
)); ?>

	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<?php echo $form->errorSummary($model); ?>

	
	<div class="form-group">
		<?php //echo $form->labelEx($model,'skill_disposition_id'); ?>
		<?php echo $form->hiddenField($model,'skill_disposition_id'); ?>
		<?php //echo $form->error($model,'skill_disposition_id'); ?>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'skill_disposition_detail_name', array('class'=>'control-label col-sm-2')); ?>
		
		<div class="col-sm-10">
			<?php echo $form->textField($model,'skill_disposition_detail_name',array('size'=>60,'maxlength'=>150)); ?>
			<?php echo $form->error($model,'skill_disposition_detail_name'); ?>
		</div>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'description', array('class'=>'control-label col-sm-2')); ?>
		
		<div class="col-sm-10">
			<?php echo $form->textField($model,'description',array('size'=>60,'maxlength'=>150)); ?>
			<?php echo $form->error($model,'description'); ?>
		</div>
	</div>

	<?php /*<div class="form-group">
		<?php echo $form->labelEx($model,'internal_notes'); ?>
		<?php echo $form->textArea($model,'internal_notes',array('form-groups'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'internal_notes'); ?>
	</div>*/ ?>

	<div class="form-group">
		<?php echo $form->labelEx($model,'external_notes', array('class'=>'control-label col-sm-2')); ?>
		
		<div class="col-sm-10">
			<?php echo $form->textArea($model,'external_notes',array('form-groups'=>6, 'cols'=>50)); ?>
			<?php echo $form->error($model,'external_notes'); ?>
		</div>
	</div>
	
	<div class="clearfix"></div>
	
	<div class="form-actions text-center">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save', array('class' => 'btn btn-info btn-xs')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->