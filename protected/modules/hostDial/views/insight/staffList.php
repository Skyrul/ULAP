<div class="page-header">
	<h1>List of Staff</h1>
</div>

<table class="table table-bordered table-condensed">
	<thead>
		<tr>
			<th>Name</th>
			<th>Status</th>
		</tr>
	</thead>
	<?php 
		if( $models )
		{
			foreach($models as $model)
			{
				$status = $model->status == 1 ? 'Active' : 'Inactive';
				
				echo '<tr>';
					echo '<td>'.$model->staff_name.'</td>';
					echo '<td>'.$status.'</td>';
				echo '</tr>';
			}
		}
		else
		{
			echo '<tr><td colspan="2"></td></tr>';
		}
	?>