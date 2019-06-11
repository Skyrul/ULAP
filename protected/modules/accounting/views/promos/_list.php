<?php $this->widget('zii.widgets.CListView', array(
	'id'=>'promo-grid',
	'dataProvider'=>$model->search(),
	'itemView'=>'_listPartial',
	'template'=>'
		<table class="table table-striped">
			<tr>
				<th>ID</th>
				<th>Promo Name</th>
				<th>Contract Name</th>
				<th>Date Created</th>
				<th>Action</th>
			</tr>
			{items}
		</table><br>
		{pager}
			
	',
)); ?>
