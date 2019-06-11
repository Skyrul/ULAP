<div class="modal fade">
	<div class="modal-dialog" style="width:50%;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title blue">
					<?php echo '<i class="fa fa-file"></i> Edit File: ' . $model->title; ?>
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
				
					<?php echo $form->hiddenField($model, 'id'); ?>
					<?php echo $form->hiddenField($model,'company_id'); ?>
				
					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name">Category <span class="red">*</span></div>

							<div class="profile-info-value">
								<?php 
									echo $form->dropDownList($model, 'category_id', $model->getCategories($model->company_id), array('style'=>'width:auto;')); 
								?>
							</div>
						</div>
						
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
											':category_id' => $model->category_id
										),
									));
									
									$modelCount = $modelCount + 1;
									
									for( $ctr=1; $ctr<=$modelCount; $ctr++ )
									{
										$sortOptions[$ctr] = $ctr; 
									}
									
									echo $form->dropDownList($model, 'sort_order', $sortOptions, array('style'=>'width:auto;')); 
								?>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name">File <span class="red">*</span></div>

							<div class="profile-info-value">
								<?php 
									echo $form->fileField($model, 'fileupload_id');
															
									if( isset($model->fileUpload) )
									{
										echo '<br />';
										
										echo '<p>';
											echo CHtml::link('<i class="fa fa-download"></i> '.$model->fileUpload->original_filename, array('download', 'file'=>$model->fileUpload->generated_filename), array('target'=>'_blank'));
										echo '</p>';
									}
									
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
													
									if( isset($model->thumbnailFileUpload) )
									{
										echo '<br />';
										echo '<p><img src="'.Yii::app()->request->baseUrl.'/learningCenterFiles/thumbnails/'.$model->thumbnailFileUpload->generated_filename.'" style="width:196px;height:110px;"></p>';
									}
									
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