<?php
/* @var $this CustomerController */
/* @var $model Customer */

$this->breadcrumbs=array(
	'Customers'=>array('index'),
	'Create',
);

$baseUrl = Yii::app()->request->baseUrl;

$cs = Yii::app()->clientScript;

$cs->registerScriptFile( $baseUrl . '/template_assets/js/jquery.maskedinput.min.js');

$cs->registerScript(uniqid(), "
	$.mask.definitions['~']='[+-]';
	$('.input-mask-phone').mask('(999) 999-9999');
	$('.input-mask-zip').mask('99999');
	
	$('#Customer_custom_customer_id').mask('?**-****',{
		completed:function(){ 
			$('#Customer_custom_customer_id').val(this.val().toUpperCase()); 
		},
		autoclear: false
	});
	
	$('#Customer_custom_customer_id').on('blur',function(){
		$('#Customer_custom_customer_id').val($(this).val().toUpperCase()); 
	});
	
", CClientScript::POS_END);

?>

<div class="row">
	<div class="col-sm-12">
		<div class="row">
			<div class="page-header">
				<h1>Create Customer</h1>
			</div>
		</div>

		<div class="row">
			<div class="col-sm-12">
				<?php $this->renderPartial('_form', array('model'=>$model, 'selectedSalesReps'=>$selectedSalesReps)); ?>
			</div>
		</div>
		
	</div>
</div>