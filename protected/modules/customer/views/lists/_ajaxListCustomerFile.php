<div class="row">
	<div class="col-sm-12">
		<?php 
			$this->widget('zii.widgets.CListView', array(
				'id'=>'fileList',
				'dataProvider'=>$dataProvider,
				'viewData' => array('customerId'=>$customer->id),
				'itemView'=>'_listCustomerFile',
				'template'=>'<table class="table table-striped table-condensed table-hover table-bordered">{items}</table>',
			)); 
		?>		
	</div>
</div>