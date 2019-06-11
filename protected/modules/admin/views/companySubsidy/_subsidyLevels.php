<table class="subsidy-level-entries table">
	<tr>
		<th>Name</th>
		<th>Type</th>
		<th>Value</th>
		<th>Link</th>
		<th>&nbsp;</th>
	</tr>
	
	<tr>
		<td><?php echo CHtml::textField('CompanySubsidyLevel['.$name.'][name]', $companySubsidyLevel->name, array('class' => 'form-control')); ?></td>
		<td><?php echo CHtml::dropDownList('CompanySubsidyLevel['.$name.'][type]', $companySubsidyLevel->type, array('$'=>'$', '%'=>'%'), array('class' => 'form-control')); ?></td>
		<td><?php echo CHtml::textField('CompanySubsidyLevel['.$name.'][value]', $companySubsidyLevel->value, array('class' => 'form-control')); ?></td>
		<td class="col-md-2"><?php echo CHtml::textField('CompanySubsidyLevel['.$name.'][tier_link]', $companySubsidyLevel->tier_link, array('class' => 'form-control')); ?></td>
		<td><?php echo CHtml::button('Remove',array('class' => 'btn btn-minier btn-danger btn-remove-subsidy-level')); ?></td>
	</tr>
	
</table>