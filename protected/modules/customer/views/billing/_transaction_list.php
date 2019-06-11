<?php 
	$existingVoidRecord = CustomerBilling::model()->find(array(
		'condition' => 'reference_transaction_id = :reference_transaction_id AND transaction_type = :transaction_type AND anet_responseCode=1',
		'params' => array(
			':reference_transaction_id' => $data->id,
			':transaction_type' => 'Void',
		),
	));
	
	$voidedRecordIds = array();
	
	$voidedRecords = CustomerBilling::model()->findAll(array(
		'condition' => 'customer_id = :customer_id AND transaction_type = :transaction_type AND anet_responseCode=1',
		'params' => array(
			':customer_id' => $data->customer_id,
			':transaction_type' => 'Void',
		),
	));
	
	if( $voidedRecords )
	{
		foreach( $voidedRecords as $voidedRecord )
		{
			$voidedRecordIds[] = $voidedRecord->reference_transaction_id;
		}
	}
	
	if( $voidedRecordIds )
	{
		$existingRefundRecord = CustomerBilling::model()->find(array(
			'condition' => '
				reference_transaction_id = :reference_transaction_id 
				AND transaction_type IN ("Refund") 
				AND id NOT IN ('.implode(', ', $voidedRecordIds).')
				 AND anet_responseCode=1
			',
			'params' => array(
				':reference_transaction_id' => $data->id,
			),
		));
	
		$existingPartialRefundRecords = CustomerBilling::model()->findAll(array(
			'condition' => '
				reference_transaction_id = :reference_transaction_id 
				AND transaction_type IN ("Partial Refund") 
				AND id NOT IN ('.implode(', ', $voidedRecordIds).')
				AND anet_responseCode=1
			',
			'params' => array(
				':reference_transaction_id' => $data->id,
			),
		));
	}
	else
	{
		$existingRefundRecord = CustomerBilling::model()->find(array(
			'condition' => 'reference_transaction_id = :reference_transaction_id AND transaction_type IN ("Refund") AND anet_responseCode=1',
			'params' => array(
				':reference_transaction_id' => $data->id,
			),
		));
		
		$existingPartialRefundRecords = CustomerBilling::model()->findAll(array(
			'condition' => 'reference_transaction_id = :reference_transaction_id AND transaction_type IN ("Partial Refund") AND anet_responseCode=1',
			'params' => array(
				':reference_transaction_id' => $data->id,
			),
		));
	}
	
	$trClass = '';
	
	if( $data->anet_responseCode == null || $data->anet_responseCode != 1)
	{
		if($data->is_imported != 1)
			$trClass = 'danger';
	}
?>

<?php if ($index == 0){ ?>

<thead>
	<th>Transaction ID</th>
	<th>Card Name</th>
	<th>Date</th>
	<th>User</th>
	<th>Type</th>
	<th>Transaction Type</th>
	<th width="20%">Amount</th>
	<th>Memo</th>
	<th>Result</th>
	
	<th class="center">Receipt</th>
	
	<?php if( !in_array(Yii::app()->user->account->account_type_id, array(Account::TYPE_CUSTOMER, Account::TYPE_CUSTOMER_OFFICE_STAFF, Account::TYPE_COMPANY)) ){ ?>
	<th class="center">Options</th>
	<?php } ?>
	
</thead>

<?php } ?>

<tr class="<?php echo $trClass; ?>">
	<td><?php echo $data->anet_transId; ?></td>
	
	<td>
		<?php 
			if( $data->payment_method == 'echeck' )
			{
				echo $data->echeck->account_name; 
			}
			elseif($data->is_imported == 1)
			{
				// echo $data->payment_method;
			}
			else
			{
				echo !empty($data->creditCard->nick_name) ? $data->creditCard->nick_name : $data->creditCard->credit_card_type; 
			}
		?>
	</td>
	
	<td>
		<?php 
			$date = new DateTime($data->date_created, new DateTimeZone('America/Chicago'));

			$date->setTimezone(new DateTimeZone('America/Denver'));

			echo $date->format('m/d/Y g:i A');
		?>
	</td>
	
	<td><?php echo isset($data->account) ? $data->account->getFullName() : ''; ?></td>
	
	<td><?php echo $data->transaction_type; ?></td>
	
	<td><?php echo $data->billing_type; ?></td>
	
	<td style="padding:0;">
		<?php 
			echo '<table class="table table-striped table-condensed">';
			
				echo '<tr>';
					
					if( $data->credit_amount != null || $data->subsidy_amount != null )
					{
						echo '<td>Original</td>';
						echo '<td>';
						
							if( $data->original_amount != null )
							{
								echo '<strike>$' . number_format( $data->original_amount, 2).'</strike>';
							}
							else
							{
								if( $data->credit_amount != null )
								{
									echo '<strike>$' . number_format( ($data->amount + $data->credit_amount), 2).'</strike>';
								}
								
								if( $data->subsidy_amount != null )
								{
									echo '<strike>$' . number_format( ($data->amount + $data->subsidy_amount), 2).'</strike>';
								}
							}
							
						echo '</td>';
					}
				
				echo '</tr>';
				
				if( $data->credit_amount != null )
				{
					echo '<tr>';
						echo '<td>Credit </td>';
						echo '<td>$' . number_format( $data->credit_amount, 2).'</td>';
					echo '</tr>';
				}
				
				if( $data->subsidy_amount != null )
				{
					echo '<tr>';
						echo '<td>Subsidy </td>';
						echo '<td>$' . number_format( $data->subsidy_amount, 2).'</td>';
					echo '</tr>';
				}
				
				echo '<tr>';
					echo '<td><b>Total</b></td>';
					echo '<td><b>$' . number_format( $data->amount, 2).'</b></td>';
				echo '</tr>';
			
			echo '</table>';
		?>
	</td>
	
	<td><?php echo $data->description; ?></td>
	
	<td>
		<?php 
			if( !in_array($data->transaction_type, array("Remove", "Write Off")) )
			{
				if( $data->anet_responseCode == 1 || $data->is_imported)
				{
					echo '<span class="label label-success">Success</span>';
				}
				else
				{
					if( empty($data->anet_transId) )
					{
						echo '<span class="label label-warning">Pending</span>';
					}
					else
					{
						echo '<span class="label label-danger">Decline</span>';
						
						if( !empty($data->anet_responseReasonDescription) )
						{
							echo ' - ' . $data->anet_responseReasonDescription;
						}
					}
				}
			}
		?>
	</td>
	
	<td class="center">
		<?php 
			if( Yii::app()->user->account->checkPermission('customer_billing_download_button','visible') && !in_array($data->transaction_type, array("Remove", "Write Off")) )
			{
				echo CHtml::link('<i class="fa fa-download"></i> Download', array('downloadReceipt', 'id'=>$data->id), array('class'=>'btn btn-yellow btn-minier')); 
			}
		?>
	</td>
	
	<?php if( !in_array(Yii::app()->user->account->account_type_id, array(Account::TYPE_CUSTOMER, Account::TYPE_CUSTOMER_OFFICE_STAFF, Account::TYPE_COMPANY)) ){ ?>
	<td class="center">
		
		<?php if( $data->anet_transId !== null && empty($existingVoidRecord) && !in_array($data->transaction_type, array("Remove", "Write Off")) ): ?>
	
			<?php 
				if( $data->transaction_type == 'Charge' )
				{
					if( empty($existingRefundRecord) )
					{
						$balance = $data->amount;
						
						if( $existingPartialRefundRecords )
						{	
							$existingAmount = 0;
								
							foreach( $existingPartialRefundRecords as $existingPartialRefundRecord )
							{
								$existingAmount += $existingPartialRefundRecord->amount;
							}
							
							$balance = ($balance - $existingAmount);
						}
						
						if( $balance > 0 && Yii::app()->user->account->checkPermission('customer_billing_action_button','visible') )
						{
							
							echo '
								<div class="btn-group">
									<button aria-expanded="false" data-toggle="dropdown" class="btn btn-primary btn-minier dropdown-toggle">
										Action
										<i class="ace-icon fa fa-angle-down icon-on-right"></i>
									</button>

									<ul class="dropdown-menu">';
							
									if( empty($existingRefundRecord) )
									{
										if( time() >= strtotime('+12 hours', strtotime($data->date_created)) )
										{
											echo '<li>'.CHtml::link('Partial Refund', 'javascript:void(0);', array('id'=>$data->id, 'class'=>'partial-refund-btn')).'</li>'; 
					
											if( empty($existingPartialRefundRecords) )
											{
												echo '<li>'.CHtml::link('Refund', 'javascript:void(0);', array('id'=>$data->id, 'class'=>'refund-transaction-btn')).'</li>'; 
											}
										}
										
										if( strtotime($data->date_created) >= strtotime('-12 hours') && empty($existingPartialRefundRecords) )
										{
											echo '<li>'.CHtml::link('Void', 'javascript:void(0);', array('id'=>$data->id, 'class'=>'void-transaction-btn')).'</li>'; 
										}
									}
							
							echo '</ul></div>';
							
						}
					}
				}
			?>
			
			<?php if( $data->transaction_type == 'Refund' || $data->transaction_type == 'Partial Refund' ): ?>

				<button id="<?php echo $data->id; ?>" class="btn btn-primary btn-minier void-transaction-btn">Void</button>
	
			<?php endif; ?>
			
		<?php endif; ?>
		
	</td>

	<?php } ?>

</tr>