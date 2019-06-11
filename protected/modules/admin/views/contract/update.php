<?php 
	$this->widget("application.components.AdminSideMenu",array(
		'active'=> Yii::app()->controller->id
	));
?>

<h1>Update Contract <small><?php echo $model->contract_name; ?></small></h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>