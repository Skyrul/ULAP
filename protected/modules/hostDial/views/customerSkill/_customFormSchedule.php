<div class="row">
	<div class="col-md-4">
		<?php echo CHtml::label($model->getAttributeLabel('schedule_start'),''); ?>
		<?php echo CHtml::dropDownList('CustomerSkillSchedule[schedule_day]['.$day.']['.$name.'][schedule_start]', @$modelValue['schedule_start'], CustomerSkillSchedule::listScheduleTime(),array('empty'=>'-- : --', 'disabled'=> $isCustomerDisabled)); ?>
		<?php //echo $form->error($model,'schedule_start'); ?>
	</div>

	<div class="col-md-4">
		<?php echo CHtml::label($model->getAttributeLabel('schedule_end'),''); ?>
		<?php echo CHtml::dropDownList('CustomerSkillSchedule[schedule_day]['.$day.']['.$name.'][schedule_end]', @$modelValue['schedule_end'], CustomerSkillSchedule::listScheduleTime(),array('empty'=>'-- : --', 'disabled' => $isCustomerDisabled)); ?>
		<?php //echo $form->error($model,'schedule_end'); ?>
	</div>

	<?php /*
	<div class="col-md-4">
		<?php echo CHtml::label($model->getAttributeLabel('status'),''); ?>
		<?php echo CHtml::dropDownList('CustomerSkillSchedule[schedule_day]['.$day.'][status]', @$modelValue['status'],  CustomerSkillSchedule::listStatus()); ?>
		<?php ///echo $form->error($model,'status'); ?>
	</div>
	
	*/ ?>
	
	<div class="col-md-4">
		<br>
		<?php if(empty($isCustomerDisabled)){ ?>
			<?php echo CHtml::button('Remove',array('class'=>'btn btn-xs btn-danger btn-remove-sched')); ?>
		<?php } ?>
	</div>
</div>