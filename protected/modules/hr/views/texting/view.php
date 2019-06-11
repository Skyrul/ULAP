<div class="modal fade">
	<div class="modal-dialog" style="">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title blue"><i class="fa fa-send-o"></i> <?php echo $model->description; ?></h4>
			</div>
			<div class="modal-body">
				<blockquote>
					<p class="lighter line-height-125">
						<?php echo $model->content; ?>
					</p>
				</blockquote>
																
				<table class="table table-bordered table-hover table-striped table-condensed">
					<thead>
						<th>User</th>
						<th>Phone</th>
					</thead>
					<?php 
						if( $receivers )
						{
							foreach( $receivers as $receiver )
							{
								echo '<tr>';
									echo '<td>'.$receiver->account->getFullName().'</td>';
									echo '<td>'.$receiver->account->accountUser->mobile_number.'</td>';
								echo '</tr>';
							}
						}
						else
						{
							echo '<tr><td colspan="2">No results found.</td></tr>';
						}
					?>
				</table>
			</div>
			<div class="modal-footer hide"></div>
		</div>
	</div>
</div>