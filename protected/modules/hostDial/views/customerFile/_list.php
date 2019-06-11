
<?php if($index == 0): ?>

<thead>
	<th width="12%">Date/Time</th>
	<th>File Name</th>
	
	<?php if( Yii::app()->user->account->checkPermission('customer_my_files_delete_button','visible') ){ ?>
	<th width="5%"></th>
	<?php } ?>
</thead>

<?php endif; ?>

<tr>
	<td>
		<?php 
			$date = new DateTime($data->date_created, new DateTimeZone('America/Chicago'));

			$date->setTimezone(new DateTimeZone('America/Denver'));
	
			echo $date->format('m/d/Y g:i A'); 
		?>
	</td>

	<td>
		<?php 
			if( isset($data->fileUpload) ) 
			{
				if( Yii::app()->user->account->checkPermission('customer_my_files_download_link','visible') )
				{
					if( file_exists(Yii::getPathOfAlias('webroot') . '/fileupload/' . $data->fileUpload->generated_filename) )
					{
						echo CHtml::link($data->fileUpload->original_filename, array('/site/download', 'file'=>$data->fileUpload->generated_filename, 'customerFileId' => $data->id), array('target'=>'_blank'));
					}
					else
					{
						echo $data->fileUpload->original_filename . ' - File upload failed please retry the file'; 
					}
				}
				else
				{
					if( file_exists(Yii::getPathOfAlias('webroot') . '/fileupload/' . $data->fileUpload->generated_filename) )
					{
						echo $data->fileUpload->original_filename;
					}
					else
					{
						echo $data->fileUpload->original_filename . ' - File upload failed please retry the file'; 
					}
				}
			}
		?>
	</td>
	
	<?php if( Yii::app()->user->account->checkPermission('customer_my_files_delete_button','visible') ){ ?>
	<td>
		<?php
			echo CHtml::link('<i class="fa fa-times"></i> Delete', array('delete', 'id'=>$data->id, 'customerId'=>$customerId), array('class'=>'btn btn-minier btn-danger', 'confirm'=>'Are you sure you want to delete this?'));
		?>
	</td>
	<?php } ?>

</tr>