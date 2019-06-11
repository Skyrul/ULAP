<table class="lead-volume-entries table">
	<tr>
		<th>Low</th>
		<th>High</th>
		<th>Amount</th>
		
		<?php /*
		<th>&nbsp;</th>
		<th>Subsidy</th>
		*/ ?>
		<th>&nbsp;</th>
	</tr>
	
	<tr>
		<?php echo CHtml::hiddenField('Contract[subsidyLevelArray]['.Contract::TYPE_FULFILLMENT_LEAD_VOLUME.']['.$subsidyLevel['group_id'].'][id]', $subsidyLevel['id'],array('class'=>'form-control')); ?>
		
		<td><?php echo CHtml::textField('Contract[subsidyLevelArray]['.Contract::TYPE_FULFILLMENT_LEAD_VOLUME.']['.$subsidyLevel['group_id'].'][low]', $subsidyLevel['low']); ?></td>
		<td><?php echo CHtml::textField('Contract[subsidyLevelArray]['.Contract::TYPE_FULFILLMENT_LEAD_VOLUME.']['.$subsidyLevel['group_id'].'][high]', $subsidyLevel['high']); ?></td>
		<td><?php echo CHtml::textField('Contract[subsidyLevelArray]['.Contract::TYPE_FULFILLMENT_LEAD_VOLUME.']['.$subsidyLevel['group_id'].'][amount]', $subsidyLevel['amount']); ?></td>
		
		<?php /*
		<td><?php echo CHtml::dropDownList('Contract[subsidyLevelArray]['.Contract::TYPE_FULFILLMENT_LEAD_VOLUME.']['.$subsidyLevel['group_id'].'][type]',$subsidyLevel['type'],array('%'=>'%', '$'=> '$'),array('empty'=>'--')); ?></td>
		<td><?php echo CHtml::textField('Contract[subsidyLevelArray]['.Contract::TYPE_FULFILLMENT_LEAD_VOLUME.']['.$subsidyLevel['group_id'].'][subsidy]', $subsidyLevel['subsidy']); ?></td>
		*/ ?>
		<td><?php echo CHtml::button('Remove',array('class' => 'btn btn-xs btn-success btn-remove-volume')); ?></td>
	</tr>
	
</table>