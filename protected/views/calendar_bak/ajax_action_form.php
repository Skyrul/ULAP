<div class="modal fade">
	<div class="modal-dialog" style="width:750px;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title blue">
					<i class="fa fa-calendar"></i> 
					<?php 
						if( $model->title == 'APPOINTMENT SET' && $model->lead_id != null )
						{
							$title =  $model->lead->first_name.' '.$model->lead->last_name;
						}
						else
						{
							$title = 'Calendar Action Form';
						}
						
						echo $title.' - '.$currentDateSelected; 
					?>
				</h4>
			</div>
			
			<div class="modal-body">
				<?php $form=$this->beginWidget('CActiveForm', array(
					'enableAjaxValidation'=>false,
					'htmlOptions' => array(
						'class' => 'form-horizontal',
					),
				)); ?>
				
					<?php echo $form->hiddenField($model, 'id'); ?>
				
					<?php 
						$page = str_replace(' ', '_', strtolower($model->title));
						
						$this->renderPartial('actionForms/'.$viewer.'/'.$page, array(
							'form' => $form,
							'viewer' => $viewer,
							'model' => $model,
							'calendar' => $calendar,
							'existingEvent' => $existingEvent,
						));
					?>
				
				<?php $this->endWidget(); ?>
			</div>
			
			<div class="modal-footer hide"></div>
		</div>
	</div>
</div>