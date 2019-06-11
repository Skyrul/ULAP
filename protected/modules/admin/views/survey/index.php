<?php 
	$this->widget("application.components.AdminSideMenu",array(
		'active'=> Yii::app()->controller->id
	));
?>

<div class="page-header">
	<h1>
		Surveys
		<?php 
			if( Yii::app()->user->account->checkPermission('structure_survey_add_button','visible') )
			{
				echo CHtml::link('<i class="fa fa-plus"></i> Add Survey',array('create'),array('class'=>'btn btn-sm btn-primary')); 
			}
		?>
	</h1>
</div>

<?php $this->forward('/admin/survey/list',false); ?>
