<?php 
	$this->widget("application.components.AdminSideMenu",array(
		'active'=> 'skill',
	));
?>

<div class="page-header">
	<h1>Add Child Skill</h1>
</div>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>