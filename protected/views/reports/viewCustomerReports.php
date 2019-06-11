<div class="page-header">
	<h1>Reports</h1>
</div>

<div class="tabbable tabs-left">
	
	<ul class="nav nav-tabs">
		<li><a href="<?php echo $this->createUrl('index'); ?>">Real-Time Monitors</a></li>
		<li class="active"><a href="<?php echo $this->createUrl('reports'); ?>">Reports</a></li>
	</ul>
	
</div>

<div class="tab-content">
	
	<div class="page-header">
		<h1><?php echo $customer->getFullName(); ?></h1>
	</div>
	
	<div class="row-fluid"><?php echo CHtml::link('&larr; Back', array('crmList'), array('')); ?></div>

	<div class="space-12"></div>
	
	<div class="row">
	
		<div class="col-sm-12">
		
			<div class="col-sm-6 widget-container-col">
				<div class="widget-box ui-sortable-handle">
					<div class="widget-header">
						<h5 class="widget-title">Current Period</h5>

						<div class="widget-toolbar no-border">
							<div class="widget-menu">
							</div>
						</div>
					</div>

					<div class="widget-body">
						<div class="widget-main">
							
							<?php echo CHtml::link('<i class="fa fa-file"></i> Generate Report', array('generateCustomerReport', 'id'=>$customer->id), array('class'=>'btn btn-success btn-xs')); ?>
							
						</div>
					</div>
				</div>
			</div>
			
			<div class="col-sm-6 widget-container-col">
				<div class="widget-box ui-sortable-handle">
					<div class="widget-header">
						<h5 class="widget-title">Monthly</h5>

						<div class="widget-toolbar no-border">
							<div class="widget-menu">
							</div>
						</div>
					</div>

					<div class="widget-body">
						<div class="widget-main">
							
						</div>
					</div>
				</div>
			</div>
			
		</div>
	
	</div>
	
</div>