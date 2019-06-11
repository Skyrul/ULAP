<?php
/* @var $this ContractController */
/* @var $data Contract */
?>

<div class="view">

	<b><?php echo CHtml::encode($data->getAttributeLabel('id')); ?>:</b>
	<?php echo CHtml::link(CHtml::encode($data->id), array('view', 'id'=>$data->id)); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('company_id')); ?>:</b>
	<?php echo CHtml::encode($data->company_id); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('skill_id')); ?>:</b>
	<?php echo CHtml::encode($data->skill_id); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('contract_name')); ?>:</b>
	<?php echo CHtml::encode($data->contract_name); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('description')); ?>:</b>
	<?php echo CHtml::encode($data->description); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('billing_calculation')); ?>:</b>
	<?php echo CHtml::encode($data->billing_calculation); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('fulfillment_type')); ?>:</b>
	<?php echo CHtml::encode($data->fulfillment_type); ?>
	<br />

	<?php /*
	<b><?php echo CHtml::encode($data->getAttributeLabel('is_subsidy')); ?>:</b>
	<?php echo CHtml::encode($data->is_subsidy); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('subsidy_name')); ?>:</b>
	<?php echo CHtml::encode($data->subsidy_name); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('subsidy_expiration')); ?>:</b>
	<?php echo CHtml::encode($data->subsidy_expiration); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('is_fee_start_activate')); ?>:</b>
	<?php echo CHtml::encode($data->is_fee_start_activate); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('start_fee_amount')); ?>:</b>
	<?php echo CHtml::encode($data->start_fee_amount); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('start_fee_day')); ?>:</b>
	<?php echo CHtml::encode($data->start_fee_day); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('start_fee_billing_cycle')); ?>:</b>
	<?php echo CHtml::encode($data->start_fee_billing_cycle); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('status')); ?>:</b>
	<?php echo CHtml::encode($data->status); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('is_deleted')); ?>:</b>
	<?php echo CHtml::encode($data->is_deleted); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('date_created')); ?>:</b>
	<?php echo CHtml::encode($data->date_created); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('date_updated')); ?>:</b>
	<?php echo CHtml::encode($data->date_updated); ?>
	<br />

	*/ ?>

</div>