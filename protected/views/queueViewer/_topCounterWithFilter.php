<?php
	if( $_REQUEST['campaign_id'] == 3 ) //Insurance
	{
		$goalMtdLabel = 'Appointments Set MTD';
		$goalTodayLabel = 'Appointments Set Today';
		$goalRemainingLabel = 'Remaining Appointments to Set';
		$noShowReschedMtdVisible = true;
	}
	else
	{
		$goalMtdLabel = 'Goals Set MTD';
		$goalTodayLabel = 'Goals Set Today';
		$goalRemainingLabel = 'Remaining Goals to Set';
		$noShowReschedMtdVisible = false;
	}
?>

<div class="page-header">
	<h1>
		<div class="row">
			<div class="col-sm-12">
				<div class="col-sm-4">
					Leads Assigned (<?php echo number_format(LeadHopper::model()->count(array('condition'=>'type=1 AND status = "READY" AND agent_account_id IS NOT NULL AND skill_id IN ('.implode(', ', $campaignSkillIds).')'))); ?>)
					<br />
					<br />
					Current Queue leads (<?php echo number_format(LeadHopper::model()->count(array('condition'=>'type=1 AND status = "READY" AND agent_account_id IS NULL AND skill_id IN ('.implode(', ', $campaignSkillIds).')'))); ?>)
					<br />
					<br />
					Leads Callable Now (<?php echo number_format($eligibleNowQuery['totalCount']); ?>)
					<br />
					<br />
					Todays Leads (<?php echo number_format($todaysLeadsQuery['totalCount']); ?>)
				</div>
				
				<div class="col-sm-4">
					<?php echo $goalMtdLabel; ?> (<?php echo number_format( $appointmentSetMTD['totalCount'] + $insertAppointmentMTD['totalCount'] ); ?>)
					<br />
					<br />
					<?php echo $goalTodayLabel; ?> (<?php echo number_format($appointmentSetToday['totalCount'] + $insertAppointmentToday['totalCount'] ); ?>)
					<br />
					<br />
					
					<?php if( $noShowReschedMtdVisible ): ?>
					
						No Show Reschedule MTD (<?php echo number_format($noShowMTD['totalCount']); ?>)
						
						<br />
						<br />
						
					<?php endif; ?>

					<span class="red">
						<?php echo $goalRemainingLabel; ?> 

						<?php 
							if( $_REQUEST['campaign_id'] == 3 )
							{
								$goalRemaining =  CustomerQueueViewerSettings::model()->findByPk(17)->value;
							}								
							else
							{
								$goalRemaining = 0;
							}
							
							echo '('.$goalRemaining.')';
						?>
					</span>
				</div>
			</div>
		</div>
	</h1>
	
	<div class="space-12"></div>
	
	
	
	<div class="row">
		<div class="col-sm-10">
			<table class="table table-condensed table-bordered table-striped table-hover">
				<tr>
					<th class="center">1</th>
					<th class="center">.99 - .75</th>
					<th class="center">.74 - .50</th>
					<th class="center">.49 - .25</th>
					<th class="center">.24 - 0</th>
					<th class="center">Goal Complete</th>
				</tr>
				<tr>
					<td class="center"><?php echo CustomerQueueViewer::model()->count(array('condition'=>'status=1 AND priority=1.00 AND available_leads > 0 AND next_available_calling_time NOT IN ("Goal Appointment Reached", "On Hold", "Cancelled", "Removed", "Future Start Date", "Blank Start Date") AND skill_id IN ('.implode(', ', $campaignSkillIds).')')); ?></td>
					
					<td class="center"><?php echo CustomerQueueViewer::model()->count(array('condition'=>'status=1 AND priority >=.75 AND priority <=.99 AND available_leads > 0 AND next_available_calling_time NOT IN ("Goal Appointment Reached", "On Hold", "Cancelled", "Removed", "Future Start Date", "Blank Start Date") AND skill_id IN ('.implode(', ', $campaignSkillIds).')')); ?></td>
					
					<td class="center"><?php echo CustomerQueueViewer::model()->count(array('condition'=>'status=1 AND priority >=.50 AND priority <=.74 AND available_leads > 0 AND next_available_calling_time NOT IN ("Goal Appointment Reached", "On Hold", "Cancelled", "Removed", "Future Start Date", "Blank Start Date") AND skill_id IN ('.implode(', ', $campaignSkillIds).')')); ?></td>
					
					<td class="center"><?php echo CustomerQueueViewer::model()->count(array('condition'=>'status=1 AND priority >=.25 AND priority <=.49 AND available_leads > 0 AND next_available_calling_time NOT IN ("Goal Appointment Reached", "On Hold", "Cancelled", "Removed", "Future Start Date", "Blank Start Date") AND skill_id IN ('.implode(', ', $campaignSkillIds).')')); ?></td>
					
					<td class="center"><?php echo CustomerQueueViewer::model()->count(array('condition'=>'status=1 AND priority >=0 AND priority <=.24 AND available_leads > 0 AND next_available_calling_time NOT IN ("Goal Appointment Reached", "On Hold", "Cancelled", "Removed", "Future Start Date", "Blank Start Date") AND skill_id IN ('.implode(', ', $campaignSkillIds).')')); ?></td>
				
					<td class="center"><?php echo $goalComplete; ?></td>
				</tr>
			</table>
		</div>
		
		<div class="col-sm-2">
			<table class="table table-condensed table-bordered table-striped table-hover">
				<tr>
					<th class="center">LOW LEADS</th>
				</tr>
				<tr>
					<td class="center"><?php echo CustomerQueueViewer::model()->count(array('condition'=>'status = 2 AND available_leads = 0 AND next_available_calling_time NOT IN ("Cancelled", "Future Start Date", "Blank Start Date") AND skill_id IN ('.implode(', ', $campaignSkillIds).')')); ?></td>
				</tr>
			</table>
		</div>
	</div>
</div>