<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	$cs = Yii::app()->clientScript;
?>


<div class="page-header">
	<h1>
		Reports
		
		<a class="btn btn-mini btn-primary" href="<?php echo $this->createUrl('removeAllCompletedLeads'); ?>">Remove All</a>
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
					<th>Customer Name</th>
					<th>Lead Name</th>
					<th>Disposition</th>
					<th>Disposition Date/Time</th>
					<th>Status</th>
					<th>Options</th>
				</thead>
				
				<?php 
					if( $models )
					{
						$ctr = 1;
						
						$completedConflictDispos = array('Appointment Set');
						
						foreach( $models as $model )
						{
							$lastCall = LeadCallHistory::model()->find(array(
								'condition' => 'lead_id = :lead_id',
								'params' => array(
									':lead_id' => $model->lead_id
								),
								'order' => 'date_created DESC',
							));
							?>

								<tr>

									<td class="center"><?php echo $ctr; ?></td>
									
									<td><?php echo $model->customer->getFullName(); ?></td>

									<td><?php echo $model->lead->getFullName(); ?></td>									
									
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
									
									<td>
										<?php  
											if( $model->lead->status == 3 )
											{
												echo 'Completed';
											}
											else
											{
												echo 'Active';
											}
										?>
									</td>
									
									<td>
										<?php echo CHtml::link('Remove <i class="fa fa-arrow-right"></i>', array('removeCompletedLead', 'id'=>$model->id), array('class'=>'btn btn-mini btn-primary')); ?>
									</td>
								</tr> 
						
							<?php	
							$ctr++;
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