<?php
/* @var $this CustomerController */
/* @var $model Customer */

$this->breadcrumbs=array(
	'Customers'=>array('index'),
	$model->id=>array('view','id'=>$model->id),
	'Update',
);

$this->widget("application.components.CustomerSideMenu",array(
		'active'=> 'customer',
		'customer' => $model,
));

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
	
	//temporary fix to highlight setup tab on customer side menu
	$('#yw0 li:eq(6)').addClass('active');
", CClientScript::POS_END);
?>

<div class="row">
	<div class="col-sm-12">
		<div class="row">
			<div class="page-header">
				<h1>Customer Information</h1>
			</div>
		</div>

		<div class="row">
			<div class="col-sm-12">
				<?php $this->renderPartial('_form', array(
					'model'=>$model,
					'selectedSalesReps'=>$selectedSalesReps,
					// 'fileupload'=>$fileupload,
				)); ?>
			</div>
		</div>
		
	</div>
</div>