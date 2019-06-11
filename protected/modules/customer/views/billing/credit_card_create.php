<div class="modal fade">
	<div class="modal-dialog" style="width:750px;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title blue"><i class="fa fa-credit-card"></i> Add New Credit Card</h4>
			</div>
			
			<div class="modal-body">
				<?php 
					$this->renderPartial('_credit_card_form', array(
						'model'=>$model,
						'viewOnly'=>false,
					));
				?>
			</div>
			
			<div class="modal-footer hide"></div>
		</div>
	</div>
</div>