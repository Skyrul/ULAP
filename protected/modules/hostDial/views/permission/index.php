<?php 
	$this->widget("application.components.CustomerSideMenu",array(
		'active'=> Yii::app()->controller->id,
		'customer' => $customer,
	));
?>

<div class="row">
	<div class="col-md-12">
		<div class="row">
			<div class="col-sm-12">
				<div class="page-header">
					<h1>Permissions</h1>
				</div>
			</div>
		</div>
		
		<?php
			foreach(Yii::app()->user->getFlashes() as $key => $message) {
				echo '<div class="alert alert-' . $key . '"><button data-dismiss="alert" class="close" type="button"><i class="ace-icon fa fa-times"></i></button> ' . $message . "</div>\n";
			}
		?>

		<table class="table table-bordered table-striped table-hover table-condensed">
		
			<thead>
				<tr>
					<th>Security Group</th>
					<th class="center">Action</th>
				</tr>
			</thead>
			
			<?php if(!empty($officeStaffs)): ?>
				<?php foreach( $officeStaffs as $officeStaff ): ?>
				
					<?php if( isset($officeStaff->account) ): ?>
				
					<tr>
						<td><?php echo $officeStaff->account->getFullName(); ?></td>
						<td class="center"><?php echo CHtml::link('<i class="fa fa-pencil"></i> Edit', array('update', 'account_id'=>$officeStaff->account->id, 'customer_id'=> $customer->id), array('class'=>'btn btn-minier btn-info')); ?></td>
					</tr>
					
					<?php endif; ?>
				
				<?php endforeach; ?>
			
			<?php endif; ?>
			
		</table>
	</div>
</div>
