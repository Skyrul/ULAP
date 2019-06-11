<div class="modal fade">
	<div class="modal-dialog" style="width:70%;">
		<div class="modal-content">
			<div class="modal-header" style="background:#438EB9;">
				<button type="button" class="close white" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title white"><?php echo $model->first_name.' '.$model->last_name; ?></h4>
			</div>
			
			<div class="modal-body no-padding">
				<div class="profile-user-info profile-user-info-striped" style="width:100%;">
					<?php
						if( $listCustomDatas )
						{
							foreach( $listCustomDatas as $listCustomData )
							{	
								$leadCustomData = LeadCustomData::model()->find(array(
									'condition' => 'lead_id = :lead_id AND field_name = :field_name',
									'params' => array(
										':lead_id' => $leadId,
										':field_name' => $listCustomData->original_name
									),
								));
								
								if( $leadCustomData )
								{
									echo '
										<div class="profile-info-row">
											<div class="profile-info-name" style="width:15%;"> '.$listCustomData->custom_name.' </div>

											<div class="profile-info-value">
												<span>'.$leadCustomData->value.'</span>
											</div>
										</div>
									';
								}
							}
						}
						else
						{
							echo 'No custom fields found.';
						}

					?>
				</div>
			</div>
			
			<div class="modal-footer hide"></div>
		</div>
	</div>
</div>

