<?php
/* @var $this SurveyController */
/* @var $data Survey */
?>

<?php if ($index == 0){ ?>

<thead>
	<th>Name</th>
	<th>Status</th>
	<th>Date Created</th>
	<th>Date Updated</th>
	<th></th>
</thead>

<?php } ?>


<tr>
	<td><?php echo $data->name; ?></td>
	<td class="text-center"><?php echo $data->getStatus(); ?></td>
	<td><?php echo date('F d, Y G:i a', strtotime($data->date_created)); ?></td>
	<td><?php echo date('F d, Y G:i a', strtotime($data->date_updated)); ?></td>
	<td>
		<?php echo CHtml::link('<i class="fa fa-search"></i>', array('survey/view', 'id'=>$data->id), array('class'=>'btn btn-success btn-xs')); ?>
		<?php echo CHtml::link('<i class="fa fa-edit"></i>', array('survey/update', 'id'=>$data->id), array('class'=>'btn btn-primary btn-xs')); ?>
		<?php echo CHtml::link('<i class="fa fa-trash-o"></i>', array('survey/delete', 'id'=>$data->id), array('class'=>'btn btn-danger btn-xs', 'confirm'=>'Are you sure you want to delete this?')); ?>
	</td>
</tr>