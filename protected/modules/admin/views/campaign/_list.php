<?php /*$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'campaign-grid',
	'dataProvider'=>$model->search(),
	// 'filter'=>$model,
	'columns'=>array(
		'id',
		'campaign_name',
		'description',
		// 'status',
		// 'is_deleted',
		'date_created',
		//'date_updated',
		array(
			'class'=>'CButtonColumn',
		),
	),
)); */ ?>


<?php $this->widget('zii.widgets.CListView', array(
	'id'=>'company-grid',
	'dataProvider'=>$model->search(),
	'itemView'=>'_listPartial',
	'template'=>'
		<table class="table table-striped">
			<tr>
				<th>ID</th>
				<th>Campaign Name</th>
				<th>Description</th>
				<th>Date Created</th>
				<th>Action</th>
			</tr>
			{items}
		</table><br>
		{pager}
			
	',
)); ?>
