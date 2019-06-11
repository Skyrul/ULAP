<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl.'/js/select2.min.js'); ?>
<?php Yii::app()->clientScript->registerCssFile(Yii::app()->request->baseUrl.'/css/select2.min.css'); ?>

<?php 
	$this->widget("application.components.AdminSideMenu",array(
		'active'=> Yii::app()->controller->id
	));
?>

<h1>Update Survey</h1>


<?php $this->renderPartial('_form', array(
	'model'=>$model,
	'surveySkillsArray' => $surveySkillsArray,
	'customers' => $customers,
	'surveyCustomersArray' => $surveyCustomersArray,
)); ?>