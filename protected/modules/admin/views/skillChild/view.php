<h1>View Skill Child <small><?php echo $model->child_name; ?></small></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'skill_id',
		'child_name',
		'description',
		'is_language',
		'language',
		'is_reminder_call',
		'status',
		'is_deleted',
		'date_created',
		'date_updated',
	),
)); ?>
