<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	
	$cs = Yii::app()->clientScript;
	
	$cs->registerCssFile($baseUrl.'/template_assets/css/jquery-ui.css');
	
	$cs->registerScript(uniqid(), '
	
		$(document).ready( function(){
			
			$(".datepicker").datepicker({
				autoclose: true,
				todayHighlight: true
			});
			
			
			$(document).on("click", ".generate-report-btn", function(){
				
				var export_type = $(".select-change-export-type").val();
				
				window.open( $(this).attr("url") + "?exportType=" + export_type, "_blank", "toolbar=0,location=0,menubar=0" );
				
			});
			
		});
				
	', CClientScript::POS_END);
?>

<?php 
	$this->widget("application.components.HostDialSideMenu",array(
		'active'=> 'reports',
		'customer' => $customer,
	));
?>

<?php 		
	$dateFilterStart = isset($_POST['dateFilterStart']) ? $_POST['dateFilterStart'] : '';
	$dateFilterEnd = isset($_POST['dateFilterEnd']) ? $_POST['dateFilterEnd'] : '';
	
	$dateFilterStart2 = isset($_POST['dateFilterStart2']) ? $_POST['dateFilterStart2'] : '';
	$dateFilterEnd2 = isset($_POST['dateFilterEnd2']) ? $_POST['dateFilterEnd2'] : '';
?>

<div class="page-header">
	<h1>Reports</h1>
</div>
	
<div class="row">

	<div class="col-sm-12">
	
		<div class="col-sm-7 widget-container-col">
			<div class="widget-box ui-sortable-handle">
				<div class="widget-header">
					<h5 class="widget-title">Host Dialer Report</h5>

					<div class="widget-toolbar no-border">
						<div class="widget-menu">
						</div>
					</div>
				</div>

				<div class="widget-body">
					<div class="widget-main">
						<div class="row">
							<form action="" method="post">
							
								<input type="hidden" name="page" value="agentPerformanceLite">
							
								<div class="col-sm-10">
									<div class="col-sm-4">
										<span style="margin: 8px 0 0 -20px;">Date: </span>

										<input type="text" name="dateFilterStart" class="datepicker" value="<?php echo isset($_POST['dateFilterStart']) ? $_POST['dateFilterStart'] : date('m/d/Y'); ?>" placeholder="From" style="width:100px;">

										<input type="text" name="dateFilterStartTime" value="<?php echo isset($_POST['dateFilterStartTime']) ? $_POST['dateFilterStartTime'] : '8:00 AM'; ?>" style="width:75px;">
									</div>
									
									<div class="col-sm-6" style="margin-left:-40px;">
										<input type="text" name="dateFilterEnd" class="datepicker" value="<?php echo isset($_POST['dateFilterEnd']) ? $_POST['dateFilterEnd'] : date('m/d/Y'); ?>" placeholder="To" style="width:100px;">

										<input type="text" name="dateFilterEndTime" value="<?php echo isset($_POST['dateFilterEndTime']) ? $_POST['dateFilterEndTime'] : date('g:i A', strtotime('-1 hour')); ?>" style="width:75px;">
									
										<button type="submit" class="btn btn-xs btn-primary">Execute <i class="fa fa-arrow-right"></i> </button>
									</div>
								</div>

								<div class="col-sm-2 text-right">
									<?php 
										echo CHtml::link('<i class="fa fa-file-excel-o"></i> Export to Excel', array(
											'/reports/export', 
											'page'=>'agentPerformanceLite', 
											'customer_id'=>$customer->id, 
											'dateFilterStart'=>$dateFilterStart, 
											'dateFilterEnd'=>$dateFilterEnd,
											'dateFilterStartTime'=>isset($_POST['dateFilterStartTime']) ? $_POST['dateFilterStartTime'] : '',
											'dateFilterEndTime'=>isset($_POST['dateFilterEndTime']) ? $_POST['dateFilterEndTime'] : '',
										), array('class'=>'btn btn-yellow btn-sm')
										); ?>
								</div>
							
							</form>
						</div>
						
						<div class="space-12"></div>
						
						<?php 
							$agents = array();
					
							if( $dateFilterStart != "" && $dateFilterEnd != "" && isset($_POST['page']) && $_POST['page'] == 'agentPerformanceLite' )
							{
								$dateFilterStart = date('Y-m-d 00:00:00', strtotime($dateFilterStart));
								$dateFilterEnd = date('Y-m-d 23:59:59', strtotime($dateFilterEnd));
								
								if( !empty($_POST['dateFilterStartTime']) )
								{
									$dateFilterStart = date('Y-m-d', strtotime($dateFilterStart)).' '.date('H:i:s', strtotime($_POST['dateFilterStartTime']));
									
									// $dateFilterStartTime = date('H:i:s', strtotime('+1 hour', strtotime($_POST['dateFilterStartTime'])));
									
									// $dateFilterStart = date('Y-m-d', strtotime($dateFilterStart)).' '.$dateFilterStartTime;
								}
								
								if( !empty($_POST['dateFilterEndTime']) )
								{
									$dateFilterEndTime = date('H:i:s', strtotime('+1 hour', strtotime($_POST['dateFilterEndTime'])));
									
									$dateFilterEnd = date('Y-m-d', strtotime($dateFilterEnd)).' '.$dateFilterEndTime;
								}
								
								$sql = "
									SELECT a.id as agent_id, cs.`staff_name` AS agent_name, a.status AS agent_status,
									(
										SELECT SUM(
											CASE WHEN time_out IS NOT NULL THEN TIME_TO_SEC(TIMEDIFF(time_out, time_in))/3600
												ELSE TIME_TO_SEC(TIMEDIFF(DATE_SUB(NOW(), INTERVAL 1 HOUR), time_in))/3600 
											END
										)
										FROM ud_account_login_tracker alt
										WHERE alt.account_id = a.`id`
										AND alt.time_in >= '".$dateFilterStart."' 
										AND alt.time_in <= '".$dateFilterEnd."'
										AND alt.status !=4 
									) AS total_hours, 
									(
										SELECT COUNT(lch.id) 
										FROM ud_lead_call_history lch
										LEFT JOIN ud_lists uls ON uls.id = lch.list_id
										WHERE lch.agent_account_id = a.`id`
										AND lch.start_call_time >= '".$dateFilterStart."' 
										AND lch.start_call_time <= '".$dateFilterEnd."' 
										AND uls.skill_id IN (53)
										AND lch.status != 4
										AND lch.customer_id = '".$customer->id."'
									) AS dials
									FROM ud_account a
									LEFT JOIN ud_account_user au ON au.`account_id` = a.`id`
									LEFT JOIN ud_customer_office_staff cs ON cs.`account_id` = a.`id`
									WHERE a.`account_type_id` IN (15)
									ORDER BY au.last_name ASC
								";

								$connection = Yii::app()->db;
								$command = $connection->createCommand($sql);
								$agents = $command->queryAll();
								
								echo '';
							
								echo '
									<table id="leadsTbl" class="table table-striped table-bordered table-condensed table-hover">
										<thead>
											<th>#</th>
											<th>Agent Name</th>
											<th>Status</th>
											<th class="center">Total Hours</th>
											<th class="center">Dials</th>
											<th class="center">Dials/Hour</th>
										</thead>';
								
								if( $agents )
								{
									$ctr = 1;
									$totalDials = 0;
									$totalAppointments = 0;
									$totalHours = 0;
									
									foreach( $agents as $agent )
									{
										if( $agent['total_hours'] != '' )
										{
											$totalDials += $agent['dials'];
											$totalAppointments += $agent['appointments'];
											$totalHours += round($agent['total_hours'], 2);
											
											echo '<tr>';
												echo '<td>'.$ctr.'</td>';
												
												echo '<td>'.CHtml::link($agent['agent_name'], array('/hr/accountUser/employeeProfile', 'id'=>$agent['agent_id'])).'</td>';
												
												echo '<td>'; 
													
													if( $agent['agent_status'] == 1 )
													{
														echo 'Active';
													}
													else
													{
														echo 'Inactive';
													}
													
												echo '</td>';
												
												echo '<td class="center">'.round($agent['total_hours'], 2).'</td>';
												
												echo '<td class="center">'.$agent['dials'].'</td>';
												
												echo '<td class="center">';
												
													if( $agent['dials'] > 0 && $agent['total_hours'] > 0 )
													{
														echo round($agent['dials'] / $agent['total_hours'], 2);
													}
													else
													{
														
														echo 0;
													}
												
												echo '</td>';
												 
											echo '</tr>';
											
											$ctr++;
										}
									}
									
									echo '<tr><td colspan="8"></td></tr>';
									
									echo '<tr>';
										echo '<th colspan="3">TOTAL</th>';
										echo '<th class="center">'.round($totalHours, 2).'</th>';
										echo '<th class="center">'.number_format($totalDials).'</th>';
										echo '<th class="center">'.round($totalDials/$totalHours, 2).'</th>';
									echo '</tr>';
								}
								else
								{
									echo '<tr><td colspan="4">No results found.</td></tr>';
								}
								
								echo '</table>';
							}
						?>
					</div>
				</div>
			</div>
			
			<div class="space-12"></div>
			
			<div class="widget-box ui-sortable-handle">
				<div class="widget-header">
					<h5 class="widget-title">Type Report</h5>

					<div class="widget-toolbar no-border">
						<div class="widget-menu">
						</div>
					</div>
				</div>

				<div class="widget-body">
					<div class="widget-main">
						<div class="row">
							<form action="" method="post">
								<input type="hidden" name="page" value="genericSkill">
								
								<div class="col-sm-3">
									<?php 
										$skills = Skill::model()->findAll(array(
											'condition' => 'is_deleted=0 AND id IN (53)',
										));
										
										$skillOptions = CHtml::listData( $skills, 'id', 'skill_name');
										
										echo CHtml::dropDownList('skillId', isset($_POST['skillId']) ? $_POST['skillId'] : '', $skillOptions, array('prompt'=>'- Select -')); 
									?>
								</div>
							
								<div class="col-sm-6">
									
									Date:
									<input type="text" name="dateFilterStart2" class="datepicker" value="<?php echo $dateFilterStart2; ?>" placeholder="From">
									<input type="text" name="dateFilterEnd2" class="datepicker" value="<?php echo $dateFilterEnd2; ?>" placeholder="To">
									
									<button type="submit" class="btn btn-xs btn-primary">Execute <i class="fa fa-arrow-right"></i> </button>
								</div>
							</form>
							
							<div class="col-sm-3 text-right">
								<?php echo CHtml::link('<i class="fa fa-file-excel-o"></i> Export to Excel', array('/reports/export', 'customer_id'=>$customer->id, 'page'=>'genericSkill', 'selectedSkills'=>isset($_POST['skillId']) ? $_POST['skillId'] : '', 'contractId'=>$contractId, 'dateFilterStart'=>$dateFilterStart, 'dateFilterEnd'=>$dateFilterEnd), array('class'=>'btn btn-yellow btn-sm')); ?>
							</div>
						</div>
						
						<div class="space-12"></div>
						
						<?php 
							$customers = array();
					
							if( !empty($_POST['skillId']) && $dateFilterStart2 != "" && $dateFilterEnd2 != "" && isset($_POST['page']) && $_POST['page'] == 'genericSkill' )
							{
								$skillIds[] = $_POST['skillId']; 

								$sql = "
									SELECT 
										co.company_name as company_name,
										CONCAT (c.firstname, ' ', c.lastname) AS customer_name,
										lch.lead_phone_number AS lead_phone,
										ld.first_name AS lead_first_name, 
										ld.last_name AS lead_last_name,
										ld.partner_first_name AS partner_first_name,
										ld.partner_last_name AS partner_last_name,
										ld.email_address AS lead_email,
										lch.is_skill_child,
										lch.disposition,
										lch.disposition_detail,
										lch.agent_note,
										CONCAT(au.first_name, ' ', au.last_name) AS agent,
										lch.start_call_time as call_date, 
										lch.callback_time as callback_date
									FROM ud_lead_call_history lch 
									LEFT JOIN ud_customer c ON lch.customer_id = c.id
									LEFT JOIN ud_company co ON co.id = c.company_id
									LEFT JOIN ud_lists ls ON ls.id = lch.list_id
									LEFT JOIN ud_lead ld ON ld.id = lch.lead_id
									LEFT JOIN ud_account_user au ON au.account_id = lch.agent_account_id
									WHERE ls.skill_id IN(".implode(', ', $skillIds).")
									AND lch.disposition IS NOT NULL 
									AND lch.start_call_time >= '".date('Y-m-d 00:00:00', strtotime($dateFilterStart2))."' 
									AND lch.start_call_time <= '".date('Y-m-d 23:59:59', strtotime($dateFilterEnd2))."' 
									AND lch.status !=4 
									AND lch.customer_id = '".$customer->id."'
									ORDER BY lch.start_call_time DESC
								";
								
								// echo '<br><br>';
								// echo $sql;
								// echo '<br><br>';
								
								$connection = Yii::app()->db;
								$command = $connection->createCommand($sql);
								$calls = $command->queryAll();
								
								// echo '<pre>';
									// print_r($calls);
								// echo '</pre>';
								
								// echo '<br><br>';
							
								echo '
									<table id="leadsTbl" class="table table-striped table-bordered table-condensed table-hover">
										<thead>
											<th class="center">#</th>
											<th class="center">Company</th>
											<th class="center">Customer</th>
											<th class="center">Lead Phone</th>
											<th class="center">Lead First</th>
											<th class="center">Lead Last</th>
											<th class="center">Partner First</th>
											<th class="center">Partner Last</th>
											<th class="center">Lead Email Address</th> 
											<th class="center">Date/Time</th>
											<th class="center">Skill</th>
											<th class="center">Disposition</th>
											<th class="center">Sub Disposition</th>
											<th class="center">Callback Date/Time</th>
											<th class="center">Disposition Note</th>
											<th class="center">Agent</th>
										</thead>';
								
								if( $calls )
								{
									$ctr = 1;
									
									foreach( $calls as $call )
									{
										$callDate = new DateTime($call['call_date'], new DateTimeZone('America/Chicago'));
										$callDate->setTimezone(new DateTimeZone('America/Denver'));
										
										$callBackDate = new DateTime($call['callback_date'], new DateTimeZone('America/Chicago'));
										$callBackDate->setTimezone(new DateTimeZone('America/Denver'));
										
										echo '<tr>';							
											echo '<td>'.$ctr.'</td>';					
											echo '<td>'.$call['company_name'].'</td>';					
											echo '<td>'.$call['customer_name'].'</td>';					
											echo '<td>'.$call['lead_phone'].'</td>';					
											echo '<td>'.$call['lead_first_name'].'</td>';					
											echo '<td>'.$call['lead_last_name'].'</td>';					
											echo '<td>'.$call['partner_first_name'].'</td>';					
											echo '<td>'.$call['partner_last_name'].'</td>';
											echo '<td>'.$call['lead_email'].'</td>';					
											echo '<td>'.$callDate->format('m/d/Y g:i A').'</td>';
											
											if( $call['is_skill_child'] == 1 )
											{
												echo '<td>Child</td>';	
											}
											else
											{
												echo '<td>Parent</td>';	
											}
											
											echo '<td>'.$call['disposition'].'</td>';		
											
											echo '<td>'.$call['disposition_detail'].'</td>';		
											
											if( in_array($call['disposition'], array('Call Back', 'Callback', 'Call Back - Confirm')) )
											{
												echo '<td>'.$callBackDate->format('m/d/Y g:i A').'</td>';	
											}
											else
											{
												echo '<td></td>';
											}
											
											echo '<td>'.$call['agent_note'].'</td>';					
											echo '<td>'.$call['agent'].'</td>';					
											 
										echo '</tr>';
										
										$ctr++;
									}
								}
								else
								{
									echo '<tr><td colspan="16">No results found.</td></tr>';
								}
								
								echo '</table>';
							}
						?>
					</div>
				</div>
			</div>
		</div>
		
		<div class="col-sm-5 widget-container-col">
		
			<div class="widget-box ui-sortable-handle">
				<div class="widget-header">
					<h5 class="widget-title">Current Period</h5>

					<div class="widget-toolbar no-border">
						<div class="widget-menu">
						</div>
					</div>
				</div>

				<div class="widget-body">
					<div class="widget-main">
						<label>Export Type: </label>
						<?php echo CHtml::link('<i class="fa fa-file"></i> Generate Report', 'javascript:void(0);', array('url'=>$this->createUrl('generateCustomerReport', array('customer_id'=>$customer->id)), 'class'=>'btn btn-success btn-xs generate-report-btn')); ?>
						
					</div>
				</div>
			</div>
			
			<div class="space-12"></div>
		
			<div class="widget-box ui-sortable-handle">
				<div class="widget-header">
					<h5 class="widget-title">Monthly Service Report</h5>

					<div class="widget-toolbar no-border">
						<div class="widget-menu">
						</div>
					</div>
				</div>

				<div class="widget-body">
					<div class="widget-main">
						<?php
							foreach( range(2015, date('Y')) as $year )
							{
								$latestCall = LeadCallHistory::model()->count(array(
									'condition' => 'customer_id = :customer_id AND YEAR(date_created) = :year', 
									'params' => array(
										':customer_id' => $customer->id,
										':year' => $year,
									),
								));
								
								if( $latestCall > 0 )
								{
									echo '<div class="page-header">';
										echo '<h5 class="blue">'.$year.'</h5>';
									echo '</div>';
									
									echo '<div class="row">';
										echo '<div class="col-sm-12">';
										
											foreach( range(1, 12) as $month )
											{
												$latestCall = LeadCallHistory::model()->count(array(
													'condition' => 'customer_id = :customer_id AND YEAR(date_created) = :year AND MONTH(date_created) = :month', 
													'params' => array(
														':customer_id' => $customer->id,
														':year' => $year,
														':month' => $month
													),
												));
												
												if( $latestCall > 0 )
												{
													$monthName = date("F", mktime(0, 0, 0, $month, 10));
													
													echo CHtml::link( ucfirst($monthName), 'javascript:void(0);', array('url'=>$this->createUrl('generateCustomerMonthlyReport', array('customer_id'=>$customer->id, 'year'=>$year, 'month'=>$month)), 'class'=>'btn btn-success btn-xs generate-report-btn'));
													echo '&nbsp;';
												}
											}
											
										echo '</div>';
									echo '</div>';
									
									echo '<div class="hr hr-18 hr-double dotted"></div>';
								}
							}
						?>
					</div>
				</div>
			</div>
			
			<div class="space-12"></div>
			
			<div class="widget-box ui-sortable-handle">
				<div class="widget-header">
					<h5 class="widget-title">Monthly Wrong Number & Do Not Call Report</h5>

					<div class="widget-toolbar no-border">
						<div class="widget-menu"> 
							<?php echo CHtml::dropDownList('exportType', '', array('pdf'=>'pdf', 'excel'=>'excel'), array('class'=>'select-change-export-type')); ?>
						</div>
					</div>
				</div>

				<div class="widget-body">
					<div class="widget-main">
						<div class="row">
							<div class="col-sm-12">
								<?php 
									echo CHtml::link( 'Inception to date', 'javascript:void(0);', array('url'=>$this->createUrl('generateCustomerAllWnDncReport', array('customer_id'=>$customer->id)), 'class'=>'btn btn-success btn-xs generate-report-btn'));
								?>
							</div>
						</div>
						
						<div class="hr hr-18 hr-double dotted"></div>
						
						<?php
							foreach( range(2015, date('Y')) as $year )
							{
								$latestCall = LeadCallHistory::model()->count(array(
									'condition' => 'customer_id = :customer_id AND YEAR(date_created) = :year', 
									'params' => array(
										':customer_id' => $customer->id,
										':year' => $year,
									),
								));
								
								if( $latestCall > 0 )
								{
									echo '<div class="page-header">';
										echo '<h5 class="blue">'.$year.'</h5>';
									echo '</div>';
									
									echo '<div class="row">';
										echo '<div class="col-sm-12">';
										
											foreach( range(1, 12) as $month )
											{
												$latestCall = LeadCallHistory::model()->count(array(
													'condition' => 'customer_id = :customer_id AND YEAR(date_created) = :year AND MONTH(date_created) = :month', 
													'params' => array(
														':customer_id' => $customer->id,
														':year' => $year,
														':month' => $month
													),
												));
												
												if( $latestCall > 0 )
												{
													$monthName = date("F", mktime(0, 0, 0, $month, 10));
													
													echo CHtml::link( ucfirst($monthName), 'javascript:void(0);', array('url'=>$this->createUrl('generateCustomerMonthlyWnDncReport', array('customer_id'=>$customer->id, 'year'=>$year, 'month'=>$month)), 'class'=>'btn btn-success btn-xs generate-report-btn'));
													echo '&nbsp;';
												}
											}
											
										echo '</div>';
									echo '</div>';
									
									echo '<div class="hr hr-18 hr-double dotted"></div>';
								}
							}
						?>
					</div>
				</div>
			</div>
		</div>
		
	</div>

</div>