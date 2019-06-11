<div class="modal fade">
	<div class="modal-dialog" style="width:40%;">
		<div class="modal-content">
			<div class="modal-header" style="background:#438EB9;">
				<button type="button" class="close white" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title white">
					<i class="fa fa-cog"></i> Settings
				</h4>
			</div>
			
			<div class="modal-body no-padding" style="height:250px; overflow:auto;">
				
				<div class="col-sm-12">
					<form class="no-margin">
					
						<div class="space-12"></div>
					
						<div class="row">
							<label class="col-sm-3 control-label no-padding-right">Recipients</label>
							<div class="col-sm-9">
								<input type="text" class="middle" name="ImpactReportSettings[auto_email_recipients]" value="<?php echo $model->auto_email_recipients; ?>" style="width:100%;"/>
							</div>
						</div>
						
						<div class="space-12"></div>
						
						<div class="row">
							<label class="col-sm-3 control-label no-padding-right">Frequency</label>
							<div class="col-sm-9">
								<select id="Impact_auto_email_frequency" class="middle" name="ImpactReportSettings[auto_email_frequency]">
									<option value="">- Select -</option>
									<option value="DAILY" <?php echo $model->auto_email_frequency == 'DAILY' ? 'selected' : ''; ?>>DAILY</option>
									<option value="ONCE A WEEK" <?php echo $model->auto_email_frequency == 'ONCE A WEEK' ? 'selected' : ''; ?>>ONCE A WEEK</option>
									<option value="ONCE A MONTH" <?php echo $model->auto_email_frequency == 'ONCE A MONTH' ? 'selected' : ''; ?>>ONCE A MONTH</option>
								</select>
							</div>
						</div>
						
						<div class="space-12"></div>
						
						<div class="row select-day-container" style="<?php echo $model->auto_email_frequency == 'DAILY' ? 'display:none;' : ''; ?>">
							<label class="col-sm-3 control-label no-padding-right">Day</label>
							<div class="col-sm-9">
								
								<?php
									if( !empty($model->auto_email_frequency) )
									{
										if( $model->auto_email_frequency == 'ONCE A MONTH' )
										{
											$selectOptions = array(
												'First day of month' => 'First day of month',
												'Last day of month' => 'Last day of month',
												'End of Day 15th' => 'End of Day 15th',
											);
										}
										else
										{
											$selectOptions = array(
												'Monday' => 'Monday',
												'Tuesday' => 'Tuesday',
												'Wednesday' => 'Wednesday',
												'Thursday' => 'Thursday',
												'Friday' => 'Friday',
												'Saturday' => 'Saturday',
												'Sunday' => 'Sunday',
											);
										}
										
										echo '<select id="ImpactReportSettings_auto_email_day" class="middle" name="ImpactReportSettings[auto_email_day]">';
											echo '<option value="">- Select -</option>';
											
											foreach( $selectOptions as $selectOption )
											{
												$selected = $model->auto_email_day == $selectOption ? 'selected' : '';
												
												echo '<option value="'.$selectOption.'" '.$selected.'>'.$selectOption.'</option>';
											}
												
										echo '</select>';
									}
									else
									{
										echo '<select id="ImpactReportSettings_auto_email_day" class="middle" name="ImpactReportSettings[auto_email_day]"></select>';
									}
								?>								
							</div>
						</div>
						
						<div class="space-12"></div>
						
						<div class="row">
							<label class="col-sm-3 control-label no-padding-right">Time</label>
							<div class="col-sm-9">
								<select class="middle" name="ImpactReportSettings[auto_email_time]">
	
									<option value="">- Select -</option>
								
									<?php 								
										for ($i = 25200; $i < 86400; $i += 1800) // 25200 = 7am, 70200 = 7:30pm, 1800 = half hour, 86400 = one day
										{  
											$selected = (date('g:i A', mktime(0, 0, 0, 1, 1) + $i) == $model->auto_email_time) ? 'selected' : '';
											
											echo '<option value="'.date('g:i A', mktime(0, 0, 0, 1, 1) + $i).'" '.$selected.'>'.date('g:i A', mktime(0, 0, 0, 1, 1) + $i).'</option>';
										}
									?>
								</select>
							</div>
						</div>
					
					</form>

				</div>
				
			</div>
			
			<div class="modal-footer">
				<button type="button" class="btn btn-xs btn-info" data-action="save"><i class="fa fa-check"></i> Save</button>
				<button type="button" class="btn btn-xs" data-dismiss="modal"><i class="fa fa-times"></i> Cancel</button>
			</div>
		</div>
	</div>
</div>

