<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	$cs = Yii::app()->clientScript;
?>


<div class="page-header">
	<h1>
		Reports
		
		<a class="btn btn-mini btn-primary" href="<?php echo $this->createUrl('appointmentsLoadALL'); ?>">Load All</a>
		<a class="btn btn-mini btn-danger" href="<?php echo $this->createUrl('appointmentsForceALL'); ?>">Force All</a>
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
					<th>Appointment Type</th>
					<th>Appointment Made</th>
					<th>Appointment Status</th>
					<th>Confirm Dials</th>
					<th>Customer Name</th>
					<th>Lead Name</th>
					<th>Last Call Dispo</th>
					<th>Appointment Date</th>
					<th>Time Zone</th>
					<th class="center">In Queue</th>
					<th class="center">Options</th>
				</thead>
				
				<?php 
					if( $models )
					{
						$ctr = 1;
						
						$completedConflictDispos = array('Appointment Confirmed', 'Appointment Confirmed - Left Message');
						
						foreach( $models as $model )
						{
							$customerIsCallable = false;
							
							$customerSkill = CustomerSkill::model()->find(array(
								'condition' => 'customer_id = :customer_id AND skill_id = :skill_id AND status=1',
								'params' => array(
									':customer_id' => $model['customer_id'],
									':skill_id' => $model['skill_id'],
								),
							));
							
							if( $customerSkill )
							{
								if( isset($customerSkill->contract) && isset($customerSkill->customer) && $customerSkill->customer->status == 1 && $customerSkill->customer->is_deleted == 0 && time() >= strtotime($customerSkill->start_month) )
								{
									$customerIsCallable = true;
								}
								
								if( $customerSkill->is_contract_hold == 1 )
								{
									if( !empty($customerSkill->is_contract_hold_start_date) && !empty($customerSkill->is_contract_hold_end_date) )
									{
										if( time() >= strtotime($customerSkill->is_contract_hold_start_date) && time() <= strtotime($customerSkill->is_contract_hold_end_date) )
										{
											$customerIsCallable = false;
										}
									}
								}
								
								if( !empty($customerSkill->end_month) && date('Y', strtotime($customerSkill->end_month)) >= 2015 )
								{
									if( time() >= strtotime($customerSkill->end_month) )
									{
										$customerIsCallable = false;
									}
								}
							}
							
							if( $customerSkill->status == 1 && $customerIsCallable )
							{
								$skillChildConfirmation = SkillChild::model()->find(array(
									'condition' => 'skill_id = :skill_id AND type = :type',
									'params' => array(
										':skill_id' => $model['skill_id'],
										':type' => SkillChild::TYPE_CONFIRM,
									),
								));
								
								if( $skillChildConfirmation )
								{
									$customerSkillChild = CustomerSkillChild::model()->find(array(
										'condition' => 'customer_id = :customer_id AND skill_id = :skill_id AND customer_skill_id = :customer_skill_id AND skill_child_id = :skill_child_id',
										'params' => array(
											':customer_id' => $model['customer_id'],
											':skill_id' => $model['skill_id'],
											':customer_skill_id' => $customerSkill->id,
											':skill_child_id' => $skillChildConfirmation->id
										),
									));
									
									if( $customerSkillChild && $customerSkillChild->is_enabled == 1 )
									{
										$createConfirm = false;
										$disposAfterConflict = array();
											
										$lastCall = LeadCallHistory::model()->find(array(
											'condition' => 'lead_id = :lead_id',
											'params' => array(
												':lead_id' => $model['lead_id']
											),
											'order' => 'date_created DESC',
										));
									
										if( $lastCall )
										{
											$hopperEntry = LeadHopper::model()->find(array(
												'condition' => 'lead_id = :lead_id',
												'params' => array(
													':lead_id' => $model['lead_id']
												),
											));
											
											$afterConflictLast3Calls = LeadCallHistory::model()->findAll(array(
												'condition' => '
													lead_id = :lead_id 
													AND DATE(date_created) > :appointment_date
												',
												'params' => array(
													':lead_id' => $model['lead_id'],
													':appointment_date' => date('Y-m-d', strtotime($model['appointment_date_updated'])),
												),
												'order' => 'date_created DESC',
												'limit' => 3
											));
											
											foreach( $afterConflictLast3Calls as $afterConflictLast3Call )
											{
												$disposAfterConflict[] = $afterConflictLast3Call->disposition;
											}
											
											$dispoMatch = array_intersect($disposAfterConflict, $completedConflictDispos);
											
											if( $lastCall->is_skill_child == 0 )
											{
												if( $lastCall->skillDisposition->is_appointment_set == 1 )
												{
													$createConfirm = true;
												}
											}
											else
											{
												if( $lastCall->skillChildDisposition->is_appointment_set == 1 || $lastCall->skillChildDisposition->is_callback == 1 )
												{
													$createConfirm = true;
												}
											}
										}
										else
										{
											$createConfirm = true;
										}
										
										if( $createConfirm && count($dispoMatch) == 0 )
										{
										?>

											<tr>

												<td class="center"><?php echo $ctr; ?></td>
												
												<td><?php echo $model['appointment_title']; ?></td>
												
												<td class="center"><?php echo date('m/d/Y', strtotime($model['appointment_date_updated'])); ?></td>
												
												<td class="center">
													<?php 
														switch( $model['appointment_status'] )
														{
															default: case 1: echo 'Approved'; break;
															case 2: echo 'Pending'; break;
															case 3: echo 'Denied'; break;
															case 5: echo 'Suggest Alternate'; break;
														}
													?>
												</td>
												
												<td class="center">
													<?php 
														echo count($afterConflictLast3Calls); 
													?>
												</td>
												
												<td><?php echo $model['customer_name']; ?></td>
												
												<td><?php echo $model['lead_name']; ?></td>
												
												<td>
													<?php  
														if( $lastCall )
														{
															echo $lastCall->disposition;
														}
													?>
												</td>
												
												<td><?php echo $model['appointment_start_date']; ?></td>
												
												<td class="center"><?php echo $model['lead_timezone']; ?></td>
												
												<td class="center"><?php echo !empty($hopperEntry) ? "<span class='badge badge-success'>YES</span>" : "<span class='badge badge-danger'>NO</span>"; ?></td>

												<td class="center">
													<?php 
														if( empty($hopperEntry) )
														{
															echo CHtml::link('Load to Queue <i class="fa fa-arrow-right"></i>', array('appointmentAddToQueue', 'calendar_appointment_id'=>$model['appointment_id']), array('class'=>'btn btn-mini btn-primary'));
														}
														else
														{
															echo CHtml::link('Force Lead <i class="fa fa-arrow-right"></i>', array('appointmentAddToQueue', 'calendar_appointment_id'=>$model['appointment_id'], 'force'=>1), array('class'=>'btn btn-mini btn-danger'));
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