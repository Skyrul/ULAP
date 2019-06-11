<?php 
	$this->widget("application.components.AdminSideMenu",array(
		'active'=> Yii::app()->controller->id
	));
?>

<h1>View Contract <small><?php echo $model->contract_name; ?></small></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'company_id',
		'skill_id',
		'contract_name',
		'description',
		'billing_calculation',
		'fulfillment_type',
		'is_subsidy',
		'subsidy_name',
		'subsidy_expiration',
		'is_fee_start_activate',
		'start_fee_amount',
		'start_fee_day',
		'start_fee_billing_cycle',
		'status',
		'is_deleted',
		'date_created',
		'date_updated',
	),
)); ?>
