<?php
$this->widget("application.components.CustomerSideMenu",array(
		'active'=> Yii::app()->controller->id,
		'customer' => $customer,
));
?>
<h1>Add Calendar</h1>

<?php $this->renderPartial('_form', array(
	'model'=>$model,
)); ?>