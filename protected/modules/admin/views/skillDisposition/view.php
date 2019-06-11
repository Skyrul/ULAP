<h1>View Skill Disposition #<?php echo $model->id; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'skill_id',
		'skill_disposition_name',
		'description',
		'is_voice_contact',
		'retry_interval',
		'is_complete_leads',
		'is_send_email',
	),
)); ?>
