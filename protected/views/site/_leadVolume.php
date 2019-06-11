<?php if( ($this->pdfView === true  && !empty($selectedCustomerEnrollment->customerEnrollmentLevelArray[$subsidyLevel['group_id']]['qty']) ) || $this->pdfView !== true){ ?>
<table class="lead-volume-entries table">
	<tr>
		<th style="width:200px;">Qty</th>
		<th style="width:200px;">Low</th>
		<th style="width:200px;">High</th>
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
				<?php echo CHtml::dropDownList('CustomerEnrollmentLevel['.$subsidyLevel['group_id'].'][qty]', $selectedCustomerEnrollment->customerEnrollmentLevelArray[$subsidyLevel['group_id']]['qty'],Contract::quantityList(), array('empty'=>'-', 'style'=>'width:60%;','class'=>'number-field skill-level-contract-level-quantity')); ?>
			<?php } ?>
		</td>	
		<td><?php echo $subsidyLevel['low']; ?></td>
		<td><?php echo $subsidyLevel['high']; ?></td>
		<td class="js-qty-to-multiply" data-original-value="<?php echo $subsidyLevel['amount']; ?>"> $<?php echo number_format((double)$subsidyLevel['amount'], 2); ?></td>
		
	</tr>
	
</table>
<?php } ?>