<table class="table table-striped table-hover table-bordered compress lead-update-history-table">
	<thead>
		<tr>
			<th>Date/Time (lead local)</th>
			<th>Content</th>
			
		</tr>
	</thead>
	<?php
		if( $leadUpdateHistories )
		{
			foreach( $leadUpdateHistories as $leadUpdateHistory )
			{
			?>
				<tr>
					<td>
						<?php 
							$date = new DateTime($leadUpdateHistory->date_created, new DateTimeZone('America/Chicago'));

							$date->setTimezone(new DateTimeZone('America/Denver'));

							echo $date->format('m/d/Y g:i A'); 
						?>
					</td>
					
					<td><?php echo $leadUpdateHistory->content; ?></td>
					
					
				</tr>
			<?php
			}
		}
		else
		{
			echo '<tr><td colspan="2"></td></tr>';
		}
	?>
</table>