<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title blue"><i class="fa fa-credit-card"></i> Refund</h4>
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
							<td><?php echo '$'.number_format($transaction->amount, 2); ?></td>
						</tr>
					</tbody>
				</table>
				
				<div class="hr hr-18 dotted hr-double"></div>
				
				<form method="post" action="" class="form-horizontal">
					<div class="form-group">
						<label class="col-sm-1 control-label">Memo</label>
						
						<div class="col-sm-11">
							<?php echo CHtml::textArea('CustomerBilling[description]','Transaction ID: '.$transaction->anet_transId, array('id'=>'refundMemo', 'class'=>'form-control col-xs-12')); ?>
						</div>
					</div>
				</form>
			</div>
			
			<div class="modal-footer center">
				<button type="button" class="btn btn-sm btn-info" data-action="save">Process</button>
			</div>
		</div>
	</div>
</div>