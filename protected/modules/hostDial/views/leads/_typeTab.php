<div class="tabpanel-table">
	<?php
		$statuses = Lists::allStatuses();
		$types = array();
		
		foreach($lists as $list)
		{
			$types[$list->skill_id]['id'] = $list->skill_id;
			$types[$list->skill_id]['name'] = $list->skill->skill_name;
			$types[$list->skill_id]['lists'][$list->id] = $list;
		}
		
		// var_dump($types);
		// exit;
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
			?>

			</table>

	<?php } ?>
</div>