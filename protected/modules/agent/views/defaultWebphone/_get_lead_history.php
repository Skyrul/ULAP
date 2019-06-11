<div class="dialogs" style="max-height: 400px; overflow:auto;">
	<div class="timeline-container">
		<?php 
			$this->widget('zii.widgets.CListView', array(
				'id'=>'leadHistoryList',
				'dataProvider' => $leadHistoryDataProvider,
				'itemView'=>'_lead_history_list',
				'template'=>'<div class="timeline-items">{items}</div>',
			)); 
		?>
	</div>
</div>

<form id="leadHistoryForm">

	<input type="hidden" name="LeadHistory[lead_id]" value="<?php echo $lead->id; ?>">
	<input type="hidden" name="LeadHistory[lead_name]" value="<?php echo $lead->first_name.' '.$lead->last_name; ?>">
	<input type="hidden" name="LeadHistory[lead_phone_number]" value="<?php echo $lead->office_phone_number; ?>">
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