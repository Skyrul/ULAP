<?php 
	// var_dump($this->pdfView === true);
	// var_dump($subsidyLevel['group_id']);
	// var_dump($selectedCustomerEnrollment->customerEnrollmentLevelArray[$subsidyLevel['group_id']]['qty']);
?>
<?php if( ($this->pdfView === true  && !empty($selectedCustomerEnrollment->customerEnrollmentLevelArray[$subsidyLevel['group_id']]['qty']) ) || $this->pdfView !== true){ ?>
<table class="goal-volume-entries table">
	<tr>
		<th style="width:200px;">Qty</th>
		<th style="width:200px;">Goal</th>
		<th style="width:200px;">Amount</th>
	</tr>
	
	<tr>
		<?php echo CHtml::hiddenField('CustomerEnrollmentLevel['.$subsidyLevel['group_id'].'][id]', $subsidyLevel['id'],array('class'=>'form-control')); ?>	
		<?php echo CHtml::hiddenField('CustomerEnrollmentLevel['.$subsidyLevel['group_id'].'][group_id]', $subsidyLevel['group_id'],array('class'=>'form-control')); ?>	
		
		<td>
		<?php if($this->pdfView === true ){ ?>
			
				<?php echo CHtml::textField('', $selectedCustomerEnrollment->customerEnrollmentLevelArray[$subsidyLevel['group_id']]['qty'], array('style'=>'width:40px;', 'class' => 'form-control')); ?>
				<?php $this->totalContractValue = $this->totalContractValue + ($selectedCustomerEnrollment->customerEnrollmentLevelArray[$subsidyLevel['group_id']]['qty'] * $subsidyLevel['amount']); ?>

		<?php }else{?>
			<?php echo CHtml::textField('CustomerEnrollmentLevel['.$subsidyLevel['group_id'].'][qty]', 1, array('empty'=>'-', 'style'=>'width:60%;','class'=>'number-field skill-level-contract-level-quantity', 'readOnly'=>true)); ?>
		<?php } ?>
		</td>	
		
		<td><?php echo $subsidyLevel['goal']; ?></td>
		<td class="js-qty-to-multiply" data-original-value="<?php echo $subsidyLevel['amount']; ?>"> $<?php echo number_format((double)$subsidyLevel['amount'],2); ?></td>
	</tr>
	
</table>
<?php } ?>