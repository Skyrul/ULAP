<?php 
	
	
	$this->widget("application.components.AdminSideMenu",array(
		'active'=> 'skill'
	));
?>

<div class="page-header">
	<h1>
		Email Setting
		
		<?php echo CHtml::link('<i class="fa fa-plus"></i> Add Email Template',array('update','id'=>$model->id,'tab'=>'emailSettingCreate'),array('class'=>'btn btn-primary btn-xs')); ?>
	</h1>
</div>

<div class="col-sm-6">
	<table class="table table-striped table-condensed table-hover">	
		<tr>
			<th>Skill Email Template Name</th>
			<th>Options</th>
		</tr>
		<?php 
			foreach($model->skillEmailTemplates as $skillEmailTemplate)
			{
			?>
				<tr>
					<td><?php echo $skillEmailTemplate->template_name;?> </td>
					
					
					<td>
						<?php 
								echo CHtml::link('<i class="fa fa-pencil"></i> Edit',array('skill/update', 'id'=>$model->id, 'tab'=>'emailSettingUpdate', 'skillEmailTemplateId'=>$skillEmailTemplate->id),array('class'=>'btn btn-xs btn-info btn-minier')); 
						
								echo CHtml::link('<i class="fa fa-times"></i> Delete',array('skill/emailSettingDelete', 'id'=>$model->id, 'skillEmailTemplateId'=>$skillEmailTemplate->id),array('class'=>'btn btn-xs btn-danger btn-minier', 'confirm'=>'Are you sure you want to delete this?')); 
						?>
					</td>
					
				</tr>
			<?php
			}
		?>
	</table>
</div>