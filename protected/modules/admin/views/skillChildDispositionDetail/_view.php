<?php
/* @var $this SkillDispositionDetailController */
/* @var $data SkillDispositionDetail */
?>

<div class="view">

	<b><?php echo CHtml::encode($data->getAttributeLabel('id')); ?>:</b>
	<?php echo CHtml::link(CHtml::encode($data->id), array('view', 'id'=>$data->id)); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('skill_id')); ?>:</b>
	<?php echo CHtml::encode($data->skill_id); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('skill_disposition_id')); ?>:</b>
	<?php echo CHtml::encode($data->skill_disposition_id); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('skill_disposition_detail_name')); ?>:</b>
	<?php echo CHtml::encode($data->skill_disposition_detail_name); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('description')); ?>:</b>
	<?php echo CHtml::encode($data->description); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('internal_notes')); ?>:</b>
	<?php echo CHtml::encode($data->internal_notes); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('external_notes')); ?>:</b>
	<?php echo CHtml::encode($data->external_notes); ?>
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