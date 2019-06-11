<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title blue"><?php echo $model->name; ?> </h4>
			</div>
			
			<div class="modal-body">
				<div class="form">
					<?php $form=$this->beginWidget('CActiveForm', array(
						'enableAjaxValidation'=>false,
						'htmlOptions' => array(
							'class' => 'form-horizontal',
						),
					)); ?>
					
						<div class="profile-user-info profile-user-info-striped">
							<div class="profile-info-row">
								<div class="profile-info-name"> Number <span class="required">*</span></div>

								<div class="profile-info-value">
									<?php echo $form->textField($model, 'phone_number', array('class'=>'form-control')); ?>
								</div>
							</div>
						</div>
						
						<div class="profile-user-info profile-user-info-striped">
							<div class="profile-info-row">
								<div class="profile-info-name"> Name <span class="required">*</span></div>

								<div class="profile-info-value">
									<?php echo $form->textField($model, 'name', array('class'=>'form-control')); ?>
								</div>
							</div>
						</div>

						<div class="space-12"></div>
					
					<?php $this->endWidget(); ?>
				</div>
			</div>
			
			<div class="modal-footer center">
				<button class="btn btn-sm btn-primary" data-action="save">
					<i class="ace-icon fa fa-check"></i>
					Save
				</button>
			</div>
		</div>
	</div>
</div>