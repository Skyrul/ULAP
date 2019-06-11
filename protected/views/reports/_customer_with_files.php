<?php if( $index == 0 ): ?>

<thead>
	<th>Date/Time</th>
	<th>File</th>
	<th>Date Downloaded</th>
	<th>Customer Name</th>
	<th>Customer ID</th>
	<th>Company</th>
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
			$downloaded = CustomerHistory::model()->find(array(
				'condition' => 'model_id = :model_id AND type = :type',
				'params' => array(
					':model_id' => $data->id,
					':type' => CustomerHistory::TYPE_DOWNLOADED,
				),
				'order' => 'date_created DESC',
			));
			
			if( $downloaded )
			{
				$dateDownloaded = new DateTime($downloaded->date_created, new DateTimeZone('America/Chicago'));

				$dateDownloaded->setTimezone(new DateTimeZone('America/Denver'));
		
				echo $dateDownloaded->format('m/d/Y g:i A');
			}
		?>
	</td>

	<td><?php echo CHtml::link($data->customer->firstname . ', '. $data->customer->lastname, array('/customer/customerFile/index', 'customer_id'=>$data->customer_id));  ?></td>
	
	<td>
		<?php 
			if( isset($data->customer) )
			{
				echo $data->customer->custom_customer_id;
			}
		?>
	</td>
	
	<td>
		<?php 
			if( isset($data->customer->company) )
			{
				echo $data->customer->company->company_name;
			}
		?>
	</td>
	
</tr>