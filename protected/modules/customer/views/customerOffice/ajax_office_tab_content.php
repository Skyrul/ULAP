<div class="tab-pane fade in active" id="office<?php echo $office->id; ?>">
	
	<?php 
		$this->renderPartial( ( count($models) > 3 ) ? '/calendar/index_standard_layout' : '/calendar/index_get_started_layout' , array(
			'office' => $office,
			'officeStaffs' => $officeStaffs,
			'customer' => $customer,
			'calendars' => $calendars,
		));
	?>
	
</div>