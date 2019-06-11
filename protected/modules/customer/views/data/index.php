<?php
    foreach(Yii::app()->user->getFlashes() as $key => $message) {
        echo '
			<div class="alert alert-' . $key . '">
				<button data-dismiss="alert" class="close" type="button">
					<i class="ace-icon fa fa-times"></i>
				</button>' . $message . "
			</div>\n";
    }
?>

<div class="row">
	<div class="col-sm-12">
		<div class="page-header">
			<h1>
				<?php
					if( Yii::app()->user->account->account_type_id == Account::TYPE_GAMING_PROJECT_MANAGER )
					{
						echo 'Hosts ';
					}
					else
					{
						echo 'Customers ';
					}
					
					if( Yii::app()->user->account->checkPermission('customer_add_new_button','visible') )
					{
						if( Yii::app()->user->account->account_type_id == Account::TYPE_GAMING_PROJECT_MANAGER )
						{
							$addCustomerLabel = '<i class="fa fa-plus"></i> Add New Host';
						}
						else
						{
							$addCustomerLabel = '<i class="fa fa-plus"></i> Add New Customer';
						}
						
						echo CHtml::link($addCustomerLabel,array('/customer/data/create'),array('class'=>'btn btn-sm btn-primary')); 
					}
				?>
			</h1>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-sm-12">
		<?php $this->forward('/customer/data/list',false); ?>
	</div>
</div>