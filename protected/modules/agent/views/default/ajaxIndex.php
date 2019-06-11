<div id="dialer" class="tab-pane fade in active">		
	<?php 
		if( $lead != null )
		{
			echo CHtml::link('<span>NEXT LEAD</span>', array('index', 'action'=>'nextLead'), array('class'=>'next-lead-btn next-lead-btn-disabled'));
			
			$this->renderPartial('leadInfo', array(
				'lead' => $lead,
				'list' => $list,
				'calendar' => $calendar,
				'customer' => $customer,
				'office' => $office,
				'officeOptions' => $officeOptions,
				'leadHistoryDataProvider' => $leadHistoryDataProvider,
			)); 
		}
		else
		{
			echo CHtml::link('<span>NEXT LEAD</span>', array('index', 'action'=>'nextLead'), array('class'=>'next-lead-btn'));
			
			$this->renderPartial('_empty_leadInfo');
		}
	?>
</div>

<div id="appointments" class="tab-pane fade">
	
	<?php 
		if( $lead != null )
		{
			$this->renderPartial('appointmentsTab', array(
				'lead' => $lead,
				'list' => $list,
				'calendar' => $calendar,
				'customer' => $customer,
				'office' => $office,
				'calendarOptions' => $calendarOptions,
			)); 
		}
		else
		{
			$this->renderPartial('_empty_appointmentsTab');
		}
	?>
	
</div>
