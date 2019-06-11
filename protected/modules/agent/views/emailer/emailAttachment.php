<div class="row">
	<div class="form-group">
		<div class="col-sm-6">
		
			<table class="table table-striped table-condensed table-hover">	
				<tr>
					<th>Email Attachments</th>
					<th>Options</th>
				</tr>

					<?php 
						if( !empty($attachments ))
						{
							foreach( $attachments as $attachment )
							{
								?>
								<tr>
									<td><?php echo CHtml::link($attachment->fileUpload->original_filename, array('/site/download', 'file'=>$attachment->fileUpload->original_filename), array('target'=>'_blank')); ?> </td>
									
									
									<td>
										<?php
												//echo CHtml::link('<i class="fa fa-times"></i> Delete',array('skill/deleteEmailAttachment', 'id'=>$model->id, 'attachment_id'=>$attachment->id,'tab'=>'emailSettingAttachment'),array('class'=>'btn btn-xs btn-danger btn-minier', 'confirm'=>'Are you sure you want to delete this?')); 
												echo CHtml::link('<i class="fa fa-plus"></i> Add','javascript:void(0);',array('class'=>'btn btn-xs btn-success btn-minier btn-add-attachment','attachment_id'=> $attachment->id, 'fileupload_id'=> $attachment->fileupload_id, 'original_filename'=>$attachment->fileUpload->original_filename)); 
										?>
									</td>
									
								</tr>
							<?php
							}
						}
						else
						{
							echo '<tr><td colspan="2">No attachments</td></tr>';
						}
					?>
			
			</table>
		</div>
	</div>
</div>