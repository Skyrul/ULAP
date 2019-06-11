<?php 
	$this->widget("application.components.AdminSideMenu",array(
		'active'=> Yii::app()->controller->id
	));
?>

<div class="page-header">
	<h1>Create Skill</h1>
</div>

<?php $this->renderPartial('_form', array(
	'model'=>$model,
	'selectedSkillCompany'=>$selectedSkillCompany,
)); ?>