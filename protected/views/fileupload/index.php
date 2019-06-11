<?php
/* @var $this FileuploadController */
/* @var $dataProvider CActiveDataProvider */

$this->breadcrumbs=array(
	'Fileuploads',
);

$this->menu=array(
	array('label'=>'Create Fileupload', 'url'=>array('create')),
	array('label'=>'Manage Fileupload', 'url'=>array('admin')),
);
?>

<h1>Fileuploads</h1>

<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
)); ?>
