<?php /* $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'contract-grid',
	'dataProvider'=>$model->search(),
	// 'filter'=>$model,
	'columns'=>array(
		'id',
		'company_id',
		'skill_id',
		'contract_name',
		'description',
		'billing_calculation',
		
		// 'fulfillment_type',
		// 'is_subsidy',
		// 'subsidy_name',
		// 'subsidy_expiration',
		// 'is_fee_start_activate',
		// 'start_fee_amount',
		// 'start_fee_day',
		// 'start_fee_billing_cycle',
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
	'id'=>'company-grid',
	'dataProvider'=>$model->search(),
	'itemView'=>'_listPartial',
	'template'=>'
		<table class="table table-striped">
			<tr>
				<th>ID</th>
				<th>Contract Name</th>
				<th>Skill</th>
				<th>Company</th>
				<th>Description</th>
				<th>Date Created</th>
				<th>Action</th>
			</tr>
			{items}
		</table><br>
		{pager}
			
	',
)); ?>