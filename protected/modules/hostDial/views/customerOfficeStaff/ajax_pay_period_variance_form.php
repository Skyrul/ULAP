<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title blue">Approve/Deny Variance</h4>
			</div>
			
			<div class="modal-body">
				<form>	
					<div class="row">
						<label class="col-sm-2 control-label no-padding-right">Note</label>
						<div class="col-sm-10">
							<textarea class="middle col-sm-12" id="AccountLoginTracker_note" name="AccountLoginTracker[note]"><?php echo $model->note; ?></textarea>
						</div>
					</div>
					
					<br />
					
				</form>
			</div>
			
			<div class="modal-footer">
				<div class="text-center">
					<button type="button" id="<?php echo $model->id; ?>" class="btn btn-success btn-xs approve-variance-btn"><i class="fa fa-check"></i> Approve</button>
					<button type="button" id="<?php echo $model->id; ?>" class="btn btn-danger btn-xs deny-variance-btn"><i class="fa fa-times"></i> Deny</button>
				</div>
			</div>
		</div>
	</div>
</div>