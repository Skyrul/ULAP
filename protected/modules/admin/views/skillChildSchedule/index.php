<?php
/* @var $this SkillScheduleController */
/* @var $dataProvider CActiveDataProvider */

$this->breadcrumbs=array(
	'Skill Child Schedules',
);

$this->menu=array(
	array('label'=>'Create Skill Child Schedule', 'url'=>array('create')),
	array('label'=>'Manage Skill Child Schedule', 'url'=>array('admin')),
);
?>

<h1>Skill Child Schedules</h1>

<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
)); ?>
