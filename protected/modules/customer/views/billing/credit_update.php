<?php 			
	if( Yii::app()->user->account->checkPermission('customer_offices_staff_list_settings_all_fields','edit') && !$viewOnly)
	{
		$page = '_credit_form';
		$title = 'Update';
	}
	else
	{
		$page = '_credit_form_view';
		$title = 'View';
		
	}
?>

<div class="modal fade">
	<div class="modal-dialog" style="width:750px;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title blue"><i class="fa fa-money"></i> <?php echo $title; ?>: <?php echo $model->description; ?></h4>
			</div>
			
			<div class="modal-body">
				<?php 	
					$this->renderPartial($page, array(
						'model'=>$model,
						'viewOnly'=> $viewOnly,
					));
				?>
			</div>
			
			<div class="modal-footer hide"></div>
		</div>
	</div>
</div>