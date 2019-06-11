<style>
	.page-content{
		background-color:#000000;
		color:#FFFFFF;
		font-size:32px;
	}
	.show-grid {
	  margin-bottom: 15px;
	}
	.show-grid [class^="col-"] {
	  padding-top: 10px;
	  padding-bottom: 10px;
	  /* border: 1px solid #000000; */
	}
	
	.show-grid .daily-pace-stats [class^="col-"]{
	  padding-top: 1px;
	  padding-bottom: 1px;
	}
	
	.border-1px{
		border: 2px solid #000000;
	}
	.show-grid .left-container{
		padding-top: 0px;
		padding-bottom: 0px;
		border: 2px solid #000000;
		min-height:1500px;
	}
	
	.show-grid .right-container{
		padding-top: 0px;
		padding-bottom: 0px;
		border: 2px solid #000000;
	}
	
	.show-grid .footer-container{
		padding-top: 0px;
		padding-bottom: 0px;
		border: 2px solid #000000;
		background-color:#000000;
		color:#FFFFFF;
		height:160px;
		line-height:160px;
		vertical-align:middle;
	}
	
	
	.number{font-size:42px; font-weight: bolder;}
	.mini-number{font-size:21px; font-weight: bolder;}
	.sblue{color:#438eb9;}
	.sred{color:#ff0000;}
	.sblack{color:#000000;}
	.todays-sales{font-size:140px;}
	#footer{
		position:fixed;
		   left:0px;
		   bottom:0px;
		   height:160px;
		   width:100%;
	}
	
	
	
	.number-label{
		font-size:18px;
		line-height:50px;
		position: absolute;
		right: -138px;
		width: 150px;
	}
	
	.marquee-logo{
		margin: 0px 50px 0px 50px;
	}
	
	.fixed-label{
		position:relative;
	}
	
	.siren-img{
		position: fixed;
		left: 40%;
		z-index: 1;
		top: 20%;
	}

	.colRed{color:#FF0000;}
	.colYellow{color:#ffff00;}
	.colViolet{color:#d6487e;}
	.colOrange{color:#ffa500;}
	

	.scoreboard-big{
		font-size:160px;
		font-weight:bolder;
	}
	
	.table>tbody>tr>td{
		border-top:none;
	}
</style>

<?php Yii::app()->clientScript->registerScript('backgroundJs','
function notifySales()
	{
		$(".siren-img").show();
		playAudio();
		
		var refreshIntervalId = setInterval(playAudio, 2500);
		
		setTimeout(function() {
			$(".siren-img").hide();
			clearInterval(refreshIntervalId);
		}, 10000);
		
		setTimeout(function() {
			$("#footer").hide();
		}, 30000);
	}
	
	function playAudio()
	{
		$("#audio-siren")[0].play();
	}
',CClientScript::POS_END); ?>

<?php 
	Yii::app()->clientScript->registerScript('salesAjaxChecker','
	
	var initialSalesCount = '.$salesData['todayEnrollCount'].';
	
	setInterval(ajaxChecker, 10000);
	
	function ajaxChecker(){
		$.ajax({
			method: "GET",
			url: "'.Yii::app()->createUrl('/sales/checkSalesCount').'",
			dataType: "json",
		}).done(function( data ) {
			if(data.count > initialSalesCount)
			{
				window.location.href = "'.Yii::app()->createUrl('/sales/view').'?alert=1";
			}
			
	  });
	}
	',CClientScript::POS_END); 
	
?>

<?php 
	Yii::app()->clientScript->registerScript( uniqid(),'
	
	$(document).ready( function(){
		
		window.setInterval(function () {
			var d = new Date();

			if( d.getHours() == 00 && d.getMinutes() >= 30 && d.getMinutes() <= 32  ) {
				location.reload(); 
			}
			
		}, 60000);
		
	});
	
	',CClientScript::POS_END); 
	
?>

<?php 
function parseCurrency($value) {
    if ( intval($value) == $value ) {
        $return = number_format($value, 0, ".", ",");
    }
    else {
        $return = number_format($value, 2, ".", ",");
        /*
        If you don't want to remove trailing zeros from decimals,
        eg. 19.90 to become: 19.9, remove the next line
        */
        $return = rtrim($return, 0);
    }

    return $return;
}
?>
<?php 
	if(isset($_GET['alert']) && $_GET['alert'] == "1"){
		Yii::app()->clientScript->registerScript('notifyNewSalesAlert','
			notifySales();
		',CClientScript::POS_END); 
	}
?>

<div class="siren-img" style="display:none;">
	<!--<div style="font-size:50px;position:relative;z-index:1;color:#FF0000; top: 160px;left: 180px;">445.00</div>-->
	<?php echo CHtml::image(Yii::app()->baseUrl.'/images/sales/siren.png','',array('class'=>'','style'=>'height:550px;')); ?>
	<!--<div style="font-size:50px;position:relative;z-index:1;color:#FFFFFF; bottom: 106px;left: 100px;">Ashley Paxton</div>-->
</div>

<audio style="display:none;" id="audio-siren" controls>
  <source src="<?php echo Yii::app()->baseUrl.'/images/sales/Police Siren-SoundBible.com-878640712.wav'; ?>" type="audio/wav">
  Your browser does not support the audio tag.
</audio>

<div class="sales-container">
	<div class="row show-grid">
		<div class="col-sm-6 left-container"><!-- LEFT COLUMN-->
		
			<div class="row">
				<div class="col-sm-12">
					
					<div class="row">
						<div class="col-sm-8">
							<div class="table-responsive">
							  <table class="table scoreboard-big">
								<tr>
									<td class="col1st text-right colOrange"><?php echo $salesData['currentEnrollCount']; ?></td>
									<td class="text-center">|</td>
									<td class="text-center"><?php echo $salesData['currentEnrollCountMonthlyGoal']; ?></td>
								</tr>
								
							  </table>
							</div>
						</div>
					</div>
					
					<div class="row">
						<div class="col-sm-12">
							<div class="text-right">
								<?php echo date('m/d/Y'); ?>
							</div>
						</div>
					</div>
					
					<div class="row">
						<div class="col-md-6">
							<div class="row">
								<div class="col-sm-12 text-center border-1px left-header" style="background-color:#438eb9;color:#ffffff;">
									<strong>Team Goal</strong>
								</div>
								
								<div class="col-sm-12">
									<div class="table-responsive">
									  <table class="table">
										<tr>
											<td class="col1st text-right"><?php echo date('F'); ?> Goal:</td>
											<td class="text-center"><?php echo $salesData['currentEnrollCountMonthlyGoal']; ?></td>
											<td class="text-center">$<?php echo $salesData['currentEnrollValueMonthlyGoal']; ?></td>
										</tr>
										
										<tr>
											<td class="col1st text-right colYellow">Sales To Date:</td>
											<td class="text-center colYellow"><?php echo $salesData['currentEnrollCount']; ?></td>
											<td class="text-center colYellow">$<?php echo $salesData['currentEnrollValue']; ?></td>
										</tr>
										
										<tr>
											<td class="col1st text-right colRed">Remaining:</td>
											<td class="text-center colRed"><?php echo $salesData['currentEnrollCountRemaining']; ?></td>
											<td class="text-center colRed">$<?php echo $salesData['currentEnrollValueRemaining']; ?></td>
										</tr>
										
										<tr>
											<td class="col1st text-right">Pacing To:</td>
											<td class="text-center">
												<?php 
													echo round( ($salesData['currentEnrollCount'] / $salesData['daysWorked']) * $salesData['remainingWorkingDay']) + $salesData['currentEnrollCount']; 
												?>
											</td>
											<td class="text-center">$<?php echo $salesData['pacingToAmount']; ?></td>
										</tr>
										
									  </table>
									</div>
								</div>
							</div>
							
							<div class="row">
								<div class="col-sm-12 text-center border-1px left-header" style="background-color:#438eb9;color:#ffffff;">
									<strong>Monthly Stats</strong>
								</div>
								
								<div class="col-sm-12">
									<div class="table-responsive">
									  <table class="table">
										<?php 
											foreach($salesRepData as $key => $salesRepIndividualGoal)
											{
												$explodedFullName = explode(' ', $salesRepIndividualGoal['fullname']);
												$firstName = $explodedFullName[0];
												$lastName = $explodedFullName[1];

												echo '<tr>';
													echo '<td class="col1st text-right">'.$firstName.'</td>';
													echo '<td class="text-center">'.$salesRepIndividualGoal['sales_month_to_date'].'</td>';
													echo '<td class="text-center">$'.$salesRepIndividualGoal['monthToDate'].'</td>';
												echo '</tr>';
											}
										?>
										
									  </table>
									</div>
								</div>
							</div>
							
							<div class="row">
								<div class="col-sm-12 text-center border-1px left-header" style="background-color:#438eb9;color:#ffffff;">
									<strong>Daily Pace</strong>
								</div>
								
								<div class="col-sm-12">
									<div class="table-responsive">
									  <table class="table">
										<tr>
											<td class="col1st text-right">Month to Date:</td>
											<td class="text-center"><?php echo round($salesData['currentEnrollCount'] / $salesData['daysWorked'], 2); ?>
											</td>
											<td class="text-center">$<?php echo $salesData['monthToDateAmount']; ?></td>
										</tr>
										
										<tr>
											<td class="col1st text-right">To Achieve Goal:</td>
											<td class="text-center"><?php echo $salesData['dailyPaceNew']; ?></td>
											<td class="text-center">$<?php echo $salesData['toAchieveGoalAmount']; ?></td>
										</tr>
										
									  </table>
									</div>
								</div>
							</div>
						</div>
						
						<div class="col-md-offset-1 col-md-5">
							<div class="row">
								<div class="col-sm-12 text-center border-1px left-header" style="background-color:#438eb9;color:#ffffff;">
									<strong>Individual Goal</strong>
								</div>
								
								<div class="col-sm-12">
									<div class="table-responsive">
									  <table class="table">
										<?php 
											foreach($salesRepData as $key => $salesRepIndividualGoal)
											{
												$explodedFullName = explode(' ', $salesRepIndividualGoal['fullname']);
												$firstName = $explodedFullName[0];
												$lastName = $explodedFullName[1];

												echo '<tr>';
													echo '<td class="col1st text-right">'.$firstName.'</td>';
													echo '<td class="text-center">'.$salesRepIndividualGoal['monthly_goal'].'</td>';
													echo '<td class="text-center">$'.$salesRepIndividualGoal['sales_revenue'].'</td>';
												echo '</tr>';
											}
										?>										
									  </table>
									</div>
								</div>
								
							</div>
							
							<div class="row">
								<div class="col-sm-12 text-center border-1px left-header" style="background-color:#438eb9;color:#ffffff;">
									<strong>Today's Stats</strong>
								</div>
								
								<div class="col-sm-12">
									<div class="table-responsive">
									  <table class="table">
										
										<?php 
											foreach($salesRepData as $key => $salesRepTodayStats)
											{
												$explodedFullName = explode(' ', $salesRepTodayStats['fullname']);
												$firstName = $explodedFullName[0];
												$lastName = $explodedFullName[1];
												
												echo '<tr>';
													echo '<td class="col1st text-right">'.$firstName.'</td>';
													echo '<td class="text-center colViolet">'.$salesRepTodayStats['todaySalesCount'].'</td>';
													echo '<td class="text-center colViolet">$'.$salesRepTodayStats['todaySales'].'</td>';
												echo '</tr>';
											}
										?>
										
										<!--<tr>
											<td class="col1st text-right">Valerie:</td>
											<td class="text-center colViolet">2</td>
											<td class="text-center colViolet">$660</td>
										</tr>
										
										<tr>
											<td class="col1st text-right">Ashley:</td>
											<td class="text-center colViolet">1</td>
											<td class="text-center colViolet">$340</td>
										</tr>
										
										<tr>
											<td class="col1st text-right">Manny:</td>
											<td class="text-center colViolet">2</td>
											<td class="text-center colViolet">$580</td>
										</tr>
										
										<tr>
											<td class="col1st text-right">Lindsay:</td>
											<td class="text-center colViolet">0</td>
											<td class="text-center colViolet">$0</td>
										</tr>
										
										<tr>
											<td class="col1st text-right">Total:</td>
											<td class="text-center">3</td>
											<td class="text-center">$1,000</td>
										</tr>-->
										
									  </table>
									</div>
								</div>
								
							</div>
							
							<div class="row">
								<div class="col-sm-12 text-center border-1px left-header" style="background-color:#438eb9;color:#ffffff;">
									<strong>Sales Days</strong>
								</div>
								
								<div class="col-sm-12">
									<div class="table-responsive">
									  <table class="table">
										<tr>
											<td class="col1st text-right">In Month:</td>
											<td class="text-center"><?php echo $salesData['workingDays']; ?></td>
											<td class="text-center">&nbsp;</td>
										</tr>
										
										<tr>
											<td class="col1st text-right">Remaining:</td>
											<td class="text-center colViolet"><?php echo $salesData['remainingWorkingDay']; ?></td>
											<td class="text-center">&nbsp;</td>
										</tr>
										
										
									  </table>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			
		</div>
		
		<div id="right-content" class="col-sm-offset-1 col-sm-5 right-container"><!--RIGHT COLUMN-->
		
			<div class="row">
				<div class="col-md-11 text-center">
					<?php echo CHtml::image(Yii::app()->baseUrl.'/images/engagex-sales.png','',array('style'=>'height:210px;margin-top:80px;margin-bottom:50px;')); ?>
				</div>
				<div class="col-md-11">
					<div class="row">
						<div class="col-sm-12 text-center border-1px left-header" style="background-color:#438eb9;color:#ffffff;">
							<strong>Today's Sales</strong>
						</div>
					</div>
					
					<?php 
						$totalContractedAmount = 0;
						$totalContractedCount = 0;
						foreach($marqueeData as $mDatas){
							$totalContractedAmount = $totalContractedAmount + $mDatas['contractedAmount'];
							$totalContractedCount++;
						}
					?>
					
					<div class="row">
						<div class="col-sm-12">
							<div class="table-responsive">
							  <table class="table  scoreboard-big">
								<tr>
									<td class="col1st text-right colOrange"><?php echo $totalContractedCount; ?></td>
									<td class="text-center">|</td>
									<td class="text-center">$<?php echo parseCurrency($totalContractedAmount); ?></td>
								</tr>
								
							  </table>
							  
							  <table class="table">
									<?php foreach($marqueeData as $mDatas){ ?>
										<tr>
											<td><?php echo $mDatas['salesRepName']; ?></td>
											<td><?php echo $mDatas['companyName']; ?></td>
											<td><?php echo $mDatas['customerName']; ?></td>
											<td class="text-center">$<?php echo $mDatas['contractedAmount']; ?></td>
										</tr>
									<?php } ?>
											
							  </table>
							</div>
						</div>
					</div>
				</div>
					
					
				
			</div>
		</div>
	</div>
	
	<!--FOOTER-->
	
	<?php 
		$display = 'display:none;';
		if(isset($_GET['alert']) && $_GET['alert'] == "1"){
			$display = '';
		}
	?>
	<div id="footer" style="<?php echo $display; ?>">
		<div class="row show-grid">
			<div class="col-sm-12 footer-container">
				<marquee  behavior="scroll" direction="left" scrollamount="12" style="font-size:80px;">
					<?php 
						foreach($marqueeData as $mDatas){ 
							$marqueeString = '';
							$marqueeString .= isset($salesRepData[$mDatas['a']]) ? $salesRepData[$mDatas['a']]['fullname'] : 'Sales Rep ID:'. $mDatas['a'];
							
							$image = '';
							switch($mDatas['b'])
							{
								case 13:
									$image = CHtml::image(Yii::app()->baseUrl.'/images/sales/statefarm-logo.png','',array('class'=>'marquee-logo'));
								break;
								
								case 9:
									$image = CHtml::image(Yii::app()->baseUrl.'/images/sales/farmers-logo.jpg','',array('class'=>'marquee-logo'));
								break;
								
								case 14:
									$image = CHtml::image(Yii::app()->baseUrl.'/images/sales/safeco-logo.jpg','',array('class'=>'marquee-logo','style'=>'height:80px;'));
								break;
							}
							
							$marqueeString .= $image;
							
							
							$marqueeString .= $mDatas['c'];
							// foreach($mDatas as $mData){
								echo $marqueeString.'.................... ';
								
								
							// }
						}
					?>
				</marquee>
			</div>
		</div>
	</div>
	
</div>