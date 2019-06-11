<div class="modal fade">
	<div class="modal-dialog" style="width:750px;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title blue">Billing Schedule</h4>
			</div>
			
			<div class="modal-body">
				<div class="form">
				
					<form action="" method="post">
					
						<div class="row">
							<div class="col-sm-12"> 
								Time &nbsp; <input type="text" value="<?php echo $model->schedule_time; ?>" placeholder="example: 2:00 PM"> 
							</div>
						</div>
						
						<br />
					
						<div class="row">
							<div class="col-sm-3">
								<div class="checkbox">
									<label>
										<input name="CustomerBillingScheduledSettings[monday]" class="ace" type="checkbox" value="1" <?php echo $model->monday == 1 ? 'checked' : ''; ?>>
										<span class="lbl"> Monday</span>
									</label>
								</div>
							</div>
							
							<div class="col-sm-3">
								<div class="checkbox">
									<label>
										<input name="CustomerBillingScheduledSettings[tuesday]" class="ace" type="checkbox" value="1" <?php echo $model->tuesday == 1 ? 'checked' : ''; ?>>
										<span class="lbl"> Tuesday</span>
									</label>
								</div>
							</div>
							
							<div class="col-sm-3">
								<div class="checkbox">
									<label>
										<input name="CustomerBillingScheduledSettings[wednesday]" class="ace" type="checkbox" value="1" <?php echo $model->wednesday == 1 ? 'checked' : ''; ?>>
										<span class="lbl"> Wednesday</span>
									</label>
								</div>
							</div>
							
							<div class="col-sm-3">
								<div class="checkbox">
									<label>
										<input name="CustomerBillingScheduledSettings[thursday]" class="ace" type="checkbox" value="1" <?php echo $model->thursday == 1 ? 'checked' : ''; ?>>
										<span class="lbl"> Thursday</span>
									</label>
								</div>
							</div>
							
							<div class="col-sm-3">
								<div class="checkbox">
									<label>
										<input name="CustomerBillingScheduledSettings[friday]" class="ace" type="checkbox" value="1" <?php echo $model->friday == 1 ? 'checked' : ''; ?>>
										<span class="lbl"> Friday</span>
									</label>
								</div>
							</div>
							
							<div class="col-sm-3">
								<div class="checkbox">
									<label>
										<input name="CustomerBillingScheduledSettings[saturday]" class="ace" type="checkbox" value="1" <?php echo $model->saturday == 1 ? 'checked' : ''; ?>>
										<span class="lbl"> Saturday</span>
									</label>
								</div>
							</div>
							
							<div class="col-sm-3">
								<div class="checkbox">
									<label>
										<input name="CustomerBillingScheduledSettings[sunday]" class="ace" type="checkbox" value="1" <?php echo $model->sunday == 1 ? 'checked' : ''; ?>>
										<span class="lbl"> Sunday</span>
									</label>
								</div>
							</div>
						</div>
						
					</form>	
					
				</div>
			</div>
			
			<div class="modal-footer center">
				<button data-action="save" class="btn btn-sm btn-primary">
					Save
				</button>
			</div>
		</div>
	</div>
</div>