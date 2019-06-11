<?php 
	$this->widget("application.components.HostDialSideMenu",array(
		'active'=> Yii::app()->controller->id,
		'customer' => $customer,
	));
?>

<?php 

$baseUrl = Yii::app()->request->baseUrl;
$cs = Yii::app()->clientScript;

// $cs->registerScriptFile("//cdn.rawgit.com/Mikhus/canvas-gauges/gh-pages/download/2.1.4/radial/gauge.min.js");

$cs->registerScriptFile($baseUrl . "/js/hostDial/gauge.min.js");

foreach($lists as $list)
{
	
	$potential_dials_total = $list->leadCallablesCount * $list->number_of_dials_per_guest;
	
	if($activeLeadsDialsCountList[$list->id] > 0 && $potential_dials_total > 0 )
		$potential_dials_made = ( ($potential_dials_total - $activeLeadsDialsCountList[$list->id]) / $potential_dials_total) * 100;
	else
		$potential_dials_made = 0;
	
	// echo $potential_dials_made; exit;
	$roundedVal = round($potential_dials_made, 1, PHP_ROUND_HALF_UP);
	
	$completed = $completedLeadsList[$list->id];
	
	if($completedLeadsList[$list->id] > 0 && $list->leadCount > 0)
		$completed_made = ($completedLeadsList[$list->id] / $list->leadCount) * 100;
	else
		$completed_made = 0;

	$roundedCompleted = round($completed_made, 1, PHP_ROUND_HALF_UP);
			
	$cs->registerScript(uniqid(),'
		
		var ctr = '.$list->id.';
		var dialsGauge = new RadialGauge({
			renderTo: "potential-dials-"+ctr
			
		}).draw();
		
		dialsGauge.value = '.$roundedVal.';
		
		var guestGauge = new RadialGauge({
			renderTo: "completed-guest-"+ctr
			
		}).draw();
		
		guestGauge.value = '.$roundedCompleted.';
		
		
	',CClientScript::POS_END);
}
?>

<?php foreach($lists as $list)
{
	
?>
	<div class="gauge-container">
	
		<div class="gauge-title"><?php echo $list->name; ?></div>
		
		<div class="gauge-box">
			<div class="gauge-label">Potential Dials</div>
			<div class="gauge-meter">
				<canvas data-type="radial-gauge" id="potential-dials-<?php echo $list->id; ?>" data-units="%"></canvas>
			</div>
		</div>

		<div class="gauge-box">
			<div class="gauge-label">Completed Guests</div>
			<div class="gauge-meter">
				<canvas data-type="radial-gauge" id="completed-guest-<?php echo $list->id; ?>"></canvas>
			</div>
		</div>

		<div>
			
			<div class="left-column-info" style="float:left;">
				<div>
					<div class="sub-meter">Total Guests in List</div><div class="sub-meter-value"><?php echo $list->leadCount; ?></div>
				</div>
				
				<div>
					<div class="sub-meter">Total Dials Made</div><div class="sub-meter-value"><?php echo $leadsDialsCountList[$list->id]; ?></div>
				</div>
				
				<div>
					<div class="sub-meter">Callable Guests</div><div class="sub-meter-value"><?php echo $list->leadCount - $completedLeadsList[$list->id]; ?></div>
				</div>
				
				<!-- guest reached = voice contact setting on disposition.-->
				<div>
					<div class="sub-meter">Guests Reached</div><div class="sub-meter-value"><?php echo $guestReached[$list->id]; ?></div>
				</div>
				
				<?php if(false){ ?>
				
				<div>
					Computation Used:
				</div>
				
				<div>
					<div class="sub-meter">Total Completed Guest</div><div class="sub-meter-value"><?php echo $list->leadCount - $list->leadCallablesCount; ?></div>
				</div>
				
				<div>
					<div class="sub-meter">Number of Dials</div><div class="sub-meter-value"><?php echo $list->number_of_dials_per_guest; ?></div>
				</div>
				
				<div>
					<div class="sub-meter">Total Potential Dials</div><div class="sub-meter-value"><?php echo $potential_dials_total = $list->leadCallablesCount * $list->number_of_dials_per_guest; ?></div>
				</div>
				
				<div>
					<div class="sub-meter">Completed Guest</div>
					<div class="sub-meter-value">
						<?php 
						
							$completed = $completedLeadsList[$list->id];
							
							
							if($completedLeadsList[$list->id] > 0 && $list->leadCount > 0)
								$completed_made = ( $completedLeadsList[$list->id] / $list->leadCount) * 100;
							else
								$completed_made = 0;
	
							$roundedCompleted = round($completed_made, 1, PHP_ROUND_HALF_UP);
	
							echo $completed_made;
							echo ' (';
							echo $roundedCompleted = round($completed_made, 1, PHP_ROUND_HALF_UP);
							echo ' )';
						?>
					</div>
				</div>
				
				<!-- completed guest = the lead is completed either from disposition or max dials -->
				<div>
					<div class="sub-meter">Potential Dials</div>
					<div class="sub-meter-value">
						<?php 
							if($activeLeadsDialsCountList[$list->id] > 0 && $potential_dials_total > 0 )
								$potential_dials_made = (($potential_dials_total - $activeLeadsDialsCountList[$list->id]) / $potential_dials_total) * 100;
							else
								$potential_dials_made = 0;
							
							//echo $potential_dials_made;
							echo ' (';
							echo ($potential_dials_total);
							echo ' )';
							
							echo ' (';
							echo ($activeLeadsDialsCountList[$list->id]);
							echo ' )';
							
							
							echo ' (';
							echo ($potential_dials_total - $activeLeadsDialsCountList[$list->id]);
							echo ' )';
							
							echo ' (';
							echo $roundedVal = round($potential_dials_made, 1, PHP_ROUND_HALF_UP);
							echo ' )';
						?>
					</div>
				</div>
				
				<div>
					Dials Per Hour
				</div>
				<div>
					<div class="sub-meter">Agent Hours</div><div class="sub-meter-value">--</div>
				</div>
				
				<div>
					<div class="sub-meter">Guest Reached Data</div><div class="sub-meter-value"><?php echo $guestReached[$list->id]; ?> / <?php echo $list->leadCount - $completedLeadsList[$list->id]; ?></div>
				</div>
				
				<?php } ?>
			</div>
			
			
			<!--
				DPH explanation:
				That would be the dials per hour for that skill list
				Number of all the dials made on the list 
				Divided by all the agent hours spent dialing on that list

				Example 
				500 dials 
				25 hours would be a dph of 20
			
			-->
			<div class="right-column-info" style="float:left;">
			
				<div class="dph-container">
					<div class="dph">DPH</div>
					<div class="dph-val"><?php echo $dphData[$list->id]; ?></div>
				</div>
				
				<div>
					<div class="sub-meter-value" style="margin-top:6px;margin-left:0px;">
			
						<?php 
						
							if($guestReached[$list->id] > 0 && ($list->leadCount - $completedLeadsList[$list->id]) > 0 )
								$guestReachedPercent = ($guestReached[$list->id] / ($list->leadCount - $completedLeadsList[$list->id])) * 100;
							else
								$guestReachedPercent = 0;
							
							// $guestReachedPercent;
							echo $roundedVal = round($guestReachedPercent, 1, PHP_ROUND_HALF_UP);
							echo '%';
						?>
					</div>
				</div>
			</div>
			
			<div style="clear:both;"></div>
		</div>
	</div>
	
<?php } ?>

<style>
	.gauge-meter{height:160px;width:160px;display:inline-block;border:1px solid #ccc;}
	.gauge-box{display:inline-block;text-align:center;width:160px;}
	.gauge-container{
			text-align:center;
			border:1px solid #a2a2a2;
			padding:4px; 
			background-color:#efd8861f; 
			display:inline-block;
			margin:0px 10px 10px 0px;
	}
	.gauge-title{font-size:16px;font-weight:bold;background-color:#FFF;margin-bottom:12px;}
	.gauge-label{font-weight:bold;}
	.dph-container{ 
		height:76px;
		background-color:#fbc767d1;
		margin-top:2px;
		border:1px #ecab33d1 solid;
		width:82px;
		padding:6px;
		text-align:center;
	}
	
	.dph{font-size:14px;}
	.dph-val{margin-top:5px;font-size:22px;}
	.sub-meter{
		background-color: #7eafea;
		border:1px #669ad8 solid;
		width: 155px;
		display:inline-block;
		font-weight:bold;
	}
	
	.sub-meter-value{
		border:1px #4C8FBD solid;
		width: 82px;
		margin: 3px;
		display:inline-block;
		padding-left:8px;
	}
</style>