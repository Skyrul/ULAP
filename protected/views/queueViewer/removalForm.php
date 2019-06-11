<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title blue">
					<i class="fa fa-user"></i> 
					Customer Queue Remove - <?php echo $model->customer->getFullName(); ?>
				</h4>
			</div>
			
			<div class="modal-body">
				<?php $form=$this->beginWidget('CActiveForm', array(
					'enableAjaxValidation'=>false,
					'htmlOptions' => array(
						'class' => 'form-horizontal',
					),
				)); ?>
				
				<div class="profile-info-row agent-field-container">
					<div class="profile-info-name"> Start Date </div>

					<div class="profile-info-value">
						<?php 
							echo $form->textField($model, 'removal_start_date', array('class'=>'datepicker')); 
						?>
					</div>
				</div>
				
				<div class="profile-info-row agent-field-container">
					<div class="profile-info-name"> End Date </div>

					<div class="profile-info-value">
						<?php 
							echo $form->textField($model, 'removal_end_date', array('class'=>'datepicker')); 
						?>
					</div>
				</div>
				
				<div class="space-12"></div>
					
				<div class="center">
					<button type="button" class="btn btn-sm btn-info" data-action="save">Save</button>
				</div>
				
				<?php $this->endWidget(); ?>
			</div>
			
			<div class="modal-footer hide"></div>
		</div>
	</div>
</div>