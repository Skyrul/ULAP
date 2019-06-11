<div class="modal fade">
	<div class="modal-dialog" style="width:50%;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title blue">
					<?php 
						if( $model->type == 1 )
						{
							echo '<i class="fa fa-file-movie-o"></i> Update ' . $model->title;
						}
						elseif( $model->type == 2 )
						{
							echo '<i class="fa fa-file-audio-o"></i> Update ' . $model->title;
						}
						else
						{
							echo '<i class="fa fa-file-o"></i> Update ' . $model->title;
						}
					?>
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
				
					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row agent-field-container">
							<div class="profile-info-name">Title <span class="red">*</span></div>

							<div class="profile-info-value">
								<?php 
									echo $form->textField($model, 'title', array('class'=>'col-xs-12')); 
								?>
							</div>
						</div>
						
						<div class="profile-info-row agent-field-container">
							<div class="profile-info-name">Description</div>

							<div class="profile-info-value">
								<?php 
									echo $form->textArea($model, 'description', array('class'=>'col-xs-12')); 
								?>
							</div>
						</div>
						
						<div class="profile-info-row agent-field-container">
							<div class="profile-info-name">Order <span class="red">*</span></div>

							<div class="profile-info-value">
								<?php 
									$modelCount = TrainingLibraryFile::model()->count() + 1;
									
									for( $ctr=1; $ctr<=$modelCount; $ctr++ )
									{
										$sortOptions[$ctr] = $ctr; 
									}
									
									echo $form->dropDownList($model, 'sort_order', $sortOptions, array('style'=>'width:auto;')); 
								?>
							</div>
						</div>
						
						<div class="profile-info-row agent-field-container">
							<div class="profile-info-name">Security Groups</div>

							<div class="profile-info-value">
								<?php 
									$securityGroups = Account::listAccountType();
									
									if( $securityGroups )
									{
										$securityGroupsHalved = array_chunk($securityGroups, ceil(count($securityGroups)/2), true);
										
										$checkedSecurityGroups = array();
										
										if( $model->security_groups != null )
										{
											$checkedSecurityGroups = explode(',', $model->security_groups);
										}
										
										echo '<div class="col-sm-3">';
											foreach( $securityGroupsHalved[0] as $securityGroupId => $securityGroupName )
											{
												$checked = (!empty($checkedSecurityGroups) && in_array($securityGroupId, $checkedSecurityGroups)) ? 'checked' : '';
												
												echo '<div class="checkbox">';
													echo '<label>';
														echo '<input name="TrainingLibraryFile[security_groups]" class="ace security-group-checkbox" type="checkbox" value="'.$securityGroupId.'" '.$checked.'>';
														echo '<span class="lbl"> '.$securityGroupName.'</span>';
													echo '</label>';
												echo '</div>';
											}
										echo '</div>';
										
										echo '<div class="col-sm-3">';
											foreach( $securityGroupsHalved[1] as $securityGroupId => $securityGroupName )
											{
												$checked = (!empty($checkedSecurityGroups) && in_array($securityGroupId, $checkedSecurityGroups)) ? 'checked' : '';
												
												echo '<div class="checkbox">';
													echo '<label>';
														echo '<input name="TrainingLibraryFile[security_groups]" class="ace security-group-checkbox" type="checkbox" value="'.$securityGroupId.'" '.$checked.'>';
														echo '<span class="lbl"> '.$securityGroupName.'</span>';
													echo '</label>';
												echo '</div>';
											}
										echo '</div>';
									}
				
									echo $form->error($model, 'security_groups');
								?>
							</div>
						</div>
						
						<div class="profile-info-row agent-field-container">
							<div class="profile-info-name">File <span class="red">*</span></div>

							<div class="profile-info-value">
								<?php 
									echo $form->fileField($model, 'fileupload_id');
									
									echo '<p class="help-block red">';
										if( $model->type == 1 )
										{
											echo 'Formats allowed are: mp4, avi';
										}
										elseif( $model->type == 2 )
										{
											echo 'Formats allowed are: wav, mp3, aiff';
										}
										else
										{
											echo 'Formats allowed are: doc, docx, xls, xlsx, pdf, ppt, pptx, jpg, tiff, bmp';
										}
									echo '</p>';
									
									if( isset($model->fileUpload) )
									{
										echo '<p>';
											echo CHtml::link('<i class="fa fa-download"></i> '.$model->fileUpload->original_filename, array('download', 'file'=>$model->fileUpload->generated_filename), array('target'=>'_blank'));
										echo '</p>';
									}
									
									echo $form->error($model, 'fileupload_id');
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