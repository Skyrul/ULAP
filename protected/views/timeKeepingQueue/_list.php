<?php $this->widget('zii.widgets.CListView', array(
	'id'=>'account_pto_form-grid',
	'htmlOptions'=>array('class'=>'col-md-12 col-lg-10'),
	'dataProvider'=>$model,
	'itemView'=>'_listPartial',
	'template'=>'<div class="table-responsive">
		<table class="table table-striped">
			<tr>
				<th>ID</th>
				<th>Account</th>
				<th>Request Date</th>
				<th>Full Shift?</th>
				<th>Hours</th>
				<th>PTO?</th>
				<th>Status</th>
				<th>Action</th>
			</tr>
			{items}
		</table><br>
		{pager}
		</div>
	',
)); ?>
