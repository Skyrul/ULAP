
<div class="row">
	<div class="col-md-4">
		<?php echo CHtml::label($model->getAttributeLabel('schedule_start'),''); ?>
		<?php echo CHtml::dropDownList('SkillSchedule[schedule_day]['.$day.']['.$name.'][schedule_start]', @$modelValue['schedule_start'], SkillSchedule::listScheduleTime(),array('empty'=>'-- : --')); ?>
		<?php //echo $form->error($model,'schedule_start'); ?>
	</div>

	<div class="col-md-4">
		<?php echo CHtml::label($model->getAttributeLabel('schedule_end'),''); ?>
		<?php echo CHtml::dropDownList('SkillSchedule[schedule_day]['.$day.']['.$name.'][schedule_end]', @$modelValue['schedule_end'], SkillSchedule::listScheduleTime(),array('empty'=>'-- : --')); ?>
		<?php //echo $form->error($model,'schedule_end'); ?>
	</div>

	<?php /*
	<div class="col-md-3">
		<?php echo CHtml::label($model->getAttributeLabel('status'),''); ?>
		<?php echo CHtml::dropDownList('SkillSchedule[schedule_day]['.$day.']['.$name.'][status]', @$modelValue['status'],  SkillSchedule::listStatus()); ?>
		<?php ///echo $form->error($model,'status'); ?>
	</div> */
	?>
</div>