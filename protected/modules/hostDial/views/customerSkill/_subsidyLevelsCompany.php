<?php 
	$customerSkillSubsidyLevelArray = $selectedCustomerSkill->getCustomerSkillSubsidyLevelArray();
	
	$customerSkillSubsidyLevel = isset($customerSkillSubsidyLevelArray[$companySubsidyLevel->id.'-'.CustomerSkillSubsidyLevel::TYPE_COMPANY_SUBSIDY_LEVEL]) ? $customerSkillSubsidyLevelArray[$companySubsidyLevel->id.'-'.CustomerSkillSubsidyLevel::TYPE_COMPANY_SUBSIDY_LEVEL] : null;
	$isToggleQuantityDisabled = ($customerSkillSubsidyLevel !== null && $customerSkillSubsidyLevel->status == CustomerSkillSubsidyLevel::STATUS_ACTIVE) ? false : true;
?>

<table class="subsidy-level-entries table" id="customer-skill-subsidy-level-<?php echo $companySubsidyLevel->id; ?>" style="<?php echo (!empty($cksl) && $cksl->subsidy_level_id == $companySubsidyLevel->id) ? "" : "display:none;"; ?>" >
	<tr>
		<?php /* <th>&nbsp;</th> */ ?>
		<th>Name</th>
		<th>Type</th>
		<th>Value</th>
	</tr>
	
	<tr>
		<?php /*
		<td>
			<small>
				<input 
					type="checkbox" 
					class="toggle-skill-subsidy-level ace ace-switch ace-switch-1" 
					value="<?php echo $companySubsidyLevel->id; ?>"  
					data-customer_skill_id="<?php echo $selectedCustomerSkill->id; ?>" 
					data-customer_id="<?php echo $selectedCustomerSkill->customer_id; ?>" 
					data-type="<?php echo CustomerSkillSubsidyLevel::TYPE_COMPANY_SUBSIDY_LEVEL; ?>" 
					<?php echo ($isToggleQuantityDisabled) ? "" : "checked"; ?>                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         
				>
				
				<span class="lbl middle"></span>
			</small>
		</td>
		*/ ?>
		<td><?php echo CHtml::textField('CompanySubsidyLevel['.$name.'][name]', $companySubsidyLevel->name, array('class' => 'form-control', 'disabled'=>true)); ?></td>
		<td><?php echo CHtml::dropDownList('CompanySubsidyLevel['.$name.'][type]', $companySubsidyLevel->type, array('$'=>'$', '%'=>'%'), array('class' => 'form-control', 'disabled'=> true)); ?></td>
		<td><?php echo CHtml::textField('CompanySubsidyLevel['.$name.'][value]', $companySubsidyLevel->value, array('class' => 'form-control', 'disabled'=> true)); ?></td>
	</tr>
	
</table>