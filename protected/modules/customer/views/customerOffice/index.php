<?php 
if(!empty($customer) && !$customer->isNewRecord){
	
	$this->widget("application.components.CustomerSideMenu",array(
		'active'=> Yii::app()->controller->id,
		'customer' => $customer,
	));

}

?>
<?php if(!empty($customer) && !$customer->isNewRecord){ ?>
	<h1>Offices <small><?php echo $customer->fullName; ?></small></h1>
<?php }else if(empty($customer) && !empty($customer_id)){ ?>
	<h1>Offices <small> #<?php echo $customer_id; ?></small></h1>
<?php }else{ ?>
	<h1>Customer Offices</h1>
<?php } ?>

<?php echo CHtml::link('Add Office',array('customerOffice/create','customer_id'=>$customer_id),array('class'=>'btn btn-success')); ?> &nbsp;
<br><br>

<?php $this->forward('/customer/customerOffice/list',false); ?>
