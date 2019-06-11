<div class="page-header">
	<h1>
		Time Keeping
		<div class="hidden-sm hidden-md hidden-lg"><div class="space space-8"></div></div>
		<?php echo CHtml::link('<i class="fa fa-plus"></i> Add Schedule Change Request',array('create'),array('class'=>'btn btn-sm btn-primary')); ?>
	</h1>
</div>


<?php
    foreach(Yii::app()->user->getFlashes() as $key => $message) {
        echo '<div class="flash-' . $key . '">' . $message . "</div>\n";
    }
?>

<?php $this->forward('/employee/timeKeeping/list',false); ?>