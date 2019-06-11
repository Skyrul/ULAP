<?php
	$filePath = Yii::app()->request->baseUrl . '/learningCenterFiles/' . $model->fileUpload->generated_filename;
				
	$extension = pathinfo($filePath, PATHINFO_EXTENSION);
?>

<div class="modal fade">
	<div class="modal-dialog" style="<?php echo in_array($extension, array('mp4', 'avi')) ? 'width:60%' : 'width:30%; margin-top:9%;'; ?>">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title blue">
					<?php echo '<i class="fa fa-file"></i> ' . $model->title; ?>
				</h4>
			</div>
			
			<div class="modal-body">
				<?php		
					if( in_array($extension, array('mp4', 'avi')) )
					{
					?>
						<div style="text-align:center">
							<video style="width:100%;" autoplay controls>
								<source src="<?php echo $filePath; ?>" type="video/<?php echo $extension; ?>">
								Your browser does not support the video tag.
							</video> 
						</div>
					<?php					
					}
					else
					{
					?>
						<div style="text-align:center">
							<audio autoplay controls>
								<source src="<?php echo $filePath; ?>" type="audio/<?php echo $extension; ?>">
								Your browser does not support the audio element.
							</audio> 
						</div>
					<?php
					}
				?>
			</div>
			
			<div class="modal-footer hide"></div>
		</div>
	</div>
</div>