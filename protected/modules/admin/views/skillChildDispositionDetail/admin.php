<?php
/* @var $this SkillDispositionDetailController */
/* @var $model SkillDispositionDetail */

$this->breadcrumbs=array(
	'Skill Disposition Details'=>array('index'),
	'Manage',
);

$this->menu=array(
	array('label'=>'List SkillDispositionDetail', 'url'=>array('index')),
	array('label'=>'Create SkillDispositionDetail', 'url'=>array('create')),
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$('#skill-disposition-detail-grid').yiiGridView('update', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Manage Skill Disposition Details</h1>

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
	'id'=>'skill-disposition-detail-grid',
	'dataProvider'=>$model->search(),
	'filter'=>$model,
	'columns'=>array(
		'id',
		'skill_id',
		'skill_disposition_id',
		'skill_disposition_detail_name',
		'description',
		'internal_notes',
		/*
		'external_notes',
		'date_created',
		'date_updated',
		*/
		array(
			'class'=>'CButtonColumn',
		),
	),
)); ?>
