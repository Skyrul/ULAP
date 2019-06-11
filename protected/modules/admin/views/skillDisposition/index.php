<?php 
	$this->widget("application.components.AdminSideMenu",array(
		'active'=> 'skill'
	));
?>

<?php 
	Yii::app()->clientScript->registerScript('did-action-buttons','
		didAjaxSending = false;
		
		$("#btn-clone-skill-disposition").on("click",function(){
			$.ajax({
				url: yii.urls.absoluteUrl + "/admin/skillDisposition/clone/skill_id/'.$skill->id.'",
				type: "GET",
				beforeSend: function(){
				},
				complete: function(){
				},
				error: function(){
				},
				success: function(r){
					header = "Clone Skill Disposition";
					$("#myModal #myModalLabel").html(header);
					$("#myModal .modal-body").html(r);
					$("#myModal").modal();
					
				},
			});
		});
	',CClientScript::POS_END);	
?>

<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Modal title</h4>
      </div>
      <div class="modal-body">
        ...
      </div>
    </div>
  </div>
</div>

<div class="page-header">
	<h1>
		<?php echo $skill->skill_name; ?> Dispositions
		
		<?php
			if( Yii::app()->user->account->checkPermission('structure_skills_disposition_add_button','visible') )
			{
				echo CHtml::link('<i class="fa fa-plus"></i> Add Skill Disposition',array('create','skill_id'=>$skill->id),array('class'=>'btn btn-primary btn-xs')); 
			}
		?>
		
		<?php 
			if( Yii::app()->user->account->checkPermission('structure_skills_disposition_clone_button','visible') )
			{
				echo CHtml::link('<i class="fa fa-files-o"></i> Clone Skill Disposition','#',array('class'=>'btn btn-primary btn-xs','id'=>'btn-clone-skill-disposition')); 
			}
		?>
	</h1>
</div>

<div class="col-sm-6">
	<table class="table table-striped table-condensed table-hover">	
		<tr>
			<th>Skill Disposition Name</th>
			<th>Options</th>
		</tr>
		<?php 
			foreach($skill->skillDispositions as $skillDisposition)
			{
			?>
				<tr>
					<td><?php echo $skillDisposition->skill_disposition_name;?> </td>
					
					
					<td>
						<?php 
							if( Yii::app()->user->account->checkPermission('structure_skills_disposition_edit_button','visible') )
							{
								echo CHtml::link('<i class="fa fa-pencil"></i> Edit',array('skillDisposition/update', 'id'=>$skillDisposition->id, 'skill_id'=>$skill->id),array('class'=>'btn btn-xs btn-info btn-minier')); 
							}
						?>
						
						<?php 
							if( Yii::app()->user->account->checkPermission('structure_skills_disposition_sub_dispo_button','visible') )
							{
								echo CHtml::link('<i class="fa fa-search"></i> Sub Dispo', array('skillDispositionDetail/index','skill_disposition_id'=>$skillDisposition->id, 'skill_id'=>$skill->id), array('class'=>'btn btn-xs btn-info btn-minier')); 
							}
						?>
						
						<?php 
							if( $skillDisposition->is_send_email == 1 && Yii::app()->user->account->checkPermission('structure_skills_disposition_email_settings_button','visible') )
							{
								echo CHtml::link('<i class="fa fa-envelope"></i> Email Settings',array('skillDisposition/emailSettings', 'id'=>$skillDisposition->id),array('class'=>'btn btn-xs btn-info btn-minier'));
							}
						?>
						
						<?php 
							if( $skillDisposition->is_send_text == 1 && Yii::app()->user->account->checkPermission('structure_skills_disposition_text_settings_button','visible') )
							{
								echo CHtml::link('<i class="fa fa-mobile"></i> Text Settings',array('skillDisposition/textSettings', 'id'=>$skillDisposition->id),array('class'=>'btn btn-xs btn-info btn-minier'));
							}
						?>
						
						<?php 
							if( Yii::app()->user->account->checkPermission('structure_skills_disposition_delete_button','visible') )
							{
								echo CHtml::link('<i class="fa fa-times"></i> Delete',array('skillDisposition/delete', 'id'=>$skillDisposition->id, 'skill_id'=>$skill->id),array('class'=>'btn btn-xs btn-danger btn-minier', 'confirm'=>'Are you sure you want to delete this?')); 
							}
						?>
					</td>
					
				</tr>
			<?php
			}
		?>
	</table>
</div>