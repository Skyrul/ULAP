<?php

$this->widget("application.components.CustomerSideMenu",array(
		'active'=> Yii::app()->controller->id,
		'customer' => $model->customer,
));
?>

<h1>Add Office Staff</h1>

<?php $this->renderPartial('_form', array(
	'model'=>$model,
	'customer_id'=>$customer_id,
	'customer_office_id'=>$customer_office_id,
)); ?>