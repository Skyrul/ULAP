<div class="modal fade">
	<div class="modal-dialog" style="width:750px;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title blue"><i class="fa fa-credit-card"></i> Update: <?php echo $model->account_name; ?></h4>
			</div>
			
			<div class="modal-body">
			
				<?php 
					if( Yii::app()->user->account->getIsAdmin() || Yii::app()->user->account->getIsCustomerService() )
					{
						$viewOnly = false;
					}
					else
					{
						$viewOnly = true;
					}
				?>
			
				<?php 
					$this->renderPartial('echeck_form', array(
						'model'=>$model,
						'viewOnly'=>$viewOnly,
					));
				?>
			</div>
			
			<div class="modal-footer hide"></div>
		</div>
	</div>
</div>