<?php
/* @var $this SkillDispositionDetailController */
/* @var $model SkillDispositionDetail */

// $this->breadcrumbs=array(
	// 'Skill Disposition Details'=>array('index'),
	// $model->id=>array('view','id'=>$model->id),
	// 'Update',
// );

$this->widget("application.components.AdminSideMenu",array(
		'active'=> 'skill'
	));
?>
<div class="page-header">
	<h1>
		Update <small>&raquo; <?php echo $model->skill_child_disposition_detail_name; ?></small>
		</small> <button type="button" class="btn btn-primary btn-sm replacement-codes-modal"><i class="fa fa-search"></i> View Replacement Codes</button>
	</h1>
</div>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>