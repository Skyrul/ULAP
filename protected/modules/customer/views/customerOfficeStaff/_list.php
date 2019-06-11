<?php /* $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'customer-office-grid',
	'dataProvider'=>$model->search(),
	'columns'=>array(
		'id',
		array(            // display 'create_time' using an expression
            'name'=>'customer',
            'value'=>'$data->customer->fullNameReverse',
        ),
		'customerOffice.office_name',
		'staff_name',
		'email_address',
		'position',
		
		// 'is_received_email',
		// 'is_portal_access',
		// 'phone',
		// 'mobile',
		// 'fax',
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
				<th>ID</th>
				<th>Customer</th>
				<th>Office Name</th>
				<th>Staff Name</th>
				<th>Email Address</th>
				<th>Position</th>
				<th>Action</th>
			</tr>
			{items}
		</table><br>
		{pager}
			
	',
)); ?>

