<?php
/* @var $this AccountUserController */
/* @var $data AccountUser */
?>

<div class="view">

	<b><?php echo CHtml::encode($data->getAttributeLabel('id')); ?>:</b>
	<?php echo CHtml::link(CHtml::encode($data->id), array('view', 'id'=>$data->id)); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('account_id')); ?>:</b>
	<?php echo CHtml::encode($data->account_id); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('first_name')); ?>:</b>
	<?php echo CHtml::encode($data->first_name); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('last_name')); ?>:</b>
	<?php echo CHtml::encode($data->last_name); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('salary')); ?>:</b>
	<?php echo CHtml::encode($data->salary); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('salary_type')); ?>:</b>
	<?php echo CHtml::encode($data->salary_type); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('date_hire')); ?>:</b>
	<?php echo CHtml::encode($data->date_hire); ?>
	<br />

	<?php /*
	<b><?php echo CHtml::encode($data->getAttributeLabel('date_termination')); ?>:</b>
	<?php echo CHtml::encode($data->date_termination); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('language')); ?>:</b>
	<?php echo CHtml::encode($data->language); ?>
	<br />

	*/ ?>

</div>