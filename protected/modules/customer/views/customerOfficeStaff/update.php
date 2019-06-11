<?php

$this->widget("application.components.CustomerSideMenu",array(
		'active'=> Yii::app()->controller->id,
		'customer' => $model->customer,
));
?>


<h1>Update Office Staff <small><?php echo $model->staff_name; ?></small></h1>

<?php $this->renderPartial('_form', array(
	'model'=>$model,
	'customer_id'=>$customer_id,
	'customer_office_id'=>$customer_office_id,
)); ?>