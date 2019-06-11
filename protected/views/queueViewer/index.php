<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	
	$cs = Yii::app()->clientScript;
	
	$cs->registerCssFile($baseUrl.'/template_assets/css/jquery-ui.css');
	
	$cs->registerCssFile($baseUrl.'/css/select2.min.css');
	
	$cs->registerScript(uniqid(), '
	
		$(document).ready( function(){
			
			$(".datepicker").datepicker({
				autoclose: true,
				todayHighlight: true
			});
			
		});
	
	', CClientScript::POS_END);

	$cs->registerScript(uniqid(), '
	
		$(document).ready( function(){
			
			setInterval(function(){ 
				
				var inProcess = false;
				
				if( !inProcess )
				{
					inProcess = true;
					
					$.ajax({
						url: yii.urls.absoluteUrl + "/queueViewer/Index",
						type: "post",
						dataType: "json",
						data: {
							"ajax": 1,
							"skill_id" : "'.$skill_id.'",
							"campaign_id" : "'.$campaign_id.'",
							"is_header" : $(".is-table-header").val()
						},
						beforeSend: function(){

						},
						success: function(response){
							
							if( response.status == "success" )
							{
								$(".wrapper").html(response.html);
							}
							
							inProcess = false;
							
						}
					});
				}

			}, 10000);
			
			var removeFormOngoing = false;
			
			$(document).on("click", ".remove-link", function(){
				
				var customer_id = $(this).attr("customer_id");
				
				if( !removeFormOngoing )
				{
					$.ajax({
						url: yii.urls.absoluteUrl + "/queueViewer/remove",
						type: "post",
						dataType: "json",
						data: { 
							"ajax": 1, 
							"customer_id": customer_id, 
						},
						success: function(response) {
						
							removeFormOngoing = false; 
							
							if(response.html != "")
							{
								modal = response.html;
								
								var modal = $(modal).appendTo("body");
								
								modal.find(".datepicker").datepicker({
									autoclose: true,
									todayHighlight: true
								});
								
								modal.find("button[data-action=save]").on("click", function() {
									
									errors = "";
									
									if( $("#CustomerQueueViewer_removal_start_date").val() == "" )
									{
										errors += "Start date is required. \n";
									}
									
									if( $("#CustomerQueueViewer_removal_end_date").val() == "" )
									{
										errors += "End date is required. \n";
									}
									
									if( errors != "" )
									{
										alert("Please fix the following to proceed: \n\n" + errors);
									}
									else
									{
										data = modal.find("form").serialize() + "&ajax=1&customer_id="+customer_id;
										
										$.ajax({
											url: yii.urls.absoluteUrl + "/queueViewer/remove",
											type: "post",
											dataType: "json",
											data: data,
											success: function(response) {

												modal.modal("hide");
											}
										});
									}
								});
							
								modal.modal("show").on("hidden.bs.modal", function(){
									
									modal.remove();
								});
							}

						}
					});
				}
				
			});
			
		});
		
	', CClientScript::POS_END);

	$cs->registerScript('boostJs','
	
		boostAjaxSending = false;
		
		$("body").on("click", ".boost-link", function(e){
			e.preventDefault();
		
			var customerId = $(this).data("customer_id");
			var skillId = $(this).data("skill_id");
			
			if(!boostAjaxSending)
			{
				boostAjaxSending = true;
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/queueViewer/addCustomerBoost",
					type: "GET",	
					data: { 
						"customer_id" : customerId,					
						"skill_id" : skillId				
					},
					beforeSend: function(){
						$("#div_ajaxLoader").empty().append("Loading...");
					},
					complete: function(){
						boostAjaxSending = false;
						$("#div_ajaxLoader").empty();
					},
					error: function(){
						$("#div_ajaxLoader").empty().append("Error...");
					},
					success: function(r){
						$("#myModalMd #myModalLabel").html("Customer Boost");
						$("#myModalMd .modal-body").html(r);
						$("#myModalMd").modal();
					},
				});
			}
			
		});
		
		$("body").on("click", ".force-queue-link", function(e){
			
			e.preventDefault();

			var this_button = $(this);
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/queueViewer/forceQueue",
				type: "GET",	
				data: { "ajax":1 },
				beforeSend: function(){
					this_button.html("Loading please wait...");
				},
				success: function(r){
					this_button.html("<i class=\"fa fa-user-md\"></i> Force Queue");
				},
			});
		});
		
	', CClientScript::POS_END);
	
	
	$cs->registerScript('boostFormJs','
	
		boostAjaxSending = false;
		
		$("body").on("change", "#'.CHtml::activeId(new CustomerQueueViewerBoost,'type').'", function(e){
				var thisVal = $(this).val();
				
				$(".beginning-date-container").addClass("hidden");
				
				if(thisVal == 1)
				{
					$(".beginning-date-container").addClass("hidden");
				}
				else
				{
					$(".beginning-date-container").removeClass("hidden");
				}
		});
		
	', CClientScript::POS_END);
	
	$cs->registerScript('filterScriptJS','
	
		$("body").on("change", ".filter-by-skill",function(){
			if( $(this).val() == "" )
			{
				window.location = "'.Yii::app()->createUrl('queueViewer/index').'";
			}
			else
			{
				window.location = "'.Yii::app()->createUrl('queueViewer/index').'?skill_id="+$(this).val();
			}
		});
		
		$("body").on("change", ".filter-by-campaign",function(){
			if( $(this).val() == "" )
			{
				window.location = "'.Yii::app()->createUrl('queueViewer/index').'";
			}
			else
			{
				window.location = "'.Yii::app()->createUrl('queueViewer/index').'?campaign_id="+$(this).val();
			}
		});
	',CClientScript::POS_END);
	
	
	$cs->registerScript('headersOnOffJS','
	
		$("body").on("change", ".is-table-header",function(){
			
			if ($(this).val() == "Off")
			{
				$("tr.style-header").addClass("hidden");
			}
			else
				$("tr.style-header").removeClass("hidden");
		});
	',CClientScript::POS_END);
	
	$cs->registerCss('tableHeaders','
		.table tr.style-header{   
			color: #707070;
			font-weight: 400;
			background: #F2F2F2;
		}
		
		.table tr.style-header>th{    
			border-color: #ddd;
			font-weight: 700;
		}
	');
	
	if( $campaignSkillIds )
	{
		$todaysLeadsQuery = Yii::app()->db->createCommand()
		->select('SUM(total_leads) as totalCount')
		->from('ud_customer_queue_viewer')
		->where('status = 1')
		->andWhere('available_leads > 0')
		->andWhere('next_available_calling_time NOT IN ("On Hold", "Cancelled", "Removed", "Goal Appointment Reached", "Blank Start Date", "Future Start Date", "Decline Hold")')
		->andWhere(array('IN', 'skill_id', $campaignSkillIds))
		->queryRow();
		
		
		
		$eligibleNowQuery = Yii::app()->db->createCommand()
		->select('SUM(available_leads) as totalCount')
		->from('ud_customer_queue_viewer')
		->where('status = 1')
		->andWhere('available_leads > 0')
		->andWhere('next_available_calling_time ="Now"')
		->andWhere(array('IN', 'skill_id', $campaignSkillIds))
		->queryRow(); 
		
		// $appointmentSetMTD = Yii::app()->db->createCommand()
		// ->select('SUM(current_goals) as totalCount')
		// ->from('ud_customer_queue_viewer')
		// ->queryRow();
		
		//appointment set count mtd
		$appointmentSetMTDSql = "
			SELECT count(distinct lch.lead_id) AS totalCount 
			FROM ud_lead_call_history lch 
			LEFT JOIN ud_lists ls ON ls.id = lch.list_id 
			LEFT JOIN ud_calendar_appointment ca ON ca.id = lch.calendar_appointment_id 
			WHERE ca.title IN ('APPOINTMENT SET', 'CANCEL APPOINTMENT', 'RESCHEDULE APPOINTMENT')
			AND ls.skill_id IN (".implode(', ', $campaignSkillIds).")
			AND lch.disposition = 'Appointment Set'
			AND lch.date_created >= '".date('Y-m-01 00:00:00')."' 
			AND lch.date_created <= '".date('Y-m-t 23:59:59')."'
		";

		
		$command = Yii::app()->db->createCommand($appointmentSetMTDSql);
		$appointmentSetMTD = $command->queryRow();
		
		//insert appointment count mtd
		$insertAppointmentMTDSql = "
			SELECT count(distinct ca.lead_id) AS totalCount 
			FROM ud_calendar_appointment ca
			LEFT JOIN ud_lead ld ON ld.id = ca.lead_id
			LEFT JOIN ud_lists ls ON ls.id = ld.list_id
			WHERE ca.title = 'INSERT APPOINTMENT'
			AND ls.skill_id IN (".implode(', ', $campaignSkillIds).")			
			AND ca.status != 4
			AND ca.date_created >= '".date('Y-m-01 00:00:00')."' 
			AND ca.date_created <= '".date('Y-m-t 23:59:59')."'				
		";
		
		$command = Yii::app()->db->createCommand($insertAppointmentMTDSql);
		$insertAppointmentMTD = $command->queryRow();
		
		//no show count
		$noShowMTD = Yii::app()->db->createCommand()
		->select('SUM(no_show) as totalCount')
		->from('ud_customer_queue_viewer')
		->andWhere(array('IN', 'skill_id', $campaignSkillIds))
		->queryRow();  
		
		$connection = Yii::app()->db;
		
		//appointment set today
		$appointmentSetTodaySql = "
			SELECT count(distinct lch.lead_id) AS totalCount 
			FROM ud_lead_call_history lch 
			LEFT JOIN ud_lists ls ON ls.id = lch.list_id 
			LEFT JOIN ud_calendar_appointment ca ON ca.id = lch.calendar_appointment_id 
			WHERE ca.title IN ('INSERT APPOINTMENT', 'APPOINTMENT SET', 'CANCEL APPOINTMENT', 'RESCHEDULE APPOINTMENT') 
			AND ls.skill_id IN (".implode(', ', $campaignSkillIds).")
			AND lch.disposition = 'Appointment Set'
			AND lch.date_created >= '".date('Y-m-d 00:00:00')."' 
			AND lch.date_created <= '".date('Y-m-d 23:59:59')."'
		";
		
		$command = Yii::app()->db->createCommand($appointmentSetTodaySql);
		$appointmentSetToday = $command->queryRow();
		
		//insert appointment today
		$insertAppointmentTodaySql = "
			SELECT count(distinct ca.lead_id) AS totalCount 
			FROM ud_calendar_appointment ca
			LEFT JOIN ud_lead ld ON ld.id = ca.lead_id
			LEFT JOIN ud_lists ls ON ls.id = ld.list_id
			WHERE ca.title = 'INSERT APPOINTMENT'
			AND ls.skill_id IN (".implode(', ', $campaignSkillIds).")				
			AND ca.status != 4
			AND ca.date_created >= '".date('Y-m-d 00:00:00')."' 
			AND ca.date_created <= '".date('Y-m-d 23:59:59')."'				
		";
		
		$command = Yii::app()->db->createCommand($insertAppointmentTodaySql);
		$insertAppointmentToday = $command->queryRow();
		
		$goalComplete = CustomerQueueViewer::model()->count(array(
			'condition' => 'next_available_calling_time = "Goal Appointment Reached" AND skill_id IN ('.implode(', ', $campaignSkillIds).')',
		));
	}
	else
	{
		$todaysLeadsQuery = Yii::app()->db->createCommand()
		->select('SUM(total_leads) as totalCount')
		->from('ud_customer_queue_viewer')
		->where('status = 1')
		->andWhere('available_leads > 0')
		->andWhere('next_available_calling_time NOT IN ("On Hold", "Cancelled", "Removed", "Goal Appointment Reached", "Blank Start Date", "Future Start Date", "Decline Hold")')
		->queryRow();
		
		$eligibleNowQuery = Yii::app()->db->createCommand()
		->select('SUM(available_leads) as totalCount')
		->from('ud_customer_queue_viewer')
		->where('status = 1')
		->andWhere('available_leads > 0')
		->andWhere('next_available_calling_time ="Now"')
		->queryRow(); 
		
		// $appointmentSetMTD = Yii::app()->db->createCommand()
		// ->select('SUM(current_goals) as totalCount')
		// ->from('ud_customer_queue_viewer')
		// ->queryRow();
		
		//appointment set count mtd
		$appointmentSetMTDSql = "
			SELECT count(distinct lch.lead_id) AS totalCount 
			FROM ud_lead_call_history lch 
			LEFT JOIN ud_lists ls ON ls.id = lch.list_id 
			LEFT JOIN ud_calendar_appointment ca ON ca.id = lch.calendar_appointment_id 
			WHERE ca.title IN ('APPOINTMENT SET', 'CANCEL APPOINTMENT', 'RESCHEDULE APPOINTMENT') 
			AND lch.disposition = 'Appointment Set'
			AND lch.date_created >= '".date('Y-m-01 00:00:00')."' 
			AND lch.date_created <= '".date('Y-m-t 23:59:59')."'
		";

		
		$command = Yii::app()->db->createCommand($appointmentSetMTDSql);
		$appointmentSetMTD = $command->queryRow();
		
		//insert appointment count mtd
		$insertAppointmentMTDSql = "
			SELECT count(distinct ca.lead_id) AS totalCount 
			FROM ud_calendar_appointment ca
			LEFT JOIN ud_lead ld ON ld.id = ca.lead_id
			LEFT JOIN ud_lists ls ON ls.id = ld.list_id
			WHERE ca.title = 'INSERT APPOINTMENT' 
			AND ca.status != 4
			AND ca.date_created >= '".date('Y-m-01 00:00:00')."' 
			AND ca.date_created <= '".date('Y-m-t 23:59:59')."'				
		";
		
		$command = Yii::app()->db->createCommand($insertAppointmentMTDSql);
		$insertAppointmentMTD = $command->queryRow();
		
		//no show count
		$noShowMTD = Yii::app()->db->createCommand()
		->select('SUM(no_show) as totalCount')
		->from('ud_customer_queue_viewer')
		->queryRow();  
		
		$connection = Yii::app()->db;
		
		//appointment set today
		$appointmentSetTodaySql = "
		
			SELECT count(distinct lch.lead_id) AS totalCount 
			FROM ud_lead_call_history lch 
			LEFT JOIN ud_lists ls ON ls.id = lch.list_id 
			LEFT JOIN ud_calendar_appointment ca ON ca.id = lch.calendar_appointment_id 
			WHERE ca.title IN ('INSERT APPOINTMENT', 'APPOINTMENT SET', 'CANCEL APPOINTMENT', 'RESCHEDULE APPOINTMENT') 
			AND lch.disposition = 'Appointment Set'
			AND lch.date_created >= '".date('Y-m-d 00:00:00')."' 
			AND lch.date_created <= '".date('Y-m-d 23:59:59')."'
		";

		
		$command = Yii::app()->db->createCommand($appointmentSetTodaySql);
		$appointmentSetToday = $command->queryRow();
		
		//insert appointment today
		$insertAppointmentTodaySql = "
			SELECT count(distinct ca.lead_id) AS totalCount 
			FROM ud_calendar_appointment ca
			LEFT JOIN ud_lead ld ON ld.id = ca.lead_id
			LEFT JOIN ud_lists ls ON ls.id = ld.list_id
			WHERE ca.title = 'INSERT APPOINTMENT' 
			AND ca.status != 4
			AND ca.date_created >= '".date('Y-m-d 00:00:00')."' 
			AND ca.date_created <= '".date('Y-m-d 23:59:59')."'				
		";
		
		$command = Yii::app()->db->createCommand($insertAppointmentTodaySql);
		$insertAppointmentToday = $command->queryRow();
		
		$goalComplete = CustomerQueueViewer::model()->count(array(
			'condition' => 'next_available_calling_time = "Goal Appointment Reached"',
		));
	}
?>

<!-- Modal -->
<div class="modal fade" id="myModalMd" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Modal title</h4>
      </div>
      <div class="modal-body">
        ...
      </div>
    </div>
  </div>
</div>


<div class="wrapper">

	<?php 
		$topCounterPage = !empty($campaignSkillIds) ? '_topCounterWithFilter' : '_topCounter';
		
		$this->renderPartial($topCounterPage, array(
			'eligibleNowQuery' => $eligibleNowQuery,
			'todaysLeadsQuery' => $todaysLeadsQuery,
			'appointmentSetMTD' => $appointmentSetMTD,
			'insertAppointmentMTD' => $insertAppointmentMTD,
			'appointmentSetToday' => $appointmentSetToday,
			'insertAppointmentToday' => $insertAppointmentToday,
			'insertAppointmentToday' => $insertAppointmentToday,
			'noShowMTD' => $noShowMTD,
			'goalComplete' => $goalComplete,
			'campaignSkillIds' => $campaignSkillIds,
		));
	?>

	<?php $this->renderPartial('_filterForm',array(
		'skill_id' => $skill_id,
		'campaign_id' => $campaign_id,
		'is_header' => $is_header,
		'queueSkillList' => $queueSkillList,
		'queueCampaignList' => $queueCampaignList,
	)); ?>
	
	
	<div class="space-12"></div>
	
	<?php 
		$currentGoalLabel = ($_REQUEST['campaign_id'] == 3 || !isset($_REQUEST['campaign_id'])) ? 'Current Appointments' : 'Current Goals';
		
		$header = '<tr class="style-header">
				<th>Customer Name</th>
				<th>Skill</th>
				<th>Goal/Lead</th>
				<th>Priority Reset Date</th>
				<th>Priority</th>
				<th>Pace</th>
				<th>'.$currentGoalLabel.'</th>
				<th>Current Dials</th>
				<th>Leads Callable Now</th>
				<th>Leads Not Callable Now</th>
				<th>Total Potential</th>
				<th>Next Available Calling Time</th>
				<th>Available Calling Blocks</th>
				<th>Call Agent</th>
				<th>Dials until Re-evaluation</th>
				<th class="center" width="12%">Options</th>
			</tr>';
	?>
			
	<table class="table table-condensed table-bordered table-hover">
		<thead>
			<tr>
				<th>Customer Name</th>
				<th>Skill</th>
				<th>Goal/Lead</th>
				<th>Priority Reset Date</th>
				<th>Priority</th>
				<th>Pace</th>
				<th><?php echo $currentGoalLabel; ?></th>
				<th>Current Dials</th>
				<th>Leads Callable Now</th>
				<th>Leads Not Callable Now</th>
				<th>Total Potential</th>
				<th>Next Available Calling Time</th>
				<th>Available Calling Blocks</th>
				<th>Call Agent</th>
				<th>Dials until Re-evaluation</th>
				<th class="center" width="12%">Options</th>
			</tr>
		</thead>
		
		<tbody>
			<?php 
				// echo 'Customers on white list: ' . count($customerWhiteQueues);
				
				// echo '<br>';
				
				// echo 'Customers on grey list: ' . count($customerGreyQueues);
				
				// echo '<br>';
				
				// echo 'Total: ' . (count($customerWhiteQueues) + count($customerGreyQueues));
				
				// echo '<br><br>';
				$ctr = 1;
				if( $customerWhiteQueues )
				{
					foreach( $customerWhiteQueues as $customerWhiteQueue  )
					{
						$availableLeadsStyle = '';
						$rowStyle = '';
						$priorityStyle = '';
						
						if($customerWhiteQueue->available_leads == 0)
						{
							$availableLeadsStyle = 'background:red;';
							$rowStyle = 'background:#CCCCCC;';
						}
						
						if( $customerWhiteQueue->priority <= 0 )
						{
							$priorityStyle = 'background:#E6B8B7;';
						}
						
						if( $customerWhiteQueue->next_available_calling_time == "Goal Appointment Reached" )
						{
							$rowStyle = 'background:#FAF2CC;';
						}
						
						if( $customerWhiteQueue->status == 2 )
						{
							$rowStyle = 'background:#CCCCCC;';
						}
						?>
						
						<tr style="<?php echo $rowStyle; ?>;">
							
							<td>
								<?php echo CHtml::link($customerWhiteQueue->customer_name, array('customer/insight', 'customer_id'=>$customerWhiteQueue->customer_id)); ?>
								
								<?php if(isset($cqvBoostHolder[$customerWhiteQueue->customer_id][$customerWhiteQueue->skill_id])){ ?>
									<br><span class="btn btn-minier btn-success">Boost</span>
								<?php } ?>
							</td>
							
							<td>
								<?php echo $customerWhiteQueue->skill_name; ?>
							</td>
							
							<td>
								<?php echo $customerWhiteQueue->fulfillment_type; ?>
							</td>
							
							<td><?php echo $customerWhiteQueue->priority_reset_date; ?></td>
							
							<td style="<?php echo $priorityStyle; ?>"><?php echo $customerWhiteQueue->priority; ?></td>
							
							<td><?php echo $customerWhiteQueue->pace; ?></td>
							
							<td><?php echo $customerWhiteQueue->fulfillment_type == 'Goal' ? $customerWhiteQueue->current_goals : ''; ?></td>
							
							<td><?php echo $customerWhiteQueue->current_dials; ?></td>
							
							<td style="<?php echo $availableLeadsStyle; ?>;"><?php echo $customerWhiteQueue->available_leads; ?></td>
							
							<td><?php echo $customerWhiteQueue->not_completed_leads; ?></td>
							
							<td><?php echo $customerWhiteQueue->total_potential_dials; ?></td>
							
							<td><?php echo $customerWhiteQueue->next_available_calling_time; ?></td>
							
							<td><?php echo $customerWhiteQueue->available_calling_blocks; ?></td>
							
							<td><?php echo $customerWhiteQueue->call_agent; ?></td>
							
							<td><?php echo $customerWhiteQueue->dials_until_reset; ?></td>

							<td class="center">
								<?php if( $customerWhiteQueue->available_leads > 0 ): ?>
								
								<button data-customer_id="<?php echo $customerWhiteQueue->customer_id; ?>" data-skill_id="<?php echo $customerWhiteQueue->skill_id; ?>" class="btn btn-minier btn-success boost-link"><i class="fa fa-plus"></i> Boost</button>

								<button customer_id="<?php echo $customerWhiteQueue->customer_id; ?>" class="btn btn-minier btn-danger remove-link"><i class="fa fa-times"></i> Remove</button>
							
								<?php endif; ?>
							</td>
						</tr>
						
						<?php
						
						$ctr++;
						
						if( ($ctr % 20) == 0)
							echo $header;
					}
				}
				
				if( $customerGreyQueues )
				{
					foreach( $customerGreyQueues as $customerGreyQueue  )
					{
						$availableLeadsStyle = '';
						$rowStyle = '';
						$priorityStyle = '';

						if( $customerGreyQueue->priority <= 0 )
						{
							$priorityStyle = 'background:#E6B8B7;';
						}
						
						if( $customerGreyQueue->status == 2 )
						{
							$rowStyle = 'background:#CCCCCC;';
						}
						
						if($customerGreyQueue->available_leads == 0)
						{
							$availableLeadsStyle = 'background:red;';
							$rowStyle = 'background:#CCCCCC;';
						}
						
						if( $customerGreyQueue->next_available_calling_time == "Goal Appointment Reached" )
						{
							$rowStyle = 'background:#FAF2CC;';
						}
						?>
						
						<tr style="<?php echo $rowStyle; ?>;">
							
							<td>
								<?php echo CHtml::link($customerGreyQueue->customer_name, array('customer/insight', 'customer_id'=>$customerGreyQueue->customer_id)); ?>
							</td>
							
							<td>
								<?php echo $customerGreyQueue->skill_name; ?>
							</td>
							
							<td>
								<?php echo $customerGreyQueue->fulfillment_type; ?>
							</td>
							
							<td><?php echo $customerGreyQueue->priority_reset_date; ?></td>
							
							<td style="<?php echo $priorityStyle; ?>"><?php echo $customerGreyQueue->priority; ?></td>
							
							<td><?php echo $customerGreyQueue->pace; ?></td>
							
							<td><?php echo $customerGreyQueue->fulfillment_type == 'Goal' ? $customerGreyQueue->current_goals : ''; ?></td>
							
							<td><?php echo $customerGreyQueue->current_dials; ?></td>
							
							<td style="<?php echo $availableLeadsStyle; ?>;"><?php echo $customerGreyQueue->available_leads; ?></td>
							
							<td><?php echo $customerGreyQueue->not_completed_leads; ?></td>
							
							<td><?php echo $customerGreyQueue->total_potential_dials; ?></td>
							
							<td>
								<?php 
									if( strtotime($customerGreyQueue->start_date) > time() )
									{
										echo 'Future Start Date';
									}
									else
									{
										echo $customerGreyQueue->next_available_calling_time; 
									}
								?>
							</td>
							
							<td><?php echo $customerGreyQueue->available_calling_blocks; ?></td>
							
							<td><?php echo $customerGreyQueue->call_agent; ?></td>
							
							<td><?php echo $customerGreyQueue->dials_until_reset; ?></td>

							<td class="center">
								<?php if( $customerGreyQueue->available_leads > 0 ): ?>
								
								<button data-customer_id="<?php echo $customerGreyQueue->customer_id; ?>" data-skill_id="<?php echo $customerGreyQueue->skill_id; ?>" class="btn btn-minier btn-success boost-link"><i class="fa fa-plus"></i> Boost</button>

								<button customer_id="<?php echo $customerGreyQueue->customer_id; ?>" class="btn btn-minier btn-danger remove-link"><i class="fa fa-times"></i> Remove</button>
							
								<?php endif; ?>
							</td>
						</tr>
						
						<?php
						$ctr++;
						
						if( ($ctr % 20) == 0)
							echo $header;
					}
				}
			?>
		</tbody>
	</table>

</div>
