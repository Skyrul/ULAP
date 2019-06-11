<?php 
	Yii::app()->clientScript->registerScript(uniqid(), '
	
		$(document).on("click", ".generate-report-btn", function(){
			
			var export_type = $(".select-change-export-type").val();
			
			window.open( $(this).attr("url") + "?exportType=" + export_type, "_blank", "toolbar=0,location=0,menubar=0" );
			
		});
	
	', CClientScript::POS_END);
?>

<?php 
	$this->widget("application.components.CustomerSideMenu",array(
		'active'=> 'reports',
		'customer' => $customer,
	));
?>

<div class="page-header">
	<h1>Reports</h1>
</div>
	
<div class="row">

	<div class="col-sm-12">
	
		<div class="col-sm-6 widget-container-col">
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
			
			<?php /*
			
			<?php if( !Yii::app()->user->isGuest && (Yii::app()->user->account->getIsAdmin() || Yii::app()->user->account->getIsCustomerService() || Yii::app()->user->account->id == 2) ): ?>
			
			<div class="widget-box ui-sortable-handle">
				<div class="widget-header">
					<h5 class="widget-title">
						At a glance
						<?php 
							$customerQueue = CustomerQueueViewer::model()->find(array(
								'condition' => 'customer_id = :customer_id AND skill_id IN(11,15,16)',
								'params' => array(
									':customer_id' => $customer->id,
								),
							));
							
							if( $customerQueue )
							{
								echo ' - <i>' . $customerQueue->contracted_quantity . ' Appointments per month</i>';
							}
						?>
					</h5>

					<div class="widget-toolbar no-border">
						<div class="widget-menu">
						</div>
					</div>
				</div>
				<div class="widget-body">
					<div class="widget-main no-padding">
						<table class="table table-bordered table-striped table-condensed table-hover">
							<thead>
								<th class="center">Month</th>
								<th class="center">Callable on the 1st</th>
								<th class="center">Leads Imported</th>
								<th class="center">Appointments Set</th>
							</thead>
							
							<tbody>
								<?php 
								
									$totalImportedCount = 0;
									$totalAppointmentSets = 0;
									
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
										
											foreach( range(1, 12) as $month )
											{
												$monthName = date("M", mktime(0, 0, 0, $month, 10));
												
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
													$callableLead = CustomerCallableLeadCount::model()->find(array(
														'condition' => 'customer_id = :customer_id AND YEAR(date_created) = :year AND MONTH(date_created) = :month',
														'params' => array(
															':customer_id' => $customer->id,
															':year' => $year,
															':month' => $month
														),
													));
															
													$appointmentSetMTDSql = "
														SELECT ca.start_date, ca.title, lch.lead_id, ld.first_name, ld.last_name
														FROM ud_lead_call_history lch 
														INNER JOIN ud_calendar_appointment ca ON ca.id = lch.calendar_appointment_id
														INNER JOIN ud_lead ld ON ld.id = lch.lead_id
														WHERE ca.title IN ('INSERT APPOINTMENT', 'APPOINTMENT SET', 'CANCEL APPOINTMENT', 'RESCHEDULE APPOINTMENT', 'NO SHOW RESCHEDULE') 
														AND lch.disposition = 'Appointment Set'
														AND lch.date_created >= '".date($year.'-'.$month.'-01 00:00:00')."' 
														AND ca.date_created <= '".date($year.'-'.$month.'-t 23:59:59')."'
														AND lch.customer_id = '".$customer->id."'
														GROUP BY lch.lead_id
														ORDER BY ld.last_name ASC 
													";

													$command = Yii::app()->db->createCommand($appointmentSetMTDSql);
													$appointmentSets = $command->queryAll();
													
													$totalAppointmentSets += count($appointmentSets);
													
												?>
													
													<tr>
														<td class="center"><?php echo ucfirst($monthName).'-'.$year; ?></td>
														
														<td class="center">
															<?php 
																if( $callableLead )
																{
																	echo $callableLead->callable_leads;
																}
																else
																{
																	echo 0;
																}
															?>
														</td>
														
														<td class="center">
															<?php 															
																// echo date($year.'-'.$month.'-01');
																// echo '<br>';
																// echo date($year.'-'.$month.'-t');
																// echo '<br>';
																// echo '<br>';
																
																$customerQueue = CustomerQueueViewer::model()->find(array(
																	'condition' => 'customer_id = :customer_id',
																	'params' => array(
																		':customer_id' => $customer->id,
																	),
																));
																
																$models = CustomerHistory::model()->findAll(array(
																	'condition' => '
																		content LIKE"%leads in list%"
																		AND DATE(date_created) >= "'.date($year.'-'.$month.'-01').'" 
																		AND DATE(date_created) <= "'.date($year.'-'.$month.'-t').'"
																		AND customer_id = "'.$customer->id.'"
																	',
																	'order' => 'date_created DESC',
																));
																
																if( $customerQueue && $models )
																{
																	$importLimit = $customerQueue->contracted_quantity * 10;
																	
																	foreach( $models as $model )
																	{
																		$importedCount = 0;
																		
																		$date = new DateTime($model->date_created, new DateTimeZone('America/Chicago'));

																		$date->setTimezone(new DateTimeZone('America/Denver'));
																		
																		$explodedContent = explode('|', $model->content);
											
																		if( count($explodedContent) == 2 )
																		{
																			$importedCount = filter_var(strip_tags($explodedContent[1]), FILTER_SANITIZE_NUMBER_INT);
																			$duplicateCount = 0;
																			$badCount = 0;
																		}
																		else
																		{	
																			$importedCount = filter_var(strip_tags($explodedContent[2]), FILTER_SANITIZE_NUMBER_INT);												
																			$duplicateCount = filter_var(strip_tags($explodedContent[4]), FILTER_SANITIZE_NUMBER_INT);
																			$badCount = filter_var(strip_tags($explodedContent[5]), FILTER_SANITIZE_NUMBER_INT);
																		}	
																		
																		if( $importLimit >= $importedCount )
																		{
																			$totalImportedCount += $importedCount;
																		
																			echo $importedCount . ' ('.$date->format('m-d').') <br />';																		
																		}
																	}
																}
																else
																{
																	echo 0;
																}
															?>
														</td>
														
														<td class="center"><?php echo count($appointmentSets); ?></td>
													</tr>
												
												<?php
												}
												
											}
											
										}
									}
								?>
								<tr><td colspan="4"></td></tr>
								<tr>
									<th colspan="2">Total</th>
	
									<th class="center"><?php echo $totalImportedCount; ?></th>
									<th class="center"><?php echo $totalAppointmentSets; ?></th>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			
			<?php endif;?>
			
			<div class="widget-box ui-sortable-handle">
				<div class="widget-header">
					<h5 class="widget-title">Imported Reports</h5>

					<div class="widget-toolbar no-border">
						<div class="widget-menu">
						</div>
					</div>
				</div>
				<div class="widget-body">
					<div class="widget-main">
						<?php 
							$criteria = new CDbCriteria;
							$criteria->compare('customer_id', $customer->id);
							$criteria->order = 't.date_created DESC';
							$customerReports = CustomerReport::model()->findAll($criteria); 
							
							foreach($customerReports as $customerReport)
							{
								echo CHtml::link($customerReport->fileUpload->original_filename, array('/site/download', 'file'=>$customerReport->fileUpload->original_filename, 'customerReportId' => $customerReport->id), array('target'=>'_blank'));
			
								echo '<br>';
							}
						?>
					</div>
				</div>
			</div>
			*/ ?>
		</div>
		
		<div class="col-sm-6 widget-container-col">
		
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