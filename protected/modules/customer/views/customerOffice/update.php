<?php
/* @var $this CustomerOfficeController */
/* @var $model CustomerOffice */

$this->breadcrumbs=array(
	'Customer Offices'=>array('index'),
	$model->id,
);

$this->widget("application.components.CustomerSideMenu",array(
		'active'=> Yii::app()->controller->id,
		'customer' => $model->customer,
));
?>

<h1>Update Office <small><?php echo $model->office_name; ?> | <?php echo $model->customer->fullName; ?></small></h1>

<?php $this->renderPartial('_form', array(
	'model'=>$model,
	
)); ?>