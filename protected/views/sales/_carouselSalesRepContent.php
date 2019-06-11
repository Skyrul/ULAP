<div class="col-sm-12">
	<h1 style="font-size:50px;"><?php echo $salesRep['fullname']; ?></h1>
</div>
		
<div class="col-sm-5">
	
	<div class="row">
		<div class="col-sm-10">Monthly Goal</div>
		<div class="col-sm-2"><?php echo $salesRep['monthly_goal']; ?></div>
	</div>
	
	<div class="row">
		<div class="col-sm-10">Monthly Stretch Goal</div>
		<div class="col-sm-2"><?php echo $salesRep['monthly_stretch_goal']; ?></div>
	</div>
	
	<div class="row">
		<div class="col-sm-10">Sales to Date</div>
		<div class="col-sm-2"><?php echo $salesRep['sales_month_to_date']; ?></div>
	</div>
	
	<div class="row">
		<div class="col-sm-12">Pace</div>
	</div>
	
	<div class="row">
		<div class="col-sm-offset-1 col-sm-9">Original</div>
		<div class="col-sm-2"><?php echo $salesRep['salesRepDailyPaceOriginal']; ?></div>
	</div>
	
	<div class="row">
		<div class="col-sm-offset-1 col-sm-9">Variance</div>
		<div class="col-sm-2"><?php echo $salesRep['salesRepDailyPaceVariance']; ?></div>
	</div>
	
	<div class="row">
		<div class="col-sm-offset-1 col-sm-9">New</div>
		<div class="col-sm-2"><?php echo $salesRep['salesRepDailyPaceNew']; ?></div>
	</div>
	
	<!--
	<div class="row">
		<div class="col-sm-6">Credits</div>
		<div class="col-sm-6">$ 110.00</div>
	</div>
	-->
</div>
<div class="col-sm-7">

	<div id="graph-container-<?php echo $account_id; ?>">
		<?php //echo CHtml::image(Yii::app()->baseUrl.'/images/sales/graph.png','',array('style'=>'width:100%;')); ?>
	</div>
</div>