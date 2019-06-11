<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title blue"><i class="fa fa-credit-card"></i> Partial Refund</h4>
			</div>
			
			<div class="modal-body">
				<table class="table table-hover table-bordered">
					<tbody>
						<tr>
							<td><b>Transaction ID:</b></td>
							<td><?php echo $transaction->anet_transId; ?></td>
						</tr>

						<tr>
							<td><b>Amount Billed:</b> </td>
							<td><?php echo '$'.number_format($totalAmount, 2); ?></td>
						</tr>
					</tbody>
				</table>
				
				<div class="hr hr-18 dotted hr-double"></div>
				
				<form method="post" action="" class="form-horizontal">
			
					<div class="form-group">
						<label class="col-sm-2">Amount <span class="red">*</span></label>
					
						<div class="col-sm-10">
							<input type="text" value="" id="partialRefund_amount" name="partialRefund_amount">
						</div>
					</div>
					
					<div class="form-group">
						<label class="col-sm-2">Memo <span class="red">*</span></label>
						
						<div class="col-sm-10">
							<?php echo CHtml::textArea('partialRefund_memo', 'Transaction ID: '.$transaction->anet_transId, array('id'=>'partialRefund_memo', 'class'=>'col-xs-12')); ?>
						</div>
					</div>
				</form>
			</div>
			
			<div class="modal-footer center">
				<button type="button" class="btn btn-sm btn-info" data-action="save" transaction_amount="<?php echo $totalAmount; ?>">Process</button>
			</div>
		</div>
	</div>
</div>