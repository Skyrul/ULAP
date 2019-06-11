<?php 
	$timeIn = new DateTime($model->time_in, new DateTimeZone('America/Chicago'));
	$timeIn->setTimezone(new DateTimeZone('America/Denver'));	

	// if( $model->type == 1)
	// {
		$timeOut = new DateTime($model->time_out, new DateTimeZone('America/Chicago'));
		$timeOut->setTimezone(new DateTimeZone('America/Denver'));	
	// }
	// else
	// {
		// $timeOut = new DateTime($model->time_out);
	// }	
?>

<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title blue">Edit Variance</h4>
			</div>
			
			<div class="modal-body">
				<form>
					<div class="row">
						<label class="col-sm-3 control-label no-padding-right">Login time <span class="red">*</span></label>
						<div class="col-sm-9">
							<input type="text" id="AccountLoginTracker_time_in_date" name="AccountLoginTracker[time_in_date]" value="<?php echo $model->time_in != null ? $timeIn->format('m/d/Y') : ''; ?>" />
							<input type="text" id="AccountLoginTracker_time_in" name="AccountLoginTracker[time_in_time]" value="<?php echo $model->time_in != null ? $timeIn->format('g:i A') : ''; ?>" placeholder="e.g., 8:00 AM"/>
						</div>
					</div>
					
					<br />
					
					<div class="row">
						<label class="col-sm-3 control-label no-padding-right">Logout time <span class="red">*</span></label>
						<div class="col-sm-9">
							<input type="text" id="AccountLoginTracker_time_out_date" name="AccountLoginTracker[time_out_date]" value="<?php echo $model->time_out != null ? $timeOut->format('m/d/Y') : ''; ?>" />
							<input type="text" id="AccountLoginTracker_time_out" name="AccountLoginTracker[time_out_time]" value="<?php echo $model->time_out != null ? $timeOut->format('g:i A') : ''; ?>" placeholder="e.g., 8:00 AM"/>
						</div>
					</div>
					
					<br />
					
					<div class="row">
						<label class="col-sm-3 control-label no-padding-right">Supervisor Note </label>
						<div class="col-sm-9">
							<textarea class="middle col-sm-12" id="AccountLoginTracker_note" name="AccountLoginTracker[note]"><?php echo $model->note; ?></textarea>
						</div>
					</div>
					
					<br />
					
				</form>
			</div>
			
			<div class="modal-footer">
				<div class="text-center">
					<button type="button" id="<?php echo $model->id; ?>" class="btn btn-primary btn-xs save-btn"><i class="fa fa-check"></i> Edit</button>
				</div>
			</div>
		</div>
	</div>
</div>