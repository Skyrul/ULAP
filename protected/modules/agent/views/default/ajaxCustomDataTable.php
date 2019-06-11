<div class="profile-user-info profile-user-info-striped data-fields-tab">
	<?php 
		if( $listCustomDatas )
		{
			foreach( $listCustomDatas as $listCustomData )
			{	
				$leadCustomData = LeadCustomData::model()->find(array(
					'condition' => 'lead_id = :lead_id AND list_id = :list_id AND field_name = :field_name',
					'params' => array(
						':lead_id' => $leadId,
						':list_id' => $listId,
						':field_name' => $listCustomData->original_name
					),
				));
				
				if( $leadCustomData )
				{
				?>
					<div class="profile-info-row">
						<div class="profile-info-name"> <?php echo $listCustomData->custom_name; ?> </div>
						<div class="profile-info-value">
							<?php echo $leadCustomData->value; ?>
						</div>
					</div>
				<?php
				}
			}
		}
		else
		{
			echo '<tr><td colspan="6">No custom fields found.</td></tr>';
		}
	?>
</div>