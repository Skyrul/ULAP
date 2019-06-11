<div class="modal fade">
	<div class="modal-dialog" style="width:50%;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title blue">
					<?php echo '<i class="fa fa-file"></i> Edit File: ' . $model->name; ?>
				</h4>
			</div>
			
			<div class="modal-body">
				<?php $form=$this->beginWidget('CActiveForm', array(
					'enableAjaxValidation'=>false,
					'htmlOptions' => array('class' => 'form-horizontal'),
				)); ?>
				
					<?php echo $form->hiddenField($model, 'id'); ?>
				
					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row agent-field-container">
							<div class="profile-info-name">Name <span class="red">*</span></div>

							<div class="profile-info-value">
								<?php 
									echo $form->textField($model, 'name', array('class'=>'col-xs-12')); 
								?>
							</div>
						</div>
					</div>
					
					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name">Order <span class="red">*</span></div>

							<div class="profile-info-value">
								<?php 
									$modelCount = CompanyLearningCenterCategory::model()->count(array(
										'condition'=>'company_id = :company_id AND status != 3',
										'params' => array(
											':company_id' => $model->company_id
										),
									));
									
									$modelCount = $modelCount + 1;
									
									for( $ctr=1; $ctr<=$modelCount; $ctr++ )
									{
										$sortOptions[$ctr] = $ctr; 
									}
									
									$model->sort_order = $modelCount;
									
									echo $form->dropDownList($model, 'sort_order', $sortOptions, array('style'=>'width:auto;')); 
								?>
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