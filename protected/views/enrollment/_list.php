<?php $this->widget('zii.widgets.CListView', array(
	'id'=>'product-grid',
	'dataProvider'=>$model,
	'itemView'=>'_listPartial',
	'template'=>'
		<table class="table table-condensed table-bordered table-striped table-hover">
			<tr>
				<th class="center">Customer Name</th>
				<th class="center">Action</th>
			</tr>
			{items}
			
		</table><br>
		{pager}
			
	',
)); ?>
