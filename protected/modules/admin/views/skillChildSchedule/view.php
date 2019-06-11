<?php
/* @var $this Skill Child ScheduleController */
/* @var $model Skill Child Schedule */

$this->breadcrumbs=array(
	'Skill Child Schedules'=>array('index'),
	$model->id,
);

$this->menu=array(
	array('label'=>'List Skill Child Schedule', 'url'=>array('index')),
	array('label'=>'Create Skill Child Schedule', 'url'=>array('create')),
	array('label'=>'Update Skill Child Schedule', 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>'Delete Skill Child Schedule', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage Skill Child Schedule', 'url'=>array('admin')),
);
?>

<h1>View Skill Child Schedule #<?php echo $model->id; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'skill_child_id',
		'schedule_start',
		'schedule_end',
		'schedule_day',
		'status',
		'is_deleted',
		'date_created',
		'date_updated',
	),
)); ?>
