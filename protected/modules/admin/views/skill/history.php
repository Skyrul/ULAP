<div class="modal fade">
	<div class="modal-dialog" style="width:50%;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title blue">
					<i class="fa fa-history"></i> History <small>&raquo; <?php echo $model->skill_name; ?></small>
				</h4>
			</div>
			
			<div class="modal-body">
			
				<?php 
					if( $models )
					{
						foreach( $models as $model )
						{
							echo '<div class="profile-activity clearfix">';
								echo '<div>';
					
									$date = new DateTime($model->date_created, new DateTimeZone('America/Chicago'));

									$date->setTimezone(new DateTimeZone('America/Denver'));

									echo $date->format('m/d/Y g:i A') . ' | ';
									
									if( isset($model->account) )
									{
										echo $model->account->username . ' | ';
									}
									
									echo $model->field_name;
									
									echo ' | ' . $model->content;
									
									if( $model->type == $model::TYPE_ADDED )
									{
										echo ' - Added';
									}
									
									if( $model->type == $model::TYPE_UPDATED )
									{
										echo ' - Updated';
									}
									
									if( $model->type == $model::TYPE_DELETED )
									{
										echo ' - Deleted';
									}
									
									if( $model->type == $model::TYPE_DOWNLOADED )
									{
										echo ' - Downloaded';
									}
									
									if( $model->type == $model::TYPE_REMOVED )
									{
										echo ' - Removed';
									}
							
								echo '</div>';
							echo '</div>';
						}
					}
					else
					{
						echo '<div class="profile-activity clearfix"><div>No records found.</div></div>';
					}
				?>
			</div>
			
			<div class="modal-footer hide"></div>
		</div>
	</div>
</div>