<?php /* $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'customer-office-grid',
	'dataProvider'=>$model->search(),
	'columns'=>array(
		'id',
		array(            // display 'create_time' using an expression
            'name'=>'customer',
            'value'=>'$data->customer->fullNameReverse',
        ),
		'office_name',
		'email_address',
		'address',
		'phone',
		
		// 'city',
		// 'fax',
		// 'state',
		// 'zip',
		// 'landmark',
		// 'status',
		// 'is_deleted',
		// 'date_created',
		// 'date_updated',
		
		array(
			'class'=>'CButtonColumn',
		),
	),
)); */ ?>

<?php $this->widget('zii.widgets.CListView', array(
	'id'=>'customer-office-grid',
	'dataProvider'=>$model->search(),
	'itemView'=>'_listPartial',
	'template'=>'
		<table class="table table-striped">
			<tr>
				<th>Office name</th>
				<th>Email address</th>
				<th>Address</th>
				<th>Phone</th>
				<th>Action</th>
			</tr>
			{items}
		</table><br>
		{pager}
			
	',
)); ?>
