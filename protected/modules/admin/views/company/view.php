
<?php 
	$this->widget("application.components.AdminSideMenu",array(
		'active'=> Yii::app()->controller->id
	));
?>

<h1>Company <small><?php echo $model->company_name; ?></small></h1>

<?php echo CHtml::link('Update Company',array('update','id'=>$model->id),array('class'=>'btn btn-success')); ?> &nbsp;
<br><br>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'company_name',
		'description',
		'email_address',
		'status',
		'is_deleted',
		'date_created',
		'date_updated',
	),
)); ?>
