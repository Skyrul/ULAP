<?php 
	if( $models )
	{
		$ctr = 1;
		
		foreach( $models as $model )
		{

			$showRecord = true;
			 
			if( in_array($model->status, array(1,3)) )
			{
				if( strtotime( $model->date_created ) >= strtotime('-24 hours') )
				{
					$showRecord = true;
				}	
				else
				{
					$showRecord = false;
				}
			}	
			
			if( $showRecord )
			{			
			?>
				<tr>
					<td><?php echo $ctr; ?></td>
					
					<td>
						<?php 
							$date = new DateTime($model->date_created, new DateTimeZone('America/Chicago'));

							$date->setTimezone(new DateTimeZone('America/Denver'));

							echo $date->format('m-d-Y g:i A');
						?>
					</td>
					
					<td><?php echo $model->lead->first_name.' '.$model->lead->last_name; ?></td>
					
					<td><?php echo $model->customer->getFullName(); ?></td>
					
					<td>
						<a class="view-recordings-link" lead_id="<?php echo $model->lead_id; ?>" disposition="<?php echo $model->disposition_id; ?>" href="javascript:void(0);">
							<?php 
								if( isset($model->leadCallHistory) )
								{
									echo $model->leadCallHistory->lead_phone_number;
								}
								else
								{
									echo $model->lead->office_phone_number; 
								}
							?>
						</a>
					</td>
					
					<td><?php echo $model->agentAccount->accountUser->getFullName(); ?></td>
					
					<td><?php echo $model->skill->skill_name; ?></td>
					
					<td>
						<?php 
							// if( $model->status == 0 || $model->status == 2 )
							// {
								// if( isset($model->disposition) )
								// {
									// echo CHtml::link($model->disposition->skill_disposition_name, array('reports/previewEmail', 'id'=>$model->id, 'filter'=>$filter)); 
								// }
								// else
								// {
									// $latestCallHistory = LeadCallHistory::model()->find(array(
										// 'condition' => 'lead_id = :lead_id',
										// 'params' => array(
											// ':lead_id' => $model->lead_id,
										// ),
										// 'order' => 'date_created DESC',
									// ));

									// if( $latestCallHistory )
									// {
										// if( $latestCallHistory->is_skill_child == 0 )
										// {
											// echo CHtml::link($latestCallHistory->skillDisposition->skill_disposition_name, array('reports/previewEmail', 'id'=>$model->id, 'filter'=>$filter)); 
										// }
										// else
										// {
											// echo CHtml::link($latestCallHistory->skillChildDisposition->skill_child_disposition_name, array('reports/previewEmail', 'id'=>$model->id, 'filter'=>$filter)); 
										// }
									// }
								// }
							// }
							// else
							// {
								// if( isset($model->disposition) )
								// {
									// echo $model->disposition->skill_disposition_name; 
								// }
								// else
								// {
									// $latestCallHistory = LeadCallHistory::model()->find(array(
										// 'condition' => 'lead_id = :lead_id',
										// 'params' => array(
											// ':lead_id' => $model->lead_id,
										// ),
										// 'order' => 'date_created DESC',
									// ));

									// if( $latestCallHistory )
									// {
										// if( $latestCallHistory->is_skill_child == 0 )
										// {
											// echo $latestCallHistory->skillDisposition->skill_disposition_name;
										// }
										// else
										// {
											// echo $latestCallHistory->skillChildDisposition->skill_child_disposition_name;
										// }
									// }
								// }
							// }
							
							if( $model->status == 0 || $model->status == 2 )
							{
								echo CHtml::link($model->disposition, array('reports/previewEmail', 'id'=>$model->id, 'filter'=>$filter)); 
							}
							else
							{
								echo $model->disposition;
							}
						?>
					</td>
					
					<td class="center">
						<?php 
							if( !empty($model->text_content ) )
							{
								echo CHtml::link('View', array('reports/previewText', 'id'=>$model->id, 'filter'=>$filter)); 
							}
						?>
					</td>
					
					<td class="text">
						<?php 
							if( $model->status == 0 )
							{
								echo '<span class="label label-warning">Pending</span>';
							}
							elseif( $model->status == 2 )
							{
								echo '<span class="label label-warning">On Hold</span>';
							}
							elseif( $model->status == 1 || $model->status == 5 )
							{ 
								echo '<span class="label label-success">Sent</span>';
							}
							elseif( $model->status == 3 )
							{
								echo '<span class="label label-important">Removed</span>';
							}
							else
							{
								echo '<span class="label label-important">Mailing Error</span>';
							}
						?>
					</td>
					
					<td>
						<?php
							if($model->status == 0)
							{
								echo round( abs( strtotime('now') - strtotime("+30 minutes", strtotime($model->date_created))) / 60,2). " minutes";
							}
						?>
					</td>
					
					<td>
						<?php 
							if($model->status == 0 || $model->status == 2 || $model->status == 4 )
							{
								echo CHtml::link('<i class="icon-remove"></i> Remove', 'javascript:void(0);', array('id'=>$model->id, 'class'=>'btn btn-danger btn-mini remove-email'));
							}
						?>	
						
						<?php 
							if($model->status == 0)
							{
								echo CHtml::link('<i class="icon-ban-circle"></i> Hold', 'javascript:void(0);', array('id'=>$model->id, 'class'=>'btn btn-warning btn-mini hold-email'));
							}
						?>
						
						<?php 
							if( $model->status != 1 && $model->status != 3 && $model->status != 5 )
							{
								echo CHtml::link('<i class="icon-share"></i> Send Now', 'javascript:void(0);', array('id'=>$model->id, 'class'=>'btn btn-success btn-mini send-email'));
							}
						?>
						
						<?php 
							if( $model->status == 1 )
							{
								echo CHtml::link('<i class="icon-share"></i> Resend', 'javascript:void(0);', array('id'=>$model->id, 'class'=>'btn btn-success btn-mini send-email'));
							}
						?>
					</td>
						
				</tr>
			<?php 
			}
		
			$ctr++;
		}
	}
	else
	{
		echo '<tr><td colspan="11">No results found.</td></tr>';
	}
	?>