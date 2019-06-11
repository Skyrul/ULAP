<?php 
	$this->widget("application.components.AdminSideMenu",array(
		'active'=> 'skill'
	));
?>

<h1>Skill Schedule <small><?php echo $skill->skill_name; ?></small></h1>

<?php $this->renderPartial('_form', array(
	'skill'=>$skill,
	'model'=>$model,
)); ?>