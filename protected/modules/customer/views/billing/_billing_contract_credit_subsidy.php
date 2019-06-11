<table id="contract-subsidy-credit-table" class="table table-bordered table-condensed table-hover">
	<thead>
		<tr>
			<th>Contract</th>
			<th class="center">Credit</th>
			<th class="center">Subsidy</th>
			<th class="center">Reduced Amount</th>
		</tr>
	</thead>


	<tbody>
		<?php foreach($contractCreditSubsidys as $id => $contractCreditSubsidy){ ?>
		<tr class="contract-subsidy-credit" id="contract-subsidy-credit-id-<?php echo $id; ?>">
			<td class=""><?php echo $contractCreditSubsidy['contract_name']; ?></td>
			<td class="center"><?php echo number_format($contractCreditSubsidy['totalCreditAmount'],2); ?></td>
			<td class="center"><?php echo number_format($contractCreditSubsidy['totalSubsidyAmount'],2); ?></td>
			
			<td class="center reduced-amount">
			
				<?php 
					// echo 'totalCreditAmount: ' . $contractCreditSubsidy['totalCreditAmount'];
					// echo '<br>';
					// echo 'totalSubsidyAmount: ' . $contractCreditSubsidy['totalSubsidyAmount'];
					// echo '<br>';
					// echo 'amount: ' . $contractCreditSubsidy['totalCreditAmount'];
					// echo '<br>';
					// echo '<br>';
					
					if( $amount )
					{
						if( $billing_type == 'Termination Fee' )
						{
							$reducedAmount = $amount;
						}
						else
						{
							$reducedAmount = $amount - $contractCreditSubsidy['totalCreditAmount'];
							$reducedAmount = $reducedAmount - $contractCreditSubsidy['totalSubsidyAmount'];
						}						
						
						if( $reducedAmount < 0 )
						{
							$reducedAmount = 0;
						}
						
						echo number_format( $reducedAmount ,2); 
					}
					
					echo CHtml::hiddenField('CustomerBilling[original_amount]', $amount);
					echo CHtml::hiddenField('CustomerBilling[credit_amount]', $contractCreditSubsidy['totalCreditAmount']); 
					echo CHtml::hiddenField('CustomerBilling[subsidy_amount]', $contractCreditSubsidy['totalSubsidyAmount']);
					echo CHtml::hiddenField('CustomerBilling[total_reduced_amount]', $reducedAmount); 
				?>
			</td>
			
			<?php /*if(!$existingBillingForCurrentMonth){ ?>
			<td class="center"><?php echo number_format($contractCreditSubsidy['totalReducedAmount'],2); ?></td>
			<?php }else{ ?>
			<td class="center">Paid</td>
			<?php }*/ ?>
		</tr>
		<?php } ?>
	</tbody>
</table>