<?php 

$this->widget("application.components.AdminSideMenu",array(
	'active'=> 'skill'
));
	
?>

<?php echo CHtml::link('Back to Dispositions', array('skillDisposition/index','skill_id' => $model->skill_id), array('class'=>'btn btn-default')); ?>
<br>

<div class="page-header">
	<h1>Update <small>&raquo; <?php echo $model->skill_disposition_name; ?></small> <button type="button" class="btn btn-primary btn-sm replacement-codes-modal"><i class="fa fa-search"></i> View Replacement Codes</button></h1>
</div>

<?php
    foreach(Yii::app()->user->getFlashes() as $key => $message) {
        echo '<div class="alert alert-' . $key . '">' . $message . "</div>\n";
    }
?>

<?php $this->renderPartial('_form', array(
	'model'=>$model,
)); ?>