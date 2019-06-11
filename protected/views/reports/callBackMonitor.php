<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	$cs = Yii::app()->clientScript;
?>


<div class="page-header">
	<h1>
		Reports
		
		<a class="btn btn-mini btn-primary" href="<?php echo $this->createUrl('callBackLoadALL'); ?>">Load All</a>
		<a class="btn btn-mini btn-danger" href="<?php echo $this->createUrl('callBackForceALL'); ?>">Force All</a>
	</h1>
</div>

<div class="tabbable tabs-left">
	
	<ul class="nav nav-tabs">
		<?php if( Yii::app()->user->account->checkPermission('reports_real_time_monitors_tab','visible') ){ ?>
			<li class="<?php echo Yii::app()->controller->action->id == 'index' ? 'active' : ''; ?>"><a href="<?php echo $this->createUrl('index'); ?>">Real-Time Monitors</a></li>
		<?php } ?>
		
		<?php if( Yii::app()->user->account->checkPermission('reports_reports_tab','visible') ){ ?>
			<li class="<?php echo Yii::app()->controller->action->id == 'reports' ? 'active' : ''; ?>"><a href="<?php echo $this->createUrl('reports'); ?>">Reports</a></li>
		<?php } ?>
		
		<?php if( Yii::app()->user->account->checkPermission('reports_caller_id_listing_tab','visible') ){ ?>
			<li class="<?php echo Yii::app()->controller->action->id == 'callerIdListing' ? 'active' : ''; ?>"><a href="<?php echo $this->createUrl('callerIdListing'); ?>">Caller ID Listing</a></li>
		<?php } ?>
		
		<li class="<?php echo Yii::app()->controller->action->id == 'conflictMonitor' ? 'active' : ''; ?>"><a href="<?php echo $this->createUrl('conflictMonitor'); ?>">Conflict Monitor</a></li>
		
		<li class="<?php echo Yii::app()->controller->action->id == 'appointmentMonitor' ? 'active' : ''; ?>"><a href="<?php echo $this->createUrl('appointmentMonitor'); ?>">Confirm Monitor</a></li>
		
		<li class="<?php echo Yii::app()->controller->action->id == 'rescheduleMonitor' ? 'active' : ''; ?>"><a href="<?php echo $this->createUrl('rescheduleMonitor'); ?>">Reschedule Monitor</a></li>
		
		<li class="<?php echo Yii::app()->controller->action->id == 'callBackMonitor' ? 'active' : ''; ?>"><a href="<?php echo $this->createUrl('callBackMonitor'); ?>">Call Back Monitor</a></li>
		
		<li class="<?php echo Yii::app()->controller->action->id == 'completedLeadMonitor' ? 'active' : ''; ?>"><a href="<?php echo $this->createUrl('completedLeadMonitor'); ?>">Completed Lead Monitor</a></li>
	</ul>
	
</div>

<div class="tab-content">
	<div class="row">
		<div class="col-sm-12">
			<?php
				foreach(Yii::app()->user->getFlashes() as $key => $message) {
					echo '<div class="alert alert-' . $key . '"><button data-dismiss="alert" class="close" type="button"><i class="ace-icon fa fa-times"></i></button> ' . $message . "</div>\n";
				} 
			?>
			
			<table id="leadsTbl" class="table table-striped table-bordered table-condensed table-hover">
				<thead>
					<th class="center">#</th>
					<th>Agent</th>
					<th>Date/Time Call Back was set</th>
					<th>Lead Name</th>
					<th>Date/Time Call Back to occur </th>
					<th>Last Call Dispo</th>
					<th>Last Call Date/Time</th>
					<th class="center">In Queue</th>
					<th class="center">Options</th>
				</thead>
				
				<?php 
					if( $models )
					{
						$ctr = 1;
						
						$completedConflictDispos = array('Appointment Set');
						
						foreach( $models as $model )
						{
							$customerSkill = CustomerSkill::model()->find(array(
								'condition' => 'customer_id = :customer_id AND skill_id = :skill_id AND status=1',
								'params' => array(
									':customer_id' => $model->customer_id,
									':skill_id' => $model->lists->skill_id,
								),
							));
							
							$status = 'Active';
							
							if( $customerSkill->is_contract_hold == 1 )
							{
								if( !empty($customerSkill->is_contract_hold_start_date) && !empty($customerSkill->is_contract_hold_end_date) )
								{
									if( time() >= strtotime($customerSkill->is_contract_hold_start_date) && time() <= strtotime($customerSkill->is_contract_hold_end_date) )
									{
										$status = 'Hold';
									}
								}
							}
							
							if( !empty($customerSkill->end_month) && date('Y', strtotime($customerSkill->end_month)) >= 2015 )
							{
								if( time() >= strtotime($customerSkill->end_month) )
								{
									$status = 'Cancelled';
								}
							}
							
							if( $customerSkill->is_hold_for_billing == 1 )
							{
								$status = 'Hold';
							}
							
							if( $status == 'Active' )
							{
								$hopperEntry = LeadHopper::model()->find(array(
									'condition' => 'lead_id = :lead_id',
									'params' => array(
										':lead_id' => $model->lead_id
									),
								));
								
								$lastCall = LeadCallHistory::model()->find(array(
									'condition' => 'lead_id = :lead_id',
									'params' => array(
										':lead_id' => $model->lead_id
									),
									'order' => 'date_created DESC',
								));

								// $afterConflictLast3Calls = LeadCallHistory::model()->findAll(array(
									// 'condition' => '
										// lead_id = :lead_id 
										// AND DATE(date_created) >= :conflict_date
									// ',
									// 'params' => array(
										// ':lead_id' => $model->lead_id,
										// ':conflict_date' => date('Y-m-d', strtotime($model->calendarAppointment->date_updated)),
									// ),
									// 'order' => 'date_created ASC',
									// 'limit' => 3
								// ));
								
								// $disposAfterConflict = array();
								
								// if( $afterConflictLast3Calls )
								// {
									// foreach( $afterConflictLast3Calls as $afterConflictLast3Call )
									// {
										// $disposAfterConflict[] = $afterConflictLast3Call->disposition;
									// }
								// }
							
								if( strtotime($model->callback_time) >= strtotime($lastCall->date_created) && in_array($lastCall->disposition, array("Call Back", "Call Back - Confirm", "Will Call Back")) )
								{
								?>

									<tr>

										<td class="center"><?php echo $ctr; ?></td>
										
										<td><?php echo $model->agentAccount->getFullName(); ?></td>
										
										<td class="center"><?php echo date('m/d/Y g:i A', strtotime($model->date_created)); ?></td>										
										
										<td><?php echo CHtml::link($model->lead->getFullName(), '', array('lead_id'=>$model->lead_id)); ?></td>									
										
										<td><?php echo date('m/d/Y g:i A', strtotime($model->callback_time)); ?></td>
										
										<td>
											<?php  
												if( $lastCall )
												{
													echo $lastCall->disposition;
												}
											?>
										</td>
										
										<td>
											<?php  
												if( $lastCall )
												{
													echo date('m/d/Y g:i A', strtotime($lastCall->date_created));
												}
											?>
										</td>
										
										<td class="center"><?php echo !empty($hopperEntry) ? "<span class='badge badge-success'>YES</span>" : "<span class='badge badge-danger'>NO</span>"; ?></td>

										<td class="center">
											<?php 
												if( empty($hopperEntry) )
												{
													echo CHtml::link('Load to Queue <i class="fa fa-arrow-right"></i>', array('callBackAddToQueue', 'id'=>$model->id), array('class'=>'btn btn-mini btn-primary'));
												}
												else
												{
													echo CHtml::link('Force Lead <i class="fa fa-arrow-right"></i>', array('callBackAddToQueue', 'id'=>$model->id, 'force'=>1), array('class'=>'btn btn-mini btn-danger'));
												}
											?>
										</td>
									</tr> 
							
								<?php	
								$ctr++;
								}
							}
						}
					}
					else
					{
						echo '<tr><td colspan="5">No results found.</td></tr>';
					}
				?>
			</table>
		</div>
	</div>
</div>