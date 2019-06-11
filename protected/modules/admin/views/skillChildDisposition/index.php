<?php 
	$this->widget("application.components.AdminSideMenu",array(
		'active'=> 'skill'
	));
?>

<div class="page-header">
	<h1>
		Child Skill Disposition <small>&raquo; <?php echo $skillChild->skill->skill_name; ?> - <?php echo $skillChild->child_name; ?></small>
		<?php echo CHtml::link('<i class="fa fa-plus"></i> Add Child Skill Disposition',array('create','skill_child_id'=>$skillChild->id),array('class'=>'btn btn-primary btn-sm')); ?>
	</h1>
</div>

<br>

<div class="col-md-8">
	<table class="table-striped table">
	<?php 
		foreach($skillChild->skillChildDispositions as $skillChildDisposition)
		{
		?>
			<tr>

				<td><?php echo $skillChildDisposition->skill_child_disposition_name; ?></td>
				
				<td>
					<?php echo CHtml::link('<i class="fa fa-pencil"></i> Edit',array('skillChildDisposition/update','id'=>$skillChildDisposition->id, 'skill_child_id'=>$skillChild->id),array('class'=>'btn btn-minier btn-info')); ?>
					
					<?php echo CHtml::link('<i class="fa fa-search"></i> Sub Dispo', array('skillChildDispositionDetail/index','skill_child_disposition_id'=>$skillChildDisposition->id, 'skill_child_id'=>$skillChild->id), array('class'=>'btn btn-xs btn-info btn-minier')); ?>
						
						
					<?php 
						if( $skillChildDisposition->is_send_email == 1)
						{
							echo CHtml::link('<i class="fa fa-envelope"></i> Email Settings',array('skillChildDisposition/emailSettings','id'=>$skillChildDisposition->id),array('class'=>'btn btn-minier btn-info'));
						}
					?>
					
					<?php 
						if( $skillChildDisposition->is_send_text == 1 && Yii::app()->user->account->checkPermission('structure_skills_disposition_text_settings_button','visible') )
						{
							echo CHtml::link('<i class="fa fa-mobile"></i> Text Settings',array('skillChildDisposition/textSettings', 'id'=>$skillChildDisposition->id),array('class'=>'btn btn-xs btn-info btn-minier'));
						}
					?>
					
					<?php echo CHtml::link('<i class="fa fa-times"></i> Delete',array('skillChildDisposition/delete','id'=>$skillChildDisposition->id, 'skill_child_id'=>$skillChild->id),array('class'=>'btn btn-minier btn-danger')); ?>
				</td>
			</tr>
		<?php
		}
	?>
	</table>
</div>