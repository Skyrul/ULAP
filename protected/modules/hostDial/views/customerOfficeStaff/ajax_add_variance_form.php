<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title blue">Add Login/Logout</h4>
			</div>
			
			<div class="modal-body">
				<form>
					<div class="row">
						<label class="col-sm-3 control-label no-padding-right">Login time <span class="red">*</span></label>
						<div class="col-sm-9">
							<input type="text" id="AccountLoginTracker_time_in_date" name="AccountLoginTracker[time_in_date]" value="" />
							<input type="text" id="AccountLoginTracker_time_in" name="AccountLoginTracker[time_in_time]" value="" placeholder="e.g., 8:00 AM"/>
						</div>
					</div>
					
					<br />
					
					<div class="row">
						<label class="col-sm-3 control-label no-padding-right">Logout time <span class="red">*</span></label>
						<div class="col-sm-9">
							<input type="text" id="AccountLoginTracker_time_out_date" name="AccountLoginTracker[time_out_date]" value="" />
							<input type="text" id="AccountLoginTracker_time_out" name="AccountLoginTracker[time_out_time]" value="" placeholder="e.g., 8:00 AM"/>
						</div>
					</div>
					
					<br />
					
					<div class="row">
						<label class="col-sm-3 control-label no-padding-right">Supervisor Note </label>
						<div class="col-sm-9">
							<textarea class="middle col-sm-12" id="AccountLoginTracker_note" name="AccountLoginTracker[note]" /></textarea>
						</div>
					</div>
					
					<br />
					
				</form>
			</div>
			
			<div class="modal-footer">
				<div class="text-center">
					<button type="button" id="" class="btn btn-primary btn-xs save-btn"><i class="fa fa-check"></i> Add</button>
				</div>
			</div>
		</div>
	</div>
</div>