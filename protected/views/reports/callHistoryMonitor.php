<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	$cs = Yii::app()->clientScript;
	
	Yii::app()->clientScript->registerMetaTag("5; url=".$this->createUrl('callHistoryMonitor')."", null, 'refresh');
?>


<div class="page-header">
	<h1>Reports</h1>
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
		
		<?php if( Yii::app()->user->account->checkPermission('reports_call_history_monitor_tab','visible') ){ ?>
			<li class="<?php echo Yii::app()->controller->action->id == 'callHistoryMonitor' ? 'active' : ''; ?>"><a href="<?php echo $this->createUrl('callHistoryMonitor'); ?>">Call History Monitor</a></li>
		<?php } ?>
		
		<li class="<?php echo Yii::app()->controller->action->id == 'conflictMonitor' ? 'active' : ''; ?>"><a href="<?php echo $this->createUrl('conflictMonitor'); ?>">Conflict Monitor</a></li>
		
		<li class="<?php echo Yii::app()->controller->action->id == 'appointmentMonitor' ? 'active' : ''; ?>"><a href="<?php echo $this->createUrl('appointmentMonitor'); ?>">Confirm Monitor</a></li>
		
		<li class="<?php echo Yii::app()->controller->action->id == 'rescheduleMonitor' ? 'active' : ''; ?>"><a href="<?php echo $this->createUrl('rescheduleMonitor'); ?>">Reschedule Monitor</a></li>
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
					<th>#</th>
					<th>Lead ID</th>
					<th>Lead Name</th>
					<th>Customer Name</th>
					<th>Company Name</th>
					<th>Agent Name</th>
					<th>Call Date</th>
					<th>Status</th>
				</thead>
				
				<?php 
					if( $models )
					{
						$ctr = 1;
						
						foreach( $models as $model )
						{
						?>

							<tr>

								<td><?php echo $ctr; ?></td>
								
								<td><?php echo $model->lead_id; ?></td>
								
								<td><?php echo isset($model->lead) ? $model->lead->getFullName() : ''; ?></td>
								
								<td><?php echo $model->customer->getFullName(); ?></td>
								
								<td><?php echo $model->company->company_name; ?></td>
								
								<td><?php echo isset($model->agentAccount) ? $model->agentAccount->getFullName() : ''; ?></td>
								
								<td>
									<?php 
										$dateTime = new DateTime($model->date_created, new DateTimeZone('America/Chicago'));
										$dateTime->setTimezone(new DateTimeZone('America/Denver'));	
										
										echo $dateTime->format('m/d/Y g:i A');
									?>
								</td>
								
								<td>
									<?php 
										$hopperEntry = LeadHopper::model()->find(array(
											'condition' => 'lead_id = :lead_id',
											'params' => array(
												':lead_id' => $model->lead_id
											),
										));
										
										if( $hopperEntry && $hopperEntry->status == 'INCALL' )
										{
											echo 'INCALL';
										}
										else
										{
											echo 'DONE';
										}
									?>
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