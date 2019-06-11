<?php 
	$this->widget("application.components.AccountingSideMenu",array(
		'active'=> 'promos'
	));
?>

<h1>Create Promo</h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>