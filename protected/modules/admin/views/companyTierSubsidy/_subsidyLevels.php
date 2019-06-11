<table class="tier-subsidy-level-entries table">
	<tr>
		<th>Name</th>
		<th>Type</th>
		<th>Value</th>
		<th>&nbsp;</th>
	</tr>
	
	<tr>
		<td><?php echo CHtml::textField('TierSubsidyLevel['.$name.'][name]', $tierSubsidyLevel->name, array('class' => 'form-control')); ?></td>
		<td><?php echo CHtml::dropDownList('TierSubsidyLevel['.$name.'][type]', $tierSubsidyLevel->type, array('$'=>'$', '%'=>'%'), array('class' => 'form-control')); ?></td>
		<td><?php echo CHtml::textField('TierSubsidyLevel['.$name.'][value]', $tierSubsidyLevel->value, array('class' => 'form-control')); ?></td>
		<td><?php echo CHtml::button('Remove',array('class' => 'btn btn-minier btn-danger btn-remove-tier-subsidy-level')); ?></td>
	</tr>
	
</table>