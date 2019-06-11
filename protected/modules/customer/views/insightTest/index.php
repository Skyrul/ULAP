<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	
	$cs = Yii::app()->clientScript;
	
	$cs->registerCssFile($baseUrl.'/template_assets/css/jquery-ui.css');

	$cs->registerCssFile($baseUrl.'/template_assets/css/fullcalendar.css');
	
	$cs->registerCss(uniqid(), '
		.popover {
			max-width:600px !important;
			width:600px !important;
		}
		.popover-content {
			max-height: 300px;
			overflow-y: scroll;
			padding: 0 !important; 
		}
	');

	$cs->registerScriptFile( $baseUrl . '/js/customer_insight.js?'.time());
	
	$cs->registerScript(uniqid(), '
	
		function updateCounters() {
			
			var customer_id = "'.$customer->id.'";
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/customer/insight/index?customer_id=" + customer_id,
				type: "post",
				dataType: "json",
				data: { "ajax":"getCount" },
				success: function(response){
					
					if( response.action_center > 0 )
					{
						$(".action-center-count").html("<span class=\"red\">(" + response.action_center + ")");
					}
					else
					{
						$(".action-center-count").empty();
					}
					
					
					if( response.schedule_conflict > 0 )
					{
						$(".schedule-conflict-count").html("<span class=\"red\">(" + response.schedule_conflict + ")");
					}
					else
					{
						$(".schedule-conflict-count").empty();
					}
					
					
					if( response.location_conflict > 0 )
					{
						$(".location-conflict-count").html("<span class=\"red\">(" + response.location_conflict + ")");
					}
					else
					{
						$(".location-conflict-count").empty();
					}
				},
			});
			
		}
		
		
		function updateDashboard() {
			
			var customer_id = "'.$customer->id.'";
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/customer/insight/index?customer_id=" + customer_id,
				type: "post",
				dataType: "json",
				data: { "ajax":"customerSummary" },
				success: function(response){
					
					$(".dashboard-container").html(response.html);
					
				},
			});
			
		}
		
	
	', CClientScript::POS_HEAD);
?>

<?php 
	$cs->registerScript(uniqid(), '

		$(document).ready( function(){
			
			$(".date-picker").datepicker({
				autoclose: true,
				todayHighlight: true
			});
			
			$(".modal-recyle-module").on("click",function(){
				$.ajax({
					url: yii.urls.absoluteUrl + "/customer/insight/recycleLeads/",
					type: "GET",	
					data: { 
						"customer_id" : "'.$customer->id.'"			
					},
					beforeSend: function(){
					},
					complete: function(){
					},
					error: function(){
					},
					success: function(r){
						// header = "Recycle Leads";
						header = "Recertify Names";
						// $("#myModalMd #myModalLabel").html(header);
						$("#myModalMd .modal-body").html(r);
						$("#myModalMd").modal();
						
					},
				});
			});
				
			$(document).on("click", ".show-description-link", function(){
				
				var descriptionDiv = $(this).parent().find(".description-container");
				var descriptionLinkIcon = $(this).parent().find(".show-description-link > i");
				
				if( descriptionDiv.is(":hidden") )
				{
					descriptionDiv.fadeIn();
					descriptionLinkIcon.addClass("fa-chevron-circle-up");
					descriptionLinkIcon.removeClass("fa-chevron-circle-down");
				}
				else
				{
					descriptionDiv.hide();
					descriptionLinkIcon.addClass("fa-chevron-circle-down");
					descriptionLinkIcon.removeClass("fa-chevron-circle-up");
				}
			});
			
			
			$(document).on("click", ".btn-recertify-list-link", function(){
				
				var skillMaxLife = $(this).attr("recertify_days");
				var msg = "I am authorizing these names for another "+skillMaxLife+" days of calling and certify that they are free of do not call restrictions.";
				
				if( confirm(msg) )
				{
					var this_button = $(this);		
					var this_row = this_button.closest("tr");
					
					var customer_id = $(this).attr("customer_id");
					var list_id = $(this).attr("list_id");
					var recertify_days = $(this).attr("recertify_days");
					
					$.ajax({
						url: yii.urls.absoluteUrl + "/customer/insight/recertify/",
						type: "GET",
						dataType: "json",
						data: { 
							"customer_id" : customer_id,	
							"list_id" : list_id,	
							"recertify_days" : recertify_days,	
						},
						beforeSend: function(){
							this_button.text("Saving please wait...");
						},
						error: function(){
							alert("Sorry but an error occurred. Please try again later.");
						},
						success: function(r){
							
							if( r.status == "success" )
							{
								this_row.fadeOut("slow", function() { $(this).remove(); });
							}
							else
							{
								alert("Sorry but an error occurred. Please try again later.");
							}
							
							this_button.text("Recertify");
							
							updateDashboard();
						},
					});
				}
				
			});
			
			$(document).on("click", ".btn-recertify-remove-lead", function(){
				
				if( confirm("Are you sure you want to remove this lead?") )
				{
					var this_button = $(this);		
					var this_row = this_button.closest("tr");
					
					var lead_id = $(this).prop("id");
					
					$.ajax({
						url: yii.urls.absoluteUrl + "/customer/insight/recertifyRemoveLead/",
						type: "GET",
						dataType: "json",
						data: { 
							"lead_id" : lead_id,	
						},
						beforeSend: function(){
							this_button.text("Saving please wait...");
						},
						error: function(){
							alert("Sorry but an error occurred. Please try again later.");
						},
						success: function(r){
							
							if( r.status == "success" )
							{
								this_row.fadeOut("slow", function() { $(this).remove(); });
							}
							else
							{
								alert("Sorry but an error occurred. Please try again later.");
							}
							
							this_button.text("Recertify");

						},
					});
				}
				
			});
			
			$(document).on("click", ".btn-recycle-link", function(){
				
				if( confirm("Are you sure you want to recycle these leads?") )
				{
					var this_button = $(this);		
					var this_row = this_button.closest("tr");
					
					var customer_id = $(this).attr("customer_id");
					var recycle_lead_call_history_disposition_id = $(this).attr("recycle_lead_call_history_disposition_id");
					
					$.ajax({
						url: yii.urls.absoluteUrl + "/customer/insight/recycle/",
						type: "GET",
						dataType: "json",
						data: { 
							"customer_id" : customer_id,	
							"recycle_lead_call_history_disposition_id" : recycle_lead_call_history_disposition_id,	
						},
						beforeSend: function(){
							this_button.text("Saving please wait...");
						},
						error: function(){
							alert("Sorry but an error occurred. Please try again later.");
						},
						success: function(r){
							
							if( r.status == "success" )
							{
								this_row.fadeOut("slow", function() { $(this).remove(); });
							}
							else
							{
								// alert("Sorry but an error occurred. Please try again later.");
								alert(r.message);
							}
							
							this_button.text("Recycle");
	
							updateDashboard(); 
						},
					});
				}
				
			});
			
			$(document).on("click", ".btn-recycle-remove-lead", function(){
				
				if( confirm("Are you sure you want to remove this lead?") )
				{
					var this_button = $(this);		
					var this_row = this_button.closest("tr");
					
					var lead_id = $(this).prop("id");
					
					$.ajax({
						url: yii.urls.absoluteUrl + "/customer/insight/recycleRemoveLead/",
						type: "GET",
						dataType: "json",
						data: { 
							"lead_id" : lead_id,	
						},
						beforeSend: function(){
							this_button.text("Saving please wait...");
						},
						error: function(){
							alert("Sorry but an error occurred. Please try again later.");
						},
						success: function(r){
							
							if( r.status == "success" )
							{
								this_row.fadeOut("slow", function() { $(this).remove(); });
							}
							else
							{
								alert("Sorry but an error occurred. Please try again later.");
							}
							
							this_button.text("Recycle");
 
						},
					});
				}
				
			});
			
			
			//adds close button on popover
			$(document).on("click", ".popover-info", function(){	
				var id = $(this).data("contentwrapper");

				var original_title = $(this).data("original-title");
				
				$(".popover-title").html(original_title + "<span id="+id+" class=\"close popover-close\">&times;</span>");
			});
			
			$(document).on("click", ".popover-close", function(){
				var id = $(this).prop("id"); 
				var popoverID = $(id).prev().prop("id");
				
				$("#" + popoverID).popover("hide");	
			});
			
			$("body").on("click", function (e) {
				if( $(e.target).data("rel") !== "popover" && $(e.target).parents(".popover.in").length === 0 )
				{ 
					$("[data-rel=\"popover\"]").popover("hide");
				}
			});
		});

	',CClientScript::POS_END);
?>

<!-- Modal -->
<div class="modal fade" id="myModalMd" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-lg" role="document" style="width:90%;">
    <div class="modal-content">
      <div class="modal-header" style="background:#438EB9;">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		<h4 class="modal-title" id="myModalLabel" style="color:#ffffff;">Recertify/Recycle Names</h4> 
      </div>
      <div class="modal-body" style="padding:5px;">
        ...
      </div>
    </div>
  </div>
</div>

<?php 
	$this->widget("application.components.CustomerSideMenu",array(
		'active'=> Yii::app()->controller->id,
		'customer' => $customer,
	));
?>

<div class="text-center">

	<div class="row">
		<div class="col-sm-12 infobox-container">
			<?php 
				if( $customerSkills )
				{
					$skillColors = array('orange', 'orange2', 'blue2', 'green2', 'purple', 'black', 'grey', 'light-brown');
					
					foreach( $customerSkills as $customerSkill )
					{
						if($customerSkill->skill_id == 11 || $customerSkill->skill_id == 12)
								continue;
							
						$skillColorsRandomKeys = array_rand($skillColors, 2);
						
						$totalLeads = 0;
						$contract = $customerSkill->contract;
						
						if($contract->fulfillment_type != null )
						{
							##get Appointment that has been scheduled ##
							$appointmentSetMTDSql = "
								SELECT count(distinct lch.lead_id) AS totalCount 
								FROM ud_lead_call_history lch 
								LEFT JOIN ud_lists ls ON ls.id = lch.list_id 
								LEFT JOIN ud_calendar_appointment ca ON ca.id = lch.calendar_appointment_id 
								WHERE ca.title IN ('APPOINTMENT SET', 'CANCEL APPOINTMENT', 'RESCHEDULE APPOINTMENT') 
								AND lch.disposition = 'Appointment Set'
								AND lch.date_created >= '".date('Y-m-01 00:00:00')."' 
								AND ca.date_created <= '".date('Y-m-t 23:59:59')."'
								AND lch.customer_id = '".$customer->id."'
								AND ls.skill_id = '".$customerSkill->skill_id."' 
							";
							
							$command = Yii::app()->db->createCommand($appointmentSetMTDSql);
							$appointmentSetMTD = $command->queryRow();
							
							$insertAppointmentMTDSql = "
								SELECT count(distinct ca.lead_id) AS totalCount 
								FROM ud_calendar_appointment ca
								LEFT JOIN ud_lead ld ON ld.id = ca.lead_id
								LEFT JOIN ud_lists ls ON ls.id = ld.list_id
								WHERE ca.title = 'INSERT APPOINTMENT' 
								AND ca.status != 4
								AND ca.date_created >= '".date('Y-m-01 00:00:00')."' 
								AND ca.date_created <= '".date('Y-m-t 23:59:59')."'				
								AND ld.customer_id = '".$customer->id."'						
								AND ls.skill_id = '".$customerSkill->skill_id."'   
							";
							
							$command = Yii::app()->db->createCommand($insertAppointmentMTDSql);
							$insertAppointmentMTD = $command->queryRow();
							
							$noShowMTDSql = "
								SELECT count(distinct lch.lead_id) AS totalCount 
								FROM ud_lead_call_history lch 
								LEFT JOIN ud_lists ls ON ls.id = lch.list_id 
								LEFT JOIN ud_calendar_appointment ca ON ca.id = lch.calendar_appointment_id 
								WHERE ca.title IN ('NO SHOW RESCHEDULE')
								AND lch.disposition = 'Appointment Set'
								AND lch.date_created >= '".date('Y-m-01 00:00:00')."' 
								AND ca.date_created <= '".date('Y-m-t 23:59:59')."'
								AND lch.customer_id = '".$customer->id."'
								AND ls.skill_id = '".$customerSkill->skill_id."' 
							";
							
							
							$command = Yii::app()->db->createCommand($noShowMTDSql);
							$noShowMTD = $command->queryRow();
							
							$appointmentSetCount = $appointmentSetMTD['totalCount'] + $noShowMTD['totalCount'] + $insertAppointmentMTD['totalCount'];
							
							if( $noShowMTD['totalCount'] > 3 )
							{
								$appointmentSetCount = $appointmentSetMTD['totalCount']-3;
							}
							else
							{
								$appointmentSetCount = $appointmentSetCount-$noShowMTD['totalCount'];
							}
						
							//get callable leads
							##NOTE: when updating this query, kindly also check the controllers/CronQueueViewerController - Line:203
							$remainingCallableCount = Lead::model()->count(array(
								'with' => array('list', 'list.skill'),
								'together' => true,
								'condition' => '
									list.customer_id = :customer_id AND list.status = 1 
									AND t.type=1 and t.status=1 AND t.number_of_dials < (skill.max_dials * 3) 
									AND (recertify_date != "0000-00-00" AND recertify_date IS NOT NULL 
									AND NOW() <= recertify_date)
									AND skill.id = :skill_id
								',
								'params' => array(
									':customer_id' => $customer->id,
									':skill_id' => $customerSkill->skill_id,
								),
							));
							
							//update line 42 && 122 && 154, if updating this query
							$leadRecycleCount = Lead::model()->count(array(
								'with' => array('list', 'list.skill'),
								'together' => true,
								'condition' => '
									t.customer_id = :customer_id 
									AND list.status = 1 
									AND t.type = 1 
									AND t.is_do_not_call = 0
									AND recycle_lead_call_history_id IS NOT NULL
									AND is_recycle_removed = 0
									AND (
										recycle_date IS NULL
										OR recycle_date = "0000-00-00"
										OR NOW() >= recycle_date 
									)
									AND ( 
										t.status = 3
										OR t.number_of_dials >= (skill.max_dials * 3)
									)
									AND skill.id = :skill_id
								',
								'params' => array(
									':customer_id' => $customer->id,
									':skill_id' => $customerSkill->skill_id,
								),
							));
							
							//update line 53 && 135 && 187, if updating this query
							$leadRecertifyCount = Lead::model()->count(array(
								'with' => array('list', 'list.skill'),
								'together' => true,
								// 'condition' => 'list.customer_id = :customer_id AND (recertify_date = "0000-00-00" || recertify_date IS NULL) AND NOW() >= recertify_date',
								'condition' => '
									t.customer_id = :customer_id 
									AND list.status = 1 
									AND t.type = 1
									AND t.status = 1 
									AND (
										t.recertify_date = "0000-00-00" 
										OR t.recertify_date IS NULL 
										OR NOW() >= t.recertify_date
									)
									AND skill.id = :skill_id
								',
								'params' => array(
									':customer_id' => $customer->id,
									':skill_id' => $customerSkill->skill_id,
								),
							));
							
							if( $contract->fulfillment_type == Contract::TYPE_FULFILLMENT_GOAL_VOLUME )
							{
								if( !empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME]) )
								{
									foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME] as $key => $subsidyLevel )
									{
										$customerSkillLevelArray = $customerSkill->getCustomerSkillLevelArray();
										$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;

										if( $customerSkillLevelArrayGroup != null )
										{							
											if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
											{
												$totalLeads += ( $subsidyLevel['goal'] * $customerSkillLevelArrayGroup->quantity );
											}
										}
									}
								}
								
								$customerExtras = CustomerExtra::model()->findAll(array(
									'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id AND status=1 AND year = :year AND month = :month',
									'params' => array(
										':customer_id' => $customerSkill->customer_id,
										':contract_id' => $customerSkill->contract_id,
										':skill_id' => $customerSkill->skill_id,
										':year' => date('Y'),
										':month' => date('m'),
									),
								));
								
								if( $customerExtras )
								{
									foreach( $customerExtras as $customerExtra )
									{
										$totalLeads += $customerExtra->quantity;
									}
								}
							}
							else
							{
								if( !empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME]) )
								{
									foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME] as $key => $subsidyLevel )
									{
										$customerSkillLevelArray = $customerSkill->getCustomerSkillLevelArray();
										
										$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;
										
										if( $customerSkillLevelArrayGroup != null )
										{
											if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
											{
												$totalLeads += ( $subsidyLevel['high'] * $customerSkillLevelArrayGroup->quantity );
											}
										}
									}
								}
								
								$customerExtras = CustomerExtra::model()->findAll(array(
									'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id AND status=1 AND year = :year AND month = :month',
									'params' => array(
										':customer_id' => $customerSkill->customer_id,
										':contract_id' => $customerSkill->contract_id,
										':skill_id' => $customerSkill->skill_id,
										':year' => date('Y'),
										':month' => date('m'),
									),
								));
								
								if( $customerExtras )
								{
									foreach( $customerExtras as $customerExtra )
									{
										$totalLeads += $customerExtra->quantity;
									}
								}
							}
							
							echo '<div class="row" style="margin-top:10px;">';
								echo '<div class="col-sm-1">';
									
									$skillStatus = 'Inactive';
									$skillIcon = 'ban';
									$skillClassLabel = 'warning';
									
									if( isset($customerSkill->contract) && isset($customerSkill->customer) && $customerSkill->customer->status == 1 && $customerSkill->customer->is_deleted == 0 && time() >= strtotime($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00' )
									{
										$skillStatus = 'Active';
										$skillIcon = 'check';
										$skillClassLabel = 'success';
									}
									
									if( $customerSkill->is_contract_hold == 1 )
									{
										if( !empty($customerSkill->is_contract_hold_start_date) && !empty($customerSkill->is_contract_hold_end_date) )
										{
											if( time() >= strtotime($customerSkill->is_contract_hold_start_date) && time() <= strtotime($customerSkill->is_contract_hold_end_date) )
											{
												$skillStatus = 'Active - On Hold';
												$skillIcon = 'ban';
												$skillClassLabel = 'warning';
											}
										}
									}
									
									if( $customerSkill->is_hold_for_billing == 1 )
									{
										$skillStatus = 'Active - Decline Hold';
										$skillIcon = 'ban';
										$skillClassLabel = 'danger';
									}
									
									if( !empty($customerSkill->end_month) && date('Y', strtotime($customerSkill->end_month)) >= 2015 )
									{
										if( time() >= strtotime($customerSkill->end_month) )
										{
											$skillStatus = 'Cancelled';
											$skillIcon = 'ban';
											$skillClassLabel = 'danger';
										}
									}
									
									echo '<span style="margin-top: 15px;" class="label label-'.$skillClassLabel.' label-lg arrowed-right">';										
										echo '<i class="fa fa-'.$skillIcon.'"></i> ' . $skillStatus;
									echo '</span>';
									
								echo '</div>';
								
								echo '<div class="col-sm-1">';
									echo '<div class="center">
										<select class="form-control" id="form-field-select-1">
											<option value="">- PRIORITY-</option>
											<option value="1">1st</option>
											<option value="2">2nd</option>
											<option value="3">3rd</option>
										</select>
									</div>';
								echo '</div>';
								
								echo '<div class="col-sm-3">';
									echo '<div class="infobox infobox-blue" style="border:none;">';
										echo '<div class="infobox-icon">';
											
										echo '</div>';

										echo '<div class="infobox-data">';											
											echo '<div class="infobox-content" style="width:100%;">'.$customerSkill->skill->skill_name.'</div>';
										echo '</div>';
									echo '</div>';
								echo '</div>';
								
								echo '<div class="col-sm-2">';
									echo '<div class="infobox infobox-blue" style="border:none;">';
										echo '<div class="infobox-icon">';
											echo '<i class="ace-icon fa fa-calendar"></i>';
										echo '</div>';

										echo '<div class="infobox-data">';
											echo '<span class="infobox-data-number">';	
												if( $contract->fulfillment_type == Contract::TYPE_FULFILLMENT_GOAL_VOLUME )
												{
													echo $appointmentSetCount.'/'.$totalLeads; 
												}
												else
												{
													echo $appointmentSetCount;
												}
											echo '</span>';
											echo '<div class="infobox-content">Appointments Set</div>';
										echo '</div>';
									echo '</div>';
								echo '</div>';
								
								$recertifyAndRecycleModalClassh = '';
								
								if( Yii::app()->user->account->checkPermission('customer_dashboard_recertify_recycle_names_button','visible') )
								{
									$recertifyAndRecycleModalClassh = ' modal-recyle-module';
								}
								
								echo '<div class="col-sm-2">';
									echo '<div class="infobox infobox-green'.$recertifyAndRecycleModalClassh.'" style="border:none; cursor:pointer;">';
										echo '<div class="infobox-icon">';
											echo '<i class="ace-icon fa fa-recycle"></i>';
										echo '</div>';

										echo '<div class="infobox-data">';
											// echo '<span class="infobox-data-number">'.($leadRecycleCount + $leadRecertifyCount).'</span>';
											// echo '<div class="infobox-content">Recycle Names</div>';
											echo '<span class="infobox-data-number">'.$leadRecertifyCount.'/'.$leadRecycleCount.'</span>';
											echo '<div class="infobox-content">Recertify/Recycle Names</div>';
										echo '</div>';
									echo '</div>';
								echo '</div>';
								
								echo '<div class="col-sm-2">';
									echo '<div class="infobox infobox-red" style="border:none;">';
										echo '<div class="infobox-icon">';
											echo '<i class="ace-icon fa fa-phone"></i>';
										echo '</div>';

										echo '<div class="infobox-data">';
											echo '<span class="infobox-data-number">'.$remainingCallableCount.'</span>';
											echo '<div class="infobox-content">Remaining Callable</div>';
										echo '</div>';
									echo '</div>';
								echo '</div>';
							echo '</div>';
						}
					}
				}
				else
				{
					echo '<tr><td colspan="2">No assigned skills found.</td></tr>';
				}
			?>
		</div>
	</div>
	
</div>

<br />
<br />

<div class="page-header">
	<h1>Action Center 
		<span class="action-center-count">
			<?php 
				echo ( $locationConflictDataProvider->totalItemCount + $scheduleConflictDataProvider->totalItemCount ) > 0 ? '<span class="red">('.( $locationConflictDataProvider->totalItemCount + $scheduleConflictDataProvider->totalItemCount ).')</span>' : ''; 
			?>
		</span>
	</h1>
</div>

<div class="accordion-style1 panel-group" id="accordion">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h4 class="panel-title">
				<a href="#collapseOne" data-parent="#accordion" data-toggle="collapse" class="accordion-toggle">
					<i data-icon-show="ace-icon fa fa-angle-right" data-icon-hide="ace-icon fa fa-angle-down" class="ace-icon fa fa-angle-down bigger-110"></i>
					&nbsp;
					Schedule Conflict 
					<span class="schedule-conflict-count">
						<?php echo $scheduleConflictDataProvider->totalItemCount > 0 ? '<span class="red">('.$scheduleConflictDataProvider->totalItemCount.')</span>' : ''; ?>
					</span>
				</a>
			</h4>
		</div>

		<div id="collapseOne" class="panel-collapse collapse in">
			<div class="panel-body no-padding">
				<?php 
					$this->widget('zii.widgets.CListView', array(
						'id'=>'scheduleConflictList',
						'dataProvider'=>$scheduleConflictDataProvider,
						'itemView'=>'_schedule_conflict_list',
						'template'=>'<ul class="item-list">{items}</ul>',
					)); 
				?>
			</div>
		</div>
	</div>

	<div class="panel panel-default">
		<div class="panel-heading">
			<h4 class="panel-title">
				<a href="#collapseTwo" data-parent="#accordion" data-toggle="collapse" class="accordion-toggle collapsed">
					<i data-icon-show="ace-icon fa fa-angle-right" data-icon-hide="ace-icon fa fa-angle-down" class="ace-icon fa fa-angle-right bigger-110"></i>
					&nbsp;
					Location Conflict 
					<span class="location-conflict-count">
						<?php echo $locationConflictDataProvider->totalItemCount > 0 ? '<span class="red">('.$locationConflictDataProvider->totalItemCount.')</span>' : ''; ?>
					</span>
				</a>
			</h4>
		</div>

		<div id="collapseTwo" class="panel-collapse collapse">
			<div class="panel-body no-padding">
				<?php 
					$this->widget('zii.widgets.CListView', array(
						'id'=>'locationConflictList',
						'dataProvider'=>$locationConflictDataProvider,
						'itemView'=>'_location_conflict_list',
						'template'=>'<ul class="item-list">{items}</ul>',
					)); 
				?>
			</div>
		</div>
	</div>

</div>
