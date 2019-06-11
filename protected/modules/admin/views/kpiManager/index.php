<?php
	$this->pageTitle = 'Engagex - KPI Manager';
?>

<?php 

	$baseUrl = Yii::app()->request->baseUrl;
	$cs = Yii::app()->clientScript;
	
	$cs->registerScript( uniqid(), '
	
		$(document).ready(function(){
			
			$(document).on("click", ".add-task", function(){
				
				var kpi_id = $(this).prop("id");
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/admin/kpiManager/addTask",
					type: "post",
					dataType: "json",
					data: { "ajax":1, "kpi_id": kpi_id },
					beforeSend: function(){},
					success: function( response ){
						
						if(response.html  != "" )
						{
							modal = response.html;
						}
											
						var modal = $(modal).appendTo("body");
						
						modal.modal("show").on("hidden.bs.modal", function(){
							modal.remove();
						});
						
					},
				});
				
			});
			
			
		});
	
	', CClientScript::POS_END);
	
?>

<?php 
	$this->widget("application.components.AdminSideMenu",array(
		'active'=> Yii::app()->controller->id
	));
?>

<div class="page-header">
	<h1>KPI Manager</h1>
</div>

<div class="row">
	<div class="col-sm-12">
		<table class="table table-bordered table-striped table-condensed table-hover">
			
			<thead>
				<th>KPI Name</th>
				<th>Task Name</th>
				<th>Assigned User(s)</th>
				<th class="center">Delay from Initial (Days)</th>
				<th class="center">Starting Priority</th>
				<th class="center">Priority Add</th>
				<th class="center">Max Priority</th>
				<th class="center">Sends Email</th>
				<th class="center">Email Setup</th>
			</thead>
			
			<?php 
				if( $models)
				{
					foreach( $models as $model )
					{
						echo '<tr>';
							echo '<td>'.$model->name.'</td>';
							
							echo '<td colspan="8">';	
								echo '<button id="'.$model->id.'" class="btn btn-minier btn-success add-task"><i class="fa fa-plus"></i> Add New Task</button>';
							echo '</td>';
						echo '</tr>';
						
						echo '<tr>';
							echo '<td></td>';
							
							echo '<td>';	
								echo '<a href="#">Low Names Phone 1</a>';
							echo '</td>';
							
							echo '<td>Mary</td>';
							echo '<td class="center">0</td>';
							echo '<td class="center">300</td>';
							echo '<td class="center">50</td>';
							echo '<td class="center">500</td>';
							echo '<td class="center">Yes</td>';
							echo '<td class="center"><a href="#" class="btn btn-minier btn-primary"><i class="fa fa-envelope"></i> Email Settings</a></td>';
							
						echo '</tr>';
						
						echo '<tr>';
							echo '<td></td>';
							
							echo '<td>';	
								echo '<a href="#">Low Names Phone 2</a>';
							echo '</td>';
							
							echo '<td>Mary</td>';
							echo '<td class="center">0</td>';
							echo '<td class="center">300</td>';
							echo '<td class="center">50</td>';
							echo '<td class="center">500</td>';
							echo '<td class="center">No</td>';
							echo '<td></td>';
							
						echo '</tr>';
					}
				}
			?>
			
		</table>
	</div>
</div>

