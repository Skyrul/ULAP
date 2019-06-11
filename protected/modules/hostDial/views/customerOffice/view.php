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

<h1>Office <small><?php echo $model->office_name; ?> | <?php echo $model->customer->fullName; ?></small></h1>

<?php echo CHtml::link('Update Office',array('customerOffice/update','id'=>$model->id,'customer_office_id'=>$model->id),array('class'=>'btn btn-success')); ?> &nbsp;
<?php echo CHtml::link('View Staffs',array('customerOfficeStaff/index','customer_id'=>$model->id,'customer_office_id'=>$model->id),array('class'=>'btn btn-success')); ?>
<br>
<br>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'customer.fullName',
		'office_name',
		'email_address',
		'address',
		'phone',
		'city',
		'fax',
		'state',
		'zip',
		'landmark',
		'statusLabel',
		'is_deleted',
		'date_created',
		'date_updated',
	),
)); ?>
