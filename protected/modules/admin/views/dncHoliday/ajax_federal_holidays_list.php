<?php 
	if( $models )
	{
		foreach( $models as $model )
		{
		?>
		
			<tr>
				<td class="center">
					<button id="<?php echo $model->id; ?>" class="btn btn-minier btn-primary btn-edit-federal-holiday"><i class="fa fa-pencil"></i> Edit</button>
					<button id="<?php echo $model->id; ?>" class="btn btn-minier btn-danger btn-delete-federal-holiday"><i class="fa fa-times"></i> Delete</button>
				</td>
				<td><?php echo $model->name; ?></td>
				<td><?php echo date('m/d/Y', strtotime($model->date)); ?></td>
				<td class="center"> <?php echo number_format($model->dials); ?></td>
			</tr>
		
		<?php
		}
	}
?>