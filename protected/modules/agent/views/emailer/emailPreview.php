<div style="width:650px;">
	<div>Subject: <?php echo $set->getReplacementCodeValues($lead, $set->subject, $personal_note); ?></div>
	<div>
		<?php echo $set->getReplacementCodeValues($lead, $set->htmlContent, $personal_note); ?>
	</div>
	
	
	<div class="clearfix"></div>
			
	<div class="row">
		<div class="col-sm-10">
			<div id="sources">
				<span class="filelist">
					<?php 
						if( $attachments )
						{
							foreach( $attachments as $attachment )
							{
								echo '
									<span class="label label-white label-inverse">						
										<span class="filename" title="'.$attachment->fileUpload->original_filename.'">'.CHtml::link($attachment->fileUpload->original_filename, array('/site/download', 'file'=>$attachment->fileUpload->original_filename), array('target'=>'_blank')).'</span>						
										<span class="percentage"></span>						
										<a href="javascript:void(0);" id="'.$attachment->id.'" class="existing-remove-file-link"></a>					
									</span>
								';
	
							}
						}
					?>
				</span>
			</div>
			
		</div>
	</div>
			
</div>