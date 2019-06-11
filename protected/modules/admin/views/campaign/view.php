<?php 
	$this->widget("application.components.AdminSideMenu",array(
		'active'=> Yii::app()->controller->id
	));
?>

<h1>Campaign <small><?php echo $model->campaign_name; ?></small></h1>

<?php echo CHtml::link('Update Campaign',array('update','id'=>$model->id),array('class'=>'btn btn-success')); ?> &nbsp;
<br><br>


<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'campaign_name',
		'description',
		'status',
		'is_deleted',
		'date_created',
		'date_updated',
	),
)); ?>
