<?php 
	$baseUrl = Yii::app()->request->baseUrl;

	$cs = Yii::app()->clientScript;
	
	$cs->registerCss(uniqid(), '

		.tab-content .nav-tabs li{ width: 220px !important; }
	');
	
	$cs->registerScriptFile('https://code.highcharts.com/highcharts.js');
					
	$cs->registerScript(uniqid(), '
	
		$(function () {
			
			$(document).ready( function(){
				
				$("#container").highcharts({
					chart: {
						type: "line"
					},
					title: {
						text: "Sales"
					},
					subtitle: {
						text: ""
					},
					credits: {
						enabled: false
					},
					xAxis: {
						categories: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"]
					},
					yAxis: {
						title: {
							text: ""
						}
					},
					plotOptions: {
						line: {
							dataLabels: {
								enabled: true
							},
							enableMouseTracking: false
						}
					},
					series: [{
						name: "2017",
						color: "#5C9BD5",
						data: [7.0, 6.9, 9.5, 14.5, 18.4, 21.5, 25.2, 26.5, 23.3, 18.3, 13.9, 9.6]
					}, {
						name: "2018",
						color: "#ED7E30",
						data: [3.9, 4.2, 5.7, 8.5, 11.9, 15.2, 17.0, 16.6, 14.2, 10.3, 6.6, 4.8]
					}]
				});
				
				$("#yoyTrendSelect").on("change", function(){
					
					titleText = $(this).val();
					
					$("#container").highcharts({
						chart: {
							type: "line"
						},
						title: {
							text: titleText
						},
						subtitle: {
							text: ""
						},
						credits: {
							enabled: false
						},
						xAxis: {
							categories: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"]
						},
						yAxis: {
							title: {
								text: ""
							}
						},
						plotOptions: {
							line: {
								dataLabels: {
									enabled: true
								},
								enableMouseTracking: false
							}
						},
						series: [{
							name: "2017",
							color: "#5C9BD5",
							data: [7.0, 6.9, 9.5, 14.5, 18.4, 21.5, 25.2, 26.5, 23.3, 18.3, 13.9, 9.6]
						}, {
							name: "2018",
							color: "#ED7E30",
							data: [3.9, 4.2, 5.7, 8.5, 11.9, 15.2, 17.0, 16.6, 14.2, 10.3, 6.6, 4.8]
						}] 
					});
				});
				
			});
		});
	
	', CClientScript::POS_END);
?>

<div class="page-header">
	<h1>Reports</h1>
</div>

<div class="tabbable tabs-left">
	<?php $this->renderPartial('_left_nav'); ?>
</div>

<div class="tab-content text">

	<?php 
		$this->renderPartial('_main_nav', array(
			'page' => isset($_REQUEST['page']) ? $_REQUEST['page'] : ''
		)); 
	?>
	
	<div class="hr hr-18 hr-double dotted"></div>

	<div class="row">
		<div class="col-sm-12">
			<select id="yoyTrendSelect" style="width:auto;">
				<option value="Sales">Sales</option>
				<option value="Cancels">Cancels</option>
				<option value="Appointments">Appointments</option>
				<option value="Dials">Dials</option>
			</select>
		
			<div id="container" style="min-width: 310px; height: 400px; margin: 0 auto"></div>

		</div>
	</div>
	
</div>