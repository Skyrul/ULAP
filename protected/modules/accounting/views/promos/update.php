<?php 
	$this->widget("application.components.AccountingSideMenu",array(
		'active'=> 'promos'
	));
?>


<h1>Update Promo <?php echo $model->promo_name; ?></h1>


<?php $this->renderPartial('_form', array(
	'model'=>$model,
)); ?>


