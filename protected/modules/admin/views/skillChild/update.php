<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl.'/js/select2.min.js'); ?>
<?php Yii::app()->clientScript->registerCssFile(Yii::app()->request->baseUrl.'/css/select2.min.css'); ?>

<?php 
	$this->widget("application.components.AdminSideMenu",array(
		'active'=> 'skill',
	));
?>

<div class="page-header">
	<h1> Update Child Skill <small>&raquo; <?php echo $model->child_name; ?></small></h1>
</div>

<?php $this->renderPartial('_form', array(
	'model'=>$model,
	'skillChildAccountsArray'=>$skillChildAccountsArray,
)); ?>