<?php 
	$this->widget("application.components.HrSideMenu",array(
		'active'=> Yii::app()->controller->id
	));
?>

<h1>Users</h1>

<?php echo CHtml::link('Add Users',array('create'),array('class'=>'btn btn-success')); ?> &nbsp;
<br><br><br/>