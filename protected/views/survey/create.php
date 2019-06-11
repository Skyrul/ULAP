<?php
/* @var $this SurveyController */
/* @var $model Survey */

$this->breadcrumbs=array(
	'Surveys'=>array('index'),
	'Create',
);

?>

<div class="page-header">
	<h1>
		Create Survey
		
		<?php echo CHtml::link('<i class="fa fa-arrow-left"></i> Back', array('survey/index'), array('class'=>'btn btn-white btn-success btn-bold')); ?>
	</h1>
</div>

<div class="row">
	<div class="col-xs-12">
	
		<?php 
			$this->renderPartial('_form', array(
				'model'=>$model,
				'questions'=>$questions,
			)); 
		?>

	</div>
</div>

