
<?php if($index == 0): ?>

<thead>
	<th width="12%">Date/Time</th>
	<th>File Name</th>
	<th width="5%"></th>
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
				echo CHtml::link($data->fileUpload->original_filename, array('/site/download', 'file'=>$data->fileUpload->original_filename, 'customerFileId' => $data->id), array('target'=>'_blank'));
			}
		?>
	</td>
	
	<td>
		<?php
			echo CHtml::link('Select', 'javascript:void(0)', array('class'=>'btn btn-minier btn-success selected-customer-file', 'data-fileupload_id'=>$data->fileUpload->id,'data-fileupload_title'=>$data->fileUpload->original_filename));
		?>
	</td>

</tr>