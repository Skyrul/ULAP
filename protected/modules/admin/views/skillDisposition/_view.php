<?php
/* @var $this SkillDispositionController */
/* @var $data SkillDisposition */
?>

<div class="view">

	<b><?php echo CHtml::encode($data->getAttributeLabel('id')); ?>:</b>
	<?php echo CHtml::link(CHtml::encode($data->id), array('view', 'id'=>$data->id)); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('skill_id')); ?>:</b>
	<?php echo CHtml::encode($data->skill_id); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('skill_disposition_name')); ?>:</b>
	<?php echo CHtml::encode($data->skill_disposition_name); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('description')); ?>:</b>
	<?php echo CHtml::encode($data->description); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('is_voice_contact')); ?>:</b>
	<?php echo CHtml::encode($data->is_voice_contact); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('retry_interval')); ?>:</b>
	<?php echo CHtml::encode($data->retry_interval); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('is_complete_leads')); ?>:</b>
	<?php echo CHtml::encode($data->is_complete_leads); ?>
	<br />

	<?php /*
	<b><?php echo CHtml::encode($data->getAttributeLabel('is_send_email')); ?>:</b>
	<?php echo CHtml::encode($data->is_send_email); ?>
	<br />

	*/ ?>

</div>