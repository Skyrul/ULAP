<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	$cs = Yii::app()->clientScript;
	
	$cs->registerScript(uniqid(), '
	
		setInterval(function() { 
		
			var isProcessing = false;
		
			var filter = $(".email-monitor-filter li.active:visible > a").attr("filter");

			if( !isProcessing )
			{
				isProcessing = true;
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/reports/emailMonitor",
					type: "post",
					dataType: "json",
					data: { "ajax":1, "filter": filter },
					success: function(response){
						
						$(".email-monitor-tbl > tbody").html(response.html);
						
						isProcessing = false;
					}
				});
			}

		}, 60000);
		
		
		$(document).on("click", ".email-monitor-filter > li", function() {
			
			$(".email-monitor-filter > li").removeClass("active");
			$(this).addClass("active");
			
			
			var filter = $(this).find("a").attr("filter");
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/reports/emailMonitor",
				type: "post",
				dataType: "json",
				data: { "ajax":1, "filter": filter },
				success: function(response){
					
					$(".email-monitor-tbl > tbody").html(response.html);
					
				},
				
			});
			
		});
		
		
		$(document).on("click", ".remove-email", function() {

			var id = $(this).prop("id");
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/reports/removeEmail",
				type: "post",
				dataType: "json",
				data: { "id": id },
				success: function(response){
				
				},
				
			});
		});
		
		$(document).on("click", ".hold-email", function() {
			
			var id = $(this).prop("id");
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/reports/holdEmail",
				type: "post",
				dataType: "json",
				data: { "id": id },
				success: function(response){

				},
				
			});
		});
		
		$(document).on("click", ".send-email", function() {
			
			var this_button = $(this);
			var id = $(this).prop("id");
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/reports/sendEmail",
				type: "post",
				dataType: "json",
				data: { "id": id },
				beforeSend: function(){
					this_button.text("Sending...");
					this_button.removeClass("send-email");
				},
				complete: function(){
					this_button.html("Sent <i class=\"fa fa-check\"></i>");
					this_button.addClass("disabled");
				},
				success: function(response){	

				},
				
			});
		});
		
		$(document).on("change", "#filterSelect", function(){
			window.location = "'.Yii::app()->createUrl('reports/emailMonitor').'?filter="+$(this).val()
		});
	
	', CClientScript::POS_END);

?>

<?php /*
<ul class="nav nav-pills email-monitor-filter">
	<li class="<?php echo ($filter == 'All') ? 'active' : ''; ?>">
		<?php echo CHtml::link('All', array('reports/emailMonitor', 'filter'=>'All')); ?>
	</li>
	
	<?php 
		if( $filters )
		{
			foreach( $filters as $filterOption )
			{
				$activeClass = $filter == $filterOption->skill_id ? 'active' : '';
				
				echo '<li class="'.$activeClass.'">'.CHtml::link($filterOption->skill->skill_name, array('reports/emailMonitor', 'filter'=>$filterOption->skill_id), array('filter'=>$filterOption->skill_id)).'</li>';
			}
		}
	?>
</ul> 
*/ ?>

Skill Filter <select id="filterSelect"> 
	<option value="All">All</option>
	<?php 
		if( $filters )
		{
			foreach( $filters as $filterOption )
			{
				$filterOptionSelected = $filter == $filterOption->skill_id ? 'selected' : '';
				
				echo '<option value="'.$filterOption->skill_id.'" '.$filterOptionSelected.'>';
					echo $filterOption->skill->skill_name;
				echo '</option>';
			}
		}
	?>
</select>

<br />
<br />

<table class="table table-condensed table-bordered email-monitor-tbl">
	<thead>
		<tr>
			<th></th>
			<th>Submitted Date/Time </th>
			<th>Lead Name</th>
			<th>Customer Name</th>
			<th>Lead Phone Number</th>
			<th>Agent Name</th>
			<th>Skill</th>
			<th>Disposition</th>
			<th>Text Message</th>
			<th>Status</th>
			<th>Time Until Send</th>
			<th style="width:16%;">Options</th>
		</tr>
	</thead>
	
	<tbody>
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
	</tbody>
	
</table>