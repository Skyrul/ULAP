<?php

$this->widget("application.components.CustomerSideMenu",array(
		'active'=> Yii::app()->controller->id,
		'customer' => $model->customer,
));
?>

<h1>Office Staff <small><?php echo $model->staff_name; ?></small></h1>

<?php echo CHtml::link('Update Office Staff',array('customerOfficeStaff/update','id'=>$model->id,'customer_office_id'=>$model->id),array('class'=>'btn btn-success')); ?> &nbsp;
<br>
<br>


<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'customer.fullName',
		'customerOffice.office_name',
		'staff_name',
		'email_address',
		'position',
		'is_received_email',
		'is_portal_access',
		'phone',
		'mobile',
		'fax',
		'status',
		'is_deleted',
		'date_created',
		'date_updated',
	),
)); ?>
