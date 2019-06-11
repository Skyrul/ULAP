<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	
	$cs = Yii::app()->clientScript;
	
	$cs->registerCssFile($baseUrl.'/template_assets/css/jquery-ui.css');
	
	$cs->registerCssFile($baseUrl . '/template_assets/css/datepicker.min.css');
	
	$cs->registerScript(uniqid(), '
	
		function addLoader()
		{
			$("div.loader").fadeIn();
		}
		
		function removeLoader()
		{
			$("div.loader").hide();
		}
	
		$(document).ready( function(){
			
			$(".datepicker").datepicker({
				autoclose: true,
				todayHighlight: true,
				dateFormat: "yy-mm-dd",
			});
			
			$(document).on("change", ".start-date, .end-date", function(e) {
		
				e.preventDefault();
				
				var search_query = $(".customer-search-input").val();
				var start_date = $(".start-date").val();
				var end_date = $(".end-date").val();
				var sorter = $(".sorter").val();
				
				if( start_date !="" && end_date !="" )
				{
					$.fn.yiiListView.update("callHistoryList", { data: { search_query: search_query, start_date:start_date, end_date:end_date, sorter:sorter } });
				}
			});
			
			$(document).on("keyup", ".customer-search-input", function(e) {
		
				e.preventDefault();
				
				var search_query = $(".customer-search-input").val();
				var start_date = $(".start-date").val();
				var end_date = $(".end-date").val();
				var sorter = $(".sorter").val();
				
				$.fn.yiiListView.update("callHistoryList", { data: { search_query: search_query, start_date:start_date, end_date:end_date, sorter:sorter } });
			});
			
			$(document).on("change", ".sorter", function(e) {
		
				e.preventDefault();
				
				var search_query = $(".customer-search-input").val();
				var start_date = $(".start-date").val();
				var end_date = $(".end-date").val();
				var sorter = $(".sorter").val();
				
				$.fn.yiiListView.update("callHistoryList", { data: { search_query: search_query, start_date:start_date, end_date:end_date, sorter:sorter } });
			});
			
			
			$(document).on("change", ".agent-stat-start-date, .agent-stat-end-date", function(e) {
		
				e.preventDefault();
				
				var start_date = $(".agent-stat-start-date").val();
				var end_date = $(".agent-stat-end-date").val();
				var agent_account_id = "'.$account->id.'";
				
				// $.fn.yiiListView.update("agentStatList", { data: { agent_stat_start_date:start_date, agent_stat_end_date:end_date } });
				
				agentStatsIsProcessing = false;
				
				if( !agentStatsIsProcessing )
				{
					agentStatsIsProcessing = true;
					
					$.ajax({
						url: yii.urls.absoluteUrl + "/hr/accountUser/ajaxStats",
						type: "post",
						dataType: "json",
						data: { 
							"ajax":1, 
							"agent_stat_start_date": start_date, 
							"agent_stat_end_date": end_date, 
							"agent_account_id": agent_account_id 
						},
						success: function(response){
							
							if( response.html != "" )
							{
								$(".agent-stats-tbl > tbody").html(response.html);
							}
							
							agentStatsIsProcessing = false;
						}
					});
				}
				
			});
			
			$(document).on("click", ".export-excel-btn", function(){
		
				var id = "'.$account->id.'";
				var search_query = $(".customer-search-input").val();
				var start_date = $(".start-date").val();
				var end_date = $(".end-date").val();
				var sorter = $(".sorter").val();

				window.location = yii.urls.absoluteUrl + "/hr/accountUser/export?id=" + id + "&search_query=" + search_query + "&start_date=" + start_date + "&end_date=" + end_date + "&sorter=" + sorter;
			});
		});
		
		$(document).on("click", ".lead-history-link", function(){
			
			var id = $(this).prop("id");
			var this_button = $(this);
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/hr/accountUser/ajaxLeadHistory",
				type: "post",
				dataType: "json",
				data: {
						"ajax":1, "id":id
				},
				success: function(response) {
					
					if(response.status  == "success")
					{
						modal = response.html;
					}
					
					var modal = $(modal).appendTo("body");
					
					modal.modal("show").on("hidden.bs.modal", function(){
						modal.remove();
					});
				}
			});
		});
	
	', CClientScript::POS_END);
?>

<div class="tabbable tabs-left">

	<ul class="nav nav-tabs">
	
		<?php 
			if( Yii::app()->user->account->checkPermission('employees_employee_profile_tab','visible') && Yii::app()->user->account->checkPermission('employees_employee_profile_tab','only_for_direct_reports') )
			{
				echo '<li class="">';
					
					if( $account->account_type_id == Account::TYPE_HOSTDIAL_AGENT )
					{
						echo CHtml::link('Host Profile', array('accountUser/employeeDetails', 'id'=>$account->id));
					}
					else
					{	
						echo CHtml::link('Employee Profile', array('accountUser/employeeDetails', 'id'=>$account->id));
					}
				echo '</li>';
			}
		?>
		
		<?php if( Yii::app()->user->account->checkPermission('employees_employee_file_tab','visible') && Yii::app()->user->account->checkPermission('employees_employee_file_tab','only_for_direct_reports', $account->id) ){ ?>
			<li><?php echo CHtml::link('Employee File', array('employeeFile', 'id'=>$account->id)); ?></li>
		<?php } ?>
		
		<?php if( Yii::app()->user->account->checkPermission('employees_time_keeping_tab','visible') && Yii::app()->user->account->checkPermission('employees_time_keeping_tab','only_for_direct_reports', $account->id) ){ ?>
			<li><?php echo CHtml::link('Time Keeping', array('timeKeeping', 'id'=>$account->id)); ?></li>
		<?php } ?>
		
		<?php if( Yii::app()->user->account->checkPermission('employees_assigments_tab','visible') && Yii::app()->user->account->checkPermission('employees_assigments_tab','only_for_direct_reports', $account->id) ){ ?>
			<li><?php echo CHtml::link('Assignments', array('assignments', 'id'=>$account->id)); ?></li>
		<?php } ?>
		
		<?php if( Yii::app()->user->account->checkPermission('employees_performance_tab','visible') && Yii::app()->user->account->checkPermission('employees_performance_tab','only_for_direct_reports', $account->id) ){ ?>
			<li class="active"><?php echo CHtml::link('Performance', array('performance', 'id'=>$account->id)); ?></li>
		<?php }?>
		
	</ul>
	
	<div class="tab-content" style="overflow:hidden;">
		<?php
			foreach(Yii::app()->user->getFlashes() as $key => $message) {
				echo '<div class="alert alert-' . $key . '"><button data-dismiss="alert" class="close" type="button"><i class="ace-icon fa fa-times"></i></button> ' . $message . "</div>\n";
			}
		?>
		
		<div class="row">
			<div class="col-sm-12">
				<div class="col-sm-5">
					<div class="row">
						<div class="col-sm-3 col-sm-offset-2">
							<?php 
								if($accountUser->getImage())
								{
									echo CHtml::image($accountUser->getImage(), '', array('class'=>'img-responsive'));
								}
								else
								{
									echo '<div style="height:180px; border:1px dashed #ccc; text-align:center; line-height: 180px;">No Image.</div>';
								}
							?>
						</div>
						<div class="col-sm-6 text-center">
							<h3><?php echo $accountUser->getFullName(); ?></h3>
						</div>
					</div>
					
					<div class="hr hr-18 hr-double dotted"></div>
					
					<?php if( Yii::app()->user->account->checkPermission('employees_performance_agent_stats','visible') && Yii::app()->user->account->checkPermission('employees_performance_agent_stats','only_for_direct_reports', $account->id) ){ ?>
					
					<div class="row">
						<div class="col-sm-12" style="line-height:30px;">

							<h2 class="lighter blue">Agent Stats </h2>

							<div class="widget-box" style="min-height:280px;">
								<div class="widget-body">
									<div class="widget-main">
									
										<div class="row">
											<div class="col-sm-12">
												<input type="text" class="agent-stat-start-date datepicker" placeholder="Start Date" style="height:35px;">
												<input type="text" class="agent-stat-end-date datepicker" placeholder="End Date" style="height:35px;">
											</div>
										</div>
										
										<div class="hr hr-18 hr-double dotted"></div>
										
										<?php 
											$skillsSql = '
												SELECT ls.`skill_id`, sk.`skill_name`
												FROM ud_lead_call_history lch 
												LEFT JOIN ud_lists ls ON ls.`id` = lch.`list_id`
												LEFT JOIN ud_skill sk ON sk.`id` = ls.`skill_id`
												WHERE lch.`agent_account_id`="'.$account->id.'"
												GROUP BY ls.`skill_id`
											';

											$skills = Yii::app()->db->createCommand($skillsSql)->queryAll();
										?>
										
										<table class="table table-striped table-hover table-condensed agent-stats-tbl">
										
											<thead>
												<th>Skill Name</th>
												<th>Hours</th>
												<th>Appts</th>
												<th>APH</th>
												<th>Dials</th>
												<th>DPH</th>
												<th>Conv</th>
											</thead>
											
											<?php 
												if( $skills )
												{
													foreach( $skills as $skill )
													{
														$addCondition = '';
														$addCondition2 = '';
														
														if( !empty($_GET['agent_stat_start_date']) && !empty($_GET['agent_stat_end_date']) )
														{
															$addCondition .= ' AND DATE(alt.time_in) >= "'.date('Y-m-d 00:00:00', strtotime($_GET['agent_stat_start_date'])).'"';
															$addCondition .= ' AND DATE(alt.time_in) <= "'.date('Y-m-d 23:59:59', strtotime($_GET['agent_stat_end_date'])).'"';
														}
														else
														{
															$addCondition .= ' AND DATE(alt.time_in) >= "'.date('Y-m-d 00:00:00').'"';
															$addCondition .= ' AND DATE(alt.time_in) <= "'.date('Y-m-d 23:59:59').'"';
														}
														
														if( !empty($_GET['agent_stat_start_date']) && !empty($_GET['agent_stat_end_date']) )
														{
															$addCondition2 .= ' AND DATE(lch.start_call_time) >= "'.date('Y-m-d 00:00:00', strtotime($_GET['agent_stat_start_date'])).'"';
															$addCondition2 .= ' AND DATE(lch.start_call_time) <= "'.date('Y-m-d 23:59:59', strtotime($_GET['agent_stat_end_date'])).'"';
														}
														else
														{
															$addCondition2 .= ' AND DATE(lch.start_call_time) >= "'.date('Y-m-d 00:00:00').'"';
															$addCondition2 .= ' AND DATE(lch.start_call_time) <= "'.date('Y-m-d 23:59:59').'"';	
														}

														$sql = "
															SELECT
															(
																SELECT SUM(
																	CASE WHEN time_out IS NOT NULL THEN TIME_TO_SEC(TIMEDIFF(time_out, time_in))/3600
																		ELSE TIME_TO_SEC(TIMEDIFF(DATE_SUB(NOW(), INTERVAL 1 HOUR), time_in))/3600 
																	END
																)
																FROM ud_account_login_tracker alt
																WHERE alt.account_id = a.`id`
																AND alt.status !=4
																".$addCondition."
															) AS total_hours,
															(
																SELECT COUNT(lch.id) 
																FROM ud_lead_call_history lch
																LEFT JOIN ud_lists uls ON uls.id = lch.list_id
																WHERE lch.agent_account_id = a.`id`															
																AND uls.skill_id ='".$skill['skill_id']."'
																AND lch.status != 4
																".$addCondition2."
															) AS dials,
															(
																SELECT COUNT(lch.id) 
																FROM ud_lead_call_history lch
																LEFT JOIN ud_lists uls ON uls.id = lch.list_id
																WHERE lch.agent_account_id = a.`id`
																AND uls.skill_id ='".$skill['skill_id']."'
																AND lch.disposition='Appointment Set'
																AND lch.status != 4
																AND lch.is_skill_child=0
																".$addCondition2."
															) AS appointments,
															(
																SELECT COUNT(lch.id) 
																FROM ud_lead_call_history lch
																LEFT JOIN ud_lists uls ON uls.id = lch.list_id
																LEFT JOIN ud_skill_disposition sd ON sd.id = lch.disposition_id 
																WHERE lch.agent_account_id = a.`id` 
																AND uls.skill_id ='".$skill['skill_id']."'
																AND sd.is_voice_contact = 1
																AND sd.id IS NOT NULL
																AND lch.status != 4
																".$addCondition2."
															) AS voice_contacts
															FROM ud_account a
															WHERE a.id = '".$account->id."'
														";
														
														$stats = Yii::app()->db->createCommand($sql)->queryRow();
														
														echo '<tr>';
														
															echo '<td>'.$skill['skill_name'].'</td>';
															
															echo '<td class="center">';
																if( $stats['dials'] > 0 )
																{
																	echo round($stats['total_hours'], 2);
																}
																else
																{
																	echo 0;
																}
															echo '</td>';
															
															echo '<td class="center">'.$stats['appointments'].'</td>';
															
															echo '<td>';
																if( $stats['appointments'] > 0 && $stats['total_hours'] > 0 )
																{
																	echo round($stats['appointments'] / $stats['total_hours'], 2);
																}
																else
																{
																	
																	echo 0;
																}
															echo '</td>';
															
															
															echo '<td class="center">'.$stats['dials'].'</td>';
															
															echo '<td class="center">';
										
																if( $stats['dials'] > 0 && $stats['total_hours'] > 0 )
																{
																	echo round($stats['dials'] / $stats['total_hours'], 2);
																}
																else
																{
																	
																	echo 0;
																}
															
															echo '</td>';
															
															echo '<td class="center">';
										
																// echo 'appointments: ' . $stats['appointments'];
																// echo '<br>';
																// echo 'voice_contacts: ' . $stats['voice_contacts'];
																
																// echo '<br><br>';
																
										
																if( $stats['appointments'] > 0 && $stats['voice_contacts'] > 0 )
																{
																	echo round($stats['appointments'] / $stats['voice_contacts'], 2) * 100 . '%';
																}
																else
																{
																	
																	echo '0%';
																}
															
															echo '</td>';
															
														echo '</tr>';
													}
												}
											?>
										
										</table>
										
									</div>
								</div>
							</div>
						</div>
					</div>
					<?php } ?>
				</div>
				
				<div class="col-sm-7">
					
					<?php if( Yii::app()->user->account->checkPermission('employees_performance_call_agent_history','visible') && Yii::app()->user->account->checkPermission('employees_performance_call_agent_history','only_for_direct_reports', $account->id) ){ ?>
					
					<h2 class="lighter blue">
						Call Agent History 
						
						<span class="pull-right">
							<button class="btn btn-yellow btn-sm export-excel-btn"><i class="fa fa-file-excel-o"></i> Export to Excel</button> 
						</span>
					</h2>
					
					<div class="widget-box" style="min-height:500px;">
						<div class="widget-body">
							<div class="widget-main">
							
								<div class="row">
									<div class="col-sm-12">
										<div class="col-sm-6">
											<input type="text" class="start-date datepicker" placeholder="Start Date">
											<input type="text" class="end-date datepicker" placeholder="End Date">
										</div>
										
										<div class="col-sm-4">
											<div id="nav-search" class="nav-search" style="position:inherit; margin-top:2px; right:0; ">
												<span class="input-icon">
													<input type="text" autocomplete="off" class="nav-search-input customer-search-input" placeholder="Search Leads..." style="width:200px;">
													<i class="ace-icon fa fa-search nav-search-icon"></i>
												</span>
											</div>
										</div>
										
										<div class="col-sm-2">
											<select class="sorter" style="width:100%;">
												<option value="date_time">Date/Time</option>
												<option value="skill">Skill</option>
												<option value="customer_name">Customer Name</option>
												<option value="disposition">Disposition</option>
											</select>
										</div>
									</div>
								</div>
								
								<div class="hr hr-18 hr-double dotted"></div>
								
								<div class="center loader alert alert-block alert-info" style="display:none;">Loading Please Wait <i class="fa fa-cog fa-spin fa-1x fa-fw"></i></div>
									
								<?php 
									$this->widget('zii.widgets.CListView', array(
										'id'=>'callHistoryList',
										'dataProvider'=>$dataProvider,
										'itemView'=>'_call_agent_history',
										'template'=>'<table class="table table-striped table-hover table-condensed">{items}</table> <div class="center loader alert alert-block alert-info" style="display:none;">Loading Please Wait <i class="fa fa-cog fa-spin fa-1x fa-fw"></i></div> <div class="text-center">{pager}</div>',
										'pagerCssClass' => 'pagination',
										'beforeAjaxUpdate'=>'addLoader',
										'afterAjaxUpdate'=>'removeLoader',
										'pager' => array(
											'header' => '',
										),
									)); 
								?>	
								
								
								
							</div>
						</div>
					</div>
				
					<?php } ?>
				</div>
				
			</div>
		</div>
	</div>
</div>