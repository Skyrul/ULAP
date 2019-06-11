<?php
/* @var $this SkillScheduleController */
/* @var $data SkillSchedule */
?>

<div class="view">

	<b><?php echo CHtml::encode($data->getAttributeLabel('id')); ?>:</b>
	<?php echo CHtml::link(CHtml::encode($data->id), array('view', 'id'=>$data->id)); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('skill_id')); ?>:</b>
	<?php echo CHtml::encode($data->skill_id); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('schedule_start')); ?>:</b>
	<?php echo CHtml::encode($data->schedule_start); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('schedule_end')); ?>:</b>
	<?php echo CHtml::encode($data->schedule_end); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('schedule_day')); ?>:</b>
	<?php echo CHtml::encode($data->schedule_day); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('status')); ?>:</b>
	<?php echo CHtml::encode($data->status); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('is_deleted')); ?>:</b>
	<?php echo CHtml::encode($data->is_deleted); ?>
	<br />

	<?php /*
	<b><?php echo CHtml::encode($data->getAttributeLabel('date_created')); ?>:</b>
	<?php echo CHtml::encode($data->date_created); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('date_updated')); ?>:</b>
	<?php echo CHtml::encode($data->date_updated); ?>
	<br />

	*/ ?>

</div>