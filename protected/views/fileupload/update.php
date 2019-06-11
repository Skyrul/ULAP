<?php
/* @var $this FileuploadController */
/* @var $model Fileupload */

$this->breadcrumbs=array(
	'Fileuploads'=>array('index'),
	$model->id=>array('view','id'=>$model->id),
	'Update',
);

$this->menu=array(
	array('label'=>'List Fileupload', 'url'=>array('index')),
	array('label'=>'Create Fileupload', 'url'=>array('create')),
	array('label'=>'View Fileupload', 'url'=>array('view', 'id'=>$model->id)),
	array('label'=>'Manage Fileupload', 'url'=>array('admin')),
);
?>

<h1>Update Fileupload <?php echo $model->id; ?></h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>