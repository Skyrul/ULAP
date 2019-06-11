<?php 
	$this->pageTitle = 'Engagex - Text Reply';
?>

<div class="">				
	<div style="clear:both;"></div>
</div>

<br />

<div class="tabbable tabs-left">
	<div class="tab-content">
		<div class="tab-pane in active">
			<?php
				foreach(Yii::app()->user->getFlashes() as $key => $message) {
					echo '<button type="button" class="close" data-dismiss="alert">
								<i class="ace-icon fa fa-times"></i>
							</button>';
					echo '<div class="alert alert-block alert-' . $key . '"><i class="ace-icon fa fa-check green"></i> ' . $message . "</div>\n";
				}
			?>

			<div class="page-header">
				<h1>Reply Note</h1>
			</div>

			<div class="form">
				<p><?php echo $leadCallHistory->lead->getFullName().' - '.$leadCallHistory->disposition; ?></p>
				<div class="row">
					<div class="col-sm-12">
					
						<?php $form=$this->beginWidget('CActiveForm', array(
							'enableAjaxValidation'=>false,
							'htmlOptions' => array(
								'class' => 'form-horizontal',
							),
						)); ?>
						
							<?php echo $form->hiddenField($model, 'customer_id'); ?>
							<?php echo $form->hiddenField($model, 'lead_id'); ?>
							<?php echo $form->hiddenField($model, 'lead_call_history_id'); ?>
							<?php echo $form->hiddenField($model, 'lead_phone_number'); ?>
						
							<div class="row-fluid">
								<?php echo $form->textArea($model, 'reply_note', array('class'=>'form-control', 'style'=>'height:200px;')); ?>
								<?php echo $form->error($model, 'reply_note'); ?>
							</div>
							
							<div class="space-12"></div>
							
							<div class="center">
								<button type="submit" class="btn btn-sm btn-info" style="width:100px;">Send</button>
							</div>
						
						<?php $this->endWidget(); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>