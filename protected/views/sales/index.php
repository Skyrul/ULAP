<style>
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
	
	#right-content{
		background-color:blue;
		min-height:1500px;
		color:#FFFFFF;
		font-size:38px;
	}
	
	.left-header{
		font-size:54px;
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
	
	.content-carousel{
		display:none;
	}
</style>

<?php Yii::app()->clientScript->registerScript('backgroundJs','
	var x = 2;
	var y;
	var carouselColors = ["blue","red","green"];
	var colorCount = carouselColors.length;
	var colorIndex = 2;
	
	function changecolors() {
		y = $(".content-carousel").length - 2;
		
		setInterval(change, 10000);
		// setInterval(change, 5000);
		
		
	}

	function change() {
		
		
		// var x = 5;
		var isReset = false;
		
		$(".content-carousel").css({"display":"none"});
		
		if(x == (y + 1) )
		{
			$("#content-remaining-sales-day").css({"display":"block"});
			color = "pink";
			x++;
		}
		else if(x == (y + 2) )
		{
			$("#content-pie-chart").css({"display":"block"});
			color = "orange";
			isReset = true;
		}
		else
		{
			$("#content-"+x).css({"display":"block"});
			color = carouselColors[colorIndex - 1];
		}
		
		
		

		$("#right-content").css({"background-color":color});
		
		
		if(x <= y)
		{
			x++;
			colorIndex++;
			
			if(colorIndex > colorCount)
				colorIndex = 1;
		}
		
		if(isReset)
			x = 1;
		
	}
	
	changecolors();
	
	function notifySales()
	{
		$(".siren-img").show();
		playAudio();
		
		var refreshIntervalId = setInterval(playAudio, 2500);
		
		setTimeout(function() {
			$(".siren-img").hide();
			clearInterval(refreshIntervalId);
		}, 7000);
	}
	
	function playAudio()
	{
		$("#audio-siren")[0].play();
	}
	
	
	//setInterval(notifySales, 3 * 60000);
	
	
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
				window.location.href = "'.Yii::app()->createUrl('/sales/index').'?alert=1";
			}
			
	  });
	}
	',CClientScript::POS_END); 
	
?>

<?php 
	if(isset($_GET['alert']) && $_GET['alert'] == "1"){
		Yii::app()->clientScript->registerScript('notifyNewSalesAlert','
			notifySales();
		',CClientScript::POS_END); 
	}
?>

<?php Yii::app()->clientScript->registerScriptFile('https://code.highcharts.com/highcharts.js',CClientScript::POS_HEAD); ?>
<?php Yii::app()->clientScript->registerScriptFile('https://code.highcharts.com/modules/exporting.js',CClientScript::POS_HEAD); ?>

<?php Yii::app()->clientScript->registerScript('graphChartJs',"
$(function () {
	
	Highcharts.setOptions({
        chart: {
            backgroundColor: {
                linearGradient: [0, 0, 500, 500],
                stops: [
                    [0, 'rgb(255, 255, 255)'],
                    [1, 'rgb(240, 240, 255)']
                    ]
            },
            borderWidth: 2,
            plotBackgroundColor: 'rgba(255, 255, 255, .9)',
            plotShadow: true,
            plotBorderWidth: 1
        }
    });
});
",CClientScript::POS_END); ?>


<?php
foreach($salesRepData as $key => $salesRep){
	Yii::app()->clientScript->registerScript('salesRepGraphData-'.$key,"
		var chart1 = new Highcharts.Chart({
			chart: {
				renderTo: 'graph-container-".$key."',
			},
			title: {
				text: '".date("F")."',
				x: -20 //center
			},
			xAxis: {
				categories: ".json_encode(range(1,31))."
			},
			yAxis: {
				title: {
					text: 'Sales'
				},
				plotLines: [{
					value: 0,
					width: 1,
					color: '#808080'
				}]
			},
			tooltip: {
				valueSuffix: 'Enrollees'
			},
			legend: {
				layout: 'vertical',
				align: 'right',
				verticalAlign: 'middle',
				borderWidth: 0
			},
			series: [
				{
					name: 'Actual',
					data: ".json_encode(array_values($salesRepData[$key]['dailySales']))."
				},
				{
					name: 'Goal',
					data: ".json_encode(array_values($salesRepData[$key]['goalSales']))."
				},
				{
					name: 'Stretch',
					data: ".json_encode(array_values($salesRepData[$key]['stretchSales']))."
				}
			]
		});
	",CClientScript::POS_END); 
}
?>

<?php

$pieChartData = array();
foreach($salesRepData as $key => $salesRep)
{
	$pieChartData[] = array(
		'name' => $salesRep['fullname'],
		'y' => (int) $salesRep['sales_month_to_date']
	);
}

Yii::app()->clientScript->registerScript('salesRepPieGraphData',"
	var pieChart = new Highcharts.Chart({
		chart: {
			renderTo: 'salesRep-pie-chart',
			plotBackgroundColor: null,
			plotBorderWidth: null,
			plotShadow: false,
			type: 'pie'
		},
		title: {
			text: '".date("F")." - Month to Date',
			x: -20, //center,
			style: {
				fontSize: '25px'
			}
		},
		tooltip: {
			pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
		},
		plotOptions: {
			pie: {
				// size:'100%',
				allowPointSelect: true,
				cursor: 'pointer',
				dataLabels: {
					enabled: true,
					format: '<b>{point.name}</b>: {point.percentage:.1f} %',
					style: {
						color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black',
						fontSize: '18px'
					}
				}
			}
		},
		series: [{
			name: 'Sales Rep',
			colorByPoint: true,
			data: ".json_encode($pieChartData)."
		}]
	});
",CClientScript::POS_END); 
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
		<div class="col-sm-4 left-container"><!-- LEFT COLUMN-->
		
			<div class="row">
				<div class="col-sm-12 text-center border-1px left-header" style="background-color:#438eb9;color:#ffffff;">
					<strong>Monthly Sales </strong>
				</div>
				
				<div class="row">
					<div class="col-sm-6">
						<div class="row">
							<div class="col-sm-12 text-center" style="padding-bottom:0px;"><span class="number sblue"><?php echo $salesData['currentEnrollCount']; ?>/<?php echo $salesData['currentEnrollCountMonthlyGoal']; ?></span></div>
							
						</div>
						
						<div class="row">
							<div class="col-sm-12 text-center" style="padding-top:0px;"><span class="mini-number sblack"><?php echo $salesData['currentEnrollValue']; ?>/<?php echo $salesData['currentEnrollValueMonthlyGoal']; ?></span></div>
							
						</div>
					</div>
					
					<div class="col-sm-6">
						<div class="row">
							<div class="col-sm-12 text-center" style="padding-bottom:0px;">
								<span class="number sblack"><?php echo $salesData['currentEnrollCountRemaining']; ?></span>
								<span class="fixed-label"><span class="number-label">Remaining</span>
							</div>
						</div>
						
						<div class="row">
							<div class="col-sm-12 text-center" style="padding-top:0px;"><span class="mini-number sblue"><?php echo $salesData['currentEnrollValueRemaining']; ?></span></div>
							
						</div>
					</div>
				</div>
				
			</div>
			
			<div class="row">
				<div class="col-sm-12 text-center border-1px  left-header">
					<strong><span style="color:#438eb9;">Daily Pace</span></strong>
				</div>
				
				<div class="daily-pace-stats">
					<div class="row">
						<div class="col-sm-12 text-center">
							<span class="number sblue"><?php echo $salesData['dailyPaceOriginal']; ?></span> 
							<span class="fixed-label"><span class="number-label">Original</span>
						</div>
					</div>
					
					<div class="row">
						<div class="col-sm-12 text-center">
							<span class="number <?php echo ($salesData['dailyPaceVariance'] < 0) ? 'sred' : 'sblue'; ?>"><?php echo $salesData['dailyPaceVariance']; ?></span> 
							<span class="fixed-label"><span class="number-label">Variance</span>
						</div>
					</div>
					
					<div class="row">
						<div class="col-sm-12 text-center">
							<span class="number sblue"><?php echo $salesData['dailyPaceNew']; ?></span> 
							<span class="fixed-label"><span class="number-label">New</span>
						</div>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col-sm-12 text-center border-1px  left-header">
					<strong><span style="color:#438eb9;">Today Sales</span></strong>
				</div>
				
				<div class="col-sm-12 text-center"> 
					<span class="number sblue todays-sales"><?php echo $salesData['todayEnrollCount']; ?></span>
				</div>
			</div>
			
		</div>
		<div id="right-content" class="col-sm-8 right-container"><!--RIGHT COLUMN-->
		
			<?php 
				$salesCtr = 1; 
				
				foreach($salesRepData as $key => $salesRep)
				{
					$style = 'style=""';
					
					if($salesCtr == 1)
						$style = 'style="display:block;"';
					?>
					
					<div id="content-<?php echo $salesCtr; ?>" class="row content-carousel" <?php echo $style; ?> data-carousel-color="<?php echo $salesCtr; ?>">
			
						<?php 
							$this->renderPartial('_carouselSalesRepContent',array(
								'account_id' => $key,
								'salesRep' => $salesRep,
							));
						?>
					</div>
					<?php
					$salesCtr++;
				}
			?>
			
			<div id="content-remaining-sales-day" class="row content-carousel">
				<div class="col-sm-12  text-center">
					<div style="font-size:55px;">Remaining Sales Days</div>
				</div>
				
				<br><br>
				<div class="col-sm-12 text-center">
					<div style="font-size:340px;font-weight:bolder"><?php echo $salesData['remainingWorkingDay']; ?></div>
				</div>
				
			</div>
			
			<div id="content-pie-chart" class="row content-carousel">
				<div class="col-sm-12 text-center">
					<br>
					<div id="salesRep-pie-chart" style="height:750px;width:800px;position: relative;left: 200px;">
						<?php //echo CHtml::image(Yii::app()->baseUrl.'/images/sales/pie.png','',array('class'=>'','style'=>'height:650px;')); ?>
					</div>
				</div>
			</div>
			
		</div>
	</div>
	
	<?php 
		// $marqueeData = array(
			// array('a'=>'Valerie Strickland','b'=>'State Farm', 'c'=> 'John Doe - 445.00'),
			// array('a'=>'Ashley Paxton','b'=>'Farmers', 'c'=> 'Mary Jane - 360.00'),
			// array('a'=>'Alejandra Clark','b'=>'Safeco', 'c'=> 'Tom Collins - 445.00')
		// );
	?>
	<!--FOOTER-->
	<div id="footer">
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