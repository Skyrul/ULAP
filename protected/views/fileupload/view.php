<?php
/* @var $this FileuploadController */
/* @var $model Fileupload */

$this->breadcrumbs=array(
	'Fileuploads'=>array('index'),
	$model->id,
);

$this->menu=array(
	array('label'=>'List Fileupload', 'url'=>array('index')),
	array('label'=>'Create Fileupload', 'url'=>array('create')),
	array('label'=>'Update Fileupload', 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>'Delete Fileupload', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage Fileupload', 'url'=>array('admin')),
);
?>

<h1>View Fileupload #<?php echo $model->id; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'original_filename',
		'generated_filename',
		'date_created',
	),
)); ?>
