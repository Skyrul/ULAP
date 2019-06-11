<?php 
	$this->widget("application.components.AdminSideMenu",array(
		'active'=> 'skill',
	));
?>

<div class="page-header">
	<h1>
		Child Skill <small>&raquo; <?php echo $skill->skill_name; ?></small>
		
		<?php echo CHtml::link('<i class="fa fa-plus"></i> Add Child Skill',array('create','skill_id'=>$skill->id),array('class'=>'btn btn-primary btn-sm')); ?>
	</h1>
</div>

<br>

<div class="col-md-8">
	<table class="table-striped table">
	<?php 
		foreach($skill->skillChilds as $skillChild)
		{
		?>
			<tr>
				<td><?php echo $skillChild->child_name; ?></td>
				<td>
					<?php echo CHtml::link('<i class="fa fa-search"></i> Disposition',array('skillChildDisposition/index','skill_child_id'=>$skillChild->id,'skill_id'=>$skill->id),array('class'=>'btn btn-minier btn-info')); ?>
					<?php echo CHtml::link('<i class="fa fa-clock-o"></i> Schedule',array('skillChildSchedule/update','skill_child_id'=>$skillChild->id, 'skill_id'=>$skill->id), array('class' => 'btn btn-minier btn-info')); ?>
					<?php echo CHtml::link('<i class="fa fa-pencil"></i> Edit',array('skillChild/update','id'=>$skillChild->id,'skill_id'=>$skill->id),array('class'=>'btn btn-minier btn-info')); ?>
					<?php echo CHtml::link('<i class="fa fa-times"></i> Delete',array('skillChild/delete','id'=>$skillChild->id,'skill_id'=>$skill->id),array('class'=>'btn btn-minier btn-danger', 'confirm'=>'Are you sure you want to delete this?')); ?>
				</td>
			</tr>
			
		<?php
		}
	?>
	</table>
</div>