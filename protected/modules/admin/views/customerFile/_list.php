
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
			$date = new DateTime($data->date_created, new DateTimeZone('America/New_York'));

			$date->setTimezone(new DateTimeZone('America/Denver'));
	
			echo $date->format('m/d/Y g:i A'); 
		?>
	</td>

	<td>
		<?php 
			if( isset($data->fileUpload) )
			{
				echo CHtml::link($data->fileUpload->original_filename, array('/site/download', 'file'=>$data->fileUpload->original_filename,'CompanyFile[id]'=>$data->id), array('target'=>'_blank'));
			}
		?>
	</td>
	
	<td>
		<?php
			echo CHtml::link('<i class="fa fa-times"></i> Delete', array('delete', 'id'=>$data->id, 'companyId'=>$companyId), array('class'=>'btn btn-minier btn-danger', 'confirm'=>'Are you sure you want to delete this?'));
		?>
	</td>

</tr>