<div class="tabpanel-table">
		<?php
			$statuses = Lists::allStatuses();
			$types = array();
			foreach($lists as $list)
			{
				$statusName = '';
				if(isset($statuses[$list->status]))
					$statusName = $statuses[$list->status];
								
				$types[$list->status]['id'] = $list->status;
				$types[$list->status]['name'] = $statusName;
				$types[$list->status]['lists'][$list->id] = $list;
			}
			
			// var_dump($types);
			// exit;
			
			ksort($types);
			
			foreach($types as $type)
			{
				echo '<h2>'.$type['name'].'</h2>';
		?>
				<table style="width:1020px;">
					<thead>
						<tr>
							<th style="width:300px;">Name</th>
							<th style="width:100px;">Status</th>
							<th style="width:300px;">Type</th>
							<th>Users Assigned</th>
							<th>Leads in List</th>
							<th>Lead Callable</th>
						</tr>
					</thead>
				<?php 
					if(!empty($type['lists']))
					{
							foreach($type['lists'] as $list)
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

		<?php } ?>
</div>