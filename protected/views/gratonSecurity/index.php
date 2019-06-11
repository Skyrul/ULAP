<?php 
	$this->pageTitle = 'Engagex - Graton Credit Monitoring Codes';

	$baseUrl = Yii::app()->request->baseUrl;
	
	$cs = Yii::app()->clientScript;
	
	
	$cs->registerScript(uniqid(), '
	
		function reloadList() {
			
			var inProcess = false;
			
			if( !inProcess )
			{
				inProcess = true;
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/gratonSecurity/index",
					type: "post",
					dataType: "json",
					data: { "ajax": 1 },
					beforeSend: function(){ },
					success: function(response){
						
						if( response.status == "success" )
						{
							$("div.wrapper .table > tbody").html(response.html);
						}
						
						inProcess = false;
					}
				});
			}
		}
	
		$(document).ready( function(){
			
			// setInterval(function(){ 
				
				// var inProcess = false;
				
				// if( !inProcess )
				// {
					// inProcess = true;
					
					// $.ajax({
						// url: yii.urls.absoluteUrl + "/gratonSecurity/index",
						// type: "post",
						// dataType: "json",
						// data: { "ajax": 1 },
						// beforeSend: function(){ },
						// success: function(response){
							
							// if( response.status == "success" )
							// {
								// $("div.wrapper .table > tbody").html(response.html);
							// }
							
							// inProcess = false;
						// }
					// });
				// }

			// }, 5000);
			
			$(document).on("click", ".name-input-save-btn", function(){
				
				var this_button = $(this);
				var this_row = this_button.closest("tr");
				var id = this_row.find(".name-input-txt").prop("id");
				var value = this_row.find(".name-input-txt").val();
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/gratonSecurity/update",
					type: "post",
					dataType: "json",
					data: { 
						"ajax": 1,
						"id": id,
						"value": value
					},
					beforeSend: function(){ 
						this_button.prop("disabled", true);
						this_button.html("Saving...");
					},
					success: function(response){
						this_button.prop("disabled", false);
						this_button.html("Save");
						
						if( response.status == "success" )
						{
							this_row.html(response.html);
							alert(response.message);
						}
						else
						{
							alert(response.message);
						}
						
						reloadList();
					}
				});
			});
		});
		
		reloadList();
	');
?>

<div class="wrapper">
	<div class="page-header">
		<h1>
			Graton Credit Monitoring Codes
			<?php echo CHtml::link('View <i class="fa fa-table"></i>', array('gratonSecurity/view'), array('class'=>'btn btn-sm btn-info')); ?>
			<?php echo CHtml::link('Export <i class="fa fa-file-excel-o"></i>', array('gratonSecurity/export'), array('class'=>'btn btn-sm btn-yellow')); ?>
		</h1>
	</div>
	
	<div class="space-12"></div>
		
	<div class="row">
		<div class="col-sm-8 col-sm-offset-2">
			<table class="table table-condensed table-bordered table-striped table-hover">
				<thead>
					<th class="center">Activation Code</th>
					<th class="center">Name</th>
				</thead>
				<tbody>
					<?php 
						if( $models )
						{
							foreach( $models as $model )
							{
								echo '<tr>';
								
									echo '<td class="center">'.$model->number.'</td>';
									
									if( !empty($model->name) )
									{
										echo '<td class="center">'.$model->name.'</td>';
									}
									else
									{
										echo '<td class="center">';
										
											echo '
												<div class="input-group" style="width:70%; margin-left:15%">
													<input type="text" class="form-control name-input-txt" id="'.$model->id.'" value="'.$model->name.'">
												
													<span class="input-group-btn">
														<button class="btn btn-sm btn-primary name-input-save-btn" type="button">
															Save
														</button>
													</span>
												</div>
											';
											
										echo '</td>';
									}
									
								echo '</tr>';
							}
						}
						else
						{
							echo '<tr><td colspan="2">No activation codes found.</td></tr>';
						}
					?>
				</tbody>
			</table>
		</div>
	</div>
</div>