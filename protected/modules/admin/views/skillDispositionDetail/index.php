<?php 
	$this->widget("application.components.AdminSideMenu",array(
		'active'=> 'skill'
	));
?>

<div class="page-header">
	<h1>Skill Dispositions Details <small>&raquo; <?php echo $skillDisposition->skill_disposition_name; ?></small></h1>
</div>

<?php echo CHtml::link('<i class="fa fa-plus"></i> Add Sub Disposition',array('create','skill_disposition_id'=>$skillDisposition->id, 'skill_id' => $skillDisposition->skill_id),array('class'=>'btn btn-sm btn-primary')); ?> &nbsp;
<br>
<br>

<div class="col-md-4">
	<table class="table-striped table">
	<?php 
		foreach($skillDisposition->skillDispositionDetails as $skillDispositionDetail)
		{
		?>
			<tr>
				<td><?php echo $skillDispositionDetail->skill_disposition_detail_name; ?></td>
			
				<td>
					<?php echo CHtml::link('Edit',array('skillDispositionDetail/update','id'=>$skillDispositionDetail->id, 'skill_id'=>$skillDisposition->id),array('class'=>'btn btn-minier btn-info')); ?>
					<?php echo CHtml::link('<i class="fa fa-times"></i> Delete',array('skillDispositionDetail/delete','id'=>$skillDispositionDetail->id, 'skill_id'=>$skillDisposition->id),array('class'=>'btn btn-minier btn-danger')); ?>
				</td>
			</tr>
		<?php
		}
	?>
	</table>
</div>