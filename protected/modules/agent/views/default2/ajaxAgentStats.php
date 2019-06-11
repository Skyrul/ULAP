<?php 
	//for testing
	// $authAccount = Account::model()->findByPk(116);

	$authAccount = Yii::app()->user->account;
	$accountUser = $authAccount->accountUser;
	
	//Policy Review Campaign
	$campaign = Campaign::model()->find(array(
		'condition' => 'id=2 AND status=1',
	)); 
	
	$todaySql = "
		SELECT
		(
			SELECT SUM(
				CASE WHEN time_out IS NOT NULL THEN TIME_TO_SEC(TIMEDIFF(time_out, time_in))/3600
					ELSE TIME_TO_SEC(TIMEDIFF(DATE_SUB(NOW(), INTERVAL 1 HOUR), time_in))/3600 
				END
			)
			FROM ud_account_login_tracker alt
			WHERE alt.account_id = a.`id`
			AND alt.time_in >= '".date('Y-m-d 00:00:00', strtotime('today'))."' 
			AND alt.time_in <= '".date('Y-m-d 23:59:59', strtotime('today'))."' 
			AND alt.status !=4
		) AS total_hours,
		(
			SELECT COUNT(lch.id) 
			FROM ud_lead_call_history lch
			LEFT JOIN ud_lists uls ON uls.id = lch.list_id
			WHERE lch.agent_account_id = a.`id`
			AND lch.start_call_time >= '".date('Y-m-d 00:00:00', strtotime('today'))."' 
			AND lch.start_call_time <= '".date('Y-m-d 23:59:59', strtotime('today'))."' 
			AND lch.end_call_time > lch.start_call_time
			AND uls.skill_id IN (11,12,15,16,17,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38)
			AND lch.status != 4
		) AS dials,
		(
			SELECT COUNT(lch.id) 
			FROM ud_lead_call_history lch
			LEFT JOIN ud_lists uls ON uls.id = lch.list_id
			LEFT JOIN ud_calendar_appointment ca ON ca.id = lch.calendar_appointment_id
			WHERE lch.agent_account_id = a.`id`
			AND lch.start_call_time >= '".date('Y-m-d 00:00:00', strtotime('today'))."'  
			AND lch.start_call_time <= '".date('Y-m-d 23:59:59', strtotime('today'))."'  
			AND lch.end_call_time > lch.start_call_time
			AND uls.skill_id IN (11,12,15,16,17,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38)
			AND lch.disposition='Appointment Set'
			AND lch.status != 4
			AND lch.is_skill_child=0
			AND ca.id IS NOT NULL
			AND ca.title IN ('INSERT APPOINTMENT', 'APPOINTMENT SET', 'CANCEL APPOINTMENT', 'RESCHEDULE APPOINTMENT', 'LOCATION CONFLICT', 'SCHEDULE CONFLICT')
		) AS appointments
		FROM ud_account a
		LEFT JOIN ud_account_user au ON au.`account_id` = a.`id`
		WHERE a.id='".$authAccount->id."'
		ORDER BY au.last_name ASC
	";
	
	$connection = Yii::app()->db;
	$command = $connection->createCommand($todaySql);
	$agentPerformanceToday = $command->queryRow();
	
	
	$weekToDateSql = "
		SELECT
		(
			SELECT SUM(
				CASE WHEN time_out IS NOT NULL THEN TIME_TO_SEC(TIMEDIFF(time_out, time_in))/3600
					ELSE TIME_TO_SEC(TIMEDIFF(DATE_SUB(NOW(), INTERVAL 1 HOUR), time_in))/3600 
				END
			)
			FROM ud_account_login_tracker alt
			WHERE alt.account_id = a.`id`
			AND alt.time_in >= '".date('Y-m-d 00:00:00', strtotime('monday this week'))."' 
			AND alt.time_in <= '".date('Y-m-d 23:59:59', strtotime('sunday this week'))."' 
			AND alt.status !=4
		) AS total_hours,
		(
			SELECT COUNT(lch.id) 
			FROM ud_lead_call_history lch
			LEFT JOIN ud_lists uls ON uls.id = lch.list_id
			WHERE lch.agent_account_id = a.`id`
			AND lch.start_call_time >= '".date('Y-m-d 00:00:00', strtotime('monday this week'))."' 
			AND lch.start_call_time <= '".date('Y-m-d 23:59:59', strtotime('sunday this week'))."' 
			AND lch.end_call_time > lch.start_call_time
			AND uls.skill_id IN (11,12,15,16,17,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38)
			AND lch.status != 4
		) AS dials,
		(
			SELECT COUNT(lch.id) 
			FROM ud_lead_call_history lch
			LEFT JOIN ud_lists uls ON uls.id = lch.list_id
			WHERE lch.agent_account_id = a.`id`
			AND lch.start_call_time >= '".date('Y-m-d 00:00:00', strtotime('monday this week'))."'  
			AND lch.start_call_time <= '".date('Y-m-d 23:59:59', strtotime('sunday this week'))."'  
			AND lch.end_call_time > lch.start_call_time
			AND uls.skill_id IN (11,12,15,16,17,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38)
			AND lch.disposition='Appointment Set'
			AND lch.status != 4
			AND lch.is_skill_child=0
		) AS appointments
		FROM ud_account a
		LEFT JOIN ud_account_user au ON au.`account_id` = a.`id`
		WHERE a.id='".$authAccount->id."'
		ORDER BY au.last_name ASC
	";
	
	$connection = Yii::app()->db;
	$command = $connection->createCommand($weekToDateSql);
	$agentPerformanceThisWeek = $command->queryRow();
	
	
	$monthToDateSql = "
		SELECT
		(
			SELECT SUM(
				CASE WHEN time_out IS NOT NULL THEN TIME_TO_SEC(TIMEDIFF(time_out, time_in))/3600
					ELSE TIME_TO_SEC(TIMEDIFF(DATE_SUB(NOW(), INTERVAL 1 HOUR), time_in))/3600 
				END
			)
			FROM ud_account_login_tracker alt
			WHERE alt.account_id = a.`id`
			AND alt.time_in >= '".date('Y-m-01 00:00:00')."' 
			AND alt.time_in <= '".date('Y-m-t 23:59:59')."' 
			AND alt.status !=4
		) AS total_hours,
		(
			SELECT COUNT(lch.id) 
			FROM ud_lead_call_history lch
			LEFT JOIN ud_lists uls ON uls.id = lch.list_id
			WHERE lch.agent_account_id = a.`id`
			AND lch.start_call_time >= '".date('Y-m-01 00:00:00')."' 
			AND lch.start_call_time <= '".date('Y-m-t 23:59:59')."' 
			AND lch.end_call_time > lch.start_call_time
			AND uls.skill_id IN (11,12,15,16,17,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38)
			AND lch.status != 4
		) AS dials,
		(
			SELECT COUNT(lch.id) 
			FROM ud_lead_call_history lch
			LEFT JOIN ud_lists uls ON uls.id = lch.list_id
			WHERE lch.agent_account_id = a.`id`
			AND lch.start_call_time >= '".date('Y-m-01 00:00:00')."'  
			AND lch.start_call_time <= '".date('Y-m-t 23:59:59')."'  
			AND lch.end_call_time > lch.start_call_time
			AND uls.skill_id IN (11,12,15,16,17,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38)
			AND lch.disposition='Appointment Set'
			AND lch.status != 4
			AND lch.is_skill_child=0
		) AS appointments
		FROM ud_account a
		LEFT JOIN ud_account_user au ON au.`account_id` = a.`id`
		WHERE a.id='".$authAccount->id."'
		ORDER BY au.last_name ASC
	";
	
	$connection = Yii::app()->db;
	$command = $connection->createCommand($monthToDateSql);
	$agentPerformanceThisMonth = $command->queryRow();

	
	$latestCallsSql = "
		SELECT c.`firstname` as customer_first_name, c.`lastname` as customer_last_name, l.`first_name` as lead_first_name, l.`last_name` as lead_last_name, lch.`lead_phone_number`, lch.`disposition`, lch.`start_call_time`
		FROM ud_lead_call_history lch
		LEFT JOIN ud_customer c ON c.`id` = lch.`customer_id`
		LEFT JOIN ud_lead l ON l.`id` = lch.`lead_id`
		WHERE lch.status !=4
		AND lch.`agent_account_id` = '".$authAccount->id."'
		ORDER BY lch.`start_call_time` DESC
		LIMIT 5
	";
	
	$connection = Yii::app()->db;
	$command = $connection->createCommand($latestCallsSql);
	$latestCalls = $command->queryAll();

?>

<div class="row">
	<div class="col-sm-12">
		
		<div class="row">
			<div class="col-sm-11 col-sm-offset-1">
				<div class="pull-left">
					<?php 
						if($accountUser->getImage())
						{
							echo CHtml::image($accountUser->getImage(), '', array('class'=>'img-responsive'));
						}
						else
						{
							echo '<div style="width:100px; height:74px; border:1px dashed #ccc; text-align:center;">No Image Uploaded.</div>';
						}
					?>
				</div>
				
				<h3 class="pull-left" style="margin-left:30px;"><?php echo $accountUser->getFullName(); ?></h3>
			</div>
		</div>
		
		<div class="space-6"></div>
		
		<div class="row">
			<div class="col-sm-11 col-sm-offset-1">
				<h3 class="row header lighter blue">
					<?php
						if( $campaign )
						{
							echo $campaign->campaign_name; 
						}
						else
						{
							echo 'Policy Review Campaign is inactive';
						}
					?> 
				</h3>
			</div>
		</div>
		
		<div class="row">
			<div class="col-sm-11 col-sm-offset-1">
				
				<table class="table table-striped table-bordered table-hover table-condensed">
					<tr>
						<td></td>
						<td>Clocked Hours</td>
						<td>Dials</td>
						<td>Dials/Hour</td>
						<td>Appointments</td>
						<td>Appts/Hour</td>
					</tr>
					
					<!-- TODAY -->
					<tr>
						<th>Today</th>
						
						<td>
							<?php 
								echo round($agentPerformanceToday['total_hours'], 2); 
							?>						
						</td>
						
						<td>
							<?php 
								echo $agentPerformanceToday['dials']; 
							?>
						</td>
						
						<td>
							<?php 
								if( $agentPerformanceToday['dials'] > 0 && $agentPerformanceToday['total_hours'] > 0 )
								{
									echo round($agentPerformanceToday['dials'] / $agentPerformanceToday['total_hours'], 2);
								}
								else
								{
									echo 0;
								}
							?>
						</td>	
						
						<td>
							<?php 
								echo $agentPerformanceToday['appointments']; 
							?>
						</td>
						
						<td>
							<?php 
								if( $agentPerformanceToday['appointments'] > 0 && $agentPerformanceToday['total_hours'] > 0 )
								{
									echo round($agentPerformanceToday['appointments'] / $agentPerformanceToday['total_hours'], 2);
								}
								else
								{
									echo 0;
								}
							?>
						</td>						
					</tr>
					
					<!-- WEEK TO DATE -->
					<tr>
						<th>Week to Date</th>
						
						<td>
							<?php 
								echo round($agentPerformanceThisWeek['total_hours'], 2); 
							?>						
						</td>
						
						<td>
							<?php 
								echo $agentPerformanceThisWeek['dials']; 
							?>
						</td>
						
						<td>
							<?php 
								if( $agentPerformanceThisWeek['dials'] > 0 && $agentPerformanceThisWeek['total_hours'] > 0 )
								{
									echo round($agentPerformanceThisWeek['dials'] / $agentPerformanceThisWeek['total_hours'], 2);
								}
								else
								{
									echo 0;
								}
							?>
						</td>
						
						<td>
							<?php 
								echo $agentPerformanceThisWeek['appointments']; 
							?>
						</td>
						
						<td>
							<?php 
								if( $agentPerformanceThisWeek['appointments'] > 0 && $agentPerformanceThisWeek['total_hours'] > 0 )
								{
									echo round($agentPerformanceThisWeek['appointments'] / $agentPerformanceThisWeek['total_hours'], 2);
								}
								else
								{
									echo 0;
								}
							?>
						</td>						
					</tr>
					
					<!-- MONTH TO DATE -->
					<tr>
						<th>Month to Date</th>
						
						<td>
							<?php 
								echo round($agentPerformanceThisMonth['total_hours'], 2); 
							?>						
						</td>
						
						<td>
							<?php 
								echo $agentPerformanceThisMonth['dials']; 
							?>
						</td>
						
						<td>
							<?php 
								if( $agentPerformanceThisMonth['dials'] > 0 && $agentPerformanceThisMonth['total_hours'] > 0 )
								{
									echo round($agentPerformanceThisMonth['dials'] / $agentPerformanceThisMonth['total_hours'], 2);
								}
								else
								{
									echo 0;
								}
							?>
						</td>
						
						<td>
							<?php 
								echo $agentPerformanceThisMonth['appointments']; 
							?>
						</td>
						
						<td>
							<?php 
								if( $agentPerformanceThisMonth['appointments'] > 0 && $agentPerformanceThisMonth['total_hours'] > 0 )
								{
									echo round($agentPerformanceThisMonth['appointments'] / $agentPerformanceThisMonth['total_hours'], 2);
								}
								else
								{
									echo 0;
								}
							?>
						</td>						
					</tr>
				</table>
				
			</div>
		</div>
		
		<div class="space-6"></div>
		
		<div class="row">
			<div class="col-sm-11 col-sm-offset-1">
				<h3 class="row header lighter blue">
					Last 5 Calls
				</h3>
			</div>
		</div>
		
		<div class="row">
			<div class="col-sm-11 col-sm-offset-1">
				
				<table class="table table-striped table-bordered table-hover table-condensed">
					<tr>
						<th>Customer First</th>
						<th>Customer Last</th>
						<th>Lead First Name</th>
						<th>Lead Last Name</th>
						<th>Lead Phone Number</th>
						<th>Disposition</th>
						<th>Call Date/Time</th>
					</tr>
					
					<?php 
						if( $latestCalls )
						{
							foreach( $latestCalls as $latestCall )
							{
								$callDate = new DateTime($latestCall['start_call_time'], new DateTimeZone('America/Chicago'));
								$callDate->setTimezone(new DateTimeZone('America/Denver'));	
									
								echo '<tr>';
									echo '<td>'.$latestCall['customer_first_name'].'</td>';
									
									echo '<td>'.$latestCall['customer_last_name'].'</td>';
									
									echo '<td>'.$latestCall['lead_first_name'].'</td>';
									
									echo '<td>'.$latestCall['lead_last_name'].'</td>';
									
									echo '<td>'.$latestCall['lead_phone_number'].'</td>';
									
									echo '<td>'.$latestCall['disposition'].'</td>';
									
									echo '<td>'.$callDate->format('m/d/Y g:i A').'</td>';
								echo '</tr>';
							}
						}
						else
						{
							echo '<tr><td colspan="7">No results found.</td></tr>';
						}
					?>
				</table>
				
			</div>
		</div>
		
	</div>
</div>