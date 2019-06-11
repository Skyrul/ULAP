
<div class="row">
	<div class="col-md-4">
		<?php echo CHtml::label($model->getAttributeLabel('schedule_start'),''); ?>
		<?php echo CHtml::dropDownList('StateSchedule[schedule_day]['.$day.']['.$name.'][schedule_start]', @$modelValue['schedule_start'], StateSchedule::listScheduleTime(),array('empty'=>'-- : --')); ?>
		<?php //echo $form->error($model,'schedule_start'); ?>
	</div>

	<div class="col-md-4">
		<?php echo CHtml::label($model->getAttributeLabel('schedule_end'),''); ?>
		<?php echo CHtml::dropDownList('StateSchedule[schedule_day]['.$day.']['.$name.'][schedule_end]', @$modelValue['schedule_end'], StateSchedule::listScheduleTime(),array('empty'=>'-- : --')); ?>
		<?php //echo $form->error($model,'schedule_end'); ?>
	</div>
	
	<div class="col-md-4">
		<br>
		<?php echo CHtml::button('Remove',array('class'=>'btn btn-xs btn-danger btn-remove-sched')); ?>
	</div>
	
</div>