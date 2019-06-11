<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	
	$cs = Yii::app()->clientScript;
	
	$cs->registerCssFile($baseUrl.'/template_assets/css/jquery-ui.css');
	
	// $cs->registerCssFile($baseUrl.'/css/select2.min.css');
	
	$cs->registerCss(uniqid(), '
	
		.ace-settings-box.open { 
		
			width: 500px !important;
			height: 390px;
		}
		
		.tab-content .nav-tabs li{ width: 220px !important; }
	
	');
	
	$cs->registerScript(uniqid(), '
	
		$(document).ready( function(){
			
			$(".datepicker").datepicker({
				autoclose: true,
				todayHighlight: true
			});
			
			// $(".select2").css("width","300px").select2({allowClear:true});  
			
		});
	
	', CClientScript::POS_END);
?>

<?php 
	if( $page == 'impactReport' )
	{		
		$month1 = ImpactReport::model()->findByPk(1);
		$month2 = ImpactReport::model()->findByPk(2);
		$month3 = ImpactReport::model()->findByPk(3);
		$month4 = ImpactReport::model()->findByPk(4);
		$month5 = ImpactReport::model()->findByPk(5);
		$month6 = ImpactReport::model()->findByPk(6);
		$month7 = ImpactReport::model()->findByPk(7);
		$month8 = ImpactReport::model()->findByPk(8);
		
		if( $month1 && $month2 && $month3 && $month4 && $month5 && $month6 && $month7 && $month8 )
		{
		?>
			<script src="https://code.highcharts.com/highcharts.js"></script>
			<script src="https://code.highcharts.com/modules/exporting.js"></script>

			<script>
				$(function () {
					$('#container').highcharts({
						chart: {
							type: 'area'
						},
						title: {
							text: 'Impact'
						},
						subtitle: {
							text: '*Month to Date collected'
						},
						credits: {
							enabled: false
						},
						xAxis: {
							categories: [
								'<?php echo $month1->month_name; ?>', 
								'<?php echo $month2->month_name; ?>', 
								'<?php echo $month3->month_name; ?>', 
								'<?php echo $month4->month_name; ?>', 
								'<?php echo $month5->month_name; ?>', 
								'<?php echo $month6->month_name; ?>', 
								'<?php echo $month7->month_name; ?>',
								'<?php echo $month8->month_name; ?>'
							],
							tickmarkPlacement: 'on',
							title: {
								enabled: false
							}
						},
						yAxis: {
							title: {
								text: ''
							},
							labels: {
								formatter: function () {
									
									if( this.value > 0 )
									{
										var value = '$' + this.value.toFixed(2).replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
									}
									else
									{
										var value = '$-';
									}
									
									return value;
								}
							}
						},
						tooltip: {
							shared: true,
							// valueSuffix: ' millions',
							valuePrefix: '$',
						},
						plotOptions: {
							area: {
								stacking: 'normal', //normal, percentage
								lineColor: '#666666',
								lineWidth: 1,
								marker: {
									lineWidth: 1,
									lineColor: '#666666'
								}
							}
						},
						series: [
							{
								name: 'Collected*',
								data: [
									<?php echo $month1->actual; ?>,
									<?php echo $month2->actual; ?>,
									<?php echo $month3->actual; ?>,
									<?php echo $month4->actual; ?>,
									<?php echo $month5->actual; ?>,
									<?php echo $month6->actual; ?>,
									<?php echo $month7->actual; ?>,
									<?php echo $month8->actual; ?>
								],
								color: '#ED7E30',
							},
							{
								name: 'Remaining',
								data: [
									<?php echo $month1->projected; ?>,
									<?php echo $month2->projected; ?>,
									<?php echo $month3->projected; ?>,
									<?php echo $month4->projected; ?>,
									<?php echo $month5->projected; ?>,
									<?php echo $month6->projected; ?>,
									<?php echo $month7->projected; ?>,
									<?php echo $month8->projected; ?>
								],
								color: '#5C9BD5',
							}
						]
					});
				});
			</script>
		<?php 
		}
	}
?>

<?php 
	if( !empty($_GET['page']) && isset($_GET['debug']) )
	{
	?>
		<div id="ace-settings-container" class="ace-settings-container">
			<div id="ace-settings-btn" class="btn btn-app btn-xs btn-warning ace-settings-btn">
				<i class="ace-icon fa fa-cog bigger-130"></i>
			</div>

			<div id="ace-settings-box" class="ace-settings-box clearfix">

				<div class="page-header center">
					<h1>Auto Email Settings</h1>
				</div>
				
				<div class="row" style="margin-bottom:5px;">
					<label for="form-field-8">Email Address</label>
					<textarea placeholder="" class="form-control"></textarea>
				</div>
				
				<div class="spave-6"></div>
			
				<?php foreach( array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday' ) as $day ): ?>
				
				<div class="row" style="margin-bottom:5px;">
					<div class="col-sm-6">
						<input type="checkbox" class="ace ace-checkbox-2">
						<label for="ace-settings-navbar" class="lbl"> <?php echo $day; ?></label>
					</div>
					
					<div class="col-sm-6">
						<div class="input-group bootstrap-timepicker">
							<input type="text" class="form-control" id="timepicker1" style="height:25px;">
							<span class="input-group-addon" style="padding:0px 12px;">
								<i class="fa fa-clock-o bigger-110"></i>
							</span>
						</div>
					</div>
				</div>
				
				<?php endforeach; ?>
				
				<div class="row" style="margin-bottom:5px;">
					<div class="col-sm-6">
						<input type="checkbox" class="ace ace-checkbox-2">
						<label for="ace-settings-navbar" class="lbl"> End of Month</label>
					</div>
					
					<div class="col-sm-6">
						<div class="input-group bootstrap-timepicker">
							<input type="text" class="form-control" id="timepicker1" style="height:25px;" value="11:59 PM" readonly>
							<span class="input-group-addon" style="padding:0px 12px;">
								<i class="fa fa-clock-o bigger-110"></i>
							</span>
						</div>
					</div>
				</div>
				
				<div class="row center" style="margin-top:18px;">
					<button class="btn btn-mini btn-primary"><i class="fa fa-check"></i> Save</button>
				</div>
				
			</div><!-- /.ace-settings-box -->
		</div>
	<?php
	}
?>

<div class="page-header">
	<h1>Reports</h1>
</div>

<div class="tabbable tabs-left">
	
	<ul class="nav nav-tabs">
		<li><a href="<?php echo $this->createUrl('index'); ?>">Real-Time Monitors</a></li>
		<li class="active"><a href="<?php echo $this->createUrl('reports'); ?>">Reports</a></li>
		<li><a href="<?php echo $this->createUrl('callerIdListing'); ?>">Caller ID Listing</a></li>
		<li><a href="<?php echo $this->createUrl('callHistoryMonitor'); ?>">Call History Monitor</a></li>
	</ul>
	
</div>

<div class="tab-content text">

	<ul class="nav nav-tabs">
		<li class="<?php echo $page == 'customerContactInfo' ? 'active':'' ;?> ">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'customerContactInfo')); ?>">
				<i class="fa fa-user"></i>
				Customer Contact Info
			</a>
		</li>
		
		<li class="<?php echo $page == 'customerWithFiles' ? 'active':'' ;?> ">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'customerWithFiles')); ?>">
				<i class="fa fa-folder-open"></i>
				Customers with Files
			</a>
		</li>

		<li class="<?php echo $page == 'creditCardTransactions' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'creditCardTransactions')); ?>">
				<i class="fa fa-credit-card"></i>
				Credit Card Transactions
			</a>
		</li>
		
		
		<?php /*<li class="<?php echo $page == 'billingResults' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'billingResults')); ?>">
				<i class="fa fa-credit-card"></i>
				Billing Results
			</a>
		</li>*/ ?>
		
		<li class="<?php echo $page == 'billingProjections' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'billingProjections')); ?>">
				<i class="fa fa-line-chart"></i>
				Billing Projections
			</a>
		</li>
		
		<li class="<?php echo $page == 'contractLeads' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'contractLeads')); ?>">
				<i class="fa fa-briefcase"></i>
				Contract Leads
			</a>
		</li>
		
		<li class="<?php echo $page == 'agentPerformance' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'agentPerformance')); ?>">
				<i class="fa fa-headphones"></i>
				Agent Performance
			</a>
		</li>
		
		<li class="<?php echo $page == 'agentPerformanceLite' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'agentPerformanceLite')); ?>">
				<i class="fa fa-headphones"></i>
				Agent Performance Lite
			</a>
		</li>
		
		<li class="<?php echo $page == 'queueListing' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'queueListing')); ?>">
				<i class="fa fa-reorder"></i>
				Queue Listing
			</a>
		</li>
		
		<?php /*<li class="<?php echo $page == 'stateFarm' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'stateFarm')); ?>">
				<i class="fa fa-building"></i>
				State Farm 
			</a>
		</li>*/?>
		
		<li class="<?php echo $page == 'pendingCalls' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'pendingCalls')); ?>">
				<i class="fa fa-calendar"></i>
				Confirmations
			</a>
		</li>
		
		<li class="<?php echo $page == 'pendingCallsReschedule' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'pendingCallsReschedule')); ?>">
				<i class="fa fa-calendar"></i>
				Reschedules
			</a>
		</li>
		
		<li class="<?php echo $page == 'employeeSummary' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'employeeSummary')); ?>">
				<i class="fa fa-users"></i>
				Employee Summary
			</a>
		</li>
		
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
		
		<li class="<?php echo $page == 'genericSkill' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'genericSkill')); ?>">
				<i class="fa fa-list-alt"></i>
				Generic Skill
			</a>
		</li>
		<li class="<?php echo $page == 'growth' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'growth')); ?>">
				<i class="fa fa-dollar"></i> Change Log
			</a>
		</li>
		<li class="<?php echo $page == 'lowNames' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'lowNames')); ?>">
				<i class="fa fa-arrow-down"></i>
				Low Names
			</a>
		</li>
		<li class="<?php echo $page == 'impactReport' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'impactReport')); ?>">
				<i class="fa fa-area-chart"></i>
				Impact
			</a>
		</li>
		<li class="<?php echo $page == 'listImportLog' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'listImportLog')); ?>">
				<i class="fa fa-file-o"></i>
				List Import Log
			</a>
		</li>
		<li class="<?php echo $page == 'agentStates' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'agentStates')); ?>">
				<i class="fa fa-clock-o"></i>
				Agent States
			</a>
		</li>
		<li class="<?php echo $page == 'commision' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'commision')); ?>">
				<i class="fa fa-edit"></i>
				Commision
			</a>
		</li>
		<li class="<?php echo $page == 'timezones' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'timezones')); ?>">
				<i class="fa fa-calendar-check-o"></i>
				Time Zones
			</a>
		</li>
		<li class="<?php echo $page == 'timeOff' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'timeOff')); ?>">
				<i class="fa fa-calendar-times-o"></i>
				Time Off
			</a>
		</li>
		<li class="<?php echo $page == 'news' ? 'active':'' ;?>">
			<a href="<?php echo $this->createUrl('reports', array('page'=>'news')); ?>">
				<i class="fa fa-newspaper-o"></i>
				News
			</a>
		</li>
	</ul>

	<div class="hr hr-18 hr-double dotted"></div>

	<div class="row">
		<?php 
			if( $page != '' )
			{
				if( $page == 'creditCardTransactions' )
				{
				?>
					<div class="col-sm-6">
						<form action="" method="post">
							Date:
							<input type="text" name="dateFilterStart" class="datepicker" value="<?php echo $dateFilterStart; ?>" placeholder="From">
							<input type="text" name="dateFilterEnd" class="datepicker" value="<?php echo $dateFilterEnd; ?>" placeholder="To">
							
							<button type="submit" class="btn btn-xs btn-primary">Execute <i class="fa fa-arrow-right"></i> </button>
						</form>
					</div>
					
					<div class="col-sm-6 text-right">
						<?php echo CHtml::link('<i class="fa fa-file-excel-o"></i> Export to Excel', array('export', 'page'=>$page, 'selectedSkills'=>'', 'contractId'=>$contractId, 'dateFilterStart'=>$dateFilterStart, 'dateFilterEnd'=>$dateFilterEnd), array('class'=>'btn btn-yellow btn-sm')); ?>
					</div>
				<?php 
				}
				elseif( $page == 'billingResults' )
				{
				?>
					<div class="col-sm-6">
						<form action="" method="post">
							Date:
							<input type="text" name="dateFilterStart" class="datepicker" value="<?php echo $dateFilterStart; ?>" placeholder="From">
							<input type="text" name="dateFilterEnd" class="datepicker" value="<?php echo $dateFilterEnd; ?>" placeholder="To">
							
							<button type="submit" class="btn btn-xs btn-primary">Execute <i class="fa fa-arrow-right"></i> </button>
						</form>
					</div>
					
					<div class="col-sm-6 text-right">
						<?php echo CHtml::link('<i class="fa fa-file-excel-o"></i> Export to Excel', array('export', 'page'=>$page, 'selectedSkills'=>'', 'contractId'=>$contractId, 'dateFilterStart'=>$dateFilterStart, 'dateFilterEnd'=>$dateFilterEnd), array('class'=>'btn btn-yellow btn-sm')); ?>
					</div>
				<?php 
				}
				elseif( $page == 'contractLeads' )
				{
				?>
				
					<form action="" method="post">
						<div class="col-sm-2">
							<?php echo CHtml::dropDownList('contractId', $contractId, $contractOptions, array('prompt'=>'- Select -')); ?>
						</div>
					
						<div class="col-sm-5">
							
							Date:
							<input type="text" name="dateFilterStart" class="datepicker" value="<?php echo $dateFilterStart; ?>" placeholder="From">
							<input type="text" name="dateFilterEnd" class="datepicker" value="<?php echo $dateFilterEnd; ?>" placeholder="To">
							
							<button type="submit" class="btn btn-xs btn-primary">Execute <i class="fa fa-arrow-right"></i> </button>
						</div>
					</form>
					
					<div class="col-sm-5 text-right">
						<?php echo CHtml::link('<i class="fa fa-file-excel-o"></i> Export to Excel', array('export', 'page'=>$page, 'selectedSkills'=>'', 'contractId'=>$contractId, 'dateFilterStart'=>$dateFilterStart, 'dateFilterEnd'=>$dateFilterEnd), array('class'=>'btn btn-yellow btn-sm')); ?>
					</div>
				
				<?php
				}
				elseif( $page == 'queueListing' )
				{
				?>
					<div class="col-sm-12 text-right">
						<?php echo CHtml::link('<i class="fa fa-file-excel-o"></i> Export to Excel', array('export', 'page'=>$page, 'selectedSkills'=>isset($_POST['skillIds']) ? implode(', ', $_POST['skillIds']) : '', 'contractId'=>$contractId, 'dateFilterStart'=>$dateFilterStart, 'dateFilterEnd'=>$dateFilterEnd), array('class'=>'btn btn-yellow btn-sm')); ?>
					</div>
				
				<?php
				}
				elseif( $page == 'stateFarm' )
				{
				?>
					<form action="" method="post">
						<div class="col-sm-5">
							
							Date:
							<input type="text" name="dateFilterStart" class="datepicker" value="<?php echo $dateFilterStart; ?>" placeholder="From">
							<input type="text" name="dateFilterEnd" class="datepicker" value="<?php echo $dateFilterEnd; ?>" placeholder="To">
							
							<button type="submit" class="btn btn-xs btn-primary">Execute <i class="fa fa-arrow-right"></i> </button>
						</div>
					</form>
					
					<div class="col-sm-7 text-right">
						<?php echo CHtml::link('<i class="fa fa-file-excel-o"></i> Export to Excel', array('export', 'page'=>$page, 'selectedSkills'=>isset($_POST['skillIds']) ? implode(', ', $_POST['skillIds']) : '', 'contractId'=>$contractId, 'dateFilterStart'=>$dateFilterStart, 'dateFilterEnd'=>$dateFilterEnd), array('class'=>'btn btn-yellow btn-sm')); ?>
					</div>
				
				<?php
				}
				elseif( $page == 'customerWithFiles' )
				{
				?>

				<?php
				}
				elseif( $page == 'pendingCalls' )
				{
				?>
					<div class="col-sm-12 text-right">
						<?php echo CHtml::link('<i class="fa fa-file-excel-o"></i> Export to Excel', array('export', 'page'=>$page), array('class'=>'btn btn-yellow btn-sm')); ?>
					</div>
				<?php
				}
				elseif( $page == 'pendingCallsReschedule' )
				{
				?>
					<div class="col-sm-12 text-right">
						<?php echo CHtml::link('<i class="fa fa-file-excel-o"></i> Export to Excel', array('export', 'page'=>$page), array('class'=>'btn btn-yellow btn-sm')); ?>
					</div>
				<?php
				}
				elseif( $page == 'agentPerformanceLite' )
				{
				?>
					<form action="" method="post">
						<div class="col-sm-1 text-right" style="margin: 8px 0 0 -40px;">Date: </div>
						
						<div class="col-sm-9">
							<div class="pull-left">
								<div class="pull-left">
									<div class="input-group">
										<span class="input-group-addon">
											<i class="fa fa-calendar bigger-110"></i>
										</span>
										<input type="text" name="dateFilterStart" class="datepicker" value="<?php echo isset($_POST['dateFilterStart']) ? $_POST['dateFilterStart'] : date('m/d/Y'); ?>" placeholder="From" style="width:100px;">
									</div>
								</div>

								<div class="pull-left" style="margin-left:5px;">
									<div class="input-group">	
										<span class="input-group-addon">
											<i class="fa fa-clock-o bigger-110"></i>
										</span>
										<input type="text" name="dateFilterStartTime" value="<?php echo isset($_POST['dateFilterStartTime']) ? $_POST['dateFilterStartTime'] : '8:00 AM'; ?>" style="width:75px;">
									</div>
								</div>
							</div>
							
							<div class="pull-left" style="margin-left:20px;">
								<div class="pull-left">
									<div class="input-group">
										<span class="input-group-addon">
											<i class="fa fa-calendar bigger-110"></i>
										</span>
										<input type="text" name="dateFilterEnd" class="datepicker" value="<?php echo isset($_POST['dateFilterEnd']) ? $_POST['dateFilterEnd'] : date('m/d/Y'); ?>" placeholder="To" style="width:100px;">
									</div>
								</div>
								
								<div class="pull-left" style="margin-left:5px;">
									<div class="input-group">	
										<span class="input-group-addon">
											<i class="fa fa-clock-o bigger-110"></i>
										</span>
										<input type="text" name="dateFilterEndTime" value="<?php echo isset($_POST['dateFilterEndTime']) ? $_POST['dateFilterEndTime'] : date('g:i A', strtotime('-1 hour')); ?>" style="width:75px;">
									</div>
								</div>
							</div>
							
							<div class="pull-left" style="margin:2px 0 0 5px;">
								<button type="submit" class="btn btn-xs btn-primary">Execute <i class="fa fa-arrow-right"></i> </button>
							</div>
						</div>


						<div class="col-sm-2 text-right" style="margin-left:40px;">
							<?php 
								echo CHtml::link('<i class="fa fa-file-excel-o"></i> Export to Excel', array(
									'export', 
									'page'=>$page, 
									'dateFilterStart'=>$dateFilterStart, 
									'dateFilterEnd'=>$dateFilterEnd,
									'dateFilterStartTime'=>isset($_POST['dateFilterStartTime']) ? $_POST['dateFilterStartTime'] : '',
									'dateFilterEndTime'=>isset($_POST['dateFilterEndTime']) ? $_POST['dateFilterEndTime'] : '',
								), array('class'=>'btn btn-yellow btn-sm')
								); ?>
						</div>
					
					</form>
				
				<?php
				}
				elseif( $page == 'waxieCampaign' )
				{
				?>
				
					<form action="" method="post">
						<div class="col-sm-5">
							
							Date:
							<input type="text" name="dateFilterStart" class="datepicker" value="<?php echo $dateFilterStart; ?>" placeholder="From">
							<input type="text" name="dateFilterEnd" class="datepicker" value="<?php echo $dateFilterEnd; ?>" placeholder="To">
							
							<button type="submit" class="btn btn-xs btn-primary">Execute <i class="fa fa-arrow-right"></i> </button>
						</div>
					</form>
				
				<?php
				}
				elseif( $page == 'genericSkill' )
				{
				?>
				
					<form action="" method="post">
						<div class="col-sm-3">
							<?php echo CHtml::dropDownList('skillId', isset($_POST['skillId']) ? $_POST['skillId'] : '', $skillOptions, array('prompt'=>'- Select -')); ?>
						</div>
					
						<div class="col-sm-5">
							
							Date:
							<input type="text" name="dateFilterStart" class="datepicker" value="<?php echo $dateFilterStart; ?>" placeholder="From">
							<input type="text" name="dateFilterEnd" class="datepicker" value="<?php echo $dateFilterEnd; ?>" placeholder="To">
							
							<button type="submit" class="btn btn-xs btn-primary">Execute <i class="fa fa-arrow-right"></i> </button>
						</div>
					</form>
					
					<div class="col-sm-4 text-right">
						<?php echo CHtml::link('<i class="fa fa-file-excel-o"></i> Export to Excel', array('export', 'page'=>$page, 'selectedSkills'=>isset($_POST['skillId']) ? $_POST['skillId'] : '', 'contractId'=>$contractId, 'dateFilterStart'=>$dateFilterStart, 'dateFilterEnd'=>$dateFilterEnd), array('class'=>'btn btn-yellow btn-sm')); ?>
					</div>
				
				<?php
				}
				elseif( $page == 'growth' )
				{
				?>
				
					<form action="" method="post">
						<div class="col-sm-8">
							
							Date:
							<input type="text" name="dateFilterStart" class="datepicker" value="<?php echo $dateFilterStart; ?>" placeholder="From">
							<input type="text" name="dateFilterEnd" class="datepicker" value="<?php echo $dateFilterEnd; ?>" placeholder="To">
							
							<button type="submit" class="btn btn-xs btn-primary">Execute <i class="fa fa-arrow-right"></i> </button>
						</div>
					</form>
					
					<div class="col-sm-4 text-right">
						<?php echo CHtml::link('<i class="fa fa-file-excel-o"></i> Export to Excel', array('export', 'page'=>$page, 'selectedSkills'=>isset($_POST['skillId']) ? $_POST['skillId'] : '', 'contractId'=>$contractId, 'dateFilterStart'=>$dateFilterStart, 'dateFilterEnd'=>$dateFilterEnd), array('class'=>'btn btn-yellow btn-sm')); ?>
					</div>
				
				<?php
				}
				elseif( $page == 'lowNames' )
				{
				?>

					<div class="col-sm-8"></div>
					
					<div class="col-sm-4 text-right">
						<?php echo CHtml::link('<i class="fa fa-file-excel-o"></i> Export to Excel', array('export', 'page'=>$page, 'selectedSkills'=>isset($_POST['skillId']) ? $_POST['skillId'] : '', 'contractId'=>$contractId, 'dateFilterStart'=>$dateFilterStart, 'dateFilterEnd'=>$dateFilterEnd), array('class'=>'btn btn-yellow btn-sm')); ?>
					</div>
				
				<?php				
				}
				elseif( $page == 'billingProjections' )
				{
					$billingPeriodOptions = array();

					$startDate = strtotime(date('Y-01-01'));
					$endDate = strtotime(date('Y-12-01'));
					
					while( $startDate <= $endDate ) 
					{
						$billingPeriodOptions[date('Y-m-01', $startDate)] = date('M Y', $startDate);
					
						$addTime   = strtotime('+1 month', $startDate);
						$diff       = $addTime-$startDate;
						
						$startDate += $diff;
					}

				?>
					<div class="col-sm-8">
						<form action="" method="post">
								
							<?php echo CHtml::dropDownList('billing_period', isset($_REQUEST['billing_period']) ? $_REQUEST['billing_period'] : '', $billingPeriodOptions, array('prompt'=>'- Select -')); ?>
							
							<button type="submit" class="btn btn-xs btn-primary">Execute <i class="fa fa-arrow-right"></i> </button>
										
						</form>
					</div>
					
					<div class="col-sm-4 text-right">
						<?php echo CHtml::link('<i class="fa fa-file-excel-o"></i> Export to Excel', array('export', 'page'=>$page, 'billing_period'=>isset($_POST['billing_period']) ? $_POST['billing_period'] : ''), array('class'=>'btn btn-yellow btn-sm')); ?>
					</div>
					
				<?php
				}
				elseif( $page == 'listImportLog' )
				{
				?>
				
					<form action="" method="post">
						<div class="col-sm-10">
							
							Date:
							<input type="text" name="dateFilterStart" class="datepicker" value="<?php echo $dateFilterStart; ?>" placeholder="From">
							<input type="text" name="dateFilterEnd" class="datepicker" value="<?php echo $dateFilterEnd; ?>" placeholder="To">
							
							<button type="submit" class="btn btn-xs btn-primary">Execute <i class="fa fa-arrow-right"></i> </button>
						</div>
						<div class="col-sm-2 text-right">
							<?php echo CHtml::link('<i class="fa fa-file-excel-o"></i> Export to Excel', array('export', 'page'=>$page, 'dateFilterStart'=>$dateFilterStart, 'dateFilterEnd'=>$dateFilterEnd), array('class'=>'btn btn-yellow btn-sm')); ?>
						</div>
					</form>
				
				<?php
				}
				elseif( $page == 'agentStates' )
				{
				?>
				
					<form action="" method="post">
						<div class="col-sm-10">
							
							Date:
							<input type="text" name="dateFilterStart" class="datepicker" value="<?php echo $dateFilterStart; ?>" placeholder="From">
							<input type="text" name="dateFilterEnd" class="datepicker" value="<?php echo $dateFilterEnd; ?>" placeholder="To">
							
							<button type="submit" class="btn btn-xs btn-primary">Execute <i class="fa fa-arrow-right"></i> </button>
						</div>
						<div class="col-sm-2 text-right">
							<?php echo CHtml::link('<i class="fa fa-file-excel-o"></i> Export to Excel', array('export', 'page'=>$page, 'dateFilterStart'=>$dateFilterStart, 'dateFilterEnd'=>$dateFilterEnd), array('class'=>'btn btn-yellow btn-sm')); ?>
						</div>
					</form>
				
				<?php
				}
				elseif( $page == 'commision' )
				{
				?>
				
					<form action="" method="post">
						<div class="col-sm-10">
							
							Date:
							<input type="text" name="dateFilterStart" class="datepicker" value="<?php echo $dateFilterStart; ?>" placeholder="From">
							<input type="text" name="dateFilterEnd" class="datepicker" value="<?php echo $dateFilterEnd; ?>" placeholder="To">
							
							<button type="submit" class="btn btn-xs btn-primary">Execute <i class="fa fa-arrow-right"></i> </button>
						</div>
						<div class="col-sm-2 text-right">
							<?php echo CHtml::link('<i class="fa fa-file-excel-o"></i> Export to Excel', array('export', 'page'=>$page, 'dateFilterStart'=>$dateFilterStart, 'dateFilterEnd'=>$dateFilterEnd), array('class'=>'btn btn-yellow btn-sm')); ?>
						</div>
					</form>
				
				<?php
				}
				else
				{
				?>
					<div class="col-sm-12 text-right">
						<?php echo CHtml::link('<i class="fa fa-file-excel-o"></i> Export to Excel', array('export', 'page'=>$page, 'selectedSkills'=>'', 'contractId'=>$contractId, 'dateFilterStart'=>$dateFilterStart, 'dateFilterEnd'=>$dateFilterEnd), array('class'=>'btn btn-yellow btn-sm')); ?>
					</div>
				<?php
				}
			}
		?>		
	</div>
	
	<div class="space-12"></div>

	<div class="row">
		<div class="col-sm-12">
			<?php 
				if( $page == 'employeeSummary' || $page == 'queueListing' || $page == 'customerWithFiles' || ($page == 'stateFarm' && $dateFilterStart != '' ) || ( $page == 'creditCardTransactions' && $dateFilterStart != '' ) || ( $page == 'contractLeads' && $dateFilterStart != '' ))
				{
					$this->widget('zii.widgets.CListView', array(
						'id'=>'leadList',
						'dataProvider'=>$dataProvider,
						'itemView' => $itemView,
						'viewData' => array('dateFilterStart'=>$dateFilterStart, 'dateFilterEnd'=>$dateFilterEnd),
						'summaryText' => '{start} - {end} of {count}',
						'emptyText' => '<div class="col-sm-12">No results found.</div>',
						'template'=>'
							<table id="leadsTbl" class="table table-striped table-bordered table-condensed table-hover">
								{items}  
							</table> 
						',
						'pagerCssClass' => 'pagination', 
						'pager' => array(
							'header' => '',
						),
					)); 
				}
				
				if( ($page == 'billingResults' ) && $dateFilterStart != '' )
				{
					$this->widget('zii.widgets.CListView', array(
						'id'=>'leadList',
						'dataProvider'=>$dataProvider,
						'itemView' => $itemView,
						'viewData' => array('dateFilterStart'=>$dateFilterStart, 'dateFilterEnd'=>$dateFilterEnd),
						'summaryText' => '{start} - {end} of {count}',
						'emptyText' => '<div class="col-sm-12">No results found.</div>',
						'template'=>'
							<table id="leadsTbl" class="table table-striped table-bordered table-condensed table-hover">
								{items}  
							</table> 
						',
						'pagerCssClass' => 'pagination', 
						'pager' => array(
							'header' => '',
						),
					)); 
				}
							
				if( $page == 'pendingCalls' )
				{
					function checkTimeZone($customerSkill, $type='customer', $lead=null)
					{
						date_default_timezone_set('America/Denver');
						
						$nextAvailableCallingTime = '';
						
						$customer = $customerSkill->customer;
						
						$skillScheduleHolder = array();
							
						$currentDateTime = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('America/Denver'));
						// $currentDateTime->setTimezone(new DateTimeZone('America/Denver')); 
						
						
						//temp code to force certain customers to get no dials 
						// $floridaAreaCodes = array('239', '305', '321', '352', '386', '407', '561', '727', '754', '772', '786', '813', '850', '863', '904', '941', '954');
							
						// $georgiaArecodeCodes = array('229', '404', '470', '478', '678', '706', '762', '770', '912');
						
						// $southCarolinaAreaCodes = array('803', '843', '864');
						
						// if( in_array(substr($customer->phone, 1, 3), $floridaAreaCodes) )
						// {
							// return 'Next Shift';
						// }
						
						// if( in_array(substr($customer->phone, 1, 3), $georgiaArecodeCodes) )
						// {
							// return 'Next Shift';
						// }
						
						// if( in_array(substr($customer->phone, 1, 3), $southCarolinaAreaCodes) )
						// {
							// return 'Next Shift';
						// }
						//end of temp code
						
						
						if( $customerSkill->is_custom_call_schedule == 1 )
						{
							$customCallSchedules = CustomerSkillSchedule::model()->findAll(array(
								'condition' => 'customer_skill_id = :customer_skill_id AND schedule_day = :schedule_day',
								'params' => array(
									':customer_skill_id' => $customerSkill->id,
									':schedule_day' => date('N'),
								),
							));
							
							if( $customCallSchedules )
							{
								foreach( $customCallSchedules as $customCallSchedule )
								{
									$skillScheduleHolder[$customer->id][$customCallSchedule->id]['schedule_start'] = date('g:i A', strtotime($customCallSchedule->schedule_start));
									$skillScheduleHolder[$customer->id][$customCallSchedule->id]['schedule_end'] = date('g:i A', strtotime($customCallSchedule->schedule_end));
								}
							}
						}
						else
						{	
							$skillSchedules = SkillSchedule::model()->findAll(array(
								// 'condition' => 'skill_id = :skill_id AND schedule_day = :schedule_day AND status=1 AND is_deleted=0',
								'condition' => 'skill_id = :skill_id AND schedule_day = :schedule_day',
								'params' => array(
									'skill_id' => $customerSkill->skill_id,
									':schedule_day' => date('N'),
								),
							));

							foreach($skillSchedules as $skillSchedule)
							{
								$skillScheduleHolder[$customer->id][$skillSchedule->id]['schedule_start'] = date('g:i A', strtotime($skillSchedule->schedule_start));
								$skillScheduleHolder[$customer->id][$skillSchedule->id]['schedule_end'] = date('g:i A', strtotime($skillSchedule->schedule_end));
							}
						}
					
						
						if( isset($skillScheduleHolder[$customer->id]) )
						{	
							foreach($skillScheduleHolder[$customer->id] as $sched)
							{	
								if( $type == 'customer' )
								{
									$timeZone = $customer->getTimeZone();
								}
								else
								{
									if( !empty($lead->timezone) )
									{
										$timeZone = $lead->timezone;
									}
									else
									{
										$timeZone = $customer->getTimeZone();
									}
								}
								
								if( !empty($timeZone) )
								{
									$timeZone = timezone_name_from_abbr($timeZone);
																	
									// if( strtoupper($lead->timezone) == 'AST' )
									// {
										// $timeZone = 'America/Puerto_Rico';
									// }
									
									// if( strtoupper($lead->timezone) == 'ADT' )
									// {
										// $timeZone = 'America/Halifax'; 
									// }
									
									if( $type == 'customer' )
									{
										$nextAvailableCallingTimeStart = new DateTime($sched['schedule_start'], new DateTimeZone($timeZone) );
										$nextAvailableCallingTimeEnd = new DateTime($sched['schedule_end'], new DateTimeZone($timeZone) );
										
										$nextAvailableCallingTimeStart->setTimezone(new DateTimeZone('America/Denver'));
										$nextAvailableCallingTimeEnd->setTimezone(new DateTimeZone('America/Denver'));
										
										$nextAvailableCallingTime = $nextAvailableCallingTimeStart->format('g:i A');

										if( strtotime($currentDateTime->format('g:i A')) >= strtotime($nextAvailableCallingTimeStart->format('g:i A')) && strtotime($currentDateTime->format('g:i A')) <= strtotime($nextAvailableCallingTimeEnd->format('g:i A')) )
										{
											$nextAvailableCallingTime = 'Now';
										}
										
										if( strtotime($currentDateTime->format('g:i A')) >= strtotime($nextAvailableCallingTimeStart->format('g:i A')) && strtotime($currentDateTime->format('g:i A')) >= strtotime($nextAvailableCallingTimeEnd->format('g:i A')) )
										{
											$nextAvailableCallingTime = 'Next Shift';
										}
										
										if( in_array($nextAvailableCallingTime, array('7:00 AM', '7:30 AM', '8:00 AM')) && time() >= strtotime('today 6:00 am') )
										{
											$nextAvailableCallingTime = 'Now';
										}
										
										// if( $customer->id == 203 )
										// {
											// echo '<br><br>';
											// echo 'nextAvailableCallingTime: ' . $nextAvailableCallingTime;
											// echo '<br>';
											// echo 'timeZone: ' . $timeZone;
											// echo '<br>';
											// echo 'currentDateTime: ' . $currentDateTime->format('g:i A');
											// echo '<br>';
											// echo 'nextAvailableCallingTimeStart: ' . $nextAvailableCallingTimeStart->format('g:i A');
											// echo '<br>';
											// echo 'nextAvailableCallingTimeEnd: ' . $nextAvailableCallingTimeEnd->format('g:i A');
											// echo '<br><br>';
										// }
									}
									else 
									{
										$nextAvailableCallingTimeStart = new DateTime($sched['schedule_start'], new DateTimeZone($timeZone) );
										$nextAvailableCallingTimeEnd = new DateTime($sched['schedule_end'], new DateTimeZone($timeZone) );
										
										$nextAvailableCallingTimeStart->setTimezone(new DateTimeZone('America/Denver'));
										$nextAvailableCallingTimeEnd->setTimezone(new DateTimeZone('America/Denver'));

										$nextAvailableCallingTime = $nextAvailableCallingTimeStart->format('g:i A');
										
										// $currentDateTime = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('America/Denver'));
										$leadLocalTime = $currentDateTime->setTimezone(new DateTimeZone($timeZone));
									
										if( strtotime($leadLocalTime->format('g:i A')) >= strtotime($sched['schedule_start']) && strtotime($leadLocalTime->format('g:i A')) <= strtotime($sched['schedule_end']) )
										{
											$nextAvailableCallingTime = 'Now';
										}
										
										if( strtotime($leadLocalTime->format('g:i A')) >= strtotime($sched['schedule_start']) && strtotime($leadLocalTime->format('g:i A')) >= strtotime($sched['schedule_end']) )
										{
											$nextAvailableCallingTime = 'Next Shift';
										}
										
										if( in_array($nextAvailableCallingTime, array('7:00 AM', '7:30 AM', '8:00 AM')) && time() >= strtotime('today 6:00 am') )
										{
											$nextAvailableCallingTime = 'Now';
										}
									}
								}
							}
						}
						else
						{
							$nextAvailableCallingTime = 'Next Shift';
						}

						return $nextAvailableCallingTime;
					}
					
					function getConfirmEndDate($date)
					{
						if( date('w', strtotime($date)) == 5 )
						{
							$confirmEndDate = strtotime('+3 day', strtotime($date));
						}
						else
						{
							$confirmEndDate = strtotime('+1 day', strtotime($date));
						}	

						if( $date == '11/23/' . date('Y') ) //for thanksgiving
						{
							return date('Y-m-d 23:59:59', strtotime('next monday', strtotime($date))); 
						}
						elseif( $date == '12/23/' . date('Y') ) //for christmas
						{
							return date('Y-12-27 23:59:59');
						}
						elseif( $date == '12/29/' . date('Y') ) //for new year
						{
							return date( (date('Y') + 1) . '-1-03 23:59:59');
						}
						else
						{
							return date('Y-m-d 23:59:59', $confirmEndDate);			
						}
					}
					
				
					$forecastDays = array();

					$date = new DateTime();

					$usFederalHolidays = new US_Federal_Holidays;
					
					$holidays = array();
					
					foreach( $usFederalHolidays->get_list() as $holiday )
					{
						$holidays[$holiday['name']] = date('m/d/Y', $holiday['timestamp']);
					}
					
					while ( count($forecastDays) < 5 )
					{
						$date->add(new DateInterval('P1D'));
						
						if( $date->format('N') < 6 && !in_array($date->format('m/d/Y'), $holidays) )
						{
							$forecastDays[] = $date->format('m/d/Y');
						}
					}
						
					echo '<div class="page-header"><h1>Forecast</h1></div>';
						
					echo '<table class="table table-bordered table-condensed">';
						
						echo '<thead>';
							echo '<tr>';
								foreach( $forecastDays as $forecastDay )
								{
									echo '<th>'.date('F d, Y', strtotime($forecastDay)).'</th>';
								}					
							echo '</tr>';
						echo '</thead>';
					
						echo '<tr>';
							foreach( $forecastDays as $forecastDay )
							{
								$confirmStartDate = date('Y-m-d 00:00:00', strtotime('+1 day', strtotime($forecastDay)));
								$confirmEndDate = getConfirmEndDate($forecastDay);			
								
								$confirmCount = CalendarAppointment::model()->count(array(
									'with' => 'lead',
									'condition' => '
										t.start_date >= "'.$confirmStartDate.'"
										AND t.start_date <= "'.$confirmEndDate.'"
										AND t.title IN ("APPOINTMENT SET", "INSERT APPOINTMENT")
										AND t.status !=4
										AND t.lead_id IS NOT NULL
										AND lead.id IS NOT NULL
									',
								));
								
								echo '<td class="center">';
									
									echo '<small>';
										echo 'Confirm Dates: ';
										echo '<br>';
										echo date('m/d/Y', strtotime($confirmStartDate));
										echo '<br>';
										echo date('m/d/Y', strtotime($confirmEndDate));
									echo '</small>';
									
									echo '<br>';
									echo '<br>';
									
									echo $confirmCount;
								echo '</td>';
								
							}					
						echo '</tr>';
					
					echo '</table>';
					
					echo '<div class="hr hr32 hr-dotted"></div>';
					
					echo '<div class="page-header"><h1>Confirmations ('.$dataProvider->itemCount.')</h1></div>';
					
					$this->widget('zii.widgets.CListView', array(
						'id'=>'leadList',
						'dataProvider'=>$dataProvider,
						'itemView' => '_confirmations',
						'summaryText' => '{start} - {end} of {count}',
						'emptyText' => '<tr><td colspan="9">No results found.</td></tr>',
						'template'=>'
							<table id="leadsTbl" class="table table-striped table-bordered table-condensed table-hover">
								<thead>
									<tr><th>Company</th>
									<th>Customer Name</th>
									<th>Status</th>
									<th>Lead Name</th>
									<th>Lead Phone</th>
									<th>Appointment Date/Time</th>
									<th>Time zone of lead</th>
									<th>Lead Available</th>
									<th>Date Added</th>
								</thead>
								{items}  
							</table> 
						',
						'pagerCssClass' => 'pagination', 
						'pager' => array(
							'header' => '',
						),
					));
				}
				
				if($page == 'pendingCallsReschedule')
				{
					$rescheduleCtr = 0;
					
					//temp fix for counter
					if( $dataProvider->itemCount > 0 )
					{
						foreach( $dataProvider->rawData as $data )
						{
							$customerSkill = CustomerSkill::model()->find(array(
								'condition' => 'customer_id = :customer_id AND skill_id = :skill_id AND status=1',
								'params' => array(
									':customer_id' => $data->customer_id,
									':skill_id' => $data->skill_id,
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
								$customerIsCallable = 'Hold';
							}
							
							
							if( $status == 'Active' )
							{
								$rescheduleCtr++;
							}
						}
					}
					
					echo '<div class="page-header"><h1>Reschedules ('.$rescheduleCtr.')</h1></div>';
					
					$this->widget('zii.widgets.CListView', array(
						'id'=>'leadList2',
						'dataProvider'=>$dataProvider,
						'itemView' => '_reschedules',
						'summaryText' => '{start} - {end} of {count}',
						'emptyText' => '<tr><td colspan="7">No results found.</td></tr>',
						'template'=>'
							<table id="leadsTbl" class="table table-striped table-bordered table-condensed table-hover">
								<thead>
									<th>Company</th>
									<th>Customer Name</th>
									<th>Status</th>
									<th>Lead Name</th>
									<th>Lead Phone</th>
									<th>Time zone of lead</th>
									<th>Date Added</th>
								</thead>
								{items}  
							</table> 
						',
						'pagerCssClass' => 'pagination', 
						'pager' => array(
							'header' => '',
						),
					));
				}
				
				if( $page == 'namesWaiting' )
				{
					$customers = Yii::app()->db->createCommand()
					->select('uc.custom_customer_id, CONCAT(uc.firstname, " ", uc.lastname) as customer_name, COUNT(ul.id) AS names_waiting_count')
					->from('ud_lead ul')
					->join('ud_customer uc', 'uc.id = ul.customer_id')
					->where('ul.status=1')
					->andWhere('ul.type=1')
					->andWhere('ul.list_id IS NULL')
					->andWhere('ul.customer_id IS NOT NULL')
					->andWhere('uc.status=1')
					->group('ul.customer_id')
					->order('uc.lastname ASC')
					->queryAll();
					
					echo '
						<table id="leadsTbl" class="table table-striped table-bordered table-condensed table-hover">
							<thead>
								<th>Customer ID</th>
								<th>Customer Name</th>
								<th>Waiting Lead Count</th>
							</thead>';
					
					if( $customers )
					{
						foreach( $customers as $customer )
						{
							echo '<tr>';
								echo '<td>'.$customer['custom_customer_id'].'</td>';
								echo '<td>'.$customer['customer_name'].'</td>';
								echo '<td class="center">'.$customer['names_waiting_count'].'</td>';
							echo '</tr>';
						}
					}
					else
					{
						echo '<tr><td colspan="3">No results found.</td></tr>';
					}
					
					echo '</table>';
				}
			
				if( $page == 'agentPerformanceLite' )
				{
					$agents = array();
					
					if( $dateFilterStart != "" && $dateFilterEnd != "" )
					{
						$dateFilterStart = date('Y-m-d 00:00:00', strtotime($dateFilterStart));
						$dateFilterEnd = date('Y-m-d 23:59:59', strtotime($dateFilterEnd));
						
						if( !empty($_POST['dateFilterStartTime']) )
						{
							$dateFilterStart = date('Y-m-d', strtotime($dateFilterStart)).' '.date('H:i:s', strtotime($_POST['dateFilterStartTime']));
						}
						
						if( !empty($_POST['dateFilterEndTime']) )
						{
							$dateFilterEndTime = date('H:i:s', strtotime('+1 hour', strtotime($_POST['dateFilterEndTime'])));
							
							$dateFilterEnd = date('Y-m-d', strtotime($dateFilterEnd)).' '.$dateFilterEndTime;
						}
						
						$sql = "
							SELECT a.id as agent_id, CONCAT(au.`first_name`, ' ', au.`last_name`) AS agent_name, a.status AS agent_status,
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
								AND uls.skill_id IN (11,12,15,16,17,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34)
								AND lch.status != 4
							) AS dials,
							(
								SELECT COUNT(lch.id) 
								FROM ud_lead_call_history lch
								LEFT JOIN ud_lists uls ON uls.id = lch.list_id
								LEFT JOIN ud_calendar_appointment ca ON ca.id = lch.calendar_appointment_id 
								WHERE lch.agent_account_id = a.`id`
								AND lch.start_call_time >= '".$dateFilterStart."'  
								AND lch.start_call_time <= '".$dateFilterEnd."'  
								AND uls.skill_id IN (11,12,15,16,17,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34)
								AND lch.disposition='Appointment Set'
								AND lch.status != 4
								AND lch.is_skill_child=0
								AND ca.id IS NOT NULL
								AND ca.title IN ('INSERT APPOINTMENT', 'APPOINTMENT SET', 'CANCEL APPOINTMENT', 'RESCHEDULE APPOINTMENT', 'LOCATION CONFLICT', 'SCHEDULE CONFLICT')
							) AS appointments
							FROM ud_account a
							LEFT JOIN ud_account_user au ON au.`account_id` = a.`id`
							WHERE a.`account_type_id` = 2
							AND a.`id` NOT IN (4, 5)
							ORDER BY au.last_name ASC
						";
						
						// echo '<br><br>';
						// echo date('m/d/Y g:i A', strtotime($dateFilterStart));
						// echo ' - ';
						// echo date('m/d/Y g:i A', strtotime($dateFilterEnd));
						// echo '<br><br>';
						// echo $sql;
						// echo '<br><br>';
						
						$connection = Yii::app()->db;
						$command = $connection->createCommand($sql);
						$agents = $command->queryAll();
					
						echo '
							<table id="leadsTbl" class="table table-striped table-bordered table-condensed table-hover">
								<thead>
									<th>#</th>
									<th>Agent Name</th>
									<th>Status</th>
									<th class="center">Total Hours</th>
									<th class="center">Dials</th>
									<th class="center">Dials/Hour</th>
									<th class="center">Appointments</th>
									<th class="center">Appts/Hour</th>
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
										
											// echo 'dials: ' . $agent['dials'];
											// echo '<br>';
											// echo 'total hours: ' . $agent['total_hours'];
											// echo '<br>';
											// echo '<br>';
										
											if( $agent['dials'] > 0 && $agent['total_hours'] > 0 )
											{
												echo round($agent['dials'] / $agent['total_hours'], 2);
											}
											else
											{
												
												echo 0;
											}
										
										echo '</td>';
										
										echo '<td class="center">'.$agent['appointments'].'</td>';
										
										echo '<td class="center">';
										
											if( $agent['appointments'] > 0 && $agent['total_hours'] > 0 )
											{
												echo round($agent['appointments'] / $agent['total_hours'], 2);
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
								echo '<th class="center">'.number_format($totalAppointments).'</th>';
								echo '<th class="center">'.round($totalAppointments/$totalHours, 2).'</th>';
							echo '</tr>';
						}
						else
						{
							echo '<tr><td colspan="4">No results found.</td></tr>';
						}
						
						echo '</table>';
					}
				}
			
				if( $page == 'waxieCampaign' )
				{
					$customers = array();
					
					if( $dateFilterStart != "" && $dateFilterEnd != "" )
					{
						$sql = "
							SELECT CONCAT(c.`firstname`, ' ', c.`lastname`) AS customer_name, c.id as customer_id,
							(
								SELECT COUNT(lch.id) 
								FROM ud_lead_call_history lch
								LEFT JOIN ud_lists uls ON uls.id = lch.list_id
								WHERE lch.customer_id = c.`id`
								AND lch.start_call_time >= '".date('Y-m-d 00:00:00', strtotime($dateFilterStart))."' 
								AND lch.start_call_time <= '".date('Y-m-d 23:59:59', strtotime($dateFilterEnd))."' 
								AND uls.skill_id=23
								AND lch.status != 4
							) AS dials,
							(
								SELECT COUNT(lch.id) 
								FROM ud_lead_call_history lch
								LEFT JOIN ud_lists uls ON uls.id = lch.list_id
								LEFT JOIN ud_skill_disposition sd ON sd.id = lch.disposition_id
								LEFT JOIN ud_skill_child_disposition scd ON scd.id = lch.skill_child_disposition_id
								WHERE lch.customer_id = c.`id`
								AND lch.start_call_time >= '".date('Y-m-d 00:00:00', strtotime($dateFilterStart))."' 
								AND lch.start_call_time <= '".date('Y-m-d 23:59:59', strtotime($dateFilterEnd))."' 
								AND uls.skill_id=23
								AND lch.status != 4
								AND ( sd.is_voice_contact=1 OR scd.is_voice_contact=1 )
							) AS voice_contacts,
							(
								SELECT COUNT(lch.id) 
								FROM ud_lead_call_history lch
								LEFT JOIN ud_lists uls ON uls.id = lch.list_id
								WHERE lch.customer_id = c.`id`
								AND lch.start_call_time >= '".date('Y-m-d 00:00:00', strtotime($dateFilterStart))."' 
								AND lch.start_call_time <= '".date('Y-m-d 23:59:59', strtotime($dateFilterEnd))."' 
								AND uls.skill_id=23
								AND lch.disposition='Appointment Set'
								AND lch.status != 4
								AND lch.is_skill_child=0
							) AS appointments,
							(
								SELECT COUNT(lch.id) 
								FROM ud_lead_call_history lch
								LEFT JOIN ud_lists uls ON uls.id = lch.list_id
								WHERE lch.customer_id = c.`id`
								AND lch.start_call_time >= '".date('Y-m-d 00:00:00', strtotime($dateFilterStart))."' 
								AND lch.start_call_time <= '".date('Y-m-d 23:59:59', strtotime($dateFilterEnd))."' 
								AND uls.skill_id=23
								AND lch.disposition='Customer Requested'
								AND lch.status != 4
							) AS customer_requested
							FROM ud_customer c
							WHERE c.`company_id`=27
							ORDER BY c.`lastname` ASC
						";
						
						$connection = Yii::app()->db;
						$command = $connection->createCommand($sql);
						$customers = $command->queryAll();
					
						echo '
							<table id="leadsTbl" class="table table-striped table-bordered table-condensed table-hover">
								<thead>
									<th></th>
									<th class="center">Dials</th>
									<th class="center">Voice Contacts</th>
									<th class="center">Appointments</th>
									<th class="center">Customer Requested</th>
								</thead>';
						
						if( $customers )
						{
							foreach( $customers as $customer )
							{
								echo '<tr>';
									echo '<td>'.CHtml::link($customer['customer_name'], array('reports', 'page'=>'waxieCampaign', 'customer_id'=>$customer['customer_id'], 'dateFilterStart'=>$dateFilterStart, 'dateFilterEnd'=>$dateFilterEnd)).'</td>';					
									echo '<td>'.$customer['dials'].'</td>';					
									echo '<td>'.$customer['voice_contacts'].'</td>';					
									echo '<td>'.$customer['appointments'].'</td>';					
									echo '<td>'.$customer['customer_requested'].'</td>';					
									 
								echo '</tr>';
							}
						}
						else
						{
							echo '<tr><td colspan="4">No results found.</td></tr>';
						}
						
						echo '</table>';
						
						if( isset($_GET['customer_id']) )
						{
							$customer = Customer::model()->findByPk($_GET['customer_id']);
							
							echo '<div class="page-header">';
								echo '<h1>'.$customer->firstname.' '.$customer->lastname.'</h1>';
							echo '</div>';
							
							echo '<div class="row">';
								echo '<div class="col-sm-12">';
									echo CHtml::link('<i class="fa fa-file-excel-o"></i> Export to Excel', array('export', 'page'=>'waxieCampaign', 'selectedSkills'=>'', 'customer_id'=>$_GET['customer_id'], 'dateFilterStart'=>$dateFilterStart, 'dateFilterEnd'=>$dateFilterEnd), array('class'=>'btn btn-yellow btn-sm pull-right'));
								echo '</div>';
							echo '</div>';
							
							echo '<div class="space-6"></div>';
							
							$calls = array();
							
							$callSql = "
								SELECT ld.id as lead_id, ld.first_name, ld.last_name,
									lch.lead_phone_number AS phone_number,
									(
										SELECT COUNT(id) from ud_lead_call_history WHERE lead_id = ld.id 
									) as dials,									
									lch.disposition, lch.agent_note 
								FROM ud_lead_call_history lch 
								LEFT JOIN ud_lead ld ON ld.id = lch.lead_id
								LEFT JOIN ud_lists uls ON uls.id = lch.list_id  
								WHERE lch.customer_id = '".$_GET['customer_id']."'
								AND lch.start_call_time >= '".date('Y-m-d 00:00:00', strtotime($dateFilterStart))."' 
								AND lch.start_call_time <= '".date('Y-m-d 23:59:59', strtotime($dateFilterEnd))."' 
								AND lch.end_call_time > lch.start_call_time
								AND lch.status != 4
								AND uls.skill_id=23
							";
							
							$connection = Yii::app()->db;
							$command = $connection->createCommand($callSql);
							$calls = $command->queryAll();
						
							if( $calls )
							{
								echo '
									<table id="leadsTbl" class="table table-striped table-bordered table-condensed table-hover">
										<thead>
											<th>Lead</th>
											<th>Phone#</th>
											<th>#Dials</th>
											<th>Disposition</th>
											<th>Agent Notes</th>
										</thead>';
								
								foreach( $calls as $call )
								{
									echo '<tr>';
										echo '<td>'.$call['first_name'].' '.$call['last_name'].'</td>';					
										echo '<td>'.$call['phone_number'].'</td>';					
										echo '<td>'.$call['dials'].'</td>';					
										echo '<td>'.$call['disposition'].'</td>';					
										echo '<td>'.$call['agent_note'].'</td>';					
										 
									echo '</tr>';
								}
							}
							
							echo '</table>';
						}
					}
				}
			
				if( $page == 'genericSkill' )
				{
					$customers = array();
					
					if( !empty($_POST['skillId']) && $dateFilterStart != "" && $dateFilterEnd != "" )
					{
						if( $_POST['skillId'] == 11 )
						{
							$skillIds = array(11, 33);
						}
						
						if( $_POST['skillId'] == 12 )
						{
							$skillIds = array(12, 34);
						}

						$sql = "
							SELECT 
								co.company_name as company_name,
								CONCAT (c.firstname, ' ', c.lastname) AS customer_name,
								lch.lead_phone_number AS lead_phone,
								ld.first_name AS lead_first_name, 
								ld.last_name AS lead_last_name,
								ld.partner_first_name AS partner_first_name,
								ld.partner_last_name AS partner_last_name,
								lch.is_skill_child,
								lch.disposition,
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
							AND lch.start_call_time >= '".date('Y-m-d 00:00:00', strtotime($dateFilterStart))."' 
							AND lch.start_call_time <= '".date('Y-m-d 23:59:59', strtotime($dateFilterEnd))."' 
							AND lch.status !=4 
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
									<th class="center">Company</th>
									<th class="center">Customer</th>
									<th class="center">Lead Phone</th>
									<th class="center">Lead First</th>
									<th class="center">Lead Last</th>
									<th class="center">Partner First</th>
									<th class="center">Partner Last</th>
									<th class="center">Date/Time</th>
									<th class="center">Skill</th>
									<th class="center">Disposition</th>
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
							echo '<tr><td colspan="9">No results found.</td></tr>';
						}
						
						echo '</table>';
					}
				}
			
				if( $page == 'growth' )
				{
					if( $dateFilterStart != "" && $dateFilterEnd != "" )
					{
						$enrollments = array();
						$cancellations = array();
						$changes = array();
						
						$enrollmentsTotalAmount = 0;
						$cancellationsTotalAmount = 0;
						$changesTotalAmount = 0;
						$totalNet = 0;
						
						//Enrollments
						$enrollmentModels = CustomerSkill::model()->findAll(array(
							'condition' => '
								DATE(date_created) >= "'.date('Y-m-d', strtotime($dateFilterStart)).'" 
								AND DATE(date_created) <= "'.date('Y-m-d', strtotime($dateFilterEnd)).'"
								AND date_created NOT BETWEEN "2016-12-22 05:03:14" 
								AND "2016-12-22 05:03:32" 
							',
							'order' => 'date_created DESC'
						));
						
						if( $enrollmentModels )
						{
							foreach( $enrollmentModels as $enrollmentModel )
							{
								$totalLeads = 0;
								$contractedAmount = 0;
								
								$customer = Customer::model()->find(array(
									'condition' => 'id = :customer_id',
									'params' => array(
										':customer_id' => $enrollmentModel->customer_id,
									),
								));

								if( $customer )
								{
									$contract = $enrollmentModel->contract;

									if( $contract )
									{
										if($contract->fulfillment_type != null )
										{
											if( $contract->fulfillment_type == Contract::TYPE_FULFILLMENT_GOAL_VOLUME )
											{
												if( !empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME]) )
												{
													foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME] as $key => $subsidyLevel )
													{
														$customerSkillLevelArray = $enrollmentModel->getCustomerSkillLevelArray();
														$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;

														if( $customerSkillLevelArrayGroup != null )
														{							
															if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
															{
																$totalLeads += ( $subsidyLevel['goal'] * $customerSkillLevelArrayGroup->quantity );
																
																$contractedAmount += ( $subsidyLevel['amount'] * $customerSkillLevelArrayGroup->quantity );
															}
														}
													}
												}
												
												$customerExtras = CustomerExtra::model()->findAll(array(
													'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id AND status=1 AND year = :year AND month = :month',
													'params' => array(
														':customer_id' => $enrollmentModel->customer_id,
														':contract_id' => $enrollmentModel->contract_id,
														':skill_id' => $enrollmentModel->skill_id,
														':year' => date('Y'),
														':month' => date('m'),
													),
												));
												
												if( $customerExtras )
												{
													foreach( $customerExtras as $customerExtra )
													{
														$totalLeads += $customerExtra->quantity;
													}
												}
											}
											else
											{
												if( !empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME]) )
												{
													foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME] as $key => $subsidyLevel )
													{
														$customerSkillLevelArray = $enrollmentModel->getCustomerSkillLevelArray();
														
														$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;
														
														if( $customerSkillLevelArrayGroup != null )
														{
															if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
															{
																$totalLeads += ( $subsidyLevel['high'] * $customerSkillLevelArrayGroup->quantity );
																
																$contractedAmount += ( $subsidyLevel['amount'] * $customerSkillLevelArrayGroup->quantity );
															}
														}
													}
												}
												
												$customerExtras = CustomerExtra::model()->findAll(array(
													'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id AND status=1 AND year = :year AND month = :month',
													'params' => array(
														':customer_id' => $enrollmentModel->customer_id,
														':contract_id' => $enrollmentModel->contract_id,
														':skill_id' => $enrollmentModel->skill_id,
														':year' => date('Y'),
														':month' => date('m'),
													),
												));
												
												if( $customerExtras )
												{
													foreach( $customerExtras as $customerExtra )
													{
														$totalLeads += $customerExtra->quantity;
													}
												}
											}
										}
										
										$selectedSalesReps = '';
										
										$salesReps = CustomerSalesRep::model()->findAll(array(
											'condition' => 'customer_id = :customer_id',
											'params' => array(
												':customer_id' => $enrollmentModel->customer_id,
											),
										));
										
										if( $salesReps )
										{
											foreach( $salesReps as $salesRep )
											{
												if( isset($salesRep->account) )
												{
													$selectedSalesReps .= $salesRep->account->getFullName() . ', ';
												}
											}
											
											$selectedSalesReps = trim($selectedSalesReps, ', ');
										}
										
										$agentName = '';
										
										$customerHistory = CustomerHistory::model()->find(array(
											'condition' => '
												content LIKE "%Registered on%"
												AND customer_id = :customer_id
											',
											'params' => array(
												':customer_id' => $customer->id,
											),
										));
										
										if( $customerHistory )
										{
											$agentName = $customerHistory->account->getFullName();
										}
										
										$enrollmentsTotalAmount += $contractedAmount;
										$totalNet += $contractedAmount;
										
										$dateTime = new DateTime($enrollmentModel->date_created, new DateTimeZone('America/Chicago'));
										$dateTime->setTimezone(new DateTimeZone('America/Denver'));	
										
										$enrollments[] = array(
											'date_entered' => $dateTime->format('m/d/Y g:i A'),
											'sales_agent' => $selectedSalesReps,
											'agent' => $agentName,
											'start_date' => date('m-d-Y', strtotime($enrollmentModel->start_month)),
											'end_date' => $enrollmentModel->end_month != '0000-00-00' && !empty($enrollmentModel->end_month) ? date('m-d-Y', strtotime($enrollmentModel->end_month)) : '',
											'company' => $customer->company->company_name,
											'customer_name' => CHtml::link($customer->getFullName(), array('/customer/insight/index', 'customer_id'=>$customer->id), array('target'=>'_blank')),
											'customer_id' => $customer->custom_customer_id,
											'skill' => $enrollmentModel->skill->skill_name,
											'contract' => $enrollmentModel->contract->contract_name,
											'quantity' => $totalLeads,
											'amount' => $contractedAmount,
										);
									}
								}
							}
						}
						
						
						//Changes
						$changesModels = CustomerHistory::model()->findAll(array(
							'condition' => '
								DATE(date_created) >= "'.date('Y-m-d', strtotime($dateFilterStart)).'" 
								AND DATE(date_created) <= "'.date('Y-m-d', strtotime($dateFilterEnd)).'"
								AND (
									content LIKE "%Contract Upgrade%"
									OR content LIKE "%Contract Downgrade%"
									OR ( 
										content LIKE "%Status Changed from On Hold to Active%"
										AND page_name = "Customer Skill"
									)
									OR ( 
										content LIKE "%Status Changed from Active to Hold%"
										AND page_name = "Customer Skill"
									)
									OR (
										page_name = "Credit"
										AND type = 1
									)
								)
							',
							'order' => 'date_created DESC'
						));
						
						if( $changesModels )
						{
							$changesAmountArray = array();
							
							foreach( $changesModels as $changesModel )
							{
								$totalLeads = 0;
								$contractedAmount = 0;
								
								$customer = Customer::model()->find(array(
									'condition' => 'id = :customer_id',
									'params' => array(
										':customer_id' => $changesModel->customer_id,
									),
								));
								
								if( $customer && $customer->is_deleted == 0 && $customer->company->company_name != "Test Company" )
								{
									if( $changesModel->page_name == 'Credit' || strpos($changesModel->content, 'Status Changed from Hold to Active') !== false || strpos($changesModel->content, 'Status Changed from Active to Hold') !== false )
									{
										$customerSkill = CustomerSkill::model()->find(array(
											'with' => 'contract',
											'condition' => 't.customer_id = :customer_id AND t.status=1 AND contract.contract_name NOT IN("Audigy Surveys", "Audigy Group", "Tournament Reservations", "Training Company Contract", "Mountain View Network")',
											'params' => array(
												':customer_id' => $customer->id,
											),
											'order' => 't.date_created DESC',
										));
									}
									else
									{
										$customerSkill = CustomerSkill::model()->find(array(
											'with' => 'contract',
											'condition' => 't.id = :id AND t.customer_id = :customer_id AND t.status=1 AND contract.contract_name NOT IN("Audigy Surveys", "Audigy Group", "Tournament Reservations", "Training Company Contract", "Mountain View Network")',
											'params' => array(
												':id' => $changesModel->model_id,
												':customer_id' => $customer->id,
											),
											'order' => 't.date_created DESC',
										));
									}
									
									if( $customerSkill )
									{
										$contract = $customerSkill->contract;

										if( $contract )
										{
											if($contract->fulfillment_type != null )
											{
												if( $contract->fulfillment_type == Contract::TYPE_FULFILLMENT_GOAL_VOLUME )
												{
													if( !empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME]) )
													{
														foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME] as $key => $subsidyLevel )
														{
															$customerSkillLevelArray = $customerSkill->getCustomerSkillLevelArray();
															$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;

															if( $customerSkillLevelArrayGroup != null )
															{							
																if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
																{
																	$totalLeads += ( $subsidyLevel['goal'] * $customerSkillLevelArrayGroup->quantity );
																	
																	$contractedAmount += ( $subsidyLevel['amount'] * $customerSkillLevelArrayGroup->quantity );
																}
															}
														}
													}
													
													$customerExtras = CustomerExtra::model()->findAll(array(
														'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id AND status=1 AND year = :year AND month = :month',
														'params' => array(
															':customer_id' => $customerSkill->customer_id,
															':contract_id' => $customerSkill->contract_id,
															':skill_id' => $customerSkill->skill_id,
															':year' => date('Y'),
															':month' => date('m'),
														),
													));
													
													if( $customerExtras )
													{
														foreach( $customerExtras as $customerExtra )
														{
															$totalLeads += $customerExtra->quantity;
														}
													}
												}
												else
												{
													if( !empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME]) )
													{
														foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME] as $key => $subsidyLevel )
														{
															$customerSkillLevelArray = $customerSkill->getCustomerSkillLevelArray();
															
															$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;
															
															if( $customerSkillLevelArrayGroup != null )
															{
																if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
																{
																	$totalLeads += ( $subsidyLevel['high'] * $customerSkillLevelArrayGroup->quantity );
																	
																	$contractedAmount += ( $subsidyLevel['amount'] * $customerSkillLevelArrayGroup->quantity );
																}
															}
														}
													}
													
													$customerExtras = CustomerExtra::model()->findAll(array(
														'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id AND status=1 AND year = :year AND month = :month',
														'params' => array(
															':customer_id' => $customerSkill->customer_id,
															':contract_id' => $customerSkill->contract_id,
															':skill_id' => $customerSkill->skill_id,
															':year' => date('Y'),
															':month' => date('m'),
														),
													));
													
													if( $customerExtras )
													{
														foreach( $customerExtras as $customerExtra )
														{
															$totalLeads += $customerExtra->quantity;
														}
													}
												}
											}
											
											$dateTime = new DateTime($changesModel->date_created, new DateTimeZone('America/Chicago'));
											$dateTime->setTimezone(new DateTimeZone('America/Denver'));	
											
											$creditAmount = 0;
											$selectedSalesReps = '';
											$changeType = '';
											
											if ( strpos(strtolower($changesModel->content), 'upgrade') !== false ) 
											{
												if( $contractedAmount > 0 )
												{
													$changeType = 'Upgrade'; 
													
													$totalNet += $contractedAmount;
													$changesTotalAmount += $contractedAmount;
													
													$changes[] = array(
														'date_entered' => $dateTime->format('m/d/Y g:i A'),
														'sales_agent' => $selectedSalesReps,
														'agent' => isset($changesModel->account) ? $changesModel->account->getFullName() : '',
														'start_date' => date('m-d-Y', strtotime($customerSkill->start_month)),
														'end_date' => $customerSkill->end_month != '0000-00-00' && !empty($customerSkill->end_month) ? date('m-d-Y', strtotime($customerSkill->end_month)) : '',
														'company' => $customer->company->company_name,
														'customer_name' => CHtml::link($customer->getFullName(), array('/customer/insight/index', 'customer_id'=>$customer->id), array('target'=>'_blank')),
														'customer_id' => $customer->custom_customer_id,
														'skill' => $customerSkill->skill->skill_name,
														'contract' => $customerSkill->contract->contract_name,
														'quantity' => $totalLeads,
														'amount' => $contractedAmount,
														'credit_amount' => $creditAmount,
														'change_type' => $changeType
													);
												}
											}
											
											if ( strpos(strtolower($changesModel->content), 'downgrade') !== false ) 
											{
												if( $contractedAmount > 0 )
												{
													$changeType = 'Downgrade'; 
												
													$totalNet -= $contractedAmount;
													$changesTotalAmount += -$contractedAmount;
													
													$changes[] = array(
														'date_entered' => $dateTime->format('m/d/Y g:i A'),
														'sales_agent' => $selectedSalesReps,
														'agent' => isset($changesModel->account) ? $changesModel->account->getFullName() : '',
														'start_date' => date('m-d-Y', strtotime($customerSkill->start_month)),
														'end_date' => $customerSkill->end_month != '0000-00-00' && !empty($customerSkill->end_month) ? date('m-d-Y', strtotime($customerSkill->end_month)) : '',
														'company' => $customer->company->company_name,
														'customer_name' => CHtml::link($customer->getFullName(), array('/customer/insight/index', 'customer_id'=>$customer->id), array('target'=>'_blank')),
														'customer_id' => $customer->custom_customer_id,
														'skill' => $customerSkill->skill->skill_name,
														'contract' => $customerSkill->contract->contract_name,
														'quantity' => $totalLeads,
														'amount' => $contractedAmount,
														'credit_amount' => $creditAmount,
														'change_type' => $changeType
													);
												}
											}
											
											if ( strpos($changesModel->content, 'Status Changed from Active to Hold') !== false || strpos($changesModel->content, 'Status Changed from Hold to Active') !== false ) 
											{
												if( $contractedAmount > 0 )
												{
													if ( strpos($changesModel->content, 'Status Changed from Hold to Active') !== false ) 
													{
														$changeType = 'Active'; 
														
														$totalNet += $contractedAmount;
														$changesTotalAmount += $contractedAmount;
													}

													if ( strpos($changesModel->content, 'Status Changed from Active to Hold') !== false ) 
													{
														$changeType = 'On Hold'; 
														
														$totalNet -= $contractedAmount;
														$changesTotalAmount += -$contractedAmount;
													}
													
													$changes[] = array(
														'date_entered' => $dateTime->format('m/d/Y g:i A'),
														'sales_agent' => $selectedSalesReps,
														'agent' => isset($changesModel->account) ? $changesModel->account->getFullName() : '',
														'start_date' => date('m-d-Y', strtotime($customerSkill->start_month)),
														'end_date' => $customerSkill->end_month != '0000-00-00' && !empty($customerSkill->end_month) ? date('m-d-Y', strtotime($customerSkill->end_month)) : '',
														'company' => $customer->company->company_name,
														'customer_name' => CHtml::link($customer->getFullName(), array('/customer/insight/index', 'customer_id'=>$customer->id), array('target'=>'_blank')),
														'customer_id' => $customer->custom_customer_id,
														'skill' => $customerSkill->skill->skill_name,
														'contract' => $customerSkill->contract->contract_name,
														'quantity' => $totalLeads,
														'amount' => $contractedAmount,
														'credit_amount' => $creditAmount,
														'change_type' => $changeType
													);
												}
											}
											
											if( $changesModel->page_name == 'Credit' AND $changesModel->type == 1 )
											{
												$changeType = 'New Credit';
												
												$customerCredit = CustomerCredit::model()->findByPk($changesModel->model_id);
												
												if( $customerCredit )
												{
													if( $creditAmount > 0 )
													{
														$creditAmount = $customerCredit->amount;
													
														$totalNet -= $creditAmount;
														$changesTotalAmount += -$creditAmount;
														
														$changes[] = array(
															'date_entered' => $dateTime->format('m/d/Y g:i A'),
															'sales_agent' => $selectedSalesReps,
															'agent' => isset($changesModel->account) ? $changesModel->account->getFullName() : '',
															'start_date' => date('m-d-Y', strtotime($customerSkill->start_month)),
															'end_date' => $customerSkill->end_month != '0000-00-00' && !empty($customerSkill->end_month) ? date('m-d-Y', strtotime($customerSkill->end_month)) : '',
															'company' => $customer->company->company_name,
															'customer_name' => CHtml::link($customer->getFullName(), array('/customer/insight/index', 'customer_id'=>$customer->id), array('target'=>'_blank')),
															'customer_id' => $customer->custom_customer_id,
															'skill' => $customerSkill->skill->skill_name,
															'contract' => $customerSkill->contract->contract_name,
															'quantity' => $totalLeads,
															'amount' => $contractedAmount,
															'credit_amount' => $creditAmount,
															'change_type' => $changeType
														);
													}
												}
											}
										}
									}
								}
							}
						}
						
						
						$cancelModels = CustomerSkill::model()->findAll(array(
							'condition' => 'DATE(end_month) >= DATE("2017-01-01") AND skill_id NOT IN (11,12,19,21,22,24,27,30)',
							'order' => 'date_created DESC'
						));
						
						if( $cancelModels )
						{
							foreach( $cancelModels as $cancelModel )
							{
								$totalLeads = 0;
								$contractedAmount = 0;
								
								$customer = Customer::model()->find(array(
									'condition' => 'id = :customer_id',
									'params' => array(
										':customer_id' => $cancelModel->customer_id,
									),
								));

								if( $customer )
								{
									$contract = $cancelModel->contract;

									if( $contract )
									{
										if($contract->fulfillment_type != null )
										{
											if( $contract->fulfillment_type == Contract::TYPE_FULFILLMENT_GOAL_VOLUME )
											{
												if( !empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME]) )
												{
													foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME] as $key => $subsidyLevel )
													{
														$customerSkillLevelArray = $cancelModel->getCustomerSkillLevelArray();
														$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;

														if( $customerSkillLevelArrayGroup != null )
														{							
															if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
															{
																$totalLeads += ( $subsidyLevel['goal'] * $customerSkillLevelArrayGroup->quantity );
																
																$contractedAmount += ( $subsidyLevel['amount'] * $customerSkillLevelArrayGroup->quantity );
															}
														}
													}
												}
												
												$customerExtras = CustomerExtra::model()->findAll(array(
													'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id AND status=1 AND year = :year AND month = :month',
													'params' => array(
														':customer_id' => $cancelModel->customer_id,
														':contract_id' => $cancelModel->contract_id,
														':skill_id' => $cancelModel->skill_id,
														':year' => date('Y'),
														':month' => date('m'),
													),
												));
												
												if( $customerExtras )
												{
													foreach( $customerExtras as $customerExtra )
													{
														$totalLeads += $customerExtra->quantity;
													}
												}
											}
											else
											{
												if( !empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME]) )
												{
													foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME] as $key => $subsidyLevel )
													{
														$customerSkillLevelArray = $cancelModel->getCustomerSkillLevelArray();
														
														$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;
														
														if( $customerSkillLevelArrayGroup != null )
														{
															if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
															{
																$totalLeads += ( $subsidyLevel['high'] * $customerSkillLevelArrayGroup->quantity );
																
																$contractedAmount += ( $subsidyLevel['amount'] * $customerSkillLevelArrayGroup->quantity );
															}
														}
													}
												}
												
												$customerExtras = CustomerExtra::model()->findAll(array(
													'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id AND status=1 AND year = :year AND month = :month',
													'params' => array(
														':customer_id' => $cancelModel->customer_id,
														':contract_id' => $cancelModel->contract_id,
														':skill_id' => $cancelModel->skill_id,
														':year' => date('Y'),
														':month' => date('m'),
													),
												));
												
												if( $customerExtras )
												{
													foreach( $customerExtras as $customerExtra )
													{
														$totalLeads += $customerExtra->quantity;
													}
												}
											}
										}

										$valid = false;
										$dateEntered = '';
										$agentName = '';
										
										$customerHistory = CustomerHistory::model()->find(array(
											'condition' => '
												content LIKE "%End Date Changed%"
												AND model_id = :model_id
											',
											'params' => array(
												':model_id' => $cancelModel->id,
											),
											'order' => 'date_created DESC'
										));
										
										if( $customerHistory )
										{
											$dateTime = new DateTime($customerHistory->date_created, new DateTimeZone('America/Chicago'));
											$dateTime->setTimezone(new DateTimeZone('America/Denver'));	
											$dateEntered = $dateTime->format('m/d/Y g:i A');
											
											$agentName = $customerHistory->account->getFullName();
											
											if( (date('Y-m-d', strtotime($customerHistory->date_created)) >= date('Y-m-d', strtotime($dateFilterStart))) && (date('Y-m-d', strtotime($customerHistory->date_created)) <= date('Y-m-d', strtotime($dateFilterEnd))) )
											{
												$valid = true;
											}
										}
										
										if( $valid )
										{
											$cancellationsTotalAmount += $contractedAmount;
											$totalNet -= $contractedAmount;
											
											$cancellations[$customerHistory->id] = array(
												'date_entered' => $dateEntered,
												'sales_agent' => '',
												'agent' => $agentName,
												'start_date' => date('m-d-Y', strtotime($cancelModel->start_month)),
												'end_date' => $cancelModel->end_month != '0000-00-00' && !empty($cancelModel->end_month) ? date('m-d-Y', strtotime($cancelModel->end_month)) : '',
												'company' => $customer->company->company_name,
												'customer_name' => CHtml::link($customer->getFullName(), array('/customer/insight/index', 'customer_id'=>$customer->id), array('target'=>'_blank')),
												'customer_id' => $customer->custom_customer_id,
												'skill' => $cancelModel->skill->skill_name,
												'contract' => $cancelModel->contract->contract_name,
												'quantity' => $totalLeads,
												'amount' => $contractedAmount,
											);
										}
									}
								}
							}
						}
						
						
						echo '<div class="row page-header">'; 
						
							echo '<div class="col-sm-6"><h1>Enrollment</h1></div>';
							
							echo '<h1>';
								echo '<div class="col-sm-5"> ';
									echo '<div class="col-sm-4">Net</div>';
									echo '<div class="col-sm-4">'.( count($enrollments) - count($cancellations) ).'</div>';
									echo '<div class="col-sm-4">';	
										
										if( $totalNet > 0 )
										{
											echo '$' . number_format($totalNet, 2);
										}
										else
										{
											echo '<span class="red">-$'.number_format( abs($totalNet), 2).'</span>';
										}
										
									echo '</div>';
									
								echo '</div>';
							echo '</h1>';

						echo '</div>';
						
						echo '
							<table id="leadsTbl" class="table table-striped table-bordered table-condensed table-hover">
								<thead>
									<th>#</th>
									<th class="center">Date Entered</th>
									<th class="center">Sales Agent</th>
									<th class="center">Start Date</th>
									<th class="center">End Date</th>
									<th class="center">Company</th>
									<th class="center">Customer Name</th>
									<th class="center">Customer ID</th>
									<th class="center">Skill</th>
									<th class="center">Contract</th>
									<th class="center">Quantity</th>
									<th class="center">Amount</th>
								</thead>
						';
						
						if( $enrollments )
						{
							$ctr = 1;
							
							foreach( $enrollments as $enrollment )
							{
								echo '<tr>';
									echo '<td>'.$ctr.'</td>';
									echo '<td>'.$enrollment['date_entered'].'</td>';
									echo '<td>'.$enrollment['sales_agent'].'</td>';
									echo '<td>'.$enrollment['start_date'].'</td>';
									echo '<td>'.$enrollment['end_date'].'</td>';
									echo '<td>'.$enrollment['company'].'</td>';
									echo '<td>'.$enrollment['customer_name'].'</td>';
									echo '<td>'.$enrollment['customer_id'].'</td>';
									echo '<td>'.$enrollment['skill'].'</td>';
									echo '<td>'.$enrollment['contract'].'</td>';
									echo '<td class="center">'.$enrollment['quantity'].'</td>';
									echo '<td>$'.$enrollment['amount'].'</td>';
								echo '</tr>';
								
								$ctr++;
							}
							
							echo '<tr>';
								echo '<th colspan="11">Total</th>';
								echo '<td colspan="1">$'.number_format($enrollmentsTotalAmount, 2).'</td>';
							echo '</tr>';
						}
						else
						{
							echo '<tr><td colspan="12">No result found.</td></tr>';
						}
						
						echo '</table>';
						
						echo '<div class="space-12"></div>';
						
						echo '<div class="row page-header">'; 
						
							echo '<div class="col-sm-6"><h1>Changes</h1></div>';

						echo '</div>';
						
						echo '
							<table id="leadsTbl" class="table table-striped table-bordered table-condensed table-hover">
								<thead>
									<th>#</th>
									<th class="center">Date Entered</th>
									<th class="center">User</th>
									<th class="center">Start Date</th>
									<th class="center">End Date</th>
									<th class="center">Company</th>
									<th class="center">Customer Name</th>
									<th class="center">Customer ID</th>
									<th class="center">Skill</th>
									<th class="center">Contract</th>
									<th class="center">Quantity</th>
									<th class="center">Change</th>
									<th class="center">Amount</th>
								</thead>
						';
						
						if( $changes )
						{
							$ctr = 1;
							
							foreach( $changes as $change )
							{
								echo '<tr>';
									echo '<td>'.$ctr.'</td>';
									echo '<td>'.$change['date_entered'].'</td>';
									echo '<td>'.$change['agent'].'</td>';
									echo '<td>'.$change['start_date'].'</td>';
									echo '<td>'.$change['end_date'].'</td>';
									echo '<td>'.$change['company'].'</td>';
									echo '<td>'.$change['customer_name'].'</td>';
									echo '<td>'.$change['customer_id'].'</td>';
									echo '<td>'.$change['skill'].'</td>';
									echo '<td>'.$change['contract'].'</td>';
									echo '<td class="center">'.$change['quantity'].'</td>';
									
									echo '<td>'.$change['change_type'].'</td>';
									
									if( $change['change_type'] == 'New Credit' )
									{
										echo '<td style="color:red;">-$'.$change['credit_amount'].'</td>';
									}
									elseif( $change['change_type'] == 'Downgrade' )
									{
										echo '<td style="color:red;">-$'.$change['amount'].'</td>';
									}
									elseif( $change['change_type'] == 'On Hold' )
									{
										echo '<td style="color:red;">-$'.$change['amount'].'</td>';
									}									
									else
									{ 
										echo '<td>$'.$change['amount'].'</td>';
									}
									
								echo '</tr>';
								
								$ctr++;
							}
							
							echo '<tr>';
								echo '<th colspan="12">Total</th>';
								
								if( $changesTotalAmount > 0 )
								{
									echo '<td colspan="1">$'.number_format($changesTotalAmount, 2).'</td>';
								}
								else
								{
									echo '<td colspan="1" style="color:red">-$'.number_format( abs($changesTotalAmount), 2).'</td>';
								}
								
							echo '</tr>';
						}
						else
						{
							echo '<tr><td colspan="12">No result found.</td></tr>';
						}
						
						echo '</table>';
						
						echo '<div class="space-12"></div>';
						
						echo '<div class="page-header"><h1>Cancellation</h1></div>';
						
						echo '
							<table id="leadsTbl" class="table table-striped table-bordered table-condensed table-hover">
								<thead>
									<th>#</th>
									<th class="center">Date Entered</th>
									<th class="center">User</th>
									<th class="center">Start Date</th>
									<th class="center">End Date</th>
									<th class="center">Company</th>
									<th class="center">Customer Name</th>
									<th class="center">Customer ID</th>
									<th class="center">Skill</th>
									<th class="center">Contract</th>
									<th class="center">Quantity</th>
									<th class="center">Amount</th>
								</thead>
						';
						
						krsort($cancellations);
						
						if( $cancellations )
						{
							$ctr = 1;
							
							foreach( $cancellations as $cancellation )
							{
								echo '<tr>';
									echo '<td>'.$ctr.'</td>';
									echo '<td>'.$cancellation['date_entered'].'</td>';
									echo '<td>'.$cancellation['agent'].'</td>';
									echo '<td>'.$cancellation['start_date'].'</td>';
									echo '<td>'.$cancellation['end_date'].'</td>';
									echo '<td>'.$cancellation['company'].'</td>';
									echo '<td>'.$cancellation['customer_name'].'</td>';
									echo '<td>'.$cancellation['customer_id'].'</td>';
									echo '<td>'.$cancellation['skill'].'</td>';
									echo '<td>'.$cancellation['contract'].'</td>';
									echo '<td class="center">'.$cancellation['quantity'].'</td>';
									// echo '<td style="color:red;">-$'.$cancellation['amount'].'</td>';
									echo '<td>'.$cancellation['amount'].'</td>';
								echo '</tr>';
								
								$ctr++;
							}
							
							echo '<tr>';
								echo '<th colspan="11">Total</th>';
								echo '<td colspan="1" style="color:red">-$'.number_format($cancellationsTotalAmount, 2).'</td>';
							echo '</tr>';
						} 
						else
						{
							echo '<tr><td colspan="12">No result found.</td></tr>';
						}
						
						echo '</table>';
					}
				}
								
				if( $page == 'lowNames' )
				{
					$customerQueueViewers = CustomerQueueViewer::model()->findAll(array(
						'with' => 'customer',
						'condition' => '
							t.company NOT IN ("Training Company", "Test Company", "Engagex Inside Sales", "Waxie", "Mountain View Network", "Audigy Group", "Graton", "GunLake")
							AND t.next_available_calling_time NOT IN ("On Hold", "Cancelled", "Removed")
							AND customer.status = 1 
						',
						// 'limit' => 5,
					));

					echo '
						<table id="leadsTbl" class="table table-striped table-bordered table-condensed table-hover">
							<thead>
								<th>#</th>
								<th>Company</th>
								<th>Customer ID</th>
								<th>First Name</th>
								<th>Last Name</th>
								<th>Phone Number</th>
								<th>Email Address</th>
								<th>Staff Email Address</th>
								<th>Skill</th>
								<th>Start Date</th>
								<th>End Date</th>
								<th>Qty</th>
								<th>Current Goal Count</th>
								<th>Dials in Current Month</th>
								<th>Callable Now</th>
								<th>Recertifiable</th>
								<th>Recyclable Names</th>
								<th>Names waiting</th>
								<th>Needs Names</th>
							</thead>';
								
					if( $customerQueueViewers )
					{
						$ctr = 1;
						
						foreach( $customerQueueViewers as $customerQueueViewer )
						{
							$endDate = '';
							
							if( $customerQueueViewer->end_date != '0000-00-00' )
							{
								$endDate = $customerQueueViewer->end_date;
							}
							
							$quantityMinusGoals = $customerQueueViewer->contracted_quantity - $customerQueueViewer->current_goals;
							$roundedQuantyCallableDividedBy9 = round($customerQueueViewer->available_leads/5);
							
							$needsNames = $roundedQuantyCallableDividedBy9 < $quantityMinusGoals ? 'Yes' : 'No';
							
							echo '<tr>';
								echo '<td>'.$ctr.'</td>';
								echo '<td>'.$customerQueueViewer->company.'</td>';
								echo '<td>'.CHtml::link($customerQueueViewer->custom_customer_id, array('/customer/insight/index', 'customer_id'=>$customerQueueViewer->customer_id)).'</td>';
								echo '<td>'.$customerQueueViewer->customer_first_name.'</td>';
								echo '<td>'.$customerQueueViewer->customer_last_name.'</td>';
								echo '<td>'.$customerQueueViewer->phone_number.'</td>';
								
								echo '<td>'.$customerQueueViewer->email_address.'</td>';
								
								echo '<td>';
								
									$officeStaffs = CustomerOfficeStaff::model()->findAll(array(
										'condition' => 'customer_id = :customer_id AND is_deleted=0 AND is_received_low_on_names_email=1',
										'params' => array(
											':customer_id' => $customerQueueViewer->customer_id,
										),
									));
									
									if( $officeStaffs )
									{
										$emailAddresses = array();
										
										foreach( $officeStaffs as $officeStaff )
										{
											$emailAddresses[] = $officeStaff->email_address;
										}
										
										echo implode(', ', $emailAddresses);
									}
								
								echo '</td>';
								
								echo '<td>'.$customerQueueViewer->skill_name.'</td>';
								echo '<td>'.$customerQueueViewer->start_date.'</td>';
								echo '<td>'.$endDate.'</td>';
								echo '<td>'.$customerQueueViewer->contracted_quantity.'</td>';
								echo '<td>'.$customerQueueViewer->current_goals.'</td>';
								echo '<td>'.$customerQueueViewer->current_dials.'</td>';
								echo '<td>'.$customerQueueViewer->available_leads.'</td>';
								echo '<td>'.$customerQueueViewer->recertifiable_leads.'</td>';
								echo '<td>'.$customerQueueViewer->recyclable_leads.'</td>';
								echo '<td>'.$customerQueueViewer->names_waiting.'</td>';
								echo '<td>';
									echo $needsNames;
									
									if( isset($_GET['debug']) )
									{
										echo '<br>';
										echo '<br>';
										echo 'quantity: ' . $customerQueueViewer->contracted_quantity;
										echo '<br>';
										echo 'current goal count: ' . $customerQueueViewer->current_goals;
										echo '<br>';
										echo 'callable now : ' . $customerQueueViewer->available_leads;
										echo '<br>';
										echo 'quantityMinusGoals: ' . $quantityMinusGoals;
										echo '<br>';
										echo 'roundedQuantyCallableDividedBy9: ' . $roundedQuantyCallableDividedBy9;
										echo '<br>';
									}
									
								echo '</td>';
							echo '</tr>';
							
							$ctr++;
						}
					}
					else
					{
						echo '<tr><td colspan="15">No result found.</td></tr>';
					}
					
					echo '</table>';
				}
					
				if($page == 'billingProjections')
				{
					function getCustomerContractCreditAndSubsidy($customer, $contract, $billing_period)
					{
						$contractCreditSubsidys = array();
						$customerSkills = CustomerSkill::model()->findAll(array(
							'with' => 'customer',
							'condition' => '
								t.customer_id = :customer_id AND t.contract_id = :contract_id
								AND customer.is_deleted=0
							',
							'params' => array(
								':customer_id' => $customer->id,
								':contract_id' => $contract->id,
							),

						));
						
						if( $customerSkills )
						{
							foreach($customerSkills as $customerSkill)
							{
								// $customerRemoved = CustomerBillingWindowRemoved::model()->find(array(
									// 'condition' => '
										// customer_id = :customer_id 
										// AND skill_id = :skill_id 
										// AND MONTH(date_created) = MONTH(NOW())
										// AND YEAR(date_created) = YEAR(NOW())
									// ',
									// 'params' => array(
										// ':customer_id' => $customerSkill->customer_id,
										// ':skill_id' => $customerSkill->skill_id,
									// ),
								// ));
									
								// if( isset($customerSkill->contract) && strtotime($billing_period) >= strtotime($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00' )
								// {
									$isBilled = false;
									
									$contract = $customerSkill->contract;
									$contractCreditSubsidys[$contract->id]['contract_name'] = $contract->contract_name;
									$contractCreditSubsidys[$contract->id]['totalCreditAmount'] = 0;
									$contractCreditSubsidys[$contract->id]['totalSubsidyAmount'] = 0;
									
									$existingBillingForCurrentMonth = CustomerBilling::model()->find(array(
										'condition' => '
											customer_id = :customer_id 
											AND contract_id = :contract_id
											AND transaction_type = "Charge"
											AND billing_period = :billing_period
											AND ( anet_responseCode = 1 OR ( amount = 0 AND anet_responseCode IS NULL ))
										',
										'params' => array(
											':customer_id' => $customerSkill->customer_id,
											':contract_id' => $customerSkill->contract_id,
											':billing_period' => $billing_period
										),
										'order' => 'date_created DESC'
									));
									
									if( $existingBillingForCurrentMonth )
									{
										$isBilled = true;
										
										$existingBillingForCurrentMonthVoidorRefund = CustomerBilling::model()->find(array(
											'condition' => '
												customer_id = :customer_id 
												AND contract_id = :contract_id
												AND anet_responseCode = 1
												AND reference_transaction_id = :reference_transaction_id
												AND (
													transaction_type = "Void"
													OR transaction_type = "Refund"
												)
											',
											'params' => array(
												':customer_id' => $customerSkill->customer_id,
												':contract_id' => $customerSkill->contract_id,
												':reference_transaction_id' => $existingBillingForCurrentMonth->id,
											),
											'order' => 'date_created DESC'
										)); 
										
										if( $existingBillingForCurrentMonthVoidorRefund )
										{
											$isBilled = false;
										}
										else
										{
											$isBilled = true;
										}
									}
									
									$totalLeads = 0;
									$totalAmount = 0;
									$subsidyAmount = 0;
									$month = '';
									$latestTransactionType = '';
									$latestTransactionStatus = '';
									
									if($contract->fulfillment_type != null )
									{
										if( $contract->fulfillment_type == Contract::TYPE_FULFILLMENT_GOAL_VOLUME )
										{
											if( !empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME]) )
											{
												foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME] as $key => $subsidyLevel )
												{
													$customerSkillLevelArray = $customerSkill->getCustomerSkillLevelArray();
													$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;

													if( $customerSkillLevelArrayGroup != null )
													{							
														if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
														{
															$totalLeads += ( $subsidyLevel['goal'] * $customerSkillLevelArrayGroup->quantity );
															$totalAmount += ( $customerSkillLevelArrayGroup->quantity * $subsidyLevel['amount'] );
														}
													}
												}
											}

											$customerExtras = CustomerExtra::model()->findAll(array(
												'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id AND status=1 AND year = :year AND month = :month',
												'params' => array(
													':customer_id' => $customerSkill->customer_id,
													':contract_id' => $customerSkill->contract_id,
													':skill_id' => $customerSkill->skill_id,
													':month' => date('n', strtotime($billingPeriod)),
													':year' => date('Y', strtotime($billingPeriod))
												),
											));
											
											if( $customerExtras )
											{
												foreach( $customerExtras as $customerExtra )
												{
													$totalLeads += $customerExtra->quantity;
												}
											}
										}
										else
										{
											if( !empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME]) )
											{
												foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME] as $key => $subsidyLevel )
												{
													$customerSkillLevelArray = $customerSkill->getCustomerSkillLevelArray();
													
													$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;
													
													if( $customerSkillLevelArrayGroup != null )
													{
														if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
														{
															$totalLeads += ( $subsidyLevel['high'] * $customerSkillLevelArrayGroup->quantity );
															$totalAmount += ( $customerSkillLevelArrayGroup->quantity * $subsidyLevel['amount'] );
														}
													}
												}
											}
											
											$customerExtras = CustomerExtra::model()->findAll(array(
												'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id AND status=1 AND year = :year AND month = :month',
												'params' => array(
													':customer_id' => $customerSkill->customer_id,
													':contract_id' => $customerSkill->contract_id,
													':skill_id' => $customerSkill->skill_id,
													':month' => date('n', strtotime($billingPeriod)),
													':year' => date('Y', strtotime($billingPeriod))
												),
											));
											
											if( $customerExtras )
											{
												foreach( $customerExtras as $customerExtra )
												{
													$totalLeads += $customerExtra->quantity;
												}
											}
										}
									
										$contractCreditSubsidys[$contract->id]['totalAmount'] = $totalAmount;
										
										$customerSkillSubsidyLevel = CustomerSkillSubsidyLevel::model()->find(array(
											'condition' => 'customer_id = :customer_id AND customer_skill_id = :customer_skill_id',
											'params' => array(
												':customer_id' => $customerSkill->customer_id,
												':customer_skill_id' => $customerSkill->id,
											),
										));
										
										$customerSkillSubsidy = CustomerSkillSubsidy::model()->find(array(
											'condition' => 'customer_id = :customer_id AND customer_skill_id = :customer_skill_id',
											'params' => array(
												':customer_id' => $customerSkill->customer_id,
												':customer_skill_id' => $customerSkill->id,
											),
										));
										
										// if( $customerSkillSubsidyLevel )
										if( !empty($customerSkillSubsidyLevel) && !empty($customerSkillSubsidy) && $customerSkillSubsidy->status == CustomerSkillSubsidy::STATUS_ACTIVE )
										{
											$subsidy = CompanySubsidyLevel::model()->find(array(
												'condition' => 'id = :id AND type="%"',
												'params' => array(
													':id' => $customerSkillSubsidyLevel->subsidy_level_id,
												),
											));
											
											if( $subsidy )
											{
												$subsidyPercent = $subsidy->value;
												
												$subsidyPercentInDecimal = $subsidyPercent / 100;

												if( $subsidyPercentInDecimal > 0 )
												{
													if( !$isBilled )
													{
														$subsidyAmount = $subsidyPercentInDecimal * $totalAmount; 
													}
													
													$contractCreditSubsidys[$contract->id]['totalSubsidyAmount'] = $subsidyAmount;
												}
											}
										}
									}
									
									$totalCreditAmount = 0;
									$customerCredits = CustomerCredit::model()->findAll(array(
										'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND status=1',
										'params' => array(
											':customer_id' => $customerSkill->customer_id,
											':contract_id' => $customerSkill->contract_id,
										),
									));
									
									if( $customerCredits )
									{
										foreach( $customerCredits as $customerCredit )
										{
											$creditStartDate = date('Y-'.$customerCredit->start_month.'-1');
												
											if( $customerCredit->type == 2 ) //month range
											{
												$creditEndDate = date('Y-'.$customerCredit->end_month.'-t');
												
												if( $customerCredit->start_month >= $customerCredit->end_month )
												{
													$creditEndDate = date('Y-m-d', strtotime('+1 year', strtotime($creditEndDate)));
												}
											}
											else
											{
												$creditEndDate = date('Y-'.$customerCredit->start_month.'-t');
											}
											
											if( (time() >= strtotime($creditStartDate)) && (time() <= strtotime($creditEndDate)) )
											{
												$totalCreditAmount += $customerCredit->amount;
											}
										}
									}
									
									
									$contractCreditSubsidys[$contract->id]['totalCreditAmount'] = $totalCreditAmount;
									
									$totalReducedAmount = ($totalAmount - $totalCreditAmount - $subsidyAmount);
									if( $totalReducedAmount < 0 )
										$totalReducedAmount = 0;
									
									$contractCreditSubsidys[$contract->id]['totalLeads'] = $totalLeads;
									$contractCreditSubsidys[$contract->id]['totalAmount'] = number_format($totalAmount, 2);
									$contractCreditSubsidys[$contract->id]['totalReducedAmount'] = number_format($totalReducedAmount, 2);
									$contractCreditSubsidys[$contract->id]['totalCreditAmount'] = number_format($totalCreditAmount, 2);
									$contractCreditSubsidys[$contract->id]['subsidyAmount'] = number_format($subsidyAmount, 2);
									$contractCreditSubsidys[$contract->id]['isBilled'] = $isBilled;
								// }
							
							}
						}
					
						return $contractCreditSubsidys;
					}
					
					$pendingBillings = array();

					if( isset($_REQUEST['billing_period']) )
					{
						$grandTotalReducedAmount = 0;
						$grandTotalSubsidyAmount = 0;
				
						$newCustomerCtr = 0;
						$cancelledCustomerCtr = 0;
						
						$billingPeriod = $_REQUEST['billing_period'];
						$billingPeriodMonth = date('m', strtotime($_REQUEST['billing_period']));
						$billingPeriodYear = date('Y', strtotime($_REQUEST['billing_period']));
						
						// if( $billingPeriodYear == 2017 )
						// {
							// $customerQueues = CustomerQueueViewer::model()->findAll(array(
								// 'with' => 'customer', 
								// 'condition' => '
									// :billingPeriod >= start_date 
									// AND start_date !="0000-00-00"
									// AND customer.is_deleted=0
									// AND skill_id IN (33,34)
								// ',
								// 'params' => array(
									// ':billingPeriod' => date('Y-m-d', strtotime($_REQUEST['billing_period'])),
								// ),
								// 'order' => 'customer.lastname ASC',
							// ));
						// }
						// else
						// {
							// $customerQueues = CustomerQueueViewer::model()->findAll(array(
								// 'with' => 'customer', 
								// 'condition' => '
									// :billingPeriod >= start_date 
									// AND start_date !="0000-00-00"
									// AND customer.is_deleted=0
									// AND skill_id NOT IN (33,34)
								// ',
								// 'params' => array(
									// ':billingPeriod' => date('Y-m-d', strtotime($billingPeriod)),
								// ),
								// 'order' => 'customer.lastname ASC',
							// ));
						// }

						$customerQueues = CustomerQueueViewer::model()->findAll(array(
							'with' => 'customer',
							'order' => 'customer.lastname ASC',
							// 'condition' => 'customer_id=1619',
							// 'condition' => 'next_available_calling_time NOT IN ("On Hold", "Cancelled")',
							// 'limit' => 100
						));
						
						if( $customerQueues )
						{
							foreach( $customerQueues as $customerQueue )
							{
								$customerSkill = CustomerSkill::model()->find(array(
									'with' => 'customer',
									'condition' => '
										t.customer_id = :customer_id 
										AND t.skill_id = :skill_id 
										AND customer.company_id NOT IN(15, 17,18,23, 24, 25, 26, 27)
										AND customer.status=1
										AND customer.is_deleted=0
									',
									'params' => array(
										':customer_id' => $customerQueue->customer_id,
										':skill_id' => $customerQueue->skill_id,
									),

								));
								
								$customerRemoved = CustomerBillingWindowRemoved::model()->find(array(
									'condition' => '
										customer_id = :customer_id 
										AND skill_id = :skill_id 
										AND MONTH(date_created) = :month
										AND YEAR(date_created) = :year
									',
									'params' => array(
										':customer_id' => $customerQueue->customer_id,
										':skill_id' => $customerQueue->skill_id,
										':month' => date('n', strtotime($billingPeriod)),
										':year' => date('Y', strtotime($billingPeriod))
									),
								));
								
								if( $customerSkill && !empty($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00' && date('Y-m', strtotime($billingPeriod)) >= date('Y-m', strtotime($customerSkill->start_month)) && empty($customerRemoved) )
								{
									if( isset($customerSkill->contract) )
									{
										$contract = $customerSkill->contract;
										$customer = $customerSkill->customer;
										
										$customerIsCallable = false;
										
										$totalLeads = 0;
										$totalAmount = 0;
										$subsidyAmount = 0;
										$month = '';
										$latestTransactionType = '';
										$latestTransactionStatus = '';
										
										$isOnHold = '';
										$isCancelled = '';
										$customerStatus = 'Active';
										
										//patch to turn on subsidy
										// if( $subsidyAmount == 0 )
										// {
											// if(!empty($contract->companySubsidies))
											// {
												// foreach($contract->companySubsidies as $companySubsidy)
												// {
													// $criteria = new CDbCriteria;
													// $criteria->compare('customer_id', $customerQueue->customer_id);
													// $criteria->compare('customer_skill_id', $customerSkill->id);
													// $criteria->compare('subsidy_id', $companySubsidy->id);
													
													// $css = CustomerSkillSubsidy::model()->find($criteria);
													
													// if($css === null)
													// {
														// $css = new CustomerSkillSubsidy;
														// $css->customer_id = $customerQueue->customer_id;
														// $css->customer_skill_id = $customerSkill->id;
														// $css->subsidy_id = $companySubsidy->id;
													// }
													
													// $css->status = CustomerSkillSubsidy::STATUS_ACTIVE;
													// $css->save(false);
												// }
											// }
										// }

										//find if customer has billing for the current month
										$existingBilling = CustomerBilling::model()->find(array(
											'condition' => '
												customer_id = :customer_id AND contract_id = :contract_id
												AND transaction_type = "Charge"
											',
											'params' => array(
												':customer_id' => $customerQueue->customer_id,
												':contract_id' => $contract->id,
											),
											'order' => 'date_created DESC'
										));
										
										$existingBillingForCurrentMonth = CustomerBilling::model()->find(array(
											'condition' => '
												customer_id = :customer_id 
												AND contract_id = :contract_id
												AND transaction_type = "Charge"
												AND billing_period = :billing_period
												AND ( anet_responseCode = 1 OR ( amount = 0 AND anet_responseCode IS NULL ))
											',
											'params' => array(
												':customer_id' => $customerQueue->customer_id,
												':contract_id' => $contract->id,
												':billing_period' => date('M Y', strtotime($billingPeriod))
											),
											'order' => 'date_created DESC'
										));
										
										$creditCardCount = CustomerCreditCard::model()->count(array(
											'condition' => 'customer_id = :customer_id AND status=1',
											'params' => array(
												':customer_id' => $customerQueue->customer_id,
											),
										));
										
										$echecksCount = CustomerEcheck::model()->count(array(
											'condition' => 'customer_id = :customer_id AND status=1',
											'params' => array(
												':customer_id' => $customerQueue->customer_id,
											),
										));
										
										if( empty($existingBilling) || ($existingBilling && empty($existingBillingForCurrentMonth) && $existingBilling->billing_period != $billingPeriod) )
										{
											$customerIsCallable = false;
											
											//check status and start date
											if( isset($customerSkill->contract) && isset($customerSkill->customer) && $customerSkill->customer->status == 1 && $customerSkill->customer->is_deleted == 0 && !empty($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00' && date('Y-m', strtotime($billingPeriod)) >= date('Y-m', strtotime($customerSkill->start_month)) )
											{
												$customerIsCallable = true;
											}
											else
											{
												$customerStatus = 'Inactive';
											}
											
											//check if on hold
											if( $customerSkill->is_contract_hold == 1 )
											{
												if( !empty($customerSkill->is_contract_hold_start_date) && !empty($customerSkill->is_contract_hold_end_date) )
												{
													if( strtotime($billingPeriod) >= strtotime($customerSkill->is_contract_hold_start_date) && strtotime($billingPeriod) <= strtotime($customerSkill->is_contract_hold_end_date) )
													{
														$customerIsCallable = false;
														$isOnHold = 'Y';
														$customerStatus = 'On Hold';
													}
												}
											}
											
											// if( $customerSkill->is_hold_for_billing == 1 )
											// {
												// $customerIsCallable = false;
												// $isOnHold = 'Y';
												// $customerStatus = 'On Hold';
											// }
											
											//check if cancelled
											if( !empty($customerSkill->end_month) && date('Y', strtotime($customerSkill->end_month)) >= 2015 )
											{
												if( strtotime($billingPeriod) >= strtotime($customerSkill->end_month) )
												{
													$customerIsCallable = false;
													$isCancelled = 'Y';
													$customerStatus = 'Cancelled';
												}
											}
											
											// echo 'customerStatus: ' . $customerStatus;
											
											// echo '<br>';
											
											// echo 'customerIsCallable: ' . $customerIsCallable;
											
											// echo '<br><br>';
											
											//&& ($creditCardCount > 0 || $echecksCount > 0)
											if( $customerIsCallable )
											{
												/*
													totalLeads
													totalAmount
													totalReducedAmount
													totalCreditAmount
													subsidyAmount
													isBilled
												*/
												
												$contractCreditSubsidys = getCustomerContractCreditAndSubsidy($customer, $contract, $billingPeriod);	

												$totalAmount = $contractCreditSubsidys[$contract->id]['totalAmount'];										
												$totalCreditAmount = $contractCreditSubsidys[$contract->id]['totalCreditAmount'];		
												$subsidyAmount = $contractCreditSubsidys[$contract->id]['subsidyAmount'];		
												$totalReducedAmount = $contractCreditSubsidys[$contract->id]['totalReducedAmount'];		
												$totalLeads = $contractCreditSubsidys[$contract->id]['totalLeads'];		
										
												$month = date('M Y', strtotime($_REQUEST['billing_period']));

												$paymentMethod = CustomerBilling::model()->getDefaultMethod($customerQueue->customer_id);
												
												$paymentMethod = explode('-', $paymentMethod);
												$paymentMethodType = $paymentMethod[0];
												$paymentMethodId = $paymentMethod[1];
												
												$creditCardType = null;
												$creditCardIsExpired = false;
												
												if( $paymentMethodType == 'creditCard' )
												{
													$creditCard = CustomerCreditCard::model()->findByPk($paymentMethodId);
													
													if( $creditCard )
													{
														$creditCardType = $creditCard->credit_card_type;
														
														if( strtotime($_REQUEST['billing_period']) >= strtotime($creditCard->expiration_year.'-'.$creditCard->expiration_month.'-01') )
														{
															$creditCardIsExpired = true;
														}
													}
												}
												else
												{
													if( $paymentMethodType == '-' )
													{
														$paymentMethodType = '';
													}
												}
																								
												//credit amount should not be over the Amount, for the customer will ask it to be billed next month -aug 9, 2016
												if($totalCreditAmount > $totalAmount)
												{
													$totalCreditAmount = $totalAmount - $subsidyAmount;
												}
												
												// if( in_array($contract->contract_name, array('Farmers Per Appointment 2016 FOLIO','Farmers Per Name 2016 FOLIO')) )
												// {
													// $totalReducedAmount = ($totalAmount - $totalCreditAmount);
												// }
												// else
												// {
													// $totalReducedAmount = ($totalAmount - $totalCreditAmount - $subsidyAmount);
												// }

												$totalReducedAmount = abs($totalAmount - $subsidyAmount);
												
												if( $totalCreditAmount < 0 )
												{
													$totalReducedAmount = $totalReducedAmount + abs($totalCreditAmount);
												}
												else
												{
													$totalReducedAmount = $totalReducedAmount - abs($totalCreditAmount);
												}
												
												if( $totalReducedAmount < 0 )
												{
													$totalReducedAmount = 0;
												}
												
												$totalReducedAmount = number_format($totalReducedAmount, 2);
												
												$grandTotalReducedAmount += $totalReducedAmount;
												$grandTotalSubsidyAmount += $subsidyAmount;
												
												$endDate = '';
												
												if( !empty($customerSkill->end_month) && $customerSkill->end_month != '0000-00-00' )
												{
													$endDate = date('m/d/Y', strtotime($customerSkill->end_month));
												}
												
												if( date('Y-m', strtotime($customerSkill->start_month)) == date('Y-m', strtotime('+1 month', strtotime($billingPeriod))) )
												{
													$newCustomerCtr++;
												}
												
												if( date('Y-m', strtotime($customerSkill->end_month)) == date('Y-m', strtotime($billingPeriod)) )
												{
													$cancelledCustomerCtr++;
												}
												
												$pendingBillings[$customerQueue->customer_id.'-'.$customerQueue->skill_id] = array(
													'customer_id' => $customerQueue->customer_id,
													'agent_id' => $customerQueue->customer->custom_customer_id,
													'status' => $customerStatus,
													'hold' => $isOnHold,
													'cancel' => $isCancelled,
													'start_date' => date('m/d/Y', strtotime($customerSkill->start_month)),
													'end_date' => $endDate,
													'customer_name' => $customerQueue->customer->getFullName(),
													'company' => $customerQueue->company,
													'skill' => $customerQueue->skill->skill_name,
													'contract' => $customerQueue->contract_name,
													'quantity' => $totalLeads,
													'billing_cycle' => $month,
													'payment_method' => $paymentMethodType,
													'credit_card_type' => $creditCardType,
													'action' => 'Charge',
													'original_amount' => $totalAmount,
													'billing_credit' => $totalCreditAmount,
													'subsidy' => $subsidyAmount,
													'reduced_amount' => $totalReducedAmount,
													'credit_is_expired' => $creditCardIsExpired
												);
											}
										}
									}
								}
							}
						}
						
						// echo '<div class="row">';
						
							// echo '<div class="col-sm-12">';
								// echo '<div class="col-sm-4">New: '.$newCustomerCtr.'</div>';
								// echo '<div class="col-sm-4">Cancel: '.$cancelledCustomerCtr.'</div>';
								// echo '<div class="col-sm-4">Net: '.($newCustomerCtr - $cancelledCustomerCtr).'</div>';
							// echo '</div>';
							 
						// echo '</div>';
						
						// echo '<div class="space-12"></div>';
						
						echo '<div class="row">';
						
							echo '<div class="col-sm-12">';
								echo '<div class="col-sm-4">Credit Card - $'.number_format($grandTotalReducedAmount, 2).'</div>';
								echo '<div class="col-sm-4">Subsidy - $'.number_format($grandTotalSubsidyAmount, 2).'</div>';
								echo '<div class="col-sm-4">Total - $'.number_format($grandTotalReducedAmount + $grandTotalSubsidyAmount, 2).'</div>';
							echo '</div>';
							 
						echo '</div>';
						
						echo '<br>';

						echo '
						<table id="leadsTbl" class="table table-striped table-bordered table-condensed table-hover">
							<thead>
								<th>#</th>
								<th>Agent ID</th>
								<th>Status</th>
								<th>Start Date</th>
								<th>End Date</th>
								<th>Customer Name</th>
								<th>Company</th>
								<th>Skill</th>
								<th>Contract</th>
								<th>Quantity</th>
								<th>Billing Cycle</th>
								<th>Payment Method</th>
								<th>Credit Card Type</th>
								<th>Action</th>
								<th>Original Amount</th>
								<th>Billing Credit</th>
								<th>Subsidy</th>
								<th>Reduced Amount</th>
							</thead>';
							 
							if( $pendingBillings )
							{
								$ctr = 1;
								
								// echo '<pre>';
									// print_r($pendingBillings);
								// echo '</pre>';
								
								foreach( $pendingBillings as $pendingBilling )
								{
									$rowClass = '';
									
									if( $pendingBilling['credit_is_expired'] )
									{
										$rowClass = 'danger';
									}
									
									echo '<tr class="'.$rowClass.'">';
									
										echo '<td>'.$ctr.'</td>';
										echo '<td>'.$pendingBilling['agent_id'].'</td>';
										echo '<td>'.$pendingBilling['status'].'</td>';
										// echo '<td>'.$pendingBilling['hold'].'</td>';
										// echo '<td>'.$pendingBilling['cancel'].'</td>';
										echo '<td>'.$pendingBilling['start_date'].'</td>';
										echo '<td>'.$pendingBilling['end_date'].'</td>';
										echo '<td>'.CHtml::link($pendingBilling['customer_name'], array('/customer/insight/index', 'customer_id'=>$pendingBilling['customer_id'])).'</td>';
										echo '<td>'.$pendingBilling['company'].'</td>';
										echo '<td>'.$pendingBilling['skill'].'</td>';
										echo '<td>'.$pendingBilling['contract'].'</td>';
										echo '<td class="center">'.$pendingBilling['quantity'].'</td>';
										echo '<td>'.$pendingBilling['billing_cycle'].'</td>';
										echo '<td>'.$pendingBilling['payment_method'].'</td>';
										echo '<td>'.$pendingBilling['credit_card_type'].'</td>';
										echo '<td>'.$pendingBilling['action'].'</td>';
										echo '<td class="center">$'.$pendingBilling['original_amount'].'</td>';
										echo '<td class="center">$'.$pendingBilling['billing_credit'].'</td>';
										echo '<td class="center">$'.$pendingBilling['subsidy'].'</td>';
										echo '<td class="center">$'.$pendingBilling['reduced_amount'].'</td>';
									
									echo '</tr>';
									
									$ctr++;
								}
							}
							
							echo '</table>';
					}
				}
					
				if($page == 'impactReport')
				{
					if( $month1 && $month2 && $month3 && $month4 && $month5 && $month6 && $month7 && $month8 )
					{
						echo '<div class="row">';
						
							echo '<div class="col-sm-12">';
							
								echo '<div id="container" style="min-width: 310px; height: 400px; margin: 0 auto"></div>';
						
								echo '<div class="space-12"></div>';
							
								echo '<table class="table table-striped table-bordered table-condensed table-hover">';
								
									echo '<tr>';
										
										echo '<td></td>';
										echo '<td class="center">'.$month1->month_name.'</td>';
										echo '<td class="center">'.$month2->month_name.'</td>';
										echo '<td class="center">'.$month3->month_name.'</td>';
										echo '<td class="center">'.$month4->month_name.'</td>';
										echo '<td class="center">'.$month5->month_name.'</td>';
										echo '<td class="center">'.$month6->month_name.'</td>';
										echo '<td class="center">'.$month7->month_name.'</td>';
										echo '<td class="center">'.$month8->month_name.'</td>';
			
										
									echo '</tr>';
									
									echo '<tr>';
										
										echo '<td align="left">Remaining</td>';
										echo '<th>'.CHtml::link('$'.number_format($month1->projected, 2), array('reports', 'page'=>'billingProjections', 'billing_period'=>$month1->month_date), array('target'=>'_blank')).'</th>';
										echo '<th>'.CHtml::link('$'.number_format($month2->projected, 2), array('reports', 'page'=>'billingProjections', 'billing_period'=>$month2->month_date), array('target'=>'_blank')).'</th>';
										echo '<th>'.CHtml::link('$'.number_format($month3->projected, 2), array('reports', 'page'=>'billingProjections', 'billing_period'=>$month3->month_date), array('target'=>'_blank')).'</th>';
										echo '<th>'.CHtml::link('$'.number_format($month4->projected, 2), array('reports', 'page'=>'billingProjections', 'billing_period'=>$month4->month_date), array('target'=>'_blank')).'</th>';
										echo '<th>'.CHtml::link('$'.number_format($month5->projected, 2), array('reports', 'page'=>'billingProjections', 'billing_period'=>$month5->month_date), array('target'=>'_blank')).'</th>';
										echo '<th>'.CHtml::link('$'.number_format($month6->projected, 2), array('reports', 'page'=>'billingProjections', 'billing_period'=>$month6->month_date), array('target'=>'_blank')).'</th>';
										echo '<th>'.CHtml::link('$'.number_format($month7->projected, 2), array('reports', 'page'=>'billingProjections', 'billing_period'=>$month7->month_date), array('target'=>'_blank')).'</th>';
										echo '<th>'.CHtml::link('$'.number_format($month8->projected, 2), array('reports', 'page'=>'billingProjections', 'billing_period'=>$month8->month_date), array('target'=>'_blank')).'</th>';
			
									echo '</tr>';
									
									echo '<tr>';
										
										echo '<td align="right">Subsidy</td>';
										echo '<td>$'.number_format($month1->projected_subsidy, 2).'</td>';
										echo '<td>$'.number_format($month2->projected_subsidy, 2).'</td>';
										echo '<td>$'.number_format($month3->projected_subsidy, 2).'</td>';
										echo '<td>$'.number_format($month4->projected_subsidy, 2).'</td>';
										echo '<td>$'.number_format($month5->projected_subsidy, 2).'</td>';
										echo '<td>$'.number_format($month6->projected_subsidy, 2).'</td>';
										echo '<td>$'.number_format($month7->projected_subsidy, 2).'</td>';
										echo '<td>$'.number_format($month8->projected_subsidy, 2).'</td>';

									echo '</tr>';
									
									echo '<tr>';
										
										echo '<td align="right">Credit Card</td>';
										echo '<td>$'.number_format($month1->projected_credit_card, 2).'</td>';
										echo '<td>$'.number_format($month2->projected_credit_card, 2).'</td>';
										echo '<td>$'.number_format($month3->projected_credit_card, 2).'</td>';
										echo '<td>$'.number_format($month4->projected_credit_card, 2).'</td>';
										echo '<td>$'.number_format($month5->projected_credit_card, 2).'</td>';
										echo '<td>$'.number_format($month6->projected_credit_card, 2).'</td>';
										echo '<td>$'.number_format($month7->projected_credit_card, 2).'</td>';
										echo '<td>$'.number_format($month8->projected_credit_card, 2).'</td>';


									echo '</tr>';
									
									echo '<tr><td colspan="9"><br></td></tr>';
									
									echo '<tr>';
										
										echo '<td>Collected*</td>';

										echo '<th>'.CHtml::link('$'.number_format($month1->actual, 2), array('reports', 'page'=>'creditCardTransactions', 'dateFilterStart'=>date('m/01/Y', strtotime($month1->month_name)), 'dateFilterEnd'=>date('m/d/Y', strtotime($month1->month_name))), array('target'=>'_blank')).'</th>';
										echo '<th>'.CHtml::link('$'.number_format($month2->actual, 2), array('reports', 'page'=>'creditCardTransactions', 'dateFilterStart'=>date('m/01/Y', strtotime($month2->month_name)), 'dateFilterEnd'=>date('m/d/Y', strtotime($month2->month_name))), array('target'=>'_blank')).'</th>';
										echo '<th>$'.number_format($month3->actual, 2).'</th>';
										echo '<th>$'.number_format($month4->actual, 2).'</th>';
										echo '<th>$'.number_format($month5->actual, 2).'</th>';
										echo '<th>$'.number_format($month6->actual, 2).'</th>';
										echo '<th>$'.number_format($month7->actual, 2).'</th>';
										echo '<th>$'.number_format($month8->actual, 2).'</th>'; 
										
									echo '</tr>';
									
									echo '<tr>';
										
										echo '<td align="right">Subsidy</td>';
										echo '<td>$'.number_format($month1->actual_subsidy, 2).'</td>';
										echo '<td>$'.number_format($month2->actual_subsidy, 2).'</td>';
										echo '<td>$0.00</td>';
										echo '<td>$0.00</td>';
										echo '<td>$0.00</td>';
										echo '<td>$0.00</td>';
										echo '<td>$0.00</td>';
										echo '<td>$0.00</td>';

									echo '</tr>';
									
									echo '<tr>';
										
										echo '<td align="right">Credit Card</td>';
										echo '<td>$'.number_format($month1->actual_credit_card, 2).'</td>';
										echo '<td>$'.number_format($month2->actual_credit_card, 2).'</td>';
										echo '<td>$0.00</td>';
										echo '<td>$0.00</td>';
										echo '<td>$0.00</td>';
										echo '<td>$0.00</td>';
										echo '<td>$0.00</td>';
										echo '<td>$0.00</td>';

									echo '</tr>';
									
									echo '<tr><td colspan="9"><br></td></tr>';
									
									echo '<tr>';
										
										echo '<th>Projected</th>';
										echo '<th><u>$'.number_format($month1->projected + $month1->actual, 2).'</u></th>';
										echo '<th><u>$'.number_format($month2->projected + $month2->actual, 2).'</u></th>';
										echo '<th><u>$'.number_format($month3->projected + $month3->actual, 2).'</u></th>';
										echo '<th><u>$'.number_format($month4->projected + $month4->actual, 2).'</u></th>';
										echo '<th><u>$'.number_format($month5->projected + $month5->actual, 2).'</u></th>';
										echo '<th><u>$'.number_format($month6->projected + $month6->actual, 2).'</u></th>';
										echo '<th><u>$'.number_format($month7->projected + $month7->actual, 2).'</u></th>';
										echo '<th><u>$'.number_format($month8->projected + $month8->actual, 2).'</u></th>';
	
										
									echo '</tr>';
									
									echo '<tr>';
										
										echo '<th>Customer Count</th>';
										echo '<th>'.$month1->projected_customer_count.'</th>';
										echo '<th>'.$month2->projected_customer_count.'</th>';
										echo '<th>'.$month3->projected_customer_count.'</th>';
										echo '<th>'.$month4->projected_customer_count.'</th>';
										echo '<th>'.$month5->projected_customer_count.'</th>';
										echo '<th>'.$month6->projected_customer_count.'</th>';
										echo '<th>'.$month7->projected_customer_count.'</th>';
										echo '<th>'.$month8->projected_customer_count.'</th>';
 
									echo '</tr>';
									
									echo '<tr><td colspan="9"><br></td></tr>';
									
									// if( Yii::app()->user->account->id == 2 )
									// {
										echo '<tr><td colspan="9"><br></td></tr>';
										
										echo '<tr>';
											echo '<td>Sales Starting ($)</td>';
											echo '<td>'.number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month1->month_name))->sales_starting_amount, 2).'</td>';
											echo '<td>'.number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month2->month_name))->sales_starting_amount, 2).'</td>';
											echo '<td>'.number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month3->month_name))->sales_starting_amount, 2).'</td>';
											echo '<td>'.number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month4->month_name))->sales_starting_amount, 2).'</td>';
											echo '<td>'.number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month5->month_name))->sales_starting_amount, 2).'</td>';
											echo '<td>'.number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month6->month_name))->sales_starting_amount, 2).'</td>';
											echo '<td>'.number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month7->month_name))->sales_starting_amount, 2).'</td>';
											echo '<td>'.number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month8->month_name))->sales_starting_amount, 2).'</td>';
										echo '</tr>';
										
										echo '<tr>';
											echo '<td>Sales Starting (count)</td>';
											echo '<td>'.ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month1->month_name))->sales_starting_count.'</td>';
											echo '<td>'.ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month2->month_name))->sales_starting_count.'</td>';
											echo '<td>'.ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month3->month_name))->sales_starting_count.'</td>';
											echo '<td>'.ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month4->month_name))->sales_starting_count.'</td>';
											echo '<td>'.ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month5->month_name))->sales_starting_count.'</td>';
											echo '<td>'.ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month6->month_name))->sales_starting_count.'</td>';
											echo '<td>'.ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month7->month_name))->sales_starting_count.'</td>';
											echo '<td>'.ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month8->month_name))->sales_starting_count.'</td>';
										echo '</tr>';
										
										echo '<tr>';
											echo '<td>Cancels Affecting ($)</td>';
											echo '<td>'.number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month1->month_name))->cancels_affecting_amount, 2).'</td>';
											echo '<td>'.number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month2->month_name))->cancels_affecting_amount, 2).'</td>';
											echo '<td>'.number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month3->month_name))->cancels_affecting_amount, 2).'</td>';
											echo '<td>'.number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month4->month_name))->cancels_affecting_amount, 2).'</td>';
											echo '<td>'.number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month5->month_name))->cancels_affecting_amount, 2).'</td>';
											echo '<td>'.number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month6->month_name))->cancels_affecting_amount, 2).'</td>';
											echo '<td>'.number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month7->month_name))->cancels_affecting_amount, 2).'</td>';
											echo '<td>'.number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month8->month_name))->cancels_affecting_amount, 2).'</td>';
										echo '</tr>';
										
										echo '<tr>';
											echo '<td>Cancels Affecting (count)</td>';
											echo '<td>'.ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month1->month_name))->cancels_affecting_count.'</td>';
											echo '<td>'.ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month2->month_name))->cancels_affecting_count.'</td>';
											echo '<td>'.ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month3->month_name))->cancels_affecting_count.'</td>';
											echo '<td>'.ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month4->month_name))->cancels_affecting_count.'</td>';
											echo '<td>'.ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month5->month_name))->cancels_affecting_count.'</td>';
											echo '<td>'.ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month6->month_name))->cancels_affecting_count.'</td>';
											echo '<td>'.ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month7->month_name))->cancels_affecting_count.'</td>';
											echo '<td>'.ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month8->month_name))->cancels_affecting_count.'</td>';

										echo '</tr>';
										
										echo '<tr>';
											echo '<td>Net Change ($)</td>';
											echo '<td>'.number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month1->month_name))->sales_starting_amount - ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month1->month_name))->cancels_affecting_amount, 2).'</td>';
											echo '<td>'.number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month2->month_name))->sales_starting_amount - ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month2->month_name))->cancels_affecting_amount, 2).'</td>';
											echo '<td>'.number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month3->month_name))->sales_starting_amount - ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month3->month_name))->cancels_affecting_amount, 2).'</td>';
											echo '<td>'.number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month4->month_name))->sales_starting_amount - ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month4->month_name))->cancels_affecting_amount, 2).'</td>';
											echo '<td>'.number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month5->month_name))->sales_starting_amount - ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month5->month_name))->cancels_affecting_amount, 2).'</td>';
											echo '<td>'.number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month6->month_name))->sales_starting_amount - ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month6->month_name))->cancels_affecting_amount, 2).'</td>';
											echo '<td>'.number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month7->month_name))->sales_starting_amount - ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month7->month_name))->cancels_affecting_amount, 2).'</td>';
											echo '<td>'.number_format(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month8->month_name))->sales_starting_amount - ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month8->month_name))->cancels_affecting_amount, 2).'</td>';
										echo '</tr>';
										
										echo '<tr>';
											echo '<td>Net Change (count)</td>';
											echo '<td>'.(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month1->month_name))->sales_starting_count - ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month1->month_name))->cancels_affecting_count).'</td>';
											echo '<td>'.(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month2->month_name))->sales_starting_count - ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month2->month_name))->cancels_affecting_count).'</td>';
											echo '<td>'.(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month3->month_name))->sales_starting_count - ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month3->month_name))->cancels_affecting_count).'</td>';
											echo '<td>'.(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month4->month_name))->sales_starting_count - ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month4->month_name))->cancels_affecting_count).'</td>';
											echo '<td>'.(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month5->month_name))->sales_starting_count - ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month5->month_name))->cancels_affecting_count).'</td>';
											echo '<td>'.(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month6->month_name))->sales_starting_count - ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month6->month_name))->cancels_affecting_count).'</td>';
											echo '<td>'.(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month7->month_name))->sales_starting_count - ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month7->month_name))->cancels_affecting_count).'</td>';
											echo '<td>'.(ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month8->month_name))->sales_starting_count - ImpactReportSummary::model()->findByAttributes(array('month_name'=>$month8->month_name))->cancels_affecting_count).'</td>';
										echo '</tr>';
									// }
						
								echo '</table>';
								
							echo '</div>';
							
						echo '</div>';
					}
					else
					{
						echo '<div class="row">';
						
							echo '<div class="col-sm-12 center blue" style="font-size:32px;">';
								echo ' <i class="fa fa-cog fa-spin"></i> Records are being updated...';
							echo '</div>';
							
						echo '</div>';
					}
				}
		
				if( $page == 'listImportLog' )
				{
					if( $dateFilterStart != "" && $dateFilterEnd != "" )
					{
						$models = CustomerHistory::model()->findAll(array(
							'condition' => '
								content LIKE"%Imported%"
								AND DATE(date_created) >= "'.date('Y-m-d', strtotime($dateFilterStart)).'" 
								AND DATE(date_created) <= "'.date('Y-m-d', strtotime($dateFilterEnd)).'"
							',
							'order' => 'date_created DESC',
						));
						
						echo '<div class="row">';
						
							echo '<div class="col-sm-12">';
							
								echo '<table class="table table-striped table-bordered table-condensed table-hover">';
									
									echo '<tr>';
										echo '<th>#</th>';
										echo '<th>Import Date/Time</th>';
										echo '<th>User</th>';
										echo '<th>Customer Name</th>';
										echo '<th>Agent ID</th>';
										echo '<th>List Name</th>';
										echo '<th>Total</th>';
										echo '<th>Imported Count</th>';
										echo '<th>Duplicate Count</th>';
										echo '<th>Bad Count</th>';
										echo '<th>% Not Imported</th>';
										echo '<th>List Status</th>';
										echo '<th>Email</th>';
									echo '</tr>';
									
									if( $models )
									{
										$ctr = 1;
										
										foreach( $models as $model )
										{
											$list = Lists::model()->findByPk($model->model_id);
											
											$listStatus = $list->status == 1 ? 'Active' : 'Inactive';
											
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
	
	
											$total = $importedCount + $duplicateCount + $badCount;
											
											$percentageOfNotImported = 0;
											
											if($total != 0)
												$percentageOfNotImported = (($duplicateCount + $badCount) / $total) * 100;
											
											
											echo '<tr>';

												echo '<td>'.$ctr.'</td>';
											
												echo '<td>'.$date->format('m/d/Y g:i A').'</td>';
												
												echo '<td>';
													if( isset($model->account) )
													{
														if( $model->account->account_type_id == Account::TYPE_CUSTOMER )
														{
															echo $model->account->customer->firstname.' '.$model->account->customer->lastname;
														}
														elseif( $model->account->account_type_id == TYPE_CUSTOMER_OFFICE_STAFF )
														{
															echo $model->account->customerOfficeStaff->staff_name;
														}
														else
														{
															echo $model->account->getFullName();
														}
														
													}
												echo '</td>';
												
												echo '<td>'.$model->customer->firstname.' '.$model->customer->lastname.'</td>';
												echo '<td>'.$model->customer->custom_customer_id.'</td>';
												echo '<td>'.$list->name.'</td>';
												echo '<td>'.$total.'</td>';
												echo '<td>'.$importedCount.'</td>';
												echo '<td>'.$duplicateCount.'</td>';
												echo '<td>'.$badCount.'</td>';
												echo '<td>'.number_format($percentageOfNotImported,2).'%</td>';
												
												echo '<td>'.$listStatus.'</td>';
												
												echo '<td>';
								
													$officeStaffs = CustomerOfficeStaff::model()->findAll(array(
														'condition' => 'customer_id = :customer_id AND is_deleted=0 AND is_received_low_on_names_email=1',
														'params' => array(
															':customer_id' => $model->customer_id,
														),
													));
													
													if( $officeStaffs )
													{
														$emailAddresses = array();
														
														foreach( $officeStaffs as $officeStaff )
														{
															$emailAddresses[] = $officeStaff->email_address;
														}
														
														echo implode(', ', $emailAddresses);
													}
												
												echo '</td>';
												
											echo '</tr>';
											
											$ctr++;
										}
									}
									
								echo '</table>';
								
							echo '</div>';
							
						echo '</div>';
					}
				}
				
				if( $page == 'agentStates' )
				{
					if( $dateFilterStart != '' && $dateFilterEnd != '' )
					{
						$sql = "
							SELECT a.id AS agent_id, CONCAT(au.`first_name`, ' ', au.`last_name`) AS agent_name,
							(
								SELECT SUM(
									CASE WHEN time_out IS NOT NULL THEN TIME_TO_SEC(TIMEDIFF(time_out, time_in))
										ELSE TIME_TO_SEC(TIMEDIFF(DATE_SUB(NOW(), INTERVAL 1 HOUR), time_in)) 
									END
								)
								FROM ud_account_login_tracker alt
								WHERE alt.account_id = a.`id`
								AND alt.time_in >= '".date('Y-m-d 00:00:00', strtotime($dateFilterStart))."' 
								AND alt.time_in <= '".date('Y-m-d 23:59:59', strtotime($dateFilterEnd))."'
							) AS login_total_seconds,
							(
								SELECT SUM(
									CASE WHEN end_time IS NOT NULL THEN TIME_TO_SEC(TIMEDIFF(end_time, start_time))
										ELSE TIME_TO_SEC(TIMEDIFF(DATE_SUB(NOW(), INTERVAL 1 HOUR), start_time)) 
									END
								)
								FROM ud_account_login_state als
								WHERE als.account_id = a.`id`
								AND als.start_time >= '".date('Y-m-d 00:00:00', strtotime($dateFilterStart))."' 
								AND als.start_time <= '".date('Y-m-d 23:59:59', strtotime($dateFilterEnd))."'
								AND als.type = 1 
							) AS available_total_seconds,
							(
								SELECT SUM(
									CASE WHEN end_time IS NOT NULL THEN TIME_TO_SEC(TIMEDIFF(end_time, start_time))
										ELSE TIME_TO_SEC(TIMEDIFF(DATE_SUB(NOW(), INTERVAL 1 HOUR), start_time)) 
									END
								)
								FROM ud_account_login_state als
								WHERE als.account_id = a.`id`
								AND als.start_time >= '".date('Y-m-d 00:00:00', strtotime($dateFilterStart))."' 
								AND als.start_time <= '".date('Y-m-d 23:59:59', strtotime($dateFilterEnd))."'
								AND als.type = 2
							) AS unavailable_total_seconds,
							(
								SELECT SUM(
									CASE WHEN end_time IS NOT NULL THEN TIME_TO_SEC(TIMEDIFF(end_time, start_time))
										ELSE TIME_TO_SEC(TIMEDIFF(DATE_SUB(NOW(), INTERVAL 1 HOUR), start_time)) 
									END
								)
								FROM ud_account_login_state als
								WHERE als.account_id = a.`id`
								AND als.start_time >= '".date('Y-m-d 00:00:00', strtotime($dateFilterStart))."' 
								AND als.start_time <= '".date('Y-m-d 23:59:59', strtotime($dateFilterEnd))."'
								AND als.type = 3
							) AS lunch_total_seconds,
							(
								SELECT SUM(
									CASE WHEN end_time IS NOT NULL THEN TIME_TO_SEC(TIMEDIFF(end_time, start_time))
										ELSE TIME_TO_SEC(TIMEDIFF(DATE_SUB(NOW(), INTERVAL 1 HOUR), start_time)) 
									END
								)
								FROM ud_account_login_state als
								WHERE als.account_id = a.`id`
								AND als.start_time >= '".date('Y-m-d 00:00:00', strtotime($dateFilterStart))."' 
								AND als.start_time <= '".date('Y-m-d 23:59:59', strtotime($dateFilterEnd))."'
								AND als.type = 4
							) AS break_total_seconds,
							(
								SELECT SUM(
									CASE WHEN end_time IS NOT NULL THEN TIME_TO_SEC(TIMEDIFF(end_time, start_time))
										ELSE TIME_TO_SEC(TIMEDIFF(DATE_SUB(NOW(), INTERVAL 1 HOUR), start_time)) 
									END
								)
								FROM ud_account_login_state als
								WHERE als.account_id = a.`id`
								AND als.start_time >= '".date('Y-m-d 00:00:00', strtotime($dateFilterStart))."' 
								AND als.start_time <= '".date('Y-m-d 23:59:59', strtotime($dateFilterEnd))."'
								AND als.type = 5 
							) AS meeting_total_seconds,
							(
								SELECT SUM(
									CASE WHEN end_time IS NOT NULL THEN TIME_TO_SEC(TIMEDIFF(end_time, start_time))
										ELSE TIME_TO_SEC(TIMEDIFF(DATE_SUB(NOW(), INTERVAL 1 HOUR), start_time)) 
									END
								)
								FROM ud_account_login_state als
								WHERE als.account_id = a.`id`
								AND als.start_time >= '".date('Y-m-d 00:00:00', strtotime($dateFilterStart))."' 
								AND als.start_time <= '".date('Y-m-d 23:59:59', strtotime($dateFilterEnd))."'
								AND als.type = 6 
							) AS training_total_seconds
							FROM ud_account a
							LEFT JOIN ud_account_user au ON au.`account_id` = a.`id`
							WHERE au.job_title IN ('Call Agent', 'Team Leader') 
							AND a.`id` NOT IN (4, 5)
							AND a.status = 1
							ORDER BY au.last_name ASC
						";
						
						// echo '<br><br>';
						// echo $sql;
						// echo '<br><br>';
						
						$connection = Yii::app()->db;
						$command = $connection->createCommand($sql);
						$agents = $command->queryAll();
						
						$agentData = array();
						$totalStateValues = array();
						
						if( $agents )
						{
							foreach( $agents as $agent )
							{
								$totalStateValues['login_time'] += $agent['login_total_seconds'];
								$totalStateValues['available'] += $agent['available_total_seconds'];
								$totalStateValues['unavailable'] += $agent['unavailable_total_seconds'];
								$totalStateValues['lunch'] += $agent['lunch_total_seconds'];
								$totalStateValues['break'] += $agent['break_total_seconds'];
								$totalStateValues['meeting'] += $agent['meeting_total_seconds'];
								$totalStateValues['training'] += $agent['training_total_seconds'];
								
								$agentData[ $agent['agent_id'] ] = array(
									'agent_name' => $agent['agent_name'],
									'available_total_seconds' => AccountLoginState::formatTime($agent['available_total_seconds']),
									'login_time' => AccountLoginState::formatTime($agent['login_total_seconds']),
									'available' => AccountLoginState::formatTime($agent['available_total_seconds']),
									'unavailable' => AccountLoginState::formatTime($agent['unavailable_total_seconds']),
									'lunch' => AccountLoginState::formatTime($agent['lunch_total_seconds']),
									'break' => AccountLoginState::formatTime($agent['break_total_seconds']),
									'meeting' => AccountLoginState::formatTime($agent['meeting_total_seconds']),
									'training' => AccountLoginState::formatTime($agent['training_total_seconds']),
								);
							}
						}
						
						echo '<table id="leadsTbl" class="table table-striped table-bordered table-condensed table-hover">
								<thead>
									<th></th>
									<th>Login Time</th>
									<th>Available</th>
									<th>Unavailable</th>
									<th>Lunch</th>
									<th>Break</th>
									<th>Meeting</th>
									<th>Training</th>
								</thead>';
								
							usort($agentData, function ($a, $b) {
								if($a['available_total_seconds'] == $b['available_total_seconds']) return 0;
								return $a['available_total_seconds'] < $b['available_total_seconds'] ? 1 : -1;
							});
							
							if( $agentData )
							{
								echo '<tr>';
									echo '<td>Total</td>';
									echo '<td>'.AccountLoginState::formatTime($totalStateValues['login_time']).'</td>';
									echo '<td>'.AccountLoginState::formatTime($totalStateValues['available']).'</td>';
									echo '<td>'.AccountLoginState::formatTime($totalStateValues['unavailable']).'</td>';
									echo '<td>'.AccountLoginState::formatTime($totalStateValues['lunch']).'</td>';
									echo '<td>'.AccountLoginState::formatTime($totalStateValues['break']).'</td>';
									echo '<td>'.AccountLoginState::formatTime($totalStateValues['meeting']).'</td>';
									echo '<td>'.AccountLoginState::formatTime($totalStateValues['training']).'</td>';
								echo '</tr>';
								
								echo '<tr><td colspan="8">&nbsp;</td></tr>';
								
								foreach( $agentData as $data )
								{
									echo '<tr>';
										echo '<td>'.$data['agent_name'].'</td>';
										echo '<td>'.$data['login_time'].'</td>';
										echo '<td>'.$data['available'].'</td>';
										echo '<td>'.$data['unavailable'].'</td>';
										echo '<td>'.$data['lunch'].'</td>';
										echo '<td>'.$data['break'].'</td>';
										echo '<td>'.$data['meeting'].'</td>';
										echo '<td>'.$data['training'].'</td>';
									echo '</tr>';
								}
							}
							
						echo '</table>';
					}
				}
					
				if( $page == 'commision' )
				{
					if( $dateFilterStart != '' && $dateFilterEnd != '' )
					{
						$connection = Yii::app()->db;
						
						$sql = '
							SELECT * FROM ud_customer_skill 
							WHERE id IN (
								SELECT max(id) FROM ud_customer_skill
								WHERE date_created >= "'.date('Y-m-d 00:00:00', strtotime($dateFilterStart)).'" 
								AND date_created <= "'.date('Y-m-d 23:59:59', strtotime($dateFilterEnd)).'"
								GROUP BY customer_id  
							) 
							ORDER BY id DESC
						';
						
						$command = $connection->createCommand($sql);
						$models = $command->queryAll();
						
						// $models = CustomerSkill::model()->findAll(array(
							// 'condition' => '
								// date_created >= "'.date('Y-m-d 00:00:00', strtotime($dateFilterStart)).'" 
								// AND date_created <= "'.date('Y-m-d 23:59:59', strtotime($dateFilterEnd)).'"
							// ',
							// 'order' => 'date_created DESC'
						// ));
						
						echo '<div class="row">';
						
							echo '<div class="col-sm-12">';
							
								echo '<table class="table table-striped table-bordered table-condensed table-hover">';
									
									echo '<tr>';
										echo '<th>#</th>';
										echo '<th>Date</th>';
										echo '<th>Sales Agent</th>';
										echo '<th>Customer Name</th>';
										echo '<th>Start Date</th>';
										echo '<th>Status</th>';
										echo '<th>Skill</th>';
										echo '<th class="center">Qty</th>';
										echo '<th class="center">Original Amount</th>';
										echo '<th class="center">Credit</th>';
										echo '<th class="center">Charged</th>';
										echo '<th class="center">Split</th>';
										echo '<th class="center">Commision</th>';
									echo '</tr>';
						
									if( $models )
									{
										$ctr = 1;
										
										foreach( $models as $model )
										{
											$totalLeads = 0;
											$totalCreditAmount = 0;
											$contractedAmount = 0;
											$commissionRate = 0;
											
											$status = 'Inactive';
											$selectedSalesReps = '';
											$charged = 'N';
											
											$customer = Customer::model()->find(array(
												'condition' => 'id = :customer_id',
												'params' => array(
													':customer_id' => $model['customer_id'],
												),
											));
											
											if( $customer )
											{
												$salesReps = CustomerSalesRep::model()->findAll(array(
													'condition' => 'customer_id = :customer_id',
													'params' => array(
														':customer_id' => $customer->id,
													),
												));
												
												if( $salesReps )
												{
													foreach( $salesReps as $salesRep )
													{
														if( isset($salesRep->account) )
														{
															$selectedSalesReps .= $salesRep->account->getFullName().', ';
											
															$userMonthlyGoal = SalesAccountMonthlyGoal::model()->find(array(
																'condition' => 'account_id = :account_id',
																'params' => array(
																	':account_id' => $salesRep->sales_rep_account_id,
																),
															));
															
															if( $userMonthlyGoal )
															{
																$userCommissionRate = str_replace('%', '', $userMonthlyGoal->commission_rate);

																$commissionRate = ($userCommissionRate / 100);
															}
														}
													}
													
													$selectedSalesReps = rtrim($selectedSalesReps, ', ');
												}
												
												$customerSkill = CustomerSkill::model()->find(array(
													'condition' => 'customer_id = :customer_id AND status=1',
													'params' => array(
														':customer_id' => $customer->id,
													),
												));
												
												if( $customerSkill )
												{
													$contract = $customerSkill->contract;
									
													if( $contract )
													{
														if($contract->fulfillment_type != null )
														{
															if( $contract->fulfillment_type == Contract::TYPE_FULFILLMENT_GOAL_VOLUME )
															{
																if( !empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME]) )
																{
																	foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME] as $key => $subsidyLevel )
																	{
																		$customerSkillLevelArray = $customerSkill->getCustomerSkillLevelArray();
																		$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;

																		if( $customerSkillLevelArrayGroup != null )
																		{							
																			if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
																			{
																				$totalLeads += ( $subsidyLevel['goal'] * $customerSkillLevelArrayGroup->quantity );
																				
																				$contractedAmount += ( $subsidyLevel['amount'] * $customerSkillLevelArrayGroup->quantity );
																			}
																		}
																	}
																}
																
																$customerExtras = CustomerExtra::model()->findAll(array(
																	'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id AND status=1 AND year = :year AND month = :month',
																	'params' => array(
																		':customer_id' => $customerSkill->customer_id,
																		':contract_id' => $customerSkill->contract_id,
																		':skill_id' => $customerSkill->skill_id,
																		':year' => date('Y'),
																		':month' => date('m'),
																	),
																));
																
																if( $customerExtras )
																{
																	foreach( $customerExtras as $customerExtra )
																	{
																		$totalLeads += $customerExtra->quantity;
																	}
																}
															}
															else
															{
																if( !empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME]) )
																{
																	foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME] as $key => $subsidyLevel )
																	{
																		$customerSkillLevelArray = $customerSkill->getCustomerSkillLevelArray();
																		
																		$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;
																		
																		if( $customerSkillLevelArrayGroup != null )
																		{
																			if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
																			{
																				$totalLeads += ( $subsidyLevel['high'] * $customerSkillLevelArrayGroup->quantity );
																				
																				$contractedAmount += ( $subsidyLevel['amount'] * $customerSkillLevelArrayGroup->quantity );
																			}
																		}
																	}
																}
																
																$customerExtras = CustomerExtra::model()->findAll(array(
																	'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id AND status=1 AND year = :year AND month = :month',
																	'params' => array(
																		':customer_id' => $customerSkill->customer_id,
																		':contract_id' => $customerSkill->contract_id,
																		':skill_id' => $customerSkill->skill_id,
																		':year' => date('Y'),
																		':month' => date('m'),
																	),
																));
																
																if( $customerExtras )
																{
																	foreach( $customerExtras as $customerExtra )
																	{
																		$totalLeads += $customerExtra->quantity;
																	}
																}
															}
														}

														$status = 'Inactive';
														
														if( isset($customerSkill->contract) && isset($customerSkill->customer) && $customerSkill->customer->status == 1 && $customerSkill->customer->is_deleted == 0 && time() >= strtotime($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00' )
														{
															$status = 'Active';
														}
														
														if( $customerSkill->is_contract_hold == 1 )
														{
															if( !empty($customerSkill->is_contract_hold_start_date) && !empty($customerSkill->is_contract_hold_end_date) )
															{
																if( time() >= strtotime($customerSkill->is_contract_hold_start_date) && time() <= strtotime($customerSkill->is_contract_hold_end_date) )
																{
																	$status = 'On Hold';
																}
															}
														}
														
														if( $customerSkill->is_hold_for_billing == 1 )
														{
															$status = 'Decline Hold';
														}
														
														if( !empty($customerSkill->end_month) && date('Y', strtotime($customerSkill->end_month)) >= 2015 )
														{
															if( time() >= strtotime($customerSkill->end_month) )
															{
																$status = 'Cancelled';
															}
														}

														
														$billingPeriod = date('M Y', strtotime($dateFilterStart));
														
														$existingBillingForCurrentMonth = CustomerBilling::model()->find(array(
															'condition' => '
																customer_id = :customer_id 
																AND contract_id = :contract_id
																AND transaction_type = "Charge"
																AND billing_period = :billing_period
																AND ( anet_responseCode = 1 OR ( amount = 0 AND anet_responseCode IS NULL ))
															',
															'params' => array(
																':customer_id' => $customerSkill->customer_id,
																':contract_id' => $contract->id,
																':billing_period' => $billingPeriod
															),
															'order' => 'date_created DESC'
														));
														
														if( $existingBillingForCurrentMonth )
														{
															$charged = 'Y';
															
															$existingBillingForCurrentMonthVoidorRefund = CustomerBilling::model()->find(array(
																'condition' => '
																	customer_id = :customer_id 
																	AND contract_id = :contract_id
																	AND anet_responseCode = 1
																	AND reference_transaction_id = :reference_transaction_id
																	AND (
																		transaction_type = "Void"
																		OR transaction_type = "Refund"
																	)
																',
																'params' => array(
																	':customer_id' => $customerSkill->customer_id,
																	':contract_id' => $customerSkill->contract_id,
																	':reference_transaction_id' => $existingBillingForCurrentMonth->id,
																),
																'order' => 'date_created DESC'
															)); 
															
															if( $existingBillingForCurrentMonthVoidorRefund )
															{
																$charged  = 'N';
															}
														}
														
														
														$customerCredits = CustomerCredit::model()->findAll(array(
															'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND status=1',
															'params' => array(
																':customer_id' => $customerSkill->customer_id,
																':contract_id' => $customerSkill->contract_id,
															),
														));
														
														if( $customerCredits )
														{
															foreach( $customerCredits as $customerCredit )
															{
																$creditStartDate = date('Y-'.$customerCredit->start_month.'-1');
																
																if( $customerCredit->type == 2 ) //month range
																{
																	$creditEndDate = date('Y-'.$customerCredit->end_month.'-t');
																}
																else
																{
																	$creditEndDate = date('Y-'.$customerCredit->start_month.'-t');
																}
																
																
																$monthBillingPeriod = explode(' ',$billing_period);
																$monthPeriod = date('m', strtotime("$monthBillingPeriod[0] 1 ".date('Y')));
																$startDayOfBillingPeriod = date("Y-m-d",strtotime(date('Y')."-".$monthPeriod."-1"));
																$lastDayOfBillingPeriod = date("Y-m-t", strtotime($startDayOfBillingPeriod));
																
																if( (strtotime($startDayOfBillingPeriod) >= strtotime($creditStartDate)) && (strtotime($lastDayOfBillingPeriod) <= strtotime($creditEndDate)) )
																{
																	$totalCreditAmount += $customerCredit->amount;
																}
															}
														}
														
														$dateTime = new DateTime($model['date_created'], new DateTimeZone('America/Chicago'));
														$dateTime->setTimezone(new DateTimeZone('America/Denver'));	
										
										
														echo '<tr>';
															echo '<td>'.$ctr.'</td>';
															
															echo '<td>'.$dateTime->format('m/d/Y g:i A').'</td>';
											
																echo '<td>'.$selectedSalesReps.'</td>';
											
															echo '<td>'.$customer->getFullName().'</td>';
															
															
															echo '<td>';
															
																if( $customerSkill->start_month != '0000-00-00' && $customerSkill->start_month != '' )
																{
																	echo date('m/d/Y', strtotime($customerSkill->start_month));
																}
															
															echo '</td>';
															
															
															echo '<td>'.$status.'</td>';
															
															echo '<td>'.$customerSkill->skill->skill_name.'</td>';
															
															echo '<td class="center">'.$totalLeads.'</td>';
															
															echo '<td class="center">$'.$contractedAmount.'</td>';
															
															echo '<td class="center">$'.$totalCreditAmount.'</td>';
															
															echo '<td class="center">'.$charged.'</td>';
															
															echo '<td class="center">';
															
																if( count($salesReps) > 1 )
																{
																	echo 'Y';
																}
																else
																{
																	echo 'N';
																}
																
															echo '</td>';
															
															echo '<td class="center">';
															
																if( $commissionRate > 0 )
																{
																	echo '$'.number_format( ($commissionRate * $contractedAmount) / count($selectedSalesReps), 2);
																}
																else
																{
																	echo '$0.00';
																}
															
															echo '</td>';
															
														echo '</tr>';
													}
												}
											}
											
											$ctr++;
										}
									}
									
								echo '</table>';
							
							echo '</div>';
						
						echo '</div>';
					}
				}
					
				if( $page == 'timezones' )
				{
					$totalActiveCustomers = 0;
					$totalOnHoldCustomers = 0;
					$totalRemovedCustomers = 0;
					
					$totalActiveCallableLeads = 0;
					$totalonHoldCallableLeads = 0;
					$totalRemovedCallableLeads = 0;
					
					$totalActiveGoals = 0;
					$totalonHoldGoals = 0;
					$totalRemovedGoals = 0;
					
					$totalActiveGoalRemaining = 0;
					$totalonHoldGoalRemaining = 0;
					$totalRemovedGoalRemaining = 0;
					
					
					$resultArray = array();
					
					$timezones = array('EST', 'CST', 'MST', 'PST', 'AKST', 'HAST');
					
					foreach( $timezones as $timezone )
					{
						$resultArray[$timezone] = array(
							'customers' => array(
								'active'  => 0,
								'on_hold'  => 0,
								'removed'  => 0,
							),
							'callable_leads' => array(
								'active'  => 0,
								'on_hold'  => 0,
								'removed'  => 0,
							),
							'goals' => array(
								'active'  => 0,
								'on_hold'  => 0,
								'removed'  => 0,
							),
							'goal_remaining' => array(
								'active' => 0,
								'on_hold' => 0,
								'removed' => 0,
							),
						);
					}
					
					$customerQueues = CustomerQueueViewer::model()->findAll(array(
						'condition' => 'company IN ("State Farm", "Farmers", "Allstate", "American Family", "Independent Insurance")',
					));
					
					if( $customerQueues )
					{
						foreach( $customerQueues as $customerQueue )
						{
							$customerSkill = CustomerSkill::model()->find(array(
								'condition' => 'customer_id = :customer_id AND skill_id = :skill_id AND status=1',
								'params' => array(
									':customer_id' => $customerQueue->customer_id,
									':skill_id' => $customerQueue->skill_id,
								),
							));
							
							if( $customerSkill )
							{				
								$status = 'Inactive';
								
								if( isset($customerSkill->contract) && isset($customerSkill->customer) && $customerSkill->customer->status == 1 && $customerSkill->customer->is_deleted == 0 && time() >= strtotime($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00' )
								{
									$status = 'Active';
								}
								
								if( !$customerIsCallable )
								{
									if( $customerSkill->start_month == '0000-00-00' )
									{
										$status = 'Active';
									}
									
									if( $customerSkill->start_month != '0000-00-00' && strtotime($customerSkill->start_month) > time() )
									{
										$status = 'Active';
									}
								}
								
								if( $customerSkill->is_contract_hold == 1 )
								{
									if( !empty($customerSkill->is_contract_hold_start_date) && !empty($customerSkill->is_contract_hold_end_date) )
									{
										if( time() >= strtotime($customerSkill->is_contract_hold_start_date) && time() <= strtotime($customerSkill->is_contract_hold_end_date) )
										{
											$status = 'On Hold';
										}
									}
								}
								
								if( $customerSkill->is_hold_for_billing == 1 )
								{
									$status = 'Decline Hold';
								}
								
								if( !empty($customerSkill->end_month) && date('Y', strtotime($customerSkill->end_month)) >= 2015 )
								{
									if( time() >= strtotime($customerSkill->end_month) )
									{
										$status = 'Cancelled';
									}
								}
								
								if( $customerQueue )
								{
									if( !empty($customerQueue->removal_start_date) && !empty($customerQueue->removal_end_date) )
									{
										if( time() >= strtotime($customerQueue->removal_start_date) && time() <= strtotime($customerQueue->removal_end_date) )
										{
											$status = 'Removed';
										}
									}
								}
								
								$appointmentSetMTDSql = "
									SELECT count(distinct lch.lead_id) AS totalCount 
									FROM ud_lead_call_history lch 
									LEFT JOIN ud_lists ls ON ls.id = lch.list_id 
									LEFT JOIN ud_calendar_appointment ca ON ca.id = lch.calendar_appointment_id 
									WHERE ca.title IN ('INSERT APPOINTMENT', 'APPOINTMENT SET', 'CANCEL APPOINTMENT', 'RESCHEDULE APPOINTMENT') 
									AND lch.disposition = 'Appointment Set'
									AND lch.date_created >= '".date('Y-m-01 00:00:00')."' 
									AND ca.date_created <= '".date('Y-m-t 23:59:59')."'
									AND lch.customer_id = '".$customerQueue->customer_id."'
									AND ls.skill_id = '".$customerSkill->skill_id."' 
								";
							
								
								$command = Yii::app()->db->createCommand($appointmentSetMTDSql);
								$appointmentSetMTD = $command->queryRow();
								
								$noShowMTDSql = "
									SELECT count(distinct lch.lead_id) AS totalCount 
									FROM ud_lead_call_history lch 
									LEFT JOIN ud_lists ls ON ls.id = lch.list_id 
									LEFT JOIN ud_calendar_appointment ca ON ca.id = lch.calendar_appointment_id 
									WHERE ca.title IN ('NO SHOW RESCHEDULE')
									AND lch.disposition = 'Appointment Set'
									AND lch.date_created >= '".date('Y-m-01 00:00:00')."' 
									AND ca.date_created <= '".date('Y-m-t 23:59:59')."'
									AND lch.customer_id = '".$customerQueue->customer_id."'
									AND ls.skill_id = '".$customerSkill->skill_id."' 
								";
								
								
								$command = Yii::app()->db->createCommand($noShowMTDSql);
								$noShowMTD = $command->queryRow();
								
								$appointmentSetCount = $appointmentSetMTD['totalCount'] + $noShowMTD['totalCount'];
								
								if( $noShowMTD['totalCount'] > 3 )
								{
									$appointmentSetCount = $appointmentSetMTD['totalCount']-3;
								}
								else
								{
									$appointmentSetCount = $appointmentSetCount-$noShowMTD['totalCount'];
								}
								
								$remainingCallableCount = Lead::model()->count(array(
									'with' => array('list', 'list.skill'),
									'together' => true,
									'condition' => '
										list.customer_id = :customer_id AND list.status = 1 
										AND t.type=1 and t.status=1 AND t.number_of_dials < (skill.max_dials * 3) 
										AND (recertify_date != "0000-00-00" AND recertify_date IS NOT NULL 
										AND NOW() <= recertify_date)
										AND skill.id = :skill_id
									',
									'params' => array(
										':customer_id' => $customerQueue->customer_id,
										':skill_id' => $customerQueue->skill_id,
									),
								));
								
								$goalRemaining = $customerQueue->contracted_quantity;
								
								if( $status == 'Active' )
								{
									$resultArray[$customerQueue->customer->getTimeZone()]['customers']['active'] += 1;
									$resultArray[$customerQueue->customer->getTimeZone()]['callable_leads']['active'] += $remainingCallableCount;
									$resultArray[$customerQueue->customer->getTimeZone()]['goals']['active'] += $appointmentSetCount;
									
									if( $customerQueue->fulfillment_type == 'Goal' )
									{
										$goalRemaining = $goalRemaining - $appointmentSetCount;
										
										if( $goalRemaining < 0 )
										{
											$goalRemaining = 0;
										}
										
										$resultArray[$customerQueue->customer->getTimeZone()]['goal_remaining']['active'] += $goalRemaining;
									}
								}
								
								if( in_array($status, array("On Hold", "Decline Hold")) )
								{
									$resultArray[$customerQueue->customer->getTimeZone()]['customers']['on_hold'] += 1;
									$resultArray[$customerQueue->customer->getTimeZone()]['callable_leads']['on_hold'] += $remainingCallableCount;
									$resultArray[$customerQueue->customer->getTimeZone()]['goals']['on_hold'] += $appointmentSetCount;
									
									if( $customerQueue->fulfillment_type == 'Goal' )
									{
										$goalRemaining = $goalRemaining - $appointmentSetCount;
										
										if( $goalRemaining < 0 )
										{
											$goalRemaining = 0;
										}
										
										$resultArray[$customerQueue->customer->getTimeZone()]['goal_remaining']['on_hold'] += $goalRemaining;
									}	
								}	
								
								if( $status == "Removed" )
								{
									$resultArray[$customerQueue->customer->getTimeZone()]['customers']['removed'] += 1;
									$resultArray[$customerQueue->customer->getTimeZone()]['callable_leads']['removed'] += $remainingCallableCount;
									$resultArray[$customerQueue->customer->getTimeZone()]['goals']['removed'] += $appointmentSetCount;
									
									if( $customerQueue->fulfillment_type == 'Goal' )
									{
										$goalRemaining = $goalRemaining - $appointmentSetCount;
										
										if( $goalRemaining < 0 )
										{
											$goalRemaining = 0;
										}
										
										$resultArray[$customerQueue->customer->getTimeZone()]['goal_remaining']['removed'] += $goalRemaining;
									}
								}
							}
						}

						foreach( $resultArray as $timezoneValues)
						{
							$totalActiveCustomers += $timezoneValues['customers']['active'];
							$totalOnHoldCustomers += $timezoneValues['customers']['on_hold'];
							$totalRemovedCustomers += $timezoneValues['customers']['removed'];
							
							$totalActiveCallableLeads +=  $timezoneValues['callable_leads']['active'];
							$totalOnHoldCallableLeads +=  $timezoneValues['callable_leads']['on_hold'];
							$totalRemovedCallableLeads +=  $timezoneValues['callable_leads']['removed'];
							
							$totalActiveGoals +=  $timezoneValues['goals']['active'];
							$totalOnHoldGoals +=  $timezoneValues['goals']['on_hold'];
							$totalRemovedGoals +=  $timezoneValues['goals']['removed'];
							
							$totalActiveGoalRemaining +=  $timezoneValues['goal_remaining']['active'];
							$totalOnHoldGoalRemaining +=  $timezoneValues['goal_remaining']['on_hold'];
							$totalRemovedGoalRemaining +=  $timezoneValues['goal_remaining']['removed'];
						}
					}			
					
					echo '<table class="table table-bordered table-condensed table-striped table-hover">';
					
						echo '<tr>';
							echo '<th></th>';
							echo '<th class="center">Eastern</th>';
							echo '<th class="center">Central</th>';
							echo '<th class="center">Mountain</th>';
							echo '<th class="center">Pacific</th>';
							echo '<th class="center">Alaska</th>';
							echo '<th class="center">Hawaii</th>';
							echo '<th></th>';
						echo '<t/r>';
						
						echo '<tr>';
							echo '<th>Customers</th>';
							echo '<td class="center" colspan="7"></td>';
						echo '</tr>';
						
						echo '<tr>';
							echo '<td>Active</td>';
							echo '<td class="center">'.$resultArray['EST']['customers']['active'].'</td>';
							echo '<td class="center">'.$resultArray['CST']['customers']['active'].'</td>';
							echo '<td class="center">'.$resultArray['MST']['customers']['active'].'</td>';
							echo '<td class="center">'.$resultArray['PST']['customers']['active'].'</td>';
							echo '<td class="center">'.$resultArray['AKST']['customers']['active'].'</td>';
							echo '<td class="center">'.$resultArray['HAST']['customers']['active'].'</td>';
							echo '<td class="center">'.$totalActiveCustomers.'</td>';
						echo '</tr>';
						
						echo '<tr>';
							echo '<td>Hold/Decline</td>';
							echo '<td class="center">'.$resultArray['EST']['customers']['on_hold'].'</td>';
							echo '<td class="center">'.$resultArray['CST']['customers']['on_hold'].'</td>';
							echo '<td class="center">'.$resultArray['MST']['customers']['on_hold'].'</td>';
							echo '<td class="center">'.$resultArray['PST']['customers']['on_hold'].'</td>';
							echo '<td class="center">'.$resultArray['AKST']['customers']['on_hold'].'</td>';
							echo '<td class="center">'.$resultArray['HAST']['customers']['on_hold'].'</td>';
							echo '<td class="center">'.$totalOnHoldCustomers.'</td>';
						echo '</tr>';
						
						echo '<tr>';
							echo '<td>Removed</td>';
							echo '<td class="center">'.$resultArray['EST']['customers']['removed'].'</td>';
							echo '<td class="center">'.$resultArray['CST']['customers']['removed'].'</td>';
							echo '<td class="center">'.$resultArray['MST']['customers']['removed'].'</td>';
							echo '<td class="center">'.$resultArray['PST']['customers']['removed'].'</td>';
							echo '<td class="center">'.$resultArray['AKST']['customers']['removed'].'</td>';
							echo '<td class="center">'.$resultArray['HAST']['customers']['removed'].'</td>';
							echo '<td class="center">'.$totalRemovedCustomers.'</td>';
						echo '</tr>';
						
						echo '<tr>';
							echo '<td></td>';
							echo '<td class="center" style="font-weight:bold; text-decoration:underline;">'.($resultArray['EST']['customers']['active'] + $resultArray['EST']['customers']['on_hold'] + $resultArray['EST']['customers']['removed']).'</td>';
							echo '<td class="center" style="font-weight:bold; text-decoration:underline;">'.($resultArray['CST']['customers']['active'] + $resultArray['CST']['customers']['on_hold'] + $resultArray['CST']['customers']['removed']).'</td>';
							echo '<td class="center" style="font-weight:bold; text-decoration:underline;">'.($resultArray['MST']['customers']['active'] + $resultArray['MST']['customers']['on_hold'] + $resultArray['MST']['customers']['removed']).'</td>';
							echo '<td class="center" style="font-weight:bold; text-decoration:underline;">'.($resultArray['PST']['customers']['active'] + $resultArray['PST']['customers']['on_hold'] + $resultArray['PST']['customers']['removed']).'</td>';
							echo '<td class="center" style="font-weight:bold; text-decoration:underline;">'.($resultArray['AKST']['customers']['active'] + $resultArray['AKST']['customers']['on_hold'] + $resultArray['AKST']['customers']['removed']).'</td>';
							echo '<td class="center" style="font-weight:bold; text-decoration:underline;">'.($resultArray['HAST']['customers']['active'] + $resultArray['HAST']['customers']['on_hold'] + $resultArray['HAST']['customers']['removed']).'</td>';
							echo '<td class="center" style="font-weight:bold; text-decoration:underline;">'.($totalActiveCustomers + $totalOnHoldCustomers + $totalRemovedCustomers).'</td>';
						echo '</tr>';
						
						echo '<tr>';
							echo '<td colspan="8"></td>';
						echo '</tr>';
						
						//CALLABLE LEADS
						
						echo '<tr>';
							echo '<th>Callable Leads</th>';
							echo '<td class="center" colspan="7"></td>';
						echo '</tr>';
						
						echo '<tr>';
							echo '<td>Active</td>';
							echo '<td class="center">'.$resultArray['EST']['callable_leads']['active'].'</td>';
							echo '<td class="center">'.$resultArray['CST']['callable_leads']['active'].'</td>';
							echo '<td class="center">'.$resultArray['MST']['callable_leads']['active'].'</td>';
							echo '<td class="center">'.$resultArray['PST']['callable_leads']['active'].'</td>';
							echo '<td class="center">'.$resultArray['AKST']['callable_leads']['active'].'</td>';
							echo '<td class="center">'.$resultArray['HAST']['callable_leads']['active'].'</td>';
							echo '<td class="center">'.$totalActiveCallableLeads.'</td>';
						echo '</tr>';
						
						echo '<tr>';
							echo '<td>Hold/Decline</td>';
							echo '<td class="center">'.$resultArray['EST']['callable_leads']['on_hold'].'</td>';
							echo '<td class="center">'.$resultArray['CST']['callable_leads']['on_hold'].'</td>';
							echo '<td class="center">'.$resultArray['MST']['callable_leads']['on_hold'].'</td>';
							echo '<td class="center">'.$resultArray['PST']['callable_leads']['on_hold'].'</td>';
							echo '<td class="center">'.$resultArray['AKST']['callable_leads']['on_hold'].'</td>';
							echo '<td class="center">'.$resultArray['HAST']['callable_leads']['on_hold'].'</td>';
							echo '<td class="center">'.$totalOnHoldCallableLeads.'</td>';
						echo '</tr>';
						
						echo '<tr>';
							echo '<td>Removed</td>';
							echo '<td class="center">'.$resultArray['EST']['callable_leads']['removed'].'</td>';
							echo '<td class="center">'.$resultArray['CST']['callable_leads']['removed'].'</td>';
							echo '<td class="center">'.$resultArray['MST']['callable_leads']['removed'].'</td>';
							echo '<td class="center">'.$resultArray['PST']['callable_leads']['removed'].'</td>';
							echo '<td class="center">'.$resultArray['AKST']['callable_leads']['removed'].'</td>';
							echo '<td class="center">'.$resultArray['HAST']['callable_leads']['removed'].'</td>';
							echo '<td class="center">'.$totalRemovedCallableLeads.'</td>';
						echo '</tr>';
						
						echo '<tr>';
							echo '<td></td>';
							echo '<td class="center" style="font-weight:bold; text-decoration:underline;">'.($resultArray['EST']['callable_leads']['active'] + $resultArray['EST']['callable_leads']['on_hold'] + $resultArray['EST']['callable_leads']['removed']).'</td>';
							echo '<td class="center" style="font-weight:bold; text-decoration:underline;">'.($resultArray['CST']['callable_leads']['active'] + $resultArray['CST']['callable_leads']['on_hold'] + $resultArray['CST']['callable_leads']['removed']).'</td>';
							echo '<td class="center" style="font-weight:bold; text-decoration:underline;">'.($resultArray['MST']['callable_leads']['active'] + $resultArray['MST']['callable_leads']['on_hold'] + $resultArray['MST']['callable_leads']['removed']).'</td>';
							echo '<td class="center" style="font-weight:bold; text-decoration:underline;">'.($resultArray['PST']['callable_leads']['active'] + $resultArray['PST']['callable_leads']['on_hold'] + $resultArray['PST']['callable_leads']['removed']).'</td>';
							echo '<td class="center" style="font-weight:bold; text-decoration:underline;">'.($resultArray['AKST']['callable_leads']['active'] + $resultArray['AKST']['callable_leads']['on_hold'] + $resultArray['AKST']['callable_leads']['removed']).'</td>';
							echo '<td class="center" style="font-weight:bold; text-decoration:underline;">'.($resultArray['HAST']['callable_leads']['active'] + $resultArray['HAST']['callable_leads']['on_hold'] + $resultArray['HAST']['callable_leads']['removed']).'</td>';
							echo '<td class="center" style="font-weight:bold; text-decoration:underline;">'.($totalActiveCallableLeads + $totalOnHoldCallableLeads + $totalRemovedCallableLeads).'</td>';
						echo '</tr>';
						
						echo '<tr>';
							echo '<td colspan="8"></td>';
						echo '</tr>';
						
						//GOALS
						
						echo '<tr>';
							echo '<th>Goal</th>';
							echo '<td class="center" colspan="7"></td>';
						echo '</tr>';
						
						echo '<tr>';
							echo '<td>Active</td>';
							echo '<td class="center">'.$resultArray['EST']['goals']['active'].'</td>';
							echo '<td class="center">'.$resultArray['CST']['goals']['active'].'</td>';
							echo '<td class="center">'.$resultArray['MST']['goals']['active'].'</td>';
							echo '<td class="center">'.$resultArray['PST']['goals']['active'].'</td>';
							echo '<td class="center">'.$resultArray['AKST']['goals']['active'].'</td>';
							echo '<td class="center">'.$resultArray['HAST']['goals']['active'].'</td>';
							echo '<td class="center">'.$totalActiveGoals.'</td>';
						echo '</tr>';
						
						echo '<tr>';
							echo '<td>Hold/Decline</td>';
							echo '<td class="center">'.$resultArray['EST']['goals']['on_hold'].'</td>';
							echo '<td class="center">'.$resultArray['CST']['goals']['on_hold'].'</td>';
							echo '<td class="center">'.$resultArray['MST']['goals']['on_hold'].'</td>';
							echo '<td class="center">'.$resultArray['PST']['goals']['on_hold'].'</td>';
							echo '<td class="center">'.$resultArray['AKST']['goals']['on_hold'].'</td>';
							echo '<td class="center">'.$resultArray['HAST']['goals']['on_hold'].'</td>';
							echo '<td class="center">'.$totalOnHoldGoals.'</td>';
						echo '</tr>';
						
						echo '<tr>';
							echo '<td>Removed</td>';
							echo '<td class="center">'.$resultArray['EST']['goals']['removed'].'</td>';
							echo '<td class="center">'.$resultArray['CST']['goals']['removed'].'</td>';
							echo '<td class="center">'.$resultArray['MST']['goals']['removed'].'</td>';
							echo '<td class="center">'.$resultArray['PST']['goals']['removed'].'</td>';
							echo '<td class="center">'.$resultArray['AKST']['goals']['removed'].'</td>';
							echo '<td class="center">'.$resultArray['HAST']['goals']['removed'].'</td>';
							echo '<td class="center">'.$totalRemovedGoals.'</td>';
						echo '</tr>';
						
						echo '<tr>';
							echo '<td></td>';
							echo '<td class="center" style="font-weight:bold; text-decoration:underline;">'.($resultArray['EST']['goals']['active'] + $resultArray['EST']['goals']['on_hold'] + $resultArray['EST']['goals']['removed']).'</td>';
							echo '<td class="center" style="font-weight:bold; text-decoration:underline;">'.($resultArray['CST']['goals']['active'] + $resultArray['CST']['goals']['on_hold'] + $resultArray['CST']['goals']['removed']).'</td>';
							echo '<td class="center" style="font-weight:bold; text-decoration:underline;">'.($resultArray['MST']['goals']['active'] + $resultArray['MST']['goals']['on_hold'] + $resultArray['MST']['goals']['removed']).'</td>';
							echo '<td class="center" style="font-weight:bold; text-decoration:underline;">'.($resultArray['PST']['goals']['active'] + $resultArray['PST']['goals']['on_hold'] + $resultArray['PST']['goals']['removed']).'</td>';
							echo '<td class="center" style="font-weight:bold; text-decoration:underline;">'.($resultArray['AKST']['goals']['active'] + $resultArray['AKST']['goals']['on_hold'] + $resultArray['AKST']['goals']['removed']).'</td>';
							echo '<td class="center" style="font-weight:bold; text-decoration:underline;">'.($resultArray['HAST']['goals']['active'] + $resultArray['HAST']['goals']['on_hold'] + $resultArray['HAST']['goals']['removed']).'</td>';
							echo '<td class="center" style="font-weight:bold; text-decoration:underline;">'.($totalActiveGoals + $totalOnHoldGoals + $totalRemovedGoals).'</td>';
						echo '</tr>';
						
						echo '<tr>';
							echo '<td colspan="8"></td>';
						echo '</tr>';
						
						//GOAL REMAINING
						
						echo '<tr>';
							echo '<th>Goal Remaining</th>';
							echo '<td class="center" colspan="7"></td>';
						echo '</tr>';
						
						echo '<tr>';
							echo '<td>Active</td>';
							echo '<td class="center">'.$resultArray['EST']['goal_remaining']['active'].'</td>';
							echo '<td class="center">'.$resultArray['CST']['goal_remaining']['active'].'</td>';
							echo '<td class="center">'.$resultArray['MST']['goal_remaining']['active'].'</td>';
							echo '<td class="center">'.$resultArray['PST']['goal_remaining']['active'].'</td>';
							echo '<td class="center">'.$resultArray['AKST']['goal_remaining']['active'].'</td>';
							echo '<td class="center">'.$resultArray['HAST']['goal_remaining']['active'].'</td>';
							echo '<td class="center">'.$totalActiveGoalRemaining.'</td>';
						echo '</tr>';
						
						echo '<tr>';
							echo '<td>Hold/Decline</td>';
							echo '<td class="center">'.$resultArray['EST']['goal_remaining']['on_hold'].'</td>';
							echo '<td class="center">'.$resultArray['CST']['goal_remaining']['on_hold'].'</td>';
							echo '<td class="center">'.$resultArray['MST']['goal_remaining']['on_hold'].'</td>';
							echo '<td class="center">'.$resultArray['PST']['goal_remaining']['on_hold'].'</td>';
							echo '<td class="center">'.$resultArray['AKST']['goal_remaining']['on_hold'].'</td>';
							echo '<td class="center">'.$resultArray['HAST']['goal_remaining']['on_hold'].'</td>';
							echo '<td class="center">'.$totalOnHoldGoalRemaining.'</td>';
						echo '</tr>';
						
						echo '<tr>';
							echo '<td>Removed</td>';
							echo '<td class="center">'.$resultArray['EST']['goal_remaining']['removed'].'</td>';
							echo '<td class="center">'.$resultArray['CST']['goal_remaining']['removed'].'</td>';
							echo '<td class="center">'.$resultArray['MST']['goal_remaining']['removed'].'</td>';
							echo '<td class="center">'.$resultArray['PST']['goal_remaining']['removed'].'</td>';
							echo '<td class="center">'.$resultArray['AKST']['goal_remaining']['removed'].'</td>';
							echo '<td class="center">'.$resultArray['HAST']['goal_remaining']['removed'].'</td>';
							echo '<td class="center">'.$totalRemovedGoalRemaining.'</td>';
						echo '</tr>';
						
						echo '<tr>';
							echo '<td></td>';
							echo '<td class="center" style="font-weight:bold; text-decoration:underline;">'.($resultArray['EST']['goal_remaining']['active'] + $resultArray['EST']['goal_remaining']['on_hold'] + $resultArray['EST']['goal_remaining']['removed']).'</td>';
							echo '<td class="center" style="font-weight:bold; text-decoration:underline;">'.($resultArray['CST']['goal_remaining']['active'] + $resultArray['CST']['goal_remaining']['on_hold'] + $resultArray['CST']['goal_remaining']['removed']).'</td>';
							echo '<td class="center" style="font-weight:bold; text-decoration:underline;">'.($resultArray['MST']['goal_remaining']['active'] + $resultArray['MST']['goal_remaining']['on_hold'] + $resultArray['MST']['goal_remaining']['removed']).'</td>';
							echo '<td class="center" style="font-weight:bold; text-decoration:underline;">'.($resultArray['PST']['goal_remaining']['active'] + $resultArray['PST']['goal_remaining']['on_hold'] + $resultArray['PST']['goal_remaining']['removed']).'</td>';
							echo '<td class="center" style="font-weight:bold; text-decoration:underline;">'.($resultArray['AKST']['goal_remaining']['active'] + $resultArray['AKST']['goal_remaining']['on_hold'] + $resultArray['AKST']['goal_remaining']['removed']).'</td>';
							echo '<td class="center" style="font-weight:bold; text-decoration:underline;">'.($resultArray['HAST']['goal_remaining']['active'] + $resultArray['HAST']['goal_remaining']['on_hold'] + $resultArray['HAST']['goal_remaining']['removed']).'</td>';
							echo '<td class="center" style="font-weight:bold; text-decoration:underline;">'.($totalActiveGoalRemaining + $totalOnHoldGoalRemaining + $totalRemovedGoalRemaining).'</td>';
						echo '</tr>';
					
					echo '</table>';
				}
		
				
				if( $page == 'timeOff' )
				{
					$models = AccountPtoRequest::model()->findAll(array(
						'condition' => '
							STR_TO_DATE(request_date, "%m/%d/%Y") > NOW()
						',
						'order' => 'STR_TO_DATE(request_date, "%m/%d/%Y") ASC'
					));
					
					echo '<div class="row">';
					
						echo '<div class="col-sm-12">';
						
							echo '<table class="table table-striped table-bordered table-condensed table-hover">';
								
								echo '<tr>';
									echo '<th>#</th>';
									echo '<th>Employee Name</th>';
									echo '<th>Request Date/Time</th>';
									echo '<th class="center">Hours</th>';
									echo '<th>Status</th>';

								echo '</tr>';
					
								$ptoArray = array();
					
								if( $models )
								{
									$ctr = 1;
									
									foreach( $models as $model )
									{
										$startDate = strtotime($model->request_date.' '.$model->start_time);
										$endDate = strtotime($model->request_date_end.' '.$model->end_time);

										$totalScheduledHours = 0;
										
										while( $startDate <= $endDate ) 
										{
											$schedules = AccountLoginSchedule::model()->findAll(array(
												'condition' => 'account_id = :account_id AND day_name = :day_name AND type=1',
												'params' => array(
													':account_id' => $model->account_id,
													':day_name' => date('l', $startDate),
												),
												'order' => 'date_created ASC',
											));
											
											
											
											if( $schedules )
											{
												foreach( $schedules as $schedule )
												{
													$startTime = date('g:i A', strtotime($schedule->start_time));
													$endTime = date('g:i A', strtotime($schedule->end_time));

													if( strtotime($model->request_date.' '.$schedule->start_time) >= strtotime($model->request_date.' '.$schedule->start_time) && strtotime($model->request_date.' '.$schedule->end_time) <= strtotime($model->request_date.' '.$schedule->end_time) )
													{
														$totalScheduledHours += round((strtotime($schedule->end_time) - strtotime($schedule->start_time))/3600, 1);
													}
												}
											}
											
											$startDate = strtotime('+1 day', $startDate);
										}
										
										if($model->status == 1)
										{
											$status = 'Approved';
										}
										elseif($model->status == 2)
										{
											$status = 'For Approval';
										}
										else
										{
											$status = 'Denied';
										}
										
										echo '<tr>';
											echo '<td>'.$ctr.'</td>';
											
											echo '<td>'.$model->account->getFullName().'</td>';
											
											echo '<td>';
												echo date('m/d/Y g:i A', strtotime($model->request_date.' '.$model->start_time)); 
												echo ' - ';
												echo date('m/d/Y g:i A', strtotime($model->request_date_end.' '.$model->end_time)); 
											echo '</td>';
											
											echo '<td class="center">'.$totalScheduledHours.'</td>';
											
											echo '<td>'.$status.'</td>';
										echo '</tr>';
										
										$ctr++;
									}
								}
								
								
								
							echo '</table>';
						echo '</div>';
					echo '</div>';
				}
				
				if( $page == 'news' )
				{
					$models = NewsAccountSettings::model()->findAll(array(
						'condition' => 'is_marked_read=1',
					));
					
					echo '<div class="row">';
					
						echo '<div class="col-sm-12">';
						
							echo '<table class="table table-striped table-bordered table-condensed table-hover">';
								
								echo '<tr>';
									echo '<th>#</th>';
									echo '<th>User name</th>';
									echo '<th>Article name</th>';
									echo '<th>Date/Time</th>';

								echo '</tr>';
								
								if( $models )
								{
									$ctr = 1;
									
									foreach( $models as $model )
									{
										$date = new DateTime($model->date_created, new DateTimeZone('America/Chicago'));
										$date->setTimezone(new DateTimeZone('America/Denver'));
										
										echo '<tr>'; 
											echo '<td>'.$ctr.'</td>';
											echo '<td>'.$model->account->getFullName().'</td>';
											echo '<td>'.$model->news->title.'</td>';
											echo '<td>'.$date->format('m/d/Y g:i A').'</td>';
										echo '</tr>';
										
										$ctr++;
									}
								}
								
							echo '</table>';
							
						echo '</div>';
					echo '</div>';
				}
				
				if( $page == 'customerContactInfo' )
				{
					// AND (
						// end_month IS NULL
						// OR end_month = ""
						// OR end_month = "0000-00-00" 
						// OR DATE(end_month) > NOW()
					// )
					
					$customerSkillIds = array();
					
					$customerSkills = CustomerSkill::model()->findAll(array(
						'with' => 'customer',
						'select' => 't.id, t.customer_id',
						'condition' => '
							customer.is_deleted = 0
							AND customer.company_id NOT IN("17", "18", "23")
							AND t.status = 1
						',
						'order' => 't.date_created DESC'
					));
					
					if( $customerSkills )
					{
						foreach( $customerSkills as $customerSkill )
						{
							if( !array_key_exists($customerSkill->customer_id, $customerSkillIds) )
							{
								$customerSkillIds[$customerSkill->customer_id] = $customerSkill->id;
							}
						}
					} 
					
					$customerSkills = CustomerSkill::model()->findAll(array(
						'with' => 'customer',
						'condition' => 't.id IN('.implode(', ', $customerSkillIds).')',
						'order' => 'customer.lastname ASC',
						// 'limit' => 500,
					));
					
					if( $customerSkills )
					{
						echo '<div class="row">';
					
							echo '<div class="col-sm-12">';
							
								echo '<table class="table table-striped table-bordered table-condensed table-hover">';
									
									echo '<tr>';
										echo '<th>#</th>';
										echo '<th>Agent ID</th>';
										echo '<th>Name</th>';
										echo '<th>Status</th>';
										echo '<th>Company</th>';
										echo '<th>Phone Number</th>';
										echo '<th>Email Address</th>';
										echo '<th>Address</th>';
										echo '<th>City</th>';
										echo '<th>State</th>';
										echo '<th>Zip</th>';
										echo '<th>Skill</th>';
										echo '<th>Contract</th>';
										echo '<th>Quantity</th>';
										echo '<th>Start Date</th>';
										echo '<th>End Date</th>';
										echo '<th>On Hold</th>';
										echo '<th>Off Hold</th>';

									echo '</tr>';
									
									if( $customerSkills )
									{
										$ctr = 1;
										
										foreach( $customerSkills as $customerSkill )
										{
											$skill = '';
											$contractName = '';
											$startDate = '';
											$endDate = '';
											$holdStartDate = '';
											$holdEndDate = '';
											$quantity = 0;
											
											if( isset($customerSkill->contract) )
											{
												$contract = $customerSkill->contract;

												if( isset($contract) && $contract->fulfillment_type != null )
												{
													if( $contract->fulfillment_type == Contract::TYPE_FULFILLMENT_GOAL_VOLUME )
													{
														if( !empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME]) )
														{
															foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME] as $key => $subsidyLevel )
															{
																$customerSkillLevelArray = $customerSkill->getCustomerSkillLevelArray();
																$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;

																if( $customerSkillLevelArrayGroup != null )
																{							
																	if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
																	{
																		$quantity += ( $subsidyLevel['goal'] * $customerSkillLevelArrayGroup->quantity );
																	}
																}
															}
														}
													}
													else
													{
														if( !empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME]) )
														{
															foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME] as $key => $subsidyLevel )
															{
																$customerSkillLevelArray = $customerSkill->getCustomerSkillLevelArray();
																
																$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;
																
																if( $customerSkillLevelArrayGroup != null )
																{
																	if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
																	{
																		$quantity += ( $subsidyLevel['high'] * $customerSkillLevelArrayGroup->quantity );
																	}
																}
															}
														}
													}
												}
												
												$status = 'Inactive';
			
												if( time() >= strtotime($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00' )
												{
													$skill = $customerSkill->skill->skill_name;
													$contractName = $contract->contract_name;
													
													$status = 'Active';
												
													if( !$customerIsCallable )
													{
														if( $customerSkill->start_month == '0000-00-00' )
														{
															$status = 'Blank Start Date';
														}
														
														if( $customerSkill->start_month != '0000-00-00' && strtotime($customerSkill->start_month) > time() )
														{
															$status = 'Future Start Date';
														}
													}
													
													if( $customerSkill->is_contract_hold == 1 )
													{
														if( !empty($customerSkill->is_contract_hold_start_date) && !empty($customerSkill->is_contract_hold_end_date) )
														{
															if( time() >= strtotime($customerSkill->is_contract_hold_start_date) && time() <= strtotime($customerSkill->is_contract_hold_end_date) )
															{
																$status = 'On Hold';
															}
														}
													}
													
													if( $customerSkill->is_hold_for_billing == 1 )
													{
														$status = 'Decline Hold';
													}
													
													if( !empty($customerSkill->end_month) && date('Y', strtotime($customerSkill->end_month)) >= 2015 )
													{
														if( time() >= strtotime($customerSkill->end_month) )
														{
															$status = 'Cancelled';
														}
													}
													
													if( $status == 'On Hold' )
													{
														$holdStartDate .= date('m/d/Y', strtotime($customerSkill->is_contract_hold_start_date));
														$holdEndDate .= date('m/d/Y', strtotime($customerSkill->is_contract_hold_end_date));
													}
												}
											}
											
											$state = !empty($customerSkill->customer->state) ? State::model()->findByPk($customerSkill->customer->state)->name : '';
											
											if( !empty($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00' )
											{
												$startDate .= date('m/d/Y', strtotime($customerSkill->start_month));
											}
											
											if( !empty($customerSkill->end_month) && $customerSkill->end_month != '0000-00-00' )
											{
												$endDate = date('m/d/Y', strtotime($customerSkill->end_month));
											}
											
											if( in_array($customerSkill->skill_id, array(11,12)) )
											{
												$quantity = 0;
												$skill = '';
												$contractName = '';
												$status = 'Inactive';
												$startDate = '';
												$endDate = '';
												$holdStartDate = '';
												$holdEndDate = '';
											}
											
											echo '<tr>';
												echo '<td>'.$ctr.'</td>';
												echo '<td>'.$customerSkill->customer->custom_customer_id.'</td>';
												echo '<td>'.CHtml::link($customerSkill->customer->firstname.', '.$customerSkill->customer->lastname, array('/customer/insight/index', 'customer_id'=>$customerSkill->customer_id), array('target'=>'_blank')).'</td>';
												echo '<td>'.$status.'</td>';
												echo '<td>'.$customerSkill->customer->company->company_name.'</td>';
												echo '<td>'.$customerSkill->customer->phone.'</td>';
												echo '<td>'.$customerSkill->customer->email_address.'</td>';
												echo '<td>'.$customerSkill->customer->address1.'</td>';
												echo '<td>'.$customerSkill->customer->city.'</td>';
												echo '<td>'.$state.'</td>';
												echo '<td>'.$customerSkill->customer->email_address.'</td>';
												echo '<td>'.$skill.'</td>';
												echo '<td>'.$contractName.'</td>';
												echo '<td>'.$quantity.'</td>';
												echo '<td>'.$startDate.'</td>';
												echo '<td>'.$endDate.'</td>';
												echo '<td>'.$holdStartDate.'</td>';
												echo '<td>'.$holdEndDate.'</td>';
											echo '</tr>';
											
											$ctr++;
										}
									}
									
								echo '</table>';
								
							echo '</div>';
						
						echo '</div>';
					}
				}
		?>
		</div>	
	</div>	

</div>