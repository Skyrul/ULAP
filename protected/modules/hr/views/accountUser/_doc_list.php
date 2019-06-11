<?php if ($index == 0): ?>

<thead>
	<th>Type</th>
	<th>File Name</th>
	<th>Date/Time</th>
	
	<?php if( Yii::app()->user->account->checkPermission('employees_employee_file_delete_button','visible') ){ ?>
	
	<th class="center">Options</th>
	
	<?php } ?>
</thead>

<?php endif; ?>

<tr>
	<td>
		<?php 
			if( $data->type_id == '' || $data->type_id == null || $data->docType->show_edit_button == 1 )
			{
				echo CHtml::dropDownList('AccountUserNoteFile[type]', $data->type_id, AccountUserDocumentType::items(), array('id' => $data->id, 'class'=>'document-type-select', 'prompt'=>'- TYPE -'));
			}
			else
			{
				echo $data->docType->name;
			}
		?>
	</td>
	
	<td>
		<?php 
			if( Yii::app()->user->account->checkPermission('employees_employee_file_download_link','visible') )
			{
				echo CHtml::link($data->fileUpload->original_filename, array('/site/download', 'file'=>$data->fileUpload->generated_filename), array('target'=>'_blank')); 
			}
			else
			{
				echo $data->fileUpload->original_filename;
			}
		?>
	</td>
	
	<td>
		<?php
			$date = new DateTime($data->date_created, new DateTimeZone('America/Chicago'));

			$date->setTimezone(new DateTimeZone('America/Denver'));

			echo $date->format('m/d/Y g:i A');
		?>
	</td>
	
	<td class="center">	
		<?php if( $data->docType->show_delete_button == 1 && Yii::app()->user->account->checkPermission('employees_employee_file_delete_button','visible') ){ ?>
			<button id="<?php echo $data->id; ?>" class="btn btn-danger btn-minier document-delete-btn"><i class="fa fa-times"></i> Delete</button>
		<?php } ?>
	</td>
	
</tr>

