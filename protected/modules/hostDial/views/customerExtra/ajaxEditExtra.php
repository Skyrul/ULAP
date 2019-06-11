<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title blue"><?php echo $model->description; ?> </h4>
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
								<div class="profile-info-name"> Description <span class="required">*</span></div>

								<div class="profile-info-value">
									<?php echo $form->textField($model, 'description', array('class'=>'form-control')); ?>
								</div>
							</div>
						</div>
						
						<div class="profile-user-info profile-user-info-striped">
							<div class="profile-info-row">
								<div class="profile-info-name"> Quantity <span class="required">*</span></div>

								<div class="profile-info-value">
									<?php echo $form->textField($model, 'quantity', array('class'=>'form-control')); ?>
								</div>
							</div>
						</div>
						
						<div class="profile-user-info profile-user-info-striped">
							<div class="profile-info-row">
								<div class="profile-info-name"> Year <span class="required">*</span></div>

								<div class="profile-info-value">
									<?php echo $form->textField($model, 'year', array('class'=>'form-control')); ?>
								</div>
							</div>
						</div>
						
						<div class="profile-user-info profile-user-info-striped">
							<div class="profile-info-row">
								<div class="profile-info-name"> Month <span class="required">*</span></div>

								<div class="profile-info-value">									
									<?php echo $form->dropDownList($model,'month', CustomerCreditCard::cardExpirationMonths(), array('class'=>'form-control', 'style'=>'width:auto;')); ?>
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