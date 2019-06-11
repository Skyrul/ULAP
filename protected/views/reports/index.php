<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	$cs = Yii::app()->clientScript;
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
		
		<li class="<?php echo Yii::app()->controller->action->id == 'conflictMonitor' ? 'active' : ''; ?>"><a href="<?php echo $this->createUrl('conflictMonitor'); ?>">Conflict Monitor</a></li>
		
		<li class="<?php echo Yii::app()->controller->action->id == 'appointmentMonitor' ? 'active' : ''; ?>"><a href="<?php echo $this->createUrl('appointmentMonitor'); ?>">Confirm Monitor</a></li>
		
		<li class="<?php echo Yii::app()->controller->action->id == 'rescheduleMonitor' ? 'active' : ''; ?>"><a href="<?php echo $this->createUrl('rescheduleMonitor'); ?>">Reschedule Monitor</a></li>
		
		<li class="<?php echo Yii::app()->controller->action->id == 'callBackMonitor' ? 'active' : ''; ?>"><a href="<?php echo $this->createUrl('callBackMonitor'); ?>">Call Back Monitor</a></li>
		
		<li class="<?php echo Yii::app()->controller->action->id == 'completedLeadMonitor' ? 'active' : ''; ?>"><a href="<?php echo $this->createUrl('completedLeadMonitor'); ?>">Completed Lead Monitor</a></li>
	</ul>
	
</div>

<div class="tab-content">

	<?php if( Yii::app()->user->account->checkPermission('reports_real_time_monitors_email_monitor_button','visible') ){ ?>
		<button onclick='window.open("<?php echo $this->createUrl('emailMonitor'); ?>", "emailMonitoringWindow", "titlebar=0,toolbar=0,scrollbars=1,location=0,statusbar=1,menubar=0,resizable=1,directories=0,width=1100,height=500");' class="btn btn-primary btn-xs">
			<i class="fa fa-envelope-o fa-lg"></i>
			Messaging Monitor
		</button>
	<?php } ?>
	
	<?php if( Yii::app()->user->account->checkPermission('reports_real_time_monitors_queue_viewer_button','visible') ){ ?>
		<button onclick='window.open("<?php echo $this->createUrl('/queueViewer'); ?>", "queueViewerWindow", "titlebar=0,toolbar=0,scrollbars=1,location=0,statusbar=1,menubar=0,resizable=1,directories=0,width=1100,height=500");' class="btn btn-primary btn-xs">
			<i class="fa fa-list-ol fa-lg"></i>
			Queue Viewer
		</button>
	<?php } ?>
	
	<?php if( Yii::app()->user->account->checkPermission('reports_real_time_monitors_employee_state_button','visible') ){ ?>
		<button onclick='window.open("<?php echo $this->createUrl('agentState'); ?>", "agentStateWindow", "titlebar=0,toolbar=0,scrollbars=1,location=0,statusbar=1,menubar=0,resizable=1,directories=0,width=1100,height=500");' class="btn btn-primary btn-xs">
			<i class="fa fa-headphones fa-lg"></i>
			Employee State
		</button>
	<?php } ?>
	
	<?php if( Yii::app()->user->account->checkPermission('reports_real_time_monitors_call_management_button','visible') ){ ?>
		<button onclick='window.open("<?php echo $this->createUrl('callManagement'); ?>", "callManagementWindow", "titlebar=0,toolbar=0,scrollbars=1,location=0,statusbar=1,menubar=0,resizable=1,directories=0,width=1100,height=500");' class="btn btn-primary btn-xs">
			<i class="fa fa-user fa-lg"></i>
			Call Management
		</button>
	<?php } ?>
	
	<?php //if( Yii::app()->user->account->checkPermission('reports_real_time_monitors_call_management_button','visible') ){ ?>
		<button onclick='window.open("<?php echo $this->createUrl('timeKeepingQueue/index'); ?>", "timeKeepingQueue", "titlebar=0,toolbar=0,scrollbars=1,location=0,statusbar=1,menubar=0,resizable=1,directories=0,width=1100,height=500");' class="btn btn-primary btn-xs">
			<i class="fa fa-list-ol fa-lg"></i>
			Time Off Request Queue
		</button>
	<?php // } ?>
	
	<br />
	<br />
	<br />
	<br />
</div>