<?php if($index == 0): ?>

<thead>
	<th class="center">Order</th>
	<th>Title</th>
	<th>Date Created</th>
	<th>Last Updated</th>
	<th class="center" width="15%">Action</th>
</thead>

<?php endif; ?>

<tr>
	<td class="center">
		<input type="text" id="<?php echo $data->id; ?>" value="<?php echo $data->sort_order; ?>" class="news-order-txt" style="width:40px;">
	</td>
	
	<td><?php echo $data->title; ?></td>

	<td>
		<?php 
			$date = new DateTime($data->date_created, new DateTimeZone('America/Chicago'));

			$date->setTimezone(new DateTimeZone('America/Denver'));

			echo $date->format('m/d/Y g:i A');
		?>
	</td>
	
	<td>
		<?php 
			$date = new DateTime($data->date_updated, new DateTimeZone('America/Chicago'));

			$date->setTimezone(new DateTimeZone('America/Denver'));

			echo $date->format('m/d/Y g:i A');
		?>
	</td>
	
	<td class="center">
		<?php echo CHtml::link('<i class="fa fa-pencil"></i> Edit', array('update', 'id'=>$data->id), array('class'=>'btn btn-info btn-minier')); ?>
		
		<?php echo CHtml::link('<i class="fa fa-times"></i> Delete', array('delete', 'id'=>$data->id), array('class'=>'btn btn-danger btn-minier', 'confirm'=>'Are you sure you want to delete this?')); ?>
	</td>
</tr>
