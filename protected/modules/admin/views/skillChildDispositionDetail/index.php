<?php 
	$this->widget("application.components.AdminSideMenu",array(
		'active'=> 'skill'
	));
?>

<div class="page-header">
	<h1>Skill Child Dispositions Details <small>&raquo; <?php echo $skillChildDisposition->skill_child_disposition_name; ?></small></h1>
</div>

<?php echo CHtml::link('<i class="fa fa-plus"></i> Add Sub Disposition',array('create','skill_child_disposition_id'=>$skillChildDisposition->id, 'skill_child_id' => $skillChildDisposition->skill_child_id),array('class'=>'btn btn-sm btn-primary')); ?> &nbsp;
<br>
<br>

<div class="col-md-4">
	<table class="table-striped table">
	<?php 
		foreach($skillChildDisposition->skillChildDispositionDetails as $skillChildDispositionDetail)
		{
		?>
			<tr>
				<td><?php echo $skillChildDispositionDetail->skill_child_disposition_detail_name; ?></td>
			
				<td>
					<?php echo CHtml::link('Edit',array('skillChildDispositionDetail/update','id'=>$skillChildDispositionDetail->id, 'skill_child_id'=>$skillChildDisposition->id),array('class'=>'btn btn-minier btn-info')); ?>
					<?php echo CHtml::link('<i class="fa fa-times"></i> Delete',array('skillChildDispositionDetail/delete','id'=>$skillChildDispositionDetail->id, 'skill_child_id'=>$skillChildDisposition->id),array('class'=>'btn btn-minier btn-danger')); ?>
				</td>
			</tr>
		<?php
		}
	?>
	</table>
</div>