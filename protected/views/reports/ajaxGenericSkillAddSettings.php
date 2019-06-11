<div class="modal fade">
	<div class="modal-dialog" style="width:40%;">
		<div class="modal-content">
			<div class="modal-header" style="background:#438EB9;">
				<button type="button" class="close white" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title white">
					<i class="fa fa-plus"></i> Add
				</h4>
			</div>
			
			<div class="modal-body no-padding" style="height:350px; overflow:auto;">
				
				<div class="col-sm-12">
					<form class="no-margin">
					
						<input type="hidden" name="ReportDeliverySettings[report_name]" value="<?php echo $_POST['report_name']; ?>">
					
						<div class="space-12"></div>
						
						<div class="row">
							<label class="col-sm-3 control-label no-padding-right">Skill Name <span class="red">*</span></label>
							<div class="col-sm-9">
								<select id="ReportDeliverySettings_skill_id" class="middle" name="ReportDeliverySettings[skill_id]">
									<?php 
										if( $_POST['report_name'] == 'surveyReport' )
										{
											$surveySkills = SurveySkill::model()->findAll(array(
												'with' => array('survey', 'skill'),
												'condition' => '
													t.is_active=1
													AND survey.is_deleted = 0
												',
												'order' => 'skill.skill_name ASC',
												'group' => 't.skill_id'
											));
											
											if( $surveySkills )
											{
												echo '<option value="">- Select -</option>';
												
												foreach( $surveySkills as $surveySkill )
												{
													echo '<option value="'.$surveySkill->skill->id.'">'.$surveySkill->skill->skill_name.'</option>';
												}
											}
										}
										else
										{
											if( $skills)
											{
												echo '<option value="">- Select -</option>';
												
												foreach( $skills as $skill )
												{
													echo '<option value="'.$skill->id.'">'.$skill->skill_name.'</option>';
												}
											}
										}
									?>
								</select>
							</div>
						</div>
						
						<div class="space-12"></div>
						
						<div class="row">
							<label class="col-sm-3 control-label no-padding-right">Customer Name <span class="red">*</span></label>
							<div class="col-sm-9">
								<select id="ReportDeliverySettings_customer_id" class="middle" name="ReportDeliverySettings[customer_id]" disabled>
									<option value="">- Select -</option>
								</select>
							</div>
						</div>
						
						<div class="space-12"></div>
						
						<div class="row">
							<label class="col-sm-3 control-label no-padding-right">Send Schedule <span class="red">*</span></label>
							<div class="col-sm-9">
								<select id="ReportDeliverySettings_auto_email_frequency" class="middle" name="ReportDeliverySettings[auto_email_frequency]">
									<option value="">- Select -</option>
									<option value="End of each week day">End of each week day</option>
									<option value="End of work week">End of work week</option>
									<option value="End of the month">End of the month</option>
								</select>
							</div>
						</div>
						
						<div class="space-12"></div>
						
						<div class="row">
							<label class="col-sm-3 control-label no-padding-right">Send Type <span class="red">*</span></label>
							<div class="col-sm-9">
								<select id="ReportDeliverySettings_type" class="middle" name="ReportDeliverySettings[type]">
									<option value="">- Select -</option>
									<option value="1">Send to customer files</option>
									<option value="2">Send to email</option>
								</select>
							</div>
						</div>
						
						<div class="space-12"></div>
					
						<div class="row auto_email_recipients_container" style="display:none;">
							<label class="col-sm-3 control-label no-padding-right">Email Address <span class="red">*</span></label>
							<div class="col-sm-9">
								<textarea id="ReportDeliverySettings_auto_email_recipients" name="ReportDeliverySettings[auto_email_recipients]" cols="60" rows="2"></textarea>
							</div>
						</div>
						
						<div class="space-12"></div>
					
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

