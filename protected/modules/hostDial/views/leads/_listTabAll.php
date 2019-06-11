<div class="tabpanel-table">
	<table style="width:1020px;">
		<thead>
			<tr>
				<th>Name</th>
				<th>Status</th>
				<th>Type</th>
				<th>Users Assigned</th>
				<th>Leads in List</th>
				<th>Lead Callable</th>
			</tr>
		</thead>
	<?php 
		if(!empty($lists))
		{
			$statuses = Lists::allStatuses();
			foreach($lists as $list)
			{
				$this->renderPartial('tr_listInfo',array(
					'customer_id' => $customer_id,
					'statuses' => $statuses,
					'list' => $list,
				));
							
				
			}
		}
		else
		{
				echo '<tr>';
						echo '<td colspan="6">No result found.</td>';
				echo '</tr>';
		}
	?>

	</table>
</div>