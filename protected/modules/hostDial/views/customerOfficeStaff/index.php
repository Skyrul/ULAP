<?php 
if(!empty($customer) && !$customer->isNewRecord){
	$this->widget("application.components.CustomerSideMenu",array(
			'active'=> Yii::app()->controller->id,
			'customer' => $customer,
	));
}

?>

<?php if(!empty($customer) && !$customer->isNewRecord){ ?>
	<h1>Office Staff <small> <?php echo $customer->fullName; ?></small></h1>

<?php }else if(empty($customer) && !empty($customer_id)){ ?>
	<h1>Office Staff <small><?php echo $customer_id; ?></small></h1>
<?php }else{ ?>
	<h1>Customer Office Staff</h1>
<?php } ?>

<?php echo CHtml::link('Add Office Staff',array('customerOfficeStaff/create','customer_id'=>$customer_id,'customer_office_id' => $customer_office_id),array('class'=>'btn btn-success')); ?> &nbsp;
<br>
<br>

<?php $this->forward('/customer/customerOfficeStaff/list',false); ?>
