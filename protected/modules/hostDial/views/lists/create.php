<div class="row">
	<div class="col-md-6">
		<?php 
			$this->renderPartial('_form', array(
				'model' => $model,
				'customer_id' => $customer_id,
				'simpleView' => false,
				'leadsWaiting' => $leadsWaiting,
				'contract' => $contract,
			));
		?>
	</div>
</div>