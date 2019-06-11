<?php 
	$this->widget("application.components.AdminSideMenu",array(
		'active'=> 'stateSchedule'
	));
?>

<div class="page-header">
	<h1>
		State Schedule 
		<small>
			<i class="ace-icon fa fa-angle-double-right"></i>
			<?php echo $state->name.' ('.$state->abbreviation.')'; ?>
		</small>
	</h1>
</div>

<?php $this->renderPartial('_form', array(
	'state'=>$state,
	'model'=>$model,
)); ?>