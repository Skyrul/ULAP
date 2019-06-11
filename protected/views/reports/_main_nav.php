<?php
	$page = Yii::app()->controller->action->id;
?>

<ul class="nav nav-tabs">
	<?php if( Yii::app()->user->account->checkPermission('reports_reports_customer_contact_info','visible') ){ ?>
		<li class="<?php echo $page == 'customerContactInfo' ? 'active':'' ;?> ">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'customerContactInfo')); ?>">
				<i class="fa fa-user"></i>
				Customer Contact Info
			</a>
		</li>
	<?php } ?>
	
	<?php if( Yii::app()->user->account->checkPermission('reports_reports_customers_with_files','visible') ){ ?>
		<li class="<?php echo $page == 'customerWithFiles' ? 'active':'' ;?> ">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'customerWithFiles')); ?>">
				<i class="fa fa-folder-open"></i>
				Customers with Files
			</a>
		</li>
	<?php } ?>

	<?php if( Yii::app()->user->account->checkPermission('reports_reports_credit_card_transactions','visible') ){ ?>
		<li class="<?php echo $page == 'creditCardTransactions' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'creditCardTransactions')); ?>">
				<i class="fa fa-credit-card"></i>
				Credit Card Transactions
			</a>
		</li>
	<?php } ?>
	
	
	<?php /*<li class="<?php echo $page == 'billingResults' ? 'active':'' ;?>">
		<a href="<?php echo $this->createUrl('reports', array('page'=>'billingResults')); ?>">
			<i class="fa fa-credit-card"></i>
			Billing Results
		</a>
	</li>*/ ?>
	
	<?php if( Yii::app()->user->account->checkPermission('reports_reports_billing_projections','visible') ){ ?>
		<li class="<?php echo $page == 'billingProjections' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'billingProjections')); ?>">
				<i class="fa fa-pie-chart"></i>
				Billing Projections
			</a>
		</li>
	<?php } ?>
	
	<?php if( Yii::app()->user->account->checkPermission('reports_reports_contract_leads','visible') ){ ?>
		<li class="<?php echo $page == 'contractLeads' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'contractLeads')); ?>">
				<i class="fa fa-briefcase"></i>
				Contract Leads
			</a>
		</li>
	<?php } ?>
	
	<?php if( Yii::app()->user->account->checkPermission('reports_reports_agent_performance','visible') ){ ?>
		<li class="<?php echo $page == 'agentPerformance' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'agentPerformance')); ?>">
				<i class="fa fa-headphones"></i>
				Agent Performance
			</a>
		</li>
	<?php } ?>
	
	<?php if( Yii::app()->user->account->checkPermission('reports_reports_agent_performance_lite','visible') ){ ?>
		<li class="<?php echo $page == 'agentPerformanceLite' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'agentPerformanceLite')); ?>">
				<i class="fa fa-headphones"></i>
				Agent Performance Lite
			</a>
		</li>
	<?php } ?>
	
	<?php if( Yii::app()->user->account->checkPermission('reports_reports_queue_listing','visible') ){ ?>
		<li class="<?php echo $page == 'queueListing' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'queueListing')); ?>">
				<i class="fa fa-sort-numeric-asc"></i>
				Queue Listing
			</a>
		</li>
	<?php } ?>
	
	<?php /*<li class="<?php echo $page == 'stateFarm' ? 'active':'' ;?>">
		<a href="<?php echo $this->createUrl('reports', array('page'=>'stateFarm')); ?>">
			<i class="fa fa-building"></i>
			State Farm 
		</a>
	</li>*/?>
	
	<?php if( Yii::app()->user->account->checkPermission('reports_reports_confirmations','visible') ){ ?>
		<li class="<?php echo $page == 'pendingCalls' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'pendingCalls')); ?>">
				<i class="fa fa-calendar"></i>
				Confirmations
			</a>
		</li>
	<?php } ?>
	
	<?php if( Yii::app()->user->account->checkPermission('reports_reports_reschedules','visible') ){ ?>
		<li class="<?php echo $page == 'pendingCallsReschedule' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'pendingCallsReschedule')); ?>">
				<i class="fa fa-calendar"></i>
				Reschedules
			</a>
		</li>
	<?php } ?>
	
	<?php if( Yii::app()->user->account->checkPermission('reports_reports_employee_summary','visible') ){ ?>
		<li class="<?php echo $page == 'employeeSummary' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'employeeSummary')); ?>">
				<i class="fa fa-users"></i>
				Employee Summary
			</a>
		</li>
	<?php } ?>
	
	<?php /*<li class="<?php echo $page == 'namesWaiting' ? 'active':'' ;?>">
		<a href="<?php echo $this->createUrl('reports', array('page'=>'namesWaiting')); ?>">
			<i class="fa fa-list"></i>
			Names Waiting
		</a>
	</li>
	
	<li class="<?php echo $page == 'waxieCampaign' ? 'active':'' ;?>">
		<a href="<?php echo $this->createUrl('reports', array('page'=>'waxieCampaign')); ?>">
			<i class="fa fa-building"></i>
			Waxie Campaign
		</a>
	</li>*/ ?>
	
	<?php if( Yii::app()->user->account->checkPermission('reports_reports_generic_skill','visible') ){ ?>
		<li class="<?php echo ($page == 'genericSkill' || $reportName == 'genericSkill') ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'genericSkill')); ?>">
				<i class="fa fa-list-alt"></i>
				Generic Skill
			</a>
		</li>
	<?php } ?>
	
	<?php if( Yii::app()->user->account->checkPermission('reports_reports_change_log','visible') ){ ?>
		<li class="<?php echo $page == 'growth' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'growth')); ?>">
				<i class="fa fa-dollar"></i> Change Log
			</a>
		</li>
	<?php } ?>
	
	<?php if( Yii::app()->user->account->checkPermission('reports_reports_low_names','visible') ){ ?>
		<li class="<?php echo $page == 'lowNames' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'lowNames')); ?>">
				<i class="fa fa-arrow-down"></i>
				Low Names
			</a>
		</li>
	<?php } ?>
	
	<?php if( Yii::app()->user->account->checkPermission('reports_reports_impact','visible') ){ ?>
		<li class="<?php echo $page == 'impactReport' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'impactReport')); ?>">
				<i class="fa fa-area-chart"></i>
				Impact
			</a>
		</li>
	<?php } ?>
	
	
	<?php if( Yii::app()->user->account->checkPermission('reports_reports_list_import_log','visible') ){ ?>
		<li class="<?php echo $page == 'listImportLog' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'listImportLog')); ?>">
				<i class="fa fa-file-o"></i>
				List Import Log
			</a>
		</li>
	<?php } ?>
	
	<?php if( Yii::app()->user->account->checkPermission('reports_reports_agent_states','visible') ){ ?>
		<li class="<?php echo $page == 'agentStates' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'agentStates')); ?>">
				<i class="fa fa-clock-o"></i>
				Agent States
			</a>
		</li>
	<?php } ?>
	
	<?php if( Yii::app()->user->account->checkPermission('reports_reports_commision','visible') ){ ?>
		<li class="<?php echo $page == 'commision' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'commision')); ?>">
				<i class="fa fa-edit"></i>
				Commision
			</a>
		</li>
	<?php } ?>
	
	<?php if( Yii::app()->user->account->checkPermission('reports_reports_time_zones','visible') ){ ?>
		<li class="<?php echo $page == 'timezones' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'timezones')); ?>">
				<i class="fa fa-calendar-check-o"></i>
				Time Zones
			</a>
		</li>
	<?php } ?>
	
	<?php if( Yii::app()->user->account->checkPermission('reports_reports_time_off','visible') ){ ?>
		<li class="<?php echo $page == 'timeOff' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'timeOff')); ?>">
				<i class="fa fa-calendar-times-o"></i>
				Time Off
			</a>
		</li>
	<?php } ?>
	
	<?php if( Yii::app()->user->account->checkPermission('reports_reports_news','visible') ){ ?>
		<li class="<?php echo $page == 'news' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'news')); ?>">
				<i class="fa fa-newspaper-o"></i>
				News
			</a>
		</li>
	<?php } ?>
	
	<?php if( Yii::app()->user->account->checkPermission('reports_reports_learning_center_usage','visible') ){ ?>
		<li class="<?php echo $page == 'learningCenterUsage' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'learningCenterUsage')); ?>">
				<i class="fa fa-mouse-pointer"></i>
				Resource Center Report
			</a>
		</li>
	<?php } ?>
	
	<?php if( Yii::app()->user->account->checkPermission('reports_reports_training_library_usage','visible') ){ ?>
		<li class="<?php echo $page == 'trainingLibraryUsage' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'trainingLibraryUsage')); ?>">
				<i class="fa fa-mouse-pointer"></i>
				Training Library Usage
			</a>
		</li>
	<?php } ?>
	
	<?php if( Yii::app()->user->account->checkPermission('reports_reports_no_show_usage','visible') ){ ?>
		<li class="<?php echo $page == 'noShow' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'noShow')); ?>">
				<i class="fa fa-calendar-minus-o"></i>
				No Show
			</a>
		</li>
	<?php } ?>
	
	<?php /*if( Yii::app()->user->account->checkPermission('reports_reports_dnc','visible') ){ ?>
		<li class="<?php echo $page == 'dnc' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'dnc')); ?>">
				<i class="fa fa-ban"></i>
				Do Not Call
			</a>
		</li>
	<?php }*/ ?>
	
	<?php if( Yii::app()->user->account->checkPermission('reports_reports_dnc_master_list','visible') ){ ?>
		<li class="<?php echo $page == 'dncMasterList' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'dncMasterList')); ?>">
				<i class="fa fa-ban"></i>
				Master DNC Listing
			</a>
		</li>
	<?php } ?>
	
	<?php if( Yii::app()->user->account->checkPermission('reports_reports_custom_data','visible') ){ ?>
		<li class="<?php echo $page == 'customData' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'customData')); ?>">
				<i class="fa fa-reorder"></i>
				Custom Data
			</a>
		</li>
	<?php } ?>
	
	<?php if( Yii::app()->user->account->checkPermission('reports_reports_customer_company_wndnc','visible') ){ ?>
		<li class="<?php echo $page == 'customerCompanyDncWn' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'customerCompanyDncWn')); ?>">
				<i class="fa fa-folder"></i>
				Company-Customer DNC/WN
			</a>
		</li>
	<?php } ?>
	
	<?php if( Yii::app()->user->account->checkPermission('reports_reports_cellphone_scrub','visible') ){ ?>
		<li class="<?php echo $page == 'cellphoneScrub' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'cellphoneScrub')); ?>">
				<i class="fa fa-filter"></i>
				Cellphone Scrub Report
			</a>
		</li>
	<?php } ?>
	
	<?php if( Yii::app()->user->account->checkPermission('reports_reports_cellphone_scrub','visible') ){ ?>
		<li class="<?php echo $page == 'possibleNow' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'possibleNow')); ?>">
				<i class="fa fa-filter"></i>
				Possible Now Report
			</a>
		</li>
	<?php } ?>
	
	<?php if( Yii::app()->user->account->checkPermission('reports_reports_document_type','visible') ){ ?>
		<li class="<?php echo $page == 'documentType' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'documentType')); ?>">
				<i class="fa fa-folder-open"></i>
				Document Type Report
			</a>
		</li>
	<?php } ?>
	
	<?php if( Yii::app()->user->account->checkPermission('reports_reports_cancellation','visible') ){ ?>
		<li class="<?php echo $page == 'cancellation' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'cancellation')); ?>">
				<i class="fa fa-envelope"></i>
				Cancellation Report
			</a>
		</li>
	<?php } ?>
	
	<?php if( Yii::app()->user->account->checkPermission('reports_reports_extra_appt','visible') ){ ?>
		<li class="<?php echo $page == 'extraAppt' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'extraAppt')); ?>">
				<i class="fa fa-plus"></i>
				Extra Appt
			</a>
		</li>
	<?php } ?>
	
	<?php if( Yii::app()->user->account->checkPermission('reports_reports_master_schedule','visible') ){ ?>
		<li class="<?php echo $page == 'masterSchedule' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'masterSchedule')); ?>">
				<i class="fa fa-check-square-o"></i>
				Master Schedule
			</a>
		</li>
	<?php } ?>
	

	<li class="<?php echo $page == 'billingCreditMonitor' ? 'active':'' ;?>">
		<a href="<?php echo $this->createUrl('reports', array('page'=>'billingCreditMonitor')); ?>">
			<i class="fa fa-check"></i>
			Billing Credit Monitor
		</a>
	</li>
	
	<li class="<?php echo $page == 'remainingApptToSet' ? 'active':'' ;?>">
		<a href="<?php echo $this->createUrl('reports', array('page'=>'remainingApptToSet')); ?>">
			<i class="fa fa-list-alt"></i>
			Remaining Appts to Set
		</a>
	</li>
	
	<li class="<?php echo ($page == 'aaaReport' || $reportName == 'aaaReport') ? 'active':'' ;?>">
		<a href="<?php echo $this->createUrl('reports', array('page'=>'aaaReport')); ?>">
			<i class="fa fa-font"></i>AA Report
		</a>
	</li>
	
	<li class="<?php echo ($page == 'yoyTrends' || $reportName == 'yoyTrends') ? 'active':'' ;?>">
		<a href="<?php echo $this->createUrl('yoyTrends'); ?>">
			<i class="fa fa-line-chart"></i> YOY Trends
		</a>
	</li>

</ul>