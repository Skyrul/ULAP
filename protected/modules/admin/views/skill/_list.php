<?php $this->widget('zii.widgets.CListView', array(
	'id'=>'skill-grid',
	'dataProvider'=>$model->search(),
	'itemView'=>'_listPartial',
	'template'=>'
		<table class="table table-striped">
			<tr>
				<th>Name</th>
				<th>Company</th>
				<th>Status</th>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
				<th>Action</th>
			</tr>
			{items}
		</table><br>
		{pager}
			
	',
)); ?>
