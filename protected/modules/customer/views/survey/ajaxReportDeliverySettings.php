<div class="modal fade">
	<div class="modal-dialog" style="width:50%;">
		<div class="modal-content">
			<div class="modal-header" style="background:#438EB9;">
				<button type="button" class="close white" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title white">
					<i class="fa fa-cogs"></i> Delivery Settings
				</h4>
			</div>
			
			<div class="modal-body no-padding" style="height:500px; overflow:auto;">
				
				<div class="space-12"></div>
				
				<div class="col-sm-12">
				
					<div class="tabbable">
							
						<ul class="nav nav-tabs padding-12 tab-color-blue background-blue" id="myTab4">
							<li class="active">
								<a data-toggle="tab" href="#saveForm">Settings</a>
							</li>

							<li>
								<a data-toggle="tab" href="#recordsList">User Records</a>
							</li>
						</ul>
					
						<div class="tab-content">
							<div id="saveForm" class="tab-pane in active">
								<form class="no-margin">
								
									<input type="hidden" name="ReportDeliverySettings[skill_id]" value="<?php echo $model->skill_id; ?>">
									<input type="hidden" name="ReportDeliverySettings[customer_id]" value="<?php echo $model->customer_id; ?>">
									<input type="hidden" name="ReportDeliverySettings[report_name]" value="<?php echo $model->report_name; ?>">
									
									<div class="space-12"></div>
									
									<div class="row">
										<label class="col-sm-3 control-label no-padding-right">Report Name :</label>
										<div class="col-sm-9">
											<b><?php echo $model->report_name == 'surveyXlsx' ? 'Survey Results' : 'Survey Analytic'; ?></b>
										</div>
									</div>
									
									<div class="space-12"></div>
									
									<div class="row">
										<label class="col-sm-3 control-label no-padding-right">File Type :</label>
										<div class="col-sm-9">
											<b><?php echo $model->report_name == 'surveyXlsx' ? 'csv' : 'pdf'; ?></b>
										</div>
									</div>
									
									<div class="space-12"></div>
									
									<div class="row">
										<label class="col-sm-3 control-label no-padding-right">Skill Name :</label>
										<div class="col-sm-9">
											<b><?php echo Skill::model()->findByPk($model->skill_id)->skill_name; ?></b>
										</div>
									</div>
												
									<div class="space-12"></div>
									
									<div class="row">
										<label class="col-sm-3 control-label no-padding-right">Send Schedule <span class="red">*</span></label>
										<div class="col-sm-9">
											<select id="ReportDeliverySettings_auto_email_frequency" class="middle" name="ReportDeliverySettings[auto_email_frequency]">
												<option value="" <?php echo empty($model->auto_email_frequency) ? "selected" : ""; ?>>- None -</option>
												<option value="End of each week day" <?php echo $model->auto_email_frequency == "End of each week day" ? "selected" : ""; ?>>End of each week day</option>
												<option value="End of work week" <?php echo $model->auto_email_frequency == "End of work week" ? "selected" : ""; ?>>End of work week</option>
												<option value="End of the month" <?php echo $model->auto_email_frequency == "End of the month" ? "selected" : ""; ?>>End of the month</option>
											</select>
										</div>
									</div>
									
									<div class="space-12"></div>
									
									<div class="row">
										<label class="col-sm-3 control-label no-padding-right">Send Type <span class="red">*</span></label>
										<div class="col-sm-9">
											<select id="ReportDeliverySettings_type" class="middle" name="ReportDeliverySettings[type]">
												<option value="1" <?php echo $model->type == 1 ? "selected" : ""; ?>>Send to customer files</option>
												<option value="2" <?php echo $model->type == 2 ? "selected" : ""; ?>>Send to email</option>
											</select>
										</div>
									</div>
									
									<div class="space-12"></div>
								
									<div class="row auto_email_recipients_container" style="display:<?php echo $model->type == 2 ? "block": "none"; ?>;">
										<label class="col-sm-3 control-label no-padding-right">Email Address <span class="red">*</span></label>
										<div class="col-sm-9">
											<textarea id="ReportDeliverySettings_auto_email_recipients" name="ReportDeliverySettings[auto_email_recipients]" cols="60" rows="2"><?php echo $model->auto_email_recipients; ?></textarea>
										</div>
									</div>
									
									<div class="space-12"></div>
								</form>
							</div>
							
							<div id="recordsList" class="tab-pane">
								<table class="table table-hover table-bordered table-striped table-bordered">
									<thead>
										<th>Skill Name</th>
										<th>User Name</th>
										<th>Send Schedule</th>
										<th>Send Type</th>
										<th>Email Address</th>
										<th class="center">Options</th>
									</thead>

									<tbody>
										<?php
											if( $deliveries )
											{
												foreach( $deliveries as $delivery )
												{
													echo '<tr>';
														echo '<td>'.$delivery->skill->skill_name.'</td>';
															
														echo '<td>'.$delivery->account->getFullName().'</td>';
														
														echo '<td>'.$delivery->auto_email_frequency.'</td>';
														
														echo '<td>';
															echo $delivery->type == 1 ? 'Send to customer files' : 'Send to email';
														echo '</td>';	
														
														echo '<td>'.$delivery->auto_email_recipients.'</td>';
														
														echo '<td class="center">';
															echo '<button id="'.$delivery->id.'" class="btn btn-mini btn-danger delivery-settings-btn-remove"><i class="fa fa-times"></i> Remove</button>';
														echo '</td>';
														
													echo '</tr>';
												}
											}
										?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>			
			</div>
			
			<div class="modal-footer">
				<button type="button" class="btn btn-xs btn-info" data-action="save"><i class="fa fa-check"></i> Save</button>
				<button type="button" class="btn btn-xs" data-dismiss="modal"><i class="fa fa-times"></i> Cancel</button>
			</div>
		</div>
	</div>
</div>

