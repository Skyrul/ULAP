<?php /* $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'company-grid',
	'dataProvider'=>$model->search(),
	// 'filter'=>$model,
	'columns'=>array(
		'id',
		'company_name',
		'description',
		
		//'date_created',
		//'date_updated',
		
		array(
			'class'=>'CButtonColumn',
		),
	),
)); */
 ?>

<?php $this->widget('zii.widgets.CListView', array(
	'id'=>'survey-grid',
	'dataProvider'=>$model->search(),
	'itemView'=>'_listPartial',
	'template'=>'
		<table class="table table-striped">
			<tr>
				<th>ID</th>
				<th>Company Name</th>
				<th>Description</th>
				<th>Date Created</th>
				<th>Status</th>
				<th>Action</th>
			</tr>
			{items}
		</table><br>
		{pager}
			
	',
)); ?>
