<?php
/* @var $this SkillScheduleController */
/* @var $dataProvider CActiveDataProvider */

$this->breadcrumbs=array(
	'Skill Schedules',
);

$this->menu=array(
	array('label'=>'Create SkillSchedule', 'url'=>array('create')),
	array('label'=>'Manage SkillSchedule', 'url'=>array('admin')),
);
?>

<h1>Skill Schedules</h1>

<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
)); ?>
