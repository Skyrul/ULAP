<?php
/* @var $this SkillScheduleController */
/* @var $model SkillSchedule */

$this->breadcrumbs=array(
	'Skill Schedules'=>array('index'),
	$model->id,
);

$this->menu=array(
	array('label'=>'List SkillSchedule', 'url'=>array('index')),
	array('label'=>'Create SkillSchedule', 'url'=>array('create')),
	array('label'=>'Update SkillSchedule', 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>'Delete SkillSchedule', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage SkillSchedule', 'url'=>array('admin')),
);
?>

<h1>View SkillSchedule #<?php echo $model->id; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'skill_id',
		'schedule_start',
		'schedule_end',
		'schedule_day',
		'status',
		'is_deleted',
		'date_created',
		'date_updated',
	),
)); ?>
