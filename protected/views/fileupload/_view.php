<?php
/* @var $this FileuploadController */
/* @var $data Fileupload */
?>

<div class="view">

	<b><?php echo CHtml::encode($data->getAttributeLabel('id')); ?>:</b>
	<?php echo CHtml::link(CHtml::encode($data->id), array('view', 'id'=>$data->id)); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('original_filename')); ?>:</b>
	<?php echo CHtml::encode($data->original_filename); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('generated_filename')); ?>:</b>
	<?php echo CHtml::encode($data->generated_filename); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('date_created')); ?>:</b>
	<?php echo CHtml::encode($data->date_created); ?>
	<br />


</div>