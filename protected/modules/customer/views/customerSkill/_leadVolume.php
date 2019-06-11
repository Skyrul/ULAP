<?php 
	$customerSkillLevelArray = $selectedCustomerSkill->getCustomerSkillLevelArray();
	$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;
	$isToggleQuantityDisabled = ($customerSkillLevelArrayGroup !== null && $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE) ? false : true;
?>

<table class="lead-volume-entries table">
	<tr>
		<th>&nbsp;</th>
		<th>Quantity</th>
		<th>Low</th>
		<th>High</th>
		<th>Amount</th>
		
		<?php /*
		<th>&nbsp;</th>
		<th>Subsidy</th>
		*/ ?>
	</tr>
	
	<tr>
		<td>
			<?php if(Yii::app()->user->account->checkPermission('customer_skills_contract_quantity_on_off_button','visible')){ ?>
				<small>
					<input 
						type="checkbox" 
						class="toggle-skill-contract-level ace ace-switch ace-switch-1" 
						value="<?php echo $subsidyLevel['group_id']; ?>"  
						data-customer_skill_id="<?php echo $selectedCustomerSkill->id; ?>" 
						data-customer_id="<?php echo $selectedCustomerSkill->customer_id; ?>" 
						data-customer_skill_contract_id="<?php echo $selectedCustomerSkill->contract_id; ?>" 
						<?php echo ($isToggleQuantityDisabled) ? "" : "checked"; ?> 
						<?php echo $isCustomerDisabled; ?>
					>
					
					<span class="lbl middle"></span>
				</small>
			<?php } ?>
		</td>		
		
		<?php echo CHtml::hiddenField('Contract[subsidyLevelArray]['.Contract::TYPE_FULFILLMENT_LEAD_VOLUME.']['.$subsidyLevel['group_id'].'][id]', $subsidyLevel['id'],array('class'=>'form-control')); ?>
		<?php echo CHtml::hiddenField('Contract[subsidyLevelArray]['.Contract::TYPE_FULFILLMENT_LEAD_VOLUME.']['.$subsidyLevel['group_id'].'][group_id]', $subsidyLevel['group_id'],array('class'=>'form-control')); ?>
		
		<td>
			<?php if(!empty($isCustomerDisabled)){ ?>
				<?php echo CHtml::textField('Contract[subsidyLevelArray]['.Contract::TYPE_FULFILLMENT_LEAD_VOLUME.']['.$subsidyLevel['group_id'].'][quantity]', @$customerSkillLevelArrayGroup->quantity, array('disabled'=> $isCustomerDisabled, 'class'=> 'number-field') ); ?>
			<?php }else{ ?>	
				<?php echo CHtml::textField('Contract[subsidyLevelArray]['.Contract::TYPE_FULFILLMENT_LEAD_VOLUME.']['.$subsidyLevel['group_id'].'][quantity]', @$customerSkillLevelArrayGroup->quantity, array('disabled'=> $isToggleQuantityDisabled, 'class'=> 'number-field skill-level-contract-level-quantity') ); ?>
			<?php } ?>
		</td>
		<td><?php echo CHtml::textField('Contract[subsidyLevelArray]['.Contract::TYPE_FULFILLMENT_LEAD_VOLUME.']['.$subsidyLevel['group_id'].'][low]', $subsidyLevel['low'], array('data-original-value'=> $subsidyLevel['low'],'readOnly'=>true )); ?></td>
		<td><?php echo CHtml::textField('Contract[subsidyLevelArray]['.Contract::TYPE_FULFILLMENT_LEAD_VOLUME.']['.$subsidyLevel['group_id'].'][high]', $subsidyLevel['high'], array('data-original-value'=> $subsidyLevel['high'],'readOnly'=>true)); ?></td>
		<td><?php echo CHtml::textField('Contract[subsidyLevelArray]['.Contract::TYPE_FULFILLMENT_LEAD_VOLUME.']['.$subsidyLevel['group_id'].'][amount]', $customerSkillLevelArrayGroup->quantity * $subsidyLevel['amount'], array('data-original-value'=> $subsidyLevel['amount'], 'readOnly'=>true, 'class'=>'js-qty-to-multiply')); ?></td>
								
	</tr>
	
</table>