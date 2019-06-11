<div class="modal fade">
	<div class="modal-dialog" style="width:750px;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title blue">Add New Task</h4>
			</div>
			
			<div class="modal-body">
				<?php $form=$this->beginWidget('CActiveForm', array(
					'enableAjaxValidation'=>false,
					'htmlOptions' => array(
						'class' => 'form-horizontal',
					),
				)); ?>
				
					<?php echo $form->hiddenField($model, 'customer_success_kpi_id'); ?>
				
					<div class="profile-user-info profile-user-info-striped">
						
						<div class="profile-info-row lead-field-container">
							<div class="profile-info-name"> Task Name </div>

							<div class="profile-info-value">
								<?php echo $form->textField($model, 'task_name', array('class'=>'form-control')); ?>
							</div>
						</div>
						
						<div class="profile-info-row lead-field-container">
							<div class="profile-info-name"> Delay From Initial Days </div>

							<div class="profile-info-value">
								<?php echo $form->textField($model, 'delay_from_initial_days', array('class'=>'form-control')); ?>
							</div>
						</div>
						
						<div class="profile-info-row lead-field-container">
							<div class="profile-info-name"> Starting Priority </div>

							<div class="profile-info-value">
								<?php echo $form->textField($model, 'starting_priority', array('class'=>'form-control')); ?>
							</div>
						</div>
						
						<div class="profile-info-row lead-field-container">
							<div class="profile-info-name"> Priority Add </div>

							<div class="profile-info-value">
								<?php echo $form->textField($model, 'priority_add', array('class'=>'form-control')); ?>
							</div>
						</div>
						
						<div class="profile-info-row lead-field-container">
							<div class="profile-info-name"> Max Priority </div>

							<div class="profile-info-value">
								<?php echo $form->textField($model, 'max_priority', array('class'=>'form-control')); ?>
							</div>
						</div>
						
						<div class="profile-info-row lead-field-container">
							<div class="profile-info-name"> Sends Email </div>

							<div class="profile-info-value">
								<?php echo $form->dropDownList($model, 'sends_email', array(1=>'Yes', 0=>'No'), array('class'=>'form-control', 'style'=>'width:auto;')); ?>
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