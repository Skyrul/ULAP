<?php 
	$this->widget("application.components.AdminSideMenu",array(
		'active'=> Yii::app()->controller->id
	));
?>

<div class="page-header">
	<h1>Skills

	<?php
		if( Yii::app()->user->account->checkPermission('structure_skills_add_button','visible') )
		{
			echo CHtml::link('<i class="fa fa-plus"></i> Add Skills',array('create'),array('class'=>'btn btn-sm btn-primary')); 
		}
	?>
	</h1>
</div>
<br><br>

<?php $this->forward('/admin/skill/list',false); ?>