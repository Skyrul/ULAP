<?php
/* @var $this SurveyController */
/* @var $model Survey */

$this->breadcrumbs=array(
	'Surveys'=>array('index'),
	$model->name=>array('view','id'=>$model->id),
	'Update',
);

?>
<div class="page-header">
	<h1>
		Update Survey: <?php echo $model->name; ?>
		
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
