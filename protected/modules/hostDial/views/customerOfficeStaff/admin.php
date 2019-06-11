<?php
/* @var $this CustomerOfficeStaffController */
/* @var $model CustomerOfficeStaff */

$this->breadcrumbs=array(
	'Customer Office Staff'=>array('index'),
	'Manage',
);

$this->menu=array(
	array('label'=>'List CustomerOfficeStaff', 'url'=>array('index')),
	array('label'=>'Create CustomerOfficeStaff', 'url'=>array('create')),
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$('#customer-office-staff-grid').yiiGridView('update', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Manage Customer Office Staff</h1>

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
	'id'=>'customer-office-staff-grid',
	'dataProvider'=>$model->search(),
	'filter'=>$model,
	'columns'=>array(
		'id',
		'customer_id',
		'customer_office_id',
		'staff_name',
		'email_address',
		'position',
		/*
		'is_received_email',
		'is_portal_access',
		'phone',
		'mobile',
		'fax',
		'status',
		'is_deleted',
		'date_created',
		'date_updated',
		*/
		array(
			'class'=>'CButtonColumn',
		),
	),
)); ?>
