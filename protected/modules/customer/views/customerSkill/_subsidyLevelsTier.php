<?php 
	$customerSkillSubsidyLevelArray = $selectedCustomerSkill->getCustomerSkillSubsidyLevelArray();
	
	$customerSkillSubsidyLevel = isset($customerSkillSubsidyLevelArray[$tierSubsidyLevel->id.'-'.CustomerSkillSubsidyLevel::TYPE_TIER_SUBSIDY_LEVEL]) ? $customerSkillSubsidyLevelArray[$tierSubsidyLevel->id.'-'.CustomerSkillSubsidyLevel::TYPE_TIER_SUBSIDY_LEVEL] : null;
	$isToggleQuantityDisabled = ($customerSkillSubsidyLevel !== null && $customerSkillSubsidyLevel->status == CustomerSkillSubsidyLevel::STATUS_ACTIVE) ? false : true;
?>

<table class="tier-subsidy-level-entries table" id="customer-skill-tier-subsidy-level-<?php echo $tierSubsidyLevel->id; ?>" style="display:none;" >
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
					value="<?php echo $tierSubsidyLevel->id; ?>"  
					data-customer_skill_id="<?php echo $selectedCustomerSkill->id; ?>" 
					data-customer_id="<?php echo $selectedCustomerSkill->customer_id; ?>" 
					data-type="<?php echo CustomerSkillSubsidyLevel::TYPE_TIER_SUBSIDY_LEVEL; ?>" 
					<?php echo ($isToggleQuantityDisabled) ? "" : "checked"; ?>                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         
				>
				
				<span class="lbl middle"></span>
			</small>
		</td> */
		?>
		<td><?php echo CHtml::textField('TierSubsidyLevel['.$name.'][name]', $tierSubsidyLevel->name, array('class' => 'form-control', 'disabled'=> true)); ?></td>
		<td><?php echo CHtml::dropDownList('TierSubsidyLevel['.$name.'][type]', $tierSubsidyLevel->type, array('$'=>'$', '%'=>'%'), array('class' => 'form-control', 'disabled'=> true)); ?></td>
		<td><?php echo CHtml::textField('TierSubsidyLevel['.$name.'][value]', $tierSubsidyLevel->value, array('class' => 'form-control', 'disabled'=> true)); ?></td>
	</tr>
	
</table>