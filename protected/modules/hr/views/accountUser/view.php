<?php 
	$this->widget("application.components.HrSideMenu",array(
		'active'=> Yii::app()->controller->id
	));
?>

<h1>View User <small><?php echo $model->fullName; ?></small></h1>

<?php echo CHtml::link('Update User',array('update','id'=>$model->id),array('class'=>'btn btn-success')); ?> &nbsp;
<br><br>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'accountTypeLabel',
		'accountUser.first_name',
		'accountUser.last_name',
		'accountUser.salary',
		'accountUser.salary_type',
		'accountUser.date_hire',
		'accountUser.date_termination',
		'accountUser.language',
	),
)); ?>
