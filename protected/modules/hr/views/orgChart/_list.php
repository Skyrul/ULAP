
	<li id="<?php echo $data->id; ?>"class="ui-state-default" data-id="<?php echo $data->id; ?>">
		<div class="row">
			<div class="col-sm-9"><i class="fa fa-arrows-v"></i>
				<?php echo $data->name; ?>
			</div>
			
			<div class="col-sm-3">
				<?php echo CHtml::link('<i class="fa fa-search"></i> View', array('viewPosition', 'id'=> $data->id), array('class'=>'btn btn-minier btn-info')); ?>&nbsp;
				
				<?php echo CHtml::link('<i class="fa fa-pencil"></i> Edit', array('updatePosition', 'id'=> $data->id), array('class'=>'btn btn-minier btn-warning')); ?>&nbsp;
				
				<?php echo CHtml::link('<i class="fa fa-times"></i> Delete', array('deletePosition', 'id'=> $data->id), array('class'=>'btn btn-minier btn-danger', 'confirm'=>'Are you sure you want to delete this?')); ?>
			</div>
		</div>
	</li>
