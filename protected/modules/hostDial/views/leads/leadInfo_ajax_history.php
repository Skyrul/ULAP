<div class="modal fade">
	<div class="modal-dialog" style="width:60%;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title blue"><?php echo $model->first_name.' '.$model->last_name; ?></h4>
			</div>
			<div class="modal-body no-padding">
				<div class="tabbable">
					<ul id="myTab4" class="nav nav-tabs padding-12 tab-color-blue background-blue">

						<li class="active">
							<a href="#history" data-toggle="tab">Call History</a>
						</li>
						
						<li>
							<a href="#lead-update-history" data-toggle="tab">Update History</a>
						</li>
						
					</ul>

					<div class="tab-content">
					
						<div class="tab-pane active" id="history">

							<div class="lead-history-table-wrapper" style="height:300px; overflow-y:auto;">
								<?php 
									echo $this->renderPartial('_lead_history_table', array('leadHistories'=>$leadHistories)); 
								?>
							</div>
							
							<form id="leadHistoryForm">
								
								<input type="hidden" name="LeadHistory[lead_id]" value="<?php echo $model->id; ?>">
								<input type="hidden" name="LeadHistory[lead_name]" value="<?php echo $model->first_name.' '.$model->last_name; ?>">
								<input type="hidden" name="LeadHistory[lead_phone_number]" value="<?php echo $model->office_phone_number; ?>">
								<input type="hidden" name="LeadHistory[type]" value="1">
							
								<div class="form-actions clearfix">
									<div class="row-fluid clearfix">							
										<textarea class="col-xs-12" id="LeadHistory_note" name="LeadHistory[note]"></textarea>
									</div>

									<div class="space-6"></div>

									<button class="btn btn-sm btn-info no-radius pull-right lead-history-submit-btn" type="button">
										SUBMIT NOTE
									</button>
								</div>
							</form>
						</div>
						
						<div class="tab-pane" id="lead-update-history">
							<div class="lead-udpdate-history-table-wrapper" style="height:300px; overflow-y:auto;">
								<?php 
									echo $this->renderPartial('_lead_update_history_table', array('leadUpdateHistories'=>$leadUpdateHistories)); 
								?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer hide"></div>
		</div>
	</div>
</div>