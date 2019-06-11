<div class="profile-activity clearfix">
	<div class="row-fluid">
	
		<?php /*<div class="col-sm-3">Type: <?php echo $data->getType(); ?></div>*/ ?>
		
		<div class="col-sm-4">Category: <?php echo $data->getCategory(); ?></div>
		
		<div class="col-sm-4">User: <?php echo $data->account->accountUser->first_name.' '.$data->account->accountUser->last_name; ?></div>
		
		<div class="col-sm-4">
			Date: 
			<?php
				$date = new DateTime($data->date_created, new DateTimeZone('America/Chicago'));

				$date->setTimezone(new DateTimeZone('America/Denver'));

				echo $date->format('m/d/Y g:i A');
			?>
		</div>	
	</div>
	
	<br />
	<br />
	<br />
	
	<div class="row-fluid" style="margin-left:40px;">
		<?php 
			if( stristr($data->content, '<b>Password</b> changed from') !== FALSE )
			{
				echo 'Changed Password';
			}
			else
			{
				echo $data->content; 
			}
		?>
	</div>
	
	<div class="row-fluid">
		<br />
		<div class="col-sm-12">
			<?php 
				$fileAttachments = AccountUserNoteFile::model()->findAll(array(
					'condition' => 'note_id = :note_id',
					'params' => array(
						':note_id' => $data->id,
					),
				));
				
				if( $fileAttachments )
				{
					$fileAttachmentHtml = '';
						
					foreach( $fileAttachments as $fileAttachment )
					{
						$fileAttachmentHtml .= CHtml::link($fileAttachment->fileUpload->original_filename, array('/site/download', 'file'=>$fileAttachment->fileUpload->generated_filename), array('target'=>'_blank')) . ', ';
					}
					
					echo 'Attachments: ' . rtrim($fileAttachmentHtml, ', ');
				}
			?>
		</div>
	</div>
</div>