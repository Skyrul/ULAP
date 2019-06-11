<?php if($index == 0): ?>

<thead>
	<th>Name</th>
	<th>Team Leader</th>
	<th>Description</th>
	<th>Action</th>
</thead>

<?php endif; ?>

<tr>
	<td><?php echo $data->name; ?></td>
	
	<td><?php echo isset($data->leader) ? $data->leader->getFullName() : ''; ?></td>
	
	<td><?php echo $data->description; ?></td>
	
	<td>
		<?php echo CHtml::link('<i class="fa fa-pencil"></i> Edit', array('update', 'id'=>$data->id), array('class'=>'btn btn-info btn-minier')); ?>
		
		<?php echo CHtml::link('<i class="fa fa-times"></i> Delete', array('delete', 'id'=>$data->id), array('class'=>'btn btn-danger btn-minier', 'confirm'=>'Are you sure you want to delete this?')); ?>
	</td>
</tr>
