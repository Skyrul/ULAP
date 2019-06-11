<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	$cs = Yii::app()->clientScript;
?>


<div class="page-header">
	<h1>Reports</h1>
</div>

<div class="tabbable tabs-left">
	
	<ul class="nav nav-tabs">
		<li><a href="<?php echo $this->createUrl('index'); ?>">Real-Time Monitors</a></li>
		<li><a href="<?php echo $this->createUrl('reports'); ?>">Reports</a></li>
		<li class="active"><a href="<?php echo $this->createUrl('callerIdListing'); ?>">Caller ID Listing</a></li>
	</ul>
	
</div>

<div class="tab-content">
	<div class="row">
		<div class="col-sm-12">
			
			<div class="page-header">
				<h1>
					<?php echo '<small>'.CHtml::link('&larr; Back', array('callerIdListing')).'</small>'; ?>
					
					<?php echo '<span class="green">'.$model->did.' - '.$model->company_name.'</span>'; ?>
				</h1>
			</div>

			<div class="space-6"></div>
			
			<table class="table table-striped table-bordered table-hover">
				<thead>
					<td>Customer</td>
					<td>Phone</td>
				</thead>
				
				<?php 
					if( $models )
					{
						foreach( $models as $customerSkill )
						{
							echo '<tr>';
								echo '<td>'.CHtml::link($customerSkill->customer->firstname.' '.$customerSkill->customer->lastname, array('/customer/insight/index', 'customer_id'=>$customerSkill->customer_id)).'</td>';
								echo '<td>'.$customerSkill->customer->phone.'</td>';
							echo '</tr>';
						}
					}
					else
					{
						echo '<td>No results found.</td>';
					}
				?>
				
			</table>
		</div>
	</div>
</div>