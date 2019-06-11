<?php 
	$this->widget("application.components.AdminSideMenu",array(
		'active'=> 'survey'
	));
?>

<?php echo CHtml::link('Back to Survey Questions', array('surveyQuestion/index','survey_id' => $model->survey_id), array('class'=>'btn btn-default')); ?>
<br>

<div class="page-header">
	<h1>Create Survey Question </h1>
</div>

<?php $this->renderPartial('_form', array(
	'survey'=>$survey,
	'model'=>$model,
	'ssqList' => $ssqList
)); ?>