<?php 
	$this->widget("application.components.AdminSideMenu",array(
		'active'=> Yii::app()->controller->id
	));
?>

<h1>Create Company</h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>