<div class="wrapper">
	<div class="page-header">
		<h1>
			State Farm - Special Enrollment Customer
		</h1>
	</div>
	
	<div class="space-12"></div>
		
		<div class="tabbable tabs-left">
		
			<ul class="nav nav-tabs" id="yw1">
				<li class="<?php echo ($is_enrolled != 1) ? 'active' : ''; ?>"><?php echo CHtml::link('Pending Customer', array('enrollment/admin')); ?></li>
				<li class="<?php echo ($is_enrolled != 1) ? '' : 'active'; ?>"><?php echo CHtml::link('Enrolled Customer', array('enrollment/admin','is_enrolled' => 1)); ?></li>
			</ul>
			
			<div class="tab-content">
				<div class="tab-pane fade in active">
					<?php echo CHtml::link('<i class="fa fa-arrow-left"></i> Back',array('enrollment/admin','is_enrolled'=>$is_enrolled),array('class'=>'btn btn-sm btn-info')); ?>
					<h1>Customer Information</h1>
					<?php $this->renderPartial('_form',array(
						'model' => $model,
						'is_enrolled' => $is_enrolled,
					)); ?>
				</div>
			</div>
		</div>
	</div>
</div>