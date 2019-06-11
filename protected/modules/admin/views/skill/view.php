<?php 
	$this->widget("application.components.AdminSideMenu",array(
		'active'=> Yii::app()->controller->id
	));
?>

<h1>View Skill <small><?php echo $model->skill_name; ?></small></h1>

<?php echo CHtml::link('Update Skill',array('update','id'=>$model->id),array('class'=>'btn btn-success')); ?> &nbsp;
<br><br>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'skill_name',
		'description',
		'status',
		'is_deleted',
		'date_created',
		'date_updated',
	),
)); ?>

<?php 
