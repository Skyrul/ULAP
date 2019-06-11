<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title blue">Add PTO Request</h4>
			</div>
			
			<div class="modal-body">
				<form>	
					<div class="row">
						<label class="col-sm-3 control-label no-padding-right">Name <span class="red">*</span></label>
						<div class="col-sm-9">
							<input type="text" class="middle col-sm-12" id="AccountPtoRequest_name" name="AccountPtoRequest[name]" value="" />
						</div>
					</div>
					
					<br />
					
					<div class="row">
						<label class="col-sm-4 control-label no-padding-right">Request Start Date <span class="red">*</span></label>
						<div class="col-sm-5">
							<input type="text" class="middle" id="AccountPtoRequest_request_date" name="AccountPtoRequest[request_date]" value="" />
						</div>
					</div>
					
					<br />
					
					<div class="row">
						<label class="col-sm-4 control-label no-padding-right">Start Time <span class="red">*</span></label>
						<div class="col-sm-5">
							<select id="AccountPtoRequest_start_time" name="AccountPtoRequest[start_time]" >
								<option></option>
								<?php 
									foreach (Calendar::createTimeRange('07:00 AM', '10:00PM') as $time) 
									{
										$time = date('g:i A', $time);
										
										echo '<option value="'.$time.'">'.$time.'</option>';
									}
								?>
							</select>
						</div>
					</div>
					
					<br />
					
					<div class="row">
						<label class="col-sm-4 control-label no-padding-right">Request End Date <span class="red">*</span></label>
						<div class="col-sm-5">
							<input type="text" class="middle" id="AccountPtoRequest_request_date_end" name="AccountPtoRequest[request_date_end]" value="" />
						</div>
					</div>
					
					<br />
					
					<div class="row">
						<label class="col-sm-4 control-label no-padding-right">End Time <span class="red">*</span></label>
						<div class="col-sm-5">
							<select id="AccountPtoRequest_end_time" name="AccountPtoRequest[end_time]" >
								<option></option>
								<?php 
									foreach (Calendar::createTimeRange('07:00 AM', '10:00PM') as $time) 
									{
										$time = date('g:i A', $time);
										
										echo '<option value="'.$time.'">'.$time.'</option>';
									}
								?>
							</select>
						</div>
					</div>
					
				</form>
			</div>
			
			<div class="modal-footer">
				<div class="text-center">
					<button type="button" class="btn btn-info btn-xs pto-submit-btn">Submit</button>
				</div>
			</div>
		</div>
	</div>
</div>