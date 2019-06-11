<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	$cs = Yii::app()->clientScript;
?>


<div class="page-header">
	<h1>
		Reports
		
		<a class="btn btn-mini btn-primary" href="<?php echo $this->createUrl('conflictsLoadALL'); ?>">Load All</a>
		<a class="btn btn-mini btn-danger" href="<?php echo $this->createUrl('conflictsForceALL'); ?>">Force All</a>
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
					<th>Conflict Type</th>
					<th>Conflict Date</th>
					<th>Conflict Status</th>
					<th>Conflict Dials</th>
					<th>Customer Name</th>
					<th>Lead Name</th>
					<th>Last Call Dispo</th>
					<th>Last Call Date/Time</th>
					<th>Appointment Date</th>
					<th class="center">In Queue</th>
					<th class="center">Options</th>
				</thead>
				
				<?php 
					if( $models )
					{
						$ctr = 1;
						
						$completedConflictDispos = array('Appointment Set', 'Client Complete', 'Appointment Confirmed', 'Appointment Confirmed - Left Message', 'Answering Machine - Left Message', 'Client to Contact Agent', 'Do Not Call');
						
						foreach( $models as $model )
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
							
							$afterConflictLast3Calls = LeadCallHistory::model()->findAll(array(
								'condition' => '
									lead_id = :lead_id 
								',
								'params' => array(
									':lead_id' => $model->lead_id,
								),
								'order' => 'date_created DESC',
								'limit' => 3
							));
							
							$disposAfterConflict = array();
							
							foreach( $afterConflictLast3Calls as $afterConflictLast3Call )
							{
								$disposAfterConflict[] = $afterConflictLast3Call->disposition;
							}
							
							$dispoMatch = array_intersect($disposAfterConflict, $completedConflictDispos);
						
							if( count($dispoMatch) == 0 )
							{
							?>

								<tr>

									<td class="center"><?php echo $ctr; ?></td>
									
									<td><?php echo $model->title; ?></td>
									
									<td class="center"><?php echo date('m/d/Y', strtotime($model->date_updated)); ?></td>
									
									<td class="center">
										<?php 
											switch( $model->status )
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
									
									<td><?php echo $model->lead->customer->getFullName(); ?></td>
									
									<td><?php echo CHtml::link($model->lead->getFullName(), '', array('lead_id'=>$model->lead_id)); ?></td>
									
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
									
									<td><?php echo $model->start_date; ?></td>
									
									<td class="center"><?php echo !empty($hopperEntry) ? "<span class='badge badge-success'>YES</span>" : "<span class='badge badge-danger'>NO</span>"; ?></td>

									<td class="center">
										<?php 
											if( empty($hopperEntry) )
											{
												echo CHtml::link('Load to Queue <i class="fa fa-arrow-right"></i>', array('conflictAddToQueue', 'calendar_appointment_id'=>$model->id), array('class'=>'btn btn-mini btn-primary'));
											}
											else
											{
												echo CHtml::link('Force Lead <i class="fa fa-arrow-right"></i>', array('conflictAddToQueue', 'calendar_appointment_id'=>$model->id, 'force'=>1), array('class'=>'btn btn-mini btn-danger'));
											}
										?>
									</td>
								</tr> 
						
							<?php	
							$ctr++;
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