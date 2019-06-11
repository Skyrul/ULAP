<?php
/* @var $this Skill Child ScheduleController */
/* @var $model Skill Child Schedule */

$this->breadcrumbs=array(
	'Skill Schedules'=>array('index'),
	'Manage',
);

$this->menu=array(
	array('label'=>'List Skill Child Schedule', 'url'=>array('index')),
	array('label'=>'Create Skill Child Schedule', 'url'=>array('create')),
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$('#skill-schedule-grid').yiiGridView('update', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Manage Skill Schedules</h1>

<p>
You may optionally enter a comparison operator (<b>&lt;</b>, <b>&lt;=</b>, <b>&gt;</b>, <b>&gt;=</b>, <b>&lt;&gt;</b>
or <b>=</b>) at the beginning of each of your search values to specify how the comparison should be done.
</p>

<?php echo CHtml::link('Advanced Search','#',array('class'=>'search-button')); ?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->

<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'skill-schedule-grid',
	'dataProvider'=>$model->search(),
	'filter'=>$model,
	'columns'=>array(
		'id',
		'skill_child_id',
		'schedule_start',
		'schedule_end',
		'schedule_day',
		'status',
		/*
		'is_deleted',
		'date_created',
		'date_updated',
		*/
		array(
			'class'=>'CButtonColumn',
		),
	),
)); ?>
