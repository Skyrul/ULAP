<?php 
	$this->widget("application.components.AdminSideMenu",array(
		'active'=> 'skill'
	));
?>

<div class="page-header">
	<h1>Add Child Skill Disposition <button type="button" class="btn btn-primary btn-sm replacement-codes-modal"><i class="fa fa-search"></i> View Replacement Codes</button></h1>
</div>

<?php $this->renderPartial('_form', array(
	'model'=>$model
)); ?>