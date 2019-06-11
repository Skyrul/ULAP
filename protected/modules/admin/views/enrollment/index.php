<?php 
	$this->widget("application.components.AdminSideMenu",array(
		'active'=> Yii::app()->controller->id
	));
?>

<div class="page-header">
	<h1>
		Enrollment
		<?php echo CHtml::link('<i class="fa fa-plus"></i> Add Content',array('create'),array('class'=>'btn btn-sm btn-primary')); ?> <br><br>
	</h1>
</div>



<div class="row">
	<div class="col-sm-12">
		<?php
			foreach(Yii::app()->user->getFlashes() as $key => $message) {
				echo '
			   <div class="alert alert-' . $key . '">
				<button data-dismiss="alert" class="close" type="button">
				 <i class="ace-icon fa fa-times"></i>
				</button>' . $message . "
			   </div>\n";
			}
		?>
		
		<table class="table table-striped table-hover">
			<thead>
				<th>#</th>
				<th>Enrollment Url</th>
				<th>Display on enrollment form</th>
				<th class="center">Options</th>
			</thead>
			
			<?php
				if( $models )
				{
					$ctr = 1;
					
					foreach( $models as $model )
					{
					?>
						<tr>
							<td><?php echo $ctr; ?></td>
							
							<td>https://enroll.engagexapp.com/index.php/<?php echo $model->enrollment_url; ?></td>
							
							<td>
								<?php 
									if( $model->status == 1 )
									{
										echo 'Yes';
									}
									else
									{
										echo 'No';
									}
								?>
							</td>
							
							<td class="center">
								<?php echo CHtml::link('<i class="fa fa-pencil"></i> Edit',array('update','id'=>$model->id),array('class'=>'btn btn-minier btn-info')); ?> 
								<?php echo CHtml::link('<i class="fa fa-times"></i> Delete',array('delete','id'=>$model->id),array('class'=>'btn btn-danger btn-minier')); ?>
							</td>
						</tr>
					
					<?php
					}
				}
				else
				{
					echo '<td cols="4">No records found.</td>';
				}
			?>
			
		</table>
		
	</div>
</div>
