<?php 
	$this->widget("application.components.AccountingSideMenu",array(
		'active'=> 'promos'
	));
?>

<div class="page-header">
	<h1>
		Promos
		<?php 
			// if( Yii::app()->user->account->checkPermission('structure_campaign_add_button','visible') )
			// {
				echo CHtml::link('<i class="fa fa-plus"></i> Add Promo',array('create'),array('class'=>'btn btn-sm btn-primary')); 
			// }
		?>
	</h1>
</div>

<?php $this->forward('/accounting/promos/list',false); ?>