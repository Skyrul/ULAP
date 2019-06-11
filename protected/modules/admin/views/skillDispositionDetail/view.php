<?php
/* @var $this SkillDispositionDetailController */
/* @var $model SkillDispositionDetail */

$this->breadcrumbs=array(
	'Skill Disposition Details'=>array('index'),
	$model->id,
);

$this->menu=array(
	array('label'=>'List SkillDispositionDetail', 'url'=>array('index')),
	array('label'=>'Create SkillDispositionDetail', 'url'=>array('create')),
	array('label'=>'Update SkillDispositionDetail', 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>'Delete SkillDispositionDetail', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage SkillDispositionDetail', 'url'=>array('admin')),
);
?>

<h1>View SkillDispositionDetail #<?php echo $model->id; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'skill_id',
		'skill_disposition_id',
		'skill_disposition_detail_name',
		'description',
		'internal_notes',
		'external_notes',
		'date_created',
		'date_updated',
	),
)); ?>
