<?php 
	$this->widget("application.components.AdminSideMenu",array(
		'active'=> 'phoneSearch'
	));
?>

<?php 
	
	Yii::app()->clientScript->registerScript(uniqid(), '
	
		$(document).on("click", ".view-call-history", function(){
			
			var this_link = $(this);
			var id = this_link.prop("id");
			
			var ajaxProcessing = false;
			
			if( !ajaxProcessing )
			{
				ajaxProcessing = true;
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/reports/ajaxLeadHistory",
					type: "post",
					dataType: "json",
					data: { "ajax":1, "id": id },
					beforeSend: function(){
						this_link.text("Loading...");
					},
					success: function(response) {
						
						ajaxProcessing = false;
						
						this_link.text("View");
						
						if(response.html  != "" )
						{
							modal = response.html;
						}
											
						var modal = $(modal).appendTo("body");
						
						modal.modal("show").on("hidden.bs.modal", function(){
							modal.remove();
						});
					}
				});
			}
			
		});
	
	', CClientScript::POS_END);
	
?>

<div class="page-header">
	<h1>Search Phone Number</h1>
</div>

<div class="row">
	<div class="col-sm-12">
		<form action="" method="post">
			Phone Number: 
			<input type="text" name="phoneNumber" value="<?php echo isset($_POST['phoneNumber']) ? $_POST['phoneNumber'] : '' ; ?>" placeholder="">
			
			<button type="submit" class="btn btn-xs btn-primary" style="margin-bottom:5px;">Search <i class="fa fa-search"></i> </button>					
		</form>
	</div>
</div>

<div class="space-12"></div>

<div class="row">
	<div class="col-sm-12">

		<table class="table table-striped table-bordered table-condensed table-hover">
			<thead>
				<th>#</th>
				<th>Company</th>
				<th>Customer Name</th>
				<th>Lead Name</th>
				<th>Call History</th>
			</thead>
			
			<?php 
				if( $models )
				{
					$ctr = 1;
					
					foreach( $models as $model )
					{
						echo '<tr>';
							echo '<td>'.$ctr.'</td>';
							echo '<td>'.$model->customer->company->company_name.'</td>';
							echo '<td>'.$model->customer->getFullName().'</td>';
							echo '<td>'.$model->getFullName().'</td>';
							echo '<td class="center">'.CHtml::link('View', '', array('id'=>$model->id, 'class'=>'view-call-history', 'style'=>'cursor:pointer;')).'</td>';

						echo '</tr>';
						
						$ctr++;
					}
				}
				else
				{
					echo '<tr><td colspan="8">No results found.</td></tr>';
				}
			?>
			
		</table>
	
	</div>
</div>
