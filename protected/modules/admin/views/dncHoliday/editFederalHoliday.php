<div class="modal fade">
	<div class="modal-dialog" style="width:750px;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title blue">Edit <?php echo $model->name; ?></h4>
			</div>
			
			<div class="modal-body">
				<?php $form=$this->beginWidget('CActiveForm', array(
					'enableAjaxValidation'=>false,
					'htmlOptions' => array(
						'class' => 'form-horizontal',
					),
				)); ?>
				
					<div class="profile-user-info profile-user-info-striped">
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Holiday </div>

							<div class="profile-info-value">
								<?php echo $form->textField($model, 'name', array('class'=>'form-control')); ?>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Date</div>

							<div class="profile-info-value">
								<?php echo $form->textField($model, 'date', array('class'=>'form-control datepicker')); ?>
							</div>
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