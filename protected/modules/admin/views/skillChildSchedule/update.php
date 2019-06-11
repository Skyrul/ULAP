<?php 
	$this->widget("application.components.AdminSideMenu",array(
		'active'=> 'skill'
	));
?>

<h1>Update Skill Child Schedule <small><?php echo $skillChild->skill->skill_name; ?> - <?php echo $skillChild->child_name; ?></small></h1>

<?php $this->renderPartial('_form', array(
	'skillChild'=>$skillChild,
	'model'=>$model,
)); ?>