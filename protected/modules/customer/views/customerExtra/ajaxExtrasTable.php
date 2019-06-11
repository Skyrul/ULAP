<?php 
	if( $customerExras ) 
	{
		foreach( $customerExras as $customerExra )
		{
			$date = new DateTime($customerExra->date_created, new DateTimeZone('America/Chicago'));

			$date->setTimezone(new DateTimeZone('America/Denver'));
			
			echo '<tr>';
				echo '<td>'.$customerExra->description.'</td>';
				
				echo '<td>'.$date->format('m/d/Y g:i A').'</td>';

				echo '<td>'.$customerExra->year.'</td>';
				
				echo '<td>'.date('F', mktime(0, 0, 0, $customerExra->month, 10)).'</td>';
				
				echo '<td>'.$customerExra->quantity.'</td>';
				
				echo '<td class="center">';
					echo '<button id="'.$customerExra->id.'" class="btn btn-info btn-minier btn-edit-extra"><i class="fa fa-pencil"></i> Edit</button>';
					echo '<button id="'.$customerExra->id.'" style="margin-left:5px;"class="btn btn-danger btn-minier btn-delete-extra"><i class="fa fa-times"></i> Delete</button>';
				echo '</td>';
			echo '</tr>';
		}
	}
	else
	{
		echo '<tr><td colspan="6">No results found.</td></tr>';
	}
?>