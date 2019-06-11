<div class="modal fade">
	<div class="modal-dialog" style="width:50%;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title blue">
					<?php echo '<i class="fa fa-file"></i> Add a File'; ?>
				</h4>
			</div>
			
			<div class="modal-body">
				<?php $form=$this->beginWidget('CActiveForm', array(
					'enableAjaxValidation'=>false,
					'htmlOptions' => array(
						'class' => 'form-horizontal',
						'enctype' => 'multipart/form-data'
					),
				)); ?>
				
					<?php echo $form->hiddenField($model,'company_id',array('value'=>$_POST['company_id'])); ?>
					<?php echo $form->hiddenField($model,'category_id',array('value'=>$_POST['category_id'])); ?>
				
					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name">Title <span class="red">*</span></div>

							<div class="profile-info-value">
								<?php 
									echo $form->textField($model, 'title', array('class'=>'col-xs-12')); 
								?>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name">Description <span class="red">*</span></div>

							<div class="profile-info-value">
								<?php 
									echo $form->textArea($model, 'description', array('class'=>'col-xs-12')); 
								?>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name">Order <span class="red">*</span></div>

							<div class="profile-info-value">
								<?php 
									$modelCount = CompanyLearningCenterFile::model()->count(array(
										'condition'=>'category_id = :category_id AND status=1',
										'params' => array(
											':category_id' => $_POST['category_id']
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
						
						<div class="profile-info-row">
							<div class="profile-info-name">File <span class="red">*</span></div>

							<div class="profile-info-value">
								<?php 
									echo $form->fileField($model, 'fileupload_id');
				
									echo $form->error($model, 'fileupload_id');
								?>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name">Thumbnail </div>

							<div class="profile-info-value">
								<?php 
									echo $form->fileField($model, 'thumbnail_fileupload_id');
									
									echo '<small class="red">Recommended image size is 196 x 110</small>';
				
									echo $form->error($model, 'thumbnail_fileupload_id');
								?>
							</div>
						</div>
					</div>
					
					<div class="space-12"></div>
					
					<div class="center">
						<button type="button" class="btn btn-sm btn-info" data-action="save" fileType="<?php echo $model->type; ?>">Save</button>
					</div>
				
				<?php $this->endWidget(); ?>
			</div>
			
			<div class="modal-footer hide"></div>
		</div>
	</div>
</div>