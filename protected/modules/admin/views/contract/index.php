<?php 
	$this->widget("application.components.AdminSideMenu",array(
		'active'=> Yii::app()->controller->id
	));
?>

<div class="page-header">
	<h1>
		Contract
		<?php 
			if( Yii::app()->user->account->checkPermission('structure_contract_add_button','visible') )
			{
				echo CHtml::link('Add Contract',array('create'),array('class'=>'btn btn-sm btn-primary')); 
			}
		?>
	</h1>
</div>	


<?php $this->forward('/admin/contract/list',false); ?>
