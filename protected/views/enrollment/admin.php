<div class="wrapper">
	<div class="page-header">
		<h1>
			State Farm - Special Enrollment Customer
			<?php echo CHtml::link('Export <i class="fa fa-file-excel-o"></i>', array('enrollment/export','is_enrolled' => $is_enrolled), array('class'=>'btn btn-sm btn-yellow')); ?>
		</h1>
	</div>
	
	<div class="space-12"></div>
	
	<div class="tabbable tabs-left">
	
		<ul class="nav nav-tabs" id="yw1">
			<li class="<?php echo ($is_enrolled != 1) ? 'active' : ''; ?>"><?php echo CHtml::link('Pending Customer', array('enrollment/admin')); ?></li>
			<li class="<?php echo ($is_enrolled != 1) ? '' : 'active'; ?>"><?php echo CHtml::link('Enrolled Customer', array('enrollment/admin','is_enrolled' => 1)); ?></li>
		</ul>
		
		<div class="tab-content">
			<div class="tab-pane fade in active">
			
				<div class="row">	
					<div class="col-sm-10 col-sm-offset-1">
					
						<?php
							foreach(Yii::app()->user->getFlashes() as $key => $message) {
								echo '
						   <div class="alert alert-' . $key . '">
							<button data-dismiss="alert" class="close" type="button">
							 <i class="ace-icon fa fa-times"></i>
							</button>' . $message . "
						   </div>\n";
							}
						?>
						
								<div class="wide form">

								<?php $form=$this->beginWidget('CActiveForm', array(
									'action'=>Yii::app()->createUrl($this->route),
									'method'=>'get',
								)); ?>
								
								<div class="row">
									<?php echo $form->label($model,'lastname'); ?>
									<?php echo $form->textField($model,'lastname',array('size'=>40,'maxlength'=>120)); ?>
								</div>
								
								<div class="row">
									<?php echo $form->label($model,'firstname'); ?>
									<?php echo $form->textField($model,'firstname',array('size'=>40,'maxlength'=>120)); ?>
								</div>
								
								<div class="row">
									<?php echo $form->label($model,'custom_customer_id'); ?>
									<?php echo $form->textField($model,'custom_customer_id',array('size'=>40,'maxlength'=>120)); ?>
								</div>
								
									<?php echo CHtml::hiddenField('is_enrolled',$is_enrolled); ?>
							
	
								<div class="row buttons">
									<?php echo CHtml::submitButton('Search',array('class'=>'btn btn-sm btn-primary')); ?>
									<?php echo CHtml::button('Clear',array('class'=>'btn btn-sm btn-primary reset')); ?>
								</div>
	
								<?php $this->endWidget(); ?>

								</div><!-- search-form -->
								<br>
								<?php 
									$this->forward('enrollment/list/is_enrolled/'.$is_enrolled,false);
								?>
					</div>
				</div>
			</div>
		</div>
	</div>
	
</div>

<?php
Yii::app()->clientScript->registerScript('jsClear','
	$(".reset").click(function() {
		$(this).closest("form").find("input[type=text], textarea, select").val("");
	});
',CClientScript::POS_END);
?>